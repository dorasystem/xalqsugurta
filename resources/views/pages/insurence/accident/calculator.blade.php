@extends('layouts.app')
@section('title', __('insurance.accident.product_name'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header
        icon="bi-person-heart"
        :title="__('insurance.accident.product_name')"
        :subtitle="__('insurance.accident.subtitle')"
    />

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach([
            1 => __('messages.applicant'),
            2 => __('messages.insured_persons'),
            3 => __('messages.insurance_summary'),
            4 => __('messages.confirm_details'),
        ] as $num => $label)
            <div class="flex items-center gap-2">
                @if($num < 3)
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;">
                        <i class="bi bi-check"></i>
                    </div>
                    <span class="text-sm font-medium" style="color:#16a34a;">{{ $label }}</span>
                @elseif($num === 3)
                    <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">3</div>
                    <span class="text-sm font-semibold text-blue-600">{{ $label }}</span>
                @else
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">{{ $num }}</div>
                    <span class="text-sm text-gray-400">{{ $label }}</span>
                @endif
            </div>
            @if($num < 4)
                <div class="flex-1" style="height:1px;background:{{ $num < 3 ? '#bbf7d0' : '#e5e7eb' }};"></div>
            @endif
        @endforeach
    </div>

    <div class="row g-4">

        {{-- ── Form Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Persons summary --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-people text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.insured_persons') }} ({{ count($persons) }})</span>
                </div>
                @foreach($persons as $i => $person)
                <div class="px-5 py-3 d-flex align-items-center gap-3 {{ $i < count($persons)-1 ? 'border-bottom' : '' }}">
                    <div class="d-flex align-items-center justify-content-center rounded-circle" style="width:28px;height:28px;min-width:28px;background:#eff6ff;font-size:11px;font-weight:700;color:#2563eb;">
                        {{ $i + 1 }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800 mb-0">{{ $person['lastname'] }} {{ $person['firstname'] }}</p>
                        <p class="text-xs text-gray-400 mb-0">
                            {{ __('messages.sum_insured') }}: {{ number_format($person['sum_insured'], 0, '.', ' ') }} UZS &bull;
                            {{ __('messages.insurance_premium') }}: {{ number_format($person['insurance_premium'], 0, '.', ' ') }} UZS
                        </p>
                    </div>
                </div>
                @endforeach
                <div class="px-5 py-3 bg-gray-50 d-flex justify-content-between align-items-center">
                    <span class="text-sm font-semibold text-gray-700">{{ __('messages.total') }}</span>
                    <span class="text-sm font-bold text-blue-600">{{ number_format($totalSum, 0, '.', ' ') }} UZS / {{ number_format($totalPremium, 0, '.', ' ') }} UZS</span>
                </div>
            </div>

            {{-- Date form --}}
            <form action="{{ route('accident.storeCalculation', ['locale' => getCurrentLocale()]) }}" method="POST">
                @csrf
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                            <i class="bi bi-calendar-check text-blue-600"></i>
                        </div>
                        <span class="font-semibold text-gray-800">{{ __('messages.insurance_details_title') }}</span>
                    </div>
                    <div class="p-5">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('messages.start_date') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="start_date" name="start_date"
                                    min="{{ now()->format('Y-m-d') }}"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('start_date') border-red-400 @else border-gray-200 @enderror"
                                    value="{{ old('start_date', $calculation['start_date'] ?? now()->format('Y-m-d')) }}" required>
                                @error('start_date')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.end_date') }}</label>
                                <input type="date" id="end_date" readonly
                                    class="w-full px-4 py-2.5 rounded-xl border border-gray-100 text-sm"
                                    style="background:#f9fafb;color:#9ca3af;"
                                    value="{{ $calculation['end_date'] ?? '' }}">
                                <p class="mt-1 text-xs text-gray-400">{{ __('messages.auto_calculated') }} (1 {{ __('messages.year') }})</p>
                            </div>
                        </div>
                    </div>
                    <div class="px-5 py-4 border-top d-flex align-items-center justify-content-between" style="background:#f9fafb;">
                        <a href="{{ route('accident.getPersons', ['locale' => getCurrentLocale()]) }}"
                           class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                           style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                            <i class="bi bi-arrow-left"></i>{{ __('messages.back') }}
                        </a>
                        <button type="submit"
                            class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                            style="background:#2563eb;">
                            {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </div>
            </form>

        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">
            <x-insurence.insurance-sidebar
                :title="__('insurance.accident.product_name')"
                :description="__('insurance.accident.subtitle')"
                :insuranceSum="number_format($totalSum, 0, '.', ' ') . ' UZS'"
                :insurancePremium="number_format($totalPremium, 0, '.', ' ') . ' UZS'"
            />
        </div>

    </div>
</div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('start_date').addEventListener('change', function () {
    var d = new Date(this.value);
    if (!isNaN(d)) {
        d.setFullYear(d.getFullYear() + 1);
        d.setDate(d.getDate() - 1);
        document.getElementById('end_date').value = d.toISOString().split('T')[0];
    }
});
// Init end date
document.getElementById('start_date').dispatchEvent(new Event('change'));
</script>
@endpush
