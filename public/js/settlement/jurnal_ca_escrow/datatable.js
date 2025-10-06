// ==========================================
// JURNAL CA ESCROW - DATATABLE MODULE
// ==========================================

// DataTable instance
let jurnalCaEscrowTable;
// State untuk menyimpan baris yang sedang di-expand
let expandedRows = new Set();

function initializeDataTable() {
    jurnalCaEscrowTable = $('#jurnalCaEscrowTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appConfig.baseUrl + 'settlement/jurnal-ca-escrow/datatable',
            type: 'GET',
            data: function(d) {
                d.tanggal = $('#tanggal').val() || window.appConfig.tanggalData;
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        jurnalCaEscrowTable.ajax.reload();
                    });
                }
            },
            dataSrc: function(json) {
                // Store child data globally for use in row details
                window.childDataMap = {};
                
                // Store processed status untuk setiap kd_settle
                window.processedStatusMap = {};
                
                // Filter hanya parent rows untuk display utama
                const parentRows = json.data.filter(row => row.is_parent);
                
                // Group child rows by parent dan simpan status
                json.data.forEach(row => {
                    if (row.is_parent) {
                        // Simpan status processed
                        window.processedStatusMap[row.r_KD_SETTLE] = row.is_processed || false;
                    }
                    
                    if (!row.is_parent && row.parent_kd_settle) {
                        if (!window.childDataMap[row.parent_kd_settle]) {
                            window.childDataMap[row.parent_kd_settle] = [];
                        }
                        window.childDataMap[row.parent_kd_settle].push(row);
                    }
                });
                
                // Debug log untuk pagination
                console.log('DataTable pagination debug:', {
                    recordsTotal: json.recordsTotal,
                    recordsFiltered: json.recordsFiltered,
                    parentRowsCount: parentRows.length,
                    totalDataReceived: json.data.length,
                    debugInfo: json.debug || 'No debug info from server'
                });
                
                return parentRows;
            }
        },
        columns: [
            { 
                className: 'details-control text-center',
                orderable: false,
                searchable: false,
                data: null,
                width: '5%',
                render: function(data, type, row, meta) {
                    if (row.has_children) {
                        return '<i class="fal fa-plus-square expand-btn text-purple" ' +
                               'style="cursor: pointer; font-size: 1.1em; color: #6c5190;" ' +
                               'title="Klik untuk melihat detail transaksi"></i>';
                    }
                    return '<span class="text-muted">' + (meta.row + 1) + '</span>';
                }
            },
            { 
                data: 'r_KD_SETTLE', 
                name: 'r_KD_SETTLE',
                width: '20%',
                render: function(data, type, row) {
                    return '<strong><code>' + (data || '') + '</code></strong>';
                }
            },
            { 
                data: 'r_NAMA_PRODUK', 
                name: 'r_NAMA_PRODUK',
                width: '25%',
                render: function(data, type, row) {
                    return '<strong>' + (data || '') + '</strong>';
                }
            },
            { 
                data: 'r_AMOUNT_ESCROW', 
                name: 'r_AMOUNT_ESCROW',
                width: '15%',
                className: 'text-end',
                render: function(data, type, row) {
                    return '<strong class="text-purple">' + formatCurrency(data) + '</strong>';
                }
            },
            { 
                data: 'r_TOTAL_JURNAL', 
                name: 'r_TOTAL_JURNAL',
                className: 'text-center',
                width: '10%',
                render: function(data, type, row) {
                    return '<span class="badge" style="background-color: #6c5190; color: white;">' + (data || '0') + '</span>';
                }
            },
            { 
                data: 'r_JURNAL_PENDING', 
                name: 'r_JURNAL_PENDING',
                className: 'text-center',
                width: '10%',
                render: function(data, type, row) {
                    const count = parseInt(data || 0);
                    if (count > 0) {
                        return '<span class="badge badge-warning">' + count + '</span>';
                    }
                    return '<span class="badge badge-light">0</span>';
                }
            },
            { 
                data: 'r_JURNAL_SUKSES', 
                name: 'r_JURNAL_SUKSES',
                className: 'text-center',
                width: '10%',
                render: function(data, type, row) {
                    const count = parseInt(data || 0);
                    if (count > 0) {
                        return '<span class="badge badge-success">' + count + '</span>';
                    }
                    return '<span class="badge badge-light">0</span>';
                }
            },
            { 
                data: null,
                name: 'summary',
                orderable: false,
                searchable: false,
                className: 'text-center',
                width: '5%',
                render: function(data, type, row) {
                    if (row.child_count > 0) {
                        return '<small class="text-muted">' + row.child_count + ' detail</small>';
                    }
                    return '<small class="text-muted">-</small>';
                }
            }
        ],
        pageLength: 15,
        lengthChange: false,
        order: [[1, 'asc']],
        language: {
            processing: "Memuat data...",
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data per halaman",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 data",
            infoFiltered: "(difilter dari _MAX_ total data)",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            },
            emptyTable: "Tidak ada data yang tersedia",
            zeroRecords: "Tidak ditemukan data yang sesuai"
        },
        responsive: false,
        searching: false,
        dom: '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>',
        drawCallback: function(settings) {
            var api = this.api();
            var pageInfo = api.page.info();
            console.log('DataTable Draw Callback:', {
                recordsTotal: pageInfo.recordsTotal,
                recordsDisplay: pageInfo.recordsDisplay,
                page: pageInfo.page,
                pages: pageInfo.pages,
                start: pageInfo.start,
                end: pageInfo.end,
                length: pageInfo.length
            });
            
            initializeTooltips();
            restoreExpandedRows();
        },
        createdRow: function(row, data, dataIndex) {
            $(row).addClass('parent-row');
            $(row).attr('data-kd-settle', data.r_KD_SETTLE);
        }
    });
    
    // Add event listener for opening and closing details
    $('#jurnalCaEscrowTable tbody').on('click', 'td.details-control', function () {
        const tr = $(this).closest('tr');
        const row = jurnalCaEscrowTable.row(tr);
        const kdSettle = tr.attr('data-kd-settle');
        const expandBtn = $(this).find('.expand-btn');
        
        if (row.child.isShown()) {
            // Close row
            $('.child-details-container').fadeOut(200, function() {
                row.child.hide();
                tr.removeClass('shown');
                expandBtn.removeClass('fa-minus-square').addClass('fa-plus-square');
                expandBtn.attr('title', 'Klik untuk melihat detail transaksi');
                expandedRows.delete(kdSettle);
                console.log('Row collapsed:', kdSettle);
            });
        } else {
            // Close any other open rows first
            jurnalCaEscrowTable.rows().every(function() {
                if (this.child.isShown()) {
                    this.child.hide();
                    $(this.node()).removeClass('shown');
                    $(this.node()).find('.expand-btn')
                        .removeClass('fa-minus-square')
                        .addClass('fa-plus-square')
                        .attr('title', 'Klik untuk melihat detail transaksi');
                    
                    const nodeKdSettle = $(this.node()).attr('data-kd-settle');
                    if (nodeKdSettle) {
                        expandedRows.delete(nodeKdSettle);
                    }
                }
            });
            
            // Open this row
            const childData = window.childDataMap[kdSettle] || [];
            row.child(formatChildRows(childData, kdSettle)).show();
            tr.addClass('shown');
            expandBtn.removeClass('fa-plus-square').addClass('fa-minus-square');
            expandBtn.attr('title', 'Klik untuk menyembunyikan detail');
            expandedRows.add(kdSettle);
            
            $('.child-details-container').hide().fadeIn(300);
            initializeTooltips();
        }
    });
    
    initializeTooltips();
    
    // Prevent double click on all process buttons globally
    $(document).on('click', '.child-details-container button', function(e) {
        const $btn = $(this);
        if ($btn.prop('disabled') || $btn.hasClass('btn-processing')) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Prevented click on disabled/processing button');
            return false;
        }
        
        if ($('.btn-processing').length > 0 && !$btn.hasClass('btn-processing')) {
            e.preventDefault();
            e.stopPropagation();
            showAlert('warning', 'Ada transaksi lain yang sedang diproses. Silakan tunggu hingga selesai.');
            return false;
        }
    });
    
    // Keyboard shortcuts
    $(document).keydown(function(e) {
        if (e.ctrlKey && e.keyCode === 69) { // Ctrl + E
            e.preventDefault();
            toggleAllRows(true);
        }
        if (e.ctrlKey && e.shiftKey && e.keyCode === 67) { // Ctrl + Shift + C
            e.preventDefault();
            toggleAllRows(false);
        }
    });
}

