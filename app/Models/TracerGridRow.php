<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerGridRow extends Model
{
    protected $table = 'tracer_grid_rows';

    protected $fillable = [
        'question_id',
        'row_label',
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