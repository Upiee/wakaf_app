<?php

namespace App\Filament\Employee\Resources\TindakLanjutEmployeeResource\Pages;

use App\Filament\Employee\Resources\TindakLanjutEmployeeResource;
use Filament\Resources\Pages\ListRecords;

class ListTindakLanjutEmployees extends ListRecords
{
    protected static string $resource = TindakLanjutEmployeeResource::class;

    public function getTitle(): string
    {
        return 'Tindak Lanjut Performance Saya';
    }
}
