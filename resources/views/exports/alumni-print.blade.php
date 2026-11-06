<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>LumiNUs Alumni Directory</title>
<style>
    @page {
        margin: 0;
        padding: 0;
        size: A4;
    }

    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        font-family: 'Poppins', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        color: #1F2937;
        font-size: 12px;
        line-height: 1.5;
        background: #F3F4F6;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }

    /* =====================
       PRINT BUTTONS BAR (hidden when printing)
       ===================== */
    .no-print {
        position: fixed;
        top: 1rem;
        right: 1rem;
        z-index: 9999;
        display: flex;
        gap: 0.5rem;
        background: white;
        padding: 0.75rem;
        border-radius: 12px;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.15);
        border: 1px solid #E5E7EB;
    }

    .no-print button,
    .no-print a {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.25rem;
        border: none;
        border-radius: 8px;
        font-family: inherit;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-print {
        background: #32418c;
        color: #ffffff;
    }
    .btn-print:hover {
        background: #253069;
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(50, 65, 140, 0.3);
    }

    .btn-back {
        background: #F3F4F6;
        color: #374151;
    }
    .btn-back:hover {
        background: #E5E7EB;
        transform: translateY(-2px);
    }

    /* =====================
       PAGE WRAPPER
       ===================== */
    .page-wrapper {
        background: #ffffff;
        max-width: 210mm;
        margin: 5rem auto 2rem;
        box-shadow: 0 20px 40px -8px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
        overflow: hidden;
    }

    /* =====================
       COVER PAGE
       ===================== */
    .cover {
        position: relative;
        width: 100%;
        height: 297mm;
        page-break-after: always;
        background: #ffffff;
        overflow: hidden;
    }

    .cover-band-top {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 55%;
        background: linear-gradient(135deg, #253069 0%, #32418c 55%, #4a59a3 100%);
        clip-path: polygon(0 0, 100% 0, 100% 82%, 0 100%);
    }

    .cover-band-gold {
        position: absolute;
        top: 42%;
        left: -5%;
        width: 110%;
        height: 22%;
        background: #fbd117;
        clip-path: polygon(0 65%, 100% 0, 100% 40%, 0 100%);
        z-index: 2;
    }

    .cover-circle-1 {
        position: absolute;
        top: -80px;
        right: -80px;
        width: 320px;
        height: 320px;
        border-radius: 50%;
        background: rgba(251, 209, 23, 0.08);
        z-index: 1;
    }
    .cover-circle-2 {
        position: absolute;
        top: 120px;
        right: 60px;
        width: 140px;
        height: 140px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        z-index: 1;
    }
    .cover-circle-3 {
        position: absolute;
        bottom: 260px;
        left: -60px;
        width: 180px;
        height: 180px;
        border-radius: 50%;
        background: rgba(50, 65, 140, 0.06);
        z-index: 1;
    }

    .cover-logos {
        position: absolute;
        top: 40px;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 5;
    }

    .cover-logos img {
        vertical-align: middle;
        height: 100px;
    }

    .logo-luminus { margin-right: 18px; width: auto; }
    .logo-nu {
        margin-left: 18px;
        padding-left: 18px;
        border-left: 2px solid rgba(255, 255, 255, 0.3);
    }

    .cover-content {
        position: absolute;
        top: 32%;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 6;
        padding: 0 60px;
    }

    .cover-eyebrow {
        display: inline-block;
        padding: 6px 18px;
        background: rgba(251, 209, 23, 0.2);
        border: 1px solid #fbd117;
        border-radius: 20px;
        color: #fbd117;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 3px;
        text-transform: uppercase;
        margin-bottom: 20px;
    }

    .cover-title {
        font-size: 52px;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -1px;
        line-height: 1.1;
        margin-bottom: 12px;
    }

    .cover-title-accent { color: #fbd117; }

    .cover-subtitle {
        font-size: 14px;
        color: rgba(255, 255, 255, 0.85);
        font-weight: 400;
        letter-spacing: 0.5px;
        max-width: 520px;
        margin: 0 auto;
        line-height: 1.6;
    }

    .cover-meta-wrap {
        position: absolute;
        top: 58%;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 7;
    }

    .cover-meta-pill {
        display: inline-block;
        background: #ffffff;
        padding: 18px 36px;
        border-radius: 12px;
        box-shadow: 0 8px 24px rgba(37, 48, 105, 0.15);
        border: 2px solid #fbd117;
    }

    .cover-meta-pill .meta-label {
        display: block;
        font-size: 9px;
        color: #6B7280;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .cover-meta-pill .meta-value {
        display: block;
        font-size: 26px;
        color: #32418c;
        font-weight: 700;
        letter-spacing: -0.5px;
    }

    .cover-meta-pill .meta-divider {
        display: inline-block;
        width: 1px;
        height: 32px;
        background: #E5E7EB;
        vertical-align: middle;
        margin: 0 28px;
    }

    .cover-footer {
        position: absolute;
        bottom: 60px;
        left: 0;
        right: 0;
        text-align: center;
        z-index: 6;
    }

    .cover-footer-brand {
        font-size: 12px;
        color: #32418c;
        font-weight: 700;
        letter-spacing: 3px;
        margin-bottom: 8px;
    }

    .cover-footer-note {
        font-size: 11px;
        color: #6B7280;
        letter-spacing: 0.3px;
    }

    .cover-footer-note strong {
        color: #32418c;
        font-weight: 600;
    }

    /* =====================
       PAGE HEADER (interior)
       ===================== */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 28px;
        background: #32418c;
        color: #ffffff;
        border-bottom: 3px solid #fbd117;
    }

    .page-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .page-header-left img {
        height: 28px;
    }

    .page-header-title {
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    .page-header-right {
        font-size: 10px;
        opacity: 0.85;
        letter-spacing: 0.5px;
    }

    .page-header-right strong {
        color: #fbd117;
        font-weight: 700;
    }

    /* =====================
       CONTENT
       ===================== */
    .page-content {
        padding: 24px 28px 60px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        font-weight: 700;
        color: #32418c;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        margin: 26px 0 16px;
        padding-bottom: 10px;
        border-bottom: 2px solid #E8EBF7;
    }

    .section-title::before {
        content: '';
        width: 4px;
        height: 16px;
        background: #fbd117;
        border-radius: 2px;
    }

    .section-title:first-child { margin-top: 0; }

    /* Stats */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .stat-cell {
        background: #ffffff;
        border: 1px solid #E5E7EB;
        border-top: 3px solid #32418c;
        border-radius: 6px;
        padding: 16px 12px;
        text-align: center;
    }

    .stat-cell.gold { border-top-color: #fbd117; }

    .stat-cell .stat-label {
        display: block;
        font-size: 9px;
        color: #6B7280;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-weight: 600;
        margin-bottom: 8px;
    }

    .stat-cell .stat-value {
        display: block;
        font-size: 28px;
        font-weight: 700;
        color: #32418c;
        letter-spacing: -0.5px;
        line-height: 1;
    }

    .stat-cell.gold .stat-value { color: #c9a300; }

    /* Distribution */
    .dist-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
    }

    .dist-box {
        background: #F9FAFB;
        border: 1px solid #E5E7EB;
        border-radius: 6px;
        padding: 14px;
    }

    .dist-box-title {
        font-size: 10px;
        font-weight: 700;
        color: #32418c;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 2px solid #fbd117;
    }

    .dist-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 7px 0;
        border-bottom: 1px dashed #E5E7EB;
        font-size: 11px;
    }

    .dist-row:last-child { border-bottom: none; }

    .dist-name {
        color: #374151;
        font-weight: 500;
        max-width: 70%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .dist-count {
        background: #32418c;
        color: #ffffff;
        font-weight: 700;
        font-size: 10px;
        padding: 3px 12px;
        border-radius: 10px;
        min-width: 28px;
        text-align: center;
    }

    .dist-row.gold .dist-count {
        background: #fbd117;
        color: #253069;
    }

    /* Alumni Table */
    .alumni-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 11px;
        margin-top: 8px;
    }

    .alumni-table thead th {
        background: #32418c;
        color: #ffffff;
        text-align: left;
        padding: 10px 10px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.8px;
        text-transform: uppercase;
        border-bottom: 2px solid #fbd117;
    }

    .alumni-table thead th.center { text-align: center; }

    .alumni-table tbody td {
        padding: 8px 10px;
        border-bottom: 1px solid #F3F4F6;
        color: #374151;
        vertical-align: middle;
    }

    .alumni-table tbody tr:nth-child(even) { background: #F9FAFB; }

    .name-cell {
        font-weight: 600;
        color: #32418c;
    }

    .badge {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 10px;
        font-size: 9px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .badge-college { background: #E8EBF7; color: #32418c; }
    .badge-shs { background: #FEF3C7; color: #92400E; }
    .badge-unknown { background: #F3F4F6; color: #6B7280; }
    .badge-active { background: #D1FAE5; color: #065F46; }
    .badge-restricted { background: #FEE2E2; color: #991B1B; }

    .row-num {
        color: #9CA3AF;
        font-size: 10px;
        font-weight: 600;
        text-align: center;
        width: 32px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #9CA3AF;
        font-style: italic;
        font-size: 12px;
    }

    /* Print-specific */
    @media print {
        .no-print { display: none !important; }
        body { background: #ffffff; }
        .page-wrapper {
            max-width: 100%;
            margin: 0;
            box-shadow: none;
            border-radius: 0;
        }
        .cover {
            height: 297mm;
            page-break-after: always;
        }
        .page-content { padding: 24px 28px; }
        .alumni-table tbody tr { page-break-inside: avoid; }
    }
</style>
</head>
<body>

{{-- ============================= --}}
{{-- FLOATING ACTION BAR (hidden when printing) --}}
{{-- ============================= --}}
<div class="no-print">
    <button class="btn-print" onclick="window.print()">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
            <polyline points="6 9 6 2 18 2 18 9"></polyline>
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path>
            <rect x="6" y="14" width="12" height="8"></rect>
        </svg>
        Print / Save as PDF
    </button>
    <a class="btn-back" href="{{ route('admin.directory') }}">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align: middle;">
            <line x1="19" y1="12" x2="5" y2="12"></line>
            <polyline points="12 19 5 12 12 5"></polyline>
        </svg>
        Back to Directory
    </a>
</div>

<div class="page-wrapper">

    {{-- ============================= --}}
    {{-- COVER PAGE --}}
    {{-- ============================= --}}
    <div class="cover">
        <div class="cover-band-top"></div>
        <div class="cover-band-gold"></div>
        <div class="cover-circle-1"></div>
        <div class="cover-circle-2"></div>
        <div class="cover-circle-3"></div>

        <div class="cover-logos">
            <img src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs" class="logo-luminus" onerror="this.style.display='none'">
            <img src="/assets/logos/NULIPA_AAO_White.png" alt="National University" class="logo-nu" onerror="this.style.display='none'">
        </div>

        <div class="cover-content">
            <div class="cover-eyebrow">Official Report</div>
            <h1 class="cover-title">
                Alumni <span class="cover-title-accent">Directory</span>
            </h1>
            <p class="cover-subtitle">
                National University Lipa &bull; LumiNUs Alumni Management System
            </p>
        </div>

        <div class="cover-meta-wrap">
            <div class="cover-meta-pill">
                <span class="meta-label">Total Alumni Records</span>
                <span class="meta-value">{{ number_format($totalAlumni) }}</span>
                <span class="meta-divider"></span>
                <span class="meta-label">Generated</span>
                <span class="meta-value" style="font-size: 18px;">
                    {{ $generatedAt->format('M d, Y') }}
                </span>
            </div>
        </div>

        <div class="cover-footer">
            <div class="cover-footer-brand">LUMINUS &bull; NATIONAL UNIVERSITY LIPA</div>
            <div class="cover-footer-note">
                Generated by <strong>{{ $generatedBy }}</strong> on
                {{ $generatedAt->format('F d, Y \a\t g:i A') }}
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- SUMMARY PAGE --}}
    {{-- ============================= --}}
    <div class="page-header">
        <div class="page-header-left">
            <img src="/assets/logos/LumiNUs_Icon.png" alt="LumiNUs" onerror="this.style.display='none'">
            <span class="page-header-title">Alumni Directory Report</span>
        </div>
        <div class="page-header-right">
            <strong>LumiNUs</strong> &bull; NU Lipa
        </div>
    </div>

    <div class="page-content">
        <div class="section-title">Executive Summary</div>

        <div class="stats-grid">
            <div class="stat-cell">
                <span class="stat-label">Total Alumni</span>
                <span class="stat-value">{{ number_format($totalAlumni) }}</span>
            </div>
            <div class="stat-cell gold">
                <span class="stat-label">With Email</span>
                <span class="stat-value">{{ number_format($stats['withEmail']) }}</span>
            </div>
            <div class="stat-cell">
                <span class="stat-label">Active</span>
                <span class="stat-value">{{ number_format($stats['active']) }}</span>
            </div>
            <div class="stat-cell gold">
                <span class="stat-label">Restricted</span>
                <span class="stat-value">{{ number_format($stats['restricted']) }}</span>
            </div>
        </div>

        <div class="section-title">Alumni Distribution</div>

        <div class="dist-grid">
            <div class="dist-box">
                <div class="dist-box-title">By Program</div>
                @forelse($byProgram->take(10) as $program => $count)
                    <div class="dist-row">
                        <span class="dist-name">{{ $program }}</span>
                        <span class="dist-count">{{ $count }}</span>
                    </div>
                @empty
                    <div style="font-size: 11px; color: #9CA3AF; font-style: italic;">No program data</div>
                @endforelse
            </div>

            <div>
                <div class="dist-box" style="margin-bottom: 16px;">
                    <div class="dist-box-title">By Alumni Type</div>
                    <div class="dist-row">
                        <span class="dist-name">College</span>
                        <span class="dist-count">{{ $byType['College'] }}</span>
                    </div>
                    <div class="dist-row gold">
                        <span class="dist-name">Senior High</span>
                        <span class="dist-count">{{ $byType['SHS'] }}</span>
                    </div>
                    @if($byType['Unknown'] > 0)
                    <div class="dist-row">
                        <span class="dist-name">Unknown</span>
                        <span class="dist-count">{{ $byType['Unknown'] }}</span>
                    </div>
                    @endif
                </div>

                @if($byYear->count() > 0)
                <div class="dist-box">
                    <div class="dist-box-title">Top Graduation Years</div>
                    @foreach($byYear->take(6) as $year => $count)
                        <div class="dist-row">
                            <span class="dist-name">{{ $year }}</span>
                            <span class="dist-count">{{ $count }}</span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ============================= --}}
    {{-- FULL LISTING --}}
    {{-- ============================= --}}
    <div style="page-break-before: always;">
        <div class="page-header">
            <div class="page-header-left">
                <img src="/assets/logos/LumiNUs_Icon.png" alt="LumiNUs" onerror="this.style.display='none'">
                <span class="page-header-title">Full Alumni Listing</span>
            </div>
            <div class="page-header-right">
                <strong>{{ number_format($totalAlumni) }}</strong> Records
            </div>
        </div>

        <div class="page-content">
            <div class="section-title">Registered Alumni Members</div>

            @if($alumni->isEmpty())
                <div class="empty-state">No alumni records available.</div>
            @else
                <table class="alumni-table">
                    <thead>
                        <tr>
                            <th class="row-num">#</th>
                            <th style="width: 24%;">Name</th>
                            <th style="width: 22%;">Email</th>
                            <th style="width: 15%;">Program</th>
                            <th style="width: 10%;">Year</th>
                            <th style="width: 10%;" class="center">Type</th>
                            <th style="width: 11%;" class="center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($alumni as $i => $a)
                            @php
                                $mi = $a->middle_name ? ' ' . strtoupper(substr(trim($a->middle_name), 0, 1)) . '.' : '';
                                $fullName = trim(($a->last_name ?? '') . ', ' . ($a->first_name ?? '') . $mi);
                                $typeClass = $a->alumni_type === 'shs' ? 'badge-shs' : ($a->alumni_type === 'college' ? 'badge-college' : 'badge-unknown');
                                $typeLabel = $a->alumni_type ? strtoupper($a->alumni_type) : 'N/A';
                                $statusClass = ($a->account_status ?? 1) == 1 ? 'badge-active' : 'badge-restricted';
                                $statusLabel = ($a->account_status ?? 1) == 1 ? 'Active' : 'Restricted';
                            @endphp
                            <tr>
                                <td class="row-num">{{ $i + 1 }}</td>
                                <td class="name-cell">{{ $fullName }}</td>
                                <td style="font-size: 10px;">{{ $a->email ?? '—' }}</td>
                                <td style="font-size: 10px;">{{ $a->program ?? '—' }}</td>
                                <td style="font-size: 10px;">
                                    {{ $a->year_graduated ? $a->year_graduated->format('Y') : '—' }}
                                </td>
                                <td class="center">
                                    <span class="badge {{ $typeClass }}">{{ $typeLabel }}</span>
                                </td>
                                <td class="center">
                                    <span class="badge {{ $statusClass }}">{{ $statusLabel }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

</div>

</body>
</html>