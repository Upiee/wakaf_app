<?php

namespace App\Filament\Manager\Resources;

use App\Filament\Manager\Resources\LaporanEvaluasiManagerResource\Pages;
use App\Models\LaporanEvaluasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class LaporanEvaluasiManagerResource extends Resource
{
    protected static ?string $model = LaporanEvaluasi::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationLabel = 'Laporan Evaluasi';
    protected static ?string $navigationGroup = 'Report';
    protected static ?int $navigationSort = 50;

    // Filter hanya untuk divisi manager
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('divisi_id', Auth::user()->getAttribute('divisi_id'));
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Form untuk view saja - tidak bisa edit
                Forms\Components\Section::make('Informasi Laporan')
                    ->schema([
                        Forms\Components\TextInput::make('kode_laporan')
                            ->label('Kode Laporan')
                            ->disabled(),

                        Forms\Components\Select::make('tipe_laporan')
                            ->label('Jenis Laporan')
                            ->options([
                                'individual' => 'Laporan Individual',
                                'divisi' => 'Laporan Divisi'
                            ])
                            ->disabled(),

                        Forms\Components\TextInput::make('user.name')
                            ->label('Karyawan')
                            ->disabled()
                            ->visible(fn($record) => $record && $record->tipe_laporan === 'individual'),

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

                Forms\Components\Section::make('Hasil Evaluasi')
                    ->schema([
                        Forms\Components\TextInput::make('rata_rata_score')
                            ->label('Overall Performance Score')
                            ->suffix('%')
                            ->disabled(),

                        Forms\Components\Textarea::make('catatan_evaluasi')
                            ->label('Catatan Evaluasi')
                            ->disabled()
                            ->rows(4),
                    ])->columns(1),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_laporan')
                    ->label('Kode Laporan')
                    ->searchable()
                    ->sortable(),

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
                    ->placeholder('N/A - Laporan Divisi'),

                Tables\Columns\TextColumn::make('periode_display')
                    ->label('Periode')
                    ->getStateUsing(fn($record) => $record->periode_mulai->format('d/m/Y') . ' - ' . $record->periode_selesai->format('d/m/Y')),

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
                    ->label('Performance Score')
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

                Tables\Columns\TextColumn::make('follow_up_status')
                    ->label('Tindak Lanjut')
                    ->getStateUsing(function ($record) {
                        $followUp = \App\Models\TindakLanjut::where('laporan_evaluasi_id', $record->id)->first();
                        if (!$followUp) {
                            return $record->rata_rata_score < 80 ? 'Perlu Tindak Lanjut' : 'Tidak Perlu';
                        }
                        return match($followUp->getAttribute('status_pelaksanaan')) {
                            'planned' => 'Direncanakan',
                            'in_progress' => 'Sedang Berlangsung',
                            'completed' => 'Selesai',
                            default => 'Unknown'
                        };
                    })
                    ->badge()
                    ->color(function ($record) {
                        $followUp = \App\Models\TindakLanjut::where('laporan_evaluasi_id', $record->id)->first();
                        if (!$followUp) {
                            return $record->rata_rata_score < 80 ? 'warning' : 'success';
                        }
                        return match($followUp->getAttribute('status_pelaksanaan')) {
                            'planned' => 'warning',
                            'in_progress' => 'info',
                            'completed' => 'success',
                            default => 'gray'
                        };
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipe_laporan')
                    ->label('Jenis Laporan')
                    ->options([
                        'individual' => 'Individual',
                        'divisi' => 'Divisi'
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

                Tables\Filters\Filter::make('performance')
                    ->form([
                        Forms\Components\Select::make('performance_level')
                            ->label('Level Performance')
                            ->options([
                                'excellent' => 'Excellent (90-100%)',
                                'good' => 'Good (80-89%)',
                                'average' => 'Average (70-79%)',
                                'below' => 'Below Average (60-69%)',
                                'poor' => 'Poor (<60%)',
                            ])
                    ])
                    ->query(function ($query, array $data) {
                        if (!$data['performance_level']) return $query;
                        
                        return match($data['performance_level']) {
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
                    ->tooltip('Lihat detail laporan'),

                Tables\Actions\Action::make('export')
                    ->label('Export')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->url(fn ($record) => route('laporan.export', $record->id))
                    ->openUrlInNewTab()
                    ->tooltip('Download laporan Excel'),
            ])
            ->bulkActions([
                // No bulk actions untuk manager
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Belum Ada Laporan Evaluasi')
            ->emptyStateDescription('Laporan evaluasi akan muncul setelah HR membuat dan generate laporan untuk divisi Anda.')
            ->emptyStateIcon('heroicon-o-document-chart-bar');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLaporanEvaluasiManagers::route('/'),
            'view' => Pages\ViewLaporanEvaluasiManager::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false; // Manager tidak bisa create laporan
    }

    public static function canEdit($record): bool
    {
        return false; // Manager tidak bisa edit laporan
    }

    public static function canDelete($record): bool
    {
        return false; // Manager tidak bisa delete laporan
    }
}
