<?php

namespace App\Filament\Manager\Resources\EmployeeKpiApprovalResource\Pages;

use App\Filament\Manager\Resources\EmployeeKpiApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Auth;

class EditEmployeeKpiApproval extends EditRecord
{
    protected static string $resource = EmployeeKpiApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check')
                ->color('success')
                ->visible(fn () => $this->record->can_be_approved)
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->approve();
                    
                    Notification::make()
                        ->success()
                        ->title('KPI Approved')
                        ->body('KPI realization has been approved successfully.')
                        ->send();
                    
                    return redirect()->to(static::getResource()::getUrl('index'));
                }),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Handle approval action
        if (isset($data['approval_action'])) {
            if ($data['approval_action'] === 'approve') {
                $data['approved_by'] = Auth::user()->id;
                $data['approved_at'] = now();
            } else {
                $data['approved_by'] = null;
                $data['approved_at'] = null;
            }
            
            // Save manager notes to JSON
            if (isset($data['manager_notes'])) {
                $this->record->manager_notes = $data['manager_notes'];
            }
            
            unset($data['approval_action']);
        }
        
        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Approval Updated')
            ->body('KPI approval has been updated successfully.');
    }
}
