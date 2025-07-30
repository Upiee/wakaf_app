<?php

namespace App\Filament\Employee\Resources\LaporanEvaluasiEmployeeResource\Pages;

use App\Filament\Employee\Resources\LaporanEvaluasiEmployeeResource;
use Filament\Resources\Pages\ListRecords;

class ListLaporanEvaluasiEmployees extends ListRecords
{
    protected static string $resource = LaporanEvaluasiEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions for employees
        ];
    }

    public function getTitle(): string
    {
        return 'Performance Evaluasi Saya';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa tambah widget performance chart nanti
        ];
    }
}
