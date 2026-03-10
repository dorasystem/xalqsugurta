@extends('layouts.app')
@section('title', __('insurance.tourist.product_name'))

@section('content')
    <section class="py-8 bg-gray-50 min-h-screen">
        <div class="container">

            <x-insurence.page-header icon="bi-luggage-fill" :title="__('insurance.tourist.product_name')" :subtitle="__('insurance.tourist.subtitle')" />

            {{-- Steps --}}
            <div class="flex items-center gap-2 mb-5">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-white"
                        style="font-size:11px;background:#22c55e;">
                        <i class="bi bi-check"></i>
                    </div>
                    <span class="text-sm font-medium" style="color:#16a34a;">{{ __('messages.applicant') }}</span>
                </div>
                <div class="flex-1" style="height:1px;background:#bbf7d0;"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white"
                        style="font-size:11px;font-weight:700;">2</div>
                    <span class="text-sm font-semibold text-blue-600">{{ __('messages.insured_persons') }}</span>
                </div>
                <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-center text-gray-400"
                        style="font-size:11px;font-weight:700;">3</div>
                    <span class="text-sm text-gray-400">{{ __('messages.insurance_summary') }}</span>
                </div>
                <div class="flex-1" style="height:1px;background:#e5e7eb;"></div>
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-full bg-gray-200 flex items-center justify-content-center text-gray-400"
                        style="font-size:11px;font-weight:700;">4</div>
                    <span class="text-sm text-gray-400">{{ __('messages.confirm_details') }}</span>
                </div>
            </div>

            <div class="row g-4">

                {{-- ── Form Column ── --}}
                <div class="col-lg-8">

                    <x-insurence.error-block />

                    {{-- Applicant Card --}}
                    <div
                        class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
                        <div class="d-flex align-items-center justify-content-center rounded-xl"
                            style="width:36px;height:36px;min-width:36px;background:#f0fdf4;">
                            <i class="bi bi-person" style="color:#16a34a;"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="font-semibold text-gray-900 text-sm mb-0">{{ $applicant['lastname'] }}
                                {{ $applicant['firstname'] }}</p>
                            <p class="text-xs text-gray-400 mb-0">
                                {{ $applicant['passport_seria'] }}{{ $applicant['passport_number'] }} &bull;
                                {{ $applicant['phone'] }}</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-center rounded-circle shrink-0"
                            style="width:24px;height:24px;background:#f0fdf4;">
                            <i class="bi bi-check" style="color:#16a34a;font-size:12px;"></i>
                        </div>
                    </div>

                    {{-- Existing persons list --}}
                    @if (count($persons) > 0)
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                            <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-xl"
                                    style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                                    <i class="bi bi-people text-blue-600"></i>
                                </div>
                                <span class="font-semibold text-gray-800">{{ __('messages.insured_persons') }}
                                    ({{ count($persons) }})</span>
                            </div>
                            <div class="divide-y">
                                @foreach ($persons as $i => $person)
                                    <div class="px-5 py-3 d-flex align-items-center gap-3">
                                        <div class="d-flex align-items-center justify-content-center rounded-circle"
                                            style="width:32px;height:32px;min-width:32px;background:#eff6ff;font-size:12px;font-weight:700;color:#2563eb;">
                                            {{ $i + 1 }}
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-800 mb-0">{{ $person['lastname'] }}
                                                {{ $person['firstname'] }}</p>
                                            <p class="text-xs text-gray-400 mb-0">
                                                {{ $person['passport_seria'] }}{{ $person['passport_number'] }} &bull;
                                                {{ __('messages.sum_insured') }}:
                                                {{ number_format($person['sum_insured'], 0, '.', ' ') }} UZS &bull;
                                                {{ __('messages.insurance_premium') }}:
                                                {{ number_format($person['insurance_premium'], 0, '.', ' ') }} UZS
                                            </p>
                                        </div>
                                        <form method="POST" action="{{ route('tourist.removePerson', ['locale' => getCurrentLocale(), 'index' => $i]) }}" onsubmit="return confirm('{{ __('messages.confirm') }}?')">
                                            @csrf
                                            <button type="submit" class="d-flex align-items-center justify-content-center rounded-circle border-0 p-0" style="width:28px;height:28px;background:#fee2e2;cursor:pointer;">
                                                <i class="bi bi-x" style="color:#ef4444;font-size:14px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Add person form --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                        <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center justify-content-center rounded-xl"
                                style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                                <i class="bi bi-person-plus text-blue-600"></i>
                            </div>
                            <span class="font-semibold text-gray-800">{{ __('messages.add_person') }}</span>
                        </div>

                        {{-- Lookup row --}}
                        <div class="p-5 border-bottom">
                            <div class="row g-3 align-items-end">
                                <div class="col-sm-3">
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2">{{ __('insurance.passport.series') }}
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" id="find_seria" maxlength="4" placeholder="AA"
                                        style="text-transform:uppercase;"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                </div>
                                <div class="col-sm-3">
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2">{{ __('insurance.passport.number') }}
                                        <span class="text-red-500">*</span></label>
                                    <input type="text" id="find_number" inputmode="numeric" maxlength="7"
                                        placeholder="1234567"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                </div>
                                <div class="col-sm-3">
                                    <label
                                        class="block text-sm font-semibold text-gray-700 mb-2">{{ __('insurance.passport.birth_date') }}
                                        <span class="text-red-500">*</span></label>
                                    <input type="date" id="find_birth_date"
                                        class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition">
                                </div>
                                <div class="col-sm-3 d-flex flex-column justify-content-end">
                                    <button type="button" id="find_btn"
                                        class="w-full py-2 text-white font-semibold rounded-xl text-sm border-0"
                                        style="background:#2563eb;">
                                        {{ __('messages.search') }}
                                    </button>
                                </div>
                            </div>
                            <div id="find_error" class="mt-2 text-xs text-red-500" style="display:none;"></div>
                        </div>

                        {{-- Found person area (hidden until AJAX) --}}
                        <div id="person_card" style="display:none;">
                            <form action="{{ route('tourist.addPerson', ['locale' => getCurrentLocale()]) }}"
                                method="POST" id="add_person_form">
                                @csrf
                                <input type="hidden" name="pinfl" id="h_pinfl">
                                <input type="hidden" name="passport_seria" id="h_seria">
                                <input type="hidden" name="passport_number" id="h_number">
                                <input type="hidden" name="passport_issue_date" id="h_issue_date">
                                <input type="hidden" name="passport_issued_by" id="h_issued_by">
                                <input type="hidden" name="birth_date" id="h_birth_date">
                                <input type="hidden" name="firstname" id="h_firstname">
                                <input type="hidden" name="lastname" id="h_lastname">
                                <input type="hidden" name="middlename" id="h_middlename">
                                <input type="hidden" name="address" id="h_address">
                                <input type="hidden" name="region_id" id="h_region_id">
                                <input type="hidden" name="district_id" id="h_district_id">
                                <input type="hidden" name="phone" id="h_phone">

                                <div class="p-5 border-bottom">
                                    {{-- Person info display --}}
                                    <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                        <p class="text-sm font-bold text-gray-800 mb-1" id="display_name">—</p>
                                        <p class="text-xs text-gray-500 mb-0" id="display_passport">—</p>
                                    </div>

                                    {{-- Sum insured slider --}}
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                                        {{ __('messages.sum_insured') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="text-xs text-gray-400">50 000 UZS</span>
                                        <span class="text-sm font-bold text-blue-600" id="sum_display">50 000 UZS</span>
                                        <span class="text-xs text-gray-400">1 000 000 UZS</span>
                                    </div>
                                    <input type="range" name="sum_insured" id="sum_slider" min="50000"
                                        max="1000000" step="50000" value="50000" class="w-full">
                                    <input type="hidden" name="insurance_premium" id="h_premium" value="0">
                                    <p class="mt-2 text-xs text-gray-400">
                                        {{ __('messages.insurance_premium') }}: <span class="font-semibold text-green-600"
                                            id="premium_display">—</span>
                                        <span id="premium_loading" class="text-gray-400" style="display:none;">...</span>
                                    </p>
                                </div>

                                <div class="px-5 py-4 d-flex justify-content-end">
                                    <button type="submit"
                                        class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                                        style="background:#2563eb;">
                                        <i class="bi bi-plus-lg"></i> {{ __('messages.add_to_list') }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Insurance period + submit --}}
                    <form action="{{ route('tourist.storeCalculation', ['locale' => getCurrentLocale()]) }}" method="POST">
                        @csrf
                        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                            <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                                <div class="d-flex align-items-center justify-content-center rounded-xl"
                                    style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
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
                                            value="{{ old('start_date', now()->format('Y-m-d')) }}"
                                            class="w-full px-4 py-2.5 rounded-xl border text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition @error('start_date') border-red-400 @else border-gray-200 @enderror"
                                            required>
                                        @error('start_date')
                                            <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                                        @enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">{{ __('messages.end_date') }}</label>
                                        <input type="date" id="end_date" readonly
                                            class="w-full px-4 py-2.5 rounded-xl border border-gray-100 text-sm"
                                            style="background:#f9fafb;color:#9ca3af;">
                                        <p class="mt-1 text-xs text-gray-400">{{ __('messages.auto_calculated') }} (1 {{ __('messages.year') }})</p>
                                    </div>
                                </div>
                            </div>
                            <div class="px-5 py-4 border-top d-flex align-items-center justify-content-between" style="background:#f9fafb;">
                                <a href="{{ route('tourist.index', ['locale' => getCurrentLocale()]) }}"
                                    class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                                    style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                                    <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                                </a>
                                @if (count($persons) > 0)
                                    <button type="submit"
                                        class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                                        style="background:#2563eb;">
                                        {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                                    </button>
                                @else
                                    <span class="text-xs text-gray-400">{{ __('messages.at_least_one_person') }}</span>
                                @endif
                            </div>
                        </div>
                    </form>

                </div>

                {{-- ── Sidebar ── --}}
                <div class="col-lg-4">
                    @php
                        $totalSum = array_sum(array_column($persons, 'sum_insured'));
                        $totalPremium = array_sum(array_column($persons, 'insurance_premium'));
                    @endphp
                    <x-insurence.insurance-sidebar
                        :title="__('insurance.tourist.product_name')"
                        :description="__('insurance.tourist.subtitle')"
                        :insuranceSum="$totalSum > 0 ? number_format($totalSum, 0, '.', ' ') . ' UZS' : '0 UZS'"
                        :insurancePremium="$totalPremium > 0 ? number_format($totalPremium, 0, '.', ' ') . ' UZS' : '0 UZS'"
                    />
                </div>

            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function fmtUzs(n) {
            return Number(n).toLocaleString('ru-RU') + ' UZS';
        }

        document.getElementById('find_btn').addEventListener('click', async function() {
            const seria = document.getElementById('find_seria').value.trim().toUpperCase();
            const number = document.getElementById('find_number').value.trim();
            const birthDate = document.getElementById('find_birth_date').value;
            const errEl = document.getElementById('find_error');

            if (!seria || !number || !birthDate) {
                errEl.textContent = '{{ __('messages.all_fields_required') }}';
                errEl.style.display = '';
                return;
            }

            errEl.style.display = 'none';
            this.disabled = true;
            this.textContent = '...';

            try {
                const resp = await fetch(
                    '{{ route('tourist.findPerson', ['locale' => getCurrentLocale()]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            passport_seria: seria,
                            passport_number: number,
                            birth_date: birthDate
                        }),
                    });

                const json = await resp.json();

                if (!json.success) {
                    errEl.textContent = json.message || '{{ __('messages.person_not_found') }}';
                    errEl.style.display = '';
                    document.getElementById('person_card').style.display = 'none';
                    return;
                }

                const d = json.data;
                document.getElementById('h_pinfl').value = d.pinfl;
                document.getElementById('h_seria').value = d.passport_seria;
                document.getElementById('h_number').value = d.passport_number;
                document.getElementById('h_issue_date').value = d.passport_issue_date || '';
                document.getElementById('h_issued_by').value = d.passport_issued_by || '';
                document.getElementById('h_birth_date').value = d.birth_date;
                document.getElementById('h_firstname').value = d.firstname;
                document.getElementById('h_lastname').value = d.lastname;
                document.getElementById('h_middlename').value = d.middlename || '';
                document.getElementById('h_address').value = d.address || '';
                document.getElementById('h_region_id').value = d.region_id;
                document.getElementById('h_district_id').value = d.district_id;
                document.getElementById('h_phone').value = d.phone || '';

                document.getElementById('display_name').textContent = d.lastname + ' ' + d.firstname + ' ' + (d.middlename || '');
                document.getElementById('display_passport').textContent = d.passport_seria + d.passport_number + ' | PINFL: ' + d.pinfl;

                document.getElementById('person_card').style.display = '';
                updateSlider();

            } catch (e) {
                errEl.textContent = '{{ __('messages.error_occurred') }}';
                errEl.style.display = '';
            } finally {
                this.disabled = false;
                this.textContent = '{{ __('messages.search') }}';
            }
        });

        const calcUrl = '{{ route('tourist.calculatePremium', ['locale' => getCurrentLocale()]) }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

        let calcTimer = null;

        async function fetchPremium(sumInsured) {
            document.getElementById('premium_display').style.display = 'none';
            document.getElementById('premium_loading').style.display = '';

            try {
                const resp = await fetch(calcUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ sum_insured: sumInsured }),
                });

                const json = await resp.json();

                if (json.success) {
                    document.getElementById('h_premium').value = json.premium;
                    document.getElementById('premium_display').textContent = fmtUzs(json.premium);
                } else {
                    document.getElementById('premium_display').textContent = '—';
                }
            } catch (e) {
                document.getElementById('premium_display').textContent = '—';
            } finally {
                document.getElementById('premium_loading').style.display = 'none';
                document.getElementById('premium_display').style.display = '';
            }
        }

        function updateSlider() {
            const val = parseInt(document.getElementById('sum_slider').value, 10);
            document.getElementById('sum_display').textContent = fmtUzs(val);

            clearTimeout(calcTimer);
            calcTimer = setTimeout(() => fetchPremium(val), 500);
        }

        document.getElementById('sum_slider').addEventListener('input', updateSlider);
        updateSlider();

        // Auto-calculate end date (start + 1 year - 1 day)
        function updateEndDate() {
            const val = document.getElementById('start_date').value;
            if (!val) return;
            const d = new Date(val);
            d.setFullYear(d.getFullYear() + 1);
            d.setDate(d.getDate() - 1);
            document.getElementById('end_date').value = d.toISOString().split('T')[0];
        }
        document.getElementById('start_date').addEventListener('change', updateEndDate);
        updateEndDate();
    </script>
@endpush
