<?php

namespace App\Controllers\Rekon\Process;

use App\Controllers\BaseController;
use App\Models\ProsesModel;

class KonfirmasiSaldoCAController extends BaseController
{
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    /**
     * Konfirmasi Saldo CA page
     */
    public function index()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();

        $data = [
            'title' => 'Konfirmasi Saldo CA',
            'tanggalRekon' => $tanggalRekon,
            'route' => 'rekon/process/konfirmasi-saldo-ca'
        ];

        return $this->render('rekon/process/konfirmasi_saldo_ca.blade.php', $data);
    }

    /**
     * Konfirmasi Saldo CA (Customer Account Reconciliation)
     */
    public function konfirmasi()
    {
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        $keterangan = $this->request->getPost('keterangan');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi harus diisi',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->query("CALL p_konfirmasi_saldo_ca(?, ?)", [
                $tanggalRekon, 
                $keterangan ?? ''
            ]);
            
            log_message('info', 'p_konfirmasi_saldo_ca procedure called successfully for tanggal: ' . $tanggalRekon);

            // Get result from procedure
            $result = $query->getResult();
            
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Konfirmasi Saldo CA berhasil diproses',
                'data' => $result,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error in konfirmasi saldo CA: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses konfirmasi saldo CA: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Get Saldo CA Detail
     */
    public function getSaldoDetail()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi harus diisi',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            $query = $db->query("
                SELECT * FROM v_saldo_ca 
                WHERE tgl_rekon = ? 
                ORDER BY id_ca
            ", [$tanggalRekon]);
            
            $saldoData = $query->getResultArray();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $saldoData,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error fetching saldo CA detail: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengambil data saldo CA',
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * DataTables endpoint for Saldo CA
     */
    public function datatable()
    {
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
            0 => 'id_ca',
            1 => 'nama_ca',
            2 => 'saldo_awal',
            3 => 'saldo_akhir',
            4 => 'selisih',
            5 => 'status'
        ];

        try {
            $db = \Config\Database::connect();
            
            // Base query
            $baseQuery = "
                SELECT id_ca, nama_ca, saldo_awal, saldo_akhir, 
                       (saldo_akhir - saldo_awal) as selisih, status
                FROM v_saldo_ca 
                WHERE tgl_rekon = ?
            ";
            
            $queryParams = [$tanggalRekon];
            
            // Add search conditions
            $searchConditions = [];
            
            if (!empty($searchValue)) {
                $searchConditions[] = "(
                    id_ca LIKE ? OR 
                    nama_ca LIKE ? OR 
                    CAST(saldo_awal AS CHAR) LIKE ? OR 
                    CAST(saldo_akhir AS CHAR) LIKE ? OR
                    status LIKE ?
                )";
                $searchTerm = "%{$searchValue}%";
                $queryParams = array_merge($queryParams, [$searchTerm, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
            }
            
            if (!empty($searchConditions)) {
                $baseQuery .= " AND " . implode(" AND ", $searchConditions);
            }
            
            // Count total records
            $totalQuery = "SELECT COUNT(*) as total FROM v_saldo_ca WHERE tgl_rekon = ?";
            $totalResult = $db->query($totalQuery, [$tanggalRekon]);
            $totalRecords = $totalResult->getRow()->total;
            
            // Count filtered records
            if (!empty($searchConditions)) {
                $filteredQuery = "SELECT COUNT(*) as total FROM v_saldo_ca WHERE tgl_rekon = ?";
                $filteredParams = [$tanggalRekon];
                $filteredQuery .= " AND " . implode(" AND ", $searchConditions);
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
            if (isset($columns[$orderColumn])) {
                $orderColumnName = $columns[$orderColumn];
                $baseQuery .= " ORDER BY {$orderColumnName} {$orderDir}";
            } else {
                $baseQuery .= " ORDER BY id_ca ASC";
            }
            
            // Add pagination
            $baseQuery .= " LIMIT {$length} OFFSET {$start}";
            
            // Execute query
            $result = $db->query($baseQuery, $queryParams);
            $data = $result->getResultArray();
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'id_ca' => $row['id_ca'] ?? '',
                    'nama_ca' => $row['nama_ca'] ?? '',
                    'saldo_awal' => number_format($row['saldo_awal'] ?? 0, 0, ',', '.'),
                    'saldo_akhir' => number_format($row['saldo_akhir'] ?? 0, 0, ',', '.'),
                    'selisih' => number_format($row['selisih'] ?? 0, 0, ',', '.'),
                    'status' => $row['status'] ?? ''
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
            log_message('error', 'Error in saldoCADataTable: ' . $e->getMessage());
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
