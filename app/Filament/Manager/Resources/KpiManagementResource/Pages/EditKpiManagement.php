<?php

namespace App\Filament\Manager\Resources\KpiManagementResource\Pages;

use App\Filament\Manager\Resources\KpiManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiManagement extends EditRecord
{
    protected static string $resource = KpiManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
