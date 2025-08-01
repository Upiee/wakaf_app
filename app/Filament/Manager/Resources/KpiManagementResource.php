<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\KpiManagementResource\Pages;
use App\Filament\Manager\Resources\KpiManagementResource\RelationManagers;
use App\Models\KelolaKPI;
use App\Models\Divisi;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class KpiManagementResource extends Resource
{
    protected static ?string $model = KelolaKPI::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Daftar KPI';

    protected static ?string $modelLabel = 'KPI';

    protected static ?string $pluralModelLabel = 'Daftar KPI';

    protected static ?string $navigationGroup = 'Kelola KPI & OKR';

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

    /**
     * Warna badge navigation
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $count = (int) static::getNavigationBadge();

        if ($count > 20) {
            return 'danger'; // Merah jika banyak
        } elseif ($count > 10) {
            return 'warning'; // Kuning jika sedang
        } elseif ($count > 0) {
            return 'success'; // Hijau jika ada
        }

        return 'gray'; // Abu-abu jika kosong
    }

    // Query untuk Manager - KPI yang di-assign ke divisinya untuk di-manage ke employee
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->where('divisi_id', $user->divisi_id) // KPI untuk divisi manager
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
                Forms\Components\Section::make('Pilih Parent / KPI Divisi')
                    ->description('Jika ini adalah KPI utama, biarkan kosong. Jika ini adalah sub-KPI, pilih KPI induk.')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('KPI Induk')
                            ->options(KelolaKPI::options(Auth::user()->divisi_id))
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih KPI Induk (jika ada)')
                            ->helperText('Pilih KPI induk jika ini adalah sub-KPI'),
                    ]),

                Forms\Components\Section::make('Informasi Dasar KPI')
                    ->schema([
                        Forms\Components\Select::make('assignment_type')
                            ->label('Tipe Assignment')
                            ->options([
                                'divisi' => 'Target ke Divisi',
                                'individual' => 'Target ke Karyawan',
                            ])
                            ->default('divisi')
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                if ($state === 'divisi') {
                                    $set('user_id', null);
                                    $set('tipe', 'kpi divisi');
                                } elseif ($state === 'individual') {
                                    $set('divisi_id', null);
                                    $set('tipe', 'kpi individu');
                                }
                            }),

                        Forms\Components\Select::make('divisi_id')
                            ->label('Pilih Divisi')
                            ->options(
                                \App\Models\Divisi::options()
                            )
                            ->searchable()
                            ->preload()
                            ->visible(fn(callable $get) => $get('assignment_type') === 'divisi')
                            ->helperText(function (callable $get) {
                                if ($get('divisi_id')) {
                                    $divisi = \App\Models\Divisi::find($get('divisi_id'));
                                    $count = $divisi ? $divisi->users()->count() : 0;
                                    return "📊 {$count} karyawan akan menerima KPI ini";
                                }
                                return 'Pilih divisi untuk assignment';
                            }),

                        Forms\Components\Select::make('user_id')
                            ->label('Pilih Karyawan')
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->visible(fn(callable $get) => $get('assignment_type') === 'individual')
                            ->options(function () {
                                $options = [];
                                $user = User::where('divisi_id', Auth::user()->divisi_id)
                                    ->where('is_active', true)
                                    ->get();

                                foreach ($user as $u) {
                                    $options[$u->id] = '#' . $u->id . ' ' . $u->name . ' (' . ($u->divisi->nama ?? 'No Division') . ')';
                                }
                                return $options;
                            })
                            ->helperText('Pilih karyawan spesifik untuk assignment'),

                        Forms\Components\Select::make('priority')
                            ->label('Prioritas')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                            ])
                            ->default('medium')
                            ->hidden(),
                        Forms\Components\TextInput::make('activity')
                            ->required()
                            ->label('Aktivitas/Deskripsi KPI')
                            ->placeholder('Contoh: Meningkatkan efisiensi operasional divisi')
                            ->disabled(fn($record) => $record && !$record->is_editable),

                        Forms\Components\TextInput::make('code_id')
                            ->label('ID KPI')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: KPI-2025-001')
                            ->helperText('ID unik untuk KPI ini, gunakan format yang konsisten')
                            ->disabled(fn($record) => $record && !$record->is_editable),
                    ])->columns(2),

                Forms\Components\Section::make('Detail Progress KPI')
                    ->description('Tambahkan detail progress dan dokumentasi untuk KPI ini')
                    ->schema([
                        Forms\Components\Repeater::make('subActivities')
                            ->relationship()
                            ->schema([
                                Forms\Components\TextInput::make('bobot')
                                    ->numeric()
                                    ->label('Bobot (%)')
                                    ->required()
                                    ->minValue(0)
                                    ->maxValue(100)
                                    ->suffix('%')
                                    ->default(0)
                                    ->helperText('Bobot KPI dalam perhitungan total'),
                                Forms\Components\TextInput::make('output')
                                    ->label('Output Target')
                                    ->required()
                                    ->placeholder('Contoh: 4 laporan per bulan')
                                    ->columnSpan(2),
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
                            ->helperText('💡 Tambahkan detail progress untuk KPI ini'),
                    ]),


                Forms\Components\Section::make('Progress & Timeline')
                    ->schema([
                        Forms\Components\TextInput::make('progress')
                            ->numeric()
                            ->label('Progress Saat Ini (%)')
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->default(0),
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
                            ->placeholder('Contoh: Akhir Juni 2025'),
                    ])->columns(2),

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_editable')
                            ->label('Dapat Diedit')
                            ->default(true)
                            ->helperText('Jika dimatikan, KPI ini tidak dapat diedit oleh user lain'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)
            ->columns([
                Tables\Columns\TextColumn::make('code_id')
                    ->label('ID KPI')
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'kpi divisi' => 'warning',
                        'kpi individu' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('activity')
                    ->label('Aktivitas KPI')
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
                    ->getStateUsing(fn($record) => $record->achievement ?? 0)
                    ->sortable()
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
                        'kpi divisi' => 'KPI Divisi',
                        'kpi individu' => 'KPI Individu',
                    ])
                    ->label('Tipe KPI'),
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
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikasi')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (KelolaKPI $record) {
                        $newRecord = $record->replicate();
                        $newRecord->save();

                        \Filament\Notifications\Notification::make()
                            ->title('KPI berhasil diduplikasi')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
                Tables\Actions\Action::make('toggle_edit')
                    ->label(fn($record) => $record->is_editable ? 'Kunci' : 'Buka Kunci')
                    ->icon(fn($record) => $record->is_editable ? 'heroicon-o-lock-closed' : 'heroicon-o-lock-open')
                    ->color(fn($record) => $record->is_editable ? 'danger' : 'success')
                    ->action(function (KelolaKPI $record) {
                        $record->update(['is_editable' => !$record->is_editable]);

                        \Filament\Notifications\Notification::make()
                            ->title($record->is_editable ? 'KPI dibuka untuk edit' : 'KPI dikunci dari edit')
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
                                ->title('KPI terpilih berhasil dikunci')
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
                                ->title('KPI terpilih berhasil dibuka')
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
            'index' => Pages\ListKpiManagement::route('/'),
            'create' => Pages\CreateKpiManagement::route('/create'),
            'edit' => Pages\EditKpiManagement::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Kelola KPI & OKR';
    }

    public static function getModelLabel(): string
    {
        return 'KPI';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar KPI';
    }

    public static function getNavigationLabel(): string
    {
        return 'Daftar KPI';
    }
}
