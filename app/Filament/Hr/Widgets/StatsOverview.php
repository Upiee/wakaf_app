<?php

namespace App\Filament\Hr\Widgets;

use App\Models\User;
use App\Models\Divisi;
use App\Models\KelolaKPI;
use App\Models\KelolaOKR;
use App\Models\RealisasiDivisi;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Divisi', Divisi::count())
                ->description('Jumlah divisi aktif')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
                
            Stat::make('Total Karyawan', User::count())
                ->description('Semua user di sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            Stat::make('KPI Aktif', KelolaKPI::count())
                ->description('Total KPI yang terdaftar')
                ->descriptionIcon('heroicon-m-chart-bar-square')
                ->color('warning'),
                
            Stat::make('OKR Aktif', KelolaOKR::count())
                ->description('Total OKR yang terdaftar')
                ->descriptionIcon('heroicon-m-flag')
                ->color('primary'),
                
            Stat::make('Managers', User::where('role', 'manager')->count())
                ->description('Total manager divisi')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
                
            Stat::make('Employees', User::where('role', 'employee')->count())
                ->description('Total employee')
                ->descriptionIcon('heroicon-m-identification')
                ->color('info'),
        ];
    }
}
