<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentCategory extends Model
{
    use HasFactory;

    protected $table = 'assessment_categories';
    protected $fillable = [
        'name', 
        'description', 
        'type', 
        'is_active'
    ];

    public function questions()
    {
        // Pastikan nama model target (AssessmentQuestion) sudah benar
        return $this->hasMany(AssessmentQuestion::class, 'category_id'); 
    }
}