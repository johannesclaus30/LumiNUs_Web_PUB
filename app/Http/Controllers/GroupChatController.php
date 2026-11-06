<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\GroupChat;
use App\Models\GroupChatMember;
use App\Models\GroupMessage;
use App\Models\GroupMessagesAttachment;
use App\Models\Alumni;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GroupChatController extends Controller
{
    public function __construct(
        private \App\Services\MessageCryptoService $crypto
    ) {}

public function getGroups(Request $request)
{
    try {
        $adminInfo = $this->getCurrentAdminInfo();
        
        if (!$adminInfo) {
            Log::error('getGroups: No admin info found');
            return response()->json([]);
        }

        $adminId = $adminInfo['id'];
        $adminType = $adminInfo['user_type'];
        $showArchived = $request->get('archived', '0') === '1';
        Log::info('getGroups: Admin ID = ' . $adminId . ', showArchived = ' . ($showArchived ? 'true' : 'false'));

        $memberGroups = GroupChatMember::where('alumni_id', $adminId)
            ->where('member_type', $adminType) // 🔥 ADD THIS - only get memberships where the member_type matches
            ->pluck('group_chat_id')
            ->toArray();
        
        Log::info('getGroups: Found ' . count($memberGroups) . ' member group IDs');

        if (empty($memberGroups)) {
            Log::info('getGroups: No groups found, returning empty array');
            return response()->json([]);
        }

        $groups = GroupChat::whereIn('id', $memberGroups)->get();
        Log::info('getGroups: Retrieved ' . $groups->count() . ' groups');

        $result = [];

        foreach ($groups as $group) {
            $memberSetting = GroupChatMember::where('group_chat_id', $group->id)
                ->where('alumni_id', $adminId)
                ->where('member_type', $adminType) 
                ->first();

            $isArchived = $memberSetting ? (bool)$memberSetting->archived : false;
            if ($showArchived && !$isArchived) continue;
            if (!$showArchived && $isArchived) continue;

            $lastMessage = GroupMessage::where('group_chat_id', $group->id)
                ->where(function($query) use ($adminId) {
                    $query->whereNull('deleted_by')
                        ->orWhereRaw('NOT (deleted_by @> ARRAY[?]::bigint[])', [$adminId]);
                })
                ->latest()
                ->first();
                
            $lastMessageContent = null;
            $lastMessageFromMe = false;
            $lastMessageTimestamp = null;

            $lastMessageSenderName = null;

            if ($lastMessage) {
                $decryptedContent = $this->crypto->decrypt($lastMessage->content);

                $lastMessageAttachments = GroupMessagesAttachment::where('group_message_id', $lastMessage->id)->get();

                $lastMessageContent = $this->buildGroupMessagePreview($decryptedContent, $lastMessageAttachments);

                $lastMessageFromMe = ($lastMessage->sender_id == $adminId && $lastMessage->sender_type === 'admin');
                $lastMessageTimestamp = $lastMessage->created_at ? $lastMessage->created_at->toISOString() : null;

                // ⬇️ NEW: resolve the sender's display name
                if (!$lastMessageFromMe) {
                    if ($lastMessage->sender_type === 'admin') {
                        $sender = Admin::find($lastMessage->sender_id);
                        if ($sender) {
                            $lastMessageSenderName = trim($sender->admin_first_name . ' ' . $sender->admin_last_name);
                        }
                    } else {
                        $sender = Alumni::find($lastMessage->sender_id);
                        if ($sender) {
                            $lastMessageSenderName = $this->getAlumniFullName($sender);
                        }
                    }
                }
            }

            $memberCount = GroupChatMember::where('group_chat_id', $group->id)->count();

// ✅ FIX: Use auto-detection for creator
$creatorInfo = $this->getUserInfoWithAutoDetect($group->created_by);
if ($creatorInfo) {
    $creatorType = $creatorInfo['user_type'];
    $creatorName = $creatorInfo['full_name'];
} else {
    // Fallback: use stored type
    $creatorType = $group->created_by_type ?? 'admin';
    $creatorName = 'Unknown';
    
    if ($creatorType === 'admin') {
        $creatorAdmin = Admin::find($group->created_by);
        if ($creatorAdmin) {
            $creatorName = trim($creatorAdmin->admin_first_name . ' ' . $creatorAdmin->admin_last_name);
        }
    } else {
        $creatorAlumni = Alumni::find($group->created_by);
        if ($creatorAlumni) {
            $creatorName = $this->getAlumniFullName($creatorAlumni);
        }
    }
}

            $unreadQuery = GroupMessage::where('group_chat_id', $group->id)
                ->where('sender_id', '!=', $adminId)
                ->where(function($query) use ($adminId) {
                    $query->whereNull('deleted_by')
                        ->orWhereRaw('NOT (deleted_by @> ARRAY[?]::bigint[])', [$adminId]);
                });

            // Only count messages newer than when this member last read the group
            if ($memberSetting && $memberSetting->last_read_at) {
                $unreadQuery->where('created_at', '>', $memberSetting->last_read_at);
            }

            $unreadCount = $unreadQuery->count();

            $avatarUrl = null;
            if ($group->avatar_url) {
                $avatarUrl = $this->getGroupAvatarUrl($group->avatar_url);
            }

            $result[] = [
                'id' => (int)$group->id,
                'type' => 'group',
                'name' => $group->name,
                'initials' => $this->getInitials($group->name),
                'avatar' => $avatarUrl,
                'member_count' => $memberCount,
                'created_by' => $group->created_by,
                'created_by_type' => $creatorType,
                'created_by_name' => $creatorName,
                'last_message' => $lastMessageContent,
                'last_message_timestamp' => $lastMessageTimestamp,
                'last_message_from_me' => $lastMessageFromMe,
                'last_message_sender_name' => $lastMessageSenderName,
                'unread_count' => $unreadCount,
                'is_archived' => $isArchived,
                'is_muted' => $memberSetting ? (bool)$memberSetting->muted : false,
            ];
        }

        usort($result, function($a, $b) {
            $timeA = $a['last_message_timestamp'] ? strtotime($a['last_message_timestamp']) : 0;
            $timeB = $b['last_message_timestamp'] ? strtotime($b['last_message_timestamp']) : 0;
            return $timeB - $timeA;
        });

        Log::info('getGroups: Returning ' . count($result) . ' groups');
        return response()->json(array_values($result));

    } catch (\Exception $e) {
        Log::error('getGroups: Fatal error: ' . $e->getMessage());
        Log::error('getGroups: Trace: ' . $e->getTraceAsString());
        return response()->json([]);
    }
}

public function createGroup(Request $request)
{
    try {
        $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|string',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $adminInfo = $this->getCurrentAdminInfo();
        if (!$adminInfo) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $adminId = $adminInfo['id'];
        $adminType = $adminInfo['user_type']; // 'admin'

        // Parse members from frontend
        $members = json_decode($request->members, true);
        
        if (!is_array($members) || count($members) < 2) {
            return response()->json(['error' => 'Please add at least 2 members'], 400);
        }

        $validMembers = []; // ✅ ADD THIS LINE
        $seenKeys = [];

        foreach ($members as $member) {
            $memberId = $member['id'] ?? null;
            $userType = $member['user_type'] ?? 'alumni';
            
            if (!$memberId) continue;
            
            // Skip the current admin (they're added automatically)
            if ($memberId == $adminId && $userType === $adminType) {
                continue;
            }
            
            // ✅ Create a compound key to detect duplicates
            $key = $memberId . '_' . $userType;
            
            // Skip if we've already seen this compound key
            if (in_array($key, $seenKeys)) {
                Log::info("Skipping duplicate member: {$key}");
                continue;
            }
            $seenKeys[] = $key;
            
            // Determine the correct member_type
            $validMembers[] = [
                'id' => $memberId,
                'user_type' => $userType,
                'role' => ($userType === 'admin') ? 'admin' : 'alumni'
            ];
        }

        // Create group
        $group = GroupChat::create([
            'name' => $request->name,
            'created_by' => $adminId,
            'created_by_type' => $adminType, // 'admin'
            'avatar_url' => null,
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $extension = $file->getClientOriginalExtension();
            $fileName = 'group_avatar/' . $group->id . '_' . Str::random(12) . '.' . $extension;
            
            try {
                Storage::disk('supabase_private_messages')->put($fileName, file_get_contents($file), 'private');
                $group->avatar_url = $fileName;
                $group->save();
                Log::info('Group avatar stored at: ' . $fileName);
            } catch (\Exception $e) {
                Log::error('Failed to upload group avatar: ' . $e->getMessage());
            }
        }

        // ✅ ADD CREATOR AS MEMBER with correct type
        GroupChatMember::create([
            'group_chat_id' => $group->id,
            'alumni_id' => $adminId,
            'member_type' => 'admin', // ✅ FORCE 'admin' since the creator is a system admin
            'role' => 'admin',
            'archived' => false,
            'muted' => false,
        ]);

        // Get creator name - always admin since only admins can create groups
        $creatorInfo = $this->getUserInfoWithAutoDetect($adminId);
        $creatorName = $creatorInfo ? $creatorInfo['full_name'] : $adminInfo['name'];

        // ✅ ADD OTHER MEMBERS with correct member_type
        foreach ($validMembers as $member) {
            GroupChatMember::create([
                'group_chat_id' => $group->id,
                'alumni_id' => $member['id'],
                'member_type' => $member['user_type'], // ✅ This should be 'admin' or 'alumni'
                'role' => $member['role'] ?? 'alumni',
                'archived' => false,
                'muted' => false,
            ]);
            
            Log::info("Added member {$member['id']} with member_type: {$member['user_type']}");
        }

        Log::info("Group {$group->id} created by admin {$adminId} (name: {$creatorName})");
        
        // Verify the members were added correctly
        $memberCount = GroupChatMember::where('group_chat_id', $group->id)->count();
        Log::info("Group {$group->id} has {$memberCount} members");

        // Log all members for debugging
        $allMembers = GroupChatMember::where('group_chat_id', $group->id)->get();
        foreach ($allMembers as $m) {
            Log::info("Group {$group->id} member: alumni_id={$m->alumni_id}, member_type={$m->member_type}, role={$m->role}");
        }

        $avatarUrl = $group->avatar_url ? $this->getGroupAvatarUrl($group->avatar_url) : null;

        return response()->json([
            'success' => true,
            'message' => 'Channel created successfully',
            'group' => [
                'id' => (int)$group->id,
                'name' => $group->name,
                'avatar' => $avatarUrl,
                'member_count' => $memberCount,
                'created_by' => $adminId,
                'created_by_type' => $adminType,
                'created_by_name' => $creatorName,
                'is_archived' => false,
                'is_muted' => false,
                'unread_count' => 0,
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error creating group: ' . $e->getMessage());
        Log::error('Error trace: ' . $e->getTraceAsString());
        return response()->json([
            'error' => 'Failed to create channel: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Determine if a user is an admin or alumni by checking the database tables
 */
private function getUserType($userId)
{
    // First check if it's an admin
    $admin = Admin::find($userId);
    if ($admin) {
        return 'admin';
    }
    
    // Then check if it's an alumni
    $alumni = Alumni::find($userId);
    if ($alumni) {
        return 'alumni';
    }
    
    return null; // User not found
}

/**
 * Get user info (name, initials, avatar) by ID and type
 */
private function getUserInfo($userId, $userType)
{
    if ($userType === 'admin') {
        $admin = Admin::find($userId);
        if ($admin) {
            return [
                'full_name' => trim($admin->admin_first_name . ' ' . $admin->admin_last_name),
                'initials' => strtoupper(substr($admin->admin_first_name ?? 'A', 0, 1) . substr($admin->admin_last_name ?? 'A', 0, 1)),
                'avatar' => $this->resolveAdminPhotoUrl($admin->photo),
                'is_online' => true,
            ];
        }
    } elseif ($userType === 'alumni') {
        $alumni = Alumni::find($userId);
        if ($alumni) {
            return [
                'full_name' => $this->getAlumniFullName($alumni),
                'initials' => $this->getAlumniInitials($alumni),
                'avatar' => $alumni->alumni_photo ? $this->resolveAvatarUrl($alumni->alumni_photo) : null,
                'is_online' => $alumni->is_online ?? false,
            ];
        }
    }
    
    return null;
}

/**
 * Get user info with automatic type detection (doesn't rely on stored member_type)
 */
private function getUserInfoWithAutoDetect($userId)
{
    // First check if it's an admin
    $admin = Admin::find($userId);
    if ($admin) {
        return [
            'full_name' => trim($admin->admin_first_name . ' ' . $admin->admin_last_name),
            'initials' => strtoupper(substr($admin->admin_first_name ?? 'A', 0, 1) . substr($admin->admin_last_name ?? 'A', 0, 1)),
            'avatar' => $this->resolveAdminPhotoUrl($admin->photo),
            'is_online' => true,
            'user_type' => 'admin',
        ];
    }
    
    // Then check if it's an alumni
    $alumni = Alumni::find($userId);
    if ($alumni) {
        return [
            'full_name' => $this->getAlumniFullName($alumni),
            'initials' => $this->getAlumniInitials($alumni),
            'avatar' => $alumni->alumni_photo ? $this->resolveAvatarUrl($alumni->alumni_photo) : null,
            'is_online' => $alumni->is_online ?? false,
            'user_type' => 'alumni',
        ];
    }
    
    return null;
}

public function getGroupInfo($groupId)
{
    try {
        $adminInfo = $this->getCurrentAdminInfo();
        
        if (!$adminInfo) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $adminId = $adminInfo['id'];
        $adminType = $adminInfo['user_type']; // ✅ ADD THIS LINE - it was missing!

        $group = GroupChat::with(['members'])->find($groupId);

        if (!$group) {
            return response()->json(['error' => 'Group not found'], 404);
        }

        // ✅ Now $adminType is defined and can be used here
        $isMember = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $adminId)
            ->where('member_type', $adminType) // 🔥 Now $adminType exists
            ->exists();

        if (!$isMember) {
            return response()->json(['error' => 'You are not a member of this group'], 403);
        }

        // Get member settings
        $memberSetting = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $adminId)
            ->first();

        // ✅ FIX 1: Trust the stored creator type instead of auto-detecting
        $creatorType = $group->created_by_type ?? 'admin';
        $creatorInfo = $this->getUserInfo($group->created_by, $creatorType);
        
        if ($creatorInfo) {
            $creatorName = $creatorInfo['full_name'];
            $creatorIsSystemAdmin = ($creatorType === 'admin');
        } else {
            $creatorName = 'Unknown';
            $creatorIsSystemAdmin = false;
        }

        $members = [];
        foreach ($group->members as $member) {
            $userId = $member->alumni_id;
            $storedType = $member->member_type; // Trust the database!
            
            // ✅ FIX 2: Use explicit type, do NOT auto-detect
            $userInfo = $this->getUserInfo($userId, $storedType);
            
            if ($userInfo) {
                $fullName = $userInfo['full_name'];
                $initials = $userInfo['initials'];
                $avatar = $userInfo['avatar'];
                $isOnline = $userInfo['is_online'];
                $isSystemAdmin = ($storedType === 'admin');
                
                // ✅ FIX 3: Removed the dangerous $member->save() block that caused the crash
            } else {
                $fullName = 'Unknown User';
                $initials = '??';
                $avatar = null;
                $isOnline = false;
                $isSystemAdmin = false;
            }
            
            $isGroupAdmin = ($member->role === 'admin');
            $isCreator = ($group->created_by == $userId && $group->created_by_type === $storedType);
            
            $members[] = [
                'id' => (int)$userId,
                'user_type' => $storedType,
                'full_name' => $fullName,
                'initials' => $initials,
                'role' => $member->role,
                'is_online' => $isOnline,
                'avatar' => $avatar,
                'is_system_admin' => $isSystemAdmin,
                'is_group_admin' => $isGroupAdmin,
                'is_creator' => $isCreator,
            ];
        }

        // Check if current user is the creator
        $isCreator = ($group->created_by == $adminId);

        // Check if current user is a group admin
        $isGroupAdmin = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $adminId)
            ->where('role', 'admin')
            ->exists();

        // Get avatar URL with signed URL
        $avatarUrl = null;
        if ($group->avatar_url) {
            $avatarUrl = $this->getGroupAvatarUrl($group->avatar_url);
        }

        return response()->json([
            'id' => (int)$group->id,
            'name' => $group->name,
            'avatar' => $avatarUrl,
            'member_count' => count($members),
            'members' => $members,
            'is_admin' => $isGroupAdmin || $isCreator,
            'can_manage' => $isGroupAdmin || $isCreator,
            'is_archived' => $memberSetting ? (bool)$memberSetting->archived : false,
            'is_muted' => $memberSetting ? (bool)$memberSetting->muted : false,
            'created_by' => $group->created_by,
            'created_by_type' => $creatorType,
            'created_by_name' => $creatorName,
            'created_by_is_system_admin' => $creatorIsSystemAdmin ?? false,
        ]);

    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::error('Error getting group info: ' . $e->getMessage());
        \Illuminate\Support\Facades\Log::error('Trace: ' . $e->getTraceAsString());
        return response()->json(['error' => 'Failed to get group info: ' . $e->getMessage()], 500);
    }
}

    public function getMessages($groupId, Request $request)
    {
        try {
            $adminInfo = $this->getCurrentAdminInfo();
            $adminId = $adminInfo['id'];
            $adminType = $adminInfo['user_type'];
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // ✅ FIX: Check if admin is a member with correct member_type
            $isMember = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->where('member_type', $adminType) // 🔥 ADD THIS
                ->exists();

            if (!$isMember) {
                Log::warning("Admin {$adminId} is not a member of group {$groupId}");
                return response()->json(['error' => 'You are not a member of this group'], 403);
            }

            // ✅ Get pagination parameters
            $limit = (int) $request->get('limit', 50);
            $offset = (int) $request->get('offset', 0);

            // ✅ Get total count
            $totalCount = GroupMessage::where('group_chat_id', $groupId)
                ->where(function($query) use ($adminId) {
                    $query->whereNull('deleted_by')
                        ->orWhereRaw('NOT (deleted_by @> ARRAY[?]::bigint[])', [$adminId]);
                })
                ->count();

            // ✅ Get messages with pagination
            $messages = GroupMessage::where('group_chat_id', $groupId)
                ->where(function($query) use ($adminId) {
                    $query->whereNull('deleted_by')
                        ->orWhereRaw('NOT (deleted_by @> ARRAY[?]::bigint[])', [$adminId]);
                })
                ->orderBy('created_at', 'desc')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->reverse()
                ->values()
                ->map(function ($message) use ($adminId) {
                    $decryptedContent = $this->crypto->decrypt($message->content);

                    $senderName = 'Unknown';
                    if ($message->sender_type === 'alumni') {
                        $sender = Alumni::find($message->sender_id);
                        if ($sender) {
                            $senderName = $this->getAlumniFullName($sender);
                        }
                    } elseif ($message->sender_type === 'admin') {
                        $sender = Admin::find($message->sender_id);
                        if ($sender) {
                            $senderName = trim($sender->admin_first_name . ' ' . $sender->admin_last_name);
                        }
                    }
                    // Get attachments
                    $attachmentsData = [];
                    $attachments = GroupMessagesAttachment::where('group_message_id', $message->id)->get();
                    
                    foreach ($attachments as $attachment) {
                        $attachmentsData[] = [
                            'id' => $attachment->id,
                            'type' => $attachment->attachment_type,
                            'name' => pathinfo($attachment->attachment_path, PATHINFO_BASENAME),
                            'size' => null,
                            'url' => $this->getSecureAttachmentUrl($attachment),
                        ];
                    }

                    // In getMessages() method, remove the 'time' field
                    return [
                        'id' => $message->id,
                        'content' => $decryptedContent,
                        'sender_id' => $message->sender_id,
                        'sender_type' => $message->sender_type,
                        'sender_name' => $senderName,
                        'group_chat_id' => $message->group_chat_id,
                        'created_at' => $message->created_at ? $message->created_at->toISOString() : null,
                        // ❌ REMOVE: 'time' => $message->created_at ? $message->created_at->format('g:i A') : null,
                        'attachments' => $attachmentsData,
                    ];
                });

            Log::info("✅ Loaded " . count($messages) . " messages for group {$groupId} (total: {$totalCount})");

            return response()->json([
                'messages' => $messages,
                'total' => $totalCount,
                'limit' => $limit,
                'offset' => $offset,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting group messages: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json(['error' => 'Failed to load messages: ' . $e->getMessage()], 500);
        }
    }
    
public function sendMessage(Request $request, $groupId)
{
    try {
        $request->validate([
            'content' => 'required|string|max:5000',
        ]);

        $adminInfo = $this->getCurrentAdminInfo();
        $adminId = $adminInfo['id'];
        $adminType = $adminInfo['user_type'];
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $member = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $adminId)
            ->where('member_type', $adminType)
            ->first();

        if (!$member) {
            return response()->json(['error' => 'You are not a member of this group'], 403);
        }

        if ($member->archived) {
            return response()->json(['error' => 'This channel is archived'], 403);
        }

        $message = GroupMessage::create([
            'group_chat_id' => $groupId,
            'sender_id' => $adminId,
            'sender_type' => 'admin',
            'content' => $this->crypto->encrypt($request->content),
            'is_read' => false,
        ]);

        $admin = Admin::find($adminId);
        $senderName = $admin ? trim($admin->admin_first_name . ' ' . $admin->admin_last_name) : 'Admin';

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $request->content,
                'sender_id' => $adminId,
                'sender_type' => 'admin',
                'sender_name' => $senderName,
                'group_chat_id' => $groupId,
                'created_at' => $message->created_at->toISOString(), // ✅ FIXED: Use toISOString()
                'attachments' => [],
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error sending group message: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to send message'], 500);
    }
}

public function sendWithAttachments(Request $request, $groupId)
{
    try {
        $request->validate([
            'content' => 'nullable|string|max:5000',
            'attachments' => 'required|array',
            'attachments.*' => 'file|max:51200',
        ]);

        $adminInfo = $this->getCurrentAdminInfo();
        $adminId = $adminInfo['id'];
        $adminType = $adminInfo['user_type'];
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $member = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $adminId)
            ->where('member_type', $adminType)
            ->first();

        if (!$member) {
            return response()->json(['error' => 'You are not a member of this group'], 403);
        }

        if ($member->archived) {
            return response()->json(['error' => 'This channel is archived'], 403);
        }

        $message = GroupMessage::create([
            'group_chat_id' => $groupId,
            'sender_id' => $adminId,
            'sender_type' => 'admin',
            'content' => $this->crypto->encrypt($request->content ?? ''),
            'is_read' => false,
        ]);

        $attachments = [];

        if ($request->hasFile('attachments')) {
            $conversationId = 'group_' . $groupId;
            $senderFolder = 'admin_' . $adminId;

            foreach ($request->file('attachments') as $file) {
                $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
                $fileName = $safeName . '-' . Str::random(12) . '.' . $extension;
                
                $directory = 'convo/' . $conversationId . '/' . $senderFolder;
                $fullPath = $directory . '/' . $fileName;
                
                Storage::disk('supabase_private_messages')->putFileAs($directory, $file, $fileName, 'private');
                
                $mimeType = $file->getMimeType();
                $attachmentType = str_starts_with($mimeType, 'image/') ? 'image' 
                                : (str_starts_with($mimeType, 'video/') ? 'video' : 'document');
                
                $attachment = GroupMessagesAttachment::create([
                    'group_message_id' => $message->id,
                    'attachment_type' => $attachmentType,
                    'attachment_path' => $fullPath,
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                ]);
                
                $signedUrl = $this->getSecureAttachmentUrl($attachment);
                
                $attachments[] = [
                    'id' => $attachment->id,
                    'type' => $attachmentType,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'url' => $signedUrl,
                ];
            }
        }

        $admin = Admin::find($adminId);
        $senderName = $admin ? trim($admin->admin_first_name . ' ' . $admin->admin_last_name) : 'Admin';

        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $request->content ?? '',
                'sender_id' => $adminId,
                'sender_type' => 'admin',
                'sender_name' => $senderName,
                'group_chat_id' => $groupId,
                'created_at' => $message->created_at->toISOString(), // ✅ FIXED: Use toISOString()
                'attachments' => $attachments,
            ]
        ]);

    } catch (\Exception $e) {
        Log::error('Error sending group message with attachments: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to send message with attachments'], 500);
    }
}

    // ============================================
    // UPDATE GROUP
    // ============================================
    public function updateGroup(Request $request, $groupId)
    {
        try {
            $request->validate([
                'name' => 'nullable|string|max:255',
                'avatar' => 'nullable|image|max:2048',
            ]);

            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $group = GroupChat::find($groupId);
            if (!$group) {
                return response()->json(['error' => 'Group not found'], 404);
            }

            $isGroupAdmin = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->where('member_type', 'admin')
                ->where('role', 'admin')
                ->exists();

            if (!$isGroupAdmin) {
                return response()->json(['error' => 'Only group admins can update group info'], 403);
            }

            if ($request->has('name')) {
                $group->name = $request->name;
            }

            if ($request->hasFile('avatar')) {
                if ($group->avatar_url) {
                    try {
                        Storage::disk('supabase_private_messages')->delete($group->avatar_url);
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete old avatar: ' . $e->getMessage());
                    }
                }
                
                $file = $request->file('avatar');
                $extension = $file->getClientOriginalExtension();
                $fileName = 'group_avatar/' . $group->id . '_' . Str::random(12) . '.' . $extension;
                
                // ✅ Store as private
                Storage::disk('supabase_private_messages')->put($fileName, file_get_contents($file), 'private');
                $group->avatar_url = $fileName;
            }

            $group->save();

            // Return the signed URL
            $avatarUrl = $group->avatar_url ? $this->getGroupAvatarUrl($group->avatar_url) : null;

            return response()->json([
                'success' => true,
                'message' => 'Channel updated successfully',
                'group' => [
                    'id' => (int)$group->id,
                    'name' => $group->name,
                    'avatar' => $avatarUrl, // ✅ Returns signed URL
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update channel'], 500);
        }
    }

    // ============================================
    // LEAVE GROUP
    // ============================================
    public function leaveGroup($groupId)
    {
        try {
            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $group = GroupChat::find($groupId);
            if (!$group) {
                return response()->json(['error' => 'Group not found'], 404);
            }

            // Check if this admin is the only admin
            $adminCount = GroupChatMember::where('group_chat_id', $groupId)
                ->where('role', 'admin') // Just check their group role
                ->count();

            $isCreator = ($group->created_by == $adminId);

            if ($isCreator && $adminCount <= 1) {
                return response()->json([
                    'error' => 'You are the only admin. Please promote another member to admin before leaving.'
                ], 403);
            }

            $deleted = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->where('member_type', 'admin')
                ->delete();

            if (!$deleted) {
                return response()->json(['error' => 'You are not a member of this group'], 404);
            }

            Log::info("Admin {$adminId} left group chat {$groupId}");

            return response()->json([
                'success' => true,
                'message' => 'You have left the channel',
            ]);

        } catch (\Exception $e) {
            Log::error('Error leaving group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to leave channel'], 500);
        }
    }

    // ============================================
    // ARCHIVE/UNARCHIVE GROUP
    // ============================================
    public function toggleArchive(Request $request, $groupId)
    {
        try {
            $request->validate([
                'archived' => 'required|boolean',
            ]);

            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $member = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->first();

            if (!$member) {
                return response()->json(['error' => 'You are not a member of this group'], 403);
            }

            $member->archived = $request->archived;
            $member->save();

            return response()->json([
                'success' => true,
                'archived' => $request->archived,
                'message' => $request->archived ? 'Channel archived successfully' : 'Channel unarchived successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling archive: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to archive channel'], 500);
        }
    }

    // ============================================
    // MUTE/UNMUTE GROUP
    // ============================================
    public function toggleMute(Request $request, $groupId)
    {
        try {
            $request->validate([
                'muted' => 'required|boolean',
            ]);

            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $member = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->first();

            if (!$member) {
                return response()->json(['error' => 'You are not a member of this group'], 403);
            }

            $member->muted = $request->muted;
            $member->save();

            return response()->json([
                'success' => true,
                'muted' => $request->muted,
                'message' => $request->muted ? 'Channel muted successfully' : 'Channel unmuted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling mute: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to mute channel'], 500);
        }
    }

    // ============================================
    // DELETE GROUP
    // ============================================
    public function deleteGroup($groupId)
    {
        try {
            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            $group = GroupChat::find($groupId);
            if (!$group) {
                return response()->json(['error' => 'Group not found'], 404);
            }

            $isGroupAdmin = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->where('member_type', 'admin')
                ->where('role', 'admin')
                ->exists();

            if (!$isGroupAdmin) {
                return response()->json(['error' => 'Only group admins can delete the channel'], 403);
            }

            $group->delete();

            GroupMessage::where('group_chat_id', $groupId)
                ->update([
                    'deleted_by' => \DB::raw("array_append(COALESCE(deleted_by, '{}'), {$adminId})")
                ]);

            Log::info("Admin {$adminId} deleted group chat {$groupId}");

            return response()->json([
                'success' => true,
                'message' => 'Channel deleted successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting group: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete channel'], 500);
        }
    }

    public function searchAlumniForGroup(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }

            $searchTerm = '%' . $query . '%';

            // Search System Admins
            $admins = Admin::where(function($q) use ($searchTerm) {
                    $q->where('admin_first_name', 'ILIKE', $searchTerm)
                    ->orWhere('admin_last_name', 'ILIKE', $searchTerm)
                    ->orWhere('admin_email', 'ILIKE', $searchTerm)
                    ->orWhere('admin_role', 'ILIKE', $searchTerm)
                    ->orWhereRaw("CONCAT(admin_first_name, ' ', admin_last_name) ILIKE ?", [$searchTerm]);
                })
                ->where('account_status', 1)
                ->limit(10)
                ->get()
                ->map(function ($admin) {
                    $fullName = trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? ''));
                    $initials = strtoupper(
                        substr($admin->admin_first_name ?? 'A', 0, 1) . 
                        substr($admin->admin_last_name ?? 'A', 0, 1)
                    );
                    
                    $avatar = null;
                    if ($admin->photo) {
                        if (filter_var($admin->photo, FILTER_VALIDATE_URL)) {
                            $avatar = $admin->photo;
                        } else {
                            $bucket = 'luminus_assets';
                            $baseUrl = env('SUPABASE_URL') . '/storage/v1/object/public/' . $bucket;
                            $path = ltrim($admin->photo, '/');
                            if (!str_starts_with($path, 'admin_photos/')) {
                                $path = 'admin_photos/' . $path;
                            }
                            $avatar = $baseUrl . '/' . $path;
                        }
                    }
                    
                    $adminRole = $admin->admin_role ?? 'Admin';

                    return [
                        'id' => $admin->id,
                        'user_type' => 'admin',  // System admin account type
                        'full_name' => $fullName ?: 'Unknown Admin',
                        'initials' => $initials ?: 'AD',
                        'program' => $adminRole,
                        'batch' => '-',
                        'student_id' => 'N/A',
                        'is_online' => true,
                        'avatar' => $avatar,
                        'admin_role' => $adminRole,
                        'is_system_admin' => true,  // ✅ Flag to identify system admins
                    ];
                });

            // Search Regular Alumni
            $alumni = Alumni::where(function($q) use ($searchTerm) {
                    $q->where('first_name', 'ILIKE', $searchTerm)
                    ->orWhere('last_name', 'ILIKE', $searchTerm)
                    ->orWhere('middle_name', 'ILIKE', $searchTerm)
                    ->orWhere('student_id_hash', hash('sha256', trim($query)))
                    ->when(filter_var($query, FILTER_VALIDATE_EMAIL), function ($q) use ($query) {
                        $q->orWhere('email_hash', hash('sha256', strtolower(trim($query))));
                    })
                    ->orWhere('program', 'ILIKE', $searchTerm)
                    ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", [$searchTerm]);
                })
                ->whereNull('deleted_at')
                ->where('account_status', 1)
                ->limit(20)
                ->get()
                ->map(function ($alumni) {
                    $fullName = trim(($alumni->first_name ?? '') . ' ' . ($alumni->last_name ?? ''));
                    $initials = strtoupper(substr($alumni->first_name ?? 'A', 0, 1) . substr($alumni->last_name ?? 'A', 0, 1));
                    
                    $avatar = null;
                    $photo = $alumni->alumni_photo ?? null;
                    if ($photo) {
                        if (filter_var($photo, FILTER_VALIDATE_URL)) {
                            $avatar = $photo;
                        } else {
                            $avatar = asset('storage/' . ltrim($photo, '/'));
                        }
                    }
                    
                    $batch = $alumni->year_graduated 
                        ? date('Y', strtotime($alumni->year_graduated)) 
                        : 'N/A';
                    
                    return [
                        'id' => $alumni->id,
                        'user_type' => 'alumni',  // Regular alumni account type
                        'full_name' => $fullName ?: 'Unknown Alumni',
                        'initials' => $initials ?: 'AL',
                        'program' => $alumni->program ?? 'N/A',
                        'batch' => $batch,
                        'student_id' => $alumni->student_id_number ?? 'N/A',
                        'is_online' => $alumni->is_online ?? false,
                        'avatar' => $avatar,
                        'is_system_admin' => false,  // ✅ Flag to identify regular alumni
                    ];
                });

            $results = array_merge($alumni->toArray(), $admins->toArray());
            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Error searching users for group: ' . $e->getMessage());
            return response()->json(['error' => 'Search failed'], 500);
        }
    }

    // ============================================
    // HELPER FUNCTIONS
    // ============================================

