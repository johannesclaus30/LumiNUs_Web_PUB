<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ImagesEvent extends Model
{
    protected $table = 'images_events';

    protected $fillable = [
        'event_id',
        'image_path',
    ];

    protected $casts = [
        'event_id' => 'integer',
    ];

    /**
     * Relationship back to the Event
     */
    public function event()
    {
        return $this->belongsTo(Event::class, 'event_id', 'id');
    }

    public function getImageUrlAttribute(): ?string
    {
        if (! $this->image_path) {
            return null;
        }

        $path = trim((string) $this->image_path);

        if ($path === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $path)) {
            return $path;
        }

        if (str_starts_with($path, '/storage/')) {
            return $path;
        }

        if (str_starts_with($path, 'storage/')) {
            return '/' . $path;
        }

        if (str_starts_with($path, '/')) {
            return $path;
        }

        if (Storage::disk('public')->exists($path)) {
            return asset('storage/' . $path);
        }

        $baseUrl = rtrim((string) config('filesystems.disks.supabase_admin.url'), '/');

        if ($baseUrl === '') {
            return null;
        }

        return $baseUrl . '/' . ltrim($path, '/');
    }
}