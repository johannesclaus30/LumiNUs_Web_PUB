<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Account Status Update</title>
    
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
        
        /* Header Logo Styling */
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

        /* Category Tag Line - Dynamic Colors */
        .category-tag {
            display: inline-block;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.2px;
            text-transform: uppercase;
            padding: 6px 14px;
            border-radius: 20px;
            margin-bottom: 12px;
        }

        .category-tag.restricted {
            background-color: #fef2f2;
            color: #dc2626;
        }

        .category-tag.restored {
            background-color: #f0fdf4;
            color: #16a34a;
        }

        /* Hero Title Emphasis */
        .welcome-title {
            color: #32418c;
            font-size: 28px;
            font-weight: 800;
            line-height: 1.25;
            margin-top: 0;
            margin-bottom: 24px;
            letter-spacing: -0.5px;
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

        /* Details Card */
        .details-card { 
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-left: 5px solid #32418c;
            padding: 20px 24px; 
            border-radius: 10px; 
            margin: 24px 0; 
        }

        .details-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .details-card td {
            padding: 6px 0;
            vertical-align: top;
        }

        .label {
            color: #32418c;
            font-size: 13px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            width: 140px;
        }

        .value {
            color: #1e293b;
            font-size: 14px;
            font-weight: 600;
            word-break: break-all;
        }

        /* Status Callout Boxes */
        .notice-box {
            font-size: 13px; 
            padding: 16px 20px; 
            border-radius: 10px;
            margin-top: 24px;
        }

        .notice-box.restricted {
            color: #991b1b; 
            background-color: #fef2f2; 
            border: 1px solid #fecaca;
            border-left: 5px solid #dc2626;
        }

        .notice-box.restored {
            color: #166534; 
            background-color: #f0fdf4; 
            border: 1px solid #bbf7d0;
            border-left: 5px solid #16a34a;
        }

        /* CTA Button Container */
        .btn-container {
            text-align: center;
            margin: 32px 0 12px 0;
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
            body {
                padding: 12px 8px;
            }
            .content {
                padding: 28px 20px;
            }
            .header {
                padding: 24px 16px;
            }
            .header img {
                max-width: 280px;
            }
            .welcome-title {
                font-size: 23px;
            }
            .greeting {
                font-size: 16px;
            }
            .details-card td {
                display: block;
                width: 100%;
            }
            .label {
                width: 100%;
                padding-bottom: 2px;
            }
            .btn {
                width: 85%;
                padding: 14px 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header with Logo -->
        <div class="header">
            <img src="https://pmnirrvwibzqjlutbnwz.supabase.co/storage/v1/object/public/luminus_assets/Email_LogoHeader.png" alt="LumiNUs National University Lipa">
        </div>
        
        <!-- Body Content -->
        <div class="content">
            @if($isRestricted)
                <!-- Restricted State -->
                <div class="category-tag restricted">Account Restricted</div>
                <h1 class="welcome-title">Admin Access Restricted</h1>

                <p class="greeting">Hello {{ $admin->admin_first_name }},</p>
                <p>Your LumiNUs admin account has been <strong>temporarily restricted</strong>. Access to administrative controls and dashboard features has been suspended.</p>

                <!-- Metadata Card -->
                <div class="details-card">
                    <table>
                        <tr>
                            <td class="label">Updated By</td>
                            <td class="value">{{ $updatedBy }}</td>
                        </tr>
                        <tr>
                            <td class="label">Account Status</td>
                            <td class="value" style="color: #dc2626; font-weight: 700;">🔒 Restricted</td>
                        </tr>
                    </table>
                </div>

                <!-- Info Box -->
                <div class="notice-box restricted">
                    ⚠️ <strong>What to do:</strong> If you believe this restriction is an error or require access reinstatement, please reach out directly to the <strong>NU Lipa Alumni Affairs Office Coordinator</strong>.
                </div>
            @else
                <!-- Restored State -->
                <div class="category-tag restored">Account Restored</div>
                <h1 class="welcome-title">Admin Access Restored</h1>

                <p class="greeting">Hello {{ $admin->admin_first_name }},</p>
                <p>Great news! Your LumiNUs admin account has been <strong>successfully restored</strong>. Full access to your administrative privileges and dashboard features has been re-enabled.</p>

                <!-- Metadata Card -->
                <div class="details-card">
                    <table>
                        <tr>
                            <td class="label">Updated By</td>
                            <td class="value">{{ $updatedBy }}</td>
                        </tr>
                        <tr>
                            <td class="label">Account Status</td>
                            <td class="value" style="color: #16a34a; font-weight: 700;">✅ Active / Restored</td>
                        </tr>
                    </table>
                </div>

                <!-- Call to Action -->
                <div class="btn-container">
                    <a href="{{ url('/admin/login') }}" class="btn">Log in to Dashboard &rarr;</a>
                </div>

                <!-- Info Box -->
                <div class="notice-box restored">
                    🎉 <strong>Welcome back!</strong> You can now sign in using your existing account credentials.
                </div>
            @endif

            <!-- Sign-off -->
            <p class="signoff">
                Regards,<br><br>
                <span style="color: #32418c; font-weight: 700; font-size: 15px;">
                    Lumi<span style="color: #fbd117">NU</span>s System
                </span><br>
                <span style="font-size: 13px; color: #64748b;">NU Lipa Alumni Affairs Office</span>
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            &copy; {{ date('Y') }} <strong>NU Lipa Alumni Affairs Office</strong>. All rights reserved.<br>
            <span style="font-size: 11px; color: #cbd5e1;">This is an automated system notification. Please do not reply directly to this email.</span>
        </div>
    </div>
</body>
</html>