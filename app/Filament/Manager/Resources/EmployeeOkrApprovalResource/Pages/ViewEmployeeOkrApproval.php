<?php

namespace App\Filament\Manager\Resources\EmployeeOkrApprovalResource\Pages;

use App\Filament\Manager\Resources\EmployeeOkrApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeOkrApproval extends ViewRecord
{
    protected static string $resource = EmployeeOkrApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
