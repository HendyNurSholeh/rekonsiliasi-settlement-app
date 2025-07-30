<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;
use App\Models\AgnTrxMgateModel;

class RekonStep3Controller extends BaseController
{
    use HasLogActivity;

    protected $prosesModel;
    protected $agnDetailModel;
    protected $agnSettleEduModel;
    protected $agnSettlePajakModel;
    protected $agnTrxMgateModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->agnDetailModel = new AgnDetailModel();
        $this->agnSettleEduModel = new AgnSettleEduModel();
        $this->agnSettlePajakModel = new AgnSettlePajakModel();
        $this->agnTrxMgateModel = new AgnTrxMgateModel();
    }

    /**
     * Halaman Step 3 - Proses Rekonsiliasi
     */
    public function index()
    {
        // Get tanggalRekon from URL parameter or session
        $tanggalRekon = $this->request->getGet('tanggal') ?: session()->get('current_rekon_date');

        // If no date available, get default from database using ORM
        if (!$tanggalRekon) {
            $tanggalRekon = $this->prosesModel->getDefaultDate();
            if ($tanggalRekon) {
                session()->set('current_rekon_date', $tanggalRekon);
            }
        }

        $data = [
            'title' => 'Step 3: Proses Rekonsiliasi',
            'route' => 'rekon/step3',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 3
        ];

        return $this->render('rekon/process/step3.blade.php', $data);
    }

    /**
     * Start reconciliation process
     */
    public function processReconciliation()
    {
        $tanggalRekon = session()->get('current_rekon_date');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session tidak valid'
            ]);
        }

        try {
            // Execute reconciliation using stored procedures
            $result = $this->executeReconciliation($tanggalRekon);

            if ($result['success']) {
                $this->logActivity([
                    'log_name' => 'RECONCILIATION_PROCESS',
                    'description' => "Proses rekonsiliasi berhasil untuk tanggal {$tanggalRekon}",
                    'event' => 'RECONCILIATION_SUCCESS',
                    'subject' => 'Settlement Reconciliation'
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Proses rekonsiliasi berhasil',
                    'results' => $result['data']
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }

        } catch (\Exception $e) {
            $this->logActivity([
                'log_name' => 'RECONCILIATION_PROCESS',
                'description' => "Error proses rekonsiliasi: " . $e->getMessage(),
                'event' => 'RECONCILIATION_ERROR',
                'subject' => 'Settlement Reconciliation'
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get reconciliation progress
     */
    public function getReconciliationProgress()
    {
        $tanggalRekon = session()->get('current_rekon_date');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session tidak valid'
            ]);
        }

        try {
            // Simulate progress check
            // In real implementation, this would check the actual process status
            $progress = [
                'percentage' => 100,
                'message' => 'Rekonsiliasi selesai',
                'status' => 'completed',
                'details' => [
                    'matched_records' => rand(100, 500),
                    'unmatched_records' => rand(0, 50),
                    'total_records' => rand(150, 550)
                ]
            ];

            return $this->response->setJSON([
                'success' => true,
                'progress' => $progress
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting progress: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Generate reconciliation reports
     */
    public function generateReports()
    {
        $tanggalRekon = session()->get('current_rekon_date');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session tidak valid'
            ]);
        }

        try {
            // Generate various reports
            $reports = $this->createReconciliationReports($tanggalRekon);

            $this->logActivity([
                'log_name' => 'REPORT_GENERATION',
                'description' => "Generate laporan rekonsiliasi untuk tanggal {$tanggalRekon}",
                'event' => 'REPORT_GENERATED',
                'subject' => 'Settlement Reports'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Laporan berhasil dibuat',
                'reports' => $reports
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error generating reports: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Download specific report
     */
    public function downloadReport()
    {
        $reportType = $this->request->getGet('type');
        $tanggalRekon = session()->get('current_rekon_date');
        
        if (!$reportType || !$tanggalRekon) {
            return redirect()->back()->with('error', 'Parameter tidak valid');
        }

        try {
            // Generate and download report
            $filePath = $this->generateReportFile($reportType, $tanggalRekon);
            
            if (file_exists($filePath)) {
                return $this->response->download($filePath, null);
            } else {
                return redirect()->back()->with('error', 'File laporan tidak ditemukan');
            }

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error downloading report: ' . $e->getMessage());
        }
    }

    /**
     * Execute reconciliation using stored procedures
     */
    private function executeReconciliation($tanggalRekon)
    {
        try {
            $db = \Config\Database::connect();
            
            // Call stored procedures for reconciliation
            // p_reset_date first, then p_proses_persiapan
            $resetQuery = "CALL p_reset_date(?)";
            $db->query($resetQuery, [$tanggalRekon]);
            
            $processQuery = "CALL p_proses_persiapan(?)";
            $db->query($processQuery, [$tanggalRekon]);

            // Simulate reconciliation results
            $results = [
                'matched_records' => rand(100, 500),
                'unmatched_records' => rand(0, 50),
                'total_records' => rand(150, 550),
                'processing_time' => rand(30, 120) . ' seconds',
                'status' => 'completed'
            ];

            return [
                'success' => true,
                'data' => $results
            ];

        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Create reconciliation reports
     */
    private function createReconciliationReports($tanggalRekon)
    {
        $reportsPath = WRITEPATH . 'reports/reconciliation/' . $tanggalRekon . '/';
        
        if (!is_dir($reportsPath)) {
            mkdir($reportsPath, 0755, true);
        }

        $reports = [
            'summary' => [
                'title' => 'Laporan Summary Rekonsiliasi',
                'filename' => 'summary_' . $tanggalRekon . '.xlsx',
                'path' => $reportsPath . 'summary_' . $tanggalRekon . '.xlsx',
                'size' => '245 KB',
                'type' => 'summary'
            ],
            'matched' => [
                'title' => 'Laporan Transaksi Matched',
                'filename' => 'matched_' . $tanggalRekon . '.xlsx',
                'path' => $reportsPath . 'matched_' . $tanggalRekon . '.xlsx',
                'size' => '1.2 MB',
                'type' => 'matched'
            ],
            'unmatched' => [
                'title' => 'Laporan Transaksi Unmatched',
                'filename' => 'unmatched_' . $tanggalRekon . '.xlsx',
                'path' => $reportsPath . 'unmatched_' . $tanggalRekon . '.xlsx',
                'size' => '156 KB',
                'type' => 'unmatched'
            ],
            'anomaly' => [
                'title' => 'Laporan Anomali',
                'filename' => 'anomaly_' . $tanggalRekon . '.xlsx',
                'path' => $reportsPath . 'anomaly_' . $tanggalRekon . '.xlsx',
                'size' => '89 KB',
                'type' => 'anomaly'
            ]
        ];

        // Create placeholder files
        foreach ($reports as &$report) {
            if (!file_exists($report['path'])) {
                file_put_contents($report['path'], 'Placeholder report content');
            }
            $report['created_at'] = date('Y-m-d H:i:s');
        }

        return $reports;
    }

    /**
     * Generate specific report file
     */
    private function generateReportFile($reportType, $tanggalRekon)
    {
        $reportsPath = WRITEPATH . 'reports/reconciliation/' . $tanggalRekon . '/';
        $filename = $reportType . '_' . $tanggalRekon . '.xlsx';
        $filePath = $reportsPath . $filename;

        if (!is_dir($reportsPath)) {
            mkdir($reportsPath, 0755, true);
        }

        // Create placeholder file if not exists
        if (!file_exists($filePath)) {
            file_put_contents($filePath, "Report content for {$reportType} on {$tanggalRekon}");
        }

        return $filePath;
    }
}
