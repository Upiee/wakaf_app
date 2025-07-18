<?php

namespace App\Filament\Manager\Resources\MyOkrProgressResource\Pages;

use App\Filament\Manager\Resources\MyOkrProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyOkrProgress extends ListRecords
{
    protected static string $resource = MyOkrProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
