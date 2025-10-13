@extends('layouts.app')

@section('content')
    <section class="container-fluid product-page py-4">
        <div class="container">
            {{-- Error Messages --}}
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Xatolik!</strong> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Xatoliklar:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h3 class="card-title mb-0">
                                        <i class="fas fa-file-alt me-2"></i>
                                        MOL-MULK Sug'urta Ariza Ma'lumotlari
                                    </h3>
                                    <p class="text-muted mb-0">Ariza ma'lumotlarini tekshirib, tasdiqlang</p>
                                </div>
                                {{-- Language Switcher --}}
                                <div class="dropdown">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                                        id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                        <i class="fas fa-globe me-2"></i>
                                        {{ strtoupper(app()->getLocale()) }}
                                    </button>
                                    <ul class="dropdown-menu" aria-labelledby="languageDropdown">
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('property.application.view', ['locale' => 'uz']) }}">
                                                <img src="{{ asset('assets/images/flags/uz.png') }}" alt="UZ"
                                                    class="me-2" style="width: 20px;"> O'zbek
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('property.application.view', ['locale' => 'ru']) }}">
                                                <img src="{{ asset('assets/images/flags/ru.png') }}" alt="RU"
                                                    class="me-2" style="width: 20px;"> Русский
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('property.application.view', ['locale' => 'en']) }}">
                                                <img src="{{ asset('assets/images/flags/en.png') }}" alt="EN"
                                                    class="me-2" style="width: 20px;"> English
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Property Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-home me-2"></i>
                                        Mulk ma'lumotlari
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Kadastr raqami</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['property']['cadasterNumber'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Manzil</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['property']['address'] ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Maydoni</label>
                                    <p class="form-control-plaintext">{{ $applicationData['property']['area'] ?? 'N/A' }}
                                        m²
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Narxi</label>
                                    <p class="form-control-plaintext">
                                        {{ number_format($applicationData['property']['price'] ?? 0) }} UZS</p>
                                </div>
                            </div>

                            {{-- Owner Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user me-2"></i>
                                        Egasi ma'lumotlari
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Familiyasi</label>
                                    <p class="form-control-plaintext">{{ $applicationData['owner']['lastName'] ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ismi</label>
                                    <p class="form-control-plaintext">{{ $applicationData['owner']['firstName'] ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Otasining ismi</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['owner']['middleName'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Telefon raqami</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['owner']['phoneNumber'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Applicant Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user-check me-2"></i>
                                        Arizachi ma'lumotlari
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Familiyasi</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['applicant']['lastName'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Ismi</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['applicant']['firstName'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Otasining ismi</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['applicant']['middleName'] ?? 'N/A' }}</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Telefon raqami</label>
                                    <p class="form-control-plaintext">
                                        {{ $applicationData['applicant']['phoneNumber'] ?? 'N/A' }}</p>
                                </div>
                            </div>

                            {{-- Insurance Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-shield-alt me-2"></i>
                                        Sug'urta ma'lumotlari
                                    </h5>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Sug'urta summasi</label>
                                    <p class="form-control-plaintext">
                                        {{ number_format($applicationData['insuranceAmount'] ?? 0) }} UZS</p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Boshlanish sanasi</label>
                                    <p class="form-control-plaintext">{{ $applicationData['paymentStartDate'] ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Tugash sanasi</label>
                                    <p class="form-control-plaintext">{{ $applicationData['paymentEndDate'] ?? 'N/A' }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Sug'urta mukofoti</label>
                                    <p class="form-control-plaintext text-success fw-bold">
                                        {{ number_format($applicationData['insurancePremium'] ?? 0) }} UZS</p>
                                </div>
                            </div>

                            {{-- API Response (if available) --}}
                            @if (isset($apiResponse))
                                <div class="row mb-4">
                                    <div class="col-12">
                                        <h5 class="border-bottom pb-2 mb-3">
                                            <i class="fas fa-check-circle me-2 text-success"></i>
                                            API Javobi
                                        </h5>
                                        <div class="alert alert-success">
                                            <pre class="mb-0">{{ json_encode($apiResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('property.main', ['locale' => app()->getLocale()]) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-arrow-left me-2"></i>Orqaga
                                </a>
                                <form action="{{ route('property.storage', ['locale' => app()->getLocale()]) }}"
                                    method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success">
                                        <i class="fas fa-credit-card me-2"></i>To'lovni davom ettirish
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
