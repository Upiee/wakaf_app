<?php

namespace App\Filament\Manager\Resources\EmployeeKpiApprovalResource\Pages;

use App\Filament\Manager\Resources\EmployeeKpiApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewEmployeeKpiApproval extends ViewRecord
{
    protected static string $resource = EmployeeKpiApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Review & Approve')
                ->visible(fn () => $this->record->can_be_approved),
        ];
    }
}
