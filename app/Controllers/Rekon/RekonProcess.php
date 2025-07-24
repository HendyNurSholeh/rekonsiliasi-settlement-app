<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;

class RekonProcess extends BaseController
{
    use HasLogActivity;

    protected $prosesModel;
    protected $agnDetailModel;
    protected $agnSettleEduModel;
    protected $agnSettlePajakModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->agnDetailModel = new AgnDetailModel();
        $this->agnSettleEduModel = new AgnSettleEduModel();
        $this->agnSettlePajakModel = new AgnSettlePajakModel();
    }

    public function index()
    {
        // Use dummy data for display
        $data = [
            'title' => 'Proses Rekonsiliasi Settlement',
            'route' => 'rekon/process'
        ];

        return $this->render('rekon/process/index.blade.php', $data);
    }

    public function create()
    {
        $tanggal = $this->request->getPost('tanggal_rekon');
        
        if (!$tanggal) {
            return redirect()->back()->with('error', 'Tanggal rekonsiliasi harus diisi');
        }

        // Validasi tanggal
        $date = \DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$date || $date->format('Y-m-d') !== $tanggal) {
            return redirect()->back()->with('error', 'Format tanggal tidak valid');
        }

        // Store date in session for demo
        session()->set('current_rekon_process_id', 'PRK-' . date('ymd', strtotime($tanggal)) . '-001');
        session()->set('current_rekon_date', $tanggal);
        
        return redirect()->to('rekon/process/step1')->with('success', 'Proses rekonsiliasi untuk tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' berhasil dibuat');
    }

    public function step1()
    {
        $processId = session()->get('current_rekon_process_id');
        $tanggalRekon = session()->get('current_rekon_date');

        if (!$processId || !$tanggalRekon) {
            return redirect()->to('rekon/process')->with('error', 'Session proses rekonsiliasi tidak ditemukan');
        }

        $data = [
            'title' => 'Step 1: Upload File Settlement',
            'route' => 'rekon/process/step1',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 1
        ];

        return $this->render('rekon/process/step1.blade.php', $data);
    }

    public function step2()
    {
        $processId = session()->get('current_rekon_process_id');
        $tanggalRekon = session()->get('current_rekon_date');

        if (!$processId || !$tanggalRekon) {
            return redirect()->to('rekon/process')->with('error', 'Session proses rekonsiliasi tidak ditemukan');
        }

        $data = [
            'title' => 'Step 2: Validasi & Review Data',
            'route' => 'rekon/process/step2',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 2
        ];

        return $this->render('rekon/process/step2.blade.php', $data);
    }

    public function step3()
    {
        $processId = session()->get('current_rekon_process_id');
        $tanggalRekon = session()->get('current_rekon_date');

        if (!$processId || !$tanggalRekon) {
            return redirect()->to('rekon/process')->with('error', 'Session proses rekonsiliasi tidak ditemukan');
        }

        $data = [
            'title' => 'Step 3: Proses Rekonsiliasi',
            'route' => 'rekon/process/step3',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 3
        ];

        return $this->render('rekon/process/step3.blade.php', $data);
    }

    public function processReconciliation()
    {
        // Simulate success response for demo
        return $this->response->setJSON(['success' => true, 'message' => 'Proses rekonsiliasi berhasil (Demo)']);
    }

    private function getUploadStatus($tanggalRekon)
    {
        return [
            'agn_detail' => $this->agnDetailModel->countByDate($tanggalRekon) > 0,
            'agn_settle_edu' => $this->agnSettleEduModel->countByDate($tanggalRekon) > 0,
            'agn_settle_pajak' => $this->agnSettlePajakModel->countByDate($tanggalRekon) > 0,
            'agn_detail_count' => $this->agnDetailModel->countByDate($tanggalRekon),
            'agn_settle_edu_count' => $this->agnSettleEduModel->countByDate($tanggalRekon),
            'agn_settle_pajak_count' => $this->agnSettlePajakModel->countByDate($tanggalRekon)
        ];
    }

    private function allFilesUploaded($uploadStatus)
    {
        return $uploadStatus['agn_detail'] && 
               $uploadStatus['agn_settle_edu'] && 
               $uploadStatus['agn_settle_pajak'];
    }

    private function validateUploadedData($tanggalRekon)
    {
        return [
            'total_transactions' => $this->agnDetailModel->countByDate($tanggalRekon),
            'total_amount' => $this->agnDetailModel->getTotalAmountByDate($tanggalRekon),
            'settlement_edu_total' => $this->agnSettleEduModel->getTotalSettlementByDate($tanggalRekon),
            'settlement_pajak_total' => $this->agnSettlePajakModel->getTotalSettlementByDate($tanggalRekon)
        ];
    }
}
