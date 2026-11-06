<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Perk | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
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
                </div>
                <button class="sidebar-close" id="sidebarClose" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="/admin/dashboard" class="nav-item">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Dashboard</span>
                </a>
                <a href="/admin/directory" class="nav-item">
                    <i class="fa-solid fa-users"></i>
                    <span>Alumni Directory</span>
                </a>
                <a href="{{ route('announcements.index') }}" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('events.index') }}" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="{{ route('perks.index') }}" class="nav-item active">
                    <i class="fa-solid fa-gift"></i>
                    <span>Perks & Discounts</span>
                </a>
                <a href="/admin/alumni_tracer" class="nav-item">
                    <i class="fa-solid fa-location-dot"></i>
                    <span>Alumni Tracer</span>
                </a>
                <a href="/admin/messages" class="nav-item">
                    <i class="fa-solid fa-envelope"></i>
                    <span>Messages</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="nav-item">
                    <i class="fa-solid fa-gear"></i>
                    <span>Settings</span>
                </a>
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
                            <i class="fa-solid fa-pen-to-square"></i>
                            Edit Perk
                        </h1>
                        <p class="page-subtitle">Update perk details for NU Lipa alumni</p>
                    </div>
                    
                    <div class="header-actions">
                        <a href="{{ route('perks.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> 
                            <span>Back to Perks</span>
                        </a>
                    </div>
                </div>
            </header>

            @if ($errors->any())
                <div class="upload-status status-error" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: var(--radius-lg); background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-top: 0.125rem; flex-shrink: 0;"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 0.375rem;">Please fix the following errors:</strong>
                        <ul style="margin:0; padding-left:1.25rem;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('perks.update', $perk->id) }}" 
                  method="POST" 
                  enctype="multipart/form-data" 
                  id="perkForm" 
                  class="form-card">
                @csrf
                @method('PUT')
                
                <div class="perks-details-layout">
                    <!-- Left Panel - Main Form Fields -->
                    <div class="form-left-panel">
                        <!-- Perk Title -->
                        <div class="form-group">
                            <label for="title" class="form-label">Perk Title</label>
                            <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $perk->title) }}" required>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Give your perk a clear, descriptive title.</small>
                        </div>

                        <!-- Description -->
                        <div class="form-group">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description" name="description" class="form-control textarea-description" required>{{ old('description', $perk->description) }}</textarea>
                            <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Describe the perk details, terms, or instructions for alumni.</small>
                        </div>

                        <!-- Status + Validity Date Row -->
                        <div class="form-row-2col">
                            <div class="form-group">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status" class="form-control">
                                    <option value="1" {{ old('status', (string) $perk->status) == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', (string) $perk->status) == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="valid_until" class="form-label">Validity Date</label>
                                <input type="date" id="valid_until" name="valid_until" class="form-control" 
                                       min="{{ date('Y-m-d') }}" 
                                       value="{{ old('valid_until', $perk->valid_until ? $perk->valid_until->format('Y-m-d') : '') }}" required>
                                <small style="color: var(--gray-500); font-size: 0.8125rem; display: block; margin-top: 0.375rem;">Set when this perk expires.</small>
                            </div>
                        </div>
                    </div>

                    <!-- Right Panel - Attachments + Actions -->
                    <div class="form-right-panel">
                        
                        @include('partials.perk-upload-zone', [
                            'perk'      => $perk,
                            'existing'  => $perk->images,
                            'maxFiles'  => 5,
                            'maxSizeMb' => 5,
                        ])

                        <!-- Form Actions -->
                        <div class="form-actions">
                            <a href="{{ route('perks.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-check"></i>
                                <span>Update Perk</span>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
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
    // ========================================
    // Mobile Menu Toggle
    // ========================================
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) toggleMobileMenu();
        });
    });

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
        // ========================================
        // WARNING MODAL SYSTEM
        // ========================================
        const warningOverlay = document.getElementById('warningModal');
        const warningTitle = document.getElementById('warningModalTitle');
        const warningMessage = document.getElementById('warningModalMessage');
        const warningIcon = document.getElementById('warningModalIcon');
        const confirmBtn = document.getElementById('warningModalConfirm');
        const modalCancelBtn = document.getElementById('warningModalCancel');

        let pendingCallback = null;

        function closeWarningModal() {
            warningOverlay.classList.remove('active');
            document.body.style.overflow = '';
            pendingCallback = null;
        }

        modalCancelBtn.addEventListener('click', closeWarningModal);
        warningOverlay.addEventListener('click', function(e) { if (e.target === warningOverlay) closeWarningModal(); });
        document.addEventListener('keydown', function(e) { if (e.key === 'Escape' && warningOverlay.classList.contains('active')) closeWarningModal(); });
        confirmBtn.addEventListener('click', function() { if (pendingCallback) pendingCallback(); closeWarningModal(); });

        window.showWarningModal = function(config) {
            const {
                title = 'Confirm Action',
                message = 'Are you sure?',
                iconType = 'warning',
                confirmText = 'Confirm',
                confirmClass = 'btn-danger',
                onConfirm = null,
                hideCancel = false
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
            modalCancelBtn.style.display = hideCancel ? 'none' : 'inline-flex';
            pendingCallback = onConfirm;
            warningOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();
        };

        // Cancel button confirmation
        const cancelLink = document.querySelector('.form-actions .btn-secondary');
        if (cancelLink) {
            cancelLink.addEventListener('click', function(e) {
                e.preventDefault();
                const href = this.getAttribute('href');
                window.showWarningModal({
                    title: 'Discard Changes',
                    message: 'Are you sure you want to <strong>cancel</strong>?<br><small>All unsaved changes will be lost.</small>',
                    iconType: 'warning',
                    confirmText: 'Discard',
                    confirmClass: 'btn-warning',
                    onConfirm: function() { window.location.href = href; }
                });
            });
        }
    });

    /* =========================================================
   PERK UPLOAD ZONE — Drag, Drop, Paste, Reorder
   ========================================================= */

