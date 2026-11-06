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
        likert_scale: 'Matrix with statements (rows) and a fixed scale (columns). Default scale: 1-5.',
        multiple_choice_grid: 'Matrix with statements (rows) and customizable option columns. Like Likert but you define the column labels.'
    };

    const typeLabels = {
        short_answer: 'Short Answer',
        paragraph: 'Paragraph',
        multiple_choice: 'Multiple Choice',
        checkboxes: 'Checkboxes',
        dropdown: 'Dropdown',
        likert_scale: 'Likert Scale',
        multiple_choice_grid: 'Multiple Choice Grid'
    };

    const typeIcons = {
        short_answer: 'fa-i-cursor',
        paragraph: 'fa-align-left',
        multiple_choice: 'fa-circle-dot',
        checkboxes: 'fa-check-square',
        dropdown: 'fa-chevron-down',
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

    // ═══════════════════════════════════════
    // GLOBAL STATE VARIABLES
    // ═══════════════════════════════════════

    let phases = [];
    window.currentFormId = null;
    let currentFormId = window.currentFormId;
    let selectedPhaseId = null;
    let expandedSections = new Set();
    let currentEditQuestion = null;
    let currentEditPhase = null;
    let currentEditSection = null;
    let tempOptions = [];
    let tempGridRows = [];
    let tempGridColumns = [];
    let selectedIcon = 'fa-user';
    let selectedColor = '#3b82f6';
    let selectedTargetType = 'all';
    let isLoading = false;

    const API_BASE = '/admin/alumni_tracer';

    // ═══════════════════════════════════════
    // CONDITIONAL LOGIC (BRANCHING) — HANDLERS
    // ═══════════════════════════════════════

    let tempCondition = null;

    /**
     * Toggle the condition editor on/off.
     */
    function handleConditionToggle() {
        const isOn = document.getElementById('qHasCondition').checked;
        document.getElementById('conditionEditor').style.display = isOn ? 'block' : 'none';

        if (isOn) {
            populateConditionParents();
            if (!tempCondition) {
                tempCondition = { dependsOnQuestionId: null, matchType: 'any', optionLabels: [] };
            }
        } else {
            tempCondition = null;
        }
    }

    /**
     * Populate the parent question dropdown with eligible parents
     * (dropdown / multiple_choice only, excluding self).
     */
    function populateConditionParents() {
        const select = document.getElementById('conditionParentQuestion');
        const currentQ = currentEditQuestion?.question;
        const currentSectionId = currentEditQuestion?.secId;
        const phaseId = currentEditQuestion?.phaseId;

        select.innerHTML = '<option value="">-- Select a question --</option>';

        if (!phaseId) return;
        const phase = phases.find(p => Number(p.id) === Number(phaseId));
        if (!phase) return;

        // Cross-section: iterate ALL questions in the phase
        phase.sections.forEach(section => {
            section.questions.forEach(q => {
                // Skip self
                if (currentQ && Number(q.id) === Number(currentQ.id)) return;
                // Only choice-ish parents
                if (!['multiple_choice', 'dropdown'].includes(q.type)) return;
                // Parent must have at least one option defined
                if (!q.options || q.options.length === 0) return;

                const opt = document.createElement('option');
                opt.value = q.id;
                const label = q.label.length > 60 ? q.label.substring(0, 60) + '…' : q.label;
                opt.textContent = `[${section.title}] ${label}`;
                select.appendChild(opt);
            });
        });

        // Restore prior selection if editing
        if (tempCondition?.dependsOnQuestionId) {
            select.value = tempCondition.dependsOnQuestionId;
        }
    }

    /**
     * When the admin picks a parent question, load its options as checkboxes.
     */
    function handleConditionParentChange() {
        const parentId = Number(document.getElementById('conditionParentQuestion').value);
        if (!tempCondition) tempCondition = { dependsOnQuestionId: null, matchType: 'any', optionLabels: [] };
        tempCondition.dependsOnQuestionId = parentId || null;

        const optionsGroup = document.getElementById('conditionOptionsGroup');
        const optionsList = document.getElementById('conditionOptionsList');

        if (!parentId) {
            optionsGroup.style.display = 'none';
            optionsList.innerHTML = '';
            return;
        }

        // Find the parent question
        const phaseId = currentEditQuestion?.phaseId;
        const phase = phases.find(p => Number(p.id) === Number(phaseId));
        if (!phase) return;

        let parentQ = null;
        phase.sections.forEach(s => {
            s.questions.forEach(q => {
                if (Number(q.id) === parentId) parentQ = q;
            });
        });

        if (!parentQ || !parentQ.options || parentQ.options.length === 0) {
            optionsGroup.style.display = 'none';
            return;
        }

        const selected = tempCondition.optionLabels || [];

        optionsList.innerHTML = parentQ.options.map((opt, idx) => {
            const safeId = `cond-opt-${parentId}-${idx}`;
            const safeValue = opt.replace(/"/g, '&quot;');
            return `
                <label class="condition-option-checkbox" for="${safeId}" style="display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; background: #FFFFFF; border: 1px solid #E5E7EB; border-radius: 0.5rem; cursor: pointer; margin-bottom: 0.375rem; font-size: 0.8125rem;">
                    <input type="checkbox" id="${safeId}" value="${safeValue}"
                        ${selected.includes(opt) ? 'checked' : ''}
                        onchange="toggleConditionOption(this.value, this.checked)"
                        style="width: 16px; height: 16px; accent-color: #8b5cf6;">
                    <span>${opt}</span>
                </label>
            `;
        }).join('');

        optionsGroup.style.display = 'block';
    }

/**
 * Toggle an option label in/out of the current condition.
 */
function toggleConditionOption(label, checked) {
    if (!tempCondition) return;
    if (!tempCondition.optionLabels) tempCondition.optionLabels = [];

    if (checked) {
        if (!tempCondition.optionLabels.includes(label)) {
            tempCondition.optionLabels.push(label);
        }
    } else {
        tempCondition.optionLabels = tempCondition.optionLabels.filter(l => l !== label);
    }
}

    // ═══════════════════════════════════════
    // API HELPERS
    // ═══════════════════════════════════════

    async function apiFetch(url, options = {}) {
        try {
            const response = await fetch(url, {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                ...options,
            });

            if (!response.ok) {
                const error = await response.json().catch(() => ({ message: 'Request failed' }));
                throw new Error(error.message || `HTTP ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }

    // ═══════════════════════════════════════
    // PHASE LOADING
    // ═══════════════════════════════════════

    async function loadPhases() {
        if (isLoading) {
            console.log('⚠️ Load already in progress, skipping...');
            return;
        }
        isLoading = true;

        console.log('🔍 Loading phases directly...');
        try {
            phases = [];
            expandedSections = new Set();
            selectedPhaseId = null;
            
            document.getElementById('phasesList').innerHTML = `
                <div class="loading-overlay">
                    <div class="loading-spinner"></div>
                    <p class="loading-text">Loading phases...</p>
                </div>
            `;
            document.getElementById('phaseDetailContent').style.display = 'none';
            document.getElementById('phaseDetailContent').innerHTML = '';
            
            const response = await fetch(`${API_BASE}/phases`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const phasesData = await response.json();
            console.log('📋 Phases data:', phasesData);
            
            phases = phasesData.map(phase => mapPhaseToFrontend(phase));
            console.log('🔄 Mapped phases:', phases);
            
            selectedPhaseId = phases.length > 0 ? phases[0].id : null;

            // 🆕 Auto-generate charts for every question (merges any user-customized ones)
            if (typeof loadAnalyticsCharts === 'function') {
                loadAnalyticsCharts();
            }
            
            try {
                console.log('🔍 Fetching active form ID...');
                const formResponse = await fetch(`${API_BASE}/active-form`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });
                
                if (formResponse.ok) {
                    const formData = await formResponse.json();
                    currentFormId = formData.form_id;
                    window.currentFormId = currentFormId;
                    console.log('📝 Active form ID:', currentFormId);
                } else {
                    throw new Error(`HTTP ${formResponse.status}`);
                }
            } catch (error) {
                console.warn('⚠️ Could not get active form, using fallback:', error);
                currentFormId = 1;
            }

            console.log('🏁 Final currentFormId:', currentFormId);
            
            if (currentFormId) {
                const dashboardPanel = document.getElementById('dashboard-panel');
                if (dashboardPanel && dashboardPanel.classList.contains('active')) {
                    console.log('📊 Dashboard is active, loading data...');
                    await loadDashboardData();
                }
            }

            renderBuilder();
        } catch (error) {
            console.error('❌ Failed to load phases:', error);
            await loadFormsFallback();
        } finally {
            isLoading = false;
        }
    }

    function mapPhaseToFrontend(phase) {
        return {
            id: phase.id,
            title: phase.title,
            subtitle: phase.subtitle || '',
            icon: phase.icon || 'fa-user',
            color: phase.color || '#3b82f6',
            target_type: phase.target_alumni_type || 'all',
            is_archived: phase.is_archived || false,  
            sections: (phase.sections || []).map(section => ({
                id: section.id,
                title: section.title,
                description: section.description || '',
                questions: (section.questions || []).map(q => ({
                    id: q.id,
                    label: q.question_text,
                    type: q.type,
                    placeholder: q.placeholder || '',
                    required: q.is_required,
                    options: q.options 
                        ? q.options.filter(o => !o.is_other).map(o => o.option_label) 
                        : undefined,
                    allowOther: q.allow_other_option || false,
                    condition: q.condition ? {
                        dependsOnQuestionId: q.condition.depends_on_question_id,
                        matchType: q.condition.match_type || 'any',
                        optionLabels: q.condition.option_labels || []
                    } : null,
                    gridRows: q.grid_rows ? q.grid_rows.map(r => r.row_label) : undefined,
                    gridColumns: q.grid_columns ? q.grid_columns.map(c => c.column_label) : undefined,
                }))
            }))
        };
    }

    async function loadFormsFallback() {
        console.log('🔍 Loading forms (fallback)...');
        try {
            const forms = await apiFetch(`${API_BASE}/list`);
            console.log('📋 Forms list:', forms);
            
            if (forms.length > 0) {
                currentFormId = forms[0].id;
                console.log('📝 Selected form ID:', currentFormId);
                
                const form = await apiFetch(`${API_BASE}/${currentFormId}`);
                console.log('📄 Form data:', form);
                
                phases = [];
                expandedSections = new Set();
                
                phases = mapFormToPhases(form);
                console.log('🔄 Mapped phases:', phases);
                
                selectedPhaseId = phases.length > 0 ? phases[0].id : null;

                if (typeof loadAnalyticsCharts === 'function') {
                    loadAnalyticsCharts();
                }

            } else {
                console.warn('⚠️ No forms found!');
                currentFormId = null;
                phases = [];
                selectedPhaseId = null;
                analyticsCharts = [];
                expandedSections = new Set();
            }
            
            renderBuilder();
        } catch (error) {
            console.error('❌ Failed to load forms (fallback):', error);
            currentFormId = null;
            phases = [];
            selectedPhaseId = null;
            analyticsCharts = [];
            expandedSections = new Set();
            renderBuilder();
        }
    }

    function mapFormToPhases(form) {
        if (!form.phases) return [];
        
        return form.phases.map(phase => ({
            id: phase.id,
            title: phase.title,
            subtitle: phase.subtitle || '',
            icon: phase.icon || 'fa-user',
            color: phase.color || '#3b82f6',
            target_type: phase.target_alumni_type || 'all',
            is_archived: phase.is_archived || false, 
            sections: (phase.sections || []).map(section => ({
                id: section.id,
                title: section.title,
                description: section.description || '',
                questions: (section.questions || []).map(q => {
                    const question = {
                        id: q.id,
                        label: q.question_text,
                        type: q.type,
                        placeholder: q.placeholder || '',
                        required: q.is_required,
                    };

                    question.condition = q.condition ? {
                        dependsOnQuestionId: q.condition.depends_on_question_id,
                        matchType: q.condition.match_type || 'any',
                        optionLabels: q.condition.option_labels || []
                    } : null;
                    
                    if (q.options && q.options.length > 0) {
                        question.options = q.options
                            .filter(o => !o.is_other)
                            .map(o => o.option_label);
                    }

                    question.allowOther = q.allow_other_option || false;
                    
                    if (q.grid_rows && q.grid_rows.length > 0) {
                        question.gridRows = q.grid_rows.map(r => r.row_label);
                    }
                    if (q.grid_columns && q.grid_columns.length > 0) {
                        question.gridColumns = q.grid_columns.map(c => c.column_label);
                    }
                    
                    return question;
                })
            }))
        }));
    }

    // ═══════════════════════════════════════
    // SAVE PHASES
    // ═══════════════════════════════════════

async function savePhasesToBackend() {
    showLoading('Saving phases...', 'Please wait while we save your changes to the database');
    
    const payload = {
        phases: phases.map((phase, index) => ({
            id: (phase.id && typeof phase.id === 'number' && phase.id > 0) ? phase.id : null,
            title: phase.title,
            subtitle: phase.subtitle || '',
            icon: phase.icon || 'fa-user',
            color: phase.color || '#3b82f6',
            target_alumni_type: phase.target_type || 'all',
            sections: (phase.sections || []).map((section, sIdx) => ({
                id: (section.id && typeof section.id === 'number' && section.id > 0) ? section.id : null,
                title: section.title,
                description: section.description || '',
                questions: (section.questions || []).map((q, qIdx) => {
                    const isChoiceType = ['multiple_choice', 'checkboxes', 'dropdown'].includes(q.type);
                    const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(q.type);

                    const question = {
                        id: (q.id && typeof q.id === 'number' && q.id > 0) ? q.id : null,
                        question_text: q.label,
                        type: q.type,
                        is_required: q.required,
                        placeholder: q.placeholder || null,
                        allow_other_option: isChoiceType ? !!q.allowOther : false,
                    };

                    if (isChoiceType) {
                        question.options = (q.options || []).map(opt => ({ label: opt }));
                    }

                    if (q.condition && q.condition.dependsOnQuestionId) {
                        question.condition = {
                            depends_on_question_id: q.condition.dependsOnQuestionId,
                            match_type: q.condition.matchType || 'any',
                            option_labels: q.condition.optionLabels || []
                        };
                    }

                    if (isGridType) {
                        if (q.gridRows && q.gridRows.length > 0) {
                            question.grid_rows = q.gridRows.map(row => ({ label: row }));
                        }
                        if (q.gridColumns && q.gridColumns.length > 0) {
                            question.grid_columns = q.gridColumns.map(col => ({ label: col }));
                        }
                    }

                    return question;
                })
            }))
        }))
    };

    console.log('💾 Saving phases directly:', JSON.parse(JSON.stringify(payload)));

    try {
        const response = await fetch(`${API_BASE}/phases/save`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify(payload),
        });

        if (!response.ok) {
            const error = await response.json();
            hideLoading();
            throw new Error(error.message || 'Failed to save phases');
        }

        const result = await response.json();
        console.log('✅ Save response:', result);

        // 🔥 CRITICAL FIX: Don't trust the POST response's phases.
        // Always re-fetch from GET /phases to guarantee fresh state.
        try {
            const freshRes = await fetch(`${API_BASE}/phases`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            if (freshRes.ok) {
                const freshPhases = await freshRes.json();
                console.log('🔄 Fresh phases re-fetched:', freshPhases);

                // 🔍 Diagnostic: log each choice question's persisted state
                const diagnostics = [];
                freshPhases.forEach(phase => {
                    (phase.sections || []).forEach(section => {
                        (section.questions || []).forEach(q => {
                            if (['multiple_choice', 'checkboxes', 'dropdown'].includes(q.type)) {
                                diagnostics.push({
                                    id: q.id,
                                    label: q.question_text,
                                    allow_other_option: q.allow_other_option,
                                    typeof_allow_other: typeof q.allow_other_option,
                                    options: (q.options || []).map(o => ({
                                        label: o.option_label,
                                        is_other: o.is_other,
                                        typeof_is_other: typeof o.is_other,
                                    })),
                                });
                            }
                        });
                    });
                });
                console.log('🔍 Persisted choice-question state:', diagnostics);

                phases = freshPhases.map(phase => mapPhaseToFrontend(phase));
            } else {
                console.warn('⚠️ Fresh re-fetch failed, falling back to POST response');
                if (result.phases) {
                    phases = result.phases.map(phase => mapPhaseToFrontend(phase));
                }
            }
        } catch (refetchErr) {
            console.warn('⚠️ Fresh re-fetch error, falling back to POST response:', refetchErr);
            if (result.phases) {
                phases = result.phases.map(phase => mapPhaseToFrontend(phase));
            }
        }

        hideLoading();
        showToast('Phases saved successfully!', 'success');
        renderBuilder();
        return true;
    } catch (error) {
        hideLoading();
        console.error('❌ Failed to save phases:', error);
        showToast('Failed to save phases: ' + error.message, 'error');
        return false;
    }
}

    // ═══════════════════════════════════════
    // TOAST NOTIFICATIONS
    // ═══════════════════════════════════════

    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) return;
        
        const icons = {
            success: 'fa-check-circle',
            error: 'fa-exclamation-circle',
            warning: 'fa-triangle-exclamation',
            info: 'fa-info-circle'
        };
        
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.innerHTML = `
            <i class="fa-solid ${icons[type] || icons.info}"></i>
            <span>${message}</span>
            <button class="toast-close" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        `;
        
        container.appendChild(toast);
        
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'toastOut 0.3s ease forwards';
                setTimeout(() => toast.remove(), 300);
            }
        }, 4000);
    }

    // ═══════════════════════════════════════
    // LOADING INDICATORS
    // ═══════════════════════════════════════

    let loadingTimeout = null;

    function showLoading(text = 'Saving phases...', subtext = 'Please wait while we save your changes') {
        const overlay = document.getElementById('loadingOverlay');
        document.getElementById('loadingText').textContent = text;
        document.getElementById('loadingSubtext').textContent = subtext;
        overlay.classList.add('active');
        
        document.querySelectorAll('button:not(.modal-close):not(.toast-close)').forEach(btn => {
            btn.disabled = true;
            btn.style.cursor = 'not-allowed';
            btn.style.opacity = '0.6';
        });
    }

    function hideLoading() {
        const overlay = document.getElementById('loadingOverlay');
        overlay.classList.remove('active');
        
        setTimeout(() => {
            document.querySelectorAll('button:not(.modal-close):not(.toast-close)').forEach(btn => {
                btn.disabled = false;
                btn.style.cursor = '';
                btn.style.opacity = '1';
            });
        }, 300);
    }

    // ═══════════════════════════════════════
    // WARNING MODAL
    // ═══════════════════════════════════════

    let pendingAction = null;

    function closeWarningModal() {
        const warningOverlay = document.getElementById('warningModal');
        if (warningOverlay) warningOverlay.classList.remove('active');
        document.body.style.overflow = '';
        pendingAction = null;
    }

    function showWarningModal(config) {
        const {
            title = 'Confirm Action',
            message = 'Are you sure?',
            iconType = 'warning',
            confirmText = 'Confirm',
            confirmClass = 'btn-danger',
            onConfirm = null
        } = config;

        const warningOverlay = document.getElementById('warningModal');
        const warningTitle = document.getElementById('warningModalTitle');
        const warningMessage = document.getElementById('warningModalMessage');
        const warningIcon = document.getElementById('warningModalIcon');
        const confirmBtn = document.getElementById('warningModalConfirm');
        const cancelBtn = document.getElementById('warningModalCancel');

        if (!warningOverlay || !warningTitle || !warningMessage || !warningIcon || !confirmBtn) return;

        warningTitle.textContent = title;
        warningMessage.innerHTML = message;
        warningIcon.className = 'warning-modal-icon ' + iconType;

        const iconElement = warningIcon.querySelector('i');
        if (iconElement) {
            if (iconType === 'danger') {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            } else if (iconType === 'success') {
                iconElement.className = 'fa-solid fa-circle-check';
            } else if (iconType === 'info') {
                iconElement.className = 'fa-solid fa-circle-info';
            } else {
                iconElement.className = 'fa-solid fa-triangle-exclamation';
            }
        }

        confirmBtn.className = 'btn ' + confirmClass;
        confirmBtn.innerHTML = '<i class="fa-solid fa-check"></i> ' + confirmText;

        pendingAction = onConfirm;

        // Remove old event listeners by cloning
        const newConfirmBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
        
        const newCancelBtn = cancelBtn.cloneNode(true);
        cancelBtn.parentNode.replaceChild(newCancelBtn, cancelBtn);

        newCancelBtn.addEventListener('click', closeWarningModal);
        newConfirmBtn.addEventListener('click', function() {
            if (typeof pendingAction === 'function') {
                pendingAction();
            }
            closeWarningModal();
        });

        warningOverlay.addEventListener('click', function(e) {
            if (e.target === warningOverlay) closeWarningModal();
        });

        warningOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
        newConfirmBtn.focus();
    }

    // ═══════════════════════════════════════
    // MODAL CLOSE ON OVERLAY CLICK & ESC
    // ═══════════════════════════════════════

    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.remove('active');
            }
        });
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
        }
    });

// ═══════════════════════════════════════
// REMINDERS FUNCTIONALITY
// ═══════════════════════════════════════

let incompleteAlumni = [];
let reminderStats = null;
let sendingReminders = false;

async function openReminderModal() {
    console.log('🔍 Opening reminder modal...');
    console.log('📍 Current form ID:', currentFormId);
    
    // First, try to get the current form ID if not set
    if (!currentFormId) {
        try {
            console.log('🔄 No form ID, fetching active form...');
            const formResponse = await fetch(`${API_BASE}/active-form`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
            
            if (formResponse.ok) {
                const formData = await formResponse.json();
                currentFormId = formData.form_id;
                console.log('📝 Loaded form ID from reminder modal:', currentFormId);
            } else {
                throw new Error('No form found');
            }
        } catch (error) {
            console.error('Failed to load form:', error);
            showToast('No tracer form found. Please create a form first.', 'error');
            return;
        }
    }
    
    // If still no form, show error
    if (!currentFormId) {
        showToast('No tracer form found. Please create a form first.', 'error');
        return;
    }
    
    // Show modal and loading state
    const modal = document.getElementById('reminderModal');
    const listContainer = document.getElementById('reminderAlumniList');
    
    modal.classList.add('active');
    listContainer.innerHTML = `
        <div class="reminder-loading">
            <div class="loading-spinner"></div>
            <p>Loading alumni list...</p>
        </div>
    `;
    
    try {
        console.log('📊 Fetching incomplete alumni for form:', currentFormId);
        const data = await apiFetch(`${API_BASE}/${currentFormId}/incomplete-alumni`);
        console.log('📋 Alumni data received:', data);
        
        incompleteAlumni = data.alumni || [];
        reminderStats = data.stats;
        
        updateReminderStats();
        renderReminderAlumniList();
        
    } catch (error) {
        console.error('Failed to load alumni data:', error);
        listContainer.innerHTML = `
            <div class="reminder-empty-state">
                <i class="fa-solid fa-triangle-exclamation" style="color: #ef4444; font-size: 2.5rem;"></i>
                <p style="margin-top: 0.5rem; font-weight: 500;">Failed to load alumni data</p>
                <p style="font-size: 0.8125rem; color: var(--gray-400);">Please try again later.</p>
                <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.5rem;">Error: ${error.message || 'Unknown error'}</p>
            </div>
        `;
        showToast('Failed to load alumni data. Please try again.', 'error');
    }
}

function closeReminderModal() {
    document.getElementById('reminderModal').classList.remove('active');
    incompleteAlumni = [];
    reminderStats = null;
    document.getElementById('reminderSearch').value = '';
    document.getElementById('selectAllCheckbox').checked = false;
    updateSelectedCount();
}

function updateReminderStats() {
    if (!reminderStats) return;
    
    document.getElementById('statTotal').textContent = reminderStats.total_alumni || 0;
    document.getElementById('statDone').textContent = reminderStats.completed || 0;
    document.getElementById('statProgress').textContent = reminderStats.in_progress || 0;
    document.getElementById('statNotStarted').textContent = reminderStats.not_started || 0;
    
    document.getElementById('reminderStats').textContent = 
        `${reminderStats.not_started || 0} haven't started · ${reminderStats.in_progress || 0} in progress`;
}

function renderReminderAlumniList(filteredList = null) {
    const container = document.getElementById('reminderAlumniList');
    const list = filteredList || incompleteAlumni;
    
    if (list.length === 0) {
        container.innerHTML = `
            <div class="reminder-empty-state">
                <i class="fa-solid fa-check-circle" style="color: #10b981; font-size: 2.5rem;"></i>
                <p style="margin-top: 0.5rem; font-weight: 500;">All alumni have completed the tracer!</p>
                <p style="font-size: 0.8125rem; color: var(--gray-400);">No reminders needed at this time.</p>
            </div>
        `;
        return;
    }
    
    container.innerHTML = list.map(alumni => `
        <div class="reminder-alumni-item" data-alumni-id="${alumni.id}">
            <label class="reminder-checkbox-wrapper">
                <input type="checkbox" class="alumni-checkbox" value="${alumni.id}" onchange="updateSelectedCount()">
                <span class="checkmark"></span>
            </label>
            <div class="reminder-alumni-avatar">
                ${alumni.name.split(' ').map(n => n[0]).join('').toUpperCase()}
            </div>
            <div class="reminder-alumni-info">
                <span class="reminder-alumni-name">${alumni.name}</span>
                <span class="reminder-alumni-details">
                    ${alumni.email} · ${alumni.program} · Batch ${alumni.year_graduated}
                </span>
            </div>
            <div class="reminder-alumni-status">
                ${alumni.has_started ? `
                    <span class="reminder-badge in-progress">
                        <i class="fa-solid fa-clock-rotate-left"></i> ${alumni.completion}% done
                    </span>
                ` : `
                    <span class="reminder-badge not-started">
                        <i class="fa-solid fa-circle"></i> Not Started
                    </span>
                `}
                ${alumni.last_activity ? `
                    <span class="reminder-last-activity" title="Last activity">${alumni.last_activity}</span>
                ` : ''}
            </div>
            <div class="reminder-alumni-actions">
                <button class="btn-remind-individual" 
                        onclick="sendIndividualReminder(${alumni.id}, this)" 
                        title="Send reminder to ${alumni.name}">
                    <i class="fa-solid fa-paper-plane"></i>
                    <span>Send Reminder</span>
                </button>
            </div>
        </div>
    `).join('');
    
    updateSelectedCount();
}

function filterReminderAlumni() {
    const query = document.getElementById('reminderSearch').value.toLowerCase();
    
    if (!query) {
        renderReminderAlumniList();
        return;
    }
    
    const filtered = incompleteAlumni.filter(alumni => 
        alumni.name.toLowerCase().includes(query) ||
        alumni.email.toLowerCase().includes(query) ||
        alumni.program.toLowerCase().includes(query) ||
        alumni.year_graduated.toString().includes(query)
    );
    
    renderReminderAlumniList(filtered);
}

function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.alumni-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = checkbox.checked;
    });
    updateSelectedCount();
}

function updateSelectedCount() {
    const selectedCheckboxes = document.querySelectorAll('.alumni-checkbox:checked');
    const count = selectedCheckboxes.length;
    document.getElementById('selectedCount').textContent = count;
    
    const sendBtn = document.getElementById('btnSendSelected');
    if (sendBtn) {
        sendBtn.disabled = count === 0;
        sendBtn.style.opacity = count === 0 ? '0.5' : '1';
    }
    
    const allCheckboxes = document.querySelectorAll('.alumni-checkbox');
    const selectAll = document.getElementById('selectAllCheckbox');
    if (selectAll && allCheckboxes.length > 0) {
        selectAll.checked = count === allCheckboxes.length;
        selectAll.indeterminate = count > 0 && count < allCheckboxes.length;
    }
}

async function sendIndividualReminder(alumniId, button) {
    if (sendingReminders) return;
    if (!currentFormId) {
        showToast('No form ID found. Please refresh the page.', 'error');
        return;
    }
    
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i><span>Sending...</span>';
    button.disabled = true;
    
    try {
        console.log('📤 Sending reminder to alumni:', alumniId, 'for form:', currentFormId);
        const response = await apiFetch(`${API_BASE}/${currentFormId}/send-reminder/${alumniId}`, {
            method: 'POST'
        });
        
        showToast(response.message || 'Reminder sent successfully!', 'success');
        
        button.innerHTML = '<i class="fa-solid fa-check"></i><span>Sent!</span>';
        button.style.background = '#10b981';
        button.style.color = '#ffffff';
        
        setTimeout(() => {
            button.innerHTML = originalHTML;
            button.style.background = '';
            button.style.color = '';
            button.disabled = false;
        }, 2000);
        
    } catch (error) {
        console.error('Failed to send reminder:', error);
        showToast(error.message || 'Failed to send reminder. Please try again.', 'error');
        button.innerHTML = originalHTML;
        button.disabled = false;
    }
}

async function sendBulkReminders() {
    if (sendingReminders) return;
    if (!currentFormId) {
        showToast('No form ID found. Please refresh the page.', 'error');
        return;
    }
    
    const selectedCheckboxes = document.querySelectorAll('.alumni-checkbox:checked');
    if (selectedCheckboxes.length === 0) {
        showWarningModal({
            title: 'No Alumni Selected',
            message: 'Please select at least one alumni to send reminders to.',
            iconType: 'warning',
            confirmText: 'OK',
            confirmClass: 'btn-primary',
            onConfirm: function() {
                // Just close the modal
            }
        });
        return;
    }
    
    const alumniIds = Array.from(selectedCheckboxes).map(cb => parseInt(cb.value));
    const alumniNames = Array.from(selectedCheckboxes).map(cb => {
        const item = cb.closest('.reminder-alumni-item');
        return item ? item.querySelector('.reminder-alumni-name').textContent : 'Unknown';
    });
    
    // Use the warning modal for confirmation
    showWarningModal({
        title: 'Send Bulk Reminders',
        message: `
            <div style="text-align: left; max-height: 200px; overflow-y: auto; padding: 0.5rem 0;">
                <p style="margin-bottom: 0.5rem;"><strong>Are you sure you want to send reminder emails to ${alumniIds.length} alumni?</strong></p>
                <div style="font-size: 0.8125rem; color: var(--gray-600);">
                    <p style="margin-bottom: 0.25rem;">This will send emails to:</p>
                    <ul style="margin: 0; padding-left: 1.25rem; list-style: disc;">
                        ${alumniNames.slice(0, 10).map(name => `<li>${name}</li>`).join('')}
                        ${alumniNames.length > 10 ? `<li style="list-style: none; color: var(--gray-500);">...and ${alumniNames.length - 10} more</li>` : ''}
                    </ul>
                </div>
            </div>
        `,
        iconType: 'info',
        confirmText: 'Send Reminders',
        confirmClass: 'btn-primary',
        onConfirm: async function() {
            // Close the warning modal first
            closeWarningModal();
            
            // Then proceed with sending
            sendingReminders = true;
            const btnSend = document.getElementById('btnSendSelected');
            const originalHTML = btnSend.innerHTML;
            btnSend.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';
            btnSend.disabled = true;
            
            try {
                console.log('📤 Sending bulk reminders to:', alumniIds.length, 'alumni for form:', currentFormId);
                const response = await apiFetch(`${API_BASE}/${currentFormId}/send-reminder-all`, {
                    method: 'POST',
                    body: JSON.stringify({ alumni_ids: alumniIds })
                });
                
                showToast(response.message || `Successfully sent ${response.sent_count} reminders!`, 'success');
                
                // Reset button state
                btnSend.innerHTML = originalHTML;
                btnSend.disabled = false;
                sendingReminders = false;
                
                // Refresh the alumni list
                try {
                    console.log('🔄 Refreshing alumni list...');
                    const data = await apiFetch(`${API_BASE}/${currentFormId}/incomplete-alumni`);
                    incompleteAlumni = data.alumni || [];
                    reminderStats = data.stats;
                    
                    updateReminderStats();
                    renderReminderAlumniList();
                    showToast('Alumni list refreshed successfully!', 'success');
                } catch (refreshError) {
                    console.error('Failed to refresh alumni list:', refreshError);
                }
                
            } catch (error) {
                console.error('Failed to send bulk reminders:', error);
                showToast(error.message || 'Failed to send reminders. Please try again.', 'error');
                btnSend.innerHTML = originalHTML;
                btnSend.disabled = false;
                sendingReminders = false;
            }
        }
    });
}

    // ═══════════════════════════════════════
    // INITIALIZATION
    // ═══════════════════════════════════════

    document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 DOM Content Loaded - Initializing Tracer...');
        
        if (window._tracerInitialized) {
            console.log('⚠️ Tracer already initialized, skipping...');
            return;
        }
        window._tracerInitialized = true;

        if (document.getElementById('preview-panel')?.classList.contains('active')) {
            renderPreview();
        }

        // Loading states
        const phasesList = document.getElementById('phasesList');
        phasesList.innerHTML = `
            <div class="skeleton-phase">
                <div class="skeleton-phase-inner">
                    <div class="skeleton-icon"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            </div>
            <div class="skeleton-phase">
                <div class="skeleton-phase-inner">
                    <div class="skeleton-icon"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            </div>
            <div class="skeleton-phase">
                <div class="skeleton-phase-inner">
                    <div class="skeleton-icon"></div>
                    <div class="skeleton-lines">
                        <div class="skeleton-line"></div>
                        <div class="skeleton-line short"></div>
                    </div>
                </div>
            </div>
        `;

        const emptyState = document.getElementById('emptyBuilderState');
        const builderContent = document.getElementById('builderContent');
        
        builderContent.style.cssText = 'display: flex; flex-direction: column; flex: 1; height: 100%; position: relative;';
        
        emptyState.innerHTML = `
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); text-align: center;">
                <div class="loading-spinner" style="margin: 0 auto 0.75rem auto;"></div>
                <p class="loading-text">Loading tracer form...</p>
            </div>
        `;
        emptyState.style.cssText = 'position: relative; flex: 1; width: 100%;';
        document.getElementById('phaseDetailContent').style.display = 'none';

        // Load tracer form and initial data
        loadPhases().then(async () => {
            if (document.getElementById('responses-panel').classList.contains('active')) {
                await loadResponses();
            }
            if (document.getElementById('dashboard-panel').classList.contains('active')) {
                await loadDashboardData();
            }
        }).catch(err => {
            console.error('Failed to load tracer form:', err);
            renderBuilder();
            renderResponsesTable([]);
        });
    });

    // ═══════════════════════════════════════
    // QUESTION MODAL FUNCTIONS
    // ═══════════════════════════════════════
    function openQuestionModal(phaseId, secId, question) {
        currentEditQuestion = { phaseId, secId, question };

        ruleBuilderLoaded = false;
        
        // 🔥 Reset all temp state first to prevent stale carry-over
        tempOptions = [];
        tempGridRows = [];
        tempGridColumns = [];
        tempCondition = null;

        if (question) {
            const isChoiceType = ['multiple_choice', 'checkboxes', 'dropdown'].includes(question.type);
            if (isChoiceType && Array.isArray(question.options)) {
                tempOptions = [...question.options];
            }

            const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(question.type);
            if (isGridType) {
                tempGridRows = Array.isArray(question.gridRows) ? [...question.gridRows] : [];
                tempGridColumns = Array.isArray(question.gridColumns) ? [...question.gridColumns] : [];
                if (question.type === 'likert_scale' && tempGridColumns.length === 0) {
                    tempGridColumns = [...DEFAULT_LIKERT_COLUMNS];
                }
            }
        }
        
        document.getElementById('questionModalTitle').textContent = question ? 'Edit Question' : 'Add New Question';
        document.getElementById('qLabel').value = question ? question.label : '';
        document.getElementById('qType').value = question ? question.type : 'short_answer';
        document.getElementById('qPlaceholder').value = question?.placeholder || '';
        document.getElementById('qRequired').checked = question ? !!question.required : true;
        document.getElementById('qAllowOther').checked = question ? !!question.allowOther : false;

        // 🔍 Debug log so we can verify what's loading
        console.log('📂 openQuestionModal:', {
            isEdit: !!question,
            type: question?.type,
            options: question?.options,
            allowOther: question?.allowOther,
        });
        
        // Load existing condition (if any)
        tempCondition = question?.condition
            ? JSON.parse(JSON.stringify(question.condition))
            : null;

        const hasCondition = !!tempCondition;
        document.getElementById('qHasCondition').checked = hasCondition;
        document.getElementById('conditionEditor').style.display = hasCondition ? 'block' : 'none';

        if (hasCondition) {
            populateConditionParents();
            requestAnimationFrame(() => {
                if (tempCondition.dependsOnQuestionId) {
                    document.getElementById('conditionParentQuestion').value = tempCondition.dependsOnQuestionId;
                    document.getElementById('conditionMatchType').value = tempCondition.matchType || 'any';
                    handleConditionParentChange();
                }
            });
        }

        handleTypeChange();
        renderOptionsList();
        renderGridRowsList();
        renderGridColumnsList();
        
        // Reset mapping UI state
        currentAnalyticsLink = null;
        const mapToggle = document.getElementById('qHasAnalyticsMapping');
        const mapEditor = document.getElementById('analyticsMappingEditor');
        if (mapToggle) mapToggle.checked = false;
        if (mapEditor) mapEditor.style.display = 'none';

        // If editing an existing question, fetch its existing analytics link
        if (question && question.id) {
            fetch(`${API_BASE}/institutional/question/${question.id}/link`, {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            })
            .then(r => r.json())
            .then(async (data) => {
                if (data.linked) {
                    currentAnalyticsLink = data;
                    if (mapToggle) mapToggle.checked = true;
                    await handleAnalyticsMappingToggle();

                    const sel = document.getElementById('analyticsIndicatorSelect');
                    if (sel) sel.value = data.indicator_key;
                    renderAnalyticsOptionMaps();
                }
            })
            .catch(() => { /* silently ignore */ });
        }

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
        const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(type);

        document.getElementById('optionsGroup').style.display = isChoiceType ? 'block' : 'none';
        document.getElementById('placeholderGroup').style.display = isTextType ? 'block' : 'none';
        document.getElementById('otherOptionGroup').style.display = isChoiceType ? 'block' : 'none';
        document.getElementById('gridSettingsGroup').style.display = isGridType ? 'block' : 'none';

        document.getElementById('typeHelp').textContent = typeHelpText[type] || '';

        document.getElementById('gridColumnsLabel').textContent =
            type === 'likert_scale' ? 'Scale Options (Columns)' : 'Choice Options (Columns)';

        // 🔥 Only clear options if we're moving AWAY from choice/grid
        if (!isChoiceType && !isGridType) {
            tempOptions = [];
            renderOptionsList();
        }

        if (!isGridType) {
            tempGridRows = [];
            tempGridColumns = [];
            renderGridRowsList();
            renderGridColumnsList();
        }

        if (isGridType) {
            if (tempGridColumns.length === 0) {
                tempGridColumns = type === 'likert_scale' ? [...DEFAULT_LIKERT_COLUMNS] : ['Option 1'];
            }
            renderGridRowsList();
            renderGridColumnsList();
        }
    }

    // ═══════════════════════════════════════
    // OPTIONS MANAGEMENT
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
    // GRID ROWS MANAGEMENT
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
    // GRID COLUMNS MANAGEMENT
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
    // SAVE QUESTION
    // ═══════════════════════════════════════

    function saveQuestion() {
        const label = document.getElementById('qLabel').value.trim();
        if (!label) {
            showWarningModal({
                title: 'Missing Question',
                message: 'Please enter a question label before saving.',
                iconType: 'warning',
                confirmText: 'OK',
                confirmClass: 'btn-primary',
                onConfirm: function() {}
            });
            return;
        }
        
        const type = document.getElementById('qType').value;
        const isChoiceType = ['multiple_choice', 'checkboxes', 'dropdown'].includes(type);
        const isTextType = ['short_answer', 'paragraph'].includes(type);
        const isFileType = type === 'file_upload';
        const isGridType = ['likert_scale', 'multiple_choice_grid'].includes(type);
        
        if (isChoiceType && tempOptions.length < 2) {
            showWarningModal({
                title: 'Not Enough Options',
                message: 'Please add at least 2 options for this question type.',
                iconType: 'warning',
                confirmText: 'OK',
                confirmClass: 'btn-primary',
                onConfirm: function() {}
            });
            return;
        }
        
        if (isGridType) {
            if (tempGridRows.length === 0) {
                showWarningModal({
                    title: 'Missing Statements',
                    message: 'Please add at least 1 statement (row) for the grid.',
                    iconType: 'warning',
                    confirmText: 'OK',
                    confirmClass: 'btn-primary',
                    onConfirm: function() {}
                });
                return;
            }
            if (tempGridColumns.length === 0) {
                showWarningModal({
                    title: 'Missing Options',
                    message: 'Please add at least 1 option (column) for the grid.',
                    iconType: 'warning',
                    confirmText: 'OK',
                    confirmClass: 'btn-primary',
                    onConfirm: function() {}
                });
                return;
            }
        }

        // 🔥 Always include every field so downstream has a stable shape
        const qData = {
            id: currentEditQuestion.question && typeof currentEditQuestion.question.id === 'number' && currentEditQuestion.question.id > 0
                ? currentEditQuestion.question.id
                : null,
            label,
            type,
            required: document.getElementById('qRequired').checked,
            allowOther: isChoiceType ? document.getElementById('qAllowOther').checked : false,
        };

        if (isChoiceType) {
            qData.options = [...tempOptions];
        }

        // Attach condition if it's valid
        if (tempCondition && tempCondition.dependsOnQuestionId && (tempCondition.optionLabels || []).length > 0) {
            qData.condition = {
                dependsOnQuestionId: tempCondition.dependsOnQuestionId,
                matchType: tempCondition.matchType || 'any',
                optionLabels: [...tempCondition.optionLabels]
            };
        } else {
            qData.condition = null;
        }

        if (isTextType) {
            qData.placeholder = document.getElementById('qPlaceholder').value.trim();
        }

        if (isGridType) {
            qData.gridRows = [...tempGridRows];
            qData.gridColumns = [...tempGridColumns];
        }

        // 🔍 Debug — visible confirmation of what's being saved
        console.log('💾 saveQuestion payload:', JSON.stringify(qData, null, 2));

        const { phaseId, secId, question } = currentEditQuestion;
        
        phases = phases.map(p => {
            if (Number(p.id) !== Number(phaseId)) return p;
            return {
                ...p,
                sections: p.sections.map(s => {
                    if (Number(s.id) !== Number(secId)) return s;
                    return {
                        ...s,
                        questions: question
                            ? s.questions.map(q => Number(q.id) === Number(question.id) ? qData : q)
                            : [...s.questions, qData]
                    };
                })
            };
        });

        // Capture state BEFORE closing the modal
        const shouldSaveMapping = document.getElementById('qHasAnalyticsMapping').checked;
        const mappingIndicatorKey = document.getElementById('analyticsIndicatorSelect').value;
        const mappingSelections = [];
        if (shouldSaveMapping && mappingIndicatorKey) {
            document.querySelectorAll('#analyticsOptionMapList select[data-option-index]').forEach(sel => {
                const idx = parseInt(sel.dataset.optionIndex, 10);
                if (sel.value) {
                    mappingSelections.push({
                        option_label: tempOptions[idx],
                        mapped_value: sel.value,
                    });
                }
            });
        }

        const savedLabel = qData.label;
        closeQuestionModal();
        renderBuilder();

        savePhasesToBackend().then(async (ok) => {
            if (!ok) return;

            // Find the saved question by label to get its real ID
            let savedQuestionId = null;
            for (const phase of phases) {
                for (const section of phase.sections) {
                    const found = section.questions.find(x => x.label === savedLabel);
                    if (found && found.id) { savedQuestionId = found.id; break; }
                }
                if (savedQuestionId) break;
            }

            if (!savedQuestionId) return;

            // Save or clear the mapping
            if (shouldSaveMapping && mappingIndicatorKey) {
                try {
                    await fetch(`${API_BASE}/institutional/question-link`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                        body: JSON.stringify({
                            question_id: savedQuestionId,
                            indicator_key: mappingIndicatorKey,
                            option_labels: mappingSelections,
                        }),
                    });
                    console.log('✅ Mapping saved for question', savedQuestionId);
                } catch (e) {
                    console.warn('⚠️ Mapping save failed:', e);
                }
            } else {
                // Clear any existing mapping
                try {
                    await fetch(`${API_BASE}/institutional/question/${savedQuestionId}/link`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });
                } catch (e) { /* ignore */ }
            }

            // Refresh the mapping UI to reflect saved state
            if (typeof refreshInstitutional === 'function' && document.getElementById('institutional-panel')?.classList.contains('active')) {
                refreshInstitutional();
            }
        });
        
    }

    // ═══════════════════════════════════════
    // PHASE MODAL
    // ═══════════════════════════════════════

    function openPhaseModal(phaseId) {
        const phase = phaseId ? phases.find(p => p.id === phaseId) : null;
        currentEditPhase = phase;
        selectedIcon = phase ? phase.icon : 'fa-user';
        selectedColor = phase ? phase.color : '#3b82f6';
        selectedTargetType = phase ? phase.target_type || 'all' : 'all';
        
        document.getElementById('phaseModalTitle').textContent = phase ? 'Edit Phase' : 'Add New Phase';
        document.getElementById('phaseTitle').value = phase ? phase.title : '';
        document.getElementById('phaseSubtitle').value = phase ? phase.subtitle : '';
        
        document.querySelectorAll('.type-option').forEach(opt => {
            const value = opt.querySelector('input[type="radio"]').value;
            opt.classList.toggle('selected', value === selectedTargetType);
            opt.querySelector('input[type="radio"]').checked = value === selectedTargetType;
        });
        
        renderIconGrid();
        renderColorGrid();
        
        document.getElementById('phaseModal').classList.add('active');
    }

    function selectAlumniType(type) {
        selectedTargetType = type;
        document.querySelectorAll('.type-option').forEach(opt => {
            const value = opt.querySelector('input[type="radio"]').value;
            opt.classList.toggle('selected', value === type);
            opt.querySelector('input[type="radio"]').checked = value === type;
        });
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
        if (!title) {
            showWarningModal({
                title: 'Missing Phase Title',
                message: 'Please enter a phase title before saving.',
                iconType: 'warning',
                confirmText: 'OK',
                confirmClass: 'btn-primary',
                onConfirm: function() {
                    // Just close the modal
                }
            });
            return;
        }
        
        const data = {
            title,
            subtitle: document.getElementById('phaseSubtitle').value.trim(),
            icon: selectedIcon,
            color: selectedColor,
            target_type: selectedTargetType || 'all'
        };

        if (currentEditPhase) {
            phases = phases.map(p => p.id === currentEditPhase.id ? { ...p, ...data } : p);
        } else {
            const newId = phases.length > 0 ? Math.max(0, ...phases.map(p => p.id)) + 1 : 1;
            phases.push({ id: newId, ...data, sections: [] });
            selectedPhaseId = newId;
        }

        closePhaseModal();
        renderBuilder();
        savePhasesToBackend();
    }

    // ═══════════════════════════════════════
    // SECTION MODAL
    // ═══════════════════════════════════════

    function openSectionModal(sectionId, phaseId) {
        const secId = sectionId ? Number(sectionId) : null;
        const phId = Number(phaseId);
        
        const section = secId ? phases.find(p => p.id === phId)?.sections.find(s => Number(s.id) === secId) : null;
        currentEditSection = { section, phaseId: phId };
        
        document.getElementById('sectionModalTitle').textContent = section ? 'Edit Section' : 'Add New Section';
        document.getElementById('sectionTitle').value = section ? section.title : '';
        document.getElementById('sectionDesc').value = section ? (section.description || '') : '';
        
        document.getElementById('sectionModal').classList.add('active');
    }

    function closeSectionModal() {
        document.getElementById('sectionModal').classList.remove('active');
        currentEditSection = null;
    }

    function saveSection() {
        const title = document.getElementById('sectionTitle').value.trim();
        if (!title) {
            showWarningModal({
                title: 'Missing Section Title',
                message: 'Please enter a section title before saving.',
                iconType: 'warning',
                confirmText: 'OK',
                confirmClass: 'btn-primary',
                onConfirm: function() {
                    // Just close the modal
                }
            });
            return;
        }
        
        const data = {
            title,
            description: document.getElementById('sectionDesc').value.trim()
        };

        const { section, phaseId } = currentEditSection;

        phases = phases.map(p => {
            if (Number(p.id) !== Number(phaseId)) return p;
            return {
                ...p,
                sections: section
                    ? p.sections.map(s => Number(s.id) === Number(section.id) ? { ...s, ...data } : s)
                    : [...p.sections, { id: null, ...data, questions: [] }]
            };
        });

        if (!section) {
            const phase = phases.find(p => Number(p.id) === Number(phaseId));
            if (phase && phase.sections.length > 0) {
                const newSection = phase.sections[phase.sections.length - 1];
                expandedSections.add(Number(newSection.id));
            }
        } else {
            expandedSections.add(Number(section.id));
        }

        closeSectionModal();
        renderBuilder();
        savePhasesToBackend();
    }

