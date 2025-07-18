<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\RealisasiKpiResource\Pages;
use App\Models\RealisasiKpi;
use App\Models\KelolaKPI;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class RealisasiKpiResource extends Resource
{
    protected static ?string $model = KelolaKPI::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Report';
    protected static ?string $navigationLabel = 'Realisasi KPI';
    protected static ?int $navigationSort = 1;

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
    // Query untuk hanya menampilkan KPI divisi manager yang login
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        return parent::getEloquentQuery()
            ->where('divisi_id', $user->divisi_id) // Hanya divisi manager
            ->where(function ($query) {
                // Tampilkan KPI bertipe 'kpi' (termasuk 'kpi divisi', 'kpi individu', dll)
                $query->where('tipe', 'LIKE', '%kpi%')
                      ->orWhere('tipe', 'kpi');
            })
            ->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Tampilan informasi KPI (read-only)
                Forms\Components\TextInput::make('activity')
                    ->label('Aktivitas KPI')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('output')
                    ->label('Target Output')
                    ->disabled()
                    ->dehydrated(false),

                Forms\Components\TextInput::make('bobot')
                    ->label('Bobot (%)')
                    ->disabled()
                    ->dehydrated(false)
                    ->suffix('%'),

                Forms\Components\TextInput::make('progress')
                    ->label('Progress Realisasi (%)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->helperText('Update progress realisasi KPI'),

                Forms\Components\TextInput::make('realisasi')
                    ->label('Nilai Realisasi Aktual')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->helperText('Masukkan nilai realisasi aktual'),

                Forms\Components\Textarea::make('notes')
                    ->label('Catatan Progress')
                    ->placeholder('Catatan atau penjelasan tambahan untuk realisasi ini...')
                    ->rows(3),

                Forms\Components\Select::make('status')
                    ->label('Status KPI')
                    ->options([
                        'draft' => 'Draft',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'archived' => 'Archived',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Informasi Dasar
                Tables\Columns\TextColumn::make('periode')
                    ->label('Quartal')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                // Target KPI Information
                Tables\Columns\TextColumn::make('activity')
                    ->label('Target KPI')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->activity)
                    ->searchable(),

                // Tipe KPI
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color('primary'),

                // Bobot KPI
                Tables\Columns\TextColumn::make('bobot')
                    ->label('Bobot')
                    ->suffix('%')
                    ->alignCenter()
                    ->sortable(),

                // Target Value
                Tables\Columns\TextColumn::make('realisasi')
                    ->label('Target')
                    ->suffix('%')
                    ->alignCenter()
                    ->badge()
                    ->color('info'),

                // Progress Actual
                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress Aktual')
                    ->suffix('%')
                    ->sortable()
                    ->weight('bold')
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info', 
                        $state >= 70 => 'warning',
                        $state >= 60 => 'danger',
                        default => 'gray'
                    }),

                // Progress Calculation (computed)
                Tables\Columns\TextColumn::make('timeline_realisasi')
                    ->label('Timeline Score')
                    ->suffix('%')
                    ->badge()
                    ->color('info'),

                // Achievement Rate
                Tables\Columns\TextColumn::make('achievement_rate')
                    ->label('Achievement')
                    ->getStateUsing(function ($record) {
                        $target = $record->realisasi ?? 100;
                        $actual = $record->progress ?? 0;
                        return $target > 0 ? round(($actual / $target) * 100, 1) . '%' : 'N/A';
                    })
                    ->badge()
                    ->color(fn ($state) => match(true) {
                        str_contains($state, 'N/A') => 'gray',
                        (float) str_replace('%', '', $state) >= 100 => 'success',
                        (float) str_replace('%', '', $state) >= 80 => 'info',
                        (float) str_replace('%', '', $state) >= 60 => 'warning',
                        default => 'danger'
                    }),

                // Status & Tracking
                Tables\Columns\IconColumn::make('is_editable')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->tooltip(fn ($state) => $state ? 'Masih dapat diubah' : 'Data sudah final'),

                // Timeline
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terakhir Update')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // Filter Dasar
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Filter Quartal')
                    ->options([
                        'Q1-2025' => 'Q1 2025',
                        'Q2-2025' => 'Q2 2025',
                        'Q3-2025' => 'Q3 2025',
                        'Q4-2025' => 'Q4 2025',
                        'H1-2025' => 'H1 2025',
                        'H2-2025' => 'H2 2025',
                        'Tahunan-2025' => 'Tahunan 2025',
                    ]),
                   

                // Filter Performance Range
                Tables\Filters\SelectFilter::make('performance_range')
                    ->label('Level Performance')
                    ->query(function (Builder $query, array $data): Builder {
                        if (! $data['value']) return $query;
                        
                        return match($data['value']) {
                            'excellent' => $query->where('progress', '>=', 90),
                            'good' => $query->whereBetween('progress', [80, 89]),
                            'average' => $query->whereBetween('progress', [70, 79]),
                            'poor' => $query->whereBetween('progress', [60, 69]),
                            'critical' => $query->where('progress', '<', 60),
                            default => $query,
                        };
                    })
                    ->options([
                        'excellent' => '🟢 Excellent (90-100%)',
                        'good' => '🔵 Good (80-89%)',
                        'average' => '🟡 Average (70-79%)',
                        'poor' => '🟠 Poor (60-69%)',
                        'critical' => '🔴 Critical (<60%)',
                    ]),

                // Filter by KPI
                Tables\Filters\SelectFilter::make('kpi_id')
                    ->label('Filter KPI')
                    ->options(function () {
                        $user = Auth::user();
                        return KelolaKPI::where('divisi_id', $user->divisi_id)
                                       ->where('assignment_type', 'divisi')
                                       ->whereNull('user_id')
                                       ->where(function($q) {
                                           $q->where('tipe', 'kpi')
                                             ->orWhere('tipe', 'kpi divisi');
                                       })
                                       ->pluck('activity', 'id');
                    })
                    ->searchable()
                    ->preload(),

                // Filter Status
                Tables\Filters\TernaryFilter::make('is_cutoff')
                    ->label('Status Data')
                    ->trueLabel('✅ Sudah Final')
                    ->falseLabel('⏳ Draft/Editable')
                    ->placeholder('Semua Status'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->visible(fn ($record) => !$record->is_cutoff),
                Tables\Actions\Action::make('finalize')
                    ->label('Finalisasi')
                    ->icon('heroicon-o-lock-closed')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Finalisasi Data Realisasi KPI')
                    ->modalDescription('Setelah data difinalisasi, data tidak dapat diubah lagi. Yakin?')
                    ->action(fn ($record) => $record->update(['is_cutoff' => true]))
                    ->visible(fn ($record) => !$record->is_cutoff),
                Tables\Actions\Action::make('unlock')
                    ->label('Buka Kunci')
                    ->icon('heroicon-o-lock-open')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['is_cutoff' => false]))
                    ->visible(fn ($record) => $record->is_cutoff),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('Hapus Terpilih'),
                    Tables\Actions\BulkAction::make('finalize_bulk')
                        ->label('Finalisasi Massal')
                        ->icon('heroicon-o-lock-closed')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn ($records) => $records->each->update(['is_cutoff' => true])),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRealisasiKpis::route('/'),
            'create' => Pages\CreateRealisasiKpi::route('/create'),
            'edit' => Pages\EditRealisasiKpi::route('/{record}/edit'),
        ];
    }
}
