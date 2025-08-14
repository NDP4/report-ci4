<?php

namespace App\Models;

use CodeIgniter\Model;

class QueueOnxModel extends Model
{
    protected $table = 'queue_onx';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'source_id',
        'fullname_raw',
        'fullname_norm',
        'channel_name_raw',
        'channel_name_norm',
        'date_start_interaction',
        'mainCategory',
        'category',
        'witel',
        'raw_payload',
        'uploaded_at'
    ];

    protected bool $allowEmptyInserts = false;

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat = 'datetime';

    // Validation
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert = ['normalizeFields'];
    protected $afterInsert = [];
    protected $beforeUpdate = ['normalizeFields'];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    protected function normalizeFields(array $data)
    {
        if (isset($data['data']['fullname_raw'])) {
            $data['data']['fullname_norm'] = strtolower(trim($data['data']['fullname_raw']));
        }

        if (isset($data['data']['channel_name_raw'])) {
            $data['data']['channel_name_norm'] = strtolower(trim($data['data']['channel_name_raw']));
        }

        return $data;
    }

    public function getQueueCountByDate($workDate)
    {
        return $this->select('fullname_norm, channel_name_norm, COUNT(*) as queue_count')
            ->where('DATE(date_start_interaction)', $workDate)
            ->groupBy('fullname_norm, channel_name_norm')
            ->findAll();
    }

    public function getQueueCountByDateAndName($workDate, $fullnameNorm)
    {
        return $this->where('DATE(date_start_interaction)', $workDate)
            ->where('fullname_norm', $fullnameNorm)
            ->countAllResults();
    }

    public function getByDateRange($startDate, $endDate)
    {
        return $this->where('DATE(date_start_interaction) >=', $startDate)
            ->where('DATE(date_start_interaction) <=', $endDate)
            ->findAll();
    }

    public function getWitelStats($workDate)
    {
        try {
            $result = $this->select('witel, COUNT(*) as count')
                ->where('DATE(date_start_interaction)', $workDate)
                ->groupBy('witel')
                ->orderBy('count', 'DESC')
                ->findAll();
            return $result ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error in getWitelStats: ' . $e->getMessage());
            return [];
        }
    }

    public function getMainCategoryStats($workDate)
    {
        try {
            $result = $this->select('mainCategory, COUNT(*) as count')
                ->where('DATE(date_start_interaction)', $workDate)
                ->groupBy('mainCategory')
                ->orderBy('count', 'DESC')
                ->findAll();
            return $result ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error in getMainCategoryStats: ' . $e->getMessage());
            return [];
        }
    }

    public function bulkInsert($data)
    {
        return $this->insertBatch($data);
    }

    /**
     * Optimized batch insert for Queue ONX data
     */
    public function insertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 500, bool $testing = false): int
    {
        if (empty($set)) {
            return 0;
        }

        // Temporarily disable foreign key checks and autocommit for speed
        $this->db->query('SET foreign_key_checks = 0');
        $this->db->query('SET autocommit = 0');
        $this->db->query('SET unique_checks = 0');

        // Start transaction
        $this->db->transStart();

        $totalInserted = 0;
        $chunks = array_chunk($set, $batchSize);

        foreach ($chunks as $chunk) {
            try {
                // Use raw SQL for maximum speed
                $result = $this->insertBatchRaw($chunk);
                $totalInserted += $result;

                // Clear memory
                unset($chunk);
            } catch (\Exception $e) {
                log_message('error', 'Queue ONX Batch insert error: ' . $e->getMessage());
                continue;
            }
        }

        // Commit transaction
        $this->db->transComplete();

        // Re-enable settings
        $this->db->query('SET unique_checks = 1');
        $this->db->query('SET autocommit = 1');
        $this->db->query('SET foreign_key_checks = 1');

        return $totalInserted;
    }

    /**
     * Raw SQL batch insert for maximum performance
     */
    private function insertBatchRaw(array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $table = $this->db->escapeIdentifiers($this->table);
        $keys = array_keys($data[0]);
        $escapedKeys = array_map([$this->db, 'escapeIdentifiers'], $keys);

        // Build the INSERT statement with IGNORE to handle duplicates
        $sql = "INSERT IGNORE INTO {$table} (" . implode(', ', $escapedKeys) . ") VALUES ";

        $values = [];
        foreach ($data as $row) {
            $rowValues = [];
            foreach ($keys as $key) {
                $value = $row[$key] ?? null;
                $rowValues[] = $value === null ? 'NULL' : $this->db->escape($value);
            }
            $values[] = '(' . implode(', ', $rowValues) . ')';
        }

        $sql .= implode(', ', $values);

        // Execute the query
        $this->db->query($sql);

        return count($data);
    }
}
