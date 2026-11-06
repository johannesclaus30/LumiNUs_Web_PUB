<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommentReport extends Model
{
    use HasFactory;

    protected $table = 'comment_reports';

    protected $fillable = [
        'comment_id',
        'reporter_id',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the comment that was reported.
     */
    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Get the alumni who reported the comment.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'reporter_id');
    }
}