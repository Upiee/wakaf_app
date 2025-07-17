<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class KelolaKpiOkr extends Model
{
    use HasFactory;

    protected $table = 'kpi_okrs'; // gunakan tabel yang benar

    protected $fillable = [
        'title',
        'description', 
        'type',
        'user_id'
    ];

    protected $keyType = 'int'; // karena primary key adalah integer
    public $incrementing = true;   // karena auto increment

    /**
     * Relasi ke user (pemilik KPI/OKR)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function indikatorProgress()
    {
        return $this->hasMany(EmployeeIndikatorProgress::class, 'kpi_okr_id');
    }
}
