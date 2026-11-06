<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerGridColumn extends Model
{
    protected $table = 'tracer_grid_columns';

    protected $fillable = [
        'question_id',
        'column_label',
        'order_priority',
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