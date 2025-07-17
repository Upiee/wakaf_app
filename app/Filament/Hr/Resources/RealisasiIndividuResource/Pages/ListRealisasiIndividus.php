<?php

namespace App\Filament\Hr\Resources\RealisasiIndividuResource\Pages;

use App\Filament\Hr\Resources\RealisasiIndividuResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiIndividus extends ListRecords
{
    protected static string $resource = RealisasiIndividuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Tidak ada create action karena view-only
        ];
    }
    
    public function getTitle(): string
    {
        return 'Realisasi Individu';
    }
    
    public function getHeading(): string
    {
        return 'Monitoring Realisasi Individu';
    }
}
