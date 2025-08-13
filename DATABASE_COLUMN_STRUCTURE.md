# Database Column Structure - t_agn_detail

## Overview
Dokumentasi ini berisi struktur kolom lengkap tabel `t_agn_detail` berdasarkan informasi database yang akurat.

## Struktur Kolom t_agn_detail

### Financial Columns
| Column Name | Description | Example Value |
|-------------|-------------|---------------|
| `RP_BILLER_POKOK` | Nominal pokok tagihan | 10000 |
| `RP_BILLER_DENDA` | Nominal denda | 700 |
| `RP_BILLER_LAIN` | Nominal lain-lain | 0 |
| `RP_BILLER_POTONGAN` | Nominal potongan | 0 |
| `RP_BILLER_TAG` | **Total tagihan biller** | 10700 |
| `RP_FEE_APP` | Fee aplikasi | 0 |
| `RP_FEE_PARTNER` | Fee partner | 0 |
| `RP_FEE_BILLER` | Fee biller | 0 |
| `RP_FEE_AGREGATOR` | Fee agregator | 0 |
| `RP_FEE_USER` | Fee user | 0 |
| `RP_FEE_STRUK` | Fee struk | 0 |
| `RP_AMOUNT_STRUK` | Amount struk | 10700 |
| `RP_AMOUNT` | Total amount | 10700 |

### Transaction Identification
| Column Name | Description | Example Value |
|-------------|-------------|---------------|
| `IDTRX` | ID transaksi | 304607 |
| `AGN_REF` | Reference agen | 0D07678021BC05677CD7F5ADB4CCCBB4 |
| `CLIENT_REF` | Reference client | D2D47FDF2136E6217110C7C7FF558806 |
| `CLIENT_STAN` | STAN client | 000000000001 |
| `CLIENT_IDTRX` | ID transaksi client | 0 |
| `REFF_BKS` | Reference BKS | D2D47FDF2136E6217110C7C7FF558806 |

### Customer & Product Info
| Column Name | Description | Example Value |
|-------------|-------------|---------------|
| `IDPARTNER` | ID partner | PPOB KON |
| `PRODUK` | Nama produk | 88010 - PBB - KABUPATEN TABALONG - NONA |
| `MERCHANT` | Merchant | 6012 |
| `IDPEL` | ID pelanggan | 6309070006002007402025 |
| `SUB_IDPEL` | Sub ID pelanggan | 59 |
| `IDPRODUK` | ID produk | (empty) |
| `TERMINALID` | Terminal ID | (value varies) |
| `v_GROUP_PRODUK` | Group produk | PBB TABALONG |

### Transaction Details
| Column Name | Description | Example Value |
|-------------|-------------|---------------|
| `BLTH` | Bulan tahun | 202507 |
| `TGL_WAKTU` | Tanggal waktu transaksi | 2025-07-21 07:25:32 |
| `BLTH_TAGIHAN` | Bulan tahun tagihan | 2025 |
| `STATUS` | Status transaksi | 1 |
| `KETERANGAN` | Keterangan | PPOB |
| `LEMBAR` | Jumlah lembar | 1 |

### System & Processing Fields
| Column Name | Description | Example Value |
|-------------|-------------|---------------|
| `SOURCE_DB` | Source database | (value varies) |
| `OWNER` | Owner | CABANG TANJUNG |
| `OUTLET` | Outlet | (value varies) |
| `KODE_USER` | Kode user | 00052D |
| `USER` | User name | DEVIE ANGGRAENI |
| `v_TGL_PROSES` | Tanggal proses | 2025-08-12 |
| `v_TGL_FILE_REKON` | **Tanggal file rekonsiliasi** | 2025-07-21 |

