<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Event;
use App\Models\EventRegistration;
use App\Models\GroupChat;
use App\Models\Alumni;
use App\Models\GroupChatMember;
use App\Models\Venue;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Services\GeoInviteService;
use App\Services\BrevoMailService;
use Carbon\Carbon;

class EventController extends Controller
{
    // Define Philippines timezone constant
    const PH_TIMEZONE = 'Asia/Manila';

    public function index(Request $request)
    {
        // Auto-archive expired events using Philippines time
        $this->autoArchiveExpiredEvents();
        
        $filter = $request->get('filter', 'all');
        
        $totalEvents = Event::count();

        $activeEvents = Event::active()
            ->where(function ($q) {
                $q->where('is_manually_restored', true)
                ->orWhere(function ($sub) {
                    $sub->where('is_manually_restored', false)
                        ->where('end_date', '>=', now());
                });
            })
            ->count();

        $archivedEvents = Event::archived()->count();
        
        // Build query:
        // - Regular active events must be upcoming (end_date >= now)
        // - OR manually-restored events (exempt from the upcoming filter)
        $query = Event::active()
            ->where(function ($q) {
                $q->where('is_manually_restored', true)      // ← restored: always show
                ->orWhere(function ($sub) {
                    // Regular active events: only upcoming ones
                    $sub->where('is_manually_restored', false)
                        ->where('end_date', '>=', now());
                });
            });

        if ($filter !== 'all') {
            $query->where('event_type', $filter);
        }

        $events = $query->withCount('registrations')
            ->orderBy('start_date', 'asc')
            ->paginate(6);
                
        return view('admin_events', compact(
            'events', 'totalEvents', 'activeEvents', 'archivedEvents', 'filter'
        ));
    }

    protected function autoArchiveExpiredEvents()
    {
        // Get current time in Philippines
        $now = Carbon::now(self::PH_TIMEZONE);
        
        $expiredEvents = Event::where(function($query) {
            $query->where('status', 1)
                ->orWhereNull('status');
        })
        ->where('end_date', '<', $now)
        ->where('is_manually_restored', false)   // ✅ NEVER re-archive manually-restored events
        ->get();
        
        if ($expiredEvents->isNotEmpty()) {
            foreach ($expiredEvents as $event) {
                $event->update(['status' => 0]);
                Log::info("Event auto-archived (Philippines time)", [
                    'event_id' => $event->id,
                    'title' => $event->title,
                    'end_date_utc' => $event->end_date->toDateTimeString(),
                    'current_ph_time' => $now->toDateTimeString()
                ]);
            }
        }
    }

    public function archived()
    {
        $filter = null;
        
        // Get counts
        $totalEvents = Event::count();

        // Active = regular upcoming + manually-restored
        $activeEvents = Event::active()
            ->where(function ($q) {
                $q->where('is_manually_restored', true)
                ->orWhere(function ($sub) {
                    $sub->where('is_manually_restored', false)
                        ->where('end_date', '>=', now());
                });
            })
            ->count();

        // Archived = status 0 (unchanged)
        $archivedEvents = Event::archived()->count();
        
        // Show only archived events (status = 0)
        $events = Event::archived()
            ->withCount('registrations')
            ->orderBy('end_date', 'desc')
            ->paginate(6);
        
        return view('admin_events', compact(
            'events',
            'totalEvents',
            'activeEvents',
            'archivedEvents',
            'filter'
        ));
    }

    // ✅ ADD THIS METHOD
    public function create()
    {
        $event = new Event();
        return view('events.create', compact('event'));
    }

    public function store(Request $request)
    {
        $this->prepareVenueAndPlatformPayload($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_capacity' => 'required|integer|min:1',
            'event_type' => 'required|in:In-Person,Online,Hybrid',
            'platform' => 'required_if:event_type,Online,Hybrid|nullable|string|max:255',
            'platform_url' => 'nullable|url|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'required_if:event_type,In-Person,Hybrid|nullable|string|max:255',
            'venue_latitude' => 'nullable|numeric|between:-90,90',
            'venue_longitude' => 'nullable|numeric|between:-180,180',
            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'images.max' => 'You can only upload a maximum of 5 images.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'images.*.mimes' => 'Images must be JPG, PNG, or WEBP format.',
        ]);

