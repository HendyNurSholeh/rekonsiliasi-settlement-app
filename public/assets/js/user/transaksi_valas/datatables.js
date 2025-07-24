/**
 * DataTables configuration and management for Transaksi Valas
 */

/**
 * Initialize DataTable with configuration
 * @returns {Object} DataTable instance
 */
function initTable() {
    return $('#dt-transaction').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        destroy: true,
        ajax: {
            url: window.transaksiValasConfig.urls.dataTables,
            type: "GET",
            data: function(d) {
                d.cif = currentCif;
            },
            dataSrc: 'aaData',
            error: function(xhr, status, error) {
                console.log("An error occurred: " + error);
                showToast("error", error);
            },
        },
        columns: [
            { data: 'ID', className: "d-none" },
            { data: 'no', className: "text-center" },
            { data: 'TGL_TX' },
            { data: 'kode_unit_kerja' },
            { 
                data: 'NAMA_TRANSAKSI',
                render: function(data, type, row) {
                    let nama = data ? data.replace(/^BANK\s+/i, '') : '';
                    let badge = '';
                    if (row.POSISI === 'SELL') {
                        badge = '<span class="badge badge-danger ml-2">'+ nama +'</span>';
                    } else if (row.POSISI === 'BUY') {
                        badge = '<span class="badge badge-success ml-2">'+ nama +'</span>';
                    }
                    return badge;
                }
            },
            { data: 'NO_REK' },
            { 
                data: 'NAMA_NASABAH',
                render: function(data) {
                    return data ? data.charAt(0).toUpperCase() + data.slice(1).toLowerCase() : '';
                }
            },
            { 
                data: 'NOMINAL_TX',
                render: function(data) {
                    return formatCurrency(data);
                }
            },
            {
                data: 'IS_UNDERLYING',
                render: function(data) {
                    if (data == 1) {
                        return '<span class="badge badge-primary">UNDERLYING</span>';
                    } else {
                        return '<span class="badge badge-secondary">NON</span>';
                    }
                },
                className: 'text-center'
            },
            { data: 'aksi', className: "text-center" },
        ]
    });
}

/**
 * Show table with specific CIF
 * @param {string} cif 
 */
function showTableWithCif(cif) {
    currentCif = cif;
    table = initTable();
    $('#table-transaksi-container').show();
    table.ajax.reload();
}

/**
 * Hide the DataTable
 */
function hideTable() {
    if (table) {
        table.clear().draw();
    }
    $('#table-transaksi-container').hide();
}

/**
 * Reload the DataTable
 */
function reloadTable() {
    if (table) {
        table.destroy();
        table = initTable();
    }
}

/**
 * Delete transaction data
 * @param {number} index - Row index
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
 * Hit delete endpoint
 * @param {number} id - Transaction ID
 */
function hitEndPointDelete(id) {
    // Disable all delete buttons to prevent double submit
    $('.btn-delete-transaction').attr('disabled', true);

    $.ajax({
        url: window.transaksiValasConfig.urls.deleteValas,
        type: "POST",
        dataType: "json",
        dataSrc: '',
        data: {
            [csrfName]: csrfHash,
            id: id,
        },
        success: function(result) {
            csrfHash = result.token;
            // Re-enable delete buttons after request
            $('.btn-delete-transaction').attr('disabled', false);
            updateLimitInfo($('#cif').val());
            
            if (result.status === 200) {
                $('#dt-transaction').DataTable().ajax.reload();
                showToast("success", result.messages);
            } else {
                $('#dt-transaction').DataTable().ajax.reload();
                showToast("warning", result.messages);
            }
        },
        error: function(xhr, status, error) {
            // Re-enable delete buttons on error
            $('.btn-delete-transaction').attr('disabled', false);
            console.log("An error occurred: " + error);
            showToast("error", error);
        }
    });
}
