<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/announcements_modern.css">
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
                            <i class="fa-solid fa-bullhorn"></i>
                            Announcements
                        </h1>
                        <p class="page-subtitle">Share important updates and news with NU Lipa alumni</p>
                    </div>
                    
                    <div class="header-actions">
                        @if (!request()->routeIs('announcements.archived'))
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Add New</span>
                            </a>
                            
                            <!-- Filter Buttons - Only show on active announcements page -->
                            <div class="filter-buttons" style="display: flex; gap: 0.5rem;">
                                <a href="{{ route('announcements.index', ['filter' => 'all']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'all' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    All
                                </a>
                                <a href="{{ route('announcements.index', ['filter' => 'active']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'active' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    Published
                                </a>
                                <a href="{{ route('announcements.index', ['filter' => 'scheduled']) }}" 
                                class="btn btn-sm {{ ($filter ?? 'all') === 'scheduled' ? 'btn-primary' : 'btn-secondary' }}"
                                style="padding: 0.5rem 1rem; font-size: 0.875rem;">
                                    Scheduled
                                </a>
                            </div>
                        @endif
                        
                        <a id="archiveToggleBtn"
                        href="{{ route('announcements.archived') }}"
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
                            <i class="fa-solid fa-bullhorn"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value" id="stat-total">{{ $totalAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Total Announcements</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value" id="stat-active">{{ $activeAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Active</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon views">
                            <i class="fa-regular fa-calendar"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value" id="stat-scheduled">{{ $scheduledAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Scheduled</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon archived">
                            <i class="fa-solid fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value" id="stat-archived">{{ $archivedAnnouncements ?? 0 }}</span>
                        <span class="stat-label">Archived</span>
                    </div>
                </div>
                
            </div>

            <!-- Announcements Card Grid -->
            <div class="announcements-grid" id="announcementsGrid">
                @forelse ($announcements as $announcement)
                    <article class="announcement-card" data-announcement-id="{{ $announcement->id }}" data-status="{{ (int) $announcement->status === 0 ? 'archived' : 'active' }}">
                        <div class="announcement-card-wrapper">
                            <div class="announcement-card-header">
                                <div class="announcement-status-badge {{ (int) $announcement->status === 0 ? 'archived' : 'active' }}" id="status-badge-{{ $announcement->id }}">
                                    <i class="fa-solid fa-circle"></i>
                                    <span>{{ (int) $announcement->status === 0 ? 'Archived' : 'Active' }}</span>
                                </div>
                                
                                @if ($announcement->scheduled_at && $announcement->scheduled_at->timestamp > now()->timestamp && (int) $announcement->status !== 0)
                                    <div class="announcement-scheduled-badge" id="scheduled-badge-{{ $announcement->id }}">
                                        <i class="fa-regular fa-clock"></i>
                                        <span>Scheduled</span>
                                    </div>
                                @endif

                            </div>
                            
                            <div class="announcement-card-body">
                                <div class="announcement-content">
                                    <h3 class="announcement-title">{{ $announcement->title }}</h3>
                                    <p class="announcement-description">{{ Str::limit($announcement->announcement_description, 150) }}</p>
                                </div>
                                
                                <!-- Date Meta -->
                                <div class="announcement-dates">
                                    @php
                                        $hasScheduled = $announcement->scheduled_at !== null;
                                        $isScheduledFuture = $hasScheduled && $announcement->scheduled_at->timestamp > now()->timestamp;
                                        $isPublished = $hasScheduled && !$isScheduledFuture;
                                        
                                        // Determine which date to show
                                        if ($isPublished) {
                                            // Was scheduled, now published - show scheduled date as published date
                                            $displayDate = $announcement->scheduled_at;
                                            $dateLabel = 'Published';
                                        } elseif (!$hasScheduled) {
                                            // Never scheduled - show date_posted as published
                                            $displayDate = $announcement->date_posted ?? $announcement->created_at;
                                            $dateLabel = 'Published';
                                        } else {
                                            // Scheduled for future - show when it was created/posted to system
                                            $displayDate = $announcement->date_posted ?? $announcement->created_at;
                                            $dateLabel = 'Posted';
                                        }
                                    @endphp

                                    <div class="date-item" id="date-item-{{ $announcement->id }}">
                                        <i class="fa-regular fa-calendar"></i>
                                        <span>{{ $dateLabel }}: {{ $displayDate->format('M d, Y') }}</span>
                                    </div>

                                    {{-- Show countdown ONLY if scheduled for the future --}}
                                    @if ($isScheduledFuture)
                                        <div class="date-item scheduled" 
                                            id="countdown-{{ $announcement->id }}" 
                                            data-target-pst="{{ $announcement->scheduled_at->format('Y-m-d\TH:i:s') }}"
                                            data-published-date="{{ $announcement->scheduled_at->format('M d, Y \a\t h:i A') }}">
                                            <i class="fa-solid fa-hourglass-half"></i>
                                            <span>Posts in: <span class="countdown-text" style="font-weight: 600;">Loading...</span></span>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Attachments Preview -->
                                @include('partials.announcement-media', ['announcement' => $announcement])

                            </div>
                            
                            <div class="announcement-card-footer">
                                <div class="announcement-analytics">
                                </div>
                                
                                <div class="announcement-actions">
                                    @if ((int) $announcement->status === 0)
                                        {{-- Archived: Show Restore + Permanent Delete --}}
                                        <form action="{{ route('announcements.restore', $announcement->id) }}" 
                                            method="POST" class="inline-form">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn-action btn-restore" title="Restore">
                                                <i class="fa-solid fa-rotate-left"></i>
                                            </button>
                                        </form>
                                        
                                        {{-- ONLY show permanent delete if archived --}}
                                        <form action="{{ route('announcements.permanent-delete', $announcement->id) }}" 
                                            method="POST" class="inline-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action" style="background:#fee; color:#ef4444;" title="Delete Permanently">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    @else
                                        {{-- Active: Show Edit + Archive --}}
                                        <a href="{{ route('announcements.edit', $announcement->id) }}" class="btn-action btn-edit" title="Edit">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                        <form action="{{ route('announcements.destroy', $announcement->id) }}" 
                                            method="POST" class="inline-form">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn-action btn-archive" title="Archive">
                                                <i class="fa-solid fa-box-archive"></i>
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
                                <i class="fa-solid fa-bullhorn"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No announcements found</h3>
                        <p class="empty-description">
                            @if (request()->routeIs('announcements.archived'))
                                There are no archived announcements at the moment.
                            @else
                                Start sharing important updates and news with your alumni community.
                            @endif
                        </p>
                        @if (!request()->routeIs('announcements.archived'))
                            <a href="{{ route('announcements.create') }}" class="btn btn-primary btn-lg">
                                <i class="fa-solid fa-plus"></i> 
                                <span>Create First Announcement</span>
                            </a>
                        @endif
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if ($announcements->hasPages())
            <div class="pagination-wrapper">
                {{ $announcements->links() }}
            </div>
            @endif
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
                <p class="warning-modal-message" id="warningModalMessage">Are you sure you want to proceed with this action?</p>
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

     <!-- ============================================
        MEDIA LIGHTBOX GALLERY
        ============================================ -->
    <div id="mediaLightbox" class="lightbox-overlay" role="dialog" aria-modal="true" aria-hidden="true">
        <div class="lightbox-backdrop" data-lightbox-close></div>

        <div class="lightbox-shell">
            <!-- Top bar -->
            <div class="lightbox-topbar">
                <div class="lightbox-counter">
                    <i class="fa-regular fa-images"></i>
                    <span id="lightboxCounter">1 / 1</span>
                </div>
                <div class="lightbox-topbar-actions">
                    <a id="lightboxDownload"
                    class="lightbox-btn"
                    href="#"
                    download
                    title="Download">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button type="button"
                            class="lightbox-btn"
                            id="lightboxClose"
                            title="Close (Esc)"
                            data-lightbox-close>
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <!-- Prev / Next -->
            <button type="button"
                    class="lightbox-nav lightbox-prev"
                    id="lightboxPrev"
                    title="Previous (←)"
                    aria-label="Previous">
                <i class="fa-solid fa-chevron-left"></i>
            </button>

            <!-- Media stage -->
            <div class="lightbox-stage" id="lightboxStage">
                <img id="lightboxImage"
                    class="lightbox-media"
                    alt="Announcement media"
                    style="display:none;">
                <video id="lightboxVideo"
                    class="lightbox-media"
                    controls
                    playsinline
                    preload="metadata"
                    style="display:none;"></video>
            </div>

            <button type="button"
                    class="lightbox-nav lightbox-next"
                    id="lightboxNext"
                    title="Next (→)"
                    aria-label="Next">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

            <!-- Thumbnail strip -->
            <div class="lightbox-thumbs" id="lightboxThumbs"></div>
        </div>
    </div>

    <script>
// Mobile menu toggle
function toggleMobileMenu() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('mobileOverlay');
    if (sidebar) sidebar.classList.toggle('mobile-open');
    if (overlay) overlay.classList.toggle('active');
    document.body.style.overflow = sidebar && sidebar.classList.contains('mobile-open') ? 'hidden' : '';
}

// ========================================
// COUNTDOWN TIMER SYSTEM WITH AUTO-REFRESH
// ========================================

let countdownIntervals = {};

function startCountdown(element) {
    const targetPst = element.getAttribute('data-target-pst');
    const publishedDate = element.getAttribute('data-published-date');
    const countdownText = element.querySelector('.countdown-text');
    const announcementId = element.id.replace('countdown-', '');
    
    if (!targetPst || !countdownText) {
        console.log('Countdown skipped - missing data:', element.id, {targetPst, countdownText});
        return;
    }
    
    // Parse the target date directly (database stores PST)
    const targetDate = new Date(targetPst);
    const targetTimeMs = targetDate.getTime();
    
    console.log('Starting countdown for:', element.id);
    console.log('  Target date:', targetDate.toString());
    console.log('  Timezone:', Intl.DateTimeFormat().resolvedOptions().timeZone);
    
    // Clear any existing interval
    if (countdownIntervals[element.id]) {
        clearInterval(countdownIntervals[element.id]);
    }
    
    function updateCountdown() {
        const now = Date.now();
        const distance = targetTimeMs - now;
        
        if (distance < 0) {
            // Countdown finished - update UI
            clearInterval(countdownIntervals[element.id]);
            delete countdownIntervals[element.id];
            
            const parentSpan = countdownText.parentElement;
            parentSpan.innerHTML = 'Published: ' + publishedDate;
            
            const icon = element.querySelector('.fa-hourglass-half');
            if (icon) {
                icon.className = 'fa-solid fa-check-circle';
                icon.style.color = '#10b981';
            }
            
            element.classList.remove('scheduled');
            element.classList.add('published');
            
            // Update the card UI
            const card = document.querySelector(`.announcement-card[data-announcement-id="${announcementId}"]`);
            if (card) {
                // Remove scheduled badge
                const scheduledBadge = card.querySelector('.announcement-scheduled-badge');
                if (scheduledBadge) {
                    scheduledBadge.style.display = 'none';
                }
                
                // Update status badge
                const statusBadge = card.querySelector('.announcement-status-badge');
                if (statusBadge) {
                    statusBadge.className = 'announcement-status-badge active';
                    statusBadge.innerHTML = `
                        <i class="fa-solid fa-circle"></i>
                        <span>Active</span>
                    `;
                }
                
                // Update card data attribute
                card.dataset.status = 'active';
            }
            
            // Auto-refresh the stats without page reload
            refreshAnnouncementStats(announcementId);
            
            // Show notification
            showNotification('📢 "' + (card ? card.querySelector('.announcement-title')?.textContent || 'Announcement' : 'Announcement') + '" is now published!', 'success');
            
            console.log('Countdown finished for:', element.id);
            return;
        }
        
        const days = Math.floor(distance / (1000 * 60 * 60 * 24));
        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);
        
        let displayText = '';
        if (days > 0) {
            displayText = days + 'd ' + hours + 'h ' + minutes + 'm ' + seconds + 's';
        } else if (hours > 0) {
            displayText = hours + 'h ' + minutes + 'm ' + seconds + 's';
        } else if (minutes > 0) {
            displayText = minutes + 'm ' + seconds + 's';
        } else {
            displayText = seconds + 's';
        }
        
        countdownText.textContent = displayText;
    }
    
    updateCountdown();
    countdownIntervals[element.id] = setInterval(updateCountdown, 1000);
}

