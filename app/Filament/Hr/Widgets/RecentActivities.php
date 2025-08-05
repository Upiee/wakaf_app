<?php

namespace App\Filament\Hr\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RecentActivities extends BaseWidget
{
    protected static ?string $heading = 'Recent Performance Activities';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function getTableRecordKey($record): string
    {
        return $record->id ?? uniqid();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getRecentActivitiesQuery())
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Employee')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('divisi_name')
                    ->label('Division')
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('activity_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'KPI' => 'success',
                        'OKR' => 'info',
                        default => 'gray',
                    }),
                    
                Tables\Columns\TextColumn::make('activity_name')
                    ->label('Activity')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 50) {
                            return null;
                        }
                        return $state;
                    }),
                    
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Score')
                    ->suffix('%')
                    ->badge()
                    ->color(function ($state) {
                        return $state >= 90 ? 'success' : ($state >= 75 ? 'warning' : 'danger');
                    }),
                    
                Tables\Columns\TextColumn::make('periode')
                    ->label('Period')
                    ->badge()
                    ->color('gray'),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->paginated([10, 25])
            ->defaultPaginationPageOption(10)
            ->filters([
                Tables\Filters\SelectFilter::make('activity_type')
                    ->options([
                        'KPI' => 'KPI',
                        'OKR' => 'OKR',
                    ])
                    ->label('Activity Type'),
                    
                Tables\Filters\SelectFilter::make('periode')
                    ->options([
                        'Q1-2025' => 'Q1 2025',
                        'Q2-2025' => 'Q2 2025',
                        'Q3-2025' => 'Q3 2025',
                        'Q4-2025' => 'Q4 2025',
                    ])
                    ->label('Period'),
            ]);
    }

    private function getRecentActivitiesQuery(): Builder
    {
        // Union KPI and OKR data with unique identifiers
        $kpiQuery = RealisasiKpi::query()
            ->join('users', 'realisasi_kpis.user_id', '=', 'users.id')
            ->join('divisis', 'users.divisi_id', '=', 'divisis.id')
            ->join('kelola__k_p_i_s', 'realisasi_kpis.kpi_id', '=', 'kelola__k_p_i_s.id')
            ->select([
                DB::raw("CONCAT('kpi_', realisasi_kpis.id) as id"),
                'users.name as user_name',
                'divisis.nama as divisi_name',
                DB::raw("'KPI' as activity_type"),
                'kelola__k_p_i_s.activity as activity_name',
                'realisasi_kpis.nilai',
                'realisasi_kpis.periode',
                'realisasi_kpis.updated_at'
            ]);

        $okrQuery = RealisasiOkr::query()
            ->join('users', 'realisasi_okrs.user_id', '=', 'users.id')
            ->join('divisis', 'users.divisi_id', '=', 'divisis.id')
            ->join('kelola__o_k_r_s', 'realisasi_okrs.okr_id', '=', 'kelola__o_k_r_s.id')
            ->select([
                DB::raw("CONCAT('okr_', realisasi_okrs.id) as id"),
                'users.name as user_name',
                'divisis.nama as divisi_name',
                DB::raw("'OKR' as activity_type"),
                'kelola__o_k_r_s.activity as activity_name',
                'realisasi_okrs.nilai',
                'realisasi_okrs.periode',
                'realisasi_okrs.updated_at'
            ]);

        return $kpiQuery->union($okrQuery);
    }
}
