<div id="vehicle-info" class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('messages.vehicle_info_title') }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <x-inputs.input_form :type="'text'" :class="'col-md-3'" :idFor="'gov_number'" :name="'gov_number'"
                :placeholder="'messages.gov_number_placeholder'" :label="'messages.gov_number'" />

            <x-inputs.input_form :type="'text'" :class="'col-md-3'" :idFor="'tech_passport_series'" :name="'tech_passport_series'"
                :placeholder="'messages.tech_passport_series_placeholder'" :label="'messages.tech_passport_series'" />

            <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'tech_passport_number'" :name="'tech_passport_number'"
                :placeholder="'messages.tech_passport_number_placeholder'" :label="'messages.tech_passport_number'" />

            <x-inputs.button :class="'col-md-3'" :button="'vehicle-search-btn'" />
        </div>
    </div>

    <div id="vehicle-info-display" class="card-footer {{ old('model') ? '' : 'd-none' }}">
        <div class="row">
            <x-inputs.input_info :class="'col-md-4'" :idFor="'model'" :name="'model'" :label="'messages.model'"
                :placeholder="'messages.model_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'car_type'" :name="'car_type'" :label="'messages.car_type'"
                :placeholder="'messages.car_type_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'car_year'" :name="'car_year'" :label="'messages.car_year'"
                :placeholder="'messages.car_year_placeholder'" />
        </div>

        <div class="row">
            <x-inputs.input_info :class="'col-md-4'" :idFor="'registration_region'" :name="'registration_region'" :label="'messages.registration_region'"
                :placeholder="'messages.registration_region_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'car_owner'" :name="'car_owner'" :label="'messages.car_owner'"
                :placeholder="'messages.car_owner_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'engine_number'" :name="'engine_number'" :label="'messages.engine_number'"
                :placeholder="'messages.engine_number_placeholder'" />

            <input type="hidden" name="other_info" id="other_info" value="{{ old('other_info') }}">
        </div>
    </div>
</div>
