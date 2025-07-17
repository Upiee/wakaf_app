<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\ScoreResource\Pages;
use App\Models\Score;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Support\Facades\Auth;

class ScoreResource extends Resource
{
    protected static ?string $model = Score::class;
    protected static ?string $navigationGroup = 'Realisasi';
    protected static ?string $navigationLabel = 'Score';
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    /**
     * Get navigation badge untuk menampilkan jumlah KPI
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        
        if (!$user || !$user->divisi_id) {
            return null;
        }
        
        $count = static::getEloquentQuery()->count();
        
        return $count > 0 ? (string) $count : null;
    }
    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Nama Individu')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->sortable()
                    ->searchable()
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('total_nilai')->label('Total Nilai')->sortable(),
                Tables\Columns\TextColumn::make('periode')->label('Periode')->sortable(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListScores::route('/'),
        ];
    }
}
