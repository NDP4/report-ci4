<?php

namespace App\Models;

use CodeIgniter\Model;

class ReportAgentLogModel extends Model
{
    protected $table = 'report_agent_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'fullname_raw',
        'fullname_norm',
        'date_start',
        'state',
        'reason_login',
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

        return $data;
    }

    public function getAuxByDate($workDate)
    {
        return $this->select('fullname_norm, 1 as has_aux')
            ->where('DATE(date_start)', $workDate)
            ->groupBy('fullname_norm')
            ->findAll();
    }

    public function hasAuxByDateAndName($workDate, $fullnameNorm)
    {
        return $this->where('DATE(date_start)', $workDate)
            ->where('fullname_norm', $fullnameNorm)
            ->countAllResults() > 0;
    }

    public function getDetailsByDate($workDate)
    {
        try {
            $result = $this->where('DATE(date_start)', $workDate)
                ->orderBy('date_start', 'ASC')
                ->findAll();
            return $result ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error in getDetailsByDate: ' . $e->getMessage());
            return [];
        }
    }

    public function getDetailsByDateAndName($workDate, $fullnameNorm)
    {
        return $this->where('DATE(date_start)', $workDate)
            ->where('fullname_norm', $fullnameNorm)
            ->orderBy('date_start', 'ASC')
            ->findAll();
    }

    public function bulkInsert($data)
    {
        return $this->insertBatch($data);
    }

    /**
     * Optimized batch insert for Report Agent Log data
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
                log_message('error', 'Report Agent Log Batch insert error: ' . $e->getMessage());
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
