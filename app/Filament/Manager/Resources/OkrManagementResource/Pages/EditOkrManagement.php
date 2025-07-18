<?php

namespace App\Filament\Manager\Resources\OkrManagementResource\Pages;

use App\Filament\Manager\Resources\OkrManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOkrManagement extends EditRecord
{
    protected static string $resource = OkrManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
