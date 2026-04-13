<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'siswa_id',
        'item_id',
        'status',
        'used_at_attendance_id',
        'used_at',
    ];

    // Casting field tanggal agar otomatis jadi objek Carbon
    protected $casts = [
        'used_at' => 'datetime',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    // Relasi ke item toko (asumsi nama modelnya StoreItem)
    public function item()
    {
        return $this->belongsTo(StoreItem::class, 'item_id');
    }

    // Relasi ke absensi (jika token digunakan saat absen tertentu)
    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'used_at_attendance_id');
    }
}