<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KelolaKPI extends Model
{
    protected $table = 'kelola__k_p_i_s';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'activity',
        'bobot',
        'output',
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
        'tipe' => 'kpi',
    ];

    public function indikatorProgress()
    {
        return $this->hasMany(KpiIndikatorProgress::class, 'kpi_id', 'id');
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
    public function realisasiKpi()
    {
        return $this->hasMany(RealisasiKpi::class, 'kpi_id', 'id');
    }

    // Relationship to sub-activities
    public function subActivities()
    {
        return $this->hasMany(KpiSubActivity::class, 'kpi_id');
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
        
        // Update main KPI progress
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
}


