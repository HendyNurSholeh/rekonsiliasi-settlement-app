<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;
use App\Models\AgnTrxMgateModel;

class RekonStep2Controller extends BaseController
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
     * Halaman Step 2 - Validasi & Review Data
     */
    public function index()
    {
        $processId = session()->get('current_rekon_process_id');
        $tanggalRekon = session()->get('current_rekon_date');

        // If no date in session, get default from database using ORM
        if (!$tanggalRekon) {
            $tanggalRekon = $this->prosesModel->getDefaultDate();
            session()->set('current_rekon_date', $tanggalRekon);
        }

        if (!$processId || !$tanggalRekon) {
            return redirect()->to('rekon')->with('error', 'Session proses rekonsiliasi tidak ditemukan');
        }

        $data = [
            'title' => 'Step 2: Validasi & Review Data',
            'route' => 'rekon/step2',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 2
        ];

        return $this->render('rekon/process/step2.blade.php', $data);
    }

    /**
     * Validate data and prepare for reconciliation
     */
    public function processValidation()
    {
        $tanggalRekon = session()->get('current_rekon_date');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session tidak valid'
            ]);
        }

        try {
            // Check if all required files are uploaded
            $fileTypes = ['agn_detail', 'agn_settle_edu', 'agn_settle_pajak', 'agn_trx_mgate'];
            $missingFiles = [];
            $validationErrors = [];

            foreach ($fileTypes as $fileType) {
                $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
                $possibleExtensions = ['csv', 'xlsx', 'xls'];
                $fileExists = false;

                foreach ($possibleExtensions as $ext) {
                    $filePath = $uploadPath . $fileType . '_' . $tanggalRekon . '.' . $ext;
                    if (file_exists($filePath)) {
                        $fileExists = true;
                        
                        // Validate file content
                        $validation = $this->validateFileContent($filePath, $fileType, $tanggalRekon);
                        if (!$validation['valid']) {
                            $validationErrors[$fileType] = $validation['errors'];
                        }
                        break;
                    }
                }

                if (!$fileExists) {
                    $missingFiles[] = $fileType;
                }
            }

            $success = empty($missingFiles) && empty($validationErrors);

            if ($success) {
                $this->logActivity([
                    'log_name' => 'DATA_VALIDATION',
                    'description' => "Validasi data berhasil untuk tanggal {$tanggalRekon}",
                    'event' => 'VALIDATION_SUCCESS',
                    'subject' => 'Settlement Data Validation'
                ]);
            }

            return $this->response->setJSON([
                'success' => $success,
                'message' => $success ? 'Validasi data berhasil' : 'Terdapat error dalam validasi',
                'missing_files' => $missingFiles,
                'validation_errors' => $validationErrors,
                'can_proceed' => $success
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error validasi: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get data preview for a specific file type
     */
    public function getDataPreview()
    {
        $fileType = $this->request->getGet('file_type');
        $tanggalRekon = session()->get('current_rekon_date');
        
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
     * Get upload statistics
     */
    public function getUploadStats()
    {
        $tanggalRekon = session()->get('current_rekon_date');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Session tidak valid'
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