async function duplicatePhase(id) {
    const source = phases.find(p => Number(p.id) === Number(id));
    if (!source) return;

    showWarningModal({
        title: 'Duplicate Phase',
        message: `Duplicate <strong>"${source.title}"</strong> including all its sections and questions?<br><small>The duplicate will appear right below the original and won't be archived.</small>`,
        iconType: 'info',
        confirmText: 'Duplicate',
        confirmClass: 'btn-primary',
        onConfirm: async function() {
            showLoading('Duplicating phase...', 'Please wait');

            try {
                const response = await fetch(`${API_BASE}/phase/${id}/duplicate`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });

                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Failed to duplicate phase');
                }

                hideLoading();
                showToast('Phase duplicated successfully!', 'success');

                // Reload from server (gets real IDs)
                await loadPhases();

                // 🔥 Auto-select the newly duplicated phase
                if (data?.phase_id) {
                    selectedPhaseId = data.phase_id;
                    renderBuilder();
                }
            } catch (error) {
                hideLoading();
                console.error('Duplicate error:', error);
                showToast('Failed to duplicate: ' + error.message, 'error');
            }
        }
    });
}

async function togglePhaseArchive(id) {
    const phase = phases.find(p => Number(p.id) === Number(id));
    if (!phase) return;

    const isArchiving = !phase.is_archived;

    showWarningModal({
        title: isArchiving ? 'Archive Phase' : 'Restore Phase',
        message: isArchiving
            ? `Archive <strong>"${phase.title}"</strong>?<br><small>It will be hidden from alumni in the mobile app, but you can still see and edit it here.</small>`
            : `Restore <strong>"${phase.title}"</strong>?<br><small>It will become visible to alumni again.</small>`,
        iconType: isArchiving ? 'warning' : 'info',
        confirmText: isArchiving ? 'Archive' : 'Restore',
        confirmClass: 'btn-primary',
        onConfirm: async function() {
            try {
                const response = await fetch(`${API_BASE}/phase/${id}/archive`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                });

                if (!response.ok) {
                    const err = await response.json();
                    throw new Error(err.message || 'Failed to toggle archive');
                }

                const data = await response.json();

                // Update local state
                phases = phases.map(p =>
                    Number(p.id) === Number(id)
                        ? { ...p, is_archived: data.is_archived }
                        : p
                );

                showToast(data.message || 'Success!', 'success');
                renderBuilder();
            } catch (error) {
                console.error('Archive toggle error:', error);
                showToast('Failed: ' + error.message, 'error');
            }
        }
    });
}