// ========================================
// AUTO-REFRESH STATS WITHOUT PAGE RELOAD
// ========================================

function refreshAnnouncementStats(announcementId) {
    // Fetch the updated stats from the server
    fetch(`/admin/announcements/check-status/${announcementId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Update stats
            if (data.stats) {
                updateStats(data.stats);
            }
            
            // If there are more scheduled announcements, we could refresh them too
            // But this is enough for the stats update
        })
        .catch(error => {
            console.error('Error refreshing announcement stats:', error);
            // Fallback: just update the stats with local changes
            updateStatsLocally();
        });
}

function updateStats(stats) {
    if (!stats) return;
    
    // Update stat values with animation
    const statElements = {
        total: document.getElementById('stat-total'),
        active: document.getElementById('stat-active'),
        scheduled: document.getElementById('stat-scheduled'),
        archived: document.getElementById('stat-archived')
    };
    
    if (stats.total !== undefined && statElements.total) {
        animateNumber(statElements.total, parseInt(statElements.total.textContent), stats.total);
    }
    if (stats.active !== undefined && statElements.active) {
        animateNumber(statElements.active, parseInt(statElements.active.textContent), stats.active);
    }
    if (stats.scheduled !== undefined && statElements.scheduled) {
        animateNumber(statElements.scheduled, parseInt(statElements.scheduled.textContent), stats.scheduled);
    }
    if (stats.archived !== undefined && statElements.archived) {
        animateNumber(statElements.archived, parseInt(statElements.archived.textContent), stats.archived);
    }
}

function animateNumber(element, start, end) {
    if (!element) return;
    const duration = 500;
    const startTime = performance.now();
    
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const current = Math.round(start + (end - start) * progress);
        element.textContent = current;
        
        if (progress < 1) {
            requestAnimationFrame(update);
        }
    }
    requestAnimationFrame(update);
}

function updateStatsLocally() {
    // Fallback: recalculate based on visible cards
    const cards = document.querySelectorAll('.announcement-card');
    let active = 0, scheduled = 0, archived = 0;
    
    cards.forEach(card => {
        const status = card.dataset.status;
        if (status === 'active') {
            const hasScheduledBadge = card.querySelector('.announcement-scheduled-badge:not([style*="display: none"])');
            if (hasScheduledBadge) {
                scheduled++;
            } else {
                active++;
            }
        } else if (status === 'archived') {
            archived++;
        }
    });
    
    const total = active + scheduled + archived;
    updateStats({ total, active, scheduled, archived });
}

// ========================================
// NOTIFICATION SYSTEM
// ========================================

function showNotification(message, type = 'info') {
    // Check if notification container exists
    let container = document.getElementById('notificationContainer');
    if (!container) {
        container = document.createElement('div');
        container.id = 'notificationContainer';
        container.style.cssText = `
            position: fixed;
            top: 1.5rem;
            right: 1.5rem;
            z-index: 9999;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            max-width: 400px;
            width: 100%;
            pointer-events: none;
        `;
        document.body.appendChild(container);
    }
    
    const colors = {
        success: { bg: '#d1fae5', border: '#10b981', text: '#065f46', icon: 'fa-check-circle' },
        info: { bg: '#dbeafe', border: '#3b82f6', text: '#1e40af', icon: 'fa-info-circle' },
        warning: { bg: '#fef3c7', border: '#f59e0b', text: '#92400e', icon: 'fa-exclamation-triangle' },
        error: { bg: '#fee2e2', border: '#ef4444', text: '#991b1b', icon: 'fa-times-circle' }
    };
    
    const color = colors[type] || colors.info;
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        padding: 1rem 1.25rem;
        border-radius: 0.5rem;
        background: ${color.bg};
        border-left: 4px solid ${color.border};
        color: ${color.text};
        font-family: 'Poppins', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        animation: slideIn 0.3s ease-out;
        pointer-events: auto;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        cursor: pointer;
    `;
    notification.innerHTML = `
        <i class="fa-solid ${color.icon}" style="font-size: 1.25rem; color: ${color.border};"></i>
        <span>${message}</span>
    `;
    
    // Add click to dismiss
    notification.addEventListener('click', function() {
        dismissNotification(this);
    });
    
    container.appendChild(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        dismissNotification(notification);
    }, 5000);
}

