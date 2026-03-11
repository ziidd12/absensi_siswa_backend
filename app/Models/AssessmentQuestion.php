<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentQuestion extends Model
{
    use HasFactory;

    protected $table = 'assessment_questions';

    protected $fillable = [
        'category_id', 
        'question_text'
    ];

    public function category()
    {
        return $this->belongsTo(AssessmentCategory::class, 'category_id');
    }

    public function details()
    {
        return $this->hasMany(AssessmentDetail::class, 'question_id');
    }
}
