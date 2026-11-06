<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>You're Invited — {{ $event->title }}</title>

    <!-- Import Poppins from Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        /* Base Styles & Typography Reset */
        body, table, td, p, h1, h2, h3, h4, h5, h6, span, div, a, strong {
            font-family: 'Poppins', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        }

        body {
            background-color: #f1f5f9;
            margin: 0;
            padding: 40px 15px;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(50, 65, 140, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04);
        }

        /* NU Blue Header with Gold Accent Line */
        .header {
            background-color: #32418c;
            padding: 32px 20px;
            text-align: center;
            border-bottom: 6px solid #fbd117;
        }

        .header img {
            width: 100%;
            max-width: 480px;
            height: auto;
            display: inline-block;
        }

        .content {
            padding: 40px 32px;
            color: #1e293b;
            line-height: 1.6;
        }

        /* Category Tag Line */
        .category-tag {
            display: inline-block;
            background-color: #fff7d6;
            color: #a17a00;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }

        /* Hero Title Emphasis */
        .welcome-title {
            color: #32418c;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.25;
            margin-top: 0;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .event-subtitle {
            color: #475569;
            font-size: 15px;
            font-weight: 500;
            margin-top: 0;
            margin-bottom: 24px;
        }

        .greeting {
            color: #334155;
            font-size: 18px;
            font-weight: 600;
            margin-top: 0;
            margin-bottom: 12px;
        }

        .content p {
            font-size: 15px;
            color: #475569;
            margin-bottom: 20px;
        }

        /* Distance Callout Badge */
        .distance-badge {
            display: inline-block;
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
            color: #32418c;
            font-size: 13px;
            font-weight: 700;
            padding: 8px 16px;
            border-radius: 999px;
            border: 1px solid #c7d2fe;
            margin-bottom: 20px;
        }

        .distance-badge .pin-icon {
            margin-right: 4px;
        }

        /* Custom admin note */
        .custom-message {
            background: #fffbeb;
            border-left: 4px solid #fbd117;
            padding: 16px 20px;
            border-radius: 8px;
            color: #78350f;
            font-size: 14px;
            font-style: italic;
            margin: 24px 0;
            line-height: 1.55;
        }

        /* Event Detail Card */
        .event-card {
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-left: 5px solid #32418c;
            padding: 24px;
            border-radius: 10px;
            margin: 28px 0;
        }

        .event-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .event-card td {
            padding: 8px 0;
            vertical-align: top;
        }

        .label {
            color: #32418c;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 130px;
        }

        .value {
            color: #1e293b;
            font-size: 15px;
            font-weight: 600;
            word-break: break-word;
        }

        .value-sub {
            display: block;
            color: #64748b;
            font-size: 13px;
            font-weight: 400;
            margin-top: 2px;
        }

        /* Venue Map Thumbnail */
        .map-preview {
            margin: 24px 0;
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 12px rgba(50, 65, 140, 0.08);
        }

        .map-preview img {
            width: 100%;
            height: auto;
            display: block;
        }

        .map-caption {
            background: #f8fafc;
            color: #64748b;
            font-size: 12px;
            padding: 10px 14px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            margin: 0;
        }

        /* CTA Buttons */
        .btn-container {
            text-align: center;
            margin: 32px 0 16px;
        }

        .btn {
            display: inline-block;
            background-color: #32418c;
            color: #ffffff !important;
            text-decoration: none;
            padding: 16px 36px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: 0.3px;
            box-shadow: 0 6px 20px rgba(50, 65, 140, 0.25);
            transition: all 0.2s ease;
        }

        .btn:hover {
            background-color: #253069;
        }

        .btn-secondary {
            display: inline-block;
            background-color: #ffffff;
            color: #32418c !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 14px;
            letter-spacing: 0.3px;
            border: 2px solid #cbd5e1;
            margin-top: 10px;
            transition: all 0.2s ease;
        }

        .btn-secondary:hover {
            border-color: #32418c;
            background: #f8fafc;
        }

        /* RSVP Notice */
        .rsvp-notice {
            font-size: 13px;
            color: #64748b;
            background-color: #f8fafc;
            padding: 14px 18px;
            border-radius: 8px;
            text-align: center;
            border: 1px dashed #cbd5e1;
            margin-top: 20px;
            line-height: 1.55;
        }

        .signoff {
            margin-top: 36px;
            border-top: 1px solid #f1f5f9;
            padding-top: 24px;
            margin-bottom: 0;
            font-size: 14px;
            color: #64748b;
        }

        .footer {
            padding: 24px;
            background-color: #f8fafc;
            text-align: center;
            font-size: 13px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
        }

        .footer strong {
            color: #32418c;
            font-weight: 600;
        }

        /* Mobile Adjustments */
        @media only screen and (max-width: 600px) {
            body { padding: 12px 8px; }
            .content { padding: 28px 20px; }
            .header { padding: 24px 16px; }
            .header img { max-width: 280px; }
            .welcome-title { font-size: 23px; }
            .greeting { font-size: 16px; }
            .event-card td { display: block; width: 100%; }
            .label { width: 100%; padding-bottom: 2px; }
            .btn, .btn-secondary { width: 85%; padding: 14px 16px; }
        }
    </style>
</head>
<body>
    @php
        // ---- Safe fallbacks for every variable the mailer passes in ----
        $alumnusName    = trim(($alumnus['first_name'] ?? '') . ' ' . ($alumnus['last_name'] ?? ''));
        $firstName      = $alumnus['first_name'] ?? 'Alumni';

        $eventTitle     = $event->title ?? 'LumiNUs Event';
        $eventType      = $event->event_type ?? 'Event';

        // Pretty date formatting (event dates are already Carbon in PH time when shown here)
        $startDate      = $event->start_date ? $event->start_date->format('l, F j, Y') : 'TBA';
        $endDate        = $event->end_date ? $event->end_date->format('l, F j, Y') : null;
        $dateRange      = ($endDate && $endDate !== $startDate)
                            ? $startDate . ' – ' . $endDate
                            : $startDate;

        // Venue
        $venueName      = $venue->name ?? 'Venue TBA';
        $venueAddress   = $venue->address ?? '';
        $venueLat       = $venue->latitude ?? null;
        $venueLng       = $venue->longitude ?? null;

        // Distance (optional)
        $distanceText   = null;
        if (!empty($distance_km)) {
            $distanceText = $distance_km < 1
                ? 'less than 1 km away'
                : number_format($distance_km, 1) . ' km away';
        }

        // Static map image — OpenStreetMap static API (no key required)
        $staticMapUrl = null;
        if ($venueLat !== null && $venueLng !== null) {
            $staticMapUrl = 'https://staticmap.openstreetmap.de/staticmap.php?center='
                . $venueLat . ',' . $venueLng
                . '&zoom=15&size=560x260&maptype=mapnik'
                . '&markers=' . $venueLat . ',' . $venueLng . ',red-pushpin';
        }

        // External directions link
        $directionsUrl = null;
        if ($venueLat !== null && $venueLng !== null) {
            $directionsUrl = 'https://www.google.com/maps/dir/?api=1&destination='
                . urlencode($venueLat . ',' . $venueLng);
        }
    @endphp

    <div class="container">
        <!-- Header with Logo -->
        <div class="header">
            <img src="https://pmnirrvwibzqjlutbnwz.supabase.co/storage/v1/object/public/luminus_assets/Email_LogoHeader.png" alt="LumiNUs National University Lipa">
        </div>

        <!-- Body Content -->
        <div class="content">
            <!-- Category Tag -->
            <div class="category-tag">You're Invited</div>

            <!-- Emphasized Event Title -->
            <h1 class="welcome-title">{{ $eventTitle }}</h1>
            <p class="event-subtitle">
                {{ $eventType }} event hosted by the NU Lipa Alumni Affairs Office
            </p>

            <p class="greeting">Hi {{ $firstName }},</p>

            <p>
                Because you're part of our alumni community near the venue, we'd love for you to join us!
                @if($distanceText)
                    Our records show your registered address is
                    <strong>{{ $distanceText }}</strong> from the venue —
                    we hope you can make it.
                @endif
            </p>

            @if($distanceText)
                <div class="distance-badge">
                    <span class="pin-icon">📍</span> {{ $distanceText }} from the venue
                </div>
            @endif

            {{-- Optional custom note from the admin --}}
            @if(!empty($custom_message))
                <div class="custom-message">
                    “{{ $custom_message }}”
                    <div style="margin-top: 8px; font-style: normal; font-size: 12px; color: #a16207; font-weight: 600;">
                        — {{ $invitedBy ?? 'NU Lipa Alumni Affairs Office' }}
                    </div>
                </div>
            @endif

            <!-- Event Details Card -->
            <div class="event-card">
                <table>
                    <tr>
                        <td class="label">📅 Date</td>
                        <td class="value">{{ $dateRange }}</td>
                    </tr>
                    <tr>
                        <td class="label">🎟️ Event Type</td>
                        <td class="value">{{ $eventType }}</td>
                    </tr>
                    <tr>
                        <td class="label">📍 Venue</td>
                        <td class="value">
                            {{ $venueName }}
                            @if($venueAddress)
                                <span class="value-sub">{{ $venueAddress }}</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            {{-- Static map preview --}}
            @if($staticMapUrl)
                <div class="map-preview">
                    <img src="{{ $staticMapUrl }}" alt="Map preview of {{ $venueName }}">
                    <p class="map-caption">
                        {{ $venueName }}@if($venueAddress) — {{ $venueAddress }}@endif
                    </p>
                </div>
            @endif

            <!-- CTAs -->
            <div class="btn-container">
                {{-- <a href="{{ url('/') }}" class="btn">View Event Details &rarr;</a> --}}
                <br>
                @if($directionsUrl)
                    <a href="{{ $directionsUrl }}" class="btn-secondary">Get Directions</a>
                @endif
            </div>

            <!-- RSVP Notice -->
            <div class="rsvp-notice">
                ✨ <strong>Please RSVP through the LumiNUs mobile app</strong> to secure your slot.
                Slots are limited and confirmed on a first-come, first-served basis.
            </div>

            <!-- Sign-off -->
            <p class="signoff">
                Warm regards,<br><br>
                <span style="color: #32418c; font-weight: 700; font-size: 15px;">
                    Lumi<span style="color: #fbd117">NU</span>s System
                </span><br>
                <span style="font-size: 13px; color: #64748b;">NU Lipa Alumni Affairs Office</span>
            </p>
        </div>

        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} <strong>NU Lipa Alumni Affairs Office</strong>. All rights reserved.
        </div>
    </div>
</body>
</html>