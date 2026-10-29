{{-- News picture or a tinted placeholder; $item = News, $class = extra classes --}}
<span class="in-thumb {{ $class ?? '' }}">
    @if ($item->imageUrl())
        <img src="{{ $item->imageUrl() }}" alt="" loading="lazy">
    @else
        <i class="bi bi-newspaper" aria-hidden="true"></i>
    @endif
</span>
