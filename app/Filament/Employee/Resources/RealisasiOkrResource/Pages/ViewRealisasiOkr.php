<?php

namespace App\Filament\Employee\Resources\RealisasiOkrResource\Pages;

use App\Filament\Employee\Resources\RealisasiOkrResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiOkr extends ViewRecord
{
    protected static string $resource = RealisasiOkrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
