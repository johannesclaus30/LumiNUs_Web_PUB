<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Directory | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- SheetJS for Excel/CSV parsing -->
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <style>
        .alumni-type-toggle {
            display: inline-flex;
            background: var(--gray-100);
            border: 2px solid var(--gray-200);
            border-radius: 9999px;
            padding: 4px;
            gap: 4px;
        }
        .alumni-type-toggle .type-option {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border: none;
            background: transparent;
            border-radius: 9999px;
            font-family: inherit;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--gray-600);
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .alumni-type-toggle .type-option:hover {
            color: var(--nu-blue);
        }
        .alumni-type-toggle .type-option.active {
            background: var(--nu-blue);
            color: white;
            box-shadow: 0 2px 8px rgba(50, 65, 140, 0.25);
        }
        .alumni-type-toggle .type-option.active i {
            color: white;
        }

        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border: 0;
        }

        /* Stack multiple status badges side by side with a gap */
        .alumni-status {
            display: flex;
            flex-wrap: wrap;
            gap: 0.375rem;
            align-items: center;
        }
        .alumni-type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            padding: 0.25rem 0.625rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 600;
            white-space: nowrap;
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
                <button class="sidebar-close" id="sidebarClose" onclick="toggleMobileMenu()" aria-label="Close sidebar menu">
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
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()" aria-label="Open navigation menu">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-users"></i>
                            Alumni Directory
                        </h1>
                        <p class="page-subtitle">Manage and connect with NU Lipa alumni members</p>
                    </div>
                    
                    <div class="header-actions">
                        <button class="btn btn-primary" onclick="showModal()">
                            <i class="fa-solid fa-user-plus"></i>
                            <span>Create Account</span>
                        </button>

                        {{-- =====================
                            EXPORT DROPDOWN
                            ===================== --}}
                        <div class="export-dropdown" id="exportDropdown">
                            <button class="btn btn-secondary export-trigger" onclick="toggleExportMenu(event)">
                                <i class="fa-solid fa-download"></i>
                                <span>Export</span>
                                <i class="fa-solid fa-chevron-down export-caret"></i>
                            </button>

                            <div class="export-menu" id="exportMenu" role="menu">
                                <div class="export-menu-header">
                                    <i class="fa-solid fa-file-export"></i>
                                    Export Directory
                                    <span class="export-count-badge">{{ $totalAlumni ?? 0 }} records</span>
                                </div>

                                <a href="{{ route('admin.alumni.export', ['format' => 'csv']) }}"
                                class="export-item" onclick="closeExportMenu()">
                                    <div class="export-item-icon csv">
                                        <i class="fa-solid fa-file-csv"></i>
                                    </div>
                                    <div class="export-item-text">
                                        <strong>Export as CSV</strong>
                                        <small>Spreadsheet format — opens in Excel</small>
                                    </div>
                                    <i class="fa-solid fa-arrow-right export-item-arrow"></i>
                                </a>

                                <a href="{{ route('admin.alumni.export', ['format' => 'pdf']) }}"
                                class="export-item premium" target="_blank" onclick="closeExportMenu()">
                                    <div class="export-item-icon pdf">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </div>
                                    <div class="export-item-text">
                                        <strong>Export as PDF
                                            <span class="premium-tag">
                                                <i class="fa-solid fa-star"></i> BRANDED
                                            </span>
                                        </strong>
                                        <small>NU-branded report with cover &amp; summary</small>
                                    </div>
                                    <i class="fa-solid fa-arrow-right export-item-arrow"></i>
                                </a>
                            </div>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Account Management Quick Access -->
            <div class="account-quick-links">
                <div>
                    <span class="quick-links-label">
                        <i class="fa-solid fa-shield-halved"></i>
                        Account Management
                    </span>
                    
                    <a href="/admin/directory/archived" class="btn-sm-link archived">
                        <i class="fa-solid fa-box-archive"></i> 
                        Archived
                        <span class="badge-count has-items">{{ \App\Models\Alumni::onlyTrashed()->count() }}</span>
                    </a>
                    
                    <a href="/admin/directory/restricted" class="btn-sm-link restricted">
                        <i class="fa-solid fa-user-slash"></i> 
                        Restricted
                        <span class="badge-count has-items">{{ \App\Models\Alumni::whereNull('deleted_at')->where('account_status', 0)->count() }}</span>
                    </a>
                </div>
            </div>
            <!-- Stats Overview -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-users"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $totalAlumni ?? 0 }}</span>
                        <span class="stat-label">Total Alumni</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon active">
                            <i class="fa-solid fa-graduation-cap"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $recentGraduates ?? 0 }}</span>
                        <span class="stat-label">Recent Graduates</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon">
                            <i class="fa-solid fa-building-columns"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $uniquePrograms ?? 0 }}</span>
                        <span class="stat-label">Programs</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon-wrapper">
                        <div class="stat-icon views">
                            <i class="fa-regular fa-envelope"></i>
                        </div>
                    </div>
                    <div class="stat-info">
                        <span class="stat-value">{{ $withEmails ?? 0 }}</span>
                        <span class="stat-label">With Email</span>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Toolbar - Unified -->
            <div class="directory-toolbar">
                <div class="search-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <label for="searchInput" class="sr-only">Search alumni by name, email, or program</label>
                    <input id="searchInput" type="text" 
                        placeholder="Search by name, email, program..." 
                        class="search-bar" 
                        oninput="performFilter()">
                    <button id="clearSearch" 
                            type="button"
                            class="clear-search" 
                            onclick="clearSearch()" 
                            aria-label="Clear search"
                            title="Clear search">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>
                
                <div class="toolbar-actions">
                    <!-- Quick Type Filters -->
                    <div class="quick-filters">
                        <button class="filter-pill active" data-filter="all" onclick="applyQuickFilter('all')" title="Show all alumni">
                            <span>All</span>
                            <span class="filter-count" id="countAll">0</span>
                        </button>
                        <button class="filter-pill" data-filter="college" onclick="applyQuickFilter('college')" title="Show college alumni">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>College</span>
                            <span class="filter-count" id="countCollege">0</span>
                        </button>
                        <button class="filter-pill" data-filter="shs" onclick="applyQuickFilter('shs')" title="Show SHS alumni">
                            <i class="fa-solid fa-school"></i>
                            <span>SHS</span>
                            <span class="filter-count" id="countShs">0</span>
                        </button>
                    </div>

                    <!-- View Toggle -->
                    <div class="view-toggle" id="viewToggle">
                        <button type="button" class="view-btn active" onclick="setViewMode('grid')" title="Grid View" aria-label="Switch to Grid View">
                            <i class="fa-solid fa-border-all"></i>
                        </button>
                        <button type="button" class="view-btn" onclick="setViewMode('list')" title="List View" aria-label="Switch to List View">
                            <i class="fa-solid fa-list"></i>
                        </button>
                    </div>
                    
                    <!-- Advanced Filter Toggle -->
                    <button class="btn-filter-toggle" onclick="toggleAdvancedFilters()" title="Advanced filters">
                        <i class="fa-solid fa-sliders"></i>
                        <span>Filters</span>
                        <span class="filter-badge" id="activeFilterBadge" style="display:none;">0</span>
                    </button>
                </div>
            </div>

            <!-- Advanced Filters Panel - Integrated -->
            <div class="advanced-filters" id="advancedFilters" style="display:none;">
                <div class="filter-grid">
                    <!-- Program Filter -->
                    <div class="filter-group">
                        <label for="filterProgram">
                            <i class="fa-solid fa-building-columns"></i> Program
                        </label>
                        <select id="filterProgram" onchange="applyAdvancedFilters()">
                            <option value="">All Programs</option>
                        </select>
                    </div>
                    
                    <!-- Year Graduated Filter -->
                    <div class="filter-group">
                        <label for="filterYear">
                            <i class="fa-regular fa-calendar"></i> Year Graduated
                        </label>
                        <select id="filterYear" onchange="applyAdvancedFilters()">
                            <option value="">All Years</option>
                        </select>
                    </div>
                    
                    <!-- Sort By -->
                    <div class="filter-group">
                        <label for="filterSort">
                            <i class="fa-solid fa-arrow-up-wide-short"></i> Sort By
                        </label>
                        <select id="filterSort" onchange="applyAdvancedFilters()">
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                            <option value="year_desc">Year Graduated (Newest)</option>
                            <option value="year_asc">Year Graduated (Oldest)</option>
                        </select>
                    </div>
                    
                    <!-- Filter Actions -->
                    <div class="filter-group filter-actions-group">
                        <label>&nbsp;</label>
                        <div class="filter-actions">
                            <button class="btn btn-secondary btn-sm" onclick="resetFilters()">
                                <i class="fa-solid fa-undo"></i> Reset
                            </button>
                            <button class="btn btn-primary btn-sm" onclick="applyAdvancedFilters(); toggleAdvancedFilters();">
                                <i class="fa-solid fa-check"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <h2 class="sr-only">Alumni Directory Results</h2>
            <!-- Alumni Card Grid -->
            <div class="alumni-grid" id="alumniGrid">
                @forelse ($alumni as $alumnus)
                    @php
                        $firstName = $alumnus->first_name ?? '';
                        $middleName = $alumnus->middle_name ?? '';
                        $lastName = $alumnus->last_name ?? '';
                        $email = $alumnus->email ?? '';
                        $program = $alumnus->program ?? '';
                        $graduationYear = optional($alumnus->year_graduated)->format('Y') ?: 'N/A';
                        $middleInitial = $middleName !== '' ? strtoupper(mb_substr(trim($middleName), 0, 1)) . '.' : '';
                        
                        // Photo URL logic
                        $photoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
                        if ($photoPath === '') {
                            $photoUrl = '/assets/FINAL-NULIPA.jpg';
                        } elseif (preg_match('/^https?:\/\//i', $photoPath)) {
                            $photoUrl = $photoPath;
                        } elseif (str_starts_with($photoPath, '/storage/')) {
                            $photoUrl = $photoPath;
                        } elseif (str_starts_with($photoPath, 'storage/')) {
                            $photoUrl = '/' . $photoPath;
                        } elseif (str_starts_with($photoPath, '/')) {
                            $photoUrl = $photoPath;
                        } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                            $photoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/');
                        } else {
                            $photoUrl = asset('storage/' . ltrim($photoPath, '/'));
                        }

                        $displayName = trim($firstName . ' ' . ($middleInitial ? $middleInitial . ' ' : '') . $lastName);
                        $initials = strtoupper(mb_substr($firstName, 0, 1) . mb_substr($lastName, 0, 1));
                    @endphp
                    @php
                        $collegeProgramForCard = $alumnus->programs->firstWhere('program_type', 'college');
                        $collegeProgramJson = $collegeProgramForCard ? json_encode([
                            'id' => $collegeProgramForCard->id,
                            'program_name' => $collegeProgramForCard->program_name,
                            'student_id_number' => $collegeProgramForCard->student_id_number,
                            'enrollment_status' => $collegeProgramForCard->enrollment_status,
                        ]) : '';
                    @endphp

                    <article class="alumni-card" 
                            data-id="{{ $alumnus->id }}"
                            data-name="{{ strtolower($displayName) }}" 
                            data-email="{{ strtolower($email) }}"
                            data-program="{{ strtolower($program) }}"
                            data-college-program="{{ $collegeProgramJson }}">

                        <div class="alumni-card-wrapper">
                            <!-- Header: Photo, Name & Edit Action -->
                            <div class="alumni-card-header">
                                
                                <!-- Photo -->
                                <div class="alumni-photo-wrapper">
                                    <img src="{{ $photoUrl }}" 
                                        alt="{{ $displayName ?: 'Alumni photo' }}" 
                                        class="alumni-photo"
                                        onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                                    @if(empty($photoPath))
                                        <span class="photo-initials">{{ $initials }}</span>
                                    @endif
                                </div>

                                <!-- Name (Wrapped so it takes up space properly) -->
                                <div class="alumni-name-wrapper">
                                    <h3 class="alumni-name">{{ $displayName ?: 'Unnamed Alumni' }}</h3>
                                </div>

                                <!-- Edit Action -->
                                <div class="alumni-quick-actions">
                                    <a href="{{ route('admin.alumni.edit', $alumnus->id) }}" 
                                    class="quick-action-btn" 
                                    aria-label="View and edit {{ $displayName }}'s profile"
                                    title="View &amp; Edit Profile">
                                        <i class="fa-solid fa-pen-to-square" aria-hidden="true"></i>
                                    </a>
                                </div>
                                
                            </div>
                            <!-- Body: Identity & Graduation Year -->
                            <div class="alumni-card-body">
                                <div class="alumni-identity">
                                    
                                    
                                    <div class="alumni-academic-info">
                                        <span class="alumni-program" title="{{ $program ?: 'Program not specified' }}">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                            {{ $program ?: 'Program not specified' }}
                                        </span>
                                        
                                        <span class="alumni-grad-year" title="Year Graduated">
                                            <i class="fa-regular fa-calendar-check"></i>
                                            Class of {{ $graduationYear }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer: Status & Actions -->
                            <div class="alumni-card-footer">
                                <div class="alumni-status">
                                    @php
                                        // Determine display state from alumni_type + program rows
                                        $collegeProgram = $alumnus->programs->firstWhere('program_type', 'college');
                                        $hasCollegeEnrolled = $collegeProgram && $collegeProgram->enrollment_status === 'enrolled';
                                        $hasCollegeDropped  = $collegeProgram && $collegeProgram->enrollment_status === 'dropped';
                                        $hasCollegeGrad     = $collegeProgram && $collegeProgram->enrollment_status === 'graduated';
                                        $hasShsRow          = $alumnus->programs->contains('program_type', 'shs');
                                    @endphp

                                    @if($alumnus->alumni_type == 'shs')
                                        <span class="alumni-type-badge shs">
                                            <i class="fa-solid fa-school"></i>
                                            SHS
                                        </span>

                                        @if($hasCollegeEnrolled)
                                            <span class="alumni-type-badge college-enrolled" title="Currently studying College at NU Lipa" style="background:#ede9fe;color:#7c3aed;border:1px solid #c4b5fd;">
                                                <i class="fa-solid fa-book-open"></i>
                                                College Enrolled
                                            </span>
                                        @elseif($hasCollegeDropped)
                                            <span class="alumni-type-badge college-dropped" title="Left NU Lipa College before graduating" style="background:#f3f4f6;color:#6b7280;border:1px solid #d1d5db;">
                                                <i class="fa-solid fa-user-xmark"></i>
                                                Left NU Lipa
                                            </span>
                                        @endif

                                    @elseif($alumnus->alumni_type == 'college')
                                        <span class="alumni-type-badge college">
                                            <i class="fa-solid fa-graduation-cap"></i>
                                            College
                                        </span>

                                        @if($hasShsRow)
                                            <span class="alumni-type-badge shs-secondary" title="Former NU Lipa SHS alumnus" style="background:#dbeafe;color:#1d4ed8;border:1px solid #93c5fd;">
                                                <i class="fa-solid fa-school"></i>
                                                +SHS
                                            </span>
                                        @endif

                                    @else
                                        <span class="alumni-type-badge unknown">
                                            <i class="fa-solid fa-question-circle"></i>
                                            Unknown
                                        </span>
                                    @endif
                                </div>

                                <div class="alumni-actions">
                                    <!-- Message Button -->
                                    <a href="/admin/messages?chat={{ $alumnus->id }}" 
                                    class="btn-action btn-message" aria-label="Message {{ $displayName }}"
                                    title="Message {{ $displayName }}">
                                        <i class="fa-solid fa-comment-dots" aria-hidden="true"></i>
                                    </a>
                                    
                                    <!-- View Profile Button -->
                                    <a href="{{ route('admin.alumni.show', $alumnus->id) }}" 
                                    class="btn-action btn-edit" 
                                    title="View Profile" aria-label="View {{ $displayName }}'s profile">
                                        <i class="fa-solid fa-eye" aria-hidden="true"></i>
                                    </a>
                                    
                                    <!-- Manage Account Button -->
                                    <button type="button" 
                                            class="btn-action btn-info-action manage-btn" aria-label="Manage {{ $displayName }}'s account"
                                            data-id="{{ $alumnus->id }}" 
                                            data-name="{{ $displayName }}"
                                            data-status="{{ $alumnus->account_status ?? 1 }}" 
                                            data-alumni-type="{{ $alumnus->alumni_type ?? '' }}"
                                            title="Manage Account">
                                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </article>

                @empty
                    <div class="empty-state full-width">
                        <div class="empty-icon-wrapper">
                            <div class="empty-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                        </div>
                        <h3 class="empty-title">No alumni records found</h3>
                        <p class="empty-description">
                            Start building your alumni network by creating the first alumni account.
                        </p>
                        <button onclick="showModal()" class="btn btn-primary btn-lg">
                            <i class="fa-solid fa-user-plus"></i> 
                            <span>Create First Alumni</span>
                        </button>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if (isset($alumni) && method_exists($alumni, 'links'))
            <div class="pagination-wrapper">
                {{ $alumni->links() }}
            </div>
            @endif
        </main>
    </div>

    <!-- Create Alumni Modal -->
    <div id="createModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-user-plus"></i>
                            Create Alumni Account
                        </h2>
                        <p class="modal-subtitle">Add an alumni record. A random temporary password will be emailed to the alumni.</p>
                    </div>
                    <button type="button" 
                            class="modal-close" 
                            onclick="hideModal()" 
                            aria-label="Close create account dialog"
                            title="Close">
                        <i class="fa-solid fa-xmark" aria-hidden="true"></i>
                    </button>
                </div>

                <div class="modal-tabbar" role="tablist" aria-label="Alumni creation options">
                    <button type="button" class="modal-tab active" aria-label="Create individual accounts" data-modal-tab="single" onclick="switchModalMode('single')">
                        <i class="fa-solid fa-user" aria-hidden="true"></i>
                        <span>Individual</span>
                    </button>
                    <button type="button" class="modal-tab" aria-label="Create accounts via Bulk Import" data-modal-tab="bulk" onclick="switchModalMode('bulk')">
                        <i class="fa-solid fa-file-csv" aria-hidden="true"></i>
                        <span>Bulk Import</span>
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Single Creation Form -->
                    <section class="modal-section modal-section-active" data-modal-section="single">
                        <form id="singleCreateForm" method="POST" action="{{ route('admin.alumni.store') }}" enctype="multipart/form-data">
                            @csrf

                            {{-- ADD THIS BLOCK TO SHOW VALIDATION ERRORS --}}
                            @if ($errors->any())
                                <div style="background-color: #fee2e2; color: #991b1b; padding: 12px; border-radius: 6px; margin-bottom: 15px; border: 1px solid #fca5a5;">
                                    <strong> Please fix the following errors:</strong>
                                    <ul style="margin: 5px 0 0 20px; padding: 0;">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            {{-- END ERROR BLOCK --}}

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="first_name">First Name *</label>
                                    <input id="first_name" name="first_name" value="{{ old('first_name') }}" required placeholder="Jane">
                                </div>
                                <div class="form-group">
                                    <label for="middle_name">Middle Name</label>
                                    <input id="middle_name" name="middle_name" value="{{ old('middle_name') }}" placeholder="Dela">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="last_name">Last Name *</label>
                                    <input id="last_name" name="last_name" value="{{ old('last_name') }}" required placeholder="Cruz">
                                </div>
                                <div class="form-group">
                                    <label for="student_id_number">Student ID *</label>
                                    <input id="student_id_number" name="student_id_number" value="{{ old('student_id_number') }}" required placeholder="2020-00001">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="date_of_birth">Date of Birth</label>
                                    <input id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}" type="date">
                                </div>
                                <div class="form-group">
                                    <label for="year_graduated">Year Graduated *</label>
                                    <input id="year_graduated" name="year_graduated" value="{{ old('year_graduated') }}" type="date">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="sex">Sex</label>
                                    <select id="sex" name="sex">
                                        <option value="">Select sex</option>
                                        <option value="Male" @selected(old('sex') === 'Male')>Male</option>
                                        <option value="Female" @selected(old('sex') === 'Female')>Female</option>
                                        <option value="Prefer not to say" @selected(old('sex') === 'Prefer not to say')>Prefer not to say</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="program">Program *</label>
                                    <input id="program" name="program" value="{{ old('program') }}" required placeholder="BS Computer Science">
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input id="email" name="email" value="{{ old('email') }}" type="email" required placeholder="email@example.com">
                                </div>
                                <div class="form-group">
                                    <label for="phone_number">Phone Number *</label>
                                    <input id="phone_number" name="phone_number" value="{{ old('phone_number') }}" required placeholder="09xx xxx xxxx">
                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label>Alumni Type <span class="text-danger">*</span></label>
                                
                                <div class="alumni-type-toggle" id="single-alumni-type-toggle">
                                    <button type="button"
                                            class="type-option active"
                                            data-type="college"
                                            onclick="selectAlumniType('single', 'college')">
                                        <i class="fa-solid fa-graduation-cap"></i>
                                        <span>College</span>
                                    </button>
                                    <button type="button"
                                            class="type-option"
                                            data-type="shs"
                                            onclick="selectAlumniType('single', 'shs')">
                                        <i class="fa-solid fa-school"></i>
                                        <span>Senior High</span>
                                    </button>
                                </div>

                                <input type="hidden" name="alumni_type" id="single-alumni-type-input" value="{{ old('alumni_type', 'college') }}">
                            </div>

                            <div class="form-note">
                                <i class="fa-solid fa-circle-info"></i>
                                <strong>Note:</strong> A random temporary password will be generated and sent to the alumni's email address.
                            </div>

                            <div class="modal-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa-solid fa-user-plus"></i>
                                    <span>Create Account</span>
                                </button>
                                <button type="button" class="btn btn-secondary" onclick="hideModal()">Cancel</button>
                            </div>
                        </form>
                    </section>

                    <!-- Bulk Import Section - Enhanced with Preview -->
                    <section class="modal-section" data-modal-section="bulk">
                        <div class="bulk-import-panel">
                        {{-- ============================================ --}}
                        {{-- ALUMNI TYPE TOGGLE (persists across steps)  --}}
                        {{-- ============================================ --}}
                        <div class="form-group full-width" style="margin-bottom: 1.5rem; padding: 1rem; background: var(--gray-50); border: 2px solid var(--gray-200); border-radius: var(--radius-lg);">
                            <label style="font-weight: 600; color: var(--nu-blue); display: flex; align-items: center; gap: 0.5rem;">
                                <i class="fa-solid fa-users-rectangle"></i>
                                Alumni Type for This Import <span class="text-danger">*</span>
                            </label>
                            <p style="font-size: 0.8125rem; color: var(--gray-500); margin: 0.25rem 0 0.75rem 0;">
                                Applies to <strong>every row</strong> in this batch. Choose <strong>College</strong> or <strong>Senior High</strong>.
                            </p>
                            <div class="alumni-type-toggle" id="bulk-alumni-type-toggle">
                                <button type="button" class="type-option active" data-type="college" onclick="selectAlumniType('bulk', 'college')">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    <span>College</span>
                                </button>
                                <button type="button" class="type-option" data-type="shs" onclick="selectAlumniType('bulk', 'shs')">
                                    <i class="fa-solid fa-school"></i>
                                    <span>Senior High</span>
                                </button>
                            </div>
                            <input type="hidden" id="bulk-alumni-type-input" value="college">
                        </div>

                            <!-- Step 1: Upload -->
                            <div id="uploadStep" class="bulk-step">
                                <div class="bulk-import-header">
                                    <div>
                                        <h3><i class="fa-solid fa-file-import"></i> Bulk Import</h3>
                                        <p>Upload a CSV or Excel file to create multiple alumni accounts at once.</p>
                                    </div>
                                    <span class="bulk-badge">CSV & Excel</span>
                                </div>

                                <div class="bulk-import-body">

                                    <div style="margin-bottom: 1rem; padding: 1rem; background: #fff7ed; border: 2px solid #f59e0b; border-radius: var(--radius-lg);">
                                        <label style="display: flex; align-items: center; gap: 0.75rem; cursor: pointer; font-weight: 600; color: #92400e;">
                                            <input type="checkbox" id="photosOnlyMode" onchange="togglePhotosOnlyMode()" style="width: 18px; height: 18px; cursor: pointer;">
                                            <span>
                                                <i class="fa-solid fa-image"></i>
                                                Photos-Only Mode
                                            </span>
                                        </label>
                                        <p style="font-size: 0.8125rem; color: #78350f; margin: 0.5rem 0 0 2rem;">
                                            Check this if you only want to <strong>update card photos</strong> for existing alumni. No new accounts will be created, and no CSV data will be imported.
                                        </p>
                                    </div>

                                    <!-- File Drop Zone -->
                                    <div class="file-drop-zone" id="fileDropZone">
                                        <i class="fa-solid fa-cloud-arrow-up"></i>
                                        <p>Drag & drop your file here</p>
                                        <span class="file-types">.csv, .xlsx, .xls</span>
                                        <input id="bulkImportFile" type="file" accept=".csv,.xlsx,.xls" class="file-input" />
                                    </div>

                                    {{-- ============================================ --}}
                                    {{-- CARD PHOTO UPLOAD --}}
                                    {{-- ============================================ --}}
                                    <div class="form-group full-width" style="margin-top: 1.5rem; padding: 1rem; background: #eff6ff; border: 2px solid #93c5fd; border-radius: var(--radius-lg);">
                                        <label style="font-weight: 600; color: var(--nu-blue); display: flex; align-items: center; gap: 0.5rem;">
                                            <i class="fa-solid fa-id-card"></i>
                                            Card Photos <span style="color: var(--gray-500); font-weight: 400;">(optional but recommended)</span>
                                        </label>

                                        {{-- STRICT NAMING NOTICE --}}
                                        <div style="background: #fff7ed; border-left: 4px solid #f59e0b; padding: 0.75rem 1rem; border-radius: var(--radius); margin: 0.75rem 0;">
                                            <p style="margin: 0 0 0.5rem 0; font-weight: 600; color: #92400e; font-size: 0.875rem;">
                                                <i class="fa-solid fa-triangle-exclamation"></i>
                                                STRICT FILENAME FORMAT REQUIRED
                                            </p>
                                            <p style="margin: 0 0 0.5rem 0; font-size: 0.8125rem; color: #78350f;">
                                                Every photo <strong>must</strong> be named exactly:
                                            </p>
                                            <div style="background: white; padding: 0.5rem 0.75rem; border-radius: 4px; font-family: monospace; font-size: 0.875rem; color: #1f2937; margin-bottom: 0.5rem;">
                                                <strong>SURNAME</strong>_<strong>FIRST NAME</strong>_<strong>MIDDLE INITIAL</strong>.jpg
                                            </div>
                                            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.8125rem; color: #78350f; line-height: 1.6;">
                                                <li>✅ <code style="background: #fde68a; padding: 0.1rem 0.3rem; border-radius: 3px;">CLAUS_JOHANNES EMMANUEL_A.jpg</code></li>
                                                <li>✅ <code style="background: #fde68a; padding: 0.1rem 0.3rem; border-radius: 3px;">DELA CRUZ_JANE_B.png</code></li>
                                                <li>❌ <code style="background: #fee2e2; padding: 0.1rem 0.3rem; border-radius: 3px;">johannes_photo.jpg</code> (wrong order)</li>
                                                <li>❌ <code style="background: #fee2e2; padding: 0.1rem 0.3rem; border-radius: 3px;">CLAUS_JOHANNES_A_2020.jpg</code> (extra info)</li>
                                            </ul>
                                            <p style="margin: 0.5rem 0 0 0; font-size: 0.75rem; color: #78350f; font-style: italic;">
                                                💡 Files that don't match this format will be skipped during import.
                                            </p>
                                        </div>

                                        {{-- Photo Upload Drop Zone --}}
                                        <div id="photoDropZone" class="file-drop-zone" style="background: white; border-color: #93c5fd;">
                                            <i class="fa-solid fa-images" style="color: #3b82f6;"></i>
                                            <p>Drop a ZIP file OR select multiple photos</p>
                                            <span class="file-types">.zip, .jpg, .jpeg, .png, .webp</span>
                                            <input id="photoFileInput" type="file" accept=".zip,.jpg,.jpeg,.png,.webp" multiple class="file-input" />
                                        </div>

                                        {{-- Photo Upload Status --}}
                                        <div id="photoUploadStatus" style="display: none; margin-top: 0.75rem; padding: 0.75rem; background: white; border-radius: var(--radius); border: 1px solid var(--gray-200);">
                                            <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                                <span style="font-size: 0.875rem; color: var(--gray-700);">
                                                    <i class="fa-solid fa-image" style="color: #3b82f6;"></i>
                                                    <span id="photoCount">0</span> photo(s) loaded
                                                </span>
                                                <button type="button" class="btn btn-sm btn-secondary" onclick="clearPhotos()" style="padding: 0.25rem 0.5rem; font-size: 0.75rem;">
                                                    <i class="fa-solid fa-xmark"></i> Clear
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Unmatched photos preview --}}
                                        <div id="unmatchedPhotos" style="display: none; margin-top: 0.75rem;"></div>
                                    </div>
                                    
                                    <!-- Template & Preview Buttons -->
                                    <div style="display: flex; gap: 1rem; justify-content: center; margin-top: 0.5rem; flex-wrap: wrap;">
                                        <button type="button" class="btn btn-secondary" onclick="downloadTemplate()">
                                            <i class="fa-solid fa-download"></i> Download Template
                                        </button>
                                        <button type="button" class="btn btn-primary" onclick="previewFile()" id="previewBtn" disabled>
                                            <i class="fa-solid fa-eye"></i> Preview Data
                                        </button>
                                    </div>
                                </div>
                                
                                <p id="bulkImportStatus" class="bulk-import-status">No file selected yet.</p>

                                <!-- ✅ NEW: File Preview Mini Table -->
                                <div id="filePreviewContainer" style="display: none; margin-top: 1.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); overflow: hidden; background: var(--white);">
                                    <div style="background: var(--nu-blue); color: white; padding: 0.75rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                                            <i class="fa-solid fa-file-lines" style="font-size: 1.1rem;"></i>
                                            <span style="font-weight: 600;">File Preview</span>
                                            <span id="filePreviewInfo" style="font-size: 0.8125rem; opacity: 0.8; font-weight: 400;"></span>
                                        </div>
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <span id="filePreviewStats" style="font-size: 0.8125rem; opacity: 0.9;"></span>
                                            <button type="button" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 0.25rem 0.75rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;" onclick="toggleFilePreview()">
                                                <i class="fa-solid fa-chevron-up"></i> <span id="previewToggleText">Collapse</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div id="filePreviewBody" style="padding: 1rem; max-height: 400px; overflow: auto;">
                                        <div style="font-size: 0.875rem; color: var(--gray-500); margin-bottom: 0.75rem; display: flex; gap: 1rem; flex-wrap: wrap;">
                                            <span><i class="fa-regular fa-file"></i> <span id="previewFileName">-</span></span>
                                            <span><i class="fa-regular fa-table"></i> <span id="previewRowCount">0</span> rows</span>
                                            <span><i class="fa-regular fa-rectangle-list"></i> <span id="previewColumnCount">0</span> columns</span>
                                        </div>
                                        <div class="table-scroll-container" style="border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: auto; max-height: 300px;">
                                            <table id="filePreviewTable" style="width: 100%; border-collapse: collapse; font-size: 0.8125rem;">
                                                <thead style="position: sticky; top: 0; background: var(--gray-100); z-index: 5;">
                                                    <!-- Dynamic headers -->
                                                </thead>
                                                <tbody>
                                                    <!-- Dynamic rows -->
                                                </tbody>
                                            </table>
                                        </div>
                                        <div style="margin-top: 0.75rem; display: flex; gap: 0.75rem; flex-wrap: wrap; align-items: center;">
                                            <span style="font-size: 0.8125rem; color: var(--gray-500);">
                                                <i class="fa-regular fa-eye"></i> Showing first <span id="previewShowingRows">5</span> rows
                                            </span>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="showFullPreview()" id="showFullPreviewBtn" style="display: none;">
                                                <i class="fa-solid fa-expand"></i> Show All Rows
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="showFirstRowsPreview()" id="showFirstRowsBtn">
                                                <i class="fa-solid fa-compress"></i> Show First 5 Rows
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Step 2: Preview & Edit -->
                            <div id="previewStep" class="bulk-step" style="display: none;">
                                <div class="preview-header">
                                    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%; flex-wrap: wrap; gap: 0.75rem;">
                                        <div>
                                            <h4><i class="fa-solid fa-table"></i> Data Preview</h4>
                                            <p id="recordCount" style="font-size: 0.875rem; color: var(--gray-500); margin: 0.25rem 0 0 0;"></p>
                                        </div>
                                        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                                            <button type="button" class="btn btn-secondary" onclick="backToUpload()">
                                                <i class="fa-solid fa-arrow-left"></i> Back
                                            </button>
                                            <button type="button" class="btn btn-primary" onclick="validateAndImport()" id="confirmImportBtn" disabled>
                                                <i class="fa-solid fa-check"></i> Import All
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Column Mapping -->
                                <div id="columnMapping" style="margin-bottom: 1rem; padding: 1rem; background: var(--gray-50); border-radius: var(--radius-lg); border: 2px solid var(--gray-200);">
                                    <h5 style="margin: 0 0 0.5rem 0; color: var(--nu-blue); font-size: 0.9375rem;">
                                        <i class="fa-solid fa-code-branch"></i> Column Mapping
                                    </h5>
                                    <p style="font-size: 0.8125rem; color: var(--gray-500); margin: 0 0 0.75rem 0;">
                                        Map your CSV columns to database fields. Required fields are marked with <span style="color: var(--danger);">*</span>
                                        <br>
                                        <span style="font-size: 0.75rem; color: var(--gray-400);">
                                            📅 Graduation Year format: <code style="background: var(--gray-200); padding: 0.1rem 0.4rem; border-radius: 4px;">YYYY-MM-DD</code> (e.g., <code style="background: var(--gray-200); padding: 0.1rem 0.4rem; border-radius: 4px;">2027-01-01</code>)
                                        </span>
                                    </p>
                                    <div id="mappingControls" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 0.75rem;">
                                        <!-- Dynamic mapping controls will be inserted here -->
                                    </div>
                                </div>

                                <!-- Editable Table -->
                                <div class="preview-table-wrapper">
                                    <div class="table-controls" style="display: flex; gap: 0.75rem; margin-bottom: 0.75rem; flex-wrap: wrap; align-items: center;">
                                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                                            <span style="font-size: 0.8125rem; font-weight: 500;">Show:</span>
                                            <select id="pageSizeSelect" onchange="changePageSize()" style="padding: 0.375rem 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius); font-size: 0.8125rem; font-family: inherit; background: var(--white);">
                                                <option value="10">10</option>
                                                <option value="25" selected>25</option>
                                                <option value="50">50</option>
                                                <option value="100">100</option>
                                                <option value="0">All Records</option>
                                            </select>
                                        </div>
                                        
                                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                            <button type="button" class="btn btn-sm btn-primary" onclick="addColumnToPreview()">
                                                <i class="fa-solid fa-plus"></i> Add Column
                                            </button>
                                            <button type="button" class="btn btn-sm btn-info" onclick="openBatchFillModal()" style="background: #3b82f6; color: white; border: none;">
                                                <i class="fa-solid fa-fill-drip"></i> Batch Fill
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="applyValueToAllRowsPrompt()">
                                                <i class="fa-solid fa-fill"></i> Fill All
                                            </button>
                                        </div>
                                        
                                        <div style="display: flex; gap: 0.5rem; margin-left: auto; flex-wrap: wrap;">
                                            <span id="paginationInfo" style="font-size: 0.8125rem; color: var(--gray-500);"></span>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="validateAllRows()">
                                                <i class="fa-solid fa-check-double"></i> Re-validate
                                            </button>
                                            <button type="button" class="btn btn-sm btn-secondary" onclick="clearAllErrors()">
                                                <i class="fa-solid fa-eraser"></i> Clear Errors
                                            </button>
                                        </div>
                                    </div>
                                    
                                    <div class="table-scroll-container">
                                        <table id="previewTable" class="editable-table" style="width: 100%; min-width: 800px; border-collapse: collapse; font-size: 0.875rem;">
                                            <thead style="position: sticky; top: 0; background: var(--nu-blue); color: white; z-index: 10;">
                                                <!-- Dynamic headers will be inserted here -->
                                            </thead>
                                            <tbody>
                                                <!-- Dynamic rows will be inserted here -->
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <!-- Pagination Controls -->
                                    <div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 0.75rem; flex-wrap: wrap;">
                                        <!-- Dynamic pagination controls will be inserted here -->
                                    </div>
                                </div>

                                <!-- Column Guide -->
                                <div class="column-guide" style="margin-top: 1rem; padding: 0.75rem 1rem; background: var(--gray-50); border-radius: var(--radius); border: 1px solid var(--gray-200); display: flex; flex-wrap: wrap; gap: 1.5rem; align-items: center;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="color: #ff6b6b; font-weight: 700; font-size: 1.1rem;">*</span>
                                        <span style="font-size: 0.8125rem; color: var(--gray-600);">Required fields</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 0.8125rem; color: var(--gray-600);">📅</span>
                                        <span style="font-size: 0.8125rem; color: var(--gray-600);"><strong>Year Graduated:</strong> Format <code style="background: var(--gray-200); padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.75rem;">YYYY-MM-DD</code> (e.g., <code style="background: var(--gray-200); padding: 0.1rem 0.4rem; border-radius: 4px; font-size: 0.75rem;">2027-01-01</code>)</span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-size: 0.8125rem; color: var(--gray-600);">💡</span>
                                        <span style="font-size: 0.8125rem; color: var(--gray-600);">Click <strong>⬇</strong> next to any cell to apply its value to <strong>all rows</strong></span>
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-left: auto;">
                                        <span style="font-size: 0.8125rem; color: var(--gray-500);">📌</span>
                                        <span style="font-size: 0.8125rem; color: var(--gray-500);">Right-click cell for options</span>
                                    </div>
                                </div>

                                <!-- Validation Summary -->
                                <div id="validationSummary" style="margin-top: 1rem; padding: 1rem; border-radius: var(--radius-lg); background: var(--gray-50); border: 2px solid var(--gray-200); display: none;">
                                    <!-- Dynamic validation summary will be inserted here -->
                                </div>

                                <!-- Photo Upload — available in preview step too -->
                                <div id="previewPhotoBar" style="margin: 1rem 0; padding: 1rem; background: #eff6ff; border: 2px solid #93c5fd; border-radius: var(--radius-lg); display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                                    <div style="flex: 1; min-width: 220px;">
                                        <strong style="color: var(--nu-blue); font-size: 0.9375rem;">
                                            <i class="fa-solid fa-id-card"></i> Card Photos
                                        </strong>
                                        <p style="font-size: 0.8125rem; color: var(--gray-600); margin: 0.25rem 0 0 0;">
                                            <span id="previewPhotoCount">0</span> photo(s) loaded.
                                            Filename format: <code style="background: white; padding: 0.1rem 0.3rem; border-radius: 3px;">SURNAME_FIRST NAME_MI.jpg</code>
                                        </p>
                                    </div>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <label class="btn btn-sm btn-primary" style="cursor: pointer; margin: 0;">
                                            <i class="fa-solid fa-images"></i> Add Photos
                                            <input type="file" id="previewPhotoInput" accept=".zip,.jpg,.jpeg,.png,.webp" multiple style="display: none;">
                                        </label>
                                        <button type="button" class="btn btn-sm btn-secondary" onclick="clearPhotos()">
                                            <i class="fa-solid fa-xmark"></i> Clear
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Batch Fill Modal -->
                            <div id="batchFillModal" class="modal-overlay" aria-hidden="true" style="display: none;">
                                <div class="modal-content-wrapper" style="max-width: 750px;">
                                    <div class="modal-card">
                                        <div class="modal-header">
                                            <div>
                                                <h2 class="modal-title">
                                                    <i class="fa-solid fa-fill-drip"></i>
                                                    Batch Fill
                                                </h2>
                                                <p class="modal-subtitle">Fill a column with a value for a specific range of rows</p>
                                            </div>
                                            <button class="modal-close" onclick="closeBatchFillModal()" title="Close">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="modal-body" style="padding: 1.5rem;">
                                            <!-- ✅ NEW: Preview Section -->
                                            <div style="margin-bottom: 1.5rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); overflow: hidden; background: var(--white);">
                                                <div style="background: var(--nu-blue); color: white; padding: 0.5rem 1rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                                        <i class="fa-solid fa-table" style="font-size: 1rem;"></i>
                                                        <span style="font-weight: 600; font-size: 0.9375rem;">Data Preview</span>
                                                        <span id="batchPreviewInfo" style="font-size: 0.75rem; opacity: 0.8; font-weight: 400;"></span>
                                                    </div>
                                                    <div style="display: flex; gap: 0.5rem; align-items: center;">
                                                        <button type="button" class="btn btn-sm" style="background: rgba(255,255,255,0.2); color: white; border: none; padding: 0.2rem 0.6rem; border-radius: 4px; cursor: pointer; font-size: 0.75rem;" onclick="toggleBatchPreview()">
                                                            <i class="fa-solid fa-chevron-up"></i> <span id="batchPreviewToggle">Collapse</span>
                                                        </button>
                                                    </div>
                                                </div>
                                                <div id="batchPreviewBody" style="padding: 0.75rem; max-height: 250px; overflow: auto; background: var(--gray-50);">
                                                    <div class="table-scroll-container" style="border: 1px solid var(--gray-200); border-radius: var(--radius); overflow: auto; max-height: 200px;">
                                                        <table id="batchPreviewTable" style="width: 100%; border-collapse: collapse; font-size: 0.75rem;">
                                                            <thead style="position: sticky; top: 0; background: var(--gray-200); z-index: 5;">
                                                                <!-- Dynamic headers -->
                                                            </thead>
                                                            <tbody>
                                                                <!-- Dynamic rows -->
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                                                        <span style="font-size: 0.75rem; color: var(--gray-500);">
                                                            <i class="fa-regular fa-eye"></i> Showing <span id="batchPreviewRowCount">0</span> rows
                                                        </span>
                                                        <div style="display: flex; gap: 0.25rem;">
                                                            <button type="button" class="btn btn-sm btn-secondary" onclick="batchPreviewAllRows()" style="padding: 0.1rem 0.5rem; font-size: 0.7rem;" id="batchShowAllBtn">
                                                                <i class="fa-solid fa-expand"></i> All
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-secondary" onclick="batchPreviewFirstRows()" style="padding: 0.1rem 0.5rem; font-size: 0.7rem;" id="batchShowFirstBtn">
                                                                <i class="fa-solid fa-compress"></i> First 10
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Batch Fill Form -->
                                            <form id="batchFillForm" onsubmit="executeBatchFill(event)">
                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                                                    <div class="form-group">
                                                        <label for="batchColumn">Select Column <span style="color: var(--danger);">*</span></label>
                                                        <select id="batchColumn" class="form-control" required>
                                                            <option value="">-- Select a column --</option>
                                                        </select>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="batchValue">Value to Set <span style="color: var(--danger);">*</span></label>
                                                        <input type="text" id="batchValue" class="form-control" placeholder="e.g., BSCE, BSIT, 2024-01-01" required>
                                                    </div>
                                                </div>

                                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1rem;">
                                                    <div class="form-group">
                                                        <label for="batchStartRow">Start Row <span style="color: var(--danger);">*</span></label>
                                                        <input type="number" id="batchStartRow" class="form-control" min="1" placeholder="1" required>
                                                        <small style="color: var(--gray-500); font-size: 0.75rem;">Row numbers start at 1 (first data row)</small>
                                                    </div>

                                                    <div class="form-group">
                                                        <label for="batchEndRow">End Row <span style="color: var(--danger);">*</span></label>
                                                        <input type="number" id="batchEndRow" class="form-control" min="1" placeholder="10" required>
                                                    </div>
                                                </div>

                                                <div class="form-note" style="margin: 1rem 0 0;">
                                                    <i class="fa-solid fa-circle-info"></i>
                                                    <div>
                                                        <strong>Example:</strong> To set rows 1-10 under "Program" to "BSCE", select the "Program" column, set Start Row = 1, End Row = 10, and Value = "BSCE".
                                                        <br>
                                                        <span style="font-size: 0.8125rem; color: var(--gray-500);">
                                                            💡 The preview table above shows your current data to help you choose the right row range.
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="modal-actions" style="margin-top: 1.5rem;">
                                                    <button type="button" class="btn btn-secondary" onclick="closeBatchFillModal()">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">
                                                        <i class="fa-solid fa-fill-drip"></i> Apply Batch Fill
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Help Section -->
                            <div class="bulk-import-help" style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 2px solid var(--gray-200);">
                                <h4 style="font-size: 1rem; font-weight: 600; color: var(--nu-blue); margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
                                    <i class="fa-solid fa-lightbulb" style="color: var(--nu-gold);"></i> Required Columns
                                </h4>
                                <ul style="list-style: none; padding: 0; margin: 0 0 0.75rem 0;">
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Student ID</strong> (unique identifier)
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>First Name</strong>
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Last Name</strong>
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Email</strong> (for login)
                                    </li>
                                    <li style="font-size: 0.875rem; color: var(--gray-600); padding: 0.375rem 0 0.375rem 1.75rem; position: relative; line-height: 1.5;">
                                        <span style="position: absolute; left: 0; color: var(--danger); font-weight: 700;">*</span>
                                        <strong>Program</strong> (e.g., BS Computer Science)
                                    </li>
                                </ul>
                                <p class="help-note" style="font-size: 0.875rem; color: var(--gray-500); margin: 0; font-style: italic; padding: 0.75rem 1rem; background: var(--white); border-radius: var(--radius); border: 1px solid var(--gray-200);">
                                    <i class="fa-solid fa-circle-info" style="color: var(--info);"></i>
                                    Optional: Middle Name, Phone Number, Graduation Year, Date of Birth, Sex
                                </p>
                                <p class="help-note" style="font-size: 0.875rem; color: var(--gray-500); margin: 0.5rem 0 0 0; padding: 0.75rem 1rem; background: var(--info-light); border-radius: var(--radius); border: 1px solid var(--info);">
                                    <i class="fa-solid fa-pen-to-square" style="color: var(--info);"></i>
                                    You can edit any cell in the preview table before importing.
                                </p>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Alumni Modal -->
    <div id="manageModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper">
            <div class="modal-card" style="max-width: 720px; margin: 0 auto;">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-user-gear"></i>
                            Manage Account
                        </h2>
                        <p class="modal-subtitle">Account actions for <strong id="manageAlumniName"></strong></p>
                    </div>
                    <button class="modal-close" onclick="hideManageModal()" title="Close" aria-label="Close manage account dialog">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <input type="hidden" id="manageAlumniId">
                    <input type="hidden" id="manageAccountStatus" value="1">
                    
                    <div class="manage-action-group">
                        <!-- Reset Password -->
                        <div class="manage-action-item">
                            <div class="manage-action-icon icon-info">
                                <i class="fa-solid fa-key"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Reset Password</h4>
                                <p>Generate a new temporary password. Alumni must change it on next login.</p>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="prepareResetPassword()">
                                <i class="fa-solid fa-rotate"></i> Reset
                            </button>
                        </div>

                        <!-- Add College Program (only for SHS alumni) -->
                        <div class="manage-action-item" 
                            id="promoteActionItem" 
                            style="display: none;">
                            <div class="manage-action-icon" style="background:#ede9fe;color:#7c3aed;">
                                <i class="fa-solid fa-graduation-cap"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Add College Program</h4>
                                <p>Record this SHS alumnus's College enrollment at NU Lipa. Their SHS identity and tracer stay active until College graduation.</p>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="openPromoteModal()">
                                <i class="fa-solid fa-plus"></i> Add Program
                            </button>
                        </div>

                        <!-- Manage College Program (only when a college row exists) -->
                        <div class="manage-action-item" 
                            id="manageCollegeActionItem" 
                            style="display: none;">
                            <div class="manage-action-icon" style="background:#dbeafe;color:#1d4ed8;">
                                <i class="fa-solid fa-book-open"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Manage College Enrollment</h4>
                                <p id="manageCollegeDesc">Update the enrollment status for this alumnus's College program.</p>
                            </div>
                            <button type="button" class="btn btn-primary" onclick="openManageCollegeModal()">
                                <i class="fa-solid fa-pen-to-square"></i> Manage
                            </button>
                        </div>

                        <!-- Restrict/Unrestrict Account -->
                        <div class="manage-action-item" id="restrictActionItem">
                            <div class="manage-action-icon icon-warning" id="restrictActionIcon">
                                <i class="fa-solid fa-user-slash"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4 id="restrictTitle">Restrict Account</h4>
                                <p id="restrictDesc">Temporarily suspend access for this alumnus.</p>
                            </div>
                            <button type="button" class="btn btn-warning" id="manageRestrictBtn" onclick="openRestrictModal()">
                                <i class="fa-solid fa-lock"></i> Restrict
                            </button>
                        </div>

                        <!-- Archive Account (Replaces Delete) -->
                        <div class="manage-action-item">
                            <div class="manage-action-icon icon-archive" style="background: #fef3c7; color: #d97706;">
                                <i class="fa-solid fa-box-archive"></i>
                            </div>
                            <div class="manage-action-content">
                                <h4>Archive Account</h4>
                                <p>Soft-delete this account. The alumni will not be able to log in.</p>
                            </div>
                            <button type="button" class="btn btn-warning" onclick="prepareArchive()">
                                <i class="fa-solid fa-box-archive"></i> Archive
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add College Program Modal -->
    <div id="promoteModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 550px;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-graduation-cap"></i>
                            Add College Program
                        </h2>
                        <p class="modal-subtitle">
                            Recording College enrollment for <strong id="promoteAlumniName"></strong>.
                            They will remain an SHS alumnus until College graduation.
                        </p>
                    </div>
                    <button class="modal-close" onclick="hidePromoteModal()" title="Close" aria-label="Close dialog">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="promoteForm" onsubmit="executePromote(event)">
                        <input type="hidden" id="promoteAlumniId">

                        <div class="form-group">
                            <label for="promoteProgram">
                                College Program <span style="color:var(--danger);">*</span>
                            </label>
                            <input type="text" id="promoteProgram" required
                                placeholder="e.g., BS Information Technology">
                        </div>

                        <div class="form-group">
                            <label for="promoteStudentId">
                                College Student ID <span style="color:var(--danger);">*</span>
                            </label>
                            <input type="text" id="promoteStudentId" required
                                placeholder="e.g., 2026-99999">
                        </div>

                        <div class="form-group">
                            <label for="promoteYearGrad">
                                Expected Graduation <span style="color:var(--danger);">*</span>
                            </label>
                            <input type="date" id="promoteYearGrad" required>
                        </div>

                        <div class="form-group">
                            <label>Enrollment Status <span style="color:var(--danger);">*</span></label>
                            <div class="alumni-type-toggle" id="promote-enrollment-toggle">
                                <button type="button"
                                        class="type-option active"
                                        data-status="enrolled"
                                        onclick="selectEnrollmentStatus('enrolled')">
                                    <i class="fa-solid fa-book-open"></i>
                                    <span>Currently Enrolled</span>
                                </button>
                                <button type="button"
                                        class="type-option"
                                        data-status="graduated"
                                        onclick="selectEnrollmentStatus('graduated')">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    <span>Already Graduated</span>
                                </button>
                                <button type="button"
                                        class="type-option"
                                        data-status="dropped"
                                        onclick="selectEnrollmentStatus('dropped')">
                                    <i class="fa-solid fa-user-xmark"></i>
                                    <span>Dropped / Left NU</span>
                                </button>
                            </div>
                            <input type="hidden" id="promoteEnrollmentStatus" value="enrolled">
                            <p class="help-text" id="enrollmentHelp" style="margin-top: 0.5rem;">
                                The alumnus stays an SHS alumnus with an active College enrollment.
                            </p>
                        </div>

                        <div class="form-note" style="margin-top: 1rem;">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>
                                <strong>What happens:</strong>
                                <ul style="margin: 0.25rem 0 0 1rem; padding: 0;">
                                    <li>Account stays the same (no re-registration)</li>
                                    <li>SHS identity and SHS tracer stay active</li>
                                    <li>College program is added as a second program</li>
                                    <li>They become a <strong>College alumnus only when College is marked graduated</strong></li>
                                </ul>
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="hidePromoteModal()">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="promoteSubmitBtn">
                                <i class="fa-solid fa-plus"></i> Add College Program
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Restriction Reason Modal -->
    <div id="restrictReasonModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 550px;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-user-slash"></i>
                            <span id="restrictModalTitle">Restrict Account</span>
                        </h2>
                        <p class="modal-subtitle">Provide a reason for restricting <strong id="restrictModalName"></strong>'s account</p>
                    </div>
                    <button class="modal-close" onclick="hideRestrictReasonModal()" title="Close" aria-label="Close restriction dialog">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="restrictForm" onsubmit="executeRestrict(event)">
                        <input type="hidden" id="restrictAlumniId">
                        <input type="hidden" id="restrictAction" value="restrict">
                        
                        <div class="form-group">
                            <label for="restrictionReason">Reason for Restriction <span style="color: var(--danger);">*</span></label>
                            <select id="restrictionReason" class="form-control" required>
                                <option value="">Select a reason...</option>
                                <option value="spam_fraud">Spam or Fraud</option>
                                <option value="nudity_sexual">Nudity or Sexual Content</option>
                                <option value="hate_speech">Hate Speech or Symbols</option>
                                <option value="violence">Violence or Dangerous Organizations</option>
                                <option value="bullying">Bullying or Harassment</option>
                                <option value="illegal_goods">Sale of Illegal or Regulated Goods</option>
                                <option value="ip_violation">Intellectual Property Violation</option>
                                <option value="other">Other (Please Specify)</option>
                            </select>
                        </div>

                        <div class="form-group" id="customReasonGroup" style="display: none;">
                            <label for="customReason">Please specify:</label>
                            <input type="text" id="customReason" class="form-control" placeholder="Enter custom reason...">
                        </div>

                        <div class="form-group">
                            <label for="restrictionComment">Additional Comments <span style="font-weight: 400; color: var(--gray-500);">(Optional)</span></label>
                            <textarea id="restrictionComment" class="form-control" rows="3" 
                                    placeholder="Add any additional notes about this restriction..." 
                                    style="resize: vertical; min-height: 80px;"></textarea>
                        </div>

                        <div class="form-note" style="margin: 1rem 0 0;">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>
                                <strong>Note:</strong> The reason and comments will be emailed to the alumni and shown when they try to log in.
                            </div>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="hideRestrictReasonModal()">Cancel</button>
                            <button type="submit" class="btn btn-warning" id="restrictConfirmBtn">
                                <i class="fa-solid fa-lock"></i> <span id="restrictBtnText">Restrict Account</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Archive Confirmation Modal -->
    <div id="archiveConfirmModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-box-archive"></i>
                </div>
                <h2 class="confirm-title">Archive Account</h2>
                <p class="confirm-message">
                    Are you sure you want to archive <strong id="archiveConfirmName"></strong>'s account?
                    <br><small>The alumni will not be able to log in. You can restore the account later from the Archived Accounts section.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideArchiveConfirm()">Cancel</button>
                    <button type="button" class="btn btn-warning" onclick="executeArchive()">
                        <i class="fa-solid fa-box-archive"></i> Archive
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteConfirmModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <h2 class="confirm-title">Delete Account</h2>
                <p class="confirm-message">
                    Are you sure you want to delete <strong id="confirmAlumniName"></strong>? 
                    <br>This action cannot be undone.
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideDeleteConfirm()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger" onclick="executeDelete()">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Confirmation Modal -->
    <div id="resetPasswordConfirmModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" style="background: #eff6ff; color: #3b82f6;">
                    <i class="fa-solid fa-key"></i>
                </div>
                <h2 class="confirm-title">Reset Password</h2>
                <p class="confirm-message">
                    Are you sure you want to reset the password for <strong id="resetConfirmName"></strong>?
                    <br><small>A new temporary password will be generated and emailed. The alumnus will be required to change it upon next login.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideResetPasswordConfirm()" >
                        Cancel
                    </button>
                    <button type="button" class="btn btn-primary" onclick="executeResetPassword()">
                        <i class="fa-solid fa-rotate"></i> Reset Password
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Restrict Account Confirmation Modal -->
    <div id="restrictConfirmModal" class="modal-overlay" aria-hidden="true">
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="confirm-modal-card">
                <div class="confirm-icon-wrapper" id="restrictConfirmIcon" style="background: #fef3c7; color: #d97706;">
                    <i class="fa-solid fa-user-slash"></i>
                </div>
                <h2 class="confirm-title" id="restrictConfirmTitle">Restrict Account</h2>
                <p class="confirm-message" id="restrictConfirmMessage">
                    Are you sure you want to restrict <strong id="restrictConfirmName"></strong>'s account?
                    <br><small>They will not be able to log in until unrestricted.</small>
                </p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-secondary" onclick="hideRestrictConfirm()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-warning" id="restrictConfirmBtn" onclick="executeToggleRestrict()">
                        <i class="fa-solid fa-lock"></i> Restrict
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage College Program Modal -->
    <div id="manageCollegeModal" class="modal-overlay" aria-hidden="true" inert>
        <div class="modal-content-wrapper" style="max-width: 550px;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-book-open"></i>
                            Manage College Program
                        </h2>
                        <p class="modal-subtitle">
                            <strong id="manageCollegeName"></strong>
                        </p>
                    </div>
                    <button class="modal-close" onclick="hideManageCollegeModal()" title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="manageCollegeForm" onsubmit="executeManageCollege(event)">
                        <input type="hidden" id="manageCollegeAlumniId">
                        <input type="hidden" id="manageCollegeProgramId">

                        {{-- Program Info (read-only display) --}}
                        <div class="form-note" style="margin-bottom: 1rem;">
                            <i class="fa-solid fa-circle-info"></i>
                            <div>
                                <strong>Program:</strong> <span id="manageCollegeProgramName">—</span><br>
                                <strong>Student ID:</strong> <span id="manageCollegeStudentId">—</span>
                            </div>
                        </div>

                        {{-- Status selector --}}
                        <div class="form-group">
                            <label>Enrollment Status <span style="color:var(--danger);">*</span></label>
                            <div class="alumni-type-toggle" id="manage-college-status-toggle">
                                <button type="button"
                                        class="type-option"
                                        data-status="enrolled"
                                        onclick="selectManageCollegeStatus('enrolled')">
                                    <i class="fa-solid fa-book-open"></i>
                                    <span>Enrolled</span>
                                </button>
                                <button type="button"
                                        class="type-option"
                                        data-status="graduated"
                                        onclick="selectManageCollegeStatus('graduated')">
                                    <i class="fa-solid fa-graduation-cap"></i>
                                    <span>Graduated</span>
                                </button>
                                <button type="button"
                                        class="type-option"
                                        data-status="dropped"
                                        onclick="selectManageCollegeStatus('dropped')">
                                    <i class="fa-solid fa-user-xmark"></i>
                                    <span>Dropped</span>
                                </button>
                            </div>
                            <input type="hidden" id="manageCollegeStatusInput" value="enrolled">
                            <p class="help-text" id="manageCollegeHelp" style="margin-top: 0.5rem;"></p>
                        </div>

                        <div class="modal-actions">
                            <button type="button" class="btn btn-secondary" onclick="hideManageCollegeModal()">
                                Cancel
                            </button>
                            <button type="submit" class="btn btn-primary" id="manageCollegeSubmitBtn">
                                <i class="fa-solid fa-save"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Toast -->
    <div id="alertToast" class="alert-toast">
        <i class="alert-icon fa-solid fa-circle-check"></i>
        <span class="alert-message"></span>
        <button class="alert-close" onclick="hideAlert()" aria-label="Dismiss notification"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <script>

        // ========================================
        // SECURITY: HTML escaping helper
        // ========================================
        window.escapeHtml = function(str) {
            const div = document.createElement('div');
            div.textContent = String(str ?? '');
            return div.innerHTML;
        };

