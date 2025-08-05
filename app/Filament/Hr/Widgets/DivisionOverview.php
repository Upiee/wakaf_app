<?php

namespace App\Filament\Hr\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\Divisi;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DivisionOverview extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        $currentPeriod = 'Q2-2025';
        
        // Get all divisions with their performance data
        $divisions = Divisi::with(['users' => function($query) {
            $query->where('role', '!=', 'manager')
                  ->where('role', '!=', 'hr');
        }])->get();

        $stats = [];

        foreach ($divisions as $division) {
            $userIds = $division->users->pluck('id');
            
            if ($userIds->isEmpty()) {
                continue;
            }

            // Calculate KPI Performance
            $kpiAvg = RealisasiKpi::whereIn('user_id', $userIds)
                ->where('periode', $currentPeriod)
                ->avg('nilai') ?? 0;

            // Calculate OKR Performance  
            $okrAvg = RealisasiOkr::whereIn('user_id', $userIds)
                ->where('periode', $currentPeriod)
                ->avg('nilai') ?? 0;

            // Overall performance
            if ($kpiAvg > 0 && $okrAvg > 0) {
                $overallPerformance = ($kpiAvg + $okrAvg) / 2;
            } elseif ($kpiAvg > 0) {
                $overallPerformance = $kpiAvg;
            } elseif ($okrAvg > 0) {
                $overallPerformance = $okrAvg;
            } else {
                $overallPerformance = 0;
            }

            // Get team size
            $teamSize = $division->users->count();
            
            // Get active employees (those with performance data)
            $activeEmployees = User::where('divisi_id', $division->id)
                ->where(function($query) use ($currentPeriod) {
                    $query->whereHas('realisasiKpis', function ($q) use ($currentPeriod) {
                        $q->where('periode', $currentPeriod);
                    })
                    ->orWhereHas('realisasiOkrs', function ($q) use ($currentPeriod) {
                        $q->where('periode', $currentPeriod);
                    });
                })
                ->count();

            $description = "{$activeEmployees}/{$teamSize} active members";
            $descriptionIcon = 'heroicon-m-users';
            
            // Color based on performance
            $color = $this->getPerformanceColor($overallPerformance);

            $stats[] = Stat::make($division->nama, number_format($overallPerformance, 1) . '%')
                ->description($description)
                ->descriptionIcon($descriptionIcon)
                ->color($color);
        }

        return $stats;
    }

    private function getPerformanceColor($score): string
    {
        if ($score >= 90) {
            return 'success';
        } elseif ($score >= 75) {
            return 'warning';
        } elseif ($score >= 60) {
            return 'info';
        } else {
            return 'danger';
        }
    }
}
