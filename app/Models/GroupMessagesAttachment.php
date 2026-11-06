<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupMessagesAttachment extends Model
{
    protected $table = 'group_messages_attachments';

    // ✅ DISABLE timestamps - the table doesn't have updated_at/created_at
    public $timestamps = false;

    protected $fillable = [
        'group_message_id',
        'attachment_type',
        'attachment_path',
        'file_name',
        'file_size',
    ];

    public function message()
    {
        return $this->belongsTo(GroupMessage::class, 'group_message_id');
    }
}

// This is the GroupMessagesAttachment.php file