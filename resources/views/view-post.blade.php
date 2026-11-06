<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Post | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <style>
        /* ========================================
           POST VIEW SPECIFIC STYLES
           ======================================== */

        .post-view-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }

        .post-view-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 20px;
            border: 1px solid #e8edf4;
        }

        /* Post Header */
        .post-view-header {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 16px;
            border-bottom: 2px solid #f0f2f5;
            margin-bottom: 20px;
        }

        .post-view-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #32418C, #4a59a3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .post-view-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .post-view-author {
            flex: 1;
        }

        .post-view-author-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-view-author-name i {
            color: #3b82f6;
            font-size: 0.85rem;
        }

        .post-view-author-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 8px 16px;
            font-size: 0.85rem;
            color: #94a3b8;
            margin-top: 4px;
        }

        .post-view-author-meta span {
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .post-view-author-meta .pst-time {
            color: #059669;
            font-weight: 500;
        }

        .post-view-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            align-self: flex-start;
            margin-top: 4px;
        }

        .post-view-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 14px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .post-view-badge.visibility-public {
            background: #dbeafe;
            color: #1e40af;
        }

        .post-view-badge.visibility-private {
            background: #f1f5f9;
            color: #475569;
        }

        .post-view-badge.moderation-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .post-view-badge.moderation-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .post-view-badge.moderation-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .post-view-badge.moderation-hidden {
            background: #f1f5f9;
            color: #475569;
        }

        /* Post Caption */
        .post-view-caption {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #1e293b;
            padding: 8px 0 16px 0;
            white-space: pre-wrap;
            word-wrap: break-word;
        }

        /* Post Images */
        .post-view-images {
            display: grid;
            gap: 8px;
            margin: 16px 0 20px 0;
        }

        .post-view-images.grid-1 {
            grid-template-columns: 1fr;
        }

        .post-view-images.grid-2 {
            grid-template-columns: 1fr 1fr;
        }

        .post-view-images.grid-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .post-view-images.grid-4 {
            grid-template-columns: 1fr 1fr;
        }

        .post-view-images.grid-5 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .post-view-images.grid-6 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .post-view-images img {
            width: 100%;
            height: 300px;
            object-fit: cover;
            border-radius: 12px;
            border: 1px solid #e8edf4;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .post-view-images img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-color: #32418C;
        }

        .post-view-images.grid-1 img {
            height: 450px;
        }

        .post-view-images.grid-4 img:first-child {
            grid-column: 1 / -1;
            height: 350px;
        }

        .post-view-images.grid-5 img:first-child {
            grid-column: 1 / -1;
            height: 320px;
        }

        /* ========================================
           POST STATS - Only Heart (Like), Comments, Reposts
           ======================================== */

        .post-view-stats {
            display: flex;
            gap: 24px;
            padding: 16px 0;
            border-top: 1px solid #f0f2f5;
            border-bottom: 1px solid #f0f2f5;
            margin: 16px 0;
        }

        .post-view-stat {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s ease;
            padding: 4px 12px;
            border-radius: 8px;
        }

        .post-view-stat:hover {
            background: #f1f5f9;
            color: #32418C;
        }

        .post-view-stat i {
            font-size: 1.1rem;
        }

        .post-view-stat .count {
            font-weight: 600;
            color: #1e293b;
        }

        /* ========================================
           COMMENTS SECTION
           ======================================== */

        .post-view-comments {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #f0f2f5;
        }

        .post-view-comments-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #1e293b;
            font-size: 1rem;
            margin-bottom: 16px;
        }

        .post-view-comments-header i {
            color: #32418C;
        }

        .post-view-comment {
            display: flex;
            gap: 12px;
            padding: 14px 16px;
            background: #f8fafc;
            border-radius: 12px;
            margin-bottom: 10px;
            transition: all 0.2s ease;
        }

        .post-view-comment:hover {
            background: #f1f5f9;
        }

        .post-view-comment-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .post-view-comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .post-view-comment-body {
            flex: 1;
        }

        .post-view-comment-author {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.9rem;
        }

        .post-view-comment-text {
            color: #475569;
            font-size: 0.95rem;
            line-height: 1.6;
            margin-top: 2px;
        }

        .post-view-comment-time {
            color: #94a3b8;
            font-size: 0.8rem;
            margin-top: 4px;
        }

        .post-view-comment-time i {
            margin-right: 4px;
        }

        .post-view-comment-time .pst-time {
            color: #059669;
            font-weight: 500;
        }

        .post-view-more-comments {
            display: inline-block;
            color: #32418C;
            font-size: 0.9rem;
            font-weight: 500;
            padding: 8px 16px;
            background: #e8edf4;
            border-radius: 8px;
            margin-top: 8px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            font-family: inherit;
        }

        .post-view-more-comments:hover {
            background: #d1d9e6;
        }

        /* Report Details */
        .post-view-report-details {
            background: #fef2f2;
            border-radius: 12px;
            padding: 16px 20px;
            border-left: 4px solid #ef4444;
            margin-top: 20px;
        }

        .post-view-report-details h4 {
            font-size: 0.9rem;
            font-weight: 600;
            color: #991b1b;
            margin: 0 0 8px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-view-report-details p {
            font-size: 0.9rem;
            color: #7f1d1d;
            margin: 4px 0;
        }

        .post-view-report-details .report-meta {
            font-size: 0.8rem;
            color: #991b1b;
            opacity: 0.7;
        }

        .post-view-report-details .pst-time {
            color: #059669;
            font-weight: 500;
        }

        /* ========================================
           BUTTON STYLES
           ======================================== */

        .post-view-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 2px solid #f0f2f5;
        }

        .post-view-actions .btn {
            min-width: 130px;
            justify-content: center;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: inherit;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-approve {
            background: #059669;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(5, 150, 105, 0.3);
        }

        .btn-approve:hover {
            background: #047857;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }

        .btn-approve:active {
            transform: translateY(0);
        }

        .btn-hide {
            background: #d97706;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.3);
        }

        .btn-hide:hover {
            background: #b45309;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.4);
        }

        .btn-hide:active {
            transform: translateY(0);
        }

        .btn-delete-post {
            background: #e11d48;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(225, 29, 72, 0.3);
        }

        .btn-delete-post:hover {
            background: #be123c;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(225, 29, 72, 0.4);
        }

        .btn-delete-post:active {
            transform: translateY(0);
        }

        .btn-restrict-user {
            background: #7c3aed;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(124, 58, 237, 0.3);
        }

        .btn-restrict-user:hover {
            background: #6d28d9;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(124, 58, 237, 0.4);
        }

        .btn-restrict-user:active {
            transform: translateY(0);
        }

        .btn-back-dashboard {
            background: #475569;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(71, 85, 105, 0.2);
        }

        .btn-back-dashboard:hover {
            background: #334155;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(71, 85, 105, 0.3);
        }

        .btn-back-dashboard:active {
            transform: translateY(0);
        }

        .btn-primary-empty {
            background: #2563eb;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.3);
        }

        .btn-primary-empty:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-primary-empty:active {
            transform: translateY(0);
        }

        /* ========================================
           RESPONSIVE
           ======================================== */

        @media (max-width: 768px) {
            .post-view-container {
                padding: 12px;
            }

            .post-view-card {
                padding: 20px;
                border-radius: 12px;
            }

            .post-view-header {
                flex-wrap: wrap;
            }

            .post-view-avatar {
                width: 44px;
                height: 44px;
                font-size: 16px;
            }

            .post-view-author-name {
                font-size: 1rem;
            }

            .post-view-badges {
                width: 100%;
                margin-top: 8px;
            }

            .post-view-caption {
                font-size: 1rem;
            }

            .post-view-images.grid-2,
            .post-view-images.grid-3,
            .post-view-images.grid-4,
            .post-view-images.grid-5,
            .post-view-images.grid-6 {
                grid-template-columns: 1fr 1fr;
            }

            .post-view-images img {
                height: 200px;
            }

            .post-view-images.grid-1 img {
                height: 280px;
            }

            .post-view-images.grid-4 img:first-child,
            .post-view-images.grid-5 img:first-child {
                height: 220px;
            }

            .post-view-stats {
                flex-wrap: wrap;
                gap: 12px;
            }

            .post-view-actions {
                flex-direction: column;
                gap: 10px;
            }

            .post-view-actions .btn {
                width: 100%;
                min-width: unset;
                justify-content: center;
            }
        }

        @media (max-width: 480px) {
            .post-view-images {
                grid-template-columns: 1fr !important;
            }

            .post-view-images img {
                height: 220px !important;
            }

            .post-view-images.grid-1 img {
                height: 240px !important;
            }

            .post-view-images.grid-4 img:first-child,
            .post-view-images.grid-5 img:first-child {
                height: 200px !important;
            }

            .post-view-card {
                padding: 16px;
            }

            .post-view-comment {
                padding: 10px 12px;
            }

            .post-view-comment-avatar {
                width: 32px;
                height: 32px;
                font-size: 12px;
            }

            .post-view-actions .btn {
                min-width: unset;
                width: 100%;
                padding: 12px;
                font-size: 0.85rem;
            }
        }

        /* ========================================
           INTERACTIONS MODAL STYLES
           ======================================== */

        .interactions-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
        }

        .interactions-modal.active {
            display: flex;
        }

        .interactions-modal-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            animation: fadeInOverlay 0.3s ease;
        }

        @keyframes fadeInOverlay {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .interactions-modal-content {
            position: relative;
            background: #ffffff;
            border-radius: 16px;
            max-width: 600px;
            width: 90%;
            max-height: 80vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            animation: slideUpModal 0.3s ease;
        }

        @keyframes slideUpModal {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .interactions-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px 24px;
            border-bottom: 1px solid #e8edf4;
            flex-shrink: 0;
        }

        .interactions-modal-title {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .interactions-modal-title i {
            color: #32418C;
            font-size: 1.2rem;
        }

        .interactions-count {
            font-weight: 400;
            color: #94a3b8;
            font-size: 0.9rem;
        }

        .interactions-modal-close {
            width: 36px;
            height: 36px;
            border: none;
            background: #f1f5f9;
            border-radius: 50%;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            transition: all 0.2s ease;
        }

        .interactions-modal-close:hover {
            background: #fee2e2;
            color: #dc2626;
            transform: rotate(90deg);
        }

        .interactions-modal-body {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            min-height: 0;
        }

        .interactions-tabs {
            display: flex;
            border-bottom: 1px solid #e8edf4;
            padding: 0 20px;
            flex-shrink: 0;
            background: #f8fafc;
        }

        .interactions-tab {
            flex: 1;
            padding: 12px 16px;
            border: none;
            background: none;
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            border-bottom: 2px solid transparent;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-family: inherit;
        }

        .interactions-tab:hover {
            color: #475569;
            background: rgba(50, 65, 140, 0.05);
        }

        .interactions-tab.active {
            color: #32418C;
            border-bottom-color: #32418C;
        }

        .interactions-tab i {
            font-size: 0.9rem;
        }

        .interactions-panels {
            flex: 1;
            overflow: hidden;
            position: relative;
        }

        .interactions-panel {
            display: none;
            height: 100%;
            overflow-y: auto;
            padding: 8px 0;
        }

        .interactions-panel.active {
            display: block;
        }

        .interactions-panel::-webkit-scrollbar {
            width: 6px;
        }

        .interactions-panel::-webkit-scrollbar-track {
            background: transparent;
        }

        .interactions-panel::-webkit-scrollbar-thumb {
            background: #d1d9e6;
            border-radius: 10px;
        }

        .interactions-panel::-webkit-scrollbar-thumb:hover {
            background: #b0c0d0;
        }

        .interactions-list {
            padding: 4px 8px;
        }

        .interactions-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px 14px;
            border-radius: 10px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .interactions-item:hover {
            background: #f1f5f9;
        }

        .interactions-item-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #32418C, #4a59a3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .interactions-item-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .interactions-item-info {
            flex: 1;
            min-width: 0;
        }

        .interactions-item-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .interactions-item-detail {
            color: #64748b;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 4px;
            margin-top: 1px;
        }

        .interactions-item-detail i {
            font-size: 0.75rem;
            color: #94a3b8;
        }

        .interactions-item-time {
            color: #94a3b8;
            font-size: 0.75rem;
            flex-shrink: 0;
        }

        .interactions-empty {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }

        .interactions-empty i {
            font-size: 2.5rem;
            display: block;
            margin-bottom: 12px;
            color: #cbd5e1;
        }

        .interactions-empty p {
            margin: 0;
            font-size: 0.95rem;
        }

        .interactions-loading {
            text-align: center;
            padding: 30px 20px;
            color: #94a3b8;
        }

        .interactions-loading i {
            margin-right: 8px;
        }

        @media (max-width: 640px) {
            .interactions-modal-content {
                width: 95%;
                max-height: 90vh;
                border-radius: 12px;
            }

            .interactions-modal-header {
                padding: 16px 18px;
            }

            .interactions-modal-title {
                font-size: 1rem;
            }

            .interactions-tabs {
                padding: 0 12px;
            }

            .interactions-tab {
                padding: 10px 12px;
                font-size: 0.8rem;
            }

            .interactions-item {
                padding: 8px 12px;
            }

            .interactions-item-avatar {
                width: 34px;
                height: 34px;
                font-size: 12px;
            }

            .interactions-item-name {
                font-size: 0.85rem;
            }
        }

        .post-view-empty {
            text-align: center;
            padding: 60px 20px;
            color: #94a3b8;
        }

        .post-view-empty i {
            font-size: 4rem;
            display: block;
            margin-bottom: 16px;
            color: #cbd5e1;
        }

        .post-view-avatar {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: linear-gradient(135deg, #32418C, #4a59a3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .post-view-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* ========================================
   CONFIRM / ALERT MODALS (matches directory style)
   ======================================== */

.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(17, 24, 39, 0.6);
    z-index: 100000;
    padding: 1rem;
    backdrop-filter: blur(6px);
    overflow-y: auto;
}

.modal-overlay.active {
    display: flex !important;
    align-items: center;
    justify-content: center;
    animation: modalFadeIn 0.3s ease-out;
}

@keyframes modalFadeIn {
    from { opacity: 0; backdrop-filter: blur(0px); }
    to   { opacity: 1; backdrop-filter: blur(6px); }
}

.modal-content-wrapper {
    width: 100%;
    max-width: 450px;
    animation: modalSlideIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-20px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Confirm modal card (centered icon + text + buttons) */
.confirm-modal-card {
    background: #ffffff;
    border-radius: 1.25rem;
    padding: 2.5rem 2rem;
    text-align: center;
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    display: flex;
    flex-direction: column;
    align-items: center;
    border: 1px solid #E5E7EB;
}

.confirm-icon-wrapper {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: #FEE2E2;
    color: #EF4444;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    margin-bottom: 1.5rem;
    animation: confirmIconPop 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
}

@keyframes confirmIconPop {
    0%   { transform: scale(0.5); opacity: 0; }
    60%  { transform: scale(1.1); }
    100% { transform: scale(1); opacity: 1; }
}

.confirm-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #32418C;
    margin: 0 0 0.75rem 0;
}

.confirm-message {
    font-size: 1rem;
    color: #4B5563;
    margin: 0 0 2rem 0;
    line-height: 1.6;
    white-space: pre-line;
}

.confirm-message strong {
    color: #1F2937;
}

.confirm-message small {
    color: #6B7280;
    font-size: 0.875rem;
    display: inline-block;
    margin-top: 0.375rem;
}

.confirm-actions {
    display: flex;
    gap: 1rem;
    width: 100%;
    justify-content: center;
}

.confirm-actions .btn {
    min-width: 120px;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 0.9375rem;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-family: inherit;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.confirm-actions .btn-secondary {
    background: #ffffff;
    color: #374151;
    border: 2px solid #D1D5DB;
}

.confirm-actions .btn-secondary:hover {
    background: #F3F4F6;
    border-color: #9CA3AF;
    transform: translateY(-2px);
}

.confirm-actions .btn-primary {
    background: #32418C;
    color: #ffffff;
}

.confirm-actions .btn-primary:hover {
    background: #253069;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(50, 65, 140, 0.3);
}

.confirm-actions .btn-success {
    background: #059669;
    color: #ffffff;
}

.confirm-actions .btn-success:hover {
    background: #047857;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(5, 150, 105, 0.3);
}

.confirm-actions .btn-warning {
    background: #F59E0B;
    color: #ffffff;
}

.confirm-actions .btn-warning:hover {
    background: #D97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(245, 158, 11, 0.3);
}

.confirm-actions .btn-danger {
    background: #EF4444;
    color: #ffffff;
}

.confirm-actions .btn-danger:hover {
    background: #DC2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 14px rgba(239, 68, 68, 0.3);
}

@media (max-width: 640px) {
    .confirm-modal-card {
        padding: 1.75rem 1.25rem;
    }
    .confirm-actions {
        flex-direction: column;
        gap: 0.75rem;
    }
    .confirm-actions .btn {
        width: 100%;
    }
}

/* ========================================
   RESTRICT REASON MODAL
   ======================================== */

#restrictReasonModal .modal-content-wrapper {
    max-width: 550px;
}

#restrictReasonModal .modal-card {
    background: #ffffff;
    border-radius: 1.25rem;
    box-shadow: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
    overflow: hidden;
    border: 1px solid #E5E7EB;
}

#restrictReasonModal .modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 1.75rem 2rem;
    border-bottom: 2px solid #F3F4F6;
    background: linear-gradient(135deg, #e8ebf7 0%, #ffffff 50%, #F9FAFB 100%);
    gap: 1rem;
}

#restrictReasonModal .modal-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #32418C;
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin: 0 0 0.375rem 0;
}

