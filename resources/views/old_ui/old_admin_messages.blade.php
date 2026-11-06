<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/messages.css">
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
                <a href="{{ url('/admin/alumni_tracer') }}" class="admin-menu-buttons">NU Alumni Tracer</a>
                <a href="{{ url('/admin/messages') }}" class="admin-menu-current">Messages</a>
                <a href="{{ route('admin.settings') }}" class="admin-menu-buttons">Settings</a>
            </div>

            <a href="{{ route('admin.logout') }}" class="admin-menu-signout">Sign Out</a>
        </div>
        <div class="div-dashboard-container">
            <div class="div-dashboard-container">
                <div class="messages-layout">
                    
                    <div class="chat-panel">
                        <div class="chat-header">
                            <div class="avatar-placeholder">J</div> 
                            <div class="chat-header-info">
                                <h2>Dela Cruz, Juan Miguel</h2>
                                <p>Batch 2023 | BSIT</p>
                            </div>
                        </div>

                        <div class="chat-messages admin-scrollable">
                            <div class="chat-timestamp">Today 11:40 AM</div>

                            <div class="message-row received">
                                <div class="avatar-placeholder small">J</div>
                                <div class="msg-bubble">
                                    Good morning! I saw the post about the Pickle Bark event on March 14. Is it open to beginners? I've never played pickleball before. 😅
                                </div>
                            </div>

                            <div class="message-row sent">
                                <div class="msg-bubble">
                                    Good morning, Sir! Yes, absolutely! The Pickle Bark Open Play is open to all skill levels. We'll have people on-site to help with the basics if it's your first time.
                                </div>
                            </div>

                            <div class="message-row received">
                                <div class="avatar-placeholder small">J</div>
                                <div class="msg-bubble">
                                    Awesome! Do I need to bring my own paddle, or is there a rental at GoldenTop Sports Center?
                                </div>
                            </div>

                            <div class="message-row sent">
                                <div class="msg-bubble">
                                    We recommend bringing your own if you have one, but we will have a limited number of spare paddles available. Just make sure to register via the QR code so we can head-count!
                                </div>
                            </div>

                            <div class="message-row received">
                                <div class="avatar-placeholder small">J</div>
                                <div class="msg-bubble">
                                    Perfect. Registered! Thanks so much.
                                </div>
                            </div>
                        </div>

                        <div class="chat-input-area">
                            <div class="chat-input-wrapper">
                                <button class="btn-attach">📎</button>
                                <input type="text" placeholder="Type a message here.">
                            </div>
                            <button class="btn-send">Send <span>➤</span></button>
                        </div>
                    </div>

                    <div class="contacts-panel">
                        <div class="contacts-header">
                            <h2>Messages</h2>
                            <div class="header-actions">
                                <button class="btn-icon">🔍</button>
                                <button class="btn-icon solid">📝</button>
                            </div>
                        </div>

                        <div class="contacts-tabs">
                            <button class="tab active">All Chats</button>
                            <button class="tab">Channels</button>
                        </div>

                        <div class="contacts-list admin-scrollable">
                            <div class="contact-item active">
                                <div class="avatar-placeholder">J</div>
                                <div class="contact-info">
                                    <h4 class="contact-name">Dela Cruz, Juan Miguel</h4>
                                    <p class="contact-batch">Batch 2023 | BSIT</p>
                                    <p class="contact-preview">Perfect. Registered! Thanks so much.</p>
                                </div>
                            </div>
                            
                            <div class="contact-item">
                                <div class="avatar-placeholder">A</div>
                                <div class="contact-info">
                                    <h4 class="contact-name">Reyes, Althea</h4>
                                    <p class="contact-batch">Batch 2026 | BSA</p>
                                    <p class="contact-preview">You: Thank you, Ms!</p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="avatar-placeholder">L</div>
                                <div class="contact-info">
                                    <h4 class="contact-name">Gutierrez, Louie Andres</h4>
                                    <p class="contact-batch">Batch 2023 | BSCS</p>
                                    <p class="contact-preview">You: Thank you, Mr!</p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="avatar-placeholder">T</div>
                                <div class="contact-info">
                                    <h4 class="contact-name">Asada, Timothy Jan</h4>
                                    <p class="contact-batch">Batch 2025 | BSN</p>
                                    <p class="contact-preview">You: Thank you, Mr!</p>
                                </div>
                            </div>

                            <div class="contact-item">
                                <div class="avatar-placeholder">J</div>
                                <div class="contact-info">
                                    <h4 class="contact-name">Caponpon, Jade Ahrenz</h4>
                                    <p class="contact-batch">Batch 2026 | BSBA-MM</p>
                                    <p class="contact-preview">I will be attending, thank you!</p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</body>
</html>