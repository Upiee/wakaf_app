<?php

namespace App\Filament\Employee\Widgets;

use App\Models\RealisasiOkr;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RecentOkrActivities extends BaseWidget
{
    protected static ?string $heading = 'Recent OKR Activities';
    protected static ?int $sort = 6;
    protected int | string | array $columnSpan = 'full';

    protected function getTableQuery(): Builder
    {
        $user = Auth::user();
        
        return RealisasiOkr::where('user_id', $user->id)
            ->with(['okr'])
            ->orderByDesc('updated_at');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->getTableQuery())
            ->columns([
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Type')
                    ->getStateUsing(fn () => 'OKR')
                    ->color('success'),
                    
                Tables\Columns\TextColumn::make('okr.activity')
                    ->label('Activity')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->okr->activity ?? 'N/A';
                    }),
                    
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Score')
                    ->badge()
                    ->color(fn ($state) => $state >= 90 ? 'success' : ($state >= 75 ? 'warning' : 'danger'))
                    ->suffix('%'),
                    
                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status')
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