// ========================================
// EXPORT DROPDOWN MENU
// ========================================
function toggleExportMenu(event) {
    event.stopPropagation();
    const dropdown = document.getElementById('exportDropdown');
    dropdown.classList.toggle('open');
}

function closeExportMenu() {
    document.getElementById('exportDropdown')?.classList.remove('open');
}

// Close on outside click
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('exportDropdown');
    if (dropdown && !dropdown.contains(e.target)) {
        dropdown.classList.remove('open');
    }
});

// Close on Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeExportMenu();
});
        
    // Add once at the top of the <script> block, outside any function
    (function injectPreviewRowStyles() {
        if (document.getElementById('previewRowStyles')) return;
        const style = document.createElement('style');
        style.id = 'previewRowStyles';
        style.textContent = `
            .preview-row:hover .row-delete-btn { display: block !important; }
            .preview-row .row-delete-btn { display: none; }
        `;
        document.head.appendChild(style);
    })();

    // ========================================
    // CONSOLE DEBUG - Check if script loads
    // ========================================
    console.log('✅ Alumni Directory page JavaScript loaded');

    // Mobile menu toggle
    function toggleMobileMenu() {
        const sidebar = document.getElementById('adminSidebar');
        const overlay = document.getElementById('mobileOverlay');
        sidebar.classList.toggle('mobile-open');
        overlay.classList.toggle('active');
        document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
    }

    // Search filter function
    function filterAlumni() {
        const q = document.getElementById('searchInput').value.toLowerCase().trim();
        const cards = document.querySelectorAll('.alumni-card');
        let visibleCount = 0;
        
        cards.forEach(card => {
            const name = (card.dataset.name || '').toLowerCase();
            const email = (card.dataset.email || '').toLowerCase();
            const program = (card.dataset.program || '').toLowerCase();
            const visible = !q || name.includes(q) || email.includes(q) || program.includes(q);
            
            card.style.display = visible ? 'flex' : 'none';
            if (visible) visibleCount++;
        });
        
        // Update results count
        document.getElementById('resultsCount').textContent = 
            `${visibleCount} result${visibleCount != 1 ? 's' : ''}`;
        
        // Show/hide clear button
        document.getElementById('clearSearch').style.display = q ? 'flex' : 'none';
    }

    function clearSearch() {
        document.getElementById('searchInput').value = '';
        filterAlumni();
        document.getElementById('searchInput').focus();
    }

    // Modal functions
    function showModal() {
        const modal = document.getElementById('createModal');
        if (!modal) return;

        modal.classList.add('active');
        document.body.style.overflow = 'hidden';

        // ✅ Always re-sync the toggle to reflect the current hidden input value
        const inputEl = document.getElementById('single-alumni-type-input');
        if (inputEl) {
            const currentValue = inputEl.value || 'college';
            console.log('📂 Modal opened — re-syncing toggle to:', currentValue);
            selectAlumniType('single', currentValue);
        }
    }

    function hideModal() {
        document.getElementById('createModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    function switchModalMode(mode) {
        // Update tabs
        document.querySelectorAll('[data-modal-tab]').forEach((tab) => {
            tab.classList.toggle('active', tab.dataset.modalTab === mode);
        });

        // Update sections
        document.querySelectorAll('[data-modal-section]').forEach((section) => {
            section.classList.toggle('modal-section-active', section.dataset.modalSection === mode);
        });
    }

    // File upload preview
    document.getElementById('card_photo')?.addEventListener('change', function(e) {
        const fileName = e.target.files[0]?.name;
        const label = this.closest('.file-upload-wrapper')?.querySelector('.file-upload-label');
        if (label && fileName) {
            label.textContent = fileName;
        }
    });

    // Bulk import file drop zone
    const dropZone = document.getElementById('fileDropZone');
    const fileInput = document.getElementById('bulkImportFile');
    
    if (dropZone && fileInput) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('drag-over'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('drag-over'), false);
        });

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files[0]) {
                fileInput.files = files;
                updateFileName(files[0].name);
            }
        }

        fileInput.addEventListener('change', function(e) {
            if (e.target.files[0]) {
                updateFileName(e.target.files[0].name);
            }
        });

        function updateFileName(name) {
            const status = document.getElementById('bulkImportStatus');
            if (status) {
                status.textContent = `Selected: ${name}`;
                status.style.color = 'var(--success)';
            }
        }
    }

    // Bulk import logic
    function normalizeBulkRow(row) {
        const findKey = (obj, possibleKeys) => {
            for (const key of possibleKeys) {
                if (obj[key] !== undefined) return obj[key];
                const found = Object.keys(obj).find(k => k.toLowerCase() === key.toLowerCase());
                if (found) return obj[found];
            }
            return '';
        };

        return {
            first_name: findKey(row, ['First Name', 'first_name', 'FirstName']).trim(),
            middle_name: findKey(row, ['Middle Name', 'middle_name', 'MiddleName']).trim(),
            last_name: findKey(row, ['Last Name', 'last_name', 'LastName']).trim(),
            student_id_number: findKey(row, ['Student ID', 'student_id_number', 'StudentID', 'Student ID Number']).trim(),
            program: findKey(row, ['Strand', 'program', 'Program', 'Department']).trim(),
            email: findKey(row, ['Personal Email', 'Official Email', 'email', 'Email', 'E-mail']).trim(),
            phone_number: findKey(row, ['Mobile No', 'phone_number', 'MobileNo', 'Mobile Number', 'Phone']).trim(),
            year_graduated: findKey(row, ['Graduation Period', 'year_graduated', 'GraduationPeriod', 'Year Graduated']).trim(),
            date_of_birth: '',
            sex: findKey(row, ['Sex', 'sex', 'Gender']).trim(),
        };
    }

    async function createAlumniRecord(record) {
        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        
        Object.keys(record).forEach(key => {
            if (record[key] !== undefined && record[key] !== null && record[key] !== '') {
                formData.append(key, record[key]);
            }
        });

        const response = await fetch('{{ route('admin.alumni.store') }}', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            },
            body: formData,
        });

        const payload = await response.json().catch(() => null);

        if (!response.ok) {
            let errorMessage = 'Unable to import record.';
            if (payload?.errors) {
                const errorList = [];
                Object.entries(payload.errors).forEach(([field, messages]) => {
                    errorList.push(`${field}: ${messages.join(', ')}`);
                });
                errorMessage = errorList.join(' | ');
            } else if (payload?.message) {
                errorMessage = payload.message;
            }
            throw new Error(errorMessage);
        }

        return payload;
    }

    // Alert toast system
    function showAlert(message, type = 'success') {
        const toast = document.getElementById('alertToast');
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
        
        icon.className = `alert-icon fa-solid ${icons[type] || icons.success}`;
        toast.style.borderColor = colors[type] || colors.success;
        msg.textContent = message;
        
        toast.classList.add('show');
        
        setTimeout(() => hideAlert(), 4000);
    }

    function hideAlert() {
        document.getElementById('alertToast').classList.remove('show');
    }

    // Close modal when clicking outside
    document.getElementById('createModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideModal();
    });

    // Close delete confirm modal when clicking outside
    document.getElementById('deleteConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideDeleteConfirm();
    });

    // Close sidebar on nav item click (mobile)
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            if (window.innerWidth <= 1024) {
                toggleMobileMenu();
            }
        });
    });

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function() {
            if (window.innerWidth > 1024) {
                document.getElementById('adminSidebar')?.classList.remove('mobile-open');
                document.getElementById('mobileOverlay')?.classList.remove('active');
                document.body.style.overflow = '';
            }
        }, 250);
    });

    // Initialize search clear button visibility
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearch');
        if (searchInput && clearBtn) {
            clearBtn.style.display = searchInput.value ? 'flex' : 'none';
        }
    });

    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            hideModal();
            hideManageModal();
            hideDeleteConfirm();
            hideAlert();
        }
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            document.getElementById('searchInput')?.focus();
        }
    });

    // Safely handle clicks on manage buttons
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('.manage-btn');
        if (btn) {
            const id = btn.dataset.id;
            const name = btn.dataset.name;
            const status = btn.dataset.status || 1;
            const alumniType = btn.dataset.alumniType || '';
            openManageModal(id, name, status, alumniType);
        }
    });

    // ========================================
