# 📋 Dokumentasi Feature "Proses Ulang" (Retry Process)

## 🎯 **Overview**

Feature "Proses Ulang" memungkinkan user untuk mengirim ulang transaksi yang gagal ke Akselgate tanpa menghapus history attempt sebelumnya. Sistem menggunakan **Versioning System** dengan `attempt_number` untuk tracking semua percobaan.

---

## 🏗️ **Arsitektur & Struktur Database**

### **Tabel: `t_akselgate_transaction_log`**

Perubahan struktur:

| Kolom | Type | Keterangan |
|-------|------|------------|
| `attempt_number` | INT | Nomor percobaan (1, 2, 3, dst) |
| `is_latest` | TINYINT | Flag record terbaru (1 = terbaru, 0 = lama) |

**REMOVED**: UNIQUE constraint `(kd_settle, transaction_type)`

**NEW INDEXES**:
- `idx_settle_type_latest` → `(kd_settle, transaction_type, is_latest)`
- `idx_attempt` → `(attempt_number)`

---

## 📊 **Aturan Bisnis (Business Rules)**

### **1. Aturan Insert Log**

```
IF is_success = 1 (SUKSES):
  - Hanya boleh ada 1 record dengan is_latest = 1
  - Tidak boleh proses ulang (button disabled)
  
IF is_success = 0 (GAGAL):
  - Boleh proses ulang berkali-kali
  - Setiap proses ulang = attempt baru
  - Previous attempts di-mark is_latest = 0
```

### **2. Pengecekan Duplicate Process**

```php
checkDuplicateProcess($kdSettle, $transactionType):
  - Cek apakah ada record dengan is_success = 1
  - Jika YES: Return exists = true (tidak boleh proses ulang)
  - Jika NO: Return exists = false (boleh proses)
```

### **3. Attempt Number Logic**

```php
getNextAttemptNumber($kdSettle, $transactionType):
  - Query MAX(attempt_number) untuk kd_settle + type
  - Return: last_attempt + 1
  - First attempt: Return 1
```

---

## 🔄 **Workflow Proses Ulang**

### **Scenario 1: First Attempt (Belum Pernah Diproses)**

```
1. User klik "Proses Semua"
2. Controller → Service → processBatchTransaction()
3. getNextAttemptNumber() → Return 1
4. Kirim ke Akselgate
5. Save log:
   - attempt_number = 1
   - is_latest = 1
   - is_success = 1/0 (tergantung response)
```

### **Scenario 2: Retry Attempt (Gagal, Proses Ulang)**

```
1. User klik "Proses Ulang (Attempt #2)"
2. Controller → Service → processBatchTransaction()
3. checkDuplicateProcess() → Return exists = false (karena is_success = 0)
4. markAsNotLatest() → Set previous attempts is_latest = 0
5. getNextAttemptNumber() → Return 2
6. Kirim ke Akselgate
7. Save log:
   - attempt_number = 2
   - is_latest = 1
   - is_success = 1/0
```

### **Scenario 3: Already Success (Tidak Boleh Proses Ulang)**

```
1. User coba proses lagi
2. Controller → Service → processBatchTransaction()
3. checkDuplicateProcess() → Return exists = true (karena is_success = 1)
4. Controller return error: "Sudah berhasil diproses"
5. UI: Button disabled "Sudah Diproses (Attempt #N)"
```

---

## 💻 **Implementasi Code**

### **1. Migration**

File: `app/Database/Migrations/2025-10-06-000001_AlterAkselgateTransactionLogAddVersioning.php`

```bash
# Run migration
php spark migrate

# Rollback migration
php spark migrate:rollback
```

### **2. Model Methods**

File: `app/Models/ApiGateway/AkselgateTransactionLog.php`

**New Methods:**
- `getLatestAttempt($kdSettle, $transactionType)` - Get record dengan `is_latest = 1`
- `getAllAttempts($kdSettle, $transactionType)` - Get semua attempts (history)
- `getNextAttemptNumber($kdSettle, $transactionType)` - Calculate attempt berikutnya
- `checkSuccessExists($kdSettle, $transactionType)` - Cek apakah ada success record
- `markAsNotLatest($kdSettle, $transactionType)` - Mark previous attempts as not latest

