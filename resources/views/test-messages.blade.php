<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Messages | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/test_messages.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <!-- Emoji Picker -->
    <script type="module" src="https://cdn.jsdelivr.net/npm/emoji-picker-element@1.21.0/index.js"></script>
    <!-- Supabase JS Client -->
    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>
    
    <style>
        .admin-main {
            margin-left: var(--sidebar-width);
            height: calc(100vh - 73px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        
        .messages-wrapper {
            flex: 1;
            display: flex;
            min-height: 0;
            overflow: hidden;
        }
        
        .contacts-panel {
            height: 100%;
            display: flex;
            flex-direction: column;
        }
        
        .contacts-list {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
        }
        
        .chat-panel {
            flex: 1;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
        }
        
        .chat-messages-area {
            flex: 1;
            overflow-y: auto;
            min-height: 0;
            padding: 20px;
        }
        
        .chat-input-container {
            flex-shrink: 0;
            margin-top: auto;
        }
        
        .loading-spinner {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 40px;
            color: #6b7280;
            gap: 8px;
        }
        
        .empty-state {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            color: #9ca3af;
            text-align: center;
            padding: 40px;
        }
        
        .empty-state i {
            font-size: 64px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        
        .empty-state h3 {
            font-size: 18px;
            margin-bottom: 8px;
            color: #6b7280;
        }
        
        .contact-avatar-img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .contact-card {
            cursor: pointer;
        }
        
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            display: none;
            justify-content: center;
            align-items: center;
        }
        
        .modal-overlay.active {
            display: flex;
        }
        
        .modal {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 500px;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            font-size: 18px;
        }
        
        .modal-body {
            padding: 20px;
            overflow-y: auto;
            flex: 1;
        }
        
        .search-results .alumni-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.2s;
        }
        
        .search-results .alumni-item:hover {
            background: #f3f4f6;
        }
        
        .alumni-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #3b82f6;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 12px;
            flex-shrink: 0;
        }
        
        .alumni-info {
            flex: 1;
            margin-left: 13px;
        }
        
        .alumni-info .name {
            font-weight: 500;
            color: #1f2937;
        }
        
        .alumni-info .details {
            font-size: 12px;
            color: #6b7280;
        }
        
        .online-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            margin-left: 8px;
            flex-shrink: 0;
        }


        /* ========================================
   ENHANCED MOBILE RESPONSIVE STYLES
   ======================================== */

/* Mobile Back Button for Chat Panel */
.mobile-back-btn {
    display: none;
    width: 36px;
    height: 36px;
    border: none;
    background: var(--gray-100);
    border-radius: var(--radius-lg);
    color: var(--nu-blue);
    cursor: pointer;
    font-size: 1rem;
    align-items: center;
    justify-content: center;
    margin-right: 0.75rem;
    flex-shrink: 0;
    transition: all var(--transition);
}
.mobile-back-btn:hover {
    background: var(--nu-blue);
    color: var(--white);
}

/* Mobile Header Adjustments */
@media (max-width: 1024px) {
    .mobile-back-btn {
        display: flex;
    }
    
    .admin-main {
        padding-top: 4rem; /* Space for mobile menu toggle */
    }
    
    .messages-wrapper {
        flex-direction: column;
        position: relative;
    }
    
    /* Contacts Panel - Full Screen on Mobile When No Chat Selected */
    .contacts-panel {
        width: 100% !important;
        min-width: 100% !important;
        height: 100%;
        border-right: none;
        transition: transform var(--transition-slow);
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1;
    }
    
    /* Hide contacts when chat is active */
    .contacts-panel.chat-active {
        transform: translateX(-100%);
    }
    
    /* Chat Panel - Full Screen on Mobile */
    .chat-panel {
        height: 100%;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 2;
        transform: translateX(100%);
        transition: transform var(--transition-slow);
    }
    
    .chat-panel.chat-active {
        transform: translateX(0);
    }
    
    /* Show empty state centered on mobile */
    .chat-panel .empty-state {
        padding: 2rem;
    }
    
    /* Adjust message bubbles for mobile */
    .message-bubble {
        max-width: 80%;
    }
    
    /* Chat header adjustments */
    .chat-header {
        padding: 0.75rem 1rem;
        gap: 0.5rem;
    }
    
    .chat-user-info {
        gap: 0.5rem;
    }
    
    .chat-user-info .contact-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
    
    .user-meta h3 {
        font-size: 0.95rem;
    }
    
    .user-status {
        font-size: 0.7rem;
    }
    
    /* Chat input adjustments */
    .chat-input-container {
        padding: 0.75rem 1rem;
        gap: 0.5rem;
    }
    
    .input-wrapper {
        padding: 0.25rem 0.375rem 0.25rem 1rem;
    }
    
    .input-wrapper input {
        font-size: 0.875rem;
        padding: 0.625rem 0;
    }
    
    .btn-send {
        width: 42px;
        height: 42px;
        font-size: 1rem;
    }
    
    .btn-attach, .btn-emoji {
        font-size: 1.1rem;
        padding: 0.375rem;
    }
    
    /* Hide some chat actions on mobile */
    .chat-actions .btn-icon:first-child {
        display: none;
    }
}

/* Small Mobile Devices */
@media (max-width: 640px) {
    .admin-main {
        padding-top: 3.5rem;
    }
    
    .panel-header {
        padding: 0.875rem 1rem;
    }
    
    .panel-header h2 {
        font-size: 1.1rem;
    }
    
    .filter-tabs {
        padding: 0.5rem 0.75rem;
        gap: 0.375rem;
    }
    
    .tab-btn {
        font-size: 0.7rem;
        padding: 0.5rem 0.375rem;
    }
    
    .contact-card {
        padding: 0.75rem 0.5rem;
        gap: 0.625rem;
    }
    
    .contact-avatar {
        width: 40px;
        height: 40px;
        font-size: 0.95rem;
    }
    
    .contact-name {
        font-size: 0.825rem;
    }
    
    .contact-preview, .contact-batch {
        font-size: 0.7rem;
    }
    
    .chat-messages-area {
        padding: 1rem 0.75rem;
        gap: 0.5rem;
    }
    
    .message-bubble {
        max-width: 85%;
        padding: 0.75rem 0.875rem;
    }
    
    .message-bubble p {
        font-size: 0.85rem;
    }
    
    .msg-time {
        font-size: 0.6rem;
    }
    
    .date-divider span {
        font-size: 0.7rem;
        padding: 0.25rem 0.75rem;
    }
    
    /* Make send button slightly smaller */
    .btn-send {
        width: 38px;
        height: 38px;
    }
    
    /* Modal adjustments */
    .modal {
        width: 95%;
        max-height: 85vh;
        border-radius: var(--radius-xl);
    }
    
    .modal-header {
        padding: 1rem;
    }
    
    .modal-header h3 {
        font-size: 1rem;
    }
    
    .modal-body {
        padding: 1rem;
    }
}

/* Very Small Devices */
@media (max-width: 380px) {
    .panel-header {
        padding: 0.75rem;
    }
    
    .filter-tabs {
        padding: 0.375rem 0.5rem;
        gap: 0.25rem;
    }
    
    .tab-btn {
        font-size: 0.65rem;
        padding: 0.4rem 0.25rem;
    }
    
    .contact-card {
        padding: 0.625rem 0.375rem;
    }
    
    .contact-avatar {
        width: 36px;
        height: 36px;
        font-size: 0.85rem;
    }
    
    .message-bubble {
        max-width: 90%;
    }
}

/* Touch-friendly improvements */
@media (hover: none) and (pointer: coarse) {
    /* Increase tap targets for touch devices */
    .contact-card {
        min-height: 56px;
    }
    
    .btn-icon, .btn-send, .btn-attach, .btn-emoji {
        min-width: 44px;
        min-height: 44px;
    }
    
    .tab-btn {
        min-height: 36px;
    }
    
    /* Add active state for touch feedback */
    .contact-card:active {
        background: var(--gray-100);
    }
    
    .btn-send:active {
        transform: scale(0.9);
    }
}

/* Archived warning styles */
.archived-warning {
    background: #fef3c7 !important;
    border: 1px solid #f59e0b !important;
    border-radius: 8px !important;
    padding: 12px 16px !important;
    margin-bottom: 16px !important;
    display: flex !important;
    align-items: center !important;
    gap: 10px !important;
    color: #92400e !important;
    font-size: 14px !important;
}

.archived-warning i {
    font-size: 18px !important;
    color: #f59e0b !important;
}

.archived-warning button {
    background: none !important;
    border: none !important;
    color: #f59e0b !important;
    font-weight: 600 !important;
    cursor: pointer !important;
    text-decoration: underline !important;
    padding: 0 !important;
}

.archived-warning button:hover {
    color: #d97706 !important;
}

/* Ensure icons are visible */
.btn-icon i {
    font-size: 1rem;
    line-height: 1;
}

/* Fix for the new group button specifically */
#newGroupBtn {
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
}

#newGroupBtn i {
    font-size: 1.1rem;
}

/* Fix for empty state create channel button */
.contacts-list .empty-state .btn-primary {
    display: inline-flex !important;
    align-items: center;
    gap: 0.5rem;
}

.contacts-list .empty-state .btn-primary i {
    /* We removed the display: inline-block !important */
    font-size: 0.9rem;
    color: #FFFFFF !important; /* Forces pure white */
    display: flex !important;  /* Forces it to center */
    align-items: center !important;
    justify-content: center !important;
}

/* Ensure Channels tab shows properly */
@media (max-width: 480px) {
    .tab-btn {
        font-size: 0.65rem;
        padding: 0.5rem 0.25rem;
        gap: 0.25rem;
    }
    
    .tab-btn i {
        font-size: 0.7rem;
    }
    
    .tab-btn .badge {
        font-size: 0.55rem;
        padding: 0.125rem 0.375rem;
        min-width: 16px;
    }
}

/* Make the empty state create channel button more visible */
.contacts-list .empty-state .btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(50, 65, 140, 0.3);
}

    </style>
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

            <!-- Messages Interface -->
            <div class="messages-wrapper">
                
                <!-- Left Panel: Contacts & Search -->
                <aside class="contacts-panel">
                    <div class="panel-header">
                        <h2 id="panelTitle">Messages</h2>
                        <div style="display: flex; gap: 8px;">
                            <button class="btn-icon" id="newGroupBtn" title="New Channel" onclick="openNewGroupModal()">
                                <i class="fa-solid fa-user-group"></i>
                            </button>
                            <button class="btn-icon" id="archiveToggleBtn" title="Archived Chats" onclick="toggleArchiveView()">
                                <i class="fa-solid fa-box-archive"></i>
                            </button>
                            <button class="btn-icon" id="newMessageBtn" title="New Message" onclick="openNewMessageModal()">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </button>
                        </div>
                    </div>
                    <div class="search-container">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="searchContacts" placeholder="Search alumni by name..." oninput="handleSearchInput()">
                        <button class="search-clear-btn" id="searchClearBtn" onclick="clearSearch()" style="display: none;" title="Clear search">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <div class="filter-tabs">
                        <button class="tab-btn active" onclick="filterByTab('all', this)">All Chats</button>
                        <button class="tab-btn" onclick="filterByTab('unread', this)">Unread <span class="badge" id="unreadBadge">0</span></button>
                        <button class="tab-btn" onclick="filterByTab('groups', this)"><i class="fa-solid fa-users"></i> Channels</button>
                    </div>

                    <div class="contacts-list" id="contactsList">
                        <div class="loading-spinner">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading conversations...
                        </div>
                    </div>
                </aside>

                <!-- Right Panel: Active Chat -->
                <main class="chat-panel" id="chatPanel">
                    <div class="empty-state" id="noChatSelected">
                        <i class="fa-solid fa-comments"></i>
                        <h3>Select a conversation</h3>
                        <p>Choose an alumni from the left panel to start messaging</p>
                    </div>

                    <!-- Chat Header -->
                    <div class="chat-header" id="chatHeader" style="display: none;">
                        <div class="chat-user-info">
                            <button class="mobile-back-btn" onclick="showContactsOnMobile()" title="Back to conversations">
                                <i class="fa-solid fa-arrow-left"></i>
                            </button>
                            <div class="contact-avatar large" id="chatAvatar">--</div>
                            <div class="user-meta">
                                <h3 id="chatName">--</h3>
                                <span class="user-status" id="chatStatus">
                                    <span class="status-dot"></span> Offline
                                </span>
                            </div>
                        </div>
                        <div class="chat-actions">
                            <button class="btn-icon" title="Search chat"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <button class="btn-icon" title="More options"><i class="fa-solid fa-ellipsis-vertical"></i></button>
                        </div>
                    </div>

                    <!-- Chat Messages Area -->
                    <div class="chat-messages-area" id="chatMessages" style="display: none;"></div>

                    <!-- File Preview Area -->
                    <div class="file-preview-container" id="filePreviewContainer">
                        <div class="file-preview-list" id="filePreviewList"></div>
                    </div>

                    <!-- Chat Input Area -->
                    <div class="chat-input-container" id="chatInput" style="display: none;">
                        <button class="btn-attach" title="Attach file" onclick="document.getElementById('fileInput').click()">
                            <i class="fa-solid fa-paperclip"></i>
                        </button>
                        <input type="file" id="fileInput" style="display: none;" accept="image/*,video/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.csv,.zip" multiple onchange="handleFileSelect(event)">
                        <div class="input-wrapper">
                            <input type="text" id="messageInput" placeholder="Type a message here..." onkeypress="handleKeyPress(event)">
                                <div class="emoji-picker-container">
                                    <button class="btn-emoji" title="Add emoji" onclick="toggleEmojiPicker(event)">
                                        <i class="fa-regular fa-face-smile"></i>
                                    </button>
                                    <div class="emoji-picker-popup" id="emojiPickerPopup">
                                        <emoji-picker></emoji-picker>
                                    </div>
                                </div>
                        </div>
                        <button class="btn-send" title="Send message" onclick="sendMessage()">
                            <i class="fa-solid fa-paper-plane"></i>
                        </button>
                    </div>
                </main>
            </div>
        </main>
    </div>

    <!-- New Message Modal -->
    <div class="modal-overlay" id="newMessageModal">
        <div class="modal">
            <div class="modal-header">
                <h3>New Message</h3>
                <button class="btn-icon" onclick="closeNewMessageModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="search-container" style="margin-bottom: 15px;">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="alumniSearch" placeholder="Search alumni by name, ID, or program..." oninput="searchAlumni()">
                </div>
                <div class="search-results" id="searchResults">
                    <p style="color: #9ca3af; text-align: center;">Start typing to search for alumni</p>
                </div>
            </div>
        </div>
    </div>

    <!-- New Group/Channel Modal -->
    <div class="modal-overlay" id="newGroupModal">
        <div class="modal" style="max-width: 600px;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-users" style="margin-right: 8px;"></i>Create New Channel</h3>
                <button class="btn-icon" onclick="closeNewGroupModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body">
                <!-- Group Name -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.375rem;">
                        Channel Name <span style="color: var(--danger);">*</span>
                    </label>
                    <input type="text" id="groupNameInput" placeholder="Enter channel name..." 
                        style="width: 100%; padding: 0.75rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-family: inherit; font-size: 0.875rem; transition: all var(--transition);">
                </div>
                
                <!-- Group Avatar -->
                <div class="form-group" style="margin-bottom: 1rem;">
                    <label style="display: block; font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.375rem;">
                        Channel Avatar
                    </label>
                    <div class="avatar-upload" style="display: flex; align-items: center; gap: 1rem;">
                        <div id="groupAvatarPreview" style="width: 64px; height: 64px; border-radius: 50%; background: linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light)); color: var(--nu-gold); display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 700; overflow: hidden; flex-shrink: 0;">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <button class="btn-secondary" onclick="document.getElementById('groupAvatarInput').click()" 
                            style="padding: 0.5rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-size: 0.875rem; transition: all var(--transition);">
                            <i class="fa-solid fa-camera"></i> Upload
                        </button>
                        <input type="file" id="groupAvatarInput" accept="image/*" style="display: none;" onchange="handleGroupAvatarSelect(event)">
                    </div>
                </div>
                
                <!-- Member Search & Selection -->
                <div class="form-group">
                    <label style="display: block; font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.375rem;">
                        Add Members <span style="color: var(--danger);">*</span>
                        <span style="font-weight: 400; color: var(--gray-500); font-size: 0.75rem;">(Minimum 2)</span>
                    </label>
                    <div class="search-container" style="padding: 0; border-bottom: none;">
                        <i class="fa-solid fa-magnifying-glass" style="left: 1rem !important;"></i>
                        <input type="text" id="groupMemberSearch" placeholder="Search alumni by name, ID, or program..." 
                            oninput="searchAlumniForGroup()"
                            style="padding-left: 2.5rem !important;">
                    </div>
                    <div id="selectedMembers" class="selected-members" 
                        style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.5rem; min-height: 44px; border: 2px dashed var(--gray-300); border-radius: var(--radius-lg); margin-top: 0.5rem; background: var(--gray-50);">
                        <span style="color: var(--gray-400); font-size: 0.8rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>
                    </div>
                    <div id="groupMemberResults" class="search-results" style="max-height: 200px; overflow-y: auto; margin-top: 0.5rem;"></div>
                </div>
                
                <div class="form-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                    <button class="btn-secondary" onclick="closeNewGroupModal()" 
                        style="padding: 0.625rem 1.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.875rem; transition: all var(--transition);">
                        Cancel
                    </button>
                    <button class="btn-primary" id="createGroupBtn" onclick="createGroupChat()" style="margin-top: 1rem; padding: 0.625rem 1.5rem; border: none; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--nu-blue) 0%, var(--nu-blue-light) 100%); color: var(--white); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.875rem; box-shadow: var(--shadow-blue); transition: all var(--transition); display: inline-flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-plus" style="color: white; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center;"></i> 
                        Create Channel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Group Info Modal -->
    <div class="modal-overlay" id="groupInfoModal">
        <div class="modal" style="max-width: 480px; max-height: 80vh;">
            <div class="modal-header">
                <h3><i class="fa-solid fa-info-circle" style="margin-right: 8px;"></i>Channel Info</h3>
                <button class="btn-icon" onclick="closeGroupInfoModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" style="overflow-y: auto;">
                <!-- Group Info Header -->
                <div class="group-info-header" style="text-align: center; margin-bottom: 1.5rem;">
                    <div class="group-info-avatar" id="groupInfoAvatar" 
                        style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light)); color: var(--nu-gold); margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; overflow: hidden;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 id="groupInfoName" style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--gray-800);">Channel Name</h3>
                    <p id="groupInfoMemberCount" style="color: var(--gray-500); font-size: 0.875rem; margin: 0.25rem 0 0;">0 members</p>
                </div>
                
                <!-- Admin Actions -->
                <div id="groupAdminActions" class="group-info-actions" style="display: none; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem;">
                    <button class="btn-secondary" onclick="editGroupName()" style="flex: 1; padding: 0.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-size: 0.8rem; font-weight: 600; transition: all var(--transition);">
                        <i class="fa-solid fa-pen"></i> Edit Name
                    </button>
                    <button class="btn-secondary" onclick="document.getElementById('groupAvatarEditInput').click()" style="flex: 1; padding: 0.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-size: 0.8rem; font-weight: 600; transition: all var(--transition);">
                        <i class="fa-solid fa-camera"></i> Change Avatar
                    </button>
                    <input type="file" id="groupAvatarEditInput" accept="image/*" style="display: none;" onchange="handleGroupAvatarEdit(event)">
                </div>
                
                <!-- Members Section -->
                <div class="group-members-section">
                    <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <h4 style="margin: 0; font-size: 0.95rem; font-weight: 600; color: var(--gray-700);">
                            <i class="fa-solid fa-user-group"></i> Members
                        </h4>
                        <button id="addMembersBtn" class="btn-icon" onclick="openAddMembersModal()" title="Add members" style="display: none; width: 32px; height: 32px; font-size: 0.8rem;">
                            <i class="fa-solid fa-user-plus"></i>
                        </button>
                    </div>
                    <div id="groupMembersList" class="group-members-list" style="max-height: 300px; overflow-y: auto;">
                        <!-- Members rendered by JavaScript -->
                    </div>
                </div>
                
                <!-- Leave Button -->
                <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                    <button class="btn-danger" onclick="leaveGroup()" 
                        style="width: 100%; padding: 0.75rem; border: none; border-radius: var(--radius-lg); background: var(--danger); color: var(--white); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.875rem; transition: all var(--transition);">
                        <i class="fa-solid fa-right-from-bracket"></i> Leave Channel
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Dropdown Backdrop -->
    <div class="dropdown-backdrop" id="dropdownBackdrop" onclick="closeAllDropdowns()"></div>

