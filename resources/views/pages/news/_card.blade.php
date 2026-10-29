{{-- News card for grids; $item = News --}}
<a class="in-ncard" href="{{ route('news.show', ['locale' => $locale, 'slug' => $item->slug]) }}">
    @include('pages.news._thumb', ['item' => $item])
    <span class="in-ncard__body">
        <time class="in-meta" datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->format('d.m.Y') }}</time>
        <b>{{ $item->title($locale) }}</b>
    </span>
</a>
