/**
 * Applicant Handler Module
 */

import { personAPI } from '../api.js';
import formState from '../state.js';
import * as dom from '../dom-utils.js';
import * as validators from '../validators.js';

export class ApplicantHandler {
    constructor() {
        this.elements = {
            checkbox: document.getElementById('is-applicant-owner'),
            passportSeries: document.getElementById('applicant-passport-series'),
            passportNumber: document.getElementById('applicant-passport-number'),
            pinfl: document.getElementById('applicant-pinfl'),
            searchBtn: document.getElementById('applicant-information-search-btn')
        };

        this.init();
    }

    init() {
        if (this.elements.checkbox) {
            this.elements.checkbox.addEventListener('change', () => this.handleCheckboxChange());
        }

        if (this.elements.searchBtn) {
            this.elements.searchBtn.addEventListener('click', () => this.handleSearch());
        }

        formState.on('applicant-updated', () => this.renderDisplay());
        formState.on('applicant-cleared', () => this.handleClear());
    }

    handleCheckboxChange() {
        const isChecked = this.elements.checkbox.checked;

        if (!isChecked) {
            const applicantData = document.getElementById('applicant-infos')?.value;
            if (applicantData && applicantData.trim() !== '') {
                if (!dom.confirm('This will clear applicant data. Continue?')) {
                    this.elements.checkbox.checked = true;
                    return;
                }
            }
        }

        formState.toggleApplicantIsOwner(isChecked);

        if (isChecked) {
            this.copyOwnerToApplicant();
            dom.showSection('applicant-info-display');
            dom.showSection('policy-calculation');
            dom.showSection('confirmation');
        } else {
            this.clearApplicantFields();
            dom.showSection('applicant-info-search');
            dom.hideSection('applicant-info-display');
            dom.hideSection('policy-calculation');
            dom.hideSection('confirmation');
        }
    }

    copyOwnerToApplicant() {
        const ownerFields = [
            'last-name', 'first-name', 'middle-name', 'address',
            'pinfl', 'passport-series', 'passport-number'
        ];

        ownerFields.forEach(field => {
            const ownerEl = document.getElementById(`insurance-${field}`);
            const applicantEl = document.getElementById(`applicant-${field}`);

            if (ownerEl && applicantEl) {
                applicantEl.value = ownerEl.value || '';
            }
        });

        // Handle special cases
        const ownerAddress = document.getElementById('owner-address');
        const applicantAddress = document.getElementById('applicant-address');
        if (ownerAddress && applicantAddress) {
            applicantAddress.value = ownerAddress.value || '';
        }
    }

    clearApplicantFields() {
        ['applicant-passport-series', 'applicant-passport-number', 'applicant-pinfl'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });
    }

    async handleSearch() {
        const { passportSeries, passportNumber, pinfl, searchBtn } = this.elements;

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
                this.populateApplicantData(result);
                dom.showSection('applicant-info-display');
                dom.showSection('policy-calculation');
                dom.showSection('confirmation');
            } else {
                dom.showToast('error', 'XALQ SUG\'URTA', 'Person information not found');
            }
        } catch (error) {
            dom.showToast('error', 'XALQ SUG\'URTA', 'Error: ' + error.message);
        } finally {
            dom.setButtonLoading(searchBtn, false);
        }
    }

    populateApplicantData(result) {
        const data = result.data.result;

        const fields = {
            'applicant-last-name': data.lastNameLatin || '',
            'applicant-first-name': data.firstNameLatin || '',
            'applicant-middle-name': data.middleNameLatin || '',
            'applicant-address': data.address || ''
        };

        dom.populateFields(fields);

        const applicantInfo = {
            regionId: data.regionId,
            districtId: data.districtId,
            issuedBy: data.issuedBy,
            issueDate: data.startDate,
            gender: data.gender,
            birthDate: data.birthDate,
            address: data.address || ''
        };

        formState.setApplicant(applicantInfo);
    }

    renderDisplay() {
        dom.showSection('applicant-info-display');
    }

    handleClear() {
        dom.hideSection('applicant-info-display');
        dom.hideSection('policy-calculation');
        dom.hideSection('confirmation');
    }

    enableEdit() {
        if (dom.confirm('Editing applicant will clear driver data. Continue?')) {
            formState.clearApplicant();
            this.elements.checkbox.checked = false;
            dom.scrollToElement('applicant-info');
        }
    }
}

export const applicantHandler = new ApplicantHandler();
window.editApplicant = () => applicantHandler.enableEdit();
export default applicantHandler;
