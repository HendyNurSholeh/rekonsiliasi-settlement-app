// ==========================================
// CALLBACK LOG MODAL MODULE
// ==========================================

/**
 * Function untuk menampilkan modal callback log berdasarkan ref_number
 * @param {string} refNumber - Reference number untuk mencari callback log
 */
function showCallbackLogModalByRef(refNumber) {
	// Set title
	$('#modalDetail .modal-title').html(`<i class="fal fa-server"></i> Callback Log Detail - REF: ${refNumber}`);

	// Show loading in modal body
	const loadingContent = `
		<div class="text-center">
			<div class="spinner-border text-primary" role="status">
				<span class="sr-only">Loading...</span>
			</div>
			<p class="mt-2">Memuat data callback log...</p>
		</div>
	`;
	$('#modalDetail .modal-body').html(loadingContent);

	// Show modal
	$('#modalDetail').modal('show');

	// Fetch callback log by ref_number
	console.log('Making AJAX request to:', `${window.appConfig.baseUrl}/settlement/jurnal-ca-escrow/get-callback-by-request-id/${encodeURIComponent(refNumber)}`);
	console.log('Request data - refNumber:', refNumber, 'encoded:', encodeURIComponent(refNumber));

	$.ajax({
		url: `${window.appConfig.baseUrl}/settlement/jurnal-ca-escrow/get-callback-by-request-id/${encodeURIComponent(refNumber)}`,
		type: 'GET',
		dataType: 'json',
		timeout: 10000, // 10 second timeout
		beforeSend: function(xhr) {
			console.log('AJAX beforeSend - setting headers');
			// Add CSRF token if available
			if (window.appConfig && window.appConfig.csrfToken) {
				xhr.setRequestHeader('X-CSRF-TOKEN', window.appConfig.csrfToken);
				console.log('CSRF token added to request');
			}
		},
		success: function(response, textStatus, xhr) {
			console.log('AJAX SUCCESS - Full response:', response);
			console.log('AJAX SUCCESS - Status:', xhr.status, 'textStatus:', textStatus);
			console.log('Response success:', response.success);
			console.log('Response data:', response.data);
			console.log('Response message:', response.message);

			if (response.success && response.data) {
				console.log('Populating modal with data:', response.data);
				// Populate modal fields with callback log data
				populateCallbackDetailFields(response.data);
				// Ensure modal is still shown and content is visible
				$('#modalDetail').modal('show');
			} else {
				console.warn('No callback data found. Response:', response);
				const message = response.message || `Tidak ada callback log untuk request ID: ${refNumber}`;
				// Gunakan modal yang sudah ada, ganti isi body dengan alert
				$('#modalDetail .modal-body').html(`
					<div class="alert alert-info">
						<i class="fal fa-info-circle"></i> ${message}
						<br><small>Silakan cek apakah transaksi ini sudah diproses atau coba lagi nanti.</small>
					</div>
				`);
			}
		},
		error: function(xhr, status, error) {
			console.error('AJAX ERROR - Full error details:', {
				status: xhr.status,
				statusText: xhr.statusText,
				responseText: xhr.responseText,
				responseJSON: xhr.responseJSON,
				error: error,
				readyState: xhr.readyState,
				statusCode: xhr.statusCode
			});

			let errorMessage = 'Terjadi kesalahan saat memuat data callback log';

			if (xhr.status === 0) {
				errorMessage = 'Tidak dapat terhubung ke server. Periksa koneksi internet Anda.';
			} else if (xhr.status === 404) {
				errorMessage = 'Endpoint tidak ditemukan. Periksa konfigurasi URL.';
			} else if (xhr.status === 500) {
				errorMessage = 'Terjadi kesalahan server internal.';
			} else if (xhr.status === 403 || xhr.status === 419) {
				errorMessage = 'Sesi telah berakhir. Silakan refresh halaman.';
			} else if (status === 'timeout') {
				errorMessage = 'Request timeout. Server membutuhkan waktu terlalu lama untuk merespons.';
			}

			// Gunakan modal yang sudah ada, ganti isi body dengan error alert
			$('#modalDetail .modal-body').html(`
				<div class="alert alert-danger">
					<i class="fal fa-exclamation-triangle"></i> ${errorMessage}
					<br><small>Status: ${xhr.status} ${xhr.statusText} | Error: ${error}</small>
					${xhr.responseText ? '<br><small>Response: ' + xhr.responseText.substring(0, 100) + '...</small>' : ''}
				</div>
			`);
		},
		complete: function(xhr, status) {
			console.log('AJAX COMPLETE - Status:', status, 'Final status:', xhr.status);
		}
	});
}

/**
 * Function untuk populate modal fields dengan data callback log
 * @param {object} log - Data callback log dari server
 */