### **3. Service Updates**

File: `app/Services/ApiGateway/AkselgateService.php`

**Updated Methods:**

```php
// Check duplicate - hanya cek is_success = 1
checkDuplicateProcess($kdSettle, $transactionType)

// Get status untuk UI - return array dengan detail
isAlreadyProcessed($kdSettle, $transactionType)

// Process batch dengan versioning
processBatchTransaction($kdSettle, $transactions, $transactionType)
```

### **4. Controller Updates**

File: `app/Controllers/Settlement/JurnalCaEscrowController.php`

**Changes:**
- `datatable()`: Kirim `is_success` dan `attempt_number` ke frontend
- `proses()`: Handle retry logic dengan attempt tracking

### **5. UI Updates**

File: `public/js/settlement/jurnal_ca_escrow/datatable.js`

**Button Logic:**

```javascript
if (!isProcessed) {
    // Belum diproses - Button "Proses Semua" (biru)
} else if (isSuccess === 1) {
    // Sudah sukses - Button "Sudah Diproses" (abu-abu, disabled)
} else if (isSuccess === 0) {
    // Gagal - Button "Proses Ulang (Attempt #N)" (orange)
}
```

---

## 📈 **Contoh Data di Database**

### **Scenario: 3 Attempts (2 Gagal, 1 Sukses)**

```sql
SELECT id, kd_settle, attempt_number, is_latest, is_success, 
       status_code_res, response_message, sent_at
FROM t_akselgate_transaction_log
WHERE kd_settle = 'SETL001' AND transaction_type = 'CA_ESCROW'
ORDER BY attempt_number;
```

**Result:**

| id | kd_settle | attempt | is_latest | is_success | status_code | response_message | sent_at |
|----|-----------|---------|-----------|------------|-------------|------------------|---------|
| 1  | SETL001   | 1       | 0         | 0          | 500         | Connection timeout | 2025-01-01 10:00 |
| 2  | SETL001   | 2       | 0         | 0          | 401         | Invalid token | 2025-01-01 10:05 |
| 3  | SETL001   | 3       | 1         | 1          | 200         | SUCCESS | 2025-01-01 10:10 |

**Penjelasan:**
- Attempt #1: Gagal (timeout), `is_latest = 0` (sudah tidak latest)
- Attempt #2: Gagal (invalid token), `is_latest = 0` (sudah tidak latest)
- Attempt #3: **SUKSES**, `is_latest = 1` (ini yang terbaru dan sukses)

---

## 🎨 **UI Behavior**

### **Button States:**

| Status | Button Text | Color | Enabled? | Icon |
|--------|-------------|-------|----------|------|
| Belum diproses | "Proses Semua (N)" | Primary (Biru) | ✅ Yes | `fa-play` |
| Sudah sukses | "Sudah Diproses (Attempt #N)" | Secondary (Abu-abu) | ❌ No | `fa-check-circle` |
| Gagal | "Proses Ulang (Attempt #N)" | Warning (Orange) | ✅ Yes | `fa-redo` |

### **Error Message Display:**

Jika transaksi gagal, tampilkan **alert danger** di atas tabel detail:

```html
<div class="alert alert-danger">
  <i class="fal fa-exclamation-triangle"></i>
  Akselgate response: Connection timeout
</div>
```

---

## 🔍 **Query Examples**

### **Get Latest Attempt untuk Semua kd_settle**

```sql
SELECT kd_settle, attempt_number, is_success, status_code_res, 
       response_message, sent_at
FROM t_akselgate_transaction_log
WHERE transaction_type = 'CA_ESCROW' AND is_latest = 1
ORDER BY sent_at DESC;
```

### **Get Full History untuk 1 kd_settle**

```sql
SELECT attempt_number, is_success, status_code_res, 
       response_message, sent_by, sent_at
FROM t_akselgate_transaction_log
WHERE kd_settle = 'SETL001' 
  AND transaction_type = 'CA_ESCROW'
ORDER BY attempt_number ASC;
```

### **Get Statistics: Success Rate per kd_settle**

