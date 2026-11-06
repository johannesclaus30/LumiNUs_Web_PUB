<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Tracer | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/alumni_tracer_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    
    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation (Unchanged) -->
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
                <a href="{{ route('perks.index') }}" class="nav-item">
                    <i class="fa-solid fa-gift"></i>
                    <span>Perks & Discounts</span>
                </a>
                <a href="/admin/alumni_tracer" class="nav-item active">
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
            
            <div class="sidebar-footer">
                <a href="{{ route('admin.logout') }}" class="nav-item logout-btn">
                    <i class="fa-solid fa-right-from-bracket"></i>
                    <span>Sign Out</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <!-- Replace the header-actions div in the page-header -->
            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-location-dot"></i>
                            Alumni Tracer
                        </h1>
                        <p class="page-subtitle">Track and manage alumni employment and career progress</p>
                    </div>
                    
                    <div class="header-actions">
                        <div class="quick-actions-header">
                            {{-- <button class="quick-action-btn-sm" onclick="document.querySelector('[data-tab=builder]').click()">
                                <i class="fa-solid fa-pen-to-square"></i>
                                <span>Edit Tracer</span>
                            </button>
                            <button class="quick-action-btn-sm" onclick="document.querySelector('[data-tab=responses]').click()">
                                <i class="fa-solid fa-inbox"></i>
                                <span>View Responses</span>
                            </button>
                            <button class="quick-action-btn-sm" onclick="document.querySelector('[data-tab=analytics]').click()">
                                <i class="fa-solid fa-chart-bar"></i>
                                <span>Analytics</span>
                            </button> --}}
                            <button class="quick-action-btn-sm">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>Send Reminders</span>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="header-tabs-row">
                    <div class="tracer-tabs">
                        <button class="tracer-tab active" data-tab="dashboard">
                            <i class="fa-solid fa-chart-pie"></i>
                            <span>Dashboard</span>
                        </button>
                        <button class="tracer-tab" data-tab="builder">
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Tracer Builder</span>
                        </button>
                        <button class="tracer-tab" data-tab="responses">
                            <i class="fa-solid fa-inbox"></i>
                            <span>Responses</span>
                        </button>
                        <button class="tracer-tab" data-tab="analytics">
                            <i class="fa-solid fa-chart-bar"></i>
                            <span>Analytics</span>
                        </button>
                    </div>
                </div>
            </header>

            <!-- ============================================ -->
            <!-- TAB: DASHBOARD -->
            <!-- ============================================ -->
            <div class="tracer-panel active" id="dashboard-panel">
                <!-- Stats Overview -->
                <div class="stats-overview">
                    <div class="stat-card">
                        <div class="stat-icon blue">
                            <i class="fa-solid fa-users"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">247</span>
                            <span class="stat-label">Total Alumni</span>
                            <span class="stat-sub">+12 this month</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon green">
                            <i class="fa-solid fa-check-circle"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">156</span>
                            <span class="stat-label">Completed</span>
                            <span class="stat-sub">63.2% completion rate</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon amber">
                            <i class="fa-solid fa-hourglass-half"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">91</span>
                            <span class="stat-label">In Progress</span>
                            <span class="stat-sub">36.8% still answering</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon purple">
                            <i class="fa-solid fa-circle-question"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">49</span>
                            <span class="stat-label">Total Questions</span>
                            <span class="stat-sub">5 phases · 10 sections</span>
                        </div>
                    </div>
                </div>

                <!-- Replace the dashboard-grid-2col section -->
                <div class="dashboard-grid-2col">
                    <div class="chart-card">
                        <h3><i class="fa-solid fa-clock-rotate-left" style="color:#3b82f6;"></i> Recent Activity</h3>
                        <div class="activity-list">
                            <div class="activity-item">
                                <div class="activity-icon green"><i class="fa-solid fa-check"></i></div>
                                <div class="activity-content">
                                    <p class="activity-text"><strong>Maria Santos</strong> completed the tracer survey.</p>
                                    <span class="activity-time">2 hours ago</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon blue"><i class="fa-solid fa-pen"></i></div>
                                <div class="activity-content">
                                    <p class="activity-text"><strong>Jose Reyes</strong> updated employment status.</p>
                                    <span class="activity-time">5 hours ago</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon amber"><i class="fa-solid fa-plus"></i></div>
                                <div class="activity-content">
                                    <p class="activity-text">New phase <strong>"Professional Development"</strong> added.</p>
                                    <span class="activity-time">1 day ago</span>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon purple"><i class="fa-solid fa-download"></i></div>
                                <div class="activity-content">
                                    <p class="activity-text">Analytics report exported by <strong>Admin</strong>.</p>
                                    <span class="activity-time">2 days ago</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="chart-card">
                        <h3><i class="fa-solid fa-bullseye" style="color:#ef4444;"></i> Survey Completion Funnel</h3>
                        <div class="funnel-chart">
                            <div class="funnel-item" style="width: 100%;">
                                <span class="funnel-label">Invited</span>
                                <span class="funnel-value">247</span>
                            </div>
                            <div class="funnel-item" style="width: 82%;">
                                <span class="funnel-label">Opened</span>
                                <span class="funnel-value">203</span>
                            </div>
                            <div class="funnel-item" style="width: 63%;">
                                <span class="funnel-label">Completed</span>
                                <span class="funnel-value">156</span>
                            </div>
                            <div class="funnel-item" style="width: 48%;">
                                <span class="funnel-label">Verified</span>
                                <span class="funnel-value">118</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="charts-grid">
                    <!-- Phase Completion Chart -->
                    <div class="chart-card">
                        <h3>Phase Completion Rate</h3>
                        <div class="bar-chart">
                            <div class="bar-chart-item">
                                <span class="bar-value">87%</span>
                                <div class="bar-fill" style="height: 122px; background: #3b82f6;"></div>
                                <span class="bar-label">Personal</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">72%</span>
                                <div class="bar-fill" style="height: 101px; background: #10b981;"></div>
                                <span class="bar-label">Education</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">65%</span>
                                <div class="bar-fill" style="height: 91px; background: #f59e0b;"></div>
                                <span class="bar-label">Employment</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">48%</span>
                                <div class="bar-fill" style="height: 67px; background: #8b5cf6;"></div>
                                <span class="bar-label">Dev't</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">41%</span>
                                <div class="bar-fill" style="height: 57px; background: #ef4444;"></div>
                                <span class="bar-label">Assessment</span>
                            </div>
                        </div>
                    </div>

                    <!-- Employment Status -->
                    <div class="chart-card">
                        <h3>Employment Status</h3>
                        <div class="employment-list">
                            <div class="employment-item">
                                <div class="employment-header">
                                    <span class="employment-label"><span class="employment-dot" style="background:#10b981;"></span> Employed (Full-time)</span>
                                    <span class="employment-count">124</span>
                                </div>
                                <div class="employment-bar"><div class="employment-fill" style="width:50%; background:#10b981;"></div></div>
                            </div>
                            <div class="employment-item">
                                <div class="employment-header">
                                    <span class="employment-label"><span class="employment-dot" style="background:#3b82f6;"></span> Employed (Part-time)</span>
                                    <span class="employment-count">28</span>
                                </div>
                                <div class="employment-bar"><div class="employment-fill" style="width:11%; background:#3b82f6;"></div></div>
                            </div>
                            <div class="employment-item">
                                <div class="employment-header">
                                    <span class="employment-label"><span class="employment-dot" style="background:#f59e0b;"></span> Self-employed</span>
                                    <span class="employment-count">18</span>
                                </div>
                                <div class="employment-bar"><div class="employment-fill" style="width:7%; background:#f59e0b;"></div></div>
                            </div>
                            <div class="employment-item">
                                <div class="employment-header">
                                    <span class="employment-label"><span class="employment-dot" style="background:#8b5cf6;"></span> Continuing Education</span>
                                    <span class="employment-count">35</span>
                                </div>
                                <div class="employment-bar"><div class="employment-fill" style="width:14%; background:#8b5cf6;"></div></div>
                            </div>
                            <div class="employment-item">
                                <div class="employment-header">
                                    <span class="employment-label"><span class="employment-dot" style="background:#ef4444;"></span> Unemployed</span>
                                    <span class="employment-count">20</span>
                                </div>
                                <div class="employment-bar"><div class="employment-fill" style="width:8%; background:#ef4444;"></div></div>
                            </div>
                            <div class="employment-item">
                                <div class="employment-header">
                                    <span class="employment-label"><span class="employment-dot" style="background:#06b6d4;"></span> OFW</span>
                                    <span class="employment-count">22</span>
                                </div>
                                <div class="employment-bar"><div class="employment-fill" style="width:9%; background:#06b6d4;"></div></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Submissions Table -->
                <div class="recent-submissions">
                    <div class="recent-submissions-header">
                        <h3>Recent Submissions</h3>
                        <div class="header-actions-table">
                            <select class="filter-select-sm">
                                <option>All Programs</option>
                                <option>BS IT</option>
                                <option>BS ME</option>
                                <option>BS Accountancy</option>
                            </select>
                            <a href="#" class="view-all-link">View All →</a>
                        </div>
                    </div>
                    <div style="overflow-x: auto;">
                        <table class="tracer-table">
                            <thead>
                                <tr>
                                    <th>Alumni</th>
                                    <th>Program</th>
                                    <th>Year</th>
                                    <th>Progress</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="alumni-info">
                                            <div class="alumni-avatar">MS</div>
                                            <span class="alumni-name">Maria Santos</span>
                                        </div>
                                    </td>
                                    <td>BS IT</td>
                                    <td>2023</td>
                                    <td>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:100%; background:#10b981;"></div></div>
                                            <span class="progress-text" style="color:#10b981;">100%</span>
                                        </div>
                                    </td>
                                    <td>2025-06-14</td>
                                    <td><span class="status-badge complete">Complete</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="alumni-info">
                                            <div class="alumni-avatar">JR</div>
                                            <span class="alumni-name">Jose Reyes</span>
                                        </div>
                                    </td>
                                    <td>BS ME</td>
                                    <td>2022</td>
                                    <td>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:60%; background:#f59e0b;"></div></div>
                                            <span class="progress-text" style="color:#f59e0b;">60%</span>
                                        </div>
                                    </td>
                                    <td>2025-06-13</td>
                                    <td><span class="status-badge in-progress">In Progress</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="alumni-info">
                                            <div class="alumni-avatar">AC</div>
                                            <span class="alumni-name">Ana Cruz</span>
                                        </div>
                                    </td>
                                    <td>BS Accountancy</td>
                                    <td>2023</td>
                                    <td>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:40%; background:#f59e0b;"></div></div>
                                            <span class="progress-text" style="color:#f59e0b;">40%</span>
                                        </div>
                                    </td>
                                    <td>2025-06-12</td>
                                    <td><span class="status-badge in-progress">In Progress</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="alumni-info">
                                            <div class="alumni-avatar">CM</div>
                                            <span class="alumni-name">Carlo Mendoza</span>
                                        </div>
                                    </td>
                                    <td>BS CS</td>
                                    <td>2024</td>
                                    <td>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:100%; background:#10b981;"></div></div>
                                            <span class="progress-text" style="color:#10b981;">100%</span>
                                        </div>
                                    </td>
                                    <td>2025-06-11</td>
                                    <td><span class="status-badge complete">Complete</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="alumni-info">
                                            <div class="alumni-avatar">LB</div>
                                            <span class="alumni-name">Liza Bautista</span>
                                        </div>
                                    </td>
                                    <td>BS Tourism</td>
                                    <td>2022</td>
                                    <td>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:20%; background:#f59e0b;"></div></div>
                                            <span class="progress-text" style="color:#f59e0b;">20%</span>
                                        </div>
                                    </td>
                                    <td>2025-06-10</td>
                                    <td><span class="status-badge in-progress">In Progress</span></td>
                                </tr>
                                <tr>
                                    <td>
                                        <div class="alumni-info">
                                            <div class="alumni-avatar">RV</div>
                                            <span class="alumni-name">Ryan Villanueva</span>
                                        </div>
                                    </td>
                                    <td>BS CE</td>
                                    <td>2021</td>
                                    <td>
                                        <div class="progress-bar-wrapper">
                                            <div class="progress-bar-track"><div class="progress-bar-fill" style="width:100%; background:#10b981;"></div></div>
                                            <span class="progress-text" style="color:#10b981;">100%</span>
                                        </div>
                                    </td>
                                    <td>2025-06-09</td>
                                    <td><span class="status-badge complete">Complete</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TAB: TRACER BUILDER (Unchanged) -->
            <!-- ============================================ -->
            <div class="tracer-panel" id="builder-panel">
                <div class="builder-layout">
                    <!-- Phases Sidebar -->
                    <div class="phases-sidebar">
                        <div class="phases-header">
                            <h3>Phases</h3>
                            <button class="btn-add-phase" onclick="openPhaseModal()">
                                <i class="fa-solid fa-plus"></i> Add
                            </button>
                        </div>
                        <div class="phases-list" id="phasesList">
                            <!-- Phase cards will be rendered by JS -->
                        </div>
                    </div>

                    <!-- Builder Content Area -->
                    <div class="builder-content" id="builderContent">
                        <div class="empty-builder-state" id="emptyBuilderState">
                            <i class="fa-solid fa-layer-group empty-builder-icon"></i>
                            <h3>Select a phase to manage its content</h3>
                            <p>Choose a phase from the left panel or create a new one to get started.</p>
                        </div>
                        <div id="phaseDetailContent" style="display: none;">
                            <!-- Phase detail content rendered by JS -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TAB: RESPONSES (Unchanged) -->
            <!-- ============================================ -->
            <div class="tracer-panel" id="responses-panel">
                <div class="responses-toolbar">
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" class="search-input" placeholder="Search by name or program..." id="responsesSearch">
                    </div>
                    <select class="filter-select" id="responsesFilter">
                        <option value="all">All Status</option>
                        <option value="complete">Complete</option>
                        <option value="in-progress">In Progress</option>
                    </select>
                    <button class="btn-export">
                        <i class="fa-solid fa-download"></i> Export CSV
                    </button>
                </div>

                <div class="responses-table-container">
                    <table class="tracer-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Alumni</th>
                                <th>Program</th>
                                <th>Year</th>
                                <th>Completion</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="responsesTableBody">
                            <!-- Rendered by JS -->
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <span id="responsesCount">8 results</span>
                </div>
            </div>

            <!-- ============================================ -->
            <!-- TAB: ANALYTICS -->
            <!-- ============================================ -->
            <div class="tracer-panel" id="analytics-panel">
                <!-- Analytics Header -->
                <div class="analytics-header">
                    <div>
                        <h2 class="analytics-title">Tracer Analytics Overview</h2>
                        <p class="analytics-subtitle">Insights from alumni employment and feedback data</p>
                    </div>
                    <button class="btn-export">
                        <i class="fa-solid fa-file-export"></i> Export Full Report
                    </button>
                </div>

                <!-- KPI Cards with Trends -->
                <div class="analytics-kpi-grid">
                    <div class="kpi-card">
                        <div class="kpi-icon green"><i class="fa-solid fa-percent"></i></div>
                        <div class="kpi-value" style="color:#10b981;">87.2%</div>
                        <div class="kpi-label">Response Rate</div>
                        <div class="kpi-trend up"><i class="fa-solid fa-arrow-up"></i> 5.2% from last month</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon blue"><i class="fa-solid fa-chart-simple"></i></div>
                        <div class="kpi-value" style="color:#3b82f6;">63%</div>
                        <div class="kpi-label">Avg. Completion</div>
                        <div class="kpi-trend up"><i class="fa-solid fa-arrow-up"></i> 2.1% from last month</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon amber"><i class="fa-solid fa-star"></i></div>
                        <div class="kpi-value" style="color:#f59e0b;">4.1 / 5</div>
                        <div class="kpi-label">Avg. Rating (Overall)</div>
                        <div class="kpi-trend down"><i class="fa-solid fa-arrow-down"></i> 0.2 from last month</div>
                    </div>
                    <div class="kpi-card">
                        <div class="kpi-icon purple"><i class="fa-solid fa-briefcase"></i></div>
                        <div class="kpi-value" style="color:#8b5cf6;">68.4%</div>
                        <div class="kpi-label">Job Relevance</div>
                        <div class="kpi-trend up"><i class="fa-solid fa-arrow-up"></i> 1.5% from last month</div>
                    </div>
                </div>

                <!-- Analytics Charts Grid -->
                <div class="analytics-grid">
                    <!-- Monthly Submissions -->
                    <div class="analytics-card">
                        <h3>Monthly Submissions</h3>
                        <div class="bar-chart">
                            <div class="bar-chart-item">
                                <span class="bar-value">12</span>
                                <div class="bar-fill" style="height: 43px; background: #32418C;"></div>
                                <span class="bar-label">Jan</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">18</span>
                                <div class="bar-fill" style="height: 64px; background: #32418C;"></div>
                                <span class="bar-label">Feb</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">25</span>
                                <div class="bar-fill" style="height: 89px; background: #32418C;"></div>
                                <span class="bar-label">Mar</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">31</span>
                                <div class="bar-fill" style="height: 111px; background: #32418C;"></div>
                                <span class="bar-label">Apr</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">28</span>
                                <div class="bar-fill" style="height: 100px; background: #32418C;"></div>
                                <span class="bar-label">May</span>
                            </div>
                            <div class="bar-chart-item">
                                <span class="bar-value">42</span>
                                <div class="bar-fill" style="height: 150px; background: #32418C;"></div>
                                <span class="bar-label">Jun</span>
                            </div>
                        </div>
                    </div>

                    <!-- Salary Distribution -->
                    <div class="analytics-card">
                        <h3>Monthly Salary Distribution</h3>
                        <div class="h-bar-list">
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#FBD117;"></span> Below ₱15k</span>
                                    <span class="h-bar-percent">18%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:18%; background:#FBD117;"></div></div>
                            </div>
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#FBD117;"></span> ₱15–25k</span>
                                    <span class="h-bar-percent">34%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:34%; background:#FBD117;"></div></div>
                            </div>
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#FBD117;"></span> ₱25–50k</span>
                                    <span class="h-bar-percent">29%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:29%; background:#FBD117;"></div></div>
                            </div>
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#FBD117;"></span> ₱50–100k</span>
                                    <span class="h-bar-percent">14%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:14%; background:#FBD117;"></div></div>
                            </div>
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#FBD117;"></span> Above ₱100k</span>
                                    <span class="h-bar-percent">5%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:5%; background:#FBD117;"></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- Average Ratings -->
                    <div class="analytics-card">
                        <h3>Average Ratings by Criterion</h3>
                        <div class="rating-list">
                            <div class="rating-item">
                                <div class="rating-header">
                                    <span class="rating-label">Overall Education Quality</span>
                                    <span class="rating-score"><i class="fa-solid fa-star"></i> 4.1 <span>/5</span></span>
                                </div>
                                <div class="rating-bar-track"><div class="rating-bar-fill" style="width:82%;"></div></div>
                            </div>
                            <div class="rating-item">
                                <div class="rating-header">
                                    <span class="rating-label">Curriculum Relevance</span>
                                    <span class="rating-score"><i class="fa-solid fa-star"></i> 3.8 <span>/5</span></span>
                                </div>
                                <div class="rating-bar-track"><div class="rating-bar-fill" style="width:76%;"></div></div>
                            </div>
                            <div class="rating-item">
                                <div class="rating-header">
                                    <span class="rating-label">Teaching Effectiveness</span>
                                    <span class="rating-score"><i class="fa-solid fa-star"></i> 4.3 <span>/5</span></span>
                                </div>
                                <div class="rating-bar-track"><div class="rating-bar-fill" style="width:86%;"></div></div>
                            </div>
                            <div class="rating-item">
                                <div class="rating-header">
                                    <span class="rating-label">Facilities & Resources</span>
                                    <span class="rating-score"><i class="fa-solid fa-star"></i> 3.5 <span>/5</span></span>
                                </div>
                                <div class="rating-bar-track"><div class="rating-bar-fill" style="width:70%;"></div></div>
                            </div>
                            <div class="rating-item">
                                <div class="rating-header">
                                    <span class="rating-label">Career Guidance Services</span>
                                    <span class="rating-score"><i class="fa-solid fa-star"></i> 3.2 <span>/5</span></span>
                                </div>
                                <div class="rating-bar-track"><div class="rating-bar-fill" style="width:64%;"></div></div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Relevance -->
                    <div class="analytics-card">
                        <h3>Job Relevance to Degree</h3>
                        <div class="h-bar-list">
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#10b981;"></span> Directly related</span>
                                    <span class="h-bar-percent" style="color:#10b981;">40%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:40%; background:#10b981;"></div></div>
                            </div>
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#3b82f6;"></span> Somewhat related</span>
                                    <span class="h-bar-percent" style="color:#3b82f6;">28%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:28%; background:#3b82f6;"></div></div>
                            </div>
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#f59e0b;"></span> Not related</span>
                                    <span class="h-bar-percent" style="color:#f59e0b;">22%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:22%; background:#f59e0b;"></div></div>
                            </div>
                            <div class="h-bar-item">
                                <div class="h-bar-header">
                                    <span class="h-bar-label"><span class="h-bar-dot" style="background:#ef4444;"></span> Not yet employed</span>
                                    <span class="h-bar-percent" style="color:#ef4444;">10%</span>
                                </div>
                                <div class="h-bar-track"><div class="h-bar-fill" style="width:10%; background:#ef4444;"></div></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- ============================================ -->
    <!-- UPDATED QUESTION MODAL -->
    <!-- ============================================ -->
    <div class="modal-overlay" id="questionModal">
        <div class="modal-dialog" style="max-width: 700px;">
            <div class="modal-header">
                <h3 id="questionModalTitle">Add New Question</h3>
                <button class="modal-close" onclick="closeQuestionModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <!-- Question Label -->
                <div class="form-group">
                    <label>Question Label <span class="required">*</span></label>
                    <textarea class="form-control" id="qLabel" rows="2" placeholder="Enter the question text..."></textarea>
                </div>

                <!-- Question Type -->
                <div class="form-group">
                    <label>Question Type</label>
                    <select class="form-control" id="qType" onchange="handleTypeChange()">
                        <option value="short_answer">Short Answer</option>
                        <option value="paragraph">Paragraph</option>
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="checkboxes">Checkboxes (Multiple Selection)</option>
                        <option value="dropdown">Dropdown</option>
                        <option value="file_upload">File Upload</option>
                        <option value="likert_scale">Likert Scale</option>
                        <option value="multiple_choice_grid">Multiple Choice Grid</option>
                    </select>
                    <p class="help-text" id="typeHelp">Short single-line text input.</p>
                </div>

                <!-- Options for Multiple Choice / Checkboxes / Dropdown -->
                <div class="form-group" id="optionsGroup" style="display:none;">
                    <label>Answer Options</label>
                    <div id="optionsList" class="options-list"></div>
                    <div class="options-input-row">
                        <input type="text" class="form-control" id="newOption" placeholder="Type an option and press Enter..." onkeydown="handleOptionKeydown(event)">
                        <button class="btn btn-primary" onclick="addOption()" style="white-space:nowrap;">Add</button>
                    </div>
                </div>

                <!-- Placeholder for Short Answer / Paragraph -->
                <div class="form-group" id="placeholderGroup">
                    <label>Placeholder Text</label>
                    <input type="text" class="form-control" id="qPlaceholder" placeholder="e.g. Enter your answer here...">
                </div>

                <!-- File Upload Settings -->
                <div class="form-group" id="fileUploadGroup" style="display:none;">
                    <label>Allowed File Types</label>
                    <div class="checkbox-group">
                        <label class="checkbox-label"><input type="checkbox" value="pdf" class="file-type-check"> PDF</label>
                        <label class="checkbox-label"><input type="checkbox" value="doc" class="file-type-check"> Word (DOC/DOCX)</label>
                        <label class="checkbox-label"><input type="checkbox" value="jpg" class="file-type-check"> Image (JPG/PNG)</label>
                        <label class="checkbox-label"><input type="checkbox" value="xlsx" class="file-type-check"> Excel (XLS/XLSX)</label>
                    </div>
                    <div class="form-group" style="margin-top: 1rem;">
                        <label>Max File Size (MB)</label>
                        <input type="number" class="form-control" id="fileMaxSize" placeholder="10" min="1" max="50" value="10">
                    </div>
                </div>

                <!-- Likert Scale / Multiple Choice Grid Settings -->
                <div class="form-group" id="gridSettingsGroup" style="display:none;">
                    <!-- Rows (Statements) -->
                    <div class="form-group">
                        <label>Statements (Rows)</label>
                        <div id="gridRowsList" class="options-list"></div>
                        <div class="options-input-row">
                            <input type="text" class="form-control" id="newGridRow" placeholder="Type a statement and press Enter..." onkeydown="handleGridRowKeydown(event)">
                            <button class="btn btn-primary" onclick="addGridRow()" style="white-space:nowrap;">Add Row</button>
                        </div>
                    </div>

                    <!-- Columns (Options/Scale) -->
                    <div class="form-group">
                        <label id="gridColumnsLabel">Scale Options (Columns)</label>
                        <div id="gridColumnsList" class="options-list"></div>
                        <div class="options-input-row">
                            <input type="text" class="form-control" id="newGridColumn" placeholder="Type an option and press Enter..." onkeydown="handleGridColumnKeydown(event)">
                            <button class="btn btn-primary" onclick="addGridColumn()" style="white-space:nowrap;">Add Column</button>
                        </div>
                    </div>
                </div>

                <!-- Required Toggle -->
                <div class="toggle-row">
                    <div>
                        <p class="toggle-label">Required Question</p>
                        <p class="toggle-sub">Alumni must answer before submitting</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="qRequired" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeQuestionModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveQuestion()">Save Question</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="phaseModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="phaseModalTitle">Add New Phase</h3>
                <button class="modal-close" onclick="closePhaseModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Phase Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="phaseTitle" placeholder="e.g. Personal Profile">
                </div>
                <div class="form-group">
                    <label>Subtitle</label>
                    <input type="text" class="form-control" id="phaseSubtitle" placeholder="e.g. Basic & contact info">
                </div>
                <div class="form-group">
                    <label>Icon</label>
                    <div class="icon-grid" id="iconGrid"></div>
                </div>
                <div class="form-group">
                    <label>Accent Color</label>
                    <div class="color-grid" id="colorGrid"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closePhaseModal()">Cancel</button>
                <button class="btn btn-primary" onclick="savePhase()">Save Phase</button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="sectionModal">
        <div class="modal-dialog">
            <div class="modal-header">
                <h3 id="sectionModalTitle">Add New Section</h3>
                <button class="modal-close" onclick="closeSectionModal()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label>Section Title <span class="required">*</span></label>
                    <input type="text" class="form-control" id="sectionTitle" placeholder="e.g. Basic Information">
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <input type="text" class="form-control" id="sectionDesc" placeholder="Brief description of this section">
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeSectionModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveSection()">Save Section</button>
            </div>
        </div>
    </div>

    <script>
    // ═══════════════════════════════════════
    // GLOBAL STATE
    // ═══════════════════════════════════════
    
    const typeHelpText = {
        short_answer: 'Short single-line text input for brief answers.',
        paragraph: 'Multi-line text area for longer responses.',
        multiple_choice: 'Alumni pick exactly one answer from the options.',
        checkboxes: 'Alumni can select multiple answers.',
        dropdown: 'Alumni choose one option from a dropdown menu.',
        file_upload: 'Alumni can upload a file (PDF, image, document, etc.).',
        likert_scale: 'Matrix with statements (rows) and a fixed scale (columns). Default scale: 1-5.',
        multiple_choice_grid: 'Matrix with statements (rows) and customizable option columns. Like Likert but you define the column labels.'
    };

    const typeLabels = {
        short_answer: 'Short Answer',
        paragraph: 'Paragraph',
        multiple_choice: 'Multiple Choice',
        checkboxes: 'Checkboxes',
        dropdown: 'Dropdown',
        file_upload: 'File Upload',
        likert_scale: 'Likert Scale',
        multiple_choice_grid: 'Multiple Choice Grid'
    };

    const typeIcons = {
        short_answer: 'fa-i-cursor',
        paragraph: 'fa-align-left',
        multiple_choice: 'fa-circle-dot',
        checkboxes: 'fa-check-square',
        dropdown: 'fa-chevron-down',
        file_upload: 'fa-cloud-arrow-up',
        likert_scale: 'fa-table-list',
        multiple_choice_grid: 'fa-border-all'
    };

    const DEFAULT_LIKERT_COLUMNS = [
        '1 - Strongly Disagree',
        '2 - Disagree', 
        '3 - Neutral',
        '4 - Agree',
        '5 - Strongly Agree'
    ];

    const icons = [
        { key: 'fa-user', label: 'Person' },
        { key: 'fa-book', label: 'Book' },
        { key: 'fa-briefcase', label: 'Briefcase' },
        { key: 'fa-bolt', label: 'Lightning' },
        { key: 'fa-clipboard-check', label: 'Clipboard' },
        { key: 'fa-graduation-cap', label: 'Graduation' },
        { key: 'fa-chart-line', label: 'Growth' },
        { key: 'fa-star', label: 'Stars' }
    ];

    const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#1f2b67', '#ec4899'];

    let phases = [
        {
            id: 1, title: 'Personal Profile', subtitle: 'Basic & contact information',
            icon: 'fa-user', color: '#3b82f6',
            sections: [
                {
                    id: '1-0', title: 'Basic Information', description: 'Personal details',
                    questions: [
                        { id: '1-0-1', label: 'Full Name', type: 'short_answer', placeholder: 'Juan dela Cruz', required: true },
                        { id: '1-0-2', label: 'Date of Birth', type: 'short_answer', placeholder: 'MM/DD/YYYY', required: true },
                        { id: '1-0-3', label: 'Civil Status', type: 'multiple_choice', options: ['Single','Married','Widowed','Separated'], required: true },
                        { id: '1-0-4', label: 'Gender', type: 'multiple_choice', options: ['Male','Female','Prefer not to say'], required: true },
                        { id: '1-0-5', label: 'Region of Residence', type: 'dropdown', options: ['Region III – Central Luzon','Region IV-A – CALABARZON','NCR – Metro Manila','Others'], required: true }
                    ]
                },
                {
                    id: '1-1', title: 'Contact Details', description: 'How to reach you',
                    questions: [
                        { id: '1-1-1', label: 'Mobile Number', type: 'short_answer', placeholder: '09XX-XXX-XXXX', required: true },
                        { id: '1-1-2', label: 'Email Address', type: 'short_answer', placeholder: 'juandelacruz@email.com', required: true },
                        { id: '1-1-3', label: 'Present Address', type: 'paragraph', placeholder: 'Street, Barangay, City/Municipality', required: true },
                        { id: '1-1-4', label: 'Upload your Resume/CV', type: 'file_upload', fileTypes: ['pdf', 'doc'], maxSize: 10, required: false }
                    ]
                }
            ]
        },
        {
            id: 2, title: 'Educational Background', subtitle: 'Academic history at NU Lipa',
            icon: 'fa-book', color: '#10b981',
            sections: [
                {
                    id: '2-0', title: 'Academic History', description: 'Your credentials',
                    questions: [
                        { id: '2-0-1', label: 'College / Department', type: 'dropdown', options: ['College of Engineering','College of Business & Accountancy','College of Computing and IT','College of Arts and Sciences','Others'], required: true },
                        { id: '2-0-2', label: 'Degree Program', type: 'short_answer', placeholder: 'e.g. BS Computer Science', required: true },
                        { id: '2-0-3', label: 'Year Graduated', type: 'dropdown', options: ['2024','2023','2022','2021','2020','2019','2018','2017','2016 or earlier'], required: true },
                        { id: '2-0-4', label: 'Academic Honors Received', type: 'multiple_choice', options: ['Summa Cum Laude','Magna Cum Laude','Cum Laude','With Honors','None'], required: true },
                        { id: '2-0-5', label: 'Did you graduate on time?', type: 'multiple_choice', options: ['Yes, on schedule','Extended by 1 semester','Extended by 1 year or more'], required: true }
                    ]
                },
                {
                    id: '2-1', title: 'Further Studies', description: 'Post-graduate education',
                    questions: [
                        { id: '2-1-1', label: 'Are you pursuing graduate studies?', type: 'multiple_choice', options: ['Yes, currently enrolled','Planning to enroll','Already finished','Not interested'], required: true },
                        { id: '2-1-2', label: 'Graduate Program (if applicable)', type: 'short_answer', placeholder: 'e.g. Master in IT' },
                        { id: '2-1-3', label: 'Licensure Exams Passed', type: 'checkboxes', options: ['Board Exam (PRC)','Civil Service Exam','CPA Board Exam','Engineering Board Exam','None / Not Applicable'] }
                    ]
                }
            ]
        },
        {
            id: 3, title: 'Employment Profile', subtitle: 'Career and work details',
            icon: 'fa-briefcase', color: '#f59e0b',
            sections: [
                {
                    id: '3-0', title: 'Current Employment', description: 'Present work situation',
                    questions: [
                        { id: '3-0-1', label: 'Employment Status', type: 'multiple_choice', options: ['Employed (full-time)','Employed (part-time)','Self-employed / Freelance','Unemployed – seeking work','Continuing Education','OFW'], required: true },
                        { id: '3-0-2', label: 'Job Title / Position', type: 'short_answer', placeholder: 'e.g. Software Developer' },
                        { id: '3-0-3', label: 'Company / Employer', type: 'short_answer', placeholder: 'Company or organization name' },
                        { id: '3-0-4', label: 'Industry / Type of Work', type: 'dropdown', options: ['Information Technology','Business / Finance','Engineering','Healthcare','Education / Academe','Government','Tourism / Hospitality','Others'] },
                        { id: '3-0-5', label: 'Monthly Salary Range', type: 'multiple_choice', options: ['Below ₱15,000','₱15,000 – ₱25,000','₱25,001 – ₱50,000','₱50,001 – ₱100,000','Above ₱100,000','Prefer not to disclose'] }
                    ]
                },
                {
                    id: '3-1', title: 'First Job Details', description: 'Journey to first employment',
                    questions: [
                        { id: '3-1-1', label: 'How long to find your first job?', type: 'multiple_choice', options: ['Before graduation','Within 1 month','1–6 months','6 months–1 year','More than 1 year','Still looking'], required: true },
                        { id: '3-1-2', label: 'How did you find your first job?', type: 'multiple_choice', options: ['Online job portal','School placement / OJT','Referral from family or friends','Walk-in / direct application','Self-employed'], required: true },
                        { id: '3-1-3', label: 'Is your job related to your degree?', type: 'multiple_choice', options: ['Yes, directly related','Somewhat related','Not related at all','Not yet employed'], required: true }
                    ]
                }
            ]
        },
        {
            id: 4, title: 'Professional Development', subtitle: 'Skills, training & growth',
            icon: 'fa-bolt', color: '#8b5cf6',
            sections: [
                {
                    id: '4-0', title: 'Skills & Competencies', description: 'What you learned and applied',
                    questions: [
                        { id: '4-0-1', label: 'Technical skills you use most', type: 'checkboxes', options: ['Programming / Coding','Data Analysis','Design / Drawing','Engineering Calculation','Accounting / Bookkeeping','Project Management','Research / Writing'] },
                        { 
                            id: '4-0-2', 
                            label: 'Rate the following competencies you gained from NU Lipa:', 
                            type: 'likert_scale', 
                            required: true,
                            gridRows: [
                                'Critical Thinking',
                                'Communication Skills', 
                                'Problem Solving',
                                'Teamwork & Collaboration',
                                'Technical/Professional Skills'
                            ],
                            gridColumns: DEFAULT_LIKERT_COLUMNS
                        }
                    ]
                },
                {
                    id: '4-1', title: 'Trainings & Certifications', description: 'Professional development',
                    questions: [
                        { id: '4-1-1', label: 'Attended professional trainings / seminars?', type: 'multiple_choice', options: ['Yes, many times','Yes, once or twice','Not yet, but planning to','No'], required: true },
                        { id: '4-1-2', label: 'Types of development activities', type: 'checkboxes', options: ['Technical / skills training','Leadership / management','Industry certification','Online courses','Government-sponsored (TESDA)','None'] },
                        { id: '4-1-3', label: 'Remarks about your professional growth', type: 'paragraph', placeholder: 'Share your career development journey...' }
                    ]
                }
            ]
        },
        {
            id: 5, title: 'Program Assessment', subtitle: 'Evaluate your NU Lipa education',
            icon: 'fa-clipboard-check', color: '#ef4444',
            sections: [
                {
                    id: '5-0', title: 'Curriculum Evaluation', description: 'Help us improve our programs',
                    questions: [
                        { 
                            id: '5-0-1', 
                            label: 'Rate the following aspects of your NU Lipa education:', 
                            type: 'likert_scale', 
                            required: true,
                            gridRows: [
                                'Overall quality of education',
                                'Relevance of curriculum to your career',
                                'Effectiveness of your instructors',
                                'Adequacy of facilities and resources',
                                'Quality of career guidance services'
                            ],
                            gridColumns: DEFAULT_LIKERT_COLUMNS
                        }
                    ]
                },
                {
                    id: '5-1', title: 'Suggestions & Recommendations', description: 'Your feedback shapes our future',
                    questions: [
                        { id: '5-1-1', label: 'Aspects of the curriculum to improve', type: 'checkboxes', options: ['More industry-relevant subjects','More practical/OJT exposure','Updated course materials','Better lab equipment','Stronger industry linkages','Better career counseling'] },
                        { id: '5-1-2', label: 'Would you recommend NU Lipa?', type: 'multiple_choice', options: ['Definitely yes','Probably yes','Probably not','Definitely not'], required: true },
                        { id: '5-1-3', label: 'Other suggestions or comments', type: 'paragraph', placeholder: 'Share your thoughts and recommendations...' }
                    ]
                }
            ]
        }
    ];

    const MOCK_RESPONSES = [
        { id: 1, name: 'Maria Santos', program: 'BS IT', year: 2023, completion: 100, date: '2025-06-14', status: 'complete' },
        { id: 2, name: 'Jose Reyes', program: 'BS ME', year: 2022, completion: 60, date: '2025-06-13', status: 'in-progress' },
        { id: 3, name: 'Ana Cruz', program: 'BS Accountancy', year: 2023, completion: 40, date: '2025-06-12', status: 'in-progress' },
        { id: 4, name: 'Carlo Mendoza', program: 'BS CS', year: 2024, completion: 100, date: '2025-06-11', status: 'complete' },
        { id: 5, name: 'Liza Bautista', program: 'BS Tourism', year: 2022, completion: 20, date: '2025-06-10', status: 'in-progress' },
        { id: 6, name: 'Ryan Villanueva', program: 'BS CE', year: 2021, completion: 100, date: '2025-06-09', status: 'complete' },
        { id: 7, name: 'Grace Domingo', program: 'BS BA', year: 2023, completion: 80, date: '2025-06-08', status: 'in-progress' },
        { id: 8, name: 'Ken Flores', program: 'BS EE', year: 2022, completion: 100, date: '2025-06-07', status: 'complete' }
    ];

    let selectedPhaseId = phases.length > 0 ? phases[0].id : null;
    let expandedSections = new Set();
    let currentEditQuestion = null;
    let currentEditPhase = null;
    let currentEditSection = null;
    let tempOptions = [];
    let tempGridRows = [];
    let tempGridColumns = [];
    let selectedIcon = 'fa-user';
    let selectedColor = '#3b82f6';

    // ═══════════════════════════════════════
    // MOBILE MENU
    // ═══════════════════════════════════════

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

    window.addEventListener('resize', function() {
        if (window.innerWidth > 1024) {
            document.getElementById('adminSidebar').classList.remove('mobile-open');
            document.getElementById('mobileOverlay').classList.remove('active');
            document.body.style.overflow = '';
        }
    });

    // ═══════════════════════════════════════
    // TAB SWITCHING
    // ═══════════════════════════════════════

    document.querySelectorAll('.tracer-tab').forEach(tab => {
        tab.addEventListener('click', function() {
            document.querySelectorAll('.tracer-tab').forEach(t => t.classList.remove('active'));
            document.querySelectorAll('.tracer-panel').forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            document.getElementById(this.dataset.tab + '-panel').classList.add('active');

            if (this.dataset.tab === 'builder') renderBuilder();
            if (this.dataset.tab === 'responses') renderResponsesTable();
        });
    });

    // ═══════════════════════════════════════
    // BUILDER RENDERING
    // ═══════════════════════════════════════

    function renderPhasesList() {
        const container = document.getElementById('phasesList');
        container.innerHTML = phases.map(phase => {
            const isSel = selectedPhaseId === phase.id;
            const totalQ = phase.sections.reduce((s, sec) => s + sec.questions.length, 0);
            return `
                <div class="phase-card ${isSel ? 'active' : ''}" onclick="selectPhase(${phase.id})">
                    <div class="phase-card-header">
                        <div class="phase-card-icon" style="background:${isSel ? 'rgba(255,255,255,0.15)' : phase.color + '20'}; color:${isSel ? '#fff' : phase.color};">
                            <i class="fa-solid ${phase.icon}"></i>
                        </div>
                        <div class="phase-card-info">
                            <p class="phase-card-title">${phase.title}</p>
                            <p class="phase-card-meta">${phase.sections.length} sections · ${totalQ} questions</p>
                        </div>
                        <div class="phase-card-actions">
                            <button onclick="event.stopPropagation(); openPhaseModal(${phase.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                            <button onclick="event.stopPropagation(); deletePhase(${phase.id})" title="Delete"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function selectPhase(id) {
        selectedPhaseId = id;
        renderBuilder();
    }

    function renderBuilder() {
        renderPhasesList();
        const phase = phases.find(p => p.id === selectedPhaseId);
        const emptyState = document.getElementById('emptyBuilderState');
        const detailContent = document.getElementById('phaseDetailContent');

        if (!phase) {
            emptyState.style.display = 'block';
            detailContent.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        detailContent.style.display = 'block';

        detailContent.innerHTML = `
            <div class="builder-content-header">
                <div class="builder-phase-info">
                    <div class="builder-phase-icon" style="background:${phase.color}20; color:${phase.color};">
                        <i class="fa-solid ${phase.icon}"></i>
                    </div>
                    <div class="builder-phase-details">
                        <h2>${phase.title}</h2>
                        <p>${phase.subtitle || 'No subtitle'}</p>
                    </div>
                </div>
                <button class="btn-add-section" onclick="openSectionModal(null, ${phase.id})">
                    <i class="fa-solid fa-plus"></i> Add Section
                </button>
            </div>
            ${phase.sections.length === 0 ? `
                <div class="empty-builder-state" style="border: 2px dashed var(--gray-200); border-radius: var(--radius-xl); padding: 3rem;">
                    <i class="fa-solid fa-folder-plus" style="font-size: 3rem; color: var(--gray-300);"></i>
                    <p style="color: var(--gray-500); font-weight: 500; margin-top: 0.75rem;">No sections yet</p>
                    <p style="color: var(--gray-400); font-size: 0.875rem; margin-top: 0.25rem;">Click "Add Section" to get started.</p>
                </div>
            ` : `
                <div class="sections-list">
                    ${phase.sections.map((section, secIdx) => {
                        const isOpen = expandedSections.has(section.id);
                        return `
                            <div class="section-card ${isOpen ? 'expanded' : ''}">
                                <div class="section-header" onclick="toggleSection('${section.id}')">
                                    <div class="section-number" style="background:${phase.color};">${secIdx + 1}</div>
                                    <div class="section-info">
                                        <h4>${section.title}</h4>
                                        <p>${section.description ? section.description + ' · ' : ''}${section.questions.length} question${section.questions.length !== 1 ? 's' : ''}</p>
                                    </div>
                                    <div class="section-actions" onclick="event.stopPropagation();">
                                        <button class="btn-icon" onclick="openSectionModal('${section.id}', ${phase.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                        <button class="btn-icon delete" onclick="deleteSection(${phase.id}, '${section.id}')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                    </div>
                                    <i class="fa-solid fa-chevron-${isOpen ? 'up' : 'down'} section-toggle"></i>
                                </div>
                                <div class="section-body">
                                    ${section.questions.length === 0 ? '<p style="color: var(--gray-400); text-align: center; padding: 1.5rem;">No questions yet.</p>' : `
                                        <div class="questions-list">
                                            ${section.questions.map((q, qi) => `
                                                <div class="question-item">
                                                    <i class="fa-solid fa-grip-vertical question-drag"></i>
                                                    <div class="question-number" style="background:${phase.color};">${qi + 1}</div>
                                                    <div class="question-content">
                                                        <p class="question-label-text">${q.label}</p>
                                                        <div class="question-meta">
                                                            <span class="question-type-badge"><i class="fa-solid ${typeIcons[q.type] || 'fa-question'}"></i> ${typeLabels[q.type] || q.type}</span>
                                                            ${q.required ? '<span class="required-badge">Required</span>' : ''}
                                                            ${q.options ? `<span style="font-size:0.6875rem;color:var(--gray-400);">${q.options.length} options</span>` : ''}
                                                            ${q.gridRows ? `<span style="font-size:0.6875rem;color:var(--gray-400);">${q.gridRows.length} rows</span>` : ''}
                                                        </div>
                                                    </div>
                                                    <div class="question-actions">
                                                        <button onclick="openQuestionModal(${phase.id}, '${section.id}', ${JSON.stringify(q).replace(/"/g, '&quot;')})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                                        <button class="delete-question" onclick="deleteQuestion(${phase.id}, '${section.id}', '${q.id}')" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                                    </div>
                                                </div>
                                            `).join('')}
                                        </div>
                                    `}
                                    <button class="add-question-btn" onclick="openQuestionModal(${phase.id}, '${section.id}', null)">
                                        <i class="fa-solid fa-plus"></i> Add Question
                                    </button>
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            `}
        `;
    }

    function toggleSection(sectionId) {
        if (expandedSections.has(sectionId)) {
            expandedSections.delete(sectionId);
        } else {
            expandedSections.add(sectionId);
        }
        renderBuilder();
    }

    function deletePhase(id) {
        if (!confirm('Delete this phase and all its content?')) return;
        phases = phases.filter(p => p.id !== id);
        if (selectedPhaseId === id) selectedPhaseId = phases.length > 0 ? phases[0].id : null;
        renderBuilder();
    }

    function deleteSection(phaseId, secId) {
        if (!confirm('Delete this section?')) return;
        phases = phases.map(p => p.id === phaseId ? { ...p, sections: p.sections.filter(s => s.id !== secId) } : p);
        expandedSections.delete(secId);
        renderBuilder();
    }

    function deleteQuestion(phaseId, secId, qId) {
        if (!confirm('Delete this question?')) return;
        phases = phases.map(p => p.id === phaseId ? {
            ...p,
            sections: p.sections.map(s => s.id === secId ? { ...s, questions: s.questions.filter(q => q.id !== qId) } : s)
        } : p);
        renderBuilder();
    }

    // ═══════════════════════════════════════
    // QUESTION MODAL (UPDATED)
    // ═══════════════════════════════════════

    function openQuestionModal(phaseId, secId, question) {
        currentEditQuestion = { phaseId, secId, question };
        
        // Reset temp arrays
        tempOptions = question?.options ? [...question.options] : [];
        tempGridRows = question?.gridRows ? [...question.gridRows] : [];
        tempGridColumns = question?.gridColumns ? [...question.gridColumns] : [];
        
        // For likert_scale, set default columns if empty
        if (question?.type === 'likert_scale' && tempGridColumns.length === 0) {
            tempGridColumns = [...DEFAULT_LIKERT_COLUMNS];
        }
        
        document.getElementById('questionModalTitle').textContent = question ? 'Edit Question' : 'Add New Question';
        document.getElementById('qLabel').value = question ? question.label : '';
        document.getElementById('qType').value = question ? question.type : 'short_answer';
        document.getElementById('qPlaceholder').value = question?.placeholder || '';
        document.getElementById('qRequired').checked = question ? !!question.required : true;
        
        // File upload settings
        if (question?.type === 'file_upload') {
            document.querySelectorAll('.file-type-check').forEach(cb => {
                cb.checked = question.fileTypes ? question.fileTypes.includes(cb.value) : false;
            });
            document.getElementById('fileMaxSize').value = question.maxSize || 10;
        } else {
            document.querySelectorAll('.file-type-check').forEach(cb => cb.checked = false);
            document.getElementById('fileMaxSize').value = 10;
        }
        
        handleTypeChange();
        renderOptionsList();
        renderGridRowsList();
        renderGridColumnsList();
        
        document.getElementById('questionModal').classList.add('active');
    }

    function closeQuestionModal() {
        document.getElementById('questionModal').classList.remove('active');
        currentEditQuestion = null;
        tempOptions = [];
        tempGridRows = [];
        tempGridColumns = [];
    }

    function handleTypeChange() {
        const type = document.getElementById('qType').value;
        const isChoiceType = ['multiple_choice', 'checkboxes', 'dropdown'].includes(type);
        const isTextType = ['short_answer', 'paragraph'].includes(type);
        const isFileType = type === 'file_upload';
        const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(type);
        
        // Show/hide groups
        document.getElementById('optionsGroup').style.display = isChoiceType ? 'block' : 'none';
        document.getElementById('placeholderGroup').style.display = isTextType ? 'block' : 'none';
        document.getElementById('fileUploadGroup').style.display = isFileType ? 'block' : 'none';
        document.getElementById('gridSettingsGroup').style.display = isGridType ? 'block' : 'none';
        
        // Update help text
        document.getElementById('typeHelp').textContent = typeHelpText[type] || '';
        
        // Update grid columns label
        document.getElementById('gridColumnsLabel').textContent = 
            type === 'likert_scale' ? 'Scale Options (Columns)' : 'Choice Options (Columns)';
        
        // Set defaults for grid types
        if (isGridType) {
            if (tempGridColumns.length === 0) {
                tempGridColumns = type === 'likert_scale' ? [...DEFAULT_LIKERT_COLUMNS] : ['Option 1'];
            }
            renderGridColumnsList();
        }
        
        // Clear irrelevant temp data
        if (!isChoiceType && !isGridType) tempOptions = [];
        if (!isGridType) {
            tempGridRows = [];
            tempGridColumns = [];
        }
    }

    // ═══════════════════════════════════════
    // OPTIONS MANAGEMENT (Multiple Choice / Checkboxes / Dropdown)
    // ═══════════════════════════════════════

    function renderOptionsList() {
        const container = document.getElementById('optionsList');
        if (!container) return;
        container.innerHTML = tempOptions.map((opt, i) => `
            <div class="option-item">
                <i class="fa-solid fa-grip-vertical" style="color:var(--gray-300);"></i>
                <span>${opt}</span>
                <button onclick="removeOption(${i})" style="color:var(--danger); cursor:pointer; background:none; border:none;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).join('');
    }

    function addOption() {
        const input = document.getElementById('newOption');
        const val = input.value.trim();
        if (val) {
            tempOptions.push(val);
            input.value = '';
            renderOptionsList();
        }
    }

    function handleOptionKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addOption();
        }
    }

    function removeOption(index) {
        tempOptions.splice(index, 1);
        renderOptionsList();
    }

    // ═══════════════════════════════════════
    // GRID ROWS MANAGEMENT (Likert / Multiple Choice Grid)
    // ═══════════════════════════════════════

    function renderGridRowsList() {
        const container = document.getElementById('gridRowsList');
        if (!container) return;
        container.innerHTML = tempGridRows.map((row, i) => `
            <div class="option-item">
                <i class="fa-solid fa-grip-vertical" style="color:var(--gray-300);"></i>
                <span>${row}</span>
                <button onclick="removeGridRow(${i})" style="color:var(--danger); cursor:pointer; background:none; border:none;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).join('');
    }

    function addGridRow() {
        const input = document.getElementById('newGridRow');
        const val = input.value.trim();
        if (val) {
            tempGridRows.push(val);
            input.value = '';
            renderGridRowsList();
        }
    }

    function handleGridRowKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addGridRow();
        }
    }

    function removeGridRow(index) {
        tempGridRows.splice(index, 1);
        renderGridRowsList();
    }

    // ═══════════════════════════════════════
    // GRID COLUMNS MANAGEMENT (Likert / Multiple Choice Grid)
    // ═══════════════════════════════════════

    function renderGridColumnsList() {
        const container = document.getElementById('gridColumnsList');
        if (!container) return;
        container.innerHTML = tempGridColumns.map((col, i) => `
            <div class="option-item">
                <i class="fa-solid fa-grip-vertical" style="color:var(--gray-300);"></i>
                <span>${col}</span>
                <button onclick="removeGridColumn(${i})" style="color:var(--danger); cursor:pointer; background:none; border:none;"><i class="fa-solid fa-xmark"></i></button>
            </div>
        `).join('');
    }

    function addGridColumn() {
        const input = document.getElementById('newGridColumn');
        const val = input.value.trim();
        if (val) {
            tempGridColumns.push(val);
            input.value = '';
            renderGridColumnsList();
        }
    }

    function handleGridColumnKeydown(event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            addGridColumn();
        }
    }

    function removeGridColumn(index) {
        tempGridColumns.splice(index, 1);
        renderGridColumnsList();
    }

    // ═══════════════════════════════════════
    // SAVE QUESTION (UPDATED)
    // ═══════════════════════════════════════

    function saveQuestion() {
        const label = document.getElementById('qLabel').value.trim();
        if (!label) {
            alert('Please enter a question label.');
            return;
        }
        
        const type = document.getElementById('qType').value;
        const isChoiceType = ['multiple_choice', 'checkboxes', 'dropdown'].includes(type);
        const isTextType = ['short_answer', 'paragraph'].includes(type);
        const isFileType = type === 'file_upload';
        const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(type);
        
        // Validate options for choice types
        if (isChoiceType && tempOptions.length < 2) {
            alert('Please add at least 2 options for this question type.');
            return;
        }
        
        // Validate grid data
        if (isGridType) {
            if (tempGridRows.length === 0) {
                alert('Please add at least 1 statement (row) for the grid.');
                return;
            }
            if (tempGridColumns.length === 0) {
                alert('Please add at least 1 option (column) for the grid.');
                return;
            }
        }
        
        // Get file upload settings
        let fileTypes = [];
        let maxSize = 10;
        if (isFileType) {
            document.querySelectorAll('.file-type-check:checked').forEach(cb => fileTypes.push(cb.value));
            maxSize = parseInt(document.getElementById('fileMaxSize').value) || 10;
        }
        
        const qData = {
            id: currentEditQuestion.question ? currentEditQuestion.question.id : 'q-' + Date.now(),
            label,
            type,
            required: document.getElementById('qRequired').checked
        };

        // Add type-specific data
        if (isChoiceType) {
            qData.options = [...tempOptions];
        }
        
        if (isTextType) {
            const placeholder = document.getElementById('qPlaceholder').value.trim();
            if (placeholder) qData.placeholder = placeholder;
        }
        
        if (isFileType) {
            qData.fileTypes = fileTypes;
            qData.maxSize = maxSize;
        }
        
        if (isGridType) {
            qData.gridRows = [...tempGridRows];
            qData.gridColumns = [...tempGridColumns];
        }

        const { phaseId, secId, question } = currentEditQuestion;
        
        phases = phases.map(p => p.id === phaseId ? {
            ...p,
            sections: p.sections.map(s => s.id === secId ? {
                ...s,
                questions: question
                    ? s.questions.map(q => q.id === question.id ? qData : q)
                    : [...s.questions, qData]
            } : s)
        } : p);

        closeQuestionModal();
        renderBuilder();
    }

    // ═══════════════════════════════════════
    // PHASE MODAL
    // ═══════════════════════════════════════

    function openPhaseModal(phaseId) {
        const phase = phaseId ? phases.find(p => p.id === phaseId) : null;
        currentEditPhase = phase;
        selectedIcon = phase ? phase.icon : 'fa-user';
        selectedColor = phase ? phase.color : '#3b82f6';
        
        document.getElementById('phaseModalTitle').textContent = phase ? 'Edit Phase' : 'Add New Phase';
        document.getElementById('phaseTitle').value = phase ? phase.title : '';
        document.getElementById('phaseSubtitle').value = phase ? phase.subtitle : '';
        
        renderIconGrid();
        renderColorGrid();
        
        document.getElementById('phaseModal').classList.add('active');
    }

    function closePhaseModal() {
        document.getElementById('phaseModal').classList.remove('active');
        currentEditPhase = null;
    }

    function renderIconGrid() {
        document.getElementById('iconGrid').innerHTML = icons.map(ic => `
            <button onclick="selectIcon('${ic.key}')" class="icon-option ${selectedIcon === ic.key ? 'selected' : ''}">
                <i class="fa-solid ${ic.key}" style="color:${selectedIcon === ic.key ? '#32418C' : '#6b7280'};"></i>
                <span>${ic.label}</span>
            </button>
        `).join('');
    }

    function selectIcon(key) {
        selectedIcon = key;
        renderIconGrid();
    }

    function renderColorGrid() {
        document.getElementById('colorGrid').innerHTML = colors.map(c => `
            <button onclick="selectedColor = '${c}'; renderColorGrid();" class="color-option ${selectedColor === c ? 'selected' : ''}" style="background:${c};"></button>
        `).join('');
    }

    function savePhase() {
        const title = document.getElementById('phaseTitle').value.trim();
        if (!title) return;
        
        const data = {
            title,
            subtitle: document.getElementById('phaseSubtitle').value.trim(),
            icon: selectedIcon,
            color: selectedColor
        };

        if (currentEditPhase) {
            phases = phases.map(p => p.id === currentEditPhase.id ? { ...p, ...data } : p);
        } else {
            const newId = Math.max(0, ...phases.map(p => p.id)) + 1;
            phases.push({ id: newId, ...data, sections: [] });
            selectedPhaseId = newId;
        }

        closePhaseModal();
        renderBuilder();
    }

    // ═══════════════════════════════════════
    // SECTION MODAL
    // ═══════════════════════════════════════

    function openSectionModal(sectionId, phaseId) {
        const section = sectionId ? phases.find(p => p.id === phaseId)?.sections.find(s => s.id === sectionId) : null;
        currentEditSection = { section, phaseId };
        
        document.getElementById('sectionModalTitle').textContent = section ? 'Edit Section' : 'Add New Section';
        document.getElementById('sectionTitle').value = section ? section.title : '';
        document.getElementById('sectionDesc').value = section ? section.description || '' : '';
        
        document.getElementById('sectionModal').classList.add('active');
    }

    function closeSectionModal() {
        document.getElementById('sectionModal').classList.remove('active');
        currentEditSection = null;
    }

    function saveSection() {
        const title = document.getElementById('sectionTitle').value.trim();
        if (!title) return;
        
        const data = {
            title,
            description: document.getElementById('sectionDesc').value.trim()
        };

        const { section, phaseId } = currentEditSection;

        phases = phases.map(p => p.id === phaseId ? {
            ...p,
            sections: section
                ? p.sections.map(s => s.id === section.id ? { ...s, ...data } : s)
                : [...p.sections, { id: phaseId + '-' + Date.now(), ...data, questions: [] }]
        } : p);

        closeSectionModal();
        renderBuilder();
    }

    // ═══════════════════════════════════════
    // RESPONSES TABLE
    // ═══════════════════════════════════════

    function renderResponsesTable(filteredData) {
        const data = filteredData || MOCK_RESPONSES;
        const tbody = document.getElementById('responsesTableBody');
        tbody.innerHTML = data.map(r => `
            <tr>
                <td style="color:var(--gray-400);font-size:0.8125rem;">${r.id}</td>
                <td>
                    <div class="alumni-info">
                        <div class="alumni-avatar">${r.name.split(' ').map(n => n[0]).join('')}</div>
                        <span class="alumni-name">${r.name}</span>
                    </div>
                </td>
                <td>${r.program}</td>
                <td>${r.year}</td>
                <td>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar-track"><div class="progress-bar-fill" style="width:${r.completion}%; background:${r.completion === 100 ? '#10b981' : '#f59e0b'};"></div></div>
                        <span class="progress-text" style="color:${r.completion === 100 ? '#10b981' : '#f59e0b'};">${r.completion}%</span>
                    </div>
                </td>
                <td>${r.date}</td>
                <td><span class="status-badge ${r.status}">${r.status === 'complete' ? 'Complete' : 'In Progress'}</span></td>
                <td>
                    <div class="action-buttons">
                        <button class="btn-icon" title="View"><i class="fa-solid fa-eye"></i></button>
                        <button class="btn-icon delete" title="Delete"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </td>
            </tr>
        `).join('');
        
        document.getElementById('responsesCount').textContent = `${data.length} result${data.length !== 1 ? 's' : ''}`;
    }

    // Responses search & filter
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('responsesSearch');
        const filterSelect = document.getElementById('responsesFilter');

        if (searchInput) {
            searchInput.addEventListener('input', filterResponses);
        }
        if (filterSelect) {
            filterSelect.addEventListener('change', filterResponses);
        }

        function filterResponses() {
            const query = (searchInput?.value || '').toLowerCase();
            const status = filterSelect?.value || 'all';
            
            const filtered = MOCK_RESPONSES.filter(r => {
                const matchesSearch = r.name.toLowerCase().includes(query) || r.program.toLowerCase().includes(query);
                const matchesStatus = status === 'all' || r.status === status;
                return matchesSearch && matchesStatus;
            });
            
            renderResponsesTable(filtered);
        }

        // Initial render
        renderResponsesTable();
        renderBuilder();
    });

    // ═══════════════════════════════════════
    // MODAL CLOSE ON OVERLAY CLICK
    // ═══════════════════════════════════════

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    // ESC to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
        }
    });
</script>

</body>
</html>