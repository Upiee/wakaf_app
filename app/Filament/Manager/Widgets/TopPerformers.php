<?php

namespace App\Filament\Manager\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class TopPerformers extends BaseWidget
{
    protected static ?string $heading = 'Top Performers This Month';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $currentMonth = now()->format('Y-m');
        $userDivisi = Auth::user()->divisi_id;
        
        return User::query()
            ->where('divisi_id', $userDivisi)
            ->where('id', '!=', Auth::id())
            ->whereHas('realisasiKpis', function ($query) use ($currentMonth) {
                $query->where('periode', 'like', "%{$currentMonth}%")
                      ->whereNotNull('approved_at');
            });
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Employee')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('avg_kpi_score')
                    ->label('Avg KPI Score')
                    ->getStateUsing(function ($record) {
                        return number_format($record->realisasiKpis()
                            ->whereNotNull('approved_at')
                            ->avg('nilai') ?? 0, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $score = (float) str_replace('%', '', $state);
                        return $score >= 90 ? 'success' : ($score >= 75 ? 'warning' : 'danger');
                    }),
                    
                Tables\Columns\TextColumn::make('avg_okr_score')
                    ->label('Avg OKR Score')
                    ->getStateUsing(function ($record) {
                        return number_format($record->realisasiOkrs()
                            ->whereNotNull('approved_at')
                            ->avg('nilai') ?? 0, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $score = (float) str_replace('%', '', $state);
                        return $score >= 90 ? 'success' : ($score >= 75 ? 'warning' : 'danger');
                    }),
                    
                Tables\Columns\TextColumn::make('total_completed')
                    ->label('Completed')
                    ->getStateUsing(function ($record) {
                        $kpi = $record->realisasiKpis()->whereNotNull('approved_at')->count();
                        $okr = $record->realisasiOkrs()->whereNotNull('approved_at')->count();
                        return $kpi + $okr;
                    })
                    ->badge()
                    ->color('primary'),
            ])
            ->defaultSort('name')
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
