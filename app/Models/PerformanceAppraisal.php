<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceAppraisal extends Model
{
    protected $fillable = [
        'periode',
        'mulai_penilaian',
        'selesai_penilaian',
        'status',
        'bobot',
    ];
}
