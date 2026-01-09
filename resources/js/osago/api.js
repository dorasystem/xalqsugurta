/**
 * API Module
 * Handles all HTTP requests with retry logic, CSRF protection, and race condition guards
 */

// Track in-progress requests to prevent race conditions
const inProgressRequests = new Map();

/**
 * Get CSRF token from meta tag
 * @throws {Error} If token is not found
 */
function getCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    if (!token) {
        throw new Error('CSRF token not found. Please refresh the page.');
    }

    return token;
}

/**
 * Send POST request with retry logic and error handling
 * @param {string} url - The endpoint URL
 * @param {object} data - The request payload
 * @param {number} retries - Number of retry attempts (default: 3)
 * @param {boolean} preventDuplicates - Prevent duplicate simultaneous requests (default: true)
 * @returns {Promise<object>} Response data
 */
export async function sendPostRequest(url, data, retries = 3, preventDuplicates = true) {
    // Generate request key for duplicate prevention
    const requestKey = `${url}-${JSON.stringify(data)}`;

    // Check if same request is already in progress
    if (preventDuplicates && inProgressRequests.has(requestKey)) {
        console.warn(`Duplicate request blocked: ${url}`);
        return inProgressRequests.get(requestKey);
    }

    // Create the request promise
    const requestPromise = performRequest(url, data, retries);

    // Store in progress map
    if (preventDuplicates) {
        inProgressRequests.set(requestKey, requestPromise);
    }

    try {
        const result = await requestPromise;
        return result;
    } finally {
        // Clean up after request completes
        if (preventDuplicates) {
            inProgressRequests.delete(requestKey);
        }
    }
}

/**
 * Perform the actual HTTP request with retry logic
 */
async function performRequest(url, data, retries) {
    const csrfToken = getCsrfToken();

    for (let attempt = 1; attempt <= retries; attempt++) {
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(data)
            });

            // Handle session expiration
            if (response.status === 419) {
                throw new Error('Session expired. Please refresh the page.');
            }

            // Parse response
            const result = await response.json();

            // Check if response is ok
            if (!response.ok) {
                throw new Error(result.message || `Request failed with status ${response.status}`);
            }

            return result;

        } catch (error) {
            // If this is the last attempt, throw the error
            if (attempt === retries) {
                console.error(`Request failed after ${retries} attempts:`, error);
                throw error;
            }

            // Exponential backoff before retry
            const delay = 1000 * Math.pow(2, attempt - 1);
            console.warn(`Request attempt ${attempt} failed, retrying in ${delay}ms...`);
            await new Promise(resolve => setTimeout(resolve, delay));
        }
    }
}

/**
 * Vehicle API methods
 */
export const vehicleAPI = {
    async search(data) {
        return sendPostRequest('/get-vehicle-info', data);
    }
};

/**
 * Person API methods
 */
export const personAPI = {
    async search(data) {
        return sendPostRequest('/get-person-info', data);
    }
};

/**
 * Driver API methods
 */
export const driverAPI = {
    async search(data) {
        return sendPostRequest('/get-driver-info', data);
    }
};

/**
 * Clear all in-progress requests (useful for cleanup)
 */
export function clearInProgressRequests() {
    inProgressRequests.clear();
}

/**
 * Check if a request is in progress
 */
export function isRequestInProgress(url, data = null) {
    const requestKey = data ? `${url}-${JSON.stringify(data)}` : url;
    return inProgressRequests.has(requestKey);
}

export default {
    sendPostRequest,
    vehicleAPI,
    personAPI,
    driverAPI,
    clearInProgressRequests,
    isRequestInProgress
};
