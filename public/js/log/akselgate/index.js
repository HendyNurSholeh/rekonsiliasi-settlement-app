/**
 * Akselgate Log - DataTable Management
 * 
 * File ini handle:
 * - DataTable initialization
 * - Filter management (tanggal, transaction type, status, kd_settle)
 * - Detail modal untuk view request/response payload
 */

(function() {
    'use strict';

    // ============================================================================
    // CSRF TOKEN MANAGEMENT
    // ============================================================================
    
    /**
     * Update CSRF token from server response
     */
    function updateCsrfToken(newToken) {
        if (newToken) {
            const csrfInput = document.getElementById('txt_csrfname');
            if (csrfInput) {
                csrfInput.value = newToken;
            }
        }
    }

    /**
     * Get current CSRF token
     */
    function getCsrfToken() {
        const csrfInput = document.getElementById('txt_csrfname');
        return csrfInput ? csrfInput.value : '';
    }

    /**
     * Get CSRF token name
     */
    function getCsrfTokenName() {
        const csrfInput = document.getElementById('txt_csrfname');
        return csrfInput ? csrfInput.name : 'csrf_test_name';
    }

    // ============================================================================
    // DATATABLE INITIALIZATION
    // ============================================================================

    let dataTable = null;

    /**
     * Initialize DataTable
     */
    function initDataTable() {
        if (dataTable) {
            dataTable.destroy();
        }

        dataTable = $('#dt-akselgate-log').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/log/akselgate/datatable',
                type: 'POST',
                data: function(d) {
                    // Add filter parameters
                    d.tanggal = $('#tanggal').val();
                    d.transaction_type = $('#transaction_type').val();
                    d.status = $('#status').val();
                    d.kd_settle = $('#kd_settle').val();
                    
                    // Add CSRF token
                    d[getCsrfTokenName()] = getCsrfToken();
                },
                dataSrc: function(json) {
                    // Update CSRF token
                    if (json.csrf_token) {
                        updateCsrfToken(json.csrf_token);
                    }
                    return json.data;
                },
                error: function(xhr, error, thrown) {
                    console.error('DataTable error:', error, thrown);
                    toastr.error('Terjadi kesalahan saat memuat data');
                }
            },
            columns: [
                { 
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function(data, type, row, meta) {
                        // Calculate row number based on current page
                        return meta.row + meta.settings._iDisplayStart + 1;
                    }
                },
                { 
                    data: 'created_at',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            const date = new Date(data);
                            return date.toLocaleString('id-ID', {
                                year: 'numeric',
                                month: '2-digit',
                                day: '2-digit',
                                hour: '2-digit',
                                minute: '2-digit',
                                second: '2-digit'
                            });
                        }
                        return data;
                    }
                },
                { 
                    data: 'transaction_type',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data === 'CA_ESCROW') {
                                return '<span class="badge badge-info">CA to Escrow</span>';
                            } else if (data === 'ESCROW_BILLER_PL') {
                                return '<span class="badge badge-primary">Escrow to Biller PL</span>';
                            }
                        }
                        return data;
                    }
                },
                { 
                    data: 'kd_settle',
                    className: 'fw-700'
                },
                { 
                    data: 'request_id',
                    className: 'text-truncate',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            return '<span class="text-muted small">' + data + '</span>';
                        }
                        return data || '-';
                    }
                },
                { 
                    data: 'attempt_number',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data > 1) {
                                return '<span class="badge text-white" style="background-color: #e6a800;">' + data + '</span>';
                            }
                            return data;
                        }
                        return data;
                    }
                },
                { 
                    data: 'total_transaksi',
                    className: 'text-center'
                },
                { 
                    data: 'status_code_res',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data === '200' || data === '201') {
                                return '<span class="badge badge-success">' + data + '</span>';
                            } else if (data && data.startsWith('4')) {
                                return '<span class="badge text-white" style="background-color: #e6a800;">' + data + '</span>';
                            } else if (data && data.startsWith('5')) {
                                return '<span class="badge badge-danger">' + data + '</span>';
                            }
                        }
                        return data || '-';
                    }
                },
                { 
                    data: 'is_success',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data == 1) {
                                return '<span class="badge badge-success"><i class="fal fa-check"></i> Sukses</span>';
                            } else {
                                return '<span class="badge badge-danger"><i class="fal fa-times"></i> Gagal</span>';
                            }
                        }
                        return data;
                    }
                },
                { 
                    data: 'is_latest',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data == 1) {
                                return '<span class="badge badge-primary"><i class="fal fa-star"></i> Latest</span>';
                            } else {
                                return '<span class="badge badge-secondary">Old</span>';
                            }
                        }
                        return data;
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        return '<button type="button" class="btn btn-sm btn-info btn-detail" data-id="' + row.id + '">' +
                               '<i class="fal fa-eye"></i> Detail</button>';
                    }
                }
            ],
            order: [[1, 'desc']], // Default order by created_at DESC
            pageLength: 10,
            searching: false,
            lengthChange: false,
            responsive: true,
            dom: "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>"
        });
    }

    // ============================================================================
    // DETAIL MODAL
    // ============================================================================

    /**
     * Show detail modal with log data
     */
    function showDetailModal(logId) {
        $.ajax({
            url: '/log/akselgate/detail/' + logId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const log = response.data;
                    
                    // Populate modal fields
                    $('#detail_id').text(log.id);
                    $('#detail_created_at').text(new Date(log.created_at).toLocaleString('id-ID'));
                    $('#detail_transaction_type').text(log.transaction_type === 'CA_ESCROW' ? 'CA to Escrow' : 'Escrow to Biller PL');
                    $('#detail_kd_settle').text(log.kd_settle);
                    $('#detail_request_id').text(log.request_id || '-');
                    $('#detail_attempt_number').text(log.attempt_number);
                    $('#detail_total_transaksi').text(log.total_transaksi);
                    $('#detail_status_code_res').text(log.status_code_res || '-');
                    $('#detail_response_code').text(log.response_code || '-');
                    
                    // Status badge
                    const statusBadge = log.is_success == 1 
                        ? '<span class="badge badge-success"><i class="fal fa-check"></i> Sukses</span>'
                        : '<span class="badge badge-danger"><i class="fal fa-times"></i> Gagal</span>';
                    $('#detail_is_success').html(statusBadge);
                    
                    // Latest badge
                    const latestBadge = log.is_latest == 1
                        ? '<span class="badge badge-primary"><i class="fal fa-star"></i> Latest</span>'
                        : '<span class="badge badge-secondary">Old</span>';
                    $('#detail_is_latest').html(latestBadge);
                    
                    // Response message - hide if empty
                    if (log.response_message && log.response_message.trim() !== '') {
                        $('#detail_response_message').text(log.response_message);
                        $('#response_message_wrapper').show();
                    } else {
                        $('#response_message_wrapper').hide();
                    }
                    
                    // Format JSON payloads
                    try {
                        const requestPayload = log.request_payload ? JSON.stringify(JSON.parse(log.request_payload), null, 2) : 'Tidak ada data';
                        $('#detail_request_payload').text(requestPayload);
                    } catch (e) {
                        $('#detail_request_payload').text(log.request_payload || 'Tidak ada data');
                    }
                    
                    try {
                        const responsePayload = log.response_payload ? JSON.stringify(JSON.parse(log.response_payload), null, 2) : 'Tidak ada data';
                        $('#detail_response_payload').text(responsePayload);
                    } catch (e) {
                        $('#detail_response_payload').text(log.response_payload || 'Tidak ada data');
                    }
                    
                    // Show modal
                    $('#modalDetail').modal('show');
                } else {
                    toastr.error(response.message || 'Gagal memuat detail log');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading log detail:', error);
                toastr.error('Terjadi kesalahan saat memuat detail log');
            }
        });
    }

    // ============================================================================
    // EVENT HANDLERS
    // ============================================================================

    $(document).ready(function() {
        // Initialize DataTable
        initDataTable();

        // Filter form submit
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            dataTable.ajax.reload();
        });

        // Reset button
        $('#btnReset').on('click', function() {
            $('#tanggal').val(new Date().toISOString().split('T')[0]);
            $('#transaction_type').val('').trigger('change');
            $('#status').val('').trigger('change');
            $('#kd_settle').val('');
            dataTable.ajax.reload();
        });

        // Detail button click
        $(document).on('click', '.btn-detail', function() {
            const logId = $(this).data('id');
            showDetailModal(logId);
        });

        // Tanggal change - auto reload
        $('#tanggal').on('change', function() {
            dataTable.ajax.reload();
        });
    });

})();
