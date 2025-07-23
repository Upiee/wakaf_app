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

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically set the 'tipe' attribute if not set
            if ($model->assignment_type == 'divisi') {
                $model->tipe = 'kpi divisi';
            } elseif ($model->assignment_type == 'individual') {
                $model->tipe = 'kpi individu';
            }

            $model->status = 'active';

            if ($model->user && $model->user->divisi_id) {
                $model->divisi_id = $model->user->divisi_id;
            } elseif ($model->divisi_id) {
                $model->user_id = null; // Clear user_id if divisi_id is set
            }
        });

        static::updating(function ($model) {
            // Ensure 'tipe' is set correctly on update
            if ($model->assignment_type == 'divisi') {
                $model->tipe = 'kpi divisi';
            } elseif ($model->assignment_type == 'individual') {
                $model->tipe = 'kpi individu';
            }

            $model->status = 'active';

            if ($model->user && $model->user->divisi_id) {
                $model->divisi_id = $model->user->divisi_id;
            } elseif ($model->divisi_id) {
                $model->user_id = null; // Clear user_id if divisi_id is set
            }
        });
    }

    public function getCodeIdAttribute()
    {
        $divisiCode = $this->divisi->kode ?? 'UNK';
        return $divisiCode . ".KPI-" . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

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

    public function getAchievementAttribute(): float
    {
        $subActivities = $this->subActivities()->where('is_active', true)->get();   

        if ($subActivities->isEmpty()) {
            return 0;
        }

        $totalAchievement = 0;
        
        foreach ($subActivities as $subActivity) {
            $achievement = $subActivity->realisasi_kpi_total ?? 0; // Default to 0 if no achievement
            $totalAchievement += $achievement;
        }

        $totalBobot = $subActivities->sum('bobot') ?: 1; // Avoid division by zero

        return $totalAchievement / $totalBobot * 100; // Return as percentage
    }
}


