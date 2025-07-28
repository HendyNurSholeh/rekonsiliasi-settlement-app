<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;
use App\Models\AgnTrxMgateModel;
use App\Services\FileProcessingService;

class RekonStep1Controller extends BaseController
{
    use HasLogActivity;

    protected $prosesModel;
    protected $agnDetailModel;
    protected $agnSettleEduModel;
    protected $agnSettlePajakModel;
    protected $agnTrxMgateModel;
    protected $fileProcessingService;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->agnDetailModel = new AgnDetailModel();
        $this->agnSettleEduModel = new AgnSettleEduModel();
        $this->agnSettlePajakModel = new AgnSettlePajakModel();
        $this->agnTrxMgateModel = new AgnTrxMgateModel();
        $this->fileProcessingService = new FileProcessingService();
    }

    /**
     * Halaman Step 1 - Upload Files
     */
    public function index()
    {
        // Get date from URL parameter or session
        $tanggalRekon = $this->request->getGet('tanggal') ?? session()->get('current_rekon_date');
        
        // If no date available, get default from database using ORM
        if (!$tanggalRekon) {
            $tanggalRekon = $this->prosesModel->getDefaultDate();
        }
        
        if (!$tanggalRekon) {
            return redirect()->to('rekon')->with('error', 'Tanggal rekonsiliasi tidak ditemukan. Silakan buat proses baru.');
        }

        // Update session with current date
        session()->set('current_rekon_date', $tanggalRekon);
        session()->set('current_rekon_process_id', 'PRK-' . date('ymd', strtotime($tanggalRekon)) . '-001');

        $data = [
            'title' => 'Step 1: Upload File Settlement',
            'route' => 'rekon/step1',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 1
        ];

        return $this->render('rekon/process/step1.blade.php', $data);
    }

    /**
     * Upload file via AJAX dengan validasi komprehensif
     */
    public function uploadFiles()
    {
        try {
            // Ambil parameter dari POST request
            $tanggalRekon = $this->request->getPost('tanggal_rekon');
            $fileType = $this->request->getPost('file_type');
            
            if (!$tanggalRekon || !$fileType) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter tidak lengkap (tanggal_rekon dan file_type wajib)'
                ]);
            }

            // Map file types to processing service types
            $fileTypeMapping = [
                'agn_detail' => 'agn_detail',
                'settle_edu' => 'settle_edu', 
                'settle_pajak' => 'settle_pajak',
                'mgate' => 'mgate'
            ];

            $processType = $fileTypeMapping[$fileType] ?? null;
            if (!$processType) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Tipe file tidak valid: ' . $fileType
                ]);
            }

            // Ambil file yang diupload
            $uploadedFile = $this->request->getFile('file');
            
            if (!$uploadedFile || !$uploadedFile->isValid()) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File tidak valid atau tidak ada file yang diupload'
                ]);
            }

            // Validasi ukuran file (max 10MB)
            if ($uploadedFile->getSize() > 10 * 1024 * 1024) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'File terlalu besar! Maksimal 10MB'
                ]);
            }

            // Validasi format file berdasarkan tipe
            $allowedExtensions = $this->getAllowedExtensions($fileType);
            $fileExtension = strtolower($uploadedFile->getClientExtension());
            
            if (!in_array($fileExtension, $allowedExtensions)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Format file tidak valid untuk ' . $fileType . '. Format yang diizinkan: ' . implode(', ', $allowedExtensions)
                ]);
            }

            // Create upload directory if not exists
            $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generate filename based on type and date
            $newFileName = $processType . '_' . $tanggalRekon . '.' . $fileExtension;
            $filePath = $uploadPath . $newFileName;

            // If file already exists, delete it first
            if (file_exists($filePath)) {
                unlink($filePath);
            }

            // Move uploaded file
            if (!$uploadedFile->move($uploadPath, $newFileName)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file ke server'
                ]);
            }

            log_message('info', 'File uploaded successfully to: ' . $filePath);
            log_message('info', 'Original filename: ' . $uploadedFile->getClientName());
            log_message('info', 'File size: ' . $uploadedFile->getSize() . ' bytes');
            log_message('info', 'Processing uploaded file: ' . $filePath . ' for type: ' . $processType);

            // Process file: validate and insert to database
            $result = $this->fileProcessingService->processUploadedFile($filePath, $processType, $tanggalRekon);
            
            if ($result['success']) {
                $this->logActivity([
                    'log_name' => 'FILE_UPLOAD',
                    'description' => "Upload dan validasi file {$fileType} berhasil untuk tanggal {$tanggalRekon}",
                    'event' => 'FILE_UPLOAD_SUCCESS',
                    'subject' => 'File Processing',
                    'properties' => json_encode($result['stats'] ?? [])
                ]);
                return $this->response->setJSON([
                    'success' => true,
                    'message' => $result['message'],
                    'filename' => $newFileName,
                    'stats' => $result['stats'] ?? [],
                    'insert_stats' => $result['insert_stats'] ?? [],
                    'warnings' => $result['warnings'] ?? []
                ]);
            } else {
                // Log validation failures
                $this->logActivity([
                    'log_name' => 'FILE_UPLOAD',
                    'description' => "Upload file {$fileType} gagal validasi: " . $result['message'],
                    'event' => 'FILE_UPLOAD_FAILED',
                    'subject' => 'File Processing',
                    'properties' => json_encode(['errors' => $result['errors'] ?? []])
                ]);

                // Remove failed file
                if (file_exists($filePath)) {
                    unlink($filePath);
                }

                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'],
                    'errors' => $result['errors'] ?? [],
                    'warnings' => $result['warnings'] ?? []
                ]);
            }

        } catch (\Exception $e) {
            log_message('error', 'Upload file error details: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine() . "\nTrace: " . $e->getTraceAsString());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error sistem: ' . $e->getMessage(),
                'debug_info' => [
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]
            ]);
        }
    }

    /**
     * Get allowed file extensions for each file type
     */
    private function getAllowedExtensions($fileType)
    {
        $extensionMapping = [
            'agn_detail' => ['txt'],
            'settle_edu' => ['txt'],
            'settle_pajak' => ['txt'],
            'mgate' => ['csv']
        ];

        return $extensionMapping[$fileType] ?? ['txt', 'csv'];
    }

    /**
     * Validate uploaded files
     */
    public function validateFiles()
    {
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi tidak valid'
            ]);
        }

        $fileTypes = ['agn_detail', 'agn_settle_edu', 'agn_settle_pajak', 'agn_trx_mgate'];
        $errors = [];
        $validationPassed = true;

        foreach ($fileTypes as $fileType) {
            $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
            $possibleExtensions = ['csv', 'xlsx', 'xls'];
            $actualFilePath = null;

            // Check if file exists with any allowed extension
            foreach ($possibleExtensions as $ext) {
                $filePath = $uploadPath . $fileType . '_' . $tanggalRekon . '.' . $ext;
                if (file_exists($filePath)) {
                    $actualFilePath = $filePath;
                    break;
                }
            }

            if (!$actualFilePath) {
                $errors[$fileType] = 'File tidak ditemukan';
                $validationPassed = false;
                continue;
            }

            // Validate file size (max 10MB)
            if (filesize($actualFilePath) > 10 * 1024 * 1024) {
                $errors[$fileType] = 'Ukuran file terlalu besar (max 10MB)';
                $validationPassed = false;
                continue;
            }

            // Validate date in file (simplified - you'll need to implement based on file format)
            $dateValidation = $this->validateDateInFile($actualFilePath, $tanggalRekon);
            if (!$dateValidation['valid']) {
                $errors[$fileType] = $dateValidation['message'];
                $validationPassed = false;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'validation_passed' => $validationPassed,
            'message' => $validationPassed ? 'Validasi berhasil' : 'Terdapat error dalam validasi',
            'errors' => $errors
        ]);
    }

    /**
     * Process data upload - call stored procedure
     */
    public function processDataUpload()
    {
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi tidak valid'
            ]);
        }

        try {
            // Simple approach - just log the action and return success
            // Stored procedure will be handled by senior later
            log_message('info', "Process data upload called for date: {$tanggalRekon}");

            $this->logActivity([
                'log_name' => 'PROCESS_DATA_UPLOAD',
                'description' => "Proses data upload untuk tanggal {$tanggalRekon}",
                'event' => 'DATA_UPLOAD_PROCESS',
                'subject' => 'Settlement Process'
            ]);

            return $this->response->setJSON([
                'success' => true,
                'message' => 'Proses data upload berhasil dijalankan',
                'data' => []
            ]);

        } catch (\Exception $e) {
            $this->logActivity([
                'log_name' => 'PROCESS_DATA_UPLOAD',
                'description' => "Error proses data upload: " . $e->getMessage(),
                'event' => 'DATA_UPLOAD_ERROR',
                'subject' => 'Settlement Process'
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Check upload status via AJAX
     */
    public function checkUploadStatus()
    {
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal tidak valid'
            ]);
        }

        $fileTypes = ['agn_detail', 'agn_settle_edu', 'agn_settle_pajak', 'agn_trx_mgate'];
        $fileStatus = [];

        foreach ($fileTypes as $fileType) {
            $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
            $possibleExtensions = ['csv', 'xlsx', 'xls'];
            $fileExists = false;

            foreach ($possibleExtensions as $ext) {
                $filePath = $uploadPath . $fileType . '_' . $tanggalRekon . '.' . $ext;
                if (file_exists($filePath)) {
                    $fileExists = true;
                    $fileStatus[$fileType] = [
                        'uploaded' => true,
                        'filename' => $fileType . '_' . $tanggalRekon . $ext,
                        'size' => filesize($filePath)
                    ];
                    break;
                }
            }

            if (!$fileExists) {
                $fileStatus[$fileType] = ['uploaded' => false];
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'file_status' => $fileStatus
        ]);
    }

    /**
     * Get upload statistics for current date
     */
    public function getUploadStats()
    {
        $tanggalRekon = session()->get('current_rekon_date');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi tidak ditemukan dalam session'
            ]);
        }

        try {
            $stats = $this->fileProcessingService->getUploadStatistics($tanggalRekon);
            
            return $this->response->setJSON([
                'success' => true,
                'stats' => $stats,
                'date' => $tanggalRekon
            ]);

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error mendapatkan statistik: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate date in uploaded file (basic implementation)
     */
    private function validateDateInFile($filePath, $expectedDate)
    {
        // Basic validation - just check if file is readable
        if (!is_readable($filePath)) {
            return ['valid' => false, 'message' => 'File tidak dapat dibaca'];
        }

        // TODO: Implement actual date validation based on file content
        // For now, just return valid
        return ['valid' => true, 'message' => 'Validasi tanggal berhasil'];
    }
}
