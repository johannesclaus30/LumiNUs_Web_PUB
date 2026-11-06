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
                <button class="quick-action-btn-sm" onclick="openReminderModal()">
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
            <!-- 🆕 Preview Tab -->
            <button class="tracer-tab" data-tab="preview">
                <i class="fa-solid fa-eye"></i>
                <span>Preview</span>
            </button>

            <button class="tracer-tab" data-tab="institutional">
                <i class="fa-solid fa-chart-column"></i>
                <span>Institutional Analytics</span>
            </button>
        </div>
    </div>
</header>

<script>
    // ============================================
    // HEADER - TAB SWITCHING (UPDATED)
    // ============================================
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.tracer-tab').forEach(tab => {
            tab.addEventListener('click', async function() {
                document.querySelectorAll('.tracer-tab').forEach(t => t.classList.remove('active'));
                document.querySelectorAll('.tracer-panel').forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                document.getElementById(this.dataset.tab + '-panel').classList.add('active');

                const tabName = this.dataset.tab;
                if (tabName === 'builder') renderBuilder();
                if (tabName === 'responses') await loadResponses();
                if (tabName === 'dashboard') await loadDashboardData();
                if (tabName === 'analytics') {
                    await loadAnalyticsKPIs();
                    renderAnalyticsChartsGrid();
                    updateChartQuestionDropdown();
                }
                if (tabName === 'preview') {
                    renderPreview();
                }
                if (tabName === 'institutional') {
                    initializeInstitutional();
                }
            });
        });
    });

    // ============================================
    // MOBILE MENU
    // ============================================
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
</script>

<!-- ============================================ -->
<!-- PREVIEW TAB STYLES -->
<!-- ============================================ -->
<style>
/* ========================================
   PREVIEW TAB STYLES
   ======================================== */

.preview-container {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

/* Preview Header */
.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.5rem;
    background: #FFFFFF;
    border-radius: 1rem;
    border: 1px solid #E5E7EB;
    box-shadow: var(--shadow-sm);
    flex-wrap: wrap;
    gap: 1rem;
}

.preview-header-info h2 {
    font-size: 1.125rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.preview-header-info p {
    font-size: 0.8125rem;
    color: #6B7280;
    margin: 0.125rem 0 0 0;
}

.preview-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

/* Alumni Type Toggle */
.preview-toggle-group {
    display: flex;
    background: #F3F4F6;
    border-radius: 0.75rem;
    padding: 0.25rem;
    gap: 0.25rem;
}

.preview-toggle-btn {
    padding: 0.5rem 1rem;
    border: none;
    border-radius: 0.5rem;
    font-family: 'Poppins', sans-serif;
    font-size: 0.8125rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    background: transparent;
    color: #6B7280;
    display: flex;
    align-items: center;
    gap: 0.375rem;
}

.preview-toggle-btn:hover {
    color: #374151;
}

.preview-toggle-btn.active {
    background: #FFFFFF;
    color: #32418C;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.preview-toggle-btn i {
    font-size: 0.875rem;
}

/* Preview Badge */
.preview-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.375rem 0.75rem;
    background: #FEF3C7;
    color: #92400E;
    border-radius: 9999px;
    font-size: 0.6875rem;
    font-weight: 600;
}

.preview-badge i {
    font-size: 0.6875rem;
}

/* Preview Content - Alumni View Simulation */
.preview-content {
    background: #F9FAFB;
    border-radius: 1rem;
    padding: 2rem;
    min-height: 400px;
    border: 2px dashed #D1D5DB;
}

.preview-content .preview-empty {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 4rem 2rem;
    color: #9CA3AF;
}

.preview-content .preview-empty i {
    font-size: 3.5rem;
    margin-bottom: 1rem;
    color: #D1D5DB;
}

.preview-content .preview-empty h3 {
    font-size: 1.125rem;
    font-weight: 600;
    color: #6B7280;
    margin-bottom: 0.5rem;
}

/* Preview Phase Card */
.preview-phase-card {
    background: #FFFFFF;
    border-radius: 1rem;
    border: 1px solid #E5E7EB;
    box-shadow: var(--shadow-sm);
    margin-bottom: 1.25rem;
    overflow: hidden;
}

.preview-phase-card:last-child {
    margin-bottom: 0;
}

.preview-phase-header {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #F3F4F6;
}

.preview-phase-icon {
    width: 44px;
    height: 44px;
    border-radius: 0.875rem;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
    flex-shrink: 0;
    color: #FFFFFF;
}

.preview-phase-info {
    flex: 1;
}

.preview-phase-info h3 {
    font-size: 1rem;
    font-weight: 700;
    color: #1F2937;
    margin: 0;
}

.preview-phase-info p {
    font-size: 0.8125rem;
    color: #6B7280;
    margin: 0.125rem 0 0 0;
}

.preview-phase-progress {
    font-size: 0.75rem;
    font-weight: 600;
    color: #9CA3AF;
}

