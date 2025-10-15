// ==========================================
// JURNAL ESCROW BILLER PL - DATATABLE MODULE
// ==========================================

// DataTable instance
let jurnalEscrowBillerPlTable;
// State untuk menyimpan baris yang sedang di-expand
let expandedRows = new Set();

/**
 * Helper function untuk render badge dengan count
 * @param {number} count - Count value
 * @param {string} badgeClass - Badge CSS class (warning, success, light)
 * @returns {string} HTML badge
 */
function renderCountBadge(count, badgeClass) {
    const parsedCount = parseInt(count || 0);
    if (parsedCount > 0) {
        // Jika warning, gunakan warna custom yang lebih gelap dengan text putih
        if (badgeClass === 'warning') {
            return `<span class="badge text-white" style="background-color: #d97706;">${parsedCount}</span>`;
        }
        return `<span class="badge badge-${badgeClass}">${parsedCount}</span>`;
    }
    return '<span class="badge badge-light">0</span>';
}

/**
 * Helper function untuk render Status Escrow dengan badge berwarna
 * @param {string} status - Status escrow value
 * @returns {string} HTML badge dengan warna
 */
function renderStatusEscrow(status) {
    if (!status) {
        return '<span class="badge badge-light small">-</span>';
    }
    
    const statusLower = status.toLowerCase();
    
    // Mapping status ke badge class
    if (statusLower.includes('sukses') || statusLower.includes('success') || statusLower.includes('selesai')) {
        return `<span class="badge badge-success small"><i class="fal fa-check-circle"></i> ${status}</span>`;
    } else if (statusLower.includes('pending') || statusLower.includes('proses') || statusLower.includes('waiting')) {
        return `<span class="badge text-white small" style="background-color: #f39c12;"><i class="fal fa-clock"></i> ${status}</span>`;
    } else if (statusLower.includes('gagal') || statusLower.includes('failed') || statusLower.includes('error')) {
        return `<span class="badge badge-danger small"><i class="fal fa-times-circle"></i> ${status}</span>`;
    } else {
        return `<span class="badge badge-info small">${status}</span>`;
    }
}

/**
 * Helper function untuk render Core Response Code
 * @param {string} coreRes - Core response code
 * @returns {string} HTML badge atau text
 */
function renderCoreResCode(coreRes) {
    if (!coreRes) {
        return '<span class="text-muted">NULL</span>';
    }
    const badgeClass = coreRes.startsWith('00') ? 'success' : 'danger';
    return `<span class="badge badge-${badgeClass} small">${coreRes}</span>`;
}

/**
 * Helper function untuk render status transaksi
 * @param {string} codeRes - Response code
 * @returns {string} HTML status badge
 */
function renderTransactionStatus(codeRes) {
    if (codeRes && codeRes.startsWith('00')) {
        return '<span class="badge badge-success small"><i class="fal fa-check"></i> Selesai</span>';
    } else if (codeRes) {
        return '<span class="badge badge-warning small"><i class="fal fa-exclamation-triangle"></i> Gagal</span>';
    }
    return '<span class="badge badge-light small"><i class="fal fa-clock"></i> Pending</span>';
}

/**
 * Helper function untuk truncate text dengan tooltip
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} HTML with truncated text
 */
function renderTruncatedText(text, maxLength = 10) {
    if (!text) {
        return '<span class="text-muted">NULL</span>';
    }
    if (text.length > maxLength) {
        return `<span title="${text}">${text.substring(0, maxLength)}...</span>`;
    }
    return `<span>${text}</span>`;
}

