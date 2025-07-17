<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\MyKpiProgressResource\Pages;
use App\Filament\Manager\Resources\MyKpiProgressResource\RelationManagers;
use App\Models\KelolaKPI;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
            ->columns([
                Tables\Columns\TextColumn::make('periode')
                    ->label('Quartal')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

                Tables\Columns\TextColumn::make('activity')
                    ->label('KPI Activity')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('output')
                    ->label('Target')
                    ->limit(25),

                Tables\Columns\TextColumn::make('bobot')
                    ->label('Bobot')
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('progress')
                    ->label('Progress')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        if ($state >= 100) return 'success';
                        if ($state >= 75) return 'warning';
                        if ($state >= 50) return 'info';
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('realisasi')
                    ->label('Achievement')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        if ($state >= 100) return 'success';
                        if ($state >= 75) return 'warning';
                        if ($state >= 50) return 'info';
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('timeline')
                    ->label('Timeline')
                    ->limit(20),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'active' => 'success', 
                        'completed' => 'primary',
                        'overdue' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Filter Quartal')
                    ->options([
                        '2025-Q1' => 'Q1 2025',
                        '2025-Q2' => 'Q2 2025',
                        '2025-Q3' => 'Q3 2025',
                        '2025-04' => 'Q4 2025',
                        '2025-H1' => 'H1 2025',
                        '2025-H2' => 'H2 2025',
                        'Tahunan-2025' => 'Tahunan 2025',
                    ]),
                  

                Tables\Filters\SelectFilter::make('status')
                    ->label('Filter Status')
                    ->options([
                        'pending' => 'Pending',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'overdue' => 'Overdue',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                //
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