(function () {
    const zone = document.getElementById('perkUploadZone');
    if (!zone) return;

    const dropzone     = document.getElementById('uploadDropzone');
    const fileInput    = document.getElementById('attachmentInput');
    const previewGrid  = document.getElementById('attachment-preview-container');
    const emptyHint    = document.getElementById('uploadEmptyHint');
    const counterEl    = document.getElementById('uploadCounter');
    const counterCur   = document.getElementById('uploadCounterCurrent');
    const removedBox   = document.getElementById('uploadRemovedIds');

    const MAX_FILES    = parseInt(zone.dataset.maxFiles || '5', 10);
    const MAX_SIZE     = parseInt(zone.dataset.maxSizeMb || '5', 10) * 1024 * 1024;
    const ALLOWED_EXT  = ['jpg', 'jpeg', 'png', 'webp'];

    // DataTransfer holds newly-selected files so we can add/remove dynamically
    const dt = new DataTransfer();

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------
    function currentTileCount() {
        return previewGrid.querySelectorAll('.upload-tile:not(.marked-for-removal)').length;
    }

    function updateCounter() {
        const n = currentTileCount();
        counterCur.textContent = n;
        counterEl.classList.toggle('is-full', n >= MAX_FILES);
        emptyHint.style.display = n === 0 ? 'flex' : 'none';
        refreshCoverBadge();
        refreshIndexBadges();
    }

    function refreshCoverBadge() {
        previewGrid.querySelectorAll('.upload-tile-cover-badge').forEach(b => b.remove());
        const first = previewGrid.querySelector('.upload-tile:not(.marked-for-removal)');
        if (first) {
            const badge = document.createElement('span');
            badge.className = 'upload-tile-cover-badge';
            badge.innerHTML = '<i class="fa-solid fa-star"></i> Cover';
            first.appendChild(badge);
        }
    }

    function refreshIndexBadges() {
        const tiles = previewGrid.querySelectorAll('.upload-tile');
        let visibleIndex = 1;
        tiles.forEach(tile => {
            let idx = tile.querySelector('.upload-tile-index');
            if (!idx) {
                idx = document.createElement('span');
                idx.className = 'upload-tile-index';
                tile.appendChild(idx);
            }
            if (tile.classList.contains('marked-for-removal')) {
                idx.textContent = '—';
            } else {
                idx.textContent = visibleIndex++;
            }
        });
    }

    function warn(title, message) {
        if (typeof window.showWarningModal === 'function') {
            window.showWarningModal({
                title: title,
                message: message,
                iconType: 'warning',
                confirmText: 'OK',
                confirmClass: 'btn-warning',
                hideCancel: true,
            });
        } else {
            alert(message.replace(/<[^>]+>/g, ''));
        }
    }

    // ---------------------------------------------------------
    // Add files to preview + DataTransfer
    // ---------------------------------------------------------
    function addFiles(fileList) {
        const files = Array.from(fileList);
        const existingCount = currentTileCount();

        // Filter valid
        const valid = [];
        for (const file of files) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_EXT.includes(ext)) {
                warn('Invalid File Type', `<strong>${file.name}</strong> is not supported.<br><small>Only JPG, PNG, WEBP allowed.</small>`);
                continue;
            }
            if (file.size > MAX_SIZE) {
                warn('File Too Large', `<strong>${file.name}</strong> exceeds ${MAX_SIZE / 1024 / 1024}MB.`);
                continue;
            }
            valid.push(file);
        }

        if (existingCount + valid.length > MAX_FILES) {
            warn('Too Many Files', `You can only have up to <strong>${MAX_FILES}</strong> images.<br><small>Currently: ${existingCount}. Tried to add: ${valid.length}.</small>`);
            return;
        }

        valid.forEach(file => {
            // Add to DataTransfer
            dt.items.add(file);

            // Add preview tile
            const reader = new FileReader();
            reader.onload = function (e) {
                const tile = document.createElement('div');
                tile.className = 'upload-tile new-image';
                tile.draggable = true;
                tile.dataset.type = 'new';
                tile.dataset.fileName = file.name;
                tile.innerHTML = `
                    <img src="${e.target.result}" class="upload-tile-img" alt="${file.name}">
                    <button type="button" class="upload-tile-remove" data-action="remove-new" title="Remove">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <span class="upload-tile-drag-handle" title="Drag to reorder">
                        <i class="fa-solid fa-grip-vertical"></i>
                    </span>
                `;
                previewGrid.appendChild(tile);
                updateCounter();
            };
            reader.readAsDataURL(file);
        });

        // Sync to real input
        fileInput.files = dt.files;
    }

    // ---------------------------------------------------------
    // Remove a file (new or existing)
    // ---------------------------------------------------------
    function removeTile(tile) {
        if (tile.dataset.type === 'new') {
            const fileName = tile.dataset.fileName;
            // Rebuild DataTransfer without that file
            const newDt = new DataTransfer();
            Array.from(dt.files).forEach(f => {
                if (f.name !== fileName) newDt.items.add(f);
            });
            // Swap contents
            dt.items.clear();
            Array.from(newDt.files).forEach(f => dt.items.add(f));
            fileInput.files = dt.files;

            tile.remove();
        } else if (tile.dataset.type === 'existing') {
            const id = tile.dataset.imageId;
            const isMarked = tile.classList.contains('marked-for-removal');

            if (isMarked) {
                // Undo
                tile.classList.remove('marked-for-removal');
                const hidden = removedBox.querySelector(`input[value="${id}"]`);
                if (hidden) hidden.remove();
            } else {
                // Mark for removal
                tile.classList.add('marked-for-removal');
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'remove_existing[]';
                hidden.value = id;
                removedBox.appendChild(hidden);
            }
        }

        updateCounter();
    }

    // ---------------------------------------------------------
    // Dropzone interactions
    // ---------------------------------------------------------
    dropzone.addEventListener('click', () => fileInput.click());
    dropzone.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            fileInput.click();
        }
    });

    fileInput.addEventListener('change', function (event) {
        addFiles(event.target.files);
        // NOTE: don't reset this.value — DataTransfer handles it.
    });

    // ---------------------------------------------------------
    // Drag & drop onto dropzone
    // ---------------------------------------------------------
    ['dragenter', 'dragover'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'drop'].forEach(evt => {
        dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('is-dragover');
        });
    });

    dropzone.addEventListener('drop', (e) => {
        if (e.dataTransfer && e.dataTransfer.files.length) {
            addFiles(e.dataTransfer.files);
        }
    });

    // ---------------------------------------------------------
    // Global drag feedback (full-page overlay)
    // ---------------------------------------------------------
    let dragDepth = 0;
    window.addEventListener('dragenter', (e) => {
        if (e.dataTransfer && Array.from(e.dataTransfer.types).includes('Files')) {
            dragDepth++;
            document.body.classList.add('is-dragging-files');
        }
    });
    window.addEventListener('dragleave', () => {
        dragDepth = Math.max(0, dragDepth - 1);
        if (dragDepth === 0) document.body.classList.remove('is-dragging-files');
    });
    window.addEventListener('drop', () => {
        dragDepth = 0;
        document.body.classList.remove('is-dragging-files');
    });
    window.addEventListener('dragover', (e) => {
        // Prevent browser from opening the file if user drops outside the zone
        if (e.dataTransfer && Array.from(e.dataTransfer.types).includes('Files')) {
            e.preventDefault();
        }
    });

    // ---------------------------------------------------------
    // Paste from clipboard
    // ---------------------------------------------------------
    document.addEventListener('paste', (e) => {
        // Only when upload zone is somewhere on the page and focus isn't in a text field
        const active = document.activeElement;
        if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA') && active !== dropzone) {
            return; // don't hijack paste inside text fields
        }

        const items = (e.clipboardData || window.clipboardData)?.items || [];
        const files = [];
        for (const item of items) {
            if (item.kind === 'file' && item.type.startsWith('image/')) {
                const file = item.getAsFile();
                if (file) files.push(file);
            }
        }
        if (files.length) {
            e.preventDefault();
            addFiles(files);
        }
    });

    // ---------------------------------------------------------
    // Remove button delegation
    // ---------------------------------------------------------
    previewGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="remove-new"], [data-action="mark-remove"]');
        if (!btn) return;
        const tile = btn.closest('.upload-tile');
        if (tile) removeTile(tile);
    });

    // ---------------------------------------------------------
    // Drag to reorder tiles
    // ---------------------------------------------------------
    let draggedTile = null;

    previewGrid.addEventListener('dragstart', (e) => {
        const tile = e.target.closest('.upload-tile');
        if (!tile) return;
        draggedTile = tile;
        tile.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
        // Firefox needs data set
        try { e.dataTransfer.setData('text/plain', ''); } catch (err) {}
    });

    previewGrid.addEventListener('dragend', () => {
        if (draggedTile) draggedTile.classList.remove('is-dragging');
        previewGrid.querySelectorAll('.upload-tile').forEach(t => t.classList.remove('is-drag-over'));
        draggedTile = null;
    });

    previewGrid.addEventListener('dragover', (e) => {
        e.preventDefault();
        const target = e.target.closest('.upload-tile');
        if (!target || target === draggedTile) return;
        target.classList.add('is-drag-over');
    });

    previewGrid.addEventListener('dragleave', (e) => {
        const target = e.target.closest('.upload-tile');
        if (target) target.classList.remove('is-drag-over');
    });

    previewGrid.addEventListener('drop', (e) => {
        e.preventDefault();
        const target = e.target.closest('.upload-tile');
        if (!target || !draggedTile || target === draggedTile) return;
        target.classList.remove('is-drag-over');

        // Figure out ordering
        const allTiles = Array.from(previewGrid.querySelectorAll('.upload-tile'));
        const fromIndex = allTiles.indexOf(draggedTile);
        const toIndex = allTiles.indexOf(target);

        if (fromIndex < toIndex) {
            target.after(draggedTile);
        } else {
            target.before(draggedTile);
        }

        // Re-sync the DataTransfer order (only matters for new images)
        const newDt = new DataTransfer();
        previewGrid.querySelectorAll('.upload-tile[data-type="new"]').forEach(t => {
            const fname = t.dataset.fileName;
            const file = Array.from(dt.files).find(f => f.name === fname);
            if (file) newDt.items.add(file);
        });
        dt.items.clear();
        Array.from(newDt.files).forEach(f => dt.items.add(f));
        fileInput.files = dt.files;

        refreshCoverBadge();
        refreshIndexBadges();
    });

    // ---------------------------------------------------------
    // Prevent accidental navigation away with unsaved changes
    // ---------------------------------------------------------
    let isDirty = false;
    zone.addEventListener('change', () => { isDirty = true; });
    previewGrid.addEventListener('click', () => { isDirty = true; });

    window.addEventListener('beforeunload', (e) => {
        if (isDirty) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    // Clear dirty flag on submit
    const form = document.getElementById('perkForm');
    if (form) {
        form.addEventListener('submit', function () {
            isDirty = false;
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('is-submitting');
                submitBtn.disabled = true;
            }
        });
    }

    // ---------------------------------------------------------
    // Initial render
    // ---------------------------------------------------------
    updateCounter();
})();

</script>

</body>
</html>

{{-- This is Perk's edit.blade.php --}}