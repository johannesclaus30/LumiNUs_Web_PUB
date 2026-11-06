<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerSection extends Model
{
    protected $table = 'tracer_sections';

    protected $fillable = [
        'phase_id',
        'title',
        'description',
        'order_priority',
    ];

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function phase(): BelongsTo
    {
        return $this->belongsTo(TracerPhase::class, 'phase_id');
    }

    public function questions(): HasMany
    {
        return $this->hasMany(TracerQuestion::class, 'section_id')->orderBy('order_priority');
    }
}