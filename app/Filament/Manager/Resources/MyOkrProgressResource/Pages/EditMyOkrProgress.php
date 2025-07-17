<?php

namespace App\Filament\Manager\Resources\MyOkrProgressResource\Pages;

use App\Filament\Manager\Resources\MyOkrProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyOkrProgress extends EditRecord
{
    protected static string $resource = MyOkrProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
