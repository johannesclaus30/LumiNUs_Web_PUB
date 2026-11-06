<!-- ============================================ -->
<!-- TAB 5: MENTORSHIP APPLICATIONS -->
<!-- ============================================ -->

<div id="tab-mentorship" class="tab-content">
    <section class="dashboard-section">
        <!-- Section Header -->
        <div class="section-header">
            <div class="header-title-section">
                <h2 class="section-title">
                    <i class="fas fa-handshake"></i>
                    Mentorship Applications
                </h2>
                <p class="section-subtitle">Review and manage mentorship requests from alumni</p>
            </div>
            <div class="section-actions">
                <button class="btn btn-sm btn-export" onclick="exportData('mentorship', 'pdf')">
                    <i class="fas fa-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-export" onclick="exportData('mentorship', 'excel')">
                    <i class="fas fa-file-excel"></i> Excel
                </button>
                <span class="badge-notification" id="pendingMentorshipCount" style="background: #F59E0B;">
                    {{ $pendingMentorships ?? 0 }}
                </span>
            </div>
        </div>

        <!-- Mentorship Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #32418C;">
                        <i class="fas fa-handshake"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($totalMentorships ?? 0) }}</span>
                    <span class="stat-label">Total Applications</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #F59E0B;">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($pendingMentorships ?? 0) }}</span>
                    <span class="stat-label">Pending Review</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #10B981;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($activeMentorships ?? 0) }}</span>
                    <span class="stat-label">Active</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #6B7280;">
                        <i class="fas fa-flag-checkered"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($completedMentorships ?? 0) }}</span>
                    <span class="stat-label">Completed</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #EF4444;">
                        <i class="fas fa-times-circle"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($declinedMentorships ?? 0) }}</span>
                    <span class="stat-label">Declined</span>
                </div>
            </div>
        </div>

        <!-- Applications Table -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <div class="card-header-left">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Applications
                        <span class="badge-count" id="applicationCount">{{ ($mentorships ?? collect())->count() }}</span>
                    </h3>
                </div>
                <div class="card-actions">
                    <div class="filter-group">
                        <div class="filter-pills">
                            <button class="filter-pill active" data-filter="all" onclick="setFilter('all', this)">
                                <i class="fas fa-list"></i> All
                                <span class="filter-count">{{ ($mentorships ?? collect())->count() }}</span>
                            </button>
                            <button class="filter-pill" data-filter="pending" onclick="setFilter('pending', this)">
                                <i class="fas fa-clock"></i> Pending
                                <span class="filter-count">{{ $pendingMentorships ?? 0 }}</span>
                            </button>
                            <button class="filter-pill" data-filter="active" onclick="setFilter('active', this)">
                                <i class="fas fa-check-circle"></i> Active
                                <span class="filter-count">{{ $activeMentorships ?? 0 }}</span>
                            </button>
                            <button class="filter-pill" data-filter="completed" onclick="setFilter('completed', this)">
                                <i class="fas fa-flag-checkered"></i> Completed
                                <span class="filter-count">{{ $completedMentorships ?? 0 }}</span>
                            </button>
                            <button class="filter-pill" data-filter="declined" onclick="setFilter('declined', this)">
                                <i class="fas fa-times-circle"></i> Declined
                                <span class="filter-count">{{ $declinedMentorships ?? 0 }}</span>
                            </button>
                        </div>
                    </div>
                    <button class="btn-refresh" onclick="refreshMentorships()">
                        <i class="fas fa-sync-alt"></i>
                        <span>Refresh</span>
                    </button>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="mentorship-table">
                    <thead>
                        <tr>
                            <th style="min-width: 200px;">Alumni</th>
                            <th style="min-width: 130px;">Event Type</th>
                            <th style="min-width: 180px;">Event Details</th>
                            <th style="min-width: 120px;">Audience</th>
                            <th style="min-width: 140px;">Proposed Date</th>
                            <th style="min-width: 110px;">Status</th>
                            <th style="min-width: 220px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="mentorshipTableBody">
                        @forelse($mentorships ?? [] as $mentorship)
                        <tr data-id="{{ $mentorship->id }}" data-status="{{ $mentorship->status }}">
                            <!-- Alumni Column -->
                            <td>
                                <div class="table-user-info">
                                    @if($mentorship->alumni && $mentorship->alumni->alumni_photo)
                                        <img src="{{ $mentorship->alumni->alumni_photo }}" alt="{{ $mentorship->alumni->first_name }}" class="table-avatar">
                                    @else
                                        <div class="table-avatar-placeholder">
                                            {{ $mentorship->alumni ? strtoupper(substr($mentorship->alumni->first_name, 0, 1) . substr($mentorship->alumni->last_name, 0, 1)) : '?' }}
                                        </div>
                                    @endif
                                    <div class="table-user-details">
                                        <div class="table-user-name">{{ $mentorship->alumni ? $mentorship->alumni->first_name . ' ' . $mentorship->alumni->last_name : 'Unknown' }}</div>
                                        <div class="table-user-email">{{ $mentorship->alumni ? $mentorship->alumni->email : 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>

                            <!-- Event Type Column -->
                            <td>
                                <span class="event-type-badge">
                                    <i class="fas fa-{{ $mentorship->event_type === 'webinar' ? 'video' : ($mentorship->event_type === 'workshop' ? 'tools' : ($mentorship->event_type === 'seminar' ? 'chalkboard-teacher' : 'calendar')) }}"></i>
                                    {{ ucfirst(str_replace('_', ' ', $mentorship->event_type ?? 'N/A')) }}
                                </span>
                            </td>

                            <!-- Event Details Column -->
                            <td>
                                <div class="event-details-cell">
                                    <div class="event-title">{{ $mentorship->event_title ?? 'N/A' }}</div>
                                    @if($mentorship->description)
                                        <div class="event-description">{{ Str::limit($mentorship->description, 80) }}</div>
                                    @endif
                                    @if($mentorship->co_speakers)
                                        <div class="event-speakers">
                                            <i class="fas fa-user-tie"></i>
                                            {{ Str::limit($mentorship->co_speakers, 40) }}
                                        </div>
                                    @endif
                                </div>
                            </td>

                            <!-- Target Audience Column -->
                            <td>
                                <span class="audience-badge">
                                    <i class="fas fa-users"></i>
                                    {{ $mentorship->target_audience ?? 'N/A' }}
                                </span>
                                @if($mentorship->expected_capacity)
                                    <div class="capacity-info">
                                        <i class="fas fa-user-plus"></i>
                                        {{ $mentorship->expected_capacity }} capacity
                                    </div>
                                @endif
                            </td>

                            <!-- Proposed Date Column -->
                            <td>
                                <div class="date-cell">
                                    <span class="date-display">
                                        <i class="fas fa-calendar-day"></i>
                                        {{ $mentorship->formatted_proposed_date ?? $mentorship->proposed_date ?? 'N/A' }}
                                    </span>
                                    @if($mentorship->start_time || $mentorship->end_time)
                                        <span class="time-display">
                                            <i class="fas fa-clock"></i>
                                            {{ $mentorship->start_time ?? 'N/A' }} 
                                            @if($mentorship->end_time)
                                                - {{ $mentorship->end_time }}
                                            @endif
                                        </span>
                                    @endif
                                    @if($mentorship->event_format)
                                        <span class="format-badge">
                                            <i class="fas fa-{{ $mentorship->event_format === 'online' ? 'wifi' : 'building' }}"></i>
                                            {{ ucfirst($mentorship->event_format ?? '') }}
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Status Column -->
                            <td>
                                <span class="status-badge status-{{ $mentorship->status }}">
                                    @if($mentorship->status === 'pending')
                                        <i class="fas fa-clock"></i>
                                    @elseif($mentorship->status === 'active')
                                        <i class="fas fa-check-circle"></i>
                                    @elseif($mentorship->status === 'completed')
                                        <i class="fas fa-flag-checkered"></i>
                                    @elseif($mentorship->status === 'declined')
                                        <i class="fas fa-times-circle"></i>
                                    @endif
                                    {{ ucfirst($mentorship->status ?? 'Unknown') }}
                                </span>
                                @if($mentorship->is_draft)
                                    <span class="draft-badge">
                                        <i class="fas fa-pen-fancy"></i> Draft
                                    </span>
                                @endif
                            </td>

                            <!-- Actions Column -->
                            <td>
                                <div class="table-actions">
                                    <!-- View Button -->
                                    <button class="btn-action btn-view" onclick="viewMentorship('{{ $mentorship->id }}')" data-title="View Full Details">
                                        <i class="fas fa-eye"></i>
                                    </button>

                                    <!-- Message Button - Redirects to messages with the alumni conversation open -->
                                    <a href="/admin/messages?chat={{ $mentorship->alumni_id }}&name={{ urlencode($mentorship->alumni ? $mentorship->alumni->first_name . ' ' . $mentorship->alumni->last_name : 'Alumni') }}" 
                                    class="btn-action btn-message" 
                                    data-title="Message Alumni">
                                        <i class="fas fa-envelope"></i>
                                    </a>

                                    @if($mentorship->status === 'pending')
                                        <!-- Approve Button -->
                                        <button class="btn-action btn-approve" onclick="confirmAction('approve', '{{ $mentorship->id }}')" data-title="Approve Application">
                                            <i class="fas fa-check"></i>
                                        </button>
                                        <!-- Decline Button -->
                                        <button class="btn-action btn-decline" onclick="confirmAction('decline', '{{ $mentorship->id }}')" data-title="Decline Application">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    @endif

                                    @if($mentorship->status === 'active')
                                        <!-- Complete Button -->
                                        <button class="btn-action btn-complete" onclick="confirmAction('complete', '{{ $mentorship->id }}')" data-title="Mark as Completed">
                                            <i class="fas fa-flag-checkered"></i>
                                        </button>
                                    @endif

                                    <!-- Attachment Button - Opens shareable URL in new tab -->
                                    @php
                                        $hasAttachment = !empty($mentorship->attached_file_url) && filter_var($mentorship->attached_file_url, FILTER_VALIDATE_URL);
                                        $fileUrl = $hasAttachment ? $mentorship->attached_file_url : '#';
                                    @endphp

                                    @if($hasAttachment)
                                        <a href="{{ $fileUrl }}" 
                                        target="_blank" 
                                        rel="noopener noreferrer"
                                        class="btn-action btn-file" 
                                        data-title="View Attachment">
                                            <i class="fas fa-paperclip"></i>
                                        </a>
                                    @else
                                        <button class="btn-action btn-file disabled" 
                                                data-title="No Attachment" 
                                                onclick="event.preventDefault(); showToast('No shareable link provided for this application.', 'warning');">
                                            <i class="fas fa-paperclip"></i>
                                        </button>
                                    @endif

                                    <!-- Delete Button - Shown for declined and completed applications -->
                                    @if(in_array($mentorship->status, ['declined', 'completed']))
                                        <button class="btn-action btn-delete" 
                                                onclick="deleteMentorship('{{ $mentorship->id }}')" 
                                                data-title="Delete Application">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    @endif
                                    
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-handshake" style="font-size: 3rem; color: var(--gray-300);"></i>
                                    <h4 style="margin: 1rem 0 0.5rem 0; color: var(--gray-600);">No applications found</h4>
                                    <p style="color: var(--gray-400);">Mentorship applications will appear here once alumni submit them</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="table-footer">
                <div class="footer-left">
                    <span><i class="fas fa-list"></i> Showing <span id="visibleCount">{{ ($mentorships ?? collect())->count() }}</span> applications</span>
                </div>
                <div class="footer-right">
                    <span><i class="fas fa-clock"></i> Last updated: {{ now()->setTimezone('Asia/Manila')->format('M d, Y g:i A') }}</span>
                </div>
            </div>
        </div>
    </section>
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

<!-- MENTORSHIP TAB JAVASCRIPT -->
<script>
// ========== MENTORSHIP FUNCTIONS ==========

function filterMentorships() {
    const filter = document.getElementById('mentorshipFilter').value;
    const rows = document.querySelectorAll('#mentorshipTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (filter === 'all' || row.dataset.status === filter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update visible count
    document.getElementById('visibleCount').textContent = visibleCount;
    
    // Update badge count
    const badge = document.querySelector('.badge-count');
    if (badge) {
        if (filter === 'all') {
            badge.textContent = rows.length;
        } else {
            badge.textContent = visibleCount;
        }
    }
}

function refreshMentorships() {
    const refreshBtn = document.querySelector('.btn-refresh');
    
    // Add loading state
    refreshBtn.classList.add('loading', 'spinning');
    refreshBtn.innerHTML = `
        <i class="fas fa-sync-alt"></i>

    `;
    
    // Show loading state
    const tbody = document.getElementById('mentorshipTableBody');
    tbody.innerHTML = `
        <tr>
            <td colspan="7">
                <div class="loading-state">
                    <i class="fas fa-spinner fa-spin" style="font-size: 2rem; color: var(--gray-400);"></i>
                    <p style="margin-top: 0.5rem; color: var(--gray-500);">Loading applications...</p>
                </div>
            </td>
        </tr>
    `;
    
    // Fetch updated data
    fetch('/admin/mentorships/refresh', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Stay on the mentorship tab after refresh
            showTab('mentorship');
            // Reload the page but stay on mentorship tab
            location.reload();
        } else {
            // Remove loading state
            refreshBtn.classList.remove('loading', 'spinning');
            refreshBtn.innerHTML = `
                <i class="fas fa-sync-alt"></i>
                <span class="btn-text">Refresh</span>
            `;
            showToast('Failed to refresh mentorship applications.', 'error');
            showTab('mentorship');
        }
    })
    .catch(error => {
        console.error('Error refreshing mentorships:', error);
        
        // Remove loading state
        refreshBtn.classList.remove('loading', 'spinning');
        refreshBtn.innerHTML = `
            <i class="fas fa-sync-alt"></i>
            <span class="btn-text">Refresh</span>
        `;
        
        showToast('An error occurred. Please try again.', 'error');
        showTab('mentorship');
    });
}

function viewMentorship(id) {
    window.open('/admin/mentorships/' + id + '/view', '_blank');
}

// ========== WARNING MODAL SYSTEM ==========
// This replaces browser alerts with custom modals

let pendingAction = null;
let pendingId = null;

function confirmAction(action, id) {
    pendingId = id;
    pendingAction = action;
    
    const modal = document.getElementById('warningModal');
    const title = document.getElementById('warningModalTitle');
    const message = document.getElementById('warningModalMessage');
    const icon = document.getElementById('warningModalIcon');
    const confirmBtn = document.getElementById('warningModalConfirm');
    
    const actionMap = {
        'approve': {
            title: 'Approve Application',
            message: 'Are you sure you want to <strong>approve</strong> this mentorship application?<br><small>The application will become active and visible to the alumni.</small>',
            iconType: 'success',
            confirmText: 'Approve',
            confirmClass: 'btn-success'
        },
        'decline': {
            title: 'Decline Application',
            message: 'Are you sure you want to <strong>decline</strong> this mentorship application?<br><small>The applicant will be notified of the decision.</small>',
            iconType: 'warning',
            confirmText: 'Decline',
            confirmClass: 'btn-danger'
        },
        'complete': {
            title: 'Mark as Completed',
            message: 'Are you sure you want to mark this mentorship as <strong>completed</strong>?<br><small>This will move it to the completed section.</small>',
            iconType: 'success',
            confirmText: 'Complete',
            confirmClass: 'btn-success'
        },
        'delete': {
            title: 'Delete Application',
            message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you sure you want to <strong>permanently delete</strong> this mentorship application?<br><small>All associated data will be removed.</small>',
            iconType: 'danger',
            confirmText: 'Delete',
            confirmClass: 'btn-danger'
        }
    };
    
    const config = actionMap[action] || actionMap['delete'];
    
    title.textContent = config.title;
    message.innerHTML = config.message;
    icon.className = 'warning-modal-icon ' + config.iconType;
    const iconElement = icon.querySelector('i');
    if (config.iconType === 'danger') iconElement.className = 'fa-solid fa-triangle-exclamation';
    else if (config.iconType === 'success') iconElement.className = 'fa-solid fa-circle-check';
    else iconElement.className = 'fa-solid fa-triangle-exclamation';
    
    confirmBtn.className = 'btn ' + config.confirmClass;
    confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + config.confirmText;
    
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    confirmBtn.focus();
}

function closeWarningModal() {
    const modal = document.getElementById('warningModal');
    modal.classList.remove('active');
    document.body.style.overflow = '';
    pendingAction = null;
    pendingId = null;
}

function executePendingAction() {
    if (!pendingAction || !pendingId) return;
    
    const actionMap = {
        'approve': 'active',
        'decline': 'declined',
        'complete': 'completed'
    };
    
    if (pendingAction === 'delete') {
        deleteMentorship(pendingId);
    } else if (actionMap[pendingAction]) {
        updateMentorshipStatus(pendingId, actionMap[pendingAction]);
    }
    
    closeWarningModal();
}

// Modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('warningModal');
    const cancelBtn = document.getElementById('warningModalCancel');
    const confirmBtn = document.getElementById('warningModalConfirm');
    
    // Close modal on cancel button click
    cancelBtn.addEventListener('click', closeWarningModal);
    
    // Close modal on overlay click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) closeWarningModal();
    });
    
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeWarningModal();
        }
    });
    
    // Execute action on confirm
    confirmBtn.addEventListener('click', executePendingAction);
});

