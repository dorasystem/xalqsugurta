@extends('layouts.app')
@section('title', __('insurance.tourist.product_name'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header
        icon="bi-luggage-fill"
        :title="__('insurance.tourist.product_name')"
        :subtitle="__('insurance.tourist.subtitle')"
    />

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">1</div>
            <span class="text-sm font-semibold text-blue-600">{{ __('messages.applicant') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">2</div>
            <span class="text-sm text-gray-400">{{ __('messages.insured_persons') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">3</div>
            <span class="text-sm text-gray-400">{{ __('messages.insurance_summary') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">4</div>
            <span class="text-sm text-gray-400">{{ __('messages.confirm_details') }}</span>
        </div>
    </div>

    <div class="row g-4">

        {{-- ── Form Column ── --}}
        <div class="col-lg-8">

            <x-insurence.error-block />

            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;border-radius:10px;">
                        <i class="bi bi-person text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.person_info_title') }}</span>
                </div>

                <form action="{{ route('tourist.storeApplicant', ['locale' => getCurrentLocale()]) }}" method="POST">
                    @csrf
                    <div class="p-5">
                        <div class="row g-3">

                            <div class="col-sm-4">
                                <label for="passport_seria" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.passport.series') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="passport_seria" name="passport_seria"
                                    maxlength="4" placeholder="AA"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('passport_seria') border-red-400 @else border-gray-200 @enderror"
                                    value="{{ old('passport_seria') }}"
                                    style="text-transform:uppercase;">
                                @error('passport_seria')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="passport_number" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.passport.number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="text" id="passport_number" name="passport_number"
                                    inputmode="numeric" maxlength="7" placeholder="1234567"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('passport_number') border-red-400 @else border-gray-200 @enderror"
                                    value="{{ old('passport_number') }}">
                                @error('passport_number')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-sm-4">
                                <label for="birth_date" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('insurance.passport.birth_date') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="date" id="birth_date" name="birth_date"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('birth_date') border-red-400 @else border-gray-200 @enderror"
                                    value="{{ old('birth_date') }}">
                                @error('birth_date')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="col-sm-6">
                                <label for="phone" class="block text-sm font-semibold text-gray-700 mb-2">
                                    {{ __('messages.phone_number') }} <span class="text-red-500">*</span>
                                </label>
                                <input type="tel" id="phone" name="phone"
                                    inputmode="tel" placeholder="+998 90 123 45 67"
                                    class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('phone') border-red-400 @else border-gray-200 @enderror"
                                    value="{{ old('phone') }}">
                                @error('phone')
                                    <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                @enderror
                            </div>

                        </div>
                    </div>

                    <x-insurence.offerta-agreement :product="$product" />

                    <div class="px-5 py-4 border-top d-flex align-items-center justify-content-end" style="background:#f9fafb;">
                        <button type="submit"
                            class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                            style="background:#2563eb;">
                            {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                        </button>
                    </div>
                </form>
            </div>

        </div>

        {{-- ── Sidebar ── --}}
        <div class="col-lg-4">
            <x-insurence.insurance-sidebar
                :title="__('insurance.tourist.product_name')"
                :description="__('insurance.tourist.subtitle')"
            />
        </div>

    </div>
</div>
</section>
@endsection
