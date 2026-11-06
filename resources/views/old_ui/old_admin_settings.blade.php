<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/settings.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>
                <a href="{{ url('/admin/dashboard') }}" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="{{ route('admin.directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-current">Settings</a>
            </div>
            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>

        <div class="div-dashboard-container admin-scrollable settings-main-panel">
            @php
                $section = request()->query('section', 'account');

                $sectionMeta = [
                    'account' => [
                        'eyebrow' => 'Admin account',
                        'title' => 'Account Information',
                        'description' => 'Update the profile details shown across the admin workspace.',
                        'status' => 'Profile ready',
                    ],
                    'security' => [
                        'eyebrow' => 'Admin account',
                        'title' => 'Security',
                        'description' => 'Protect the dashboard with stronger credentials and multi-factor access.',
                        'status' => 'Security controls',
                    ],
                    'roles' => [
                        'eyebrow' => 'Admin control',
                        'title' => 'Admin Roles',
                        'description' => 'Review the people who can manage content, users, and system settings.',
                        'status' => '2 active roles',
                    ],
                    'add-admin' => [
                        'eyebrow' => 'Admin control',
                        'title' => 'Add New Admin',
                        'description' => 'Invite a new administrator and assign the appropriate access level.',
                        'status' => 'Invitation flow',
                    ],
                    'notifications' => [
                        'eyebrow' => 'System settings',
                        'title' => 'Notification Settings',
                        'description' => 'Choose how the team receives updates about platform activity.',
                        'status' => 'Delivery preferences',
                    ],
                    'download' => [
                        'eyebrow' => 'System settings',
                        'title' => 'Download Data',
                        'description' => 'Export records for reporting, archiving, or offline review.',
                        'status' => 'Export tools',
                    ],
                ];

                $activeMeta = $sectionMeta[$section] ?? $sectionMeta['account'];
            @endphp

            <div class="settings-page-header">
                <div>
                    <p class="settings-eyebrow">{{ $activeMeta['eyebrow'] }}</p>
                    <h1 class="page-title">{{ $activeMeta['title'] }}</h1>
                    <p class="settings-page-description">{{ $activeMeta['description'] }}</p>
                </div>
                <div class="settings-status-card">
                    <span class="settings-status-label">Active area</span>
                    <strong>{{ $activeMeta['status'] }}</strong>
                    <span class="settings-status-note">Use the sidebar to switch between account, admin, and system settings.</span>
                </div>
            </div>

            @if (session('status'))
                <div class="settings-alert settings-alert-success">
                    <strong>{{ session('status') }}</strong>
                    @if (session('temporary_password'))
                        <span>Temporary password: {{ session('temporary_password') }}</span>
                    @endif
                </div>
            @endif

            @if ($errors->any())
                <div class="settings-alert settings-alert-error">
                    <strong>Please review the form.</strong>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @switch($section)
                @case('security')
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div>
                                <h3>Change Password</h3>
                                <p>Keep the admin account protected with a strong, unique password.</p>
                            </div>
                        </div>
                        <form id="change-password-form" class="settings-form-grid">
                            <div class="form-group full-width">
                                <label>Current Password</label>
                                <input type="password" name="current_password" placeholder="Enter current password">
                            </div>
                            <div class="form-group">
                                <label>New Password</label>
                                <input type="password" name="password" placeholder="Enter new password">
                            </div>
                            <div class="form-group">
                                <label>Confirm New Password</label>
                                <input type="password" name="password_confirmation" placeholder="Repeat new password">
                            </div>
                            <div class="form-footer full-width">
                                <button type="button" class="btn-discard" onclick="resetForm('change-password-form')">Reset</button>
                                <button type="button" class="btn-save" onclick="fakeSave('Password updated')">Change Password</button>
                            </div>
                        </form>
                    </div>

                    <div class="settings-card settings-card-spaced">
                        <div class="settings-card-header">
                            <div>
                                <h3>Two-Factor Authentication</h3>
                                <p>Add a second layer of login verification for sensitive admin work.</p>
                            </div>
                            <span class="settings-badge">Recommended</span>
                        </div>
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
                                <label>Authentication Method</label>
                                <select>
                                    <option>Authenticator App</option>
                                    <option>SMS</option>
                                </select>
                            </div>
                            <div class="form-footer full-width">
                                <button type="button" class="btn-discard" onclick="fakeSave('2FA disabled')">Disable</button>
                                <button type="button" class="btn-save" onclick="fakeSave('2FA enabled')">Enable</button>
                            </div>
                        </div>
                    </div>
                @break

                @case('roles')
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div>
                                <h3>Admin Accounts</h3>
                                <p>Track who has access to the admin tools and what they can change.</p>
                            </div>
                            <span class="settings-badge settings-badge-muted">Live data</span>
                        </div>
                        <div class="table-wrap">
                            <table class="table-simple settings-table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Role</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($admins as $admin)

                                    @php
                                        $firstName = $admin->admin_first_name ?? $admin->AdminFirstName ?? '';
                                        $middleName = $admin->admin_middle_name ?? $admin->AdminMiddleName ?? '';
                                        $lastName = $admin->admin_last_name ?? $admin->AdminLastName ?? '';
                                        $email = $admin->admin_email ?? $admin->AdminEmail ?? '';
                                        $roleValue = $admin->admin_role ?? $admin->AdminRole ?? '';
                                        $displayName = trim($firstName . ' ' . ($middleName ? $middleName . ' ' : '') . $lastName);
                                        
                                        // Map role values to display labels (matches controller validation)
                                        $roleLabels = [
                                            'Executive Director' => 'Executive Director',
                                            'Academic Director' => 'Academic Director',
                                            'Coordinator' => 'Coordinator',
                                            'Assistant Coordinator' => 'Assistant Coordinator',
                                        ];
                                        $roleLabel = $roleLabels[$roleValue] ?? ($roleValue !== '' ? ucwords(str_replace(['_', '-'], ' ', $roleValue)) : 'Unassigned');
                                    @endphp

                                        <tr>
                                            <td>{{ $displayName ?: 'Unnamed Admin' }}</td>
                                            <td>{{ $email ?: 'No email provided' }}</td>
                                            <td>
                                                {{-- <span class="role-chip {{ $roleValue === 'super_admin' || $roleValue === 'SuperAdmin' ? 'role-chip-primary' : '' }}">
                                                    {{ $roleLabel }}
                                                </span> --}}
                                                @php
                                                    $roleChipClass = match($roleValue) {
                                                        'Executive Director' => 'role-chip-primary',
                                                        'Academic Director' => 'role-chip-secondary',
                                                        'Coordinator' => 'role-chip-success',
                                                        'Assistant Coordinator' => 'role-chip-muted',
                                                        default => '',
                                                    };
                                                @endphp
                                                <span class="role-chip {{ $roleChipClass }}">
                                                    {{ $roleLabel }}
                                                </span>
                                            </td>
                                            <td>
                                                <button type="button" class="btn-small">Edit</button>
                                                <button type="button" class="btn-small btn-danger">Remove</button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4">No admin accounts have been created yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                @break

                @case('add-admin')
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div>
                                <h3>Invite Admin</h3>
                                <p>Fill in the contact details and assign an access level before sending the invitation.</p>
                            </div>
                        </div>
                        <form id="add-admin-form" class="settings-form-grid" method="POST" action="{{ route('admin.settings.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="admin_first_name" value="{{ old('admin_first_name') }}" placeholder="First name">
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="admin_middle_name" value="{{ old('admin_middle_name') }}" placeholder="Middle name">
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="admin_last_name" value="{{ old('admin_last_name') }}" placeholder="Last name">
                            </div>
                            <div class="form-group full-width">
                                <label>Email</label>
                                <input type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="name@example.com">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="text" name="phone_number" value="{{ old('phone_number') }}" placeholder="09xx xxx xxxx">
                            </div>
                            <div class="form-group">
                            <label>Role</label>
                            <select name="admin_role">
                                <option value="">-- Select a role --</option>
                                <option value="Executive Director" @selected(old('admin_role') === 'Executive Director')>Executive Director</option>
                                <option value="Academic Director" @selected(old('admin_role') === 'Academic Director')>Academic Director</option>
                                <option value="Coordinator" @selected(old('admin_role') === 'Coordinator')>Coordinator</option>
                                <option value="Assistant Coordinator" @selected(old('admin_role') === 'Assistant Coordinator')>Assistant Coordinator</option>
                            </select>
                        </div>
                            <div class="form-group full-width settings-form-note">
                                <strong>Temporary password</strong>
                                <p>A secure password will be generated automatically after saving.</p>
                            </div>

                            <div class="form-group full-width settings-file-group">
                                <label>Photo</label>
                                <input type="file" name="photo" accept="image/*" class="settings-file-input">
                                <p class="settings-file-hint">Choose a profile photo in JPG, PNG, or WEBP format.</p>
                            </div>

                            <div class="form-footer full-width">
                                <button type="button" class="btn-discard" onclick="resetForm('add-admin-form')">Clear</button>
                                <button type="submit" class="btn-save">Add Admin</button>
                            </div>
                        </form>
                    </div>
                @break

                @case('notifications')
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div>
                                <h3>Notification Channels</h3>
                                <p>Select which alerts should reach the team in real time.</p>
                            </div>
                        </div>
                        <form id="notifications-form" class="settings-form-grid">
                            <div class="form-group full-width settings-option-row">
                                <div>
                                    <label>Email Notifications</label>
                                    <p>Send important updates to the admin inbox.</p>
                                </div>
                                <input type="checkbox" name="email_notifications" checked>
                            </div>
                            <div class="form-group full-width settings-option-row">
                                <div>
                                    <label>SMS Notifications</label>
                                    <p>Push urgent alerts to registered phone numbers.</p>
                                </div>
                                <input type="checkbox" name="sms_notifications">
                            </div>
                            <div class="form-group full-width settings-option-row">
                                <div>
                                    <label>System Alerts</label>
                                    <p>Track issues, warnings, and maintenance events.</p>
                                </div>
                                <input type="checkbox" name="system_alerts" checked>
                            </div>
                            <div class="form-footer full-width">
                                <button type="button" class="btn-discard" onclick="resetForm('notifications-form')">Reset</button>
                                <button type="button" class="btn-save" onclick="fakeSave('Notification settings saved')">Save</button>
                            </div>
                        </form>
                    </div>
                @break

                @case('download')
                    <div class="settings-card">
                        <div class="settings-card-header">
                            <div>
                                <h3>Export Options</h3>
                                <p>Generate a file for reporting, audits, or offline review.</p>
                            </div>
                        </div>
                        <form class="settings-form-grid">
                            <div class="form-group full-width">
                                <label>Export</label>
                                <select>
                                    <option>All Users (CSV)</option>
                                    <option>Announcements</option>
                                    <option>Events</option>
                                </select>
                            </div>
                            <div class="download-note full-width">
                                The export will be prepared using the latest available records.
                            </div>
                            <div class="form-footer full-width">
                                <button type="button" class="btn-save" onclick="fakeSave('Preparing download...')">Download</button>
                            </div>
                        </form>
                    </div>
                @break

                @default
                    @php
                        // Compute initials for the current admin (same logic as navbar)
                        $firstName = trim($currentAdmin->admin_first_name ?? '');
                        $lastName = trim($currentAdmin->admin_last_name ?? '');
                        $initials = '';

                        if ($firstName !== '') {
                            $initials .= strtoupper(mb_substr($firstName, 0, 1));
                        }
                        if ($lastName !== '') {
                            $initials .= strtoupper(mb_substr($lastName, 0, 1));
                        }
                        if ($initials === '') {
                            $nameParts = preg_split('/\s+/', trim($currentAdmin->admin_name ?? '')) ?: [];
                            if (count($nameParts) >= 2) {
                                $initials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1));
                            } else {
                                $initials = strtoupper(mb_substr($currentAdmin->admin_name ?? 'AD', 0, 2));
                            }
                        }
                    @endphp

                    <div class="settings-card settings-profile-card">
                        <div class="profile-pic-section">
                            {{-- Avatar with conditional photo/initials --}}
                            <div class="profile-avatar-wrapper {{ $currentAdminPhotoUrl ? 'has-photo' : 'is-initials' }}">
                                @if ($currentAdminPhotoUrl)
                                    <img src="{{ $currentAdminPhotoUrl }}" alt="Profile photo">
                                @else
                                    <span class="profile-initials">{{ $initials }}</span>
                                @endif
                            </div>
                            <div class="profile-pic-copy">
                                <h3>Profile Photo</h3>
                                <p>Use a clear headshot so the admin profile is recognizable across the system.</p>
                                <div class="profile-action-row">
                                    <label class="upload-btn" for="account-photo-input">Upload New Photo</label>
                                    <button type="button" id="remove-photo-btn" class="remove-btn">Remove Photo</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card settings-card-spaced">
                        <div class="settings-card-header">
                            <div>
                                <h3>Personal Details</h3>
                                <p>These details appear in the admin profile and internal references.</p>
                            </div>
                        </div>

                        <form id="account-form" class="settings-form-grid" method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="remove_photo" id="remove-photo-flag" value="">

                            <div class="form-group">
                                <label>Last Name</label>
                                <input type="text" name="admin_last_name" value="{{ old('admin_last_name', $currentAdmin->admin_last_name ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>First Name</label>
                                <input type="text" name="admin_first_name" value="{{ old('admin_first_name', $currentAdmin->admin_first_name ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>Middle Name</label>
                                <input type="text" name="admin_middle_name" value="{{ old('admin_middle_name', $currentAdmin->admin_middle_name ?? '') }}">
                            </div>
                            <div class="form-group">
                                <label>Mobile Number</label>
                                <input type="text" name="phone_number" value="{{ old('phone_number', $currentAdmin->phone_number ?? '') }}">
                            </div>
                            <div class="form-group full-width">
                                <label>Email</label>
                                <input type="email" name="admin_email" value="{{ old('admin_email', $currentAdmin->admin_email ?? '') }}">
                            </div>
                            <div class="form-group full-width settings-file-group">
                                <label>Profile Photo</label>
                                <input id="account-photo-input" type="file" name="photo" accept="image/*" class="settings-file-input">
                                <p class="settings-file-hint">Uploading a new photo will replace the current admin photo in Supabase.</p>
                            </div>
                            <div class="form-footer full-width">
                                <button type="button" class="btn-discard" onclick="resetForm('account-form')">Discard Changes</button>
                                <button type="submit" class="btn-save">Save Profile Information</button>
                            </div>
                        </form>
                    </div>
                {{-- @enddefault --}}
            @endswitch
        </div>

        <div class="settings-right-sidebar">
            <p class="text-titles">Settings</p>
            
            <span class="sidebar-group-label">Admin Account</span>
            <a href="?section=account" class="settings-nav-item {{ $section == 'account' ? 'active' : '' }}">
                Account Information
            </a>
            <a href="?section=security" class="settings-nav-item {{ $section == 'security' ? 'active' : '' }}">
                Security
            </a>

            <span class="sidebar-group-label">Admin Control</span>
            <a href="?section=roles" class="settings-nav-item {{ $section == 'roles' ? 'active' : '' }}">
                Admin Roles
            </a>
            <a href="?section=add-admin" class="settings-nav-item {{ $section == 'add-admin' ? 'active' : '' }}">
                Add New Admin
            </a>

            <span class="sidebar-group-label">System Settings</span>
            <a href="?section=notifications" class="settings-nav-item {{ $section == 'notifications' ? 'active' : '' }}">
                Notification Settings
            </a>
            <a href="?section=download" class="settings-nav-item {{ $section == 'download' ? 'active' : '' }}">
                Download Data
            </a>
        </div>
    </div>

    <script>
        // Toggle 2FA settings visibility
        document.addEventListener('DOMContentLoaded', function(){
            var t2 = document.getElementById('toggle-2fa');
            if(t2){
                t2.addEventListener('change', function(e){
                    var settings = document.getElementById('2fa-settings');
                    if(this.checked) settings.style.display = 'block'; else settings.style.display = 'none';
                });
                // initialize
                if(t2.checked){
                    var s = document.getElementById('2fa-settings'); if(s) s.style.display = 'block';
                }
            }
        });

        function resetForm(id){
            var f = document.getElementById(id);
            if(f) f.reset();
        }

        function fakeSave(msg){
            alert(msg);
        }
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const removeBtn = document.getElementById('remove-photo-btn');
            const form = document.getElementById('account-form');
            const removeFlag = document.getElementById('remove-photo-flag');

            if (removeBtn && form && removeFlag) {
                removeBtn.addEventListener('click', function() {
                    if (confirm('Are you sure you want to remove your profile photo?')) {
                        removeFlag.value = '1';
                        form.submit();
                    }
                });
            }
        });
    </script>
</body>
</html>