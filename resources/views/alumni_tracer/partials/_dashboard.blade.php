<div class="tracer-panel active" id="dashboard-panel">
    <!-- Stats Overview -->
    <div class="stats-overview">
        <div class="stat-card">
            <div class="stat-icon blue">
                <i class="fa-solid fa-users"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value" id="statTotalAlumni">0</span>
                <span class="stat-label">Total Alumni</span>
                <span class="stat-sub">--</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon green">
                <i class="fa-solid fa-check-circle"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value" id="statCompleted">0</span>
                <span class="stat-label">Completed</span>
                <span class="stat-sub">--</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon amber">
                <i class="fa-solid fa-hourglass-half"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value" id="statInProgress">0</span>
                <span class="stat-label">In Progress</span>
                <span class="stat-sub">--</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon purple">
                <i class="fa-solid fa-circle-question"></i>
            </div>
            <div class="stat-info">
                <span class="stat-value" id="statTotalQuestions">0</span>
                <span class="stat-label">Total Questions</span>
                <span class="stat-sub">0 phases · 0 sections</span>
            </div>
        </div>
    </div>

    <!-- Dashboard 2-Column Grid -->
    <div class="dashboard-grid-2col">
        <div class="chart-card">
            <h3><i class="fa-solid fa-clock-rotate-left" style="color:#3b82f6;"></i> Recent Activity</h3>
            <div class="activity-list" id="activityList">
                <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No recent activity yet.</p>
            </div>
        </div>
        
        <div class="chart-card">
            <h3><i class="fa-solid fa-bullseye" style="color:#ef4444;"></i> Survey Completion Funnel</h3>
            <div class="funnel-chart" id="funnelChart">
                <div class="funnel-item" style="width: 100%;">
                    <span class="funnel-label">Invited</span>
                    <span class="funnel-value">0</span>
                </div>
                <div class="funnel-item" style="width: 0%;">
                    <span class="funnel-label">Opened</span>
                    <span class="funnel-value">0</span>
                </div>
                <div class="funnel-item" style="width: 0%;">
                    <span class="funnel-label">Completed</span>
                    <span class="funnel-value">0</span>
                </div>
                <div class="funnel-item" style="width: 0%;">
                    <span class="funnel-label">Verified</span>
                    <span class="funnel-value">0</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row - Phase Completion & Program Distribution -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Phase Completion Rate</h3>
            <div class="bar-chart" id="phaseCompletionChart">
                <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No phases created yet.</p>
            </div>
        </div>

        <div class="chart-card">
            <h3>Program Distribution</h3>
            <div class="distribution-list" id="programDistributionList">
                <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No program data yet.</p>
            </div>
        </div>
    </div>

    <!-- Charts Row - Graduation Year & Top Responders -->
    <div class="charts-grid">
        <div class="chart-card">
            <h3>Graduation Year Distribution</h3>
            <div class="distribution-list" id="yearDistributionList">
                <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No year data yet.</p>
            </div>
        </div>

        <div class="chart-card">
            <h3>Top Responders</h3>
            <div class="top-responders-list" id="topRespondersList">
                <p style="color: var(--gray-400); text-align: center; padding: 2rem;">No responses yet.</p>
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
                <tbody id="recentSubmissionsBody">
                    <tr>
                        <td colspan="6" style="text-align: center; color: var(--gray-400); padding: 2rem;">
                            No submissions yet.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // ============================================
    // DASHBOARD DATA LOADING
    // ============================================

    async function loadDashboardData() {
        if (!currentFormId) {
            console.log('No form available');
            return;
        }
        
        try {
            const stats = await apiFetch(`${API_BASE}/${currentFormId}/dashboard-stats`);
            updateDashboardStats(stats);
            
            const submissions = await apiFetch(`${API_BASE}/${currentFormId}/recent-submissions`);
            updateRecentSubmissions(submissions);
            
            updateFunnelChart(stats);
            updatePhaseCompletionChart(stats);
            updateProgramDistribution(stats);
            updateYearDistribution(stats);
            updateTopResponders(stats);
            
        } catch (error) {
            console.error('Failed to load dashboard data:', error);
            showDashboardError();
        }
    }

    function updateDashboardStats(stats) {
        document.getElementById('statTotalAlumni').textContent = stats.totalAlumni || '0';
        document.getElementById('statCompleted').textContent = stats.completedResponses || '0';
        document.getElementById('statInProgress').textContent = stats.inProgressResponses || '0';
        document.getElementById('statTotalQuestions').textContent = stats.totalQuestions || '0';
        
        const statSubs = document.querySelectorAll('.stat-sub');
        if (statSubs[0]) statSubs[0].textContent = `${stats.responseRate || 0}% response rate`;
        if (statSubs[1]) statSubs[1].textContent = `${stats.completedResponses || 0} of ${stats.totalAlumni || 0}`;
        if (statSubs[2]) statSubs[2].textContent = `${stats.inProgressResponses || 0} pending`;
        if (statSubs[3]) statSubs[3].textContent = `${stats.phaseCount || 0} phases · ${stats.sectionCount || 0} sections`;
    }

    function updateRecentSubmissions(submissions) {
        const tbody = document.getElementById('recentSubmissionsBody');
        if (!Array.isArray(submissions)) {
            console.error('Recent submissions response was not an array:', submissions);
            submissions = [];
        }
        
        if (!submissions || submissions.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--gray-400); padding: 2rem;">
                        No submissions yet.
                    </td>
                </tr>
            `;
            return;
        }
        
        tbody.innerHTML = submissions.map(sub => `
            <tr>
                <td>${sub.alumni_name || 'Unknown'}</td>
                <td>${sub.program || 'N/A'}</td>
                <td>${sub.year_graduated || 'N/A'}</td>
                <td>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <div class="progress-bar-wrapper" style="flex: 1;">
                            <div class="progress-bar-track">
                                <div class="progress-bar-fill" style="width:${sub.completion}%; background:${sub.completion === 100 ? '#10b981' : '#f59e0b'};"></div>
                            </div>
                        </div>
                        <span style="font-size: 0.75rem; color:${sub.completion === 100 ? '#10b981' : '#f59e0b'}; white-space: nowrap;">${sub.completion}%</span>
                    </div>
                </td>
                <td>${sub.submitted_at || 'N/A'}</td>
                <td><span class="status-badge ${sub.status || 'in_progress'}">${sub.status === 'completed' ? 'Complete' : 'In Progress'}</span></td>
            </tr>
        `).join('');
    }

    function updateFunnelChart(stats) {
        const total = stats.totalAlumni || 1;
        const completed = stats.completedResponses || 0;
        const totalResponses = (stats.completedResponses || 0) + (stats.inProgressResponses || 0);
        
        const funnelItems = document.querySelectorAll('#funnelChart .funnel-item');
        if (funnelItems.length >= 4) {
            funnelItems[0].style.width = '100%';
            funnelItems[0].querySelector('.funnel-value').textContent = total;
            
            const openedPct = Math.max((totalResponses / total) * 100, 5);
            funnelItems[1].style.width = openedPct + '%';
            funnelItems[1].querySelector('.funnel-value').textContent = totalResponses;
            
            const completedPct = Math.max((completed / total) * 100, 5);
            funnelItems[2].style.width = completedPct + '%';
            funnelItems[2].querySelector('.funnel-value').textContent = completed;
            
            funnelItems[3].style.width = Math.max(completedPct * 0.8, 2) + '%';
            funnelItems[3].querySelector('.funnel-value').textContent = completed;
        }
    }

    function updatePhaseCompletionChart(stats) {
        const container = document.getElementById('phaseCompletionChart');
        if (!stats.phaseStats || stats.phaseStats.length === 0) {
            container.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 2rem;">No phases created yet.</p>';
            return;
        }
        
        const maxVal = Math.max(...stats.phaseStats.map(p => p.completionRate), 1);
        container.innerHTML = stats.phaseStats.map(phase => `
            <div class="h-bar-item">
                <div class="h-bar-header">
                    <span class="h-bar-label">${phase.title}</span>
                    <span class="h-bar-percent">${phase.completionRate}%</span>
                </div>
                <div class="h-bar-track">
                    <div class="h-bar-fill" style="width:${(phase.completionRate / maxVal) * 100}%; background: ${phase.color || '#3b82f6'};"></div>
                </div>
            </div>
        `).join('');
    }

    function updateProgramDistribution(stats) {
        const container = document.getElementById('programDistributionList');
        if (!stats.programStats || stats.programStats.length === 0) {
            container.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 2rem;">No program data yet.</p>';
            return;
        }
        
        const maxVal = Math.max(...stats.programStats.map(p => p.count), 1);
        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#f97316'];
        
        container.innerHTML = stats.programStats.map((prog, i) => `
            <div class="distribution-item">
                <div class="distribution-item-header">
                    <span class="distribution-dot" style="background:${colors[i % colors.length]};"></span>
                    <span class="distribution-name">${prog.program || 'Unknown'}</span>
                    <span class="distribution-count">${prog.count}</span>
                </div>
                <div class="h-bar-track">
                    <div class="h-bar-fill" style="width:${(prog.count / maxVal) * 100}%; background:${colors[i % colors.length]};"></div>
                </div>
            </div>
        `).join('');
    }

    function updateYearDistribution(stats) {
        const container = document.getElementById('yearDistributionList');
        if (!stats.yearStats || stats.yearStats.length === 0) {
            container.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 2rem;">No year data yet.</p>';
            return;
        }
        
        const maxVal = Math.max(...stats.yearStats.map(y => y.count), 1);
        const colors = ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#f97316', '#84cc16', '#14b8a6'];
        
        container.innerHTML = stats.yearStats.map((year, i) => `
            <div class="distribution-item">
                <div class="distribution-item-header">
                    <span class="distribution-dot" style="background:${colors[i % colors.length]};"></span>
                    <span class="distribution-name">Batch ${year.year || 'Unknown'}</span>
                    <span class="distribution-count">${year.count}</span>
                </div>
                <div class="h-bar-track">
                    <div class="h-bar-fill" style="width:${(year.count / maxVal) * 100}%; background:${colors[i % colors.length]};"></div>
                </div>
            </div>
        `).join('');
    }

    function updateTopResponders(stats) {
        const container = document.getElementById('topRespondersList');
        if (!stats.topResponders || stats.topResponders.length === 0) {
            container.innerHTML = '<p style="color: var(--gray-400); text-align: center; padding: 2rem;">No responses yet.</p>';
            return;
        }
        
        const maxCompletion = Math.max(...stats.topResponders.map(r => r.completion), 1);
        
        container.innerHTML = stats.topResponders.map((responder, i) => {
            const initials = responder.name.split(' ').map(n => n[0]).join('').toUpperCase();
            const medalColors = ['#f59e0b', '#94a3b8', '#cd7f32'];
            const barColors = ['#3b82f6', '#6366f1', '#8b5cf6', '#10b981', '#06b6d4'];
            
            return `
                <div class="top-responder-item">
                    <div class="top-responder-rank">
                        ${i < 3 ? `<i class="fa-solid fa-medal" style="color:${medalColors[i]};"></i>` : `<span style="color:var(--gray-400); font-weight:600;">#${i + 1}</span>`}
                    </div>
                    <div class="top-responder-avatar" style="background:${barColors[i % barColors.length]};">
                        ${initials}
                    </div>
                    <div class="top-responder-info">
                        <span class="top-responder-name">${responder.name}</span>
                        <span class="top-responder-program">${responder.program || 'N/A'} · Batch ${responder.year || 'N/A'}</span>
                    </div>
                    <div class="top-responder-completion">
                        <span class="top-responder-pct">${responder.completion}%</span>
                        <div class="mini-progress-bar" style="width:${(responder.completion / maxCompletion) * 60}px; background:${barColors[i % barColors.length]};"></div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function showDashboardError() {
        document.getElementById('statTotalAlumni').textContent = 'Error';
        document.getElementById('statCompleted').textContent = 'Error';
        document.getElementById('statInProgress').textContent = 'Error';
        document.getElementById('statTotalQuestions').textContent = 'Error';
    }
</script>

{{-- This is the _dashboard.blade.php --}}