private function getAdminId()
{
    if (session()->has('admin_id')) {
        return session('admin_id');
    }
    
    if (session()->has('admin_logged_in') && session()->has('admin_data')) {
        $adminData = session('admin_data');
        return $adminData['id'] ?? $adminData->id ?? null;
    }
    
    return null;
}

private function getCurrentAdminInfo()
{
    $adminId = $this->getAdminId();
    if (!$adminId) return null;
    
    $admin = Admin::find($adminId);
    if ($admin) {
        return [
            'id' => $admin->id,
            'user_type' => 'admin',
            'name' => trim($admin->admin_first_name . ' ' . $admin->admin_last_name),
        ];
    }
    
    // Fallback: check if it's an alumni
    $alumni = Alumni::find($adminId);
    if ($alumni) {
        return [
            'id' => $alumni->id,
            'user_type' => 'alumni',
            'name' => $this->getAlumniFullName($alumni),
        ];
    }
    
    return null;
}

    private function getAlumniFullName($alumni)
    {
        $middleName = $alumni->middle_name 
            ? ' ' . strtoupper(substr($alumni->middle_name, 0, 1)) . '. ' 
            : ' ';
        return trim($alumni->first_name . $middleName . $alumni->last_name);
    }

    private function getAlumniInitials($alumni)
    {
        return strtoupper(
            substr($alumni->first_name ?? 'A', 0, 1) . 
            substr($alumni->last_name ?? 'A', 0, 1)
        );
    }

    private function getInitials($name)
    {
        $words = explode(' ', $name);
        $initials = '';
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }
        return substr($initials, 0, 2);
    }

