<div class="tracer-panel" id="analytics-panel">
    <!-- Analytics Header -->
    <div class="analytics-header">
        <div>
            <h2 class="analytics-title">Tracer Analytics</h2>
            <p class="analytics-subtitle">Visualize and analyze alumni responses</p>
        </div>
        <div class="analytics-header-actions">
            <button class="btn-add-chart" onclick="openChartBuilderModal()">
                <i class="fa-solid fa-plus"></i> Add Chart
            </button>
            <button class="btn-export" onclick="exportAnalyticsReport()">
                <i class="fa-solid fa-file-export"></i> Export Report
            </button>
        </div>
    </div>

    <div class="analytics-filter-row">
        <div class="analytics-type-tabs">
            <button class="type-tab active" data-type="all" onclick="setAnalyticsType('all')">
                <i class="fa-solid fa-users"></i> All Alumni
            </button>
            <button class="type-tab" data-type="shs" onclick="setAnalyticsType('shs')">
                <i class="fa-solid fa-school"></i> SHS
            </button>
            <button class="type-tab" data-type="college" onclick="setAnalyticsType('college')">
                <i class="fa-solid fa-graduation-cap"></i> College
            </button>
        </div>

        <div class="analytics-range-tabs">
            <button class="range-tab" data-range="7d" onclick="setAnalyticsRange('7d')">7d</button>
            <button class="range-tab" data-range="30d" onclick="setAnalyticsRange('30d')">30d</button>
            <button class="range-tab" data-range="90d" onclick="setAnalyticsRange('90d')">90d</button>
            <button class="range-tab active" data-range="all" onclick="setAnalyticsRange('all')">All</button>
        </div>
    </div>

    <!-- KPI Summary Cards -->
    <div class="analytics-kpi-grid" id="analyticsKpiGrid">
        <div class="kpi-card">
            <div class="kpi-icon green"><i class="fa-solid fa-percent"></i></div>
            <div class="kpi-value" style="color:#10b981;" id="kpiResponseRate">--</div>
            <div class="kpi-label">Response Rate</div>
            <div class="kpi-trend" id="kpiResponseRateTrend">--</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon blue"><i class="fa-solid fa-chart-simple"></i></div>
            <div class="kpi-value" style="color:#3b82f6;" id="kpiAvgCompletion">--</div>
            <div class="kpi-label">Avg. Completion</div>
            <div class="kpi-trend" id="kpiAvgCompletionTrend">--</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-icon amber"><i class="fa-solid fa-users"></i></div>
            <div class="kpi-value" style="color:#f59e0b;" id="kpiTotalResponses">--</div>
            <div class="kpi-label">Total Responses</div>
            <div class="kpi-trend" id="kpiTotalResponsesTrend">--</div>
        </div>
        {{-- <div class="kpi-card">
            <div class="kpi-icon purple"><i class="fa-solid fa-clock"></i></div>
            <div class="kpi-value" style="color:#8b5cf6;" id="kpiAvgTime">--</div>
            <div class="kpi-label">Avg. Time to Complete</div>
            <div class="kpi-trend" id="kpiAvgTimeTrend">--</div>
        </div> --}}
    </div>

    <!-- Insights (NEW) -->
    <div class="analytics-insights" id="analyticsInsights" style="display:none;">
        <div class="insights-header">
            <i class="fa-solid fa-lightbulb"></i>
            <span>Insights</span>
        </div>
        <div class="insights-list" id="insightsList"></div>
    </div>

    <!-- Customizable Charts Grid -->
    <div class="analytics-grid" id="analyticsChartsGrid">
        <div class="analytics-empty-state" id="analyticsEmptyState">
            <i class="fa-solid fa-chart-pie"></i>
            <h3>No charts configured yet</h3>
            <p>Click "Add Chart" to select questions and visualize response data.</p>
            <button class="btn btn-primary" onclick="openChartBuilderModal()">
                Add Your First Chart
            </button>
        </div>
    </div>
</div>

<!-- Drilldown Modal (NEW) -->
<div class="modal-overlay" id="drilldownModal">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="drilldownTitle">Responses</h3>
            <button class="modal-close" onclick="closeDrilldownModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body" id="drilldownBody" style="padding: 0;">
            <!-- Filled by JS -->
        </div>
    </div>
</div>

