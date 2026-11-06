<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reaction extends Model
{
    protected $table = 'reactions';
    
    protected $fillable = [
        'alumni_id',
        'post_id',
        'reaction',
        'announcement_id',
    ];
    
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
    
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}