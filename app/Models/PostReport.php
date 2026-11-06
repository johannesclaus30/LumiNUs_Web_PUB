<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PostReport extends Model
{
    use HasFactory;

    protected $table = 'post_reports';

    protected $fillable = [
        'post_id',
        'reporter_id',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the post that was reported.
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the alumni who reported the post.
     */
    public function reporter(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'reporter_id');
    }
}