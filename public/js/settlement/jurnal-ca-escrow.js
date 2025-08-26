// CSRF Management
let currentCSRF = window.appConfig?.csrfToken || '';

$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        if (settings.type === 'POST') {
            if (settings.data instanceof FormData) {
                settings.data.append('csrf_test_name', currentCSRF);
            } else {
                const separator = settings.data ? '&' : '';
                settings.data = (settings.data || '') + separator + 'csrf_test_name=' + encodeURIComponent(currentCSRF);
            }
        }
    }
});

$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 403 || xhr.status === 419) {
        console.log('CSRF Token expired, refreshing...');
        refreshCSRFToken().then(function() {
            console.log('CSRF refreshed, retrying request...');
            if (!settings._retried) {
                settings._retried = true;
                $.ajax(settings);
            }
        });
    }
});

function refreshCSRFToken() {
    console.log('Attempting to refresh CSRF token...');
    console.log('Current CSRF before refresh:', currentCSRF);

    return $.get(window.appConfig.baseUrl + "get-csrf-token").then(function(response) {
        console.log('CSRF refresh response:', response);
        
        if (response.csrf_token) {
            const oldToken = currentCSRF;
            currentCSRF = response.csrf_token;
            console.log('CSRF token refreshed from:', oldToken, 'to:', currentCSRF);
            
            // Update all forms with new token
            $('input[name="csrf_test_name"]').val(currentCSRF);
        } else {
            console.warn('No CSRF token in refresh response');
        }
    }).catch(function(error) {
        console.error('Failed to refresh CSRF token:', error);
        setTimeout(function() {
            if (confirm('Session expired. Reload page?')) {
                location.reload();
            }
        }, 1000);
    });
}

// Function untuk cleanup state processing yang tertinggal
function cleanupProcessingState() {
    // Remove semua class processing dan hidden
    $('.btn-processing').removeClass('btn-processing');
    $('.btn-hidden-temp').removeClass('btn-hidden-temp').show();
    
    // Enable semua button
    $('.child-details-container button').prop('disabled', false);
    
    console.log('Processing state cleaned up');
}

// DataTable instance
let jurnalCaEscrowTable;
// State untuk menyimpan baris yang sedang di-expand
let expandedRows = new Set();

