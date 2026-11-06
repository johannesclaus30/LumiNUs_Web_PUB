<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Admin extends Model
{
    protected $table = 'admins';

    public $timestamps = true;

    protected $fillable = [
        'admin_first_name',
        'admin_middle_name',
        'admin_last_name',
        'admin_email',
        'admin_password_hash',
        'admin_role',
        'phone_number',
        'photo',
        'reset_token',
        'reset_token_expires_at',
        'account_status',
    ];

    protected $hidden = [
        'admin_password_hash',
    ];

    protected $casts = [
        'reset_token_expires_at' => 'datetime',
    ];

    public static function emailHash(string $email): string
    {
        return hash('sha256', strtolower(trim($email)));
    }

    /**
     * Find both encrypted accounts and legacy plaintext accounts.
     */
    public function scopeMatchingEmail($query, string $email)
    {
        $normalized = strtolower(trim($email));

        return $query->where(function ($query) use ($normalized) {
            $query->where(
                'admin_email_hash',
                self::emailHash($normalized)
            )->orWhere(function ($legacy) use ($normalized) {
                $legacy->whereNull('admin_email_hash')
                    ->whereRaw(
                        'LOWER(TRIM(admin_email)) = ?',
                        [$normalized]
                    );
            });
        });
    }

    protected static function booted()
    {
        static::saving(function (Admin $admin) {
            // Accessors return readable values from either format.
            $email = $admin->admin_email;
            $phone = $admin->phone_number;

            // Mutators encrypt both fields whenever this account is saved.
            $admin->admin_email = $email;
            $admin->phone_number = $phone;

            $admin->admin_email_hash = self::emailHash($email);
        });
    }

    public function getAdminEmailAttribute($value)
    {
        return $this->readContactValue($value);
    }

    public function setAdminEmailAttribute($value)
    {
        $this->attributes['admin_email'] = $value === null
            ? null
            : Crypt::encryptString((string) $value);
    }

    public function getPhoneNumberAttribute($value)
    {
        return $this->readContactValue($value);
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['phone_number'] = $value === null
            ? null
            : Crypt::encryptString((string) $value);
    }

    /**
     * Read Laravel ciphertext or an existing plaintext value.
     */
    protected function readContactValue($value)
    {
        if ($value === null) {
            return null;
        }

        $decoded = base64_decode((string) $value, true);

        $payload = $decoded === false
            ? null
            : json_decode($decoded, true);

        $looksEncrypted = is_array($payload)
            && isset(
                $payload['iv'],
                $payload['value'],
                $payload['mac']
            );

        if ($looksEncrypted) {
            // Let decryption errors surface rather than treating
            // damaged ciphertext as plaintext.
            return Crypt::decryptString($value);
        }

        return $value;
    }

    public function getFullNameAttribute()
    {
        return "{$this->admin_last_name}, {$this->admin_first_name}";
    }

    public function getInitialsAttribute()
    {
        return strtoupper(
            substr($this->admin_first_name, 0, 1)
            . substr($this->admin_last_name, 0, 1)
        );
    }

    public function getNameAttribute()
    {
        return trim(
            $this->admin_first_name . ' ' . $this->admin_last_name
        );
    }

    public function getEmailAttribute()
    {
        return $this->admin_email;
    }

    public function messagesSent()
    {
        return $this->hasMany(Message::class, 'sender_id')
            ->where('sender_type', 'admin');
    }

    public function messagesReceived()
    {
        return $this->hasMany(Message::class, 'receiver_id')
            ->where('receiver_type', 'admin');
    }

    public function permissions()
    {
        return $this->hasMany(AdminPermission::class, 'admin_id');
    }

    public function canViewModule(string $module): bool
    {
        // Coordinator always has full access.
        if ($this->admin_role === 'Coordinator') {
            return true;
        }

        if (($this->account_status ?? 1) == 0) {
            return false;
        }

        $permission = $this->permissions()
            ->where('module', $module)
            ->first();

        if ($permission) {
            return $permission->can_view;
        }

        $defaults = AdminPermission::getDefaultPermissionsForRole(
            $this->admin_role
        );

        return $defaults[$module] ?? false;
    }

    public function getAccessibleModules(): array
    {
        if ($this->admin_role === 'Coordinator') {
            return AdminPermission::getAvailableModules();
        }

        $allModules = AdminPermission::getAvailableModules();
        $accessibleModules = [];

        foreach ($allModules as $key => $module) {
            if ($this->canViewModule($key)) {
                $accessibleModules[$key] = $module;
            }
        }

        return $accessibleModules;
    }

    public function syncPermissions(array $modulePermissions): void
    {
        foreach ($modulePermissions as $module => $canView) {
            AdminPermission::updateOrCreate(
                [
                    'admin_id' => $this->id,
                    'module' => $module,
                ],
                [
                    'can_view' => $canView,
                    'can_manage' => $canView,
                ]
            );
        }
    }

    public function setupDefaultPermissions(): void
    {
        if ($this->admin_role === 'Coordinator') {
            return;
        }

        $defaults = AdminPermission::getDefaultPermissionsForRole(
            $this->admin_role
        );

        foreach ($defaults as $module => $canView) {
            AdminPermission::create([
                'admin_id' => $this->id,
                'module' => $module,
                'can_view' => $canView,
                'can_manage' => $canView,
            ]);
        }
    }
}