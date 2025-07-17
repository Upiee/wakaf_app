<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Divisi extends Model
{
    protected $table = 'divisis';

    protected $fillable = [
        'nama',
    ];

    /**
     * Get the employees for the divisi.
     */
    public function employees()
    {
        return $this->hasMany(User::class, 'divisi_id');
    }

    /**
     * Alias for employees (untuk kompatibilitas dengan KPI/OKR resources)
     */
    public function users()
    {
        return $this->employees();
    }

    /**
     * Get the managers for the divisi.
     */
    public function managers()
    {
        return $this->hasMany(User::class, 'divisi_id')->where('role', 'manager');
    }

    /**
     * Get the KPI/OKR for the divisi.
     */
    public function kpis()
    {
        return $this->hasMany(KelolaKPI::class, 'divisi_id');
    }

    public function okrs()
    {
        return $this->hasMany(KelolaOKR::class, 'divisi_id');
    }
}
