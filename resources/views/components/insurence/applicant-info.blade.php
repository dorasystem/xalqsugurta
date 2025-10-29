<div id="applicant-info" class="card {{ old('applicant.lastName') ? '' : 'd-none' }}">
    <div class="card-header">
        <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
    </div>
    <div class="card-body">
        <div class="form-check">
            <input class="form-check-input @error('is_applicant_owner') is-invalid @enderror" type="checkbox"
                id="is-applicant-owner" name="is_applicant_owner" {{ old('is_applicant_owner') ? 'checked' : '' }}>
            <label class="form-check-label" for="is-applicant-owner">
                {{ __('messages.is_applicant_owner') }}
            </label>
            @error('is_applicant_owner')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div id="applicant-info-search">
            <div class="row">
                <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'applicant-passport-series'" :name="'applicant[passportSeries]'"
                    :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />

                <x-inputs.input_form :type="'number'" :class="'col-md-3'" :idFor="'applicant-passport-number'" :name="'applicant[passportNumber]'"
                    :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number'" />

                <x-inputs.input_form :type="'number'" :class="'col-md-4'" :idFor="'applicant-pinfl'" :name="'applicant[pinfl]'"
                    :placeholder="'messages.owner_pinfl_placeholder'" :label="'messages.owner_pinfl'" />

                <x-inputs.button :class="'col-md-3'" :button="'applicant-information-search-btn'" />
            </div>
        </div>
    </div>

    <div id="applicant-info-display" class="card-footer {{ old('applicant.lastName') ? '' : 'd-none' }}">
        <div class="row">
            <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-last-name'" :name="'applicant[lastName]'" :label="'messages.last_name'"
                :placeholder="'messages.last_name_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-first-name'" :name="'applicant[firstName]'" :label="'messages.first_name'"
                :placeholder="'messages.first_name_placeholder'" />

            <x-inputs.input_info :class="'col-md-4'" :idFor="'applicant-middle-name'" :name="'applicant[middleName]'" :label="'messages.middle_name'"
                :placeholder="'messages.middle_name_placeholder'" />

            <input type="hidden" name="applicant[infos]" id="applicant-infos" value="{{ old('applicant.infos') }}">
        </div>

        <div class="row">
            <x-inputs.input_info :class="'col-md-6'" :idFor="'applicant-address'" :name="'applicant[address]'" :label="'messages.address'"
                :placeholder="'messages.address_placeholder'" />

            <x-inputs.input_form :type="'number'" :class="'col-md-6'" :idFor="'applicant-phone-number'" :name="'applicant[phoneNumber]'"
                :label="'messages.phone_number'" :placeholder="'messages.phone_number_placeholder'" />
        </div>
    </div>
</div>
