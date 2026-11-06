<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Registrations | {{ $event->title }} | LumiNUs Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/events_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    
    <style>
        /* Additional styles specific to registrations page */
        .event-info-banner {
            background: linear-gradient(135deg, var(--nu-blue) 0%, var(--nu-blue-dark) 100%);
            border-radius: var(--radius-2xl);
            padding: 1.75rem 2rem;
            margin-bottom: 2rem;
            color: var(--white);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            box-shadow: var(--shadow-lg);
        }
        
        .event-info-content {
            flex: 1;
            min-width: 250px;
        }
        
        .event-info-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .event-info-title i {
            color: var(--nu-gold);
        }
        
        .event-info-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.25rem;
            font-size: 0.9375rem;
            opacity: 0.9;
        }
        
        .event-info-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .event-info-meta i {
            color: var(--nu-gold);
            font-size: 0.875rem;
        }
        
        .event-type-badge-large {
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 0.5rem 1.25rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        
        .capacity-indicator {
            background: rgba(255, 255, 255, 0.15);
            border-radius: var(--radius-xl);
            padding: 1.25rem 1.5rem;
            text-align: center;
            min-width: 140px;
        }
        
        .capacity-number {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            color: var(--nu-gold);
        }
        
        .capacity-label {
            font-size: 0.8125rem;
            opacity: 0.8;
            margin-top: 0.25rem;
        }
        
        /* Registration Table */
        .registrations-table-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow);
            border: 1px solid var(--gray-200);
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1.5rem 2rem;
            background: linear-gradient(135deg, var(--gray-50) 0%, var(--white) 100%);
            border-bottom: 1px solid var(--gray-200);
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .table-title-section {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--nu-blue);
        }
        
        .table-count-badge {
            background: var(--nu-blue-soft);
            color: var(--nu-blue);
            padding: 0.375rem 0.875rem;
            border-radius: var(--radius-full);
            font-weight: 600;
            font-size: 0.875rem;
        }
        
        .table-actions {
            display: flex;
            gap: 0.75rem;
            flex-wrap: wrap;
        }
        
        .registrations-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .registrations-table thead th {
            background: var(--gray-50);
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--gray-600);
            border-bottom: 2px solid var(--gray-200);
            white-space: nowrap;
        }
        
        .registrations-table tbody tr {
            border-bottom: 1px solid var(--gray-100);
            transition: all var(--transition);
        }
        
        .registrations-table tbody tr:hover {
            background: var(--gray-50);
        }
        
        .registrations-table tbody td {
            padding: 1rem 1.5rem;
            vertical-align: middle;
        }
        
        .alumni-info-cell {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .alumni-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            object-fit: cover;
            flex-shrink: 0;
            box-shadow: var(--shadow-sm);
        }
        
        .alumni-avatar-placeholder {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--nu-blue-soft), var(--nu-blue-light));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--nu-blue);
            font-weight: 700;
            font-size: 1.125rem;
            flex-shrink: 0;
        }
        
        .alumni-name {
            font-weight: 600;
            color: var(--gray-800);
            margin-bottom: 0.125rem;
        }
        
        .alumni-id {
            font-size: 0.8125rem;
            color: var(--gray-500);
        }
        
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: var(--radius-full);
            font-size: 0.8125rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
        }
        
        .status-badge i {
            font-size: 0.5rem;
        }
        
        .status-confirmed {
            background: var(--success-light);
            color: var(--success);
        }
        
        .status-pending {
            background: var(--warning-light);
            color: var(--warning);
        }
        
        .status-cancelled {
            background: var(--danger-light);
            color: var(--danger);
        }
        
        /* Quick Actions */
        .quick-action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-md);
            border: 1px solid var(--gray-300);
            background: var(--white);
            color: var(--gray-600);
            cursor: pointer;
            transition: all var(--transition);
            font-size: 0.875rem;
        }
        
        .quick-action-btn:hover {
            background: var(--nu-blue);
            color: var(--white);
            border-color: var(--nu-blue);
            transform: translateY(-2px);
            box-shadow: var(--shadow-blue);
        }
        
        .quick-action-btn.view:hover {
            background: var(--info);
            border-color: var(--info);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }
        
        .quick-action-btn.message:hover {
            background: var(--success);
            border-color: var(--success);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }
        
        /* Search Bar */
        .search-bar-wrapper {
            position: relative;
        }
        
        .search-bar {
            padding: 0.625rem 1rem 0.625rem 2.75rem;
            border: 2px solid var(--gray-300);
            border-radius: var(--radius-lg);
            font-size: 0.875rem;
            font-family: 'Poppins', sans-serif;
            width: 280px;
            transition: all var(--transition);
            background: var(--white);
        }
        
        .search-bar:focus {
            outline: none;
            border-color: var(--nu-blue);
            box-shadow: 0 0 0 3px rgba(50, 65, 140, 0.1);
            width: 320px;
        }
        
        .search-bar-wrapper i {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--gray-400);
            font-size: 0.875rem;
        }
        
        /* Empty State */
        .empty-state-card {
            background: var(--white);
            border-radius: var(--radius-2xl);
            box-shadow: var(--shadow);
            border: 2px dashed var(--gray-300);
            padding: 4rem 2rem;
            text-align: center;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .event-info-banner {
                padding: 1.25rem 1.5rem;
                flex-direction: column;
                align-items: flex-start;
            }
            
            .event-info-meta {
                flex-direction: column;
                gap: 0.5rem;
            }
            
            .registrations-table thead {
                display: none;
            }
            
            .registrations-table tbody td {
                display: block;
                padding: 0.75rem 1rem;
                text-align: right;
            }
            
            .registrations-table tbody td::before {
                content: attr(data-label);
                float: left;
                font-weight: 600;
                color: var(--gray-600);
                text-transform: uppercase;
                font-size: 0.75rem;
            }
            
            .registrations-table tbody tr {
                display: block;
                padding: 1rem;
                border: 1px solid var(--gray-200);
                border-radius: var(--radius-lg);
                margin-bottom: 1rem;
                background: var(--white);
            }
            
            .search-bar {
                width: 100%;
            }
            
            .search-bar:focus {
                width: 100%;
            }
        }

        /* ============================================
        EVENT BANNER — RIGHT COLUMN STACK
        ============================================ */
        .event-banner-right {
            display: flex;
            flex-direction: column;
            gap: 0.875rem;
            min-width: 280px;
        }

        /* ============================================
        EVENT GROUP CHAT TOGGLE CARD
        ============================================ */
        .group-chat-toggle-card {
            background: rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.22);
            border-radius: var(--radius-xl);
            padding: 0.875rem 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .group-chat-toggle-card.active {
            background: rgba(16, 185, 129, 0.22);
            border-color: rgba(16, 185, 129, 0.55);
            box-shadow: 0 4px 20px rgba(16, 185, 129, 0.2);
        }

        .gct-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .gct-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--nu-gold);
            font-size: 0.95rem;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .group-chat-toggle-card.active .gct-icon {
            background: rgba(16, 185, 129, 0.4);
            color: #ffffff;
        }

        .gct-text {
            flex: 1;
            min-width: 0;
        }

        .gct-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #ffffff;
            line-height: 1.2;
        }

        .gct-subtitle {
            font-size: 0.7rem;
            color: rgba(255, 255, 255, 0.78);
            margin-top: 2px;
            line-height: 1.3;
        }

        /* --- Toggle Switch --- */
        .gct-switch {
            position: relative;
            display: inline-block;
            width: 46px;
            height: 26px;
            flex-shrink: 0;
            cursor: pointer;
            margin: 0;
        }

        .gct-switch input {
            opacity: 0;
            width: 0;
            height: 0;
            position: absolute;
        }

        .gct-slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: rgba(255, 255, 255, 0.32);
            transition: 0.3s;
            border-radius: 26px;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .gct-slider::before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 2px;
            bottom: 2px;
            background-color: #ffffff;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.22);
        }

        .gct-switch input:checked + .gct-slider {
            background-color: #10b981;
            border-color: #10b981;
        }

        .gct-switch input:checked + .gct-slider::before {
            transform: translateX(20px);
        }

        .gct-switch input:disabled + .gct-slider {
            opacity: 0.55;
            cursor: not-allowed;
        }

        .gct-switch input:focus-visible + .gct-slider {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.35);
        }

        /* --- Open Channel Button --- */
        .gct-actions {
            margin-top: 0.75rem;
            padding-top: 0.75rem;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            animation: gctFadeIn 0.3s ease;
        }

        @keyframes gctFadeIn {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .gct-open-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.5rem 0.75rem;
            background: rgba(255, 255, 255, 0.95);
            color: var(--nu-blue-dark);
            border-radius: var(--radius-lg);
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .gct-open-btn:hover {
            background: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.18);
        }

        .gct-open-btn i {
            font-size: 0.72rem;
        }

        /* Mobile */
        @media (max-width: 768px) {
            .event-banner-right {
                width: 100%;
                min-width: 0;
            }
            .group-chat-toggle-card {
                width: 100%;
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
                <a href="{{ route('events.index') }}" class="nav-item active">
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
                            <i class="fa-solid fa-users-gear"></i>
                            Manage Registrations
                        </h1>
                        <p class="page-subtitle">View and manage alumni registrations for this event</p>
                    </div>
                    
                    <div class="header-actions">
                        <a href="{{ route('events.index') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i> 
                            <span>Back to Events</span>
                        </a>
                        <a href="{{ route('events.edit', $event) }}" class="btn btn-secondary">
                            <i class="fa-solid fa-pen-to-square"></i> 
                            <span>Edit Event</span>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Event Info Banner -->
            <div class="event-info-banner">
                <div class="event-info-content">
                    <h2 class="event-info-title">
                        <i class="fa-solid fa-calendar-check"></i>
                        {{ $event->title }}
                    </h2>
                    <div class="event-info-meta">
                        <span>
                            <i class="fa-regular fa-calendar"></i>
                            {{ $event->start_date->format('M d, Y') }}
                            @if($event->end_date) – {{ $event->end_date->format('M d, Y') }}@endif
                        </span>
                        <span>
                            <i class="fa-solid fa-tag"></i>
                            {{ $event->event_type }}
                        </span>
                        @if($event->venue)
                        <span>
                            <i class="fa-solid fa-location-dot"></i>
                            {{ $event->venue->name }}
                        </span>
                        @endif
                        @if($event->platform)
                        <span>
                            <i class="fa-solid fa-globe"></i>
                            {{ $event->platform }}
                        </span>
                        @endif
                    </div>
                </div>
                <div class="event-banner-right">
                    {{-- Capacity indicator --}}
                    <div class="capacity-indicator">
                        <div class="capacity-number">{{ $totalRegistrations }}/{{ $event->max_capacity }}</div>
                        <div class="capacity-label">Registered</div>
                    </div>

                    {{-- Event Group Chat Toggle --}}
                    <div class="group-chat-toggle-card {{ $hasGroupChat ? 'active' : '' }}"
                        id="groupChatToggleCard">
                        <div class="gct-row">
                            <div class="gct-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="gct-text">
                                <div class="gct-title">Event Group Chat</div>
                                <div class="gct-subtitle" id="gctSubtitle">
                                    @if($hasGroupChat)
                                        Active — members synced
                                    @else
                                        Create a channel for all registrants
                                    @endif
                                </div>
                            </div>
                            <label class="gct-switch" aria-label="Toggle event group chat">
                                <input type="checkbox"
                                    id="groupChatToggle"
                                    {{ $hasGroupChat ? 'checked' : '' }}
                                    onchange="handleGroupChatToggle(this)">
                                <span class="gct-slider"></span>
                            </label>
                        </div>

                        <div class="gct-actions"
                            id="gctActions"
                            style="{{ $hasGroupChat ? '' : 'display:none;' }}">
                            <a href="/admin/messages?chat={{ $event->group_chat_id ?? '' }}"
                            class="gct-open-btn"
                            id="gctOpenBtn">
                                <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                Open Channel
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalRegistrations }}</span>
                        <span class="stat-label">Total Registrations</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $confirmedRegistrations }}</span>
                        <span class="stat-label">Confirmed</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-clock"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $pendingRegistrations }}</span>
                        <span class="stat-label">Pending</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-chart-pie"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $fillRate }}%</span>
                        <span class="stat-label">Fill Rate</span>
                    </div>
                </div>
            </div>

            <!-- Registrations Table -->
            @if($registrations->count() > 0)
                <div class="registrations-table-card">
                    <div class="table-header">
                        <div class="table-title-section">
                            <h3 class="table-title">
                                <i class="fa-solid fa-list-check"></i>
                                Registered Alumni
                            </h3>
                            <span class="table-count-badge">{{ $totalRegistrations }} total</span>
                        </div>
                        <div class="table-actions">
                            <div class="search-bar-wrapper">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                <input type="text" 
                                       class="search-bar" 
                                       id="registrationSearch" 
                                       placeholder="Search alumni name or email..."
                                       onkeyup="filterRegistrations()">
                            </div>
                            <button class="btn btn-secondary" onclick="exportRegistrations()">
                                <i class="fa-solid fa-download"></i>
                                <span>Export</span>
                            </button>
                        </div>
                    </div>
                    
                    <div style="overflow-x: auto;">
                        <table class="registrations-table" id="registrationsTable">
                            <thead>
                                <tr>
                                    <th>Alumni</th>
                                    <th>Email</th>
                                    <th>Program</th>
                                    <th>RSVP Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($registrations as $registration)
                                    <tr class="registration-row" 
                                        data-name="{{ strtolower(($registration->alumni->first_name ?? '') . ' ' . ($registration->alumni->last_name ?? '')) }}"
                                        data-email="{{ strtolower($registration->alumni->email ?? '') }}">
                                        <td data-label="Alumni">
                                            <div class="alumni-info-cell">
                                                @if($registration->alumni && $registration->alumni->alumni_photo_url)
                                                    <img src="{{ $registration->alumni->alumni_photo_url }}" 
                                                         alt="Photo" 
                                                         class="alumni-avatar"
                                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                                    <div class="alumni-avatar-placeholder" style="display: none;">
                                                        {{ $registration->alumni->initials }}
                                                    </div>
                                                @else
                                                    <div class="alumni-avatar-placeholder">
                                                        {{ $registration->alumni->initials ?? '?' }}
                                                    </div>
                                                @endif
                                                <div>
                                                    <div class="alumni-name">
                                                        {{ $registration->alumni->full_name ?? 'Unknown Alumni' }}
                                                    </div>
                                                    <div class="alumni-id">
                                                        {{ $registration->alumni->student_id_number ?? 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td data-label="Email">
                                            <span style="color: var(--gray-600);">{{ $registration->alumni->email ?? 'N/A' }}</span>
                                        </td>
                                        <td data-label="Program">
                                            <span style="color: var(--gray-600);">{{ $registration->alumni->program ?? 'N/A' }}</span>
                                        </td>
                                        <td data-label="RSVP Date">
                                            <span style="color: var(--gray-600); font-weight: 500;">
                                                {{ $registration->rsvp_date ? $registration->rsvp_date->format('M d, Y') : 'N/A' }}
                                            </span>
                                        </td>
                                        <td data-label="Status">
                                            <span class="status-badge {{ $registration->registration_confirmation ? 'status-confirmed' : 'status-pending' }}">
                                                <i class="fa-solid fa-circle"></i>
                                                {{ $registration->registration_confirmation ? 'Confirmed' : 'Pending' }}
                                            </span>
                                        </td>
                                        <td data-label="Actions">
                                            <div style="display: flex; gap: 0.5rem;">
                                                <a href="{{ route('admin.alumni.show', $registration->alumni_id) }}" 
                                                   class="quick-action-btn view" 
                                                   title="View Profile">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <button class="quick-action-btn message" 
                                                        title="Send Message"
                                                        onclick="sendMessageToAlumni({{ $registration->alumni_id }})">
                                                    <i class="fa-solid fa-envelope"></i>
                                                </button>
                                                
                                                @if($registration->registration_confirmation)
                                                    <button class="quick-action-btn"
                                                            title="Mark as Pending"
                                                            style="color: var(--warning);"
                                                            data-alumni-name="{{ trim(($registration->alumni->first_name ?? '') . ' ' . ($registration->alumni->last_name ?? '')) }}"
                                                            onclick="toggleConfirmation({{ $registration->id }}, false, this)">
                                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                                    </button>
                                                @else
                                                    <button class="quick-action-btn"
                                                            title="Confirm Registration"
                                                            style="color: var(--success);"
                                                            data-alumni-name="{{ trim(($registration->alumni->first_name ?? '') . ' ' . ($registration->alumni->last_name ?? '')) }}"
                                                            onclick="toggleConfirmation({{ $registration->id }}, true, this)">
                                                        <i class="fa-solid fa-check"></i>
                                                    </button>
                                                @endif

                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if ($registrations->hasPages())
                        <div class="pagination-wrapper" style="padding: 1.5rem;">
                            {{ $registrations->links() }}
                        </div>
                    @endif
                </div>
            @else
                <div class="empty-state-card">
                    <div class="empty-icon-wrapper">
                        <div class="empty-icon">
                            <i class="fa-solid fa-users-slash"></i>
                        </div>
                    </div>
                    <h3 class="empty-title">No Registrations Yet</h3>
                    <p class="empty-description">
                        No alumni have registered for this event yet. Share the event details with your alumni community to start receiving registrations.
                    </p>
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
        // Warning Modal System
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
        warningOverlay.addEventListener('click', function(e) { 
            if (e.target === warningOverlay) closeWarningModal(); 
        });
        document.addEventListener('keydown', function(e) { 
            if (e.key === 'Escape' && warningOverlay.classList.contains('active')) closeWarningModal(); 
        });
        confirmBtn.addEventListener('click', function() { 
            if (pendingCallback) pendingCallback(); 
            closeWarningModal(); 
        });

        function showWarningModal(config) {
            const {
                title = 'Confirm Action',
                message = 'Are you sure?',
                iconType = 'warning',
                confirmText = 'Confirm',
                confirmClass = 'btn-danger',
                onConfirm = null
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

            pendingCallback = onConfirm;
            warningOverlay.classList.add('active');
            document.body.style.overflow = 'hidden';
            confirmBtn.focus();
        }

        // ========================================
        // Registration Search/Filter
        // ========================================
        function filterRegistrations() {
            const searchTerm = document.getElementById('registrationSearch').value.toLowerCase();
            const rows = document.querySelectorAll('.registration-row');
            
            rows.forEach(row => {
                const name = row.getAttribute('data-name') || '';
                const email = row.getAttribute('data-email') || '';
                
                if (name.includes(searchTerm) || email.includes(searchTerm)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        // ========================================
        // Toggle Registration Confirmation
        // ========================================
        function toggleConfirmation(registrationId, confirm, triggerBtn) {
            const action = confirm ? 'confirm' : 'mark as pending';
            const iconType = confirm ? 'success' : 'warning';
            const confirmClass = confirm ? 'btn-success' : 'btn-warning';

            // Read the name directly from the clicked button
            const alumniName = triggerBtn?.dataset?.alumniName || 'this alumnus';
            // Removed escapeHtml because Blade already handled it, and dataset reads it back safely
            const safeName = alumniName;

            // Build message — different copy for confirm vs unconfirm
            const message = confirm
                ? `Are you sure you want to <strong>confirm</strong> the registration for <strong>${safeName}</strong>?<br>
                <small>
                    <i class="fa-solid fa-envelope" style="color: var(--nu-blue);"></i>
                    A confirmation email with the event details will be sent to their registered email address.
                </small>`
                : `Are you sure you want to <strong>mark this registration as pending</strong> for <strong>${safeName}</strong>?<br>
                <small>
                    <i class="fa-solid fa-circle-info" style="color: var(--warning);"></i>
                    No email will be sent. You can confirm again at any time.
                </small>`;

            showWarningModal({
                title: confirm ? 'Confirm Registration' : 'Mark as Pending',
                message,
                iconType,
                confirmText: confirm ? 'Confirm & Send Email' : 'Mark as Pending',
                confirmClass,
                onConfirm: function() {
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/events/registrations/${registrationId}/toggle`;
                    form.style.display = 'none';

                    const csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
                    form.appendChild(csrf);

                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'PATCH';
                    form.appendChild(methodInput);

                    const confirmInput = document.createElement('input');
                    confirmInput.type = 'hidden';
                    confirmInput.name = 'registration_confirmation';
                    confirmInput.value = confirm ? '1' : '0';
                    form.appendChild(confirmInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        // ========================================
        // Send Message to Alumni
        // ========================================
        function sendMessageToAlumni(alumniId) {
            window.location.href = `/admin/messages?chat=${alumniId}`;
        }
        // ========================================
        // Export Registrations (CSV)
        // ========================================
        function exportRegistrations() {
            const table = document.getElementById('registrationsTable');
            const rows = table.querySelectorAll('tbody tr');
            let csv = 'Alumni Name,Email,Program,RSVP Date,Status\n';
            
            rows.forEach(row => {
                if (row.style.display === 'none') return; // Skip filtered rows
                
                const cells = row.querySelectorAll('td');
                const name = cells[0].querySelector('.alumni-name')?.textContent.trim() || '';
                const email = cells[1].textContent.trim();
                const program = cells[2].textContent.trim();
                const rsvpDate = cells[3].textContent.trim();
                const status = cells[4].querySelector('.status-badge')?.textContent.trim() || '';
                
                csv += `"${name}","${email}","${program}","${rsvpDate}","${status}"\n`;
            });
            
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const link = document.createElement('a');
            const url = URL.createObjectURL(blob);
            link.setAttribute('href', url);
            link.setAttribute('download', `registrations_{{ Str::slug($event->title) }}.csv`);
            link.style.visibility = 'hidden';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        // ========================================
        // EVENT GROUP CHAT TOGGLE
        // ========================================
        (function () {
            const TOGGLE_URL = "{{ route('events.group-chat.toggle', $event) }}";
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]')?.content
                                || '{{ csrf_token() }}';

            let busy = false;

            window.handleGroupChatToggle = async function (checkboxEl) {
                if (busy) {
                    checkboxEl.checked = !checkboxEl.checked;
                    return;
                }

                const enabled  = checkboxEl.checked;
                const card     = document.getElementById('groupChatToggleCard');
                const subtitle = document.getElementById('gctSubtitle');
                const actions  = document.getElementById('gctActions');
                const openBtn  = document.getElementById('gctOpenBtn');

                const originalSubtitle = subtitle.textContent;

                busy = true;
                checkboxEl.disabled = true;
                subtitle.textContent = enabled ? 'Creating channel…' : 'Disabling…';

                try {
                    const res = await fetch(TOGGLE_URL, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF_TOKEN,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ enabled: enabled ? 1 : 0 }),
                    });

                    const data = await res.json().catch(() => ({}));

                    if (!res.ok || !data.success) {
                        throw new Error(data.error || `Request failed (${res.status})`);
                    }

                    if (data.enabled) {
                        card.classList.add('active');
                        subtitle.textContent = 'Active — members synced';
                        actions.style.display = '';
                        if (data.group_id && openBtn) {
                            openBtn.href = `/admin/messages?chat=${data.group_id}`;
                        }
                        showEventToast('✅ ' + (data.message || 'Group chat enabled.'), 'success');
                    } else {
                        card.classList.remove('active');
                        subtitle.textContent = 'Create a channel for all registrants';
                        actions.style.display = 'none';
                        showEventToast('Group chat disabled.', 'info');
                    }
                } catch (err) {
                    console.error('[Event Group Chat Toggle]', err);
                    checkboxEl.checked = !enabled;
                    subtitle.textContent = originalSubtitle;
                    card.classList.toggle('active', !enabled);
                    showEventToast('❌ ' + (err.message || 'Failed to toggle group chat.'), 'error');
                } finally {
                    checkboxEl.disabled = false;
                    busy = false;
                }
            };

            /* ---------- Minimal toast ---------- */
            window.showEventToast = function (message, type = 'success') {
                document.querySelectorAll('.event-custom-toast').forEach(t => t.remove());

                const colors = {
                    success: '#10b981',
                    error:   '#ef4444',
                    info:    '#3b82f6',
                    warning: '#f59e0b',
                };

                const toast = document.createElement('div');
                toast.className = 'event-custom-toast';
                toast.innerHTML = `
                    <span>${message}</span>
                    <button type="button" aria-label="Dismiss">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                `;

                Object.assign(toast.style, {
                    position: 'fixed',
                    bottom: '24px',
                    right: '24px',
                    background: colors[type] || colors.success,
                    color: '#fff',
                    padding: '12px 18px',
                    borderRadius: '10px',
                    boxShadow: '0 8px 24px rgba(0,0,0,0.18)',
                    display: 'flex',
                    alignItems: 'center',
                    gap: '12px',
                    zIndex: 9999,
                    fontFamily: 'Poppins, sans-serif',
                    fontSize: '0.9rem',
                    maxWidth: '380px',
                    animation: 'gctFadeIn 0.25s ease',
                });

                const closeBtn = toast.querySelector('button');
                closeBtn.style.cssText =
                    'background:none;border:none;color:#fff;cursor:pointer;font-size:1rem;opacity:.85;';
                closeBtn.onclick = () => toast.remove();

                document.body.appendChild(toast);
                setTimeout(() => { if (toast.parentElement) toast.remove(); }, 4000);
            };
        })();
    </script>

</body>
</html>