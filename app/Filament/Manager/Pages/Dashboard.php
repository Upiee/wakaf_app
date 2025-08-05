<?php

namespace App\Filament\Manager\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?int $navigationSort = 1;

    public function getTitle(): string
    {
        $user = Auth::user();
        $divisiName = $user->divisi->nama ?? 'Unknown';
        return "Dashboard Manager - {$divisiName}";
    }

    public function getSubheading(): ?string
    {
        $user = Auth::user();
        
        return "Selamat datang, {$user->name}! Silahkan kelola performa divisi Anda dengan optimal.";
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Manager\Widgets\PerformanceTrends::class,
            \App\Filament\Manager\Widgets\TopPerformers::class,
            \App\Filament\Manager\Widgets\TeamOverview::class,
        ];
    }

    public function getColumns(): int | string | array
    {
        return [
            'default' => 1,
            'sm' => 2,
            'md' => 3,
            'lg' => 4,
            'xl' => 6,
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Manager\Widgets\DivisionPerformanceSummary::class,
            \App\Filament\Manager\Widgets\QuickStats::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            \App\Filament\Manager\Widgets\RecentActivities::class,
        ];
    }
}