        $adminId = $request->session()->get('admin_id');
        abort_unless($adminId, 403);

        $venueId = $this->syncVenue($request);

        // Parse dates in Philippines time, convert to UTC for storage
        $startDate = Carbon::parse($request->start_date, self::PH_TIMEZONE)->utc();
        $endDate = Carbon::parse($request->end_date, self::PH_TIMEZONE)->utc();

        $event = Event::create([
            'admin_id' => $adminId,
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_capacity' => $request->max_capacity,
            'event_type' => $request->event_type,
            'platform' => $request->platform,
            'platform_url' => $request->platform_url,
            'venue_id' => $venueId,
            'status' => 1,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $file) {
                $path = $this->storeEventImage($event, $file);
                $event->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()->route('events.index')->with('success', 'Event published successfully!');
    }

    public function edit(Event $event)
    {
        $event->load(['images', 'admin', 'venue']);
        return view('events.edit', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $this->prepareVenueAndPlatformPayload($request);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'max_capacity' => 'required|integer|min:1',
            'event_type' => 'required|in:In-Person,Online,Hybrid',
            'platform' => 'required_if:event_type,Online,Hybrid|nullable|string|max:255',
            'platform_url' => 'nullable|url|max:255',
            'venue_name' => 'nullable|string|max:255',
            'venue_address' => 'required_if:event_type,In-Person,Hybrid|nullable|string|max:255',
            'venue_latitude' => 'nullable|numeric|between:-90,90',
            'venue_longitude' => 'nullable|numeric|between:-180,180',

            'remove_existing'   => 'nullable|array',
            'remove_existing.*' => 'integer|exists:images_events,id',
            'deleted_media'     => 'nullable|array',   // keep for backwards compat
            'deleted_media.*'   => 'integer|exists:images_events,id',

            'images' => 'nullable|array|max:5',
            'images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'images.max' => 'You can only upload a maximum of 5 images.',
            'images.*.max' => 'Each image must not exceed 5MB.',
            'images.*.mimes' => 'Images must be JPG, PNG, or WEBP format.',
        ]);

        // Accept either `deleted_media` (legacy) or `remove_existing` (new upload zone)
        $removedIds = $request->input('remove_existing', []);

        if (empty($removedIds)) {
            $removedIds = $request->input('deleted_media', []);
        }

        $removedIds = array_values(array_filter(array_map('intval', (array) $removedIds)));

        $deletedCount = count($removedIds);

        $existingImageCount = $event->images()->count();
        $newImageCount = $request->hasFile('images') ? count($request->file('images')) : 0;
        $totalAfterUpdate = ($existingImageCount - $deletedCount) + $newImageCount;

        if ($totalAfterUpdate > 5) {
            throw ValidationException::withMessages([
                'images' => "You can only have 5 images total. You have {$existingImageCount} existing, marked {$deletedCount} for removal, and are adding {$newImageCount} new. That would leave {$totalAfterUpdate} images.",
            ]);
        }

        // Process deletions
        foreach ($removedIds as $mediaId) {
            $media = $event->images()->find($mediaId);
            if ($media) {
                $this->deleteStoredImage($media->image_path);
                $media->delete();
            }
        }
                


        $venueId = $this->syncVenue($request, $event->venue_id);

        // Parse dates in Philippines time, convert to UTC for storage
        $startDate = Carbon::parse($request->start_date, self::PH_TIMEZONE)->utc();
        $endDate = Carbon::parse($request->end_date, self::PH_TIMEZONE)->utc();

        // ============================================================
        // AUTO-UN-RESTORE
        // ------------------------------------------------------------
        // If the admin edits a manually-restored event and moves the
        // end_date into the future, we treat it as a fresh, legitimate
        // upcoming event. Clear the restored flag so it appears
        // normally on the active list and is visible to alumni again.
        // ============================================================
        $isNowUpcoming = $endDate->isFuture();

        if ($event->is_manually_restored && $isNowUpcoming) {
            $event->is_manually_restored = false;
            $event->manually_restored_at = null;

            Log::info('Event auto-un-restored: end_date moved to the future', [
                'event_id'      => $event->id,
                'title'         => $event->title,
                'new_end_date'  => $endDate->toDateTimeString(),
            ]);
        }

        $event->update([
            'title' => $request->title,
            'description' => $request->description,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'max_capacity' => $request->max_capacity,
            'event_type' => $request->event_type,
            'platform' => $request->platform,
            'platform_url' => $request->platform_url,
            'venue_id' => $venueId,
        ]);

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $this->storeEventImage($event, $image);
                $event->images()->create([
                    'image_path' => $path,
                ]);
            }
        }

        return redirect()
            ->route('events.index')
            ->with('success', 'Event updated successfully!');
    }

    public function destroy(Event $event)
    {
        // If this was a manually-restored event, clear the flag —
        // it's going back to a "normal archived" state.
        $wasRestored = $event->is_manually_restored;

        $event->update([
            'status'               => 0,
            'is_manually_restored' => false,
            'manually_restored_at' => null,
        ]);

        $message = $wasRestored
            ? 'Event returned to archive.'
            : 'Event successfully archived!';

        return redirect()->route('events.index')->with('success', $message);
    }

    public function restore(Event $event)
    {
        // Determine if this is an "expired" event being restored for admin access
        $isExpired = $event->end_date && $event->end_date->isPast();

        $event->update([
            'status'                => 1,
            'is_manually_restored'  => $isExpired,     // ✅ only flag if it's already over
            'manually_restored_at'  => $isExpired ? now() : null,
        ]);

        $message = $isExpired
            ? 'Event restored for admin access. It will stay active so you can edit it — alumni will not see it.'
            : 'Event restored.';

        return redirect()->route('events.archived')->with('success', $message);
    }

    public function permanentDelete(Event $event)
    {
        // Only allow permanent deletion of archived events
        if ((int) $event->status !== 0) {
            return redirect()->back()->with('error', 'Only archived events can be permanently deleted.');
        }

        // ✅ NEW: Null out the group_chat_id BEFORE deleting the event,
        //        so the FK constraint doesn't cause issues.
        //        (Preserves the group chat for history.)
        if ($event->group_chat_id) {
            $groupId = $event->group_chat_id;
            $event->group_chat_id = null;
            $event->save();
            
            // Now safe to delete the group (FK constraint is released)
            \App\Models\GroupChat::where('id', $groupId)->delete();
            \App\Models\GroupChatMember::where('group_chat_id', $groupId)->delete();
            // (messages and attachments need FK cascade or manual cleanup)
        }

        // Delete all associated image files from storage
        foreach ($event->images as $media) {
            $this->deleteStoredImage($media->image_path);
            $media->delete();
        }

        // Delete venue if it exists and is only associated with this event
        if ($event->venue) {
            $venueUsageCount = Event::where('venue_id', $event->venue_id)
                ->where('id', '!=', $event->id)
                ->count();
            
            if ($venueUsageCount === 0) {
                $event->venue->delete();
            }
        }

        // Hard delete the event
        $event->delete();

        return redirect()->route('events.archived')->with('success', 'Event permanently deleted.');
    }

    public function registrations(Event $event)
    {
        // Eager load registrations with alumni data
        $event->load(['registrations.alumni', 'images', 'venue']);
        
        // Get registrations with alumni details
        $registrations = $event->registrations()
            ->with('alumni')
            ->orderBy('created_at', 'desc')
            ->paginate(15);
        
        // Get registration statistics
        $totalRegistrations = $event->registrations()->count();
        $confirmedRegistrations = $event->registrations()
            ->where('registration_confirmation', true)
            ->count();
        $pendingRegistrations = $totalRegistrations - $confirmedRegistrations;
        
        // Calculate fill rate
        $fillRate = $event->max_capacity > 0 
            ? round(($totalRegistrations / $event->max_capacity) * 100, 1) 
            : 0;
        
        $hasGroupChat = !empty($event->group_chat_id)
            && GroupChat::where('id', $event->group_chat_id)->exists();

        return view('events.registrations', compact(
            'event',
            'registrations',
            'totalRegistrations',
            'confirmedRegistrations',
            'pendingRegistrations',
            'fillRate',
            'hasGroupChat'
        ));
    }

    protected function syncVenue(Request $request, ?int $existingVenueId = null): ?int
    {
        $address = trim((string) $request->input('venue_address', ''));
        $latitude = $request->input('venue_latitude');
        $longitude = $request->input('venue_longitude');

        if ($address === '' || $latitude === null || $longitude === null) {
            return null;
        }

        $venue = $existingVenueId ? Venue::find($existingVenueId) : null;
        $venueData = [
            'name' => trim((string) $request->input('venue_name', $address)) ?: $address,
            'address' => $address,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ];

        if ($venue) {
            $venue->update($venueData);
            return $venue->getKey();
        }

        return Venue::create($venueData)->getKey();
    }

    protected function prepareVenueAndPlatformPayload(Request $request): void
    {
        $eventType = $request->input('event_type');

        if ($eventType === 'Online') {
            $request->merge([
                'venue_name' => null,
                'venue_address' => null,
                'venue_latitude' => null,
                'venue_longitude' => null,
            ]);
            return;
        }

        if ($eventType === 'In-Person') {
            $request->merge([
                'platform' => null,
                'platform_url' => null,
            ]);
        }
    }

    protected function storeEventImage(Event $event, $file): string
    {
        $directory = 'events_images/' . $event->id;
        $fileName = $this->buildEventImageFileName($file);

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

    protected function buildEventImageFileName($file): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'jpg');
        $name = pathinfo((string) $file->getClientOriginalName(), PATHINFO_FILENAME);
        $slug = Str::slug($name);

        if ($slug === '') {
            $slug = 'event-image';
        }

        return $slug . '-' . Str::random(12) . '.' . $extension;
    }

