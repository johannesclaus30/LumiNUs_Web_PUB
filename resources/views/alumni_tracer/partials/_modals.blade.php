<!-- ============================================ -->
<!-- QUESTION MODAL -->
<!-- ============================================ -->
<div class="modal-overlay" id="questionModal">
    <div class="modal-dialog" style="max-width: 700px;">
        <div class="modal-header">
            <h3 id="questionModalTitle">Add New Question</h3>
            <button class="modal-close" onclick="closeQuestionModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <!-- Question Label -->
            <div class="form-group">
                <label>Question Label <span class="required">*</span></label>
                <textarea class="form-control" id="qLabel" rows="2" placeholder="Enter the question text..."></textarea>
            </div>

            <!-- Question Type -->
            <div class="form-group">
                <label>Question Type</label>
                <select class="form-control" id="qType" onchange="handleTypeChange()">
                    <option value="short_answer">Short Answer</option>
                    <option value="paragraph">Paragraph</option>
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="checkboxes">Checkboxes (Multiple Selection)</option>
                    <option value="dropdown">Dropdown</option>
                    <option value="likert_scale">Likert Scale</option>
                    <option value="multiple_choice_grid">Multiple Choice Grid</option>
                </select>
                <p class="help-text" id="typeHelp">Short single-line text input.</p>
            </div>

            <!-- Options for Multiple Choice / Checkboxes / Dropdown -->
            <div class="form-group" id="optionsGroup" style="display:none;">
                <label>Answer Options</label>
                <div id="optionsList" class="options-list"></div>
                <div class="options-input-row">
                    <input type="text" class="form-control" id="newOption" placeholder="Type an option and press Enter..." onkeydown="handleOptionKeydown(event)">
                    <button class="btn btn-primary" onclick="addOption()" style="white-space:nowrap;">Add</button>
                </div>
            </div>

            <!-- "Allow Other" toggle — only shows for choice types -->
            <div class="form-group" id="otherOptionGroup" style="display:none;">
                <div class="toggle-row" style="background: #F9FAFB; padding: 0.875rem 1rem; border-radius: 0.625rem; border: 1px solid #E5E7EB;">
                    <div>
                        <p class="toggle-label" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-circle-plus" style="color: #3b82f6; font-size: 0.875rem;"></i>
                            Allow "Other" option
                        </p>
                        <p class="toggle-sub">Adds an "Other" choice with a free-text field for alumni</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="qAllowOther">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Placeholder for Short Answer / Paragraph -->
            <div class="form-group" id="placeholderGroup">
                <label>Placeholder Text</label>
                <input type="text" class="form-control" id="qPlaceholder" placeholder="e.g. Enter your answer here...">
            </div>

            <!-- Likert Scale / Multiple Choice Grid Settings -->
            <div class="form-group" id="gridSettingsGroup" style="display:none;">
                <!-- Rows (Statements) -->
                <div class="form-group">
                    <label>Statements (Rows)</label>
                    <div id="gridRowsList" class="options-list"></div>
                    <div class="options-input-row">
                        <input type="text" class="form-control" id="newGridRow" placeholder="Type a statement and press Enter..." onkeydown="handleGridRowKeydown(event)">
                        <button class="btn btn-primary" onclick="addGridRow()" style="white-space:nowrap;">Add Row</button>
                    </div>
                </div>

                <!-- Columns (Options/Scale) -->
                <div class="form-group">
                    <label id="gridColumnsLabel">Scale Options (Columns)</label>
                    <div id="gridColumnsList" class="options-list"></div>
                    <div class="options-input-row">
                        <input type="text" class="form-control" id="newGridColumn" placeholder="Type an option and press Enter..." onkeydown="handleGridColumnKeydown(event)">
                        <button class="btn btn-primary" onclick="addGridColumn()" style="white-space:nowrap;">Add Column</button>
                    </div>
                </div>
            </div>

            <!-- Required Toggle -->
            <div class="toggle-row">
                <div>
                    <p class="toggle-label">Required Question</p>
                    <p class="toggle-sub">Alumni must answer before submitting</p>
                </div>
                <label class="toggle-switch">
                    <input type="checkbox" id="qRequired" checked>
                    <span class="toggle-slider"></span>
                </label>
            </div>

            <!-- Conditional Logic (Branching) -->
            <div class="form-group" id="conditionGroup" style="margin-top: 1rem;">
                <!-- Toggle Row -->
                <div class="toggle-row" style="background: #F9FAFB; padding: 0.875rem 1rem; border-radius: 0.625rem; border: 1px solid #E5E7EB;">
                    <div>
                        <p class="toggle-label" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-code-branch" style="color: #8b5cf6; font-size: 0.875rem;"></i>
                            Show only if another question was answered
                        </p>
                        <p class="toggle-sub">Creates a branching path — this question appears conditionally</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="qHasCondition" onchange="handleConditionToggle()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <!-- Condition Editor (hidden until toggle is ON) -->
                <div id="conditionEditor" style="display: none; margin-top: 1rem; padding: 1rem; background: #F5F3FF; border: 1px solid #DDD6FE; border-radius: 0.75rem;">
                    <!-- Parent question selector -->
                    <div class="form-group">
                        <label style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #6D28D9;">
                            Show this question when:
                        </label>
                        <select class="form-control" id="conditionParentQuestion" onchange="handleConditionParentChange()">
                            <option value="">-- Select a question --</option>
                            <!-- Populated by JS -->
                        </select>
                        <p class="help-text" style="margin-top: 0.375rem;">
                            Only dropdown and multiple-choice questions can be parents.
                        </p>
                    </div>

                    <!-- Match type -->
                    <div class="form-group" id="conditionMatchTypeGroup">
                        <label style="font-size: 0.75rem;">Match Type</label>
                        <select class="form-control" id="conditionMatchType">
                            <option value="any">Answer is ANY of the following</option>
                            <option value="all">Answer is ALL of the following</option>
                            <option value="none">Answer is NONE of the following</option>
                        </select>
                    </div>

                    <!-- Matching option labels -->
                    <div class="form-group" id="conditionOptionsGroup" style="display: none;">
                        <label style="font-size: 0.75rem;">
                            <i class="fa-solid fa-check-circle" style="color: #10b981;"></i>
                            Matching answer(s)
                        </label>
                        <div id="conditionOptionsList" class="options-list">
                            <!-- Populated by JS with checkboxes of the parent's options -->
                        </div>
                    </div>

                    <p class="help-text" style="margin-top: 0.5rem;">
                        <i class="fa-solid fa-lightbulb" style="color: #F59E0B;"></i>
                        This question stays hidden until the alumni answers the question above with a matching value.
                    </p>
                </div>
            </div>

            <!-- ═══════════════════════════════════════ -->
            <!-- INSTITUTIONAL ANALYTICS MAPPING        -->
            <!-- ═══════════════════════════════════════ -->
            <div class="form-group" id="analyticsMappingGroup" style="margin-top: 1rem;">
                <div class="toggle-row" style="background: #EFF6FF; padding: 0.875rem 1rem; border-radius: 0.625rem; border: 1px solid #BFDBFE;">
                    <div>
                        <p class="toggle-label" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-chart-column" style="color: #2563EB; font-size: 0.875rem;"></i>
                            Map to Institutional Indicator
                        </p>
                        <p class="toggle-sub">Enables this question to feed the Institutional Analytics tab</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="qHasAnalyticsMapping" onchange="handleAnalyticsMappingToggle()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div id="analyticsMappingEditor" style="display:none; margin-top: 1rem; padding: 1rem; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 0.75rem;">
                    <div class="form-group">
                        <label style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #1D4ED8;">
                            Indicator
                        </label>
                        <select class="form-control" id="analyticsIndicatorSelect" onchange="renderAnalyticsOptionMaps()">
                            <option value="">-- Select an indicator --</option>
                        </select>
                        <p class="help-text" style="margin-top: 0.375rem;" id="analyticsIndicatorHelp"></p>
                    </div>

                    <div class="form-group" id="analyticsOptionMapGroup" style="display:none;">
                        <label style="font-size: 0.75rem;">Map each option to a canonical value</label>
                        <div id="analyticsOptionMapList"></div>
                    </div>

                    <p class="help-text" style="margin-top: 0.5rem;">
                        <i class="fa-solid fa-lock" style="color: #6B7280;"></i>
                        This does <strong>not</strong> change the options alumni see. It only tags them internally for analytics.
                    </p>
                </div>
            </div>

            <!-- ═══════════════════════════════════════ -->
            <!-- ADVANCED: ALIGNMENT RULES              -->
            <!-- ═══════════════════════════════════════ -->
            <div class="form-group" id="advancedRulesGroup" style="margin-top: 1rem;">
                <div class="toggle-row" style="background: #F0FDF4; padding: 0.875rem 1rem; border-radius: 0.625rem; border: 1px solid #BBF7D0;">
                    <div>
                        <p class="toggle-label" style="display: flex; align-items: center; gap: 0.5rem;">
                            <i class="fa-solid fa-wand-magic-sparkles" style="color: #16A34A; font-size: 0.875rem;"></i>
                            <span>Advanced: Alignment Rules</span>
                        </p>
                        <p class="toggle-sub">Define IF-THEN rules combining two questions — powers the alignment trend chart</p>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" id="qShowAdvancedRules" onchange="toggleAdvancedRulesPanel()">
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div id="advancedRulesPanel" style="display:none; margin-top: 1rem;">
                    <!-- Header with Add button -->
                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 1rem;">
                        <p style="font-size: 0.8125rem; color: #6B7280; margin: 0;">
                            Each rule categorizes alumni who match <strong>both</strong> conditions.
                        </p>
                        <button type="button" class="btn btn-primary" onclick="addNewRuleCard()" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
                            <i class="fa-solid fa-plus"></i> Add Rule
                        </button>
                    </div>

                    <!-- Rules list (populated by JS) -->
                    <div id="rulesListContainer" style="display:flex; flex-direction:column; gap: 0.75rem;">
                        <!-- Filled by JS -->
                    </div>

                    <p class="help-text" style="margin-top: 0.75rem;">
                        <i class="fa-solid fa-circle-info" style="color: #6B7280;"></i>
                        Rules are saved separately from the question. They apply across the whole tracer.
                    </p>
                </div>
            </div>

        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeQuestionModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveQuestion()">Save Question</button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- PHASE MODAL -->
