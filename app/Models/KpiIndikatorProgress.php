<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class KpiIndikatorProgress extends Model
{
    protected $fillable = [
        'kpi_id',
        'kelola_kpi_id', // alias untuk kpi_id
        'kode',
        'indikator',
        'progress',
        'dokumen',
        'bobot',
        'periode',    
        'lampiran',   
        'realisasi',
        'tanggal_realisasi',
        'keterangan',
        'user_id',
    ];
    protected $table = 'kpi_indikator_progress';
    protected $primaryKey = 'id';  
    public function kpi()
    {
        return $this->belongsTo(KelolaKPI::class, 'kpi_id', 'id');
    }

    public function kelolaKpi()
    {
        return $this->kpi(); // alias
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

