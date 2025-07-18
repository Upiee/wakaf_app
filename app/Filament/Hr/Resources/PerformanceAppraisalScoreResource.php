<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\PerformanceAppraisalScoreResource\Pages;
use App\Models\PerformanceAppraisalScore;
use App\Models\PerformanceAppraisal;
use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;

class PerformanceAppraisalScoreResource extends Resource
{
    protected static ?string $model = PerformanceAppraisalScore::class;
    protected static ?string $navigationGroup = 'Performance';
    protected static ?string $navigationLabel = 'Performance Appraisal Score';
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?int $navigationSort = 1;

    public static function form(Forms\Form $form): Forms\Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('performance_appraisal_id')
                    ->label('Periode Appraisal')
                    ->options(PerformanceAppraisal::pluck('periode', 'id'))
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('Karyawan')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\TextInput::make('score')
                    ->label('Score')
                    ->numeric()
                    ->required(),
                Forms\Components\Textarea::make('catatan')
                    ->label('Catatan')
                    ->nullable(),
            ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('appraisal.periode')->label('Periode'),
                Tables\Columns\TextColumn::make('user.name')->label('Karyawan')->searchable(),
                Tables\Columns\TextColumn::make('score')->label('Score'),
                Tables\Columns\TextColumn::make('catatan')->label('Catatan')->limit(30),
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
            'index' => Pages\ListPerformanceAppraisalScores::route('/'),
            'create' => Pages\CreatePerformanceAppraisalScore::route('/create'),
            'edit' => Pages\EditPerformanceAppraisalScore::route('/{record}/edit'),
        ];
    }
}