function updateMentorshipStatus(id, status) {
    const actionMap = {
        'active': 'approve',
        'declined': 'decline',
        'completed': 'mark as completed'
    };
    const action = actionMap[status] || status;
    
    fetch('/admin/mentorships/' + id + '/status', {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ status: status })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        alert('An error occurred. Please try again.');
    });
}

function deleteMentorship(id) {
    // Find the row so we can show the user WHICH app they're deleting
    const row = document.querySelector(`#mentorshipTableBody tr[data-id="${id}"]`);
    const nameEl = row ? row.querySelector('.table-user-name') : null;
    const alumniName = nameEl ? nameEl.textContent.trim() : 'this alumni';

    showConfirmModal({
        title: 'Delete Mentorship Application',
        message: `Are you sure you want to permanently delete the mentorship application from ${alumniName}?\n\n⚠️ This action cannot be undone.`,
        confirmText: 'Delete',
        confirmClass: 'btn-danger',
        icon: 'fa-trash-alt',
        iconColor: '#EF4444',
    }).then(async (confirmed) => {
        if (!confirmed) return;

        // Visual feedback on the row
        if (row) {
            row.style.transition = 'opacity 0.2s ease';
            row.style.opacity = '0.4';
        }

        try {
            const response = await fetch('/admin/mentorships/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            });

            const data = await response.json();

            if (data.success) {
                showToast('Mentorship application deleted successfully', 'success');

                // Smoothly remove the row
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.transform = 'translateX(20px)';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }

                // Refresh counters after a short delay
                setTimeout(() => location.reload(), 800);
            } else {
                if (row) row.style.opacity = '1';
                showErrorModal(data.message || 'Failed to delete the application.');
            }
        } catch (error) {
            if (row) row.style.opacity = '1';
            showErrorModal('An error occurred while deleting. Please try again.');
            console.error('Delete mentorship error:', error);
        }
    });
}

