<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StorageService
{
    /**
     * Upload a file to Supabase Storage
     */
    public function upload($file, $path, $disk = 'supabase')
    {
        $fileName = $path . '/' . Str::random(12) . '.' . $file->getClientOriginalExtension();
        Storage::disk($disk)->put($fileName, file_get_contents($file), 'public');
        return $fileName;
    }
    
    /**
     * Delete a file from Supabase Storage
     */
    public function delete($path, $disk = 'supabase')
    {
        if ($path) {
            Storage::disk($disk)->delete($path);
        }
    }
    
    /**
     * Get public URL for a file
     */
    public function getUrl($path)
    {
        if (!$path) return null;
        
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }
        
        $baseUrl = env('SUPABASE_URL') . '/storage/v1/object/public/' . env('SUPABASE_BUCKET', 'luminus_messages_attachments');
        return $baseUrl . '/' . ltrim($path, '/');
    }
}