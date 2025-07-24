import { csrfName, csrfHash, reloadTable, updateCsrfToken } from './utils.js';
import { getTable } from './datatables.js';

let table;

function initializeFormHandler() {
    table = getTable();
}

function openModal() {
    $('#modal-title').html('Form Tambah Currency Conversion');
    $('#action').val('add');
    $('#id').val(null);
    $('#kode_mata_uang').val(null);
    $('#nama_mata_uang').val(null);
    $('#kurs_buy_tt').val(null);
    $('#kurs_sell_tt').val(null);
    $('#kurs_buy_bn').val(null);
    $('#kurs_sell_bn').val(null);
    $('#is_publish').prop('checked', false);
    $('#form-modal').modal('show');
}

function editData(index) {
    $('#modal-title').html('Form Edit Currency Conversion');
    $('#action').val('edit');
    const data = table.row(index).data();
    $('#id').val(data.ID);
    $('#kode_mata_uang').val(data.KODE_MATA_UANG);
    $('#nama_mata_uang').val(data.NAMA_MATA_UANG);
    $('#kurs_buy_tt').val(data.KURS_BUY_TT ? data.KURS_BUY_TT.replace('.', ',') : '');
    $('#kurs_sell_tt').val(data.KURS_SELL_TT ? data.KURS_SELL_TT.replace('.', ',') : '');
    $('#kurs_buy_bn').val(data.KURS_BUY_BN ? data.KURS_BUY_BN.replace('.', ',') : '');
    $('#kurs_sell_bn').val(data.KURS_SELL_BN ? data.KURS_SELL_BN.replace('.', ',') : '');
    $('#is_publish').prop('checked', data.IS_PUBLISH == 1);
    $('#form-modal').modal('show');
}

function submit() {
    const fields = {
        kode_mata_uang: $('#kode_mata_uang').val(),
        nama_mata_uang: $('#nama_mata_uang').val(),
        kurs_buy_tt: $('#kurs_buy_tt').val(),
        kurs_sell_tt: $('#kurs_sell_tt').val(),
        kurs_buy_bn: $('#kurs_buy_bn').val(),
        kurs_sell_bn: $('#kurs_sell_bn').val(),
    };
    for (const [key, value] of Object.entries(fields)) {
        if (!value) {
            return toastr["warning"](`${key.replace('_', ' ')} tidak boleh kosong.`);
        }
    }
    return hitEndPoint();
}

function hitEndPoint() {
    const urls = window.currencyConversionConfig?.urls || {};
    let endpoint;
    if ($('#action').val() === 'add') {
        endpoint = urls.post;
    } else if ($('#action').val() === 'edit') {
        endpoint = urls.edit;
        if (!$('#id').val()) {
            return toastr["warning"]("Data tidak ditemukan");
        }
    }
    $('#btnSubmit').attr('disabled', true);
    $('#btnSubmit').html('<i class="fal fa-circle-notch fa-spin"></i> Simpan');
    const formData = new FormData();
    formData.append(csrfName, csrfHash);
    formData.append('id', $('#id').val());
    formData.append('kode_mata_uang', $('#kode_mata_uang').val());
    formData.append('nama_mata_uang', $('#nama_mata_uang').val());
    formData.append('kurs_buy_tt', parseFloat($('#kurs_buy_tt').val().replace(/[^0-9,-]+/g, "").replace(',', '.')));
    formData.append('kurs_sell_tt', parseFloat($('#kurs_sell_tt').val().replace(/[^0-9,-]+/g, "").replace(',', '.')));
    formData.append('kurs_buy_bn', parseFloat($('#kurs_buy_bn').val().replace(/[^0-9,-]+/g, "").replace(',', '.')));
    formData.append('kurs_sell_bn', parseFloat($('#kurs_sell_bn').val().replace(/[^0-9,-]+/g, "").replace(',', '.')));
    formData.append('is_publish', $('#is_publish').is(':checked') ? 1 : 0);
    $.ajax({
        url: endpoint,
        type: 'POST',
        dataType: 'json',
        processData: false,
        contentType: false,
        data: formData,
        success: function(result) {
            updateCsrfToken(result.token);
            if (result.status === 200) {
                $('#dt-table').DataTable().ajax.reload();
                $('#form-modal').modal('hide');
                $('#btnSubmit').attr('disabled', false);
                $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                return toastr["success"](result.messages);
            } else {
                $('#dt-table').DataTable().ajax.reload();
                $('#form-modal').modal('hide');
                $('#btnSubmit').attr('disabled', false);
                $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
                return toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) {
            console.log('An error occurred: ' + error);
            $('#form-modal').modal('hide');
            $('#btnSubmit').attr('disabled', false);
            $('#btnSubmit').html('<i class="fal fa-save mr-2"></i>Simpan');
            toastr["error"](error);
        }
    });
}

function deleteData(index) {
    const data = table.row(index).data();
    return toastr.warning(
        "<button type='button' id='confirmationButtonYes' class='btn btn-light mt-2 ml-2'>Hapus Data</button>",
        `Apakah anda yakin ingin menghapus data ${data.KODE_MATA_UANG} ?`, {
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
    const urls = window.currencyConversionConfig?.urls || {};
    $.ajax({
        url: urls.delete,
        type: 'POST',
        dataType: 'json',
        data: {
            [csrfName]: csrfHash,
            id: id,
        },
        success: function(result) {
            updateCsrfToken(result.token);
            if (result.status === 200) {
                $('#dt-table').DataTable().ajax.reload();
                return toastr["success"](result.messages);
            } else {
                $('#dt-table').DataTable().ajax.reload();
                return toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) {
            console.log('An error occurred: ' + error);
            toastr["error"](error);
        }
    });
}

window.openModal = openModal;
window.editData = editData;
window.submit = submit;
window.deleteData = deleteData;

export { initializeFormHandler, openModal, editData, submit, deleteData };
