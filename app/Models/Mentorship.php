<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mentorship extends Model
{
    protected $table = 'mentorships';
    
    // ⬇️ ADD THESE TWO LINES ⬇️
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'alumni_id',
        'status',
        'event_type',
        'event_title',
        'target_audience',
        'proposed_date',
        'description',
        'event_format',
        'start_time',
        'end_time',
        'expected_capacity',
        'co_speakers',
        'attached_file_url',
        'is_draft'
    ];

    protected $casts = [
        'is_draft' => 'boolean'
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class);
    }
}