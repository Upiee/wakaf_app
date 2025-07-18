<?php

namespace App\Filament\Employee\Widgets;

use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class MyKpiOkrOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        
        // Count assigned KPI & OKR
        $totalKpi = KelolaKPI::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->count();
            
        $totalOkr = KelolaOKR::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->count();

        // Count completed realizations
        $completedKpi = RealisasiKpi::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->count();
            
        $completedOkr = RealisasiOkr::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->count();

        // Count pending approvals
        $pendingKpi = RealisasiKpi::where('user_id', $user->id)
            ->where('is_cutoff', true)
            ->whereNull('approved_at')
            ->count();
            
        $pendingOkr = RealisasiOkr::where('user_id', $user->id)
            ->where('is_cutoff', true)
            ->whereNull('approved_at')
            ->count();

        // Calculate average performance
        $avgKpiScore = RealisasiKpi::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->avg('nilai') ?? 0;
            
        $avgOkrScore = RealisasiOkr::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->avg('nilai') ?? 0;

        $overallAvg = ($avgKpiScore + $avgOkrScore) / 2;

        return [
            Stat::make('Total KPI Assigned', $totalKpi)
                ->description('Individual KPI assigned to me')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 3, 4, 5, 6, 3, 5, 3]),
                
            Stat::make('Total OKR Assigned', $totalOkr)
                ->description('Individual OKR assigned to me')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->chart([3, 4, 3, 5, 6, 7, 4, 5]),
                
            Stat::make('Completed & Approved', $completedKpi + $completedOkr)
                ->description('KPI & OKR realizations approved')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
                
            Stat::make('Pending Approval', $pendingKpi + $pendingOkr)
                ->description('Waiting for manager approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
                
            Stat::make('Average Performance', number_format($overallAvg, 1) . '%')
                ->description('Overall performance score')
                ->descriptionIcon($overallAvg >= 80 ? 'heroicon-m-face-smile' : 'heroicon-m-face-frown')
                ->color($overallAvg >= 90 ? 'success' : ($overallAvg >= 75 ? 'warning' : 'danger'))
                ->chart([65, 70, 75, 80, 85, 78, 82, $overallAvg]),
        ];
    }

    protected function getColumns(): int
    {
        return 5;
    }
}