private function resolveAdminPhotoUrl($path)
{
    if (!$path) return null;
    if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
    
    // Use the admin assets bucket (public)
    $bucket = env('SUPABASE_BUCKET', 'luminus_assets');
    $baseUrl = env('SUPABASE_URL') . '/storage/v1/object/public/' . $bucket;
    
    $path = ltrim($path, '/');
    
    // If the path doesn't start with 'admin_photos/', add it
    if (!str_starts_with($path, 'admin_photos/')) {
        $path = 'admin_photos/' . $path;
    }
    
    return $baseUrl . '/' . $path;
}

/**
 * Resolve alumni photo URL - use public URL (bucket is public)
 */
private function resolveAlumniPhotoUrl($path)
{
    if (!$path) return null;
    if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
    
    // ✅ Use the admin assets bucket (public) for alumni photos too
    $bucket = env('SUPABASE_BUCKET', 'luminus_assets');
    $baseUrl = env('SUPABASE_URL') . '/storage/v1/object/public/' . $bucket;
    
    // Clean up the path
    $path = ltrim($path, '/');
    
    // If the path doesn't start with 'alumni_photos/', add it
    if (!str_starts_with($path, 'alumni_photos/')) {
        $path = 'alumni_photos/' . $path;
    }
    
    return $baseUrl . '/' . $path;
}

    private function resolveAvatarUrl($path)
    {
        if (!$path) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        
        $bucket = env('SUPABASE_MESSAGES_BUCKET', 'luminus_messages_attachments');
        $baseUrl = env('SUPABASE_URL') . '/storage/v1/object/public/' . $bucket;
        return $baseUrl . '/' . ltrim($path, '/');
    }

