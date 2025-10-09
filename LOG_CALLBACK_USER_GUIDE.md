# 📊 LOG CALLBACK AKSELGATE FWD - USER GUIDE

**Created**: October 9, 2025  
**Module**: Log Callback Viewer  
**URL**: `/log/callback`  
**Permission**: `view log callback`

---

## 📋 OVERVIEW

Menu **Log Callback** digunakan untuk memonitor dan menganalisis callback yang diterima dari **Akselgate FWD (Forward) Gateway**. Setiap transaksi yang diproses oleh Aksel FWD akan mengirimkan callback ke sistem ini.

### **Fungsi Utama:**
✅ View riwayat callback per transaksi  
✅ Filter by tanggal, kode settle, status  
✅ Statistics panel (Total, Success, Failed, Unprocessed)  
✅ Detail modal dengan raw callback data (JSON)  
✅ Monitor callback yang belum diproses  

---

## 🎯 AKSES MENU

### **Navigasi:**
```
Sidebar → Log → Callback
```

### **Permission Required:**
- Permission: `view log callback`
- Jika tidak punya permission, akan redirect ke dashboard dengan error message

---

## 📊 STATISTICS PANEL

Di bagian atas halaman terdapat **4 kartu statistik** yang update otomatis:

### **1. Total Callback**
- Icon: 🗄️ Database (biru)
- Menampilkan: Total callback diterima pada tanggal terpilih
- Update: Otomatis saat ganti tanggal atau filter

### **2. Success**
- Icon: ✅ Check Circle (hijau)
- Menampilkan: Jumlah callback dengan status SUCCESS
- Kriteria: `res_code = '00'`

### **3. Failed**
- Icon: ❌ Times Circle (merah)
- Menampilkan: Jumlah callback dengan status FAILED
- Kriteria: `res_code ≠ '00'`

### **4. Unprocessed**
- Icon: ⚠️ Exclamation Triangle (kuning)
- Menampilkan: Callback yang belum di-update ke `t_settle_message`
- Kriteria: `is_processed = 0`
- **Penting**: Jika ada unprocessed, perlu dicek kenapa tidak ter-update

---

## 🔍 FILTER PANEL

### **1. Tanggal**
- **Type**: Date input
- **Default**: Tanggal hari ini
- **Fungsi**: Filter callback berdasarkan tanggal diterima
- **Auto-reload**: Ya (saat tanggal diubah)

### **2. Kode Settle**
- **Type**: Text input
- **Fungsi**: Search kode settle (partial match)
- **Contoh**: Ketik "ABC" akan cari semua kd_settle yang mengandung "ABC"
- **Case**: Insensitive

### **3. Status**
- **Type**: Select dropdown (Select2)
- **Options**:
  - `Semua` - Tampilkan semua status
  - `SUCCESS` - Hanya yang berhasil
  - `FAILED` - Hanya yang gagal

### **Tombol:**
- **Filter**: Apply filter yang sudah diset
- **Reset**: Reset semua filter ke default (tanggal hari ini, kosong semua)

---

## 📋 DATATABLE COLUMNS

| No | Kolom | Deskripsi | Format |
|----|-------|-----------|--------|
| 1 | **No** | Nomor urut (per halaman) | 1, 2, 3, ... |
| 2 | **Waktu Diterima** | Timestamp callback diterima | `09/10/2025 14:30:45` |
| 3 | **REF Number** | Reference Number transaksi | Bold, dari `t_settle_message` |
| 4 | **Kode Settle** | Kode settlement | Bold |
| 5 | **Response Code** | Response code dari core banking | Badge hijau (`00`) / kuning (lainnya) |
| 6 | **Core Ref** | Core Reference Number | Text abu-abu kecil |
| 7 | **Status** | Status callback | Badge SUCCESS (hijau) / FAILED (merah) |
| 8 | **Processed** | Sudah di-update ke settlement? | Badge Yes (hijau) / No (kuning) |
| 9 | **IP Address** | IP pengirim callback | Text abu-abu kecil |
| 10 | **Aksi** | Tombol detail | Button "Detail" (biru) |

