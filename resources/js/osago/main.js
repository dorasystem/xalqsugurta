/**
 * OSAGO Main Entry Point
 * Initializes all handlers and sets up the form
 */

import formState from './state.js';
import * as formatters from './formatters.js';
import * as dom from './dom-utils.js';
import CONFIG from './config.js';
import './handlers/vehicle.js';
console.log('Main: About to import owner.js');
import './handlers/owner.js';
console.log('Main: owner.js imported');
import './handlers/applicant.js';
import './handlers/driver.js';
import policyHandler from './handlers/policy.js';

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('OSAGO form initializing...');

    // Initialize form
    initializeForm();

    // Setup auto-formatting
    setupAutoFormatting();

    // Setup form submission
    setupFormSubmission();

    // Handle old values (Laravel validation errors)
    handleOldValues();

    // Setup bootstrap features
    setupBootstrapFeatures();

    // Ensure insurance_period select is always enabled
    // According to new 2026 regulations, both unlimited and limited drivers can choose period
    const periodSelect = document.getElementById('insurance_period');
    if (periodSelect) {
        periodSelect.removeAttribute('disabled');
        periodSelect.removeAttribute('readonly');
    }

    console.log('OSAGO form initialized successfully');
});

/**
 * Initialize form state and basic setup
 */
function initializeForm() {
    // Auto-scroll to first error if validation failed
    const firstError = document.querySelector('.is-invalid');
    if (firstError) {
        dom.scrollToFirstError();
    }

    // Make formState available globally for debugging
    window.osagoFormState = formState;
}

/**
 * Setup auto-formatting for all inputs
 * DISABLED: All masks removed as requested
 */
function setupAutoFormatting() {
    // All auto-formatting (masks) have been removed
    // Inputs will accept raw values without formatting
}

/**
 * Clean formatted fields before submission
 * DISABLED: All formatting removed, fields are submitted as-is
 */
function cleanFormattedFields() {
    // All field cleaning/formatting has been removed
    // Fields are submitted with their raw values
}

/**
 * Setup form submission handler
 */
function setupFormSubmission() {
    const form = document.getElementById('policy-calculation-form');
    if (!form) return;

    form.addEventListener('submit', function(e) {
        console.log('Form submitting...');

        // Clean formatted fields before submission (remove spaces)
        cleanFormattedFields();

        // Populate hidden inputs from state
        const formData = formState.toFormData();

        Object.entries(formData).forEach(([key, value]) => {
            let input = document.getElementById(key);
            if (!input) {
                input = document.createElement('input');
                input.type = 'hidden';
                input.id = key;
                input.name = key;
                form.appendChild(input);
            }
            input.value = value;
        });

        // Ensure is_applicant_owner is sent as "on" or "off" string
        const isApplicantOwnerCheckbox = document.getElementById('is-applicant-owner');
        if (isApplicantOwnerCheckbox) {
            // If unchecked, add hidden input with "off" value
            if (!isApplicantOwnerCheckbox.checked) {
                // Remove existing hidden "off" input if any
                const existingOff = form.querySelector('input[name="is_applicant_owner"][value="off"]');
                if (existingOff) {
                    existingOff.remove();
                }
                // Add hidden input with "off" value
                const offInput = document.createElement('input');
                offInput.type = 'hidden';
                offInput.name = 'is_applicant_owner';
                offInput.value = 'off';
                form.appendChild(offInput);
            } else {
                // Remove "off" input if checkbox is checked
                const existingOff = form.querySelector('input[name="is_applicant_owner"][value="off"]');
                if (existingOff) {
                    existingOff.remove();
                }
            }
        }

        // Validate required fields
        if (!validateFormBeforeSubmit()) {
            e.preventDefault();
            dom.showToast('error', 'XALQ SUG\'URTA', 'Please fill all required fields correctly');
            dom.scrollToFirstError();
        }
    });
}

/**
 * Validate form before submission
 */
function validateFormBeforeSubmit() {
    const requiredOtherKeys = ['techPassportIssueDate', 'typeId', 'bodyNumber'];

    // Validate required fields from request
    const phoneNumber = document.getElementById('applicant-phone-number');
    if (!phoneNumber || !phoneNumber.value || phoneNumber.value.trim() === '') {
        dom.showToast('error', 'Error', 'Phone number is required');
        return false;
    }

    const isApplicantOwner = document.getElementById('is-applicant-owner');
    if (!isApplicantOwner) {
        dom.showToast('error', 'Error', 'Please specify if applicant is owner');
        return false;
    }

    // Validate other info (vehicle technical information)
    if (!formState.otherInfo) {
        dom.showToast('error', 'Error', 'Vehicle technical information is missing');
        return false;
    }

    const otherMissing = requiredOtherKeys.filter(key =>
        !(key in formState.otherInfo) || formState.otherInfo[key] === null || formState.otherInfo[key] === ''
    );

    if (otherMissing.length > 0) {
        console.error('Missing other info fields:', otherMissing);
        dom.showToast('error', 'Error', 'Vehicle technical information is incomplete');
        return false;
    }

    // Validate drivers if limited
    if (formState.driverLimit === 'limited') {
        if (!formState.drivers || formState.drivers.length === 0) {
            dom.showToast('error', 'XALQ SUG\'URTA', 'Cheklangan haydovchilar uchun kamida 1 ta haydovchi qo\'shish kerak / Please add at least 1 driver for limited drivers');
            dom.scrollToElement('limited-drivers-info');
            return false;
        }

        if (formState.drivers.length > 5) {
            dom.showToast('error', 'XALQ SUG\'URTA', 'Maksimal 5 ta haydovchi qo\'shish mumkin / Maximum 5 drivers allowed');
            return false;
        }
    }

    return true;
}

/**
 * Handle old values from Laravel validation
 */
function handleOldValues() {
    // Check if we have old values (from validation errors)
    const hasOldModel = document.querySelector('input[name="model"]')?.value;
    const hasOldOwner = document.querySelector('input[name="owner[lastName]"]')?.value;
    const hasOldApplicant = document.querySelector('input[name="applicant[lastName]"]')?.value;
    const hasOldDriverLimit = document.querySelector('input[name="driver_limit"]')?.value;

    if (hasOldModel) {
        dom.showSection('vehicle-info-display');
        dom.showSection('owner-info');
    }

    if (hasOldOwner) {
        dom.showSection('insurance-driver-full-information');
        dom.showSection('applicant-info');
    }

    if (hasOldApplicant) {
        dom.showSection('applicant-info-display');
        dom.showSection('policy-calculation');
        dom.showSection('confirmation');
    }

    if (hasOldDriverLimit === 'limited') {
        dom.showSection('limited-drivers-info');
        dom.showSection('note');
    }

    // Recalculate policy if we have old values
    if (hasOldModel) {
        setTimeout(() => {
            policyHandler.updateEndDate();
            policyHandler.updateCalculation();
        }, 100);
    }
}

/**
 * Setup Bootstrap-specific features
 */
function setupBootstrapFeatures() {
    // Bootstrap alert close functionality
    document.querySelectorAll('.btn-close').forEach(button => {
        button.addEventListener('click', function() {
            const alert = this.closest('.alert');
            if (alert) {
                alert.style.transition = 'opacity 0.15s linear';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 150);
            }
        });
    });
}

// Export for debugging
export { formState };
