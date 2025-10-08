# Log Akselgate - Documentation

## Overview
Menu Log Akselgate adalah halaman untuk melihat dan monitoring semua transaksi yang dikirim ke Akselgate API Gateway. Halaman ini menyediakan fitur filtering, searching, dan detail view untuk request/response payload.

## 📂 File Structure

```
app/
├── Controllers/
│   └── Log/
│       └── AkselgateLogController.php      # Controller untuk log Akselgate
├── Views/
│   └── log/
│       └── akselgate/
│           └── index.blade.php             # View utama dengan filter & datatable
├── Config/
│   └── Routes/
│       └── user_management.php             # Routes untuk log Akselgate

public/
└── js/
    └── log/
        └── akselgate/
            └── index.js                     # JavaScript untuk DataTable & modal
```

## 🚀 Features

### 1. **Filter Data**
- **Tanggal**: Filter berdasarkan tanggal transaksi (format: YYYY-MM-DD)
- **Tipe Transaksi**: Filter berdasarkan jenis transaksi
  - CA to Escrow (`CA_ESCROW`)
  - Escrow to Biller PL (`ESCROW_BILLER_PL`)
- **Status**: Filter berdasarkan status transaksi
  - Semua
  - Sukses (is_success = 1)
  - Gagal (is_success = 0)
- **Kode Settle**: Search berdasarkan kode settlement

### 2. **DataTable Display**
Menampilkan kolom:
- **ID**: ID log
- **Tanggal**: Timestamp transaksi (created_at)
- **Tipe Transaksi**: Badge berwarna untuk CA_ESCROW atau ESCROW_BILLER_PL
- **Kode Settle**: Kode settlement
- **Request ID**: ID request yang dikirim ke Akselgate
- **Attempt**: Nomor percobaan (ditandai warning jika > 1)
- **Total Tx**: Jumlah transaksi dalam batch
- **Status Code**: HTTP status code response
- **Status**: Badge sukses/gagal
- **Latest**: Badge untuk menandai attempt terbaru
- **Aksi**: Button detail untuk melihat payload lengkap

**Settings**:
- Page length: 15 rows (fixed, tidak bisa diubah)
- Search box: Disabled (menggunakan filter di atas)
- Length menu: Disabled (tidak perlu karena ada filter kd_settle)
- Language: Default English (tidak ditranslate)
- Sorting: Default by created_at DESC

### 3. **Detail Modal**
Modal untuk melihat detail lengkap log:
- **Informasi Umum**: ID, tanggal, tipe transaksi, kode settle, request ID
- **Status Detail**: Attempt number, total transaksi, status code, response code
- **Response Message**: Pesan error/sukses dari Akselgate
- **Request Payload**: JSON payload yang dikirim (formatted)
- **Response Payload**: JSON response dari Akselgate (formatted)

### 4. **Export & Print**
- **Export to Excel**: Button di panel toolbar (kanan atas tabel)
  - Trigger DataTables Excel export
  - Export semua data yang terfilter
  - Format: `.xlsx`

## 🔧 Technical Implementation

### Controller (AkselgateLogController.php)

#### Methods:
1. **`index()`**
   - Display halaman utama
   - Set tanggal default (hari ini)
   - Log activity user

2. **`datatable()`**
   - Handle AJAX request dari DataTables
   - Apply filters (tanggal, transaction_type, status, kd_settle)
   - Apply search (global search)
   - Apply ordering & pagination
   - Return JSON response dengan CSRF token

3. **`detail($id)`**
   - Get detail log by ID
   - Return JSON dengan full payload data

### View (index.blade.php)

#### Sections:
1. **Filter Form**
   - Form dengan 4 input filter
   - Submit button & Reset button
   - Panel collapsible

2. **DataTable**
   - Table dengan 11 kolom
   - Server-side processing
   - Responsive layout

3. **Detail Modal**
   - Bootstrap modal XL size
   - 2 column layout untuk info
   - Pre-formatted JSON display

### JavaScript (index.js)

#### Functions:
1. **CSRF Token Management**
   - `updateCsrfToken()`: Update token dari server
   - `getCsrfToken()`: Get current token
   - `getCsrfTokenName()`: Get token name

2. **DataTable**
   - `initDataTable()`: Initialize dengan server-side processing
   - Custom column rendering untuk badges
   - Excel export button only

3. **Detail Modal**
   - `showDetailModal(logId)`: Load & display detail
   - Format JSON payloads

4. **Event Handlers**
   - Filter form submit
   - Reset button
   - Export Excel button
   - Detail button click
   - Auto reload on date change

## 🔐 Permissions

