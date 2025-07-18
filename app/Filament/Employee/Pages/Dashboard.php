<?php

namespace App\Filament\Employee\Pages;

use App\Filament\Employee\Widgets\MyKpiOkrOverview;
use App\Filament\Employee\Widgets\PerformanceChart;
use App\Filament\Employee\Widgets\RecentRealizationActivities;
use App\Filament\Employee\Widgets\RecentOkrActivities;
use App\Filament\Employee\Widgets\QuickActions;
use App\Filament\Employee\Widgets\DeadlineReminders;
use App\Filament\Employee\Widgets\ProgressSummary;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.employee.pages.dashboard';

    public function getWidgets(): array
    {
        return [
            MyKpiOkrOverview::class,
            PerformanceChart::class,
            ProgressSummary::class,
            QuickActions::class,
            DeadlineReminders::class,
            RecentRealizationActivities::class,
            RecentOkrActivities::class,
        ];
    }

    public function getColumns(): int|string|array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 3,
            'xl' => 4,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            MyKpiOkrOverview::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            RecentRealizationActivities::class,
            RecentOkrActivities::class,
        ];
    }
}
