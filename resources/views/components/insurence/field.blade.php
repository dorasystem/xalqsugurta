@props([
    'name',
    'label',
    'type'  => 'text',
    'value' => null,
    'help'  => null,
    'id'    => null,
])

@php
    $id ??= 'f_' . $name;
@endphp

<div class="xf-field">
    <label for="{{ $id }}" class="xf-field__label">{{ $label }}</label>
    <div class="xf-field__control">
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $id }}"
            value="{{ old($name, $value) }}"
            {{ $attributes->class(['xf-input', 'is-invalid' => $errors->has($name)]) }}
            @error($name) aria-invalid="true" aria-describedby="{{ $id }}_error" @enderror
        >
        {{ $append ?? '' }}
    </div>
    @error($name)
        <p class="xf-field__error" id="{{ $id }}_error">{{ $message }}</p>
    @else
        @if ($help)
            <p class="xf-field__help">{{ $help }}</p>
        @endif
    @enderror
</div>
