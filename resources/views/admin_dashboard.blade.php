<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/dashboard_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Leaflet.js for Maps -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- Add after Leaflet CSS/JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.0/dist/MarkerCluster.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.0/dist/MarkerCluster.Default.css" />
    <script src="https://unpkg.com/leaflet.markercluster@1.5.0/dist/leaflet.markercluster.js"></script>

    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Export libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
</head>
<body>
    
    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        @include('dashboard.partials._sidebar')

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
                            <i class="fa-solid fa-chart-line"></i>
                            Admin Control Center
                        </h1>
                        <p class="page-subtitle">Overview of alumni engagement and platform activity</p>
                    </div>
                </div>
            </header>

            <!-- TAB NAVIGATION -->
            <div class="tab-navigation">
                <button class="tab-btn active" onclick="showTab('moderation')">
                    <i class="fa-solid fa-flag"></i>
                    <span>Moderation</span>
                    @if($totalReports > 0)
                        <span class="badge-notification">{{ $totalReports }}</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="showTab('mentorship')">
                    <i class="fa-solid fa-handshake"></i>
                    <span>Mentorship</span>
                    @if(isset($pendingMentorships) && $pendingMentorships > 0)
                        <span class="badge-notification" style="background: #F59E0B;">{{ $pendingMentorships }}</span>
                    @endif
                </button>
                <button class="tab-btn" onclick="showTab('users')">
                    <i class="fa-solid fa-users-gear"></i>
                    <span>User Analytics</span>
                </button>
                <button class="tab-btn" onclick="showTab('tracer')">
                    <i class="fa-solid fa-file-lines"></i>
                    <span>Tracer Analytics</span>
                </button>
                <button class="tab-btn" onclick="showTab('events')">
                    <i class="fa-solid fa-calendar-check"></i>
                    <span>Event Analytics</span>
                </button>
            </div>

            <!-- ============================================ -->
            <!-- TAB CONTENTS -->
            <!-- ============================================ -->
            @include('dashboard.partials._moderation-tab')
            @include('dashboard.partials._mentorship-tab')  <!-- NEW -->
            @include('dashboard.partials._users-tab')
            @include('dashboard.partials._tracer-tab')
            @include('dashboard.partials._events-tab')
        </main>
    </div>

    <!-- ============================================ -->
    <!-- MODALS -->
    <!-- ============================================ -->
    @include('dashboard.partials._modals')

    <!-- ============================================ -->
    <!-- GLOBAL SCRIPTS -->
    <!-- ============================================ -->
    @include('dashboard.partials._scripts')
</body>
</html>