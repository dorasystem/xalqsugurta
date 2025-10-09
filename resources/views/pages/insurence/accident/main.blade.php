@extends('layouts.app')
@section('title', 'OSAGO')

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps />
    <section class="container-fluid product-page py-4" id="accident-main" style="">
        <div class="container">
            <form action="{{ route('accident.application', ['locale' => getCurrentLocale()]) }}" method="post">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        <div id="person-info" class="card">
                            <div class="card-header">
                                <h4 class="card-title">{{ __('messages.applicant_info_title') }}</h4>
                            </div>
                            <div class="card-body">
                                <div class="row gap-1">

                                    <x-inputs.input_form :type="'text'" :class="'col-md-2'" :idFor="'passport_seria'"
                                        :name="'passport_seria'" :placeholder="'messages.passport_series_placeholder'" :label="'messages.passport_series'" />
                                    <x-inputs.input_form :type="'text'" :class="'col-md-3'" :idFor="'passport_number'"
                                        :name="'passport_number'" :placeholder="'messages.passport_number_placeholder'" :label="'messages.passport_number_placeholder'" />
                                    <x-inputs.input_form :type="'date'" :class="'col-md-3'" :idFor="'applicant_birthDate'"
                                        :name="'applicant_birthDate'" :placeholder="'messages.applicant_birthDate_placeholder'" :label="'messages.applicant_birthDate'" />
                                    <x-inputs.button :class="'col-md-3'" :button="'applicant-information-search-btn'" />

                                </div>
                            </div>

                            <div id="vehicle-info-display" class="card-footer d-none">
                                <div class="row">

                                </div>

                                <div class="row">

                                    <x-inputs.input_info :class="'col-md-4'" :idFor="'registration_region'" :name="'registration_region'"
                                        :label="'messages.registration_region'" :placeholder="'messages.registration_region_placeholder'" />

                                </div>
                            </div>

                        </div>
                    </div>
                    <x-insurence.calculate />
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const applicantInfoBtn = document.getElementById('applicant-information-search-btn');
                const applicantInfoCheck = document.getElementById('is-applicant-owner');
                const applicantInfoBtn = document.getElementById('applicant-information-search-btn');

                applicantInfoBtn.addEventListener('click', function() {
                    if (applicantInfoCheck.checked) {
                        window.location.href =
                            '{{ route('accident.application', ['locale' => getCurrentLocale()]) }}';
                    }
                });
            });
        </script>
    @endpush
@endsection