### Settlement & Core System Fields
| Column Name | Description | Values | Note |
|-------------|-------------|--------|------|
| `v_REK_SUMBER` | Rekening sumber | 0 | |
| `v_TYPE_CORE` | Type core | 0 | |
| `v_IS_DIRECT_JURNAL` | Direct jurnal flag | 0 | |
| `v_IS_DIRECT_FEE` | Direct fee flag | 0 | |
| `v_IS_FILE_SETTLE` | File settle type | 0, 1, 2 | 0=Default, 1=Pajak, 2=Edu |
| `v_STAT_CORE_AGR` | Status core agreement | 0, 1, 2 | 0=direct jurnal (tidak ada di mgate), 1=... |
| `v_CORE_RP_TAG` | Core RP tag | 0 | |
| `v_CORE_RP_FEE` | Core RP fee | 0 | |
| `v_SETTLE_VERIFIKASI` | Settlement verifikasi | 0, 1, 2 | 0=belum verifikasi, 1=tersedia dan... |
| `v_SETTLE_RP_TAG` | Settlement RP tag | 10700 | |
| `v_SETTLE_RP_FEE` | Settlement RP fee | 0 | |

## Key Fields for Laporan Transaksi Detail

### Primary Filter
- `v_TGL_FILE_REKON` - **Filter utama berdasarkan tanggal rekonsiliasi**

### Display Columns
- `IDPARTNER` - Partner
- `TERMINALID` - Terminal
- `v_GROUP_PRODUK` - Produk (group)
- `IDPEL` - ID Pelanggan
- `RP_BILLER_TAG` - **Tagihan (langsung dari kolom, tidak perlu alias)**
- `STATUS` - Status Biller
- `v_STAT_CORE_AGR` - Status Core
- `v_SETTLE_VERIFIKASI` - Settlement Verifikasi
- `AGN_REF` - Reference ID

### Filter Options
- `STATUS` - Status biller filter
- `v_STAT_CORE_AGR` - Status core filter
- `v_SETTLE_VERIFIKASI` - Settlement verifikasi filter
- `IDPEL` - ID pelanggan search (LIKE)

## Correction Applied

### Before (Incorrect)
```sql
v_SETTLE_RP_TAG AS RP_BILLER_TAG
```

### After (Correct)
```sql
RP_BILLER_TAG
```

**Reasoning**: Kolom `RP_BILLER_TAG` sudah ada langsung di tabel `t_agn_detail`, tidak perlu menggunakan `v_SETTLE_RP_TAG` atau alias.

## Sample Data Structure
```
IDTRX: 304607
IDPARTNER: PPOB KON
TERMINALID: (varies)
v_GROUP_PRODUK: PBB TABALONG
IDPEL: 6309070006002007402025
RP_BILLER_TAG: 10700
STATUS: 1
v_STAT_CORE_AGR: 2
v_SETTLE_VERIFIKASI: 0
AGN_REF: 0D07678021BC05677CD7F5ADB4CCCBB4
v_TGL_FILE_REKON: 2025-07-21
```

## Updated Query Structure

### Final Query
```sql
SELECT IDPARTNER, TERMINALID, v_GROUP_PRODUK AS PRODUK, IDPEL, 
       RP_BILLER_TAG, STATUS AS STATUS_BILLER, v_STAT_CORE_AGR AS STATUS_CORE, 
       v_SETTLE_VERIFIKASI, AGN_REF AS v_ID
FROM t_agn_detail 
WHERE v_TGL_FILE_REKON = ?
```

### Search Fields
```sql
IDPARTNER LIKE ? OR 
TERMINALID LIKE ? OR 
v_GROUP_PRODUK LIKE ? OR 
IDPEL LIKE ? OR 
CAST(RP_BILLER_TAG AS CHAR) LIKE ? OR
CAST(v_SETTLE_VERIFIKASI AS CHAR) LIKE ?
```

## Column Mapping
```php
$columns = [
    0 => 'AGN_REF',
    1 => 'IDPARTNER',
    2 => 'TERMINALID', 
    3 => 'v_GROUP_PRODUK',
    4 => 'IDPEL',
    5 => 'RP_BILLER_TAG',        // Direct column, no alias needed
    6 => 'STATUS',
    7 => 'v_STAT_CORE_AGR',
    8 => 'v_SETTLE_VERIFIKASI',
    9 => 'AGN_REF'
];
```

---

**Note**: Struktur ini berdasarkan informasi database aktual yang disediakan, memastikan query menggunakan kolom yang benar-benar ada di tabel `t_agn_detail`.
