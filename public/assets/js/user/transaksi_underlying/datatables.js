/**
 * DataTables functionality for Transaksi Underlying
 */

/**
 * Initialize DataTable
 * @returns {DataTable}
 */
function initTable() {
    return table = $('#dt-transaction').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: { 
            url: "/dataTables/transactionAPI",
            type: "GET",
            dataType: "json",   
            data: {
                underlying_id: getUnderlyingId()
            },
            dataSrc: 'data',
            error: function(xhr, status, error) {
                console.log("An error occurred: " + error);
                toastr["error"](error);
            },
        },
        columns: [
            {
                data: 'ID',
                className: "d-none"
            },
            {
                data: 'no',
                className: "text-center"
            },
            {
                data: 'TGL_TX'
            },
            {
                data: 'kode_unit_kerja',
            },
            {
                data: 'NAMA_TRANSAKSI',
                render: function(data, type, row) {
                    // Hapus kata 'BANK ' di depan (case-insensitive)
                    let nama = data.replace(/^BANK\s+/i, '');
                    // Tambahkan badge posisi
                    let badge = '';
                    if (row.POSISI === 'SELL') {
                        badge = '<span class="badge badge-danger ml-2">'+ nama +'</span>';
                    } else if (row.POSISI === 'BUY') {
                        badge = '<span class="badge badge-success ml-2">'+ nama +'</span>';
                    }
                    return badge;
                }
            },
            {
                data: 'NO_REK'
            },
            {
                data: 'NAMA_NASABAH',
                render: function(data, type, row) {
                    return data.charAt(0).toUpperCase() + data.slice(1).toLowerCase();
                }
            },
            {
                data: 'NOMINAL_TX',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'aksi',
                className: "text-center"
            },
        ]
    });
}

/**
 * Reload DataTable
 */
function reloadTable() {
    if (table) {
        table.destroy();
    }
    table = initTable();
}

/**
 * Delete transaction data
 * @param {number} index 
 */
function deleteData(index) {
    var data = table.row(index).data();
    return toastr.warning(
        "<button type='button' id='confirmationButtonYes' class='btn-delete-transaction btn btn-light mt-2 ml-2'>Submit</button>",
        `Apakah anda yakin ingin membatalkan transaksi ini pada tanggal: ${data.TGL_TX}?`, {
            allowHtml: true,
            onShown: function(toast) {
                $("#confirmationButtonYes").click(function() {
                    return hitEndPointDelete(data.ID);
                });
            }
        });
}

/**
 * Execute delete API call
 * @param {string} id 
 */
function hitEndPointDelete(id) {
    // Disable all delete buttons to prevent double submit
    $('.btn-delete-transaction').attr('disabled', true);

    $.ajax({
        url: "/delete/transactionAPI",
        type: "POST",
        dataType: "json",
        dataSrc: '',
        data: {
            [csrfName]: csrfHash,
            id: id,
        },
        success: function(result) {
            csrfHash = result.token;
            console.log(result);
            $('.btn-delete-transaction').attr('disabled', false);

            if (result.status === 200) {
                $('#dt-transaction').DataTable().ajax.reload();
                let nominalChange = result.data.NOMINAL_TX;
                updateTotals(nominalChange, false);
                return toastr["success"](result.messages);
            } else {
                $('#dt-transaction').DataTable().ajax.reload();
                return toastr["warning"](result.messages);
            }
        },
        error: function(xhr, status, error) {
            $('.btn-delete-transaction').attr('disabled', false);
            console.log("An error occurred: " + error);
            toastr["error"](error);
        }
    });
}

/**
 * Handle modal assignments
 * @param {number} index 
 */
function assignModal(index) {
    var data = table.row(index).data();
    $('#id_detail').val(data.id);

    $('#form-assign').modal('show');
    $.ajax({
        url: "/permission/transactionAPI/" + data.id,
        type: "GET",
        dataType: "json",
        dataSrc: '',
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
            console.log("An error occurred: " + error);
            $('#form-assign').modal('hide');
            toastr["error"](error);
        }
    });
}
