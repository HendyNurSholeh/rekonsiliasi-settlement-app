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
     * DataTables AJAX endpoint for dispute resolution
     */
    public function disputeDataTable()
    {
        // Get parameters from both GET and POST to handle DataTables requests
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        // DataTables parameters
        $draw = $this->request->getGet('draw') ?? $this->request->getPost('draw') ?? 1;
        $start = $this->request->getGet('start') ?? $this->request->getPost('start') ?? 0;
        $length = $this->request->getGet('length') ?? $this->request->getPost('length') ?? 25;
        
        // Handle search parameter
        $searchArray = $this->request->getGet('search') ?? $this->request->getPost('search') ?? [];
        $searchValue = isset($searchArray['value']) ? $searchArray['value'] : '';
        
        // Handle order parameter
        $orderArray = $this->request->getGet('order') ?? $this->request->getPost('order') ?? [];
        $orderColumn = isset($orderArray[0]['column']) ? $orderArray[0]['column'] : 0;
        $orderDir = isset($orderArray[0]['dir']) ? $orderArray[0]['dir'] : 'asc';

        // Column mapping
        $columns = [
            0 => 'v_ID', // For row number (not actual column)
            1 => 'IDPARTNER',
            2 => 'TERMINALID', 
            3 => 'v_GROUP_PRODUK',
            4 => 'IDPEL',
            5 => 'RP_BILLER_TAG',
            6 => 'STATUS',
            7 => 'v_STAT_CORE_AGR',
            8 => 'v_ID' // For action column
        ];

        try {
            $db = \Config\Database::connect();
            
            // Base query
            $baseQuery = "
                SELECT IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, 
                       RP_BILLER_TAG, STATUS AS STATUS_BILLER, v_STAT_CORE_AGR AS STATUS_CORE, v_ID
                FROM v_cek_biller_dispute_direct 
                WHERE v_TGL_FILE_REKON = ?
            ";
            
            // Add search conditions
            $searchConditions = [];
            $searchParams = [$tanggalRekon];
            
            if (!empty($searchValue)) {
                $searchConditions[] = "(
                    IDPARTNER LIKE ? OR 
                    TERMINALID LIKE ? OR 
                    v_GROUP_PRODUK LIKE ? OR 
                    IDPEL LIKE ? OR 
                    CAST(RP_BILLER_TAG AS CHAR) LIKE ?
                )";
                $searchTerm = "%{$searchValue}%";
                $searchParams = array_merge($searchParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (!empty($searchConditions)) {
                $baseQuery .= " AND " . implode(" AND ", $searchConditions);
            }
            
            // Count total records without filtering
            $totalQuery = "
                SELECT COUNT(*) as total 
                FROM v_cek_biller_dispute_direct 
                WHERE v_TGL_FILE_REKON = ?
            ";
            $totalResult = $db->query($totalQuery, [$tanggalRekon]);
            $totalRecords = $totalResult->getRow()->total;
            
            // Count filtered records
            if (!empty($searchConditions)) {
                $filteredQuery = "
                    SELECT COUNT(*) as total 
                    FROM v_cek_biller_dispute_direct 
                    WHERE v_TGL_FILE_REKON = ? AND " . implode(" AND ", $searchConditions);
                $filteredResult = $db->query($filteredQuery, $searchParams);
                $filteredRecords = $filteredResult->getRow()->total;
            } else {
                $filteredRecords = $totalRecords;
            }
            
            // Add ordering
            if (isset($columns[$orderColumn]) && $orderColumn > 0 && $orderColumn < 8) {
                $orderColumnName = $columns[$orderColumn];
                if ($orderColumn == 3) $orderColumnName = 'v_GROUP_PRODUK'; // Handle alias
                $baseQuery .= " ORDER BY {$orderColumnName} {$orderDir}";
            } else {
                $baseQuery .= " ORDER BY v_ID ASC";
            }
            
            // Add pagination
            $baseQuery .= " LIMIT {$length} OFFSET {$start}";
            
            // Execute query
            $result = $db->query($baseQuery, $searchParams);
            $data = $result->getResultArray();
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'IDPARTNER' => $row['IDPARTNER'] ?? '',
                    'TERMINALID' => $row['TERMINALID'] ?? '',
                    'PRODUK' => $row['PRODUK'] ?? '',
                    'IDPEL' => $row['IDPEL'] ?? '',
                    'RP_BILLER_TAG' => $row['RP_BILLER_TAG'] ?? '0',
                    'STATUS_BILLER' => $row['STATUS_BILLER'] ?? '0',
                    'STATUS_CORE' => $row['STATUS_CORE'] ?? '0',
                    'v_ID' => $row['v_ID'] ?? ''
                ];
            }
            
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => intval($totalRecords),
                'recordsFiltered' => intval($filteredRecords),
                'data' => $formattedData,
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in disputeDataTable: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data',
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
