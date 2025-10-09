# 📊 AKSELGATE FWD CALLBACK DATABASE DESIGN

**Created**: October 9, 2025  
**Author**: System Architect  
**Purpose**: Database structure untuk callback Aksel FWD (Forward) Gateway

---

## 🎯 PROBLEM STATEMENT

### **Situasi:**
Aksel FWD Gateway mengirim callback **per transaksi individual** dengan delay beberapa detik, bukan batch. Ini berbeda dengan request awal yang dikirim dalam bentuk batch.

### **Challenge:**
- Callback datang satu-per-satu (real-time/delayed)
- Perlu tracking audit trail untuk setiap callback
- Update ke `t_settle_message` harus trackable
- Butuh separation of concerns antara log dan business data

---

## 📋 STRUKTUR TABEL

### **Tabel yang Terlibat:**

#### **1. `t_akselgate_transaction_log`** (Existing)
**Purpose**: Log request/response ke Aksel Gateway API (batch level)

```sql
- id
- transaction_type (CA_ESCROW | ESCROW_BILLER_PL)
- kd_settle
- request_id
- total_transaksi (jumlah transaksi dalam batch)
- request_payload
- response_payload
- status_code_res
- is_success
- sent_at
- created_at
```

**Contoh**: 1 batch dengan 100 transaksi = **1 record**

---

#### **2. `t_settle_message`** (Existing)
**Purpose**: Data transaksi jurnal individual (business data)

```sql
- ID
- KD_SETTLE
- REF_NUMBER (unique per transaksi)
- DEBIT_ACCOUNT
- CREDIT_ACCOUNT
- AMOUNT
- r_code (callback: response code)
- r_message (callback: SUCCESS/FAILED)
- r_coreReference (callback: core ref number)
- r_referenceNumber (callback: ref number)
- r_dateTime (callback: waktu update)
```

**Contoh**: 1 batch dengan 100 transaksi = **100 records**

---

#### **3. `t_akselgatefwd_callback_log`** (NEW - RECOMMENDED)
**Purpose**: Log callback dari Aksel FWD Gateway (audit trail)

```sql
CREATE TABLE t_akselgatefwd_callback_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ref_number VARCHAR(15) NOT NULL COMMENT 'REF_NUMBER dari t_settle_message',
    kd_settle VARCHAR(15) NULL COMMENT 'KD_SETTLE untuk kemudahan query',
    res_code VARCHAR(5) NULL COMMENT 'Response code (00=success)',
    res_coreref VARCHAR(15) NULL COMMENT 'Core Reference Number',
    status ENUM('SUCCESS','FAILED') NOT NULL COMMENT 'SUCCESS jika res_code=00, FAILED jika lainnya',
    callback_data TEXT NULL COMMENT 'Raw callback JSON untuk audit',
    ip_address VARCHAR(45) NULL COMMENT 'IP pengirim callback',
    is_processed TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0=Belum, 1=Sudah di-update ke t_settle_message',
    processed_at DATETIME NULL COMMENT 'Waktu diproses',
    created_at DATETIME NULL COMMENT 'Waktu callback diterima',
    updated_at DATETIME NULL,
    
    INDEX idx_ref_number (ref_number),
    INDEX idx_kd_settle (kd_settle),
    INDEX idx_status (status),
    INDEX idx_is_processed (is_processed),
    INDEX idx_created_at (created_at),
    INDEX idx_settle_status (kd_settle, status),
    INDEX idx_ref_processed (ref_number, is_processed)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Contoh**: 1 batch dengan 100 transaksi = **100 callback records** (datang bertahap dengan delay)

---

## 🔄 FLOW CALLBACK PROCESS

```
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 1: KIRIM BATCH KE AKSEL GATEWAY                                │
├─────────────────────────────────────────────────────────────────────┤
│ Aplikasi → Aksel Gateway API                                        │
│ Request: {kd_settle: "ABC123", transaksi: [100 records]}            │
│ Log: t_akselgate_transaction_log (1 record)                         │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 2: AKSEL FWD PROSES SATU-PER-SATU                              │
├─────────────────────────────────────────────────────────────────────┤
│ Aksel FWD → Core Banking (delay beberapa detik per transaksi)      │
│ Transaksi 1 → Core Banking → Selesai (delay 2-5 detik)             │
│ Transaksi 2 → Core Banking → Selesai (delay 2-5 detik)             │
│ Transaksi 3 → Core Banking → Selesai (delay 2-5 detik)             │
│ ... (100 transaksi)                                                 │
└─────────────────────────────────────────────────────────────────────┘
                              ↓