<!-- ============================================ -->
<div class="modal-overlay" id="phaseModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="phaseModalTitle">Add New Phase</h3>
            <button class="modal-close" onclick="closePhaseModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Phase Title <span class="required">*</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="phaseTitle" 
                    data-maxlength="255"
                    placeholder="e.g. Personal Profile"
                >
            </div>
            <div class="form-group">
                <label>Subtitle <span class="optional">(optional)</span></label>
                <input 
                    type="text" 
                    class="form-control" 
                    id="phaseSubtitle" 
                    data-maxlength="1000"
                    placeholder="e.g. Basic & contact info"
                >
            </div>
            
            <!-- Target Alumni Type Selector -->
            <div class="form-group">
                <label>Target Alumni Type</label>
                <div class="alumni-type-selector">
                    <label class="type-option selected" data-type="all" onclick="selectAlumniType('all')">
                        <input type="radio" name="target_alumni_type" value="all" checked>
                        <div class="type-option-content">
                            <i class="fa-solid fa-users"></i>
                            <span>All Alumni</span>
                            <small>Visible to everyone</small>
                        </div>
                    </label>
                    <label class="type-option" data-type="shs" onclick="selectAlumniType('shs')">
                        <input type="radio" name="target_alumni_type" value="shs">
                        <div class="type-option-content">
                            <i class="fa-solid fa-school"></i>
                            <span>SHS Only</span>
                            <small>Senior High School only</small>
                        </div>
                    </label>
                    <label class="type-option" data-type="college" onclick="selectAlumniType('college')">
                        <input type="radio" name="target_alumni_type" value="college">
                        <div class="type-option-content">
                            <i class="fa-solid fa-graduation-cap"></i>
                            <span>College Only</span>
                            <small>College graduates only</small>
                        </div>
                    </label>
                </div>
                <p class="help-text">Select which alumni group can see this phase</p>
            </div>
            
            <div class="form-group">
                <label>Icon</label>
                <div class="icon-grid" id="iconGrid"></div>
            </div>
            <div class="form-group">
                <label>Accent Color</label>
                <div class="color-grid" id="colorGrid"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closePhaseModal()">Cancel</button>
            <button class="btn btn-primary" onclick="savePhase()">Save Phase</button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SECTION MODAL -->
