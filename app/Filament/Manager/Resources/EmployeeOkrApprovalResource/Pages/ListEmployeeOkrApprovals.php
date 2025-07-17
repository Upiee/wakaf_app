<?php

namespace App\Filament\Manager\Resources\EmployeeOkrApprovalResource\Pages;

use App\Filament\Manager\Resources\EmployeeOkrApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeOkrApprovals extends ListRecords
{
    protected static string $resource = EmployeeOkrApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