function populateCallbackDetailFields(log) {
	// Debug log untuk melihat data yang diterima
	console.log('Callback log data received:', log);

	// Jika log null atau undefined, tampilkan pesan error dalam modal yang sudah ada
	if (!log) {
		console.warn('Callback log data is null or undefined');
		$('#modalDetail .modal-body').html(`
			<div class="alert alert-warning">
				<i class="fal fa-exclamation-triangle"></i> Data callback log tidak tersedia
			</div>
		`);
		return;
	}

	// Pastikan modal body menampilkan konten detail yang benar (bukan loading)
	const detailContent = `
		<div class="row">
			<div class="col-md-6">
				<dl class="row">
					<dt class="col-sm-4">ID:</dt>
					<dd class="col-sm-8" id="detail_id">${log.id || '-'}</dd>

					<dt class="col-sm-4">REF Number:</dt>
					<dd class="col-sm-8 fw-700" id="detail_ref_number">${log.ref_number || '-'}</dd>

					<dt class="col-sm-4">Kode Settle:</dt>
					<dd class="col-sm-8 fw-700" id="detail_kd_settle">${log.kd_settle || '-'}</dd>

					<dt class="col-sm-4">Response Code:</dt>
					<dd class="col-sm-8" id="detail_res_code">${log.res_code || '-'}</dd>

					<dt class="col-sm-4">Core Reference:</dt>
					<dd class="col-sm-8" id="detail_res_coreref">${log.res_coreref || '-'}</dd>
				</dl>
			</div>
			<div class="col-md-6">
				<dl class="row">
					<dt class="col-sm-4">Status:</dt>
					<dd class="col-sm-8" id="detail_status">${getStatusBadge(log.status)}</dd>

					<dt class="col-sm-4">Processed:</dt>
					<dd class="col-sm-8" id="detail_is_processed">${getProcessedBadge(log.is_processed)}</dd>

					<dt class="col-sm-4">IP Address:</dt>
					<dd class="col-sm-8" id="detail_ip_address">${log.ip_address || '-'}</dd>

					<dt class="col-sm-4">Waktu Diterima:</dt>
					<dd class="col-sm-8" id="detail_created_at">${log.created_at ? new Date(log.created_at).toLocaleString('id-ID') : '-'}</dd>

					<dt class="col-sm-4">Waktu Diproses:</dt>
					<dd class="col-sm-8" id="detail_processed_at">${log.processed_at ? new Date(log.processed_at).toLocaleString('id-ID') : '-'}</dd>
				</dl>
			</div>
		</div>

		<div class="row mt-3">
			<div class="col-12">
				<h6 class="fw-700">Raw Callback Data (JSON):</h6>
				<pre class="p-3" style="max-height:400px; overflow:auto; background:#ffffff; border:1px solid #e6e7e8; border-left:4px solid #28a745; border-radius:6px;">
					<code id="detail_callback_data" style="color:#1b5e20; font-family: Menlo, Monaco, 'Courier New', monospace; font-size:0.95rem; white-space:pre-wrap; word-break:break-word;"><br>${getFormattedCallbackData(log.callback_data)}</code>
				</pre>
			</div>
		</div>
	`;

	// Replace modal body dengan konten detail
	$('#modalDetail .modal-body').html(detailContent);

	console.log('Modal fields populated successfully');
}

/**
 * Helper function untuk generate status badge
 */
function getStatusBadge(status) {
	if (!status) return '<span class="badge badge-light">UNKNOWN</span>';

	const statusUpper = status.toUpperCase();
	if (statusUpper === 'SUCCESS') {
		return '<span class="badge badge-success"><i class="fal fa-check"></i> SUCCESS</span>';
	} else if (statusUpper === 'FAILED') {
		return '<span class="badge badge-danger"><i class="fal fa-times"></i> FAILED</span>';
	} else {
		return `<span class="badge badge-info">${status}</span>`;
	}
}

/**
 * Helper function untuk generate processed badge
 */
function getProcessedBadge(isProcessed) {
	if (isProcessed === null || isProcessed === undefined) {
		return '<span class="badge badge-light">UNKNOWN</span>';
	}

	const processed = parseInt(isProcessed);
	if (processed === 1) {
		return '<span class="badge badge-success"><i class="fal fa-check-circle"></i> Yes</span>';
	} else if (processed === 0) {
		return '<span class="badge badge-warning"><i class="fal fa-clock"></i> No</span>';
	} else {
		return '<span class="badge badge-light">UNKNOWN</span>';
	}
}

/**
 * Helper function untuk format callback data JSON
 */
function getFormattedCallbackData(callbackData) {
	try {
		if (!callbackData) return 'Tidak ada data';

		if (typeof callbackData === 'string') {
			// Jika string, parse dulu lalu stringify lagi untuk format
			const parsed = JSON.parse(callbackData);
			return JSON.stringify(parsed, null, 2);
		} else if (typeof callbackData === 'object') {
			// Jika sudah object, langsung stringify
			return JSON.stringify(callbackData, null, 2);
		}

		return callbackData;
	} catch (e) {
		console.warn('Error parsing callback_data JSON:', e, 'Raw data:', callbackData);
		return callbackData || 'Tidak ada data';
	}
}

/**
 * Function untuk menampilkan detail callback log (untuk kompatibilitas jika masih digunakan)
 * @param {number} logId - ID callback log
 */
function showCallbackDetailModal(logId) {
	// Set title
	$('#modalDetail .modal-title').text('Detail Callback Log');

	// Show loading
	$('#modalDetail .modal-body').html(`
		<div class="text-center">
			<div class="spinner-border text-primary" role="status">
				<span class="sr-only">Loading...</span>
			</div>
			<p class="mt-2">Memuat detail callback log...</p>
		</div>
	`);

	// Show modal
	$('#modalDetail').modal('show');

	$.ajax({
		url: `${window.appConfig.baseUrl}/settlement/jurnal-ca-escrow/get-callback-detail/${logId}`,
		type: 'GET',
		dataType: 'json',
		success: function(response) {
			if (response.success) {
				populateCallbackDetailFields(response.data);
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