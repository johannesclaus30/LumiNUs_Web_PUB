<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Announcement | LumiNUs Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/announcements_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    @include('partials.admin-navbar')
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
                <a href="{{ route('announcements.index') }}" class="nav-item active">
                    <i class="fa-solid fa-bullhorn"></i>
                    <span>Announcements</span>
                </a>
                <a href="{{ route('events.index') }}" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Events</span>
                </a>
                <a href="{{ route('perks.index') }}" class="nav-item">
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

        <main class="admin-main">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title"><i class="fa-solid fa-pen-to-square"></i> Edit Announcement</h1>
                        <p class="page-subtitle">Update the details of this announcement</p>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('announcements.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> <span>Back</span>
                        </a>
                    </div>
                </div>
            </header>

            @if ($errors->has('images') || $errors->has('video'))
                <div class="upload-status status-error" style="margin-bottom: 1.5rem; padding: 1rem 1.25rem; border-radius: var(--radius-lg); background: #fee2e2; color: #ef4444; border: 1px solid #ef4444; display: flex; align-items: flex-start; gap: 0.75rem;">
                    <i class="fa-solid fa-circle-exclamation" style="margin-top: 0.125rem; flex-shrink: 0;"></i>
                    <div>
                        <strong style="display: block; margin-bottom: 0.375rem;">Attachment error:</strong>
                        <ul style="margin:0; padding-left:1.25rem;">
                            @foreach ($errors->get('images') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                            @foreach ($errors->get('video') as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <form action="{{ route('announcements.update', $announcement->id) }}" method="POST" enctype="multipart/form-data" id="announcementForm" class="form-card">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label for="title" class="form-label">Announcement Title</label>
                    <input type="text" id="title" name="title" class="form-control" value="{{ old('title', $announcement->title) }}" required>
                </div>

                <div class="form-group">
                    <label for="announcement_description" class="form-label">Description</label>
                    <textarea id="announcement_description" name="announcement_description" class="form-control" required>{{ old('announcement_description', $announcement->announcement_description) }}</textarea>
                </div>
                
                <div class="form-group">
                    <label for="scheduled_at" class="form-label">Schedule Post (Optional)</label>
                    @php
                        $scheduledValue = old('scheduled_at');
                        if (!$scheduledValue && $announcement->scheduled_at) {
                            $scheduledValue = $announcement->scheduled_at->setTimezone('Asia/Manila')->format('Y-m-d\TH:i');
                        }
                    @endphp

                    <input type="datetime-local" id="scheduled_at" name="scheduled_at" class="form-control"
                        value="{{ $scheduledValue }}">
                    <small style="color: var(--gray-500); font-size: 0.8125rem;">Leave blank to keep this announcement unscheduled.</small>
                </div>

                <!-- Attachments Section -->
                <div class="form-group">
                    
                    @include('partials.announcement-upload-zone', [
                        'announcement'   => $announcement,
                        'maxImages'      => 5,
                        'maxImageMb'     => 3,
                        'maxVideoMb'     => 30,
                        'existingImages' => $announcement->images->filter(fn($i) => !in_array(strtolower(pathinfo($i->image_path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi'])),
                        'existingVideos' => $announcement->images->filter(fn($i) => in_array(strtolower(pathinfo($i->image_path, PATHINFO_EXTENSION)), ['mp4', 'mov', 'avi'])),
                    ])

                </div>

                <div class="form-actions">
                    <a href="{{ route('announcements.index') }}" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-save"></i>
                        <span>Update Announcement</span>
                    </button>
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
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        // ========================================
        // WARNING MODAL SYSTEM
        // ========================================
        
        document.addEventListener('DOMContentLoaded', function() {
            // Get modal elements
            const warningOverlay = document.getElementById('warningModal');
            const warningTitle = document.getElementById('warningModalTitle');
            const warningMessage = document.getElementById('warningModalMessage');
            const warningIcon = document.getElementById('warningModalIcon');
            const confirmBtn = document.getElementById('warningModalConfirm');
            const cancelBtn = document.getElementById('warningModalCancel');
            
            let pendingCallback = null;
            
            // Close modal function
            function closeWarningModal() {
                warningOverlay.classList.remove('active');
                document.body.style.overflow = '';
                pendingCallback = null;
            }
            
            // Cancel button
            cancelBtn.addEventListener('click', closeWarningModal);
            
            // Close on overlay click
            warningOverlay.addEventListener('click', function(e) {
                if (e.target === warningOverlay) {
                    closeWarningModal();
                }
            });

            const formCancelBtn = document.querySelector('.form-actions .btn-secondary');
            if (formCancelBtn) {
                formCancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const href = this.getAttribute('href');
                    window.showWarningModal({
                        title: 'Discard Changes',
                        message: 'Are you sure you want to <strong>cancel</strong>?<br><small>All unsaved changes will be lost.</small>',
                        iconType: 'warning',
                        confirmText: 'Discard',
                        confirmClass: 'btn-warning',
                        onConfirm: function() {
                            window.location.href = href;
                        }
                    });
                });
            }
            
            // Close on Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && warningOverlay.classList.contains('active')) {
                    closeWarningModal();
                }
            });
            
            // Confirm button - execute the pending callback
            confirmBtn.addEventListener('click', function() {
                if (pendingCallback) {
                    pendingCallback();
                }
                closeWarningModal();
            });
            
            // Show modal function
            window.showWarningModal = function(config) {
                const {
                    title = 'Confirm Action',
                    message = 'Are you sure?',
                    iconType = 'warning',
                    confirmText = 'Confirm',
                    confirmClass = 'btn-danger',
                    onConfirm = null
                } = config;
                
                // Set title and message
                warningTitle.textContent = title;
                warningMessage.innerHTML = message;
                
                // Set icon
                warningIcon.className = 'warning-modal-icon ' + iconType;
                const iconElement = warningIcon.querySelector('i');
                if (iconType === 'danger') {
                    iconElement.className = 'fa-solid fa-triangle-exclamation';
                } else if (iconType === 'success') {
                    iconElement.className = 'fa-solid fa-circle-question';
                } else {
                    iconElement.className = 'fa-solid fa-triangle-exclamation';
                }
                
                // Set confirm button
                confirmBtn.className = 'btn ' + confirmClass;
                confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;
                
                // Store callback
                pendingCallback = onConfirm;
                
                // Show modal
                warningOverlay.classList.add('active');
                document.body.style.overflow = 'hidden';
                confirmBtn.focus();
            };
            
            // Close sidebar when clicking on a nav item (mobile)
            document.querySelectorAll('.nav-item').forEach(item => {
                item.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        toggleMobileMenu();
                    }
                });
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

        /* =========================================================
   ANNOUNCEMENT UPLOAD ZONE — Images + Video (mutually exclusive)
   ========================================================= */

(function () {
    const zone = document.getElementById('announcementUploadZone');
    if (!zone) return;

    const imageSection     = document.getElementById('imageSection');
    const videoSection     = document.getElementById('videoSection');
    const orDivider        = document.getElementById('uploadOrDivider');

    const imageDropzone    = document.getElementById('uploadDropzone');
    const videoDropzone    = document.getElementById('videoDropzone');

    const imageInput       = document.getElementById('attachmentInput');
    const videoInput       = document.getElementById('videoInput');

    const previewGrid      = document.getElementById('attachment-preview-container');
    const videoSlot        = document.getElementById('videoPreviewSlot');

    const emptyHint        = document.getElementById('uploadEmptyHint');
    const counterEl        = document.getElementById('uploadCounter');
    const counterCur       = document.getElementById('uploadCounterCurrent');
    const removedBox       = document.getElementById('uploadRemovedIds');

    const MAX_IMAGES       = parseInt(zone.dataset.maxImages || '5', 10);
    const MAX_IMAGE_SIZE   = parseInt(zone.dataset.maxImageMb || '3', 10) * 1024 * 1024;
    const MAX_VIDEO_SIZE   = parseInt(zone.dataset.maxVideoMb || '30', 10) * 1024 * 1024;
    const ALLOWED_IMG_EXT  = ['jpg', 'jpeg', 'png', 'webp'];

    const dt = new DataTransfer();
    let newVideoFile = null;

    // -----------------------------------------------------
    // Helpers
    // -----------------------------------------------------
    function activeImageTiles() {
        return previewGrid.querySelectorAll('.upload-tile:not(.marked-for-removal)').length;
    }

    function hasActiveVideo() {
        const existingVid = videoSlot.querySelector('.upload-tile.existing-video:not(.marked-for-removal)');
        return !!existingVid || !!newVideoFile;
    }

    function warn(title, message) {
        if (typeof window.showWarningModal === 'function') {
            window.showWarningModal({
                title, message, iconType: 'warning',
                confirmText: 'OK', confirmClass: 'btn-warning', hideCancel: true,
            });
        } else {
            alert(message.replace(/<[^>]+>/g, ''));
        }
    }

    function updateState() {
        const imageCount = activeImageTiles();
        const videoOn    = hasActiveVideo();

        counterCur.textContent = imageCount;
        counterEl.classList.toggle('is-full', imageCount >= MAX_IMAGES);
        emptyHint.style.display = imageCount === 0 ? 'flex' : 'none';

        imageSection.classList.toggle('is-disabled', videoOn);
        videoSection.classList.toggle('is-disabled', imageCount > 0);

        imageInput.disabled = videoOn;
        videoInput.disabled = imageCount > 0;

        orDivider.classList.toggle('is-hidden', videoOn || imageCount > 0);

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
        let vi = 1;
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
                idx.textContent = vi++;
            }
        });
    }

    // -----------------------------------------------------
    // Image handling
    // -----------------------------------------------------
    function addImageFiles(fileList) {
        const files = Array.from(fileList);
        const current = activeImageTiles();

        const valid = [];
        for (const file of files) {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!ALLOWED_IMG_EXT.includes(ext)) {
                warn('Invalid File Type', `<strong>${file.name}</strong> is not supported.<br><small>Only JPG, PNG, WEBP allowed.</small>`);
                continue;
            }
            if (file.size > MAX_IMAGE_SIZE) {
                warn('File Too Large', `<strong>${file.name}</strong> exceeds ${MAX_IMAGE_SIZE / 1024 / 1024}MB.`);
                continue;
            }
            valid.push(file);
        }

        if (current + valid.length > MAX_IMAGES) {
            warn('Too Many Images', `You can have up to <strong>${MAX_IMAGES}</strong> images.<br><small>Currently: ${current}. Tried to add: ${valid.length}.</small>`);
            return;
        }

        valid.forEach(file => {
            dt.items.add(file);
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
                updateState();
            };
            reader.readAsDataURL(file);
        });

        imageInput.files = dt.files;
    }

    function removeImageTile(tile) {
        if (tile.dataset.type === 'new') {
            const name = tile.dataset.fileName;
            const newDt = new DataTransfer();
            Array.from(dt.files).forEach(f => { if (f.name !== name) newDt.items.add(f); });
            dt.items.clear();
            Array.from(newDt.files).forEach(f => dt.items.add(f));
            imageInput.files = dt.files;
            tile.remove();
        } else if (tile.dataset.type === 'existing') {
            const id = tile.dataset.imageId;
            const marked = tile.classList.contains('marked-for-removal');
            if (marked) {
                tile.classList.remove('marked-for-removal');
                const hidden = removedBox.querySelector(`input[value="${id}"][data-kind="image"]`);
                if (hidden) hidden.remove();
            } else {
                tile.classList.add('marked-for-removal');
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'deleted_media[]';
                hidden.value = id;
                hidden.dataset.kind = 'image';
                removedBox.appendChild(hidden);
            }
        }
        updateState();
    }

    // -----------------------------------------------------
    // Video handling
    // -----------------------------------------------------
    function addVideoFile(file) {
        const ext = file.name.split('.').pop().toLowerCase();
        if (ext !== 'mp4') {
            warn('Invalid Video Type', `<strong>${file.name}</strong> is not MP4.<br><small>Only MP4 videos are allowed.</small>`);
            return;
        }
        if (file.size > MAX_VIDEO_SIZE) {
            warn('Video Too Large', `<strong>${file.name}</strong> exceeds ${MAX_VIDEO_SIZE / 1024 / 1024}MB.`);
            return;
        }

        newVideoFile = file;
        videoSlot.innerHTML = '';

        const reader = new FileReader();
        reader.onload = function (e) {
            const tile = document.createElement('div');
            tile.className = 'upload-tile video new-video';
            tile.dataset.type = 'new-video';
            tile.innerHTML = `
                <video src="${e.target.result}" class="upload-tile-img" muted preload="metadata"></video>
                <span class="upload-play-badge"><i class="fa-solid fa-play"></i></span>
                <button type="button" class="upload-tile-remove" data-action="remove-new-video" title="Remove video">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            `;
            videoSlot.appendChild(tile);
            updateState();
        };
        reader.readAsDataURL(file);
    }

    function removeVideoTile(tile) {
        if (tile.dataset.type === 'new-video') {
            newVideoFile = null;
            // Clear video input
            const clearDt = new DataTransfer();
            videoInput.files = clearDt.files;
            tile.remove();
        } else if (tile.dataset.type === 'existing-video') {
            const id = tile.dataset.imageId;
            const marked = tile.classList.contains('marked-for-removal');
            if (marked) {
                tile.classList.remove('marked-for-removal');
                const hidden = removedBox.querySelector(`input[value="${id}"][data-kind="video"]`);
                if (hidden) hidden.remove();
            } else {
                tile.classList.add('marked-for-removal');
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'deleted_media[]';
                hidden.value = id;
                hidden.dataset.kind = 'video';
                removedBox.appendChild(hidden);
            }
        }
        updateState();
    }

    // -----------------------------------------------------
    // Image input wiring
    // -----------------------------------------------------
    imageDropzone.addEventListener('click', () => {
        if (!imageInput.disabled) imageInput.click();
    });
    imageDropzone.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && !imageInput.disabled) {
            e.preventDefault();
            imageInput.click();
        }
    });

    imageInput.addEventListener('change', (event) => {
        addImageFiles(event.target.files);
    });

    ['dragenter', 'dragover'].forEach(evt => {
        imageDropzone.addEventListener(evt, (e) => {
            if (imageInput.disabled) return;
            e.preventDefault(); e.stopPropagation();
            imageDropzone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        imageDropzone.addEventListener(evt, (e) => {
            e.preventDefault(); e.stopPropagation();
            imageDropzone.classList.remove('is-dragover');
        });
    });
    imageDropzone.addEventListener('drop', (e) => {
        if (imageInput.disabled) return;
        if (e.dataTransfer && e.dataTransfer.files.length) {
            addImageFiles(e.dataTransfer.files);
        }
    });

    // -----------------------------------------------------
    // Video input wiring
    // -----------------------------------------------------
    videoDropzone.addEventListener('click', () => {
        if (!videoInput.disabled) videoInput.click();
    });
    videoDropzone.addEventListener('keydown', (e) => {
        if ((e.key === 'Enter' || e.key === ' ') && !videoInput.disabled) {
            e.preventDefault();
            videoInput.click();
        }
    });

    videoInput.addEventListener('change', (event) => {
        if (event.target.files.length) addVideoFile(event.target.files[0]);
    });

    ['dragenter', 'dragover'].forEach(evt => {
        videoDropzone.addEventListener(evt, (e) => {
            if (videoInput.disabled) return;
            e.preventDefault(); e.stopPropagation();
            videoDropzone.classList.add('is-dragover');
        });
    });
    ['dragleave', 'drop'].forEach(evt => {
        videoDropzone.addEventListener(evt, (e) => {
            e.preventDefault(); e.stopPropagation();
            videoDropzone.classList.remove('is-dragover');
        });
    });
    videoDropzone.addEventListener('drop', (e) => {
        if (videoInput.disabled) return;
        if (e.dataTransfer && e.dataTransfer.files.length) {
            addVideoFile(e.dataTransfer.files[0]);
        }
    });

    // -----------------------------------------------------
    // Paste (images only)
    // -----------------------------------------------------
    document.addEventListener('paste', (e) => {
        if (imageInput.disabled) return;
        const active = document.activeElement;
        if (active && (active.tagName === 'INPUT' || active.tagName === 'TEXTAREA')) return;

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
            addImageFiles(files);
        }
    });

    // -----------------------------------------------------
    // Global drag overlay
    // -----------------------------------------------------
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
        if (e.dataTransfer && Array.from(e.dataTransfer.types).includes('Files')) {
            e.preventDefault();
        }
    });

    // -----------------------------------------------------
    // Remove-button delegation (both grid + video slot)
    // -----------------------------------------------------
    previewGrid.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="remove-new"], [data-action="mark-remove"]');
        if (!btn) return;
        const tile = btn.closest('.upload-tile');
        if (tile) removeImageTile(tile);
    });

    videoSlot.addEventListener('click', (e) => {
        const btn = e.target.closest('[data-action="remove-new-video"], [data-action="mark-remove"]');
        if (!btn) return;
        const tile = btn.closest('.upload-tile');
        if (tile) removeVideoTile(tile);
    });

    // -----------------------------------------------------
    // Image reorder
    // -----------------------------------------------------
    let draggedTile = null;
    previewGrid.addEventListener('dragstart', (e) => {
        const tile = e.target.closest('.upload-tile');
        if (!tile) return;
        draggedTile = tile;
        tile.classList.add('is-dragging');
        e.dataTransfer.effectAllowed = 'move';
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

        const allTiles = Array.from(previewGrid.querySelectorAll('.upload-tile'));
        const fromIndex = allTiles.indexOf(draggedTile);
        const toIndex = allTiles.indexOf(target);
        if (fromIndex < toIndex) target.after(draggedTile);
        else target.before(draggedTile);

        const newDt = new DataTransfer();
        previewGrid.querySelectorAll('.upload-tile[data-type="new"]').forEach(t => {
            const fname = t.dataset.fileName;
            const file = Array.from(dt.files).find(f => f.name === fname);
            if (file) newDt.items.add(file);
        });
        dt.items.clear();
        Array.from(newDt.files).forEach(f => dt.items.add(f));
        imageInput.files = dt.files;

        refreshCoverBadge();
        refreshIndexBadges();
    });

    // -----------------------------------------------------
    // Submit spinner
    // -----------------------------------------------------
    const form = document.getElementById('announcementForm');
    if (form) {
        form.addEventListener('submit', function () {
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.classList.add('is-submitting');
                submitBtn.disabled = true;
            }
        });
    }

    // -----------------------------------------------------
    // Initial render
    // -----------------------------------------------------
    updateState();
})();

    </script>
</body>
</html>

{{-- This is the edit.blade.php file for editing announcements --}}