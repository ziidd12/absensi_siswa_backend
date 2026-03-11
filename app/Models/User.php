<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'device_id',
        'tahun_ajaran_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
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
        ];
    }
    
    /**
     * Relasi ke profil Siswa
     */
    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id');
    }

    /**
     * Relasi ke profil Guru
     */
    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id');
    }

    

    /**
     * Relasi ke penilaian yang diterima (Sebagai Siswa)
     * Mengambil data dari tabel assessments lewat perantara tabel siswa
     */
    public function assessmentsReceived()
    {
        // Karena evaluatee_id di tabel assessments merujuk ke id di tabel users
        return $this->hasMany(Assessment::class, 'evaluatee_id', 'id');
    }

    /**
     * Relasi ke penilaian yang diberikan (Sebagai Guru/Evaluator)
     * Langsung ke tabel assessments karena guru adalah User
     */
    public function assessmentsGiven()
    {
        return $this->hasMany(Assessment::class, 'evaluator_id');
    }

    protected $appends = ['nis'];

    public function getNisAttribute()
    {
        // Mengambil NIS dari relasi siswa jika ada
        return $this->siswa ? $this->siswa->nis : '-';
    }
}