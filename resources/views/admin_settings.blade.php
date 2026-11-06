<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/settings_modern.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <style>
        /* Photo Preview Styles */
        .photo-preview-container {
            display: none;
            margin-top: 1rem;
            text-align: center;
            animation: fadeIn 0.3s ease-out;
        }
        .photo-preview-container.active { display: block; }
        .photo-preview-wrapper { position: relative; display: inline-block; }
        .photo-preview-image {
            width: 150px; height: 150px; border-radius: 50%;
            object-fit: cover; border: 4px solid var(--white);
            box-shadow: var(--shadow-md);
        }
        .photo-preview-remove {
            position: absolute; top: -5px; right: -5px;
            width: 32px; height: 32px; border-radius: 50%;
            background: var(--danger); color: var(--white);
            border: 2px solid var(--white); cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            font-size: 0.875rem; transition: all var(--transition-bounce);
            box-shadow: var(--shadow);
        }
        .photo-preview-remove:hover { background: #dc2626; transform: scale(1.1); }
        .photo-preview-filename {
            margin-top: 0.75rem; font-size: 0.8125rem;
            color: var(--gray-500); font-weight: 500;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .upload-zone.has-preview {
            border-style: solid; border-color: var(--success);
            background: var(--success-light);
        }
        
        /* Section description text */
        .section-desc {
            font-size: 0.875rem;
            color: var(--gray-500);
            margin-bottom: 1.5rem;
            line-height: 1.6;
        }
        
        /* Status indicator dot */
        .status-dot {
            width: 8px; height: 8px; border-radius: 50%;
            display: inline-block; margin-right: 0.5rem;
        }
        .status-dot.active { background: var(--success); }
        .status-dot.restricted { background: var(--danger); }

        .permissions-modal-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(4px);
            z-index: 1100;
            align-items: center;
            justify-content: center;
        }
        .permissions-modal-overlay.active {
            display: flex;
        }

        .permissions-modal {
            background: var(--white);
            border-radius: 16px;
            width: 90%;
            max-width: 550px;
            max-height: 80vh;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .permissions-modal-header {
            padding: 1.5rem;
            border-bottom: 1px solid var(--gray-200);
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .permissions-modal-title {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .permissions-modal-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.125rem;
        }

        .permissions-modal-title h3 {
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--gray-800);
            margin: 0;
        }

        .permissions-modal-title p {
            font-size: 0.8125rem;
            color: var(--gray-500);
            margin: 0.125rem 0 0 0;
        }

        .permissions-modal-close {
            background: none;
            border: none;
            color: var(--gray-400);
            font-size: 1.25rem;
            cursor: pointer;
            padding: 0.5rem;
            border-radius: 8px;
        }

        .permissions-modal-close:hover {
            background: var(--gray-100);
            color: var(--gray-600);
        }

        .permissions-modal-body {
            padding: 1.5rem;
            overflow-y: auto;
            flex: 1;
        }

        .permissions-info-banner {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-size: 0.8125rem;
            color: #1e40af;
        }

        .permissions-list {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .permission-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.875rem 1rem;
            background: var(--gray-50);
            border-radius: 10px;
            border: 1px solid transparent;
            transition: all 0.2s ease;
        }

        .permission-item:hover {
            background: var(--white);
            border-color: var(--gray-200);
        }

        .permission-item-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .permission-item-icon {
            width: 38px; height: 38px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.875rem;
            background: #dbeafe;
            color: #3b82f6;
            transition: all 0.2s ease;
        }

        .permission-item-icon.active {
            background: #3b82f6;
            color: white;
        }

        .permission-item-info h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            color: var(--gray-700);
            margin: 0;
        }

        .permission-item-info span {
            font-size: 0.75rem;
            color: var(--gray-500);
        }

        .permissions-modal-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid var(--gray-200);
            display: flex;
            justify-content: flex-end;
            gap: 0.75rem;
        }
    </style>
