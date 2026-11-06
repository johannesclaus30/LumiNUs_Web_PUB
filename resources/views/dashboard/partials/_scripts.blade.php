<script>
// ========== MOBILE MENU TOGGLE ==========
function toggleMobileMenu() {
    const sidebar = document.getElementById('adminSidebar');
    const overlay = document.getElementById('mobileOverlay');
    sidebar.classList.toggle('mobile-open');
    overlay.classList.toggle('active');
    document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
}

document.querySelectorAll('.nav-item').forEach(item => {
    item.addEventListener('click', function() {
        if (window.innerWidth <= 1024) {
            toggleMobileMenu();
        }
    });
});

// ============================================
// CUSTOM CONFIRM & ALERT MODALS (directory-style)
// ============================================

function showConfirmModal(opts) {
    return new Promise((resolve) => {
        const {
            title = 'Confirm Action',
            message = 'Are you sure?',
            confirmText = 'Confirm',
            cancelText = 'Cancel',
            confirmClass = 'btn-primary',
            icon = 'fa-circle-question',
            iconColor = '#3B82F6',
            iconBg = null,
        } = opts || {};

        const modalId = 'customConfirmModal';
        document.getElementById(modalId)?.remove();

        const bg = iconBg || (iconColor + '26');

        const html = `
            <div id="${modalId}" class="modal-overlay active" style="z-index:100000;">
                <div class="modal-content-wrapper" style="max-width:450px;">
                    <div class="confirm-modal-card">
                        <div class="confirm-icon-wrapper" style="background:${bg}; color:${iconColor};">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <h3 class="confirm-title">${escapeHtml(title)}</h3>
                        <p class="confirm-message">${escapeHtml(message)}</p>
                        <div class="confirm-actions">
                            <button type="button" class="btn btn-secondary" data-action="cancel">
                                ${escapeHtml(cancelText)}
                            </button>
                            <button type="button" class="btn ${confirmClass}" data-action="confirm">
                                ${escapeHtml(confirmText)}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        document.body.style.overflow = 'hidden';

        const modal = document.getElementById(modalId);

        const cleanup = () => {
            modal.classList.remove('active');
            setTimeout(() => modal.remove(), 200);
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKey);
        };

        const handle = (result) => {
            cleanup();
            resolve(result);
        };

        modal.querySelectorAll('[data-action="cancel"]').forEach(el => {
            el.addEventListener('click', () => handle(false));
        });
        modal.querySelector('[data-action="confirm"]').addEventListener('click', () => handle(true));

        modal.addEventListener('click', (e) => {
            if (e.target === modal) handle(false);
        });

        const onKey = (e) => {
            if (e.key === 'Escape') handle(false);
            if (e.key === 'Enter') handle(true);
        };
        document.addEventListener('keydown', onKey);

        setTimeout(() => modal.querySelector('[data-action="confirm"]')?.focus(), 100);
    });
}

function showAlertModal(opts) {
    return new Promise((resolve) => {
        const {
            title = 'Notice',
            message = '',
            okText = 'OK',
            icon = 'fa-circle-info',
            iconColor = '#3B82F6',
            iconBg = null,
        } = opts || {};

        const modalId = 'customAlertModal';
        document.getElementById(modalId)?.remove();

        const bg = iconBg || (iconColor + '26');

        const html = `
            <div id="${modalId}" class="modal-overlay active" style="z-index:100000;">
                <div class="modal-content-wrapper" style="max-width:450px;">
                    <div class="confirm-modal-card">
                        <div class="confirm-icon-wrapper" style="background:${bg}; color:${iconColor};">
                            <i class="fa-solid ${icon}"></i>
                        </div>
                        <h3 class="confirm-title">${escapeHtml(title)}</h3>
                        <p class="confirm-message">${escapeHtml(message)}</p>
                        <div class="confirm-actions">
                            <button type="button" class="btn btn-primary" data-action="ok">
                                ${escapeHtml(okText)}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
        document.body.style.overflow = 'hidden';

        const modal = document.getElementById(modalId);

        const cleanup = () => {
            modal.classList.remove('active');
            setTimeout(() => modal.remove(), 200);
            document.body.style.overflow = '';
            document.removeEventListener('keydown', onKey);
        };

        const handle = () => {
            cleanup();
            resolve();
        };

        modal.querySelectorAll('[data-action="ok"]').forEach(el => {
            el.addEventListener('click', handle);
        });

        modal.addEventListener('click', (e) => {
            if (e.target === modal) handle();
        });

        const onKey = (e) => {
            if (e.key === 'Escape' || e.key === 'Enter') handle();
        };
        document.addEventListener('keydown', onKey);

        setTimeout(() => modal.querySelector('[data-action="ok"]')?.focus(), 100);
    });
}

function showErrorModal(message, title = 'Error') {
    return showAlertModal({
        title,
        message,
        icon: 'fa-circle-exclamation',
        iconColor: '#EF4444',
        iconBg: '#FEE2E2',
    });
}

function showSuccessModal(message, title = 'Success') {
    return showAlertModal({
        title,
        message,
        icon: 'fa-circle-check',
        iconColor: '#10B981',
        iconBg: '#D1FAE5',
    });
}

// ========== TAB NAVIGATION ==========
function showTab(tabName) {
    // Store the current tab in sessionStorage
    sessionStorage.setItem('currentTab', tabName);
    
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Deselect all buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show the selected tab
    const targetTab = document.getElementById('tab-' + tabName);
    if (targetTab) {
        targetTab.classList.add('active');
    }
    
    // Highlight the clicked button
    const clickedBtn = document.querySelector(`.tab-btn[onclick*="${tabName}"]`);
    if (clickedBtn) {
        clickedBtn.classList.add('active');
    }

    // Refresh charts and handle map
    setTimeout(() => {
        // Refresh charts (safely)
        const chartsObj = window._charts || {};
        Object.values(chartsObj).forEach(chart => {
            if (chart && chart.resize) {
                try { chart.resize(); } catch(e) {}
            }
        });
        
                // 🔹 Ensure tab-specific charts are initialized for the tab being shown
        if (tabName === 'users' && typeof initUsersCharts === 'function') {
            initUsersCharts();
        } else if (tabName === 'tracer' && typeof initTracerCharts === 'function') {
            initTracerCharts();
        } else if (tabName === 'events' && typeof initEventsCharts === 'function') {
            initEventsCharts();
        }

        // Handle map initialization for users tab
        if (tabName === 'users') {
            let tries = 0;
            const tryInit = () => {
                const el = document.getElementById('alumniMap');
                if (el && isElementVisible(el)) {
                    if (!mapInitialized) {
                        initAlumniMap();
                    } else if (alumniMap) {
                        try { alumniMap.invalidateSize(); } catch(e) {}
                    }
                } else if (tries++ < 15) {
                    setTimeout(tryInit, 150);
                }
            };
            tryInit();
        }
    }, 200);
}

// ========== EXPORT FUNCTIONS ==========
async function exportData(tab, format) {
    let data = [];
    let title = '';

    switch (tab) {
        case 'users':
            title = 'User Analytics Report';
            if (format === 'pdf') {
                await exportUsersPDF();
                return;
            } else {
                exportUsersExcel();
                return;
            }

        case 'tracer':
            title = 'Tracer Analytics Report';
            data = [
                ['Metric', 'Value'],
                ['Total Responses', '{{ $totalTracerResponses }}'],
                ['Completed', '{{ $completedTracerResponses }}'],
                ['In Progress', '{{ $inProgressTracerResponses }}'],
                ['Completion Rate', '{{ $tracerCompletionRate }}%'],
            ];
            break;

        case 'events':
            title = 'Event Analytics Report';
            if (format === 'pdf') {
                await exportEventsPDF();
                return;
            } else {
                exportEventsExcel();
                return;
            }

        // ============================================
        // ✨ MENTORSHIP EXPORT
        // ============================================
        case 'mentorship':
            title = 'Mentorship Applications Report';

            const header = [
                '#',
                'Alumni Name',
                'Email',
                'Program',
                'Event Type',
                'Event Title',
                'Target Audience',
                'Expected Capacity',
                'Proposed Date',
                'Start Time',
                'End Time',
                'Format',
                'Co-Speakers',
                'Description',
                'Status',
                'Submitted At',
            ];

            const rows = [
                @forelse($mentorships ?? [] as $m)
                [
                    '{{ $loop->iteration }}',
                    {!! json_encode($m->alumni ? $m->alumni->first_name . ' ' . $m->alumni->last_name : 'Unknown') !!},
                    {!! json_encode($m->alumni->email ?? 'N/A') !!},
                    {!! json_encode($m->alumni->program ?? 'N/A') !!},
                    {!! json_encode(ucfirst(str_replace('_', ' ', $m->event_type ?? 'N/A'))) !!},
                    {!! json_encode($m->event_title ?? 'N/A') !!},
                    {!! json_encode($m->target_audience ?? 'N/A') !!},
                    {!! json_encode($m->expected_capacity ?? 'N/A') !!},
                    {!! json_encode($m->proposed_date ?? 'N/A') !!},
                    {!! json_encode($m->start_time ?? 'N/A') !!},
                    {!! json_encode($m->end_time ?? 'N/A') !!},
                    {!! json_encode(ucfirst($m->event_format ?? 'N/A')) !!},
                    {!! json_encode($m->co_speakers ?? 'N/A') !!},
                    {!! json_encode(Str::limit($m->description ?? 'N/A', 120)) !!},
                    {!! json_encode(ucfirst($m->status ?? 'Unknown')) !!},
                    {!! json_encode($m->created_at ? $m->created_at->format('M d, Y g:i A') : 'N/A') !!},
                ],
                @empty
                // no rows
                @endforelse
            ];

            data = [header, ...rows];

            if (format === 'pdf') {
                await exportMentorshipPDF(data, title);
                return;
            } else {
                exportMentorshipExcel(data, title);
                return;
            }
    }

    // Default fallback for other tabs
    if (format === 'pdf') {
        exportPDF(data, title);
    } else {
        exportExcel(data, title);
    }
}

// ============================================
// ✨ TRACER ANALYTICS EXPORTERS
// ============================================

async function exportTracerData(format) {
    @if(isset($tracerAnalytics) && $tracerAnalytics['hasActiveForm'])
        if (format === 'pdf') {
            await exportTracerPDF();
        } else {
            exportTracerExcel();
        }
    @else
        if (typeof showToast === 'function') {
            showToast('No active tracer form — nothing to export.', 'warning');
        } else {
            alert('No active tracer form — nothing to export.');
        }
    @endif
}

