<?php

namespace App\Filament\Employee\Pages;

use App\Filament\Employee\Widgets\MyKpiOkrOverview;
use App\Filament\Employee\Widgets\PerformanceChart;
use App\Filament\Employee\Widgets\RecentRealizationActivities;
use App\Filament\Employee\Widgets\RecentOkrActivities;
use App\Filament\Employee\Widgets\QuickActions;
use App\Filament\Employee\Widgets\DeadlineReminders;
use App\Filament\Employee\Widgets\ProgressSummary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament.employee.pages.dashboard';
    public ?string $user_id = null;

    public function mount(Request $request): void
    {
        parent::mount();

        $this->user_id = $request->query('user_id') ?? Auth::id();
    }

    public function getTitle(): string
    {
        $user = Auth::user();
        $divisiName = $user->divisi->nama ?? 'Unknown';
        return "Dashboard Karyawan - {$divisiName}";
    }
    
    public function getSubheading(): ?string
    {
        $user = Auth::user();
        
        return "Selamat datang, {$user->name}! Silahkan kelola performa KPI/OKR Anda dengan optimal.";
    }
    public function getWidgets(): array
    {
        return [
            MyKpiOkrOverview::class,
            PerformanceChart::class,
            ProgressSummary::class,
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
            ProgressSummary::make([
                'user_id' => $this->user_id,
            ]),
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
