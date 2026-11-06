<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TracerForm extends Model
{
    protected $table = 'tracer_forms';

    protected $fillable = [
        'admin_id',
        'form_title',
        'form_description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    public const STATUS_DELETED = 0;
    public const STATUS_ACTIVE = 1;
    public const STATUS_DRAFT = 2;
    public const STATUS_CLOSED = 3;

    // ═══════════════════════════════════════
    // RELATIONSHIPS
    // ═══════════════════════════════════════

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class);
    }

    public function phases(): HasMany
    {
        return $this->hasMany(TracerPhase::class, 'form_id')->orderBy('order_priority');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(TracerResponse::class, 'form_id');
    }

    // ═══════════════════════════════════════
    // SCOPES
    // ═══════════════════════════════════════

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeNotDeleted($query)
    {
        return $query->where('status', '!=', self::STATUS_DELETED);
    }

    public function scopeDeleted($query)
    {
        return $query->where('status', self::STATUS_DELETED);
    }

    // ═══════════════════════════════════════
    // HELPER METHODS
    // ═══════════════════════════════════════

    public function isDeleted(): bool
    {
        return $this->status === self::STATUS_DELETED;
    }

    public function isActiveStatus(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function markAsDeleted()
    {
        $this->status = self::STATUS_DELETED;
        $this->save();
    }

    public function markAsActive()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    public function markAsDraft()
    {
        $this->status = self::STATUS_DRAFT;
        $this->save();
    }

    public function markAsClosed()
    {
        $this->status = self::STATUS_CLOSED;
        $this->save();
    }

    public function restoreFromDeleted()
    {
        $this->status = self::STATUS_ACTIVE;
        $this->save();
    }

    /**
     * Get a flat collection of all questions in this form.
     */
    public function allQuestions()
    {
        return TracerQuestion::whereIn('section_id', function ($query) {
            $query->select('id')
                ->from('tracer_sections')
                ->whereIn('phase_id', function ($subQuery) {
                    $subQuery->select('id')
                        ->from('tracer_phases')
                        ->where('form_id', $this->id);
                });
        })->orderBy('order_priority')->get();
    }

    /**
     * Count total questions in this form.
     */
    public function totalQuestionsCount(): int
    {
        return TracerQuestion::whereIn('section_id', function ($query) {
            $query->select('id')
                ->from('tracer_sections')
                ->whereIn('phase_id', function ($subQuery) {
                    $subQuery->select('id')
                        ->from('tracer_phases')
                        ->where('form_id', $this->id);
                });
        })->count();
    }
}