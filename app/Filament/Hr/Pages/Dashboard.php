<?php

namespace App\Filament\Hr\Pages;

use App\Filament\Hr\Widgets\DivisionPerformanceTrends;
use App\Filament\Hr\Widgets\StatsOverview;
use App\Filament\Hr\Widgets\TopPerformersAllDivisions;
use App\Filament\Hr\Widgets\DivisionOverview;
use App\Filament\Hr\Widgets\RecentActivities;
use Illuminate\Support\Facades\Auth;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static string $view = 'filament-panels::pages.dashboard';
    
    public function getTitle(): string
    {
        return "Dashboard HR - Performance Management";
    }
    
    public function getSubheading(): ?string
    {
        $user = Auth::user();
        
        return "Selamat datang, {$user->name}! Kelola performa strategis seluruh organisasi dengan optimal.";
    }
    public function getWidgets(): array
    {
        return [
            StatsOverview::class,
            DivisionOverview::class,
            DivisionPerformanceTrends::class,
            TopPerformersAllDivisions::class,
            RecentActivities::class,
        ];
    }
    
    public function getColumns(): int | string | array
    {
        return 2;
    }
}
