<?php

namespace App\Filament\Employee\Widgets;

use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProgressSummary extends Widget
{
    protected static ?string $heading = 'My Progress Summary';
    protected static ?int $sort = 7;
    protected int | string | array $columnSpan = 2;

    protected static string $view = 'filament.employee.widgets.progress-summary';

    public function getViewData(): array
    {
        $user = Auth::user();
        $currentQuarter = 'Q' . Carbon::now()->quarter . '-' . Carbon::now()->year;
        
        // KPI Progress
        $totalKpis = KelolaKPI::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->count();
            
        $completedKpis = RealisasiKpi::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->count();
            
        $pendingKpis = RealisasiKpi::where('user_id', $user->id)
            ->where('is_cutoff', true)
            ->whereNull('approved_at')
            ->count();
            
        $draftKpis = RealisasiKpi::where('user_id', $user->id)
            ->where('is_cutoff', false)
            ->count();

        // OKR Progress
        $totalOkrs = KelolaOKR::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->count();
            
        $completedOkrs = RealisasiOkr::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->count();
            
        $pendingOkrs = RealisasiOkr::where('user_id', $user->id)
            ->where('is_cutoff', true)
            ->whereNull('approved_at')
            ->count();
            
        $draftOkrs = RealisasiOkr::where('user_id', $user->id)
            ->where('is_cutoff', false)
            ->count();

        // Calculate completion rates
        $kpiCompletionRate = $totalKpis > 0 ? ($completedKpis / $totalKpis) * 100 : 0;
        $okrCompletionRate = $totalOkrs > 0 ? ($completedOkrs / $totalOkrs) * 100 : 0;
        $overallCompletionRate = ($totalKpis + $totalOkrs) > 0 ? 
            (($completedKpis + $completedOkrs) / ($totalKpis + $totalOkrs)) * 100 : 0;

        // Average scores
        $avgKpiScore = RealisasiKpi::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->avg('nilai') ?? 0;
            
        $avgOkrScore = RealisasiOkr::where('user_id', $user->id)
            ->whereNotNull('approved_at')
            ->avg('nilai') ?? 0;

        return [
            'current_quarter' => $currentQuarter,
            'kpi_stats' => [
                'total' => $totalKpis,
                'completed' => $completedKpis,
                'pending' => $pendingKpis,
                'draft' => $draftKpis,
                'completion_rate' => $kpiCompletionRate,
                'avg_score' => $avgKpiScore,
            ],
            'okr_stats' => [
                'total' => $totalOkrs,
                'completed' => $completedOkrs,
                'pending' => $pendingOkrs,
                'draft' => $draftOkrs,
                'completion_rate' => $okrCompletionRate,
                'avg_score' => $avgOkrScore,
            ],
            'overall_completion_rate' => $overallCompletionRate,
        ];
    }
}
