<?php

namespace App\Filament\Hr\Resources\KelolaOKRResource\Pages;

use App\Filament\Hr\Resources\KelolaOKRResource;
use App\Models\KelolaOKR;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateKelolaOKR extends CreateRecord
{
    protected static string $resource = KelolaOKRResource::class;
    
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Auto-generate ID berdasarkan assignment type
        if (isset($data['assignment_type'])) {
            $targetId = $data['assignment_type'] === 'divisi' ? $data['divisi_id'] : $data['user_id'];
            $autoId = KelolaOKR::generateAutoId($data['assignment_type'], $targetId);
            
            if ($autoId) {
                $data['id'] = $autoId;
            }
        }
        
        return $data;
    }
}
