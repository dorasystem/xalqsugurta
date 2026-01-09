/**
 * Policy Handler Module
 */

import CONFIG from '../config.js';
import formState from '../state.js';
import * as dom from '../dom-utils.js';
import { formatCurrency, formatDateForInput, calculateEndDate, parseDateFromInput } from '../formatters.js';

export class PolicyHandler {
    constructor() {
        this.elements = {
            startInput: document.getElementById('policy_start_date'),
            endInput: document.getElementById('policy_end_date'),
            periodSelect: document.getElementById('insurance_period')
        };

        this.init();
    }

    init() {
        if (this.elements.startInput) {
            // Set default to today if empty
            if (!this.elements.startInput.value) {
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                this.elements.startInput.value = formatDateForInput(today);
            }

            this.elements.startInput.addEventListener('change', () => this.updateEndDate());

            // Initialize end date on load
            this.updateEndDate();
        }

        if (this.elements.periodSelect) {
            this.elements.periodSelect.addEventListener('change', () => this.updateEndDate());
        }

        formState.on('vehicle-updated', () => this.updateCalculation());
        formState.on('driver-limit-changed', () => this.updateCalculation());
        formState.on('driver-added', () => this.updateCalculation());
        formState.on('driver-removed', () => this.updateCalculation());
        formState.on('policy-calculated', (policy) => this.renderAmount(policy));
    }

    updateEndDate() {
        if (!this.elements.startInput || !this.elements.endInput || !this.elements.periodSelect) {
            return;
        }

        // Get or set start date
        let startDate;
        if (!this.elements.startInput.value) {
            startDate = new Date();
            startDate.setHours(0, 0, 0, 0);
            this.elements.startInput.value = formatDateForInput(startDate);
        } else {
            startDate = parseDateFromInput(this.elements.startInput.value);
        }

        // Calculate end date
        const periodValue = this.elements.periodSelect.value;
        const periodNumber = parseFloat(periodValue) || 1;
        const endDate = calculateEndDate(startDate, periodValue);

        if (endDate) {
            this.elements.endInput.value = formatDateForInput(endDate);
        }

        // Store in state (period must be integer for validation)
        // Map period values: 1 = 1 year, 0.7 = 6 months (1), 0.4 = 3 months (1)
        const periodInteger = periodNumber >= 1 ? 1 : 1; // Always 1 for now (can be extended later)

        formState.setPolicy({
            startDate: startDate,
            endDate: endDate,
            period: periodInteger
        });

        // Recalculate
        this.updateCalculation();
    }

    updateCalculation() {
        const govNumberEl = document.getElementById('gov_number');
        if (!govNumberEl || !govNumberEl.value) return;

        // Determine region coefficient based on government number prefix
        const govPrefix = govNumberEl.value.trim().substring(0, 2);
        const isTashkent = CONFIG.TASHKENT_PREFIXES.includes(govPrefix);

        formState.regionId = isTashkent ? CONFIG.REGION_TASHKENT : CONFIG.REGION_OTHER;

        // Calculate policy amount
        const amount = formState.calculatePolicy();

        if (amount) {
            // Update hidden input for form submission
            const insuranceInfosEl = document.getElementById('insurance-infos');
            if (insuranceInfosEl) {
                insuranceInfosEl.value = JSON.stringify({
                    amount: formatCurrency(amount),
                    period: parseInt(formState.policy.period) || 1, // Must be integer for validation
                    insuranceAmount: CONFIG.INSURANCE_AMOUNT
                });
            }
        }
    }

    renderAmount(policy) {
        // Update amount display
        dom.updateElement('amount', formatCurrency(policy.amount));
        dom.updateElement('premium', formatCurrency(CONFIG.INSURANCE_AMOUNT));
    }
}

export const policyHandler = new PolicyHandler();
export default policyHandler;
