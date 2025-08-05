<?php

namespace App\Filament\Manager\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class PerformanceChart extends ChartWidget
{
    protected static ?string $heading = 'Performance Trends';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        $userDivisi = Auth::user()->divisi_id;
        $quarters = ['Q1-2025', 'Q2-2025', 'Q3-2025'];
        
        $kpiData = [];
        $okrData = [];
        
        foreach ($quarters as $quarter) {
            // Get average KPI performance for the quarter
            $avgKpi = RealisasiKpi::whereHas('user', function ($query) use ($userDivisi) {
                    $query->where('divisi_id', $userDivisi);
                })
                ->where('periode', $quarter)
                ->avg('nilai') ?? 0;
                
            // Get average OKR performance for the quarter
            $avgOkr = RealisasiOkr::whereHas('user', function ($query) use ($userDivisi) {
                    $query->where('divisi_id', $userDivisi);
                })
                ->where('periode', $quarter)
                ->avg('nilai') ?? 0;
                
            $kpiData[] = round($avgKpi, 1);
            $okrData[] = round($avgOkr, 1);
        }

        return [
            'datasets' => [
                [
                    'label' => 'KPI Performance',
                    'data' => $kpiData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                ],
                [
                    'label' => 'OKR Performance', 
                    'data' => $okrData,
                    'borderColor' => '#f59e0b',
                    'backgroundColor' => 'rgba(245, 158, 11, 0.1)',
                    'tension' => 0.4,
                ],
            ],
            'labels' => $quarters,
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
