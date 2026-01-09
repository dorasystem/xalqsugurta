/**
 * OSAGO Form State Management
 * Centralized state with event-driven updates
 */

import CONFIG from './config.js';

export class OSAGOFormState {
    constructor() {
        // Form data
        this.vehicle = null;
        this.owner = null;
        this.applicant = null;
        this.policy = {
            startDate: null,
            endDate: null,
            period: 1,
            amount: 0,
            insuranceAmount: CONFIG.INSURANCE_AMOUNT
        };
        this.drivers = [];
        this.otherInfo = null;

        // Form state
        this.isApplicantOwner = false;
        this.currentStep = 1;
        this.editMode = null;
        this.driverIdCounter = 0;

        // Calculation variables
        this.regionId = null;
        this.vehicleType = null;
        this.limitedCoef = CONFIG.DRIVER_COEF.UNLIMITED;
        this.driverLimit = 'unlimited';

        // New coefficients (according to 2026 regulations)
        this.accidentCoef = CONFIG.ACCIDENT_COEF.NO_ACCIDENTS;  // КБМ - default: no accidents
        this.experienceCoef = CONFIG.EXPERIENCE_COEF;  // КВ - always 1.0
        this.violationCoef = CONFIG.VIOLATION_COEF;  // КН - always 1.0
        this.ageCoef = CONFIG.AGE_COEF;  // КВЗ - always 1.0

        // Event listeners
        this.listeners = {};
    }

    // Event emitter methods
    on(event, callback) {
        if (!this.listeners[event]) {
            this.listeners[event] = [];
        }
        this.listeners[event].push(callback);
    }

    emit(event, data) {
        if (this.listeners[event]) {
            this.listeners[event].forEach(callback => callback(data));
        }
    }

    // Vehicle methods
    setVehicle(data) {
        this.vehicle = data;
        this.emit('vehicle-updated', data);
    }

    clearVehicle() {
        this.vehicle = null;
        this.clearOwner(); // Cascade clear
        this.emit('vehicle-cleared');
    }

    // Owner methods
    setOwner(data) {
        this.owner = data;

        // Auto-sync applicant if checkbox is checked
        if (this.isApplicantOwner) {
            this.applicant = { ...data };
        }

        this.emit('owner-updated', data);
    }

    clearOwner() {
        this.owner = null;
        this.clearApplicant(); // Cascade clear
        this.emit('owner-cleared');
    }

    // Applicant methods
    setApplicant(data) {
        this.applicant = data;
        this.emit('applicant-updated', data);
    }

    clearApplicant() {
        this.applicant = null;
        this.clearDrivers(); // Cascade clear
        this.emit('applicant-cleared');
    }

    toggleApplicantIsOwner(checked) {
        this.isApplicantOwner = checked;

        if (checked && this.owner) {
            this.applicant = { ...this.owner };
            this.emit('applicant-updated', this.applicant);
        } else if (!checked) {
            this.applicant = null;
            this.emit('applicant-cleared');
        }

        this.emit('applicant-owner-toggled', checked);
    }

    // Driver methods
    addDriver(driver) {
        if (this.drivers.length >= CONFIG.MAX_DRIVERS) {
            throw new Error(`Maximum ${CONFIG.MAX_DRIVERS} drivers allowed`);
        }

        this.driverIdCounter++;
        driver.id = this.driverIdCounter;
        this.drivers.push(driver);
        this.emit('driver-added', driver);
    }

    removeDriver(id) {
        const index = this.drivers.findIndex(d => d.id === id);
        if (index > -1) {
            const driver = this.drivers[index];
            this.drivers.splice(index, 1);
            this.emit('driver-removed', driver);
        }
    }

    clearDrivers() {
        this.drivers = [];
        this.emit('drivers-cleared');
    }

    // Policy methods
    setPolicy(data) {
        this.policy = { ...this.policy, ...data };
        this.emit('policy-updated', this.policy);
    }

