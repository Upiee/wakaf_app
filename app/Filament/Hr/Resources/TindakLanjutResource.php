<?php

namespace App\Filament\Hr\Resources;

use App\Filament\Hr\Resources\TindakLanjutResource\Pages;
use App\Models\TindakLanjut;
use App\Models\User;
use App\Models\LaporanEvaluasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TindakLanjutResource extends Resource
{
    protected static ?string $model = TindakLanjut::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Tindak Lanjut';
    protected static ?string $navigationGroup = 'Report';
    protected static ?int $navigationSort = 40;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Informasi Dasar
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('kode_tindak_lanjut')
                            ->label('Kode Tindak Lanjut')
                            ->default(fn() => TindakLanjut::generateAutoId())
                            ->disabled()
                            ->dehydrated(),

                        Forms\Components\Select::make('laporan_evaluasi_id')
                            ->label('Laporan Evaluasi')
                            ->options(function () {
                                return LaporanEvaluasi::with('user')
                                    ->get()
                                    ->mapWithKeys(function ($laporan) {
                                        return [$laporan->getAttribute('id') => 
                                            ($laporan->user?->name ?? 'N/A') . ' - ' . 
                                            ($laporan->getAttribute('periode_quartal') ?? 'N/A') . ' (Score: ' . 
                                            number_format($laporan->getAttribute('rata_rata_score') ?? 0, 1) . '%)'
                                        ];
                                    });
                            })
                            ->searchable()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, $set) {
                                if ($state) {
                                    $laporan = LaporanEvaluasi::find($state);
                                    if ($laporan) {
                                        $set('user_id', $laporan->getAttribute('user_id'));
                                    }
                                }
                            }),

                        Forms\Components\Hidden::make('user_id')
                            ->required()
                            ->afterStateHydrated(function ($component, $state, $get) {
                                if (!$state && $get('laporan_evaluasi_id')) {
                                    $laporan = LaporanEvaluasi::find($get('laporan_evaluasi_id'));
                                    if ($laporan) {
                                        $component->state($laporan->user_id);
                                    }
                                }
                            }),

                        Forms\Components\Placeholder::make('karyawan_info')
                            ->label('Karyawan Terkait')
                            ->content(function ($get) {
                                $laporanId = $get('laporan_evaluasi_id');
                                if ($laporanId) {
                                    $laporan = LaporanEvaluasi::with('user.divisi')->find($laporanId);
                                    if ($laporan && $laporan->user) {
                                        return $laporan->user->name . ' - ' . ($laporan->user->divisi->nama ?? 'No Division');
                                    }
                                }
                                return 'Pilih laporan evaluasi terlebih dahulu';
                            })
                            ->visible(fn($get) => $get('laporan_evaluasi_id')),
                    ])->columns(2),

                // Detail Tindak Lanjut
                Forms\Components\Section::make('Detail Tindak Lanjut')
                    ->schema([
                        Forms\Components\Select::make('jenis_tindakan')
                            ->label('Jenis Tindakan')
                            ->options([
                                'pelatihan' => 'Pelatihan',
                                'coaching' => 'Coaching',
                                'peringatan' => 'Performance Warning',
                                'development_plan' => 'Development Plan',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('deskripsi_tindakan')
                            ->label('Deskripsi Tindakan')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

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
                                return User::whereIn('role', ['manager', 'hr'])
                                    ->get()
                                    ->mapWithKeys(function ($user) {
                                        return [$user->id => $user->name . ' - ' . ucfirst($user->role)];
                                    });
                            })
                            ->required(),

                        Forms\Components\Select::make('status_pelaksanaan')
                            ->label('Status')
                            ->options([
                                'planned' => 'Direncanakan',
                                'in_progress' => 'Sedang Berlangsung',
                                'completed' => 'Selesai',
                            ])
                            ->default('planned')
                            ->required(),

                        // Forms\Components\TextInput::make('progress_percentage')
                        //     ->label('Progress (%)')
                        //     ->numeric()
                        //     ->minValue(0)
                        //     ->maxValue(100)
                        //     ->suffix('%')
                        //     ->default(0),
                    ])->columns(2),

                // Hidden fields
                Forms\Components\Hidden::make('dibuat_oleh')
                    ->default(fn() => Auth::id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('kode_tindak_lanjut')
                    ->label('Kode')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Karyawan')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record) => $record->user?->divisi?->nama ?? 'No Division'),

                Tables\Columns\TextColumn::make('laporanEvaluasi.rata_rata_score')
                    ->label('Score Evaluasi')
                    ->numeric(decimalPlaces: 1)
                    ->suffix('%')
                    ->sortable()
                    ->badge()
                    ->color(fn($state) => match(true) {
                        $state >= 80 => 'success',
                        $state >= 70 => 'warning',
                        default => 'danger'
                    }),

                Tables\Columns\TextColumn::make('jenis_tindakan')
                    ->label('Jenis Tindakan')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'pelatihan' => 'info',
                        'coaching' => 'warning',
                        'peringatan' => 'danger',
                        'development_plan' => 'primary',
                        default => 'secondary'
                    }),

                Tables\Columns\TextColumn::make('picResponsible.name')
                    ->label('PIC')
                    ->searchable(),

                Tables\Columns\TextColumn::make('timeline_selesai')
                    ->label('Target Selesai')
                    ->date('d/m/Y')
                    ->sortable()
                    ->color(fn($record) => 
                        ($record->timeline_selesai < now() && $record->status_pelaksanaan !== 'completed') 
                        ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('status_pelaksanaan')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'planned' => 'warning',
                        'in_progress' => 'info',
                        'completed' => 'success',
                        default => 'secondary'
                    }),

                // Tables\Columns\TextColumn::make('progress_percentage')
                //     ->label('Progress')
                //     ->numeric()
                //     ->suffix('%')
                //     ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status_pelaksanaan')
                    ->label('Status')
                    ->options([
                        'planned' => 'Direncanakan',
                        'in_progress' => 'Sedang Berlangsung',
                        'completed' => 'Selesai',
                    ]),

                Tables\Filters\SelectFilter::make('jenis_tindakan')
                    ->label('Jenis Tindakan')
                    ->options([
                        'pelatihan' => 'Pelatihan',
                        'coaching' => 'Coaching',
                        'peringatan' => 'Performance Warning',
                        'development_plan' => 'Development Plan',
                    ]),

                Tables\Filters\SelectFilter::make('pic_responsible')
                    ->label('PIC')
                    ->relationship('picResponsible', 'name'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                
                Tables\Actions\Action::make('mark_completed')
                    ->label('Tandai Selesai')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status_pelaksanaan !== 'completed')
                    ->action(function ($record) {
                        $record->update([
                            'status_pelaksanaan' => 'completed',
                            'progress_percentage' => 100,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Tindak Lanjut Selesai')
                            ->body('Tindak lanjut telah ditandai selesai.')
                            ->send();
                    })
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListTindakLanjuts::route('/'),
            'create' => Pages\CreateTindakLanjut::route('/create'),
            'view' => Pages\ViewTindakLanjut::route('/{record}'),
            'edit' => Pages\EditTindakLanjut::route('/{record}/edit'),
        ];
    }
}
