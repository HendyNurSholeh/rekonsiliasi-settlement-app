<?php

namespace App\Models\Settlement;

use CodeIgniter\Model;

/**
 * Model untuk menangani data Jurnal CA to Escrow
 * Mengikuti best practices untuk database operations
 */
class JurnalCaEscrowModel extends Model
{
    protected $table = 'jurnal_ca_escrow_log';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'kd_settle', 'no_ref', 'amount', 'debit_account', 'credit_account',
        'status', 'response_code', 'core_ref', 'request_data', 'response_data',
        'processing_time', 'ip_address', 'user_agent', 'processed_at',
        'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Cek status pemrosesan jurnal berdasarkan kd_settle dan no_ref
     * 
     * @param string $kdSettle
     * @param string $noRef
     * @return array|null
     */
    public function getProcessStatus(string $kdSettle, string $noRef): ?array
    {
        return $this->where('kd_settle', $kdSettle)
                   ->where('no_ref', $noRef)
                   ->orderBy('created_at', 'DESC')
                   ->first();
    }

    /**
     * Insert log proses transaksi
     * 
     * @param array $data
     * @return int ID dari record yang di-insert
     */
    public function insertProcessLog(array $data): int
    {
        $this->insert($data);
        return $this->getInsertID();
    }

    /**
     * Update log proses transaksi
     * 
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateProcessLog(int $id, array $data): bool
    {
        return $this->update($id, $data);
    }

    /**
     * Update data jurnal di tabel utama berdasarkan referensi
     * Asumsi: ada tabel jurnal_ca_escrow yang menyimpan data utama
     * 
     * @param string $kdSettle
     * @param string $noRef
     * @param array $data
     * @return bool
     */
    public function updateJurnalByRef(string $kdSettle, string $noRef, array $data): bool
    {
        try {
            $db = \Config\Database::connect();
            
            // Update menggunakan stored procedure atau direct update
            // Sesuaikan dengan struktur database yang ada
            $sql = "UPDATE jurnal_ca_escrow 
                    SET d_CODE_RES = ?, d_CORE_REF = ?, d_CORE_DATETIME = ?
                    WHERE r_KD_SETTLE = ? AND d_NO_REF = ?";
            
            $query = $db->query($sql, [
                $data['d_CODE_RES'],
                $data['d_CORE_REF'], 
                $data['d_CORE_DATETIME'],
                $kdSettle,
                $noRef
            ]);

            return $query !== false;

        } catch (\Exception $e) {
            log_message('error', 'Failed to update jurnal data', [
                'kd_settle' => $kdSettle,
                'no_ref' => $noRef,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get history transaksi untuk audit trail
     * 
     * @param string $kdSettle
     * @param string $noRef
     * @return array
     */
    public function getTransactionHistory(string $kdSettle, string $noRef): array
    {
        return $this->where('kd_settle', $kdSettle)
                   ->where('no_ref', $noRef)
                   ->orderBy('created_at', 'ASC')
                   ->findAll();
    }

    /**
     * Get statistik transaksi untuk dashboard/monitoring
     * 
     * @param string $startDate
     * @param string $endDate
     * @return array
     */
    public function getTransactionStats(string $startDate, string $endDate): array
    {
        $sql = "SELECT 
                    status,
                    COUNT(*) as count,
                    SUM(amount) as total_amount,
                    AVG(processing_time) as avg_processing_time
                FROM {$this->table}
                WHERE DATE(created_at) BETWEEN ? AND ?
                GROUP BY status";

        $query = $this->db->query($sql, [$startDate, $endDate]);
        return $query->getResultArray();
    }

    /**
     * Clean up old logs (untuk maintenance)
     * 
     * @param int $daysToKeep
     * @return int Number of deleted records
     */
    public function cleanupOldLogs(int $daysToKeep = 90): int
    {
        $cutoffDate = date('Y-m-d', strtotime("-{$daysToKeep} days"));
        
        $deletedCount = $this->where('DATE(created_at) <', $cutoffDate)
                            ->where('status !=', 'PROCESSING') // Jangan hapus yang masih processing
                            ->countAllResults();

        $this->where('DATE(created_at) <', $cutoffDate)
             ->where('status !=', 'PROCESSING')
             ->delete();

        return $deletedCount;
    }
}
