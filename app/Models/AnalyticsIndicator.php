<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnalyticsIndicator extends Model
{
    protected $table = 'analytics_indicators';
    protected $guarded = [];

    protected $casts = [
        'allowed_values' => 'array',
        'is_active'      => 'boolean',
    ];
}