$(document).ready(function() {
    // Refresh CSRF token saat page load
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        
        // Cleanup any leftover processing states
        cleanupProcessingState();
        
        initializeDataTable();
    });
    
    // Handle form submit
    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        
        console.log('Form submit - Tanggal:', tanggal);
        
        if (tanggal && jurnalCaEscrowTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload data tetap di halaman yang sama
            jurnalCaEscrowTable.ajax.reload(null, false);
        }
    });
});

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
                
                // Filter hanya parent rows untuk display utama
                const parentRows = json.data.filter(row => row.is_parent);
                
                // Group child rows by parent
                json.data.forEach(row => {
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
                
                // Pastikan recordsTotal dan recordsFiltered sesuai dengan parent rows yang ditampilkan
                console.log('Pagination calculation check:', {
                    shouldShowPagination: json.recordsFiltered > 15,
                    totalPages: Math.ceil(json.recordsFiltered / 15),
                    currentRecordsShown: parentRows.length
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
        lengthChange: false, // Kembali ke setting asli
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
            // Log info setelah tabel di-render
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
            
            // Re-initialize tooltips setelah draw
            initializeTooltips();
            
            // Restore expanded rows setelah reload
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
            // This row is already open - close it with animation
            $('.child-details-container').fadeOut(200, function() {
                row.child.hide();
                tr.removeClass('shown');
                expandBtn.removeClass('fa-minus-square').addClass('fa-plus-square');
                expandBtn.attr('title', 'Klik untuk melihat detail transaksi');
                
                // Remove dari expandedRows
                expandedRows.delete(kdSettle);
                console.log('Row collapsed:', kdSettle, 'Current expanded rows:', Array.from(expandedRows));
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
                    
                    // Remove dari expandedRows
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
            
                // Add ke expandedRows
                expandedRows.add(kdSettle);
                console.log('Row expanded:', kdSettle, 'Current expanded rows:', Array.from(expandedRows));            // Animate the appearance
            $('.child-details-container').hide().fadeIn(300);
            
            // Initialize tooltips for the new content
            initializeTooltips();
        }
    });
    
    // Initialize tooltips on page load
    initializeTooltips();
    
    // Prevent double click on all process buttons globally
    $(document).on('click', '.child-details-container button', function(e) {
        const $btn = $(this);
        if ($btn.prop('disabled') || $btn.hasClass('btn-processing') || $btn.hasClass('btn-hidden-temp')) {
            e.preventDefault();
            e.stopPropagation();
            console.log('Prevented click on disabled/processing/hidden button');
            return false;
        }
        
        // Cek apakah ada proses lain yang sedang berjalan
        if ($('.btn-processing').length > 0 && !$btn.hasClass('btn-processing')) {
            e.preventDefault();
            e.stopPropagation();
            showAlert('warning', 'Ada transaksi lain yang sedang diproses. Silakan tunggu hingga selesai.');
            console.log('Prevented click - another process is running');
            return false;
        }
    });
    
    // Keyboard shortcuts
    $(document).keydown(function(e) {
        // Ctrl + E = Expand all
        if (e.ctrlKey && e.keyCode === 69) {
            e.preventDefault();
            toggleAllRows(true);
        }
        // Ctrl + Shift + C = Collapse all
        if (e.ctrlKey && e.shiftKey && e.keyCode === 67) {
            e.preventDefault();
            toggleAllRows(false);
        }
    });
}

// Function to initialize tooltips
function initializeTooltips() {
    // Initialize Bootstrap tooltips if available
    if (typeof $().tooltip === 'function') {
        $('[title]').tooltip({
            placement: 'top',
            delay: { show: 500, hide: 100 }
        });
    }
}

// Function untuk restore expanded rows setelah reload
function restoreExpandedRows() {
    // Tunggu sebentar untuk memastikan DOM sudah ready
    setTimeout(function() {
        expandedRows.forEach(function(kdSettle) {
            // Cari row dengan kd_settle yang sesuai
            const $row = $('tr[data-kd-settle="' + kdSettle + '"]');
            if ($row.length > 0) {
                const row = jurnalCaEscrowTable.row($row);
                const expandBtn = $row.find('.expand-btn');
                
                // Expand row jika belum expanded
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
        
        // Initialize tooltips untuk expanded content
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
    
    // Header dengan informasi jumlah detail
    html += '<div class="child-details-header">';
    html += '<i class="fal fa-list-alt"></i>';
    html += 'Detail Transaksi (' + childData.length + ' item)';
    html += '</div>';
    
    // Table detail dengan styling compact
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
    // html += '<th style="width: 5%">Aksi</th>';
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';
    
    childData.forEach(function(child, index) {
        html += '<tr>';
        
        // No Ref - tampilkan apa adanya
        html += '<td>';
        html += '<code>' + (child.d_NO_REF || '-') + '</code>';
        html += '</td>';
        
        // Debit Account - tampilkan apa adanya
        html += '<td>';
        html += '<code>' + (child.d_DEBIT_ACCOUNT || '-') + '</code>';
        html += '</td>';
        
        // Debit Name - tampilkan apa adanya
        html += '<td>';
        html += '<small>' + (child.d_DEBIT_NAME || '-') + '</small>';
        html += '</td>';
        
        // Credit Account - tampilkan apa adanya
        html += '<td>';
        html += '<code>' + (child.d_CREDIT_ACCOUNT || '-') + '</code>';
        html += '</td>';
        
        // Credit Name - tampilkan apa adanya
        html += '<td>';
        html += '<small>' + (child.d_CREDIT_NAME || '-') + '</small>';
        html += '</td>';
        
        // Amount - tampilkan dengan format currency
        html += '<td class="text-end">';
        html += '<strong class="text-dark">' + formatCurrency(child.d_AMOUNT || 0) + '</strong>';
        html += '</td>';
        
        // Core Res - tampilkan apa adanya dengan badge sederhana
        html += '<td class="text-center">';
        let coreResBadge = '';
        const coreRes = child.d_CODE_RES;
        if (coreRes === null || coreRes === undefined || coreRes === '') {
            coreResBadge = '<span class="text-muted">NULL</span>';
        } else if (coreRes.startsWith('00')) {
            coreResBadge = '<span class="badge badge-success small">' + coreRes + '</span>';
        } else {
            coreResBadge = '<span class="badge badge-danger small">' + coreRes + '</span>';
        }
        html += coreResBadge;
        html += '</td>';
        
        // Core Ref - tampilkan apa adanya dengan truncate untuk data panjang
        html += '<td>';
        const coreRef = child.d_CORE_REF;
        if (coreRef === null || coreRef === undefined || coreRef === '') {
            html += '<span class="text-muted">NULL</span>';
        } else if (coreRef.length > 10) {
            html += '<span title="' + coreRef + '">' + coreRef.substring(0, 10) + '...</span>';
        } else {
            html += '<span>' + coreRef + '</span>';
        }
        html += '</td>';
        
        // Core DateTime - tampilkan apa adanya
        html += '<td>';
        const coreDateTime = child.d_CORE_DATETIME;
        if (coreDateTime === null || coreDateTime === undefined || coreDateTime === '') {
            html += '<span class="text-muted">NULL</span>';
        } else {
            html += '<small>' + coreDateTime + '</small>';
        }
        html += '</td>';
        
        // Actions - sederhana tanpa icon yang berlebihan
        /*
        html += '<td class="text-center">';
        let actionButton = '';
        if (child.d_CODE_RES && child.d_CODE_RES.startsWith('00')) {
            actionButton = '<span class="badge badge-success small">Selesai</span>';
        } else if (child.d_CODE_RES && !child.d_CODE_RES.startsWith('00')) {
            actionButton = "<button class='btn btn-xs btn-outline-warning' " +
                          "onclick='handleProsesClick(this, " + JSON.stringify(child) + ", \"" + kdSettle + "\")' " +
                          "id='btn-child-" + index + "' title='Proses ulang transaksi'>" +
                          "<i class='fal fa-redo me-1'></i>Ulang" +
                          "</button>";
        } else {
            actionButton = "<button class='btn btn-xs btn-outline-primary' " +
                          "onclick='handleProsesClick(this, " + JSON.stringify(child) + ", \"" + kdSettle + "\")' " +
                          "id='btn-child-" + index + "' title='Proses transaksi'>" +
                          "<i class='fal fa-play me-1'></i>Proses" +
                          "</button>";
        }
        html += actionButton;
        html += '</td>';
        */
        
        html += '</tr>';
    });
    
    html += '</tbody>';
    html += '</table>';
    html += '</div>';
    html += '</div>';
    html += '</div>';
    
    return html;
}

// Function untuk format currency - digunakan hanya untuk parent rows dan konfirmasi
function formatCurrency(amount) {
    const num = parseFloat(String(amount || 0).replace(/,/g, ''));
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
}

function resetFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    window.location.href = url.pathname + url.search;
}

// Function untuk expand/collapse semua rows
function toggleAllRows(expand = true) {
    jurnalCaEscrowTable.rows().every(function() {
        const tr = $(this.node());
        const kdSettle = tr.attr('data-kd-settle');
        const expandBtn = tr.find('.expand-btn');
        
        if (expand && !this.child.isShown() && expandBtn.length > 0) {
            // Expand row
            const childData = window.childDataMap[kdSettle] || [];
            this.child(formatChildRows(childData, kdSettle)).show();
            tr.addClass('shown');
            expandBtn.removeClass('fa-plus-square').addClass('fa-minus-square');
            
            // Add ke expandedRows
            if (kdSettle) {
                expandedRows.add(kdSettle);
            }
        } else if (!expand && this.child.isShown()) {
            // Collapse row
            this.child.hide();
            tr.removeClass('shown');
            expandBtn.removeClass('fa-minus-square').addClass('fa-plus-square');
            
            // Remove dari expandedRows
            if (kdSettle) {
                expandedRows.delete(kdSettle);
            }
        }
    });
    
    // Initialize tooltips after changes
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
        
        // Check if any child data matches search
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
            // Auto expand rows that contain matches
            const expandBtn = tr.find('.expand-btn');
            this.child(formatChildRows(childData, kdSettle)).show();
            tr.addClass('shown');
            expandBtn.removeClass('fa-plus-square').addClass('fa-minus-square');
            
            // Add ke expandedRows
            if (kdSettle) {
                expandedRows.add(kdSettle);
            }
            
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
            // Re-initialize tooltips after reload
            setTimeout(() => initializeTooltips(), 500);
        }, false); // false = stay on current page, expanded rows akan di-restore otomatis oleh drawCallback
    }
}

// Action Functions dengan Security Best Practices

// Function untuk handle click button dengan prevent double click
function handleProsesClick(buttonElement, childData, kdSettle) {
    const $btn = $(buttonElement);
    
    // Cek apakah button sudah disabled (prevent double click)
    if ($btn.prop('disabled')) {
        console.log('Button already disabled, preventing double click');
        return false;
    }
    
    // Cek apakah ada proses lain yang sedang berjalan
    if ($('.btn-processing').length > 0) {
        console.log('Another process is running, preventing new process');
        showAlert('warning', 'Ada transaksi lain yang sedang diproses. Silakan tunggu hingga selesai.');
        return false;
    }
    
    // Disable dan hide SEMUA button proses di seluruh halaman
    hideAllProcessButtons();
    
    // Disable button dan ubah tampilan untuk button yang diklik
    $btn.prop('disabled', true);
    const originalHtml = $btn.html();
    $btn.html('<i class="fal fa-spinner fa-spin me-1"></i>Memproses...');
    $btn.addClass('btn-processing');
    $btn.show(); // Pastikan button yang sedang diproses tetap terlihat
    
    // Simpan original state untuk restore nanti
    $btn.data('original-html', originalHtml);
    
    console.log('All process buttons hidden, current button processing...');
    
    // Reset button state jika ada error atau selesai
    const resetButton = function() {
        setTimeout(function() {
            $btn.prop('disabled', false);
            $btn.html(originalHtml);
            $btn.removeClass('btn-processing');
            
            // Show kembali semua button proses
            showAllProcessButtons();
            
            console.log('All buttons restored, ready for next process');
        }, 1000); // Delay 1 detik sebelum enable kembali
    };
    
    // Call prosesJurnalChild dengan callback untuk reset button
    prosesJurnalChild(childData, kdSettle, 0, resetButton);
    
    return false; // Prevent default action
}

// Function untuk hide semua button proses
function hideAllProcessButtons() {
    $('.child-details-container button').each(function() {
        const $btn = $(this);
        if (!$btn.hasClass('btn-processing')) {
            $btn.hide();
            $btn.addClass('btn-hidden-temp');
        }
    });
    console.log('All process buttons hidden');
}

// Function untuk show kembali semua button proses
function showAllProcessButtons() {
    $('.child-details-container button.btn-hidden-temp').each(function() {
        const $btn = $(this);
        $btn.show();
        $btn.removeClass('btn-hidden-temp');
    });
    console.log('All process buttons restored');
}

function prosesJurnalChild(childData, kdSettle, retryCount = 0, resetButtonCallback = null) {
    console.log('Proses Jurnal Child:', childData, 'KD Settle:', kdSettle, 'Retry:', retryCount);
    
    // Maksimal 3 percobaan
    if (retryCount > 2) {
        console.log('Max retry attempts exceeded');
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: 'Maksimal percobaan telah tercapai. Silakan refresh halaman dan coba lagi.'
        });
        return;
    }
    
    // Validasi data
    if (!childData.d_NO_REF || !kdSettle) {
        showAlert('error', 'Data tidak valid untuk diproses! Pastikan memilih detail transaksi.');
        if (resetButtonCallback) {
            resetButtonCallback();
        }
        return;
    }
    
    // Cek apakah sudah diproses sukses
    if (childData.d_CODE_RES && childData.d_CODE_RES.startsWith('00')) {
        showAlert('warning', 'Jurnal sudah berhasil diproses sebelumnya!');
        if (resetButtonCallback) {
            resetButtonCallback();
        }
        return;
    }
    
    // Konfirmasi dengan detail informasi
    const isReprocess = childData.d_CODE_RES && !childData.d_CODE_RES.startsWith('00');
    
    // Helper function untuk format NULL values
    const formatValue = (value) => {
        return (value === null || value === undefined || value === '') ? 'NULL' : value;
    };
    
    const confirmMessage = isReprocess 
        ? `Apakah Anda yakin ingin memproses ULANG jurnal?\n\nKode Settle: ${kdSettle}\nNo Ref: ${childData.d_NO_REF}\nAmount: ${formatCurrency(childData.d_AMOUNT)}\nDebit: ${childData.d_DEBIT_ACCOUNT}\nDebit Name: ${formatValue(childData.d_DEBIT_NAME)}\nCredit: ${childData.d_CREDIT_ACCOUNT}\nCredit Name: ${formatValue(childData.d_CREDIT_NAME)}\nCore Res: ${formatValue(childData.d_CODE_RES)}\nCore Ref: ${formatValue(childData.d_CORE_REF)}\nCore DateTime: ${formatValue(childData.d_CORE_DATETIME)}\n\nTransaksi ini akan mengirim dana ke rekening bank!`
        : `Apakah Anda yakin ingin memproses jurnal?\n\nKode Settle: ${kdSettle}\nNo Ref: ${childData.d_NO_REF}\nAmount: ${formatCurrency(childData.d_AMOUNT)}\nDebit: ${childData.d_DEBIT_ACCOUNT}\nDebit Name: ${formatValue(childData.d_DEBIT_NAME)}\nCredit: ${childData.d_CREDIT_ACCOUNT}\nCredit Name: ${formatValue(childData.d_CREDIT_NAME)}\nCore Res: ${formatValue(childData.d_CODE_RES)}\nCore Ref: ${formatValue(childData.d_CORE_REF)}\nCore DateTime: ${formatValue(childData.d_CORE_DATETIME)}\n\nTransaksi ini akan mengirim dana ke rekening bank!`;
    
    if (!confirm(confirmMessage)) {
        // User membatalkan, restore semua button
        if (resetButtonCallback) {
            resetButtonCallback();
        }
        return;
    }
    
    // Disable semua tombol di child table
    $('.child-details-container button').prop('disabled', true);
    disableAllActions();
    
    // Show progress modal
    showProgressModal(childData, kdSettle);
    
    // Prevent browser close/refresh
    setBeforeUnloadWarning(true);
    
    // Store variables for error handler
    const currentChildData = childData;
    const currentKdSettle = kdSettle;
    const currentRetryCount = retryCount;
    
    // AJAX call untuk proses jurnal
    $.ajax({
        url: window.appConfig.baseUrl + "settlement/jurnal-ca-escrow/proses",
        type: 'POST',
        timeout: 120000, // 2 menit timeout
        data: {
            csrf_test_name: currentCSRF,
            kd_settle: kdSettle,
            no_ref: childData.d_NO_REF,
            amount: childData.d_AMOUNT,
            debit_account: childData.d_DEBIT_ACCOUNT,
            debit_name: childData.d_DEBIT_NAME,
            credit_account: childData.d_CREDIT_ACCOUNT,
            credit_name: childData.d_CREDIT_NAME,
            core_ref: childData.d_CORE_REF,
            core_datetime: childData.d_CORE_DATETIME,
            is_reprocess: isReprocess ? 1 : 0
        },
        beforeSend: function(xhr, settings) {
            console.log('Sending AJAX request with CSRF token:', currentCSRF);
            console.log('Request data:', settings.data);
        },
        success: function(response) {
            console.log('AJAX success response received:', response);
            
            hideProgressModal();
            setBeforeUnloadWarning(false);
            
            // Update CSRF token dari response jika ada
            if (response.csrf_token) {
                console.log('Updating CSRF token from:', currentCSRF, 'to:', response.csrf_token);
                currentCSRF = response.csrf_token;
                console.log('CSRF token updated after process:', currentCSRF);
            }
            
            if (response.success) {
                showAlert('success', 'Jurnal berhasil diproses!\nCore Ref: ' + (response.core_ref || '-'));
                
                // Reload table untuk update status dengan mempertahankan expand state
                setTimeout(function() {
                    console.log('Reloading table with preserved expand state for:', Array.from(expandedRows));
                    jurnalCaEscrowTable.ajax.reload(null, false); // false = stay on current page
                }, 1500);
            } else {
                showAlert('error', 'Gagal memproses jurnal: ' + (response.message || 'Unknown error'));
                
                // Reset button state on error
                if (resetButtonCallback) {
                    resetButtonCallback();
                }
            }
            
            enableAllActions();
            $('.child-details-container button').prop('disabled', false);
        },
        error: function(xhr, status, error) {
            console.log('AJAX error details:', {
                status: xhr.status,
                statusText: xhr.statusText,
                responseText: xhr.responseText,
                readyState: xhr.readyState,
                currentCSRF: currentCSRF,
                ajaxStatus: status,
                error: error
            });
            
            hideProgressModal();
            setBeforeUnloadWarning(false);
            enableAllActions();
            $('.child-details-container button').prop('disabled', false);
            
            let errorMessage = 'Terjadi kesalahan saat memproses jurnal';
            
            if (status === 'timeout') {
                errorMessage = 'Timeout! Transaksi mungkin masih berjalan. Silakan cek status transaksi.';
            } else if (xhr.status === 403 || xhr.status === 419) {
                console.log('CSRF Token expired during process, refreshing... Retry count:', currentRetryCount);
                
                if (currentRetryCount < 2) {
                    // Refresh CSRF token dan retry (tanpa reset button karena akan retry)
                    refreshCSRFToken().then(function() {
                        console.log('CSRF token refreshed, retrying process... Attempt:', currentRetryCount + 1);
                        setTimeout(function() {
                            prosesJurnalChild(currentChildData, currentKdSettle, currentRetryCount + 1, resetButtonCallback);
                        }, 500);
                    }).catch(function(refreshError) {
                        showAlert('error', 'Gagal memperbaharui token. Silakan refresh halaman.');
                        console.error('Failed to refresh CSRF after 403:', refreshError);
                        
                        // Reset button on CSRF refresh error
                        if (resetButtonCallback) {
                            resetButtonCallback();
                        }
                    });
                    return; // Exit without showing error message
                } else {
                    errorMessage = 'Session expired. Maksimal percobaan tercapai. Silakan refresh halaman.';
                    // Reset button after max retry
                    if (resetButtonCallback) {
                        resetButtonCallback();
                    }
                }
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
                
                // Update CSRF token jika ada di response error
                if (xhr.responseJSON.csrf_token) {
                    currentCSRF = xhr.responseJSON.csrf_token;
                    console.log('CSRF token updated from error response:', currentCSRF);
                }
            }
            
            showAlert('error', errorMessage);
            
            // Reset button state on any error
            if (resetButtonCallback) {
                resetButtonCallback();
            }
            
            console.error('Proses Jurnal Error:', {
                status: xhr.status,
                statusText: xhr.statusText,
                response: xhr.responseText,
                error: error,
                currentCSRF: currentCSRF
            });
        }
    });
}

// Legacy function for compatibility - redirect to child function
function prosesJurnal(rowData, rowIndex) {
    // If this is called with old format, try to adapt
    if (rowData.parent_kd_settle) {
        prosesJurnalChild(rowData, rowData.parent_kd_settle);
    } else {
        showAlert('warning', 'Silakan klik tombol expand untuk melihat detail dan memproses jurnal.');
    }
}

function disableAllActions() {
    // Disable semua tombol proses di halaman (yang tidak sedang processing)
    $('button[id^="btn-proses-"]:not(.btn-processing)').prop('disabled', true);
    $('.child-details-container button:not(.btn-processing)').prop('disabled', true);
    
    // Disable form filter
    $('#tanggal').prop('disabled', true);
    $('button[type="submit"]').prop('disabled', true);
    
    // Disable table interactions
    $('.dataTables_length select').prop('disabled', true);
    $('.dataTables_paginate .paginate_button').addClass('disabled');
}

function enableAllActions() {
    // Enable kembali semua tombol dan form, kecuali yang sedang processing
    $('button[id^="btn-proses-"]:not(.btn-processing)').prop('disabled', false);
    $('.child-details-container button:not(.btn-processing):not(.btn-hidden-temp)').prop('disabled', false);
    $('#tanggal').prop('disabled', false);
    $('button[type="submit"]').prop('disabled', false);
    $('.dataTables_length select').prop('disabled', false);
    $('.dataTables_paginate .paginate_button').removeClass('disabled');
}

function resetButtonState($btn, isReprocess) {
    if (isReprocess) {
        $btn.html('<i class="fal fa-redo"></i> Proses Ulang');
    } else {
        $btn.html('<i class="fal fa-play"></i> Proses Jurnal');
    }
    $btn.prop('disabled', false);
}

function showProgressModal(childData, kdSettle) {
    const modalContent = `
        <div class="modal fade" id="progressModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-md">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #6c5190 0%, #553d73 100%); color: white; border-bottom: 1px solid #553d73;">
                        <h5 class="modal-title">
                            <i class="fal fa-cog fa-spin"></i> Memproses Transaksi
                        </h5>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-3">
                            <div class="spinner-border" style="color: #6c5190;" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <h6>Sedang memproses jurnal:</h6>
                        <div class="alert" style="background-color: #ede8f2; border-color: #6c5190; color: #553d73;">
                            <strong>Kode Settle:</strong> ${kdSettle || childData.parent_kd_settle || childData.d_NO_REF}<br>
                            <strong>No Ref:</strong> ${childData.d_NO_REF}<br>
                            <strong>Amount:</strong> ${formatCurrency(childData.d_AMOUNT)}<br>
                            <strong>Debit:</strong> ${childData.d_DEBIT_ACCOUNT}<br>
                            <strong>Debit Name:</strong> ${childData.d_DEBIT_NAME || '-'}<br>
                            <strong>Credit:</strong> ${childData.d_CREDIT_ACCOUNT}<br>
                            <strong>Credit Name:</strong> ${childData.d_CREDIT_NAME || '-'}<br>
                            <strong>Status:</strong> ${childData.d_CODE_RES || '-'}<br>
                            <strong>Core Ref:</strong> ${childData.d_CORE_REF || '-'}<br>
                            <strong>Core DateTime:</strong> ${childData.d_CORE_DATETIME || '-'}
                        </div>
                        <div class="alert alert-warning">
                            <i class="fal fa-exclamation-triangle"></i>
                            <strong>PENTING:</strong><br>
                            Jangan tutup atau refresh browser!<br>
                            Transaksi sedang berlangsung...<br>
                            <small class="text-muted">
                                <i class="fal fa-lock"></i> Tombol proses lainnya disembunyikan untuk mencegah konflik
                            </small>
                        </div>
                        <div class="progress">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 100%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalContent);
    $('#progressModal').modal('show');
}

function hideProgressModal() {
    $('#progressModal').modal('hide');
    setTimeout(function() {
        $('#progressModal').remove();
    }, 500);
}

function setBeforeUnloadWarning(enable) {
    if (enable) {
        window.onbeforeunload = function() {
            return "Transaksi sedang berlangsung! Jika Anda menutup halaman ini, transaksi mungkin gagal.";
        };
    } else {
        window.onbeforeunload = null;
    }
}

function showAlert(type, message) {
    // Implementasi alert yang lebih baik (bisa diganti dengan toastr/sweet alert)
    const alertClass = {
        'success': 'alert-success',
        'error': 'alert-danger', 
        'warning': 'alert-warning',
        'info': 'alert-info'
    };
    
    const alertHtml = `
        <div class="alert ${alertClass[type]} alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;">
            <strong>${type.charAt(0).toUpperCase() + type.slice(1)}!</strong> ${message}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    `;
    
    $('body').append(alertHtml);
    
    // Auto hide after 5 seconds
    setTimeout(function() {
        $('.alert').alert('close');
    }, 5000);
}