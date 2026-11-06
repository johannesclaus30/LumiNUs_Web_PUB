<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perks;
use App\Models\PerkImage;   
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PerksController extends Controller
{
    const PH_TIMEZONE = 'Asia/Manila';

    public function index()
    {
        // Auto-archive expired perks
        $this->autoArchiveExpiredPerks();

        // Get TOTAL counts from the ENTIRE database
        $totalPerks = Perks::count();
        $activePerks = Perks::visibleActive()->count();
        $archivedPerks = Perks::archived()->count();

        // Get paginated perks for display (active OR manually-restored)
        $perks = Perks::with('images')
            ->visibleActive()
            ->orderByRaw('CASE WHEN is_manually_restored = true THEN 1 ELSE 0 END ASC')
            ->orderBy('created_at', 'desc')
            ->paginate(6);
            
        return view('admin_perks', compact(
            'perks', 
            'totalPerks', 
            'activePerks', 
            'archivedPerks'
        ));
    }

    protected function autoArchiveExpiredPerks()
    {
        $now = Carbon::now(self::PH_TIMEZONE)->startOfDay();
        
        $expiredPerks = Perks::where(function($query) {
                $query->where('status', 1)
                    ->orWhereNull('status');
            })
            ->where('valid_until', '<', $now)
            ->where('is_manually_restored', false)   // ✅ NEVER re-archive manually-restored perks
            ->get();
        
        if ($expiredPerks->isNotEmpty()) {
            foreach ($expiredPerks as $perk) {
                $perk->update(['status' => 0]);
                Log::info("Perk auto-archived (Philippines time)", [
                    'perk_id' => $perk->id,
                    'title' => $perk->title,
                    'valid_until_utc' => $perk->valid_until->toDateString(),
                    'current_ph_date' => $now->toDateString()
                ]);
            }
        }
    }

    public function archived()
    {
        // Get TOTAL counts from the ENTIRE database
        $totalPerks = Perks::count();
        $activePerks = Perks::visibleActive()->count();
        $archivedPerks = Perks::archived()->count();

        // Get paginated perks for display (only archived)
        $perks = Perks::with('images')
            ->archived()
            ->orderBy('created_at', 'desc')
            ->paginate(6);

        return view('admin_perks', compact(
            'perks', 
            'totalPerks', 
            'activePerks', 
            'archivedPerks'
        ));
    }

    public function create()
    {
        return view('perks.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date',
            'status' => 'nullable',
            'images' => 'nullable|array|max:5', 
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'images.max' => 'You can only upload a maximum of 5 images.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'images.*.mimes' => 'Images must be JPG, PNG, or WEBP format.',
        ]);

        $validUntil = Carbon::parse($request->valid_until, self::PH_TIMEZONE)->utc();

        $adminId = $request->session()->get('admin_id');

        if (!$adminId) {
            abort(403, 'Admin session expired. Please log in again.');
        }

        $perk = Perks::create([
            'title'       => $request->title,
            'description' => $request->description,
            'valid_until' => $validUntil,
            'status'      => $this->normalizeStatus($request->input('status'), 1),
            'admin_id'    => $adminId,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storePerkImage($perk, $file);
                $perk->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('perks.index')->with('success', 'Perk created successfully.');
    }

    public function edit(Perks $perk)
    {
        $perk->load('images');
        return view('perks.edit', compact('perk'));
    }

    public function update(Request $request, Perks $perk)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'valid_until' => 'required|date',
            'images' => 'nullable|array|max:5',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ], [
            'images.max' => 'You can only upload a maximum of 5 images.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'images.*.mimes' => 'Images must be JPG, PNG, or WEBP format.',
        ]);

        $toRemove = $request->input('remove_existing', []);

        if (! empty($toRemove)) {
            foreach ($toRemove as $id) {
                $img = $perk->images()->find($id);

                if ($img) {
                    $this->deleteStoredImage($img->image_path);
                    $img->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            $existingCount = $perk->images()->count();
            $newCount = count($request->file('images'));

            if ($existingCount + $newCount > 5) {
                return back()->withErrors(['images' => 'You can upload up to 5 images total (including existing).'])->withInput();
            }
        }

        $validUntil = Carbon::parse($request->valid_until, self::PH_TIMEZONE)->utc();

        // ============================================================
        // AUTO-UN-RESTORE
        // ------------------------------------------------------------
        // If the admin edits a manually-restored perk and moves the
        // valid_until date into the future, we treat it as a fresh,
        // legitimate active perk. Clear the restored flag so it
        // appears normally and is visible to alumni again.
        // ============================================================
        $today = Carbon::now(self::PH_TIMEZONE)->startOfDay();
        $isNowActive = $validUntil->gte($today);

        if ($perk->is_manually_restored && $isNowActive) {
            $perk->is_manually_restored = false;
            $perk->manually_restored_at = null;

            Log::info('Perk auto-un-restored: valid_until moved to the future', [
                'perk_id'        => $perk->id,
                'title'          => $perk->title,
                'new_valid_until'=> $validUntil->toDateString(),
            ]);
        }

        $perk->update([
            'title'       => $request->title,
            'description' => $request->description,
            'valid_until' => $validUntil,
            'status'      => $request->filled('status')
                ? $this->normalizeStatus($request->input('status'), $perk->status ?? 1)
                : $perk->status,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storePerkImage($perk, $file);
                $perk->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('perks.index')->with('success', 'Perk updated successfully.');
    }

    public function destroy(Perks $perk) 
    {
        $wasRestored = $perk->is_manually_restored;

        $perk->update([
            'status'               => 0,
            'is_manually_restored' => false,
            'manually_restored_at' => null,
        ]);

        $message = $wasRestored
            ? 'Perk returned to archive.'
            : 'Perk archived.';

        return redirect()->route('perks.index')->with('success', $message);
    }

    public function restore(Perks $perk)
    {
        // Determine if this is an "expired" perk being restored for admin access
        $today = Carbon::now(self::PH_TIMEZONE)->startOfDay();
        $isExpired = $perk->valid_until && $perk->valid_until->lt($today);

        $perk->update([
            'status'               => 1,
            'is_manually_restored' => $isExpired,
            'manually_restored_at' => $isExpired ? now() : null,
        ]);

        $message = $isExpired
            ? 'Perk restored for admin access. It will stay active so you can edit it — alumni will not see it.'
            : 'Perk restored.';

        return redirect()->route('perks.archived')->with('success', $message);
    }

    public function permanentDelete(Perks $perk)
    {
        if ((int) $perk->status !== 0) {
            return redirect()->back()->with('error', 'Only archived perks can be permanently deleted.');
        }

        foreach ($perk->images as $media) {
            $this->deleteStoredImage($media->image_path);
            $media->delete();
        }

        $perk->delete();

        return redirect()->route('perks.archived')->with('success', 'Perk permanently deleted.');
    }

    protected function storePerkImage(Perks $perk, $file): string
    {
        $directory = 'perks_images/' . $perk->id;
        $fileName = $this->buildAttachmentFileName($file, 'perk-image');

        Storage::disk('supabase_admin')->putFileAs($directory, $file, $fileName, 'public');

        return $directory . '/' . $fileName;
    }

    protected function deleteStoredImage(?string $imagePath): void
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

    protected function buildAttachmentFileName($file, string $fallbackPrefix): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = $fallbackPrefix;
        }

        return $slug . '-' . Str::random(12) . '.' . $extension;
    }

    protected function normalizeStatus($status, int $default = 1): int
    {
        if ($status === null || $status === '') {
            return $default;
        }

        if (is_int($status) || ctype_digit((string) $status)) {
            return (int) $status;
        }

        return strtolower((string) $status) === 'inactive' ? 0 : 1;
    }
}

// This is the PerksController.php