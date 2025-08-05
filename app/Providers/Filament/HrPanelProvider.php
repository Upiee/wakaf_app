<?php

namespace App\Providers\Filament;

use Filament\Pages;
use Filament\Panel;
use Filament\Widgets;
use Filament\PanelProvider;
use App\Http\Middleware\onlyHr;
use Filament\Support\Colors\Color;
use Illuminate\Support\Facades\Route;
use Filament\Navigation\NavigationItem;
use Filament\Navigation\NavigationGroup;
use Filament\Http\Middleware\Authenticate;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Filament\Http\Middleware\AuthenticateSession;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;

class HrPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('hr')
            ->path('hr')
            ->login()
            ->brandLogo('https://r2.wakafsalman.or.id/logo_baru_ws.jpg')
            ->brandLogoHeight('50px')
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/Hr/Resources'), for: 'App\\Filament\\Hr\\Resources')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Hr/Pages'), for: 'App\\Filament\\Hr\\Pages')
            ->pages([
                \App\Filament\Hr\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Hr/Widgets'), for: 'App\\Filament\\Hr\\Widgets')
            ->widgets([
                \App\Filament\Hr\Widgets\StatsOverview::class,
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
            ])
            ->navigationGroups([
                'Dashboard',
                'Master Data',
                'Strategic Planning',
                'Manajemen KPI',
                'Realisasi',
                'Performance',
                'Settings'
            ])
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
            ->authMiddleware([
                Authenticate::class,
                onlyHr::class,
            ]);
    }
}
