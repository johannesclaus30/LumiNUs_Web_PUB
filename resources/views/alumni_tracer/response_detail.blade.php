@php
    /**
     * Format the answer for display
     * This must be defined BEFORE it's called
     */
    function formatAnswerDisplay($answers, $question)
    {
        $questionType = $question->type;
        $answers = $answers instanceof \Illuminate\Support\Collection
            ? $answers
            : collect($answers ? [$answers] : []);
        $answer = $answers->first();

        if ($answers->isEmpty()) {
            return '<i class="fa-regular fa-circle"></i> Not answered yet';
        }

        switch ($questionType) {
            case 'short_answer':
            case 'paragraph':
                $value = e($answer->answer_value ?? '');
                return nl2br($value);

            case 'multiple_choice':
                $selection = $answer->selections->first();
                if ($selection && $selection->option) {
                    return '<span style="background:#EEF2FF;color:#32418C;padding:0.25rem 0.75rem;border-radius:9999px;font-weight:500;">' . e($selection->option->option_label) . '</span>';
                }
                return 'No selection';

            case 'checkboxes':
                $selections = $answer->selections;
                if ($selections->isEmpty()) {
                    return 'No selections';
                }
                $tags = [];
                foreach ($selections as $sel) {
                    if ($sel->option) {
                        $tags[] = '<span class="tag" style="background:#EEF2FF;color:#32418C;padding:0.25rem 0.75rem;border-radius:9999px;font-size:0.8125rem;font-weight:500;">' . e($sel->option->option_label) . '</span>';
                    }
                }
                return '<div class="checkbox-answers">' . implode('', $tags) . '</div>';

            case 'dropdown':
                $selection = $answer->selections->first();
                if ($selection && $selection->option) {
                    return '<span style="background:#EEF2FF;color:#32418C;padding:0.25rem 0.75rem;border-radius:9999px;font-weight:500;">' . e($selection->option->option_label) . '</span>';
                }
                return 'No selection';

            case 'file_upload':
                if ($answer->file_path) {
                    $fileName = e($answer->file_name ?? 'Download File');
                    $filePath = e($answer->file_path);
                    return '<a href="' . $filePath . '" target="_blank" class="file-link"><i class="fa-solid fa-paperclip"></i> ' . $fileName . '</a>';
                }
                return 'No file uploaded';

            case 'likert_scale':
            case 'multiple_choice_grid':
                $columns = $question->gridColumns ?? collect();
                $rows = $question->gridRows ?? collect();
                $selected = [];

                foreach ($answers as $gridAnswer) {
                    $rowId = $gridAnswer->grid_row_id;
                    foreach ($gridAnswer->selections as $selection) {
                        if ($rowId && $selection->grid_column_id) {
                            $selected[$rowId][$selection->grid_column_id] = true;
                        }
                    }
                }

                $html = '<div class="grid-answers"><table><thead><tr><th>Statement</th>';
                foreach ($columns as $column) {
                    $html .= '<th>' . e($column->column_label) . '</th>';
                }
                $html .= '</tr></thead><tbody>';

                foreach ($rows as $row) {
                    $rowSelected = $selected[$row->id] ?? [];
                    $html .= '<tr><td><strong>' . e($row->row_label) . '</strong></td>';
                    foreach ($columns as $column) {
                        $isSelected = isset($rowSelected[$column->id]);
                        $html .= '<td aria-label="' . e($isSelected ? 'Selected' : 'Not answered') . '">'
                            . ($isSelected ? '<span class="grid-selected">✓</span>' : '<span class="grid-not-answered">—</span>')
                            . '</td>';
                    }
                    $html .= '</tr>';
                }

                $html .= '</tbody></table></div>';
                return $html;

            default:
                return e($answer->answer_value ?? '');
        }
    }

    $typeIcons = [
        'short_answer' => 'fa-i-cursor',
        'paragraph' => 'fa-align-left',
        'multiple_choice' => 'fa-circle-dot',
        'checkboxes' => 'fa-check-square',
        'dropdown' => 'fa-chevron-down',
        'file_upload' => 'fa-cloud-arrow-up',
        'likert_scale' => 'fa-table-list',
        'multiple_choice_grid' => 'fa-border-all'
    ];

    $typeLabels = [
        'short_answer' => 'Short Answer',
        'paragraph' => 'Paragraph',
        'multiple_choice' => 'Multiple Choice',
        'checkboxes' => 'Checkboxes',
        'dropdown' => 'Dropdown',
        'file_upload' => 'File Upload',
        'likert_scale' => 'Likert Scale',
        'multiple_choice_grid' => 'Multiple Choice Grid'
    ];
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Response Detail | {{ $response->alumni->first_name }} {{ $response->alumni->last_name }}</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/alumni_tracer_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* ========================================
           OVERRIDE: Make page scrollable
           ======================================== */
        html, body {
            overflow: auto !important;
            height: auto !important;
            min-height: 100vh;
        }

        body {
            background: #F0F2F5;
        }

        /* ========================================
           RESPONSE DETAIL STYLES
           ======================================== */
        :root {
            --primary: #32418C;
            --primary-light: #4a59a3;
            --primary-dark: #253069;
            --accent: #FBD117;
            --success: #10b981;
            --warning: #f59e0b;
            --danger: #ef4444;
            --info: #3b82f6;
            --gray-50: #F9FAFB;
            --gray-100: #F3F4F6;
            --gray-200: #E5E7EB;
            --gray-300: #D1D5DB;
            --gray-400: #9CA3AF;
            --gray-500: #6B7280;
            --gray-600: #4B5563;
            --gray-700: #374151;
            --gray-800: #1F2937;
            --radius-sm: 0.5rem;
            --radius-md: 0.75rem;
            --radius-lg: 1rem;
            --radius-xl: 1.25rem;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        /* ========================================
           SIDEBAR + LAYOUT - MATCH ADMIN
           ======================================== */
        .admin-layout {
            display: flex;
            min-height: 100vh;
            position: relative;
        }

        .admin-main {
            flex: 1;
            margin-left: 280px;
            padding: 2rem 2rem 4rem 2rem;
            min-height: 100vh;
            background: #F0F2F5;
        }

        @media (max-width: 1024px) {
            .admin-main {
                margin-left: 0;
                padding: 5rem 1.5rem 1.5rem;
            }
        }

        /* ========================================
           RESPONSE DETAIL CONTAINER
           ======================================== */
        .response-detail-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Back Button */
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.625rem 1.25rem;
            background: #FFFFFF;
            border: 2px solid #E5E7EB;
            border-radius: 0.75rem;
            color: #4B5563;
            font-family: 'Poppins', sans-serif;
            font-weight: 600;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            margin-top: 3rem;
            margin-bottom: 1.5rem;
        }

        .back-button:hover {
            border-color: #32418C;
            color: #32418C;
            transform: translateX(-2px);
        }

        /* Alumni Profile Header */
        .alumni-profile-header {
            background: #FFFFFF;
            border-radius: 1.25rem;
            box-shadow: var(--shadow-md);
            border: 1px solid #E5E7EB;
            padding: 2rem;
            margin-bottom: 2rem;
            display: flex;
            gap: 2rem;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .alumni-avatar-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #32418C, #4a59a3);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: #FFFFFF;
            flex-shrink: 0;
            box-shadow: 0 4px 14px rgba(50, 65, 140, 0.25);
        }

        .alumni-avatar-large img {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            object-fit: cover;
        }

        .alumni-profile-info {
            flex: 1;
            min-width: 200px;
        }

        .alumni-profile-info .name {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1F2937;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            flex-wrap: wrap;
        }

        .alumni-profile-info .name .type-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.375rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.6875rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .type-badge.college {
            background: #DBEAFE;
            color: #1D4ED8;
        }

        .type-badge.shs {
            background: #EDE9FE;
            color: #6D28D9;
        }

        .alumni-profile-info .details {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
            margin-top: 0.5rem;
        }

        .alumni-profile-info .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6B7280;
        }

        .alumni-profile-info .detail-item i {
            color: #9CA3AF;
            width: 18px;
            text-align: center;
        }

        .alumni-profile-info .detail-item strong {
            color: #374151;
            font-weight: 600;
        }

        /* Response Status */
        .response-status-section {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 0.5rem;
            flex-shrink: 0;
        }

        .response-status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1.25rem;
            border-radius: 9999px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .response-status-badge.completed {
            background: #D1FAE5;
            color: #065F46;
        }

        .response-status-badge.in_progress {
            background: #FEF3C7;
            color: #92400E;
        }

        .response-meta {
            font-size: 0.75rem;
            color: #9CA3AF;
            text-align: right;
        }

        /* Progress Overview - 3 columns */
        .progress-overview {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .progress-stat-card {
            background: #FFFFFF;
            padding: 1.25rem 1.5rem;
            border-radius: 1rem;
            border: 1px solid #E5E7EB;
            box-shadow: var(--shadow-sm);
            text-align: center;
            transition: all 0.2s ease;
        }

        .progress-stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .progress-stat-card .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: #1F2937;
            line-height: 1.2;
        }

        .progress-stat-card .stat-value .percent {
            font-size: 1.25rem;
            color: #9CA3AF;
        }

        .progress-stat-card .stat-label {
            font-size: 0.8125rem;
            color: #6B7280;
            font-weight: 500;
            margin-top: 0.25rem;
        }

        .progress-stat-card .stat-icon {
            font-size: 1.5rem;
            margin-bottom: 0.25rem;
            display: block;
        }

        /* Phase Progress */
        .phase-progress-list {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        .phase-progress-item {
            background: #FFFFFF;
            border-radius: 0.875rem;
            padding: 0.75rem 1.25rem;
            border: 1px solid #E5E7EB;
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.2s ease;
        }

        .phase-progress-item:hover {
            border-color: #D1D5DB;
            box-shadow: var(--shadow-sm);
        }

        .phase-progress-item .phase-icon {
            width: 36px;
            height: 36px;
            border-radius: 0.625rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            flex-shrink: 0;
            font-size: 0.875rem;
        }

        .phase-progress-item .phase-info {
            flex: 1;
            min-width: 0;
        }

        .phase-progress-item .phase-name {
            font-weight: 600;
            font-size: 0.875rem;
            color: #1F2937;
        }

        .phase-progress-item .phase-count {
            font-size: 0.75rem;
            color: #9CA3AF;
        }

        .phase-progress-item .phase-bar {
            width: 120px;
            flex-shrink: 0;
        }

        .phase-progress-item .phase-bar .bar-track {
            height: 6px;
            background: #F3F4F6;
            border-radius: 9999px;
            overflow: hidden;
        }

        .phase-progress-item .phase-bar .bar-fill {
            height: 100%;
            border-radius: 9999px;
            transition: width 0.6s ease;
        }

        .phase-progress-item .phase-percent {
            font-weight: 700;
            font-size: 0.875rem;
            color: #1F2937;
            min-width: 44px;
            text-align: right;
        }

        /* Questions & Answers */
        .answers-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .phase-answer-card {
            background: #FFFFFF;
            border-radius: 1.25rem;
            border: 1px solid #E5E7EB;
            box-shadow: var(--shadow-sm);
            overflow: hidden;
        }

        .phase-answer-header {
            display: flex;
            align-items: center;
            gap: 0.875rem;
            padding: 1.25rem 1.5rem;
            border-bottom: 2px solid #F3F4F6;
            background: #FAFAFA;
        }

        .phase-answer-header .phase-icon {
            width: 40px;
            height: 40px;
            border-radius: 0.75rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FFFFFF;
            font-size: 1rem;
            flex-shrink: 0;
        }

        .phase-answer-header .phase-title {
            font-size: 1.0625rem;
            font-weight: 700;
            color: #1F2937;
        }

        .phase-answer-header .phase-subtitle {
            font-size: 0.8125rem;
            color: #6B7280;
        }

        .phase-answer-header .phase-completion {
            margin-left: auto;
            font-size: 0.8125rem;
            font-weight: 600;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
        }

        .phase-answer-header .phase-completion.complete {
            background: #D1FAE5;
            color: #065F46;
        }

        .phase-answer-header .phase-completion.partial {
            background: #FEF3C7;
            color: #92400E;
        }

        .phase-answer-header .phase-completion.empty {
            background: #F3F4F6;
            color: #9CA3AF;
        }

        .section-answers {
            padding: 1rem 1.5rem 0;
        }

        .section-title {
            font-size: 0.9375rem;
            font-weight: 600;
            color: #374151;
            padding: 0.75rem 0 0.5rem;
            border-bottom: 1px solid #F3F4F6;
            margin-bottom: 0.75rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .section-title .section-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 26px;
            height: 26px;
            border-radius: 0.5rem;
            font-size: 0.625rem;
            font-weight: 700;
            color: #FFFFFF;
            flex-shrink: 0;
        }

        .section-title .section-desc {
            font-size: 0.75rem;
            font-weight: 400;
            color: #9CA3AF;
        }

        .question-answer-item {
            padding: 0.875rem 0;
            border-bottom: 1px solid #F9FAFB;
        }

        .question-answer-item:last-child {
            border-bottom: none;
        }

        .question-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: #374151;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        .question-label .required-star {
            color: #EF4444;
        }

        .question-label .question-type-tag {
            font-size: 0.5625rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #9CA3AF;
            background: #F3F4F6;
            padding: 0.0625rem 0.5rem;
            border-radius: 0.25rem;
            margin-left: 0.5rem;
        }

        .answer-content {
            margin-top: 0.5rem;
            padding: 0.75rem 1rem;
            background: #F9FAFB;
            border-radius: 0.625rem;
            border-left: 4px solid #32418C;
            font-size: 0.875rem;
            color: #1F2937;
            word-break: break-word;
        }

        .answer-content.no-answer {
            color: #9CA3AF;
            font-style: italic;
            border-left-color: #D1D5DB;
        }

        .answer-content .file-link {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.375rem 0.75rem;
            background: #FFFFFF;
            border: 1px solid #E5E7EB;
            border-radius: 0.5rem;
            color: #32418C;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.8125rem;
            transition: all 0.2s ease;
        }

        .answer-content .file-link:hover {
            border-color: #32418C;
            background: #EEF2FF;
        }

        .answer-content .checkbox-answers {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .answer-content .checkbox-answers .tag {
            padding: 0.25rem 0.75rem;
            background: #EEF2FF;
            color: #32418C;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 500;
        }

        .answer-content .grid-answers {
            overflow-x: auto;
        }

        .answer-content .grid-answers table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }

        .answer-content .grid-answers table th,
        .answer-content .grid-answers table td {
            padding: 0.375rem 0.75rem;
            border: 1px solid #E5E7EB;
            text-align: center;
        }

        .answer-content .grid-answers table th {
            background: #F9FAFB;
            font-weight: 600;
            color: #4B5563;
        }

        .answer-content .grid-answers table td:first-child {
            text-align: left;
            font-weight: 500;
            background: #FAFAFA;
        }

        .empty-answers {
            text-align: center;
            padding: 3rem 2rem;
            color: #9CA3AF;
        }

        .empty-answers i {
            font-size: 3rem;
            color: #D1D5DB;
            margin-bottom: 1rem;
            display: block;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .alumni-profile-header {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 1.5rem;
            }

            .alumni-profile-info .details {
                justify-content: center;
            }

            .response-status-section {
                align-items: center;
                width: 100%;
            }

            .progress-overview {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 768px) {
            .phase-progress-item {
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .phase-progress-item .phase-bar {
                width: 100%;
            }

            .phase-answer-header {
                flex-wrap: wrap;
            }

            .phase-answer-header .phase-completion {
                margin-left: 0;
            }

            .progress-overview {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 480px) {
            .alumni-profile-header {
                padding: 1rem;
            }

            .phase-answer-header {
                flex-direction: column;
                align-items: flex-start;
                text-align: left;
            }

            .phase-answer-header .phase-completion {
                margin-left: 0;
                align-self: flex-start;
            }

            .section-title {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: #D1D5DB;
            border-radius: 9999px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #9CA3AF;
        }

        /* Mobile overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 998;
            opacity: 0;
            transition: opacity 0.2s ease;
        }

        .mobile-overlay.active {
            opacity: 1;
            display: block;
        }

        .mobile-menu-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 997;
            width: 44px;
            height: 44px;
            border: none;
            background: #32418C;
            color: #FFFFFF;
            border-radius: 0.75rem;
            cursor: pointer;
            font-size: 1.25rem;
            box-shadow: 0 6px 12px -2px rgb(0 0 0 / 0.1);
            transition: all 0.2s ease;
            align-items: center;
            justify-content: center;
        }

        .mobile-menu-toggle:hover {
            background: #253069;
            transform: scale(1.05);
        }

        @media (max-width: 1024px) {
            .mobile-menu-toggle {
                display: flex;
            }
            .admin-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .admin-sidebar.mobile-open {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body>

    @include('partials.admin-navbar')

    <!-- Mobile Menu Toggle -->
    <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="/assets/logos/LumiNUs_Logo_Landscape_Blue.png" alt="LumiNUs Logo" class="logo-luminus">
                </div>
                <button class="sidebar-close" id="sidebarClose" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            @php
                $currentAdmin = null;
                $adminId = session('admin_id');
                if ($adminId) {
                    $currentAdmin = \App\Models\Admin::find($adminId);
                }
                $accessibleModules = $currentAdmin ? $currentAdmin->getAccessibleModules() : [];
            @endphp

            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                
                @if(isset($accessibleModules['dashboard']))
                <a href="/admin/dashboard" class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-chart-line"></i><span>Dashboard</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['directory']))
                <a href="/admin/directory" class="nav-item {{ request()->is('admin/directory*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users"></i><span>Alumni Directory</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['announcements']))
                <a href="{{ route('announcements.index') }}" class="nav-item {{ request()->is('admin/announcements*') ? 'active' : '' }}">
                    <i class="fa-solid fa-bullhorn"></i><span>Announcements</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['events']))
                <a href="{{ route('events.index') }}" class="nav-item {{ request()->is('admin/events*') ? 'active' : '' }}">
                    <i class="fa-solid fa-calendar-check"></i><span>Events</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['perks']))
                <a href="{{ route('perks.index') }}" class="nav-item {{ request()->is('admin/perks*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gift"></i><span>Perks & Discounts</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['tracer']))
                <a href="/admin/alumni_tracer" class="nav-item {{ request()->is('admin/alumni_tracer*') ? 'active' : '' }}">
                    <i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['messages']))
                <a href="/admin/messages" class="nav-item {{ request()->is('admin/messages*') ? 'active' : '' }}">
                    <i class="fa-solid fa-envelope"></i><span>Messages</span>
                </a>
                @endif
                
                @if(isset($accessibleModules['settings']))
                <a href="{{ route('admin.settings') }}" class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
                    <i class="fa-solid fa-gear"></i><span>Settings</span>
                </a>
                @endif
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="response-detail-container">
                <!-- Back Button -->
                <a href="/admin/alumni_tracer" class="back-button">
                    <i class="fa-solid fa-arrow-left"></i>
                    Back to Alumni Tracer
                </a>

                <!-- Alumni Profile Header -->
                <div class="alumni-profile-header">
                    <div class="alumni-avatar-large">
                        @if($response->alumni->alumni_photo)
                            <img src="{{ $response->alumni->alumni_photo }}" alt="{{ $response->alumni->first_name }}">
                        @else
                            {{ strtoupper(substr($response->alumni->first_name, 0, 1) . substr($response->alumni->last_name, 0, 1)) }}
                        @endif
                    </div>

                    <div class="alumni-profile-info">
                        <div class="name">
                            {{ $response->alumni->first_name }} {{ $response->alumni->middle_name ? $response->alumni->middle_name . ' ' : '' }}{{ $response->alumni->last_name }}
                            <span class="type-badge {{ $response->alumni->alumni_type ?? 'college' }}">
                                <i class="fa-solid {{ $response->alumni->alumni_type === 'shs' ? 'fa-school' : 'fa-graduation-cap' }}"></i>
                                {{ strtoupper($response->alumni->alumni_type ?? 'College') }}
                            </span>
                        </div>

                        <div class="details">
                            <span class="detail-item">
                                <i class="fa-solid fa-envelope"></i>
                                {{ $response->alumni->email }}
                            </span>
                            <span class="detail-item">
                                <i class="fa-solid fa-graduation-cap"></i>
                                <strong>Program:</strong> {{ $response->alumni->program ?? 'N/A' }}
                            </span>
                            <span class="detail-item">
                                <i class="fa-solid fa-calendar"></i>
                                <strong>Batch:</strong> {{ date('Y', strtotime($response->alumni->year_graduated)) }}
                            </span>
                            <span class="detail-item">
                                <i class="fa-solid fa-id-card"></i>
                                <strong>ID:</strong> {{ $response->alumni->student_id_number }}
                            </span>
                        </div>
                    </div>

                    <div class="response-status-section">
                        <span class="response-status-badge {{ $response->status }}">
                            <i class="fa-solid {{ $response->status === 'completed' ? 'fa-check-circle' : 'fa-clock' }}"></i>
                            {{ $response->status === 'completed' ? 'Completed' : 'In Progress' }}
                        </span>
                        <div class="response-meta">
                            @if($response->submitted_at)
                                <div>Submitted: {{ $response->submitted_at->format('M d, Y h:i A') }}</div>
                            @endif
                            <div>Last updated: {{ $response->updated_at->format('M d, Y h:i A') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Progress Overview - 3 columns -->
                <div class="progress-overview">
                    <div class="progress-stat-card">
                        <span class="stat-icon" style="color: #32418C;">📊</span>
                        <div class="stat-value">{{ $completionPercentage }}<span class="percent">%</span></div>
                        <div class="stat-label">Overall Completion</div>
                    </div>
                    <div class="progress-stat-card">
                        <span class="stat-icon" style="color: #10b981;">✅</span>
                        <div class="stat-value">{{ $answeredQuestions }}<span class="percent">/{{ $totalQuestions }}</span></div>
                        <div class="stat-label">Questions Answered</div>
                    </div>
                    <div class="progress-stat-card">
                        <span class="stat-icon" style="color: #f59e0b;">📝</span>
                        <div class="stat-value">{{ $phases->count() }}</div>
                        <div class="stat-label">Total Phases</div>
                    </div>
                </div>

                <!-- Phase Progress Bars -->
                <div class="phase-progress-list">
                    @foreach($phaseProgress as $progress)
                        <div class="phase-progress-item">
                            <div class="phase-icon" style="background: {{ $progress['phase']->color ?? '#32418C' }};">
                                <i class="fa-solid {{ $progress['phase']->icon ?? 'fa-layer-group' }}"></i>
                            </div>
                            <div class="phase-info">
                                <div class="phase-name">{{ $progress['phase']->title }}</div>
                                <div class="phase-count">{{ $progress['answered'] }} of {{ $progress['total'] }} questions answered</div>
                            </div>
                            <div class="phase-bar">
                                <div class="bar-track">
                                    <div class="bar-fill" style="width: {{ $progress['percentage'] }}%; background: {{ $progress['percentage'] === 100 ? '#10b981' : ($progress['percentage'] > 0 ? '#f59e0b' : '#D1D5DB') }};"></div>
                                </div>
                            </div>
                            <div class="phase-percent" style="color: {{ $progress['percentage'] === 100 ? '#10b981' : ($progress['percentage'] > 0 ? '#f59e0b' : '#9CA3AF') }};">
                                {{ $progress['percentage'] }}%
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Questions & Answers -->
                <div class="answers-container">
                    @foreach($phases as $phaseIndex => $phase)
                        @php
                            $phaseTotal = $phaseProgress[$phaseIndex]['total'] ?? 0;
                            $phaseAnswered = $phaseProgress[$phaseIndex]['answered'] ?? 0;
                            $phasePercent = $phaseProgress[$phaseIndex]['percentage'] ?? 0;
                            $phaseStatus = $phasePercent === 100 ? 'complete' : ($phasePercent > 0 ? 'partial' : 'empty');
                        @endphp

                        <div class="phase-answer-card">
                            <div class="phase-answer-header">
                                <div class="phase-icon" style="background: {{ $phase->color ?? '#32418C' }};">
                                    <i class="fa-solid {{ $phase->icon ?? 'fa-layer-group' }}"></i>
                                </div>
                                <div>
                                    <div class="phase-title">{{ $phase->title }}</div>
                                    <div class="phase-subtitle">{{ $phase->subtitle ?? 'No subtitle' }}</div>
                                </div>
                                <span class="phase-completion {{ $phaseStatus }}">
                                    <i class="fa-solid {{ $phasePercent === 100 ? 'fa-check-circle' : ($phasePercent > 0 ? 'fa-clock' : 'fa-circle') }}"></i>
                                    {{ $phaseAnswered }}/{{ $phaseTotal }} ({{ $phasePercent }}%)
                                </span>
                            </div>

                            @foreach($phase->sections as $sectionIndex => $section)
                                <div class="section-answers">
                                    <div class="section-title">
                                        <span class="section-number" style="background: {{ $phase->color ?? '#32418C' }};">
                                            {{ $sectionIndex + 1 }}
                                        </span>
                                        {{ $section->title }}
                                        @if($section->description)
                                            <span class="section-desc">— {{ $section->description }}</span>
                                        @endif
                                    </div>

                                    @forelse($section->questions as $question)
                                        @php
                                            $answers = $response->answers->where('question_id', $question->id);
                                            $hasAnswer = $answers->contains(function ($answer) {
                                                return trim((string) ($answer->answer_value ?? '')) !== ''
                                                    || !empty($answer->file_path)
                                                    || $answer->selections->isNotEmpty();
                                            });
                                            $answerDisplay = formatAnswerDisplay($answers, $question);
                                        @endphp

                                        <div class="question-answer-item">
                                            <div class="question-label">
                                                <span>
                                                    {{ $question->question_text }}
                                                    @if($question->is_required)
                                                        <span class="required-star">*</span>
                                                    @endif
                                                    <span class="question-type-tag">
                                                        <i class="fa-solid {{ $typeIcons[$question->type] ?? 'fa-question' }}"></i>
                                                        {{ $typeLabels[$question->type] ?? $question->type }}
                                                    </span>
                                                </span>
                                            </div>

                                            <div class="answer-content {{ $hasAnswer ? '' : 'no-answer' }}">
                                                @if($hasAnswer)
                                                    {!! $answerDisplay !!}
                                                @else
                                                    <i class="fa-regular fa-circle"></i> Not answered yet
                                                @endif
                                            </div>
                                        </div>

                                    @empty
                                        <div class="empty-answers">
                                            <i class="fa-regular fa-file-lines"></i>
                                            <p>No questions in this section</p>
                                        </div>
                                    @endforelse
                                </div>
                            @endforeach

                            @if($phase->sections->isEmpty())
                                <div class="section-answers" style="padding-bottom: 1.5rem;">
                                    <div class="empty-answers">
                                        <i class="fa-regular fa-folder-open"></i>
                                        <p>No sections in this phase</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </main>
    </div>

    <script>
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

        // ============================================
        // PHASE PROGRESS BAR ANIMATION
        // ============================================
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.phase-progress-item .bar-fill').forEach((bar, index) => {
                const targetWidth = bar.style.width;
                bar.style.width = '0%';
                setTimeout(() => {
                    bar.style.width = targetWidth;
                }, 100 + (index * 50));
            });
        });
    </script>

</body>
</html>