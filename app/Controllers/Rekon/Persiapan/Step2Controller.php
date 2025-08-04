<?php

namespace App\Controllers\Rekon\Persiapan;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;
use App\Models\AgnTrxMgateModel;
use App\Models\VGroupProdukModel;
use App\Models\TampAgnDetailModel;
use App\Models\TampAgnSettleEduModel;
use App\Models\TampAgnSettlePajakModel;
use App\Models\TampAgnTrxMgateModel;

class Step2Controller extends BaseController
{
    use HasLogActivity;

    protected $prosesModel;
    protected $agnDetailModel;
    protected $agnSettleEduModel;
    protected $agnSettlePajakModel;
    protected $agnTrxMgateModel;
    protected $vGroupProdukModel;
    protected $tampAgnDetailModel;
    protected $tampAgnSettleEduModel;
    protected $tampAgnSettlePajakModel;
    protected $tampAgnTrxMgateModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->agnDetailModel = new AgnDetailModel();
        $this->agnSettleEduModel = new AgnSettleEduModel();
        $this->agnSettlePajakModel = new AgnSettlePajakModel();
        $this->agnTrxMgateModel = new AgnTrxMgateModel();
        $this->vGroupProdukModel = new VGroupProdukModel();
        $this->tampAgnDetailModel = new TampAgnDetailModel();
        $this->tampAgnSettleEduModel = new TampAgnSettleEduModel();
        $this->tampAgnSettlePajakModel = new TampAgnSettlePajakModel();
        $this->tampAgnTrxMgateModel = new TampAgnTrxMgateModel();
    }

    /**
     * Halaman Step 2 - Validasi & Review Data
     */
    public function index()
    {
        // Get date from URL parameter or database
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        if (!$tanggalRekon) {
            return redirect()->to('rekon')->with('error', 'Tanggal rekonsiliasi tidak ditemukan. Silakan buat proses baru.');
        }

        try {
            // Get product mapping data from v_cek_group_produk view
            $validationStatus = $this->vGroupProdukModel->getValidationStatus();
            $mappingData = $this->vGroupProdukModel->getGroupProdukData();
            $mappingStats = $this->vGroupProdukModel->getMappingStatistics();

            // Get real data statistics from temporary tables
            $dataStats = [
                'agn_detail' => $this->tampAgnDetailModel->getStatistics(),
                'settle_edu' => $this->tampAgnSettleEduModel->getStatistics(),
                'settle_pajak' => $this->tampAgnSettlePajakModel->getStatistics(),
                'mgate' => $this->tampAgnTrxMgateModel->getStatistics()
            ];

            $data = [
                'title' => 'Step 2: Verifikasi Isi Data',
                'route' => 'rekon/step2',
                'tanggalRekon' => $tanggalRekon,
                'currentStep' => 2,
                'mappingData' => $mappingData,
                'mappingStats' => $mappingStats,
                'validationStatus' => $validationStatus,
                'dataStats' => $dataStats
            ];

            return $this->render('rekon/process/step2.blade.php', $data);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in Step 2 index: ' . $e->getMessage());
            
            $data = [
                'title' => 'Step 2: Verifikasi Isi Data',
                'route' => 'rekon/step2',
                'tanggalRekon' => $tanggalRekon,
                'currentStep' => 2,
                'mappingData' => [],
                'mappingStats' => [
                    'total_products' => 0,
                    'mapped_products' => 0,
                    'unmapped_products' => 0,
                    'mapping_percentage' => 0
                ],
                'validationStatus' => [
                    'is_valid' => false,
                    'validation_message' => 'Error: ' . $e->getMessage(),
                    'can_proceed' => false
                ],
                'error' => $e->getMessage()
            ];

            return $this->render('rekon/process/step2.blade.php', $data);
        }
    }

    /**
     * Validate data and prepare for reconciliation
     */
    public function processValidation()
    {
        $tanggalRekon = $this->request->getPost('tanggal') ?? $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi tidak ditemukan'
            ]);
        }

        try {
            // Check product mapping using view v_cek_group_produk
            $validationStatus = $this->vGroupProdukModel->getValidationStatus();
            
            if (!$validationStatus['can_proceed']) {
                // Filter data yang belum mapping dari getGroupProdukData
                $allData = $this->vGroupProdukModel->getGroupProdukData();
                $unmappedProducts = array_filter($allData, function($item) {
                    return empty($item['NAMA_GROUP']) || $item['NAMA_GROUP'] === '';
                });
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $validationStatus['validation_message'],
                    'unmapped_products' => array_values($unmappedProducts)
                ]);
            }

            // If mapping is valid, call reconciliation procedure
            $result = $this->executeReconciliation($tanggalRekon);

            if ($result['success']) {
                $this->logActivity([
                    'log_name' => 'RECONCILIATION_START',
                    'description' => "Memulai proses rekonsiliasi untuk tanggal {$tanggalRekon}",
                    'event' => 'RECONCILIATION_START',
                    'subject' => 'Settlement Reconciliation'
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Proses rekonsiliasi berhasil dimulai',
                    'redirect' => base_url('/rekon/process/detail-vs-rekap')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message']
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error in processValidation: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Execute reconciliation procedure
     */
    private function executeReconciliation($tanggalRekon)
    {
        try {
            $db = \Config\Database::connect();
            
            // Call stored procedure p_proses_rekonsiliasi
            $query = $db->query("CALL p_proses_rekonsiliasi(?)", [$tanggalRekon]);
            
            return [
                'success' => true,
                'message' => 'Procedure executed successfully'
            ];
            
        } catch (\Exception $e) {
            log_message('error', 'Error executing reconciliation: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Get data preview for a specific file type
     */
    public function getDataPreview()
    {
        $fileType = $this->request->getGet('file_type');
        $tanggalRekon = $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        if (!$fileType || !$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter tidak valid'
            ]);
        }

        try {
            $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
            $possibleExtensions = ['csv', 'xlsx', 'xls'];
            $filePath = null;

            foreach ($possibleExtensions as $ext) {
                $testPath = $uploadPath . $fileType . '_' . $tanggalRekon . '.' . $ext;
                if (file_exists($testPath)) {
                    $filePath = $testPath;
                    break;
                }
            }

            if (!$filePath) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File tidak ditemukan'
                ]);
            }

            // Read preview data (first 10 rows)
            $previewData = $this->readFilePreview($filePath, 10);

            return $this->response->setJSON([
                'success' => true,
                'file_type' => $fileType,
                'preview_data' => $previewData,
                'total_rows' => count($previewData)
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error reading preview: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get product mapping data via AJAX
     */
    public function getMappingData()
    {
        try {
            $mappingData = $this->vGroupProdukModel->getGroupProdukData();
            $mappingStats = $this->vGroupProdukModel->getMappingStatistics();
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $mappingData,
                'stats' => $mappingStats
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting mapping data: ' . $e->getMessage(),
                'data' => [],
                'stats' => [
                    'total_products' => 0,
                    'mapped_products' => 0,
                    'unmapped_products' => 0,
                    'mapping_percentage' => 0
                ]
            ]);
        }
    }

    /**
     * Get upload statistics
     */
    public function getUploadStats()
    {
        $tanggalRekon = $this->request->getPost('tanggal') ?? $this->request->getGet('tanggal') ?? $this->prosesModel->getDefaultDate();
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi tidak ditemukan'
            ]);
        }

        try {
            $stats = [];
            $fileTypes = ['agn_detail', 'agn_settle_edu', 'agn_settle_pajak', 'agn_trx_mgate'];

            foreach ($fileTypes as $fileType) {
                $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
                $possibleExtensions = ['csv', 'xlsx', 'xls'];
                $fileInfo = null;

                foreach ($possibleExtensions as $ext) {
                    $filePath = $uploadPath . $fileType . '_' . $tanggalRekon . '.' . $ext;
                    if (file_exists($filePath)) {
                        $fileInfo = [
                            'exists' => true,
                            'filename' => basename($filePath),
                            'size' => filesize($filePath),
                            'size_formatted' => $this->formatFileSize(filesize($filePath)),
                            'modified' => date('Y-m-d H:i:s', filemtime($filePath)),
                            'rows' => $this->countFileRows($filePath)
                        ];
                        break;
                    }
                }

                if (!$fileInfo) {
                    $fileInfo = ['exists' => false];
                }

                $stats[$fileType] = $fileInfo;
            }

            return $this->response->setJSON([
                'success' => true,
                'stats' => $stats,
                'tanggal_rekon' => $tanggalRekon
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error getting stats: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate file content based on file type
     */
    private function validateFileContent($filePath, $fileType, $tanggalRekon)
    {
        $errors = [];
        
        try {
            // Basic file validation
            if (!is_readable($filePath)) {
                $errors[] = 'File tidak dapat dibaca';
                return ['valid' => false, 'errors' => $errors];
            }

            if (filesize($filePath) == 0) {
                $errors[] = 'File kosong';
                return ['valid' => false, 'errors' => $errors];
            }

            // Specific validation based on file type
            switch ($fileType) {
                case 'agn_trx_mgate':
                    // M-Gate file is mandatory and has specific requirements
                    $mgateValidation = $this->validateMgateFile($filePath, $tanggalRekon);
                    if (!$mgateValidation['valid']) {
                        $errors = array_merge($errors, $mgateValidation['errors']);
                    }
                    break;
                    
                default:
                    // Generic validation for other files
                    $genericValidation = $this->validateGenericFile($filePath, $tanggalRekon);
                    if (!$genericValidation['valid']) {
                        $errors = array_merge($errors, $genericValidation['errors']);
                    }
                    break;
            }

            return ['valid' => empty($errors), 'errors' => $errors];

        } catch (\Exception $e) {
            return ['valid' => false, 'errors' => ['Error validasi: ' . $e->getMessage()]];
        }
    }

    /**
     * Validate M-Gate file (mandatory file with specific rules)
     */
    private function validateMgateFile($filePath, $tanggalRekon)
    {
        $errors = [];
        
        // M-Gate file is mandatory
        if (!file_exists($filePath)) {
            $errors[] = 'File M-Gate wajib diupload';
            return ['valid' => false, 'errors' => $errors];
        }

        // Additional M-Gate specific validations can be added here
        // For example: check required columns, data format, etc.
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Generic file validation
     */
    private function validateGenericFile($filePath, $tanggalRekon)
    {
        $errors = [];
        
        // Add generic validation rules here
        // For example: check file format, required columns, etc.
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Read file preview (first N rows)
     */
    private function readFilePreview($filePath, $limit = 10)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $data = [];

        if (strtolower($extension) === 'csv') {
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                $rowCount = 0;
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE && $rowCount < $limit) {
                    $data[] = $row;
                    $rowCount++;
                }
                fclose($handle);
            }
        } else {
            // For Excel files, you would need a library like PhpSpreadsheet
            // For now, return placeholder data
            $data = [['Preview not available for Excel files']];
        }

        return $data;
    }

    /**
     * Count rows in file
     */
    private function countFileRows($filePath)
    {
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        
        if (strtolower($extension) === 'csv') {
            $rowCount = 0;
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rowCount++;
                }
                fclose($handle);
            }
            return $rowCount;
        }
        
        // For Excel files, return estimated count
        return 'N/A';
    }

    /**
     * Format file size for display
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
