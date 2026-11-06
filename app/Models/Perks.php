<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Perks extends Model
{
    // Add this constant
    const PH_TIMEZONE = 'Asia/Manila';

    protected $table = 'perks';

    protected $fillable = [
        'title',
        'description',
        'valid_until',
        'status',
        'admin_id',
        'is_manually_restored',
        'manually_restored_at',
    ];

    protected $casts = [
        'valid_until' => 'date',
        'status' => 'integer',
        'is_manually_restored' => 'boolean',
        'manually_restored_at' => 'datetime',
    ];

    protected $dates = [
        'valid_until',
    ];

    /**
     * Relationship: A perk was created by an admin.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Relationship: A perk can have many gallery images.
     * This links to your 'images_perks' table via the PerkImage model.
     */
    public function images(): HasMany
    {
        return $this->hasMany(PerkImage::class, 'perk_id');
    }

    // ========== SCOPES ==========

    /**
     * Scope to only include active perks (status = 1 or null).
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('status', 1)
              ->orWhereNull('status');
        });
    }

    /**
     * Scope to only include archived perks (status = 0).
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope to only include expired perks (valid_until < current Philippines date).
     */
    public function scopeExpired($query)
    {
        $now = Carbon::now(self::PH_TIMEZONE)->startOfDay();
        return $query->where('valid_until', '<', $now);
    }

    /**
     * Scope to only include valid perks (valid_until >= current Philippines date).
     */
    public function scopeValid($query)
    {
        $now = Carbon::now(self::PH_TIMEZONE)->startOfDay();
        return $query->where('valid_until', '>=', $now);
    }

    // ========== HELPER METHODS ==========

    /**
     * Check if the perk is expired.
     */
    public function isExpired()
    {
        $now = Carbon::now(self::PH_TIMEZONE)->startOfDay();
        return $this->valid_until < $now;
    }

    /**
     * Check if the perk is active.
     */
    public function isActive()
    {
        return ($this->status == 1 || is_null($this->status)) && !$this->isExpired();
    }

    /**
     * Check if the perk is archived.
     */
    public function isArchived()
    {
        return $this->status == 0;
    }

    /**
     * Get valid_until in Philippines timezone.
     */
    public function getValidUntilInTimezone($timezone = 'Asia/Manila')
    {
        return $this->valid_until->setTimezone($timezone);
    }

    /**
     * Scope: perks that should appear on the Active Perks admin list.
     *
     * Includes:
     *  - Regular active perks with valid_until >= today
     *  - Manually-restored perks (regardless of valid_until)
     */
    public function scopeVisibleActive($query)
    {
        $today = \Carbon\Carbon::now('Asia/Manila')->startOfDay();

        return $query->where(function ($q) {
            $q->where('status', 1)
            ->orWhereNull('status');
        })
        ->where(function ($q) use ($today) {
            $q->where('is_manually_restored', true)
            ->orWhere(function ($sub) use ($today) {
                $sub->where('is_manually_restored', false)
                    ->where('valid_until', '>=', $today);
            });
        });
    }

}