<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupChat extends Model
{
    // ❌ REMOVE: use SoftDeletes;

    protected $table = 'group_chats';

    protected $fillable = [
        'name',
        'avatar_url',
        'created_by',
    ];

    // If you need relationships:
    public function members()
    {
        return $this->hasMany(GroupChatMember::class);
    }

    public function messages()
    {
        return $this->hasMany(GroupMessage::class);
    }

    public function creator()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
}

// This is the GroupChat.php file