<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes; 
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Alumni extends Model
{
    use HasFactory, SoftDeletes; // ← ADD SoftDeletes

    protected $table = 'alumnis_base';

    protected $fillable = [
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'sex',
        'year_graduated',
        'alumni_photo',
        'alumni_bio',
        'student_id_number',
        'student_id_hash',
        'email',
        'email_hash',
        'phone_number',
        'password_hash',
        'verification_status',
        'program',
        'card_photo',
        'needs_password_change',
        'account_status',
        'is_online',
        'push_token',
        'alumni_type',    
        'promoted_to_college_at',
        'restriction_reason',
        'restriction_comment',
        'restricted_by',
        'restricted_at',
        'last_login_at',            
        'last_invite_sent_at',       
        'invite_count',              
        'last_active_at',       
        'deleted_at', 
    ];

    protected $hidden = [
        'password_hash',
        'push_token',
    ];

    protected $casts = [
        'student_id_number' => 'encrypted',
        'date_of_birth'     => 'encrypted',
        'phone_number'      => 'encrypted',
        'email'             => 'encrypted',
    
        'year_graduated' => 'date',
        'is_online' => 'boolean',
        'needs_password_change' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'restricted_at' => 'datetime',
        'deleted_at' => 'datetime',
        'last_login_at' => 'datetime',
        'last_invite_sent_at' => 'datetime',
        'alumni_type' => 'string',
        'promoted_to_college_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::saving(function ($alumnus) {
            if ($alumnus->isDirty('student_id_number') && !empty($alumnus->student_id_number)) {
                $alumnus->student_id_hash = hash('sha256', trim($alumnus->student_id_number));
            }
            if ($alumnus->isDirty('email') && !empty($alumnus->email)) {
                $alumnus->email_hash = hash('sha256', strtolower(trim($alumnus->email)));
            }
        });
    }

    /**
     * Get the alumni's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->last_name}, {$this->first_name}" . ($this->middle_name ? " {$this->middle_name}" : '');
    }

    /**
     * Get the alumni's initials.
     */
    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
    }

    /**
     * Get the alumni's photo URL.
     */
    public function getAlumniPhotoUrlAttribute(): ?string
    {
        if ($this->alumni_photo) {
            if (filter_var($this->alumni_photo, FILTER_VALIDATE_URL)) {
                return $this->alumni_photo;
            }
            return asset('storage/' . $this->alumni_photo);
        }
        return null;
    }

    // ============================================
    // STATUS CHECK METHODS
    // ============================================

    /**
     * Check if the alumni account is active.
     */
    public function isActive(): bool
    {
        return $this->account_status == 1 && $this->deleted_at === null;
    }

    /**
     * Check if the alumni account is restricted.
     */
    public function isRestricted(): bool
    {
        return $this->account_status == 0 && $this->deleted_at === null;
    }

    /**
     * Check if the alumni account is archived (soft deleted).
     */
    public function isArchived(): bool
    {
        return $this->deleted_at !== null;
    }

    // ============================================
    // SCOPE METHODS
    // ============================================

    /**
     * Scope a query to only include active alumni.
     */
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at')->where('account_status', 1);
    }

    /**
     * Scope a query to only include restricted alumni.
     */
    public function scopeRestricted($query)
    {
        return $query->whereNull('deleted_at')->where('account_status', 0);
    }

    /**
     * Scope a query to only include archived alumni.
     */
    public function scopeArchived($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    /**
     * Scope a query to only include verified alumni.
     */
    public function scopeVerified($query): void
    {
        $query->where('verification_status', 'verified');
    }

    /**
     * Scope a query to only include online alumni.
     */
    public function scopeOnline($query): void
    {
        $query->where('is_online', true);
    }

    // ============================================
    // RESTRICTION REASONS
    // ============================================

    /**
     * Get the list of predefined restriction reasons.
     */
    public static function getRestrictionReasons(): array
    {
        return [
            'violation' => 'Violation of Community Guidelines',
            'spam' => 'Spam or Unsolicited Messages',
            'fake_account' => 'Fake or Misleading Account Information',
            'harassment' => 'Harassment or Bullying',
            'inappropriate_content' => 'Posting Inappropriate Content',
            'unauthorized_access' => 'Unauthorized Access Attempts',
            'other' => 'Other (Please Specify)',
        ];
    }

    /**
     * Get the human-readable restriction reason label.
     */
    public function getRestrictionReasonLabelAttribute(): ?string
    {
        if (!$this->restriction_reason) {
            return null;
        }
        $reasons = self::getRestrictionReasons();
        return $reasons[$this->restriction_reason] ?? $this->restriction_reason;
    }

    // ============================================
    // RELATIONSHIPS
    // ============================================

    /**
     * Get the admin who restricted this account.
     */
    public function restrictedBy()
    {
        return $this->belongsTo(Admin::class, 'restricted_by');
    }

    /**
     * Get the addresses associated with the alumni.
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    /**
     * Get the employment records associated with the alumni.
     */
    public function employments(): HasMany
    {
        return $this->hasMany(AlumniEmployment::class);
    }

    /**
     * Get the skills associated with the alumni.
     */
    public function skills(): HasMany
    {
        return $this->hasMany(AlumniSkill::class);
    }

    /**
     * Get the tracer responses associated with the alumni.
     */
    public function tracerResponses(): HasMany
    {
        return $this->hasMany(TracerResponse::class);
    }

    /**
     * Get the event registrations associated with the alumni.
     */
    public function eventRegistrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Get the followers (alumni who follow this alumni).
     */
    public function followers(): HasMany
    {
        return $this->hasMany(Follower::class, 'followed_alumni_id');
    }

    /**
     * Get the following (alumni this alumni follows).
     */
    public function following(): HasMany
    {
        return $this->hasMany(Follower::class, 'follower_alumni_id');
    }

    /**
     * Get the posts associated with the alumni.
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the messages sent by this alumni.
     */
    public function messagesSent(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id')->where('sender_type', 'alumni');
    }

    /**
     * Get the messages received by this alumni.
     */
    public function messagesReceived(): HasMany
    {
        return $this->hasMany(Message::class, 'receiver_id')->where('receiver_type', 'alumni');
    }

    /**
     * Get the comments made by this alumni.
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Get the reactions made by this alumni.
     */
    public function reactions(): HasMany
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Get the reposts made by this alumni.
     */
    public function reposts(): HasMany
    {
        return $this->hasMany(Repost::class);
    }

    /**
     * Get the group chat memberships for this alumni.
     */
    public function groupChatMembers(): HasMany
    {
        return $this->hasMany(GroupChatMember::class);
    }

    /**
     * Get the group chats this alumni is a member of.
     */
    public function groupChats(): BelongsToMany
    {
        return $this->belongsToMany(GroupChat::class, 'group_chat_members');
    }

    /**
     * Get the dismissed notifications for this alumni.
     */
    public function dismissedNotifications(): HasMany
    {
        return $this->hasMany(DismissedNotification::class);
    }

    /**
     * Get the favorite chats for this alumni.
     */
    public function favoriteChats(): HasMany
    {
        return $this->hasMany(FavoriteChat::class, 'user_id');
    }

    /**
     * Get the DM settings for this alumni.
     */
    public function dmSettings(): HasMany
    {
        return $this->hasMany(DmSetting::class, 'user_id');
    }

    // ============================================
    // HELPER METHODS
    // ============================================

    /**
     * Check if the alumni is verified.
     */
    public function isVerified(): bool
    {
        return $this->verification_status === 'verified';
    }

    /**
     * Check if the alumni is pending verification.
     */
    public function isPending(): bool
    {
        return $this->verification_status === 'pending';
    }

    /**
     * Check if the alumni is rejected.
     */
    public function isRejected(): bool
    {
        return $this->verification_status === 'rejected';
    }

    /**
     * Get the number of followers.
     */
    public function getFollowersCountAttribute(): int
    {
        return $this->followers()->count();
    }

    /**
     * Get the number of following.
     */
    public function getFollowingCountAttribute(): int
    {
        return $this->following()->count();
    }

    /**
     * Get the number of posts.
     */
    public function getPostsCountAttribute(): int
    {
        return $this->posts()->count();
    }

    /**
     * Check if this alumni follows another alumni.
     */
    public function isFollowing(int $alumniId): bool
    {
        return $this->following()->where('followed_alumni_id', $alumniId)->exists();
    }

    /**
     * Check if this alumni is followed by another alumni.
     */
    public function isFollowedBy(int $alumniId): bool
    {
        return $this->followers()->where('follower_alumni_id', $alumniId)->exists();
    }

    public function getHasAccessedAttribute(): bool
    {
        return !is_null($this->last_login_at);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(AlumniProgram::class, 'alumni_id');
    }

    public function primaryProgram(): HasOne
    {
        return $this->hasOne(AlumniProgram::class, 'alumni_id')
                    ->where('is_primary', true);
    }

    /**
     * All distinct program types this alumnus has been part of.
     * e.g. ['shs', 'college'] for a promoted alumnus.
     */
    public function allProgramTypes(): array
    {
        return $this->programs()
                    ->pluck('program_type')
                    ->unique()
                    ->values()
                    ->toArray();
    }

    public function hasShsObligation(): bool
    {
        return $this->programs()->where('program_type', 'shs')->exists();
    }

    public function currentProgramType(): ?string
    {
        return $this->primaryProgram?->program_type ?? $this->alumni_type;
    }

    public function getWasShsAlumniAttribute(): bool
    {
        // Cached within the request after first call
        if (!array_key_exists('_was_shs_cache', $this->attributes)) {
            $this->attributes['_was_shs_cache'] = $this->programs()
                ->where('program_type', 'shs')
                ->exists();
        }
        return (bool) $this->attributes['_was_shs_cache'];
    }

    protected function castAttribute($key, $value)
    {
        if (is_null($value)) {
            return $value;
        }

        if (is_string($value) && str_starts_with($value, 'PENDING-')) {
            return $value;
        }

        if (in_array($key, ['student_id_number', 'date_of_birth', 'phone_number', 'email'])) {
            try {
                $castValue = parent::castAttribute($key, $value);

                // Some legacy Laravel payloads were created with
                // Crypt::encrypt(), while the encrypted cast expects the
                // string payload form. Unwrap that serialized scalar.
                if (is_string($castValue) && str_starts_with($castValue, 's:')) {
                    $unserialized = @unserialize($castValue, ['allowed_classes' => false]);
                    return is_string($unserialized) ? $unserialized : $castValue;
                }

                return $castValue;
            } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
                \Log::warning('Undecryptable encrypted column on alumni', [
                    'alumni_id' => $this->id ?? null,
                    'column'    => $key,
                ]);
                $decoded = base64_decode((string) $value, true);
                $payload = is_string($decoded) ? json_decode($decoded, true) : null;

                // Preserve legacy plaintext values while the backfill is
                // incomplete, but never show an invalid encrypted payload.
                if ($key === 'email' && !(
                    is_array($payload)
                    && isset($payload['iv'], $payload['value'], $payload['mac'])
                )) {
                    return $value;
                }

                return null;
            }
        }

        return parent::castAttribute($key, $value);
    }
}

// This is the Alumni.php file