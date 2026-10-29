@props([
    'title',
    'lead'  => null,
    'crumb' => null,   // [label, url] of a middle breadcrumb, e.g. the list page on a detail page
])

{{-- Inner pages (design "A", inner.css): breadcrumbs, display title, lead; the slot goes to the right (filters, search) --}}
@once
    @push('head')
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Unbounded:wght@600;800&display=swap">
        <link rel="stylesheet" href="{{ assetVersioned('assets/css/inner.css') }}">
    @endpush
@endonce

<section class="in-wrap in-head">
    <div class="in-head__text">
        <nav class="in-crumbs" aria-label="breadcrumb">
            <a href="{{ route('home', ['locale' => getCurrentLocale()]) }}">{{ __t('messages.error_page.home') }}</a>
            @if ($crumb)
                <span aria-hidden="true">/</span>
                <a href="{{ $crumb[1] }}">{{ $crumb[0] }}</a>
            @endif
            <span aria-hidden="true">/</span>
            <span aria-current="page">{{ \Illuminate\Support\Str::limit($title, 60) }}</span>
        </nav>
        <h1 class="in-title">{{ $title }}</h1>
        @if ($lead)
            <p class="in-lead">{{ $lead }}</p>
        @endif
    </div>
    {{ $slot }}
</section>
