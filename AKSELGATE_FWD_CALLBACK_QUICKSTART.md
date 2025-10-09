# 🚀 QUICK START - Akselgate FWD Callback Implementation

## 📋 SUMMARY

**Created**: October 9, 2025  
**Purpose**: Implementasi tabel terpisah untuk callback Aksel FWD Gateway  
**Decision**: ✅ **USE SEPARATE TABLE** `t_akselgatefwd_callback_log`

---

## ⚡ QUICK STEPS

### **1. Run Migration**
```bash
php spark migrate
```

**Expected Output:**
```
Running: 2025-10-09-000000_App\Database\Migrations\CreateAkselgateFwdCallbackLogTable
Migrated: 2025-10-09-000000_App\Database\Migrations\CreateAkselgateFwdCallbackLogTable
```

**Verify Table Created:**
```sql
SHOW TABLES LIKE 't_akselgatefwd_callback_log';
DESC t_akselgatefwd_callback_log;
```

---

### **2. Test Callback Endpoint**

**Test SUCCESS Callback:**
```bash
curl -X GET "http://localhost:8080/callback?ref=TEST001&rescore=00&rescoreref=CORE001"
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Callback processed successfully",
    "ref": "TEST001",
    "status": "SUCCESS",
    "timestamp": "2025-10-09 10:30:45"
}
```

**Test FAILED Callback:**
```bash
curl -X GET "http://localhost:8080/callback?ref=TEST002&rescore=99&rescoreref=CORE002"
```

**Expected Response:**
```json
{
    "success": true,
    "message": "Callback processed successfully",
    "ref": "TEST002",
    "status": "FAILED",
    "timestamp": "2025-10-09 10:31:15"
}
```

**Note**: 
- `rescore=00` → Status **SUCCESS**
- `rescore≠00` → Status **FAILED**
- Tidak ada status PENDING karena callback hanya dikirim setelah transaksi selesai diproses

---

### **3. Verify Data**

**Check Callback Log:**
```sql
SELECT * FROM t_akselgatefwd_callback_log 
ORDER BY created_at DESC 
LIMIT 10;
```

**Check Settlement Message Update:**
```sql
SELECT 
    sm.REF_NUMBER,
    sm.KD_SETTLE,
    sm.AMOUNT,
    sm.r_code,
    sm.r_message,
    sm.r_coreReference,
    sm.r_dateTime
FROM t_settle_message sm
WHERE sm.REF_NUMBER = 'TEST001';
```

**Check Callback Status:**
```sql
SELECT 
    cl.ref_number,
    cl.kd_settle,
    cl.status,
    cl.is_processed,
    cl.processed_at,
    cl.created_at
FROM t_akselgatefwd_callback_log cl
WHERE cl.ref_number = 'TEST001';
```

---

## 📊 FILES CREATED/UPDATED

### **1. Migration File**
```
app/Database/Migrations/2025-10-09-000000_CreateAkselgateFwdCallbackLogTable.php
```
- ✅ Creates `t_akselgatefwd_callback_log` table
- ✅ 7 indexes for performance
- ✅ Complete audit trail fields

### **2. Model File**
```
app/Models/ApiGateway/AkselgateFwdCallbackLog.php
```
- ✅ CRUD operations
- ✅ Helper methods (getUnprocessed, markAsProcessed, etc)
- ✅ Statistics query

### **3. Controller Updated**
```
app/Controllers/Settlement/AkselGateCallbackController.php
```
- ✅ Two-step process (log → update)
- ✅ Complete error handling
- ✅ Audit trail logging

### **4. Documentation**
```
AKSELGATE_FWD_CALLBACK_DATABASE_DESIGN.md
```
- ✅ Complete architecture explanation
- ✅ Flow diagram
- ✅ Query examples
- ✅ Security considerations

---

## 🔍 MONITORING QUERIES

### **Unprocessed Callbacks**
```sql
SELECT COUNT(*) as total_unprocessed
FROM t_akselgatefwd_callback_log
WHERE is_processed = 0;
```

### **Today's Callback Statistics**
```sql
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed,
    SUM(CASE WHEN is_processed = 1 THEN 1 ELSE 0 END) as processed,
    ROUND(SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) / COUNT(*) * 100, 2) as success_rate
FROM t_akselgatefwd_callback_log
WHERE DATE(created_at) = CURDATE();
```

