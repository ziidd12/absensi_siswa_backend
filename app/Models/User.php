<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

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
        'device_id',
        'tahun_ajaran_id',
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
        ];
    }
    
    public function siswa()
    {
        return $this->hasOne(Siswa::class);
    }

    public function guru()
    {
        // User memiliki satu profil Guru
        return $this->hasOne(Guru::class, 'user_id');
    }

    /**
     * Relasi ke penilaian yang diterima (sebagai siswa)
     * Melalui tabel siswa
     */
    public function assessmentsReceived()
    {
        return $this->hasManyThrough(
            Assessment::class,      // Model tujuan
            Siswa::class,           // Model perantara
            'user_id',              // Foreign key di tabel siswa (user_id)
            'siswa_id',             // Foreign key di tabel assessments (siswa_id)
            'id',                   // Local key di tabel users
            'id'                    // Local key di tabel siswa
        );
    }

    /**
     * Relasi ke penilaian yang dibuat (sebagai evaluator/guru)
     */
    public function assessmentsGiven()
    {
        return $this->hasMany(Assessment::class, 'evaluator_id');
    }
}
