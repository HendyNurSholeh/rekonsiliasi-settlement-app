# AKSEL Gateway Integration Guide

Panduan untuk mengintegrasikan controller baru dengan AKSEL Gateway Service.

## 📋 Overview

AKSEL Gateway Service adalah service terpusat untuk menangani komunikasi dengan API Gateway AKSEL. Service ini sudah dilengkapi dengan:
- ✅ Duplicate check
- ✅ Transaction validation
- ✅ API communication
- ✅ Unified logging system
- ✅ Error handling

## 🏗️ Architecture

```
Controller (HTTP Layer)
    ↓
AkselGatewayService (Business Logic)
    ↓
AkselgateTransactionLog Model (Database)
```

## 🔧 How to Integrate (Step by Step)

### 1. Setup Controller

```php
<?php

namespace App\Controllers\Settlement;

use App\Controllers\BaseController;
use App\Models\ProsesModel;
use App\Models\ApiGateway\AkselgateTransactionLog;
use App\Services\ApiGateway\AkselGatewayService;

class YourNewController extends BaseController
{
    protected $prosesModel;
    protected $akselGatewayService;

    public function __construct()
    {
        $this->prosesModel = new ProsesModel();
        $this->akselGatewayService = new AkselGatewayService();
    }
}
```

### 2. Check if Already Processed (for UI)

Gunakan method `isAlreadyProcessed()` untuk disable button atau show status:

```php
public function datatable()
{
    // ... get data from database ...
    
    foreach ($data as $row) {
        // Check if already processed
        $isProcessed = $this->akselGatewayService->isAlreadyProcessed(
            $row['kd_settle'], 
            AkselgateTransactionLog::TYPE_YOUR_TRANSACTION  // Ganti dengan type Anda
        );
        
        $row['is_processed'] = $isProcessed; // Flag untuk disable button
    }
    
    return $this->response->setJSON($data);
}
```

### 3. Process Transaction

Method `proses()` untuk handle form submission:

```php
public function proses()
{
    try {
        // Step 1: Validasi CSRF
        if (!$this->validate(['csrf_test_name' => 'required'])) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Token CSRF tidak valid'
            ])->setStatusCode(403);
        }

        // Step 2: Ambil parameter
        $kdSettle = $this->request->getPost('kd_settle');
        $tanggalData = $this->request->getPost('tanggal') ?? date('Y-m-d');

        // Step 3: Check duplicate (PENTING!)
        $duplicateCheck = $this->akselGatewayService->checkDuplicateProcess(
            $kdSettle, 
            AkselgateTransactionLog::TYPE_YOUR_TRANSACTION  // Ganti dengan type Anda
        );
        
        if ($duplicateCheck['exists']) {
            log_message('warning', "Duplicate process attempt for kd_settle: {$kdSettle}");
            
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Kode settle ' . $kdSettle . ' sudah pernah diproses sebelumnya',
                'error_code' => 'DUPLICATE_PROCESS',
                'previous_data' => [
                    'status_code_res' => $duplicateCheck['status_code_res'],
                    'is_success' => $duplicateCheck['is_success'],
                    'request_id' => $duplicateCheck['request_id'],
                    'sent_by' => $duplicateCheck['sent_by'],
                    'sent_at' => $duplicateCheck['sent_at']
                ],
                'csrf_token' => csrf_hash()
            ]);
        }

        // Step 4: Ambil data transaksi dari database
        $transaksiData = $this->getTransaksiByKdSettle($kdSettle, $tanggalData);

        // Step 5: Process via service (ALL IN ONE!)
        $result = $this->akselGatewayService->processBatchTransaction(
            $kdSettle, 
            $transaksiData, 
            AkselgateTransactionLog::TYPE_YOUR_TRANSACTION  // Ganti dengan type Anda
        );
        
        // Step 6: Return result
        if (!$result['success']) {
            log_message('error', 'Batch transaction failed for kd_settle: ' . $kdSettle);
            return $this->response->setJSON(array_merge($result, ['csrf_token' => csrf_hash()]));
        }
        
        log_message('info', 'Batch transaction successful for kd_settle: ' . $kdSettle);
        return $this->response->setJSON(array_merge($result, ['csrf_token' => csrf_hash()]));
        
    } catch (\Exception $e) {
        log_message('error', 'Error: ' . $e->getMessage());
        return $this->response->setJSON([
            'success' => false,
            'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage(),
            'csrf_token' => csrf_hash()
        ])->setStatusCode(500);
    }
}
```

### 4. Get Transaction Data

Implement method untuk ambil data transaksi dari database:

