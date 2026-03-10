@extends('layouts.app')
@section('title', __('insurance.property.page_title'))

@section('content')
<section class="py-8 bg-gray-50 min-h-screen">
<div class="container">

    <x-insurence.page-header icon="bi-house" :title="__('insurance.property.page_title')" :subtitle="__('insurance.property.subtitle')" />

    {{-- Steps --}}
    <div class="flex items-center gap-2 mb-5">
        @foreach([1 => __('messages.applicant'), 2 => __('messages.property_owner'), 3 => __('messages.property_info'), 4 => __('messages.confirm_details')] as $n => $lbl)
            <div class="flex items-center gap-2">
                @if($n < 2)<div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;"><i class="bi bi-check"></i></div><span class="text-sm font-medium" style="color:#16a34a;">{{ $lbl }}</span>
                @elseif($n===2)<div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">2</div><span class="text-sm font-semibold text-blue-600">{{ $lbl }}</span>
                @else<div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400" style="font-size:11px;font-weight:700;">{{ $n }}</div><span class="text-sm text-gray-400">{{ $lbl }}</span>@endif
            </div>
            @if($n < 4)<div class="flex-1" style="height:1px;background:{{ $n < 2 ? '#bbf7d0' : '#e5e7eb' }};"></div>@endif
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-lg-8">

            <x-insurence.error-block />

            {{-- Applicant summary --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#f0fdf4;">
                    <i class="bi bi-person" style="color:#16a34a;"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 text-sm mb-0">{{ $applicant['lastname'] }} {{ $applicant['firstname'] }}</p>
                    <p class="text-xs text-gray-400 mb-0">{{ $applicant['passport_seria'] }}{{ $applicant['passport_number'] }} &bull; {{ $applicant['phone'] }}</p>
                </div>
                <i class="bi bi-check" style="color:#16a34a;"></i>
            </div>

            <form action="{{ route('property.storeOwner', ['locale' => getCurrentLocale()]) }}" method="POST" id="owner_form">
                @csrf

                {{-- Toggle: same as applicant --}}
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <input type="checkbox" id="owner_same" name="owner_same" value="1" checked
                            class="form-check-input" style="width:20px;height:20px;">
                        <label for="owner_same" class="text-sm font-semibold text-gray-700 mb-0 cursor-pointer">
                            {{ __('messages.property_owner_is_applicant') }}
                        </label>
                    </div>
                </div>

                {{-- Owner passport fields (shown when NOT same) --}}
                <div id="owner_fields" style="display:none;">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                        <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                                <i class="bi bi-person-check text-blue-600"></i>
                            </div>
                            <span class="font-semibold text-gray-800">{{ __('messages.owner_info_title') }}</span>
                        </div>
                        <div class="p-5">
                            <div class="row g-3">
                                <div class="col-sm-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('insurance.passport.series') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="passport_seria" maxlength="4" placeholder="AA" style="text-transform:uppercase;"
                                        class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('passport_seria') border-red-400 @else border-gray-200 @enderror"
                                        value="{{ old('passport_seria') }}">
                                    @error('passport_seria')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('insurance.passport.number') }} <span class="text-red-500">*</span></label>
                                    <input type="text" name="passport_number" inputmode="numeric" maxlength="7" placeholder="1234567"
                                        class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('passport_number') border-red-400 @else border-gray-200 @enderror"
                                        value="{{ old('passport_number') }}">
                                    @error('passport_number')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-sm-4">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('insurance.passport.birth_date') }} <span class="text-red-500">*</span></label>
                                    <input type="date" name="birth_date"
                                        class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('birth_date') border-red-400 @else border-gray-200 @enderror"
                                        value="{{ old('birth_date') }}">
                                    @error('birth_date')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                                <div class="col-sm-6">
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.phone_number') }} <span class="text-red-500">*</span></label>
                                    <input type="tel" name="phone" inputmode="tel" placeholder="+998 90 123 45 67"
                                        class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('phone') border-red-400 @else border-gray-200 @enderror"
                                        value="{{ old('phone') }}">
                                    @error('phone')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('property.index', ['locale' => getCurrentLocale()]) }}"
                       class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                       style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                        <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                    <button type="submit" class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0" style="background:#2563eb;">
                        {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
            </form>

        </div>
        <div class="col-lg-4">
            <x-insurence.insurance-sidebar :title="__('insurance.property.product_name')" :description="__('insurance.property.subtitle')" />
        </div>
    </div>
</div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('owner_same').addEventListener('change', function () {
    document.getElementById('owner_fields').style.display = this.checked ? 'none' : '';
});
</script>
@endpush