<script>
    // ============================================
    // ANALYTICS
    // ============================================
    let analyticsCharts = [];
    let analyticsRange = 'all';
    let analyticsType = 'all';
    window.analyticsCharts = [];  // expose for debugging

    // ── Persistence ──
    function chartStorageKey() {
        return `tracer_analytics_charts_${currentFormId || 'default'}`;
    }

function loadAnalyticsCharts() {
    let saved = [];
    try {
        const raw = localStorage.getItem(chartStorageKey());
        if (raw) {
            saved = JSON.parse(raw);
        }
    } catch (e) { console.warn('Chart load failed', e); }

    // Build a fresh set of auto-charts for every question
    const auto = getDefaultCharts();

    // IDs that the user has already customized
    const savedByQuestionId = new Map();
    saved.forEach(c => savedByQuestionId.set(String(c.questionId), c));

    // Start with auto-charts, then let saved user configs override
    const merged = auto.map(autoChart => {
        const userChart = savedByQuestionId.get(String(autoChart.questionId));
        if (userChart) {
            // User has customized this chart — use their config
            // But keep their saved ID if present
            return { ...userChart };
        }
        return autoChart;
    });

    // Also add any saved charts for questions that no longer exist?
    // Skip them — orphaned charts would be confusing.

    analyticsCharts = merged;
    window.analyticsCharts = merged;
    console.log(`📊 Auto-merged charts: ${auto.length} questions, ${saved.length} user-customized`);

    return merged.length > 0;
}

    function saveAnalyticsCharts() {
        try {
            localStorage.setItem(chartStorageKey(), JSON.stringify(analyticsCharts));
            window.analyticsCharts = analyticsCharts;
            console.log('💾 Charts saved to localStorage:', analyticsCharts.length, 'charts');
        } catch (e) { console.warn('Chart save failed', e); }
    }

    // ── Date Range ──
    function setAnalyticsRange(range) {
        analyticsRange = range;
        document.querySelectorAll('.range-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.range === range);
        });
        loadAnalyticsKPIs();
        renderAnalyticsChartsGrid();
    }

    function setAnalyticsType(type) {
        analyticsType = type;
        document.querySelectorAll('.type-tab').forEach(t => {
            t.classList.toggle('active', t.dataset.type === type);
        });
        loadAnalyticsKPIs();
        renderAnalyticsChartsGrid();
    }

    // ── KPIs ──
    async function loadAnalyticsKPIs() {
        if (!currentFormId) return;

        try {
            const qs = `?range=${analyticsRange}&alumni_type=${analyticsType}`;
            const kpis = await apiFetch(`${API_BASE}/${currentFormId}/analytics-kpis${qs}`);

            document.getElementById('kpiResponseRate').textContent = kpis.responseRate ? kpis.responseRate + '%' : '0%';
            document.getElementById('kpiAvgCompletion').textContent = kpis.avgCompletion ? kpis.avgCompletion + '%' : '0%';
            document.getElementById('kpiTotalResponses').textContent = kpis.totalResponses || 0;
            
            // Safety check: This element is commented out in the blade file
            const avgTimeEl = document.getElementById('kpiAvgTime');
            if (avgTimeEl) {
                avgTimeEl.textContent = kpis.avgTimeToComplete ? kpis.avgTimeToComplete + ' min' : '--';
            }

            // Render trends
            const renderTrend = (elId, delta, suffix = '%') => {
                const el = document.getElementById(elId);
                if (delta === null || delta === undefined || isNaN(delta)) {
                    el.textContent = '—';
                    el.className = 'kpi-trend';
                    return;
                }
                const dir = delta >= 0 ? 'up' : 'down';
                const arrow = delta >= 0 ? '▲' : '▼';
                el.textContent = `${arrow} ${Math.abs(delta)}${suffix} vs previous period`;
                el.className = `kpi-trend ${dir}`;
            };

            renderTrend('kpiResponseRateTrend', kpis.trends?.responseRate);
            renderTrend('kpiTotalResponsesTrend', kpis.trends?.totalResponses);
            document.getElementById('kpiAvgCompletionTrend').textContent = 'Overall average';

            const avgTimeTrendEl = document.getElementById('kpiAvgTimeTrend');
            if (avgTimeTrendEl) {
                avgTimeTrendEl.textContent = 'Per response';
            }

            // Load insights too
            loadAnalyticsInsights();
        } catch (error) {
            console.error('Failed to load analytics KPIs:', error);
        }
    }

    // ── Insights ──
    async function loadAnalyticsInsights() {
        try {
            const data = await apiFetch(`${API_BASE}/${currentFormId}/analytics-insights?alumni_type=${analyticsType}`);
            const insights = data.insights || [];
            const panel = document.getElementById('analyticsInsights');
            const list = document.getElementById('insightsList');

            if (insights.length === 0) {
                panel.style.display = 'none';
                return;
            }

            list.innerHTML = insights.map(i => `
                <div class="insight-item insight-${i.color}">
                    <i class="fa-solid ${i.icon}"></i>
                    <span>${i.text}</span>
                </div>
            `).join('');

            panel.style.display = 'block';
        } catch (e) {
            console.warn('Insights failed:', e);
        }
    }

