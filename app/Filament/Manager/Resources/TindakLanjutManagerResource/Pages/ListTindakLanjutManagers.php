<?php

namespace App\Filament\Manager\Resources\TindakLanjutManagerResource\Pages;

use App\Filament\Manager\Resources\TindakLanjutManagerResource;
use Filament\Resources\Pages\ListRecords;

class ListTindakLanjutManagers extends ListRecords
{
    protected static string $resource = TindakLanjutManagerResource::class;

    public function getTitle(): string
    {
        return 'Tindak Lanjut Tim';
    }
}
