// Super Simple but Robust CSRF Management
let currentCSRF = window.appConfig?.csrfToken || '';

// Global AJAX setup untuk auto-inject CSRF
$.ajaxSetup({
    beforeSend: function(xhr, settings) {
        // Untuk semua POST request, tambahkan CSRF
        if (settings.type === 'POST') {
            // Jika data adalah FormData
            if (settings.data instanceof FormData) {
                settings.data.append('csrf_test_name', currentCSRF);
            } 
            // Jika data adalah string biasa
            else {
                const separator = settings.data ? '&' : '';
                settings.data = (settings.data || '') + separator + 'csrf_test_name=' + encodeURIComponent(currentCSRF);
            }
        }
    }
});

// Global error handler untuk CSRF expired
$(document).ajaxError(function(event, xhr, settings) {
    if (xhr.status === 403 || xhr.status === 419) {
        console.log('CSRF Token expired, refreshing...');
        refreshCSRFToken().then(function() {
            console.log('CSRF refreshed, retrying request...');
            // Retry the request with new token
            if (!settings._retried) {
                settings._retried = true;
                $.ajax(settings);
            }
        });
    }
});

// Function untuk refresh CSRF token
function refreshCSRFToken() {
    return $.get(window.appConfig.baseUrl + 'get-csrf-token').then(function(response) {
        if (response.csrf_token) {
            currentCSRF = response.csrf_token;
            console.log('New CSRF token:', currentCSRF);
        }
    }).catch(function(error) {
        console.error('Failed to refresh CSRF:', error);
        // Fallback: reload page if can't refresh token
        setTimeout(function() {
            if (confirm('Session expired. Reload page?')) {
                location.reload();
            }
        }, 1000);
    });
}

// DataTable instance
let disputeTable;

$(document).ready(function() {
    // Refresh CSRF token saat page load untuk memastikan token fresh
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        
        // Initialize DataTable dengan AJAX
        initializeDataTable();
    });
    
    // Handle form submit for date filter
    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        const statusBiller = $('#filter_status_biller').val();
        const statusCore = $('#filter_status_core').val();
        const settleVerifikasi = $('#filter_settle_verifikasi').val();
        const idPelanggan = $('#filter_id_pelanggan').val();
        
        console.log('Form submit - Tanggal:', tanggal);
        console.log('Form submit - Status Biller:', statusBiller);
        console.log('Form submit - Status Core:', statusCore);
        console.log('Form submit - Settle Verifikasi:', settleVerifikasi);
        console.log('Form submit - ID Pelanggan:', idPelanggan);
        
        if (tanggal && disputeTable) {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (statusBiller !== '') {
                url.searchParams.set('status_biller', statusBiller);
            } else {
                url.searchParams.delete('status_biller');
            }
            if (statusCore !== '') {
                url.searchParams.set('status_core', statusCore);
            } else {
                url.searchParams.delete('status_core');
            }
            if (settleVerifikasi !== '') {
                url.searchParams.set('settle_verifikasi', settleVerifikasi);
            } else {
                url.searchParams.delete('settle_verifikasi');
            }
            if (idPelanggan !== '') {
                url.searchParams.set('id_pelanggan', idPelanggan);
            } else {
                url.searchParams.delete('id_pelanggan');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload DataTable with new filters
            disputeTable.ajax.reload();
        }
    });
    
    // Reset tombol simpan ketika modal ditutup
    $('#disputeModal').on('hidden.bs.modal', function () {
        const saveButton = $('.btn-primary[onclick="saveDispute()"]');
        saveButton.prop('disabled', false).html('<i class="fal fa-save"></i> Simpan');
    });
});

