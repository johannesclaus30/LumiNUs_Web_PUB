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

    <link rel="stylesheet" href="/css/media-preview-modal.css">

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

/* Lock the static headers so they don't squish */
.panel-header, 
.search-container, 
.filter-tabs {
    flex-shrink: 0;
    background: var(--white);
    z-index: 10; 
}

/* Scrollable Contacts List - Fix overflow conflicts */
.contacts-list {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden; /* Overrides the previous visible !important conflict */
    padding: 0.5rem;
    background: var(--white);
    min-height: 0; /* Crucial for flex child scrolling */
    scroll-behavior: smooth;
}

/* Contact Card Flex Alignment */
.contact-card {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 1rem;
    cursor: pointer;
    transition: all var(--transition);
    border-radius: var(--radius-xl);
    margin-bottom: 0.25rem;
    border: 2px solid transparent;
    position: relative;
}

/* Contact Details Container */
.contact-details {
    flex: 1;
    min-width: 0; /* Prevents long text from breaking the card width */
    display: flex;
    flex-direction: column;
    justify-content: center;
    gap: 0.15rem;
}

/* Ensure actions stay pinned to the right */
.contact-actions {
    flex-shrink: 0; 
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ============================================
   CHAT INPUT - TEXTAREA AUTO-EXPAND (FIXED)
   ============================================ */

/* Override the input-wrapper to use flex-end alignment */
.chat-input-container .input-wrapper {
    align-items: flex-end !important;
    padding: 0.375rem 0.5rem 0.375rem 1.25rem !important;
    transition: all var(--transition);
}

/* Style the textarea specifically */
.chat-input-container .input-wrapper textarea#messageInput {
    flex: 1;
    border: none;
    background: transparent;
    font-family: inherit;
    font-size: 0.9375rem;
    color: var(--gray-700);
    padding: 0.625rem 0;
    font-weight: 500;
    resize: none;
    outline: none;
    line-height: 1.5;
    max-height: 150px;
    min-height: 24px;
    overflow-y: auto;
    overflow-x: hidden;
    word-wrap: break-word;
    white-space: pre-wrap;
    scrollbar-width: thin;
    scrollbar-color: #e5e7eb transparent;
    /* Reset any inherited input styles */
    box-sizing: border-box;
    width: 100%;
    display: block;
    margin: 0;
}

.chat-input-container .input-wrapper textarea#messageInput::placeholder {
    color: var(--gray-400);
    font-weight: 400;
}

/* Custom scrollbar */
.chat-input-container .input-wrapper textarea#messageInput::-webkit-scrollbar {
    width: 5px;
}

.chat-input-container .input-wrapper textarea#messageInput::-webkit-scrollbar-track {
    background: transparent;
}

.chat-input-container .input-wrapper textarea#messageInput::-webkit-scrollbar-thumb {
    background: #e5e7eb;
    border-radius: 10px;
}

.chat-input-container .input-wrapper textarea#messageInput::-webkit-scrollbar-thumb:hover {
    background: #d1d5db;
}

/* Keep emoji button aligned to bottom */
.chat-input-container .input-wrapper .emoji-picker-container {
    align-self: flex-end !important;
    padding-bottom: 0.375rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
}

/* Mobile adjustments */
@media (max-width: 640px) {
    .chat-input-container .input-wrapper textarea#messageInput {
        font-size: 0.875rem;
        max-height: 120px;
    }
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
                            <textarea 
                                id="messageInput" 
                                placeholder="Type a message here..." 
                                rows="1"
                                onkeydown="handleKeyPress(event)"
                                oninput="autoResizeTextarea(this); onMessageInput();"
                            ></textarea>
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

    <!-- New Group/Channel Modal - IMPROVED -->
<div class="modal-overlay" id="newGroupModal">
    <div class="modal" style="max-width: 720px; max-height: 90vh; border-radius: 24px; overflow: hidden;">
        <div class="modal-header" style="padding: 1.5rem 2rem; border-bottom: 2px solid var(--gray-100); background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);">
            <h3 style="display: flex; align-items: center; gap: 0.75rem; margin: 0; font-size: 1.3rem; font-weight: 700; color: var(--nu-blue);">
                <i class="fa-solid fa-users" style="color: #8b5cf6; font-size: 1.4rem;"></i>
                Create New Channel
                <span style="font-size: 0.7rem; font-weight: 500; color: var(--gray-400); background: var(--gray-100); padding: 0.125rem 0.75rem; border-radius: 20px; margin-left: 0.25rem;">Group Chat</span>
            </h3>
            <button class="btn-icon" onclick="closeNewGroupModal()" style="width: 40px; height: 40px; border-radius: 50%; background: var(--gray-100); border: none; color: var(--gray-600); cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-xmark" style="font-size: 1.2rem;"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 2rem; overflow-y: auto; background: var(--white);">
            <!-- Group Name -->
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.375rem;">
                    Channel Name <span style="color: var(--danger);">*</span>
                </label>
                <input type="text" id="groupNameInput" placeholder="Enter channel name (e.g., 'Batch 2024 Chat')" 
                    style="width: 100%; padding: 0.75rem 1rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-family: inherit; font-size: 0.95rem; transition: all var(--transition); background: var(--gray-50);">
            </div>
            
            <!-- Group Avatar -->
            <div class="form-group" style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.375rem;">
                    Channel Avatar <span style="font-weight: 400; color: var(--gray-400); font-size: 0.75rem;">(Optional)</span>
                </label>
                <div class="avatar-upload" style="display: flex; align-items: center; gap: 1rem;">
                    <div id="groupAvatarPreview" style="width: 72px; height: 72px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 700; overflow: hidden; flex-shrink: 0; box-shadow: var(--shadow-sm); border: 3px solid var(--white);">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <button class="btn-secondary" onclick="document.getElementById('groupAvatarInput').click()" 
                        style="padding: 0.625rem 1.25rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-size: 0.875rem; transition: all var(--transition); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-camera"></i> Upload Image
                    </button>
                    <span style="font-size: 0.7rem; color: var(--gray-400);">Max 2MB</span>
                    <input type="file" id="groupAvatarInput" accept="image/*" style="display: none;" onchange="handleGroupAvatarSelect(event)">
                </div>
            </div>
            
            <!-- Member Search & Selection -->
            <div class="form-group">
                <label style="display: block; font-weight: 600; font-size: 0.875rem; color: var(--gray-700); margin-bottom: 0.375rem;">
                    Add Members <span style="color: var(--danger);">*</span>
                    <span style="font-weight: 400; color: var(--gray-500); font-size: 0.75rem;">(Minimum 2 members)</span>
                </label>
                
                <!-- Search Input - FIXED with CSS override -->
                <div class="search-container" style="padding: 0; border-bottom: none; position: relative; margin-bottom: 0.75rem; width: 100%;">
                    <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1.25rem !important; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 0.9rem; z-index: 2; pointer-events: none;"></i>
                    <input type="text" id="groupMemberSearch" placeholder="Search alumni or admins by name..." 
                        oninput="searchAlumniForGroup()"
                        style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem; border: 2px solid var(--gray-200); border-radius: 50px; font-family: inherit; font-size: 0.9rem; background: var(--gray-50); transition: all 0.2s ease; outline: none; box-sizing: border-box;">
                </div>
                
                <!-- Selected Members -->
                <div id="selectedMembers" class="selected-members" 
                    style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem; min-height: 52px; border: 2px dashed var(--gray-300); border-radius: var(--radius-lg); margin-bottom: 0.75rem; background: var(--gray-50); align-items: center;">
                    <span style="color: var(--gray-400); font-size: 0.85rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>
                </div>
                
                <!-- Search Results -->
                <div id="groupMemberResults" class="search-results" style="max-height: 220px; overflow-y: auto; margin-top: 0.5rem; border: 1px solid var(--gray-200); border-radius: var(--radius-lg); padding: 0.25rem; background: var(--white);"></div>
            </div>
            
            <!-- Action Buttons -->
            <div class="form-actions" style="display: flex; gap: 0.75rem; justify-content: flex-end; margin-top: 1.75rem; padding-top: 1.25rem; border-top: 2px solid var(--gray-100);">
                <button class="btn-secondary" onclick="closeNewGroupModal()" 
                    style="padding: 0.75rem 2rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.9rem; transition: all var(--transition);">
                    Cancel
                </button>
                <button class="btn-primary" id="createGroupBtn" onclick="createGroupChat()" 
                    style="padding: 0.75rem 2rem; border: none; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--nu-blue) 0%, var(--nu-blue-light) 100%); color: var(--white); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.9rem; box-shadow: var(--shadow-blue); transition: all var(--transition); display: inline-flex; align-items: center; gap: 0.625rem;">
                    <i class="fa-solid fa-circle-plus" style="font-size: 1rem;"></i> 
                    Create Channel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Group Info Modal -->
<div class="modal-overlay" id="groupInfoModal">
    <div class="modal" style="max-width: 520px; max-height: 85vh; border-radius: 20px; overflow: hidden;">
        <div class="modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 2px solid var(--gray-100); background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);">
            <h3 style="display: flex; align-items: center; gap: 0.5rem; margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--gray-800);">
                <i class="fa-solid fa-users" style="color: #8b5cf6; font-size: 1.2rem;"></i>
                Channel Info
            </h3>
            <button class="btn-icon" onclick="closeGroupInfoModal()" style="width: 36px; height: 36px; border-radius: 50%; background: var(--gray-100); border: none; color: var(--gray-600); cursor: pointer; transition: all 0.2s;">
                <i class="fa-solid fa-xmark" style="font-size: 1.1rem;"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 1.5rem; overflow-y: auto; background: var(--white);">
            <!-- Group Info Header -->
            <div class="group-info-header" style="text-align: center; margin-bottom: 1.5rem;">
                <div class="group-info-avatar" id="groupInfoAvatar" 
                    style="width: 88px; height: 88px; border-radius: 50%; background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white; margin: 0 auto 0.75rem; display: flex; align-items: center; justify-content: center; font-size: 36px; font-weight: 700; overflow: hidden; box-shadow: 0 8px 32px rgba(139, 92, 246, 0.3); border: 3px solid var(--white);">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 id="groupInfoName" style="margin: 0 0 0.25rem 0; font-size: 1.2rem; font-weight: 700; color: var(--gray-800); letter-spacing: -0.3px;">Channel Name</h3>
                <p id="groupInfoMemberCount" style="color: var(--gray-500); font-size: 0.8rem; margin: 0; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-group" style="font-size: 0.75rem;"></i>
                    <span>0 members</span>
                </p>
                <div id="groupCreatorInfo" style="margin-top: 0.5rem; font-size: 0.75rem; color: var(--gray-400);">
                    <i class="fa-solid fa-crown" style="color: var(--nu-gold); font-size: 0.7rem;"></i>
                    Created by <span id="groupCreatorName" style="color: var(--gray-600); font-weight: 500;">Unknown</span>
                </div>
            </div>
            
            <!-- Admin Actions -->
            <div id="groupAdminActions" class="group-info-actions" style="display: none; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1.25rem; padding: 0.75rem; background: var(--gray-50); border-radius: 12px; border: 1px solid var(--gray-100);">
                <button class="btn-secondary" onclick="editGroupName()" style="flex: 1; padding: 0.5rem 0.75rem; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-size: 0.75rem; font-weight: 600; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.375rem; min-width: 80px;">
                    <i class="fa-solid fa-pen" style="font-size: 0.7rem;"></i> Edit Name
                </button>
                <button class="btn-secondary" onclick="document.getElementById('groupAvatarEditInput').click()" style="flex: 1; padding: 0.5rem 0.75rem; border: 1px solid var(--gray-200); border-radius: 8px; background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-size: 0.75rem; font-weight: 600; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.375rem; min-width: 80px;">
                    <i class="fa-solid fa-camera" style="font-size: 0.7rem;"></i> Change Avatar
                </button>
                <input type="file" id="groupAvatarEditInput" accept="image/*" style="display: none;" onchange="handleGroupAvatarEdit(event)">
            </div>
            
            <!-- Members Section -->
            <div class="group-members-section" style="margin-top: 0.25rem;">
                <div class="section-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <h4 style="margin: 0; font-size: 0.85rem; font-weight: 600; color: var(--gray-700); display: flex; align-items: center; gap: 0.5rem;">
                        <i class="fa-solid fa-user-group" style="color: #8b5cf6; font-size: 0.85rem;"></i>
                        Members
                        <span id="memberCountBadge" style="font-size: 0.65rem; background: var(--gray-200); color: var(--gray-600); padding: 0.125rem 0.5rem; border-radius: 12px; font-weight: 600; margin-left: 0.25rem;">0</span>
                    </h4>
                    <button id="addMembersBtn" class="btn-icon" onclick="openAddMembersModal()" title="Add members" style="display: none; width: 32px; height: 32px; border-radius: 50%; background: var(--nu-blue-soft); border: none; color: var(--nu-blue); cursor: pointer; transition: all 0.2s; font-size: 0.8rem;">
                        <i class="fa-solid fa-user-plus"></i>
                    </button>
                </div>
                <div id="groupMembersList" class="group-members-list" style="max-height: 280px; overflow-y: auto; padding-right: 4px;">
                    <!-- Members rendered by JavaScript -->
                </div>
            </div>
            
            <!-- Leave Button -->
            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                <button class="btn-danger" onclick="leaveGroup()" 
                    style="width: 100%; padding: 0.75rem; border: none; border-radius: 12px; background: linear-gradient(135deg, var(--danger) 0%, #dc2626 100%); color: var(--white); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.85rem; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <i class="fa-solid fa-right-from-bracket" style="font-size: 0.85rem;"></i> Leave Channel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add Members Modal -->
