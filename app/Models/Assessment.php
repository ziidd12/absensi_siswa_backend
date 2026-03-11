<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'evaluator_id', 
        'evaluatee_id', 
        'tahun_ajaran_id', 
        'assessment_date',
        'general_notes'
    ];

    // Relasi ke Guru/User yang menilai
    public function evaluator()
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    // Relasi ke Siswa yang dinilai
    public function siswa()
    {
        return $this->belongsTo(User::class, 'evaluatee_id');
    }

    // Relasi ke rincian nilai-nilainya
    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'assessment_id');
    }
}