<?php

namespace App\Controllers\Log;

use App\Controllers\BaseController;
use App\Models\ApiGateway\AkselgateFwdCallbackLog;

/**
 * Akselgate FWD Callback Log Controller
 * 
 * Controller untuk menampilkan dan mengelola log callback dari Aksel FWD Gateway
 * 
 * Features:
 * - View callback logs dengan DataTable
 * - Filter by tanggal, kd_settle, status
 * - Detail modal untuk melihat callback data lengkap
 * - Server-side processing untuk performa optimal
 */
class AkselgateFwdCallbackLogController extends BaseController
{
    protected $callbackLogModel;
    protected $permissions;

    public function __construct()
    {
        $this->callbackLogModel = new AkselgateFwdCallbackLog();
        
        // Get user permissions from session
        $this->permissions = session()->get('permissions') ?? [];
    }

    /**
     * Display callback log page
     * 
     * @return string
     */
    public function index()
    {
        // Check permission
        if (!in_array('view log callback', $this->permissions)) {
            return redirect()->to('/dashboard')->with('error', 'Anda tidak memiliki akses ke halaman ini');
        }

        // Get tanggal dari query parameter atau default ke hari ini
        $tanggalData = $this->request->getGet('tanggal') ?? date('Y-m-d');

        $data = [
            'title' => 'Log Callback Akselgate FWD',
            'tanggalData' => $tanggalData,
            'route' => 'log/callback'
        ];

        return $this->render('log/akselgate_fwd_callback/index.blade.php', $data);
    }

    /**
     * DataTable server-side processing
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function datatable()
    {
        // Check permission
        if (!in_array('view log callback', $this->permissions)) {
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Unauthorized access',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            // Get filter parameters dari POST (dikirim via DataTable AJAX)
            $tanggal = $this->request->getPost('tanggal') ?? date('Y-m-d');
            $kdSettle = $this->request->getPost('kd_settle') ?? '';
            $status = $this->request->getPost('status') ?? '';
            
            // Get DataTable parameters
            $draw = (int) ($this->request->getPost('draw') ?? 1);
            $start = (int) ($this->request->getPost('start') ?? 0);
            $length = (int) ($this->request->getPost('length') ?? 10);
            $searchValue = $this->request->getPost('search')['value'] ?? '';
            $orderColumnIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
            $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
            
            // Column mapping for ordering
            $columns = ['id', 'created_at', 'ref_number', 'kd_settle', 'res_code', 'status', 'is_processed'];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';

            // Build query
            $builder = $this->callbackLogModel->builder();

            // Apply date filter (sama seperti AkselgateLogController)
            $builder->where('DATE(created_at)', $tanggal);
            
            // Apply kd_settle filter
            if (!empty($kdSettle)) {
                $builder->like('kd_settle', $kdSettle);
            }
            
            // Apply status filter
            if (!empty($status)) {
                $builder->where('status', $status);
            }

            // Apply global search (jika ada)
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('ref_number', $searchValue)
                    ->orLike('kd_settle', $searchValue)
                    ->orLike('res_code', $searchValue)
                    ->orLike('res_coreref', $searchValue)
                    ->groupEnd();
            }

            // Get total records after filtering
            $recordsFiltered = $builder->countAllResults(false);

            // Apply ordering
            $builder->orderBy($orderColumn, $orderDir);

            // Apply pagination
            $builder->limit($length, $start);

            // Get data
            $data = $builder->get()->getResultArray();

            // Get total records without filter
            $recordsTotal = $this->callbackLogModel->countAll();

            // Return response
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'DataTable error in AkselgateFwdCallbackLogController: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'draw' => 0,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Get callback detail by ID
     * 
     * @param int $id
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function detail($id)
    {
        // Check permission
        if (!in_array('view log callback', $this->permissions)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Unauthorized access',
                'csrf_token' => csrf_hash()
            ]);
        }

        try {
            $callback = $this->callbackLogModel->find($id);
            
            if (!$callback) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Callback log tidak ditemukan',
                    'csrf_token' => csrf_hash()
                ])->setStatusCode(404);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => $callback,
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting callback detail: ' . $e->getMessage());
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ])->setStatusCode(500);
        }
    }
}
