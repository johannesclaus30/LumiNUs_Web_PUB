<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerQuestion extends Model
{
    protected $table = 'tracer_questions';

    protected $fillable = [
        'section_id',
        'type',
        'question_text',
        'description',
        'placeholder',
        'is_required',
        'order_priority',
        'file_types',
        'max_file_size',
        'allow_other_option',
    ];

    protected $casts = [
        'is_required'        => 'boolean',
        'allow_other_option' => 'boolean',
        'file_types'         => 'array',
        'max_file_size'      => 'integer',
    ];

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function section(): BelongsTo
    {
        return $this->belongsTo(TracerSection::class, 'section_id');
    }

    public function options(): HasMany
    {
        return $this->hasMany(TracerQuestionOption::class, 'question_id')->orderBy('order_priority');
    }

    public function gridRows(): HasMany
    {
        return $this->hasMany(TracerGridRow::class, 'question_id')->orderBy('order_priority');
    }

    public function gridColumns(): HasMany
    {
        return $this->hasMany(TracerGridColumn::class, 'question_id')->orderBy('order_priority');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TracerAnswer::class, 'question_id');
    }
}