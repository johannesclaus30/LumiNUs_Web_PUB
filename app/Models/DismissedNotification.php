<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DismissedNotification extends Model
{
    use HasFactory;

    protected $table = 'dismissed_notifications';

    protected $fillable = [
        'alumni_id',
        'notification_key',
    ];

    /**
     * The alumni this notification belongs to.
     */
    public function alumni()
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }
}