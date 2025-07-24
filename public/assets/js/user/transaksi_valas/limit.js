/**
 * Limit information management for Transaksi Valas
 */

/**
 * Update limit information for a specific CIF
 * @param {string} cif - Customer CIF
 */
function updateLimitInfo(cif) {
    if (!cif) {
        $('#info-limit-cif').hide();
        return;
    }
    
    $.get(window.transaksiValasConfig.urls.monthlyTotal, { cif: cif }, function(res) {
        console.log("Limit Info Response:", res);
        if (res.status === 200) {
            displayLimitInfo(res);
            $('#info-limit-cif').show();
        } else {
            $('#info-limit-cif').hide();
        }
    }).fail(function() {
        $('#info-limit-cif').hide();
    });
}

/**
 * Display limit information in the UI
 * @param {Object} data - Limit data from API
 */
function displayLimitInfo(data) {
    console.log("Limit Info Data:", data);
    $('#total-jual-non-underlying').text('$' + parseFloat(data.jual_non_underlying).toLocaleString('en-US', {minimumFractionDigits:2}));
    $('#sisa-limit-jual-non-underlying').text('$' + parseFloat(data.sisa_jual_non_underlying).toLocaleString('en-US', {minimumFractionDigits:2}));
    $('#total-jual-underlying').text('$' + parseFloat(data.jual_underlying).toLocaleString('en-US', {minimumFractionDigits:2}));
    $('#total-beli').text('$' + parseFloat(data.beli).toLocaleString('en-US', {minimumFractionDigits:2}));
}

/**
 * Check if transaction amount exceeds limit
 * @param {number} amount - Transaction amount in USD
 * @param {number} availableLimit - Available limit
 * @returns {boolean}
 */
function checkLimitExceeded(amount, availableLimit) {
    return amount > availableLimit;
}

/**
 * Get available limit for non-underlying transactions
 * @returns {number}
 */
function getAvailableNonUnderlyingLimit() {
    const limitText = $('#sisa-limit-jual-non-underlying').text();
    const limitValue = parseFloat(limitText.replace(/[$,]/g, ''));
    return isNaN(limitValue) ? 0 : limitValue;
}

/**
 * Validate transaction against limits
 * @param {number} transactionAmount - Amount in USD
 * @returns {Object} Validation result
 */
function validateTransactionLimit(transactionAmount) {
    const availableLimit = getAvailableNonUnderlyingLimit();
    const isExceeded = checkLimitExceeded(transactionAmount, availableLimit);
    
    return {
        isValid: !isExceeded,
        availableLimit: availableLimit,
        message: isExceeded ? 
            `Transaksi melebihi sisa limit. Sisa limit: ${formatCurrency(availableLimit)}` : 
            'Transaksi dalam batas limit yang diizinkan'
    };
}
