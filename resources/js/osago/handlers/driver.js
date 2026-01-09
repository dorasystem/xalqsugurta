/**
 * Driver Handler Module
 */

import { driverAPI, personAPI } from '../api.js';
import CONFIG from '../config.js';
import formState from '../state.js';
import * as dom from '../dom-utils.js';
import * as validators from '../validators.js';

export class DriverHandler {
    constructor() {
        this.elements = {
            passportSeries: document.getElementById('driver-passport-series'),
            passportNumber: document.getElementById('driver-passport-number'),
            pinfl: document.getElementById('driver-pinfl'),
            searchBtn: document.getElementById('driver-information-search-btn'),
            display: document.getElementById('driver-info-display'),
            unlimitedRadio: document.getElementById('driver_unlimited'),
            limitedRadio: document.getElementById('driver_limited')
        };

        this.init();
    }

    init() {
        if (this.elements.searchBtn) {
            this.elements.searchBtn.addEventListener('click', () => this.handleAddDriver());
        }

        if (this.elements.unlimitedRadio) {
            this.elements.unlimitedRadio.addEventListener('change', () => this.handleLimitChange('unlimited'));
        }

        if (this.elements.limitedRadio) {
            this.elements.limitedRadio.addEventListener('change', () => this.handleLimitChange('limited'));
        }

        // Global click handler for delete buttons
        document.addEventListener('click', (e) => {
            const button = e.target.closest('.btn-danger[data-target]');
            if (button) {
                this.handleDeleteDriver(button.getAttribute('data-target'));
            }
        });

        formState.on('driver-added', () => this.renderDrivers());
        formState.on('driver-removed', () => this.renderDrivers());

        // Ensure insurance_period select is always enabled on init
        // According to new 2026 regulations, both unlimited and limited drivers can choose period
        const periodSelect = document.getElementById('insurance_period');
        if (periodSelect) {
            periodSelect.removeAttribute('disabled');
            periodSelect.removeAttribute('readonly');
        }

        // Initialize driver limit section visibility based on checked radio
        // Ensure limited-drivers-info is hidden by default
        const limitedDriversInfo = document.getElementById('limited-drivers-info');
        if (limitedDriversInfo) {
            limitedDriversInfo.classList.add('d-none');
        }

        // Check which radio is checked and update visibility
        if (this.elements.unlimitedRadio && this.elements.unlimitedRadio.checked) {
            this.handleLimitChange('unlimited');
        } else if (this.elements.limitedRadio && this.elements.limitedRadio.checked) {
            this.handleLimitChange('limited');
        } else {
            // Default to unlimited (hide limited-drivers-info)
            this.handleLimitChange('unlimited');
        }
    }

    handleLimitChange(type) {
        formState.setDriverLimit(type);

        if (type === 'limited') {
            dom.showSection('limited-drivers-info');
            dom.showSection('note');
        } else {
            dom.hideSection('limited-drivers-info');
            document.getElementById('limited-drivers-info').classList.add('d-none')
            dom.hideSection('note');
        }

        // Insurance period select should always be enabled
        // According to new 2026 regulations, both unlimited and limited drivers can choose period
        const periodSelect = document.getElementById('insurance_period');
        if (periodSelect) {
            periodSelect.removeAttribute('disabled');
        }
    }

