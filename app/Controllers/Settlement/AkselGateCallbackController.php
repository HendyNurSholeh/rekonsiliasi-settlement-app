<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Models\Settlement\SettlementMessageModel;
use App\Models\ApiGateway\AkselgateFwdCallbackLog;

/**
 * AKSEL Gateway Callback Controller
 * 
 * Controller khusus untuk handle callback dari AKSEL FWD (Forward) Gateway
 * 
 * Flow Callback:
 * 1. Aplikasi kirim batch transaksi ke Aksel Gateway
 * 2. Aksel FWD proses transaksi satu-per-satu dengan delay
 * 3. Setiap transaksi selesai diproses, Aksel FWD kirim callback ke endpoint ini
 * 4. Callback disimpan ke t_akselgatefwd_callback_log (audit trail)
 * 5. Data callback di-update ke t_settle_message (business data)
 * 
 * Terpisah dari JurnalCaEscrowController untuk menjaga kode lebih clean dan modular
 */
class AkselGateCallbackController extends BaseController
{
    protected $settlementMessageModel;
    protected $callbackLogModel;

    public function __construct()
    {
        $this->settlementMessageModel = new SettlementMessageModel();
        $this->callbackLogModel = new AkselgateFwdCallbackLog();
    }

    /**
     * Callback endpoint untuk menerima response dari Aksel FWD Gateway
     * Endpoint ini di-exempt dari authentication dan CSRF (lihat Config\Filters.php)
     * 
     * GET Parameters:
     * - ref: Reference Number transaksi (REF_NUMBER dari t_settle_message)
     * - rescore: Response code dari core banking ('00' = success, lainnya = failed)
     * - rescoreref: Core Reference Number (nomor referensi dari core banking)
     * 
     * Flow:
     * 1. Terima callback dari Aksel FWD
     * 2. Simpan ke t_akselgatefwd_callback_log (audit trail)
     * 3. Update t_settle_message dengan hasil callback (business data)
     */
    public function index()
    {
        try {
            // Ambil parameter dari API Gateway
            $ref = $this->request->getGet('ref') ?? null;
            $rescore = $this->request->getGet('rescore') ?? null;
            $rescoreref = $this->request->getGet('rescoreref') ?? null;
            
            $ipAddress = $this->request->getIPAddress();
            $timestamp = date('Y-m-d H:i:s');
            
            log_message('info', 'Callback received from Aksel FWD Gateway', [
                'ref' => $ref,
                'rescore' => $rescore,
                'rescoreref' => $rescoreref,
                'ip' => $ipAddress,
                'timestamp' => $timestamp
            ]);
            
            // Validasi parameter required
            if (empty($ref)) {
                log_message('warning', 'Callback received without ref parameter');
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Parameter ref is required'
                ]);
            }
            
            // Step 1: Simpan callback ke log table (audit trail)
            $callbackLogId = $this->saveCallbackLog($ref, $rescore, $rescoreref, $ipAddress);
            