// ========== TOOLTIP POSITIONING FIX ==========
// This ensures tooltips are always visible even at the top of the table

document.addEventListener('DOMContentLoaded', function() {
    // Add hover event listeners to all action buttons
    document.querySelectorAll('#tab-mentorship .btn-action').forEach(button => {
        // Store the original title
        const originalTitle = button.getAttribute('data-title') || button.getAttribute('title');
        
        button.addEventListener('mouseenter', function(e) {
            // Remove title attribute to prevent browser default tooltip
            this.removeAttribute('title');
            
            // Use the stored original title
            const tooltip = originalTitle;
            if (!tooltip) return;
            
            // Remove existing tooltip
            const existing = document.querySelector('.custom-tooltip');
            if (existing) existing.remove();
            
            // Create custom tooltip
            const tip = document.createElement('div');
            tip.className = 'custom-tooltip';
            tip.textContent = tooltip;
            tip.style.cssText = `
                position: fixed;
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 0.35rem 0.75rem;
                border-radius: 6px;
                font-size: 0.7rem;
                font-weight: 500;
                white-space: nowrap;
                z-index: 999999;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                pointer-events: none;
                opacity: 0;
                transition: opacity 0.2s ease;
            `;
            
            document.body.appendChild(tip);
            
            // Position the tooltip
            const rect = this.getBoundingClientRect();
            let top = rect.top - tip.offsetHeight - 10;
            let left = rect.left + (rect.width / 2) - (tip.offsetWidth / 2);
            
            // If tooltip would go above viewport, position below the button
            if (top < 10) {
                top = rect.bottom + 10;
            }
            
            // Ensure tooltip doesn't go off screen horizontally
            if (left < 10) left = 10;
            if (left + tip.offsetWidth > window.innerWidth - 10) {
                left = window.innerWidth - tip.offsetWidth - 10;
            }
            
            tip.style.top = top + 'px';
            tip.style.left = left + 'px';
            
            // Show tooltip
            requestAnimationFrame(() => {
                tip.style.opacity = '1';
            });
            
            // Store reference to remove on mouseleave
            this._tooltip = tip;
        });
        
        button.addEventListener('mouseleave', function() {
            // Restore the title attribute
            if (originalTitle) {
                this.setAttribute('title', originalTitle);
            }
            
            // Remove custom tooltip
            const tip = this._tooltip;
            if (tip) {
                tip.style.opacity = '0';
                setTimeout(() => {
                    if (tip.parentNode) tip.remove();
                }, 200);
                this._tooltip = null;
            }
        });
    });
});

