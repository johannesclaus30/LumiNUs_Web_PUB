<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupChatMember extends Model
{
    protected $table = 'group_chat_members';

    protected $fillable = [
        'group_chat_id',
        'alumni_id',
        'last_read_message_id',
        'role',
        'archived',
        'ignored',
        'muted',
        'member_type',
        'last_read_at',
    ];

    protected $casts = [
        'archived' => 'boolean',
        'ignored' => 'boolean',
        'muted' => 'boolean',
        'last_read_at'  => 'datetime',
    ];

    public function group()
    {
        return $this->belongsTo(GroupChat::class, 'group_chat_id');
    }

    public function alumni()
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }

    public function lastReadMessage()
    {
        return $this->belongsTo(GroupMessage::class, 'last_read_message_id');
    }
}

// This is the GroupChatMember.php 