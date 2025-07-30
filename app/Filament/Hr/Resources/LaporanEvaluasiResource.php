<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\LaporanEvaluasiResource\Pages;
use App\Filament\Hr\Resources\LaporanEvaluasiResource\RelationManagers;
use App\Models\LaporanEvaluasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaporanEvaluasiResource extends Resource
{
    protected static ?string $model = LaporanEvaluasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Laporan Evaluasi';
    protected static ?string $navigationGroup = 'Report';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setup Laporan Evaluasi')
                    ->description('Pilih target dan jenis laporan')
                    ->schema([
                        Forms\Components\Select::make('divisi_id')
                            ->label('Pilih Divisi')
                            ->relationship('divisi', 'nama')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Reset dependent fields
                                $set('user_id', null);
                                $set('kode_laporan', null);
                                
                                // Generate kode when both divisi and tipe_laporan are selected
                                static::generateKodeLaporan($state, $get('tipe_laporan'), $set, null);
                            })
                            ->helperText('Wajib pilih divisi terlebih dahulu untuk generate kode'),

                        Forms\Components\Select::make('tipe_laporan')
                            ->label('Jenis Laporan')
                            ->options([
                                'individual' => 'Laporan Individual Karyawan',
                                'divisi' => 'Laporan Kinerja Divisi'
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Reset dependent fields
                                if ($state === 'divisi') {
                                    $set('user_id', null);
                                }
                                
                                // Generate kode when both divisi and tipe_laporan are selected
                                static::generateKodeLaporan($get('divisi_id'), $state, $set, null);
                            }),

                        Forms\Components\Select::make('user_id')
                            ->label('Pilih Karyawan')
                            ->searchable()
                            ->preload()
                            ->visible(fn(callable $get) => $get('tipe_laporan') === 'individual')
                            ->required(fn(callable $get) => $get('tipe_laporan') === 'individual')
                            ->live()
                            ->options(function (callable $get) {
                                $divisiId = $get('divisi_id');
                                if ($divisiId) {
                                    return \App\Models\User::where('divisi_id', $divisiId)
                                        ->pluck('name', 'id');
                                }
                                return [];
                            })
                            ->afterStateUpdated(function ($state, $set, $get) {
                                // Re-generate kode when user is selected for individual report
                                if ($get('tipe_laporan') === 'individual' && $state) {
                                    static::generateKodeLaporan($get('divisi_id'), $get('tipe_laporan'), $set, $state);
                                }
                            })
                            ->helperText('Pilih karyawan untuk laporan individual'),

                        Forms\Components\TextInput::make('kode_laporan')
                            ->label('Kode Laporan')
                            ->readonly()
                            ->helperText('Format: LAP-DIV{ID}-{YYYYMM} atau LAP-IND{DIVID}{USERID}-{YYYYMM}'),

                        Forms\Components\DatePicker::make('periode_mulai')
                            ->label('Periode Mulai')
                            ->required()
                            ->default(now()->startOfMonth()),

                        Forms\Components\DatePicker::make('periode_selesai')
                            ->label('Periode Selesai')
                            ->required()
                            ->default(now()->endOfMonth()),
                    ])->columns(3),

                Forms\Components\Section::make('Hasil Evaluasi')
                    ->description('Hasil otomatis berdasarkan realisasi KPI/OKR')
                    ->schema([
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('load_realisasi')
                                ->label('Generate Laporan')
                                ->icon('heroicon-o-document-chart-bar')
                                ->color('success')
                                ->action(function ($record, $set, $get) {
                                    if (!$record) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error')
                                            ->body('Simpan laporan terlebih dahulu sebelum generate data')
                                            ->danger()
                                            ->send();
                                        return;
                                    }

                                    try {
                                        // Call populate method
                                        $record->populateDataFromRealisasi();
                                        
                                        // Refresh record to get updated data
                                        $record->refresh();
                                        
                                        // Update form fields
                                        $set('rata_rata_score', $record->rata_rata_score);
                                        
                                        \Filament\Notifications\Notification::make()
                                            ->title('Laporan berhasil digenerate!')
                                            ->body("Data evaluasi telah dihitung: KPI ({$record->total_kpi}), OKR ({$record->total_okr}), Score ({$record->rata_rata_score}%)")
                                            ->success()
                                            ->duration(5000)
                                            ->send();
                                    } catch (\Exception $exception) {
                                        \Filament\Notifications\Notification::make()
                                            ->title('Error saat generate laporan')
                                            ->body('Detail error: ' . $exception->getMessage())
                                            ->danger()
                                            ->duration(8000)
                                            ->send();
                                    }
                                })
                                ->visible(fn($record) => $record !== null),
                        ]),

                        Forms\Components\TextInput::make('rata_rata_score')
                            ->label('Overall Performance Score')
                            ->numeric()
                            ->suffix('%')
                            ->readonly()
                            ->default(0)
                            ->helperText('Rata-rata pencapaian dari semua KPI dan OKR'),

                        Forms\Components\Textarea::make('catatan_evaluasi')
                            ->label('Catatan Evaluasi')
                            ->rows(4)
                            ->placeholder('Tambahkan catatan atau rekomendasi khusus dari HR...'),
                    ])->columns(1),

                // Hidden fields untuk data yang auto-generated
                Forms\Components\Hidden::make('dibuat_oleh')
                    ->default(fn() => auth()->id()),
                Forms\Components\Hidden::make('total_kpi')
                    ->default(0),
                Forms\Components\Hidden::make('total_okr')
                    ->default(0),
                Forms\Components\Hidden::make('pencapaian_kpi')
                    ->default(0),
                Forms\Components\Hidden::make('pencapaian_okr')
                    ->default(0),
                Forms\Components\Hidden::make('data_laporan')
                    ->default('{}'),
                Forms\Components\Hidden::make('kpi_references')
                    ->default('[]'),
                Forms\Components\Hidden::make('okr_references')
                    ->default('[]'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_laporan')
                    ->label('Kode Laporan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->description(fn($record) => 'Terintegrasi dengan sistem KPI/OKR'),

                Tables\Columns\TextColumn::make('tipe_laporan')
                    ->label('Jenis')
                    ->badge()
                    ->color(fn(string $state) => match($state) {
                        'individual' => 'primary',
                        'divisi' => 'success',
                        default => 'gray'
                    })
                    ->formatStateUsing(fn(string $state) => match($state) {
                        'individual' => 'Individual',
                        'divisi' => 'Divisi',
                        default => ucfirst($state)
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->toggleable()
                    ->placeholder('N/A'),

                Tables\Columns\TextColumn::make('divisi.nama')
                    ->label('Divisi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('periode_display')
                    ->label('Periode')
                    ->getStateUsing(fn($record) => $record->periode_mulai->format('d/m/Y') . ' - ' . $record->periode_selesai->format('d/m/Y'))
                    ->sortable(['periode_mulai']),

                Tables\Columns\TextColumn::make('kpi_okr_summary')
                    ->label('KPI/OKR')
                    ->getStateUsing(function ($record) {
                        $kpiCount = $record->total_kpi ?? 0;
                        $okrCount = $record->total_okr ?? 0;
                        return "KPI: {$kpiCount} | OKR: {$okrCount}";
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('rata_rata_score')
                    ->label('Overall Score')
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info', 
                        $state >= 70 => 'warning',
                        $state >= 60 => 'danger',
                        default => 'gray'
                    }),

                // Status Tindak Lanjut
                Tables\Columns\TextColumn::make('follow_up_status')
                    ->label('Tindak Lanjut')
                    ->getStateUsing(function ($record) {
                        $followUp = \App\Models\TindakLanjut::where('laporan_evaluasi_id', $record->id)->first();
                        if (!$followUp) {
                            return $record->rata_rata_score < 80 ? 'Perlu Tindak Lanjut' : 'Tidak Perlu';
                        }
                        return match($followUp->status_pelaksanaan) {
                            'planned' => 'Direncanakan',
                            'in_progress' => 'Sedang Berlangsung',
                            'completed' => 'Selesai',
                            'cancelled' => 'Dibatalkan',
                            default => 'Unknown'
                        };
                    })
                    ->badge()
                    ->color(function ($record) {
                        $followUp = \App\Models\TindakLanjut::where('laporan_evaluasi_id', $record->id)->first();
                        if (!$followUp) {
                            return $record->rata_rata_score < 80 ? 'warning' : 'success';
                        }
                        return match($followUp->status_pelaksanaan) {
                            'planned' => 'warning',
                            'in_progress' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger',
                            default => 'gray'
                        };
                    }),

                Tables\Columns\TextColumn::make('dibuatOleh.name')
                    ->label('Dibuat Oleh')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_laporan')
                    ->label('Jenis Laporan')
                    ->options([
                        'individual' => 'Individual',
                        'divisi' => 'Divisi'
                    ]),

                Tables\Filters\SelectFilter::make('status_laporan')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'pending_approval' => 'Pending Approval',
                        'approved' => 'Approved',
                        'published' => 'Published'
                    ]),

                Tables\Filters\SelectFilter::make('status_kinerja')
                    ->label('Performance')
                    ->options([
                        'Excellent' => 'Excellent',
                        'Good' => 'Good',
                        'Average' => 'Average',
                        'Below Average' => 'Below Average',
                        'Poor' => 'Poor'
                    ]),

                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('periode_from')
                            ->label('Periode Dari'),
                        Forms\Components\DatePicker::make('periode_until')
                            ->label('Periode Sampai'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['periode_from'], fn ($q) => $q->where('periode_mulai', '>=', $data['periode_from']))
                            ->when($data['periode_until'], fn ($q) => $q->where('periode_selesai', '<=', $data['periode_until']));
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('Lihat detail laporan'),

                Tables\Actions\EditAction::make()
                    ->tooltip('Edit laporan'),

                Tables\Actions\Action::make('export')
                    ->label('Export Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('laporan.export', $record->id))
                    ->openUrlInNewTab()
                    ->tooltip('Download laporan dalam format Excel'),

                // Tindak Lanjut Action - Sederhana untuk KPI/OKR yang tidak achieve
                Tables\Actions\Action::make('create_follow_up')
                    ->label('Buat Tindak Lanjut')
                    ->icon('heroicon-o-clipboard-document-check')
                    ->color('warning')
                    ->visible(fn($record) => $record->rata_rata_score < 80) // Hanya untuk yang perlu improvement
                    ->form([
                        Forms\Components\TextInput::make('employee_name')
                            ->label('Karyawan')
                            ->default(fn($record) => $record->user?->name ?? 'N/A')
                            ->disabled(),

                        Forms\Components\TextInput::make('current_score')
                            ->label('Score Saat Ini')
                            ->default(fn($record) => number_format($record->rata_rata_score, 1) . '%')
                            ->disabled(),

                        Forms\Components\Select::make('jenis_tindakan')
                            ->label('Jenis Tindakan')
                            ->options([
                                'pelatihan' => 'Pelatihan',
                                'coaching' => 'Coaching',
                                'development_plan' => 'Development Plan',
                                'peringatan' => 'Performance Warning',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('deskripsi_tindakan')
                            ->label('Deskripsi Tindakan')
                            ->required()
                            ->rows(3),

                        Forms\Components\DatePicker::make('timeline_mulai')
                            ->label('Tanggal Mulai')
                            ->required()
                            ->default(now()),

                        Forms\Components\DatePicker::make('timeline_selesai')
                            ->label('Target Selesai')
                            ->required()
                            ->after('timeline_mulai'),

                        Forms\Components\Select::make('pic_responsible')
                            ->label('PIC Penanggung Jawab')
                            ->options(function () {
                                return \App\Models\User::whereIn('role', ['manager', 'hr'])
                                    ->get()
                                    ->pluck('name', 'id');
                            })
                            ->required(),
                    ])
                    ->action(function ($record, array $data) {
                        \App\Models\TindakLanjut::create([
                            'kode_tindak_lanjut' => \App\Models\TindakLanjut::generateAutoId(),
                            'laporan_evaluasi_id' => $record->id,
                            'user_id' => $record->user_id,
                            'jenis_tindakan' => $data['jenis_tindakan'],
                            'deskripsi_tindakan' => $data['deskripsi_tindakan'],
                            'timeline_mulai' => $data['timeline_mulai'],
                            'timeline_selesai' => $data['timeline_selesai'],
                            'pic_responsible' => $data['pic_responsible'],
                            'status_pelaksanaan' => 'planned',
                            'progress_percentage' => 0,
                            'dibuat_oleh' => auth()->id(),
                        ]);

                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Tindak Lanjut Berhasil Dibuat')
                            ->body('Action plan telah dibuat untuk karyawan ini.')
                            ->send();
                    })
                    ->modalHeading('Buat Tindak Lanjut untuk Karyawan')
                    ->modalWidth('2xl')
                    ->tooltip('Buat tindak lanjut untuk karyawan dengan performa yang perlu diperbaiki'),

                Tables\Actions\Action::make('populate_references')
                    ->label('Sync KPI/OKR')
                    ->icon('heroicon-o-arrow-path')
                    ->color('info')
                    ->action(function ($record) {
                        $record->populateReferences();
                        \Filament\Notifications\Notification::make()
                            ->title('Referensi KPI/OKR berhasil disinkronisasi')
                            ->success()
                            ->send();
                    })
                    ->tooltip('Sinkronisasi dengan data KPI/OKR terbaru'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->visible(fn($records) => $records ? $records->every(fn($record) => $record->status_laporan === 'draft') : false),

                    Tables\Actions\BulkAction::make('bulk_export')
                        ->label('Export Terpilih')
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function ($records) {
                            if (!$records || $records->isEmpty()) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->body('Tidak ada data yang dipilih')
                                    ->danger()
                                    ->send();
                                return;
                            }
                            
                            \Filament\Notifications\Notification::make()
                                ->title('Export bulk berhasil')
                                ->body('Fitur bulk export akan tersedia segera')
                                ->info()
                                ->send();
                        }),
                ]),
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
            'index' => Pages\ListLaporanEvaluasis::route('/'),
            'create' => Pages\CreateLaporanEvaluasi::route('/create'),
            'edit' => Pages\EditLaporanEvaluasi::route('/{record}/edit'),
        ];
    }

    /**
     * Generate kode laporan berdasarkan divisi, tipe, dan karyawan
     */
    protected static function generateKodeLaporan($divisiId, $tipeLaporan, $set, $userId = null)
    {
        if (!$divisiId || !$tipeLaporan) {
            return;
        }

        // Get divisi data
        $divisi = \App\Models\Divisi::find($divisiId);
        if (!$divisi) {
            return;
        }

        // Generate kode divisi (3 digit)
        $kodeDivisi = str_pad($divisiId, 3, '0', STR_PAD_LEFT);
        
        // Get current year-month
        $yearMonth = date('Ym');
        
        if ($tipeLaporan === 'individual' && $userId) {
            // For individual reports: include user code
            $user = \App\Models\User::find($userId);
            if (!$user) {
                return;
            }
            
            // Generate user code (3 digit)
            $kodeUser = str_pad($userId, 3, '0', STR_PAD_LEFT);
            
            // Get next sequence for this user and period
            $lastSequence  = \App\Models\LaporanEvaluasi::where('user_id', $userId)
                ->where('kode_laporan', 'LIKE', "LAP-IND{$kodeDivisi}-{$kodeUser}-{$yearMonth}-%")
                ->count();
            
            // $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
            
            // Generate final kode for individual
            $kode = "LAP-IND{$kodeDivisi}-{$kodeUser}-{$yearMonth}";
            
        } else {
            // For divisi reports: divisi only
            $lastSequence = \App\Models\LaporanEvaluasi::where('divisi_id', $divisiId)
                ->where('tipe_laporan', 'divisi')
                ->where('kode_laporan', 'LIKE', "LAP-DIV{$kodeDivisi}-{$yearMonth}-%")
                ->count();
            
            // $nextSequence = str_pad($lastSequence + 1, 3, '0', STR_PAD_LEFT);
            
            // Generate final kode for divisi
            $kode = "LAP-DIV{$kodeDivisi}-{$yearMonth}";
        }
        
        $set('kode_laporan', $kode);
    }
}
