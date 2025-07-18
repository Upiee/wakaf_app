<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealisasiIndividu extends Model
{
    protected $table = 'realisasi_individu';

    protected $fillable = [
        'user_id',
        'kpi_id',
        'okr_id',
        'nilai',
        'periode',
        'is_cutoff',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke KPI (jika id di kelola__k_p_i_s juga string)
    public function kpi()
    {
        return $this->belongsTo(KelolaKPI::class, 'kpi_id', 'id');
    }

    // Relasi ke OKR (jika id di kelola__o_k_r_s juga string)
    public function okr()
    {
        return $this->belongsTo(KelolaOKR::class, 'okr_id', 'id');
    }
}
