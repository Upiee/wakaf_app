<?php

namespace App\Filament\Hr\Resources\RealisasiDivisiResource\Pages;

use App\Filament\Hr\Resources\RealisasiDivisiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewRealisasiDivisi extends ViewRecord
{
    protected static string $resource = RealisasiDivisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(false), // Hidden karena hanya untuk view
        ];
    }
}
