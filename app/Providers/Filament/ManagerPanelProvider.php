<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ManagerPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('manager')
            ->path('manager')
            ->login()
            ->brandLogo('https://r2.wakafsalman.or.id/logo_baru_ws.jpg')
            ->brandLogoHeight('50px')
            ->colors([
                'primary' => Color::Red,
            ])
            ->brandName('Wakaf KPI - Manager Panel')
            ->resources([
                \App\Filament\Manager\Resources\KpiManagementResource::class,
                \App\Filament\Manager\Resources\OkrManagementResource::class,
                \App\Filament\Manager\Resources\MyKpiProgressResource::class,
                \App\Filament\Manager\Resources\MyOkrProgressResource::class,
                \App\Filament\Manager\Resources\ManajemenTimResource::class,
                \App\Filament\Manager\Resources\EmployeeKpiApprovalResource::class,
                \App\Filament\Manager\Resources\EmployeeOkrApprovalResource::class,
            ])
            ->discoverResources(in: app_path('Filament/Manager/Resources'), for: 'App\\Filament\\Manager\\Resources')
            ->discoverPages(in: app_path('Filament/Manager/Pages'), for: 'App\\Filament\\Manager\\Pages')
            ->pages([
                \App\Filament\Manager\Pages\Dashboard::class,
            ])
            ->navigationGroups([
                'Dashboard',
                'Data KPI & OKR',
                'Kelola KPI & OKR',
                'Team Management',
                'Report'
            ])
            ->discoverWidgets(in: app_path('Filament/Manager/Widgets'), for: 'App\\Filament\\Manager\\Widgets')
            ->widgets([
                \App\Filament\Manager\Widgets\RecentActivities::class,
                \App\Filament\Manager\Widgets\TeamOverview::class,
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
                \App\Http\Middleware\onlyManager::class, // Middleware khusus manager
            ]);
    }
}
