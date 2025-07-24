import { 
    csrfName, 
    csrfHash, 
    updateCsrfToken, 
    setInvoiceDateLimits, 
    validateInvoiceDate,
    parseNumericValue,
    validateFileSize,
    toggleButtonLoading
} from './utils.js';
import { getTable, initTable, reloadTable } from './datatables.js';

let table;

export function initializeFormHandler() {
    table = getTable() || initTable();
    $('#permissions').select2({
        dropdownParent: $("#form-assign"),
        placeholder: " "
    });
    $('#form-modal').on('show.bs.modal', function() {
        toggleFileInput();
    });
}

export function openModal() {
    $('#modal-title').html('Form Tambah Underlying');
    $('#action').val('add');
    $('#id').val(null);
    $('#no_invoice').val(null);
    $('#tanggal_invoice').val(null);
    $('#penerbit').val(null);
    $('#deskripsi').val(null);
    $('#nominal').val(null);
    $('#path_filename').val(null);
    setInvoiceDateLimits();
    $('#form-modal').modal('show');
}

function toggleFileInput() {
    if ($('#action').val() === 'add') {
        $('#path_filename').next('label').html("Choose PDF File");
        $('.modal-view-add').show();
        $('.modal-view-edit').hide();
    } else if ($('#action').val() === 'edit') {
        $('.modal-view-add').hide();
        $('.modal-view-edit').show();
    }
}

export function submit() {
    const fields = {
        no_invoice: $('#no_invoice').val(),
        tanggal_invoice: $('#tanggal_invoice').val(),
        penerbit: $('#penerbit').val(),
        deskripsi: $('#deskripsi').val(),
        nominal_usd: $('#nominal').val()
    };

    // Validate required fields
    for (const [key, value] of Object.entries(fields)) {
        if (!value || (key === 'nominal_usd' && (value === "$0.00" || parseNumericValue(value) <= 0))) {
            return toastr["warning"](`${key.replace('_', ' ')} tidak boleh kosong, nol, atau negatif`);
        }
        if (key === 'nominal_usd' && !/^\d+(\.00)?$/.test(value.replace(/[^0-9.]+/g, ""))) {
            return toastr["warning"](`${key.replace('_', ' ')} harus berupa bilangan bulat dengan format .00`);
        }
    }

    // Validate invoice date
    if (!validateInvoiceDate($('#tanggal_invoice').val())) {
        return toastr["warning"]("Tanggal invoice tidak valid. Maksimal 3 bulan dari hari ini dan tidak boleh future date");
    }

    // Validate file upload for add action
    if ($('#action').val() === 'add') {
        const fileInput = $('#path_filename')[0];
        if (!fileInput.files.length) {
            return toastr["warning"]("File PDF wajib diunggah saat menambah data");
        }
        if (!validateFileSize(fileInput.files[0], 2)) {
            return toastr["warning"]("Ukuran file terlalu besar. Maksimal 2 MB");
        }
    }

    return hitEndPoint();
}

function hitEndPoint() {
    const config = window.underlyingConfig || {};
    let endpoint;
    
    if ($('#action').val() === 'add') {
        endpoint = config.api?.post;
    } else if ($('#action').val() === 'edit') {
        endpoint = config.api?.edit;
        if (!$('#id').val()) {
            return toastr["warning"]("Data tidak ditemukan");
        }
    }

    const $btnSubmit = $('#btnSubmit');
    toggleButtonLoading($btnSubmit, true);

    const formData = new FormData();
    formData.append(csrfName, csrfHash);
    formData.append('id', $('#id').val());
    formData.append('no_invoice', $('#no_invoice').val());
    formData.append('tanggal_invoice', $('#tanggal_invoice').val());
    formData.append('penerbit', $('#penerbit').val());
    formData.append('deskripsi', $('#deskripsi').val());
    formData.append('kode_mata_uang', $('#kode_mata_uang').val());
    formData.append('nominal', parseNumericValue($('#nominal').val().slice(0, -2)));
    
    const fileInput = $('#path_filename')[0];
    if (fileInput.files.length > 0) {
        if (!validateFileSize(fileInput.files[0], 2)) {
            toggleButtonLoading($btnSubmit, false);
            return toastr["warning"]("Ukuran file terlalu besar. Maksimal 2 MB");
        }
        formData.append('path_filename', fileInput.files[0]);
    }

    $.ajax({
        url: endpoint,
        type: "POST",
        dataType: "json",
        processData: false,
        contentType: false,
        data: formData,
        success: function(result) {
            updateCsrfToken(result.token);
            
            $('#dt-underlaying').DataTable().ajax.reload();
            $('#form-modal').modal('hide');
            toggleButtonLoading($btnSubmit, false);

            if (result.status === 200) {
                $('#dt-underlaying_filter input').val($('#no_invoice').val()).trigger('input');
                toastr["success"](result.messages);
            } else {
                toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) {
            console.log("An error occurred: " + error);
            $('#form-modal').modal('hide');
            toggleButtonLoading($btnSubmit, false);
            toastr["error"](error);
        }
    });
}

export function deleteData(index) {
    let table = getTable();
    var data = table.row(index).data();
    return toastr.warning(
        "<button type='button' id='confirmationButtonYes' class='btn btn-light mt-2 ml-2'>Hapus Data</button>",
        `Apakah anda yakin ingin menghapus data (No invoice: ${data.NO_INVOICE})?`, {
            allowHtml: true,
            onShown: function(toast) {
                $("#confirmationButtonYes").click(function() {
                    return hitEndPointDelete(data.ID);
                });
            }
        }
    );
}

function hitEndPointDelete(id) {
    const config = window.underlyingConfig || {};
    $.ajax({
        url: config.api?.delete,
        type: 'POST',
        dataType: 'json',
        data: {
            [csrfName]: csrfHash,
            id: id,
        },
        success: function(result) {
            updateCsrfToken(result.token);
            $('#dt-underlaying').DataTable().ajax.reload();
            
            if (result.status === 200) {
                $('#dt-underlaying_filter input').val(result.data.NO_INVOICE).trigger('input');
                toastr["success"](result.messages);
            } else {
                toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) {
            console.log('An error occurred: ' + error);
            toastr["error"](error);
        }
    });
}

export function assignModal(index) {
    const table = getTable();
    const data = table.row(index).data();
    const config = window.underlyingConfig || {};
    
    $('#id_detail').val(data.id);
    $('#form-assign').modal('show');
    
    $.ajax({
        url: config.api?.permission + data.id,
        type: 'GET',
        dataType: 'json',
        success: function(result) {
            if (result.status === 200) {
                $('#permissions').val(result.data).change();
            } else {
                toastr["warning"](result.messages);
                table.ajax.reload(null, false);
                $('#form-assign').modal('hide');
            }
        },
        error: function(xhr, status, error) {
            console.log('An error occurred: ' + error);
            $('#form-assign').modal('hide');
            toastr["error"](error);
        }
    });
}
