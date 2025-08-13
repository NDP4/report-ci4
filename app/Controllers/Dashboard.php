<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ServiceTicketModel;
use App\Models\ActivityLogModel;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $serviceTicketModel;
    protected $activityLogModel;

    public function __construct()
    {
        $this->serviceTicketModel = new ServiceTicketModel();
        $this->activityLogModel = new ActivityLogModel();
    }

    public function index()
    {
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

        $channels = $this->serviceTicketModel->select('channel_name')
            ->where('channel_name IS NOT NULL')
            ->where('channel_name !=', '')
            ->groupBy('channel_name')
            ->orderBy('channel_name', 'ASC')
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
            'channels' => $channels,
            'witels' => $witels
        ];

        return view('dashboard/index', $data);
    }

    /**
     * API endpoint to get categories filtered by main category
     */
    public function getCategoriesByMainCategory()
    {
        $mainCategory = $this->request->getPost('main_category');

        if (empty($mainCategory)) {
            // Return all categories if no main category selected
            $categories = $this->serviceTicketModel->select('category')
                ->where('category IS NOT NULL')
                ->where('category !=', '')
                ->groupBy('category')
                ->orderBy('category', 'ASC')
                ->findAll();
        } else {
            // Return categories filtered by main category
            $categories = $this->serviceTicketModel->select('category')
                ->where('main_category', $mainCategory)
                ->where('category IS NOT NULL')
                ->where('category !=', '')
                ->groupBy('category')
                ->orderBy('category', 'ASC')
                ->findAll();
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $categories
        ]);
    }

    /**
     * API endpoint for chart data
     */
    public function getChartData()
    {
        $request = $this->request;

        // Get filters from request
        $dateStart = $request->getGet('date_start');
        $dateEnd = $request->getGet('date_end');
        $mainCategory = $request->getGet('main_category');
        $category = $request->getGet('category');
        $channel = $request->getGet('channel');
        $witel = $request->getGet('witel');

        try {
            // Get status distribution
            $statusData = $this->getStatusDistribution($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);

            // Get monthly trend
            $monthlyData = $this->getMonthlyTrendData($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);

            // Get category distribution
            $categoryData = $this->getCategoryDistribution($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);

            // Get witel distribution
            $witelData = $this->getWitelDistribution($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);

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

    private function getStatusDistribution($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel)
    {
        $builder = $this->serviceTicketModel->builder();
        $builder->select('ticket_status_name, COUNT(*) as count');
        $this->applyFilters($builder, $dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);
        $builder->groupBy('ticket_status_name');
        $builder->orderBy('count', 'DESC');

        return $builder->get()->getResultArray();
    }

    private function getMonthlyTrendData($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel)
    {
        $builder = $this->serviceTicketModel->builder();
        $builder->select("DATE_FORMAT(date_open, '%Y-%m') as month, COUNT(*) as count");
        $this->applyFilters($builder, $dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);
        $builder->groupBy('month');
        $builder->orderBy('month', 'ASC');
        $builder->limit(12);

        return $builder->get()->getResultArray();
    }

    private function getCategoryDistribution($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel)
    {
        $builder = $this->serviceTicketModel->builder();
        $builder->select('main_category, COUNT(*) as count');
        $this->applyFilters($builder, $dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);
        $builder->where('main_category IS NOT NULL');
        $builder->where('main_category !=', '');
        $builder->groupBy('main_category');
        $builder->orderBy('count', 'DESC');
        $builder->limit(10);

        return $builder->get()->getResultArray();
    }

    private function getWitelDistribution($dateStart, $dateEnd, $mainCategory, $category, $channel, $witel)
    {
        $builder = $this->serviceTicketModel->builder();
        $builder->select('witel, COUNT(*) as count');
        $this->applyFilters($builder, $dateStart, $dateEnd, $mainCategory, $category, $channel, $witel);
        $builder->where('witel IS NOT NULL');
        $builder->where('witel !=', '');
        $builder->groupBy('witel');
        $builder->orderBy('count', 'DESC');
        $builder->limit(10);

        return $builder->get()->getResultArray();
    }

    private function applyFilters($builder, $dateStart, $dateEnd, $mainCategory, $category, $channel, $witel)
    {
        if (!empty($dateStart)) {
            $builder->where('date_open >=', $dateStart);
        }

        if (!empty($dateEnd)) {
            $builder->where('date_open <=', $dateEnd);
        }

        if (!empty($mainCategory)) {
            $builder->where('main_category', $mainCategory);
        }

        if (!empty($category)) {
            $builder->where('category', $category);
        }

        if (!empty($channel)) {
            $builder->where('channel_name', $channel);
        }

        if (!empty($witel)) {
            $builder->where('witel', $witel);
        }
    }

    private function checkAdminAccess()
    {
        if (session()->get('role') != 'admin') {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Access denied');
        }
    }

    public function activitylog()
    {
        $this->checkAdminAccess();

        // Get activity logs with user information
        $logs = $this->activityLogModel
            ->select('activity_log.*, users.username')
            ->join('users', 'users.id = activity_log.user_id', 'left')
            ->orderBy('activity_log.created_at', 'DESC')
            ->paginate(25);

        $data = [
            'title' => 'Activity Log',
            'logs' => $logs,
            'pager' => $this->activityLogModel->pager
        ];

        return view('dashboard/activitylog', $data);
    }

    public function exportActivityLog()
    {
        $this->checkAdminAccess();

        // Set memory limit for large exports
        ini_set('memory_limit', '512M');
        ini_set('max_execution_time', 300);

        // Get all activity logs with user information
        $logs = $this->activityLogModel
            ->select('activity_log.*, users.username')
            ->join('users', 'users.id = activity_log.user_id', 'left')
            ->orderBy('activity_log.created_at', 'DESC')
            ->findAll();

        // Create CSV content
        $filename = 'activity_log_' . date('Y-m-d_H-i-s') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Expires: 0');

        $output = fopen('php://output', 'w');

        // Add CSV headers
        fputcsv($output, [
            'ID',
            'Date/Time',
            'User ID',
            'Username',
            'Action',
            'Description'
        ]);

        // Add data rows
        foreach ($logs as $log) {
            fputcsv($output, [
                $log['id'],
                date('d/m/Y H:i:s', strtotime($log['created_at'])),
                $log['user_id'] ?? 'N/A',
                $log['username'] ?? 'System',
                $log['action'],
                $log['description']
            ]);
        }

        fclose($output);
        exit;
    }

    public function deleteActivityLog($id = null)
    {
        $this->checkAdminAccess();

        if (!$id) {
            return redirect()->to('dashboard/activitylog')->with('error', 'Invalid activity log ID');
        }

        $log = $this->activityLogModel->find($id);
        if (!$log) {
            return redirect()->to('dashboard/activitylog')->with('error', 'Activity log not found');
        }

        if ($this->activityLogModel->delete($id)) {
            return redirect()->to('dashboard/activitylog')->with('success', 'Activity log deleted successfully');
        } else {
            return redirect()->to('dashboard/activitylog')->with('error', 'Failed to delete activity log');
        }
    }

    public function bulkDeleteActivityLog()
    {
        $this->checkAdminAccess();

        $selectedIds = $this->request->getPost('selected_logs');

        if (empty($selectedIds) || !is_array($selectedIds)) {
            return redirect()->to('dashboard/activitylog')->with('error', 'No activity logs selected');
        }

        $deletedCount = 0;
        foreach ($selectedIds as $id) {
            if ($this->activityLogModel->delete($id)) {
                $deletedCount++;
            }
        }

        if ($deletedCount > 0) {
            return redirect()->to('dashboard/activitylog')->with('success', "Successfully deleted {$deletedCount} activity log(s)");
        } else {
            return redirect()->to('dashboard/activitylog')->with('error', 'Failed to delete selected activity logs');
        }
    }

    public function clearAllActivityLogs()
    {
        $this->checkAdminAccess();

        // Truncate the activity log table
        $this->activityLogModel->truncate();

        return redirect()->to('dashboard/activitylog')->with('success', 'All activity logs have been cleared');
    }

    public function import()
    {
        $this->checkAdminAccess();
        // ...existing code for import
    }

    public function user()
    {
        $this->checkAdminAccess();
        // ...existing code for user
    }

    public function createUser()
    {
        $this->checkAdminAccess();
        // ...existing code for creating user
    }

    public function editUser($id = null)
    {
        $this->checkAdminAccess();
        // ...existing code for editing user
    }

    public function deleteUser($id = null)
    {
        $this->checkAdminAccess();
        // ...existing code for deleting user
    }
}
