/**
 * ═══════════════════════════════════════════════════════════
 * INSTITUTIONAL ANALYTICS - FRONTEND CONTROLLER (V3)
 * ═══════════════════════════════════════════════════════════
 */

let instType = 'all';
let instTab = 'overview';
let currentInstData = {
    kpis: null,
    careerMatrix: null,
    employment: null,
    trendData: null,
    shsChoice: null,
    shsMatrix: null,
    checkpointInsights: null,
    narratives: null
};

let instCharts = {}; // Holds Chart.js instances
const INST_BASE = `${API_BASE}/institutional`;

// The institutional panel is loaded by the shared tab controller.
// Keep the legacy initializer name as an alias for older markup.
function initializeInstitutional() {
    return refreshInstitutional();
}

function escapeInstHtml(value) {
    return String(value ?? '').replace(/[&<>"']/g, character => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;'
    }[character]));
}

async function loadInstNarratives() {
    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/narratives`);
        currentInstData.narratives = data;
        renderInstNarrative('instCollegeNarrative', data.college);
        renderInstNarrative('instShsNarrative', data.shs);
    } catch (e) {
        console.error('Failed to load institutional narratives:', e);
        ['instCollegeNarrative', 'instShsNarrative'].forEach(id => {
            const target = document.getElementById(id);
            if (target) target.innerHTML = '<div class="narrative-empty">Narrative unavailable. Check the analytics data source.</div>';
        });
    }
}

function renderInstNarrative(elementId, narrative) {
    const target = document.getElementById(elementId);
    if (!target || !narrative) return;

    const metrics = narrative.metrics || {};
    const metricLabels = {
        records: 'Records',
        continuation_rate: 'College continuation',
        employment_rate: 'Employment coverage',
        satisfaction: 'Satisfaction',
        preparedness: 'Preparedness',
        interest_match: 'Interest match',
        usefulness: 'Learning usefulness',
        useful_skills: 'Useful skills'
    };
    const metricItems = Object.entries(metrics)
        .filter(([, value]) => value !== null && value !== undefined)
        .map(([key, value]) => {
            const suffix = key.includes('rate') ? '%' : (key !== 'records' ? '/5' : '');
            return `<div class="narrative-metric"><span>${escapeInstHtml(metricLabels[key] || key)}</span><strong>${escapeInstHtml(value)}${suffix}</strong></div>`;
        }).join('');

    target.innerHTML = `
        <div class="inst-narrative">
            <div class="narrative-metrics">${metricItems}</div>
            <div class="narrative-copy">
                ${(narrative.paragraphs || []).map(text => `<p>${escapeInstHtml(text)}</p>`).join('')}
            </div>
            <div class="narrative-actions">
                <strong>Recommended institutional actions</strong>
                <ul>${(narrative.recommendations || []).map(text => `<li>${escapeInstHtml(text)}</li>`).join('')}</ul>
            </div>
            <small class="narrative-disclaimer">Generated from recorded tracer responses using transparent rule-based thresholds; it is not an AI-generated conclusion.</small>
        </div>
    `;
}

/* ── TAB SWITCHING ── */
function switchInstTab(tabName) {
    instTab = tabName;
    
    // Update active button
    document.querySelectorAll('.inst-tab').forEach(btn => {
        btn.classList.toggle('active', btn.getAttribute('onclick').includes(tabName));
    });
    
    // Update active content
    document.querySelectorAll('.inst-view-content').forEach(el => {
        el.style.display = 'none';
        el.classList.remove('active');
    });
    
    const target = document.getElementById(`inst-view-${tabName}`);
    if (target) {
        target.style.display = 'block';
        setTimeout(() => target.classList.add('active'), 10);
    }

    // Redraw charts
    if (instCharts.employment) instCharts.employment.resize();
    if (instCharts.trend) instCharts.trend.resize();
}

function setInstitutionalType(type) {
    instType = type;
    document.querySelectorAll('[data-inst-type]').forEach(t => 
        t.classList.toggle('active', t.dataset.instType === type));
    refreshInstitutional();
}

/* ── MAIN REFRESH ── */
async function refreshInstitutional() {
    if (!currentFormId) return;
    
    await loadInstKPIs();
    loadInstQuality();
    loadInstCareerMatrix();
    loadInstEmployment();
    loadInstTrend();  
    loadInstCheckpointInsights();
    loadInstShsChoice();
    loadInstShsMatrix();
    loadInstIndustry('all'); 
    loadInstNarratives();
}

/* ── 1. KPIs ── */
async function loadInstKPIs() {
    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/kpis?alumni_type=${instType}`);
        currentInstData.kpis = data;
        
        document.getElementById('instEmploymentRate').textContent = (data.employmentRate ?? 0) + '%';
        document.getElementById('instEmploymentSub').textContent = `${data.employedCount ?? 0} of ${data.totalAlumni ?? 0} employed`;
        
        document.getElementById('instAlignmentRate').textContent = (data.alignmentRate ?? 0) + '%';
        document.getElementById('instAlignmentSub').textContent = 'Of classified respondents';
        
        document.getElementById('instCoverage').textContent = (data.coverage ?? 0) + '%';
        document.getElementById('instCoverageSub').textContent = `${data.respondentCount ?? 0} of ${data.totalAlumni ?? 0} responded`;
        
        document.getElementById('instPrograms').textContent = data.programsTracked ?? 0;
    } catch (e) {
        console.error('Failed to load KPIs:', e);
    }
}

