/**
 * Validators Module
 * Uzbekistan-specific validation rules for OSAGO forms
 */

/**
 * Government vehicle number validation
 * Format: 01 A 123 BC (region code + series + number + series)
 */
export function validateGovNumber(value) {
    if (!value || value.trim() === '') {
        return 'Government number is required';
    }

    const cleaned = value.replace(/\s/g, '');

    // Check format: 2 digits + 1 letter + 3 digits + 2 letters
    if (!/^[0-9]{2}[A-Z][0-9]{3}[A-Z]{2}$/i.test(cleaned)) {
        return 'Invalid format. Example: 01 A 123 BC';
    }

    // Validate region code (01-99)
    const region = parseInt(cleaned.slice(0, 2));
    if (region < 1 || region > 99) {
        return 'Invalid region code';
    }

    return null;
}

/**
 * Passport series validation
 * Format: AA (2 uppercase letters)
 */
export function validatePassportSeries(value) {
    if (!value || value.trim() === '') {
        return 'Passport series is required';
    }

    if (!/^[A-Z]{2}$/i.test(value.trim())) {
        return 'Passport series must be 2 letters (e.g., AA)';
    }

    return null;
}

/**
 * Passport number validation
 * Format: 1234567 (7 digits)
 */
export function validatePassportNumber(value) {
    if (!value || value.trim() === '') {
        return 'Passport number is required';
    }

    const cleaned = value.replace(/\s/g, '');

    if (!/^[0-9]{7}$/.test(cleaned)) {
        return 'Passport number must be 7 digits';
    }

    return null;
}

/**
 * PINFL validation
 * Format: 14 digits
 */
export function validatePinfl(value) {
    if (!value || value.trim() === '') {
        return 'PINFL is required';
    }

    const cleaned = value.replace(/\s/g, '');

    if (!/^[0-9]{14}$/.test(cleaned)) {
        return 'PINFL must be 14 digits';
    }

    return null;
}

/**
 * Tech passport series validation
 * Format: AA or AAA (2-3 uppercase letters)
 */
export function validateTechPassportSeries(value) {
    if (!value || value.trim() === '') {
        return 'Tech passport series is required';
    }

    if (!/^[A-Z]{2,3}$/i.test(value.trim())) {
        return 'Tech passport series must be 2-3 letters';
    }

    return null;
}

/**
 * Tech passport number validation
 * Format: 6-8 digits
 */
export function validateTechPassportNumber(value) {
    if (!value || value.trim() === '') {
        return 'Tech passport number is required';
    }

    const cleaned = value.replace(/\s/g, '');

    if (!/^[0-9]{6,8}$/.test(cleaned)) {
        return 'Tech passport number must be 6-8 digits';
    }

    return null;
}

/**
 * Phone number validation
 * Format: +998 XX XXX XX XX
 */
export function validatePhone(value) {
    if (!value || value.trim() === '') {
        return 'Phone number is required';
    }

    const cleaned = value.replace(/\D/g, '');

    // Must start with 998 (Uzbekistan country code)
    if (!cleaned.startsWith('998')) {
        return 'Phone must start with +998';
    }

    // Must be exactly 12 digits
    if (cleaned.length !== 12) {
        return 'Invalid phone number length';
    }

    // Validate operator code (second two digits after 998)
    const operator = cleaned.slice(3, 5);
    const validOperators = ['88', '90', '91', '93', '94', '95', '97', '98', '99', '33', '71', '77'];

    if (!validOperators.includes(operator)) {
        return 'Invalid operator code';
    }

    return null;
}

/**
 * Engine number validation
 */
export function validateEngineNumber(value) {
    if (!value || value.trim() === '') {
        return 'Engine number is required';
    }

    // Engine numbers are typically alphanumeric, 6-20 characters
    if (!/^[A-Z0-9]{6,20}$/i.test(value.trim())) {
        return 'Invalid engine number format';
    }

    return null;
}

/**
 * VIN validation (17 characters, no I, O, Q)
 */
export function validateVin(value) {
    if (!value || value.trim() === '') {
        return null; // VIN might be optional
    }

    const cleaned = value.trim().toUpperCase();

    if (cleaned.length !== 17) {
        return 'VIN must be exactly 17 characters';
    }

    if (!/^[A-HJ-NPR-Z0-9]{17}$/.test(cleaned)) {
        return 'Invalid VIN format (no I, O, or Q allowed)';
    }

    return null;
}

/**
 * Address validation
 */
export function validateAddress(value) {
    if (!value || value.trim() === '') {
        return 'Address is required';
    }

    if (value.trim().length < 10) {
        return 'Address is too short (minimum 10 characters)';
    }

    return null;
}

/**
 * Date validation (ensure not in future, not too old)
 */
export function validateDate(value, fieldName = 'Date') {
    if (!value || value.trim() === '') {
        return `${fieldName} is required`;
    }

    const date = new Date(value);
    const now = new Date();
    const minDate = new Date('1900-01-01');

    if (isNaN(date.getTime())) {
        return `Invalid ${fieldName.toLowerCase()} format`;
    }

    if (date > now) {
        return `${fieldName} cannot be in the future`;
    }

    if (date < minDate) {
        return `${fieldName} is too far in the past`;
    }

    return null;
}

/**
 * Birth date validation (must be at least 18 years old)
 */
export function validateBirthDate(value) {
    const baseError = validateDate(value, 'Birth date');
    if (baseError) return baseError;

    const birthDate = new Date(value);
    const today = new Date();
    const age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();

    if (age < 18 || (age === 18 && monthDiff < 0)) {
        return 'Must be at least 18 years old';
    }

    if (age > 120) {
        return 'Invalid birth date';
    }

    return null;
}

/**
 * Driver license validation
 */
export function validateDriverLicense(series, number) {
    if (!series || !number) {
        return 'Driver license series and number are required';
    }

    // License series: typically 2-3 letters
    if (!/^[A-Z]{2,3}$/i.test(series.trim())) {
        return 'Invalid license series format';
    }

    // License number: typically 6-8 digits
    if (!/^[0-9]{6,8}$/.test(number.trim())) {
        return 'Invalid license number format';
    }

    return null;
}

/**
 * Validate multiple fields at once
 */
export function validateInputs(elements) {
    return elements.every(el => el && el.value && el.value.trim() !== '');
}

/**
 * Get validator function by field type
 */
export function getValidator(fieldType) {
    const validators = {
        'gov_number': validateGovNumber,
        'passport_series': validatePassportSeries,
        'passport_number': validatePassportNumber,
        'pinfl': validatePinfl,
        'tech_passport_series': validateTechPassportSeries,
        'tech_passport_number': validateTechPassportNumber,
        'phone': validatePhone,
        'engine_number': validateEngineNumber,
        'vin': validateVin,
        'address': validateAddress,
        'birth_date': validateBirthDate,
        'date': validateDate
    };

    return validators[fieldType] || null;
}

export default {
    validateGovNumber,
    validatePassportSeries,
    validatePassportNumber,
    validatePinfl,
    validateTechPassportSeries,
    validateTechPassportNumber,
    validatePhone,
    validateEngineNumber,
    validateVin,
    validateAddress,
    validateDate,
    validateBirthDate,
    validateDriverLicense,
    validateInputs,
    getValidator
};
