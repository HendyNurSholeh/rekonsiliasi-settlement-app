// ==========================================
// JURNAL CA ESCROW - BATCH PROCESS MODULE
// ==========================================

/**
 * Update button state menjadi "Sudah Diproses"
 * @param {jQuery} $button - jQuery button element
 * @param {string} kdSettle - Kode settlement
 */
function markButtonAsProcessed($button, kdSettle) {
    $button.prop('disabled', true)
           .removeClass('btn-processing btn-primary')
           .addClass('btn-secondary')
           .html('<i class="fal fa-check-circle me-1"></i>Sudah Diproses');
    
    // Update status di global map
    if (typeof window.processedStatusMap !== 'undefined') {
        window.processedStatusMap[kdSettle] = true;
    }
}

/**
 * Reload datatable untuk menampilkan alert danger dengan error message
 */
function reloadDatatableForError() {
    if (typeof jurnalCaEscrowTable !== 'undefined') {
        setTimeout(function() {
            jurnalCaEscrowTable.ajax.reload(null, false);
        });
    }
}

// Function untuk memproses semua detail transaksi dalam satu grup (batch processing)
function processBatchJurnal(kdSettle) {
    console.log('Processing batch jurnal for KD Settle:', kdSettle);
    
    // Cek apakah sudah ada proses yang sedang berjalan
    if ($('.btn-processing').length > 0) {
        showAlert('warning', 'Ada proses lain yang sedang berjalan. Mohon tunggu hingga selesai.');
        return;
    }
    
    // Konfirmasi untuk memproses semua transaksi
    const confirmMessage = `Apakah Anda yakin ingin memproses SEMUA transaksi untuk kode settle: ${kdSettle}?\n\nSemua transaksi akan dikirim ke Akselgate secara bersamaan.\nProses ini tidak dapat dibatalkan!`;
    
    if (!confirm(confirmMessage)) {
        console.log('Batch process cancelled by user');
        return;
    }
    
    // Update button batch menjadi processing
    const $batchBtn = $(`#btn-batch-${kdSettle}`);
    const originalHtml = $batchBtn.html();
    $batchBtn.prop('disabled', true)
           .html('<i class="fal fa-spinner fa-spin me-1"></i>Memproses...')
           .addClass('btn-processing');
    
    // Show progress modal
    showBatchProgressModal(kdSettle);
    
    // Prevent browser close/refresh
    setBeforeUnloadWarning(true);
    
    console.log('Sending batch request to Akselgate...');
    
    // Safety timeout - force cleanup jika request terlalu lama (6 menit)
    const safetyTimeoutId = setTimeout(function() {
        console.warn('Safety timeout triggered - forcing cleanup');
        $batchBtn.prop('disabled', false)
               .html(originalHtml)
               .removeClass('btn-processing');
        hideBatchProgressModal();
        setBeforeUnloadWarning(false);
        showAlert('warning', 'Request timeout. Silakan periksa status transaksi atau coba lagi.');
    }, 360000); // 6 menit
    
    // AJAX call untuk batch process
    let isSuccess = false;
    
    $.ajax({
        url: window.appConfig.baseUrl + "settlement/jurnal-ca-escrow/proses",
        type: 'POST',
        timeout: 300000, // 5 menit timeout
        data: {
            csrf_test_name: currentCSRF,
            kd_settle: kdSettle,
            tanggal: window.appConfig.tanggalData
        },
        beforeSend: function(xhr, settings) {
            console.log('Batch request data:', settings.data);
        },
        success: function(response) {
            console.log('Batch API response:', response);
            
            // Update CSRF token
            if (response.csrf_token) {
                currentCSRF = response.csrf_token;
            }
            
            if (response.success) {
                showAlert('success', 'Transaksi berhasil dikirim ke Akselgate!');
                isSuccess = true;
                
                // Update button menjadi "Sudah Diproses"
                markButtonAsProcessed($batchBtn, kdSettle);
                
                console.log('Success: Button changed to "Sudah Diproses"');
            } else {
                // GAGAL: Juga ubah button jadi "Sudah Diproses" dan tandai sebagai processed
                showAlert('error', `Batch process gagal: ${response.message || 'Unknown error'}`);
                isSuccess = true; // Tandai sebagai "processed" walaupun gagal
                
                // Update button menjadi "Sudah Diproses"
                markButtonAsProcessed($batchBtn, kdSettle);
                
                console.log('Failed: Button changed to "Sudah Diproses" - error will be shown in alert');
                
                // Reload datatable untuk menampilkan alert danger dengan error message
                reloadDatatableForError();
            }
        },
        error: function(xhr, status, error) {
            console.error('Batch process error:', status, error, xhr.responseText);
            
            // Clear safety timeout
            clearTimeout(safetyTimeoutId);
            
            // FORCE hide modal immediately
            console.log('Error callback - forcing modal cleanup');
            $('#batchProgressModal').modal('hide');
            $('#batchProgressModal').remove();
            $('.modal-backdrop').remove();
            $('body').removeClass('modal-open');
            $('body').css('padding-right', '');
            setBeforeUnloadWarning(false);
            
            let errorMessage = '❌ Terjadi kesalahan saat batch process!';
            
            if (xhr.status === 403 || xhr.status === 419) {
                errorMessage = '❌ Session expired. Silakan refresh halaman dan coba lagi.';
                refreshCSRFToken().then(function() {
                    console.log('CSRF refreshed after error');
                }).catch(function() {
                    console.log('Failed to refresh CSRF after error');
                });
            } else if (xhr.status === 0) {
                errorMessage = '❌ Koneksi terputus. Periksa koneksi internet Anda.';
            } else if (xhr.status >= 500) {
                errorMessage = '❌ Server error. Silakan coba lagi atau hubungi administrator.';
            } else if (xhr.responseText) {
                try {
                    const errorResponse = JSON.parse(xhr.responseText);
                    errorMessage = `❌ Error: ${errorResponse.message || error}`;
                } catch (e) {
                    errorMessage = `❌ Error: ${xhr.responseText}`;
                }
            }
            
            showAlert('error', errorMessage);
            
            // Network error: Juga ubah button jadi "Sudah Diproses"
            isSuccess = true; // Tandai sebagai "processed"
            
            markButtonAsProcessed($batchBtn, kdSettle);
            
            console.log('Network/HTTP error: Button changed to "Sudah Diproses"');
            
            // Reload datatable untuk menampilkan alert danger dengan error message
            reloadDatatableForError();
        },
        complete: function(xhr, status) {
            // Clear safety timeout
            clearTimeout(safetyTimeoutId);
            
            // Hide modal and remove warning
            hideBatchProgressModal();
            setBeforeUnloadWarning(false);
            
            console.log('Complete callback - isSuccess:', isSuccess);
            
            // Tidak perlu restore button lagi karena semua case (sukses/gagal) 
            // sudah mengubah button menjadi "Sudah Diproses"
            
            console.log('Batch process complete');
        }
    });
}

// Function untuk menampilkan modal progress batch
function showBatchProgressModal(kdSettle) {
    // Update kode settle di modal
    $('#batch-kd-settle').text(kdSettle);
    
    // Show modal
    $('#batchProgressModal').modal('show');
}

// Function untuk menyembunyikan modal progress batch
function hideBatchProgressModal() {
    // Hide modal
    $('#batchProgressModal').modal('hide');
    
    // Force cleanup - remove modal, backdrop, and body class
    setTimeout(function() {
        $('#batchProgressModal').remove();
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');
        $('body').css('padding-right', '');
        console.log('Batch progress modal fully cleaned up');
    }, 300);
}