┌─────────────────────────────────────────────────────────────────────┐
│ STEP 3: CALLBACK SATU-PER-SATU                                      │
├─────────────────────────────────────────────────────────────────────┤
│ Aksel FWD → Aplikasi (callback per transaksi)                       │
│                                                                      │
│ Callback 1:                                                          │
│ GET /callback?ref=REF001&rescore=00&rescoreref=CORE001             │
│   ↓                                                                  │
│   1. Save to t_akselgatefwd_callback_log (audit)                    │
│   2. Update t_settle_message (business data)                        │
│   3. Mark callback as processed                                     │
│                                                                      │
│ Callback 2:                                                          │
│ GET /callback?ref=REF002&rescore=00&rescoreref=CORE002             │
│   ↓ (same process)                                                   │
│                                                                      │
│ ... (100 callbacks dengan delay)                                    │
└─────────────────────────────────────────────────────────────────────┘
```

---

## ✅ KEUNTUNGAN TABEL TERPISAH

### **1. Separation of Concerns**
```
t_akselgate_transaction_log  → API request/response log (batch level)
t_settle_message             → Business data (transaksi jurnal)
t_akselgatefwd_callback_log  → Callback audit trail (per transaksi)
```

### **2. Complete Audit Trail**
- Setiap callback tercatat dengan timestamp
- Bisa track berapa kali callback diterima untuk 1 REF_NUMBER
- Bisa lihat callback yang belum diproses (`is_processed = 0`)
- Raw callback data tersimpan untuk debugging

### **3. Data Integrity**
- `t_settle_message` tetap clean untuk business logic
- Callback log tidak mengganggu existing query
- Mudah rollback jika ada masalah

### **4. Performance**
- Query callback log tidak mengganggu query transaksi
- Index terpisah, lebih optimal
- Bisa di-archive/cleanup lebih mudah

### **5. Monitoring & Debugging**
```sql
-- Cek callback yang belum diproses
SELECT * FROM t_akselgatefwd_callback_log 
WHERE is_processed = 0 
ORDER BY created_at DESC;

-- Cek callback by kd_settle
SELECT * FROM t_akselgatefwd_callback_log 
WHERE kd_settle = 'ABC123' 
ORDER BY created_at DESC;

-- Statistik callback per hari
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed
FROM t_akselgatefwd_callback_log
WHERE DATE(created_at) = CURDATE()
GROUP BY DATE(created_at);
```

---

## 🔧 IMPLEMENTASI

### **1. Migration**
File: `app/Database/Migrations/2025-10-09-000000_CreateAkselgateFwdCallbackLogTable.php`

```bash
php spark migrate
```

### **2. Model**
File: `app/Models/ApiGateway/AkselgateFwdCallbackLog.php`

**Methods:**
- `getUnprocessed()` - Get callback yang belum diproses
- `getByRefNumber($ref)` - Get callback by REF_NUMBER
- `getByKdSettle($kdSettle)` - Get callback by KD_SETTLE
- `getByStatus($status)` - Get callback by status
- `markAsProcessed($id)` - Mark sebagai sudah diproses
- `getStatistics($start, $end)` - Get statistik callback

### **3. Controller Update**
File: `app/Controllers/Settlement/AkselGateCallbackController.php`

**Flow:**
1. Terima callback dari Aksel FWD
2. Save ke `t_akselgatefwd_callback_log` (audit)
3. Update `t_settle_message` (business data)
4. Mark callback as processed
5. Return success/error response

---

## 📊 QUERY EXAMPLES

### **Get Callback Summary by KD_SETTLE**
```sql
SELECT 
    cl.kd_settle,
    COUNT(*) as total_callbacks,
    SUM(CASE WHEN cl.status = 'SUCCESS' THEN 1 ELSE 0 END) as success_count,
    SUM(CASE WHEN cl.status = 'FAILED' THEN 1 ELSE 0 END) as failed_count,
    SUM(CASE WHEN cl.is_processed = 0 THEN 1 ELSE 0 END) as unprocessed_count,
    MIN(cl.created_at) as first_callback,
    MAX(cl.created_at) as last_callback
