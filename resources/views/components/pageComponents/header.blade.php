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
                        <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/filialy" class="header-button mob-hidden" type="button">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-pin"></use>
                            </svg>
                            <span>Филиалы</span>
                        </a>
                        <a href="tel:998712021966" class="header-button mob-hidden" type="button">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-phone"></use>
                            </svg>
                            <span>(+998 71) 202-19-66</span>
                        </a>
                        <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/oprosnik" class="header-button mob-hidden" type="button">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-faq"></use>
                            </svg>
                            <span>Опросник</span>
                        </a>
                        <details class="opportunities">
                            <summary class="opportunities-opener" aria-label="Специальные возможности">
                                <svg width="20" height="20" aria-hidden="true">
                                    <use xlink:href="#icon-eye"></use>
                                </svg>
                                <span>Спец.возможности</span>
                            </summary>
                            <div class="opportunities-list">
                                <button class="opportunities-item" type="button" data-vision="off">
                                    <span class="switch"></span>
                                    <span class="opportunities-text">Версия для слабовидящих</span>
                                </button>
                                <button class="opportunities-item" type="button" data-voice="off"
                                    data-voice-lang="ru_RU">
                                    <!-- ru_RU / en_GB / uz_UZ -->
                                    <span class="switch"></span>
                                    <span class="opportunities-text">Озвучивание</span>
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
                        <a class="btn" href="uploads/files/xalq.pdf">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-pdf-file"></use>
                            </svg>
                            <span>Европротокол</span>
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
                            <li class="menu-header__item menu-item">
                                <a class="menu-item__link menu-item-title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/about">
                                    <span>О компании</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/management"
                                                    class="menu-item__link">
                                                    <span>Руководство</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/licenses" class="menu-item__link">
                                                    <span>Лицензия и сертификаты</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/financial-statements"
                                                    class="menu-item__link">
                                                    <span>Финансовая отчетность</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/audit-zaklyuchenie"
                                                    class="menu-item__link">
                                                    <span>Аудит</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/biznes-plan" class="menu-item__link">
                                                    <span>Бизнес-план</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/vacancies" class="menu-item__link">
                                                    <span>Вакансии</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/kollegialnye-organy"
                                                    class="menu-item__link">
                                                    <span>Коллегиальные органы</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/predmet-i-celi-deyatelnosti-obshestva"
                                                    class="menu-item__link">
                                                    <span>Предмет и цели деятельности общества</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/struktura-kompanii"
                                                    class="menu-item__link">
                                                    <span>Структура компании</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/polozheniya" class="menu-item__link">
                                                    <span>Положения</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/oprosnik" class="menu-item__link">
                                                    <span>Опросник</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </li>
                            <li class="menu-header__item menu-item">
                                <a class="menu-item__link menu-item-title"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/affilirovannye-lica">
                                    <span>Акционерам и инвесторам</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/affilirovannye-lica"
                                                    class="menu-item__link">
                                                    <span>Аффилированные лица</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/akcii" class="menu-item__link">
                                                    <span>Акции</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/dividendy" class="menu-item__link">
                                                    <span>Дивиденды</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/essential-facts"
                                                    class="menu-item__link">
                                                    <span>Существенные факты</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </li>
                            <li class="menu-header__item menu-item">
                                <a class="menu-item__link menu-item-title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovanie">
                                    <span>Страхование</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovanie#private"
                                                    class="menu-item__link" data-scroll="private">
                                                    <span>Частным клиентам</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovanie#corporate"
                                                    class="menu-item__link" data-scroll="corporate">
                                                    <span>Корпоративным клиентам</span>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>

                            </li>
                            <li class="menu-header__item menu-item">
                                <a class="menu-item__link menu-item-title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/news">
                                    <span>Пресс-центр</span>

                                </a>

                            </li>
                            <li class="menu-header__item menu-item">
                                <a class="menu-item__link menu-item-title"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/zakonodatelstvo-v-sfere-strahovaniya">
                                    <span>Полезная информация</span>
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-more"></use>
                                    </svg>

                                </a>
                                <div class="menu-item__dropdown">
                                    <div class="menu-item-dropdown__content">
                                        <ul class="menu-item__list">
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/zakonodatelstvo-v-sfere-strahovaniya"
                                                    class="menu-item__link">
                                                    <span>Законодательство в сфере страхования</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/osnovanie-predostavleniya-nalogovyh-lgot"
                                                    class="menu-item__link">
                                                    <span>Основание предоставления налоговых льгот</span>
                                                </a>
                                            </li>
                                            <li class="menu-item__row">
                                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovye-terminy"
                                                    class="menu-item__link">
                                                    <span>Страховые термины</span>
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
                        <a class="btn" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/signin">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-user"></use>
                            </svg>
                            <span>Личный кабинет</span>
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
                    <div class="menu-block">
                        <a class="menu-block__title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/about" data-id="67e288002adb5">О
                            компании</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/management">Руководство</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/licenses">Лицензия и
                                    сертификаты</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/financial-statements">Финансовая отчетность</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/audit-zaklyuchenie">Аудит</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/biznes-plan">Бизнес-план</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/vacancies">Вакансии</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/kollegialnye-organy">Коллегиальные органы</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/predmet-i-celi-deyatelnosti-obshestva">Предмет
                                    и
                                    цели деятельности общества</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/struktura-kompanii">Структура
                                    компании</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/polozheniya">Положения</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/oprosnik">Опросник</a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block">
                        <a class="menu-block__title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/affilirovannye-lica"
                            data-id="67e288002adc1">Акционерам
                            и инвесторам</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/affilirovannye-lica">Аффилированные лица</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/akcii">Акции</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/dividendy">Дивиденды</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/essential-facts">Существенные
                                    факты</a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block">
                        <a class="menu-block__title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovanie"
                            data-id="67e288002adc6">Страхование</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovanie#private">Частным
                                    клиентам</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovanie#corporate">Корпоративным
                                    клиентам</a>
                            </li>
                        </ul>
                    </div>
                    <div class="menu-block">
                        <a class="menu-block__title" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/news"
                            data-id="67e288002adc9">Пресс-центр</a>
                    </div>
                    <div class="menu-block">
                        <a class="menu-block__title"
                            href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/zakonodatelstvo-v-sfere-strahovaniya"
                            data-id="67e288002adca">Полезная информация</a>
                        <ul class="menu-block__list">
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/zakonodatelstvo-v-sfere-strahovaniya">Законодательство
                                    в сфере
                                    страхования</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link"
                                    href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/osnovanie-predostavleniya-nalogovyh-lgot">Основание
                                    предоставления
                                    налоговых льгот</a>
                            </li>
                            <li class="menu-block__item">
                                <a class="menu-block__link" href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/strahovye-terminy">Страховые
                                    термины</a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="menu__footer">
                    <div class="social">
                        <a href="#" class="social__item" target="_blank" aria-label="Instagram">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-instagram"></use>
                            </svg>
                        </a>
                        <a href="#" class="social__item" target="_blank" aria-label="facebook">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-facebook"></use>
                            </svg>
                        </a>
                        <a href="#" class="social__item" target="_blank" aria-label="telegram">
                            <svg width="20" height="20">
                                <use xlink:href="#icon-telegram"></use>
                            </svg>
                        </a>
                    </div>
                    <a class="copyright-logo" href="https://alex-software.ru/" target="_blank"
                        rel="nofollow noopener" aria-label="Разработано Alex software">
                        <svg viewbox="0 0 595.4 365.7" width="50">
                            <path class="st0"
                                d="M273.3,235.6H7.2L140.2,6L273.3,235.6 M60.3,205c51.1,0,108.9,0,159.9,0c-25.6-44.2-54.5-94-80-138 C114.8,111,85.9,160.9,60.3,205">
                            </path>
                            <path class="st st1" d="M275,193.1V59.3h25.2V168h50.1v25.1H275L275,193.1z"></path>
                            <path class="st st2"
                                d="M373.2,193.1V59.3h83.9v25.1h-58.6v28.3h57.4v25.1h-57.4v30.3h58.6v25.1h-83.9V193.1z">
                            </path>
                            <path class="st st3"
                                d="M563.5,193.1l-29.7-49l-29.9,49h-30l43-68.6l-40.3-65.2h30l27.2,45.7l26.8-45.7H591l-40.3,65l43,68.8 L563.5,193.1L563.5,193.1z">
                            </path>
                            <path class="st0 st01"
                                d="M0,356.9l5.4-7.5c3.8,4.1,9.8,7.7,17.6,7.7c8,0,11.2-3.9,11.2-7.6c0-11.7-32.4-4.4-32.4-24.9 c0-9.2,8.1-16.2,20.2-16.2c8.7,0,15.6,2.7,20.8,7.7l-5.6,7.2C32.9,319,27,317,21.1,317c-5.7,0-9.4,2.7-9.4,6.9 c0,10.3,32.3,3.9,32.3,24.6c0,9.2-6.5,17.1-21.5,17.1C12.3,365.7,4.9,362.2,0,356.9z">
                            </path>
                            <path class="st0 st01"
                                d="M73.7,337c0-16.6,11.7-28.7,28.4-28.7c16.6,0,28.4,12.2,28.4,28.7c0,16.6-11.7,28.7-28.4,28.7 C85.5,365.7,73.7,353.6,73.7,337z M120.5,337c0-11.5-7.2-20.1-18.4-20.1s-18.4,8.6-18.4,20.1c0,11.4,7.2,20.1,18.4,20.1 S120.5,348.4,120.5,337z">
                            </path>
                            <path class="st0 st01"
                                d="M163.1,364.7v-55.5h38v8.6h-28.3v14.4h27.7v8.6h-27.7v24h-9.7V364.7z"></path>
                            <path class="st0 st01" d="M246.3,364.7v-46.9h-16.8v-8.6h43.4v8.6h-16.8v46.9H246.3z"></path>
                            <path class="st st1"
                                d="M346.8,364.7L336,323.8l-10.7,40.9h-10.4L299,309.2h10.9l10.8,42.8l11.5-42.8h7.7l11.5,42.8l10.7-42.8H373 l-15.8,55.5L346.8,364.7L346.8,364.7z">
                            </path>
                            <path class="st st3"
                                d="M513.6,364.7L501.3,344h-9.7v20.7h-9.7v-55.5h24.4c11,0,18.1,7.2,18.1,17.4c0,9.9-6.5,15.2-13.1,16.3 l13.6,21.8L513.6,364.7L513.6,364.7z M514.5,326.6c0-5.3-4-8.8-9.5-8.8h-13.3v17.6H505C510.5,335.4,514.5,331.9,514.5,326.6z">
                            </path>
                            <path class="st st3"
                                d="M557.4,364.7v-55.5h38v8.6h-28.3v14.4h27.7v8.6h-27.7v15.4h28.3v8.6h-38V364.7z"></path>
                            <path class="st st2"
                                d="M451.3,364.7h-52.6c-1.8,0-3.4-1-4.3-2.5c-0.9-1.5-0.9-3.4,0-5l13.1-22.8l13.2-22.8c0.9-1.5,2.5-2.5,4.3-2.5 s3.4,1,4.3,2.5l13.2,22.8c0,0,0,0,0,0l13.1,22.8c0.9,1.5,0.9,3.5,0,5C454.7,363.7,453.1,364.7,451.3,364.7z M407.3,354.7h35.3 l-8.8-15.3l-8.9-15.3l-8.9,15.3L407.3,354.7z">
                            </path>
                        </svg>
                    </a>
                </div>
            </div>
            <div class="menu__columnR menu-bar">
                <div class="menu-bar__block">
                    <span class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-user"></use>
                        </svg>
                        <span>Личный кабинет</span>
                    </span>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/signin" class="menu-bar__link">Войти</a>
                        </li>
                        <li class="menu-bar__line">
                            <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/signup" class="menu-bar__link">Зарегистрироваться</a>
                        </li>
                    </ul>
                </div>
                <div class="menu-bar__block">
                    <div class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-phone"></use>
                        </svg>
                        <h4>Служба поддержки</h4>
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
                        <h4> Адрес:</h4>
                    </div>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <a href="#" class="menu-bar__link">г. Ташкент, Мирзо-Улугбекский район, СГМ
                                Лашкарбеги, ул.
                                Хамид Алимджана, 13 А</a>
                        </li>
                    </ul>
                </div>
                <div class="menu-bar__block">
                    <div class="menu-bar__title">
                        <svg width="20" height="20">
                            <use xlink:href="#icon-email-02"></use>
                        </svg>
                        <h4>Email:</h4>
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
                        <h4>График работы:</h4>
                    </div>
                    <ul class="menu-bar__list">
                        <li class="menu-bar__line">
                            <a href="#" class="menu-bar__link">Пн - Пт с 09:00 до 18:00</a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
