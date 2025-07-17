<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\OkrManagementResource\Pages;
use App\Filament\Employee\Resources\OkrManagementResource\RelationManagers;
use App\Models\KelolaOKR;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class OkrManagementResource extends Resource
{
    protected static ?string $model = KelolaOKR::class;

    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Manajemen KPI';
    protected static ?string $navigationLabel = 'OKR Management';
    protected static ?int $navigationSort = 2;

    /**
     * Get navigation badge untuk menampilkan jumlah OKR employee
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
        
        if ($count > 8) {
            return 'warning'; // Kuning jika banyak OKR
        } elseif ($count > 4) {
            return 'info'; // Biru jika sedang
        } elseif ($count > 0) {
            return 'success'; // Hijau jika ada
        }
        
        return 'gray'; // Abu-abu jika kosong
    }

    // Query untuk Employee - hanya lihat OKR yang di-assign ke dirinya
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        return parent::getEloquentQuery()
            ->where('user_id', $user->id) // Hanya OKR yang assigned ke employee
            ->where('tipe', 'LIKE', 'okr%') // Support semua tipe OKR
            ->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('activity')
                    ->label('Nama OKR')
                    ->required()
                    ->disabled(fn ($record) => $record && !$record->is_editable),

                Forms\Components\Select::make('tipe')
                    ->label('Tipe OKR')
                    ->options([
                        'okr divisi' => 'OKR Divisi',
                        'okr individu' => 'OKR Individu',
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
                    ->helperText('Update progress realisasi OKR'),

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
                    ->helperText('Tandai jika OKR masih dapat diedit')
                    ->default(true)
                    ->disabled(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('activity')
                    ->label('Nama OKR')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe OKR')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'okr divisi' => 'success',
                        'okr individu' => 'warning',
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
                        'okr divisi' => 'OKR Divisi',
                        'okr individu' => 'OKR Individu',
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
            'index' => Pages\ListOkrManagement::route('/'),
            'create' => Pages\CreateOkrManagement::route('/create'),
            'edit' => Pages\EditOkrManagement::route('/{record}/edit'),
            'view' => Pages\ViewOkrManagement::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Employee tidak bisa create OKR
    }

    public static function canEdit($record): bool
    {
        return true; // Allow employees to edit their OKR
    }

    public static function canDelete($record): bool
    {
        return false; // Employee tidak bisa delete OKR
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
