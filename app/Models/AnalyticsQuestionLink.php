<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsQuestionLink extends Model
{
    protected $table = 'analytics_question_links';
    protected $guarded = [];

    public function question()
    {
        return $this->belongsTo(TracerQuestion::class, 'question_id');
    }

    public function indicator()
    {
        return $this->belongsTo(AnalyticsIndicator::class, 'indicator_key', 'indicator_key');
    }
}