{{-- One news card; $item = News --}}
<a class="xf-news__card" href="{{ route('news.show', ['locale' => $locale, 'slug' => $item->slug]) }}">
    <span class="xf-news__img">
        @if ($item->imageUrl())
            <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
        @else
            <i class="bi bi-newspaper" aria-hidden="true"></i>
        @endif
    </span>
    <span class="xf-news__body">
        <time datetime="{{ $item->published_at->toDateString() }}">{{ $item->published_at->format('d.m.Y') }}</time>
        <strong>{{ $item->title($locale) }}</strong>
        @if ($excerpt = $item->excerpt($locale))
            <small>{{ $excerpt }}</small>
        @endif
    </span>
</a>
