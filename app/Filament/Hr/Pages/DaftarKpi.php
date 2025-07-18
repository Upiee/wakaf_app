<?php

namespace App\Filament\Hr\Pages;

use Filament\Pages\Page;

class DaftarKpi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-list-bullet';
    protected static ?string $navigationGroup = 'Manajemen KPI';
    protected static ?string $navigationLabel = 'Daftar KPI';
    protected static ?int $navigationSort = 0;

    protected static string $view = 'filament.hr.pages.daftar-kpi';
}
