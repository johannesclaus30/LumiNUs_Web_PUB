<div class="tracer-panel" id="builder-panel">
    <div class="builder-layout">
        <!-- Phases Sidebar -->
        <div class="phases-sidebar">
            <div class="phases-header">
                <h3>Phases</h3>
                <button class="btn-add-phase" onclick="openPhaseModal()">
                    <i class="fa-solid fa-plus"></i> Add
                </button>
            </div>
            <div class="phases-list" id="phasesList">
                <!-- Phase cards will be rendered by JS -->
            </div>
        </div>

        <!-- Builder Content Area -->
        <div class="builder-content" id="builderContent">
            <div class="empty-builder-state" id="emptyBuilderState">
                <i class="fa-solid fa-layer-group empty-builder-icon"></i>
                <h3>Select a phase to manage its content</h3>
                <p>Choose a phase from the left panel or create a new one to get started.</p>
            </div>
            <div id="phaseDetailContent" style="display: none;">
                <!-- Phase detail content rendered by JS -->
            </div>
        </div>
    </div>
</div>

<script>
    // ============================================
    // BUILDER - PHASES, SECTIONS, QUESTIONS
    // ============================================

    // This renders the builder content
    function renderBuilder() {
        const detailContent = document.getElementById('phaseDetailContent');
        detailContent.style.display = 'none';
        detailContent.innerHTML = '';

        renderPhasesList();
        const phase = phases.find(p => p.id === selectedPhaseId);
        const emptyState = document.getElementById('emptyBuilderState');

        if (!phase) {
            emptyState.style.display = 'block';
            detailContent.style.display = 'none';
            return;
        }

        emptyState.style.display = 'none';
        detailContent.style.display = 'block';

        detailContent.innerHTML = `
            <div class="builder-content-header">
                <div class="builder-phase-info">
                    <div class="builder-phase-icon" style="background:${phase.color}20; color:${phase.color};">
                        <i class="fa-solid ${phase.icon}"></i>
                    </div>
                    <div class="builder-phase-details">
                        <div style="display:flex; align-items:center; gap:0.5rem; flex-wrap:wrap;">
                            <h2 style="margin:0;">${phase.title}</h2>
                            ${(() => {
                                const targetInfo = {
                                    all: { label: 'All', icon: 'fa-users', class: 'all' },
                                    college: { label: 'College', icon: 'fa-graduation-cap', class: 'college' },
                                    shs: { label: 'SHS', icon: 'fa-school', class: 'shs' }
                                };
                                const target = targetInfo[phase.target_type] || targetInfo.all;
                                return `<span class="phase-target-badge ${target.class}">
                                    <i class="fa-solid ${target.icon}"></i>
                                    ${target.label}
                                </span>`;
                            })()}
                        </div>
                        <p>${phase.subtitle || 'No subtitle'}</p>
                    </div>
                </div>
                <button class="btn-add-section" onclick="openSectionModal(null, ${phase.id})">
                    <i class="fa-solid fa-plus"></i> Add Section
                </button>
            </div>
            ${phase.sections.length === 0 ? `
                <div class="empty-builder-state" style="border: 2px dashed var(--gray-200); border-radius: var(--radius-xl); padding: 3rem;">
                    <i class="fa-solid fa-folder-plus" style="font-size: 3rem; color: var(--gray-300);"></i>
                    <p style="color: var(--gray-500); font-weight: 500; margin-top: 0.75rem;">No sections yet</p>
                    <p style="color: var(--gray-400); font-size: 0.875rem; margin-top: 0.25rem;">Click "Add Section" to get started.</p>
                </div>
            ` : `
                <div class="sections-list">
                    ${phase.sections.map((section, secIdx) => {
                    const hasQuestions = section.questions && section.questions.length > 0;
                    const isOpen = expandedSections.has(section.id) || !hasQuestions;
                    if (!expandedSections.has(section.id) && !hasQuestions) {
                        expandedSections.add(section.id);
                    }
                    return `
                        <div class="section-card ${isOpen ? 'expanded' : ''}" data-section-id="${section.id}" data-phase-id="${phase.id}">
                            <div class="section-header" onclick="toggleSection('${section.id}')">
                                <div class="section-number" style="background:${phase.color};">${secIdx + 1}</div>
                                <div class="section-info">
                                    <h4>${section.title}</h4>
                                    <p>${section.description ? section.description + ' · ' : ''}${section.questions.length} question${section.questions.length !== 1 ? 's' : ''}</p>
                                </div>
                                <div class="section-actions" onclick="event.stopPropagation();">
                                    <button class="btn-icon" onclick="openSectionModal('${section.id}', ${phase.id})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn-icon delete" onclick="deleteSection(${phase.id}, '${section.id}')" title="Delete">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                                <i class="fa-solid fa-chevron-${isOpen ? 'up' : 'down'} section-toggle"></i>
                            </div>
                            <div class="section-body">
                                ${section.questions.length === 0 ? '<p style="color: var(--gray-400); text-align: center; padding: 1.5rem;">No questions yet.</p>' : `
                                    <div class="questions-list">
                                        ${section.questions.map((q, qi) => `
                                            <div class="question-item">
                                                <i class="fa-solid fa-grip-vertical question-drag"></i>
                                                <div class="question-number" style="background:${phase.color};">${qi + 1}</div>
                                                <div class="question-content">
                                                    <p class="question-label-text">${q.label}</p>
                                                    <div class="question-meta">
                                                        <span class="question-type-badge"><i class="fa-solid ${typeIcons[q.type] || 'fa-question'}"></i> ${typeLabels[q.type] || q.type}</span>
                                                        ${q.required ? '<span class="required-badge">Required</span>' : ''}
                                                        ${q.condition ? `<span class="condition-badge" title="Conditional question"><i class="fa-solid fa-code-branch"></i> Conditional</span>` : ''}
                                                        ${q.options ? `<span style="font-size:0.6875rem;color:var(--gray-400);">${q.options.length} options</span>` : ''}
                                                        ${q.gridRows ? `<span style="font-size:0.6875rem;color:var(--gray-400);">${q.gridRows.length} rows</span>` : ''}
                                                    </div>
                                                </div>
                                                <div class="question-actions">
                                                    <button onclick="openQuestionModal(${phase.id}, '${section.id}', ${JSON.stringify(q).replace(/"/g, '&quot;')})" title="Edit"><i class="fa-solid fa-pen"></i></button>
                                                    <button class="delete-question" onclick="deleteQuestion(${phase.id}, '${section.id}', '${q.id}')" title="Delete">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        `).join('')}
                                    </div>
                                `}
                                <button class="add-question-btn" onclick="openQuestionModal(${phase.id}, '${section.id}', null)">
                                    <i class="fa-solid fa-plus"></i> Add Question
                                </button>
                            </div>
                        </div>
                    `;
                }).join('')}
                </div>
            `}
        `;

        // Initialize drag-and-drop reordering for questions
        requestAnimationFrame(() => initQuestionSortables());
    }

    function renderPhasesList() {
        const container = document.getElementById('phasesList');
        container.innerHTML = '';

        if (!phases || phases.length === 0) {
            container.innerHTML = `
                <div style="padding: 1.5rem; text-align: center; color: var(--gray-400); font-size: 0.8125rem;">
                    <i class="fa-solid fa-folder-open" style="font-size: 1.5rem; display: block; margin-bottom: 0.5rem;"></i>
                    No phases yet. Click "Add" to create one.
                </div>
            `;
            return;
        }

        container.innerHTML = phases.map(phase => {
        const isSel = selectedPhaseId === phase.id;
        const totalQ = phase.sections.reduce((s, sec) => s + sec.questions.length, 0);

        const targetInfo = {
            all: { label: 'All', icon: 'fa-users', class: 'all' },
            college: { label: 'College', icon: 'fa-graduation-cap', class: 'college' },
            shs: { label: 'SHS', icon: 'fa-school', class: 'shs' }
        };
        const target = targetInfo[phase.target_type] || targetInfo.all;

        return `
            <div class="phase-card ${isSel ? 'active' : ''}" onclick="selectPhase(${phase.id})">
                <div class="phase-card-header">
                    <div class="phase-card-icon"
                        style="background:${isSel ? 'rgba(255,255,255,0.15)' : phase.color + '20'};
                                color:${isSel ? '#fff' : phase.color};">
                        <i class="fa-solid ${phase.icon}"></i>
                    </div>
                    <div class="phase-card-info">
                        <p class="phase-card-title">${phase.title}</p>
                        <div class="phase-card-badges">
                            <span class="phase-target-badge ${target.class}">
                                <i class="fa-solid ${target.icon}"></i>
                                ${target.label}
                            </span>
                            ${phase.is_archived ? `
                                <span class="phase-archived-badge">
                                    <i class="fa-solid fa-box-archive"></i> Archived
                                </span>
                            ` : ''}
                        </div>
                        <p class="phase-card-meta">${phase.sections.length} sections · ${totalQ} questions</p>
                    </div>
                </div>

                <div class="phase-card-toolbar">
                    <button class="phase-action-btn" title="Edit" onclick="event.stopPropagation(); openPhaseModal(${phase.id})">
                        <i class="fa-solid fa-pen"></i>
                        <span>Edit</span>
                    </button>
                    <button class="phase-action-btn" title="Duplicate" onclick="event.stopPropagation(); duplicatePhase(${phase.id})">
                        <i class="fa-solid fa-copy"></i>
                        <span>Copy</span>
                    </button>
                    <button class="phase-action-btn" title="${phase.is_archived ? 'Unarchive' : 'Archive'}"
                            onclick="event.stopPropagation(); togglePhaseArchive(${phase.id})">
                        <i class="fa-solid ${phase.is_archived ? 'fa-box-open' : 'fa-box-archive'}"></i>
                        <span>${phase.is_archived ? 'Unarchive' : 'Archive'}</span>
                    </button>
                    <button class="phase-action-btn danger" title="Delete" onclick="event.stopPropagation(); deletePhase(${phase.id})">
                        <i class="fa-solid fa-trash"></i>
                        <span>Delete</span>
                    </button>
                </div>
            </div>
        `;
    }).join('');

    }

    function selectPhase(id) {
        selectedPhaseId = id;
        renderBuilder();
    }

    function toggleSection(sectionId) {
        const id = Number(sectionId);
        if (expandedSections.has(id)) {
            expandedSections.delete(id);
        } else {
            expandedSections.add(id);
        }
        renderBuilder();
    }

    // ============================================
    // BUILDER - DELETE FUNCTIONS
    // ============================================

    async function deletePhase(id) {
        showWarningModal({
            title: 'Delete Phase',
            message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you sure you want to <strong>delete</strong> this phase?<br><small>All sections, questions, and associated data will be permanently removed.</small>',
            iconType: 'danger',
            confirmText: 'Delete Phase',
            confirmClass: 'btn-danger',
            onConfirm: async function() {
                showLoading('Deleting phase...', 'Please wait');
                
                try {
                    const response = await fetch(`${API_BASE}/phase/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });

                    if (!response.ok) {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to delete phase');
                    }

                    phases = phases.filter(p => p.id !== id);
                    if (selectedPhaseId === id) {
                        selectedPhaseId = phases.length > 0 ? phases[0].id : null;
                    }
                    
                    hideLoading();
                    showToast('Phase deleted successfully!', 'success');
                    renderBuilder();
                } catch (error) {
                    hideLoading();
                    console.error('❌ Failed to delete phase:', error);
                    showToast('Failed to delete phase: ' + error.message, 'error');
                }
            }
        });
    }

    async function deleteSection(phaseId, secId) {
        showWarningModal({
            title: 'Delete Section',
            message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you sure you want to <strong>delete</strong> this section?<br><small>All questions and associated data within this section will be permanently removed.</small>',
            iconType: 'danger',
            confirmText: 'Delete Section',
            confirmClass: 'btn-danger',
            onConfirm: async function() {
                showLoading('Deleting section...', 'Please wait');
                
                try {
                    const response = await fetch(`${API_BASE}/section/${secId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });

                    if (!response.ok) {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to delete section');
                    }

                    let found = false;
                    phases = phases.map(p => {
                        if (Number(p.id) !== Number(phaseId)) return p;
                        const filteredSections = p.sections.filter(s => Number(s.id) !== Number(secId));
                        if (filteredSections.length < p.sections.length) {
                            found = true;
                        }
                        return { ...p, sections: filteredSections };
                    });
                    
                    hideLoading();
                    
                    if (found) {
                        showToast('Section deleted successfully!', 'success');
                        expandedSections.delete(Number(secId));
                        renderBuilder();
                    } else {
                        await loadPhases();
                    }
                } catch (error) {
                    hideLoading();
                    console.error('❌ Failed to delete section:', error);
                    showToast('Failed to delete section: ' + error.message, 'error');
                }
            }
        });
    }

    async function deleteQuestion(phaseId, secId, qId) {
        showWarningModal({
            title: 'Delete Question',
            message: '<strong style="color: #ef4444;">⚠️ Warning: This action cannot be undone!</strong><br><br>Are you sure you want to <strong>delete</strong> this question?<br><small>All associated options and data will be permanently removed.</small>',
            iconType: 'danger',
            confirmText: 'Delete Question',
            confirmClass: 'btn-danger',
            onConfirm: async function() {
                showLoading('Deleting question...', 'Please wait');
                
                try {
                    const response = await fetch(`${API_BASE}/question/${qId}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        },
                    });

                    if (!response.ok) {
                        const error = await response.json();
                        throw new Error(error.message || 'Failed to delete question');
                    }

                    let found = false;
                    phases = phases.map(p => {
                        if (Number(p.id) !== Number(phaseId)) return p;
                        return {
                            ...p,
                            sections: p.sections.map(s => {
                                if (Number(s.id) !== Number(secId)) return s;
                                const filteredQuestions = s.questions.filter(q => Number(q.id) !== Number(qId));
                                if (filteredQuestions.length < s.questions.length) {
                                    found = true;
                                }
                                return { ...s, questions: filteredQuestions };
                            })
                        };
                    });
                    
                    hideLoading();
                    
                    if (found) {
                        showToast('Question deleted successfully!', 'success');
                        renderBuilder();
                    } else {
                        await loadPhases();
                    }
                } catch (error) {
                    hideLoading();
                    console.error('❌ Failed to delete question:', error);
                    showToast('Failed to delete question: ' + error.message, 'error');
                }
            }
        });
    }

    // ═══════════════════════════════════════
// CHARACTER COUNTER
// ═══════════════════════════════════════

/**
 * Attach a live character counter to an input/textarea.
 * The input must have a `data-maxlength` attribute.
 * 
 * Usage:
 *   <input type="text" id="phaseTitle" data-maxlength="255">
 *   attachCharCounter(document.getElementById('phaseTitle'));
 * 
 * Or use `autoAttachCharCounters()` to auto-scan the DOM.
 */
function attachCharCounter(input) {
    if (!input || input._charCounterAttached) return;
    input._charCounterAttached = true;

    const maxLength = parseInt(input.dataset.maxlength, 10);
    if (!maxLength || maxLength <= 0) return;

    // Enforce hard limit at the browser level (prevents paste overflow)
    input.setAttribute('maxlength', maxLength);

    // Build the counter DOM
    const wrapper = document.createElement('div');
    wrapper.className = 'char-counter-wrapper pristine';
    wrapper.innerHTML = `
        <div class="char-counter">
            <span class="char-counter-text">
                <i class="fa-solid fa-keyboard"></i>
                <span class="char-counter-current">0</span>
                <span style="opacity:0.5;">/</span>
                <span class="char-counter-max">${maxLength}</span>
            </span>
            <span class="char-counter-bar">
                <span class="char-counter-bar-fill"></span>
            </span>
        </div>
    `;

    // Insert right after the input
    input.parentNode.insertBefore(wrapper, input.nextSibling);

    const currentEl = wrapper.querySelector('.char-counter-current');
    const barFill = wrapper.querySelector('.char-counter-bar-fill');
    const icon = wrapper.querySelector('.char-counter-text i');

    const updateCounter = () => {
        const len = input.value.length;
        const pct = Math.min((len / maxLength) * 100, 100);

        currentEl.textContent = len;
        barFill.style.width = pct + '%';

        // State classes
        wrapper.classList.remove('pristine', 'warning', 'danger');

        if (len === 0) {
            wrapper.classList.add('pristine');
        } else if (len >= maxLength) {
            wrapper.classList.add('danger');
        } else if (pct >= 90) {
            wrapper.classList.add('warning');
        }

        // Icon swap for extra polish
        if (len === 0) {
            icon.className = 'fa-solid fa-keyboard';
        } else if (len >= maxLength) {
            icon.className = 'fa-solid fa-circle-exclamation';
        } else if (pct >= 90) {
            icon.className = 'fa-solid fa-triangle-exclamation';
        } else {
            icon.className = 'fa-solid fa-keyboard';
        }
    };

    // Live update on input
    input.addEventListener('input', updateCounter);

    // Shake when trying to exceed (paste or programmatic set)
    input.addEventListener('beforeinput', (e) => {
        if (e.inputType === 'insertText' || e.inputType === 'insertFromPaste') {
            const data = e.data || (e.dataTransfer && e.dataTransfer.getData('text')) || '';
            if (input.value.length + data.length > maxLength) {
                wrapper.classList.add('shake');
                setTimeout(() => wrapper.classList.remove('shake'), 400);
            }
        }
    });

    // Prevent paste overflow (belt and suspenders)
    input.addEventListener('paste', (e) => {
        const pasted = (e.clipboardData || window.clipboardData).getData('text');
        if (input.value.length + pasted.length > maxLength) {
            e.preventDefault();

            // Truncate to fit
            const remaining = maxLength - input.value.length;
            const truncated = pasted.slice(0, remaining);
            const start = input.selectionStart;
            const end = input.selectionEnd;

            input.value = input.value.slice(0, start) + truncated + input.value.slice(end);

            wrapper.classList.add('shake', 'danger');
            setTimeout(() => wrapper.classList.remove('shake'), 400);
            updateCounter();
        }
    });

    // Initial paint
    updateCounter();
}

/**
 * Auto-attach counters to every input/textarea
 * that has a `data-maxlength` attribute.
 */
function autoAttachCharCounters(root = document) {
    root.querySelectorAll('input[data-maxlength], textarea[data-maxlength]')
        .forEach(attachCharCounter);
}

// Auto-run on page load
document.addEventListener('DOMContentLoaded', () => {
    autoAttachCharCounters();
});

// ═══════════════════════════════════════
// DRAG & DROP REORDER — QUESTIONS
// ═══════════════════════════════════════

/**
 * Attach Sortable.js to every .questions-list in the builder.
 * Reorders questions within the same section only.
 * Persists order via savePhasesToBackend().
 */
function initQuestionSortables() {
    if (typeof Sortable === 'undefined') {
        console.warn('⚠️ Sortable.js not loaded, skipping question sortables');
        return;
    }

    document.querySelectorAll('.questions-list').forEach(list => {
        // Prevent double-init
        if (list._sortableInitialized) return;
        list._sortableInitialized = true;

        new Sortable(list, {
            handle: '.question-drag',
            animation: 180,
            ghostClass: 'question-ghost',
            chosenClass: 'question-chosen',
            dragClass: 'question-dragging',
            // Snap to sibling positions for smoother feel
            fallbackTolerance: 3,

            onEnd: function(evt) {
                const sectionCard = evt.item.closest('.section-card');
                if (!sectionCard) return;

                const phaseId  = Number(sectionCard.dataset.phaseId);
                const sectionId = Number(sectionCard.dataset.sectionId);
                if (!phaseId || !sectionId) return;

                // Find the phase + section in state
                const phase = phases.find(p => Number(p.id) === phaseId);
                if (!phase) return;
                const section = phase.sections.find(s => Number(s.id) === sectionId);
                if (!section) return;

                // Move the question in state to match the new DOM order
                const [movedQuestion] = section.questions.splice(evt.oldIndex, 1);
                section.questions.splice(evt.newIndex, 0, movedQuestion);

                console.log('🔄 Reordered questions in section', sectionId, ':', 
                    evt.oldIndex, '→', evt.newIndex);

                // Persist — this will hit the diff-based backend
                savePhasesToBackend();
            }
        });
    });
}

</script>

{{-- This is the views/alumni_tracer/partials/_builder.blade.php --}}