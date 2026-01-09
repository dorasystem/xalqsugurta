/**
 * Vehicle Handler Module
 * Handles vehicle information search and display
 */

import { vehicleAPI } from '../api.js';
import CONFIG, { getVehicleType } from '../config.js';
import formState from '../state.js';
import * as dom from '../dom-utils.js';
import * as validators from '../validators.js';

export class VehicleHandler {
    constructor() {
        this.elements = {
            govNumber: document.getElementById('gov_number'),
            techSeries: document.getElementById('tech_passport_series'),
            techNumber: document.getElementById('tech_passport_number'),
            searchBtn: document.getElementById('vehicle-search-btn'),
            display: document.getElementById('vehicle-info-display'),
            searchSection: document.getElementById('vehicle-info')
        };

        this.init();
    }

    init() {
        this.attachEventListeners();
        this.setupValidation();
    }

    attachEventListeners() {
        if (this.elements.searchBtn) {
            this.elements.searchBtn.addEventListener('click', () => this.handleSearch());
        }

        // Listen to state changes
        formState.on('vehicle-updated', () => this.renderDisplay());
        formState.on('vehicle-cleared', () => this.handleClear());
    }

    setupValidation() {
        const { govNumber, techSeries, techNumber } = this.elements;

        if (govNumber) {
            dom.attachFieldValidation(
                govNumber,
                validators.validateGovNumber,
                'Invalid government number'
            );
        }

        if (techSeries) {
            dom.attachFieldValidation(
                techSeries,
                validators.validateTechPassportSeries,
                'Invalid tech passport series'
            );
        }

        if (techNumber) {
            dom.attachFieldValidation(
                techNumber,
                validators.validateTechPassportNumber,
                'Invalid tech passport number'
            );
        }
    }

    async handleSearch() {
        const { govNumber, techSeries, techNumber, searchBtn } = this.elements;

        // Validate inputs
        const errors = [];

        const govError = validators.validateGovNumber(govNumber?.value);
        if (govError) {
            errors.push(govError);
            dom.showFieldError(govNumber, govError);
        }

        const seriesError = validators.validateTechPassportSeries(techSeries?.value);
        if (seriesError) {
            errors.push(seriesError);
            dom.showFieldError(techSeries, seriesError);
        }

        const numberError = validators.validateTechPassportNumber(techNumber?.value);
        if (numberError) {
            errors.push(numberError);
            dom.showFieldError(techNumber, numberError);
        }

        if (errors.length > 0) {
            dom.showToast('error', 'Error', 'Please fill all fields correctly');
            return;
        }

        // Prepare data - remove spaces and formatting
        const data = {
            gov_number: govNumber.value.replace(/\s/g, '').trim().toUpperCase(),
            tech_passport_series: techSeries.value.trim().toUpperCase(),
            tech_passport_number: techNumber.value.trim()
        };

        // Show loading state
        dom.setButtonLoading(searchBtn, true);

        try {
            const result = await vehicleAPI.search(data);

            if (result?.data?.result) {
                this.populateVehicleData(result.data.result, data);
                dom.showSection('vehicle-info-display');
                dom.showSection('owner-info');
                dom.scrollToElement('vehicle-info-display');
            } else {
                dom.showToast('error', 'XALQ SUG\'URTA', 'Vehicle information not found');
            }
        } catch (error) {
            console.error('Vehicle search error:', error);
            dom.showToast('error', 'XALQ SUG\'URTA', 'Error: ' + error.message);
        } finally {
            dom.setButtonLoading(searchBtn, false);
        }
    }

    populateVehicleData(apiResult, searchData) {
        // Lock search inputs
        dom.lockInput(this.elements.govNumber);
        dom.lockInput(this.elements.techSeries);
        dom.lockInput(this.elements.techNumber);

        // Populate visible fields
        const fields = {
            'engine_number': apiResult.engineNumber || '',
            'car_year': apiResult.issueYear || '',
            'registration_region': apiResult.division || '',
            'car_owner': apiResult.owner || '',
            'model': apiResult.modelName || '',
            'insurance-pinfl': apiResult.pinfl || ''
        };

        dom.populateFields(fields);

        // Handle vehicle type (get label from translations)
        const carTypeEl = document.getElementById('car_type');
        const vehicleTypeInfo = getVehicleType(apiResult.vehicleTypeId);

        if (vehicleTypeInfo) {
            formState.vehicleType = vehicleTypeInfo.coef;
            if (carTypeEl) carTypeEl.value = vehicleTypeInfo.label;
        } else {
            if (carTypeEl) carTypeEl.value = 'Vehicle type not found';
        }

        // Store other info
        const otherInfo = {
            techPassportIssueDate: apiResult.techPassportIssueDate?.split('T')[0],
            typeId: apiResult.vehicleTypeId,
            bodyNumber: apiResult.bodyNumber
        };

        formState.setOtherInfo(otherInfo);

        // Store vehicle data in state
        const vehicleData = {
            govNumber: searchData.gov_number,
            techSeries: searchData.tech_passport_series,
            techNumber: searchData.tech_passport_number,
            ...apiResult
        };

        formState.setVehicle(vehicleData);
    }

    renderDisplay() {
        // Display is handled by Blade component, but we show the section
        dom.showSection('vehicle-info-display');
    }

    handleClear() {
        // Unlock inputs
        dom.unlockInput(this.elements.govNumber);
        dom.unlockInput(this.elements.techSeries);
        dom.unlockInput(this.elements.techNumber);

        // Clear values
        if (this.elements.govNumber) this.elements.govNumber.value = '';
        if (this.elements.techSeries) this.elements.techSeries.value = '';
        if (this.elements.techNumber) this.elements.techNumber.value = '';

        // Hide display
        dom.hideSection('vehicle-info-display');
        dom.hideSection('owner-info');

        // Clear errors
        dom.clearFieldError(this.elements.govNumber);
        dom.clearFieldError(this.elements.techSeries);
        dom.clearFieldError(this.elements.techNumber);
    }

    enableEdit() {
        const message = window.TRANSLATIONS?.edit_vehicle_warning ||
            'Editing vehicle information will clear owner, applicant, and driver data. Continue?';

        if (dom.confirm(message)) {
            formState.clearVehicle();
            dom.scrollToElement('vehicle-info');
        }
    }
}

// Export singleton instance
export const vehicleHandler = new VehicleHandler();

// Make edit function globally available for Edit button
window.editVehicle = () => vehicleHandler.enableEdit();

export default vehicleHandler;
