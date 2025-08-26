<?php

namespace App\Controllers\Rekon\Process;

use App\Controllers\BaseController;
use App\Models\ProsesModel;

class DirectJurnalController extends BaseController
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
    public function rekap()
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

        return $this->render('rekon/process/direct_jurnal_rekap/index.blade.php', $data);
    }

    /**
     * Penyelesaian Dispute Direct Jurnal
     * Menampilkan data dari v_cek_biller_dispute_direct
     */
    public function dispute()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Penyelesaian Dispute Direct Jurnal',
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
            $query = $db->query("CALL p_update_dispute_tx(?, ?, ?, ?, ?)", [
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
        $statusBiller = $this->request->getGet('status_biller') ?? $this->request->getPost('status_biller') ?? '';
        $statusCore = $this->request->getGet('status_core') ?? $this->request->getPost('status_core') ?? '';
        
        // Debug log
        log_message('info', 'DataTable parameters - Tanggal: ' . $tanggalRekon . ', Status Biller: ' . $statusBiller . ', Status Core: ' . $statusCore);
        
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
            
            // Add status filters
            $queryParams = [$tanggalRekon];
            if ($statusBiller !== '') {
                $baseQuery .= " AND STATUS = ?";
                $queryParams[] = $statusBiller;
                log_message('info', 'Adding status_biller filter: ' . $statusBiller);
            }
            if ($statusCore !== '') {
                $baseQuery .= " AND v_STAT_CORE_AGR = ?";
                $queryParams[] = $statusCore;
                log_message('info', 'Adding status_core filter: ' . $statusCore);
            }
            
            // Add search conditions
            $searchConditions = [];
            
            if (!empty($searchValue)) {
                $searchConditions[] = "(
                    IDPARTNER LIKE ? OR 
                    TERMINALID LIKE ? OR 
                    v_GROUP_PRODUK LIKE ? OR 
                    IDPEL LIKE ? OR 
                    CAST(RP_BILLER_TAG AS CHAR) LIKE ?
                )";
                $searchTerm = "%{$searchValue}%";
                $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (!empty($searchConditions)) {
                $baseQuery .= " AND " . implode(" AND ", $searchConditions);
            }
            
            // Count total records without filtering (but with status filters)
            $totalQuery = "
                SELECT COUNT(*) as total 
                FROM v_cek_biller_dispute_direct 
                WHERE v_TGL_FILE_REKON = ?
            ";
            $totalParams = [$tanggalRekon];
            if ($statusBiller !== '') {
                $totalQuery .= " AND STATUS = ?";
                $totalParams[] = $statusBiller;
            }
            if ($statusCore !== '') {
                $totalQuery .= " AND v_STAT_CORE_AGR = ?";
                $totalParams[] = $statusCore;
            }
            $totalResult = $db->query($totalQuery, $totalParams);
            $totalRecords = $totalResult->getRow()->total;
            
            // Count filtered records (with status filters and search)
            if (!empty($searchConditions)) {
                $filteredQuery = "
                    SELECT COUNT(*) as total 
                    FROM v_cek_biller_dispute_direct 
                    WHERE v_TGL_FILE_REKON = ?
                ";
                $filteredParams = [$tanggalRekon];
                if ($statusBiller !== '') {
                    $filteredQuery .= " AND STATUS = ?";
                    $filteredParams[] = $statusBiller;
                }
                if ($statusCore !== '') {
                    $filteredQuery .= " AND v_STAT_CORE_AGR = ?";
                    $filteredParams[] = $statusCore;
                }
                $filteredQuery .= " AND " . implode(" AND ", $searchConditions);
                // Add search parameters
                if (!empty($searchValue)) {
                    $searchTerm = "%{$searchValue}%";
                    $filteredParams = array_merge($filteredParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
                }
                $filteredResult = $db->query($filteredQuery, $filteredParams);
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
            
            // Log the final query and parameters
            log_message('info', 'Final query: ' . $baseQuery);
            log_message('info', 'Query parameters: ' . json_encode($queryParams));
            
            // Execute query
            $result = $db->query($baseQuery, $queryParams);
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
}
