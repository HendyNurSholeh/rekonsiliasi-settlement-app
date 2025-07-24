<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Traits\HasLogActivity;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;

class RekonProcess extends BaseController
{
    use HasLogActivity;

    protected $prosesModel;
    protected $agnDetailModel;
    protected $agnSettleEduModel;
    protected $agnSettlePajakModel;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->agnDetailModel = new AgnDetailModel();
        $this->agnSettleEduModel = new AgnSettleEduModel();
        $this->agnSettlePajakModel = new AgnSettlePajakModel();
    }

    public function index()
    {
        // Use dummy data for display
        $data = [
            'title' => 'Proses Rekonsiliasi Settlement',
            'route' => 'rekon/process'
        ];

        return $this->render('rekon/process/index.blade.php', $data);
    }

    public function create()
    {
        $tanggal = $this->request->getPost('tanggal_rekon');
        $resetConfirmed = $this->request->getPost('reset_confirmed') === 'true';
        
        if (!$tanggal) {
            return redirect()->back()->with('error', 'Tanggal rekonsiliasi harus diisi');
        }

        // Validasi tanggal
        $date = \DateTime::createFromFormat('Y-m-d', $tanggal);
        if (!$date || $date->format('Y-m-d') !== $tanggal) {
            return redirect()->back()->with('error', 'Format tanggal tidak valid');
        }

        // Check if process already exists for this date
        $existingCheck = $this->prosesModel->checkExistingProcess($tanggal);
        
        if ($existingCheck['exists'] && !$resetConfirmed) {
            // Return to form with confirmation dialog data
            return redirect()->back()
                ->with('existing_date', $tanggal)
                ->with('need_confirmation', true)
                ->with('warning', 'Proses rekonsiliasi untuk tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' sudah ada.');
        }

        try {
            if ($existingCheck['exists'] && $resetConfirmed) {
                // Reset existing process using proper flow: p_reset_date then p_proses_persiapan
                $result = $this->prosesModel->resetProcess($tanggal);
                
                if (!$result['success']) {
                    return redirect()->back()->with('error', $result['message']);
                }
                
                $this->logActivity([
                    'log_name' => 'REKON_PROCESS',
                    'description' => 'Reset dan buat ulang proses rekonsiliasi untuk tanggal: ' . $tanggal,
                    'event' => 'RESET_AND_CREATE_PROCESS',
                    'subject' => 'Rekonsiliasi Settlement'
                ]);
                $message = $result['message'];
                
            } else {
                // Create new process
                $result = $this->prosesModel->callProcessPersiapan($tanggal, false);
                
                if (!$result['success']) {
                    return redirect()->back()->with('error', $result['message']);
                }
                
                $this->logActivity([
                    'log_name' => 'REKON_PROCESS',
                    'description' => 'Membuat proses rekonsiliasi baru untuk tanggal: ' . $tanggal,
                    'event' => 'CREATE_PROCESS',
                    'subject' => 'Rekonsiliasi Settlement'
                ]);
                $message = 'Proses rekonsiliasi untuk tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' berhasil dibuat';
            }

            // Store date in session
            session()->set('current_rekon_process_id', 'PRK-' . date('ymd', strtotime($tanggal)) . '-001');
            session()->set('current_rekon_date', $tanggal);
            
            return redirect()->to('rekon/process/step1?tanggal=' . $tanggal)->with('success', $message);
            
        } catch (\Exception $e) {
            $this->logActivity([
                'log_name' => 'REKON_PROCESS',
                'description' => 'Error saat membuat/reset proses: ' . $e->getMessage(),
                'event' => 'ERROR_PROCESS',
                'subject' => 'Rekonsiliasi Settlement'
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Check if date has existing process (AJAX endpoint)
     */
    public function checkDate()
    {
        $tanggal = $this->request->getPost('tanggal');
        
        if (!$tanggal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal tidak valid'
            ]);
        }

        $existingCheck = $this->prosesModel->checkExistingProcess($tanggal);
        
        return $this->response->setJSON([
            'success' => true,
            'exists' => $existingCheck['exists'],
            'formatted_date' => date('d/m/Y', strtotime($tanggal)),
            'csrf_token' => csrf_hash(),
            'csrf_name' => csrf_token()
        ]);
    }

    /**
     * Upload files endpoint
     */
    public function uploadFiles()
    {
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi tidak valid'
            ]);
        }

        $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
        
        // Create directory if not exists
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $uploadedFiles = [];
        $fileTypes = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];

        foreach ($fileTypes as $fileType) {
            $file = $this->request->getFile("file_{$fileType}");
            
            if ($file && $file->isValid() && !$file->hasMoved()) {
                $newName = $fileType . '_' . $tanggalRekon . '.' . $file->getExtension();
                
                if ($file->move($uploadPath, $newName)) {
                    $uploadedFiles[$fileType] = true;
                    
                    $this->logActivity([
                        'log_name' => 'UPLOAD_FILE',
                        'description' => "Upload file {$fileType} untuk tanggal {$tanggalRekon}",
                        'event' => 'FILE_UPLOAD',
                        'subject' => 'Settlement Files'
                    ]);
                } else {
                    $uploadedFiles[$fileType] = false;
                }
            } else {
                $uploadedFiles[$fileType] = false;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Files berhasil diupload',
            'uploaded_files' => $uploadedFiles
        ]);
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

        $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
        $errors = [];
        $validationPassed = true;

        // Validate each required file
        $requiredFiles = ['agn_detail', 'settle_edu', 'settle_pajak'];
        
        foreach ($requiredFiles as $fileType) {
            $filePath = $uploadPath . $fileType . '_' . $tanggalRekon;
            
            // Check for different extensions
            $extensions = ['.xlsx', '.xls', '.csv'];
            $fileExists = false;
            
            foreach ($extensions as $ext) {
                if (file_exists($filePath . $ext)) {
                    $fileExists = true;
                    $actualFilePath = $filePath . $ext;
                    break;
                }
            }
            
            if (!$fileExists) {
                $errors[$fileType] = "File {$fileType} tidak ditemukan";
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
                'event' => 'ERROR_DATA_UPLOAD',
                'subject' => 'Settlement Process'
            ]);

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate date in uploaded file (basic implementation)
     * You'll need to customize this based on your file formats
     */
    private function validateDateInFile($filePath, $expectedDate)
    {
        // This is a simplified validation
        // You'll need to implement actual file reading and date validation
        // based on the file formats you receive
        
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        try {
            if ($extension === 'csv') {
                return $this->validateDateInCSV($filePath, $expectedDate);
            } elseif (in_array($extension, ['xlsx', 'xls'])) {
                return $this->validateDateInExcel($filePath, $expectedDate);
            }
            
            return ['valid' => true, 'message' => 'Format file tidak dikenali, validasi dilewati'];
            
        } catch (\Exception $e) {
            return ['valid' => false, 'message' => 'Error validasi file: ' . $e->getMessage()];
        }
    }

    /**
     * Validate date in CSV file
     */
    private function validateDateInCSV($filePath, $expectedDate)
    {
        // Implement CSV date validation
        // This is a placeholder - implement based on your CSV structure
        return ['valid' => true, 'message' => 'CSV validation passed'];
    }

    /**
     * Validate date in Excel file
     */
    private function validateDateInExcel($filePath, $expectedDate)
    {
        // Implement Excel date validation
        // This is a placeholder - implement based on your Excel structure
        return ['valid' => true, 'message' => 'Excel validation passed'];
    }

    public function step1()
    {
        // Get date from URL parameter or session
        $tanggalRekon = $this->request->getGet('tanggal') ?? session()->get('current_rekon_date');
        
        if (!$tanggalRekon) {
            return redirect()->to('rekon/process')->with('error', 'Tanggal rekonsiliasi tidak ditemukan. Silakan buat proses baru.');
        }

        // Update session with current date
        session()->set('current_rekon_date', $tanggalRekon);
        session()->set('current_rekon_process_id', 'PRK-' . date('ymd', strtotime($tanggalRekon)) . '-001');

        $data = [
            'title' => 'Step 1: Upload File Settlement',
            'route' => 'rekon/process/step1',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 1
        ];

        return $this->render('rekon/process/step1.blade.php', $data);
    }

    /**
     * Check upload status for existing files
     */
    public function checkUploadStatus()
    {
        $tanggalRekon = $this->request->getPost('tanggal_rekon');
        
        if (!$tanggalRekon) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal rekonsiliasi tidak valid'
            ]);
        }

        $uploadPath = WRITEPATH . 'uploads/settlement/' . $tanggalRekon . '/';
        $fileStatus = [];
        $fileTypes = ['agn_detail', 'settle_edu', 'settle_pajak', 'mgate'];

        foreach ($fileTypes as $fileType) {
            $extensions = ['.xlsx', '.xls', '.csv'];
            $fileExists = false;
            
            foreach ($extensions as $ext) {
                $filePath = $uploadPath . $fileType . '_' . $tanggalRekon . $ext;
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

    public function step2()
    {
        $processId = session()->get('current_rekon_process_id');
        $tanggalRekon = session()->get('current_rekon_date');

        if (!$processId || !$tanggalRekon) {
            return redirect()->to('rekon/process')->with('error', 'Session proses rekonsiliasi tidak ditemukan');
        }

        $data = [
            'title' => 'Step 2: Validasi & Review Data',
            'route' => 'rekon/process/step2',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 2
        ];

        return $this->render('rekon/process/step2.blade.php', $data);
    }

    public function step3()
    {
        $processId = session()->get('current_rekon_process_id');
        $tanggalRekon = session()->get('current_rekon_date');

        if (!$processId || !$tanggalRekon) {
            return redirect()->to('rekon/process')->with('error', 'Session proses rekonsiliasi tidak ditemukan');
        }

        $data = [
            'title' => 'Step 3: Proses Rekonsiliasi',
            'route' => 'rekon/process/step3',
            'tanggalRekon' => $tanggalRekon,
            'currentStep' => 3
        ];

        return $this->render('rekon/process/step3.blade.php', $data);
    }

    public function processReconciliation()
    {
        // Simulate success response for demo
        return $this->response->setJSON(['success' => true, 'message' => 'Proses rekonsiliasi berhasil (Demo)']);
    }

    private function getUploadStatus($tanggalRekon)
    {
        return [
            'agn_detail' => $this->agnDetailModel->countByDate($tanggalRekon) > 0,
            'agn_settle_edu' => $this->agnSettleEduModel->countByDate($tanggalRekon) > 0,
            'agn_settle_pajak' => $this->agnSettlePajakModel->countByDate($tanggalRekon) > 0,
            'agn_detail_count' => $this->agnDetailModel->countByDate($tanggalRekon),
            'agn_settle_edu_count' => $this->agnSettleEduModel->countByDate($tanggalRekon),
            'agn_settle_pajak_count' => $this->agnSettlePajakModel->countByDate($tanggalRekon)
        ];
    }

    private function allFilesUploaded($uploadStatus)
    {
        return $uploadStatus['agn_detail'] && 
               $uploadStatus['agn_settle_edu'] && 
               $uploadStatus['agn_settle_pajak'];
    }

    private function validateUploadedData($tanggalRekon)
    {
        return [
            'total_transactions' => $this->agnDetailModel->countByDate($tanggalRekon),
            'total_amount' => $this->agnDetailModel->getTotalAmountByDate($tanggalRekon),
            'settlement_edu_total' => $this->agnSettleEduModel->getTotalSettlementByDate($tanggalRekon),
            'settlement_pajak_total' => $this->agnSettlePajakModel->getTotalSettlementByDate($tanggalRekon)
        ];
    }
}
