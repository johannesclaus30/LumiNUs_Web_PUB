<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminPermission extends Model
{
    protected $table = 'admin_permissions';
    
    protected $fillable = [
        'admin_id',
        'module',
        'can_view',
        'can_manage',
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_manage' => 'boolean',
    ];

    public $timestamps = true;

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    /**
     * Get all available modules with their details
     */
    public static function getAvailableModules(): array
    {
        return [
            'dashboard' => [
                'name' => 'Dashboard',
                'icon' => 'fa-chart-line',
                'route' => '/admin/dashboard',
                'description' => 'View analytics and statistics',
            ],
            'directory' => [
                'name' => 'Alumni Directory',
                'icon' => 'fa-users',
                'route' => '/admin/directory',
                'description' => 'Manage alumni records and profiles',
            ],
            'announcements' => [
                'name' => 'Announcements',
                'icon' => 'fa-bullhorn',
                'route' => '/admin/announcements',
                'description' => 'Create and manage announcements',
            ],
            'events' => [
                'name' => 'Events',
                'icon' => 'fa-calendar-check',
                'route' => '/admin/events',
                'description' => 'Manage events and registrations',
            ],
            'perks' => [
                'name' => 'Perks & Discounts',
                'icon' => 'fa-gift',
                'route' => '/admin/perks',
                'description' => 'Manage alumni benefits and discounts',
            ],
            'tracer' => [
                'name' => 'Alumni Tracer',
                'icon' => 'fa-location-dot',
                'route' => '/admin/alumni_tracer',
                'description' => 'View tracer study responses',
            ],
            'messages' => [
                'name' => 'Messages',
                'icon' => 'fa-envelope',
                'route' => '/admin/messages',
                'description' => 'Manage communications',
            ],
            'settings' => [
                'name' => 'Settings',
                'icon' => 'fa-gear',
                'route' => '/admin/settings',
                'description' => 'Manage system settings and admins',
            ],
        ];
    }

    /**
     * Default permissions by role
     * Executive Director & Academic Director: Limited access
     * Coordinator & Assistant Coordinator: Full access
     */
    public static function getDefaultPermissionsForRole(string $role): array
    {
        $allModules = array_keys(self::getAvailableModules());
        
        // Director roles: Limited access
        if (in_array($role, ['Executive Director', 'Academic Director'])) {
            $allowedModules = ['dashboard', 'directory', 'tracer', 'messages', 'settings'];
            
            $permissions = [];
            foreach ($allModules as $module) {
                $permissions[$module] = in_array($module, $allowedModules);
            }
            return $permissions;
        }
        
        // Coordinator and Assistant Coordinator: Full access by default
        $permissions = [];
        foreach ($allModules as $module) {
            $permissions[$module] = true;
        }
        return $permissions;
    }
}