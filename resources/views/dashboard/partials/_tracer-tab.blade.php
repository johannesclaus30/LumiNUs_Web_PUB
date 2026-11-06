<!-- ============================================ -->
<!-- TAB 3: TRACER ANALYTICS - ENHANCED -->
<!-- ============================================ -->
<div id="tab-tracer" class="tab-content">
    <section class="dashboard-section">
        
        <!-- ===== HEADER ===== -->
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-chart-pie"></i>
                Tracer Response Analytics
            </h2>
            <div class="section-actions">
                <button class="btn btn-sm btn-export" onclick="exportTracerData('pdf')">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-export" onclick="exportTracerData('excel')">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
            </div>
        </div>

        @if(!$tracerAnalytics['hasActiveForm'])
            <div class="empty-state" style="padding: 4rem 2rem;">
                <i class="fa-solid fa-file-circle-exclamation"></i>
                <p>No active tracer form found. Please activate a tracer form to view analytics.</p>
            </div>
        @else

        <!-- ===== SUMMARY STAT CARDS ===== -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon views">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($tracerAnalytics['summary']->total_eligible) }}</span>
                    <span class="stat-label">Eligible Alumni</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon success">
                        <i class="fa-solid fa-check-circle"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($tracerAnalytics['summary']->total_completed) }}</span>
                    <span class="stat-label">Completed</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon warning">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($tracerAnalytics['summary']->total_in_progress) }}</span>
                    <span class="stat-label">In Progress</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon info">
                        <i class="fa-solid fa-percent"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $tracerAnalytics['summary']->overall_completion_rate }}%</span>
                    <span class="stat-label">Completion Rate</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background:#f3e8ff; color:#9333ea;">
                        <i class="fa-solid fa-layer-group"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $tracerAnalytics['summary']->total_phases }}</span>
                    <span class="stat-label">Active Phases</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background:#dbeafe; color:#2563eb;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $tracerAnalytics['summary']->overall_response_rate }}%</span>
                    <span class="stat-label">Response Rate</span>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- 🎓 COLLEGE EMPLOYMENT ANALYTICS --}}
        {{-- ============================================ --}}
        @if($tracerAnalytics['collegeEmployment']['has_data'] ?? false)
