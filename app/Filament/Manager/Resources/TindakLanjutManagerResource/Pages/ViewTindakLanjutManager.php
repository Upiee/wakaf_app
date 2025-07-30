<?php

namespace App\Filament\Manager\Resources\TindakLanjutManagerResource\Pages;

use App\Filament\Manager\Resources\TindakLanjutManagerResource;
use Filament\Resources\Pages\ViewRecord;

class ViewTindakLanjutManager extends ViewRecord
{
    protected static string $resource = TindakLanjutManagerResource::class;

    public function getTitle(): string 
    {
        return 'Detail Tindak Lanjut - ' . $this->record->getAttribute('kode_tindak_lanjut');
    }
}