// Function to initialize tooltips
function initializeTooltips() {
    if (typeof $().tooltip === 'function') {
        $('[title]').tooltip({
            placement: 'top',
            delay: { show: 500, hide: 100 }
        });
    }
}

// Function untuk restore expanded rows setelah reload
function restoreExpandedRows() {
    setTimeout(function() {
        expandedRows.forEach(function(kdSettle) {
            const $row = $('tr[data-kd-settle="' + kdSettle + '"]');
            if ($row.length > 0) {
                const row = jurnalCaEscrowTable.row($row);
                const expandBtn = $row.find('.expand-btn');
                
                if (!row.child.isShown() && expandBtn.length > 0) {
                    const childData = window.childDataMap[kdSettle] || [];
                    row.child(formatChildRows(childData, kdSettle)).show();
                    $row.addClass('shown');
                    expandBtn.removeClass('fa-plus-square').addClass('fa-minus-square');
                    expandBtn.attr('title', 'Klik untuk menyembunyikan detail');
                    console.log('Restored expanded state for:', kdSettle);
                }
            }
        });
        
        initializeTooltips();
    }, 200);
}

// Function to format child rows
function formatChildRows(childData, kdSettle) {
    if (!childData || childData.length === 0) {
        return '<div class="child-details-container">' +
               '<div class="p-3 text-center text-muted">' +
               '<i class="fal fa-info-circle me-2"></i>' +
               '<em>Tidak ada detail transaksi</em>' +
               '</div></div>';
    }
    
    let html = '<div class="child-details-container">';
    
    // Cek apakah sudah diproses
    const isProcessed = window.processedStatusMap && window.processedStatusMap[kdSettle];
    
    // Header dengan tombol batch process
    html += '<div class="child-details-header d-flex justify-content-between align-items-center">';
    html += '<div><i class="fal fa-list-alt"></i> Detail Transaksi (' + childData.length + ' item)</div>';
    
    // Render button sesuai status
    if (isProcessed) {
        // Button sudah diproses (abu-abu, disabled)
        html += '<button type="button" class="btn btn-secondary btn-sm" disabled id="btn-batch-' + kdSettle + '">';
        html += '<i class="fal fa-check-circle me-1"></i>Sudah Diproses';
        html += '</button>';
    } else {
        // Button normal (biru, aktif)
        html += '<button type="button" class="btn btn-primary btn-sm" onclick="processBatchJurnal(\'' + kdSettle + '\')" id="btn-batch-' + kdSettle + '">';
        html += '<i class="fal fa-play me-1"></i> Proses Semua (' + childData.length + ')';
        html += '</button>';
    }
    
    html += '</div>';
    
    // Table detail
    html += '<div class="px-2 pb-2">';
    html += '<div class="table-responsive">';
    html += '<table class="table table-sm table-hover child-table">';
    html += '<thead>';
    html += '<tr>';
    html += '<th style="width: 10%">No. Ref</th>';
    html += '<th style="width: 11%">Debit Account</th>';
    html += '<th style="width: 13%">Debit Name</th>';
    html += '<th style="width: 11%">Credit Account</th>';
    html += '<th style="width: 13%">Credit Name</th>';
    html += '<th style="width: 10%">Nominal</th>';
    html += '<th style="width: 8%">Core Res</th>';
    html += '<th style="width: 9%">Core Ref</th>';
    html += '<th style="width: 10%">Core DateTime</th>';
    html += '<th style="width: 8%">Status</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    childData.forEach(function(child, index) {
        html += '<tr>';
        html += '<td><code>' + (child.d_NO_REF || '-') + '</code></td>';
        html += '<td><code>' + (child.d_DEBIT_ACCOUNT || '-') + '</code></td>';
        html += '<td><small>' + (child.d_DEBIT_NAME || '-') + '</small></td>';
        html += '<td><code>' + (child.d_CREDIT_ACCOUNT || '-') + '</code></td>';
        html += '<td><small>' + (child.d_CREDIT_NAME || '-') + '</small></td>';
        html += '<td class="text-end"><strong class="text-dark">' + formatCurrency(child.d_AMOUNT || 0) + '</strong></td>';
        
        // Core Res
        html += '<td class="text-center">';
        const coreRes = child.d_CODE_RES;
        if (!coreRes) {
            html += '<span class="text-muted">NULL</span>';
        } else if (coreRes.startsWith('00')) {
            html += '<span class="badge badge-success small">' + coreRes + '</span>';
        } else {
            html += '<span class="badge badge-danger small">' + coreRes + '</span>';
        }
        html += '</td>';
        
        // Core Ref
        html += '<td>';
        const coreRef = child.d_CORE_REF;
        if (!coreRef) {
            html += '<span class="text-muted">NULL</span>';
        } else if (coreRef.length > 10) {
            html += '<span title="' + coreRef + '">' + coreRef.substring(0, 10) + '...</span>';
        } else {
            html += '<span>' + coreRef + '</span>';
        }
        html += '</td>';
        
        // Core DateTime
        html += '<td>';
        const coreDateTime = child.d_CORE_DATETIME;
        html += coreDateTime ? '<small>' + coreDateTime + '</small>' : '<span class="text-muted">NULL</span>';
        html += '</td>';
        
        // Status
        html += '<td class="text-center">';
        if (child.d_CODE_RES && child.d_CODE_RES.startsWith('00')) {
            html += '<span class="badge badge-success small"><i class="fal fa-check"></i> Selesai</span>';
        } else if (child.d_CODE_RES && !child.d_CODE_RES.startsWith('00')) {
            html += '<span class="badge badge-warning small"><i class="fal fa-exclamation-triangle"></i> Gagal</span>';
        } else {
            html += '<span class="badge badge-light small"><i class="fal fa-clock"></i> Pending</span>';
        }
        html += '</td>';
        
        html += '</tr>';
    });
    
    html += '</tbody></table></div></div></div>';
    
    return html;
}

