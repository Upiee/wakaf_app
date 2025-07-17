<?php

namespace App\Filament\Manager\Widgets;

use App\Models\RealisasiKpi;
use App\Models\RealisasiOkr;
use App\Models\KpiIndikatorProgress;
use App\Models\OkrIndikatorProgress;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class RecentActivities extends BaseWidget
{
    protected static ?string $heading = 'Aktivitas Terbaru';
    protected static ?int $sort = 3;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $user = Auth::user();
        $divisiId = $user->divisi_id;

        return $table
            ->query(
                RealisasiKpi::query()
                    ->where('divisi_id', $divisiId)
                    ->with(['kpi', 'user'])
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('type')
                    ->label('Tipe')
                    ->badge()
                    ->color('primary')
                    ->formatStateUsing(fn ($record) => 'KPI')
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('kpi.activity')
                    ->label('Aktivitas')
                    ->limit(40)
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Oleh')
                    ->searchable(),
                    
                Tables\Columns\TextColumn::make('nilai')
                    ->label('Nilai')
                    ->suffix('%')
                    ->numeric()
                    ->sortable(),
                    
                Tables\Columns\TextColumn::make('periode')
                    ->label('Periode')
                    ->badge()
                    ->color('warning'),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
            ])
            ->paginated(false)
            ->poll('30s'); // Auto refresh setiap 30 detik
    }
}
