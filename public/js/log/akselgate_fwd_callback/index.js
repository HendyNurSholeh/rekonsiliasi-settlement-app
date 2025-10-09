/**
 * Akselgate FWD Callback Log - DataTable Management
 * 
 * File ini handle:
 * - DataTable initialization dengan server-side processing
 * - Filter management (tanggal, kd_settle, status)
 * - Statistics panel update
 * - Detail modal untuk view callback data lengkap
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

        dataTable = $('#dt-callback-log').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '/log/callback/datatable',
                type: 'POST',
                data: function(d) {
                    // Add filter parameters
                    d.tanggal = $('#tanggal').val();
                    d.kd_settle = $('#kd_settle').val();
                    d.status = $('#status').val();
                    
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
                        if (type === 'display' && data) {
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
                        return data || '-';
                    }
                },
                { 
                    data: 'ref_number',
                    className: 'fw-700',
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                { 
                    data: 'kd_settle',
                    className: 'fw-700',
                    render: function(data, type, row) {
                        return data || '-';
                    }
                },
                { 
                    data: 'res_code',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            if (data === '00') {
                                return '<span class="badge badge-success">' + data + '</span>';
                            } else {
                                return '<span class="badge text-white" style="background-color: #e6a800;">' + data + '</span>';
                            }
                        }
                        return data || '-';
                    }
                },
                { 
                    data: 'res_coreref',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            return '<span class="text-muted small">' + data + '</span>';
                        }
                        return data || '-';
                    }
                },
                { 
                    data: 'status',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data == 'SUCCESS') {
                                return '<span class="badge badge-success"><i class="fal fa-check"></i> SUCCESS</span>';
                            } else if (data == 'FAILED') {
                                return '<span class="badge badge-danger"><i class="fal fa-times"></i> FAILED</span>';
                            }
                        }
                        return data || '-';
                    }
                },
                { 
                    data: 'is_processed',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display') {
                            if (data == 1) {
                                return '<span class="badge badge-success"><i class="fal fa-check-circle"></i> Yes</span>';
                            } else {
                                return '<span class="badge badge-warning"><i class="fal fa-clock"></i> No</span>';
                            }
                        }
                        return data;
                    }
                },
                { 
                    data: 'ip_address',
                    className: 'text-center',
                    render: function(data, type, row) {
                        if (type === 'display' && data) {
                            return '<span class="text-muted small">' + data + '</span>';
                        }
                        return data || '-';
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
     * Show detail modal with callback data
     */
    function showDetailModal(logId) {
        $.ajax({
            url: '/log/callback/detail/' + logId,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    const log = response.data;
                    
                    // Populate modal fields
                    $('#detail_id').text(log.id);
                    $('#detail_ref_number').text(log.ref_number || '-');
                    $('#detail_kd_settle').text(log.kd_settle || '-');
                    $('#detail_res_code').text(log.res_code || '-');
                    $('#detail_res_coreref').text(log.res_coreref || '-');
                    $('#detail_ip_address').text(log.ip_address || '-');
                    
                    // Status badge
                    const statusBadge = log.status == 'SUCCESS'
                        ? '<span class="badge badge-success"><i class="fal fa-check"></i> SUCCESS</span>'
                        : '<span class="badge badge-danger"><i class="fal fa-times"></i> FAILED</span>';
                    $('#detail_status').html(statusBadge);
                    
                    // Processed badge
                    const processedBadge = log.is_processed == 1
                        ? '<span class="badge badge-success"><i class="fal fa-check-circle"></i> Yes</span>'
                        : '<span class="badge badge-warning"><i class="fal fa-clock"></i> No</span>';
                    $('#detail_is_processed').html(processedBadge);
                    
                    // Timestamps
                    $('#detail_created_at').text(log.created_at ? new Date(log.created_at).toLocaleString('id-ID') : '-');
                    $('#detail_processed_at').text(log.processed_at ? new Date(log.processed_at).toLocaleString('id-ID') : '-');
                    
                    // Format JSON callback data
                    try {
                        const callbackData = log.callback_data ? JSON.stringify(JSON.parse(log.callback_data), null, 2) : 'Tidak ada data';
                        $('#detail_callback_data').text(callbackData);
                    } catch (e) {
                        $('#detail_callback_data').text(log.callback_data || 'Tidak ada data');
                    }
                    
                    // Show modal
                    $('#modalDetail').modal('show');
                } else {
                    toastr.error(response.message || 'Gagal memuat detail callback');
                }
            },
            error: function(xhr, status, error) {
                console.error('Error loading callback detail:', error);
                toastr.error('Terjadi kesalahan saat memuat detail callback');
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
            $('#kd_settle').val('');
            $('#status').val('').trigger('change');
            dataTable.ajax.reload();
        });

        // Detail button click
        $(document).on('click', '.btn-detail', function() {
            const logId = $(this).data('id');
            showDetailModal(logId);
        });

        // Tanggal change - auto reload (sama seperti Akselgate)
        $('#tanggal').on('change', function() {
            dataTable.ajax.reload();
        });
    });

})();
