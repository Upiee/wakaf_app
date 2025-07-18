<?php

namespace App\Filament\Employee\Resources\KpiManagementResource\Pages;

use App\Filament\Employee\Resources\KpiManagementResource;
use Filament\Resources\Pages\ListRecords;

class ListKpiManagement extends ListRecords
{
    protected static string $resource = KpiManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for read-only resource
        ];
    }
}
