// ==========================================
// UTILITY/HELPER FUNCTIONS MODULE
// ==========================================

/**
 * Helper function untuk render badge dengan count
 * @param {number} count - Count value
 * @param {string} badgeClass - Badge CSS class (warning, success, light)
 * @returns {string} HTML badge
 */
function renderCountBadge(count, badgeClass) {
	const parsedCount = parseInt(count || 0);
	if (parsedCount > 0) {
		// Jika warning, gunakan warna custom yang lebih gelap dengan text putih
		if (badgeClass === "warning") {
			return `<span class="badge text-white" style="background-color: #e97f07ff;">${parsedCount}</span>`;
		}
		return `<span class="badge badge-${badgeClass}">${parsedCount}</span>`;
	}
	return '<span class="badge badge-light">0</span>';
}

/**
 * Helper function untuk render Core Response Code
 * @param {string} coreRes - Core response code
 * @returns {string} HTML badge atau text
 */
function renderCoreResCode(coreRes) {
	if (!coreRes) {
		return '<span class="text-muted">NULL</span>';
	}
	const badgeClass = coreRes.startsWith("00") ? "success" : "danger";
	return `<span class="badge badge-${badgeClass} small">${coreRes}</span>`;
}

/**
 * Helper function untuk render status transaksi
 * @param {string} codeRes - Response code
 * @returns {string} HTML status badge
 */
function renderTransactionStatus(codeRes) {
	if (codeRes && codeRes.startsWith("00")) {
		return '<span class="badge badge-success small"><i class="fal fa-check"></i> Selesai</span>';
	} else if (codeRes) {
		return '<span class="badge badge-warning small"><i class="fal fa-exclamation-triangle"></i> Gagal</span>';
	}
	return '<span class="badge badge-light small"><i class="fal fa-clock"></i> Pending</span>';
}

/**
 * Helper function untuk truncate text dengan tooltip
 * @param {string} text - Text to truncate
 * @param {number} maxLength - Maximum length
 * @returns {string} HTML with truncated text
 */
function renderTruncatedText(text, maxLength = 10) {
	if (!text) {
		return '<span class="text-muted">NULL</span>';
	}
	if (text.length > maxLength) {
		return `<span title="${text}">${text.substring(0, maxLength)}...</span>`;
	}
	return `<span>${text}</span>`;
}

/**
 * Helper function untuk format currency
 * @param {number} amount - Amount value
 * @returns {string} Formatted currency
 */
function formatCurrency(amount) {
	const num = parseFloat(String(amount || 0).replace(/,/g, ""));
	return "Rp " + new Intl.NumberFormat("id-ID").format(num);
}