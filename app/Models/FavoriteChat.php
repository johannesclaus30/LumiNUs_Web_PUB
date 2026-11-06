<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavoriteChat extends Model
{
    use HasFactory;

    protected $table = 'favorite_chats';

    protected $fillable = [
        'user_id',
        'contact_id',
        'contact_type',
    ];

    public function user()
    {
        return $this->belongsTo(Alumni::class, 'user_id');
    }
}