<!-- ============================================ -->
<!-- TAB 2: USER ANALYTICS -->
<!-- ============================================ -->
<div id="tab-users" class="tab-content">
    <section class="dashboard-section">
        <div class="section-header">
            <h2 class="section-title">
                <i class="fa-solid fa-users-gear"></i>
                User Analytics
            </h2>
            <div class="section-actions">
                <button class="btn btn-sm btn-export" onclick="exportData('users', 'pdf')">
                    <i class="fa-solid fa-file-pdf"></i> PDF
                </button>
                <button class="btn btn-sm btn-export" onclick="exportData('users', 'excel')">
                    <i class="fa-solid fa-file-excel"></i> Excel
                </button>
            </div>
        </div>

        <!-- Alumni Location Map -->
        <div class="dash-card mb-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-map-location-dot"></i>
                    Alumni Location Map
                </h3>
                <div class="card-actions">
                    <span class="badge-count">{{ isset($alumniLocations) ? $alumniLocations->count() : 0 }} Alumni Located</span>
                </div>
            </div>
            <div id="alumniMap" style="height: 400px; width: 100%; border-radius: var(--radius-lg);"></div>
            <div style="margin-top: 0.75rem; font-size: 0.8rem; color: var(--gray-500); text-align: center;">
                <i class="fa-solid fa-info-circle"></i> 
                Hover over markers to see alumni names. Click for more details.
            </div>
        </div>

        <!-- ============================== -->
        <!-- ENGAGEMENT KPI CARDS -->
        <!-- ============================== -->
        <div class="stats-grid mt-20">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #10B981;">
                        <i class="fa-solid fa-user-clock"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($activity['activeLast7Days']) }}</span>
                    <span class="stat-label">Active (Last 7 Days)</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #3B82F6;">
                        <i class="fa-solid fa-circle" style="font-size: 0.75rem;"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($activity['onlineNow']) }}</span>
                    <span class="stat-label">Online Now</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #F59E0B;">
                        <i class="fa-solid fa-user-slash"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($activity['dormantCount']) }}</span>
                    <span class="stat-label">Dormant (30+ Days)</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #8B5CF6;">
                        <i class="fa-solid fa-chart-simple"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ $activity['avgPostsPerUser'] }}</span>
                    <span class="stat-label">Avg Posts / User</span>
                </div>
            </div>
        </div>

        <!-- ============================== -->
        <!-- ACTIVITY OVERVIEW + BREAKDOWN -->
        <!-- ============================== -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-gauge-high"></i>
                        Activity Overview (Last 30 Days)
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="activityOverviewChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-chart-pie"></i>
                        Unique Users by Activity (Last 30 Days)
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="activityBreakdownChart"></canvas>
                </div>
            </div>
        </div>

        <!-- ============================== -->
        <!-- RETENTION + TOP CONTRIBUTORS -->
        <!-- ============================== -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        Monthly Active Users (6 Months)
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="retentionChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-trophy"></i>
                        Top Contributors (Last 30 Days)
                    </h3>
                </div>
                <div class="list-wrapper" style="max-height: 320px; overflow-y: auto;">
                    @forelse($activity['topContributors'] as $tc)
                    <div class="violator-item" style="padding: 0.75rem 1rem;">
                        <div class="violator-avatar">
                            @if($tc->alumni_photo)
                                <img src="{{ $tc->alumni_photo }}" alt="{{ $tc->first_name }}" class="violator-photo">
                            @else
                                <div class="violator-initials">
                                    {{ strtoupper(substr($tc->first_name, 0, 1) . substr($tc->last_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="violator-info">
                            <div class="violator-name">
                                {{ $tc->first_name }} {{ $tc->last_name }}
                                <span class="violator-badge" style="background: #32418C; color: white;">
                                    Score: {{ $tc->activity_score }}
                                </span>
                            </div>
                            <div class="violator-details">
                                <span class="violator-meta"><i class="fa-solid fa-file-lines"></i> {{ $tc->post_count }} posts</span>
                                <span class="violator-meta"><i class="fa-solid fa-comment"></i> {{ $tc->comment_count }} comments</span>
                                <span class="violator-meta"><i class="fa-solid fa-heart"></i> {{ $tc->reaction_count }} reactions</span>
                            </div>
                        </div>
                        <div class="violator-actions">
                            <a href="/admin/alumni/{{ $tc->id }}/view" class="btn-action btn-view" data-tooltip="View Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <p>No contributor activity yet</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ============================== -->
        <!-- PROGRAM ACTIVITY + DORMANT USERS -->
        <!-- ============================== -->
        <div class="two-col-grid mt-20">
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Avg Activity per User by Program
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="programsActivityChart"></canvas>
                </div>
            </div>

            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-moon"></i>
                        Dormant Users (Top 10)
                        <span class="badge-count">{{ $activity['dormantCount'] }}</span>
                    </h3>
                </div>
                <div class="list-wrapper" style="max-height: 320px; overflow-y: auto;">
                    @forelse($activity['dormantUsers'] as $du)
                    <div class="violator-item" style="padding: 0.75rem 1rem;">
                        <div class="violator-avatar">
                            @if($du->alumni_photo)
                                <img src="{{ $du->alumni_photo }}" alt="{{ $du->first_name }}" class="violator-photo">
                            @else
                                <div class="violator-initials">
                                    {{ strtoupper(substr($du->first_name, 0, 1) . substr($du->last_name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                        <div class="violator-info">
                            <div class="violator-name">{{ $du->first_name }} {{ $du->last_name }}</div>
                            <div class="violator-details">
                                <span class="violator-meta">
                                    <i class="fa-regular fa-clock"></i>
                                    Last login: {{ $du->last_login_at ? \Carbon\Carbon::parse($du->last_login_at)->diffForHumans() : 'Never' }}
                                </span>
                            </div>
                        </div>
                        <div class="violator-actions">
                            <a href="/admin/alumni/{{ $du->id }}/view" class="btn-action btn-view" data-tooltip="View Profile">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </div>
                    </div>
                    @empty
                    <div class="empty-state">
                        <p>No dormant users 🎉</p>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- ============================== -->
        <!-- CONNECTION DENSITY -->
        <!-- ============================== -->
        <div class="stats-grid mt-20">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #EC4899;">
                        <i class="fa-solid fa-user-group"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($activity['usersWithFollowers']) }}</span>
                    <span class="stat-label">Users With Followers</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon" style="background: #14B8A6;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($activity['usersFollowing']) }}</span>
                    <span class="stat-label">Users Following Others</span>
                </div>
            </div>
        </div>

        <!-- User Stats -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon verified">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($verifiedAlumniCount) }}</span>
                    <span class="stat-label">Verified Alumni</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon-wrapper">
                    <div class="stat-icon active">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                </div>
                <div class="stat-info">
                    <span class="stat-value">{{ number_format($userGrowth->sum('count')) }}</span>
                    <span class="stat-label">New Users (12 months)</span>
                </div>
            </div>
        </div>

        <!-- User Growth Chart -->
        <div class="chart-card mt-20">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fa-solid fa-chart-line"></i>
                    User Growth (Last 12 Months)
                </h3>
            </div>
            <div class="chart-container-wrapper">
                <div class="chart-container">
                    <canvas id="userGrowthChart"></canvas>
                </div>
            </div>
        </div>

        <div class="two-col-grid mt-20">
            <!-- Alumni by Program -->
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-graduation-cap"></i>
                        Alumni by Program
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="programChart"></canvas>
                </div>
            </div>

            <!-- Alumni by Year -->
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-calendar"></i>
                        Alumni by Year Graduated
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="yearChart"></canvas>
                </div>
            </div>
        </div>

        <div class="two-col-grid mt-20">
            <!-- Alumni by Type -->
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-layer-group"></i>
                        Alumni by Type
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="typeChart"></canvas>
                </div>
            </div>

            <!-- Alumni by Region -->
            <div class="dash-card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fa-solid fa-location-dot"></i>
                        Alumni by Region
                    </h3>
                </div>
                <div class="chart-container">
                    <canvas id="regionChart"></canvas>
                </div>
            </div>
        </div>
    </section>
</div>

<!-- USERS TAB JAVASCRIPT -->
<script>

// Users tab specific - Map and Chart initialization
let alumniMap = null;
let mapInitialized = false;
let mapInitAttempts = 0;
const MAX_MAP_ATTEMPTS = 10;

function isElementVisible(el) {
    if (!el) return false;
    const rect = el.getBoundingClientRect();
    const style = window.getComputedStyle(el);
    return style.display !== 'none' &&
           style.visibility !== 'hidden' &&
           rect.width > 0 &&
           rect.height > 0;
}

function initAlumniMap() {
    const mapContainer = document.getElementById('alumniMap');
    if (!mapContainer) {
        console.warn('Map container not found');
        return;
    }

    // ✅ Already initialized → just fix its size and return
    if (mapInitialized && alumniMap) {
        setTimeout(() => {
            try { alumniMap.invalidateSize(); } catch(e) {}
        }, 100);
        return;
    }

    // ✅ Not visible yet? Retry instead of silently giving up
    if (!isElementVisible(mapContainer)) {
        if (mapInitAttempts < MAX_MAP_ATTEMPTS) {
            mapInitAttempts++;
            console.log(`Map not visible yet, retry ${mapInitAttempts}/${MAX_MAP_ATTEMPTS}`);
            setTimeout(initAlumniMap, 200);
        } else {
            console.warn('Map gave up after max attempts');
        }
        return;
    }

    // Reset counter now that we're actually initializing
    mapInitAttempts = 0;
    mapContainer.innerHTML = '';

    try {
        let alumniLocations = @json($alumniLocations ?? []);

        if (typeof alumniLocations === 'string') {
            alumniLocations = JSON.parse(alumniLocations);
        }
        if (!Array.isArray(alumniLocations)) {
            alumniLocations = [];
        }

        console.log('Parsed alumni locations:', alumniLocations);

        if (!alumniLocations || alumniLocations.length === 0) {
            mapContainer.innerHTML = `
                <div class="map-loading">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 3rem; color: var(--gray-400);"></i>
                    <p style="color: var(--gray-500); font-weight: 500;">No alumni location data available</p>
                    <p style="color: var(--gray-400); font-size: 0.875rem; max-width: 300px; text-align: center;">
                        Alumni need to add their address information with valid coordinates
                    </p>
                </div>
            `;
            return;
        }

        const validLocations = alumniLocations.filter((alumni) => {
            const lat = parseFloat(alumni.latitude);
            const lng = parseFloat(alumni.longitude);
            return !isNaN(lat) && !isNaN(lng) &&
                   lat !== 0 && lng !== 0 &&
                   lat >= -90 && lat <= 90 &&
                   lng >= -180 && lng <= 180 &&
                   alumni.latitude !== null &&
                   alumni.longitude !== null &&
                   alumni.latitude !== '' &&
                   alumni.longitude !== '';
        });

        console.log('Valid locations count:', validLocations.length);

        if (validLocations.length === 0) {
            mapContainer.innerHTML = `
                <div class="map-loading">
                    <i class="fa-solid fa-map-location-dot" style="font-size: 3rem; color: var(--gray-400);"></i>
                    <p style="color: var(--gray-500); font-weight: 500;">No valid location data available</p>
                    <p style="color: var(--gray-400); font-size: 0.875rem; max-width: 300px; text-align: center;">
                        Please check that alumni have valid coordinates (latitude and longitude)
                    </p>
                </div>
            `;
            return;
        }

        mapContainer.style.height = '400px';
        mapContainer.style.width = '100%';

        // NU Lipa (SM City Lipa) — 13.9432, 121.1626
        const NU_LIPA_CENTER = [13.9432, 121.1626];
        const NU_LIPA_ZOOM   = 9;   // 9 = Batangas province visible; 8 = most of Luzon

        alumniMap = L.map('alumniMap', {
            center: NU_LIPA_CENTER,
            zoom: NU_LIPA_ZOOM,
            minZoom: 2,
            worldCopyJump: true,
            maxBoundsViscosity: 1.0
        });

        // OpenStreetMap public tiles — free, no API key required
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
            noWrap: false
        }).addTo(alumniMap);

                // ============================================
        // 📍 NU LIPA CAMPUS MARKER (always on top of map)
        // ============================================
        const NU_LIPA_LAT  = 13.9554;
        const NU_LIPA_LNG  = 121.1620;

        const nuLipaIcon = L.divIcon({
            className: 'nu-lipa-marker',
            html: `
                <div style="
                    position: relative;
                    width: 44px;
                    height: 44px;
                ">
                    <!-- Outer ring -->
                    <div style="
                        position: absolute;
                        inset: 0;
                        border-radius: 50%;
                        background: #FBD117;
                        border: 3px solid #32418C;
                        box-shadow: 0 4px 14px rgba(50, 65, 140, 0.35);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        overflow: hidden;
                    ">
                        <img
                            src="/assets/logos/nu_logo_blue.png"
                            alt="NU Lipa"
                            style="
                                width: 26px;
                                height: 26px;
                                object-fit: contain;
                                display: block;
                            "
                        />
                    </div>
                    <!-- Pulse ring -->
                    <div style="
                        position: absolute;
                        inset: -6px;
                        border-radius: 50%;
                        border: 2px solid rgba(50, 65, 140, 0.35);
                        animation: nuPulse 2.2s ease-out infinite;
                        pointer-events: none;
                    "></div>
                </div>
            `,
            iconSize: [44, 44],
            iconAnchor: [22, 22],
            popupAnchor: [0, -22],
            zIndexOffset: -1000,   // keeps alumni pins clickable on top
        });

        const nuLipaMarker = L.marker([NU_LIPA_LAT, NU_LIPA_LNG], {
            icon: nuLipaIcon,
            interactive: true,
            keyboard: false,
            riseOnHover: true,
        }).addTo(alumniMap);

        nuLipaMarker.bindPopup(`
            <div style="
                padding: 10px 6px 8px;
                font-family: 'Poppins', sans-serif;
                text-align: center;
                min-width: 190px;
            ">
                <div style="
                    display: inline-flex;
                    align-items: center;
                    justify-content: center;
                    width: 44px;
                    height: 44px;
                    border-radius: 50%;
                    background: #32418C;
                    margin-bottom: 8px;
                ">
                    <img
                        src="/assets/logos/nu_logo_white.png"
                        alt="NU Lipa"
                        style="width: 26px; height: 26px; object-fit: contain;"
                    />
                </div>
                <strong style="
                    display: block;
                    color: #32418C;
                    font-size: 13.5px;
                    margin-bottom: 3px;
                    font-weight: 700;
                ">
                    National University Lipa
                </strong>
                <div style="
                    font-size: 11.5px;
                    color: #555;
                    line-height: 1.4;
                ">
                    SM City Lipa<br>
                    Lipa City, Batangas, Philippines
                </div>
            </div>
        `, {
            maxWidth: 260,
            minWidth: 200,
            closeButton: true,
            autoClose: true,
        });

        // ============================================
        // 🎓 ALUMNI MARKER ICON (existing)
        // ============================================

        const alumniIcon = L.divIcon({
            className: '',
            html: `<div style="
                background: #32418C;
                width: 30px;
                height: 30px;
                border-radius: 50%;
                border: 3px solid #FBD117;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                cursor: pointer;
                box-shadow: 0 2px 10px rgba(50,65,140,0.3);
                margin: 0 !important;
                padding: 0 !important;
            ">
                <i class="fa-solid fa-user" style="font-size: 12px; margin: 0; padding: 0;"></i>
            </div>`,
            iconSize: [30, 30],
            iconAnchor: [15, 15],
            popupAnchor: [0, -15]
        });

        const markerGroup = L.markerClusterGroup({
            spiderfyOnMaxZoom: true,
            showCoverageOnHover: false,
            zoomToBoundsOnClick: true
        }).addTo(alumniMap);

        let addedCount = 0;

        validLocations.forEach((alumni) => {
            const lat = parseFloat(alumni.latitude);
            const lng = parseFloat(alumni.longitude);

            if (isNaN(lat) || isNaN(lng)) return;

            addedCount++;

            const popupContent = `
                <div style="padding: 12px; min-width: 220px;">
                    <div style="display: flex; align-items: center; margin-bottom: 10px;">
                        ${alumni.alumni_photo ?
                            `<img src="${alumni.alumni_photo}" style="width: 40px; height: 40px; border-radius: 50%; margin-right: 10px; object-fit: cover;">` :
                            `<div style="width: 40px; height: 40px; border-radius: 50%; background: #32418C; color: white; display: flex; align-items: center; justify-content: center; margin-right: 10px; font-weight: bold;">
                                ${alumni.first_name.charAt(0)}${alumni.last_name.charAt(0)}
                            </div>`
                        }
                        <div>
                            <strong style="display: block; font-size: 14px;">${alumni.first_name} ${alumni.last_name}</strong>
                            <small style="color: #666; font-size: 12px;">
                                <i class="fa-solid fa-location-dot"></i>
                                ${alumni.region || 'Location not specified'}
                            </small>
                        </div>
                    </div>
                    ${alumni.province || alumni.municipality || alumni.barangay ? `
                        <div style="font-size: 12px; color: #555; margin-bottom: 8px; padding: 4px 8px; background: #f5f5f5; border-radius: 4px;">
                            ${[alumni.barangay, alumni.municipality, alumni.province].filter(Boolean).join(', ')}
                        </div>
                    ` : ''}
                    <div style="display: flex; gap: 8px; border-top: 1px solid #eee; padding-top: 8px;">
                        <a href="/admin/alumni/${alumni.id}/view" style="flex: 1; text-align: center; padding: 6px; background: #32418C; color: white; text-decoration: none; border-radius: 4px; font-size: 12px;">
                            <i class="fa-solid fa-eye"></i> View
                        </a>
                        <a href="/admin/messages?chat=${alumni.id}" style="flex: 1; text-align: center; padding: 6px; background: #FBD117; color: #32418C; text-decoration: none; border-radius: 4px; font-size: 12px;">
                            <i class="fa-solid fa-comment"></i> Message
                        </a>
                    </div>
                </div>
            `;

            const marker = L.marker([lat, lng], {
                icon: alumniIcon,
                interactive: true
            });

            marker.bindPopup(popupContent, {
                maxWidth: 280,
                minWidth: 220,
                closeButton: true,
                autoClose: true,
                closeOnClick: true
            });

            markerGroup.addLayer(marker);
        });

        console.log(`Added ${addedCount} markers to map`);

        mapInitialized = true;

        // ✅ Multiple invalidateSize calls — covers slow tab transitions
        setTimeout(() => { try { alumniMap && alumniMap.invalidateSize(); } catch(e) {} }, 250);
        setTimeout(() => { try { alumniMap && alumniMap.invalidateSize(); } catch(e) {} }, 800);

    } catch (error) {
        console.error('Error initializing map:', error);
        mapInitialized = false;

        // Cleanup half-built map so retry doesn't hit "already initialized" error
        if (alumniMap) {
            try { alumniMap.remove(); } catch(e) {}
            alumniMap = null;
        }

        mapContainer.innerHTML = `
            <div class="map-loading">
                <i class="fa-solid fa-triangle-exclamation" style="font-size: 3rem; color: red;"></i>
                <p>Error loading map: ${error.message}</p>
            </div>
        `;

        if (mapInitAttempts < MAX_MAP_ATTEMPTS) {
            mapInitAttempts++;
            setTimeout(initAlumniMap, 500);
        }
    }
}

function initUsersCharts() {
    const chartData = @json($chartData);
    let charts = window._charts || {};

    // Guard: don't re-create charts that already exist
    const destroyIfExists = (key) => {
        if (charts[key]) {
            try { charts[key].destroy(); } catch(e) {}
            delete charts[key];
        }
    };

    // User Growth Chart
    const userGrowthCtx = document.getElementById('userGrowthChart');
    if (userGrowthCtx && chartData.user_growth.labels.length > 0) {
        destroyIfExists('userGrowth');
        charts.userGrowth = new Chart(userGrowthCtx, {
            type: 'line',
            data: {
                labels: chartData.user_growth.labels,
                datasets: [{
                    label: 'New Alumni',
                    data: chartData.user_growth.data,
                    borderColor: '#32418C',
                    backgroundColor: 'rgba(50, 65, 140, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#32418C',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Program Chart
    const programCtx = document.getElementById('programChart');
    if (programCtx && chartData.alumni_by_program.labels.length > 0) {
        destroyIfExists('program');   
        charts.program = new Chart(programCtx, {
            type: 'bar',
            data: {
                labels: chartData.alumni_by_program.labels,
                datasets: [{
                    label: 'Alumni',
                    data: chartData.alumni_by_program.data,
                    backgroundColor: 'rgba(50, 65, 140, 0.7)',
                    borderColor: '#32418C',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Year Chart
    const yearCtx = document.getElementById('yearChart');
    if (yearCtx && chartData.alumni_by_year.labels.length > 0) {
        destroyIfExists('year');   
        charts.year = new Chart(yearCtx, {
            type: 'bar',
            data: {
                labels: chartData.alumni_by_year.labels,
                datasets: [{
                    label: 'Alumni',
                    data: chartData.alumni_by_year.data,
                    backgroundColor: 'rgba(251, 209, 23, 0.7)',
                    borderColor: '#FBD117',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Type Chart
    const typeCtx = document.getElementById('typeChart');
    if (typeCtx && chartData.alumni_by_type.labels.length > 0) {
        destroyIfExists('type');
        charts.type = new Chart(typeCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.alumni_by_type.labels.map(l => l === 'shs' ? 'SHS' : 'College'),
                datasets: [{
                    data: chartData.alumni_by_type.data,
                    backgroundColor: ['#32418C', '#FBD117'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 11 } } }
                }
            }
        });
    }

    // Region Chart
    const regionCtx = document.getElementById('regionChart');
    if (regionCtx && chartData.alumni_by_region.labels.length > 0) {
        destroyIfExists('region');
        charts.region = new Chart(regionCtx, {
            type: 'bar',
            data: {
                labels: chartData.alumni_by_region.labels,
                datasets: [{
                    label: 'Alumni',
                    data: chartData.alumni_by_region.data,
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: '#10B981',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // In initUsersCharts() - Activity Overview section
    const activityOverviewCtx = document.getElementById('activityOverviewChart');
    if (activityOverviewCtx && chartData.activity_overview?.labels?.length > 0) {
        destroyIfExists('activityOverview');
        
        // Check if all data is zero
        const hasData = chartData.activity_overview.data.some(v => v > 0);
        
        charts.activityOverview = new Chart(activityOverviewCtx, {
            type: 'bar',
            data: {
                labels: chartData.activity_overview.labels,
                datasets: [{
                    label: 'Count',
                    data: chartData.activity_overview.data,
                    backgroundColor: hasData ? 'rgba(50, 65, 140, 0.75)' : 'rgba(200, 200, 200, 0.5)',
                    borderColor: hasData ? '#32418C' : '#ccc',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { 
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' total';
                            }
                        }
                    }
                },
                scales: { 
                    y: { 
                        beginAtZero: true, 
                        ticks: { stepSize: 1 },
                        title: {
                            display: true,
                            text: hasData ? '' : 'No activity in the last 30 days',
                            color: '#999'
                        }
                    } 
                }
            }
        });
    }

    // Activity Breakdown (unique users)
    const activityBreakdownCtx = document.getElementById('activityBreakdownChart');
    if (activityBreakdownCtx && chartData.activity_breakdown?.labels?.length > 0) {
        destroyIfExists('activityBreakdown');
        charts.activityBreakdown = new Chart(activityBreakdownCtx, {
            type: 'doughnut',
            data: {
                labels: chartData.activity_breakdown.labels,
                datasets: [{
                    data: chartData.activity_breakdown.data,
                    backgroundColor: ['#32418C', '#FBD117', '#10B981', '#3B82F6', '#EF4444'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom', labels: { font: { size: 11 } } } }
            }
        });
    }

    // Retention / MAU
    const retentionCtx = document.getElementById('retentionChart');
    if (retentionCtx && chartData.retention?.labels?.length > 0) {
        destroyIfExists('retention');
        charts.retention = new Chart(retentionCtx, {
            type: 'line',
            data: {
                labels: chartData.retention.labels,
                datasets: [{
                    label: 'Monthly Active Users',
                    data: chartData.retention.data,
                    borderColor: '#8B5CF6',
                    backgroundColor: 'rgba(139, 92, 246, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#8B5CF6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    }

    // Program activity per user
    const programsActivityCtx = document.getElementById('programsActivityChart');
    if (programsActivityCtx && chartData.programs_activity?.labels?.length > 0) {
        destroyIfExists('programsActivity');
        charts.programsActivity = new Chart(programsActivityCtx, {
            type: 'bar',
            data: {
                labels: chartData.programs_activity.labels,
                datasets: [{
                    label: 'Activity per User',
                    data: chartData.programs_activity.data,
                    backgroundColor: 'rgba(251, 209, 23, 0.75)',
                    borderColor: '#FBD117',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
    }

    window._charts = charts;
    return charts;
}
</script>

<!-- ============================================ -->
<!-- USER ANALYTICS — CLEANED UP STYLES -->
<!-- ============================================ -->
<style>
/* ============================================
   BASE — scoped to #tab-users only
   ============================================ */

/* Ensure consistent spacing between blocks */
#tab-users .dashboard-section > * + * {
    margin-top: 1rem;
}

#tab-users .dash-card,
#tab-users .chart-card {
    background: #fff;
    border-radius: 14px;
    border: 1px solid var(--gray-100, #f1f2f6);
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    overflow: hidden;
    /* CRITICAL: allow this card to shrink inside a grid */
    min-width: 0;
    max-width: 100%;
}

#tab-users .card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.25rem;
    background: linear-gradient(180deg, #fafbff 0%, #ffffff 100%);
    border-bottom: 1px solid var(--gray-100, #eef0f6);
    border-radius: 14px 14px 0 0;
    flex-wrap: wrap;
    min-width: 0;
}

#tab-users .card-title {
    display: flex;
    align-items: center;
    gap: 0.55rem;
    font-size: 0.95rem;
    font-weight: 600;
    color: var(--gray-800, #1f2937);
    margin: 0;
    min-width: 0;
    word-break: break-word;
}

#tab-users .card-title i {
    color: #32418C;
    flex-shrink: 0;
}

#tab-users .badge-count {
    background: #eef1fb;
    color: #32418C;
    padding: 0.2rem 0.7rem;
    border-radius: 999px;
    font-size: 0.72rem;
    font-weight: 600;
    flex-shrink: 0;
}

/* ============================================
   ⭐ CRITICAL: Make sure the two-column grid collapses properly
   ============================================ */

/* Neutralize any inherited grid-template-columns on this tab */
#tab-users .two-col-grid {
    display: grid !important;
    grid-template-columns: 1fr 1fr !important;
    gap: 1rem;
    width: 100%;
    min-width: 0;
}

/* Grid items MUST be allowed to shrink below content size */
#tab-users .two-col-grid > * {
    min-width: 0 !important;
    max-width: 100% !important;
    overflow: hidden;
}

#tab-users .stats-grid {
    display: grid !important;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)) !important;
    gap: 1rem;
    width: 100%;
    min-width: 0;
}

#tab-users .stats-grid > * {
    min-width: 0 !important;
}

/* ============================================
   KPI STAT CARDS
   ============================================ */
#tab-users .stat-card {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1.1rem 1.25rem;
    background: #fff;
    border: 1px solid var(--gray-100, #eef0f6);
    border-radius: 14px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.04);
    transition: transform 0.15s ease, box-shadow 0.15s ease;
    min-width: 0;
    overflow: hidden;
}

#tab-users .stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 18px rgba(50, 65, 140, 0.08);
}

#tab-users .stat-icon-wrapper {
    flex-shrink: 0;
}

#tab-users .stat-icon {
    width: 46px;
    height: 46px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.05rem;
    color: #fff;
    background: #32418C;
    box-shadow: 0 2px 8px rgba(50, 65, 140, 0.18);
    flex-shrink: 0;
}

#tab-users .stat-info {
    display: flex;
    flex-direction: column;
    min-width: 0;
    flex: 1;
}

#tab-users .stat-value {
    font-size: 1.35rem;
    font-weight: 700;
    color: var(--gray-900, #111827);
    line-height: 1.15;
    word-break: break-word;
}

#tab-users .stat-label {
    font-size: 0.78rem;
    color: var(--gray-500, #6b7280);
    font-weight: 500;
    word-break: break-word;
}

/* ============================================
   ⭐ CRITICAL: Chart containers must NOT overflow
   ============================================ */
#tab-users .chart-container {
    padding: 1rem 1.25rem 1.25rem;
    height: 280px;
    position: relative;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    box-sizing: border-box;
}

#tab-users .chart-container-wrapper {
    padding: 1rem 1.25rem 1.25rem;
    height: 320px;
    position: relative;
    width: 100%;
    max-width: 100%;
    overflow: hidden;
    box-sizing: border-box;
}

#tab-users .chart-container canvas,
#tab-users .chart-container-wrapper canvas {
    max-width: 100% !important;
    max-height: 100% !important;
    display: block;
}

/* ============================================
   TOP CONTRIBUTORS / DORMANT USERS LISTS
   ============================================ */
#tab-users .list-wrapper {
    padding: 0.5rem;
    max-height: 340px;
    overflow-y: auto;
    overflow-x: hidden;
    position: relative;
    width: 100%;
    box-sizing: border-box;
}

#tab-users .list-wrapper::-webkit-scrollbar {
    width: 6px;
}
#tab-users .list-wrapper::-webkit-scrollbar-thumb {
    background: #d5dae7;
    border-radius: 999px;
}
#tab-users .list-wrapper::-webkit-scrollbar-thumb:hover {
    background: #b8c0d4;
}

#tab-users .violator-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0.9rem !important;
    margin: 0 0.25rem 0.5rem;
    background: #fff;
    border: 1px solid var(--gray-100, #eef0f6);
    border-radius: 10px;
    transition: background 0.15s ease, border-color 0.15s ease;
    cursor: default;
    position: relative;
    overflow: visible;
    min-width: 0;
}

#tab-users .violator-item:hover {
    background: #f8f9ff;
    border-color: #dde3f5;
}

#tab-users .violator-avatar {
    width: 42px;
    height: 42px;
    border-radius: 50%;
    overflow: hidden;
    flex-shrink: 0;
    background: #eef1fb;
    display: flex;
    align-items: center;
    justify-content: center;
}

#tab-users .violator-photo {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

#tab-users .violator-initials {
    font-weight: 700;
    color: #32418C;
    font-size: 0.85rem;
}

#tab-users .violator-info {
    flex: 1;
    min-width: 0;
}

#tab-users .violator-name {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: var(--gray-800, #1f2937);
    font-size: 0.85rem;
    margin-bottom: 0.2rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}

#tab-users .violator-badge {
    font-size: 0.65rem;
    font-weight: 600;
    padding: 0.15rem 0.5rem;
    border-radius: 999px;
    background: #32418C;
    color: #fff;
    flex-shrink: 0;
}

#tab-users .violator-details {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem 0.9rem;
    font-size: 0.72rem;
    color: var(--gray-500, #6b7280);
    min-width: 0;
}

#tab-users .violator-meta {
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}

#tab-users .violator-meta i {
    color: #9aa3bd;
    font-size: 0.68rem;
}

#tab-users .violator-actions {
    display: flex;
    gap: 0.35rem;
    flex-shrink: 0;
    position: relative;
    overflow: visible;
    z-index: 20;
}

#tab-users .btn-action {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    background: #eef1fb;
    color: #32418C;
    cursor: pointer;
    font-size: 0.8rem;
    transition: background 0.15s ease, transform 0.1s ease;
    text-decoration: none;
    position: relative;
    overflow: visible !important;
    flex-shrink: 0;
}

#tab-users .btn-action:hover {
    background: #dde3f5;
    transform: scale(1.05);
}

#tab-users .empty-state {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--gray-500, #6b7280);
    font-size: 0.85rem;
}

/* ============================================
   CUSTOM TOOLTIPS
   ============================================ */
#tab-users .btn-action[data-tooltip]::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: calc(100% + 8px);
    left: 50%;
    transform: translateX(-50%) translateY(4px);
    background: #1f2937;
    color: #fff;
    font-family: 'Poppins', sans-serif;
    font-size: 0.68rem;
    font-weight: 500;
    padding: 0.35rem 0.65rem;
    border-radius: 6px;
    white-space: nowrap;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s ease, transform 0.15s ease;
    z-index: 9999;
}

#tab-users .btn-action[data-tooltip]::before {
    content: '';
    position: absolute;
    bottom: calc(100% + 3px);
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: #1f2937;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.15s ease;
    z-index: 9999;
}

#tab-users .btn-action[data-tooltip]:hover::after {
    opacity: 1;
    transform: translateX(-50%) translateY(0);
}

#tab-users .btn-action[data-tooltip]:hover::before {
    opacity: 1;
}

/* Tooltip flip for the topmost list item */
#tab-users .violator-item:first-child .btn-action[data-tooltip]::after {
    bottom: auto;
    top: calc(100% + 8px);
}

#tab-users .violator-item:first-child .btn-action[data-tooltip]::before {
    bottom: auto;
    top: calc(100% + 3px);
    border-top-color: transparent;
    border-bottom-color: #1f2937;
}

