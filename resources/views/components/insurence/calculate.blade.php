<div class="col-md-4">
    <div class="card summary-panel">
        <div class="card-header">
            <h5>{{ __('messages.calculation_results') }}</h5>
        </div>
        <div class="card-body p-3">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-label fs-6 text-body-secondary">{{ __('messages.insurance_premium') }}:</div>
                    <div class="summary-total-value fs-4 fw-bold">
                        <span id="premium" class="summary-total-value-number">0</span> {{ __('messages.sum') }}
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <div class="form-label fs-6 text-body-secondary">{{ __('messages.insurance_sum') }}:</div>
                    <div class="summary-total-value fs-4 fw-bold">
                        <span id="amount" class="summary-total-value-number">0</span> {{ __('messages.sum') }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
