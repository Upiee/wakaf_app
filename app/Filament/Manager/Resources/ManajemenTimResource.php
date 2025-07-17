<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\ManajemenTimResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ManajemenTimResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Team Management';
    protected static ?string $navigationLabel = 'Tim Saya';
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
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Employee')
                    ->disabled(),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->disabled(),
                Forms\Components\Select::make('role')
                    ->label('Role')
                    ->options([
                        'employee' => 'Employee',
                        'manager' => 'Manager',
                    ])
                    ->disabled(),
                // Tambahan info atau action untuk manager
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Employee')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->badge()
                    ->color('info'),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'employee' => 'success',
                        'manager' => 'warning',
                        'hr' => 'danger',
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Bergabung')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'employee' => 'Employee',
                        'manager' => 'Manager',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('lihat_kpi_progress')
                    ->label('Lihat KPI Progress')
                    ->icon('heroicon-o-chart-bar')
                    ->url(fn (User $record): string => 
                        route('filament.manager.resources.employee-kpi-approvals.index') . 
                        '?tableFilters[kpi.user_id][value]=' . $record->id
                    )
                    ->openUrlInNewTab(),
                Tables\Actions\Action::make('lihat_okr_progress')
                    ->label('Lihat OKR Progress')
                    ->icon('heroicon-o-flag')
                    ->url(fn (User $record): string => 
                        route('filament.manager.resources.employee-okr-approvals.index') . 
                        '?tableFilters[okr.user_id][value]=' . $record->id
                    )
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                // Tidak ada bulk actions untuk safety
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Manager hanya melihat employee di divisinya
        return parent::getEloquentQuery()
            ->where('role', 'employee')
            ->where('divisi_id', $user->divisi_id); // Filter berdasarkan divisi manager
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
            'index' => Pages\ListManajemenTims::route('/'),
        ];
    }
}
