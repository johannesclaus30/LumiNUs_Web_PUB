<!-- ============================================ -->
<!-- TAB: PREVIEW -->
<!-- ============================================ -->
<div class="tracer-panel" id="preview-panel">
    <div class="preview-container">
        <!-- Preview Header -->
        <div class="preview-header">
            <div class="preview-header-info">
                <h2>
                    <i class="fa-solid fa-eye" style="color: #32418C;"></i>
                    Alumni Tracer Preview
                </h2>
                <p>See how your tracer form will appear to alumni</p>
            </div>
            <div class="preview-controls">
                <span class="preview-badge">
                    <i class="fa-solid fa-info-circle"></i>
                    Preview Mode
                </span>

                <!-- Reset Simulation Button -->
                <button class="btn-reset-preview" onclick="resetPreviewAnswers()" title="Clear all simulated answers">
                    <i class="fa-solid fa-rotate-left"></i>
                    <span>Reset Simulation</span>
                </button>

                <!-- Collapse All Toggle -->
                <button class="preview-collapse-all-btn" onclick="toggleAllPreviewSections()" title="Expand/Collapse All">
                    <i class="fa-solid fa-compress-arrows-alt"></i>
                    <span>Collapse All</span>
                </button>
                <!-- Alumni Type Toggle -->
                <div class="preview-toggle-group" id="previewToggleGroup">
                    <button class="preview-toggle-btn active" data-type="college" onclick="switchPreviewType('college')">
                        <i class="fa-solid fa-graduation-cap"></i>
                        College
                    </button>
                    <button class="preview-toggle-btn" data-type="shs" onclick="switchPreviewType('shs')">
                        <i class="fa-solid fa-school"></i>
                        SHS
                    </button>
                </div>
            </div>
        </div>

        <!-- Preview Content -->
        <div class="preview-content" id="previewContent">
            <!-- Rendered by JavaScript -->
            <div class="preview-empty">
                <i class="fa-solid fa-file-lines"></i>
                <h3>No phases created yet</h3>
                <p>Go to the <strong>Tracer Builder</strong> tab to create phases and questions.</p>
            </div>
        </div>
    </div>
</div>

