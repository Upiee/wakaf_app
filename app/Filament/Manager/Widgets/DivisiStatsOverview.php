<?php

namespace App\Filament\Manager\Widgets;

use App\Models\User;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisiStatsOverview extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        $userDivisi = Auth::user()->divisi_id;
        
        // Average performance this month
        $avgKpiPerformance = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->whereMonth('created_at', now()->month)
            ->avg('nilai') ?? 0;

        $avgOkrPerformance = RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->whereMonth('created_at', now()->month)
            ->avg('nilai') ?? 0;

        // Completed realizations this month
        $completedThisMonth = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->whereMonth('created_at', now()->month)
            ->whereNotNull('approved_at')
            ->count() +
            RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->whereMonth('created_at', now()->month)
            ->whereNotNull('approved_at')
            ->count();

        // Active employees (those with recent activity)
        $activeEmployees = User::where('divisi_id', $userDivisi)
            ->where('id', '!=', Auth::id())
            ->whereHas('realisasiKpis', function ($query) {
                $query->whereMonth('created_at', now()->month);
            })
            ->orWhereHas('realisasiOkrs', function ($query) {
                $query->whereMonth('created_at', now()->month);
            })
            ->count();

        $overallPerformance = ($avgKpiPerformance + $avgOkrPerformance) / 2;

        return [
            Stat::make('Divisi Performance', number_format($overallPerformance, 1) . '%')
                ->description('Average performance this month')
                ->descriptionIcon($overallPerformance >= 80 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($overallPerformance >= 90 ? 'success' : ($overallPerformance >= 75 ? 'warning' : 'danger')),
                
            Stat::make('Active Employees', $activeEmployees)
                ->description('Employees with recent activity')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('info'),
                
            Stat::make('Completed This Month', $completedThisMonth)
                ->description('Realizations completed')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
