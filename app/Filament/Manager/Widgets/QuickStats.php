<?php

namespace App\Filament\Manager\Widgets;

use App\Models\User;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class QuickStats extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $userDivisi = Auth::user()->divisi_id;
        $currentQuarter = 'Q2-2025'; // Using Q2-2025 where we have data
        
        // Team members count
        $teamCount = User::where('divisi_id', $userDivisi)
            ->where('id', '!=', Auth::id())
            ->count();

        // Total KPIs assigned to division
        $totalKpis = KelolaKPI::where('divisi_id', $userDivisi)
            ->orWhere(function($query) use ($userDivisi) {
                $query->where('assignment_type', 'individual')
                      ->whereHas('user', function($q) use ($userDivisi) {
                          $q->where('divisi_id', $userDivisi);
                      });
            })
            ->count();

        // Total OKRs assigned to division
        $totalOkrs = KelolaOKR::where('divisi_id', $userDivisi)
            ->orWhere(function($query) use ($userDivisi) {
                $query->where('assignment_type', 'individual')
                      ->whereHas('user', function($q) use ($userDivisi) {
                          $q->where('divisi_id', $userDivisi);
                      });
            })
            ->count();

        // Pending items (using current quarter)
        $pendingKpis = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->where('periode', $currentQuarter)
            ->where('nilai', 0) // Assuming 0 means pending
            ->count();
            
        $pendingOkrs = RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->where('periode', $currentQuarter)
            ->where('nilai', 0) // Assuming 0 means pending
            ->count();
            
        $pendingApprovals = $pendingKpis + $pendingOkrs;

        // Completion rate this quarter
        $completedThisQuarter = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->where('periode', $currentQuarter)
            ->where('nilai', '>', 0)
            ->count() + 
            RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->where('periode', $currentQuarter)
            ->where('nilai', '>', 0)
            ->count();

        $totalThisQuarter = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->where('periode', $currentQuarter)
            ->count() + 
            RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->where('periode', $currentQuarter)
            ->count();

        $completionRate = $totalThisQuarter > 0 ? round(($completedThisQuarter / $totalThisQuarter) * 100, 1) : 0;

        // Average performance
        $avgPerformance = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                $query->where('divisi_id', $userDivisi);
            })
            ->where('periode', $currentQuarter)
            ->where('nilai', '>', 0)
            ->avg('nilai') ?? 0;

        return [
            Stat::make('Team Members', $teamCount)
                ->description('Active team members')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([4, 3, 2, 5, 3, 4, 6]),
                
            Stat::make('Total KPIs', $totalKpis)
                ->description('KPIs assigned to division')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('info')
                ->chart([2, 4, 3, 5, 4, 6, 5]),
                
            Stat::make('Total OKRs', $totalOkrs)
                ->description('OKRs assigned to division')
                ->descriptionIcon('heroicon-m-flag')
                ->color('warning')
                ->chart([3, 2, 4, 3, 5, 4, 6]),
                
            Stat::make('Pending Approvals', $pendingApprovals)
                ->description('Waiting for your approval')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingApprovals > 0 ? 'danger' : 'success')
                ->chart([1, 2, 0, 1, 3, 2, $pendingApprovals]),
                
            Stat::make('Completion Rate', $completionRate . '%')
                ->description('This month completion rate')
                ->descriptionIcon($completionRate >= 80 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($completionRate >= 80 ? 'success' : ($completionRate >= 60 ? 'warning' : 'danger'))
                ->chart([65, 70, 75, 80, 85, 78, $completionRate]),
                
            Stat::make('Avg Performance', number_format($avgPerformance, 1) . '%')
                ->description('Division average performance')
                ->descriptionIcon($avgPerformance >= 80 ? 'heroicon-m-face-smile' : 'heroicon-m-face-frown')
                ->color($avgPerformance >= 90 ? 'success' : ($avgPerformance >= 75 ? 'warning' : 'danger'))
                ->chart([70, 75, 80, 85, 82, 88, $avgPerformance]),
        ];
    }
}
