<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class ImagesAnnouncement extends Model
{
    protected $table = 'images_announcements';

    protected $fillable = [
        'announcement_id',
        'image_path',
    ];

    protected $casts = [
        'announcement_id' => 'integer',
    ];

    public function announcement()
    {
        return $this->belongsTo(Announcement::class, 'announcement_id', 'id');
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

// This is the ImagesAnnouncement.php model