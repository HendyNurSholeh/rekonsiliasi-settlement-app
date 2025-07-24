/**
 * Utility functions for Transaksi Underlying
 */

// Global variables
let specialRateCache = {};
let table = null;
let allCurrencies = [];

// CSRF variables
var csrfName = null;
var csrfHash = null;

/**
 * Initialize CSRF tokens
 */
function initCSRF() {
    csrfName = $('#txt_csrfname').attr('name');
    csrfHash = $('#txt_csrfname').val();
}

/**
 * Format currency to USD display
 * @param {number} amount 
 * @returns {string}
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', { 
        style: 'currency', 
        currency: 'USD' 
    }).format(amount);
}

/**
 * Format number with Indonesian locale
 * @param {number} value 
 * @returns {string}
 */
function formatNumber(value) {
    return parseFloat(value).toLocaleString('id-ID', {minimumFractionDigits: 2});
}

/**
 * Show/hide loading state on submit button
 * @param {boolean} loading 
 */
function setSubmitButtonLoading(loading) {
    const btn = $('#btnSubmit');
    if (loading) {
        btn.attr('disabled', true);
        btn.html('<i class="fal fa-circle-notch fa-spin"></i> Simpan Transaksi');
    } else {
        btn.attr('disabled', false);
        btn.html('<i class="fal fa-save mr-2"></i> Simpan Transaksi');
    }
}

/**
 * Clean numeric value from formatted string
 * @param {string} value 
 * @returns {number}
 */
function cleanNumericValue(value) {
    return parseFloat(value.replace(/[^0-9.-]+/g, "")) || 0;
}

/**
 * Update invoice totals display
 * @param {number} nominalChange 
 * @param {boolean} isAddition 
 */
function updateTotals(nominalChange, isAddition = true) {
    // Get current values from the HTML (from <div> content, not input value)
    let totalTransaksi = cleanNumericValue($('#total_transaksi').text());
    let sisaInvoice = cleanNumericValue($('#sisa_invoice').text());

    if (isAddition) {
        totalTransaksi += nominalChange;
        sisaInvoice -= nominalChange;
    } else {
        totalTransaksi -= nominalChange;
        sisaInvoice += nominalChange;
    }

    $('#total_transaksi').text("$" + totalTransaksi.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
    $('#sisa_invoice').text("$" + sisaInvoice.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
}

/**
 * Get underlying ID from current page context
 * @returns {string}
 */
function getUnderlyingId() {
    return window.underlyingId || "{{$underlying_id}}";
}

/**
 * Get underlying expired date
 * @returns {string}
 */
function getUnderlyingExpiredDate() {
    return window.underlyingExpiredDate || "{{ $underlying->EXPIRED_DATE }}";
}

/**
 * Get default currency ID
 * @returns {string}
 */
function getDefaultCurrencyId() {
    return window.defaultCurrencyId || "{{ $underlying['CURRENCY_CONVERSION_ID'] ?? env('USD_ID') }}";
}

/**
 * Get USD currency ID
 * @returns {string}
 */
function getUsdCurrencyId() {
    return window.usdCurrencyId || "{{ env('USD_ID') }}";
}
