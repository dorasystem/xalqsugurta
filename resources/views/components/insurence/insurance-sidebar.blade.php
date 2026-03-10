@props([
    'title',
    'description' => '',
    'insuranceSum' => '0 UZS',
    'insurancePremium' => '0 UZS',
])

<div class="rounded-2xl text-white p-5 mb-4" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
    <div class="d-flex align-items-center justify-content-center rounded-xl mb-3"
         style="width:40px;height:40px;background:rgba(255,255,255,0.2);">
        <i class="bi bi-shield-check fs-5"></i>
    </div>
    <h3 class="font-bold fs-6 mb-1">{{ $title }}</h3>
    @if($description)
        <p class="mb-0 text-sm opacity-75">{{ $description }}</p>
    @endif
</div>

<div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
    <div class="px-4 py-3 border-bottom d-flex align-items-center gap-2">
        <i class="bi bi-receipt text-blue-600"></i>
        <span class="text-sm font-semibold text-gray-700">{{ __('messages.insurance_summary') }}</span>
    </div>
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-xs text-gray-400">{{ __('messages.insurance_sum') }}</span>
            <span id="sidebar_sum" class="text-sm font-bold text-gray-800">{{ $insuranceSum }}</span>
        </div>
        <div class="d-flex justify-content-between align-items-center py-2">
            <span class="text-xs text-gray-400">{{ __('messages.insurance_premium') }}</span>
            <span id="sidebar_premium" class="text-sm font-bold text-green-600">{{ $insurancePremium }}</span>
        </div>
    </div>
</div>