// ═══════════════════════════════════════
// INSTITUTIONAL MAPPING — QUESTION MODAL
// ═══════════════════════════════════════

let availableIndicators = [];
let currentAnalyticsLink = null;

async function loadIndicatorsOnce() {
    if (availableIndicators.length > 0) return availableIndicators;
    try {
        availableIndicators = await apiFetch(`${API_BASE}/institutional/indicators`);
    } catch (e) {
        console.warn('Failed to load indicators:', e);
        availableIndicators = [];
    }
    return availableIndicators;
}

async function handleAnalyticsMappingToggle() {
    const isOn = document.getElementById('qHasAnalyticsMapping').checked;
    document.getElementById('analyticsMappingEditor').style.display = isOn ? 'block' : 'none';

    if (isOn) {
        await loadIndicatorsOnce();
        const select = document.getElementById('analyticsIndicatorSelect');
        select.innerHTML = '<option value="">-- Select an indicator --</option>' +
            availableIndicators.map(i =>
                `<option value="${i.indicator_key}">${i.label}</option>`
            ).join('');

        // Restore if editing
        if (currentAnalyticsLink?.linked) {
            select.value = currentAnalyticsLink.indicator_key;
            renderAnalyticsOptionMaps();
        }
    } else {
        currentAnalyticsLink = null;
    }
}

