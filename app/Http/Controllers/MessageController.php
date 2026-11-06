<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Message;
use App\Models\Alumni;
use App\Models\Admin;
use App\Models\MessagesAttachment;
use App\Models\DmSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema; 
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MessageController extends Controller
{

    public function __construct(
        private \App\Services\MessageCryptoService $crypto
    ) {}

    public function index()
    {
        $admin = $this->getAuthenticatedAdmin();
        
        if (!$admin) {
            return redirect()->route('admin.login');
        }
        
        return view('admin_messages', compact('admin'));
    }

public function getConversations(Request $request)
{
    $showArchived = $request->get('archived', '0') === '1';
    try {
        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Get all unique contacts from messages
        $sentContacts = Message::where('sender_id', $adminId)
            ->where(function($query) use ($adminId) {
                $query->whereRaw('NOT (deleted_by @> ARRAY[?]::bigint[])', [$adminId]);
            })
            ->select('receiver_id as contact_id', 'receiver_type as contact_type')
            ->distinct()
            ->get();
            
        $receivedContacts = Message::where('receiver_id', $adminId)
            ->where(function($query) use ($adminId) {
                $query->whereRaw('NOT (deleted_by @> ARRAY[?]::bigint[])', [$adminId]);
            })
            ->select('sender_id as contact_id', 'sender_type as contact_type')
            ->distinct()
            ->get();
        
        $allContacts = collect();
        $seenContacts = [];
        
        foreach ($sentContacts as $contact) {
            $contactType = is_string($contact->contact_type) ? $contact->contact_type : 'alumni';
            $key = $contactType . '_' . $contact->contact_id;
            if (!in_array($key, $seenContacts)) {
                $seenContacts[] = $key;
                $allContacts->push([
                    'contact_id' => $contact->contact_id,
                    'contact_type' => $contactType
                ]);
            }
        }
        
        foreach ($receivedContacts as $contact) {
            $contactType = is_string($contact->contact_type) ? $contact->contact_type : 'alumni';
            $key = $contactType . '_' . $contact->contact_id;
            if (!in_array($key, $seenContacts)) {
                $seenContacts[] = $key;
                $allContacts->push([
                    'contact_id' => $contact->contact_id,
                    'contact_type' => $contactType
                ]);
            }
        }
        
        $contacts = collect();
        
        foreach ($allContacts as $contact) {
            $contactId = $contact['contact_id'];
            $contactType = $contact['contact_type'];
            
            // ✅ SKIP: Don't show the admin's own account
            if ($contactType === 'admin' && $contactId == $adminId) {
                continue;
            }
            
            // ✅ SKIP: Only show contacts that have at least one message
            $hasMessages = Message::where(function($query) use ($adminId, $contactId, $contactType) {
                    $query->where('sender_id', $adminId)
                        ->where('sender_type', 'admin')
                        ->where('receiver_id', $contactId)
                        ->where('receiver_type', $contactType);
                })
                ->orWhere(function($query) use ($adminId, $contactId, $contactType) {
                    $query->where('sender_id', $contactId)
                        ->where('sender_type', $contactType)
                        ->where('receiver_id', $adminId)
                        ->where('receiver_type', 'admin');
                })
                ->exists();
            
            if (!$hasMessages) {
                continue; // Skip contacts with no messages
            }

            // Check settings for ALL contact types
            $setting = DmSetting::where('user_id', $adminId)
                ->where('contact_id', $contactId)
                ->where('contact_type', $contactType)
                ->first();
            
            // Skip hidden chats
            if ($setting && $setting->is_hidden) {
                continue;
            }
            
            // Filter by archive status
            $isArchived = $setting->is_archived ?? false;
            if ($showArchived && !$isArchived) {
                continue;
            }
            if (!$showArchived && $isArchived) {
                continue;
            }
            
            $userData = null;
            
            if ($contactType === 'alumni') {
                $userData = Alumni::find($contactId);
            } elseif ($contactType === 'admin') {
                $userData = Admin::find($contactId);
            }
            
            if ($userData) {
                $contactData = $this->buildContactData($userData, $contactType, $adminId);
                if ($contactData) {
                    // REUSE the $setting we already fetched
                    $contactData['is_archived'] = $setting->is_archived ?? false;
                    $contactData['is_muted'] = $setting->is_muted ?? false;
                    
                    $contacts->push($contactData);
                }
            }
        }
        
        $contacts = $contacts->sortByDesc(function ($contact) {
            return $contact['last_message_timestamp'] ?? 0;
        })->values();
        
        return response()->json($contacts);
        
    } catch (\Exception $e) {
        Log::error('Error in getConversations: ' . $e->getMessage());
        
        return response()->json([
            'error' => 'Failed to load conversations',
            'message' => $e->getMessage()
        ], 500);
    }
}

    private function buildContactData($user, $type, $adminId)
    {
        try {
            $type = is_string($type) ? $type : 'alumni';
            
            // Get last message with sender info
            $lastMessage = Message::where(function($query) use ($adminId, $user, $type) {
                    $query->where('sender_id', $adminId)
                        ->where('sender_type', 'admin')
                        ->where('receiver_id', $user->id)
                        ->where('receiver_type', $type);
                })
                ->orWhere(function($query) use ($adminId, $user, $type) {
                    $query->where('sender_id', $user->id)
                        ->where('sender_type', $type)
                        ->where('receiver_id', $adminId)
                        ->where('receiver_type', 'admin');
                })
                ->latest()
                ->first();
            
            // Get unread count
            $unreadCount = Message::where('sender_id', $user->id)
                ->where('sender_type', $type)
                ->where('receiver_id', $adminId)
                ->where('receiver_type', 'admin')
                ->where('is_read', false)
                ->count();
            
            // Build user info
            if ($type === 'admin') {
                $firstName = $user->admin_first_name ?? '';
                $lastName = $user->admin_last_name ?? '';
                $fullName = trim($firstName . ' ' . $lastName);
                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                $photo = $user->photo ?? null;
                $program = 'Admin Staff';
                $batch = '-';
                $isOnline = true;
                $adminRole = strtoupper($user->admin_role ?? 'ADMIN');
            } else {
                $firstName = $user->first_name ?? '';
                $lastName = $user->last_name ?? '';
                $fullName = trim($firstName . ' ' . $lastName);
                $initials = strtoupper(substr($firstName, 0, 1) . substr($lastName, 0, 1));
                $photo = $user->alumni_photo ?? null;
                $program = $user->program ?? 'N/A';
                $batch = $user->year_graduated ? date('Y', strtotime($user->year_graduated)) : 'N/A';
                $isOnline = $user->is_online ?? false;
                $adminRole = null;
            }
            
            // Resolve avatar URL
            $avatar = null;
            if ($photo) {
                if ($type === 'admin') {
                    $avatar = $this->resolveAdminPhotoUrl($photo);
                } else {
                    // ✅ Use resolveAlumniPhotoUrl for alumni photos
                    $avatar = $this->resolveAlumniPhotoUrl($photo);
                }
            }
            
            // Decrypt last message content
            $lastMessageContent = null;
            $lastMessageFromMe = false;
            // 🔧 FIX: Return ISO 8601 string, NOT formatted time
            $lastMessageTimestamp = null;
            
            if ($lastMessage) {
                $decryptedContent = $this->crypto->decrypt($lastMessage->content);

                // ⬇️ NEW: fetch attachments for THIS message
                $lastMessageAttachments = MessagesAttachment::where('message_id', $lastMessage->id)->get();

                // ⬇️ NEW: build preview text (falls back to "📷 Photo" etc.)
                $lastMessageContent = $this->buildMessagePreview($decryptedContent, $lastMessageAttachments);

                $lastMessageFromMe = ($lastMessage->sender_id == $adminId && $lastMessage->sender_type === 'admin');
                $lastMessageTimestamp = $lastMessage->created_at->toISOString();
            }
            
            return [
                'id' => (int)$user->id,
                'type' => $type,
                'full_name' => $fullName ?: 'Unknown',
                'initials' => $initials ?: '??',
                'program' => $program,
                'batch' => $batch,
                'is_online' => $isOnline,
                'last_message' => $lastMessageContent,
                // 🔧 FIX: Don't send formatted time - let JavaScript format it
                'last_message_time' => null, // Will be formatted by JavaScript
                'last_message_timestamp' => $lastMessageTimestamp, // ISO string for JS
                'last_message_from_me' => $lastMessageFromMe,
                'unread_count' => $unreadCount,
                'avatar' => $avatar,
                'admin_role' => $adminRole,
            ];
        } catch (\Exception $e) {
            Log::error('Error building contact data: ' . $e->getMessage());
            return null;
        }
    }

public function getMessages($type, $id, Request $request)
{
    try {
        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validate the type
        if (!in_array($type, ['alumni', 'admin'])) {
            return response()->json(['error' => 'Invalid contact type'], 400);
        }

        $contactId = (int) $id;

        // ✅ Get pagination parameters
        $limit = (int) $request->get('limit', 50);
        $offset = (int) $request->get('offset', 0);

        // ✅ Get total count first
        $totalCount = Message::where(function($query) use ($adminId, $contactId, $type) {
                $query->where('sender_id', $adminId)
                    ->where('sender_type', 'admin')
                    ->where('receiver_id', $contactId)
                    ->where('receiver_type', $type);
            })
            ->orWhere(function($query) use ($adminId, $contactId, $type) {
                $query->where('sender_id', $contactId)
                    ->where('sender_type', $type)
                    ->where('receiver_id', $adminId)
                    ->where('receiver_type', 'admin');
            })
            ->where(function($query) use ($adminId) {
                $query->whereNull('deleted_by')
                    ->orWhereRaw('NOT (deleted_by @> ARRAY[?]::bigint[])', [$adminId]);
            })
            ->count();

        // ✅ Get messages with pagination
        $messages = Message::where(function($query) use ($adminId, $contactId, $type) {
                $query->where('sender_id', $adminId)
                    ->where('sender_type', 'admin')
                    ->where('receiver_id', $contactId)
                    ->where('receiver_type', $type);
            })
            ->orWhere(function($query) use ($adminId, $contactId, $type) {
                $query->where('sender_id', $contactId)
                    ->where('sender_type', $type)
                    ->where('receiver_id', $adminId)
                    ->where('receiver_type', 'admin');
            })
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
            ->map(function ($message) {
                $decryptedContent = $this->crypto->decrypt($message->content);
                
                $attachmentsData = [];
                $attachments = MessagesAttachment::where('message_id', $message->id)->get();
                
                foreach ($attachments as $attachment) {
                    $attachmentsData[] = [
                        'id' => $attachment->id,
                        'type' => $attachment->attachment_type,
                        'name' => pathinfo($attachment->attachment_path, PATHINFO_BASENAME),
                        'size' => null,
                        'url' => $this->getSecureAttachmentUrl($attachment),
                    ];
                }
                
                return [
                    'id' => $message->id,
                    'content' => $decryptedContent,
                    'sender_id' => $message->sender_id,
                    'sender_type' => $message->sender_type,
                    'receiver_id' => $message->receiver_id,
                    'receiver_type' => $message->receiver_type,
                    'is_read' => $message->is_read,
                    'created_at' => $message->created_at->format('Y-m-d H:i:s'),
                    'attachments' => $attachmentsData,
                ];
            });

        Log::info("✅ Loaded " . count($messages) . " messages for contact {$contactId} (type: {$type}, total: {$totalCount})");

        return response()->json([
            'messages' => $messages,
            'total' => $totalCount,
            'limit' => $limit,
            'offset' => $offset,
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error loading messages: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to load messages',
            'message' => $e->getMessage()
        ], 500);
    }
}

public function sendMessage(Request $request)
{
    $request->validate([
        'receiver_id' => 'required|integer',
        'receiver_type' => 'required|in:alumni,admin',
        'content' => 'required|string|max:5000',
    ]);
    
    $adminId = $this->getAdminId();
    
    if (!$adminId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }
    
    if ($request->receiver_type === 'alumni') {
        $exists = Alumni::where('id', $request->receiver_id)->exists();
    } else {
        $exists = Admin::where('id', $request->receiver_id)->exists();
    }
    
    if (!$exists) {
        return response()->json(['error' => 'Receiver not found'], 404);
    }
    
    $message = Message::create([
        'sender_id' => $adminId,
        'receiver_id' => $request->receiver_id,
        'sender_type' => 'admin',
        'receiver_type' => $request->receiver_type,
        'content' => $this->crypto->encrypt($request->content), // 🔐
        'is_read' => false,
    ]);
    
    // ✅ Return only the ISO 8601 UTC string, let JavaScript format it
    return response()->json([
        'success' => true,
        'message' => [
            'id' => $message->id,
            'content' => $request->content,
            'sender_id' => $adminId,
            'sender_type' => 'admin',
            'receiver_id' => $message->receiver_id,
            'receiver_type' => $message->receiver_type,
            'is_read' => false,
            'created_at' => $message->created_at->toISOString(),
        ]
    ]);
}

    public function searchAlumni(Request $request)
    {
        try {
            $query = $request->get('q', '');
            
            if (strlen($query) < 2) {
                return response()->json([]);
            }
            
            $currentAdminId = $this->getAdminId();
            
            // Log the search query for debugging
            Log::info('Search query: "' . $query . '"');
            
            // Search Alumni by first_name, last_name, student_id_number, or email
             $alumni = Alumni::where(function($q) use ($query) {
                $q->where('first_name', 'ILIKE', "%{$query}%")
                ->orWhere('last_name', 'ILIKE', "%{$query}%")
                ->orWhere('middle_name', 'ILIKE', "%{$query}%")
                 ->orWhere('program', 'ILIKE', "%{$query}%")
                 ->orWhereRaw("CONCAT(first_name, ' ', last_name) ILIKE ?", ["%{$query}%"]);
                 })
                 ->when(filter_var($query, FILTER_VALIDATE_EMAIL), function ($q) use ($query) {
                     $q->orWhere('email_hash', hash('sha256', strtolower(trim($query))));
                 })
                ->limit(15)
                ->get()
                ->map(function ($alumni) {  // ✅ Fixed: Parameter renamed to $alumni
                    $fullName = trim(
                        ($alumni->first_name ?? '') . ' ' .  // ✅ Using alumni fields
                        ($alumni->last_name ?? '')
                    );
                    $initials = strtoupper(
                        substr($alumni->first_name ?? 'A', 0, 1) .  // ✅ Using alumni fields
                        substr($alumni->last_name ?? 'A', 0, 1)
                    );
                    
                    // ✅ Handle alumni photo properly
                    $avatar = null;
                    $photo = $alumni->alumni_photo ?? null;
                    if ($photo) {
                        $avatar = $this->resolveAlumniPhotoUrl($photo);
                    }
                    
                    $batch = $alumni->year_graduated 
                        ? date('Y', strtotime($alumni->year_graduated)) 
                        : 'N/A';
                    
                    return [
                        'id' => $alumni->id,
                        'type' => 'alumni',  // ✅ Fixed: Setting correct type
                        'full_name' => $fullName ?: 'Unknown Alumni',
                        'initials' => $initials ?: 'AL',
                        'program' => $alumni->program ?? 'N/A',  // ✅ Using actual program
                        'batch' => $batch,  // ✅ Using actual batch
                        'student_id' => $alumni->student_id_number ?? 'N/A',
                        'email' => $alumni->email ?? 'N/A',  // ✅ Using alumni email
                        'is_online' => $alumni->is_online ?? false,
                        'avatar' => $avatar,
                    ];
                });
            
            // Log alumni search results
            Log::info('Alumni search results: ' . $alumni->count() . ' found');
            
            $admins = collect();

            if ($currentAdminId) {
                $admins = Admin::where('id', '!=', (int)$currentAdminId) // ✅ EXCLUDE current admin
                    ->where(function($q) use ($query) {
                        $q->where('admin_first_name', 'ILIKE', "%{$query}%")
                        ->orWhere('admin_last_name', 'ILIKE', "%{$query}%")
                        ->orWhere('admin_email', 'ILIKE', "%{$query}%")
                        ->orWhere('admin_role', 'ILIKE', "%{$query}%");
                        
                        $q->orWhereRaw("CONCAT(admin_first_name, ' ', admin_last_name) ILIKE ?", ["%{$query}%"]);
                    })
                    ->limit(10)
                    ->get()
                    ->map(function ($admin) {
                        $fullName = trim(
                            ($admin->admin_first_name ?? '') . ' ' . 
                            ($admin->admin_last_name ?? '')
                        );
                        $initials = strtoupper(
                            substr($admin->admin_first_name ?? 'A', 0, 1) . 
                            substr($admin->admin_last_name ?? 'A', 0, 1)
                        );
                        
                        $avatar = $this->resolveAdminPhotoUrl($admin->photo ?? null);
                        $adminRole = $admin->admin_role ?? 'Admin';

                        return [
                            'id' => $admin->id,
                            'type' => 'admin',
                            'full_name' => $fullName ?: 'Unknown Admin',
                            'initials' => $initials ?: 'AD',
                            'program' => $adminRole,
                            'batch' => '-',
                            'student_id' => 'N/A',
                            'email' => $admin->admin_email ?? 'N/A',
                            'is_online' => true,
                            'avatar' => $avatar,
                            'admin_role' => $adminRole,
                        ];
                    });
            }
            
            // Log admin search results
            Log::info('Admin search results: ' . $admins->count() . ' found');
            
            // Merge results (alumni first, then admins)
            $results = $alumni->merge($admins)->values();
            
            return response()->json($results);
            
        } catch (\Exception $e) {
            Log::error('Search error: ' . $e->getMessage());
            Log::error('Search error trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function debugConversations(Request $request)
    {
        try {
            $adminId = $this->getAdminId();
            
            $sampleMessage = Message::where('sender_type', 'alumni')
                ->where('content', 'like', 'enc:%')
                ->first();
            
            $testData = [
                'admin_id' => $adminId,
                'secret_key_configured' => config('services.messages.secret_key') ? 'yes' : 'no',
                'sample_original' => null,
                'sample_decrypted' => null,
                'decryption_test' => 'FAILED',
            ];
            
            if ($sampleMessage) {
                $testData['sample_original'] = $sampleMessage->content;
                $testData['sample_decrypted'] = $this->crypto->decrypt($sampleMessage->content);
                
                $testData['decryption_test'] = (
                    $testData['sample_decrypted'] !== $sampleMessage->content && 
                    substr($testData['sample_decrypted'], 0, 4) !== 'enc:'
                ) ? 'SUCCESS ✅' : 'FAILED ❌';
            }
            
            return response()->json($testData);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

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

    private function getAuthenticatedAdmin()
    {
        $adminId = $this->getAdminId();
        
        if ($adminId) {
            return Admin::find($adminId);
        }
        
        if (session()->has('admin_data')) {
            $adminData = session('admin_data');
            if (is_object($adminData)) {
                return $adminData;
            }
            if (is_array($adminData) && isset($adminData['id'])) {
                return Admin::find($adminData['id']);
            }
        }
        
        return null;
    }

    public function testEncryption(Request $request)
    {
        $plaintext = 'Hello World Test ' . now()->toIso8601String();

        $encrypted = $this->crypto->encrypt($plaintext);
        $decrypted = $this->crypto->decrypt($encrypted);

        return response()->json([
            'plaintext'   => $plaintext,
            'encrypted'   => $encrypted,
            'decrypted'   => $decrypted,
            'is_encrypted_format' => str_starts_with($encrypted, 'enc:'),
            'round_trip_ok'       => $decrypted === $plaintext,
        ]);
    }

    // Add this method to your MessageController
    public function decryptMessage(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'sender_type' => 'required|string',
            'receiver_type' => 'required|string',
        ]);
        
        $decrypted = $this->crypto->decrypt($request->content);
        
        return response()->json([
            'decrypted' => $decrypted
        ]);
    }

    public function getContactInfo($type, $id)
    {
        try {
            if (!in_array($type, ['alumni', 'admin'])) {
                return response()->json(['error' => 'Invalid contact type'], 400);
            }
            
            $adminId = $this->getAdminId();
            
            // ✅ Don't allow viewing your own contact info
            if ($type === 'admin' && $id == $adminId) {
                return response()->json(['error' => 'Cannot view your own profile'], 403);
            }
            
            if ($type === 'alumni') {
                $user = Alumni::find($id);
            } else {
                $user = Admin::find($id);
            }
            
            if (!$user) {
                return response()->json(['error' => 'Contact not found'], 404);
            }
            
            if ($type === 'alumni') {
                $middleInitial = $user->middle_name 
                    ? ' ' . strtoupper(substr($user->middle_name, 0, 1)) . '. ' 
                    : ' ';
                $fullName = trim($user->first_name . $middleInitial . $user->last_name);
                $initials = strtoupper(
                    substr($user->first_name ?? 'A', 0, 1) . 
                    substr($user->last_name ?? 'A', 0, 1)
                );
                $batch = $user->year_graduated 
                    ? date('Y', strtotime($user->year_graduated)) 
                    : 'N/A';
                $program = $user->program ?? 'N/A';
                $isOnline = $user->is_online ?? false;
                $photo = $user->alumni_photo ?? $user->card_photo ?? null;
                $adminRole = null;
            } else {
                $fullName = trim(($user->admin_first_name ?? '') . ' ' . ($user->admin_last_name ?? ''));
                $initials = strtoupper(
                    substr($user->admin_first_name ?? 'A', 0, 1) . 
                    substr($user->admin_last_name ?? 'A', 0, 1)
                );
                $batch = '-';
                $adminRole = strtoupper($user->admin_role ?? 'ADMIN');
                $program = $adminRole;
                $isOnline = true;
                $photo = $user->photo ?? null;
            }
            
            // Resolve avatar URL
            $avatar = null;
            if ($photo) {
                if ($type === 'admin') {
                    $avatar = $this->resolveAdminPhotoUrl($photo);
                } else {
                    // ✅ Use resolveAlumniPhotoUrl for alumni photos
                    $avatar = $this->resolveAlumniPhotoUrl($photo);
                }
            }
            
            return response()->json([
                'id' => (int)$user->id,
                'type' => $type,
                'full_name' => $fullName ?: 'Unknown',
                'initials' => $initials ?: '??',
                'program' => $program,
                'batch' => $batch,
                'is_online' => $isOnline,
                'avatar' => $avatar,
                'admin_role' => $adminRole, // Add admin role to response
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error fetching contact info: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to fetch contact info'], 500);
        }
    }

    public function markAsRead(Request $request)
    {
        $request->validate([
            'sender_id' => 'required|integer',
            'sender_type' => 'required|string|in:alumni,admin'
        ]);

        // 🔧 FIX: Use getAdminId() instead of auth()->guard('admin')->id()
        $adminId = $this->getAdminId();
        
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        // Mark all unread messages from this sender to this admin as read
        Message::where('sender_id', $request->sender_id)
            ->where('sender_type', $request->sender_type)
            ->where('receiver_id', $adminId)
            ->where('receiver_type', 'admin')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    public function sendWithAttachments(Request $request)
    {
        // ✅ DEBUG: Log everything
        Log::info('📤 sendWithAttachments called');
        Log::info('📤 All request data:', $request->all());
        Log::info('📤 Files:', $request->allFiles());
        Log::info('📤 Has files?', ['hasFiles' => $request->hasFile('attachments')]);

        $request->validate([
            'receiver_id' => 'required|integer',
            'receiver_type' => 'required|in:alumni,admin',
            'content' => 'nullable|string|max:5000',
            'attachments' => 'required|array',
            'attachments.*' => 'file|max:51200', // 50MB max
        ]);
        
        $adminId = $this->getAdminId();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $message = Message::create([
            'sender_id' => $adminId,
            'receiver_id' => $request->receiver_id,
            'sender_type' => 'admin',
            'receiver_type' => $request->receiver_type,
            'content' => $this->crypto->encrypt($request->content ?? ''),
            'is_read' => false,
        ]);
        
        $attachments = [];
        
        if ($request->hasFile('attachments')) {
            // Generate conversation folder name (consistent for both participants)
            $conversationId = $this->generateConversationId(
                'admin', $adminId,
                $request->receiver_type, $request->receiver_id
            );
            
            // Sender's folder within the conversation
            $senderFolder = 'admin_' . $adminId;
            
            foreach ($request->file('attachments') as $file) {
                // 1. Generate a safe, unique filename
                $extension = strtolower($file->getClientOriginalExtension() ?: 'bin');
                $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'file';
                $fileName = $safeName . '-' . Str::random(12) . '.' . $extension;
                
                // 2. Create the organized path: convo/{conversation_id}/{sender_type}_{sender_id}/filename
                $directory = 'convo/' . $conversationId . '/' . $senderFolder;
                $fullPath = $directory . '/' . $fileName;
                
                // 3. Upload to the PRIVATE Supabase bucket
                Storage::disk('supabase_private_messages')->putFileAs($directory, $file, $fileName, 'private');
                
                // 4. Determine type
                $mimeType = $file->getMimeType();
                $attachmentType = str_starts_with($mimeType, 'image/') ? 'image' 
                                : (str_starts_with($mimeType, 'video/') ? 'video' : 'document');
                
                // 5. Save to database (store ONLY the path)
                $attachment = MessagesAttachment::create([
                    'message_id' => $message->id,
                    'attachment_type' => $attachmentType,
                    'attachment_path' => $fullPath, 
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(), 
                ]);
                
                // 6. Generate signed URL for immediate frontend display
                $signedUrl = $this->getSecureAttachmentUrl($attachment);
                
                // ✅ ADD THIS LOGGING
                Log::info('📎 Attachment created:', [
                    'id' => $attachment->id,
                    'type' => $attachmentType,
                    'path' => $fullPath,
                    'signed_url' => $signedUrl,
                    'name' => $file->getClientOriginalName(),
                ]);

                $attachments[] = [
                    'id' => $attachment->id,
                    'type' => $attachmentType,
                    'name' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                    'url' => $signedUrl, 
                ];
            }
        }

        Log::info('📦 Final response attachments count: ' . count($attachments));
    Log::info('📦 Final response attachments:', $attachments);
        
        return response()->json([
            'success' => true,
            'message' => [
                'id' => $message->id,
                'content' => $request->content ?? '',
                'sender_id' => $adminId,
                'sender_type' => 'admin',
                'receiver_id' => $message->receiver_id,
                'receiver_type' => $message->receiver_type,
                'is_read' => false,
                'created_at' => $message->created_at->toISOString(),
                'attachments' => $attachments,
            ]
        ]);
    }

    /**
     * Generate a consistent conversation ID for two participants
     * Ensures admin_1_to_admin_2 and admin_2_to_admin_1 use the same folder
     */
    private function generateConversationId($type1, $id1, $type2, $id2)
    {
        // Create sortable identifiers
        $participant1 = $type1 . '_' . $id1;
        $participant2 = $type2 . '_' . $id2;
        
        // Sort alphabetically to ensure consistency
        $participants = [$participant1, $participant2];
        sort($participants);
        
        // Return combined ID
        return $participants[0] . '_' . $participants[1];
    }

    protected function resolveAdminPhotoUrl(?string $photoPath): ?string
    {
        $photoPath = trim((string) $photoPath);

        if ($photoPath === '') {
            return null;
        }

        if (preg_match('/^https?:\/\//i', $photoPath)) {
            return $photoPath;
        }

        if (str_starts_with($photoPath, '/storage/')) {
            return $photoPath;
        }

        if (str_starts_with($photoPath, 'storage/')) {
            return '/' . $photoPath;
        }

        if (str_starts_with($photoPath, '/')) {
            return $photoPath;
        }

        // ✅ FIXED: Correct bucket name is 'luminus_assets' (not 'luminous_assets')
        $bucket = 'luminus_assets';
        $baseUrl = env('SUPABASE_URL') . '/storage/v1/object/public/' . $bucket;
        $path = ltrim($photoPath, '/');
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

    
        /**
     * API Endpoint for React Native Mobile App
     * Route: GET /api/messages/attachments/{id}/url
     */
    public function getAttachmentUrl($attachmentId, Request $request)
    {
        // 1. Identify the requester (Admin via Session OR Alumni via API Token/Sanctum)
        $adminId = $this->getAdminId();
        $requesterId = $adminId;
        $requesterType = 'admin';
        
        // If not an admin, check if it's an authenticated alumni (Assuming Laravel Sanctum)
        if (!$requesterId && $request->user()) { 
            $requesterId = $request->user()->id;
            $requesterType = 'alumni';
        }

        if (!$requesterId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. Fetch the attachment and its parent message
        $attachment = MessagesAttachment::with('message')->find($attachmentId);
        
        if (!$attachment || !$attachment->message) {
            return response()->json(['error' => 'Attachment not found'], 404);
        }

        $msg = $attachment->message;

        // 3. SECURITY CHECK: Is the requester the sender or receiver?
        $isAuthorized = (
            ($msg->sender_id == $requesterId && $msg->sender_type === $requesterType) ||
            ($msg->receiver_id == $requesterId && $msg->receiver_type === $requesterType)
        );

        if (!$isAuthorized) {
            Log::warning("Unauthorized attachment access attempt by {$requesterType} ID: {$requesterId}");
            return response()->json(['error' => 'Forbidden'], 403);
        }

        // 4. Generate and return the temporary signed URL
        return response()->json([
            'success' => true,
            'url' => $this->getSecureAttachmentUrl($attachment),
        ]);
    }

public function archiveChat(Request $request)
{
    $request->validate([
        'contact_id' => 'required|integer',
        'contact_type' => 'required|in:alumni,admin',
        'archived' => 'required|boolean'
    ]);

    $adminId = $this->getAdminId();
    if (!$adminId) {
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // Log everything
    Log::info('=== ARCHIVE CHAT REQUEST ===');
    Log::info('admin_id: ' . $adminId);
    Log::info('contact_id: ' . $request->contact_id);
    Log::info('contact_type: ' . $request->contact_type);
    Log::info('archived: ' . ($request->archived ? 'true' : 'false'));

    try {
        // Try to find existing setting first
        $existing = DmSetting::where('user_id', $adminId)
            ->where('contact_id', $request->contact_id)
            ->where('contact_type', $request->contact_type)
            ->first();
        
        Log::info('Existing setting found: ' . ($existing ? 'yes (id: ' . $existing->id . ')' : 'no'));

        // Try to update or create
        Log::info('Attempting to updateOrCreate...');
        
        $setting = DmSetting::updateOrCreate(
            [
                'user_id' => $adminId,
                'contact_id' => $request->contact_id,
                'contact_type' => $request->contact_type,
            ],
            [
                'is_archived' => $request->archived
            ]
        );

        Log::info('updateOrCreate successful! Setting ID: ' . $setting->id);
        Log::info('is_archived: ' . ($setting->is_archived ? 'true' : 'false'));

        return response()->json([
            'success' => true,
            'archived' => $setting->is_archived,
            'contact_id' => $request->contact_id,
            'contact_type' => $request->contact_type,
            'message' => $request->archived ? 'Chat archived successfully' : 'Chat unarchived successfully'
        ]);
        
    } catch (\Exception $e) {
        Log::error('=== ARCHIVE CHAT EXCEPTION ===');
        Log::error('Message: ' . $e->getMessage());
        Log::error('File: ' . $e->getFile());
        Log::error('Line: ' . $e->getLine());
        Log::error('Trace: ' . $e->getTraceAsString());
        
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'line' => $e->getLine()
        ], 500);
    }
}

    public function muteChat(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|integer',
            'contact_type' => 'required|in:alumni,admin',
            'muted' => 'required|boolean'
        ]);

        $adminId = $this->getAdminId();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ✅ REMOVED the restriction - now works for both alumni and admin

        try {
            $setting = DmSetting::updateOrCreate(
                [
                    'user_id' => $adminId,
                    'contact_id' => $request->contact_id,
                    'contact_type' => $request->contact_type,
                ],
                [
                    'is_muted' => $request->muted
                ]
            );

            return response()->json([
                'success' => true,
                'muted' => $setting->is_muted,
                'contact_id' => $request->contact_id,
                'contact_type' => $request->contact_type,
                'message' => $request->muted ? 'Chat muted successfully' : 'Chat unmuted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Mute chat error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to mute chat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a chat (soft delete - hide for user)
     */
    public function deleteChat(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|integer',
            'contact_type' => 'required|in:alumni,admin',
        ]);

        $adminId = $this->getAdminId();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            // Mark messages as deleted for this admin
            $deletedCount = Message::where(function($query) use ($adminId, $request) {
                $query->where('sender_id', $adminId)
                    ->where('sender_type', 'admin')
                    ->where('receiver_id', $request->contact_id)
                    ->where('receiver_type', $request->contact_type);
            })->orWhere(function($query) use ($adminId, $request) {
                $query->where('sender_id', $request->contact_id)
                    ->where('sender_type', $request->contact_type)
                    ->where('receiver_id', $adminId)
                    ->where('receiver_type', 'admin');
            })->update([
                'deleted_by' => \DB::raw("array_append(COALESCE(deleted_by, '{}'), {$adminId})")
            ]);

            // For alumni DMs, mark as hidden in dm_settings
            if ($request->contact_type === 'alumni') {
                $setting = DmSetting::updateOrCreate(
                    [
                        'user_id' => $adminId,
                        'contact_id' => $request->contact_id,
                        'contact_type' => 'alumni',
                    ],
                    [
                        'is_hidden' => true,
                        'is_archived' => false // Remove from archive when deleted
                    ]
                );
            }

            return response()->json([
                'success' => true,
                'message' => 'Chat deleted successfully',
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            Log::error('Delete chat error: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to delete chat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get DM settings for a contact
     */
    public function getDmSettings(Request $request)
    {
        $request->validate([
            'contact_id' => 'required|integer',
            'contact_type' => 'required|in:alumni,admin,group',
        ]);

        $adminId = $this->getAdminId();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // ✅ If it's a group, get settings from group_chat_members
        if ($request->contact_type === 'group') {
            $member = \App\Models\GroupChatMember::where('group_chat_id', $request->contact_id)
                ->where('alumni_id', $adminId)
                ->first();

            return response()->json([
                'is_archived' => $member ? $member->archived : false,
                'is_muted' => $member ? $member->muted : false,
                'is_hidden' => false,
            ]);
        }

        // For alumni/admin, use dm_settings
        $setting = DmSetting::where('user_id', $adminId)
            ->where('contact_id', $request->contact_id)
            ->where('contact_type', $request->contact_type)
            ->first();

        return response()->json([
            'is_archived' => $setting->is_archived ?? false,
            'is_muted' => $setting->is_muted ?? false,
            'is_hidden' => $setting->is_hidden ?? false,
        ]);
    }

    private function getSecureAttachmentUrl($attachment)
    {
        try {
            // ✅ Log the path we're trying to access
            Log::info('🔐 Generating signed URL for attachment:', [
                'id' => $attachment->id,
                'path' => $attachment->attachment_path,
                'full_path_exists' => Storage::disk('supabase_private_messages')->exists($attachment->attachment_path)
            ]);
            
            // ✅ Check if the file exists before generating URL
            if (!Storage::disk('supabase_private_messages')->exists($attachment->attachment_path)) {
                Log::error('❌ File does not exist at path: ' . $attachment->attachment_path);
                return null;
            }
            
            $url = Storage::disk('supabase_private_messages')->temporaryUrl(
                $attachment->attachment_path,
                now()->addMinutes(15)
            );
            
            Log::info('✅ Signed URL generated successfully');
            return $url;
            
        } catch (\Exception $e) {
            Log::error('❌ Failed to generate signed URL: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
 * Get all attachments for a specific message
 */
public function getAttachmentsForMessage($messageId, Request $request)
{
    try {
        $adminId = $this->getAdminId();
        if (!$adminId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        
        $message = Message::find($messageId);
        if (!$message) {
            return response()->json(['error' => 'Message not found'], 404);
        }
        
        // Check if admin is part of this conversation
        if ($message->sender_id != $adminId && $message->receiver_id != $adminId) {
            return response()->json(['error' => 'Forbidden'], 403);
        }
        
        $attachments = MessagesAttachment::where('message_id', $messageId)->get();
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
        Log::error('Error fetching attachments: ' . $e->getMessage());
        return response()->json(['error' => 'Failed to fetch attachments'], 500);
    }
}

/**
 * Build a preview string from a message's content + attachments.
 * If there's text, return it. If it's attachment-only, describe the attachment.
 */
private function buildMessagePreview(?string $content, $attachments = null): string
{
    $text = trim((string) $content);
    if ($text !== '') {
        return $text;
    }

    // No text — check for attachments
    if (!$attachments || $attachments->count() === 0) {
        return '';
    }

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
        case 'rar':
            return '🗜️ Archive';
        case 'doc':
        case 'docx':
            return '📝 Document';
        case 'xls':
        case 'xlsx':
        case 'csv':
            return '📊 Spreadsheet';
        case 'ppt':
        case 'pptx':
            return '📽️ Presentation';
    }

    return '📎 File';
}


}

// This is the MessageController.php