// WARNING MODAL SYSTEM
// ========================================

function showWarningModal(config) {
    const {
        title = 'Confirm Action',
        message = 'Are you sure?',
        iconType = 'warning',
        confirmText = 'Confirm',
        confirmClass = 'btn-danger',
        onConfirm = null,
        data = null
    } = config;

    // Use the existing warning modal HTML or create one dynamically
    let overlay = document.getElementById('warningModal');
    
    // If the modal doesn't exist in HTML, create it dynamically
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.id = 'warningModal';
        overlay.className = 'warning-modal-overlay';
        overlay.innerHTML = `
            <div class="warning-modal">
                <div class="warning-modal-icon" id="warningModalIcon">
                    <i class="fa-solid fa-triangle-exclamation"></i>
                </div>
                <div class="warning-modal-content">
                    <h2 class="warning-modal-title" id="warningModalTitle">Confirm Action</h2>
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
        `;
        document.body.appendChild(overlay);
    }

    const titleEl = document.getElementById('warningModalTitle');
    const messageEl = document.getElementById('warningModalMessage');
    const iconEl = document.getElementById('warningModalIcon');
    const confirmBtn = document.getElementById('warningModalConfirm');
    const cancelBtn = document.getElementById('warningModalCancel');

    titleEl.textContent = title;
    messageEl.innerHTML = message;
    
    // Set icon
    iconEl.className = 'warning-modal-icon ' + iconType;
    const iconElement = iconEl.querySelector('i');
    if (iconType === 'danger') iconElement.className = 'fa-solid fa-triangle-exclamation';
    else if (iconType === 'warning') iconElement.className = 'fa-solid fa-triangle-exclamation';
    else if (iconType === 'success') iconElement.className = 'fa-solid fa-circle-check';
    else if (iconType === 'info') iconElement.className = 'fa-solid fa-circle-info';
    else iconElement.className = 'fa-solid fa-circle-question';

    confirmBtn.className = 'btn ' + confirmClass;
    confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;

    // Store the callback
    window._pendingAction = onConfirm;
    window._pendingData = data;

    // Remove old listeners and add new ones
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    const newCancelBtn = cancelBtn.cloneNode(true);
    cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

    newConfirmBtn.addEventListener('click', function() {
        if (window._pendingAction) {
            window._pendingAction(window._pendingData);
        }
        closeWarningModal();
    });

    newCancelBtn.addEventListener('click', closeWarningModal);

    overlay.classList.add('active');
    document.body.style.overflow = 'hidden';
    newConfirmBtn.focus();
}

