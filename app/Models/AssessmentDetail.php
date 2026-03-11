<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssessmentDetail extends Model
{
    use HasFactory;

    protected $table = 'assessment_details';

    protected $fillable = [
        'assessment_id', 
        'question_id', 
        'score'
    ];

    /**
     * Relasi ke model Question
     */
    public function question()
    {
        // Pastikan nama modelnya adalah Question
        return $this->belongsTo(AssessmentQuestion::class, 'question_id');
    }
    
    /**
     * Relasi ke header Assessment
     */
    public function assessment()
    {
        return $this->belongsTo(Assessment::class, 'assessment_id');
    }
}