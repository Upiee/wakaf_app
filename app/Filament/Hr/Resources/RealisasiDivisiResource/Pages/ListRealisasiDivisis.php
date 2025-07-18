<?php

namespace App\Filament\Hr\Resources\RealisasiDivisiResource\Pages;

use App\Filament\Hr\Resources\RealisasiDivisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiDivisis extends ListRecords
{
    protected static string $resource = RealisasiDivisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Hapus create action karena hanya untuk view
        ];
    }
    
    public function getTitle(): string
    {
        return 'Realisasi Divisi';
    }
    
    public function getHeading(): string
    {
        return 'Monitoring Realisasi Divisi';
    }
}
