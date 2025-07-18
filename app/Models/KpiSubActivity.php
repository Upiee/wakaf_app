<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KpiSubActivity extends Model
{
    protected $table = 'kpi_sub_activities';
    
    protected $fillable = [
        'kpi_id',
        'output',
        'bobot',
        'indikator',
        'progress_percentage',
        'status',
        'target_date',
        'actual_date',
        'dokumen',
        'keterangan',
        'is_active'
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
        'progress_percentage' => 'decimal:2',
        'target_date' => 'date',
        'actual_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function kpi(): BelongsTo
    {
        return $this->belongsTo(KelolaKPI::class, 'kpi_id');
    }

    public function progressTracking(): HasMany
    {
        return $this->hasMany(SubActivityProgressTracking::class, 'kpi_sub_activity_id');
    }

    // Helper methods
    public function updateProgress(float $progress, string $keterangan = null, string $dokumen = null, string $periode = null): void
    {
        // Update main progress
        $this->update(['progress_percentage' => $progress]);
        
        // Update status based on progress
        if ($progress >= 100) {
            $this->update(['status' => 'completed', 'actual_date' => now()]);
        } elseif ($progress > 0) {
            $this->update(['status' => 'in_progress']);
        }

        // Create progress tracking record
        if ($periode) {
            $this->progressTracking()->updateOrCreate(
                ['periode' => $periode],
                [
                    'progress_value' => $progress,
                    'status' => $progress >= 100 ? 'completed' : 'in_progress',
                    'keterangan' => $keterangan,
                    'dokumen' => $dokumen,
                    'updated_by' => auth()->id(),
                    'completed_at' => $progress >= 100 ? now() : null
                ]
            );
        }
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'in_progress' => 'blue',
            'completed' => 'green',
            'overdue' => 'red',
            default => 'gray'
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'overdue' => 'Overdue',
            default => 'Unknown'
        };
    }
}
