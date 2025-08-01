<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\MyKpiProgressResource\Pages;
use App\Filament\Manager\Resources\MyKpiProgressResource\RelationManagers;
use App\Models\KelolaKPI;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class MyKpiProgressResource extends Resource
{
    protected static ?string $model = KelolaKPI::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Data Divisi';
    protected static ?string $navigationLabel = 'KPI Divisi Saya';
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

    public static function canCreate(): bool
    {
        return false;
    }

    // Query untuk KPI divisi yang menjadi tanggung jawab manager
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        return parent::getEloquentQuery()
            ->where('divisi_id', $user->divisi_id) // KPI untuk divisi manager
            ->where('tipe', 'LIKE', 'kpi%')
            ->where('assignment_type', 'divisi') // Hanya KPI level divisi
            ->whereNull('user_id') // Tidak di-assign ke user spesifik (divisi level)
            ->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('KPI Information')
                    ->schema([
                        Forms\Components\TextInput::make('activity')
                            ->label('KPI Activity')
                            ->disabled(), // View only - assigned by HR

                        Forms\Components\Textarea::make('output')
                            ->label('Target Output')
                            ->rows(2)
                            ->disabled(), // View only - assigned by HR

                        Forms\Components\TextInput::make('bobot')
                            ->label('Bobot (%)')
                            ->disabled()
                            ->suffix('%'),

                        Forms\Components\TextInput::make('timeline')
                            ->label('Timeline')
                            ->disabled(),

                        Forms\Components\Select::make('periode')
                            ->label('Quartal/Periode')
                            ->options([
                                '2025-Q1' => 'Q1 2025',
                                '2025-Q2' => 'Q2 2025',
                                '2025-Q3' => 'Q3 2025',
                                '2025-04' => 'Q4 2025',
                                '2025-H1' => 'H1 2025',
                                '2025-H2' => 'H2 2025',
                                'Tahunan-2025' => 'Tahunan 2025',
                            ])
                            ->default('2025-Q3')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Progress Input')
                    ->schema([
                        Forms\Components\TextInput::make('progress')
                            ->label('Current Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->required()
                            ->helperText('Update your current progress'),

                        Forms\Components\TextInput::make('realisasi')
                            ->label('Achievement (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%')
                            ->helperText('Actual achievement against target'),

                        Forms\Components\Textarea::make('indikator_progress')
                            ->label('Progress Description')
                            ->rows(3)
                            ->placeholder('Describe what has been achieved...')
                            ->helperText('Explain your progress in detail'),

                        Forms\Components\FileUpload::make('dokumen')
                            ->label('Supporting Documents')
                            ->multiple()
                            ->acceptedFileTypes(['pdf', 'doc', 'docx', 'jpg', 'png'])
                            ->helperText('Upload evidence of your progress'),
                    ])
                    ->columns(2),
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
            'index' => Pages\ListMyKpiProgress::route('/'),
            'create' => Pages\CreateMyKpiProgress::route('/create'),
            'edit' => Pages\EditMyKpiProgress::route('/{record}/edit'),
        ];
    }
}