### **Callbacks by KD_SETTLE**
```sql
SELECT 
    kd_settle,
    COUNT(*) as total,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed
FROM t_akselgatefwd_callback_log
WHERE DATE(created_at) = CURDATE()
GROUP BY kd_settle
ORDER BY total DESC;
```

### **Callback Timeline**
```sql
SELECT 
    DATE_FORMAT(created_at, '%Y-%m-%d %H:%i') as time_window,
    COUNT(*) as callback_count
FROM t_akselgatefwd_callback_log
WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
GROUP BY DATE_FORMAT(created_at, '%Y-%m-%d %H:%i')
ORDER BY time_window DESC;
```

---

## 🛠️ TROUBLESHOOTING

### **Issue: Callback tidak masuk ke log**
**Check:**
1. Endpoint accessible?
   ```bash
   curl -I http://localhost:8080/callback
   ```
2. CSRF exempt? (Check `Config/Filters.php`)
3. Check application logs:
   ```bash
   tail -f writable/logs/log-$(date +%Y-%m-%d).log
   ```

### **Issue: Callback masuk tapi tidak update t_settle_message**
**Check:**
1. REF_NUMBER exists?
   ```sql
   SELECT * FROM t_settle_message WHERE REF_NUMBER = 'TEST001';
   ```
2. Check callback log yang belum diproses:
   ```sql
   SELECT * FROM t_akselgatefwd_callback_log 
   WHERE is_processed = 0;
   ```
3. Check application logs untuk error detail:
   ```bash
   tail -f writable/logs/log-$(date +%Y-%m-%d).log | grep "callback"
   ```

### **Issue: Duplicate callbacks**
**Solution**: Add rate limiting (see documentation)

---

## 📈 NEXT ENHANCEMENTS

### **1. Admin Interface** (Optional)
Create menu untuk:
- View callback logs
- Reprocess failed callbacks
- Statistics dashboard

### **2. Auto-Reprocess** (Recommended)
Cron job untuk reprocess failed callbacks:
```php
// Command: php spark callback:reprocess
php spark make:command ReprocessCallbacks
```

### **3. Archive Old Data** (Performance)
```sql
-- Archive callbacks > 30 days
CREATE TABLE t_akselgatefwd_callback_log_archive LIKE t_akselgatefwd_callback_log;

INSERT INTO t_akselgatefwd_callback_log_archive
SELECT * FROM t_akselgatefwd_callback_log
WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY);

DELETE FROM t_akselgatefwd_callback_log
WHERE created_at < DATE_SUB(CURDATE(), INTERVAL 30 DAY);
```

### **4. Security Hardening**
- IP Whitelist
- Request Signature validation
- Rate limiting per REF_NUMBER

---

## ✅ CHECKLIST

Before going to production:

- [ ] Migration executed successfully
- [ ] Test callback endpoint (success & failed cases)
- [ ] Verify data in both tables (callback log + settlement message)
- [ ] Check logs for errors
- [ ] Add IP whitelist (security)
- [ ] Setup monitoring queries
- [ ] Document callback URL for Aksel FWD team
- [ ] Test with real data (staging environment)

---

## 📞 SUPPORT

**Questions?** Check:
1. `AKSELGATE_FWD_CALLBACK_DATABASE_DESIGN.md` - Complete architecture
2. Application logs: `writable/logs/log-*.log`
3. Database errors: Check MySQL error log

**Key Files:**
- Controller: `app/Controllers/Settlement/AkselGateCallbackController.php`
- Model: `app/Models/ApiGateway/AkselgateFwdCallbackLog.php`
- Migration: `app/Database/Migrations/2025-10-09-000000_CreateAkselgateFwdCallbackLogTable.php`

---

## 🎯 CONCLUSION

✅ **Tabel terpisah `t_akselgatefwd_callback_log` sudah siap digunakan**  
✅ **Complete audit trail untuk setiap callback**  
✅ **Clean separation antara log dan business data**  
✅ **Ready for production setelah testing**

**Status**: ✅ READY TO TEST 🚀
