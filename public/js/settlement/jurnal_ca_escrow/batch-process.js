// ==========================================
// JURNAL CA ESCROW - BATCH PROCESS MODULE
// ==========================================

// Function untuk memproses semua detail transaksi dalam satu grup (batch processing)
function processBatchJurnal(kdSettle) {
    console.log('Processing batch jurnal for KD Settle:', kdSettle);
    
    // Cek apakah sudah ada proses yang sedang berjalan
    if ($('.btn-processing').length > 0) {
        showAlert('warning', 'Ada proses lain yang sedang berjalan. Mohon tunggu hingga selesai.');
        return;
    }
    
    // Konfirmasi untuk memproses semua transaksi
    const confirmMessage = `Apakah Anda yakin ingin memproses SEMUA transaksi untuk kode settle: ${kdSettle}?\n\nSemua transaksi akan dikirim ke API Gateway secara bersamaan.\nProses ini tidak dapat dibatalkan!`;
    
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
    
    console.log('Sending batch request to API Gateway...');
    
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
                showAlert('success', 'Transaksi berhasil dikirim ke API Gateway!');
                isSuccess = true;
                
                // Disable button permanently
                $batchBtn.prop('disabled', true)
                       .removeClass('btn-processing');
                
                console.log('Success: Button disabled permanently');
            } else {
                showAlert('error', `Batch process gagal: ${response.message || 'Unknown error'}`);
                
                // Restore button untuk retry
                $batchBtn.prop('disabled', false)
                       .html(originalHtml)
                       .removeClass('btn-processing');
                
                console.log('Backend error: Button restored for retry');
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
            
            // Restore button untuk retry
            $batchBtn.prop('disabled', false)
                   .html(originalHtml)
                   .removeClass('btn-processing');
            
            console.log('Network/HTTP error: Button restored, modal force cleaned');
        },
        complete: function(xhr, status) {
            // Clear safety timeout
            clearTimeout(safetyTimeoutId);
            
            // Hide modal and remove warning
            hideBatchProgressModal();
            setBeforeUnloadWarning(false);
            
            console.log('Complete callback - isSuccess:', isSuccess);
            
            // Safety net: restore button if not success
            if (!isSuccess && $batchBtn.prop('disabled')) {
                console.log('Safety net: Restoring button in complete callback');
                $batchBtn.prop('disabled', false)
                       .html(originalHtml)
                       .removeClass('btn-processing');
            }
            
            console.log('Batch process complete');
        }
    });
}

// Function untuk menampilkan modal progress batch
function showBatchProgressModal(kdSettle) {
    const modalContent = `
        <div class="modal fade" id="batchProgressModal" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header" style="background: linear-gradient(135deg, #6c5190 0%, #553d73 100%); color: white; border-bottom: 1px solid #553d73;">
                        <h5 class="modal-title">
                            <i class="fal fa-cloud-upload-alt me-2"></i>Mengirim ke API Gateway
                        </h5>
                    </div>
                    <div class="modal-body">
                        <div class="text-center py-4">
                            <div class="mb-4">
                                <i class="fal fa-spinner fa-spin" style="font-size: 3rem; color: #6c5190;"></i>
                            </div>
                            <h5>Memproses Transaksi Batch</h5>
                            <p class="text-muted">Kode Settle: <strong>${kdSettle}</strong></p>
                            <p class="text-muted">Sedang mengirim semua transaksi ke API Gateway...</p>
                            
                            <div class="mt-4">
                                <div class="alert alert-info">
                                    <i class="fal fa-info-circle me-2"></i>
                                    Mohon tunggu, proses ini dapat memakan waktu beberapa saat.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    // Remove existing modal if any
    $('#batchProgressModal').remove();
    
    // Add to DOM and show
    $('body').append(modalContent);
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
