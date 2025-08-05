<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LaporanEvaluasi extends Model
{
    use HasFactory;

    protected $table = 'laporan_evaluasi';

    protected $fillable = [
        'kode_laporan',
        'user_id',
        'divisi_id',
        'periode_mulai',
        'periode_selesai',
        'periode_quartal',
        'tipe_laporan',
        'data_laporan',
        'kpi_references',
        'okr_references',
        'total_kpi',
        'total_okr',
        'pencapaian_kpi',
        'pencapaian_okr',
        'rata_rata_score',
        'catatan_evaluasi',
        'dibuat_oleh',
    ];

    protected $attributes = [
        'total_kpi' => 0,
        'total_okr' => 0,
        'pencapaian_kpi' => 0.00,
        'pencapaian_okr' => 0.00,
        'rata_rata_score' => 0.00,
        'data_laporan' => '{}',
    ];

    protected $casts = [
        'periode_mulai' => 'date',
        'periode_selesai' => 'date',
        'data_laporan' => 'array',
        'kpi_references' => 'array',
        'okr_references' => 'array',
        'pencapaian_kpi' => 'float',
        'pencapaian_okr' => 'float',
        'rata_rata_score' => 'float',
    ];

    protected $appends = [
        'kpi_reference_array',
        'okr_reference_array',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->kode_laporan)) {
                $model->kode_laporan = self::generateKodeLaporan($model);
            }

            // Auto-generate rekomendasi berdasarkan rata_rata_score
            if (empty($model->rekomendasi) && $model->rata_rata_score > 0) {
                $model->rekomendasi = self::generateAutoRekomendasi($model->rata_rata_score);
            }

            // Auto-generate status_kinerja berdasarkan rata_rata_score
            if (empty($model->status_kinerja) && $model->rata_rata_score > 0) {
                $model->status_kinerja = self::generateStatusKinerja($model->rata_rata_score);
            }
        });
    }

    /**
     * Generate kode laporan dengan pattern: LAP-{DIV}-{YYYYMM}-{SEQUENCE}
     * Terintegrasi dengan sistem kodifikasi KPI/OKR yang sudah ada
     * Contoh: LAP-IT001-202501-001, LAP-ALL-202501-001 (untuk perusahaan)
     */
    public static function generateKodeLaporan($model): string
    {
        $prefix = 'LAP';
        $yearMonth = now()->format('Ym'); // 202501
        
        // Tentukan kode divisi berdasarkan tipe laporan - konsisten dengan KPI/OKR
        if ($model->tipe_laporan === 'perusahaan') {
            $divisiCode = 'ALL';
        } elseif ($model->divisi_id) {
            $divisi = Divisi::find($model->divisi_id);
            // Menggunakan format yang sama dengan KPI: DIV.KPI-0001
            $divisiCode = $divisi && isset($divisi->kode) ? $divisi->kode : 'UNK';
        } else {
            $divisiCode = 'IND'; // Individual
        }

        // Hitung sequence berdasarkan bulan dan divisi yang sama
        $sequence = self::where('kode_laporan', 'LIKE', "{$prefix}-{$divisiCode}-{$yearMonth}-%")
            ->count() + 1;

        return sprintf(
            '%s-%s-%s-%03d',
            $prefix,
            $divisiCode,
            $yearMonth,
            $sequence
        );
    }

    /**
     * Generate rekomendasi otomatis berdasarkan score
     */
    public static function generateAutoRekomendasi(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Kinerja excellent. Pertahankan dan jadikan role model. Pertimbangkan promosi atau peningkatan tanggung jawab.',
            $score >= 80 => 'Kinerja baik. Identifikasi area improvement dan berikan development program lanjutan.',
            $score >= 70 => 'Kinerja cukup. Perlu coaching dan monitoring intensif. Buat action plan improvement.',
            $score >= 60 => 'Kinerja di bawah standar. Diperlukan training khusus dan performance improvement plan (PIP).',
            default => 'Kinerja sangat di bawah standar. Perlu evaluasi menyeluruh dan tindakan korektif.'
        };
    }

    /**
     * Generate status kinerja berdasarkan score
     */
    public static function generateStatusKinerja(float $score): string
    {
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Good',
            $score >= 70 => 'Average',
            $score >= 60 => 'Below Average',
            default => 'Poor'
        };
    }

    /**
     * Populate KPI dan OKR references dengan kode terintegrasi
     * Menghubungkan dengan sistem kodifikasi yang sudah ada
     */
    public function populateReferences(): void
    {
        $kpiRefs = [];
        $okrRefs = [];

        if ($this->tipe_laporan === 'individual' && $this->user_id) {
            // Ambil KPI individual dengan kode
            $kpis = RealisasiKpi::where('user_id', $this->user_id)
                ->whereBetween('created_at', [$this->periode_mulai, $this->periode_selesai])
                ->with('kpi')
                ->get();

            foreach ($kpis as $realisasi) {
                if ($realisasi->kpi) {
                    $kpiRefs[] = [
                        'kpi_id' => $realisasi->kpi->id,
                        'kode' => $realisasi->kpi->code_id, // Menggunakan kode yang sudah ada: DIV.KPI-0001
                        'activity' => $realisasi->kpi->activity,
                        'nilai' => $realisasi->nilai,
                        'periode' => $realisasi->periode
                    ];
                }
            }

            // Ambil OKR individual dengan kode
            $okrs = RealisasiOkr::where('user_id', $this->user_id)
                ->whereBetween('created_at', [$this->periode_mulai, $this->periode_selesai])
                ->with('okr')
                ->get();

            foreach ($okrs as $realisasi) {
                if ($realisasi->okr) {
                    $okrRefs[] = [
                        'okr_id' => $realisasi->okr->id,
                        'kode' => $realisasi->okr->code_id, // Menggunakan kode yang sudah ada: DIV.OKR-0001
                        'activity' => $realisasi->okr->activity,
                        'nilai' => $realisasi->nilai,
                        'periode' => $realisasi->periode
                    ];
                }
            }
        }

        $this->update([
            'kpi_references' => $kpiRefs,
            'okr_references' => $okrRefs,
            'total_kpi' => count($kpiRefs),
            'total_okr' => count($okrRefs),
        ]);
    }

    public function getKpiReferenceArrayAttribute()
    {   
        $value = $this->getAttribute('kpi_references');
        // if string then decode
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    public function getOkrReferenceArrayAttribute()
    {
        $value = $this->getAttribute('okr_references');
        // if string then decode
        if (is_string($value)) {
            return json_decode($value, true);
        }
        return $value;
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class);
    }

    public function dibuatOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function disetujuiOleh(): BelongsTo
    {
        return $this->belongsTo(User::class, 'disetujui_oleh');
    }

    public function tindakLanjuts()
    {
        return $this->hasMany(TindakLanjut::class, 'laporan_evaluasi_id');
    }

    // Accessors yang ada
    public function getKinerjaScoreAttribute()
    {
        return $this->rata_rata_score ?? 0;
    }

    public function getStatusKinerjaAttribute()
    {
        $score = $this->rata_rata_score ?? 0;
        return match (true) {
            $score >= 90 => 'Excellent',
            $score >= 80 => 'Good',
            $score >= 70 => 'Average',
            $score >= 60 => 'Below Average',
            default => 'Poor'
        };
    }

    public function getRekomendasiAttribute()
    {
        $score = $this->rata_rata_score ?? 0;
        return match (true) {
            $score >= 90 => 'Kinerja excellent. Pertahankan dan jadikan role model.',
            $score >= 80 => 'Kinerja baik. Identifikasi area improvement.',
            $score >= 70 => 'Kinerja cukup. Perlu coaching dan monitoring.',
            $score >= 60 => 'Kinerja di bawah standar. Diperlukan training khusus.',
            default => 'Kinerja sangat di bawah standar. Perlu evaluasi menyeluruh.'
        };
    }

    // Scopes
    public function scopeInPeriode($query, $start, $end)
    {
        return $query->whereBetween('periode_mulai', [$start, $end])
            ->orWhereBetween('periode_selesai', [$start, $end]);
    }

    public function scopeByTipe($query, $tipe)
    {
        return $query->where('tipe_laporan', $tipe);
    }

    /**
     * Auto populate data dari realisasi KPI/OKR
     */
    public function populateDataFromRealisasi()
    {
        if ($this->tipe_laporan === 'individual' && $this->user_id) {
            $this->populateIndividualData();
        } elseif ($this->tipe_laporan === 'divisi' && $this->divisi_id) {
            $this->populateDivisiData();
        }
        
        // Save() sudah dipanggil di dalam method populate
    }

    /**
     * Populate data untuk laporan individual
     */
    private function populateIndividualData()
    {
        // Ambil realisasi KPI berdasarkan user dan quartal
        $realisasiKpi = RealisasiKpi::where('user_id', $this->user_id)
            ->where('periode', $this->periode_quartal ?? 'Q3-2025')
            ->with('kpi')
            ->get();

        // Ambil realisasi OKR berdasarkan user dan quartal
        $realisasiOkr = RealisasiOkr::where('user_id', $this->user_id)
            ->where('periode', $this->periode_quartal ?? 'Q3-2025')
            ->with('okr')
            ->get();

        // Hitung data KPI
        $kpiData = $this->calculateKpiData($realisasiKpi);
        $okrData = $this->calculateOkrData($realisasiOkr);

        // Update data using update method to handle decimal casting
        $avgScore = ($kpiData['rata_rata'] + $okrData['rata_rata']) / 2;
        
        $this->update([
            'total_kpi' => $kpiData['total'],
            'total_okr' => $okrData['total'],
            'pencapaian_kpi' => $kpiData['rata_rata'],
            'pencapaian_okr' => $okrData['rata_rata'],
            'rata_rata_score' => $avgScore,
            'kpi_references' => $kpiData['references'],
            'okr_references' => $okrData['references'],
        ]);
    }

    /**
     * Populate data untuk laporan divisi
     */
    private function populateDivisiData()
    {
        // Ambil semua user di divisi
        $userIds = User::where('divisi_id', $this->divisi_id)->pluck('id');

        // Ambil realisasi KPI untuk semua user di divisi berdasarkan quartal
        $realisasiKpi = RealisasiKpi::whereIn('user_id', $userIds)
            ->where('periode', $this->periode_quartal ?? 'Q3-2025')
            ->with('kpi')
            ->get();

        // Ambil realisasi OKR untuk semua user di divisi berdasarkan quartal
        $realisasiOkr = RealisasiOkr::whereIn('user_id', $userIds)
            ->where('periode', $this->periode_quartal ?? 'Q3-2025')
            ->with('okr')
            ->get();

        // Hitung data
        $kpiData = $this->calculateKpiData($realisasiKpi);
        $okrData = $this->calculateOkrData($realisasiOkr);

        // Update data using update method to handle decimal casting
        $avgScore = ($kpiData['rata_rata'] + $okrData['rata_rata']) / 2;
        
        $this->update([
            'total_kpi' => $kpiData['total'],
            'total_okr' => $okrData['total'],
            'pencapaian_kpi' => $kpiData['rata_rata'],
            'pencapaian_okr' => $okrData['rata_rata'],
            'rata_rata_score' => $avgScore,
            'kpi_references' => $kpiData['references'],
            'okr_references' => $okrData['references'],
        ]);
    }

    /**
     * Hitung data KPI
     */
    private function calculateKpiData($realisasiKpi)
    {
        if ($realisasiKpi->isEmpty()) {
            return [
                'total' => 0,
                'rata_rata' => 0,
                'references' => []
            ];
        }

        $totalScore = 0;
        $references = [];

        foreach ($realisasiKpi as $realisasi) {
            // Gunakan nilai langsung dari realisasi
            $score = (float) $realisasi->nilai ?? 0;
            $totalScore += $score;
            
            $references[] = [
                'kpi_id' => $realisasi->kpi_id,
                'kode' => $realisasi->kpi->code_id ?? 'N/A',
                'activity' => $realisasi->kpi->activity ?? 'N/A',
                'target' => $realisasi->kpi->timeline_realisasi ?? $realisasi->kpi->output ?? 100,
                'nilai' => $realisasi->nilai,
                'periode' => $realisasi->periode,
                'skor' => $score
            ];
        }

        return [
            'total' => $realisasiKpi->count(),
            'rata_rata' => $totalScore / $realisasiKpi->count(),
            'references' => $references
        ];
    }

    /**
     * Hitung data OKR
     */
    private function calculateOkrData($realisasiOkr)
    {
        if ($realisasiOkr->isEmpty()) {
            return [
                'total' => 0,
                'rata_rata' => 0,
                'references' => []
            ];
        }

        $totalScore = 0;
        $references = [];

        foreach ($realisasiOkr as $realisasi) {
            // Gunakan nilai langsung dari realisasi
            $score = (float) $realisasi->nilai ?? 0;
            $totalScore += $score;
            
            $references[] = [
                'okr_id' => $realisasi->okr_id,
                'kode' => $realisasi->okr->code_id ?? 'N/A',
                'judul' => $realisasi->okr->activity ?? 'N/A',
                'activity' => $realisasi->okr->activity ?? 'N/A',
                'target' => $realisasi->okr->timeline_realisasi ?? $realisasi->okr->output ?? 100,
                'nilai' => $realisasi->nilai,
                'periode' => $realisasi->periode,
                'skor' => $score
            ];
        }

        return [
            'total' => $realisasiOkr->count(),
            'rata_rata' => $totalScore / $realisasiOkr->count(),
            'references' => $references
        ];
    }

    /**
     * Generate data untuk export dengan kode KPI/OKR terintegrasi
     */
    public function toExportArray(): array
    {
        $kpiCodes = [];
        $okrCodes = [];

        if ($this->kpi_references) {
            $kpiCodes = collect($this->kpi_references)->pluck('kode')->toArray();
        }

        if ($this->okr_references) {
            $okrCodes = collect($this->okr_references)->pluck('kode')->toArray();
        }

        return [
            'kode_laporan' => $this->getAttribute('kode_laporan'),
            'karyawan' => $this->user ? $this->user->name : 'N/A',
            'divisi' => $this->divisi ? $this->divisi->nama : 'N/A',
            'periode' => $this->getAttribute('periode_mulai')?->format('d/m/Y') . ' - ' . $this->getAttribute('periode_selesai')?->format('d/m/Y'),
            'tipe_laporan' => ucfirst($this->getAttribute('tipe_laporan')),
            'kpi_terkait' => implode(', ', $kpiCodes),
            'okr_terkait' => implode(', ', $okrCodes),
            'total_kpi' => $this->getAttribute('total_kpi'),
            'total_okr' => $this->getAttribute('total_okr'),
            'pencapaian_kpi' => $this->getAttribute('pencapaian_kpi') . '%',
            'pencapaian_okr' => $this->getAttribute('pencapaian_okr') . '%',
            'rata_rata_score' => $this->getAttribute('rata_rata_score'),
            'status_kinerja' => $this->getAttribute('status_kinerja'),
            'rekomendasi' => $this->getAttribute('rekomendasi'),
            'dibuat_oleh' => $this->dibuatOleh ? $this->dibuatOleh->name : 'N/A',
            'status_laporan' => ucfirst($this->getAttribute('status_laporan')),
        ];
    }
}