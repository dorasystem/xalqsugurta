        <!-- header -->
        <header class="header">
            <div class="header__wrapper">

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
                        <button class="search-open" type="button" aria-label="Открытие поиска">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-search"></use>
                            </svg>
                        </button>
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
                    <a href="/" class="header-logo" aria-label="Логотип">
                        <picture>
                            <source media="(max-width:800px)" srcset="{{ asset('assets/img/logo-mobile.png') }}">
                            <img src="{{ asset('assets/img/logo.svg') }}" alt="logo">
                        </picture>
                    </a>
                    <nav class="menu-header">
                        <ul class="menu-header__list">
                            <li class="menu-header__item menu-item"> {{-- Kompaniya Haqida --}}
                                <a class="menu-item__link menu-item-title" href="{{ \App\Models\InfoPage::link('about') }}">
                                    <span>{{ __('messages.header_about_company') }}</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">{{-- ////////////////////////////////////////////////////////////// --}}
                                                <a href="{{ \App\Models\InfoPage::link('management') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_management') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('licenses') }}" class="menu-item__link">
                                                    <span>{{ __('messages.header_licenses') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('financial-statements') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_financial_reports') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('audit') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_audit') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('business-plan') }}" class="menu-item__link">
                                                    <span>{{ __('messages.header_business_plan') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('vacancies') }}" class="menu-item__link">
                                                    <span>{{ __('messages.header_vacancies') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('collegial-bodies') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_collegial_bodies') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('subject-objectives') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_objectives') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('company-structure') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_company_structure') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('regulation') }}" class="menu-item__link">
                                                    <span>{{ __('messages.header_regulations') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.questionnaire')" class="menu-item__link">
                                                    <span>{{ __('messages.header_questionnaire') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </li>
                            <li class="menu-header__item menu-item">  {{-- Aktsiyadorlari va investorlari --}}
                                <a class="menu-item__link menu-item-title"
                                    href="{{ \App\Models\InfoPage::link('affiliates') }}">
                                    <span>{{ __('messages.header_shareholders') }}</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('affiliates') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_affiliated_persons') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('stocks') }}" class="menu-item__link">
                                                    <span>{{ __('messages.header_shares') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('dividend') }}" class="menu-item__link">
                                                    <span>{{ __('messages.header_dividends') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('essential-facts') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_important_facts') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </li>
                            <li class="menu-header__item menu-item"> {{-- Sug’urta --}}
                                <a class="menu-item__link menu-item-title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.insurance')">
                                    <span>{{ __('messages.header_insurance') }}</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.insurance')#private"
                                                    class="menu-item__link" data-scroll="private">
                                                    <span>{{ __('messages.header_private_clients') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.insurance')#corporate"
                                                    class="menu-item__link" data-scroll="corporate">
                                                    <span>{{ __('messages.header_corporate_clients') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </li>
                            <li class="menu-header__item menu-item"> {{-- Matbuot – markazi --}}
                                <a class="menu-item__link menu-item-title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.news')">
                                    <span>{{ __('messages.header_press_center') }}</span>

                                </a>

                            </li>
                            <li class="menu-header__item menu-item"> {{-- Foydali ma’lumotlar --}}
                                <a class="menu-item__link menu-item-title"
                                    href="{{ \App\Models\InfoPage::link('legislation') }}">
                                    <span>{{ __('messages.header_useful_info') }}</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('legislation') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_insurance_legislation') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('tax-benefits') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_tax_benefits_basis') }}</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="{{ \App\Models\InfoPage::link('useful-information') }}"
                                                    class="menu-item__link">
                                                    <span>{{ __('messages.header_insurance_terms') }}</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </li>
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
                <div class="menu-content">
                    <div class="menu-block"> {{-- Kompaniya Haqida --}}
                        <a class="menu-block__title" href="{{ \App\Models\InfoPage::link('about') }}" data-id="67e288002adb5">{{ __('messages.header_about_company') }}</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('management') }}">{{ __('messages.header_management') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('licenses') }}">{{ __('messages.header_licenses') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="{{ \App\Models\InfoPage::link('financial-statements') }}">{{ __('messages.header_financial_reports') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('audit') }}">{{ __('messages.header_audit') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('business-plan') }}">{{ __('messages.header_business_plan') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('vacancies') }}">{{ __('messages.header_vacancies') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="{{ \App\Models\InfoPage::link('collegial-bodies') }}">{{ __('messages.header_collegial_bodies') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="{{ \App\Models\InfoPage::link('subject-objectives') }}">{{ __('messages.header_objectives') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('company-structure') }}">{{ __('messages.header_company_structure') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('regulation') }}">{{ __('messages.header_regulations') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.questionnaire')">{{ __('messages.header_questionnaire') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block"> {{-- Aktsiyadorlari va investorlari --}}
                        <a class="menu-block__title" href="{{ \App\Models\InfoPage::link('affiliates') }}"
                            data-id="67e288002adc1">{{ __('messages.header_shareholders') }}</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="{{ \App\Models\InfoPage::link('affiliates') }}">{{ __('messages.header_affiliated_persons') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('stocks') }}">{{ __('messages.header_shares') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('dividend') }}">{{ __('messages.header_dividends') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('essential-facts') }}">{{ __('messages.header_important_facts') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block"> {{-- Sug’urta --}}
                        <a class="menu-block__title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.insurance')"
                            data-id="67e288002adc6">{{ __('messages.header_insurance') }}</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.insurance')#private">{{ __('messages.header_private_clients') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.insurance')#corporate">{{ __('messages.header_corporate_clients') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block"> {{-- Matbuot – markazi --}}
                        <a class="menu-block__title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/@lang('routes.news')"
                            data-id="67e288002adc9">{{ __('messages.header_press_center') }}</a>
                    </div>
                    <div class="menu-block"> {{-- Foydali ma’lumotlar --}}
                        <a class="menu-block__title"
                            href="{{ \App\Models\InfoPage::link('legislation') }}"
                            data-id="67e288002adca">{{ __('messages.header_useful_info') }}</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="{{ \App\Models\InfoPage::link('legislation') }}">{{ __('messages.header_insurance_legislation') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="{{ \App\Models\InfoPage::link('tax-benefits') }}">{{ __('messages.header_tax_benefits_basis') }}</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="{{ \App\Models\InfoPage::link('useful-information') }}">{{ __('messages.header_insurance_terms') }}</a>
                            </li>
                        </ul>
                    </div>
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
