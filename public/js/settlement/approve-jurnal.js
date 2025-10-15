// APPROVE JURNAL SETTLEMENT SCRIPTS

let currentCSRF = window.appConfig?.csrfToken || '';
let approveJurnalTable;
let currentSettleData = null;

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
    return $.get(window.appConfig.baseUrl + 'get-csrf-token').then(function(response) {
        if (response.csrf_token) {
            currentCSRF = response.csrf_token;
            console.log('New CSRF token:', currentCSRF);
        }
    }).catch(function(error) {
        console.error('Failed to refresh CSRF:', error);
        setTimeout(function() {
            if (confirm('Session expired. Reload page?')) {
                location.reload();
            }
        }, 1000);
    });
}

$(document).ready(function() {
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        initializeDataTable();
        loadSummary();
    });

    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        const statusApprove = $('#filter_status_approve').val();

        console.log('Form submit - Tanggal:', tanggal);
        console.log('Form submit - Status Approve:', statusApprove);

        if (tanggal && approveJurnalTable) {
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (statusApprove !== '') {
                url.searchParams.set('status_approve', statusApprove);
            } else {
                url.searchParams.delete('status_approve');
            }
            window.history.pushState({}, '', url);
            console.log('Updated URL:', url.toString());
            approveJurnalTable.ajax.reload(null, false);
            loadSummary();
        }
    });
});

function initializeDataTable() {
    approveJurnalTable = $('#approveJurnalTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: window.appConfig.baseUrl + 'settlement/approve-jurnal/datatable',
            type: 'GET',
            data: function(d) {
                d.tanggal = $('#tanggal').val() || window.appConfig.tanggalRekon;
                d.status_approve = $('#filter_status_approve').val();
                console.log('DataTable request data:', d);
                return d;
            },
            error: function(xhr, error, thrown) {
                console.error('DataTable AJAX Error:', error, thrown, xhr.responseText);
                if (xhr.status === 403 || xhr.status === 419) {
                    console.log('CSRF error in DataTable, refreshing token...');
                    refreshCSRFToken().then(function() {
                        console.log('CSRF refreshed, reloading DataTable...');
                        approveJurnalTable.ajax.reload();
                    });
                }
            },
            dataSrc: function(json) {
                console.log('DataTable response:', json);
                return json.data;
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
            {
                data: 'TGL_DATA',
                name: 'TGL_DATA',
                render: function(data) {
                    if (data) {
                        return new Date(data).toLocaleDateString('id-ID');
                    }
                    return '';
                }
            },
            { data: 'NAMA_PRODUK', name: 'NAMA_PRODUK' },
            {
                data: 'KD_SETTLE',
                name: 'KD_SETTLE',
                render: function(data) {
                    return '<code>' + (data || '') + '</code>';
                }
            },
            {
                data: 'AMOUNT_NET_DB_ECR',
                name: 'AMOUNT_NET_DB_ECR',
                render: function(data, type, row) {
                    const formatted = formatCurrency(data);
                    if (!row.NET_MATCH) {
                        return '<span class="text-danger font-weight-bold">' + formatted + '</span>';
                    }
                    return '<span class="text-success font-weight-bold">' + formatted + '</span>';
                }
            },
            {
                data: 'AMOUNT_NET_KR_ECR',
                name: 'AMOUNT_NET_KR_ECR',
                render: function(data, type, row) {
                    const formatted = formatCurrency(data);
                    if (!row.NET_MATCH) {
                        return '<span class="text-danger font-weight-bold">' + formatted + '</span>';
                    }
                    return '<span class="text-success font-weight-bold">' + formatted + '</span>';
                }
            },
            {
                data: 'STAT_APPROVER',
                name: 'STAT_APPROVER',
                render: function(data) {
                    if (data === '-1') {
                        return '<span class="badge badge-danger">Net Amount Beda</span>';
                    } else if (data === '1') {
                        return '<span class="badge badge-success">Disetujui</span>';
                    } else if (data === '9') {
                        return '<span class="badge badge-warning">Ditolak</span>';
                    } else if (data === '0') {
                        return '<span class="badge text-white" style="background-color: #f9911b;">Pending</span>';
                    } else {
                        return '<span class="badge text-white" style="background-color: #f9911b;">Pending</span>';
                    }
                }
            },
            { 
                data: 'APPROVAL_INFO', 
                name: 'APPROVAL_INFO',
                render: function(data) {
                    return data || '-';
                }
            },
            {
                data: null,
                name: 'action',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    // Disable button if net amount doesn't match OR already approved/rejected
                    const isDisabled = !row.NET_MATCH || row.STAT_APPROVER === '1' || row.STAT_APPROVER === '9' || row.STAT_APPROVER === '-1';
                    const btnClass = isDisabled ? 'btn-secondary' : 'btn-primary';
                    const disabledAttr = isDisabled ? 'disabled' : '';
                    const title = !row.NET_MATCH ? 'Tidak bisa approve: Net amount debet dan credit tidak sama' : 
                                  row.STAT_APPROVER === '1' ? 'Sudah disetujui' :
                                  row.STAT_APPROVER === '9' ? 'Sudah ditolak' : 
                                  'Klik untuk approve';
                    
                    return '<button type="button" class="btn btn-sm ' + btnClass + ' btn-view-detail" ' +
                           'data-kd-settle="' + (row.KD_SETTLE || '') + '" ' +
                           'data-net-match="' + (row.NET_MATCH ? 'true' : 'false') + '" ' +
                           disabledAttr + ' title="' + title + '">' +
                           '<i class="fal fa-check-circle"></i> Approve</button>';
                }
            }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[1, 'desc']],
        orderMulti: false,
        stateSave: false,
        deferRender: true,
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
            $('.btn-view-detail').off('click').on('click', function() {
                const $btn = $(this);
                const kdSettle = $btn.data('kd-settle');
                const netMatch = $btn.data('net-match');
                
                // If button is disabled, don't proceed
                if ($btn.prop('disabled')) {
                    if (!netMatch) {
                        toastr["error"]('Tidak bisa approve: Net amount debet dan credit tidak sama');
                    }
                    return;
                }
                
                $btn.prop('disabled', true);
                openApprovalModal(kdSettle, $btn, netMatch);
            });
        }
    });
}

