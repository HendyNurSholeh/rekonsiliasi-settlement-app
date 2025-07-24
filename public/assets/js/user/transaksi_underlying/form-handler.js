/**
 * Form handling functionality for Transaksi Underlying
 */

/**
 * Initialize form handlers
 */
function initFormHandlers() {

    // Form detail submission handler
    $("#form-detail").submit(function(event) {
        event.preventDefault();
        handleFormDetailSubmit();
    });

    // Reset button handler
    $('button[type="reset"]').on('click', function() {
        resetTransactionForm();
    });

    // Initialize nasabah handlers
    initNasabahHandlers();
}

/**
 * Main form submission
 */
function submit() {
    if (!validateForm()) {
        return;
    }

    if (!validateExpiredDate()) {
        return;
    }

    if (!validateDocuments()) {
        return;
    }

    submitFormData();
}

/**
 * Validate form fields
 * @returns {boolean}
 */
function validateForm() {
    var fields = {
        no_rekening: $('#no_rekening').val(),
        nama_nasabah: $('#nama_nasabah').val(),
        mata_uang_invoice: $('#kode_mata_uang').val(),
        nominal_transaksi: $('#nominal_transaksi').val()
    };

    for (const [key, value] of Object.entries(fields)) {
        if (!value || (key === 'nominal_transaksi' && (value === "$0.00" || cleanNumericValue(value) <= 0))) {
            if (key === 'nama_nasabah') {
                toastr["warning"]("Nama nasabah wajib diisi. Silakan cari nasabah dengan menekan tombol pencarian pada No Rekening.");
                return false;
            }
            toastr["warning"](`${key.replace('_', ' ')} tidak boleh kosong atau nol`);
            return false;
        }
        if (key === 'nominal_transaksi' && !/^\d+(\.00)?$/.test(value.replace(/[^0-9.]+/g, ""))) {
            toastr["warning"](`${key.replace('_', ' ')} harus berupa bilangan bulat dengan format .00`);
            return false;
        }
    }
    return true;
}

/**
 * Validate expired date
 * @returns {boolean}
 */
function validateExpiredDate() {
    var expiredDate = getUnderlyingExpiredDate();
    var trxDate = new Date().toISOString().slice(0, 10);
    
    if (trxDate > expiredDate) {
        toastr["warning"]("Tanggal transaksi melebihi expired date underlying (" + (expiredDate ? expiredDate : '-') + "). Transaksi tidak dapat diproses.");
        return false;
    }
    return true;
}

/**
 * Validate mandatory documents
 * @returns {boolean}
 */
function validateDocuments() {
    let allDocsValid = true;
    let docMsg = '';
    
    $('.form-control-file[data-is-mandatory="1"]').each(function() {
        if (!this.files || this.files.length === 0) {
            allDocsValid = false;
            let label = $(this).closest('.card-body').find('label').text().trim();
            docMsg = `Dokumen wajib "${label}" belum diupload.`;
            return false;
        }
        if (this.files.length > 0 && this.files[0].type !== "application/pdf") {
            allDocsValid = false;
            let label = $(this).closest('.card-body').find('label').text().trim();
            docMsg = `File dokumen "${label}" harus berupa PDF.`;
            return false;
        }
    });
    
    if (!allDocsValid) {
        toastr["warning"](docMsg);
        return false;
    }
    return true;
}

/**
 * Submit form data
 */
