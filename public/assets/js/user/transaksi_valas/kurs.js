/**
 * Currency and exchange rate management for Transaksi Valas
 */

/**
 * Fetch available currencies based on transaction type
 * @param {string} tipeKurs - Type of exchange rate (BN/TT)
 */
function fetchCurrencies(tipeKurs) {
    $.ajax({
        url: window.transaksiValasConfig.urls.currencyOptions,
        type: "POST",
        dataType: "json",
        data: {
            [csrfName]: csrfHash
        },
        success: function(response) {
            if (response.status === 200 && Array.isArray(response.data)) {
                csrfHash = response.token;
                allCurrencies = response.data;
                filterCurrenciesByTipe(tipeKurs);
                $('#kode_mata_uang')
                    .val(window.transaksiValasConfig.defaultCurrencyId)
                    .trigger('change');
            } else {
                showToast("warning", "Failed to fetch currencies");
            }
        },
        error: function(xhr, status, error) {
            showToast("warning", "Failed to fetch currencies");
        }
    });
}

/**
 * Filter currencies by exchange rate type
 * @param {string} tipeKurs - Type of exchange rate
 */
function filterCurrenciesByTipe(tipeKurs) {
    // Save previous value
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
        placeholder: 'Pilih Kode Mata Uang',
        dropdownCssClass: "border-primary rounded",
        containerCssClass: "border-primary rounded"
    });

    // Check if previous value still exists in filtered list
    if (filtered.some(c => c.id == prevValue)) {
        $('#kode_mata_uang').val(prevValue).trigger('change');
    } else {
        // Fallback to USD if not available
        $('#kode_mata_uang').val(window.transaksiValasConfig.defaultCurrencyId).trigger('change');
    }
}

/**
 * Show exchange rate information
 */
function showKursInfo() {
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
        $('#kurs-value').text(formatNumber(kurs));
        $('#kurs-label').text(label);
    } else {
        $('#info-kurs').show();
        $('#kurs-value').text('-');
        $('#kurs-label').text('Kurs tidak ditemukan');
    }
}

/**
 * Convert currency amount to USD
 */
function convertToUsd() {
    var nominalValue = parseFloat($("#nominal_currency").val().replace(/[^0-9.-]+/g, ""));
    var selectedCurrency = $("#kode_mata_uang").val();
    var tipeKurs = $('#jenis_transaksi option:selected').data('tipe-kurs');
    var posisi = $('#jenis_transaksi option:selected').data('posisi');
    var rate_type = 'default';
    var ticket_id = null;
    
    if ($('input[name="rate_option"]:checked').val() === 'special') {
        rate_type = 'special';
        ticket_id = specialRateCache.ticket_id || null;
    }
    
    var ajaxData = { 
        currency_id: selectedCurrency, 
        tipe_kurs: tipeKurs, 
        posisi: posisi, 
        rate_type: rate_type 
    };
    
    console.log("Converting to USD with data:", ajaxData);
    
    if (rate_type === 'special' && ticket_id) {
        ajaxData.ticket_id = ticket_id;
    }
    
    if (!isNaN(nominalValue)) {
        $.ajax({
            url: window.transaksiValasConfig.urls.getCurrencyRateToUsd,
            type: "GET",
            dataType: "json",
            data: ajaxData,
            success: function(response) {
                if (response.status === 200) {
                    var convertedValue = Math.ceil(nominalValue * response.rate);
                    $('#nominal_transaksi').val(formatCurrency(convertedValue));
                } else {
                    showToast("warning", "Failed to fetch conversion rate");
                }
            },
            error: function(xhr, status, error) {
                showToast("error", "Error fetching conversion rate");
            }
        });
    }
}

/**
 * Handle currency change events
 */
function handleCurrencyChange() {
    $('#kode_mata_uang').on('change', function() {
        showKursInfo();
        var selectedCurrency = this.value;
        var selectedCurrencyText = '';
        
        if (this.selectedIndex >= 0 && this.options[this.selectedIndex]) {
            selectedCurrencyText = this.options[this.selectedIndex].text;
        }
        
        if (selectedCurrency && selectedCurrency != window.transaksiValasConfig.defaultCurrencyId) {
            $('.con-nominal').removeClass('d-none');
            $('#nominal_transaksi').val('');
            $('#nominal_transaksi').prop('readonly', true);
            $('.con-nominal .form-label .text-nominal-convert').text('Nominal ' + selectedCurrencyText);
        } else {
            $('.con-nominal').addClass('d-none');
            $('#nominal_transaksi').prop('readonly', false).val('');
        }
    });
}

/**
 * Handle transaction type change events
 */
function handleTransactionTypeChange() {
    $('#jenis_transaksi').on('change', function() {
        let tipeKurs = $('#jenis_transaksi option:selected').data('tipe-kurs');
        filterCurrenciesByTipe(tipeKurs);
        setTimeout(showKursInfo, 200); // Update exchange rate info after filter
    });
}

