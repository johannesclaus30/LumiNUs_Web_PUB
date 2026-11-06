<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/events_modern.css">

    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Add a variable to control the fill percentage */
        input[type=range].geo-radius-slider {
            -webkit-appearance: none;
            width: 100%;
            background: transparent; 
        }

        /* Webkit (Chrome, Safari, Edge) */
        input[type=range].geo-radius-slider::-webkit-slider-runnable-track {
            width: 100%;
            height: 8px;
            cursor: pointer;
            background: linear-gradient(to right, #32418c var(--fill-percent, 12.5%), #e2e8f0 var(--fill-percent, 12.5%));
            border-radius: 4px;
        }

        input[type=range].geo-radius-slider::-webkit-slider-thumb {
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #32418c;
            cursor: pointer;
            -webkit-appearance: none;
            margin-top: -6px; /* Centers thumb on track */
        }

        /* Firefox */
        input[type=range].geo-radius-slider::-moz-range-track {
            width: 100%;
            height: 8px;
            cursor: pointer;
            background: #e2e8f0;
            border-radius: 4px;
        }

        input[type=range].geo-radius-slider::-moz-range-progress {
            background-color: #32418c;
            height: 8px;
            border-radius: 4px;
        }

        input[type=range].geo-radius-slider::-moz-range-thumb {
            height: 20px;
            width: 20px;
            border-radius: 50%;
            background: #ffffff;
            border: 3px solid #32418c;
            cursor: pointer;
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

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-calendar-check"></i>
                            Events
                        </h1>
                        <p class="page-subtitle">Organize alumni gatherings, webinars, and hybrid meet-ups</p>
                    </div>
                    <div class="header-actions">
                        @if (!request()->routeIs('events.archived'))
                            <a href="{{ route('events.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Add New Event</span>
                            </a>
                            
                            <!-- Filter Buttons - Only show on active events page -->
                            <div class="filter-buttons" style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('events.index', ['filter' => 'all']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'all' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    All
                                </a>
                                <a href="{{ route('events.index', ['filter' => 'In-Person']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'In-Person' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fa-solid fa-building" style="font-size: 0.75rem;"></i> On-Site
                                </a>
                                <a href="{{ route('events.index', ['filter' => 'Online']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'Online' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fa-solid fa-globe" style="font-size: 0.75rem;"></i> Online
                                </a>
                                <a href="{{ route('events.index', ['filter' => 'Hybrid']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'Hybrid' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    <i class="fa-solid fa-rotate" style="font-size: 0.75rem;"></i> Hybrid
                                </a>
                            </div>
                        @endif
                        
                        <a id="archiveToggleBtn"
                        href="{{ route('events.archived') }}"
                        class="btn btn-secondary archived-toggle">
                            <i class="fa-solid fa-box-archive"></i> 
                            <span class="btn-text">Archived</span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-calendar-check"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalEvents ?? 0 }}</span>
                        <span class="stat-label">Total Events</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $activeEvents ?? 0 }}</span>
                        <span class="stat-label">Active Events</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon archived">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $archivedEvents ?? 0 }}</span>
                        <span class="stat-label">Archived</span>
                    </div>
                </div>
            </div>

            <!-- Events Card Grid -->
            <div class="events-grid">
                @forelse ($events as $event)
                    <article class="event-card" data-event-id="{{ $event->id }}">
                        <div class="event-card-wrapper">
                            <div class="event-card-header">
                                
                                @php
                                    $isRestored = !empty($event->is_manually_restored);
                                    $isActive   = ($event->status == 1 || is_null($event->status));
                                @endphp

                                <div class="event-status-badge {{ $isRestored ? 'restored' : ($isActive ? 'active' : 'archived') }}">
                                    <i class="fa-solid {{ $isRestored ? 'fa-rotate-left' : 'fa-circle' }}"></i>
                                    <span>
                                        @if($isRestored)
                                            Restored
                                        @elseif($isActive)
                                            Active
                                        @else
                                            Archived
                                        @endif
                                    </span>
                                </div>

                                <div class="event-type-badge badge-{{ strtolower($event->event_type) }}">
                                    {{ $event->event_type }}
                                </div>
                            </div>

                            @if(!empty($event->is_manually_restored))
                                <div class="event-restored-banner">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>
                                        Restored for admin access — <strong>hidden from alumni</strong>.
                                        @if($event->manually_restored_at)
                                            Restored {{ $event->manually_restored_at->diffForHumans() }}.
                                        @endif
                                    </span>
                                </div>
                            @endif
                            
                            <div class="event-card-body">
                                <div class="event-content">
                                    <h3 class="event-title">{{ $event->title }}</h3>
                                    @if($event->description)
                                        <p class="event-description">{{ Str::limit($event->description, 120) }}</p>
                                    @endif
                                </div>

                                <!-- Event Meta (date, platform, venue) -->
                                <div class="event-meta">
                                    <div class="meta-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span class="event-date-display" 
                                            data-start-utc="{{ $event->start_date ? $event->start_date->toIso8601String() : '' }}"
                                            data-end-utc="{{ $event->end_date ? $event->end_date->toIso8601String() : '' }}">
                                            {{ $event->start_date ? $event->start_date->format('M d, Y') : 'N/A' }}
                                            @if($event->end_date) – {{ $event->end_date->format('M d, Y') }}@endif
                                        </span>
                                    </div>
                                    @if(in_array($event->event_type, ['Online','Hybrid']))
                                        <div class="meta-item">
                                            <i class="fa-solid fa-globe"></i>
                                            <span>{{ $event->platform ?: 'No platform' }}</span>
                                        </div>
                                    @endif
                                    @if(in_array($event->event_type, ['In-Person','Hybrid']))
                                        <div class="meta-item">
                                            <i class="fa-solid fa-location-dot"></i>
                                            <span>{{ $event->venue ? $event->venue->name : 'No venue' }}</span>
                                        </div>
                                    @endif
                                </div>

                                {{-- Two‑column split when a map is available --}}
                                @if(in_array($event->event_type, ['In-Person','Hybrid']) && $event->venue && $event->venue->latitude && $event->venue->longitude)
                                    <div class="event-attachments-map-split">
                                        {{-- Left: Media gallery --}}
                                        <div class="split-left">
                                            @include('partials.event-media', ['event' => $event])
                                        </div>

                                        {{-- Right: Map --}}
                                        <div class="event-mini-map-wrapper split-right" style="cursor: pointer;">
                                            <div id="venue-map-{{ $event->id }}"
                                                class="venue-mini-map"
                                                data-lat="{{ $event->venue->latitude }}"
                                                data-lng="{{ $event->venue->longitude }}"
                                                onclick="openVenueModal(
                                                    '{{ $event->venue->latitude }}',
                                                    '{{ $event->venue->longitude }}',
                                                    '{{ addslashes($event->title) }}',
                                                    '{{ addslashes($event->venue->name ?? 'No venue name') }}',
                                                    '{{ addslashes($event->venue->address ?? 'No address') }}',
                                                    '{{ $event->max_capacity }}',
                                                    '{{ $event->event_type }}',
                                                    '{{ $event->start_date ? $event->start_date->format('M d, Y') : 'TBA' }}',
                                                    '{{ $event->end_date ? $event->end_date->format('M d, Y') : 'TBA' }}'
                                                )">
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    {{-- No map — full-width gallery --}}
                                    @include('partials.event-media', ['event' => $event])
                                @endif
                                
                            </div>
                            
                            <div class="event-card-footer">
                                <div class="event-analytics">
                                    <div class="analytics-item">
                                        <i class="fa-solid fa-users"></i>
                                        @php
                                            $registeredCount = $event->registrations_count;
                                            $maxCapacity = $event->max_capacity;
                                            $fillPercentage = $maxCapacity > 0 ? ($registeredCount / $maxCapacity) * 100 : 0;
                                        @endphp
                                        
                                        @if($registeredCount >= $maxCapacity)
                                            <span class="capacity-full-badge">Maximum Capacity Reached</span>
                                        @elseif($fillPercentage >= 80)
                                            <span class="capacity-warning">
                                                <i class="fa-solid fa-circle-exclamation"></i> 
                                                {{ $registeredCount }}/{{ $maxCapacity }} registered
                                            </span>
                                        @else
                                            <span>{{ $registeredCount }}/{{ $maxCapacity }} registered</span>
                                        @endif
                                    </div>
                                </div>

                                <div class="event-actions">
                                    
                                    @if ((int) $event->status === 1 || is_null($event->status))
                                        {{-- Geo-Invite (Promote) — only for In-Person / Hybrid with venue coords,
                                            and HIDDEN for manually-restored events (they're invisible to alumni) --}}
                                        @if(
                                            in_array($event->event_type, ['In-Person','Hybrid'])
                                            && $event->venue
                                            && $event->venue->latitude
                                            && $event->venue->longitude
                                            && empty($event->is_manually_restored)
                                        )
                                            <button type="button"
                                                class="btn-action btn-promote"
                                                title="Promote this event — invite nearby alumni"
                                                onclick="openGeoInviteModal({{ $event->id }})">
                                                <i class="fa-solid fa-bullhorn"></i>
                                            </button>
                                        @endif

                                        {{-- Active: Show Edit + Archive --}}
                                        <a href="{{ route('events.edit', $event) }}" class="btn-action btn-edit" title="Edit Event">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('events.destroy', $event) }}" 
                                            method="POST" 
                                            class="inline-form"
                                            data-confirm="Archive this event?">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-archive" title="Archive Event">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                        {{-- Manage Event Registrations Button --}}
                                        <a href="{{ route('events.registrations', $event) }}" class="btn-action btn-manage" title="Manage Registrations">
                                            <i class="fa-solid fa-users-gear"></i>
                                        </a>
                                    @else
                                        {{-- Archived: Show Restore + Permanent Delete --}}
                                        <form action="{{ route('events.restore', $event) }}" 
                                            method="POST" 
                                            class="inline-form"
                                            data-confirm="Restore this event?">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="btn-action btn-unarchive" title="Restore Event">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- Permanent Delete (only for archived) --}}
                                        <form action="{{ route('events.permanent-delete', $event->id) }}" 
                                            method="POST" class="inline-form"
                                            data-confirm="Permanently delete this event? This cannot be undone. All associated data including images will be removed.">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete-permanent" title="Delete Permanently">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </article>
                @empty
                    <div class="empty-state full-width">
                        <div class="empty-icon-wrapper">
                            <div class="empty-icon">
                                <i class="fa-solid fa-calendar-xmark"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No events found</h3>
                        <p class="empty-description">
                            @if (request()->routeIs('events.archived'))
                                There are no archived events at the moment.
                            @else
                                Start planning your first alumni event or gathering.
                            @endif
                        </p>
                        @if (!request()->routeIs('events.archived'))
                            <a href="{{ route('events.create') }}" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Create First Event</span>
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($events->hasPages())
            <div class="pagination-wrapper">
                {{ $events->links() }}
            </div>
            @endif

            <!-- Venue Map Modal -->
            <div id="venueModal" class="venue-modal-overlay" onclick="closeVenueModal(event)">
                <div class="venue-modal-container" onclick="event.stopPropagation()">
                    <!-- Modal Header -->
                    <div class="venue-modal-header">
                        <div class="venue-modal-title-section">
                            <h2 class="venue-modal-title" id="venueModalEventTitle">
                                <i class="fa-solid fa-location-dot"></i> 
                                Event Venue
                            </h2>
                            <p class="venue-modal-subtitle" id="venueModalEventDate"></p>
                        </div>
                        <button class="venue-modal-close" onclick="closeVenueModal()" title="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    
                    <!-- Scrollable Body -->
                    <div class="venue-modal-body">
                        <!-- Map -->
                        <div class="venue-modal-map" id="venueModalMap"></div>
                    </div>
                    
                    <!-- Venue Details Footer -->
                    <div class="venue-modal-footer">
                        <div class="venue-modal-info-grid">
                            <div class="venue-modal-info-item">
                                <div class="venue-info-icon">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div class="venue-info-content">
                                    <span class="venue-info-label">Venue</span>
                                    <span class="venue-info-value" id="venueModalVenueName">-</span>
                                </div>
                            </div>
                            <div class="venue-modal-info-item">
                                <div class="venue-info-icon">
                                    <i class="fa-solid fa-map-pin"></i>
                                </div>
                                <div class="venue-info-content">
                                    <span class="venue-info-label">Address</span>
                                    <span class="venue-info-value" id="venueModalAddress">-</span>
                                </div>
                            </div>
                            <div class="venue-modal-info-item">
                                <div class="venue-info-icon">
                                    <i class="fa-solid fa-users"></i>
                                </div>
                                <div class="venue-info-content">
                                    <span class="venue-info-label">Capacity</span>
                                    <span class="venue-info-value" id="venueModalCapacity">-</span>
                                </div>
                            </div>
                            <div class="venue-modal-info-item">
                                <div class="venue-info-icon">
                                    <i class="fa-solid fa-globe"></i>
                                </div>
                                <div class="venue-info-content">
                                    <span class="venue-info-label">Event Mode</span>
                                    <span class="venue-info-value" id="venueModalEventType">-</span>
                                </div>
                            </div>
                        </div>
                        <div class="venue-modal-actions">
                            <button class="btn btn-secondary" onclick="closeVenueModal()">
                                <i class="fa-solid fa-xmark"></i> Close
                            </button>
                            <a href="#" class="btn btn-primary" id="venueModalDirections" target="_blank" rel="noopener noreferrer">
                                <i class="fa-solid fa-map-location-dot"></i> Get Directions
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ============================================================ --}}
            {{-- GEO-INVITE MODAL                                             --}}
            {{-- Only usable for In-Person / Hybrid events with a venue       --}}
            {{-- ============================================================ --}}
            <div id="geoInviteModal" class="geo-modal-overlay" onclick="closeGeoInviteModal(event)">
                <div class="geo-modal-container" onclick="event.stopPropagation()">

                    <!-- ============ HEADER ============ -->
                    <div class="geo-modal-header">
                        <div class="geo-modal-title-section">
                            <h2 class="geo-modal-title">
                                <i class="fa-solid fa-bullhorn"></i>
                                Promote Event to Nearby Alumni
                            </h2>
                            <p class="geo-modal-subtitle" id="geoModalEventTitle">
                                Loading event details…
                            </p>
                        </div>
                        <button class="geo-modal-close" onclick="closeGeoInviteModal()" title="Close">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- ============ BODY ============ -->
                    <div class="geo-modal-body">

                        <!-- LEFT: MAP -->
                        <div class="geo-map-panel">
                            <div id="geoInviteMap" class="geo-modal-map"></div>

                            <!-- Map legend -->
                            <div class="geo-map-legend">
                                <div class="legend-item">
                                    <span class="legend-dot legend-venue"></span> Venue
                                </div>
                                <div class="legend-item">
                                    <span class="legend-dot legend-nearby"></span> Nearby alumni
                                </div>
                                <div class="legend-item">
                                    <span class="legend-dot legend-far"></span> Outside radius
                                </div>
                            </div>
                        </div>

                        <!-- RIGHT: CONTROLS + LIST -->
                        <div class="geo-controls-panel">

                            <!-- Radius slider block -->
                            <div class="geo-section">
                                <label class="geo-section-label">
                                    <i class="fa-solid fa-circle-dot"></i> Radius around venue
                                </label>

                                <div class="geo-radius-display">
                                    <span class="geo-radius-value" id="geoRadiusValue">25</span>
                                    <span class="geo-radius-unit">km</span>
                                </div>

                                <input type="range"
                                    id="geoRadiusSlider"
                                    class="geo-radius-slider"
                                    min="1"
                                    max="200"
                                    step="1"
                                    value="25"
                                    oninput="onRadiusSliderChange(this.value)">

                                <div class="geo-radius-presets">
                                    <button type="button" onclick="setGeoRadius(5)"    data-radius="5">5 km</button>
                                    <button type="button" onclick="setGeoRadius(10)"   data-radius="10">10 km</button>
                                    <button type="button" onclick="setGeoRadius(25)"   data-radius="25" class="active">25 km</button>
                                    <button type="button" onclick="setGeoRadius(50)"   data-radius="50">50 km</button>
                                    <button type="button" onclick="setGeoRadius(100)"  data-radius="100">100 km</button>
                                    <!-- 👇 ADD THIS LINE -->
                                    <button type="button" onclick="setGeoRadius(200)"  data-radius="200">200 km</button>
                                </div>

                                <label class="geo-checkbox-row">
                                    <input type="checkbox"
                                        id="geoSkipRecent"
                                        onchange="onSkipRecentChange(this.checked)">
                                    <span>Skip alumni invited in the last 7 days</span>
                                </label>
                            </div>

                            <!-- Live stats -->
                            <div class="geo-stats-grid">
                                <div class="geo-stat">
                                    <span class="geo-stat-value" id="geoStatCount">—</span>
                                    <span class="geo-stat-label">Nearby alumni</span>
                                </div>
                                <div class="geo-stat">
                                    <span class="geo-stat-value" id="geoStatSelected">—</span>
                                    <span class="geo-stat-label">Will be invited</span>
                                </div>
                                <div class="geo-stat">
                                    <span class="geo-stat-value geo-stat-muted" id="geoStatNoAddress">—</span>
                                    <span class="geo-stat-label">No address on file</span>
                                </div>
                            </div>

                            <!-- Alumni list -->
                            <div class="geo-list-header">
                                <span class="geo-list-title">
                                    <i class="fa-solid fa-users"></i> Alumni within radius
                                </span>
                                <div class="geo-list-actions">
                                    <button type="button" class="geo-list-btn" onclick="geoSelectAll(true)">All</button>
                                    <button type="button" class="geo-list-btn" onclick="geoSelectAll(false)">None</button>
                                </div>
                            </div>

                            <div class="geo-alumni-list" id="geoAlumniList">
                                <div class="geo-alumni-empty">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                    <p>Loading nearby alumni…</p>
                                </div>
                            </div>

                            <!-- Optional custom message -->
                            <div class="geo-section geo-section-msg">
                                <label class="geo-section-label">
                                    <i class="fa-solid fa-comment-dots"></i>
                                    Add a personal note (optional)
                                </label>
                                <textarea
                                    id="geoCustomMessage"
                                    class="geo-textarea"
                                    maxlength="2000"
                                    rows="3"
                                    placeholder="e.g., We'd love to see you there! Bring a friend from your batch…"></textarea>
                                <div class="geo-char-count">
                                    <span id="geoCharCount">0</span> / 2000
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ============ FOOTER ============ -->
                    <div class="geo-modal-footer">
                        <div class="geo-footer-info">
                            <i class="fa-solid fa-circle-info"></i>
                            <span id="geoFooterHint">Ready to send invitations.</span>
                        </div>
                        <div class="geo-footer-actions">
                            <button class="btn btn-secondary" onclick="closeGeoInviteModal()">
                                <i class="fa-solid fa-xmark"></i> Cancel
                            </button>
                            <button class="btn btn-promote-confirm"
                                id="geoInviteSendBtn"
                                onclick="sendGeoInvitations()"
                                disabled>
                                <i class="fa-solid fa-paper-plane"></i>
                                <span id="geoInviteSendLabel">Send Invitations</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <!-- ============================================
     EVENT MEDIA LIGHTBOX GALLERY
     ============================================ -->
    <div id="eventMediaLightbox" class="lightbox-overlay" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="lightbox-backdrop" data-event-lightbox-close></div>

        <div class="lightbox-shell">
            <div class="lightbox-topbar">
                <div class="lightbox-counter">
                    <i class="fa-regular fa-images"></i>
                    <span id="eventLightboxCounter">1 / 1</span>
                </div>
                <div class="lightbox-topbar-actions">
                    <a id="eventLightboxDownload"
                    class="lightbox-btn"
                    href="#"
                    download
                    title="Download">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button type="button"
                            class="lightbox-btn"
                            id="eventLightboxClose"
                            title="Close (Esc)"
                            data-event-lightbox-close>
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <button type="button"
                    class="lightbox-nav lightbox-prev"
                    id="eventLightboxPrev"
                    title="Previous (←)"
                    aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="lightbox-stage" id="eventLightboxStage">
                <img id="eventLightboxImage"
                    class="lightbox-media"
                    alt="Event media"
                    style="display:none;">
                <video id="eventLightboxVideo"
                    class="lightbox-media"
                    controls
                    playsinline
                    preload="metadata"
                    style="display:none;"></video>
            </div>

            <button type="button"
                    class="lightbox-nav lightbox-next"
                    id="eventLightboxNext"
                    title="Next (→)"
                    aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="lightbox-thumbs" id="eventLightboxThumbs"></div>
        </div>
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

    <script>
