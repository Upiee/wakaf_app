<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'divisi_id',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the divisi that the user belongs to.
     */
    public function divisi()
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Get the KPI realizations for the user.
     */
    public function realisasiKpis()
    {
        return $this->hasMany(RealisasiKpi::class, 'user_id');
    }

    /**
     * Get the OKR realizations for the user.
     */
    public function realisasiOkrs()
    {
        return $this->hasMany(RealisasiOkr::class, 'user_id');
    }

    /**
     * Get the KPIs assigned to the user.
     */
    public function kelolaKpis()
    {
        return $this->hasMany(KelolaKPI::class, 'user_id');
    }

    /**
     * Get the OKRs assigned to the user.
     */
    public function kelolaOkrs()
    {
        return $this->hasMany(KelolaOKR::class, 'user_id');
    }
}

