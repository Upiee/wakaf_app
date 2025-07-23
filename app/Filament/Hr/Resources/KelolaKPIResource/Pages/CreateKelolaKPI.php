<?php

namespace App\Filament\Hr\Resources\KelolaKPIResource\Pages;

use App\Filament\Hr\Resources\KelolaKPIResource;
use App\Models\KelolaKPI;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKelolaKPI extends CreateRecord
{
    protected static string $resource = KelolaKPIResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate ID berdasarkan assignment type
        if (isset($data['assignment_type'])) {
            $targetId = $data['assignment_type'] === 'divisi' ? $data['divisi_id'] : $data['user_id'];
            $autoId = KelolaKPI::generateAutoId($data['assignment_type'], $targetId);
            
            if ($autoId) {
                $data['id'] = $autoId;
            }
        }
        
        return $data;
    }
}
