# Callback Log Filter - Testing Guide

## ✅ Status: Filter Berfungsi Normal!

Filter sudah berfungsi dengan benar. Jika tidak ada hasil, berarti memang tidak ada data yang sesuai dengan kriteria filter.

---

## 📊 Data yang Ada di Database (Contoh)

Berdasarkan pengecekan database, berikut contoh data yang ada:

| ID | REF_NUMBER | KD_SETTLE | RES_CODE | STATUS | CREATED_AT |
|----|------------|-----------|----------|---------|------------|
| 1  | SE42R9SX323K | SE42R9SX | ERR | FAILED | 2025-10-09 |
| 2  | SE42R9SX323K | SE42R9SX | ERR | FAILED | 2025-10-09 |

---

## 🧪 Cara Testing Filter

### Test 1: Filter by Tanggal (Berhasil)
```
Tanggal: 2025-10-09
Kode Settle: (kosong)
Status: (Semua)

Expected: Menampilkan 2 data
```

### Test 2: Filter by Tanggal + Status (Berhasil)
```
Tanggal: 2025-10-09
Kode Settle: (kosong)
Status: FAILED

Expected: Menampilkan 2 data
```

### Test 3: Filter by Kode Settle (Berhasil)
```
Tanggal: 2025-10-09
Kode Settle: SE42R9SX
Status: (Semua)

Expected: Menampilkan 2 data (karena LIKE search)
```

### Test 4: Filter dengan Data yang Tidak Ada (Normal)
```
Tanggal: 2025-10-08
Kode Settle: aaaaaaaaaaa
Status: (Semua)

Expected: "No data available in table"
Ini NORMAL karena memang tidak ada data dengan kriteria tersebut!
```

---

## 🔍 Cara Verifikasi Filter Berjalan

### 1. Cek Browser Console (F12)
Setiap kali filter, akan muncul log:
```javascript
Filter values being sent: {tanggal: '2025-10-09', kd_settle: 'SE42R9SX', status: 'FAILED'}
```

### 2. Cek Log File (`writable/logs/log-YYYY-MM-DD.log`)
```
DEBUG - 2025-10-09 14:04:51 --> Filter values - Tanggal: 2025-10-09, KD_SETTLE: SE42R9SX, Status: FAILED
```

### 3. Cek SQL Query (Optional - Set Config Debug = true)
Di `app/Config/Database.php` set `'DBDebug' => true` untuk melihat query SQL yang dijalankan.

---

## 🎯 Kesimpulan

**Filter sudah berfungsi 100% dengan benar!**

Yang terjadi:
- ✅ JavaScript mengirim filter values
- ✅ Controller menerima filter values
- ✅ Query SQL dibangun dengan WHERE clause yang benar
- ✅ Database diquery dengan filter
- ✅ Hasil dikembalikan ke DataTable

Jika tidak ada hasil:
- Bukan bug/error
- Memang tidak ada data yang sesuai kriteria filter
- DataTable akan menampilkan "No data available in table"

---

## 💡 Tips Testing

1. **Gunakan data yang ada**: Filter dengan tanggal hari ini (2025-10-09)
2. **Test LIKE search**: Kode settle bisa partial match (misal: cari "SE42" akan ketemu "SE42R9SX")
3. **Reset filter**: Klik tombol "Reset" untuk kembali ke default
4. **Cek console**: Selalu buka Browser Console untuk melihat log filter

---

## 🐛 Jika Masih Ada Masalah

1. Clear browser cache (Ctrl+Shift+Delete)
2. Clear session: `php spark cache:clear`
3. Check permission: User harus punya permission 'view log callback'
4. Check database: Pastikan tabel `t_akselgatefwd_callback_log` ada dan berisi data
5. Check timezone: Pastikan timezone server sama dengan timezone database

---

**Last Updated:** 2025-10-09
**Status:** ✅ Working as Expected
