<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTicketModel extends Model
{
    protected $table = 'service_tickets';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'ticket_number',
        'date_created',
        'customer_name',
        'service_type',
        'priority',
        'status',
        'assigned_to',
        'description',
        'resolution',
        'date_resolved'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true; // Skip validation for faster insert
    protected $cleanValidationRules = true;

    /**
     * Ultra-fast batch insert with optimizations
     */
    public function insertBatch(?array $set = null, ?bool $escape = null, int $batchSize = 500, bool $testing = false): int
    {
        if (empty($set)) {
            return 0;
        }

        // Temporarily disable foreign key checks and autocommit for speed
        $this->db->query('SET foreign_key_checks = 0');
        $this->db->query('SET autocommit = 0');

        // Start transaction
        $this->db->transStart();

        $totalInserted = 0;
        $chunks = array_chunk($set, $batchSize);

        foreach ($chunks as $chunk) {
            // Add timestamps to all records at once
            $currentTime = date('Y-m-d H:i:s');
            foreach ($chunk as &$record) {
                if ($this->useTimestamps) {
                    $record[$this->createdField] = $currentTime;
                    $record[$this->updatedField] = $currentTime;
                }
            }
            unset($record); // Break reference

            // Use raw SQL for maximum speed
            $result = $this->insertBatchRaw($chunk);
            $totalInserted += $result;

            // Clear memory
            unset($chunk);
        }

        // Commit transaction
        $this->db->transComplete();

        // Re-enable foreign key checks and autocommit
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

        // Build the INSERT statement
        $sql = "INSERT INTO {$table} (" . implode(', ', $escapedKeys) . ") VALUES ";

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
