@extends('layouts.app')
@section('title', 'OSAGO - Ariza ma\'lumotlari')

@push('styles')
    <link href="{{ asset('assets/css/form.css') }}" rel="stylesheet">
@endpush

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
                                        OSAGO Sug'urta Ariza Ma'lumotlari
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
                                                href="{{ route('accident.application.view', ['locale' => 'uz']) }}">
                                                <img src="{{ asset('assets/images/flags/uz.png') }}" alt="UZ"
                                                    class="me-2" style="width: 20px;"> O'zbek
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('accident.application.view', ['locale' => 'ru']) }}">
                                                <img src="{{ asset('assets/images/flags/ru.png') }}" alt="RU"
                                                    class="me-2" style="width: 20px;"> Русский
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item"
                                                href="{{ route('accident.application.view', ['locale' => 'en']) }}">
                                                <img src="{{ asset('assets/images/flags/en.png') }}" alt="EN"
                                                    class="me-2" style="width: 20px;"> English
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            {{-- Applicant Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user me-2 text-primary"></i>
                                        Ariza Beruvchi Ma'lumotlari
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">Familiya:</td>
                                                <td>{{ $applicationData['applicantData']['lastName'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Ism:</td>
                                                <td>{{ $applicationData['applicantData']['firstName'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Otasining ismi:</td>
                                                <td>{{ $applicationData['applicantData']['middleName'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Passport seriyasi:</td>
                                                <td>{{ $applicationData['applicantData']['seria'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">Passport raqami:</td>
                                                <td>{{ $applicationData['applicantData']['number'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Tug'ilgan sana:</td>
                                                <td>{{ $applicationData['applicantData']['birthDate'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Manzil:</td>
                                                <td>{{ $applicationData['applicantData']['address'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Telefon:</td>
                                                <td>{{ $applicationData['phone'] ?? 'Ko\'rsatilmagan' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Insured Person Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-user-shield me-2 text-success"></i>
                                        Sug'urta Qilinuvchi Shaxs Ma'lumotlari
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">Familiya:</td>
                                                <td>{{ $applicationData['insuredInfo']['lastName'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Ism:</td>
                                                <td>{{ $applicationData['insuredInfo']['firstName'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Otasining ismi:</td>
                                                <td>{{ $applicationData['insuredInfo']['middleName'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Passport seriyasi:</td>
                                                <td>{{ $applicationData['insuredInfo']['seria'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">Passport raqami:</td>
                                                <td>{{ $applicationData['insuredInfo']['number'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Tug'ilgan sana:</td>
                                                <td>{{ $applicationData['insuredInfo']['birthDate'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Manzil:</td>
                                                <td>{{ $applicationData['insuredInfo']['address'] ?? 'Ko\'rsatilmagan' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Telefon:</td>
                                                <td>{{ $applicationData['phone'] ?? 'Ko\'rsatilmagan' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Insurance Information --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-shield-alt me-2 text-warning"></i>
                                        Sug'urta Ma'lumotlari
                                    </h5>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">Sug'urta summasi:</td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format($applicationData['insuranceAmount'] ?? 0) }} UZS</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Boshlanish sanasi:</td>
                                                <td>{{ $applicationData['paymentStartDate'] ?? 'Ko\'rsatilmagan' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm">
                                        <tbody>
                                            <tr>
                                                <td class="fw-bold bg-light" style="width: 40%;">Tugash sanasi:</td>
                                                <td>{{ $applicationData['paymentEndDate'] ?? 'Ko\'rsatilmagan' }}</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold bg-light">Premium (0.3%):</td>
                                                <td class="text-success fw-bold">
                                                    {{ number_format((($applicationData['insuranceAmount'] ?? 0) * 0.3) / 100) }}
                                                    UZS</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Public Offer --}}
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h5 class="border-bottom pb-2 mb-3">
                                        <i class="fas fa-file-contract me-2 text-info"></i>
                                        Ommaviy Oferta (Public Offer)
                                    </h5>
                                    <div class="card border-info">
                                        <div class="card-body">
                                            <h6 class="card-title text-info">
                                                <i class="fas fa-gavel me-2"></i>
                                                OSAGO Sug'urta Shartlari
                                            </h6>
                                            <div class="text-justify"
                                                style="max-height: 300px; overflow-y: auto; font-size: 0.9rem;">
                                                <p><strong>1. Umumiy qoidalar:</strong></p>
                                                <ul>
                                                    <li>Ushbu sug'urta polisi O'zbekiston Respublikasi qonunchiligiga
                                                        muvofiq tuzilgan.</li>
                                                    <li>Sug'urta qiluvchi va sug'urta qilinuvchi o'rtasidagi munosabatlar
                                                        shu shartnoma bilan tartibga solinadi.</li>
                                                    <li>Barcha to'lovlar O'zbekiston so'mida amalga oshiriladi.</li>
                                                </ul>

                                                <p><strong>2. Sug'urta qamrovi:</strong></p>
                                                <ul>
                                                    <li>Avtoulov egalarining uchinchi shaxslarga qarshi javobgarlik majburiy
                                                        sug'urtasi (OSAGO)</li>
                                                    <li>Sug'urta hodisasi sodir bo'lganda, sug'urta qilinuvchiga qo'shgan
                                                        zarari uchun to'lov</li>
                                                    <li>Maksimal sug'urta summasi:
                                                        {{ number_format($applicationData['insuranceAmount'] ?? 0) }} UZS
                                                    </li>
                                                </ul>

                                                <p><strong>3. Sug'urta hodisasi:</strong></p>
                                                <ul>
                                                    <li>Avtoulov harakati natijasida uchinchi shaxslarga jismoniy yoki
                                                        moliyaviy zarar yetkazilganda</li>
                                                    <li>Zarar miqdori sug'urta summasi doirasida qoplash</li>
                                                    <li>24 soat ichida sug'urta qiluvchiga xabar berish majburiy</li>
                                                </ul>

                                                <p><strong>4. To'lov shartlari:</strong></p>
                                                <ul>
                                                    <li>Sug'urta mukofoti:
                                                        {{ number_format((($applicationData['insuranceAmount'] ?? 0) * 0.3) / 100) }}
                                                        UZS</li>
                                                    <li>Sug'urta muddati: {{ $applicationData['paymentStartDate'] ?? '' }}
                                                        dan {{ $applicationData['paymentEndDate'] ?? '' }} gacha</li>
                                                    <li>To'lov shartnoma imzolangandan keyin darhol amalga oshiriladi</li>
                                                </ul>

                                                <p><strong>5. Shartnomani bekor qilish:</strong></p>
                                                <ul>
                                                    <li>Sug'urta qilinuvchi tomonidan 14 kun ichida bekor qilish huquqi</li>
                                                    <li>Sug'urta hodisasi sodir bo'lmagan taqdirda, sug'urta mukofotining
                                                        90% qaytariladi</li>
                                                    <li>Bekor qilish uchun yozma ariza taqdim etish kerak</li>
                                                </ul>

                                                <p><strong>6. Boshqa shartlar:</strong></p>
                                                <ul>
                                                    <li>Ushbu shartnoma O'zbekiston Respublikasi qonunchiligiga muvofiq</li>
                                                    <li>Nizolar hal qilinishida O'zbekiston Respublikasi sudlari vakolatli
                                                    </li>
                                                    <li>Shartnoma o'zbek tilida tuzilgan va unga amal qilinadi</li>
                                                </ul>

                                                <div class="alert alert-info mt-3">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    <strong>Diqqat:</strong> Ushbu shartlar to'liq o'qib chiqilgan va qabul
                                                    qilingan deb hisoblanadi.
                                                    Shartnoma imzolash orqali siz barcha shartlarga rozilik bildirasiz.
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Confirmation and Actions --}}
                            <div class="row">
                                <div class="col-12">
                                    <div class="card border-success">
                                        <div class="card-body">
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="confirm-data"
                                                    required>
                                                <label class="form-check-label" for="confirm-data">
                                                    <strong>Barcha ma'lumotlar to'g'ri ekanligini tasdiqlayman</strong>
                                                </label>
                                            </div>
                                            <div class="form-check mb-3">
                                                <input class="form-check-input" type="checkbox" id="confirm-offer"
                                                    required>
                                                <label class="form-check-label" for="confirm-offer">
                                                    <strong>Ommaviy ofertani o'qib chiqdim va barcha shartlarga
                                                        roziman</strong>
                                                </label>
                                            </div>
                                            <form
                                                action="{{ route('accident.storage', ['locale' => getCurrentLocale()]) }}"
                                                method="POST" id="storage-form">
                                                @csrf
                                                <div class="d-flex gap-2">
                                                    <button type="submit" class="btn btn-success"
                                                        id="confirm-application" disabled>
                                                        <i class="fas fa-check me-2"></i>
                                                        To'lovga O'tish
                                                    </button>
                                                    <a href="{{ route('accident.main', ['locale' => getCurrentLocale()]) }}"
                                                        class="btn btn-secondary">
                                                        <i class="fas fa-arrow-left me-2"></i>
                                                        Orqaga Qaytish
                                                    </a>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const confirmData = document.getElementById('confirm-data');
                const confirmOffer = document.getElementById('confirm-offer');
                const confirmBtn = document.getElementById('confirm-application');

                // Enable/disable confirm button based on checkboxes
                function toggleConfirmButton() {
                    if (confirmData.checked && confirmOffer.checked) {
                        confirmBtn.disabled = false;
                        confirmBtn.classList.remove('btn-secondary');
                        confirmBtn.classList.add('btn-success');
                    } else {
                        confirmBtn.disabled = true;
                        confirmBtn.classList.remove('btn-success');
                        confirmBtn.classList.add('btn-secondary');
                    }
                }

                confirmData.addEventListener('change', toggleConfirmButton);
                confirmOffer.addEventListener('change', toggleConfirmButton);

                // Form submit handler
                const storageForm = document.getElementById('storage-form');
                storageForm.addEventListener('submit', function(e) {
                    // Show loading state
                    confirmBtn.innerHTML =
                        '<span class="spinner-border spinner-border-sm me-2"></span>Buyurtma yaratilmoqda...';
                    confirmBtn.disabled = true;
                });

                // Initialize button state
                toggleConfirmButton();
            });
        </script>
    @endpush
@endsection
