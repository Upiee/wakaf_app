<?php

namespace App\Filament\Manager\Resources\KpiManagementResource\Pages;

use App\Filament\Manager\Resources\KpiManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKpiManagement extends ListRecords
{
    protected static string $resource = KpiManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
