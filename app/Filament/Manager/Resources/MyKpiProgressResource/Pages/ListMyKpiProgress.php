<?php

namespace App\Filament\Manager\Resources\MyKpiProgressResource\Pages;

use App\Filament\Manager\Resources\MyKpiProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyKpiProgress extends ListRecords
{
    protected static string $resource = MyKpiProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
