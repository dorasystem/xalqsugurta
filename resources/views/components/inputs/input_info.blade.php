@php
    $errorKey = str_replace(['[', ']'], ['.', ''], $name);
@endphp

<div class="{{ $class }}">
    <label for="{{ $idFor }}" class="form-label">{{ __($label) }}</label>
    <input type="text" id="{{ $idFor }}" name="{{ $name }}"
        class="form-input @error($errorKey) is-invalid @enderror" readonly placeholder="{{ __($placeholder) }}"
        value="{{ old($errorKey) }}">
    @error($errorKey)
        <div class="invalid-feedback d-block">
            {{ $message }}
        </div>
    @enderror
</div>
