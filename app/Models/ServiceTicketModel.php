<?php

namespace App\Models;

use CodeIgniter\Model;

class ServiceTicketModel extends Model
{
    protected $table = 'service_tickets';
    protected $primaryKey = 'ticket_id';
    protected $useAutoIncrement = false; // Changed because ticket_id is string
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'ticket_id',
        'subject',
        'remark',
        'priority_id',
        'priority_name',
        'ticket_status_name',
        'unit_id',
        'unit_name',
        'informant_id',
        'informant_name',
        'informant_hp',
        'informant_email',
        'customer_id',
        'customer_name',
        'customer_hp',
        'customer_email',
        'date_origin_interaction',
        'date_start_interaction',
        'date_open',
        'date_close',
        'date_last_update',
        'is_escalated',
        'created_by_name',
        'updated_by_name',
        'channel_id',
        'session_id',
        'category_id',
        'category_name',
        'date_created_at',
        'sla',
        'channel_name',
        'main_category',
        'category',
        'sub_category',
        'detail_sub_category',
        'detail_sub_category2',
        'regional',
        'type_queue_priority',
        'group_id',
        'group_name',
        'date_first_pickup_interaction',
        'status_case',
        'indihome_num',
        'witel',
        'feedback',
        'date_first_response_interaction',
        'date_pickup_interaction',
        'date_end_interaction',
        'case_in',
        'case_out',
        'account',
        'account_name',
        'informant_member_id',
        'customer_member_id',
        'shift',
        'status_date',
        'sentiment_incoming',
        'sentiment_outgoing',
        'sentiment_all',
        'sentiment_service',
        'parent_id',
        'count_merged',
        'source_id',
        'source_name',
        'msisdn',
        'from_id',
        'from_username',
        'ticket_id_digipos',
        'ticket_customer_consent',
        'ticket_no_indi_home_alternatif',
        'sla_second',
        'informant_1',
        'informant_2',
        'customer_1',
        'customer_2',
        'ticket_no_k_t_p'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true;
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
