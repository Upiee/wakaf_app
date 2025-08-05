<?php

namespace App\Filament\Manager\Widgets;

use App\Models\User;
use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class TeamOverview extends BaseWidget
{
    protected static ?string $heading = 'Overview Tim Divisi';
    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $divisiId = $user->divisi_id;

        return $table
            ->query(
                User::query()
                    ->where('divisi_id', $divisiId)
                    ->where('id', '!=', $user->id) // Exclude manager
                    ->with(['divisi'])
                    ->orderBy('name')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color('primary'),
                    
                Tables\Columns\TextColumn::make('kpi_count')
                    ->label('KPI Assigned')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        return KelolaKPI::where('user_id', $record->id)
                            ->where('assignment_type', 'individual')
                            ->count();
                    }),
                    
                Tables\Columns\TextColumn::make('okr_count')
                    ->label('OKR Assigned')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        return KelolaOKR::where('user_id', $record->id)
                            ->where('assignment_type', 'individual')
                            ->count();
                    }),
                    
                Tables\Columns\TextColumn::make('kpi_realisasi')
                    ->label('KPI Realisasi')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        $currentQuarter = 'Q2-2025'; // Using Q2-2025 where we have data
                        return RealisasiKpi::where('user_id', $record->id)
                            ->where('periode', $currentQuarter)
                            ->count();
                    }),
                    
                Tables\Columns\TextColumn::make('okr_realisasi')
                    ->label('OKR Realisasi')
                    ->numeric()
                    ->getStateUsing(function ($record) {
                        $currentQuarter = 'Q2-2025'; // Using Q2-2025 where we have data
                        return RealisasiOkr::where('user_id', $record->id)
                            ->where('periode', $currentQuarter)
                            ->count();
                    }),
                    
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color('success')
                    ->formatStateUsing(fn ($record) => 'Active'),
            ])
            ->emptyStateHeading('Tidak ada anggota tim')
            ->emptyStateDescription('Belum ada anggota tim di divisi ini.')
            ->emptyStateIcon('heroicon-o-users')
            ->paginated(false);
    }
}
