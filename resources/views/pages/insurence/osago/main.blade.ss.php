@extends('layouts.app')
@section('title', 'OSAGO')

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

@section('content')
    <x-insurence.steps :activeStep="1" />
    <section class="container-fluid product-page py-4" id="osago-main">
        <div class="container">
            <form id="policy-calculation-form" method="POST" action="{{ route('osago.application', app()->getLocale()) }}">
                @csrf
                <div class="row g-4">
                    <div class="col-md-8">
                        {{-- Validation errors display --}}
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" id="validation-errors-alert">
                                <h4 class="alert-heading">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    {{ __('messages.error') }}
                                </h4>
                                <p class="mb-2">
                                    {{ __('messages.validation_errors_found') }}
                                </p>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                            <script>
                                // Auto-scroll to validation errors
                                document.addEventListener('DOMContentLoaded', function() {
                                    const alertEl = document.getElementById('validation-errors-alert');
                                    if (alertEl) {
                                        setTimeout(() => {
                                            alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });

                                            // Also scroll to first error input
                                            const firstError = document.querySelector('.is-invalid');
                                            if (firstError) {
                                                setTimeout(() => {
                                                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                                                    firstError.focus();
                                                }, 300);
                                            }
                                        }, 100);
                                    }
                                });
                            </script>
                        @endif

                        <x-insurence.vehicle-info />
                        <x-insurence.owner-info />
                        <x-insurence.applicant-info />
                        <x-insurence.policy-calculation />
                        <x-insurence.limited-drivers-info />
                        <x-insurence.confirmation />
                    </div>
                    <x-insurence.calculate />
                </div>
            </form>
        </div>
    </section>


@push('scripts')
        {{-- IMPORTANT: Define TRANSLATIONS BEFORE loading Vite scripts --}}
        <script>
            // Pass translations to JavaScript (must be defined before config.js loads)
            window.TRANSLATIONS = {
                car_type_2: {!! json_encode(__('insurance.car_type_2')) !!},
                car_type_6: {!! json_encode(__('insurance.car_type_6')) !!},
                car_type_9: {!! json_encode(__('insurance.car_type_9')) !!},
                car_type_15: {!! json_encode(__('insurance.car_type_15')) !!},
                edit_vehicle_warning: {!! json_encode(__('messages.edit_vehicle_warning')) !!},
                edit_owner_warning: {!! json_encode(__('messages.edit_owner_warning')) !!},
                edit_applicant_warning: {!! json_encode(__('messages.edit_applicant_warning')) !!},
                driver_info_title: {!! json_encode(__('messages.driver_info_title')) !!},
                driver_full_name: {!! json_encode(__('messages.driver_full_name')) !!},
                kinship: {!! json_encode(__('messages.kinship')) !!}
            };

            // DEBUG: Log translations to console
            console.log('🔍 DEBUG - TRANSLATIONS loaded:', window.TRANSLATIONS);
            console.log('🔍 DEBUG - car_type_2:', window.TRANSLATIONS.car_type_2);
        </script>

        {{-- Load OSAGO modular JavaScript --}}
        @vite(['resources/js/osago/main.js'])
    @endpush
@endsection
