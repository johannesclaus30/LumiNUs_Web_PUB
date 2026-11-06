<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlumniCheckpoint extends Model
{
    protected $table = 'alumni_checkpoints';
    protected $guarded = [];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }
}