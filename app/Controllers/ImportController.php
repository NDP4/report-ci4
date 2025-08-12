<?php

namespace App\Controllers;

use App\Models\ServiceTicketModel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Csv;
use CodeIgniter\HTTP\ResponseInterface;

class ImportController extends BaseController
{
    protected $serviceTicketModel;
    private $batchSize = 500; // Reduce batch size to prevent memory issues

    public function __construct()
    {
        $this->serviceTicketModel = new ServiceTicketModel();
    }

    public function index()
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        // Get server limits for display
        $uploadMaxSize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $memoryLimit = ini_get('memory_limit');

        $data = [
            'title' => 'Import Data Ticket',
            'breadcrumb' => [
                'Ticket' => 'ticket',
                'Import Data Ticket' => 'import'
            ],
            'uploadMaxSize' => $uploadMaxSize,
            'postMaxSize' => $postMaxSize,
            'memoryLimit' => $memoryLimit,
            'postMaxSizeBytes' => $this->parseSize($postMaxSize) // Add parsed size in bytes
        ];

        // return view('import/index', $data);
        return view('pages/import', $data);
    }

    public function upload()
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        // Early check for file size issues before any processing
        $contentLength = $_SERVER['CONTENT_LENGTH'] ?? 0;
        $postMaxSize = $this->parseSize(ini_get('post_max_size'));

        if ($contentLength > $postMaxSize) {
            $displayMaxSize = ini_get('post_max_size');
            echo json_encode([
                'status' => 'error',
                'message' => "File terlalu besar. Maksimal ukuran file yang diizinkan: {$displayMaxSize}"
            ]);
            return;
        }

        if (empty($_POST) && empty($_FILES) && $contentLength > 0) {
            $displayMaxSize = ini_get('post_max_size');
            echo json_encode([
                'status' => 'error',
                'message' => "File terlalu besar. Maksimal ukuran file yang diizinkan: {$displayMaxSize}"
            ]);
            return;
        }

        $validation = \Config\Services::validation();

        $validation->setRules([
            'file' => [
                'label' => 'File',
                'rules' => 'uploaded[file]|ext_in[file,xlsx,xls,csv]|max_size[file,102400]'
            ]
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $file = $this->request->getFile('file');

        if ($file->isValid() && !$file->hasMoved()) {
            if ($file->getSize() > 104857600) {
                return redirect()->to('/import')->with('error', 'File terlalu besar. Maksimal 100MB');
            }

            $fileName = $file->getRandomName();
            $file->move(WRITEPATH . 'uploads', $fileName);
            $filePath = WRITEPATH . 'uploads/' . $fileName;

            try {
                $totalRecords = $this->processExcelFileInBatches($filePath);

                unlink($filePath);

                if ($totalRecords > 0) {
                    return redirect()->to('/import')->with('success', "Data berhasil diimport. Total: {$totalRecords} records");
                } else {
                    return redirect()->to('/import')->with('error', 'File kosong atau format tidak sesuai');
                }
            } catch (\Exception $e) {
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
                return redirect()->to('/import')->with('error', 'Error: ' . $e->getMessage());
            }
        }

        return redirect()->to('/import')->with('error', 'File upload gagal');
    }

    private function processExcelFileInBatches($filePath)
    {
        // More aggressive memory optimization
        ini_set('memory_limit', '-1'); // Unlimited memory
        ini_set('max_execution_time', 0);
        ini_set('max_input_time', -1);

        // Disable garbage collection during processing for speed
        gc_disable();

        // Get file extension to determine reader type
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Create appropriate reader with optimizations
        switch ($extension) {
            case 'xlsx':
                $reader = new Xlsx();
                break;
            case 'xls':
                $reader = new Xls();
                break;
            case 'csv':
                $reader = new Csv();
                $reader->setInputEncoding('UTF-8');
                $reader->setDelimiter(',');
                $reader->setEnclosure('"');
                break;
            default:
                throw new \Exception('Unsupported file format');
        }

        // Configure reader for maximum performance
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);

        // Use read filter for XLSX files
        if ($extension === 'xlsx') {
            $reader->setReadFilter(new ReadFilter());
        }

        // Load spreadsheet
        $spreadsheet = $reader->load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();

        // Debug: Check if worksheet has data
        $highestRow = $worksheet->getHighestRow();
        log_message('info', "Excel file loaded. Highest row: {$highestRow}");

        // Use streaming approach for large files
        $allData = $this->readDataStreamingApproach($worksheet, $highestRow);

        log_message('info', "Data processed. Total records: " . count($allData));

        if (!empty($allData)) {
            // Insert data in smaller batches to prevent memory issues
            $result = $this->serviceTicketModel->insertBatch($allData, null, 500);
            log_message('info', "Insert batch result: {$result}");
        }

        // Clean up
        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet, $worksheet, $reader);

        // Re-enable garbage collection
        gc_enable();
        gc_collect_cycles();

        return count($allData ?? []);
    }

    /**
     * Streaming approach to handle large Excel files
     */
    private function readDataStreamingApproach($worksheet, $highestRow)
    {
        log_message('info', "Using streaming approach for large file processing");

        $data = [];
        $processedCount = 0;
        $batchSize = 1000; // Process in smaller batches

        // Process data in chunks to prevent memory exhaustion
        for ($startRow = 2; $startRow <= $highestRow; $startRow += $batchSize) {
            $endRow = min($startRow + $batchSize - 1, $highestRow);
            $range = "A{$startRow}:BX{$endRow}";

            try {
                log_message('info', "Processing range: {$range}");
                $rangeData = $worksheet->rangeToArray($range, null, false, false, false);

                if (!empty($rangeData)) {
                    foreach ($rangeData as $index => $rowData) {
                        $actualRowNumber = $startRow + $index;

                        // Skip empty rows
                        if (empty(array_filter($rowData))) {
                            continue;
                        }

                        $mappedData = $this->mapExcelRowToDatabase($rowData, $actualRowNumber);

                        if (!empty($mappedData['ticket_id'])) {
                            $data[] = $mappedData;
                            $processedCount++;
                        }

                        // Process and insert data in smaller chunks to free memory
                        if (count($data) >= 500) {
                            $this->serviceTicketModel->insertBatch($data, null, 500);
                            log_message('info', "Inserted batch of " . count($data) . " records");
                            $data = []; // Clear array to free memory

                            // Force garbage collection
                            gc_collect_cycles();
                        }
                    }
                }

                // Clear memory for large files
                unset($rangeData);
            } catch (\Exception $e) {
                log_message('error', "Error reading range {$range}: " . $e->getMessage());
                continue;
            }
        }

        log_message('info', "Streaming approach total records: {$processedCount}");
        return $data; // Return remaining data
    }

    /**
     * Compatible data reading method with enhanced debugging
     */
    private function readAllDataCompatible($worksheet)
    {
        $highestRow = $worksheet->getHighestRow();
        $highestColumn = $worksheet->getHighestColumn();
        $data = [];

        log_message('info', "Reading Excel: Rows={$highestRow}, Columns={$highestColumn}");

        // Convert column letter to number for better debugging
        $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn);
        log_message('info', "Highest column index: {$highestColumnIndex}");

        // Get all data in one go to see the actual structure
        try {
            $allDataArray = $worksheet->toArray();
            if (!empty($allDataArray)) {
                $firstRowCount = count($allDataArray[0]);
                log_message('info', "First row count from toArray(): {$firstRowCount}");
                log_message('info', "First row sample: " . json_encode(array_slice($allDataArray[0], 0, 10)));

                if (isset($allDataArray[1])) {
                    $secondRowCount = count($allDataArray[1]);
                    log_message('info', "Second row count: {$secondRowCount}");
                    log_message('info', "Second row sample: " . json_encode(array_slice($allDataArray[1], 0, 10)));
                }

                // Use toArray() data since it reads all columns correctly
                log_message('info', "Using toArray() method since it reads all columns correctly");

                // Process data starting from row 2 (skip header)
                for ($i = 1; $i < count($allDataArray); $i++) {
                    $rowData = $allDataArray[$i];

                    // Debug first few rows
                    if ($i <= 5) {
                        log_message('info', "Row " . ($i + 1) . " column count: " . count($rowData));
                        log_message('info', "Row " . ($i + 1) . " first 10 values: " . json_encode(array_slice($rowData, 0, 10)));
                    }

                    // Skip empty rows
                    if (empty(array_filter($rowData))) {
                        continue;
                    }

                    // Map columns to database fields
                    $mappedData = $this->mapExcelRowToDatabase($rowData, $i + 1);

                    if (!empty($mappedData['ticket_id'])) {
                        $data[] = $mappedData;

                        // Debug first few mapped rows
                        if ($i <= 3) {
                            $filledFields = array_filter($mappedData, function ($value) {
                                return $value !== null && $value !== '';
                            });
                            log_message('info', "Row " . ($i + 1) . " mapped fields: " . count($filledFields) . " out of 76");
                        }
                    } else {
                        log_message('warning', "Row " . ($i + 1) . " skipped - no ticket_id found");
                    }
                }

                log_message('info', "Total processed records: " . count($data));
                return $data;
            }
        } catch (\Exception $e) {
            log_message('error', "Error using toArray(): " . $e->getMessage());
        }

        // If toArray() fails, fall back to the alternative method
        log_message('warning', "toArray() failed, using alternative reading method");
        return $this->readDataAlternativeMethod($worksheet, $highestRow);
    }

    /**
     * Alternative reading method using range - updated to process all rows
     */
    private function readDataAlternativeMethod($worksheet, $highestRow)
    {
        log_message('info', "Using alternative reading method for all {$highestRow} rows");

        // Read all data in batches to handle large files
        $batchSize = 1000;
        $data = [];

        for ($startRow = 2; $startRow <= $highestRow; $startRow += $batchSize) {
            $endRow = min($startRow + $batchSize - 1, $highestRow);
            $range = "A{$startRow}:BX{$endRow}";

            try {
                log_message('info', "Reading range: {$range}");
                $rangeData = $worksheet->rangeToArray($range, null, false, false, false);

                if (!empty($rangeData)) {
                    log_message('info', "Range batch - first row count: " . count($rangeData[0]));

                    // Process each row in this batch
                    foreach ($rangeData as $index => $rowData) {
                        $actualRowNumber = $startRow + $index;

                        // Skip empty rows
                        if (empty(array_filter($rowData))) {
                            continue;
                        }

                        $mappedData = $this->mapExcelRowToDatabase($rowData, $actualRowNumber);

                        if (!empty($mappedData['ticket_id'])) {
                            $data[] = $mappedData;
                        }
                    }
                }

                // Clear memory for large files
                unset($rangeData);
            } catch (\Exception $e) {
                log_message('error', "Error reading range {$range}: " . $e->getMessage());
                continue;
            }
        }

        log_message('info', "Alternative method total records: " . count($data));
        return $data;
    }

    private function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size);
        $size = preg_replace('/[^0-9\.]/', '', $size);

        if ($unit) {
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    private function formatDate($date)
    {
        if (empty($date)) {
            return null;
        }

        // Optimized date formatting
        if (is_numeric($date) && $date > 1 && $date < 2958466) {
            $unix_date = (int)(($date - 25569) * 86400);
            return $unix_date > 0 ? date('Y-m-d', $unix_date) : null;
        }

        if (is_string($date)) {
            $date = trim($date);
            if (empty($date)) return null;

            // Quick date parsing
            $timestamp = strtotime($date);
            return $timestamp !== false ? date('Y-m-d', $timestamp) : null;
        }

        return null;
    }

    /**
     * Enhanced datetime formatting
     */
    private function formatDateTime($date)
    {
        if (empty($date) || $date === '-' || $date === 'null') {
            return null;
        }

        // Remove quotes
        if (is_string($date) && preg_match('/^\'(.*)\'$/', $date, $matches)) {
            $date = $matches[1];
        }

        // Handle Excel date format
        if (is_numeric($date) && $date > 1 && $date < 2958466) {
            $unix_date = (int)(($date - 25569) * 86400);
            return $unix_date > 0 ? date('Y-m-d H:i:s', $unix_date) : null;
        }

        if (is_string($date)) {
            $date = trim($date);
            if (empty($date)) return null;

            // Handle DD.MM.YYYY H:i:s format
            if (preg_match('/^(\d{2})\.(\d{2})\.(\d{4}) (\d{1,2}):(\d{2}):(\d{2})$/', $date, $matches)) {
                $day = $matches[1];
                $month = $matches[2];
                $year = $matches[3];
                $hour = $matches[4];
                $minute = $matches[5];
                $second = $matches[6];

                return "{$year}-{$month}-{$day} {$hour}:{$minute}:{$second}";
            }

            $timestamp = strtotime($date);
            return $timestamp !== false ? date('Y-m-d H:i:s', $timestamp) : null;
        }

        return null;
    }

    /**
     * Map Excel row data to database fields with corrected field names
     */
    private function mapExcelRowToDatabase($rowData, $rowNumber = 0)
    {
        $totalColumns = count($rowData);

        // Create mapping with correct field names based on Excel header
        $mappedData = [
            'ticket_id' => $this->cleanValue($this->getArrayValue($rowData, 0)),
            'subject' => $this->cleanValue($this->getArrayValue($rowData, 1)),
            'remark' => $this->cleanValue($this->getArrayValue($rowData, 2)),
            'priority_id' => $this->cleanValue($this->getArrayValue($rowData, 3)),
            'priority_name' => $this->cleanValue($this->getArrayValue($rowData, 4)),
            'ticket_status_name' => $this->cleanValue($this->getArrayValue($rowData, 5)),
            'unit_id' => $this->cleanNumericValue($this->getArrayValue($rowData, 6)),
            'unit_name' => $this->cleanValue($this->getArrayValue($rowData, 7)),
            'informant_id' => $this->cleanValue($this->getArrayValue($rowData, 8)),
            'informant_name' => $this->cleanValue($this->getArrayValue($rowData, 9)),
            'informant_hp' => $this->cleanValue($this->getArrayValue($rowData, 10)),
            'informant_email' => $this->cleanValue($this->getArrayValue($rowData, 11)),
            'customer_id' => $this->cleanValue($this->getArrayValue($rowData, 12)),
            'customer_name' => $this->cleanValue($this->getArrayValue($rowData, 13)),
            'customer_hp' => $this->cleanValue($this->getArrayValue($rowData, 14)),
            'customer_email' => $this->cleanValue($this->getArrayValue($rowData, 15)),
            'date_origin_interaction' => $this->formatDateTime($this->getArrayValue($rowData, 16)),
            'date_start_interaction' => $this->formatDateTime($this->getArrayValue($rowData, 17)),
            'date_open' => $this->formatDateTime($this->getArrayValue($rowData, 18)),
            'date_close' => $this->formatDateTime($this->getArrayValue($rowData, 19)),
            'date_last_update' => $this->formatDateTime($this->getArrayValue($rowData, 20)),
            'is_escalated' => $this->cleanValue($this->getArrayValue($rowData, 21)),
            'created_by_name' => $this->cleanValue($this->getArrayValue($rowData, 22)),
            'updated_by_name' => $this->cleanValue($this->getArrayValue($rowData, 23)),
            'channel_id' => $this->cleanNumericValue($this->getArrayValue($rowData, 24)),
            'session_id' => $this->cleanValue($this->getArrayValue($rowData, 25)),
            'category_id' => $this->cleanNumericValue($this->getArrayValue($rowData, 26)),
            'category_name' => $this->cleanValue($this->getArrayValue($rowData, 27)),
            'date_created_at' => $this->formatDateTime($this->getArrayValue($rowData, 28)),
            'sla' => $this->cleanValue($this->getArrayValue($rowData, 29)),
            'channel_name' => $this->cleanValue($this->getArrayValue($rowData, 30)),
            'main_category' => $this->cleanValue($this->getArrayValue($rowData, 31)),
            'category' => $this->cleanValue($this->getArrayValue($rowData, 32)),
            'sub_category' => $this->cleanValue($this->getArrayValue($rowData, 33)),
            'detail_sub_category' => $this->cleanValue($this->getArrayValue($rowData, 34)),
            'detail_sub_category2' => $this->cleanValue($this->getArrayValue($rowData, 35)),
            'regional' => $this->cleanNumericValue($this->getArrayValue($rowData, 36)),
            'type_queue_priority' => $this->cleanValue($this->getArrayValue($rowData, 37)),
            'group_id' => $this->cleanNumericValue($this->getArrayValue($rowData, 38)),
            'group_name' => $this->cleanValue($this->getArrayValue($rowData, 39)),
            'date_first_pickup_interaction' => $this->formatDateTime($this->getArrayValue($rowData, 40)),
            'status_case' => $this->cleanValue($this->getArrayValue($rowData, 41)),
            'indihome_num' => $this->cleanNumericValue($this->getArrayValue($rowData, 42)),
            'witel' => $this->cleanValue($this->getArrayValue($rowData, 43)),
            'feedback' => $this->cleanValue($this->getArrayValue($rowData, 44)),
            'date_first_response_interaction' => $this->formatDateTime($this->getArrayValue($rowData, 45)),
            'date_pickup_interaction' => $this->formatDateTime($this->getArrayValue($rowData, 46)),
            'date_end_interaction' => $this->formatDateTime($this->getArrayValue($rowData, 47)),
            'case_in' => $this->cleanNumericValue($this->getArrayValue($rowData, 48)),
            'case_out' => $this->cleanNumericValue($this->getArrayValue($rowData, 49)),
            'account' => $this->cleanValue($this->getArrayValue($rowData, 50)),
            'account_name' => $this->cleanValue($this->getArrayValue($rowData, 51)),
            'informant_member_id' => $this->cleanValue($this->getArrayValue($rowData, 52)),
            'customer_member_id' => $this->cleanValue($this->getArrayValue($rowData, 53)),
            'shift' => $this->cleanValue($this->getArrayValue($rowData, 54)),
            'status_date' => $this->cleanValue($this->getArrayValue($rowData, 55)),
            'sentiment_incoming' => $this->cleanNumericValue($this->getArrayValue($rowData, 56)),
            'sentiment_outgoing' => $this->cleanNumericValue($this->getArrayValue($rowData, 57)),
            'sentiment_all' => $this->cleanNumericValue($this->getArrayValue($rowData, 58)),
            'sentiment_service' => $this->cleanNumericValue($this->getArrayValue($rowData, 59)),
            'parent_id' => $this->cleanValue($this->getArrayValue($rowData, 60)),
            'count_merged' => $this->cleanNumericValue($this->getArrayValue($rowData, 61)),
            'source_id' => $this->cleanNumericValue($this->getArrayValue($rowData, 62)),
            'source_name' => $this->cleanValue($this->getArrayValue($rowData, 63)),
            'msisdn' => $this->cleanValue($this->getArrayValue($rowData, 64)),
            'from_id' => $this->cleanValue($this->getArrayValue($rowData, 65)),
            'from_username' => $this->cleanValue($this->getArrayValue($rowData, 66)),
            'ticket_id_digipos' => $this->cleanValue($this->getArrayValue($rowData, 67)),
            'ticket_customer_consent' => $this->cleanValue($this->getArrayValue($rowData, 68)),
            'ticket_no_indi_home_alternatif' => $this->cleanValue($this->getArrayValue($rowData, 69)),
            'sla_second' => $this->cleanValue($this->getArrayValue($rowData, 70)),
            'informant_1' => $this->cleanValue($this->getArrayValue($rowData, 71)),
            'informant_2' => $this->cleanValue($this->getArrayValue($rowData, 72)),
            'customer_1' => $this->cleanValue($this->getArrayValue($rowData, 73)),
            'customer_2' => $this->cleanValue($this->getArrayValue($rowData, 74)),
            'ticket_no_k_t_p' => $this->cleanValue($this->getArrayValue($rowData, 75)),
        ];

        return $mappedData;
    }

    /**
     * Safely get array value by index
     */
    private function getArrayValue($array, $index)
    {
        return isset($array[$index]) ? $array[$index] : null;
    }

    /**
     * Enhanced clean value function to handle various data formats
     */
    private function cleanValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convert to string and trim
        $cleaned = trim((string)$value);

        // Handle specific values that should be null
        if (in_array(strtolower($cleaned), ['-', 'null', "'null'", '\'null\'', 'undefined', "'undefined'"])) {
            return null;
        }

        // Remove quotes from values like '38474540'
        if (preg_match('/^\'(.*)\'$/', $cleaned, $matches)) {
            $cleaned = $matches[1];
        }

        return $cleaned === '' ? null : $cleaned;
    }

    /**
     * Enhanced numeric value cleaning
     */
    private function cleanNumericValue($value)
    {
        if (empty($value) || $value === '-' || $value === 'null') {
            return null;
        }

        // Remove quotes
        if (is_string($value) && preg_match('/^\'(.*)\'$/', $value, $matches)) {
            $value = $matches[1];
        }

        // Handle comma as decimal separator (European format)
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
        }

        return is_numeric($value) ? (float)$value : null;
    }

    public function template()
    {
        // Check if user is admin
        $redirect = $this->requireAdmin();
        if ($redirect) {
            return $redirect;
        }

        $filepath = FCPATH . 'assets/template/service_ticket_template.xlsx';

        if (file_exists($filepath)) {
            return $this->response->download($filepath, null);
        }

        return redirect()->to('/import')->with('error', 'Template file tidak ditemukan');
    }
}

/**
 * Updated read filter for more columns
 */
class ReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // Read all columns from A to CZ (covering up to 100+ columns)
        return true; // Read all cells for maximum compatibility
    }
}
