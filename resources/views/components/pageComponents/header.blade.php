        @php
            $siteSection = \App\Support\SiteMenu::current();
            $siteMenu    = \App\Support\SiteMenu::groups($siteSection);
        @endphp
        <!-- header -->
        <header class="header">
            <div class="header__wrapper">

                {{-- Site sections: individuals / business / career, each with its own menu below --}}
                <nav class="site-sections" aria-label="{{ __t('messages.sections.label') }}">
                    <div class="site-sections__inner">
                        @foreach (\App\Support\SiteMenu::SECTIONS as $key => [$routeName, $labelKey])
                            <a href="{{ \App\Support\SiteMenu::sectionUrl($key) }}" @class(['site-sections__link', 'is-active' => $key === $siteSection]) @if ($key === $siteSection) aria-current="true" @endif>{{ __t($labelKey) }}</a>
                        @endforeach
                    </div>
                </nav>

                <div class="header-top">
                    <div class="header-top__left">
                        <button class="burger mob-hidden" type="button" aria-label="Открыть меню" data-open="menu">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                        <a href="{{ \App\Models\InfoPage::link('branches') }}" class="header-button mob-hidden" type="button">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-pin"></use>
                            </svg>
                            <span>{{ __('messages.header_branches') }}</span>
                        </a>
                        <a href="tel:998555007171" class="header-button mob-hidden" type="button">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-phone"></use>
                            </svg>
                            <span>(+998 55) 500-71-71</span>
                        </a>
                        <a href="tel:998712021966" class="header-button mob-hidden" type="button">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-phone"></use>
                            </svg>
                            <span>(+998 71) 202-19-66</span>
                        </a>
                        <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.questionnaire')" class="header-button mob-hidden" type="button">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-faq"></use>
                            </svg>
                            <span>{{ __('messages.header_survey_form') }}</span>
                        </a>
                        <details class="opportunities">
                            <summary class="opportunities-opener" aria-label="Специальные возможности">
                                <svg width="20" height="20" aria-hidden="true">
                                    <use xlink:href="#icon-eye"></use>
                                </svg>
                                <span>{{ __('messages.header_special_features') }}</span>
                            </summary>
                            <div class="opportunities-list">
                                <button class="opportunities-item" type="button" data-vision="off">
                                    <span class="switch"></span>
                                    <span class="opportunities-text">{{ __('messages.header_accessibility_version') }}</span>
                                </button>
                                <button class="opportunities-item" type="button" data-voice="off"
                                    data-voice-lang="ru_RU">
                                    <!-- ru_RU / en_GB / uz_UZ -->
                                    <span class="switch"></span>
                                    <span class="opportunities-text">{{ __('messages.header_audio_support') }}</span>
                                </button>
                            </div>
                        </details>
                    </div>
                    <div class="header-top__right">
                        <div class="site-search">
                            <button class="search-open" type="button" aria-label="{{ __t('messages.site_search.open') }}"
                                aria-expanded="false" aria-controls="site_search">
                                <svg width="20" height="20">
                                    <use xlink:href="#icon-search"></use>
                                </svg>
                            </button>
                            <form class="site-search__form" id="site_search" role="search" method="GET"
                                action="{{ route('search', ['locale' => getCurrentLocale()]) }}" hidden>
                                <input type="search" name="q" required minlength="2" maxlength="100" autocomplete="off"
                                    placeholder="{{ __t('messages.site_search.placeholder') }}" aria-label="{{ __t('messages.site_search.title') }}">
                                <button type="submit" aria-label="{{ __t('messages.site_search.button') }}">
                                    <svg width="18" height="18" aria-hidden="true">
                                        <use xlink:href="#icon-search"></use>
                                    </svg>
                                </button>
                            </form>
                        </div>
                        <details class="lang">
                            <summary class="lang-opener" aria-label="{{ __('messages.select_language') }}">
                                <span>{{ app()->getLocale() }}</span>
                                <svg width="20" height="20">
                                    <use xlink:href="#icon-more"></use>
                                </svg>
                            </summary>
                            <ul class="lang-list">
                                @foreach ($localeService->getAvailableLocales() as $locale)
                                    @if ($locale !== $localeService->getCurrentLocale())
                                        <li class="lang-list__item">
                                            <a class="lang-list__link"
                                                href="{{ $localeService->getLocalizedUrl($locale) }}">
                                                <span>{{ $locale }}</span>
                                            </a>
                                        </li>
                                    @endif
                                @endforeach
                            </ul>
                        </details>
                        <a class="btn" href="https://xalqsugurta.uz/uploads/files/xalq.pdf">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-pdf-file"></use>
                            </svg>
                            <span>{{ __('messages.header_euro_protocol') }}</span>
                        </a>
                        <button class="burger" type="button" aria-label="Открыть меню" data-open="menu">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    </div>
                </div>

                <div class="header-bottom">
                    <a href="{{ route('home', ['locale' => getCurrentLocale()]) }}" class="header-logo" aria-label="Логотип">
                        <picture>
                            <source media="(max-width:800px)" srcset="{{ asset('assets/img/logo-mobile.png') }}">
                            <img src="{{ asset('assets/img/logo.svg') }}" alt="logo">
                        </picture>
                    </a>
                    <nav class="menu-header" aria-label="{{ __t('messages.sections.' . $siteSection) }}">
                        <ul class="menu-header__list">
                            @foreach ($siteMenu as $group)
                                <li class="menu-header__item menu-item">
                                    <a class="menu-item__link menu-item-title" href="{{ $group['url'] }}">
                                        <span>{{ $group['label'] }}</span>
                                        @if ($group['items'])
                                            <svg width="20" height="20">
                                                <use xlink:href="#icon-more"></use>
                                            </svg>
                                        @endif
                                    </a>
                                    @if ($group['items'])
                                        <div class="menu-item__dropdown">
                                            <div class="menu-item-dropdown__content">
                                                <ul class="menu-item__list">
                                                    @foreach ($group['items'] as $item)
                                                        <li class="menu-item__row">
                                                            <a href="{{ $item['url'] }}" class="menu-item__link">
                                                                <span>{{ $item['label'] }}</span>
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                    <div class="header-bottom__buttons">
                        <a class="btn"
                            href="http://online.xalqsugurta.uz/xs/ins/r/e-osgo/%D0%B0%D1%80%D0%B8%D0%B7%D0%B0?session=15469626418195">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-polis"></use>
                            </svg>
                            <span>E-POLIS</span>
                        </a>
                        <a class="btn" href="{{ route('my-policies', ['locale' => getCurrentLocale()]) }}">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-user"></use>
                            </svg>
                            <span>{{ __('messages.header_personal_account') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </header>
        <div class="menu" data-modal="menu">
            <button class="menu__close" type="button" data-close="menu">
                <svg width="20" height="20">
                    <use xlink:href="#icon-cancel"></use>
                </svg>
            </button>

            <div class="menu__columnL">
                <nav class="site-sections site-sections--menu" aria-label="{{ __t('messages.sections.label') }}">
                    @foreach (\App\Support\SiteMenu::SECTIONS as $key => [$routeName, $labelKey])
                        <a href="{{ \App\Support\SiteMenu::sectionUrl($key) }}" @class(['site-sections__link', 'is-active' => $key === $siteSection]) @if ($key === $siteSection) aria-current="true" @endif>{{ __t($labelKey) }}</a>
                    @endforeach
                </nav>
                <div class="menu-content">
                    @foreach ($siteMenu as $group)
                        <div class="menu-block">
                            <a class="menu-block__title" href="{{ $group['url'] }}">{{ $group['label'] }}</a>
                            @if ($group['items'])
                                <ul class="menu-block__list">
                                    @foreach ($group['items'] as $item)
                                        <li class="menu-block__item">
                                            <a class="menu-block__link" href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                    @endforeach
                </div>
                <div class="menu__footer">
                    <div class="social">
                        @foreach (\App\Services\SiteSettings::social() as $network => $url)
                            <a href="{{ $url }}" class="social__item" target="_blank" rel="noopener" aria-label="{{ \App\Services\SiteSettings::SOCIAL[$network] }}">
                                <svg width="20" height="20">
                                    <use xlink:href="#icon-{{ $network }}"></use>
                                </svg>
                            </a>
                        @endforeach
                    </div>
                    @include('components.pageComponents.dora-credit')
                </div>
            </div>
            <div class="menu__columnR menu-bar">
                <div class="menu-bar__block">
                    <span class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-user"></use>
                        </svg>
                        <span>{{ __('messages.header_personal_account') }}</span>
                    </span>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <a href="{{ route('my-policies', ['locale' => getCurrentLocale()]) }}" class="menu-bar__link">{{ __t('messages.my_policies.title') }}</a>
                        </li>
                        <li class="menu-bar__line">
                            <a href="{{ route('claims.create', ['locale' => getCurrentLocale()]) }}" class="menu-bar__link">{{ __t('messages.claims.title') }}</a>
                        </li>
                        <li class="menu-bar__line">
                            <a href="{{ route('callback', ['locale' => getCurrentLocale()]) }}" class="menu-bar__link">{{ __t('messages.callback.title') }}</a>
                        </li>
                    </ul>
                </div>
                <div class="menu-bar__block">
                    <div class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-phone"></use>
                        </svg>
                        <h4>{{ __('messages.support_service') }}</h4>
                    </div>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <a href="tel:(+998 71) 202-19-66" class="menu-bar__link">(+998 71) 202-19-66</a>
                        </li>
                    </ul>
                </div>
                <div class="menu-bar__block">
                    <div class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-pin"></use>
                        </svg>
                        <h4> {{ __('messages.address') }}:</h4>
                    </div>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <span class="menu-bar__link">{{ __('messages.company_address') }}</span>
                        </li>
                    </ul>
                </div>
                <div class="menu-bar__block">
                    <div class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-email-02"></use>
                        </svg>
                        <h4>{{ __('messages.footer_email') }}:</h4>
                    </div>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <a href="mailto:info@xalqsugurta.uz" class="menu-bar__link">info@xalqsugurta.uz</a>
                        </li>
                    </ul>
                </div>
                <div class="menu-bar__block">
                    <div class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-clock"></use>
                        </svg>
                        <h4>{{ __('messages.work_schedule') }}:</h4>
                    </div>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <span class="menu-bar__link">{{ __('messages.work_schedule_time') }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

<script>
    // Header search: the button opens a small form; Esc or a click outside closes it
    (function () {
        var box = document.querySelector('.site-search');
        if (!box) return;
        var button = box.querySelector('.search-open');
        var form = box.querySelector('.site-search__form');

        function toggle(open) {
            form.hidden = !open;
            button.setAttribute('aria-expanded', open ? 'true' : 'false');
            if (open) form.querySelector('input').focus();
        }

        button.addEventListener('click', function () { toggle(form.hidden); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !form.hidden) { toggle(false); button.focus(); } });
        document.addEventListener('click', function (e) { if (!form.hidden && !box.contains(e.target)) toggle(false); });
    })();
</script>
