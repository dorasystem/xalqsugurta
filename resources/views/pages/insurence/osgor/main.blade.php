@extends('layouts.app')
@section('title', __('insurance.osgor.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5">
        <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center shrink-0">
            <i class="bi bi-person-badge text-white"></i>
        </div>
        <div>
            <h1 class="text-xl font-bold text-gray-900 mb-0">{{ __('insurance.osgor.title') }}</h1>
            <p class="text-sm text-gray-500 mb-0">{{ __('insurance.osgor.description') }}</p>
        </div>
    </div>

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">1</div>
            <span class="text-sm font-semibold text-blue-600">{{ __('messages.applicant') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">2</div>
            <span class="text-sm text-gray-400">{{ __('messages.insurance_summary') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">3</div>
            <span class="text-sm text-gray-400">{{ __('messages.confirm_details') }}</span>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Form Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            <form action="{{ route('osgor.storeApplicant', ['locale' => getCurrentLocale()]) }}" method="POST">
                @csrf
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                    <label for="inn" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('messages.inn') }} <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="inn" name="inn" inputmode="numeric" maxlength="9"
                        placeholder="{{ __('messages.inn_placeholder') }}"
                        class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('inn') border-red-400 bg-red-50 @else border-gray-200 @enderror"
                        value="{{ old('inn') }}">
                    @error('inn')
                        <p class="mt-1 text-xs text-red-500 d-flex align-items-center gap-1">
                            <i class="bi bi-exclamation-circle"></i> {{ $message }}
                        </p>
                    @enderror
                    <p class="mt-2 text-xs text-gray-400">{{ __('messages.inn_invalid') }}</p>
                </div>
                <x-insurence.offerta-agreement :product="$product" />
                <button type="submit" class="w-full py-2 text-white font-semibold rounded-xl d-flex align-items-center justify-content-center gap-2 text-sm border-0" style="background:#2563eb;">
                    {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                </button>
            </form>

        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">
            <div class="rounded-2xl text-white p-5 mb-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                <div class="w-10 h-10 rounded-xl mb-3 d-flex align-items-center justify-content-center" style="background:rgba(255,255,255,0.2);">
                    <i class="bi bi-person-badge fs-5"></i>
                </div>
                <h3 class="font-bold fs-6 mb-1">{{ __('insurance.osgor.title') }}</h3>
                <p class="mb-0 text-sm opacity-75">{{ __('insurance.osgor.description') }}</p>
            </div>
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-4 py-3 border-bottom d-flex align-items-center gap-2">
                    <i class="bi bi-receipt text-blue-600"></i>
                    <span class="text-sm font-semibold text-gray-700">{{ __('messages.insurance_summary') }}</span>
                </div>
                <div class="p-4">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_sum') }}</span>
                        <span class="text-sm font-bold text-gray-800">— UZS</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_premium') }}</span>
                        <span class="text-sm font-bold text-green-600">— UZS</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
</section>
@endsection
