<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; 

class AnnouncementController extends Controller
{
    private const MAX_IMAGE_COUNT = 5;
    private const MAX_IMAGE_SIZE_MB = 3;
    private const MAX_VIDEO_SIZE_MB = 30;

    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        
        // Get counts using local time (no UTC conversion needed since DB uses PST)
        $now = now();
        
        $totalAnnouncements = Announcement::where('status', 1)->count();
        
        $activeAnnouncements = Announcement::where('status', 1)
            ->where(function($q) use ($now) {
                $q->whereNull('scheduled_at')
                ->orWhere('scheduled_at', '<=', $now);
            })
            ->count();
            
        $archivedAnnouncements = Announcement::where('status', 0)->count();
        
        $scheduledAnnouncements = Announcement::where('status', 1)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', $now)
            ->count();
        
        // Build query based on filter
        $query = Announcement::where('status', 1);
        
        if ($filter === 'active') {
            $query->where(function($q) use ($now) {
                $q->whereNull('scheduled_at')
                ->orWhere('scheduled_at', '<=', $now);
            });
        } elseif ($filter === 'scheduled') {
            $query->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>', $now);
        }
        
        $announcements = $query->orderByRaw('
            CASE 
                WHEN scheduled_at IS NULL OR scheduled_at <= NOW() THEN 0
                ELSE 1
            END,
            CASE 
                WHEN scheduled_at IS NULL OR scheduled_at <= NOW() THEN COALESCE(date_posted, created_at)
                ELSE scheduled_at
            END DESC
        ')->paginate(6);
        
        return view('admin_announcements', compact(
            'announcements',
            'totalAnnouncements',
            'activeAnnouncements',
            'archivedAnnouncements',
            'scheduledAnnouncements',
            'filter'
        ));
    }

    public function archived()
    {
        $filter = null;
        
        $now = now();
        
        $totalAnnouncements = Announcement::where('status', 1)->count();
        $activeAnnouncements = Announcement::where('status', 1)
            ->where(function($q) use ($now) {
                $q->whereNull('scheduled_at')
                ->orWhere('scheduled_at', '<=', $now);
            })
            ->count();
        $archivedAnnouncements = Announcement::where('status', 0)->count();
        $scheduledAnnouncements = Announcement::whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', $now)
            ->where('status', 1)
            ->count();
        
        $announcements = Announcement::where('status', 0)
            ->orderBy('date_posted', 'desc')
            ->paginate(6);
        
        return view('admin_announcements', compact(
            'announcements',
            'totalAnnouncements',
            'activeAnnouncements',
            'archivedAnnouncements',
            'scheduledAnnouncements',
            'filter'
        ));
    }

    public function create()
    {
        return view('announcements.create');
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'announcement_description' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:' . (self::MAX_IMAGE_SIZE_MB * 1024),
            'video' => 'nullable|file|mimetypes:video/mp4|max:' . (self::MAX_VIDEO_SIZE_MB * 1024),
        ];

        $request->validate($rules);

        // Manual check for mutually exclusive media
        if ($request->hasFile('images') && $request->hasFile('video')) {
            throw ValidationException::withMessages([
                'images' => 'You can only upload either images or a video, not both.'
            ]);
        }

        $adminId = $request->session()->get('admin_id');
        if (!$adminId) abort(403);

        // Store scheduled_at as-is (database is already in PST)
        $scheduledAt = $request->scheduled_at;

        $announcement = Announcement::create([
            'admin_id' => $adminId,
            'title' => $request->title,
            'announcement_description' => $request->announcement_description,
            'date_posted' => now(),
            'scheduled_at' => $scheduledAt,
            'status' => 1,
        ]);

        // Handle images
        if ($request->hasFile('images')) {
            $uploadedImages = $this->normalizeUploadedImages($request->file('images'));
            foreach ($uploadedImages as $image) {
                $path = $this->storeAnnouncementImage($announcement, $image);
                $announcement->images()->create(['image_path' => $path]);
            }
        }