/* ============================================
   MAP
   ============================================ */
#tab-users #alumniMap {
    height: 400px;
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    border: 1px solid var(--gray-100, #eef0f6);
    box-sizing: border-box;
}

#tab-users .leaflet-container {
    font-family: 'Poppins', sans-serif;
}

/* ============================================
   NU LIPA PULSE ANIMATION
   ============================================ */
@keyframes nuPulse {
    0%   { transform: scale(0.85); opacity: 0.9; }
    70%  { transform: scale(1.4);  opacity: 0; }
    100% { transform: scale(1.4);  opacity: 0; }
}

.nu-lipa-marker {
    background: transparent !important;
    border: none !important;
}

/* ============================================
   ⭐ RESPONSIVE BREAKPOINTS
   ============================================ */

/* ---------- TABLET / SMALL LAPTOP (≤1024px) ---------- */
@media (max-width: 1024px) {
    #tab-users .two-col-grid {
        grid-template-columns: 1fr !important;
        gap: 1rem;
    }

    #tab-users .stats-grid {
        grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)) !important;
        gap: 0.85rem;
    }

    #tab-users .section-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.75rem;
    }

    #tab-users .section-actions {
        width: 100%;
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
}

/* ---------- MOBILE (≤768px) ---------- */
@media (max-width: 768px) {
    #tab-users .section-title {
        font-size: 1.15rem;
    }

    #tab-users .section-header {
        padding-bottom: 0.5rem;
    }

    /* KPI cards — 2 per row */
    #tab-users .stats-grid {
        grid-template-columns: repeat(2, 1fr) !important;
        gap: 0.65rem;
    }

    #tab-users .stat-card {
        padding: 0.8rem 0.9rem;
        gap: 0.65rem;
        flex-direction: column;
        align-items: flex-start;
    }

    #tab-users .stat-icon {
        width: 38px;
        height: 38px;
        font-size: 0.9rem;
        border-radius: 10px;
    }

    #tab-users .stat-value {
        font-size: 1.05rem;
    }

    #tab-users .stat-label {
        font-size: 0.68rem;
        line-height: 1.25;
    }

    /* Charts */
    #tab-users .chart-container {
        height: 220px;
        padding: 0.75rem 0.85rem 0.9rem;
    }

    #tab-users .chart-container-wrapper {
        height: 260px;
        padding: 0.75rem 0.85rem 0.9rem;
    }

    /* Card headers */
    #tab-users .card-header {
        padding: 0.75rem 0.9rem;
        gap: 0.5rem;
    }

    #tab-users .card-title {
        font-size: 0.85rem;
    }

    #tab-users .badge-count {
        font-size: 0.65rem;
        padding: 0.15rem 0.55rem;
    }

    /* Lists */
    #tab-users .list-wrapper {
        max-height: 280px !important;
        padding: 0.35rem;
    }

    #tab-users .violator-item {
        padding: 0.6rem 0.7rem !important;
        gap: 0.55rem;
        margin-bottom: 0.4rem;
    }

    #tab-users .violator-avatar {
        width: 36px;
        height: 36px;
    }

    #tab-users .violator-name {
        font-size: 0.78rem;
    }

    #tab-users .violator-details {
        font-size: 0.66rem;
        gap: 0.3rem 0.7rem;
    }

    #tab-users .violator-badge {
        font-size: 0.6rem;
        padding: 0.12rem 0.4rem;
    }

    #tab-users .btn-action {
        width: 28px;
        height: 28px;
        font-size: 0.72rem;
    }

    /* Map */
    #tab-users #alumniMap {
        height: 300px !important;
    }

    /* Export buttons full-width */
    #tab-users .section-actions .btn {
        flex: 1;
        justify-content: center;
    }
}

/* ---------- VERY SMALL PHONES (≤480px) ---------- */
@media (max-width: 480px) {
    #tab-users .stats-grid {
        grid-template-columns: 1fr !important;
    }

    #tab-users .stat-card {
        flex-direction: row;
        align-items: center;
        padding: 0.75rem 0.85rem;
    }

    #tab-users .stat-icon {
        width: 42px;
        height: 42px;
        font-size: 0.95rem;
    }

    #tab-users .stat-value {
        font-size: 1.1rem;
    }

    #tab-users .chart-container {
        height: 200px;
    }

    #tab-users .chart-container-wrapper {
        height: 220px;
    }

    #tab-users #alumniMap {
        height: 260px !important;
    }

    #tab-users .card-title {
        font-size: 0.8rem;
    }

    #tab-users .violator-details {
        flex-direction: column;
        gap: 0.2rem;
        align-items: flex-start;
    }
}
</style>