function dismissNotification(notification) {
    if (!notification || !notification.parentNode) return;
    notification.style.opacity = '0';
    notification.style.transform = 'translateX(100%)';
    notification.style.transition = 'all 0.3s ease-out';
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 300);
}

// Add slide-in animation
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
`;
document.head.appendChild(style);

// ========================================
// COUNTDOWN INITIALIZATION
// ========================================

function initializeCountdowns() {
    const countdownElements = document.querySelectorAll('[id^="countdown-"]');
    console.log('Found countdown elements:', countdownElements.length);
    
    countdownElements.forEach(element => {
        const targetPst = element.getAttribute('data-target-pst');
        console.log('Element:', element.id, 'Target:', targetPst);
        if (targetPst) {
            startCountdown(element);
        }
    });
}

window.addEventListener('beforeunload', function() {
    Object.keys(countdownIntervals).forEach(key => {
        clearInterval(countdownIntervals[key]);
    });
    countdownIntervals = {};
});

// ========================================
// WARNING MODAL SYSTEM
// ========================================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize countdowns
    initializeCountdowns();
    
    // Modal elements
    const warningOverlay = document.getElementById('warningModal');
    const warningTitle = document.getElementById('warningModalTitle');
    const warningMessage = document.getElementById('warningModalMessage');
    const warningIcon = document.getElementById('warningModalIcon');
    const confirmBtn = document.getElementById('warningModalConfirm');
    const cancelBtn = document.getElementById('warningModalCancel');
    
    let pendingForm = null;
    
    function closeWarningModal() {
        if (warningOverlay) warningOverlay.classList.remove('active');
        document.body.style.overflow = '';
        pendingForm = null;
    }
    
    if (cancelBtn) cancelBtn.addEventListener('click', closeWarningModal);
    
    if (warningOverlay) {
        warningOverlay.addEventListener('click', function(e) {
            if (e.target === warningOverlay) closeWarningModal();
        });
    }
    
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && warningOverlay && warningOverlay.classList.contains('active')) {
            closeWarningModal();
        }
    });
    
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function() {
            if (pendingForm) pendingForm.submit();
            closeWarningModal();
        });
    }
    
    function showWarningModal(config) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure?',
            iconType = 'warning',
            confirmText = 'Confirm',
            confirmClass = 'btn-danger'
        } = config;
        
        if (!warningOverlay || !warningTitle || !warningMessage || !warningIcon || !confirmBtn) return;
        
        warningTitle.textContent = title;
        warningMessage.innerHTML = message;
        warningIcon.className = 'warning-modal-icon ' + iconType;
        
        const iconElement = warningIcon.querySelector('i');
        if (iconElement) {
            if (iconType === 'danger') {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            } else if (iconType === 'success') {
                iconElement.className = 'fa-solid fa-circle-question';
            } else {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            }
        }
        
        confirmBtn.className = 'btn ' + confirmClass;
        confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;
        
        warningOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        confirmBtn.focus();
    }
    
    document.querySelectorAll('.inline-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitButton = form.querySelector('button[type="submit"]');
            if (!submitButton) return;
            
            const isArchive = submitButton.classList.contains('btn-archive');
            const isRestore = submitButton.classList.contains('btn-restore');
            const hasTrashIcon = submitButton.querySelector('.fa-trash-can') !== null;
            
            pendingForm = form;
            
            if (isArchive) {
                showWarningModal({
                    title: 'Archive Announcement',
                    message: 'Are you sure you want to <strong>archive</strong> this announcement?<br><small>It will be moved to the archived section and hidden from alumni.</small>',
                    iconType: 'warning',
                    confirmText: 'Archive',
                    confirmClass: 'btn-warning'
                });
            } else if (isRestore) {
                showWarningModal({
                    title: 'Restore Announcement',
                    message: 'Are you sure you want to <strong>restore</strong> this announcement?<br><small>It will be moved back to active announcements and visible to alumni.</small>',
                    iconType: 'success',
                    confirmText: 'Restore',
                    confirmClass: 'btn-success'
                });
            } else if (hasTrashIcon) {
                showWarningModal({
                    title: 'Delete Permanently',
                    message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you absolutely sure you want to <strong>permanently delete</strong> this announcement?<br><small>All associated data, images, and attachments will be permanently removed.</small>',
                    iconType: 'danger',
                    confirmText: 'Delete Permanently',
                    confirmClass: 'btn-danger'
                });
            }
        });
    });
    
    // Archive toggle button logic
    const btn = document.getElementById('archiveToggleBtn');
    if (btn) {
        const archivedPath = new URL(btn.href).pathname.replace(/\/$/, '');
        const currentPath = window.location.pathname.replace(/\/$/, '');
        if (currentPath === archivedPath) {
            btn.classList.add('active');
            btn.innerHTML = '<i class="fa-solid fa-list"></i> <span class="btn-text">Active Announcements</span>';
            btn.href = '{{ route('announcements.index') }}';
        }
    }
    
    // Close sidebar on nav item click (mobile)
    document.querySelectorAll('.nav-item').forEach(function(item) {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });
});

/* =========================================================
   LIGHTBOX GALLERY — Full Featured
   ========================================================= */

(function () {
    const lightbox   = document.getElementById('mediaLightbox');
    if (!lightbox) return;

    const stage      = document.getElementById('lightboxStage');
    const imgEl      = document.getElementById('lightboxImage');
    const videoEl    = document.getElementById('lightboxVideo');
    const counterEl  = document.getElementById('lightboxCounter');
    const thumbsEl   = document.getElementById('lightboxThumbs');
    const prevBtn    = document.getElementById('lightboxPrev');
    const nextBtn    = document.getElementById('lightboxNext');
    const downloadEl = document.getElementById('lightboxDownload');

    let items   = [];
    let current = 0;

    /* ---------- Render current item ---------- */
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
        downloadEl.setAttribute('download', `announcement-${item.id}.${item.extension || ''}`);

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

    /* ---------- Build thumbnail strip ---------- */
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

    /* ---------- Open ---------- */
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

    /* ---------- Close ---------- */
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

    /* ---------- Navigation ---------- */
    function go(delta) {
        if (items.length <= 1) return;
        current = (current + delta + items.length) % items.length;
        renderItem();
    }

    prevBtn.addEventListener('click', (e) => { e.stopPropagation(); go(-1); });
    nextBtn.addEventListener('click', (e) => { e.stopPropagation(); go(1); });

    /* ---------- Close handlers ---------- */
    lightbox.querySelectorAll('[data-lightbox-close]').forEach(el => {
        el.addEventListener('click', closeLightbox);
    });

    document.addEventListener('keydown', (e) => {
        if (!lightbox.classList.contains('active')) return;
        if (e.key === 'Escape')     closeLightbox();
        if (e.key === 'ArrowLeft')  go(-1);
        if (e.key === 'ArrowRight') go(1);
    });

    /* ---------- Swipe support ---------- */
    let touchStartX = 0;
    stage.addEventListener('touchstart', (e) => {
        touchStartX = e.touches[0].clientX;
    }, { passive: true });

    stage.addEventListener('touchend', (e) => {
        const dx = e.changedTouches[0].clientX - touchStartX;
        if (Math.abs(dx) > 60) go(dx < 0 ? 1 : -1);
    }, { passive: true });

    /* ---------- Delegate clicks on tiles ---------- */
    document.addEventListener('click', (e) => {
        const tile = e.target.closest('.media-tile');
        if (!tile) return;

        const wrapper = tile.closest('.announcement-media');
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

// Handle window resize
let resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        if (window.innerWidth > 1024) {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            if (sidebar) sidebar.classList.remove('mobile-open');
            if (overlay) overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
    }, 250);
});
</script>

</body>
</html>