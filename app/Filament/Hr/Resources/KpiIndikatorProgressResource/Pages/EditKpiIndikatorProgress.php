<?php

namespace App\Filament\Hr\Resources\KpiIndikatorProgressResource\Pages;

use App\Filament\Hr\Resources\KpiIndikatorProgressResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKpiIndikatorProgress extends EditRecord
{
    protected static string $resource = KpiIndikatorProgressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
