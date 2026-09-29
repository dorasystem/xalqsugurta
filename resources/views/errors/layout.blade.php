{{--
    Branded error page. Stands alone (no layouts.app, no database): it must render when the
    database or a service is down. Locale from the URL, since a 404 never reaches SetLocale.
--}}
@php
    $code   = (string) ($code ?? 500);
    $locale = in_array(request()->segment(1), ['uz', 'ru', 'en'], true) ? request()->segment(1) : 'uz';
    $t      = fn (string $key) => trans('messages.error_page.' . $key, [], $locale);
    $home   = '/' . $locale;
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $code }} · {{ $t($code . '.title') }} · Xalq Sug‘urta</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/assets/favicon-32x32.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Unbounded:wght@800&family=Inter:wght@400;600;800&display=swap">
    <style>
        :root { --ink: #16123f; --brand: #393185; --accent: #f2b632; --soft: #cfcaf5; }
        * { box-sizing: border-box; }
        html, body { margin: 0; min-height: 100%; }
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: var(--ink);
            color: #fff;
            font-family: Inter, system-ui, sans-serif;
        }
        .bg { position: fixed; inset: 0; overflow: hidden; pointer-events: none; }
        .ring { position: absolute; border-radius: 50%; pointer-events: none; }
        .ring--1 { right: -220px; top: -180px; width: 820px; height: 820px; border: 1px solid rgba(255, 255, 255, 0.07); }
        .ring--2 { right: 40px; top: 60px; width: 420px; height: 420px; border: 2px solid var(--accent); opacity: 0.3; }
        .blob { position: absolute; left: -220px; bottom: -280px; width: 620px; height: 620px; border-radius: 50%; background: var(--brand); opacity: 0.5; }
        header, main, footer { position: relative; width: 100%; max-width: 1200px; margin: 0 auto; padding: 0 24px; }
        header { padding-top: 28px; }
        header a { display: inline-flex; align-items: center; }
        header img { height: 40px; }
        main { flex: 1; display: flex; flex-direction: column; justify-content: center; gap: 22px; padding-top: 48px; padding-bottom: 48px; }
        .code { font: 800 clamp(5rem, 18vw, 11rem)/0.9 Unbounded, sans-serif; letter-spacing: -0.04em; color: var(--accent); }
        h1 { margin: 0; font: 800 clamp(1.8rem, 4.5vw, 3.2rem)/1.1 Unbounded, sans-serif; letter-spacing: -0.02em; }
        p { margin: 0; max-width: 560px; font-size: 1.15rem; line-height: 1.6; color: var(--soft); }
        .actions { display: flex; flex-wrap: wrap; gap: 12px; margin-top: 8px; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 56px; padding: 0 26px; border-radius: 16px; font: 800 1rem/1 Inter, sans-serif; text-decoration: none; border: 1.5px solid transparent; }
        .btn--accent { background: var(--accent); color: var(--ink); }
        .btn--ghost { border-color: rgba(255, 255, 255, 0.35); color: #fff; background: transparent; cursor: pointer; }
        .btn:hover { transform: translateY(-1px); }
        .btn:focus-visible { outline: 3px solid var(--accent); outline-offset: 3px; }
        footer { padding-bottom: 28px; font-size: 0.95rem; color: var(--soft); }
        footer a { color: #fff; font-weight: 800; text-decoration: none; }
        @media (max-width: 600px) {
            .ring--2 { display: none; }
            .btn { flex: 1 1 100%; }
        }
    </style>
</head>
<body>
    <div class="bg" aria-hidden="true">
        <span class="ring ring--1"></span>
        <span class="ring ring--2"></span>
        <span class="blob"></span>
    </div>

    <header>
        <a href="{{ $home }}" aria-label="Xalq Sug‘urta"><img src="/assets/img/logo-footer.svg" alt="Xalq Sug‘urta"></a>
    </header>

    <main>
        <span class="code" aria-hidden="true">{{ $code }}</span>
        <h1>{{ $t($code . '.title') }}</h1>
        <p>{{ $t($code . '.text') }}</p>
        <div class="actions">
            <a href="{{ $home }}" class="btn btn--accent">{{ $t('home') }}</a>
            @if ($code !== '503')
                <button type="button" class="btn btn--ghost" onclick="history.length > 1 ? history.back() : location.assign('{{ $home }}')">{{ $t('back') }}</button>
            @endif
        </div>
    </main>

    <footer>
        {{ $t('help') }}: <a href="tel:+998712021966">(+998 71) 202-19-66</a>
    </footer>
</body>
</html>
