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
                    <a href="{{ route('events.create') }}" class="add-events-button">Add New Event</a>
                    <a id="archiveToggleBtn" href="{{ route('events.archived') }}" class="add-events-button" style="margin-left:8px; background-color:#818181; color:#ffffff; width:auto;">Archived Events</a>
                </div>
                <div class="pagination-container">
                    {{ $events->links() }}
                </div>
            </div>
            
            <div class="events-list-wrapper">
                @forelse ($events as $event)
                    <div class="events-container">
                        <div class="events-title-description">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <p class="events-title-text" style="margin: 0;">{{ $event->title }}</p>
                                <i>
                                    <span class="status-badge {{ ((int) $event->status === 1 || is_null($event->status)) ? 'badge-active' : 'badge-archived' }}">
                                        {{ (int) $event->status === 0 ? 'Archived' : 'Active' }}
                                    </span>
                                </i>
                                <span class="event-type-badge badge-{{ strtolower($event->event_type) }}">
                                    {{ $event->event_type }}
                                </span>
                            </div>

                            <p class="events-description-text">
                                <strong>Date:</strong>
                                @if($event->start_date)
                                    {{ $event->start_date->format('F d, Y') }}
                                    @if($event->end_date) - {{ $event->end_date->format('F d, Y') }}@endif
                                @else
                                    N/A
                                @endif
                                <br>

                                {{-- Hide platform fields when the event is In-Person only --}}
                                @if(in_array($event->event_type, ['Online','Hybrid']))
                                    <strong>Platform:</strong> {{ $event->platform ?: 'N/A' }}<br>
                                    @if($event->platform_url)
                                        <strong>Platform URL:</strong>
                                        <a href="{{ $event->platform_url }}" target="_blank" rel="noopener">{{ $event->platform_url }}</a><br>
                                    @endif
                                @endif

                                {{-- Hide venue field when the event is Online only --}}
                                @if(in_array($event->event_type, ['In-Person','Hybrid']))
                                    <strong>Venue:</strong>
                                    @if($event->venue)
                                        {{ $event->venue->name }}<br>
                                        {{ $event->venue->address }}<br>
                                        <small>{{ $event->venue->latitude }}, {{ $event->venue->longitude }}</small>
                                    @else
                                        N/A
                                    @endif
                                    <br>
                                @endif

                                <strong>Uploaded by:</strong>
                                @if($event->admin)
                                    {{ $event->admin->AdminFirstName }} {{ $event->admin->AdminLastName }}
                                @endif
                                (Admin ID: {{ $event->admin_id }})
                            </p>

                            {{-- Mini map for In-Person / Hybrid events --}}
                            @if(in_array($event->event_type, ['In-Person','Hybrid']) && $event->venue && $event->venue->latitude && $event->venue->longitude)
                                <div id="venue-map-{{ $event->id }}" 
                                     class="venue-mini-map" 
                                     data-lat="{{ $event->venue->latitude }}" 
                                     data-lng="{{ $event->venue->longitude }}">
                                </div>
                            @endif
                        </div>
                        
                        <div class="events-image-container">
                            <p class="events-image-text">Attachments:</p>
                            <div class="attachment-preview-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
                                @foreach($event->images as $image)
                                    <img src="{{ $image->image_url }}" 
                                         alt="Event Image" 
                                         style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; cursor: pointer;"
                                         onclick="openModal(this.src)">
                                @endforeach
                            </div>
                        </div>

                        <div class="events-tools">
                            <div class="events-tools-analytics">
                                <span>Capacity</span>
                                <p>👥 {{ $event->max_capacity }} Max</p>
                            </div>

                            @if ((int) $event->status === 0)
                                <form action="{{ route('events.restore', $event) }}" method="POST" onsubmit="return confirm('Restore this event?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="events-edit-archive-btn edit-btn">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('events.edit', $event) }}" class="events-edit-archive-btn edit-btn">Edit</a>
                                <a href="#" class="events-edit-archive-btn manage-btn">Attendees</a>
                                
                                <form action="{{ route('events.destroy', $event) }}" method="POST" onsubmit="return confirm('Archive this event?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="events-edit-archive-btn archive-btn" style="width: 100%; border: none; cursor: pointer;">Archive</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="events-container" style="justify-content: center;">
                        <p class="events-description-text">No events have been scheduled yet. Time to plan something big!</p>
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

    <script>
        function openModal(src) {
            document.getElementById('imageModal').style.display = "block";
            document.getElementById('enlargedImage').src = src;
        }
        function closeModal() {
            document.getElementById('imageModal').style.display = "none";
        }

        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('archiveToggleBtn');
            if (!btn) return;

            const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
            const currentPath = window.location.pathname.replace(/\/$/, '');

            if (currentPath === archivedPath) {
                btn.textContent = 'View Active Events';
                btn.href = '{{ route('events.index') }}';
            }

            // Initialize all mini maps
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

                // Fix map sizing in flex/grid containers
                setTimeout(() => map.invalidateSize(), 100);
            });
        });
    </script>
</body>
</html>