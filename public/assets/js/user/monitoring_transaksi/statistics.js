/**
 * Statistics functionality for Monitoring Transaksi
 */
import { safeParseInt, safeParseFloat, formatMoney } from './utils.js';
import { SELECTORS, BADGE_CLASSES } from './constants.js';

/**
 * Update summary cards with statistics
 * @param {Array} data - DataTable data
 */
export const updateSummaryCards = (data) => {
    if (!data || !Array.isArray(data)) {
        resetSummaryCards();
        return;
    }

    const totals = calculateTotals(data);
    
    $(SELECTORS.CARD_NASABAH).text(totals.nasabah);
    $(SELECTORS.CARD_UNDERLYING).text(totals.underlying);
    $(SELECTORS.CARD_NONUNDERLYING).text(totals.valas);
    $(SELECTORS.CARD_NOMINAL).text('$' + formatMoney(totals.nominal));
};

/**
 * Reset summary cards to zero
 */
export const resetSummaryCards = () => {
    $(SELECTORS.CARD_NASABAH).text('0');
    $(SELECTORS.CARD_UNDERLYING).text('0');
    $(SELECTORS.CARD_NONUNDERLYING).text('0');
    $(SELECTORS.CARD_NOMINAL).text('$0.00');
};

/**
 * Calculate totals from data array
 * @param {Array} data 
 * @returns {Object}
 */
export const calculateTotals = (data) => {
    return data.reduce((acc, row) => {
        acc.underlying += safeParseInt(row.TOTAL_UNDERLYING);
        acc.valas += safeParseInt(row.TOTAL_VALAS);
        acc.transaksi += safeParseInt(row.TOTAL_UNDERLYING) + safeParseInt(row.TOTAL_VALAS);
        acc.nominal += safeParseFloat(row.TOTAL_NOMINAL);
        acc.nasabah++;
        return acc;
    }, {
        underlying: 0, 
        valas: 0, 
        transaksi: 0, 
        nominal: 0, 
        nasabah: 0
    });
};

