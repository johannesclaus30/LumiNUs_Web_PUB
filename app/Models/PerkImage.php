<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PerkImage extends Model
{
    // Table name for perk images (adjust if your DB uses a different name)
    protected $table = 'images_perks';

    protected $fillable = [
        'perk_id',
        'image_path',
    ];

    /**
     * Relationship back to the Perks model
     */
    public function perk()
    {
        return $this->belongsTo(Perks::class, 'perk_id');
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
