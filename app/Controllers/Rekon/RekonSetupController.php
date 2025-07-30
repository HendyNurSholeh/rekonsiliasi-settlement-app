<?php

namespace App\Controllers\Rekon;

use App\Controllers\BaseController;
use App\Models\ProsesModel;
use App\Traits\HasLogActivity;

class RekonSetupController extends BaseController
{
    use HasLogActivity;
    
    protected $prosesModel;
    
    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
    }
    
    /**
     * Halaman setup awal - pilih tanggal dan buat proses
     */
    public function index()
    {
        // Get default date from database where status = 1 using ORM
        $defaultDate = $this->prosesModel->getDefaultDate();
        
        $data = [
            'title' => 'Setup Proses Rekonsiliasi Settlement',
            'route' => 'rekon',
            'defaultDate' => $defaultDate
        ];
        
        return $this->render('rekon/process/index.blade.php', $data);
    }
    
    /**
     * Buat proses rekonsiliasi baru
     */
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

            // Redirect to step 1 with tanggal parameter
            return redirect()->to('rekon/step1?tanggal=' . $tanggal)->with('success', $message);
            
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

        try {
            $result = $this->prosesModel->checkExistingProcess($tanggal);
            
            return $this->response->setJSON([
                'success' => true,
                'exists' => $result['exists'],
                'formatted_date' => date('d/m/Y', strtotime($tanggal)),
                'csrf_name' => csrf_token(),
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error checking date: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Reset process endpoint
     */
    public function resetProcess()
    {
        $tanggal = $this->request->getPost('tanggal');
        
        if (!$tanggal) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Tanggal tidak valid'
            ]);
        }

        try {
            $result = $this->prosesModel->resetProcess($tanggal);
            
            if ($result['success']) {
                // Process reset completed successfully
                
                $this->logActivity([
                    'log_name' => 'REKON_PROCESS',
                    'description' => 'Reset proses rekonsiliasi untuk tanggal: ' . $tanggal,
                    'event' => 'RESET_PROCESS',
                    'subject' => 'Rekonsiliasi Settlement'
                ]);
            }
            
            return $this->response->setJSON($result);
            
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Error resetting process: ' . $e->getMessage()
            ]);
        }
    }
}
