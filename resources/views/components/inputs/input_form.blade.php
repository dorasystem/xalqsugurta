@props([
    'type',
    'disabled' => false,
    'readonly' => false,
    'required' => false,
    'class' => '',
    'idFor' => '',
    'name' => '',
    'label' => '',
    'placeholder' => '',
    'value' => null,
    'min' => null,
    'max' => null,
])

@php
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div class="{{ $class }} d-flex flex-column justify-content-end">
    <label for="{{ $idFor }}" class="form-label">{{ __($label) }}</label>
    <div>
        <input type="{{ $type }}" id="{{ $idFor }}" placeholder="{{ __($placeholder) }}"
            name="{{ $name }}" class="form-input @error($errorKey) is-invalid @enderror"
            value="{{ $value ?? old($errorKey) }}"
            @if ($min !== null) min="{{ $min }}" @endif
            @if ($max !== null) max="{{ $max }}" @endif
            @if ($disabled) disabled @endif
            @if ($readonly) readonly @endif
            @if ($required) required @endif>
        @error($errorKey)
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
