/**
 * Owner Handler Module
 */

import { personAPI } from '../api.js';
import formState from '../state.js';
import * as dom from '../dom-utils.js';
import * as validators from '../validators.js';

console.log('✅ Owner Handler module loaded successfully');

export class OwnerHandler {
    constructor() {
        this.elements = {
            passportSeries: document.getElementById('insurance-passport-series'),
            passportNumber: document.getElementById('insurance-passport-number'),
            pinfl: document.getElementById('insurance-pinfl'),
            searchBtn: document.getElementById('owner-information-search-btn')
        };

        this.init();
    }

    init() {
        if (this.elements.searchBtn) {
            this.elements.searchBtn.addEventListener('click', () => this.handleSearch());
        }

        formState.on('owner-updated', () => this.renderDisplay());
        formState.on('owner-cleared', () => this.handleClear());
    }

    async handleSearch() {
        const { passportSeries, passportNumber, pinfl, searchBtn } = this.elements;

        // Validate
        if (!validators.validateInputs([passportSeries, passportNumber, pinfl])) {
            dom.showToast('error', 'Error', 'Please fill all fields');
            return;
        }

        // Clean data - remove spaces from PINFL and uppercase passport series
        const cleanPinfl = pinfl.value.replace(/\s/g, '').trim();
        const data = {
            senderPinfl: cleanPinfl,
            passport_series: passportSeries.value.trim().toUpperCase(),
            passport_number: passportNumber.value.trim(),
            pinfl: cleanPinfl,
            isConsent: 'Y'
        };

        dom.setButtonLoading(searchBtn, true);

        try {
            const result = await personAPI.search(data);

            if (result?.data?.result) {
                this.populateOwnerData(result);

                // Auto-check applicant as owner
                const applicantCheck = document.getElementById('is-applicant-owner');
                if (applicantCheck) {
                    applicantCheck.checked = true;
                    formState.toggleApplicantIsOwner(true);
                }

                dom.showSection('insurance-driver-full-information');
                dom.showSection('applicant-info');
                dom.scrollToElement('insurance-driver-full-information');
            } else {
                dom.showToast('error', 'XALQ SUG\'URTA', 'Person information not found');
            }
        } catch (error) {
            dom.showToast('error', 'XALQ SUG\'URTA', 'Error: ' + error.message);
        } finally {
            dom.setButtonLoading(searchBtn, false);
        }
    }

    populateOwnerData(result) {
        const data = result.data.result;

        const fields = {
            'insurance-last-name': data.lastNameLatin || '',
            'insurance-first-name': data.firstNameLatin || '',
            'insurance-middle-name': data.middleNameLatin || '',
            'owner-address': data.address || ''
        };

        dom.populateFields(fields);

        // Get address from hidden input if API didn't provide it
        const addressInput = document.getElementById('owner-address');
        const address = data.address || (addressInput ? addressInput.value : '') || '';

        const ownerInfo = {
            regionId: data.regionId,
            districtId: data.districtId,
            issuedBy: data.issuedBy,
            issueDate: data.startDate,
            gender: data.gender,
            birthDate: data.birthDate,
            address: address
        };

        formState.setOwner(ownerInfo);

        // Ensure address is also set in hidden input
        if (addressInput) {
            addressInput.value = address;
        }
    }

    renderDisplay() {
        dom.showSection('insurance-driver-full-information');
    }

    handleClear() {
        dom.hideSection('insurance-driver-full-information');
        dom.hideSection('applicant-info');
    }

    enableEdit() {
        if (dom.confirm('Editing owner will clear applicant and driver data. Continue?')) {
            formState.clearOwner();
            dom.scrollToElement('owner-info');
        }
    }
}

console.log('✅ Owner Handler: Creating instance...');
export const ownerHandler = new OwnerHandler();
console.log('✅ Owner Handler: Instance created successfully', ownerHandler);
window.editOwner = () => ownerHandler.enableEdit();
export default ownerHandler;
