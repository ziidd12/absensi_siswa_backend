<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    use HasFactory;

    protected $table = 'assessment_categories';
    protected $fillable = ['name', 'description'];

    // Relasi ke detail penilaian
    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'category_id');
    }
}