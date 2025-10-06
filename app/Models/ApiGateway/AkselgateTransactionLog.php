<?php

namespace App\Models\ApiGateway;

use CodeIgniter\Model;

/**
 * Model untuk logging transaksi AKSEL Gateway
 * Support multiple transaction types (CA_ESCROW, ESCROW_BILLER_PL)
 */
class AkselgateTransactionLog extends Model
{
    protected $table = 't_akselgate_transaction_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'transaction_type',
        'kd_settle',
        'request_id',
        'attempt_number',
        'total_transaksi',
        'request_payload',
        'status_code_res',
        'response_code',
        'response_message',
        'response_payload',
        'is_success',
        'is_latest',
        'sent_by',
        'sent_at',
    ];
    
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
    
    // Transaction type constants
    const TYPE_CA_ESCROW = 'CA_ESCROW';
    const TYPE_ESCROW_BILLER_PL = 'ESCROW_BILLER_PL';
    
    /**
     * Get last process log untuk kd_settle dengan transaction_type tertentu
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Tipe transaksi
     * @return array|null
     */
    public function getLastProcess(string $kdSettle, string $transactionType): ?array
    {
        return $this->where('kd_settle', $kdSettle)
            ->where('transaction_type', $transactionType)
            ->orderBy('id', 'DESC')
            ->first();
    }
    
    /**
     * Get latest attempt (record dengan is_latest = 1)
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Tipe transaksi
     * @return array|null
     */
    public function getLatestAttempt(string $kdSettle, string $transactionType): ?array
    {
        return $this->where('kd_settle', $kdSettle)
            ->where('transaction_type', $transactionType)
            ->where('is_latest', 1)
            ->first();
    }
    
    /**
     * Get all attempts untuk kd_settle dan transaction_type
     * Untuk melihat history semua percobaan
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Tipe transaksi
     * @return array
     */
    public function getAllAttempts(string $kdSettle, string $transactionType): array
    {
        return $this->where('kd_settle', $kdSettle)
            ->where('transaction_type', $transactionType)
            ->orderBy('attempt_number', 'DESC')
            ->findAll();
    }
    
    /**
     * Get next attempt number untuk kd_settle dan transaction_type
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Tipe transaksi
     * @return int Next attempt number
     */
    public function getNextAttemptNumber(string $kdSettle, string $transactionType): int
    {
        $lastAttempt = $this->where('kd_settle', $kdSettle)
            ->where('transaction_type', $transactionType)
            ->orderBy('attempt_number', 'DESC')
            ->first();
        
        return $lastAttempt ? (int)$lastAttempt['attempt_number'] + 1 : 1;
    }
    
    /**
     * Cek apakah sudah ada record success (is_success = 1) untuk kd_settle dan transaction_type
     * Aturan: Jika sudah success, tidak boleh proses ulang
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Tipe transaksi
     * @return array|null Return record success jika ada, null jika tidak ada
     */
    public function checkSuccessExists(string $kdSettle, string $transactionType): ?array
    {
        return $this->where('kd_settle', $kdSettle)
            ->where('transaction_type', $transactionType)
            ->where('is_success', 1)
            ->first();
    }
    
    /**
     * Mark semua record lama sebagai not latest (is_latest = 0)
     * Digunakan sebelum insert attempt baru
     * 
     * @param string $kdSettle Kode settlement
     * @param string $transactionType Tipe transaksi
     * @return bool
     */
    public function markAsNotLatest(string $kdSettle, string $transactionType): bool
    {
        return $this->where('kd_settle', $kdSettle)
            ->where('transaction_type', $transactionType)
            ->set(['is_latest' => 0])
            ->update();
    }
    
    /**
     * Create new log entry
     * 
     * @param array $data Data log yang akan disimpan
     * @return int|bool Insert ID atau false jika gagal
     */
    public function createLog(array $data)
    {
        return $this->insert($data);
    }
    
    /**
     * Get logs by transaction type dengan pagination
     * 
     * @param string $transactionType Tipe transaksi
     * @param int $limit Limit per page
     * @param int $offset Offset untuk pagination
     * @return array
     */
    public function getLogsByType(string $transactionType, int $limit = 50, int $offset = 0): array
    {
        return $this->where('transaction_type', $transactionType)
            ->orderBy('sent_at', 'DESC')
            ->findAll($limit, $offset);
    }
    
    /**
     * Get statistics by transaction type
     * 
     * @param string $transactionType Tipe transaksi
     * @return array Statistics dengan total requests, success, failed, dan transactions
     */
    public function getStatsByType(string $transactionType): array
    {
        $result = $this->select('
            COUNT(*) as total_requests,
            SUM(CASE WHEN is_success = 1 THEN 1 ELSE 0 END) as total_success,
            SUM(CASE WHEN is_success = 0 THEN 1 ELSE 0 END) as total_failed,
            SUM(total_transaksi) as total_transactions
        ')
        ->where('transaction_type', $transactionType)
        ->first();
        
        return $result ?? [
            'total_requests' => 0,
            'total_success' => 0,
            'total_failed' => 0,
            'total_transactions' => 0,
        ];
    }
    
    /**
     * Get all logs dengan filter dan pagination
     * 
     * @param array $filters Filter conditions (transaction_type, kd_settle, is_success, date_from, date_to)
     * @param int $limit Limit per page
     * @param int $offset Offset untuk pagination
     * @return array
     */
    public function getFilteredLogs(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $builder = $this->builder();
        
        if (!empty($filters['transaction_type'])) {
            $builder->where('transaction_type', $filters['transaction_type']);
        }
        
        if (!empty($filters['kd_settle'])) {
            $builder->like('kd_settle', $filters['kd_settle']);
        }
        
        if (isset($filters['is_success'])) {
            $builder->where('is_success', $filters['is_success']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('sent_at >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('sent_at <=', $filters['date_to']);
        }
        
        return $builder->orderBy('sent_at', 'DESC')
            ->limit($limit, $offset)
            ->get()
            ->getResultArray();
    }
}