function closeWarningModal() {
    const overlay = document.getElementById('warningModal');
    if (overlay) {
        overlay.classList.remove('active');
        document.body.style.overflow = '';
    }
    window._pendingAction = null;
    window._pendingData = null;
}

    // ========================================
    // MANAGE MODAL - ENHANCED WITH AJAX
    // ========================================
    let currentManageAlumniId = null;
    let currentManageAlumniName = '';
    let currentManageAccountStatus = 1;

    function openManageModal(id, name, status) {
        const accountStatus = status !== undefined ? parseInt(status) : 1;
        
        currentManageAlumniId = id;
        currentManageAlumniName = name;
        currentManageAccountStatus = accountStatus;
        
        document.getElementById('manageAlumniId').value = id;
        document.getElementById('manageAlumniName').textContent = name;
        document.getElementById('manageAccountStatus').value = accountStatus;
        
        updateRestrictButton(accountStatus);
        
        const modal = document.getElementById('manageModal');
        // Remove closing class if it exists
        modal.classList.remove('closing');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function openPromoteModal() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;

        document.getElementById('promoteAlumniId').value = id;
        document.getElementById('promoteAlumniName').textContent = name;

        // Reset fields
        document.getElementById('promoteProgram').value = '';
        document.getElementById('promoteStudentId').value = '';
        document.getElementById('promoteYearGrad').value = '';
        selectEnrollmentStatus('enrolled');

        // Close manage modal
        hideManageModal();

        // Show promote modal
        const modal = document.getElementById('promoteModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';

        setTimeout(() => document.getElementById('promoteProgram')?.focus(), 150);
    }

    /**
     * Enrollment status selector for the Add College Program modal.
     */
    function selectEnrollmentStatus(status) {
        const toggle = document.getElementById('promote-enrollment-toggle');
        const input = document.getElementById('promoteEnrollmentStatus');
        const help = document.getElementById('enrollmentHelp');
        if (!toggle || !input) return;

        toggle.querySelectorAll('.type-option').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.status === status);
        });
        input.value = status;

        // Context-aware help text
        const messages = {
            enrolled: 'The alumnus stays an SHS alumnus with an active College enrollment.',
            graduated: 'This alumnus becomes a College alumnus immediately. They will owe both SHS and College tracers.',
            dropped: 'They attended College but left without graduating. They stay an SHS alumnus; only the SHS tracer applies.',
        };
        if (help) help.textContent = messages[status] || '';
    }

    function hidePromoteModal() {
        const modal = document.getElementById('promoteModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
    }

    async function executePromote(event) {
        event.preventDefault();

        const id = document.getElementById('promoteAlumniId').value;
        const program = document.getElementById('promoteProgram').value.trim();
        const studentId = document.getElementById('promoteStudentId').value.trim();
        const yearGrad = document.getElementById('promoteYearGrad').value;

        if (!program || !studentId || !yearGrad) {
            showAlert('Please fill in all fields.', 'error');
            return;
        }

        // Loading state
        const submitBtn = document.getElementById('promoteSubmitBtn');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Promoting...';

        try {
                const response = await fetch(`/admin/alumni/${id}/add-college-program`, {                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    program: program,
                    student_id_number: studentId,
                    year_graduated: yearGrad,
                    enrollment_status: document.getElementById('promoteEnrollmentStatus')?.value || 'enrolled'
                })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert(data.message || 'Alumnus promoted to College successfully!', 'success');

                // Close modal
                hidePromoteModal();

                // Reload page after a short delay so the card updates + badge changes
                setTimeout(() => window.location.reload(), 1500);
            } else {
                showAlert(data.message || 'Promotion failed.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        } catch (error) {
            console.error('Promote error:', error);
            showAlert('An error occurred during promotion.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    }

    // ═══════════════════════════════════════════════════════════
    // MANAGE COLLEGE PROGRAM
    // ═══════════════════════════════════════════════════════════

    let currentCollegeProgram = null;

    function openManageCollegeModal() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;

        // Find the card and its college program data
        const card = document.querySelector(`.alumni-card[data-id="${id}"]`);
        const collegeProgram = card?.dataset.collegeProgram;

        if (!collegeProgram) {
            showAlert('No College program found for this alumnus.', 'warning');
            return;
        }

        currentCollegeProgram = JSON.parse(collegeProgram);

        document.getElementById('manageCollegeAlumniId').value = id;
        document.getElementById('manageCollegeProgramId').value = currentCollegeProgram.id;
        document.getElementById('manageCollegeName').textContent = name;
        document.getElementById('manageCollegeProgramName').textContent = currentCollegeProgram.program_name || '—';
        document.getElementById('manageCollegeStudentId').textContent = currentCollegeProgram.student_id_number || '—';

        selectManageCollegeStatus(currentCollegeProgram.enrollment_status || 'enrolled');

        // Close manage modal, show this modal
        hideManageModal();

        const modal = document.getElementById('manageCollegeModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideManageCollegeModal() {
        const modal = document.getElementById('manageCollegeModal');
        if (!modal) return;
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
        currentCollegeProgram = null;
    }

    function selectManageCollegeStatus(status) {
        const toggle = document.getElementById('manage-college-status-toggle');
        const input = document.getElementById('manageCollegeStatusInput');
        const help = document.getElementById('manageCollegeHelp');
        if (!toggle || !input) return;

        toggle.querySelectorAll('.type-option').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.status === status);
        });
        input.value = status;

        const messages = {
            enrolled: 'Still studying College at NU Lipa. Alumnus stays SHS until College graduation.',
            graduated: 'Has graduated from College. Alumnus becomes a full College alumnus.',
            dropped: 'Left NU Lipa College before graduating. Alumnus stays SHS.',
        };
        if (help) help.textContent = messages[status] || '';
    }

    async function executeManageCollege(event) {
        event.preventDefault();

        const alumniId = document.getElementById('manageCollegeAlumniId').value;
        const programId = document.getElementById('manageCollegeProgramId').value;
        const newStatus = document.getElementById('manageCollegeStatusInput').value;

        const submitBtn = document.getElementById('manageCollegeSubmitBtn');
        const originalHTML = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';

        try {
            const response = await fetch(`/admin/alumni/${alumniId}/programs/${programId}/enrollment`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ enrollment_status: newStatus })
            });

            const data = await response.json();

            if (response.ok && data.success) {
                showAlert(data.message || 'Enrollment status updated!', 'success');
                hideManageCollegeModal();
                setTimeout(() => window.location.reload(), 1200);
            } else {
                showAlert(data.message || 'Update failed.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHTML;
            }
        } catch (error) {
            console.error('Manage college error:', error);
            showAlert('An error occurred while updating.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    }

    // ═══════════════════════════════════════════════════════════
    // SHOW/HIDE PROMOTE BUTTON BASED ON ALUMNI TYPE
    // ═══════════════════════════════════════════════════════════

    // Extend the existing openManageModal to show/hide actions based on alumni state
    const _originalOpenManage = window.openManageModal;
    window.openManageModal = function(id, name, status, alumniType) {
        _originalOpenManage.call(this, id, name, status);

        const card = document.querySelector(`.alumni-card[data-id="${id}"]`);
        const collegeProgramJson = card?.dataset.collegeProgram;
        const hasCollegeProgram = collegeProgramJson && collegeProgramJson.trim() !== '';

        // Show "Add College Program" ONLY for SHS alumni who DON'T already have a college program
        const promoteItem = document.getElementById('promoteActionItem');
        if (promoteItem) {
            promoteItem.style.display = (alumniType === 'shs' && !hasCollegeProgram) ? 'flex' : 'none';
        }

        // Show "Manage College Enrollment" ONLY if they already have a college program
        const manageCollegeItem = document.getElementById('manageCollegeActionItem');
        if (manageCollegeItem) {
            manageCollegeItem.style.display = hasCollegeProgram ? 'flex' : 'none';
        }
    };

    function hideManageModal() {
        const modal = document.getElementById('manageModal');
        if (modal) {
            // Add closing class for exit animation
            modal.classList.add('closing');
            
            // Wait for animation to complete before removing
            setTimeout(() => {
                modal.classList.remove('active');
                modal.classList.remove('closing');
                modal.setAttribute('inert', '');
                document.body.style.overflow = '';
                currentManageAlumniId = null;
            }, 300); // Match the animation duration (0.3s)
        }
    }

    function updateRestrictButton(status) {
        const isRestricted = status == 0;
        const restrictTitle = document.getElementById('restrictTitle');
        const restrictDesc = document.getElementById('restrictDesc');
        const restrictBtn = document.getElementById('manageRestrictBtn');
        const restrictIcon = document.getElementById('restrictActionIcon');
        
        if (isRestricted) {
            restrictTitle.textContent = 'Unrestrict Account';
            restrictDesc.textContent = 'Restore access for this alumnus.';
            restrictBtn.innerHTML = '<i class="fa-solid fa-unlock"></i> Unrestrict';
            restrictBtn.className = 'btn btn-success';
            restrictIcon.innerHTML = '<i class="fa-solid fa-user-check"></i>';
        } else {
            restrictTitle.textContent = 'Restrict Account';
            restrictDesc.textContent = 'Temporarily suspend access for this alumnus.';
            restrictBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Restrict';
            restrictBtn.className = 'btn btn-warning';
            restrictIcon.innerHTML = '<i class="fa-solid fa-user-slash"></i>';
        }
    }

    // ========================================
    // ARCHIVE ACCOUNT - AJAX VERSION
    // ========================================
    function prepareArchive() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;
        
        document.getElementById('archiveConfirmName').textContent = name;
        hideManageModal();
        
        const modal = document.getElementById('archiveConfirmModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideArchiveConfirm() {
        const modal = document.getElementById('archiveConfirmModal');
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
    }

    async function executeArchive() {
        const id = document.getElementById('manageAlumniId').value;
        
        try {
            const response = await fetch(`/admin/alumni/${id}/archive`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            hideArchiveConfirm();
            
            if (data.success) {
                showAlert(data.message, 'success');
                // Remove the card from the DOM
                removeAlumniCard(id);
                // Update stats
                updateDirectoryStats();
                // Update both badges
                updateArchivedBadge();
                updateRestrictedBadge();
            } else {
                showAlert(data.message || 'Failed to archive account.', 'error');
            }
        } catch (error) {
            console.error('Archive error:', error);
            hideArchiveConfirm();
            showAlert('An error occurred while archiving the account.', 'error');
        }
    }

    // ========================================
    // RESET PASSWORD - AJAX VERSION
    // ========================================
    function prepareResetPassword() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;
        
        document.getElementById('resetConfirmName').textContent = name;
        hideManageModal();
        
        const modal = document.getElementById('resetPasswordConfirmModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideResetPasswordConfirm() {
        const modal = document.getElementById('resetPasswordConfirmModal');
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
    }

    async function executeResetPassword() {
        const id = document.getElementById('manageAlumniId').value;
        
        try {
            const response = await fetch(`/admin/alumni/${id}/reset-password`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            hideResetPasswordConfirm();
            
            if (data.success) {
                showAlert(data.message, 'success');
            } else {
                showAlert(data.message || 'Failed to reset password.', 'error');
            }
        } catch (error) {
            console.error('Reset password error:', error);
            hideResetPasswordConfirm();
            showAlert('An error occurred while resetting the password.', 'error');
        }
    }

    // ========================================
    // DELETE CONFIRMATION
    // ========================================
    let pendingDeleteId = null;

    function prepareDelete() {
        const id = document.getElementById('manageAlumniId').value;
        const name = document.getElementById('manageAlumniName').textContent;
        
        pendingDeleteId = id;
        document.getElementById('confirmAlumniName').textContent = name;
        
        hideManageModal();
        
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.add('active');
        modal.removeAttribute('inert');
        document.body.style.overflow = 'hidden';
    }

    function hideDeleteConfirm() {
        const modal = document.getElementById('deleteConfirmModal');
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
        pendingDeleteId = null;
    }

    async function executeDelete() {
        if (!pendingDeleteId) return;

        const formData = new FormData();
        formData.append('_method', 'DELETE');
        formData.append('_token', '{{ csrf_token() }}');

        try {
            const response = await fetch(`/admin/alumni/${pendingDeleteId}`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                hideDeleteConfirm();
                showAlert('Alumni account deleted successfully.', 'success');
                // Remove the card from the DOM
                removeAlumniCard(pendingDeleteId);
                // Update stats
                updateDirectoryStats();
                // Update both badges (just in case)
                updateArchivedBadge();
                updateRestrictedBadge();
            } else {
                const data = await response.json().catch(() => null);
                showAlert(data?.message || 'Failed to delete account.', 'error');
                hideDeleteConfirm();
            }
        } catch (error) {
            console.error('Delete error:', error);
            showAlert('An error occurred.', 'error');
            hideDeleteConfirm();
        }
    }

    // ========================================
    // DOM MANIPULATION HELPERS
    // ========================================

    function updateDirectoryStats() {
        const totalCards = document.querySelectorAll('.alumni-card').length;
        const statValues = document.querySelectorAll('.stat-value');
        if (statValues.length > 0) {
            statValues[0].textContent = totalCards;
        }
        
        // Update results count
        const resultsCount = document.getElementById('resultsCount');
        if (resultsCount) {
            resultsCount.textContent = `${totalCards} result${totalCards != 1 ? 's' : ''}`;
        }
    }

    // ========================================
    // ENHANCED BULK IMPORT WITH PREVIEW
    // ========================================

    let importedData = [];
    let currentPage = 1;
    let pageSize = 25;
    let columnMapping = {};
    let validationResults = {};
    let fileHeaders = [];

    function downloadTemplate() {
        const headers = [
            'Student ID', 'First Name', 'Middle Name', 'Last Name',
            'Email', 'Phone Number', 'Program', 'Graduation Year',
            'Date of Birth', 'Sex'
        ];
        
        const sample = [
            '2020-00001', 'Jane', 'Dela', 'Cruz',
            'jane.cruz@email.com', '09123456789', 'BS Computer Science',
            '2024-06-01', '2000-01-15', 'Female'
        ];
        
        const BOM = '\uFEFF';
        const csvContent = BOM + headers.join(',') + '\n' + sample.join(',') + '\n';
        
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8' });
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = 'alumni_import_template.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(link.href);
        
        showAlert('Template downloaded successfully!', 'success');
    }

    async function previewFile() {
        const fileInput = document.getElementById('bulkImportFile');
        const file = fileInput?.files[0];
        const status = document.getElementById('bulkImportStatus');
        
        if (!file) {
            showAlert('Please select a file first.', 'error');
            return;
        }

        status.textContent = 'Reading file...';
        status.style.color = 'var(--warning)';

        try {
            // Parse with SheetJS (works for CSV and Excel)
            const buffer = await file.arrayBuffer();
            const workbook = XLSX.read(buffer, { type: 'array', cellDates: true });
            const sheet = workbook.Sheets[workbook.SheetNames[0]];
            const rawData = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false, defval: '' });
            
            if (!rawData || rawData.length < 2) {
                status.textContent = 'Error: File is empty or contains only headers.';
                status.style.color = 'var(--danger)';
                return;
            }

            // Find header row
            let headerRowIndex = -1;
            for (let i = 0; i < Math.min(rawData.length, 10); i++) {
                const row = rawData[i] || [];
                const rowString = row.join(' ').toLowerCase();
                const hasStudentId = rowString.includes('student id') || rowString.includes('studentid');
                const hasLastName = rowString.includes('last name') || rowString.includes('lastname');
                const hasFirstName = rowString.includes('first name') || rowString.includes('firstname');
                const hasEmail = rowString.includes('email') || rowString.includes('e-mail');
                
                if (hasStudentId && hasLastName && hasFirstName && hasEmail) {
                    headerRowIndex = i;
                    break;
                }
            }

            if (headerRowIndex === -1) {
                status.textContent = 'Error: Could not find required headers.';
                status.style.color = 'var(--danger)';
                return;
            }

            const headers = rawData[headerRowIndex].map(h => String(h || '').trim());
            const dataRows = [];
            
            for (let i = headerRowIndex + 1; i < rawData.length; i++) {
                const row = rawData[i] || [];
                if (row.every(cell => !cell || String(cell).trim() === '')) continue;
                
                const rowData = {};
                headers.forEach((header, index) => {
                    const value = row[index] || '';
                    rowData[header] = typeof value === 'string' ? value.trim() : String(value).trim();
                });
                dataRows.push(rowData);
            }

            if (dataRows.length === 0) {
                status.textContent = 'Error: No data rows found after headers.';
                status.style.color = 'var(--danger)';
                return;
            }

            // ✅ ADD DEFAULT "Year Graduated" COLUMN IF MISSING
            const yearGraduatedExists = headers.some(h => 
                h.toLowerCase().includes('year graduated') || 
                h.toLowerCase().includes('graduation year') ||
                h.toLowerCase().includes('grad year')
            );

            if (!yearGraduatedExists) {
                // Add the column to headers
                headers.push('Year Graduated');
                
                // Add empty values for all rows
                dataRows.forEach(row => {
                    row['Year Graduated'] = '';
                });
                
                // Show a notification
                console.log('✅ Added missing "Year Graduated" column');
            }

            // 👇 SEND TO SERVER FOR VALIDATION
            status.textContent = 'Validating data...';
            status.style.color = 'var(--warning)';

            const validationResponse = await fetch('/admin/alumni/preview-bulk-data', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    headers: headers,
                    rows: dataRows,
                    _token: getCsrfToken()
                })
            });

            const validationResult = await validationResponse.json();

            if (!validationResult.success) {
                status.textContent = 'Validation error: ' + (validationResult.message || 'Unknown error');
                status.style.color = 'var(--danger)';
                return;
            }

            importedData = dataRows;
            fileHeaders = headers;
            // Store validation results from server
            if (validationResult.validation) {
                validationResults = validationResult.validation;
            }
            
            document.getElementById('uploadStep').style.display = 'none';
            document.getElementById('previewStep').style.display = 'block';
            document.getElementById('bulkImportStatus').textContent = `Loaded ${dataRows.length} records for preview.`;
            document.getElementById('bulkImportStatus').style.color = 'var(--success)';
            
            renderPreviewTable(dataRows);
            renderColumnMapping(headers);
            
            // Re-validate on frontend (will use server results if available)
            validateAllRows();
            
        } catch (error) {
            console.error('Preview error:', error);
            status.textContent = 'Error reading file: ' + error.message;
            status.style.color = 'var(--danger)';
        }
    }

    // ========================================
// FILE PREVIEW - SHOW MINI TABLE ON UPLOAD
// ========================================

let filePreviewData = [];
let filePreviewHeaders = [];
let filePreviewFullData = [];
let isPreviewExpanded = false;

// Modify the file input change event to auto-preview
document.addEventListener('DOMContentLoaded', function() {
    const fileInput = document.getElementById('bulkImportFile');
    const previewBtn = document.getElementById('previewBtn');
    const status = document.getElementById('bulkImportStatus');
    
    if (fileInput) {
        fileInput.addEventListener('change', function(e) {
            if (e.target.files[0]) {
                const fileName = e.target.files[0].name;
                if (status) {
                    status.textContent = `Selected: ${fileName}`;
                    status.style.color = 'var(--success)';
                }
                if (previewBtn) {
                    previewBtn.disabled = false;
                }
                
                // ✅ Auto-show file preview
                autoPreviewFile(e.target.files[0]);
            } else {
                // Hide preview if no file
                document.getElementById('filePreviewContainer').style.display = 'none';
                if (previewBtn) {
                    previewBtn.disabled = true;
                }
            }
        });
    }
});

async function autoPreviewFile(file) {
    try {
        const buffer = await file.arrayBuffer();
        const workbook = XLSX.read(buffer, { type: 'array', cellDates: true });
        const sheet = workbook.Sheets[workbook.SheetNames[0]];
        const rawData = XLSX.utils.sheet_to_json(sheet, { header: 1, raw: false, defval: '' });
        
        if (!rawData || rawData.length < 2) {
            console.log('File is empty or contains only headers.');
            return;
        }

        // Find header row
        let headerRowIndex = -1;
        for (let i = 0; i < Math.min(rawData.length, 10); i++) {
            const row = rawData[i] || [];
            const rowString = row.join(' ').toLowerCase();
            const hasStudentId = rowString.includes('student id') || rowString.includes('studentid');
            const hasLastName = rowString.includes('last name') || rowString.includes('lastname');
            const hasFirstName = rowString.includes('first name') || rowString.includes('firstname');
            const hasEmail = rowString.includes('email') || rowString.includes('e-mail');
            
            if (hasStudentId && hasLastName && hasFirstName && hasEmail) {
                headerRowIndex = i;
                break;
            }
        }

        if (headerRowIndex === -1) {
            // Try using first row as header
            headerRowIndex = 0;
        }

        const headers = rawData[headerRowIndex].map(h => String(h || '').trim());
        const dataRows = [];
        
        for (let i = headerRowIndex + 1; i < rawData.length; i++) {
            const row = rawData[i] || [];
            if (row.every(cell => !cell || String(cell).trim() === '')) continue;
            
            const rowData = {};
            headers.forEach((header, index) => {
                const value = row[index] || '';
                rowData[header] = typeof value === 'string' ? value.trim() : String(value).trim();
            });
            dataRows.push(rowData);
        }

        if (dataRows.length === 0) {
            console.log('No data rows found.');
            return;
        }

        // Store preview data
        filePreviewHeaders = headers;
        filePreviewFullData = dataRows;
        filePreviewData = dataRows.slice(0, 5); // First 5 rows for preview
        
        // Show the preview container
        const container = document.getElementById('filePreviewContainer');
        container.style.display = 'block';
        
        // Update file info
        document.getElementById('previewFileName').textContent = file.name;
        document.getElementById('previewRowCount').textContent = dataRows.length;
        document.getElementById('previewColumnCount').textContent = headers.length;
        document.getElementById('previewShowingRows').textContent = Math.min(5, dataRows.length);
        
        // Show/hide full preview button
        const fullBtn = document.getElementById('showFullPreviewBtn');
        if (dataRows.length > 5) {
            fullBtn.style.display = 'inline-flex';
        } else {
            fullBtn.style.display = 'none';
        }
        
        // Render the preview table
        renderFilePreviewTable(filePreviewData);
        
        // Update status
        const status = document.getElementById('bulkImportStatus');
        status.textContent = `📄 Loaded ${dataRows.length} records. Click "Preview Data" to edit and import.`;
        status.style.color = 'var(--success)';
        
    } catch (error) {
        console.error('Auto-preview error:', error);
        // Don't show error, just hide preview
        document.getElementById('filePreviewContainer').style.display = 'none';
    }
}

