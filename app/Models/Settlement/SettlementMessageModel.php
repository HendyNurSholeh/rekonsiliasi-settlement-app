<?php

namespace App\Models\Settlement;

use CodeIgniter\Model;

/**
 * Model untuk menangani data Settlement utama
 * Tabel yang menyimpan semua data settlement CA-ESCR dan ESCR-BILR
 */
class SettlementMessageModel extends Model
{
    protected $table = 't_settle_message';
    protected $primaryKey = 'ID';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'KD_SETTLE', 'JENIS_SETTLE', 'TGL_DATA', 'NAMA_PRODUK', 'IDPARTNER',
        'CORE', 'BRANCH_CODE', 'BRANCH_NAME', 'DEBIT_ACCOUNT', 'DEBIT_NAME',
        'CREDIT_CORE', 'CREDIT_ACCOUNT', 'CREDIT_NAME', 'AMOUNT', 'DESCRIPTION',
        'REF_NUMBER', 'r_code', 'r_message', 'r_coreReference', 'r_referenceNumber',
        'r_dateTime'
    ];

    protected $useTimestamps = false; // Karena tidak ada created_at/updated_at
    protected $dateFormat = 'datetime';

    /**
     * Update response data dari core banking setelah settlement berhasil
     * 
     * @param string $kdSettle
     * @param string $refNumber
     * @param array $responseData Response dari core banking
     * @return bool
     */
    public function updateSettlementResponse(string $kdSettle, string $refNumber, array $responseData): bool
    {
        try {
            $updateData = [
                'r_code' => $responseData['response_code'] ?? null,
                'r_message' => $responseData['message'] ?? null,
                'r_coreReference' => $responseData['core_ref'] ?? null,
                'r_referenceNumber' => $responseData['core_ref'] ?? null, // Bisa sama dengan core_ref
                'r_dateTime' => $responseData['timestamp'] ?? date('Y-m-d H:i:s')
            ];

            // Update berdasarkan KD_SETTLE dan REF_NUMBER
            $result = $this->where('KD_SETTLE', $kdSettle)
                          ->where('REF_NUMBER', $refNumber)
                          ->set($updateData)
                          ->update();

            if ($result) {
                log_message('info', 'Settlement response updated successfully', [
                    'kd_settle' => $kdSettle,
                    'ref_number' => $refNumber,
                    'update_data' => $updateData
                ]);
                return true;
            } else {
                log_message('warning', 'No settlement record found to update', [
                    'kd_settle' => $kdSettle,
                    'ref_number' => $refNumber
                ]);
                return false;
            }

        } catch (\Exception $e) {
            log_message('error', 'Failed to update settlement response', [
                'kd_settle' => $kdSettle,
                'ref_number' => $refNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Get settlement data berdasarkan KD_SETTLE dan REF_NUMBER
     * 
     * @param string $kdSettle
     * @param string $refNumber
     * @return array|null
     */
    public function getSettlementByRef(string $kdSettle, string $refNumber): ?array
    {
        return $this->where('KD_SETTLE', $kdSettle)
                   ->where('REF_NUMBER', $refNumber)
                   ->first();
    }

    /**
     * Get settlement data berdasarkan JENIS_SETTLE untuk CA-ESCR
     * 
     * @param string $kdSettle
     * @return array
     */
    public function getSettlementCA2Escrow(string $kdSettle): array
    {
        return $this->where('KD_SETTLE', $kdSettle)
                   ->where('JENIS_SETTLE', 'CA-ESCR')
                   ->findAll();
    }

    /**
     * Get settlement data berdasarkan JENIS_SETTLE untuk ESCR-BILR
     * 
     * @param string $kdSettle
     * @return array
     */
    public function getSettlementEscrow2Biller(string $kdSettle): array
    {
        return $this->where('KD_SETTLE', $kdSettle)
                   ->where('JENIS_SETTLE', 'ESCR-BILR')
                   ->findAll();
    }

    /**
     * Cek apakah settlement sudah diproses (sudah ada response dari core)
     * 
     * @param string $kdSettle
     * @param string $refNumber
     * @return bool
     */
    public function isSettlementProcessed(string $kdSettle, string $refNumber): bool
    {
        $settlement = $this->where('KD_SETTLE', $kdSettle)
                          ->where('REF_NUMBER', $refNumber)
                          ->where('r_code IS NOT NULL')
                          ->where('r_coreReference IS NOT NULL')
                          ->first();

        return !empty($settlement);
    }

    /**
     * Get settlement yang belum diproses (untuk batch processing)
     * 
     * @param string $jenisSettle CA-ESCR atau ESCR-BILR
     * @param int $limit
     * @return array
     */
    public function getPendingSettlements(string $jenisSettle, int $limit = 100): array
    {
        return $this->where('JENIS_SETTLE', $jenisSettle)
                   ->where('r_code IS NULL')
                   ->where('r_coreReference IS NULL')
                   ->limit($limit)
                   ->findAll();
    }

    /**
     * Get settlement summary untuk monitoring
     * 
     * @param string $tglData
     * @return array
     */
    public function getSettlementSummary(string $tglData): array
    {
        $sql = "SELECT 
                    JENIS_SETTLE,
                    COUNT(*) as total_records,
                    SUM(AMOUNT) as total_amount,
                    COUNT(CASE WHEN r_code IS NOT NULL THEN 1 END) as processed_count,
                    COUNT(CASE WHEN r_code IS NULL THEN 1 END) as pending_count,
                    COUNT(CASE WHEN r_code = '00' THEN 1 END) as success_count,
                    COUNT(CASE WHEN r_code != '00' AND r_code IS NOT NULL THEN 1 END) as failed_count
                FROM {$this->table}
                WHERE TGL_DATA = ?
                GROUP BY JENIS_SETTLE";

        $query = $this->db->query($sql, [$tglData]);
        return $query->getResultArray();
    }

    /**
     * Update settlement response untuk multiple records (batch update)
     * 
     * @param array $settlements Array of settlement data to update
     * @return array Result summary
     */
    public function batchUpdateSettlementResponse(array $settlements): array
    {
        $successCount = 0;
        $failedCount = 0;
        $errors = [];

        $this->db->transBegin();

        try {
            foreach ($settlements as $settlement) {
                $kdSettle = $settlement['KD_SETTLE'];
                $refNumber = $settlement['REF_NUMBER'];
                $responseData = $settlement['response_data'];

                if ($this->updateSettlementResponse($kdSettle, $refNumber, $responseData)) {
                    $successCount++;
                } else {
                    $failedCount++;
                    $errors[] = "Failed to update {$kdSettle} - {$refNumber}";
                }
            }

            if ($this->db->transStatus() === false) {
                $this->db->transRollback();
                throw new \Exception('Database transaction failed');
            }

            $this->db->transCommit();

            return [
                'success' => true,
                'total_processed' => count($settlements),
                'success_count' => $successCount,
                'failed_count' => $failedCount,
                'errors' => $errors
            ];

        } catch (\Exception $e) {
            $this->db->transRollback();
            
            log_message('error', 'Batch update settlement response failed', [
                'error' => $e->getMessage(),
                'settlements_count' => count($settlements)
            ]);

            return [
                'success' => false,
                'message' => 'Batch update failed: ' . $e->getMessage(),
                'total_processed' => 0,
                'success_count' => 0,
                'failed_count' => count($settlements),
                'errors' => [$e->getMessage()]
            ];
        }
    }

    /**
     * Get account mapping untuk settlement
     * Helper method untuk mendapatkan debit/credit account
     * 
     * @param string $kdSettle
     * @param string $jenisSettle
     * @return array
     */
    public function getAccountMapping(string $kdSettle, string $jenisSettle): array
    {
        $settlement = $this->where('KD_SETTLE', $kdSettle)
                          ->where('JENIS_SETTLE', $jenisSettle)
                          ->first();

        if (!$settlement) {
            return [
                'debit_account' => null,
                'credit_account' => null,
                'amount' => 0
            ];
        }

        return [
            'debit_account' => $settlement['DEBIT_ACCOUNT'],
            'credit_account' => $settlement['CREDIT_ACCOUNT'],
            'amount' => $settlement['AMOUNT'],
            'description' => $settlement['DESCRIPTION']
        ];
    }
}
