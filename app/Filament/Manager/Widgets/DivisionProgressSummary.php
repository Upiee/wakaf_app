<?php

namespace App\Filament\Manager\Widgets;

use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DivisionProgressSummary extends Widget
{
    protected static ?string $heading = 'Division Progress Summary';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 'full';

    protected static string $view = 'filament.manager.widgets.division-progress-summary';

    public function getViewData(): array
    {
        $user = Auth::user();
        $divisiId = $user->divisi_id;
        
        // Get all employees in division (excluding manager)
        $divisionEmployees = User::where('divisi_id', $divisiId)
            ->where('role', 'employee')
            ->get();
        
        // Get available quarters and use the latest one with data
        $availableQuarters = $this->getAvailableQuarters($divisionEmployees);
        $targetQuarter = $availableQuarters[0] ?? 'Q2-2025';
        $totalEmployees = $divisionEmployees->count();
        
        // Initialize totals
        $totalKpisAssigned = 0;
        $totalOkrsAssigned = 0;
        $completedKpis = 0;
        $pendingKpis = 0;
        $draftKpis = 0;
        $completedOkrs = 0;
        $pendingOkrs = 0;
        $draftOkrs = 0;
        $avgKpiScore = 0;
        $avgOkrScore = 0;
        $totalKpiBobot = 0;
        $totalOkrBobot = 0;
        
        if ($totalEmployees > 0) {
            // KPI Progress untuk seluruh divisi
            $totalKpisAssigned = KelolaKPI::whereIn('user_id', $divisionEmployees->pluck('id'))
                ->where('assignment_type', 'individual')
                ->count();
                
            $kpiRealisasiData = RealisasiKpi::whereIn('user_id', $divisionEmployees->pluck('id'))
                ->where('periode', $targetQuarter)
                ->get();
                
            $completedKpis = $kpiRealisasiData->whereNotNull('approved_at')->count();
            $pendingKpis = $kpiRealisasiData->where('is_cutoff', true)->whereNull('approved_at')->count();
            $draftKpis = $kpiRealisasiData->where('is_cutoff', false)->count();

            // OKR Progress untuk seluruh divisi
            $totalOkrsAssigned = KelolaOKR::whereIn('user_id', $divisionEmployees->pluck('id'))
                ->where('assignment_type', 'individual')
                ->count();
                
            $okrRealisasiData = RealisasiOkr::whereIn('user_id', $divisionEmployees->pluck('id'))
                ->where('periode', $targetQuarter)
                ->get();
                
            $completedOkrs = $okrRealisasiData->whereNotNull('approved_at')->count();
            $pendingOkrs = $okrRealisasiData->where('is_cutoff', true)->whereNull('approved_at')->count();
            $draftOkrs = $okrRealisasiData->where('is_cutoff', false)->count();

            // Hitung rata-rata nilai realisasi untuk quartal ini
            $avgKpiScore = $kpiRealisasiData->whereNotNull('approved_at')->avg('nilai') ?? 0;
            $avgOkrScore = $okrRealisasiData->whereNotNull('approved_at')->avg('nilai') ?? 0;

            // Hitung total bobot KPI dan OKR berdasarkan assignment aktual ke divisi ini
            $totalKpiBobot = KelolaKPI::whereIn('user_id', $divisionEmployees->pluck('id'))
                ->where('assignment_type', 'individual')
                ->sum('bobot') ?? 0;
                
            $totalOkrBobot = KelolaOKR::whereIn('user_id', $divisionEmployees->pluck('id'))
                ->where('assignment_type', 'individual')
                ->sum('bobot') ?? 0;
        }
        
        // Hitung total keseluruhan bobot
        $totalBobot = $totalKpiBobot + $totalOkrBobot;
        
        // Hitung persentase bobot masing-masing (dynamic weight calculation)
        $kpiWeight = $totalBobot > 0 ? ($totalKpiBobot / $totalBobot) * 100 : 50;
        $okrWeight = $totalBobot > 0 ? ($totalOkrBobot / $totalBobot) * 100 : 50;
        
        // Hitung weighted average untuk total progress
        $totalWeightedScore = 0;
        if ($kpiWeight > 0 && $avgKpiScore > 0) {
            $totalWeightedScore += ($avgKpiScore * $kpiWeight / 100);
        }
        if ($okrWeight > 0 && $avgOkrScore > 0) {
            $totalWeightedScore += ($avgOkrScore * $okrWeight / 100);
        }

        // Performance level based on weighted score
        $performanceLevel = $this->getPerformanceLevel($totalWeightedScore);
        $performanceColor = $this->getPerformanceColor($totalWeightedScore);

        // Calculate completion rates
        $kpiCompletionRate = $totalKpisAssigned > 0 ? ($completedKpis / $totalKpisAssigned) * 100 : 0;
        $okrCompletionRate = $totalOkrsAssigned > 0 ? ($completedOkrs / $totalOkrsAssigned) * 100 : 0;

        return [
            'division_name' => $user->divisi->nama ?? 'Unknown Division',
            'total_employees' => $totalEmployees,
            'target_quarter' => $targetQuarter,
            'available_quarters' => $availableQuarters,
            
            // KPI Stats
            'kpi_stats' => [
                'total_assigned' => $totalKpisAssigned,
                'completed' => $completedKpis,
                'pending' => $pendingKpis,
                'draft' => $draftKpis,
                'avg_score' => $avgKpiScore,
                'completion_rate' => $kpiCompletionRate,
                'weight_percentage' => $kpiWeight,
            ],
            
            // OKR Stats
            'okr_stats' => [
                'total_assigned' => $totalOkrsAssigned,
                'completed' => $completedOkrs,
                'pending' => $pendingOkrs,
                'draft' => $draftOkrs,
                'avg_score' => $avgOkrScore,
                'completion_rate' => $okrCompletionRate,
                'weight_percentage' => $okrWeight,
            ],
            
            // Overall Stats
            'overall' => [
                'weighted_score' => $totalWeightedScore,
                'performance_level' => $performanceLevel,
                'performance_color' => $performanceColor,
                'total_completed' => $completedKpis + $completedOkrs,
                'total_pending' => $pendingKpis + $pendingOkrs,
                'total_draft' => $draftKpis + $draftOkrs,
            ]
        ];
    }

    private function getPerformanceLevel(float $score): string
    {
        if ($score >= 90) return 'Excellent';
        if ($score >= 75) return 'Good';  
        if ($score >= 60) return 'Satisfactory';
        return 'Needs Improvement';
    }

    private function getPerformanceColor(float $score): string
    {
        if ($score >= 90) return 'success';
        if ($score >= 75) return 'warning';
        if ($score >= 60) return 'info';
        return 'danger';
    }

    private function getAvailableQuarters($divisionEmployees): array
    {
        if ($divisionEmployees->isEmpty()) {
            return ['Q2-2025']; // Default fallback
        }

        $employeeIds = $divisionEmployees->pluck('id');
        
        // Get all distinct quarters from both KPI and OKR realization data
        $kpiQuarters = RealisasiKpi::whereIn('user_id', $employeeIds)
            ->distinct()
            ->pluck('periode')
            ->toArray();
            
        $okrQuarters = RealisasiOkr::whereIn('user_id', $employeeIds)
            ->distinct()
            ->pluck('periode')
            ->toArray();
            
        // Merge and get unique quarters
        $allQuarters = array_unique(array_merge($kpiQuarters, $okrQuarters));
        
        // Sort quarters in descending order (latest first)
        usort($allQuarters, function($a, $b) {
            // Parse quarters (e.g., "Q2-2025")
            preg_match('/Q(\d+)-(\d+)/', $a, $matchesA);
            preg_match('/Q(\d+)-(\d+)/', $b, $matchesB);
            
            $yearA = (int)$matchesA[2];
            $quarterA = (int)$matchesA[1];
            $yearB = (int)$matchesB[2];
            $quarterB = (int)$matchesB[1];
            
            if ($yearA !== $yearB) {
                return $yearB - $yearA; // Latest year first
            }
            return $quarterB - $quarterA; // Latest quarter first
        });
        
        return array_values($allQuarters);
    }
}
