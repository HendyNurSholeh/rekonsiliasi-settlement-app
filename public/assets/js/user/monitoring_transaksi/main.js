/**
 * Main entry point for Monitoring Transaksi module
 */
import { initTable, getTable } from './datatables.js';
import { initializeFilter } from './filter.js';
import { updateSummaryCards, resetSummaryCards, calculateTotals} from './statistics.js';

/**
 * Initialize all components
 */
const init = () => {
    // Initialize filter
    initializeFilter();
    
    // Initialize DataTable
    const table = initTable();
    
    // Setup XHR event handler for statistics
    setupXHRHandler(table);
};

/**
 * Setup XHR event handler for DataTable
 * @param {DataTable} table 
 */
const setupXHRHandler = (table) => {
    table.on('xhr', function(e, settings, json) {
        if (json && json.aaData) {
            const totals = calculateTotals(json.aaData);
            updateSummaryCards(json.aaData);
        } else {
            resetSummaryCards();
        }
    });
};

// Initialize when DOM is ready
$(document).ready(init);
