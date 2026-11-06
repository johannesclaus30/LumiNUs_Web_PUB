<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Repost extends Model
{
    use HasFactory;

    protected $table = 'reposts';

    protected $fillable = [
        'alumni_id',
        'post_id',
        'caption',
        'moderation_status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the alumni who reposted.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * Get the post that was reposted.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}