<script>
    // ============================================
    // GLOBAL STATE
    // ============================================
    const adminId = {{ $admin->id ?? 0 }};
    let currentChat = null;
    let allContacts = [];
    let allGroups = [];
    let activeTab = 'all';
    let supabaseClient;
    let searchTimeout;
    let supabaseRealtimeChannel;
    let pollingInterval;
    let lastMessageId = 0;
    let isDecrypting = false;
    let conversationsLoaded = false;
    let archiveMode = false;
    let typingChannel;
    let presenceChannel;
    let typingTimeout;
    let typingIndicatorTimeout;
    const TYPING_TIMEOUT = 3000;
    
    // ============================================
    // SUPABASE INITIALIZATION
    // ============================================
    function initSupabase() {
        const supabaseUrl = '{{ env("SUPABASE_URL") }}';
        const supabaseKey = '{{ env("SUPABASE_KEY") }}';
        
        if (!supabaseUrl || !supabaseKey) {
            console.warn('Supabase credentials not configured - falling back to polling');
            startPolling();
            return;
        }
        
        supabaseClient = window.supabase.createClient(supabaseUrl, supabaseKey);
        
        // ============================================
        // REALTIME: Message changes
        // ============================================
        supabaseRealtimeChannel = supabaseClient
            .channel('admin-messages-' + adminId)
            .on('postgres_changes', {
                event: 'INSERT',
                schema: 'public',
                table: 'messages',
                filter: `receiver_id=eq.${adminId}`,
            }, (payload) => {
                console.log('📨 New incoming message:', payload.new);
                handleIncomingMessage(payload.new);
            })
            .on('postgres_changes', {
                event: 'INSERT',
                schema: 'public',
                table: 'messages',
                filter: `sender_id=eq.${adminId}`,
            }, (payload) => {
                console.log('📤 Message sent (from another session):', payload.new);
                handleOutgoingMessageFromOtherSession(payload.new);
            })
            .subscribe((status) => {
                if (status === 'SUBSCRIBED') {
                    console.log('✅ Supabase Realtime connected');
                } else if (status === 'CHANNEL_ERROR') {
                    console.error('❌ Supabase Realtime error - falling back to polling');
                    startPolling();
                }
            });
        
        // ============================================
        // BROADCAST: Typing indicators
        // ============================================
        typingChannel = supabaseClient.channel('typing-indicators', {
            config: {
                broadcast: { self: false } // Don't receive own broadcasts
            }
        });
        
        typingChannel.on('broadcast', { event: 'typing' }, (payload) => {
            handleTypingEvent(payload.payload);
        }).subscribe((status) => {
            if (status === 'SUBSCRIBED') {
                console.log('✅ Typing channel connected');
            }
        });
        
        // ============================================
        // PRESENCE: Online status tracking
        // ============================================
        initPresence();
        
        // ============================================
        // REALTIME: Alumni online status changes
        // ============================================
        supabaseClient
            .channel('alumni-presence')
            .on('postgres_changes', {
                event: 'UPDATE',
                schema: 'public',
                table: 'alumnis',
            }, (payload) => {
                handleAlumniPresenceChange(payload.new, payload.old);
            })
            .subscribe((status) => {
                if (status === 'SUBSCRIBED') {
                    console.log('✅ Alumni presence tracking connected');
                }
            });
    }

    // ============================================
    // PRESENCE: Track admin online status
    // ============================================
    async function initPresence() {
        presenceChannel = supabaseClient.channel('admin-presence', {
            config: {
                presence: {
                    key: `admin-${adminId}`,
                },
            },
        });
        
        presenceChannel.on('presence', { event: 'sync' }, () => {
            updateAdminPresenceList();
        });
        
        presenceChannel.on('presence', { event: 'join' }, ({ key, newPresences }) => {
            console.log('🟢 Admin joined:', key);
            updateAdminPresenceList();
        });
        
        presenceChannel.on('presence', { event: 'leave' }, ({ key, leftPresences }) => {
            console.log('🔴 Admin left:', key);
            updateAdminPresenceList();
        });
        
        presenceChannel.subscribe(async (status) => {
            if (status === 'SUBSCRIBED') {
                // Track this admin as online
                const status = await presenceChannel.track({
                    admin_id: adminId,
                    online_at: new Date().toISOString(),
                });
                console.log('✅ Presence tracking active');
            }
        });
    }

    // ============================================
    // PRESENCE: Update admin online status in UI
    // ============================================
    function updateAdminPresenceList() {
        if (!presenceChannel) return;
        
        const state = presenceChannel.presenceState();
        const onlineAdminIds = new Set();
        
        // Collect all online admin IDs
        Object.values(state).forEach(presences => {
            presences.forEach(presence => {
                if (presence.admin_id) {
                    onlineAdminIds.add(presence.admin_id);
                }
            });
        });
        
        console.log('🟢 Online admins:', Array.from(onlineAdminIds));
        
        // Update contacts list
        allContacts.forEach(contact => {
            if (contact.type === 'admin') {
                const wasOnline = contact.is_online;
                contact.is_online = onlineAdminIds.has(parseInt(contact.id));
                
                // Only re-render if changed
                if (wasOnline !== contact.is_online) {
                    applyFilter();
                }
            }
        });
        
        // Update current chat if it's an admin
        if (currentChat && currentChat.type === 'admin') {
            const isOnline = onlineAdminIds.has(parseInt(currentChat.id));
            document.getElementById('chatStatus').innerHTML = `
                <span class="status-dot ${isOnline ? 'online' : ''}"></span> 
                ${isOnline ? 'Online' : 'Offline'}
            `;
        }
        
        // Update unread badge
        updateUnreadBadge();
    }

    // ============================================
    // HANDLE: Alumni presence changes from DB
    // ============================================
    function handleAlumniPresenceChange(newData, oldData) {
        // Only process if is_online changed
        if (!oldData || newData.is_online === oldData.is_online) return;
        
        const alumniId = parseInt(newData.id);
        const isOnline = newData.is_online === true || newData.is_online === 'true';
        
        console.log(`🔔 Alumni ${alumniId} is now ${isOnline ? 'online' : 'offline'}`);
        
        // Update in contacts list
        const contact = allContacts.find(c => c.id === alumniId && c.type === 'alumni');
        if (contact) {
            contact.is_online = isOnline;
            applyFilter();
        }
        
        // Update current chat if this is the active alumni
        if (currentChat && currentChat.id == alumniId && currentChat.type === 'alumni') {
            document.getElementById('chatStatus').innerHTML = `
                <span class="status-dot ${isOnline ? 'online' : ''}"></span> 
                ${isOnline ? 'Online' : 'Offline'}
            `;
        }
        
        // Update unread badge
        updateUnreadBadge();
    }

    // ============================================
    // TYPING: Broadcast typing event
    // ============================================
    function broadcastTyping() {
        if (!currentChat || !typingChannel) return;
        
        typingChannel.send({
            type: 'broadcast',
            event: 'typing',
            payload: {
                sender_id: adminId,
                sender_type: 'admin',
                receiver_id: currentChat.id,
                receiver_type: currentChat.type,
                timestamp: new Date().toISOString(),
            },
        });
    }

    // ============================================
    // TYPING: Handle incoming typing event
    // ============================================
    function handleTypingEvent(data) {
        // Only process typing events for the current chat
        if (!currentChat) return;
        
        if (
            data.sender_id == currentChat.id && 
            data.sender_type === currentChat.type &&
            data.receiver_id == adminId &&
            data.receiver_type === 'admin'
        ) {
            showTypingIndicator();
        }
    }

    // ============================================
    // TYPING: Show typing indicator in chat header
    // ============================================
    function showTypingIndicator() {
        const statusEl = document.getElementById('chatStatus');
        
        // Update to show typing
        statusEl.innerHTML = `
            <span class="typing-indicator">
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
                <span class="typing-dot"></span>
            </span>
            <span class="typing-text">typing...</span>
        `;
        
        // Auto-hide after timeout
        clearTimeout(typingIndicatorTimeout);
        typingIndicatorTimeout = setTimeout(() => {
            hideTypingIndicator();
        }, TYPING_TIMEOUT);
    }

    // ============================================
    // TYPING: Hide typing indicator
    // ============================================
    function hideTypingIndicator() {
        if (!currentChat) return;
        
        const contact = allContacts.find(c => c.id == currentChat.id && c.type === currentChat.type);
        const isOnline = contact ? contact.is_online : false;
        
        document.getElementById('chatStatus').innerHTML = `
            <span class="status-dot ${isOnline ? 'online' : ''}"></span> 
            ${isOnline ? 'Online' : 'Offline'}
        `;
    }

    // ============================================
    // TYPING: Debounced typing broadcast
    // ============================================
    function onMessageInput() {
        // Debounce typing broadcast (every 2 seconds)
        clearTimeout(typingTimeout);
        typingTimeout = setTimeout(() => {
            broadcastTyping();
        }, 500); // Send typing event after 500ms of typing
        
        // Also reset the typing indicator timeout for the recipient
        // (This is handled on their end via the broadcast)
    }


    // ============================================
    // MESSAGE HANDLERS
    // ============================================
    async function handleIncomingMessage(message) {
        if (message.receiver_id != adminId || message.receiver_type !== 'admin') return;
        
        // 🔧 FIX: Ensure the created_at is treated as UTC
        const utcTimestamp = ensureUTCTimestamp(message.created_at);
        
        // Update contacts panel immediately
        updateContactWithNewMessage(
            message.sender_id, 
            message.sender_type || 'alumni', 
            message.content, 
            utcTimestamp, // 🔧 Use the fixed UTC timestamp
            true
        );
        
        if (currentChat && message.sender_id == currentChat.id && message.sender_type === currentChat.type) {
            const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
            if (existingMsg) {
                console.log('⚠️ Duplicate message prevented:', message.id);
                await markMessagesAsRead(currentChat.id, currentChat.type);
                return;
            }
            
            const decryptedContent = await decryptContent(
                message.content, 
                message.sender_type, 
                message.receiver_type
            );
            
            const decryptedMessage = {
                id: message.id,
                content: decryptedContent,
                sender_id: message.sender_id,
                sender_type: message.sender_type,
                receiver_id: message.receiver_id,
                receiver_type: message.receiver_type,
                is_read: message.is_read,
                created_at: utcTimestamp, // 🔧 Use the fixed UTC timestamp
                time: formatTime(new Date(utcTimestamp)), // 🔧 Proper local time
                attachments: []
            };
            
            appendMessage(decryptedMessage);
            scrollToBottom();
            await markMessagesAsRead(currentChat.id, currentChat.type);
            lastMessageId = Math.max(lastMessageId, message.id);
        }
        
        refreshConversationsInBackground();
    }

    function handleOutgoingMessageFromOtherSession(message) {
        if (message.sender_id != adminId || message.sender_type !== 'admin') return;
        
        // 🔧 FIX: Ensure the created_at is treated as UTC
        const utcTimestamp = ensureUTCTimestamp(message.created_at);
        
        updateContactWithNewMessage(
            message.receiver_id, 
            message.receiver_type || 'alumni', 
            message.content, 
            utcTimestamp, // 🔧 Use the fixed UTC timestamp
            false
        );
        
        if (currentChat && message.receiver_id == currentChat.id && message.receiver_type === currentChat.type) {
            const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
            if (existingMsg) {
                console.log('⚠️ Duplicate outgoing message prevented:', message.id);
                return;
            }
            
            appendMessage({
                id: message.id,
                content: message.content,
                sender_id: message.sender_id,
                sender_type: message.sender_type,
                receiver_id: message.receiver_id,
                receiver_type: message.receiver_type,
                is_read: message.is_read,
                created_at: utcTimestamp, // 🔧 Use the fixed UTC timestamp
                time: formatTime(new Date(utcTimestamp)), // 🔧 Proper local time
                attachments: []
            });
            scrollToBottom();
            lastMessageId = Math.max(lastMessageId, message.id);
        }
    }

    // ============================================
    // 🔧 NEW: Helper function to ensure UTC timestamps
    // ============================================
    function ensureUTCTimestamp(timestamp) {
        if (!timestamp) return new Date().toISOString();
        
        // If it's already an ISO string with Z or + timezone, return as-is
        if (typeof timestamp === 'string') {
            // Already has timezone indicator (Z, +08:00, etc.)
            if (timestamp.endsWith('Z') || timestamp.includes('+') || timestamp.includes('-', 10)) {
                return timestamp;
            }
            
            // Missing timezone - assume UTC by appending Z
            // Supabase sends timestamps without timezone, so we must add it
            if (timestamp.includes('T')) {
                // Format: "2026-07-17T08:00:00" -> "2026-07-17T08:00:00Z"
                return timestamp + 'Z';
            }
            
            // If it's just a date string, create a proper UTC ISO string
            const date = new Date(timestamp + 'Z'); // Force UTC interpretation
            return date.toISOString();
        }
        
        // If it's already a Date object or number, convert to ISO string
        return new Date(timestamp).toISOString();
    }

    // ============================================
    // NEW: UPDATE CONTACT IN PANEL WITHOUT FULL RELOAD
    // ============================================
    function updateContactWithNewMessage(contactId, contactType, content, timestamp, isUnread = false) {
        let contact = allContacts.find(c => c.id == contactId && c.type === contactType);
        
        if (!contact) {
            console.log('📋 New contact detected, fetching info...');
            fetchContactInfo(contactId, contactType, content, timestamp, isUnread);
            return;
        }
        
        // 🔧 Ensure timestamp is proper UTC ISO string
        const utcTimestamp = ensureUTCTimestamp(timestamp);
        const messageDate = new Date(utcTimestamp);
        
        contact.last_message = content;
        contact.last_message_time = formatTime(messageDate);
        contact.last_message_timestamp = utcTimestamp; // Store the fixed UTC string
        contact.last_message_from_me = !isUnread;
        
        if (isUnread && (!currentChat || currentChat.id != contactId || currentChat.type !== contactType)) {
            contact.unread_count = (contact.unread_count || 0) + 1;
        }
        
        // Move this contact to the top of the list
        allContacts = allContacts.filter(c => !(c.id == contactId && c.type === contactType));
        allContacts.unshift(contact);
        
        applyFilter();
    }

    async function fetchContactInfo(contactId, contactType, content, timestamp, isUnread = false) {
        try {
            const response = await fetch(`/admin/messages/${contactType}/${contactId}/info`);
            
            if (response.ok) {
                const data = await response.json();
                
                // 🔧 Fix UTC timestamp
                const utcTimestamp = ensureUTCTimestamp(timestamp);
                
                // ✅ Also fetch DM settings to get archive status
                let isArchived = false;
                let isMuted = false;
                try {
                    const settingsResponse = await fetch(`/admin/messages/dm-settings?contact_id=${contactId}&contact_type=${contactType}`);
                    if (settingsResponse.ok) {
                        const settingsData = await settingsResponse.json();
                        isArchived = settingsData.is_archived || false;
                        isMuted = settingsData.is_muted || false;
                    }
                } catch (e) {
                    console.warn('Could not fetch DM settings:', e);
                }
                
                const newContact = {
                    id: data.id,
                    type: data.type,
                    full_name: data.full_name,
                    initials: data.initials,
                    program: data.program || '',
                    batch: data.batch || '-',
                    is_online: data.is_online || false,
                    last_message: content,
                    last_message_time: formatTime(new Date(utcTimestamp)),
                    last_message_timestamp: utcTimestamp,
                    last_message_from_me: !isUnread,
                    unread_count: isUnread ? 1 : 0,
                    avatar: data.avatar || null,
                    is_archived: isArchived,  // ✅ Add archive status
                    is_muted: isMuted
                };
                
                allContacts.unshift(newContact);
                applyFilter();
            } else {
                // Create placeholder with fixed UTC timestamp
                const utcTimestamp = ensureUTCTimestamp(timestamp);
                
                const newContact = {
                    id: contactId,
                    type: contactType,
                    full_name: contactType === 'admin' ? 'Admin Staff' : 'Alumni #' + contactId,
                    initials: contactType === 'admin' ? 'AD' : 'AU',
                    program: '',
                    batch: '-',
                    is_online: false,
                    last_message: content,
                    last_message_time: formatTime(new Date(utcTimestamp)),
                    last_message_timestamp: utcTimestamp,
                    last_message_from_me: !isUnread,
                    unread_count: isUnread ? 1 : 0,
                    avatar: null,
                    is_archived: false,
                    is_muted: false
                };
                
                allContacts.unshift(newContact);
                applyFilter();
            }
        } catch (error) {
            console.error('Error fetching contact info:', error);
        }
    }

    // ============================================
    // NEW: BACKGROUND REFRESH FOR ACCURATE COUNTS
    // ============================================
    let refreshTimeout;
    function refreshConversationsInBackground() {
        // Debounce background refreshes
        clearTimeout(refreshTimeout);
        refreshTimeout = setTimeout(async () => {
            try {
                const url = archiveMode ? '/admin/messages/conversations?archived=1' : '/admin/messages/conversations';
                const response = await fetch(url);
                if (!response.ok) return;
                
                const freshConversations = await response.json();
                
                // Update unread counts without disrupting the current order
                freshConversations.forEach(fresh => {
                    const existing = allContacts.find(c => c.id == fresh.id && c.type === fresh.type);
                    if (existing) {
                        existing.unread_count = fresh.unread_count;
                        existing.last_message = fresh.last_message;
                        existing.last_message_time = fresh.last_message_time;
                        existing.last_message_from_me = fresh.last_message_from_me;
                    }
                });
                
                updateUnreadBadge();
                applyFilter();
            } catch (error) {
                console.error('Background refresh error:', error);
            }
        }, 2000); // Wait 2 seconds before refreshing to avoid too many requests
    }
    
    function handleOutgoingMessageFromOtherSession(message) {
        // 🔧 FIXED: Check both ID AND type
        if (message.sender_id != adminId || message.sender_type !== 'admin') return;
        
        loadConversations();
        
        // 🔧 FIXED: Match by ID AND type
        if (currentChat && message.receiver_id == currentChat.id && message.receiver_type === currentChat.type) {
            const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
            if (existingMsg) {
                console.log('⚠️ Duplicate outgoing message prevented:', message.id);
                return;
            }
            
            appendMessage({
                id: message.id,
                content: message.content,
                sender_id: message.sender_id,
                sender_type: message.sender_type,
                receiver_id: message.receiver_id,
                receiver_type: message.receiver_type,
                is_read: message.is_read,
                created_at: message.created_at,
                time: formatTime(new Date(message.created_at)),
                attachments: []
            });
            scrollToBottom();
            lastMessageId = Math.max(lastMessageId, message.id);
        }
    }
    
    async function decryptContent(content, senderType, receiverType) {
        if (!content || (typeof content === 'string' && !content.startsWith('enc:') && !content.startsWith('U2FsdGVkX1'))) {
            return content || '';
        }
        
        if (isDecrypting) {
            console.log('⏳ Decryption already in progress...');
            return '[Decrypting...]';
        }
        
        isDecrypting = true;
        
        try {
            const response = await fetch('/admin/messages/decrypt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    content: content,
                    sender_type: senderType,
                    receiver_type: receiverType
                })
            });
            
            if (!response.ok) {
                console.error('Decryption failed with status:', response.status);
                return '[Encrypted message]';
            }
            
            const data = await response.json();
            return data.decrypted || content;
        } catch (error) {
            console.error('Decryption error:', error);
            return '[Error decrypting message]';
        } finally {
            isDecrypting = false;
        }
    }
    
    // ============================================
    // POLLING FALLBACK
    // ============================================
    function startPolling() {
        console.log('🔄 Starting message polling (every 2 seconds)...');
        if (pollingInterval) clearInterval(pollingInterval);
        pollingInterval = setInterval(checkForNewMessages, 2000);
    }
    
    async function checkForNewMessages() {
        if (!currentChat) return;
        
        try {
            const response = await fetch(`/admin/messages/${currentChat.type}/${currentChat.id}`);
            if (!response.ok) return;
            
            const messages = await response.json();
            if (messages && messages.length > 0) {
                let hasNewMessages = false;
                
                messages.forEach(msg => {
                    if (msg.id > lastMessageId && msg.sender_id != adminId) {
                        const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
                        if (!existingMsg) {
                            appendMessage(msg);
                            hasNewMessages = true;
                        }
                        lastMessageId = Math.max(lastMessageId, msg.id);
                    }
                });
                
                if (hasNewMessages) {
                    scrollToBottom();
                    loadConversations();
                }
            }
        } catch (error) {
            console.error('Polling error:', error);
        }
    }
    