```sql
SELECT 
    kd_settle,
    MAX(attempt_number) as total_attempts,
    MAX(CASE WHEN is_success = 1 THEN 1 ELSE 0 END) as is_successful,
    SUM(CASE WHEN is_success = 1 THEN 1 ELSE 0 END) as success_count,
    COUNT(*) - SUM(CASE WHEN is_success = 1 THEN 1 ELSE 0 END) as failed_count
FROM t_akselgate_transaction_log
WHERE transaction_type = 'CA_ESCROW'
GROUP BY kd_settle;
```

---

## 🧪 **Testing Checklist**

### **Test Case 1: First Attempt - Success**
- [ ] Button tampil "Proses Semua"
- [ ] Klik button → transaksi terkirim
- [ ] Response sukses → Button jadi "Sudah Diproses (Attempt #1)"
- [ ] Database: `attempt_number = 1`, `is_latest = 1`, `is_success = 1`

### **Test Case 2: First Attempt - Failed, Then Retry**
- [ ] Button tampil "Proses Semua"
- [ ] Klik button → transaksi gagal
- [ ] Button jadi "Proses Ulang (Attempt #2)" (orange)
- [ ] Database: `attempt_number = 1`, `is_latest = 1`, `is_success = 0`
- [ ] Klik "Proses Ulang" → kirim attempt #2
- [ ] Jika sukses: Button jadi "Sudah Diproses (Attempt #2)"
- [ ] Database:
  - Attempt #1: `is_latest = 0`
  - Attempt #2: `is_latest = 1`, `is_success = 1`

### **Test Case 3: Multiple Retries Until Success**
- [ ] Attempt #1: Gagal → Button "Proses Ulang (Attempt #2)"
- [ ] Attempt #2: Gagal → Button "Proses Ulang (Attempt #3)"
- [ ] Attempt #3: Sukses → Button "Sudah Diproses (Attempt #3)" (disabled)
- [ ] Database: 3 records, hanya attempt #3 yang `is_latest = 1`

### **Test Case 4: Prevent Duplicate Success**
- [ ] Transaksi sudah sukses (attempt #N)
- [ ] Button tampil "Sudah Diproses (Attempt #N)" (disabled)
- [ ] User coba proses lagi via direct API call
- [ ] Response: "Sudah berhasil diproses pada attempt #N"

### **Test Case 5: Error Message Display**
- [ ] Transaksi gagal dengan response message
- [ ] Alert danger tampil di atas tabel detail
- [ ] Error message sesuai dari Akselgate

---

## 📝 **Changelog**

### **Version 1.0.0 (2025-10-06)**

**Added:**
- Versioning system dengan `attempt_number` dan `is_latest`
- Feature "Proses Ulang" untuk transaksi gagal
- Button conditional logic berdasarkan status
- Full audit trail untuk semua attempts

**Changed:**
- Removed UNIQUE constraint `(kd_settle, transaction_type)`
- Updated `checkDuplicateProcess()` untuk cek `is_success = 1`
- Updated `isAlreadyProcessed()` return array dengan detail
- UI button logic: Biru (proses), Orange (retry), Abu-abu (success)

**Removed:**
- ❌ `previous_attempt_id` (tidak dipakai)
- ❌ `retry_reason` (tidak dipakai)

---

## ⚠️ **Important Notes**

1. **Data Migration**: Existing records akan di-set `attempt_number = 1` dan `is_latest = 1`
2. **Performance**: Index `idx_settle_type_latest` untuk query cepat
3. **Audit Trail**: Semua attempts tersimpan permanent (tidak ada delete)
4. **UI State**: Frontend menggunakan `processedStatusMap` untuk tracking button state
5. **Rollback**: Migration bisa di-rollback, tapi data akan hilang

---

## 🚀 **Future Enhancements**

Potential improvements:

- [ ] Add `retry_reason` textarea untuk user input alasan retry
- [ ] Dashboard statistik: Total attempts, Success rate, Failed reasons
- [ ] Email notification untuk admin jika attempt gagal > 3x
- [ ] Auto-retry dengan exponential backoff (optional)
- [ ] Archive old attempts (> 6 bulan) ke separate table

---

## 👨‍💻 **Developer Contact**

Jika ada pertanyaan atau issue terkait feature ini, silakan hubungi tim development.

**Created**: 2025-10-06  
**Last Updated**: 2025-10-06  
**Version**: 1.0.0