**Permission Name**: `view log akselgate`

**Naming Convention**: Konsisten dengan permission lain di menu Log:
- `view log activity` - Untuk log aktivitas user
- `view log error` - Untuk log error sistem
- `view log akselgate` - Untuk log transaksi Akselgate

**Usage**:
```php
$log_akselgate = in_array('view log akselgate', $permissions ?? []) ?? true;
```

**Menu Display Logic**:
```php
$show_log = $activity || $error || $log_akselgate;
```

## 📊 Database Schema

**Table**: `t_akselgate_transaction_log`

**Relevant Columns**:
- `id`: Primary key
- `created_at`: Timestamp transaksi
- `transaction_type`: Tipe transaksi (CA_ESCROW, ESCROW_BILLER_PL)
- `kd_settle`: Kode settlement
- `request_id`: ID request unik
- `attempt_number`: Nomor percobaan
- `total_transaksi`: Jumlah transaksi dalam batch
- `status_code_res`: HTTP status code
- `response_code`: Response code dari Akselgate
- `response_message`: Pesan response
- `is_success`: Status sukses (1) atau gagal (0)
- `is_latest`: Flag untuk attempt terbaru (1) atau lama (0)
- `request_payload`: JSON request yang dikirim
- `response_payload`: JSON response yang diterima

## 🎨 UI/UX Design

### Color Scheme:
- **Transaction Type Badges**:
  - CA_ESCROW: `badge-info` (biru)
  - ESCROW_BILLER_PL: `badge-primary` (biru tua)

- **Status Code Badges**:
  - 200/201: `badge-success` (hijau)
  - 4xx: `badge-warning` (kuning)
  - 5xx: `badge-danger` (merah)

- **Status Badges**:
  - Sukses: `badge-success` (hijau) dengan icon check
  - Gagal: `badge-danger` (merah) dengan icon times

- **Latest Badges**:
  - Latest: `badge-primary` (biru) dengan icon star
  - Old: `badge-secondary` (abu-abu)

- **Attempt Badges**:
  - Attempt > 1: `badge-warning` (kuning) - menandai retry

### Responsive Design:
- Desktop: Full table dengan 11 kolom
- Tablet/Mobile: DataTables responsive mode (collapse columns)

## 🔗 Routes

```php
// View page
GET  /log/akselgate

// DataTable AJAX
POST /log/akselgate/datatable

// Detail by ID
GET  /log/akselgate/detail/{id}
```

## 📝 Usage Example

### Access Page:
1. Login ke sistem
2. Buka menu **Log** di sidebar
3. Klik submenu **Akselgate**
4. Set filter tanggal, tipe transaksi, status (optional)
5. Klik **Filter** untuk apply filter
6. Klik **Detail** pada row untuk melihat payload lengkap

### Filter Example:
```javascript
// Filter transaksi CA_ESCROW yang gagal pada tanggal tertentu
Tanggal: 2025-10-08
Tipe Transaksi: CA to Escrow
Status: Gagal
Kode Settle: (kosongkan untuk semua)
```

### Search Example:
```
// Global search di kolom kd_settle, request_id, transaction_type, response_message
Search: "SETL_202510"
```

## 🐛 Error Handling

### DataTable Error:
- Catch AJAX error dan display toastr
- Return empty data dengan error message

### Detail Modal Error:
- Catch 404 (log not found)
- Catch 500 (server error)
- Display toastr error message

### CSRF Token:
- Auto update token dari setiap response
- Include token di setiap request

## 🚀 Performance Optimization

1. **Server-Side Processing**: DataTables menggunakan server-side processing untuk handle data besar
2. **Pagination**: Default 10 rows per page (bisa diubah ke 25, 50, 100)
3. **Indexing**: Database menggunakan index pada `created_at`, `transaction_type`, `kd_settle`, `is_success`
4. **Lazy Loading**: Modal detail hanya load data saat button diklik

## 📋 TODO / Future Enhancements

1. [ ] Add real-time notification saat ada transaksi baru
2. [ ] Add bulk retry untuk transaksi yang gagal
3. [ ] Add chart/graph untuk visualisasi sukses vs gagal
4. [ ] Add auto-refresh setiap X detik
5. [ ] Add filter by date range (start date - end date)
6. [ ] Add download individual request/response payload
7. [ ] Add comparison view untuk retry attempts

## 👥 Maintainers

- Backend: AkselgateLogController.php
- Frontend: index.blade.php, index.js
- Routes: user_management.php
- Permissions: app.blade.php (sidebar)

---

**Last Updated**: October 8, 2025
**Version**: 1.0.0
