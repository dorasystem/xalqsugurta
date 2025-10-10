@props(['type' => 'button', 'button' => '', 'class' => '', 'disabled' => false])

<div class="{{ $class }} d-flex h-100 flex-column align-items-end justify-content-end">
    <label class="form-label hidden">clean</label>
    <button type="{{ $type }}" id="{{ $button }}" class="btn btn-icon" {{ $disabled ? 'disabled' : '' }}>
        <svg width="20" height="20">
            <use xlink:href="#icon-search"></use>
        </svg>
    </button>
</div>