        // Handle video
        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $path = $this->storeAnnouncementVideo($announcement, $video);
            $announcement->images()->create(['image_path' => $path]);
        }

        return redirect()->route('announcements.index')->with('success', 'Announcement created successfully!');
    }

    public function edit(Announcement $announcement)
    {
        $announcement->load('images');
        return view('announcements.edit', compact('announcement'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $rules = [
            'title' => 'required|string|max:255',
            'announcement_description' => 'required|string',
            'scheduled_at' => 'nullable|date',
            'deleted_media' => 'nullable|array',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:' . (self::MAX_IMAGE_SIZE_MB * 1024),
            'video' => 'nullable|file|mimetypes:video/mp4|max:' . (self::MAX_VIDEO_SIZE_MB * 1024),
        ];

        $request->validate($rules);

        if ($request->hasFile('images') && $request->hasFile('video')) {
            throw ValidationException::withMessages([
                'images' => 'You can only upload either images or a video, not both.'
            ]);
        }

        $announcement->load('images');

        // Delete marked media
        $deletedMediaIds = collect($request->input('deleted_media', []))->map(fn($v) => (int)$v)->filter()->all();
        foreach ($deletedMediaIds as $id) {
            $media = $announcement->images->firstWhere('id', $id);
            if ($media) {
                $this->deleteStoredImage($media->image_path);
                $media->delete();
            }
        }

        // Count remaining valid attachments
        $remainingAttachments = $announcement->images->reject(fn($a) => in_array((int)$a->id, $deletedMediaIds));
        $remainingCount = $remainingAttachments->count();

        // Validate total count after new uploads
        $newImageCount = $request->hasFile('images') ? count($this->normalizeUploadedImages($request->file('images'))) : 0;
        $hasNewVideo = $request->hasFile('video');

        if ($hasNewVideo && $remainingCount > 0) {
            throw ValidationException::withMessages(['video' => 'Cannot add video when existing attachments are present.']);
        }

        if ($newImageCount + $remainingCount > self::MAX_IMAGE_COUNT) {
            throw ValidationException::withMessages(['images' => 'Total images cannot exceed 5.']);
        }

        // Save new files
        if ($request->hasFile('images')) {
            foreach ($this->normalizeUploadedImages($request->file('images')) as $img) {
                $path = $this->storeAnnouncementImage($announcement, $img);
                $announcement->images()->create(['image_path' => $path]);
            }
        }

        if ($request->hasFile('video')) {
            $video = $request->file('video');
            $path = $this->storeAnnouncementVideo($announcement, $video);
            $announcement->images()->create(['image_path' => $path]);
        }

        // Store scheduled_at as-is (database is already in PST)
        $scheduledAt = $request->scheduled_at;

        $announcement->update([
            'title' => $request->title,
            'announcement_description' => $request->announcement_description,
            'scheduled_at' => $scheduledAt,
        ]);

        return redirect()->route('announcements.index')->with('success', 'Announcement updated successfully!');
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->update(['status' => 0]);
        return redirect()->route('announcements.index')->with('success', 'Announcement archived.');
    }

    public function restore(Announcement $announcement)
    {
        $announcement->update(['status' => 1]);
        return redirect()->route('announcements.archived')->with('success', 'Announcement restored.');
    }

    public function permanentDelete(Announcement $announcement)
    {
        // Delete all associated files first
        foreach ($announcement->images as $media) {
            $this->deleteStoredImage($media->image_path);
            $media->delete();
        }
        
        $announcement->delete(); // Hard delete
        
        return redirect()->back()->with('success', 'Announcement permanently deleted.');
    }

    private function normalizeUploadedImages(mixed $images): array
    {
        if (! $images) {
            return [];
        }

        if (! is_array($images)) {
            return [$images];
        }

        return array_values(array_filter($images));
    }

    private function isImageAttachment(?string $path): bool
    {
        if (! $path) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'], true);
    }

    private function storeAnnouncementImage(Announcement $announcement, $image): string
    {
        $directory = 'announcements_images/' . $announcement->id;
        $fileName = $this->buildAttachmentFileName($image, 'announcement-image');
        Storage::disk('supabase_admin')->putFileAs($directory, $image, $fileName, 'public');
        return $directory . '/' . $fileName;
    }

    private function storeAnnouncementVideo(Announcement $announcement, $video): string
    {
        $directory = 'announcements_videos/' . $announcement->id;
        $fileName = $this->buildAttachmentFileName($video, 'announcement-video');
        Storage::disk('supabase_admin')->putFileAs($directory, $video, $fileName, 'public');
        return $directory . '/' . $fileName;
    }

    private function deleteStoredImage(?string $imagePath): void
    {
        $path = trim((string) $imagePath);

        if ($path === '') {
            return;
        }

        foreach (['public', 'supabase_admin'] as $diskName) {
            $disk = Storage::disk($diskName);

            if ($disk->exists($path)) {
                $disk->delete($path);
                return;
            }
        }
    }

    private function buildAttachmentFileName($file, string $fallbackPrefix): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = $fallbackPrefix;
        }

        return $slug . '-' . Str::random(12) . '.' . $extension;
    }

    /**
     * Check if a scheduled announcement has been published
     */
    public function checkStatus($id)
    {
        $announcement = Announcement::findOrFail($id);
        
        $now = now();
        $isPublished = $announcement->scheduled_at && $announcement->scheduled_at <= $now;
        
        // If scheduled time has passed and status is still active (1)
        if ($isPublished && $announcement->status == 1) {
            // Optionally update any fields if needed
            // $announcement->update(['date_posted' => $now]);
        }
        
        // Get updated stats
        $stats = [
            'total' => Announcement::where('status', 1)->count(),
            'active' => Announcement::where('status', 1)
                ->where(function($q) use ($now) {
                    $q->whereNull('scheduled_at')
                    ->orWhere('scheduled_at', '<=', $now);
                })->count(),
            'scheduled' => Announcement::where('status', 1)
                ->whereNotNull('scheduled_at')
                ->where('scheduled_at', '>', $now)->count(),
            'archived' => Announcement::where('status', 0)->count(),
        ];
        
        return response()->json([
            'status' => $isPublished ? 'published' : 'scheduled',
            'stats' => $stats,
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'scheduled_at' => $announcement->scheduled_at ? $announcement->scheduled_at->format('M d, Y \a\t h:i A') : null,
                'is_published' => $isPublished
            ]
        ]);
    }
}