function submitFormData() {
    var formData = new FormData();
    
    // Basic form data
    formData.append(csrfName, csrfHash);
    formData.append('no_rekening', $('#form-add #no_rekening').val());
    formData.append('nama_nasabah', $('#form-add #nama_nasabah').val());
    formData.append('id_jenis_transaksi', $('#form-add #jenis_transaksi').val());
    formData.append('nominal_transaksi', cleanNumericValue($('#form-add #nominal_transaksi').val()));
    formData.append('id_currency_conversion', $('#form-add #kode_mata_uang').val());
    formData.append('underlying_id', getUnderlyingId());
    formData.append('cif', $('#form-add #cif').val());
    
    // Add exchange rate info
    var kursValueRaw = $('#kurs-value').text().replace(/[^0-9.,-]+/g, '');
    var kursValueClean = kursValueRaw.replace(/,(\d{2})$/, '');
    kursValueClean = kursValueClean.replace(/\./g, '');
    formData.append('kurs', kursValueClean);
    console.log("Kurs yang digunakan:", kursValueClean);
    
    // Add special rate info if selected
    if ($('input[name="rate_option"]:checked').val() === 'special') {
        formData.append('rate_type', 'special');
        formData.append('ticket_id', specialRateCache.ticket_id || '');
        console.log("Menggunakan special rate:", specialRateCache);
    } else {
        formData.append('rate_type', 'default');
    }

    // Add documents
    $('.form-control-file[name^="document_file["]').each(function() {
        var match = this.name.match(/document_file\[(\d+)\]/);
        if (match && this.files.length > 0) {
            var docTypeId = match[1];
            formData.append('document_file[' + docTypeId + ']', this.files[0]);
        }
    });

    setSubmitButtonLoading(true);

    $.ajax({
        url: "/post/transactionAPI",
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(result) {
            handleSubmitSuccess(result);
        },
        error: function(xhr, status, error) {
            handleSubmitError(xhr, status, error);
        }
    });
}

/**
 * Handle successful form submission
 * @param {object} result 
 */
function handleSubmitSuccess(result) {
    csrfHash = result.token;
    setSubmitButtonLoading(false);
    
    if (result.status === 200) {
        $('#dt-transaction').DataTable().ajax.reload();
        $('#form-modal').modal('hide');

        let nominalChange = cleanNumericValue($('#nominal_transaksi').val());
        updateTotals(nominalChange, true);

        resetTransactionForm();
        toastr["success"](result.messages);
    } else {
        $('#dt-transaction').DataTable().ajax.reload();
        $('#form-modal').modal('hide');
        toastr["warning"](result.messages);
    }
}

/**
 * Handle form submission error
 * @param {object} xhr 
 * @param {string} status 
 * @param {string} error 
 */
function handleSubmitError(xhr, status, error) {
    console.log("An error occurred: " + error);
    $('#form-modal').modal('hide');
    setSubmitButtonLoading(false);
    toastr["error"](error);
}

/**
 * Handle form detail submission
 */
function handleFormDetailSubmit() {
    var formData = {
        _method: $('#method_detail').val(),
        id: $('#id_detail').val(),
        permissions: $('#permissions').val(),
    };
    formData[csrfName] = csrfHash;

    $.ajax({
        url: "/assignPermission/transactionAPI",
        type: "POST",
        dataType: "json",
        data: formData,
        success: function(result) {
            csrfHash = result.token;
            if (result.status === 200) {
                toastr["success"](result.body);
            } else {
                toastr["warning"](result.messages);
            }
            table.ajax.reload(null, false);
            $('#form-assign').modal('hide');
        },
        error: function(error) {
            console.log("An error occurred: " + error);
            $('#form-assign').modal('hide');
            toastr["error"](error);
        }
    });
}

/**
 * Reset transaction form
 */
function resetTransactionForm() {
    // Reset input fields
    $('.is-success-getnasabah').hide();
    $('#no_rekening').val('');
    $('#nama_nasabah').val('');
    $('#nominal_currency').val('');
    $('#nominal_transaksi').val('');
    $('.is-special-rate').hide();
    $('#special-rate-options').html('');
    $('#special-rate-info').html('');
    $('#jenis_transaksi').prop('disabled', false);
    $('#kode_mata_uang').prop('disabled', false);
    $('.form-control-file[name^="document_file["]').val('');
    
    // Reset mata uang select2 ke default (trigger change for UI update)
    $('#kode_mata_uang').val(getDefaultCurrencyId()).trigger('change');
    
    // Hide currency conversion input if USD
    if ($('#kode_mata_uang').val() == getUsdCurrencyId()) {
        $('.con-nominal').addClass('d-none');
    }
}

/**
 * Open modal for adding new transaction
 */
function openModal() {
    $('#modal-title').html('Form Tambah Transaction');
    $('#action').val('add');
    $('#id').val(null);
    $('#key').val(null);
    $('#name').val(null);
    $('#form-modal').modal('show');
}
