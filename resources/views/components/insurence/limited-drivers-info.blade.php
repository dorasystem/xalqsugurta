<div id="limited-drivers-info" class="card {{ old('driver_limit') == 'limited' ? '' : 'd-none' }}">
    <div class="card-header">
        <h4 class="card-title">{{ __('insurance.driver_info') }}</h4>
    </div>
    <div class="card-body">
        <div id="driver-info-search">
            <div class="row">
                <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'driver-passport-series'" :name="'driver_passport_series'"
                    :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'driver-passport-number'" :name="'driver_passport_number'"
                    :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'driver-pinfl'" :name="'driver_pinfl'"
                    :placeholder="'messages.owner_pinfl_placeholder'" :label="'messages.owner_pinfl'" />

                <x-inputs.button :class="'col-md-3'" :button="'driver-information-search-btn'" />
            </div>
        </div>
    </div>

    <div id="driver-info-display"></div>
</div>