function renderFilePreviewTable(data) {
    const table = document.getElementById('filePreviewTable');
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    
    thead.innerHTML = '';
    tbody.innerHTML = '';
    
    // Headers
    const headerRow = document.createElement('tr');
    const headers = filePreviewHeaders;
    
    // Row number column
    const numTh = document.createElement('th');
    numTh.textContent = '#';
    numTh.style.cssText = 'padding: 0.5rem; text-align: center; font-weight: 600; color: var(--gray-600); background: var(--gray-100); position: sticky; left: 0; z-index: 2; min-width: 40px;';
    headerRow.appendChild(numTh);
    
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        th.style.cssText = 'padding: 0.5rem 0.75rem; text-align: left; font-weight: 600; color: var(--gray-600); background: var(--gray-100); white-space: nowrap; min-width: 120px;';
        
        // Check if required
        const requiredFields = ['Student ID', 'First Name', 'Last Name', 'Email', 'Program'];
        const isRequired = requiredFields.some(f => header.toLowerCase().includes(f.toLowerCase()));
        if (isRequired) {
            const star = document.createElement('span');
            star.textContent = ' *';
            star.style.color = 'var(--danger)';
            th.appendChild(star);
        }
        
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    
    // Data rows
    data.forEach((row, index) => {
        const tr = document.createElement('tr');
        tr.style.transition = 'background 0.2s';
        tr.onmouseenter = function() { this.style.background = 'var(--gray-50)'; };
        tr.onmouseleave = function() { this.style.background = ''; };
        
        // Row number
        const numTd = document.createElement('td');
        numTd.textContent = index + 1;
        numTd.style.cssText = 'padding: 0.5rem; text-align: center; color: var(--gray-500); font-weight: 500; position: sticky; left: 0; z-index: 1; background: var(--white);';
        tr.appendChild(numTd);
        
        headers.forEach(header => {
            const td = document.createElement('td');
            const value = row[header] || '';
            td.textContent = value;
            td.style.cssText = 'padding: 0.5rem 0.75rem; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;';
            
            // Highlight empty required fields
            const requiredFields = ['Student ID', 'First Name', 'Last Name', 'Email', 'Program'];
            const isRequired = requiredFields.some(f => header.toLowerCase().includes(f.toLowerCase()));
            if (isRequired && !value.trim()) {
                td.style.background = 'var(--danger-light)';
                td.style.color = 'var(--danger)';
                td.title = 'Required field is empty';
            }
            
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
}

function toggleFilePreview() {
    const body = document.getElementById('filePreviewBody');
    const toggleText = document.getElementById('previewToggleText');
    const isHidden = body.style.display === 'none';
    
    if (isHidden) {
        body.style.display = 'block';
        toggleText.textContent = 'Collapse';
    } else {
        body.style.display = 'none';
        toggleText.textContent = 'Expand';
    }
}

function showFullPreview() {
    filePreviewData = filePreviewFullData;
    renderFilePreviewTable(filePreviewData);
    document.getElementById('previewShowingRows').textContent = filePreviewData.length;
    document.getElementById('showFullPreviewBtn').style.display = 'none';
    document.getElementById('showFirstRowsBtn').style.display = 'inline-flex';
}

function showFirstRowsPreview() {
    filePreviewData = filePreviewFullData.slice(0, 5);
    renderFilePreviewTable(filePreviewData);
    document.getElementById('previewShowingRows').textContent = Math.min(5, filePreviewFullData.length);
    document.getElementById('showFullPreviewBtn').style.display = 'inline-flex';
    document.getElementById('showFirstRowsBtn').style.display = 'none';
}

function renderPreviewTable(data) {
    const table = document.getElementById('previewTable');
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    
    thead.innerHTML = '';
    tbody.innerHTML = '';
    
    // ✅ Define required fields
    const requiredFields = ['student_id_number', 'first_name', 'last_name', 'email', 'program', 'year_graduated'];
    
    // ✅ Map display names to field names for required check
    const fieldNameMap = {
        'Student ID': 'student_id_number',
        'First Name': 'first_name',
        'Last Name': 'last_name',
        'Email': 'email',
        'Program': 'program',
        'Year Graduated': 'year_graduated',
        'Graduation Year': 'year_graduated',
        'Grad Year': 'year_graduated',
    };
    
    const columnsToShow = fileHeaders;
    
    // Create header row with column management
    const headerRow = document.createElement('tr');
    
    // ✅ NEW: Actions column (will show delete button on hover)
    const actionsTh = document.createElement('th');
    actionsTh.textContent = '';
    actionsTh.style.width = '40px';
    actionsTh.style.minWidth = '40px';
    actionsTh.style.maxWidth = '40px';
    actionsTh.style.position = 'sticky';
    actionsTh.style.left = '0';
    actionsTh.style.zIndex = '11';
    actionsTh.style.background = 'var(--nu-blue)';
    headerRow.appendChild(actionsTh);
    
    // # column
    const numTh = document.createElement('th');
    numTh.textContent = '#';
    numTh.style.width = '40px';
    numTh.style.minWidth = '40px';
    numTh.style.maxWidth = '40px';
    numTh.style.textAlign = 'center';
    numTh.style.position = 'sticky';
    numTh.style.left = '40px';
    numTh.style.zIndex = '11';
    numTh.style.background = 'var(--nu-blue)';
    headerRow.appendChild(numTh);
    
    // Status column
    const statusTh = document.createElement('th');
    statusTh.textContent = 'Status';
    statusTh.style.width = '60px';
    statusTh.style.minWidth = '60px';
    statusTh.style.maxWidth = '60px';
    statusTh.style.textAlign = 'center';
    statusTh.style.position = 'sticky';
    statusTh.style.left = '80px';
    statusTh.style.zIndex = '11';
    statusTh.style.background = 'var(--nu-blue)';
    headerRow.appendChild(statusTh);

    // ========================================
    // CARD PHOTO COLUMN (live preview per row)
    // ========================================
    const photoTh = document.createElement('th');
    photoTh.textContent = 'Card Photo';
    photoTh.style.width = '90px';
    photoTh.style.minWidth = '90px';
    photoTh.style.maxWidth = '90px';
    photoTh.style.textAlign = 'center';
    photoTh.style.position = 'sticky';
    photoTh.style.left = '140px';
    photoTh.style.zIndex = '11';
    photoTh.style.background = 'var(--nu-blue)';
    headerRow.appendChild(photoTh);
    
    // Data columns
    columnsToShow.forEach((col) => {
        const th = document.createElement('th');
        
        const fieldKey = fieldNameMap[col] || col.toLowerCase().replace(/ /g, '_');
        const isRequired = requiredFields.includes(fieldKey);
        
        th.textContent = col;  // Plain text
        if (isRequired) {
            const star = document.createElement('span');
            star.textContent = ' *';
            star.style.color = '#ff6b6b';
            star.style.fontWeight = '700';
            th.appendChild(star);
        }
        
        th.dataset.column = col;
        th.style.minWidth = '150px';
        th.style.maxWidth = '300px';
        th.style.position = 'sticky';
        th.style.top = '0';
        th.style.background = 'var(--nu-blue)';
        th.style.zIndex = '10';
        th.style.color = 'white';
        th.style.fontWeight = '600';
        
        // ✅ Add hint for Year Graduated
        if (col.toLowerCase().includes('year graduated') || 
            col.toLowerCase().includes('graduation year') ||
            col.toLowerCase().includes('grad year')) {
            const hint = document.createElement('div');
            hint.textContent = '📅 Format: YYYY-MM-DD (e.g., 2027-01-01)';
            hint.style.cssText = `
                font-size: 0.65rem;
                font-weight: 400;
                color: rgba(255,255,255,0.8);
                margin-top: 2px;
                font-style: italic;
            `;
            th.appendChild(hint);
        }
        
        // ✅ Add hint for Program
        if (col.toLowerCase().includes('program') || col.toLowerCase().includes('strand')) {
            const hint = document.createElement('div');
            hint.textContent = '📚 e.g., BSA, BSCE, BSBA-FM';
            hint.style.cssText = `
                font-size: 0.65rem;
                font-weight: 400;
                color: rgba(255,255,255,0.8);
                margin-top: 2px;
                font-style: italic;
            `;
            th.appendChild(hint);
        }
        
        // ✅ Add hint for Email
        if (col.toLowerCase().includes('email') || col.toLowerCase().includes('e-mail')) {
            const hint = document.createElement('div');
            hint.textContent = '✉️ e.g., name@example.com';
            hint.style.cssText = `
                font-size: 0.65rem;
                font-weight: 400;
                color: rgba(255,255,255,0.8);
                margin-top: 2px;
                font-style: italic;
            `;
            th.appendChild(hint);
        }
        
        // Delete column button (only for custom columns)
        if (!isRequired) {
            const deleteBtn = document.createElement('button');
            deleteBtn.type = 'button';     
            deleteBtn.innerHTML = '×';
            deleteBtn.style.cssText = `
                margin-left: 8px;
                background: rgba(255,255,255,0.2);
                border: none;
                color: white;
                border-radius: 50%;
                width: 20px;
                height: 20px;
                cursor: pointer;
                font-size: 14px;
                line-height: 20px;
                text-align: center;
                transition: background 0.2s;
            `;
            deleteBtn.title = 'Delete column "' + String(col).replace(/"/g, '') + '"';
            deleteBtn.setAttribute('aria-label', 'Delete column ' + col);

            deleteBtn.onmouseover = function() { this.style.background = 'rgba(255,0,0,0.5)'; };
            deleteBtn.onmouseout = function() { this.style.background = 'rgba(255,255,255,0.2)'; };
            deleteBtn.onclick = function(e) {
                e.stopPropagation();
                deleteColumn(col);
            };
            th.appendChild(deleteBtn);
        }
        
        headerRow.appendChild(th);
    });
    
    thead.appendChild(headerRow);
    
    const total = data.length;
    const totalPages = Math.ceil(total / pageSize);
    
    if (currentPage > totalPages) currentPage = totalPages;
    if (currentPage < 1) currentPage = 1;
    
    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);
    const pageData = data.slice(start, end);
    
    pageData.forEach((row, index) => {
        const actualIndex = start + index;
        const tr = document.createElement('tr');
        tr.dataset.rowIndex = actualIndex;
        tr.id = `row-${actualIndex}`;
        tr.className = 'preview-row';
        
        // ✅ Hover effect: position relative for absolute positioning
        tr.style.position = 'relative';
        
        // ✅ DELETE BUTTON - appears on hover (left side, inside the sticky cell)
        const actionsTd = document.createElement('td');
        actionsTd.style.position = 'sticky';
        actionsTd.style.left = '0';
        actionsTd.style.zIndex = '6';
        actionsTd.style.background = 'var(--white)';
        actionsTd.style.textAlign = 'center';
        actionsTd.style.verticalAlign = 'middle';
        actionsTd.style.padding = '4px 0';
        actionsTd.style.minWidth = '40px';
        actionsTd.style.width = '40px';
        actionsTd.style.maxWidth = '40px';
        
        // Hidden by default, shows on row hover
        const deleteRowBtn = document.createElement('button');
        deleteRowBtn.className = 'row-delete-btn';
        deleteRowBtn.innerHTML = '×';
        deleteRowBtn.title = 'Delete this row';
        deleteRowBtn.setAttribute('aria-label', `Delete row ${actualIndex + 1}`);
        deleteRowBtn.setAttribute('type', 'button');
        deleteRowBtn.style.cssText = `
            display: none;
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #dc2626;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            line-height: 20px;
            text-align: center;
            transition: all 0.2s;
            padding: 0;
            margin: 0 auto;
        `;
        deleteRowBtn.onmouseover = function() { 
            this.style.background = '#dc2626';
            this.style.color = 'white';
            this.style.transform = 'scale(1.1)';
        };
        deleteRowBtn.onmouseout = function() { 
            this.style.background = '#fee2e2';
            this.style.color = '#dc2626';
            this.style.transform = 'scale(1)';
        };
        deleteRowBtn.onclick = function(e) {
            e.stopPropagation();
            deleteRow(actualIndex);
        };
        actionsTd.appendChild(deleteRowBtn);
        tr.appendChild(actionsTd);
        
        // Row number
        const numTd = document.createElement('td');
        numTd.className = 'row-number';
        numTd.textContent = actualIndex + 1;
        numTd.style.position = 'sticky';
        numTd.style.left = '40px';
        numTd.style.zIndex = '5';
        numTd.style.background = 'var(--white)';
        numTd.style.textAlign = 'center';
        numTd.style.minWidth = '40px';
        numTd.style.width = '40px';
        numTd.style.maxWidth = '40px';
        tr.appendChild(numTd);
        
        // Status
        const statusTd = document.createElement('td');
        statusTd.className = 'row-status';
        statusTd.id = `status-${actualIndex}`;
        statusTd.style.position = 'sticky';
        statusTd.style.left = '80px';
        statusTd.style.zIndex = '5';
        statusTd.style.background = 'var(--white)';
        statusTd.style.textAlign = 'center';
        statusTd.style.minWidth = '60px';
        statusTd.style.width = '60px';
        statusTd.style.maxWidth = '60px';
        statusTd.innerHTML = '<i class="fa-regular fa-hourglass-half" style="color: var(--gray-400);"></i>';
        tr.appendChild(statusTd);

        // ========================================
        // CARD PHOTO CELL - preview matched photo
        // ========================================
        const photoTd = document.createElement('td');
        photoTd.className = 'row-photo';
        photoTd.id = `photo-${actualIndex}`;
        photoTd.style.position = 'sticky';
        photoTd.style.left = '140px';
        photoTd.style.zIndex = '5';
        photoTd.style.background = 'var(--white)';
        photoTd.style.textAlign = 'center';
        photoTd.style.minWidth = '90px';
        photoTd.style.width = '90px';
        photoTd.style.maxWidth = '90px';
        photoTd.style.padding = '4px';
        photoTd.innerHTML = '<i class="fa-regular fa-image" style="color: var(--gray-300);"></i>';
        tr.appendChild(photoTd);
        
        columnsToShow.forEach(col => {
            const td = document.createElement('td');
            td.style.position = 'relative';
            
            const input = document.createElement('input');
            input.type = 'text';
            input.value = row[col] || '';
            input.dataset.column = col;
            input.dataset.rowIndex = actualIndex;
            input.className = 'editable-cell';
            input.placeholder = 'Enter value...';
            input.id = `cell-${actualIndex}-${col}`;
            input.style.width = '100%';
            input.style.minWidth = '120px';
            input.style.paddingRight = '30px';
            
            input.addEventListener('input', function() {
                handleCellEdit(this, actualIndex, col);
            });
            
            input.addEventListener('blur', function() {
                validateSingleRow(actualIndex);
            });
            
            const applyBtn = document.createElement('button');
            applyBtn.type = 'button';                    // ← Add this too
            applyBtn.innerHTML = '⬇';                    // ← ADD THIS
            applyBtn.title = 'Apply this value to all rows in column "' + 
                String(col).replace(/"/g, '') + '"';
            applyBtn.setAttribute('aria-label', 'Apply this value to all rows in column ' + col);
                        
            applyBtn.style.cssText = `
                position: absolute;
                right: 4px;
                top: 50%;
                transform: translateY(-50%);
                background: var(--gray-200);
                border: none;
                border-radius: 4px;
                padding: 2px 4px;
                cursor: pointer;
                font-size: 12px;
                color: var(--gray-600);
                transition: all 0.2s;
                opacity: 0.6;
            `;
            applyBtn.onmouseover = function() { 
                this.style.opacity = '1'; 
                this.style.background = 'var(--nu-blue)';
                this.style.color = 'white';
            };
            applyBtn.onmouseout = function() { 
                this.style.opacity = '0.6'; 
                this.style.background = 'var(--gray-200)';
                this.style.color = 'var(--gray-600)';
            };
            applyBtn.onclick = function(e) {
                e.stopPropagation();
                applyCellValueToAll(actualIndex, col);
            };
            
            td.appendChild(input);
            td.appendChild(applyBtn);
            tr.appendChild(td);
        });
        
        tbody.appendChild(tr);
    });
    requestAnimationFrame(() => matchPhotosToRows());
    
    // Update record count
    let countText = `Showing ${start + 1}-${end} of ${total} records`;
    if (totalPages > 1) {
        countText += ` (Page ${currentPage} of ${totalPages})`;
    }
    document.getElementById('recordCount').textContent = countText;
    
    renderPaginationControls();
}

// ========================================
// DELETE ROW FROM BULK IMPORT
// ========================================

function deleteRow(rowIndex) {
    const rowData = importedData[rowIndex];
    if (!rowData) {
        showAlert('Row not found.', 'error');
        return;
    }

    const studentId = rowData.student_id_number || rowData['Student ID'] || '';
    const firstName = rowData.first_name || rowData['First Name'] || '';
    const lastName = rowData.last_name || rowData['Last Name'] || '';
    const identifier = studentId || `${firstName} ${lastName}`.trim() || `Row ${rowIndex + 1}`;

    // ✅ Declare BEFORE the object
    const safeIdentifier = window.escapeHtml(identifier);

    showWarningModal({
        title: 'Delete Row',
        message: `Are you sure you want to delete <strong>Row ${rowIndex + 1}</strong>?<br>
                  <small>${identifier ? `This row contains data for: <strong>${safeIdentifier}</strong>` : 'This row will be permanently removed from the import list.'}<br>
                  This action cannot be undone.</small>`,
        iconType: 'danger',
        confirmText: 'Delete Row',
        confirmClass: 'btn-danger',
        onConfirm: function() {
            importedData.splice(rowIndex, 1);
            validationResults = {};
            renderPreviewTable(importedData);
            renderColumnMapping(fileHeaders);
            
            setTimeout(() => {
                validateAllRows();
            }, 100);
            
            const total = importedData.length;
            document.getElementById('recordCount').textContent = total === 0 ? 'No records' : `${total} records`;
            showAlert(`Row ${rowIndex + 1} deleted successfully. (${total} rows remaining)`, 'success');
            
            if (total === 0) {
                const tbody = document.querySelector('#previewTable tbody');
                if (tbody) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="${fileHeaders.length + 3}" style="text-align: center; padding: 2rem; color: var(--gray-500);">
                                <i class="fa-regular fa-face-frown" style="font-size: 2rem; display: block; margin-bottom: 0.5rem;"></i>
                                No rows remaining. <a href="#" onclick="backToUpload()" style="color: var(--nu-blue); text-decoration: underline;">Upload a new file</a> or <a href="#" onclick="showAddColumnModal()" style="color: var(--nu-blue); text-decoration: underline;">add a column</a>.
                            </td>
                        </tr>
                    `;
                }
                document.getElementById('confirmImportBtn').disabled = true;
            }
        }
    });
}

// ========================================
// DELETE MULTIPLE ROWS (BULK DELETE)
// ========================================

let selectedRows = new Set();

function toggleRowSelection(rowIndex) {
    const checkbox = document.getElementById(`row-select-${rowIndex}`);
    if (checkbox) {
        if (checkbox.checked) {
            selectedRows.add(rowIndex);
        } else {
            selectedRows.delete(rowIndex);
        }
        updateBulkDeleteUI();
    }
}

function selectAllRows() {
    const total = importedData.length;
    const start = (currentPage - 1) * pageSize;
    const end = Math.min(start + pageSize, total);
    
    for (let i = start; i < end; i++) {
        const checkbox = document.getElementById(`row-select-${i}`);
        if (checkbox) {
            checkbox.checked = true;
            selectedRows.add(i);
        }
    }
    updateBulkDeleteUI();
}

function deselectAllRows() {
    document.querySelectorAll('.row-select-checkbox').forEach(cb => {
        cb.checked = false;
    });
    selectedRows.clear();
    updateBulkDeleteUI();
}

function updateBulkDeleteUI() {
    const count = selectedRows.size;
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');
    
    if (count > 0) {
        bulkDeleteBtn.style.display = 'inline-flex';
        selectedCount.textContent = count;
        selectedCount.style.display = 'inline';
    } else {
        bulkDeleteBtn.style.display = 'none';
        selectedCount.style.display = 'none';
    }
}

function deleteSelectedRows() {
    if (selectedRows.size === 0) {
        showAlert('No rows selected.', 'info');
        return;
    }
    
    const count = selectedRows.size;
    const sortedIndices = Array.from(selectedRows).sort((a, b) => b - a); // Delete from bottom up
    
    showWarningModal({
        title: 'Delete Selected Rows',
        message: `Are you sure you want to delete <strong>${count}</strong> selected row(s)?<br>
                  <small>This action cannot be undone.</small>`,
        iconType: 'danger',
        confirmText: `Delete ${count} Rows`,
        confirmClass: 'btn-danger',
        onConfirm: function() {
            // Delete from bottom up to maintain correct indices
            sortedIndices.forEach(index => {
                importedData.splice(index, 1);
            });
            
            selectedRows.clear();
            
            // Re-validate all rows
            validationResults = {};
            
            // Re-render the table
            renderPreviewTable(importedData);
            renderColumnMapping(fileHeaders);
            
            setTimeout(() => {
                validateAllRows();
            }, 100);
            
            const remaining = importedData.length;
            showAlert(`Deleted ${count} row(s). ${remaining} rows remaining.`, 'success');
            
            if (remaining === 0) {
                document.getElementById('confirmImportBtn').disabled = true;
            }
        }
    });
}

function executeBatchFill(event) {
    event.preventDefault();
    
    const column = document.getElementById('batchColumn').value;
    const startRow = parseInt(document.getElementById('batchStartRow').value);
    const endRow = parseInt(document.getElementById('batchEndRow').value);
    const value = document.getElementById('batchValue').value.trim();
    
    // Validation
    if (!column) {
        showAlert('Please select a column.', 'error');
        return;
    }
    
    if (!value) {
        showAlert('Please enter a value to set.', 'error');
        return;
    }
    
    const totalRows = importedData.length;
    
    if (isNaN(startRow) || startRow < 1 || startRow > totalRows) {
        showAlert(`Start row must be between 1 and ${totalRows}.`, 'error');
        return;
    }
    
    if (isNaN(endRow) || endRow < 1 || endRow > totalRows) {
        showAlert(`End row must be between 1 and ${totalRows}.`, 'error');
        return;
    }
    
    if (startRow > endRow) {
        showAlert('Start row must be less than or equal to End row.', 'error');
        return;
    }
    
    // Check if column exists
    if (!fileHeaders.includes(column)) {
        showAlert(`Column "${column}" does not exist!`, 'error');
        return;
    }
    
    // Calculate rowCount
    const rowCount = endRow - startRow + 1;
    
    const safeColumn = window.escapeHtml(column);
    const safeValue = window.escapeHtml(value);

    showWarningModal({
        title: 'Apply Batch Fill',
        message: `Ready to set "<strong>${safeColumn}</strong>" to "<strong>${safeValue}</strong>" for rows <strong>${startRow}</strong> to <strong>${endRow}</strong> (${rowCount} row(s)).<br>
                <small>This will overwrite existing values in these rows.</small>`,
        iconType: 'info',
        confirmText: 'Apply',
        confirmClass: 'btn-primary',
        onConfirm: function() {
            console.log('✅ Batch fill confirmed! Applying values...');
            
            // Apply the batch fill
            for (let i = startRow - 1; i < endRow; i++) {
                if (i < importedData.length) {
                    importedData[i][column] = value;
                    console.log(`✅ Row ${i+1} set to "${value}"`);
                }
            }
            
            // Re-render the main table
            renderPreviewTable(importedData);
            validateAllRows();
            
            // Close the batch fill modal
            closeBatchFillModal();
            
            showAlert(`✅ Successfully set "${column}" to "${value}" for rows ${startRow}-${endRow} (${rowCount} row(s)).`, 'success');
        }
    });
}

// ========================================
// DELETE COLUMN - ENHANCED
// ========================================

function deleteColumn(columnName) {
    // ✅ Declare BEFORE the object
    const safeName = window.escapeHtml(columnName);

    showWarningModal({
        title: 'Delete Column',
        message: `Are you sure you want to delete column "<strong>${safeName}</strong>"?<br>
                  <small>This action cannot be undone. All data in this column will be permanently removed.</small>`,
        iconType: 'danger',
        confirmText: 'Delete Column',
        confirmClass: 'btn-danger',
        onConfirm: function() {
            const index = fileHeaders.indexOf(columnName);
            if (index > -1) {
                fileHeaders.splice(index, 1);
            }
            
            importedData.forEach(row => {
                delete row[columnName];
            });
            
            renderPreviewTable(importedData);
            renderColumnMapping(fileHeaders);
            validateAllRows();
            
            showAlert('Column "' + columnName + '" deleted.', 'info');
        }
    });
}

    function renderColumnMapping(headers) {
        const container = document.getElementById('mappingControls');
        container.innerHTML = '';
        
        const requiredFields = {
            'student_id_number': 'Student ID *',
            'first_name': 'First Name *',
            'last_name': 'Last Name *',
            'email': 'Email *',
            'program': 'Program *',
            // ✅ ADD THIS - Year Graduated is now required
            'year_graduated': 'Graduation Year *'
        };
        
        const optionalFields = {
            'middle_name': 'Middle Name',
            'phone_number': 'Phone Number',
            'date_of_birth': 'Date of Birth',
            'sex': 'Sex'
        };
        
        const allFields = { ...requiredFields, ...optionalFields };
        
        Object.entries(allFields).forEach(([field, label]) => {
            const div = document.createElement('div');
            div.className = 'mapping-item';
            
            const labelEl = document.createElement('label');
            labelEl.className = 'mapping-label';
            labelEl.textContent = label;
            div.appendChild(labelEl);
            
            const select = document.createElement('select');
            select.id = `map-${field}`;
            select.dataset.field = field;
            
            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = '-- Select Column --';
            select.appendChild(defaultOption);
            
            headers.forEach(header => {
                const option = document.createElement('option');
                option.value = header;
                option.textContent = header;
                
                const headerLower = header.toLowerCase();
                const fieldLower = field.toLowerCase();
                if (headerLower.includes(fieldLower.replace('_', ' ')) || 
                    headerLower === fieldLower ||
                    headerLower.replace(' ', '_') === fieldLower) {
                    option.selected = true;
                }
                
                select.appendChild(option);
            });
            
            select.addEventListener('change', function() {
                columnMapping[this.dataset.field] = this.value;
                renderPreviewTable(importedData);
                validateAllRows();
            });
            
            div.appendChild(select);
            container.appendChild(div);
        });
    }

    function handleCellEdit(input, rowIndex, column) {
        const value = input.value;
        importedData[rowIndex][column] = value;
        
        input.classList.remove('error', 'success');
        const errorMsg = input.parentElement.querySelector('.error-message');
        if (errorMsg) errorMsg.remove();
    }

    function validateSingleRow(rowIndex) {
        const row = importedData[rowIndex];
        if (!row) return;
        
        const errors = [];
        const mappedFields = getFieldMapping();
        
        const required = ['student_id_number', 'first_name', 'last_name', 'email', 'program'];
        required.forEach(field => {
            const col = mappedFields[field];
            if (!col) {
                errors.push(`Missing mapping for ${field}`);
                return;
            }
            const value = row[col] || '';
            if (!value.trim()) {
                errors.push(`${field.replace('_', ' ')} is required`);
            }
        });
        
        if (mappedFields.email && row[mappedFields.email]) {
            const email = row[mappedFields.email];
            if (!email.match(/^[^\s@]+@[^\s@]+\.[^\s@]+$/)) {
                errors.push('Invalid email format');
            }
        }
        
        const statusTd = document.getElementById(`status-${rowIndex}`);
        const tr = statusTd?.closest('tr');
        const inputs = tr?.querySelectorAll('.editable-cell');
        
        if (errors.length > 0) {
            statusTd.innerHTML = '<i class="fa-solid fa-times-circle"></i>';
            tr?.classList.add('error-row');
            tr?.classList.remove('success-row');
            
            inputs?.forEach(input => {
                const col = input.dataset.column;
                const field = findFieldByColumn(col);
                if (field && required.includes(field)) {
                    if (!input.value.trim()) {
                        input.classList.add('error');
                    }
                }
            });
        } else {
            statusTd.innerHTML = '<i class="fa-solid fa-check-circle"></i>';
            tr?.classList.remove('error-row');
            tr?.classList.add('success-row');
            
            inputs?.forEach(input => {
                input.classList.remove('error');
            });
        }
        
        validationResults[rowIndex] = { errors, valid: errors.length === 0 };
    }

    function validateAllRows() {
        const total = importedData.length;
        let validCount = 0;
        let errorCount = 0;
        
        for (let i = 0; i < total; i++) {
            validateSingleRow(i);
            if (validationResults[i]?.valid) {
                validCount++;
            } else {
                errorCount++;
            }
        }
        
        updateValidationSummary(validCount, errorCount, total);
        
        const importBtn = document.getElementById('confirmImportBtn');
        if (errorCount === 0 && validCount > 0) {
            importBtn.disabled = false;
            importBtn.style.opacity = '1';
        } else {
            importBtn.disabled = true;
            importBtn.style.opacity = '0.6';
        }
    }

    function updateValidationSummary(valid, errors, total) {
        const summary = document.getElementById('validationSummary');
        summary.style.display = 'block';
        
        summary.innerHTML = `
            <h5 style="margin: 0 0 0.75rem 0; color: var(--nu-blue);">
                <i class="fa-solid fa-clipboard-check"></i> Validation Summary
            </h5>
            <div class="validation-summary-grid">
                <div class="validation-stat success">
                    <span class="stat-number">${valid}</span>
                    <span class="stat-label">Valid Records</span>
                </div>
                <div class="validation-stat error">
                    <span class="stat-number">${errors}</span>
                    <span class="stat-label">Invalid Records</span>
                </div>
                <div class="validation-stat info">
                    <span class="stat-number">${total}</span>
                    <span class="stat-label">Total Records</span>
                </div>
            </div>
            ${errors > 0 ? `
                <div style="margin-top: 0.75rem; padding: 0.75rem; background: var(--danger-light); border-radius: var(--radius); border-left: 4px solid var(--danger);">
                    <p style="font-size: 0.875rem; color: var(--danger); margin: 0;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        ${errors} record(s) have errors. Please fix them before importing.
                    </p>
                </div>
            ` : `
                <div style="margin-top: 0.75rem; padding: 0.75rem; background: var(--success-light); border-radius: var(--radius); border-left: 4px solid var(--success);">
                    <p style="font-size: 0.875rem; color: var(--success); margin: 0;">
                        <i class="fa-solid fa-check-circle"></i>
                        All ${total} record(s) are valid and ready for import!
                    </p>
                </div>
            `}
        `;
    }

    function getFieldMapping() {
        const mapping = {};
        const allFields = [
            'student_id_number', 'first_name', 'last_name', 'email', 'program',
            'middle_name', 'phone_number', 'year_graduated', 'date_of_birth', 'sex'
        ];
        
        allFields.forEach(field => {
            const select = document.getElementById(`map-${field}`);
            if (select && select.value !== '') {
                mapping[field] = select.value;
            }
        });
        
        return mapping;
    }

    function findFieldByColumn(column) {
        const mapping = getFieldMapping();
        for (const [field, col] of Object.entries(mapping)) {
            if (col === column) return field;
        }
        return null;
    }

    function backToUpload() {
        document.getElementById('uploadStep').style.display = 'block';
        document.getElementById('previewStep').style.display = 'none';
        document.getElementById('bulkImportStatus').textContent = 'No file selected yet.';
        document.getElementById('bulkImportStatus').style.color = 'var(--gray-500)';
        
        // ✅ Reset the file input too
        const fileInput = document.getElementById('bulkImportFile');
        if (fileInput) fileInput.value = '';
        const previewBtn = document.getElementById('previewBtn');
        if (previewBtn) previewBtn.disabled = true;
        
        // ✅ Reset internal state
        importedData = [];
        fileHeaders = [];
        validationResults = {};
        uploadedPhotos = [];
        excludedPhotoKeys.clear();
        document.getElementById('filePreviewContainer').style.display = 'none';
        document.getElementById('unmatchedPhotos').style.display = 'none';
    }

    function renderPaginationControls() {
        const container = document.getElementById('paginationControls');
        if (!container) return;
        
        const total = importedData.length;
        const totalPages = Math.ceil(total / pageSize);
        
        if (pageSize >= total || totalPages <= 1) {
            container.innerHTML = '';
            return;
        }
        
        let html = '';
        
        html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled' : ''}>
            <i class="fa-solid fa-chevron-left"></i>
        </button>`;
        
        const maxVisible = 7;
        let startPage = Math.max(1, currentPage - Math.floor(maxVisible / 2));
        let endPage = Math.min(totalPages, startPage + maxVisible - 1);
        
        if (endPage - startPage < maxVisible - 1) {
            startPage = Math.max(1, endPage - maxVisible + 1);
        }
        
        if (startPage > 1) {
            html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(1)">1</button>`;
            if (startPage > 2) {
                html += `<span style="color: var(--gray-400); padding: 0 0.25rem;">...</span>`;
            }
        }
        
        for (let i = startPage; i <= endPage; i++) {
            html += `<button class="btn btn-sm ${i === currentPage ? 'btn-primary' : 'btn-secondary'}" onclick="goToPage(${i})">
                ${i}
            </button>`;
        }
        
        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                html += `<span style="color: var(--gray-400); padding: 0 0.25rem;">...</span>`;
            }
            html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${totalPages})">${totalPages}</button>`;
        }
        
        html += `<button class="btn btn-sm btn-secondary" onclick="goToPage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled' : ''}>
            <i class="fa-solid fa-chevron-right"></i>
        </button>`;
        
        const start = (currentPage - 1) * pageSize + 1;
        const end = Math.min(currentPage * pageSize, total);
        html += `<span style="font-size: 0.8125rem; color: var(--gray-500); margin-left: 0.5rem;">
            ${start}-${end} of ${total}
        </span>`;
        
        container.innerHTML = html;
    }

    function changePageSize() {
        const select = document.getElementById('pageSizeSelect');
        const value = parseInt(select.value);
        
        if (value === 0) {
            pageSize = importedData.length || 25;
            currentPage = 1;
        } else {
            pageSize = value;
            currentPage = 1;
        }
        
        renderPreviewTable(importedData);
        renderPaginationControls();
        validateAllRows();
    }

    function goToPage(page) {
        const totalPages = Math.ceil(importedData.length / pageSize);
        if (page < 1 || page > totalPages) return;
        currentPage = page;
        renderPreviewTable(importedData);
        renderPaginationControls();
        validateAllRows();
    }

async function validateAndImport() {
    const total = importedData.length;
    let allValid = true;
    let invalidCount = 0;
    
    for (let i = 0; i < total; i++) {
        if (!validationResults[i]?.valid) {
            allValid = false;
            invalidCount++;
        }
    }
    
    if (!allValid) {
        showAlert(`Please fix ${invalidCount} error(s) before importing.`, 'error');
        return;
    }
    
    // Show the modal and wait for user action
    return new Promise((resolve) => {
        showWarningModal({
            title: 'Import Records',
            message: `Ready to import <strong>${total}</strong> alumni records.<br>
                      <small>Please verify the data in the preview table before proceeding.</small>`,
            iconType: 'info',
            confirmText: 'Import All',
            confirmClass: 'btn-primary',
            onConfirm: async function() {
                await performImport();
                resolve();
            }
        });
    });
}

async function performImport() {
    const status = document.getElementById('bulkImportStatus');
    const importBtn = document.getElementById('confirmImportBtn');
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Importing...';

    const mapping = getFieldMapping();

    const requiredFields = ['student_id_number', 'first_name', 'last_name', 'email', 'program'];
    const hasAllRequired = requiredFields.every(field => mapping[field] && mapping[field] !== '');

    if (!hasAllRequired) {
        showAlert('Please map all required fields before importing.', 'error');
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fa-solid fa-check"></i> Import All';
        return;
    }

    // ⚠️ Switch to FormData to support multipart photo upload
    const formData = new FormData();
    formData.append('data', JSON.stringify(importedData));
    formData.append('mapping', JSON.stringify(mapping));
    formData.append('default_alumni_type', document.getElementById('bulk-alumni-type-input')?.value || 'college');
    formData.append('_token', getCsrfToken());

    // Append photos
    uploadedPhotos.forEach((photo, i) => {
        formData.append(`photos[${i}]`, photo.file, photo.name);
    });

    try {
        const response = await fetch('/admin/alumni/process-bulk', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                // ⚠️ Do NOT set Content-Type — browser sets multipart boundary
            },
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            const { results } = result;

            let message = `Import complete! ✅ ${results.successful} successful, ❌ ${results.failed} failed.`;

            if (results.duplicates?.length) {
                message += `\n⚠️ ${results.duplicates.length} duplicates skipped.`;
            }
            if (results.photos_matched) {
                message += `\n🖼️ ${results.photos_matched} card photo(s) linked.`;
            }
            if (results.photos_overridden) {
                message += `\n♻️ ${results.photos_overridden} photo(s) overrode existing.`;
            }
            if (results.warnings?.length) {
                message += `\n⚠️ ${results.warnings.length} warnings.`;
            }
            if (results.errors?.length) {
                const errorList = results.errors.slice(0, 5).join('\n');
                message += `\n\nErrors:\n${errorList}`;
                if (results.errors.length > 5) {
                    message += `\n...and ${results.errors.length - 5} more errors.`;
                }
            }

            status.innerHTML = message.replace(/\n/g, '<br>');
            status.style.color = results.failed > 0 ? 'var(--warning)' : 'var(--success)';

            if (results.successful > 0) {
                showAlert(`Successfully imported ${results.successful} alumni!`, 'success');
                setTimeout(() => window.location.reload(), 2500);
            }
        } else {
            status.textContent = 'Error: ' + (result.message || 'Unknown error.');
            status.style.color = 'var(--danger)';
            showAlert('Import failed: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Import error:', error);
        status.textContent = 'Error: ' + error.message;
        status.style.color = 'var(--danger)';
        showAlert('An error occurred during import.', 'error');
    } finally {
        importBtn.disabled = false;
        importBtn.innerHTML = '<i class="fa-solid fa-check"></i> Import All';
    }
}

    function clearAllErrors() {
        document.querySelectorAll('.error-row').forEach(tr => {
            tr.classList.remove('error-row');
        });
        document.querySelectorAll('.editable-cell.error').forEach(input => {
            input.classList.remove('error');
        });
        showAlert('Error indicators cleared.', 'info');
    }

    function getCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content || '';
    }

    // ========================================
    // EXPORT FUNCTIONALITY
    // ========================================
    function exportAlumni() {
        const exportBtn = document.querySelector('.btn-secondary .fa-download')?.closest('button');
        if (exportBtn) {
            exportBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Exporting...';
            exportBtn.disabled = true;
        }
        
        const link = document.createElement('a');
        link.href = '{{ route('admin.alumni.export') }}';
        link.download = 'alumni_export_' + new Date().toISOString().split('T')[0] + '.csv';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        
        setTimeout(() => {
            if (exportBtn) {
                exportBtn.innerHTML = '<i class="fa-solid fa-download"></i> Export';
                exportBtn.disabled = false;
            }
        }, 2000);
        
        showAlert('Exporting alumni data...', 'info');
    }

    // ========================================
    // REDIRECT TO MESSAGES
    // ========================================
    function redirectToMessages(alumniId, alumniName) {
        sessionStorage.setItem('openChat', JSON.stringify({
            id: alumniId,
            name: alumniName,
            timestamp: Date.now()
        }));
        
        window.location.href = '/admin/messages';
    }

    // ========================================
    // CLOSE MODALS ON OVERLAY CLICK
    // ========================================
    document.getElementById('manageModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideManageModal();
    });

    document.getElementById('archiveConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideArchiveConfirm();
    });

    document.getElementById('resetPasswordConfirmModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideResetPasswordConfirm();
    });

    document.getElementById('restrictReasonModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideRestrictReasonModal();
    });

    // ========================================
    // FILE INPUT AUTO-PREVIEW
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('bulkImportFile');
        const previewBtn = document.getElementById('previewBtn');
        const status = document.getElementById('bulkImportStatus');
        
        if (fileInput) {
            fileInput.addEventListener('change', function(e) {
                if (e.target.files[0]) {
                    const fileName = e.target.files[0].name;
                    if (status) {
                        status.textContent = `Selected: ${fileName}`;
                        status.style.color = 'var(--success)';
                    }
                    if (previewBtn) {
                        previewBtn.disabled = false;
                        setTimeout(() => {
                            previewBtn.click();
                        }, 500);
                    }
                }
            });
        }
    });

    // ========================================
    // HANDLE SUCCESS MESSAGES FROM SERVER
    // ========================================
    document.addEventListener('DOMContentLoaded', function() {
        @if(session('success'))
            showAlert('{{ session('success') }}', 'success');
        @endif
        
        @if(session('error'))
            showAlert('{{ session('error') }}', 'error');
        @endif

        // Add data-id and data-alumni-type attributes to cards
        document.querySelectorAll('.alumni-card').forEach(card => {
            const manageBtn = card.querySelector('.manage-btn');
            if (manageBtn) {
                const id = manageBtn.dataset.id;
                if (id) {
                    card.dataset.id = id;
                }
            }
            
            // Get alumni type from the badge
            const typeBadge = card.querySelector('.alumni-type-badge');
            if (typeBadge) {
                const type = typeBadge.classList.contains('shs') ? 'shs' : 
                            typeBadge.classList.contains('college') ? 'college' : 'unknown';
                card.dataset.alumniType = type;
            }
        });
    });

   // ========================================
    // FIX: OPEN RESTRICT MODAL - SHOW HIDDEN MODAL
    // ========================================

    function openRestrictModal() {
        console.log('🔓 Opening restrict modal');
        
        // Get all required elements safely
        const id = document.getElementById('manageAlumniId')?.value;
        const nameEl = document.getElementById('manageAlumniName');
        const statusEl = document.getElementById('manageAccountStatus');
        
        if (!id || !nameEl) {
            console.error('❌ Required elements not found');
            return;
        }
        
        const name = nameEl.textContent;
        const isRestricted = statusEl ? statusEl.value == 0 : false;
        
        console.log('📊 Account status:', isRestricted ? 'Restricted' : 'Active');
        
        if (isRestricted) {
            prepareUnrestrictFromManage(id, name);
            return;
        }
        
        // Set up for restriction
        const restrictIdEl = document.getElementById('restrictAlumniId');
        const restrictNameEl = document.getElementById('restrictModalName');
        const restrictTitleEl = document.getElementById('restrictModalTitle');
        const restrictActionEl = document.getElementById('restrictAction');
        const restrictBtnTextEl = document.getElementById('restrictBtnText');
        const confirmBtn = document.getElementById('restrictConfirmBtn');
        const reasonEl = document.getElementById('restrictionReason');
        const customReasonEl = document.getElementById('customReason');
        const commentEl = document.getElementById('restrictionComment');
        const customReasonGroup = document.getElementById('customReasonGroup');
        
        // If elements are missing, the modal might have been removed from DOM
        if (!restrictIdEl || !restrictNameEl || !restrictTitleEl || !restrictActionEl || !restrictBtnTextEl || !confirmBtn) {
            console.warn('⚠️ Some modal elements missing, attempting to recover...');
            const modal = document.getElementById('restrictReasonModal');
            if (modal) {
                modal.classList.add('active');
                modal.removeAttribute('inert');
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
                
                if (restrictIdEl) restrictIdEl.value = id;
                if (restrictNameEl) restrictNameEl.textContent = name;
                if (restrictTitleEl) restrictTitleEl.textContent = 'Restrict Account';
                if (restrictActionEl) restrictActionEl.value = 'restrict';
                if (restrictBtnTextEl) restrictBtnTextEl.textContent = 'Restrict Account';
                if (confirmBtn) {
                    confirmBtn.className = 'btn btn-warning';
                    confirmBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Restrict Account';
                }
                // Reset values
                if (reasonEl) { reasonEl.value = ''; reasonEl.disabled = false; }
                if (customReasonEl) customReasonEl.value = '';
                if (commentEl) commentEl.value = '';
                if (customReasonGroup) customReasonGroup.style.display = 'none';
            } else {
                console.error('❌ Modal completely missing from DOM');
            }
            return;
        }
        
        // Elements exist, set values for current alumni
        restrictIdEl.value = id;
        restrictNameEl.textContent = name;
        restrictTitleEl.textContent = 'Restrict Account';
        restrictActionEl.value = 'restrict';
        restrictBtnTextEl.textContent = 'Restrict Account';
        
        confirmBtn.className = 'btn btn-warning';
        confirmBtn.innerHTML = '<i class="fa-solid fa-lock"></i> Restrict Account';
        
        // Reset and show reason inputs
        if (reasonEl) {
            reasonEl.disabled = false;
            reasonEl.value = ''; // Reset to empty
        }
        if (customReasonEl) {
            customReasonEl.value = ''; // Reset
        }
        if (commentEl) {
            commentEl.disabled = false;
            commentEl.value = ''; // Reset
        }
        if (customReasonGroup) {
            customReasonGroup.style.display = 'none';
        }
        
        const reasonGroup = document.getElementById('restrictionReason')?.closest('.form-group');
        if (reasonGroup) reasonGroup.style.display = 'block';
        
        const commentGroup = document.getElementById('restrictionComment')?.closest('.form-group');
        if (commentGroup) commentGroup.style.display = 'block';
        
        // Update note
        const note = document.querySelector('#restrictReasonModal .form-note');
        if (note) {
            note.innerHTML = `
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Note:</strong> The reason and comments will be emailed to the alumni and shown when they try to log in.
                </div>
            `;
        }
        
        // Close the manage modal
        hideManageModal();
        
        // Open restrict reason modal - make sure it's visible
        const modal = document.getElementById('restrictReasonModal');
        if (modal) {
            modal.classList.add('active');
            modal.removeAttribute('inert');
            modal.style.display = 'block';
            document.body.style.overflow = 'hidden';
        }
    }

    // ========================================
    // FIX: HIDE RESTRICT REASON MODAL - RESET FORM VALUES
    // ========================================

    function hideRestrictReasonModal() {
        const modal = document.getElementById('restrictReasonModal');
        if (modal) {
            // Reset the form values
            const reasonEl = document.getElementById('restrictionReason');
            const customReasonEl = document.getElementById('customReason');
            const commentEl = document.getElementById('restrictionComment');
            
            if (reasonEl) {
                reasonEl.value = '';
                reasonEl.disabled = false;
            }
            if (customReasonEl) {
                customReasonEl.value = '';
            }
            if (commentEl) {
                commentEl.value = '';
            }
            
            // Hide custom reason group
            const customReasonGroup = document.getElementById('customReasonGroup');
            if (customReasonGroup) {
                customReasonGroup.style.display = 'none';
            }
            
            // Only hide the modal, don't remove it from DOM
            modal.classList.remove('active');
            modal.setAttribute('inert', '');
            modal.style.display = 'none';
            document.body.style.overflow = '';
            
            // Ensure focus is removed from the modal
            if (document.activeElement && document.activeElement.closest('#restrictReasonModal')) {
                document.activeElement.blur();
            }
        }
    }

   // ========================================
    // FIX: PREPARE UNRESTRICT FROM MANAGE - SAFE VERSION
    // ========================================

    function prepareUnrestrictFromManage(id, name) {
        console.log('🔓 Preparing unrestrict for:', name);
        
        const restrictIdEl = document.getElementById('restrictAlumniId');
        const restrictNameEl = document.getElementById('restrictModalName');
        const restrictTitleEl = document.getElementById('restrictModalTitle');
        const restrictActionEl = document.getElementById('restrictAction');
        const restrictBtnTextEl = document.getElementById('restrictBtnText');
        const confirmBtn = document.getElementById('restrictConfirmBtn');
        
        if (!restrictIdEl || !restrictNameEl || !restrictTitleEl || !restrictActionEl || !restrictBtnTextEl || !confirmBtn) {
            console.error('❌ Restrict modal elements not found');
            return;
        }
        
        restrictIdEl.value = id;
        restrictNameEl.textContent = name;
        restrictTitleEl.textContent = 'Unrestrict Account';
        restrictActionEl.value = 'unrestrict';
        restrictBtnTextEl.textContent = 'Unrestrict Account';
        
        confirmBtn.className = 'btn btn-success';
        confirmBtn.innerHTML = '<i class="fa-solid fa-user-check"></i> Unrestrict Account';
        
        // Hide reason inputs
        const reasonGroup = document.getElementById('restrictionReason')?.closest('.form-group');
        if (reasonGroup) reasonGroup.style.display = 'none';
        const reasonEl = document.getElementById('restrictionReason');
        if (reasonEl) reasonEl.disabled = true;
        const customReasonGroup = document.getElementById('customReasonGroup');
        if (customReasonGroup) customReasonGroup.style.display = 'none';
        
        const commentGroup = document.getElementById('restrictionComment')?.closest('.form-group');
        if (commentGroup) commentGroup.style.display = 'none';
        const commentEl = document.getElementById('restrictionComment');
        if (commentEl) commentEl.disabled = true;
        
        // Update note
        const note = document.querySelector('#restrictReasonModal .form-note');
        if (note) {
            note.innerHTML = `
                <i class="fa-solid fa-circle-info"></i>
                <div>
                    <strong>Note:</strong> Unrestricting this account will allow the alumni to log in again.
                    They will receive an email notification.
                </div>
            `;
        }
        
        // Close the manage modal
        hideManageModal();
        
        // Open restrict reason modal
        const modal = document.getElementById('restrictReasonModal');
        if (modal) {
            modal.classList.add('active');
            modal.removeAttribute('inert');
            document.body.style.overflow = 'hidden';
        }
    }

