<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniSkill extends Model
{
    use HasFactory;

    protected $table = 'alumni_skills';

    protected $fillable = [
        'alumni_id',
        'skill_name',
    ];

    /**
     * Get the alumni that owns the skill.
     */
    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class);
    }
}