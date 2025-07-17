<?php

namespace App\Filament\Employee\Resources\RealisasiKpiResource\Pages;

use App\Filament\Employee\Resources\RealisasiKpiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiKpi extends ViewRecord
{
    protected static string $resource = RealisasiKpiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