/* ── 2. DATA QUALITY ── */
async function loadInstQuality() {
    const grid = document.getElementById('instQualityGrid');
    if (!grid) return;

    try {
        const kpis = currentInstData.kpis || {};
        const matrix = currentInstData.careerMatrix;
        
        let missingEmployment = 0;
        let missingProgram = 0;

        if (matrix && matrix.rows) {
            matrix.rows.forEach(r => {
                if (r.program === 'Unknown') missingProgram += r.total;
                else missingEmployment += (r.not_applicable || 0);
            });
        }

        const totalAlumni = kpis.totalAlumni || 0;
        const respondentCount = kpis.respondentCount || 0;
        const coverage = kpis.coverage || 0;

        const cell = (label, value, hint, level) => `
            <div class="quality-item">
                <div class="quality-label">${label}</div>
                <div class="quality-value ${level}">${value}</div>
                <div class="quality-hint">${hint}</div>
            </div>
        `;

        grid.innerHTML = [
            cell('Missing Program', missingProgram, 'No linked college program', missingProgram > 0 ? 'warn' : 'ok'),
            cell('No Occupation', missingEmployment, 'Unemployed or unmapped', missingEmployment > 0 ? 'warn' : 'ok'),
            cell('Coverage', coverage + '%', 'Tracer completion rate', coverage >= 80 ? 'ok' : 'warn'),
            cell('Total Analyzed', totalAlumni, 'In the pipeline', 'ok'),
        ].join('');
    } catch (e) {
        console.error('Failed to load data quality:', e);
    }
}

/* ── 3. COLLEGE → CAREER MATRIX ── */
async function loadInstCareerMatrix() {
    const wrapper = document.getElementById('instMatrixWrapper');
    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/program-matrix?alumni_type=${instType}`);
        currentInstData.careerMatrix = data;
        
        if (!data.rows || data.rows.length === 0) {
            wrapper.innerHTML = `<div style="padding:3rem;text-align:center;color:#9CA3AF;">No data available yet.</div>`;
            return;
        }

        const columns = [
            { key: 'directly_related', label: 'Directly Related', color: '#10B981' },
            { key: 'somewhat_related', label: 'Somewhat Related', color: '#3B82F6' },
            { key: 'not_related', label: 'Not Related', color: '#F59E0B' },
            { key: 'not_applicable', label: 'Undetermined', color: '#94A3B8' }
        ];

        wrapper.innerHTML = `
            <div class="chart-container" id="instAlignmentChartContainer" style="height: ${Math.max(360, data.rows.length * 58 + 90)}px; padding: 1rem 1.5rem;">
                <canvas id="instAlignmentChartCanvas"></canvas>
            </div>
        `;

        if (instCharts.alignment) instCharts.alignment.destroy();
        const canvas = document.getElementById('instAlignmentChartCanvas');
        const rows = [...data.rows].sort((a, b) => (b.total || 0) - (a.total || 0));
        const totals = rows.map(row => row.total || 0);

        instCharts.alignment = new Chart(canvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: rows.map(row => row.program),
                datasets: columns.map(column => ({
                    label: column.label,
                    data: rows.map(row => row[column.key] || 0),
                    backgroundColor: column.color,
                    borderWidth: 0,
                    borderRadius: 3,
                    barPercentage: 0.72,
                    categoryPercentage: 0.8
                }))
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: {
                        beginAtZero: true,
                        stacked: true,
                        ticks: { precision: 0 },
                        title: { display: true, text: 'Number of alumni' },
                        grid: { color: '#F1F5F9' }
                    },
                    y: {
                        stacked: true,
                        grid: { display: false },
                        ticks: { color: '#1E293B', font: { weight: '600' } }
                    }
                },
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: (context) => {
                                const value = Number(context.raw || 0);
                                const total = totals[context.dataIndex] || 0;
                                const percentage = total ? ((value / total) * 100).toFixed(1) : '0.0';
                                return `${context.dataset.label}: ${value} alumni (${percentage}%)`;
                            },
                            footer: (items) => `Total analyzed: ${totals[items[0].dataIndex] || 0}`
                        }
                    }
                }
            }
        });
    } catch (e) {
        wrapper.innerHTML = `<div style="padding:2rem;color:#EF4444;text-align:center;">Failed to load matrix.</div>`;
    }
}

/* ── 4. EMPLOYMENT COVERAGE (Chart.js) ── */
async function loadInstEmployment() {
    const canvas = document.getElementById('instEmploymentChartCanvas');
    if (!canvas) return;

    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/employment-by-program?alumni_type=${instType}`);
        currentInstData.employment = data;
        
        if (!data || data.length === 0) {
            canvas.parentNode.innerHTML = `<div style="padding:2rem;text-align:center;color:#9CA3AF;">No employment data.</div>`;
            return;
        }

        if (instCharts.employment) instCharts.employment.destroy();

        const ctx = canvas.getContext('2d');
        instCharts.employment = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(r => r.program),
                datasets: [{
                    label: 'Employment Rate (%)',
                    data: data.map(r => r.rate),
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                    x: { grid: { display: false } }
                }
            }
        });
    } catch (e) {
        canvas.parentNode.innerHTML = `<div style="padding:2rem;color:#EF4444;">Failed to load.</div>`;
    }
}

