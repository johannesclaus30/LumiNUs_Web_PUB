<!-- ============================================ -->
<!-- TAB 1: MODERATION QUEUE -->
<!-- ============================================ -->
<div id="tab-moderation" class="tab-content active">
    <section class="dashboard-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-flag"></i>
                Moderation Queue
            </h2>
            <div class="section-actions">
                <span class="report-count-badge">
                    <i class="fa-solid fa-circle-exclamation"></i>
                    {{ $totalReports }} pending report{{ $totalReports !== 1 ? 's' : '' }}
                </span>
            </div>
        </div>

        <!-- Moderation Stats -->
        <div class="moderation-stats-grid">
            <div class="mod-stat-card">
                <div class="mod-stat-icon reported-posts">
                    <i class="fa-solid fa-file-lines"></i>
                </div>
                <div class="mod-stat-info">
                    <span class="mod-stat-value">{{ $reportedPosts->count() }}</span>
                    <span class="mod-stat-label">Reported Posts</span>
                </div>
            </div>
            <div class="mod-stat-card">
                <div class="mod-stat-icon reported-comments">
                    <i class="fa-solid fa-comment"></i>
                </div>
                <div class="mod-stat-info">
                    <span class="mod-stat-value">{{ $reportedComments->count() }}</span>
                    <span class="mod-stat-label">Reported Comments</span>
                </div>
            </div>
            <div class="mod-stat-card">
                <div class="mod-stat-icon total-reports">
                    <i class="fa-solid fa-flag"></i>
                </div>
                <div class="mod-stat-info">
                    <span class="mod-stat-value">{{ $totalReports }}</span>
                    <span class="mod-stat-label">Total Reports</span>
                </div>
            </div>
        </div>

        <!-- Frequent Violators Section -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-triangle-exclamation" style="color: #D97706;"></i>
                    Frequent Violators
                    <span class="badge-count" style="background: #FEF3C7; color: #D97706;">{{ $frequentViolators->count() }}</span>
                </h3>
                <div class="card-actions">
                    <button class="btn-expand" onclick="openModal('violators')">
                        <i class="fa-solid fa-expand"></i>
                        <span>Expand All</span>
                    </button>
                </div>
            </div>
            <div class="list-wrapper">
                @forelse($frequentViolators as $violator)
                <div class="violator-item" data-id="{{ $violator->id }}"  data-status="{{ $violator->account_status }}" onclick="openModal('violator', {{ $violator->id }})" style="cursor: pointer;">
                    <div class="violator-avatar">
                        @if($violator->alumni_photo)
                            <img src="{{ $violator->alumni_photo }}" alt="{{ $violator->first_name }}" class="violator-photo">
                        @else
                            <div class="violator-initials">{{ strtoupper(substr($violator->first_name, 0, 1) . substr($violator->last_name, 0, 1)) }}</div>
                        @endif
                    </div>
                    <div class="violator-info">
                        <div class="violator-name">
                            {{ $violator->first_name }} {{ $violator->last_name }}
                            <span class="violator-badge" style="background: {{ $violator->total_reports >= 5 ? '#EF4444' : ($violator->total_reports >= 3 ? '#F59E0B' : '#3B82F6') }}; color: white;">
                                {{ $violator->total_reports }} report{{ $violator->total_reports > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <div class="violator-details">
                            <span class="violator-meta"><i class="fa-regular fa-envelope"></i> {{ $violator->email }}</span>
                            <span class="violator-meta"><i class="fa-regular fa-calendar"></i> Joined {{ optional($violator->created_at)->format('M Y') ?? 'N/A' }}</span>
                            <span class="violator-meta"><i class="fa-solid fa-flag"></i> {{ $violator->post_reports }} post{{ $violator->post_reports > 1 ? 's' : '' }}, {{ $violator->comment_reports }} comment{{ $violator->comment_reports > 1 ? 's' : '' }}</span>
                        </div>
                        @if($violator->report_reasons)
                        <div class="violator-reasons">
                            <i class="fa-solid fa-comment"></i>
                            <span>Report reasons: {{ Str::limit($violator->report_reasons, 100) }}</span>
                        </div>
                        @endif
                    </div>
                    <div class="violator-actions">
                        <button class="btn-action btn-expand-item" onclick="event.stopPropagation(); openModal('violator', {{ $violator->id }})" title="View Details">
                            <i class="fa-solid fa-expand"></i>
                        </button>
                        <a href="{{ route('admin.alumni.show', $violator->id) }}" class="btn-action btn-view" title="View Profile" onclick="event.stopPropagation();">
                            <i class="fa-solid fa-eye"></i>
                        </a>
                        @if($violator->account_status == 1)
                        <button class="btn-action btn-restrict" onclick="event.stopPropagation(); restrictUser({{ $violator->id }})" title="Restrict User">
                            <i class="fa-solid fa-user-slash"></i>
                        </button>
                        @else
                        <button class="btn-action btn-unrestrict" onclick="event.stopPropagation(); restrictUser({{ $violator->id }})" title="Unrestrict User" style="background: #D1FAE5; color: #065F46;">
                            <i class="fa-solid fa-user-check"></i>
                        </button>
                        @endif
                        <a href="/admin/messages?chat={{ $violator->id }}" class="btn-action btn-message" title="Send Message" onclick="event.stopPropagation();">
                            <i class="fa-solid fa-comment-dots"></i>
                        </a>
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fa-solid fa-check-circle" style="color: #10B981; font-size: 2.5rem;"></i>
                    <p style="font-weight: 500; color: var(--gray-600);">No frequent violators!</p>
                    <p style="font-size: 0.875rem; color: var(--gray-400);">All alumni are following community standards.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Reported Posts Section -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-file-lines"></i>
                    Reported Posts
                    <span class="badge-count">{{ $reportedPosts->count() }}</span>
                </h3>
                <div class="card-actions">
                    <button class="btn-expand" onclick="openModal('posts')">
                        <i class="fa-solid fa-expand"></i>
                        <span>Expand All</span>
                    </button>
                </div>
            </div>
            <div class="list-wrapper">
                @forelse($reportedPosts as $post)
                <div class="moderation-item priority-{{ $post->report_count >= 3 ? 'high' : ($post->report_count >= 2 ? 'medium' : 'low') }} {{ $post->is_hidden ? 'item-hidden' : '' }}" 
                    onclick="openModal('post', {{ $post->id }})" 
                    style="cursor: pointer;"
                    data-post-id="{{ $post->id }}"
                    data-is-hidden="{{ $post->is_hidden ? '1' : '0' }}"
                    data-moderation-status="{{ $post->moderation_status }}">
                    <div class="mod-item-left">
                        <div class="priority-indicator">
                            <span class="priority-dot priority-{{ $post->report_count >= 3 ? 'high' : ($post->report_count >= 2 ? 'medium' : 'low') }}"></span>
                            <span class="report-count-badge-small">{{ $post->report_count }} report{{ $post->report_count > 1 ? 's' : '' }}</span>
                            @if($post->is_hidden)
                                <span class="status-badge status-hidden" title="This post is hidden from the app">
                                    <i class="fa-solid fa-eye-slash"></i> Hidden
                                </span>
                            @elseif($post->moderation_status === 'approved')
                                <span class="status-badge status-approved" title="This post is visible">
                                    <i class="fa-solid fa-check"></i> Visible
                                </span>
                            @endif
                        </div>
                        <div class="mod-item-content">
                            <p class="mod-item-text">{{ Str::limit($post->caption ?? 'No caption', 100) }}</p>
                            <div class="mod-item-meta">
                                <span class="mod-meta-author"><i class="fa-solid fa-user"></i> {{ $post->alumni ? $post->alumni->first_name . ' ' . $post->alumni->last_name : 'Unknown' }}</span>
                                <span class="mod-meta-date"><i class="fa-regular fa-clock"></i> {{ $post->created_at ? $post->created_at->format('M d, Y') : 'Date not available' }}</span>
                                @if($post->report_reasons)
                                <span class="mod-meta-reasons" title="{{ $post->report_reasons }}"><i class="fa-solid fa-comment"></i> {{ Str::limit($post->report_reasons, 40) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mod-item-actions">
                        <button class="btn-action btn-expand-item" onclick="event.stopPropagation(); openModal('post', {{ $post->id }})" title="View Details">
                            <i class="fa-solid fa-expand"></i>
                        </button>
                        <button class="btn-action view-post" onclick="event.stopPropagation(); viewPost({{ $post->id }})" title="View Post">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        
                        <button class="btn-action approve-post" onclick="event.stopPropagation(); moderatePost({{ $post->id }}, 'approve', this)" title="Approve (Dismiss Reports)">
                            <i class="fa-solid fa-check"></i>
                        </button>

                        @if($post->is_hidden)
                            <button class="btn-action unhide-post" onclick="event.stopPropagation(); moderatePost({{ $post->id }}, 'unhide', this)" title="Unhide Post">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        @else
                            <button class="btn-action hide-post" onclick="event.stopPropagation(); moderatePost({{ $post->id }}, 'hide', this)" title="Hide Post">
                                <i class="fa-solid fa-eye-slash"></i>
                            </button>
                        @endif

                        <button class="btn-action delete-post" onclick="event.stopPropagation(); moderatePost({{ $post->id }}, 'delete', this)" title="Delete Post">
                            <i class="fa-solid fa-trash"></i>
                        </button>

                        {{-- In reported posts --}}
                        @if(($post->alumni->account_status ?? 1) == 1)
                            <button class="btn-action restrict-user" 
                                    onclick="event.stopPropagation(); restrictUser({{ $post->alumni_id }}, 'restrict')" 
                                    title="Restrict User">
                                <i class="fa-solid fa-user-slash"></i>
                            </button>
                        @else
                            <button class="btn-action unrestrict-user" 
                                    onclick="event.stopPropagation(); restrictUser({{ $post->alumni_id }}, 'unrestrict')" 
                                    title="Unrestrict User">
                                <i class="fa-solid fa-user-check"></i>
                            </button>
                        @endif
                        
                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fa-solid fa-check-circle" style="color: #10B981;"></i>
                    <p>No reported posts! All posts are clean.</p>
                </div>
                @endforelse
            </div>
        </div>

        <!-- Reported Comments Section -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-comments"></i>
                    Reported Comments
                    <span class="badge-count">{{ $reportedComments->count() }}</span>
                </h3>
                <div class="card-actions">
                    <button class="btn-expand" onclick="openModal('comments')">
                        <i class="fa-solid fa-expand"></i>
                        <span>Expand All</span>
                    </button>
                </div>
            </div>
            <div class="list-wrapper">
                @forelse($reportedComments as $comment)
                <div class="moderation-item priority-{{ $comment->report_count >= 3 ? 'high' : ($comment->report_count >= 2 ? 'medium' : 'low') }}" onclick="openModal('comment', {{ $comment->id }})" style="cursor: pointer;">
                    <div class="mod-item-left">
                        <div class="priority-indicator">
                            <span class="priority-dot priority-{{ $comment->report_count >= 3 ? 'high' : ($comment->report_count >= 2 ? 'medium' : 'low') }}"></span>
                            <span class="report-count-badge-small">{{ $comment->report_count }} report{{ $comment->report_count > 1 ? 's' : '' }}</span>
                        </div>
                        <div class="mod-item-content">
                            <p class="mod-item-text">"{{ Str::limit($comment->comment ?? 'Comment deleted', 100) }}"</p>
                            <div class="mod-item-meta">
                                <span class="mod-meta-author"><i class="fa-solid fa-user"></i> {{ $comment->alumni ? $comment->alumni->first_name . ' ' . $comment->alumni->last_name : 'Unknown User' }}</span>
                                <span class="mod-meta-date"><i class="fa-regular fa-clock"></i> {{ $comment->created_at ? $comment->created_at->format('M d, Y') : 'Date not available' }}</span>
                                @if($comment->post_caption)
                                <span class="mod-meta-post"><i class="fa-solid fa-file-lines"></i> {{ Str::limit($comment->post_caption, 30) }}</span>
                                @endif
                                @if($comment->report_reasons)
                                <span class="mod-meta-reasons" title="{{ $comment->report_reasons }}"><i class="fa-solid fa-comment"></i> {{ Str::limit($comment->report_reasons, 40) }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="mod-item-actions">
                        <button class="btn-action btn-expand-item" onclick="event.stopPropagation(); openModal('comment', {{ $comment->id }})" title="View Details">
                            <i class="fa-solid fa-expand"></i>
                        </button>
                        <button class="btn-action view-comment" onclick="event.stopPropagation(); viewComment({{ $comment->id }})" title="View Comment">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                        
                        <button class="btn-action approve-comment" onclick="event.stopPropagation(); moderateComment({{ $comment->id }}, 'approve', this)" title="Approve (Dismiss Reports)">
                            <i class="fa-solid fa-check"></i>
                        </button>

                        <button class="btn-action delete-comment" onclick="event.stopPropagation(); moderateComment({{ $comment->id }}, 'delete', this)" title="Delete Comment">
                            <i class="fa-solid fa-trash"></i>
                        </button>

                        @if(($comment->alumni->account_status ?? 1) == 1)
                            <button class="btn-action restrict-user" 
                                    onclick="event.stopPropagation(); restrictUser({{ $comment->alumni_id }}, 'restrict')" 
                                    title="Restrict User">
                                <i class="fa-solid fa-user-slash"></i>
                            </button>
                        @else
                            <button class="btn-action unrestrict-user" 
                                    onclick="event.stopPropagation(); restrictUser({{ $comment->alumni_id }}, 'unrestrict')" 
                                    title="Unrestrict User"
                                    style="background:#D1FAE5; color:#065F46; border-color:#6EE7B7;">
                                <i class="fa-solid fa-user-check"></i>
                            </button>
                        @endif

                    </div>
                </div>
                @empty
                <div class="empty-state">
                    <i class="fa-solid fa-check-circle" style="color: #10B981;"></i>
                    <p>No reported comments! All comments are clean.</p>
                </div>
                @endforelse
            </div>
        </div>
    </section>
</div>

<!-- MODERATION TAB JAVASCRIPT -->
<script>

async function moderatePost(postId, action, btn) {
    const config = {
        'approve': {
            verb: 'approve and dismiss all reports for',
            title: 'Approve Post',
            confirmText: 'Approve',
            confirmClass: 'btn-success',
            icon: 'fa-circle-check',
            iconColor: '#10B981',
            iconBg: '#D1FAE5',
        },
        'hide': {
            verb: 'hide from the app',
            title: 'Hide Post',
            confirmText: 'Hide',
            confirmClass: 'btn-warning',
            icon: 'fa-eye-slash',
            iconColor: '#F59E0B',
            iconBg: '#FEF3C7',
        },
        'unhide': {
            verb: 'unhide and make visible again',
            title: 'Unhide Post',
            confirmText: 'Unhide',
            confirmClass: 'btn-success',
            icon: 'fa-eye',
            iconColor: '#10B981',
            iconBg: '#D1FAE5',
        },
        'delete': {
            verb: 'permanently delete',
            title: 'Delete Post',
            confirmText: 'Delete',
            confirmClass: 'btn-danger',
            icon: 'fa-triangle-exclamation',
            iconColor: '#EF4444',
            iconBg: '#FEE2E2',
        },
    };

    const cfg = config[action] || {
        verb: action,
        title: 'Confirm Action',
        confirmText: 'Confirm',
        confirmClass: 'btn-primary',
        icon: 'fa-circle-question',
        iconColor: '#32418C',
        iconBg: '#E8EBF7',
    };

    let message = `Are you sure you want to ${cfg.verb} this post?`;
    if (action === 'delete') {
        message += '\n\nThis action cannot be undone.';
    }

    const confirmed = await showConfirmModal({
        title: cfg.title,
        message,
        confirmText: cfg.confirmText,
        cancelText: 'Cancel',
        confirmClass: cfg.confirmClass,
        icon: cfg.icon,
        iconColor: cfg.iconColor,
        iconBg: cfg.iconBg,      // 👈 ADD THIS
    });

    if (!confirmed) return;

    if (btn) btn.classList.add('btn-loading');

    try {
        const response = await fetch('/admin/moderate/post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: postId, action: action })
        });
        const data = await response.json();

        if (data.success) {
            if (action === 'delete') {
                const item = document.querySelector(`.moderation-item[data-post-id="${postId}"]`);
                if (item) {
                    item.style.transition = 'opacity 0.3s, transform 0.3s';
                    item.style.opacity = '0';
                    item.style.transform = 'translateX(-20px)';
                    setTimeout(() => {
                        item.remove();
                        const wrapper = document.querySelector('#tab-moderation .list-wrapper');
                        if (wrapper && wrapper.querySelectorAll('.moderation-item').length === 0) {
                            wrapper.innerHTML = `<div class="empty-state"><i class="fa-solid fa-check-circle" style="color:#10B981;"></i><p>No reported posts! All posts are clean.</p></div>`;
                        }
                    }, 300);
                } else {
                    location.reload();
                }
            } else {
                location.reload();
            }
        } else {
            showErrorModal(data.message || 'Something went wrong');
            if (btn) btn.classList.remove('btn-loading');
        }
    } catch (err) {
        console.error(err);
        showErrorModal('An error occurred. Please try again.');
        if (btn) btn.classList.remove('btn-loading');
    }
}

async function moderateComment(commentId, action, btn) {
    const config = {
        'approve': {
            verb: 'approve and dismiss all reports for',
            title: 'Approve Comment',
            confirmText: 'Approve',
            confirmClass: 'btn-success',
            icon: 'fa-circle-check',
            iconColor: '#10B981',
            iconBg: '#D1FAE5',
        },
        'delete': {
            verb: 'permanently delete',
            title: 'Delete Comment',
            confirmText: 'Delete',
            confirmClass: 'btn-danger',
            icon: 'fa-triangle-exclamation',
            iconColor: '#EF4444',
            iconBg: '#FEE2E2',
        },
    };

    const cfg = config[action] || {
        verb: action,
        title: 'Confirm Action',
        confirmText: 'Confirm',
        confirmClass: 'btn-primary',
        icon: 'fa-circle-question',
        iconColor: '#32418C',
        iconBg: '#E8EBF7',
    };

    let message = `Are you sure you want to ${cfg.verb} this comment?`;
    if (action === 'delete') {
        message += '\n\nThis action cannot be undone.';
    }

    const confirmed = await showConfirmModal({
        title: cfg.title,
        message,
        confirmText: cfg.confirmText,
        confirmClass: cfg.confirmClass,
        icon: cfg.icon,
        iconColor: cfg.iconColor,
        iconBg: cfg.iconBg,      // 👈 ADD THIS
    });

    if (!confirmed) return;

    if (btn) btn.classList.add('btn-loading');

    try {
        const response = await fetch('/admin/moderate/comment', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: commentId, action: action })
        });
        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            showErrorModal(data.message || 'Something went wrong');
            if (btn) btn.classList.remove('btn-loading');
        }
    } catch (err) {
        console.error(err);
        showErrorModal('An error occurred. Please try again.');
        if (btn) btn.classList.remove('btn-loading');
    }
}

