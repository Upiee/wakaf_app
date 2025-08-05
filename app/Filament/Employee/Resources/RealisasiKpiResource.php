<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\RealisasiKpiResource\Pages;
use App\Models\RealisasiKpi;
use App\Models\KelolaKPI;
use App\Models\KpiSubActivity;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class RealisasiKpiResource extends Resource
{
    protected static ?string $model = RealisasiKpi::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-pie';
    protected static ?string $navigationGroup = 'Realisasi';
    protected static ?string $navigationLabel = 'Realisasi KPI';
    protected static ?int $navigationSort = 1;

    /**
     * Get navigation badge untuk menampilkan jumlah realisasi KPI
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

    // Query untuk Employee - hanya realisasi KPI yang dibuat oleh employee ini
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
                    ->default(fn() => Auth::user()->divisi_id),

                // Hidden field untuk user_id (auto-filled dengan user yang login)
                Forms\Components\Hidden::make('user_id')
                    ->default(fn() => Auth::user()->id),

                Forms\Components\Select::make('kpi_id')
                    ->label('🎯 KPI Saya')
                    ->options(function () {
                        $user = Auth::user();
                        // Hanya KPI individual yang assigned ke employee ini
                        return KelolaKPI::where('user_id', $user->id)
                            ->where('assignment_type', 'individual')
                            ->whereIn('tipe', ['kpi', 'kpi individu'])
                            ->pluck('activity', 'id');
                    })
                    ->required()
                    ->searchable()
                    ->placeholder('Pilih KPI individual Anda...')
                    ->helperText('Hanya KPI yang secara khusus assigned ke Anda')
                    ->live()
                    ->afterStateUpdated(function ($state, $set) {
                        // Cek apakah sudah ada realisasi untuk KPI ini di periode yang sama
                        $user = Auth::user();
                        $periode = request()->input('periode', 'Q2-2025');

                        // if ($state) {
                        //     $exists = RealisasiKpi::where('kpi_id', $state)
                        //         ->where('user_id', $user->id)
                        //         ->where('periode', $periode)
                        //         ->exists();

                        //     if ($exists) {
                        //         // Reset field jika sudah ada
                        //         $set('kpi_id', null);
                        //         // Notification akan ditampilkan via validation
                        //     }
                        // }
                    }),

                Forms\Components\Select::make('kpi_sub_activity_id')
                    ->label('Indikator KPI')
                    ->options(function (callable $get) {
                        $kpiId = $get('kpi_id');

                        if (!$kpiId) {
                            return [];
                        }

                        return KpiSubActivity::where('kpi_id', $kpiId)
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
                    ->live()
                    ->rules([
                        function () {
                            return function (string $attribute, $value, $fail) {
                                $user = Auth::user();
                                $periode = request()->input('periode', 'Q3-2025');

                                // Cek duplicate entry, tapi izinkan edit jika data di-reject
                                $existingRecord = RealisasiKpi::where('kpi_id', $value)
                                    ->where('user_id', $user->id)
                                    ->where('periode', $periode)
                                    ->first();

                                if ($existingRecord) {
                                    // Jika sedang dalam mode edit dan ini adalah record yang sama, izinkan
                                    $currentRecordId = request()->route('record');
                                    if ($currentRecordId && $existingRecord->id == $currentRecordId) {
                                        return; // Izinkan edit record yang sama
                                    }
                                    
                                    // Jika data sudah approved, tidak boleh buat baru
                                    if ($existingRecord->approved_at) {
                                        $fail('KPI ini sudah disetujui untuk periode yang sama. Tidak dapat membuat realisasi baru.');
                                    }
                                    
                                    // Jika data pending approval (belum di-reject), tidak boleh buat baru
                                    if ($existingRecord->is_cutoff && !$existingRecord->rejected_at) {
                                        $fail('KPI ini sedang menunggu persetujuan untuk periode yang sama. Tidak dapat membuat realisasi baru.');
                                    }
                                    
                                    // Jika data dalam draft atau di-reject, beri peringatan tapi masih izinkan
                                    if (!$existingRecord->is_cutoff || $existingRecord->rejected_at) {
                                        // Izinkan, karena bisa edit data draft atau yang di-reject
                                        return;
                                    }
                                }
                            };
                        }
                    ]),

                Forms\Components\TextInput::make('nilai')
                    ->label('Realisasi KPI (%)')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%')
                    ->helperText('Masukkan persentase realisasi KPI'),

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
                    ->default('Q2-2025')
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, $set, $get) {
                        // Reset KPI selection jika periode berubah
                        $kpiId = $get('kpi_id');
                        if ($kpiId && $state) {
                            $user = Auth::user();
                            $exists = RealisasiKpi::where('kpi_id', $kpiId)
                                ->where('user_id', $user->id)
                                ->where('periode', $state)
                                ->exists();

                            if ($exists) {
                                $set('kpi_id', null);
                            }
                        }
                    }),

                Forms\Components\Textarea::make('keterangan')
                    ->label('keterangan')
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
                Tables\Columns\TextColumn::make('kpi.activity')
                    ->label('KPI')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('indikator.indikator')
                    ->label('Indikator')
                    ->limit(50)
                    ->searchable()
                    ->sortable(),

                // Tables\Columns\TextColumn::make('kpi.output')
                //     ->label('Target')
                //     ->limit(50)
                //     ->sortable(),

                Tables\Columns\TextColumn::make('kpi.tipe')
                    ->label('Tipe')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'kpi individu' => 'success',
                        'kpi divisi' => 'warning',
                        'kpi' => 'info',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('nilai')
                    ->label('Realisasi')
                    ->suffix('%')
                    ->numeric()
                    ->sortable()
                    ->color(fn(string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 80 => 'warning',
                        $state >= 60 => 'info',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('periode')
                    ->label('Quartal')
                    ->badge()
                    ->color('primary')
                    ->sortable(),

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
                    ->label('keterangan')
                    ->limit(50)
                    ->tooltip(function ($record) {
                        return $record->keterangan;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('periode')
                    ->label('Periode')
                    ->options([
                        'Q1-2025' => 'Q1 2025',
                        'Q2-2025' => 'Q2 2025',
                        'Q3-2025' => 'Q3 2025',
                        'Q4-2025' => 'Q4 2025',
                    ]),

                Tables\Filters\SelectFilter::make('kpi_id')
                    ->label('KPI')
                    ->relationship('kpi', 'activity')
                    ->searchable()
                    ->preload(),

                Tables\Filters\TernaryFilter::make('is_cutoff')
                    ->label('Status Final')
                    ->trueLabel('Sudah Final')
                    ->falseLabel('Belum Final'),

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
                            ->body('Realisasi telah disubmit ulang untuk persetujuan manager.')
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->modalHeading('Submit Ulang Realisasi')
                    ->modalDescription('Apakah Anda yakin ingin submit ulang realisasi ini untuk persetujuan manager?'),

                Tables\Actions\DeleteAction::make()
                    ->visible(fn($record) => $record->is_editable)
                    ->tooltip(function ($record) {
                        if (!$record->is_editable) {
                            return 'Data sudah final atau sudah di-approve dan tidak dapat dihapus';
                        }
                        return 'Hapus realisasi';
                    }),

                // Action untuk set final/cutoff
                Tables\Actions\Action::make('set_final')
                    ->label('Set Final')
                    ->icon('heroicon-o-lock-closed')
                    ->color('warning')
                    ->visible(fn($record) => $record->approval_status === 'draft')
                    ->requiresConfirmation()
                    ->modalHeading('Set Data Final')
                    ->modalDescription('Apakah Anda yakin ingin menetapkan data ini sebagai final? Setelah final, data tidak dapat diubah lagi.')
                    ->action(function ($record) {
                        $record->setFinal();

                        Notification::make()
                            ->success()
                            ->title('Data berhasil di-set final')
                            ->body('Data realisasi KPI tidak dapat diubah lagi.')
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'asc');
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
            'index' => Pages\ListRealisasiKpis::route('/'),
            'create' => Pages\CreateRealisasiKpi::route('/create'),
            'view' => Pages\ViewRealisasiKpi::route('/{record}'),
            'edit' => Pages\EditRealisasiKpi::route('/{record}/edit'),
        ];
    }
}