document.addEventListener('DOMContentLoaded', function() {
    const createForm = document.getElementById('singleCreateForm');
    if (!createForm) return;

    createForm.addEventListener('submit', async function(e) {
        e.preventDefault();

        // ═══════════════════════════════════════════════════════════
        // 1. READ THE VISUALLY ACTIVE TOGGLE — IT IS THE TRUTH
        // ═══════════════════════════════════════════════════════════
        const toggleEl = document.getElementById('single-alumni-type-toggle');
        const inputEl  = document.getElementById('single-alumni-type-input');

        // Default: use whatever's in the hidden input, fallback to 'college'
        let resolvedType = inputEl?.value || 'college';

        // If a button is visually active, THAT is the truth
        if (toggleEl) {
            const activeBtn = toggleEl.querySelector('.type-option.active');
            if (activeBtn && activeBtn.dataset.type) {
                resolvedType = activeBtn.dataset.type;
            }
        }

        console.log('🚀 [submit] Resolved alumni_type =', resolvedType);
        console.log('   - Hidden input value was:', inputEl?.value);
        console.log('   - Active button was:', toggleEl?.querySelector('.type-option.active')?.dataset.type);

        // ═══════════════════════════════════════════════════════════
        // 2. BUILD FORMDATA AND FORCE-OVERRIDE alumni_type
        // ═══════════════════════════════════════════════════════════
        const formData = new FormData(this);

        // 🔥 CRITICAL: Overwrite any stale value with the resolved one
        formData.set('alumni_type', resolvedType);

        // Log everything we're sending
        console.log('📦 FormData being submitted:');
        for (const [key, value] of formData.entries()) {
            console.log(`   ${key} = ${value}`);
        }

        // ═══════════════════════════════════════════════════════════
        // 3. SUBMIT
        // ═══════════════════════════════════════════════════════════
        const submitBtn = this.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating...';

        try {
            const response = await fetch(this.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: formData
            });

            const data = await response.json();
            console.log('📥 Server response:', data);

            if (response.ok && data.success) {
                showAlert(data.message || 'Alumni created successfully!', 'success');
                hideModal();
                setTimeout(() => window.location.reload(), 1500);
            } else {
                let errorMessage = data.message || 'Failed to create alumni.';
                if (data.errors) {
                    errorMessage = Object.values(data.errors).flat().join('\n');
                }
                showAlert(errorMessage, 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Create error:', error);
            showAlert('An error occurred while creating the alumni.', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    console.log('✅ Single create form AJAX handler attached (bulletproof)');
});

// ========================================
// FIX: ENSURE DATA-ID ATTRIBUTE IS SET
// ========================================

// This runs on page load to ensure all cards have data-id
document.addEventListener('DOMContentLoaded', function() {
    // Ensure all alumni cards have data-id
    document.querySelectorAll('.alumni-card').forEach(card => {
        const manageBtn = card.querySelector('.manage-btn');
        if (manageBtn && !card.dataset.id) {
            const id = manageBtn.dataset.id;
            if (id) {
                card.dataset.id = id;
                console.log('✅ Added data-id to card:', id);
            }
        }
    });
});

// ========================================
// FIX: UPDATE CARD STATUS FOR RESTRICT/UNRESTRICT - COMPLETE VERSION
// ========================================

function updateCardStatus(alumniId, newStatus) {
    console.log('🔄 Updating card status for ID:', alumniId, 'New status:', newStatus);
    
    // Try to find the card by data-id first
    let cards = document.querySelectorAll(`.alumni-card[data-id="${alumniId}"]`);
    
    // If not found, try to find by manage-btn
    if (cards.length === 0) {
        const manageBtn = document.querySelector(`.manage-btn[data-id="${alumniId}"]`);
        if (manageBtn) {
            const card = manageBtn.closest('.alumni-card');
            if (card) {
                cards = [card];
                console.log('✅ Found card via manage-btn for status update');
            }
        }
    }
    
    cards.forEach(card => {
        // Update the status badge
        const statusBadge = card.querySelector('.alumni-status');
        if (statusBadge) {
            if (newStatus === 0) {
                // Restricted
                statusBadge.innerHTML = `
                    <span class="alumni-type-badge restricted" style="background: var(--danger-light); color: var(--danger);">
                        <i class="fa-solid fa-user-slash"></i>
                        Restricted
                    </span>
                `;
                console.log('✅ Updated badge to Restricted');
            } else {
                // Active - show original type
                const type = card.dataset.alumniType || 'college';
                const typeLabels = {
                    'shs': { icon: 'fa-solid fa-school', label: 'SHS' },
                    'college': { icon: 'fa-solid fa-graduation-cap', label: 'College' }
                };
                const info = typeLabels[type] || typeLabels['college'];
                statusBadge.innerHTML = `
                    <span class="alumni-type-badge ${type}">
                        <i class="${info.icon}"></i>
                        ${info.label}
                    </span>
                `;
                console.log('✅ Updated badge to', info.label);
            }
        }
        
        // Update the manage button data-status
        const manageBtn = card.querySelector('.manage-btn');
        if (manageBtn) {
            manageBtn.dataset.status = newStatus;
        }
        
        // 👇 THIS IS CRITICAL - Update the hidden input in manage modal
        const statusInput = document.getElementById('manageAccountStatus');
        if (statusInput) {
            statusInput.value = newStatus;
        }
        
        // 👇 THIS IS CRITICAL - Update current status variable
        currentManageAccountStatus = newStatus;
    });
}

// ========================================
// FIX: IMPROVED REMOVE ALUMNI CARD - COMPLETE VERSION
// ========================================

function removeAlumniCard(alumniId, useAnimation = true) {
    console.log('🗑️ Removing card with ID:', alumniId);
    
    if (!alumniId) {
        console.warn('⚠️ No alumni ID provided');
        return;
    }
    
    // Try multiple selectors
    let cards = document.querySelectorAll(`.alumni-card[data-id="${alumniId}"]`);
    
    // If not found, try to find by manage-btn data-id
    if (cards.length === 0) {
        const manageBtn = document.querySelector(`.manage-btn[data-id="${alumniId}"]`);
        if (manageBtn) {
            const card = manageBtn.closest('.alumni-card');
            if (card) {
                cards = [card];
                console.log('✅ Found card via manage-btn');
            }
        }
    }
    
    // If still not found, try to find by scanning all cards
    if (cards.length === 0) {
        document.querySelectorAll('.alumni-card').forEach(card => {
            const btn = card.querySelector('.manage-btn');
            if (btn && btn.dataset.id == alumniId) {
                cards = [card];
                console.log('✅ Found card by scanning all cards');
            }
        });
    }
    
    console.log(`📊 Found ${cards.length} cards to remove`);
    
    if (cards.length === 0) {
        console.warn('⚠️ No cards found with ID:', alumniId);
        // Update stats anyway
        updateDirectoryStats();
        return;
    }
    
    cards.forEach(card => {
        if (useAnimation) {
            // Add fade-out animation
            card.style.transition = 'all 0.3s ease';
            card.style.opacity = '0';
            card.style.transform = 'scale(0.9)';
            card.style.height = card.offsetHeight + 'px';
            card.style.overflow = 'hidden';
            
            setTimeout(() => {
                if (card.parentNode) {
                    card.remove();
                    console.log('✅ Card removed from DOM');
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
        updateDirectoryStats();
    }, 400);
}

// ========================================
// FIX: EXECUTE RESTRICT - CLOSE BOTH MODALS
// ========================================

async function executeRestrict(event) {
    if (event) event.preventDefault();
    
    const id = document.getElementById('restrictAlumniId').value;
    const action = document.getElementById('restrictAction').value;
    const isRestricting = action === 'restrict';
    
    console.log('🔒 Executing restrict/unrestrict:', { id, action, isRestricting });
    
    let payload = {
        restrict: isRestricting ? 1 : 0
    };
    
    if (isRestricting) {
        const reason = document.getElementById('restrictionReason').value;
        const customReason = document.getElementById('customReason').value;
        const comment = document.getElementById('restrictionComment').value;
        
        if (!reason) {
            showAlert('Please select a reason for restriction.', 'error');
            return;
        }
        
        const finalReason = reason === 'other' ? customReason : reason;
        
        if (reason === 'other' && !customReason.trim()) {
            showAlert('Please specify a custom reason.', 'error');
            return;
        }
        
        payload.restriction_reason = finalReason;
        payload.restriction_comment = comment || '';
    }
    
    // Show loading state on button
    const submitBtn = document.getElementById('restrictConfirmBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
    
    try {
        const response = await fetch(`/admin/alumni/${id}/toggle-restrict`, {
            method: 'PATCH',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const data = await response.json();
        console.log('📥 Restrict response:', data);
        
        // ✅ CLOSE BOTH MODALS
        hideRestrictReasonModal();
        hideManageModal();
        
        if (data.success) {
            showAlert(data.message, 'success');
            
            // Wait a tiny bit before removing the card for smoother UX
            setTimeout(() => {
                removeAlumniCard(id, true);
            }, 300);
            
            // Update stats after animation
            setTimeout(() => {
                updateDirectoryStats();
                // Update both badges
                updateArchivedBadge();
                updateRestrictedBadge();
            }, 700);
        } else {
            showAlert(data.message || 'Failed to update account status.', 'error');
        }
    } catch (error) {
        console.error('❌ Toggle restrict error:', error);
        hideRestrictReasonModal();
        hideManageModal();
        showAlert('An error occurred while updating account status. Check console for details.', 'error');
    } finally {
        // Reset button
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// ========================================
// UPDATE BADGE COUNTS
// ========================================

function updateArchivedBadge() {
    const archivedBadge = document.querySelector('.btn-sm-link.archived .badge-count');
    if (archivedBadge) {
        fetch('/admin/alumni/archived-count', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.count !== undefined) {
                archivedBadge.textContent = data.count;
                if (data.count > 0) {
                    archivedBadge.classList.add('has-items');
                } else {
                    archivedBadge.classList.remove('has-items');
                }
            }
        })
        .catch(error => console.error('Error updating archived badge:', error));
    }
}

function updateRestrictedBadge() {
    const restrictedBadge = document.querySelector('.btn-sm-link.restricted .badge-count');
    if (restrictedBadge) {
        fetch('/admin/alumni/restricted-count', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.count !== undefined) {
                restrictedBadge.textContent = data.count;
                if (data.count > 0) {
                    restrictedBadge.classList.add('has-items');
                } else {
                    restrictedBadge.classList.remove('has-items');
                }
            }
        })
        .catch(error => console.error('Error updating restricted badge:', error));
    }
}

// ========================================
// UNIFIED FILTER SYSTEM
// ========================================

let currentQuickFilter = 'all';
let currentAdvancedFilters = {
    program: '',
    year: '',
    sort: 'name_asc'
};
let activeFilterCount = 0;
let availablePrograms = new Set();
let availableYears = new Set();

// Populate filter dropdowns from existing alumni cards
function populateFilterOptions() {
    const programSelect = document.getElementById('filterProgram');
    const yearSelect = document.getElementById('filterYear');
    
    programSelect.innerHTML = '<option value="">All Programs</option>';
    yearSelect.innerHTML = '<option value="">All Years</option>';
    
    availablePrograms = new Set();
    availableYears = new Set();
    
    document.querySelectorAll('.alumni-card').forEach(card => {
        // Get program
        const programEl = card.querySelector('.alumni-program');
        if (programEl) {
            const programText = programEl.textContent.replace(/[^\w\s\-\(\)]/g, '').trim();
            if (programText && programText !== 'Program not specified') {
                availablePrograms.add(programText);
            }
        }
        
        // Get year
        const metaItems = card.querySelectorAll('.meta-item');
        metaItems.forEach(item => {
            const text = item.textContent.trim();
            if (text.includes('Graduated:')) {
                const yearMatch = text.match(/(\d{4})/);
                if (yearMatch) availableYears.add(yearMatch[1]);
            }
        });
    });
    
    // Sort and populate
    Array.from(availablePrograms).sort().forEach(program => {
        const option = document.createElement('option');
        option.value = program;
        option.textContent = program;
        programSelect.appendChild(option);
    });
    
    Array.from(availableYears).sort((a, b) => b - a).forEach(year => {
        const option = document.createElement('option');
        option.value = year;
        option.textContent = year;
        yearSelect.appendChild(option);
    });
}

function updateFilterCounts() {
    const cards = document.querySelectorAll('.alumni-card');
    let total = 0, college = 0, shs = 0;
    
    cards.forEach(card => {
        const isVisible = card.style.display !== 'none';
        if (isVisible) {
            total++;
            const typeBadge = card.querySelector('.alumni-type-badge');
            if (typeBadge) {
                if (typeBadge.classList.contains('college')) college++;
                else if (typeBadge.classList.contains('shs')) shs++;
            }
        }
    });
    
    // ✅ Null-safe element updates
    const countAllEl = document.getElementById('countAll');
    if (countAllEl) countAllEl.textContent = total;

    const countCollegeEl = document.getElementById('countCollege');
    if (countCollegeEl) countCollegeEl.textContent = college;

    const countShsEl = document.getElementById('countShs');
    if (countShsEl) countShsEl.textContent = shs;

    const resultsCountEl = document.getElementById('resultsCount');
    if (resultsCountEl) {
        resultsCountEl.textContent = `${total} result${total != 1 ? 's' : ''}`;
    }
}

// Apply quick filter
function applyQuickFilter(type) {
    currentQuickFilter = type;
    
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.classList.toggle('active', pill.dataset.filter === type);
    });
    
    // Reset advanced filters
    document.getElementById('filterProgram').value = '';
    document.getElementById('filterYear').value = '';
    currentAdvancedFilters.program = '';
    currentAdvancedFilters.year = '';
    updateActiveFilterBadge();
    
    performFilter();
}

// Toggle advanced filters panel
function toggleAdvancedFilters() {
    const panel = document.getElementById('advancedFilters');
    const btn = document.querySelector('.btn-filter-toggle');
    
    if (panel.style.display === 'none') {
        panel.style.display = 'block';
        btn.classList.add('active');
        populateFilterOptions();
    } else {
        panel.style.display = 'none';
        btn.classList.remove('active');
    }
}

// Apply advanced filters
function applyAdvancedFilters() {
    currentAdvancedFilters.program = document.getElementById('filterProgram').value;
    currentAdvancedFilters.year = document.getElementById('filterYear').value;
    currentAdvancedFilters.sort = document.getElementById('filterSort').value;
    
    // Unset quick filter if advanced filters are active
    if (currentAdvancedFilters.program || currentAdvancedFilters.year) {
        document.querySelectorAll('.filter-pill').forEach(pill => {
            pill.classList.remove('active');
        });
        currentQuickFilter = 'all';
    }
    
    updateActiveFilterBadge();
    performFilter();
}

// Update active filter badge
function updateActiveFilterBadge() {
    const badge = document.getElementById('activeFilterBadge');
    const count = Object.values(currentAdvancedFilters)
        .filter(v => v !== '' && v !== 'name_asc').length;
    activeFilterCount = count;
    
    if (count > 0) {
        badge.textContent = count;
        badge.style.display = 'inline-block';
    } else {
        badge.style.display = 'none';
    }
}

// Perform the actual filtering
function performFilter() {
    const cards = document.querySelectorAll('.alumni-card');
    const searchQuery = document.getElementById('searchInput').value.toLowerCase().trim();
    let visibleCount = 0;
    let visibleCards = [];
    
    // Show/hide clear button
    document.getElementById('clearSearch').style.display = searchQuery ? 'flex' : 'none';
    
    cards.forEach(card => {
        // Get card data
        const name = (card.dataset.name || '').toLowerCase();
        const email = (card.dataset.email || '').toLowerCase();
        const program = (card.dataset.program || '').toLowerCase();
        const type = card.dataset.alumniType || '';
        
        // Search filter
        let matchesSearch = !searchQuery || 
            name.includes(searchQuery) || 
            email.includes(searchQuery) || 
            program.includes(searchQuery);
        
        // Quick filter
        let matchesQuick = true;
        if (currentQuickFilter !== 'all') {
            matchesQuick = type === currentQuickFilter;
        }
        
        // Advanced filters
        let matchesAdvanced = true;
        
        if (currentAdvancedFilters.program) {
            const cardProgram = card.querySelector('.alumni-program')?.textContent.replace(/[^\w\s\-\(\)]/g, '').trim() || '';
            matchesAdvanced = matchesAdvanced && cardProgram === currentAdvancedFilters.program;
        }
        
        if (currentAdvancedFilters.year) {
            let cardYear = '';
            card.querySelectorAll('.meta-item').forEach(item => {
                const text = item.textContent.trim();
                if (text.includes('Graduated:')) {
                    const yearMatch = text.match(/(\d{4})/);
                    if (yearMatch) cardYear = yearMatch[1];
                }
            });
            matchesAdvanced = matchesAdvanced && cardYear === currentAdvancedFilters.year;
        }
        
        // Access filter (from access-tracker partial)
        let matchesAccess = true;
        if (typeof currentAccessFilter !== 'undefined' && currentAccessFilter !== 'all') {
            const hasAccessed = card.dataset.hasAccessed === '1';
            matchesAccess = (currentAccessFilter === 'accessed') ? hasAccessed : !hasAccessed;
        }

        const visible = matchesSearch && matchesQuick && matchesAdvanced && matchesAccess;
                
        card.style.display = visible ? 'flex' : 'none';
        if (visible) {
            visibleCount++;
            visibleCards.push(card);
        }
    });
    
    // Apply sorting
    applySorting(visibleCards);
    updateFilterCounts();
    
    // Show/hide no results
    const grid = document.getElementById('alumniGrid');
    const existingNoResults = grid.querySelector('.no-results');
    
    if (visibleCount === 0 && cards.length > 0) {
        if (!existingNoResults) {
            const noResults = document.createElement('div');
            noResults.className = 'no-results';
            noResults.innerHTML = `
                <div class="empty-icon"><i class="fa-solid fa-filter-circle-xmark"></i></div>
                <h4>No matching alumni found</h4>
                <p>Try adjusting your search or filters</p>
                <button class="btn btn-secondary btn-sm" onclick="resetFilters()" style="margin-top: 0.5rem;">
                    <i class="fa-solid fa-undo"></i> Reset Filters
                </button>
            `;
            grid.appendChild(noResults);
        }
    } else {
        if (existingNoResults) existingNoResults.remove();
    }
}

// Apply sorting
function applySorting(visibleCards) {
    const sort = currentAdvancedFilters.sort || 'name_asc';
    const grid = document.getElementById('alumniGrid');
    
    if (visibleCards.length <= 1) return;
    
    visibleCards.sort((a, b) => {
        const getName = (card) => {
            const nameEl = card.querySelector('.alumni-name');
            return nameEl ? nameEl.textContent.trim() : '';
        };
        const getYear = (card) => {
            let year = 0;
            card.querySelectorAll('.meta-item').forEach(item => {
                const text = item.textContent.trim();
                if (text.includes('Graduated:')) {
                    const yearMatch = text.match(/(\d{4})/);
                    if (yearMatch) year = parseInt(yearMatch[1]);
                }
            });
            return year;
        };
        
        const nameA = getName(a), nameB = getName(b);
        const yearA = getYear(a), yearB = getYear(b);
        
        switch(sort) {
            case 'name_asc': return nameA.localeCompare(nameB);
            case 'name_desc': return nameB.localeCompare(nameA);
            case 'year_desc': return yearB - yearA;
            case 'year_asc': return yearA - yearB;
            default: return 0;
        }
    });
    
    visibleCards.forEach(card => grid.appendChild(card));
}

// Reset all filters
function resetFilters() {
    currentQuickFilter = 'all';
    document.querySelectorAll('.filter-pill').forEach(pill => {
        pill.classList.toggle('active', pill.dataset.filter === 'all');
    });
    
    document.getElementById('filterProgram').value = '';
    document.getElementById('filterYear').value = '';
    document.getElementById('filterSort').value = 'name_asc';
    document.getElementById('searchInput').value = '';
    
    currentAdvancedFilters = { program: '', year: '', sort: 'name_asc' };
    updateActiveFilterBadge();
    performFilter();
}

// Clear search
function clearSearch() {
    document.getElementById('searchInput').value = '';
    performFilter();
    document.getElementById('searchInput').focus();
}

// Initialize filter system
document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => {
        populateFilterOptions();
        updateFilterCounts();
        document.querySelector('.filter-pill[data-filter="all"]')?.classList.add('active');
    }, 300);
});

// ========================================
// ADD COLUMN TO PREVIEW TABLE
// ========================================

function addColumnToPreview() {
    showAddColumnModal();
}

function showAddColumnModal() {
    // Create a temporary modal for input
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay active';
    overlay.style.display = 'flex';
    overlay.id = 'addColumnModal';
    
    overlay.innerHTML = `
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-plus"></i>
                            Add Column
                        </h2>
                        <p class="modal-subtitle">Enter the name of the new column</p>
                    </div>
                    <button class="modal-close" onclick="closeAddColumnModal()" title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label for="newColumnName">Column Name <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="newColumnName" class="form-control" placeholder="e.g., Year Graduated" autofocus>
                        <small style="color: var(--gray-500); font-size: 0.75rem;">Enter a unique column name (e.g., "Department", "Batch")</small>
                    </div>
                    <div class="modal-actions" style="margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeAddColumnModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="confirmAddColumn()">
                            <i class="fa-solid fa-plus"></i> Add Column
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    
    // Focus the input after a short delay
    setTimeout(() => {
        const input = document.getElementById('newColumnName');
        if (input) input.focus();
    }, 100);
    
    // Enter key to submit
    overlay.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmAddColumn();
        }
        if (e.key === 'Escape') {
            closeAddColumnModal();
        }
    });
}

function closeAddColumnModal() {
    const modal = document.getElementById('addColumnModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

function confirmAddColumn() {
    const input = document.getElementById('newColumnName');
    const columnName = input ? input.value.trim() : '';
    
    if (!columnName) {
        showAlert('Please enter a column name.', 'error');
        input?.focus();
        return;
    }
    
    // Check if column already exists
    if (fileHeaders.includes(columnName)) {
        showAlert('Column "' + columnName + '" already exists!', 'warning');
        input?.focus();
        return;
    }
    
    // Add to headers
    fileHeaders.push(columnName);
    
    // Add to all rows
    importedData.forEach(row => {
        row[columnName] = '';
    });
    
    // Re-render the table
    renderPreviewTable(importedData);
    
    // Also update column mapping UI
    renderColumnMapping(fileHeaders);
    
    closeAddColumnModal();
    
    showAlert('Column "' + columnName + '" added successfully!', 'success');
}

// ========================================
// APPLY VALUE TO ALL ROWS - ENHANCED
// ========================================

function applyValueToAllRows(columnName) {
    if (!columnName || columnName.trim() === '') {
        showAlert('Please specify a column name.', 'error');
        return;
    }
    
    // Check if column exists
    if (!fileHeaders.includes(columnName)) {
        showAlert('Column "' + columnName + '" does not exist!', 'error');
        return;
    }
    
    // Show modal for value input
    showFillAllModal(columnName);
}

function showFillAllModal(columnName) {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay active';
    overlay.style.display = 'flex';
    overlay.id = 'fillAllModal';

    const wrapper = document.createElement('div');
    wrapper.className = 'modal-content-wrapper';
    wrapper.style.maxWidth = '450px';

    const card = document.createElement('div');
    card.className = 'modal-card';

    // Header
    const header = document.createElement('div');
    header.className = 'modal-header';
    
    const headerContent = document.createElement('div');
    const title = document.createElement('h2');
    title.className = 'modal-title';
    const titleIcon = document.createElement('i');
    titleIcon.className = 'fa-solid fa-fill';
    titleIcon.setAttribute('aria-hidden', 'true');
    title.append(titleIcon, ' Fill All Rows');
    
    const subtitle = document.createElement('p');
    subtitle.className = 'modal-subtitle';
    subtitle.append('Apply a value to all rows in column: ');
    const strongName = document.createElement('strong');
    strongName.textContent = columnName;  // ✅ Safe
    subtitle.appendChild(strongName);

    headerContent.append(title, subtitle);
    header.appendChild(headerContent);

    const closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'modal-close';
    closeBtn.setAttribute('aria-label', 'Close');
    closeBtn.title = 'Close';
    closeBtn.addEventListener('click', closeFillAllModal);
    closeBtn.innerHTML = '<i class="fa-solid fa-xmark" aria-hidden="true"></i>';
    header.appendChild(closeBtn);

    // Body
    const body = document.createElement('div');
    body.className = 'modal-body';
    body.style.padding = '1.5rem';

    const formGroup = document.createElement('div');
    formGroup.className = 'form-group';

    const label = document.createElement('label');
    label.htmlFor = 'fillAllValue';
    label.append('Value ');
    const star = document.createElement('span');
    star.style.color = 'var(--danger)';
    star.textContent = '*';
    label.appendChild(star);

    const input = document.createElement('input');
    input.type = 'text';
    input.id = 'fillAllValue';
    input.className = 'form-control';
    input.placeholder = 'Enter value to apply to all rows';

    const hint = document.createElement('small');
    hint.style.color = 'var(--gray-500)';
    hint.style.fontSize = '0.75rem';
    hint.textContent = `This will apply the same value to all ${importedData.length} rows in column "${columnName}"`;

    formGroup.append(label, input, hint);

    // Actions
    const actions = document.createElement('div');
    actions.className = 'modal-actions';
    actions.style.marginTop = '1rem';

    const cancelBtn = document.createElement('button');
    cancelBtn.type = 'button';
    cancelBtn.className = 'btn btn-secondary';
    cancelBtn.textContent = 'Cancel';
    cancelBtn.addEventListener('click', closeFillAllModal);

    const confirmBtn = document.createElement('button');
    confirmBtn.type = 'button';
    confirmBtn.className = 'btn btn-primary';
    confirmBtn.innerHTML = '<i class="fa-solid fa-fill" aria-hidden="true"></i> Apply to All';
    confirmBtn.addEventListener('click', () => confirmFillAll(columnName));

    actions.append(cancelBtn, confirmBtn);
    body.append(formGroup, actions);

    card.append(header, body);
    wrapper.appendChild(card);
    overlay.appendChild(wrapper);
    document.body.appendChild(overlay);

    document.body.style.overflow = 'hidden';
    setTimeout(() => input.focus(), 100);

    overlay.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') { e.preventDefault(); confirmFillAll(columnName); }
        if (e.key === 'Escape') closeFillAllModal();
    });
}

function closeFillAllModal() {
    const modal = document.getElementById('fillAllModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

function confirmFillAll(columnName) {
    const input = document.getElementById('fillAllValue');
    const value = input ? input.value.trim() : '';
    
    if (!value) {
        showAlert('Please enter a value to apply.', 'error');
        input?.focus();
        return;
    }
    
    // Apply to all rows
    importedData.forEach(row => {
        row[columnName] = value;
    });
    
    // Re-render the table
    renderPreviewTable(importedData);
    validateAllRows();
    
    closeFillAllModal();
    
    showAlert('Applied "' + value + '" to all rows in column "' + columnName + '"', 'success');
}

// ========================================
// APPLY VALUE TO ALL ROWS - ENHANCED WITH MODAL
// ========================================

function applyValueToAllRowsPrompt() {
    // Show a modal to select column and enter value
    showFillAllColumnSelectModal();
}

function showFillAllColumnSelectModal() {
    const overlay = document.createElement('div');
    overlay.className = 'modal-overlay active';
    overlay.style.display = 'flex';
    overlay.id = 'fillAllColumnSelectModal';

    // ✅ Build escaped HTML string
    let columnOptions = '';
    fileHeaders.forEach(header => {
        const safe = window.escapeHtml(header);
        columnOptions += `<option value="${safe}">${safe}</option>`;
    });
    
    overlay.innerHTML = `
        <div class="modal-content-wrapper" style="max-width: 450px;">
            <div class="modal-card">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title">
                            <i class="fa-solid fa-fill"></i>
                            Fill All Rows
                        </h2>
                        <p class="modal-subtitle">Apply a value to all rows in a column</p>
                    </div>
                    <button class="modal-close" onclick="closeFillAllColumnSelectModal()" title="Close">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body" style="padding: 1.5rem;">
                    <div class="form-group">
                        <label for="fillAllColumnSelect">Select Column <span style="color: var(--danger);">*</span></label>
                        <select id="fillAllColumnSelect" class="form-control" required>
                            <option value="">-- Select a column --</option>
                            ${columnOptions}
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="fillAllValueInput">Value <span style="color: var(--danger);">*</span></label>
                        <input type="text" id="fillAllValueInput" class="form-control" placeholder="Enter value to apply to all rows" autofocus>
                        <small style="color: var(--gray-500); font-size: 0.75rem;">
                            This will apply the same value to all ${importedData.length} rows in the selected column
                        </small>
                    </div>
                    <div class="modal-actions" style="margin-top: 1rem;">
                        <button type="button" class="btn btn-secondary" onclick="closeFillAllColumnSelectModal()">Cancel</button>
                        <button type="button" class="btn btn-primary" onclick="confirmFillAllColumnSelect()">
                            <i class="fa-solid fa-fill"></i> Apply to All
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    document.body.style.overflow = 'hidden';
    
    // Focus the input after a short delay
    setTimeout(() => {
        const input = document.getElementById('fillAllValueInput');
        if (input) input.focus();
    }, 100);
    
    // Enter key to submit
    overlay.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmFillAllColumnSelect();
        }
        if (e.key === 'Escape') {
            closeFillAllColumnSelectModal();
        }
    });
}

function closeFillAllColumnSelectModal() {
    const modal = document.getElementById('fillAllColumnSelectModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

function confirmFillAllColumnSelect() {
    const columnSelect = document.getElementById('fillAllColumnSelect');
    const valueInput = document.getElementById('fillAllValueInput');
    
    const columnName = columnSelect ? columnSelect.value : '';
    const value = valueInput ? valueInput.value.trim() : '';
    
    if (!columnName) {
        showAlert('Please select a column.', 'error');
        columnSelect?.focus();
        return;
    }
    
    if (!value) {
        showAlert('Please enter a value to apply.', 'error');
        valueInput?.focus();
        return;
    }
    
    // Check if column exists
    if (!fileHeaders.includes(columnName)) {
        showAlert('Column "' + columnName + '" does not exist!', 'error');
        return;
    }
    
    closeFillAllColumnSelectModal();
    
    // Apply to all rows
    importedData.forEach(row => {
        row[columnName] = value;
    });
    
    // Re-render the table
    renderPreviewTable(importedData);
    validateAllRows();
    
    showAlert('Applied "' + value + '" to all rows in column "' + columnName + '"', 'success');
}

// ========================================
// APPLY VALUE FROM ONE ROW TO ALL ROWS - ENHANCED
// ========================================

function applyCellValueToAll(rowIndex, columnName) {
    if (!fileHeaders.includes(columnName)) {
        showAlert('Column "' + columnName + '" does not exist!', 'error');
        return;
    }
    
    const cellValue = importedData[rowIndex]?.[columnName];
    if (cellValue === undefined || cellValue === null || cellValue.trim() === '') {
        showAlert('The cell is empty. Please enter a value first.', 'warning');
        return;
    }

    const safeValue = window.escapeHtml(cellValue);
    const safeColumn = window.escapeHtml(columnName);
    
    // Use warning modal for confirmation
    showWarningModal({
        title: 'Apply to All Rows',

        message: `Apply "<strong>${safeValue}</strong>" to all rows in column "<strong>${safeColumn}</strong>"?<br>
                <small>This will overwrite all existing values in this column.</small>`,
        iconType: 'info',
        confirmText: 'Apply to All',
        confirmClass: 'btn-primary',
        onConfirm: function() {
            // Apply to all rows
            importedData.forEach(row => {
                row[columnName] = cellValue;
            });
            
            // Re-render the table
            renderPreviewTable(importedData);
            validateAllRows();
            
            showAlert('Applied "' + cellValue + '" to all rows in column "' + columnName + '"', 'success');
        }
    });
}

// ========================================
// BATCH FILL - ENHANCED WITH PREVIEW
// ========================================

let batchPreviewData = [];
let batchPreviewFullData = [];
let batchShowAll = false;

function openBatchFillModal() {
    const modal = document.getElementById('batchFillModal');
    const select = document.getElementById('batchColumn');
    
    // Populate column options
    select.innerHTML = '<option value="">-- Select a column --</option>';
    
    fileHeaders.forEach(header => {
        const option = document.createElement('option');
        option.value = header;
        option.textContent = header;
        select.appendChild(option);
    });
    
    // Set default row range
    const totalRows = importedData.length;
    document.getElementById('batchStartRow').value = 1;
    document.getElementById('batchEndRow').value = Math.min(10, totalRows);
    document.getElementById('batchValue').value = '';
    
    // ✅ Load preview data
    batchPreviewFullData = importedData;
    batchShowAll = false;
    renderBatchPreviewTable();
    
    // Show modal
    modal.style.display = 'block';
    modal.classList.add('active');
    modal.removeAttribute('inert');
    document.body.style.overflow = 'hidden';
}

function closeBatchFillModal() {
    const modal = document.getElementById('batchFillModal');
    modal.style.display = 'none';
    modal.classList.remove('active');
    modal.setAttribute('inert', '');
    document.body.style.overflow = '';
}

function renderBatchPreviewTable() {
    const table = document.getElementById('batchPreviewTable');
    const thead = table.querySelector('thead');
    const tbody = table.querySelector('tbody');
    
    thead.innerHTML = '';
    tbody.innerHTML = '';
    
    const headers = fileHeaders;
    const totalRows = batchPreviewFullData.length;
    
    // Determine which rows to show
    let displayData;
    if (batchShowAll) {
        displayData = batchPreviewFullData;
    } else {
        displayData = batchPreviewFullData.slice(0, 10);
    }
    
    // Update info
    document.getElementById('batchPreviewInfo').textContent = `(${totalRows} total rows)`;
    document.getElementById('batchPreviewRowCount').textContent = displayData.length;
    
    // Show/hide buttons
    document.getElementById('batchShowAllBtn').style.display = batchShowAll ? 'none' : 'inline-flex';
    document.getElementById('batchShowFirstBtn').style.display = batchShowAll ? 'inline-flex' : 'none';
    
    // Headers
    const headerRow = document.createElement('tr');
    
    // Row number column
    const numTh = document.createElement('th');
    numTh.textContent = '#';
    numTh.style.cssText = 'padding: 0.25rem 0.5rem; text-align: center; font-weight: 600; color: var(--gray-700); background: var(--gray-200); position: sticky; left: 0; z-index: 2; min-width: 30px; font-size: 0.7rem;';
    headerRow.appendChild(numTh);
    
    headers.forEach(header => {
        const th = document.createElement('th');
        th.textContent = header;
        th.style.cssText = 'padding: 0.25rem 0.5rem; text-align: left; font-weight: 600; color: var(--gray-700); background: var(--gray-200); white-space: nowrap; min-width: 80px; font-size: 0.7rem;';
        headerRow.appendChild(th);
    });
    thead.appendChild(headerRow);
    
    // Data rows
    displayData.forEach((row, index) => {
        const tr = document.createElement('tr');
        tr.style.transition = 'background 0.15s';
        tr.onmouseenter = function() { this.style.background = 'var(--gray-100)'; };
        tr.onmouseleave = function() { this.style.background = ''; };
        
        // Row number (actual index + 1)
        const actualIndex = batchShowAll ? index : index;
        const numTd = document.createElement('td');
        numTd.textContent = actualIndex + 1;
        numTd.style.cssText = 'padding: 0.25rem 0.5rem; text-align: center; color: var(--gray-500); font-weight: 500; position: sticky; left: 0; z-index: 1; background: var(--white); font-size: 0.7rem;';
        tr.appendChild(numTd);
        
        headers.forEach(header => {
            const td = document.createElement('td');
            const value = row[header] || '';
            td.textContent = value;
            td.style.cssText = 'padding: 0.25rem 0.5rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 0.7rem;';
            
            // Highlight if this row is in the selected range
            const startRow = parseInt(document.getElementById('batchStartRow').value) || 0;
            const endRow = parseInt(document.getElementById('batchEndRow').value) || 0;
            const rowNum = actualIndex + 1;
            
            if (rowNum >= startRow && rowNum <= endRow && startRow > 0 && endRow > 0) {
                td.style.background = 'var(--info-light)';
                td.style.borderLeft = '3px solid var(--info)';
            }
            
            tr.appendChild(td);
        });
        tbody.appendChild(tr);
    });
}

function toggleBatchPreview() {
    const body = document.getElementById('batchPreviewBody');
    const toggleText = document.getElementById('batchPreviewToggle');
    const isHidden = body.style.display === 'none';
    
    if (isHidden) {
        body.style.display = 'block';
        toggleText.textContent = 'Collapse';
    } else {
        body.style.display = 'none';
        toggleText.textContent = 'Expand';
    }
}

function batchPreviewAllRows() {
    batchShowAll = true;
    renderBatchPreviewTable();
}

function batchPreviewFirstRows() {
    batchShowAll = false;
    renderBatchPreviewTable();
}

// ✅ Update row range display when inputs change
document.addEventListener('DOMContentLoaded', function() {
    // Re-render preview when row range changes
    document.getElementById('batchStartRow')?.addEventListener('input', function() {
        renderBatchPreviewTable();
    });
    document.getElementById('batchEndRow')?.addEventListener('input', function() {
        renderBatchPreviewTable();
    });
});

// Close modal on overlay click
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('batchFillModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeBatchFillModal();
    });
});

function selectAlumniType(scope, type) {
    // scope: 'single' | 'bulk'
    const toggle = document.getElementById(`${scope}-alumni-type-toggle`);
    const input = document.getElementById(`${scope}-alumni-type-input`);
    
    console.log('🎯 selectAlumniType called:', { scope, type, toggleFound: !!toggle, inputFound: !!input });
    
    if (!toggle || !input) {
        console.warn('❌ Toggle or input not found for scope:', scope);
        return;
    }

    toggle.querySelectorAll('.type-option').forEach(btn => {
        const isActive = btn.dataset.type === type;
        btn.classList.toggle('active', isActive);
    });
    
    input.value = type;
    console.log('✅ Set hidden input', input.id, 'to:', input.value);
}

// Initialize both toggles on page load
document.addEventListener('DOMContentLoaded', function () {
    selectAlumniType('single', document.getElementById('single-alumni-type-input')?.value || 'college');
    selectAlumniType('bulk', document.getElementById('bulk-alumni-type-input')?.value || 'college');
});

// ========================================
// CARD PHOTO UPLOAD + MATCHING
// ========================================

let uploadedPhotos = [];              // [{ file, name, key, dataUrl }]
let excludedPhotoKeys = new Set();    // keys the admin manually unmatched

/**
 * Normalize a filename into SURNAME|FIRSTNAME|MI key.
 * "CLAUS_JOHANNES EMMANUEL_A.jpg" -> "CLAUS|JOHANNES EMMANUEL|A"
 */
function normalizePhotoKey(filename) {
    const base = filename.replace(/\.[^.]+$/, '').trim().toUpperCase();
    const parts = base.split('_').map(p => p.trim()).filter(Boolean);

    if (parts.length < 3) return null;

    const lastName = parts[0];
    const middleInitial = parts[parts.length - 1];
    const firstName = parts.slice(1, -1).join(' ');

    if (middleInitial.length !== 1 || !/[A-Z]/.test(middleInitial)) return null;

    return `${lastName}|${firstName}|${middleInitial}`;
}

/**
 * Read a File as a data URL for thumbnail display.
 */
function fileToDataUrl(file) {
    return new Promise((resolve) => {
        const reader = new FileReader();
        reader.onload = (e) => resolve(e.target.result);
        reader.readAsDataURL(file);
    });
}

/**
 * Handle photo selection (ZIP or multiple images).
 */
async function handlePhotoSelection(files) {
    if (!files || files.length === 0) return;

    uploadedPhotos = [];
    excludedPhotoKeys.clear();

    const statusEl = document.getElementById('photoUploadStatus');
    const countEl = document.getElementById('photoCount');

    if (files.length === 1 && files[0].name.toLowerCase().endsWith('.zip')) {
        await handleZipUpload(files[0]);
    } else {
        await handleImageFiles(Array.from(files));
    }

    // Update status (top section)
    if (statusEl) {
        statusEl.style.display = uploadedPhotos.length > 0 ? 'block' : 'none';
    }
    if (countEl) {
        countEl.textContent = uploadedPhotos.length;
    }

    // Update preview-step counter
    const previewCount = document.getElementById('previewPhotoCount');
    if (previewCount) previewCount.textContent = uploadedPhotos.length;

    // Re-match photos against current table
    matchPhotosToRows();
}

/**
 * Process a ZIP file client-side using JSZip (loaded on demand).
 */
async function handleZipUpload(zipFile) {
    if (typeof JSZip === 'undefined') {
        await loadScript('https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js');
    }

    const zip = await JSZip.loadAsync(zipFile);
    const entries = Object.values(zip.files).filter(f => !f.dir && /\.(jpe?g|png|webp)$/i.test(f.name));

    for (const entry of entries) {
        if (entry.name.includes('__MACOSX')) continue;

        const filename = entry.name.split('/').pop();
        const key = normalizePhotoKey(filename);
        if (!key) {
            console.warn('Skipping photo with invalid name:', filename);
            continue;
        }

        const blob = await entry.async('blob');
        const dataUrl = await new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => resolve(e.target.result);
            reader.readAsDataURL(blob);
        });

        uploadedPhotos.push({
            file: new File([blob], filename, { type: blob.type }),
            name: filename,
            key,
            dataUrl,
        });
    }
}

