{{-- ============================================ --}}
{{-- ALUMNI ACCESS TRACKER + RESEND INVITE       --}}
{{-- Modal-based selection (no card checkboxes)  --}}
{{-- ============================================ --}}

<style>
.access-filter-bar { display:flex; align-items:center; gap:1rem; padding:0.75rem 1rem; background:var(--white); border:1px solid var(--gray-200); border-radius:var(--radius-lg); margin-bottom:1rem; flex-wrap:wrap; box-shadow:var(--shadow-xs); }
.access-filter-label { display:flex; align-items:center; gap:0.5rem; font-size:0.8125rem; font-weight:600; color:var(--gray-600); padding-right:0.75rem; border-right:1px solid var(--gray-200); }
.access-filter-label i { color:var(--nu-blue); }
.access-filter-pills { display:flex; gap:0.5rem; flex-wrap:wrap; flex:1; }
.access-pill { display:inline-flex; align-items:center; gap:0.4rem; padding:0.4rem 0.9rem; border-radius:9999px; border:2px solid var(--gray-200); background:var(--white); font-family:inherit; font-size:0.75rem; font-weight:500; color:var(--gray-600); cursor:pointer; transition:all 0.2s ease; white-space:nowrap; }
.access-pill:hover { border-color:var(--gray-300); transform:translateY(-1px); }
.access-pill.active { border-color:var(--nu-blue); background:var(--nu-blue); color:white; box-shadow:0 4px 12px rgba(50,65,140,0.25); }
.access-pill[data-access="accessed"].active { border-color:var(--success); background:var(--success); }
.access-pill[data-access="pending"].active { border-color:var(--warning); background:var(--warning); }
.access-pill-count { display:inline-flex; align-items:center; justify-content:center; min-width:20px; height:18px; padding:0 0.4rem; background:var(--gray-100); color:var(--gray-600); border-radius:9999px; font-size:0.65rem; font-weight:700; }
.access-pill.active .access-pill-count { background:rgba(255,255,255,0.25); color:white; }
.btn-resend-bulk { background:var(--nu-blue); color:white; border:none; padding:0.5rem 1rem; border-radius:9999px; font-family:inherit; font-size:0.8125rem; font-weight:500; cursor:pointer; display:inline-flex; align-items:center; gap:0.5rem; transition:all 0.2s ease; box-shadow:0 2px 8px rgba(50,65,140,0.2); }
.btn-resend-bulk:hover:not(:disabled) { background:var(--nu-blue-dark); transform:translateY(-1px); box-shadow:0 4px 12px rgba(50,65,140,0.3); }
.btn-resend-bulk:disabled { opacity:0.5; cursor:not-allowed; }
.bulk-count-badge { background:rgba(255,255,255,0.3); padding:0.1rem 0.5rem; border-radius:9999px; font-size:0.7rem; font-weight:700; }

