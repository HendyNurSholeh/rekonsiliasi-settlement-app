# MODUL SETTLEMENT - DOKUMENTASI LENGKAP

## Gambaran Umum
Modul Settlement adalah bagian dari Sistem Rekonsiliasi dan Pelimpahan Dana (SIRELA) yang berfungsi untuk membuat dan menyetujui jurnal transaksi settlement yang kemudian akan diproses di sistem core banking.

### Persyaratan Settlement
Untuk produk yang dapat dilakukan proses settlement harus memenuhi kriteria:
- Data transaksi tidak terdapat data dispute, ATAU
- Status settle verifikasinya (v_SETTLE_VERIFIKASI) adalah:
  - 1: dana tersedia dan siap settlement (dilimpahkan)
  - 9: tidak dilimpahkan

## MENU 1: BUAT JURNAL SETTLEMENT

### Lokasi File
- **Controller**: `app/Controllers/Settlement/BuatJurnalController.php`
- **View**: `app/Views/settlement/buat_jurnal.blade.php`
- **Route**: `settlement/buat-jurnal`

### Fungsi Utama
Menampilkan data dari procedure `p_compare_rekap('2025-07-21')` dengan filter tanggal dan file settle.

### Fitur Filter
1. **Tanggal Rekonsiliasi**: Filter berdasarkan tanggal rekonsiliasi
2. **File Settle**: 
   - Default (0)
   - Pajak (1) 
   - Edu (2)

### Tabel Data
Menampilkan kolom:
- No (auto increment)
- Nama Produk
- File Settle (dengan badge warna)
- Selisih (highlight merah jika != 0)
- Jum TX Dispute (highlight merah jika != 0)
- Kode Settle
- Action (tombol Create Jurnal jika KD_SETTLE masih NULL)

### Action Button: Create Jurnal
- **Kondisi**: Muncul hanya jika KD_SETTLE masih NULL
- **Validasi**: SELISIH dan JUM_TX_DISPUTE harus = 0
- **Process**: Memanggil procedure `p_generate_settle_jurnal('NAMA_PRODUK', 'TANGGAL_REKON')`
- **Hasil**: Refresh table setelah berhasil membuat jurnal

### Endpoint API
1. `GET /settlement/buat-jurnal` - Halaman utama
2. `GET /settlement/buat-jurnal/datatable` - DataTable AJAX
3. `POST /settlement/buat-jurnal/validate` - Validasi sebelum create
4. `POST /settlement/buat-jurnal/create` - Create jurnal settlement

### Parameter DataTable
```php
$tanggalRekon = $this->request->getGet('tanggal');
$fileSettle = $this->request->getGet('file_settle');
```

### Validasi Business Logic
```php
// Validasi SELISIH dan JUM_TX_DISPUTE harus 0
if ($selisih !== 0 || $jumTxDispute !== 0) {
    return error('Validasi gagal: SELISIH dan JUM_TX_DISPUTE harus 0');
}
```

## MENU 2: APPROVE JURNAL SETTLEMENT

### Lokasi File
- **Controller**: `app/Controllers/Settlement/ApproveJurnalController.php`
- **View**: `app/Views/settlement/approve_jurnal.blade.php`
- **Route**: `settlement/approve-jurnal`

### Fungsi Utama
Menampilkan data dari table `t_settle_produk` dengan filter tanggal untuk proses approval.

### Summary Cards
Dashboard cards menampilkan ringkasan:
- Total Jurnal
- Jurnal Disetujui
- Jurnal Ditolak
- Jurnal Pending

### Fitur Filter
1. **Tanggal Settlement**: Filter berdasarkan tanggal settlement
2. **Status Approval**:
   - Semua Status
   - Pending (NULL)
   - Disetujui (1)
   - Ditolak (0)

### Tabel Data
Menampilkan kolom:
- No (auto increment)
- Kode Settle
- Nama Produk
- Tanggal Settle
- Total Amount (format currency)
- Status Approval (badge dengan warna)
- User Approve
- Tanggal Approve
- Action (tombol Approve/Lihat Detail)

### Modal Approval
Ketika tombol "Approve" diklik, akan menampilkan modal dengan:

#### Header Modal
```
Jurnal Settlement tanggal [TGL_SETTLE] untuk produk [NAMA_PRODUK]
```

#### Data yang Ditampilkan
Query untuk detail jurnal:
```sql
SELECT JENIS_SETTLE, IDPARTNER, CORE, DEBIT_ACCOUNT, DEBIT_NAME, 
       CREDIT_CORE, CREDIT_ACCOUNT, CREDIT_NAME, AMOUNT, DESCRIPTION, REF_NUMBER 
FROM tamp_settle_message 
WHERE KD_SETTLE = 'KODE_SETTLE'
```

#### Action Buttons
- **Setujui**: Memanggil `p_approve_settle_jurnal(KD_SETTLE, TANGGAL, USER, '1')`
- **Tolak**: Memanggil `p_approve_settle_jurnal(KD_SETTLE, TANGGAL, USER, '0')`

### Endpoint API
1. `GET /settlement/approve-jurnal` - Halaman utama
2. `GET /settlement/approve-jurnal/datatable` - DataTable AJAX
3. `POST /settlement/approve-jurnal/detail` - Detail jurnal untuk modal
4. `POST /settlement/approve-jurnal/process` - Proses approval/rejection
5. `GET /settlement/approve-jurnal/summary` - Summary statistics

### Parameter DataTable
```php
$tanggalRekon = $this->request->getGet('tanggal');
$statusApprove = $this->request->getGet('status_approve');
```

