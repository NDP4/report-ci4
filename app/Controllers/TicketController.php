<?php

namespace App\Controllers;

use App\Models\ServiceTicketModel;
use CodeIgniter\HTTP\ResponseInterface;

class TicketController extends BaseController
{
    protected $serviceTicketModel;

    public function __construct()
    {
        // Check if user is logged in
        if (!session()->get('logged_in')) {
            header('Location: ' . base_url('/login'));
            exit();
        }

        $this->serviceTicketModel = new ServiceTicketModel();
    }

    public function index()
    {
        // Check if user is logged in
        $redirect = $this->requireAuth();
        if ($redirect) {
            return $redirect;
        }

        // Get basic statistics
        $totalRecords = $this->serviceTicketModel->countAll();
        $totalClosed = $this->serviceTicketModel->where('ticket_status_name', 'CLOSED')->countAllResults();
        $totalOpen = $this->serviceTicketModel->where('ticket_status_name !=', 'CLOSED')->countAllResults();

        // Get filter options
        $mainCategories = $this->serviceTicketModel->select('main_category')
            ->where('main_category IS NOT NULL')
            ->where('main_category !=', '')
            ->groupBy('main_category')
            ->orderBy('main_category', 'ASC')
            ->findAll();

        $categories = $this->serviceTicketModel->select('category')
            ->where('category IS NOT NULL')
            ->where('category !=', '')
            ->groupBy('category')
            ->orderBy('category', 'ASC')
            ->findAll();

        $witels = $this->serviceTicketModel->select('witel')
            ->where('witel IS NOT NULL')
            ->where('witel !=', '')
            ->groupBy('witel')
            ->orderBy('witel', 'ASC')
            ->findAll();

        $data = [
            'title' => 'Dashboard - Service Tickets Analytics',
            'breadcrumb' => [
                'Dashboard' => '/'
            ],
            'totalRecords' => $totalRecords,
            'totalClosed' => $totalClosed,
            'totalOpen' => $totalOpen,
            'mainCategories' => $mainCategories,
            'categories' => $categories,
            'witels' => $witels
        ];

        return view('dashboard/index', $data);
    }

    public function ticket()
    {
        // Check if user is logged in
        $redirect = $this->requireAuth();
        if ($redirect) {
            return $redirect;
        }

        // Get basic statistics
        $totalRecords = $this->serviceTicketModel->countAll();
        $totalClosed = $this->serviceTicketModel->where('ticket_status_name', 'CLOSED')->countAllResults();
        $totalOpen = $this->serviceTicketModel->where('ticket_status_name !=', 'CLOSED')->countAllResults();

        $data = [
            'title' => 'Service Tickets',
            'breadcrumb' => [
                'Dashboard' => '/',
                'Service Tickets' => 'tickets'
            ],
            'totalRecords' => $totalRecords,
            'totalClosed' => $totalClosed,
            'totalOpen' => $totalOpen
        ];

        return view('pages/ticket', $data);
    }

    /**
     * API endpoint for DataTables
     */
    public function getDataTables()
    {
        // Check if user is logged in
        $redirect = $this->requireAuth();
        if ($redirect) {
            return $redirect;
        }

        $request = $this->request;

        // DataTables parameters
        $draw = $request->getPost('draw');
        $start = $request->getPost('start') ?? 0;
        $length = $request->getPost('length') ?? 10;
        $searchValue = $request->getPost('search')['value'] ?? '';
        $orderColumnIndex = $request->getPost('order')[0]['column'] ?? 0;
        $orderDirection = $request->getPost('order')[0]['dir'] ?? 'desc';

        // Define columns
        $columns = [
            'ticket_id',
            'subject',
            'customer_name',
            'ticket_status_name',
            'priority_name',
            'witel',
            'main_category',
            'category',
            'date_created',
            'date_close',
            'created_by_name'
        ];

        $orderColumn = $columns[$orderColumnIndex] ?? 'ticket_id';

        // Build query
        $builder = $this->serviceTicketModel->builder();

        // Apply search if provided
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('ticket_id', $searchValue)
                ->orLike('subject', $searchValue)
                ->orLike('customer_name', $searchValue)
                ->orLike('ticket_status_name', $searchValue)
                ->orLike('priority_name', $searchValue)
                ->orLike('witel', $searchValue)
                ->orLike('main_category', $searchValue)
                ->orLike('category', $searchValue)
                ->orLike('created_by_name', $searchValue)
                ->groupEnd();
        }

        // Get total records (before filtering)
        $totalRecords = $this->serviceTicketModel->countAll();

        // Get filtered records count
        $filteredRecords = $builder->countAllResults(false);

        // Apply ordering and pagination
        $builder->orderBy($orderColumn, $orderDirection);
        $builder->limit($length, $start);

        // Get the data
        $data = $builder->get()->getResultArray();