```php
private function getTransaksiByKdSettle($kdSettle, $tanggalData)
{
    try {
        $db = \Config\Database::connect();
        
        // Sesuaikan dengan stored procedure atau query Anda
        $query = "CALL your_stored_procedure(?, ?)";
        $result = $db->query($query, [$tanggalData, $kdSettle]);
        
        if (!$result) {
            throw new \Exception('Failed to get transaction data');
        }
        
        $data = $result->getResultArray();
        
        // Filter by kd_settle
        $filtered = array_filter($data, function($row) use ($kdSettle) {
            return isset($row['d_KD_SETTLE']) && $row['d_KD_SETTLE'] === $kdSettle;
        });
        
        return array_values($filtered);
        
    } catch (\Exception $e) {
        log_message('error', 'Error getting transaction data: ' . $e->getMessage());
        return [];
    }
}
```

## 📝 Transaction Types

Tambahkan constant baru di `AkselgateTransactionLog` model jika perlu:

```php
// Existing
const TYPE_CA_ESCROW = 'CA_ESCROW';
const TYPE_ESCROW_BILLER_PL = 'ESCROW_BILLER_PL';

// Add new type if needed
const TYPE_YOUR_NEW_TYPE = 'YOUR_NEW_TYPE';
```

Dan update migration jika perlu menambah type baru di ENUM.

## 🎯 Transaction Data Format

Data transaksi yang dikirim ke service harus dalam format array dengan fields:

```php
[
    [
        'd_DEBIT_ACCOUNT' => '1234567890',      // Required
        'd_CREDIT_ACCOUNT' => '0987654321',     // Required
        'd_NO_REF' => 'REF001',                 // Required
        'd_AMOUNT' => '100000',                 // Required, numeric
        'd_DEBIT_NAME' => 'Account Name',       // Optional
        // ... field lainnya ...
    ],
    // ... transaksi lainnya ...
]
```

Service akan otomatis:
- ✅ Validate semua required fields
- ✅ Format ke API Gateway format
- ✅ Send ke API Gateway
- ✅ Save log dengan request & response
- ✅ Return result

## ⚠️ Important Notes

### DO's ✅
1. **Always check duplicate** sebelum process
2. **Use service methods** - jangan duplicate logic
3. **Pass correct transaction_type** ke service
4. **Handle result properly** - check `$result['success']`
5. **Log important events** untuk debugging

### DON'Ts ❌
1. **Jangan hit API Gateway langsung** - use service
2. **Jangan save log manual** - service sudah handle
3. **Jangan duplicate checkDuplicateProcess()** - use service method
4. **Jangan hardcode transaction_type** - use constant
5. **Jangan skip duplicate check** - penting untuk prevent duplicate

## 🔍 Service Methods Available

### `checkDuplicateProcess(string $kdSettle, string $transactionType): array`
Cek apakah kd_settle sudah pernah diproses.

**Returns:**
```php
[
    'exists' => true/false,
    'status_code_res' => '201',
    'response_code' => '201',
    'is_success' => 1,
    'request_id' => 'SETL_XXX_20251005',
    'sent_by' => 'username',
    'sent_at' => '2025-10-05 19:18:46'
]
```

### `isAlreadyProcessed(string $kdSettle, string $transactionType): bool`
Simple check untuk disable button di UI.

**Returns:** `true` jika sudah pernah diproses dengan sukses, `false` jika belum.

### `processBatchTransaction(string $kdSettle, array $transactions, string $transactionType): array`
Main method untuk process transaksi (format, validate, send, log - ALL IN ONE).

**Returns:**
```php
// Success
[
    'success' => true,
    'message' => 'Transaksi berhasil dikirim ke API Gateway',
    'request_id' => 'SETL_XXX_20251005191846',
    'total_transaksi' => 10,
    'status_code' => '201',
    'api_response' => [...]
]

// Failed
[
    'success' => false,
    'message' => 'Error message',
    'error_code' => 'ERROR_CODE',
    'errors' => [...],  // Validation errors jika ada
]
```

## 📊 Example: JurnalCaEscrowController

Lihat `app/Controllers/Settlement/JurnalCaEscrowController.php` sebagai reference implementation yang lengkap.

## 🔒 Database Schema

Log disimpan di `t_akselgate_transaction_log` dengan struktur:
- Unique constraint: `(kd_settle, transaction_type)`
- Request & response payload disimpan sebagai JSON
- Support multiple transaction types

## 🚀 Future Controllers

Untuk menambah controller baru (misal: `JurnalEscrowBillerPlController`):
1. Copy pattern dari `JurnalCaEscrowController`
2. Ganti transaction type ke `AkselgateTransactionLog::TYPE_ESCROW_BILLER_PL`
3. Sesuaikan query untuk ambil data transaksi
4. Done! Service sudah handle sisanya.

## 📞 Support

Jika ada pertanyaan, lihat:
- Service code: `app/Services/ApiGateway/AkselGatewayService.php`
- Model code: `app/Models/ApiGateway/AkselgateTransactionLog.php`
- Migration: `app/Database/Migrations/*_CreateAkselgateTransactionLogTable.php`
- Example controller: `app/Controllers/Settlement/JurnalCaEscrowController.php`
