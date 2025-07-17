<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceAppraisalScore extends Model
{
    protected $fillable = [
        'performance_appraisal_id',
        'user_id',
        'score',
        'catatan',
    ];

    public function appraisal()
    {
        return $this->belongsTo(PerformanceAppraisal::class, 'performance_appraisal_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