/**
 * Get a signed URL for message attachments from private Supabase storage
 */
private function getSecureAttachmentUrl($attachment)
{
    try {
        return Storage::disk('supabase_private_messages')->temporaryUrl(
            $attachment->attachment_path,
            now()->addMinutes(15)
        );
    } catch (\Exception $e) {
        Log::error('Failed to generate signed URL for group attachment: ' . $e->getMessage());
        return null;
    }
}

    private function getSupabaseStorageUrl($path)
    {
        if (!$path) return null;
        if (filter_var($path, FILTER_VALIDATE_URL)) return $path;
        
        // ✅ Use the messages bucket for group avatars
        $bucket = env('SUPABASE_MESSAGES_BUCKET', 'luminus_messages_attachments');
        $baseUrl = env('SUPABASE_URL') . '/storage/v1/object/public/' . $bucket;
        
        // Clean the path - remove leading slash
        $path = ltrim($path, '/');
        
        // Debug: Log what we're getting
        Log::info('getSupabaseStorageUrl: Original path = ' . $path);
        
        // If the path doesn't start with 'group_avatar/', add it
        if (!str_starts_with($path, 'group_avatar/') && !str_starts_with($path, 'convo/')) {
            // If it's a numeric ID like "10_34Ts6duQaUy9.jpg", it's a group avatar
            if (preg_match('/^\d+_/', $path)) {
                $path = 'group_avatar/' . $path;
            }
        }
        
        $finalUrl = $baseUrl . '/' . $path;
        Log::info('getSupabaseStorageUrl: Final URL = ' . $finalUrl);
        
        return $finalUrl;
    }

    /**
 * Get a signed URL for a group avatar from private Supabase storage
 */