function initializeDataTable() {
    disputeTable = $('#disputeTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appConfig.baseUrl + "rekon/process/laporan-transaksi-detail/datatable",
            type: 'GET',
            data: function(d) {
                // Add current date filter
                d.tanggal = $('#tanggal').val() || window.appConfig.tanggalData;
                // Add status filters
                d.status_biller = $('#filter_status_biller').val();
                d.status_core = $('#filter_status_core').val();
                d.settle_verifikasi = $('#filter_settle_verifikasi').val();
                d.id_pelanggan = $('#filter_id_pelanggan').val();
                console.log('DataTable request data:', d);
                console.log('Status Biller:', d.status_biller);
                console.log('Status Core:', d.status_core);
                console.log('Settle Verifikasi:', d.settle_verifikasi);
                console.log('ID Pelanggan:', d.id_pelanggan);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        disputeTable.ajax.reload();
                    });
                }
            }
        },
        columns: [
            { 
                data: null,
                name: 'no',
                orderable: false,
                searchable: false,
                render: function(data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { data: 'IDPARTNER', name: 'IDPARTNER' },
            { 
                data: 'TERMINALID', 
                name: 'TERMINALID',
                render: function(data, type, row) {
                    return data && data.trim() !== '' ? data : '-';
                }
            },
            { 
                data: 'PRODUK', 
                name: 'PRODUK',
                render: function(data, type, row) {
                    return '<code>' + (data || '') + '</code>';
                }
            },
            { data: 'IDPEL', name: 'IDPEL' },
            { 
                data: 'RP_BILLER_TAG', 
                name: 'RP_BILLER_TAG',
                render: function(data, type, row) {
                    const amount = parseFloat(String(data || 0).replace(/,/g, ''));
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
                }
            },
            { 
                data: 'STATUS_BILLER', 
                name: 'STATUS_BILLER',
                render: function(data, type, row) {
                    const status = parseInt(data || 0);
                    if (status === 0) {
                        return '<span class="badge text-white" style="background-color: #f9911b;">Pending</span>';
                    } else if (status === 1) {
                        return '<span class="badge badge-success">Sukses</span>';
                    } else if (status === 2) {
                        return '<span class="badge badge-danger">Gagal</span>';
                    } else {
                        return '<span class="badge badge-light">' + status + '</span>';
                    }
                }
            },
            { 
                data: 'STATUS_CORE', 
                name: 'STATUS_CORE',
                render: function(data, type, row) {
                    const status = parseInt(data || 0);
                    if (status === 0) {
                        return '<span class="badge badge-danger">Tidak Terdebet</span>';
                    } else if (status === 1) {
                        return '<span class="badge badge-primary">Terdebet</span>';
                    } else if (status === 2) {
                        return '<span class="badge text-white" style="background-color: #f9911b;">Belum Di Verifikasi</span>';
                    } else {
                        return '<span class="badge badge-light">' + status + '</span>';
                    }
                }
            },
            { 
                data: 'v_SETTLE_VERIFIKASI', 
                name: 'v_SETTLE_VERIFIKASI',
                render: function(data, type, row) {
                    const status = parseInt(data || 0);
                    if (status === 0) {
                        return '<span class="badge badge-secondary">Belum Verif</span>';
                    } else if (status === 1) {
                        return '<span class="badge badge-success">Dilimpahkan</span>';
                    } else if (status === 8) {
                        return '<span class="badge badge-info">Pengembalian ke Nasabah</span>';
                    } else if (status === 9) {
                        return '<span class="badge badge-danger">Tidak Dilimpahkan</span>';
                    } else {
                        return '<span class="badge badge-light">' + status + '</span>';
                    }
                }
            },
            { 
                data: 'v_ID', 
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return '<button type="button" class="btn btn-sm btn-primary btn-proses" data-id="' + (data || '') + '">' +
                           '<i class="fal fa-tools"></i> Proses</button>';
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'asc']],
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
        responsive: true,
        searching: false,
        dom: '<"row"<"col-sm-12">>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-5"i><"col-sm-7"p>>',
        drawCallback: function(settings) {
            // Re-attach event handlers untuk button yang baru di-render
            $('.btn-proses').off('click').on('click', function() {
                const id = $(this).data('id');
                openDisputeModal(id);
            });
        }
    });
}

