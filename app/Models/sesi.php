<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sesi extends Model // Saya ubah S-nya jadi besar supaya standar PSR-4 Laravel
{
    use HasFactory;

    // Tetap mengarah ke tabel 'Sesi' sesuai database kamu
    protected $table = 'Sesi'; 

    protected $fillable = [
        'jadwal_id', 
        'tanggal', 
        'token_qr'
    ];

    // Relasi ke Jadwal
    public function jadwal() 
    { 
        return $this->belongsTo(Jadwal::class, 'jadwal_id'); 
    }

    // Relasi ke Absensi
    public function absensi() 
    { 
        return $this->hasMany(Absensi::class, 'sesi_id'); 
    }
}