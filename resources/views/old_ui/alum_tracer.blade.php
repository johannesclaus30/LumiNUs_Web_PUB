<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Digital Alumni Tracer | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/alumni_tracer.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>

                <a href="{{ url('/admin/dashboard') }}" class="admin-menu-buttons">Admin Dashboard</a>
                <a href="{{ route('admin.directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-current">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="div-dashboard-container">
            
        <div class="tracer-layout">
    
            <div class="tracer-main-panel">
                <div class="panel-header">
                    <div class="header-logo-placeholder">NU</div>
                    <div class="header-titles">
                        <h2>NU Lipa College Tracer Study</h2>
                        <span class="status-badge">Accepting Responses</span>
                    </div>
                </div>

                <div class="survey-builder-container">
                    <div class="form-group">
                        <label>Header Photo:</label>
                        <div class="photo-upload-box">
                            <img src="/assets/logos/nu_banner.png" alt="National University Banner" class="banner-img">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Survey Title:</label>
                        <input type="text" class="form-control title-input" value="NU Lipa College Tracer Study">
                    </div>

                    <div class="form-group section-group">
                        <label>Section 1</label>
                        <input type="text" class="form-control" value="Data Privacy Notice">
                    </div>

                    <div class="form-group question-group">
                        <label>Question 1</label>
                        <div class="question-card">
                            <div class="question-card-header">
                                <input type="text" class="form-control question-title" value="Data Privacy Notice">
                                <select class="form-control question-type">
                                    <option>Multiple Choice</option>
                                </select>
                            </div>
                            
                            <div class="question-body">
                                <label>Description:</label>
                                <textarea class="form-control description-box" readonly>I understand and agree that by filling out and submitting this form, I am allowing NU Lipa to collect, process, use, share and disclose my personal information...</textarea>
                            </div>
                            
                            <div class="question-footer">
                                <label>Response:</label>
                                </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tracer-sidebar">
                <h2 class="sidebar-title">Alumni Tracer</h2>
                
                <div class="sidebar-tabs">
                    <button class="tab-btn active">Add New Survey</button>
                    <button class="tab-btn">Manage Surveys</button>
                </div>

                <div class="survey-list">
                    <div class="survey-item active">
                        <div class="survey-item-icon">NU</div>
                        <div class="survey-item-details">
                            <h4>NU Lipa College Tracer Study</h4>
                            <span>Accepting Responses</span>
                        </div>
                    </div>

                    <div class="survey-item">
                        <div class="survey-item-icon warning">NU</div>
                        <div class="survey-item-details">
                            <h4>NU Lipa SHS Tracer Study</h4>
                            <span>Accepting Responses</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        </div>
    </div>

</body>
</html>