async function loadConversations() {
    try {
        // Load individual conversations
        const url = archiveMode ? '/admin/messages/conversations?archived=1' : '/admin/messages/conversations';
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to load conversations');
        
        const data = await response.json();
        // ✅ Ensure data is always an array
        allContacts = Array.isArray(data) ? data : [];
        
        // Load group conversations
        await loadGroupConversations();
        
        conversationsLoaded = true;
        applyFilter();
        updateUnreadBadge();
        
        // Dispatch event when conversations are loaded
        document.dispatchEvent(new CustomEvent('conversationsLoaded'));
        
        console.log('✅ All conversations loaded - Individuals:', allContacts.length, 'Groups:', allGroups.length);
    } catch (error) {
        console.error('Error loading conversations:', error);
        allContacts = [];
        allGroups = [];
        document.getElementById('contactsList').innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-exclamation-circle"></i>
                <h3>Error loading conversations</h3>
                <p>Please try refreshing the page</p>
            </div>
        `;
    }
}
    
function renderContacts(contacts) {
    const contactsList = document.getElementById('contactsList');
    
    if (!contacts || contacts.length === 0) {
        const query = document.getElementById('searchContacts')?.value?.toLowerCase() || '';
        const isGroupsTab = activeTab === 'groups';
        contactsList.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid ${isGroupsTab ? 'fa-users' : 'fa-user-group'}"></i>
                <h3>${query ? 'No matches found' : isGroupsTab ? 'No channels yet' : 'No conversations yet'}</h3>
                <p>${query ? 'Try a different search term' : isGroupsTab ? 'Create a new channel to connect with multiple alumni' : 'Start a new message to connect with alumni'}</p>
                ${isGroupsTab ? `
                    <button class="btn-primary" id="createGroupBtnEmpty" onclick="openNewGroupModal()" style="margin-top: 1rem; padding: 0.625rem 1.5rem; border: none; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--nu-blue) 0%, var(--nu-blue-light) 100%); color: var(--white); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.875rem; box-shadow: var(--shadow-blue); transition: all var(--transition); display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem;">
                        <i class="fa-solid fa-circle-plus" style="color: #FFFFFF !important; opacity: 1 !important; font-size: 1.1rem; margin: 0;"></i>
                        <span>Create Channel</span>
                    </button>
                ` : ''}
            </div>
        `;
        return;
    }
    
    contactsList.innerHTML = contacts.map(contact => {
        const isGroup = contact.type === 'group';
        const contactId = contact.id;
        const contactType = contact.type || 'alumni';
        const isLastMessageFromMe = contact.last_message_from_me || false;
        const isArchived = contact.is_archived || false;
        const isMuted = contact.is_muted || false;
        
        // ✅ Determine onClick handler
        const onClick = isGroup 
            ? `openGroupChat(${contactId})`
            : `openChat(${contactId}, '${contactType}')`;
        
        // ✅ Avatar HTML for Groups vs Individuals
        let avatarHtml = '';
        if (isGroup) {
            avatarHtml = `
                <div class="group-avatar" style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6d28d9); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.1rem; flex-shrink: 0; overflow: hidden; position: relative;">
                    ${contact.avatar 
                        ? `<img src="${contact.avatar}" alt="${escapeHtml(contact.name)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` 
                        : `<span style="font-size: 1rem; font-weight: 700;">${contact.initials || 'G'}</span>`
                    }
                    <span class="member-count-badge" style="position: absolute; bottom: -2px; right: -2px; background: #8b5cf6; color: white; font-size: 0.55rem; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; border: 2px solid white;">${contact.member_count || 0}</span>
                </div>
            `;
        } else {
            avatarHtml = contact.avatar 
                ? `<img src="${contact.avatar}" class="contact-avatar-img" alt="${escapeHtml(contact.full_name)}">`
                : `<div class="contact-avatar" style="width: 40px; height: 40px; border-radius: 50%; background: linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light)); color: var(--nu-gold); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0;">${contact.initials || '??'}</div>`;
        }
        
        // ✅ Display name - groups use 'name', individuals use 'full_name'
        const displayName = isGroup ? contact.name : contact.full_name;
        
        // ✅ Subtitle - groups show member count, individuals show batch/program
        const displaySubtitle = isGroup 
            ? `${contact.member_count || 0} members`
            : (contact.type === 'admin' 
                ? (contact.admin_role || 'Admin')
                : `Batch ${contact.batch || 'N/A'} | ${contact.program || 'N/A'}`);
        
        // ✅ Last message preview
        const lastMessagePreview = contact.last_message 
            ? (isLastMessageFromMe 
                ? `<span class="you-prefix">You: </span>${escapeHtml(truncateText(contact.last_message, 30))}`
                : escapeHtml(truncateText(contact.last_message, 30)))
            : '<span class="no-message" style="color: #9ca3af;">No messages yet</span>';
        
        const displayTime = contact.last_message_timestamp 
            ? formatTime(new Date(contact.last_message_timestamp))
            : '';
        
        // ✅ Build the contact card HTML
        return `
            <div class="contact-card ${isGroup ? 'group-card' : ''} ${currentChat?.id == contactId && currentChat?.type === contactType ? 'active' : ''} ${contact.unread_count > 0 ? 'unread' : ''} ${isArchived ? 'archived' : ''} ${isMuted ? 'muted' : ''}" 
                onclick="${onClick}"
                style="display: flex; align-items: center; padding: 0.75rem 1rem; gap: 0.75rem; cursor: pointer; border-bottom: 1px solid #f3f4f6; transition: all 0.2s; position: relative;">
                
                ${avatarHtml}
                
                <div class="contact-details" style="flex: 1; min-width: 0;">
                    <div class="contact-row-1" style="display: flex; justify-content: space-between; align-items: center;">
                        <span class="contact-name" style="font-weight: 600; font-size: 0.9rem; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="${escapeHtml(displayName)}">
                            ${escapeHtml(displayName)}
                            ${isMuted ? '<span class="muted-indicator" style="margin-left: 4px; color: #9ca3af;"><i class="fa-solid fa-bell-slash"></i></span>' : ''}
                            ${isGroup ? `<span class="group-badge" style="font-size: 0.6rem; background: #8b5cf6; color: white; padding: 2px 6px; border-radius: 8px; margin-left: 4px;"><i class="fa-solid fa-users"></i></span>` : ''}
                        </span>
                        <span class="contact-time" style="font-size: 0.7rem; color: #9ca3af; white-space: nowrap;">${displayTime}</span>
                    </div>
                    <div class="contact-row-2" style="font-size: 0.75rem; color: #6b7280; margin-top: 2px;">
                        ${escapeHtml(displaySubtitle)}
                    </div>
                    <div class="contact-row-3" style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                        <span class="contact-preview" style="font-size: 0.8rem; color: #6b7280; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; flex: 1;">${lastMessagePreview}</span>
                        ${contact.unread_count > 0 
                            ? `<span class="unread-count" style="background: #3b82f6; color: white; font-size: 0.7rem; font-weight: 600; padding: 1px 8px; border-radius: 10px; min-width: 20px; text-align: center;">${contact.unread_count}</span>` 
                            : ''
                        }
                    </div>
                </div>
                
                <div class="contact-actions" style="flex-shrink: 0;">
                    <button class="btn-more" 
                        data-contact-id="${contactId}" 
                        data-contact-type="${contactType}"
                        onclick="event.stopPropagation(); toggleContactDropdown(${contactId}, '${contactType}', this)"
                        style="background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px 8px; border-radius: 4px; font-size: 1rem;">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                </div>
            </div>
            
            <div class="contact-dropdown ${isGroup ? 'group-dropdown' : ''}" id="dropdown-${contactId}-${contactType}" data-contact-id="${contactId}" data-contact-type="${contactType}" style="display: none; position: absolute; right: 10px; top: 100%; background: white; border-radius: 8px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); min-width: 180px; z-index: 100; padding: 4px 0; margin-top: 4px;">
                ${isGroup ? `
                    <button type="button" class="dropdown-item" onclick="event.stopPropagation(); openGroupInfo(${contactId})" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; width: 100%; background: none; border: none; cursor: pointer; font-size: 0.85rem; color: #374151; transition: background 0.2s;">
                        <i class="fa-solid fa-info-circle"></i>
                        Channel Info
                    </button>
                    <hr style="margin: 4px 12px; border: none; border-top: 1px solid #e5e7eb;">
                ` : ''}
                <button type="button" class="dropdown-item" onclick="handleDropdownAction(event, ${contactId}, '${contactType}', '${isArchived ? 'unarchive' : 'archive'}')" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; width: 100%; background: none; border: none; cursor: pointer; font-size: 0.85rem; color: #374151; transition: background 0.2s;">
                    <i class="fa-solid ${isArchived ? 'fa-box-open' : 'fa-box-archive'}"></i>
                    ${isArchived ? 'Unarchive' : 'Archive'}
                </button>
                <button type="button" class="dropdown-item" onclick="handleDropdownAction(event, ${contactId}, '${contactType}', '${isMuted ? 'unmute' : 'mute'}')" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; width: 100%; background: none; border: none; cursor: pointer; font-size: 0.85rem; color: #374151; transition: background 0.2s;">
                    <i class="fa-solid ${isMuted ? 'fa-bell' : 'fa-bell-slash'}"></i>
                    ${isMuted ? 'Unmute' : 'Mute'}
                </button>
                ${isGroup ? `
                    <hr style="margin: 4px 12px; border: none; border-top: 1px solid #e5e7eb;">
                    <button type="button" class="dropdown-item leave-group" onclick="event.stopPropagation(); handleDropdownAction(event, ${contactId}, '${contactType}', 'leave')" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; width: 100%; background: none; border: none; cursor: pointer; font-size: 0.85rem; color: #ef4444; transition: background 0.2s;">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        Leave Channel
                    </button>
                ` : ''}
                <hr style="margin: 4px 12px; border: none; border-top: 1px solid #e5e7eb;">
                <button type="button" class="dropdown-item danger" onclick="handleDropdownAction(event, ${contactId}, '${contactType}', 'delete')" style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; width: 100%; background: none; border: none; cursor: pointer; font-size: 0.85rem; color: #ef4444; transition: background 0.2s;">
                    <i class="fa-solid fa-trash-can"></i>
                    Delete Chat
                </button>
            </div>
        `;
    }).join('');
    
    // ✅ Make dropdowns work with click outside
    document.querySelectorAll('.btn-more').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
        });
    });
    
    // ✅ Close dropdowns when clicking elsewhere
    document.addEventListener('click', function() {
        document.querySelectorAll('.contact-dropdown').forEach(d => {
            d.style.display = 'none';
        });
    });
}
    
    // Handle search input - show/hide clear button and trigger search
    function handleSearchInput() {
        const input = document.getElementById('searchContacts');
        const clearBtn = document.getElementById('searchClearBtn');
        
        if (input.value.length > 0) {
            clearBtn.style.display = 'flex';
        } else {
            clearBtn.style.display = 'none';
        }
        
        applyFilter();
    }

    // Clear the search input and reset the contacts list
    function clearSearch() {
        const input = document.getElementById('searchContacts');
        const clearBtn = document.getElementById('searchClearBtn');
        
        // Clear the input
        input.value = '';
        
        // Hide the clear button
        clearBtn.style.display = 'none';
        
        // Reset to show all conversations
        applyFilter();
        
        // Focus back on the input for convenience
        input.focus();
    }

    // Keep this for backward compatibility if needed
    function filterContacts() {
        applyFilter();
    }
    
    function filterByTab(tab, element) {
        activeTab = tab;
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
        applyFilter();
    }
    
function applyFilter() {
    const query = document.getElementById('searchContacts')?.value?.toLowerCase() || '';
    
    // ✅ Ensure arrays exist
    const individuals = Array.isArray(allContacts) ? [...allContacts] : [];
    const groups = Array.isArray(allGroups) ? [...allGroups] : [];
    
    console.log('📊 Applying filter - Individuals:', individuals.length, 'Groups:', groups.length);
    
    // Apply archive filter
    let filteredIndividuals = individuals;
    let filteredGroups = groups;
    
    if (!archiveMode) {
        filteredIndividuals = individuals.filter(c => !c.is_archived);
        filteredGroups = groups.filter(g => !g.is_archived);
    } else {
        filteredIndividuals = individuals.filter(c => c.is_archived);
        filteredGroups = groups.filter(g => g.is_archived);
    }
    
    // Apply search
    if (query && query.length >= 2) {
        filteredIndividuals = filteredIndividuals.filter(c => 
            c.full_name?.toLowerCase().includes(query) ||
            c.program?.toLowerCase().includes(query) ||
            c.batch?.toLowerCase().includes(query)
        );
        filteredGroups = filteredGroups.filter(g => 
            g.name?.toLowerCase().includes(query)
        );
    }
    
    // Apply tab filter
    switch(activeTab) {
        case 'unread':
            filteredIndividuals = filteredIndividuals.filter(c => c.unread_count > 0);
            filteredGroups = filteredGroups.filter(g => g.unread_count > 0);
            break;
        case 'groups':
            filteredIndividuals = [];
            break;
        case 'all':
        default:
            break;
    }
    
    // ✅ FIXED: Merge and sort by timestamp (newest first)
    const merged = [...filteredIndividuals, ...filteredGroups];
    
    // ✅ IMPROVED SORTING: Handle null/undefined timestamps properly
    merged.sort((a, b) => {
        // Get timestamps, default to 0 (1970-01-01) if null/undefined
        const timeA = a.last_message_timestamp ? new Date(a.last_message_timestamp).getTime() : 0;
        const timeB = b.last_message_timestamp ? new Date(b.last_message_timestamp).getTime() : 0;
        
        // Sort descending (newest first)
        return timeB - timeA;
    });
    
    console.log('📊 Rendering', merged.length, 'contacts');
    renderContacts(merged);
}

    async function toggleArchiveView() {
        archiveMode = !archiveMode;
        const archiveBtn = document.getElementById('archiveToggleBtn');
        const panelTitle = document.getElementById('panelTitle');
        const newMsgBtn = document.getElementById('newMessageBtn');
        const newGroupBtn = document.getElementById('newGroupBtn');
        
        if (archiveMode) {
            // Switch to archive view
            archiveBtn.innerHTML = '<i class="fa-solid fa-arrow-left"></i>';
            archiveBtn.title = 'Back to Messages';
            panelTitle.textContent = 'Archived Chats';
            newMsgBtn.style.display = 'none';
            newGroupBtn.style.display = 'none';
            
            // Load archived conversations
            await loadConversations();
            
            // Close any open chat that's not archived
            if (currentChat) {
                const contact = allContacts.find(c => c.id == currentChat.id && c.type === currentChat.type);
                const group = allGroups.find(g => g.id == currentChat.id);
                if ((contact && !contact.is_archived) || (group && !group.is_archived)) {
                    currentChat = null;
                    document.getElementById('noChatSelected').style.display = 'flex';
                    document.getElementById('chatHeader').style.display = 'none';
                    document.getElementById('chatMessages').style.display = 'none';
                    document.getElementById('chatInput').style.display = 'none';
                }
            }
        } else {
            // Switch back to normal view
            archiveBtn.innerHTML = '<i class="fa-solid fa-box-archive"></i>';
            archiveBtn.title = 'Archived Chats';
            panelTitle.textContent = 'Messages';
            newMsgBtn.style.display = '';
            newGroupBtn.style.display = '';
            
            // Reload normal conversations
            await loadConversations();
            
            // If current chat is archived, close it
            if (currentChat) {
                const contact = allContacts.find(c => c.id == currentChat.id && c.type === currentChat.type);
                const group = allGroups.find(g => g.id == currentChat.id);
                if ((contact && contact.is_archived) || (group && group.is_archived)) {
                    currentChat = null;
                    document.getElementById('noChatSelected').style.display = 'flex';
                    document.getElementById('chatHeader').style.display = 'none';
                    document.getElementById('chatMessages').style.display = 'none';
                    document.getElementById('chatInput').style.display = 'none';
                }
            }
        }
    }
    
    function updateUnreadBadge() {
        const individualUnread = allContacts.reduce((sum, c) => sum + (c.unread_count || 0), 0);
        const groupUnread = allGroups.reduce((sum, g) => sum + (g.unread_count || 0), 0);
        const totalUnread = individualUnread + groupUnread;
        
        const badge = document.getElementById('unreadBadge');
        if (badge) {
            badge.textContent = totalUnread;
            badge.style.display = totalUnread > 0 ? 'inline' : 'none';
        }
    }
    
async function openChat(contactId, type = 'alumni') {
    lastMessageId = 0;
    currentChat = { id: contactId, type: type };
    
    // Hide typing indicator from previous chat
    hideTypingIndicator();
    clearTimeout(typingTimeout);
    clearTimeout(typingIndicatorTimeout);
    
    // Update contact list active state
    document.querySelectorAll('.contact-card').forEach(card => card.classList.remove('active'));
    const activeCard = document.querySelector(`.contact-card[onclick="openChat(${contactId}, '${type}')"]`);
    if (activeCard) activeCard.classList.add('active');
    
    // Show chat panel, hide empty state
    document.getElementById('noChatSelected').style.display = 'none';
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('chatMessages').style.display = 'block';
    document.getElementById('chatInput').style.display = 'flex';
    
    // 🔥 IMPORTANT: Initially disable input while loading
    const input = document.getElementById('messageInput');
    const sendBtn = document.querySelector('.btn-send');
    const attachBtn = document.querySelector('.btn-attach');
    const emojiBtn = document.querySelector('.btn-emoji');
    
    input.disabled = true;
    input.placeholder = 'Loading conversation...';
    sendBtn.disabled = true;
    sendBtn.style.opacity = '0.5';
    sendBtn.style.cursor = 'not-allowed';
    attachBtn.disabled = true;
    attachBtn.style.opacity = '0.5';
    attachBtn.style.cursor = 'not-allowed';
    emojiBtn.disabled = true;
    emojiBtn.style.opacity = '0.5';
    emojiBtn.style.cursor = 'not-allowed';
    
    // Update header
    let contact = allContacts.find(c => c.id == contactId && c.type === type);
    
    if (!contact) {
        const cardElement = document.querySelector(`.contact-card[onclick="openChat(${contactId}, '${type}')"]`);
        if (cardElement) {
            const nameEl = cardElement.querySelector('.contact-name');
            const batchEl = cardElement.querySelector('.contact-batch');
            const avatarEl = cardElement.querySelector('.contact-avatar');
            const avatarImgEl = cardElement.querySelector('.contact-avatar-img');
            const adminBadgeEl = cardElement.querySelector('.admin-role-badge');
            const adminRole = adminBadgeEl ? adminBadgeEl.textContent.trim() : (type === 'admin' ? 'Admin' : null);
            
            contact = {
                id: contactId,
                type: type,
                full_name: nameEl ? nameEl.textContent.trim() : (type === 'admin' ? 'Admin' : 'Alumni'),
                initials: avatarEl ? avatarEl.textContent.trim() : '??',
                program: '',
                batch: batchEl ? batchEl.textContent.trim().replace('Batch ', '') : '-',
                is_online: false,
                avatar: avatarImgEl ? avatarImgEl.src : null,
                admin_role: adminRole,
                is_archived: false
            };
        }
    }
    
    // ✅ Fetch archive status from server
    let isArchived = false;
    if (type !== 'group') {
        try {
            const response = await fetch(`/admin/messages/dm-settings?contact_id=${contactId}&contact_type=${type}`);
            if (response.ok) {
                const data = await response.json();
                isArchived = data.is_archived || false;
                
                if (contact) {
                    contact.is_archived = isArchived;
                }
                
                const existingContact = allContacts.find(c => c.id == contactId && c.type === type);
                if (existingContact) {
                    existingContact.is_archived = isArchived;
                }
            }
        } catch (error) {
            console.error('Error fetching archive status:', error);
            isArchived = false;
        }
    } else {
        // ✅ For groups, use the is_archived from the group data
        const group = allGroups.find(g => g.id == contactId);
        if (group) {
            isArchived = group.is_archived || false;
            if (contact) {
                contact.is_archived = isArchived;
            }
        }
    }
    
    // ✅ Now check archive status and update UI
    if (contact) {
        const chatAvatar = document.getElementById('chatAvatar');
        
        if (contact.avatar) {
            chatAvatar.innerHTML = `<img src="${contact.avatar}" alt="${escapeHtml(contact.full_name)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
        } else {
            chatAvatar.textContent = contact.initials;
            chatAvatar.style.background = 'linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light))';
            chatAvatar.style.color = 'var(--nu-gold)';
        }
        
        const displayRole = contact.admin_role || (type === 'admin' ? 'Admin' : '');
        document.getElementById('chatName').innerHTML = `${escapeHtml(contact.full_name)} ${contact.type === 'admin' ? `<span class="admin-badge" style="font-size: 0.65rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 2px 8px; border-radius: 12px; margin-left: 8px; font-weight: 600;">${escapeHtml(displayRole)}</span>` : ''}`;
        
        // Check real-time presence for admin contacts
        let isOnline = contact.is_online;
        if (contact.type === 'admin' && presenceChannel) {
            const state = presenceChannel.presenceState();
            let found = false;
            Object.values(state).forEach(presences => {
                presences.forEach(presence => {
                    if (presence.admin_id == contactId) {
                        found = true;
                    }
                });
            });
            isOnline = found;
        }
        
        document.getElementById('chatStatus').innerHTML = `
            <span class="status-dot ${isOnline ? 'online' : ''}"></span> 
            ${isOnline ? 'Online' : 'Offline'}
        `;
    }
    
    // Load messages
    await loadMessages(contactId, type);
    await markMessagesAsRead(contactId, type);
    
    // ✅ CHECK IF CHAT IS ARCHIVED AND UPDATE INPUT
    // This will either enable or disable input based on archive status
    checkAndDisableArchivedChat(contactId, type);
    
    // Focus input (only if not archived)
    if (!isArchived) {
        document.getElementById('messageInput').focus();
    }
    
    showChatOnMobile();
}

    // ============================================
    // CHECK IF CHAT IS ARCHIVED AND DISABLE INPUT
    // ============================================
