<?php

namespace App\Filament\Manager\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PerformanceTrends extends ChartWidget
{
    protected static ?string $heading = 'Division Performance Trends (Quarterly)';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    public function getHeading(): string
    {
        $divisiName = Auth::user()->divisi->nama ?? 'Unknown Division';
        return "Performance Trends - {$divisiName} Division";
    }

    protected function getData(): array
    {
        $userDivisi = Auth::user()->divisi_id;
        
        // Get available periods from actual data
        $availablePeriods = collect([
            ['label' => 'Q1 2025', 'period' => 'Q1-2025'],
            ['label' => 'Q2 2025', 'period' => 'Q2-2025'],
            ['label' => 'Q3 2025', 'period' => 'Q3-2025'],
            ['label' => 'Q4 2025', 'period' => 'Q4-2025'],
        ]);

        $kpiData = [];
        $okrData = [];
        $avgData = [];
        $labels = [];

        foreach ($availablePeriods as $period) {
            $labels[] = $period['label'];
            
            // Get KPI average for this quarter from users in this division
            $kpiAvg = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                    $query->where('divisi_id', $userDivisi);
                })
                ->where('periode', $period['period'])
                ->avg('nilai') ?? 0;
                
            // Get OKR average for this quarter from users in this division
            $okrAvg = RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                    $query->where('divisi_id', $userDivisi);
                })
                ->where('periode', $period['period'])
                ->avg('nilai') ?? 0;
                
            $kpiData[] = round($kpiAvg, 1);
            $okrData[] = round($okrAvg, 1);
            
            // Calculate average only if both KPI and OKR have data
            if ($kpiAvg > 0 && $okrAvg > 0) {
                $avgData[] = round(($kpiAvg + $okrAvg) / 2, 1);
            } elseif ($kpiAvg > 0) {
                $avgData[] = round($kpiAvg, 1);
            } elseif ($okrAvg > 0) {
                $avgData[] = round($okrAvg, 1);
            } else {
                $avgData[] = 0;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'KPI Performance',
                    'data' => $kpiData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 3,
                    'fill' => true,
                ],
                [
                    'label' => 'OKR Performance',
                    'data' => $okrData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 3,
                    'fill' => true,
                ],
                [
                    'label' => 'Average Performance',
                    'data' => $avgData,
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'borderColor' => 'rgb(245, 158, 11)',
                    'borderWidth' => 3,
                    'fill' => false,
                    'borderDash' => [5, 5],
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
                    'position' => 'top',
                ],
                'title' => [
                    'display' => true,
                    'text' => 'Performance Trend Analysis',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'max' => 100,
                    'ticks' => [
                        'callback' => 'function(value) { return value + "%"; }',
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}
