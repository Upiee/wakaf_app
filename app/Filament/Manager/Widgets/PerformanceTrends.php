<?php

namespace App\Filament\Manager\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PerformanceTrends extends ChartWidget
{
    protected static ?string $heading = 'Division Performance Trends';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $userDivisi = Auth::user()->divisi_id;
        
        // Get last 6 months data
        $months = collect();
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months->push([
                'month' => $date->format('M Y'),
                'period' => $date->format('Y-m'),
            ]);
        }

        $kpiData = [];
        $okrData = [];
        $avgData = [];
        $labels = [];

        foreach ($months as $month) {
            $labels[] = $month['month'];
            
            // Get KPI average for this period
            $kpiAvg = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                    $query->where('divisi_id', $userDivisi);
                })
                ->where('periode', 'like', '%' . $month['period'] . '%')
                ->whereNotNull('approved_at')
                ->avg('nilai') ?? 0;
                
            // Get OKR average for this period  
            $okrAvg = RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                    $query->where('divisi_id', $userDivisi);
                })
                ->where('periode', 'like', '%' . $month['period'] . '%')
                ->whereNotNull('approved_at')
                ->avg('nilai') ?? 0;
                
            $kpiData[] = round($kpiAvg, 1);
            $okrData[] = round($okrAvg, 1);
            $avgData[] = round(($kpiAvg + $okrAvg) / 2, 1);
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
