<?php

namespace App\Models;

use CodeIgniter\Model;

class AgentBucketModel extends Model
{
    protected $table = 'agent_bucket';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'work_date',
        'sdm_id',
        'logid',
        'fullname_norm',
        'channel_name_norm',
        'queue_count',
        'has_aux',
        'presensi',
        'bucket',
        'reason',
        'updated_at'
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
    protected $beforeInsert = [];
    protected $afterInsert = [];
    protected $beforeUpdate = [];
    protected $afterUpdate = [];
    protected $beforeFind = [];
    protected $afterFind = [];
    protected $beforeDelete = [];
    protected $afterDelete = [];

    public function computeBucketsForDate($workDate)
    {
        $db = \Config\Database::connect();

        try {
            $db->transStart();

            // Create temporary tables for queue count
            $db->query("
                CREATE TEMPORARY TABLE tmp_queue AS
                SELECT q.fullname_norm, q.channel_name_norm, COUNT(*) AS qcount
                FROM queue_onx q
                WHERE DATE(q.date_start_interaction) = ?
                GROUP BY q.fullname_norm, q.channel_name_norm
            ", [$workDate]);

            // Create temporary tables for aux presence
            $db->query("
                CREATE TEMPORARY TABLE tmp_aux AS
                SELECT r.fullname_norm, 1 AS has_aux
                FROM report_agent_log r
                WHERE DATE(r.date_start) = ?
                GROUP BY r.fullname_norm
            ", [$workDate]);

            // Create temporary tables for attendance
            $db->query("
                CREATE TEMPORARY TABLE tmp_presensi AS
                SELECT p.fullname_norm, p.sdm_id
                FROM presensi p
                WHERE p.work_date = ? AND p.hadir = 1
                GROUP BY p.fullname_norm
            ", [$workDate]);

            // Upsert into agent_bucket
            $db->query("
                INSERT INTO agent_bucket (work_date, sdm_id, fullname_norm, channel_name_norm, queue_count, has_aux, presensi, bucket, updated_at)
                SELECT
                    ? as work_date,
                    p.sdm_id,
                    p.fullname_norm,
                    COALESCE(tq.channel_name_norm, NULL) AS channel_name_norm,
                    COALESCE(tq.qcount, 0) AS queue_count,
                    COALESCE(ta.has_aux, 0) AS has_aux,
                    1 AS presensi,
                    CASE
                        WHEN COALESCE(tq.qcount,0) IN (1,2,3) AND COALESCE(ta.has_aux,0) = 0 THEN CAST(COALESCE(tq.qcount,0) AS CHAR)
                        WHEN COALESCE(tq.qcount,0) = 0 AND COALESCE(ta.has_aux,0) = 1 THEN 'idle'
                        WHEN COALESCE(tq.qcount,0) = 0 AND COALESCE(ta.has_aux,0) = 0 THEN 'anomali'
                        ELSE 'anomali'
                    END AS bucket,
                    NOW() as updated_at
                FROM tmp_presensi p
                LEFT JOIN tmp_queue tq ON p.fullname_norm = tq.fullname_norm
                LEFT JOIN tmp_aux ta ON p.fullname_norm = ta.fullname_norm
                ON DUPLICATE KEY UPDATE
                    queue_count = VALUES(queue_count),
                    has_aux = VALUES(has_aux),
                    presensi = VALUES(presensi),
                    bucket = VALUES(bucket),
                    channel_name_norm = VALUES(channel_name_norm),
                    updated_at = VALUES(updated_at)
            ", [$workDate]);

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \Exception('Transaction failed');
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Error computing buckets: ' . $e->getMessage());
            return false;
        }
    }

    public function getByDate($workDate, $bucket = null)
    {
        try {
            $query = $this->where('work_date', $workDate);

            if ($bucket) {
                $query->where('bucket', $bucket);
            }

            $result = $query->orderBy('fullname_norm', 'ASC')->findAll();
            return $result ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error in getByDate: ' . $e->getMessage());
            return [];
        }
    }

    public function getBucketStats($workDate)
    {
        try {
            $result = $this->select('bucket, COUNT(*) as count')
                ->where('work_date', $workDate)
                ->groupBy('bucket')
                ->findAll();
            return $result ?? [];
        } catch (\Exception $e) {
            log_message('error', 'Error in getBucketStats: ' . $e->getMessage());
            return [];
        }
    }

    public function getDateRange($startDate, $endDate)
    {
        return $this->where('work_date >=', $startDate)
            ->where('work_date <=', $endDate)
            ->orderBy('work_date', 'DESC')
            ->orderBy('fullname_norm', 'ASC')
            ->findAll();
    }

    public function upsertBucket($data)
    {
        $existing = $this->where('work_date', $data['work_date'])
            ->where('fullname_norm', $data['fullname_norm'])
            ->first();

        if ($existing) {
            return $this->update($existing['id'], $data);
        } else {
            return $this->insert($data);
        }
    }
}
