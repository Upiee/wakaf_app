<?php

namespace App\Filament\Employee\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'My Performance Trend';
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $user = Auth::user();
        
        // Get last 6 months data
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'period' => 'Q' . $date->quarter . '-' . $date->year,
            ]);
        }

        $kpiData = [];
        $okrData = [];
        $labels = [];

        foreach ($months as $month) {
            $labels[] = $month['month'];
            
            // Get KPI average for this period
            $kpiAvg = RealisasiKpi::where('user_id', $user->id)
                ->where('periode', 'like', '%' . $month['period'] . '%')
                ->avg('nilai') ?? 0;
                
            // Get OKR average for this period  
            $okrAvg = RealisasiOkr::where('user_id', $user->id)
                ->where('periode', 'like', '%' . $month['period'] . '%')
                ->avg('nilai') ?? 0;
                
            $kpiData[] = round($kpiAvg, 1);
            $okrData[] = round($okrAvg, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'KPI Performance',
                    'data' => $kpiData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
                [
                    'label' => 'OKR Performance',
                    'data' => $okrData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 2,
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => true,
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                ],
            ],
        ];
    }
}
