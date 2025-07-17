<?php

namespace App\Filament\Hr\Resources\PerformanceAppraisalScoreResource\Pages;

use App\Filament\Hr\Resources\PerformanceAppraisalScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPerformanceAppraisalScores extends ListRecords
{
    protected static string $resource = PerformanceAppraisalScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
