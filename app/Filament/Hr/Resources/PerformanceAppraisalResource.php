<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\PerformanceAppraisalResource\Pages;
use App\Models\PerformanceAppraisal;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class PerformanceAppraisalResource extends Resource
{
    protected static ?string $model = PerformanceAppraisal::class;
    protected static ?string $navigationGroup = 'Performance';
    protected static ?string $navigationLabel = 'Set Performance Appraisal';
    protected static ?string $navigationIcon = 'heroicon-o-cog';
    protected static ?int $navigationSort = 1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('periode')
                    ->label('Periode')
                    ->required(),
                Forms\Components\DatePicker::make('mulai_penilaian')
                    ->label('Mulai Penilaian')
                    ->required(),
                Forms\Components\DatePicker::make('selesai_penilaian')
                    ->label('Selesai Penilaian')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'aktif' => 'Aktif',
                        'nonaktif' => 'Nonaktif',
                        'finish' => 'Finish',
                    ])
                    ->required()
                    ->label('Status'),
                Forms\Components\TextInput::make('bobot')
                    ->label('Bobot/Score')
                    ->numeric()
                    ->nullable(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('periode')->label('Periode')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('mulai_penilaian')->label('Mulai')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('selesai_penilaian')->label('Selesai')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('bobot')->label('Bobot/Score'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPerformanceAppraisals::route('/'),
            'create' => Pages\CreatePerformanceAppraisal::route('/create'),
            'edit' => Pages\EditPerformanceAppraisal::route('/{record}/edit'),
        ];
    }


    public static function getNavigationLabel(): string
    {
        return 'Performance Appraisal';
    }

    public static function getPluralModelLabel(): string
    {
        return 'Performance Appraisal';
    }
}

