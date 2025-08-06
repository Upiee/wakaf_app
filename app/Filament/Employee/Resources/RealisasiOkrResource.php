<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\RealisasiOkrResource\Pages;
use App\Models\RealisasiOkr;
use App\Models\KelolaOKR;
use App\Models\OkrSubActivity;
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

        // Hitung yang di-reject (prioritas tertinggi)
        $rejectedCount = static::getEloquentQuery()
            ->whereNotNull('rejected_at')
            ->count();

        if ($rejectedCount > 0) {
            return 'danger'; // Merah jika ada yang di-reject
        }

        // Hitung realisasi yang pending approval
        $pendingCount = static::getEloquentQuery()
            ->where('is_cutoff', true)
            ->whereNull('approved_at')
            ->whereNull('rejected_at')
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
            ->orderBy('created_at', 'asc');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Hidden field untuk divisi (auto-filled dengan divisi employee)
                Forms\Components\Hidden::make('divisi_id')
                    ->default(fn() => Auth::user()->divisi_id),

                // Hidden field untuk user_id (auto-filled dengan user yang login)
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::user()->id),

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
                        // $user = Auth::user();
                        // $periode = request()->input('periode', 'Q3-2025');

                        // if ($state) {
                        //     $exists = RealisasiOkr::where('okr_id', $state)
                        //         ->where('user_id', $user->id)
                        //         ->where('periode', $periode)
                        //         ->exists();

                        //     if ($exists) {
                        //         // Reset field jika sudah ada
                        //         $set('okr_id', null);
                        //         // Notification akan ditampilkan via validation
                        //     }
                        // }
                    }),

                Forms\Components\Select::make('okr_sub_activity_id')
                    ->label('Indikator OKR')
                    ->options(function (callable $get) {
                        $kpiId = $get('okr_id');

                        if (!$kpiId) {
                            return [];
                        }

                        return OkrSubActivity::where('okr_id', $kpiId)
                            ->pluck('indikator', 'id')
                            ->mapWithKeys(function ($item, $key) {
                                return [$key => $item];
                            })
                            ->toArray() ?? [];
                    })
                    ->placeholder('Pilih sub-activity (jika ada)')
                    ->helperText('Opsional, jika KPI memiliki sub-activity')
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Cek apakah sudah ada realisasi untuk sub-activity ini di periode yang sama
                        $user = Auth::user();
                        $periode = request()->input('periode', 'Q2-2025');
                        $kpiId = $get('kpi_id');

                        // if ($state && $kpiId) {
                        //     $exists = RealisasiKpi::where('kpi_sub_activity_id', $state)
                        //         ->where('user_id', $user->id)
                        //         ->where('periode', $periode)
                        //         ->exists();

                        //     if ($exists) {
                        //         // Reset field jika sudah ada
                        //         $set('kpi_sub_activity_id', null);
                        //         // Notification akan ditampilkan via validation
                        //     }
                        // }
                    })
                    ->required()
                    ->searchable()
                    ->placeholder('Pilih sub-activity (jika ada)')
                    ->helperText('Opsional, jika KPI memiliki sub-activity')
                    ->live(),

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
                    ])
                    ->default('Q2-2025')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Reset OKR selection jika periode berubah
                        $okrId = $get('okr_id');
                        if ($okrId && $state) {
                            $user = Auth::user();
                            $existingRecord = RealisasiOkr::where('okr_id', $okrId)
                                ->where('user_id', $user->id)
                                ->where('periode', $state)
                                ->first();

                            if ($existingRecord) {
                                // Jika sedang dalam mode edit dan ini adalah record yang sama, izinkan
                                $currentRecordId = request()->route('record');
                                if ($currentRecordId && $existingRecord->id == $currentRecordId) {
                                    return; // Izinkan edit record yang sama
                                }
                                
                                // Jika data sudah approved atau pending approval (belum di-reject), reset
                                if ($existingRecord->approved_at || 
                                    ($existingRecord->is_cutoff && !$existingRecord->rejected_at)) {
                                    $set('okr_id', null);
                                    
                                    Notification::make()
                                        ->warning()
                                        ->title('OKR sudah ada!')
                                        ->body('OKR ini sudah memiliki realisasi untuk periode yang sama dan sudah disetujui/pending approval.')
                                        ->send();
                                }
                                
                                // Jika data dalam draft atau di-reject, izinkan (tidak reset)
                                if (!$existingRecord->is_cutoff || $existingRecord->rejected_at) {
                                    // Beri notifikasi info saja
                                    Notification::make()
                                        ->info()
                                        ->title('Info')
                                        ->body('Anda dapat mengedit realisasi yang sudah ada untuk OKR ini.')
                                        ->send();
                                }
                            }
                        }
                    }),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan')
                    ->placeholder('Catatan atau penjelasan tambahan untuk realisasi ini...')
                    ->rows(3),

                // Forms\Components\Toggle::make('is_cutoff')
                //     ->label('Set Final/Cutoff')
                //     ->helperText('⚠️ PERINGATAN: Setelah dicentang, data tidak dapat diubah lagi!')
                //     ->default(false)
                //     ->live()
                //     ->afterStateUpdated(function ($state, $set) {
                //         if ($state) {
                //             // Tampilkan konfirmasi via JavaScript atau notifikasi
                //             Notification::make()
                //                 ->warning()
                //                 ->title('Data akan di-set final!')
                //                 ->body('Setelah di-save, data ini tidak dapat diubah lagi.')
                //                 ->send();
                //         }
                //     }),
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

                Tables\Columns\TextColumn::make('indikator.indikator')
                    ->label('Target')
                    ->limit(50)
                    ->sortable(),

                Tables\Columns\TextColumn::make('nilai')
                    ->label('Realisasi (%)')
                    ->formatStateUsing(fn($state) => $state . '%')
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

                // Tables\Columns\IconColumn::make('is_cutoff')
                //     ->label('Final')
                //     ->boolean()
                //     ->trueIcon('heroicon-o-lock-closed')
                //     ->falseIcon('heroicon-o-lock-open')
                //     ->trueColor('success')
                //     ->falseColor('warning')
                //     ->sortable()
                //     ->tooltip(function ($record) {
                //         if ($record->is_cutoff) {
                //             return 'Data sudah final dan tidak dapat diubah';
                //         }
                //         return 'Data masih bisa diubah';
                //     }),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status Approval')
                    ->colors([
                        'success' => 'approved',
                        'danger' => 'rejected', 
                        'warning' => 'pending_approval',
                        'secondary' => 'draft',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match($state) {
                            'approved' => 'Disetujui',
                            'rejected' => 'Ditolak',
                            'pending_approval' => 'Menunggu Persetujuan',
                            'draft' => 'Draft',
                            default => 'Unknown'
                        };
                    })
                    ->tooltip(function ($record) {
                        if ($record->approved_at) {
                            return 'Disetujui pada: ' . $record->approved_at->format('d M Y H:i') . 
                                   ' oleh ' . ($record->approvedBy->name ?? 'Manager');
                        }
                        if ($record->rejected_at) {
                            return 'Ditolak pada: ' . $record->rejected_at->format('d M Y H:i') . 
                                   ' oleh ' . ($record->rejectedBy->name ?? 'Manager') .
                                   (isset($record->rejection_reason) ? "\nAlasan: " . $record->rejection_reason : '');
                        }
                        if ($record->is_cutoff) {
                            return 'Menunggu persetujuan manager';
                        }
                        return 'Masih dalam tahap draft';
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

                Tables\Filters\Filter::make('pending_approval')
                    ->label('Menunggu Persetujuan')
                    ->query(fn (Builder $query) => $query->where('is_cutoff', true)->whereNull('approved_at')->whereNull('rejected_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('approved')
                    ->label('Disetujui')
                    ->query(fn (Builder $query) => $query->whereNotNull('approved_at'))
                    ->toggle(),

                Tables\Filters\Filter::make('rejected')
                    ->label('Ditolak')
                    ->query(fn (Builder $query) => $query->whereNotNull('rejected_at'))
                    ->toggle(),

                // Tables\Filters\Filter::make('created_at')
                //     ->form([
                //         Forms\Components\DatePicker::make('tanggal_dari')
                //             ->label('Tanggal Dari'),
                //         Forms\Components\DatePicker::make('tanggal_sampai')
                //             ->label('Tanggal Sampai'),
                //     ])
                //     ->query(function (Builder $query, array $data): Builder {
                //         return $query
                //             ->when(
                //                 $data['tanggal_dari'],
                //                 fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                //             )
                //             ->when(
                //                 $data['tanggal_sampai'],
                //                 fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                //             );
                //     }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\EditAction::make()
                    ->visible(fn($record) => $record->is_editable)
                    ->tooltip(function ($record) {
                        if ($record->approval_status === 'rejected') {
                            return 'Edit dan submit ulang realisasi yang ditolak';
                        }
                        if (!$record->is_editable) {
                            return 'Data sudah final atau sudah di-approve dan tidak dapat diubah';
                        }
                        return 'Edit realisasi';
                    }),

                Tables\Actions\Action::make('resubmit')
                    ->label('Submit Ulang')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->visible(fn($record) => $record->approval_status === 'rejected')
                    ->action(function ($record) {
                        $record->update([
                            'rejected_by' => null,
                            'rejected_at' => null,
                            'rejection_reason' => null,
                            'is_cutoff' => true, // Set final untuk review ulang
                        ]);
                        
                        Notification::make()
                            ->success()
                            ->title('Berhasil Submit Ulang')
                            ->body('Realisasi OKR telah disubmit ulang untuk persetujuan manager.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Submit Ulang Realisasi OKR')
                    ->modalDescription('Apakah Anda yakin ingin submit ulang realisasi OKR ini untuk persetujuan manager?'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->is_editable)
                    ->tooltip(function ($record) {
                        if (!$record->is_editable) {
                            return 'Data sudah final atau sudah di-approve dan tidak dapat dihapus';
                        }
                        return 'Hapus realisasi';
                    }),

                // Action untuk set final/cutoff
                // Tables\Actions\Action::make('set_final')
                //     ->label('Set Final')
                //     ->icon('heroicon-o-lock-closed')
                //     ->color('warning')
                //     ->visible(fn($record) => !$record->is_cutoff && !$record->approved_at)
                //     ->requiresConfirmation()
                //     ->modalHeading('Set Data Final')
                //     ->modalDescription('Apakah Anda yakin ingin menetapkan data ini sebagai final? Setelah final, data tidak dapat diubah lagi.')
                    
                    // ->action(function ($record) {
                    //     $record->update(['is_cutoff' => true]);

                    //     Notification::make()
                    //         ->success()
                    //         ->title('Data berhasil di-set final')
                    //         ->body('Data realisasi OKR tidak dapat diubah lagi.')
                    //         ->send();
                    // }),
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
