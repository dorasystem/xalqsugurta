<div id="policy-calculation" class="card d-none">
    <div class="card-header">
        <h4>{{ __('messages.policy_calculation') }}</h4>
    </div>
    <div class="card-body">
        <div class="row mb-3">
            <div class="col-md-6">
                <label for="policy_start_date" class="form-label">{{ __('messages.policy_start_date') }}</label>
                <input type="date" class="form-control @error('policy_start_date') is-invalid @enderror"
                    id="policy_start_date" name="policy_start_date" value="{{ old('policy_start_date', \Carbon\Carbon::today()->format('Y-m-d')) }}" required>
                @error('policy_start_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-6">
                <label for="policy_end_date" class="form-label">{{ __('messages.policy_end_date') }}</label>
                <input type="date" class="form-control @error('policy_end_date') is-invalid @enderror"
                    id="policy_end_date" name="policy_end_date" value="{{ old('policy_end_date') }}" readonly>
                @error('policy_end_date')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <label for="insurance_period" class="form-label">{{ __('messages.insurance_period') }}</label>
                <select class="form-select @error('insurance_period') is-invalid @enderror" id="insurance_period"
                    name="insurance_period" required>
                    <option value="1" {{ old('insurance_period') == '1' ? 'selected' : 'selected' }}>
                        {{ __('messages.1_year') }}</option>
                    {{-- <option value="0.7" {{ old('insurance_period') == '0.7' ? 'selected' : '' }}>
                        {{ __('messages.6_months') }}</option> --}}
                    {{-- <option value="0.4" {{ old('insurance_period') == '0.4' ? 'selected' : '' }}>
                        {{ __('messages.3_months') }}</option> --}}
                </select>
                @error('insurance_period')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <input type="hidden" id="insurance-infos" name="insurance_infos" value="{{ old('insurance_infos') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">{{ __('messages.driver_limit') }}</label>
                <div class="form-check">
                    <input class="form-check-input @error('driver_limit') is-invalid @enderror" type="radio"
                        name="driver_limit" id="driver_unlimited" value="unlimited"
                        {{ old('driver_limit', 'unlimited') == 'unlimited' ? 'checked' : '' }}>
                    <label class="form-check-label" for="driver_unlimited">
                        {{ __('messages.unlimited_drivers') }}
                    </label>
                </div>
                <div class="form-check">
                    <input class="form-check-input @error('driver_limit') is-invalid @enderror" type="radio"
                        name="driver_limit" id="driver_limited" value="limited"
                        {{ old('driver_limit') == 'limited' ? 'checked' : '' }}>
                    <label class="form-check-label" for="driver_limited">
                        {{ __('messages.limited_drivers') }}
                    </label>
                </div>
                @error('driver_limit')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>
</div>