/* =============================================================
   GEO-INVITE MODAL — Full Client Logic
   Depends on: Leaflet (already loaded in <head>)
   ============================================================= */
(function () {
    'use strict';

    // -------------------------------------------------------------
    // State
    // -------------------------------------------------------------
    const state = {
        eventId:       null,
        eventTitle:    '',
        venue:         null,       // { name, address, latitude, longitude }
        radiusKm:      25,
        skipRecent:    false,
        alumni:        [],         // full list from server
        selectedIds:   new Set(),  // alumni.id currently checked
        map:           null,
        venueMarker:   null,
        radiusCircle:  null,
        alumniLayer:   L.layerGroup(),
        previewAbort:  null,       // AbortController for pending fetch
        previewTimer:  null,       // debounce timer
        isSending:     false,
    };

    // -------------------------------------------------------------
    // CSRF
    // -------------------------------------------------------------
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content
              || '{{ csrf_token() }}';

    // -------------------------------------------------------------
    // API Endpoints (built via Laravel route names)
    // -------------------------------------------------------------
    const ROUTES = {
        preview: (id) => `/admin/events/${id}/geo-invite/preview`,
        send:    (id) => `/admin/events/${id}/geo-invite/send`,
    };

    // =============================================================
    // OPEN / CLOSE
    // =============================================================
    window.openGeoInviteModal = function (eventId) {
        state.eventId     = eventId;
        state.radiusKm    = 25;
        state.skipRecent  = false;
        state.alumni      = [];
        state.selectedIds = new Set();

        // Reset UI
        document.getElementById('geoInviteModal').classList.add('active');
        document.body.style.overflow = 'hidden';

        document.getElementById('geoRadiusSlider').value = 25;
        document.getElementById('geoRadiusValue').textContent = '25';

        updateSliderFill(25); 

        document.getElementById('geoSkipRecent').checked = false;
        document.getElementById('geoCustomMessage').value = '';
        document.getElementById('geoCharCount').textContent = '0';
        document.getElementById('geoStatCount').textContent = '—';
        document.getElementById('geoStatSelected').textContent = '—';
        document.getElementById('geoStatNoAddress').textContent = '—';
        document.getElementById('geoFooterHint').textContent = 'Ready to send invitations.';
        document.getElementById('geoInviteSendBtn').disabled = true;
        document.getElementById('geoAlumniList').innerHTML = `
            <div class="geo-alumni-empty">
                <i class="fa-solid fa-spinner fa-spin"></i>
                <p>Loading nearby alumni…</p>
            </div>`;

        // Fetch preview
        fetchPreview();
    };

    window.closeGeoInviteModal = function (event) {
        if (event && event.target !== document.getElementById('geoInviteModal')) return;

        // Prevent closing while sending
        if (state.isSending) return;

        document.getElementById('geoInviteModal').classList.remove('active');
        document.body.style.overflow = '';

        // Clean up map so it re-initializes fresh next time
        if (state.map) {
            state.map.remove();
            state.map = null;
            state.venueMarker = null;
            state.radiusCircle = null;
            state.alumniLayer = L.layerGroup();
        }
    };

    // =============================================================
    // RADIUS HANDLERS
    // =============================================================
        // Helper function to update the blue line fill
    function updateSliderFill(value) {
        const slider = document.getElementById('geoRadiusSlider');
        const min = parseFloat(slider.min) || 1;
        const max = parseFloat(slider.max) || 200;
        const percent = ((value - min) / (max - min)) * 100;
        slider.style.setProperty('--fill-percent', `${percent}%`);
    }

    window.onRadiusSliderChange = function (value) {
        state.radiusKm = parseFloat(value);
        document.getElementById('geoRadiusValue').textContent = value;
        
        updateActivePreset(state.radiusKm);
        updateSliderFill(state.radiusKm); // 👈 Update the blue line
        
        schedulePreview(300);
    };

    window.setGeoRadius = function (km) {
        state.radiusKm = km;
        document.getElementById('geoRadiusSlider').value = km;
        document.getElementById('geoRadiusValue').textContent = km;
        
        updateActivePreset(km);
        updateSliderFill(km); // 👈 Update the blue line
        
        schedulePreview(150);
    };

    function updateActivePreset(km) {
        document.querySelectorAll('.geo-radius-presets button').forEach(btn => {
            btn.classList.toggle('active', parseFloat(btn.dataset.radius) === km);
        });
    }

    window.onSkipRecentChange = function (checked) {
        state.skipRecent = !!checked;
        schedulePreview(150);
    };

    // =============================================================
    // DEBOUNCED PREVIEW
    // =============================================================
    function schedulePreview(delay = 300) {
        clearTimeout(state.previewTimer);
        state.previewTimer = setTimeout(fetchPreview, delay);
    }

    async function fetchPreview() {
        if (!state.eventId) return;

        // Cancel any in-flight request
        if (state.previewAbort) state.previewAbort.abort();
        state.previewAbort = new AbortController();

        try {
            const url = new URL(ROUTES.preview(state.eventId), window.location.origin);
            url.searchParams.set('radius_km', state.radiusKm);
            url.searchParams.set('skip_recent', state.skipRecent ? 1 : 0);

            const res = await fetch(url, {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
                signal: state.previewAbort.signal,
            });

            const contentType = res.headers.get('content-type') || '';
            let data;
            if (contentType.includes('application/json')) {
                data = await res.json();
            } else {
                const text = await res.text();
                console.error('[GeoInvite] Non-JSON response:', text.slice(0, 500));
                showListError(`Server error (${res.status}). Check storage/logs/laravel.log.`);
                return;
            }

            if (!res.ok || !data.success) {
                const msg = data.error || data.message || `Failed to load nearby alumni (${res.status}).`;
                console.warn('[GeoInvite] Preview failed:', msg, data);
                showListError(msg);
                return;
            }

            // Update state
            state.eventTitle = data.event?.title || '';
            state.venue      = data.venue;
            state.alumni     = data.alumni || [];
            state.selectedIds = new Set(state.alumni.map(a => a.id));

            // Update header title
            document.getElementById('geoModalEventTitle').innerHTML =
                `<i class="fa-solid fa-calendar-check"></i> ${escapeHtml(state.eventTitle)}`;

            // Update stats
            document.getElementById('geoStatCount').textContent = data.count;
            document.getElementById('geoStatNoAddress').textContent = data.missing_address_count ?? 0;
            updateSelectedCount();

            // Render UI
            renderMap();
            renderAlumniList();
            updateSendButton();

        } catch (err) {
            if (err.name === 'AbortError') return; // cancelled on purpose
            console.error('[GeoInvite] Preview error:', err);
            showListError('Network error. Please try again.');
        }
    }

    // =============================================================
    // MAP RENDERING
    // =============================================================
    function renderMap() {
        if (!state.venue || state.venue.latitude == null || state.venue.longitude == null) return;

        const vLat = parseFloat(state.venue.latitude);
        const vLng = parseFloat(state.venue.longitude);

        // Init map once
        if (!state.map) {
            const mapEl = document.getElementById('geoInviteMap');
            // Defensive: ensure the container has a real height BEFORE Leaflet mounts
            if (mapEl && mapEl.offsetHeight < 100) {
                mapEl.style.height = '520px';
            }

            state.map = L.map('geoInviteMap', {
                center: [vLat, vLng],
                zoom: 12,
                scrollWheelZoom: true,
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(state.map);

            state.alumniLayer.addTo(state.map);

            // Force Leaflet to recompute size after the modal finishes painting
            setTimeout(() => {
                if (state.map) state.map.invalidateSize();
            }, 250);
        }

        // Clear old markers (but keep venue + circle)
        state.alumniLayer.clearLayers();

        // ---- Venue marker ----
        if (state.venueMarker) state.map.removeLayer(state.venueMarker);

        const venueIcon = L.divIcon({
            className: 'geo-venue-marker',
            html: `<div class="geo-venue-pin"><i class="fa-solid fa-location-dot"></i></div>`,
            iconSize: [40, 40],
            iconAnchor: [20, 40],
        });

        state.venueMarker = L.marker([vLat, vLng], { icon: venueIcon })
            .addTo(state.map)
            .bindPopup(`
                <strong>${escapeHtml(state.venue.name || 'Venue')}</strong><br>
                <small>${escapeHtml(state.venue.address || '')}</small>
            `);

        // ---- Radius circle ----
        if (state.radiusCircle) state.map.removeLayer(state.radiusCircle);

        state.radiusCircle = L.circle([vLat, vLng], {
            radius: state.radiusKm * 1000, // meters
            color: '#32418c',
            weight: 2,
            fillColor: '#32418c',
            fillOpacity: 0.08,
            dashArray: '6 6',
        }).addTo(state.map);

        // ---- Alumni markers ----
        state.alumni.forEach(alumnus => {
            if (alumnus.latitude == null || alumnus.longitude == null) return;

            const aLat = parseFloat(alumnus.latitude);
            const aLng = parseFloat(alumnus.longitude);

            const isSelected = state.selectedIds.has(alumnus.id);

            const markerIcon = L.divIcon({
                className: 'geo-alumni-marker',
                html: `<div class="geo-alumni-pin ${isSelected ? 'selected' : 'unselected'}"></div>`,
                iconSize: [16, 16],
                iconAnchor: [8, 8],
            });

            const marker = L.marker([aLat, aLng], { icon: markerIcon });

            marker.bindPopup(`
                <div class="geo-popup">
                    <strong>${escapeHtml(alumnus.full_name)}</strong><br>
                    <small>${escapeHtml(alumnus.municipality || '')}${alumnus.province ? ', ' + escapeHtml(alumnus.province) : ''}</small><br>
                    <span class="geo-popup-distance">
                        <i class="fa-solid fa-route"></i> ${alumnus.distance_km} km away
                    </span>
                </div>
            `);

            marker.alumniId = alumnus.id;
            marker.addTo(state.alumniLayer);
        });

        // Fit bounds so all markers are visible
        if (state.alumni.length > 0) {
            const points = [[vLat, vLng], ...state.alumni.map(a => [a.latitude, a.longitude])];
            state.map.fitBounds(points, { padding: [40, 40], maxZoom: 14 });
        } else {
            state.map.setView([vLat, vLng], 12);
        }

        // Always re-draw circle after fit
        setTimeout(() => { if (state.map) state.map.invalidateSize(); }, 120);
        setTimeout(() => { if (state.map) state.map.invalidateSize(); }, 400);
    }

    // =============================================================
    // LIST RENDERING
    // =============================================================
    function renderAlumniList() {
        const listEl = document.getElementById('geoAlumniList');

        if (state.alumni.length === 0) {
            listEl.innerHTML = `
                <div class="geo-alumni-empty">
                    <i class="fa-solid fa-user-slash"></i>
                    <p>No alumni found within <strong>${state.radiusKm} km</strong>.</p>
                    <small>Try increasing the radius.</small>
                </div>`;
            return;
        }

        // Sort by distance ascending
        const sorted = [...state.alumni].sort((a, b) => a.distance_km - b.distance_km);

        listEl.innerHTML = sorted.map(a => `
            <label class="geo-alumni-item" data-alumni-id="${a.id}">
                <input type="checkbox"
                    checked
                    onchange="toggleAlumnus(${a.id}, this.checked)">
                <div class="geo-alumni-avatar">
                    ${a.photo_url
                        ? `<img src="${escapeHtml(a.photo_url)}" alt="" onerror="this.parentElement.innerHTML='${getInitials(a.full_name)}'">`
                        : getInitials(a.full_name)}
                </div>
                <div class="geo-alumni-info">
                    <div class="geo-alumni-name">${escapeHtml(a.full_name)}</div>
                    <div class="geo-alumni-meta">
                        <span><i class="fa-solid fa-envelope"></i> ${escapeHtml(a.email)}</span>
                        ${a.program ? `<span><i class="fa-solid fa-graduation-cap"></i> ${escapeHtml(a.program)}</span>` : ''}
                    </div>
                </div>
                <div class="geo-alumni-distance">
                    <span class="distance-pill">${a.distance_km} km</span>
                </div>
            </label>
        `).join('');
    }

    // =============================================================
    // CHECKBOX HANDLING
    // =============================================================
    window.toggleAlumnus = function (alumniId, checked) {
        if (checked) state.selectedIds.add(alumniId);
        else state.selectedIds.delete(alumniId);

        updateSelectedCount();
        updateSendButton();
        updateMarkerSelection(alumniId, checked);
    };

    function updateMarkerSelection(alumniId, isSelected) {
        if (!state.alumniLayer) return;
        state.alumniLayer.eachLayer(layer => {
            if (layer.alumniId === alumniId) {
                const pin = layer.getElement()?.querySelector('.geo-alumni-pin');
                if (pin) {
                    pin.classList.toggle('selected', isSelected);
                    pin.classList.toggle('unselected', !isSelected);
                }
            }
        });
    }

    window.geoSelectAll = function (shouldSelect) {
        state.selectedIds = shouldSelect
            ? new Set(state.alumni.map(a => a.id))
            : new Set();

        // Update all checkboxes
        document.querySelectorAll('#geoAlumniList input[type="checkbox"]').forEach(cb => {
            cb.checked = shouldSelect;
        });

        // Update all markers
        state.alumniLayer.eachLayer(layer => {
            if (layer.alumniId != null) {
                const pin = layer.getElement()?.querySelector('.geo-alumni-pin');
                if (pin) {
                    pin.classList.toggle('selected', shouldSelect);
                    pin.classList.toggle('unselected', !shouldSelect);
                }
            }
        });

        updateSelectedCount();
        updateSendButton();
    };

    function updateSelectedCount() {
        const n = state.selectedIds.size;
        document.getElementById('geoStatSelected').textContent = n;
    }

    function updateSendButton() {
        const btn = document.getElementById('geoInviteSendBtn');
        const label = document.getElementById('geoInviteSendLabel');
        const hint = document.getElementById('geoFooterHint');

        const n = state.selectedIds.size;
        btn.disabled = n === 0 || state.isSending;

        if (state.isSending) {
            label.textContent = 'Sending…';
            hint.textContent = 'Please wait while invitations are being sent.';
        } else if (n === 0) {
            label.textContent = 'Send Invitations';
            hint.textContent = 'Select at least one alumni to invite.';
        } else {
            label.textContent = `Send ${n} Invitation${n > 1 ? 's' : ''}`;
            hint.textContent = `Ready to send ${n} invitation${n > 1 ? 's' : ''}.`;
        }
    }

    // =============================================================
    // SEND INVITATIONS
    // =============================================================
    window.sendGeoInvitations = async function () {
        if (state.isSending) return;
        if (state.selectedIds.size === 0) return;

        const n = state.selectedIds.size;
        const ok = confirm(
            `Send ${n} invitation${n > 1 ? 's' : ''} to nearby alumni?\n\nThis will immediately send emails via Brevo.`
        );
        if (!ok) return;

        state.isSending = true;
        updateSendButton();

        // Show a subtle progress hint
        document.getElementById('geoFooterHint').textContent =
            `Sending ${n} invitation${n > 1 ? 's' : ''}… this may take a few seconds.`;

        try {
            const res = await fetch(ROUTES.send(state.eventId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify({
                    alumni_ids:     Array.from(state.selectedIds),
                    radius_km:      state.radiusKm,
                    skip_recent:    state.skipRecent ? 1 : 0,
                    custom_message: document.getElementById('geoCustomMessage').value.trim() || null,
                }),
            });

            const data = await res.json();

            if (!res.ok || !data.success) {
                showToast('❌ ' + (data.error || data.message || 'Failed to send invitations.'), 'error');
                state.isSending = false;
                updateSendButton();
                return;
            }

            showToast(
                `✅ ${data.sent} invitation${data.sent !== 1 ? 's' : ''} sent${data.failed > 0 ? `, ${data.failed} failed` : ''}.`,
                data.failed > 0 ? 'warning' : 'success'
            );

            // Close modal after short delay
            setTimeout(() => {
                state.isSending = false;
                closeGeoInviteModal();
                // Optional: refresh page to reflect changes
                // window.location.reload();
            }, 1400);

        } catch (err) {
            console.error('[GeoInvite] Send error:', err);
            showToast('❌ Network error while sending invitations.', 'error');
            state.isSending = false;
            updateSendButton();
        }
    };

    // =============================================================
    // CUSTOM MESSAGE CHAR COUNT
    // =============================================================
    document.addEventListener('input', function (e) {
        if (e.target && e.target.id === 'geoCustomMessage') {
            document.getElementById('geoCharCount').textContent = e.target.value.length;
        }
    });

    // =============================================================
    // HELPERS
    // =============================================================
    function getInitials(fullName) {
        const parts = (fullName || '?').trim().split(/\s+/);
        if (parts.length === 1) return parts[0][0]?.toUpperCase() || '?';
        return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
    }

    function escapeHtml(str) {
        if (str == null) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function showListError(msg) {
        document.getElementById('geoAlumniList').innerHTML = `
            <div class="geo-alumni-empty geo-alumni-error">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <p>${escapeHtml(msg)}</p>
            </div>`;
    }

    function showToast(message, type = 'success') {
        const colors = {
            success: '#10b981',
            error:   '#ef4444',
            warning: '#f59e0b',
            info:    '#3b82f6',
        };

        const toast = document.createElement('div');
        toast.className = 'geo-toast';
        toast.style.background = colors[type] || colors.success;
        toast.innerHTML = `
            <span>${message}</span>
            <button type="button" aria-label="Dismiss">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;

        const closeBtn = toast.querySelector('button');
        closeBtn.onclick = () => toast.remove();

        document.body.appendChild(toast);
        setTimeout(() => { if (toast.parentElement) toast.remove(); }, 5000);
    }

    // =============================================================
    // ESC key closes modal
    // =============================================================
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('geoInviteModal');
            if (modal && modal.classList.contains('active')) {
                closeGeoInviteModal();
            }
        }
    });

})();
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

    // Venue Map Modal (global scope)
    let venueModalMap = null;
    let venueModalMarker = null;

    function openVenueModal(lat, lng, eventTitle, venueName, address, capacity, eventType, startDate, endDate) {
        const modal = document.getElementById('venueModal');
        
        document.getElementById('venueModalEventTitle').innerHTML = 
            '<i class="fa-solid fa-location-dot"></i> ' + eventTitle;
        
        let dateStr = startDate;
        if (endDate && endDate !== startDate) {
            dateStr += ' – ' + endDate;
        }
        document.getElementById('venueModalEventDate').textContent = dateStr;
        document.getElementById('venueModalVenueName').textContent = venueName;
        document.getElementById('venueModalAddress').textContent = address;
        document.getElementById('venueModalCapacity').textContent = capacity;
        document.getElementById('venueModalEventType').textContent = eventType;
        
        const directionsUrl = `https://www.google.com/maps/dir/?api=1&destination=${encodeURIComponent(lat)},${encodeURIComponent(lng)}`;
        document.getElementById('venueModalDirections').href = directionsUrl;
        
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        setTimeout(function() {
            initVenueModalMap(parseFloat(lat), parseFloat(lng), venueName);
        }, 150);
    }

    function closeVenueModal(event) {
        if (event && event.target !== document.getElementById('venueModal')) return;
        
        const modal = document.getElementById('venueModal');
        modal.style.display = 'none';
        document.body.style.overflow = '';
        
        if (venueModalMap) {
            venueModalMap.remove();
            venueModalMap = null;
            venueModalMarker = null;
        }
    }

    function initVenueModalMap(lat, lng, venueName) {
        const mapContainer = document.getElementById('venueModalMap');
        
        if (venueModalMap) {
            venueModalMap.remove();
            venueModalMap = null;
        }
        
        if (mapContainer.offsetHeight === 0) {
            mapContainer.style.height = '450px';
        }
        
        venueModalMap = L.map('venueModalMap', {
            center: [lat, lng],
            zoom: 17,
            zoomControl: true,
            scrollWheelZoom: true
        });
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(venueModalMap);
        
        venueModalMarker = L.marker([lat, lng])
            .addTo(venueModalMap)
            .bindPopup(`<strong>${venueName}</strong>`)
            .openPopup();
        
        setTimeout(function() {
            venueModalMap.invalidateSize();
        }, 100);
    }

    // ========================================
    // INITIALIZE EVERYTHING ON DOM LOAD
    // ========================================
    
    document.addEventListener('DOMContentLoaded', function() {
        // === Archive toggle button logic ===
        const archiveBtn = document.getElementById('archiveToggleBtn');
        if (archiveBtn) {
            const archivedPath = new URL(archiveBtn.href).pathname.replace(/\/$/, '');
            const currentPath = window.location.pathname.replace(/\/$/, '');

            if (currentPath === archivedPath) {
                archiveBtn.classList.add('active');
                archiveBtn.innerHTML = '<i class="fa-solid fa-list"></i> <span class="btn-text">Active Events</span>';
                archiveBtn.href = '{{ route('events.index') }}';
            }
        }

        // === Close sidebar on nav item click (mobile) ===
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 1024) {
                    toggleMobileMenu();
                }
            });
        });

        // ========================================
        // WARNING MODAL SYSTEM
        // ========================================
        
        const warningOverlay = document.getElementById('warningModal');
        const warningTitle = document.getElementById('warningModalTitle');
        const warningMessage = document.getElementById('warningModalMessage');
        const warningIcon = document.getElementById('warningModalIcon');
        const confirmBtn = document.getElementById('warningModalConfirm');
        const cancelBtn = document.getElementById('warningModalCancel');
        
        let pendingForm = null;
        
        function closeWarningModal() {
            warningOverlay.classList.remove('active');
            document.body.style.overflow = '';
            pendingForm = null;
        }
        
        cancelBtn.addEventListener('click', closeWarningModal);
        
        warningOverlay.addEventListener('click', function(e) {
            if (e.target === warningOverlay) {
                closeWarningModal();
            }
        });
        
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && warningOverlay.classList.contains('active')) {
                closeWarningModal();
            }
        });
        
        confirmBtn.addEventListener('click', function() {
            if (pendingForm) {
                pendingForm.submit();
            }
            closeWarningModal();
        });
        
        function showWarningModal(config) {
            const {
                title = 'Confirm Action',
                message = 'Are you sure?',
                iconType = 'warning',
                confirmText = 'Confirm',
                confirmClass = 'btn-danger'
            } = config;
            
            warningTitle.textContent = title;
            warningMessage.innerHTML = message;
            
            warningIcon.className = 'warning-modal-icon ' + iconType;
            const iconElement = warningIcon.querySelector('i');
            if (iconType === 'danger') {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            } else if (iconType === 'success') {
                iconElement.className = 'fa-solid fa-circle-question';
            } else {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            }
            
            confirmBtn.className = 'btn ' + confirmClass;
            confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;
            
            warningOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();
        }
        
        // Intercept all forms with data-confirm
        document.querySelectorAll('form[data-confirm]').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                
                pendingForm = form;
                const action = this.action;
                
                if (action.includes('restore')) {
                    showWarningModal({
                        title: 'Restore Event',
                        message: 'Are you sure you want to <strong>restore</strong> this event?<br><small>It will be moved back to active events and visible to alumni.</small>',
                        iconType: 'success',
                        confirmText: 'Restore',
                        confirmClass: 'btn-success'
                    });
                } else if (action.includes('permanent-delete')) {
                    showWarningModal({
                        title: 'Delete Permanently',
                        message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you absolutely sure you want to <strong>permanently delete</strong> this event?<br><small>All associated data, images, and venue information will be permanently removed.</small>',
                        iconType: 'danger',
                        confirmText: 'Delete Permanently',
                        confirmClass: 'btn-danger'
                    });
                } else {
                    showWarningModal({
                        title: 'Archive Event',
                        message: 'Are you sure you want to <strong>archive</strong> this event?<br><small>It will be moved to the archived section and hidden from alumni.</small>',
                        iconType: 'warning',
                        confirmText: 'Archive',
                        confirmClass: 'btn-warning'
                    });
                }
            });
        });

        // === Initialize Leaflet mini maps ===
        document.querySelectorAll('.venue-mini-map').forEach(function(mapDiv) {
            const lat = parseFloat(mapDiv.dataset.lat);
            const lng = parseFloat(mapDiv.dataset.lng);
            if (isNaN(lat) || isNaN(lng)) return;

            const map = L.map(mapDiv.id, {
                center: [lat, lng],
                zoom: 15,
                scrollWheelZoom: false,
                dragging: false,
                zoomControl: false
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            L.marker([lat, lng]).addTo(map);
            setTimeout(function() { map.invalidateSize(); }, 100);
        });
    });

    // === Global event listeners ===
    
    document.addEventListener('keydown', function(event) {
        if (event.key === "Escape") { 
            
            const venueModal = document.getElementById('venueModal');
            if (venueModal && venueModal.style.display === 'flex') {
                closeVenueModal();
            }
        }
    });

    // Handle window resize
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

    /* =========================================================
   EVENT MEDIA LIGHTBOX — Full Featured
   ========================================================= */