### Proses Approval
```php
// Parameter untuk procedure
$kdSettle = $this->request->getPost('kd_settle');
$tanggalRekon = $this->request->getPost('tanggal_rekon');
$action = $this->request->getPost('action'); // 'approve' or 'reject'
$username = session()->get('username');
$approvalStatus = ($action === 'approve') ? '1' : '0';

// Call procedure
CALL p_approve_settle_jurnal($kdSettle, $tanggalRekon, $username, $approvalStatus);
```

## STRUKTUR DATABASE

### Table: t_settle_produk
```sql
- id (primary key)
- KD_SETTLE (kode settlement)
- NAMA_PRODUK (nama produk)
- TGL_SETTLE (tanggal settlement)
- TOTAL_AMOUNT (total amount)
- STATUS_APPROVE (status approval: NULL=pending, 1=approved, 0=rejected)
- USER_APPROVE (user yang approve)
- TGL_APPROVE (tanggal approve)
```

### Table: tamp_settle_message
```sql
- KD_SETTLE (foreign key)
- JENIS_SETTLE
- IDPARTNER
- CORE
- DEBIT_ACCOUNT
- DEBIT_NAME
- CREDIT_CORE
- CREDIT_ACCOUNT
- CREDIT_NAME
- AMOUNT
- DESCRIPTION
- REF_NUMBER
```

### Procedures
1. `p_compare_rekap(tanggal)` - Mengambil data rekap untuk pembuatan jurnal
2. `p_generate_settle_jurnal(nama_produk, tanggal)` - Generate jurnal settlement
3. `p_approve_settle_jurnal(kd_settle, tanggal, user, status)` - Approve/reject jurnal

## TEKNOLOGI YANG DIGUNAKAN

### Backend
- **Framework**: CodeIgniter 4
- **Database**: MySQL dengan stored procedures
- **Architecture**: MVC Pattern

### Frontend
- **Framework**: Bootstrap 4
- **DataTables**: Server-side processing
- **JavaScript**: jQuery dengan AJAX
- **Template Engine**: Blade

### Security
- **CSRF Protection**: Automatic token management
- **Session Management**: User authentication
- **SQL Injection Prevention**: Parameter binding

## CSS STYLING

### Custom CSS File
- **Lokasi**: `public/css/settlement/settlement.css`
- **Fitur**: Responsive design, animations, custom card styles

### Component Styling
- **Cards**: Hover effects dengan shadow
- **Buttons**: Gradient backgrounds dengan hover animations
- **Tables**: Custom thead styling dan row hover effects
- **Modals**: Gradient header backgrounds
- **Badges**: Status-based color coding

## RESPONSIVE DESIGN

### Mobile Support
- Responsive table design
- Optimized button sizes untuk touch
- Adaptive card layouts
- Mobile-friendly modal dialogs

### Tablet Support
- Medium screen optimization
- Flexible grid layouts
- Touch-friendly interface elements

## ERROR HANDLING

### Frontend Error Management
- CSRF token refresh otomatis
- AJAX error handling dengan retry mechanism
- User-friendly error messages
- Loading states untuk better UX

### Backend Error Management
- Try-catch blocks untuk database operations
- Detailed error logging
- Graceful error responses
- Validation error handling

## NAVIGATION INTEGRATION

### Menu Structure
```
Settlement
├── Buat Jurnal Settlement
└── Approve Jurnal Settlement
```

### Route Organization
```php
settlement/
├── buat-jurnal/
│   ├── (index)
│   ├── datatable
│   ├── validate
│   └── create
└── approve-jurnal/
    ├── (index)
    ├── datatable
    ├── detail
    ├── process
    └── summary
```

## TESTING CHECKLIST

### Functional Testing
- [ ] Filter tanggal berfungsi
- [ ] Filter file settle berfungsi (buat jurnal)
- [ ] Filter status approval berfungsi (approve jurnal)
- [ ] DataTable pagination dan sorting
- [ ] Create jurnal dengan validasi
- [ ] Approve/reject jurnal
- [ ] Modal detail jurnal
- [ ] Summary statistics

### UI/UX Testing
- [ ] Responsive design di berbagai device
- [ ] Loading states
- [ ] Error message display
- [ ] Success message display
- [ ] Button hover effects
- [ ] Modal animations

### Security Testing
- [ ] CSRF protection
- [ ] SQL injection prevention
- [ ] Session management
- [ ] Input validation
- [ ] Authorization checks

## DEPLOYMENT NOTES

### Prerequisites
1. Database procedures harus sudah dibuat:
   - `p_compare_rekap`
   - `p_generate_settle_jurnal`
   - `p_approve_settle_jurnal`

2. Tables harus sudah ada:
   - `t_settle_produk`
   - `tamp_settle_message`

3. CSS files harus di-deploy:
   - `public/css/settlement/settlement.css`

### Configuration
1. Routes sudah ditambahkan di `app/Config/Routes.php`
2. Navigation menu sudah ditambahkan di `app/Views/layouts/app.blade.php`
3. Permission system integration (optional)

## FUTURE ENHANCEMENTS

### Possible Improvements
1. **Export Functionality**: Export jurnal ke Excel/PDF
2. **Audit Trail**: Log semua perubahan status
3. **Email Notifications**: Notifikasi approval via email
4. **Bulk Operations**: Approve multiple jurnal sekaligus
5. **Advanced Filtering**: Filter berdasarkan amount range
6. **Dashboard Analytics**: Grafik dan statistik lengkap
7. **Real-time Updates**: WebSocket untuk real-time updates
8. **API Integration**: REST API untuk integrasi eksternal
