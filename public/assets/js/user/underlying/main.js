import { initTable } from './datatables.js';
import { initializeFormHandler, openModal, submit, deleteData, assignModal } from './form-handler.js';
import { fetchCurrencies, filterCurrenciesByTipe, showKursInfo } from './currency.js';

$(function() {
    initTable();
    initializeFormHandler();
    // Currency select2 and event bindings
    let initialTipeKurs = $('#jenis_transaksi option:selected').data('tipe-kurs');
    fetchCurrencies(initialTipeKurs);
    $('#jenis_transaksi').on('change', function() {
        let tipeKurs = $('#jenis_transaksi option:selected').data('tipe-kurs');
        filterCurrenciesByTipe(tipeKurs);
        setTimeout(showKursInfo, 200);
    });
    $('#kode_mata_uang').on('change', function() {
        showKursInfo();
        var selectedCurrency = $(this).val();
        var selectedCurrencyText = this.options[this.selectedIndex].text;
        if (selectedCurrency != window.underlyingConfig?.defaultCurrencyId) {
            $('.con-nominal').removeClass('d-none');
            $('#nominal').val('');
            $('#nominal').prop('readonly', true);
            $('.con-nominal .form-label').text('Nominal ' + selectedCurrencyText);
        } else {
            $('.con-nominal').addClass('d-none');
            $('#nominal').prop('readonly', false).val('');
        }
    });
});

// Expose for HTML usage
window.openModal = openModal;
window.submit = submit;
window.deleteData = deleteData;
window.assignModal = assignModal;
