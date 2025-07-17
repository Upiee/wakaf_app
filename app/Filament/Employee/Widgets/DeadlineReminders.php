<?php

namespace App\Filament\Employee\Widgets;

use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DeadlineReminders extends Widget
{
    protected static ?string $heading = 'Task Reminders';
    protected static ?int $sort = 5;
    protected int | string | array $columnSpan = 1;

    protected static string $view = 'filament.employee.widgets.deadline-reminders';

    public function getViewData(): array
    {
        $user = Auth::user();
        $currentQuarter = 'Q' . now()->quarter . '-' . now()->year;
        
        // Get pending KPIs (without realization)
        $pendingKpis = KelolaKPI::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->whereDoesntHave('realisasiKpi', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($kpi) {
                return [
                    'type' => 'KPI',
                    'title' => $kpi->activity,
                    'periode' => $kpi->periode,
                    'priority' => $kpi->priority ?? 'medium',
                    'progress' => $kpi->progress ?? 0,
                    'is_urgent' => $kpi->priority === 'high',
                ];
            });

        // Get pending OKRs (without realization)
        $pendingOkrs = KelolaOKR::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->whereDoesntHave('realisasiOkr', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->get()
            ->map(function ($okr) {
                return [
                    'type' => 'OKR',
                    'title' => $okr->activity,
                    'periode' => $okr->periode,
                    'priority' => $okr->priority ?? 'medium',
                    'progress' => $okr->progress ?? 0,
                    'is_urgent' => $okr->priority === 'high',
                ];
            });

        // Get draft realizations (not yet submitted)
        $draftKpis = RealisasiKpi::where('user_id', $user->id)
            ->where('is_cutoff', false)
            ->with('kpi')
            ->get()
            ->map(function ($realisasi) {
                return [
                    'type' => 'KPI Draft',
                    'title' => $realisasi->kpi->activity ?? 'Unknown KPI',
                    'periode' => $realisasi->periode,
                    'priority' => 'medium',
                    'progress' => $realisasi->nilai ?? 0,
                    'is_urgent' => false,
                ];
            });

        $draftOkrs = RealisasiOkr::where('user_id', $user->id)
            ->where('is_cutoff', false)
            ->with('okr')
            ->get()
            ->map(function ($realisasi) {
                return [
                    'type' => 'OKR Draft',
                    'title' => $realisasi->okr->activity ?? 'Unknown OKR',
                    'periode' => $realisasi->periode,
                    'priority' => 'medium',
                    'progress' => $realisasi->nilai ?? 0,
                    'is_urgent' => false,
                ];
            });

        // Combine all pending items
        $allPendingItems = $pendingKpis->concat($pendingOkrs)
            ->concat($draftKpis)
            ->concat($draftOkrs)
            ->sortByDesc('is_urgent')
            ->take(8);

        // Count totals
        $totalPending = $pendingKpis->count() + $pendingOkrs->count();
        $totalDrafts = $draftKpis->count() + $draftOkrs->count();

        return [
            'pending_items' => $allPendingItems,
            'total_pending' => $totalPending,
            'total_drafts' => $totalDrafts,
            'current_quarter' => $currentQuarter,
        ];
    }
}
