<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perks and Discounts | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/perks.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        <div class="admin-menu">
                <div>
                <p class="text-titles">Admin Menu</p>
                <a href="/admin/dashboard" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="/admin/directory" class="admin-menu-buttons">Alumni Directory</a>
                <a href="/admin/announcements" class="admin-menu-buttons">Announcement Editor</a>
                <a href="/admin/events" class="admin-menu-buttons">Event Organizer</a>
                <a href="/admin/perks" class="admin-menu-current">Perks and Discounts</a>
                <a href="/admin/alumni_tracer" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="/admin/messages" class="admin-menu-buttons">Messages</a>
                <a href="/admin/settings" class="admin-menu-buttons">Settings</a>
            </div>
            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>

        <div class="perks-panel admin-scrollable">
            <div class="perks-panel-header">
                <div class="add-perks-container">
                    @if (!request()->routeIs('perks.archived'))
                        <a href="{{ route('perks.create') }}" class="add-perks-button">Add New Perks</a>
                    @endif
                    <a id="archiveToggleBtn"
                    href="{{ route('perks.archived') }}"
                    class="add-perks-button archived-toggle"
                    style="@if (!request()->routeIs('perks.archived')) margin-left:8px; @endif">
                        Archived Perks
                    </a>
                </div>
                <div class="pagination-container">
                    {{ $perks->links() }}
                </div>
            </div>
            
            <div>
                @forelse ($perks as $perk)
                    <div class="perks-container">
                        <div class="perks-title-description">
                            <p class="perks-title-text">{{ $perk->title }}</p>
                            <p class="perks-description-text">{{ $perk->description }}</p>
                            
                            <p class="perks-description-text">
                                <i class="fa-solid fa-calendar-day"></i> 
                                Valid until {{ $perk->valid_until->format('F d, Y') }}
                            </p>
                        </div>

                        <div class="perks-image-container">
                            <p class="perks-image-text">Attachments (Gallery):</p>
                            <div class="perks-gallery" style="display: flex; gap: 5px; flex-wrap: wrap;">
                                @if ($perk->images->isNotEmpty())
                                    @foreach ($perk->images as $image)
                                        <img
                                            src="{{ $image->image_url }}"
                                            alt="Perk Image"
                                            class="perk-image"
                                            style="width: 60px; height: 60px; object-fit: cover; cursor: pointer;"
                                            onclick="openModal(this.src)"
                                        >
                                    @endforeach
                                @else
                                    <img
                                        src="{{ asset('assets/FINAL-NULIPA.jpg') }}"
                                        alt="No attachment available"
                                        class="perk-image"
                                        onclick="openModal(this.src)"
                                    >
                                @endif
                            </div>
                        </div>
                        
                        <div class="perks-tools">
                            <div class="perks-tools-analytics">
                                <span>Analytics</span>
                                <p>👁 No Data Yet</p>
                            </div>
                            <div class="perk-action-buttons">
                                @if ((int) $perk->status === 1 || is_null($perk->status))
                                    <a href="{{ route('perks.edit', $perk->id) }}" class="perk-edit-archive-btn edit-btn">Edit</a>
                                    <form action="{{ route('perks.destroy', $perk->id) }}" method="POST" onsubmit="return confirm('Archive this perk?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="perk-edit-archive-btn archive-btn">Archive</button>
                                    </form>
                                @else
                                    <form action="{{ route('perks.restore', $perk->id) }}" method="POST" onsubmit="return confirm('Unarchive this perk?')">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="perk-edit-archive-btn perk-unarchive-btn">Unarchive</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="perks-container" style="justify-content: center;">
                        <p class="perks-description-text">No perks available.</p>
                    </div>
                @endforelse

                <div class="pagination-container bottom-pagination">
                    {{ $perks->links() }}
                </div>
            </div>
        </div>
    </div>

    <div id="imageModal" class="custom-modal" onclick="closeModal()">
        <span class="close-modal">&times;</span>
        <img class="modal-content" id="enlargedImage">
    </div>

    <script>
        // Toggle archived button label/color based on current URL
        document.addEventListener('DOMContentLoaded', function () {
            const btn = document.getElementById('archiveToggleBtn');
            if (!btn) return;
            const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
            const currentPath = window.location.pathname.replace(/\/$/, '');

            if (currentPath === archivedPath) {
                // We're on the archived page — make button active and link back to active perks
                btn.classList.add('active');
                btn.textContent = 'View Active Perks';
                btn.href = '{{ route('perks.index') }}';
            } else {
                // Ensure default state
                btn.classList.remove('active');
                btn.textContent = 'Archived Perks';
                btn.href = '{{ route('perks.archived') }}';
            }
        });

        function openModal(src) {
            const modal = document.getElementById("imageModal");
            const modalImg = document.getElementById("enlargedImage");
            modal.style.display = "flex";
            modalImg.src = src;
        }

        function closeModal() {
            document.getElementById("imageModal").style.display = "none";
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === "Escape") { closeModal(); }
        });
    </script>

</body>
</html>