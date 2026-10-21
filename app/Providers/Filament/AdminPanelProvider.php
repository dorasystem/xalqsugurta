<?php

namespace App\Providers\Filament;

use App\Http\Middleware\SetAdminLocale;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use App\Filament\Admin\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()

            // ─── Brand (same look as xalqsugurta.uz) ──────────────────────────
            ->brandName('Xalq Sug\'urta')
            ->brandLogo(asset('assets/img/logo.svg'))
            ->brandLogoHeight('2.4rem')
            ->favicon(asset('assets/favicon-32x32.png'))
            ->colors([
                // Brand palette with #393185 as the 600 shade (buttons, links)
                'primary' => [
                    50  => '#f3f2fb',
                    100 => '#e8e6f6',
                    200 => '#d2cfee',
                    300 => '#b3ade2',
                    400 => '#8d85d1',
                    500 => '#6258b8',
                    600 => '#393185',
                    700 => '#2f2870',
                    800 => '#262059',
                    900 => '#1e1947',
                    950 => '#13102e',
                ],
                'gray'    => Color::Slate,
                'info'    => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger'  => Color::Rose,
            ])
            ->font('Inter', provider: LocalFontProvider::class)
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(
                    '<link rel="stylesheet" href="' . assetVersioned('assets/css/admin.css') . '">' .
                    '<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">'
                ),
            )

            // ─── Layout ───────────────────────────────────────────────────────
            // SPA: pages swap without a full reload (no custom <script> in the admin views)
            ->spa()
            ->unsavedChangesAlerts()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->sidebarCollapsibleOnDesktop()
            ->maxContentWidth(Width::Full)
            ->navigationGroups([
                NavigationGroup::make('Savdo'),
                NavigationGroup::make('Nazorat'),
                NavigationGroup::make('Katalog'),
                NavigationGroup::make('Tizim'),
            ])
            ->navigationItems([
                NavigationItem::make('Saytni ochish')
                    ->url(fn (): string => route('home', ['locale' => 'uz']), shouldOpenInNewTab: true)
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->sort(100),
            ])

            // ─── Content ──────────────────────────────────────────────────────
            ->discoverResources(in: app_path('Filament/Admin/Resources'), for: 'App\Filament\Admin\Resources')
            ->discoverPages(in: app_path('Filament/Admin/Pages'), for: 'App\Filament\Admin\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Admin/Widgets'), for: 'App\Filament\Admin\Widgets')
            ->widgets([])

            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // Persistent so Livewire updates (table search, filters, widgets) stay in Uzbek too
            ->middleware([
                SetAdminLocale::class,
            ], isPersistent: true)
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
