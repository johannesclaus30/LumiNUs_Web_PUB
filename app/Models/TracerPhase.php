<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerPhase extends Model
{
    protected $table = 'tracer_phases';

    protected $fillable = [
        'form_id',
        'title',
        'subtitle',
        'icon',
        'color',
        'order_priority',
        'target_alumni_type',
        'is_archived', 
    ];

    protected $casts = [
        'is_archived' => 'boolean',   // ← new
    ];

    // ═══════════════════════════════════════
    // CONSTANTS
    // ═══════════════════════════════════════

    const TARGET_ALL = 'all';
    const TARGET_COLLEGE = 'college';
    const TARGET_SHS = 'shs';

    /**
     * Get all target types for dropdown/UI
     */
    public static function getTargetTypes(): array
    {
        return [
            self::TARGET_ALL => 'All Alumni',
            self::TARGET_COLLEGE => 'College Only',
            self::TARGET_SHS => 'SHS Only',
        ];
    }

    /**
     * Get target type label with icon
     */
    public static function getTargetTypeInfo(string $type): array
    {
        $info = [
            'all' => ['label' => 'All Alumni', 'icon' => 'fa-users', 'class' => 'all'],
            'college' => ['label' => 'College Only', 'icon' => 'fa-graduation-cap', 'class' => 'college'],
            'shs' => ['label' => 'SHS Only', 'icon' => 'fa-school', 'class' => 'shs'],
        ];
        
        return $info[$type] ?? $info['all'];
    }

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function form(): BelongsTo
    {
        return $this->belongsTo(TracerForm::class, 'form_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(TracerSection::class, 'phase_id')->orderBy('order_priority');
    }

    // ═══════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════

    /**
     * Scope to get phases visible to a specific alumni type
     */
    public function scopeForAlumniType($query, string $alumniType)
    {
        return $query->where(function ($q) use ($alumniType) {
            $q->where('target_alumni_type', 'all')
              ->orWhere('target_alumni_type', $alumniType);
        });
    }

    /**
     * Scope to get only phases for a specific target type
     */
    public function scopeTargetedFor($query, string $targetType)
    {
        return $query->where('target_alumni_type', $targetType);
    }
}