<?php

namespace App\Filament\Hr\Resources\SetKpiOkrResource\Pages;

use App\Filament\Hr\Resources\SetKpiOkrResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSetKpiOkr extends EditRecord
{
    protected static string $resource = SetKpiOkrResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
