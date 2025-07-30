<?php

namespace App\Filament\Hr\Resources\TindakLanjutResource\Pages;

use App\Filament\Hr\Resources\TindakLanjutResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateTindakLanjut extends CreateRecord
{
    protected static string $resource = TindakLanjutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->icon('heroicon-o-arrow-left')
                ->color('gray'),
        ];
    }
}