function checkAndDisableArchivedChat(contactId, contactType) {
    // Find the contact in allContacts
    const contact = allContacts.find(c => c.id == contactId && c.type === contactType);
    
    const input = document.getElementById('messageInput');
    const sendBtn = document.querySelector('.btn-send');
    const attachBtn = document.querySelector('.btn-attach');
    const emojiBtn = document.querySelector('.btn-emoji');
    
    // Ensure we have a valid contact with is_archived property
    const isArchived = contact ? contact.is_archived : false;
    
    if (isArchived) {
        // Disable input and buttons
        input.disabled = true;
        input.placeholder = 'This conversation is archived. Unarchive to send messages.';
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.5';
        sendBtn.style.cursor = 'not-allowed';
        attachBtn.disabled = true;
        attachBtn.style.opacity = '0.5';
        attachBtn.style.cursor = 'not-allowed';
        emojiBtn.disabled = true;
        emojiBtn.style.opacity = '0.5';
        emojiBtn.style.cursor = 'not-allowed';
        
        // Show a message in the chat area
        showArchivedWarning();
    } else {
        // Enable input and buttons
        input.disabled = false;
        input.placeholder = 'Type a message here...';
        sendBtn.disabled = false;
        sendBtn.style.opacity = '1';
        sendBtn.style.cursor = 'pointer';
        attachBtn.disabled = false;
        attachBtn.style.opacity = '1';
        attachBtn.style.cursor = 'pointer';
        emojiBtn.disabled = false;
        emojiBtn.style.opacity = '1';
        emojiBtn.style.cursor = 'pointer';
        
        // Remove archived warning if exists
        const warning = document.querySelector('.archived-warning');
        if (warning) warning.remove();
    }
}

    function showArchivedWarning() {
        const container = document.getElementById('chatMessages');
        
        // Remove existing warning
        const existingWarning = container.querySelector('.archived-warning');
        if (existingWarning) existingWarning.remove();
        
        // Add warning at the top of messages
        const warning = document.createElement('div');
        warning.className = 'archived-warning';
        warning.style.cssText = `
            background: #fef3c7;
            border: 1px solid #f59e0b;
            border-radius: 8px;
            padding: 12px 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: #92400e;
            font-size: 14px;
        `;
        warning.innerHTML = `
            <i class="fa-solid fa-box-archive" style="color: #f59e0b; font-size: 18px;"></i>
            <span>This conversation is archived. <button onclick="unarchiveAndRefresh()" style="background: none; border: none; color: #f59e0b; font-weight: 600; cursor: pointer; text-decoration: underline; padding: 0;">Unarchive</button> to continue messaging.</span>
        `;
        
        container.prepend(warning);
    }

    async function unarchiveAndRefresh() {
        if (!currentChat) return;
        
        try {
            const response = await fetch('/admin/messages/archive', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    contact_id: currentChat.id,
                    contact_type: currentChat.type,
                    archived: false
                })
            });
            
            const data = await response.json();
            
            if (data.success) {
                // Update local data
                const contact = allContacts.find(c => c.id == currentChat.id && c.type === currentChat.type);
                if (contact) {
                    contact.is_archived = false;
                }
                
                // Update card
                const card = findContactCard(currentChat.id, currentChat.type);
                if (card) {
                    card.classList.remove('archived');
                }
                
                // Refresh chat
                await loadConversations();
                checkAndDisableArchivedChat(currentChat.id, currentChat.type);
                
                showToast('Chat unarchived successfully');
            }
        } catch (error) {
            console.error('Error unarchiving:', error);
            showToast('Failed to unarchive chat', 'error');
        }
    }

    // ============================================
    // MOBILE CHAT VIEW TOGGLE
    // ============================================
    function showChatOnMobile() {
        if (window.innerWidth <= 1024) {
            document.querySelector('.contacts-panel').classList.add('chat-active');
            document.querySelector('.chat-panel').classList.add('chat-active');
        }
    }

    function showContactsOnMobile() {
        if (window.innerWidth <= 1024) {
            document.querySelector('.contacts-panel').classList.remove('chat-active');
            document.querySelector('.chat-panel').classList.remove('chat-active');
        }
    }
    
    async function loadMessages(contactId, type = 'alumni') {
        const container = document.getElementById('chatMessages');
        container.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading messages...</div>';
        
        try {
            const response = await fetch(`/admin/messages/${type}/${contactId}`);
            if (!response.ok) throw new Error('Failed to load messages');
            
            const messages = await response.json();
            
            if (messages && messages.length > 0) {
                lastMessageId = Math.max(...messages.map(m => m.id));
            }
            
            renderMessages(messages);
            scrollToBottom();
        } catch (error) {
            console.error('Error loading messages:', error);
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <h3>Error loading messages</h3>
                    <p>Please try again</p>
                </div>
            `;
        }
    }
    
    function renderMessages(messages) {
        const container = document.getElementById('chatMessages');
        
        if (!messages || messages.length === 0) {
            container.innerHTML = `
                <div class="empty-state">
                    <i class="fa-solid fa-message"></i>
                    <h3>No messages yet</h3>
                    <p>Send the first message to start the conversation</p>
                </div>
            `;
            return;
        }
        
        let html = '';
        let lastDate = null;
        
        messages.forEach(msg => {
            const msgDate = new Date(msg.created_at);
            const localDateStr = msgDate.toLocaleDateString();
            
            if (localDateStr !== lastDate) {
                html += `<div class="date-divider"><span>${formatDateDivider(msgDate)}</span></div>`;
                lastDate = localDateStr;
            }
            
            const isSent = msg.sender_id == adminId;
            html += `
                <div class="message-group ${isSent ? 'sent' : 'received'}" data-msg-id="${msg.id}">
                    <div class="message-bubble">
                        <p>${escapeHtml(msg.content)}</p>
                        ${msg.attachments && msg.attachments.length > 0 ? renderAttachments(msg.attachments, isSent) : ''}
                        <span class="msg-time">
                            ${msg.time || formatTime(msgDate)}
                            ${isSent ? '<i class="fa-solid fa-check-double read-check"></i>' : ''}
                        </span>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
    }

    function renderAttachments(attachments, isSent) {
        return attachments.map(att => {
            if (att.type === 'image') {
                return `
                    <div class="message-attachment">
                        <img src="${att.url}" alt="${escapeHtml(att.name)}" onclick="window.open('${att.url}', '_blank')" loading="lazy">
                    </div>
                `;
            } else {
                return `
                    <div class="message-attachment">
                        <a href="${att.url}" class="file-download" download="${escapeHtml(att.name)}">
                            <i class="fa-solid ${getFileIcon(att.type || 'document')}"></i>
                            <div class="file-info">
                                <span class="file-name">${escapeHtml(att.name)}</span>
                                <span class="file-size">${att.size ? formatFileSize(att.size) : ''}</span>
                            </div>
                            <i class="fa-solid fa-download"></i>
                        </a>
                    </div>
                `;
            }
        }).join('');
    }
    
    function appendMessage(msg) {
        const container = document.getElementById('chatMessages');
        
        if (msg.id && !msg.id.toString().startsWith('temp-')) {
            const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
            if (existingMsg) {
                console.log('⚠️ Duplicate message prevented in appendMessage:', msg.id);
                return;
            }
        }
        
        const isSent = msg.sender_id == adminId;
        const displayTime = msg.time || formatTime(new Date(msg.created_at));
        
        const messageHtml = `
            <div class="message-group ${isSent ? 'sent' : 'received'}" ${msg.id ? `data-msg-id="${msg.id}"` : ''}>
                <div class="message-bubble">
                    <p>${escapeHtml(msg.content)}</p>
                    ${msg.attachments && msg.attachments.length > 0 ? renderAttachments(msg.attachments, isSent) : ''}
                    <span class="msg-time">
                        ${displayTime}
                        ${isSent ? '<i class="fa-solid fa-check-double read-check"></i>' : ''}
                    </span>
                </div>
            </div>
        `;
        
        const emptyState = container.querySelector('.empty-state');
        if (emptyState) emptyState.remove();
        
        container.insertAdjacentHTML('beforeend', messageHtml);
    }
    
async function sendMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    // If it's a group chat, use group send
    if (currentChat && currentChat.type === 'group') {
        return sendGroupMessage();
    }
    
    if (!content || !currentChat) return;

    // Check if chat is archived
    const contact = allContacts.find(c => c.id == currentChat.id && c.type === currentChat.type);
    if (contact && contact.is_archived) {
        showToast('Cannot send messages to archived chat. Unarchive it first.', 'error');
        return;
    }
    
    // If there are files, send with attachments
    if (selectedFiles.length > 0) {
        if (!currentChat) return;
        
        input.value = '';
        input.focus();
        
        const tempId = 'temp-' + Date.now();
        const fileNames = selectedFiles.map(f => f.name).join(', ');
        const tempContent = content || `📎 ${fileNames}`;
        
        const tempMessage = {
            id: tempId,
            content: tempContent,
            sender_id: adminId,
            sender_type: 'admin',
            is_read: false,
            created_at: new Date().toISOString(),
            time: formatTime(new Date()),
            attachments: selectedFiles.map(f => ({
                name: f.name,
                size: f.size,
                type: f.type,
                uploading: true
            }))
        };
        
        appendMessage(tempMessage);
        scrollToBottom();
        
        // Clear typing broadcast
        clearTimeout(typingTimeout);
        if (typingChannel) {
            typingChannel.send({
                type: 'broadcast',
                event: 'typing',
                payload: {
                    sender_id: adminId,
                    sender_type: 'admin',
                    receiver_id: currentChat.id,
                    receiver_type: currentChat.type,
                    stopped: true,
                    timestamp: new Date().toISOString(),
                },
            });
        }
        
        const filesToUpload = [...selectedFiles];
        selectedFiles = [];
        renderFilePreviews();
        
        try {
            const formData = new FormData();
            formData.append('receiver_id', currentChat.id);
            formData.append('receiver_type', currentChat.type);
            formData.append('content', content || '');
            filesToUpload.forEach(file => {
                formData.append('attachments[]', file);
            });
            
            const response = await fetch('/admin/messages/send-with-attachments', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if (!response.ok) {
                const errorData = await response.json().catch(() => ({}));
                throw new Error(errorData.error || 'Failed to send files');
            }
            
            const data = await response.json();
            
            if (data.success) {
                const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
                if (tempElement) tempElement.remove();
                
                data.message.time = formatTime(new Date(data.message.created_at));
                appendMessage(data.message);
                scrollToBottom();
                
                lastMessageId = Math.max(lastMessageId, data.message.id);
                
                updateContactWithNewMessage(
                    currentChat.id,
                    currentChat.type,
                    content || '📎 Attachment',
                    data.message.created_at,
                    false
                );
            }
        } catch (error) {
            console.error('Error sending files:', error);
            const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
            if (tempElement) tempElement.remove();
            alert('Failed to send files. Please try again.');
        }
        return;
    }
    
    // No files, send text message
    // Clear typing timeout since we're sending
    clearTimeout(typingTimeout);
    
    input.value = '';
    input.focus();
    
    const now = new Date();
    const tempId = 'temp-' + Date.now();
    const tempMessage = {
        id: tempId,
        content: content,
        sender_id: adminId,
        sender_type: 'admin',
        is_read: false,
        created_at: now.toISOString(),
        time: formatTime(now),
        attachments: []
    };
    
    appendMessage(tempMessage);
    scrollToBottom();
    
    // Broadcast that we're no longer typing
    if (typingChannel) {
        typingChannel.send({
            type: 'broadcast',
            event: 'typing',
            payload: {
                sender_id: adminId,
                sender_type: 'admin',
                receiver_id: currentChat.id,
                receiver_type: currentChat.type,
                stopped: true,
                timestamp: new Date().toISOString(),
            },
        });
    }
    
    try {
        const response = await fetch('/admin/messages/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                receiver_id: currentChat.id,
                receiver_type: currentChat.type,
                content: content
            })
        });
        
        if (!response.ok) throw new Error('Failed to send message');
        
        const data = await response.json();
        
        if (data.success) {
            const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
            if (tempElement) tempElement.remove();
            
            if (!data.message.time) {
                data.message.time = formatTime(new Date(data.message.created_at));
            }
            
            appendMessage(data.message);
            scrollToBottom();
            
            lastMessageId = Math.max(lastMessageId, data.message.id);
            
            updateContactWithNewMessage(
                currentChat.id,
                currentChat.type,
                content,
                data.message.created_at,
                false
            );
        }
    } catch (error) {
        console.error('Error sending message:', error);
        const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
        if (tempElement) tempElement.remove();
        
        input.value = content;
        alert('Failed to send message. Please try again.');
    }
}
    
    async function markMessagesAsRead(contactId, contactType = 'alumni') {
        const contact = allContacts.find(c => c.id == contactId && c.type === contactType);
        if (contact) {
            // Update local state immediately for instant UI feedback
            contact.unread_count = 0;
            updateUnreadBadge();
            applyFilter();
            
            // 🔧 Send request to backend to persist read status
            try {
                const response = await fetch('/admin/messages/mark-read', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        sender_id: contactId,
                        sender_type: contactType
                    })
                });
                
                if (!response.ok) {
                    console.error('Failed to mark messages as read on server');
                } else {
                    console.log('✅ Messages marked as read on server');
                }
            } catch (error) {
                console.error('Error marking messages as read:', error);
            }
        }
    }
    
    function handleKeyPress(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        } else {
            // User is typing - broadcast typing indicator
            onMessageInput();
        }
    }
    
    function scrollToBottom() {
        const container = document.getElementById('chatMessages');
        setTimeout(() => {
            container.scrollTop = container.scrollHeight;
        }, 100);
    }
    
    // ============================================
    // DIRECTORY REDIRECT HANDLER
    // ============================================
    function handleDirectoryRedirect() {
        const openChatData = sessionStorage.getItem('openChat');
        if (!openChatData) return;
        
        try {
            const chatData = JSON.parse(openChatData);
            sessionStorage.removeItem('openChat');
            
            console.log('📋 Directory redirect detected for:', chatData.name);
            
            // Function to attempt opening the chat
            const tryOpenChat = () => {
                if (allContacts.length > 0) {
                    console.log('✅ Conversations loaded, opening chat for:', chatData.name);
                    
                    // Check if contact exists in conversations
                    const existingContact = allContacts.find(
                        c => c.id == chatData.id && c.type === 'alumni'
                    );
                    
                    if (existingContact) {
                        // Open existing conversation
                        openChat(chatData.id, 'alumni');
                    } else {
                        // Create a placeholder and open chat
                        const initials = chatData.name
                            .split(' ')
                            .map(n => n.charAt(0))
                            .join('')
                            .toUpperCase();
                        
                        allContacts.unshift({
                            id: chatData.id,
                            type: 'alumni',
                            full_name: chatData.name,
                            initials: initials || '??',
                            program: '',
                            batch: '-',
                            is_online: false,
                            last_message: null,
                            last_message_time: null,
                            unread_count: 0,
                            avatar: null
                        });
                        
                        renderContacts(allContacts);
                        openChat(chatData.id, 'alumni');
                        
                        // Refresh conversations in background
                        setTimeout(() => loadConversations(), 1500);
                    }
                    return true; // Successfully handled
                }
                return false; // Conversations not loaded yet
            };
            
            // Try immediately
            if (tryOpenChat()) return;
            
            // If conversations aren't loaded yet, wait for them
            let attempts = 0;
            const maxAttempts = 25; // 5 seconds (25 * 200ms)
            
            const checkInterval = setInterval(() => {
                attempts++;
                
                if (tryOpenChat() || attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    
                    if (attempts >= maxAttempts) {
                        console.warn('⚠️ Timed out waiting for conversations to load');
                        // Force try one more time
                        tryOpenChat();
                    }
                }
            }, 200);
            
        } catch (error) {
            console.error('❌ Error handling directory redirect:', error);
            sessionStorage.removeItem('openChat');
        }
    }
    
    // ============================================
    // NEW MESSAGE MODAL
    // ============================================
    function openNewMessageModal() {
        document.getElementById('newMessageModal').classList.add('active');
        document.getElementById('alumniSearch').value = '';
        document.getElementById('searchResults').innerHTML = '<p style="color: #9ca3af; text-align: center;">Start typing to search for alumni</p>';
        setTimeout(() => {
            document.getElementById('alumniSearch').focus();
        }, 100);
    }
    
    function closeNewMessageModal() {
        document.getElementById('newMessageModal').classList.remove('active');
    }
    
    async function searchAlumni() {
        const query = document.getElementById('alumniSearch').value.trim();
        
        if (query.length < 2) {
            document.getElementById('searchResults').innerHTML = '<p style="color: #9ca3af; text-align: center;">Type at least 2 characters to search</p>';
            return;
        }
        
        document.getElementById('searchResults').innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</div>';
        
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(async () => {
            try {
                const response = await fetch(`/admin/messages/search/alumni?q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (!response.ok) {
                    const errorMsg = data.error || 'Unknown error';
                    const errorDetails = data.file ? ` (${data.file}:${data.line})` : '';
                    document.getElementById('searchResults').innerHTML = 
                        `<p style="color: #ef4444; text-align: center;">Error: ${errorMsg}${errorDetails}</p>`;
                    return;
                }
                
                if (data.length === 0) {
                    document.getElementById('searchResults').innerHTML = '<p style="color: #9ca3af; text-align: center;">No alumni found</p>';
                    return;
                }
                
                document.getElementById('searchResults').innerHTML = data.map(a => `
                    <div class="alumni-item" onclick="startNewChat(${a.id}, '${a.type}')">
                        ${a.avatar 
                            ? `<img src="${a.avatar}" class="contact-avatar-img" alt="${escapeHtml(a.full_name)}">`
                            : `<div class="alumni-avatar">${a.initials}</div>`
                        }
                        <div class="alumni-info">
                            <div class="name">${escapeHtml(a.full_name)}</div>
                            <div class="details">${a.type === 'admin' ? a.program || 'Admin' : `Batch ${a.batch} | ${a.program || 'N/A'}`}</div>
                        </div>
                        ${a.is_online ? '<span class="online-dot" title="Online"></span>' : ''}
                    </div>
                `).join('');
            } catch (error) {
                console.error('Error searching:', error);
                document.getElementById('searchResults').innerHTML = 
                    `<p style="color: #ef4444; text-align: center;">Error searching. Please try again.</p>`;
            }
        }, 300);
    }
    
    async function startNewChat(alumniId, type = 'alumni') {
        closeNewMessageModal();
        
        // Check if contact already exists
        const existingContact = allContacts.find(c => c.id == alumniId && c.type === type);
        
        if (existingContact) {
            // Open existing conversation
            openChat(alumniId, type);
            return;
        }
        
        // Fetch alumni info directly from the server
        try {
            const response = await fetch(`/admin/messages/${type}/${alumniId}/info`);
            if (response.ok) {
                const data = await response.json();
                
                // Add to contacts with real data
                allContacts.unshift({
                    id: data.id,
                    type: data.type,
                    full_name: data.full_name,
                    initials: data.initials,
                    program: data.program || '',
                    batch: data.batch || '-',
                    is_online: data.is_online || false,
                    last_message: null,
                    last_message_time: null,
                    unread_count: 0,
                    avatar: data.avatar || null
                });
                
                renderContacts(allContacts);
                openChat(data.id, data.type);
            } else {
                // Fallback: create placeholder
                allContacts.unshift({
                    id: alumniId,
                    type: type,
                    full_name: type === 'admin' ? 'Admin Staff' : 'Alumni #' + alumniId,
                    initials: type === 'admin' ? 'AD' : 'AU',
                    program: type === 'admin' ? 'Admin' : '',
                    batch: '-',
                    is_online: false,
                    last_message: null,
                    last_message_time: null,
                    unread_count: 0,
                    avatar: null
                });
                
                renderContacts(allContacts);
                openChat(alumniId, type);
            }
        } catch (error) {
            console.error('Error fetching alumni info:', error);
            // Create fallback placeholder
            allContacts.unshift({
                id: alumniId,
                type: type,
                full_name: type === 'admin' ? 'Admin Staff' : 'Alumni #' + alumniId,
                initials: type === 'admin' ? 'AD' : 'AU',
                program: type === 'admin' ? 'Admin' : '',
                batch: '-',
                is_online: false,
                last_message: null,
                last_message_time: null,
                unread_count: 0,
                avatar: null
            });
            
            renderContacts(allContacts);
            openChat(alumniId, type);
        }
    }
    
    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function truncateText(text, maxLength) {
        if (!text) return '';
        return text.length > maxLength ? text.substring(0, maxLength) + '...' : text;
    }
    
    function formatTime(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        
        const now = new Date();
        const diffMs = now - date;
        const diffMins = Math.floor(diffMs / 60000);
        
        // Show "Just now" for messages less than 1 minute old
        if (diffMins < 1) {
            return 'Just now';
        }
        
        // Show minutes for messages less than 1 hour old
        if (diffMins < 60) {
            return `${diffMins}m ago`;
        }
        
        // Show hours for messages less than 24 hours old
        const diffHours = Math.floor(diffMins / 60);
        if (diffHours < 24) {
            return `${diffHours}h ago`;
        }
        
        // Otherwise show the time
        return date.toLocaleTimeString('en-US', { 
            hour: 'numeric', 
            minute: '2-digit', 
            hour12: true 
        });
    }
    
    function formatDateDivider(date) {
        // 🔧 If the date is a string from the server (UTC), convert it properly
        if (typeof date === 'string') {
            date = new Date(date);
        }
        
        const now = new Date();
        const yesterday = new Date(now);
        yesterday.setDate(yesterday.getDate() - 1);
        
        // 🔧 Compare using local date strings
        if (date.toLocaleDateString() === now.toLocaleDateString()) {
            return 'Today';
        } else if (date.toLocaleDateString() === yesterday.toLocaleDateString()) {
            return 'Yesterday';
        } else {
            return date.toLocaleDateString('en-US', { 
                month: 'long', 
                day: 'numeric', 
                year: 'numeric' 
            });
        }
    }
    
    // ============================================
    // MOBILE MENU TOGGLE
    // ============================================
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }
    
    document.addEventListener('DOMContentLoaded', async function() {
        console.log('🚀 Initializing messages module...');
        
        // Load all conversations (individual + groups)
        await loadConversations();
        
        // Initialize Supabase
        initSupabase();
        
        // Check for URL parameter to auto-open chat
        handleUrlChatRedirect();
    });

    // Handle chat redirect from URL parameter
    async function handleUrlChatRedirect() {
        const urlParams = new URLSearchParams(window.location.search);
        const chatId = urlParams.get('chat');
        
        if (!chatId) {
            console.log('📋 No chat parameter in URL');
            return;
        }
        
        console.log('📋 Found chat parameter:', chatId);
        
        // Clean up URL immediately
        if (window.history && window.history.replaceState) {
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
        
        // Wait for conversations to load (max 3 seconds)
        let attempts = 0;
        const maxAttempts = 30;
        
        while (attempts < maxAttempts) {
            if (allContacts.length > 0 || attempts > 10) {
                console.log('✅ Checking for contact:', chatId);
                
                // Try to find the contact in existing conversations
                const existingContact = allContacts.find(c => c.id == chatId && c.type === 'alumni');
                
                if (existingContact) {
                    console.log('✅ Found existing contact, opening chat');
                    openChat(chatId, 'alumni');
                    return;
                }
                
                // Fetch alumni info directly from the server
                console.log('⚠️ Contact not in conversations, fetching alumni info...');
                try {
                    const response = await fetch(`/admin/messages/alumni/${chatId}/info`);
                    
                    if (response.ok) {
                        const alumni = await response.json();
                        console.log('✅ Found alumni:', alumni.full_name);
                        
                        allContacts.unshift({
                            id: alumni.id,
                            type: 'alumni',
                            full_name: alumni.full_name,
                            initials: alumni.initials,
                            program: alumni.program || '',
                            batch: alumni.batch || '-',
                            is_online: alumni.is_online || false,
                            last_message: null,
                            last_message_time: null,
                            unread_count: 0,
                            avatar: alumni.avatar || null
                        });
                        
                        renderContacts(allContacts);
                        openChat(alumni.id, 'alumni');
                    } else {
                        console.log('⚠️ Failed to fetch alumni, creating placeholder');
                        createPlaceholder(chatId);
                    }
                } catch (error) {
                    console.error('❌ Error fetching alumni:', error);
                    createPlaceholder(chatId);
                }
                
                return;
            }
            
            await new Promise(resolve => setTimeout(resolve, 100));
            attempts++;
        }
        
        console.warn('⚠️ Timed out, creating placeholder');
        createPlaceholder(chatId);
        
        function createPlaceholder(id) {
            allContacts.unshift({
                id: parseInt(id),
                type: 'alumni',
                full_name: 'Alumni #' + id,
                initials: 'AU',
                program: '',
                batch: '-',
                is_online: false,
                last_message: null,
                last_message_time: null,
                unread_count: 0,
                avatar: null
            });
            
            renderContacts(allContacts);
            openChat(id, 'alumni');
        }
    }
    
    // Close sidebar when clicking on a nav item (mobile)
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });
    
    // Close modal when clicking overlay
    document.getElementById('newMessageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeNewMessageModal();
        }
    });
    
    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                // Reset mobile classes on desktop
                document.getElementById('adminSidebar')?.classList.remove('mobile-open');
                document.getElementById('mobileOverlay')?.classList.remove('active');
                document.querySelector('.contacts-panel')?.classList.remove('chat-active');
                document.querySelector('.chat-panel')?.classList.remove('chat-active');
                document.body.style.overflow = '';
            } else {
                // On mobile, show appropriate view
                if (currentChat) {
                    showChatOnMobile();
                } else {
                    showContactsOnMobile();
                }
            }
        }, 250);
    });
    
    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        // Clean up typing channel
        if (typingChannel) {
            supabaseClient.removeChannel(typingChannel);
        }
        
        // Clean up presence channel (this will automatically untrack)
        if (presenceChannel) {
            supabaseClient.removeChannel(presenceChannel);
        }
        
        // Clean up message channel
        if (supabaseRealtimeChannel) {
            supabaseClient.removeChannel(supabaseRealtimeChannel);
        }
        
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }
    });

    // ============================================
    // HANDLE: Typing event with stopped flag
    // ============================================
    // Update the handleTypingEvent function to handle stopped events
    const originalHandleTypingEvent = handleTypingEvent;
    handleTypingEvent = function(data) {
        if (data.stopped) {
            hideTypingIndicator();
            return;
        }
        originalHandleTypingEvent(data);
    };

    // ============================================
    // EMOJI PICKER
    // ============================================
    let emojiPickerOpen = false;

    function toggleEmojiPicker(event) {
        event.stopPropagation();
        const popup = document.getElementById('emojiPickerPopup');
        const picker = popup.querySelector('emoji-picker');
        
        if (!emojiPickerOpen) {
            // Open picker
            popup.classList.add('active');
            emojiPickerOpen = true;
            
            // Add emoji click listener if not already added
            if (!picker.hasEmojiListener) {
                picker.addEventListener('emoji-click', handleEmojiSelect);
                picker.hasEmojiListener = true;
            }
        } else {
            // Close picker
            closeEmojiPicker();
        }
    }

    function handleEmojiSelect(event) {
        const input = document.getElementById('messageInput');
        const emoji = event.detail.unicode;
        
        // Insert emoji at cursor position
        const start = input.selectionStart;
        const end = input.selectionEnd;
        const text = input.value;
        
        input.value = text.substring(0, start) + emoji + text.substring(end);
        
        // Move cursor after the inserted emoji
        input.selectionStart = input.selectionEnd = start + emoji.length;
        
        // Focus back on input
        input.focus();
        
        // Trigger typing indicator
        onMessageInput();
    }

    function closeEmojiPicker() {
        const popup = document.getElementById('emojiPickerPopup');
        popup.classList.remove('active');
        emojiPickerOpen = false;
    }

    // Close emoji picker when clicking outside
    document.addEventListener('click', function(event) {
        if (emojiPickerOpen) {
            const pickerContainer = document.querySelector('.emoji-picker-container');
            if (pickerContainer && !pickerContainer.contains(event.target)) {
                closeEmojiPicker();
            }
        }
    });

    // Close emoji picker when pressing Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && emojiPickerOpen) {
            closeEmojiPicker();
        }
    });

    // ============================================
// FILE SHARING
// ============================================
let selectedFiles = [];
const MAX_FILE_SIZE = 50 * 1024 * 1024;
const ALLOWED_TYPES = [
    'image/jpeg', 'image/png', 'image/gif', 'image/webp',
    'video/mp4', 'video/webm',
    'application/pdf',
    'application/msword',
    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    'application/vnd.ms-excel',
    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    'application/vnd.ms-powerpoint',
    'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    'text/plain', 'text/csv',
    'application/zip', 'application/x-zip-compressed'
];

function handleFileSelect(event) {
    const files = Array.from(event.target.files);
    
    const validFiles = files.filter(file => {
        if (!ALLOWED_TYPES.includes(file.type)) {
            alert(`File "${file.name}" is not a supported type.`);
            return false;
        }
        if (file.size > MAX_FILE_SIZE) {
            alert(`File "${file.name}" is too large. Maximum size is 50MB.`);
            return false;
        }
        return true;
    });
    
    if (validFiles.length === 0) {
        event.target.value = '';
        return;
    }
    
    selectedFiles = [...selectedFiles, ...validFiles];
    renderFilePreviews();
    event.target.value = '';
}

function renderFilePreviews() {
    const container = document.getElementById('filePreviewContainer');
    const list = document.getElementById('filePreviewList');
    
    if (selectedFiles.length === 0) {
        container.classList.remove('active');
        return;
    }
    
    container.classList.add('active');
    
    list.innerHTML = selectedFiles.map((file, index) => {
        const isImage = file.type.startsWith('image/');
        const icon = getFileIcon(file.type);
        const size = formatFileSize(file.size);
        
        return `
            <div class="file-preview-item">
                ${isImage 
                    ? `<img src="${URL.createObjectURL(file)}" alt="${escapeHtml(file.name)}">`
                    : `<i class="fa-solid ${icon} file-icon"></i>`
                }
                <div>
                    <div class="file-name" title="${escapeHtml(file.name)}">${escapeHtml(file.name)}</div>
                    <div class="file-size">${size}</div>
                </div>
                <button class="remove-file" onclick="removeFile(${index})" title="Remove file">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        `;
    }).join('');
}

function removeFile(index) {
    selectedFiles.splice(index, 1);
    renderFilePreviews();
}

function getFileIcon(mimeType) {
    if (!mimeType) return 'fa-file';
    if (mimeType.startsWith('image/')) return 'fa-image';
    if (mimeType.startsWith('video/')) return 'fa-video';
    if (mimeType.includes('pdf')) return 'fa-file-pdf';
    if (mimeType.includes('word') || mimeType.includes('document')) return 'fa-file-word';
    if (mimeType.includes('excel') || mimeType.includes('spreadsheet')) return 'fa-file-excel';
    if (mimeType.includes('powerpoint') || mimeType.includes('presentation')) return 'fa-file-powerpoint';
    if (mimeType.includes('text') || mimeType.includes('csv')) return 'fa-file-lines';
    if (mimeType.includes('zip') || mimeType.includes('compressed')) return 'fa-file-zipper';
    return 'fa-file';
}

function formatFileSize(bytes) {
    if (!bytes) return '';
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}

function renderAttachments(attachments, isSent) {
    if (!attachments || attachments.length === 0) return '';
    
    return attachments.map(att => {
        if (att.type === 'image') {
            return `
                <div class="message-attachment">
                    <img src="${att.url}" alt="${escapeHtml(att.name || 'Image')}" onclick="window.open('${att.url}', '_blank')" loading="lazy">
                </div>
            `;
        } else {
            return `
                <div class="message-attachment">
                    <a href="${att.url}" class="file-download" download="${escapeHtml(att.name || 'file')}">
                        <i class="fa-solid ${getFileIcon(att.type || 'document')}"></i>
                        <div class="file-info">
                            <span class="file-name">${escapeHtml(att.name || 'File')}</span>
                            <span class="file-size">${formatFileSize(att.size)}</span>
                        </div>
                        <i class="fa-solid fa-download"></i>
                    </a>
                </div>
            `;
        }
    }).join('');
}

    // Override sendMessage to handle files
    const originalSendMessageFn = sendMessage;
    sendMessage = async function() {
        const input = document.getElementById('messageInput');
        const content = input.value.trim();
        
        // If there are files, send with attachments
        if (selectedFiles.length > 0) {
            if (!currentChat) return;
            
            input.value = '';
            input.focus();
            
            const tempId = 'temp-' + Date.now();
            const fileNames = selectedFiles.map(f => f.name).join(', ');
            const tempContent = content || `📎 ${fileNames}`;
            
            const tempMessage = {
                id: tempId,
                content: tempContent,
                sender_id: adminId,
                sender_type: 'admin',
                is_read: false,
                created_at: new Date().toISOString(),
                time: formatTime(new Date()),
                attachments: selectedFiles.map(f => ({
                    name: f.name,
                    size: f.size,
                    type: f.type,
                    uploading: true
                }))
            };
            
            appendMessage(tempMessage);
            scrollToBottom();
            
            // Clear typing broadcast
            clearTimeout(typingTimeout);
            if (typingChannel) {
                typingChannel.send({
                    type: 'broadcast',
                    event: 'typing',
                    payload: {
                        sender_id: adminId,
                        sender_type: 'admin',
                        receiver_id: currentChat.id,
                        receiver_type: currentChat.type,
                        stopped: true,
                        timestamp: new Date().toISOString(),
                    },
                });
            }
            
            const filesToUpload = [...selectedFiles];
            selectedFiles = [];
            renderFilePreviews();
            
            try {
                const formData = new FormData();
                formData.append('receiver_id', currentChat.id);
                formData.append('receiver_type', currentChat.type);
                formData.append('content', content || '');
                filesToUpload.forEach(file => {
                    formData.append('attachments[]', file);
                });
                
                const response = await fetch('/admin/messages/send-with-attachments', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    throw new Error(errorData.error || 'Failed to send files');
                }
                
                const data = await response.json();
                
                if (data.success) {
                    const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
                    if (tempElement) tempElement.remove();
                    
                    data.message.time = formatTime(new Date(data.message.created_at));
                    appendMessage(data.message);
                    scrollToBottom();
                    
                    lastMessageId = Math.max(lastMessageId, data.message.id);
                    
                    updateContactWithNewMessage(
                        currentChat.id,
                        currentChat.type,
                        content || '📎 Attachment',
                        data.message.created_at,
                        false
                    );
                }
            } catch (error) {
                console.error('Error sending files:', error);
                const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
                if (tempElement) tempElement.remove();
                alert('Failed to send files. Please try again.');
            }
            return;
        }
        
        // No files, use original send
        if (!content || !currentChat) return;
        originalSendMessageFn();
    };

// ============================================
// CONTACT DROPDOWN TOGGLE - SIMPLEST FIX
// ============================================
function toggleContactDropdown(contactId, contactType, button) {
    event.stopPropagation();
    const dropdownId = `dropdown-${contactId}-${contactType}`;
    const dropdown = document.getElementById(dropdownId);
    const backdrop = document.getElementById('dropdownBackdrop');
    
    // Close all other dropdowns
    document.querySelectorAll('.contact-dropdown.active').forEach(d => {
        if (d.id !== dropdownId) d.classList.remove('active');
    });
    
    if (dropdown.classList.contains('active')) {
        dropdown.classList.remove('active');
        backdrop.classList.remove('active');
        return;
    }
    
    // Position the dropdown near the button
    const buttonRect = button.getBoundingClientRect();
    const listRect = document.querySelector('.contacts-list').getBoundingClientRect();
    
    dropdown.style.top = (buttonRect.bottom - listRect.top + 4) + 'px';
    dropdown.style.right = (listRect.right - buttonRect.right) + 'px';
    
    dropdown.classList.add('active');
    backdrop.classList.add('active');
}

    function handleDropdownAction(event, contactId, contactType, action) {
        event.stopPropagation();
        event.preventDefault();
        
        // Close dropdown
        const dropdown = document.getElementById(`dropdown-${contactId}-${contactType}`);
        if (dropdown) dropdown.classList.remove('active');
        document.getElementById('dropdownBackdrop').classList.remove('active');
        
        switch(action) {
            case 'archive':
            case 'unarchive':
                if (contactType === 'group') {
                    toggleArchiveGroup(contactId);
                } else {
                    toggleArchiveChat(contactId, contactType);
                }
                break;
            case 'mute':
            case 'unmute':
                if (contactType === 'group') {
                    toggleMuteGroup(contactId);
                } else {
                    toggleMuteChat(contactId, contactType);
                }
                break;
            case 'leave':
                if (contactType === 'group') {
                    leaveGroupById(contactId);
                }
                break;
            case 'delete':
                if (contactType === 'group') {
                    confirmDeleteGroup(contactId);
                } else {
                    confirmDeleteChat(contactId, contactType);
                }
                break;
        }
    }

// Close dropdown when clicking backdrop
document.getElementById('dropdownBackdrop').addEventListener('click', function() {
    closeAllDropdowns();
});

// Close dropdown on Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closeAllDropdowns();
});

function closeAllDropdowns() {
    document.querySelectorAll('.contact-dropdown.active').forEach(d => d.classList.remove('active'));
    document.getElementById('dropdownBackdrop').classList.remove('active');
}

// ============================================
// HELPER: Find contact card by ID and type
// ============================================
function findContactCard(contactId, contactType) {
    // The dropdown is a sibling of the card, not a child
    const dropdown = document.getElementById(`dropdown-${contactId}-${contactType}`);
    if (dropdown) {
        // Find the card that comes right before this dropdown
        const card = dropdown.previousElementSibling;
        if (card && card.classList.contains('contact-card')) {
            return card;
        }
    }
    
    // Fallback: find by onclick attribute
    return document.querySelector(`.contact-card[onclick*="openChat(${contactId}, '${contactType}')"]`);
}

// ============================================
// ARCHIVE CHAT - WITH MODAL CONFIRMATION
// ============================================
async function toggleArchiveChat(contactId, contactType) {
    const card = findContactCard(contactId, contactType);
    if (!card) return;
    
    const isCurrentlyArchived = card.classList.contains('archived');
    const newArchivedState = !isCurrentlyArchived;
    
    const action = newArchivedState ? 'archive' : 'unarchive';
    const confirmed = await showArchiveConfirmModal(contactId, contactType, action);
    
    if (!confirmed) return;
    
    try {
        const response = await fetch('/admin/messages/archive', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                contact_id: contactId,
                contact_type: contactType,
                archived: newArchivedState
            })
        });
        
        // Get the response as text first to see what's happening
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            showToast('Server returned invalid response: ' + responseText.substring(0, 100), 'error');
            return;
        }
        
        if (!response.ok || !data.success) {
            console.error('Archive failed:', data);
            showToast(data.error || data.message || 'Failed to archive chat', 'error');
            return;
        }
        
        // Update UI...
        if (newArchivedState) {
            card.classList.add('archived');
        } else {
            card.classList.remove('archived');
        }
        
        // Update the dropdown button text
        const dropdown = document.getElementById(`dropdown-${contactId}-${contactType}`);
        if (dropdown) {
            const archiveBtn = dropdown.querySelector('.dropdown-item:first-child');
            if (archiveBtn) {
                archiveBtn.innerHTML = `
                    <i class="fa-solid ${newArchivedState ? 'fa-box-open' : 'fa-box-archive'}"></i>
                    ${newArchivedState ? 'Unarchive Chat' : 'Archive Chat'}
                `;
                archiveBtn.setAttribute('onclick', 
                    `handleDropdownAction(event, ${contactId}, '${contactType}', '${newArchivedState ? 'unarchive' : 'archive'}')`
                );
            }
        }
        
        // Update allContacts data
        const contact = allContacts.find(c => c.id == contactId && c.type === contactType);
        if (contact) {
            contact.is_archived = newArchivedState;
        }
        
        // If archiving and currently viewing this chat, close it
        if (newArchivedState && currentChat && currentChat.id == contactId && currentChat.type === contactType) {
            currentChat = null;
            document.getElementById('noChatSelected').style.display = 'flex';
            document.getElementById('chatHeader').style.display = 'none';
            document.getElementById('chatMessages').style.display = 'none';
            document.getElementById('chatInput').style.display = 'none';
        } else if (!newArchivedState && currentChat && currentChat.id == contactId && currentChat.type === contactType) {
            // If unarchiving and currently viewing, refresh the input
            checkAndDisableArchivedChat(contactId, contactType);
        }
        
        // Reload conversations to reflect changes
        await loadConversations();
        
        showToast(`Chat ${newArchivedState ? 'archived' : 'unarchived'} successfully`);
        
    } catch (error) {
        console.error('Error archiving chat:', error);
        showToast('Failed to archive chat: ' + error.message, 'error');
    }
}

// ============================================
// ARCHIVE CONFIRMATION MODAL
// ============================================
function showArchiveConfirmModal(contactId, contactType, action) {
    return new Promise((resolve) => {
        let contactName = 'this chat';
        
        if (contactType === 'group') {
            const group = allGroups.find(g => g.id == contactId);
            if (group) {
                contactName = group.name;
            }
        } else {
            const contact = allContacts.find(c => c.id == contactId && c.type === contactType);
            if (contact) {
                contactName = contact.full_name;
            }
        }
        
        const actionText = action === 'archive' ? 'archive' : 'unarchive';
        const icon = action === 'archive' ? 'fa-box-archive' : 'fa-box-open';
        const color = action === 'archive' ? '#f59e0b' : '#10b981';
        
        // Create modal overlay
        const overlay = document.createElement('div');
        overlay.className = 'archive-modal-overlay';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: fadeIn 0.2s ease;
        `;
        
        overlay.innerHTML = `
            <div class="archive-modal" style="
                background: white;
                border-radius: 16px;
                width: 90%;
                max-width: 400px;
                padding: 24px;
                text-align: center;
                box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
                animation: scaleIn 0.3s ease;
                font-family: 'Poppins', sans-serif;
            ">
                <div style="
                    width: 64px;
                    height: 64px;
                    border-radius: 50%;
                    background: ${color}15;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 16px;
                ">
                    <i class="fa-solid ${icon}" style="font-size: 28px; color: ${color};"></i>
                </div>
                <h3 style="margin: 0 0 8px; font-size: 18px; color: #1f2937;">
                    ${action === 'archive' ? 'Archive Chat' : 'Unarchive Chat'}
                </h3>
                <p style="margin: 0 0 24px; font-size: 14px; color: #6b7280; line-height: 1.5;">
                    Are you sure you want to ${actionText} your conversation with <strong>${escapeHtml(contactName)}</strong>?
                    ${action === 'archive' ? '<br><small>You can find archived chats by clicking the archive icon.</small>' : ''}
                </p>
                <div style="display: flex; gap: 12px; justify-content: center;">
                    <button id="archiveModalCancel" style="
                        padding: 10px 24px;
                        border: 1px solid #e5e7eb;
                        border-radius: 8px;
                        background: white;
                        color: #374151;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 500;
                        transition: all 0.2s;
                    ">Cancel</button>
                    <button id="archiveModalConfirm" style="
                        padding: 10px 24px;
                        border: none;
                        border-radius: 8px;
                        background: ${color};
                        color: white;
                        cursor: pointer;
                        font-size: 14px;
                        font-weight: 500;
                        transition: all 0.2s;
                    ">${action === 'archive' ? 'Archive' : 'Unarchive'}</button>
                </div>
            </div>
        `;
        
        document.body.appendChild(overlay);
        
        // Add animation styles if not present
        if (!document.querySelector('#archive-modal-styles')) {
            const styles = document.createElement('style');
            styles.id = 'archive-modal-styles';
            styles.textContent = `
                @keyframes fadeIn {
                    from { opacity: 0; }
                    to { opacity: 1; }
                }
                @keyframes scaleIn {
                    from { transform: scale(0.9); opacity: 0; }
                    to { transform: scale(1); opacity: 1; }
                }
                .archive-modal-overlay .archive-modal button:hover {
                    opacity: 0.9;
                    transform: translateY(-1px);
                }
            `;
            document.head.appendChild(styles);
        }
        
        // Button handlers
        overlay.querySelector('#archiveModalCancel').onclick = () => {
            overlay.remove();
            resolve(false);
        };
        
        overlay.querySelector('#archiveModalConfirm').onclick = () => {
            overlay.remove();
            resolve(true);
        };
        
        // Close on overlay click
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) {
                overlay.remove();
                resolve(false);
            }
        });
        
        // Close on Escape
        const escHandler = (e) => {
            if (e.key === 'Escape') {
                overlay.remove();
                resolve(false);
                document.removeEventListener('keydown', escHandler);
            }
        };
        document.addEventListener('keydown', escHandler);
    });
}