        // Format data for DataTables
        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'ticket_id' => $row['ticket_id'] ?? '',
                'subject' => $this->truncateText($row['subject'] ?? '', 50),
                'customer_name' => $row['customer_name'] ?? '',
                'ticket_status_name' => $this->getStatusBadge($row['ticket_status_name'] ?? ''),
                'priority_name' => $this->getPriorityBadge($row['priority_name'] ?? ''),
                'witel' => $row['witel'] ?? '',
                'main_category' => $row['main_category'] ?? '',
                'category' => $row['category'] ?? '',
                'date_created' => $this->formatDate($row['date_start_interaction'] ?? ''),
                'date_close' => $this->formatDate($row['date_close'] ?? ''),
                'created_by_name' => $row['created_by_name'] ?? '',
                'actions' => $this->getActionButtons($row['ticket_id'] ?? '')
            ];
        }

        $response = [
            'draw' => intval($draw),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData
        ];

        return $this->response->setJSON($response);
    }

    /**
     * View detail ticket
     */
    public function detail($ticketId)
    {
        // Check if user is logged in
        $redirect = $this->requireAuth();
        if ($redirect) {
            return $redirect;
        }

        $ticket = $this->serviceTicketModel->find($ticketId);

        if (!$ticket) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Ticket not found');
        }

        $data = [
            'ticket' => $ticket
        ];

        return view('dashboard/detail', $data);
    }

    /**
     * Export data to Excel
     */
    public function export()
    {
        // Check if user is logged in
        $redirect = $this->requireAuth();
        if ($redirect) {
            return $redirect;
        }

        // Set memory limit for large exports
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 300);

        $searchValue = $this->request->getGet('search') ?? '';

        // Build query
        $builder = $this->serviceTicketModel->builder();

        // Apply search if provided
        if (!empty($searchValue)) {
            $builder->groupStart()
                ->like('ticket_id', $searchValue)
                ->orLike('subject', $searchValue)
                ->orLike('customer_name', $searchValue)
                ->orLike('ticket_status_name', $searchValue)
                ->orLike('witel', $searchValue)
                ->groupEnd();
        }

        $data = $builder->get()->getResultArray();

        // Create Excel file
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers
        $headers = [
            'A1' => 'Ticket ID',
            'B1' => 'Subject',
            'C1' => 'Customer Name',
            'D1' => 'Status',
            'E1' => 'Priority',
            'F1' => 'Witel',
            'G1' => 'Main Category',
            'H1' => 'Category',
            'I1' => 'Date Created',
            'J1' => 'Date Closed',
            'K1' => 'Created By'
        ];

        foreach ($headers as $cell => $header) {
            $sheet->setCellValue($cell, $header);
        }

        // Add data
        $row = 2;
        foreach ($data as $ticket) {
            $sheet->setCellValue('A' . $row, $ticket['ticket_id']);
            $sheet->setCellValue('B' . $row, $ticket['subject']);
            $sheet->setCellValue('C' . $row, $ticket['customer_name']);
            $sheet->setCellValue('D' . $row, $ticket['ticket_status_name']);
            $sheet->setCellValue('E' . $row, $ticket['priority_name']);
            $sheet->setCellValue('F' . $row, $ticket['witel']);
            $sheet->setCellValue('G' . $row, $ticket['main_category']);
            $sheet->setCellValue('H' . $row, $ticket['category']);
            $sheet->setCellValue('I' . $row, $ticket['date_start_interaction']);
            $sheet->setCellValue('J' . $row, $ticket['date_close']);
            $sheet->setCellValue('K' . $row, $ticket['created_by_name']);
            $row++;
        }

        // Set filename
        $filename = 'service_tickets_export_' . date('Y-m-d_H-i-s') . '.xlsx';

        // Create writer and save
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer->save('php://output');
        exit;
    }

    /**
     * Helper methods
     */
    private function truncateText($text, $length = 50)
    {
        if (strlen($text) > $length) {
            return substr($text, 0, $length) . '...';
        }
        return $text;
    }

    private function getStatusBadge($status)
    {
        $badgeClass = '';
        switch (strtoupper($status)) {
            case 'CLOSED':
                $badgeClass = 'success';
                break;
            case 'OPEN':
                $badgeClass = 'warning';
                break;
            case 'IN PROGRESS':
                $badgeClass = 'info';
                break;
            default:
                $badgeClass = 'secondary';
        }

        return '<span class="badge bg-' . $badgeClass . '">' . $status . '</span>';
    }

    private function getPriorityBadge($priority)
    {
        $badgeClass = '';
        switch (strtoupper($priority)) {
            case 'HIGH':
                $badgeClass = 'danger';
                break;
            case 'MEDIUM':
                $badgeClass = 'warning';
                break;
            case 'LOW':
                $badgeClass = 'info';
                break;
            default:
                $badgeClass = 'secondary';
        }

        return '<span class="badge bg-' . $badgeClass . '">' . $priority . '</span>';
    }

    private function formatDate($date)
    {
        if (empty($date)) return '-';

        try {
            return date('d/m/Y H:i', strtotime($date));
        } catch (\Exception $e) {
            return '-';
        }
    }

    private function getActionButtons($ticketId)
    {
        return '<div class="btn-group btn-group-sm" role="group">
                    <a href="' . base_url('dashboard/detail/' . $ticketId) . '" class="btn btn-outline-primary btn-sm" title="View Detail">
                        <i class="fas fa-eye"></i>
                    </a>
                </div>';
    }

    /**
     * API endpoint for chart data
     */
    public function getChartData()
    {
        // Check if user is logged in
        $redirect = $this->requireAuth();
        if ($redirect) {
            return $redirect;
        }

        $request = $this->request;

        // Get filters from request
        $dateStart = $request->getGet('date_start');
        $dateEnd = $request->getGet('date_end');
        $mainCategory = $request->getGet('main_category');
        $category = $request->getGet('category');
        $witel = $request->getGet('witel');

        try {
            // Build base query
            $builder = $this->serviceTicketModel->builder();

            // Apply filters for status data
            if (!empty($dateStart) && !empty($dateEnd)) {
                $builder->where('date_start_interaction >=', $dateStart)
                    ->where('date_start_interaction <=', $dateEnd . ' 23:59:59');
            }

            if (!empty($mainCategory)) {
                $builder->where('main_category', $mainCategory);
            }

            if (!empty($category)) {
                $builder->where('category', $category);
            }

            if (!empty($witel)) {
                $builder->where('witel', $witel);
            }

            // Clone the builder for status data to avoid interference
            $statusBuilder = clone $builder;
            $statusData = $statusBuilder->select('ticket_status_name, COUNT(*) as count')
                ->groupBy('ticket_status_name')
                ->get()
                ->getResultArray();

            // Get monthly trend data
            $monthlyData = $this->getMonthlyTrendData($dateStart, $dateEnd, $mainCategory, $category, $witel);

            // Get category distribution
            $categoryData = $this->getCategoryDistribution($dateStart, $dateEnd, $mainCategory, $category, $witel);

            // Get witel distribution
            $witelData = $this->getWitelDistribution($dateStart, $dateEnd, $mainCategory, $category, $witel);

            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'statusDistribution' => $statusData,
                    'monthlyTrend' => $monthlyData,
                    'categoryDistribution' => $categoryData,
                    'witelDistribution' => $witelData
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    private function getMonthlyTrendData($dateStart, $dateEnd, $mainCategory, $category, $witel)
    {
        $builder = $this->serviceTicketModel->builder();

        // Apply filters
        if (!empty($dateStart) && !empty($dateEnd)) {
            $builder->where('date_start_interaction >=', $dateStart)
                ->where('date_start_interaction <=', $dateEnd . ' 23:59:59');
        }

        if (!empty($mainCategory)) {
            $builder->where('main_category', $mainCategory);
        }

        if (!empty($category)) {
            $builder->where('category', $category);
        }

        if (!empty($witel)) {
            $builder->where('witel', $witel);
        }

        return $builder->select("DATE_FORMAT(date_start_interaction, '%Y-%m') as month, COUNT(*) as count")
            ->groupBy("DATE_FORMAT(date_start_interaction, '%Y-%m')")
            ->orderBy("month", 'ASC')
            ->limit(12)
            ->get()
            ->getResultArray();
    }

    private function getCategoryDistribution($dateStart, $dateEnd, $mainCategory, $category, $witel)
    {
        $builder = $this->serviceTicketModel->builder();

        // Apply filters
        if (!empty($dateStart) && !empty($dateEnd)) {
            $builder->where('date_start_interaction >=', $dateStart)
                ->where('date_start_interaction <=', $dateEnd . ' 23:59:59');
        }

        if (!empty($mainCategory)) {
            $builder->where('main_category', $mainCategory);
        }

        if (!empty($category)) {
            $builder->where('category', $category);
        }

        if (!empty($witel)) {
            $builder->where('witel', $witel);
        }

        return $builder->select('main_category, COUNT(*) as count')
            ->where('main_category IS NOT NULL')
            ->where('main_category !=', '')
            ->groupBy('main_category')
            ->orderBy('count', 'DESC')
            ->limit(10)
            ->get()
            ->getResultArray();
    }

    private function getWitelDistribution($dateStart, $dateEnd, $mainCategory, $category, $witel)
    {
        $builder = $this->serviceTicketModel->builder();

        // Apply filters
        if (!empty($dateStart) && !empty($dateEnd)) {
            $builder->where('date_start_interaction >=', $dateStart)
                ->where('date_start_interaction <=', $dateEnd . ' 23:59:59');
        }

        if (!empty($mainCategory)) {
            $builder->where('main_category', $mainCategory);
        }

        if (!empty($category)) {
            $builder->where('category', $category);
        }

        if (!empty($witel)) {
            $builder->where('witel', $witel);
        }

        return $builder->select('witel, COUNT(*) as count')
            ->where('witel IS NOT NULL')
            ->where('witel !=', '')
            ->groupBy('witel')
            ->orderBy('count', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();
    }
}
