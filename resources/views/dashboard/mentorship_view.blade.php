<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentorship Application Details | LumiNUs Admin</title>

    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Stylesheets -->
    <link rel="stylesheet" href="/css/admin.css">
    <link rel="stylesheet" href="/css/dashboard_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <style>
        /* Page-specific styles */
        .mentorship-view-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .mentorship-profile-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: var(--gray-50);
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .mentorship-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #32418C;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            flex-shrink: 0;
            overflow: hidden;
        }

        .mentorship-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .mentorship-detail-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .mentorship-detail-label {
            font-weight: 600;
            font-size: 0.875rem;
            color: var(--gray-500);
            display: block;
            margin-bottom: 0.25rem;
        }

        .mentorship-detail-value {
            margin: 0;
            font-weight: 500;
        }

        .mentorship-detail-full {
            grid-column: 1 / -1;
        }

        .mentorship-description {
            background: var(--gray-50);
            padding: 1rem;
            border-radius: var(--radius);
            line-height: 1.6;
            margin: 0;
        }

        .attachment-box {
            padding: 1rem;
            background: #E0E7FF;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .attachment-left {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .attachment-left i {
            color: #4338CA;
            font-size: 1.25rem;
        }

        .mentorship-actions {
            display: flex;
            gap: 0.75rem;
            padding-top: 1.5rem;
            border-top: 2px solid var(--gray-100);
            flex-wrap: wrap;
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 1rem;
            font-weight: 500;
        }

        .status-pending {
            background: #FEF3C7;
            color: #D97706;
        }

        .status-active {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-completed {
            background: #F3F4F6;
            color: #6B7280;
        }

        .status-declined {
            background: #FEE2E2;
            color: #991B1B;
        }

        @media (max-width: 768px) {
            .mentorship-detail-grid {
                grid-template-columns: 1fr;
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
        @include('dashboard.partials._sidebar')

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Mobile Menu Toggle -->
            <button class="mobile-menu-toggle" id="mobileMenuToggle" onclick="toggleMobileMenu()">
                <i class="fa-solid fa-bars"></i>
            </button>

            <div class="mentorship-view-wrapper">
                <div class="dash-card">
                    <div class="card-header">
                        <h2 class="card-title" style="display: flex; align-items: center; gap: 0.75rem;">
                            <i class="fa-solid fa-handshake" style="color: #32418C;"></i>
                            Mentorship Application Details
                        </h2>
                        <div class="card-actions">
                            <a href="/admin/dashboard" class="btn btn-secondary btn-sm">
                                <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                            </a>
                        </div>
                    </div>

                    <div style="padding: 1.5rem;">
                        <!-- Alumni Info -->
                        <div class="mentorship-profile-header">
                            <div class="mentorship-avatar">
                                @if($mentorship->alumni && $mentorship->alumni->alumni_photo)
                                    <img src="{{ $mentorship->alumni->alumni_photo }}" alt="Profile">
                                @else
                                    {{ $mentorship->alumni ? strtoupper(substr($mentorship->alumni->first_name, 0, 1) . substr($mentorship->alumni->last_name, 0, 1)) : '?' }}
                                @endif
                            </div>
                            <div>
                                <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0;">
                                    {{ $mentorship->alumni ? $mentorship->alumni->first_name . ' ' . $mentorship->alumni->last_name : 'Unknown' }}
                                </h3>
                                <p style="margin: 0.25rem 0 0 0; color: var(--gray-500); font-size: 0.875rem;">
                                    <i class="fa-regular fa-envelope"></i> {{ $mentorship->alumni ? $mentorship->alumni->email : 'N/A' }}
                                </p>
                                <p style="margin: 0.25rem 0 0 0; color: var(--gray-500); font-size: 0.875rem;">
                                    <i class="fa-regular fa-calendar"></i> Applied: {{ $mentorship->created_at->format('F d, Y g:i A') }}
                                </p>
                            </div>
                            <div style="margin-left: auto;">
                                <span class="status-badge status-{{ $mentorship->status }}">
                                    <i class="fa-solid fa-{{ $mentorship->status === 'pending' ? 'clock' : ($mentorship->status === 'active' ? 'check-circle' : ($mentorship->status === 'completed' ? 'flag-checkered' : 'times-circle')) }}"></i>
                                    {{ ucfirst($mentorship->status) }}
                                </span>
                            </div>
                        </div>

                        <!-- Event Details -->
                        <div class="mentorship-detail-grid">
                            <div>
                                <label class="mentorship-detail-label">Event Type</label>
                                <p class="mentorship-detail-value">{{ ucfirst(str_replace('_', ' ', $mentorship->event_type ?? 'N/A')) }}</p>
                            </div>
                            <div>
                                <label class="mentorship-detail-label">Event Format</label>
                                <p class="mentorship-detail-value">{{ ucfirst($mentorship->event_format ?? 'N/A') }}</p>
                            </div>
                            <div class="mentorship-detail-full">
                                <label class="mentorship-detail-label">Event Title</label>
                                <p class="mentorship-detail-value" style="font-size: 1.125rem;">{{ $mentorship->event_title ?? 'N/A' }}</p>
                            </div>
                            <div class="mentorship-detail-full">
                                <label class="mentorship-detail-label">Description</label>
                                <p class="mentorship-description">{{ $mentorship->description ?? 'No description provided' }}</p>
                            </div>
                            <div>
                                <label class="mentorship-detail-label">Target Audience</label>
                                <p class="mentorship-detail-value">{{ $mentorship->target_audience ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="mentorship-detail-label">Expected Capacity</label>
                                <p class="mentorship-detail-value">{{ $mentorship->expected_capacity ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="mentorship-detail-label">Proposed Date</label>
                                <p class="mentorship-detail-value">{{ $mentorship->proposed_date ? \Carbon\Carbon::parse($mentorship->proposed_date)->format('F d, Y') : 'N/A' }}</p>
                            </div>
                            <div>
                                <label class="mentorship-detail-label">Time</label>
                                <p class="mentorship-detail-value">{{ $mentorship->start_time ?? 'N/A' }} - {{ $mentorship->end_time ?? 'N/A' }}</p>
                            </div>
                            <div class="mentorship-detail-full">
                                <label class="mentorship-detail-label">Co-speakers</label>
                                <p class="mentorship-detail-value">{{ $mentorship->co_speakers ?? 'None specified' }}</p>
                            </div>
                        </div>

                        <!-- Attachment -->
                        @if($mentorship->attached_file_url)
                        <div class="attachment-box">
                            <div class="attachment-left">
                                <i class="fa-solid fa-paperclip"></i>
                                <span style="font-weight: 500;">Attachment Available</span>
                            </div>
                            <a href="{{ $mentorship->attached_file_url }}" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-download"></i> Download
                            </a>
                        </div>
                        @endif

                        <!-- Actions -->
                        @if($mentorship->status === 'pending')
                        <div class="mentorship-actions">
                            <button onclick="updateStatus('{{ $mentorship->id }}', 'active')" class="btn btn-success">
                                <i class="fa-solid fa-check"></i> Approve Application
                            </button>
                            <button onclick="updateStatus('{{ $mentorship->id }}', 'declined')" class="btn btn-danger">
                                <i class="fa-solid fa-times"></i> Decline Application
                            </button>
                        </div>
                        @endif

                        @if($mentorship->status === 'active')
                        <div class="mentorship-actions">
                            <button onclick="updateStatus('{{ $mentorship->id }}', 'completed')" class="btn btn-primary">
                                <i class="fa-solid fa-flag-checkered"></i> Mark as Completed
                            </button>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- JavaScript -->
    <script>
        // Mobile menu toggle
        function toggleMobileMenu() {
            const sidebar = document.getElementById('adminSidebar');
            const overlay = document.getElementById('mobileOverlay');
            if (!sidebar || !overlay) return;
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
            document.body.style.overflow = sidebar.classList.contains('mobile-open') ? 'hidden' : '';
        }

        // Auto-close mobile menu when clicking a nav link
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                if (window.innerWidth <= 1024) toggleMobileMenu();
            });
        });

        // Auto-close sidebar on desktop resize
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (window.innerWidth > 1024) {
                    const sidebar = document.getElementById('adminSidebar');
                    const overlay = document.getElementById('mobileOverlay');
                    if (sidebar) sidebar.classList.remove('mobile-open');
                    if (overlay) overlay.classList.remove('active');
                    document.body.style.overflow = '';
                }
            }, 250);
        });

        function updateStatus(id, status) {
            const action = status === 'active' ? 'approve' : (status === 'completed' ? 'mark as completed' : 'decline');
            if (!confirm(`Are you sure you want to ${action} this mentorship application?`)) return;
            
            fetch('/admin/mentorships/' + id + '/status', {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ status: status })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                alert('An error occurred. Please try again.');
            });
        }
    </script>
</body>
</html>