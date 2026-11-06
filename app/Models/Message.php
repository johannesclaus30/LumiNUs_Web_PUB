<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    protected $table = 'messages';
    
    protected $fillable = [
        'sender_id',
        'receiver_id',
        'sender_type',
        'receiver_type',
        'content',
        'is_read',
        'deleted_by',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'deleted_by' => 'array',
    ];

    public function sender()
    {
        if ($this->sender_type === 'admin') {
            return $this->belongsTo(Admin::class, 'sender_id');
        }
        return $this->belongsTo(Alumni::class, 'sender_id');
    }

    public function receiver()
    {
        if ($this->receiver_type === 'admin') {
            return $this->belongsTo(Admin::class, 'receiver_id');
        }
        return $this->belongsTo(Alumni::class, 'receiver_id');
    }

    public function attachments()
    {
        return $this->hasMany(MessageAttachment::class, 'message_id');
    }
}