<div class="tracer-panel" id="institutional-panel">

    <!-- ── TOP HEADER ── -->
    <div class="inst-header">
        <div>
            <h2>Institutional Analytics</h2>
            <p>Graduate employability tracking & curriculum effectiveness insights</p>
        </div>
        <div class="inst-actions">
            <button class="btn-inst" onclick="exportInstitutional('csv')">
                <i class="fa-solid fa-file-csv" style="color: #10B981;"></i> Export CSV
            </button>
            <button class="btn-inst" onclick="exportInstitutional('pdf')">
                <i class="fa-solid fa-file-pdf" style="color: #EF4444;"></i> Export PDF
            </button>
            <button class="btn-inst primary" onclick="refreshInstitutional()">
                <i class="fa-solid fa-rotate"></i> Refresh Data
            </button>
        </div>
    </div>

    <!-- ── CONTROLS ── -->
    <div class="inst-controls">
        <div class="inst-tabs">
            <button class="inst-tab active" onclick="switchInstTab('overview')">
                <i class="fa-solid fa-chart-pie"></i> Overview
            </button>
            <button class="inst-tab" onclick="switchInstTab('career')">
                <i class="fa-solid fa-briefcase"></i> College → Career
            </button>
            <button class="inst-tab" onclick="switchInstTab('shs')">
                <i class="fa-solid fa-school"></i> SHS → College
            </button>
        </div>
        <div class="inst-filters">
            <button class="filter-btn active" data-inst-type="all" onclick="setInstitutionalType('all')">All Alumni</button>
            <button class="filter-btn" data-inst-type="college" onclick="setInstitutionalType('college')">College Only</button>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- VIEW 1: OVERVIEW                             -->
    <!-- ============================================ -->
    <div id="inst-view-overview" class="inst-view-content active">
        
        <!-- KPI Grid -->
        <div class="inst-kpi-grid">
            <!-- Direct Alignment -->
            <div class="inst-kpi-card blue">
                <div class="inst-kpi-top">
                    <div class="inst-kpi-label">Direct Alignment</div>
                    <div class="inst-kpi-icon"><i class="fa-solid fa-bullseye"></i></div>
                </div>
                <div class="inst-kpi-value" id="instAlignmentRate">--</div>
                <div class="inst-kpi-sub" id="instAlignmentSub">--</div>
            </div>
            <!-- Employment -->
            <div class="inst-kpi-card green">
                <div class="inst-kpi-top">
                    <div class="inst-kpi-label">Employment Rate</div>
                    <div class="inst-kpi-icon"><i class="fa-solid fa-briefcase"></i></div>
                </div>
                <div class="inst-kpi-value" id="instEmploymentRate">--</div>
                <div class="inst-kpi-sub" id="instEmploymentSub">--</div>
            </div>
            <!-- Coverage -->
            <div class="inst-kpi-card amber">
                <div class="inst-kpi-top">
                    <div class="inst-kpi-label">Response Coverage</div>
                    <div class="inst-kpi-icon"><i class="fa-solid fa-chart-pie"></i></div>
                </div>
                <div class="inst-kpi-value" id="instCoverage">--</div>
                <div class="inst-kpi-sub" id="instCoverageSub">--</div>
            </div>
            <!-- Programs -->
            <div class="inst-kpi-card purple">
                <div class="inst-kpi-top">
                    <div class="inst-kpi-label">Programs Tracked</div>
                    <div class="inst-kpi-icon"><i class="fa-solid fa-graduation-cap"></i></div>
                </div>
                <div class="inst-kpi-value" id="instPrograms">--</div>
                <div class="inst-kpi-sub">Distinct college programs</div>
            </div>
        </div>

        <!-- Data Quality -->
        <div class="inst-card">
            <div class="inst-card-header">
                <h3><i class="fa-solid fa-shield-halved"></i> Data Coverage & Quality</h3>
            </div>
            <div class="inst-card-body">
                <div class="inst-quality-grid" id="instQualityGrid">
                    <div style="grid-column: span 4; text-align: center; padding: 2rem;">
                        <div class="loading-spinner"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ============================================ -->
    <!-- VIEW 2: COLLEGE → CAREER                     -->
    <!-- ============================================ -->
    <div id="inst-view-career" class="inst-view-content" style="display:none;">
        
        <div class="grid-2">
            <!-- Matrix -->
            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <div>
                        <h3><i class="fa-solid fa-chart-bar"></i> Degree–Career Alignment by Program</h3>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Compare how graduates' current occupations relate to each college program</p>
                    </div>
                </div>
                <div class="matrix-container" id="instMatrixWrapper">
                    <div class="chart-container" id="instAlignmentChartContainer" style="height: 420px; padding: 1rem 1.5rem;">
                        <canvas id="instAlignmentChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- NEW: Industry Breakdown Chart -->
            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <div>
                        <h3><i class="fa-solid fa-building"></i> Employment by Industry</h3>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Compare the number of employed alumni across all registered industries, or filter by program</p>
                    </div>
                </div>
                
                <!-- Moved dropdown to its own row, above the chart -->
                <div style="padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: flex-end; align-items: center; gap: 0.75rem; background: #F8FAFC;">
                    <label for="instProgramFilter" style="font-size: 0.8125rem; font-weight: 600; color: var(--text-muted);">Filter by Program:</label>
                    <select id="instProgramFilter" class="form-select" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #E2E8F0; font-size: 0.8125rem; color: #1E293B; font-weight: 600; min-width: 200px; cursor: pointer; background: #fff;" onchange="loadInstIndustry(this.value)">
                        <option value="all">All Programs</option>
                    </select>
                </div>

                <div class="inst-card-body">
                    <div class="chart-container" style="height: 350px; position: relative;">
                        <canvas id="instIndustryChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Employment Chart -->
            <div class="inst-card">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-chart-bar"></i> Employment Coverage</h3>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container">
                        <canvas id="instEmploymentChartCanvas"></canvas>
                    </div>
                </div>
            </div>
            
            <!-- Trend Chart -->
            <div class="inst-card">
                <div class="inst-card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="fa-solid fa-chart-line"></i> Trend by Graduation Year</h3>
                    <select id="instBatchFilter" class="form-select" style="padding: 0.25rem 0.5rem; border-radius: 0.375rem; border: 1px solid #E2E8F0; font-size: 0.75rem; color: #64748B;" onchange="filterInstTrendByBatch(this.value)">
                        <option value="all">All Batches</option>
                    </select>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container">
                        <canvas id="instTrendChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Scatter Plot (Full Width) -->
            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:1rem;">
                        <div>
                            <h3><i class="fa-solid fa-chart-scatter"></i> Curriculum Effectiveness & Alignment Quadrant</h3>
                            <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Compare all programs or inspect one program's checkpoint indicators</p>
                        </div>
                        <select id="instCheckpointProgramFilter" class="form-select" onchange="filterInstCheckpointProgram(this.value)" style="padding:0.35rem 0.6rem; border-radius:0.375rem; border:1px solid #E2E8F0; font-size:0.75rem; color:#64748B;">
                            <option value="all">All Programs</option>
                        </select>
                    </div>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container" style="height: 350px;">
                        <canvas id="instScatterChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Preparedness & Influence -->
            <div class="inst-card">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-brain"></i> Preparedness & Influence</h3>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container">
                        <canvas id="instPreparednessChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Sentiment (Would Choose Again) -->
            <div class="inst-card">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-face-smile"></i> Would Choose Program Again</h3>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container">
                        <canvas id="instSentimentChartCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Checkpoint-derived curriculum action signals -->
            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-list-check"></i> Curriculum Development Priorities</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Programs flagged by graduate preparedness, useful-skills, satisfaction, and career-alignment evidence</p>
                </div>
                <div class="inst-card-body" id="instCurriculumPriorities">
                    <div style="text-align:center; padding:2rem;"><div class="loading-spinner"></div></div>
                </div>
            </div>

            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-route"></i> Graduate Pathway Signals</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Perceived curriculum relevance, further-study plans, and reported employment status</p>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="instCollegePathwayCanvas"></canvas>
                    </div>
                </div>
            </div>

            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-file-lines"></i> College Improvement Narrative</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Rule-based interpretation of current College checkpoint and employability evidence</p>
                </div>
                <div class="inst-card-body" id="instCollegeNarrative">
                    <div style="text-align:center; padding:2rem;"><div class="loading-spinner"></div></div>
                </div>
            </div>
        </div> <!-- ✅ PROPERLY CLOSE grid-2 -->
    </div> <!-- ✅ PROPERLY CLOSE inst-view-career -->

    <!-- ============================================ -->
    <!-- VIEW 3: SHS → COLLEGE                        -->
    <!-- ============================================ -->
    <div id="inst-view-shs" class="inst-view-content" style="display:none;">
        
        <div class="grid-2">
            <!-- Continuation Rate -->
            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-chart-bar"></i> SHS to NU Lipa College Continuation</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Number and percentage of SHS alumni who continued into a NU Lipa college program</p>
                </div>
                <div class="inst-card-body">
                    <div class="inst-shs-summary" id="instShsContinuationSummary"></div>
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="instShsContinuationCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- NU Lipa college lifecycle -->
            <div class="inst-card">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-route"></i> SHS Alumni College Status</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Current status of SHS alumni who entered NU Lipa college</p>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="instShsStatusCanvas"></canvas>
                    </div>
                </div>
            </div>

            <!-- Top Programs -->
            <div class="inst-card">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-list-ol"></i> Top College Programs</h3>
                </div>
                <div class="inst-card-body" id="instShsTopPrograms">
                    <div style="text-align: center; padding: 2rem;"><div class="loading-spinner"></div></div>
                </div>
            </div>

            <!-- SHS Matrix -->
            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <div>
                        <h3><i class="fa-solid fa-table-cells"></i> SHS Strand Alignment</h3>
                        <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.25rem;">Strand feeding into college programs</p>
                    </div>
                </div>
                <div class="matrix-container" id="instShsMatrixWrapper">
                    <div style="padding: 3rem; text-align: center;"><div class="loading-spinner"></div></div>
                </div>
            </div>

            <!-- SHS checkpoint learning outcomes -->
            <div class="inst-card">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-compass"></i> SHS Learning Outcomes</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Self-reported preparation for college and next-step decisions (1–5)</p>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="instShsOutcomesCanvas"></canvas>
                    </div>
                </div>
            </div>

            <div class="inst-card">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-route"></i> SHS Primary Activities</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">What SHS alumni do after graduation</p>
                </div>
                <div class="inst-card-body">
                    <div class="chart-container" style="height: 320px;">
                        <canvas id="instShsActivityCanvas"></canvas>
                    </div>
                </div>
            </div>

            <div class="inst-card grid-full">
                <div class="inst-card-header">
                    <h3><i class="fa-solid fa-file-lines"></i> SHS Improvement Narrative</h3>
                    <p style="font-size:0.75rem; color:var(--text-muted); margin-top:0.25rem;">Rule-based interpretation of SHS transition and readiness evidence</p>
                </div>
                <div class="inst-card-body" id="instShsNarrative">
                    <div style="text-align:center; padding:2rem;"><div class="loading-spinner"></div></div>
                </div>
            </div>
        </div> <!-- ✅ PROPERLY CLOSE grid-2 -->
    </div> <!-- ✅ PROPERLY CLOSE inst-view-shs -->

</div> <!-- ✅ PROPERLY CLOSE institutional-panel -->

<!-- DRILLDOWN MODAL -->
<div class="modal-overlay" id="instDrilldownModal">
    <div class="modal-dialog" style="max-width: 600px;">
        <div class="modal-header">
            <h3 id="instDrilldownTitle">Alumni</h3>
            <button class="modal-close" onclick="closeInstDrilldown()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body" id="instDrilldownBody" style="padding: 0;"></div>
    </div>
</div>

<script>
    (function() {
        const init = () => {
            const instPanel = document.getElementById('institutional-panel');
            if (!instPanel) return;

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        if (instPanel.classList.contains('active')) {
                            triggerLoad();
                        }
                    }
                });
            });

            observer.observe(instPanel, { attributes: true });

            function triggerLoad() {
                const tryLoad = setInterval(() => {
                    if (window.currentFormId) {
                        clearInterval(tryLoad);
                        if (typeof refreshInstitutional === 'function') {
                            refreshInstitutional();
                        }
                    }
                }, 300);
            }

            if (instPanel.classList.contains('active')) {
                triggerLoad();
            }
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
</script>