<div class="modal-overlay" id="addMembersModal">
    <div class="modal" style="max-width: 520px; max-height: 85vh; border-radius: 20px; overflow: hidden;">
        <div class="modal-header" style="padding: 1.25rem 1.5rem; border-bottom: 2px solid var(--gray-100); background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);">
            <h3 style="display: flex; align-items: center; gap: 0.5rem; margin: 0; font-size: 1.1rem; font-weight: 700; color: var(--gray-800);">
                <i class="fa-solid fa-user-plus" style="color: #8b5cf6; font-size: 1.2rem;"></i>
                Add Members
            </h3>
            <button class="btn-icon" onclick="closeAddMembersModal()" style="width: 36px; height: 36px; border-radius: 50%; background: var(--gray-100); border: none; color: var(--gray-600); cursor: pointer; transition: all 0.2s;">
                <i class="fa-solid fa-xmark" style="font-size: 1.1rem;"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 1.5rem; overflow-y: auto; background: var(--white);">
            <!-- Search Input -->
            <div class="search-container" style="padding: 0 0 1rem 0; border-bottom: none; position: relative;">
                <i class="fa-solid fa-magnifying-glass" style="position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--gray-400); font-size: 0.9rem; z-index: 1;"></i>
                <input type="text" id="addMemberSearch" 
                    placeholder="Search alumni or admins by name..." 
                    oninput="searchUsersForAdd()"
                    style="width: 100%; padding: 0.75rem 1rem 0.75rem 2.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-full); font-family: inherit; font-size: 0.875rem; background: var(--gray-50); transition: all var(--transition);">
            </div>
            
            <!-- Search Results -->
            <div id="addMemberResults" class="search-results" style="max-height: 300px; overflow-y: auto; margin-bottom: 1rem;">
                <div style="text-align: center; padding: 2rem 0; color: var(--gray-400);">
                    <i class="fa-solid fa-users" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                    <p style="font-size: 0.9rem;">Search for users to add to this channel</p>
                    <p style="font-size: 0.8rem; margin-top: 0.25rem;">Type at least 2 characters to start searching</p>
                </div>
            </div>
            
            <!-- Selected Members -->
            <div style="margin-bottom: 1rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                    <span style="font-size: 0.85rem; font-weight: 600; color: var(--gray-700);">
                        Selected (<span id="selectedAddCount">0</span>)
                    </span>
                    <button onclick="clearSelectedAddMembers()" 
                        style="background: none; border: none; color: var(--danger); font-size: 0.75rem; cursor: pointer; font-weight: 500; padding: 0.25rem 0.5rem; border-radius: 4px; transition: all 0.2s;">
                        Clear All
                    </button>
                </div>
                <div id="selectedAddMembers" style="display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.5rem; min-height: 44px; border: 2px dashed var(--gray-300); border-radius: var(--radius-lg); background: var(--gray-50);">
                    <span style="color: var(--gray-400); font-size: 0.8rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div style="display: flex; gap: 0.75rem; justify-content: flex-end; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                <button class="btn-secondary" onclick="closeAddMembersModal()" 
                    style="padding: 0.625rem 1.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); background: var(--white); color: var(--gray-700); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.875rem; transition: all var(--transition);">
                    Cancel
                </button>
                <button class="btn-primary" id="addMembersConfirmBtn" onclick="confirmAddMembers()" 
                    style="padding: 0.625rem 1.5rem; border: none; border-radius: var(--radius-lg); background: linear-gradient(135deg, var(--nu-blue) 0%, var(--nu-blue-light) 100%); color: var(--white); cursor: pointer; font-family: inherit; font-weight: 600; font-size: 0.875rem; box-shadow: var(--shadow-blue); transition: all var(--transition); display: inline-flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-user-plus" style="font-size: 0.9rem;"></i> 
                    Add Members
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
    const senderCache = new Map(); 
    
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
    let isLoadingMore = false;
    let hasMoreMessages = true;
    let currentOffset = 0;
    const MESSAGES_PER_PAGE = 50;
    const TYPING_TIMEOUT = 3000;
    
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
    // ✅ CORRECT: Add ALL listeners BEFORE subscribe
    // ============================================
    supabaseRealtimeChannel = supabaseClient
        .channel('admin-messages-' + adminId)
        
        // ✅ Listener 1: Incoming messages
        .on('postgres_changes', {
            event: 'INSERT',
            schema: 'public',
            table: 'messages',
            filter: `receiver_id=eq.${adminId}`,
        }, (payload) => {
            console.log('📨 New incoming message:', payload.new);
            handleIncomingMessage(payload.new);
        })
        
        // ✅ Listener 2: Outgoing messages from other sessions
        .on('postgres_changes', {
            event: 'INSERT',
            schema: 'public',
            table: 'messages',
            filter: `sender_id=eq.${adminId}`,
        }, (payload) => {
            console.log('📤 Message sent (from another session):', payload.new);
            handleOutgoingMessageFromOtherSession(payload.new);
        })
        
        // ✅ Listener 3: Attachments (moved BEFORE subscribe)
        .on('postgres_changes', {
            event: 'INSERT',
            schema: 'public',
            table: 'messages_attachments',
        }, (payload) => {
            console.log('📎 New attachment inserted (realtime):', payload.new);
            console.log('📎 Current chat:', currentChat);
            handleAttachmentInsert(payload.new);
        })
        
        // ✅ NOW subscribe after ALL listeners are added
        .subscribe((status) => {
            if (status === 'SUBSCRIBED') {
                console.log('✅ Supabase Realtime connected');
            } else if (status === 'CHANNEL_ERROR') {
                console.error('❌ Supabase Realtime error - falling back to polling');
                startPolling();
            }
        });
    
    // ============================================
    // BROADCAST: Typing indicators (separate channel)
    // ============================================
    typingChannel = supabaseClient.channel('typing-indicators', {
        config: {
            broadcast: { self: false }
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
    // REALTIME: Alumni online status changes (separate channel)
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

function initGroupRealtime() {
    console.log('🚀🚀🚀 initGroupRealtime CALLED!');
    
    if (groupRealtimeChannel) {
        console.log('⚠️ Existing group channel found, removing...');
        try {
            supabaseClient.removeChannel(groupRealtimeChannel);
        } catch (e) {}
        groupRealtimeChannel = null;
    }
    
    if (!supabaseClient) {
        console.warn('⚠️ Supabase not initialized');
        return;
    }
    
    console.log('📡 Creating GROUP realtime channel...');
    
    // ✅ Use the EXACT same approach as the test channel
    groupRealtimeChannel = supabaseClient
        .channel('group-messages-all')
        .on('postgres_changes', 
            {
                event: 'INSERT',
                schema: 'public',
                table: 'group_messages'
            },
            (payload) => {
                console.log('🔥🔥🔥 GROUP MESSAGE RECEIVED!', payload);
                const message = payload.new;
                
                if (currentChat && currentChat.type === 'group' && currentChat.id == message.group_chat_id) {
                    handleNewGroupMessage(message);
                } else {
                    // ✅ Let handleNewGroupMessage decrypt + update the list preview
                    handleNewGroupMessage(message);
                }
            }
        )
        .subscribe((status) => {
            console.log('📡 Group channel status:', status);
            if (status === 'SUBSCRIBED') {
                console.log('✅ Subscribed to ALL group messages');
            }
        });
}

function handleAttachmentInsert(attachment) {
    console.log('🔄 Processing new attachment:', attachment);
    
    // Find the message element by ID
    const messageElement = document.querySelector(`[data-msg-id="${attachment.message_id}"]`);
    if (!messageElement) {
        console.log('⏭️ Message element not found for attachment:', attachment.message_id);
        // ✅ Try to find the message in the DOM after a delay
        setTimeout(() => {
            const retryElement = document.querySelector(`[data-msg-id="${attachment.message_id}"]`);
            if (retryElement) {
                console.log('✅ Message found after delay, updating attachment');
                handleAttachmentInsert(attachment);
            }
        }, 1000);
        return;
    }
    
    // Check if this attachment is already rendered
    const existingAttach = messageElement.querySelector('.message-attachment');
    if (existingAttach && !existingAttach.classList.contains('uploading') && !existingAttach.classList.contains('loading')) {
        console.log('⏭️ Attachment already rendered for message:', attachment.message_id);
        return;
    }
    
    // Generate signed URL for the attachment
    fetch(`/admin/messages/attachments/${attachment.id}/url`)
        .then(response => {
            if (!response.ok) throw new Error('Failed to get attachment URL');
            return response.json();
        })
        .then(data => {
            if (data.success && data.url) {
                console.log('✅ Got attachment URL:', data.url);
                // ✅ Update the attachment with the URL
                updateMessageAttachment(attachment.message_id, attachment.id, data.url, attachment);
            } else {
                console.warn('⚠️ No URL returned for attachment:', attachment.id);
            }
        })
        .catch(error => {
            console.error('❌ Error fetching attachment URL:', error);
        });
}

// Fallback function to check and update attachment
function checkAndUpdateAttachment(attachment) {
    // Check if the message is now in the DOM
    const messageElement = document.querySelector(`[data-msg-id="${attachment.message_id}"]`);
    if (messageElement) {
        console.log('✅ Message found after delay, updating attachment');
        handleAttachmentInsert(attachment);
    } else {
        console.log('⏭️ Message still not found, attachment might be for a different chat');
    }
}

function updateMessageAttachment(messageId, attachmentId, url, attachmentData) {
    console.log('🔍 Looking for message element:', messageId);
    
    // Try multiple selectors
    let messageElement = document.querySelector(`[data-msg-id="${messageId}"]`);
    if (!messageElement) {
        messageElement = document.querySelector(`[data-msg-id="${messageId}"]`);
    }
    
    if (!messageElement) {
        console.log('⚠️ Message element not found for update:', messageId);
        // Try again after delay
        setTimeout(() => {
            const retryElement = document.querySelector(`[data-msg-id="${messageId}"]`);
            if (retryElement) {
                console.log('✅ Message found on retry, updating attachment');
                updateMessageAttachment(messageId, attachmentId, url, attachmentData);
            }
        }, 500);
        return;
    }
    
    // Find the message bubble
    const bubble = messageElement.querySelector('.message-bubble');
    if (!bubble) {
        console.log('⚠️ No message bubble found');
        return;
    }
    
    // Remove any existing attachment containers (especially loading ones)
    const existingAttach = bubble.querySelector('.message-attachment');
    if (existingAttach) {
        existingAttach.remove();
    }
    
    // Get filename from attachment_path or file_name
    let fileName = 'File';
    if (attachmentData.attachment_path) {
        const pathParts = attachmentData.attachment_path.split('/');
        fileName = pathParts[pathParts.length - 1] || 'File';
    }
    if (attachmentData.file_name) {
        fileName = attachmentData.file_name;
    }
    
    // ✅ Determine media type
    const mediaType = getAttachmentMediaType({
        type: attachmentData.attachment_type,
        name: fileName
    });
    
    // ✅ Build the attachment object
    const attachmentObj = {
        url: url,
        type: mediaType,
        name: fileName,
        size: attachmentData.file_size || 0,
        id: attachmentId
    };
    
    // ✅ Escape for onclick
    const escapedName = fileName.replace(/'/g, "\\'").replace(/"/g, '\\"');
    const escapedUrl = url.replace(/'/g, "\\'");
    
    // ✅ Build attachments list for navigation
    const allAttachmentsJson = JSON.stringify([{
        url: url,
        type: mediaType,
        name: fileName,
        size: attachmentData.file_size || 0,
        id: attachmentId
    }]).replace(/'/g, "\\'").replace(/"/g, '&quot;');
    
    // ✅ Create attachment container using the modal
    const attachmentContainer = document.createElement('div');
    attachmentContainer.className = 'message-attachment';
    
    if (mediaType === 'image') {
        attachmentContainer.innerHTML = `
            <img src="${url}" alt="${escapeHtml(fileName)}" 
                loading="lazy"
                onclick="openMediaPreview(
                    {url: '${escapedUrl}', type: '${mediaType}', name: '${escapedName}', size: ${attachmentData.file_size || 0}, id: '${attachmentId}'},
                    ${allAttachmentsJson},
                    0
                )"
                style="max-width: 100%; max-height: 300px; border-radius: 8px; cursor: pointer; object-fit: contain;"
                onerror="this.outerHTML = '<div class=\\'file-error\\'><i class=\\'fa-solid fa-file-image\\'></i> Failed to load image</div>'">
        `;
    } else {
        const fileIcon = getFileIconByType(mediaType, fileName);
        attachmentContainer.innerHTML = `
            <div class="file-download" 
                onclick="openMediaPreview(
                    {url: '${escapedUrl}', type: '${mediaType}', name: '${escapedName}', size: ${attachmentData.file_size || 0}, id: '${attachmentId}'},
                    ${allAttachmentsJson},
                    0
                )"
                style="cursor: pointer;">
                <i class="fa-solid ${fileIcon}"></i>
                <div class="file-info">
                    <span class="file-name">${escapeHtml(fileName)}</span>
                    <span class="file-size">${attachmentData.file_size ? formatFileSize(attachmentData.file_size) : ''}</span>
                </div>
                <i class="fa-solid fa-eye"></i>
            </div>
        `;
    }
    
    // Append to message bubble (insert before the time)
    insertAttachmentIntoBubble(bubble, attachmentContainer);    
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
    
    // For group chats, broadcast to the group
    if (currentChat.type === 'group') {
        typingChannel.send({
            type: 'broadcast',
            event: 'typing',
            payload: {
                sender_id: adminId,
                sender_type: 'admin',
                receiver_id: currentChat.id,
                receiver_type: 'group',
                timestamp: new Date().toISOString(),
            },
        });
        return;
    }
    
    // For individual chats
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
    
    // For group chats, check if the sender is not the current user and the receiver is the current group
    if (currentChat.type === 'group') {
        if (
            data.sender_id != adminId &&
            data.receiver_id == currentChat.id &&
            data.receiver_type === 'group'
        ) {
            showTypingIndicator();
        }
        return;
    }
    
    // For individual chats
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
    clearTimeout(typingTimeout);
    typingTimeout = setTimeout(() => {
        broadcastTyping();
    }, 500);
}

async function handleIncomingMessage(message) {
    if (message.receiver_id != adminId || message.receiver_type !== 'admin') return;

    const utcTimestamp = message.created_at;

    // ✅ Decrypt FIRST — used by both the contact preview and the chat panel
    const decryptedContent = await decryptContent(
        message.content,
        message.sender_type,
        message.receiver_type
    );

    // Contact list now receives plaintext, no flash
    updateContactWithNewMessage(
        message.sender_id,
        message.sender_type || 'alumni',
        decryptedContent,
        utcTimestamp,
        true
    );

    if (currentChat && message.sender_id == currentChat.id && message.sender_type === currentChat.type) {
        const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
        if (existingMsg) {
            await markMessagesAsRead(currentChat.id, currentChat.type);
            return;
        }

        // No second decrypt needed — reuse decryptedContent
        let attachments = [];
        try {
            const attachResponse = await fetch(`/admin/messages/attachments/message/${message.id}`);
            if (attachResponse.ok) {
                const attachData = await attachResponse.json();
                if (attachData.length > 0) {
                    attachments = attachData.map(att => ({
                        id: att.id,
                        type: att.attachment_type || 'document',
                        name: att.file_name || 'File',
                        size: att.file_size || 0,
                        url: att.url || null,
                        uploading: false
                    }));
                }
            }
        } catch (e) {
            console.warn('Could not fetch attachments for incoming message:', e);
        }

        const decryptedMessage = {
            id: message.id,
            content: decryptedContent,
            sender_id: message.sender_id,
            sender_type: message.sender_type,
            receiver_id: message.receiver_id,
            receiver_type: message.receiver_type,
            is_read: message.is_read,
            created_at: utcTimestamp,
            time: formatTime(new Date(utcTimestamp)),
            attachments: attachments
        };

        appendMessage(decryptedMessage);
        scrollToBottom();
        await markMessagesAsRead(currentChat.id, currentChat.type);
        lastMessageId = Math.max(lastMessageId, message.id);
    }

    refreshConversationsInBackground();
}

async function handleOutgoingMessageFromOtherSession(message) {
    if (message.sender_id != adminId || message.sender_type !== 'admin') return;
    
    // ✅ CRITICAL FIX: Skip self-sent messages that we already handled locally.
    //    Only process these if the receiver is NOT the current chat
    //    (meaning it was sent from another device/tab).
    if (currentChat && 
        message.receiver_id == currentChat.id && 
        message.receiver_type === currentChat.type) {
        // Already handled by sendTextMessage() — skip
        const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
        if (existingMsg) return;
    }

    const utcTimestamp = message.created_at;

    // ✅ Decrypt before showing anywhere
    const decryptedContent = await decryptContent(
        message.content,
        message.sender_type,
        message.receiver_type
    );

    // Fetch attachments FIRST so the sidebar preview is accurate
    let attachments = [];
    try {
        const attachResponse = await fetch(`/admin/messages/attachments/message/${message.id}`);
        if (attachResponse.ok) {
            const attachData = await attachResponse.json();
            attachments = attachData.map(att => ({
                id: att.id,
                type: att.attachment_type || 'document',
                name: att.file_name || 'File',
                size: att.file_size || 0,
                url: att.url || null,
                uploading: false
            }));
        }
    } catch (e) {
        console.warn('Could not fetch attachments for incoming message:', e);
    }

    updateContactWithNewMessage(
        message.receiver_id,               // ← Carla's ID
        message.receiver_type,             // ← 'alumni'
        decryptedContent,
        utcTimestamp,
        false,                             // ← Not unread (it's your own message)
        attachments
    );

    if (currentChat && message.receiver_id == currentChat.id && message.receiver_type === currentChat.type) {
        const existingMsg = document.querySelector(`[data-msg-id="${message.id}"]`);
        if (existingMsg) return;

        fetch(`/admin/messages/attachments/message/${message.id}`)
            .then(r => r.json())
            .then(attachData => {
                let attachments = [];
                if (attachData.length > 0) {
                    attachments = attachData.map(att => ({
                        id: att.id,
                        type: att.attachment_type || 'document',
                        name: att.file_name || 'File',
                        size: att.file_size || 0,
                        url: att.url || null,
                        uploading: false
                    }));
                }
                appendMessage({
                    id: message.id,
                    content: decryptedContent,   // ✅ reuse
                    sender_id: message.sender_id,
                    sender_type: message.sender_type,
                    receiver_id: message.receiver_id,
                    receiver_type: message.receiver_type,
                    is_read: message.is_read,
                    created_at: utcTimestamp,
                    attachments
                });
                scrollToBottom();
                lastMessageId = Math.max(lastMessageId, message.id);
            })
            .catch(() => {
                appendMessage({
                    id: message.id,
                    content: decryptedContent,
                    sender_id: message.sender_id,
                    sender_type: message.sender_type,
                    receiver_id: message.receiver_id,
                    receiver_type: message.receiver_type,
                    is_read: message.is_read,
                    created_at: utcTimestamp,
                    attachments: []
                });
                scrollToBottom();
                lastMessageId = Math.max(lastMessageId, message.id);
            });
    }
}

    function updateContactWithNewMessage(contactId, contactType, content, timestamp, isUnread = false, attachments = []) {
        let contact = allContacts.find(c => c.id == contactId && c.type === contactType);

        if (!contact) {
            fetchContactInfo(contactId, contactType, content, timestamp, isUnread, attachments);
            return;
        }

        contact.last_message = buildPreviewText(content, attachments) || null;
        contact.last_message_timestamp = timestamp;
        contact.last_message_from_me = !isUnread;

        if (isUnread && (!currentChat || currentChat.id != contactId || currentChat.type !== contactType)) {
            contact.unread_count = (contact.unread_count || 0) + 1;
        }

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
                const utcTimestamp = timestamp; // Already in PST
                
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
                const utcTimestamp = timestamp; // Already in PST
                
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
        const url = currentChat.type === 'group' 
            ? `/admin/messages/groups/${currentChat.id}/messages?limit=10&offset=0`
            : `/admin/messages/${currentChat.type}/${currentChat.id}?limit=10&offset=0`;
            
        const response = await fetch(url);
        const data = await response.json();
        const messageList = Array.isArray(data) ? data : (data.messages || []);
        
        if (messageList && messageList.length > 0) {
            let hasNewMessages = false;
            
            for (const msg of messageList) {
                if (msg.id > lastMessageId) {
                    const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
                    if (!existingMsg) {
                        let decryptedContent = msg.content;
                        if (msg.content && (msg.content.startsWith('enc:') || msg.content.startsWith('U2FsdGVkX1'))) {
                            decryptedContent = await decryptContent(msg.content, msg.sender_type, 'admin');
                        }
                        
                        // ✅ Ensure attachments have URLs
                        let attachments = msg.attachments || [];
                        if (attachments.length > 0) {
                            // Fetch fresh attachment URLs
                            for (const att of attachments) {
                                if (att.id && !att.url) {
                                    try {
                                        const urlResponse = await fetch(`/admin/messages/attachments/${att.id}/url`);
                                        const urlData = await urlResponse.json();
                                        if (urlData.success) {
                                            att.url = urlData.url;
                                        }
                                    } catch (e) {
                                        console.error('Error fetching attachment URL:', e);
                                    }
                                }
                            }
                        }
                        
                        const formattedMsg = {
                            ...msg,
                            content: decryptedContent,
                            time: msg.time || formatTime(new Date(msg.created_at)),
                            attachments: attachments
                        };
                        
                        appendMessage(formattedMsg);
                        hasNewMessages = true;
                    }
                    lastMessageId = Math.max(lastMessageId, msg.id);
                }
            }
            
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
    
// Replace the renderContacts function with this corrected version:

// ============================================
// PREVIEW TEXT FOR ATTACHMENTS
// ============================================
function describeAttachments(attachments) {
    if (!attachments || attachments.length === 0) return '';

    const first = attachments[0];
    const type = getAttachmentMediaType(first);
    const count = attachments.length;

    // Single attachment — describe the type
    if (count === 1) {
        switch (type) {
            case 'image':    return '📷 Photo';
            case 'video':    return '🎥 Video';
            case 'pdf':      return '📄 PDF';
            case 'document': {
                const ext = (first.name || '').toLowerCase().split('.').pop();
                if (ext === 'zip' || ext === 'rar') return '🗜️ Archive';
                if (ext === 'doc' || ext === 'docx') return '📝 Document';
                if (ext === 'xls' || ext === 'xlsx' || ext === 'csv') return '📊 Spreadsheet';
                if (ext === 'ppt' || ext === 'pptx') return '📽️ Presentation';
                return '📎 File';
            }
            default:         return '📎 Attachment';
        }
    }

    // Multiple attachments
    switch (type) {
        case 'image':    return `📷 ${count} photos`;
        case 'video':    return `🎥 ${count} videos`;
        default:         return `📎 ${count} files`;
    }
}

// Build a preview string for a message, preferring text if present,
// falling back to an attachment description otherwise.
function buildPreviewText(content, attachments) {
    const text = (content || '').trim();
    if (text) return text;

    const attachDesc = describeAttachments(attachments);
    if (attachDesc) return attachDesc;

    // Nothing to show
    return '';
}

function renderContacts(contacts) {
    const contactsList = document.getElementById('contactsList');

    // In renderContacts function, update the empty state for groups tab:

    if (!contacts || contacts.length === 0) {
    const query = document.getElementById('searchContacts')?.value?.toLowerCase() || '';
    const isGroupsTab = activeTab === 'groups';
    contactsList.innerHTML = `
        <div class="empty-state">
            <i class="fa-solid ${isGroupsTab ? 'fa-users' : 'fa-user-group'}"></i>
            <h3>${query ? 'No matches found' : isGroupsTab ? 'No channels yet' : 'No conversations yet'}</h3>
            <p>${query ? 'Try a different search term' : isGroupsTab ? 'Create a new channel to connect with multiple alumni' : 'Start a new message to connect with alumni'}</p>
            ${isGroupsTab ? `<button class="btn-primary" onclick="openNewGroupModal()" style="
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.625rem;
                padding: 0.75rem 2rem;
                margin-top: 0.75rem;
                background: linear-gradient(135deg, #32418C 0%, #4a59a3 100%);
                color: #FFFFFF;
                border: none;
                border-radius: 0.75rem;
                font-family: 'Poppins', sans-serif;
                font-weight: 600;
                font-size: 0.875rem;
                cursor: pointer;
                transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 0 4px 14px rgba(50, 65, 140, 0.15);
                min-height: 48px;
                min-width: 180px;
            " 
            onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 8px 30px rgba(50, 65, 140, 0.3)'; this.style.background='linear-gradient(135deg, #253069 0%, #32418C 100%)'"
            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 14px rgba(50, 65, 140, 0.15)'; this.style.background='linear-gradient(135deg, #32418C 0%, #4a59a3 100%)'"
            onmousedown="this.style.transform='scale(0.97)'"
            onmouseup="this.style.transform='translateY(-3px)'"
            >
                Create Channel
            </button>` : ''}
        </div>
    `;
    return;
}

    contactsList.innerHTML = contacts.map(contact => {
        // ✅ Determine if this is a group
        const isGroup = contact.type === 'group';
        const contactId = contact.id;
        const contactType = contact.type || 'alumni';
        const isLastMessageFromMe = contact.last_message_from_me || false;
        const isArchived = contact.is_archived || false;
        const isMuted = contact.is_muted || false;

        // ✅ For groups, use the group's name and properties
        const onClick = isGroup ? `openGroupChat(${contactId})` : `openChat(${contactId}, '${contactType}')`;

                let avatarHtml;
        if (isGroup) {
            // ✅ Group avatar - use group avatar or initials
            const groupInitials = contact.initials || 'G';
            const groupAvatar = contact.avatar || null;
            const memberCount = contact.member_count || 0;
            
            avatarHtml = `
                <div style="display: flex; align-items: center; gap: 0; position: relative;">
                    <div class="group-avatar" style="position: relative; width: 40px; height: 40px; border-radius: 50%; overflow: hidden; flex-shrink: 0; background: linear-gradient(135deg, #8b5cf6, #6d28d9); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 14px;">
                        ${groupAvatar 
                            ? `<img src="${groupAvatar}" alt="${escapeHtml(contact.name || 'Group')}" style="width: 100%; height: 100%; object-fit: cover;">`
                            : `<span>${groupInitials}</span>`
                        }
                    </div>
                    ${memberCount > 0 ? `
                        <span class="member-count-badge" style="position: absolute; bottom: -4px; right: -4px; background: #8b5cf6; color: white; border-radius: 50%; min-width: 18px; height: 18px; font-size: 9px; display: flex; align-items: center; justify-content: center; border: 2px solid white; font-weight: 700; padding: 0 4px; box-shadow: 0 2px 6px rgba(139, 92, 246, 0.4); z-index: 2;">
                            ${memberCount}
                        </span>
                    ` : ''}
                </div>
            `;
        } else {
            avatarHtml = contact.avatar
                ? `<img src="${contact.avatar}" class="contact-avatar-img" alt="${escapeHtml(contact.full_name)}">`
                : `<div class="contact-avatar">${contact.initials || '??'}</div>`;
        }

        // ✅ For groups, use contact.name instead of full_name
        const displayName = isGroup ? (contact.name || 'Unnamed Channel') : (contact.full_name || 'Unknown');
        
        // ✅ For groups, show member count in row 2
        let rowTwo;
        if (isGroup) {
            const memberCount = contact.member_count || 0;
            rowTwo = `<span class="alumni-info-text"><i class="fa-solid fa-user-group" style="font-size: 10px; margin-right: 4px;"></i> ${memberCount} members</span>`;
        } else {
            rowTwo = contact.type === 'admin'
                ? `<span class="admin-role-badge">${escapeHtml(contact.admin_role || 'Admin')}</span>`
                : `<span class="alumni-info-text">Batch ${escapeHtml(contact.batch || 'N/A')} | ${escapeHtml(contact.program || 'N/A')}</span>`;
        }

        // Determine the sender prefix for the preview
        let previewPrefix = '';

        if (contact.last_message) {
            if (isLastMessageFromMe) {
                // Sent by you
                previewPrefix = '<span class="you-prefix">You: </span>';
            } else {
                // Sent by someone else — use their name
                let senderName = '';

                if (isGroup) {
                    // Group: use the group's "last_message_sender_name" if the backend
                    // provides it, otherwise fall back to the group name (still useful).
                    senderName = contact.last_message_sender_name || contact.name || 'Someone';
                } else {
                    // Individual: the sender is always the contact themselves
                    senderName = contact.full_name || 'Someone';
                }

                // Only show the first name
                const firstName = senderName.trim().split(/\s+/)[0] || senderName;
                const safeName = truncateText(firstName, 18);
                previewPrefix = `<span class="sender-prefix">${escapeHtml(safeName)}: </span>`;
            }
        }

        const lastMessagePreview = contact.last_message
            ? `${previewPrefix}${escapeHtml(truncateText(contact.last_message, 30))}`
            : '<span class="no-message">Start a conversation</span>';

        const displayTime = contact.last_message_timestamp
            ? formatContactTime(contact.last_message_timestamp)
            : '';

        const cardClasses = [
            'contact-card',
            isGroup ? 'group-card' : '',
            currentChat?.id == contactId && currentChat?.type === contactType ? 'active' : '',
            contact.unread_count > 0 ? 'unread' : '',
            isArchived ? 'archived' : '',
            isMuted ? 'muted' : ''
        ].filter(Boolean).join(' ');

        return `
            <div class="${cardClasses}" onclick="${onClick}">
                ${avatarHtml}
                <div class="contact-details">
                    <div class="contact-row-1">
                        <span class="contact-name" title="${escapeHtml(displayName)}">
                            ${escapeHtml(displayName)}
                            ${isMuted ? '<span class="muted-indicator"><i class="fa-solid fa-bell-slash"></i></span>' : ''}
                            ${isGroup ? `<span class="group-badge" style="margin-left: 6px; font-size: 0.6rem; background: #8b5cf6; color: white; padding: 2px 8px; border-radius: 10px; font-weight: 600;"><i class="fa-solid fa-users" style="font-size: 0.5rem; margin-right: 3px;"></i>Channel</span>` : ''}
                        </span>
                        <span class="contact-time">${displayTime}</span>
                    </div>
                    <div class="contact-row-2">${rowTwo}</div>
                    <div class="contact-row-3">
                        <span class="contact-preview">${lastMessagePreview}</span>
                        ${contact.unread_count > 0 ? `<span class="unread-count">${contact.unread_count}</span>` : ''}
                    </div>
                </div>
                <div class="contact-actions">
                    <button class="btn-more"
                        data-contact-id="${contactId}"
                        data-contact-type="${contactType}"
                        onclick="event.stopPropagation(); toggleContactDropdown(${contactId}, '${contactType}', this)">
                        <i class="fa-solid fa-ellipsis-vertical"></i>
                    </button>
                </div>
            </div>
            <div class="contact-dropdown ${isGroup ? 'group-dropdown' : ''}" id="dropdown-${contactId}-${contactType}" data-contact-id="${contactId}" data-contact-type="${contactType}">
                ${isGroup ? `
                    <button type="button" class="dropdown-item" onclick="event.stopPropagation(); openGroupInfo(${contactId})">
                        <i class="fa-solid fa-info-circle"></i> Channel Info
                    </button>
                    <hr>
                ` : ''}

                <button type="button" class="dropdown-item" 
                    onclick="event.stopPropagation(); event.preventDefault(); handleDropdownAction(event, ${contactId}, '${contactType}', '${isArchived ? 'unarchive' : 'archive'}')">
                    <i class="fa-solid ${isArchived ? 'fa-box-open' : 'fa-box-archive'}"></i>
                    ${isArchived ? 'Unarchive Chat' : 'Archive Chat'}
                </button>
                <button type="button" class="dropdown-item" onclick="handleDropdownAction(event, ${contactId}, '${contactType}', '${isMuted ? 'unmute' : 'mute'}')">
                    <i class="fa-solid ${isMuted ? 'fa-bell' : 'fa-bell-slash'}"></i>
                    ${isMuted ? 'Unmute Chat' : 'Mute Chat'}
                </button>
                ${isGroup ? `
                    <hr>
                    <button type="button" class="dropdown-item leave-group" onclick="event.stopPropagation(); handleDropdownAction(event, ${contactId}, '${contactType}', 'leave')">
                        <i class="fa-solid fa-right-from-bracket"></i> Leave Channel
                    </button>
                ` : ''}
                <hr>
                <button type="button" class="dropdown-item danger" onclick="handleDropdownAction(event, ${contactId}, '${contactType}', 'delete')">
                    <i class="fa-solid fa-trash-can"></i> Delete Chat
                </button>
            </div>
        `;
    }).join('');
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
    document.body.classList.remove('is-group-chat'); 

    // ✅ FIX: Same immediate clear as group chat
    const _msgContainer = document.getElementById('chatMessages');
    if (_msgContainer) {
        _msgContainer.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading conversation...</div>';
        _msgContainer.style.display = 'block';
    }

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

    // ✅ Clear any leftover text from previous chat
    const messageInput = document.getElementById('messageInput');
    if (messageInput) {
        messageInput.value = '';
        resetTextareaHeight();
    }

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
        
        // ✅ Reset textarea
        input.value = '';
        resetTextareaHeight();
        
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
    
    currentOffset = 0;
    hasMoreMessages = true;
    
    try {
        // ✅ Use the correct URL without extra /admin
        const response = await fetch(`/admin/messages/${type}/${contactId}?limit=${MESSAGES_PER_PAGE}&offset=0`);
        if (!response.ok) throw new Error('Failed to load messages');
        
        const data = await response.json();
        const messages = data.messages || [];
        const total = data.total || 0;
        
        hasMoreMessages = messages.length < total;
        
        if (messages && messages.length > 0) {
            lastMessageId = Math.max(...messages.map(m => m.id));
        }
        
        renderMessages(messages);
        scrollToBottom();
        setupInfiniteScroll(contactId, type);
        
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

function setupInfiniteScroll(contactId, type) {
    const container = document.getElementById('chatMessages');
    
    // Remove existing listener
    container.removeEventListener('scroll', loadMoreMessages);
    
    // Add scroll listener with context
    container.addEventListener('scroll', function() {
        loadMoreMessages(contactId, type);
    });
}

async function loadMoreMessages(contactId, type) {
    if (!hasMoreMessages || isLoadingMore) return;
    
    const container = document.getElementById('chatMessages');
    const scrollTop = container.scrollTop;
    
    if (scrollTop > 200) return;
    
    isLoadingMore = true;
    const nextOffset = currentOffset + MESSAGES_PER_PAGE;
    
    try {
        let url;
        if (type === 'group') {
            url = `/admin/messages/groups/${contactId}/messages?limit=${MESSAGES_PER_PAGE}&offset=${nextOffset}`;
        } else {
            url = `/admin/messages/${type}/${contactId}?limit=${MESSAGES_PER_PAGE}&offset=${nextOffset}`;
        }
        
        const response = await fetch(url);
        if (!response.ok) throw new Error('Failed to load more messages');
        
        const data = await response.json();
        
        let messages = [];
        let total = 0;
        
        if (Array.isArray(data)) {
            messages = data;
            total = data.length;
        } else if (data.messages && Array.isArray(data.messages)) {
            messages = data.messages;
            total = data.total || messages.length;
        } else {
            messages = [];
            total = 0;
        }
        
        if (messages.length === 0) {
            hasMoreMessages = false;
            isLoadingMore = false;
            return;
        }
        
        const firstMessage = container.firstChild;
        const firstMessageOffset = firstMessage ? firstMessage.offsetTop : 0;
        
        // ✅ Decrypt content for group messages
        if (type === 'group') {
            const decryptedMessages = await Promise.all(messages.map(async (msg) => {
                let decryptedContent = msg.content;
                if (msg.content && (msg.content.startsWith('enc:') || msg.content.startsWith('U2FsdGVkX1'))) {
                    decryptedContent = await decryptContent(msg.content, msg.sender_type, 'admin');
                }
                return {
                    ...msg,
                    content: decryptedContent
                };
            }));
            renderGroupMessagesPrepend(decryptedMessages);
        } else {
            renderMessagesPrepend(messages);
        }
        
        if (firstMessage) {
            const newFirstMessage = container.firstChild;
            if (newFirstMessage) {
                const newOffset = newFirstMessage.offsetTop;
                container.scrollTop = newOffset - firstMessageOffset + 50;
            }
        }
        
        currentOffset = nextOffset;
        hasMoreMessages = messages.length + currentOffset < total;
        
    } catch (error) {
        console.error('Error loading more messages:', error);
    }
    
    isLoadingMore = false;
}

// ============================================
// SHARED BUBBLE BUILDER
// ============================================
function buildBubbleInnerHTML(msg, isSent, senderName) {
    const nameHtml = (!isSent && senderName)
        ? `<div class="sender-name">${escapeHtml(senderName)}</div>`
        : '';

    const contentHtml = msg.content && msg.content.trim() !== ''
        ? `<p>${escapeHtml(msg.content)}</p>`
        : '';

    const attachmentsHtml = msg.attachments && msg.attachments.length > 0
        ? renderAttachments(msg.attachments, isSent)
        : '';

    const timeIcon = isSent
        ? '<i class="fa-solid fa-check-double read-check"></i>'
        : '';

    return `
        ${nameHtml}
        ${contentHtml}
        ${attachmentsHtml}
        <div class="msg-time">
            ${formatCompactTime(msg.created_at ? new Date(msg.created_at) : new Date())}
            ${timeIcon}
        </div>
    `;
}

function renderGroupMessagesPrepend(messages) {
    const container = document.getElementById('chatMessages');
    if (!messages || messages.length === 0) return;

    let html = '';
    let lastDate = null;
    let lastSenderId = null;
    let lastSenderType = null;

    messages.forEach(msg => {
        const msgDate = new Date(msg.created_at);
        const localDateStr = msgDate.toISOString().split('T')[0];

        if (localDateStr !== lastDate) {
            html += `<div class="date-divider"><span>${formatDateDivider(msgDate)}</span></div>`;
            lastDate = localDateStr;
            lastSenderId = null;
            lastSenderType = null;
        }

        const isSent = (msg.sender_id == adminId && msg.sender_type === 'admin');
        const senderName = msg.sender_name || 'Unknown';
        const isSameSender =
            !isSent &&
            lastSenderId === msg.sender_id &&
            lastSenderType === (msg.sender_type || 'alumni');

        const avatarInfo = getSenderAvatarInfo(msg.sender_id, msg.sender_type);

        let avatarHtml = '';
        if (!isSent && !isSameSender) {
            avatarHtml = `
                <div class="message-avatar" style="background: ${avatarInfo.color};">
                    ${avatarInfo.photo
                        ? `<img src="${avatarInfo.photo}" alt="${escapeHtml(senderName)}">`
                        : `<span>${escapeHtml(senderName.charAt(0).toUpperCase())}</span>`
                    }
                    ${avatarInfo.is_online ? `<span class="online-indicator"></span>` : ''}
                </div>
            `;
        }

        html += `
            <div class="message-group ${isSent ? 'sent' : 'received'} ${!isSent && isSameSender ? 'same-sender' : ''}"
                 data-msg-id="${msg.id}"
                 data-sender-name="${escapeHtml(senderName)}"
                 data-sender-id="${msg.sender_id}"
                 data-sender-type="${msg.sender_type || 'alumni'}">
                <div class="message-wrapper">
                    ${!isSent ? avatarHtml : ''}
                    <div class="message-bubble">
                        ${buildBubbleInnerHTML(msg, isSent, senderName)}
                    </div>
                </div>
            </div>
        `;

        if (!isSent) {
            lastSenderId = msg.sender_id;
            lastSenderType = msg.sender_type || 'alumni';
        } else {
            lastSenderId = null;
            lastSenderType = null;
        }
    });

    container.insertAdjacentHTML('afterbegin', html);
}

function renderMessagesPrepend(messages) {
    const container = document.getElementById('chatMessages');
    if (!messages || messages.length === 0) return;

    const partnerName = (allContacts.find(
        c => c.id == currentChat?.id && c.type === currentChat?.type
    ) || {}).full_name || '';

    let html = '';
    let lastDate = null;
    let lastSenderId = null;
    let lastSenderType = null;

    messages.forEach(msg => {
        const msgDate = new Date(msg.created_at);
        const localDateStr = msgDate.toISOString().split('T')[0];

        if (localDateStr !== lastDate) {
            html += `<div class="date-divider"><span>${formatDateDivider(msgDate)}</span></div>`;
            lastDate = localDateStr;
            lastSenderId = null;
            lastSenderType = null;
        }

        const isSent = (msg.sender_id == adminId && msg.sender_type === 'admin');
        const isSameSender =
            !isSent &&
            lastSenderId === msg.sender_id &&
            lastSenderType === (msg.sender_type || 'alumni');

        const senderName = isSent ? '' : (partnerName || 'Unknown');

        html += `
            <div class="message-group ${isSent ? 'sent' : 'received'} ${!isSent && isSameSender ? 'same-sender' : ''}"
                 data-msg-id="${msg.id}">
                <div class="message-wrapper">
                    <div class="message-bubble">
                        ${buildBubbleInnerHTML(msg, isSent, senderName)}
                    </div>
                </div>
            </div>
        `;

        if (!isSent) {
            lastSenderId = msg.sender_id;
            lastSenderType = msg.sender_type || 'alumni';
        } else {
            lastSenderId = null;
            lastSenderType = null;
        }
    });

    container.insertAdjacentHTML('afterbegin', html);
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

    // Identify the individual-chat partner (for optional name display)
    const partnerName = (allContacts.find(
        c => c.id == currentChat?.id && c.type === currentChat?.type
    ) || {}).full_name || '';

    let html = '';
    let lastDate = null;
    let lastSenderId = null;
    let lastSenderType = null;

    messages.forEach((msg) => {
        const msgDate = msg.created_at ? new Date(msg.created_at) : new Date();
        const dateStr = formatDateDivider(msgDate);
        const localDateStr = msgDate.toISOString().split('T')[0];

        if (localDateStr !== lastDate) {
            html += `<div class="date-divider"><span>${dateStr}</span></div>`;
            lastDate = localDateStr;
            lastSenderId = null;
            lastSenderType = null;
        }

        const isSent = (msg.sender_id == adminId && msg.sender_type === 'admin');
        const isSameSender =
            !isSent &&
            lastSenderId === msg.sender_id &&
            lastSenderType === (msg.sender_type || 'alumni');

        // For individual chats, the sender is always the partner.
        // We still show their name inside the bubble — same design as group chats.
        const senderName = isSent ? '' : (partnerName || 'Unknown');

        html += `
            <div class="message-group ${isSent ? 'sent' : 'received'} ${!isSent && isSameSender ? 'same-sender' : ''}"
                 data-msg-id="${msg.id}"
                 data-sender-id="${msg.sender_id}"
                 data-sender-type="${msg.sender_type || 'alumni'}">
                <div class="message-wrapper">
                    <div class="message-bubble">
                        ${buildBubbleInnerHTML(msg, isSent, senderName)}
                    </div>
                </div>
            </div>
        `;

        if (!isSent) {
            lastSenderId = msg.sender_id;
            lastSenderType = msg.sender_type || 'alumni';
        } else {
            lastSenderId = null;
            lastSenderType = null;
        }
    });

    container.innerHTML = html;
}
    
function appendMessage(msg) {
    const container = document.getElementById('chatMessages');

    if (msg.id && !msg.id.toString().startsWith('temp-')) {
        const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
        if (existingMsg) {
            if (msg.attachments && msg.attachments.length > 0) {
                const existingAttach = existingMsg.querySelector('.message-attachment');
                if (!existingAttach || existingAttach.classList.contains('uploading')) {
                    updateExistingMessageWithAttachments(existingMsg, msg);
                }
            }
            return;
        }

        // Remove temp message with same content
        const tempMessages = container.querySelectorAll('[data-msg-id^="temp-"]');
        tempMessages.forEach(tempEl => {
            const tempBubble = tempEl.querySelector('.message-bubble p');
            const tempContent = tempBubble ? tempBubble.textContent : '';
            const realContent = msg.content || '';
            if (tempContent.includes('📎') || tempContent === realContent) {
                tempEl.remove();
            }
        });
    }

    const isSent = (msg.sender_id == adminId && msg.sender_type === 'admin');
    const partnerName = (allContacts.find(
        c => c.id == currentChat?.id && c.type === currentChat?.type
    ) || {}).full_name || '';
    const senderName = isSent ? '' : (partnerName || 'Unknown');

    const messageHtml = `
        <div class="message-group ${isSent ? 'sent' : 'received'}"
             ${msg.id ? `data-msg-id="${msg.id}"` : ''}
             data-sender-id="${msg.sender_id}"
             data-sender-type="${msg.sender_type || 'alumni'}">
            <div class="message-wrapper">
                <div class="message-bubble">
                    ${buildBubbleInnerHTML(msg, isSent, senderName)}
                </div>
            </div>
        </div>
    `;

    const emptyState = container.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    container.insertAdjacentHTML('beforeend', messageHtml);
}

// ✅ Enhanced: Update existing message with attachments
function updateExistingMessageWithAttachments(messageElement, msg) {
    const bubble = messageElement.querySelector('.message-bubble');
    if (!bubble) return;
    
    // Remove any existing attachment containers (especially uploading ones)
    const existingAttach = bubble.querySelector('.message-attachment');
    if (existingAttach) existingAttach.remove();
    
    // Add the new attachments
    if (msg.attachments && msg.attachments.length > 0) {
        const attachmentsHtml = renderAttachments(msg.attachments, true);
        if (attachmentsHtml) {
            // Insert before the time
            const timeElement = bubble.querySelector('.msg-time');
            if (timeElement) {
                // Insert the attachments HTML before the time element
                timeElement.insertAdjacentHTML('beforebegin', attachmentsHtml);
            } else {
                bubble.insertAdjacentHTML('beforeend', attachmentsHtml);
            }
            
            // Ensure the message content is visible
            const contentP = bubble.querySelector('p');
            if (!contentP && msg.content) {
                bubble.insertAdjacentHTML('afterbegin', `<p>${escapeHtml(msg.content)}</p>`);
            }
        }
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

    // ============================================
    // AUTO-RESIZE TEXTAREA
    // ============================================
    function autoResizeTextarea(textarea) {
        if (!textarea) return;
        
        // Reset height to auto to get the correct scrollHeight
        textarea.style.height = 'auto';
        
        // Set the height based on content (max 150px via CSS)
        const newHeight = Math.min(textarea.scrollHeight, 150);
        textarea.style.height = newHeight + 'px';
    }

    // Reset textarea height when clearing
    function resetTextareaHeight() {
        const textarea = document.getElementById('messageInput');
        if (textarea) {
            textarea.style.height = 'auto';
            textarea.style.height = '24px';
        }
    }
    
    function handleKeyPress(event) {
        // Enter without Shift = send message
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
            
            // Reset textarea height after sending
            setTimeout(() => {
                resetTextareaHeight();
            }, 50);
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
    
// ============================================
// TIME FORMATTING - FIXED
// ============================================

function formatCompactTime(date) {
    if (!date) return '';
    const d = (date instanceof Date) ? date : new Date(date);
    if (isNaN(d.getTime())) return '';

    const now = new Date();
    const diffMs = now - d;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);

    if (diffMins < 1 && diffMs >= 0) return 'just now';
    if (diffMins < 60) return `${diffMins}m`;
    if (diffHours < 24) return `${diffHours}h`;

    // Same calendar day check (yesterday, today)
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today); yesterday.setDate(today.getDate() - 1);
    const msgDay = new Date(d.getFullYear(), d.getMonth(), d.getDate());

    if (msgDay.getTime() === yesterday.getTime()) {
        return 'yesterday ' + formatClock(d);
    }
    if (diffDays < 7) {
        const days = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
        return days[d.getDay()] + ' ' + formatClock(d);
    }

    const months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    const sameYear = d.getFullYear() === now.getFullYear();
    return sameYear
        ? `${months[d.getMonth()]} ${d.getDate()}`
        : `${months[d.getMonth()]} ${d.getDate()}, ${d.getFullYear()}`;
}

function formatClock(d) {
    let h = d.getHours();
    const m = String(d.getMinutes()).padStart(2, '0');
    const ampm = h >= 12 ? 'pm' : 'am';
    h = h % 12 || 12;
    return `${h}:${m}${ampm}`;
}

/**
 * Format time for display with proper timezone handling
 */
function formatTime(date) {
    if (!date) return 'Just now';
    
    // Ensure we have a Date object
    let dateObj;
    if (typeof date === 'string') {
        // If it's an ISO string, parse it directly
        dateObj = new Date(date);
    } else if (date instanceof Date) {
        dateObj = date;
    } else {
        dateObj = new Date(date);
    }
    
    // If date is invalid, return 'Just now'
    if (isNaN(dateObj.getTime())) {
        return 'Just now';
    }
    
    // Get current time
    const now = new Date();
    const msgTime = dateObj.getTime();
    const diffMs = now.getTime() - msgTime;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    // Show "Just now" for messages less than 1 minute old
    if (diffMins < 1 && diffMs >= 0) {
        return 'Just now';
    }
    
    // Show minutes for messages less than 1 hour old
    if (diffMins < 60 && diffMins > 0) {
        return `${diffMins}m ago`;
    }
    
    // Show hours for messages less than 24 hours old
    if (diffHours < 24 && diffHours > 0) {
        return `${diffHours}h ago`;
    }
    
    // Show days for messages less than 7 days old
    if (diffDays < 7 && diffDays > 0) {
        return `${diffDays}d ago`;
    }
    
    // For older messages, show the actual date and time in local timezone
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    const month = months[dateObj.getMonth()];
    const day = dateObj.getDate();
    const year = dateObj.getFullYear();
    
    let hours = dateObj.getHours();
    const ampm = hours >= 12 ? 'PM' : 'AM';
    hours = hours % 12 || 12;
    const minutes = dateObj.getMinutes().toString().padStart(2, '0');
    
    return `${month} ${day}, ${year} at ${hours}:${minutes} ${ampm}`;
}

/**
 * Format date divider with proper timezone handling
 */
function formatDateDivider(date) {
    if (!date) return 'Unknown';
    
    let dateObj;
    if (typeof date === 'string') {
        dateObj = new Date(date);
    } else if (date instanceof Date) {
        dateObj = date;
    } else {
        dateObj = new Date(date);
    }
    
    if (isNaN(dateObj.getTime())) return 'Unknown';
    
    const now = new Date();
    const today = new Date(now.getFullYear(), now.getMonth(), now.getDate());
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);
    
    const msgDate = new Date(dateObj.getFullYear(), dateObj.getMonth(), dateObj.getDate());
    
    if (msgDate.getTime() === today.getTime()) {
        return 'Today';
    } else if (msgDate.getTime() === yesterday.getTime()) {
        return 'Yesterday';
    } else {
        const months = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        return `${months[dateObj.getMonth()]} ${dateObj.getDate()}, ${dateObj.getFullYear()}`;
    }
}

/**
 * Format time for display in the contact list
 */
function formatContactTime(timestamp) {
    if (!timestamp) return '';
    
    let dateObj;
    if (typeof timestamp === 'string') {
        dateObj = new Date(timestamp);
    } else {
        dateObj = new Date(timestamp);
    }
    
    if (isNaN(dateObj.getTime())) return '';
    
    const now = new Date();
    const diffMs = now.getTime() - dateObj.getTime();
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMins / 60);
    const diffDays = Math.floor(diffHours / 24);
    
    if (diffMins < 1 && diffMs >= 0) return 'Just now';
    if (diffMins < 60 && diffMins > 0) return `${diffMins}m ago`;
    if (diffHours < 24 && diffHours > 0) return `${diffHours}h ago`;
    if (diffDays < 7 && diffDays > 0) return `${diffDays}d ago`;
    
    const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    return `${months[dateObj.getMonth()]} ${dateObj.getDate()}`;
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
    
    // Load all conversations
    await loadConversations();
    
    // Initialize Supabase
    initSupabase();
    
    // ✅ Initialize GROUP realtime
    initGroupRealtime();
    
    // ✅ Check for URL parameter to auto-open chat
    await handleUrlChatRedirect();  // Make sure this is awaited
    
    // ✅ AFTER the redirect, check if we're in a group chat
    if (currentChat && currentChat.type === 'group') {
        console.log('✅ Group chat is already open, currentChat set to:', currentChat);
    }
    
    // Check for URL parameter (fallback)
    handleUrlChatRedirect();
});

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
        if (allContacts.length > 0 || allGroups.length > 0 || attempts > 10) {
            console.log('✅ Checking for contact/group:', chatId);
            
            // ✅ Check if it's a GROUP first
            const existingGroup = allGroups.find(g => g.id == chatId);
            if (existingGroup) {
                console.log('✅ Found existing group, opening');
                await openGroupChat(chatId);
                return;
            }
            
            // Then check if it's an individual contact
            const existingContact = allContacts.find(c => c.id == chatId && c.type === 'alumni');
            if (existingContact) {
                console.log('✅ Found existing contact, opening chat');
                await openChat(chatId, 'alumni');
                return;
            }
            
            // If not found, try fetching from server
            console.log('⚠️ Contact not in conversations, fetching info...');
            try {
                // Try group first
                const groupResponse = await fetch(`/admin/messages/groups/${chatId}/info`);
                if (groupResponse.ok) {
                    const group = await groupResponse.json();
                    allGroups.unshift({
                        ...group,
                        type: 'group',
                        id: parseInt(group.id),
                        is_archived: group.is_archived || false,
                        is_muted: group.is_muted || false,
                        unread_count: 0,
                        last_message: null,
                        last_message_timestamp: null,
                        last_message_from_me: false,
                        member_count: group.member_count || 0,
                        name: group.name || 'Unnamed Channel',
                        initials: group.initials || 'GC',
                        avatar: group.avatar || null,
                        created_by: group.created_by || null,
                        created_by_name: group.created_by_name || 'Unknown',
                    });
                    renderContacts(allContacts);
                    await openGroupChat(chatId);
                    return;
                }
            } catch (e) {}
            
            // Try alumni
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
                        avatar: alumni.avatar || null,
                        is_archived: false,
                        is_muted: false
                    });
                    
                    renderContacts(allContacts);
                    await openChat(alumni.id, 'alumni');
                    return;
                }
            } catch (e) {}
            
            // Create placeholder
            createPlaceholder(chatId);
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
            avatar: null,
            is_archived: false,
            is_muted: false
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
    
    // Only process typing events for the current chat
    if (!currentChat) return;
    
    // For group chats
    if (currentChat.type === 'group') {
        if (
            data.sender_id != adminId &&
            data.receiver_id == currentChat.id &&
            data.receiver_type === 'group'
        ) {
            showTypingIndicator();
        }
        return;
    }
    
    // For individual chats
    if (
        data.sender_id == currentChat.id && 
        data.sender_type === currentChat.type &&
        data.receiver_id == adminId &&
        data.receiver_type === 'admin'
    ) {
        showTypingIndicator();
    }
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

        autoResizeTextarea(input);
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
    console.log('🎨 renderAttachments called with:', attachments);
    
    if (!attachments || attachments.length === 0) {
        console.log('⚠️ No attachments to render');
        return '';
    }
    
    const result = attachments.map((att, index) => {
        console.log('🔍 Processing attachment:', att);
        
        // If still uploading, show a spinner
        if (att.uploading) {
            console.log('⏳ Attachment is still uploading');
            return `
                <div class="message-attachment uploading">
                    <div class="uploading-placeholder">
                        <i class="fa-solid fa-spinner fa-spin"></i>
                        <span>Uploading ${escapeHtml(att.name || 'file')}...</span>
                    </div>
                </div>
            `;
        }
        
        // ✅ CRITICAL: If no URL, show a placeholder
        if (!att.url) {
            console.log('⚠️ No URL for attachment, showing placeholder');
            return `
                <div class="message-attachment loading">
                    <div class="uploading-placeholder">
                        <i class="fa-solid ${getFileIcon(att.type || 'document')}"></i>
                        <span>${escapeHtml(att.name || 'File')}</span>
                        <span style="font-size: 0.7rem; color: #999;">Loading...</span>
                    </div>
                </div>
            `;
        }
        
        // ✅ Now we have a URL, render the actual attachment
        console.log('✅ Rendering attachment with URL:', att.url);
        
        // ✅ Determine media type
        const mediaType = getAttachmentMediaType(att);
        const isImage = mediaType === 'image';
        
        // ✅ Prepare data for the modal (escape for onclick)
        const escapedName = (att.name || 'File').replace(/'/g, "\\'").replace(/"/g, '\\"');
        const escapedUrl = att.url.replace(/'/g, "\\'");
        const fileSize = att.size || 0;
        const attachmentId = att.id || '';
        
        // ✅ Build the attachments array for navigation (if multiple)
        // We'll pass the full list so users can navigate between them
        const allAttachmentsJson = JSON.stringify(
            attachments
                .filter(a => a.url && !a.uploading) // Only include loaded attachments
                .map(a => ({
                    url: a.url,
                    type: getAttachmentMediaType(a),
                    name: a.name || 'File',
                    size: a.size || 0,
                    id: a.id || ''
                }))
        ).replace(/'/g, "\\'").replace(/"/g, '&quot;');
        
        if (isImage) {
            return `
                <div class="message-attachment">
                    <img src="${att.url}" 
                         alt="${escapeHtml(att.name || 'Image')}" 
                         loading="lazy"
                         onclick="openMediaPreview(
                             {url: '${escapedUrl}', type: '${mediaType}', name: '${escapedName}', size: ${fileSize}, id: '${attachmentId}'},
                             ${allAttachmentsJson},
                             ${index}
                         )"
                         onerror="this.outerHTML = '<div class=\\'file-error\\'><i class=\\'fa-solid fa-file-image\\'></i> Failed to load image</div>'"
                         style="max-width: 100%; max-height: 300px; border-radius: 8px; cursor: pointer; object-fit: contain;">
                </div>
            `;
        } else {
            // ✅ For documents/videos/PDFs - show a clickable file card
            const fileIcon = getFileIconByType(mediaType, att.name);
            
            return `
                <div class="message-attachment">
                    <div class="file-download" 
                         onclick="openMediaPreview(
                             {url: '${escapedUrl}', type: '${mediaType}', name: '${escapedName}', size: ${fileSize}, id: '${attachmentId}'},
                             ${allAttachmentsJson},
                             ${index}
                         )"
                         style="cursor: pointer;">
                        <i class="fa-solid ${fileIcon}"></i>
                        <div class="file-info">
                            <span class="file-name">${escapeHtml(att.name || 'File')}</span>
                            <span class="file-size">${att.size ? formatFileSize(att.size) : ''}</span>
                        </div>
                        <i class="fa-solid fa-eye"></i>
                    </div>
                </div>
            `;
        }
    });
    
    console.log('🎨 renderAttachments result length:', result.length);
    return result.join('');
}

// ============================================
// HELPER: Determine attachment media type
// ============================================
function getAttachmentMediaType(att) {
    if (!att) return 'document';
    
    // Check explicit type first
    if (att.type) {
        const type = att.type.toLowerCase();
        if (type === 'image' || type === 'video' || type === 'pdf' || type === 'document') {
            return type;
        }
    }
    
    // Check by file extension
    const name = (att.name || '').toLowerCase();
    const extension = name.split('.').pop();
    
    if (['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp', 'svg', 'ico'].includes(extension)) {
        return 'image';
    }
    if (['mp4', 'webm', 'ogg', 'mov', 'avi', 'mkv'].includes(extension)) {
        return 'video';
    }
    if (extension === 'pdf') {
        return 'pdf';
    }
    
    return 'document';
}

// ============================================
// HELPER: Get file icon by type (extended)
// ============================================
function getFileIconByType(mediaType, fileName) {
    if (mediaType === 'video') return 'fa-video';
    if (mediaType === 'pdf') return 'fa-file-pdf';
    
    // Check extension for more specific icons
    const ext = (fileName || '').toLowerCase().split('.').pop();
    
    const iconMap = {
        doc: 'fa-file-word',
        docx: 'fa-file-word',
        xls: 'fa-file-excel',
        xlsx: 'fa-file-excel',
        ppt: 'fa-file-powerpoint',
        pptx: 'fa-file-powerpoint',
        txt: 'fa-file-lines',
        csv: 'fa-file-csv',
        zip: 'fa-file-zipper',
        rar: 'fa-file-zipper'
    };
    
    return iconMap[ext] || 'fa-file';
}

// ============================================
// DROPDOWN FUNCTIONS - COMPLETE FIX
// ============================================

// Height cache
const dropdownHeightCache = {};

function toggleContactDropdown(contactId, contactType, button) {
    // Prevent event from bubbling to parent
    if (event) {
        event.stopPropagation();
        event.preventDefault();
    }
    
    const dropdownId = `dropdown-${contactId}-${contactType}`;
    const dropdown = document.getElementById(dropdownId);
    const backdrop = document.getElementById('dropdownBackdrop');
    
    // If this dropdown is already active, close it
    if (dropdown && dropdown.classList.contains('active')) {
        closeAllDropdowns();
        return;
    }
    
    // Close all other dropdowns first
    closeAllDropdowns();
    
    if (!dropdown) {
        console.error('Dropdown not found:', dropdownId);
        return;
    }
    
    // Position the dropdown
    const buttonRect = button.getBoundingClientRect();
    
    // Get dropdown height
    let dropdownHeight = dropdownHeightCache[contactType] || 150;
    
    let top = buttonRect.bottom + 4;
    let left = buttonRect.right - 200;
    
    // Ensure dropdown stays in viewport
    if (top + dropdownHeight > window.innerHeight - 10) {
        top = buttonRect.top - dropdownHeight - 4;
    }
    if (top < 10) top = 10;
    if (left < 10) left = 10;
    if (left + 200 > window.innerWidth) {
        left = window.innerWidth - 210;
    }
    
    // Apply styles - use direct style assignment, not cssText
    dropdown.style.position = 'fixed';
    dropdown.style.top = top + 'px';
    dropdown.style.left = left + 'px';
    dropdown.style.right = 'auto';
    dropdown.style.bottom = 'auto';
    dropdown.style.display = 'block';
    dropdown.style.visibility = 'visible';
    dropdown.style.zIndex = '999999';
    dropdown.style.pointerEvents = 'auto';
    dropdown.style.opacity = '1';
    dropdown.style.transform = 'none';
    
    // Add active class
    dropdown.classList.add('active');
    
    // Show backdrop with pointer-events: none
    if (backdrop) {
        backdrop.style.display = 'block';
        backdrop.style.pointerEvents = 'none';
        backdrop.style.background = 'transparent';
        backdrop.classList.add('active');
    }
    
    // Log for debugging
    console.log('✅ Dropdown opened:', dropdownId, 'at position:', top, left);
}

function handleDropdownAction(event, contactId, contactType, action) {
    event.stopPropagation();
    event.preventDefault();
    
    // Close dropdown
    closeAllDropdowns();
    
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

function closeAllDropdowns() {
    document.querySelectorAll('.contact-dropdown.active').forEach(d => {
        d.classList.remove('active');
        d.style.display = 'none';
        d.style.pointerEvents = 'none';
    });
    
    const backdrop = document.getElementById('dropdownBackdrop');
    if (backdrop) {
        backdrop.classList.remove('active');
        backdrop.style.display = 'none';
        backdrop.style.pointerEvents = 'none';
    }
}

// ============================================
// DROPDOWN ITEM CLICK HANDLER - FIX
// ============================================

// Direct click handler for dropdown items using event delegation
document.addEventListener('click', function(event) {
    // Check if click is on a dropdown item
    const item = event.target.closest('.dropdown-item');
    if (item) {
        // Prevent the dropdown from closing
        event.stopPropagation();
        
        // Find the onclick attribute and execute it
        const onclickAttr = item.getAttribute('onclick');
        if (onclickAttr) {
            console.log('🔘 Dropdown item clicked:', onclickAttr);
            // Execute the onclick
            try {
                // Use Function constructor to execute with proper context
                const fn = new Function('event', onclickAttr);
                fn(event);
            } catch (e) {
                console.error('Error executing dropdown onclick:', e);
            }
        }
    }
}, true); // Use capture phase to ensure we catch it

// Also handle touch events
document.addEventListener('touchstart', function(event) {
    const item = event.target.closest('.dropdown-item');
    if (item) {
        event.preventDefault();
        event.stopPropagation();
        
        const onclickAttr = item.getAttribute('onclick');
        if (onclickAttr) {
            console.log('👆 Dropdown item touched:', onclickAttr);
            try {
                const fn = new Function('event', onclickAttr);
                fn(event);
            } catch (e) {
                console.error('Error executing dropdown touch:', e);
            }
        }
    }
}, { passive: false, capture: true });

// ✅ Close dropdown when clicking outside (using event delegation)
document.addEventListener('click', function(event) {
    const activeDropdowns = document.querySelectorAll('.contact-dropdown.active');
    if (activeDropdowns.length === 0) return;
    
    // Check if click is on a dropdown or the three-dots button
    const isDropdown = event.target.closest('.contact-dropdown');
    const isMoreButton = event.target.closest('.btn-more');
    
    if (!isDropdown && !isMoreButton) {
        closeAllDropdowns();
    }
});

// Close dropdown on Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeAllDropdowns();
    }
});

// Also handle backdrop click directly (the HTML has onclick="closeAllDropdowns()")
// But also add a backup click listener
document.addEventListener('DOMContentLoaded', function() {
    const backdrop = document.getElementById('dropdownBackdrop');
    if (backdrop) {
        backdrop.addEventListener('click', function(e) {
            closeAllDropdowns();
        });
    }
});

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

async function sendMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    // If it's a group chat, use group send
    if (currentChat && currentChat.type === 'group') {
        return sendGroupMessage();
    }
    
    // ✅ FIX: Allow sending files even without text content
    if (!currentChat) {
        return;
    }
    
    // ✅ If there's no content AND no files, do nothing
    if (!content && selectedFiles.length === 0) {
        return;
    }

    // Check if chat is archived
    const contact = allContacts.find(c => c.id == currentChat.id && c.type === currentChat.type);
    if (contact && contact.is_archived) {
        showToast('Cannot send messages to archived chat. Unarchive it first.', 'error');
        return;
    }
    
    // If there are files, send with attachments
    if (selectedFiles.length > 0) {
        await sendMessageWithAttachments(content);
        return;
    }
    
    // No files, send text message
    await sendTextMessage(content);
}

// ============================================
// SEND MESSAGE WITH ATTACHMENTS - FIXED FOR SENDER
// ============================================
async function sendMessageWithAttachments(content) {
    const input = document.getElementById('messageInput');
    
    input.value = '';
    resetTextareaHeight();  // ✅ ADD THIS
    input.focus();
    
    const tempId = 'temp-' + Date.now();
    const fileNames = selectedFiles.map(f => f.name).join(', ');
    const tempContent = content || `📎 ${fileNames}`;
    
    // ✅ Create attachments array for temp message with proper structure
    const tempAttachments = selectedFiles.map(f => ({
        id: 'temp-' + Date.now() + '-' + Math.random(),
        type: f.type.startsWith('image/') ? 'image' : 'document',
        name: f.name,
        size: f.size,
        uploading: true,
        url: null
    }));
    
    const tempMessage = {
        id: tempId,
        content: tempContent,
        sender_id: adminId,
        sender_type: 'admin',
        is_read: false,
        created_at: new Date().toISOString(), // Keep this - it creates the date in local time
        
        attachments: tempAttachments
    };
    
    // ✅ Show temp message with upload indicator
    appendMessage(tempMessage);
    scrollToBottom();
    
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
        
        const responseText = await response.text();
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('❌ Failed to parse response:', responseText);
            throw new Error('Server returned invalid response');
        }
        
        if (!response.ok) {
            throw new Error(data.error || `Server error: ${response.status}`);
        }
        
        if (data.success && data.message) {
            // ✅ Remove temp message
            const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
            if (tempElement) tempElement.remove();
            
            // ✅ Ensure created_at is set properly
            let serverCreatedAt = data.message.created_at;
            if (!serverCreatedAt) {
                // If the server didn't return created_at, use the current time in UTC
                serverCreatedAt = new Date().toISOString();
            } else {
                // Ensure it's a proper UTC timestamp
                // serverCreatedAt is already in PST
            }
                    
            // ✅ IMPORTANT: Get attachments from the server response
            let finalAttachments = [];
            if (data.message.attachments && data.message.attachments.length > 0) {
                // Server should return attachments with signed URLs
                finalAttachments = data.message.attachments.map(att => ({
                    id: att.id,
                    type: att.type || 'document',
                    name: att.name || 'File',
                    size: att.size || 0,
                    url: att.url || null,
                    uploading: false
                }));
                console.log('✅ Final attachments from server:', finalAttachments);
            } else {
                // ✅ FALLBACK: If server didn't return attachments, fetch them
                console.warn('⚠️ No attachments in server response, fetching them...');
                try {
                    const attachResponse = await fetch(`/admin/messages/attachments/message/${data.message.id}`);
                    if (attachResponse.ok) {
                        const attachData = await attachResponse.json();
                        if (attachData.length > 0) {
                            finalAttachments = attachData.map(att => ({
                                id: att.id,
                                type: att.attachment_type || 'document',
                                name: att.file_name || 'File',
                                size: att.file_size || 0,
                                url: att.url || null,
                                uploading: false
                            }));
                            console.log('✅ Attachments fetched via fallback:', finalAttachments);
                        }
                    }
                } catch (e) {
                    console.warn('Could not fetch attachments directly:', e);
                }
            }
            
            const finalMessage = {
                id: data.message.id,
                content: data.message.content || content || '📎 Attachment',
                sender_id: data.message.sender_id || adminId,
                sender_type: data.message.sender_type || 'admin',
                receiver_id: data.message.receiver_id || currentChat.id,
                receiver_type: data.message.receiver_type || currentChat.type,
                is_read: data.message.is_read || false,
                created_at: serverCreatedAt, // Use the ensured timestamp
                attachments: finalAttachments
            };
            
            console.log('📤 Final message with attachments:', finalMessage);
            
            // ✅ Append the final message - this will render the attachment
            appendMessage(finalMessage);
            scrollToBottom();
            
            lastMessageId = Math.max(lastMessageId, data.message.id || 0);
            
            updateContactWithNewMessage(
                currentChat.id,
                currentChat.type,
                finalMessage.content,
                finalMessage.created_at,
                false,
                finalMessage.attachments || []
            );
                        
            showToast('Message sent successfully');
        }
    } catch (error) {
        console.error('❌ Error sending files:', error);
        const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
        if (tempElement) tempElement.remove();
        showToast('Failed to send files. Please try again.', 'error');
    }
}

// ============================================
// SEND TEXT MESSAGE ONLY
// ============================================
async function sendTextMessage(content) {
    const input = document.getElementById('messageInput');
    
    clearTimeout(typingTimeout);
    input.value = '';
    resetTextareaHeight();  // ✅ ADD THIS
    input.focus();

    const now = new Date();
    const tempId = 'temp-' + Date.now();
    const tempMessage = {
        id: tempId,
        content: content,
        sender_id: adminId,
        sender_type: 'admin',
        is_read: false,
        created_at: now.toISOString(), // ✅ Use ISO string with timezone
        attachments: []
    };
        
    appendMessage(tempMessage);
    scrollToBottom();
    
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
            
            // ✅ Ensure created_at is set properly
            let serverCreatedAt = data.message.created_at;
            if (!serverCreatedAt) {
                serverCreatedAt = new Date().toISOString();
            } else {
                // serverCreatedAt is already in PST
            }

            // ✅ Use the ensured timestamp
            const finalMessage = {
                id: data.message.id,
                content: data.message.content,
                sender_id: data.message.sender_id,
                sender_type: data.message.sender_type,
                receiver_id: data.message.receiver_id,
                receiver_type: data.message.receiver_type,
                is_read: data.message.is_read,
                created_at: serverCreatedAt,
                attachments: data.message.attachments || []
            };
                    
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
        showToast('Failed to send message. Please try again.', 'error');
    }
}


    // ============================================
    // GROUP CHAT STATE
    // ============================================
    let selectedGroupMembers = [];
    let selectedAddMembers = [];
    let addMemberSearchTimeout = null;
    let addMemberResultsCache = [];
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

