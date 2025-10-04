<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Models\Settlement\SettlementMessageModel;

/**
 * AKSEL Gateway Callback Controller
 * 
 * Controller khusus untuk handle callback dari AKSEL Gateway
 * Terpisah dari JurnalCaEscrowController untuk menjaga kode lebih clean dan modular
 */
class AkselGateCallbackController extends BaseController
{
    protected $settlementMessageModel;

    public function __construct()
    {
        $this->settlementMessageModel = new SettlementMessageModel();
    }

    /**
     * Callback endpoint untuk menerima response dari API Gateway
     * Endpoint ini di-exempt dari authentication dan CSRF (lihat Config\Filters.php)
     * 
     * GET Parameters:
     * - ref: Reference Number transaksi
     * - rescore: Response code dari core banking ('00' = success, lainnya = failed)
     * - rescoreref: Core Reference Number
     */
    public function index()
    {
        try {
            // Ambil parameter dari API Gateway
            $ref = $this->request->getGet('ref') ?? null;
            $rescore = $this->request->getGet('rescore') ?? null;
            $rescoreref = $this->request->getGet('rescoreref') ?? null;
            
            log_message('info', 'Callback received from API Gateway', [
                'ref' => $ref,
                'rescore' => $rescore,
                'rescoreref' => $rescoreref,
                'ip' => $this->request->getIPAddress(),
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            
            // Validasi parameter required
            if (empty($ref)) {
                log_message('warning', 'Callback received without ref parameter');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter ref is required'
                ]);
            }
            
            // Simpan callback response ke t_settle_message
            $result = $this->saveCallback($ref, $rescore, $rescoreref);
            
            if ($result) {
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Callback processed successfully',
                    'ref' => $ref,
                    'timestamp' => date('Y-m-d H:i:s')
                ]);
            } else {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Record not found or update failed',
                    'ref' => $ref
                ]);
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Callback error: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Callback processing failed: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Simpan callback response ke t_settle_message
     * Hanya UPDATE record yang sudah ada, tidak ada INSERT
     * 
     * @param string $ref - Reference Number (REF_NUMBER)
     * @param string $rescore - Response code ('00' = success, lainnya = failed)
     * @param string $rescoreref - Core Reference Number
     * @return bool
     */
    private function saveCallback($ref, $rescore, $rescoreref): bool
    {
        try {
            // Cek apakah record dengan REF_NUMBER sudah ada
            $existing = $this->settlementMessageModel->where('REF_NUMBER', $ref)->first();
            
            if (!$existing) {
                log_message('warning', 'Callback received but REF_NUMBER not found in t_settle_message', [
                    'ref' => $ref,
                    'rescore' => $rescore,
                    'rescoreref' => $rescoreref
                ]);
                return false;
            }
            
            // Tentukan message berdasarkan rescore
            $message = 'FAILED';
            if ($rescore === '00') {
                $message = 'SUCCESS';
            }
            
            // Update existing record dengan callback data
            $updated = $this->settlementMessageModel->update($existing['ID'], [
                'r_code' => $rescore,
                'r_coreReference' => $rescoreref,
                'r_referenceNumber' => $ref,
                'r_message' => $message,
                'r_dateTime' => date('Y-m-d H:i:s'),
            ]);
            
            if ($updated) {
                log_message('info', 'Callback updated in t_settle_message', [
                    'id' => $existing['ID'],
                    'ref' => $ref,
                    'rescore' => $rescore,
                    'rescoreref' => $rescoreref,
                    'message' => $message,
                    'callback_time' => date('Y-m-d H:i:s')
                ]);
                return true;
            } else {
                log_message('error', 'Failed to update callback in t_settle_message', [
                    'ref' => $ref,
                    'id' => $existing['ID']
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error saving callback to t_settle_message: ' . $e->getMessage(), [
                'ref' => $ref,
                'rescore' => $rescore,
                'rescoreref' => $rescoreref
            ]);
            throw $e;
        }
    }
}
