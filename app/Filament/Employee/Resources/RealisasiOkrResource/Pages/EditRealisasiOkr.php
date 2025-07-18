<?php

namespace App\Filament\Employee\Resources\RealisasiOkrResource\Pages;

use App\Filament\Employee\Resources\RealisasiOkrResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRealisasiOkr extends EditRecord
{
    protected static string $resource = RealisasiOkrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn () => !$this->record->is_cutoff && !$this->record->approved_at),
        ];
    }

    // Cek apakah record bisa diedit
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Jika data sudah final atau sudah di-approve, redirect dengan notifikasi
        if ($this->record->is_cutoff || $this->record->approved_at) {
            Notification::make()
                ->danger()
                ->title('Tidak dapat mengedit!')
                ->body('Data realisasi ini sudah final atau sudah di-approve dan tidak dapat diubah.')
                ->send();

            $this->redirect(static::getResource()::getUrl('index'));
        }

        return $data;
    }
}
