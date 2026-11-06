<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes; 

class Post extends Model
{
    use HasFactory, SoftDeletes; // ✅ RESTORE SoftDeletes HERE

    protected $table = 'posts';

    protected $fillable = [
        'alumni_id',
        'caption',
        'moderation_status',
        'visibility',
        'is_draft',
        'is_hidden',
    ];

    protected $casts = [
        'is_draft' => 'boolean',
        'is_hidden' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the alumni that owns the post.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }

    /**
     * Get the images for the post.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ImagesPost::class);
    }

    /**
     * Get the comments for the post.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the reactions for the post.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Get the reposts for the post.
     */
    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * Get the reports for this post.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(PostReport::class);
    }

    /**
     * Scope a query to only include approved posts.
     */
    public function scopeApproved($query)
    {
        return $query->where('moderation_status', 'approved');
    }

    /**
     * Scope a query to only include visible posts (not hidden).
     */
    public function scopeVisible($query)
    {
        return $query->where('is_hidden', false);
    }
}