async function loadQuestionAnalytics(questionId) {
    try {
        return await apiFetch(
            `${API_BASE}/question/${questionId}/analytics?range=${analyticsRange}&alumni_type=${analyticsType}`
        );
    } catch (error) {
        console.error(`Failed to load analytics for Q${questionId}:`, error);
        return null;   // Promise.allSettled + null → chart is hidden
    }
}

async function renderAnalyticsChartsGrid() {
    const grid = document.getElementById('analyticsChartsGrid');
    const emptyState = document.getElementById('analyticsEmptyState');

    if (analyticsCharts.length === 0) {
        emptyState.style.display = 'flex';
        grid.querySelectorAll('.analytics-group, .analytics-card').forEach(c => c.remove());
        return;
    }

    emptyState.style.display = 'none';
    grid.querySelectorAll('.analytics-group, .analytics-card').forEach(c => c.remove());

    const results = await Promise.allSettled(
        analyticsCharts.map(chart => loadQuestionAnalytics(chart.questionId))
    );

    const enriched = analyticsCharts.map((chart, index) => {
        const r = results[index];
        const data = r.status === 'fulfilled' ? r.value : null;
        return { chart, data, index };
    });

    const visible = enriched.filter(({ data }) => {
        if (!data) return false;
        if (data.applicable === false) return false;
        return true;
    });

    // Map: question_id → [enriched chart entries]
    const chartsByQuestionId = new Map();
    visible.forEach(item => {
        const qid = String(item.chart.questionId);
        if (!chartsByQuestionId.has(qid)) chartsByQuestionId.set(qid, []);
        chartsByQuestionId.get(qid).push(item);
    });

    // Iterate over ALL phases and sections from `phases` state
    phases.forEach(phase => {
        const phaseCharts = [];

        (phase.sections || []).forEach(section => {
            (section.questions || []).forEach(q => {
                const matches = chartsByQuestionId.get(String(q.id));
                if (matches) {
                    matches.forEach(m => phaseCharts.push({ ...m, section }));
                }
            });
        });

        // Skip phases that have zero charts
        if (phaseCharts.length === 0) return;

        const phaseEl = document.createElement('div');
        phaseEl.className = 'analytics-group';

        const targetBadge = phase.target_type && phase.target_type !== 'all'
            ? `<span class="phase-target-badge ${phase.target_type}">
                   <i class="fa-solid ${phase.target_type === 'college' ? 'fa-graduation-cap' : 'fa-school'}"></i>
                   ${phase.target_type === 'college' ? 'College' : 'SHS'}
               </span>`
            : '';

        phaseEl.innerHTML = `
            <div class="analytics-group-header" style="border-left-color:${phase.color};">
                <div class="analytics-group-title">
                    <span class="analytics-group-dot" style="background:${phase.color};"></span>
                    <h3>${phase.title}</h3>
                    ${targetBadge}
                </div>
                <span class="analytics-group-count">
                    ${phaseCharts.length} chart${phaseCharts.length === 1 ? '' : 's'}
                </span>
            </div>
        `;

        (phase.sections || []).forEach(section => {
            const sectionCharts = phaseCharts.filter(item =>
                Number(item.section.id) === Number(section.id)
            );

            const sectionEl = document.createElement('div');
            sectionEl.className = 'analytics-section';
            sectionEl.innerHTML = `
                <div class="analytics-section-header">
                    <i class="fa-solid fa-folder-open"></i>
                    <span>${section.title}</span>
                    <span style="margin-left:auto; font-size:0.6875rem; color:#D1D5DB; text-transform:none; letter-spacing:0;">
                        ${sectionCharts.length} chart${sectionCharts.length === 1 ? '' : 's'}
                    </span>
                </div>
            `;

            const cardsWrap = document.createElement('div');
            cardsWrap.className = 'analytics-grid';

            if (sectionCharts.length === 0) {
                cardsWrap.innerHTML = `
                    <div class="analytics-empty-chart-placeholder">
                        <i class="fa-solid fa-chart-simple"></i>
                        <span>No charts yet for this section.</span>
                        <button class="btn-link" onclick="openChartBuilderModal()">
                            Add one →
                        </button>
                    </div>
                `;
            } else {
                sectionCharts.forEach(({ chart, data, index }) => {
                    const card = document.createElement('div');
                    card.className = 'analytics-card';
                    card.innerHTML = `
                        <div class="analytics-card-header">
                            <h3>${chart.title}</h3>
                            <div class="analytics-card-actions">
                                <button class="btn-icon" onclick="editChart(${index})" title="Edit Chart">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <button class="btn-icon delete" onclick="deleteChart(${index})" title="Remove Chart">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        </div>
                        <div class="chart-container" id="chartContainer${index}">
                            ${generateChartHTMLWithData(chart, data, index)}
                        </div>
                    `;
                    cardsWrap.appendChild(card);
                });
            }

            sectionEl.appendChild(cardsWrap);
            phaseEl.appendChild(sectionEl);
        });

        grid.appendChild(phaseEl);
    });
}

