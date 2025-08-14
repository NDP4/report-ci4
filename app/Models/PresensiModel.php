<?php

namespace App\Models;

use CodeIgniter\Model;

class PresensiModel extends Model
{
    protected $table = 'presensi';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'sdm_id',
        'logid',
        'fullname_norm',
        'work_date',
        'time_in',
        'time_out',
        'hadir',
        'created_at'
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
        if (isset($data['data']['fullname'])) {
            $data['data']['fullname_norm'] = strtolower(trim($data['data']['fullname']));
        }

        return $data;
    }

    public function getHadirByDate($workDate)
    {
        return $this->where('work_date', $workDate)
            ->where('hadir', 1)
            ->findAll();
    }

    public function getByDateAndName($workDate, $fullnameNorm)
    {
        return $this->where('work_date', $workDate)
            ->where('fullname_norm', $fullnameNorm)
            ->first();
    }

    public function bulkInsert($data)
    {
        return $this->insertBatch($data);
    }

    /**
     * Optimized batch insert for Presensi data
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
                log_message('error', 'Presensi Batch insert error: ' . $e->getMessage());
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
