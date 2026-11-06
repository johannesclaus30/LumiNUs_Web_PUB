<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/events.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>
                <a href="{{ url('admin/dashboard') }}" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="{{ url('admin/directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-current">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ url('admin/settings') }}" class="admin-menu-buttons">Settings</a>
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>

        <div class="events-panel admin-scrollable">
            <div class="events-panel-header">
                <div class="add-events-container">
                    @if(request()->route()->getName() !== 'events.archived')
                        <a href="{{ route('events.create') }}" class="add-events-button">➕ Add New Event</a>
                    @endif
                    <a id="archiveToggleBtn" href="{{ route('events.archived') }}" class="add-events-button" style="margin-left:8px; background-color:#818181; color:#ffffff; width:auto;">📂 Archived Events</a>
                </div>
                <div class="pagination-container">
                    {{ $events->links() }}
                </div>
            </div>
            
            <div class="events-list-wrapper">
                @forelse ($events as $event)
                    <div class="events-container event-card event-card-{{ strtolower($event->event_type) }}">
                        {{-- Left colour accent handled by CSS class --}}
                        <div class="events-title-description">
                            <div class="event-header-row">
                                <p class="events-title-text">{{ $event->title }}</p>
                                <div class="event-badges">
                                    <span class="status-badge {{ ((int) $event->status === 1 || is_null($event->status)) ? 'badge-active' : 'badge-archived' }}">
                                        {{ (int) $event->status === 0 ? 'Archived' : 'Active' }}
                                    </span>
                                    <span class="event-type-badge badge-{{ strtolower($event->event_type) }}">
                                        {{ $event->event_type }}
                                    </span>
                                </div>
                            </div>

                            <div class="events-details-list">
                                <div class="detail-item">
                                    <span class="detail-icon">📅</span>
                                    <span>
                                        @if($event->start_date)
                                            {{ $event->start_date->format('F d, Y') }}
                                            @if($event->end_date) – {{ $event->end_date->format('F d, Y') }}@endif
                                        @else
                                            N/A
                                        @endif
                                    </span>
                                </div>

                                @if(in_array($event->event_type, ['Online','Hybrid']))
                                    <div class="detail-item">
                                        <span class="detail-icon">💻</span>
                                        <span><strong>Platform:</strong> {{ $event->platform ?: 'N/A' }}</span>
                                    </div>
                                    @if($event->platform_url)
                                        <div class="detail-item">
                                            <span class="detail-icon">🔗</span>
                                            <span>
                                                <a href="{{ $event->platform_url }}" target="_blank" rel="noopener" class="platform-link">
                                                    {{ $event->platform_url }}
                                                </a>
                                            </span>
                                        </div>
                                    @endif
                                @endif

                                @if(in_array($event->event_type, ['In-Person','Hybrid']))
                                    <div class="detail-item">
                                        <span class="detail-icon">🏢</span>
                                        <span>
                                            @if($event->venue)
                                                <strong>{{ $event->venue->name }}</strong><br>
                                                {{ $event->venue->address }}<br>
                                                <small class="coords">{{ $event->venue->latitude }}, {{ $event->venue->longitude }}</small>
                                            @else
                                                N/A
                                            @endif
                                        </span>
                                    </div>
                                @endif

                                <div class="detail-item">
                                    <span class="detail-icon">👤</span>
                                    <span>
                                        Uploaded by
                                        @if($event->admin)
                                            <strong>{{ $event->admin->AdminFirstName }} {{ $event->admin->AdminLastName }}</strong>
                                        @endif
                                        (ID: {{ $event->admin_id }})
                                    </span>
                                </div>
                            </div>

                            {{-- Mini map for In-Person / Hybrid --}}
                            @if(in_array($event->event_type, ['In-Person','Hybrid']) && $event->venue && $event->venue->latitude && $event->venue->longitude)
                                <div id="venue-map-{{ $event->id }}" 
                                     class="venue-mini-map" 
                                     data-lat="{{ $event->venue->latitude }}" 
                                     data-lng="{{ $event->venue->longitude }}"
                                     title="Click to expand">
                                </div>
                            @endif
                        </div>
                        
                        <div class="events-image-container">
                            <p class="events-image-text">📸 Attachments</p>
                            <div class="attachment-grid">
                                @forelse($event->images as $image)
                                    <img src="{{ $image->image_url }}" 
                                         alt="Event Image" 
                                         class="attachment-thumb"
                                         onclick="openModal(this.src)"
                                         loading="lazy">
                                @empty
                                    <p class="no-attachments">No images uploaded</p>
                                @endforelse
                            </div>
                        </div>

                        <div class="events-tools">
                            <div class="events-tools-analytics">
                                <div class="capacity-stat">
                                    <span class="capacity-icon">👥</span>
                                    <div>
                                        <span class="capacity-label">Max Capacity</span>
                                        <strong class="capacity-number">{{ $event->max_capacity }}</strong>
                                    </div>
                                </div>
                            </div>

                            @if ((int) $event->status === 0)
                                <form action="{{ route('events.restore', $event) }}" method="POST" data-confirm="Restore this event?">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="events-edit-archive-btn edit-btn">↩️ Restore</button>
                                </form>
                            @else
                                <a href="{{ route('events.edit', $event) }}" class="events-edit-archive-btn edit-btn">✏️ Edit</a>
                                <a href="#" class="events-edit-archive-btn manage-btn">📋 Attendees</a>
                                <form action="{{ route('events.destroy', $event) }}" method="POST" data-confirm="Archive this event?">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="events-edit-archive-btn archive-btn">🗄️ Archive</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="events-container empty-state">
                        <div class="empty-icon">📭</div>
                        <p class="empty-text">No events have been scheduled yet.</p>
                        <p class="empty-sub">Time to plan something big!</p>
                    </div>
                @endforelse

                <div class="pagination-container bottom-pagination">
                    {{ $events->links() }}
                </div>
            </div>
        </div>
    </div>

    <div id="imageModal" class="custom-modal" style="display:none;" onclick="closeModal()">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="enlargedImage">
    </div>

    {{-- Custom Confirmation Modal --}}
    <div id="confirmModal" class="confirm-modal-overlay" style="display:none;">
        <div class="confirm-modal-box">
            <div class="confirm-modal-icon">⚠️</div>
            <h3 id="confirmTitle" class="confirm-modal-title">Confirm Action</h3>
            <p id="confirmMessage" class="confirm-modal-message">Are you sure you want to proceed?</p>
            <div class="confirm-modal-actions">
                <button id="confirmCancel" class="confirm-btn confirm-btn-cancel">Cancel</button>
                <button id="confirmOk" class="confirm-btn confirm-btn-ok">Confirm</button>
            </div>
        </div>
    </div>

    <script>
    // ---------- Image Modal (global functions) ----------
    function openModal(src) {
        document.getElementById('imageModal').style.display = "block";
        document.getElementById('enlargedImage').src = src;
    }
    function closeModal() {
        document.getElementById('imageModal').style.display = "none";
    }

    // ---------- All DOM‑dependent logic ----------
    document.addEventListener('DOMContentLoaded', function () {

        // 1. Archive / Active toggle button
        const btn = document.getElementById('archiveToggleBtn');
        if (btn) {
            const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
            const currentPath = window.location.pathname.replace(/\/$/, '');

            if (currentPath === archivedPath) {
                btn.textContent = '📋 View Active Events';
                btn.href = '{{ route('events.index') }}';
            }
        }

        // 2. Leaflet mini maps
        document.querySelectorAll('.venue-mini-map').forEach(function (mapDiv) {
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
            setTimeout(function () { map.invalidateSize(); }, 100);
        });

        // 3. Custom Confirmation Modal
        const confirmModal   = document.getElementById('confirmModal');
        const confirmTitle   = document.getElementById('confirmTitle');
        const confirmMessage = document.getElementById('confirmMessage');
        const confirmOk      = document.getElementById('confirmOk');
        const confirmCancel  = document.getElementById('confirmCancel');

        let pendingForm = null;

        function openConfirmModal(form, message) {
            pendingForm = form;
            confirmMessage.textContent = message || 'Are you sure?';
            confirmTitle.textContent = form.action.includes('restore') ? 'Restore Event' : 'Archive Event';
            confirmModal.style.display = 'flex';
        }

        function closeConfirmModal() {
            confirmModal.style.display = 'none';
            pendingForm = null;
        }

        // Intercept all forms with data-confirm
        document.querySelectorAll('form[data-confirm]').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                openConfirmModal(this, this.dataset.confirm);
            });
        });

        confirmOk.addEventListener('click', function () {
            if (pendingForm) {
                pendingForm.submit();
            }
            closeConfirmModal();
        });

        confirmCancel.addEventListener('click', closeConfirmModal);

        // Close modal when clicking the dark overlay
        confirmModal.addEventListener('click', function (e) {
            if (e.target === confirmModal) {
                closeConfirmModal();
            }
        });

    });
</script>
</body>
</html>