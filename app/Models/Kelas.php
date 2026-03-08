<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'tingkat',
        'jurusan',
        'nomor_kelas',
    ];

    public function siswa()
    {
        return $this->hasMany(Siswa::class);
    }

    public function scopeWithActiveTahun($query)
    {
        // Ini akan membantu kamu mengambil tahun ajaran yang is_active = 1
        return TahunAjaran::where('is_active', true)->first();
    }
}
