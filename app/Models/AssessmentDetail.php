<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id', 
        'category_id', 
        'score'
    ];

    // Relasi balik ke kategori (Disiplin, dll)
    public function category()
    {
        return $this->belongsTo(AssessmentCategory::class, 'category_id');
    }
}