@props([
    'type',
    'disabled' => false,
    'readonly' => false,
    'class' => '',
    'idFor' => '',
    'name' => '',
    'label' => '',
    'placeholder' => '',
])

@php
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div class="{{ $class }} d-flex flex-column justify-content-end">
    <label for="{{ $idFor }}" class="form-label">{{ __($label) }}</label>
    <div>
        <input type="{{ $type }}" id="{{ $idFor }}" placeholder="{{ __($placeholder) }}"
            name="{{ $name }}" class="form-input @error($errorKey) is-invalid @enderror"
            value="{{ old($errorKey) }}" @if ($disabled === true) disabled @endif
            @if ($readonly === true) readonly @endif>
        @error($errorKey)
            <div class="invalid-feedback d-block">
                {{ $message }}
            </div>
        @enderror
    </div>
</div>
