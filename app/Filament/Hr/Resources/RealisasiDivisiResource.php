<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\RealisasiDivisiResource\Pages;
use App\Filament\Hr\Resources\RealisasiDivisiResource\RelationManagers;
use App\Models\RealisasiDivisi;
use App\Models\Divisi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;

class RealisasiDivisiResource extends Resource
{
    protected static ?string $model = RealisasiDivisi::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    
    protected static ?string $navigationLabel = 'Realisasi Divisi';
    
    protected static ?string $navigationGroup = 'Realisasi';
    
    protected static ?int $navigationSort = 10;

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
                Forms\Components\Select::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama')
                    ->required(),
                    
                Forms\Components\Select::make('kpi_id')
                    ->label('KPI')
                    ->relationship('kpi', 'nama')
                    ->nullable(),
                    
                Forms\Components\Select::make('okr_id')
                    ->label('OKR')
                    ->relationship('okr', 'nama')
                    ->nullable(),
                    
                Forms\Components\TextInput::make('nilai')
                    ->label('Nilai Realisasi')
                    ->numeric()
                    ->step(0.01)
                    ->suffix('%'),
                    
                Forms\Components\TextInput::make('periode')
                    ->label('Periode')
                    ->maxLength(255)
                    ->placeholder('Q1-2025, Q2-2025, dll'),
                    
                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Catatan atau penjelasan tambahan...')
                    ->columnSpanFull(),
                    
                Forms\Components\Toggle::make('is_cutoff')
                    ->label('Final/Cutoff')
                    ->helperText('Tandai jika data sudah final dan tidak dapat diubah')
                    ->default(false),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('kpi.nama')
                    ->label('KPI')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('okr.nama')
                    ->label('OKR')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                    
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Nilai Realisasi')
                    ->suffix('%')
                    ->sortable()
                    ->color(fn ($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    }),
                    
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->sortable()
                    ->searchable(),
                    
                Tables\Columns\IconColumn::make('is_cutoff')
                    ->label('Final')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('warning'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                    
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('divisi_id')
                    ->label('Divisi')
                    ->relationship('divisi', 'nama')
                    ->multiple()
                    ->preload(),
                    
                SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Q1-2025' => 'Q1-2025',
                        'Q2-2025' => 'Q2-2025',
                        'Q3-2025' => 'Q3-2025',
                        'Q4-2025' => 'Q4-2025',
                    ])
                    ->multiple(),
                    
                Tables\Filters\TernaryFilter::make('is_cutoff')
                    ->label('Status Final')
                    ->placeholder('Semua')
                    ->trueLabel('Final')
                    ->falseLabel('Draft'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\Action::make('addComment')
                    ->label('Komentar')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('info')
                    ->form([
                        Forms\Components\Textarea::make('comment')
                            ->label('Komentar HR')
                            ->required()
                            ->placeholder('Tambahkan komentar untuk realisasi ini...')
                            ->rows(3),
                    ])
                    ->action(function (array $data, $record) {
                        // Simpan komentar ke database
                        $record->update([
                            'keterangan' => $record->keterangan . "\n\n[HR Comment - " . now()->format('Y-m-d H:i') . "]: " . $data['comment']
                        ]);
                    }),
            ])
            ->bulkActions([
                // Hapus bulk actions untuk read-only
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListRealisasiDivisis::route('/'),
            'view' => Pages\ViewRealisasiDivisi::route('/{record}'),
        ];
    }
}