/**
 * Toggle the auto-managed group chat for an event.
 *
 * ENABLE  → create a new group chat and sync members.
 * DISABLE → delete the group chat entirely (members + row).
 *
 * No `is_active` column is used — "active" is inferred from
 * whether $event->group_chat_id is set.
 *
 * Coordinators + Assistant Coordinators are ALWAYS synced.
 */
public function toggleGroupChat(Request $request, Event $event)
{
    try {
        $enable = filter_var($request->input('enabled', true), FILTER_VALIDATE_BOOLEAN);

        $adminInfo = $this->getCurrentAdminInfo();
        if (!$adminInfo) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $currentAdminId = $adminInfo['id'];

        // ============================================================
        // DISABLE — hard delete the group chat and its members.
        //           Message history is intentionally discarded.
        // ============================================================
        if (!$enable) {
            if ($event->group_chat_id) {
                $groupId = $event->group_chat_id;

                DB::beginTransaction();

                // 1. Unlink first so the FK on events.group_chat_id
                //    doesn't block the delete.
                $event->group_chat_id = null;
                $event->save();

                // 2. Delete all members (alumni, coordinators, admins)
                GroupChatMember::where('group_chat_id', $groupId)->delete();

                // 3. Delete the group chat row itself
                GroupChat::where('id', $groupId)->delete();

                // 4. Clean up any group messages + attachments,
                //    since we're not preserving history.
                if (class_exists(\App\Models\GroupMessage::class)) {
                    $messageIds = \App\Models\GroupMessage::where('group_chat_id', $groupId)
                        ->pluck('id');

                    if ($messageIds->isNotEmpty()
                        && class_exists(\App\Models\GroupMessageAttachment::class)) {
                        \App\Models\GroupMessageAttachment::whereIn('group_message_id', $messageIds)
                            ->delete();
                    }

                    \App\Models\GroupMessage::where('group_chat_id', $groupId)->delete();
                }

                DB::commit();
            }

            return response()->json([
                'success' => true,
                'enabled' => false,
                'message' => 'Group chat deleted for this event.',
            ]);
        }

        // ============================================================
        // ENABLE — create a fresh group chat and sync members.
        //          Because disable fully deletes the old group,
        //          no duplicates can ever exist.
        // ============================================================
        DB::beginTransaction();

        $group = GroupChat::create([
            'name'            => 'Event: ' . $event->title,
            'created_by'      => $currentAdminId,
            'created_by_type' => 'admin',
            'avatar_url'      => null,
        ]);

        // Add creating admin as group-admin
        GroupChatMember::create([
            'group_chat_id' => $group->id,
            'alumni_id'     => $currentAdminId,
            'member_type'   => 'admin',
            'role'          => 'admin',
            'archived'      => false,
            'muted'         => false,
        ]);

        // Sync alumni + ALL coordinators / asst. coordinators
        $this->syncEventGroupMembers($event, $group, $currentAdminId);

        // Link event → group
        $event->group_chat_id = $group->id;
        $event->save();

        DB::commit();

        return response()->json([
            'success'    => true,
            'enabled'    => true,
            'group_id'   => $group->id,
            'group_name' => $group->name,
            'message'    => 'Group chat created and members added.',
        ]);

    } catch (\Throwable $e) {
        DB::rollBack();
        Log::error('toggleGroupChat failed: ' . $e->getMessage(), [
            'event_id' => $event->id ?? null,
            'trace'    => $e->getTraceAsString(),
        ]);

        return response()->json([
            'success' => false,
            'error'   => 'Failed to toggle group chat: ' . $e->getMessage(),
        ], 500);
    }
}

    public function toggleRegistrationConfirmation(Request $request, EventRegistration $registration)
    {
        $request->validate([
            'registration_confirmation' => 'required|boolean',
        ]);

        $wasConfirmed = (bool) $registration->registration_confirmation;
        $isNowConfirmed = (bool) $request->input('registration_confirmation');

        // 1. Persist the new state first — DB write is what matters
        $registration->update([
            'registration_confirmation' => $isNowConfirmed,
        ]);

        // 2. Only send mail when transitioning into "confirmed"
        //    (never spam on un-confirm, never double-send on re-confirm)
        if ($isNowConfirmed && !$wasConfirmed) {
            try {
                // Eager-load what the email needs
                $registration->loadMissing(['alumni', 'event.venue']);
                $alumnus = $registration->alumni;
                $event   = $registration->event;

                if ($alumnus && !empty($alumnus->email)
                    && filter_var($alumnus->email, FILTER_VALIDATE_EMAIL)) {

                    $service = new BrevoMailService();

                    // 🌟 SMART CALENDAR LINKS (Google & Apple/Outlook)
                    $startUtc = $event->start_date->format('Ymd\THis\Z');
                    $endUtc = $event->end_date->format('Ymd\THis\Z');
                    $location = $event->event_type === 'Online' ? $event->platform : ($event->venue->name ?? 'TBA');
                    $details = 'You are confirmed for this event! See you there.';

                    // Google Calendar Link
                    $googleCalUrl = "https://calendar.google.com/calendar/render?action=TEMPLATE"
                        . "&text=" . urlencode($event->title)
                        . "&dates=" . $startUtc . "/" . $endUtc
                        . "&details=" . urlencode($details)
                        . "&location=" . urlencode($location);

                    // Apple / Outlook (ics download) Link
                    $calendarData = [
                        'title'     => $event->title,
                        'start'     => $startUtc,
                        'end'       => $endUtc,
                        'location'  => $location,
                        'details'   => $details
                    ];
                    $encodedCal = base64_encode(json_encode($calendarData));
                    $appleCalUrl = route('events.calendar', ['data' => $encodedCal]);

                    $htmlContent = view('emails.registration_confirmed', [
                        'alumnus' => $alumnus,
                        'event'   => $event,
                        'googleCalUrl' => $googleCalUrl,
                        'appleCalUrl'  => $appleCalUrl,
                    ])->render();

                    $service->sendEmail(
                        $alumnus->email,
                        "Registration Confirmed – {$event->title}",
                        $htmlContent
                    );

                    Log::info('Registration confirmation email sent', [
                        'registration_id' => $registration->id,
                        'event_id'        => $event->id,
                        'alumni_id'       => $alumnus->id,
                        'email'           => $alumnus->email,
                        'admin_id'        => $request->session()->get('admin_id'),
                    ]);
                } else {
                    Log::warning('Skipped confirmation email — no valid alumni email', [
                        'registration_id' => $registration->id,
                        'alumni_id'       => $registration->alumni_id ?? null,
                    ]);
                }
            } catch (\Throwable $e) {
                // Email failure must NOT break the admin flow
                Log::error('Failed to send registration confirmation email', [
                    'registration_id' => $registration->id,
                    'error'           => $e->getMessage(),
                ]);
            }
        }

        $statusWord = $isNowConfirmed ? 'confirmed' : 'marked as pending';

        return redirect()
            ->route('events.registrations', $registration->event_id)
            ->with('success', "Registration {$statusWord}" . ($isNowConfirmed ? ' and notification emailed.' : '.'));
    }

    /**
     * Add all registered alumni + all Coordinators + all Assistant
     * Coordinators to the given group chat. Idempotent.
     */
    protected function syncEventGroupMembers(Event $event, GroupChat $group, ?int $excludeAdminId = null): void
    {
        // ---------------- 1. Registered Alumni ----------------
        $alumniIds = EventRegistration::where('event_id', $event->id)
            ->pluck('alumni_id')
            ->unique()
            ->toArray();

        foreach ($alumniIds as $alumniId) {
            $this->addGroupMemberIfMissing($group->id, (int) $alumniId, 'alumni', 'alumni');
        }

        // ---------------- 2. Coordinators + Asst. Coordinators ----------------
        $staff = Admin::whereIn('admin_role', ['Coordinator', 'Assistant Coordinator'])
            ->where('account_status', 1)
            ->get();

        foreach ($staff as $admin) {
            if ($excludeAdminId && $admin->id == $excludeAdminId) {
                continue; // already added as creator
            }

            // Coordinators → group-admin role
            // Assistant Coordinators → regular member role
            $groupRole = ($admin->admin_role === 'Coordinator') ? 'admin' : 'alumni';

            $this->addGroupMemberIfMissing(
                $group->id,
                (int) $admin->id,
                'admin',       // member_type (this is an admin account)
                $groupRole     // role within the group
            );
        }
    }

    /**
     * Insert (group_chat_id, alumni_id, member_type) only if missing.
     * Matches the compound dedupe key used everywhere in GroupChatController.
     */
    protected function addGroupMemberIfMissing(
        int $groupId,
        int $userId,
        string $memberType,
        string $role = 'alumni'
    ): void {
        $existing = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $userId)
            ->where('member_type', $memberType)
            ->first();

        if ($existing) {
            if ($existing->archived) {
                $existing->update(['archived' => false, 'muted' => false]);
            }
            return;
        }

        GroupChatMember::create([
            'group_chat_id' => $groupId,
            'alumni_id'     => $userId,
            'member_type'   => $memberType,
            'role'          => $role,
            'archived'      => false,
            'muted'         => false,
        ]);
    }

    /**
     * Local copy of the auth helper (mirrors GroupChatController).
     */
    protected function getCurrentAdminInfo(): ?array
    {
        $adminId = session('admin_id');
        if (!$adminId) return null;

        $admin = Admin::find($adminId);
        if (!$admin) return null;

        return [
            'id'        => $admin->id,
            'user_type' => 'admin',
            'name'      => trim($admin->admin_first_name . ' ' . $admin->admin_last_name),
        ];
    }

        /**
     * ============================================================
     * GEO-INVITE — Preview nearby alumni (JSON)
     * ============================================================
     * GET /admin/events/{event}/geo-invite/preview
     *
     * Called by the modal whenever the admin changes the radius slider.
     */
    public function geoInvitePreview(Request $request, Event $event, GeoInviteService $geo)
    {
        // Only for In-Person / Hybrid events with a venue
        if (!in_array($event->event_type, ['In-Person', 'Hybrid'], true)) {
            return response()->json([
                'success' => false,
                'error'   => 'Geo-invites are only available for In-Person and Hybrid events.',
            ], 422);
        }

        // Block geo-invites for manually-restored events
        // (they're hidden from alumni, so promoting them is misleading)
        if ($event->is_manually_restored) {
            return response()->json([
                'success' => false,
                'error'   => 'This event was restored for admin access only. It is not visible to alumni, so geo-invites are disabled. Move its dates to the future to promote it again.',
            ], 422);
        }

        if (!$event->venue || $event->venue->latitude === null || $event->venue->longitude === null) {
            return response()->json([
                'success' => false,
                'error'   => 'This event has no venue coordinates set. Please edit the event and pick a venue on the map.',
            ], 422);
        }

        $request->validate([
            'radius_km' => 'nullable|numeric|min:0.5|max:500',
            'skip_recent' => 'nullable|boolean',
        ]);

        $radiusKm = (float) $request->input('radius_km', 25);
        $skipRecent = filter_var($request->input('skip_recent', false), FILTER_VALIDATE_BOOLEAN);

        $result = $geo->findNearbyAlumni($event, $radiusKm, $skipRecent);

        return response()->json([
            'success'                => true,
            'event' => [
                'id'         => $event->id,
                'title'      => $event->title,
                'event_type' => $event->event_type,
                'start_date' => $event->start_date?->toIso8601String(),
                'end_date'   => $event->end_date?->toIso8601String(),
            ],
            'radius_km'              => $result['radius_km'],
            'venue'                  => $result['venue'] ?? null,
            'alumni'                 => $result['alumni'],
            'count'                  => count($result['alumni']),
            'total_with_address'     => $result['total_with_address'] ?? 0,
            'total_active_alumni'    => $result['total_active_alumni'] ?? 0,
            'missing_address_count'  => $result['missing_address_count'] ?? 0,
        ]);
    }

    /**
     * ============================================================
     * GEO-INVITE — Send invitation emails via Brevo
     * ============================================================
     * POST /admin/events/{event}/geo-invite/send
     *
     * Body:
     *   alumni_ids: [1,2,3...]        (optional — if omitted, uses radius)
     *   radius_km:  25                (used if alumni_ids not provided)
     *   skip_recent: false
     *   custom_message: "Hi! ..."     (optional)
     */
    public function geoInviteSend(Request $request, Event $event, GeoInviteService $geo)
    {
        if (!in_array($event->event_type, ['In-Person', 'Hybrid'], true)) {
            return response()->json([
                'success' => false,
                'error'   => 'Geo-invites are only available for In-Person and Hybrid events.',
            ], 422);
        }

        // Block geo-invites for manually-restored events
        // (they're hidden from alumni, so promoting them is misleading)
        if ($event->is_manually_restored) {
            return response()->json([
                'success' => false,
                'error'   => 'This event was restored for admin access only. It is not visible to alumni, so geo-invites are disabled. Move its dates to the future to promote it again.',
            ], 422);
        }

        if (!$event->venue || $event->venue->latitude === null || $event->venue->longitude === null) {
            return response()->json([
                'success' => false,
                'error'   => 'This event has no venue coordinates set.',
            ], 422);
        }

        $validated = $request->validate([
            'alumni_ids'     => 'nullable|array',
            'alumni_ids.*' => 'integer|exists:alumnis_base,id',
            'radius_km'      => 'nullable|numeric|min:0.5|max:500',
            'skip_recent'    => 'nullable|boolean',
            'custom_message' => 'nullable|string|max:2000',
        ]);

        // ---------------------------------------------------------
        // 1. Resolve the final list of alumni to invite
        // ---------------------------------------------------------
        if (!empty($validated['alumni_ids'])) {
            // Admin manually picked specific alumni from the modal list
            $radiusKm   = (float) ($validated['radius_km'] ?? 25);
            $skipRecent = filter_var($validated['skip_recent'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $preview = $geo->findNearbyAlumni($event, $radiusKm, $skipRecent);
            $allowed = collect($preview['alumni'])->keyBy('id');
            $targets = collect($validated['alumni_ids'])
                ->map(fn($id) => $allowed->get($id))
                ->filter()
                ->values()
                ->all();
        } else {
            // Fallback: send to everyone in radius
            $radiusKm   = (float) ($validated['radius_km'] ?? 25);
            $skipRecent = filter_var($validated['skip_recent'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $preview    = $geo->findNearbyAlumni($event, $radiusKm, $skipRecent);
            $targets    = $preview['alumni'];
        }

        if (empty($targets)) {
            return response()->json([
                'success' => false,
                'error'   => 'No alumni to invite — the selected list is empty.',
            ], 422);
        }

        // ---------------------------------------------------------
        // 2. Send emails via Brevo
        // ---------------------------------------------------------
        $service = new BrevoMailService();

        $sent      = 0;
        $failed    = 0;
        $failures  = [];
        $adminId   = $request->session()->get('admin_id');

        // Load admin info for the "invited by" line in the email
        $admin = Admin::find($adminId);

        foreach ($targets as $alumnus) {
        // ================================================
        // STEP 1 — Send the email (this is what really matters)
        // ================================================
        $emailSent = false;

        try {
            $htmlContent = view('emails.event_geo_invite', [
                'alumnus'        => $alumnus,
                'event'          => $event,
                'venue'          => $event->venue,
                'distance_km'    => $alumnus['distance_km'] ?? null,
                'custom_message' => $validated['custom_message'] ?? null,
                'invitedBy'      => $admin
                    ? trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? ''))
                    : 'LumiNUs Team',
            ])->render();

            $subject = "You're invited: {$event->title}";

            $service->sendEmail($alumnus['email'], $subject, $htmlContent);

            $emailSent = true;
            $sent++;

            Log::info('Geo-invite sent', [
                'event_id'  => $event->id,
                'alumni_id' => $alumnus['id'],
                'email'     => $alumnus['email'],
                'distance'  => $alumnus['distance_km'] ?? null,
                'admin_id'  => $adminId,
            ]);

        } catch (\Throwable $e) {
            $failed++;
            $failures[] = [
                'alumni_id' => $alumnus['id'],
                'email'     => $alumnus['email'] ?? null,
                'stage'     => 'email_send',
                'error'     => $e->getMessage(),
            ];

            Log::error('Geo-invite EMAIL FAILED', [
                'event_id'  => $event->id,
                'alumni_id' => $alumnus['id'],
                'email'     => $alumnus['email'] ?? null,
                'error'     => $e->getMessage(),
            ]);

            continue; // skip the rest for this alumnus
        }

        // ================================================
        // STEP 2 — Soft marker update (non-critical)
        // ================================================
        if ($emailSent) {
            try {
                Alumni::where('id', $alumnus['id'])->update([
                    'last_invite_sent_at' => now(),
                    'invite_count'        => DB::raw('COALESCE(invite_count, 0) + 1'),
                ]);
            } catch (\Throwable $e) {
                // Just log it — do NOT increment $failed
                Log::warning('Geo-invite: soft marker update failed (email was sent OK)', [
                    'event_id'  => $event->id,
                    'alumni_id' => $alumnus['id'],
                    'error'     => $e->getMessage(),
                ]);
            }
        }
    }

        return response()->json([
            'success'  => true,
            'sent'     => $sent,
            'failed'   => $failed,
            'total'    => count($targets),
            'failures' => $failures,
            'message'  => "✅ {$sent} invitation(s) sent" .
                          ($failed > 0 ? ", ❌ {$failed} failed." : '.'),
        ]);
    }

}

// This is the EventController.php