<?php

namespace App\Filament\Hr\Resources\TindakLanjutResource\Pages;

use App\Filament\Hr\Resources\TindakLanjutResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTindakLanjut extends ViewRecord
{
    protected static string $resource = TindakLanjutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->label('Edit')
                ->icon('heroicon-o-pencil-square'),
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash'),
        ];
    }
}
