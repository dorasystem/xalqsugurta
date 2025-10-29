<div id="owner-info" class="card {{ old('model') ? '' : 'd-none' }}">
    <div class="card-header">
        <h4 class="card-title">{{ __('messages.owner_info_title') }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'insurance-passport-series'" :name="'owner[passportSeries]'"
                :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

            <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'insurance-passport-number'" :name="'owner[passportNumber]'"
                :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

            <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'insurance-pinfl'" :name="'owner[pinfl]'"
                :placeholder="'messages.owner_pinfl_placeholder'" :label="'messages.owner_pinfl'" :readonly="true" />

            <x-inputs.button :class="'col-md-3'" :button="'owner-information-search-btn'" />
        </div>
    </div>

    <div id="insurance-driver-full-information" class="card-footer {{ old('owner.lastName') ? '' : 'd-none' }}">
        <div class="row">
            <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-last-name'" :name="'owner[lastName]'" :label="'messages.last_name'"
                :placeholder="'messages.last_name_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-first-name'" :name="'owner[firstName]'" :label="'messages.first_name'"
                :placeholder="'messages.first_name_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'insurance-middle-name'" :name="'owner[middleName]'" :label="'messages.middle_name'"
                :placeholder="'messages.middle_name_placeholder'" />

            <input type="hidden" id="owner-address" name="owner[address]" value="{{ old('owner.address') }}">
            <input type="hidden" id="owner-infos" name="owner[infos]" value="{{ old('owner.infos') }}">
        </div>
    </div>
</div>
