<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Divisi;

class RealisasiDivisi extends Model
{
    protected $table = 'realisasi_divisi';

    protected $fillable = [
        'divisi_id',
        'kpi_id',
        'okr_id',
        'nilai',
        'periode',
        'keterangan',
        'is_cutoff',
    ];

    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id', 'id');
    }

    public function kpi()
    {
        return $this->belongsTo(KelolaKPI::class, 'kpi_id', 'id');
    }

    public function okr()
    {
        return $this->belongsTo(KelolaOKR::class, 'okr_id', 'id');
    }
}
