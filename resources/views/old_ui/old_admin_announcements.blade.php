<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/announcements.css">
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
                <a href="{{ route('announcements.index') }}" class="admin-menu-current">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="announcements-panel admin-scrollable">
            <div class="announcements-panel-header">
                <div class="add-announcements-container">
                    @if (!request()->routeIs('announcements.archived'))
                        <a href="{{ route('announcements.create') }}" class="add-announcements-button">Add New Announcement</a>
                    @endif
                    <a id="archiveToggleBtn"
                    href="{{ route('announcements.archived') }}"
                    class="add-announcements-button archived-toggle">
                        Archived Announcements
                    </a>
                </div>
                <div class="pagination-container">
                    {{ $announcements->links() }}
                </div>
            </div>
            
            <div>
                @forelse ($announcements as $announcement)
                    <div class="announcements-container">
                        <div class="announcements-title-description">
                            <p class="announcements-title-text">
                                {{ $announcement->title }}
                            </p>

                            <p class="announcements-description-text">
                                {{ $announcement->announcement_description }}
                            </p>

                            <p class="announcements-description-text">
                                {{ optional($announcement->date_posted)->format('F d, Y') }}
                            </p>

                            @if ($announcement->scheduled_post_at)
                                <p class="announcements-description-text">
                                    Scheduled for: {{ $announcement->scheduled_post_at->format('F d, Y h:i A') }}
                                </p>
                            @endif
                        </div>
                        
                        <div class="announcements-image-container">
                            <p class="announcements-image-text">Attachments:</p>

                            {{-- A simplified grid that contains both images and videos --}}
                            <div class="attachment-preview-wrapper" style="display: flex; flex-wrap: wrap; gap: 10px; align-items: flex-start;">
                                
                                {{-- Iterate through all attachments from the hasMany relation --}}
                                @forelse ($announcement->images as $attachment)
                                    @php
                                        $path = $attachment->image_path;
                                        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
                                        
                                        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp'];
                                    @endphp

                                    @if (in_array($extension, $imageExtensions))
                                        <img
                                            src="{{ $attachment->image_url }}"
                                            alt="Announcement"
                                            class="perk-image" {{-- The JS looks for this class --}}
                                            style="width: 80px; height: 80px; object-fit: cover; border-radius: 5px; border: 1px solid #eee;"
                                        >
                                    @endif

                                @empty
                                    {{-- Default fallback if NO attachments exist in the list --}}
                                    {{-- <img
                                        src="{{ asset('assets/FINAL-NULIPA.jpg') }}"
                                        alt="No attachment available"
                                        class="perk-image"
                                        style="width: 80px; height: 80px; object-fit: cover; opacity: 0.5;"
                                    > --}}
                                @endforelse
                            </div>
                        </div>

                        <!-- RIGHT COLUMN -->
                        <div class="announcements-tools">
                            <div class="announcements-tools-analytics">
                                <span>Analytics</span>
                                <p>👁 No Data Yet</p>
                            </div>

                            @if ((int) $announcement->status === 0)
                                <form action="{{ route('announcements.restore', $announcement->id) }}" method="POST" onsubmit="return confirm('Restore this announcement?');">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="perk-edit-archive-btn edit-btn">Restore</button>
                                </form>
                            @else
                                <a href="{{ route('announcements.edit', $announcement->id) }}" class="perk-edit-archive-btn edit-btn">Edit</a>
                                <form action="{{ route('announcements.destroy', $announcement->id) }}" method="POST" onsubmit="return confirm('Archive this announcement?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="perk-edit-archive-btn archive-btn">Archive</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="announcements-container" style="justify-content: center;">
                        <p class="announcements-description-text">No announcements yet!</p>
                    </div>
                @endforelse

                <div class="pagination-container bottom-pagination">
                    {{ $announcements->links() }}
                </div>
            </div>
        </div>
    </div>

<div id="imageModal" class="custom-modal" onclick="closeModal()">
    <span class="close-modal">&times;</span>
    <img class="modal-content" id="enlargedImage">
</div>

<script>
        function openModal(src) {
        const modal = document.getElementById("imageModal");
        const modalImg = document.getElementById("enlargedImage");
        modal.style.display = "flex";
        modalImg.src = src;
    }

    function closeModal() {
        document.getElementById("imageModal").style.display = "none";
    }

    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('archiveToggleBtn');
        if (!btn) return;

        const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
        const currentPath = window.location.pathname.replace(/\/$/, '');

        if (currentPath === archivedPath) {
            // We're on the archived page
            btn.textContent = 'View Active Announcements';
            btn.href = '{{ route('announcements.index') }}';
            btn.classList.add('active');
        } else {
            // On the active page – ensure class is removed just in case
            btn.classList.remove('active');
        }
    });

    // Attach click event to all perk images
    document.addEventListener("DOMContentLoaded", function() {
        const images = document.querySelectorAll(".perk-image");
        images.forEach(img => {
            img.style.cursor = "zoom-in"; // Change cursor to show it's clickable
            img.onclick = function() {
                openModal(this.src);
            };
        });
    });
</script>

</body>
</html>