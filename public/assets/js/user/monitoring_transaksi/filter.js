/**
 * Filter functionality for Monitoring Transaksi
 */
import { getCurrentMonth } from './utils.js';
import { reloadTable } from './datatables.js';
import { MONTH_NAMES, SELECTORS } from './constants.js';

/**
 * Initialize month filter
 */
export const initializeFilter = () => {
    // Set default month if not set
    if (!$(SELECTORS.FILTER_BULAN).val()) {
        $(SELECTORS.FILTER_BULAN).val(getCurrentMonth());
    }

    // Update label
    updateMonthLabel();

    // Bind filter button click event
    $(SELECTORS.BTN_FILTER).on('click', handleFilterClick);

    // Update print links on load
    updatePrintLinks();
};

/**
 * Handle filter button click
 */
const handleFilterClick = () => {
    reloadTable();
    updatePrintLinks();
    updateMonthLabel();
};

/**
 * Update print links with current month
 */
export const updatePrintLinks = () => {
    const bulan = $(SELECTORS.FILTER_BULAN).val();
    const config = window.monitoringConfig || {};
    
    // Update general print link if exists
    const printBtn = $(SELECTORS.BTN_CETAK);
    if (printBtn.length && config.api?.printGeneral) {
        printBtn.attr('href', `${config.api.printGeneral}?bulan=${bulan}`);
    }
};

/**
 * Update month label display
 */
export const updateMonthLabel = () => {
    const bulan = $(SELECTORS.FILTER_BULAN).val();
    const labelElement = $(SELECTORS.LABEL_BULAN);
    
    if (bulan && labelElement.length) {
        const [year, month] = bulan.split('-');
        const monthName = MONTH_NAMES[parseInt(month) - 1];
        labelElement.text(`Data untuk ${monthName} ${year}`);
    }
};

/**
 * Get current selected month
 * @returns {string}
 */
export const getSelectedMonth = () => {
    return $(SELECTORS.FILTER_BULAN).val() || getCurrentMonth();
};
