<?php

namespace App\Controllers\Rekon\Process\DirectJurnal;

use App\Controllers\BaseController;
use App\Models\ProsesModel;

class RekapDirectJurnalController extends BaseController
{
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    /**
     * Rekap Tx Direct Jurnal
     * Menampilkan data dari procedure p_direct_jurnal_rekap
     */
    public function index()
    {
        $tanggalData = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Rekap Tx Direct Jurnal',
            'tanggalData' => $tanggalData,
            'route' => 'rekon/process/direct-jurnal/rekap'
        ];

        // Get data from procedure if date is provided
        if ($tanggalData) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("CALL p_direct_jurnal_rekap(?)", [$tanggalData]);
                $data['rekapData'] = $query->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error calling p_direct_jurnal_rekap: ' . $e->getMessage());
                $data['rekapData'] = [];
            }
        } else {
            $data['rekapData'] = [];
        }

        return $this->render('rekon/process/direct_jurnal/rekap_direct_jurnal/index.blade.php', $data);
    }
}