// ============================================
// MUTE CHAT
// ============================================
async function toggleMuteChat(contactId, contactType) {
    const card = findContactCard(contactId, contactType);
    if (!card) return;
    
    const isCurrentlyMuted = card.classList.contains('muted');
    const newMutedState = !isCurrentlyMuted;
    
    try {
        const response = await fetch('/admin/messages/mute', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                contact_id: contactId,
                contact_type: contactType,
                muted: newMutedState
            })
        });
        
        if (!response.ok) throw new Error('Failed to mute chat');
        
        const data = await response.json();
        
        if (data.success) {
            if (newMutedState) {
                card.classList.add('muted');
                const nameEl = card.querySelector('.contact-name');
                if (nameEl) {
                    const text = nameEl.textContent.trim().replace(/🔕$/, '').trim();
                    nameEl.innerHTML = `${escapeHtml(text)} <span class="muted-indicator"><i class="fa-solid fa-bell-slash"></i></span>`;
                }
            } else {
                card.classList.remove('muted');
                const nameEl = card.querySelector('.contact-name');
                if (nameEl) {
                    const text = nameEl.textContent.trim().replace(/🔕$/, '').trim();
                    nameEl.textContent = text;
                }
            }
            
            // Update dropdown button
            const dropdown = card.querySelector('.contact-dropdown');
            if (dropdown) {
                const muteBtn = dropdown.querySelectorAll('.dropdown-item')[1];
                if (muteBtn) {
                    muteBtn.innerHTML = `
                        <i class="fa-solid ${newMutedState ? 'fa-bell' : 'fa-bell-slash'}"></i>
                        ${newMutedState ? 'Unmute Chat' : 'Mute Chat'}
                    `;
                    muteBtn.setAttribute('onclick',
                        `handleDropdownAction(event, ${contactId}, '${contactType}', '${newMutedState ? 'unmute' : 'mute'}')`
                    );
                }
            }
            
            showToast(`Chat ${newMutedState ? 'muted' : 'unmuted'} successfully`);
            
            // Update allContacts data
            const contact = allContacts.find(c => c.id == contactId && c.type === contactType);
            if (contact) {
                contact.is_muted = newMutedState;
            }
        }
    } catch (error) {
        console.error('Error muting chat:', error);
        showToast('Failed to mute chat', 'error');
    }
}