/**
 * Process multiple image files.
 */
async function handleImageFiles(files) {
    for (const file of files) {
        const key = normalizePhotoKey(file.name);
        if (!key) {
            console.warn('Skipping photo with invalid name:', file.name);
            continue;
        }

        const dataUrl = await fileToDataUrl(file);
        uploadedPhotos.push({ file, name: file.name, key, dataUrl });
    }
}

/**
 * Helper to load a script dynamically.
 */
function loadScript(src) {
    return new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

/**
 * Match uploaded photos to the preview table rows and render thumbnails.
 */
function matchPhotosToRows() {
    if (!importedData || importedData.length === 0) return;

    // Build fast lookup
    const photoLookup = {};
    uploadedPhotos.forEach(p => {
        photoLookup[p.key] = p;  // last wins on duplicate
    });

    const usedKeys = new Set();

    importedData.forEach((row, index) => {
        const photoCell = document.getElementById(`photo-${index}`);
        if (!photoCell) return;

        // Build alumni key from row data
        const lastName = (row['Last Name'] || row['last_name'] || '').toUpperCase().trim();
        const firstName = (row['First Name'] || row['first_name'] || '').toUpperCase().trim();
        const middleName = (row['Middle Name'] || row['middle_name'] || '').trim();
        const middleInitial = middleName ? middleName.charAt(0).toUpperCase() : '';

        const alumniKey = lastName && firstName && middleInitial
            ? `${lastName}|${firstName}|${middleInitial}`
            : null;

        let matched = null;
        if (alumniKey && photoLookup[alumniKey] && !excludedPhotoKeys.has(alumniKey)) {
            matched = photoLookup[alumniKey];
            usedKeys.add(alumniKey);
        }

    if (matched) {
        const safeName = window.escapeHtml(matched.name);
        photoCell.innerHTML = `
            <div style="position: relative; display: inline-block;">
                <img src="${matched.dataUrl}"
                    alt="Card photo"
                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 6px; border: 2px solid #10b981;"
                    title="${safeName}">
                <button type="button"
                        onclick="unmatchPhotoForRow(${index})"
                        aria-label="Remove photo from row ${index + 1}"
                        title="Remove this photo from this row"
                        style="position: absolute; top: -6px; right: -6px; background: #dc2626; color: white; border: 2px solid white; border-radius: 50%; width: 20px; height: 20px; cursor: pointer; font-size: 11px; line-height: 1; padding: 0;">
                    ×
                </button>
                    <span style="position: absolute; bottom: -4px; right: -4px; background: #10b981; color: white; border-radius: 50%; width: 18px; height: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; border: 2px solid white;">
                        <i class="fa-solid fa-check"></i>
                    </span>
                </div>
            `;
        } else {
            photoCell.innerHTML = `
                <span style="color: var(--gray-400); font-size: 0.75rem;" title="No matching photo found">
                    <i class="fa-regular fa-image"></i><br>No match
                </span>
            `;
        }
    });

    // Unmatched summary
    const unmatched = uploadedPhotos.filter(p => !usedKeys.has(p.key) && !excludedPhotoKeys.has(p.key));
    const unmatchedEl = document.getElementById('unmatchedPhotos');

    if (unmatchedEl) {
        if (unmatched.length > 0) {
            unmatchedEl.style.display = 'block';
            unmatchedEl.innerHTML = `
                <div style="padding: 0.75rem; background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: var(--radius);">
                    <p style="margin: 0 0 0.5rem 0; font-size: 0.8125rem; font-weight: 600; color: #92400e;">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        ${unmatched.length} photo(s) have no matching alumni in the import:
                    </p>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">

                        ${unmatched.slice(0, 20).map(p => {
                            const safeName = window.escapeHtml(p.name);
                            return `
                                <div style="display: flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; background: white; border-radius: 4px; font-size: 0.75rem;">
                                    <img src="${p.dataUrl}" style="width: 24px; height: 24px; object-fit: cover; border-radius: 3px;">
                                    <span>${safeName}</span>
                                </div>
                            `;
                        }).join('')}

                        ${unmatched.length > 20 ? `<span style="font-size: 0.75rem; color: #92400e;">…and ${unmatched.length - 20} more</span>` : ''}
                    </div>
                </div>
            `;
        } else {
            unmatchedEl.style.display = 'none';
        }
    }
}

/**
 * Admin clicked × on a matched photo. Mark that row as "skip photo".
 */
function unmatchPhotoForRow(rowIndex) {
    const row = importedData[rowIndex];
    if (!row) return;

    const lastName = (row['Last Name'] || row['last_name'] || '').toUpperCase().trim();
    const firstName = (row['First Name'] || row['first_name'] || '').toUpperCase().trim();
    const middleName = (row['Middle Name'] || row['middle_name'] || '').trim();
    const middleInitial = middleName ? middleName.charAt(0).toUpperCase() : '';
    const key = lastName && firstName && middleInitial
        ? `${lastName}|${firstName}|${middleInitial}`
        : null;

    if (key) {
        excludedPhotoKeys.add(key);
        row._skip_photo = true;  // backend hint
        showAlert('Photo removed from this row. It will not be uploaded.', 'info');
        matchPhotosToRows();
    }
}

/**
 * Clear all uploaded photos.
 */
function clearPhotos() {
    uploadedPhotos = [];
    excludedPhotoKeys.clear();

    // Reset both file inputs
    ['photoFileInput', 'previewPhotoInput'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.value = '';
    });

    // Reset UI bits
    const statusEl = document.getElementById('photoUploadStatus');
    if (statusEl) statusEl.style.display = 'none';
    const unmatchedEl = document.getElementById('unmatchedPhotos');
    if (unmatchedEl) unmatchedEl.style.display = 'none';
    const previewCount = document.getElementById('previewPhotoCount');
    if (previewCount) previewCount.textContent = '0';

    matchPhotosToRows();
}

