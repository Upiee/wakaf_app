<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\RealisasiIndividuResource\Pages;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RealisasiIndividuResource extends Resource
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Realisasi';
    protected static ?string $navigationLabel = 'Realisasi Individu';
    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?int $navigationSort = 20;
    protected static ?string $slug = 'realisasi-individu';

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
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', 'employee') // Only employees
            ->with(['divisi', 'realisasiKpis', 'realisasiOkrs'])
            ->orderBy('name');
    }

    public static function canCreate(): bool
    {
        return false; // HR hanya view, tidak create
    }

    public static function canEdit($record): bool
    {
        return false; // HR hanya view, tidak edit
    }

    public static function canDelete($record): bool
    {
        return false; // HR hanya view, tidak delete
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Employee')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_details')
                    ->label('Lihat Detail')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->url(fn ($record) => '#'),
            ])
            ->emptyStateHeading('Belum ada data employee')
            ->emptyStateDescription('Data employee akan muncul di sini')
            ->emptyStateIcon('heroicon-o-user-circle');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRealisasiIndividus::route('/'),
        ];
    }
    
    public static function getPluralModelLabel(): string
    {
        return 'Realisasi Individu';
    }
}
