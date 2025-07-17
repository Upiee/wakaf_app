<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\SetKpiOkrResource\Pages;
use App\Filament\Hr\Resources\SetKpiOkrResource\RelationManagers;
use App\Models\SetKpiOkr;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SetKpiOkrResource extends Resource
{
    protected static ?string $model = SetKpiOkr::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?string $navigationGroup = 'Settings';
    protected static ?string $navigationLabel = 'Set KPI & OKR';
    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Setting')
                    ->required()
                    ->maxLength(255),
                    
                Forms\Components\Select::make('type')
                    ->label('Tipe')
                    ->options([
                        'kpi' => 'KPI',
                        'okr' => 'OKR',
                    ])
                    ->required(),
                    
                Forms\Components\DatePicker::make('start_date')
                    ->label('Tanggal Mulai Input')
                    ->required(),
                    
                Forms\Components\DatePicker::make('end_date')
                    ->label('Tanggal Selesai Input')
                    ->required(),
                    
                Forms\Components\DatePicker::make('cutoff_date')
                    ->label('Tanggal Cut Off')
                    ->required(),
                    
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true),
                    
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi')
                    ->rows(3)
                    ->maxLength(1000),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Setting')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\BadgeColumn::make('type')
                    ->label('Tipe')
                    ->colors([
                        'primary' => 'kpi',
                        'success' => 'okr',
                    ]),
                    
                Tables\Columns\TextColumn::make('start_date')
                    ->label('Mulai Input')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('end_date')
                    ->label('Selesai Input')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('cutoff_date')
                    ->label('Cut Off')
                    ->date('d M Y')
                    ->sortable(),
                    
                Tables\Columns\BooleanColumn::make('is_active')
                    ->label('Status')
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->label('Tipe')
                    ->options([
                        'kpi' => 'KPI',
                        'okr' => 'OKR',
                    ]),
                    
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->placeholder('Semua Status')
                    ->trueLabel('Aktif')
                    ->falseLabel('Tidak Aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListSetKpiOkrs::route('/'),
            'create' => Pages\CreateSetKpiOkr::route('/create'),
            'edit' => Pages\EditSetKpiOkr::route('/{record}/edit'),
        ];
    }
}