            if (!$callbackLogId) {
                log_message('error', 'Failed to save callback log', ['ref' => $ref]);
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Failed to save callback log'
                ]);
            }
            
            // Step 2: Update t_settle_message dengan callback data
            $result = $this->updateSettlementMessage($ref, $rescore, $rescoreref);
            
            // Step 3: Mark callback log as processed
            if ($result['success']) {
                $this->callbackLogModel->markAsProcessed($callbackLogId);
                
                return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Callback processed successfully',
                    'ref' => $ref,
                    'status' => $result['status'],
                    'timestamp' => $timestamp
                ]);
            } else {
                // Log error (tidak perlu simpan ke database, sudah ada di application log)
                log_message('error', 'Failed to update t_settle_message from callback', [
                    'ref' => $ref,
                    'reason' => $result['message'],
                    'callback_log_id' => $callbackLogId
                ]);
                
                return $this->response->setJSON([
                    'success' => false,
                    'message' => $result['message'],
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
     * Simpan callback ke t_akselgatefwd_callback_log (audit trail)
     * 
     * @param string $ref - Reference Number (REF_NUMBER)
     * @param string $rescore - Response code ('00' = success, lainnya = failed)
     * @param string $rescoreref - Core Reference Number
     * @param string $ipAddress - IP address pengirim
     * @return int|false - ID dari record yang disimpan, atau false jika gagal
     */
    private function saveCallbackLog($ref, $rescore, $rescoreref, $ipAddress)
    {
        try {
            // Get kd_settle from t_settle_message untuk kemudahan query
            $settlement = $this->settlementMessageModel->where('REF_NUMBER', $ref)->first();
            $kdSettle = $settlement ? $settlement['KD_SETTLE'] : null;
            
            // Tentukan status berdasarkan rescore
            $status = ($rescore === '00') ? 'SUCCESS' : 'FAILED';
            
            // Prepare callback data sebagai JSON untuk audit
            $callbackData = json_encode([
                'ref' => $ref,
                'rescore' => $rescore,
                'rescoreref' => $rescoreref,
                'received_at' => date('Y-m-d H:i:s'),
                'ip_address' => $ipAddress
            ]);
            
            // Insert ke callback log
            $data = [
                'ref_number' => $ref,
                'kd_settle' => $kdSettle,
                'res_code' => $rescore,
                'res_coreref' => $rescoreref,
                'status' => $status,
                'callback_data' => $callbackData,
                'ip_address' => $ipAddress,
                'is_processed' => 0, // Belum diproses
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            $inserted = $this->callbackLogModel->insert($data);
            
            if ($inserted) {
                log_message('info', 'Callback saved to t_akselgatefwd_callback_log', [
                    'id' => $inserted,
                    'ref' => $ref,
                    'status' => $status,
                    'kd_settle' => $kdSettle
                ]);
                return $inserted;
            } else {
                log_message('error', 'Failed to insert callback log', [
                    'ref' => $ref,
                    'errors' => $this->callbackLogModel->errors()
                ]);
                return false;
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error saving callback log: ' . $e->getMessage(), [
                'ref' => $ref,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return false;
        }
    }

    /**
     * Update t_settle_message dengan callback data
     * Hanya UPDATE record yang sudah ada, tidak ada INSERT
     * 
     * @param string $ref - Reference Number (REF_NUMBER)
     * @param string $rescore - Response code ('00' = success, lainnya = failed)
     * @param string $rescoreref - Core Reference Number
     * @return array - ['success' => bool, 'message' => string, 'status' => string]
     */
    private function updateSettlementMessage($ref, $rescore, $rescoreref): array
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
                return [
                    'success' => false,
                    'message' => 'REF_NUMBER not found in t_settle_message',
                    'status' => 'NOT_FOUND'
                ];
            }
            
            // Tentukan message berdasarkan rescore
            $message = ($rescore === '00') ? 'SUCCESS' : 'FAILED';
            
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
                    'kd_settle' => $existing['KD_SETTLE'],
                    'rescore' => $rescore,
                    'rescoreref' => $rescoreref,
                    'message' => $message,
                    'callback_time' => date('Y-m-d H:i:s')
                ]);
                return [
                    'success' => true,
                    'message' => 'Settlement message updated successfully',
                    'status' => $message
                ];
            } else {
                log_message('error', 'Failed to update callback in t_settle_message', [
                    'ref' => $ref,
                    'id' => $existing['ID']
                ]);
                return [
                    'success' => false,
                    'message' => 'Failed to update t_settle_message',
                    'status' => 'UPDATE_FAILED'
                ];
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Error updating t_settle_message: ' . $e->getMessage(), [
                'ref' => $ref,
                'rescore' => $rescore,
                'rescoreref' => $rescoreref,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return [
                'success' => false,
                'message' => 'Exception: ' . $e->getMessage(),
                'status' => 'EXCEPTION'
            ];
        }
    }
}
