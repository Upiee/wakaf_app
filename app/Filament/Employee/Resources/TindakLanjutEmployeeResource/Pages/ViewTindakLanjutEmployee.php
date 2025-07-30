<?php

namespace App\Filament\Employee\Resources\TindakLanjutEmployeeResource\Pages;

use App\Filament\Employee\Resources\TindakLanjutEmployeeResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTindakLanjutEmployee extends ViewRecord
{
    protected static string $resource = TindakLanjutEmployeeResource::class;

    public function getTitle(): string 
    {
        $jenis = ucfirst($this->record->getAttribute('jenis_tindakan') ?? 'Tindak Lanjut');
        return 'My Follow-up: ' . $jenis;
    }
}