</head>
<body>
    
    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="/assets/logos/LumiNUs_Logo_Landscape_Blue.png" alt="LumiNUs Logo" class="logo-luminus">
                </div>
                <button class="sidebar-close" id="sidebarClose" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            @php
                $currentAdmin = null;
                $adminId = session('admin_id');
                if ($adminId) {
                    $currentAdmin = \App\Models\Admin::find($adminId);
                }
                $accessibleModules = $currentAdmin ? $currentAdmin->getAccessibleModules() : [];
            @endphp

            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                
                @if(isset($accessibleModules['dashboard']))
                <a href="/admin/dashboard" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['directory']))
                <a href="/admin/directory" class="nav-item {{ request()->is('admin/directory*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i><span>Alumni Directory</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['announcements']))
                <a href="{{ route('announcements.index') }}" class="nav-item {{ request()->is('admin/announcements*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['events']))
                <a href="{{ route('events.index') }}" class="nav-item {{ request()->is('admin/events*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i><span>Events</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['perks']))
                <a href="{{ route('perks.index') }}" class="nav-item {{ request()->is('admin/perks*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gift"></i><span>Perks & Discounts</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['tracer']))
                <a href="/admin/alumni_tracer" class="nav-item {{ request()->is('admin/alumni_tracer*') ? 'active' : '' }}">
                    <i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['messages']))
                <a href="/admin/messages" class="nav-item {{ request()->is('admin/messages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope"></i><span>Messages</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['settings']))
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i><span>Settings</span>
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">

            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            @php
                $section = request()->query('section', 'account');
                $sectionMeta = [
                    'account' => [
                        'icon' => 'fa-user',
                        'title' => 'Account Information',
                        'description' => 'Manage your personal profile details and photo.',
                    ],
                    'security' => [
                        'icon' => 'fa-shield-halved',
                        'title' => 'Security',
                        'description' => 'Protect your account with a strong password and two-factor authentication.',
                    ],
                    'roles' => [
                        'icon' => 'fa-user-tag',
                        'title' => 'Admin Roles & Permissions',
                        'description' => 'Manage administrator accounts, roles, and access levels.',
                    ],
                    'add-admin' => [
                        'icon' => 'fa-user-plus',
                        'title' => 'Add New Admin',
                        'description' => 'Invite a new administrator and assign their role.',
                    ],
                    'notifications' => [
                        'icon' => 'fa-bell',
                        'title' => 'Notification Preferences',
                        'description' => 'Configure how you receive alerts and updates.',
                    ],
                    'download' => [
                        'icon' => 'fa-download',
                        'title' => 'Download Data',
                        'description' => 'Export system records for reporting and archiving.',
                    ],
                ];
                $activeMeta = $sectionMeta[$section] ?? $sectionMeta['account'];
            @endphp

            <!-- Page Header -->
            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid {{ $activeMeta['icon'] }}"></i>
                            {{ $activeMeta['title'] }}
                        </h1>
                        <p class="page-subtitle">{{ $activeMeta['description'] }}</p>
                    </div>
                </div>
            </header>

            <!-- Alerts -->
            @if (session('status'))
                <div class="settings-alert settings-alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <div>
                        <strong>{{ session('status') }}</strong>
                        @if (session('temporary_password'))
                            <span>Temporary password: <code>{{ session('temporary_password') }}</code></span>
                        @endif
                    </div>
                </div>
            @endif

            @if ($errors->any())
                <div class="settings-alert settings-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>Please review the form.</strong>
                        <span>{{ $errors->first() }}</span>
                    </div>
                </div>
            @endif

            @if (session('error'))
                <div class="settings-alert settings-alert-error">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    <div>
                        <strong>{{ session('error') }}</strong>
                    </div>
                </div>
            @endif

            <!-- Settings Layout -->
            <div class="settings-layout">
                <!-- Sub-Navigation -->
                <aside class="settings-sidebar">
                    <div class="settings-nav-group">
                        <p class="nav-section-title">Personal</p>
                        <a href="?section=account" class="settings-nav-item {{ $section == 'account' ? 'active' : '' }}">
                            <i class="fa-solid fa-user"></i> Account
                        </a>
                        <a href="?section=security" class="settings-nav-item {{ $section == 'security' ? 'active' : '' }}">
                            <i class="fa-solid fa-shield-halved"></i> Security
                        </a>
                    </div>
                    @if($currentAdmin && $currentAdmin->admin_role === 'Coordinator')
                    <div class="settings-nav-group">
                        <p class="nav-section-title">Administration</p>
                        <a href="?section=roles" class="settings-nav-item {{ $section == 'roles' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-tag"></i> Admin Roles
                        </a>
                        <a href="?section=add-admin" class="settings-nav-item {{ $section == 'add-admin' ? 'active' : '' }}">
                            <i class="fa-solid fa-user-plus"></i> Add New Admin
                        </a>
                    </div>
                    @endif
{{--                     
                    <div class="settings-nav-group">
                        <p class="nav-section-title">System</p>
                        <a href="?section=notifications" class="settings-nav-item {{ $section == 'notifications' ? 'active' : '' }}">
                            <i class="fa-solid fa-bell"></i> Notifications
                        </a>
                        <a href="?section=download" class="settings-nav-item {{ $section == 'download' ? 'active' : '' }}">
                            <i class="fa-solid fa-download"></i> Download Data
                        </a>
                    </div> --}}
                </aside>

                <!-- Content Area -->
                <div class="settings-content">
                    
                    {{-- ============ ACCOUNT SECTION ============ --}}
                    @if($section == 'account')
                        @php
                            $firstName = trim($currentAdmin->admin_first_name ?? '');
                            $lastName = trim($currentAdmin->admin_last_name ?? '');
                            $initials = '';
                            if ($firstName !== '') $initials .= strtoupper(mb_substr($firstName, 0, 1));
                            if ($lastName !== '') $initials .= strtoupper(mb_substr($lastName, 0, 1));
                            if ($initials === '') $initials = 'AD';
                        @endphp

                        <!-- Profile Card -->
                        <div class="form-card settings-profile-card">
                            <div class="profile-pic-section">
                                <div class="profile-avatar-wrapper {{ $currentAdminPhotoUrl ? 'has-photo' : 'is-initials' }}">
                                    @if ($currentAdminPhotoUrl)
                                        <img src="{{ $currentAdminPhotoUrl }}" alt="Profile photo" id="current-profile-photo">
                                    @else
                                        <span class="profile-initials" id="current-profile-initials">{{ $initials }}</span>
                                    @endif
                                </div>
                                <div class="profile-pic-copy">
                                    <h3>{{ trim($currentAdmin->admin_first_name . ' ' . $currentAdmin->admin_last_name) ?: 'Administrator' }}</h3>
                                    <p class="text-muted">{{ $currentAdmin->admin_email ?? '' }}</p>
                                    <div class="profile-action-row">
                                        <label class="btn btn-primary upload-btn" for="account-photo-input">
                                            <i class="fa-solid fa-camera"></i> Change Photo
                                        </label>
                                        <button type="button" id="remove-photo-btn" class="btn btn-secondary remove-btn">
                                            <i class="fa-solid fa-trash-can"></i> Remove
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Details Form -->
                        <div class="form-card">
                            <div class="form-card-header">
                                <h3>Personal Details</h3>
                            </div>
                            <form id="account-form" class="settings-form-grid" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="remove_photo" id="remove-photo-flag" value="">
                                <input id="account-photo-input" type="file" name="photo" accept="image/*" style="display: none;" onchange="handleAccountPhotoUpload(this)">

                                <div class="form-group">
                                    <label class="form-label">First Name</label>
                                    <input type="text" name="admin_first_name" class="form-control" value="{{ old('admin_first_name', $currentAdmin->admin_first_name ?? '') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="admin_middle_name" class="form-control" value="{{ old('admin_middle_name', $currentAdmin->admin_middle_name ?? '') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Last Name</label>
                                    <input type="text" name="admin_last_name" class="form-control" value="{{ old('admin_last_name', $currentAdmin->admin_last_name ?? '') }}">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Mobile Number</label>
                                    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number', $currentAdmin->phone_number ?? '') }}">
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email', $currentAdmin->admin_email ?? '') }}">
                                </div>
                                <div class="form-actions full-width">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm('account-form')">Discard Changes</button>
                                    <button type="submit" class="btn btn-primary">Save Profile</button>
                                </div>
                            </form>
                        </div>

                    {{-- ============ SECURITY SECTION ============ --}}
                    @elseif($section == 'security')
                        <!-- Change Password -->
                        <div class="form-card">
                            <div class="form-card-header">
                                <h3>Change Password</h3>
                                <span class="status-badge status-badge-info">Required</span>
                            </div>
                            <form id="change-password-form" class="settings-form-grid" method="POST" action="{{ route('admin.password.update') }}">
                                @csrf
                                @method('PUT')
                                <div class="form-group full-width">
                                    <label class="form-label">Current Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">New Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="password" class="form-control" placeholder="Min. 8 characters" required minlength="8">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Confirm Password</label>
                                    <div class="password-input-wrapper">
                                        <input type="password" name="password_confirmation" class="form-control" placeholder="Repeat new password" required minlength="8">
                                        <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="forgot-password-row full-width">
                                    <a href="{{ route('admin.forgot-password') }}" class="forgot-password-link">
                                        <i class="fa-solid fa-key"></i> Forgot your password?
                                    </a>
                                </div>
                                <div class="form-actions full-width">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm('change-password-form')">Reset</button>
                                    <button type="submit" class="btn btn-primary">Update Password</button>
                                </div>
                            </form>
                        </div>

                        <!-- Two-Factor Authentication -->
                        {{-- <div class="form-card">
                            <div class="form-card-header">
                                <h3>Two-Factor Authentication</h3>
                                <span class="status-badge status-badge-warning">Recommended</span>
                            </div>
                            <p class="section-desc">Add an extra layer of security by requiring a verification code when signing in.</p>
                            <div class="settings-toggle-row">
                                <div>
                                    <strong>Enable 2FA</strong>
                                    <p>Require a verification code when signing in.</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" id="toggle-2fa">
                                    <span class="slider round"></span>
                                </label>
                            </div>
                            <div id="2fa-settings" class="settings-reveal-panel" style="display:none;">
                                <div class="form-group full-width">
                                    <label class="form-label">Authentication Method</label>
                                    <select class="form-control">
                                        <option>Authenticator App</option>
                                        <option>Email</option>
                                    </select>
                                </div>
                                <div class="form-actions full-width">
                                    <button type="button" class="btn btn-secondary" onclick="fakeSave('2FA disabled')">Disable</button>
                                    <button type="button" class="btn btn-primary" onclick="fakeSave('2FA enabled')">Enable</button>
                                </div>
                            </div>
                        </div> --}}

                    {{-- ============ ADMIN ROLES SECTION ============ --}}
                    @elseif($section == 'roles')
                        <div class="form-card">
                            <div class="form-card-header">
                                <div>
                                    <h3>Admin Accounts</h3>
                                    <p class="section-desc" style="margin-bottom:0;">Manage administrator access and permissions.</p>
                                </div>
                                <a href="?section=add-admin" class="btn btn-primary">
                                    <i class="fa-solid fa-plus"></i> Add Admin
                                </a>
                            </div>
                            <div class="table-wrap">
                                <table class="settings-table">
                                    <thead>
                                        <tr>
                                            <th>Admin</th>
                                            <th>Email</th>
                                            <th>Role</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($admins as $admin)
                                            @php
                                                $firstName = $admin->admin_first_name ?? '';
                                                $middleName = $admin->admin_middle_name ?? '';
                                                $lastName = $admin->admin_last_name ?? '';
                                                $email = $admin->admin_email ?? '';
                                                $roleValue = $admin->admin_role ?? '';
                                                $displayName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);
                                                $accountStatus = $admin->account_status ?? 1;
                                                
                                                $roleLabels = [
                                                    'Executive Director' => 'Executive Director',
                                                    'Academic Director' => 'Academic Director',
                                                    'Coordinator' => 'Coordinator',
                                                    'Assistant Coordinator' => 'Assistant Coordinator',
                                                ];
                                                $roleLabel = $roleLabels[$roleValue] ?? ($roleValue ?: 'Unassigned');
                                                
                                                $roleChipClass = match($roleValue) {
                                                    'Executive Director' => 'role-chip-primary',
                                                    'Academic Director' => 'role-chip-secondary',
                                                    'Coordinator' => 'role-chip-success',
                                                    'Assistant Coordinator' => 'role-chip-muted',
                                                    default => '',
                                                };
                                            @endphp
                                            <tr class="{{ $accountStatus == 0 ? 'restricted-row' : '' }}">
                                                <td>
                                                    <div class="table-admin-info">
                                                        @php
                                                            // Resolve admin photo URL (same logic as resolveAdminPhotoUrl in controller)
                                                            $adminPhotoUrl = null;
                                                            $photoPath = trim((string)($admin->photo ?? ''));
                                                            if ($photoPath !== '') {
                                                                if (preg_match('/^https?:\/\//i', $photoPath)) {
                                                                    $adminPhotoUrl = $photoPath;
                                                                } elseif (str_starts_with($photoPath, '/storage/')) {
                                                                    $adminPhotoUrl = $photoPath;
                                                                } elseif (str_starts_with($photoPath, 'storage/')) {
                                                                    $adminPhotoUrl = '/' . $photoPath;
                                                                } elseif (str_starts_with($photoPath, '/')) {
                                                                    $adminPhotoUrl = $photoPath;
                                                                } else {
                                                                    $adminPhotoUrl = Storage::disk('supabase_admin')->url($photoPath);
                                                                }
                                                            }
                                                        @endphp

                                                        <div class="table-admin-avatar">
                                                            @if($adminPhotoUrl)
                                                                <img src="{{ $adminPhotoUrl }}" alt="{{ $displayName }}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                                                            @else
                                                                {{ strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1)) }}
                                                            @endif
                                                        </div>
                                                        <span class="table-admin-name">{{ $displayName ?: 'Unnamed Admin' }}</span>
                                                    </div>
                                                </td>
                                                <td>{{ $email ?: '—' }}</td>
                                                <td>
                                                    <span class="role-chip {{ $roleChipClass }}">{{ $roleLabel }}</span>
                                                </td>
                                                <td>
                                                    @if($accountStatus == 1)
                                                        <span class="status-badge status-badge-success">Active</span>
                                                    @else
                                                        <span class="status-badge status-badge-danger">Restricted</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="table-actions-group">
                                                        @if($currentAdmin && $currentAdmin->admin_role === 'Coordinator' && $currentAdmin->id != $admin->id)
                                                        <button type="button" 
                                                                class="btn-action btn-permissions" 
                                                                onclick="openPermissionsModal('{{ $admin->id }}', '{{ addslashes($displayName) }}')"
                                                                title="Manage Module Permissions">
                                                            <i class="fa-solid fa-shield-halved"></i>
                                                        </button>
                                                        @endif
                                                        <button type="button" class="btn-action btn-manage" title="Manage" 
                                                                onclick="openManageModal(
                                                                    '{{ $admin->id }}',
                                                                    '{{ addslashes($displayName) }}',
                                                                    '{{ addslashes($roleLabel) }}',
                                                                    {{ $accountStatus }}
                                                                )">
                                                            <i class="fa-solid fa-ellipsis"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="empty-table-cell">
                                                    <i class="fa-solid fa-user-slash" style="font-size:2rem; display:block; margin-bottom:0.5rem; color: var(--gray-300);"></i>
                                                    No admin accounts found.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    {{-- ============ ADD ADMIN SECTION ============ --}}
                    @elseif($section == 'add-admin')
                        <div class="form-card">
                            <div class="form-card-header">
                                <div>
                                    <h3>Invite New Admin</h3>
                                    <p class="section-desc" style="margin-bottom:0;">Fill in the details below to create a new administrator account.</p>
                                </div>
                            </div>
                            <form id="add-admin-form" class="settings-form-grid" method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="form-group">
                                    <label class="form-label">First Name <span class="text-danger">*</span></label>
                                    <input type="text" name="admin_first_name" class="form-control" value="{{ old('admin_first_name') }}" placeholder="e.g. Juan" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Middle Name</label>
                                    <input type="text" name="admin_middle_name" class="form-control" value="{{ old('admin_middle_name') }}" placeholder="e.g. Santos">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" name="admin_last_name" class="form-control" value="{{ old('admin_last_name') }}" placeholder="e.g. Dela Cruz" required>
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="admin_email" class="form-control" value="{{ old('admin_email') }}" placeholder="admin@example.com" required>
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Phone Number</label>
                                    <input type="text" name="phone_number" class="form-control" value="{{ old('phone_number') }}" placeholder="09xx xxx xxxx">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Role <span class="text-danger">*</span></label>
                                    <select name="admin_role" class="form-control" required>
                                        <option value="">— Select Role —</option>
                                        <option value="Executive Director" @selected(old('admin_role') === 'Executive Director')>Executive Director</option>
                                        <option value="Academic Director" @selected(old('admin_role') === 'Academic Director')>Academic Director</option>
                                        <option value="Coordinator" @selected(old('admin_role') === 'Coordinator')>Coordinator</option>
                                        <option value="Assistant Coordinator" @selected(old('admin_role') === 'Assistant Coordinator')>Assistant Coordinator</option>
                                    </select>
                                </div>
                                <div class="form-group full-width">
                                    <label class="form-label">Profile Photo</label>
                                    <div class="upload-zone" id="add-admin-upload-zone">
                                        <i class="fa-solid fa-cloud-arrow-up upload-icon"></i>
                                        <p class="upload-title">Click to upload</p>
                                        <p class="upload-desc">JPG, PNG, or WEBP (max 2MB)</p>
                                        <input type="file" name="photo" accept="image/*" class="settings-file-input" onchange="previewPhoto(this, 'add-admin-photo-preview', 'add-admin-upload-zone')">
                                    </div>
                                    <div class="photo-preview-container" id="add-admin-photo-preview">
                                        <div class="photo-preview-wrapper">
                                            <img class="photo-preview-image" src="" alt="Photo preview">
                                            <button type="button" class="photo-preview-remove" onclick="removePhoto(null, 'add-admin-photo-preview', 'add-admin-upload-zone')" title="Remove photo">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        <p class="photo-preview-filename"></p>
                                    </div>
                                </div>
                                <div class="form-group full-width">
                                    <div class="rule-alert">
                                        <i class="fa-solid fa-circle-info"></i>
                                        <div>
                                            <strong>Password</strong>
                                            <p>A secure temporary password will be generated automatically and sent via email.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-actions full-width">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm('add-admin-form'); removePhoto(null, 'add-admin-photo-preview', 'add-admin-upload-zone')">Clear Form</button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fa-solid fa-paper-plane"></i> Send Invitation
                                    </button>
                                </div>
                            </form>
                        </div>

                    {{-- ============ NOTIFICATIONS SECTION ============ --}}
                    @elseif($section == 'notifications')
                        <div class="form-card">
                            <div class="form-card-header">
                                <h3>Notification Channels</h3>
                            </div>
                            <p class="section-desc">Choose which channels to receive alerts and updates through.</p>
                            <form id="notifications-form" class="settings-form-grid">
                                <div class="form-group full-width settings-option-row">
                                    <div>
                                        <strong>Email Notifications</strong>
                                        <p>Receive updates directly to your inbox.</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="email_notifications" checked>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div class="form-group full-width settings-option-row">
                                    <div>
                                        <strong>SMS Notifications</strong>
                                        <p>Get urgent alerts via text message.</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="sms_notifications">
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div class="form-group full-width settings-option-row">
                                    <div>
                                        <strong>System Alerts</strong>
                                        <p>Track maintenance events, warnings, and issues.</p>
                                    </div>
                                    <label class="switch">
                                        <input type="checkbox" name="system_alerts" checked>
                                        <span class="slider round"></span>
                                    </label>
                                </div>
                                <div class="form-actions full-width">
                                    <button type="button" class="btn btn-secondary" onclick="resetForm('notifications-form')">Reset</button>
                                    <button type="button" class="btn btn-primary" onclick="fakeSave('Notification preferences saved')">Save Preferences</button>
                                </div>
                            </form>
                        </div>

                    {{-- ============ DOWNLOAD SECTION ============ --}}
                    @elseif($section == 'download')
                        <div class="form-card">
                            <div class="form-card-header">
                                <h3>Export Data</h3>
                            </div>
                            <p class="section-desc">Download system records for offline analysis or reporting.</p>
                            <form class="settings-form-grid">
                                <div class="form-group full-width">
                                    <label class="form-label">Data Type</label>
                                    <select class="form-control">
                                        <option>All Alumni Records (CSV)</option>
                                        <option>Announcements (CSV)</option>
                                        <option>Events & Registrations (CSV)</option>
                                        <option>Tracer Form Responses (CSV)</option>
                                    </select>
                                </div>
                                <div class="rule-alert full-width">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>The export will be generated using the most recent data available.</span>
                                </div>
                                <div class="form-actions full-width">
                                    <button type="button" class="btn btn-primary" onclick="fakeSave('Preparing download...')">
                                        <i class="fa-solid fa-download"></i> Download
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif

                </div>
            </div>
        </main>
    </div>

    <!-- Warning Modal -->
    <div id="warningModal" class="warning-modal-overlay">
        <div class="warning-modal">
            <div class="warning-modal-icon" id="warningModalIcon">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="warning-modal-content">
                <h3 class="warning-modal-title" id="warningModalTitle">Confirm Action</h3>
                <p class="warning-modal-message" id="warningModalMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="warning-modal-actions">
                <button class="btn btn-secondary" id="warningModalCancel">
                    <i class="fa-solid fa-xmark"></i> Cancel
                </button>
                <button class="btn btn-danger" id="warningModalConfirm">
                    <i class="fa-solid fa-check"></i> Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Manage Admin Modal -->
    <div id="manageModal" class="manage-modal-overlay">
        <div class="manage-modal">
            <div class="manage-modal-header">
                <div class="manage-modal-admin-info">
                    <div class="manage-modal-avatar">
                        <i class="fa-solid fa-user-gear"></i>
                    </div>
                    <div>
                        <h3 class="manage-modal-name" id="manageModalName">Admin Name</h3>
                        <span class="manage-modal-role" id="manageModalRole">Role</span>
                    </div>
                </div>
                <button class="manage-modal-close" onclick="closeManageModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="manage-modal-body">
                <div class="manage-action-group">
                    <div class="manage-action-item">
                        <div class="manage-action-icon icon-info">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div class="manage-action-content">
                            <h4>Reset Password</h4>
                            <p>Generate a new temporary password for this admin.</p>
                        </div>
                        <button type="button" class="btn btn-primary btn-sm" onclick="executeResetPassword()">
                            <i class="fa-solid fa-rotate"></i> Reset
                        </button>
                    </div>
                    <div class="manage-action-item" id="restrictActionItem">
                        <div class="manage-action-icon icon-warning">
                            <i class="fa-solid fa-user-slash"></i>
                        </div>
                        <div class="manage-action-content">
                            <h4 id="restrictTitle">Restrict Account</h4>
                            <p id="restrictDesc">Temporarily suspend access for this admin.</p>
                        </div>
                        <button type="button" class="btn btn-warning btn-sm" id="manageRestrictBtn" onclick="executeToggleRestrict()">
                            <i class="fa-solid fa-lock"></i> Restrict
                        </button>
                    </div>
                    <div class="manage-action-item danger-zone">
                        <div class="manage-action-icon icon-danger">
                            <i class="fa-solid fa-trash-can"></i>
                        </div>
                        <div class="manage-action-content">
                            <h4>Delete Account</h4>
                            <p>Permanently remove this admin from the system.</p>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm" onclick="executeDeleteAdmin()">
                            <i class="fa-solid fa-trash"></i> Delete
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Permissions Modal -->
    <div id="permissionsModal" class="permissions-modal-overlay">
        <div class="permissions-modal">
            <div class="permissions-modal-header">
                <div class="permissions-modal-title">
                    <div class="permissions-modal-icon">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div>
                        <h3>Module Permissions</h3>
                        <p id="permissionsAdminName">Manage what this admin can access</p>
                    </div>
                </div>
                <button class="permissions-modal-close" onclick="closePermissionsModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="permissions-modal-body">
                <div class="permissions-info-banner">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Coordinator has full access by default. These settings apply to other roles.</span>
                </div>
                <div id="permissionsList" class="permissions-list">
                    <!-- Dynamically populated -->
                </div>
            </div>
            <div class="permissions-modal-footer">
                <button class="btn btn-secondary" onclick="closePermissionsModal()">Cancel</button>
                <button class="btn btn-primary" onclick="savePermissions()">
                    <i class="fa-solid fa-floppy-disk"></i> Save Permissions
                </button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
    // ========================================
    // MANAGE MODAL LOGIC
    // ========================================
    let currentManageAdminId = null;
    let currentManageAdminName = '';
    let currentManageAccountStatus = 1;

    function openManageModal(adminId, adminName, adminRole, accountStatus) {
        currentManageAdminId = adminId;
        currentManageAdminName = adminName;
        currentManageAccountStatus = accountStatus;

        document.getElementById('manageModalName').textContent = adminName;
        document.getElementById('manageModalRole').textContent = adminRole;

        const restrictTitle = document.getElementById('restrictTitle');
        const restrictDesc = document.getElementById('restrictDesc');
        const restrictBtn = document.getElementById('manageRestrictBtn');
        const restrictIcon = document.getElementById('restrictActionItem').querySelector('.manage-action-icon');

        if (accountStatus == 0) {
            restrictTitle.textContent = 'Unrestrict Account';
            restrictDesc.textContent = 'Restore access for this admin.';
            restrictBtn.innerHTML = '<i class="fa-solid fa-unlock"></i> Unrestrict';
            restrictBtn.className = 'btn btn-success btn-sm';
            restrictIcon.className = 'manage-action-icon icon-success';
        } else {
            restrictTitle.textContent = 'Restrict Account';
            restrictDesc.textContent = 'Temporarily suspend access for this admin.';
            restrictBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Restrict';
            restrictBtn.className = 'btn btn-warning btn-sm';
            restrictIcon.className = 'manage-action-icon icon-warning';
        }

        document.getElementById('manageModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeManageModal() {
        document.getElementById('manageModal').classList.remove('active');
        document.body.style.overflow = '';
        currentManageAdminId = null;
    }

    document.getElementById('manageModal').addEventListener('click', function(e) {
        if (e.target === this) closeManageModal();
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const warningModal = document.getElementById('warningModal');
            if (!warningModal.classList.contains('active')) {
                closeManageModal();
            }
        }
    });

    function executeResetPassword() {
        if (!currentManageAdminId) { closeManageModal(); return; }
        const adminId = currentManageAdminId;
        const adminName = currentManageAdminName;
        closeManageModal();
        
        window.showWarningModal({
            title: 'Reset Password',
            message: 'Are you sure you want to reset the password for <strong>' + adminName + '</strong>?<br><small>A new temporary password will be generated.</small>',
            iconType: 'info',
            confirmText: 'Reset Password',
            confirmClass: 'btn-info',
            onConfirm: function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/settings/admin/' + adminId + '/reset-password';
                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                form.appendChild(csrf);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function executeToggleRestrict() {
        if (!currentManageAdminId) { closeManageModal(); return; }
        const adminId = currentManageAdminId;
        const adminName = currentManageAdminName;
        const isCurrentlyRestricted = currentManageAccountStatus == 0;
        closeManageModal();
        
        const title = isCurrentlyRestricted ? 'Unrestrict Account' : 'Restrict Account';
        const message = isCurrentlyRestricted 
            ? 'Are you sure you want to <strong>unrestrict</strong> ' + adminName + '\'s account?<br><small>They will be able to log in again.</small>'
            : 'Are you sure you want to <strong>restrict</strong> ' + adminName + '\'s account?<br><small>They will not be able to log in until unrestricted.</small>';
        
        window.showWarningModal({
            title: title, message: message, iconType: 'warning',
            confirmText: isCurrentlyRestricted ? 'Unrestrict' : 'Restrict',
            confirmClass: isCurrentlyRestricted ? 'btn-success' : 'btn-warning',
            onConfirm: function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/settings/admin/' + adminId + '/toggle-restrict';
                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                form.appendChild(csrf);
                const method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'PATCH';
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    function executeDeleteAdmin() {
        if (!currentManageAdminId) { closeManageModal(); return; }
        const adminId = currentManageAdminId;
        const adminName = currentManageAdminName;
        closeManageModal();
        
        window.showWarningModal({
            title: 'Delete Admin Account',
            message: 'Are you sure you want to <strong>permanently delete</strong> ' + adminName + '\'s account?<br><small>This action cannot be undone. All data associated with this admin will be removed.</small>',
            iconType: 'danger',
            confirmText: 'Delete',
            confirmClass: 'btn-danger',
            onConfirm: function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = '/admin/settings/admin/' + adminId;
                const csrf = document.createElement('input');
                csrf.type = 'hidden'; csrf.name = '_token';
                csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                form.appendChild(csrf);
                const method = document.createElement('input');
                method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }
    </script>

    <script>
    // Mobile menu toggle
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) toggleMobileMenu();
        });
    });

    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                document.getElementById('adminSidebar').classList.remove('mobile-open');
                document.getElementById('mobileOverlay').classList.remove('active');
                document.body.style.overflow = '';
            }
        }, 250);
    });

    function togglePassword(button) {
        const input = button.parentElement.querySelector('input');
        const icon = button.querySelector('i');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
        }
    }

    function resetForm(id) { var f = document.getElementById(id); if (f) f.reset(); }

    function previewPhoto(input, previewContainerId, uploadZoneId) {
        const previewContainer = document.getElementById(previewContainerId);
        const uploadZone = document.getElementById(uploadZoneId);
        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                previewContainer.querySelector('.photo-preview-image').src = e.target.result;
                previewContainer.querySelector('.photo-preview-filename').textContent = file.name;
                previewContainer.classList.add('active');
                if (uploadZone) uploadZone.classList.add('has-preview');
            };
            reader.readAsDataURL(file);
        }
    }

    function removePhoto(inputId, previewContainerId, uploadZoneId) {
        const previewContainer = document.getElementById(previewContainerId);
        const uploadZone = document.getElementById(uploadZoneId);
        if (inputId) { const fi = document.getElementById(inputId); if (fi) fi.value = ''; }
        if (previewContainer) {
            previewContainer.classList.remove('active');
            const img = previewContainer.querySelector('.photo-preview-image');
            if (img) img.src = '';
        }
        if (uploadZone) uploadZone.classList.remove('has-preview');
    }

    function handleAccountPhotoUpload(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const wrapper = document.querySelector('.profile-avatar-wrapper');
                const initials = document.getElementById('current-profile-initials');
                if (initials) initials.style.display = 'none';
                const existingImg = wrapper.querySelector('img');
                if (existingImg) {
                    existingImg.src = e.target.result;
                } else {
                    const img = document.createElement('img');
                    img.src = e.target.result; img.alt = 'Profile photo'; img.id = 'current-profile-photo';
                    wrapper.innerHTML = '';
                    wrapper.appendChild(img);
                }
                wrapper.classList.add('has-photo');
                wrapper.classList.remove('is-initials');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // ========================================
    // INITIALIZE ON DOM LOAD
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        const warningOverlay = document.getElementById('warningModal');
        const warningTitle = document.getElementById('warningModalTitle');
        const warningMessage = document.getElementById('warningModalMessage');
        const warningIcon = document.getElementById('warningModalIcon');
        const confirmBtn = document.getElementById('warningModalConfirm');
        const modalCancelBtn = document.getElementById('warningModalCancel');
        let pendingCallback = null;

        function closeWarningModal() {
            warningOverlay.classList.remove('active');
            const manageModal = document.getElementById('manageModal');
            if (!manageModal || !manageModal.classList.contains('active')) {
                document.body.style.overflow = '';
            }
            pendingCallback = null;
        }

        modalCancelBtn.addEventListener('click', closeWarningModal);
        warningOverlay.addEventListener('click', function(e) { if (e.target === warningOverlay) closeWarningModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && warningOverlay.classList.contains('active')) closeWarningModal(); });
        confirmBtn.addEventListener('click', function() { if (pendingCallback) pendingCallback(); closeWarningModal(); });

        window.showWarningModal = function(config) {
            const {
                title = 'Confirm Action', message = 'Are you sure?',
                iconType = 'warning', confirmText = 'Confirm',
                confirmClass = 'btn-danger', onConfirm = null, hideCancel = false
            } = config;

            warningTitle.textContent = title;
            warningMessage.innerHTML = message;
            warningIcon.className = 'warning-modal-icon ' + iconType;
            const iconElement = warningIcon.querySelector('i');
            if (iconType === 'danger') iconElement.className = 'fa-solid fa-triangle-exclamation';
            else if (iconType === 'success') iconElement.className = 'fa-solid fa-circle-question';
            else if (iconType === 'info') iconElement.className = 'fa-solid fa-circle-info';
            else iconElement.className = 'fa-solid fa-triangle-exclamation';

            confirmBtn.className = 'btn ' + confirmClass;
            confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;
            modalCancelBtn.style.display = hideCancel ? 'none' : 'inline-flex';
            pendingCallback = onConfirm;
            warningOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();
        };

        window.fakeSave = function(msg) {
            window.showWarningModal({
                title: 'Settings Saved', message: `<strong>${msg}</strong>`,
                iconType: 'info', confirmText: 'OK', confirmClass: 'btn-info', hideCancel: true
            });
        };

        const removeBtn = document.getElementById('remove-photo-btn');
        const form = document.getElementById('account-form');
        const removeFlag = document.getElementById('remove-photo-flag');
        if (removeBtn && form && removeFlag) {
            removeBtn.addEventListener('click', function() {
                window.showWarningModal({
                    title: 'Remove Profile Photo',
                    message: 'Are you sure you want to <strong>remove</strong> your profile photo?<br><small>This will revert to the default initials avatar.</small>',
                    iconType: 'warning', confirmText: 'Remove', confirmClass: 'btn-warning',
                    onConfirm: function() { removeFlag.value = '1'; form.submit(); }
                });
            });
        }

        var t2 = document.getElementById('toggle-2fa');
        if (t2) {
            t2.addEventListener('change', function(e) {
                var settings = document.getElementById('2fa-settings');
                if (this.checked) settings.style.display = 'block';
                else settings.style.display = 'none';
            });
            if (t2.checked) {
                var s = document.getElementById('2fa-settings');
                if (s) s.style.display = 'block';
            }
        }
    });

   // ========================================
    // PERMISSIONS MODAL LOGIC
    // ========================================
    let currentPermissionsAdminId = null;
    let currentPermissionsData = {};

    async function openPermissionsModal(adminId, adminName) {
        currentPermissionsAdminId = adminId;
        document.getElementById('permissionsAdminName').textContent = 
            'Manage what ' + adminName + ' can access';
        
        try {
            const response = await fetch(`/admin/settings/admin/${adminId}/permissions`);
            const data = await response.json();
            
            if (data.error) {
                window.showWarningModal({
                    title: 'Error',
                    message: '<strong>' + data.error + '</strong>',
                    iconType: 'danger',
                    confirmText: 'OK',
                    confirmClass: 'btn-danger',
                    hideCancel: true
                });
                return;
            }
            
            currentPermissionsData = data.permissions;
            renderPermissionsList(data.permissions);
            
            document.getElementById('permissionsModal').classList.add('active');
            document.body.style.overflow = 'hidden';
            
        } catch (error) {
            console.error('Error fetching permissions:', error);
            window.showWarningModal({
                title: 'Error',
                message: '<strong>Failed to load permissions. Please try again.</strong>',
                iconType: 'danger',
                confirmText: 'OK',
                confirmClass: 'btn-danger',
                hideCancel: true
            });
        }
    }

    function renderPermissionsList(permissions) {
        const container = document.getElementById('permissionsList');
        container.innerHTML = '';
        
        Object.entries(permissions).forEach(([moduleKey, moduleData]) => {
            const item = document.createElement('div');
            item.className = 'permission-item';
            item.innerHTML = `
                <div class="permission-item-left">
                    <div class="permission-item-icon ${moduleData.can_view ? 'active' : ''}">
                        <i class="fa-solid ${moduleData.icon}"></i>
                    </div>
                    <div class="permission-item-info">
                        <h4>${moduleData.name}</h4>
                        <span>${moduleData.description || ''}</span>
                    </div>
                </div>
                <label class="switch">
                    <input type="checkbox" 
                        data-module="${moduleKey}" 
                        ${moduleData.can_view ? 'checked' : ''}
                        onchange="togglePermission('${moduleKey}', this.checked)">
                    <span class="slider round"></span>
                </label>
            `;
            container.appendChild(item);
        });
    }

    function togglePermission(moduleKey, enabled) {
        if (currentPermissionsData[moduleKey]) {
            currentPermissionsData[moduleKey].can_view = enabled;
            
            const item = document.querySelector(`.permission-item input[data-module="${moduleKey}"]`);
            if (item) {
                const icon = item.closest('.permission-item').querySelector('.permission-item-icon');
                if (enabled) {
                    icon.classList.add('active');
                } else {
                    icon.classList.remove('active');
                }
            }
        }
    }

    async function savePermissions() {
        if (!currentPermissionsAdminId) return;
        
        const permissions = Object.entries(currentPermissionsData).map(([module, data]) => ({
            module: module,
            can_view: data.can_view,
            can_manage: data.can_view,
        }));
        
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            const response = await fetch(`/admin/settings/admin/${currentPermissionsAdminId}/permissions`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({ permissions }),
            });
            
            const data = await response.json();
            
            if (data.success) {
                closePermissionsModal();
                window.showWarningModal({
                    title: 'Permissions Updated',
                    message: '<strong>' + data.message + '</strong>',
                    iconType: 'success',
                    confirmText: 'OK',
                    confirmClass: 'btn-success',
                    hideCancel: true,
                    onConfirm: function() {
                        location.reload();
                    }
                });
            } else {
                window.showWarningModal({
                    title: 'Error',
                    message: '<strong>' + (data.error || 'Failed to save permissions') + '</strong>',
                    iconType: 'danger',
                    confirmText: 'OK',
                    confirmClass: 'btn-danger',
                    hideCancel: true
                });
            }
            
        } catch (error) {
            console.error('Error saving permissions:', error);
            window.showWarningModal({
                title: 'Error',
                message: '<strong>Failed to save permissions. Please try again.</strong>',
                iconType: 'danger',
                confirmText: 'OK',
                confirmClass: 'btn-danger',
                hideCancel: true
            });
        }
    }

    function closePermissionsModal() {
        document.getElementById('permissionsModal').classList.remove('active');
        document.body.style.overflow = '';
        currentPermissionsAdminId = null;
    }

    document.getElementById('permissionsModal')?.addEventListener('click', function(e) {
        if (e.target === this) closePermissionsModal();
    });
    </script>
</body>
</html>