<!-- ============================================ -->
<!-- MODALS -->
<!-- ============================================ -->

<!-- Violator Detail Modal -->
<div id="violatorModal" class="modal-overlay" onclick="if(event.target === this) closeModal('violatorModal')">
    <div class="modal-content-wrapper" style="max-width: 800px;">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="violatorModalTitle">
                        <i class="fa-solid fa-user"></i>
                        Violator Details
                    </h2>
                    <p class="modal-subtitle" id="violatorModalSubtitle">Full report details for this alumni</p>
                </div>
                <button class="modal-close" onclick="closeModal('violatorModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" id="violatorModalBody">
                <div class="modal-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p>Loading details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Post Detail Modal -->
<div id="postModal" class="modal-overlay" onclick="if(event.target === this) closeModal('postModal')">
    <div class="modal-content-wrapper" style="max-width: 800px;">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="postModalTitle">
                        <i class="fa-solid fa-file-lines"></i>
                        Post Report Details
                    </h2>
                    <p class="modal-subtitle" id="postModalSubtitle">Full details of reported post</p>
                </div>
                <button class="modal-close" onclick="closeModal('postModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" id="postModalBody">
                <div class="modal-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p>Loading details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Comment Detail Modal -->
<div id="commentModal" class="modal-overlay" onclick="if(event.target === this) closeModal('commentModal')">
    <div class="modal-content-wrapper" style="max-width: 800px;">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title" id="commentModalTitle">
                        <i class="fa-solid fa-comment"></i>
                        Comment Report Details
                    </h2>
                    <p class="modal-subtitle" id="commentModalSubtitle">Full details of reported comment</p>
                </div>
                <button class="modal-close" onclick="closeModal('commentModal')">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" id="commentModalBody">
                <div class="modal-loading">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                    <p>Loading details...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Post Interactions Modal -->
<div id="interactionsModal" class="interactions-modal" style="display: none;">
    <div class="interactions-modal-overlay" onclick="closeInteractionsModal()"></div>
    <div class="interactions-modal-content">
        <div class="interactions-modal-header">
            <div class="interactions-modal-title">
                <i class="fa-regular fa-heart"></i>
                <span id="interactionsModalTitle">Interactions</span>
                <span class="interactions-count" id="interactionsCount">(0)</span>
            </div>
            <button class="interactions-modal-close" onclick="closeInteractionsModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="interactions-modal-body">
            <!-- Tabs -->
            <div class="interactions-tabs">
                <button class="interactions-tab active" data-tab="likes" onclick="switchInteractionTab('likes')">
                    <i class="fa-regular fa-heart"></i> Likes
                </button>
                <button class="interactions-tab" data-tab="comments" onclick="switchInteractionTab('comments')">
                    <i class="fa-regular fa-comment"></i> Comments
                </button>
                <button class="interactions-tab" data-tab="reposts" onclick="switchInteractionTab('reposts')">
                    <i class="fa-solid fa-retweet"></i> Reposts
                </button>
            </div>
            
            <!-- Content Panels -->
            <div class="interactions-panels">
                <!-- Likes Panel -->
                <div class="interactions-panel active" id="likesPanel">
                    <div class="interactions-list" id="likesList">
                        <div class="interactions-loading">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading likes...
                        </div>
                    </div>
                </div>
                
                <!-- Comments Panel -->
                <div class="interactions-panel" id="commentsPanel">
                    <div class="interactions-list" id="commentsList">
                        <div class="interactions-loading">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading comments...
                        </div>
                    </div>
                </div>
                
                <!-- Reposts Panel -->
                <div class="interactions-panel" id="repostsPanel">
                    <div class="interactions-list" id="repostsList">
                        <div class="interactions-loading">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading reposts...
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- INTERACTIONS MODAL JAVASCRIPT -->
<script>
let currentPostId = null;
let currentTab = 'likes';

function openInteractionsModal(postId, tab = 'likes') {
    currentPostId = postId;
    currentTab = tab;
    
    const modal = document.getElementById('interactionsModal');
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Set title
    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';
    
    // Switch tab
    switchInteractionTab(tab);
    
    // Load data
    loadInteractions(postId, tab);
}

