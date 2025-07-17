<?php

namespace App\Filament\Hr\Resources\PerformanceAppraisalResource\Pages;

use App\Filament\Hr\Resources\PerformanceAppraisalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerformanceAppraisals extends ListRecords
{
    protected static string $resource = PerformanceAppraisalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