// ============================================
// DELETE CHAT
// ============================================
function confirmDeleteChat(contactId, contactType) {
    if (!confirm('Are you sure you want to delete this conversation? This action cannot be undone.')) {
        return;
    }
    
    closeAllDropdowns();
    deleteChat(contactId, contactType);
}

async function deleteChat(contactId, contactType) {
    try {
        const response = await fetch('/admin/messages/delete', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                contact_id: contactId,
                contact_type: contactType
            })
        });
        
        if (!response.ok) throw new Error('Failed to delete chat');
        
        const data = await response.json();
        
        if (data.success) {
            showToast('Chat deleted successfully');
            
            const card = findContactCard(contactId, contactType);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.transform = 'translateX(-100%)';
                card.style.opacity = '0';
                setTimeout(() => {
                    card.remove();
                    if (document.querySelectorAll('.contact-card').length === 0) {
                        renderContacts([]);
                    }
                }, 300);
            }
            
            if (currentChat && currentChat.id == contactId && currentChat.type === contactType) {
                currentChat = null;
                document.getElementById('noChatSelected').style.display = 'flex';
                document.getElementById('chatHeader').style.display = 'none';
                document.getElementById('chatMessages').style.display = 'none';
                document.getElementById('chatInput').style.display = 'none';
            }
            
            // Remove from allContacts
            allContacts = allContacts.filter(c => !(c.id == contactId && c.type === contactType));
        }
    } catch (error) {
        console.error('Error deleting chat:', error);
        showToast('Failed to delete chat', 'error');
    }
}