.alumni-access-status { display:inline-flex; align-items:center; }
.access-badge { display:inline-flex; align-items:center; gap:0.35rem; padding:0.2rem 0.6rem; border-radius:9999px; font-size:0.7rem; font-weight:600; letter-spacing:0.2px; }
.access-badge.accessed { background:var(--success-light); color:#065F46; }
.access-badge.pending { background:var(--warning-light); color:#92400E; }
.access-badge i { font-size:0.7rem; }

.resend-recipient-list { margin-top:0.75rem; max-height:340px; overflow-y:auto; border:1px solid var(--gray-200); border-radius:var(--radius-md); background:var(--white); }
.resend-recipient-item { display:flex; align-items:center; gap:0.75rem; padding:0.6rem 0.85rem; border-bottom:1px solid var(--gray-100); cursor:pointer; transition:background 0.15s ease; user-select:none; }
.resend-recipient-item:last-child { border-bottom:none; }
.resend-recipient-item:hover { background:var(--gray-50); }
.resend-recipient-item .item-avatar { width:34px; height:34px; border-radius:50%; background:var(--gray-200); color:var(--gray-600); display:flex; align-items:center; justify-content:center; font-weight:600; font-size:0.72rem; flex-shrink:0; overflow:hidden; }
.resend-recipient-item .item-avatar img { width:100%; height:100%; object-fit:cover; }
.resend-recipient-item .item-info { flex:1; min-width:0; }
.resend-recipient-item .item-name { font-weight:600; color:var(--gray-800); display:block; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; font-size:0.85rem; }
.resend-recipient-item .item-email { color:var(--gray-500); font-size:0.75rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; display:block; }
.resend-recipient-item .item-status { flex-shrink:0; font-size:0.78rem; color:var(--gray-400); min-width:16px; text-align:center; }
.resend-recipient-item .item-status.sent { color:var(--success); }
.resend-recipient-item .item-status.failed { color:var(--danger); }
.resend-recipient-item.selected { background:rgba(50,65,140,0.04); }

.recipient-toggle { position:relative; display:inline-block; width:42px; height:24px; flex-shrink:0; }
.recipient-toggle input { opacity:0; width:0; height:0; }
.recipient-toggle .toggle-slider { position:absolute; cursor:pointer; top:0; left:0; right:0; bottom:0; background:var(--gray-300); transition:.25s; border-radius:24px; }
.recipient-toggle .toggle-slider:before { position:absolute; content:""; height:18px; width:18px; left:3px; bottom:3px; background:white; transition:.25s; border-radius:50%; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
.recipient-toggle input:checked + .toggle-slider { background:var(--nu-blue); }
.recipient-toggle input:checked + .toggle-slider:before { transform:translateX(18px); }

.resend-toolbar { display:flex; justify-content:space-between; align-items:center; gap:0.75rem; flex-wrap:wrap; padding:0.6rem 0.85rem; background:var(--nu-blue-soft); border-radius:var(--radius-md); border:1px solid var(--nu-blue-light); font-size:0.8125rem; color:var(--gray-700); }
.resend-toolbar strong { color:var(--nu-blue-dark); }
.resend-toolbar .toolbar-links { display:flex; gap:0.75rem; }
.resend-toolbar .toolbar-links button { background:none; border:none; color:var(--nu-blue); font-family:inherit; font-size:0.8125rem; font-weight:600; cursor:pointer; padding:0.15rem 0.4rem; border-radius:4px; }
.resend-toolbar .toolbar-links button:hover { background:rgba(50,65,140,0.08); }

.resend-progress-bar { width:100%; height:8px; background:var(--gray-200); border-radius:9999px; overflow:hidden; }
.resend-progress-fill { height:100%; background:linear-gradient(90deg,var(--nu-blue) 0%,var(--nu-blue-light) 100%); width:0%; transition:width 0.3s ease; border-radius:9999px; }
.resend-progress-text { margin:0.5rem 0 0 0; font-size:0.8125rem; color:var(--gray-600); text-align:center; }
@media (max-width:640px) {
    .access-filter-bar { flex-direction:column; align-items:stretch; }
    .access-filter-label { border-right:none; border-bottom:1px solid var(--gray-200); padding-bottom:0.5rem; padding-right:0; }
    .access-filter-pills { justify-content:center; }
    .btn-resend-bulk { justify-content:center; width:100%; }
}
</style>

{{-- FILTER BAR --}}
<div class="access-filter-bar" id="accessFilterBar">
    <div class="access-filter-label">
        <i class="fa-solid fa-signal"></i>
        <span>Access Status</span>
    </div>
    <div class="access-filter-pills">
        <button type="button" class="access-pill active" data-access="all" onclick="applyAccessFilter('all')">
            <i class="fa-solid fa-list"></i><span>All</span>
        </button>
        <button type="button" class="access-pill" data-access="accessed" onclick="applyAccessFilter('accessed')">
            <i class="fa-solid fa-circle-check"></i><span>Has Accessed</span>
        </button>
        <button type="button" class="access-pill" data-access="pending" onclick="applyAccessFilter('pending')">
            <i class="fa-solid fa-hourglass-half"></i><span>Never Accessed</span>
            <span class="access-pill-count" id="pendingAccessCount">0</span>
        </button>
    </div>
    <button type="button" class="btn btn-sm btn-resend-bulk" id="resendBulkBtn" onclick="openResendInviteModal()" disabled>
        <i class="fa-solid fa-paper-plane"></i>
        <span>Resend Invites</span>
        <span class="bulk-count-badge" id="resendBulkCount" style="display:none;">0</span>
    </button>
</div>

{{-- RESEND MODAL --}}
<div id="resendInviteModal" class="modal-overlay" aria-hidden="true" inert>
    <div class="modal-content-wrapper" style="max-width:640px;">
        <div class="modal-card">
            <div class="modal-header">
                <div>
                    <h2 class="modal-title"><i class="fa-solid fa-paper-plane"></i> Resend LumiNUs Invitation</h2>
                    <p class="modal-subtitle" id="resendModalSubtitle">Choose who should receive a fresh invitation email.</p>
                </div>
                <button class="modal-close" onclick="hideResendInviteModal()" title="Close"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body" style="padding:1.5rem;">
                <div class="form-note" style="margin-bottom:1rem;">
                    <i class="fa-solid fa-circle-info"></i>
                    <div>
                        <strong>What happens when you resend?</strong>
                        <ul style="margin:0.5rem 0 0 0; padding-left:1.25rem; font-size:0.85rem; line-height:1.55;">
                            <li>A new temporary password will be generated</li>
                            <li>The alumni's Supabase login credentials will be updated</li>
                            <li>The welcome email will be sent again</li>
                            <li>They will be required to change their password on first login</li>
                        </ul>
                    </div>
                </div>

                <div class="resend-toolbar">
                    <span>Selected: <strong id="resendSelectedCount">0</strong> of <strong id="resendTotalCount">0</strong> never-accessed alumni</span>
                    <div class="toolbar-links">
                        <button type="button" onclick="selectAllRecipients(true)">Select All</button>
                        <button type="button" onclick="selectAllRecipients(false)">Clear</button>
                    </div>
                </div>

                <div id="resendRecipientList" class="resend-recipient-list"></div>

                <div id="resendProgress" style="display:none; margin-top:1.25rem;">
                    <div class="resend-progress-bar"><div class="resend-progress-fill" id="resendProgressFill"></div></div>
                    <p class="resend-progress-text" id="resendProgressText">Sending... 0 / 0</p>
                </div>
            </div>
            <div class="modal-actions" style="padding:1rem 1.5rem; border-top:1px solid var(--gray-200); display:flex; justify-content:flex-end; gap:0.75rem;">
                <button type="button" class="btn btn-secondary" id="resendCancelBtn" onclick="hideResendInviteModal()">Cancel</button>
                <button type="button" class="btn btn-primary" id="resendConfirmBtn" onclick="executeResendInvites()" disabled>
                    <i class="fa-solid fa-paper-plane"></i>
                    <span id="resendConfirmText">Send Invitations</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let currentAccessFilter = 'all';
let allNeverAccessed = [];
let selectedRecipientIds = new Set();

document.addEventListener('DOMContentLoaded', function() {
    // Reposition filter bar just below the directory search toolbar
    const filterBar = document.getElementById('accessFilterBar');
    const toolbar   = document.querySelector('.directory-toolbar');
    if (filterBar && toolbar && toolbar.parentNode) {
        toolbar.parentNode.insertBefore(filterBar, toolbar.nextSibling);
    }

    fetchAccessStatus();
});

/* ---------- FETCH FROM BACKEND ---------- */
async function fetchAccessStatus() {
    try {
        const response = await fetch('/admin/alumni/access-status', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        const data = await response.json();

        if (!data.success || !Array.isArray(data.alumni)) {
            console.error('Access status fetch failed:', data);
            return;
        }

        allNeverAccessed = data.alumni
            .filter(a => !a.has_accessed)
            .map(a => ({
                id:    a.id,
                name:  a.name,
                email: a.email,
                photo: a.photo,
            }));

        updatePendingCount();
        updateBulkResendButtonLabel();

        // Set data-has-accessed on each card so the filter can use it.
        // No visual badge is injected — access status stays invisible on the card.
        document.querySelectorAll('.alumni-card').forEach(card => {
            const id = card.dataset.id;
            const record = data.alumni.find(a => String(a.id) === String(id));
            if (!record) return;

            card.dataset.hasAccessed = record.has_accessed ? '1' : '0';
        });

        // Re-run the directory filter so access pills work
        if (typeof performFilter === 'function') performFilter();

    } catch (err) {
        console.error('Access status error:', err);
    }
}

function updatePendingCount() {
    const badge = document.getElementById('pendingAccessCount');
    if (badge) badge.textContent = allNeverAccessed.length;

    const total = document.getElementById('resendTotalCount');
    if (total) total.textContent = allNeverAccessed.length;

    updateBulkResendButtonLabel();
}

function updateBulkResendButtonLabel() {
    const btn = document.getElementById('resendBulkBtn');
    if (!btn) return;
    btn.disabled = allNeverAccessed.length === 0;
}

/* ---------- ACCESS FILTER ---------- */
function applyAccessFilter(filter) {
    currentAccessFilter = filter;
    document.querySelectorAll('.access-pill').forEach(pill => {
        pill.classList.toggle('active', pill.dataset.access === filter);
    });
    if (typeof performFilter === 'function') performFilter();
}

/* ---------- MODAL ---------- */
function openResendInviteModal() {
    if (allNeverAccessed.length === 0) {
        if (typeof showAlert === 'function') showAlert('No alumni to invite — everyone has accessed their account.', 'info');
        return;
    }

    selectedRecipientIds = new Set(allNeverAccessed.map(a => a.id));
    renderRecipientList();
    updateSelectedCount();

    const progress = document.getElementById('resendProgress');
    progress.style.display = 'none';
    document.getElementById('resendProgressFill').style.width = '0%';

    const modal = document.getElementById('resendInviteModal');
    modal.classList.add('active');
    modal.removeAttribute('inert');
    document.body.style.overflow = 'hidden';
}

function hideResendInviteModal() {
    const modal = document.getElementById('resendInviteModal');
    if (!modal) return;
    modal.classList.remove('active');
    modal.setAttribute('inert', '');
    document.body.style.overflow = '';

    const confirmBtn = document.getElementById('resendConfirmBtn');
    confirmBtn.disabled = false;
    confirmBtn.onclick = executeResendInvites;
    confirmBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span id="resendConfirmText">Send Invitations</span>';
    updateSelectedCount();
}

function renderRecipientList() {
    const list = document.getElementById('resendRecipientList');
    if (!list) return;
    list.innerHTML = '';

    allNeverAccessed.forEach(person => {
        const initials = (person.name || '?')
            .split(' ').filter(Boolean).slice(0, 2)
            .map(w => w[0]).join('').toUpperCase();

        const isSelected = selectedRecipientIds.has(person.id);

        const item = document.createElement('div');
        item.className = 'resend-recipient-item' + (isSelected ? ' selected' : '');
        item.dataset.id = person.id;
        item.onclick = (e) => {
            if (e.target.closest('.recipient-toggle')) return;
            const cb = item.querySelector('input[type="checkbox"]');
            cb.checked = !cb.checked;
            cb.dispatchEvent(new Event('change'));
        };

        const avatarHTML = person.photo
            ? `<img src="${escapeAttr(person.photo)}" alt="">`
            : initials;

        item.innerHTML = `
            <div class="item-avatar">${avatarHTML}</div>
            <div class="item-info">
                <span class="item-name">${escapeHtml(person.name) || 'Unnamed'}</span>
                <span class="item-email">${escapeHtml(person.email) || '—'}</span>
            </div>
            <span class="item-status" data-status-for="${escapeAttr(person.id)}">
                <i class="fa-regular fa-circle"></i>
            </span>
            <label class="recipient-toggle" onclick="event.stopPropagation();">
                <input type="checkbox" ${isSelected ? 'checked' : ''}
                       onchange="toggleRecipient('${escapeAttr(person.id)}', this.checked)">
                <span class="toggle-slider"></span>
            </label>
        `;
        list.appendChild(item);
    });
}

function toggleRecipient(id, checked) {
    if (checked) selectedRecipientIds.add(id);
    else selectedRecipientIds.delete(id);

    const item = document.querySelector(`.resend-recipient-item[data-id="${id}"]`);
    if (item) item.classList.toggle('selected', checked);

    updateSelectedCount();
}

function selectAllRecipients(select) {
    if (select) {
        selectedRecipientIds = new Set(allNeverAccessed.map(a => a.id));
    } else {
        selectedRecipientIds = new Set();
    }
    renderRecipientList();
    updateSelectedCount();
}

function updateSelectedCount() {
    const selEl = document.getElementById('resendSelectedCount');
    if (selEl) selEl.textContent = selectedRecipientIds.size;

    const confirmBtn = document.getElementById('resendConfirmBtn');
    if (confirmBtn) confirmBtn.disabled = selectedRecipientIds.size === 0;

    const badge = document.getElementById('resendBulkCount');
    if (badge) {
        if (selectedRecipientIds.size > 0 && document.getElementById('resendInviteModal').classList.contains('active')) {
            badge.style.display = 'inline-block';
            badge.textContent = selectedRecipientIds.size;
        } else {
            badge.style.display = 'none';
        }
    }
}

/* ---------- SEND ---------- */
async function executeResendInvites() {
    if (selectedRecipientIds.size === 0) {
        if (typeof showAlert === 'function') showAlert('Select at least one recipient.', 'warning');
        return;
    }

    const targets = allNeverAccessed.filter(a => selectedRecipientIds.has(a.id));

    const confirmBtn  = document.getElementById('resendConfirmBtn');
    const cancelBtn   = document.getElementById('resendCancelBtn');
    const progress    = document.getElementById('resendProgress');
    const progressFill = document.getElementById('resendProgressFill');
    const progressText = document.getElementById('resendProgressText');

    confirmBtn.disabled = true;
    cancelBtn.disabled  = true;
    confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

    const total = targets.length;
    let successCount = 0, failCount = 0;

    progress.style.display = 'block';
    progressFill.style.width = '0%';
    progressText.textContent = `Sending... 0 / ${total}`;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    for (let i = 0; i < total; i++) {
        const t = targets[i];
        try {
            const response = await fetch(`/admin/alumni/${t.id}/resend-invite`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            if (response.ok && data.success) {
                successCount++;
                markRecipient(t.id, 'sent');
            } else {
                failCount++;
                markRecipient(t.id, 'failed', data.message);
            }
        } catch (err) {
            failCount++;
            markRecipient(t.id, 'failed', err.message);
        }

        const done = i + 1;
        progressFill.style.width = Math.round((done / total) * 100) + '%';
        progressText.textContent = `Sending... ${done} / ${total}`;
    }

    progressText.textContent = `✅ Done — ${successCount} sent, ${failCount} failed`;
    cancelBtn.disabled = false;
    confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> Close';

    confirmBtn.onclick = function() {
        hideResendInviteModal();
        if (successCount > 0) {
            if (typeof showAlert === 'function') {
                showAlert(`${successCount} invitation${successCount !== 1 ? 's' : ''} sent successfully.`, 'success');
            }
            setTimeout(() => window.location.reload(), 900);
        }
    };
}

function markRecipient(id, status, message = '') {
    const el = document.querySelector(`.item-status[data-status-for="${id}"]`);
    if (!el) return;
    if (status === 'sent') {
        el.className = 'item-status sent';
        el.innerHTML = '<i class="fa-solid fa-check-circle"></i>';
    } else {
        el.className = 'item-status failed';
        el.title = message || 'Failed';
        el.innerHTML = '<i class="fa-solid fa-times-circle"></i>';
    }
}

/* ---------- HELPERS ---------- */
function escapeHtml(str) {
    return String(str || '').replace(/[&<>"']/g, c => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    })[c]);
}
function escapeAttr(str) { return escapeHtml(str); }
</script>