#restrictReasonModal .modal-title i {
    color: #FBD117;
    font-size: 1.25rem;
}

#restrictReasonModal .modal-subtitle {
    font-size: 0.875rem;
    color: #6B7280;
    margin: 0;
    line-height: 1.4;
}

#restrictReasonModal .modal-close {
    width: 38px;
    height: 38px;
    border: 2px solid #E5E7EB;
    background: #ffffff;
    border-radius: 0.75rem;
    color: #6B7280;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.125rem;
    transition: all 0.2s ease;
    flex-shrink: 0;
}

#restrictReasonModal .modal-close:hover {
    background: #EF4444;
    color: #ffffff;
    border-color: #EF4444;
    transform: rotate(90deg);
}

#restrictReasonModal .modal-body {
    padding: 2rem;
    background: #ffffff;
}

#restrictReasonModal .form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin-bottom: 1.25rem;
}

#restrictReasonModal .form-group label {
    font-size: 0.875rem;
    font-weight: 600;
    color: #374151;
}

#restrictReasonModal .form-control {
    width: 100%;
    padding: 0.75rem 1rem;
    font-size: 0.9375rem;
    font-family: 'Poppins', sans-serif;
    border: 2px solid #E5E7EB;
    border-radius: 0.75rem;
    background: #F9FAFB;
    color: #1F2937;
    transition: all 0.2s ease;
}

