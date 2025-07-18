<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\RealisasiOkrResource\Pages;
use App\Models\RealisasiOkr;
use App\Models\KelolaOKR;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class RealisasiOkrResource extends Resource
{
    protected static ?string $model = RealisasiOkr::class;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-circle';
    protected static ?string $navigationGroup = 'Realisasi';
    protected static ?string $navigationLabel = 'Realisasi OKR';
    protected static ?int $navigationSort = 2;

    /**
     * Get navigation badge untuk menampilkan jumlah realisasi OKR
     */
    public static function getNavigationBadge(): ?string
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        $count = static::getEloquentQuery()->count();
        
        return $count > 0 ? (string) $count : null;
    }

    /**
     * Warna badge berdasarkan status realisasi
     */
    public static function getNavigationBadgeColor(): ?string
    {
        $user = Auth::user();
        
        if (!$user) {
            return 'gray';
        }
        
        // Hitung realisasi yang pending approval
        $pendingCount = static::getEloquentQuery()
            ->where('is_cutoff', true)
            ->whereNull('approved_at')
            ->count();
            
        if ($pendingCount > 0) {
            return 'warning'; // Kuning jika ada yang pending
        }
        
        $approvedCount = static::getEloquentQuery()
            ->whereNotNull('approved_at')
            ->count();
            
        if ($approvedCount > 0) {
            return 'success'; // Hijau jika ada yang approved
        }
        
        return 'info'; // Biru untuk draft
    }

    // Query untuk Employee - hanya realisasi OKR yang dibuat oleh employee ini
    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();
        
        return parent::getEloquentQuery()
            ->where('user_id', $user->id) // Hanya realisasi employee yang login
            ->orderBy('created_at', 'desc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hidden field untuk divisi (auto-filled dengan divisi employee)
                Forms\Components\Hidden::make('divisi_id')
                    ->default(fn () => Auth::user()->divisi_id),

                // Hidden field untuk user_id (auto-filled dengan user yang login)
                Forms\Components\Hidden::make('user_id')
                    ->default(fn () => Auth::user()->id),

                Forms\Components\Select::make('okr_id')
                    ->label('🚀 OKR Saya')
                    ->options(function () {
                        $user = Auth::user();
                        // Hanya OKR individual yang assigned ke employee ini
                        return KelolaOKR::where('user_id', $user->id)
                                       ->where('assignment_type', 'individual')
                                       ->whereIn('tipe', ['okr', 'okr individu'])
                                       ->pluck('activity', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->placeholder('Pilih OKR individual Anda...')
                    ->helperText('Hanya OKR yang secara khusus assigned ke Anda')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        // Cek apakah sudah ada realisasi untuk OKR ini di periode yang sama
                        $user = Auth::user();
                        $periode = request()->input('periode', 'Q3-2025');
                        
                        if ($state) {
                            $exists = RealisasiOkr::where('okr_id', $state)
                                                  ->where('user_id', $user->id)
                                                  ->where('periode', $periode)
                                                  ->exists();
                            
                            if ($exists) {
                                // Reset field jika sudah ada
                                $set('okr_id', null);
                                // Notification akan ditampilkan via validation
                            }
                        }
                    })
                    ->rules([
                        function () {
                            return function (string $attribute, $value, $fail) {
                                $user = Auth::user();
                                $periode = request()->input('periode', 'Q3-2025');
                                
                                // Cek duplicate entry
                                $exists = RealisasiOkr::where('okr_id', $value)
                                                      ->where('user_id', $user->id)
                                                      ->where('periode', $periode)
                                                      ->exists();
                                
                                if ($exists) {
                                    $fail('Anda sudah mengisi realisasi untuk OKR ini pada periode yang sama.');
                                }
                            };
                        }
                    ]),

                Forms\Components\TextInput::make('nilai')
                    ->label('Realisasi OKR (%)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->helperText('Masukkan persentase realisasi OKR'),

                Forms\Components\Select::make('periode')
                    ->label('Quartal/Periode')
                    ->options([
                        'Q1-2025' => 'Q1 2025 (Jan-Mar)',
                        'Q2-2025' => 'Q2 2025 (Apr-Jun)',
                        'Q3-2025' => 'Q3 2025 (Jul-Sep)',
                        'Q4-2025' => 'Q4 2025 (Oct-Des)',
                        'H1-2025' => 'H1 2025 (Jan-Jun)',
                        'H2-2025' => 'H2 2025 (Jul-Des)',
                        'Tahunan-2025' => 'Tahunan 2025',
                    ])
                    ->default('Q3-2025')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Reset OKR selection jika periode berubah
                        $okrId = $get('okr_id');
                        if ($okrId && $state) {
                            $user = Auth::user();
                            $exists = RealisasiOkr::where('okr_id', $okrId)
                                                  ->where('user_id', $user->id)
                                                  ->where('periode', $state)
                                                  ->exists();
                            
                            if ($exists) {
                                $set('okr_id', null);
                            }
                        }
                    }),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Catatan atau penjelasan tambahan untuk realisasi ini...')
                    ->rows(3),

                Forms\Components\Toggle::make('is_cutoff')
                    ->label('Set Final/Cutoff')
                    ->helperText('⚠️ PERINGATAN: Setelah dicentang, data tidak dapat diubah lagi!')
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        if ($state) {
                            // Tampilkan konfirmasi via JavaScript atau notifikasi
                            Notification::make()
                                ->warning()
                                ->title('Data akan di-set final!')
                                ->body('Setelah di-save, data ini tidak dapat diubah lagi.')
                                ->send();
                        }
                    }),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('okr.activity')
                    ->label('OKR')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('okr.output')
                    ->label('Target')
                    ->limit(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('nilai')
                    ->label('Realisasi (%)')
                    ->formatStateUsing(fn ($state) => $state . '%')
                    ->sortable()
                    ->badge()
                    ->color(function ($state) {
                        $nilai = (float) $state;
                        if ($nilai >= 90) return 'success';
                        if ($nilai >= 75) return 'warning';
                        if ($nilai >= 50) return 'info';
                        return 'danger';
                    }),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Quartal')
                    ->sortable()
                    ->badge()
                    ->color('primary'),

                Tables\Columns\IconColumn::make('is_cutoff')
                    ->label('Final')
                    ->boolean()
                    ->trueIcon('heroicon-o-lock-closed')
                    ->falseIcon('heroicon-o-lock-open')
                    ->trueColor('success')
                    ->falseColor('warning')
                    ->sortable()
                    ->tooltip(function ($record) {
                        if ($record->is_cutoff) {
                            return 'Data sudah final dan tidak dapat diubah';
                        }
                        return 'Data masih bisa diubah';
                    }),

                Tables\Columns\TextColumn::make('approved_at')
                    ->label('Approved')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('Belum di-approve')
                    ->tooltip(function ($record) {
                        if ($record->approved_at) {
                            return 'Di-approve pada: ' . $record->approved_at->format('d M Y H:i');
                        }
                        return 'Belum di-approve oleh manager';
                    }),

                Tables\Columns\TextColumn::make('keterangan')
                    ->label('Keterangan')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->keterangan;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('okr_id')
                    ->label('OKR')
                    ->relationship('okr', 'activity', function ($query) {
                        $user = Auth::user();
                        return $query->where('user_id', $user->id)
                            ->where('tipe', 'LIKE', 'okr%');
                    }),

                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Q1-2025' => 'Q1-2025',
                        'Q2-2025' => 'Q2-2025',
                        'Q3-2025' => 'Q3-2025',
                        'Q4-2025' => 'Q4-2025',
                    ]),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('tanggal_dari')
                            ->label('Tanggal Dari'),
                        Forms\Components\DatePicker::make('tanggal_sampai')
                            ->label('Tanggal Sampai'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['tanggal_dari'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['tanggal_sampai'],
                                fn (Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                
                Tables\Actions\EditAction::make()
                    ->visible(fn ($record) => !$record->is_cutoff && !$record->approved_at)
                    ->tooltip(function ($record) {
                        if ($record->is_cutoff) {
                            return 'Data sudah final dan tidak dapat diubah';
                        }
                        if ($record->approved_at) {
                            return 'Data sudah di-approve dan tidak dapat diubah';
                        }
                        return 'Edit realisasi';
                    }),
                
                Tables\Actions\DeleteAction::make()
                    ->visible(fn ($record) => !$record->is_cutoff && !$record->approved_at)
                    ->tooltip(function ($record) {
                        if ($record->is_cutoff) {
                            return 'Data sudah final dan tidak dapat dihapus';
                        }
                        if ($record->approved_at) {
                            return 'Data sudah di-approve dan tidak dapat dihapus';
                        }
                        return 'Hapus realisasi';
                    }),

                // Action untuk set final/cutoff
                Tables\Actions\Action::make('set_final')
                    ->label('Set Final')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn ($record) => !$record->is_cutoff && !$record->approved_at)
                    ->requiresConfirmation()
                    ->modalHeading('Set Data Final')
                    ->modalDescription('Apakah Anda yakin ingin menetapkan data ini sebagai final? Setelah final, data tidak dapat diubah lagi.')
                    ->action(function ($record) {
                        $record->update(['is_cutoff' => true]);
                        
                        Notification::make()
                            ->success()
                            ->title('Data berhasil di-set final')
                            ->body('Data realisasi OKR tidak dapat diubah lagi.')
                            ->send();
                    }),
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
            'index' => Pages\ListRealisasiOkrs::route('/'),
            'create' => Pages\CreateRealisasiOkr::route('/create'),
            'view' => Pages\ViewRealisasiOkr::route('/{record}'),
            'edit' => Pages\EditRealisasiOkr::route('/{record}/edit'),
        ];
    }
}
