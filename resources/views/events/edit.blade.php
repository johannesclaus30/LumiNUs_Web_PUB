<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/events_modern.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

        <style>
        .venue-suggestions {
            position: absolute;
            z-index: 9999 !important;
            top: calc(100% + 2px);
            left: 0;
            right: 0;
            background: var(--white);
            border: 2px solid var(--nu-blue);
            border-radius: 0 0 var(--radius-lg) var(--radius-lg);
            box-shadow: var(--shadow-lg);
            max-height: 260px;
            overflow-y: auto;
            margin-top: 0;
            padding: 0;
        }

        .venue-suggestions.hidden-section {
            display: none !important;
        }

        /* Ensure the next form group doesn't overlap */
        .form-group + .form-group {
            margin-top: 0;
            position: relative;
            z-index: 1; /* Lower z-index than the suggestions container */
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
            
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="/admin/dashboard" class="nav-item">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/directory" class="nav-item">
                    <i class="fa-solid fa-users"></i>
                    <span>Alumni Directory</span>
                </a>
                <a href="{{ route('announcements.index') }}" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('events.index') }}" class="nav-item active">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="{{ route('perks.index') }}" class="nav-item">
                    <i class="fa-solid fa-gift"></i>
                    <span>Perks & Discounts</span>
                </a>
                <a href="/admin/alumni_tracer" class="nav-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Alumni Tracer</span>
                </a>
                <a href="/admin/messages" class="nav-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="nav-item">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
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
                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit Event
                        </h1>
                        <p class="page-subtitle">Update event details for NU Lipa alumni</p>
                    </div>
                    
                    <div class="header-actions">
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> 
                            <span>Back to Events</span>
                        </a>
                    </div>
                </div>
            </header>

            @if ($errors->any())
                <div class="upload-status status-error" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: var(--radius-lg); background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-top: 0.125rem; flex-shrink: 0;"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 0.375rem;">Please fix the following errors:</strong>
                        <ul style="margin:0; padding-left:1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('events.update', $event) }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="eventForm" 
                  class="form-card">
                @csrf
                @method('PUT')

                <!-- Form Guide Alert -->
                <div class="rule-alert" style="margin-bottom: 2rem;">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>Required fields:</strong> event title, event mode, event dates, max capacity, and description.
                        <br><strong>On-site / hybrid:</strong> fill in the venue address and pin it on the map.
                        <br><strong>Online / hybrid:</strong> choose the platform and add the platform URL if needed.
                    </div>
                </div>
                
                <div class="events-details-layout">
                    <!-- Left Panel - Main Form Fields -->
                    <div class="form-left-panel">
                        <!-- Event Title -->
                        <div class="form-group">
                            <label for="title" class="form-label">Event Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $event->title) }}" required>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Use the official event title shown to attendees.</small>
                        </div>

                        <!-- Event Mode + Max Capacity Row -->
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="event_type" class="form-label">Event Mode</label>
                                @php
                                    $selectedEventType = old('event_type', $event->event_type);
                                @endphp
                                <select id="event_type" name="event_type" class="form-control" required>
                                    <option value="" disabled {{ $selectedEventType ? '' : 'selected' }}>Choose event mode</option>
                                    @foreach (['In-Person' => 'On-Site / Face to Face', 'Online' => 'Online / Virtual', 'Hybrid' => 'Hybrid'] as $eventValue => $eventLabel)
                                        <option value="{{ $eventValue }}" {{ $selectedEventType === $eventValue ? 'selected' : '' }}>{{ $eventLabel }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="max_capacity" class="form-label">Max Capacity</label>
                                <input type="number" id="max_capacity" name="max_capacity" class="form-control" placeholder="e.g. 100" min="1" value="{{ old('max_capacity', $event->max_capacity) }}" required>
                            </div>
                        </div>

                        <!-- Event Dates Row -->
                        <div class="form-group">
                            <label class="form-label">Event Dates</label>
                            <div class="date-inputs-row">
                                <div class="date-input-group">
                                    <span class="date-label">From</span>
                                    <input type="date" name="start_date" class="form-control" value="{{ old('start_date', $event->start_date ? $event->start_date->format('Y-m-d') : '') }}" required>
                                </div>
                                <div class="date-input-group">
                                    <span class="date-label">To</span>
                                    <input type="date" name="end_date" class="form-control" value="{{ old('end_date', $event->end_date ? $event->end_date->format('Y-m-d') : '') }}" required>
                                </div>
                            </div>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Choose the start and end dates for the event schedule.</small>
                        </div>

                        <!-- Venue Section (On-Site / Hybrid) -->
                        <div class="section-card" id="venueSection">
                            <div class="section-card-header">
                                <div>
                                    <h3 class="section-card-title"><i class="fa-solid fa-location-dot"></i> On-Site Venue</h3>
                                    <p class="section-card-subtitle">Use this for face-to-face or hybrid events. The map and address stay synced.</p>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="venueAddress" class="form-label">Venue Address</label>
                                <input
                                    type="text"
                                    name="venue_address"
                                    id="venueAddress"
                                    class="form-control"
                                    placeholder="Start typing a location or exact address"
                                    value="{{ old('venue_address', optional($event->venue)->address) }}"
                                >
                                <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Type a place name or exact address to search nearby results.</small>
                                <div id="venueSuggestions" class="venue-suggestions hidden-section"></div>
                            </div>

                            <div class="form-group">
                                <label for="venueName" class="form-label">Venue Name</label>
                                <input
                                    type="text"
                                    name="venue_name"
                                    id="venueName"
                                    class="form-control"
                                    placeholder="Optional venue name"
                                    value="{{ old('venue_name', optional($event->venue)->name) }}"
                                >
                                <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Please set the actual name of the event venue. This will not auto-fill.</small>
                            </div>

                            <input type="hidden" name="venue_latitude" id="venueLatitude" value="{{ old('venue_latitude', optional($event->venue)->latitude) }}">
                            <input type="hidden" name="venue_longitude" id="venueLongitude" value="{{ old('venue_longitude', optional($event->venue)->longitude) }}">

                            <div id="venueMap" class="venue-map"></div>
                            <div class="venue-status" id="venueStatus">Search an address or click the map to pin the venue.</div>
                        </div>

                        <!-- Online Section (Online / Hybrid) -->
                        <div class="section-card hidden-section" id="onlineSection">
                            <div class="section-card-header">
                                <div>
                                    <h3 class="section-card-title"><i class="fa-solid fa-globe"></i> Online Details</h3>
                                    <p class="section-card-subtitle">Use this for virtual events. Hidden when the event is on-site only.</p>
                                </div>
                            </div>

                            <div class="form-row-2col">
                                <div class="form-group">
                                    <label for="platformField" class="form-label">Platform</label>
                                    <input type="text" name="platform" id="platformField" class="form-control" placeholder="Zoom, Google Meet, Facebook Live, etc." value="{{ old('platform', $event->platform) }}">
                                    <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Select the online service or channel where the event will happen.</small>
                                </div>

                                <div class="form-group">
                                    <label for="platformUrlField" class="form-label">Platform URL</label>
                                    <input type="url" name="platform_url" id="platformUrlField" class="form-control" placeholder="https://..." value="{{ old('platform_url', $event->platform_url) }}">
                                    <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Add the meeting link, livestream URL, or event page if available.</small>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="form-label">Content / Description</label>
                            <textarea id="description" name="description" class="form-control textarea-description" required>{{ old('description', $event->description) }}</textarea>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Provide the event description, agenda, or important instructions for attendees.</small>
                        </div>
                    </div>

                    <!-- Right Panel - Attachments + Actions -->
                    <div class="form-right-panel">
                        
                        @include('partials.event-upload-zone', [
                            'event'     => $event,
                            'existing'  => $event->images,
                            'maxFiles'  => 5,
                            'maxSizeMb' => 5,
                        ])

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('events.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-save"></i>
                                <span>Update Event</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    
    <script>
    // ========================================
    // Mobile Menu Toggle
    // ========================================
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

    // ========================================
    // Attachment Handling
    // ========================================

    document.addEventListener('DOMContentLoaded', function() {

        const eventTypeField = document.getElementById('event_type');
        const venueAddressField = document.getElementById('venueAddress');
        const venueNameField = document.getElementById('venueName');
        const venueLatitudeField = document.getElementById('venueLatitude');
        const venueLongitudeField = document.getElementById('venueLongitude');
        const venueSuggestions = document.getElementById('venueSuggestions');
        const venueSection = document.getElementById('venueSection');
        const onlineSection = document.getElementById('onlineSection');
        const platformField = document.getElementById('platformField');
        const platformUrlField = document.getElementById('platformUrlField');
        const venueStatus = document.getElementById('venueStatus');
        const venueMapElement = document.getElementById('venueMap');
        const defaultCenter = [14.5995, 120.9842];
        const existingLatitude = parseFloat(venueLatitudeField?.value || '');
        const existingLongitude = parseFloat(venueLongitudeField?.value || '');
        const existingAddress = (venueAddressField?.value || '').trim();
        let venueMap = null;
        let venueMarker = null;
        let addressDebounce = null;
        let suggestionDebounce = null;
        let lastSuggestionQuery = '';

        // ========================================
        // WARNING MODAL SYSTEM
        // ========================================
        const warningOverlay = document.getElementById('warningModal');
        const warningTitle = document.getElementById('warningModalTitle');
        const warningMessage = document.getElementById('warningModalMessage');
        const warningIcon = document.getElementById('warningModalIcon');
        const confirmBtn = document.getElementById('warningModalConfirm');
        const modalCancelBtn = document.getElementById('warningModalCancel');

        let pendingCallback = null;

        function closeWarningModal() {
            warningOverlay.classList.remove('active');
            document.body.style.overflow = '';
            pendingCallback = null;
        }

        modalCancelBtn.addEventListener('click', closeWarningModal);
        warningOverlay.addEventListener('click', function(e) { if (e.target === warningOverlay) closeWarningModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && warningOverlay.classList.contains('active')) closeWarningModal(); });
        confirmBtn.addEventListener('click', function() { if (pendingCallback) pendingCallback(); closeWarningModal(); });

        window.showWarningModal = function(config) {
            const {
                title = 'Confirm Action',
                message = 'Are you sure?',
                iconType = 'warning',
                confirmText = 'Confirm',
                confirmClass = 'btn-danger',
                onConfirm = null,
                hideCancel = false
            } = config;

            warningTitle.textContent = title;
            warningMessage.innerHTML = message;
            warningIcon.className = 'warning-modal-icon ' + iconType;
            const iconElement = warningIcon.querySelector('i');
            if (iconType === 'danger') iconElement.className = 'fa-solid fa-triangle-exclamation';
            else if (iconType === 'success') iconElement.className = 'fa-solid fa-circle-question';
            else iconElement.className = 'fa-solid fa-triangle-exclamation';

            confirmBtn.className = 'btn ' + confirmClass;
            confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;

            modalCancelBtn.style.display = hideCancel ? 'none' : 'inline-flex';

            pendingCallback = onConfirm;
            warningOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();
        };

        // Cancel button confirmation
        const cancelLink = document.querySelector('.form-actions .btn-secondary');
        if (cancelLink) {
            cancelLink.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                window.showWarningModal({
                    title: 'Discard Changes',
                    message: 'Are you sure you want to <strong>cancel</strong>?<br><small>All unsaved changes will be lost.</small>',
                    iconType: 'warning',
                    confirmText: 'Discard',
                    confirmClass: 'btn-warning',
                    onConfirm: function() { window.location.href = href; }
                });
            });
        }

        const setSectionVisibility = () => {
            const mode = eventTypeField?.value;
            const showVenue = ['In-Person', 'Hybrid'].includes(mode);
            const showOnline = ['Online', 'Hybrid'].includes(mode);
            venueSection?.classList.toggle('hidden-section', !showVenue);
            onlineSection?.classList.toggle('hidden-section', !showOnline);
            if (venueAddressField) { venueAddressField.required = showVenue; venueAddressField.disabled = !showVenue; }
            if (venueNameField) venueNameField.disabled = !showVenue;
            if (platformField) { platformField.required = showOnline; platformField.disabled = !showOnline; }
            if (platformUrlField) platformUrlField.disabled = !showOnline;
            if (venueMapElement) venueMapElement.style.display = showVenue ? 'block' : 'none';
            if (!showVenue) { venueSuggestions?.classList.add('hidden-section'); venueSuggestions.innerHTML = ''; }
            if (venueStatus) {
                venueStatus.textContent = showVenue ? 'Search an address or click the map to pin the venue.' : 'Venue details are hidden for this event mode.';
            }
        };

        const renderVenueSuggestions = (items) => {
            if (!venueSuggestions) return;
            venueSuggestions.innerHTML = '';
            if (!items.length) { venueSuggestions.classList.add('hidden-section'); return; }
            items.forEach((item) => {
                const row = document.createElement('div');
                row.className = 'venue-suggestion-item';
                row.textContent = item.display_name;
                row.dataset.lat = item.lat; 
                row.dataset.lon = item.lon; 
                row.dataset.name = item.display_name;
                row.addEventListener('mousedown', (event) => {
                    event.preventDefault();
                    venueAddressField.value = item.display_name;
                    // REMOVED: auto-fill for venue name - admin types it manually
                    // venueNameField.value = venueNameField.value.trim() || item.display_name;
                    venueSuggestions.classList.add('hidden-section'); 
                    venueSuggestions.innerHTML = '';
                    setVenuePoint(parseFloat(item.lat), parseFloat(item.lon), false);
                    venueStatus.textContent = 'Venue selected from suggestions.';
                });
                venueSuggestions.appendChild(row);
            });
            venueSuggestions.classList.remove('hidden-section');
        };

        const searchVenueSuggestions = (query) => {
            const trimmedQuery = query.trim();
            if (!trimmedQuery || trimmedQuery.length < 2 || trimmedQuery === lastSuggestionQuery) {
                if (!trimmedQuery) { venueSuggestions?.classList.add('hidden-section'); venueSuggestions.innerHTML = ''; }
                return;
            }
            lastSuggestionQuery = trimmedQuery;
            fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=6&q=${encodeURIComponent(trimmedQuery)}`)
                .then(r => r.json()).then(results => renderVenueSuggestions(results))
                .catch(() => { venueSuggestions?.classList.add('hidden-section'); });
        };

        if (venueMapElement && window.L) {
            const mapCenter = Number.isFinite(existingLatitude) && Number.isFinite(existingLongitude) ? [existingLatitude, existingLongitude] : defaultCenter;
            venueMap = L.map('venueMap').setView(mapCenter, Number.isFinite(existingLatitude) ? 16 : 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 19, attribution: '&copy; OpenStreetMap contributors' }).addTo(venueMap);
            venueMarker = L.marker(mapCenter, { draggable: true }).addTo(venueMap);

            const setVenuePoint = (lat, lng, updateAddress = true) => {
                venueLatitudeField.value = lat.toFixed(6);
                venueLongitudeField.value = lng.toFixed(6);
                venueMarker.setLatLng([lat, lng]);
                venueMap.setView([lat, lng], 16);
                if (updateAddress) {
                    venueStatus.textContent = 'Resolving address from map pin...';
                    fetch(`https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat=${lat}&lon=${lng}`)
                        .then(r => r.json()).then(data => {
                            const address = data.display_name || '';
                            if (address) { venueAddressField.value = address; venueStatus.textContent = 'Address updated from map pin.'; }
                            else venueStatus.textContent = 'Unable to resolve an address for that pin.';
                        }).catch(() => { venueStatus.textContent = 'Address lookup failed.'; });
                }
            };

            const geocodeAddress = (address) => {
                const query = address.trim();
                if (!query) return;
                venueStatus.textContent = 'Looking up the typed address...';
                fetch(`https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=${encodeURIComponent(query)}`)
                    .then(r => r.json()).then(results => {
                        if (!results.length) { venueStatus.textContent = 'No map result found.'; return; }
                        const place = results[0];
                        venueAddressField.value = place.display_name;
                        // REMOVE any venue name auto-fill here too
                        venueStatus.textContent = 'Map updated from typed address.';
                        setVenuePoint(parseFloat(place.lat), parseFloat(place.lon), false);
                    }).catch(() => { venueStatus.textContent = 'Address lookup failed.'; });
            };

            venueMarker.on('dragend', () => { const { lat, lng } = venueMarker.getLatLng(); setVenuePoint(lat, lng, true); });
            venueMap.on('click', (event) => { setVenuePoint(event.latlng.lat, event.latlng.lng, true); });
            venueAddressField?.addEventListener('blur', () => {
                if (['In-Person', 'Hybrid'].includes(eventTypeField?.value)) {
                    clearTimeout(addressDebounce);
                    addressDebounce = setTimeout(() => geocodeAddress(venueAddressField.value), 300);
                }
            });
            venueAddressField?.addEventListener('input', () => {
                clearTimeout(suggestionDebounce);
                if (!['In-Person', 'Hybrid'].includes(eventTypeField?.value)) { venueSuggestions?.classList.add('hidden-section'); venueSuggestions.innerHTML = ''; return; }
                suggestionDebounce = setTimeout(() => searchVenueSuggestions(venueAddressField.value), 300);
            });
            venueAddressField?.addEventListener('keydown', (event) => { if (event.key === 'Enter') { event.preventDefault(); geocodeAddress(venueAddressField.value); } });
            if (Number.isFinite(existingLatitude) && Number.isFinite(existingLongitude)) {
                venueMarker.setLatLng([existingLatitude, existingLongitude]);
                venueMap.setView([existingLatitude, existingLongitude], 16);
            } else if (existingAddress) { geocodeAddress(existingAddress); }
        }

        eventTypeField?.addEventListener('change', setSectionVisibility);
        setSectionVisibility();
    });

    /* =========================================================
   EVENT UPLOAD ZONE — Drag, Drop, Paste, Reorder
   ========================================================= */

(function () {
    const zone = document.getElementById('eventUploadZone');
    if (!zone) return;

    const dropzone     = document.getElementById('uploadDropzone');
    const fileInput    = document.getElementById('attachmentInput');
    const previewGrid  = document.getElementById('attachment-preview-container');
    const emptyHint    = document.getElementById('uploadEmptyHint');
    const counterEl    = document.getElementById('uploadCounter');
    const counterCur   = document.getElementById('uploadCounterCurrent');
    const removedBox   = document.getElementById('uploadRemovedIds');

    const MAX_FILES    = parseInt(zone.dataset.maxFiles || '5', 10);
    const MAX_SIZE     = parseInt(zone.dataset.maxSizeMb || '5', 10) * 1024 * 1024;
    const ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp'];

    const dt = new DataTransfer();

    function currentTileCount() {
        return previewGrid.querySelectorAll('.upload-tile:not(.marked-for-removal)').length;
    }

    function updateCounter() {
        const n = currentTileCount();
        counterCur.textContent = n;
        counterEl.classList.toggle('is-full', n >= MAX_FILES);
        emptyHint.style.display = n === 0 ? 'flex' : 'none';
        refreshCoverBadge();
        refreshIndexBadges();
    }

    function refreshCoverBadge() {
        previewGrid.querySelectorAll('.upload-tile-cover-badge').forEach(b => b.remove());
        const first = previewGrid.querySelector('.upload-tile:not(.marked-for-removal)');
        if (first) {
            const badge = document.createElement('span');
            badge.className = 'upload-tile-cover-badge';
            badge.innerHTML = '<i class="fa-solid fa-star"></i> Cover';
            first.appendChild(badge);
        }
    }

    function refreshIndexBadges() {
        const tiles = previewGrid.querySelectorAll('.upload-tile');
        let visibleIndex = 1;
        tiles.forEach(tile => {
            let idx = tile.querySelector('.upload-tile-index');
            if (!idx) {
                idx = document.createElement('span');
                idx.className = 'upload-tile-index';
                tile.appendChild(idx);
            }
            if (tile.classList.contains('marked-for-removal')) {
                idx.textContent = '—';
            } else {
                idx.textContent = visibleIndex++;
            }
        });
    }

    function warn(title, message) {
        if (typeof window.showWarningModal === 'function') {
            window.showWarningModal({
                title: title,
                message: message,
                iconType: 'warning',
                confirmText: 'OK',
                confirmClass: 'btn-warning',
                hideCancel: true,
            });
        } else {
            alert(message.replace(/<[^>]+>/g, ''));
        }
    }

    function addFiles(fileList) {
        const files = Array.from(fileList);
        const existingCount = currentTileCount();

        const valid = [];
        for (const file of files) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXT.includes(ext)) {
                warn('Invalid File Type', `<strong>${file.name}</strong> is not supported.<br><small>Only JPG, PNG, WEBP allowed.</small>`);
                continue;
            }
            if (file.size > MAX_SIZE) {
                warn('File Too Large', `<strong>${file.name}</strong> exceeds ${MAX_SIZE / 1024 / 1024}MB.`);
                continue;
            }
            valid.push(file);
        }

        if (existingCount + valid.length > MAX_FILES) {
            warn('Too Many Files', `You can only have up to <strong>${MAX_FILES}</strong> images.<br><small>Currently: ${existingCount}. Tried to add: ${valid.length}.</small>`);
            return;
        }

        valid.forEach(file => {
            dt.items.add(file);

            const reader = new FileReader();
            reader.onload = function (e) {
                const tile = document.createElement('div');
                tile.className = 'upload-tile new-image';
                tile.draggable = true;
                tile.dataset.type = 'new';
                tile.dataset.fileName = file.name;
                tile.innerHTML = `
                    <img src="${e.target.result}" class="upload-tile-img" alt="${file.name}">
                    <button type="button" class="upload-tile-remove" data-action="remove-new" title="Remove">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <span class="upload-tile-drag-handle" title="Drag to reorder">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                `;
                previewGrid.appendChild(tile);
                updateCounter();
            };
            reader.readAsDataURL(file);
        });

        fileInput.files = dt.files;
    }

    function removeTile(tile) {
        if (tile.dataset.type === 'new') {
            const fileName = tile.dataset.fileName;
            const newDt = new DataTransfer();
            Array.from(dt.files).forEach(f => {
                if (f.name !== fileName) newDt.items.add(f);
            });
            dt.items.clear();
            Array.from(newDt.files).forEach(f => dt.items.add(f));
            fileInput.files = dt.files;
            tile.remove();
        } else if (tile.dataset.type === 'existing') {
            const id = tile.dataset.imageId;
            const isMarked = tile.classList.contains('marked-for-removal');
            if (isMarked) {
                tile.classList.remove('marked-for-removal');
                const hidden = removedBox.querySelector(`input[value="${id}"]`);
                if (hidden) hidden.remove();
            } else {
                tile.classList.add('marked-for-removal');
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'remove_existing[]';
                hidden.value = id;
                removedBox.appendChild(hidden);
            }
        }
        updateCounter();
    }

    // ---- Dropzone ----
    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', (event) => {
        addFiles(event.target.files);
    });

    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        if (e.dataTransfer && e.dataTransfer.files.length) {
            addFiles(e.dataTransfer.files);
        }
    });

    // ---- Global drag overlay ----
    let dragDepth = 0;
    window.addEventListener('dragenter', (e) => {
        if (e.dataTransfer && Array.from(e.dataTransfer.types).includes('Files')) {
            dragDepth++;
            document.body.classList.add('is-dragging-files');
        }
    });
    window.addEventListener('dragleave', () => {
        dragDepth = Math.max(0, dragDepth - 1);
        if (dragDepth === 0) document.body.classList.remove('is-dragging-files');
    });
    window.addEventListener('drop', () => {
        dragDepth = 0;
        document.body.classList.remove('is-dragging-files');
    });
    window.addEventListener('dragover', (e) => {
        if (e.dataTransfer && Array.from(e.dataTransfer.types).includes('Files')) {
            e.preventDefault();
        }
    });

    // ---- Paste ----
    document.addEventListener('paste', (e) => {
        const active = document.activeElement;
        if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA') && active !== dropzone) {
            return;
        }
        const items = (e.clipboardData || window.clipboardData)?.items || [];
        const files = [];
        for (const item of items) {
            if (item.kind === 'file' && item.type.startsWith('image/')) {
                const file = item.getAsFile();
                if (file) files.push(file);
            }
        }
        if (files.length) {
            e.preventDefault();
            addFiles(files);
        }
    });

    // ---- Remove button delegation ----
    previewGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="remove-new"], [data-action="mark-remove"]');
        if (!btn) return;
        const tile = btn.closest('.upload-tile');
        if (tile) removeTile(tile);
    });

    // ---- Drag to reorder ----
    let draggedTile = null;

    previewGrid.addEventListener('dragstart', (e) => {
        const tile = e.target.closest('.upload-tile');
        if (!tile) return;
        draggedTile = tile;
        tile.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        try { e.dataTransfer.setData('text/plain', ''); } catch (err) {}
    });

    previewGrid.addEventListener('dragend', () => {
        if (draggedTile) draggedTile.classList.remove('is-dragging');
        previewGrid.querySelectorAll('.upload-tile').forEach(t => t.classList.remove('is-drag-over'));
        draggedTile = null;
    });

    previewGrid.addEventListener('dragover', (e) => {
        e.preventDefault();
        const target = e.target.closest('.upload-tile');
        if (!target || target === draggedTile) return;
        target.classList.add('is-drag-over');
    });

    previewGrid.addEventListener('dragleave', (e) => {
        const target = e.target.closest('.upload-tile');
        if (target) target.classList.remove('is-drag-over');
    });

    previewGrid.addEventListener('drop', (e) => {
        e.preventDefault();
        const target = e.target.closest('.upload-tile');
        if (!target || !draggedTile || target === draggedTile) return;
        target.classList.remove('is-drag-over');

        const allTiles = Array.from(previewGrid.querySelectorAll('.upload-tile'));
        const fromIndex = allTiles.indexOf(draggedTile);
        const toIndex = allTiles.indexOf(target);

        if (fromIndex < toIndex) target.after(draggedTile);
        else target.before(draggedTile);

        const newDt = new DataTransfer();
        previewGrid.querySelectorAll('.upload-tile[data-type="new"]').forEach(t => {
            const fname = t.dataset.fileName;
            const file = Array.from(dt.files).find(f => f.name === fname);
            if (file) newDt.items.add(file);
        });
        dt.items.clear();
        Array.from(newDt.files).forEach(f => dt.items.add(f));
        fileInput.files = dt.files;

        refreshCoverBadge();
        refreshIndexBadges();
    });

    // ---- Dirty guard ----
    let isDirty = false;
    zone.addEventListener('change', () => { isDirty = true; });
    previewGrid.addEventListener('click', () => { isDirty = true; });

    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    const form = document.getElementById('eventForm');
    if (form) {
        form.addEventListener('submit', function () {
            isDirty = false;
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('is-submitting');
                submitBtn.disabled = true;
            }
        });
    }

    updateCounter();
})();

</script>

</body>
</html>

{{-- This is Event's edit.blade.php --}}