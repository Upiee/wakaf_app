<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class KelolaOKR extends Model
{
    protected $table = 'kelola__o_k_r_s';
    protected $primaryKey = 'id';
    public $incrementing = true; // Change to true since it's auto-incrementing
    protected $keyType = 'int';

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
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Automatically set the 'tipe' attribute if not set
            if ($model->assignment_type == 'divisi') {
                $model->tipe = 'okr divisi';
            } elseif ($model->assignment_type == 'individual') {
                $model->tipe = 'okr individu';
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
                $model->tipe = 'okr divisi';
            } elseif ($model->assignment_type == 'individual') {
                $model->tipe = 'okr individu';
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
        return $divisiCode . ".OKR-" . str_pad($this->id, 4, '0', STR_PAD_LEFT);
    }

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
}