<script>
    // ============================================
    // PREVIEW - RENDER FUNCTIONS
    // ============================================

    let previewType = 'college'; // 'college' or 'shs'
    let collapsedPreviewSections = new Set(); // Track collapsed section IDs

    // Simulated alumni answers for the preview (keyed by question id)
    // Value can be: string (single-select), array of strings (multi-select), or null
    let previewAnswers = {};

    /**
     * Record a simulated answer and re-render the preview.
     * Called by the mock inputs when the user clicks on them.
     */
    function setPreviewAnswer(questionId, value) {
        previewAnswers[questionId] = value;
        renderPreview();
    }

    /**
     * Toggle a value in a checkboxes-style answer array.
     */
    function togglePreviewCheckbox(questionId, value) {
        const current = previewAnswers[questionId] || [];
        const idx = current.indexOf(value);
        let next;
        if (idx >= 0) {
            next = current.filter(v => v !== value);
        } else {
            next = [...current, value];
        }
        // Empty array? Store as undefined so isQuestionVisible treats it as unanswered
        previewAnswers[questionId] = next.length > 0 ? next : null;
        renderPreview();
    }

    /**
     * Set the answer for a specific row in a grid question.
     * Stores as { [rowLabel]: columnLabel }
     */
    function setPreviewGridAnswer(questionId, rowLabel, columnLabel) {
        const current = previewAnswers[questionId] || {};
        previewAnswers[questionId] = { ...current, [rowLabel]: columnLabel };
        renderPreview();
    }

    /**
     * Switch between College and SHS preview modes
     */
    function switchPreviewType(type) {
        previewType = type;
        
        // Update toggle buttons
        document.querySelectorAll('.preview-toggle-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.type === type);
        });
        
        // Reset collapsed state when switching types
        collapsedPreviewSections = new Set();
        
        // Re-render preview
        renderPreview();
    }

    /**
     * Toggle all sections collapsed/expanded
     */
    function toggleAllPreviewSections() {
        const allSectionIds = [];
        const filteredPhases = getFilteredPhases();
        
        filteredPhases.forEach(phase => {
            phase.sections.forEach(section => {
                const sectionId = `preview-section-${phase.id}-${section.id}`;
                allSectionIds.push(sectionId);
            });
        });

        // Check if all are collapsed
        const allCollapsed = allSectionIds.every(id => collapsedPreviewSections.has(id));
        
        if (allCollapsed) {
            // Expand all
            allSectionIds.forEach(id => collapsedPreviewSections.delete(id));
        } else {
            // Collapse all
            allSectionIds.forEach(id => collapsedPreviewSections.add(id));
        }
        
        // Update button text
        updateCollapseAllButton();
        renderPreview();
    }

    /**
     * Update the collapse all button text
     */
    function updateCollapseAllButton() {
        const btn = document.querySelector('.preview-collapse-all-btn');
        if (!btn) return;
        
        const allSectionIds = [];
        const filteredPhases = getFilteredPhases();
        
        filteredPhases.forEach(phase => {
            phase.sections.forEach(section => {
                const sectionId = `preview-section-${phase.id}-${section.id}`;
                allSectionIds.push(sectionId);
            });
        });

        const allCollapsed = allSectionIds.length > 0 && allSectionIds.every(id => collapsedPreviewSections.has(id));
        
        if (allCollapsed) {
            btn.innerHTML = '<i class="fa-solid fa-expand-arrows-alt"></i><span>Expand All</span>';
        } else {
            btn.innerHTML = '<i class="fa-solid fa-compress-arrows-alt"></i><span>Collapse All</span>';
        }
    }

    /**
     * Toggle a single section
     */
    function togglePreviewSection(sectionId) {
        if (collapsedPreviewSections.has(sectionId)) {
            collapsedPreviewSections.delete(sectionId);
        } else {
            collapsedPreviewSections.add(sectionId);
        }
        updateCollapseAllButton();
        renderPreview();
    }

    /**
     * Get filtered phases based on selected alumni type
     */
    function getFilteredPhases() {
        return phases.filter(phase => {
            if (previewType === 'college') {
                return phase.target_type === 'college' || phase.target_type === 'all';
            } else { // SHS
                return phase.target_type === 'shs' || phase.target_type === 'all';
            }
        });
    }

    /**
     * Determine whether a question should be visible based on:
     *   - its condition (if any)
     *   - the simulated alumni answers
     *
     * IMPORTANT: This logic is identical to what the mobile app should use.
     */
    function isQuestionVisible(question, answers) {
        // No condition → always visible
        if (!question.condition?.dependsOnQuestionId) return true;

        const parentAnswer = answers[question.condition.dependsOnQuestionId];

        // Parent not answered yet → hide
        if (parentAnswer === undefined || parentAnswer === null || parentAnswer === '') {
            return false;
        }

        const matchLabels = question.condition.optionLabels || [];
        const matchType = question.condition.matchType || 'any';

        // Normalize parent's answer into an array
        const parentValues = Array.isArray(parentAnswer) ? parentAnswer : [parentAnswer];

        switch (matchType) {
            case 'any':
                return parentValues.some(v => matchLabels.includes(v));
            case 'all':
                return matchLabels.every(l => parentValues.includes(l));
            case 'none':
                return !parentValues.some(v => matchLabels.includes(v));
            default:
                return true;
        }
    }

    /**
     * A section is visible if it has at least one currently-visible question,
     * OR if it has no questions at all.
     */
    function isSectionVisible(section, answers) {
        if (!section.questions || section.questions.length === 0) return true;
        return section.questions.some(q => isQuestionVisible(q, answers));
    }

    /**
     * Reset all simulated answers.
     */
    function resetPreviewAnswers() {
        previewAnswers = {};
        renderPreview();
    }

    /**
     * Main preview render function
     */
    function renderPreview() {
        const container = document.getElementById('previewContent');
        
        const filteredPhases = getFilteredPhases();

        if (filteredPhases.length === 0) {
            container.innerHTML = `
                <div class="preview-empty">
                    <i class="fa-solid fa-file-lines"></i>
                    <h3>No phases available for ${previewType === 'college' ? 'College' : 'SHS'} alumni</h3>
                    <p>Create phases in the <strong>Tracer Builder</strong> tab and set the target alumni type.</p>
                </div>
            `;
            return;
        }

        let html = '';

    filteredPhases.forEach((phase, phaseIndex) => {

        // Count only VISIBLE questions for the phase header
        const visibleSections = phase.sections.filter(sec =>
            isSectionVisible(sec, previewAnswers)
        );

        if (visibleSections.length === 0) return; // Skip empty phases

        const totalVisibleQuestions = visibleSections.reduce((sum, sec) =>
            sum + sec.questions.filter(q => isQuestionVisible(q, previewAnswers)).length,
            0
        );

        html += `
            <div class="preview-phase-card" data-phase-id="${phase.id}">
                <!-- Phase Header -->
                <div class="preview-phase-header" onclick="toggleAllSectionsInPhase(${phase.id})" style="cursor: pointer;">
                    <div class="preview-phase-header-left">
                        <div class="preview-phase-icon" style="background: ${phase.color};">
                            <i class="fa-solid ${phase.icon}"></i>
                        </div>
                        <div class="preview-phase-info">
                            <h3>${phase.title}</h3>
                            <p>${phase.subtitle || 'No subtitle provided'}</p>
                        </div>
                    </div>
                    <div class="preview-phase-controls">
                        <span class="preview-phase-progress">
                            <i class="fa-regular fa-circle" style="color: #9CA3AF;"></i>
                            ${totalVisibleQuestions} question${totalVisibleQuestions !== 1 ? 's' : ''}
                        </span>
                        <span class="preview-phase-toggle-icon" id="phaseToggleIcon-${phase.id}">
                            <i class="fa-solid fa-chevron-down"></i>
                        </span>
                    </div>
                </div>

                <!-- Phase Body -->
                <div class="preview-phase-body" id="phaseBody-${phase.id}">
                    ${visibleSections.map((section, sectionIndex) => {
                        const sectionId = `preview-section-${phase.id}-${section.id}`;
                        const isCollapsed = collapsedPreviewSections.has(sectionId);

                        // Only include VISIBLE questions, renumbered
                        const visibleQuestions = section.questions.filter(q =>
                            isQuestionVisible(q, previewAnswers)
                        );

                        return `
                            <div class="preview-section-card" data-section-id="${sectionId}">
                                <div class="preview-section-header" onclick="togglePreviewSection('${sectionId}')">
                                    <div class="preview-section-header-left">
                                        <span class="section-number" style="background: ${phase.color};">${sectionIndex + 1}</span>
                                        <span class="section-title">${section.title}</span>
                                        <span class="section-question-count">
                                            ${visibleQuestions.length} question${visibleQuestions.length !== 1 ? 's' : ''}
                                        </span>
                                    </div>
                                    <div class="preview-section-header-right">
                                        <span class="preview-section-toggle-icon">
                                            <i class="fa-solid ${isCollapsed ? 'fa-chevron-right' : 'fa-chevron-down'}"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="preview-section-body ${isCollapsed ? 'collapsed' : ''}">
                                    ${section.description ? `<p class="preview-section-desc">${section.description}</p>` : ''}

                                    ${visibleQuestions.map((question, qIndex) => `
                                        <div class="preview-question">
                                            <div class="preview-question-label">
                                                <span>
                                                    ${qIndex + 1}. ${question.label}
                                                    ${question.required ? '<span class="required-star">*</span>' : ''}
                                                </span>
                                            </div>
                                            <div class="preview-question-type">
                                                <i class="fa-solid ${typeIcons[question.type] || 'fa-question'}"></i>
                                                ${typeLabels[question.type] || question.type}
                                            </div>

                                            ${renderPreviewInput(question, phase.color)}
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        `;
                    }).join('')}
                </div>
            </div>
        `;
    });

        // Add footer actions
        html += `
            <div class="preview-actions">
                <button class="btn-preview secondary" onclick="switchPreviewType(previewType === 'college' ? 'shs' : 'college')">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                    Switch to ${previewType === 'college' ? 'SHS' : 'College'} View
                </button>
                <button class="btn-preview primary" onclick="showToast('This is a preview. The actual tracer will look like this to alumni.', 'info')">
                    <i class="fa-solid fa-check-circle"></i>
                    Looks Good!
                </button>
            </div>
        `;

        // Remember scroll position before we nuke the DOM
        const scrollParent = document.getElementById('preview-panel')?.querySelector('.preview-content')
            || container;
        const previousScroll = scrollParent.scrollTop;

        container.innerHTML = html;
        updateCollapseAllButton();

        // Restore scroll position after re-render (with a micro delay so the DOM is laid out)
        requestAnimationFrame(() => {
            scrollParent.scrollTop = previousScroll;
        });
    }

    /**
     * Toggle all sections within a phase
     */
    function toggleAllSectionsInPhase(phaseId) {
        const phase = phases.find(p => p.id === phaseId);
        if (!phase) return;
        
        const sectionIds = phase.sections.map(section => `preview-section-${phase.id}-${section.id}`);
        
        // Check if all sections in this phase are collapsed
        const allCollapsed = sectionIds.every(id => collapsedPreviewSections.has(id));
        
        if (allCollapsed) {
            // Expand all sections in this phase
            sectionIds.forEach(id => collapsedPreviewSections.delete(id));
        } else {
            // Collapse all sections in this phase
            sectionIds.forEach(id => collapsedPreviewSections.add(id));
        }
        
        // Update the phase toggle icon
        const icon = document.getElementById(`phaseToggleIcon-${phaseId}`);
        if (icon) {
            const allCollapsedNow = sectionIds.every(id => collapsedPreviewSections.has(id));
            icon.innerHTML = `<i class="fa-solid ${allCollapsedNow ? 'fa-chevron-right' : 'fa-chevron-down'}"></i>`;
        }
        
        updateCollapseAllButton();
        renderPreview();
    }

    /**
     * Render the appropriate input type based on question type.
     * Interactive — clicking a mock input updates previewAnswers.
     */
    function renderPreviewInput(question, color) {
        const type = question.type;
        const answer = previewAnswers[question.id];

        // Small helper — is this option currently selected?
        const isSelectedSingle = (label) => answer === label;
        const isSelectedMulti  = (label) => Array.isArray(answer) && answer.includes(label);

        // Purple border for conditional questions so admins can spot them
        const wrapClasses = question.condition
            ? 'preview-input conditional-glow'
            : 'preview-input';
        const optionsWrapClasses = question.condition
            ? 'preview-options conditional-glow-box'
            : 'preview-options';

        switch (type) {
            case 'short_answer':
            case 'paragraph':
                return `
                    <div class="${wrapClasses}" style="border-color: ${color}33;">
                        <i class="fa-solid ${type === 'short_answer' ? 'fa-pencil' : 'fa-align-left'}" style="color: #9CA3AF; margin-right: 0.5rem;"></i>
                        ${question.placeholder || (type === 'short_answer' ? 'Type your answer here...' : 'Write your response here...')}
                    </div>
                `;

            case 'multiple_choice':
                return `
                    <div class="${optionsWrapClasses}">
                        ${(question.options || []).map(opt => `
                            <div class="preview-option ${isSelectedSingle(opt) ? 'selected' : ''}"
                                onclick="setPreviewAnswer(${question.id}, ${JSON.stringify(opt)})"
                                style="cursor: pointer;">
                                <span class="radio-circle ${isSelectedSingle(opt) ? 'filled' : ''}"></span>
                                ${opt}
                            </div>
                        `).join('')}
                    </div>
                `;

            case 'checkboxes':
                return `
                    <div class="${optionsWrapClasses}">
                        ${(question.options || []).map(opt => `
                            <div class="preview-option ${isSelectedMulti(opt) ? 'selected' : ''}"
                                onclick="togglePreviewCheckbox(${question.id}, ${JSON.stringify(opt)})"
                                style="cursor: pointer;">
                                <span class="checkbox-square ${isSelectedMulti(opt) ? 'checked' : ''}"></span>
                                ${opt}
                            </div>
                        `).join('')}
                    </div>
                `;

            case 'dropdown':
                return `
                    <div class="${wrapClasses}" style="border-color: ${color}33; padding: 0;">
                        <select style="width: 100%; border: none; outline: none; padding: 0.625rem 0.875rem; background: transparent; font-family: inherit; font-size: inherit; color: inherit; cursor: pointer;"
                                onchange="setPreviewAnswer(${question.id}, this.value ? this.value : null)">
                            <option value="" ${!answer ? 'selected' : ''}>-- Select an option --</option>
                            ${(question.options || []).map(opt => `
                                <option value="${opt.replace(/"/g, '&quot;')}" ${answer === opt ? 'selected' : ''}>${opt}</option>
                            `).join('')}
                        </select>
                    </div>
                `;

            case 'file_upload':
                return `
                    <div class="${wrapClasses} file-upload" style="border-color: ${color}33;">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <span>Click to upload or drag and drop</span>
                        <span style="font-size: 0.625rem; color: #9CA3AF;">
                            ${question.fileTypes && question.fileTypes.length > 0 ? question.fileTypes.join(', ').toUpperCase() : 'PDF, DOC, JPG'} 
                            (Max ${question.maxSize || 10}MB)
                        </span>
                    </div>
                `;

            case 'likert_scale':
            case 'multiple_choice_grid': {
                const rows = question.gridRows || [];
                const columns = question.gridColumns || [];
                const currentAnswer = answer || {};

                return `
                    <div class="preview-grid">
                        <table>
                            <thead>
                                <tr>
                                    <th style="min-width: 120px; text-align: left;">Statement</th>
                                    ${columns.map(col => `<th style="min-width: 80px;">${col}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>
                                ${rows.map(row => `
                                    <tr>
                                        <td style="text-align: left;">${row}</td>
                                        ${columns.map(col => {
                                            const isSelected = currentAnswer[row] === col;
                                            return `
                                                <td onclick='setPreviewGridAnswer(${question.id}, ${JSON.stringify(row)}, ${JSON.stringify(col)})'
                                                    style="cursor: pointer;">
                                                    <span class="grid-radio ${isSelected ? 'filled' : ''}"></span>
                                                </td>
                                            `;
                                        }).join('')}
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `;
            }

            default:
                return `
                    <div class="${wrapClasses}" style="border-color: ${color}33;">
                        <i class="fa-solid fa-question" style="color: #9CA3AF;"></i>
                        Question type: ${typeLabels[type] || type}
                    </div>
                `;
        }
    }

    // ============================================
    // PREVIEW - AUTO-RENDER ON LOAD
    // ============================================

    // Watch for phases changes and re-render preview if it's visible
    const originalRenderBuilder = renderBuilder;
    renderBuilder = function() {
        originalRenderBuilder();
        if (document.getElementById('preview-panel')?.classList.contains('active')) {
            renderPreview();
        }
    };
</script>

{{-- This is the _preview.blade.php --}}