/* ── 5. TREND BY YEAR (Chart.js) ── */
async function loadInstTrend() {
    const canvas = document.getElementById('instTrendChartCanvas');
    const filterSelect = document.getElementById('instBatchFilter');
    if (!canvas) return;

    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/trend-by-year?alumni_type=${instType}`);
        currentInstData.trendData = data;

        if (!data || data.length === 0) {
            canvas.parentNode.innerHTML = `<div style="padding:2rem;text-align:center;color:#9CA3AF;">No graduation year data.</div>`;
            return;
        }

        // --- NEW: Populate the Batch Filter Dropdown ---
        if (filterSelect) {
            // Keep the "All Batches" option, remove old ones
            filterSelect.innerHTML = '<option value="all">All Batches</option>';
            
            // Add each year as an option
            data.forEach(row => {
                const option = document.createElement('option');
                option.value = row.year;
                option.textContent = `Batch ${row.year}`;
                filterSelect.appendChild(option);
            });
        }

        // Draw the initial chart (All Batches)
        renderTrendChart(data);

    } catch (e) {
        canvas.parentNode.innerHTML = `<div style="padding:2rem;color:#EF4444;">Failed to load.</div>`;
    }
}

/* ── 6. SHS CONTINUATION (Chart.js) ── */
async function loadInstShsChoice() {
    const canvas = document.getElementById('instShsContinuationCanvas');
    const topProgBody = document.getElementById('instShsTopPrograms');
    const summary = document.getElementById('instShsContinuationSummary');
    const statusCanvas = document.getElementById('instShsStatusCanvas');
    if (!canvas || !topProgBody) return;

    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/shs-college-choice`);
        currentInstData.shsChoice = data;
        
        if (!data.has_data) {
            canvas.parentNode.innerHTML = `<div style="padding:2rem;text-align:center;color:#9CA3AF;">No SHS data found.</div>`;
            topProgBody.innerHTML = `<div style="padding:2rem;text-align:center;color:#9CA3AF;">No data.</div>`;
            if (summary) summary.innerHTML = '';
            return;
        }

        const statusCounts = data.status_counts || {};
        if (summary) {
            const cards = [
                ['SHS cohort', data.total_shs_alumni || 0, 'All completed SHS alumni'],
                ['Continued to NU Lipa', data.continued_count || 0, `${data.continuation_rate || 0}% continuation rate`],
                ['Graduated at NU Lipa', statusCounts.graduated || 0, 'College program status'],
            ];
            summary.innerHTML = cards.map(([label, value, hint]) => `
                <div class="shs-summary-item">
                    <span>${label}</span><strong>${value}</strong><small>${hint}</small>
                </div>
            `).join('');
        }

        // 1. Draw the Bar Chart (Strand Comparison)
        if (instCharts.shsContinuation) instCharts.shsContinuation.destroy();
        const ctx = canvas.getContext('2d');
        
        instCharts.shsContinuation = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.chart_labels,
                datasets: [{
                    label: 'Continuation Rate (%)',
                    data: data.chart_data,
                    backgroundColor: '#32418C',
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.raw}% continued to college`
                        }
                    }
                },
                scales: {
                    y: { 
                        beginAtZero: true, 
                        max: 100, 
                        ticks: { callback: v => v + '%' },
                        grid: { color: '#F1F5F9' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });

        if (statusCanvas) {
            if (instCharts.shsStatus) instCharts.shsStatus.destroy();
            instCharts.shsStatus = new Chart(statusCanvas.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Enrolled', 'Left before graduation', 'Graduated', 'Did not continue'],
                    datasets: [{
                        data: [
                            statusCounts.enrolled || 0,
                            statusCounts.dropped || 0,
                            statusCounts.graduated || 0,
                            statusCounts.not_continued || 0
                        ],
                        backgroundColor: ['#3B82F6', '#F59E0B', '#10B981', '#CBD5E1'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => `${ctx.label}: ${ctx.raw} alumni`
                            }
                        }
                    }
                }
            });
        }

        // 2. Render Top Programs (List)
        if (data.top_college_programs && data.top_college_programs.length > 0) {
            topProgBody.innerHTML = `<div class="inst-list">` + data.top_college_programs.map((p, i) => `
                <div class="list-item">
                    <span class="list-rank">${i + 1}</span>
                    <span class="list-name" title="${p.program_name}">${p.program_name}</span>
                    <span class="list-count">${p.count}</span>
                </div>
            `).join('') + `</div>`;
        } else {
            topProgBody.innerHTML = `<div style="padding:2rem;text-align:center;color:#9CA3AF;">No programs chosen.</div>`;
        }

    } catch (e) {
        canvas.parentNode.innerHTML = `<div style="padding:2rem;color:#EF4444;">Failed to load.</div>`;
        topProgBody.innerHTML = `<div style="padding:2rem;color:#EF4444;">Failed to load.</div>`;
    }
}

/* ── 7. SHS MATRIX ── */
async function loadInstShsMatrix() {
    const wrapper = document.getElementById('instShsMatrixWrapper');
    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/shs-alignment-matrix`);
        currentInstData.shsMatrix = data;
        
        if (!data.rows || data.rows.length === 0) {
            wrapper.innerHTML = `<div style="padding:3rem;text-align:center;color:#9CA3AF;">No SHS alignment data yet.</div>`;
            return;
        }

        const columns = [
            { key: 'directly_related',  label: 'Directly Related', color: 'bg-direct' },
            { key: 'somewhat_related',  label: 'Somewhat Related', color: 'bg-related' },
            { key: 'not_related',       label: 'Not Related',      color: 'bg-notdirect' },
            { key: 'not_applicable',    label: 'Undetermined',     color: 'bg-undetermined' }
        ];

        wrapper.innerHTML = `
            <table class="inst-matrix">
                <thead>
                    <tr>
                        <th>SHS Strand</th>
                        ${columns.map(c => `<th class="center">${c.label}</th>`).join('')}
                        <th class="center">Total</th>
                    </tr>
                </thead>
                <tbody>
                    ${data.rows.map(row => {
                        const total = row.total || 1;
                        return `
                            <tr>
                                <td><strong>${row.program}</strong></td>
                                ${columns.map(col => {
                                    const val = row[col.key] || 0;
                                    const pct = Math.round((val / total) * 100);
                                    return `
                                        <td class="center">
                                            <div class="matrix-cell-wrapper">
                                                <div class="matrix-bar-bg">
                                                    <div class="matrix-bar-fill ${col.color}" style="width: ${pct}%"></div>
                                                </div>
                                                <span class="matrix-count">${val}</span>
                                            </div>
                                        </td>
                                    `;
                                }).join('')}
                                <td class="center"><strong>${total}</strong></td>
                            </tr>
                        `;
                    }).join('')}
                </tbody>
            </table>
        `;
    } catch (e) {
        wrapper.innerHTML = `<div style="padding:2rem;color:#EF4444;text-align:center;">Failed to load matrix.</div>`;
    }
}

