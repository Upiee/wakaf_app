<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TindakLanjut extends Model
{
    use HasFactory;

    protected $table = 'tindak_lanjut';

    protected $fillable = [
        'kode_tindak_lanjut',
        'laporan_evaluasi_id',
        'user_id',
        'jenis_tindakan',
        'deskripsi_tindakan',
        'target_perbaikan',
        'timeline_mulai',
        'timeline_selesai',
        'pic_responsible',
        'status_pelaksanaan',
        'progress_percentage',
        'catatan_progress',
        'hasil_evaluasi',
        'dibuat_oleh',
        'disetujui_oleh',
        'tanggal_persetujuan'
    ];

    protected $casts = [
        'timeline_mulai' => 'date',
        'timeline_selesai' => 'date',
        'tanggal_persetujuan' => 'datetime',
        'progress_percentage' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Auto-generate kode jika belum ada
            if (empty($model->kode_tindak_lanjut)) {
                $model->kode_tindak_lanjut = self::generateAutoId();
            }

            // Auto-fill user_id dari laporan evaluasi jika belum ada
            if (empty($model->user_id) && !empty($model->laporan_evaluasi_id)) {
                $laporan = LaporanEvaluasi::find($model->laporan_evaluasi_id);
                if ($laporan) {
                    $model->user_id = $laporan->user_id;
                }
            }
        });
    }

    public function laporanEvaluasi(): BelongsTo
    {
        return $this->belongsTo(LaporanEvaluasi::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function picResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pic_responsible');
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    /**
     * Generate auto ID untuk tindak lanjut
     */
    public static function generateAutoId(): string
    {
        $currentYear = date('Y');
        $currentMonth = date('m');
        $prefix = "TL-{$currentYear}{$currentMonth}-";
        
        $lastRecord = static::where('kode_tindak_lanjut', 'like', $prefix . '%')
            ->orderBy('kode_tindak_lanjut', 'desc')
            ->first();
        
        if ($lastRecord) {
            $lastNumber = (int) substr($lastRecord->kode_tindak_lanjut, -3);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Daftar jenis tindakan sederhana
     */
    public static function getJenisTindakan(): array
    {
        return [
            'pelatihan' => 'Pelatihan',
            'coaching' => 'Coaching',
            'development_plan' => 'Development Plan',
            'peringatan' => 'Performance Warning',
            'promosi' => 'Promosi',
        ];
    }

    /**
     * Daftar status pelaksanaan sederhana
     */
    public static function getStatusPelaksanaan(): array
    {
        return [
            'planned' => 'Direncanakan',
            'in_progress' => 'Sedang Berlangsung',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
        ];
    }

    /**
     * Get status color for UI
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status_pelaksanaan ?? 'planned') {
            'planned' => 'warning',
            'in_progress' => 'info',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'primary'
        };
    }

    /**
     * Check if action is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status_pelaksanaan === 'completed') {
            return false;
        }
        
        return $this->timeline_selesai < now();
    }
}
