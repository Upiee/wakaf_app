<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubActivityProgressTracking extends Model
{
    protected $table = 'sub_activity_progress_tracking';
    
    protected $fillable = [
        'kpi_sub_activity_id',
        'okr_sub_activity_id',
        'periode',
        'progress_value',
        'status',
        'keterangan',
        'dokumen',
        'updated_by',
        'completed_at'
    ];

    protected $casts = [
        'progress_value' => 'decimal:2',
        'completed_at' => 'datetime'
    ];

    // Relationships
    public function kpiSubActivity(): BelongsTo
    {
        return $this->belongsTo(KpiSubActivity::class, 'kpi_sub_activity_id');
    }

    public function okrSubActivity(): BelongsTo
    {
        return $this->belongsTo(OkrSubActivity::class, 'okr_sub_activity_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Helper methods
    public function getSubActivity()
    {
        return $this->kpiSubActivity ?? $this->okrSubActivity;
    }

    public function getParentActivity()
    {
        $subActivity = $this->getSubActivity();
        return $subActivity ? ($subActivity->kpi ?? $subActivity->okr) : null;
    }

    public function getActivityType(): string
    {
        return $this->kpi_sub_activity_id ? 'kpi' : 'okr';
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'not_started' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'blocked' => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'not_started' => 'Not Started',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'blocked' => 'Blocked',
            default => 'Unknown'
        };
    }
}
