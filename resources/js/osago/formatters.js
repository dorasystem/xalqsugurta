/**
 * Formatters Module
 * Date, currency, and input formatting utilities for Uzbekistan formats
 */

import CONFIG from './config.js';

/**
 * Format currency amount for display
 * Uzbekistan format: 168 000,00 сум (spaces for thousands, comma for decimals)
 */
export function formatCurrency(amount, showDecimals = true) {
    if (isNaN(amount)) return '0';

    const options = {
        minimumFractionDigits: showDecimals ? 2 : 0,
        maximumFractionDigits: showDecimals ? 2 : 0
    };

    // Use English locale for consistent formatting, then replace separators
    const formatted = amount.toLocaleString('en-US', options);

    // Replace comma with space for thousands, period with comma for decimals (Uzbek style)
    return formatted.replace(/,/g, ' ');
}

/**
 * Format date for HTML input (YYYY-MM-DD)
 */
export function formatDateForInput(date) {
    if (!(date instanceof Date)) {
        date = new Date(date);
    }

    if (isNaN(date.getTime())) {
        return '';
    }

    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${year}-${month}-${day}`;
}

/**
 * Parse date from HTML input (YYYY-MM-DD)
 */
export function parseDateFromInput(dateString) {
    if (!dateString) return null;

    const [year, month, day] = dateString.split('-').map(Number);

    if (!year || !month || !day) return null;

    const date = new Date(year, month - 1, day);
    date.setHours(0, 0, 0, 0); // Normalize to midnight

    return date;
}

/**
 * Format date for display (DD.MM.YYYY - Uzbekistan format)
 */
export function formatDateForDisplay(date) {
    if (!(date instanceof Date)) {
        date = new Date(date);
    }

    if (isNaN(date.getTime())) {
        return '';
    }

    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const year = date.getFullYear();

    return `${day}.${month}.${year}`;
}

/**
 * Calculate end date based on start date and period
 * Returns end date = start + N months - 1 day
 */
export function calculateEndDate(startDate, periodValue) {
    if (!startDate) return null;

    const monthsToAdd = CONFIG.MONTHS_MAP[periodValue] || 12;

    const endDate = new Date(startDate);
    endDate.setMonth(endDate.getMonth() + monthsToAdd);
    endDate.setDate(endDate.getDate() - 1);

    // Handle edge case: if day overflowed to next month
    // (e.g., Jan 31 + 1 month = Mar 3 instead of Feb 28)
    const startMonth = startDate.getMonth();
    const expectedEndMonth = (startMonth + monthsToAdd) % 12;
    const actualEndMonth = endDate.getMonth();

    // If we're in the wrong month, go back to last day of previous month
    if (actualEndMonth !== expectedEndMonth && actualEndMonth !== (expectedEndMonth + 1) % 12) {
        endDate.setDate(0); // Go to last day of previous month
    }

    return endDate;
}

/**
 * Auto-format government number as user types
 * Format: 01 A 123 BC
 */
export function formatGovNumber(value) {
    let cleaned = value.toUpperCase().replace(/[^A-Z0-9]/g, '');

    // Add spaces: 01 A 123 BC
    let formatted = cleaned;

    if (cleaned.length >= 2) {
        formatted = cleaned.slice(0, 2) + ' ' + cleaned.slice(2);
    }
    if (cleaned.length >= 3) {
        formatted = formatted.slice(0, 5) + ' ' + formatted.slice(5);
    }
    if (cleaned.length >= 6) {
        formatted = formatted.slice(0, 9) + ' ' + formatted.slice(9);
    }

    return formatted.slice(0, 13); // Max length with spaces
}

/**
 * Auto-format PINFL with spaces for readability
 * Format: 12 345 67 89 01 23 4
 */
export function formatPinfl(value) {
    const cleaned = value.replace(/\D/g, '');

    // Add spaces: 12 345 67 89 01 23 4
    let formatted = '';

    if (cleaned.length > 0) formatted = cleaned.slice(0, 2);
    if (cleaned.length > 2) formatted += ' ' + cleaned.slice(2, 5);
    if (cleaned.length > 5) formatted += ' ' + cleaned.slice(5, 7);
    if (cleaned.length > 7) formatted += ' ' + cleaned.slice(7, 9);
    if (cleaned.length > 9) formatted += ' ' + cleaned.slice(9, 11);
    if (cleaned.length > 11) formatted += ' ' + cleaned.slice(11, 13);
    if (cleaned.length > 13) formatted += ' ' + cleaned.slice(13, 14);

    return formatted;
}

/**
 * Auto-format phone number
 * Format: +998 XX XXX XX XX
 */
export function formatPhone(value) {
    let cleaned = value.replace(/\D/g, '');

    // Ensure it starts with 998
    if (cleaned.length > 0 && !cleaned.startsWith('998')) {
        if (cleaned.startsWith('998')) {
            // Already correct
        } else if (cleaned.startsWith('98')) {
            cleaned = '9' + cleaned;
        } else if (cleaned.length >= 9) {
            cleaned = '998' + cleaned;
        }
    }

    // Add spaces: +998 XX XXX XX XX
    let formatted = '+';

    if (cleaned.length > 0) formatted += cleaned.slice(0, 3); // 998
    if (cleaned.length > 3) formatted += ' ' + cleaned.slice(3, 5); // XX
    if (cleaned.length > 5) formatted += ' ' + cleaned.slice(5, 8); // XXX
    if (cleaned.length > 8) formatted += ' ' + cleaned.slice(8, 10); // XX
    if (cleaned.length > 10) formatted += ' ' + cleaned.slice(10, 12); // XX

    return formatted;
}

/**
 * Auto-format passport (series + number combined)
 * Format: AA-1234567
 */
export function formatPassport(value) {
    const cleaned = value.toUpperCase().replace(/[^A-Z0-9]/g, '');

    let formatted = cleaned.slice(0, 2); // Series (2 letters)

    if (cleaned.length > 2) {
        formatted += '-' + cleaned.slice(2, 9); // Number (7 digits)
    }

    return formatted;
}

/**
 * Auto-uppercase a value
 */
export function toUpperCase(value) {
    return value ? value.toUpperCase() : '';
}

/**
 * Setup auto-format for an input element
 * @param {HTMLInputElement} element - The input element
 * @param {string} formatType - Type of formatting ('gov_number', 'pinfl', 'phone', etc.)
 */
export function setupAutoFormat(element, formatType) {
    if (!element) return;

    const formatters = {
        'gov_number': formatGovNumber,
        'pinfl': formatPinfl,
        'phone': formatPhone,
        'passport': formatPassport,
        'uppercase': toUpperCase
    };

    const formatter = formatters[formatType];
    if (!formatter) return;

    element.addEventListener('input', function(e) {
        const cursorPos = e.target.selectionStart;
        const oldValue = e.target.value;
        const newValue = formatter(oldValue);

        if (newValue !== oldValue) {
            e.target.value = newValue;

            // Try to maintain cursor position
            const diff = newValue.length - oldValue.length;
            e.target.setSelectionRange(cursorPos + diff, cursorPos + diff);
        }
    });
}

/**
 * Setup auto-uppercase for serial fields
 * @param {HTMLInputElement} element - The input element
 */
export function setupAutoUppercase(element) {
    if (!element) return;

    element.style.textTransform = 'uppercase';

    element.addEventListener('input', function(e) {
        const cursorPos = e.target.selectionStart;
        e.target.value = e.target.value.toUpperCase();
        e.target.setSelectionRange(cursorPos, cursorPos);
    });
}

/**
 * Remove formatting from value (get raw value)
 */
export function unformatValue(value, type = 'default') {
    if (!value) return '';

    switch(type) {
        case 'gov_number':
        case 'pinfl':
        case 'phone':
        case 'passport':
            return value.replace(/[^A-Z0-9]/gi, '');
        case 'currency':
            return value.replace(/[^0-9.]/g, '');
        default:
            return value.trim();
    }
}

export default {
    formatCurrency,
    formatDateForInput,
    formatDateForDisplay,
    parseDateFromInput,
    calculateEndDate,
    formatGovNumber,
    formatPinfl,
    formatPhone,
    formatPassport,
    toUpperCase,
    setupAutoFormat,
    setupAutoUppercase,
    unformatValue
};