<!-- ============================================ -->
<div class="modal-overlay" id="sectionModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <h3 id="sectionModalTitle">Add New Section</h3>
            <button class="modal-close" onclick="closeSectionModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Section Title <span class="required">*</span></label>
                <input type="text" class="form-control" id="sectionTitle" placeholder="e.g. Basic Information">
            </div>
            <div class="form-group">
                <label>Description</label>
                <input type="text" class="form-control" id="sectionDesc" placeholder="Brief description of this section">
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeSectionModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveSection()">Save Section</button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- CHART BUILDER MODAL (Updated) -->
<!-- ============================================ -->
<div class="modal-overlay" id="chartBuilderModal">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-header">
            <h3 id="chartBuilderModalTitle">Add Analytics Chart</h3>
            <button class="modal-close" onclick="closeChartBuilderModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Chart Title <span class="required">*</span></label>
                <input type="text" class="form-control" id="chartTitle" placeholder="e.g. Employment Status Distribution">
            </div>

            <div class="form-group">
                <label>Chart Type</label>
                <select class="form-control" id="chartType" onchange="handleChartTypeChange()">
                    <option value="bar">Bar Chart</option>
                    <option value="pie">Pie Chart</option>
                    <option value="horizontal_bar">Horizontal Bar Chart</option>
                    <option value="doughnut">Doughnut Chart</option>
                </select>
            </div>

            <div class="form-group">
                <label>Select Question to Visualize <span class="required">*</span></label>
                <select class="form-control" id="chartQuestion" onchange="handleChartQuestionChange()">
                    <option value="">-- Select a question --</option>
                    <!-- Populated dynamically -->
                </select>
                <p class="help-text" id="chartQuestionHelp">Choose a multiple choice, checkbox, dropdown, or grid question.</p>
            </div>

            <div class="form-group" id="chartPhaseFilterGroup">
                <label>Filter by Phase (optional)</label>
                <select class="form-control" id="chartPhaseFilter">
                    <option value="">All Phases</option>
                    <!-- Populated dynamically -->
                </select>
            </div>

            <div class="form-group" id="chartColorGroup">
                <label>Color Scheme</label>
                <div class="color-scheme-grid" id="colorSchemeGrid">
                    <button class="color-scheme-option selected" data-scheme="default" onclick="selectColorScheme('default', this)">
                        <span style="background:#3b82f6;"></span>
                        <span style="background:#10b981;"></span>
                        <span style="background:#f59e0b;"></span>
                        <span style="background:#8b5cf6;"></span>
                        <span style="background:#ef4444;"></span>
                    </button>
                    <button class="color-scheme-option" data-scheme="ocean" onclick="selectColorScheme('ocean', this)">
                        <span style="background:#06b6d4;"></span>
                        <span style="background:#0ea5e9;"></span>
                        <span style="background:#3b82f6;"></span>
                        <span style="background:#6366f1;"></span>
                        <span style="background:#8b5cf6;"></span>
                    </button>
                    <button class="color-scheme-option" data-scheme="forest" onclick="selectColorScheme('forest', this)">
                        <span style="background:#22c55e;"></span>
                        <span style="background:#10b981;"></span>
                        <span style="background:#059669;"></span>
                        <span style="background:#047857;"></span>
                        <span style="background:#065f46;"></span>
                    </button>
                    <button class="color-scheme-option" data-scheme="sunset" onclick="selectColorScheme('sunset', this)">
                        <span style="background:#f97316;"></span>
                        <span style="background:#ef4444;"></span>
                        <span style="background:#ec4899;"></span>
                        <span style="background:#a855f7;"></span>
                        <span style="background:#6366f1;"></span>
                    </button>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeChartBuilderModal()">Cancel</button>
            <button class="btn btn-primary" onclick="saveChart()">Add Chart</button>
        </div>
    </div>
