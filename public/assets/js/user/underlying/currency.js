import { csrfName, csrfHash, updateCsrfToken } from './utils.js';

let allCurrencies = [];

export function fetchCurrencies(tipeKurs) {
    const config = window.underlyingConfig || {};
    $.ajax({
        url: config.api?.currencyOptions,
        type: 'POST',
        dataType: 'json',
        data: {
            [csrfName]: csrfHash
        },
        success: function(response) {
            if (response.status === 200 && Array.isArray(response.data)) {
                updateCsrfToken(response.token);
                allCurrencies = response.data;
                filterCurrenciesByTipe(tipeKurs);
                $('#kode_mata_uang')
                    .val(config.selectedCurrencyId || config.defaultCurrencyId || '')
                    .trigger('change');
            } else {
                toastr["warning"]("Failed to fetch currencies");
            }
        },
        error: function() {
            toastr["warning"]("Failed to fetch currencies");
        }
    });
}

export function filterCurrenciesByTipe(tipeKurs) {
    let prevValue = $('#kode_mata_uang').val();
    let filtered = [];
    if (tipeKurs === 'BN') {
        filtered = allCurrencies.filter(c => c.kurs_sell_bn !== undefined && parseFloat(c.kurs_sell_bn) > 0);
    } else if (tipeKurs === 'TT') {
        filtered = allCurrencies.filter(c => c.kurs_sell_tt !== undefined && parseFloat(c.kurs_sell_tt) > 0);
    } else {
        filtered = allCurrencies;
    }
    $('#kode_mata_uang').empty().select2({
        data: filtered,
        dropdownParent: $("#form-modal"),
        placeholder: 'Pilih Kode Mata Uang',
        dropdownCssClass: "border-primary rounded",
        containerCssClass: "border-primary rounded"
    });
    if (filtered.some(c => c.id == prevValue)) {
        $('#kode_mata_uang').val(prevValue).trigger('change');
    } else {
        $('#kode_mata_uang').val(window.underlyingConfig?.defaultCurrencyId || '').trigger('change');
    }
}

export function showKursInfo() {
    let tipeKurs = $('#jenis_transaksi option:selected').data('tipe-kurs');
    let posisi = $('#jenis_transaksi option:selected').data('posisi');
    let selectedId = $('#kode_mata_uang').val();
    let currency = allCurrencies.find(c => c.id == selectedId);
    let kurs = null;
    let label = '';
    if (currency) {
        if (tipeKurs === 'BN') {
            if (posisi === 'SELL') {
                kurs = currency.kurs_sell_bn;
                label = 'Kurs Sell BN (Rupiah)';
            } else if (posisi === 'BUY') {
                kurs = currency.kurs_buy_bn;
                label = 'Kurs Buy BN (Rupiah)';
            }
        } else if (tipeKurs === 'TT') {
            if (posisi === 'SELL') {
                kurs = currency.kurs_sell_tt;
                label = 'Kurs Sell TT (Rupiah)';
            } else if (posisi === 'BUY') {
                kurs = currency.kurs_buy_tt;
                label = 'Kurs Buy TT (Rupiah)';
            }
        }
    }
    if (kurs && parseFloat(kurs) > 0) {
        $('#info-kurs').show();
        $('#kurs-value').text(parseFloat(kurs).toLocaleString('id-ID', {minimumFractionDigits: 2}));
        $('#kurs-label').text(label);
    } else {
        $('#info-kurs').show();
        $('#kurs-value').text('-');
        $('#kurs-label').text('Kurs tidak ditemukan');
    }
}