// ========================================
// WIRE UP PHOTO INPUTS (both sections)
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    // Top section input
    const photoInput = document.getElementById('photoFileInput');
    if (photoInput) {
        photoInput.addEventListener('change', function(e) {
            handlePhotoSelection(e.target.files);
        });
    }

    // Preview-step input (the one you added)
    const previewPhotoInput = document.getElementById('previewPhotoInput');
    if (previewPhotoInput) {
        previewPhotoInput.addEventListener('change', function(e) {
            handlePhotoSelection(e.target.files);
        });
    }

    // Drop zone
    const photoDropZone = document.getElementById('photoDropZone');
    if (photoDropZone) {
        ['dragenter', 'dragover'].forEach(ev => {
            photoDropZone.addEventListener(ev, (e) => {
                e.preventDefault();
                photoDropZone.classList.add('drag-over');
            });
        });

        ['dragleave', 'drop'].forEach(ev => {
            photoDropZone.addEventListener(ev, (e) => {
                e.preventDefault();
                photoDropZone.classList.remove('drag-over');
            });
        });

        photoDropZone.addEventListener('drop', function(e) {
            const files = e.dataTransfer.files;
            if (files && files.length > 0) {
                handlePhotoSelection(files);
            }
        });
    }
});

// ========================================
// PHOTOS-ONLY MODE
// ========================================

function togglePhotosOnlyMode() {
    const isPhotosOnly = document.getElementById('photosOnlyMode')?.checked || false;

    const csvDrop = document.getElementById('fileDropZone');
    const templateBtn = document.querySelector('button[onclick="downloadTemplate()"]');
    const previewBtn = document.getElementById('previewBtn');

    if (csvDrop) csvDrop.style.display = isPhotosOnly ? 'none' : 'block';
    if (templateBtn) templateBtn.style.display = isPhotosOnly ? 'none' : 'inline-flex';
    if (previewBtn) previewBtn.style.display = isPhotosOnly ? 'none' : 'inline-flex';

    // Show / create the "Upload Photos Only" button
    let submitBtn = document.getElementById('photosOnlySubmitBtn');
    if (isPhotosOnly) {
        if (!submitBtn) {
            submitBtn = document.createElement('button');
            submitBtn.id = 'photosOnlySubmitBtn';
            submitBtn.type = 'button';
            submitBtn.className = 'btn btn-primary';
            submitBtn.style.marginTop = '1rem';
            submitBtn.innerHTML = '<i class="fa-solid fa-cloud-arrow-up"></i> Upload Photos to Existing Alumni';
            submitBtn.onclick = submitPhotosOnlyImport;
            document.querySelector('.bulk-import-body')?.appendChild(submitBtn);
        }
        submitBtn.style.display = 'inline-flex';
    } else if (submitBtn) {
        submitBtn.style.display = 'none';
    }
}

async function submitPhotosOnlyImport() {
    if (uploadedPhotos.length === 0) {
        showAlert('Please upload at least one photo first.', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('_token', getCsrfToken());

    uploadedPhotos.forEach((photo, i) => {
        formData.append(`photos[${i}]`, photo.file, photo.name);
    });

    const btn = document.getElementById('photosOnlySubmitBtn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Uploading...';

    try {
        const response = await fetch('/admin/alumni/upload-photos-only', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            },
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showAlert(result.message, 'success');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert(result.message || 'Upload failed.', 'error');
        }
    } catch (err) {
        console.error('Photos-only import error:', err);
        showAlert('Error: ' + err.message, 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = orig;
    }
}

// ========================================
// GRID / LIST VIEW TOGGLE
// ========================================
function setViewMode(mode) {
    const grid = document.getElementById('alumniGrid');
    const toggleBtns = document.querySelectorAll('.view-btn');
    
    // Update button active states
    toggleBtns.forEach(btn => btn.classList.remove('active'));
    if (mode === 'grid') {
        toggleBtns[0].classList.add('active');
    } else {
        toggleBtns[1].classList.add('active');
    }
    
    // Update grid container class
    if (mode === 'list') {
        grid.classList.add('list-view-active');
        localStorage.setItem('alumniViewMode', 'list');
    } else {
        grid.classList.remove('list-view-active');
        localStorage.setItem('alumniViewMode', 'grid');
    }
}

// Restore saved view preference on page load
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('alumniViewMode');
    if (savedView === 'list') {
        setViewMode('list');
    }
});

</script>

@include('directory.partials._access-tracker')

</body>
</html>

{{-- This is admin_directory.blade.php --}}