/* Preview Section */
.preview-section {
    padding: 0 1.5rem 1.25rem;
}

.preview-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 0 0.5rem;
    font-size: 0.9375rem;
    font-weight: 600;
    color: #1F2937;
    border-bottom: 2px solid #F3F4F6;
    margin-bottom: 0.75rem;
}

.preview-section-title .section-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 0.375rem;
    font-size: 0.625rem;
    font-weight: 700;
    color: #FFFFFF;
    flex-shrink: 0;
}

.preview-section-desc {
    font-size: 0.8125rem;
    color: #6B7280;
    margin: -0.25rem 0 0.75rem 0;
}

/* Preview Question */
.preview-question {
    padding: 0.875rem 0;
    border-bottom: 1px solid #F9FAFB;
}

.preview-question:last-child {
    border-bottom: none;
}

.preview-question-label {
    display: flex;
    align-items: flex-start;
    gap: 0.5rem;
    font-size: 0.875rem;
    font-weight: 500;
    color: #374151;
}

.preview-question-label .required-star {
    color: #EF4444;
    font-weight: 700;
}

.preview-question-type {
    font-size: 0.625rem;
    font-weight: 600;
    color: #9CA3AF;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 0.1875rem;
}

/* Preview Input Simulation */
.preview-input {
    margin-top: 0.5rem;
    padding: 0.5rem 0.75rem;
    background: #F9FAFB;
    border: 2px solid #E5E7EB;
    border-radius: 0.625rem;
    color: #9CA3AF;
    font-size: 0.8125rem;
    font-family: 'Poppins', sans-serif;
    min-height: 38px;
    transition: all 0.2s ease;
}

.preview-input:hover {
    border-color: #D1D5DB;
}

.preview-input.textarea {
    min-height: 80px;
}

.preview-input.file-upload {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #6B7280;
    border-style: dashed;
    justify-content: center;
    padding: 1rem;
}

.preview-input.file-upload i {
    font-size: 1.25rem;
    color: #9CA3AF;
}

/* Preview Options (for multiple choice, checkboxes, dropdown) */
.preview-options {
    display: flex;
    flex-direction: column;
    gap: 0.375rem;
    margin-top: 0.5rem;
}

.preview-option {
    display: flex;
    align-items: center;
    gap: 0.625rem;
    padding: 0.375rem 0.75rem;
    background: #F9FAFB;
    border-radius: 0.5rem;
    font-size: 0.8125rem;
    color: #6B7280;
}

.preview-option .radio-circle {
    width: 18px;
    height: 18px;
    border: 2px solid #D1D5DB;
    border-radius: 50%;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.preview-option .radio-circle.filled {
    border-color: #32418C;
}

.preview-option .radio-circle.filled::after {
    content: '';
    width: 8px;
    height: 8px;
    background: #32418C;
    border-radius: 50%;
}

.preview-option .checkbox-square {
    width: 18px;
    height: 18px;
    border: 2px solid #D1D5DB;
    border-radius: 0.25rem;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}

.preview-option .checkbox-square.checked {
    background: #32418C;
    border-color: #32418C;
    color: #FFFFFF;
}

.preview-option .checkbox-square.checked::after {
    content: '✓';
    font-size: 0.625rem;
    font-weight: 700;
}

/* Preview Grid (Likert Scale / Multiple Choice Grid) */
.preview-grid {
    margin-top: 0.75rem;
    overflow-x: auto;
}

.preview-grid table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.8125rem;
}

.preview-grid table th,
.preview-grid table td {
    padding: 0.5rem 0.75rem;
    text-align: center;
    border: 1px solid #E5E7EB;
}

.preview-grid table th {
    background: #F9FAFB;
    font-weight: 600;
    color: #4B5563;
    font-size: 0.6875rem;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.preview-grid table td:first-child {
    text-align: left;
    font-weight: 500;
    color: #374151;
    background: #FAFAFA;
}

.preview-grid .grid-radio {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 20px;
    height: 20px;
    border: 2px solid #D1D5DB;
    border-radius: 50%;
    transition: all 0.2s ease;
}

.preview-grid .grid-radio.filled {
    border-color: #32418C;
}

.preview-grid .grid-radio.filled::after {
    content: '';
    width: 8px;
    height: 8px;
    background: #32418C;
    border-radius: 50%;
}

/* Preview Footer Actions */
.preview-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.75rem;
    padding: 1.25rem 1.5rem;
    border-top: 1px solid #E5E7EB;
    background: #FFFFFF;
    border-radius: 0 0 1rem 1rem;
}

