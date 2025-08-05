<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\UserResource\Pages;
use App\Filament\Hr\Resources\UserResource\RelationManagers;
use App\Models\User;
use App\Models\Divisi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'User Management';
    protected static ?int $navigationSort = 2;

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
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->minLength(8)
                    ->dehydrateStateUsing(fn ($state) => Hash::make($state))
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Minimal 8 karakter. Kosongkan jika tidak ingin mengubah password.'),
                Forms\Components\Select::make('role')
                    ->label('Role')
                    ->options([
                        'hr' => 'HR',
                        'manager' => 'Manager',
                        'employee' => 'Employee',
                    ])
                    ->required()
                    ->native(false),
                Forms\Components\Select::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama')
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Status Aktif')
                    ->default(true)
                    ->helperText('User aktif dapat login ke sistem'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'hr' => 'danger',
                        'manager' => 'warning',
                        'employee' => 'success',
                        default => 'gray',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'hr' => 'HR',
                        'manager' => 'Manager',
                        'employee' => 'Employee',
                    ]),
                Tables\Filters\SelectFilter::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama'),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueLabel('Aktif')
                    ->falseLabel('Non-Aktif'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // Tables\Actions\Action::make('reset_password')
                //     ->label('Reset Password')
                //     ->icon('heroicon-o-key')
                //     ->color('warning')
                //     ->action(function (User $record) {
                //         $newPassword = 'password123';
                //         $record->update(['password' => Hash::make($newPassword)]);
                //         \Filament\Notifications\Notification::make()
                //             ->title('Password berhasil direset')
                //             ->body("Password baru: {$newPassword}")
                //             ->success()
                //             ->send();
                //     })
                //     ->requiresConfirmation()
                //     ->modalHeading('Reset Password User')
                //     ->modalDescription('Password akan direset ke "password123". User dapat menggantinya setelah login.')
                //     ->modalSubmitActionLabel('Reset'),
                    
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
