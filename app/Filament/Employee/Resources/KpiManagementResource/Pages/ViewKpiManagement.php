<?php

namespace App\Filament\Employee\Resources\KpiManagementResource\Pages;

use App\Filament\Employee\Resources\KpiManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewKpiManagement extends ViewRecord
{
    protected static string $resource = KpiManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No actions available for employees
        ];
    }
}