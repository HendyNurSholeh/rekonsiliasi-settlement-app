// Utility functions and CSRF helpers
export let csrfName = document.querySelector('#txt_csrfname')?.getAttribute('name');
export let csrfHash = document.querySelector('#txt_csrfname')?.value;

export function updateCsrfToken(newToken) {
    csrfHash = newToken;
    const csrfInput = document.querySelector('#txt_csrfname');
    if (csrfInput) csrfInput.value = newToken;
}

/**
 * Format currency to USD
 * @param {number} amount 
 * @returns {string}
 */
export const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { 
        style: 'currency', 
        currency: 'USD' 
    }).format(amount || 0);
};

/**
 * Calculate remaining amount (sisa)
 * @param {number} nominal 
 * @param {number} totalTransaksi 
 * @returns {number}
 */
export const calculateSisa = (nominal, totalTransaksi) => {
    const nominalValue = parseFloat(nominal) || 0;
    const totalValue = parseFloat(totalTransaksi) || 0;
    return nominalValue - totalValue;
};

/**
 * Get status badge based on remaining amount
 * @param {Object} row - DataTable row data
 * @returns {string} HTML badge
 */
export const getStatusBadge = (row) => {
    if (row.deleted_at != null) {
        return '<span class="badge badge-danger">Dihapus</span>';
    }
    
    const sisa = calculateSisa(row.NOMINAL, row.TOTAL_TRANSAKSI);
    const nominal = parseFloat(row.NOMINAL) || 0;
    
    if (sisa === nominal) {
        return '<span class="badge badge-warning text-white font-weight-bold">Belum Dipakai</span>';
    } else if (sisa > 0) {
        return '<span class="badge badge-info">Terpakai Sebagian</span>';
    } else if (sisa === 0) {
        return '<span class="badge badge-success">Selesai</span>';
    }
    return '<span class="badge badge-secondary">Tidak Diketahui</span>';
};

/**
 * Truncate text with ellipsis
 * @param {string} text 
 * @param {number} maxLength 
 * @returns {string}
 */
export const truncateText = (text, maxLength = 30) => {
    return text && text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
};

/**
 * Parse numeric value from formatted string
 * @param {string} value 
 * @returns {number}
 */
export const parseNumericValue = (value) => {
    return parseFloat(value.replace(/[^0-9.-]+/g, ""));
};

/**
 * Validate file size
 * @param {File} file 
 * @param {number} maxSizeMB 
 * @returns {boolean}
 */
export const validateFileSize = (file, maxSizeMB = 2) => {
    const maxSize = maxSizeMB * 1024 * 1024; // Convert to bytes
    return file.size <= maxSize;
};

/**
 * Show loading state on button
 * @param {jQuery} button 
 * @param {boolean} loading 
 */
export const toggleButtonLoading = (button, loading) => {
    if (loading) {
        button.attr('disabled', true);
        button.html('<i class="fal fa-circle-notch fa-spin"></i> Simpan');
    } else {
        button.attr('disabled', false);
        button.html('<i class="fal fa-save mr-2"></i>Simpan');
    }
};

export function setInvoiceDateLimits() {
    var today = new Date();
    var ninetyDaysAgo = new Date();
    ninetyDaysAgo.setDate(today.getDate() - 90);
    var maxDate = today.toISOString().split('T')[0];
    var minDate = ninetyDaysAgo.toISOString().split('T')[0];
    $('#tanggal_invoice').attr('min', minDate);
    $('#tanggal_invoice').attr('max', maxDate);
}

export function validateInvoiceDate(dateValue) {
    if (!dateValue) return false;
    var invoiceDate = new Date(dateValue);
    var today = new Date();
    var ninetyDaysAgo = new Date();
    ninetyDaysAgo.setDate(today.getDate() - 90);
    today.setHours(23, 59, 59, 999);
    ninetyDaysAgo.setHours(0, 0, 0, 0);
    invoiceDate.setHours(0, 0, 0, 0);
    return invoiceDate >= ninetyDaysAgo && invoiceDate <= today;
}
