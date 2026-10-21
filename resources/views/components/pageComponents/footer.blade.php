        <footer class="footer">
            <div class="footer__wrapper">
                <div class="footer-top">
                    <div class="footer-column">
                        <a href="{{ route('home', ['locale' => getCurrentLocale()]) }}" class="header-logo" aria-label="Логотип">
                            <img src="{{ asset('assets/img/logo-footer.svg') }}" alt="logo">
                        </a>

                        <ul class="footer-menu">
                            <li class="footer-menu__item"> {{-- Kompaniya haqida --}}
                                <a href="{{ \App\Models\InfoPage::link('about') }}" class="footer-menu__link">{{ __('messages.about_company') }}</a>
                            </li>
                            <li class="footer-menu__item">{{-- Aktsiyadorlari va investorlari --}}
                                <a href="{{ \App\Models\InfoPage::link('affiliates') }}" class="footer-menu__link">{{ __('messages.shareholders') }}</a>
                            </li>
                            <li class="footer-menu__item">{{-- Sug'urta --}}
                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/{{ __('routes.insurance') }}" class="footer-menu__link">{{ __('messages.insurance') }}</a>
                            </li>
                            <li class="footer-menu__item">{{-- Matbuot - markazi --}}
                                <a href="https://xalqsugurta.uz/{{ getCurrentLocale() }}/{{ __('routes.news') }}" class="footer-menu__link">{{ __('messages.press_center') }}</a>
                            </li>
                            <li class="footer-menu__item">
                                <a href="{{ route('my-policies', ['locale' => getCurrentLocale()]) }}" class="footer-menu__link">{{ __t('messages.my_policies.title') }}</a>
                            </li>
                            <li class="footer-menu__item">
                                <a href="{{ route('claims.create', ['locale' => getCurrentLocale()]) }}" class="footer-menu__link">{{ __t('messages.claims.title') }}</a>
                            </li>
                            <li class="footer-menu__item">
                                <a href="{{ route('claims.status', ['locale' => getCurrentLocale()]) }}" class="footer-menu__link">{{ __t('messages.claims.check_status') }}</a>
                            </li>
                            <li class="footer-menu__item">
                                <a href="{{ route('callback', ['locale' => getCurrentLocale()]) }}" class="footer-menu__link">{{ __t('messages.callback.title') }}</a>
                            </li>
                            <li class="footer-menu__item">{{-- Foydali ma'lumotlar --}}
                                <a href="{{ \App\Models\InfoPage::link('useful-information') }}" class="footer-menu__link">{{ __('messages.useful_info') }}</a>
                            </li>
                        </ul>
                    </div>
                    <div class="footer-column">


                        <ul class="footer-contacts">
                            <li class="footer-contacts__block">
                                <h3 class="footer-contacts__title">{{ __('messages.footer_phone') }}:</h3>
                                <a href="tel:+998712021966" class="footer-contacts__text">(+998 71)
                                    202-19-66</a>
                            </li>
                            <li class="footer-contacts__block">
                                <h3 class="footer-contacts__title">{{ __('messages.footer_address') }}:</h3>
                                <p class="footer-contacts__text">{{ __('messages.company_address') }}</p>
                            </li>
                            <li class="footer-contacts__block">
                                <h3 class="footer-contacts__title">{{ __('messages.footer_email') }}:</h3>
                                <a href="mailto:info@xalqsugurta.uz"
                                    class="footer-contacts__text">info@xalqsugurta.uz</a>
                            </li>
                            <li class="footer-contacts__block">
                                <h3 class="footer-contacts__title">{{ __('messages.work_schedule') }}:</h3>
                                <p class="footer-contacts__text">{{ __('messages.work_schedule_time') }}</p>
                            </li>
                        </ul>
                    </div>
                    <div class="footer-column">
                        <div class="subscription">
                            <h3 class="subscription__title">{{ __('messages.subscription') }}</h3>
                            <span class="subscription__description">{{ __('messages.subscribe_newsletter') }}</span>
                            <form class="subscription-form" method="POST" action="{{ route('newsletter.store', ['locale' => getCurrentLocale()]) }}">
                                @csrf
                                <input type="text" name="website" value="" tabindex="-1" autocomplete="off" aria-hidden="true" style="position: absolute; left: -9999px">
                                <input class="subscription-form__input" type="email" name="email" required maxlength="150"
                                    value="{{ old('email') }}" placeholder="Email" aria-label="Email" autocomplete="email">
                                <button class="subscription-form__button" type="submit" aria-label="{{ __('messages.subscription') }}">
                                    <svg width="20" height="20">
                                        <use xlink:href="#icon-down"></use>
                                    </svg>
                                </button>
                            </form>
                            @error('email', 'newsletter')
                                <span class="subscription__description" role="alert" style="color: #ffb4b4">{{ $message }}</span>
                            @enderror
                        </div>
                        <span class="footer-description">{{ __('messages.site_materials_notice') }}</span>
                    </div>
                </div>
                <div class="footer-bottom">
                    <span class="footer-copyright">© {{ date('Y') }} XALQ SUG‘URTA</span>
                    @include('components.pageComponents.dora-credit')
                </div>
            </div>
        </footer>
        </div>

        <!-- scripts -->
        <script src="{{ asset('assets/vendors/choices.js') }}"></script>
        <script src="{{ asset('assets/js/main.min.js') }}"></script>

        <script>
            window.replainSettings = {
                id: '01365ccb-704d-4cba-8311-208381bb92a1'
            };
            (function(u) {
                var s = document.createElement('script');
                s.async = true;
                s.src = u;
                var x = document.getElementsByTagName('script')[0];
                x.parentNode.insertBefore(s, x);
            })('https://widget.replain.cc/dist/client.js');
        </script>
