<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerResponse extends Model
{
    protected $table = 'tracer_responses';

    protected $fillable = [
        'alumni_id',
        'form_id',
        'status',
        'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(TracerForm::class, 'form_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(TracerAnswer::class, 'tracer_response_id');
    }
}