    async handleAddDriver() {
        const { passportSeries, passportNumber, pinfl, searchBtn, display } = this.elements;

        if (!validators.validateInputs([passportSeries, passportNumber, pinfl])) {
            dom.showToast('error', 'Error', 'Please fill all fields');
            return;
        }

        // Check max drivers
        if (formState.drivers.length >= CONFIG.MAX_DRIVERS) {
            dom.showToast('error', 'XALQ SUG\'URTA', `Maximum ${CONFIG.MAX_DRIVERS} drivers allowed`);
            return;
        }

        // Clean data - remove spaces from PINFL and uppercase passport series
        const cleanPinfl = pinfl.value.replace(/\s/g, '').trim();
        const driverData = {
            passport_series: passportSeries.value.trim().toUpperCase(),
            passport_number: passportNumber.value.trim(),
            pinfl: cleanPinfl
        };

        const personData = {
            ...driverData,
            senderPinfl: cleanPinfl,
            isConsent: 'Y'
        };

        dom.setButtonLoading(searchBtn, true);

        try {
            const driverResult = await driverAPI.search(driverData);

            if (!driverResult?.success || !driverResult?.data?.result) {
                throw new Error('Driver information not found');
            }

            const shortResult = driverResult.data.result;
            const personResult = await personAPI.search(personData);

            if (!personResult?.data?.result) {
                throw new Error('Driver personal information not found');
            }

            const driverInfo = {
                pinfl: driverData.pinfl,
                seria: driverData.passport_series,
                number: driverData.passport_number,
                issuedBy: personResult.data.result.issuedBy,
                issueDate: personResult.data.result.startDate,
                firstname: personResult.data.result.firstNameLatin,
                lastname: personResult.data.result.lastNameLatin,
                middlename: personResult.data.result.middleNameLatin,
                licenseNumber: shortResult.DriverInfo.licenseNumber,
                licenseSeria: shortResult.DriverInfo.licenseSeria,
                birthDate: shortResult.DriverInfo.pOwnerDate,
                birthPlace: personResult.data.result.birthPlace,
                licenseIssueDate: shortResult.DriverInfo.issueDate?.split('T')[0]
            };

            formState.addDriver(driverInfo);

            // Clear input fields
            passportSeries.value = '';
            passportNumber.value = '';
            pinfl.value = '';

        } catch (error) {
            dom.showToast('error', 'XALQ SUG\'URTA', 'Error: ' + error.message);
        } finally {
            dom.setButtonLoading(searchBtn, false);
        }
    }

    handleDeleteDriver(driverId) {
        if (dom.confirm('Remove this driver?')) {
            formState.removeDriver(parseInt(driverId));
        }
    }

    renderDrivers() {
        if (!this.elements.display) return;

        // Clear existing display
        this.elements.display.innerHTML = '';

        // Render each driver using safe DOM manipulation (no XSS)
        formState.drivers.forEach(driver => {
            this.elements.display.appendChild(this.createDriverCard(driver));
        });
    }

    createDriverCard(driver) {
        const cardFooter = dom.createElement('div', 'card-footer mb-3', {
            'data-id': driver.id
        });

        const title = dom.createElement('h4', 'card-title', {
            textContent: window.TRANSLATIONS?.driver_info_title || 'Driver Information'
        });

        const row = dom.createElement('div', 'row mb-2');

        // Name column
        const nameCol = dom.createElement('div', 'col-md-5');
        const nameLabel = dom.createElement('label', 'form-label', {
            textContent: window.TRANSLATIONS?.driver_full_name || 'Full Name'
        });
        const nameInput = dom.createElement('input', 'form-input', {
            type: 'text',
            name: `driver_full_name[${driver.id}]`,
            value: `${driver.firstname || ''} ${driver.lastname || ''}`,
            readonly: true
        });
        const hiddenInput = dom.createElement('input', '', {
            type: 'hidden',
            name: `driver_full_info[${driver.id}]`,
            value: JSON.stringify(driver)
        });

        nameCol.appendChild(nameLabel);
        nameCol.appendChild(nameInput);
        nameCol.appendChild(hiddenInput);

        // Delete button column
        const deleteCol = dom.createElement('div', 'col-md-7 d-flex flex-column align-items-end justify-content-end');
        const deleteBtn = dom.createElement('button', 'btn btn-icon btn-danger btn-sm', {
            type: 'button',
            'data-target': driver.id
        });
        deleteBtn.innerHTML = '<svg width="20" height="20"><use xlink:href="#icon-cancel"></use></svg>';

        deleteCol.appendChild(deleteBtn);

        // Assemble
        row.appendChild(nameCol);
        row.appendChild(deleteCol);

        cardFooter.appendChild(title);
        cardFooter.appendChild(row);

        return cardFooter;
    }
}

export const driverHandler = new DriverHandler();
export default driverHandler;