// ============================================
// TOAST NOTIFICATIONS
// ============================================
function showToast(message, type = 'success') {
    // Remove existing toasts
    const existingToast = document.querySelector('.custom-toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    const toast = document.createElement('div');
    toast.className = `custom-toast ${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fa-solid ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
        <button class="toast-close" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    `;
    
    Object.assign(toast.style, {
        position: 'fixed',
        bottom: '20px',
        right: '20px',
        background: type === 'success' ? '#10b981' : '#ef4444',
        color: 'white',
        padding: '12px 20px',
        borderRadius: '8px',
        boxShadow: '0 4px 12px rgba(0,0,0,0.15)',
        display: 'flex',
        alignItems: 'center',
        gap: '12px',
        zIndex: '9999',
        fontFamily: 'Poppins, sans-serif',
        fontSize: '0.9rem',
        minWidth: '250px',
        maxWidth: '400px',
        animation: 'slideInUp 0.3s ease',
        border: 'none'
    });
    
    document.body.appendChild(toast);
    
    // Auto-dismiss after 4 seconds
    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideOutDown 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }
    }, 4000);
}

// Add toast animations (add this once, outside the function)
if (!document.querySelector('#toast-styles')) {
    const toastStyles = document.createElement('style');
    toastStyles.id = 'toast-styles';
    toastStyles.textContent = `
        @keyframes slideInUp {
            from {
                transform: translateY(100%);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }
        @keyframes slideOutDown {
            from {
                transform: translateY(0);
                opacity: 1;
            }
            to {
                transform: translateY(100%);
                opacity: 0;
            }
        }
        .custom-toast .toast-content {
            display: flex;
            align-items: center;
            gap: 8px;
            flex: 1;
        }
        .custom-toast .toast-close {
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 0 4px;
            opacity: 0.8;
            transition: opacity 0.2s;
        }
        .custom-toast .toast-close:hover {
            opacity: 1;
        }
    `;
    document.head.appendChild(toastStyles);
}

    // ============================================
    // GROUP CHAT STATE
    // ============================================
    let selectedGroupMembers = [];
    let groupAvatarFile = null;
    let currentGroupInfo = null;
    let groupRealtimeChannel = null;
    let isGroupChat = false;

    // LOAD GROUP CONVERSATIONS
async function loadGroupConversations() {
    try {
        const url = archiveMode ? '/admin/messages/groups/list?archived=1' : '/admin/messages/groups/list';
        console.log('📋 Fetching groups from:', url);
        
        const response = await fetch(url);
        
        if (!response.ok) {
            console.warn('⚠️ Group chat endpoint returned', response.status);
            allGroups = [];
            return [];
        }
        
        const groups = await response.json();
        console.log('✅ Loaded', groups.length, 'groups');
        
        // ✅ Ensure each group has all required properties
        allGroups = Array.isArray(groups) ? groups.map(group => ({
            ...group,
            id: parseInt(group.id),
            type: 'group',
            is_archived: group.is_archived || false,
            is_muted: group.is_muted || false,
            unread_count: group.unread_count || 0,
            last_message: group.last_message || null,
            last_message_timestamp: group.last_message_timestamp || null,
            last_message_from_me: group.last_message_from_me || false,
            member_count: group.member_count || 0,
            name: group.name || 'Unnamed Channel',
            initials: group.initials || 'GC',
            avatar: group.avatar || null,
            created_by: group.created_by || null,
            created_by_name: group.created_by_name || 'Unknown',
        })) : [];
        
        return allGroups;
    } catch (error) {
        console.warn('⚠️ Error loading groups:', error.message);
        allGroups = [];
        return [];
    }
}

// OPEN GROUP CHAT
async function openGroupChat(groupId) {
    lastMessageId = 0;
    currentChat = { id: groupId, type: 'group' };
    isGroupChat = true;
    
    // Hide typing indicator
    hideTypingIndicator();
    clearTimeout(typingTimeout);
    clearTimeout(typingIndicatorTimeout);
    
    // Show chat panel
    document.getElementById('noChatSelected').style.display = 'none';
    document.getElementById('chatHeader').style.display = 'flex';
    document.getElementById('chatMessages').style.display = 'block';
    document.getElementById('chatInput').style.display = 'flex';
    
    // Disable input while loading
    const input = document.getElementById('messageInput');
    const sendBtn = document.querySelector('.btn-send');
    const attachBtn = document.querySelector('.btn-attach');
    const emojiBtn = document.querySelector('.btn-emoji');
    
    input.disabled = true;
    input.placeholder = 'Loading channel...';
    sendBtn.disabled = true;
    sendBtn.style.opacity = '0.5';
    sendBtn.style.cursor = 'not-allowed';
    attachBtn.disabled = true;
    attachBtn.style.opacity = '0.5';
    attachBtn.style.cursor = 'not-allowed';
    emojiBtn.disabled = true;
    emojiBtn.style.opacity = '0.5';
    emojiBtn.style.cursor = 'not-allowed';
    
    // Update header
    const group = allGroups.find(g => g.id == groupId);
    const chatAvatar = document.getElementById('chatAvatar');
    const chatName = document.getElementById('chatName');
    const chatStatus = document.getElementById('chatStatus');
    
    if (group) {
        if (group.avatar) {
            chatAvatar.innerHTML = `<img src="${group.avatar}" alt="${escapeHtml(group.name)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
        } else {
            chatAvatar.textContent = group.initials || 'G';
            chatAvatar.style.background = 'linear-gradient(135deg, #8b5cf6, #6d28d9)';
            chatAvatar.style.color = '#ffffff';
        }
        
        chatName.innerHTML = `${escapeHtml(group.name)} <span class="group-badge" style="font-size: 0.65rem; background: #8b5cf6; color: white; padding: 2px 10px; border-radius: 12px; margin-left: 8px; font-weight: 600;"><i class="fa-solid fa-users"></i> ${group.member_count || 0}</span>`;
        
        chatStatus.innerHTML = `
            <span class="status-dot"></span> 
            ${group.member_count || 0} members
        `;
    }
    
    // Load messages
    await loadGroupMessages(groupId);
    
    // Mark as read
    await markGroupMessagesAsRead(groupId);
    
    // Check archive status and update input
    checkAndDisableGroupChat(groupId);
    
    // Subscribe to real-time
    subscribeToGroupMessages(groupId);
    
    // Show on mobile
    showChatOnMobile();
}

// LOAD GROUP MESSAGES
async function loadGroupMessages(groupId) {
    const container = document.getElementById('chatMessages');
    container.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading messages...</div>';
    
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}/messages`);
        if (!response.ok) throw new Error('Failed to load messages');
        
        const messages = await response.json();
        renderGroupMessages(messages);
        scrollToBottom();
        
        // Enable input
        const input = document.getElementById('messageInput');
        const sendBtn = document.querySelector('.btn-send');
        const attachBtn = document.querySelector('.btn-attach');
        const emojiBtn = document.querySelector('.btn-emoji');
        
        input.disabled = false;
        input.placeholder = 'Type a message here...';
        sendBtn.disabled = false;
        sendBtn.style.opacity = '1';
        sendBtn.style.cursor = 'pointer';
        attachBtn.disabled = false;
        attachBtn.style.opacity = '1';
        attachBtn.style.cursor = 'pointer';
        emojiBtn.disabled = false;
        emojiBtn.style.opacity = '1';
        emojiBtn.style.cursor = 'pointer';
        
        input.focus();
        
    } catch (error) {
        console.error('Error loading group messages:', error);
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-exclamation-circle"></i>
                <h3>Error loading messages</h3>
                <p>Please try again</p>
            </div>
        `;
    }
}

// RENDER GROUP MESSAGES
function renderGroupMessages(messages) {
    const container = document.getElementById('chatMessages');
    
    if (!messages || messages.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="fa-solid fa-users"></i>
                <h3>No messages yet</h3>
                <p>Send the first message to start the conversation</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    let lastDate = null;
    
    messages.forEach(msg => {
        // ✅ Handle both string and Date objects for created_at
        let msgDate;
        if (typeof msg.created_at === 'string') {
            // Make sure we handle UTC properly
            if (!msg.created_at.endsWith('Z') && !msg.created_at.includes('+')) {
                msgDate = new Date(msg.created_at + 'Z');
            } else {
                msgDate = new Date(msg.created_at);
            }
        } else {
            msgDate = new Date(msg.created_at);
        }
        
        const localDateStr = msgDate.toLocaleDateString();
        
        if (localDateStr !== lastDate) {
            html += `<div class="date-divider"><span>${formatDateDivider(msgDate)}</span></div>`;
            lastDate = localDateStr;
        }
        
        const isSent = msg.sender_id == adminId;
        const senderName = msg.sender_name || 'Unknown';
        
        html += `
            <div class="message-group ${isSent ? 'sent' : 'received'}" data-msg-id="${msg.id}">
                ${!isSent ? `<div class="sender-name" style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem; margin-left: 0.75rem;">${escapeHtml(senderName)}</div>` : ''}
                <div class="message-bubble">
                    <p>${escapeHtml(msg.content)}</p>
                    ${msg.attachments && msg.attachments.length > 0 ? renderAttachments(msg.attachments, isSent) : ''}
                    <span class="msg-time">
                        ${msg.time || formatTime(msgDate)}
                    </span>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function appendGroupMessage(msg) {
    const container = document.getElementById('chatMessages');
    
    if (msg.id && !msg.id.toString().startsWith('temp-')) {
        const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
        if (existingMsg) return;
    }
    
    const isSent = msg.sender_id == adminId;
    const displayTime = msg.time || formatTime(new Date(msg.created_at));
    const senderName = msg.sender_name || 'Unknown';
    
    const messageHtml = `
        <div class="message-group ${isSent ? 'sent' : 'received'}" ${msg.id ? `data-msg-id="${msg.id}"` : ''}>
            ${!isSent ? `<div class="sender-name" style="font-size: 0.75rem; font-weight: 600; color: #6b7280; margin-bottom: 0.25rem; margin-left: 0.75rem;">${escapeHtml(senderName)}</div>` : ''}
            <div class="message-bubble">
                <p>${escapeHtml(msg.content)}</p>
                ${msg.attachments && msg.attachments.length > 0 ? renderAttachments(msg.attachments, isSent) : ''}
                <span class="msg-time">
                    ${displayTime}
                </span>
            </div>
        </div>
    `;
    
    const emptyState = container.querySelector('.empty-state');
    if (emptyState) emptyState.remove();
    
    container.insertAdjacentHTML('beforeend', messageHtml);
}

// SEND GROUP MESSAGE
async function sendGroupMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!content || !currentChat || currentChat.type !== 'group') return;
    
    // Check if group is archived
    const group = allGroups.find(g => g.id == currentChat.id);
    if (group && group.is_archived) {
        showToast('Cannot send messages to archived channel. Unarchive it first.', 'error');
        return;
    }
    
    clearTimeout(typingTimeout);
    input.value = '';
    input.focus();
    
    const now = new Date();
    const tempId = 'temp-' + Date.now();
    const tempMessage = {
        id: tempId,
        content: content,
        sender_id: adminId,
        sender_type: 'admin',
        sender_name: 'You',
        group_chat_id: currentChat.id,
        created_at: now.toISOString(),
        time: formatTime(now),
        attachments: []
    };
    
    appendGroupMessage(tempMessage);
    scrollToBottom();
    
    // Broadcast typing stopped
    if (typingChannel) {
        typingChannel.send({
            type: 'broadcast',
            event: 'typing',
            payload: {
                sender_id: adminId,
                sender_type: 'admin',
                receiver_id: currentChat.id,
                receiver_type: 'group',
                stopped: true,
                timestamp: now.toISOString(),
            },
        });
    }
    
    try {
        const response = await fetch(`/admin/messages/groups/${currentChat.id}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ content: content })
        });
        
        if (!response.ok) throw new Error('Failed to send message');
        
        const data = await response.json();
        if (data.success) {
            const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
            if (tempElement) tempElement.remove();
            
            appendGroupMessage(data.message);
            scrollToBottom();
            
            // Update group in list
            updateGroupWithNewMessage(currentChat.id, content, data.message.created_at);
        }
    } catch (error) {
        console.error('Error sending group message:', error);
        const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
        if (tempElement) tempElement.remove();
        input.value = content;
        showToast('Failed to send message. Please try again.', 'error');
    }
}

// UPDATE GROUP WITH NEW MESSAGE
function updateGroupWithNewMessage(groupId, content, timestamp) {
    const group = allGroups.find(g => g.id == groupId);
    if (!group) return;
    
    const utcTimestamp = ensureUTCTimestamp(timestamp);
    const messageDate = new Date(utcTimestamp);
    
    group.last_message = content;
    group.last_message_timestamp = utcTimestamp;
    group.last_message_from_me = true;
    
    // Move to top
    allGroups = allGroups.filter(g => g.id != groupId);
    allGroups.unshift(group);
    
    applyFilter();
}

// MARK GROUP MESSAGES AS READ
async function markGroupMessagesAsRead(groupId) {
    const group = allGroups.find(g => g.id == groupId);
    if (group) {
        group.unread_count = 0;
        updateUnreadBadge();
        applyFilter();
    }
}

// REAL-TIME GROUP MESSAGES (Supabase)
function subscribeToGroupMessages(groupId) {
    // Unsubscribe from previous
    if (groupRealtimeChannel) {
        supabaseClient.removeChannel(groupRealtimeChannel);
        groupRealtimeChannel = null;
    }
    
    if (!supabaseClient) return;
    
    groupRealtimeChannel = supabaseClient
        .channel(`group-messages-${groupId}`)
        .on('postgres_changes', {
            event: 'INSERT',
            schema: 'public',
            table: 'group_messages',
            filter: `group_chat_id=eq.${groupId}`,
        }, (payload) => {
            console.log('📨 New group message:', payload.new);
            handleNewGroupMessage(payload.new);
        })
        .subscribe((status) => {
            if (status === 'SUBSCRIBED') {
                console.log(`✅ Subscribed to group ${groupId} messages`);
            }
        });
}

function handleNewGroupMessage(message) {
    if (message.sender_id == adminId && message.sender_type === 'admin') return;
    
    // Check if already displayed
    const existing = document.querySelector(`[data-msg-id="${message.id}"]`);
    if (existing) return;
    
    // Decrypt content
    decryptContent(message.content, message.sender_type, 'admin').then(decryptedContent => {
        // Get sender name
        let senderName = 'Unknown';
        if (message.sender_type === 'alumni') {
            const contact = allContacts.find(c => c.id == message.sender_id && c.type === 'alumni');
            if (contact) {
                senderName = contact.full_name;
            } else {
                fetchContactInfo(message.sender_id, 'alumni', message.content, message.created_at, true);
                senderName = 'Alumni #' + message.sender_id;
            }
        } else if (message.sender_type === 'admin') {
            const contact = allContacts.find(c => c.id == message.sender_id && c.type === 'admin');
            if (contact) {
                senderName = contact.full_name;
            } else {
                senderName = 'Admin #' + message.sender_id;
            }
        }
        
        const formattedMessage = {
            id: message.id,
            content: decryptedContent,
            sender_id: message.sender_id,
            sender_type: message.sender_type,
            sender_name: senderName,
            group_chat_id: message.group_chat_id,
            created_at: message.created_at,
            time: formatTime(new Date(message.created_at)),
            attachments: []
        };
        
        appendGroupMessage(formattedMessage);
        scrollToBottom();
        
        if (currentChat && currentChat.id == message.group_chat_id) {
            markGroupMessagesAsRead(message.group_chat_id);
        }
        
        updateGroupWithNewMessage(message.group_chat_id, decryptedContent, message.created_at);
    });
}

// CHECK AND DISABLE GROUP CHAT IF ARCHIVED
function checkAndDisableGroupChat(groupId) {
    const group = allGroups.find(g => g.id == groupId);
    const isArchived = group ? group.is_archived : false;
    
    const input = document.getElementById('messageInput');
    const sendBtn = document.querySelector('.btn-send');
    const attachBtn = document.querySelector('.btn-attach');
    const emojiBtn = document.querySelector('.btn-emoji');
    
    if (isArchived) {
        input.disabled = true;
        input.placeholder = 'This channel is archived. Unarchive to send messages.';
        sendBtn.disabled = true;
        sendBtn.style.opacity = '0.5';
        sendBtn.style.cursor = 'not-allowed';
        attachBtn.disabled = true;
        attachBtn.style.opacity = '0.5';
        attachBtn.style.cursor = 'not-allowed';
        emojiBtn.disabled = true;
        emojiBtn.style.opacity = '0.5';
        emojiBtn.style.cursor = 'not-allowed';
    } else {
        input.disabled = false;
        input.placeholder = 'Type a message here...';
        sendBtn.disabled = false;
        sendBtn.style.opacity = '1';
        sendBtn.style.cursor = 'pointer';
        attachBtn.disabled = false;
        attachBtn.style.opacity = '1';
        attachBtn.style.cursor = 'pointer';
        emojiBtn.disabled = false;
        emojiBtn.style.opacity = '1';
        emojiBtn.style.cursor = 'pointer';
    }
}

// GROUP ARCHIVE/MUTE
async function toggleArchiveGroup(groupId) {
    const group = allGroups.find(g => g.id == groupId);
    if (!group) return;
    
    const newState = !group.is_archived;
    const action = newState ? 'archive' : 'unarchive';
    const confirmed = await showArchiveConfirmModal(groupId, 'group', action);
    if (!confirmed) return;
    
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}/archive`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ archived: newState })
        });
        
        const data = await response.json();
        if (data.success) {
            group.is_archived = newState;
            
            if (newState && currentChat && currentChat.id == groupId && currentChat.type === 'group') {
                currentChat = null;
                document.getElementById('noChatSelected').style.display = 'flex';
                document.getElementById('chatHeader').style.display = 'none';
                document.getElementById('chatMessages').style.display = 'none';
                document.getElementById('chatInput').style.display = 'none';
            }
            
            applyFilter();
            showToast(`Channel ${newState ? 'archived' : 'unarchived'} successfully`);
        } else {
            showToast(data.error || 'Failed to archive channel', 'error');
        }
    } catch (error) {
        console.error('Error toggling archive:', error);
        showToast('Failed to archive channel', 'error');
    }
}

