<div id="{{ $cardId ?? 'card-info' }}" class="card">
    <div class="card-header">
        <h4 class="card-title">{{ $title }}</h4>
    </div>
    <div class="card-body">
        {!! $body !!}
    </div>
    @if (isset($footer))
        <div class="card-footer {{ $footerClass ?? '' }}">
            {{ $footer }}
        </div>
    @endif
</div>