function renderAnalyticsOptionMaps() {
    const indicatorKey = document.getElementById('analyticsIndicatorSelect').value;
    const group = document.getElementById('analyticsOptionMapGroup');
    const list = document.getElementById('analyticsOptionMapList');
    const help = document.getElementById('analyticsIndicatorHelp');

    if (!indicatorKey) {
        group.style.display = 'none';
        help.textContent = '';
        return;
    }

    const indicator = availableIndicators.find(i => i.indicator_key === indicatorKey);
    help.textContent = indicator?.description || '';

    const isChoiceType = ['multiple_choice','checkboxes','dropdown'].includes(document.getElementById('qType').value);
    if (!isChoiceType) {
        group.style.display = 'none';
        help.textContent += ' (Only choice questions can be mapped at the option level.)';
        return;
    }

    const allowed = indicator?.allowed_values || [];
    if (allowed.length === 0 && indicator?.value_type === 'boolean') {
        // Boolean — auto "Yes"/"No"
        list.innerHTML = tempOptions.map((opt, i) => `
            <div style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; background:#fff; border:1px solid #E5E7EB; border-radius:0.5rem; margin-bottom:0.375rem;">
                <span style="flex:1; font-size:0.8125rem;">${opt}</span>
                <select class="form-control" data-option-index="${i}" style="max-width:140px; padding:0.375rem;">
                    <option value="">Unmapped</option>
                    <option value="true">Yes</option>
                    <option value="false">No</option>
                </select>
            </div>
        `).join('');
    } else {
        list.innerHTML = tempOptions.map((opt, i) => `
            <div style="display:flex; align-items:center; gap:0.5rem; padding:0.5rem 0.75rem; background:#fff; border:1px solid #E5E7EB; border-radius:0.5rem; margin-bottom:0.375rem;">
                <span style="flex:1; font-size:0.8125rem;">${opt}</span>
                <select class="form-control" data-option-index="${i}" style="max-width:200px; padding:0.375rem;">
                    <option value="">Unmapped</option>
                    ${allowed.map(v => `<option value="${v}">${v.replace(/_/g, ' ')}</option>`).join('')}
                </select>
            </div>
        `).join('');
    }

    group.style.display = 'block';

        // Restore previous selections if we have them
    if (currentAnalyticsLink?.linked && currentAnalyticsLink.option_maps) {
        const optionMaps = currentAnalyticsLink.option_maps; // { option_id: mapped_value }
        // Since we only have labels, we need to map by index
        // Build a label→value lookup from the server response (which has option_ids)
        // — but we don't have option_ids client-side. So we rely on the server returning
        // the value keyed by ID; we can't restore by label without another round-trip.
        // Simplest fix: don't restore individual dropdown values — the admin will re-map if needed.
        // (A future enhancement could return { option_label: mapped_value }.)
    }

}

