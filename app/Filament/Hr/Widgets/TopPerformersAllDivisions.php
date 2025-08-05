<?php

namespace App\Filament\Hr\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class TopPerformersAllDivisions extends BaseWidget
{
    protected static ?string $heading = 'Top Performers Across All Divisions (Q2-2025)';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $currentQuarter = 'Q2-2025';
        
        return User::query()
            ->where('role', '!=', 'hr')
            ->where('role', '!=', 'manager')
            ->where(function($query) use ($currentQuarter) {
                $query->whereHas('realisasiKpis', function ($q) use ($currentQuarter) {
                    $q->where('periode', $currentQuarter);
                })
                ->orWhereHas('realisasiOkrs', function ($q) use ($currentQuarter) {
                    $q->where('periode', $currentQuarter);
                });
            })
            ->leftJoin('realisasi_kpis as rk', function($join) use ($currentQuarter) {
                $join->on('users.id', '=', 'rk.user_id')
                     ->where('rk.periode', '=', $currentQuarter);
            })
            ->leftJoin('realisasi_okrs as ro', function($join) use ($currentQuarter) {
                $join->on('users.id', '=', 'ro.user_id')
                     ->where('ro.periode', '=', $currentQuarter);
            })
            ->leftJoin('divisis as d', 'users.divisi_id', '=', 'd.id')
            ->selectRaw('
                users.id,
                users.name,
                users.email,
                users.created_at,
                users.updated_at,
                d.nama as divisi_nama,
                COALESCE(AVG(rk.nilai), 0) as avg_kpi,
                COALESCE(AVG(ro.nilai), 0) as avg_okr,
                (COALESCE(AVG(rk.nilai), 0) + COALESCE(AVG(ro.nilai), 0)) / 2 as total_score
            ')
            ->groupBy('users.id', 'users.name', 'users.email', 'users.created_at', 'users.updated_at', 'd.nama')
            ->orderByDesc('total_score');
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
                    
                Tables\Columns\TextColumn::make('divisi_nama')
                    ->label('Division')
                    ->badge()
                    ->color('primary')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('total_score')
                    ->label('Total Score')
                    ->getStateUsing(function ($record) {
                        return number_format($record->total_score ?? 0, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $score = (float) str_replace('%', '', $state);
                        return $score >= 95 ? 'success' : ($score >= 85 ? 'warning' : 'danger');
                    })
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('avg_kpi_score')
                    ->label('KPI Score')
                    ->getStateUsing(function ($record) {
                        return number_format($record->avg_kpi ?? 0, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $score = (float) str_replace('%', '', $state);
                        return $score >= 90 ? 'success' : ($score >= 75 ? 'warning' : 'danger');
                    }),
                    
                Tables\Columns\TextColumn::make('avg_okr_score')
                    ->label('OKR Score')
                    ->getStateUsing(function ($record) {
                        return number_format($record->avg_okr ?? 0, 1) . '%';
                    })
                    ->badge()
                    ->color(function ($state) {
                        $score = (float) str_replace('%', '', $state);
                        return $score >= 90 ? 'success' : ($score >= 75 ? 'warning' : 'danger');
                    }),
                    
                Tables\Columns\TextColumn::make('total_completed')
                    ->label('Completed Tasks')
                    ->getStateUsing(function ($record) {
                        $currentQuarter = 'Q2-2025';
                        $kpi = $record->realisasiKpis()->where('periode', $currentQuarter)->count();
                        $okr = $record->realisasiOkrs()->where('periode', $currentQuarter)->count();
                        return $kpi + $okr;
                    })
                    ->badge()
                    ->color('info'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('divisi_id')
                    ->label('Filter by Division')
                    ->relationship('divisi', 'nama')
                    ->searchable()
                    ->preload(),
            ])
            ->defaultSort('total_score', 'desc')
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10);
    }
}
