/**
 * DataTable functionality for Monitoring Transaksi
 */
import { formatCurrency, calculatePercentage, safeParseInt } from './utils.js';
import { SELECTORS, BADGE_CLASSES } from './constants.js';

let table;

/**
 * Initialize DataTable
 * @returns {DataTable}
 */
export const initTable = () => {
    const config = window.monitoringConfig || {};
    
    table = $(SELECTORS.TABLE).DataTable({
        responsive: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: config.api?.dataTable,
            type: "GET",
            dataType: "json",
            data: function(d) {
                d.bulan = $(SELECTORS.FILTER_BULAN).val();
            },
            dataSrc: 'aaData',
            error: function(xhr, status, error) {
                console.error('DataTable error:', error);
                toastr["error"]("Error loading data: " + error);
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
                data: 'CIF' 
            },
            { 
                data: 'NAMA_NASABAH' 
            },
            { 
                data: 'TOTAL_NOMINAL', 
                className: "text-right", 
                render: function(data) { 
                    return `<span class="${BADGE_CLASSES.INFO}">${formatCurrency(data)}</span>`; 
                } 
            },
            { 
                data: 'NOMINAL_VALAS', 
                className: "text-right", 
                render: function(data) { 
                    return `<span class="${BADGE_CLASSES.SUCCESS}">${formatCurrency(data)}</span>`; 
                } 
            },
            { 
                data: 'NOMINAL_UNDERLYING', 
                className: "text-right", 
                render: function(data) { 
                    return `<span class="${BADGE_CLASSES.UNDERLYING}" style="background-color:#fd7e14;color:#fff;">${formatCurrency(data)}</span>`; 
                } 
            },
            {
                data: null,
                className: "text-center",
                render: function(data, type, row) {
                    const totalUnderlying = safeParseInt(row.TOTAL_UNDERLYING);
                    const totalValas = safeParseInt(row.TOTAL_VALAS);
                    const total = totalUnderlying + totalValas;
                    const percent = calculatePercentage(totalUnderlying, total);
                    
                    return `
                        <div class="d-flex flex-column align-items-center">
                            <span class="mb-1">${totalUnderlying} / ${total} <small>(${percent}%)</small></span>
                            <div class="progress" style="height:8px;width:70px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: ${percent}%"></div>
                            </div>
                        </div>
                    `;
                }
            },
            {
                data: null,
                orderable: false,
                className: "text-center",
                render: function(data, type, row) {
                    const bulan = $(SELECTORS.FILTER_BULAN).val();
                    const config = window.monitoringConfig || {};
                    const url = `${config.api?.print}?bulan=${bulan}&cif=${row.CIF}`;
                    return `<a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary">
                        <i class="fal fa-print"></i> Cetak
                    </a>`;
                }
            }
        ]
    });

    return table;
};

/**
 * Get current table instance
 * @returns {DataTable}
 */
export const getTable = () => {
    return table;
};

/**
 * Reload table data
 */
export const reloadTable = () => {
    if (table) {
        table.destroy();
        table = initTable();
    }
};