function generateChartHTMLWithData(chart, analyticsData, index) {
    // Handle: fetch failure, not applicable, or no data
    if (!analyticsData || analyticsData.error) {
        return `<p style="color: var(--gray-400); text-align: center; padding: 2rem;">No data available yet.</p>`;
    }
    if (analyticsData.applicable === false) {
        return `<p style="color: var(--gray-400); text-align: center; padding: 2rem;">
            Not applicable to this alumni type.
        </p>`;
    }

    let labels = [];
    let values = [];

    const colors = colorSchemes[chart.colorScheme] || colorSchemes.default;

    if (analyticsData.options && analyticsData.options.length > 0) {
        labels = analyticsData.options.map(o => o.label);
        values = analyticsData.options.map(o => o.count);
    } else if (analyticsData.gridData && analyticsData.gridData.length > 0) {
        labels = analyticsData.gridData.map(row => row.row_label);
        values = labels.map((_, rowIdx) => {
            return analyticsData.gridData[rowIdx].columns.reduce((sum, col) => sum + col.count, 0);
        });
    } else if (analyticsData.responseCount !== undefined) {
        labels = ['Responses'];
        values = [analyticsData.responseCount];
    } else {
        return `<p style="color: var(--gray-400); text-align: center; padding: 2rem;">No response data yet.</p>`;
    }

    // Extra guard: everything zero → show friendly empty state
    const total = values.reduce((a, b) => a + b, 0);
    if (total === 0) {
        return `<p style="color: var(--gray-400); text-align: center; padding: 2rem;">
            No responses recorded yet for this question.
        </p>`;
    }

    const maxValue = Math.max(...values, 1);

        if (chart.type === 'bar' || chart.type === 'horizontal_bar') {
            const isHorizontal = chart.type === 'horizontal_bar';
            return `
                <div class="${isHorizontal ? 'h-bar-list' : 'bar-chart'}">
                    ${labels.map((label, i) => {
                        const pct = Math.round((values[i] / maxValue) * 100);
                        const pctOfTotal = total > 0 ? Math.round((values[i] / total) * 100) : 0;
                        if (isHorizontal) {
                            return `
                                <div class="h-bar-item" style="cursor:pointer;" onclick="openDrilldown(${chart.questionId}, '${label.replace(/'/g, "\\'")}', '${chart.title.replace(/'/g, "\\'")}')">
                                    <div class="h-bar-header">
                                        <span class="h-bar-label">
                                            <span class="h-bar-dot" style="background:${colors[i % colors.length]};"></span> ${label}
                                        </span>
                                        <span class="h-bar-percent">${values[i]} (${pctOfTotal}%)</span>
                                    </div>
                                    <div class="h-bar-track">
                                        <div class="h-bar-fill" style="width:${pct}%; background:${colors[i % colors.length]};"></div>
                                    </div>
                                </div>
                            `;
                        }
                        return `
                            <div class="bar-chart-item" style="cursor:pointer;" onclick="openDrilldown(${chart.questionId}, '${label.replace(/'/g, "\\'")}', '${chart.title.replace(/'/g, "\\'")}')">
                                <span class="bar-value">${values[i]}</span>
                                <div class="bar-fill" style="height: ${Math.max(pct * 1.5, 10)}px; background: ${colors[i % colors.length]};"></div>
                                <span class="bar-label">${label.length > 10 ? label.substring(0, 10) + '...' : label}</span>
                            </div>
                        `;
                    }).join('')}
                </div>
            `;
        }

        if (chart.type === 'pie' || chart.type === 'doughnut') {
            if (total === 0) {
                return `<p style="color: var(--gray-400); text-align: center; padding: 2rem;">No responses yet.</p>`;
            }
            return `
                <div class="simple-pie-chart">
                    <div class="pie-legend">
                        ${labels.map((label, i) => {
                            const pct = total > 0 ? Math.round((values[i] / total) * 100) : 0;
                            return `
                                <div class="pie-legend-item" style="cursor:pointer;" onclick="openDrilldown(${chart.questionId}, '${label.replace(/'/g, "\\'")}', '${chart.title.replace(/'/g, "\\'")}')">
                                    <span class="pie-legend-dot" style="background:${colors[i % colors.length]};"></span>
                                    <span class="pie-legend-label">${label}</span>
                                    <span class="pie-legend-value">${values[i]} (${pct}%)</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                    <div class="pie-visual" style="
                        background: conic-gradient(
                            ${labels.map((label, i) => {
                                const pct = total > 0 ? (values[i] / total) * 100 : 0;
                                const prevPct = values.slice(0, i).reduce((a, b) => a + b, 0);
                                const prevAngle = total > 0 ? (prevPct / total) * 100 : 0;
                                return `${colors[i % colors.length]} ${prevAngle}% ${prevAngle + pct}%`;
                            }).join(', ')}
                        );
                        ${chart.type === 'doughnut' ? 'mask: radial-gradient(circle, transparent 40%, black 41%);' : ''}
                        ${chart.type === 'doughnut' ? '-webkit-mask: radial-gradient(circle, transparent 40%, black 41%);' : ''}
                    "></div>
                </div>
            `;
        }

        return `<p style="color: var(--gray-400); text-align: center;">Unknown chart type.</p>`;
    }

    // ── Drilldown ──
    async function openDrilldown(questionId, optionLabel, chartTitle) {
        const modal = document.getElementById('drilldownModal');
        const body = document.getElementById('drilldownBody');
        const title = document.getElementById('drilldownTitle');

        title.textContent = `${chartTitle} → ${optionLabel}`;
        body.innerHTML = `<div class="loading-overlay" style="padding: 2rem;"><div class="loading-spinner"></div></div>`;
        modal.classList.add('active');

        try {
            const data = await apiFetch(`${API_BASE}/question/${questionId}/drilldown?option=${encodeURIComponent(optionLabel)}`);
            const alumni = data.alumni || [];

            if (alumni.length === 0) {
                body.innerHTML = `<p style="padding: 2rem; text-align:center; color:var(--gray-400);">No alumni have chosen this option yet.</p>`;
                return;
            }

            body.innerHTML = `
                <div style="padding: 0.75rem 1.25rem; border-bottom: 1px solid #F3F4F6; background: #F9FAFB;">
                    <strong>${alumni.length}</strong> ${alumni.length === 1 ? 'alumnus' : 'alumni'} chose this
                </div>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${alumni.map(a => `
                        <div style="padding: 0.75rem 1.25rem; display: flex; align-items: center; gap: 0.75rem; border-bottom: 1px solid #F9FAFB;">
                            <div style="width: 36px; height: 36px; border-radius: 50%; background: #32418C; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.75rem; flex-shrink: 0;">
                                ${a.name.split(' ').map(n => n[0]).join('').toUpperCase().slice(0, 2)}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <div style="font-weight: 600; font-size: 0.875rem; color: #1F2937;">${a.name}</div>
                                <div style="font-size: 0.75rem; color: #9CA3AF;">${a.email}</div>
                            </div>
                            ${a.program ? `<div style="font-size: 0.75rem; color: #6B7280; text-align: right;">${a.program}${a.year_graduated ? ' · ' + a.year_graduated : ''}</div>` : ''}
                        </div>
                    `).join('')}
                </div>
            `;
        } catch (e) {
            body.innerHTML = `<p style="padding: 2rem; text-align:center; color:#ef4444;">Failed to load: ${e.message}</p>`;
        }
    }

    function closeDrilldownModal() {
        document.getElementById('drilldownModal').classList.remove('active');
    }

    // ── Helpers ──
    function findQuestionById(questionId) {
        for (const phase of phases) {
            for (const section of phase.sections) {
                const q = section.questions.find(q => q.id == questionId);
                if (q) return q;
            }
        }
        return null;
    }

    function updateChartQuestionDropdown() {
        const select = document.getElementById('chartQuestion');
        const currentValue = select.value;
        let options = '<option value="">-- Select a question --</option>';
        for (const phase of phases) {
            for (const section of phase.sections) {
                for (const q of section.questions) {
                    if (q.type !== 'file_upload') {
                        const label = q.label.length > 60 ? q.label.substring(0, 57) + '...' : q.label;
                        options += `<option value="${q.id}">[${typeLabels[q.type]}] ${label}</option>`;
                    }
                }
            }
        }
        select.innerHTML = options;
        if (currentValue && [...select.options].some(o => o.value === currentValue)) {
            select.value = currentValue;
        }
    }

    function updatePhaseFilterDropdown() {
        const select = document.getElementById('chartPhaseFilter');
        select.innerHTML = '<option value="">All Phases</option>' +
            phases.map(p => `<option value="${p.id}">${p.title}</option>`).join('');
    }

    // ── Chart Builder Modal ──
    let currentEditingChart = null;
    let selectedColorScheme = 'default';

    const colorSchemes = {
        default: ['#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#06b6d4', '#ec4899', '#f97316', '#84cc16', '#14b8a6'],
        ocean: ['#06b6d4', '#0ea5e9', '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#f97316'],
        forest: ['#22c55e', '#10b981', '#059669', '#047857', '#065f46', '#84cc16', '#a3e635', '#65a30d', '#4d7c0f', '#3f6212'],
        sunset: ['#f97316', '#ef4444', '#ec4899', '#a855f7', '#6366f1', '#3b82f6', '#0ea5e9', '#06b6d4', '#10b981', '#84cc16']
    };

    function openChartBuilderModal(chartIndex = null) {
        updateChartQuestionDropdown();
        updatePhaseFilterDropdown();

        if (chartIndex !== null && chartIndex !== undefined) {
            const chart = analyticsCharts[chartIndex];
            currentEditingChart = chartIndex;
            document.getElementById('chartBuilderModalTitle').textContent = 'Edit Chart';
            document.getElementById('chartTitle').value = chart.title;
            document.getElementById('chartType').value = chart.type;
            document.getElementById('chartQuestion').value = chart.questionId;
            document.getElementById('chartPhaseFilter').value = chart.phaseFilter || '';
            selectedColorScheme = chart.colorScheme || 'default';
        } else {
            currentEditingChart = null;
            document.getElementById('chartBuilderModalTitle').textContent = 'Add Analytics Chart';
            document.getElementById('chartTitle').value = '';
            document.getElementById('chartType').value = 'bar';
            document.getElementById('chartQuestion').value = '';
            document.getElementById('chartPhaseFilter').value = '';
            selectedColorScheme = 'default';
        }

        handleChartTypeChange();
        handleChartQuestionChange();
        renderColorSchemeOptions();
        document.getElementById('chartBuilderModal').classList.add('active');
    }

    function closeChartBuilderModal() {
        document.getElementById('chartBuilderModal').classList.remove('active');
        currentEditingChart = null;
    }

    function handleChartTypeChange() {
        // Show color options for all types
        document.getElementById('chartColorGroup').style.display = 'block';
    }

    function handleChartQuestionChange() {
        const questionId = document.getElementById('chartQuestion').value;
        const question = questionId ? findQuestionById(questionId) : null;
        const helpEl = document.getElementById('chartQuestionHelp');

        if (question) {
            if (question.type === 'multiple_choice' || question.type === 'dropdown') {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · ${question.options?.length || 0} options · Pie or Bar chart.`;
            } else if (question.type === 'checkboxes') {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · ${question.options?.length || 0} options · Each option counted independently.`;
            } else if (question.type === 'likert_scale' || question.type === 'multiple_choice_grid') {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · ${question.gridRows?.length || 0} rows × ${question.gridColumns?.length || 0} columns · Horizontal Bar.`;
            } else {
                helpEl.textContent = `Type: ${typeLabels[question.type]} · Shows response count.`;
            }
        } else {
            helpEl.textContent = 'Choose a question to visualize its response data.';
        }
    }

    function selectColorScheme(scheme, button) {
        selectedColorScheme = scheme;
        document.querySelectorAll('.color-scheme-option').forEach(b => b.classList.remove('selected'));
        button.classList.add('selected');
    }

    function renderColorSchemeOptions() {
        document.querySelectorAll('.color-scheme-option').forEach(btn => {
            btn.classList.toggle('selected', btn.dataset.scheme === selectedColorScheme);
        });
    }

