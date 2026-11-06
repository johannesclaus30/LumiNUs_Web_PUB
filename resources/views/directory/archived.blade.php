{{-- resources/views/directory/archived.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Archived & Restricted Alumni | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SheetJS for Excel/CSV parsing -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- Add this in the head section -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    
    <style>
        :root {
            --gray-50: #f9fafb;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-500: #6b7280;
            --gray-700: #374151;
            --warning: #f59e0b;
            --warning-light: #fef3c7;
            --danger: #ef4444;
            --danger-light: #fee2e2;
            --success: #10b981;
            --success-light: #d1fae5;
            --info: #3b82f6;
            --info-light: #dbeafe;
            --nu-blue: #32418c;
            --nu-blue-soft: #e8edf9;
            --white: #ffffff;
            --radius: 0.5rem;
            --radius-full: 9999px;
            --transition: 0.3s ease;
        }

        .tabs-container {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 2rem;
            border-bottom: 2px solid var(--gray-200);
            padding-bottom: 0;
        }
        
        .tab-btn {
            padding: 0.75rem 1.5rem;
            border: none;
            background: transparent;
            color: var(--gray-500);
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.9375rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            transition: all var(--transition);
            border-radius: 0;
        }
        
        .tab-btn:hover {
            color: var(--nu-blue);
            background: var(--nu-blue-soft);
        }
        
        .tab-btn.active {
            color: var(--nu-blue);
            border-bottom-color: var(--nu-blue);
        }
        
        .tab-btn .badge {
            display: inline-block;
            padding: 0.125rem 0.625rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            margin-left: 0.5rem;
        }
        
        .tab-btn .badge.archived {
            background: var(--warning-light);
            color: var(--warning);
        }
        
        .tab-btn .badge.restricted {
            background: var(--danger-light);
            color: var(--danger);
        }
        
        .tab-content {
            display: none;
            animation: fadeIn 0.3s ease-out;
        }
        
        .tab-content.active {
            display: block;
        }

        .alumni-card .archived-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--warning-light);
            color: var(--warning);
            border: 1px solid var(--warning);
        }

        .alumni-card .restricted-badge {
            position: absolute;
            top: 1rem;
            right: 1rem;
            padding: 0.375rem 0.75rem;
            border-radius: var(--radius-full);
            font-size: 0.75rem;
            font-weight: 600;
            background: var(--danger-light);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .alumni-card .restriction-info {
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: var(--gray-50);
            border-radius: var(--radius);
            border-left: 3px solid var(--danger);
        }

        .alumni-card .restriction-info .reason-label {
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--gray-500);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .alumni-card .restriction-info .reason-text {
            font-size: 0.875rem;
            color: var(--gray-700);
            margin: 0.25rem 0;
        }

        .alumni-card .restriction-info .comment-text {
            font-size: 0.8125rem;
            color: var(--gray-500);
            font-style: italic;
        }

        .btn-restore {
            background: var(--success-light);
            color: var(--success);
            border: 1px solid var(--success);
        }

        .btn-restore:hover {
            background: var(--success);
            color: var(--white);
        }

        .btn-permanent-delete {
            background: var(--danger-light);
            color: var(--danger);
            border: 1px solid var(--danger);
        }

        .btn-permanent-delete:hover {
            background: var(--danger);
            color: var(--white);
        }

        .alumni-card-header {
            position: relative;
        }

        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }

        .empty-state .empty-icon {
            font-size: 4rem;
            color: var(--gray-300);
            margin-bottom: 1rem;
        }

        /* Unrestrict modal specific styles */
        #unrestrictConfirmModal .confirm-icon-wrapper {
            background: var(--success-light);
            color: var(--success);
        }
    </style>
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
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
                <a href="/admin/dashboard" class="nav-item">
                    <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['directory']))
                <a href="/admin/directory" class="nav-item active">
                    <i class="fa-solid fa-users"></i><span>Alumni Directory</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['announcements']))
                <a href="{{ route('announcements.index') }}" class="nav-item">
                    <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['events']))
                <a href="{{ route('events.index') }}" class="nav-item">
                    <i class="fa-solid fa-calendar-check"></i><span>Events</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['perks']))
                <a href="{{ route('perks.index') }}" class="nav-item">
                    <i class="fa-solid fa-gift"></i><span>Perks & Discounts</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['tracer']))
                <a href="/admin/alumni_tracer" class="nav-item">
                    <i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['messages']))
                <a href="/admin/messages" class="nav-item">
                    <i class="fa-solid fa-envelope"></i><span>Messages</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['settings']))
                <a href="{{ route('admin.settings') }}" class="nav-item">
                    <i class="fa-solid fa-gear"></i><span>Settings</span>
                </a>
                @endif
            </nav>
        </aside>

        <main class="admin-main">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-box-archive"></i>
                            Archived & Restricted Accounts
                        </h1>
                        <p class="page-subtitle">Manage archived and restricted alumni accounts</p>
                    </div>
                    <div class="header-actions">
                        <a href="/admin/directory" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> 
                            <span>Back to Directory</span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon" style="background: var(--warning-light); color: var(--warning);">
                            <i class="fa-solid fa-box-archive"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalArchived ?? 0 }}</span>
                        <span class="stat-label">Archived Accounts</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon" style="background: var(--danger-light); color: var(--danger);">
                            <i class="fa-solid fa-user-slash"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalRestricted ?? 0 }}</span>
                        <span class="stat-label">Restricted Accounts</span>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="tabs-container">
                <button class="tab-btn active" data-tab="archived" onclick="switchTab('archived')">
                    <i class="fa-solid fa-box-archive"></i> Archived
                    <span class="badge archived">{{ $totalArchived ?? 0 }}</span>
                </button>
                <button class="tab-btn" data-tab="restricted" onclick="switchTab('restricted')">
                    <i class="fa-solid fa-user-slash"></i> Restricted
                    <span class="badge restricted">{{ $totalRestricted ?? 0 }}</span>
                </button>
            </div>

            <!-- Archived Tab Content -->
            <div id="tab-archived" class="tab-content active">
                @include('directory.partials.archived_list', ['alumni' => $archivedAlumni])
            </div>

            <!-- Restricted Tab Content -->
            <div id="tab-restricted" class="tab-content">
                @include('directory.partials.restricted_list', ['alumni' => $restrictedAlumni])
            </div>
        </main>
    </div>

    <!-- Restore Confirmation Modal -->
    <div id="restoreConfirmModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" style="background: var(--success-light); color: var(--success);">
                    <i class="fa-solid fa-rotate-left"></i>
                </div>
                <h3 class="confirm-title">Restore Account</h3>
                <p class="confirm-message">
                    Are you sure you want to restore <strong id="restoreConfirmName"></strong>'s account?
                    <br><small>The alumni will be able to log in again.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideRestoreConfirm()">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="executeRestore()">
                        <i class="fa-solid fa-rotate-left"></i> Restore
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Unrestrict Confirmation Modal -->
    <div id="unrestrictConfirmModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" style="background: var(--success-light); color: var(--success);">
                    <i class="fa-solid fa-user-check"></i>
                </div>
                <h3 class="confirm-title">Unrestrict Account</h3>
                <p class="confirm-message">
                    Are you sure you want to unrestrict <strong id="unrestrictConfirmName"></strong>'s account?
                    <br><small>The alumni will be able to log in again. They will receive an email notification.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideUnrestrictConfirm()">Cancel</button>
                    <button type="button" class="btn btn-success" onclick="executeUnrestrict()">
                        <i class="fa-solid fa-user-check"></i> Unrestrict
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Permanent Delete Confirmation Modal -->
    <div id="permanentDeleteConfirmModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" style="background: var(--danger-light); color: var(--danger);">
                    <i class="fa-solid fa-trash-can"></i>
                </div>
                <h3 class="confirm-title">Permanently Delete Account</h3>
                <p class="confirm-message">
                    Are you sure you want to permanently delete <strong id="permanentDeleteConfirmName"></strong>'s account?
                    <br><strong style="color: var(--danger);">This action cannot be undone!</strong>
                    <br><small>All data associated with this account will be permanently removed.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hidePermanentDeleteConfirm()">Cancel</button>
                    <button type="button" class="btn btn-danger" onclick="executePermanentDelete()">
                        <i class="fa-solid fa-trash-can"></i> Delete Permanently
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Toast -->
    <div id="alertToast" class="alert-toast">
        <i class="alert-icon fa-solid fa-circle-check"></i>
        <span class="alert-message"></span>
        <button class="alert-close" onclick="hideAlert()"><i class="fa-solid fa-xmark"></i></button>
    </div>

   <script>
    // ========================================
    // CONSOLE DEBUG - Check if script loads
    // ========================================
    console.log('✅ Archived page JavaScript loaded');

    // Mobile menu toggle
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    // Tab switching - with hash update
    function switchTab(tab) {
        console.log('🔄 Switching to tab:', tab);
        // Update buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.tab === tab);
        });
        
        // Update content
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.toggle('active', content.id === 'tab-' + tab);
        });

        // Update URL hash without triggering scroll
        if (history.pushState) {
            history.pushState(null, null, '#tab-' + tab);
        }
    }

    // ========================================
    // RESTORE FUNCTIONS - Complete AJAX Version
    // ========================================
    let pendingRestoreId = null;

    function prepareRestore(id, name) {
        console.log('🔵 prepareRestore called with ID:', id, 'Name:', name);
        pendingRestoreId = id;
        const nameElement = document.getElementById('restoreConfirmName');
        if (nameElement) {
            nameElement.textContent = name;
        } else {
            console.error('❌ restoreConfirmName element not found!');
        }
        openModal('restoreConfirmModal');
    }

    function hideRestoreConfirm() {
        closeModal('restoreConfirmModal');
        pendingRestoreId = null;
    }

    async function executeRestore() {
        console.log('🔄 executeRestore called, pendingRestoreId:', pendingRestoreId);
        if (!pendingRestoreId) {
            console.error('❌ No pending restore ID!');
            return;
        }

        // Store the ID locally before it gets cleared
        const alumniId = pendingRestoreId;

        // Find the restore button in the modal
        const restoreBtn = document.querySelector('#restoreConfirmModal .btn-success');
        if (!restoreBtn) {
            console.error('❌ Restore button not found in modal!');
            return;
        }
        
        // Store original text and show loading
        if (!restoreBtn.getAttribute('data-original-text')) {
            restoreBtn.setAttribute('data-original-text', restoreBtn.innerHTML);
        }
        setButtonLoading(restoreBtn, true);

        try {
            const url = `/admin/alumni/${alumniId}/restore`;
            console.log('📡 Sending restore request to:', url);
            
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('📥 Restore response:', data);
            
            // Now clear the pending ID after storing it locally
            hideRestoreConfirm();

            if (data.success) {
                showAlert(data.message, 'success');
                // Use the stored ID, not pendingRestoreId
                removeAlumniCardWithAnimation(alumniId);
                // Update stats after animation
                setTimeout(() => {
                    updateStats();
                    checkEmptyState('tab-archived');
                }, 350);
            } else {
                showAlert(data.message || 'Failed to restore account.', 'error');
            }
        } catch (error) {
            console.error('❌ Restore error:', error);
            hideRestoreConfirm();
            showAlert('An error occurred while restoring the account. Check console for details.', 'error');
        } finally {
            // Reset button state
            if (restoreBtn) {
                setButtonLoading(restoreBtn, false);
            }
        }
    }

    // ========================================
    // UNRESTRICT FUNCTIONS - Complete AJAX Version
    // ========================================
    let pendingUnrestrictId = null;

    function prepareUnrestrict(id, name) {
        console.log('🟢 prepareUnrestrict called with ID:', id, 'Name:', name);
        pendingUnrestrictId = id;
        const nameElement = document.getElementById('unrestrictConfirmName');
        if (nameElement) {
            nameElement.textContent = name;
        } else {
            console.error('❌ unrestrictConfirmName element not found!');
        }
        openModal('unrestrictConfirmModal');
    }

    function hideUnrestrictConfirm() {
        closeModal('unrestrictConfirmModal');
        pendingUnrestrictId = null;
    }

    async function executeUnrestrict() {
        console.log('🔄 executeUnrestrict called, pendingUnrestrictId:', pendingUnrestrictId);
        if (!pendingUnrestrictId) {
            console.error('❌ No pending unrestrict ID!');
            return;
        }

        // Store the ID locally before it gets cleared
        const alumniId = pendingUnrestrictId;

        // Find the unrestrict button in the modal
        const unrestrictBtn = document.querySelector('#unrestrictConfirmModal .btn-success');
        if (!unrestrictBtn) {
            console.error('❌ Unrestrict button not found in modal!');
            return;
        }
        
        // Store original text and show loading
        if (!unrestrictBtn.getAttribute('data-original-text')) {
            unrestrictBtn.setAttribute('data-original-text', unrestrictBtn.innerHTML);
        }
        setButtonLoading(unrestrictBtn, true);

        try {
            const url = `/admin/alumni/${alumniId}/toggle-restrict`;
            console.log('📡 Sending unrestrict request to:', url);
            
            const response = await fetch(url, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ restrict: 0 })
            });

            const data = await response.json();
            console.log('📥 Unrestrict response:', data);
            
            hideUnrestrictConfirm();

            if (data.success) {
                showAlert(data.message, 'success');
                // Use the stored ID
                removeAlumniCardWithAnimation(alumniId);
                // Update stats after animation
                setTimeout(() => {
                    updateStats();
                    checkEmptyState('tab-restricted');
                }, 350);
            } else {
                showAlert(data.message || 'Failed to unrestrict account.', 'error');
            }
        } catch (error) {
            console.error('❌ Unrestrict error:', error);
            hideUnrestrictConfirm();
            showAlert('An error occurred while unrestricting the account. Check console for details.', 'error');
        } finally {
            // Reset button state
            if (unrestrictBtn) {
                setButtonLoading(unrestrictBtn, false);
            }
        }
    }

    // ========================================
    // PERMANENT DELETE FUNCTIONS - Complete AJAX Version
    // ========================================
    let pendingPermanentDeleteId = null;

    function preparePermanentDelete(id, name) {
        console.log('🔴 preparePermanentDelete called with ID:', id, 'Name:', name);
        pendingPermanentDeleteId = id;
        const nameElement = document.getElementById('permanentDeleteConfirmName');
        if (nameElement) {
            nameElement.textContent = name;
        } else {
            console.error('❌ permanentDeleteConfirmName element not found!');
        }
        openModal('permanentDeleteConfirmModal');
    }

    function hidePermanentDeleteConfirm() {
        closeModal('permanentDeleteConfirmModal');
        pendingPermanentDeleteId = null;
    }

    async function executePermanentDelete() {
        console.log('🔄 executePermanentDelete called, pendingPermanentDeleteId:', pendingPermanentDeleteId);
        if (!pendingPermanentDeleteId) {
            console.error('❌ No pending delete ID!');
            return;
        }

        // Store the ID locally before it gets cleared
        const alumniId = pendingPermanentDeleteId;

        // Find the delete button in the modal
        const deleteBtn = document.querySelector('#permanentDeleteConfirmModal .btn-danger');
        if (!deleteBtn) {
            console.error('❌ Delete button not found in modal!');
            return;
        }
        
        // Store original text and show loading
        if (!deleteBtn.getAttribute('data-original-text')) {
            deleteBtn.setAttribute('data-original-text', deleteBtn.innerHTML);
        }
        setButtonLoading(deleteBtn, true);

        try {
            const url = `/admin/alumni/${alumniId}/permanent-delete`;
            console.log('📡 Sending permanent delete request to:', url);
            
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();
            console.log('📥 Permanent delete response:', data);
            
            hidePermanentDeleteConfirm();

            if (data.success) {
                showAlert(data.message, 'success');
                // Use the stored ID
                removeAlumniCardWithAnimation(alumniId);
                // Update stats after animation
                setTimeout(() => {
                    updateStats();
                    const activeTab = document.querySelector('.tab-btn.active')?.dataset.tab || 'archived';
                    checkEmptyState('tab-' + activeTab);
                }, 350);
            } else {
                showAlert(data.message || 'Failed to delete account.', 'error');
            }
        } catch (error) {
            console.error('❌ Delete error:', error);
            hidePermanentDeleteConfirm();
            showAlert('An error occurred while deleting the account. Check console for details.', 'error');
        } finally {
            // Reset button state
            if (deleteBtn) {
                setButtonLoading(deleteBtn, false);
            }
        }
    }

    // ========================================
    // DOM MANIPULATION HELPERS
    // ========================================

    // Remove an alumni card from the DOM with animation
    function removeAlumniCard(alumniId, useAnimation = true) {
        console.log('🗑️ Removing card with ID:', alumniId);
        
        // Try to find the card
        const cards = document.querySelectorAll(`.alumni-card[data-id="${alumniId}"]`);
        console.log(`📊 Found ${cards.length} cards to remove`);
        
        if (cards.length === 0) {
            console.warn('⚠️ No cards found with ID:', alumniId);
            // Try to find by a different selector
            const altCards = document.querySelectorAll(`[data-id="${alumniId}"]`);
            console.log(`📊 Found ${altCards.length} elements with data-id="${alumniId}"`);
            
            // If we still can't find it, update stats and check empty state
            if (altCards.length === 0) {
                console.log('🔄 Card not found, assuming already removed');
                updateStats();
                const activeTab = document.querySelector('.tab-btn.active')?.dataset.tab || 'archived';
                checkEmptyState('tab-' + activeTab);
                return;
            } else {
                // We found elements with the data-id, but they might not be alumni cards
                // Try to find the parent alumni-card
                altCards.forEach(el => {
                    const card = el.closest('.alumni-card');
                    if (card) {
                        cards.push(card);
                        console.log('✅ Found card by climbing up from element:', el);
                    }
                });
            }
        }
        
        if (cards.length === 0) {
            console.warn('⚠️ Still no cards found after trying alternatives');
            return;
        }
        
        cards.forEach((card, index) => {
            console.log(`  Card ${index + 1}:`, card);
            if (useAnimation) {
                // Add fade-out animation
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                card.style.height = card.offsetHeight + 'px';
                card.style.overflow = 'hidden';
                
                // Remove after animation completes
                setTimeout(() => {
                    if (card.parentNode) {
                        card.remove();
                        console.log('✅ Card removed from DOM');
                    } else {
                        console.warn('⚠️ Card already removed from DOM');
                    }
                }, 300);
            } else {
                if (card.parentNode) {
                    card.remove();
                    console.log('✅ Card removed from DOM (no animation)');
                }
            }
        });
        
        // Update stats after a short delay
        setTimeout(() => {
            updateStats();
            const activeTab = document.querySelector('.tab-btn.active')?.dataset.tab || 'archived';
            checkEmptyState('tab-' + activeTab);
        }, 400);
    }

    // Keep the old function name for backward compatibility
    function removeAlumniCardWithAnimation(alumniId) {
        removeAlumniCard(alumniId, true);
    }

    // Update the statistics counters
    function updateStats() {
        // Count cards in each tab
        const archivedCount = document.querySelectorAll('#tab-archived .alumni-card').length;
        const restrictedCount = document.querySelectorAll('#tab-restricted .alumni-card').length;
        
        console.log('📊 Stats - Archived:', archivedCount, 'Restricted:', restrictedCount);
        
        // Update stat cards
        const statValues = document.querySelectorAll('.stat-value');
        if (statValues.length >= 2) {
            statValues[0].textContent = archivedCount;
            statValues[1].textContent = restrictedCount;
        }
        
        // Update tab badges
        const archivedBadge = document.querySelector('.tab-btn[data-tab="archived"] .badge');
        const restrictedBadge = document.querySelector('.tab-btn[data-tab="restricted"] .badge');
        
        if (archivedBadge) archivedBadge.textContent = archivedCount;
        if (restrictedBadge) restrictedBadge.textContent = restrictedCount;
    }

    // Check if a tab is empty and show the empty state
    function checkEmptyState(tabId) {
        console.log('🔍 Checking empty state for:', tabId);
        const tab = document.getElementById(tabId);
        if (!tab) {
            console.error('❌ Tab not found:', tabId);
            return;
        }
        
        const cards = tab.querySelectorAll('.alumni-card');
        const emptyState = tab.querySelector('.empty-state');
        
        console.log(`📊 Tab ${tabId} has ${cards.length} cards`);
        
        if (cards.length === 0) {
            // Show empty state if it exists, otherwise create one
            if (emptyState) {
                console.log('✅ Showing existing empty state');
                emptyState.style.display = 'block';
            } else {
                console.log('📝 Creating new empty state');
                // Create empty state if it doesn't exist
                const newEmptyState = document.createElement('div');
                newEmptyState.className = 'empty-state';
                newEmptyState.innerHTML = `
                    <div class="empty-icon"><i class="fa-solid fa-inbox"></i></div>
                    <h3>No accounts here</h3>
                    <p>This section is empty.</p>
                `;
                tab.appendChild(newEmptyState);
            }
        } else if (emptyState) {
            // Hide empty state if there are cards
            console.log('🙈 Hiding empty state');
            emptyState.style.display = 'none';
        }
    }

    // Add loading state to buttons
    function setButtonLoading(button, loading = true) {
        if (loading) {
            button.disabled = true;
            // Store original text if not already stored
            if (!button.getAttribute('data-original-text')) {
                button.setAttribute('data-original-text', button.innerHTML);
            }
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
        } else {
            button.disabled = false;
            // Restore original text
            const originalText = button.getAttribute('data-original-text');
            if (originalText) {
                button.innerHTML = originalText;
            }
        }
    }

    // Handle case where action fails and we need to keep the card
    function handleActionError(alumniId, errorMessage) {
        showAlert(errorMessage, 'error');
        // Re-enable any buttons that might have been disabled
        document.querySelectorAll(`.alumni-card[data-id="${alumniId}"] .btn`).forEach(btn => {
            btn.disabled = false;
        });
    }

    // ========================================
    // MODAL HELPERS
    // ========================================
    function openModal(modalId) {
        console.log('📂 Opening modal:', modalId);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
            modal.removeAttribute('inert');
            document.body.style.overflow = 'hidden';
        } else {
            console.error('❌ Modal not found:', modalId);
        }
    }

    function closeModal(modalId) {
        console.log('📂 Closing modal:', modalId);
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.remove('active');
            modal.setAttribute('inert', '');
            document.body.style.overflow = '';
        }
    }

    // ========================================
    // ALERT TOAST
    // ========================================
    function showAlert(message, type = 'success') {
        console.log('🔔 Showing alert:', message, type);
        const toast = document.getElementById('alertToast');
        if (!toast) {
            console.error('❌ Alert toast not found!');
            return;
        }
        
        const icon = toast.querySelector('.alert-icon');
        const msg = toast.querySelector('.alert-message');
        
        const icons = {
            success: 'fa-circle-check',
            error: 'fa-circle-exclamation',
            info: 'fa-circle-info',
            warning: 'fa-circle-exclamation'
        };
        const colors = {
            success: 'var(--success)',
            error: 'var(--danger)',
            info: 'var(--info)',
            warning: 'var(--warning)'
        };
        
        if (icon) {
            icon.className = `alert-icon fa-solid ${icons[type] || icons.success}`;
        }
        toast.style.borderColor = colors[type] || colors.success;
        if (msg) {
            msg.textContent = message;
        }
        
        toast.classList.add('show');
        
        setTimeout(() => hideAlert(), 4000);
    }

    function hideAlert() {
        const toast = document.getElementById('alertToast');
        if (toast) {
            toast.classList.remove('show');
        }
    }

    // ========================================
    // CLOSE MODALS ON ESCAPE
    // ========================================
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideRestoreConfirm();
            hideUnrestrictConfirm();
            hidePermanentDeleteConfirm();
            hideAlert();
        }
    });

    // ========================================
    // CLOSE MODALS ON OVERLAY CLICK
    // ========================================
    document.getElementById('restoreConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideRestoreConfirm();
    });

    document.getElementById('unrestrictConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideUnrestrictConfirm();
    });

    document.getElementById('permanentDeleteConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hidePermanentDeleteConfirm();
    });

    // ========================================
    // DOM CONTENT LOADED - Initialize
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 DOM fully loaded');
        
        // Check if URL has #tab-restricted or #tab-archived
        const hash = window.location.hash;
        if (hash === '#tab-restricted') {
            switchTab('restricted');
        } else if (hash === '#tab-archived') {
            switchTab('archived');
        }
        
        // Remove the query parameter from URL after page loads
        if (window.location.search.includes('reload=')) {
            const cleanUrl = window.location.pathname + window.location.hash;
            window.history.replaceState({}, document.title, cleanUrl);
        }
        
        // Verify that all elements exist
        console.log('📋 Checking elements:');
        console.log('  - restoreConfirmModal:', document.getElementById('restoreConfirmModal') ? '✅' : '❌');
        console.log('  - unrestrictConfirmModal:', document.getElementById('unrestrictConfirmModal') ? '✅' : '❌');
        console.log('  - permanentDeleteConfirmModal:', document.getElementById('permanentDeleteConfirmModal') ? '✅' : '❌');
        console.log('  - alertToast:', document.getElementById('alertToast') ? '✅' : '❌');
        
        // Count cards
        const archivedCards = document.querySelectorAll('#tab-archived .alumni-card').length;
        const restrictedCards = document.querySelectorAll('#tab-restricted .alumni-card').length;
        console.log(`📊 Card counts - Archived: ${archivedCards}, Restricted: ${restrictedCards}`);
    });
</script>

</body>
</html>

{{-- This is archived.blade.php --}}