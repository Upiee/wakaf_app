<?php

namespace App\Filament\Hr\Resources\RealisasiIndividuResource\Pages;

use App\Filament\Hr\Resources\RealisasiIndividuResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiIndividu extends EditRecord
{
    protected static string $resource = RealisasiIndividuResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
