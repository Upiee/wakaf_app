<?php

namespace App\Filament\Employee\Resources\LaporanEvaluasiEmployeeResource\Pages;

use App\Filament\Employee\Resources\LaporanEvaluasiEmployeeResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Actions;

class ViewLaporanEvaluasiEmployee extends ViewRecord
{
    protected static string $resource = LaporanEvaluasiEmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('Export Laporan Saya')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('success')
                ->url(fn () => route('laporan.export', $this->record->getAttribute('id')))
                ->openUrlInNewTab(),

            Actions\Action::make('print')
                ->label('Print')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->action(fn () => $this->js('window.print()')),
        ];
    }

    public function getTitle(): string 
    {
        $score = $this->record->getAttribute('rata_rata_score');
        $level = match(true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Good',
            $score >= 70 => 'Average', 
            $score >= 60 => 'Below Average',
            default => 'Poor'
        };
        
        return 'Performance Report - ' . $level . ' (' . $score . '%)';
    }
}
