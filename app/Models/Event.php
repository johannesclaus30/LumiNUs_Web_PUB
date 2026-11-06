<?php

namespace App\Models;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon; 

class Event extends Model
{
    // Add this constant
    const PH_TIMEZONE = 'Asia/Manila';

    protected $table = 'events';

    protected $fillable = [
        'admin_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'max_capacity',
        'status',
        'event_type',
        'platform',
        'platform_url',
        'venue_id',
        'group_chat_id',
        'is_manually_restored',
        'manually_restored_at',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'max_capacity' => 'integer',
        'venue_id' => 'integer',
        'status' => 'integer',
        'is_manually_restored' => 'boolean',
        'manually_restored_at' => 'datetime',
    ];

    public function images()
    {
        return $this->hasMany(ImagesEvent::class, 'event_id', 'id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function venue()
    {
        return $this->belongsTo(Venue::class, 'venue_id');
    }

    public function registrations()
    {
        return $this->hasMany(EventRegistration::class, 'event_id', 'id');
    }

    // ========== SCOPES ==========
    
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('status', 1)
              ->orWhereNull('status');
        });
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope to only include expired events (end_date < current Philippines time).
     */
    public function scopeExpired($query)
    {
        $now = Carbon::now(self::PH_TIMEZONE);
        return $query->where('end_date', '<', $now);
    }

    /**
     * Scope to only include upcoming events (end_date >= current Philippines time).
     */
    public function scopeUpcoming($query)
    {
        $now = Carbon::now(self::PH_TIMEZONE);
        return $query->where('end_date', '>=', $now);
    }

    // ========== HELPER METHODS ==========
    
    public function isExpired()
    {
        $now = Carbon::now(self::PH_TIMEZONE);
        return $this->end_date < $now;
    }

    public function isActive()
    {
        return ($this->status == 1 || is_null($this->status)) && !$this->isExpired();
    }

    public function isArchived()
    {
        return $this->status == 0;
    }

    public function getStartDateInTimezone($timezone = 'Asia/Manila')
    {
        return $this->start_date->setTimezone($timezone);
    }

    public function getEndDateInTimezone($timezone = 'Asia/Manila')
    {
        return $this->end_date->setTimezone($timezone);
    }

    /**
     * Scope: events that should appear on the Active Events admin list.
     *
     * Includes:
     *  - Regular active events with upcoming end_date
     *  - Manually-restored events (regardless of end_date)
     */
    public function scopeVisibleActive($query)
    {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->where('is_manually_restored', true)
                ->orWhere(function ($sub) {
                    $sub->where('is_manually_restored', false)
                        ->where('end_date', '>=', now());
                });
            });
    }
}