async function openGroupChat(groupId) {
    lastMessageId = 0;
    currentChat = { id: groupId, type: 'group' };
    isGroupChat = true;
    document.body.classList.add('is-group-chat'); 

    // ✅ FIX: Clear the previous chat's messages immediately so the user
    //         doesn't stare at the wrong conversation while we load the new one.
    const _msgContainer = document.getElementById('chatMessages');
    if (_msgContainer) {
        _msgContainer.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading channel...</div>';
        _msgContainer.style.display = 'block';
    }

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

    // ✅ Initialize the global group channel if it doesn't exist
    if (!groupRealtimeChannel) {
        initGroupRealtime();
    }
    
    // ✅ FIX: Fire prefetch in the background, don't await it.
    //    Messages render immediately; avatars upgrade as soon as
    //    the sender data arrives.
    prefetchGroupSenders(groupId).then(() => {
        // Once prefetch completes, re-render just the avatars
        // (lightweight — doesn't reload messages)
        refreshGroupMessageAvatars();
    });

    // Load messages immediately — don't wait for prefetch
    await loadGroupMessages(groupId);
    
    // Mark as read
    await markGroupMessagesAsRead(groupId);
    
    // Check archive status and update input
    checkAndDisableGroupChat(groupId);
    
    // Show on mobile
    showChatOnMobile();
}