#restrictReasonModal .form-control:focus {
    outline: none;
    border-color: #32418C;
    background: #ffffff;
    box-shadow: 0 0 0 4px #e8ebf7;
}

#restrictReasonModal .form-control:hover {
    border-color: #D1D5DB;
}

#restrictReasonModal textarea.form-control {
    resize: vertical;
    min-height: 80px;
}

#restrictReasonModal .form-note {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    background: #DBEAFE;
    border-left: 4px solid #3B82F6;
    border-radius: 0 0.5rem 0.5rem 0;
    font-size: 0.875rem;
    color: #374151;
    margin: 1.5rem 0 0;
    line-height: 1.5;
}

#restrictReasonModal .form-note i {
    color: #3B82F6;
    font-size: 1.125rem;
    margin-top: 2px;
}

#restrictReasonModal .modal-actions {
    display: flex;
    justify-content: flex-end;
    gap: 0.875rem;
    margin-top: 1.5rem;
    padding-top: 1.5rem;
    border-top: 2px solid #F3F4F6;
}

#restrictReasonModal .modal-actions .btn {
    min-width: 120px;
    justify-content: center;
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    font-size: 0.9375rem;
    border-radius: 0.75rem;
    border: none;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    font-family: inherit;
    transition: all 0.2s ease;
    white-space: nowrap;
}

