<?php

namespace App\Controllers;

use App\Models\AgentBucketModel;
use App\Models\QueueOnxModel;
use App\Models\ReportAgentLogModel;
use App\Models\PresensiModel;

class AuxDashboardController extends BaseController
{
    protected $agentBucketModel;
    protected $queueOnxModel;
    protected $reportAgentLogModel;
    protected $presensiModel;

    public function __construct()
    {
        $this->agentBucketModel = new AgentBucketModel();
        $this->queueOnxModel = new QueueOnxModel();
        $this->reportAgentLogModel = new ReportAgentLogModel();
        $this->presensiModel = new PresensiModel();
    }

    public function index()
    {
        try {
            $workDate = $this->request->getGet('date') ?? date('Y-m-d');
            $bucket = $this->request->getGet('bucket');

            // Get agent bucket data
            $agentBuckets = $this->agentBucketModel->getByDate($workDate, $bucket);

            // Get bucket statistics
            $bucketStats = $this->agentBucketModel->getBucketStats($workDate);

            // Get queue statistics
            $witelStats = $this->queueOnxModel->getWitelStats($workDate);
            $mainCategoryStats = $this->queueOnxModel->getMainCategoryStats($workDate);

            // Get AUX details
            $auxDetails = $this->reportAgentLogModel->getDetailsByDate($workDate);

            // Prepare chart data
            $chartData = $this->prepareChartData($bucketStats, $witelStats, $mainCategoryStats);

            return view('aux/dashboard', [
                'title' => 'Dashboard AUX',
                'workDate' => $workDate,
                'selectedBucket' => $bucket,
                'agentBuckets' => $agentBuckets,
                'bucketStats' => $bucketStats,
                'auxDetails' => $auxDetails,
                'chartData' => $chartData
            ]);
        } catch (\Exception $e) {
            log_message('error', 'Error in AuxDashboardController index: ' . $e->getMessage());

            // Return safe defaults when error occurs
            return view('aux/dashboard', [
                'title' => 'Dashboard AUX',
                'workDate' => date('Y-m-d'),
                'selectedBucket' => null,
                'agentBuckets' => [],
                'bucketStats' => [],
                'auxDetails' => [],
                'chartData' => ['buckets' => [], 'witel' => [], 'mainCategory' => []]
            ]);
        }
    }

    public function filter()
    {
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $bucket = $this->request->getPost('bucket');

        if (!$startDate || !$endDate) {
            return redirect()->back()->with('error', 'Tanggal harus diisi');
        }

        // Get filtered data
        $agentBuckets = $this->agentBucketModel->getDateRange($startDate, $endDate);

        if ($bucket) {
            $agentBuckets = array_filter($agentBuckets, function ($item) use ($bucket) {
                return $item['bucket'] === $bucket;
            });
        }

        // Group by date for statistics
        $dateStats = [];
        foreach ($agentBuckets as $item) {
            $date = $item['work_date'];
            if (!isset($dateStats[$date])) {
                $dateStats[$date] = [];
            }

            $bucketType = $item['bucket'];
            if (!isset($dateStats[$date][$bucketType])) {
                $dateStats[$date][$bucketType] = 0;
            }
            $dateStats[$date][$bucketType]++;
        }

        return view('aux/dashboard_filtered', [
            'title' => 'Dashboard AUX - Filtered',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedBucket' => $bucket,
            'agentBuckets' => $agentBuckets,
            'dateStats' => $dateStats
        ]);
    }

    public function computeBuckets()
    {
        $workDate = $this->request->getPost('work_date') ?? date('Y-m-d');

        $result = $this->agentBucketModel->computeBucketsForDate($workDate);

        if ($result) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Buckets berhasil dihitung untuk tanggal ' . $workDate
            ]);
        } else {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Gagal menghitung buckets'
            ]);
        }
    }

    public function getAuxDetails()
    {
        $workDate = $this->request->getGet('date') ?? date('Y-m-d');
        $fullnameNorm = $this->request->getGet('name');

        if ($fullnameNorm) {
            $auxDetails = $this->reportAgentLogModel->getDetailsByDateAndName($workDate, $fullnameNorm);
        } else {
            $auxDetails = $this->reportAgentLogModel->getDetailsByDate($workDate);
        }

        return $this->response->setJSON([
            'success' => true,
            'data' => $auxDetails
        ]);
    }

    public function exportData()
    {
        $workDate = $this->request->getGet('date') ?? date('Y-m-d');
        $format = $this->request->getGet('format') ?? 'csv';

        $agentBuckets = $this->agentBucketModel->getByDate($workDate);

        if ($format === 'csv') {
            return $this->exportToCsv($agentBuckets, $workDate);
        } else {
            return $this->exportToExcel($agentBuckets, $workDate);
        }
    }

    private function prepareChartData($bucketStats, $witelStats, $mainCategoryStats)
    {
        // Prepare bucket chart data
        $bucketLabels = [];
        $bucketData = [];
        $bucketColors = [
            '1' => '#28a745',
            '2' => '#ffc107',
            '3' => '#dc3545',
            'idle' => '#17a2b8',
            'anomali' => '#6c757d',
            'absent' => '#343a40'
        ];

        foreach ($bucketStats as $stat) {
            $bucketLabels[] = ucfirst($stat['bucket']);
            $bucketData[] = $stat['count'];
        }

        // Prepare witel chart data
        $witelLabels = [];
        $witelData = [];
        foreach ($witelStats as $stat) {
            $witelLabels[] = $stat['witel'];
            $witelData[] = $stat['count'];
        }

        // Prepare main category chart data
        $categoryLabels = [];
        $categoryData = [];
        foreach ($mainCategoryStats as $stat) {
            $categoryLabels[] = $stat['mainCategory'];
            $categoryData[] = $stat['count'];
        }

        return [
            'bucket' => [
                'labels' => $bucketLabels,
                'data' => $bucketData,
                'colors' => array_values($bucketColors)
            ],
            'witel' => [
                'labels' => $witelLabels,
                'data' => $witelData
            ],
            'category' => [
                'labels' => $categoryLabels,
                'data' => $categoryData
            ]
        ];
    }

    private function exportToCsv($data, $workDate)
    {
        $filename = "agent_buckets_{$workDate}.csv";

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // CSV Headers
        fputcsv($output, [
            'Work Date',
            'Full Name',
            'Channel',
            'Queue Count',
            'Has AUX',
            'Presensi',
            'Bucket',
            'Reason'
        ]);

        // Data rows
        foreach ($data as $row) {
            fputcsv($output, [
                $row['work_date'],
                $row['fullname_norm'],
                $row['channel_name_norm'],
                $row['queue_count'],
                $row['has_aux'] ? 'Yes' : 'No',
                $row['presensi'] ? 'Yes' : 'No',
                $row['bucket'],
                $row['reason']
            ]);
        }

        fclose($output);
        exit;
    }

    private function exportToExcel($data, $workDate)
    {
        // This would require PhpSpreadsheet for Excel export
        // For now, redirect to CSV export
        return $this->exportToCsv($data, $workDate);
    }
}