function refreshGroupMessageAvatars() {
    document.querySelectorAll('.message-group[data-sender-name]').forEach(el => {
        const senderId = el.dataset.senderId;
        const senderType = el.dataset.senderType || 'alumni';
        if (!senderId) return;

        const info = getSenderAvatarInfo(senderId, senderType);

        // 1. Update the avatar image (if there is one)
        const avatarEl = el.querySelector('.message-avatar');
        if (avatarEl && info.full_name !== 'Unknown') {
            avatarEl.innerHTML = `
                ${info.photo
                    ? `<img src="${info.photo}" alt="${escapeHtml(info.full_name)}">`
                    : `<span>${escapeHtml(info.initials || '?')}</span>`
                }
                ${info.is_online ? `<span class="online-indicator"></span>` : ''}
            `;
        }

        // 2. Update the visible sender label (the important part!)
        const labelEl = el.querySelector('.sender-label .sender-name');
        if (labelEl && info.full_name !== 'Unknown') {
            labelEl.textContent = info.full_name;
        }
    });
}

// ============================================
// ✅ Pre-fetch sender info for a group chat (optimized)
// ============================================
// - Caches per-group so subsequent opens skip network entirely
// - Fetches unknown senders in PARALLEL (not sequentially)
// - Hard 3-second timeout so a slow backend can't freeze the UI
// ============================================
const _prefetchedGroups = new Set();