private function getGroupAvatarUrl($path)
{
    if (!$path) return null;
    
    // If it's already a full URL, return it
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }
    
    try {
        // Clean the path
        $path = ltrim($path, '/');
        
        // Ensure the path is in the correct format
        if (!str_starts_with($path, 'group_avatar/') && !str_starts_with($path, 'convo/')) {
            if (preg_match('/^\d+_/', $path)) {
                $path = 'group_avatar/' . $path;
            }
        }
        
        // ✅ Generate a signed URL (valid for 1 hour) for private bucket
        $signedUrl = Storage::disk('supabase_private_messages')->temporaryUrl(
            $path,
            now()->addHour()
        );
        
        Log::info('Group avatar signed URL generated: ' . $signedUrl);
        return $signedUrl;
        
    } catch (\Exception $e) {
        Log::error('Failed to generate signed URL for group avatar: ' . $e->getMessage() . ' - Path: ' . $path);
        return null;
    }
}

/**
 * Mark all group messages as read up to now for the current admin.
 * POST /admin/messages/groups/{groupId}/mark-read
 */
public function markGroupAsRead($groupId)
{
    try {
        $adminInfo = $this->getCurrentAdminInfo();
        if (!$adminInfo) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $adminId   = $adminInfo['id'];
        $adminType = $adminInfo['user_type'];

        $member = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $adminId)
            ->where('member_type', $adminType)
            ->first();

        if (!$member) {
            return response()->json(['error' => 'Not a member'], 403);
        }

        $member->last_read_at = now();
        $member->save();

        return response()->json(['success' => true]);
    } catch (\Exception $e) {
        Log::error('markGroupAsRead error: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to mark as read'], 500);
    }
}

    /**
     * Get group settings for a specific admin member
     */
    public function getGroupSettings($groupId)
    {
        try {
            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
            
            $member = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->first();
            
            if (!$member) {
                return response()->json(['error' => 'You are not a member of this group'], 403);
            }
            
            return response()->json([
                'is_archived' => (bool) $member->archived,
                'is_muted' => (bool) $member->muted,
                'role' => $member->role,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting group settings: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to get settings'], 500);
        }
    }

public function addMembers(Request $request, $groupId)
{
    try {
        $request->validate([
            'member_ids' => 'required|array',
            'member_ids.*' => 'integer',
        ]);

        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check if admin is a group admin
        $isGroupAdmin = GroupChatMember::where('group_chat_id', $groupId)
            ->where('alumni_id', $adminId)
            ->where('member_type', 'admin')
            ->where('role', 'admin')
            ->exists();

        if (!$isGroupAdmin) {
            return response()->json(['error' => 'Only group admins can add members'], 403);
        }

        $added = 0;
        $skipped = 0;

        foreach ($request->member_ids as $memberId) {
            // ✅ Determine member type correctly
            $memberType = 'alumni';
            $role = 'alumni';
            
            // Check if this is a system admin
            if (Admin::find($memberId)) {
                $memberType = 'admin';
                $role = 'admin';
            }

            // ✅ Check if already a member using compound key
            $exists = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $memberId)
                ->where('member_type', $memberType)
                ->exists();

            if ($exists) {
                $skipped++;
                continue;
            }

            GroupChatMember::create([
                'group_chat_id' => $groupId,
                'alumni_id' => $memberId,
                'member_type' => $memberType,
                'role' => $role,
                'archived' => false,
                'muted' => false,
            ]);

            $added++;
            Log::info("Added member {$memberId} with member_type: {$memberType}");
        }

        return response()->json([
            'success' => true,
            'message' => "Added {$added} members" . ($skipped > 0 ? " (skipped {$skipped} already in group)" : ""),
            'added' => $added,
            'skipped' => $skipped,
        ]);

    } catch (\Exception $e) {
        Log::error('Error adding members: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to add members'], 500);
    }
}

    /**
     * Remove a member from a group
     */
    public function removeMember($groupId, $memberId)
    {
        try {
            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Check if admin is a group admin
            $isGroupAdmin = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->where('member_type', 'admin')
                ->where('role', 'admin')
                ->exists();

            if (!$isGroupAdmin) {
                return response()->json(['error' => 'Only group admins can remove members'], 403);
            }

            // Don't allow removing the creator
            $group = GroupChat::find($groupId);
            if ($group && $group->created_by == $memberId) {
                return response()->json(['error' => 'Cannot remove the group creator'], 403);
            }

            // Don't allow removing self
            if ($adminId == $memberId) {
                return response()->json(['error' => 'Use the "Leave Group" option to remove yourself'], 403);
            }

            $deleted = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $memberId)
                ->delete();

            if (!$deleted) {
                return response()->json(['error' => 'Member not found'], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Member removed successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing member: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to remove member'], 500);
        }
    }

    /**
     * Update a member's role (promote/demote)
     */
    public function updateMemberRole(Request $request, $groupId, $memberId)
    {
        try {
            $request->validate([
                'role' => 'required|in:admin,alumni',
            ]);

            $adminId = $this->getAdminId();
            
            if (!$adminId) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Check if admin is a group admin
            $isGroupAdmin = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $adminId)
                ->where('member_type', 'admin')
                ->where('role', 'admin')
                ->exists();

            if (!$isGroupAdmin) {
                return response()->json(['error' => 'Only group admins can update roles'], 403);
            }

            // Don't allow changing the creator's role
            $group = GroupChat::find($groupId);
            if ($group && $group->created_by == $memberId) {
                return response()->json(['error' => 'Cannot change the group creator\'s role'], 403);
            }

            $member = GroupChatMember::where('group_chat_id', $groupId)
                ->where('alumni_id', $memberId)
                ->first();

            if (!$member) {
                return response()->json(['error' => 'Member not found'], 404);
            }

            $oldRole = $member->role;
            $member->role = $request->role;
            $member->save();

            return response()->json([
                'success' => true,
                'message' => "Member role updated from {$oldRole} to {$request->role}",
                'role' => $request->role,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating member role: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update role'], 500);
        }
    }

    /**
 * Get all attachments for a specific group message
 */
public function getGroupMessageAttachments($messageId, Request $request)
{
    try {
        $adminId = $this->getAdminId();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $message = GroupMessage::find($messageId);
        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }
        
        // Check if admin is a member of this group
        $isMember = GroupChatMember::where('group_chat_id', $message->group_chat_id)
            ->where('alumni_id', $adminId)
            ->exists();
        
        if (!$isMember) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        $attachments = GroupMessagesAttachment::where('group_message_id', $messageId)->get();
        $result = [];
        
        foreach ($attachments as $attachment) {
            $result[] = [
                'id' => $attachment->id,
                'attachment_type' => $attachment->attachment_type,
                'file_name' => $attachment->file_name ?? pathinfo($attachment->attachment_path, PATHINFO_BASENAME),
                'file_size' => $attachment->file_size,
                'url' => $this->getSecureAttachmentUrl($attachment),
            ];
        }
        
        return response()->json($result);
    } catch (\Exception $e) {
        Log::error('Error fetching group attachments: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch attachments'], 500);
    }
}