FROM t_akselgatefwd_callback_log cl
WHERE cl.kd_settle = 'ABC123'
GROUP BY cl.kd_settle;
```

### **Get Unprocessed Callbacks**
```sql
SELECT * FROM t_akselgatefwd_callback_log
WHERE is_processed = 0
ORDER BY created_at ASC
LIMIT 100;
```

### **Join with Settlement Message**
```sql
SELECT 
    cl.*,
    sm.KD_SETTLE,
    sm.DEBIT_ACCOUNT,
    sm.CREDIT_ACCOUNT,
    sm.AMOUNT,
    sm.r_code,
    sm.r_message
FROM t_akselgatefwd_callback_log cl
LEFT JOIN t_settle_message sm ON cl.ref_number = sm.REF_NUMBER
WHERE cl.kd_settle = 'ABC123'
ORDER BY cl.created_at DESC;
```

### **Callback Success Rate by Day**
```sql
SELECT 
    DATE(created_at) as date,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed,
    ROUND(SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as success_rate
FROM t_akselgatefwd_callback_log
WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

---

## 🛡️ SECURITY CONSIDERATIONS

### **1. IP Whitelist** (Recommended)
```php
// Di Controller
$allowedIPs = ['192.168.1.100', '10.0.0.50']; // IP Aksel Gateway
if (!in_array($ipAddress, $allowedIPs)) {
    log_message('warning', 'Callback from unauthorized IP', ['ip' => $ipAddress]);
    return $this->response->setStatusCode(403)->setJSON(['error' => 'Unauthorized']);
}
```

### **2. Request Signature** (Future Enhancement)
```php
// Validasi signature dari Aksel Gateway
$signature = $this->request->getGet('signature');
$expectedSignature = hash_hmac('sha256', $ref . $rescore, $secretKey);
if ($signature !== $expectedSignature) {
    return $this->response->setStatusCode(401)->setJSON(['error' => 'Invalid signature']);
}
```

### **3. Rate Limiting**
```php
// Limit callback per REF_NUMBER (prevent duplicate attack)
$recentCallbacks = $this->callbackLogModel
    ->where('ref_number', $ref)
    ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')))
    ->countAllResults();

if ($recentCallbacks > 3) { // Max 3 callback per 5 menit
    log_message('warning', 'Too many callbacks for REF_NUMBER', ['ref' => $ref]);
    return $this->response->setStatusCode(429)->setJSON(['error' => 'Too many requests']);
}
```

---

## 🚀 NEXT STEPS

1. ✅ **Run Migration**
   ```bash
   php spark migrate
   ```

2. ✅ **Test Callback Endpoint**
   ```bash
   curl "http://localhost:8080/callback?ref=TEST001&rescore=00&rescoreref=CORE001"
   ```

3. ✅ **Monitor Logs**
   ```bash
   tail -f writable/logs/log-*.log
   ```

4. ⏳ **Create Admin Interface** (Optional)
   - View callback logs
   - Reprocess failed callbacks
   - Callback statistics dashboard

5. ⏳ **Setup Cron Job** (Optional)
   - Auto-reprocess failed callbacks
   - Archive old callback logs (> 30 hari)

---

## 📝 KESIMPULAN

Menggunakan **tabel terpisah `t_akselgatefwd_callback_log`** adalah pilihan terbaik karena:

✅ **Clean Architecture** - Separation of concerns yang jelas  
✅ **Complete Audit Trail** - Setiap callback tercatat lengkap  
✅ **Easy Monitoring** - Query terpisah tidak mengganggu business data  
✅ **Data Integrity** - Business data tetap clean  
✅ **Scalable** - Mudah di-extend untuk fitur baru  
✅ **Debuggable** - Raw callback data tersimpan untuk troubleshooting  

**Alternative yang TIDAK disarankan:**
- ❌ Update langsung ke `t_settle_message` tanpa log → Kehilangan audit trail
- ❌ Simpan di `t_akselgate_transaction_log` → Mixing batch & individual level
- ❌ Buat kolom baru di `t_settle_message` → Bloated table structure

**Recommendation: GO WITH SEPARATE TABLE** 🎯
