<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsOptionMap extends Model
{
    protected $table = 'analytics_option_maps';
    protected $guarded = [];

    public function option()
    {
        return $this->belongsTo(TracerQuestionOption::class, 'option_id');
    }

    public function indicator()
    {
        return $this->belongsTo(AnalyticsIndicator::class, 'indicator_key', 'indicator_key');
    }
}