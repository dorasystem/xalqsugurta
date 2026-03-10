/**
 * Multi-step form handler (Vanilla JS)
 * - Validates required fields per step before allowing Next
 * - Persists data (all inputs in one form)
 * - Updates progress bar
 * - Supports server validation (old values, errors)
 */
(function() {
    'use strict';

    window.MultiStepForm = function(formSelector, options) {
        const form = typeof formSelector === 'string' ? document.querySelector(formSelector) : formSelector;
        if (!form) return;

        const config = Object.assign({
            stepSelector: '[data-step]',
            nextBtnSelector: '[data-step-next]',
            prevBtnSelector: '[data-step-prev]',
            submitBtnSelector: '[data-step-submit]',
            progressBarSelector: '.multi-step-stepper .progress-bar',
            requiredInStep: {}
        }, options || {});

        let currentStep = 1;
        const stepPanels = Array.from(form.querySelectorAll(config.stepSelector));
        const totalSteps = stepPanels.length;

        function showStep(step) {
            step = Math.max(1, Math.min(step, totalSteps));
            currentStep = step;

            stepPanels.forEach(function(panel, index) {
                const stepNum = index + 1;
                panel.classList.toggle('d-none', stepNum !== step);
                panel.setAttribute('data-step-active', stepNum === step ? '1' : '0');
            });

            updateProgressBar();
            updateButtons();
        }

        function updateProgressBar() {
            const scope = form.closest('.container') || form.parentElement || document;
            const bar = scope.querySelector ? scope.querySelector(config.progressBarSelector) : document.querySelector(config.progressBarSelector);
            if (bar) {
                bar.style.width = ((currentStep / totalSteps) * 100) + '%';
                bar.setAttribute('aria-valuenow', currentStep);
            }

            const stepper = (scope.querySelector && scope.querySelector('.multi-step-stepper')) || document.querySelector('.multi-step-stepper');
            if (stepper) {
                stepper.querySelectorAll('.step-label').forEach(function(el, i) {
                    el.classList.toggle('text-primary', i + 1 <= currentStep);
                    el.classList.toggle('fw-semibold', i + 1 <= currentStep);
                });
            }
        }

        function updateButtons() {
            const prevBtns = form.querySelectorAll(config.prevBtnSelector);
            const nextBtns = form.querySelectorAll(config.nextBtnSelector);
            const submitBtns = form.querySelectorAll(config.submitBtnSelector);

            prevBtns.forEach(function(btn) {
                btn.style.display = currentStep <= 1 ? 'none' : '';
                btn.disabled = currentStep <= 1;
            });
            nextBtns.forEach(function(btn) {
                btn.style.display = currentStep >= totalSteps ? 'none' : '';
            });
            submitBtns.forEach(function(btn) {
                btn.style.display = currentStep >= totalSteps ? '' : 'none';
            });
        }

        function getRequiredFields(step) {
            const panel = stepPanels[step - 1];
            if (!panel) return [];
            const custom = config.requiredInStep[step];
            if (Array.isArray(custom)) return custom;
            return panel.querySelectorAll('[required]');
        }

        function validateStep(step) {
            const panel = stepPanels[step - 1];
            if (!panel) return true;

            const required = panel.querySelectorAll('[required]');
            let valid = true;

            required.forEach(function(field) {
                const isEmpty = !field.value || (typeof field.value === 'string' && !field.value.trim());
                const isHidden = field.closest('.d-none');
                if (isEmpty && !isHidden) {
                    valid = false;
                    field.classList.add('is-invalid');
                    if (!field.hasAttribute('aria-describedby')) {
                        const id = 'err-' + (field.id || field.name || Math.random().toString(36).slice(2));
                        field.setAttribute('aria-describedby', id);
                        const err = document.createElement('div');
                        err.id = id;
                        err.className = 'invalid-feedback d-block';
                        err.textContent = (field.dataset.requiredMsg || 'This field is required');
                        field.parentNode.appendChild(err);
                    }
                } else {
                    field.classList.remove('is-invalid');
                    const errId = field.getAttribute('aria-describedby');
                    if (errId) {
                        const err = document.getElementById(errId);
                        if (err && !err.classList.contains('invalid-feedback')) err.remove();
                    }
                }
            });

            if (!valid && panel.offsetParent) {
                const firstInvalid = panel.querySelector('.is-invalid');
                if (firstInvalid) {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus();
                }
            }

            return valid;
        }

        function goNext() {
            if (!validateStep(currentStep)) return;
            showStep(currentStep + 1);
        }

        function goPrev() {
            showStep(currentStep - 1);
        }

        function handleSubmit(e) {
            if (currentStep < totalSteps) {
                e.preventDefault();
                goNext();
                return;
            }
            const submitBtn = form.querySelector(config.submitBtnSelector);
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>' + (submitBtn.dataset.loadingText || 'Loading...');
            }
        }

        form.querySelectorAll(config.nextBtnSelector).forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                goNext();
            });
        });

        form.querySelectorAll(config.prevBtnSelector).forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                goPrev();
            });
        });

        form.addEventListener('submit', handleSubmit);

        const firstErrorPanel = form.querySelector('[data-step] .is-invalid');
        if (firstErrorPanel) {
            const panel = firstErrorPanel.closest('[data-step]');
            if (panel) {
                const step = parseInt(panel.dataset.step, 10);
                if (!isNaN(step)) showStep(step);
            }
        } else {
            showStep(1);
        }

        return {
            goToStep: showStep,
            getCurrentStep: function() { return currentStep; },
            validateStep: validateStep
        };
    };
})();
