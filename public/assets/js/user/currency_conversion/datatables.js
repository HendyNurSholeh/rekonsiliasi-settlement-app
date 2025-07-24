import { csrfName, csrfHash, updateCsrfToken } from './utils.js';

let table;

function initializeDataTables() {
    const urls = window.currencyConversionConfig?.urls || {};
    table = $('#dt-table').DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: urls.dataTables,
            type: 'GET',
            dataType: 'json',
            dataSrc: 'data',
            error: function(xhr, status, error) {
                console.log('An error occurred: ' + error);
                toastr["error"](error);
            }
        },
        columns: [
            { data: 'ID', className: 'd-none' },
            { data: 'no', className: 'text-center' },
            { data: 'KODE_MATA_UANG', render: function(data) { return data; } },
            { data: 'NAMA_MATA_UANG' },
            { data: 'KURS_BUY_TT', render: function(data) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data); } },
            { data: 'KURS_SELL_TT', render: function(data) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data); } },
            { data: 'KURS_BUY_BN', render: function(data) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data); } },
            { data: 'KURS_SELL_BN', render: function(data) { return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(data); } },
            { data: 'IS_PUBLISH', className: 'text-center', render: function(data) { return data == 1 ? '<span class="badge badge-success">Tampil</span>' : '<span class="badge badge-secondary">Tidak</span>'; } },
            { data: 'aksi', className: 'text-center' }
        ],
        drawCallback: function() {
            $('.dynamic-tooltip').tooltip();
        }
    });
}

function getTable() {
    return table;
}

export { initializeDataTables, getTable };
