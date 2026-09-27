@props([
    'icon'     => 'bi-shield-check',
    'title',
    'subtitle' => '',
    'steps'    => [],
    'current'  => 1,
    'stepUrls' => [],
])

<link rel="stylesheet" href="{{ assetVersioned('assets/css/flow.css') }}">

<section class="xf">
    <div class="xf__wrapper">

        <div class="xf__head">
            <span class="xf__head-icon"><i class="bi {{ $icon }}"></i></span>
            <div>
                <h1 class="xf__title">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="xf__subtitle">{{ $subtitle }}</p>
                @endif
            </div>
            <a href="{{ route('home', ['locale' => getCurrentLocale()]) }}" class="xf__home">
                <i class="bi bi-arrow-left"></i> {{ __t('messages.flow.all_products') }}
            </a>
        </div>

        <x-insurence.stepper :steps="$steps" :current="$current" :urls="$stepUrls" />

        <div class="xf__grid">
            <div class="xf__main">
                @if ($errors->has('error'))
                    <div class="xf-alert" role="alert">
                        <i class="bi bi-exclamation-circle"></i>
                        <span>{{ $errors->first('error') }}</span>
                    </div>
                @endif

                {{ $slot }}
            </div>

            @isset($summary)
                {{ $summary }}
            @endisset
        </div>

    </div>
</section>
