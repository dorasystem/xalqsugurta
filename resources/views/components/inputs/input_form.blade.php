@props([
    'type',
    'disabled' => false,
    'class' => '',
    'idFor' => '',
    'name' => '',
    'label' => '',
    'placeholder' => '',
])

<div class="{{ $class }} d-flex flex-column justify-content-end">
    <label for="{{ $idFor }}" class="form-label">{{ __($label) }}</label>
    <div class="position-relative">
        <input type="{{ $type }}" id="{{ $idFor }}" placeholder="{{ __($placeholder) }}"
            name="{{ $name }}" class="form-input @error($name) is-invalid @enderror" value="{{ old($name) }}"
            @if ($disabled === true) disabled @endif>
        @error($name)
            <div class="position-absolute top-50 end-0 translate-middle-y me-2">
                <i class="fas fa-exclamation-circle text-danger" data-bs-toggle="tooltip" data-bs-placement="top"
                    data-bs-title="{{ $message }}" style="cursor: help; font-size: 16px;"></i>
            </div>
        @enderror
    </div>
</div>