/**
 * Build a preview string from a group message's content + attachments.
 */
private function buildGroupMessagePreview(?string $content, $attachments = null): string
{
    $text = trim((string) $content);
    if ($text !== '') return $text;

    if (!$attachments || $attachments->count() === 0) return '';

    $first = $attachments->first();
    $count = $attachments->count();
    $type  = strtolower($first->attachment_type ?? 'document');
    $ext   = strtolower(pathinfo($first->file_name ?? $first->attachment_path ?? '', PATHINFO_EXTENSION));

    if ($count > 1) {
        if ($type === 'image') return "📷 {$count} photos";
        if ($type === 'video') return "🎥 {$count} videos";
        return "📎 {$count} files";
    }

    if ($type === 'image') return '📷 Photo';
    if ($type === 'video') return '🎥 Video';
    if ($type === 'pdf' || $ext === 'pdf') return '📄 PDF';

    switch ($ext) {
        case 'zip':
        case 'rar': return '🗜️ Archive';
        case 'doc':
        case 'docx': return '📝 Document';
        case 'xls':
        case 'xlsx':
        case 'csv': return '📊 Spreadsheet';
        case 'ppt':
        case 'pptx': return '📽️ Presentation';
    }

    return '📎 File';
}

}

// This is the GroupChatController.php file