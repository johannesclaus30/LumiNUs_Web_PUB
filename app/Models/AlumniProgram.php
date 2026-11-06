<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlumniProgram extends Model
{
    protected $table = 'alumni_programs';

    protected $fillable = [
        'alumni_id',
        'program_type',
        'program_name',
        'student_id_number',
        'student_id_hash', // 👈 ADDED
        'year_graduated',
        'enrollment_status',
        'is_primary',
    ];

    protected $casts = [
        'year_graduated' => 'date',
        'is_primary'     => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function ($program) {
            if ($program->isDirty('student_id_number') && !empty($program->student_id_number)) {
                $program->student_id_hash = hash('sha256', trim($program->student_id_number));
            }
        });
    }

    public function alumni(): BelongsTo
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }
}