async function prefetchGroupSenders(groupId) {
    if (_prefetchedGroups.has(String(groupId))) {
        return;
    }

    try {
        // 1. Get the member list for this group
        const infoRes = await fetch(`/admin/messages/groups/${groupId}/info`);
        if (!infoRes.ok) return;

        const info = await infoRes.json();
        const members = info.members || [];

        // 2. Seed the sender cache with every group member.
        //    ⚠️ NOT allContacts — that would leak members into the sidebar.
        members.forEach(member => {
            const type = member.user_type || 'alumni';
            const key = `${type}:${member.id}`;

            if (senderCache.has(key)) return;

            senderCache.set(key, {
                id: member.id,
                type: type,
                full_name: member.full_name || 'Unknown',
                initials: member.initials || initialsFromName(member.full_name) || '?',
                avatar: member.avatar || null,
                is_online: member.is_online || false,
                program: member.program || '',
                batch: member.batch || '-',
                admin_role: member.admin_role || null,
            });
        });

        // 3. Fetch the message list to find any ex-member senders
        const messages = await (async () => {
            const res = await fetch(`/admin/messages/groups/${groupId}/messages?limit=50&offset=0`);
            if (!res.ok) return [];
            const data = await res.json();
            return Array.isArray(data) ? data : (data.messages || []);
        })();

        // 4. Collect unknown sender IDs
        const unknownSenders = new Set();
        messages.forEach(msg => {
            if (!msg.sender_id) return;
            if (msg.sender_id == adminId && msg.sender_type === 'admin') return;
            const type = msg.sender_type || 'alumni';
            const key = `${type}:${msg.sender_id}`;
            if (!senderCache.has(key)) {
                unknownSenders.add(`${msg.sender_id}|${type}`);
            }
        });

        // 5. Fetch unknowns IN PARALLEL, with a hard timeout
        if (unknownSenders.size > 0) {
            const fetchPromises = Array.from(unknownSenders).map(k => {
                const [id, type] = k.split('|');
                return fetchContactInfoForAvatar(parseInt(id), type);
            });
            const timeoutPromise = new Promise(resolve => setTimeout(resolve, 3000));
            await Promise.race([Promise.all(fetchPromises), timeoutPromise]);
        }

        _prefetchedGroups.add(String(groupId));
    } catch (err) {
        console.warn('prefetchGroupSenders failed:', err);
    }
}

