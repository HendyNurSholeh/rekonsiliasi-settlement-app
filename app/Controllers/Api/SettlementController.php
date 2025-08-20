<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

/**
 * Mock API Transfer Dana untuk Settlement CA to Escrow
 * 
 * PENTING: Ini HANYA simulasi untuk API transfer dana ke core banking.
 * Semua proses lain (validasi, database, logging, security) adalah REAL/Production.
 * 
 * Simulasi ini menggantikan koneksi ke core banking untuk transfer dana,
 * sehingga tidak ada uang sungguhan yang berpindah selama testing.
 */
class SettlementController extends ResourceController
{
    use ResponseTrait;

    /**
     * SIMULASI Transfer Dana CA to Escrow ke Core Banking
     * POST /api/settlement/ca-escrow/process
     * 
     * Endpoint sederhana yang hanya mengembalikan response sukses
     */
    public function process()
    {
        try {
            // === VALIDASI PAYLOAD ===
            $payload = $this->request->getJSON(true);
            if (empty($payload)) {
                return $this->fail([
                    'success' => false,
                    'message' => 'Empty payload',
                    'timestamp' => date('Y-m-d H:i:s')
                ], 400);
            }

            // === SIMULASI SUKSES ===
            return $this->respond([
                'success' => true,
                'message' => 'Fund transfer completed successfully',
                'core_ref' => $this->generateCoreReference(),
                'response_code' => '00',
                'timestamp' => date('Y-m-d H:i:s'),
                'debit_account' => $payload['debit_account'] ?? '',
                'credit_account' => $payload['credit_account'] ?? '',
                'amount' => $payload['amount'] ?? 0
            ], 200);

        } catch (\Exception $e) {
            return $this->fail([
                'success' => false,
                'message' => 'System error',
                'timestamp' => date('Y-m-d H:i:s')
            ], 500);
        }
    }

    /**kln
     * Generate core reference number yang simple
     */
    private function generateCoreReference(): string
    {
        $prefix = 'CR'; // Core Reference
        $date = date('Ymd');
        $time = date('His');
        $random = str_pad(rand(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        return $prefix . $date . $time . $random;
    }
}
