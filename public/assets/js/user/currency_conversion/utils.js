let csrfName = document.querySelector('#txt_csrfname')?.getAttribute('name');
let csrfHash = document.querySelector('#txt_csrfname')?.value;

/**
 * Reloads the DataTable instance.
 * @param {DataTables.Api} table - The DataTable instance to reload.
 */
function reloadTable(table) {
    table.ajax.reload();
}

/**
 * Updates the CSRF token value.
 * @param {string} newToken - The new CSRF token.
 */
function updateCsrfToken(newToken) {
    csrfHash = newToken;
    // Also update the hidden input in the DOM if it's used by other scripts.
    const csrfInput = document.querySelector('#txt_csrfname');
    if (csrfInput) {
        csrfInput.value = newToken;
    }
}

export { csrfName, csrfHash, reloadTable, updateCsrfToken };