function openApprovalModal(kdSettle, $btn, netMatch) {
    if (!kdSettle) {
        toastr["error"]('Kode settle tidak ditemukan');
        if ($btn) $btn.prop('disabled', false);
        return;
    }
    
    // If net doesn't match, show error and don't open modal
    if (netMatch === false || netMatch === 'false') {
        toastr["error"]('Tidak bisa approve: Net amount debet dan credit tidak sama');
        if ($btn) $btn.prop('disabled', false);
        return;
    }
    
    refreshCSRFToken().then(function() {
        $.ajax({
            url: window.appConfig.baseUrl + 'settlement/approve-jurnal/detail',
            type: 'POST',
            data: { kd_settle: kdSettle },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                if (response.success) {
                    currentSettleData = {
                        kd_settle: kdSettle,
                        settle_info: response.settle_info
                    };
                    const settleInfo = response.settle_info;
                    const tglSettle = new Date(settleInfo.TGL_DATA).toLocaleDateString('id-ID');
                    $('#modalTitle').text(`Jurnal Settlement tanggal ${tglSettle} untuk produk ${settleInfo.NAMA_PRODUK}`);
                    $('#modal_kd_settle').val(kdSettle);
                    $('#modal_nama_produk').val(settleInfo.NAMA_PRODUK);
                    $('#modal_total_amount').val(formatCurrency(settleInfo.TOT_JURNAL_KR_ECR));
                    
                    // Display net amounts
                    $('#modal_net_debet').val(formatCurrency(settleInfo.AMOUNT_NET_DB_ECR || 0));
                    $('#modal_net_credit').val(formatCurrency(settleInfo.AMOUNT_NET_KR_ECR || 0));
                    
                    // Show warning if net doesn't match
                    if (!settleInfo.NET_MATCH) {
                        const diff = Math.abs(settleInfo.NET_DIFF || 0);
                        $('#netWarning').html(
                            '<div class="alert alert-danger">' +
                            '<i class="fas fa-exclamation-triangle"></i> ' +
                            '<strong>Perhatian!</strong> Net amount debet dan credit tidak sama. ' +
                            'Selisih: ' + formatCurrency(diff) + '. ' +
                            'Approval tidak dapat dilakukan.' +
                            '</div>'
                        ).show();
                    } else {
                        $('#netWarning').hide();
                    }
                    
                    const detailBody = $('#detailJurnalBody');
                    detailBody.empty();
                    if (response.detail_data && response.detail_data.length > 0) {
                        response.detail_data.forEach(function(detail) {
                            const row = `
                                <tr class="text-xs">
                                    <td>${detail.JENIS_SETTLE || ''}</td>
                                    <td>${detail.IDPARTNER || ''}</td>
                                    <td class="text-center">${detail.CORE || ''}</td>
                                    <td>${detail.DEBIT_ACCOUNT || ''}</td>
                                    <td>${detail.DEBIT_NAME || ''}</td>
                                    <td class="text-center">${detail.CREDIT_CORE || ''}</td>
                                    <td>${detail.CREDIT_ACCOUNT || ''}</td>
                                    <td>${detail.CREDIT_NAME || ''}</td>
                                    <td class="text-right text-nowrap">${formatCurrency(detail.AMOUNT)}</td>
                                    <td>${detail.DESCRIPTION || ''}</td>
                                    <td>${detail.REF_NUMBER || ''}</td>
                                </tr>
                            `;
                            detailBody.append(row);
                        });
                    } else {
                        detailBody.append('<tr><td colspan="11" class="text-center">Tidak ada detail jurnal</td></tr>');
                    }
                    let currentRowData = null;
                    if (approveJurnalTable) {
                        const tableData = approveJurnalTable.rows().data();
                        for (let i = 0; i < tableData.length; i++) {
                            if (tableData[i].KD_SETTLE === kdSettle) {
                                currentRowData = tableData[i];
                                break;
                            }
                        }
                    }
                    
                    // Hide approval buttons if:
                    // 1. Net doesn't match
                    // 2. Already approved (1) or rejected (9)
                    // 3. Status is -1 (net beda)
                    if (!settleInfo.NET_MATCH || 
                        (currentRowData && (currentRowData.STAT_APPROVER === '1' || 
                                           currentRowData.STAT_APPROVER === '9' || 
                                           currentRowData.STAT_APPROVER === '-1'))) {
                        $('#approvalButtons').hide();
                    } else {
                        $('#approvalButtons').show();
                    }
                    
                    $('#approvalModal').modal('show');
                    if ($btn) $btn.prop('disabled', false);
                } else {
                    toastr["error"](response.message);
                    if ($btn) $btn.prop('disabled', false);
                }
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    // toastr["error"]('Session expired. Please try again.');
                } else {
                    toastr["error"]('Terjadi kesalahan saat mengambil detail jurnal');
                }
                if ($btn) $btn.prop('disabled', false);
            }
        });
    });
}

