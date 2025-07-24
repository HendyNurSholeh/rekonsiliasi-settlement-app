/**
 * Main entry point for Transaksi Valas JavaScript modules
 */

$(document).ready(function() {
    // Initialize CSRF tokens
    initCSRF();
    
    // Hide elements on page load
    $('.is-success-getnasabah').hide();
    $('#table-transaksi-container').hide();
    
    // Initialize form handlers
    initFormHandlers();
    
    // Initialize currency handlers
    handleCurrencyChange();
    handleTransactionTypeChange();
    
    // Initialize nasabah handlers
    handleRateOptionChange();
    
    // Initial currency load
    let initialTipeKurs = $('#jenis_transaksi option:selected').data('tipe-kurs');
    fetchCurrencies(initialTipeKurs);
    
    // Make the form visually cohesive
    $('#transaction-form .form-control, #transaction-form .form-control-file, #transaction-form select').addClass('rounded');
    $('#transaction-form .input-group').addClass('shadow-sm');
    $('#transaction-form .form-row').css('margin-bottom', '0.5rem');
    
    console.log('Transaksi Valas modules initialized successfully');
});

// Global functions that need to be accessible from HTML onclick attributes
window.getNasabah = getNasabah;
window.submit = submit;
window.convertToUsd = convertToUsd;
window.deleteData = deleteData;
window.reloadTable = reloadTable;