// Function untuk expand/collapse semua rows
function toggleAllRows(expand = true) {
    jurnalCaEscrowTable.rows().every(function() {
        const tr = $(this.node());
        const kdSettle = tr.attr('data-kd-settle');
        const expandBtn = tr.find('.expand-btn');
        
        if (expand && !this.child.isShown() && expandBtn.length > 0) {
            const childData = window.childDataMap[kdSettle] || [];
            this.child(formatChildRows(childData, kdSettle)).show();
            tr.addClass('shown');
            expandBtn.removeClass('fa-plus-square').addClass('fa-minus-square');
            if (kdSettle) expandedRows.add(kdSettle);
        } else if (!expand && this.child.isShown()) {
            this.child.hide();
            tr.removeClass('shown');
            expandBtn.removeClass('fa-minus-square').addClass('fa-plus-square');
            if (kdSettle) expandedRows.delete(kdSettle);
        }
    });
    
    setTimeout(() => initializeTooltips(), 300);
}

// Function untuk mencari dalam detail data
function searchInDetails(searchTerm) {
    if (!searchTerm) return;
    
    const searchLower = searchTerm.toLowerCase();
    let found = false;
    
    jurnalCaEscrowTable.rows().every(function() {
        const tr = $(this.node());
        const kdSettle = tr.attr('data-kd-settle');
        const childData = window.childDataMap[kdSettle] || [];
        
        const hasMatch = childData.some(child => 
            (child.d_NO_REF && child.d_NO_REF.toLowerCase().includes(searchLower)) ||
            (child.d_DEBIT_ACCOUNT && child.d_DEBIT_ACCOUNT.toLowerCase().includes(searchLower)) ||
            (child.d_DEBIT_NAME && child.d_DEBIT_NAME.toLowerCase().includes(searchLower)) ||
            (child.d_CREDIT_ACCOUNT && child.d_CREDIT_ACCOUNT.toLowerCase().includes(searchLower)) ||
            (child.d_CREDIT_NAME && child.d_CREDIT_NAME.toLowerCase().includes(searchLower)) ||
            (child.d_CODE_RES && child.d_CODE_RES.toLowerCase().includes(searchLower)) ||
            (child.d_CORE_REF && child.d_CORE_REF.toLowerCase().includes(searchLower)) ||
            (child.d_CORE_DATETIME && child.d_CORE_DATETIME.toLowerCase().includes(searchLower))
        );
        
        if (hasMatch && !this.child.isShown()) {
            const expandBtn = tr.find('.expand-btn');
            this.child(formatChildRows(childData, kdSettle)).show();
            tr.addClass('shown');
            expandBtn.removeClass('fa-plus-square').addClass('fa-minus-square');
            if (kdSettle) expandedRows.add(kdSettle);
            found = true;
        }
    });
    
    if (found) {
        showAlert('info', `Ditemukan hasil pencarian untuk "${searchTerm}". Baris yang relevan telah diperluas.`);
        setTimeout(() => initializeTooltips(), 300);
    } else {
        showAlert('warning', `Tidak ditemukan hasil untuk "${searchTerm}" dalam detail transaksi.`);
    }
}

// Function untuk refresh data table
function refreshTableData() {
    if (jurnalCaEscrowTable) {
        showAlert('info', 'Memuat ulang data...');
        console.log('Manual refresh with preserved expand state for:', Array.from(expandedRows));
        jurnalCaEscrowTable.ajax.reload(function() {
            showAlert('success', 'Data berhasil dimuat ulang!');
            setTimeout(() => initializeTooltips(), 500);
        }, false);
    }
}