function saveChart() {
    const title = document.getElementById('chartTitle').value.trim();
    const type = document.getElementById('chartType').value;
    const questionId = document.getElementById('chartQuestion').value;
    const phaseFilter = document.getElementById('chartPhaseFilter').value;

    if (!title) { alert('Please enter a chart title.'); return; }
    if (!questionId) { alert('Please select a question to visualize.'); return; }

    // Find which phase/section this question belongs to (for grouping)
    let sectionTitle = null, phaseTitle = null, phaseColor = null;
    for (const phase of phases) {
        for (const section of phase.sections) {
            if (section.questions.some(q => String(q.id) === String(questionId))) {
                sectionTitle = section.title;
                phaseTitle   = phase.title;
                phaseColor   = phase.color;
                break;
            }
        }
        if (sectionTitle) break;
    }

    const chartData = {
        id: currentEditingChart !== null
            ? (analyticsCharts[currentEditingChart].id || `chart_${Date.now()}`)
            : `chart_${Date.now()}`,
        title,
        type,
        questionId,
        phaseFilter: phaseFilter || null,
        colorScheme: selectedColorScheme,
        sectionTitle,
        phaseTitle,
        phaseColor,
    };

    if (currentEditingChart !== null) {
        analyticsCharts[currentEditingChart] = chartData;
    } else {
        analyticsCharts.push(chartData);
    }

    saveAnalyticsCharts();
    closeChartBuilderModal();
    renderAnalyticsChartsGrid();
    showToast('Chart saved!', 'success');   // 🆕 visible confirmation
}

    function editChart(index) { openChartBuilderModal(index); }

    function deleteChart(index) {
        if (!confirm('Remove this chart from the analytics dashboard?')) return;
        analyticsCharts.splice(index, 1);
        saveAnalyticsCharts();
        renderAnalyticsChartsGrid();
    }

    function exportAnalyticsReport() {
        alert('Analytics export will be available when response data is connected.');
    }

