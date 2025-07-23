<?php

namespace App\Traits;

use App\Models\Divisi;
use App\Models\User;

trait AutoGenerateId
{
    /**
     * Generate auto ID untuk KPI berdasarkan assignment type
     */
    public function generateKpiId($assignmentType, $targetId)
    {
        $prefix = 'KPI';
        
        if ($assignmentType === 'divisi') {
            // Format: KPI-DIV-001-001
            $divisi = Divisi::find($targetId);
            if (!$divisi) return null;
            
            $divisiCode = str_replace('DIV-', '', $divisi->kode); // Get 001 from DIV-001
            
            // Get last sequence untuk divisi ini
            $lastKpi = \App\Models\KelolaKPI::where('id', 'like', "KPI-DIV-{$divisiCode}-%")
                                         ->orderBy('id', 'desc')
                                         ->first();
            
            $sequence = 1;
            if ($lastKpi) {
                // Extract sequence dari ID terakhir (KPI-DIV-001-005 -> 5)
                $parts = explode('-', $lastKpi->id);
                if (count($parts) >= 4) {
                    $sequence = intval($parts[3]) + 1;
                }
            }
            
            return sprintf('KPI-DIV-%s-%03d', $divisiCode, $sequence);
            
        } elseif ($assignmentType === 'individual') {
            // Format: KPI-IND-EMP00001-001
            $user = User::find($targetId);
            if (!$user) return null;
            
            $userCode = $user->kode; // EMP00001
            
            // Get last sequence untuk user ini
            $lastKpi = \App\Models\KelolaKPI::where('id', 'like', "KPI-IND-{$userCode}-%")
                                         ->orderBy('id', 'desc')
                                         ->first();
            
            $sequence = 1;
            if ($lastKpi) {
                // Extract sequence dari ID terakhir
                $parts = explode('-', $lastKpi->id);
                if (count($parts) >= 4) {
                    $sequence = intval($parts[3]) + 1;
                }
            }
            
            return sprintf('KPI-IND-%s-%03d', $userCode, $sequence);
        }
        
        return null;
    }
    
    /**
     * Generate auto ID untuk OKR berdasarkan assignment type
     */
    public function generateOkrId($assignmentType, $targetId)
    {
        $prefix = 'OKR';
        
        if ($assignmentType === 'divisi') {
            // Format: OKR-DIV-001-001
            $divisi = Divisi::find($targetId);
            if (!$divisi) return null;
            
            $divisiCode = str_replace('DIV-', '', $divisi->kode); // Get 001 from DIV-001
            
            // Get last sequence untuk divisi ini
            $lastOkr = \App\Models\KelolaOKR::where('id', 'like', "OKR-DIV-{$divisiCode}-%")
                                         ->orderBy('id', 'desc')
                                         ->first();
            
            $sequence = 1;
            if ($lastOkr) {
                // Extract sequence dari ID terakhir
                $parts = explode('-', $lastOkr->id);
                if (count($parts) >= 4) {
                    $sequence = intval($parts[3]) + 1;
                }
            }
            
            return sprintf('OKR-DIV-%s-%03d', $divisiCode, $sequence);
            
        } elseif ($assignmentType === 'individual') {
            // Format: OKR-IND-EMP00001-001
            $user = User::find($targetId);
            if (!$user) return null;
            
            $userCode = $user->kode; // EMP00001
            
            // Get last sequence untuk user ini
            $lastOkr = \App\Models\KelolaOKR::where('id', 'like', "OKR-IND-{$userCode}-%")
                                         ->orderBy('id', 'desc')
                                         ->first();
            
            $sequence = 1;
            if ($lastOkr) {
                // Extract sequence dari ID terakhir
                $parts = explode('-', $lastOkr->id);
                if (count($parts) >= 4) {
                    $sequence = intval($parts[3]) + 1;
                }
            }
            
            return sprintf('OKR-IND-%s-%03d', $userCode, $sequence);
        }
        
        return null;
    }
}
