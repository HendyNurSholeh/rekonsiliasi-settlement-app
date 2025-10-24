// ==========================================
// JURNAL CA ESCROW - MAIN MODULE
// ==========================================

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
    // Remove semua class processing
    $('.btn-processing').removeClass('btn-processing');
    
    // Enable semua button
    $('.child-details-container button').prop('disabled', false).show();
    
    // Hide modal jika masih ada
    $('#batchProgressModal').modal('hide').remove();
    $('.modal-backdrop').remove();
    $('body').removeClass('modal-open');
    $('body').css('padding-right', '');
    
    // Remove warning
    setBeforeUnloadWarning(false);
    
    // Enable actions
    enableAllActions();
    
    console.log('Processing state cleaned up - ALL buttons restored and enabled');
}

// Utility Functions
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
    $('.child-details-container button:not(.btn-processing)').prop('disabled', false);
    $('#tanggal').prop('disabled', false);
    $('button[type="submit"]').prop('disabled', false);
    $('.dataTables_length select').prop('disabled', false);
    $('.dataTables_paginate .paginate_button').removeClass('disabled');
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

// Function untuk format currency
function formatCurrency(amount) {
    const num = parseFloat(String(amount || 0).replace(/,/g, ''));
    return 'Rp ' + new Intl.NumberFormat('id-ID').format(num);
}

function resetFilters() {
    const url = new URL(window.location);
    url.searchParams.delete('tanggal');
    url.searchParams.delete('status');
    window.location.href = url.pathname + url.search;
}

// Document Ready
$(document).ready(function() {
    // Refresh CSRF token saat page load
    refreshCSRFToken().then(function() {
        console.log('CSRF token refreshed on page load');
        
        // Cleanup any leftover processing states
        cleanupProcessingState();
        
        // Initialize DataTable (from datatable.js)
        if (typeof initializeDataTable === 'function') {
            initializeDataTable();
        }
    });
    
    // Handle form submit
    $('form').on('submit', function(e) {
        e.preventDefault();
        const tanggal = $('#tanggal').val();
        const status = $('#status').val();
        
        console.log('Form submit - Tanggal:', tanggal, 'Status:', status);
        
        if (tanggal && typeof jurnalCaEscrowTable !== 'undefined') {
            // Update current URL parameters
            const url = new URL(window.location);
            url.searchParams.set('tanggal', tanggal);
            if (status) {
                url.searchParams.set('status', status);
            } else {
                url.searchParams.delete('status');
            }
            window.history.pushState({}, '', url);
            
            console.log('Updated URL:', url.toString());
            
            // Reload data tetap di halaman yang sama
            jurnalCaEscrowTable.ajax.reload(null, false);
        }
    });
});