/**
 * Auto-generate a chart config for EVERY question that can be visualized.
 * Choice questions → pie/doughnut
 * Grid questions → horizontal bar
 * Text questions → bar (shows response count)
 */
function getDefaultCharts() {
    const charts = [];

    for (const phase of phases) {
        for (const section of phase.sections) {
            for (const q of section.questions) {
                const type = q.type;

                // Skip file uploads
                if (type === 'file_upload') continue;

                let chartType = 'bar';

                if (type === 'multiple_choice' || type === 'dropdown') {
                    chartType = 'pie';
                } else if (type === 'checkboxes') {
                    chartType = 'bar';
                } else if (type === 'likert_scale' || type === 'multiple_choice_grid') {
                    chartType = 'horizontal_bar';
                } else if (type === 'short_answer' || type === 'paragraph') {
                    chartType = 'bar';
                }

                charts.push({
                    id: `auto_${q.id}`,
                    title: q.label,
                    type: chartType,
                    questionId: q.id,
                    phaseFilter: null,
                    colorScheme: 'default',
                    sectionTitle: section.title,
                    phaseTitle: phase.title,
                    phaseColor: phase.color,
                    autoGenerated: true,   // ← mark as auto so we can distinguish
                });
            }
        }
    }

    return charts;
}

</script>