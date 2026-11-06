<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsAlignmentRule extends Model
{
    protected $table = 'analytics_alignment_rules';
    protected $guarded = [];

    protected $casts = [
        'program_option_ids' => 'array',
        'career_option_ids'  => 'array',
        'is_active'          => 'boolean',
    ];

    public function programQuestion()
    {
        return $this->belongsTo(TracerQuestion::class, 'program_question_id');
    }

    public function careerQuestion()
    {
        return $this->belongsTo(TracerQuestion::class, 'career_question_id');
    }
}