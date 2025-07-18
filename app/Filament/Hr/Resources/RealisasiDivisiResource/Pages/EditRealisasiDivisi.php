<?php

namespace App\Filament\Hr\Resources\RealisasiDivisiResource\Pages;

use App\Filament\Hr\Resources\RealisasiDivisiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiDivisi extends EditRecord
{
    protected static string $resource = RealisasiDivisiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
