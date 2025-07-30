<?php

namespace App\Filament\Employee\Resources;

use App\Filament\Employee\Resources\LaporanEvaluasiEmployeeResource\Pages;
use App\Models\LaporanEvaluasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaporanEvaluasiEmployeeResource extends Resource
{
    protected static ?string $model = LaporanEvaluasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Evaluasi Saya';
    protected static ?string $navigationGroup = 'Performa Saya';
    protected static ?int $navigationSort = 10;

    // Filter hanya untuk laporan pribadi employee
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('user_id', Auth::id());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Forms\Components\TextInput::make('kode_laporan')
                            ->label('Kode Laporan')
                            ->disabled(),

                        Forms\Components\TextInput::make('divisi.nama')
                            ->label('Divisi')
                            ->disabled(),

                        Forms\Components\DatePicker::make('periode_mulai')
                            ->label('Periode Mulai')
                            ->disabled(),

                        Forms\Components\DatePicker::make('periode_selesai')
                            ->label('Periode Selesai')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Performance Saya')
                    ->schema([
                        Forms\Components\TextInput::make('rata_rata_score')
                            ->label('Overall Performance Score')
                            ->suffix('%')
                            ->disabled()
                            ->extraAttributes(['style' => 'font-size: 1.2em; font-weight: bold;']),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('total_kpi')
                                    ->label('Total KPI')
                                    ->disabled(),

                                Forms\Components\TextInput::make('total_okr')
                                    ->label('Total OKR')
                                    ->disabled(),

                                Forms\Components\TextInput::make('pencapaian_kpi')
                                    ->label('Pencapaian KPI')
                                    ->suffix('%')
                                    ->disabled(),

                                Forms\Components\TextInput::make('pencapaian_okr')
                                    ->label('Pencapaian OKR')
                                    ->suffix('%')
                                    ->disabled(),
                            ]),

                        Forms\Components\Textarea::make('catatan_evaluasi')
                            ->label('Catatan dari HR/Manager')
                            ->disabled()
                            ->rows(4),
                    ]),

                Forms\Components\Section::make('Tindak Lanjut')
                    ->schema([
                        Forms\Components\Placeholder::make('follow_up_info')
                            ->label('')
                            ->content(function ($record) {
                                if (!$record) return 'Belum ada data tindak lanjut.';
                                
                                $followUp = \App\Models\TindakLanjut::where('laporan_evaluasi_id', $record->getAttribute('id'))->first();
                                
                                if (!$followUp) {
                                    return $record->getAttribute('rata_rata_score') < 80 
                                        ? '⚠️ Performance Anda memerlukan tindak lanjut. Tim HR akan menghubungi Anda segera.'
                                        : '✅ Performance Anda baik, tidak memerlukan tindak lanjut khusus.';
                                }
                                
                                $status = match($followUp->getAttribute('status_pelaksanaan')) {
                                    'planned' => '📋 Rencana tindak lanjut sedang disiapkan',
                                    'in_progress' => '🚀 Tindak lanjut sedang berlangsung',
                                    'completed' => '✅ Tindak lanjut telah selesai',
                                    default => 'Status tidak diketahui'
                                };
                                
                                return $status . "\n\nJenis: " . ucfirst($followUp->getAttribute('jenis_tindakan') ?? 'N/A') .
                                       "\nProgress: " . ($followUp->getAttribute('progress_percentage') ?? 0) . '%';
                            })
                    ])
                    ->visible(fn($record) => $record !== null),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_laporan')
                    ->label('Kode Laporan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('periode_display')
                    ->label('Periode')
                    ->getStateUsing(fn($record) => $record->periode_mulai->format('M Y')),

                Tables\Columns\TextColumn::make('total_activities')
                    ->label('Total Aktivitas')
                    ->getStateUsing(function ($record) {
                        $kpiCount = $record->getAttribute('total_kpi') ?? 0;
                        $okrCount = $record->getAttribute('total_okr') ?? 0;
                        return ($kpiCount + $okrCount) . ' aktivitas';
                    })
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('rata_rata_score')
                    ->label('Performance Score')
                    ->suffix('%')
                    ->sortable()
                    ->size('lg')
                    ->weight('bold')
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 90 => 'success',
                        $state >= 80 => 'info', 
                        $state >= 70 => 'warning',
                        $state >= 60 => 'danger',
                        default => 'gray'
                    }),

                Tables\Columns\TextColumn::make('performance_level')
                    ->label('Level')
                    ->getStateUsing(fn($record) => match(true) {
                        $record->getAttribute('rata_rata_score') >= 90 => 'Excellent',
                        $record->getAttribute('rata_rata_score') >= 80 => 'Good',
                        $record->getAttribute('rata_rata_score') >= 70 => 'Average',
                        $record->getAttribute('rata_rata_score') >= 60 => 'Below Average',
                        default => 'Poor'
                    })
                    ->badge()
                    ->color(fn($record) => match(true) {
                        $record->getAttribute('rata_rata_score') >= 90 => 'success',
                        $record->getAttribute('rata_rata_score') >= 80 => 'info',
                        $record->getAttribute('rata_rata_score') >= 70 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\IconColumn::make('needs_follow_up')
                    ->label('Tindak Lanjut')
                    ->getStateUsing(fn($record) => $record->getAttribute('rata_rata_score') < 80)
                    ->boolean()
                    ->tooltip(fn($record) => 
                        $record->getAttribute('rata_rata_score') < 80 
                            ? 'Memerlukan tindak lanjut' 
                            : 'Tidak perlu tindak lanjut'
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->date('d/m/Y')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\Filter::make('periode')
                    ->form([
                        Forms\Components\DatePicker::make('periode_from')
                            ->label('Dari Tanggal'),
                        Forms\Components\DatePicker::make('periode_until')
                            ->label('Sampai Tanggal'),
                    ])
                    ->query(function ($query, array $data) {
                        return $query
                            ->when($data['periode_from'], fn ($q) => $q->where('periode_mulai', '>=', $data['periode_from']))
                            ->when($data['periode_until'], fn ($q) => $q->where('periode_selesai', '<=', $data['periode_until']));
                    }),

                Tables\Filters\SelectFilter::make('performance_range')
                    ->label('Level Performance')
                    ->options([
                        'excellent' => 'Excellent (90-100%)',
                        'good' => 'Good (80-89%)',
                        'average' => 'Average (70-79%)',
                        'below' => 'Below Average (60-69%)',
                        'poor' => 'Poor (<60%)',
                    ])
                    ->query(function ($query, array $data) {
                        if (!$data['value']) return $query;
                        
                        return match($data['value']) {
                            'excellent' => $query->where('rata_rata_score', '>=', 90),
                            'good' => $query->whereBetween('rata_rata_score', [80, 89]),
                            'average' => $query->whereBetween('rata_rata_score', [70, 79]),
                            'below' => $query->whereBetween('rata_rata_score', [60, 69]),
                            'poor' => $query->where('rata_rata_score', '<', 60),
                            default => $query
                        };
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->tooltip('Lihat detail performance'),

                Tables\Actions\Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('laporan.export', $record->getAttribute('id')))
                    ->openUrlInNewTab()
                    ->tooltip('Download laporan saya'),
            ])
            ->bulkActions([
                // No bulk actions untuk employee
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Laporan Evaluasi')
            ->emptyStateDescription('Laporan evaluasi performance Anda akan muncul setelah HR melakukan evaluasi bulanan.')
            ->emptyStateIcon('heroicon-o-chart-bar');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanEvaluasiEmployees::route('/'),
            'view' => Pages\ViewLaporanEvaluasiEmployee::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Employee tidak bisa create laporan
    }

    public static function canEdit($record): bool
    {
        return false; // Employee tidak bisa edit laporan
    }

    public static function canDelete($record): bool
    {
        return false; // Employee tidak bisa delete laporan
    }
}
