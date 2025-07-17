<?php

namespace App\Filament\Hr\Resources\KelolaOKRResource\Pages;

use App\Filament\Hr\Resources\KelolaOKRResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKelolaOKRS extends ListRecords
{
    protected static string $resource = KelolaOKRResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
