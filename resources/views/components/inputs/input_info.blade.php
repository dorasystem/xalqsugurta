<div class="{{ $class }}">
    <label for="{{ $idFor }}" class="form-label">{{ __($label) }}</label>
    <input type="text" id="{{ $idFor }}" name="{{ $name }}" class="form-input" readonly
        placeholder="{{ __($placeholder) }}" value="{{ old(str_replace(['[', ']'], ['.', ''], $name)) }}">
</div>