async function exportTracerPDF() {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        showToast('PDF library not loaded. Please refresh the page.', 'error');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4'); // portrait

    const NU_NAVY = [50, 65, 140];
    const NU_GOLD = [251, 209, 23];

    const pageWidth  = 210;
    const pageHeight = 297;
    const marginX    = 12;

    // ---- Preload NU logo ----
    let nuLogoDataUrl = null;
    try {
        nuLogoDataUrl = await loadImageAsDataURL('/assets/logos/nu_logo_white.png');
    } catch (e) {
        console.warn('Could not load NU logo for PDF:', e);
    }

    // ============================================
    // 🎨 HEADER
    // ============================================
    doc.setFillColor(...NU_NAVY);
    doc.rect(0, 0, pageWidth, 26, 'F');
    doc.setFillColor(...NU_GOLD);
    doc.rect(0, 26, pageWidth, 1.5, 'F');

    let logoDrawn = false;
    if (nuLogoDataUrl) {
        try {
            doc.addImage(nuLogoDataUrl, 'PNG', marginX, 4, 18, 18, undefined, 'FAST');
            logoDrawn = true;
        } catch (e) {}
    }
    const textStartX = logoDrawn ? marginX + 22 : marginX;

    doc.setTextColor(255, 255, 255);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(15);
    doc.text('Tracer Response Analytics', textStartX, 12);

    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);
    doc.setTextColor(230, 235, 255);
    doc.text('LumiNUs Alumni Portal  •  National University Lipa', textStartX, 18.5);

    const generatedStr = 'Generated: ' + new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' });
    doc.setFontSize(8.5);
    doc.text(generatedStr, pageWidth - marginX, 18.5, { align: 'right' });

    // Active form name subtitle
    let y = 34;
    doc.setFontSize(9);
    doc.setFont(undefined, 'italic');
    doc.setTextColor(100, 100, 100);
    doc.text(
        'Active Form: ' + {!! json_encode($tracerAnalytics['activeForm']->form_title ?? 'N/A') !!},
        marginX, y
    );
    y += 6;

    // ============================================
    // 📊 KEY METRICS
    // ============================================
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(11);
    doc.text('KEY METRICS', marginX + 4, y);
    y += 6;

    const kpis = [
        ['Eligible Alumni',        '{{ number_format($tracerAnalytics["summary"]->total_eligible) }}'],
        ['Completed Responses',    '{{ number_format($tracerAnalytics["summary"]->total_completed) }}'],
        ['In Progress',            '{{ number_format($tracerAnalytics["summary"]->total_in_progress) }}'],
        ['Unique Responders',      '{{ number_format($tracerAnalytics["summary"]->unique_responders) }}'],
        ['Overall Completion Rate','{{ $tracerAnalytics["summary"]->overall_completion_rate }}%'],
        ['Overall Response Rate',  '{{ $tracerAnalytics["summary"]->overall_response_rate }}%'],
        ['Active Phases',          '{{ $tracerAnalytics["summary"]->total_phases }}'],
    ];

    doc.autoTable({
        body: kpis.map(([k, v]) => [k, v]),
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, textColor: [40,40,50], lineColor: [225,228,240], lineWidth: 0.15 },
        columnStyles: {
            0: { cellWidth: 90, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 35, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📋 PHASE COMPLETION PROGRESS
    // ============================================
    if (y > pageHeight - 80) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('PHASE COMPLETION PROGRESS', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['Phase', 'Target', 'Eligible', 'Completed', 'In Progress', 'Rate', 'Drop-off']],
        body: [
            @foreach($tracerAnalytics['phases'] as $phase)
            [
                {!! json_encode($phase->title) !!},
                {!! json_encode(ucfirst($phase->target_alumni_type)) !!},
                '{{ number_format($phase->eligible_count) }}',
                '{{ number_format($phase->completed_responses) }}',
                '{{ number_format($phase->in_progress_responses) }}',
                '{{ $phase->completion_rate }}%',
                '{{ $phase->drop_off_rate }}%'
            ],
            @endforeach
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8, cellPadding: 1.8, lineColor: [225,228,240], lineWidth: 0.15, overflow: 'linebreak' },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center', fontSize: 8 },
        columnStyles: {
            0: { cellWidth: 52 },
            1: { cellWidth: 20, halign: 'center' },
            2: { cellWidth: 18, halign: 'center' },
            3: { cellWidth: 20, halign: 'center' },
            4: { cellWidth: 20, halign: 'center' },
            5: { cellWidth: 18, halign: 'center', fontStyle: 'bold' },
            6: { cellWidth: 18, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📈 RESPONSES OVER TIME
    // ============================================
    if (y > pageHeight - 60) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('RESPONSES OVER TIME (Last 6 Months)', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['Month', 'Responses']],
        body: [
            @forelse($tracerAnalytics['chartData']['responses_over_time']['labels'] as $idx => $label)
            [
                {!! json_encode($label) !!},
                '{{ $tracerAnalytics["chartData"]["responses_over_time"]["data"][$idx] ?? 0 }}'
            ],
            @empty
            ['No data', '0'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 90 },
            1: { cellWidth: 35, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📊 COMPLETION FUNNEL
    // ============================================
    if (y > pageHeight - 50) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('COMPLETION FUNNEL', marginX + 4, y);
    y += 5;

    const funnelLabels = @json($tracerAnalytics['chartData']['funnel']['labels'] ?? []);
    const funnelData   = @json($tracerAnalytics['chartData']['funnel']['data'] ?? []);

    doc.autoTable({
        head: [['Stage', 'Alumni', 'Rate vs Eligible']],
        body: funnelLabels.map((label, i) => {
            const total = funnelData[0] || 0;
            const val = funnelData[i] || 0;
            const pct = total > 0 ? ((val / total) * 100).toFixed(1) + '%' : '—';
            return [label, String(val), pct];
        }),
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 80, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 30, halign: 'center' },
            2: { cellWidth: 30, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 👥 AUDIENCE BREAKDOWN
    // ============================================
    if (y > pageHeight - 50) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('TARGET AUDIENCE BREAKDOWN', marginX + 4, y);
    y += 5;

    const audLabels = @json($tracerAnalytics['chartData']['audience_breakdown']['labels'] ?? []);
    const audEligible = @json($tracerAnalytics['chartData']['audience_breakdown']['eligible'] ?? []);
    const audCompleted = @json($tracerAnalytics['chartData']['audience_breakdown']['completed'] ?? []);
    const audRate = @json($tracerAnalytics['chartData']['audience_breakdown']['completion_rate'] ?? []);

    doc.autoTable({
        head: [['Audience', 'Eligible', 'Completed', 'Completion Rate']],
        body: audLabels.map((label, i) => [
            label,
            String(audEligible[i] ?? 0),
            String(audCompleted[i] ?? 0),
            (audRate[i] ?? 0) + '%'
        ]),
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 60, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 30, halign: 'center' },
            2: { cellWidth: 30, halign: 'center' },
            3: { cellWidth: 35, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 🏆 TOP PERFORMING PHASES
    // ============================================
    if (y > pageHeight - 60) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('🏆  TOP PERFORMING PHASES', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Phase', 'Completed / Eligible', 'Completion Rate']],
        body: [
            @forelse($tracerAnalytics['topPhases'] as $idx => $phase)
            [
                '{{ $idx + 1 }}',
                {!! json_encode($phase->title) !!},
                '{{ $phase->completed_responses }} / {{ $phase->eligible_count }}',
                '{{ $phase->completion_rate }}%'
            ],
            @empty
            ['—', 'No phase data', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 8, halign: 'center', fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 70 },
            2: { cellWidth: 40, halign: 'center' },
            3: { cellWidth: 37, halign: 'center', fontStyle: 'bold', textColor: [5, 120, 80] },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // ⚠️ NEEDS ATTENTION
    // ============================================
    if (y > pageHeight - 60) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('⚠️  NEEDS ATTENTION', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Phase', 'Completion Rate', 'Drop-off Rate']],
        body: [
            @forelse($tracerAnalytics['needsAttention'] as $idx => $phase)
            [
                '{{ $idx + 1 }}',
                {!! json_encode($phase->title) !!},
                '{{ $phase->completion_rate }}%',
                '{{ $phase->drop_off_rate }}%'
            ],
            @empty
            ['—', 'No phase data', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: [170, 30, 30], textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 8, halign: 'center', fontStyle: 'bold', textColor: [170,30,30] },
            1: { cellWidth: 70 },
            2: { cellWidth: 40, halign: 'center', fontStyle: 'bold', textColor: [170,30,30] },
            3: { cellWidth: 37, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [255, 240, 240] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 🕐 RECENT SUBMISSIONS
    // ============================================
    if (y > pageHeight - 60) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('RECENT SUBMISSIONS', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Name', 'Type', 'Program', 'Submitted']],
        body: [
            @forelse($tracerAnalytics['recentSubmissions'] as $idx => $sub)
            [
                '{{ $idx + 1 }}',
                {!! json_encode($sub->alumni_name) !!},
                {!! json_encode(ucfirst($sub->alumni_type)) !!},
                {!! json_encode($sub->program) !!},
                {!! json_encode($sub->time_ago) !!}
            ],
            @empty
            ['—', 'No recent submissions', '—', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8.5, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15, overflow: 'linebreak' },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 8, halign: 'center', fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 60 },
            2: { cellWidth: 20, halign: 'center' },
            3: { cellWidth: 40 },
            4: { cellWidth: 30, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
        didDrawPage: function (hookData) {
            const pageCount = doc.internal.getNumberOfPages();
            doc.setFillColor(...NU_GOLD);
            doc.rect(0, pageHeight - 7, pageWidth, 0.8, 'F');

            doc.setFontSize(7);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(120, 130, 155);
            doc.text('LumiNUs Alumni Portal — Tracer Analytics Report', marginX, pageHeight - 3.5);

            doc.setTextColor(...NU_NAVY);
            doc.setFont(undefined, 'bold');
            doc.text(`Page ${hookData.pageNumber} of ${pageCount}`, pageWidth - marginX, pageHeight - 3.5, { align: 'right' });
        }
    });

    const filename = `Tracer_Analytics_${new Date().toISOString().slice(0, 10)}.pdf`;
    doc.save(filename);
    showToast('Tracer Analytics exported to PDF', 'success');
}

function exportTracerExcel() {
    if (!window.XLSX) {
        showToast('Excel library not loaded. Please refresh the page.', 'error');
        return;
    }

    const wb = XLSX.utils.book_new();

    const NU_NAVY = '32418C';
    const NU_GOLD = 'FBD117';

    const headerStyle = {
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 11 },
        fill: { fgColor: { rgb: NU_NAVY } },
        alignment: { horizontal: 'center', vertical: 'center' },
        border: { top: { style: 'thin' }, bottom: { style: 'thin' }, left: { style: 'thin' }, right: { style: 'thin' } }
    };
    const titleStyle = {
        font: { bold: true, sz: 14, color: { rgb: NU_NAVY } },
        alignment: { horizontal: 'left', vertical: 'center' }
    };

    // ============================================
    // 📄 SHEET 1 — Overview
    // ============================================
    const overviewData = [
        ['Tracer Response Analytics'],
        ['LumiNUs Alumni Portal — National University Lipa'],
        ['Generated:', new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' })],
        ['Active Form:', {!! json_encode($tracerAnalytics['activeForm']->form_title ?? 'N/A') !!}],
        [],
        ['KEY METRICS', 'Value'],
        ['Eligible Alumni',         {{ $tracerAnalytics["summary"]->total_eligible }}],
        ['Completed Responses',     {{ $tracerAnalytics["summary"]->total_completed }}],
        ['In Progress',             {{ $tracerAnalytics["summary"]->total_in_progress }}],
        ['Unique Responders',       {{ $tracerAnalytics["summary"]->unique_responders }}],
        ['Overall Completion Rate', {{ $tracerAnalytics["summary"]->overall_completion_rate }}],
        ['Overall Response Rate',   {{ $tracerAnalytics["summary"]->overall_response_rate }}],
        ['Active Phases',           {{ $tracerAnalytics["summary"]->total_phases }}],
        [],
        ['RESPONSES OVER TIME', 'Count'],
        @foreach($tracerAnalytics['chartData']['responses_over_time']['labels'] as $idx => $label)
        [{!! json_encode($label) !!}, {{ $tracerAnalytics['chartData']['responses_over_time']['data'][$idx] ?? 0 }}],
        @endforeach
    ];

    const overviewWs = XLSX.utils.aoa_to_sheet(overviewData);
    overviewWs['!cols'] = [{ wch: 32 }, { wch: 18 }];
    overviewWs['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 1 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 1 } },
    ];
    if (overviewWs['A1']) overviewWs['A1'].s = { font: { bold: true, sz: 16, color: { rgb: NU_NAVY } } };
    ['A6','B6','A15','B15'].forEach(cell => { if (overviewWs[cell]) overviewWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, overviewWs, 'Overview');

    // ============================================
    // 📄 SHEET 2 — Phase Completion
    // ============================================
    const phaseData = [
        ['PHASE COMPLETION PROGRESS'],
        [],
        ['Phase', 'Target Audience', 'Eligible', 'Completed', 'In Progress', 'Completion Rate (%)', 'Drop-off Rate (%)']
    ];

    @foreach($tracerAnalytics['phases'] as $phase)
    phaseData.push([
        {!! json_encode($phase->title) !!},
        {!! json_encode(ucfirst($phase->target_alumni_type)) !!},
        {{ $phase->eligible_count }},
        {{ $phase->completed_responses }},
        {{ $phase->in_progress_responses }},
        {{ $phase->completion_rate }},
        {{ $phase->drop_off_rate }}
    ]);
    @endforeach

    const phaseWs = XLSX.utils.aoa_to_sheet(phaseData);
    phaseWs['!cols'] = [
        { wch: 32 }, { wch: 18 }, { wch: 10 }, { wch: 12 }, { wch: 12 }, { wch: 18 }, { wch: 16 }
    ];
    phaseWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }];
    if (phaseWs['A1']) phaseWs['A1'].s = titleStyle;
    ['A3','B3','C3','D3','E3','F3','G3'].forEach(cell => {
        if (phaseWs[cell]) phaseWs[cell].s = headerStyle;
    });

    XLSX.utils.book_append_sheet(wb, phaseWs, 'Phase Progress');

    // ============================================
    // 📄 SHEET 3 — Audience Breakdown
    // ============================================
    const audLabels    = @json($tracerAnalytics['chartData']['audience_breakdown']['labels'] ?? []);
    const audEligible  = @json($tracerAnalytics['chartData']['audience_breakdown']['eligible'] ?? []);
    const audCompleted = @json($tracerAnalytics['chartData']['audience_breakdown']['completed'] ?? []);
    const audRate      = @json($tracerAnalytics['chartData']['audience_breakdown']['completion_rate'] ?? []);

    const audData = [
        ['TARGET AUDIENCE BREAKDOWN'],
        [],
        ['Audience', 'Eligible', 'Completed', 'Completion Rate (%)']
    ];
    audLabels.forEach((label, i) => {
        audData.push([label, audEligible[i] ?? 0, audCompleted[i] ?? 0, audRate[i] ?? 0]);
    });

    const audWs = XLSX.utils.aoa_to_sheet(audData);
    audWs['!cols'] = [{ wch: 22 }, { wch: 12 }, { wch: 12 }, { wch: 20 }];
    audWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }];
    if (audWs['A1']) audWs['A1'].s = titleStyle;
    ['A3','B3','C3','D3'].forEach(cell => { if (audWs[cell]) audWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, audWs, 'Audience Breakdown');

    // ============================================
    // 📄 SHEET 4 — Completion Funnel
    // ============================================
    const fLabels = @json($tracerAnalytics['chartData']['funnel']['labels'] ?? []);
    const fData   = @json($tracerAnalytics['chartData']['funnel']['data'] ?? []);
    const funnelRows = [
        ['COMPLETION FUNNEL'],
        [],
        ['Stage', 'Alumni', 'Rate vs Eligible (%)']
    ];
    fLabels.forEach((label, i) => {
        const total = fData[0] || 0;
        const val = fData[i] || 0;
        const pct = total > 0 ? ((val / total) * 100).toFixed(1) : 0;
        funnelRows.push([label, val, parseFloat(pct)]);
    });

    const funnelWs = XLSX.utils.aoa_to_sheet(funnelRows);
    funnelWs['!cols'] = [{ wch: 20 }, { wch: 12 }, { wch: 20 }];
    funnelWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 2 } }];
    if (funnelWs['A1']) funnelWs['A1'].s = titleStyle;
    ['A3','B3','C3'].forEach(cell => { if (funnelWs[cell]) funnelWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, funnelWs, 'Funnel');

    // ============================================
    // 📄 SHEET 5 — Top Performers
    // ============================================
    const topData = [
        ['TOP PERFORMING PHASES'],
        [],
        ['#', 'Phase', 'Completed', 'Eligible', 'Completion Rate (%)']
    ];
    @forelse($tracerAnalytics['topPhases'] as $idx => $phase)
    topData.push([
        {{ $idx + 1 }},
        {!! json_encode($phase->title) !!},
        {{ $phase->completed_responses }},
        {{ $phase->eligible_count }},
        {{ $phase->completion_rate }}
    ]);
    @empty
    topData.push(['—', 'No phase data', 0, 0, 0]);
    @endforelse

    const topWs = XLSX.utils.aoa_to_sheet(topData);
    topWs['!cols'] = [{ wch: 5 }, { wch: 32 }, { wch: 12 }, { wch: 12 }, { wch: 20 }];
    topWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 4 } }];
    if (topWs['A1']) topWs['A1'].s = titleStyle;
    ['A3','B3','C3','D3','E3'].forEach(cell => { if (topWs[cell]) topWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, topWs, 'Top Phases');

    // ============================================
    // 📄 SHEET 6 — Needs Attention
    // ============================================
    const attentionData = [
        ['PHASES NEEDING ATTENTION'],
        [],
        ['#', 'Phase', 'Completion Rate (%)', 'Drop-off Rate (%)']
    ];
    @forelse($tracerAnalytics['needsAttention'] as $idx => $phase)
    attentionData.push([
        {{ $idx + 1 }},
        {!! json_encode($phase->title) !!},
        {{ $phase->completion_rate }},
        {{ $phase->drop_off_rate }}
    ]);
    @empty
    attentionData.push(['—', 'No phase data', 0, 0]);
    @endforelse

    const attentionWs = XLSX.utils.aoa_to_sheet(attentionData);
    attentionWs['!cols'] = [{ wch: 5 }, { wch: 32 }, { wch: 20 }, { wch: 18 }];
    attentionWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }];
    if (attentionWs['A1']) attentionWs['A1'].s = { font: { bold: true, sz: 14, color: { rgb: 'AA1E1E' } } };
    ['A3','B3','C3','D3'].forEach(cell => { if (attentionWs[cell]) attentionWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, attentionWs, 'Needs Attention');

    // ============================================
    // 📄 SHEET 7 — Recent Submissions
    // ============================================
    const recentData = [
        ['RECENT SUBMISSIONS'],
        [],
        ['#', 'Name', 'Type', 'Program', 'Submitted']
    ];
    @forelse($tracerAnalytics['recentSubmissions'] as $idx => $sub)
    recentData.push([
        {{ $idx + 1 }},
        {!! json_encode($sub->alumni_name) !!},
        {!! json_encode(ucfirst($sub->alumni_type)) !!},
        {!! json_encode($sub->program) !!},
        {!! json_encode($sub->time_ago) !!}
    ]);
    @empty
    recentData.push(['—', 'No recent submissions', '—', '—', '—']);
    @endforelse

    const recentWs = XLSX.utils.aoa_to_sheet(recentData);
    recentWs['!cols'] = [{ wch: 5 }, { wch: 28 }, { wch: 12 }, { wch: 28 }, { wch: 24 }];
    recentWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 4 } }];
    if (recentWs['A1']) recentWs['A1'].s = titleStyle;
    ['A3','B3','C3','D3','E3'].forEach(cell => { if (recentWs[cell]) recentWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, recentWs, 'Recent Submissions');

    // ============================================
    // 💾 SAVE
    // ============================================
    const filename = `Tracer_Analytics_${new Date().toISOString().slice(0, 10)}.xlsx`;
    XLSX.writeFile(wb, filename);
    showToast('Tracer Analytics exported to Excel', 'success');
}

// ============================================
// ✨ EMPLOYMENT ANALYTICS EXPORTERS
// ============================================

async function exportEmploymentData(format) {
    @if(!isset($tracerAnalytics['collegeEmployment']['has_data']) || !$tracerAnalytics['collegeEmployment']['has_data'])
        showToast('No employment data to export.', 'warning');
        return;
    @endif

    if (format === 'pdf') {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            showToast('PDF library not loaded. Please refresh the page.', 'error');
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const NU_NAVY = [50, 65, 140];
        const NU_GOLD = [251, 209, 23];
        const pageWidth = 210, pageHeight = 297, marginX = 12;

        doc.setFillColor(...NU_NAVY);
        doc.rect(0, 0, pageWidth, 26, 'F');
        doc.setFillColor(...NU_GOLD);
        doc.rect(0, 26, pageWidth, 1.5, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(15);
        doc.text('College Alumni Employment Analytics', marginX, 12);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(9);
        doc.text('LumiNUs Alumni Portal  •  National University Lipa', marginX, 18.5);
        doc.setFontSize(8.5);
        doc.text('Generated: ' + new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' }), pageWidth - marginX, 18.5, { align: 'right' });

        let y = 36;
        doc.setFillColor(...NU_GOLD);
        doc.rect(marginX, y - 3.5, 2, 5, 'F');
        doc.setTextColor(...NU_NAVY);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(11);
        doc.text('EMPLOYMENT OVERVIEW', marginX + 4, y);
        y += 6;

        doc.autoTable({
            body: [
                ['Total Verified College Alumni', '{{ number_format($tracerAnalytics["collegeEmployment"]["total_college_alumni"] ?? 0) }}'],
                ['With Employment Records', '{{ number_format($tracerAnalytics["collegeEmployment"]["total_with_employment_records"] ?? 0) }}'],
                ['Currently Employed', '{{ number_format($tracerAnalytics["collegeEmployment"]["currently_employed"] ?? 0) }}'],
                ['No Employment Records', '{{ number_format($tracerAnalytics["collegeEmployment"]["unemployed_count"] ?? 0) }}'],
                ['Employment Rate', '{{ $tracerAnalytics["collegeEmployment"]["employment_rate"] ?? 0 }}%'],
            ],
            startY: y,
            theme: 'grid',
            styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
            columnStyles: {
                0: { cellWidth: 90, fontStyle: 'bold', textColor: NU_NAVY },
                1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
            },
            alternateRowStyles: { fillColor: [248, 249, 253] },
            margin: { left: marginX, right: marginX },
        });
        y = doc.lastAutoTable.finalY + 8;

        @if($tracerAnalytics['collegeEmployment']['top_employers']->count())
        doc.setFillColor(...NU_GOLD);
        doc.rect(marginX, y - 3.5, 2, 5, 'F');
        doc.setTextColor(...NU_NAVY);
        doc.setFontSize(11);
        doc.text('TOP EMPLOYERS', marginX + 4, y);
        y += 5;
        doc.autoTable({
            head: [['#', 'Employer', 'Alumni']],
            body: [
                @foreach($tracerAnalytics['collegeEmployment']['top_employers'] as $i => $emp)
                ['{{ $i + 1 }}', {!! json_encode($emp->company) !!}, '{{ $emp->count }}'],
                @endforeach
            ],
            startY: y,
            theme: 'grid',
            styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
            headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center' },
                1: { cellWidth: 110 },
                2: { cellWidth: 30, halign: 'center', fontStyle: 'bold' },
            },
            alternateRowStyles: { fillColor: [248, 249, 253] },
            margin: { left: marginX, right: marginX },
        });
        y = doc.lastAutoTable.finalY + 8;
        @endif

        @if($tracerAnalytics['collegeEmployment']['top_job_titles']->count())
        if (y > pageHeight - 60) { doc.addPage(); y = 20; }
        doc.setFillColor(...NU_GOLD);
        doc.rect(marginX, y - 3.5, 2, 5, 'F');
        doc.setTextColor(...NU_NAVY);
        doc.setFontSize(11);
        doc.text('TOP JOB TITLES', marginX + 4, y);
        y += 5;
        doc.autoTable({
            head: [['#', 'Job Title', 'Alumni']],
            body: [
                @foreach($tracerAnalytics['collegeEmployment']['top_job_titles'] as $i => $job)
                ['{{ $i + 1 }}', {!! json_encode($job->job_title) !!}, '{{ $job->count }}'],
                @endforeach
            ],
            startY: y,
            theme: 'grid',
            styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
            headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center' },
                1: { cellWidth: 110 },
                2: { cellWidth: 30, halign: 'center', fontStyle: 'bold' },
            },
            alternateRowStyles: { fillColor: [248, 249, 253] },
            margin: { left: marginX, right: marginX },
        });
        y = doc.lastAutoTable.finalY + 8;
        @endif

        @if($tracerAnalytics['collegeEmployment']['employment_by_program']->count())
        if (y > pageHeight - 60) { doc.addPage(); y = 20; }
        doc.setFillColor(...NU_GOLD);
        doc.rect(marginX, y - 3.5, 2, 5, 'F');
        doc.setTextColor(...NU_NAVY);
        doc.setFontSize(11);
        doc.text('EMPLOYMENT RATE BY PROGRAM', marginX + 4, y);
        y += 5;
        doc.autoTable({
            head: [['Program', 'Employed', 'Total', 'Rate']],
            body: [
                @foreach($tracerAnalytics['collegeEmployment']['employment_by_program'] as $prog)
                [
                    {!! json_encode($prog->program) !!},
                    '{{ $prog->employed_alumni }}',
                    '{{ $prog->total_alumni }}',
                    '{{ $prog->employment_rate }}%'
                ],
                @endforeach
            ],
            startY: y,
            theme: 'grid',
            styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15, overflow: 'linebreak' },
            headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
            columnStyles: {
                0: { cellWidth: 80 },
                1: { cellWidth: 25, halign: 'center' },
                2: { cellWidth: 25, halign: 'center' },
                3: { cellWidth: 30, halign: 'center', fontStyle: 'bold', textColor: [5, 120, 80] },
            },
            alternateRowStyles: { fillColor: [248, 249, 253] },
            margin: { left: marginX, right: marginX },
        });
        @endif

        doc.save(`College_Employment_Analytics_${new Date().toISOString().slice(0, 10)}.pdf`);
        showToast('Employment analytics exported to PDF', 'success');
    } else {
        // Excel version — simplified
        if (!window.XLSX) { showToast('Excel library not loaded.', 'error'); return; }
        const wb = XLSX.utils.book_new();

        const overviewWs = XLSX.utils.aoa_to_sheet([
            ['College Alumni Employment Analytics'],
            ['Generated:', new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' })],
            [],
            ['Metric', 'Value'],
            ['Total Verified College Alumni', {{ $tracerAnalytics["collegeEmployment"]["total_college_alumni"] ?? 0 }}],
            ['With Employment Records', {{ $tracerAnalytics["collegeEmployment"]["total_with_employment_records"] ?? 0 }}],
            ['Currently Employed', {{ $tracerAnalytics["collegeEmployment"]["currently_employed"] ?? 0 }}],
            ['No Employment Records', {{ $tracerAnalytics["collegeEmployment"]["unemployed_count"] ?? 0 }}],
            ['Employment Rate', '{{ $tracerAnalytics["collegeEmployment"]["employment_rate"] ?? 0 }}%'],
        ]);
        overviewWs['!cols'] = [{ wch: 32 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(wb, overviewWs, 'Overview');

        @if($tracerAnalytics['collegeEmployment']['top_employers']->count())
        const empWs = XLSX.utils.aoa_to_sheet([
            ['#', 'Employer', 'Alumni'],
            @foreach($tracerAnalytics['collegeEmployment']['top_employers'] as $i => $emp)
            [{{ $i + 1 }}, {!! json_encode($emp->company) !!}, {{ $emp->count }}],
            @endforeach
        ]);
        empWs['!cols'] = [{ wch: 5 }, { wch: 45 }, { wch: 12 }];
        XLSX.utils.book_append_sheet(wb, empWs, 'Employers');
        @endif

        @if($tracerAnalytics['collegeEmployment']['top_job_titles']->count())
        const jobWs = XLSX.utils.aoa_to_sheet([
            ['#', 'Job Title', 'Alumni'],
            @foreach($tracerAnalytics['collegeEmployment']['top_job_titles'] as $i => $job)
            [{{ $i + 1 }}, {!! json_encode($job->job_title) !!}, {{ $job->count }}],
            @endforeach
        ]);
        jobWs['!cols'] = [{ wch: 5 }, { wch: 45 }, { wch: 12 }];
        XLSX.utils.book_append_sheet(wb, jobWs, 'Job Titles');
        @endif

        @if($tracerAnalytics['collegeEmployment']['employment_by_program']->count())
        const progWs = XLSX.utils.aoa_to_sheet([
            ['Program', 'Employed', 'Total', 'Rate'],
            @foreach($tracerAnalytics['collegeEmployment']['employment_by_program'] as $prog)
            [{!! json_encode($prog->program) !!}, {{ $prog->employed_alumni }}, {{ $prog->total_alumni }}, {{ $prog->employment_rate }}],
            @endforeach
        ]);
        progWs['!cols'] = [{ wch: 35 }, { wch: 12 }, { wch: 10 }, { wch: 10 }];
        XLSX.utils.book_append_sheet(wb, progWs, 'By Program');
        @endif

        XLSX.writeFile(wb, `College_Employment_Analytics_${new Date().toISOString().slice(0, 10)}.xlsx`);
        showToast('Employment analytics exported to Excel', 'success');
    }
}

async function exportShsCollegeData(format) {
    @if(!isset($tracerAnalytics['shsCollegeChoice']['has_data']) || !$tracerAnalytics['shsCollegeChoice']['has_data'])
        showToast('No SHS data to export.', 'warning');
        return;
    @endif

    if (format === 'pdf') {
        if (!window.jspdf || !window.jspdf.jsPDF) {
            showToast('PDF library not loaded.', 'error');
            return;
        }
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF('p', 'mm', 'a4');
        const NU_NAVY = [50, 65, 140];
        const NU_GOLD = [251, 209, 23];
        const pageWidth = 210, pageHeight = 297, marginX = 12;

        doc.setFillColor(...NU_NAVY);
        doc.rect(0, 0, pageWidth, 26, 'F');
        doc.setFillColor(...NU_GOLD);
        doc.rect(0, 26, pageWidth, 1.5, 'F');
        doc.setTextColor(255, 255, 255);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(15);
        doc.text('SHS Alumni College Continuation Report', marginX, 12);
        doc.setFont(undefined, 'normal');
        doc.setFontSize(9);
        doc.text('LumiNUs Alumni Portal  •  National University Lipa', marginX, 18.5);
        doc.setFontSize(8.5);
        doc.text('Generated: ' + new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' }), pageWidth - marginX, 18.5, { align: 'right' });

        let y = 36;
        doc.setFillColor(...NU_GOLD);
        doc.rect(marginX, y - 3.5, 2, 5, 'F');
        doc.setTextColor(...NU_NAVY);
        doc.setFont(undefined, 'bold');
        doc.setFontSize(11);
        doc.text('CONTINUATION OVERVIEW', marginX + 4, y);
        y += 6;

        doc.autoTable({
            body: [
                ['Total Verified SHS Alumni', '{{ number_format($tracerAnalytics["shsCollegeChoice"]["total_shs_alumni"] ?? 0) }}'],
                ['Continued to College', '{{ number_format($tracerAnalytics["shsCollegeChoice"]["continued_count"] ?? 0) }}'],
                ['Did Not Continue', '{{ number_format($tracerAnalytics["shsCollegeChoice"]["did_not_continue_count"] ?? 0) }}'],
                ['Continuation Rate', '{{ $tracerAnalytics["shsCollegeChoice"]["continuation_rate"] ?? 0 }}%'],
                ['Now at NU Lipa', '{{ number_format($tracerAnalytics["shsCollegeChoice"]["nu_lipa_count"] ?? 0) }}'],
            ],
            startY: y,
            theme: 'grid',
            styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
            columnStyles: {
                0: { cellWidth: 90, fontStyle: 'bold', textColor: NU_NAVY },
                1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
            },
            alternateRowStyles: { fillColor: [248, 249, 253] },
            margin: { left: marginX, right: marginX },
        });
        y = doc.lastAutoTable.finalY + 8;

        @if($tracerAnalytics['shsCollegeChoice']['top_college_programs']->count())
        doc.setFillColor(...NU_GOLD);
        doc.rect(marginX, y - 3.5, 2, 5, 'F');
        doc.setTextColor(...NU_NAVY);
        doc.setFontSize(11);
        doc.text('TOP COLLEGE PROGRAMS CHOSEN', marginX + 4, y);
        y += 5;
        doc.autoTable({
            head: [['#', 'Program', 'Students']],
            body: [
                @foreach($tracerAnalytics['shsCollegeChoice']['top_college_programs'] as $i => $prog)
                ['{{ $i + 1 }}', {!! json_encode($prog->program_name) !!}, '{{ $prog->count }}'],
                @endforeach
            ],
            startY: y,
            theme: 'grid',
            styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
            headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
            columnStyles: {
                0: { cellWidth: 10, halign: 'center' },
                1: { cellWidth: 110 },
                2: { cellWidth: 30, halign: 'center', fontStyle: 'bold' },
            },
            alternateRowStyles: { fillColor: [248, 249, 253] },
            margin: { left: marginX, right: marginX },
        });
        @endif

        doc.save(`SHS_College_Continuation_${new Date().toISOString().slice(0, 10)}.pdf`);
        showToast('SHS continuation data exported to PDF', 'success');
    } else {
        if (!window.XLSX) { showToast('Excel library not loaded.', 'error'); return; }
        const wb = XLSX.utils.book_new();

        const overviewWs = XLSX.utils.aoa_to_sheet([
            ['SHS Alumni College Continuation'],
            ['Generated:', new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' })],
            [],
            ['Metric', 'Value'],
            ['Total Verified SHS Alumni', {{ $tracerAnalytics["shsCollegeChoice"]["total_shs_alumni"] ?? 0 }}],
            ['Continued to College', {{ $tracerAnalytics["shsCollegeChoice"]["continued_count"] ?? 0 }}],
            ['Did Not Continue', {{ $tracerAnalytics["shsCollegeChoice"]["did_not_continue_count"] ?? 0 }}],
            ['Continuation Rate', '{{ $tracerAnalytics["shsCollegeChoice"]["continuation_rate"] ?? 0 }}%'],
            ['Now at NU Lipa', {{ $tracerAnalytics["shsCollegeChoice"]["nu_lipa_count"] ?? 0 }}],
        ]);
        overviewWs['!cols'] = [{ wch: 32 }, { wch: 20 }];
        XLSX.utils.book_append_sheet(wb, overviewWs, 'Overview');

        @if($tracerAnalytics['shsCollegeChoice']['top_college_programs']->count())
        const progWs = XLSX.utils.aoa_to_sheet([
            ['#', 'College Program', 'Students'],
            @foreach($tracerAnalytics['shsCollegeChoice']['top_college_programs'] as $i => $prog)
            [{{ $i + 1 }}, {!! json_encode($prog->program_name) !!}, {{ $prog->count }}],
            @endforeach
        ]);
        progWs['!cols'] = [{ wch: 5 }, { wch: 45 }, { wch: 12 }];
        XLSX.utils.book_append_sheet(wb, progWs, 'College Programs');
        @endif

        XLSX.writeFile(wb, `SHS_College_Continuation_${new Date().toISOString().slice(0, 10)}.xlsx`);
        showToast('SHS continuation data exported to Excel', 'success');
    }
}

// ============================================
// ✨ EVENT ANALYTICS EXPORTERS
// ============================================

async function exportEventsPDF() {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        showToast('PDF library not loaded. Please refresh the page.', 'error');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');

    const NU_NAVY = [50, 65, 140];
    const NU_GOLD = [251, 209, 23];

    const pageWidth  = 210;
    const pageHeight = 297;
    const marginX    = 12;

    // ---- Preload NU logo ----
    let nuLogoDataUrl = null;
    try {
        nuLogoDataUrl = await loadImageAsDataURL('/assets/logos/nu_logo_white.png');
    } catch (e) {
        console.warn('Could not load NU logo for PDF:', e);
    }

    // ============================================
    // 🎨 HEADER
    // ============================================
    doc.setFillColor(...NU_NAVY);
    doc.rect(0, 0, pageWidth, 26, 'F');
    doc.setFillColor(...NU_GOLD);
    doc.rect(0, 26, pageWidth, 1.5, 'F');

    let logoDrawn = false;
    if (nuLogoDataUrl) {
        try {
            doc.addImage(nuLogoDataUrl, 'PNG', marginX, 4, 18, 18, undefined, 'FAST');
            logoDrawn = true;
        } catch (e) {}
    }
    const textStartX = logoDrawn ? marginX + 22 : marginX;

    doc.setTextColor(255, 255, 255);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(15);
    doc.text('Event Analytics Report', textStartX, 12);

    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);
    doc.setTextColor(230, 235, 255);
    doc.text('LumiNUs Alumni Portal  •  National University Lipa', textStartX, 18.5);

    const generatedStr = 'Generated: ' + new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' });
    doc.setFontSize(8.5);
    doc.text(generatedStr, pageWidth - marginX, 18.5, { align: 'right' });

    // ============================================
    // 📊 KEY METRICS
    // ============================================
    let y = 36;

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(11);
    doc.text('KEY METRICS', marginX + 4, y);
    y += 6;

    const growthVal = {{ $eventAnalytics["summary"]["registration_growth"] }};
    const growthLabel = growthVal > 0
        ? `+${growthVal}%`
        : (growthVal < 0 ? `${growthVal}%` : '0%');

    const kpis = [
        ['Total Events',              '{{ number_format($eventAnalytics["summary"]["total_events"]) }}'],
        ['Active Events',             '{{ number_format($eventAnalytics["summary"]["active_events"]) }}'],
        ['Upcoming Events',           '{{ number_format($eventAnalytics["summary"]["upcoming_events"]) }}'],
        ['Completed Events',          '{{ number_format($eventAnalytics["summary"]["completed_events"]) }}'],
        ['Cancelled Events',          '{{ number_format($eventAnalytics["summary"]["cancelled_events"]) }}'],
        ['Total Registrations',       '{{ number_format($eventAnalytics["summary"]["total_registrations"]) }}'],
        ['Avg Registrations / Event', '{{ number_format($eventAnalytics["summary"]["avg_registrations_per_event"], 1) }}'],
        ['Overall Fill Rate',         '{{ $eventAnalytics["summary"]["overall_fill_rate"] }}%'],
        ['This Month Registrations',  '{{ number_format($eventAnalytics["summary"]["this_month_registrations"]) }}'],
        ['Registration Growth',       growthLabel + ' vs last month'],
    ];

    doc.autoTable({
        body: kpis.map(([k, v]) => [k, v]),
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, textColor: [40,40,50], lineColor: [225,228,240], lineWidth: 0.15 },
        columnStyles: {
            0: { cellWidth: 85, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📈 REGISTRATIONS OVER TIME
    // ============================================
    if (y > pageHeight - 70) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('REGISTRATIONS OVER TIME (Last 12 Months)', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['Month', 'Registrations']],
        body: [
            @forelse($eventAnalytics['charts']['registrations_over_time']['labels'] as $idx => $label)
            [
                {!! json_encode($label) !!},
                '{{ $eventAnalytics["charts"]["registrations_over_time"]["data"][$idx] ?? 0 }}'
            ],
            @empty
            ['No data', '0'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 85 },
            1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 🏆 TOP PERFORMING EVENTS
    // ============================================
    if (y > pageHeight - 70) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('🏆  TOP PERFORMING EVENTS', marginX + 4, y);
    y += 5;

    const topEventFullTitles = @json($eventAnalytics['charts']['top_events']['full_titles'] ?? []);
    const topEventData       = @json($eventAnalytics['charts']['top_events']['data'] ?? []);
    const topEventFillRates  = @json($eventAnalytics['charts']['top_events']['fill_rates'] ?? []);

    doc.autoTable({
        head: [['#', 'Event', 'Registrations', 'Fill Rate']],
        body: topEventFullTitles.map((title, i) => [
            String(i + 1),
            title,
            String(topEventData[i] ?? 0),
            (topEventFillRates[i] ?? 0) + '%'
        ]),
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8.5, cellPadding: 2, overflow: 'linebreak', lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 8, halign: 'center', fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 90 },
            2: { cellWidth: 30, halign: 'center', fontStyle: 'bold' },
            3: { cellWidth: 30, halign: 'center', fontStyle: 'bold', textColor: [5, 120, 80] },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📊 EVENT FORMAT BREAKDOWN + STATUS DISTRIBUTION
    // ============================================
    if (y > pageHeight - 70) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('EVENT FORMAT BREAKDOWN', marginX + 4, y);
    y += 5;

    const evtTypeLabels = @json($eventAnalytics['charts']['event_type_breakdown']['labels'] ?? []);
    const evtTypeData   = @json($eventAnalytics['charts']['event_type_breakdown']['data'] ?? []);

    doc.autoTable({
        head: [['Format', 'Count']],
        body: evtTypeLabels.length
            ? evtTypeLabels.map((label, i) => [label, String(evtTypeData[i] ?? 0)])
            : [['No data', '0']],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 85, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    if (y > pageHeight - 60) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('EVENT STATUS DISTRIBUTION', marginX + 4, y);
    y += 5;

    const evtStatusLabels = @json($eventAnalytics['charts']['status_distribution']['labels'] ?? []);
    const evtStatusData   = @json($eventAnalytics['charts']['status_distribution']['data'] ?? []);

    doc.autoTable({
        head: [['Status', 'Count']],
        body: evtStatusLabels.map((label, i) => [label, String(evtStatusData[i] ?? 0)]),
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 85, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 🎯 CAPACITY & FILL RATE
    // ============================================
    if (y > pageHeight - 70) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('CAPACITY & FILL RATE (Upcoming & Active)', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['Event', 'Capacity', 'Registered', 'Fill Rate']],
        body: [
            @forelse($eventAnalytics['charts']['capacity_analysis'] as $evt)
            [
                {!! json_encode($evt['title']) !!},
                '{{ number_format($evt["capacity"]) }}',
                '{{ number_format($evt["registered"]) }}',
                '{{ $evt["fill_rate"] }}%'
            ],
            @empty
            ['No upcoming events', '—', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8.5, cellPadding: 2, overflow: 'linebreak', lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 78 },
            1: { cellWidth: 25, halign: 'center' },
            2: { cellWidth: 28, halign: 'center' },
            3: { cellWidth: 27, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 🎓 TOP PROGRAMS ATTENDING
    // ============================================
    if (y > pageHeight - 60) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('TOP PROGRAMS ATTENDING', marginX + 4, y);
    y += 5;

    const topProgLabels = @json($eventAnalytics['charts']['top_programs']['labels'] ?? []);
    const topProgData   = @json($eventAnalytics['charts']['top_programs']['data'] ?? []);

    doc.autoTable({
        head: [['Program', 'Registrations']],
        body: topProgLabels.length
            ? topProgLabels.map((label, i) => [label, String(topProgData[i] ?? 0)])
            : [['No data', '0']],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 85, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 40, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // ⚡ RECENT REGISTRATION ACTIVITY
    // ============================================
    if (y > pageHeight - 70) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('RECENT REGISTRATION ACTIVITY', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Alumni', 'Event', 'When']],
        body: [
            @forelse($eventAnalytics['recent_registrations'] as $idx => $reg)
            [
                '{{ $idx + 1 }}',
                {!! json_encode($reg['alumni_name']) !!},
                {!! json_encode(\Str::limit($reg['event_title'], 45)) !!},
                {!! json_encode($reg['time_ago']) !!}
            ],
            @empty
            ['—', 'No recent registrations', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8, cellPadding: 1.8, overflow: 'linebreak', lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 7, halign: 'center', fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 42 },
            2: { cellWidth: 70 },
            3: { cellWidth: 40, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📋 FULL EVENT PERFORMANCE TABLE
    // ============================================
    if (y > pageHeight - 60) { doc.addPage(); y = 20; }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('FULL EVENT PERFORMANCE TABLE', marginX + 4, y);
    y += 5;

    doc.setFontSize(8);
    doc.setFont(undefined, 'normal');
    doc.setTextColor(100, 100, 100);
    doc.text('All events — sorted by start date (most recent first)', marginX, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Event', 'Format', 'Date', 'Reg.', 'Cap.', 'Fill', 'Status']],
        body: [
            @forelse($eventAnalytics['event_performance'] as $idx => $evt)
            [
                '{{ $idx + 1 }}',
                {!! json_encode(\Str::limit($evt['title'], 55)) !!},
                {!! json_encode($evt['event_type']) !!},
                '{{ \Carbon\Carbon::parse($evt["start_date"])->format("M d, Y") }}',
                '{{ number_format($evt["registered"]) }}',
                '{{ number_format($evt["capacity"]) }}',
                '{{ $evt["fill_rate"] }}%',
                {!! json_encode($evt['status']) !!}
            ],
            @empty
            ['—', 'No events found', '—', '—', '—', '—', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 7, cellPadding: 1.5, overflow: 'linebreak', lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center', fontSize: 7.5 },
        columnStyles: {
            0: { cellWidth: 6, halign: 'center', fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 58 },
            2: { cellWidth: 18, halign: 'center' },
            3: { cellWidth: 18, halign: 'center' },
            4: { cellWidth: 12, halign: 'center', fontStyle: 'bold' },
            5: { cellWidth: 12, halign: 'center' },
            6: { cellWidth: 14, halign: 'center', fontStyle: 'bold' },
            7: { cellWidth: 17, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
        didDrawPage: function (hookData) {
            const pageCount = doc.internal.getNumberOfPages();
            doc.setFillColor(...NU_GOLD);
            doc.rect(0, pageHeight - 7, pageWidth, 0.8, 'F');

            doc.setFontSize(7);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(120, 130, 155);
            doc.text('LumiNUs Alumni Portal — Event Analytics Report', marginX, pageHeight - 3.5);

            doc.setTextColor(...NU_NAVY);
            doc.setFont(undefined, 'bold');
            doc.text(`Page ${hookData.pageNumber} of ${pageCount}`, pageWidth - marginX, pageHeight - 3.5, { align: 'right' });
        }
    });

    const filename = `Event_Analytics_${new Date().toISOString().slice(0, 10)}.pdf`;
    doc.save(filename);
    showToast('Event Analytics exported to PDF', 'success');
}

function exportEventsExcel() {
    if (!window.XLSX) {
        showToast('Excel library not loaded. Please refresh the page.', 'error');
        return;
    }

    const wb = XLSX.utils.book_new();

    const NU_NAVY = '32418C';
    const NU_GOLD = 'FBD117';

    const headerStyle = {
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 11 },
        fill: { fgColor: { rgb: NU_NAVY } },
        alignment: { horizontal: 'center', vertical: 'center' },
        border: { top: { style: 'thin' }, bottom: { style: 'thin' }, left: { style: 'thin' }, right: { style: 'thin' } }
    };
    const titleStyle = {
        font: { bold: true, sz: 14, color: { rgb: NU_NAVY } },
        alignment: { horizontal: 'left', vertical: 'center' }
    };

    // ============================================
    // 📄 SHEET 1 — Overview
    // ============================================
    const overviewData = [
        ['Event Analytics Report'],
        ['LumiNUs Alumni Portal — National University Lipa'],
        ['Generated:', new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' })],
        [],
        ['KEY METRICS', 'Value'],
        ['Total Events',              {{ $eventAnalytics["summary"]["total_events"] }}],
        ['Active Events',             {{ $eventAnalytics["summary"]["active_events"] }}],
        ['Upcoming Events',           {{ $eventAnalytics["summary"]["upcoming_events"] }}],
        ['Completed Events',          {{ $eventAnalytics["summary"]["completed_events"] }}],
        ['Cancelled Events',          {{ $eventAnalytics["summary"]["cancelled_events"] }}],
        ['Total Registrations',       {{ $eventAnalytics["summary"]["total_registrations"] }}],
        ['Avg Registrations / Event', {{ $eventAnalytics["summary"]["avg_registrations_per_event"] }}],
        ['Overall Fill Rate',         {{ $eventAnalytics["summary"]["overall_fill_rate"] }}],
        ['This Month Registrations',  {{ $eventAnalytics["summary"]["this_month_registrations"] }}],
        ['Registration Growth',       {{ $eventAnalytics["summary"]["registration_growth"] }}],
        [],
        ['REGISTRATIONS OVER TIME', 'Count'],
        @foreach($eventAnalytics['charts']['registrations_over_time']['labels'] as $idx => $label)
        [{!! json_encode($label) !!}, {{ $eventAnalytics['charts']['registrations_over_time']['data'][$idx] ?? 0 }}],
        @endforeach
    ];

    const overviewWs = XLSX.utils.aoa_to_sheet(overviewData);
    overviewWs['!cols'] = [{ wch: 32 }, { wch: 18 }];
    overviewWs['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 1 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 1 } },
    ];
    if (overviewWs['A1']) overviewWs['A1'].s = { font: { bold: true, sz: 16, color: { rgb: NU_NAVY } } };
    ['A5','B5','A17','B17'].forEach(cell => { if (overviewWs[cell]) overviewWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, overviewWs, 'Overview');

    // ============================================
    // 📄 SHEET 2 — Top Events
    // ============================================
    const topEventsData = [
        ['TOP PERFORMING EVENTS'],
        [],
        ['#', 'Event', 'Registrations', 'Fill Rate (%)']
    ];
    const topFullTitles = @json($eventAnalytics['charts']['top_events']['full_titles'] ?? []);
    const topData       = @json($eventAnalytics['charts']['top_events']['data'] ?? []);
    const topFill       = @json($eventAnalytics['charts']['top_events']['fill_rates'] ?? []);
    topFullTitles.forEach((title, i) => {
        topEventsData.push([i + 1, title, topData[i] ?? 0, topFill[i] ?? 0]);
    });

    const topWs = XLSX.utils.aoa_to_sheet(topEventsData);
    topWs['!cols'] = [{ wch: 5 }, { wch: 50 }, { wch: 16 }, { wch: 15 }];
    topWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }];
    if (topWs['A1']) topWs['A1'].s = titleStyle;
    ['A3','B3','C3','D3'].forEach(cell => { if (topWs[cell]) topWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, topWs, 'Top Events');

    // ============================================
    // 📄 SHEET 3 — Format Breakdown
    // ============================================
    const fmtLabels = @json($eventAnalytics['charts']['event_type_breakdown']['labels'] ?? []);
    const fmtData   = @json($eventAnalytics['charts']['event_type_breakdown']['data'] ?? []);

    const fmtSheetData = [
        ['EVENT FORMAT BREAKDOWN'],
        [],
        ['Format', 'Count']
    ];
    fmtLabels.forEach((label, i) => {
        fmtSheetData.push([label, fmtData[i] ?? 0]);
    });

    const fmtWs = XLSX.utils.aoa_to_sheet(fmtSheetData);
    fmtWs['!cols'] = [{ wch: 24 }, { wch: 12 }];
    fmtWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }];
    if (fmtWs['A1']) fmtWs['A1'].s = titleStyle;
    ['A3','B3'].forEach(cell => { if (fmtWs[cell]) fmtWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, fmtWs, 'Format Breakdown');

    // ============================================
    // 📄 SHEET 4 — Status Distribution
    // ============================================
    const statusLabels = @json($eventAnalytics['charts']['status_distribution']['labels'] ?? []);
    const statusData   = @json($eventAnalytics['charts']['status_distribution']['data'] ?? []);

    const statusSheetData = [
        ['EVENT STATUS DISTRIBUTION'],
        [],
        ['Status', 'Count']
    ];
    statusLabels.forEach((label, i) => {
        statusSheetData.push([label, statusData[i] ?? 0]);
    });

    const statusWs = XLSX.utils.aoa_to_sheet(statusSheetData);
    statusWs['!cols'] = [{ wch: 24 }, { wch: 12 }];
    statusWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }];
    if (statusWs['A1']) statusWs['A1'].s = titleStyle;
    ['A3','B3'].forEach(cell => { if (statusWs[cell]) statusWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, statusWs, 'Status Distribution');

    // ============================================
    // 📄 SHEET 5 — Capacity & Fill Rate
    // ============================================
    const capacitySheetData = [
        ['CAPACITY & FILL RATE (Upcoming & Active Events)'],
        [],
        ['Event', 'Capacity', 'Registered', 'Fill Rate (%)']
    ];

    @forelse($eventAnalytics['charts']['capacity_analysis'] as $evt)
    capacitySheetData.push([
        {!! json_encode($evt['title']) !!},
        {{ $evt['capacity'] }},
        {{ $evt['registered'] }},
        {{ $evt['fill_rate'] }}
    ]);
    @empty
    capacitySheetData.push(['No upcoming events', 0, 0, 0]);
    @endforelse

    const capacityWs = XLSX.utils.aoa_to_sheet(capacitySheetData);
    capacityWs['!cols'] = [{ wch: 40 }, { wch: 12 }, { wch: 14 }, { wch: 15 }];
    capacityWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }];
    if (capacityWs['A1']) capacityWs['A1'].s = titleStyle;
    ['A3','B3','C3','D3'].forEach(cell => { if (capacityWs[cell]) capacityWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, capacityWs, 'Capacity & Fill Rate');

    // ============================================
    // 📄 SHEET 6 — Top Programs
    // ============================================
    const progLabels = @json($eventAnalytics['charts']['top_programs']['labels'] ?? []);
    const progData   = @json($eventAnalytics['charts']['top_programs']['data'] ?? []);

    const progSheetData = [
        ['TOP PROGRAMS ATTENDING EVENTS'],
        [],
        ['Program', 'Registrations']
    ];
    progLabels.forEach((label, i) => {
        progSheetData.push([label, progData[i] ?? 0]);
    });

    const progWs = XLSX.utils.aoa_to_sheet(progSheetData);
    progWs['!cols'] = [{ wch: 30 }, { wch: 16 }];
    progWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }];
    if (progWs['A1']) progWs['A1'].s = titleStyle;
    ['A3','B3'].forEach(cell => { if (progWs[cell]) progWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, progWs, 'Top Programs');

    // ============================================
    // 📄 SHEET 7 — Recent Registrations
    // ============================================
    const recentSheetData = [
        ['RECENT REGISTRATION ACTIVITY'],
        [],
        ['#', 'Alumni', 'Event', 'Registered']
    ];

    @forelse($eventAnalytics['recent_registrations'] as $idx => $reg)
    recentSheetData.push([
        {{ $idx + 1 }},
        {!! json_encode($reg['alumni_name']) !!},
        {!! json_encode($reg['event_title']) !!},
        {!! json_encode($reg['time_ago']) !!}
    ]);
    @empty
    recentSheetData.push(['—', 'No recent registrations', '—', '—']);
    @endforelse

    const recentWs = XLSX.utils.aoa_to_sheet(recentSheetData);
    recentWs['!cols'] = [{ wch: 5 }, { wch: 28 }, { wch: 50 }, { wch: 24 }];
    recentWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }];
    if (recentWs['A1']) recentWs['A1'].s = titleStyle;
    ['A3','B3','C3','D3'].forEach(cell => { if (recentWs[cell]) recentWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, recentWs, 'Recent Registrations');

    // ============================================
    // 📄 SHEET 8 — Full Event Performance ⭐
    // ============================================
    const perfSheetData = [
        ['FULL EVENT PERFORMANCE TABLE'],
        ['All events — sorted by start date (most recent first)'],
        [],
        ['#', 'Event', 'Format', 'Start Date', 'End Date', 'Registered', 'Capacity', 'Fill Rate (%)', 'Status']
    ];

    @forelse($eventAnalytics['event_performance'] as $idx => $evt)
    perfSheetData.push([
        {{ $idx + 1 }},
        {!! json_encode($evt['title']) !!},
        {!! json_encode($evt['event_type']) !!},
        {!! json_encode(\Carbon\Carbon::parse($evt['start_date'])->format('M d, Y')) !!},
        {!! json_encode(\Carbon\Carbon::parse($evt['end_date'])->format('M d, Y')) !!},
        {{ $evt['registered'] }},
        {{ $evt['capacity'] }},
        {{ $evt['fill_rate'] }},
        {!! json_encode($evt['status']) !!}
    ]);
    @empty
    perfSheetData.push(['—', 'No events found', '—', '—', '—', 0, 0, 0, '—']);
    @endforelse

    const perfWs = XLSX.utils.aoa_to_sheet(perfSheetData);
    perfWs['!cols'] = [
        { wch: 5 },   // #
        { wch: 50 },  // Event
        { wch: 16 },  // Format
        { wch: 14 },  // Start
        { wch: 14 },  // End
        { wch: 12 },  // Registered
        { wch: 12 },  // Capacity
        { wch: 14 },  // Fill Rate
        { wch: 14 },  // Status
    ];
    perfWs['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 8 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 8 } },
    ];
    if (perfWs['A1']) perfWs['A1'].s = { font: { bold: true, sz: 14, color: { rgb: NU_NAVY } } };
    if (perfWs['A2']) perfWs['A2'].s = { font: { italic: true, sz: 10, color: { rgb: '666666' } } };
    ['A4','B4','C4','D4','E4','F4','G4','H4','I4'].forEach(cell => {
        if (perfWs[cell]) perfWs[cell].s = headerStyle;
    });

    // Freeze header row for scrolling
    perfWs['!freeze'] = { ySplit: 4 };

    XLSX.utils.book_append_sheet(wb, perfWs, 'Event Performance');

    // ============================================
    // 💾 SAVE
    // ============================================
    const filename = `Event_Analytics_${new Date().toISOString().slice(0, 10)}.xlsx`;
    XLSX.writeFile(wb, filename);
    showToast('Event Analytics exported to Excel', 'success');
}

// ============================================
// ✨ MENTORSHIP-SPECIFIC EXPORTERS
// ============================================

// Pre-load NU logo as base64 so addImage works
async function loadImageAsDataURL(url) {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.crossOrigin = 'Anonymous';
        img.onload = function () {
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            resolve(canvas.toDataURL('image/png'));
        };
        img.onerror = reject;
        img.src = url;
    });
}

async function exportMentorshipPDF(data, title) {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        showToast('PDF library not loaded. Please refresh the page.', 'error');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('l', 'mm', 'a4');

    const NU_NAVY = [50, 65, 140];
    const NU_GOLD = [251, 209, 23];

    // 🖼️ Pre-load NU logo
    let nuLogoDataUrl = null;
    try {
        nuLogoDataUrl = await loadImageAsDataURL('/assets/logos/nu_logo_white.png');
    } catch (e) {
        console.warn('Could not load NU logo for PDF:', e);
    }

    const pageWidth  = 297;
    const pageHeight = 210;
    const marginX    = 8;

    // ============================================
    // 🎨 HEADER BANNER (navy background + gold underline)
    // ============================================
    doc.setFillColor(...NU_NAVY);
    doc.rect(0, 0, pageWidth, 26, 'F');

    // Gold underline strip
    doc.setFillColor(...NU_GOLD);
    doc.rect(0, 26, pageWidth, 1.5, 'F');

    // ---- Try to embed NU logo (top-left, white on navy) ----
    // Path points to /public/assets/logos/nu_logo_white.png
    // If it fails, header text still renders fine.
    let logoDrawn = false;
    if (nuLogoDataUrl) {
        try {
            doc.addImage(nuLogoDataUrl, 'PNG', marginX, 4, 18, 18, undefined, 'FAST');
            logoDrawn = true;
        } catch (e) {
            console.warn('Failed to add logo:', e);
        }
    }

    const textStartX = logoDrawn ? marginX + 22 : marginX;

    // ---- Title ----
    doc.setTextColor(255, 255, 255);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(15);
    doc.text('Mentorship Applications Report', textStartX, 12);

    // ---- Subtitle ----
    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);
    doc.setTextColor(230, 235, 255);
    doc.text('LumiNUs Alumni Portal  •  National University Lipa', textStartX, 18.5);

    // ---- Generated timestamp (right-aligned) ----
    doc.setFontSize(8.5);
    doc.setTextColor(230, 235, 255);
    const generatedStr = 'Generated: ' + new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' });
    doc.text(generatedStr, pageWidth - marginX, 18.5, { align: 'right' });

    // ============================================
    // 📊 SUMMARY STRIP (gold accents on white)
    // ============================================
    const totalApps = Math.max(data.length - 1, 0);
    const pending   = {{ $pendingMentorships ?? 0 }};
    const active    = {{ $activeMentorships ?? 0 }};
    const completed = {{ $completedMentorships ?? 0 }};
    const declined  = {{ $declinedMentorships ?? 0 }};

    let summaryY = 34;

    // Small "SUMMARY" label with gold accent bar
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, summaryY - 3.5, 2, 5, 'F');

    doc.setTextColor(...NU_NAVY);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(9);
    doc.text('SUMMARY', marginX + 4, summaryY);

    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);

    // Build the summary line — highlight the numbers in navy bold
    const pills = [
        { label: 'Total:',     value: totalApps, color: NU_NAVY },
        { label: 'Pending:',   value: pending,   color: [180, 120, 0] },   // amber
        { label: 'Active:',    value: active,    color: [5, 120, 80] },    // green
        { label: 'Completed:', value: completed, color: [90, 90, 90] },    // gray
        { label: 'Declined:',  value: declined,  color: [170, 30, 30] },   // red
    ];

    let pillX = marginX + 25;
    const pillSpacing = 42;

    pills.forEach(p => {
        // Label (gray)
        doc.setTextColor(100, 100, 100);
        doc.setFont(undefined, 'normal');
        doc.text(p.label, pillX, summaryY);

        // Value (colored bold)
        const labelWidth = doc.getTextWidth(p.label);
        doc.setTextColor(...p.color);
        doc.setFont(undefined, 'bold');
        doc.text(String(p.value), pillX + labelWidth + 1, summaryY);

        pillX += pillSpacing;
    });

    // Divider line under summary
    doc.setDrawColor(220, 225, 240);
    doc.setLineWidth(0.3);
    doc.line(marginX, summaryY + 3.5, pageWidth - marginX, summaryY + 3.5);

    // ============================================
    // 📋 TABLE
    // ============================================
    // ✅ FIT columns inside 297 - 2*marginX = 281mm usable
    //    Total of these widths = 279mm  (2mm slack)
    doc.autoTable({
        head: [data[0]],
        body: data.slice(1),
        startY: summaryY + 6,
        theme: 'grid',
        styles: {
            font: 'helvetica',
            fontSize: 6.5,
            cellPadding: 1.4,
            overflow: 'linebreak',
            valign: 'middle',
            textColor: [40, 40, 50],
            lineColor: [225, 228, 240],
            lineWidth: 0.15,
        },
        headStyles: {
            fillColor: NU_NAVY,
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            halign: 'center',
            valign: 'middle',
            fontSize: 6.5,
            cellPadding: 1.8,
        },
        alternateRowStyles: {
            fillColor: [248, 249, 253],
        },
        columnStyles: {
            0:  { halign: 'center', cellWidth: 6  },   // #                (was 8)
            1:  { cellWidth: 22 },                     // Alumni Name      (was 26)
            2:  { cellWidth: 30 },                     // Email            (was 34)
            3:  { cellWidth: 14 },                     // Program          (was 18)
            4:  { halign: 'center', cellWidth: 14 },   // Event Type       (was 16)
            5:  { cellWidth: 26 },                     // Event Title      (was 32)
            6:  { cellWidth: 16 },                     // Target Audience  (was 20)
            7:  { halign: 'center', cellWidth: 10 },   // Capacity         (was 13)
            8:  { halign: 'center', cellWidth: 14 },   // Proposed Date    (was 17)
            9:  { halign: 'center', cellWidth: 11 },   // Start            (was 13)
            10: { halign: 'center', cellWidth: 11 },   // End              (was 13)
            11: { halign: 'center', cellWidth: 12 },   // Format           (was 14)
            12: { cellWidth: 20 },                     // Co-Speakers      (was 22)
            13: { cellWidth: 36 },                     // Description      (was 33 — slightly wider to compensate)
            14: { halign: 'center', cellWidth: 13 },   // Status           (was 15)
            15: { cellWidth: 20 },                     // Submitted At     (was 24)
            // Total = 6+22+30+14+14+26+16+10+14+11+11+12+20+36+13+20 = 275mm
            // Page has 297 - (6*2) = 285mm available → 10mm slack ✅
        },
        didParseCell: function (hookData) {
            // 🎨 Color-code Status column (index 14) with NU palette
            if (hookData.section === 'body' && hookData.column.index === 14) {
                const val = String(hookData.cell.raw || '').toLowerCase();
                if (val.includes('pending')) {
                    hookData.cell.styles.textColor = [180, 120, 0];
                    hookData.cell.styles.fontStyle = 'bold';
                    hookData.cell.styles.fillColor = [255, 248, 225];
                } else if (val.includes('active')) {
                    hookData.cell.styles.textColor = [5, 120, 80];
                    hookData.cell.styles.fontStyle = 'bold';
                    hookData.cell.styles.fillColor = [230, 250, 240];
                } else if (val.includes('completed')) {
                    hookData.cell.styles.textColor = [80, 80, 95];
                    hookData.cell.styles.fontStyle = 'bold';
                    hookData.cell.styles.fillColor = [240, 242, 248];
                } else if (val.includes('declined')) {
                    hookData.cell.styles.textColor = [170, 30, 30];
                    hookData.cell.styles.fontStyle = 'bold';
                    hookData.cell.styles.fillColor = [255, 235, 235];
                }
            }
            // Column 0 (#) — NU navy bold
            if (hookData.section === 'body' && hookData.column.index === 0) {
                hookData.cell.styles.textColor = NU_NAVY;
                hookData.cell.styles.fontStyle = 'bold';
            }
            // Column 1 (Name) — bold navy
            if (hookData.section === 'body' && hookData.column.index === 1) {
                hookData.cell.styles.fontStyle = 'bold';
                hookData.cell.styles.textColor = [30, 40, 90];
            }
        },
        margin: { top: 30, left: marginX, right: marginX, bottom: 12 },
        didDrawPage: function (hookData) {
            // ============================================
            // 🦶 FOOTER — gold accent line + page number + branding
            // ============================================
            const pageCount = doc.internal.getNumberOfPages();

            // Gold footer accent line
            doc.setFillColor(...NU_GOLD);
            doc.rect(0, pageHeight - 7, pageWidth, 0.8, 'F');

            // Footer text left
            doc.setFontSize(7);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(120, 130, 155);
            doc.text('LumiNUs Alumni Portal — Mentorship Report', marginX, pageHeight - 3.5);

            // Page number right
            doc.setTextColor(...NU_NAVY);
            doc.setFont(undefined, 'bold');
            const pageStr = `Page ${hookData.pageNumber} of ${pageCount}`;
            doc.text(pageStr, pageWidth - marginX, pageHeight - 3.5, { align: 'right' });

            // Reset for body content on subsequent pages
            doc.setFontSize(6.5);
        }
    });

    const filename = `Mentorship_Applications_${new Date().toISOString().slice(0, 10)}.pdf`;
    doc.save(filename);
    showToast(`Exported ${totalApps} applications to PDF`, 'success');
}

function exportMentorshipExcel(data, title) {
    if (!window.XLSX) {
        showToast('Excel library not loaded. Please refresh the page.', 'error');
        return;
    }

    const wb = XLSX.utils.book_new();

    // ---- Build styled sheet data with summary above the table ----
    const totalApps = Math.max(data.length - 1, 0);
    const pending   = {{ $pendingMentorships ?? 0 }};
    const active    = {{ $activeMentorships ?? 0 }};
    const completed = {{ $completedMentorships ?? 0 }};
    const declined  = {{ $declinedMentorships ?? 0 }};

    const summaryRows = [
        ['Mentorship Applications Report'],
        ['Generated:', new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' })],
        [],
        ['Summary'],
        ['Total', totalApps],
        ['Pending', pending],
        ['Active', active],
        ['Completed', completed],
        ['Declined', declined],
        [],
    ];

    const sheetData = [...summaryRows, ...data];

    const ws = XLSX.utils.aoa_to_sheet(sheetData);

    // ---- Column widths ----
    ws['!cols'] = [
        { wch: 5 },   // #
        { wch: 22 },  // Name
        { wch: 28 },  // Email
        { wch: 22 },  // Program
        { wch: 14 },  // Event Type
        { wch: 30 },  // Event Title
        { wch: 18 },  // Audience
        { wch: 10 },  // Capacity
        { wch: 14 },  // Proposed Date
        { wch: 10 },  // Start
        { wch: 10 },  // End
        { wch: 10 },  // Format
        { wch: 22 },  // Co-speakers
        { wch: 40 },  // Description
        { wch: 12 },  // Status
        { wch: 20 },  // Submitted At
    ];

    // ---- Merge title cell A1:P1 ----
    ws['!merges'] = ws['!merges'] || [];
    ws['!merges'].push({ s: { r: 0, c: 0 }, e: { r: 0, c: 15 } });
    ws['!merges'].push({ s: { r: 3, c: 0 }, e: { r: 3, c: 15 } });

    XLSX.utils.book_append_sheet(wb, ws, 'Mentorship Applications');

    const filename = `Mentorship_Applications_${new Date().toISOString().slice(0, 10)}.xlsx`;
    XLSX.writeFile(wb, filename);
    showToast(`Exported ${totalApps} applications to Excel`, 'success');
}

// ============================================
// ✨ USER ANALYTICS EXPORTERS
// ============================================

async function exportUsersPDF() {
    if (!window.jspdf || !window.jspdf.jsPDF) {
        showToast('PDF library not loaded. Please refresh the page.', 'error');
        return;
    }

    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4'); // portrait for text-heavy content

    const NU_NAVY = [50, 65, 140];
    const NU_GOLD = [251, 209, 23];

    const pageWidth  = 210;
    const pageHeight = 297;
    const marginX    = 12;

    // ---- Preload NU logo ----
    let nuLogoDataUrl = null;
    try {
        nuLogoDataUrl = await loadImageAsDataURL('/assets/logos/nu_logo_white.png');
    } catch (e) {
        console.warn('Could not load NU logo for PDF:', e);
    }

    // ============================================
    // 🎨 HEADER
    // ============================================
    doc.setFillColor(...NU_NAVY);
    doc.rect(0, 0, pageWidth, 26, 'F');
    doc.setFillColor(...NU_GOLD);
    doc.rect(0, 26, pageWidth, 1.5, 'F');

    let logoDrawn = false;
    if (nuLogoDataUrl) {
        try {
            doc.addImage(nuLogoDataUrl, 'PNG', marginX, 4, 18, 18, undefined, 'FAST');
            logoDrawn = true;
        } catch (e) {}
    }
    const textStartX = logoDrawn ? marginX + 22 : marginX;

    doc.setTextColor(255, 255, 255);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(15);
    doc.text('User Analytics Report', textStartX, 12);

    doc.setFont(undefined, 'normal');
    doc.setFontSize(9);
    doc.setTextColor(230, 235, 255);
    doc.text('LumiNUs Alumni Portal  •  National University Lipa', textStartX, 18.5);

    const generatedStr = 'Generated: ' + new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' });
    doc.setFontSize(8.5);
    doc.text(generatedStr, pageWidth - marginX, 18.5, { align: 'right' });

    // ============================================
    // 📊 KPI SUMMARY
    // ============================================
    let y = 36;

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFont(undefined, 'bold');
    doc.setFontSize(11);
    doc.text('KEY METRICS', marginX + 4, y);

    y += 6;
    const kpis = [
        ['Verified Alumni',        '{{ $verifiedAlumniCount }}'],
        ['Pending Verification',   '{{ $pendingVerificationCount }}'],
        ['Active (Last 7 Days)',   '{{ $activity["activeLast7Days"] }}'],
        ['Online Now',             '{{ $activity["onlineNow"] }}'],
        ['Dormant (30+ Days)',     '{{ $activity["dormantCount"] }}'],
        ['Avg Posts / User',       '{{ $activity["avgPostsPerUser"] }}'],
        ['Users With Followers',   '{{ $activity["usersWithFollowers"] }}'],
        ['Users Following Others', '{{ $activity["usersFollowing"] }}'],
        ['New Users (12 months)',  '{{ $userGrowth->sum("count") }}'],
    ];

    doc.autoTable({
        body: kpis.map(([k, v]) => [k, v]),
        startY: y,
        theme: 'grid',
        styles: {
            font: 'helvetica',
            fontSize: 9,
            cellPadding: 2.2,
            textColor: [40, 40, 50],
            lineColor: [225, 228, 240],
            lineWidth: 0.15,
        },
        columnStyles: {
            0: { cellWidth: 80, fontStyle: 'bold', textColor: NU_NAVY },
            1: { cellWidth: 30, halign: 'center', fontStyle: 'bold' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📈 ACTIVITY OVERVIEW (Last 30 Days)
    // ============================================
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('ACTIVITY OVERVIEW (Last 30 Days)', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['Activity Type', 'Total', 'Unique Users']],
        body: [
            ['Posts',       '{{ $activity["postsCount"] }}',       '{{ $activity["usersWhoPosted"] }}'],
            ['Comments',    '{{ $activity["commentsCount"] }}',    '{{ $activity["usersWhoCommented"] }}'],
            ['Reactions',   '{{ $activity["reactionsCount"] }}',   '{{ $activity["usersWhoReacted"] }}'],
            ['Reposts',     '{{ $activity["repostsCount"] }}',     '—'],
            ['Direct Msgs', '{{ $activity["messagesCount"] }}',    '{{ $activity["usersWhoMessaged"] }}'],
            ['Group Msgs',  '{{ $activity["groupMessagesCount"] }}', '—'],
            ['Event Regs',  '{{ $activity["registrationsCount"] }}','{{ $activity["usersWhoRegistered"] }}'],
        ],
        startY: y,
        theme: 'grid',
        styles: {
            font: 'helvetica',
            fontSize: 9,
            cellPadding: 2.2,
            textColor: [40, 40, 50],
            lineColor: [225, 228, 240],
            lineWidth: 0.15,
        },
        headStyles: {
            fillColor: NU_NAVY,
            textColor: [255, 255, 255],
            fontStyle: 'bold',
            halign: 'center',
        },
        columnStyles: {
            0: { cellWidth: 80 },
            1: { cellWidth: 30, halign: 'center' },
            2: { cellWidth: 30, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📉 MONTHLY ACTIVE USERS
    // ============================================
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('MONTHLY ACTIVE USERS (Last 6 Months)', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['Month', 'Active Users']],
        body: [
            @foreach($activity['months'] as $m)
            ['{{ $m["label"] }}', '{{ $m["count"] }}'],
            @endforeach
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 9, cellPadding: 2.2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { cellWidth: 80 },
            1: { cellWidth: 30, halign: 'center' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 👥 TOP CONTRIBUTORS
    // ============================================
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('TOP CONTRIBUTORS (Last 30 Days)', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Name', 'Program', 'Posts', 'Comments', 'Reactions', 'Score']],
        body: [
            @forelse($activity['topContributors'] as $tc)
            [
                '{{ $loop->iteration }}',
                {!! json_encode($tc->first_name . ' ' . $tc->last_name) !!},
                {!! json_encode($tc->program ?? '—') !!},
                '{{ $tc->post_count }}',
                '{{ $tc->comment_count }}',
                '{{ $tc->reaction_count }}',
                '{{ $tc->activity_score }}'
            ],
            @empty
            ['—', 'No contributor activity yet', '—', '—', '—', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8.5, cellPadding: 2, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { halign: 'center', cellWidth: 8 },
            1: { cellWidth: 50 },
            2: { cellWidth: 32 },
            3: { halign: 'center', cellWidth: 18 },
            4: { halign: 'center', cellWidth: 22 },
            5: { halign: 'center', cellWidth: 22 },
            6: { halign: 'center', cellWidth: 18, fontStyle: 'bold', textColor: NU_NAVY },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 💤 DORMANT USERS
    // ============================================
    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('DORMANT USERS (Top 10)', marginX + 4, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Name', 'Email', 'Last Login']],
        body: [
            @forelse($activity['dormantUsers'] as $du)
            [
                '{{ $loop->iteration }}',
                {!! json_encode($du->first_name . ' ' . $du->last_name) !!},
                {!! json_encode($du->email) !!},
                '{{ $du->last_login_at ? \Carbon\Carbon::parse($du->last_login_at)->format("M d, Y") : "Never" }}'
            ],
            @empty
            ['—', 'No dormant users', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: { font: 'helvetica', fontSize: 8, cellPadding: 1.8, lineColor: [225,228,240], lineWidth: 0.15 },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center' },
        columnStyles: {
            0: { halign: 'center', cellWidth: 8 },
            1: { cellWidth: 50 },
            2: { cellWidth: 60 },
            3: { halign: 'center', cellWidth: 30 },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
    });
    y = doc.lastAutoTable.finalY + 8;

    // ============================================
    // 📍 FULL GEOLOCATION TABLE
    // ============================================
    if (y > pageHeight - 60) {
        doc.addPage();
        y = 20;
    }

    doc.setFillColor(...NU_GOLD);
    doc.rect(marginX, y - 3.5, 2, 5, 'F');
    doc.setTextColor(...NU_NAVY);
    doc.setFontSize(11);
    doc.text('ALUMNI GEOLOCATION', marginX + 4, y);
    y += 5;

    doc.setFontSize(8);
    doc.setFont(undefined, 'normal');
    doc.setTextColor(100, 100, 100);
    doc.text('Addresses with verified coordinates — sorted by region', marginX, y);
    y += 5;

    doc.autoTable({
        head: [['#', 'Name', 'Region', 'Province', 'Municipality', 'Barangay', 'Latitude', 'Longitude']],
        body: [
            @forelse($alumniLocations ?? [] as $loc)
            [
                '{{ $loop->iteration }}',
                {!! json_encode($loc->first_name . ' ' . $loc->last_name) !!},
                {!! json_encode($loc->region ?? '—') !!},
                {!! json_encode($loc->province ?? '—') !!},
                {!! json_encode($loc->municipality ?? '—') !!},
                {!! json_encode($loc->barangay ?? '—') !!},
                '{{ number_format((float) $loc->latitude, 6) }}',
                '{{ number_format((float) $loc->longitude, 6) }}'
            ],
            @empty
            ['—', 'No alumni with location data', '—', '—', '—', '—', '—', '—'],
            @endforelse
        ],
        startY: y,
        theme: 'grid',
        styles: {
            font: 'helvetica',
            fontSize: 7,
            cellPadding: 1.5,
            overflow: 'linebreak',
            lineColor: [225,228,240],
            lineWidth: 0.15,
        },
        headStyles: { fillColor: NU_NAVY, textColor: [255,255,255], fontStyle: 'bold', halign: 'center', fontSize: 7.5 },
        columnStyles: {
            0: { halign: 'center', cellWidth: 7 },
            1: { cellWidth: 30 },
            2: { cellWidth: 22 },
            3: { cellWidth: 22 },
            4: { cellWidth: 24 },
            5: { cellWidth: 24 },
            6: { halign: 'right', cellWidth: 19, font: 'courier' },
            7: { halign: 'right', cellWidth: 19, font: 'courier' },
        },
        alternateRowStyles: { fillColor: [248, 249, 253] },
        margin: { left: marginX, right: marginX },
        didDrawPage: function(hookData) {
            // Page footer
            const pageCount = doc.internal.getNumberOfPages();
            doc.setFillColor(...NU_GOLD);
            doc.rect(0, pageHeight - 7, pageWidth, 0.8, 'F');

            doc.setFontSize(7);
            doc.setFont(undefined, 'normal');
            doc.setTextColor(120, 130, 155);
            doc.text('LumiNUs Alumni Portal — User Analytics Report', marginX, pageHeight - 3.5);

            doc.setTextColor(...NU_NAVY);
            doc.setFont(undefined, 'bold');
            const pageStr = `Page ${hookData.pageNumber} of ${pageCount}`;
            doc.text(pageStr, pageWidth - marginX, pageHeight - 3.5, { align: 'right' });
        }
    });

    const filename = `User_Analytics_${new Date().toISOString().slice(0, 10)}.pdf`;
    doc.save(filename);
    showToast('User Analytics exported to PDF', 'success');
}

function exportUsersExcel() {
    if (!window.XLSX) {
        showToast('Excel library not loaded. Please refresh the page.', 'error');
        return;
    }

    const wb = XLSX.utils.book_new();

    const NU_NAVY = '32418C';
    const NU_GOLD = 'FBD117';

    const headerStyle = {
        font: { bold: true, color: { rgb: 'FFFFFF' }, sz: 11 },
        fill: { fgColor: { rgb: NU_NAVY } },
        alignment: { horizontal: 'center', vertical: 'center' },
        border: { top: { style: 'thin' }, bottom: { style: 'thin' }, left: { style: 'thin' }, right: { style: 'thin' } }
    };

    const titleStyle = {
        font: { bold: true, sz: 14, color: { rgb: NU_NAVY } },
        alignment: { horizontal: 'left', vertical: 'center' }
    };

    // ============================================
    // 📄 SHEET 1 — Overview
    // ============================================
    const overviewData = [
        ['User Analytics Report'],
        ['LumiNUs Alumni Portal — National University Lipa'],
        ['Generated:', new Date().toLocaleString('en-PH', { timeZone: 'Asia/Manila' })],
        [],
        ['KEY METRICS', 'Value'],
        ['Verified Alumni',        {{ $verifiedAlumniCount }}],
        ['Pending Verification',   {{ $pendingVerificationCount }}],
        ['Active (Last 7 Days)',   {{ $activity["activeLast7Days"] }}],
        ['Online Now',             {{ $activity["onlineNow"] }}],
        ['Dormant (30+ Days)',     {{ $activity["dormantCount"] }}],
        ['Avg Posts / User',       {{ $activity["avgPostsPerUser"] }}],
        ['Users With Followers',   {{ $activity["usersWithFollowers"] }}],
        ['Users Following Others', {{ $activity["usersFollowing"] }}],
        ['New Users (12 months)',  {{ $userGrowth->sum("count") }}],
        [],
        ['ACTIVITY OVERVIEW (Last 30 Days)', 'Total', 'Unique Users'],
        ['Posts',       {{ $activity["postsCount"] }},       {{ $activity["usersWhoPosted"] }}],
        ['Comments',    {{ $activity["commentsCount"] }},    {{ $activity["usersWhoCommented"] }}],
        ['Reactions',   {{ $activity["reactionsCount"] }},   {{ $activity["usersWhoReacted"] }}],
        ['Reposts',     {{ $activity["repostsCount"] }},     0],
        ['Direct Msgs', {{ $activity["messagesCount"] }},    {{ $activity["usersWhoMessaged"] }}],
        ['Group Msgs',  {{ $activity["groupMessagesCount"] }}, 0],
        ['Event Regs',  {{ $activity["registrationsCount"] }}, {{ $activity["usersWhoRegistered"] }}],
        [],
        ['MONTHLY ACTIVE USERS', 'Count'],
        @foreach($activity['months'] as $m)
        ['{{ $m["label"] }}', {{ $m["count"] }}],
        @endforeach
    ];

    const overviewWs = XLSX.utils.aoa_to_sheet(overviewData);
    overviewWs['!cols'] = [{ wch: 32 }, { wch: 18 }, { wch: 16 }, { wch: 16 }];
    overviewWs['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 3 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 3 } },
    ];

    // Apply styles
    ['A1','A2','A3','A5','B5','A16','B16','C16','A25','B25'].forEach(cell => {
        if (overviewWs[cell]) overviewWs[cell].s = titleStyle;
    });
    ['A1','A2','A5','B5','A16','B16','C16','A25','B25'].forEach(cell => {
        if (overviewWs[cell]) overviewWs[cell].s = headerStyle;
    });
    ['A1'].forEach(cell => { if (overviewWs[cell]) overviewWs[cell].s = { font: { bold: true, sz: 16, color: { rgb: NU_NAVY } } }; });

    XLSX.utils.book_append_sheet(wb, overviewWs, 'Overview');

    // ============================================
    // 📄 SHEET 2 — Top Contributors
    // ============================================
    const contributorsData = [
        ['TOP CONTRIBUTORS (Last 30 Days)'],
        [],
        ['#', 'Name', 'Program', 'Posts', 'Comments', 'Reactions', 'Activity Score']
    ];

    @forelse($activity['topContributors'] as $tc)
    contributorsData.push([
        {{ $loop->iteration }},
        {!! json_encode($tc->first_name . ' ' . $tc->last_name) !!},
        {!! json_encode($tc->program ?? '—') !!},
        {{ $tc->post_count }},
        {{ $tc->comment_count }},
        {{ $tc->reaction_count }},
        {{ $tc->activity_score }}
    ]);
    @empty
    contributorsData.push(['—', 'No contributor activity yet', '—', 0, 0, 0, 0]);
    @endforelse

    const contribWs = XLSX.utils.aoa_to_sheet(contributorsData);
    contribWs['!cols'] = [{ wch: 6 }, { wch: 28 }, { wch: 20 }, { wch: 10 }, { wch: 12 }, { wch: 12 }, { wch: 15 }];
    contribWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 6 } }];
    if (contribWs['A1']) contribWs['A1'].s = { font: { bold: true, sz: 13, color: { rgb: NU_NAVY } } };
    ['A3','B3','C3','D3','E3','F3','G3'].forEach(cell => {
        if (contribWs[cell]) contribWs[cell].s = headerStyle;
    });

    XLSX.utils.book_append_sheet(wb, contribWs, 'Top Contributors');

    // ============================================
    // 📄 SHEET 3 — Dormant Users
    // ============================================
    const dormantData = [
        ['DORMANT USERS (No login in 30+ days)'],
        [],
        ['#', 'Name', 'Email', 'Last Login']
    ];

    @forelse($activity['dormantUsers'] as $du)
    dormantData.push([
        {{ $loop->iteration }},
        {!! json_encode($du->first_name . ' ' . $du->last_name) !!},
        {!! json_encode($du->email) !!},
        {!! json_encode($du->last_login_at ? \Carbon\Carbon::parse($du->last_login_at)->format('M d, Y') : 'Never') !!}
    ]);
    @empty
    dormantData.push(['—', 'No dormant users', '—', '—']);
    @endforelse

    const dormantWs = XLSX.utils.aoa_to_sheet(dormantData);
    dormantWs['!cols'] = [{ wch: 6 }, { wch: 30 }, { wch: 36 }, { wch: 20 }];
    dormantWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 3 } }];
    if (dormantWs['A1']) dormantWs['A1'].s = { font: { bold: true, sz: 13, color: { rgb: NU_NAVY } } };
    ['A3','B3','C3','D3'].forEach(cell => {
        if (dormantWs[cell]) dormantWs[cell].s = headerStyle;
    });

    XLSX.utils.book_append_sheet(wb, dormantWs, 'Dormant Users');

    // ============================================
    // 📄 SHEET 4 — Alumni by Program
    // ============================================
    const programData = [
        ['ALUMNI BY PROGRAM'],
        [],
        ['Program', 'Count']
    ];
    @foreach($alumniByProgram as $p)
    programData.push([{!! json_encode($p->program) !!}, {{ $p->count }}]);
    @endforeach

    const programWs = XLSX.utils.aoa_to_sheet(programData);
    programWs['!cols'] = [{ wch: 30 }, { wch: 12 }];
    programWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }];
    if (programWs['A1']) programWs['A1'].s = { font: { bold: true, sz: 13, color: { rgb: NU_NAVY } } };
    ['A3','B3'].forEach(cell => { if (programWs[cell]) programWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, programWs, 'By Program');

    // ============================================
    // 📄 SHEET 5 — Alumni by Year
    // ============================================
    const yearData = [
        ['ALUMNI BY YEAR GRADUATED'],
        [],
        ['Year', 'Count']
    ];
    @foreach($alumniByYear as $y)
    yearData.push([{{ $y['year'] }}, {{ $y['count'] }}]);
    @endforeach

    const yearWs = XLSX.utils.aoa_to_sheet(yearData);
    yearWs['!cols'] = [{ wch: 12 }, { wch: 12 }];
    yearWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }];
    if (yearWs['A1']) yearWs['A1'].s = { font: { bold: true, sz: 13, color: { rgb: NU_NAVY } } };
    ['A3','B3'].forEach(cell => { if (yearWs[cell]) yearWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, yearWs, 'By Year');

    // ============================================
    // 📄 SHEET 6 — Alumni by Type
    // ============================================
    const typeData = [
        ['ALUMNI BY TYPE'],
        [],
        ['Type', 'Count']
    ];
    @foreach($alumniByType as $t)
    typeData.push([{!! json_encode(ucfirst($t->alumni_type)) !!}, {{ $t->count }}]);
    @endforeach

    const typeWs = XLSX.utils.aoa_to_sheet(typeData);
    typeWs['!cols'] = [{ wch: 20 }, { wch: 12 }];
    typeWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }];
    if (typeWs['A1']) typeWs['A1'].s = { font: { bold: true, sz: 13, color: { rgb: NU_NAVY } } };
    ['A3','B3'].forEach(cell => { if (typeWs[cell]) typeWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, typeWs, 'By Type');

    // ============================================
    // 📄 SHEET 7 — Alumni by Region
    // ============================================
    const regionData = [
        ['ALUMNI BY REGION'],
        [],
        ['Region', 'Count']
    ];
    @foreach($alumniByRegion as $r)
    regionData.push([{!! json_encode($r->region) !!}, {{ $r->count }}]);
    @endforeach

    const regionWs = XLSX.utils.aoa_to_sheet(regionData);
    regionWs['!cols'] = [{ wch: 28 }, { wch: 12 }];
    regionWs['!merges'] = [{ s: { r: 0, c: 0 }, e: { r: 0, c: 1 } }];
    if (regionWs['A1']) regionWs['A1'].s = { font: { bold: true, sz: 13, color: { rgb: NU_NAVY } } };
    ['A3','B3'].forEach(cell => { if (regionWs[cell]) regionWs[cell].s = headerStyle; });

    XLSX.utils.book_append_sheet(wb, regionWs, 'By Region');

    // ============================================
    // 📄 SHEET 8 — 🌍 FULL GEOLOCATION  ⭐
    // ============================================
    const geoData = [
        ['ALUMNI GEOLOCATION — Full Address Data'],
        ['Sorted by Region, Province, Municipality'],
        [],
        ['#', 'Name', 'Region', 'Province', 'Municipality', 'Barangay', 'Latitude', 'Longitude', 'Address Type', 'Google Maps Link']
    ];

    @forelse($alumniLocations ?? [] as $loc)
    (function() {
        const lat = {{ (float) $loc->latitude }};
        const lng = {{ (float) $loc->longitude }};
        const mapsLink = `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
        geoData.push([
            {{ $loop->iteration }},
            {!! json_encode($loc->first_name . ' ' . $loc->last_name) !!},
            {!! json_encode($loc->region ?? '—') !!},
            {!! json_encode($loc->province ?? '—') !!},
            {!! json_encode($loc->municipality ?? '—') !!},
            {!! json_encode($loc->barangay ?? '—') !!},
            lat,
            lng,
            {!! json_encode($loc->address_type ?? '—') !!},
            { t: 's', v: mapsLink, l: { Target: mapsLink } }
        ]);
    })();
    @empty
    geoData.push(['—', 'No alumni with location data', '—', '—', '—', '—', '—', '—', '—', '—']);
    @endforelse

    const geoWs = XLSX.utils.aoa_to_sheet(geoData);
    geoWs['!cols'] = [
        { wch: 5 },   // #
        { wch: 26 },  // Name
        { wch: 18 },  // Region
        { wch: 18 },  // Province
        { wch: 20 },  // Municipality
        { wch: 20 },  // Barangay
        { wch: 12 },  // Lat
        { wch: 12 },  // Lng
        { wch: 14 },  // Address Type
        { wch: 28 },  // Maps Link
    ];
    geoWs['!merges'] = [
        { s: { r: 0, c: 0 }, e: { r: 0, c: 9 } },
        { s: { r: 1, c: 0 }, e: { r: 1, c: 9 } },
    ];
    if (geoWs['A1']) geoWs['A1'].s = { font: { bold: true, sz: 14, color: { rgb: NU_NAVY } } };
    if (geoWs['A2']) geoWs['A2'].s = { font: { italic: true, sz: 10, color: { rgb: '666666' } } };

    ['A4','B4','C4','D4','E4','F4','G4','H4','I4','J4'].forEach(cell => {
        if (geoWs[cell]) geoWs[cell].s = headerStyle;
    });

    // Freeze the header row + first column for easy scrolling
    geoWs['!freeze'] = { xSplit: 2, ySplit: 4 };

    XLSX.utils.book_append_sheet(wb, geoWs, 'Alumni Locations');

    // ============================================
    // 💾 SAVE
    // ============================================
    const filename = `User_Analytics_${new Date().toISOString().slice(0, 10)}.xlsx`;
    XLSX.writeFile(wb, filename);
    showToast('User Analytics exported to Excel', 'success');
}

function exportPDF(data, title) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF('p', 'mm', 'a4');
    
    doc.setFontSize(16);
    doc.text(title, 14, 22);
    doc.setFontSize(10);
    doc.text('Generated: ' + new Date().toLocaleString(), 14, 30);
    
    doc.autoTable({
        head: [data[0]],
        body: data.slice(1),
        startY: 38,
        styles: { fontSize: 9 },
        headStyles: { fillColor: [50, 65, 140] },
        margin: { left: 14, right: 14 },
    });
    
    doc.save(title + '.pdf');
}

function exportExcel(data, title) {
    const wb = XLSX.utils.book_new();
    const ws = XLSX.utils.aoa_to_sheet(data);
    XLSX.utils.book_append_sheet(wb, ws, 'Report');
    XLSX.writeFile(wb, title + '.xlsx');
}

// ========== RESTRICT USER FUNCTION (Shared) ==========
async function restrictUser(alumniId, forcedAction = null) {
    // Determine current state
    let isRestricted = false;

    if (forcedAction === 'unrestrict') {
        isRestricted = true;
    } else if (forcedAction === 'restrict') {
        isRestricted = false;
    } else {
        // Fall back to reading the violator row's data-status
        const userRow = document.querySelector(`.violator-item[data-id="${alumniId}"]`);
        isRestricted = userRow?.dataset?.status === '0';
    }

    if (isRestricted) {
        const confirmed = await showConfirmModal({
            title: 'Unrestrict User',
            message: 'This user is currently restricted. Do you want to unrestrict them?\n\nThey will regain full access to the platform.',
            confirmText: 'Unrestrict',
            confirmClass: 'btn-success',
            icon: 'fa-user-check',
            iconColor: '#10B981',
        });
        if (!confirmed) return;

        try {
            const response = await fetch('/admin/restrict-user', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ alumni_id: alumniId, restrict: 0 })
            });
            const data = await response.json();
            if (data.success) {
                location.reload();
            } else {
                showErrorModal(data.message || 'Something went wrong');
            }
        } catch (err) {
            showErrorModal('An error occurred. Please try again.');
        }
        return;
    }
    
    // Show restriction reason modal
    const reasonOptions = [
        'Spam or Fraud',
        'Nudity or Sexual Content', 
        'Hate Speech or Symbols',
        'Violence or Dangerous Organizations',
        'Bullying or Harassment',
        'Sale of Illegal or Regulated Goods',
        'Intellectual Property Violation',
        'Other'
    ];
    
    let reasonHtml = reasonOptions.map(r => 
        `<option value="${r.toLowerCase().replace(/ /g, '_')}">${r}</option>`
    ).join('');
    
    const reasonModal = `
        <div id="restrictReasonModal" class="modal-overlay active" style="display:flex;">
            <div class="modal-content-wrapper" style="max-width: 500px;">
                <div class="modal-card">
                    <div class="modal-header">
                        <div>
                            <h2 class="modal-title">
                                <i class="fa-solid fa-user-slash"></i>
                                Restrict User
                            </h2>
                            <p class="modal-subtitle">Select a reason for restricting this user</p>
                        </div>
                        <button class="modal-close" onclick="closeRestrictModal()">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="restrictForm" onsubmit="submitRestrict(event, ${alumniId})">
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label for="restrictionReason" style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    Reason for Restriction <span style="color: var(--danger);">*</span>
                                </label>
                                <select id="restrictionReason" required style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 0.9375rem; font-family: inherit;">
                                    <option value="">Select a reason...</option>
                                    ${reasonHtml}
                                </select>
                            </div>
                            <div class="form-group" id="customReasonGroup" style="display: none; margin-bottom: 1rem;">
                                <label for="customReason" style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    Please specify:
                                </label>
                                <input type="text" id="customReason" placeholder="Enter custom reason..." style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 0.9375rem; font-family: inherit;">
                            </div>
                            <div class="form-group" style="margin-bottom: 1rem;">
                                <label for="restrictionComment" style="display: block; font-weight: 600; margin-bottom: 0.5rem; font-size: 0.875rem;">
                                    Additional Comments <span style="font-weight: 400; color: var(--gray-500);">(Optional)</span>
                                </label>
                                <textarea id="restrictionComment" rows="3" placeholder="Add any additional notes..." style="width: 100%; padding: 0.75rem; border: 2px solid var(--gray-200); border-radius: var(--radius-lg); font-size: 0.9375rem; font-family: inherit; resize: vertical; min-height: 80px;"></textarea>
                            </div>
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end; margin-top: 1rem; padding-top: 1rem; border-top: 2px solid var(--gray-100);">
                                <button type="button" class="btn btn-secondary" onclick="closeRestrictModal()">Cancel</button>
                                <button type="submit" class="btn btn-warning">
                                    <i class="fa-solid fa-lock"></i> Restrict User
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    `;
    
    const existingModal = document.getElementById('restrictReasonModal');
    if (existingModal) existingModal.remove();
    
    document.body.insertAdjacentHTML('beforeend', reasonModal);
    document.body.style.overflow = 'hidden';
    
    document.getElementById('restrictionReason').addEventListener('change', function() {
        const customGroup = document.getElementById('customReasonGroup');
        if (this.value === 'other') {
            customGroup.style.display = 'block';
        } else {
            customGroup.style.display = 'none';
        }
    });
}

function closeRestrictModal() {
    const modal = document.getElementById('restrictReasonModal');
    if (modal) {
        modal.remove();
        document.body.style.overflow = '';
    }
}

async function submitRestrict(event, alumniId) {
    event.preventDefault();
    
    const reason = document.getElementById('restrictionReason').value;
    const customReason = document.getElementById('customReason')?.value || '';
    const comment = document.getElementById('restrictionComment')?.value || '';
    
    if (!reason) {
        await showAlertModal({
            title: 'Missing Reason',
            message: 'Please select a reason for restriction.',
            icon: 'fa-circle-exclamation',
            iconColor: '#F59E0B',
        });
        return;
    }
    
    const finalReason = reason === 'other' ? customReason : reason;
    
    if (reason === 'other' && !customReason.trim()) {
        await showAlertModal({
            title: 'Missing Custom Reason',
            message: 'Please specify a custom reason.',
            icon: 'fa-circle-exclamation',
            iconColor: '#F59E0B',
        });
        return;
    }
    
    fetch('/admin/restrict-user', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ 
            alumni_id: alumniId, 
            restrict: 1,
            restriction_reason: finalReason,
            restriction_comment: comment
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
        closeRestrictModal();
        location.reload();
    } else {
        showErrorModal(data.message || 'Something went wrong');
    }
    })
    .catch(error => {
        showErrorModal('An error occurred. Please try again.');
    });
}

// ========== MODAL FUNCTIONS (Shared) ==========
function openModal(type, id) {
    let modalId, bodyId, titleId, subtitleId;
    
    switch(type) {
        case 'violators':
            modalId = 'violatorModal';
            bodyId = 'violatorModalBody';
            titleId = 'violatorModalTitle';
            subtitleId = 'violatorModalSubtitle';
            document.getElementById(titleId).innerHTML = '<i class="fa-solid fa-users"></i> All Frequent Violators';
            document.getElementById(subtitleId).textContent = 'Showing all alumni with reports';
            loadAllViolators(bodyId);
            break;
            
        case 'violator':
            modalId = 'violatorModal';
            bodyId = 'violatorModalBody';
            titleId = 'violatorModalTitle';
            subtitleId = 'violatorModalSubtitle';
            document.getElementById(titleId).innerHTML = '<i class="fa-solid fa-user"></i> Violator Details';
            document.getElementById(subtitleId).textContent = 'Full report details for this alumni';
            loadViolatorDetails(id, bodyId);
            break;
            
        case 'posts':
            modalId = 'postModal';
            bodyId = 'postModalBody';
            subtitleId = 'postModalSubtitle';
            document.getElementById('postModalTitle').innerHTML = '<i class="fa-solid fa-file-lines"></i> All Reported Posts';
            document.getElementById(subtitleId).textContent = 'Showing all reported posts';
            loadAllPosts(bodyId);
            break;
            
        case 'post':
            modalId = 'postModal';
            bodyId = 'postModalBody';
            subtitleId = 'postModalSubtitle';
            document.getElementById('postModalTitle').innerHTML = '<i class="fa-solid fa-file-lines"></i> Post Report Details';
            document.getElementById(subtitleId).textContent = 'Full details of reported post';
            loadPostDetails(id, bodyId);
            break;
            
        case 'comments':
            modalId = 'commentModal';
            bodyId = 'commentModalBody';
            subtitleId = 'commentModalSubtitle';
            document.getElementById('commentModalTitle').innerHTML = '<i class="fa-solid fa-comments"></i> All Reported Comments';
            document.getElementById(subtitleId).textContent = 'Showing all reported comments';
            loadAllComments(bodyId);
            break;
            
        case 'comment':
            modalId = 'commentModal';
            bodyId = 'commentModalBody';
            subtitleId = 'commentModalSubtitle';
            document.getElementById('commentModalTitle').innerHTML = '<i class="fa-solid fa-comment"></i> Comment Report Details';
            document.getElementById(subtitleId).textContent = 'Full details of reported comment';
            loadCommentDetails(id, bodyId);
            break;
    }
    
    document.getElementById(modalId).classList.add('active');
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
    document.body.style.overflow = '';
}

// ========== MODAL DATA LOADING FUNCTIONS ==========
// Safely serialize moderation data - prevents page crashes
const moderationData = {
    frequentViolators: @json($frequentViolators ?? []),
    reportedPosts: @json($reportedPosts ?? []),
    reportedComments: @json($reportedComments ?? [])
};

function loadViolatorDetails(id, bodyId) {
    const body = document.getElementById(bodyId);
    const violator = moderationData.frequentViolators.find(v => v.id === id);
    
    if (violator) {
        body.innerHTML = `
            <div class="modal-detail-section">
                <div class="detail-row">
                    <span class="detail-label">Name</span>
                    <span class="detail-value">${violator.first_name} ${violator.last_name}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email</span>
                    <span class="detail-value">${violator.email}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Account Status</span>
                    <span class="detail-value">${violator.account_status == 1 ? 'Active' : 'Restricted'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Joined</span>
                    <span class="detail-value">${violator.created_at ? new Date(violator.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) : 'N/A'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Total Reports</span>
                    <span class="detail-value"><strong>${violator.total_reports}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Post Reports</span>
                    <span class="detail-value">${violator.post_reports}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Comment Reports</span>
                    <span class="detail-value">${violator.comment_reports}</span>
                </div>
                ${violator.report_reasons ? `
                <div class="detail-row full-width">
                    <span class="detail-label">Report Reasons</span>
                    <span class="detail-value">${violator.report_reasons}</span>
                </div>
                ` : ''}
                <div class="detail-actions">
                    <a href="/admin/alumni/${violator.id}/view" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-eye"></i> View Profile
                    </a>
                    ${violator.account_status == 1 ? 
                        `<button class="btn btn-warning btn-sm" onclick="restrictUser(${violator.id})">
                            <i class="fa-solid fa-user-slash"></i> Restrict User
                        </button>` :
                        `<button class="btn btn-success btn-sm" onclick="restrictUser(${violator.id})">
                            <i class="fa-solid fa-user-check"></i> Unrestrict User
                        </button>`
                    }
                    <a href="/admin/messages?chat=${violator.id}" class="btn btn-secondary btn-sm">
                        <i class="fa-solid fa-comment-dots"></i> Send Message
                    </a>
                </div>
            </div>
        `;
    } else {
        body.innerHTML = `<div class="empty-state"><p>Violator not found</p></div>`;
    }
}

function loadAllViolators(bodyId) {
    const body = document.getElementById(bodyId);
    const violators = moderationData.frequentViolators;
    
    if (!violators || violators.length === 0) {
        body.innerHTML = `<div class="empty-state"><p>No violators found</p></div>`;
        return;
    }
    
    let html = `<div class="modal-list">`;
    violators.forEach(v => {
        html += `
            <div class="modal-list-item">
                <div class="modal-list-avatar">
                    ${v.alumni_photo ? 
                        `<img src="${v.alumni_photo}" alt="${v.first_name}">` :
                        `<div class="modal-list-initials">${v.first_name.charAt(0)}${v.last_name.charAt(0)}</div>`
                    }
                </div>
                <div class="modal-list-info">
                    <div class="modal-list-name">${v.first_name} ${v.last_name}</div>
                    <div class="modal-list-meta">
                        <span>${v.email}</span>
                        <span>${v.total_reports} reports</span>
                        <span>${v.post_reports} posts, ${v.comment_reports} comments</span>
                    </div>
                    ${v.report_reasons ? `<div class="modal-list-reasons">${v.report_reasons}</div>` : ''}
                </div>
                <div class="modal-list-actions">
                    <a href="/admin/alumni/${v.id}/view" class="btn-action btn-view" title="View Profile">
                        <i class="fa-solid fa-eye"></i>
                    </a>
                    ${v.account_status == 1 ? 
                        `<button class="btn-action btn-restrict" onclick="restrictUser(${v.id})" title="Restrict User">
                            <i class="fa-solid fa-user-slash"></i>
                        </button>` :
                        `<button class="btn-action btn-unrestrict" onclick="restrictUser(${v.id})" title="Unrestrict User" style="background: #D1FAE5; color: #065F46;">
                            <i class="fa-solid fa-user-check"></i>
                        </button>`
                    }
                </div>
            </div>
        `;
    });
    html += `</div>`;
    body.innerHTML = html;
}

function loadPostDetails(id, bodyId) {
    const body = document.getElementById(bodyId);
    const post = moderationData.reportedPosts.find(p => p.id === id);
    
    if (post) {
        fetch(`/admin/posts/${id}/full`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderPostDetailModal(body, data.post, post);
            } else {
                renderBasicPostDetail(body, post);
            }
        })
        .catch(error => {
            console.error('Error loading full post:', error);
            renderBasicPostDetail(body, post);
        });
    } else {
        body.innerHTML = `<div class="empty-state"><p>Post not found</p></div>`;
    }
}

function renderPostDetailModal(body, fullPost, reportData) {
    const author = fullPost.alumni || {};
    const authorName = author ? `${author.first_name || ''} ${author.last_name || ''}`.trim() : 'Unknown';
    const initials = author ? (author.first_name?.[0] || '?') + (author.last_name?.[0] || '') : '?';
    
    // Build images HTML
    let imagesHtml = '';
    if (fullPost.images && fullPost.images.length > 0) {
        const imageCount = fullPost.images.length;
        const gridClass = imageCount === 1 ? 'grid-1' : (imageCount === 2 ? 'grid-2' : (imageCount === 3 ? 'grid-3' : 'grid-4'));
        
        imagesHtml = `
            <div class="modal-post-images ${gridClass}">
                ${fullPost.images.map(img => {
                    const imgPath = (img.image_path || '').replace(/^\/+/, '');
                    const supabaseUrl = '{{ rtrim(config("filesystems.disks.s3.url", ""), "/") }}';
                    const imgUrl = supabaseUrl ? supabaseUrl + '/' + imgPath : imgPath;
                    return `<img src="${imgUrl}" alt="Post image" onclick="window.open('${imgUrl}', '_blank')" loading="lazy" onerror="this.style.display='none'">`;
                }).join('')}
            </div>
        `;
    }
    
    body.innerHTML = `
        <div class="modal-post-full">
            <!-- Author Info -->
            <div class="modal-post-author">
                <div class="modal-post-avatar">${initials}</div>
                <div class="modal-post-author-info">
                    <div class="modal-post-author-name">${authorName}</div>
                    
                    <div class="modal-post-author-meta">
                        <span><i class="fa-regular fa-calendar"></i> ${fullPost.created_at ? new Date(fullPost.created_at).toLocaleString() : 'N/A'}</span>
                        <span class="post-badge visibility ${fullPost.visibility || 'public'}">
                            <i class="fa-solid fa-${(fullPost.visibility || 'public') === 'public' ? 'globe' : 'lock'}"></i>
                            ${fullPost.visibility || 'Public'}
                        </span>
                        <span class="post-badge moderation ${fullPost.moderation_status || 'pending'}">
                            <i class="fa-solid fa-${(fullPost.moderation_status || 'pending') === 'approved' ? 'check' : 'clock'}"></i>
                            ${fullPost.moderation_status || 'Pending'}
                        </span>

                        ${fullPost.is_hidden ? `
                            <span class="post-badge visibility hidden-badge" style="background:#FEE2E2;color:#B91C1C;">
                                <i class="fa-solid fa-eye-slash"></i> Hidden from app
                            </span>
                        ` : ''}
                        
                    </div>

                </div>
            </div>
            
            <!-- Caption -->
            ${fullPost.caption ? `<div class="modal-post-caption">${escapeHtml(fullPost.caption)}</div>` : ''}
            
            <!-- Images -->
            ${imagesHtml}
            
            <!-- Report Details -->
            <div class="modal-post-report-details">
                <div class="report-details-header">
                    <i class="fa-solid fa-flag" style="color: var(--danger);"></i>
                    Report Details
                    <span class="report-count-badge-small">${reportData.report_count} report${reportData.report_count > 1 ? 's' : ''}</span>
                </div>
                <div class="report-details-body">
                    <div class="detail-row">
                        <span class="detail-label">Report Reasons</span>
                        <span class="detail-value">${reportData.report_reasons || 'No specific reasons provided'}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Status</span>
                        <span class="detail-value">${fullPost.moderation_status || 'pending'}</span>
                    </div>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="detail-actions">
                <button class="btn btn-primary btn-sm" onclick="viewPost(${reportData.id})">
                    <i class="fa-solid fa-eye"></i> View Full Post
                </button>
                <button class="btn btn-success btn-sm" onclick="moderatePost(${reportData.id}, 'approve', this)">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
                ${fullPost.is_hidden ? `
                    <button class="btn btn-success btn-sm" onclick="moderatePost(${reportData.id}, 'unhide')">
                        <i class="fa-solid fa-eye"></i> Unhide
                    </button>
                ` : `
                    <button class="btn btn-warning btn-sm" onclick="moderatePost(${reportData.id}, 'hide')">
                        <i class="fa-solid fa-eye-slash"></i> Hide
                    </button>
                `}
                <button class="btn btn-danger btn-sm" onclick="moderatePost(${reportData.id}, 'delete')">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
                <button class="btn btn-danger btn-sm" onclick="restrictUser(${fullPost.alumni_id})">
                    <i class="fa-solid fa-user-slash"></i> Restrict User
                </button>
            </div>
        </div>
    `;
}

function renderBasicPostDetail(body, post) {
    const isHidden = post.is_hidden;
    body.innerHTML = `
        <div class="modal-detail-section">
            ${isHidden ? `
                <div style="background:#FEF2F2;border-left:4px solid #EF4444;padding:0.75rem 1rem;border-radius:8px;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;">
                    <i class="fa-solid fa-eye-slash" style="color:#B91C1C;"></i>
                    <strong style="color:#B91C1C;">This post is currently hidden from the mobile app.</strong>
                </div>
            ` : ''}
            <div class="detail-row full-width">
                <span class="detail-label">Post Content</span>
                <span class="detail-value" style="font-size: 1rem; background: var(--gray-50); padding: 1rem; border-radius: var(--radius);">${post.caption || 'No caption'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Author</span>
                <span class="detail-value">${post.alumni ? post.alumni.first_name + ' ' + post.alumni.last_name : 'Unknown'}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Report Count</span>
                <span class="detail-value"><strong>${post.report_count}</strong></span>
            </div>
            <div class="detail-row full-width">
                <span class="detail-label">Report Reasons</span>
                <span class="detail-value">${post.report_reasons || 'No specific reasons provided'}</span>
            </div>
            <div class="detail-actions">
                <button class="btn btn-primary btn-sm" onclick="viewPost(${post.id})">
                    <i class="fa-solid fa-eye"></i> View Full Post
                </button>
                <button class="btn btn-success btn-sm" onclick="moderatePost(${post.id}, 'approve')">
                    <i class="fa-solid fa-check"></i> Approve
                </button>
                ${isHidden ? `
                    <button class="btn btn-success btn-sm" onclick="moderatePost(${post.id}, 'unhide')">
                        <i class="fa-solid fa-eye"></i> Unhide
                    </button>
                ` : `
                    <button class="btn btn-warning btn-sm" onclick="moderatePost(${post.id}, 'hide')">
                        <i class="fa-solid fa-eye-slash"></i> Hide
                    </button>
                `}
                <button class="btn btn-danger btn-sm" onclick="moderatePost(${post.id}, 'delete')">
                    <i class="fa-solid fa-trash"></i> Delete
                </button>
            </div>
        </div>
    `;
}

function loadAllPosts(bodyId) {
    const body = document.getElementById(bodyId);
    const posts = moderationData.reportedPosts;

    if (!posts || posts.length === 0) {
        body.innerHTML = `<div class="empty-state"><p>No reported posts</p></div>`;
        return;
    }

    let html = `<div class="modal-list">`;
    posts.forEach(p => {
        const isHidden = p.is_hidden;
        html += `
            <div class="modal-list-item ${isHidden ? 'is-hidden' : ''}"
                 style="${isHidden ? 'background:#FEF2F2;border-left:3px solid #EF4444;' : ''}">
                <div class="modal-list-info">
                    <div class="modal-list-name">
                        ${p.caption ? p.caption.substring(0, 80) + '...' : 'No caption'}
                        ${isHidden ? `<span style="background:#FEE2E2;color:#B91C1C;font-size:0.7rem;padding:0.15rem 0.5rem;border-radius:999px;margin-left:0.5rem;">Hidden</span>` : ''}
                    </div>
                    <div class="modal-list-meta">
                        <span>By: ${p.alumni ? p.alumni.first_name + ' ' + p.alumni.last_name : 'Unknown'}</span>
                        <span>${p.report_count} reports</span>
                        <span>${p.created_at ? new Date(p.created_at).toLocaleDateString() : 'N/A'}</span>
                    </div>
                    ${p.report_reasons ? `<div class="modal-list-reasons">${p.report_reasons}</div>` : ''}
                </div>
                <div class="modal-list-actions">
                    <button class="btn-action btn-view" onclick="viewPost(${p.id})" title="View Post">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn-action btn-approve" onclick="moderatePost(${p.id}, 'approve')" title="Approve">
                        <i class="fa-solid fa-check"></i>
                    </button>
                    ${isHidden ? `
                        <button class="btn-action btn-unhide" onclick="moderatePost(${p.id}, 'unhide')" title="Unhide" style="background:#D1FAE5;color:#065F46;">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    ` : `
                        <button class="btn-action btn-hide" onclick="moderatePost(${p.id}, 'hide')" title="Hide">
                            <i class="fa-solid fa-eye-slash"></i>
                        </button>
                    `}
                    <button class="btn-action btn-delete" onclick="moderatePost(${p.id}, 'delete')" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += `</div>`;
    body.innerHTML = html;
}

function loadCommentDetails(id, bodyId) {
    const body = document.getElementById(bodyId);
    const comment = moderationData.reportedComments.find(c => c.id === id);
    
    if (comment) {
        body.innerHTML = `
            <div class="modal-detail-section">
                <div class="detail-row full-width">
                    <span class="detail-label">Comment Content</span>
                    <span class="detail-value" style="font-size: 1rem; background: var(--gray-50); padding: 1rem; border-radius: var(--radius);">${comment.comment || 'Comment deleted'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Author</span>
                    <span class="detail-value">${comment.alumni ? comment.alumni.first_name + ' ' + comment.alumni.last_name : 'Unknown User'}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Commented Date</span>
                    <span class="detail-value">${comment.created_at ? new Date(comment.created_at).toLocaleDateString('en-US', { month: 'long', day: 'numeric', year: 'numeric' }) : 'Date not available'}</span>
                </div>
                ${comment.post_caption ? `
                <div class="detail-row full-width">
                    <span class="detail-label">Related Post</span>
                    <span class="detail-value">${comment.post_caption}</span>
                </div>
                ` : ''}
                <div class="detail-row">
                    <span class="detail-label">Report Count</span>
                    <span class="detail-value"><strong>${comment.report_count}</strong></span>
                </div>
                <div class="detail-row full-width">
                    <span class="detail-label">Report Reasons</span>
                    <span class="detail-value">${comment.report_reasons || 'No specific reasons provided'}</span>
                </div>
                <div class="detail-actions">
                    <button class="btn btn-primary btn-sm" onclick="viewComment(${comment.id})">
                        <i class="fa-solid fa-eye"></i> View Full Comment
                    </button>
                    <button class="btn btn-success btn-sm" onclick="moderateComment(${comment.id}, 'approve')">
                        <i class="fa-solid fa-check"></i> Approve
                    </button>
                    <button class="btn btn-danger btn-sm" onclick="moderateComment(${comment.id}, 'delete')">
                        <i class="fa-solid fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        `;
    } else {
        body.innerHTML = `<div class="empty-state"><p>Comment not found</p></div>`;
    }
}

function loadAllComments(bodyId) {
    const body = document.getElementById(bodyId);
    const comments = moderationData.reportedComments;
    
    if (!comments || comments.length === 0) {
        body.innerHTML = `<div class="empty-state"><p>No reported comments</p></div>`;
        return;
    }
    
    let html = `<div class="modal-list">`;
    comments.forEach(c => {
        html += `
            <div class="modal-list-item">
                <div class="modal-list-info">
                    <div class="modal-list-name">"${c.comment ? c.comment.substring(0, 80) + '...' : 'Comment deleted'}"</div>
                    <div class="modal-list-meta">
                        <span>By: ${c.alumni ? c.alumni.first_name + ' ' + c.alumni.last_name : 'Unknown User'}</span>
                        <span>${c.report_count} reports</span>
                        ${c.post_caption ? `<span>On: ${c.post_caption.substring(0, 30)}...</span>` : ''}
                    </div>
                    ${c.report_reasons ? `<div class="modal-list-reasons">${c.report_reasons}</div>` : ''}
                </div>
                <div class="modal-list-actions">
                    <button class="btn-action btn-view" onclick="viewComment(${c.id})" title="View Comment">
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <button class="btn-action btn-approve" onclick="moderateComment(${c.id}, 'approve')" title="Approve">
                        <i class="fa-solid fa-check"></i>
                    </button>
                    <button class="btn-action btn-delete" onclick="moderateComment(${c.id}, 'delete')" title="Delete">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </div>
            </div>
        `;
    });
    html += `</div>`;
    body.innerHTML = html;
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        // Close interactions modal if open
        const interactionsModal = document.getElementById('interactionsModal');
        if (interactionsModal && interactionsModal.style.display === 'flex') {
            if (typeof closeInteractionsModal === 'function') {
                closeInteractionsModal();
            }
        }
        // Close restrict modal
        const restrictModal = document.getElementById('restrictReasonModal');
        if (restrictModal && restrictModal.style.display === 'flex') {
            closeRestrictModal();
        }
    }
});

// ========== INITIALIZE ON PAGE LOAD ==========
document.addEventListener('DOMContentLoaded', function() {
    // Initialize chart data FIRST
    const chartData = @json($chartData);
    window._chartData = chartData;
    window._charts = window._charts || {};

    // THEN restore the last tab (deferred, so layout is ready)
    const lastTab = sessionStorage.getItem('currentTab');
    if (lastTab && lastTab !== 'moderation') {
        // Wait for the browser to actually paint before switching tabs
        requestAnimationFrame(() => {
            setTimeout(() => showTab(lastTab), 50);
        });
    }
    
    // Initialize tab-specific charts based on active tab
    const activeTab = document.querySelector('.tab-content.active');
    if (activeTab) {
        const tabId = activeTab.id;
        if (tabId === 'tab-users' && typeof initUsersCharts === 'function') {
            initUsersCharts();
            setTimeout(() => {
                if (typeof initAlumniMap === 'function') {
                    initAlumniMap();
                }
            }, 300);
        } else if (tabId === 'tab-tracer' && typeof initTracerCharts === 'function') {
            initTracerCharts();
        } else if (tabId === 'tab-events' && typeof initEventsCharts === 'function') {
            initEventsCharts();
        }
    }
    
    // Resize charts and map on window resize
    let resizeTimer;
    window.addEventListener('resize', function() {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            if (window._charts) {
                Object.values(window._charts).forEach(chart => {
                    if (chart && chart.resize) chart.resize();
                });
            }
            if (typeof alumniMap !== 'undefined' && alumniMap && typeof mapInitialized !== 'undefined' && mapInitialized) {
                try { alumniMap.invalidateSize(); } catch(e) {}
            }
        }, 200);
    });
});

// ============================================
// TRACER ANALYTICS CHARTS
// ============================================

function initTracerCharts() {
    const tracerData = @json($tracerAnalytics['chartData'] ?? []);
    const charts = window._charts || {};

    // Helper: destroy existing chart
    const destroyIfExists = (key) => {
        if (charts[key]) {
            charts[key].destroy();
            delete charts[key];
        }
    };

    // ---------- 1. OVERALL RESPONSES OVER TIME ----------
    const overTimeCtx = document.getElementById('tracerResponsesOverTimeChart');
    if (overTimeCtx && tracerData.responses_over_time?.labels?.length) {
        destroyIfExists('tracerOverTime');
        charts.tracerOverTime = new Chart(overTimeCtx, {
            type: 'line',
            data: {
                labels: tracerData.responses_over_time.labels,
                datasets: [{
                    label: 'Responses',
                    data: tracerData.responses_over_time.data,
                    borderColor: '#32418C',
                    backgroundColor: 'rgba(50, 65, 140, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#32418C',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1F2937',
                        padding: 12,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { stepSize: 1, color: '#6B7280' },
                        grid: { color: '#F3F4F6' }
                    },
                    x: {
                        ticks: { color: '#6B7280' },
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // ---------- 2. PHASE-SPECIFIC TRENDS ----------
    const trendsCtx = document.getElementById('tracerPhaseTrendsChart');
    if (trendsCtx && tracerData.phase_trends?.length) {
        destroyIfExists('tracerPhaseTrends');
        
        // Collect all unique months across phases
        const allMonths = [...new Set(
            tracerData.phase_trends.flatMap(p => p.data.map(d => d.month))
        )].sort((a, b) => new Date(a) - new Date(b));

        const datasets = tracerData.phase_trends.map(phase => ({
            label: phase.phase_title,
            data: allMonths.map(month => {
                const found = phase.data.find(d => d.month === month);
                return found ? found.count : 0;
            }),
            borderColor: phase.phase_color,
            backgroundColor: phase.phase_color + '20',
            fill: false,
            tension: 0.4,
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5,
        }));

        charts.tracerPhaseTrends = new Chart(trendsCtx, {
            type: 'line',
            data: { labels: allMonths, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 11 }
                        }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ---------- 3. PHASE COMPARISON (Grouped Bar) ----------
    const compCtx = document.getElementById('tracerPhaseComparisonChart');
    if (compCtx && tracerData.phase_comparison?.labels?.length) {
        destroyIfExists('tracerPhaseComparison');
        charts.tracerPhaseComparison = new Chart(compCtx, {
            type: 'bar',
            data: {
                labels: tracerData.phase_comparison.labels,
                datasets: [
                    {
                        label: 'Eligible',
                        data: tracerData.phase_comparison.eligible,
                        backgroundColor: 'rgba(156, 163, 175, 0.6)',
                        borderColor: '#9CA3AF',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'In Progress',
                        data: tracerData.phase_comparison.in_progress,
                        backgroundColor: 'rgba(245, 158, 11, 0.7)',
                        borderColor: '#F59E0B',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Completed',
                        data: tracerData.phase_comparison.completed,
                        backgroundColor: 'rgba(16, 185, 129, 0.8)',
                        borderColor: '#10B981',
                        borderWidth: 1,
                        borderRadius: 4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ---------- 4. COMPLETION FUNNEL ----------
    const funnelCtx = document.getElementById('tracerFunnelChart');
    if (funnelCtx && tracerData.funnel?.labels?.length) {
        destroyIfExists('tracerFunnel');
        charts.tracerFunnel = new Chart(funnelCtx, {
            type: 'bar',
            data: {
                labels: tracerData.funnel.labels,
                datasets: [{
                    data: tracerData.funnel.data,
                    backgroundColor: [
                        'rgba(156, 163, 175, 0.7)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                    ],
                    borderColor: ['#9CA3AF', '#F59E0B', '#10B981'],
                    borderWidth: 1,
                    borderRadius: 8,
                    barPercentage: 0.7,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                const total = tracerData.funnel.data[0];
                                const pct = total > 0 ? ((ctx.raw / total) * 100).toFixed(1) : 0;
                                return `${ctx.raw} alumni (${pct}%)`;
                            }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // ---------- 5. AUDIENCE BREAKDOWN (Grouped Bar + Line) ----------
    const audCtx = document.getElementById('tracerAudienceChart');
    if (audCtx && tracerData.audience_breakdown?.labels?.length) {
        destroyIfExists('tracerAudience');
        charts.tracerAudience = new Chart(audCtx, {
            type: 'bar',
            data: {
                labels: tracerData.audience_breakdown.labels,
                datasets: [
                    {
                        label: 'Eligible',
                        data: tracerData.audience_breakdown.eligible,
                        backgroundColor: 'rgba(156, 163, 175, 0.5)',
                        borderColor: '#9CA3AF',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Completed',
                        data: tracerData.audience_breakdown.completed,
                        backgroundColor: 'rgba(50, 65, 140, 0.8)',
                        borderColor: '#32418C',
                        borderWidth: 1,
                        borderRadius: 4,
                    },
                    {
                        label: 'Completion Rate (%)',
                        data: tracerData.audience_breakdown.completion_rate,
                        type: 'line',
                        borderColor: '#FBD117',
                        backgroundColor: '#FBD117',
                        borderWidth: 3,
                        pointRadius: 5,
                        pointBackgroundColor: '#FBD117',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        yAxisID: 'y1',
                        tension: 0.3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 12, padding: 10, font: { size: 11 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => {
                                if (ctx.dataset.yAxisID === 'y1') {
                                    return `Completion: ${ctx.raw}%`;
                                }
                                return `${ctx.dataset.label}: ${ctx.raw}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        position: 'left',
                        ticks: { stepSize: 1 },
                        title: { display: true, text: 'Count' }
                    },
                    y1: {
                        beginAtZero: true,
                        position: 'right',
                        max: 100,
                        ticks: { callback: (v) => v + '%' },
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'Rate (%)' }
                    },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // ---------- 6. DROP-OFF RATES (Horizontal Bar) ----------
    const dropCtx = document.getElementById('tracerDropOffChart');
    if (dropCtx && tracerData.drop_off_rates?.labels?.length) {
        destroyIfExists('tracerDropOff');
        charts.tracerDropOff = new Chart(dropCtx, {
            type: 'bar',
            data: {
                labels: tracerData.drop_off_rates.labels,
                datasets: [{
                    label: 'Drop-off Rate',
                    data: tracerData.drop_off_rates.data,
                    backgroundColor: tracerData.drop_off_rates.data.map(v =>
                        v > 50 ? 'rgba(239, 68, 68, 0.8)' :
                        v > 25 ? 'rgba(245, 158, 11, 0.8)' :
                        'rgba(16, 185, 129, 0.8)'
                    ),
                    borderRadius: 6,
                    barPercentage: 0.6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `Drop-off: ${ctx.raw}%`
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: (v) => v + '%' }
                    },
                    y: { grid: { display: false } }
                }
            }
        });
    }

    // ---------- 7. EMPLOYMENT BY PROGRAM ----------
const empProgCtx = document.getElementById('employmentByProgramChart');
@if(isset($tracerAnalytics['collegeEmployment']['employment_by_program']) && $tracerAnalytics['collegeEmployment']['employment_by_program']->count())
if (empProgCtx) {
    destroyIfExists('employmentByProgram');
    const empProgData = @json($tracerAnalytics['collegeEmployment']['employment_by_program']);
    charts.employmentByProgram = new Chart(empProgCtx, {
        type: 'bar',
        data: {
            labels: empProgData.map(p => p.program),
            datasets: [{
                label: 'Employment Rate (%)',
                data: empProgData.map(p => p.employment_rate),
                backgroundColor: empProgData.map(p =>
                    p.employment_rate >= 75 ? 'rgba(16, 185, 129, 0.8)' :
                    p.employment_rate >= 50 ? 'rgba(59, 130, 246, 0.8)' :
                                              'rgba(245, 158, 11, 0.8)'
                ),
                borderRadius: 6,
                barPercentage: 0.65,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: (ctx) => {
                            const p = empProgData[ctx.dataIndex];
                            return `${p.employed_alumni} of ${p.total_alumni} employed (${ctx.raw}%)`;
                        }
                    }
                }
            },
            scales: {
                x: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                y: { grid: { display: false } }
            }
        }
    });
}
@endif

// ---------- 8. SHS PROMOTION TIMELINE ----------
const shsTimelineCtx = document.getElementById('shsPromotionTimelineChart');
@if(isset($tracerAnalytics['shsCollegeChoice']['promotion_timeline']) && $tracerAnalytics['shsCollegeChoice']['promotion_timeline']->count())
if (shsTimelineCtx) {
    destroyIfExists('shsPromotionTimeline');
    const timelineData = @json($tracerAnalytics['shsCollegeChoice']['promotion_timeline']);
    charts.shsPromotionTimeline = new Chart(shsTimelineCtx, {
        type: 'bar',
        data: {
            labels: timelineData.map(t => t.year),
            datasets: [{
                label: 'SHS Alumni Promoted to College',
                data: timelineData.map(t => t.count),
                backgroundColor: 'rgba(139, 92, 246, 0.8)',
                borderColor: '#8B5CF6',
                borderWidth: 1,
                borderRadius: 6,
                barPercentage: 0.6,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } },
                x: { grid: { display: false } }
            }
        }
    });
}
@endif

    // ---------- 9. SHS TOP PROGRAMS ----------
    const shsProgCtx = document.getElementById('shsTopProgramsChart');
    @if(isset($tracerAnalytics['shsCollegeChoice']['top_college_programs']) && $tracerAnalytics['shsCollegeChoice']['top_college_programs']->count())
    if (shsProgCtx) {
        destroyIfExists('shsTopPrograms');
        const topProgs = @json($tracerAnalytics['shsCollegeChoice']['top_college_programs']->take(5)->values());
        charts.shsTopPrograms = new Chart(shsProgCtx, {
            type: 'bar',
            data: {
                labels: topProgs.map(p => p.answer_value.length > 20 ? p.answer_value.substring(0, 20) + '…' : p.answer_value),
                datasets: [{
                    label: 'Students',
                    data: topProgs.map(p => p.count),
                    backgroundColor: 'rgba(139, 92, 246, 0.8)',
                    borderColor: '#8B5CF6',
                    borderWidth: 1,
                    borderRadius: 6,
                    barPercentage: 0.65,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { beginAtZero: true, ticks: { stepSize: 1 } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
    @endif

    window._charts = charts;
    return charts;
}

// Initialize when tab is shown
document.addEventListener('DOMContentLoaded', function() {
    // If tracer tab is active on load
    if (document.getElementById('tab-tracer')?.classList.contains('active')) {
        setTimeout(initTracerCharts, 100);
    }
});

</script>