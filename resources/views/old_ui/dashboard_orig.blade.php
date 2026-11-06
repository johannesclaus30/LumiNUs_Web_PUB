<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin_dashboard.css">
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>
<body>
    
    @include('partials.admin-navbar')

    <div class="layout-wrapper">
        <div class="admin-menu">
            <div>
                <p class="text-titles">Admin Menu</p>

                <a href="{{ url('/admin/dashboard') }}" class="admin-menu-current">Admin Dashboard</a>
                <a href="{{ route('admin.directory') }}" class="admin-menu-buttons">Alumni Directory</a>
                <a href="{{ route('announcements.index') }}" class="admin-menu-buttons">Announcement Editor</a>
                <a href="{{ route('events.index') }}" class="admin-menu-buttons">Event Organizer</a>
                <a href="{{ route('perks.index') }}" class="admin-menu-buttons">Perks and Discounts</a>
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-buttons">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="div-dashboard-container">
            <div class="div-dashboard-container admin-scrollable">
    
                <div class="dash-top-row">
                    <div class="stat-card stat-yellow">
                        <div class="stat-icon-wrapper">👤</div>
                        <div class="stat-details">
                            <span class="stat-label">Total Registered Alumni</span>
                            <span class="stat-number">1,256</span>
                        </div>
                    </div>
                    <div class="stat-card stat-blue">
                        <div class="stat-icon-wrapper">🌍</div>
                        <div class="stat-details">
                            <span class="stat-label">Mapped Alumni Worldwide</span>
                            <span class="stat-number">874</span>
                        </div>
                    </div>
                    <div class="stat-card stat-green">
                        <div class="stat-icon-wrapper">📈</div>
                        <div class="stat-details">
                            <span class="stat-label">Active Alumni This Month</span>
                            <span class="stat-number">342</span>
                        </div>
                    </div>
                    <div class="stat-card stat-red">
                        <div class="stat-icon-wrapper">🔔</div>
                        <div class="stat-details">
                            <span class="stat-label">Pending Notifications</span>
                            <span class="stat-number">8 Alerts</span>
                        </div>
                    </div>
                </div>

                <div class="dash-mid-row">
                    <div class="dash-card geo-card">
                        <div class="card-header">
                            <h3 class="card-title">Alumni Geo-Mapping</h3>
                        </div>
                        <div class="map-placeholder">
                            <div class="mock-map">Map Placeholder</div>
                        </div>
                        <div class="card-legend">
                            <span><i class="legend-dot dot-yellow"></i> High Density Alumni Areas</span>
                            <span><i class="legend-dot dot-green"></i> Moderate Density Alumni Areas</span>
                        </div>
                    </div>

                    <div class="dash-card activity-card">
                        <div class="card-header flex-between">
                            <h3 class="card-title">Alumni Activity Overview</h3>
                            <select class="dash-select">
                                <option>Last 6 Months</option>
                            </select>
                        </div>
                        <div class="chart-placeholder">
                            <div class="mock-chart">Line Chart Placeholder</div>
                        </div>
                        <div class="card-footer flex-between">
                            <div class="card-legend">
                                <span><i class="legend-box box-blue"></i> Daily Logins</span>
                                <span><i class="legend-box box-yellow"></i> Event Registrations</span>
                            </div>
                            <button class="btn-dash btn-light-blue">View All Events ></button>
                        </div>
                    </div>
                </div>

                <div class="dash-bot-row">
                    <div class="dash-card">
                        <div class="card-header flex-between">
                            <h3 class="card-title">Pending Tracer Surveys</h3>
                            <a href="#" class="link-text">View All ></a>
                        </div>
                        <div class="list-wrapper">
                            <div class="list-item">
                                <div class="item-avatar">SL</div>
                                <div class="item-info">
                                    <p class="item-name">Sarah Lin</p>
                                    <p class="item-sub">Pending Checkout</p>
                                </div>
                                <div class="item-action">
                                    <p class="due-text">Due Dec 21</p>
                                    <span class="badge badge-yellow">✔ Due</span>
                                </div>
                            </div>
                            <div class="list-item">
                                <div class="item-avatar">JM</div>
                                <div class="item-info">
                                    <p class="item-name">John Murphy</p>
                                    <p class="item-sub">Awaiting Consent</p>
                                </div>
                                <div class="item-action">
                                    <p class="due-text">Due Dec 28</p>
                                    <span class="badge badge-yellow">✔ Due</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="dash-card events-card">
                        <div class="mini-map-placeholder"></div>
                        <div class="list-wrapper mt-10">
                            <div class="event-item">
                                <div class="event-left">
                                    <i class="legend-dot dot-blue"></i>
                                    <span>NU Alumni Meetup</span>
                                </div>
                                <span class="event-date">Apr 25 ∨</span>
                            </div>
                            <div class="event-item">
                                <div class="event-left">
                                    <i class="legend-dot dot-yellow"></i>
                                    <span>Career Fair</span>
                                </div>
                                <span class="event-date">Apr 30 ∨</span>
                            </div>
                            <div class="event-item">
                                <div class="event-left">
                                    <i class="legend-dot dot-green"></i>
                                    <span>Workshop</span>
                                </div>
                                <span class="event-date">May 5 ∨</span>
                            </div>
                        </div>
                    </div>

                    <div class="dash-card">
                        <div class="card-header">
                            <h3 class="card-title">Recent Announcements</h3>
                        </div>
                        <div class="list-wrapper">
                            <div class="message-item">
                                <div class="item-avatar">AG</div>
                                <div class="msg-content">
                                    <div class="msg-header">
                                        <span class="msg-name">Amy Garcia</span>
                                        <span class="msg-time">3 min ago</span>
                                    </div>
                                    <p class="msg-text">Anyone going to the alumni meetup over report? Looking forward to catching up!</p>
                                </div>
                            </div>
                            <div class="message-item">
                                <div class="item-avatar">DL</div>
                                <div class="msg-content">
                                    <div class="msg-header">
                                        <span class="msg-name">Daniel Lee</span>
                                        <span class="msg-time">10 min ago</span>
                                    </div>
                                    <p class="msg-text">You'll be free too. Excited to reconnect.</p>
                                </div>
                            </div>
                        </div>
                        <button class="btn-dash btn-light-yellow mt-auto">View All Events ></button>
                    </div>

                    <div class="dash-card flex-col">
                        <div class="card-header">
                            <h3 class="card-title">In-App Messaging</h3>
                        </div>
                        <div class="list-wrapper">
                            <div class="message-item">
                                <div class="item-avatar">AG</div>
                                <div class="msg-content">
                                    <div class="msg-header">
                                        <span class="msg-name">Amy Garcia</span>
                                        <span class="msg-time">3m</span>
                                    </div>
                                    <p class="msg-text line-clamp">Anyone going to the alumni meetup over report? Looking forward to catching up!</p>
                                </div>
                            </div>
                        </div>
                        <button class="btn-dash btn-primary mt-auto">Open Inbox ></button>
                    </div>
                </div>

            </div>
        </div>
    </div>

</body>
</html>