function closeInteractionsModal() {
    const modal = document.getElementById('interactionsModal');
    modal.style.display = 'none';
    modal.classList.remove('active');
    document.body.style.overflow = '';
    currentPostId = null;
}

function switchInteractionTab(tab) {
    currentTab = tab;
    
    // Update tabs
    document.querySelectorAll('.interactions-tab').forEach(t => {
        t.classList.remove('active');
        if (t.dataset.tab === tab) {
            t.classList.add('active');
        }
    });
    
    // Update panels
    document.querySelectorAll('.interactions-panel').forEach(p => {
        p.classList.remove('active');
    });
    const panel = document.getElementById(tab + 'Panel');
    if (panel) {
        panel.classList.add('active');
    }
    
    // Update title
    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';
    
    // Load data if we have a post ID
    if (currentPostId) {
        loadInteractions(currentPostId, tab);
    }
}

function loadInteractions(postId, type) {
    const listId = type + 'List';
    const list = document.getElementById(listId);
    
    if (!list) return;
    
    // Show loading
    list.innerHTML = `
        <div class="interactions-loading">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading ${type}...
        </div>
    `;
    
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // Make AJAX request
    fetch(`/admin/posts/${postId}/interactions?type=${type}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderInteractions(listId, data.data, type);
            const countEl = document.getElementById('interactionsCount');
            if (countEl) {
                countEl.textContent = `(${data.total || 0})`;
            }
        } else {
            list.innerHTML = `
                <div class="interactions-empty">
                    <i class="fa-regular fa-circle-xmark"></i>
                    <p>${data.message || 'Failed to load interactions.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading interactions:', error);
        list.innerHTML = `
            <div class="interactions-empty">
                <i class="fa-regular fa-circle-xmark"></i>
                <p>Error loading ${type}. Please try again.</p>
            </div>
        `;
    });
}

function renderInteractions(listId, items, type) {
    const list = document.getElementById(listId);
    if (!list) return;
    
    if (!items || items.length === 0) {
        const iconMap = {
            'likes': 'fa-regular fa-heart',
            'comments': 'fa-regular fa-comment',
            'reposts': 'fa-solid fa-retweet'
        };
        list.innerHTML = `
            <div class="interactions-empty">
                <i class="${iconMap[type] || 'fa-regular fa-circle'}" style="font-size: 2.5rem;"></i>
                <p>No ${type} yet</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    items.forEach(item => {
        const initials = (item.first_name?.[0] || '?') + (item.last_name?.[0] || '');
        const fullName = `${item.first_name || 'Unknown'} ${item.last_name || ''}`.trim();
        const timeAgo = item.created_at ? timeAgoHelper(item.created_at) : '';
        
        let avatarHtml = '';
        if (item.profile_photo) {
            avatarHtml = `<img src="${item.profile_photo}" alt="${fullName}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
        } else {
            avatarHtml = initials.toUpperCase();
        }
        
        let detailHtml = '';
        if (type === 'comments' && item.comment) {
            detailHtml = `<div class="interactions-item-detail"><i class="fa-regular fa-comment"></i> ${escapeHtml(item.comment)}</div>`;
        } else if (type === 'reposts' && item.caption) {
            detailHtml = `<div class="interactions-item-detail"><i class="fa-regular fa-retweet"></i> ${escapeHtml(item.caption)}</div>`;
        }
        
        html += `
            <div class="interactions-item">
                <div class="interactions-item-avatar" style="${item.profile_photo ? 'padding: 0; overflow: hidden; background: none;' : ''}">
                    ${avatarHtml}
                </div>
                <div class="interactions-item-info">
                    <div class="interactions-item-name">${escapeHtml(fullName)}</div>
                    ${detailHtml}
                </div>
                ${timeAgo ? `<div class="interactions-item-time">${timeAgo}</div>` : ''}
            </div>
        `;
    });
    
    list.innerHTML = html;
}

function timeAgoHelper(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diffMs = now - past;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);
    
    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return diffMins + 'm ago';
    if (diffHours < 24) return diffHours + 'h ago';
    if (diffDays < 7) return diffDays + 'd ago';
    return past.toLocaleDateString();
}

// Close modal on overlay click
document.querySelector('.interactions-modal-overlay')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeInteractionsModal();
    }
});
</script>