// ========== TOAST NOTIFICATION SYSTEM ==========
function showToast(message, type = 'info') {
    // Remove existing toasts
    const existing = document.querySelector('.custom-toast');
    if (existing) existing.remove();
    
    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    
    const colors = {
        'warning': { bg: '#FEF3C7', text: '#92400E', border: '#F59E0B', icon: 'fa-exclamation-triangle' },
        'info': { bg: '#DBEAFE', text: '#1E40AF', border: '#3B82F6', icon: 'fa-info-circle' },
        'success': { bg: '#D1FAE5', text: '#065F46', border: '#10B981', icon: 'fa-check-circle' },
        'error': { bg: '#FEE2E2', text: '#991B1B', border: '#EF4444', icon: 'fa-times-circle' }
    };
    
    const color = colors[type] || colors['info'];
    
    toast.style.cssText = `
        background: ${color.bg};
        color: ${color.text};
        border-left: 4px solid ${color.border};
    `;
    
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas ${color.icon}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fas fa-times"></i>
        </button>
    `;
    
    document.body.appendChild(toast);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.style.opacity = '0';
            toast.style.transform = 'translateY(20px)';
            setTimeout(() => {
                if (toast.parentNode) toast.remove();
            }, 300);
        }
    }, 5000);
}

// Add this to your JavaScript
function setFilter(filter, element) {
    // Update active state on pills
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.classList.remove('active');
    });
    element.classList.add('active');
    
    // Update the select to match (if you want to keep both)
    const select = document.getElementById('mentorshipFilter');
    if (select) {
        select.value = filter;
    }
    
    // Apply the filter
    const rows = document.querySelectorAll('#mentorshipTableBody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        if (filter === 'all' || row.dataset.status === filter) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Update visible count
    document.getElementById('visibleCount').textContent = visibleCount;
    
    // Update badge count
    const badge = document.querySelector('.badge-count');
    if (badge) {
        badge.textContent = visibleCount;
    }
}

// Initialize the filter pills on load
document.addEventListener('DOMContentLoaded', function() {
    // Set initial active pill
    const activePill = document.querySelector('.filter-pill.active');
    if (!activePill) {
        const firstPill = document.querySelector('.filter-pill');
        if (firstPill) {
            firstPill.classList.add('active');
        }
    }
});

</script>

<!-- MENTORSHIP TAB STYLES -->
<style>
/* ============================================
   MENTORSHIP TAB STYLES
   ============================================ */

#tab-mentorship .section-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

#tab-mentorship .header-title-section {
    flex: 1;
}

#tab-mentorship .section-subtitle {
    color: var(--gray-500);
    font-size: 0.875rem;
    margin-top: 0.25rem;
}

#tab-mentorship .section-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Stats Grid */
#tab-mentorship .stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 1rem;
    margin-bottom: 1.5rem;
}

#tab-mentorship .stat-card {
    background: white;
    border-radius: 12px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.06);
    border: 1px solid var(--gray-100);
    display: flex;
    align-items: center;
    gap: 1rem;
    transition: all 0.2s ease;
}

#tab-mentorship .stat-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-2px);
}

#tab-mentorship .stat-icon-wrapper {
    flex-shrink: 0;
}

#tab-mentorship .stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
}

#tab-mentorship .stat-info {
    display: flex;
    flex-direction: column;
}

#tab-mentorship .stat-value {
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gray-800);
    line-height: 1.2;
}

#tab-mentorship .stat-label {
    font-size: 0.8rem;
    color: var(--gray-500);
    font-weight: 500;
}

/* Card Header */
#tab-mentorship .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 1rem;
    padding: 1rem 1.5rem;
    background: var(--gray-50);
    border-radius: 12px 12px 0 0;
    border-bottom: 1px solid var(--gray-200);
}

#tab-mentorship .card-header-left {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

#tab-mentorship .card-title {
    font-size: 1rem;
    font-weight: 600;
    color: var(--gray-700);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

#tab-mentorship .badge-count {
    background: var(--gray-200);
    padding: 0.15rem 0.6rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 600;
    color: var(--gray-600);
}

#tab-mentorship .card-actions {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

#tab-mentorship .filter-group select {
    padding: 0.4rem 1rem;
    border-radius: 8px;
    border: 1px solid var(--gray-200);
    font-size: 0.8rem;
    background: white;
    color: var(--gray-700);
    cursor: pointer;
    outline: none;
    transition: border-color 0.2s;
}

#tab-mentorship .filter-group select:focus {
    border-color: #32418C;
}

/* Compact refresh button style */
#tab-mentorship .btn-refresh {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 1rem;
    border-radius: 9999px;
    border: 2px solid var(--gray-200);
    background: white;
    font-family: 'Poppins', sans-serif;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--gray-600);
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    white-space: nowrap;
}

#tab-mentorship .btn-refresh:hover {
    border-color: #32418C;
    color: #32418C;
    transform: translateY(-1px);
    box-shadow: 0 4px 16px rgba(50, 65, 140, 0.15);
    background: #F8F9FF;
}

#tab-mentorship .btn-refresh:active {
    transform: scale(0.96);
}

#tab-mentorship .btn-refresh i {
    font-size: 0.8rem;
    transition: transform 0.6s ease;
    color: var(--gray-500);
}

#tab-mentorship .btn-refresh:hover i {
    color: #32418C;
}

#tab-mentorship .btn-refresh.spinning i {
    animation: spin 1s ease-in-out;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Refresh button loading state */
#tab-mentorship .btn-refresh.loading {
    pointer-events: none;
    opacity: 0.7;
    border-color: #32418C;
}

#tab-mentorship .btn-refresh.loading i {
    animation: spin 1s linear infinite;
}

#tab-mentorship .btn-refresh .btn-text {
    transition: opacity 0.3s ease;
}

#tab-mentorship .btn-refresh.loading .btn-text {
    opacity: 0;
}

#tab-mentorship .btn-refresh .loading-text {
    display: none;
    position: absolute;
    left: 50%;
    top: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.75rem;
    font-weight: 500;
    color: #32418C;
}

#tab-mentorship .btn-refresh.loading .loading-text {
    display: inline;
}

/* Table Styles - FIXED for tooltips */
#tab-mentorship .table-wrapper {
    overflow-x: auto;
    overflow-y: visible;
    padding: 0.5rem;
    position: relative;
}

/* Make the table container not clip tooltips */
#tab-mentorship .dash-card {
    overflow: visible !important;
    position: relative;
}

#tab-mentorship .mentorship-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.85rem;
}

/* Important: Make table rows and cells not clip overflow */
#tab-mentorship .mentorship-table tbody tr {
    position: relative;
}

#tab-mentorship .mentorship-table tbody td {
    padding: 0.75rem 1rem;
    border-bottom: 1px solid var(--gray-100);
    vertical-align: middle;
    position: relative;
    overflow: visible !important;
}

#tab-mentorship .mentorship-table thead th {
    text-align: left;
    padding: 0.75rem 1rem;
    background: white;
    font-weight: 600;
    color: var(--gray-600);
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid var(--gray-200);
    position: sticky;
    top: 0;
    z-index: 10;
}

#tab-mentorship .mentorship-table tbody tr:hover {
    background: var(--gray-50);
}

#tab-mentorship .mentorship-table tbody tr:last-child td {
    border-bottom: none;
}

/* User Info */
#tab-mentorship .table-user-info {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

#tab-mentorship .table-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    object-fit: cover;
    flex-shrink: 0;
}

#tab-mentorship .table-avatar-placeholder {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--gray-200);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    color: var(--gray-600);
    font-size: 0.8rem;
    flex-shrink: 0;
}

#tab-mentorship .table-user-details {
    display: flex;
    flex-direction: column;
}

#tab-mentorship .table-user-name {
    font-weight: 600;
    color: var(--gray-800);
}

#tab-mentorship .table-user-email {
    font-size: 0.75rem;
    color: var(--gray-500);
}

/* Event Type Badge */
#tab-mentorship .event-type-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.3rem 0.7rem;
    background: var(--gray-100);
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--gray-700);
}

/* Event Details */
#tab-mentorship .event-details-cell {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

#tab-mentorship .event-title {
    font-weight: 600;
    color: var(--gray-800);
}

#tab-mentorship .event-description {
    font-size: 0.75rem;
    color: var(--gray-500);
    line-height: 1.3;
}

#tab-mentorship .event-speakers {
    font-size: 0.7rem;
    color: var(--gray-500);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

/* Audience Badge */
#tab-mentorship .audience-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.2rem 0.6rem;
    background: #EEF2FF;
    color: #4338CA;
    border-radius: 6px;
    font-size: 0.75rem;
    font-weight: 500;
}

#tab-mentorship .capacity-info {
    font-size: 0.7rem;
    color: var(--gray-500);
    margin-top: 0.2rem;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

/* Date Cell */
#tab-mentorship .date-cell {
    display: flex;
    flex-direction: column;
    gap: 0.2rem;
}

#tab-mentorship .date-display {
    font-weight: 500;
    color: var(--gray-800);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

#tab-mentorship .time-display {
    font-size: 0.7rem;
    color: var(--gray-500);
    display: flex;
    align-items: center;
    gap: 0.3rem;
}

#tab-mentorship .format-badge {
    font-size: 0.65rem;
    color: var(--gray-500);
    background: var(--gray-100);
    padding: 0.1rem 0.5rem;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    width: fit-content;
}

/* Status Badge */
#tab-mentorship .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

#tab-mentorship .status-pending {
    background: #FEF3C7;
    color: #D97706;
}

#tab-mentorship .status-active {
    background: #D1FAE5;
    color: #065F46;
}

#tab-mentorship .status-completed {
    background: #F3F4F6;
    color: #6B7280;
}

#tab-mentorship .status-declined {
    background: #FEE2E2;
    color: #991B1B;
}

#tab-mentorship .draft-badge {
    font-size: 0.6rem;
    color: var(--gray-500);
    background: var(--gray-100);
    padding: 0.1rem 0.4rem;
    border-radius: 4px;
    margin-left: 0.25rem;
    display: inline-flex;
    align-items: center;
    gap: 0.2rem;
}

/* Table Actions - FIXED TOOLTIPS */
#tab-mentorship .table-actions {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
    justify-content: center;
    position: relative;
    overflow: visible !important;
}

#tab-mentorship .btn-action {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s ease;
    font-size: 0.85rem;
    position: relative;
    overflow: visible !important;
    flex-shrink: 0;
    z-index: 1;
}

/* Button specific styles */
#tab-mentorship .btn-action.btn-view {
    background: #EEF2FF;
    color: #4338CA;
}

#tab-mentorship .btn-action.btn-view:hover {
    background: #C7D2FE;
    transform: scale(1.05);
}

#tab-mentorship .btn-action.btn-approve {
    background: #D1FAE5;
    color: #065F46;
}

#tab-mentorship .btn-action.btn-approve:hover {
    background: #A7F3D0;
    transform: scale(1.05);
}

#tab-mentorship .btn-action.btn-decline {
    background: #FEE2E2;
    color: #991B1B;
}

#tab-mentorship .btn-action.btn-decline:hover {
    background: #FECACA;
    transform: scale(1.05);
}

#tab-mentorship .btn-action.btn-complete {
    background: #DBEAFE;
    color: #1E40AF;
}

#tab-mentorship .btn-action.btn-complete:hover {
    background: #BFDBFE;
    transform: scale(1.05);
}

#tab-mentorship .btn-action.btn-file {
    background: #E0E7FF;
    color: #4338CA;
}

#tab-mentorship .btn-action.btn-file:hover {
    background: #C7D2FE;
    transform: scale(1.05);
}

#tab-mentorship .btn-action.btn-delete {
    background: #FEE2E2;
    color: #991B1B;
}

#tab-mentorship .btn-action.btn-delete:hover {
    background: #FECACA;
    transform: scale(1.05);
}

/* Table Footer */
#tab-mentorship .table-footer {
    padding: 0.75rem 1.5rem;
    background: var(--gray-50);
    border-top: 1px solid var(--gray-200);
    font-size: 0.8rem;
    color: var(--gray-500);
    border-radius: 0 0 12px 12px;
    display: flex;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 0.5rem;
}

#tab-mentorship .footer-left,
#tab-mentorship .footer-right {
    display: flex;
    align-items: center;
    gap: 0.4rem;
}

/* Empty & Loading States */
#tab-mentorship .empty-state,
#tab-mentorship .loading-state {
    text-align: center;
    padding: 3rem 1rem;
}

#tab-mentorship .empty-state h4 {
    font-size: 1rem;
    font-weight: 600;
}

/* ============================================
   WARNING MODAL STYLES
   ============================================ */

.warning-modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(4px);
    z-index: 99999;
    align-items: center;
    justify-content: center;
    animation: fadeIn 0.2s ease;
}

.warning-modal-overlay.active {
    display: flex;
}

.warning-modal {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    max-width: 440px;
    width: 90%;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

.warning-modal-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 1rem;
    font-size: 2rem;
}

.warning-modal-icon.warning {
    background: #FEF3C7;
    color: #D97706;
}

.warning-modal-icon.danger {
    background: #FEE2E2;
    color: #EF4444;
}

.warning-modal-icon.success {
    background: #D1FAE5;
    color: #065F46;
}

.warning-modal-content {
    text-align: center;
    margin-bottom: 1.5rem;
}

.warning-modal-title {
    font-size: 1.25rem;
    font-weight: 600;
    color: var(--gray-800);
    margin-bottom: 0.5rem;
}

.warning-modal-message {
    color: var(--gray-600);
    font-size: 0.95rem;
    line-height: 1.6;
}

.warning-modal-message strong {
    color: var(--gray-800);
}

.warning-modal-message small {
    color: var(--gray-500);
    font-size: 0.85rem;
}

.warning-modal-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: center;
}

.warning-modal-actions .btn {
    padding: 0.6rem 1.5rem;
    border-radius: 8px;
    font-weight: 500;
    font-size: 0.9rem;
    border: none;
    cursor: pointer;
    transition: all 0.2s ease;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

.warning-modal-actions .btn-secondary {
    background: var(--gray-100);
    color: var(--gray-700);
}

.warning-modal-actions .btn-secondary:hover {
    background: var(--gray-200);
}

.warning-modal-actions .btn-success {
    background: #10B981;
    color: white;
}

.warning-modal-actions .btn-success:hover {
    background: #059669;
}

.warning-modal-actions .btn-danger {
    background: #EF4444;
    color: white;
}

.warning-modal-actions .btn-danger:hover {
    background: #DC2626;
}

.warning-modal-actions .btn-warning {
    background: #F59E0B;
    color: white;
}

.warning-modal-actions .btn-warning:hover {
    background: #D97706;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Responsive */
@media (max-width: 1024px) {
    #tab-mentorship .mentorship-table thead th {
        font-size: 0.65rem;
        padding: 0.5rem 0.75rem;
    }
    
    #tab-mentorship .mentorship-table tbody td {
        padding: 0.5rem 0.75rem;
    }
}

@media (max-width: 768px) {
    #tab-mentorship .section-header {
        flex-direction: column;
    }
    
    #tab-mentorship .section-actions {
        width: 100%;
        justify-content: flex-start;
    }
    
    #tab-mentorship .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    }
    
    #tab-mentorship .stat-card {
        padding: 0.75rem;
    }
    
    #tab-mentorship .stat-value {
        font-size: 1.1rem;
    }
    
    #tab-mentorship .card-header {
        flex-direction: column;
        align-items: stretch;
        gap: 0.75rem;
    }
    
    #tab-mentorship .card-actions {
        flex-wrap: wrap;
    }
    
    #tab-mentorship .mentorship-table {
        font-size: 0.75rem;
    }
    
    #tab-mentorship .mentorship-table th,
    #tab-mentorship .mentorship-table td {
        padding: 0.5rem;
    }
    
    #tab-mentorship .table-actions {
        gap: 0.3rem;
    }
    
    #tab-mentorship .btn-action {
        width: 30px;
        height: 30px;
        font-size: 0.7rem;
    }

    .warning-modal {
        padding: 1.5rem;
        max-width: 380px;
    }

    .warning-modal-actions {
        flex-direction: column;
    }

    .warning-modal-actions .btn {
        justify-content: center;
        padding: 0.7rem;
    }
}

@media (max-width: 480px) {
    #tab-mentorship .stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 0.5rem;
    }
    
    #tab-mentorship .stat-card {
        flex-direction: column;
        text-align: center;
        padding: 0.75rem;
    }
    
    #tab-mentorship .stat-icon {
        width: 40px;
        height: 40px;
        font-size: 1rem;
    }
    
    #tab-mentorship .table-footer {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .warning-modal {
        padding: 1.25rem;
        max-width: 340px;
    }
}

/* ============================================
   DISABLED BUTTON STYLES
   ============================================ */

#tab-mentorship .btn-action.btn-file.disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}

#tab-mentorship .btn-action.btn-file.disabled:hover {
    transform: none;
    background: #E0E7FF;
}

/* If you want to allow clicks but show a toast (optional) */
#tab-mentorship .btn-action.btn-file.disabled {
    opacity: 0.5;
    cursor: pointer;
}

#tab-mentorship .btn-action.btn-file.disabled:hover {
    background: #E0E7FF;
    transform: scale(1.02);
}

/* ============================================
   TOAST NOTIFICATION STYLES
   ============================================ */

.custom-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 1rem 1.25rem;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.15);
    z-index: 999999;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    max-width: 420px;
    min-width: 280px;
    font-size: 0.9rem;
    font-weight: 500;
    font-family: 'Poppins', sans-serif;
    transition: opacity 0.3s ease, transform 0.3s ease;
    animation: toastSlideUp 0.3s ease;
}

.custom-toast .toast-content {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.custom-toast .toast-close {
    background: none;
    border: none;
    cursor: pointer;
    color: inherit;
    opacity: 0.5;
    padding: 0.25rem;
    font-size: 1rem;
    transition: opacity 0.2s;
    flex-shrink: 0;
}

.custom-toast .toast-close:hover {
    opacity: 1;
}

@keyframes toastSlideUp {
    from {
        transform: translateY(20px);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Message Button */
#tab-mentorship .btn-action.btn-message {
    background: #DBEAFE;
    color: #1E40AF;
}

#tab-mentorship .btn-action.btn-message:hover {
    background: #BFDBFE;
    transform: scale(1.05);
}

/* ============================================
   PILL-STYLE FILTER
   ============================================ */

#tab-mentorship .filter-pills {
    display: flex;
    gap: 0.5rem;
    flex-wrap: wrap;
}

#tab-mentorship .filter-pill {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.45rem 1rem;
    border-radius: 9999px;
    border: 2px solid var(--gray-200);
    background: white;
    font-family: 'Poppins', sans-serif;
    font-size: 0.75rem;
    font-weight: 500;
    color: var(--gray-600);
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
    white-space: nowrap;
}

#tab-mentorship .filter-pill:hover {
    border-color: var(--gray-300);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

#tab-mentorship .filter-pill i {
    font-size: 0.75rem;
    opacity: 0.7;
}

#tab-mentorship .filter-pill .filter-count {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: var(--gray-100);
    padding: 0 0.5rem;
    border-radius: 9999px;
    font-size: 0.6rem;
    font-weight: 600;
    color: var(--gray-500);
    min-width: 18px;
    height: 18px;
    transition: all 0.3s ease;
}

#tab-mentorship .filter-pill.active {
    border-color: #32418C;
    background: #32418C;
    color: white;
    box-shadow: 0 4px 16px rgba(50, 65, 140, 0.25);
}

#tab-mentorship .filter-pill.active .filter-count {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

#tab-mentorship .filter-pill.active i {
    opacity: 1;
}

/* Individual pill colors when active */
#tab-mentorship .filter-pill[data-filter="pending"].active {
    border-color: #F59E0B;
    background: #F59E0B;
}

#tab-mentorship .filter-pill[data-filter="active"].active {
    border-color: #10B981;
    background: #10B981;
}

#tab-mentorship .filter-pill[data-filter="completed"].active {
    border-color: #6B7280;
    background: #6B7280;
}

#tab-mentorship .filter-pill[data-filter="declined"].active {
    border-color: #EF4444;
    background: #EF4444;
}

</style>

