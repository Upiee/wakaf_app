<?php

namespace App\Filament\Hr\Resources\LaporanEvaluasiResource\Pages;

use App\Filament\Hr\Resources\LaporanEvaluasiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanEvaluasis extends ListRecords
{
    protected static string $resource = LaporanEvaluasiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
