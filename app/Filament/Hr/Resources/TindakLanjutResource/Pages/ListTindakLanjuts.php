<?php

namespace App\Filament\Hr\Resources\TindakLanjutResource\Pages;

use App\Filament\Hr\Resources\TindakLanjutResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTindakLanjuts extends ListRecords
{
    protected static string $resource = TindakLanjutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Buat Tindak Lanjut')
                ->icon('heroicon-o-plus'),
        ];
    }
}
