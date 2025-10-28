// ==========================================
// CALLBACK LOG MODAL MODULE
// ==========================================

(function() {
    'use strict';

    let currentKdSettle = null;

    /**
     * Initialize akselgate log modal - langsung tampilkan detail log terbaru
     */
    function initAkselgateLogModal(kdSettle) {
        currentKdSettle = kdSettle;

        // Set modal title
        $('#modalDetailAkselgate #akselgate-log-title').text(kdSettle);

        // Show modal dengan loading
        $('#modalDetailAkselgate').modal('show');

        // Add loading indicator
        $('#modalDetailAkselgate .modal-body').prepend('<div id="loading-indicator" class="text-center my-4"><i class="fa fa-spinner fa-spin fa-2x"></i><br><span>Memuat data log...</span></div>');

        // Fetch detail log terbaru untuk kode settle ini
        fetchLatestAkselgateLog(kdSettle);
    }

    /**
     * Fetch latest akselgate log for specific kd_settle
     */
    function fetchLatestAkselgateLog(kdSettle) {
        $.ajax({
            url: '/settlement/jurnal-escrow-biller-pl/akselgate-log/' + kdSettle,
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    populateAkselgateLogDetail(response.data);
                } else {
                    showNoLogFound();
                }

                // Update CSRF token
                if (response.csrf_token) {
                    updateCsrfToken(response.csrf_token);
                }
            },
            error: function(xhr, status, error) {
                console.error('Error fetching latest akselgate log:', error);
                toastr.error('Terjadi kesalahan saat memuat data akselgate log');
                showNoLogFound();
            }
        });
    }

    /**
     * Populate akselgate log detail modal
     */
    function populateAkselgateLogDetail(log) {
        // Remove loading indicator
        $('#modalDetailAkselgate #loading-indicator').remove();

        // Populate modal fields
        $('#modalDetailAkselgate #detail_id').text(log.id);
        $('#modalDetailAkselgate #detail_created_at').text(new Date(log.created_at).toLocaleString('id-ID'));
        $('#modalDetailAkselgate #detail_transaction_type').text(log.transaction_type === 'CA_ESCROW' ? 'CA to Escrow' : 'Escrow to Biller PL');
        $('#modalDetailAkselgate #detail_kd_settle').text(log.kd_settle);
        $('#modalDetailAkselgate #detail_request_id').text(log.request_id || '-');
        $('#modalDetailAkselgate #detail_attempt_number').text(log.attempt_number);
        $('#modalDetailAkselgate #detail_total_transaksi').text(log.total_transaksi);
        $('#modalDetailAkselgate #detail_status_code_res').text(log.status_code_res || '-');
        $('#modalDetailAkselgate #detail_response_code').text(log.response_code || '-');

        // Status badge
        const statusBadge = log.is_success == 1
            ? '<span class="badge badge-success"><i class="fal fa-check"></i> Sukses</span>'
            : '<span class="badge badge-danger"><i class="fal fa-times"></i> Gagal</span>';
        $('#modalDetailAkselgate #detail_is_success').html(statusBadge);

        // Latest badge
        const latestBadge = log.is_latest == 1
            ? '<span class="badge badge-primary"><i class="fal fa-star"></i> Latest</span>'
            : '<span class="badge badge-secondary">Old</span>';
        $('#modalDetailAkselgate #detail_is_latest').html(latestBadge);

        // Response message - hide if empty
        if (log.response_message && log.response_message.trim() !== '') {
            $('#modalDetailAkselgate #detail_response_message').text(log.response_message);
            $('#modalDetailAkselgate #response_message_wrapper').show();
        } else {
            $('#modalDetailAkselgate #response_message_wrapper').hide();
        }

        // Format JSON payloads
        try {
            const requestPayload = log.request_payload ? JSON.stringify(JSON.parse(log.request_payload), null, 2) : 'Tidak ada data';
            $('#modalDetailAkselgate #detail_request_payload').text(requestPayload);
        } catch (e) {
            $('#modalDetailAkselgate #detail_request_payload').text(log.request_payload || 'Tidak ada data');
        }

        try {
            const responsePayload = log.response_payload ? JSON.stringify(JSON.parse(log.response_payload), null, 2) : 'Tidak ada data';
            $('#modalDetailAkselgate #detail_response_payload').text(responsePayload);
        } catch (e) {
            $('#modalDetailAkselgate #detail_response_payload').text(log.response_payload || 'Tidak ada data');
        }
    }

    /**
     * Show message when no log found
     */
    function showNoLogFound() {
        // Remove loading indicator
        $('#modalDetailAkselgate #loading-indicator').remove();

        // Reset all fields
        $('#modalDetailAkselgate #detail_id').text('-');
        $('#modalDetailAkselgate #detail_created_at').text('-');
        $('#modalDetailAkselgate #detail_transaction_type').text('-');
        $('#modalDetailAkselgate #detail_kd_settle').text(currentKdSettle);
        $('#modalDetailAkselgate #detail_request_id').text('-');
        $('#modalDetailAkselgate #detail_attempt_number').text('-');
        $('#modalDetailAkselgate #detail_total_transaksi').text('-');
        $('#modalDetailAkselgate #detail_status_code_res').text('-');
        $('#modalDetailAkselgate #detail_response_code').text('-');
        $('#modalDetailAkselgate #detail_is_success').html('<span class="badge badge-secondary">Tidak ada data</span>');
        $('#modalDetailAkselgate #detail_is_latest').html('<span class="badge badge-secondary">Tidak ada data</span>');
        $('#modalDetailAkselgate #response_message_wrapper').hide();
        $('#modalDetailAkselgate #detail_request_payload').text('Belum ada log akselgate untuk kode settle ini');
        $('#modalDetailAkselgate #detail_response_payload').text('Belum ada log akselgate untuk kode settle ini');
    }

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
    // EVENT HANDLERS
    // ============================================================================

    $(document).ready(function() {
        // Modal hidden - reset fields
        $('#modalDetailAkselgate').on('hidden.bs.modal', function() {
            // Reset fields when modal is closed
            $('#modalDetailAkselgate #detail_id').text('-');
            $('#modalDetailAkselgate #detail_created_at').text('-');
            $('#modalDetailAkselgate #detail_transaction_type').text('-');
            $('#modalDetailAkselgate #detail_kd_settle').text('-');
            $('#modalDetailAkselgate #detail_request_id').text('-');
            $('#modalDetailAkselgate #detail_attempt_number').text('-');
            $('#modalDetailAkselgate #detail_total_transaksi').text('-');
            $('#modalDetailAkselgate #detail_status_code_res').text('-');
            $('#modalDetailAkselgate #detail_response_code').text('-');
            $('#modalDetailAkselgate #detail_is_success').html('-');
            $('#modalDetailAkselgate #detail_is_latest').html('-');
            $('#modalDetailAkselgate #response_message_wrapper').hide();
            $('#modalDetailAkselgate #detail_request_payload').text('-');
            $('#modalDetailAkselgate #detail_response_payload').text('-');
        });
    });

    // ============================================================================
    // GLOBAL FUNCTIONS
    // ============================================================================

    // Expose function to global scope
    window.initAkselgateLogModal = initAkselgateLogModal;

})();