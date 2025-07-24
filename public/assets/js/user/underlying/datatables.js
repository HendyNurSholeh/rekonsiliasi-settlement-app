import { formatCurrency, calculateSisa, getStatusBadge, truncateText } from './utils.js';

let table;

export function initTable() {
    const config = window.underlyingConfig || {};
    
    table = $('#dt-underlaying').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: config.api?.dataTable,
            type: 'GET',
            dataType: 'json',
            dataSrc: 'data',
            error: function(xhr, status, error) {
                console.log('An error occurred: ' + error);
                toastr["error"](error);
            }
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
                data: 'NO_INVOICE'
            },
            {
                data: 'PENERBIT'
            },
            {
                data: 'DESKRIPSI',
                render: function(data, type, row) {
                    return truncateText(data, 30);
                }
            },
            {
                data: 'NOMINAL',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            }, 
            {
                data: 'TOTAL_TRANSAKSI',
                render: function(data, type, row) {
                    return formatCurrency(data);
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    const sisa = calculateSisa(row.NOMINAL, row.TOTAL_TRANSAKSI);
                    return formatCurrency(sisa);
                }
            },
            {
                data: 'created_at',
            },
            {
                data: null,
                render: function(data, type, row) {
                    return getStatusBadge(row);
                }
            },
            {
                data: 'aksi',
                className: "text-center"
            },
        ],
        drawCallback: function() {
            $('.dynamic-tooltip').tooltip();
        }
    });

    // Custom search input styling
    $('#dt-underlaying_filter input').on('focus', function() {
        $(this).css({
            'border-width': '1px',
            'box-shadow': '0 0 2px 1px #00bcd4'
        });
    }).on('blur', function() {
        $(this).css({
            'background-color': '',
            'box-shadow': ''
        });
    });
    $('#dt-underlaying_filter input').focus();
    $('#dt-underlaying_filter input').attr('placeholder', 'Search...');

    return table;
}

export function getTable() {
    return table;
}

export function reloadTable() {
    if (table) {
        table.destroy();
        table = initTable();
    }
}
