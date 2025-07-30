<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\KelolaOKRResource\Pages;
use App\Models\KelolaOKR;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class KelolaOKRResource extends Resource
{
    protected static ?string $model = KelolaOKR::class;
    protected static ?string $navigationIcon = 'heroicon-o-flag';
    protected static ?string $navigationGroup = 'Manajemen KPI';
    protected static ?string $navigationLabel = 'Daftar OKR';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pilih Parent / KPI Divisi')
                    ->description('Jika ini adalah KPI utama, biarkan kosong. Jika ini adalah sub-KPI, pilih KPI induk.')
                    ->schema([
                        Forms\Components\Select::make('parent_id')
                            ->label('KPI Induk')
                            // ->relationship('parent', 'activity')
                            ->options(KelolaOKR::options())
                            ->searchable()
                            ->preload()
                            ->placeholder('Pilih KPI Induk (jika ada)')
                            ->helperText('Pilih KPI induk jika ini adalah sub-KPI'),
                    ]),

                Forms\Components\Section::make('Informasi Dasar OKR')
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
                            ->relationship('user', 'name')
                            ->getOptionLabelFromRecordUsing(fn($record) => '#' . $record->id . ' ' . $record->name . ' (' . ($record->divisi->nama ?? 'No Division') . ')')
                            ->searchable(['name', 'email'])
                            ->preload()
                            ->visible(fn(callable $get) => $get('assignment_type') === 'individual')
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
                            ->label('ID OKR')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: OKR-2025-001')
                            ->helperText('ID unik untuk OKR ini, gunakan format yang konsisten')
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

                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_editable')
                            ->label('Dapat Diedit')
                            ->default(true)
                            ->helperText('Jika dimatikan, OKR ini tidak dapat diedit oleh user lain'),
                    ]),
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
                Tables\Actions\Action::make('duplicate')
                    ->label('Duplikasi')
                    ->icon('heroicon-o-document-duplicate')
                    ->color('info')
                    ->action(function (KelolaOKR $record) {
                        $newRecord = $record->replicate();
                        $newRecord->activity = $record->activity . ' (Copy)';
                        $newRecord->save();

                        \Filament\Notifications\Notification::make()
                            ->title('OKR berhasil diduplikasi')
                            ->success()
                            ->send();
                    })
                    ->requiresConfirmation(),
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
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKelolaOKRS::route('/'),
            'create' => Pages\CreateKelolaOKR::route('/create'),
            'edit' => Pages\EditKelolaOKR::route('/{record}/edit'),
        ];
    }
    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen KPI';
    }

    public static function getModelLabel(): string
    {
        return 'OKR';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Daftar OKR';
    }

    public static function getNavigationLabel(): string
    {
        return 'Daftar OKR';
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
