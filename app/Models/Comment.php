<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $table = 'comments';
    
    protected $fillable = [
        'alumni_id',
        'post_id',
        'comment',
        'moderation_status',
        'parent_id',
        'announcement_id',
    ];
    
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Get the alumni that owns the comment.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
    
    /**
     * Get the post that owns the comment.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
    
    /**
     * Get the announcement that owns the comment.
     */
    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }
    
    /**
     * Get the parent comment (for nested comments).
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }
    
    /**
     * Get the replies to this comment.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }
    
    /**
     * Get the reports for this comment.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }
}