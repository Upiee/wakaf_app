<?php

namespace App\Filament\Employee\Resources\OkrManagementResource\Pages;

use App\Filament\Employee\Resources\OkrManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOkrManagement extends ListRecords
{
    protected static string $resource = OkrManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