    calculatePolicy() {
        if (!this.vehicleType || !this.regionId) {
            return;
        }

        // Get seasonal coefficient (КС) based on period
        // 6 months (0.7) = 0.7, 12 months (1) = 1.0
        const periodValue = this.policy.period >= 1 ? '1' : '0.7';
        const seasonalCoef = CONFIG.SEASONAL_COEF[periodValue] || 1.0;

        let amount;

        // New formula according to 2026 regulations
        if (this.driverLimit === 'unlimited') {
            // Unlimited drivers: ПР = СС х ТБ х КТ х КБО х КС / 100
            // For 12 months: КС = 1.0, so: ПР = СС х ТБ х КТ х КБО / 100
            amount = (CONFIG.INSURANCE_AMOUNT * this.vehicleType * this.regionId * this.limitedCoef * seasonalCoef) / 100;
        } else {
            // Limited drivers: ПР = СС х ТБ х КТ х КБМ х КВ х КС х КН х КВЗ / 100
            // КВ, КН, КВЗ are always 1.0, so simplified: ПР = СС х ТБ х КТ х КБМ х КС / 100
            amount = (CONFIG.INSURANCE_AMOUNT * this.vehicleType * this.regionId * this.accidentCoef * this.experienceCoef * seasonalCoef * this.violationCoef * this.ageCoef) / 100;
        }

        // Fallback to default if calculation fails
        if (isNaN(amount) || amount === 0) {
            amount = CONFIG.DEFAULT_AMOUNT;
        }

        this.policy.amount = amount;
        this.emit('policy-calculated', this.policy);

        return amount;
    }

    // Driver limit methods
    setDriverLimit(limit) {
        this.driverLimit = limit;
        this.limitedCoef = limit === 'unlimited'
            ? CONFIG.DRIVER_COEF.UNLIMITED
            : CONFIG.DRIVER_COEF.LIMITED;

        this.emit('driver-limit-changed', limit);
        this.calculatePolicy();
    }

    // Other info methods
    setOtherInfo(data) {
        this.otherInfo = data;
        this.emit('other-info-updated', data);
    }

    // Validation
    isValid() {
        return !!(
            this.vehicle &&
            this.owner &&
            this.applicant &&
            this.policy.amount > 0 &&
            this.otherInfo
        );
    }

    isStepComplete(step) {
        switch(step) {
            case 1: return !!this.vehicle;
            case 2: return !!this.owner;
            case 3: return !!this.applicant;
            case 4: return this.policy.amount > 0;
            case 5: return this.driverLimit === 'unlimited' || this.drivers.length > 0;
            case 6: return this.isValid();
            default: return false;
        }
    }

    // Edit mode
    enterEditMode(section) {
        this.editMode = section;
        this.emit('edit-mode-entered', section);
    }

    exitEditMode() {
        this.editMode = null;
        this.emit('edit-mode-exited');
    }

    // Serialization for form submission
    toJSON() {
        return {
            vehicle: this.vehicle,
            owner: this.owner,
            applicant: this.applicant,
            policy: this.policy,
            drivers: this.drivers,
            otherInfo: this.otherInfo,
            driverLimit: this.driverLimit
        };
    }

    toFormData() {
        // Owner and applicant infos are loaded from session (PersonInfoController)
        // Only send required fields for validation
        return {
            'other_info': JSON.stringify(this.otherInfo),
            'insurance-infos': JSON.stringify({
                amount: this.policy.amount.toLocaleString('en-US', { minimumFractionDigits: 2 }),
                period: parseInt(this.policy.period) || 1, // Must be integer for validation
                insuranceAmount: CONFIG.INSURANCE_AMOUNT
            }),
            'driver_limit': this.driverLimit
        };
    }

    // Reset
    reset() {
        this.vehicle = null;
        this.owner = null;
        this.applicant = null;
        this.policy = {
            startDate: null,
            endDate: null,
            period: 1,
            amount: 0,
            insuranceAmount: CONFIG.INSURANCE_AMOUNT
        };
        this.drivers = [];
        this.otherInfo = null;
        this.isApplicantOwner = false;
        this.currentStep = 1;
        this.editMode = null;
        this.driverIdCounter = 0;
        this.regionId = null;
        this.vehicleType = null;
        this.limitedCoef = CONFIG.DRIVER_COEF.UNLIMITED;
        this.driverLimit = 'unlimited';

        this.emit('state-reset');
    }
}

// Create singleton instance
export const formState = new OSAGOFormState();

export default formState;
