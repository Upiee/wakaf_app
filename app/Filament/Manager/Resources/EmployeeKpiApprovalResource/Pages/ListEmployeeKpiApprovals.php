<?php

namespace App\Filament\Manager\Resources\EmployeeKpiApprovalResource\Pages;

use App\Filament\Manager\Resources\EmployeeKpiApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEmployeeKpiApprovals extends ListRecords
{
    protected static string $resource = EmployeeKpiApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
