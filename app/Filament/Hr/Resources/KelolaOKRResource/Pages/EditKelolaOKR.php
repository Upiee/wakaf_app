<?php

namespace App\Filament\Hr\Resources\KelolaOKRResource\Pages;

use App\Filament\Hr\Resources\KelolaOKRResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKelolaOKR extends EditRecord
{
    protected static string $resource = KelolaOKRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
