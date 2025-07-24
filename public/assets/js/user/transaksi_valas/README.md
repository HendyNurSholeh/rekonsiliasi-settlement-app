# Transaksi Valas JavaScript Modules

Struktur JavaScript untuk halaman Transaksi Valas telah direfactor menjadi beberapa modul terpisah untuk meningkatkan maintainability dan readability.

## Struktur File

```
public/assets/js/user/transaksi_valas/
├── utils.js           # Utility functions dan global variables
├── datatables.js      # DataTables configuration dan management
├── nasabah.js         # Customer search dan special rate handling
├── limit.js           # Limit information management
├── kurs.js            # Currency dan exchange rate management
├── form-handler.js    # Form validation dan submission
└── main.js            # Entry point dan initialization
```

## Deskripsi Modul

### 1. utils.js
- **Fungsi**: Utility functions dan global variables
- **Konten**:
  - Global variables: `specialRateCache`, `currentCif`, `table`, `allCurrencies`
  - CSRF token management
  - Currency formatting functions
  - Form reset utilities
  - Toast notification wrapper

### 2. datatables.js
- **Fungsi**: Manajemen DataTables
- **Konten**:
  - Konfigurasi DataTable dengan server-side processing
  - Column definitions dan renderers
  - Table show/hide/reload functions
  - Delete transaction functionality

### 3. nasabah.js
- **Fungsi**: Pencarian dan manajemen data nasabah
- **Konten**:
  - Customer search by account number
  - Special rate handling dan display
  - Rate option change handlers
  - Customer data validation

### 4. limit.js
- **Fungsi**: Manajemen informasi limit transaksi
- **Konten**:
  - Monthly limit fetching
  - Limit display formatting
  - Limit validation functions
  - Available limit calculations

### 5. kurs.js
- **Fungsi**: Manajemen mata uang dan kurs
- **Konten**:
  - Currency fetching dari API
  - Exchange rate filtering berdasarkan transaction type
  - Currency conversion to USD
  - Exchange rate display
  - Currency change event handlers

### 6. form-handler.js
- **Fungsi**: Validasi dan submission form
- **Konten**:
  - Form validation (required fields, documents)
  - FormData building untuk file uploads
  - Submit button loading states
  - Success/error handling
  - Form reset after submission

### 7. main.js
- **Fungsi**: Entry point dan initialization
- **Konten**:
  - Document ready initialization
  - Module coordination
  - Global function exposure untuk HTML onclick
  - UI enhancements

## Konfigurasi

Configuration object dibuat di Blade template:

```javascript
window.transaksiValasConfig = {
    urls: {
        dataTables: "...",
        getNasabah: "...",
        postValas: "...",
        // ... other URLs
    },
    defaultCurrencyId: "..."
};
```

## Benefits

1. **Separation of Concerns**: Setiap modul memiliki tanggung jawab yang jelas
2. **Maintainability**: Lebih mudah untuk maintain dan debug
3. **Reusability**: Functions dapat digunakan kembali di modul lain
4. **Scalability**: Mudah untuk menambah fitur baru
5. **Team Collaboration**: Developer dapat bekerja pada modul yang berbeda
6. **Testing**: Lebih mudah untuk unit testing per modul

## Usage dalam HTML

Functions yang perlu diakses dari HTML onclick masih tersedia:

```html
<button onclick="getNasabah()">Cari Nasabah</button>
<button onclick="submit()">Submit</button>
<button onclick="convertToUsd()">Convert</button>
```

## Load Order

File dimuat dalam urutan berikut di Blade template:

1. Configuration object
2. utils.js
3. datatables.js
4. nasabah.js
5. limit.js
6. kurs.js
7. form-handler.js
8. main.js (initialization)
9. InputMask plugin

## Migration Notes

- Semua functionality tetap sama seperti sebelumnya
- Tidak ada breaking changes dalam API calls
- HTML tetap kompatibel dengan onclick handlers
- CSRF tokens tetap dihandle dengan benar
