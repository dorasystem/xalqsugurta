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
            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white" style="font-size:11px;background:#22c55e;">
                <i class="bi bi-check"></i>
            </div>
            <span class="text-sm font-medium" style="color:#16a34a;">{{ __('messages.applicant') }}</span>
        </div>
        <div class="flex-1" style="height:1px;background:#bbf7d0;"></div>
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-blue-600 flex items-center justify-center text-white" style="font-size:11px;font-weight:700;">2</div>
            <span class="text-sm font-semibold text-blue-600">{{ __('messages.insurance_summary') }}</span>
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

            {{-- Applicant Card --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-4 mb-4 d-flex align-items-center gap-3">
                <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#f0fdf4;">
                    <i class="bi bi-building" style="color:#16a34a;"></i>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="font-semibold text-gray-900 text-sm mb-0 text-truncate">{{ $applicant['name'] }}</p>
                    <p class="text-xs text-gray-400 mb-0">{{ __('messages.inn') }}: {{ $applicant['inn'] }} &bull; {{ $applicant['representativeName'] }}</p>
                </div>
                <div class="d-flex align-items-center justify-content-center rounded-circle shrink-0" style="width:24px;height:24px;background:#f0fdf4;">
                    <i class="bi bi-check" style="color:#16a34a;font-size:12px;"></i>
                </div>
            </div>

            {{-- Calculation Form --}}
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden mb-4">
                <div class="px-5 py-4 border-bottom d-flex align-items-center gap-3">
                    <div class="d-flex align-items-center justify-content-center rounded-xl" style="width:36px;height:36px;min-width:36px;background:#eff6ff;">
                        <i class="bi bi-calculator text-blue-600"></i>
                    </div>
                    <span class="font-semibold text-gray-800">{{ __('messages.insurance_summary') }}</span>
                </div>
                <div class="p-5">
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('messages.fond_oplaty_truda') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="number" id="fot_input" min="1" step="1"
                                placeholder="10000000"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                value="{{ $calculation['fot'] ?? '' }}">
                        </div>
                        <div class="col-md-6">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                {{ __('messages.start_date') }} <span class="text-red-500">*</span>
                            </label>
                            <input type="date" id="start_date_input"
                                min="{{ now()->format('Y-m-d') }}"
                                class="w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 transition"
                                value="{{ $calculation['start_date'] ?? now()->format('Y-m-d') }}">
                        </div>
                    </div>

                    <button type="button" id="calc_btn"
                        class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0 mb-4"
                        style="background:#2563eb;">
                        <i class="bi bi-calculator"></i> {{ __('messages.calculate') }}
                    </button>

                    {{-- Result panel (hidden until calculated) --}}
                    <div id="calc_result" style="display:none;">
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-sm text-gray-500">{{ __('messages.insurance_sum') }}</span>
                                <span class="text-sm font-bold text-gray-800" id="result_sum">—</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-sm text-gray-500">{{ __('messages.insurance_premium') }}</span>
                                <span class="text-sm font-bold text-green-600" id="result_premium">—</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-sm text-gray-500">{{ __('messages.insurance_rate') }}</span>
                                <span class="text-sm font-semibold text-gray-700" id="result_rate">—</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center py-2">
                                <span class="text-sm text-gray-500">{{ __('messages.period') }}</span>
                                <span class="text-sm font-semibold text-gray-700" id="result_period">—</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Continue form (submits hidden calc data) --}}
            <form id="store_form" action="{{ route('osgor.storeCalculation', ['locale' => getCurrentLocale()]) }}" method="POST">
                @csrf
                <input type="hidden" name="fot"                  id="h_fot">
                <input type="hidden" name="start_date"           id="h_start_date">
                <input type="hidden" name="end_date"             id="h_end_date">
                <input type="hidden" name="insurance_premium"    id="h_premium">
                <input type="hidden" name="insurance_sum"        id="h_sum">
                <input type="hidden" name="insurance_rate"       id="h_rate">
                <input type="hidden" name="funeral_expenses_sum" id="h_funeral">
                <input type="hidden" name="insurance_term_id"    id="h_term_id">

                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('osgor.index', ['locale' => getCurrentLocale()]) }}"
                       class="d-flex align-items-center gap-2 px-4 py-2 rounded-xl text-sm font-semibold text-decoration-none"
                       style="border:1.5px solid #d1d5db;background:#fff;color:#374151;">
                        <i class="bi bi-arrow-left"></i> {{ __('messages.back') }}
                    </a>
                    <button type="submit" id="continue_btn" disabled
                        class="d-flex align-items-center gap-2 px-5 py-2 text-white font-semibold rounded-xl text-sm border-0"
                        style="background:#9ca3af;">
                        {{ __('messages.next_step') }} <i class="bi bi-arrow-right"></i>
                    </button>
                </div>
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
                        <span class="text-sm font-bold text-gray-800" id="sidebar_sum">— UZS</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-xs text-gray-400">{{ __('messages.insurance_premium') }}</span>
                        <span class="text-sm font-bold text-green-600" id="sidebar_premium">— UZS</span>
                    </div>
                </div>
            </div>
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

document.getElementById('calc_btn').addEventListener('click', async function () {
    const fot       = document.getElementById('fot_input').value.trim();
    const startDate = document.getElementById('start_date_input').value;

    if (!fot || !startDate) {
        alert('{{ __("messages.all_fields_required") }}');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    try {
        const resp = await fetch('{{ route("osgor.calculate", ["locale" => getCurrentLocale()]) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ fot, start_date: startDate }),
        });

        const json = await resp.json();

        if (!json.success) {
            alert(json.message || '{{ __("messages.error_occurred") }}');
            return;
        }

        const d = json.data;

        document.getElementById('result_sum').textContent     = fmtUzs(d.insurance_sum);
        document.getElementById('result_premium').textContent = fmtUzs(d.insurance_premium);
        document.getElementById('result_rate').textContent    = d.insurance_rate + '%';
        document.getElementById('result_period').textContent  = d.start_date + ' — ' + d.end_date;

        document.getElementById('sidebar_sum').textContent     = fmtUzs(d.insurance_sum);
        document.getElementById('sidebar_premium').textContent = fmtUzs(d.insurance_premium);

        document.getElementById('h_fot').value       = fot;
        document.getElementById('h_start_date').value = d.start_date;
        document.getElementById('h_end_date').value   = d.end_date;
        document.getElementById('h_premium').value    = d.insurance_premium;
        document.getElementById('h_sum').value        = d.insurance_sum;
        document.getElementById('h_rate').value       = d.insurance_rate;
        document.getElementById('h_funeral').value    = d.funeral_expenses_sum;
        document.getElementById('h_term_id').value    = d.insurance_term_id;

        document.getElementById('calc_result').style.display = '';
        const btn = document.getElementById('continue_btn');
        btn.disabled = false;
        btn.style.background = '#2563eb';

    } catch (e) {
        alert('{{ __("messages.error_occurred") }}');
    } finally {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-calculator"></i> {{ __("messages.calculate") }}';
    }
});
</script>
@endpush