function viewPost(postId) {
    window.open('/admin/posts/' + postId + '/view', '_blank');
}

function viewComment(commentId) {
    window.open('/admin/comments/' + commentId, '_blank');
}

// ========== CUSTOM TOOLTIP FOR MODERATION TAB ==========
document.addEventListener('DOMContentLoaded', function() {
    // Apply custom tooltips to all action buttons in moderation tab
    document.querySelectorAll('#tab-moderation .btn-action').forEach(button => {
        const originalTitle = button.getAttribute('data-title') || button.getAttribute('title');
        if (!originalTitle) return;

        button.addEventListener('mouseenter', function(e) {
            // Prevent default browser tooltip
            this.removeAttribute('title');

            // Remove any existing custom tooltip
            const existing = document.querySelector('.custom-tooltip');
            if (existing) existing.remove();

            // Create custom tooltip
            const tip = document.createElement('div');
            tip.className = 'custom-tooltip';
            tip.textContent = originalTitle;
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

            this._tooltip = tip;
        });

        button.addEventListener('mouseleave', function() {
            // Restore title attribute
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
</script>

<style>
    /* ========================================
   FIX: MODERATION TAB TOOLTIP OVERFLOW
   ======================================== */

/* Allow overflow on moderation tab containers so tooltips don't get clipped */
#tab-moderation .dash-card,
#tab-moderation .list-wrapper,
#tab-moderation .moderation-item,
#tab-moderation .violator-item,
#tab-moderation .mod-item-actions,
#tab-moderation .violator-actions {
    overflow: visible !important;
}

/* Ensure the list wrapper doesn't clip tooltips vertically */
#tab-moderation .list-wrapper {
    overflow-y: auto;
    overflow-x: visible;
}

/* Custom tooltip styles */
.custom-tooltip {
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
    font-family: 'Poppins', sans-serif;
}

/* ========================================
   HIDDEN POST VISUAL STATES (Moderation Tab)
   ======================================== */

#tab-moderation .moderation-item.item-hidden {
    background: linear-gradient(135deg, #FEF2F2 0%, #FEE2E2 100%);
    border-left-color: #EF4444 !important;
}

#tab-moderation .moderation-item.item-hidden .mod-item-text {
    color: var(--gray-500);
    font-style: italic;
}

#tab-moderation .status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    padding: 0.2rem 0.55rem;
    border-radius: var(--radius-full);
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.02em;
    white-space: nowrap;
}

#tab-moderation .status-badge.status-hidden {
    background: #FEE2E2;
    color: #B91C1C;
    border: 1px solid #FCA5A5;
}

#tab-moderation .status-badge.status-approved {
    background: #D1FAE5;
    color: #065F46;
    border: 1px solid #6EE7B7;
}

/* Green unhide button */
#tab-moderation .btn-action.unhide-post {
    background: #D1FAE5;
    color: #065F46;
    border-color: #6EE7B7;
}
#tab-moderation .btn-action.unhide-post:hover {
    background: #A7F3D0;
    color: #064E3B;
    border-color: #10B981;
}

/* Loading state — only the clicked button spins */
#tab-moderation .btn-action.btn-loading {
    opacity: 0.55;
    pointer-events: none;
}
#tab-moderation .btn-action.btn-loading i {
    animation: spin 0.9s linear infinite;
}

#tab-moderation .btn-action.unrestrict-user {
    background: #D1FAE5;
    color: #065F46;
    border-color: #6EE7B7;
}
#tab-moderation .btn-action.unrestrict-user:hover {
    background: #A7F3D0;
    color: #064E3B;
    border-color: #10B981;
}

</style>