<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MessagesAttachment extends Model
{
    protected $table = 'messages_attachments';
    
    protected $fillable = [
        'message_id',
        'attachment_type',
        'attachment_path',
    ];
    
    public function message()
    {
        return $this->belongsTo(Message::class);
    }
}