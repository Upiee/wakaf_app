<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealisasiOkr extends Model
{
    protected $table = 'realisasi_okrs';

    protected $fillable = [
        'divisi_id',
        'okr_id',
        'okr_sub_activity_id',
        'user_id', // untuk tracking siapa yang input
        'nilai',
        'periode',
        'keterangan',
        'is_cutoff',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'is_cutoff' => 'boolean',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
    ];

    // Virtual attributes untuk approval logic
    protected $appends = ['approval_status', 'is_editable', 'can_be_approved'];

    // Virtual attribute: Status approval
    public function getApprovalStatusAttribute()
    {
        if ($this->approved_at) {
            return 'approved';
        }
        if ($this->rejected_at) {
            return 'rejected';
        }
        if ($this->is_cutoff) {
            return 'pending_approval';
        }
        return 'draft';
    }

    public function indikator()
    {
        return $this->belongsTo(OkrSubActivity::class, 'okr_sub_activity_id', 'id');
    }

    // Virtual attribute: Apakah masih bisa diedit
    public function getIsEditableAttribute()
    {
        // Bisa edit jika:
        // 1. Masih draft (belum final dan belum approved)
        // 2. Sudah di-reject (bisa diperbaiki)
        return (!$this->is_cutoff && !$this->approved_at) || $this->rejected_at;
    }

    // Virtual attribute: Apakah bisa di-approve
    public function getCanBeApprovedAttribute()
    {
        return $this->is_cutoff && !$this->approved_at;
    }

    // Method untuk approve
    public function approve($managerId = null)
    {
        $this->update([
            'approved_by' => $managerId ?? auth()->id(),
            'approved_at' => now(),
        ]);
    }

    // Method untuk set final
    public function setFinal()
    {
        $this->update(['is_cutoff' => true]);
    }

    // Method untuk cek apakah sudah locked
    public function isLocked()
    {
        return $this->is_cutoff || $this->approved_at;
    }

    // Menggunakan keterangan sebagai JSON untuk data tambahan
    public function getManagerNotesAttribute()
    {
        if ($this->keterangan && is_string($this->keterangan)) {
            $data = json_decode($this->keterangan, true);
            return $data['manager_notes'] ?? '';
        }
        return '';
    }

    public function setManagerNotesAttribute($value)
    {
        $currentData = $this->keterangan ? json_decode($this->keterangan, true) : [];
        $currentData['manager_notes'] = $value;
        $this->attributes['keterangan'] = json_encode($currentData);
    }

    public function getEmployeeNotesAttribute()
    {
        if ($this->keterangan && is_string($this->keterangan)) {
            $data = json_decode($this->keterangan, true);
            return $data['employee_notes'] ?? $this->keterangan;
        }
        return $this->keterangan ?? '';
    }

    public function setEmployeeNotesAttribute($value)
    {
        $currentData = $this->keterangan ? json_decode($this->keterangan, true) : [];
        $currentData['employee_notes'] = $value;
        $this->attributes['keterangan'] = json_encode($currentData);
    }

    // Relationships
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id', 'id');
    }

    public function okr()
    {
        return $this->belongsTo(KelolaOKR::class, 'okr_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by', 'id');
    }

    // Scopes
    public function scopeByDivisi($query, $divisiId)
    {
        return $query->where('divisi_id', $divisiId);
    }

    public function scopeByPeriode($query, $periode)
    {
        return $query->where('periode', $periode);
    }

    public function scopeFinal($query)
    {
        return $query->where('is_cutoff', true);
    }

    public function scopeDraft($query)
    {
        return $query->where('is_cutoff', false);
    }

    // Accessors
    public function getAchievementRateAttribute()
    {
        $target = $this->okr?->realisasi ?? 100; // Default target 100%
        if ($target > 0) {
            return round(($this->nilai / $target) * 100, 1);
        }
        return 0;
    }

    public function getProgressScoreAttribute()
    {
        $okrBobot = $this->okr?->bobot ?? 0;
        if ($okrBobot > 0) {
            return round(($this->nilai * $okrBobot) / 100, 1);
        }
        return $this->nilai;
    }
}
