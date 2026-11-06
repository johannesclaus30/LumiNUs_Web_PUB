<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerAnswer extends Model
{
    protected $table = 'tracer_answers_base';

    protected $fillable = [
        'tracer_response_id',
        'question_id',
        'answer_value',
        'file_path',
        'file_name',
        'grid_row_id',
    ];

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function response(): BelongsTo
    {
        return $this->belongsTo(TracerResponse::class, 'tracer_response_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(TracerQuestion::class, 'question_id');
    }

    public function gridRow(): BelongsTo
    {
        return $this->belongsTo(TracerGridRow::class, 'grid_row_id');
    }

    public function selections(): HasMany
    {
        return $this->hasMany(TracerAnswerSelection::class, 'tracer_answer_id');
    }
}