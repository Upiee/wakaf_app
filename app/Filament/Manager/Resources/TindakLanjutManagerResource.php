<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\TindakLanjutManagerResource\Pages;
use App\Models\TindakLanjut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TindakLanjutManagerResource extends Resource
{
    protected static ?string $model = TindakLanjut::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Tindak Lanjut Tim';
    protected static ?string $navigationGroup = 'Report';
    protected static ?int $navigationSort = 51;

    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        
        if (!$user || !$user->divisi_id) {
            return null;
        }
        
        $count = static::getEloquentQuery()->count();
        
        return $count > 0 ? (string) $count : null;
    }

    // Simple filter - hanya untuk tim manager
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('user', function ($query) {
                $query->where('divisi_id', Auth::user()->getAttribute('divisi_id'));
            });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Simple form - mostly disabled untuk view
                Forms\Components\Section::make('Info Tindak Lanjut')
                    ->schema([
                        Forms\Components\TextInput::make('kode_tindak_lanjut')
                            ->label('Kode')
                            ->disabled(),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Karyawan')
                            ->disabled(),

                        Forms\Components\TextInput::make('jenis_tindakan')
                            ->label('Jenis')
                            ->disabled(),

                        Forms\Components\Textarea::make('deskripsi_tindakan')
                            ->label('Deskripsi')
                            ->disabled()
                            ->rows(2),
                    ])->columns(2),

                // Simple progress update
                Forms\Components\Section::make('Update Progress')
                    ->schema([
                        Forms\Components\Select::make('status_pelaksanaan')
                            ->label('Status')
                            ->options([
                                'planned' => 'Direncanakan',
                                'in_progress' => 'Sedang Berlangsung', 
                                'completed' => 'Selesai',
                            ]),

                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->suffix('%'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis_tindakan')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pelatihan' => 'info',
                        'coaching' => 'warning',
                        'peringatan' => 'danger',
                        'development_plan' => 'primary',
                        default => 'secondary'
                    }),

                Tables\Columns\TextColumn::make('timeline_selesai')
                    ->label('Target')
                    ->date('d/m/Y'),

                Tables\Columns\TextColumn::make('status_pelaksanaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'planned' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'secondary'
                    }),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pelaksanaan')
                    ->options([
                        'planned' => 'Direncanakan',
                        'in_progress' => 'Berlangsung',
                        'completed' => 'Selesai',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                // Simple quick update action
                Tables\Actions\Action::make('quick_update')
                    ->label('Update')
                    ->icon('heroicon-o-pencil')
                    ->color('success')
                    ->form([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'in_progress' => 'Mulai Berlangsung',
                                'completed' => 'Tandai Selesai',
                            ])
                            ->required(),
                        
                        Forms\Components\TextInput::make('progress')
                            ->label('Progress (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        $record->update([
                            'status_pelaksanaan' => $data['status'],
                            'progress_percentage' => $data['progress'],
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Progress berhasil diupdate!')
                            ->send();
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Tindak Lanjut')
            ->emptyStateDescription('Tindak lanjut akan muncul setelah ada laporan evaluasi yang memerlukan improvement.');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTindakLanjutManagers::route('/'),
            'view' => Pages\ViewTindakLanjutManager::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Simple - tidak bisa create
    }

    public static function canEdit($record): bool
    {
        return false; // Simple - pakai quick action saja
    }

    public static function canDelete($record): bool
    {
        return false; // Simple - tidak bisa delete
    }
}
