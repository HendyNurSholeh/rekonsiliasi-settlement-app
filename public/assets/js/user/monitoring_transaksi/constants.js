/**
 * Constants for Monitoring Transaksi module
 */

// Month names in Indonesian
export const MONTH_NAMES = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];

// CSS Classes for badges
export const BADGE_CLASSES = {
    INFO: 'badge fw-bold badge-info',
    SUCCESS: 'badge fw-bold badge-success',
    WARNING: 'badge fw-bold badge-warning',
    PRIMARY: 'badge badge-primary',
    VALAS: 'badge badge-valas',
    UNDERLYING: 'badge fw-bold',
};

// Number formatting options
export const NUMBER_FORMAT_OPTIONS = {
    CURRENCY: {
        style: 'currency',
        currency: 'USD'
    },
    DECIMAL: {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    }
};

// DataTable column indices
export const COLUMN_INDICES = {
    ID: 0,
    NO: 1,
    CIF: 2,
    NAMA_NASABAH: 3,
    TOTAL_NOMINAL: 4,
    NOMINAL_VALAS: 5,
    NOMINAL_UNDERLYING: 6,
    RASIO: 7,
    AKSI: 8
};

// Element selectors
export const SELECTORS = {
    FILTER_BULAN: '#filter-bulan',
    BTN_FILTER: '#btn-filter',
    BTN_CETAK: '#btn-cetak-monitoring',
    LABEL_BULAN: '#label-bulan-aktif',
    CARD_NASABAH: '#card-total-nasabah',
    CARD_UNDERLYING: '#card-total-underlying',
    CARD_NONUNDERLYING: '#card-total-nonunderlying',
    CARD_NOMINAL: '#card-total-nominal',
    TABLE: '#dt-monitoring'
};