#restrictReasonModal .modal-actions .btn-secondary {
    background: #ffffff;
    color: #374151;
    border: 2px solid #D1D5DB;
}

#restrictReasonModal .modal-actions .btn-secondary:hover {
    background: #F3F4F6;
    border-color: #9CA3AF;
}

#restrictReasonModal .modal-actions .btn-warning {
    background: #F59E0B;
    color: #ffffff;
}

#restrictReasonModal .modal-actions .btn-warning:hover {
    background: #D97706;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(245, 158, 11, 0.4);
}

@media (max-width: 640px) {
    #restrictReasonModal .modal-content-wrapper {
        max-width: 98%;
    }
    #restrictReasonModal .modal-actions {
        flex-direction: column-reverse;
    }
    #restrictReasonModal .modal-actions .btn {
        width: 100%;
    }
}

    </style>
</head>
<body>
    @php
        // Helper function to convert UTC to PST (Philippine Standard Time)
        function convertToPST($dateTime) {
            if (!$dateTime) return 'N/A';
            $utc = new DateTime($dateTime, new DateTimeZone('UTC'));
            $utc->setTimezone(new DateTimeZone('Asia/Manila'));
            return $utc->format('F d, Y \a\t h:i A');
        }
        
        // Helper for short date
        function convertToPSTShort($dateTime) {
            if (!$dateTime) return 'N/A';
            $utc = new DateTime($dateTime, new DateTimeZone('UTC'));
            $utc->setTimezone(new DateTimeZone('Asia/Manila'));
            return $utc->format('M d, Y \a\t h:i A');
        }
        
        // Helper for time ago in PST
        function timeAgoPST($dateTime) {
            if (!$dateTime) return 'N/A';
            $utc = new DateTime($dateTime, new DateTimeZone('UTC'));
            $utc->setTimezone(new DateTimeZone('Asia/Manila'));
            $now = new DateTime('now', new DateTimeZone('Asia/Manila'));
            $diff = $now->diff($utc);
            
            if ($diff->y > 0) return $diff->y . ' year' . ($diff->y > 1 ? 's' : '') . ' ago';
            if ($diff->m > 0) return $diff->m . ' month' . ($diff->m > 1 ? 's' : '') . ' ago';
            if ($diff->d > 0) return $diff->d . ' day' . ($diff->d > 1 ? 's' : '') . ' ago';
            if ($diff->h > 0) return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ago';
            if ($diff->i > 0) return $diff->i . ' minute' . ($diff->i > 1 ? 's' : '') . ' ago';
            return 'Just now';
        }
    @endphp

    @include('partials.admin-navbar')

    <!-- Mobile Menu Overlay -->
    <div class="mobile-overlay" id="mobileOverlay" onclick="toggleMobileMenu()"></div>

    <div class="admin-layout">
        <aside class="admin-sidebar" id="adminSidebar">
            <div class="sidebar-header">
                <div class="logo-container">
                    <img src="/assets/logos/LumiNUs_Logo_Landscape_Blue.png" alt="LumiNUs Logo" class="logo-luminus">
                </div>
                <button class="sidebar-close" id="sidebarClose" onclick="toggleMobileMenu()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="/admin/dashboard" class="nav-item"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
                <a href="{{ route('admin.directory') }}" class="nav-item"><i class="fa-solid fa-users"></i><span>Alumni Directory</span></a>
                <a href="{{ route('announcements.index') }}" class="nav-item"><i class="fa-solid fa-bullhorn"></i><span>Announcements</span></a>
                <a href="{{ route('events.index') }}" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>Events</span></a>
                <a href="{{ route('perks.index') }}" class="nav-item"><i class="fa-solid fa-gift"></i><span>Perks & Discounts</span></a>
                <a href="/admin/alumni_tracer" class="nav-item"><i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span></a>
                <a href="/admin/messages" class="nav-item"><i class="fa-solid fa-envelope"></i><span>Messages</span></a>
                <a href="{{ route('admin.settings') }}" class="nav-item"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
            </nav>
        </aside>

        <main class="admin-main">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title">
                            <i class="fa-solid fa-file-lines"></i>
                            View Post
                        </h1>
                        <p class="page-subtitle">
                            @if(isset($post) && $post->alumni)
                                Post by {{ $post->alumni->first_name }} {{ $post->alumni->last_name }}
                            @else
                                Post details
                            @endif
                        </p>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                            <i class="fa-solid fa-arrow-left"></i>
                            <span>Back to Dashboard</span>
                        </a>
                    </div>
                </div>
            </header>

            <div class="post-view-container">
                @if(isset($post))
                    <div class="post-view-card">
                        <!-- Post Header -->
                        <div class="post-view-header">
                            @php
                                $author = $post->alumni;
                                $authorPhotoPath = trim((string) ($author->alumni_photo ?? $author->card_photo ?? ''));
                                $authorHasPhoto = !empty($authorPhotoPath);
                                $authorPhotoUrl = null;
                                
                                if ($authorHasPhoto) {
                                    if (preg_match('/^https?:\/\//i', $authorPhotoPath)) {
                                        $authorPhotoUrl = $authorPhotoPath;
                                    } elseif (str_starts_with($authorPhotoPath, '/storage/')) {
                                        $authorPhotoUrl = $authorPhotoPath;
                                    } elseif (str_starts_with($authorPhotoPath, 'storage/')) {
                                        $authorPhotoUrl = '/' . $authorPhotoPath;
                                    } elseif (str_starts_with($authorPhotoPath, '/')) {
                                        $authorPhotoUrl = $authorPhotoPath;
                                    } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                                        $authorPhotoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($authorPhotoPath, '/');
                                    } else {
                                        $authorPhotoUrl = asset('storage/' . ltrim($authorPhotoPath, '/'));
                                    }
                                }
                                
                                $authorInitials = $author ? strtoupper(substr($author->first_name ?? 'U', 0, 1) . substr($author->last_name ?? '', 0, 1)) : 'U';
                            @endphp

                            <div class="post-view-avatar" style="{{ $authorHasPhoto ? 'padding: 0; overflow: hidden; background: none;' : '' }}">
                                @if($authorHasPhoto && $authorPhotoUrl)
                                    <img src="{{ $authorPhotoUrl }}" alt="{{ $author->first_name ?? 'User' }}" onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #32418C, #4a59a3)'; this.parentElement.innerHTML='{{ $authorInitials }}';">
                                @else
                                    {{ $authorInitials }}
                                @endif
                            </div>

                            <div class="post-view-author">
                                <div class="post-view-author-name">
                                    @if($author)
                                        {{ $author->first_name }} {{ $author->last_name }}
                                        @if($author->verification_status === 'verified')
                                            <i class="fa-solid fa-check-circle" title="Verified Alumni"></i>
                                        @endif
                                    @else
                                        Unknown User
                                    @endif
                                </div>
                                <div class="post-view-author-meta">
                                    <span>
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ $post->created_at ? convertToPST($post->created_at) : 'N/A' }}
                                    </span>
                                    <span>
                                        <i class="fa-regular fa-clock"></i>
                                        <span class="pst-time">{{ $post->created_at ? timeAgoPST($post->created_at) : 'N/A' }}</span>
                                    </span>
                                    @if($author)
                                        <span>
                                            <i class="fa-solid fa-envelope"></i>
                                            {{ $author->email ?? 'No email' }}
                                        </span>
                                        <span>
                                            <i class="fa-solid fa-graduation-cap"></i>
                                            {{ $author->program ?? 'N/A' }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="post-view-badges">
                                <span class="post-view-badge visibility-{{ $post->visibility ?? 'public' }}">
                                    <i class="fa-solid fa-{{ ($post->visibility ?? 'public') === 'public' ? 'globe' : 'lock' }}"></i>
                                    {{ ucfirst($post->visibility ?? 'Public') }}
                                </span>
                                <span class="post-view-badge moderation-{{ $post->moderation_status ?? 'pending' }}">
                                    <i class="fa-solid fa-{{ ($post->moderation_status ?? 'pending') === 'approved' ? 'check' : 'clock' }}"></i>
                                    {{ ucfirst($post->moderation_status ?? 'Pending') }}
                                </span>
                                @if($post->is_hidden ?? false)
                                    <span class="post-view-badge moderation-hidden">
                                        <i class="fa-solid fa-eye-slash"></i>
                                        Hidden
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Post Caption -->
                        @if($post->caption)
                            <div class="post-view-caption">{{ $post->caption }}</div>
                        @endif

                        <!-- Post Images -->
                        @if($post->images && $post->images->isNotEmpty())
                            @php
                                $imageCount = $post->images->count();
                                $gridClass = 'grid-' . min($imageCount, 6);
                            @endphp
                            <div class="post-view-images {{ $gridClass }}">
                                @foreach($post->images as $image)
                                    @php
                                        $imagePath = ltrim($image->image_path, '/');
                                        $supabaseUrl = config('filesystems.disks.s3.url', '');
                                        if (empty($supabaseUrl)) {
                                            $supabaseUrl = rtrim(config('services.supabase.url', ''), '/') . '/storage/v1/object/public/luminus_assets/';
                                        } else {
                                            $supabaseUrl = rtrim($supabaseUrl, '/') . '/';
                                        }
                                        $imageUrl = $supabaseUrl . $imagePath;
                                    @endphp
                                    <img 
                                        src="{{ $imageUrl }}" 
                                        alt="Post image" 
                                        loading="lazy"
                                        onerror="this.style.display='none'"
                                        onclick="window.open(this.src, '_blank')"
                                    >
                                @endforeach
                            </div>
                        @endif

                        <!-- Post Stats - Only Heart (Like), Comments, Reposts -->
                        <div class="post-view-stats">
                            <!-- ❤️ Likes - Click to open modal -->
                            <div class="post-view-stat" onclick="openInteractionsModal({{ $post->id }}, 'likes')" title="View who liked this post">
                                <i class="fa-regular fa-heart" style="color: #ef4444;"></i>
                                <span class="count">{{ $post->reactions->count() }}</span>
                                <span>Like{{ $post->reactions->count() != 1 ? 's' : '' }}</span>
                            </div>
                            
                            <!-- 💬 Comments - Click to open modal -->
                            <div class="post-view-stat" onclick="openInteractionsModal({{ $post->id }}, 'comments')" title="View comments">
                                <i class="fa-regular fa-comment" style="color: #3b82f6;"></i>
                                <span class="count">{{ $post->comments->count() }}</span>
                                <span>Comment{{ $post->comments->count() != 1 ? 's' : '' }}</span>
                            </div>
                            
                            <!-- 🔄 Reposts - Click to open modal -->
                            <div class="post-view-stat" onclick="openInteractionsModal({{ $post->id }}, 'reposts')" title="View reposts">
                                <i class="fa-solid fa-retweet" style="color: #10b981;"></i>
                                <span class="count">{{ $post->reposts->count() }}</span>
                                <span>Repost{{ $post->reposts->count() != 1 ? 's' : '' }}</span>
                            </div>
                        </div>

                        <!-- Comments Section -->
                        @if($post->comments && $post->comments->isNotEmpty())
                            <div class="post-view-comments">
                                <div class="post-view-comments-header">
                                    <i class="fa-regular fa-comment-dots"></i>
                                    Comments ({{ $post->comments->count() }})
                                </div>

                                @foreach($post->comments->take(5) as $comment)
                                    @php
                                        $commenter = $comment->alumni;
                                        $commenterPhotoPath = trim((string) ($commenter->alumni_photo ?? $commenter->card_photo ?? ''));
                                        $commenterHasPhoto = !empty($commenterPhotoPath);
                                        $commenterPhotoUrl = null;
                                        
                                        if ($commenterHasPhoto) {
                                            if (preg_match('/^https?:\/\//i', $commenterPhotoPath)) {
                                                $commenterPhotoUrl = $commenterPhotoPath;
                                            } elseif (str_starts_with($commenterPhotoPath, '/storage/')) {
                                                $commenterPhotoUrl = $commenterPhotoPath;
                                            } elseif (str_starts_with($commenterPhotoPath, 'storage/')) {
                                                $commenterPhotoUrl = '/' . $commenterPhotoPath;
                                            } elseif (str_starts_with($commenterPhotoPath, '/')) {
                                                $commenterPhotoUrl = $commenterPhotoPath;
                                            } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                                                $commenterPhotoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($commenterPhotoPath, '/');
                                            } else {
                                                $commenterPhotoUrl = asset('storage/' . ltrim($commenterPhotoPath, '/'));
                                            }
                                        }
                                        
                                        $commenterInitials = $commenter ? strtoupper(substr($commenter->first_name ?? 'U', 0, 1) . substr($commenter->last_name ?? '', 0, 1)) : 'U';
                                    @endphp
                                    <div class="post-view-comment">
                                        <div class="post-view-comment-avatar" style="{{ $commenterHasPhoto ? 'padding: 0; overflow: hidden; background: none;' : '' }}">
                                            @if($commenterHasPhoto && $commenterPhotoUrl)
                                                <img src="{{ $commenterPhotoUrl }}" alt="{{ $commenter->first_name ?? 'User' }}" onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #6366f1, #8b5cf6)'; this.parentElement.innerHTML='{{ $commenterInitials }}';">
                                            @else
                                                {{ $commenterInitials }}
                                            @endif
                                        </div>
                                        <div class="post-view-comment-body">
                                            <div class="post-view-comment-author">
                                                {{ $commenter->first_name ?? 'Unknown' }} {{ $commenter->last_name ?? '' }}
                                            </div>
                                            <div class="post-view-comment-text">{{ $comment->comment }}</div>
                                            <div class="post-view-comment-time">
                                                <i class="fa-regular fa-clock"></i>
                                                {{ $comment->created_at ? convertToPSTShort($comment->created_at) : 'N/A' }}
                                                <span style="opacity: 0.6; margin-left: 4px;">({{ $comment->created_at ? timeAgoPST($comment->created_at) : 'N/A' }})</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach

                                @if($post->comments->count() > 5)
                                    <button class="post-view-more-comments" onclick="openInteractionsModal({{ $post->id }}, 'comments')">
                                        <i class="fa-regular fa-comment-dots"></i>
                                        View all {{ $post->comments->count() }} comments
                                    </button>
                                @endif
                            </div>
                        @endif

                        <!-- Report Details -->
                        @if(isset($post->report_count) && $post->report_count > 0)
                            <div class="post-view-report-details">
                                <h4>
                                    <i class="fa-solid fa-flag"></i>
                                    Report Details
                                    <span style="background: #ef4444; color: white; padding: 2px 10px; border-radius: 12px; font-size: 0.7rem; margin-left: 8px;">
                                        {{ $post->report_count }} report{{ $post->report_count > 1 ? 's' : '' }}
                                    </span>
                                </h4>
                                @if(isset($post->report_reasons) && $post->report_reasons)
                                    <p><strong>Reasons:</strong> {{ $post->report_reasons }}</p>
                                @endif
                                @if(isset($post->reported_at))
                                    <p class="report-meta">
                                        <i class="fa-regular fa-clock"></i>
                                        Reported on {{ $post->reported_at ? convertToPST($post->reported_at) : 'N/A' }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- Moderation Actions -->
                        <div class="post-view-actions">
                            <button class="btn btn-approve" onclick="moderatePost({{ $post->id }}, 'approve')">
                                <i class="fa-solid fa-check-circle"></i> Approve
                            </button>
                            <button class="btn btn-hide" onclick="moderatePost({{ $post->id }}, 'hide')">
                                <i class="fa-solid fa-eye-slash"></i> Hide
                            </button>
                            <button class="btn btn-delete-post" onclick="moderatePost({{ $post->id }}, 'delete')">
                                <i class="fa-solid fa-trash-can"></i> Delete
                            </button>
                            @if(isset($post->alumni_id))
                                <button class="btn btn-restrict-user" onclick="restrictUser({{ $post->alumni_id }})">
                                    <i class="fa-solid fa-user-slash"></i> Restrict User
                                </button>
                            @endif
                            <a href="{{ route('admin.dashboard') }}" class="btn btn-back-dashboard">
                                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>
                @else
                    <div class="post-view-empty">
                        <i class="fa-regular fa-circle-xmark"></i>
                        <h3>Post not found</h3>
                        <p>The post you're looking for doesn't exist or has been deleted.</p>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary" style="margin-top: 20px;">
                            <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </div>
                @endif
            </div>

            <!-- Restrict Reason Modal -->
            <div id="restrictReasonModal" class="modal-overlay" aria-hidden="true">
                <div class="modal-content-wrapper" style="max-width: 550px;">
                    <div class="modal-card">
                        <div class="modal-header">
                            <div>
                                <h2 class="modal-title">
                                    <i class="fa-solid fa-user-slash"></i>
                                    <span id="restrictModalTitle">Restrict Account</span>
                                </h2>
                                <p class="modal-subtitle">Provide a reason for restricting <strong id="restrictModalName"></strong>'s account</p>
                            </div>
                            <button type="button" class="modal-close" onclick="hideRestrictReasonModal()" title="Close">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <form id="restrictForm" onsubmit="executeRestrict(event)">
                                <input type="hidden" id="restrictAlumniId">
                                
                                <div class="form-group">
                                    <label for="restrictionReason">Reason for Restriction <span style="color: #EF4444;">*</span></label>
                                    <select id="restrictionReason" class="form-control" required>
                                        <option value="">Select a reason...</option>
                                        <option value="spam_fraud">Spam or Fraud</option>
                                        <option value="nudity_sexual">Nudity or Sexual Content</option>
                                        <option value="hate_speech">Hate Speech or Symbols</option>
                                        <option value="violence">Violence or Dangerous Organizations</option>
                                        <option value="bullying">Bullying or Harassment</option>
                                        <option value="illegal_goods">Sale of Illegal or Regulated Goods</option>
                                        <option value="ip_violation">Intellectual Property Violation</option>
                                        <option value="other">Other (Please Specify)</option>
                                    </select>
                                </div>

                                <div class="form-group" id="customReasonGroup" style="display: none;">
                                    <label for="customReason">Please specify:</label>
                                    <input type="text" id="customReason" class="form-control" placeholder="Enter custom reason...">
                                </div>

                                <div class="form-group">
                                    <label for="restrictionComment">Additional Comments <span style="font-weight: 400; color: #6B7280;">(Optional)</span></label>
                                    <textarea id="restrictionComment" class="form-control" rows="3" 
                                            placeholder="Add any additional notes about this restriction..." 
                                            style="resize: vertical; min-height: 80px;"></textarea>
                                </div>

                                <div class="form-note">
                                    <i class="fa-solid fa-circle-info"></i>
                                    <div>
                                        <strong>Note:</strong> The reason and comments will be emailed to the alumni and shown when they try to log in.
                                    </div>
                                </div>

                                <div class="modal-actions">
                                    <button type="button" class="btn btn-secondary" onclick="hideRestrictReasonModal()">Cancel</button>
                                    <button type="submit" class="btn btn-warning" id="restrictConfirmBtn">
                                        <i class="fa-solid fa-lock"></i> Restrict Account
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Post Interactions Modal -->
    <div id="interactionsModal" class="interactions-modal" style="display: none;">
        <div class="interactions-modal-overlay" onclick="closeInteractionsModal()"></div>
        <div class="interactions-modal-content">
            <div class="interactions-modal-header">
                <div class="interactions-modal-title">
                    <i class="fa-regular fa-heart"></i>
                    <span id="interactionsModalTitle">Interactions</span>
                    <span class="interactions-count" id="interactionsCount">(0)</span>
                </div>
                <button class="interactions-modal-close" onclick="closeInteractionsModal()">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="interactions-modal-body">
                <!-- Tabs -->
                <div class="interactions-tabs">
                    <button class="interactions-tab active" data-tab="likes" onclick="switchInteractionTab('likes')">
                        <i class="fa-regular fa-heart"></i> Likes
                    </button>
                    <button class="interactions-tab" data-tab="comments" onclick="switchInteractionTab('comments')">
                        <i class="fa-regular fa-comment"></i> Comments
                    </button>
                    <button class="interactions-tab" data-tab="reposts" onclick="switchInteractionTab('reposts')">
                        <i class="fa-solid fa-retweet"></i> Reposts
                    </button>
                </div>
                
                <!-- Content Panels -->
                <div class="interactions-panels">
                    <div class="interactions-panel active" id="likesPanel">
                        <div class="interactions-list" id="likesList">
                            <div class="interactions-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i> Loading likes...
                            </div>
                        </div>
                    </div>
                    <div class="interactions-panel" id="commentsPanel">
                        <div class="interactions-list" id="commentsList">
                            <div class="interactions-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i> Loading comments...
                            </div>
                        </div>
                    </div>
                    <div class="interactions-panel" id="repostsPanel">
                        <div class="interactions-list" id="repostsList">
                            <div class="interactions-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i> Loading reposts...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
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

// ========================================
// CONFIRM / ALERT MODALS (directory-style)
// ========================================

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

        // Derive icon background from iconColor with 15% alpha if not provided
        const bg = iconBg || (iconColor + '26');

        const html = `
            <div id="${modalId}" class="modal-overlay active">
                <div class="modal-content-wrapper">
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
            <div id="${modalId}" class="modal-overlay active">
                <div class="modal-content-wrapper">
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

// ========================================
// MODERATION ACTIONS
// ========================================

async function moderatePost(postId, action) {
    const config = {
        'approve': {
            verb: 'approve and dismiss all reports for',
            title: 'Approve Post',
            confirmText: 'Approve',
            confirmClass: 'btn-success',
            icon: 'fa-circle-check',
            iconColor: '#10B981',
            iconBg: '#D1FAE5',
        },
        'hide': {
            verb: 'hide from the app',
            title: 'Hide Post',
            confirmText: 'Hide',
            confirmClass: 'btn-warning',
            icon: 'fa-eye-slash',
            iconColor: '#F59E0B',
            iconBg: '#FEF3C7',
        },
        'unhide': {
            verb: 'unhide and make visible again',
            title: 'Unhide Post',
            confirmText: 'Unhide',
            confirmClass: 'btn-success',
            icon: 'fa-eye',
            iconColor: '#10B981',
            iconBg: '#D1FAE5',
        },
        'delete': {
            verb: 'permanently delete',
            title: 'Delete Post',
            confirmText: 'Delete',
            confirmClass: 'btn-danger',
            icon: 'fa-triangle-exclamation',
            iconColor: '#EF4444',
            iconBg: '#FEE2E2',
        },
    };

    const cfg = config[action] || {
        verb: action,
        title: 'Confirm Action',
        confirmText: 'Confirm',
        confirmClass: 'btn-primary',
        icon: 'fa-circle-question',
        iconColor: '#32418C',
        iconBg: '#E8EBF7',
    };

    let message = `Are you sure you want to ${cfg.verb} this post?`;
    if (action === 'delete') {
        message += '\n\nThis action cannot be undone.';
    }

    const confirmed = await showConfirmModal({
        title: cfg.title,
        message,
        confirmText: cfg.confirmText,
        cancelText: 'Cancel',
        confirmClass: cfg.confirmClass,
        icon: cfg.icon,
        iconColor: cfg.iconColor,
        iconBg: cfg.iconBg,
    });

    if (!confirmed) return;

    try {
        const response = await fetch('/admin/moderate/post', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ id: postId, action: action })
        });
        const data = await response.json();

        if (data.success) {
            window.location.href = '/admin/dashboard';
        } else {
            showErrorModal(data.message || 'Something went wrong');
        }
    } catch (err) {
        console.error(err);
        showErrorModal('An error occurred. Please try again.');
    }
}

// ========================================
// RESTRICT USER — opens the reason modal
// ========================================

function restrictUser(alumniId) {
    // Grab the author name from the page so we can show it in the modal
    const postAuthorNameEl = document.querySelector('.post-view-author-name');
    const authorName = postAuthorNameEl
        ? postAuthorNameEl.textContent.trim().replace(/\s+/g, ' ')
        : 'this user';

    // Set modal fields
    document.getElementById('restrictAlumniId').value = alumniId;
    document.getElementById('restrictModalName').textContent = authorName;

    // Reset the form
    document.getElementById('restrictionReason').value = '';
    document.getElementById('customReason').value = '';
    document.getElementById('restrictionComment').value = '';
    document.getElementById('customReasonGroup').style.display = 'none';

    // Show modal
    const modal = document.getElementById('restrictReasonModal');
    modal.classList.add('active');
    modal.removeAttribute('inert');
    document.body.style.overflow = 'hidden';
}

function hideRestrictReasonModal() {
    const modal = document.getElementById('restrictReasonModal');
    if (modal) {
        modal.classList.remove('active');
        modal.setAttribute('inert', '');
        document.body.style.overflow = '';
    }
}

// Handle the "Other" option to reveal custom reason field
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('restrictionReason')?.addEventListener('change', function() {
        const customGroup = document.getElementById('customReasonGroup');
        if (this.value === 'other') {
            customGroup.style.display = 'flex';
        } else {
            customGroup.style.display = 'none';
        }
    });

    // Close modal when clicking overlay
    document.getElementById('restrictReasonModal')?.addEventListener('click', function(e) {
        if (e.target === this) hideRestrictReasonModal();
    });

    // ESC to close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            const modal = document.getElementById('restrictReasonModal');
            if (modal && modal.classList.contains('active')) {
                hideRestrictReasonModal();
            }
        }
    });
});

// Execute the restriction (called by form submit)
async function executeRestrict(event) {
    event.preventDefault();

    const id = document.getElementById('restrictAlumniId').value;
    const reason = document.getElementById('restrictionReason').value;
    const customReason = document.getElementById('customReason').value.trim();
    const comment = document.getElementById('restrictionComment').value.trim();

    // Validation
    if (!reason) {
        showErrorModal('Please select a reason for restriction.');
        return;
    }

    if (reason === 'other' && !customReason) {
        showErrorModal('Please specify a custom reason.');
        return;
    }

    const finalReason = reason === 'other' ? customReason : reason;

    const submitBtn = document.getElementById('restrictConfirmBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';

    try {
        const response = await fetch(`/admin/alumni/${id}/toggle-restrict`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                restrict: 1,
                restriction_reason: finalReason,
                restriction_comment: comment
            })
        });

        const data = await response.json();

        hideRestrictReasonModal();

        if (data.success) {
            await showSuccessModal(
                data.message || 'User has been restricted successfully.',
                'User Restricted'
            );
            window.location.reload();
        } else {
            showErrorModal(data.message || 'Failed to restrict account.');
        }
    } catch (err) {
        console.error('Restrict error:', err);
        hideRestrictReasonModal();
        showErrorModal('An error occurred. Please try again.');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

// ========================================
// INTERACTIONS MODAL FUNCTIONS
// ========================================

let currentPostId = null;
let currentTab = 'likes';

function openInteractionsModal(postId, tab = 'likes') {
    currentPostId = postId;
    currentTab = tab;

    const modal = document.getElementById('interactionsModal');
    modal.classList.add('active');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';

    switchInteractionTab(tab);
    loadInteractions(postId, tab);
}

function closeInteractionsModal() {
    const modal = document.getElementById('interactionsModal');
    modal.classList.remove('active');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    currentPostId = null;
}

function switchInteractionTab(tab) {
    currentTab = tab;

    document.querySelectorAll('.interactions-tab').forEach(t => {
        t.classList.remove('active');
        if (t.dataset.tab === tab) {
            t.classList.add('active');
        }
    });

    document.querySelectorAll('.interactions-panel').forEach(p => {
        p.classList.remove('active');
    });
    document.getElementById(tab + 'Panel').classList.add('active');

    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';

    if (currentPostId) {
        loadInteractions(currentPostId, tab);
    }
}

function loadInteractions(postId, type) {
    const listId = type + 'List';
    const list = document.getElementById(listId);

    list.innerHTML = `
        <div class="interactions-loading">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading ${type}...
        </div>
    `;

    fetch(`/admin/posts/${postId}/interactions?type=${type}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        if (data.success) {
            renderInteractions(listId, data.data, type);
            document.getElementById('interactionsCount').textContent = `(${data.total || 0})`;
        } else {
            list.innerHTML = `
                <div class="interactions-empty">
                    <i class="fa-regular fa-circle-xmark"></i>
                    <p>${data.message || 'Failed to load interactions.'}</p>
                </div>
            `;
        }
    })
    .catch(error => {
        console.error('Error loading interactions:', error);
        list.innerHTML = `
            <div class="interactions-empty">
                <i class="fa-regular fa-circle-xmark"></i>
                <p>Error loading ${type}. Please try again.</p>
            </div>
        `;
    });
}

