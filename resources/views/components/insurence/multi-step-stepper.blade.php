@props([
    'totalSteps' => 5,
    'currentStep' => 1,
    'labels' => [],
])

<div class="multi-step-stepper mb-4" data-total-steps="{{ $totalSteps }}" data-current-step="{{ $currentStep }}">
    <div class="progress mb-2" style="height: 6px;">
        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ ($currentStep / $totalSteps) * 100 }}%;" aria-valuenow="{{ $currentStep }}" aria-valuemin="1" aria-valuemax="{{ $totalSteps }}"></div>
    </div>
    <div class="d-flex justify-content-between small text-muted">
        @for ($i = 1; $i <= $totalSteps; $i++)
            <span class="step-label {{ $i <= $currentStep ? 'text-primary fw-semibold' : '' }}" data-step="{{ $i }}">
                {{ $labels[$i - 1] ?? __('messages.step') . ' ' . $i }}
            </span>
        @endfor
    </div>
</div>
