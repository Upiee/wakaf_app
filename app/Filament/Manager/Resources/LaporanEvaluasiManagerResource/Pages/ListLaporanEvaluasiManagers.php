<?php

namespace App\Filament\Manager\Resources\LaporanEvaluasiManagerResource\Pages;

use App\Filament\Manager\Resources\LaporanEvaluasiManagerResource;
use Filament\Resources\Pages\ListRecords;

class ListLaporanEvaluasiManagers extends ListRecords
{
    protected static string $resource = LaporanEvaluasiManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for managers
        ];
    }

    public function getTitle(): string
    {
        return 'Laporan Evaluasi Kinerja Tim';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            // Bisa tambah widget statistik nanti
        ];
    }
}
