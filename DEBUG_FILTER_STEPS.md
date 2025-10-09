# Debug Filter - Step by Step Guide

## 🔍 Langkah-langkah Debug Filter

### Step 1: Buka Halaman & Browser Console
1. Buka halaman: `http://your-domain/log/callback`
2. Tekan **F12** untuk buka Developer Tools
3. Pilih tab **Console**

### Step 2: Isi Filter & Submit
1. Isi form filter:
   - **Tanggal:** 2025-10-08
   - **Kode Settle:** aaaaaaaaaaa
   - **Status:** FAILED
2. Klik tombol **"Filter"**

### Step 3: Cek Console Browser
Anda harus melihat log seperti ini:

```javascript
Filter form submitted, reloading DataTable...
Filter values being sent: {tanggal: '2025-10-08', kd_settle: 'aaaaaaaaaaa', status: 'FAILED'}
DataTable response: {recordsTotal: 2, recordsFiltered: 0, dataCount: 0}
No data found matching the filter criteria
```

**Analisis:**
- `recordsTotal: 2` = Total semua data di database (tanpa filter)
- `recordsFiltered: 0` = Data setelah filter diterapkan (0 = tidak ada yang match)
- `dataCount: 0` = Jumlah data yang ditampilkan

### Step 4: Cek Log File PHP
Buka file: `writable/logs/log-2025-10-09.log`

Cari bagian terbaru, harus ada log seperti ini:

```
DEBUG - 2025-10-09 xx:xx:xx --> === DataTable Request ===
DEBUG - 2025-10-09 xx:xx:xx --> ALL POST data: {"draw":"1","columns":[...],"tanggal":"2025-10-08","kd_settle":"aaaaaaaaaaa","status":"FAILED"}
DEBUG - 2025-10-09 xx:xx:xx --> Filter values - Tanggal: 2025-10-08, KD_SETTLE: aaaaaaaaaaa, Status: FAILED
DEBUG - 2025-10-09 xx:xx:xx --> Applied filter: DATE(created_at) = 2025-10-08
DEBUG - 2025-10-09 xx:xx:xx --> Applied filter: kd_settle LIKE %aaaaaaaaaaa%
DEBUG - 2025-10-09 xx:xx:xx --> Applied filter: status = FAILED
DEBUG - 2025-10-09 xx:xx:xx --> Query result - Total records: 2, Filtered records: 0, Data returned: 0
```

**Analisis:**
- ✅ POST data diterima dengan benar
- ✅ Filter diterapkan ke query (WHERE dan LIKE)
- ✅ Hasil query: 0 records (tidak ada yang match)

### Step 5: Test dengan Data yang Ada
Sekarang test dengan data yang BENAR-BENAR ADA di database:

1. **Reset filter:** Klik tombol **"Reset"**
2. **Isi filter baru:**
   - **Tanggal:** 2025-10-09 (hari ini)
   - **Kode Settle:** SE42R9SX
   - **Status:** FAILED
3. **Klik "Filter"**

**Expected Console Output:**
```javascript
Filter values being sent: {tanggal: '2025-10-09', kd_settle: 'SE42R9SX', status: 'FAILED'}
DataTable response: {recordsTotal: 2, recordsFiltered: 2, dataCount: 2}
```

**Expected Log File:**
```
DEBUG --> Filter values - Tanggal: 2025-10-09, KD_SETTLE: SE42R9SX, Status: FAILED
DEBUG --> Applied filter: DATE(created_at) = 2025-10-09
DEBUG --> Applied filter: kd_settle LIKE %SE42R9SX%
DEBUG --> Applied filter: status = FAILED
DEBUG --> Query result - Total records: 2, Filtered records: 2, Data returned: 2
```

**Expected Result:** DataTable menampilkan 2 data! ✅

---

## 🐛 Troubleshooting

### Masalah: Data tetap muncul semua meskipun filter diisi

**Kemungkinan Penyebab:**

#### 1. Cache Browser
**Solusi:**
```
Ctrl + Shift + Delete → Clear cache → Hard Reload (Ctrl + Shift + R)
```

#### 2. JavaScript tidak reload dengan benar
**Cek Console, harus ada:**
```javascript
Filter form submitted, reloading DataTable...
Filter values being sent: {...}
```

**Jika tidak ada:**
- Clear cache browser
- Cek apakah ada JavaScript error (warna merah di Console)

#### 3. POST data tidak terkirim
**Cek di log file, harus ada:**
```
ALL POST data: {"draw":"1",...,"tanggal":"2025-10-08","kd_settle":"aaaaaaaaaaa","status":"FAILED"}
```

**Jika tanggal/kd_settle/status = null:**
- Cek nama field di HTML (harus: id="tanggal", id="kd_settle", id="status")
- Cek jQuery selector di JavaScript

#### 4. Filter tidak diterapkan di query
**Cek di log file, harus ada:**
```
Applied filter: DATE(created_at) = 2025-10-08
Applied filter: kd_settle LIKE %aaaaaaaaaaa%
Applied filter: status = FAILED
```

**Jika tidak ada:**
- Cek kondisi `if (!empty($tanggal))` di controller
- Cek apakah nilai null/empty string

#### 5. Data memang tidak ada yang match
**Cek di log file:**
```
Query result - Filtered records: 0, Data returned: 0
```

**Ini NORMAL jika:**
- Tidak ada data dengan tanggal tersebut
- Tidak ada data dengan kd_settle tersebut
- Kombinasi filter tidak match dengan data apapun

**Solusi:** Gunakan filter yang sesuai dengan data yang ada!

---

## ✅ Checklist Debug

- [ ] Browser Console terbuka (F12)
- [ ] Form filter diisi dengan benar
- [ ] Tombol "Filter" diklik
- [ ] Console menunjukkan "Filter form submitted"
- [ ] Console menunjukkan "Filter values being sent" dengan nilai yang benar
- [ ] Console menunjukkan "DataTable response" dengan recordsFiltered
- [ ] Log file menunjukkan "ALL POST data" dengan filter values
- [ ] Log file menunjukkan "Applied filter" untuk setiap filter yang diisi
- [ ] Log file menunjukkan "Query result" dengan jumlah data
- [ ] DataTable menampilkan hasil yang sesuai (atau "No data available" jika 0)

---

## 📞 Jika Masih Bermasalah

Kirimkan informasi berikut:

1. **Screenshot Console Browser** (setelah klik Filter)
2. **Screenshot DataTable** (hasil yang ditampilkan)
3. **Copy isi log file** (bagian yang relevan)
4. **Data di database** (hasil query: `SELECT * FROM t_akselgatefwd_callback_log`)

---

**Last Updated:** 2025-10-09
**Status:** Enhanced Debugging Mode ✅