async function toggleMuteGroup(groupId) {
    const group = allGroups.find(g => g.id == groupId);
    if (!group) return;
    
    const newState = !group.is_muted;
    
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}/mute`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ muted: newState })
        });
        
        const data = await response.json();
        if (data.success) {
            group.is_muted = newState;
            applyFilter();
            showToast(`Channel ${newState ? 'muted' : 'unmuted'} successfully`);
        } else {
            showToast(data.error || 'Failed to mute channel', 'error');
        }
    } catch (error) {
        console.error('Error toggling mute:', error);
        showToast('Failed to mute channel', 'error');
    }
}

// LEAVE GROUP BY ID
async function leaveGroupById(groupId) {
    if (!confirm('Are you sure you want to leave this channel?')) return;
    
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}/leave`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            
            allGroups = allGroups.filter(g => g.id != groupId);
            
            if (currentChat && currentChat.id == groupId && currentChat.type === 'group') {
                currentChat = null;
                document.getElementById('noChatSelected').style.display = 'flex';
                document.getElementById('chatHeader').style.display = 'none';
                document.getElementById('chatMessages').style.display = 'none';
                document.getElementById('chatInput').style.display = 'none';
            }
            
            applyFilter();
        } else {
            showToast(data.error || 'Failed to leave channel', 'error');
        }
    } catch (error) {
        console.error('Error leaving group:', error);
        showToast('Failed to leave channel', 'error');
    }
}

// DELETE GROUP
function confirmDeleteGroup(groupId) {
    if (!confirm('Are you sure you want to delete this channel? This action cannot be undone.')) return;
    deleteGroup(groupId);
}

async function deleteGroup(groupId) {
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            showToast('Channel deleted successfully');
            
            allGroups = allGroups.filter(g => g.id != groupId);
            
            if (currentChat && currentChat.id == groupId && currentChat.type === 'group') {
                currentChat = null;
                document.getElementById('noChatSelected').style.display = 'flex';
                document.getElementById('chatHeader').style.display = 'none';
                document.getElementById('chatMessages').style.display = 'none';
                document.getElementById('chatInput').style.display = 'none';
            }
            
            applyFilter();
        } else {
            showToast(data.error || 'Failed to delete channel', 'error');
        }
    } catch (error) {
        console.error('Error deleting group:', error);
        showToast('Failed to delete channel', 'error');
    }
}

async function createGroupChat() {
    const name = document.getElementById('groupNameInput').value.trim();
    const members = selectedGroupMembers
        .filter(m => m.id != adminId) // Remove current admin
        .map(m => ({ 
            id: m.id, 
            user_type: m.user_type 
        }));
    
    if (!name) {
        showToast('Please enter a channel name', 'error');
        return;
    }
    if (members.length < 2) {
        showToast('Please add at least 2 members', 'error');
        return;
    }
    
    const createBtn = document.getElementById('createGroupBtn');
    createBtn.disabled = true;
    createBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';
    
    const formData = new FormData();
    formData.append('name', name);
    formData.append('members', JSON.stringify(members));
    if (groupAvatarFile) {
        formData.append('avatar', groupAvatarFile);
    }
    
    try {
        const response = await fetch('/admin/messages/groups/create', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            closeNewGroupModal();
            showToast('Channel created successfully');
            
            if (data.group) {
                allGroups.unshift(data.group);
                applyFilter();
                openGroupChat(data.group.id);
            }
            
            // Reset form
            selectedGroupMembers = [];
            groupAvatarFile = null;
            document.getElementById('groupNameInput').value = '';
            document.getElementById('selectedMembers').innerHTML = '<span style="color: var(--gray-400); font-size: 0.8rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>';
            document.getElementById('groupAvatarPreview').innerHTML = '<i class="fa-solid fa-users"></i>';
            
        } else {
            showToast(data.error || 'Failed to create channel', 'error');
        }
    } catch (error) {
        console.error('Error creating group:', error);
        showToast('Failed to create channel', 'error');
    } finally {
        createBtn.disabled = false;
        createBtn.innerHTML = '<i class="fa-solid fa-circle-plus" style="color: white; font-size: 0.9rem; display: inline-flex; align-items: center; justify-content: center;"></i> Create Channel';
    }
}

// GROUP INFO FUNCTIONS
async function openGroupInfo(groupId) {
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}/info`);
        if (!response.ok) throw new Error('Failed to load group info');
        
        const data = await response.json();
        currentGroupInfo = data;
        
        const avatarEl = document.getElementById('groupInfoAvatar');
        if (data.avatar) {
            avatarEl.innerHTML = `<img src="${data.avatar}" alt="${escapeHtml(data.name)}" style="width: 100%; height: 100%; object-fit: cover;">`;
        } else {
            avatarEl.innerHTML = data.initials || '<i class="fa-solid fa-users"></i>';
            avatarEl.style.background = 'linear-gradient(135deg, #8b5cf6, #6d28d9)';
            avatarEl.style.color = '#ffffff';
        }
        
        document.getElementById('groupInfoName').textContent = data.name;
        document.getElementById('groupInfoMemberCount').textContent = `${data.member_count} members`;
        
        const adminActions = document.getElementById('groupAdminActions');
        const addMembersBtn = document.getElementById('addMembersBtn');
        if (data.is_admin) {
            adminActions.style.display = 'flex';
            addMembersBtn.style.display = 'flex';
        } else {
            adminActions.style.display = 'none';
            addMembersBtn.style.display = 'none';
        }
        
        renderGroupMembers(data.members);
        
        document.getElementById('groupInfoModal').classList.add('active');
        
    } catch (error) {
        console.error('Error opening group info:', error);
        showToast('Failed to load channel info', 'error');
    }
}

function renderGroupMembers(members) {
    const container = document.getElementById('groupMembersList');
    
    if (!members || members.length === 0) {
        container.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 1rem;">No members found</p>';
        return;
    }
    
    container.innerHTML = members.map(member => {
        const isAdmin = member.role === 'admin';
        const isSelf = member.id == adminId;
        const isCreator = currentGroupInfo && currentGroupInfo.created_by == member.id;
        
        return `
            <div class="group-member-item" style="display: flex; align-items: center; padding: 0.625rem; border-radius: var(--radius-lg); transition: all var(--transition);">
                <div style="position: relative; flex-shrink: 0; margin-right: 0.75rem;">
                    ${member.avatar 
                        ? `<img src="${member.avatar}" alt="${escapeHtml(member.full_name)}" style="width: 36px; height: 36px; border-radius: 50%; object-fit: cover;">`
                        : `<div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light)); color: var(--nu-gold); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem;">${member.initials || '??'}</div>`
                    }
                    ${member.is_online ? `<span style="position: absolute; bottom: 0; right: 0; width: 10px; height: 10px; border-radius: 50%; background: var(--success); border: 2px solid var(--white);"></span>` : ''}
                </div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; font-size: 0.9rem; color: var(--gray-800); display: flex; align-items: center; gap: 0.5rem;">
                        ${escapeHtml(member.full_name)}
                        ${isSelf ? '<span style="font-size: 0.65rem; color: var(--gray-500); font-weight: 400;">(You)</span>' : ''}
                        ${isCreator ? '<span style="font-size: 0.6rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 1px 8px; border-radius: 8px; font-weight: 700;">Creator</span>' : ''}
                    </div>
                    <div style="font-size: 0.7rem; color: var(--gray-500);">
                        ${isAdmin ? 'Channel Admin' : 'Member'}
                        ${member.is_online ? ' • Online' : ' • Offline'}
                    </div>
                </div>
                ${currentGroupInfo && currentGroupInfo.is_admin && !isSelf && !isCreator ? `
                    <div style="display: flex; gap: 0.375rem;">
                        <button onclick="updateMemberRole(${member.id}, '${isAdmin ? 'alumni' : 'admin'}')" 
                            style="background: none; border: 1px solid var(--gray-200); border-radius: var(--radius); padding: 0.25rem 0.5rem; cursor: pointer; font-size: 0.7rem; color: var(--gray-600); transition: all var(--transition);"
                            title="${isAdmin ? 'Demote to Member' : 'Promote to Admin'}">
                            ${isAdmin ? '<i class="fa-solid fa-user"></i>' : '<i class="fa-solid fa-crown"></i>'}
                        </button>
                        <button onclick="removeGroupMember(${member.id})" 
                            style="background: none; border: 1px solid var(--danger); border-radius: var(--radius); padding: 0.25rem 0.5rem; cursor: pointer; font-size: 0.7rem; color: var(--danger); transition: all var(--transition);"
                            title="Remove Member">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
}

// GROUP MEMBER MANAGEMENT
async function updateMemberRole(memberId, newRole) {
    if (!currentGroupInfo) return;
    
    try {
        const response = await fetch(`/admin/messages/groups/${currentGroupInfo.id}/members/${memberId}/role`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ role: newRole })
        });
        
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            openGroupInfo(currentGroupInfo.id);
        } else {
            showToast(data.error || 'Failed to update role', 'error');
        }
    } catch (error) {
        console.error('Error updating role:', error);
        showToast('Failed to update role', 'error');
    }
}

async function removeGroupMember(memberId) {
    if (!currentGroupInfo) return;
    if (!confirm('Are you sure you want to remove this member from the channel?')) return;
    
    try {
        const response = await fetch(`/admin/messages/groups/${currentGroupInfo.id}/members/${memberId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            openGroupInfo(currentGroupInfo.id);
            loadGroupConversations();
        } else {
            showToast(data.error || 'Failed to remove member', 'error');
        }
    } catch (error) {
        console.error('Error removing member:', error);
        showToast('Failed to remove member', 'error');
    }
}

// LEAVE GROUP (from modal)
async function leaveGroup() {
    if (!currentGroupInfo) return;
    if (!confirm('Are you sure you want to leave this channel?')) return;
    
    try {
        const response = await fetch(`/admin/messages/groups/${currentGroupInfo.id}/leave`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            closeGroupInfoModal();
            
            allGroups = allGroups.filter(g => g.id != currentGroupInfo.id);
            
            if (currentChat && currentChat.id == currentGroupInfo.id) {
                currentChat = null;
                document.getElementById('noChatSelected').style.display = 'flex';
                document.getElementById('chatHeader').style.display = 'none';
                document.getElementById('chatMessages').style.display = 'none';
                document.getElementById('chatInput').style.display = 'none';
            }
            
            applyFilter();
        } else {
            showToast(data.error || 'Failed to leave channel', 'error');
        }
    } catch (error) {
        console.error('Error leaving group:', error);
        showToast('Failed to leave channel', 'error');
    }
}

// GROUP MODAL FUNCTIONS
function openNewGroupModal() {
    document.getElementById('newGroupModal').classList.add('active');
    document.getElementById('groupNameInput').value = '';
    document.getElementById('groupMemberSearch').value = '';
    document.getElementById('groupMemberResults').innerHTML = '';
    selectedGroupMembers = [];
    groupAvatarFile = null;
    document.getElementById('groupAvatarPreview').innerHTML = '<i class="fa-solid fa-users"></i>';
    document.getElementById('selectedMembers').innerHTML = '<span style="color: var(--gray-400); font-size: 0.8rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>';
    setTimeout(() => {
        document.getElementById('groupNameInput').focus();
    }, 100);
}

function closeNewGroupModal() {
    document.getElementById('newGroupModal').classList.remove('active');
}

function closeGroupInfoModal() {
    document.getElementById('groupInfoModal').classList.remove('active');
}

// GROUP AVATAR HANDLERS
function handleGroupAvatarSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file.');
        event.target.value = '';
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('Image must be less than 2MB.');
        event.target.value = '';
        return;
    }
    
    groupAvatarFile = file;
    const preview = document.getElementById('groupAvatarPreview');
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.innerHTML = `<img src="${e.target.result}" style="width: 100%; height: 100%; object-fit: cover;">`;
        preview.style.background = 'transparent';
    };
    reader.readAsDataURL(file);
}

function handleGroupAvatarEdit(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    if (!file.type.startsWith('image/')) {
        alert('Please select an image file.');
        event.target.value = '';
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('Image must be less than 2MB.');
        event.target.value = '';
        return;
    }
    
    const formData = new FormData();
    formData.append('avatar', file);
    formData.append('_method', 'PUT');
    
    fetch(`/admin/messages/groups/${currentGroupInfo.id}`, {
        method: 'POST', // Laravel spoofs PUT with _method
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Avatar updated successfully');
            openGroupInfo(currentGroupInfo.id);
            loadGroupConversations();
        } else {
            showToast(data.error || 'Failed to update avatar', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating avatar:', error);
        showToast('Failed to update avatar', 'error');
    });
    
    event.target.value = '';
}

function editGroupName() {
    const newName = prompt('Enter new channel name:', currentGroupInfo?.name || '');
    if (!newName || newName.trim() === '') return;
    if (newName.trim() === currentGroupInfo?.name) return;
    
    fetch(`/admin/messages/groups/${currentGroupInfo.id}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: newName.trim() })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showToast('Channel name updated successfully');
            openGroupInfo(currentGroupInfo.id);
            loadGroupConversations();
            if (currentChat && currentChat.id == currentGroupInfo.id) {
                document.getElementById('chatName').textContent = data.group.name;
            }
        } else {
            showToast(data.error || 'Failed to update name', 'error');
        }
    })
    .catch(error => {
        console.error('Error updating name:', error);
        showToast('Failed to update name', 'error');
    });
}

function searchAlumniForGroup() {
    const query = document.getElementById('groupMemberSearch').value.trim();
    const resultsContainer = document.getElementById('groupMemberResults');
    
    if (query.length < 2) {
        resultsContainer.innerHTML = '';
        return;
    }
    
    resultsContainer.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</div>';
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/admin/messages/groups/search/alumni?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (!response.ok) {
                resultsContainer.innerHTML = `<p style="color: var(--danger); text-align: center;">Error searching</p>`;
                return;
            }
            
            if (data.length === 0) {
                resultsContainer.innerHTML = '<p style="color: var(--gray-400); text-align: center;">No users found</p>';
                return;
            }
            
            // Filter out already selected members
            const filtered = data.filter(a => !selectedGroupMembers.some(m => m.id == a.id && m.user_type === a.user_type));
            
            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<p style="color: var(--gray-400); text-align: center;">All results already selected</p>';
                return;
            }
            
            resultsContainer.innerHTML = filtered.map(a => `
                <div class="alumni-item" onclick="addGroupMember(${a.id}, '${escapeHtml(a.full_name)}', '${escapeHtml(a.initials)}', '${a.user_type}', '${a.admin_role || null}')">
                    ${a.avatar 
                        ? `<img src="${a.avatar}" class="contact-avatar-img" alt="${escapeHtml(a.full_name)}">`
                        : `<div class="alumni-avatar">${a.initials}</div>`
                    }
                    <div class="alumni-info">
                        <div class="name">${escapeHtml(a.full_name)}</div>
                        <div class="details">
                            ${a.user_type === 'admin' 
                                ? `<span class="admin-badge" style="font-size: 0.65rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 2px 12px; border-radius: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;">${escapeHtml(a.admin_role || 'Admin')}</span>`
                                : `Batch ${a.batch} | ${a.program || 'N/A'}`
                            }
                        </div>
                    </div>
                    ${a.is_online ? '<span class="online-dot" title="Online"></span>' : ''}
                </div>
            `).join('');        
                        
        } catch (error) {
            console.error('Error searching:', error);
            resultsContainer.innerHTML = '<p style="color: var(--danger); text-align: center;">Error searching. Please try again.</p>';
        }
    }, 300);
}

function addGroupMember(id, name, initials, userType = 'alumni', adminRole = null) {
    // Check if already selected
    if (selectedGroupMembers.some(m => m.id == id && m.user_type === userType)) {
        showToast('This user is already selected', 'info');
        return;
    }
    
    selectedGroupMembers.push({ 
        id: id, 
        name: name, 
        initials: initials, 
        user_type: userType,
        admin_role: adminRole  
    });
    
    renderSelectedGroupMembers();
    document.getElementById('groupMemberSearch').value = '';
    document.getElementById('groupMemberResults').innerHTML = '';
}

function removeGroupMember(id, userType) {
    selectedGroupMembers = selectedGroupMembers.filter(m => !(m.id == id && m.user_type === userType));
    renderSelectedGroupMembers();
}

function renderSelectedGroupMembers() {
    const container = document.getElementById('selectedMembers');
    
    if (selectedGroupMembers.length === 0) {
        container.innerHTML = '<span style="color: var(--gray-400); font-size: 0.8rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>';
        return;
    }
    
    container.innerHTML = selectedGroupMembers.map(m => `
        <span class="selected-member-tag" style="display: inline-flex; align-items: center; gap: 0.375rem; background: var(--nu-blue-soft); color: var(--nu-blue); padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
            ${escapeHtml(m.name)}
            ${m.user_type === 'admin' ? `<span style="font-size: 0.55rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 1px 10px; border-radius: 8px; font-weight: 700; margin-left: 4px; text-transform: uppercase; letter-spacing: 0.5px;">${escapeHtml(m.admin_role || 'Admin')}</span>` : ''}
            <span class="remove-member" onclick="removeGroupMember(${m.id}, '${m.user_type}')" style="cursor: pointer; color: var(--gray-400); font-size: 0.7rem; margin-left: 2px;">✕</span>
        </span>
    `).join('');
}

// OPEN ADD MEMBERS MODAL
async function openAddMembersModal() {
    if (!currentGroupInfo) return;
    
    // Simple prompt for now - you can replace with a proper modal later
    const searchQuery = prompt('Enter alumni name or ID to add to this channel:');
    if (!searchQuery || searchQuery.trim() === '') return;
    
    try {
        const response = await fetch(`/admin/messages/groups/search/alumni?q=${encodeURIComponent(searchQuery)}`);
        const data = await response.json();
        
        if (data.length === 0) {
            showToast('No alumni found', 'error');
            return;
        }
        
        const existingMemberIds = currentGroupInfo.members.map(m => m.id);
        const available = data.filter(a => !existingMemberIds.includes(a.id));
        
        if (available.length === 0) {
            showToast('All found alumni are already in this channel', 'error');
            return;
        }
        
        // Just add the first one for simplicity
        const memberToAdd = available[0];
        if (confirm(`Add ${memberToAdd.full_name} to this channel?`)) {
            await addGroupMembers([memberToAdd.id]);
        }
    } catch (error) {
        console.error('Error searching alumni:', error);
        showToast('Failed to search alumni', 'error');
    }
}

async function addGroupMembers(memberIds) {
    if (!currentGroupInfo) return;
    
    try {
        const response = await fetch(`/admin/messages/groups/${currentGroupInfo.id}/members/add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({ member_ids: memberIds })
        });
        
        const data = await response.json();
        if (data.success) {
            showToast(data.message);
            openGroupInfo(currentGroupInfo.id);
            loadGroupConversations();
        } else {
            showToast(data.error || 'Failed to add members', 'error');
        }
    } catch (error) {
        console.error('Error adding members:', error);
        showToast('Failed to add members', 'error');
    }
}

</script>

</body>
</html>

{{-- This is the admin_messages.blade.php --}}