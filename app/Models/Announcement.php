<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Announcement extends Model
{
    protected $fillable = [
        'admin_id',
        'title',
        'announcement_description',
        'date_posted',
        'scheduled_at',
        'status',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'date_posted'  => 'datetime',
        'status'       => 'integer',
    ];

    /**
     * Backward-compatibility accessor for the old scheduled_post_at column.
     * Only used if something still references $announcement->scheduled_post_at.
     */
    public function getScheduledPostAtAttribute($value)
    {
        if ($value !== null) {
            return Carbon::parse($value);
        }

        return $this->scheduled_at;
    }

    /**
     * Relationship with announcement images/videos.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ImagesAnnouncement::class);
    }
}

// This is the Announcement.php