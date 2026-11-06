<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessage extends Model
{
    protected $table = 'group_messages';

    protected $fillable = [
        'group_chat_id',
        'sender_id',
        'sender_type',
        'content',
        'reactions',
        'is_read',
        'deleted_by',
    ];

    protected $casts = [
        'reactions' => 'array',
        'deleted_by' => 'array',
        'is_read' => 'boolean',
    ];

    public function group()
    {
        return $this->belongsTo(GroupChat::class, 'group_chat_id');
    }

    public function sender()
    {
        if ($this->sender_type === 'alumni') {
            return $this->belongsTo(Alumni::class, 'sender_id');
        } elseif ($this->sender_type === 'admin') {
            return $this->belongsTo(Admin::class, 'sender_id');
        }
        return null;
    }

    public function attachments()
    {
        return $this->hasMany(GroupMessagesAttachment::class, 'group_message_id');
    }
}

// This is the GroupMessage.php file 