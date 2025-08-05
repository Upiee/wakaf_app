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
                ->description('Jumlah divisi aktif saat ini')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('success'),
                
            Stat::make('Total Karyawan', User::count())
                ->description('Semua user di sistem')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
                
            // Stat::make('KPI Aktif', KelolaKPI::count())
            //     ->description('Total KPI yang terdaftar')
            //     ->descriptionIcon('heroicon-m-chart-bar-square')
            //     ->color('warning'),
                
            Stat::make('KPIs Assigned to Division', KelolaKPI::where('assignment_type', 'divisi')->count())
                ->description('KPI yang di-assign ke divisi')
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
                
            // Stat::make('OKR Aktif', KelolaOKR::count())
            //     ->description('Total OKR yang terdaftar')
            //     ->descriptionIcon('heroicon-m-flag')
            //     ->color('primary'),
                
            Stat::make('OKRs Assigned to Division', KelolaOKR::where('assignment_type', 'divisi')->count())
                ->description('OKR yang di-assign ke divisi')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),
                
            Stat::make('Managers', User::where('role', 'manager')->count())
                ->description('Total manager divisi yang telah mengelola KPI/OKR')
                ->descriptionIcon('heroicon-m-user-group')
                ->color('success'),
                
            Stat::make('Employees', User::where('role', 'employee')->count())
                ->description('Total karyawan yang telah mengelola KPI/OKR')
                ->descriptionIcon('heroicon-m-identification')
                ->color('info'),
        ];
    }
}