/* ── 8. CHECKPOINT INSIGHTS (Curriculum Effectiveness) ── */
async function loadInstCheckpointInsights() {
    try {
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/checkpoint-insights?alumni_type=${instType}`);
        if (!data || !data.scatter_data) return;
        currentInstData.checkpointInsights = data;
        const checkpointFilter = document.getElementById('instCheckpointProgramFilter');
        if (checkpointFilter) {
            checkpointFilter.innerHTML = '<option value="all">All Programs</option>';
            (data.chart_labels || []).forEach(program => {
                const option = document.createElement('option');
                option.value = program;
                option.textContent = program;
                checkpointFilter.appendChild(option);
            });
        }

        // 1. Scatter Plot (Satisfaction vs Alignment)
        const scatterCtx = document.getElementById('instScatterChartCanvas')?.getContext('2d');
        if (scatterCtx) {
            if (instCharts.scatter) instCharts.scatter.destroy();
            
            // Custom plugin to draw labels on the scatter points
            const scatterLabelPlugin = {
                id: 'scatterLabel',
                afterDatasetsDraw(chart) {
                    const { ctx } = chart;
                    chart.data.datasets.forEach((dataset, i) => {
                        const meta = chart.getDatasetMeta(i);
                        meta.data.forEach((element, index) => {
                            const data = dataset.data[index];
                            ctx.save();
                            ctx.fillStyle = '#1E293B';
                            ctx.font = 'bold 11px Poppins';
                            ctx.textAlign = 'center';
                            // Draw the label 12px above the dot
                            ctx.fillText(data.label, element.x, element.y - 12);
                            ctx.restore();
                        });
                    });
                }
            };

            instCharts.scatter = new Chart(scatterCtx, {
                type: 'scatter',
                data: {
                    datasets: [{
                        label: 'Programs',
                        data: data.scatter_data,
                        backgroundColor: '#32418C',
                        borderColor: '#fff',
                        borderWidth: 2,
                        pointRadius: 8,
                        pointHoverRadius: 12
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const p = ctx.raw;
                                    return `${p.label}: Satisfaction ${p.x}/5, Alignment ${p.y}%`;
                                }
                            }
                        }
                    },
                    scales: {
                        x: { 
                            title: { display: true, text: 'Avg. Satisfaction (1-5)' }, 
                            min: 0, 
                            max: 5.5, // <-- CHANGED from 5 to 5.5 for right padding
                            grid: { color: '#F1F5F9' }
                        },
                        y: { 
                            title: { display: true, text: 'Direct Alignment (%)' }, 
                            min: 0, 
                            max: 110, // <-- CHANGED from 100 to 110 to prevent top label cutoff
                            grid: { color: '#F1F5F9' }
                        }
                    }
                },
                plugins: [scatterLabelPlugin] // Activate the label plugin
            });
        }

        // 2. Preparedness & Influence (Bar Chart)
        const prepCtx = document.getElementById('instPreparednessChartCanvas')?.getContext('2d');
        if (prepCtx) {
            if (instCharts.preparedness) instCharts.preparedness.destroy();
            instCharts.preparedness = new Chart(prepCtx, {
                type: 'bar',
                data: {
                    labels: data.chart_labels,
                    datasets: [
                        { label: 'Preparedness', data: data.preparedness, backgroundColor: '#3B82F6', borderRadius: 4 },
                        { label: 'Influence', data: data.influence, backgroundColor: '#8B5CF6', borderRadius: 4 },
                        { label: 'Useful Skills', data: data.useful_skills || [], backgroundColor: '#10B981', borderRadius: 4 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } },
                    scales: { y: { beginAtZero: true, max: 5, ticks: { stepSize: 1 } } }
                }
            });
        }

        // 3. Sentiment (Doughnut Chart)
        const sentCtx = document.getElementById('instSentimentChartCanvas')?.getContext('2d');
        if (sentCtx) {
            if (instCharts.sentiment) instCharts.sentiment.destroy();
            instCharts.sentiment = new Chart(sentCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Definitely Yes', 'Probably Yes', 'Unsure', 'Probably Not', 'Definitely Not'],
                    datasets: [{
                        data: [
                            data.would_choose_again.definitely_yes,
                            data.would_choose_again.probably_yes,
                            data.would_choose_again.unsure,
                            data.would_choose_again.probably_not,
                            data.would_choose_again.definitely_not
                        ],
                        backgroundColor: ['#10B981', '#34D399', '#F59E0B', '#F87171', '#EF4444'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { position: 'bottom' } }
                }
            });
        }

        renderInstCheckpointActions(data);

    } catch (e) {
        console.error('Failed to load checkpoint insights:', e);
    }
}

function renderInstCheckpointActions(data) {
    const priorityBody = document.getElementById('instCurriculumPriorities');
    if (priorityBody) {
        const priorities = data.curriculum_priorities || [];
        priorityBody.innerHTML = priorities.length
            ? `<div class="inst-list">${priorities.map(item => `
                <div class="list-item">
                    <span class="list-rank ${item.priority === 'high' ? 'text-danger' : ''}">${item.priority === 'high' ? '!' : '•'}</span>
                    <span class="list-name">
                        <strong>${item.program}</strong><br>
                        <small>${item.signals.join(' · ')}</small>
                        ${item.evidence ? `<br><small>Responses: ${item.evidence.respondents}
                            · Satisfaction: ${item.evidence.satisfaction ?? '—'}/5
                            · Preparedness: ${item.evidence.preparedness ?? '—'}/5
                            · Skills: ${item.evidence.useful_skills ?? '—'}/5
                            · Direct alignment: ${item.evidence.direct_alignment ?? '—'}%</small>` : ''}
                    </span>
                    <span class="list-count">${item.priority === 'high' ? 'High' : item.priority === 'watch' ? 'Watch' : 'Stable'}</span>
                </div>`).join('')}</div>`
            : `<div style="padding:2rem;text-align:center;color:#64748B;">No priority signals were detected in the available checkpoint responses.</div>`;
    }

    const shs = data.shs || {};
    const averages = shs.averages || {};
    const outcomesCanvas = document.getElementById('instShsOutcomesCanvas');
    if (outcomesCanvas && Object.keys(averages).length) {
        if (instCharts.shsOutcomes) instCharts.shsOutcomes.destroy();
        instCharts.shsOutcomes = new Chart(outcomesCanvas.getContext('2d'), {
            type: 'radar',
            data: {
                labels: ['Satisfaction', 'Matches interests', 'Preparedness', 'Useful learning', 'College-choice satisfaction'],
                datasets: [{
                    label: 'SHS average',
                    data: [
                        averages.satisfaction, averages.match_interests, averages.preparedness,
                        averages.usefulness, averages.satisfaction_choice
                    ],
                    backgroundColor: 'rgba(50, 65, 140, 0.18)',
                    borderColor: '#32418C',
                    pointBackgroundColor: '#32418C'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: { r: { min: 0, max: 5, ticks: { stepSize: 1 } } },
                plugins: { legend: { display: false } }
            }
        });
    }

    const activityCanvas = document.getElementById('instShsActivityCanvas');
    const activities = shs.primary_activity || {};
    if (activityCanvas && Object.keys(activities).length) {
        if (instCharts.shsActivity) instCharts.shsActivity.destroy();
        instCharts.shsActivity = new Chart(activityCanvas.getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: Object.keys(activities),
                datasets: [{
                    data: Object.values(activities),
                    backgroundColor: ['#32418C', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4']
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    const pathwayCanvas = document.getElementById('instCollegePathwayCanvas');
    const college = data.college || {};
    const relation = college.perceived_relation || {};
    const furtherStudy = college.further_study || {};
    const employment = college.employment_status || {};
    const reasons = college.reason_not_related || {};
    const pathwayLabels = [
        ...Object.keys(relation).map(key => `Relation: ${key}`),
        ...Object.keys(furtherStudy).map(key => `Further study: ${key}`),
        ...Object.keys(employment).map(key => `Employment: ${key}`),
        ...Object.keys(reasons).map(key => `Not-related reason: ${key}`)
    ];
    const pathwayValues = [
        ...Object.values(relation),
        ...Object.values(furtherStudy),
        ...Object.values(employment),
        ...Object.values(reasons)
    ];
    if (pathwayCanvas && pathwayLabels.length) {
        if (instCharts.pathway) instCharts.pathway.destroy();
        instCharts.pathway = new Chart(pathwayCanvas.getContext('2d'), {
            type: 'bar',
            data: {
                labels: pathwayLabels,
                datasets: [{
                    label: 'Alumni',
                    data: pathwayValues,
                    backgroundColor: pathwayLabels.map((_, index) => ['#32418C', '#10B981', '#F59E0B'][index % 3]),
                    borderRadius: 4
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { precision: 0 } }, y: { grid: { display: false } } }
            }
        });
    }
}

function filterInstCheckpointProgram(program) {
    const data = currentInstData.checkpointInsights;
    if (!data) return;

    const selectedIndex = (data.chart_labels || []).indexOf(program);
    const indexes = selectedIndex === -1
        ? (data.chart_labels || []).map((_, index) => index)
        : [selectedIndex];
    const labels = indexes.map(index => data.chart_labels[index]);

    if (instCharts.scatter) {
        instCharts.scatter.data.datasets[0].data = indexes.map(index => data.scatter_data[index]);
        instCharts.scatter.update();
    }
    if (instCharts.preparedness) {
        instCharts.preparedness.data.labels = labels;
        instCharts.preparedness.data.datasets[0].data = indexes.map(index => data.preparedness[index]);
        instCharts.preparedness.data.datasets[1].data = indexes.map(index => data.influence[index]);
        instCharts.preparedness.data.datasets[2].data = indexes.map(index => (data.useful_skills || [])[index] || 0);
        instCharts.preparedness.update();
    }
}

/* ── TREND CHART RENDERER ── */
function renderTrendChart(data) {
    const canvas = document.getElementById('instTrendChartCanvas');
    if (!canvas) return;

    if (instCharts.trend) instCharts.trend.destroy();

    const ctx = canvas.getContext('2d');
    
    // We want the line to be green if it's above 50%, red if it's below
    const barColor = data.map(r => r.rate >= 50 ? '#10B981' : '#EF4444');

    instCharts.trend = new Chart(ctx, {
        type: 'bar', // Changed to bar for better visibility of individual batches
        data: {
            labels: data.map(r => `Batch ${r.year}`),
            datasets: [{
                label: 'Employment Rate (%)',
                data: data.map(r => r.rate),
                backgroundColor: barColor,
                borderRadius: 6,
                barPercentage: 0.6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const row = data[ctx.dataIndex];
                            return `${row.employed} of ${row.total} employed (${row.rate}%)`;
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                x: { grid: { display: false } }
            }
        }
    });
}

/* ── BATCH FILTER HANDLER ── */
function filterInstTrendByBatch(batchYear) {
    const data = currentInstData.trendData;
    if (!data) return;

    if (batchYear === 'all') {
        // Show all batches
        renderTrendChart(data);
    } else {
        // Filter to just the selected batch
        const filteredData = data.filter(row => String(row.year) === String(batchYear));
        renderTrendChart(filteredData);
    }
}

/* ── 9. INDUSTRY BREAKDOWN BY PROGRAM ── */
async function loadInstIndustry(programCode = 'all') {
    const canvas = document.getElementById('instIndustryChartCanvas');
    const filterSelect = document.getElementById('instProgramFilter');
    if (!canvas) return;

    try {
        // 1. Populate the dropdown with available programs (only once)
        if (filterSelect && filterSelect.options.length <= 1) {
            try {
                const programs = await apiFetch(`${INST_BASE}/${currentFormId}/programs`);
                
                // If the backend returns an array of strings, loop through it
                if (programs && programs.length > 0) {
                    programs.forEach(p => {
                        const opt = document.createElement('option');
                        opt.value = p;
                        opt.textContent = p;
                        filterSelect.appendChild(opt);
                    });
                }
            } catch (err) {
                console.error("Failed to load program list. Error:", err);
            }
        }

        // 2. Fetch the data
        const data = await apiFetch(`${INST_BASE}/${currentFormId}/industry-by-program?program=${programCode}&alumni_type=${instType}`);

        if (!data || data.length === 0) {
            canvas.parentNode.innerHTML = `<div style="padding:2rem;text-align:center;color:#9CA3AF;">No industry data available for this program.</div>`;
            return;
        }

                // 3. Draw the Horizontal Bar Chart
        if (instCharts.industry) instCharts.industry.destroy();
        const ctx = canvas.getContext('2d');
        
        // Define a vibrant color palette for the different industries
        const colors = [
            '#32418C', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', 
            '#06B6D4', '#EC4899', '#F97316', '#84CC16', '#14B8A6'
        ];

        // Calculate total for the percentage tooltips
        const totalCount = data.reduce((sum, item) => sum + item.count, 0);

        instCharts.industry = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(r => r.label),
                datasets: [{
                    label: 'Number of Alumni',
                    data: data.map(r => r.count),
                    // THIS IS THE FIX: Map a different color to each bar
                    backgroundColor: data.map((_, i) => colors[i % colors.length]), 
                    borderRadius: 4, 
                    barPercentage: 0.7 
                }]
            },
            options: {
                indexAxis: 'y', // Keep it horizontal
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }, 
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const value = ctx.raw;
                                const percentage = ((value / totalCount) * 100).toFixed(1);
                                return ` ${value} alumni (${percentage}%)`;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { precision: 0 }, // Whole numbers only
                        grid: { color: '#F1F5F9' } 
                    },
                    y: {
                        grid: { display: false }, 
                        ticks: {
                            font: { family: 'Poppins', size: 11, weight: '600' },
                            color: '#1E293B'
                        }
                    }
                }
            }
        });

    } catch (e) {
        console.error('Failed to load industry data:', e);
        canvas.parentNode.innerHTML = `<div style="padding:2rem;color:#EF4444;">Failed to load industry data.</div>`;
    }
}

/* ═══════════════════════════════════════════════════════════
   EXPORTS
   ═══════════════════════════════════════════════════════════ */

function exportInstitutional(format) {
    if (format === 'csv') {
        exportInstitutionalCSV();
    } else if (format === 'pdf') {
        exportInstitutionalPDF();
    }
}

/* ── 1. CSV EXPORT ── */
function exportInstitutionalCSV() {
    const d = currentInstData;
    if (!d.kpis || !d.careerMatrix) {
        showToast('Data is still loading. Please wait.', 'warning');
        return;
    }

    let csv = 'NU LIPA — INSTITUTIONAL TRACER REPORT\n';
    csv += `Generated,${new Date().toLocaleString()}\n`;
    csv += `Filter Applied,${instType === 'college' ? 'College Only' : 'All Alumni'}\n\n`;

    // KPIs
    csv += 'EXECUTIVE SUMMARY\n';
    csv += 'Metric,Value\n';
    csv += `Total Alumni,${d.kpis.totalAlumni}\n`;
    csv += `Employed Alumni,${d.kpis.employedCount}\n`;
    csv += `Employment Rate,${d.kpis.employmentRate}%\n`;
    csv += `Direct Alignment Rate,${d.kpis.alignmentRate}%\n`;
    csv += `Programs Tracked,${d.kpis.programsTracked}\n\n`;

    // Career Matrix
    csv += 'COLLEGE → CAREER ALIGNMENT BY PROGRAM\n';
    csv += 'Program,Directly Related,Somewhat Related,Not Related,Undetermined,Total\n';
    d.careerMatrix.rows.forEach(r => {
        csv += `${r.program},${r.directly_related},${r.somewhat_related},${r.not_related},${r.not_applicable},${r.total}\n`;
    });
    csv += '\n';

    // Employment
    if (d.employment) {
        csv += 'EMPLOYMENT COVERAGE BY PROGRAM\n';
        csv += 'Program,Employed,Total,Rate %\n';
        d.employment.forEach(r => {
            csv += `${r.program},${r.employed},${r.total},${r.rate}\n`;
        });
        csv += '\n';
    }

    // SHS Matrix
    if (d.shsMatrix && d.shsMatrix.rows.length > 0) {
        csv += 'SHS STRAND → COLLEGE PROGRAM ALIGNMENT\n';
        csv += 'SHS Strand,Directly Related,Somewhat Related,Not Related,Undetermined,Total\n';
        d.shsMatrix.rows.forEach(r => {
            csv += `${r.program},${r.directly_related},${r.somewhat_related},${r.not_related},${r.not_applicable},${r.total}\n`;
        });
    }

    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `Institutional_Analytics_${new Date().toISOString().slice(0, 10)}.csv`;
    a.click();
    window.URL.revokeObjectURL(url);
    showToast('CSV exported successfully', 'success');
}

/* ── 2. PDF EXPORT ── */
async function exportInstitutionalPDF() {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        showToast('PDF library not loaded. Please refresh.', 'error');
        return;
    }

    const d = currentInstData;
    if (!d.kpis || !d.careerMatrix) {
        showToast('Data is still loading. Please wait.', 'warning');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    const NU_NAVY = [50, 65, 140];
    const NU_GOLD = [251, 209, 23];
    const pageWidth = 210, pageHeight = 297, marginX = 12;

    // --- HEADER ---
    doc.setFillColor(...NU_NAVY);
    doc.rect(0, 0, pageWidth, 26, 'F');
    doc.setFillColor(...NU_GOLD);
    doc.rect(0, 26, pageWidth, 1.5, 'F');
    
    doc.setTextColor(255, 255, 255);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(15);
    doc.text('Institutional Analytics Report', marginX, 12);
    
    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);
    doc.text('LumiNUs Alumni Portal  •  National University Lipa', marginX, 18.5);
    doc.setFontSize(8.5);
    doc.text('Generated: ' + new Date().toLocaleString(), pageWidth - marginX, 18.5, { align: 'right' });

    let y = 36;

    // --- KPIs ---
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(11);
    doc.text('EXECUTIVE SUMMARY', marginX + 4, y);
    y += 6;

    doc.autoTable({
        body: [
            ['Total Alumni Analyzed', d.kpis.totalAlumni],
            ['Employed Alumni', d.kpis.employedCount],
            ['Employment Rate', d.kpis.employmentRate + '%'],
            ['Direct Alignment Rate', d.kpis.alignmentRate + '%'],
            ['Programs Tracked', d.kpis.programsTracked]
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2 },
        columnStyles: {
            0: { cellWidth: 90, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 10;

    // --- CAREER MATRIX ---
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('DEGREE–CAREER ALIGNMENT BY PROGRAM', marginX + 4, y);
    y += 5;

    const matrixBody = d.careerMatrix.rows.map(r => [
        r.program, 
        r.directly_related, 
        r.somewhat_related, 
        r.not_related, 
        r.not_applicable, 
        r.total
    ]);

    doc.autoTable({
        head: [['Program', 'Direct', 'Related', 'Not Direct', 'Undetermined', 'Total']],
        body: matrixBody,
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8.5, cellPadding: 2 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 45 },
            1: { cellWidth: 22, halign: 'center', fontStyle: 'bold', textColor: [5, 120, 80] },
            2: { cellWidth: 22, halign: 'center' },
            3: { cellWidth: 22, halign: 'center' },
            4: { cellWidth: 25, halign: 'center' },
            5: { cellWidth: 20, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 10;

    // --- SHS MATRIX (If applicable) ---
    if (d.shsMatrix && d.shsMatrix.rows.length > 0) {
        if (y > pageHeight - 60) { doc.addPage(); y = 20; }
        
        doc.setFillColor(...NU_GOLD);
        doc.rect(marginX, y - 3.5, 2, 5, 'F');
        doc.setTextColor(...NU_NAVY);
        doc.setFontSize(11);
        doc.text('SHS STRAND → COLLEGE PROGRAM ALIGNMENT', marginX + 4, y);
        y += 5;

        const shsBody = d.shsMatrix.rows.map(r => [
            r.program, 
            r.directly_related, 
            r.somewhat_related, 
            r.not_related, 
            r.not_applicable, 
            r.total
        ]);

        doc.autoTable({
            head: [['SHS Strand', 'Direct', 'Related', 'Not Direct', 'Undetermined', 'Total']],
            body: shsBody,
            startY: y,
            theme: 'grid',
            styles: { font: 'helvetica', fontSize: 8.5, cellPadding: 2 },
            headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
            columnStyles: {
                0: { cellWidth: 45 },
                1: { cellWidth: 22, halign: 'center', fontStyle: 'bold', textColor: [5, 120, 80] },
                2: { cellWidth: 22, halign: 'center' },
                3: { cellWidth: 22, halign: 'center' },
                4: { cellWidth: 25, halign: 'center' },
                5: { cellWidth: 20, halign: 'center', fontStyle: 'bold' },
            },
            alternateRowStyles: { fillColor: [248, 249, 253] },
            margin: { left: marginX, right: marginX },
        });
    }

    doc.save(`Institutional_Analytics_${new Date().toISOString().slice(0, 10)}.pdf`);
    showToast('PDF exported successfully', 'success');
}


// This is alumni_tracer_institutional.js, which handles the frontend logic for the Institutional Analytics section of the alumni tracer dashboard. It includes functions for switching tabs, setting institutional types, refreshing data, loading KPIs, career matrices, employment coverage, SHS continuation rates, and exporting data to CSV or PDF formats.