<div class="dash-card mt-20" style="border-left: 4px solid var(--nu-blue);">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                <i class="fa-solid fa-briefcase"></i>
                College Alumni Employment Analytics
            </h3>
            <span class="card-subtitle">
                Based on {{ number_format($tracerAnalytics['collegeEmployment']['total_college_alumni']) }} verified college alumni
                &middot; {{ number_format($tracerAnalytics['collegeEmployment']['total_with_employment_records']) }} with employment records
            </span>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button class="btn btn-sm btn-export" onclick="exportEmploymentData('pdf')">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </button>
            <button class="btn btn-sm btn-export" onclick="exportEmploymentData('excel')">
                <i class="fa-solid fa-file-excel"></i> Excel
            </button>
        </div>
    </div>

    {{-- Employment Rate Hero --}}
    <div class="employment-hero">
        <div class="employment-rate-ring">
            <svg viewBox="0 0 120 120">
                <circle cx="60" cy="60" r="52" class="ring-track"/>
                <circle cx="60" cy="60" r="52" class="ring-fill"
                    style="stroke-dasharray: {{ $tracerAnalytics['collegeEmployment']['employment_rate'] * 3.267 }} 326.7;"/>
            </svg>
            <div class="ring-center">
                <span class="ring-value">{{ $tracerAnalytics['collegeEmployment']['employment_rate'] }}%</span>
                <span class="ring-label">Employed</span>
            </div>
        </div>

        <div class="employment-breakdown">
            @foreach($tracerAnalytics['collegeEmployment']['status_breakdown'] as $status => $count)
                @if($count > 0)
                <div class="employment-status-pill">
                    <div class="status-pill-icon">
                        <i class="fa-solid @switch($status)
                            @case('Currently Employed') fa-user-tie @break
                            @case('Has Employment History') fa-clock-rotate-left @break
                            @case('No Employment Records') fa-user-clock @break
                            @default fa-circle-question
                        @endswitch"></i>
                    </div>
                    <div class="status-pill-info">
                        <span class="status-pill-count">{{ number_format($count) }}</span>
                        <span class="status-pill-label">{{ $status }}</span>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Top Employers / Job Titles / Locations --}}
    <div class="employment-lists-grid">
        @if($tracerAnalytics['collegeEmployment']['top_employers']->count())
        <div class="employment-list-card">
            <div class="employment-list-header">
                <i class="fa-solid fa-building"></i>
                Top Employers
            </div>
            <div class="employment-list-items">
                @foreach($tracerAnalytics['collegeEmployment']['top_employers'] as $emp)
                <div class="employment-list-item">
                    <div class="item-rank">{{ $loop->iteration }}</div>
                    <div class="item-label">{{ $emp->company }}</div>
                    <div class="item-count">{{ $emp->count }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($tracerAnalytics['collegeEmployment']['top_job_titles']->count())
        <div class="employment-list-card">
            <div class="employment-list-header">
                <i class="fa-solid fa-id-badge"></i>
                Top Job Titles
            </div>
            <div class="employment-list-items">
                @foreach($tracerAnalytics['collegeEmployment']['top_job_titles'] as $job)
                <div class="employment-list-item">
                    <div class="item-rank">{{ $loop->iteration }}</div>
                    <div class="item-label">{{ $job->job_title }}</div>
                    <div class="item-count">{{ $job->count }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($tracerAnalytics['collegeEmployment']['top_locations']->count())
        <div class="employment-list-card">
            <div class="employment-list-header">
                <i class="fa-solid fa-location-dot"></i>
                Top Work Locations
            </div>
            <div class="employment-list-items">
                @foreach($tracerAnalytics['collegeEmployment']['top_locations'] as $loc)
                <div class="employment-list-item">
                    <div class="item-rank">{{ $loop->iteration }}</div>
                    <div class="item-label">{{ $loc->location }}</div>
                    <div class="item-count">{{ $loc->count }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Employment by Program Chart --}}
    @if($tracerAnalytics['collegeEmployment']['employment_by_program']->count())
    <div class="chart-container" style="height: 300px; margin-top: 1.5rem;">
        <canvas id="employmentByProgramChart"></canvas>
    </div>
    @endif

    {{-- Recently Added Employment Records --}}
    @if($tracerAnalytics['collegeEmployment']['recently_added']->count())
    <div class="employment-list-card" style="margin-top: 1.5rem;">
        <div class="employment-list-header">
            <i class="fa-solid fa-clock-rotate-left"></i>
            Recently Added Employment Records
        </div>
        <div class="recent-submissions-list" style="max-height: 260px;">
            @foreach($tracerAnalytics['collegeEmployment']['recently_added'] as $rec)
            <div class="recent-submission-item">
                <div class="submission-avatar">
                    <img src="{{ $rec->alumni_photo ?: '/assets/FINAL-NULIPA.jpg' }}"
                         onerror="this.onerror=null; this.src='/assets/FINAL-NULIPA.jpg';">
                </div>
                <div class="submission-info">
                    <div class="submission-name">{{ $rec->first_name }} {{ $rec->last_name }}</div>
                    <div class="submission-meta">
                        <span>{{ $rec->job_title }}</span>
                        <span>&middot;</span>
                        <span>{{ $rec->company }}</span>
                    </div>
                </div>
                <div class="submission-time">
                    <i class="fa-regular fa-clock"></i>
                    {{ \Carbon\Carbon::parse($rec->created_at)->diffForHumans() }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@elseif(isset($tracerAnalytics['collegeEmployment']['total_college_alumni']))
<div class="dash-card mt-20" style="border-left: 4px solid var(--warning);">
    <div class="empty-state" style="padding: 2rem;">
        <i class="fa-solid fa-briefcase"></i>
        <p>No verified college alumni found yet. Employment analytics will appear once alumni are added.</p>
    </div>
</div>
@endif

        {{-- ============================================ --}}
        {{-- 🎓 SHS COLLEGE CHOICE ANALYTICS --}}
        {{-- ============================================ --}}
        @if($tracerAnalytics['shsCollegeChoice']['has_data'] ?? false)
<div class="dash-card mt-20" style="border-left: 4px solid #8B5CF6;">
    <div class="card-header">
        <div>
            <h3 class="card-title">
                <i class="fa-solid fa-school"></i>
                SHS Alumni College Continuation
            </h3>
            <span class="card-subtitle">
                Based on {{ number_format($tracerAnalytics['shsCollegeChoice']['total_shs_alumni']) }} verified SHS alumni
            </span>
        </div>
        <div style="display:flex; gap:0.5rem;">
            <button class="btn btn-sm btn-export" onclick="exportShsCollegeData('pdf')">
                <i class="fa-solid fa-file-pdf"></i> PDF
            </button>
            <button class="btn btn-sm btn-export" onclick="exportShsCollegeData('excel')">
                <i class="fa-solid fa-file-excel"></i> Excel
            </button>
        </div>
    </div>

    {{-- Dual Rate Hero --}}
    <div class="shs-hero">
        <div class="shs-stat-block">
            <div class="shs-stat-icon" style="background:#EDE9FE; color:#8B5CF6;">
                <i class="fa-solid fa-graduation-cap"></i>
            </div>
            <div class="shs-stat-body">
                <span class="shs-stat-value">{{ $tracerAnalytics['shsCollegeChoice']['continuation_rate'] }}%</span>
                <span class="shs-stat-label">Continued to College</span>
                <span class="shs-stat-detail">
                    {{ $tracerAnalytics['shsCollegeChoice']['continued_count'] }} of
                    {{ $tracerAnalytics['shsCollegeChoice']['total_shs_alumni'] }} SHS alumni
                </span>
            </div>
        </div>

        <div class="shs-stat-block highlight">
            <div class="shs-stat-icon" style="background:#FEF3C7; color:#D97706;">
                <i class="fa-solid fa-building-columns"></i>
            </div>
            <div class="shs-stat-body">
                <span class="shs-stat-value">{{ number_format($tracerAnalytics['shsCollegeChoice']['nu_lipa_count']) }}</span>
                <span class="shs-stat-label">Now at NU Lipa</span>
                <span class="shs-stat-detail">
                    {{ $tracerAnalytics['shsCollegeChoice']['did_not_continue_count'] }} did not continue
                </span>
            </div>
        </div>
    </div>

    {{-- Top College Programs + Enrollment Status --}}
    <div class="two-col-grid mt-20">
        @if($tracerAnalytics['shsCollegeChoice']['top_college_programs']->count())
        <div class="employment-list-card">
            <div class="employment-list-header">
                <i class="fa-solid fa-book-open"></i>
                Top College Programs Chosen
            </div>
            <div class="employment-list-items">
                @foreach($tracerAnalytics['shsCollegeChoice']['top_college_programs'] as $prog)
                <div class="employment-list-item">
                    <div class="item-rank">{{ $loop->iteration }}</div>
                    <div class="item-label">{{ $prog->program_name }}</div>
                    <div class="item-count">{{ $prog->count }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @if($tracerAnalytics['shsCollegeChoice']['enrollment_status']->count())
        <div class="employment-list-card">
            <div class="employment-list-header">
                <i class="fa-solid fa-clipboard-check"></i>
                Current Enrollment Status
            </div>
            <div class="employment-list-items">
                @foreach($tracerAnalytics['shsCollegeChoice']['enrollment_status'] as $status)
                <div class="employment-list-item">
                    <div class="item-rank">
                        <i class="fa-solid @if($status->enrollment_status === 'graduated') fa-graduation-cap
                                          @elseif($status->enrollment_status === 'enrolled') fa-book-reader
                                          @else fa-user-slash @endif"></i>
                    </div>
                    <div class="item-label">{{ ucfirst($status->enrollment_status) }}</div>
                    <div class="item-count">{{ $status->count }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    {{-- Promotion Timeline Chart --}}
    @if($tracerAnalytics['shsCollegeChoice']['promotion_timeline']->count())
    <div class="chart-container" style="height: 260px; margin-top: 1.5rem;">
        <canvas id="shsPromotionTimelineChart"></canvas>
    </div>
    @endif

    {{-- Recently Promoted --}}
    @if($tracerAnalytics['shsCollegeChoice']['recently_promoted']->count())
    <div class="employment-list-card" style="margin-top: 1.5rem;">
        <div class="employment-list-header">
            <i class="fa-solid fa-arrow-up-right-dots"></i>
            Recently Promoted to College
        </div>
        <div class="recent-submissions-list" style="max-height: 260px;">
            @foreach($tracerAnalytics['shsCollegeChoice']['recently_promoted'] as $alum)
            <div class="recent-submission-item">
                <div class="submission-avatar">
                    <img src="{{ $alum->alumni_photo ?: '/assets/FINAL-NULIPA.jpg' }}"
                         onerror="this.onerror=null; this.src='/assets/FINAL-NULIPA.jpg';">
                </div>
                <div class="submission-info">
                    <div class="submission-name">{{ $alum->first_name }} {{ $alum->last_name }}</div>
                    <div class="submission-meta">
                        <span class="badge-target">SHS → College</span>
                        <span>{{ $alum->program ?? 'N/A' }}</span>
                    </div>
                </div>
                <div class="submission-time">
                    <i class="fa-regular fa-clock"></i>
                    {{ \Carbon\Carbon::parse($alum->promoted_to_college_at)->diffForHumans() }}
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif
</div>
@elseif(isset($tracerAnalytics['shsCollegeChoice']['total_shs_alumni']))
<div class="dash-card mt-20" style="border-left: 4px solid var(--warning);">
    <div class="empty-state" style="padding: 2rem;">
        <i class="fa-solid fa-school"></i>
        <p>No verified SHS alumni found yet. Analytics will appear once SHS alumni are added.</p>
    </div>
</div>
@endif

        <!-- ===== PHASE PROGRESS BARS ===== -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-bars-progress"></i>
                    Phase Completion Progress
                </h3>
                <span class="card-subtitle">Completion rate per tracer phase</span>
            </div>
            <div class="phase-progress-list">
                @foreach($tracerAnalytics['phases'] as $phase)
                <div class="phase-progress-item">
                    <div class="phase-progress-header">
                        <div class="phase-progress-info">
                            <div class="phase-icon" style="background: {{ $phase->color }}20; color: {{ $phase->color }};">
                                <i class="fa-solid {{ $phase->icon }}"></i>
                            </div>
                            <div>
                                <div class="phase-progress-title">{{ $phase->title }}</div>
                                <div class="phase-progress-meta">
                                    <span class="badge-target">
                                        {{ ucfirst($phase->target_alumni_type) }}
                                    </span>
                                    <span>{{ number_format($phase->completed_responses) }} / {{ number_format($phase->eligible_count) }} completed</span>
                                </div>
                            </div>
                        </div>
                        <div class="phase-progress-rate">
                            <span class="rate-value" style="color: {{ $phase->color }};">{{ $phase->completion_rate }}%</span>
                            <span class="rate-label">completion</span>
                        </div>
                    </div>
                    <div class="phase-progress-bar">
                        <div class="phase-progress-fill" style="width: {{ $phase->completion_rate }}%; background: {{ $phase->color }};"></div>
                    </div>
                    <div class="phase-progress-stats">
                        <span><i class="fa-solid fa-circle-check"></i> {{ $phase->completed_responses }} completed</span>
                        <span><i class="fa-solid fa-spinner"></i> {{ $phase->in_progress_responses }} in progress</span>
                        <span><i class="fa-solid fa-arrow-trend-down"></i> {{ $phase->drop_off_rate }}% drop-off</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- ===== ROW 1: Responses Over Time + Phase Trends ===== -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-chart-line"></i>
                        Overall Responses Over Time
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="tracerResponsesOverTimeChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-chart-area"></i>
                        Phase-Specific Trends
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="tracerPhaseTrendsChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ===== ROW 2: Phase Comparison + Completion Funnel ===== -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-chart-column"></i>
                        Phase Comparison
                    </h3>
                </div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="tracerPhaseComparisonChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-filter"></i>
                        Completion Funnel
                    </h3>
                </div>
                <div class="chart-container" style="height: 300px;">
                    <canvas id="tracerFunnelChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ===== ROW 3: Audience Breakdown + Drop-off Rates ===== -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-users-rectangle"></i>
                        Target Audience Breakdown
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="tracerAudienceChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-arrow-trend-down"></i>
                        Drop-off Rates by Phase
                    </h3>
                </div>
                <div class="chart-container" style="height: 280px;">
                    <canvas id="tracerDropOffChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ===== ROW 4: Top Phases + Needs Attention ===== -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-trophy" style="color:#F59E0B;"></i>
                        Top Performing Phases
                    </h3>
                </div>
                <div class="phase-rank-list">
                    @foreach($tracerAnalytics['topPhases'] as $index => $phase)
                    <div class="phase-rank-item rank-{{ $index + 1 }}">
                        <div class="rank-badge">{{ $index + 1 }}</div>
                        <div class="rank-info">
                            <div class="rank-title">{{ $phase->title }}</div>
                            <div class="rank-meta">{{ $phase->completed_responses }} completed of {{ $phase->eligible_count }} eligible</div>
                        </div>
                        <div class="rank-rate" style="color: {{ $phase->color }};">{{ $phase->completion_rate }}%</div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-triangle-exclamation" style="color:#EF4444;"></i>
                        Needs Attention
                    </h3>
                </div>
                <div class="phase-rank-list">
                    @foreach($tracerAnalytics['needsAttention'] as $index => $phase)
                    <div class="phase-rank-item attention">
                        <div class="rank-badge attention-badge">
                            <i class="fa-solid fa-exclamation"></i>
                        </div>
                        <div class="rank-info">
                            <div class="rank-title">{{ $phase->title }}</div>
                            <div class="rank-meta">
                                {{ $phase->completion_rate }}% completion &middot;
                                {{ $phase->drop_off_rate }}% drop-off
                            </div>
                        </div>
                        <div class="rank-rate" style="color: #EF4444;">{{ $phase->completion_rate }}%</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- ===== RECENT SUBMISSIONS ===== -->
        <div class="dash-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                    Recent Submissions
                </h3>
                <span class="card-subtitle">Latest completed tracer responses</span>
            </div>
            <div class="recent-submissions-list">
                @forelse($tracerAnalytics['recentSubmissions'] as $sub)
                <div class="recent-submission-item">
                    <div class="submission-avatar">
                        <img src="{{ $sub->alumni_photo ?: '/assets/FINAL-NULIPA.jpg' }}"
                            alt="{{ $sub->alumni_name }}"
                            onerror="this.onerror=null; this.src='/assets/FINAL-NULIPA.jpg';">
                    </div>
                    <div class="submission-info">
                        <div class="submission-name">{{ $sub->alumni_name }}</div>
                        <div class="submission-meta">
                            <span class="badge-target">{{ ucfirst($sub->alumni_type) }}</span>
                            <span>{{ $sub->program }}</span>
                        </div>
                    </div>
                    <div class="submission-time">
                        <i class="fa-regular fa-clock"></i> {{ $sub->time_ago }}
                    </div>
                </div>
                @empty
                <div class="empty-state" style="padding:1.5rem;">
                    <i class="fa-solid fa-inbox"></i>
                    <p>No recent submissions yet.</p>
                </div>
                @endforelse
            </div>
        </div>

        @endif
    </section>
</div>