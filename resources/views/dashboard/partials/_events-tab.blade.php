<!-- ============================================ -->
<!-- TAB 4: EVENT ANALYTICS — REDESIGNED -->
<!-- ============================================ -->
<div id="tab-events" class="tab-content">
    <section class="dashboard-section">

        <!-- ═══════════════════════════════════════ -->
        <!-- SECTION HEADER -->
        <!-- ═══════════════════════════════════════ -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-calendar-check"></i>
                Event Analytics
            </h2>
            <div class="section-actions">
                <button class="btn btn-sm btn-export" onclick="exportData('events', 'pdf')">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-export" onclick="exportData('events', 'excel')">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
            </div>
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- KPI GRID — 6 CARDS -->
        <!-- ═══════════════════════════════════════ -->
        <div class="stats-grid events-stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon total"><i class="fa-solid fa-calendar"></i></div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($eventAnalytics['summary']['total_events']) }}</span>
                    <span class="stat-label">Total Events</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon active"><i class="fa-solid fa-tower-broadcast"></i></div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($eventAnalytics['summary']['active_events']) }}</span>
                    <span class="stat-label">Active Now</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon upcoming"><i class="fa-solid fa-calendar-plus"></i></div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($eventAnalytics['summary']['upcoming_events']) }}</span>
                    <span class="stat-label">Upcoming</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon registrations"><i class="fa-solid fa-user-plus"></i></div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($eventAnalytics['summary']['total_registrations']) }}</span>
                    <span class="stat-label">Total Registrations</span>
                    @if($eventAnalytics['summary']['registration_growth'] != 0)
                        <span class="stat-trend {{ $eventAnalytics['summary']['registration_growth'] > 0 ? 'trend-up' : 'trend-down' }}">
                            <i class="fa-solid fa-arrow-{{ $eventAnalytics['summary']['registration_growth'] > 0 ? 'up' : 'down' }}"></i>
                            {{ abs($eventAnalytics['summary']['registration_growth']) }}% vs last month
                        </span>
                    @endif
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon success"><i class="fa-solid fa-chart-simple"></i></div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($eventAnalytics['summary']['avg_registrations_per_event'], 1) }}</span>
                    <span class="stat-label">Avg per Event</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon warning"><i class="fa-solid fa-gauge-high"></i></div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $eventAnalytics['summary']['overall_fill_rate'] }}%</span>
                    <span class="stat-label">Overall Fill Rate</span>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- ROW 1: REGISTRATIONS OVER TIME + TOP EVENTS -->
        <!-- ═══════════════════════════════════════ -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-chart-line"></i>
                        Registrations Over Time
                        <span class="card-subtitle">Last 12 months</span>
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="registrationsOverTimeChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-trophy"></i>
                        Top Performing Events
                        <span class="card-subtitle">By registration count</span>
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="topEventsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- ROW 2: EVENT TYPE + STATUS DONUTS -->
        <!-- ═══════════════════════════════════════ -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-shapes"></i>
                        Event Format Breakdown
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="eventTypeChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-circle-half-stroke"></i>
                        Event Status Distribution
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="eventStatusChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- ROW 3: CAPACITY FILL RATE + PROGRAMS -->
        <!-- ═══════════════════════════════════════ -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-gauge-high"></i>
                        Capacity & Fill Rate
                        <span class="card-subtitle">Upcoming & active events</span>
                    </h3>
                </div>
                <div class="chart-container" style="height: 320px;">
                    <canvas id="capacityChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Top Programs Attending
                    </h3>
                </div>
                <div class="chart-container" style="height: 320px;">
                    <canvas id="topProgramsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- ROW 4: RECENT ACTIVITY FEED -->
        <!-- ═══════════════════════════════════════ -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-bolt"></i>
                    Recent Registration Activity
                </h3>
                <span class="card-subtitle">Latest 10 sign-ups</span>
            </div>

            @if(count($eventAnalytics['recent_registrations']) > 0)
                <div class="event-activity-feed">
                    @foreach($eventAnalytics['recent_registrations'] as $reg)
                        <div class="event-activity-item">
                            <div class="activity-avatar">
                                @if(!empty($reg['alumni_photo']))
                                    <img
                                        src="{{ $reg['alumni_photo'] }}"
                                        alt="{{ $reg['alumni_name'] }}"
                                        loading="lazy"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                                    >
                                    <span class="avatar-initials" style="display:none;">
                                        {{ strtoupper(substr($reg['alumni_name'], 0, 1)) }}
                                    </span>
                                @else
                                    <span class="avatar-initials">
                                        {{ strtoupper(substr($reg['alumni_name'], 0, 1)) }}
                                    </span>
                                @endif
                            </div>

                            <div class="activity-content">
                                <div class="activity-line">
                                    <a href="{{ route('admin.alumni.show', $reg['alumni_id']) }}" class="activity-alumni-link">
                                        <strong>{{ $reg['alumni_name'] }}</strong>
                                    </a>
                                    registered for
                                    <a href="{{ route('events.index') }}" class="activity-event-link">
                                        {{ \Str::limit($reg['event_title'], 40) }}
                                    </a>
                                </div>
                                <div class="activity-time">
                                    <i class="fa-regular fa-clock"></i>
                                    {{ $reg['time_ago'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <i class="fa-solid fa-inbox"></i>
                    <p>No recent registration activity</p>
                    <span class="empty-subtitle">Registrations will appear here as they come in.</span>
                </div>
            @endif
        </div>

        <!-- ═══════════════════════════════════════ -->
        <!-- ROW 5: FULL EVENT PERFORMANCE TABLE -->
        <!-- ═══════════════════════════════════════ -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-table-list"></i>
                    Event Performance Table
                </h3>
                <div class="card-actions">
                    <input type="text" id="eventTableSearch" class="table-search-input" placeholder="Search events...">
                </div>
            </div>

            <div class="table-responsive">
                <table class="event-performance-table" id="eventPerformanceTable">
                    <thead>
                        <tr>
                            <th>Event</th>
                            <th>Format</th>
                            <th>Date</th>
                            <th>Registered</th>
                            <th>Capacity</th>
                            <th>Fill Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($eventAnalytics['event_performance'] as $event)
                            <tr>
                                <td class="event-title-cell">
                                    <a href="{{ route('events.index') }}">
                                        {{ \Str::limit($event['title'], 40) }}
                                    </a>
                                </td>
                                <td>
                                    <span class="event-format-badge format-{{ strtolower($event['event_type']) }}">
                                        {{ $event['event_type'] }}
                                    </span>
                                </td>
                                <td class="event-date-cell">
                                    {{ \Carbon\Carbon::parse($event['start_date'])->format('M d, Y') }}
                                </td>
                                <td class="text-center">
                                    <strong>{{ number_format($event['registered']) }}</strong>
                                </td>
                                <td class="text-center">{{ number_format($event['capacity']) }}</td>
                                <td>
                                    <div class="fill-rate-cell">
                                        <div class="mini-progress">
                                            <div class="mini-progress-fill
                                                {{ $event['fill_rate'] >= 80 ? 'fill-high' : ($event['fill_rate'] >= 50 ? 'fill-mid' : 'fill-low') }}"
                                                style="width: {{ min($event['fill_rate'], 100) }}%">
                                            </div>
                                        </div>
                                        <span class="fill-rate-value">{{ $event['fill_rate'] }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-pill status-{{ strtolower($event['status']) }}">
                                        {{ $event['status'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center empty-row">
                                    No events found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </section>
</div>

<!-- ═══════════════════════════════════════════ -->
<!-- EVENTS TAB STYLES -->
<!-- ═══════════════════════════════════════════ -->
<style>
/* ══════════════════════════════════════════
   STAT CARD TREND BADGE
   ══════════════════════════════════════════ */
.stat-trend {
    display: inline-flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.7rem;
    font-weight: 600;
    padding: 0.125rem 0.5rem;
    border-radius: var(--radius-full);
    margin-top: 0.25rem;
    width: fit-content;
}
.stat-trend.trend-up {
    background: var(--success-light);
    color: #047857;
}
.stat-trend.trend-down {
    background: var(--danger-light);
    color: #B91C1C;
}

/* ══════════════════════════════════════════
   RECENT ACTIVITY FEED
   ══════════════════════════════════════════ */
.event-activity-feed {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    max-height: 380px;
    overflow-y: auto;
    padding-right: 0.25rem;
}

.event-activity-feed::-webkit-scrollbar { width: 6px; }
.event-activity-feed::-webkit-scrollbar-track { background: var(--gray-100); border-radius: var(--radius-full); }
.event-activity-feed::-webkit-scrollbar-thumb { background: var(--gray-300); border-radius: var(--radius-full); }

.event-activity-item {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 0.75rem 1rem;
    background: var(--gray-50);
    border-radius: var(--radius-lg);
    transition: all var(--transition);
    border-left: 3px solid transparent;
}
.event-activity-item:hover {
    background: var(--white);
    border-left-color: var(--nu-gold);
    box-shadow: var(--shadow-sm);
    transform: translateX(3px);
}

.activity-avatar {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--nu-blue), var(--nu-blue-light));
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.95rem;
    flex-shrink: 0;
    overflow: hidden;
    border: 2px solid var(--nu-gold);
    box-shadow: 0 2px 6px rgba(50, 65, 140, 0.15);
    transition: transform var(--transition), box-shadow var(--transition);
}

/* Alumni photo inside the avatar */
.activity-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
    display: block;
}

/* Initials fallback */
.activity-avatar .avatar-initials {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Subtle zoom + glow on hover */
.event-activity-item:hover .activity-avatar {
    transform: scale(1.08);
    box-shadow: 0 4px 12px rgba(251, 209, 23, 0.4);
}

/* Make the alumni name a nice link */
.activity-alumni-link {
    color: inherit;
    text-decoration: none;
    transition: color var(--transition);
}
.activity-alumni-link:hover strong {
    color: var(--nu-blue);
    text-decoration: underline;
}
.activity-alumni-link strong {
    transition: color var(--transition);
}

.activity-content {
    flex: 1;
    min-width: 0;
}
.activity-line {
    font-size: 0.875rem;
    color: var(--gray-700);
    line-height: 1.5;
    word-break: break-word;
}
.activity-line strong {
    color: var(--gray-900);
    font-weight: 600;
}
.activity-event-link {
    color: var(--nu-blue);
    font-weight: 600;
    text-decoration: none;
    transition: color var(--transition);
}
.activity-event-link:hover {
    color: var(--nu-gold-dark);
    text-decoration: underline;
}
.activity-time {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    font-size: 0.75rem;
    color: var(--gray-500);
    margin-top: 0.25rem;
}
.activity-time i {
    font-size: 0.7rem;
}

/* ══════════════════════════════════════════
   EVENT PERFORMANCE TABLE
   ══════════════════════════════════════════ */
.table-responsive {
    overflow-x: auto;
    border-radius: var(--radius-lg);
    border: 1px solid var(--gray-200);
    -webkit-overflow-scrolling: touch;
}

.event-performance-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.875rem;
    min-width: 800px;
}

.event-performance-table thead {
    background: linear-gradient(135deg, var(--nu-blue) 0%, var(--nu-blue-dark) 100%);
}

.event-performance-table thead th {
    padding: 0.875rem 1rem;
    text-align: left;
    color: white;
    font-weight: 600;
    font-size: 0.75rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    white-space: nowrap;
}

.event-performance-table tbody tr {
    border-bottom: 1px solid var(--gray-100);
    transition: background var(--transition);
}
.event-performance-table tbody tr:last-child {
    border-bottom: none;
}
.event-performance-table tbody tr:hover {
    background: var(--nu-blue-soft);
}

.event-performance-table tbody td {
    padding: 0.75rem 1rem;
    color: var(--gray-700);
    vertical-align: middle;
}

.event-title-cell a {
    color: var(--nu-blue);
    font-weight: 600;
    text-decoration: none;
    transition: color var(--transition);
}
.event-title-cell a:hover {
    color: var(--nu-gold-dark);
    text-decoration: underline;
}

.event-date-cell {
    color: var(--gray-600);
    font-size: 0.8125rem;
    white-space: nowrap;
}

.text-center {
    text-align: center;
}

/* ── Event Format Badges ── */
.event-format-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.625rem;
    border-radius: var(--radius-full);
    font-size: 0.7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.format-virtual   { background: #DBEAFE; color: #1E40AF; }
.format-in-person { background: #D1FAE5; color: #065F46; }
.format-hybrid   { background: #FEF3C7; color: #92400E; }
.format-na       { background: var(--gray-100); color: var(--gray-600); }

/* ── Fill Rate Cell ── */
.fill-rate-cell {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    min-width: 130px;
}

.mini-progress {
    flex: 1;
    height: 8px;
    background: var(--gray-200);
    border-radius: var(--radius-full);
    overflow: hidden;
    min-width: 60px;
}

.mini-progress-fill {
    height: 100%;
    border-radius: var(--radius-full);
    transition: width 0.6s cubic-bezier(0.4, 0, 0.2, 1);
}
.fill-high { background: linear-gradient(90deg, #10B981, #059669); }
.fill-mid  { background: linear-gradient(90deg, #F59E0B, #D97706); }
.fill-low  { background: linear-gradient(90deg, #EF4444, #DC2626); }

.fill-rate-value {
    font-size: 0.75rem;
    font-weight: 700;
    color: var(--gray-700);
    min-width: 45px;
    text-align: right;
}

/* ── Status Pills ── */
.status-pill {
    display: inline-flex;
    align-items: center;
    padding: 0.25rem 0.75rem;
    border-radius: var(--radius-full);
    font-size: 0.7rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}
.status-active    { background: #D1FAE5; color: #065F46; }
.status-upcoming  { background: #DBEAFE; color: #1E40AF; }
.status-completed { background: var(--gray-200); color: var(--gray-700); }
.status-cancelled { background: #FEE2E2; color: #B91C1C; }

/* ── Search Input ── */
.table-search-input {
    padding: 0.5rem 1rem;
    border: 2px solid var(--gray-200);
    border-radius: var(--radius-lg);
    font-family: inherit;
    font-size: 0.875rem;
    color: var(--gray-700);
    min-width: 240px;
    transition: all var(--transition);
    outline: none;
}
.table-search-input:focus {
    border-color: var(--nu-blue);
    box-shadow: 0 0 0 3px rgba(50, 65, 140, 0.1);
}
.table-search-input::placeholder {
    color: var(--gray-400);
}

.empty-row {
    padding: 2.5rem 1rem !important;
    color: var(--gray-400);
    font-style: italic;
}

/* ══════════════════════════════════════════
   RESPONSIVE
   ══════════════════════════════════════════ */
@media (max-width: 1024px) {
    .events-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 768px) {
    .table-search-input {
        width: 100%;
        min-width: 0;
    }
    .card-actions {
        width: 100%;
    }
}

@media (max-width: 640px) {
    .events-stats-grid {
        grid-template-columns: 1fr;
    }
    .activity-line {
        font-size: 0.8125rem;
    }
}
</style>

<!-- ═══════════════════════════════════════════ -->
<!-- EVENTS TAB JAVASCRIPT -->
<!-- ═══════════════════════════════════════════ -->
<script>
/**
 * Initialize all Event Analytics charts.
 * Safe to call multiple times — destroys existing instances first.
 */
function initEventsCharts() {
    if (typeof Chart === 'undefined') {
        console.warn('Chart.js not loaded — skipping Events charts.');
        return;
    }

    const analytics = @json($eventAnalytics ?? []);
    if (!analytics.charts) {
        console.warn('No event analytics data available.');
        return;
    }

    const charts = window._charts || {};

    // Helper: destroy existing chart if present
    const destroyIfExists = (key) => {
        if (charts[key]) {
            try { charts[key].destroy(); } catch (e) {}
            delete charts[key];
        }
    };

    const NU_NAVY = '#32418C';
    const NU_GOLD = '#FBD117';

    /* ══════════════════════════════════════════
       1. REGISTRATIONS OVER TIME (Line)
       ══════════════════════════════════════════ */
    const regCtx = document.getElementById('registrationsOverTimeChart');
    if (regCtx && analytics.charts.registrations_over_time?.labels?.length) {
        destroyIfExists('registrationsOverTime');

        const ctx = regCtx.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 280);
        gradient.addColorStop(0, 'rgba(50, 65, 140, 0.35)');
        gradient.addColorStop(1, 'rgba(50, 65, 140, 0.02)');

        charts.registrationsOverTime = new Chart(regCtx, {
            type: 'line',
            data: {
                labels: analytics.charts.registrations_over_time.labels,
                datasets: [{
                    label: 'Registrations',
                    data: analytics.charts.registrations_over_time.data,
                    borderColor: NU_NAVY,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    borderWidth: 3,
                    pointBackgroundColor: NU_NAVY,
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointHoverBackgroundColor: NU_GOLD,
                    pointHoverBorderColor: NU_NAVY,
                    pointHoverBorderWidth: 3,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        displayColors: false,
                        callbacks: {
                            label: (ctx) => `${ctx.raw} registration${ctx.raw !== 1 ? 's' : ''}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#6B7280', font: { size: 11 } },
                        grid: { color: '#F3F4F6' }
                    },
                    x: {
                        ticks: { color: '#6B7280', font: { size: 10 }, maxRotation: 45, minRotation: 0 },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    /* ══════════════════════════════════════════
       2. TOP PERFORMING EVENTS (Horizontal Bar)
       ══════════════════════════════════════════ */
    const topCtx = document.getElementById('topEventsChart');
    if (topCtx && analytics.charts.top_events?.labels?.length) {
        destroyIfExists('topEvents');

        charts.topEvents = new Chart(topCtx, {
            type: 'bar',
            data: {
                labels: analytics.charts.top_events.labels,
                datasets: [{
                    label: 'Registrations',
                    data: analytics.charts.top_events.data,
                    backgroundColor: [
                        'rgba(251, 209, 23, 0.9)',   // gold
                        'rgba(50, 65, 140, 0.9)',    // navy
                        'rgba(16, 185, 129, 0.9)',   // green
                        'rgba(245, 158, 11, 0.9)',   // amber
                        'rgba(139, 92, 246, 0.9)',   // purple
                        'rgba(236, 72, 153, 0.9)',   // pink
                        'rgba(59, 130, 246, 0.9)',   // blue
                        'rgba(107, 114, 128, 0.9)',  // gray
                    ],
                    borderColor: 'transparent',
                    borderWidth: 0,
                    borderRadius: 8,
                    barPercentage: 0.75,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            title: (items) => {
                                const idx = items[0].dataIndex;
                                return analytics.charts.top_events.full_titles[idx];
                            },
                            label: (ctx) => {
                                const idx = ctx.dataIndex;
                                const fill = analytics.charts.top_events.fill_rates[idx];
                                return [
                                    `${ctx.raw} registrations`,
                                    `Fill rate: ${fill}%`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#6B7280', font: { size: 11 } },
                        grid: { color: '#F3F4F6' }
                    },
                    y: {
                        ticks: { color: '#374151', font: { size: 11, weight: '500' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    /* ══════════════════════════════════════════
       3. EVENT TYPE BREAKDOWN (Doughnut)
       ══════════════════════════════════════════ */
    const typeCtx = document.getElementById('eventTypeChart');
    if (typeCtx && analytics.charts.event_type_breakdown?.labels?.length) {
        destroyIfExists('eventType');

        charts.eventType = new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: analytics.charts.event_type_breakdown.labels,
                datasets: [{
                    data: analytics.charts.event_type_breakdown.data,
                    backgroundColor: [
                        'rgba(50, 65, 140, 0.9)',
                        'rgba(251, 209, 23, 0.9)',
                        'rgba(16, 185, 129, 0.9)',
                        'rgba(139, 92, 246, 0.9)',
                        'rgba(236, 72, 153, 0.9)',
                    ],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            boxWidth: 12,
                            boxHeight: 12,
                            font: { size: 12, family: 'Poppins, sans-serif' },
                            usePointStyle: true,
                            pointStyle: 'circle',
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    /* ══════════════════════════════════════════
       4. EVENT STATUS DISTRIBUTION (Doughnut)
       ══════════════════════════════════════════ */
    const statusCtx = document.getElementById('eventStatusChart');
    if (statusCtx && analytics.charts.status_distribution?.labels?.length) {
        destroyIfExists('eventStatus');

        charts.eventStatus = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: analytics.charts.status_distribution.labels,
                datasets: [{
                    data: analytics.charts.status_distribution.data,
                    backgroundColor: [
                        'rgba(16, 185, 129, 0.9)',   // Active - green
                        'rgba(59, 130, 246, 0.9)',   // Upcoming - blue
                        'rgba(107, 114, 128, 0.9)',  // Completed - gray
                        'rgba(239, 68, 68, 0.9)',    // Cancelled - red
                    ],
                    borderColor: '#fff',
                    borderWidth: 3,
                    hoverOffset: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            boxWidth: 12,
                            boxHeight: 12,
                            font: { size: 12, family: 'Poppins, sans-serif' },
                            usePointStyle: true,
                            pointStyle: 'circle',
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: (ctx) => {
                                const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                                const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }

    /* ══════════════════════════════════════════
       5. CAPACITY & FILL RATE (Horizontal Bar)
       ══════════════════════════════════════════ */
    const capacityCtx = document.getElementById('capacityChart');
    if (capacityCtx && analytics.charts.capacity_analysis?.length) {
        destroyIfExists('capacity');

        const data = analytics.charts.capacity_analysis;

        charts.capacity = new Chart(capacityCtx, {
            type: 'bar',
            data: {
                labels: data.map(e => e.title),
                datasets: [
                    {
                        label: 'Registered',
                        data: data.map(e => e.registered),
                        backgroundColor: 'rgba(50, 65, 140, 0.85)',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    },
                    {
                        label: 'Remaining Capacity',
                        data: data.map(e => Math.max(e.capacity - e.registered, 0)),
                        backgroundColor: 'rgba(229, 231, 235, 0.7)',
                        borderRadius: 6,
                        barPercentage: 0.6,
                    }
                ]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 14,
                            font: { size: 11, family: 'Poppins, sans-serif' },
                            usePointStyle: true,
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            afterBody: (items) => {
                                const idx = items[0].dataIndex;
                                return `Fill rate: ${data[idx].fill_rate}%`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        stacked: true,
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#6B7280', font: { size: 11 } },
                        grid: { color: '#F3F4F6' }
                    },
                    y: {
                        stacked: true,
                        ticks: { color: '#374151', font: { size: 10, weight: '500' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    /* ══════════════════════════════════════════
       6. TOP PROGRAMS ATTENDING (Horizontal Bar)
       ══════════════════════════════════════════ */
    const programsCtx = document.getElementById('topProgramsChart');
    if (programsCtx && analytics.charts.top_programs?.labels?.length) {
        destroyIfExists('topPrograms');

        charts.topPrograms = new Chart(programsCtx, {
            type: 'bar',
            data: {
                labels: analytics.charts.top_programs.labels,
                datasets: [{
                    label: 'Registrations',
                    data: analytics.charts.top_programs.data,
                    backgroundColor: 'rgba(251, 209, 23, 0.9)',
                    borderColor: '#e6bd0d',
                    borderWidth: 1,
                    borderRadius: 8,
                    barPercentage: 0.7,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: (ctx) => `${ctx.raw} registration${ctx.raw !== 1 ? 's' : ''}`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#6B7280', font: { size: 11 } },
                        grid: { color: '#F3F4F6' }
                    },
                    y: {
                        ticks: { color: '#374151', font: { size: 11, weight: '500' } },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    window._charts = charts;
    return charts;
}

/* ══════════════════════════════════════════
   EVENT TABLE SEARCH
   ══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('eventTableSearch');
    const table = document.getElementById('eventPerformanceTable');

    if (searchInput && table) {
        searchInput.addEventListener('input', function () {
            const query = this.value.toLowerCase().trim();
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }
});

/* ══════════════════════════════════════════
   INIT ON LOAD (in case tab is already active)
   ══════════════════════════════════════════ */
document.addEventListener('DOMContentLoaded', function () {
    if (document.getElementById('tab-events')?.classList.contains('active')) {
        setTimeout(() => {
            if (typeof initEventsCharts === 'function') initEventsCharts();
        }, 150);
    }
});
</script>