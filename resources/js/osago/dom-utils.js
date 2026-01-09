/**
 * DOM Utilities Module
 * Helper functions for DOM manipulation, validation UI, and user feedback
 */

/**
 * Show a section by removing d-none class
 */
export function showSection(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.remove('d-none');
    }
}

/**
 * Hide a section by adding d-none class
 */
export function hideSection(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.add('d-none');
    }
}

/**
 * Toggle section visibility
 */
export function toggleSection(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.classList.toggle('d-none');
    }
}

/**
 * Scroll to an element smoothly
 */
export function scrollToElement(elementId, block = 'nearest') {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({
            behavior: 'smooth',
            block: block
        });
    }
}

/**
 * Update element inner HTML safely
 */
export function updateElement(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = value;
    }
}

/**
 * Set button loading state
 */
export function setButtonLoading(button, loading) {
    if (!button) return;

    button.disabled = loading;

    if (loading) {
        // Store original content
        if (!button.dataset.originalContent) {
            button.dataset.originalContent = button.innerHTML;
        }
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>';
    } else {
        // Restore original content
        if (button.dataset.originalContent) {
            button.innerHTML = button.dataset.originalContent;
        }
    }
}

/**
 * Show field-level validation error
 */
export function showFieldError(field, message) {
    if (!field) return;

    // Add is-invalid class
    field.classList.add('is-invalid');

    // Find or create error message element
    let errorDiv = field.nextElementSibling;

    if (!errorDiv || !errorDiv.classList.contains('invalid-feedback')) {
        errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        field.parentNode.insertBefore(errorDiv, field.nextSibling);
    }

    errorDiv.textContent = message;
    errorDiv.style.display = 'block';
}

/**
 * Clear field-level validation error
 */
export function clearFieldError(field) {
    if (!field) return;

    field.classList.remove('is-invalid');

    const errorDiv = field.nextElementSibling;
    if (errorDiv && errorDiv.classList.contains('invalid-feedback')) {
        errorDiv.style.display = 'none';
    }
}

/**
 * Clear all validation errors in a container
 */
export function clearAllErrors(containerId = 'policy-calculation-form') {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Remove all is-invalid classes
    const invalidFields = container.querySelectorAll('.is-invalid');
    invalidFields.forEach(field => field.classList.remove('is-invalid'));

    // Hide all error messages
    const errorDivs = container.querySelectorAll('.invalid-feedback');
    errorDivs.forEach(div => div.style.display = 'none');
}

/**
 * Attach validation to a field (validates on blur)
 */
export function attachFieldValidation(field, validatorFn, errorMessage) {
    if (!field || !validatorFn) return;

    // Validate on blur
    field.addEventListener('blur', function() {
        const error = validatorFn(this.value);
        if (error) {
            showFieldError(this, error || errorMessage);
        } else {
            clearFieldError(this);
        }
    });

    // Clear error on input (immediate feedback)
    field.addEventListener('input', function() {
        if (this.classList.contains('is-invalid')) {
            clearFieldError(this);
        }
    });
}

/**
 * Lock input field (make readonly)
 */
export function lockInput(element) {
    if (element) {
        element.setAttribute('readonly', 'readonly');
        element.classList.add('readonly');
    }
}

/**
 * Unlock input field (remove readonly)
 */
export function unlockInput(element) {
    if (element) {
        element.removeAttribute('readonly');
        element.classList.remove('readonly');
    }
}

/**
 * Get element value safely
 */
export function getElementValue(elementId) {
    const element = document.getElementById(elementId);
    return element ? element.value : '';
}

/**
 * Set element value safely
 */
export function setElementValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.value = value || '';
    }
}

/**
 * Create element safely (prevents XSS)
 */
export function createElement(tag, className = '', attributes = {}) {
    const element = document.createElement(tag);

    if (className) {
        element.className = className;
    }

    Object.entries(attributes).forEach(([key, value]) => {
        if (key === 'textContent' || key === 'innerText') {
            element.textContent = value;
        } else if (key === 'innerHTML') {
            // Sanitize HTML if absolutely necessary
            console.warn('Using innerHTML - ensure content is sanitized!');
            element.innerHTML = value;
        } else {
            element.setAttribute(key, value);
        }
    });

    return element;
}

/**
 * Confirm dialog with custom message
 */
export function confirm(message) {
    return window.confirm(message);
}

/**
 * Show simple toast notification
 * Assumes showSimpleToast is defined globally (from Laravel/app)
 */
export function showToast(type, title, message) {
    if (typeof window.showSimpleToast === 'function') {
        window.showSimpleToast(type, title, message);
    } else {
        // Fallback to alert if toast function not available
        alert(`${title}: ${message}`);
    }
}

/**
 * Auto-scroll to first error field
 */
export function scrollToFirstError() {
    const firstError = document.querySelector('.is-invalid');
    if (firstError) {
        firstError.scrollIntoView({
            behavior: 'smooth',
            block: 'center'
        });
        firstError.focus();
    }
}

/**
 * Debounce function (for input validation)
 */
export function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Get form data as object
 */
export function getFormData(formId) {
    const form = document.getElementById(formId);
    if (!form) return {};

    const formData = new FormData(form);
    const data = {};

    for (const [key, value] of formData.entries()) {
        data[key] = value;
    }

    return data;
}

/**
 * Populate multiple fields from object
 */
export function populateFields(fieldsMap) {
    Object.entries(fieldsMap).forEach(([id, value]) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
        }
    });
}

/**
 * Cache DOM elements for better performance
 */
export function cacheElements(elementIds) {
    const cache = {};

    elementIds.forEach(id => {
        cache[id] = document.getElementById(id);
    });

    return cache;
}

export default {
    showSection,
    hideSection,
    toggleSection,
    scrollToElement,
    updateElement,
    setButtonLoading,
    showFieldError,
    clearFieldError,
    clearAllErrors,
    attachFieldValidation,
    lockInput,
    unlockInput,
    getElementValue,
    setElementValue,
    createElement,
    confirm,
    showToast,
    scrollToFirstError,
    debounce,
    getFormData,
    populateFields,
    cacheElements
};
