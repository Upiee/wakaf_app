<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class KelolaOKR extends Model
{
    protected $table = 'kelola__o_k_r_s';
    protected $primaryKey = 'id';
    public $incrementing = false; // Changed to false karena kita pakai string ID
    protected $keyType = 'string'; // Changed to string

    protected $fillable = [
        'activity',
        'output',
        'bobot',
        'indikator_progress',
        'progress',
        'dokumen',
        'periode',
        'timeline',
        'timeline_realisasi',
        'realisasi',
        'tipe',
        'is_editable',
        // Assignment fields
        'assignment_type',
        'divisi_id',
        'user_id',
        'status',
        'notes',
        'priority',
    ];

    protected $attributes = [
        'tipe' => 'okr',
        'output' => '',
    ];

    public function indikatorProgress()
    {
        return $this->hasMany(OkrIndikatorProgress::class, 'okr_id', 'id');
    }

    // Assignment relationships
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relationship with realisasi
    public function realisasiOkr()
    {
        return $this->hasMany(RealisasiOkr::class, 'okr_id', 'id');
    }

    // Relationship to sub-activities
    public function subActivities(): HasMany
    {
        return $this->hasMany(OkrSubActivity::class, 'okr_id');
    }

    // Accessor to get assigned members count
    public function getAssignedMembersCountAttribute()
    {
        if ($this->assignment_type === 'divisi' && $this->divisi) {
            return $this->divisi->users()->count();
        }
        return $this->assignment_type === 'individual' ? 1 : 0;
    }

    // Scope for filtering by assignment
    public function scopeAssignedToDivisi($query, $divisiId)
    {
        return $query->where('assignment_type', 'divisi')
                    ->where('divisi_id', $divisiId);
    }

    public function scopeAssignedToUser($query, $userId)
    {
        return $query->where(function($q) use ($userId) {
            $q->where('assignment_type', 'individual')
              ->where('user_id', $userId)
              ->orWhere(function($subQ) use ($userId) {
                  $subQ->where('assignment_type', 'divisi')
                       ->whereHas('divisi.users', function($userQ) use ($userId) {
                           $userQ->where('users.id', $userId);
                       });
              });
        });
    }

    // Calculate total progress from sub-activities
    public function calculateTotalProgress(): float
    {
        $subActivities = $this->subActivities()->where('is_active', true)->get();
        
        if ($subActivities->isEmpty()) {
            return 0;
        }

        $totalWeightedProgress = 0;
        $totalWeight = 0;

        foreach ($subActivities as $subActivity) {
            $weight = $subActivity->bobot ?? 1; // Default weight 1 if no bobot
            $totalWeightedProgress += ($subActivity->progress_percentage * $weight);
            $totalWeight += $weight;
        }

        $averageProgress = $totalWeight > 0 ? $totalWeightedProgress / $totalWeight : 0;
        
        // Update main OKR progress
        $this->update(['progress' => $averageProgress]);
        
        return $averageProgress;
    }

    // Get completion status based on sub-activities
    public function getCompletionStatusAttribute(): string
    {
        $subActivities = $this->subActivities()->where('is_active', true)->get();
        
        if ($subActivities->isEmpty()) {
            return 'no_sub_activities';
        }

        $completedCount = $subActivities->where('status', 'completed')->count();
        $totalCount = $subActivities->count();

        if ($completedCount === $totalCount) {
            return 'completed';
        } elseif ($completedCount > 0) {
            return 'in_progress';
        } else {
            return 'not_started';
        }
    }
    
    /**
     * Generate auto ID untuk OKR berdasarkan assignment type
     */
    public static function generateAutoId($assignmentType, $targetId)
    {
        if ($assignmentType === 'divisi') {
            // Format: OKR-DIV-001-001
            $divisi = Divisi::find($targetId);
            if (!$divisi) return null;
            
            $divisiCode = str_replace('DIV-', '', $divisi->kode); // Get 001 from DIV-001
            
            // Get last sequence untuk divisi ini
            $lastOkr = self::where('id', 'like', "OKR-DIV-{$divisiCode}-%")
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
            $lastOkr = self::where('id', 'like', "OKR-IND-{$userCode}-%")
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


