/**
 * Form handling and validation for Transaksi Valas
 */

/**
 * Submit transaction form
 */
function submit() {
    if (!validateForm()) {
        return;
    }
    
    var formData = buildFormData();
    setSubmitButtonLoading(true);
    
    $.ajax({
        url: window.transaksiValasConfig.urls.postValas,
        type: "POST",
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(result) {
            handleSubmitSuccess(result);
        },
        error: function(xhr, status, error) {
            handleSubmitError(error);
        }
    });
}

/**
 * Validate form fields
 * @returns {boolean}
 */
function validateForm() {
    var fields = {
        no_rekening: $('#no_rekening').val(),
        nama_nasabah: $('#nama_nasabah').val(),
        nominal_transaksi: $('#nominal_transaksi').val()
    };
    
    // Check required fields
    for (const [key, value] of Object.entries(fields)) {
        if (!value || (key === 'nominal_transaksi' && (value === "$0.00" || parseFloat(value.replace(/[^0-9-]+/g, "")) <= 0))) {
            if (key === 'nama_nasabah') {
                showToast("warning", "Nama nasabah wajib diisi. Silakan cari nasabah dengan menekan tombol pencarian pada No Rekening.");
                return false;
            }
            showToast("warning", `${key.replace('_', ' ')} tidak boleh kosong, nol, atau negatif`);
            return false;
        }
        
        if (key === 'nominal_transaksi' && !/^\d+(\.00)?$/.test(value.replace(/[^0-9.]+/g, ""))) {
            console.log(value.replace(/[^0-9.]+/g, ""));
            showToast("warning", `${key.replace('_', ' ')} harus berupa bilangan bulat dengan format .00`);
            return false;
        }
    }
    
    // Validate mandatory documents
    return validateDocuments();
}

/**
 * Validate document uploads
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
        showToast("warning", docMsg);
        return false;
    }
    
    return true;
}

/**
 * Build FormData for submission
 * @returns {FormData}
 */
function buildFormData() {
    var formData = new FormData();
    
    // Basic form data
    formData.append(csrfName, csrfHash);
    formData.append('no_rekening', $('#form-add #no_rekening').val());
    formData.append('nama_nasabah', $('#form-add #nama_nasabah').val());
    formData.append('id_jenis_transaksi', $('#form-add #jenis_transaksi').val());
    formData.append('nominal_transaksi', $('#form-add #nominal_transaksi').val().replace(/[^0-9.-]+/g, ""));
    formData.append('id_currency_conversion', $('#form-add #kode_mata_uang').val());
    formData.append('underlying_id', window.underlyingId || "");
    formData.append('cif', $('#form-add #cif').val());
    
    // Exchange rate information
    var kursValueRaw = $('#kurs-value').text().replace(/[^0-9.,-]+/g, '');
    var kursValueClean = kursValueRaw.replace(/,(\d{2})$/, '');
    kursValueClean = kursValueClean.replace(/\./g, '');
    formData.append('kurs', kursValueClean);
    console.log("Kurs yang digunakan:", kursValueClean);
    
    // Special rate information
    if ($('input[name="rate_option"]:checked').val() === 'special') {
        formData.append('rate_type', 'special');
        formData.append('ticket_id', specialRateCache.ticket_id || '');
        console.log("Menggunakan special rate:", specialRateCache);
    } else {
        formData.append('rate_type', 'default');
    }
    
    // Document files
    $('.form-control-file[name^="document_file["]').each(function() {
        var match = this.name.match(/document_file\[(\d+)\]/);
        if (match && this.files.length > 0) {
            var docTypeId = match[1];
            formData.append('document_file[' + docTypeId + ']', this.files[0]);
        }
    });
    
    return formData;
}

/**
 * Handle successful form submission
 * @param {Object} result 
 */
function handleSubmitSuccess(result) {
    csrfHash = result.token;
    setSubmitButtonLoading(false);
    
    if (result.status === 200) {
        $('#dt-transaction').DataTable().ajax.reload();
        resetFormAfterSubmit();
        updateLimitInfo($('#cif').val());
        showToast("success", result.messages);
    } else {
        $('#dt-transaction').DataTable().ajax.reload();
        showToast("warning", result.messages);
    }
}

/**
 * Handle form submission error
 * @param {string} error 
 */
function handleSubmitError(error) {
    console.log("An error occurred: " + error);
    setSubmitButtonLoading(false);
    showToast("error", error);
}

/**
 * Reset form after successful submission
 */
function resetFormAfterSubmit() {
    $('#nominal_currency').val('');
    $('#nominal_transaksi').val('');
    $('.form-control-file[name^="document_file["]').val('');
    
    // Reset currency select to default
    $('#kode_mata_uang').val(window.transaksiValasConfig.defaultCurrencyId).trigger('change');
    
    // Hide currency conversion input if USD
    if ($('#kode_mata_uang').val() == window.transaksiValasConfig.defaultCurrencyId) {
        $('.con-nominal').addClass('d-none');
    }
    
    // Reset special rate elements
    $('.is-special-rate').hide();
    $('#special-rate-options').html('');
    $('#special-rate-info').html('');
    $('#jenis_transaksi').prop('disabled', false);
    $('#kode_mata_uang').prop('disabled', false);
}

/**
 * Reset transaction form completely
 */
function resetTransactionForm() {
    hideTable();
    resetAllFields();
    updateLimitInfo(null);
}

/**
 * Initialize form event handlers
 */
function initFormHandlers() {
    // Submit button
    $('#btnSubmit').on('click', submit);
    
    // Reset button
    $('button[type="reset"]').on('click', function() {
        resetTransactionForm();
    });
    
    // Account number input changes
    $('#no_rekening').on('input', function() {
        $('.is-success-getnasabah').hide();
        $('.is-special-rate').hide();
        $('#nama_nasabah').val('');
        $('#cif').val('');
    });
    
    $('#no_rekening').on('change', function() {
        $('.is-success-getnasabah').hide();
        $('#cif').val('');
        $('#nama_nasabah').val('');
        $('#table-transaksi-container').hide();
        updateLimitInfo(null);
    });
    
    // Convert to USD button
    $('#btn-convert-usd').on('click', convertToUsd);
}