function renderInteractions(listId, items, type) {
    const list = document.getElementById(listId);

    if (!items || items.length === 0) {
        const iconMap = {
            'likes': 'fa-regular fa-heart',
            'comments': 'fa-regular fa-comment',
            'reposts': 'fa-solid fa-retweet'
        };
        list.innerHTML = `
            <div class="interactions-empty">
                <i class="${iconMap[type] || 'fa-regular fa-circle'}" style="font-size: 2.5rem;"></i>
                <p>No ${type} yet</p>
            </div>
        `;
        return;
    }

    let html = '';
    items.forEach(item => {
        const initials = (item.first_name?.[0] || '?') + (item.last_name?.[0] || '');
        const fullName = `${item.first_name || 'Unknown'} ${item.last_name || ''}`.trim();
        const timeAgo = item.created_at ? timeAgoHelper(item.created_at) : '';

        let avatarHtml = '';
        if (item.profile_photo) {
            avatarHtml = `<img src="${item.profile_photo}" alt="${fullName}" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">`;
        } else {
            avatarHtml = initials.toUpperCase();
        }

        let detailHtml = '';
        if (type === 'comments' && item.comment) {
            detailHtml = `<div class="interactions-item-detail"><i class="fa-regular fa-comment"></i> ${escapeHtml(item.comment)}</div>`;
        } else if (type === 'reposts' && item.caption) {
            detailHtml = `<div class="interactions-item-detail"><i class="fa-regular fa-retweet"></i> ${escapeHtml(item.caption)}</div>`;
        }

        html += `
            <div class="interactions-item">
                <div class="interactions-item-avatar" style="${item.profile_photo ? 'padding: 0; overflow: hidden; background: none;' : ''}">
                    ${avatarHtml}
                </div>
                <div class="interactions-item-info">
                    <div class="interactions-item-name">${escapeHtml(fullName)}</div>
                    ${detailHtml}
                </div>
                ${timeAgo ? `<div class="interactions-item-time">${timeAgo}</div>` : ''}
            </div>
        `;
    });

    list.innerHTML = html;
}

function timeAgoHelper(dateString) {
    const now = new Date();
    const past = new Date(dateString);
    const diffMs = now - past;
    const diffMins = Math.floor(diffMs / 60000);
    const diffHours = Math.floor(diffMs / 3600000);
    const diffDays = Math.floor(diffMs / 86400000);

    if (diffMins < 1) return 'Just now';
    if (diffMins < 60) return diffMins + 'm ago';
    if (diffHours < 24) return diffHours + 'h ago';
    if (diffDays < 7) return diffDays + 'd ago';
    return past.toLocaleDateString();
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Close modal on ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('interactionsModal').style.display === 'flex') {
        closeInteractionsModal();
    }
});

// Close modal on overlay click
document.querySelector('.interactions-modal-overlay')?.addEventListener('click', closeInteractionsModal);

    </script>

</body>
</html>

{{-- This is view-post.blade.php --}}