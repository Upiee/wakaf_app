<?php

namespace App\Filament\Manager\Resources\ManajemenTimResource\Pages;

use App\Filament\Manager\Resources\ManajemenTimResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListManajemenTims extends ListRecords
{
    protected static string $resource = ManajemenTimResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
