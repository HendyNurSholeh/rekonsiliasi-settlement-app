/**
 * Nasabah (Customer) functionality for Transaksi Underlying
 */

/**
 * Initialize nasabah input handlers
 */
function initNasabahHandlers() {
    $('#no_rekening').on('input', function() {
        $('.is-success-getnasabah').hide();
        $('#nama_nasabah').val('');
        $('#cif').val('');
        $('.is-special-rate').hide();
    });
}

/**
 * Get nasabah data by account number
 */
function getNasabah() {
    var noRek = $('#no_rekening').val();
    if (!noRek) {
        $('.is-success-getnasabah').hide();
        $('#nama_nasabah').val('');
        $('.is-special-rate').hide();
        $('#cif').val('');
        return toastr["warning"]("No Rekening tidak boleh kosong");
    }
    
    $.ajax({
        url: "/getNasabah",
        type: "POST",
        dataType: "json",
        data: {
            no_rek: noRek,
            [csrfName]: csrfHash
        },
        success: function(result) {
            csrfHash = result.token;
            if (result.status === 200) {
                $('#nama_nasabah').val(result.data.nama_nasabah);
                $('#cif').val(result.data.cif || '');
                $('.is-success-getnasabah').show();
                
                if (result.data.special_rate) {
                    handleSpecialRateFound(result.data.special_rate);
                } else {
                    handleNoSpecialRate();
                }
                
                toastr["success"]("Data nasabah ditemukan.");
            } else {
                handleNasabahNotFound();
                toastr["warning"](result.messages);
                toastr["warning"]("Data nasabah tidak ditemukan. Periksa kembali nomor rekening yang dimasukkan.");
            }
        },
        error: function(xhr, status, error) {
            console.log("An error occurred: " + error);
            toastr["error"](error);
            handleNasabahNotFound();
        }
    });
}

/**
 * Handle when special rate is found
 * @param {object} specialRate 
 */
function handleSpecialRateFound(specialRate) {
    console.log("Special rate ditemukan:", specialRate);
    specialRateCache.rate = specialRate.rate;
    specialRateCache.ticket_id = specialRate.ticket_id || '';
    specialRateCache.mata_uang = specialRate.mata_uang;
    specialRateCache.mata_uang_id = specialRate.mata_uang_id || '';
    specialRateCache.amount = specialRate.amount;
    specialRateCache.kurs = specialRate.kurs || '';
    specialRateCache.jenis_transaksi_id = specialRate.jenis_transaksi_id || '';
    
    $('.is-special-rate').show();
    
    let html = `<div class='form-check'>
        <input class='form-check-input' type='radio' name='rate_option' id='rate_default' value='default' checked>
        <label class='form-check-label' for='rate_default'>Kurs Default</label>
    </div>`;
    html += `<div class='form-check'>
        <input class='form-check-input' type='radio' name='rate_option' id='rate_special' value='special'>
        <label class='form-check-label' for='rate_special'>
            <span class='badge badge-warning p-2' style='font-size:1em;'>
                <i class='fal fa-comments mr-1'></i> Special Rate Hasil Negosiasi Chat
            </span>
       </label>
    </div>`;
    
    $('#special-rate-options').html(html);
    $('#special-rate-info').html(`
        <div class='alert alert-info border border-warning shadow-sm mt-2 w-100' style='background: #fffbe6; min-width:100%; width:100%; display:block;'>
            <div class='d-flex align-items-center mb-2'>
                <i class='fal fa-info-circle fa-lg text-warning mr-2'></i>
                <span class='font-weight-bold text-warning'>Special Rate ini adalah hasil negosiasi Anda melalui halaman chat tiket transaksi. Jika Anda memilih opsi ini, kurs dan nominal akan otomatis mengikuti hasil negosiasi.</span>
            </div>
            <div class='pl-4'>
                <span class='text-dark'>Nominal: <b>${formatNumber(specialRate.amount)}</b> ${specialRate.mata_uang}</span><br>
                <span class='text-dark'>Kurs: <b>${formatNumber(specialRate.rate)}</b></span>
            </div>
        </div>
    `);
}

/**
 * Handle when no special rate is found
 */
function handleNoSpecialRate() {
    specialRateCache = {};
    $('.is-special-rate').show();
    $('#special-rate-options').html('');
    $('#special-rate-info').html(`
        <div class='alert alert-info border border-secondary shadow-sm mt-2 w-100' style='background: #f8f9fa; min-width:100%; width:100%; display:block;'>
            <div class='d-flex align-items-center mb-2'>
                <i class='fal fa-info-circle fa-lg text-secondary mr-2'></i>
                <span class='font-weight-bold text-secondary'>CIF ini tidak memiliki special rate. Transaksi akan menggunakan kurs default.</span>
            </div>
        </div>
    `);
}

/**
 * Handle when nasabah is not found
 */
function handleNasabahNotFound() {
    $('#nama_nasabah').val('');
    $('#cif').val('');
    $('.is-success-getnasabah').hide();
    $('.is-special-rate').hide();
    $('#special-rate-options').html('');
    $('#special-rate-info').html('');
}

/**
 * Handle rate option change
 */
function handleRateOptionChange() {
    $(document).on('change', 'input[name="rate_option"]', function() {
        if ($(this).val() === 'special') {
            handleSpecialRateSelected();
        } else {
            handleDefaultRateSelected();
        }
    });
}

/**
 * Handle when special rate is selected
 */
function handleSpecialRateSelected() {
    let special = specialRateCache;
    if (!special || !special.amount) return;
    
    setTimeout(function() {
        $('#jenis_transaksi').val(special.jenis_transaksi_id).prop('disabled', true).trigger('change');
        $('#kode_mata_uang').val(special.mata_uang_id).prop('disabled', true).trigger('change');
        $('#nominal_transaksi').val(nominalUSD ? formatCurrency(nominalUSD) : '').prop('readonly', true);
        $('#special-rate-info').html(`
            <div class='alert alert-success border border-success shadow-sm'>
                <b><i class='fal fa-star mr-1'></i>Special Rate Aktif!</b><br>
                Transaksi akan menggunakan kurs <b>${formatNumber(special.rate)}</b> (${special.mata_uang}) untuk jumlah <b>${formatNumber(special.amount)}</b>. 
                Semua field terkait dikunci otomatis.
            </div>
        `);
    }, 200);
    
    $('#nominal_currency').val(special.amount).prop('readonly', true);
    
    let nominalUSD = 0;
    if (special.mata_uang === 'USD') {
        nominalUSD = parseFloat(special.amount);
    } else if (special.rate_to_usd) {
        nominalUSD = parseFloat(special.amount) * parseFloat(special.rate_to_usd);
    } else if (special.rate && special.kurs_usd_to_idr) {
        nominalUSD = (parseFloat(special.amount) * parseFloat(special.rate)) / parseFloat(special.kurs_usd_to_idr);
    }
    
    setTimeout(function() {
        convertToUsd()
    }, 200);
    
    setTimeout(function() {
        $('#kurs-value').text(formatNumber(special.rate));
    }, 500);
}

/**
 * Handle when default rate is selected
 */
function handleDefaultRateSelected() {
    $('#jenis_transaksi').prop('disabled', false);
    $('#kode_mata_uang').prop('disabled', false);
    $('#nominal_currency').prop('readonly', false).val('');
    $('#nominal_transaksi').prop('readonly', true).val('');
    $('#special-rate-info').html('');
    showKursInfo();
}
