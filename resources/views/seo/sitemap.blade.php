{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($pages as [$name, $params, $freq, $priority])
@foreach ($locales as $locale)
    <url>
        <loc>{{ route($name, ['locale' => $locale] + $params) }}</loc>
@foreach ($locales as $alt)
        <xhtml:link rel="alternate" hreflang="{{ $alt }}" href="{{ route($name, ['locale' => $alt] + $params) }}"/>
@endforeach
        <changefreq>{{ $freq }}</changefreq>
        <priority>{{ $priority }}</priority>
    </url>
@endforeach
@endforeach
</urlset>
