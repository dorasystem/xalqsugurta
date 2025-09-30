@props(['placeholder', 'type', 'name', 'value', 'label', 'style'])

<div class="flex flex-col">
    <label for="{{ $name }}">{{ $label }}</label>
    <input type="{{ $type }}" class="text-input {{ $style }}" placeholder="{{ $placeholder }}"
        name="{{ $name }}" value="{{ $value }}">
</div>