// ============================================
// ✅ Helper: derive initials from a full name
// ============================================
function initialsFromName(name) {
    if (!name) return '?';
    const parts = name.trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) return '?';
    if (parts.length === 1) return parts[0].charAt(0).toUpperCase();
    return (parts[0].charAt(0) + parts[parts.length - 1].charAt(0)).toUpperCase();
}

// LOAD GROUP MESSAGES
async function loadGroupMessages(groupId) {
    const container = document.getElementById('chatMessages');
    container.innerHTML = '<div class="loading-spinner"><i class="fa-solid fa-spinner fa-spin"></i> Loading messages...</div>';
    
    // Reset pagination for group messages
    currentOffset = 0;
    hasMoreMessages = true;
    
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}/messages?limit=${MESSAGES_PER_PAGE}&offset=0`);
        if (!response.ok) throw new Error('Failed to load messages');
        
        const data = await response.json();
        
        // ✅ Handle both old and new response formats
        let messages = [];
        let total = 0;
        
        if (Array.isArray(data)) {
            // Old format: just an array of messages
            messages = data;
            total = data.length;
        } else if (data.messages && Array.isArray(data.messages)) {
            // New format: { messages: [...], total: X, limit: Y, offset: Z }
            messages = data.messages;
            total = data.total || messages.length;
        } else {
            // Unexpected format
            console.error('Unexpected response format:', data);
            messages = [];
            total = 0;
        }
        
        // Check if there are more messages
        hasMoreMessages = messages.length < total;
        
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

        // ✅ Reset textarea
        input.value = '';
        resetTextareaHeight();

        input.focus();
        
        // Setup infinite scroll for group messages
        setupInfiniteScroll(groupId, 'group');
        
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
    let lastSenderId = null;
    let lastSenderType = null;

    messages.forEach((msg) => {
        const msgDate = new Date(msg.created_at);
        const localDateStr = msgDate.toISOString().split('T')[0];

        if (localDateStr !== lastDate) {
            html += `<div class="date-divider"><span>${formatDateDivider(msgDate)}</span></div>`;
            lastDate = localDateStr;
            lastSenderId = null;
            lastSenderType = null;
        }

        const isSent = (msg.sender_id == adminId && msg.sender_type === 'admin');
        const senderName = msg.sender_name || 'Unknown';

        // Same sender as previous? (breaks on sent msg)
        const isSameSender =
            !isSent &&
            lastSenderId === msg.sender_id &&
            lastSenderType === (msg.sender_type || 'alumni');

        const avatarInfo = getSenderAvatarInfo(msg.sender_id, msg.sender_type);

        let avatarHtml = '';
        if (!isSent && !isSameSender) {
            avatarHtml = `
                <div class="message-avatar" style="background: ${avatarInfo.color};">
                    ${avatarInfo.photo
                        ? `<img src="${avatarInfo.photo}" alt="${escapeHtml(senderName)}">`
                        : `<span>${escapeHtml(senderName.charAt(0).toUpperCase())}</span>`
                    }
                    ${avatarInfo.is_online ? `<span class="online-indicator"></span>` : ''}
                </div>
            `;
        }

        html += `
            <div class="message-group ${isSent ? 'sent' : 'received'} ${!isSent && isSameSender ? 'same-sender' : ''}"
                 data-msg-id="${msg.id}"
                 data-sender-name="${escapeHtml(senderName)}"
                 data-sender-id="${msg.sender_id}"
                 data-sender-type="${msg.sender_type || 'alumni'}">
                <div class="message-wrapper">
                    ${!isSent ? avatarHtml : ''}
                    <div class="message-bubble">
                        ${buildBubbleInnerHTML(msg, isSent, senderName)}
                    </div>
                </div>
            </div>
        `;

        if (!isSent) {
            lastSenderId = msg.sender_id;
            lastSenderType = msg.sender_type || 'alumni';
        } else {
            lastSenderId = null;
            lastSenderType = null;
        }
    });

    container.innerHTML = html;
}

function appendGroupMessage(msg) {
    const container = document.getElementById('chatMessages');

    // De-dupe
    if (msg.id && !msg.id.toString().startsWith('temp-')) {
        const existing = document.querySelector(`[data-msg-id="${msg.id}"]`);
        if (existing) {
            if (msg.attachments && msg.attachments.length > 0) {
                const existingAttach = existing.querySelector('.message-attachment');
                if (!existingAttach || existingAttach.classList.contains('uploading')) {
                    updateExistingGroupMessageWithAttachments(existing, msg);
                }
            }
            return;
        }
    }

    const isSent = msg.sender_id == adminId;
    const senderName = msg.sender_name || 'Unknown';

    // Detect same sender as the last received message
    let isSameSender = false;
    let lastEl = container.lastElementChild;
    while (lastEl && lastEl.classList.contains('date-divider')) {
        lastEl = lastEl.previousElementSibling;
    }
    if (
        !isSent &&
        lastEl &&
        lastEl.classList.contains('received') &&
        lastEl.dataset.senderId == msg.sender_id &&
        lastEl.dataset.senderType === (msg.sender_type || 'alumni')
    ) {
        isSameSender = true;
    }

    const avatarInfo = getSenderAvatarInfo(msg.sender_id, msg.sender_type);

    let avatarHtml = '';
    if (!isSent && !isSameSender) {
        avatarHtml = `
            <div class="message-avatar" style="background: ${avatarInfo.color};">
                ${avatarInfo.photo
                    ? `<img src="${avatarInfo.photo}" alt="${escapeHtml(senderName)}">`
                    : `<span>${escapeHtml(senderName.charAt(0).toUpperCase())}</span>`
                }
                ${avatarInfo.is_online ? `<span class="online-indicator"></span>` : ''}
            </div>
        `;
    }

    const messageHtml = `
        <div class="message-group ${isSent ? 'sent' : 'received'} ${!isSent && isSameSender ? 'same-sender' : ''}"
             data-msg-id="${msg.id}"
             data-sender-name="${escapeHtml(senderName)}"
             data-sender-id="${msg.sender_id}"
             data-sender-type="${msg.sender_type || 'alumni'}">
            <div class="message-wrapper">
                ${!isSent ? avatarHtml : ''}
                <div class="message-bubble">
                    ${buildBubbleInnerHTML(msg, isSent, senderName)}
                </div>
            </div>
        </div>
    `;

    const emptyState = container.querySelector('.empty-state');
    if (emptyState) emptyState.remove();

    container.insertAdjacentHTML('beforeend', messageHtml);

    if (!isSent) {
        updateGroupWithNewMessage(msg.group_chat_id, msg, msg.created_at, false);
    }
}

// Helper: Update existing group message with attachments
function updateExistingGroupMessageWithAttachments(messageElement, msg) {
    const bubble = messageElement.querySelector('.message-bubble');
    if (!bubble) return;
    
    // Remove any existing attachment containers (especially uploading ones)
    const existingAttach = bubble.querySelector('.message-attachment');
    if (existingAttach) existingAttach.remove();
    
    // Add the new attachments
    if (msg.attachments && msg.attachments.length > 0) {
        const attachmentsHtml = renderAttachments(msg.attachments, true);
        if (attachmentsHtml) {
            const timeElement = bubble.querySelector('.msg-time');
            if (timeElement) {
                timeElement.insertAdjacentHTML('beforebegin', attachmentsHtml);
            } else {
                bubble.insertAdjacentHTML('beforeend', attachmentsHtml);
            }
        }
    }
}

// ============================================
// SEND GROUP MESSAGE (with attachments support)
// ============================================
async function sendGroupMessage() {
    const input = document.getElementById('messageInput');
    const content = input.value.trim();
    
    if (!currentChat || currentChat.type !== 'group') return;
    
    // Check if group is archived
    const group = allGroups.find(g => g.id == currentChat.id);
    if (group && group.is_archived) {
        showToast('Cannot send messages to archived channel. Unarchive it first.', 'error');
        return;
    }
    
    // If there are files, send with attachments
    if (selectedFiles.length > 0) {
        input.value = '';
        resetTextareaHeight();  // ✅ ADD THIS
        input.focus();
        
        const tempId = 'temp-' + Date.now();
        const fileNames = selectedFiles.map(f => f.name).join(', ');
        const tempContent = content || `📎 ${fileNames}`;
        
        // Create temp attachments with uploading state
        const tempAttachments = selectedFiles.map(f => ({
            id: 'temp-' + Date.now() + '-' + Math.random(),
            type: f.type.startsWith('image/') ? 'image' : 'document',
            name: f.name,
            size: f.size,
            uploading: true,
            url: null
        }));
        
        const tempMessage = {
            id: tempId,
            content: tempContent,
            sender_id: adminId,
            sender_type: 'admin',
            sender_name: 'You',
            group_chat_id: currentChat.id,
            created_at: new Date().toISOString(),
            attachments: tempAttachments
        };
        
        appendGroupMessage(tempMessage);
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
                    receiver_type: 'group',
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
            formData.append('content', content || '');
            filesToUpload.forEach(file => {
                formData.append('attachments[]', file);
            });
            
            // ✅ This URL is correct - it matches the route
            const response = await fetch(`/admin/messages/groups/${currentChat.id}/send-attachments`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            const responseText = await response.text();
            console.log('📤 Group send-attachments response:', responseText);
            
            let data;
            try {
                data = JSON.parse(responseText);
            } catch (e) {
                console.error('❌ Failed to parse response:', responseText);
                throw new Error('Server returned invalid response');
            }
            
            if (!response.ok) {
                throw new Error(data.error || `Server error: ${response.status}`);
            }
            
            if (data.success && data.message) {
                // Remove temp message
                const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
                if (tempElement) tempElement.remove();
                
                // Fix UTC timestamp
                const utcTimestamp = data.message.created_at;
                
                // Get sender name for the message
                let senderName = 'You';
                if (data.message.sender_id != adminId) {
                    const contact = allContacts.find(c => c.id == data.message.sender_id && c.type === data.message.sender_type);
                    senderName = contact ? contact.full_name : 'Unknown';
                }
                
                // Process attachments from server response
                let finalAttachments = [];
                if (data.message.attachments && data.message.attachments.length > 0) {
                    finalAttachments = data.message.attachments.map(att => ({
                        id: att.id,
                        type: att.type || 'document',
                        name: att.name || 'File',
                        size: att.size || 0,
                        url: att.url || null,
                        uploading: false
                    }));
                    console.log('📎 Final attachments from server:', finalAttachments);
                }
                
                const finalMessage = {
                    id: data.message.id,
                    content: data.message.content || content || '📎 Attachment',
                    sender_id: data.message.sender_id || adminId,
                    sender_type: data.message.sender_type || 'admin',
                    sender_name: senderName,
                    group_chat_id: data.message.group_chat_id || currentChat.id,
                    created_at: utcTimestamp,
                    time: formatTime(new Date(utcTimestamp)),
                    attachments: finalAttachments
                };
                
                appendGroupMessage(finalMessage);
                scrollToBottom();
                
                // Update group in list
                updateGroupWithNewMessage(currentChat.id, finalMessage, utcTimestamp, true);
                
                showToast('Message sent successfully');
            }
        } catch (error) {
            console.error('❌ Error sending files:', error);
            const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
            if (tempElement) tempElement.remove();
            showToast('Failed to send files. Please try again.', 'error');
        }
        return;
    }
    
    // No files, send text message
    if (!content) return;
    
    clearTimeout(typingTimeout);
    input.value = '';
    resetTextareaHeight();  // ✅ ADD THIS
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
            
            // Fix UTC timestamp
            const utcTimestamp = data.message.created_at;
            data.message.created_at = utcTimestamp;
            data.message.time = formatTime(new Date(utcTimestamp));
            
            appendGroupMessage(data.message);
            scrollToBottom();
            
            // Update group in list
            updateGroupWithNewMessage(currentChat.id, data.message, utcTimestamp, true);
        }
    } catch (error) {
        console.error('Error sending group message:', error);
        const tempElement = document.querySelector(`[data-msg-id="${tempId}"]`);
        if (tempElement) tempElement.remove();
        input.value = content;
        showToast('Failed to send message. Please try again.', 'error');
    }
}

// Replace the updateGroupWithNewMessage function with this corrected version:

function updateGroupWithNewMessage(groupId, contentOrMsg, timestamp, fromMe = true) {
    const group = allGroups.find(g => g.id == groupId);
    if (!group) {
        console.warn('⚠️ Group not found for update:', groupId);
        return;
    }

    // Support both call signatures:
    //   (groupId, content, timestamp, fromMe)                ← old
    //   (groupId, {content, attachments}, timestamp, fromMe) ← new
    let content = '';
    let attachments = [];

    if (contentOrMsg && typeof contentOrMsg === 'object') {
        content     = contentOrMsg.content || '';
        attachments = contentOrMsg.attachments || [];
    } else {
        content = contentOrMsg || '';
    }

    const preview = buildPreviewText(content, attachments);

    group.last_message = preview || null;
    group.last_message_timestamp = timestamp;
    group.last_message_from_me = fromMe;
    group.type = 'group';

    allGroups = allGroups.filter(g => g.id != groupId);
    allGroups.unshift(group);
    applyFilter();
}

async function markGroupMessagesAsRead(groupId) {
    const group = allGroups.find(g => g.id == groupId);
    if (group) {
        // 1. Update local state immediately for instant UI feedback
        group.unread_count = 0;
        updateUnreadBadge();
        applyFilter();

        // 2. Persist to the backend so it survives a page refresh
        try {
            await fetch(`/admin/messages/groups/${groupId}/mark-read`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            });
        } catch (err) {
            console.error('Failed to persist group read state:', err);
        }
    }
}

function subscribeToGroupMessages(groupId) {
    // Unsubscribe from previous group channel
    if (groupRealtimeChannel) {
        try {
            supabaseClient.removeChannel(groupRealtimeChannel);
        } catch (e) {
            console.warn('Error removing previous channel:', e);
        }
        groupRealtimeChannel = null;
    }
    
    if (!supabaseClient) {
        console.warn('⚠️ Supabase not initialized, using polling');
        startGroupPolling(groupId);
        return;
    }
    
    console.log(`📡 Subscribing to group ${groupId} messages...`);
    
    // ✅ CORRECT: Add ALL listeners BEFORE subscribe
    groupRealtimeChannel = supabaseClient
        .channel(`group-realtime-${groupId}`)
        
        // ✅ Listener 1: Group messages
        .on(
            'postgres_changes',
            {
                event: 'INSERT',
                schema: 'public',
                table: 'group_messages',
                filter: `group_chat_id=eq.${groupId}`
            },
            (payload) => {
                console.log('📨 New group message (realtime):', payload.new);
                console.log('🔍 Payload table:', payload.table);
                console.log('🔍 Payload schema:', payload.schema);
                handleNewGroupMessage(payload.new);
            }
        )
        
        // ✅ Listener 2: Group attachments (added BEFORE subscribe)
        .on(
            'postgres_changes',
            {
                event: 'INSERT',
                schema: 'public',
                table: 'group_messages_attachments',
            },
            (payload) => {
                console.log('📎 New group attachment (realtime):', payload.new);
                handleGroupAttachmentInsert(payload.new);
            }
        )
        
        // ✅ NOW subscribe after ALL listeners are added
        .subscribe((status, err) => {
            console.log(`📡 Group ${groupId} subscription status:`, status);
            if (err) {
                console.error('❌ Subscription error:', err);
            }
            if (status === 'SUBSCRIBED') {
                console.log(`✅ Subscribed to group ${groupId} messages`);
                if (groupPollingInterval) {
                    clearInterval(groupPollingInterval);
                    groupPollingInterval = null;
                }
            } else if (status === 'CHANNEL_ERROR') {
                console.error(`❌ Failed to subscribe to group ${groupId} messages`);
                startGroupPolling(groupId);
            }
        });
}

function handleGroupAttachmentInsert(attachment) {
    console.log('🔄 Processing new group attachment:', attachment);
    
    // ✅ Find the message element by ID - try multiple times with delay
    let attempts = 0;
    const maxAttempts = 5;
    
    function tryFindAndUpdate() {
        const messageElement = document.querySelector(`[data-msg-id="${attachment.group_message_id}"]`);
        
        if (messageElement) {
            console.log('✅ Message found, updating attachment');
            // Check if this attachment is already rendered
            const existingAttach = messageElement.querySelector('.message-attachment');
            if (existingAttach && !existingAttach.classList.contains('uploading') && !existingAttach.classList.contains('loading')) {
                console.log('⏭️ Attachment already rendered for message:', attachment.group_message_id);
                return;
            }
            
            // Fetch and update
            fetchGroupAttachments(attachment.group_message_id);
            return;
        }
        
        attempts++;
        if (attempts < maxAttempts) {
            console.log(`⏳ Message not found, retry ${attempts}/${maxAttempts}...`);
            setTimeout(tryFindAndUpdate, 500 * attempts);
        } else {
            console.log('⚠️ Message not found after retries, trying via polling fallback');
            // Try polling as fallback
            fetchGroupAttachments(attachment.group_message_id);
        }
    }
    
    tryFindAndUpdate();
}

function fetchGroupAttachments(messageId) {
    console.log('📎 Fetching attachments for message:', messageId);
    
    fetch(`/admin/messages/groups/attachments/message/${messageId}`)
        .then(response => {
            if (!response.ok) throw new Error('Failed to get attachment URL');
            return response.json();
        })
        .then(attachments => {
            if (attachments && attachments.length > 0) {
                console.log('✅ Got attachments:', attachments);
                updateGroupMessageWithAttachments(messageId, attachments);
            } else {
                console.warn('⚠️ No attachments returned for message:', messageId);
            }
        })
        .catch(error => {
            console.error('❌ Error fetching attachment URL:', error);
        });
}

function updateGroupMessageWithAttachments(messageId, attachments) {
    console.log('🔍 Updating group message with attachments:', messageId, attachments);
    
    const messageElement = document.querySelector(`[data-msg-id="${messageId}"]`);
    if (!messageElement) {
        console.log('⚠️ Message element not found for update:', messageId);
        return;
    }
    
    const bubble = messageElement.querySelector('.message-bubble');
    if (!bubble) {
        console.log('⚠️ No message bubble found');
        return;
    }
    
    // Remove any existing attachment containers (especially loading ones)
    const existingAttach = bubble.querySelector('.message-attachment');
    if (existingAttach) {
        existingAttach.remove();
    }
    
    // Format attachments for rendering
    const formattedAttachments = attachments.map(att => ({
        id: att.id,
        type: att.attachment_type || 'document',
        name: att.file_name || 'File',
        size: att.file_size || 0,
        url: att.url || null,
        uploading: false
    }));
    
    // Render attachments
    const attachmentsHtml = renderAttachments(formattedAttachments, false);
    if (attachmentsHtml) {
        // Insert before the time element or at the end
        insertAttachmentIntoBubble(bubble, attachmentContainer);
        console.log('✅ Attachments rendered for message:', messageId);
    }
}

// GROUP POLLING FALLBACK
let groupPollingInterval = null;

function startGroupPolling(groupId) {
    if (groupPollingInterval) {
        clearInterval(groupPollingInterval);
        groupPollingInterval = null;
    }
    
    console.log('🔄 Starting group polling (every 2 seconds)...');
    groupPollingInterval = setInterval(() => {
        checkForNewGroupMessages(groupId);
    }, 2000);
}

async function checkForNewGroupMessages(groupId) {
    if (!currentChat || currentChat.type !== 'group' || currentChat.id != groupId) return;
    
    try {
        const response = await fetch(`/admin/messages/groups/${groupId}/messages?limit=10&offset=0`);
        if (!response.ok) return;
        
        const data = await response.json();
        const messageList = data.messages || [];
        
        if (messageList && messageList.length > 0) {
            let hasNewMessages = false;
            
            // Get the last message ID currently displayed
            const lastMsgElement = document.querySelector('.message-group:last-child');
            let lastDisplayedId = null;
            if (lastMsgElement) {
                lastDisplayedId = parseInt(lastMsgElement.dataset.msgId);
            }
            
            // Process messages in reverse (oldest first) to maintain order
            const reversedMessages = [...messageList].reverse();
            
            for (const msg of reversedMessages) {
                // Skip if message is from current user or already displayed
                if (msg.sender_id == adminId) continue;
                
                const existingMsg = document.querySelector(`[data-msg-id="${msg.id}"]`);
                if (!existingMsg) {
                    // Decrypt content
                    let decryptedContent = msg.content;
                    if (msg.content && (msg.content.startsWith('enc:') || msg.content.startsWith('U2FsdGVkX1'))) {
                        decryptedContent = await decryptContent(msg.content, msg.sender_type, 'admin');
                    }
                    
                    // ✅ FETCH ATTACHMENTS for polling too
                    let attachments = [];
                    try {
                        const attachResponse = await fetch(`/admin/messages/groups/attachments/message/${msg.id}`);
                        if (attachResponse.ok) {
                            const attachData = await attachResponse.json();
                            if (attachData && attachData.length > 0) {
                                attachments = attachData.map(att => ({
                                    id: att.id,
                                    type: att.attachment_type || 'document',
                                    name: att.file_name || 'File',
                                    size: att.file_size || 0,
                                    url: att.url || null,
                                    uploading: false
                                }));
                            }
                        }
                    } catch (e) {
                        console.warn('Could not fetch attachments for polled message:', e);
                    }
                    
                    const formattedMsg = {
                        ...msg,
                        content: decryptedContent,
                        time: msg.time || formatTime(new Date(msg.created_at)),
                        attachments: attachments
                    };
                    
                    appendGroupMessage(formattedMsg);
                    hasNewMessages = true;
                }
            }
            
            if (hasNewMessages) {
                scrollToBottom();
                markGroupMessagesAsRead(groupId);
                loadGroupConversations(); // Update unread count
            }
        }
    } catch (error) {
        console.error('Group polling error:', error);
    }
}

async function handleNewGroupMessage(message) {
    if (!message || !message.id) return;
    if (message.sender_id == adminId && message.sender_type === 'admin') return;

    const existing = document.querySelector(`[data-msg-id="${message.id}"]`);
    if (existing) return;

    // ✅ Decrypt up front, regardless of whether we're viewing the group
    const decryptedContent = await decryptContent(
        message.content,
        message.sender_type,
        'admin'
    );

    if (!currentChat || currentChat.type !== 'group' || currentChat.id != message.group_chat_id) {
        updateGroupWithNewMessage(
            message.group_chat_id,
            { content: decryptedContent, attachments: [] },
            message.created_at,
            false
        );
        return;
    }
    
    console.log('✅ Viewing this group, processing message');
    
    // ✅ Get sender name
    let senderName = 'Unknown';
    if (message.sender_type === 'alumni') {
        const contact = allContacts.find(c => c.id == message.sender_id && c.type === 'alumni');
        if (contact) {
            senderName = contact.full_name;
        } else {
            fetchContactInfoForAvatar(message.sender_id, 'alumni');
            senderName = 'Alumni #' + message.sender_id;
        }
    } else if (message.sender_type === 'admin') {
        const contact = allContacts.find(c => c.id == message.sender_id && c.type === 'admin');
        if (contact) {
            senderName = contact.full_name;
        } else {
            fetchContactInfoForAvatar(message.sender_id, 'admin');
            senderName = 'Admin #' + message.sender_id;
        }
    }
    
    // ✅ Reuse the already-decrypted content
    const utcTimestamp = message.created_at;

    fetchAttachmentsForMessage(message.id).then(attachments => {
        const formattedMessage = {
            id: message.id,
            content: decryptedContent,   // ✅ reuse, do NOT call decryptContent again
            sender_id: message.sender_id,
            sender_type: message.sender_type,
            sender_name: senderName,
            group_chat_id: message.group_chat_id,
            created_at: utcTimestamp,
            time: formatTime(new Date(utcTimestamp)),
            attachments: attachments
        };

        appendGroupMessage(formattedMessage);
        scrollToBottom();
        markGroupMessagesAsRead(message.group_chat_id);
        updateGroupWithNewMessage(
            message.group_chat_id,
            { content: decryptedContent, attachments: [] },   // attachments filled in later if needed
            utcTimestamp,
            false
        );
        
    });
}

// Helper function to fetch attachments for a message
function fetchAttachmentsForMessage(messageId) {
    console.log('📎 fetchAttachmentsForMessage called for:', messageId);
    return fetch(`/admin/messages/groups/attachments/message/${messageId}`)
        .then(response => {
            if (response.ok) return response.json();
            return [];
        })
        .then(attachData => {
            console.log('📎 Attachments for message:', attachData);
            if (attachData && attachData.length > 0) {
                return attachData.map(att => ({
                    id: att.id,
                    type: att.attachment_type || 'document',
                    name: att.file_name || 'File',
                    size: att.file_size || 0,
                    url: att.url || null,
                    uploading: false
                }));
            }
            return [];
        })
        .catch(error => {
            console.error('❌ Error fetching attachments:', error);
            return [];
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
        
        // ✅ Reset textarea
        input.value = '';
        resetTextareaHeight();
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
    
    // ✅ Deduplicate members using compound key (id + user_type)
    const uniqueMembers = new Map();
    const members = [];
    
    selectedGroupMembers.forEach(m => {
        const key = m.id + '_' + m.user_type;
        if (!uniqueMembers.has(key)) {
            uniqueMembers.set(key, true);
            // Filter out current admin
            if (!(m.id == adminId && m.user_type === 'admin')) {
                members.push({ 
                    id: m.id, 
                    user_type: m.user_type 
                });
            }
        }
    });
    
    if (!name) {
        showToast('Please enter a channel name', 'error');
        return;
    }
    if (members.length < 2) {
        showToast('Please add at least 2 members (excluding yourself)', 'error');
        return;
    }
    
    // Log what we're sending
    console.log('📤 Creating group with members:', members);
    console.log('📤 Current admin ID:', adminId);
    
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
                const newGroup = {
                    ...data.group,
                    type: 'group',
                    is_archived: false,
                    is_muted: false,
                    unread_count: 0,
                    last_message: null,
                    last_message_timestamp: null,
                    last_message_from_me: false,
                };
                allGroups.unshift(newGroup);
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
    
    // Update member count badge
    const badge = document.getElementById('memberCountBadge');
    if (badge && members) {
        badge.textContent = members.length;
    }
    
    if (!members || members.length === 0) {
        container.innerHTML = `
            <div style="text-align: center; padding: 1.5rem; color: var(--gray-400);">
                <i class="fa-solid fa-user-group" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                <p style="margin: 0; font-size: 0.85rem;">No members found</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = members.map(member => {
        const isGroupAdmin = member.is_group_admin || false;
        const isSystemAdmin = member.is_system_admin || false;
        const isCreator = member.is_creator || false;
        const isSelf = (member.id == adminId && member.user_type === 'admin'); 
        const canManage = currentGroupInfo && currentGroupInfo.can_manage;
        
        // Determine badge HTML
        let badgeHtml = '';
        if (isSelf) {
            badgeHtml = `<span class="badge you">You</span>`;
        }
        if (isCreator) {
            badgeHtml += `<span class="badge creator"><i class="fa-solid fa-crown" style="font-size: 0.45rem;"></i> Creator</span>`;
        }
        if (isSystemAdmin && !isCreator) {
            badgeHtml += `<span class="badge system-admin"><i class="fa-solid fa-shield-halved" style="font-size: 0.45rem;"></i> System Admin</span>`;
        }
        if (isGroupAdmin && !isCreator && !isSystemAdmin) {
            badgeHtml += `<span class="badge group-admin"><i class="fa-solid fa-user-tie" style="font-size: 0.45rem;"></i> Group Admin</span>`;
        }
        
        // Avatar background color based on user type from the server
        const avatarBg = member.user_type === 'admin' 
            ? 'linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light))'
            : 'linear-gradient(135deg, #8b5cf6, #6d28d9)';
        
        const isOnline = member.is_online || false;
        
        return `
            <div class="group-member-item" style="display: flex; align-items: center; padding: 0.625rem 0.75rem; border-radius: 12px; transition: all 0.2s ease; border: 1px solid transparent; margin-bottom: 0.25rem; background: var(--white);">
                <div class="member-avatar-wrapper" style="position: relative; flex-shrink: 0; margin-right: 0.75rem;">
                    <div class="member-avatar" style="width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.8rem; color: white; background: ${avatarBg};">
                        ${member.avatar 
                            ? `<img src="${member.avatar}" alt="${escapeHtml(member.full_name)}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` 
                            : (member.initials || '??')
                        }
                    </div>
                    <span class="online-indicator ${isOnline ? '' : 'offline'}" style="position: absolute; bottom: -1px; right: -1px; width: 12px; height: 12px; border-radius: 50%; background: ${isOnline ? 'var(--success)' : 'var(--gray-300)'}; border: 2.5px solid var(--white); box-shadow: 0 0 0 2px ${isOnline ? 'var(--success-light)' : 'var(--gray-200)'};"></span>
                </div>
                <div class="member-info" style="flex: 1; min-width: 0;">
                    <div class="member-name" style="font-weight: 600; font-size: 0.875rem; color: var(--gray-800); display: flex; align-items: center; gap: 0.375rem; flex-wrap: wrap;">
                        ${escapeHtml(member.full_name)}
                        ${badgeHtml}
                    </div>
                    <div class="member-role-text" style="font-size: 0.7rem; color: var(--gray-500); margin-top: 0.125rem;">
                        ${member.user_type === 'admin' ? '👑 Admin Account' : '🎓 Alumni Account'}
                        ${isOnline ? ' • <span style="color: var(--success); font-weight: 500;">Online</span>' : ' • <span style="color: var(--gray-400);">Offline</span>'}
                    </div>
                </div>
                ${canManage && !isSelf && !isCreator ? `
                    <div class="member-actions" style="display: flex; gap: 0.25rem; opacity: 0; transition: opacity 0.2s ease;">
                        ${isGroupAdmin ? `
                            <button onclick="updateMemberRole(${member.id}, 'alumni')" 
                                style="background: none; border: 1px solid var(--gray-200); border-radius: 6px; padding: 0.25rem 0.5rem; cursor: pointer; font-size: 0.65rem; color: var(--gray-600); transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="fa-solid fa-user"></i> Demote
                            </button>
                        ` : `
                            <button onclick="updateMemberRole(${member.id}, 'admin')" 
                                style="background: none; border: 1px solid var(--gray-200); border-radius: 6px; padding: 0.25rem 0.5rem; cursor: pointer; font-size: 0.65rem; color: var(--gray-600); transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;">
                                <i class="fa-solid fa-crown"></i> Promote
                            </button>
                        `}
                        <button onclick="removeGroupMember(${member.id})" 
                            style="background: none; border: 1px solid var(--danger); border-radius: 6px; padding: 0.25rem 0.5rem; cursor: pointer; font-size: 0.65rem; color: var(--danger); transition: all 0.2s ease; display: flex; align-items: center; gap: 0.25rem;">
                            <i class="fa-solid fa-xmark"></i> Remove
                        </button>
                    </div>
                ` : ''}
            </div>
        `;
    }).join('');
    
    // Add hover effect
    container.querySelectorAll('.group-member-item').forEach(item => {
        item.addEventListener('mouseenter', function() {
            const actions = this.querySelector('.member-actions');
            if (actions) {
                actions.style.opacity = '1';
            }
            this.style.background = 'var(--gray-50)';
            this.style.borderColor = 'var(--gray-200)';
            this.style.transform = 'translateX(2px)';
        });
        item.addEventListener('mouseleave', function() {
            const actions = this.querySelector('.member-actions');
            if (actions) {
                actions.style.opacity = '0';
            }
            this.style.background = 'var(--white)';
            this.style.borderColor = 'transparent';
            this.style.transform = 'translateX(0)';
        });
    });
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
    
    resultsContainer.innerHTML = '<div class="loading-spinner" style="padding: 1.5rem;"><i class="fa-solid fa-spinner fa-spin"></i> Searching...</div>';
    
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(async () => {
        try {
            const response = await fetch(`/admin/messages/groups/search/alumni?q=${encodeURIComponent(query)}`);
            const data = await response.json();
            
            if (!response.ok) {
                resultsContainer.innerHTML = `<p style="color: var(--danger); text-align: center; padding: 1rem;">Error searching</p>`;
                return;
            }
            
            if (data.length === 0) {
                resultsContainer.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 1.5rem;">No users found</p>';
                return;
            }
            
            // ✅ Filter out already selected members using compound key (id + user_type)
            const filtered = data.filter(a => {
                // ✅ Use compound key for checking if already selected
                const compoundKey = a.id + '_' + a.user_type;
                const isSelected = selectedGroupMembers.some(m => 
                    (m.id == a.id && m.user_type === a.user_type)
                );
                return !isSelected;
            });
            
            if (filtered.length === 0) {
                resultsContainer.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 1.5rem;">All results already selected</p>';
                return;
            }
            
            resultsContainer.innerHTML = filtered.map(a => {
                const isAdmin = a.user_type === 'admin';
                const typeBadge = isAdmin 
                    ? `<span class="user-type-badge admin-badge" style="font-size: 0.6rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 2px 10px; border-radius: 10px; font-weight: 700; margin-left: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Admin</span>`
                    : `<span class="user-type-badge alumni-badge" style="font-size: 0.6rem; background: #8b5cf6; color: white; padding: 2px 10px; border-radius: 10px; font-weight: 600; margin-left: 8px;">Alumni</span>`;
                
                // ✅ Show user type clearly
                return `
                    <div class="alumni-item" 
                         onclick="addGroupMember(${a.id}, '${escapeHtml(a.full_name)}', '${escapeHtml(a.initials)}', '${a.user_type}', '${a.admin_role || null}')" 
                         style="padding: 0.75rem 1rem; border-radius: var(--radius-lg); margin-bottom: 0.25rem; display: flex; align-items: center; gap: 1rem; cursor: pointer; transition: all 0.2s; border: 2px solid transparent;"
                         data-compound-key="${a.id}_${a.user_type}">
                        ${a.avatar 
                            ? `<img src="${a.avatar}" class="contact-avatar-img" alt="${escapeHtml(a.full_name)}" style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; flex-shrink: 0;">`
                            : `<div class="alumni-avatar" style="width: 44px; height: 44px; border-radius: 50%; background: ${isAdmin ? 'var(--nu-blue)' : '#8b5cf6'}; color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0;">${a.initials || '?'}</div>`
                        }
                        <div class="alumni-info" style="flex: 1;">
                            <div class="name" style="font-weight: 600; font-size: 0.95rem; color: var(--gray-800); display: flex; align-items: center; flex-wrap: wrap; gap: 0.25rem;">
                                ${escapeHtml(a.full_name)}
                                ${typeBadge}
                            </div>
                            <div class="details" style="font-size: 0.8rem; color: var(--gray-500); margin-top: 0.125rem;">
                                ${isAdmin 
                                    ? `👑 ${escapeHtml(a.admin_role || 'Admin Staff')}`
                                    : `🎓 Batch ${a.batch || 'N/A'} | ${a.program || 'N/A'}`
                                }
                            </div>
                        </div>
                        ${a.is_online ? '<span class="online-dot" title="Online" style="width: 10px; height: 10px; border-radius: 50%; background: var(--success); flex-shrink: 0; box-shadow: 0 0 0 3px var(--success-light);"></span>' : ''}
                    </div>
                `;
            }).join('');
            
            // Add hover effects
            resultsContainer.querySelectorAll('.alumni-item').forEach(el => {
                el.addEventListener('mouseenter', function() {
                    this.style.background = 'var(--gray-50)';
                    this.style.borderColor = 'var(--gray-200)';
                    this.style.transform = 'translateX(4px)';
                });
                el.addEventListener('mouseleave', function() {
                    this.style.background = 'transparent';
                    this.style.borderColor = 'transparent';
                    this.style.transform = 'translateX(0)';
                });
            });
                    
        } catch (error) {
            console.error('Error searching:', error);
            resultsContainer.innerHTML = '<p style="color: var(--danger); text-align: center; padding: 1rem;">Error searching. Please try again.</p>';
        }
    }, 300);
}

function addGroupMember(id, name, initials, userType = 'alumni', adminRole = null) {
    // ✅ Use compound key: id + userType
    const compoundKey = id + '_' + userType;
    
    // ✅ Check if already selected using compound key
    if (selectedGroupMembers.some(m => (m.id == id && m.user_type === userType))) {
        showToast('This user is already selected', 'info');
        return;
    }
    
    // ✅ Prevent adding the current admin (they are automatically added as creator)
    if (id == adminId && userType === 'admin') {
        showToast('You are already the channel creator', 'info');
        return;
    }
    
    selectedGroupMembers.push({ 
        id: id, 
        name: name, 
        initials: initials, 
        user_type: userType,
        admin_role: adminRole,
        _key: compoundKey
    });
    
    renderSelectedGroupMembers();
    document.getElementById('groupMemberSearch').value = '';
    document.getElementById('groupMemberResults').innerHTML = '';
}

function removeGroupMember(id, userType) {
    // ✅ Use compound key for removal too
    selectedGroupMembers = selectedGroupMembers.filter(m => !(m.id == id && m.user_type === userType));
    renderSelectedGroupMembers();
}

function renderSelectedGroupMembers() {
    const container = document.getElementById('selectedMembers');
    
    if (selectedGroupMembers.length === 0) {
        container.innerHTML = '<span style="color: var(--gray-400); font-size: 0.8rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>';
        return;
    }
    
    container.innerHTML = selectedGroupMembers.map(m => {
        const isAdmin = m.user_type === 'admin';
        const bgColor = isAdmin ? 'var(--nu-blue-soft)' : '#ede9fe';
        const textColor = isAdmin ? 'var(--nu-blue)' : '#6d28d9';
        
        return `
            <span class="selected-member-tag" style="display: inline-flex; align-items: center; gap: 0.375rem; background: ${bgColor}; color: ${textColor}; padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                ${escapeHtml(m.name)}
                ${m.user_type === 'admin' 
                    ? `<span style="font-size: 0.55rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 1px 10px; border-radius: 8px; font-weight: 700; margin-left: 4px; text-transform: uppercase; letter-spacing: 0.5px;">${escapeHtml(m.admin_role || 'Admin')}</span>` 
                    : `<span style="font-size: 0.55rem; background: #8b5cf6; color: white; padding: 1px 8px; border-radius: 8px; font-weight: 600; margin-left: 4px;">Alumni</span>`
                }
                <span class="remove-member" onclick="removeGroupMember(${m.id}, '${m.user_type}')" style="cursor: pointer; color: var(--gray-400); font-size: 0.7rem; margin-left: 2px;">✕</span>
            </span>
        `;
    }).join('');
}

function openAddMembersModal() {
    // Reset state
    selectedAddMembers = [];
    document.getElementById('addMemberSearch').value = '';
    document.getElementById('addMemberResults').innerHTML = `
        <div style="text-align: center; padding: 2rem 0; color: var(--gray-400);">
            <i class="fa-solid fa-users" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
            <p style="font-size: 0.9rem;">Search for users to add to this channel</p>
            <p style="font-size: 0.8rem; margin-top: 0.25rem;">Type at least 2 characters to start searching</p>
        </div>
    `;
    renderSelectedAddMembers();
    
    // Show modal
    document.getElementById('addMembersModal').classList.add('active');
    
    // Focus search input after a delay
    setTimeout(() => {
        document.getElementById('addMemberSearch').focus();
    }, 300);
}

function closeAddMembersModal() {
    document.getElementById('addMembersModal').classList.remove('active');
    selectedAddMembers = [];
}

// Search users for adding to group
function searchUsersForAdd() {
    const query = document.getElementById('addMemberSearch').value.trim();
    const resultsContainer = document.getElementById('addMemberResults');
    
    if (query.length < 2) {
        resultsContainer.innerHTML = `
            <div style="text-align: center; padding: 2rem 0; color: var(--gray-400);">
                <i class="fa-solid fa-users" style="font-size: 2rem; display: block; margin-bottom: 0.5rem; opacity: 0.3;"></i>
                <p style="font-size: 0.9rem;">Search for users to add to this channel</p>
                <p style="font-size: 0.8rem; margin-top: 0.25rem;">Type at least 2 characters to start searching</p>
            </div>
        `;
        return;
    }
    
    resultsContainer.innerHTML = `
        <div class="loading-state">
            <i class="fa-solid fa-spinner fa-spin"></i> Searching...
        </div>
    `;
    
    clearTimeout(addMemberSearchTimeout);
    addMemberSearchTimeout = setTimeout(async () => {
        try {
            // Get current group members to exclude them
            const currentMemberIds = currentGroupInfo?.members?.map(m => m.id) || [];
            
            // Use the existing search endpoint
            const response = await fetch(`/admin/messages/groups/search/alumni?q=${encodeURIComponent(query)}`);
            
            if (!response.ok) {
                throw new Error('Search failed');
            }
            
            const data = await response.json();
            addMemberResultsCache = data;
            
            // Filter out already selected and existing members
            const filtered = data.filter(user => {
                // Exclude current user
                if (user.id == adminId) return false;
                // Exclude already in group
                if (currentMemberIds.includes(user.id)) return false;
                // Exclude already selected for addition
                if (selectedAddMembers.some(m => m.id == user.id && m.user_type === user.user_type)) return false;
                return true;
            });
            
            if (filtered.length === 0) {
                resultsContainer.innerHTML = `
                    <div class="empty-state">
                        <i class="fa-solid fa-user-slash"></i>
                        <p>No users available to add</p>
                        <p style="font-size: 0.8rem; margin-top: 0.25rem;">All matching users are either already in the channel or selected</p>
                    </div>
                `;
                return;
            }
            
            resultsContainer.innerHTML = filtered.map(user => {
                const isAdmin = user.user_type === 'admin';
                const isOnline = user.is_online || false;
                const avatarBg = isAdmin 
                    ? 'linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light))'
                    : 'linear-gradient(135deg, #8b5cf6, #6d28d9)';
                
                return `
                    <div class="user-item" data-user-id="${user.id}" data-user-type="${user.user_type}">
                        <div class="user-avatar" style="background: ${avatarBg};">
                            ${user.avatar 
                                ? `<img src="${user.avatar}" alt="${escapeHtml(user.full_name)}">` 
                                : (user.initials || '??')
                            }
                        </div>
                        <div class="user-info">
                            <div class="user-name">
                                ${escapeHtml(user.full_name)}
                                ${isAdmin ? `<span class="admin-badge"><i class="fa-solid fa-shield-halved" style="font-size: 0.45rem;"></i> Admin</span>` : ''}
                            </div>
                            <div class="user-details">
                                ${isAdmin 
                                    ? `👑 ${escapeHtml(user.admin_role || 'System Admin')}`
                                    : `🎓 Batch ${escapeHtml(user.batch || 'N/A')} | ${escapeHtml(user.program || 'N/A')}`
                                }
                            </div>
                        </div>
                        <div class="user-status">
                            <span class="online-dot ${isOnline ? 'online' : 'offline'}" title="${isOnline ? 'Online' : 'Offline'}"></span>
                        </div>
                        <button class="add-btn" onclick="addUserToSelection(${user.id}, '${user.user_type}', '${escapeHtml(user.full_name)}', '${escapeHtml(user.initials || '??')}', ${isAdmin})">
                            <i class="fa-solid fa-plus"></i> Add
                        </button>
                    </div>
                `;
            }).join('');
            
        } catch (error) {
            console.error('Error searching users:', error);
            resultsContainer.innerHTML = `
                <div class="empty-state" style="color: var(--danger);">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <p>Error searching users</p>
                    <p style="font-size: 0.8rem; margin-top: 0.25rem;">Please try again</p>
                </div>
            `;
        }
    }, 400);
}

// Add user to selection
function addUserToSelection(userId, userType, fullName, initials, isAdmin) {
    // Check if already selected
    if (selectedAddMembers.some(m => m.id == userId && m.user_type === userType)) {
        return;
    }
    
    // Check if already in group
    const currentMemberIds = currentGroupInfo?.members?.map(m => m.id) || [];
    if (currentMemberIds.includes(userId)) {
        showToast('User is already in this channel', 'info');
        return;
    }
    
    selectedAddMembers.push({
        id: userId,
        user_type: userType,
        full_name: fullName,
        initials: initials,
        is_admin: isAdmin
    });
    
    // Update UI
    renderSelectedAddMembers();
    updateAddMemberSearchResults();
}

// Remove user from selection
function removeUserFromSelection(userId, userType) {
    selectedAddMembers = selectedAddMembers.filter(m => !(m.id == userId && m.user_type === userType));
    renderSelectedAddMembers();
    updateAddMemberSearchResults();
}

// Clear all selected
function clearSelectedAddMembers() {
    selectedAddMembers = [];
    renderSelectedAddMembers();
    updateAddMemberSearchResults();
}

// Render selected members tags
function renderSelectedAddMembers() {
    const container = document.getElementById('selectedAddMembers');
    const countEl = document.getElementById('selectedAddCount');
    
    countEl.textContent = selectedAddMembers.length;
    
    if (selectedAddMembers.length === 0) {
        container.innerHTML = `
            <span style="color: var(--gray-400); font-size: 0.8rem; width: 100%; text-align: center; padding: 0.25rem 0;">No members selected</span>
        `;
        return;
    }
    
    container.innerHTML = selectedAddMembers.map(m => `
        <span class="selected-tag">
            ${escapeHtml(m.full_name)}
            ${m.is_admin ? `<span style="font-size: 0.55rem; background: var(--nu-gold); color: var(--nu-blue-dark); padding: 1px 6px; border-radius: 6px; font-weight: 700; margin-left: 2px;">Admin</span>` : ''}
            <button class="remove-selected" onclick="removeUserFromSelection(${m.id}, '${m.user_type}')" title="Remove">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </span>
    `).join('');
}

// Update search results to reflect selections
function updateAddMemberSearchResults() {
    const resultsContainer = document.getElementById('addMemberResults');
    const items = resultsContainer.querySelectorAll('.user-item');
    
    items.forEach(item => {
        const userId = parseInt(item.dataset.userId);
        const userType = item.dataset.userType;
        const isSelected = selectedAddMembers.some(m => m.id == userId && m.user_type === userType);
        const addBtn = item.querySelector('.add-btn');
        
        if (isSelected) {
            item.classList.add('selected');
            addBtn.textContent = 'Added';
            addBtn.disabled = true;
            addBtn.className = 'add-btn added';
        } else {
            item.classList.remove('selected');
            addBtn.innerHTML = '<i class="fa-solid fa-plus"></i> Add';
            addBtn.disabled = false;
            addBtn.className = 'add-btn';
        }
    });
}

// Confirm and add members to group
async function confirmAddMembers() {
    if (selectedAddMembers.length === 0) {
        showToast('Please select at least one member to add', 'error');
        return;
    }
    
    const memberIds = selectedAddMembers.map(m => m.id);
    
    const confirmBtn = document.getElementById('addMembersConfirmBtn');
    confirmBtn.disabled = true;
    confirmBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Adding...';
    
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
            showToast(data.message || 'Members added successfully');
            closeAddMembersModal();
            
            // Refresh group info
            await openGroupInfo(currentGroupInfo.id);
            
            // Refresh group conversations
            await loadGroupConversations();
        } else {
            showToast(data.error || 'Failed to add members', 'error');
        }
    } catch (error) {
        console.error('Error adding members:', error);
        showToast('Failed to add members', 'error');
    } finally {
        confirmBtn.disabled = false;
        confirmBtn.innerHTML = '<i class="fa-solid fa-user-plus" style="font-size: 0.9rem;"></i> Add Members';
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

// Helper function to get group avatar URL
function getGroupAvatarUrl(avatarPath) {
    if (!avatarPath) return null;
    
    // If it's already a full URL (including signed URLs), return it
    if (avatarPath.startsWith('http://') || avatarPath.startsWith('https://')) {
        return avatarPath;
    }
    
    // Fallback: if for some reason the server didn't provide a signed URL
    const supabaseUrl = '{{ env("SUPABASE_URL") }}';
    if (supabaseUrl) {
        const baseUrl = supabaseUrl.replace(/\/$/, '');
        const cleanPath = avatarPath.replace(/^\/+/, '');
        return `${baseUrl}/storage/v1/object/public/luminus_messages_attachments/${cleanPath}`;
    }
    
    return null;
}

function getSenderAvatarInfo(senderId, senderType) {
    const colors = [
        '#32418C', '#4A59A3', '#6B7BC4', '#8B5CF6',
        '#6D28D9', '#EC4899', '#F59E0B', '#10B981',
        '#3B82F6', '#EF4444', '#8B5CF6', '#14B8A6'
    ];
    const color = colors[(parseInt(senderId) || 0) % colors.length];

    const type = senderType || 'alumni';
    const key = `${type}:${senderId}`;

    // 1. Look in the sender cache (group-chat member info lives here)
    const cached = senderCache.get(key);
    if (cached) {
        return {
            full_name: cached.full_name || 'Unknown',
            initials: cached.initials || '?',
            photo: cached.avatar || null,
            color: color,
            is_online: cached.is_online || false,
        };
    }

    // 2. Fall back to allContacts (for individual chats / previously known)
    const contact = allContacts.find(c => c.id == senderId && c.type === type);
    if (contact) {
        return {
            full_name: contact.full_name || 'Unknown',
            initials: contact.initials || '?',
            photo: contact.avatar || null,
            color: color,
            is_online: contact.is_online || false,
        };
    }

    // 3. Unknown — schedule a fetch and return placeholder for now
    fetchContactInfoForAvatar(senderId, senderType);
    return {
        full_name: 'Unknown',
        initials: '?',
        photo: null,
        color: color,
        is_online: false,
    };
}

async function fetchContactInfoForAvatar(senderId, senderType) {
    const type = senderType || 'alumni';
    const key = `${type}:${senderId}`;

    // Already cached? Return immediately.
    if (senderCache.has(key)) {
        return senderCache.get(key);
    }

    try {
        const response = await fetch(`/admin/messages/${type}/${senderId}/info`);
        if (!response.ok) return null;

        const data = await response.json();

        const contact = {
            id: data.id,
            type: data.type || type,
            full_name: data.full_name || 'Unknown',
            initials: data.initials || initialsFromName(data.full_name),
            avatar: data.avatar || null,
            is_online: data.is_online || false,
            program: data.program || '',
            batch: data.batch || '-',
            admin_role: data.admin_role || null,
        };

        // ✅ Write to the sender cache — NOT allContacts
        senderCache.set(key, contact);

        // Also update allContacts if this person is *already* a real
        // conversation (so individual-chat UI stays fresh) — but do NOT
        // add them if they aren't already there.
        const existing = allContacts.find(c => c.id == senderId && c.type === type);
        if (existing) {
            existing.full_name = contact.full_name;
            existing.initials  = contact.initials;
            existing.avatar    = contact.avatar;
            existing.is_online = contact.is_online;
        }

        return contact;
    } catch (error) {
        console.error('Error fetching contact info for avatar:', error);
        return null;
    }
}

function insertAttachmentIntoBubble(bubble, attachmentEl) {
    if (!bubble || !attachmentEl) return;
    // If there's a visible .msg-time (sent messages), insert before it.
    // Otherwise, append at the end.
    const timeEl = bubble.querySelector('.msg-time');
    if (timeEl && timeEl.offsetParent !== null) {
        // offsetParent check = element is actually rendered (not display:none)
        bubble.insertBefore(attachmentEl, timeEl);
    } else {
        bubble.appendChild(attachmentEl);
    }
}

</script>

@include('partials.media-preview-modal')
<script src="/js/media-preview-modal.js"></script>

</body>
</html>

{{-- This is the admin_messages.blade.php --}}