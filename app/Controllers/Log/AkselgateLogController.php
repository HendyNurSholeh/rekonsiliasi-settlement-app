<?php

namespace App\Controllers\Log;

use App\Controllers\BaseController;
use App\Libraries\EventLogEnum;
use App\Libraries\LogEnum;
use App\Models\ApiGateway\AkselgateTransactionLog;
use App\Traits\HasLogActivity;

class AkselgateLogController extends BaseController
{
    use HasLogActivity;
    
    protected $logModel;

    public function __construct()
    {
        $this->logModel = new AkselgateTransactionLog();
    }

    /**
     * Index page - Display Akselgate Transaction Logs
     */
    public function index()
    {
        // Get tanggal dari query parameter atau default ke hari ini
        $tanggalData = $this->request->getGet('tanggal') ?? date('Y-m-d');

        $data = [
            'title' => 'Log Transaksi Akselgate',
            'tanggalData' => $tanggalData,
            'route' => 'log/akselgate'
        ];

        $this->logActivity([
            'log_name' => LogEnum::VIEW,
            'description' => session('username') . ' mengakses Halaman ' . $data['title'],
            'event' => EventLogEnum::VERIFIED,
            'subject' => '-',
        ]);

        return $this->render('log/akselgate/index.blade.php', $data);
    }

    /**
     * DataTables AJAX endpoint
     */
    public function datatable()
    {
        try {
            // Get filter parameters
            $tanggalData = $this->request->getPost('tanggal') ?? date('Y-m-d');
            $transactionType = $this->request->getPost('transaction_type') ?? '';
            $status = $this->request->getPost('status') ?? ''; // 'success', 'failed', 'all'
            $kdSettle = $this->request->getPost('kd_settle') ?? '';
            
            // Get DataTables parameters
            $draw = (int) ($this->request->getPost('draw') ?? 1);
            $start = (int) ($this->request->getPost('start') ?? 0);
            $length = (int) ($this->request->getPost('length') ?? 10);
            $searchValue = $this->request->getPost('search')['value'] ?? '';
            $orderColumnIndex = (int) ($this->request->getPost('order')[0]['column'] ?? 0);
            $orderDir = $this->request->getPost('order')[0]['dir'] ?? 'desc';
            
            // Column mapping untuk ordering
            $columns = [
                0 => 'id',
                1 => 'created_at',
                2 => 'transaction_type',
                3 => 'kd_settle',
                4 => 'request_id',
                5 => 'attempt_number',
                6 => 'total_transaksi',
                7 => 'status_code_res',
                8 => 'is_success'
            ];
            $orderColumn = $columns[$orderColumnIndex] ?? 'created_at';
            
            // Build query
            $builder = $this->logModel->builder();
            
            // Apply date filter (YYYY-MM-DD format from created_at datetime)
            $builder->where('DATE(created_at)', $tanggalData);
            
            // Apply transaction type filter
            if (!empty($transactionType)) {
                $builder->where('transaction_type', $transactionType);
            }
            
            // Apply status filter
            if ($status === 'success') {
                $builder->where('is_success', 1);
            } elseif ($status === 'failed') {
                $builder->where('is_success', 0);
            }
            // 'all' means no filter
            
            // Apply kd_settle search
            if (!empty($kdSettle)) {
                $builder->like('kd_settle', $kdSettle);
            }
            
            // Apply global search
            if (!empty($searchValue)) {
                $builder->groupStart()
                    ->like('kd_settle', $searchValue)
                    ->orLike('request_id', $searchValue)
                    ->orLike('transaction_type', $searchValue)
                    ->orLike('response_message', $searchValue)
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
            
            // Get total records without filter (dari table)
            $recordsTotal = $this->logModel->countAll();
            
            // Format data for DataTable
            $formattedData = [];
            foreach ($data as $row) {
                $formattedData[] = [
                    'id' => $row['id'],
                    'created_at' => $row['created_at'],
                    'transaction_type' => $row['transaction_type'],
                    'kd_settle' => $row['kd_settle'],
                    'request_id' => $row['request_id'],
                    'attempt_number' => $row['attempt_number'],
                    'total_transaksi' => $row['total_transaksi'],
                    'status_code_res' => $row['status_code_res'],
                    'response_code' => $row['response_code'],
                    'response_message' => $row['response_message'],
                    'is_success' => $row['is_success'],
                    'is_latest' => $row['is_latest'],
                    'request_payload' => $row['request_payload'],
                    'response_payload' => $row['response_payload'],
                ];
            }
            
            // Return DataTables response
            return $this->response->setJSON([
                'draw' => $draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $formattedData,
                'csrf_token' => csrf_hash()
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error in Akselgate Log DataTable: ' . $e->getMessage());
            return $this->response->setJSON([
                'draw' => $draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => [],
                'error' => 'Terjadi kesalahan saat mengambil data: ' . $e->getMessage(),
                'csrf_token' => csrf_hash()
            ]);
        }
    }

    /**
     * Get detail log by ID (untuk modal detail)
     */
    public function detail($id)
    {
        try {
            $log = $this->logModel->find($id);
            
            if (!$log) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Log tidak ditemukan'
                ])->setStatusCode(404);
            }
            
            return $this->response->setJSON([
                'success' => true,
                'data' => [
                    'id' => $log['id'],
                    'created_at' => $log['created_at'],
                    'transaction_type' => $log['transaction_type'],
                    'kd_settle' => $log['kd_settle'],
                    'request_id' => $log['request_id'],
                    'attempt_number' => $log['attempt_number'],
                    'total_transaksi' => $log['total_transaksi'],
                    'status_code_res' => $log['status_code_res'],
                    'response_code' => $log['response_code'],
                    'response_message' => $log['response_message'],
                    'is_success' => $log['is_success'],
                    'is_latest' => $log['is_latest'],
                    'request_payload' => $log['request_payload'],
                    'response_payload' => $log['response_payload'],
                ]
            ]);
            
        } catch (\Exception $e) {
            log_message('error', 'Error getting log detail: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ])->setStatusCode(500);
        }
    }
}