/**
 * Called from saveQuestion() AFTER savePhasesToBackend resolves.
 * We delay this to ensure question IDs exist.
 */
async function saveAnalyticsMapping(questionId) {
    if (!questionId) return;

    const isOn = document.getElementById('qHasAnalyticsMapping').checked;

    if (!isOn) {
        // Remove any existing mapping
        try {
            await fetch(`${API_BASE}/institutional/question/${questionId}/link`, {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            });
        } catch (e) { /* ignore */ }
        return;
    }

    const indicatorKey = document.getElementById('analyticsIndicatorSelect').value;
    if (!indicatorKey) return;

    // Build option maps: option_id → mapped_value
    // We need the real option IDs, which we don't have client-side after save.
    // Easiest approach: send option LABELS, and let the server match them.
    const optionSelections = [];
    document.querySelectorAll('#analyticsOptionMapList select[data-option-index]').forEach(sel => {
        const idx = parseInt(sel.dataset.optionIndex, 10);
        if (sel.value) {
            optionSelections.push({
                option_label: tempOptions[idx],
                mapped_value: sel.value,
            });
        }
    });

    try {
        await fetch(`${API_BASE}/institutional/question-link`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            },
            body: JSON.stringify({
                question_id: questionId,
                indicator_key: indicatorKey,
                option_labels: optionSelections,   // ← server matches label → option_id
            }),
        });
    } catch (e) {
        console.warn('Analytics mapping save failed:', e);
    }
}

</script>

{{-- This is the views/alumni_tracer/partials/_scripts.blade.php --}}