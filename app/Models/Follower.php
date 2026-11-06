<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Follower extends Model
{
    use HasFactory;

    protected $table = 'followers';

    protected $fillable = [
        'follower_alumni_id',
        'followed_alumni_id',
        'status',
    ];

    /**
     * Get the alumni who is following.
     */
    public function follower(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'follower_alumni_id');
    }

    /**
     * Get the alumni who is being followed.
     */
    public function followed(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'followed_alumni_id');
    }
}