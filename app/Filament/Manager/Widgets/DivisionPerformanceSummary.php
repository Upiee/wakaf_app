<?php

namespace App\Filament\Manager\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class DivisionPerformanceSummary extends BaseWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();
        $divisionId = $user->divisi_id;
        $currentPeriod = 'Q2-2025'; // You can make this dynamic based on current period
        
        // Get all users in the division (excluding the manager)
        $divisionUsers = User::where('divisi_id', $divisionId)
            ->where('id', '!=', $user->id)
            ->get();
        
        // Calculate KPI Performance
        $kpiData = $this->calculateKpiPerformance($divisionUsers, $currentPeriod);
        
        // Calculate OKR Performance
        $okrData = $this->calculateOkrPerformance($divisionUsers, $currentPeriod);
        
        // Overall Division Performance
        $overallData = $this->calculateOverallPerformance($kpiData, $okrData);
        
        return [
            Stat::make('KPI Division Performance', $kpiData['average'] . '%')
                ->description($kpiData['description'])
                ->descriptionIcon('heroicon-m-presentation-chart-bar')
                ->color($this->getPerformanceColor($kpiData['average'])),
                
            Stat::make('OKR Division Performance', $okrData['average'] . '%')
                ->description($okrData['description'])
                ->descriptionIcon('heroicon-m-flag')
                ->color($this->getPerformanceColor($okrData['average'])),
                
            Stat::make('Overall Division Score', $overallData['average'] . '%')
                ->description($overallData['description'])
                ->descriptionIcon('heroicon-m-trophy')
                ->color($this->getPerformanceColor($overallData['average'])),
        ];
    }
    
    private function calculateKpiPerformance($users, $period)
    {
        $userIds = $users->pluck('id');
        
        $kpiRealisasi = RealisasiKpi::whereIn('user_id', $userIds)
            ->where('periode', $period)
            ->get();
            
        if ($kpiRealisasi->isEmpty()) {
            return [
                'average' => 0,
                'description' => 'No KPI data for this period',
                'count' => 0
            ];
        }
        
        // Calculate average per user
        $userAverages = [];
        foreach ($users as $user) {
            $userKpis = $kpiRealisasi->where('user_id', $user->id);
            if ($userKpis->isNotEmpty()) {
                $userAverages[] = $userKpis->avg('nilai');
            }
        }
        
        $overallAverage = collect($userAverages)->avg();
        $activeUsers = count($userAverages);
        
        return [
            'average' => round($overallAverage, 1),
            'description' => "{$activeUsers} of {$users->count()} employees have KPI data",
            'count' => $activeUsers
        ];
    }
    
    private function calculateOkrPerformance($users, $period)
    {
        $userIds = $users->pluck('id');
        
        $okrRealisasi = RealisasiOkr::whereIn('user_id', $userIds)
            ->where('periode', $period)
            ->get();
            
        if ($okrRealisasi->isEmpty()) {
            return [
                'average' => 0,
                'description' => 'No OKR data for this period',
                'count' => 0
            ];
        }
        
        // Calculate average per user
        $userAverages = [];
        foreach ($users as $user) {
            $userOkrs = $okrRealisasi->where('user_id', $user->id);
            if ($userOkrs->isNotEmpty()) {
                $userAverages[] = $userOkrs->avg('nilai');
            }
        }
        
        $overallAverage = collect($userAverages)->avg();
        $activeUsers = count($userAverages);
        
        return [
            'average' => round($overallAverage, 1),
            'description' => "{$activeUsers} of {$users->count()} employees have OKR data",
            'count' => $activeUsers
        ];
    }
    
    private function calculateOverallPerformance($kpiData, $okrData)
    {
        // If both have data, average them
        if ($kpiData['count'] > 0 && $okrData['count'] > 0) {
            $overall = ($kpiData['average'] + $okrData['average']) / 2;
            $description = "Combined KPI & OKR performance";
        }
        // If only KPI has data
        elseif ($kpiData['count'] > 0) {
            $overall = $kpiData['average'];
            $description = "Based on KPI data only";
        }
        // If only OKR has data
        elseif ($okrData['count'] > 0) {
            $overall = $okrData['average'];
            $description = "Based on OKR data only";
        }
        // No data
        else {
            $overall = 0;
            $description = "No performance data available";
        }
        
        return [
            'average' => round($overall, 1),
            'description' => $description
        ];
    }
    
    private function getPerformanceColor($percentage)
    {
        if ($percentage >= 90) {
            return 'success';
        } elseif ($percentage >= 70) {
            return 'warning';
        } elseif ($percentage >= 50) {
            return 'info';
        } else {
            return 'danger';
        }
    }
}
