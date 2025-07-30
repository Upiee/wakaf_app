<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\TindakLanjutEmployeeResource\Pages;
use App\Models\TindakLanjut;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TindakLanjutEmployeeResource extends Resource
{
    protected static ?string $model = TindakLanjut::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Tindak Lanjut Saya';
    protected static ?string $navigationGroup = 'Performa Saya';
    protected static ?int $navigationSort = 20;

    // Simple filter - hanya tindak lanjut pribadi
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Super simple - semua disabled untuk view only
                Forms\Components\Section::make('Tindak Lanjut untuk Saya')
                    ->schema([
                        Forms\Components\TextInput::make('kode_tindak_lanjut')
                            ->label('Kode')
                            ->disabled(),

                        Forms\Components\TextInput::make('jenis_tindakan')
                            ->label('Jenis Tindakan')
                            ->disabled(),

                        Forms\Components\Textarea::make('deskripsi_tindakan')
                            ->label('Deskripsi')
                            ->disabled()
                            ->rows(3),

                        Forms\Components\DatePicker::make('timeline_mulai')
                            ->label('Tanggal Mulai')
                            ->disabled(),

                        Forms\Components\DatePicker::make('timeline_selesai')
                            ->label('Target Selesai')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Status & Progress')
                    ->schema([
                        Forms\Components\TextInput::make('status_pelaksanaan')
                            ->label('Status Saat Ini')
                            ->disabled(),

                        Forms\Components\TextInput::make('progress_percentage')
                            ->label('Progress')
                            ->suffix('%')
                            ->disabled(),

                        Forms\Components\TextInput::make('picResponsible.name')
                            ->label('PIC Penanggung Jawab')
                            ->disabled(),

                        Forms\Components\Textarea::make('catatan_progress')
                            ->label('Catatan Progress')
                            ->disabled()
                            ->rows(3),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_tindak_lanjut')
                    ->label('Kode')
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

                Tables\Columns\TextColumn::make('timeline_display')
                    ->label('Periode')
                    ->getStateUsing(fn($record) => 
                        $record->getAttribute('timeline_mulai')?->format('d/m') . ' - ' . 
                        $record->getAttribute('timeline_selesai')?->format('d/m/Y')
                    ),

                Tables\Columns\TextColumn::make('status_pelaksanaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'planned' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'secondary'
                    })
                    ->formatStateUsing(fn($state) => match($state) {
                        'planned' => 'Direncanakan',
                        'in_progress' => 'Berlangsung',
                        'completed' => 'Selesai',
                        default => ucfirst($state)
                    }),

                Tables\Columns\TextColumn::make('progress_percentage')
                    ->label('Progress')
                    ->suffix('%')
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'info',
                        $state >= 50 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('picResponsible.name')
                    ->label('PIC')
                    ->placeholder('Belum ditentukan'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d/m/Y'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pelaksanaan')
                    ->label('Status')
                    ->options([
                        'planned' => 'Direncanakan',
                        'in_progress' => 'Berlangsung',
                        'completed' => 'Selesai',
                    ]),

                Tables\Filters\SelectFilter::make('jenis_tindakan')
                    ->label('Jenis')
                    ->options([
                        'pelatihan' => 'Pelatihan',
                        'coaching' => 'Coaching',
                        'development_plan' => 'Development Plan',
                        'peringatan' => 'Performance Warning',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('Lihat detail tindak lanjut'),
            ])
            ->bulkActions([
                // No bulk actions untuk employee
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Tindak Lanjut')
            ->emptyStateDescription('Tindak lanjut akan muncul jika ada area performance yang perlu diperbaiki.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTindakLanjutEmployees::route('/'),
            'view' => Pages\ViewTindakLanjutEmployee::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Employee tidak bisa create
    }

    public static function canEdit($record): bool
    {
        return false; // Employee tidak bisa edit - view only
    }

    public static function canDelete($record): bool
    {
        return false; // Employee tidak bisa delete
    }
}
