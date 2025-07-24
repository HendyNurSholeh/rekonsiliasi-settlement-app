/**
 * Utility functions for Transaksi Valas
 */

// Global variables
let specialRateCache = {};
let currentCif = null;
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
 * Reset all form fields
 */
function resetAllFields() {
    $('#no_rekening').val('');
    $('#nama_nasabah').val('');
    $('#cif').val('');
    $('#nominal_currency').val('');
    $('#nominal_transaksi').val('');
    $('.form-control-file[name^="document_file["]').val('');
    
    // Reset special rate elements
    $('.is-success-getnasabah').hide();
    $('.is-special-rate').hide();
    $('#special-rate-options').html('');
    $('#special-rate-info').html('');
    
    // Re-enable disabled fields
    $('#jenis_transaksi').prop('disabled', false);
    $('#kode_mata_uang').prop('disabled', false);
    $('#nominal_currency').prop('readonly', false);
    $('#nominal_transaksi').prop('readonly', false);
    
    // Reset mata uang select2 ke default
    $('#kode_mata_uang').val(window.transaksiValasConfig.defaultCurrencyId).trigger('change');
    
    // Hide currency conversion input if USD
    if ($('#kode_mata_uang').val() == window.transaksiValasConfig.defaultCurrencyId) {
        $('.con-nominal').addClass('d-none');
    }
    
    // Clear cache
    specialRateCache = {};
}

/**
 * Show toast notification
 * @param {string} type - success, warning, error
 * @param {string} message 
 */
function showToast(type, message) {
    toastr[type](message);
}
