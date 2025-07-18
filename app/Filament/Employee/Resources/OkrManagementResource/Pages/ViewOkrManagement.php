<?php

namespace App\Filament\Employee\Resources\OkrManagementResource\Pages;

use App\Filament\Employee\Resources\OkrManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOkrManagement extends ViewRecord
{
    protected static string $resource = OkrManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
