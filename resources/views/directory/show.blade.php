<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Profile | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/admin-fixed-navbar.css">
    <link rel="stylesheet" href="/css/directory_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <style>
        /* Profile specific styles */
        .profile-container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .profile-card { background: #fff; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); padding: 30px; margin-bottom: 20px; }
        .profile-header { display: flex; align-items: center; gap: 20px; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
        .profile-photo { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 4px solid #f0f0f0; }
        .profile-info h2 { margin: 0; font-size: 1.8rem; color: #1e293b; }
        .profile-info p { margin: 5px 0 0; color: #64748b; font-size: 1.1rem; }
        .profile-details { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px; }
        .detail-item { background: #f8fafc; padding: 15px; border-radius: 8px; }
        .detail-item label { display: block; font-size: 0.85rem; color: #64748b; margin-bottom: 5px; font-weight: 500; }
        .detail-item span { font-size: 1rem; color: #1e293b; font-weight: 500; }
        
        /* Section Titles */
        .section-title { margin-top: 0; margin-bottom: 20px; color: #1e293b; font-size: 1.3rem; display: flex; align-items: center; gap: 10px; }
        .section-title i { color: #3b82f6; }
        
        /* Address Cards */
        .address-card { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid #3b82f6; }
        .address-card:last-child { margin-bottom: 0; }
        .address-card .address-type { font-weight: 600; color: #1e293b; margin-bottom: 8px; font-size: 1.05rem; }
        .address-card .address-type i { color: #3b82f6; margin-right: 8px; }
        .address-card .address-text { color: #475569; line-height: 1.6; }
        .address-card .coordinates { font-size: 0.9rem; color: #64748b; margin-top: 8px; padding-top: 8px; border-top: 1px dashed #e2e8f0; }
        .address-card .coordinates i { color: #3b82f6; margin-right: 6px; }
        
        /* Map Container */
        .map-container { margin-top: 15px; border-radius: 8px; overflow: hidden; border: 1px solid #e2e8f0; }
        #alumniMap { height: 300px; width: 100%; }
        
        .no-address { text-align: center; padding: 40px 20px; color: #94a3b8; }
        .no-address i { font-size: 3rem; display: block; margin-bottom: 15px; color: #cbd5e1; }
        .no-address p { font-size: 1.1rem; margin: 0; }
        
        /* Verification Badge */
        .verification-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .verification-badge.verified { background: #dcfce7; color: #166534; }
        .verification-badge.pending { background: #fef9c3; color: #854d0e; }
        .verification-badge.rejected { background: #fee2e2; color: #991b1b; }
        
        /* Status Badge */
        .status-badge { display: inline-block; padding: 4px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 500; }
        .status-badge.active { background: #dcfce7; color: #21ac56; }
        .status-badge.inactive { background: #fee2e2; color: #991b1b; }
        
        /* Professional Items */
        .professional-item { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 10px; }
        .professional-item:last-child { margin-bottom: 0; }
        .professional-item .item-title { font-weight: 600; color: #1e293b; }
        .professional-item .item-subtitle { color: #64748b; font-size: 0.95rem; }
        .professional-item .item-dates { color: #94a3b8; font-size: 0.85rem; }
        
        /* Skill Tags */
        .skill-tag { display: inline-block; background: #e0e7ff; color: #3730a3; padding: 5px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 500; margin: 4px; }
        
        /* Follower Stats */
        .follower-stat { text-align: center; padding: 20px; background: #f8fafc; border-radius: 8px; }
        .follower-stat .number { font-size: 2rem; font-weight: 700; color: #1e293b; display: block; }
        .follower-stat .label { color: #64748b; font-size: 0.9rem; }
        
        /* Event Cards */
        .event-item { background: #f8fafc; padding: 15px; border-radius: 8px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        .event-item:last-child { margin-bottom: 0; }
        .event-item .event-info { flex: 1; }
        .event-item .event-title { font-weight: 600; color: #1e293b; }
        .event-item .event-details { color: #64748b; font-size: 0.9rem; }
        .event-item .event-status { padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500; background: #dcfce7; color: #166534; }
        
        /* Post Cards */
        .post-item { background: #f8fafc; padding: 20px; border-radius: 8px; margin-bottom: 15px; }
        .post-item:last-child { margin-bottom: 0; }
        .post-item .post-caption { color: #1e293b; margin-bottom: 10px; }
        .post-item .post-meta { color: #94a3b8; font-size: 0.85rem; display: flex; gap: 15px; flex-wrap: wrap; }
        .post-item .post-meta i { margin-right: 4px; }
        .post-item .post-images { display: grid; grid-template-columns: repeat(auto-fill, minmax(150px, 1fr)); gap: 10px; margin-top: 10px; }
        .post-item .post-images img { width: 100%; height: 150px; object-fit: cover; border-radius: 6px; }
        .post-item .post-comments { margin-top: 10px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
        .post-item .post-comment { font-size: 0.9rem; color: #475569; margin-bottom: 5px; }
        .post-item .post-comment strong { color: #1e293b; }
        
        .no-data { text-align: center; padding: 30px 20px; color: #94a3b8; }
        .no-data i { font-size: 2rem; display: block; margin-bottom: 10px; color: #cbd5e1; }
        
        /* Mailer Test Section */
        .mailer-test-section { background: #eff6ff; border: 1px dashed #3b82f6; border-radius: 12px; padding: 25px; text-align: center; margin-top: 20px; }
        .mailer-test-section h3 { color: #1e40af; margin-top: 0; }
        .btn-send-email { background: #2563eb; color: white; padding: 12px 24px; border: none; border-radius: 8px; font-size: 1rem; font-weight: 600; cursor: pointer; transition: background 0.2s; display: inline-flex; align-items: center; gap: 8px; }
        .btn-send-email:hover { background: #1d4ed8; }
        .btn-send-email:disabled { background: #93c5fd; cursor: not-allowed; }
        
        .btn-back { background: #e2e8f0; color: #475569; padding: 10px 20px; border: none; border-radius: 8px; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; margin-bottom: 20px; }
        .btn-back:hover { background: #cbd5e1; }
        
        /* Responsive */
        @media (max-width: 640px) {
            .profile-header { flex-direction: column; text-align: center; }
            .profile-details { grid-template-columns: 1fr; }
            #alumniMap { height: 200px; }
            .event-item { flex-direction: column; align-items: flex-start; gap: 10px; }
            .post-item .post-images { grid-template-columns: 1fr; }
            .follower-stats { grid-template-columns: 1fr 1fr; }
        }
        
        /* Grid layouts */
        .two-col-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .three-col-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
        
        @media (max-width: 768px) {
            .two-col-grid { grid-template-columns: 1fr; }
            .three-col-grid { grid-template-columns: 1fr; }
        }

        /* ========================================
        ENHANCED POST SECTION STYLES
        ======================================== */

        /* Post Card Container */
        .post-item {
            background: #ffffff;
            padding: 24px;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid #e8edf4;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .post-item:last-child {
            margin-bottom: 0;
        }

        .post-item:hover {
            border-color: #c5d0e0;
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        /* Post Header with Alumni Info */
        .post-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            padding-bottom: 14px;
            border-bottom: 1px solid #f0f2f5;
        }

        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #32418C, #4a59a3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        .post-author-info {
            flex: 1;
        }

        .post-author-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.95rem;
        }

        .post-author-name i {
            color: #3b82f6;
            font-size: 0.75rem;
            margin-left: 4px;
        }

        .post-timestamp {
            color: #94a3b8;
            font-size: 0.8rem;
        }

        .post-timestamp i {
            margin-right: 4px;
        }

        /* Post Caption */
        .post-caption {
            color: #1e293b;
            margin-bottom: 16px;
            font-size: 0.95rem;
            line-height: 1.7;
            padding: 0 4px;
        }

        /* Image Grid - Enhanced */
        .post-images {
            display: grid;
            gap: 8px;
            margin-top: 12px;
            margin-bottom: 16px;
        }

        /* Dynamic grid based on image count */
        .post-images.grid-1 {
            grid-template-columns: 1fr;
        }

        .post-images.grid-2 {
            grid-template-columns: 1fr 1fr;
        }

        .post-images.grid-3 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .post-images.grid-4 {
            grid-template-columns: 1fr 1fr;
        }

        .post-images.grid-5 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .post-images.grid-6 {
            grid-template-columns: 1fr 1fr 1fr;
        }

        .post-images img {
            width: 100%;
            height: 220px;
            object-fit: cover;
            border-radius: 8px;
            border: 1px solid #e8edf4;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .post-images img:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            border-color: #32418C;
        }

        /* For single images */
        .post-images.grid-1 img {
            height: 380px;
        }

        /* For 4 images - first image larger */
        .post-images.grid-4 img:first-child {
            grid-column: 1 / -1;
            height: 280px;
        }

        /* For 5 images */
        .post-images.grid-5 img:first-child {
            grid-column: 1 / -1;
            height: 260px;
        }

        /* For 6 images - top row 3, bottom row 3 */
        .post-images.grid-6 img {
            height: 180px;
        }

        /* Post Meta Actions */
        .post-actions {
            display: flex;
            align-items: center;
            gap: 20px;
            padding: 12px 0 8px 0;
            border-top: 1px solid #f0f2f5;
            margin-top: 4px;
        }

        .post-action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            background: none;
            border: none;
            color: #64748b;
            font-size: 0.85rem;
            font-weight: 500;
            cursor: pointer;
            padding: 6px 12px;
            border-radius: 8px;
            transition: all 0.2s ease;
            font-family: inherit;
        }

        .post-action-btn:hover {
            background: #f1f5f9;
            color: #32418C;
        }

        .post-action-btn i {
            font-size: 1rem;
        }

        .post-action-btn .count {
            color: #94a3b8;
            font-weight: 400;
        }

        /* Post Status Badges */
        .post-badges {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-top: 4px;
        }

        .post-badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.7rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .post-badge.visibility {
            background: #e8edf4;
            color: #475569;
        }

        .post-badge.visibility.public {
            background: #dbeafe;
            color: #1e40af;
        }

        .post-badge.visibility.private {
            background: #f1f5f9;
            color: #475569;
        }

        .post-badge.moderation {
            background: #fef3c7;
            color: #92400e;
        }

        .post-badge.moderation.approved {
            background: #d1fae5;
            color: #065f46;
        }

        .post-badge.moderation.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .post-badge.moderation.rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Comments Section - Enhanced */
        .post-comments {
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid #f0f2f5;
        }

        .post-comments-header {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 12px;
            font-weight: 600;
            color: #475569;
            font-size: 0.85rem;
        }

        .post-comments-header i {
            color: #32418C;
        }

        .post-comment {
            display: flex;
            gap: 10px;
            padding: 10px 14px;
            background: #f8fafc;
            border-radius: 10px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }

        .post-comment:last-child {
            margin-bottom: 0;
        }

        .post-comment:hover {
            background: #f1f5f9;
        }

        .post-comment-avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 12px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .post-comment-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .post-comment-body {
            flex: 1;
        }

        .post-comment-author {
            font-weight: 600;
            color: #1e293b;
            font-size: 0.85rem;
        }

        .post-comment-text {
            color: #475569;
            font-size: 0.9rem;
            line-height: 1.5;
            margin-top: 2px;
        }

        .post-comment-time {
            color: #94a3b8;
            font-size: 0.75rem;
            margin-top: 2px;
        }

        .post-comment-time i {
            margin-right: 3px;
        }

        .more-comments-link {
            display: inline-block;
            color: #32418C;
            font-size: 0.85rem;
            font-weight: 500;
            padding: 6px 14px;
            background: #e8edf4;
            border-radius: 8px;
            margin-top: 8px;
            transition: all 0.2s ease;
            cursor: pointer;
        }

        .more-comments-link:hover {
            background: #d1d9e6;
        }

        /* No Posts State */
        .no-posts {
            text-align: center;
            padding: 50px 20px;
            background: #f8fafc;
            border-radius: 12px;
            border: 2px dashed #e8edf4;
        }

        .no-posts i {
            font-size: 3rem;
            color: #cbd5e1;
            display: block;
            margin-bottom: 16px;
        }

        .no-posts p {
            font-size: 1rem;
            color: #94a3b8;
            margin: 0;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .post-item {
                padding: 16px;
                border-radius: 10px;
            }
            
            .post-images.grid-1 img {
                height: 240px;
            }
            
            .post-images.grid-2,
            .post-images.grid-3,
            .post-images.grid-4,
            .post-images.grid-5,
            .post-images.grid-6 {
                grid-template-columns: 1fr 1fr;
            }
            
            .post-images img {
                height: 150px;
            }
            
            .post-images.grid-4 img:first-child,
            .post-images.grid-5 img:first-child {
                height: 180px;
            }
            
            .post-images.grid-6 img {
                height: 140px;
            }
            
            .post-actions {
                flex-wrap: wrap;
                gap: 8px;
            }
            
            .post-action-btn {
                font-size: 0.8rem;
                padding: 4px 10px;
            }
        }

        @media (max-width: 480px) {
            .post-images {
                grid-template-columns: 1fr !important;
            }
            
            .post-images img {
                height: 200px !important;
            }
            
            .post-images.grid-1 img {
                height: 220px !important;
            }
            
            .post-images.grid-4 img:first-child,
            .post-images.grid-5 img:first-child {
                height: 200px !important;
            }
            
            .post-item {
                padding: 12px;
            }
            
            .post-header {
                gap: 10px;
            }
            
            .post-avatar {
                width: 34px;
                height: 34px;
                font-size: 13px;
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

        /* Tabs */
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

        /* Panels */
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

        /* List Items */
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

        /* Responsive */
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

        /* Update post avatar to support images */
        .post-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #32418C, #4a59a3);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
            overflow: hidden;
        }

        .post-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* ========================================
        IMAGE LIGHTBOX MODAL
        ======================================== */

        .image-lightbox {
            position: fixed;
            inset: 0;
            z-index: 10000;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(10, 15, 30, 0.92);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .image-lightbox.active {
            display: flex;
            opacity: 1;
        }

        .image-lightbox-stage {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 80px;
            box-sizing: border-box;
        }

        .image-lightbox-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 20px 80px rgba(0, 0, 0, 0.5);
            animation: lightboxZoomIn 0.35s cubic-bezier(0.16, 1, 0.3, 1);
            user-select: none;
        }

        @keyframes lightboxZoomIn {
            from {
                opacity: 0;
                transform: scale(0.92);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Top bar */
        .image-lightbox-topbar {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            color: #fff;
            z-index: 2;
            background: linear-gradient(to bottom, rgba(0,0,0,0.6), transparent);
        }

        .image-lightbox-counter {
            font-size: 0.95rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.85);
            letter-spacing: 0.5px;
        }

        .image-lightbox-counter strong {
            color: #fff;
        }

        .image-lightbox-close {
            width: 44px;
            height: 44px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
        }

        .image-lightbox-close:hover {
            background: rgba(239, 68, 68, 0.9);
            transform: rotate(90deg);
        }

        /* Navigation arrows */
        .image-lightbox-nav {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 52px;
            height: 52px;
            border: none;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s ease;
            backdrop-filter: blur(4px);
            z-index: 2;
        }

        .image-lightbox-nav:hover {
            background: rgba(50, 65, 140, 0.85);
            transform: translateY(-50%) scale(1.08);
        }

        .image-lightbox-nav.prev { left: 24px; }
        .image-lightbox-nav.next { right: 24px; }

        .image-lightbox-nav.hidden {
            display: none;
        }

        /* Thumbnail strip */
        .image-lightbox-thumbs {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 24px;
            background: linear-gradient(to top, rgba(0,0,0,0.6), transparent);
            z-index: 2;
            overflow-x: auto;
            scrollbar-width: thin;
        }

        .image-lightbox-thumbs::-webkit-scrollbar {
            height: 4px;
        }

        .image-lightbox-thumbs::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.3);
            border-radius: 4px;
        }

        .image-lightbox-thumb {
            width: 64px;
            height: 64px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            opacity: 0.5;
            border: 2px solid transparent;
            transition: all 0.2s ease;
            flex-shrink: 0;
        }

        .image-lightbox-thumb:hover {
            opacity: 0.85;
        }

        .image-lightbox-thumb.active {
            opacity: 1;
            border-color: #4a59a3;
            transform: scale(1.05);
        }

        /* Prevent body scroll when modal is open */
        body.lightbox-open {
            overflow: hidden;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .image-lightbox-stage {
                padding: 70px 16px 90px;
            }

            .image-lightbox-nav {
                width: 42px;
                height: 42px;
                font-size: 1rem;
            }

            .image-lightbox-nav.prev { left: 10px; }
            .image-lightbox-nav.next { right: 10px; }

            .image-lightbox-thumb {
                width: 52px;
                height: 52px;
            }
        }

    </style>
</head>
<body>
    @include('partials.admin-navbar')

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
            
            <nav class="sidebar-nav">
                <p class="nav-section-title">Admin Menu</p>
                <a href="/admin/dashboard" class="nav-item"><i class="fa-solid fa-chart-line"></i><span>Dashboard</span></a>
                <a href="{{ route('admin.directory') }}" class="nav-item active"><i class="fa-solid fa-users"></i><span>Alumni Directory</span></a>
                <a href="{{ route('announcements.index') }}" class="nav-item"><i class="fa-solid fa-bullhorn"></i><span>Announcements</span></a>
                <a href="{{ route('events.index') }}" class="nav-item"><i class="fa-solid fa-calendar-check"></i><span>Events</span></a>
                <a href="{{ route('perks.index') }}" class="nav-item"><i class="fa-solid fa-gift"></i><span>Perks & Discounts</span></a>
                <a href="/admin/alumni_tracer" class="nav-item"><i class="fa-solid fa-location-dot"></i><span>Alumni Tracer</span></a>
                <a href="/admin/messages" class="nav-item"><i class="fa-solid fa-envelope"></i><span>Messages</span></a>
                <a href="{{ route('admin.settings') }}" class="nav-item"><i class="fa-solid fa-gear"></i><span>Settings</span></a>
            </nav>

        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <header class="page-header">
                <div class="header-content">
                    <div class="header-title-section">
                        <h1 class="page-title"><i class="fa-solid fa-user"></i> Alumni Profile</h1>
                        <p class="page-subtitle">View complete details for {{ $alumnus->first_name }} {{ $alumnus->last_name }}</p>
                    </div>
                    <div class="header-actions">
                        <a href="{{ route('admin.alumni.edit', $alumnus->id) }}" class="btn btn-primary">
                            <i class="fa-solid fa-pen-to-square"></i> <span>Edit Profile</span>
                        </a>
                    </div>
                </div>
            </header>

            <div class="profile-container">
                <a href="{{ route('admin.directory') }}" class="btn-back">
                    <i class="fa-solid fa-arrow-left"></i> Back to Directory
                </a>

                <!-- Profile Information Card -->
                <div class="profile-card">
                    <div class="profile-header">
                        @php
                            $photoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
                            if ($photoPath === '') { $photoUrl = '/assets/FINAL-NULIPA.jpg'; } 
                            elseif (preg_match('/^https?:\/\//i', $photoPath)) { $photoUrl = $photoPath; } 
                            elseif (str_starts_with($photoPath, '/storage/')) { $photoUrl = $photoPath; } 
                            elseif (str_starts_with($photoPath, 'storage/')) { $photoUrl = '/' . $photoPath; } 
                            elseif (str_starts_with($photoPath, '/')) { $photoUrl = $photoPath; } 
                            elseif (trim((string) config('filesystems.disks.s3.url')) !== '') { $photoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($photoPath, '/'); } 
                            else { $photoUrl = asset('storage/' . ltrim($photoPath, '/')); }
                        @endphp
                        <img src="{{ $photoUrl }}" alt="{{ $alumnus->first_name }}" class="profile-photo" onerror="this.src='/assets/FINAL-NULIPA.jpg'">
                        <div class="profile-info">
                            <h2>
                                {{ $alumnus->first_name }} {{ $alumnus->middle_name }} {{ $alumnus->last_name }}
                                @if($alumnus->verification_status)
                                    <span class="verification-badge {{ $alumnus->verification_status }}">
                                        <i class="fa-solid fa-{{ $alumnus->verification_status == 'verified' ? 'check-circle' : ($alumnus->verification_status == 'pending' ? 'clock' : 'times-circle') }}"></i>
                                        {{ ucfirst($alumnus->verification_status) }}
                                    </span>
                                @endif
                            </h2>
                            <p><i class="fa-solid fa-graduation-cap"></i> {{ $alumnus->program ?? 'N/A' }} &bull; Class of {{ optional($alumnus->year_graduated)->format('Y') ?? 'N/A' }}</p>
                            <p style="margin-top: 8px;">
                                <span class="status-badge {{ ($alumnus->account_status ?? 1) == 1 ? 'active' : 'inactive' }}">
                                    <i class="fa-solid fa-circle"></i>
                                    {{ ($alumnus->account_status ?? 1) == 1 ? 'Active' : 'Restricted' }}
                                </span>
                                @if($alumnus->is_online)
                                    <span class="status-badge active" style="margin-left: 8px;">
                                        <i class="fa-solid fa-circle"></i> Online
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    <div class="profile-details">
                        <div class="detail-item"><label>Student ID</label><span>{{ $alumnus->student_id_number ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Email Address</label><span>{{ $alumnus->email ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Phone Number</label><span>{{ $alumnus->phone_number ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Sex</label><span>{{ $alumnus->sex ?? 'N/A' }}</span></div>
                        <div class="detail-item"><label>Date of Birth</label><span>{{ $alumnus->date_of_birth ? date('F d, Y', strtotime($alumnus->date_of_birth)) : 'N/A' }}</span></div>
                        <div class="detail-item"><label>Joined</label><span>{{ $alumnus->created_at ? date('M d, Y', strtotime($alumnus->created_at)) : 'N/A' }}</span></div>
                        @if($alumnus->alumni_bio)
                            <div class="detail-item" style="grid-column: 1 / -1;">
                                <label>Biography</label>
                                <span style="font-weight: 400; line-height: 1.6;">{{ $alumnus->alumni_bio }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Address Information Card -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-location-dot"></i>
                        Address Information
                    </h3>
                    
                    @if($alumnus->addresses->isNotEmpty())
                        @foreach($alumnus->addresses as $address)
                            <div class="address-card">
                                <div class="address-type">
                                    <i class="fa-solid fa-{{ $address->address_type == 'home' ? 'house' : ($address->address_type == 'work' ? 'briefcase' : 'map-pin') }}"></i>
                                    {{ ucfirst($address->address_type ?? 'Other') }} Address
                                </div>
                                <div class="address-text">
                                    @php
                                        $addressParts = [];
                                        if ($address->street) $addressParts[] = $address->street;
                                        if ($address->barangay) $addressParts[] = $address->barangay;
                                        if ($address->municipality) $addressParts[] = $address->municipality;
                                        if ($address->province) $addressParts[] = $address->province;
                                        if ($address->region) $addressParts[] = $address->region;
                                        $fullAddress = implode(', ', $addressParts);
                                        if ($address->zip_code) $fullAddress .= ' ' . $address->zip_code;
                                    @endphp
                                    {{ $fullAddress ?: 'No address details provided' }}
                                </div>
                                
                                @if($address->latitude && $address->longitude)
                                    <div class="coordinates">
                                        <i class="fa-solid fa-globe"></i>
                                        Coordinates: {{ number_format((float)$address->latitude, 6) }}, {{ number_format((float)$address->longitude, 6) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                        
                        @php
                            $hasCoordinates = $alumnus->addresses->filter(function($addr) {
                                return $addr->latitude && $addr->longitude;
                            })->isNotEmpty();
                        @endphp
                        
                        @if($hasCoordinates)
                            <div class="map-container">
                                <div id="alumniMap"></div>
                            </div>
                        @endif
                    @else
                        <div class="no-address">
                            <i class="fa-solid fa-map-pin"></i>
                            <p>No address information available for this alumni.</p>
                        </div>
                    @endif
                </div>

                <!-- Professional Information: Employment + Skills -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-briefcase"></i>
                        Professional Information
                    </h3>
                    
                    <div class="two-col-grid">
                        <!-- Employment -->
                        <div>
                            <h4 style="color: #475569; margin-bottom: 15px; font-size: 1rem;">
                                <i class="fa-solid fa-building" style="color: #3b82f6;"></i> Employment History
                            </h4>
                            @if($alumnus->employments->isNotEmpty())
                                @foreach($alumnus->employments as $employment)
                                    <div class="professional-item">
                                        <div class="item-title">{{ $employment->job_title }}</div>
                                        <div class="item-subtitle">{{ $employment->company }}</div>
                                        <div class="item-subtitle" style="font-size: 0.9rem;">
                                            <i class="fa-solid fa-location-dot"></i> {{ $employment->location }}
                                        </div>
                                        <div class="item-dates">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ date('M Y', strtotime($employment->start_date)) }} - 
                                            @if($employment->is_current)
                                                <span style="color: #16a34a; font-weight: 500;">Present</span>
                                            @elseif($employment->end_date)
                                                {{ date('M Y', strtotime($employment->end_date)) }}
                                            @else
                                                N/A
                                            @endif
                                            @if($employment->is_current)
                                                <span style="display: inline-block; background: #dcfce7; color: #166534; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; margin-left: 8px;">Current</span>
                                            @endif
                                        </div>
                                        @if($employment->career_description)
                                            <div style="margin-top: 8px; color: #64748b; font-size: 0.9rem;">
                                                {{ $employment->career_description }}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="no-data">
                                    <i class="fa-solid fa-briefcase"></i>
                                    <p>No employment history available.</p>
                                </div>
                            @endif
                        </div>
                        
                        <!-- Skills -->
                        <div>
                            <h4 style="color: #475569; margin-bottom: 15px; font-size: 1rem;">
                                <i class="fa-solid fa-code" style="color: #3b82f6;"></i> Skills
                            </h4>
                            @if($alumnus->skills->isNotEmpty())
                                <div style="display: flex; flex-wrap: wrap; gap: 4px;">
                                    @foreach($alumnus->skills as $skill)
                                        <span class="skill-tag">{{ $skill->skill_name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-data">
                                    <i class="fa-solid fa-code"></i>
                                    <p>No skills listed.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Tracer Form Status -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-clipboard-list"></i>
                        Tracer Form Status
                    </h3>
                    
                    @if($alumnus->tracerResponses->isNotEmpty())
                        @foreach($alumnus->tracerResponses as $response)
                            <div class="professional-item" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
                                <div>
                                    <div class="item-title">Form #{{ $response->form_id }}</div>
                                    <div class="item-subtitle">
                                        Status: <span class="status-badge {{ $response->status == 'completed' ? 'active' : 'pending' }}">
                                            {{ ucfirst($response->status) }}
                                        </span>
                                    </div>
                                    <div class="item-dates">
                                        <i class="fa-regular fa-calendar"></i>
                                        Started: {{ $response->created_at ? date('M d, Y', strtotime($response->created_at)) : 'N/A' }}
                                        @if($response->submitted_at)
                                            &bull; Submitted: {{ date('M d, Y', strtotime($response->submitted_at)) }}
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="event-status" style="background: {{ $response->status == 'completed' ? '#dcfce7' : '#fef9c3' }}; color: {{ $response->status == 'completed' ? '#166534' : '#854d0e' }};">
                                        <i class="fa-solid fa-{{ $response->status == 'completed' ? 'check' : 'clock' }}"></i>
                                        {{ ucfirst($response->status) }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no-data">
                            <i class="fa-solid fa-clipboard-list"></i>
                            <p>No tracer form responses recorded.</p>
                        </div>
                    @endif
                </div>

                <!-- Event Registrations -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-calendar-check"></i>
                        Event Registrations
                    </h3>
                    
                    @php
                        $activeRegistrations = $alumnus->eventRegistrations->filter(function($reg) {
                            return $reg->event && $reg->event->status == 1;
                        });
                    @endphp
                    
                    @if($activeRegistrations->isNotEmpty())
                        @foreach($activeRegistrations as $registration)
                            <div class="event-item">
                                <div class="event-info">
                                    <div class="event-title">{{ $registration->event->title }}</div>
                                    <div class="event-details">
                                        <i class="fa-regular fa-calendar"></i>
                                        {{ date('M d, Y', strtotime($registration->event->start_date)) }} - 
                                        {{ date('M d, Y', strtotime($registration->event->end_date)) }}
                                        @if($registration->event->event_type)
                                            &bull; {{ ucfirst($registration->event->event_type) }}
                                        @endif
                                    </div>
                                    <div class="event-details">
                                        <i class="fa-regular fa-clock"></i>
                                        Capacity: {{ $registration->event->max_capacity ?? 'N/A' }}
                                        @if($registration->rsvp_date)
                                            &bull; RSVP: {{ date('M d, Y', strtotime($registration->rsvp_date)) }}
                                        @endif
                                    </div>
                                </div>
                                <div>
                                    <span class="event-status">
                                        <i class="fa-solid fa-check-circle"></i>
                                        Registered
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="no-data">
                            <i class="fa-solid fa-calendar-check"></i>
                            <p>No active event registrations.</p>
                        </div>
                    @endif
                </div>

                <!-- Social Information: Followers, Following -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-users"></i>
                        Social Information
                    </h3>
                    
                    <div class="three-col-grid">
                        <div class="follower-stat">
                            <span class="number">{{ $alumnus->followers->count() }}</span>
                            <span class="label"><i class="fa-solid fa-user-plus"></i> Followers</span>
                        </div>
                        <div class="follower-stat">
                            <span class="number">{{ $alumnus->following->count() }}</span>
                            <span class="label"><i class="fa-solid fa-user-friends"></i> Following</span>
                        </div>
                        <div class="follower-stat">
                            <span class="number">{{ $alumnus->posts->count() }}</span>
                            <span class="label"><i class="fa-solid fa-pen"></i> Posts</span>
                        </div>
                    </div>
                </div>

                <!-- Posts Section - Enhanced -->
                <div class="profile-card">
                    <h3 class="section-title">
                        <i class="fa-solid fa-newspaper"></i>
                        Alumni Posts
                        <span style="font-size: 0.8rem; font-weight: 400; color: #94a3b8; margin-left: 8px;">
                            ({{ $alumnus->posts->count() }} posts)
                        </span>
                    </h3>
                    
                    @if($alumnus->posts->isNotEmpty())
                        @foreach($alumnus->posts->sortByDesc('created_at') as $post)
                            <div class="post-item">
                                <!-- Post Header with Alumni Info -->
                                <div class="post-header">
                                    @php
                                        // Get the alumni's profile photo URL
                                        $alumniPhotoPath = trim((string) ($alumnus->alumni_photo ?: $alumnus->card_photo));
                                        $hasPhoto = !empty($alumniPhotoPath);
                                        
                                        if ($hasPhoto) {
                                            if (preg_match('/^https?:\/\//i', $alumniPhotoPath)) {
                                                $alumniPhotoUrl = $alumniPhotoPath;
                                            } elseif (str_starts_with($alumniPhotoPath, '/storage/')) {
                                                $alumniPhotoUrl = $alumniPhotoPath;
                                            } elseif (str_starts_with($alumniPhotoPath, 'storage/')) {
                                                $alumniPhotoUrl = '/' . $alumniPhotoPath;
                                            } elseif (str_starts_with($alumniPhotoPath, '/')) {
                                                $alumniPhotoUrl = $alumniPhotoPath;
                                            } elseif (trim((string) config('filesystems.disks.s3.url')) !== '') {
                                                $alumniPhotoUrl = rtrim((string) config('filesystems.disks.s3.url'), '/') . '/' . ltrim($alumniPhotoPath, '/');
                                            } else {
                                                $alumniPhotoUrl = asset('storage/' . ltrim($alumniPhotoPath, '/'));
                                            }
                                        } else {
                                            $alumniPhotoUrl = null;
                                        }
                                        
                                        $initials = strtoupper(substr($alumnus->first_name, 0, 1)) . strtoupper(substr($alumnus->last_name, 0, 1));
                                    @endphp
                                    
                                    <div class="post-avatar" style="{{ $hasPhoto ? 'padding: 0; overflow: hidden; background: none;' : '' }}">
                                        @if($hasPhoto)
                                            <img 
                                                src="{{ $alumniPhotoUrl }}" 
                                                alt="{{ $alumnus->first_name }} {{ $alumnus->last_name }}" 
                                                style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
                                                onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #32418C, #4a59a3)'; this.parentElement.innerHTML='{{ $initials }}';"
                                            >
                                        @else
                                            {{ $initials }}
                                        @endif
                                    </div>
                                    <div class="post-author-info">
                                        <div class="post-author-name">
                                            {{ $alumnus->first_name }} {{ $alumnus->last_name }}
                                            <i class="fa-solid fa-check-circle" style="color: #3b82f6;" title="Verified Alumni"></i>
                                        </div>
                                        <div class="post-timestamp">
                                            <i class="fa-regular fa-calendar"></i>
                                            {{ $post->created_at ? date('M d, Y \a\t h:i A', strtotime($post->created_at)) : 'N/A' }}
                                        </div>
                                    </div>
                                    <div class="post-badges">
                                        <span class="post-badge visibility {{ $post->visibility ?? 'public' }}">
                                            <i class="fa-solid fa-{{ ($post->visibility ?? 'public') == 'public' ? 'globe' : 'lock' }}"></i>
                                            {{ ucfirst($post->visibility ?? 'public') }}
                                        </span>
                                        <span class="post-badge moderation {{ $post->moderation_status ?? 'pending' }}">
                                            <i class="fa-solid fa-{{ ($post->moderation_status ?? 'pending') == 'approved' ? 'check' : 'clock' }}"></i>
                                            {{ ucfirst($post->moderation_status ?? 'pending') }}
                                        </span>
                                    </div>
                                </div>
                                
                                <!-- Post Caption -->
                                @if($post->caption)
                                    <div class="post-caption">{{ $post->caption }}</div>
                                @endif
                                
                                <!-- Post Images - Dynamic Grid -->
                                @if($post->images->isNotEmpty())
                                    @php
                                        $imageCount = $post->images->count();
                                        $gridClass = 'grid-' . min($imageCount, 6);
                                    @endphp
                                    <div class="post-images {{ $gridClass }}">
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
                                                onclick="openLightbox(this, {{ $post->id }})"
                                            >
                                        @endforeach
                                    </div>
                                @endif
                                
                                <!-- Post Actions -->
                                <div class="post-actions">
                                    <button class="post-action-btn" onclick="openInteractionsModal({{ $post->id }}, 'likes')" title="View likes">
                                        <i class="fa-regular fa-heart"></i>
                                        <span class="count">{{ $post->reactions->count() }}</span>
                                    </button>
                                    <button class="post-action-btn" onclick="openInteractionsModal({{ $post->id }}, 'comments')" title="View comments">
                                        <i class="fa-regular fa-comment"></i>
                                        <span class="count">{{ $post->comments->count() }}</span>
                                    </button>
                                    <button class="post-action-btn" onclick="openInteractionsModal({{ $post->id }}, 'reposts')" title="View reposts">
                                        <i class="fa-solid fa-retweet"></i>
                                        <span class="count">{{ $post->reposts()->count() }}</span>
                                    </button>
                                    <span style="margin-left: auto; font-size: 0.75rem; color: #94a3b8;">
                                        <i class="fa-regular fa-eye"></i> 
                                        {{ $post->visibility ?? 'public' }}
                                    </span>
                                </div>
                                
                                <!-- Comments Section -->
                                @if($post->comments->isNotEmpty())
                                    <div class="post-comments">
                                        <div class="post-comments-header">
                                            <i class="fa-regular fa-comment-dots"></i>
                                            Recent Comments
                                            <span style="font-weight: 400; color: #94a3b8; font-size: 0.75rem;">
                                                ({{ $post->comments->count() }} total)
                                            </span>
                                        </div>
                                        
                                        @foreach($post->comments->take(3) as $comment)
                                            @php
                                                // Get commenter's profile photo
                                                $commenterPhotoPath = trim((string) ($comment->alumni->alumni_photo ?? $comment->alumni->card_photo ?? ''));
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
                                                
                                                $commenterInitials = strtoupper(substr($comment->alumni->first_name ?? 'U', 0, 1)) . strtoupper(substr($comment->alumni->last_name ?? '', 0, 1));
                                            @endphp
                                            <div class="post-comment">
                                                <div class="post-comment-avatar" style="{{ $commenterHasPhoto ? 'padding: 0; overflow: hidden; background: none;' : '' }}">
                                                    @if($commenterHasPhoto && $commenterPhotoUrl)
                                                        <img 
                                                            src="{{ $commenterPhotoUrl }}" 
                                                            alt="{{ $comment->alumni->first_name ?? 'User' }}" 
                                                            style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;"
                                                            onerror="this.style.display='none'; this.parentElement.style.background='linear-gradient(135deg, #6366f1, #8b5cf6)'; this.parentElement.innerHTML='{{ $commenterInitials }}';"
                                                        >
                                                    @else
                                                        {{ $commenterInitials }}
                                                    @endif
                                                </div>
                                                <div class="post-comment-body">
                                                    <div class="post-comment-author">
                                                        {{ $comment->alumni->first_name ?? 'Unknown' }} {{ $comment->alumni->last_name ?? '' }}
                                                    </div>
                                                    <div class="post-comment-text">{{ $comment->comment }}</div>
                                                    <div class="post-comment-time">
                                                        <i class="fa-regular fa-clock"></i>
                                                        {{ $comment->created_at ? date('M d, Y \a\t h:i A', strtotime($comment->created_at)) : 'N/A' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                        
                                        @if($post->comments->count() > 3)
                                            <div class="more-comments-link" onclick="openInteractionsModal({{ $post->id }}, 'comments')" style="cursor: pointer;">
                                                <i class="fa-regular fa-comment-dots"></i>
                                                View all {{ $post->comments->count() }} comments
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    @else
                        <div class="no-posts">
                            <i class="fa-regular fa-newspaper"></i>
                            <p>No posts from this alumni yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </main>
    </div>

    <!-- Alert Toast -->
    <div id="alertToast" class="alert-toast">
        <i class="alert-icon fa-solid fa-circle-check"></i>
        <span class="alert-message"></span>
        <button class="alert-close" onclick="hideAlert()"><i class="fa-solid fa-xmark"></i></button>
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
                    <!-- Likes Panel -->
                    <div class="interactions-panel active" id="likesPanel">
                        <div class="interactions-list" id="likesList">
                            <!-- Dynamically populated -->
                            <div class="interactions-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i> Loading likes...
                            </div>
                        </div>
                    </div>
                    
                    <!-- Comments Panel -->
                    <div class="interactions-panel" id="commentsPanel">
                        <div class="interactions-list" id="commentsList">
                            <div class="interactions-loading">
                                <i class="fa-solid fa-spinner fa-spin"></i> Loading comments...
                            </div>
                        </div>
                    </div>
                    
                    <!-- Reposts Panel -->
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

    <!-- Image Lightbox Modal -->
    <div id="imageLightbox" class="image-lightbox">
        <div class="image-lightbox-topbar">
            <div class="image-lightbox-counter">
                <strong id="lightboxCurrent">1</strong> / <span id="lightboxTotal">1</span>
            </div>
            <button class="image-lightbox-close" onclick="closeLightbox()" title="Close (Esc)">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <button class="image-lightbox-nav prev" id="lightboxPrev" onclick="navigateLightbox(-1)" title="Previous (←)">
            <i class="fa-solid fa-chevron-left"></i>
        </button>

        <div class="image-lightbox-stage">
            <img id="lightboxImage" class="image-lightbox-img" src="" alt="Preview">
        </div>

        <button class="image-lightbox-nav next" id="lightboxNext" onclick="navigateLightbox(1)" title="Next (→)">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <div class="image-lightbox-thumbs" id="lightboxThumbs"></div>
    </div>

    <!-- Leaflet JavaScript -->
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    
    <script>
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        function showAlert(message, type = 'success') {
            const toast = document.getElementById('alertToast');
            const icon = toast.querySelector('.alert-icon');
            const msg = toast.querySelector('.alert-message');
            const icons = { success: 'fa-circle-check', error: 'fa-circle-exclamation', info: 'fa-circle-info', warning: 'fa-circle-exclamation' };
            const colors = { success: 'var(--success)', error: 'var(--danger)', info: 'var(--info)', warning: 'var(--warning)' };
            
            icon.className = `alert-icon fa-solid ${icons[type] || icons.success}`;
            toast.style.borderColor = colors[type] || colors.success;
            msg.textContent = message;
            toast.classList.add('show');
            setTimeout(() => hideAlert(), 4000);
        }

        function hideAlert() { document.getElementById('alertToast').classList.remove('show'); }

        // Show flash messages from session
        @if(session('success'))
            window.addEventListener('DOMContentLoaded', () => showAlert("{{ session('success') }}", 'success'));
        @endif
        @if(session('error'))
            window.addEventListener('DOMContentLoaded', () => showAlert("{{ session('error') }}", 'error'));
        @endif

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() { if (window.innerWidth <= 1024) toggleMobileMenu(); });
        });

        // Initialize map with markers for all addresses that have coordinates
        document.addEventListener('DOMContentLoaded', function() {
            @php
                $addressesWithCoords = $alumnus->addresses->filter(function($addr) {
                    return $addr->latitude && $addr->longitude;
                });
            @endphp
            
            @if($addressesWithCoords->isNotEmpty())
                var map = L.map('alumniMap');
                
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
                }).addTo(map);
                
                var markers = [];
                
                @foreach($addressesWithCoords as $address)
                    var lat = {{ $address->latitude }};
                    var lng = {{ $address->longitude }};
                    
                    var marker = L.marker([lat, lng]);
                    
                    @php
                        $popupParts = [];
                        if ($address->address_type) $popupParts[] = '<strong>' . ucfirst($address->address_type) . ' Address</strong>';
                        if ($address->street) $popupParts[] = $address->street;
                        if ($address->barangay) $popupParts[] = $address->barangay;
                        if ($address->municipality) $popupParts[] = $address->municipality;
                        if ($address->province) $popupParts[] = $address->province;
                        if ($address->region) $popupParts[] = $address->region;
                        if ($address->zip_code) $popupParts[] = 'ZIP: ' . $address->zip_code;
                        $popupText = implode('<br>', $popupParts);
                    @endphp
                    
                    marker.bindPopup(`{!! $popupText !!}`);
                    marker.addTo(map);
                    markers.push(marker);
                @endforeach
                
                if (markers.length > 1) {
                    var group = L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                } else if (markers.length === 1) {
                    map.setView([markers[0].getLatLng().lat, markers[0].getLatLng().lng], 14);
                }
                
                setTimeout(function() {
                    map.invalidateSize();
                }, 500);
            @endif
        });

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
    
    // Set title
    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';
    
    // Switch tab
    switchInteractionTab(tab);
    
    // Load data
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
    
    // Update tabs
    document.querySelectorAll('.interactions-tab').forEach(t => {
        t.classList.remove('active');
        if (t.dataset.tab === tab) {
            t.classList.add('active');
        }
    });
    
    // Update panels
    document.querySelectorAll('.interactions-panel').forEach(p => {
        p.classList.remove('active');
    });
    document.getElementById(tab + 'Panel').classList.add('active');
    
    // Update title
    const titleMap = {
        'likes': 'Likes',
        'comments': 'Comments',
        'reposts': 'Reposts'
    };
    document.getElementById('interactionsModalTitle').textContent = titleMap[tab] || 'Interactions';
    
    // Load data if we have a post ID
    if (currentPostId) {
        loadInteractions(currentPostId, tab);
    }
}

function loadInteractions(postId, type) {
    const listId = type + 'List';
    const list = document.getElementById(listId);
    
    // Show loading
    list.innerHTML = `
        <div class="interactions-loading">
            <i class="fa-solid fa-spinner fa-spin"></i> Loading ${type}...
        </div>
    `;
    
    // Make AJAX request
    fetch(`/admin/posts/${postId}/interactions?type=${type}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
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
        
        // Check if user has a profile photo
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
    
// ========================================
// IMAGE LIGHTBOX
// ========================================

let lightboxImages = [];
let lightboxIndex = 0;

function openLightbox(clickedImg, postId) {
    // Collect all images from the same post container
    const postItem = clickedImg.closest('.post-item');
    if (!postItem) return;

    const allImgs = Array.from(postItem.querySelectorAll('.post-images img'));
    if (allImgs.length === 0) return;

    lightboxImages = allImgs;
    lightboxIndex = allImgs.indexOf(clickedImg);
    if (lightboxIndex < 0) lightboxIndex = 0;

    buildLightboxThumbs();
    showLightboxImage(lightboxIndex);

    const modal = document.getElementById('imageLightbox');
    modal.classList.add('active');
    document.body.classList.add('lightbox-open');
}

function closeLightbox() {
    const modal = document.getElementById('imageLightbox');
    modal.classList.remove('active');
    document.body.classList.remove('lightbox-open');
}

function showLightboxImage(index) {
    if (!lightboxImages.length) return;

    lightboxIndex = index;
    const imgEl = document.getElementById('lightboxImage');
    const newSrc = lightboxImages[index].src;

    // small crossfade animation
    imgEl.style.animation = 'none';
    imgEl.offsetHeight; // reflow
    imgEl.style.animation = '';
    imgEl.src = newSrc;

    document.getElementById('lightboxCurrent').textContent = index + 1;
    document.getElementById('lightboxTotal').textContent = lightboxImages.length;

    // show/hide nav
    const hasMultiple = lightboxImages.length > 1;
    document.getElementById('lightboxPrev').classList.toggle('hidden', !hasMultiple);
    document.getElementById('lightboxNext').classList.toggle('hidden', !hasMultiple);

    // update thumbnails
    document.querySelectorAll('.image-lightbox-thumb').forEach((thumb, i) => {
        thumb.classList.toggle('active', i === index);
    });

    // scroll active thumb into view
    const activeThumb = document.querySelectorAll('.image-lightbox-thumb')[index];
    if (activeThumb) {
        activeThumb.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
    }
}

function navigateLightbox(direction) {
    if (lightboxImages.length <= 1) return;
    let newIndex = lightboxIndex + direction;

    // wrap around
    if (newIndex < 0) newIndex = lightboxImages.length - 1;
    if (newIndex >= lightboxImages.length) newIndex = 0;

    showLightboxImage(newIndex);
}

function buildLightboxThumbs() {
    const container = document.getElementById('lightboxThumbs');
    container.innerHTML = '';

    if (lightboxImages.length <= 1) {
        container.style.display = 'none';
        return;
    }
    container.style.display = 'flex';

    lightboxImages.forEach((img, i) => {
        const thumb = document.createElement('img');
        thumb.src = img.src;
        thumb.className = 'image-lightbox-thumb' + (i === lightboxIndex ? ' active' : '');
        thumb.alt = 'Thumbnail ' + (i + 1);
        thumb.onclick = () => showLightboxImage(i);
        container.appendChild(thumb);
    });
}

// Keyboard navigation
document.addEventListener('keydown', function(e) {
    const lightbox = document.getElementById('imageLightbox');
    if (!lightbox || !lightbox.classList.contains('active')) return;

    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') navigateLightbox(-1);
    if (e.key === 'ArrowRight') navigateLightbox(1);
});

// Click backdrop to close
document.getElementById('imageLightbox')?.addEventListener('click', function(e) {
    if (e.target === this || e.target.classList.contains('image-lightbox-stage')) {
        closeLightbox();
    }
});

    </script>
</body>
</html>

{{-- This is the show alumni profile page - show.blade.php --}}