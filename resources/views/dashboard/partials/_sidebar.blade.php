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
        <a href="/admin/dashboard" class="nav-item active">
            <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
        </a>
        @endif
        
        @if(isset($accessibleModules['directory']))
        <a href="/admin/directory" class="nav-item">
            <i class="fa-solid fa-users"></i><span>Alumni Directory</span>
        </a>
        @endif
        
        @if(isset($accessibleModules['announcements']))
        <a href="{{ route('announcements.index') }}" class="nav-item">
            <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
        </a>
        @endif
        
        @if(isset($accessibleModules['events']))
        <a href="{{ route('events.index') }}" class="nav-item">
            <i class="fa-solid fa-calendar-check"></i><span>Events</span>
        </a>
        @endif
        
        @if(isset($accessibleModules['perks']))
        <a href="{{ route('perks.index') }}" class="nav-item">
            <i class="fa-solid fa-gift"></i><span>Perks & Discounts</span>
        </a>
        @endif
        
        @if(isset($accessibleModules['tracer']))
        <a href="/admin/alumni_tracer" class="nav-item">
            <i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span>
        </a>
        @endif
        
        @if(isset($accessibleModules['messages']))
        <a href="/admin/messages" class="nav-item">
            <i class="fa-solid fa-envelope"></i><span>Messages</span>
        </a>
        @endif
        
        @if(isset($accessibleModules['settings']))
        <a href="{{ route('admin.settings') }}" class="nav-item">
            <i class="fa-solid fa-gear"></i><span>Settings</span>
        </a>
        @endif
    </nav>
</aside>