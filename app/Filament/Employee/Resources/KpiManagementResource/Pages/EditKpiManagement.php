<?php

namespace App\Filament\Employee\Resources\KpiManagementResource\Pages;

use App\Filament\Employee\Resources\KpiManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiManagement extends EditRecord
{
    protected static string $resource = KpiManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
