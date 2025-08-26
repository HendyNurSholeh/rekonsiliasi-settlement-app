$(document).ready(function() {
    // Set max date to today
    $('#tanggal_rekon').attr('max', new Date().toISOString().split('T')[0]);
    
    // Check date when it changes
    $('#tanggal_rekon').on('change', function() {
        const selectedDate = $(this).val();
        if (selectedDate) {
            checkDateExists(selectedDate);
        }
    });
    
    // Form validation
    $('#processForm').on('submit', function(e) {
        var tanggal = $('#tanggal_rekon').val();
        if (!tanggal) {
            e.preventDefault();
            toastr.error('Tanggal settlement harus dipilih');
            return false;
        }
        
        // Show loading
        var submitBtn = $(this).find('button[type="submit"]');
        submitBtn.prop('disabled', true);
        submitBtn.html('<i class="fal fa-spinner fa-spin"></i> Membuat Proses...');
    });
});

function checkDateExists(date) {
    const statusDiv = $('#dateStatus');
    
    // Show loading indicator
    statusDiv.html('<i class="fal fa-spinner fa-spin text-info"></i> Memeriksa tanggal...').show();
    
    // Get CSRF token
    const csrfName = $('#processForm input[name^="csrf_"]').attr('name');
    const csrfValue = $('#processForm input[name^="csrf_"]').val();
    
    $.ajax({
        url: window.appConfig.baseUrl + "rekon/checkDate",
        method: 'POST',
        data: {
            tanggal: date,
            [csrfName]: csrfValue
        },
        success: function(response) {
            // Update CSRF token
            if (response.csrf_token && response.csrf_name) {
                $('#processForm input[name^="csrf_"]').attr('name', response.csrf_name).val(response.csrf_token);
            }
            
            if (response.success) {
                if (response.exists) {
                    statusDiv.html(`
                        <div class="alert alert-info alert-sm">
                            <i class="fal fa-info-circle"></i> 
                            Proses untuk tanggal <strong>${response.formatted_date}</strong> sudah ada
                        </div>
                    `);
                } else {
                    statusDiv.html(`
                        <div class="alert alert-success alert-sm">
                            <i class="fal fa-check-circle"></i> 
                            Tanggal <strong>${response.formatted_date}</strong> tersedia untuk proses baru
                        </div>
                    `);
                }
            } else {
                statusDiv.html(`
                    <div class="alert alert-danger alert-sm">
                        <i class="fal fa-exclamation-circle"></i> 
                        Error: ${response.message}
                    </div>
                `);
            }
        },
        error: function(xhr) {
            if (xhr.status === 403 || xhr.status === 419) {
                statusDiv.html(`
                    <div class="alert alert-warning alert-sm">
                        <i class="fal fa-exclamation-triangle"></i> 
                        Session expired. Silakan refresh halaman.
                    </div>
                `);
            } else {
                statusDiv.html(`
                    <div class="alert alert-danger alert-sm">
                        <i class="fal fa-exclamation-circle"></i> 
                        Terjadi kesalahan saat memeriksa tanggal
                    </div>
                `);
            }
        }
    });
}

function confirmReset(date) {
    // Gunakan konfirmasi JavaScript biasa agar pasti berfungsi
    if (confirm(`Reset proses rekonsiliasi untuk tanggal ${formatDate(date)}? Semua data akan dihapus.`)) {
        doReset(date);
    }
}

function doReset(date) {
    $('#reset_confirmed').val('true');
    $('#tanggal_rekon').val(date);
    $('#submitBtn').html('<i class="fal fa-redo fa-spin"></i> Mereset Proses...');
    $('#processForm').submit();
}

function dismissAlert() {
    $('.alert-warning').fadeOut();
    $('#tanggal_rekon').val(window.appConfig.defaultDate);
    $('#dateStatus').hide();
}

function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID');
}