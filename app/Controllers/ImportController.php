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
    private $batchSize = 1000; // Increase batch size for faster processing

    public function __construct()
    {
        $this->serviceTicketModel = new ServiceTicketModel();
    }

    public function index()
    {
        // Get server limits for display
        $uploadMaxSize = ini_get('upload_max_filesize');
        $postMaxSize = ini_get('post_max_size');
        $memoryLimit = ini_get('memory_limit');

        $data = [
            'uploadMaxSize' => $uploadMaxSize,
            'postMaxSize' => $postMaxSize,
            'memoryLimit' => $memoryLimit,
            'postMaxSizeBytes' => $this->parseSize($postMaxSize) // Add parsed size in bytes
        ];

        return view('import/index', $data);
    }

    public function upload()
    {
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
        // Optimize memory and performance settings
        ini_set('memory_limit', '2048M');
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

        // Use the old method but optimized for better compatibility
        $allData = $this->readAllDataCompatible($worksheet);

        log_message('info', "Data processed. Total records: " . count($allData));

        if (!empty($allData)) {
            // Insert all data in optimized batches
            $result = $this->serviceTicketModel->insertBatch($allData, null, 1000);
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
     * Compatible data reading method that works like the old version
     */
    private function readAllDataCompatible($worksheet)
    {
        $highestRow = $worksheet->getHighestRow();
        $data = [];

        // Start from row 2 (skip header) and read row by row like before
        for ($row = 2; $row <= $highestRow; $row++) {
            try {
                // Read row data the old way - cell by cell for compatibility
                $rowData = [
                    $worksheet->getCell('A' . $row)->getValue(),
                    $worksheet->getCell('B' . $row)->getValue(),
                    $worksheet->getCell('C' . $row)->getValue(),
                    $worksheet->getCell('D' . $row)->getValue(),
                    $worksheet->getCell('E' . $row)->getValue(),
                    $worksheet->getCell('F' . $row)->getValue(),
                    $worksheet->getCell('G' . $row)->getValue(),
                    $worksheet->getCell('H' . $row)->getValue(),
                    $worksheet->getCell('I' . $row)->getValue(),
                    $worksheet->getCell('J' . $row)->getValue(),
                ];

                // Skip empty rows
                if (empty(array_filter($rowData))) {
                    continue;
                }

                $data[] = [
                    'ticket_number' => $this->cleanValue($rowData[0]),
                    'date_created' => $this->formatDate($rowData[1]),
                    'customer_name' => $this->cleanValue($rowData[2]),
                    'service_type' => $this->cleanValue($rowData[3]),
                    'priority' => $this->cleanValue($rowData[4]),
                    'status' => $this->cleanValue($rowData[5]),
                    'assigned_to' => $this->cleanValue($rowData[6]),
                    'description' => $this->cleanValue($rowData[7]),
                    'resolution' => $this->cleanValue($rowData[8]),
                    'date_resolved' => $this->formatDate($rowData[9]),
                ];

                // Debug: Log first few rows
                if ($row <= 5) {
                    log_message('info', "Row {$row} data: " . json_encode($rowData));
                }
            } catch (\Exception $e) {
                log_message('error', "Error reading row {$row}: " . $e->getMessage());
                continue;
            }
        }

        return $data;
    }

    /**
     * Alternative: Keep the old method completely for troubleshooting
     */
    private function readAllDataOptimized($worksheet)
    {
        $highestRow = $worksheet->getHighestRow();

        // If only header row or empty, return empty
        if ($highestRow <= 1) {
            log_message('info', "File appears to be empty or only has header row");
            return [];
        }

        $data = [];

        try {
            // Read range data more efficiently
            $range = 'A2:J' . $highestRow;
            log_message('info', "Reading range: {$range}");

            $allRows = $worksheet->rangeToArray($range, null, false, false, false);
            log_message('info', "Range read successfully. Rows count: " . count($allRows));

            foreach ($allRows as $index => $rowData) {
                // Skip empty rows quickly
                if (empty(array_filter($rowData))) {
                    continue;
                }

                $data[] = [
                    'ticket_number' => $this->cleanValue($rowData[0]),
                    'date_created' => $this->formatDate($rowData[1]),
                    'customer_name' => $this->cleanValue($rowData[2]),
                    'service_type' => $this->cleanValue($rowData[3]),
                    'priority' => $this->cleanValue($rowData[4]),
                    'status' => $this->cleanValue($rowData[5]),
                    'assigned_to' => $this->cleanValue($rowData[6]),
                    'description' => $this->cleanValue($rowData[7]),
                    'resolution' => $this->cleanValue($rowData[8]),
                    'date_resolved' => $this->formatDate($rowData[9]),
                ];

                // Debug first few rows
                if ($index < 3) {
                    log_message('info', "Processing row " . ($index + 2) . ": " . json_encode($rowData));
                }
            }
        } catch (\Exception $e) {
            log_message('error', "Error in readAllDataOptimized: " . $e->getMessage());
            // Fallback to compatible method
            return $this->readAllDataCompatible($worksheet);
        }

        return $data;
    }

    private function cleanValue($value)
    {
        // Optimized cleaning function with proper parentheses
        return ($value === null || $value === '') ? null : (trim((string)$value) ?: null);
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

    public function template()
    {
        $filepath = FCPATH . 'assets/template/service_ticket_template.xlsx';

        if (file_exists($filepath)) {
            return $this->response->download($filepath, null);
        }

        return redirect()->to('/import')->with('error', 'Template file tidak ditemukan');
    }
}

/**
 * Simple read filter class for memory optimization
 */
class ReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
    public function readCell(string $columnAddress, int $row, string $worksheetName = ''): bool
    {
        // Only read columns A through J
        if (in_array($columnAddress, ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'])) {
            return true;
        }
        return false;
    }
}
