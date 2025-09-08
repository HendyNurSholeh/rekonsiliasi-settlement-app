<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Libraries\EventLogEnum;
use App\Libraries\LogEnum;
use App\Models\ProsesModel;
use App\Traits\HasLogActivity;

class BuatJurnalController extends BaseController
{
    use HasLogActivity;
    protected $prosesModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }

    /**
     * Buat Jurnal Settlement
     * Menampilkan data dari p_compare_rekap dengan filter tanggal
     */
    public function index()
    {
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        $fileSettle = $this->request->getGet('file_settle') ?? '';

        $data = [
            'title' => 'Buat Jurnal Settlement',
            'tanggalRekon' => $tanggalRekon,
            'fileSettle' => $fileSettle,
            'route' => 'settlement/buat-jurnal'
        ];

        $this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

        return $this->render('settlement/buat_jurnal/index.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint for buat jurnal settlement
     */
    public function datatable()
    {
        // Get parameters from both GET and POST to handle DataTables requests
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->request->getPost('tanggal') ?? $this->prosesModel->getDefaultDate();
        $fileSettle = $this->request->getGet('file_settle') ?? $this->request->getPost('file_settle') ?? '';
        
        // Debug log
        log_message('info', 'Buat Jurnal DataTable parameters - Tanggal: ' . $tanggalRekon . ', File Settle: ' . $fileSettle);
        
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

        try {
            $db = \Config\Database::connect();
            
            // Call procedure to get data
            $procedureQuery = "CALL p_compare_rekap(?)";
            $procedureResult = $db->query($procedureQuery, [$tanggalRekon]);
            
            if (!$procedureResult) {
                throw new \Exception('Failed to execute p_compare_rekap procedure');
            }

            // Get all data from procedure result
            $allData = $procedureResult->getResultArray();
            $procedureResult->freeResult();
            
            // Apply file_settle filter if specified
            if ($fileSettle !== '') {
                $allData = array_filter($allData, function($row) use ($fileSettle) {
                    return isset($row['FILE_SETTLE']) && $row['FILE_SETTLE'] == $fileSettle;
                });
                // Reset array keys after filtering
                $allData = array_values($allData);
            }
            
            // Apply search filter if specified
            if (!empty($searchValue)) {
                $allData = array_filter($allData, function($row) use ($searchValue) {
                    $searchableFields = ['NAMA_GROUP', 'FILE_SETTLE', 'SELISIH', 'JUM_TX_DISPURE', 'KD_SETTLE'];
                    foreach ($searchableFields as $field) {
                        if (isset($row[$field]) && stripos($row[$field], $searchValue) !== false) {
                            return true;
                        }
                    }
                    return false;
                });
                // Reset array keys after filtering
                $allData = array_values($allData);
            }
            
            // Get total counts
            $totalRecords = count($allData);
            $filteredRecords = count($allData);
            
            // Apply sorting
            if ($orderColumn > 0) {
                $columns = [
                    1 => 'NAMA_GROUP',
                    2 => 'FILE_SETTLE', 
                    3 => 'SELISIH',
                    4 => 'JUM_TX_DISPURE',
                    5 => 'KD_SETTLE'
                ];
                
                if (isset($columns[$orderColumn])) {
                    $sortField = $columns[$orderColumn];
                    usort($allData, function($a, $b) use ($sortField, $orderDir) {
                        $valueA = $a[$sortField] ?? '';
                        $valueB = $b[$sortField] ?? '';
                        
                        if (is_numeric($valueA) && is_numeric($valueB)) {
                            $result = $valueA <=> $valueB;
                        } else {
                            $result = strcmp($valueA, $valueB);
                        }
                        
                        return $orderDir === 'desc' ? -$result : $result;
                    });
                }
            }
            
            // Apply pagination
            $paginatedData = array_slice($allData, $start, $length);
            
            // Format data for DataTables
            $formattedData = [];
            foreach ($paginatedData as $index => $row) {
                // Konversi nama kolom dari procedure ke format yang diharapkan view
                $selisih = intval(str_replace(',', '', $row['SELISIH'] ?? '0'));
                $jumTxDispute = intval($row['JUM_TX_DISPURE'] ?? 0);
                $amountTxDispute = intval(str_replace(',', '', $row['AMOUNT_TX_DISPURE'] ?? '0'));
                
                $formattedData[] = [
                    'NAMA_PRODUK' => $row['NAMA_GROUP'] ?? '',
                    'FILE_SETTLE' => $row['FILE_SETTLE'] ?? '0',
                    'AMOUNT_DETAIL' => $row['AMOUNT_DETAIL'] ?? '0',
                    'AMOUNT_REKAP' => $row['AMOUNT_REKAP'] ?? '0',
                    'SELISIH' => $row['SELISIH'] ?? '0',
                    'JUM_TX_DISPUTE' => $row['JUM_TX_DISPURE'] ?? '0',
                    'AMOUNT_TX_DISPUTE' => $row['AMOUNT_TX_DISPURE'] ?? '0',
                    'KD_SETTLE' => $row['KD_SETTLE'] ?? '',
                    'CAN_CREATE' => (empty($row['KD_SETTLE']) && $selisih == 0 && $jumTxDispute == 0 && $amountTxDispute == 0) ? 1 : 0,
                    'ROW_DATA' => $row // For passing all data to action buttons
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
            log_message('error', 'Error in Buat Jurnal DataTable: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => intval($draw),
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Create jurnal settlement
     */
    public function createJurnal()
    {
        $namaProduk = $this->request->getPost('nama_produk');
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        
        if (!$namaProduk || !$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter nama produk dan tanggal rekonsiliasi harus diisi',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Call procedure to generate settlement journal
            $query = "CALL p_generate_settle_jurnal(?, ?)";
            $result = $db->query($query, [$namaProduk, $tanggalRekon]);
            
            if (!$result) {
                throw new \Exception('Failed to execute p_generate_settle_jurnal procedure');
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Jurnal settlement berhasil dibuat untuk produk ' . $namaProduk,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error creating settlement journal: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat membuat jurnal settlement: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Validate settlement data before creating journal
     */
    public function validateSettlement()
    {
        $namaProduk = $this->request->getPost('nama_produk');
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        
        if (!$namaProduk || !$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter tidak lengkap',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $db = \Config\Database::connect();
            
            // Get current data to validate SELISIH and JUM_TX_DISPUTE
            $procedureQuery = "CALL p_compare_rekap(?)";
            $procedureResult = $db->query($procedureQuery, [$tanggalRekon]);
            
            if (!$procedureResult) {
                throw new \Exception('Failed to execute validation procedure');
            }

            $allData = $procedureResult->getResultArray();
            $procedureResult->freeResult();
            
            // Find the specific product data
            $productData = null;
            foreach ($allData as $row) {
                if ($row['NAMA_GROUP'] === $namaProduk) {
                    $productData = $row;
                    break;
                }
            }
            
            if (!$productData) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Data produk tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ]);
            }
            
            // Validate SELISIH and JUM_TX_DISPUTE must be 0
            $selisih = intval(str_replace(',', '', $productData['SELISIH'] ?? '0'));
            $jumTxDispute = intval($productData['JUM_TX_DISPURE'] ?? 0);
            $amountTxDispute = intval(str_replace(',', '', $productData['AMOUNT_TX_DISPURE'] ?? '0'));
            
            if ($selisih !== 0 || $jumTxDispute !== 0 || $amountTxDispute !== 0) {
                $errors = [];
                if ($selisih !== 0) {
                    $errors[] = "SELISIH harus 0 (saat ini: {$selisih})";
                }
                if ($jumTxDispute !== 0) {
                    $errors[] = "JUM_TX_DISPUTE harus 0 (saat ini: {$jumTxDispute})";
                }
                if ($amountTxDispute !== 0) {
                    $errors[] = "AMOUNT_TX_DISPUTE harus 0 (saat ini: {$amountTxDispute})";
                }
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . implode(', ', $errors),
                    'csrf_token' => csrf_hash()
                ]);
            }

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Validasi berhasil. Data siap untuk dibuat jurnal.',
                'data' => $productData,
                'csrf_token' => csrf_hash()
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Error validating settlement: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan saat validasi: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }
}
