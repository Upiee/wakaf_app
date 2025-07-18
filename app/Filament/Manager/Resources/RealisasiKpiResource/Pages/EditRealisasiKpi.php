<?php

namespace App\Filament\Manager\Resources\RealisasiKpiResource\Pages;

use App\Filament\Manager\Resources\RealisasiKpiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiKpi extends EditRecord
{
    protected static string $resource = RealisasiKpiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
