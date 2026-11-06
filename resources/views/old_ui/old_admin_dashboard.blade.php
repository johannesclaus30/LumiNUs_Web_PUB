<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/dashboard_new.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <!-- Leaflet & Chart.js CDN -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>
                <a href="{{ url('/admin/dashboard') }}" class="admin-menu-current">Admin Dashboard</a>
                <a href="{{ route('admin.directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>
            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>

        <div class="div-dashboard-container">
            <div class="admin-scrollable">
                
                <!-- TAB NAVIGATION -->
                <div class="tab-navigation">
                    <button class="tab-btn active" onclick="showTab('main')">📊 Main Dashboard</button>
                    <button class="tab-btn" onclick="showTab('events')">📅 Events</button>
                    <button class="tab-btn" onclick="showTab('alumni')">👥 Alumni Info</button>
                    <button class="tab-btn" onclick="showTab('tracer')">📋 Tracer Forms</button>
                </div>

                <!-- MAIN DASHBOARD TAB (Always Visible by Default) -->
                <div id="tab-main" class="tab-content active">
                    
                    <!-- KEY METRICS -->
                    <section class="dashboard-section">
                        <h2 class="section-title">📊 Overview</h2>
                        <div class="dash-top-row">
                            <div class="stat-card stat-yellow">
                                <div class="stat-icon-wrapper">✅</div>
                                <div class="stat-details">
                                    <span class="stat-label">Verified Alumni</span>
                                    <span class="stat-number">{{ number_format($verifiedAlumniCount) }}</span>
                                </div>
                            </div>
                            <div class="stat-card stat-blue">
                                <div class="stat-icon-wrapper">📅</div>
                                <div class="stat-details">
                                    <span class="stat-label">Active Events</span>
                                    <span class="stat-number">{{ number_format($activeEventsCount) }}</span>
                                </div>
                            </div>
                            <div class="stat-card stat-green">
                                <div class="stat-icon-wrapper">📝</div>
                                <div class="stat-details">
                                    <span class="stat-label">Tracer Responses</span>
                                    <span class="stat-number">{{ number_format($totalTracerResponses) }}</span>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- CHARTS SECTION -->
                    <section class="dashboard-section">
                        <div class="section-header-flex">
                            <h2 class="section-title">📈 Alumni Analytics</h2>
                            <div class="chart-toggle">
                                <button class="toggle-btn active" onclick="showChart('year')" id="btn-year">
                                    📅 By Year Graduated
                                </button>
                                <button class="toggle-btn" onclick="showChart('program')" id="btn-program">
                                    🎓 By Program
                                </button>
                            </div>
                        </div>
                        
                        <div class="chart-card">
                            <div class="card-header">
                                <h3 class="card-title" id="chart-title">Alumni by Year Graduated</h3>
                            </div>
                            <div class="chart-container">
                                <canvas id="yearChart"></canvas>
                                <canvas id="programChart" style="display: none;"></canvas>
                            </div>
                        </div>
                    </section>

                    <!-- RECENT ACTIVITY -->
                    <section class="dashboard-section">
                        <h2 class="section-title">🔔 Recent Activity</h2>
                        <div class="activity-grid">
                            <div class="dash-card">
                                <div class="card-header flex-between">
                                    <h3 class="card-title">Recent Announcements</h3>
                                    <a href="{{ route('announcements.index') }}" class="link-text">View All ></a>
                                </div>
                                <div class="list-wrapper compact">
                                    @forelse($recentAnnouncements as $announcement)
                                    <div class="message-item compact">
                                        <div class="item-avatar small">AN</div>
                                        <div class="msg-content">
                                            <div class="msg-header">
                                                <span class="msg-name">{{ Str::limit($announcement->title, 40) }}</span>
                                                <span class="msg-time">{{ $announcement->date_posted->format('M d') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                    <div class="empty-state">No announcements yet.</div>
                                    @endforelse
                                </div>
                            </div>

                            <div class="dash-card">
                                <div class="card-header flex-between">
                                    <h3 class="card-title">Latest Tracer Forms</h3>
                                    <a href="{{ url('/admin/alumni_tracer') }}" class="link-text">View All ></a>
                                </div>
                                <div class="list-wrapper compact">
                                    @forelse($recentTracerForms as $form)
                                    <div class="list-item compact">
                                        <div class="item-avatar small" style="background: var(--nu-blue);">TF</div>
                                        <div class="item-info">
                                            <p class="item-name small">{{ Str::limit($form->form_title, 35) }}</p>
                                            <p class="item-sub">{{ $form->created_at->diffForHumans() }}</p>
                                        </div>
                                        <span class="badge badge-green small">Active</span>
                                    </div>
                                    @empty
                                    <div class="empty-state">No tracer forms yet.</div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- EVENTS TAB -->
                <div id="tab-events" class="tab-content">
                    <section class="dashboard-section">
                        <h2 class="section-title">📅 Events Management</h2>
                        
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">Event Geomap</h3>
                                <span class="card-subtitle">Live event locations</span>
                            </div>
                            <div class="map-wrapper" id="event-map"></div>
                        </div>

                        <div class="dash-card mt-20">
                            <div class="card-header flex-between">
                                <h3 class="card-title">Upcoming Events with Registrations</h3>
                                <a href="{{ route('events.index') }}" class="link-text">Manage Events ></a>
                            </div>
                            <div class="list-wrapper">
                                @forelse($upcomingEvents as $event)
                                <div class="event-item detailed">
                                    <div class="event-left">
                                        <i class="legend-dot {{ now()->between($event->start_date, $event->end_date) ? 'dot-yellow' : 'dot-blue' }}"></i>
                                        <div>
                                            <p class="item-name">{{ $event->title }}</p>
                                            <p class="item-sub">📍 {{ $event->venue_name ?? 'TBA' }} | 📅 {{ $event->start_date->format('M d, Y') }}</p>
                                        </div>
                                    </div>
                                    <div class="event-stats">
                                        <div class="registration-badge">
                                            <span class="reg-count">{{ $event->registration_count ?? 0 }}</span>
                                            <span class="reg-label">Registered</span>
                                        </div>
                                        <span class="event-date">{{ $event->start_date->format('M d') }}</span>
                                    </div>
                                </div>
                                @empty
                                <div class="empty-state">No upcoming events scheduled.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>

                <!-- ALUMNI INFO TAB -->
                <div id="tab-alumni" class="tab-content">
                    <section class="dashboard-section">
                        <h2 class="section-title">👥 Alumni Information</h2>
                        
                        <div class="dash-card">
                            <div class="card-header">
                                <h3 class="card-title">Detailed Alumni Statistics</h3>
                            </div>
                            <div class="stats-detail-grid">
                                <div class="stat-box">
                                    <h4>Total Verified Alumni</h4>
                                    <p class="stat-big">{{ number_format($verifiedAlumniCount) }}</p>
                                </div>
                                <div class="stat-box">
                                    <h4>Top Program</h4>
                                    <p class="stat-big">{{ $chartData['programs'][0] ?? 'N/A' }}</p>
                                    <p class="stat-small">{{ $chartData['programs_count'][0] ?? 0 }} alumni</p>
                                </div>
                                <div class="stat-box">
                                    <h4>Most Recent Batch</h4>
                                    <p class="stat-big">{{ $chartData['years'][0] ?? 'N/A' }}</p>
                                    <p class="stat-small">{{ $chartData['years_count'][0] ?? 0 }} graduates</p>
                                </div>
                            </div>
                        </div>

                        <div class="dash-card mt-20">
                            <div class="card-header">
                                <h3 class="card-title">Alumni Distribution by Program</h3>
                            </div>
                            <div class="program-list">
                                @foreach($chartData['programs'] as $index => $program)
                                <div class="program-item">
                                    <div class="program-name">{{ $program }}</div>
                                    <div class="program-bar">
                                        <div class="program-fill" style="width: {{ ($chartData['programs_count'][$index] / max($chartData['programs_count'])) * 100 }}%"></div>
                                    </div>
                                    <div class="program-count">{{ $chartData['programs_count'][$index] }}</div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </section>
                </div>

                <!-- TRACER FORMS TAB -->
                <div id="tab-tracer" class="tab-content">
                    <section class="dashboard-section">
                        <h2 class="section-title">📋 Tracer Forms Management</h2>
                        
                        <div class="dash-card">
                            <div class="card-header flex-between">
                                <h3 class="card-title">All Tracer Forms</h3>
                                <a href="{{ url('/admin/alumni_tracer') }}" class="link-text">Create New ></a>
                            </div>
                            <div class="list-wrapper">
                                @forelse($recentTracerForms as $form)
                                <div class="list-item">
                                    <div class="item-avatar" style="background: var(--nu-blue);">TF</div>
                                    <div class="item-info">
                                        <p class="item-name">{{ $form->form_title }}</p>
                                        <p class="item-sub">Published {{ $form->created_at->diffForHumans() }}</p>
                                    </div>
                                    <div class="item-action">
                                        <span class="badge {{ $form->status == 1 ? 'badge-green' : 'badge-gray' }}">
                                            {{ $form->status == 1 ? 'Active' : 'Draft' }}
                                        </span>
                                    </div>
                                </div>
                                @empty
                                <div class="empty-state">No tracer forms published yet.</div>
                                @endforelse
                            </div>
                        </div>
                    </section>
                </div>

            </div>
        </div>
    </div>

    <script>
    // ========== TAB NAVIGATION ==========
    let mapInitialized = false;
    
    function showTab(tabName) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active class from all buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab
        document.getElementById('tab-' + tabName).classList.add('active');
        
        // Add active class to clicked button
        if (event && event.target) {
            event.target.classList.add('active');
        }
        
        // Initialize map ONLY when Events tab is shown
        if (tabName === 'events' && !mapInitialized) {
            setTimeout(initializeMap, 200); // Small delay for CSS transition
        }
    }

    // ========== MAP INITIALIZATION ==========
    function initializeMap() {
        const eventLocations = @json($eventLocations);
        const mapContainer = document.getElementById('event-map');
        
        // Handle empty data
        if (!eventLocations || eventLocations.length === 0) {
            mapContainer.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:300px;color:#718096;font-family:Poppins;">No event locations available</div>';
            mapInitialized = true;
            return;
        }
        
        // Create map instance
        const map = L.map('event-map', {
            zoomControl: true,
            scrollWheelZoom: false
        }).setView([14.6091, 121.0223], 12);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);
        
        // Add markers
        const bounds = [];
        eventLocations.forEach(evt => {
            const lat = parseFloat(evt.latitude);
            const lng = parseFloat(evt.longitude);
            
            if (!isNaN(lat) && !isNaN(lng)) {
                const marker = L.marker([lat, lng]).addTo(map);
                marker.bindPopup(`
                    <div style="min-width:200px;padding:8px;font-family:'Poppins',sans-serif;">
                        <strong style="font-size:14px;color:#32418c;display:block;margin-bottom:4px;">${evt.title}</strong>
                        <span style="color:#666;font-size:12px;display:block;">📍 ${evt.venue_name || 'TBA'}</span>
                        <span style="color:#666;font-size:12px;display:block;margin-top:2px;">📅 ${evt.start_date} - ${evt.end_date}</span>
                    </div>
                `);
                bounds.push([lat, lng]);
            }
        });
        
        // Fit bounds if we have markers
        if (bounds.length > 0) {
            map.fitBounds(bounds, { padding: [40, 40], maxZoom: 14 });
        }
        
        // Critical: Force Leaflet to recalculate size after tab becomes visible
        setTimeout(() => {
            map.invalidateSize();
        }, 100);
        
        mapInitialized = true;
    }

    // ========== CHART TOGGLE ==========
    let yearChartInstance = null;
    let programChartInstance = null;
    
    function showChart(type) {
        const yearChart = document.getElementById('yearChart');
        const programChart = document.getElementById('programChart');
        const btnYear = document.getElementById('btn-year');
        const btnProgram = document.getElementById('btn-program');
        const chartTitle = document.getElementById('chart-title');
        
        if (type === 'year') {
            yearChart.style.display = 'block';
            programChart.style.display = 'none';
            btnYear.classList.add('active');
            btnProgram.classList.remove('active');
            chartTitle.textContent = 'Alumni by Year Graduated';
            
            if (!yearChartInstance && chartData.years?.length > 0) {
                yearChartInstance = new Chart(yearChart, {
                    type: 'bar',
                    data: {
                        labels: chartData.years,
                        datasets: [{
                            label: 'Number of Alumni',
                            data: chartData.years_count,
                            backgroundColor: 'rgba(50, 65, 140, 0.8)',
                            borderColor: 'rgba(50, 65, 140, 1)',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                    }
                });
            }
        } else {
            yearChart.style.display = 'none';
            programChart.style.display = 'block';
            btnYear.classList.remove('active');
            btnProgram.classList.add('active');
            chartTitle.textContent = 'Alumni by Program';
            
            if (!programChartInstance && chartData.programs?.length > 0) {
                programChartInstance = new Chart(programChart, {
                    type: 'bar',
                    data: {
                        labels: chartData.programs,
                        datasets: [{
                            label: 'Number of Alumni',
                            data: chartData.programs_count,
                            backgroundColor: 'rgba(251, 209, 23, 0.8)',
                            borderColor: 'rgba(251, 209, 23, 1)',
                            borderWidth: 1,
                            borderRadius: 6
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: { x: { beginAtZero: true } }
                    }
                });
            }
        }
    }

    // ========== INITIALIZE ON PAGE LOAD ==========
    const chartData = @json($chartData);
    
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize Year chart (visible by default)
        const yearChart = document.getElementById('yearChart');
        if (yearChart && chartData.years?.length > 0) {
            yearChartInstance = new Chart(yearChart, {
                type: 'bar',
                data: {
                    labels: chartData.years,
                    datasets: [{
                        label: 'Number of Alumni',
                        data: chartData.years_count,
                        backgroundColor: 'rgba(50, 65, 140, 0.8)',
                        borderColor: 'rgba(50, 65, 140, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
                }
            });
        }
        
        // Optional: Pre-load map if Events tab is somehow active on load
        if (document.getElementById('tab-events')?.classList.contains('active')) {
            setTimeout(initializeMap, 200);
        }
    });
</script>


</body>
</html>