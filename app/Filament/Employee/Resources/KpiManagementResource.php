<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\KpiManagementResource\Pages;
use App\Models\KelolaKPI;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class KpiManagementResource extends Resource
{
    protected static ?string $model = KelolaKPI::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationGroup = 'Manajemen KPI';
    protected static ?string $navigationLabel = 'KPI Management';
    protected static ?int $navigationSort = 1;

    /**
     * Get navigation badge untuk menampilkan jumlah KPI employee
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        $count = static::getEloquentQuery()->count();
        
        return $count > 0 ? (string) $count : '0';
    }

    /**
     * Warna badge navigation untuk employee
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = (int) static::getNavigationBadge();
        
        if ($count > 10) {
            return 'warning'; // Kuning jika banyak KPI
        } elseif ($count > 5) {
            return 'info'; // Biru jika sedang
        } elseif ($count > 0) {
            return 'success'; // Hijau jika ada
        }
        
        return 'gray'; // Abu-abu jika kosong
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        return parent::getEloquentQuery()
            ->where('user_id', $user->id) // Hanya KPI yang assigned ke employee
            ->where('tipe', 'LIKE', 'kpi%') // Support semua tipe KPI
            ->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('activity')
                    ->label('Nama KPI')
                    ->required()
                    ->disabled(fn ($record) => $record && !$record->is_editable),

                Forms\Components\Select::make('tipe')
                    ->label('Tipe KPI')
                    ->options([
                        'kpi divisi' => 'KPI Divisi',
                        'kpi individu' => 'KPI Individu',
                    ])
                    ->disabled(),

                Forms\Components\Textarea::make('output')
                    ->label('Target/Output')
                    ->required()
                    ->rows(3)
                    ->disabled(fn ($record) => $record && !$record->is_editable),

                Forms\Components\TextInput::make('progress')
                    ->label('Progress (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->default(0)
                    ->helperText('Update progress realisasi KPI'),

                Forms\Components\TextInput::make('realisasi')
                    ->label('Target Realisasi (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->default(100)
                    ->disabled(fn ($record) => $record && !$record->is_editable),

                Forms\Components\TextInput::make('timeline_realisasi')
                    ->label('Timeline Realisasi (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->default(100)
                    ->helperText('Persentase ketepatan waktu'),

                Forms\Components\TextInput::make('periode')
                    ->label('Periode')
                    ->placeholder('Q1-2025, Q2-2025, dll')
                    ->disabled(fn ($record) => $record && !$record->is_editable),

                Forms\Components\TextInput::make('timeline')
                    ->label('Timeline')
                    ->placeholder('Januari - Maret 2025')
                    ->disabled(fn ($record) => $record && !$record->is_editable),

                Forms\Components\Select::make('assignment_type')
                    ->label('Assignment Type')
                    ->options([
                        'individual' => 'Individual',
                        'team' => 'Team',
                        'divisi' => 'Divisi',
                    ])
                    ->disabled(),

                Forms\Components\Select::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama')
                    ->disabled(),

                Forms\Components\Select::make('user_id')
                    ->label('Assigned To')
                    ->relationship('user', 'name')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ])
                    ->required()
                    ->default('active'),

                Forms\Components\Select::make('priority')
                    ->label('Priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ])
                    ->default('medium')
                    ->disabled(fn ($record) => $record && !$record->is_editable),

                Forms\Components\Textarea::make('notes')
                    ->label('Notes/Keterangan')
                    ->rows(3)
                    ->placeholder('Catatan progress atau kendala...')
                    ->helperText('Tambahkan catatan progress, kendala, atau informasi penting lainnya'),

                Forms\Components\Toggle::make('is_editable')
                    ->label('Dapat Diedit')
                    ->helperText('Tandai jika KPI masih dapat diedit')
                    ->default(true)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity')
                    ->label('Nama KPI')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe KPI')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'kpi divisi' => 'success',
                        'kpi individu' => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('output')
                    ->label('Target/Output')
                    ->limit(50)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'warning',
                        $state >= 50 => 'info',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('realisasi')
                    ->label('Target Realisasi')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('timeline_realisasi')
                    ->label('Timeline Realisasi')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('achievement_rate')
                    ->label('Achievement Rate')
                    ->getStateUsing(function ($record) {
                        return $record->achievement_rate;
                    })
                    ->suffix('%')
                    ->color(fn ($state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'warning',
                        $state >= 50 => 'info',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('performance_score')
                    ->label('Performance Score')
                    ->getStateUsing(function ($record) {
                        return $record->performance_score;
                    })
                    ->color(fn ($state): string => match (true) {
                        $state >= 4.0 => 'success',
                        $state >= 3.0 => 'warning',
                        $state >= 2.0 => 'info',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('timeline')
                    ->label('Timeline')
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'primary' => 'active',
                        'success' => 'completed',
                        'warning' => 'on_hold',
                        'danger' => 'cancelled',
                    ]),

                Tables\Columns\BadgeColumn::make('priority')
                    ->label('Priority')
                    ->colors([
                        'gray' => 'low',
                        'primary' => 'medium',
                        'warning' => 'high',
                        'danger' => 'critical',
                    ]),

                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Assigned To')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_editable')
                    ->label('Editable')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'on_hold' => 'On Hold',
                        'cancelled' => 'Cancelled',
                    ]),
                    
                Tables\Filters\SelectFilter::make('priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'critical' => 'Critical',
                    ]),
                    
                Tables\Filters\SelectFilter::make('tipe')
                    ->options([
                        'kpi divisi' => 'KPI Divisi',
                        'kpi individu' => 'KPI Individu',
                    ]),
                    
                Tables\Filters\Filter::make('progress')
                    ->form([
                        Forms\Components\TextInput::make('progress_from')
                            ->label('Progress From (%)')
                            ->numeric(),
                        Forms\Components\TextInput::make('progress_to')
                            ->label('Progress To (%)')
                            ->numeric(),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['progress_from'],
                                fn (Builder $query, $value): Builder => $query->where('progress', '>=', $value),
                            )
                            ->when(
                                $data['progress_to'],
                                fn (Builder $query, $value): Builder => $query->where('progress', '<=', $value),
                            );
                    }),
                    
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\TextInput::make('periode')
                            ->label('Periode')
                            ->placeholder('Q1-2025, Q2-2025, dll'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['periode'],
                                fn (Builder $query, $value): Builder => $query->where('periode', 'like', "%{$value}%"),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading('Tidak ada KPI')
            ->emptyStateDescription('Anda belum memiliki KPI yang ditetapkan.')
            ->emptyStateIcon('heroicon-o-chart-bar');
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
            'index' => Pages\ListKpiManagement::route('/'),
            'create' => Pages\CreateKpiManagement::route('/create'),
            'edit' => Pages\EditKpiManagement::route('/{record}/edit'),
            'view' => Pages\ViewKpiManagement::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Employees cannot create KPIs
    }

    public static function canEdit($record): bool
    {
        return true; // Allow employees to edit their KPIs
    }

    public static function canDelete($record): bool
    {
        return false; // Employees cannot delete KPIs
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}