function processApproval(action) {
    if (!currentSettleData) {
        toastr["error"]('Data settlement tidak ditemukan');
        return;
    }
    const actionText = action === 'approve' ? 'menyetujui' : 'menolak';
    const tanggalRekon = $('#tanggal').val() || window.appConfig.tanggalRekon;
    const namaProduk = currentSettleData.settle_info ? currentSettleData.settle_info.NAMA_PRODUK : '';
    if (!namaProduk) {
        toastr["error"]('Nama produk tidak ditemukan. Silakan coba lagi.');
        return;
    }
    const $approveBtn = $('#approveBtn');
    const $rejectBtn = $('#rejectBtn');
    $approveBtn.prop('disabled', true);
    $rejectBtn.prop('disabled', true);
    if (action === 'approve') {
        $approveBtn.html('<i class="fal fa-spinner fa-spin"></i> Menyetujui...');
    } else {
        $rejectBtn.html('<i class="fal fa-spinner fa-spin"></i> Menolak...');
    }
    refreshCSRFToken().then(function() {
        $.ajax({
            url: window.appConfig.baseUrl + 'settlement/approve-jurnal/process',
            type: 'POST',
            data: {
                kd_settle: currentSettleData.kd_settle,
                tanggal_rekon: tanggalRekon,
                nama_produk: namaProduk,
                action: action
            },
            dataType: 'json',
            success: function(response) {
                if (response.csrf_token) {
                    currentCSRF = response.csrf_token;
                }
                if (response.success) {
                    toastr["success"](response.message);
                    $('#approvalModal').modal('hide');
                    if (approveJurnalTable) {
                        approveJurnalTable.ajax.reload(null, false);
                    }
                    loadSummary();
                } else {
                    toastr["error"](response.message);
                }
                $approveBtn.prop('disabled', false).html('<i class="fal fa-check-circle"></i> Setujui');
                $rejectBtn.prop('disabled', false).html('<i class="fal fa-times-circle"></i> Tolak');
            },
            error: function(xhr) {
                if (xhr.status === 403) {
                    // toastr["error"]('Session expired. Please try again.');
                } else {
                    toastr["error"](`Terjadi kesalahan saat ${actionText} jurnal`);
                }
                $approveBtn.prop('disabled', false).html('<i class="fal fa-check-circle"></i> Setujui');
                $rejectBtn.prop('disabled', false).html('<i class="fal fa-times-circle"></i> Tolak');
            }
        });
    });
}

function loadSummary() {
    const tanggal = $('#tanggal').val() || window.appConfig.tanggalRekon;
    $.ajax({
        url: window.appConfig.baseUrl + 'settlement/approve-jurnal/summary',
        type: 'GET',
        data: { tanggal: tanggal },
        dataType: 'json',
        success: function(response) {
            if (response.success && response.summary) {
                const summary = response.summary;
                $('#totalJurnal').text(summary.total_jurnal || 0);
                $('#approvedJurnal').text(summary.approved || 0);
                $('#pendingJurnal').text(summary.pending || 0);
            }
        },
        error: function(xhr) {
            console.error('Error loading summary:', xhr);
        }
    });
}

function formatCurrency(amount) {
    const num = parseFloat(String(amount || 0).replace(/,/g, ''));
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
}

function resetFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    url.searchParams.delete('status_approve');
    window.location.href = url.pathname + url.search;
}

// Expose for inline HTML usage
window.openApprovalModal = openApprovalModal;
window.processApproval = processApproval;
window.resetFilters = resetFilters;
