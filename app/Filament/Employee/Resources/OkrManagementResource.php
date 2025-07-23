<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\OkrManagementResource\Pages;
use App\Filament\Employee\Resources\OkrManagementResource\RelationManagers;
use App\Models\KelolaOKR;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
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
                Forms\Components\Section::make('Informasi Dasar OKR')
                    ->schema([
                        Forms\Components\TextInput::make('activity')
                            ->required()
                            ->label('Aktivitas/Deskripsi KPI')
                            ->placeholder('Contoh: Meningkatkan efisiensi operasional divisi')
                            ->disabled(fn($record) => $record && !$record->is_editable),
                    ])->columns(2),


                Forms\Components\Section::make('Detail Progress OKR')
                    ->description('Tambahkan detail progress dan dokumentasi untuk OKR ini')
                    ->schema([
                        Forms\Components\Repeater::make('subActivities')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('output')
                                    ->label('Output Target')
                                    ->required()
                                    ->placeholder('Contoh: 15% conversion rate Q1')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('bobot')
                                    ->label('Bobot (%)')
                                    ->numeric()
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->default(0)
                                    ->live()
                                    ->afterStateUpdated(function ($state, $component) {
                                        // Auto calculate total bobot validation
                                        $repeater = $component->getContainer()->getParentComponent();
                                        $items = $repeater->getState();
                                        $totalBobot = 0;
                                        foreach ($items as $item) {
                                            if (isset($item['bobot'])) {
                                                $totalBobot += (float) $item['bobot'];
                                            }
                                        }
                                        // Bisa ditambahkan validasi disini
                                    }),
                                Forms\Components\Textarea::make('indikator')
                                    ->label('Indikator Progress')
                                    ->required()
                                    ->rows(2)
                                    ->placeholder('Cara mengukur progress...')
                                    ->columnSpan(2),
                                Forms\Components\TextInput::make('progress_percentage')
                                    ->label('Progress (%)')
                                    ->numeric()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->default(0),
                                Forms\Components\Select::make('status')
                                    ->label('Status')
                                    ->options([
                                        'not_started' => 'Belum Dimulai',
                                        'in_progress' => 'Sedang Berjalan',
                                        'completed' => 'Selesai',
                                        'on_hold' => 'Ditunda',
                                    ])
                                    ->default('not_started')
                                    ->required(),
                                Forms\Components\Textarea::make('dokumen')
                                    ->label('Dokumen/Link')
                                    ->rows(1)
                                    ->placeholder('Link atau nama dokumen...')
                                    ->columnSpan(2),
                            ])
                            ->columns(4)
                            ->defaultItems(1)
                            ->addActionLabel('+ Tambah Detail Progress')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->helperText('💡 Tambahkan detail progress dengan bobot untuk OKR ini. Total bobot harus = 100%'),
                    ]),

                // Form Konfigurasi    
                Forms\Components\Section::make('Progress & Timeline')
                    ->schema([
                        Forms\Components\TextInput::make('progress')
                            ->numeric()
                            ->label('Progress Saat Ini (%)')
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0)
                            ->helperText('Progress akan dihitung otomatis dari sub-activities'),
                        Forms\Components\Select::make('periode')
                            ->label('Periode')
                            ->options([
                                '2025-Q1' => 'Q1 2025',
                                '2025-Q2' => 'Q2 2025',
                                '2025-Q3' => 'Q3 2025',
                                '2025-Q4' => 'Q4 2025',
                                '2025-H1' => 'H1 2025',
                                '2025-H2' => 'H2 2025',
                                '2025' => 'Tahunan 2025',
                            ])
                            ->searchable(),
                        Forms\Components\TextInput::make('timeline')
                            ->label('Timeline Target')
                            ->placeholder('Contoh: Akhir Desember 2025'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('code_id')
                    ->label('ID OKR')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'okr divisi' => 'primary',
                        'okr individu' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                // Form Konfigurasi
                Tables\Columns\TextColumn::make('activity')
                    ->label('Objective (Tujuan)')
                    ->searchable()
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        if (strlen($state) <= 40) {
                            return null;
                        }
                        return $state;
                    }),
                Tables\Columns\TextColumn::make('assignment_type')
                    ->label('Assignment')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'divisi' => 'success',
                        'individual' => 'warning',
                        default => 'gray',
                    })
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'divisi' => 'Divisi',
                        'individual' => 'Individual',
                        default => $state,
                    }),
                Tables\Columns\TextColumn::make('target_info')
                    ->label('Target')
                    ->getStateUsing(function ($record) {
                        if ($record->assignment_type === 'divisi' && $record->divisi) {
                            $count = $record->divisi->users()->count();
                            return $record->divisi->nama . " ({$count} members)";
                        } elseif ($record->assignment_type === 'individual' && $record->user) {
                            return $record->user->name;
                        }
                        return 'Not assigned';
                    })
                    ->icon(fn($record) => $record->assignment_type === 'divisi' ? 'heroicon-o-building-office' : 'heroicon-o-user'),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'draft' => 'gray',
                        'active' => 'success',
                        'completed' => 'info',
                        'archived' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('priority')
                    ->label('Priority')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'high' => 'danger',
                        'medium' => 'warning',
                        'low' => 'success',
                        default => 'gray',
                    }),


                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->achievement ?? 0)
                    ->color(fn($state) => $state >= 80 ? 'success' : ($state >= 60 ? 'warning' : 'danger'))
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->badge()
                    ->color('info'),
                Tables\Columns\IconColumn::make('is_editable')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil')
                    ->falseIcon('heroicon-o-lock-closed')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tipe')
                    ->options([
                        'okr divisi' => 'OKR Divisi',
                        'okr individu' => 'OKR Individu',
                    ])
                    ->label('Tipe OKR'),
                Tables\Filters\Filter::make('progress_range')
                    ->form([
                        Forms\Components\Select::make('progress_status')
                            ->label('Status Progress')
                            ->options([
                                'low' => 'Di Bawah 60% (Perlu Perhatian)',
                                'medium' => '60-79% (On Track)',
                                'high' => '80%+ (Excellent)',
                            ])
                    ])
                    ->query(function ($query, array $data) {
                        if (!isset($data['progress_status'])) {
                            return $query;
                        }

                        return match ($data['progress_status']) {
                            'low' => $query->where('progress', '<', 60),
                            'medium' => $query->whereBetween('progress', [60, 79]),
                            'high' => $query->where('progress', '>=', 80),
                            default => $query,
                        };
                    }),
                Tables\Filters\TernaryFilter::make('is_editable')
                    ->label('Status Edit')
                    ->boolean()
                    ->trueLabel('Dapat Diedit')
                    ->falseLabel('Terkunci'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Detail'),
                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->is_editable)
                    ->label('Edit'),
                Tables\Actions\Action::make('toggle_edit')
                    ->label(fn($record) => $record->is_editable ? 'Kunci' : 'Buka Kunci')
                    ->icon(fn($record) => $record->is_editable ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn($record) => $record->is_editable ? 'danger' : 'success')
                    ->action(function (KelolaOKR $record) {
                        $record->update(['is_editable' => !$record->is_editable]);

                        \Filament\Notifications\Notification::make()
                            ->title($record->is_editable ? 'OKR dibuka untuk edit' : 'OKR dikunci dari edit')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_lock')
                        ->label('Kunci Terpilih')
                        ->icon('heroicon-o-lock-closed')
                        ->color('danger')
                        ->action(function ($records) {
                            $records->each(fn($record) => $record->update(['is_editable' => false]));

                            \Filament\Notifications\Notification::make()
                                ->title('OKR terpilih berhasil dikunci')
                                ->success()
                                ->send();
                        })
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('bulk_unlock')
                        ->label('Buka Kunci Terpilih')
                        ->icon('heroicon-o-lock-open')
                        ->color('success')
                        ->action(function ($records) {
                            $records->each(fn($record) => $record->update(['is_editable' => true]));

                            \Filament\Notifications\Notification::make()
                                ->title('OKR terpilih berhasil dibuka')
                                ->success()
                                ->send();
                        }),
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
