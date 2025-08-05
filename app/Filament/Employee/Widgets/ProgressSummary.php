<?php

namespace App\Filament\Employee\Widgets;

use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProgressSummary extends Widget
{
    protected static ?string $heading = 'My Progress Summary';
    protected static ?int $sort = 7;
    public ?string $user_id = null;
    protected ?string $dynamicHeading = null;
    protected int | string | array $columnSpan = 2;

    protected static string $view = 'filament.employee.widgets.progress-summary';

    public function getHeading(): string
    {
        return $this->dynamicHeading ?? static::$heading;
    }

    public function getViewData(): array
    {
        // Check for user_id query parameter or use authenticated user
        $targetUserId = request()->get('user_id') ? 
            User::find(request()->get('user_id'))?->id : 
            ($this->user_id ?? Auth::id());
            
        $user = User::findOrFail($targetUserId);
        
        // Update heading based on user
        if ($targetUserId !== Auth::id()) {
            $this->dynamicHeading = "Progress Summary - {$user->name}";
        } else {
            $this->dynamicHeading = 'My Progress Summary';
        }
        
        $currentQuarter = 'Q' . Carbon::now()->quarter . '-' . Carbon::now()->year;
        
        // Filter berdasarkan quartal yang spesifik (Q2-2025 untuk data yang ada)
        $targetQuarter = 'Q2-2025'; // Bisa disesuaikan atau dibuat dinamis
        
        // KPI Progress berdasarkan quartal
        $totalKpis = KelolaKPI::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->count();
            
        $kpiRealisasiData = RealisasiKpi::where('user_id', $user->id)
            ->where('periode', $targetQuarter)
            ->get();
            
        $completedKpis = $kpiRealisasiData->whereNotNull('approved_at')->count();
        $pendingKpis = $kpiRealisasiData->where('is_cutoff', true)->whereNull('approved_at')->count();
        $draftKpis = $kpiRealisasiData->where('is_cutoff', false)->count();

        // OKR Progress berdasarkan quartal
        $totalOkrs = KelolaOKR::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->count();
            
        $okrRealisasiData = RealisasiOkr::where('user_id', $user->id)
            ->where('periode', $targetQuarter)
            ->get();
            
        $completedOkrs = $okrRealisasiData->whereNotNull('approved_at')->count();
        $pendingOkrs = $okrRealisasiData->where('is_cutoff', true)->whereNull('approved_at')->count();
        $draftOkrs = $okrRealisasiData->where('is_cutoff', false)->count();

        // Hitung rata-rata nilai realisasi untuk quartal ini
        $avgKpiScore = $kpiRealisasiData->whereNotNull('approved_at')->avg('nilai') ?? 0;
        $avgOkrScore = $okrRealisasiData->whereNotNull('approved_at')->avg('nilai') ?? 0;

        // Hitung total bobot KPI dan OKR berdasarkan assignment aktual ke user ini
        $totalKpiBobot = KelolaKPI::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->sum('bobot') ?? 0;
            
        $totalOkrBobot = KelolaOKR::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->sum('bobot') ?? 0;
            
        // Hitung total keseluruhan bobot
        $totalBobot = $totalKpiBobot + $totalOkrBobot;
        
        // Hitung persentase bobot masing-masing (dynamic weight calculation)
        $kpiWeight = $totalBobot > 0 ? ($totalKpiBobot / $totalBobot) * 100 : 0;
        $okrWeight = $totalBobot > 0 ? ($totalOkrBobot / $totalBobot) * 100 : 0;
        
        // Hitung weighted average untuk total progress
        $totalProgress = 0;
        if ($avgKpiScore > 0 || $avgOkrScore > 0) {
            $totalProgress = (($avgKpiScore * $kpiWeight) + ($avgOkrScore * $okrWeight)) / 100;
        }

        // Calculate completion rates berdasarkan quartal
        $kpiCompletionRate = $totalKpis > 0 ? ($completedKpis / $totalKpis) * 100 : 0;
        $okrCompletionRate = $totalOkrs > 0 ? ($completedOkrs / $totalOkrs) * 100 : 0;
        $overallCompletionRate = ($totalKpis + $totalOkrs) > 0 ? 
            (($completedKpis + $completedOkrs) / ($totalKpis + $totalOkrs)) * 100 : 0;

        return [
            'current_quarter' => $targetQuarter,
            'kpi_stats' => [
                'total' => $totalKpis,
                'completed' => $completedKpis,
                'pending' => $pendingKpis,
                'draft' => $draftKpis,
                'completion_rate' => $kpiCompletionRate,
                'avg_score' => round($avgKpiScore, 1),
                'weight_percentage' => round($kpiWeight, 1),
                'total_bobot' => $totalKpiBobot,
            ],
            'okr_stats' => [
                'total' => $totalOkrs,
                'completed' => $completedOkrs,
                'pending' => $pendingOkrs,
                'draft' => $draftOkrs,
                'completion_rate' => $okrCompletionRate,
                'avg_score' => round($avgOkrScore, 1),
                'weight_percentage' => round($okrWeight, 1),
                'total_bobot' => $totalOkrBobot,
            ],
            'overall_completion_rate' => $overallCompletionRate,
            'total_weighted_progress' => round($totalProgress, 1),
            'total_bobot_keseluruhan' => $totalBobot,
            'performance_breakdown' => [
                'kpi_contribution' => round(($avgKpiScore * $kpiWeight) / 100, 1),
                'okr_contribution' => round(($avgOkrScore * $okrWeight) / 100, 1),
            ]
        ];
    }
}
