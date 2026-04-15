<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nama_siswa',
        'NIS',           
        'id_kelas',
        'points_store', // <--- Ieu nu ditambahan meh kodingan redeem jalan
    ];

    public function kelas()
    {
        // Beritahu Laravel kalau foreign key-nya adalah id_kelas
        return $this->belongsTo(Kelas::class, 'id_kelas');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class);
    }

    public function ledgers()
    {
        return $this->hasMany(PointLedger::class);
    }

    public function tokens()
    {
        return $this->hasMany(UserToken::class);
    }
    public function redeems()
    {
    return $this->hasMany(Redeem::class, 'siswa_id');
    }
}