### **Badge Colors:**
- 🟢 **Hijau**: SUCCESS, Response code `00`, Processed Yes
- 🔴 **Merah**: FAILED status
- 🟡 **Kuning**: Response code error, Processed No

---

## 🔎 DETAIL MODAL

### **Cara Buka:**
Klik tombol **Detail** pada baris data

### **Informasi yang Ditampilkan:**

#### **Section 1: Basic Info (Kiri)**
- **ID**: ID callback log
- **REF Number**: Reference Number (bold)
- **Kode Settle**: Kode settlement (bold)
- **Response Code**: Code dari core banking
- **Core Reference**: Nomor referensi core

#### **Section 2: Status Info (Kanan)**
- **Status**: Badge SUCCESS/FAILED
- **Processed**: Badge Yes/No
- **IP Address**: IP pengirim callback
- **Waktu Diterima**: Timestamp callback diterima
- **Waktu Diproses**: Timestamp di-update ke settlement (atau `-` jika belum)

#### **Section 3: Raw Callback Data**
- Format: JSON (pretty print)
- Background: Hitam
- Text color: Abu-abu terang (#d4d4d4)
- Scrollable: Max height 400px
- Contoh data:
```json
{
  "ref": "REF001",
  "rescore": "00",
  "rescoreref": "CORE001",
  "received_at": "2025-10-09 14:30:45",
  "ip_address": "192.168.1.100"
}
```

---

## 🎨 UI/UX FEATURES

### **DataTable Configuration:**
✅ Server-side processing (optimal untuk data besar)  
✅ Pagination: 10 data per halaman  
✅ Sorting: Click column header untuk sort  
✅ Responsive: Mobile-friendly  
✅ No search box (gunakan filter panel)  
✅ No length menu (fixed 10)  

### **Auto-Reload Features:**
- Ganti tanggal → Auto reload table & statistics
- Submit filter → Reload table & statistics
- Reset → Reload dengan filter default

### **Select2 Integration:**
- Dropdown Status menggunakan Select2
- Searchable dropdown
- Beautiful UI

---

## 💡 USE CASES

### **Use Case 1: Monitor Callback Hari Ini**
```
1. Buka menu Log → Callback
2. Lihat statistics panel (4 kartu di atas)
3. Periksa "Unprocessed" - seharusnya 0
4. Scroll table untuk lihat detail per callback
```

### **Use Case 2: Cari Callback by Kode Settle**
```
1. Input kode settle di filter "Kode Settle"
2. Klik tombol "Filter"
3. Table akan tampilkan callback untuk kd_settle tersebut
4. Klik "Detail" untuk lihat raw data
```

### **Use Case 3: Cek Callback yang Gagal**
```
1. Pilih "FAILED" di dropdown Status
2. Klik tombol "Filter"
3. Table tampilkan hanya callback yang gagal
4. Lihat Response Code untuk analisis error
5. Klik Detail untuk lihat raw callback data
```

### **Use Case 4: Investigasi Callback yang Belum Diproses**
```
1. Perhatikan kartu "Unprocessed" (kuning)
2. Jika ada angka > 0:
   - Filter by status atau kd_settle
   - Klik Detail pada row yang belum processed
   - Check "Waktu Diproses" = "-"
   - Check application log untuk error
```

### **Use Case 5: Analisis Callback Historical**
```
1. Ganti tanggal ke tanggal yang ingin dilihat
2. Table auto-reload dengan data tanggal tersebut
3. Statistics panel update otomatis
4. Export data (jika diperlukan) via browser print
```

---

## 🔧 TROUBLESHOOTING

### **Issue 1: Table Kosong**
**Possible Causes:**
- Tidak ada callback pada tanggal terpilih
- Filter terlalu ketat
- Backend error

**Solution:**
1. Check statistics panel - jika "Total Callback" = 0, berarti memang tidak ada data
2. Coba reset filter
3. Ganti ke tanggal lain
4. Check browser console (F12) untuk error
5. Check application log

### **Issue 2: Statistics Tidak Update**
**Possible Causes:**
- AJAX error
- Backend error

**Solution:**
1. Refresh halaman (F5)
2. Check browser console untuk error
3. Check application log: `writable/logs/log-*.log`

### **Issue 3: Detail Modal Error**
**Possible Causes:**
- Record tidak ditemukan
- Backend error

**Solution:**
1. Check ID callback masih ada di database
2. Refresh halaman dan coba lagi
3. Check application log

### **Issue 4: Unprocessed Count Tinggi**
**Possible Causes:**
- REF_NUMBER tidak ditemukan di `t_settle_message`
- Error saat update settlement message
- Backend error

**Solution:**
1. Click Detail pada row unprocessed
2. Check "REF Number" - pastikan exist di `t_settle_message`
3. Check application log:
   ```bash
   grep "callback" writable/logs/log-2025-10-09.log
   ```
4. Cari error message related to REF_NUMBER
5. Fix data di database jika perlu

---

## 📊 QUERY EXAMPLES (For Developer)

### **Get Unprocessed Callbacks**
```sql
SELECT * FROM t_akselgatefwd_callback_log
WHERE is_processed = 0
ORDER BY created_at DESC;
```

### **Get Today's Statistics**
```sql
SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'SUCCESS' THEN 1 ELSE 0 END) as success,
    SUM(CASE WHEN status = 'FAILED' THEN 1 ELSE 0 END) as failed,
    SUM(CASE WHEN is_processed = 1 THEN 1 ELSE 0 END) as processed,
    SUM(CASE WHEN is_processed = 0 THEN 1 ELSE 0 END) as unprocessed
FROM t_akselgatefwd_callback_log
WHERE DATE(created_at) = CURDATE();
```

### **Check Callback vs Settlement**
```sql
SELECT 
    cl.ref_number,
    cl.status as callback_status,
    cl.is_processed,
    sm.r_code,
    sm.r_message
FROM t_akselgatefwd_callback_log cl
LEFT JOIN t_settle_message sm ON cl.ref_number = sm.REF_NUMBER
WHERE cl.created_at >= CURDATE()
ORDER BY cl.created_at DESC;
```

---

## 📱 MOBILE RESPONSIVE

✅ Table responsive (horizontal scroll jika perlu)  
✅ Statistics cards stack vertical di mobile  
✅ Filter panel adapt ke layar kecil  
✅ Modal full screen di mobile  

---

## 🎯 BEST PRACTICES

### **Untuk Admin:**
1. ✅ Monitor "Unprocessed" count setiap hari
2. ✅ Jika ada callback FAILED, investigasi kenapa
3. ✅ Pastikan semua callback ter-processed dalam 5 menit
4. ✅ Archive old data (> 30 hari) untuk performa

### **Untuk Developer:**
1. ✅ Check application log jika ada issue
2. ✅ Monitor response time DataTable
3. ✅ Setup cron job untuk reprocess failed callbacks
4. ✅ Add index jika query lambat

### **Monitoring:**
```bash
# Check callback today
mysql> SELECT COUNT(*) FROM t_akselgatefwd_callback_log WHERE DATE(created_at) = CURDATE();

# Check unprocessed
mysql> SELECT COUNT(*) FROM t_akselgatefwd_callback_log WHERE is_processed = 0;

# Check failed
mysql> SELECT * FROM t_akselgatefwd_callback_log WHERE status = 'FAILED' AND DATE(created_at) = CURDATE();
```

---

## 📞 SUPPORT

**Questions?** Contact:
- Developer Team
- Check documentation: `AKSELGATE_FWD_CALLBACK_DATABASE_DESIGN.md`
- Application logs: `writable/logs/log-*.log`

**Related Files:**
- Controller: `app/Controllers/Log/AkselgateFwdCallbackLogController.php`
- View: `app/Views/log/akselgate_fwd_callback/index.blade.php`
- JavaScript: `public/js/log/akselgate_fwd_callback/index.js`
- Model: `app/Models/ApiGateway/AkselgateFwdCallbackLog.php`

---

## ✅ SUMMARY

✅ **Menu baru "Log → Callback" sudah aktif**  
✅ **Statistics panel untuk quick overview**  
✅ **Filter lengkap (tanggal, kd_settle, status)**  
✅ **Detail modal dengan raw JSON data**  
✅ **Server-side DataTable untuk performa optimal**  
✅ **Mobile responsive & user-friendly**  

**Status**: ✅ READY TO USE 🚀
