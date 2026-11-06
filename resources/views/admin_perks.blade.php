<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perks and Discounts | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Stylesheets -->
    {{-- <link rel="stylesheet" href="/css/admin_dashboard.css"> --}}
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/perks_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
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
                    {{-- <img src="/assets/logos/NU_Lipa_Alumni_Office.png" alt="NU Lipa Alumni Affairs" class="logo-nu"> --}}
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
                            <i class="fa-solid fa-gift"></i>
                            Perks & Discounts
                        </h1>
                        <p class="page-subtitle">Manage exclusive offers for NU Lipa alumni members</p>
                    </div>
                    <div class="header-actions">
                        @if (!request()->routeIs('perks.archived'))
                            <a href="{{ route('perks.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Add New Perk</span>
                            </a>
                        @endif
                        <a id="archiveToggleBtn"
                           href="{{ route('perks.archived') }}"
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
                                <i class="fa-solid fa-gift"></i>
                            </div>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ $totalPerks ?? 0 }}</span>
                            <span class="stat-label">Total Perks</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon active">
                                <i class="fa-solid fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ $activePerks ?? 0 }}</span>
                            <span class="stat-label">Active Perks</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon-wrapper">
                            <div class="stat-icon archived">
                                <i class="fa-solid fa-clock-rotate-left"></i>
                            </div>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">{{ $archivedPerks ?? 0 }}</span>
                            <span class="stat-label">Archived</span>
                        </div>
                    </div>
                </div>

            <!-- Perks Card Grid -->
            <div class="perks-grid">
                @forelse ($perks as $perk)
                    <article class="perk-card" data-perk-id="{{ $perk->id }}">
                        <div class="perk-card-wrapper">
                            <div class="perk-card-header">
                                
                                @php
                                    $isRestored = !empty($perk->is_manually_restored);
                                    $isActive   = ($perk->status == 1 || is_null($perk->status));
                                @endphp

                                <div class="perk-status-badge {{ $isRestored ? 'restored' : ($isActive ? 'active' : 'archived') }}">
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
                            </div>

                            @if(!empty($perk->is_manually_restored))
                                <div class="perk-restored-banner">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <span>
                                        Restored for admin access — <strong>hidden from alumni</strong>.
                                        @if($perk->manually_restored_at)
                                            Restored {{ $perk->manually_restored_at->diffForHumans() }}.
                                        @endif
                                    </span>
                                </div>
                            @endif
                            
                            <div class="perk-card-body">
                                <div class="perk-content">
                                    <h3 class="perk-title">{{ $perk->title }}</h3>
                                    <p class="perk-description">{{ Str::limit($perk->description, 120) }}</p>
                                </div>
                                
                                <!-- Gallery Preview -->
                                @include('partials.perk-media', ['perk' => $perk])
                                                      
                            </div>
                            
                            <div class="perk-card-footer">
                                <div class="perk-analytics">
                                    <div class="analytics-item">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>Until {{ $perk->valid_until->format('M d') }}</span>
                                    </div>
                                    {{-- <div class="analytics-item">
                                        <i class="fa-regular fa-eye"></i>
                                        <span>{{ $perk->views ?? 0 }}</span>
                                    </div> --}}
                                </div>
                                <div class="perk-actions">
                                    @if ((int) $perk->status === 1 || is_null($perk->status))
                                        {{-- Active: Show Edit + Archive --}}
                                        <a href="{{ route('perks.edit', $perk) }}" class="btn-action btn-edit" title="Edit Perk">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('perks.destroy', $perk) }}" method="POST" class="inline-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-archive" title="Archive Perk">
                                                <i class="fa-solid fa-box-archive"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Archived: Show Restore + Permanent Delete --}}
                                        <form action="{{ route('perks.restore', $perk) }}" method="POST" class="inline-form">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn-action btn-unarchive" title="Restore Perk">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- Permanent Delete (only for archived) --}}
                                        <form action="{{ route('perks.permanent-delete', $perk->id) }}" 
                                            method="POST" class="inline-form">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn-action" style="background:#fee; color:#ef4444;" title="Delete Permanently">
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
                                <i class="fa-solid fa-gift"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No perks found</h3>
                        <p class="empty-description">
                            @if (request()->routeIs('perks.archived'))
                                There are no archived perks at the moment.
                            @else
                                Get started by creating your first alumni perk or discount offer.
                            @endif
                        </p>
                        @if (!request()->routeIs('perks.archived'))
                            <a href="{{ route('perks.create') }}" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Create First Perk</span>
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($perks->hasPages())
            <div class="pagination-wrapper">
                {{ $perks->links() }}
            </div>
            @endif
        </main>
    </div>

    <!-- ============================================
     PERK MEDIA LIGHTBOX GALLERY
     ============================================ -->
    <div id="perkMediaLightbox" class="lightbox-overlay" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="lightbox-backdrop" data-perk-lightbox-close></div>

        <div class="lightbox-shell">
            <div class="lightbox-topbar">
                <div class="lightbox-counter">
                    <i class="fa-regular fa-images"></i>
                    <span id="perkLightboxCounter">1 / 1</span>
                </div>
                <div class="lightbox-topbar-actions">
                    <a id="perkLightboxDownload"
                    class="lightbox-btn"
                    href="#"
                    download
                    title="Download">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button type="button"
                            class="lightbox-btn"
                            id="perkLightboxClose"
                            title="Close (Esc)"
                            data-perk-lightbox-close>
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <button type="button"
                    class="lightbox-nav lightbox-prev"
                    id="perkLightboxPrev"
                    title="Previous (←)"
                    aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <div class="lightbox-stage" id="perkLightboxStage">
                <img id="perkLightboxImage"
                    class="lightbox-media"
                    alt="Perk media">
            </div>

            <button type="button"
                    class="lightbox-nav lightbox-next"
                    id="perkLightboxNext"
                    title="Next (→)"
                    aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <div class="lightbox-thumbs" id="perkLightboxThumbs"></div>
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
    // Mobile menu toggle
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    // Close sidebar when clicking on a nav item (mobile)
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) toggleMobileMenu();
        });
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
                archiveBtn.innerHTML = '<i class="fa-solid fa-list"></i> <span class="btn-text">Active Perks</span>';
                archiveBtn.href = '{{ route('perks.index') }}';
            }
        }

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
        warningOverlay.addEventListener('click', function(e) { if (e.target === warningOverlay) closeWarningModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && warningOverlay.classList.contains('active')) closeWarningModal(); });

        confirmBtn.addEventListener('click', function() {
            if (pendingForm) pendingForm.submit();
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
            if (iconType === 'danger') iconElement.className = 'fa-solid fa-triangle-exclamation';
            else if (iconType === 'success') iconElement.className = 'fa-solid fa-circle-question';
            else iconElement.className = 'fa-solid fa-triangle-exclamation';

            confirmBtn.className = 'btn ' + confirmClass;
            confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;
            warningOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();
        }

        // Intercept all inline forms (Archive, Restore, Permanent Delete)
        document.querySelectorAll('.inline-form').forEach(function(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                pendingForm = form;
                const action = this.action;

                if (action.includes('restore')) {
                    showWarningModal({
                        title: 'Restore Perk',
                        message: 'Are you sure you want to <strong>restore</strong> this perk?<br><small>It will be moved back to active perks and visible to alumni.</small>',
                        iconType: 'success',
                        confirmText: 'Restore',
                        confirmClass: 'btn-success'
                    });
                } else if (action.includes('permanent-delete')) {
                    showWarningModal({
                        title: 'Delete Permanently',
                        message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you absolutely sure you want to <strong>permanently delete</strong> this perk?<br><small>All associated images will be permanently removed.</small>',
                        iconType: 'danger',
                        confirmText: 'Delete Permanently',
                        confirmClass: 'btn-danger'
                    });
                } else {
                    // Default: Archive
                    showWarningModal({
                        title: 'Archive Perk',
                        message: 'Are you sure you want to <strong>archive</strong> this perk?<br><small>It will be moved to the archived section and hidden from alumni.</small>',
                        iconType: 'warning',
                        confirmText: 'Archive',
                        confirmClass: 'btn-warning'
                    });
                }
            });
        });
    });

    /* =========================================================
   PERK MEDIA LIGHTBOX — Full Featured (image-only)
   ========================================================= */

