@php
    $admin = $currentAdmin ?? null;

    if (! $admin && request()->hasSession()) {
        $adminId = request()->session()->get('admin_id');

        if ($adminId) {
            $admin = \App\Models\Admin::query()->find($adminId);
        }
    }

    $adminName = trim((string) ($currentAdminName ?? request()->session()->get('admin_name', '')));

    if ($admin) {
        $resolvedName = trim((string) (($admin->admin_first_name ?? '') . ' ' . ($admin->admin_last_name ?? '')));

        if ($resolvedName !== '') {
            $adminName = $resolvedName;
        }
    }

    if ($adminName === '') {
        $adminName = 'Admin';
    }

    $photoUrl = $currentAdminPhotoUrl ?? null;

    if (! $photoUrl && $admin?->photo) {
        $photoPath = trim((string) $admin->photo);

        if (preg_match('/^https?:\/\//i', $photoPath)) {
            $photoUrl = $photoPath;
        } elseif (str_starts_with($photoPath, '/storage/')) {
            $photoUrl = $photoPath;
        } elseif (str_starts_with($photoPath, 'storage/')) {
            $photoUrl = '/' . $photoPath;
        } elseif (str_starts_with($photoPath, '/')) {
            $photoUrl = $photoPath;
        } else {
            $photoUrl = \Illuminate\Support\Facades\Storage::disk('supabase_admin')->url($photoPath);
        }
    }

    $firstName = trim((string) ($admin?->admin_first_name ?? ''));
    $lastName = trim((string) ($admin?->admin_last_name ?? ''));
    $initials = '';

    if ($firstName !== '') {
        $initials .= strtoupper(mb_substr($firstName, 0, 1));
    }

    if ($lastName !== '') {
        $initials .= strtoupper(mb_substr($lastName, 0, 1));
    }

    if ($initials === '') {
        $nameParts = preg_split('/\s+/', trim($adminName)) ?: [];

        if (count($nameParts) >= 2) {
            $initials = strtoupper(mb_substr($nameParts[0], 0, 1) . mb_substr($nameParts[count($nameParts) - 1], 0, 1));
        } else {
            $initials = strtoupper(mb_substr($adminName, 0, 2));
        }
    }
    $displayFirstName = $firstName !== '' ? $firstName : (explode(' ', $adminName)[0] ?? 'Admin');
@endphp

<nav class="nav-main">
    <img class="nav-logo" src="{{ asset('assets/logos/NULIPA_AAO_White.png') }}" alt="LumiNUs Logo">

    <div class="nav-admin-profile">
        <!-- 1. The Clickable Toggle Area -->
        <div class="nav-admin-toggle" id="profileToggle">
            <div class="nav-admin-avatar {{ $photoUrl ? 'has-photo' : 'is-initials' }}">
                @if ($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $adminName }} profile photo">
                @else
                    <span>{{ $initials }}</span>
                @endif
            </div>

            <div class="nav-admin-greeting">
                <span>Hello,</span>
                <strong>{{ $displayFirstName }}</strong>
            </div>
        </div>

        <!-- 2. The Dropdown Menu -->
        <div class="profile-dropdown" id="profileDropdown">
            <!-- You can add more links here later (e.g., Profile Settings, Change Password) -->
            {{-- <a href="#" class="dropdown-item">Profile Settings</a> --}}
            
            <!-- Sign Out Link (Updated to match your old UI) -->
            <a href="{{ route('admin.logout') }}" class="dropdown-item logout-btn">Sign Out</a>
        </div>
    </div>
</nav>

<style>
    .nav-logo {
        height: 100%;
        width: auto;
        padding: 10px;
        margin-left: 30px;
    }

    /* =========================================
       DROPDOWN STYLES
       ========================================= */
    
    /* Anchor the dropdown to the profile container */
    .nav-admin-profile {
        position: relative; 
    }

    /* The clickable area */
    .nav-admin-toggle {
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        padding: 6px 10px;
        border-radius: 6px;
        transition: background-color 0.2s ease;
    }

    /* Hover effect (Adjust the rgba color to match your navbar's background) */
    .nav-admin-toggle:hover {
        background-color: rgba(255, 255, 255, 0.1); 
    }

    /* The hidden dropdown menu */
    .profile-dropdown {
        display: none;
        position: absolute;
        top: 100%; /* Positions it below the toggle */
        right: 0;
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        min-width: 180px;
        z-index: 1000;
        margin-top: 8px;
        overflow: hidden;
    }

    /* Class added via JS to show the menu */
    .profile-dropdown.active {
        display: block;
    }

    /* Individual dropdown links */
    .dropdown-item {
        display: block;
        padding: 10px 16px;
        text-decoration: none;
        color: #4a5568;
        width: 100%;
        text-align: left;
        background: none;
        border: none;
        cursor: pointer;
        font-size: 14px;
        font-family: inherit;
        transition: background-color 0.2s, color 0.2s;
        box-sizing: border-box;
    }

    .dropdown-item:hover {
        background-color: #f7fafc;
        color: #2d3748;
    }

    /* Sign Out link specific styling (Red to indicate destructive action) */
    .logout-btn {
        color: #e53e3e; 
        border-top: 1px solid #edf2f7; /* Separator line above Sign Out */
    }

    .logout-btn:hover {
        background-color: #fff5f5;
        color: #c53030;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('profileToggle');
        const dropdown = document.getElementById('profileDropdown');

        if (toggle && dropdown) {
            // 1. Open/Close dropdown when clicking the profile area
            toggle.addEventListener('click', function (event) {
                event.stopPropagation(); // Prevents the click from bubbling up to the document listener below
                dropdown.classList.toggle('active');
            });

            // 2. Close the dropdown if the user clicks anywhere else on the page
            document.addEventListener('click', function (event) {
                if (!dropdown.contains(event.target) && !toggle.contains(event.target)) {
                    dropdown.classList.remove('active');
                }
            });
        }
    });
</script>