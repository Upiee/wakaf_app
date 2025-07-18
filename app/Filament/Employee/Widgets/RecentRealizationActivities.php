<?php

namespace App\Filament\Employee\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RecentRealizationActivities extends BaseWidget
{
    protected static ?string $heading = 'Recent KPI Activities';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        
        return RealisasiKpi::where('user_id', $user->id)
            ->with(['kpi'])
            ->orderByDesc('updated_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->getStateUsing(fn () => 'KPI')
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('kpi.activity')
                    ->label('Activity')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->kpi->activity ?? 'N/A';
                    }),
                    
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Score')
                    ->badge()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 75 ? 'warning' : 'danger'))
                    ->suffix('%'),
                    
                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status')
                    ->getStateUsing(function ($record) {
                        if ($record->approved_at) {
                            return 'approved';
                        } elseif ($record->is_cutoff) {
                            return 'pending';
                        } else {
                            return 'draft';
                        }
                    })
                    ->colors([
                        'danger' => 'draft',
                        'warning' => 'pending',
                        'success' => 'approved',
                    ]),
                    
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
            ->paginated([5, 10])
            ->defaultPaginationPageOption(5);
    }
}
