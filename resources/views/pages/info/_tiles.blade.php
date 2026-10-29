{{-- Pages of one section as tiles; $pages, $active = the page shown (or null) --}}
<div class="in-tiles-grid">
    @foreach ($pages as $item)
        <a @class(['in-ptile', 'is-active' => $active?->is($item)]) href="{{ route('info.show', ['locale' => $locale, 'key' => $item->key]) }}#page"
            @if ($active?->is($item)) aria-current="page" @endif>
            <span class="in-ptile__icon"><i class="bi {{ $item->icon() }}" aria-hidden="true"></i></span>
            <b>{{ $item->title($locale) }}</b>
            <span class="in-link">{{ __t('messages.info_page.open') }} →</span>
        </a>
    @endforeach
</div>
