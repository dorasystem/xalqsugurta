@props(['disabled' => false, 'class' => '', 'idFor' => '', 'name' => '', 'label' => '', 'placeholder' => ''])

<div class="{{ $class }} d-flex flex-column justify-content-end">
    <label for="{{ $idFor }}" class="form-label">{{ __($label) }}</label>
    <input type="text" id="{{ $idFor }}" placeholder="{{ __($placeholder) }}" name="{{ $name }}"
        class="form-input" @if ($disabled === true) disabled @endif>
</div>