.preview-actions .btn-preview {
    padding: 0.625rem 1.5rem;
    border: none;
    border-radius: 0.75rem;
    font-family: 'Poppins', sans-serif;
    font-weight: 600;
    font-size: 0.875rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.preview-actions .btn-preview.primary {
    background: #32418C;
    color: #FFFFFF;
}

.preview-actions .btn-preview.primary:hover {
    background: #253069;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(50, 65, 140, 0.2);
}

.preview-actions .btn-preview.secondary {
    background: #F3F4F6;
    color: #4B5563;
}

.preview-actions .btn-preview.secondary:hover {
    background: #E5E7EB;
}

/* Responsive Preview */
@media (max-width: 768px) {
    .preview-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .preview-controls {
        flex-direction: column;
        align-items: stretch;
    }
    
    .preview-toggle-group {
        width: 100%;
    }
    
    .preview-toggle-btn {
        flex: 1;
        justify-content: center;
    }
    
    .preview-content {
        padding: 1rem;
    }
    
    .preview-phase-header {
        flex-wrap: wrap;
    }
    
    .preview-grid table {
        font-size: 0.6875rem;
    }
    
    .preview-grid table th,
    .preview-grid table td {
        padding: 0.375rem 0.5rem;
    }
}

@media (max-width: 480px) {
    .preview-phase-header {
        flex-direction: column;
        align-items: flex-start;
        text-align: left;
    }
    
    .preview-phase-progress {
        align-self: flex-start;
    }
}

/* ========================================
   PREVIEW TAB STYLES (UPDATED WITH COLLAPSIBLE)
   ======================================== */

/* ... existing preview styles ... */

/* Collapse All Button */
.preview-collapse-all-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.375rem;
    padding: 0.5rem 0.875rem;
    border: 2px solid #E5E7EB;
    border-radius: 0.625rem;
    background: #FFFFFF;
    color: #4B5563;
    font-family: 'Poppins', sans-serif;
    font-size: 0.75rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.preview-collapse-all-btn:hover {
    border-color: #32418C;
    color: #32418C;
    background: #F9FAFB;
}

.preview-collapse-all-btn i {
    font-size: 0.8125rem;
}

/* Phase Header - Clickable */
.preview-phase-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1.25rem 1.5rem;
    border-bottom: 1px solid #F3F4F6;
    cursor: pointer;
    transition: background 0.15s ease;
    user-select: none;
}

.preview-phase-header:hover {
    background: #FAFAFA;
}

.preview-phase-header-left {
    display: flex;
    align-items: center;
    gap: 0.875rem;
}

.preview-phase-controls {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.preview-phase-toggle-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 0.5rem;
    background: #F3F4F6;
    color: #6B7280;
    transition: all 0.2s ease;
}

.preview-phase-toggle-icon:hover {
    background: #E5E7EB;
    color: #374151;
}

.preview-phase-toggle-icon i {
    font-size: 0.75rem;
    transition: transform 0.25s ease;
}

/* Phase Body */
.preview-phase-body {
    padding: 0;
}

/* Section Card */
.preview-section-card {
    border-bottom: 1px solid #F3F4F6;
    background: #FFFFFF;
}

.preview-section-card:last-child {
    border-bottom: none;
}

/* Section Header - Clickable */
.preview-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.875rem 1.5rem;
    cursor: pointer;
    transition: background 0.15s ease;
    user-select: none;
    background: #FAFAFA;
}

.preview-section-header:hover {
    background: #F3F4F6;
}

.preview-section-header-left {
    display: flex;
    align-items: center;
    gap: 0.625rem;
}

.preview-section-header-left .section-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    border-radius: 0.5rem;
    font-size: 0.625rem;
    font-weight: 700;
    color: #FFFFFF;
    flex-shrink: 0;
}

.preview-section-header-left .section-title {
    font-size: 0.875rem;
    font-weight: 600;
    color: #1F2937;
}

.preview-section-header-left .section-question-count {
    font-size: 0.6875rem;
    color: #9CA3AF;
    font-weight: 500;
    background: #F3F4F6;
    padding: 0.125rem 0.5rem;
    border-radius: 9999px;
}

.preview-section-header-right .preview-section-toggle-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 0.375rem;
    color: #9CA3AF;
    transition: all 0.2s ease;
}

.preview-section-header-right .preview-section-toggle-icon i {
    font-size: 0.6875rem;
    transition: transform 0.25s ease;
}

/* Section Body - Collapsible */
.preview-section-body {
    padding: 0 1.5rem 1.25rem;
    overflow: hidden;
    transition: all 0.3s ease;
}

.preview-section-body.collapsed {
    display: none;
}

.preview-section-desc {
    font-size: 0.8125rem;
    color: #6B7280;
    margin: 0.75rem 0 0.75rem 0;
}

/* Responsive */
@media (max-width: 768px) {
    .preview-phase-header {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .preview-phase-controls {
        width: 100%;
        justify-content: space-between;
    }
    
    .preview-section-header {
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .preview-section-header-left {
        flex-wrap: wrap;
    }
    
    .preview-collapse-all-btn span {
        display: none;
    }
    
    .preview-collapse-all-btn {
        padding: 0.5rem 0.75rem;
    }
}

@media (max-width: 480px) {
    .preview-phase-header-left {
        flex-wrap: wrap;
    }
    
    .preview-section-header-left .section-question-count {
        display: none;
    }
}

</style>