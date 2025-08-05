<?php

namespace App\Filament\Hr\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\Divisi;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class DivisionPerformanceTrends extends ChartWidget
{
    protected static ?string $heading = 'Performance Trends by Division (Quarterly)';
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Get available periods from actual data
        $availablePeriods = collect([
            ['label' => 'Q1 2025', 'period' => 'Q1-2025'],
            ['label' => 'Q2 2025', 'period' => 'Q2-2025'],
            ['label' => 'Q3 2025', 'period' => 'Q3-2025'],
            ['label' => 'Q4 2025', 'period' => 'Q4-2025'],
        ]);

        $labels = $availablePeriods->pluck('label')->toArray();
        
        // Get all divisions that have performance data
        $divisions = Divisi::whereHas('users.realisasiKpis')
            ->orWhereHas('users.realisasiOkrs')
            ->get();

        $datasets = [];
        $colors = [
            'rgb(59, 130, 246)',    // Blue
            'rgb(16, 185, 129)',    // Green
            'rgb(245, 158, 11)',    // Yellow
            'rgb(239, 68, 68)',     // Red
            'rgb(139, 92, 246)',    // Purple
            'rgb(236, 72, 153)',    // Pink
        ];

        foreach ($divisions as $index => $division) {
            $divisionData = [];
            
            foreach ($availablePeriods as $period) {
                // Get KPI average for this division and period
                $kpiAvg = RealisasiKpi::whereHas('user', function ($query) use ($division) {
                        $query->where('divisi_id', $division->id);
                    })
                    ->where('periode', $period['period'])
                    ->avg('nilai') ?? 0;
                    
                // Get OKR average for this division and period
                $okrAvg = RealisasiOkr::whereHas('user', function ($query) use ($division) {
                        $query->where('divisi_id', $division->id);
                    })
                    ->where('periode', $period['period'])
                    ->avg('nilai') ?? 0;
                    
                // Calculate weighted average
                if ($kpiAvg > 0 && $okrAvg > 0) {
                    $avgScore = ($kpiAvg + $okrAvg) / 2;
                } elseif ($kpiAvg > 0) {
                    $avgScore = $kpiAvg;
                } elseif ($okrAvg > 0) {
                    $avgScore = $okrAvg;
                } else {
                    $avgScore = 0;
                }
                
                $divisionData[] = round($avgScore, 1);
            }

            $color = $colors[$index % count($colors)];
            
            $datasets[] = [
                'label' => $division->nama,
                'data' => $divisionData,
                'backgroundColor' => str_replace('rgb', 'rgba', str_replace(')', ', 0.1)', $color)),
                'borderColor' => $color,
                'borderWidth' => 3,
                'fill' => false,
                'tension' => 0.1,
            ];
        }

        return [
            'datasets' => $datasets,
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
                    'text' => 'Division Performance Comparison',
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
