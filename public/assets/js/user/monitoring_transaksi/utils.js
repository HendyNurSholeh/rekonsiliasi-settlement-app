/**
 * Utility functions for Monitoring Transaksi module
 */

/**
 * Get current month in YYYY-MM format
 * @returns {string}
 */
export const getCurrentMonth = () => {
    const now = new Date();
    return now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
};

/**
 * Format number as money with locale
 * @param {number|string} val 
 * @returns {string}
 */
export const formatMoney = (val) => {
    val = (val || '0').toString().replace(/,/g, '');
    return parseFloat(val).toLocaleString('en-US', {
        minimumFractionDigits: 2, 
        maximumFractionDigits: 2
    });
};

/**
 * Format currency to USD
 * @param {number} amount 
 * @returns {string}
 */
export const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', { 
        style: 'currency', 
        currency: 'USD' 
    }).format(amount || 0);
};

/**
 * Calculate percentage
 * @param {number} part 
 * @param {number} total 
 * @returns {number}
 */
export const calculatePercentage = (part, total) => {
    return total ? Math.round((part / total) * 100) : 0;
};

/**
 * Parse integer safely
 * @param {*} value 
 * @returns {number}
 */
export const safeParseInt = (value) => {
    return parseInt(value) || 0;
};

/**
 * Parse float safely
 * @param {*} value 
 * @returns {number}
 */
export const safeParseFloat = (value) => {
    return parseFloat((value || '0').toString().replace(/,/g, ''));
};
