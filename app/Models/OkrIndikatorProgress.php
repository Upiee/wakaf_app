<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class OkrIndikatorProgress extends Model
{
    protected $table = 'okr_indikator_progress';
    protected $primaryKey = 'id';

    protected $fillable = [
        'okr_id',
        'kelola_okr_id', // alias untuk okr_id
        'kode',
        'indikator',
        'bobot',
        'progress',
        'periode',
        'lampiran',
        'realisasi',
        'tanggal_realisasi',
        'keterangan',
        'dokumen',
        'user_id',
        'status_approval',
        'manager_notes',
    ];

    public function okr()
    {
        return $this->belongsTo(KelolaOKR::class, 'okr_id', 'id');
    }

    public function kelolaOkr()
    {
        return $this->okr(); // alias
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
