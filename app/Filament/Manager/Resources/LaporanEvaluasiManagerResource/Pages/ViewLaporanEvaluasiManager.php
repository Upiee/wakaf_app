<?php

namespace App\Filament\Manager\Resources\LaporanEvaluasiManagerResource\Pages;

use App\Filament\Manager\Resources\LaporanEvaluasiManagerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewLaporanEvaluasiManager extends ViewRecord
{
    protected static string $resource = LaporanEvaluasiManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export Excel')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('laporan.export', $this->record->getAttribute('id')))
                ->openUrlInNewTab(),
        ];
    }

    public function getTitle(): string 
    {
        return 'Detail Laporan Evaluasi - ' . $this->record->getAttribute('kode_laporan');
    }
}
