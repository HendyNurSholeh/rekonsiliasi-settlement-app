<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use DateTime;
use CodeIgniter\Exceptions\PageNotFoundException;

class RekonReport extends BaseController
{
    use HasLogActivity;

    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    public function index($date = null)
    {
        if (!$date) {
            // Get default date from database where status = 1 using ORM
            $date = $this->prosesModel->getDefaultDate();
        }

        // Validate date format
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            return redirect()->to('rekon/process')->with('error', 'Format tanggal tidak valid');
        }

        $data = [
            'title' => 'Laporan Rekonsiliasi Settlement',
            'route' => 'rekon/reports',
            'tanggalRekon' => $date,
            'reportData' => $this->generateReportData($date)
        ];

        return $this->render('rekon/reports/index.blade.php', $data);
    }

    public function downloadExcel($date)
    {
        // TODO: Implement Excel download
        return redirect()->back()->with('info', 'Download Excel akan segera tersedia');
    }

    public function downloadPdf($date)
    {
        // TODO: Implement PDF download
        return redirect()->back()->with('info', 'Download PDF akan segera tersedia');
    }

    private function generateReportData($date)
    {
        // Generate dummy report data
        return [
            'summary' => [
                'total_transactions' => 37270,
                'total_amount' => 2658900000,
                'match_rate' => 99.8,
                'discrepancies' => 12,
                'processed_files' => 4,
                'processing_time' => '00:04:32'
            ],
            'file_summary' => [
                'agn_detail' => [
                    'records' => 15847,
                    'amount' => 2456890000,
                    'matched' => 15842,
                    'unmatched' => 5
                ],
                'settlement_edu' => [
                    'records' => 2456,
                    'amount' => 156780000,
                    'matched' => 2456,
                    'unmatched' => 0
                ],
                'settlement_pajak' => [
                    'records' => 892,
                    'amount' => 45230000,
                    'matched' => 890,
                    'unmatched' => 2
                ],
                'mgate' => [
                    'records' => 18923,
                    'amount' => 2658900000,
                    'matched' => 18918,
                    'unmatched' => 5
                ]
            ],
            'discrepancies' => [
                [
                    'id' => 'DISC001',
                    'type' => 'Amount Mismatch',
                    'source_file' => 'AGN_DETAIL',
                    'reference' => 'TXN-240722-001',
                    'agn_amount' => 150000,
                    'mgate_amount' => 145000,
                    'difference' => -5000,
                    'status' => 'Under Review'
                ],
                [
                    'id' => 'DISC002',
                    'type' => 'Missing Transaction',
                    'source_file' => 'M_GATE',
                    'reference' => 'TXN-240722-002',
                    'agn_amount' => 0,
                    'mgate_amount' => 75000,
                    'difference' => 75000,
                    'status' => 'Pending'
                ],
                [
                    'id' => 'DISC003',
                    'type' => 'Settlement Variance',
                    'source_file' => 'SETTLEMENT_PAJAK',
                    'reference' => 'STL-240722-001',
                    'agn_amount' => 25000,
                    'mgate_amount' => 23500,
                    'difference' => -1500,
                    'status' => 'Resolved'
                ]
            ],
            'statistics' => [
                'by_source' => [
                    'agn_detail_vs_mgate' => ['matched' => 15842, 'unmatched' => 5, 'rate' => 99.97],
                    'settlement_edu' => ['matched' => 2456, 'unmatched' => 0, 'rate' => 100.0],
                    'settlement_pajak' => ['matched' => 890, 'unmatched' => 2, 'rate' => 99.78],
                ],
                'by_amount' => [
                    'total_processed' => 2658900000,
                    'total_matched' => 2658825000,
                    'total_variance' => 75000,
                    'variance_rate' => 0.0028
                ]
            ]
        ];
    }
}
