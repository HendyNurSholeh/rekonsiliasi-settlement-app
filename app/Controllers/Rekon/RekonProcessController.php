<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Models\ProsesModel;

class RekonProcessController extends BaseController
{
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    /**
     * Laporan Detail vs Rekap
     * Menampilkan data dari procedure p_compare_rekap
     */
    public function detailVsRekap()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Laporan Detail vs Rekap',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/detail-vs-rekap'
        ];

        // Get data from procedure if date is provided
        if ($tanggalRekon) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("CALL p_compare_rekap(?)", [$tanggalRekon]);
                $data['compareData'] = $query->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error calling p_compare_rekap: ' . $e->getMessage());
                $data['compareData'] = [];
            }
        } else {
            $data['compareData'] = [];
        }

        return $this->render('rekon/process/detail_vs_rekap.blade.php', $data);
    }

    /**
     * Rekap Tx Direct Jurnal
     * Menampilkan data dari procedure p_direct_jurnal_rekap
     */
    public function directJurnalRekap()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Rekap Tx Direct Jurnal',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/direct-jurnal/rekap'
        ];

        // Get data from procedure if date is provided
        if ($tanggalRekon) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("CALL p_direct_jurnal_rekap(?)", [$tanggalRekon]);
                $data['rekapData'] = $query->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error calling p_direct_jurnal_rekap: ' . $e->getMessage());
                $data['rekapData'] = [];
            }
        } else {
            $data['rekapData'] = [];
        }

        return $this->render('rekon/process/direct_jurnal_rekap.blade.php', $data);
    }

    /**
     * Penyelesaian Dispute
     * Menampilkan data dari v_cek_biller_dispute_direct
     */
    public function disputeResolution()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Penyelesaian Dispute',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/direct-jurnal/dispute'
        ];

        // Get data from view if date is provided
        if ($tanggalRekon) {
            try {
                $db = \Config\Database::connect();
                $query = $db->query("
                    SELECT IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, 
                           RP_BILLER_TAG, STATUS AS STATUS_BILLER, v_STAT_CORE_AGR AS STATUS_CORE, v_ID
                    FROM v_cek_biller_dispute_direct 
                    WHERE v_TGL_FILE_REKON = ?
                ", [$tanggalRekon]);
                $data['disputeData'] = $query->getResultArray();
            } catch (\Exception $e) {
                log_message('error', 'Error fetching dispute data: ' . $e->getMessage());
                $data['disputeData'] = [];
            }
        } else {
            $data['disputeData'] = [];
        }

        return $this->render('rekon/process/dispute_resolution.blade.php', $data);
    }

    /**
     * Get dispute detail for modal
     */
    public function getDisputeDetail()
    {
        $id = $this->request->getPost('id');
        
        if (!$id) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'ID tidak ditemukan',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT * FROM v_cek_biller_dispute_direct 
                WHERE v_ID = ?
            ", [$id]);
            
            $disputeDetail = $query->getRowArray();
            
            if (!$disputeDetail) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'data' => $disputeDetail,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching dispute detail: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Update dispute data
     */
    public function updateDispute()
    {
        $id = $this->request->getPost('id');
        $statusBiller = $this->request->getPost('status_biller');
        $statusCore = $this->request->getPost('status_core');
        $statusSettlement = $this->request->getPost('status_settlement');
        $idpartner = $this->request->getPost('idpartner');

        if (!$id || $statusBiller === null || $statusCore === null || $statusSettlement === null || !$idpartner) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Data tidak lengkap',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->query("CALL p_direct_jurnal_update(?, ?, ?, ?, ?)", [
                $id, $statusBiller, $statusCore, $statusSettlement, $idpartner
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Data berhasil diupdate',
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error updating dispute: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengupdate data',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Get fresh CSRF token
     */
    public function getCSRFToken()
    {
        return $this->response->setJSON([
            'success' => true,
            'csrf_token' => csrf_hash()
        ]);
    }
}
