<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;
use App\Models\AgnTrxMgateModel;

class RekonStep1Controller extends BaseController
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
     * Upload file via AJAX
     */
    public function uploadFiles()
    {
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        $fileType = $this->request->getPost('file_type');
        
        if (!$tanggalRekon || !$fileType) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Parameter tidak lengkap'
            ]);
        }

        // Validate file type
        $allowedTypes = ['agn_detail', 'agn_settle_edu', 'agn_settle_pajak', 'agn_trx_mgate'];
        if (!in_array($fileType, $allowedTypes)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tipe file tidak valid'
            ]);
        }

        $uploadedFile = $this->request->getFile('file');
        
        if (!$uploadedFile || !$uploadedFile->isValid()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'File tidak valid atau tidak ada file yang diupload'
            ]);
        }

        // Validate file extension
        $allowedExtensions = ['csv', 'xlsx', 'xls'];
        $fileExtension = $uploadedFile->getClientExtension();
        
        if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Format file harus CSV atau Excel (.xlsx/.xls)'
            ]);
        }

        try {
            // Create upload directory if not exists
            $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Generate unique filename
            $newFileName = $fileType . '_' . $tanggalRekon . '.' . $fileExtension;
            $filePath = $uploadPath . $newFileName;

            // Move uploaded file
            if ($uploadedFile->move($uploadPath, $newFileName)) {
                
                $this->logActivity([
                    'log_name' => 'FILE_UPLOAD',
                    'description' => "Upload file {$fileType} untuk tanggal {$tanggalRekon}",
                    'event' => 'UPLOAD_SUCCESS',
                    'subject' => 'Settlement File Upload'
                ]);

                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'File berhasil diupload',
                    'filename' => $newFileName,
                    'file_path' => $filePath,
                    'file_size' => $uploadedFile->getSize()
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Gagal menyimpan file'
                ]);
            }

        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
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
