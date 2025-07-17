<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SetKpiOkr extends Model
{
    protected $fillable = [
        'name',
        'type',
        'start_date', 
        'end_date',
        'cutoff_date',
        'is_active',
        'description'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date', 
        'cutoff_date' => 'date',
        'is_active' => 'boolean'
    ];
}
