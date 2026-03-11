<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentRating extends Model
{
    use HasFactory;

    // Nama tabel di database kamu (sesuaikan jika namanya berbeda di HeidiSQL)
    protected $table = 'student_ratings'; 

    protected $fillable = [
        'siswa_id', 
        'guru_id',
        'kedisiplinan', 
        'kerja_sama', 
        'tanggung_jawab', 
        'inisiatif',
        'catatan'
    ];
}