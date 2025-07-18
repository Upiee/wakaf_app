<?php

namespace App\Filament\Manager\Resources\RealisasiOkrResource\Pages;

use App\Filament\Manager\Resources\RealisasiOkrResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRealisasiOkrs extends ListRecords
{
    protected static string $resource = RealisasiOkrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
