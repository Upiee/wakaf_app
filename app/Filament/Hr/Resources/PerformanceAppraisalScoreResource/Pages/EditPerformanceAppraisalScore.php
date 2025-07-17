<?php

namespace App\Filament\Hr\Resources\PerformanceAppraisalScoreResource\Pages;

use App\Filament\Hr\Resources\PerformanceAppraisalScoreResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPerformanceAppraisalScore extends EditRecord
{
    protected static string $resource = PerformanceAppraisalScoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
