<?php

namespace App\Filament\Employee\Widgets;

use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class QuickActions extends Widget
{
    protected static ?string $heading = 'Quick Actions';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 1;

    protected static string $view = 'filament.employee.widgets.quick-actions';

    public function getViewData(): array
    {
        $user = Auth::user();
        
        $pendingKpiCount = KelolaKPI::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->whereDoesntHave('realisasiKpi', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        $pendingOkrCount = KelolaOKR::where('user_id', $user->id)
            ->where('assignment_type', 'individual')
            ->whereDoesntHave('realisasiOkr', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->count();

        return [
            'pendingKpiCount' => $pendingKpiCount,
            'pendingOkrCount' => $pendingOkrCount,
        ];
    }
}
