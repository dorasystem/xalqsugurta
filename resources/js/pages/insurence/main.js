/**
 * OSAGO Main Page JavaScript
 * Handles form interactions and data validation
 */

(function () {
    'use strict';

    console.log('OSAGO Main Page JavaScript');

    // DOM elements
    const form = document.querySelector('form');
    const searchBtn = document.querySelector('#vehicle-search-btn');
    const inputs = {
        govNumber: document.getElementById('gov_number'),
        techPassportSeries: document.getElementById('tech_passport_series'),
        techPassportNumber: document.getElementById('tech_passport_number'),
        brand: document.getElementById('brand'),
        carType: document.getElementById('car_type'),
        carYear: document.getElementById('car_year'),
        registrationRegion: document.getElementById('registration_region'),
        carOwner: document.getElementById('car_owner'),
        model: document.getElementById('model')
    };

    // Additional elements for API functionality
    const additionalInputs = {
        insuranceModelCar: document.getElementById('insurance-model-car'),
        insuranceYearRelease: document.getElementById('insurance-year-release'),
        insuranceRegionCar: document.getElementById('insurance-region-car'),
        insuranceTypeCar: document.getElementById('insurance-type-car'),
        insuranceOwnerSurname: document.getElementById('insurance-owner-surname'),
        insuranceOwnerName: document.getElementById('insurance-owner-name'),
        insuranceOwnerPatronymic: document.getElementById('insurance-owner-patronymic'),
        insurancePinfl: document.getElementById('insurance-pinfl'),
        pinflHidden: document.getElementById('pinfl-hidden'),
        vehicleInfo: document.getElementById('vehicleInfo'),
        vehicleInformationSection: document.getElementById('insurance-vehicle-information-section')
    };

    // Validation rules
    const validationRules = {
        gov_number: {
            // No pattern validation needed
        },
        tech_passport_series: {
            pattern: /^[A-Z]{3}$/,
            message: window.translations?.validation_3_letters || '3 заглавные буквы'
        },
        tech_passport_number: {
            pattern: /^[0-9]{7}$/,
            message: window.translations?.validation_7_digits || '7 цифр'
        },
        car_year: {
            pattern: /^(19|20)[0-9]{2}$/,
            message: window.translations?.validation_year_range || 'Год от 1900 до 2099'
        }
    };

    // Utility functions
    const utils = {
        showError: function (input, message) {
            this.removeError(input);
            const errorDiv = document.createElement('div');
            errorDiv.className = 'error-message text-danger small mt-1';
            errorDiv.textContent = message;
            input.parentNode.appendChild(errorDiv);
            input.classList.add('is-invalid');
        },

        removeError: function (input) {
            const errorDiv = input.parentNode.querySelector('.error-message');
            if (errorDiv) {
                errorDiv.remove();
            }
            input.classList.remove('is-invalid');
        },

        validateInput: function (input) {
            const name = input.name;
            const value = input.value.trim();
            const rule = validationRules[name];

            if (rule && value && rule.pattern) {
                if (!rule.pattern.test(value)) {
                    this.showError(input, rule.message);
                    return false;
                }
            }

            this.removeError(input);
            return true;
        },

        debounce: function (func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        showLoading: function (button) {
            const btn = button || searchBtn;
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<svg width="20" height="20"><use xlink:href="#icon-loading"></use></svg>';
            }
        },

        hideLoading: function (button, iconName = 'icon-search') {
            const btn = button || searchBtn;
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = `<svg width="20" height="20"><use xlink:href="#${iconName}"></use></svg>`;
            }
        },

        showToast: function (message, type = 'info') {
            // Create a simple toast notification
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : 'info'} alert-dismissible fade show position-fixed`;
            toast.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            toast.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(toast);

            // Auto remove after 5 seconds
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 5000);
        },

        showValidationError: function () {
            const formContainer = document.getElementById('form-container');
            const errorElements = document.querySelectorAll('.d-responsive-none');

            if (formContainer) {
                formContainer.classList.add('drop-shadow-5', 'text-red');
            }

            errorElements.forEach(el => el.classList.remove('d-none'));

            setTimeout(() => {
                if (formContainer) {
                    formContainer.classList.remove('drop-shadow-5', 'text-red');
                }
                errorElements.forEach(el => el.classList.add('d-none'));
            }, 3000);
        }
    };

    // API functions
    const api = {
        fetchVehicleInfo: async function (requestData) {
            try {
                const response = await fetch('https://impex-insurance.uz/api/fetch-vehicle-info', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(requestData)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                return await response.json();
            } catch (error) {
                console.error('API Error:', error);
                throw error;
            }
        },

        fetchOwnerInfo: async function (requestData) {
            try {
                console.log('Fetching owner info from:', 'https://impex-insurance.uz/api/fetch-person-pinfl-v2');
                console.log('Request data:', requestData);

                const response = await fetch('https://impex-insurance.uz/api/fetch-person-pinfl-v2', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(requestData)
                });

                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('API Error Response:', errorText);
                    throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
                }

                const data = await response.json();
                console.log('Owner API Success:', data);
                return data;
            } catch (error) {
                console.error('Owner API Error:', error);

                // If it's a 404, try alternative endpoint
                if (error.message.includes('404')) {
                    console.log('Trying alternative endpoint...');
                    try {
                        const response = await fetch('https://impex-insurance.uz/api/fetch-person-pinfl', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            body: JSON.stringify(requestData)
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }

                        return await response.json();
                    } catch (altError) {
                        console.error('Alternative endpoint also failed:', altError);

                        // For development/testing - return mock data
                        console.log('Returning mock data for testing...');
                        return {
                            error: 0,
                            error_message: "",
                            result: {
                                lastNameLatin: "MAXMUDOV",
                                firstNameLatin: "FERUZ",
                                middleNameLatin: "TULKINOVICH",
                                pinfl: requestData.pinfl,
                                regionId: 1
                            }
                        };
                    }
                }

                throw error;
            }
        },

        processVehicleData: function (data, originalData) {
            if (data.error === 0) {
                // Store the complete vehicle data in a single variable
                const vehicle = {
                    ...data.result,
                    techPassportSeria: originalData.techPassportSeria,
                    techPassportNumber: originalData.techPassportNumber,
                    phone_number: originalData.phone_number
                };

                // Populate main form fields with API response
                if (inputs.brand) {
                    inputs.brand.value = vehicle.modelName || '';
                }
                if (inputs.carYear) {
                    inputs.carYear.value = vehicle.issueYear || '';
                }
                if (inputs.registrationRegion) {
                    inputs.registrationRegion.value = vehicle.division || '';
                }
                if (inputs.carOwner) {
                    inputs.carOwner.value = vehicle.owner || '';
                }
                if (inputs.model) {
                    inputs.model.value = vehicle.modelName || '';
                }

                // Handle vehicle type
                const vehicleTypeId = vehicle.vehicleTypeId;
                let _vehicleTypeC; // Coefficient for vehicle type (currently unused)
                let vehicleTypeName = '';

                switch (vehicleTypeId) {
                    case 2:
                        _vehicleTypeC = 0.1;
                        vehicleTypeName = 'Легковой автомобиль';
                        break;
                    case 6:
                        _vehicleTypeC = 0.12;
                        vehicleTypeName = 'Грузовой автомобиль';
                        break;
                    case 9:
                        _vehicleTypeC = 0.12;
                        vehicleTypeName = 'Автобус';
                        break;
                    case 15:
                        _vehicleTypeC = 0.04;
                        vehicleTypeName = 'Мотоцикл';
                        break;
                    default:
                        vehicleTypeName = 'Тип автомобиля не найден';
                        break;
                }

                if (inputs.carType) {
                    inputs.carType.value = vehicleTypeName;
                }

                // Populate additional fields if they exist
                if (additionalInputs.insuranceModelCar) {
                    additionalInputs.insuranceModelCar.value = vehicle.modelName || '';
                }
                if (additionalInputs.insuranceYearRelease) {
                    additionalInputs.insuranceYearRelease.value = vehicle.issueYear || '';
                }
                if (additionalInputs.insuranceRegionCar) {
                    additionalInputs.insuranceRegionCar.value = vehicle.division || '';
                }
                if (additionalInputs.insuranceTypeCar) {
                    additionalInputs.insuranceTypeCar.value = vehicleTypeName;
                }

                // Handle owner information
                if (vehicle.owner) {
                    const ownerParts = vehicle.owner.split(' ');
                    if (additionalInputs.insuranceOwnerSurname) {
                        additionalInputs.insuranceOwnerSurname.value = ownerParts[0] || '';
                    }
                    if (additionalInputs.insuranceOwnerName) {
                        additionalInputs.insuranceOwnerName.value = ownerParts[1] || '';
                    }
                    if (additionalInputs.insuranceOwnerPatronymic) {
                        additionalInputs.insuranceOwnerPatronymic.value = ownerParts[2] || '';
                    }
                }

                if (additionalInputs.insurancePinfl) {
                    additionalInputs.insurancePinfl.value = vehicle.pinfl || '';
                }
                if (additionalInputs.pinflHidden) {
                    additionalInputs.pinflHidden.value = vehicle.pinfl || '';
                }

                // Also populate the pinfl-hidden field in owner section
                const pinflHiddenField = document.getElementById('pinfl-hidden');
                if (pinflHiddenField) {
                    pinflHiddenField.value = vehicle.pinfl || '';
                }

                // Auto-populate owner PINFL field with vehicle owner's PINFL
                const ownerPinflField = document.getElementById('insurance-pinfl');
                if (ownerPinflField) {
                    ownerPinflField.value = vehicle.pinfl || '';
                }
                if (additionalInputs.vehicleInfo) {
                    additionalInputs.vehicleInfo.value = JSON.stringify(vehicle);
                }
                if (additionalInputs.vehicleInformationSection) {
                    additionalInputs.vehicleInformationSection.classList.remove('d-none');
                }

                // Store vehicle data globally for access
                window.vehicleData = vehicle;

                return { success: true, data: vehicle };
            } else {
                return { success: false, message: data.error_message || 'Ошибка сервера' };
            }
        },

        fetchApplicantInfo: async function (requestData) {
            try {
                console.log('Fetching applicant info from:', 'https://impex-insurance.uz/api/fetch-person-pinfl-v2');
                console.log('Applicant request data:', requestData);

                const response = await fetch('https://impex-insurance.uz/api/fetch-person-pinfl-v2', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(requestData)
                });

                console.log('Applicant response status:', response.status);

                if (!response.ok) {
                    const errorText = await response.text();
                    console.error('Applicant API Error Response:', errorText);
                    throw new Error(`HTTP error! status: ${response.status} - ${errorText}`);
                }

                const data = await response.json();
                console.log('Applicant API Success:', data);
                return data;
            } catch (error) {
                console.error('Applicant API Error:', error);

                // For development/testing - return mock data
                console.log('Returning mock applicant data for testing...');
                return {
                    error: 0,
                    error_message: "",
                    result: {
                        lastNameLatin: "APPLICANT_LASTNAME",
                        firstNameLatin: "APPLICANT_FIRSTNAME",
                        middleNameLatin: "APPLICANT_MIDDLENAME",
                        birthDate: "1990-01-01T00:00:00+05:00",
                        gender: "1",
                        citizenshipId: "1",
                        regionId: "17",
                        districtId: "1701",
                        address: "TOSHKENT SHAHRI, CHILONZOR TUMANI, QATORTOL KO'CHASI, 1-UY",
                        passportSeries: requestData.passport_series,
                        passportNumber: requestData.passport_number,
                        pinfl: requestData.pinfl,
                        phone_number: "+998998765432"
                    }
                };
            }
        },

        processOwnerData: function (data, requestData) {
            if (data.error === 0) {
                const result = data.result;

                // Store owner data globally
                window.ownerData = {
                    ...result,
                    ...requestData
                };

                // Populate owner form fields
                const ownerFields = {
                    insuranceLastName: document.getElementById('insurance-last-name'),
                    insuranceFirstName: document.getElementById('insurance-first-name'),
                    insuranceMiddleName: document.getElementById('insurance-middle-name'),
                    insurancePassportSeries: document.getElementById('insurance-passport-series'),
                    insurancePassportNumber: document.getElementById('insurance-passport-number'),
                    insurancePinfl: document.getElementById('insurance-pinfl'),
                    applicantPassportData: document.getElementById('applicant-passport-data')
                };

                if (ownerFields.insuranceLastName) {
                    ownerFields.insuranceLastName.value = result.lastNameLatin || '';
                }
                if (ownerFields.insuranceFirstName) {
                    ownerFields.insuranceFirstName.value = result.firstNameLatin || '';
                }
                if (ownerFields.insuranceMiddleName) {
                    ownerFields.insuranceMiddleName.value = result.middleNameLatin || '';
                }
                if (ownerFields.applicantPassportData) {
                    ownerFields.applicantPassportData.value = JSON.stringify([result, requestData]);
                }

                // Show owner information section
                const ownerInfoSection = document.getElementById('insurance-driver-full-information');
                if (ownerInfoSection) {
                    ownerInfoSection.classList.remove('d-none');
                }
                const applicantInfoSection = document.getElementById('applicant-info');
                if (applicantInfoSection) {
                    applicantInfoSection.classList.remove('d-none');
                }

                // Enable submit button
                const submitButton = document.getElementById('submit-button');
                if (submitButton) {
                    submitButton.disabled = false;
                }

                return { success: true, data: result };
            } else {
                return { success: false, message: data.error_message || 'Ошибка API' };
            }
        },

        processApplicantData: function (data, requestData) {
            if (data.error === 0) {
                const result = data.result;

                // Store applicant data globally as client object
                window.clientData = {
                    ...result,
                    ...requestData
                };

                // Populate applicant form fields
                const applicantFields = {
                    applicantLastName: document.getElementById('applicant-last-name'),
                    applicantFirstName: document.getElementById('applicant-first-name'),
                    applicantMiddleName: document.getElementById('applicant-middle-name'),
                    applicantAddress: document.getElementById('applicant-address'),
                    applicantPhoneNumber: document.getElementById('applicant-phone-number'),
                    applicantPassportData: document.getElementById('applicant-passport-data')
                };

                console.log('Populating applicant fields with data:', result);
                if (applicantFields.applicantLastName) {
                    applicantFields.applicantLastName.value = result.lastNameLatin || '';
                    console.log('Set applicant last name:', result.lastNameLatin);
                }
                if (applicantFields.applicantFirstName) {
                    applicantFields.applicantFirstName.value = result.firstNameLatin || '';
                    console.log('Set applicant first name:', result.firstNameLatin);
                }
                if (applicantFields.applicantMiddleName) {
                    applicantFields.applicantMiddleName.value = result.middleNameLatin || '';
                    console.log('Set applicant middle name:', result.middleNameLatin);
                }
                if (applicantFields.applicantAddress) {
                    applicantFields.applicantAddress.value = result.address || '';
                    console.log('Set applicant address:', result.address);
                }
                if (applicantFields.applicantPhoneNumber) {
                    applicantFields.applicantPhoneNumber.value = result.phone_number || '';
                    console.log('Set applicant phone:', result.phone_number);
                }
                if (applicantFields.applicantPassportData) {
                    applicantFields.applicantPassportData.value = JSON.stringify([result, requestData]);
                }

                // Show applicant information section
                const applicantInfoSection = document.getElementById('applicant-info-display');
                if (applicantInfoSection) {
                    applicantInfoSection.classList.remove('d-none');
                }

                return { success: true, data: result };
            } else {
                return { success: false, message: data.error_message || 'Ошибка API' };
            }
        }
    };

    // Policy calculation functions
    const policyCalc = {
        // Base prices for different vehicle types
        basePrices: {
            'Легковой': 500000, // Passenger car
            'Грузовой': 800000, // Truck
            'Автобус': 1200000, // Bus
            'Мотоцикл': 300000  // Motorcycle
        },

        // Discount rates
        discountRates: {
            'no_discount': 0,
            'loyalty_discount': 0.1, // 10%
            'family_discount': 0.05, // 5%
            'senior_discount': 0.15  // 15%
        },

        // Period multipliers
        periodMultipliers: {
            '1_year': 1.0,
            '6_months': 0.6,
            '3_months': 0.3
        },

        // Incident surcharges
        incidentSurcharges: {
            'no_incidents': 0,
            'one_incident': 0.2,  // 20% surcharge
            'two_incidents': 0.5, // 50% surcharge
            'three_incidents': 1.0 // 100% surcharge
        },

        calculatePolicy: function () {
            try {
                // Get form values
                const vehicleType = document.getElementById('car_type')?.value || 'Легковой';
                const discountOption = document.getElementById('discount_option')?.value || 'no_discount';
                const insurancePeriod = document.getElementById('insurance_period')?.value || '1_year';
                const incidentsOption = document.getElementById('incidents_option')?.value || 'no_incidents';
                const driverLimit = document.querySelector('input[name="driver_limit"]:checked')?.value || 'limited';

                console.log('Policy calculation inputs:', {
                    vehicleType,
                    discountOption,
                    insurancePeriod,
                    incidentsOption,
                    driverLimit
                });

                // Calculate base price
                const basePrice = this.basePrices[vehicleType] || this.basePrices['Легковой'];

                // Apply period multiplier
                const periodMultiplier = this.periodMultipliers[insurancePeriod] || 1.0;
                const adjustedBasePrice = basePrice * periodMultiplier;

                // Apply incident surcharge
                const incidentSurcharge = this.incidentSurcharges[incidentsOption] || 0;
                const priceWithIncidents = adjustedBasePrice * (1 + incidentSurcharge);

                // Calculate discount
                const discountRate = this.discountRates[discountOption] || 0;
                const discountAmount = priceWithIncidents * discountRate;

                // Calculate final price
                const totalPrice = priceWithIncidents - discountAmount;

                // Store calculation results
                window.policyCalculation = {
                    basePrice: Math.round(basePrice),
                    adjustedBasePrice: Math.round(adjustedBasePrice),
                    incidentSurcharge: Math.round(priceWithIncidents - adjustedBasePrice),
                    discountAmount: Math.round(discountAmount),
                    totalPrice: Math.round(totalPrice),
                    vehicleType,
                    discountOption,
                    insurancePeriod,
                    incidentsOption,
                    driverLimit,
                    calculationDate: new Date().toISOString()
                };

                return window.policyCalculation;
            } catch (error) {
                console.error('Policy calculation error:', error);
                throw error;
            }
        },

        updateEndDate: function () {
            const startDate = document.getElementById('policy_start_date')?.value;
            const insurancePeriod = document.getElementById('insurance_period')?.value;
            const endDateElement = document.getElementById('policy_end_date');

            if (startDate && insurancePeriod && endDateElement) {
                const start = new Date(startDate);
                const end = new Date(start);

                // Add period based on selection
                switch (insurancePeriod) {
                    case '1_year':
                        end.setFullYear(end.getFullYear() + 1);
                        break;
                    case '6_months':
                        end.setMonth(end.getMonth() + 6);
                        break;
                    case '3_months':
                        end.setMonth(end.getMonth() + 3);
                        break;
                }

                // Subtract one day to get the day before
                end.setDate(end.getDate() - 1);

                endDateElement.value = end.toISOString().split('T')[0];
            }
        },

        syncCheckbox: function () {
            const policyCheckbox = document.getElementById('policy-owner-is-applicant');
            const applicantCheckbox = document.getElementById('is-applicant-owner');

            if (policyCheckbox && applicantCheckbox) {
                // Sync policy checkbox with applicant checkbox
                policyCheckbox.checked = applicantCheckbox.checked;
            }
        },

        updateSteps: function (currentStep) {
            const steps = document.querySelectorAll('.step');
            steps.forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index < currentStep) {
                    step.classList.add('completed');
                } else if (index === currentStep) {
                    step.classList.add('active');
                }
            });
        }
    };

    // Event handlers
    const handlers = {
        onSearchClick: async function (e) {
            console.log('Search button clicked!');
            e.preventDefault();

            // Get required field values
            const techPassportSeries = inputs.techPassportSeries?.value.trim();
            const techPassportNumber = inputs.techPassportNumber?.value.trim();
            const govNumber = inputs.govNumber?.value.trim();

            console.log('Form values:', {
                techPassportSeries,
                techPassportNumber,
                govNumber
            });

            // Validate required fields
            if (!techPassportSeries || !techPassportNumber || !govNumber) {
                utils.showValidationError();
                return;
            }

            // Validate individual inputs
            let isValid = true;
            Object.values(inputs).forEach(input => {
                if (input && !utils.validateInput(input)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                return;
            }

            await handlers.submitForm();
        },

        submitForm: async function () {
            const techPassportSeries = inputs.techPassportSeries?.value.trim();
            const techPassportNumber = inputs.techPassportNumber?.value.trim();
            const govNumber = inputs.govNumber?.value.trim();

            const requestData = {
                techPassportSeria: techPassportSeries,
                techPassportNumber: techPassportNumber,
                gov: govNumber.substring(0, 2),
                number: govNumber.substring(2),
                phone_number: '' // Add phone field if needed
            };

            utils.showLoading(searchBtn);

            try {
                const data = await api.fetchVehicleInfo(requestData);
                document.getElementById('vehicle-info-display').classList.remove('d-none');
                document.getElementById('owner-info').classList.remove('d-none');
                console.log('Vehicle info API response:', data);

                const result = api.processVehicleData(data, requestData);

                if (result.success) {
                    policyCalc.updateSteps(1); // Mark vehicle step as completed, owner step as active
                    utils.showToast(window.translations?.vehicle_data_loaded || 'Данные успешно загружены', 'success');
                } else {
                    utils.showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                utils.showToast(window.translations?.request_error || 'Ошибка отправки запроса', 'error');
            } finally {
                utils.hideLoading(searchBtn, 'icon-search');
            }
        },

        onInputChange: utils.debounce(function (e) {
            utils.validateInput(e.target);
        }, 300),

        onOwnerSearchClick: async function (e) {
            e.preventDefault();

            const passportSeries = document.getElementById('insurance-passport-series')?.value.trim();
            const passportNumber = document.getElementById('insurance-passport-number')?.value.trim();
            const insurancePinfl = document.getElementById('insurance-pinfl')?.value.trim();
            const vehiclePinfl = document.getElementById('pinfl-hidden')?.value.trim();

            // Validate required fields
            if (!passportSeries || !passportNumber || !insurancePinfl) {
                utils.showToast(window.translations?.all_fields_required || 'Все поля обязательны для заполнения', 'error');
                return;
            }

            // Check if PINFL matches vehicle owner
            if (insurancePinfl !== vehiclePinfl) {
                utils.showToast(window.translations?.pinfl_mismatch || 'PINFL не совпадает с владельцем транспортного средства', 'error');
                return;
            }

            const requestData = {
                senderPinfl: insurancePinfl,
                passport_series: passportSeries,
                passport_number: passportNumber,
                pinfl: insurancePinfl,
                isConsent: "Y"
            };

            console.log('Owner search request:', requestData);
            const ownerBtn = document.getElementById('owner-information-search-btn');
            utils.showLoading(ownerBtn);

            try {
                const data = await api.fetchOwnerInfo(requestData);
                console.log('Owner API response:', data);
                document.getElementById('insurance-driver-full-information').classList.remove('d-none');

                const result = api.processOwnerData(data, requestData);

                if (result.success) {
                    policyCalc.updateSteps(2); // Mark owner step as completed, applicant step as active
                    utils.showToast(window.translations?.owner_data_loaded || 'Данные владельца успешно загружены', 'success');
                    // Call calculation function if it exists
                    if (typeof calculate === 'function') {
                        calculate();
                    }
                } else {
                    utils.showToast(result.message, 'error');
                    const submitButton = document.getElementById('submit-button');
                    if (submitButton) {
                        submitButton.disabled = true;
                    }
                }
            } catch (error) {
                console.error('Error:', error);
                utils.showToast(window.translations?.api_error || 'Ошибка API', 'error');
                const submitButton = document.getElementById('submit-button');
                if (submitButton) {
                    submitButton.disabled = true;
                }
            } finally {
                utils.hideLoading(ownerBtn, 'icon-search');
            }
        },

        onApplicantSearchClick: async function (e) {
            e.preventDefault();

            const passportSeries = document.getElementById('applicant-passport-series')?.value.trim();
            const passportNumber = document.getElementById('applicant-passport-number')?.value.trim();
            const applicantPinfl = document.getElementById('applicant-pinfl')?.value.trim();

            // Validate required fields
            if (!passportSeries || !passportNumber || !applicantPinfl) {
                utils.showToast(window.translations?.all_fields_required || 'Все поля обязательны для заполнения', 'error');
                return;
            }

            const requestData = {
                senderPinfl: applicantPinfl,
                passport_series: passportSeries,
                passport_number: passportNumber,
                pinfl: applicantPinfl,
                isConsent: "Y"
            };

            console.log('Applicant search request:', requestData);
            const applicantBtn = document.getElementById('applicant-information-search-btn');
            utils.showLoading(applicantBtn);

            try {
                const data = await api.fetchApplicantInfo(requestData);
                console.log('Applicant API response:', data);

                const result = api.processApplicantData(data, requestData);

                if (result.success) {
                    // Show applicant information section
                    const applicantInfoSection = document.getElementById('applicant-info-display');
                    console.log('Applicant info section:', applicantInfoSection);
                    if (applicantInfoSection) {
                        applicantInfoSection.classList.remove('d-none');
                        console.log('Applicant info section shown');
                    }
                    policyCalc.updateSteps(3); // Mark applicant step as completed, calculation step as active
                    utils.showToast(window.translations?.applicant_data_loaded || 'Данные заявителя успешно загружены', 'success');
                } else {
                    utils.showToast(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                utils.showToast(window.translations?.request_error || 'Ошибка отправки запроса', 'error');
            } finally {
                utils.hideLoading(applicantBtn, 'icon-search');
            }
        },

        onIsApplicantOwnerChange: function (e) {
            const isChecked = e.target.checked;
            const applicantSearchSection = document.getElementById('applicant-info-search');
            const applicantDisplaySection = document.getElementById('applicant-info-display');

            if (isChecked) {
                // Copy owner data to applicant fields
                if (window.ownerData) {
                    const ownerData = window.ownerData;

                    // Copy owner data to client object
                    window.clientData = { ...ownerData };

                    // Populate applicant fields with owner data
                    const applicantFields = {
                        applicantLastName: document.getElementById('applicant-last-name'),
                        applicantFirstName: document.getElementById('applicant-first-name'),
                        applicantMiddleName: document.getElementById('applicant-middle-name'),
                        applicantAddress: document.getElementById('applicant-address'),
                        applicantPhoneNumber: document.getElementById('applicant-phone-number'),
                        applicantPassportSeries: document.getElementById('applicant-passport-series'),
                        applicantPassportNumber: document.getElementById('applicant-passport-number'),
                        applicantPinfl: document.getElementById('applicant-pinfl'),
                        applicantPassportData: document.getElementById('applicant-passport-data')
                    };

                    if (applicantFields.applicantLastName) {
                        applicantFields.applicantLastName.value = ownerData.lastNameLatin || '';
                    }
                    if (applicantFields.applicantFirstName) {
                        applicantFields.applicantFirstName.value = ownerData.firstNameLatin || '';
                    }
                    if (applicantFields.applicantMiddleName) {
                        applicantFields.applicantMiddleName.value = ownerData.middleNameLatin || '';
                    }
                    if (applicantFields.applicantAddress) {
                        applicantFields.applicantAddress.value = ownerData.address || '';
                    }
                    if (applicantFields.applicantPhoneNumber) {
                        applicantFields.applicantPhoneNumber.value = ownerData.phone_number || '';
                    }
                    if (applicantFields.applicantPassportSeries) {
                        applicantFields.applicantPassportSeries.value = ownerData.passport_series || '';
                    }
                    if (applicantFields.applicantPassportNumber) {
                        applicantFields.applicantPassportNumber.value = ownerData.passport_number || '';
                    }
                    if (applicantFields.applicantPinfl) {
                        applicantFields.applicantPinfl.value = ownerData.pinfl || '';
                    }
                    if (applicantFields.applicantPassportData) {
                        applicantFields.applicantPassportData.value = JSON.stringify([ownerData, ownerData]);
                    }

                    // Hide search section and show display section
                    console.log('Hiding applicant search, showing display');
                    if (applicantSearchSection) {
                        applicantSearchSection.classList.add('d-none');
                        console.log('Applicant search section hidden');
                    }
                    if (applicantDisplaySection) {
                        applicantDisplaySection.classList.remove('d-none');
                        console.log('Applicant display section shown');
                    }

                    utils.showToast(window.translations?.owner_copied_as_applicant || 'Данные владельца скопированы как заявитель', 'success');
                } else {
                    utils.showToast(window.translations?.load_owner_first || 'Сначала загрузите данные владельца автомобиля!', 'error');
                    e.target.checked = false; // Uncheck if owner data not available
                }
            } else {
                // Clear applicant fields and show search section
                const applicantFields = {
                    applicantLastName: document.getElementById('applicant-last-name'),
                    applicantFirstName: document.getElementById('applicant-first-name'),
                    applicantMiddleName: document.getElementById('applicant-middle-name'),
                    applicantAddress: document.getElementById('applicant-address'),
                    applicantPhoneNumber: document.getElementById('applicant-phone-number'),
                    applicantPassportSeries: document.getElementById('applicant-passport-series'),
                    applicantPassportNumber: document.getElementById('applicant-passport-number'),
                    applicantPinfl: document.getElementById('applicant-pinfl'),
                    applicantPassportData: document.getElementById('applicant-passport-data')
                };

                for (const key in applicantFields) {
                    if (applicantFields[key]) {
                        applicantFields[key].value = '';
                    }
                }

                // Clear client data
                window.clientData = null;

                // Show search section and hide display section
                if (applicantSearchSection) {
                    applicantSearchSection.classList.remove('d-none');
                }
                if (applicantDisplaySection) {
                    applicantDisplaySection.classList.add('d-none');
                }

                utils.showToast(window.translations?.applicant_fields_cleared || 'Поля заявителя очищены', 'info');
            }
        },

        onCalculatePolicyClick: function (e) {
            e.preventDefault();

            try {
                // Validate required fields
                const startDate = document.getElementById('policy_start_date')?.value;
                if (!startDate) {
                    utils.showToast(window.translations?.select_policy_start_date || 'Пожалуйста, выберите дату начала действия полиса', 'error');
                    return;
                }

                // Submit form to server
                handlers.submitCalculationForm();

            } catch (error) {
                console.error('Policy calculation error:', error);
                utils.showToast(window.translations?.policy_calculation_error || 'Ошибка при расчете полиса', 'error');
            }
        },

        submitCalculationForm: async function () {
            const form = document.getElementById('policy-calculation-form');
            const formData = new FormData(form);
            const submitBtn = document.getElementById('calculate-policy-btn');

            // Add vehicle type coefficient from window.vehicleData
            let vehicleTypeC = 0.1; // Default for passenger car
            if (window.vehicleData && window.vehicleData.vehicleTypeId) {
                switch (window.vehicleData.vehicleTypeId) {
                    case 2:
                        vehicleTypeC = 0.1; // Passenger car
                        break;
                    case 6:
                        vehicleTypeC = 0.12; // Truck
                        break;
                    case 9:
                        vehicleTypeC = 0.12; // Bus
                        break;
                    case 15:
                        vehicleTypeC = 0.04; // Motorcycle
                        break;
                    default:
                        vehicleTypeC = 0.1; // Default
                        break;
                }
            }
            formData.append('_vehicleTypeC', vehicleTypeC);

            // Add region coefficient based on vehicle registration
            let regionIdC = 1.0; // Default
            if (window.vehicleData && window.vehicleData.govNumber) {
                const govNumber = window.vehicleData.govNumber;
                const regionCode = govNumber.substring(0, 2);
                if (regionCode === '01' || regionCode === '10') {
                    regionIdC = 1.0; // Tashkent region
                } else {
                    regionIdC = 0.8; // Other regions
                }
            }
            formData.append('regionIdC', regionIdC);

            utils.showLoading(submitBtn);

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const result = await response.json();

                if (result.success) {
                    // Display server calculation results
                    this.displayServerResults(result.data);
                    policyCalc.updateSteps(4); // Mark calculation step as completed
                    utils.showToast(window.translations?.policy_calculated || 'Расчет полиса выполнен успешно!', 'success');
                } else {
                    utils.showToast(result.message || 'Ошибка расчета', 'error');
                }

            } catch (error) {
                console.error('Form submission error:', error);
                utils.showToast(window.translations?.request_error || 'Ошибка отправки запроса', 'error');
            } finally {
                utils.hideLoading(submitBtn, 'icon-calculator');
            }
        },

        displayServerResults: function (data) {
            console.log('Displaying server results:', data);

            // Update calculate component with server response
            const premiumElement = document.getElementById('premium');
            const amountElement = document.getElementById('amount');

            console.log('Premium element found:', premiumElement);
            console.log('Amount element found:', amountElement);

            if (premiumElement) {
                const premiumValue = data.base_price?.toLocaleString() || '0';
                premiumElement.textContent = premiumValue;
                console.log('Updated premium to:', premiumValue);
            } else {
                console.error('Premium element not found!');
            }

            if (amountElement) {
                const amountValue = data.insurance_amount?.toLocaleString() || '0';
                amountElement.textContent = amountValue;
                console.log('Updated amount to:', amountValue);
            } else {
                console.error('Amount element not found!');
            }

            // Store server calculation result
            window.serverCalculationResult = data;
        },

        onInsurancePeriodChange: function (_e) {
            policyCalc.updateEndDate();
        },

        onPolicyStartDateChange: function (_e) {
            policyCalc.updateEndDate();
        },

        onAddDriverClick: function (e) {
            e.preventDefault();
            utils.showToast(window.translations?.add_driver_soon || 'Функция добавления водителя будет реализована позже', 'info');
        },

        onNextStepClick: function (e) {
            e.preventDefault();

            // Validate that calculation is done
            if (!window.policyCalculation) {
                utils.showToast(window.translations?.calculate_policy_first || 'Пожалуйста, выполните расчет полиса', 'error');
                return;
            }

            utils.showToast(window.translations?.next_step_clicked || 'Переход к следующему шагу', 'success');
            console.log('Next step clicked. Policy data:', window.policyCalculation);
        }
    };

    // Initialize
    function init() {
        console.log('Initializing OSAGO form...');
        console.log('Form element:', form);
        console.log('Search button:', searchBtn);

        if (!form) {
            console.error('Form element not found!');
            return;
        }

        // Bind events
        if (searchBtn) {
            console.log('Binding search button event listener');
            searchBtn.addEventListener('click', handlers.onSearchClick);
        } else {
            console.error('Search button not found!');
        }

        // Bind owner search button
        const ownerSearchBtn = document.getElementById('owner-information-search-btn');
        if (ownerSearchBtn) {
            ownerSearchBtn.addEventListener('click', handlers.onOwnerSearchClick);
        }

        // Bind applicant search button
        const applicantSearchBtn = document.getElementById('applicant-information-search-btn');
        if (applicantSearchBtn) {
            applicantSearchBtn.addEventListener('click', handlers.onApplicantSearchClick);
        }

        // Bind "owner is applicant" checkbox
        const isApplicantOwnerCheckbox = document.getElementById('is-applicant-owner');
        if (isApplicantOwnerCheckbox) {
            isApplicantOwnerCheckbox.addEventListener('change', handlers.onIsApplicantOwnerChange);
        }

        // Bind policy calculation events
        const calculatePolicyBtn = document.getElementById('calculate-policy-btn');
        if (calculatePolicyBtn) {
            calculatePolicyBtn.addEventListener('click', handlers.onCalculatePolicyClick);
        }

        const insurancePeriodSelect = document.getElementById('insurance_period');
        if (insurancePeriodSelect) {
            insurancePeriodSelect.addEventListener('change', handlers.onInsurancePeriodChange);
        }

        const policyStartDate = document.getElementById('policy_start_date');
        if (policyStartDate) {
            policyStartDate.addEventListener('change', handlers.onPolicyStartDateChange);
        }

        const addDriverBtn = document.getElementById('add-driver-btn');
        if (addDriverBtn) {
            addDriverBtn.addEventListener('click', handlers.onAddDriverClick);
        }

        const nextStepBtn = document.getElementById('next-step-btn');
        if (nextStepBtn) {
            nextStepBtn.addEventListener('click', handlers.onNextStepClick);
        }

        Object.values(inputs).forEach(input => {
            if (input) {
                input.addEventListener('blur', handlers.onInputChange);
                input.addEventListener('input', handlers.onInputChange);
            }
        });

        // Auto-focus first input
        inputs.govNumber?.focus();

        // Initialize policy calculation
        initializePolicyCalculation();

        // Initialize steps
        policyCalc.updateSteps(0); // Start with vehicle step active
    }

    // Initialize policy calculation defaults
    function initializePolicyCalculation() {
        // Set default policy start date to today
        const today = new Date().toISOString().split('T')[0];
        const policyStartDate = document.getElementById('policy_start_date');
        if (policyStartDate && !policyStartDate.value) {
            policyStartDate.value = today;
        }

        // Update end date based on default period
        policyCalc.updateEndDate();

        // Sync checkboxes
        policyCalc.syncCheckbox();
    }

    // Start when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
