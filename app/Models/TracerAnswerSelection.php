<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerAnswerSelection extends Model
{
    protected $table = 'tracer_answer_selections';

    protected $fillable = [
        'tracer_answer_id',
        'option_id',
        'grid_column_id',
    ];

    public $timestamps = false;

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function answer(): BelongsTo
    {
        return $this->belongsTo(TracerAnswer::class, 'tracer_answer_id');
    }

    public function option(): BelongsTo
    {
        return $this->belongsTo(TracerQuestionOption::class, 'option_id');
    }

    public function gridColumn(): BelongsTo
    {
        return $this->belongsTo(TracerGridColumn::class, 'grid_column_id');
    }
}