(function () {
    const lightbox   = document.getElementById('eventMediaLightbox');
    if (!lightbox) return;

    const stage      = document.getElementById('eventLightboxStage');
    const imgEl      = document.getElementById('eventLightboxImage');
    const videoEl    = document.getElementById('eventLightboxVideo');
    const counterEl  = document.getElementById('eventLightboxCounter');
    const thumbsEl   = document.getElementById('eventLightboxThumbs');
    const prevBtn    = document.getElementById('eventLightboxPrev');
    const nextBtn    = document.getElementById('eventLightboxNext');
    const downloadEl = document.getElementById('eventLightboxDownload');

    let items   = [];
    let current = 0;

    function renderItem() {
        const item = items[current];
        if (!item) return;

        imgEl.style.display   = 'none';
        videoEl.style.display = 'none';

        if (item.type === 'video') {
            videoEl.src = item.url;
            videoEl.style.display = 'block';
            imgEl.removeAttribute('src');
            videoEl.play().catch(() => {});
        } else {
            imgEl.src = item.url;
            imgEl.style.display = 'block';
            videoEl.pause();
            videoEl.removeAttribute('src');
            videoEl.load();
        }

        counterEl.textContent = `${current + 1} / ${items.length}`;

        downloadEl.href = item.url;
        downloadEl.setAttribute('download', `event-${item.id}.${item.extension || ''}`);

        prevBtn.disabled = items.length <= 1;
        nextBtn.disabled = items.length <= 1;

        thumbsEl.querySelectorAll('.lightbox-thumb').forEach((t, i) => {
            t.classList.toggle('active', i === current);
        });

        const activeThumb = thumbsEl.querySelector('.lightbox-thumb.active');
        if (activeThumb) {
            activeThumb.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
        }
    }

    function buildThumbs() {
        thumbsEl.innerHTML = '';
        if (items.length <= 1) {
            thumbsEl.style.display = 'none';
            return;
        }
        thumbsEl.style.display = 'flex';

        items.forEach((item, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'lightbox-thumb';
            btn.dataset.index = i;

            if (item.type === 'video') {
                btn.innerHTML = `
                    <video src="${item.url}#t=0.3" preload="metadata" muted playsinline></video>
                    <span class="thumb-video-icon"><i class="fa-solid fa-play"></i></span>
                `;
            } else {
                btn.innerHTML = `<img src="${item.url}" alt="Thumbnail ${i + 1}" loading="lazy">`;
            }

            btn.addEventListener('click', () => {
                current = i;
                renderItem();
            });

            thumbsEl.appendChild(btn);
        });
    }

    function openLightbox(mediaItems, startIndex = 0) {
        items   = Array.isArray(mediaItems) ? mediaItems : [];
        current = Math.max(0, Math.min(startIndex, items.length - 1));
        if (!items.length) return;

        buildThumbs();
        renderItem();

        lightbox.classList.add('active');
        lightbox.setAttribute('aria-hidden', 'false');
        document.body.classList.add('lightbox-open');
    }

    function closeLightbox() {
        lightbox.classList.remove('active');
        lightbox.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('lightbox-open');

        try {
            videoEl.pause();
            videoEl.removeAttribute('src');
            videoEl.load();
        } catch (e) {}
    }

    function go(delta) {
        if (items.length <= 1) return;
        current = (current + delta + items.length) % items.length;
        renderItem();
    }

    prevBtn.addEventListener('click', (e) => { e.stopPropagation(); go(-1); });
    nextBtn.addEventListener('click', (e) => { e.stopPropagation(); go(1); });

    lightbox.querySelectorAll('[data-event-lightbox-close]').forEach(el => {
        el.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('active')) return;
        if (e.key === 'Escape')     closeLightbox();
        if (e.key === 'ArrowLeft')  go(-1);
        if (e.key === 'ArrowRight') go(1);
    });

    let touchStartX = 0;
    stage.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    stage.addEventListener('touchend', (e) => {
        const dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 60) go(dx < 0 ? 1 : -1);
    }, { passive: true });

    // Event delegation — works for current + future tiles
    document.addEventListener('click', (e) => {
        const tile = e.target.closest('.event-media .media-tile');
        if (!tile) return;

        const wrapper = tile.closest('.event-media');
        if (!wrapper) return;

        let mediaItems = [];
        try {
            mediaItems = JSON.parse(wrapper.dataset.mediaItems || '[]');
        } catch (err) {
            mediaItems = [];
        }
        if (!mediaItems.length) return;

        const idx = parseInt(tile.dataset.mediaIndex || '0', 10);
        openLightbox(mediaItems, isNaN(idx) ? 0 : idx);
    });
})();

</script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Get user's timezone
    const userTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone;
    
    // Convert all UTC dates to local time
    document.querySelectorAll('.event-date-display').forEach(function(el) {
        const startUtc = el.dataset.startUtc;
        const endUtc = el.dataset.endUtc;
        
        if (startUtc) {
            const startDate = new Date(startUtc);
            let displayText = startDate.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                year: 'numeric'
            });
            
            if (endUtc) {
                const endDate = new Date(endUtc);
                const endFormatted = endDate.toLocaleDateString('en-US', {
                    month: 'short',
                    day: 'numeric',
                    year: 'numeric'
                });
                displayText += ' – ' + endFormatted;
            }
            
            el.textContent = displayText;
        }
    });
    
    console.log('🌍 Timezone detected:', userTimezone);
});
</script>

</body>
</html>