<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmed – {{ $event->title }}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body, table, td, p, h1, h2, h3, h4, h5, h6, span, div, a, strong {
            font-family: 'Poppins', 'Segoe UI', Roboto, Helvetica, Arial, sans-serif !important;
        }
        body { background-color: #f1f5f9; margin: 0; padding: 40px 15px; }
        .container {
            max-width: 600px; width: 100%; margin: 0 auto;
            background: #ffffff; border-radius: 16px; overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(50,65,140,0.08), 0 8px 10px -6px rgba(0,0,0,0.04);
        }
        .header { background-color: #32418c; padding: 32px 20px; text-align: center; border-bottom: 6px solid #fbd117; }
        .header img { width: 100%; max-width: 480px; height: auto; display: inline-block; }
        .content { padding: 40px 32px; color: #1e293b; line-height: 1.6; }
        .category-tag {
            display: inline-block; background-color: #dcfce7; color: #15803d;
            font-size: 12px; font-weight: 700; letter-spacing: 1.2px;
            text-transform: uppercase; padding: 6px 14px; border-radius: 20px; margin-bottom: 12px;
        }
        .title { color: #32418c; font-size: 26px; font-weight: 800; line-height: 1.25; margin: 0 0 20px; letter-spacing: -0.5px; }
        .greeting { color: #334155; font-size: 18px; font-weight: 600; margin: 0 0 12px; }
        .content p { font-size: 15px; color: #475569; margin-bottom: 20px; }

        .event-card {
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0; border-left: 5px solid #32418c;
            padding: 24px; border-radius: 10px; margin: 28px 0;
        }
        .event-card table { width: 100%; border-collapse: collapse; }
        .event-card td { padding: 8px 0; vertical-align: top; }
        .label {
            color: #32418c; font-size: 13px; font-weight: 700;
            text-transform: uppercase; letter-spacing: 0.5px; width: 130px;
        }
        .value { color: #1e293b; font-size: 15px; font-weight: 600; }

        .note {
            font-size: 13px; color: #475569; background-color: #f8fafc;
            padding: 14px 18px; border-radius: 8px; border: 1px dashed #cbd5e1; margin-top: 20px;
        }
        .signoff {
            margin-top: 36px; border-top: 1px solid #f1f5f9;
            padding-top: 24px; font-size: 14px; color: #64748b;
        }
        .footer {
            padding: 24px; background-color: #f8fafc; text-align: center;
            font-size: 13px; color: #94a3b8; border-top: 1px solid #e2e8f0;
        }
        .footer strong { color: #32418c; font-weight: 600; }

        @media only screen and (max-width: 600px) {
            body { padding: 12px 8px; }
            .content { padding: 28px 20px; }
            .header { padding: 24px 16px; }
            .header img { max-width: 280px; }
            .title { font-size: 22px; }
            .event-card td { display: block; width: 100%; }
            .label { width: 100%; padding-bottom: 2px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="https://pmnirrvwibzqjlutbnwz.supabase.co/storage/v1/object/public/luminus_assets/Email_LogoHeader.png"
                 alt="LumiNUs – National University Lipa">
        </div>

        <div class="content">
            <div class="category-tag">Registration Confirmed</div>

            <h1 class="title">You're confirmed, {{ $alumnus->first_name }}! 🎉</h1>

            <p class="greeting">Hello {{ $alumnus->first_name }},</p>

            <p>
                Great news — the <strong>NU Lipa Alumni Affairs Office</strong> has officially
                <strong>confirmed your registration</strong> for the event below.
                We can't wait to see you there!
            </p>

            <div class="event-card">
                <table>
                    <tr>
                        <td class="label">Event</td>
                        <td class="value">{{ $event->title }}</td>
                    </tr>
                    <tr>
                        <td class="label">Date</td>
                        <td class="value">
                            {{ \Carbon\Carbon::parse($event->start_date)->format('F d, Y') }}
                            @if($event->end_date && $event->end_date != $event->start_date)
                                – {{ \Carbon\Carbon::parse($event->end_date)->format('F d, Y') }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td class="label">Type</td>
                        <td class="value">{{ $event->event_type }}</td>
                    </tr>
                    @if($event->event_type !== 'Online' && $event->venue)
                    <tr>
                        <td class="label">Venue</td>
                        <td class="value">{{ $event->venue->name }}</td>
                    </tr>
                    @endif
                    @if($event->event_type !== 'In-Person' && $event->platform)
                    <tr>
                        <td class="label">Platform</td>
                        <td class="value">{{ $event->platform }}</td>
                    </tr>
                    @endif
                    @if($event->group_chat_id)
                    <tr>
                        <td class="label">Group Chat</td>
                        <td class="value">A group chat has been created for attendees — check your Messages tab in LumiNUs.</td>
                    </tr>
                    @endif
                </table>
            </div>

            <div style="text-align: center; margin: 30px 0;">
                <!-- Google Calendar Button -->
                <a href="{{ $googleCalUrl }}" target="_blank" style="display: inline-block; background-color: #4285F4; color: #ffffff; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-weight: 700; font-size: 15px; margin-bottom: 12px; width: 80%; max-width: 300px;">
                    📅 Add to Google Calendar
                </a>
                <br>
                <!-- Apple / Outlook Button -->
                <a href="{{ $appleCalUrl }}" style="display: inline-block; background-color: #FBD117; color: #32418C; text-decoration: none; padding: 14px 24px; border-radius: 8px; font-weight: 700; font-size: 15px; width: 80%; max-width: 300px;">
                    🍎 Add to Apple/Outlook
                </a>
                <p style="font-size: 12px; color: #64748b; margin-top: 12px;">
                    Save the event directly to your preferred calendar.
                </p>
            </div>

            <div class="note">
                📌 <strong>Reminder:</strong> Please arrive 10–15 minutes early (or join the online session on time).
                If your plans change, kindly message the Alumni Affairs Office so we can free up your slot.
            </div>

            <p class="signoff">
                See you soon,<br><br>
                <span style="color: #32418c; font-weight: 700; font-size: 15px;">
                    Lumi<span style="color: #fbd117">NU</span>s Team
                </span><br>
                <span style="font-size: 13px; color: #64748b;">NU Lipa Alumni Affairs Office</span>
            </p>
        </div>

        <div class="footer">
            &copy; {{ date('Y') }} <strong>NU Lipa Alumni Affairs Office</strong>. All rights reserved.
        </div>
    </div>
</body>
</html>