<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerQuestionOption extends Model
{
    protected $table = 'tracer_question_options';

    protected $fillable = [
        'question_id',
        'option_label',
        'order_priority',
        'is_other', 
    ];

    protected $casts = [
        'is_other' => 'boolean',   // 🔥 THE FIX
    ];

    public $timestamps = false;

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function question(): BelongsTo
    {
        return $this->belongsTo(TracerQuestion::class, 'question_id');
    }
}