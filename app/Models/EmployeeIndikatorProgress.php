<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeIndikatorProgress extends Model
{
    protected $table = 'employee_indikator_progress';

    protected $fillable = [
        'kpi_okr_id',
        'kode',
        'indikator',
        'bobot',
        'progress',
        'periode',
        'lampiran',
        'realisasi',
        'dokumen',
    ];

    public function kelolaKpiOkr()
    {
        return $this->belongsTo(KelolaKpiOkr::class, 'kpi_okr_id', 'id');
    }
}
