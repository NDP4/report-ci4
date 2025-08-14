<?php

namespace App\Controllers;

use App\Models\SdmModel;
use App\Models\PresensiModel;
use App\Models\QueueOnxModel;
use App\Models\ReportAgentLogModel;
use App\Models\AgentBucketModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReader;

class AuxUploadController extends BaseController
{
    protected $sdmModel;
    protected $presensiModel;
    protected $queueOnxModel;
    protected $reportAgentLogModel;
    protected $agentBucketModel;

    public function __construct()
    {
        $this->sdmModel = new SdmModel();
        $this->presensiModel = new PresensiModel();
        $this->queueOnxModel = new QueueOnxModel();
        $this->reportAgentLogModel = new ReportAgentLogModel();
        $this->agentBucketModel = new AgentBucketModel();
    }

    public function index()
    {
        return view('aux/upload', [
            'title' => 'Upload Data AUX'
        ]);
    }

    /**
     * Simple upload form without complex JavaScript
     */
    public function simple()
    {
        return view('aux/upload_simple', [
            'title' => 'Upload Data AUX (Simple)'
        ]);
    }

    public function process()
    {
        if ($this->request->getMethod() !== 'POST') {
            return redirect()->back()->with('error', 'Invalid request method');
        }

        // Set time limit and memory for large file processing
        ini_set('max_execution_time', 0); // No time limit
        ini_set('memory_limit', '2048M'); // Increase memory limit

        // Enable output buffering to prevent timeout
        if (ob_get_level() == 0) ob_start();

        $validation = \Config\Services::validation();
        $validation->setRules([
            'file_type' => 'required|in_list[sdm,presensi,queue_onx,report_agent_log]',
            'file' => 'uploaded[file]|ext_in[file,xlsx,xls,csv]|max_size[file,512000]'
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            $errors = $validation->getErrors();
            log_message('error', 'AUX Upload validation failed: ' . json_encode($errors));
            return redirect()->back()->with('error', 'Validasi gagal: ' . implode(', ', $errors));
        }

        $fileType = $this->request->getPost('file_type');
        $file = $this->request->getFile('file');

        if (!$file || !$file->isValid()) {
            $errorMsg = $file ? $file->getErrorString() : 'No file uploaded';
            log_message('error', 'AUX Upload file validation failed: ' . $errorMsg);
            return redirect()->back()->with('error', 'File tidak valid: ' . $errorMsg);
        }

        try {
            $uploadPath = WRITEPATH . 'uploads/aux/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fileName = $file->getRandomName();
            $file->move($uploadPath, $fileName);
            $filePath = $uploadPath . $fileName;

            // Process the file based on type with optimized method
            $result = $this->processFileOptimized($filePath, $fileType);

            // Delete uploaded file after processing
            unlink($filePath);

            if ($result['success']) {
                session()->setFlashdata('success', $result['message']);

                // Auto compute buckets if this is presensi, queue_onx, or report_agent_log
                if (in_array($fileType, ['presensi', 'queue_onx', 'report_agent_log'])) {
                    $this->computeBucketsForToday();
                }

                return redirect()->to('/aux/dashboard');
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Exception $e) {
            // Clean up file if exists
            if (isset($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }

            log_message('error', 'Upload error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        } finally {
            // Clean output buffer
            if (ob_get_level() > 0) ob_end_clean();
        }
    }

    /**
     * Optimized file processing with memory management and chunking
     */
    private function processFileOptimized($filePath, $fileType)
    {
        try {
            // Use read-only mode to save memory
            $reader = IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);

            $spreadsheet = $reader->load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            // Get total row count for progress tracking
            $totalRows = $worksheet->getHighestRow();
            $headers = null;
            $processedCount = 0;
            $batchSize = 1000; // Process in chunks of 1000 rows

            log_message('info', "Starting to process {$totalRows} rows for {$fileType}");

            // Read file in chunks to save memory
            for ($startRow = 1; $startRow <= $totalRows; $startRow += $batchSize) {
                $endRow = min($startRow + $batchSize - 1, $totalRows);

                // Read chunk of rows
                $chunkData = $worksheet->rangeToArray(
                    'A' . $startRow . ':' . $worksheet->getHighestColumn() . $endRow,
                    null, // Return null for empty cells
                    true, // Calculate formulas
                    false, // Don't format values
                    false // Don't return as associative array
                );

                // Extract headers from first chunk
                if ($startRow === 1 && !empty($chunkData)) {
                    $headers = array_shift($chunkData);
                    if (empty($chunkData)) {
                        continue; // Only header row in this chunk
                    }
                }

                if (empty($chunkData)) {
                    continue;
                }

                // Process chunk based on file type
                $result = $this->processDataChunk($chunkData, $fileType, $headers);

                if (!$result['success']) {
                    return $result;
                }

                $processedCount += $result['count'];

                // Clear memory
                unset($chunkData);

                // Force garbage collection
                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }

                // Log progress for large files
                if ($totalRows > 5000) {
                    $progress = round(($endRow / $totalRows) * 100, 2);
                    log_message('info', "Progress: {$progress}% ({$endRow}/{$totalRows}) - Processed: {$processedCount}");
                }
            }

            // Clean up spreadsheet object
            $spreadsheet->disconnectWorksheets();
            unset($spreadsheet, $worksheet, $reader);

            return [
                'success' => true,
                'message' => "Berhasil upload {$processedCount} data {$fileType}",
                'count' => $processedCount
            ];
        } catch (\Exception $e) {
            log_message('error', "Error in processFileOptimized: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error processing file: ' . $e->getMessage()];
        }
    }

    /**
     * Process a chunk of data based on file type
     */
    private function processDataChunk($rows, $fileType, $headers)
    {
        try {
            switch ($fileType) {
                case 'sdm':
                    return $this->processSdmDataOptimized($rows);
                case 'presensi':
                    return $this->processPresensiDataOptimized($rows);
                case 'queue_onx':
                    return $this->processQueueOnxDataOptimized($rows);
                case 'report_agent_log':
                    return $this->processReportAgentLogDataOptimized($rows);
                default:
                    return ['success' => false, 'message' => 'Tipe file tidak dikenal', 'count' => 0];
            }
        } catch (\Exception $e) {
            log_message('error', "Error in processDataChunk: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error processing data chunk: ' . $e->getMessage(), 'count' => 0];
        }
    }

    /**
     * Optimized SDM data processing with batch insert
     */
    private function processSdmDataOptimized($rows)
    {
        $data = [];
        $now = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            if (empty($row[0])) continue; // Skip empty rows

            $data[] = [
                'logid' => $row[0] ?? null,
                'fullname' => $row[1] ?? null,
                'fullname_norm' => strtolower(trim($row[1] ?? '')),
                'channel_name' => $row[2] ?? null,
                'channel_name_norm' => strtolower(trim($row[2] ?? '')),
                'position' => $row[3] ?? null,
                'unit' => $row[4] ?? null,
                'created_at' => $now,
                'updated_at' => $now
            ];
        }

        if (empty($data)) {
            return ['success' => true, 'message' => 'Tidak ada data valid dalam chunk ini', 'count' => 0];
        }

        // Use optimized batch insert
        $inserted = $this->insertBatchOptimized($this->sdmModel, $data);
        return ['success' => true, 'message' => "Chunk processed", 'count' => $inserted];
    }

    /**
     * Optimized Presensi data processing with batch insert
     */
    private function processPresensiDataOptimized($rows)
    {
        $data = [];
        $now = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            if (empty($row[0])) continue; // Skip empty rows

            $workDate = null;
            if (!empty($row[2])) {
                $workDate = date('Y-m-d', strtotime($row[2]));
            }

            $data[] = [
                'logid' => $row[0] ?? null,
                'fullname_norm' => strtolower(trim($row[1] ?? '')),
                'work_date' => $workDate,
                'time_in' => !empty($row[3]) ? date('H:i:s', strtotime($row[3])) : null,
                'time_out' => !empty($row[4]) ? date('H:i:s', strtotime($row[4])) : null,
                'hadir' => !empty($row[5]) ? 1 : 0,
                'created_at' => $now
            ];
        }

        if (empty($data)) {
            return ['success' => true, 'message' => 'Tidak ada data valid dalam chunk ini', 'count' => 0];
        }

        // Use optimized batch insert
        $inserted = $this->insertBatchOptimized($this->presensiModel, $data);
        return ['success' => true, 'message' => "Chunk processed", 'count' => $inserted];
    }

    /**
     * Optimized Queue ONX data processing with batch insert
     */
    private function processQueueOnxDataOptimized($rows)
    {
        $data = [];
        $now = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            if (empty($row[0])) continue; // Skip empty rows

            $dateStart = null;
            if (!empty($row[5])) {
                $dateStart = date('Y-m-d H:i:s', strtotime($row[5]));
            }

            $data[] = [
                'source_id' => $row[0] ?? null,
                'fullname_raw' => $row[1] ?? null,
                'fullname_norm' => strtolower(trim($row[1] ?? '')),
                'channel_name_raw' => $row[2] ?? null,
                'channel_name_norm' => strtolower(trim($row[2] ?? '')),
                'date_start_interaction' => $dateStart,
                'mainCategory' => $row[6] ?? null,
                'category' => $row[7] ?? null,
                'witel' => $row[8] ?? null,
                'raw_payload' => json_encode($row),
                'uploaded_at' => $now
            ];
        }

        if (empty($data)) {
            return ['success' => true, 'message' => 'Tidak ada data valid dalam chunk ini', 'count' => 0];
        }

        // Use optimized batch insert
        $inserted = $this->insertBatchOptimized($this->queueOnxModel, $data);
        return ['success' => true, 'message' => "Chunk processed", 'count' => $inserted];
    }

    /**
     * Optimized Report Agent Log data processing with batch insert
     */
    private function processReportAgentLogDataOptimized($rows)
    {
        $data = [];
        $now = date('Y-m-d H:i:s');

        foreach ($rows as $row) {
            if (empty($row[0])) continue; // Skip empty rows

            $dateStart = null;
            if (!empty($row[2])) {
                $dateStart = date('Y-m-d H:i:s', strtotime($row[2]));
            }

            $data[] = [
                'fullname_raw' => $row[0] ?? null,
                'fullname_norm' => strtolower(trim($row[0] ?? '')),
                'date_start' => $dateStart,
                'state' => $row[3] ?? null,
                'reason_login' => $row[4] ?? null,
                'raw_payload' => json_encode($row),
                'uploaded_at' => $now
            ];
        }

        if (empty($data)) {
            return ['success' => true, 'message' => 'Tidak ada data valid dalam chunk ini', 'count' => 0];
        }

        // Use optimized batch insert
        $inserted = $this->insertBatchOptimized($this->reportAgentLogModel, $data);
        return ['success' => true, 'message' => "Chunk processed", 'count' => $inserted];
    }

    /**
     * Ultra-fast optimized batch insert method
     */
    private function insertBatchOptimized($model, $data, $batchSize = 500)
    {
        if (empty($data)) {
            return 0;
        }

        // Get database connection
        $db = $model->db;

        // Temporarily disable foreign key checks and autocommit for speed
        $db->query('SET foreign_key_checks = 0');
        $db->query('SET autocommit = 0');
        $db->query('SET unique_checks = 0');
        $db->query('SET sql_log_bin = 0');

        // Start transaction
        $db->transStart();

        $totalInserted = 0;
        $chunks = array_chunk($data, $batchSize);

        foreach ($chunks as $chunk) {
            try {
                // Use raw SQL for maximum speed
                $result = $this->insertBatchRaw($model, $chunk);
                $totalInserted += $result;

                // Clear memory
                unset($chunk);
            } catch (\Exception $e) {
                log_message('error', 'Batch insert error: ' . $e->getMessage());
                // Continue with next chunk
                continue;
            }
        }

        // Commit transaction
        $db->transComplete();

        // Re-enable settings
        $db->query('SET sql_log_bin = 1');
        $db->query('SET unique_checks = 1');
        $db->query('SET autocommit = 1');
        $db->query('SET foreign_key_checks = 1');

        return $totalInserted;
    }

    /**
     * Raw SQL batch insert for maximum performance
     */
    private function insertBatchRaw($model, array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        $db = $model->db;
        $table = $db->escapeIdentifiers($model->table);
        $keys = array_keys($data[0]);
        $escapedKeys = array_map([$db, 'escapeIdentifiers'], $keys);

        // Build the INSERT statement with IGNORE to handle duplicates
        $sql = "INSERT IGNORE INTO {$table} (" . implode(', ', $escapedKeys) . ") VALUES ";

        $values = [];
        foreach ($data as $row) {
            $rowValues = [];
            foreach ($keys as $key) {
                $value = $row[$key] ?? null;
                $rowValues[] = $value === null ? 'NULL' : $db->escape($value);
            }
            $values[] = '(' . implode(', ', $rowValues) . ')';
        }

        $sql .= implode(', ', $values);

        // Execute the query
        $db->query($sql);

        return count($data);
    }

    public function computeBucketsForToday()
    {
        $today = date('Y-m-d');
        $result = $this->agentBucketModel->computeBucketsForDate($today);

        if ($result) {
            log_message('info', 'Buckets computed successfully for ' . $today);
        } else {
            log_message('error', 'Failed to compute buckets for ' . $today);
        }

        return $result;
    }

    /**
     * AJAX endpoint to check upload progress (optional enhancement)
     */
    public function checkProgress()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['error' => 'Invalid request']);
        }

        // For now, return a simple response
        // In the future, this could be connected to session-based progress tracking
        return $this->response->setJSON([
            'progress' => 0,
            'status' => 'waiting',
            'message' => 'Progress tracking not yet implemented'
        ]);
    }

    /**
     * Test upload limits endpoint
     */
    public function testLimits()
    {
        $limits = [
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'post_max_size' => ini_get('post_max_size'),
            'max_execution_time' => ini_get('max_execution_time'),
            'memory_limit' => ini_get('memory_limit'),
            'max_input_time' => ini_get('max_input_time'),
            'max_file_uploads' => ini_get('max_file_uploads')
        ];

        return $this->response->setJSON($limits);
    }
}