function openDisputeModal(id) {
    if (!id) {
        showAlert('error', 'ID tidak ditemukan');
        return;
    }

    // Clear form
    $('#disputeForm')[0].reset();
    $('#dispute_id').val(id);
    
    // Reset tombol simpan ke kondisi normal
    const saveButton = $('.btn-primary[onclick="saveDispute()"]');
    saveButton.prop('disabled', false).html('<i class="fal fa-save"></i> Simpan');
    
    // Refresh CSRF token terlebih dahulu untuk memastikan valid
    refreshCSRFToken().then(function() {
        // Get dispute detail - CSRF otomatis ditambahkan dengan token fresh
        $.ajax({
            url: window.appConfig.baseUrl + "rekon/process/laporan-transaksi-detail/detail",
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                // Update CSRF jika ada di response
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                
                if (response.success) {
                    const data = response.data;
                    
                    // Fill readonly fields
                    $('#modal_idpartner').val(data.IDPARTNER || '');
                    $('#modal_terminalid').val(data.TERMINALID && data.TERMINALID.trim() !== '' ? data.TERMINALID : '-');
                    $('#modal_is_direct_fee').val(data.v_IS_DIRECT_FEE || '');
                    $('#modal_produk').val(data.v_GROUP_PRODUK || '');
                    $('#modal_idpel').val(data.IDPEL || '');
                    $('#modal_rp_pokok').val(formatNumber(data.RP_BILLER_POKOK || 0));
                    $('#modal_rp_denda').val(formatNumber(data.RP_BILLER_DENDA || 0));
                    $('#modal_rp_fee_struk').val(formatNumber(data.RP_FEE_STRUK || 0));
                    $('#modal_rp_amount_struk').val(formatNumber(data.RP_AMOUNT_STRUK || 0));
                    $('#modal_rp_tag').val(formatNumber(data.RP_BILLER_TAG || 0));
                    
                    // Auto-select channel berdasarkan IDPARTNER
                    $('#modal_channel').val(data.IDPARTNER || '');
                    
                    // Set current values for radio buttons
                    $('input[name="status_biller"][value="' + (data.STATUS || '0') + '"]').prop('checked', true);
                    $('input[name="status_core"][value="' + (data.v_STAT_CORE_AGR || '0') + '"]').prop('checked', true);
                    
                    // Auto-select status settlement hanya jika bukan "Belum Verif" (0)
                    const settleStatus = data.v_SETTLE_VERIFIKASI || '0';
                    if (settleStatus !== '0') {
                        $('input[name="status_settlement"][value="' + settleStatus + '"]').prop('checked', true);
                    } else {
                        // Jika "Belum Verif", tidak ada yang terselect
                        $('input[name="status_settlement"]').prop('checked', false);
                    }
                    
                    $('#disputeModal').modal('show');
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    showAlert('error', 'Session expired. Please try again.');
                } else {
                    showAlert('error', 'Terjadi kesalahan saat mengambil data');
                }
            }
        });
    }).catch(function(error) {
        showAlert('error', 'Gagal memperbarui token. Silakan refresh halaman.');
    });
}

function saveDispute() {
    // Disable tombol simpan untuk mencegah double click
    const saveButton = $('.btn-primary[onclick="saveDispute()"]');
    const originalText = saveButton.html();
    saveButton.prop('disabled', true)
             .html('<i class="fal fa-spinner fa-spin"></i> Menyimpan...');
    
    const formData = new FormData($('#disputeForm')[0]);
    
    // Validate required fields
    if (!formData.get('idpartner') || !formData.get('status_biller') || 
        !formData.get('status_core') || !formData.get('status_settlement')) {
        showAlert('warning', 'Mohon lengkapi semua field yang wajib diisi');
        // Re-enable tombol jika validasi gagal
        saveButton.prop('disabled', false).html(originalText);
        return;
    }
    
    // Refresh CSRF token terlebih dahulu untuk memastikan valid
    refreshCSRFToken().then(function() {
        // CSRF otomatis ditambahkan oleh ajaxSetup dengan token fresh
        $.ajax({
            url: window.appConfig.baseUrl + "rekon/process/laporan-transaksi-detail/update",
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // Re-enable tombol setelah request selesai
                saveButton.prop('disabled', false).html(originalText);
                
                // Update CSRF jika ada di response
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                
                if (response.success) {
                    showAlert('success', response.message);
                    $('#disputeModal').modal('hide');
                    // Reload DataTable instead of page
                    if (disputeTable) {
                        disputeTable.ajax.reload();
                    }
                } else {
                    showAlert('error', response.message);
                }
            },
            error: function(xhr) {
                // Re-enable tombol jika terjadi error
                saveButton.prop('disabled', false).html(originalText);
                
                if (xhr.status === 403) {
                    showAlert('error', 'Session expired. Please try again.');
                } else {
                    showAlert('error', 'Terjadi kesalahan saat menyimpan data');
                }
            }
        });
    }).catch(function(error) {
        // Re-enable tombol jika CSRF refresh gagal
        saveButton.prop('disabled', false).html(originalText);
        showAlert('error', 'Gagal memperbarui token. Silakan refresh halaman.');
    });
}

function formatNumber(num) {
    // Convert string to number first, removing any existing commas
    const cleanNum = parseFloat(String(num).replace(/,/g, '')) || 0;
    return new Intl.NumberFormat('id-ID').format(cleanNum);
}

function showAlert(type, message) {
    switch(type) {
        case 'success':
            toastr["success"](message);
            break;
        case 'error':
            toastr["error"](message);
            break;
        case 'warning':
            toastr["warning"](message);
            break;
        case 'info':
        default:
            toastr["info"](message);
            break;
    }
}

function resetFilters() {
   // Remove 'tanggal' parameter from URL and redirect
   const url = new URL(window.location);
   url.searchParams.delete('tanggal');
   url.searchParams.delete('status_biller');
   url.searchParams.delete('status_core');
   url.searchParams.delete('settle_verifikasi');
   url.searchParams.delete('id_pelanggan');
   window.location.href = url.pathname + url.search;
}