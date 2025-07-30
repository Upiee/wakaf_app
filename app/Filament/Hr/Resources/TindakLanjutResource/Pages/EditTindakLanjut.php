<?php

namespace App\Filament\Hr\Resources\TindakLanjutResource\Pages;

use App\Filament\Hr\Resources\TindakLanjutResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTindakLanjut extends EditRecord
{
    protected static string $resource = TindakLanjutResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make()
                ->label('Lihat')
                ->icon('heroicon-o-eye'),
            Actions\DeleteAction::make()
                ->label('Hapus')
                ->icon('heroicon-o-trash'),
        ];
    }
}
