<?php

namespace App\Filament\Hr\Resources\KelolaKPIResource\Pages;

use App\Filament\Hr\Resources\KelolaKPIResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelolaKPIS extends ListRecords
{
    protected static string $resource = KelolaKPIResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