</div>

<!-- ============================================ -->
<!-- SEND REMINDERS MODAL -->
<!-- ============================================ -->
<div class="modal-overlay" id="reminderModal">
    <div class="modal-dialog" style="max-width: 850px;">
        <div class="modal-header" style="border-bottom: 2px solid #F3F4F6;">
            <div>
                <h3 id="reminderModalTitle" style="display: flex; align-items: center; gap: 0.625rem;">
                    <i class="fa-solid fa-paper-plane" style="color: #3b82f6; font-size: 1.25rem;"></i>
                    Send Tracer Reminders
                </h3>
                <p style="font-size: 0.8125rem; color: var(--gray-500); margin-top: 0.25rem;" id="reminderStats">
                    Loading alumni data...
                </p>
            </div>
            <button class="modal-close" onclick="closeReminderModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="modal-body" style="padding: 0;">
            <!-- Stats Summary Bar -->
            <div class="reminder-stats-bar" id="reminderStatsBar">
                <div class="reminder-stat-item">
                    <span class="reminder-stat-value" id="statTotal">0</span>
                    <span class="reminder-stat-label">Total Alumni</span>
                </div>
                <div class="reminder-stat-item completed">
                    <span class="reminder-stat-value" id="statDone">0</span>
                    <span class="reminder-stat-label">Completed</span>
                </div>
                <div class="reminder-stat-item in-progress">
                    <span class="reminder-stat-value" id="statProgress">0</span>
                    <span class="reminder-stat-label">In Progress</span>
                </div>
                <div class="reminder-stat-item pending">
                    <span class="reminder-stat-value" id="statNotStarted">0</span>
                    <span class="reminder-stat-label">Not Started</span>
                </div>
            </div>
            
            <!-- Toolbar -->
            <div class="reminder-toolbar">
                <div class="reminder-search">
                    <i class="fa-solid fa-search"></i>
                    <input type="text" id="reminderSearch" placeholder="Search alumni by name, email, or program..." oninput="filterReminderAlumni()">
                </div>
                <div class="reminder-toolbar-actions">
                    <label class="reminder-checkbox-select-all">
                        <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        <span>Select All</span>
                    </label>
                    <button class="btn btn-primary btn-send-selected" id="btnSendSelected" onclick="sendBulkReminders()" disabled>
                        <i class="fa-solid fa-paper-plane"></i> Send to Selected (<span id="selectedCount">0</span>)
                    </button>
                </div>
            </div>
            
            <!-- Alumni List -->
            <div class="reminder-alumni-list" id="reminderAlumniList">
                <!-- Loading skeleton -->
                <div class="reminder-loading">
                    <div class="loading-spinner"></div>
                    <p>Loading alumni list...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Warning Modal -->
<div id="warningModal" class="warning-modal-overlay">
    <div class="warning-modal">
        <div class="warning-modal-icon" id="warningModalIcon">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
        <div class="warning-modal-content">
            <h3 class="warning-modal-title" id="warningModalTitle">Confirm Action</h3>
            <p class="warning-modal-message" id="warningModalMessage">Are you sure you want to proceed with this action?</p>
        </div>
        <div class="warning-modal-actions">
            <button class="btn btn-secondary" id="warningModalCancel">
                <i class="fa-solid fa-xmark"></i> Cancel
            </button>
            <button class="btn btn-danger" id="warningModalConfirm">
                <i class="fa-solid fa-check"></i> Confirm
            </button>
        </div>
    </div>
</div>



{{-- This is the _modals.blade.php file --}}