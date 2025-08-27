let currentTanggalRekon = window.appConfig.tanggalData;

function updateMappingStats(stats) {
    $('#totalProducts').text(stats.total_products || 0);
    $('#mappedProducts').text(stats.mapped_products || 0);
    $('#unmappedProducts').text(stats.unmapped_products || 0);
    $('#mappingPercentage').text((stats.mapping_percentage || 0).toFixed(1) + '%');
}

function startReconciliation() {
    let btn = $('#btnMulaiRekonsiliasi');
    let originalText = btn.html();
    
    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Memproses...');
    
    $.ajax({
        url: window.appConfig.baseUrl + 'rekon/step2/validate',
        type: 'POST',
        data: {
            tanggal_rekon: currentTanggalRekon,
            [window.appConfig.csrfToken]: window.appConfig.csrfHash
        },
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                toastr['success'](response.message);
                if (response.redirect) {
                    setTimeout(function() {
                        window.location.href = response.redirect;
                    }, 1500);
                }
            } else {
                if (response.unmapped_products && response.unmapped_products.length > 0) {
                    let productList = response.unmapped_products.map(p => `${p.SOURCE}: ${p.PRODUK}`).join('<br>');
                    toastr['warning'](response.message + '<br><br><strong>Produk yang belum mapping:</strong><br>' + productList);
                } else {
                    toastr['error'](response.message);
                }
                btn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr) {
            let errorMsg = 'Error saat memulai rekonsiliasi';
            if (xhr && xhr.responseJSON && xhr.responseJSON.message) {
                errorMsg = xhr.responseJSON.message;
            }
            toastr['error'](errorMsg);
            btn.prop('disabled', false).html(originalText);
        }
    });
}

function prosesUlangPersiapan() {
    let btn = $('#btnProsesUlang');
    let originalText = btn.html();
    
    // Disable tombol dan tampilkan loading
    btn.prop('disabled', true).html('<i class="fal fa-spinner fa-spin"></i> Memproses...');
    
    // Kirim request AJAX
    $.ajax({
        url: window.appConfig.baseUrl + 'rekon/step2/proses-ulang',
        type: 'POST',
        data: {
            tanggal_rekon: currentTanggalRekon,
            [window.appConfig.csrfToken]: window.appConfig.csrfHash
        },
        dataType: 'json',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        success: function(response) {
            if (response.success) {
                toastr['success'](response.message || 'Proses ulang persiapan berhasil!');
                
                // Refresh halaman setelah delay
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } else {
                toastr['error'](response.message || 'Gagal menjalankan proses ulang persiapan');
                btn.prop('disabled', false).html(originalText);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error proses ulang:', xhr.responseText);
            toastr['error']('Error saat menjalankan proses ulang persiapan: ' + error);
            btn.prop('disabled', false).html(originalText);
        }
    });
}