function initializeDataTable() {
    jurnalEscrowBillerPlTable = $('#jurnalEscrowBillerPlTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appConfig.baseUrl + 'settlement/jurnal-escrow-biller-pl/datatable',
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
                        jurnalEscrowBillerPlTable.ajax.reload();
                    });
                }
            },
            dataSrc: function(json) {
                // Store child data globally for use in row details
                window.childDataMap = {};
                
                // Store processed status untuk setiap kd_settle dengan detail status
                window.processedStatusMap = {};
                
                // Filter hanya parent rows untuk display utama
                const parentRows = json.data.filter(row => row.is_parent);
                
                // Group child rows by parent dan simpan status lengkap
                json.data.forEach(row => {
                    if (row.is_parent) {
                        // Simpan status processed dengan detail
                        window.processedStatusMap[row.r_KD_SETTLE] = {
                            processed: row.is_processed || false,
                            is_success: row.is_success, // 1 = success, 0 = failed, null = not processed
                            attempt_number: row.attempt_number || 0,
                            response_message: row.response_message || '' // Error message dari Akselgate
                        };
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
                render: (data) => renderCountBadge(data, 'warning')
            },
            { 
                data: 'r_JURNAL_SUKSES', 
                name: 'r_JURNAL_SUKSES',
                className: 'text-center',
                width: '10%',
                render: (data) => renderCountBadge(data, 'success')
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
    $('#jurnalEscrowBillerPlTable tbody').on('click', 'td.details-control', function () {
        const tr = $(this).closest('tr');
        const row = jurnalEscrowBillerPlTable.row(tr);
        const kdSettle = tr.attr('data-kd-settle');
        const expandBtn = $(this).find('.expand-btn');
        
        if (row.child.isShown()) {
            // Close row
            $('.child-details-container').fadeOut(200, function() {
                row.child.hide();
                tr.removeClass('shown');
                toggleExpandButton(expandBtn, false);
                expandedRows.delete(kdSettle);
                console.log('Row collapsed:', kdSettle);
            });
        } else {
            // Close any other open rows first
            jurnalEscrowBillerPlTable.rows().every(function() {
                if (this.child.isShown()) {
                    this.child.hide();
                    const $node = $(this.node());
                    $node.removeClass('shown');
                    toggleExpandButton($node.find('.expand-btn'), false);
                    
                    const nodeKdSettle = $node.attr('data-kd-settle');
                    if (nodeKdSettle) {
                        expandedRows.delete(nodeKdSettle);
                    }
                }
            });
            
            // Open this row
            const childData = window.childDataMap[kdSettle] || [];
            row.child(formatChildRows(childData, kdSettle)).show();
            tr.addClass('shown');
            toggleExpandButton(expandBtn, true);
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

/**
 * Toggle expand button icon dan title
 * @param {jQuery} $btn - Expand button element
 * @param {boolean} isExpanded - Apakah row sudah di-expand
 */
function toggleExpandButton($btn, isExpanded) {
    if (isExpanded) {
        $btn.removeClass('fa-plus-square').addClass('fa-minus-square')
            .attr('title', 'Klik untuk menyembunyikan detail');
    } else {
        $btn.removeClass('fa-minus-square').addClass('fa-plus-square')
            .attr('title', 'Klik untuk melihat detail transaksi');
    }
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
            const $row = $(`tr[data-kd-settle="${kdSettle}"]`);
            if ($row.length > 0) {
                const row = jurnalEscrowBillerPlTable.row($row);
                const expandBtn = $row.find('.expand-btn');
                
                if (!row.child.isShown() && expandBtn.length > 0) {
                    const childData = window.childDataMap[kdSettle] || [];
                    row.child(formatChildRows(childData, kdSettle)).show();
                    $row.addClass('shown');
                    toggleExpandButton(expandBtn, true);
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
        return `<div class="child-details-container">
                    <div class="p-3 text-center text-muted">
                        <i class="fal fa-info-circle me-2"></i>
                        <em>Tidak ada detail transaksi</em>
                    </div>
                </div>`;
    }
    
    // Cek status processing
    const status = window.processedStatusMap?.[kdSettle];
    const isProcessed = status?.processed || false;
    const isSuccess = status?.is_success; // '1' = success, '0' = failed (STRING dari backend)
    const attemptNumber = status?.attempt_number || 0;
    const responseMessage = status?.response_message || '';
    
    // Cek apakah semua status escrow adalah "SUKSES"
    const hasNonSuccessStatus = childData.some(child => {
        const statusEscrow = (child.d_STATUS_KR_ESCROW || '').toLowerCase();
        return !statusEscrow.includes('sukses') && !statusEscrow.includes('success') && !statusEscrow.includes('selesai');
    });
    
    // Build button HTML
    let buttonHtml = '';
    if (!isProcessed) {
        // Jika ada status yang bukan sukses, show disabled button dengan tooltip
        if (hasNonSuccessStatus) {
            buttonHtml = `<button type="button" class="btn btn-secondary btn-sm" disabled
                                 title="Tidak dapat diproses: Ada transaksi dengan Status Escrow yang belum SUKSES. Pastikan semua transaksi berstatus SUKSES sebelum memproses."
                                 data-toggle="tooltip" data-placement="top">
                            <i class="fal fa-lock me-1"></i> Tidak Dapat Diproses
                          </button>`;
        } else {
            buttonHtml = `<button type="button" class="btn btn-primary btn-sm" 
                                 onclick="processBatchJurnal('${kdSettle}')" 
                                 id="btn-batch-${kdSettle}">
                            <i class="fal fa-play me-1"></i> Proses Semua (${childData.length})
                          </button>`;
        }
    } else {
        buttonHtml = `<button type="button" class="btn btn-secondary btn-sm" disabled>
                        <i class="fal fa-check-circle me-1"></i>Sudah Diproses (Attempt #${attemptNumber})
                      </button>`;
        
        // Tambahkan button "Proses Ulang" jika gagal dengan error Akselgate
        if ((isSuccess == 0 || isSuccess === '0') && responseMessage) {
            buttonHtml += `<button type="button" class="btn btn-primary btn-sm ml-2" 
                                  onclick="processBatchJurnal('${kdSettle}')" 
                                  id="btn-batch-${kdSettle}">
                            <i class="fal fa-redo me-1"></i> Proses Ulang
                           </button>`;
        }
    }
    
    // Cek error message dari child pertama
    const errorMessage = childData.find(c => c.d_ERROR_MESSAGE?.trim())?.d_ERROR_MESSAGE || '';
    const errorAlert = errorMessage ? 
        `<div class="alert alert-danger fade show mx-2 my-1 mb-0" role="alert" style="font-size: 0.9rem;">
            <i class="fal fa-exclamation-triangle me-2"></i> Akselgate response: ${errorMessage}
         </div>` : '';
    
    // Build table rows
    const tableRows = childData.map(child => {
        const rowData = [
            renderStatusEscrow(child.d_STATUS_KR_ESCROW),
            `<code>${child.d_NO_REF || '-'}</code>`,
            `<code>${child.d_DEBIT_ACCOUNT || '-'}</code>`,
            `<small>${child.d_DEBIT_NAME || '-'}</small>`,
            `<code>${child.d_CREDIT_ACCOUNT || '-'}</code>`,
            `<small>${child.d_CREDIT_NAME || '-'}</small>`,
            `<strong class="text-dark">${formatCurrency(child.d_AMOUNT || 0)}</strong>`,
            renderCoreResCode(child.d_CODE_RES),
            child.d_CORE_REF ? `<code>${child.d_CORE_REF}</code>` : '<span class="text-muted">NULL</span>',
            child.d_CORE_DATETIME ? `<small>${child.d_CORE_DATETIME}</small>` : '<span class="text-muted">NULL</span>',
            renderTransactionStatus(child.d_CODE_RES)
        ];
        
        const cells = rowData.map((data, idx) => {
            const align = idx === 6 ? ' class="text-end"' : (idx >= 7 ? ' class="text-center"' : '');
            return `<td${align}>${data}</td>`;
        }).join('');
        
        return `<tr>${cells}</tr>`;
    }).join('');
    
    // Table headers
    const tableHeaders = [
        'Status Escrow', 'No. Ref', 'Debit Account', 'Debit Name', 'Credit Account', 'Credit Name',
        'Nominal', 'Core Res', 'Core Ref', 'Core DateTime', 'Status'
    ];
    const colWidths = ['8%', '9%', '10%', '12%', '10%', '12%', '10%', '7%', '8%', '9%', '7%'];
    
    const headerCells = tableHeaders.map((header, idx) => 
        `<th style="width: ${colWidths[idx]}">${header}</th>`
    ).join('');
    
    // Combine all HTML
    return `<div class="child-details-container">
                <div class="child-details-header d-flex justify-content-between align-items-center">
                    <div><i class="fal fa-list-alt"></i> Detail Transaksi (${childData.length} item)</div>
                    <div class="d-flex align-items-center">${buttonHtml}</div>
                </div>
                ${errorAlert}
                <div class="px-2 pb-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover child-table">
                            <thead><tr>${headerCells}</tr></thead>
                            <tbody>${tableRows}</tbody>
                        </table>
                    </div>
                </div>
            </div>`;
}

// Function untuk expand/collapse semua rows
function toggleAllRows(expand = true) {
    jurnalEscrowBillerPlTable.rows().every(function() {
        const tr = $(this.node());
        const kdSettle = tr.attr('data-kd-settle');
        const expandBtn = tr.find('.expand-btn');
        
        if (expand && !this.child.isShown() && expandBtn.length > 0) {
            const childData = window.childDataMap[kdSettle] || [];
            this.child(formatChildRows(childData, kdSettle)).show();
            tr.addClass('shown');
            toggleExpandButton(expandBtn, true);
            if (kdSettle) expandedRows.add(kdSettle);
        } else if (!expand && this.child.isShown()) {
            this.child.hide();
            tr.removeClass('shown');
            toggleExpandButton(expandBtn, false);
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
    
    jurnalEscrowBillerPlTable.rows().every(function() {
        const tr = $(this.node());
        const kdSettle = tr.attr('data-kd-settle');
        const childData = window.childDataMap[kdSettle] || [];
        
        const hasMatch = childData.some(child => 
            (child.d_STATUS_KR_ESCROW && child.d_STATUS_KR_ESCROW.toLowerCase().includes(searchLower)) ||
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
    if (jurnalEscrowBillerPlTable) {
        console.log('Manual refresh with preserved expand state for:', Array.from(expandedRows));
        jurnalEscrowBillerPlTable.ajax.reload(function() {
            showAlert('success', 'Data berhasil dimuat ulang!');
            setTimeout(() => initializeTooltips(), 500);
        }, false);
    }
}