(function () {
    const lightbox   = document.getElementById('perkMediaLightbox');
    if (!lightbox) return;

    const stage      = document.getElementById('perkLightboxStage');
    const imgEl      = document.getElementById('perkLightboxImage');
    const counterEl  = document.getElementById('perkLightboxCounter');
    const thumbsEl   = document.getElementById('perkLightboxThumbs');
    const prevBtn    = document.getElementById('perkLightboxPrev');
    const nextBtn    = document.getElementById('perkLightboxNext');
    const downloadEl = document.getElementById('perkLightboxDownload');

    let items   = [];
    let current = 0;

    function renderItem() {
        const item = items[current];
        if (!item) return;

        imgEl.src = item.url;

        counterEl.textContent = `${current + 1} / ${items.length}`;

        downloadEl.href = item.url;
        downloadEl.setAttribute('download', `perk-${item.id}.${item.extension || 'jpg'}`);

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
            btn.innerHTML = `<img src="${item.url}" alt="Thumbnail ${i + 1}" loading="lazy">`;

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
    }

    function go(delta) {
        if (items.length <= 1) return;
        current = (current + delta + items.length) % items.length;
        renderItem();
    }

    prevBtn.addEventListener('click', (e) => { e.stopPropagation(); go(-1); });
    nextBtn.addEventListener('click', (e) => { e.stopPropagation(); go(1); });

    lightbox.querySelectorAll('[data-perk-lightbox-close]').forEach(el => {
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

    // Delegated click on tiles — skip empty placeholder
    document.addEventListener('click', (e) => {
        const tile = e.target.closest('.perk-media .media-tile');
        if (!tile) return;
        if (tile.classList.contains('is-empty')) return;

        const wrapper = tile.closest('.perk-media');
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

</body>
</html>

{{-- This is the admin_perks.blade.php file --}}