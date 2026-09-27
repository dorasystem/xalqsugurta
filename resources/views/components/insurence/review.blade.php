@props([
    'title',
    'editUrl' => null,
    'items'   => [],   // [label => value]
])

<div class="xf-review">
    <div class="xf-review__head">
        <span class="xf-review__title">{{ $title }}</span>
        @if ($editUrl)
            <a href="{{ $editUrl }}" class="xf-review__edit">{{ __t('messages.flow.edit') }}</a>
        @endif
    </div>
    <dl class="xf-review__list">
        @foreach ($items as $label => $value)
            <div>
                <dt>{{ $label }}</dt>
                <dd>{{ filled($value) ? $value : '—' }}</dd>
            </div>
        @endforeach
    </dl>
</div>
