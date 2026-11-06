<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to LumiNUs Admin</title>
    
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

        /* Category Tag Line */
        .category-tag {
            display: inline-block;
            background-color: #eef2ff;
            color: #32418c;
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

        /* Credentials Container */
        .credentials-card { 
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-left: 5px solid #32418c;
            padding: 24px; 
            border-radius: 10px; 
            margin: 28px 0; 
        }

        .credentials-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .credentials-card td {
            padding: 8px 0;
            vertical-align: middle;
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
            font-family: 'SF Mono', 'Monaco', 'Inconsolata', 'Fira Code', monospace !important;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.5px;
            word-break: break-all;
        }

        /* CTA Button */
        .btn-container {
            text-align: center;
            margin: 36px 0;
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

        .security-notice {
            font-size: 13px; 
            color: #64748b; 
            background-color: #f8fafc; 
            padding: 14px 18px; 
            border-radius: 8px;
            text-align: center;
            border: 1px dashed #cbd5e1;
            margin-top: 20px;
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
            .credentials-card td {
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
    @php
        $fullName = trim(($admin->admin_first_name ?? '') . ' ' . ($admin->admin_middle_name ?? '') . ' ' . ($admin->admin_last_name ?? ''));
        $firstName = $admin->admin_first_name ?? 'there';
        $roleLabel = match($admin->admin_role) {
            'Executive Director' => 'Executive Director',
            'Academic Director' => 'Academic Director',
            'Coordinator' => 'Coordinator',
            'Assistant Coordinator' => 'Assistant Coordinator',
            default => 'Administrator',
        };
    @endphp

    <div class="container">
        <!-- Header with Logo -->
        <div class="header">
            <img src="https://pmnirrvwibzqjlutbnwz.supabase.co/storage/v1/object/public/luminus_assets/Email_LogoHeader.png" alt="LumiNUs National University Lipa">
        </div>
        
        <!-- Body Content -->
        <div class="content">
            <!-- Category Tag -->
            <div class="category-tag">Admin Invitation</div>

            <!-- Highly Emphasized Title -->
            <h1 class="welcome-title">Welcome to the Team!</h1>

            <p class="greeting">Hello {{ $firstName }},</p>
            
            <p>
                <strong>{{ $fullName }}</strong>, you have been invited to join the 
                <strong>LumiNUs Admin Portal</strong> as an 
                <strong style="color: #32418c;">{{ $roleLabel }}</strong>.
            </p>
            
            <p>Below are your temporary login credentials. Please use them to access your administrative account:</p>

            <!-- Credentials Card -->
            <div class="credentials-card">
                <table>
                    <tr>
                        <td class="label">Admin Email</td>
                        <td class="value">{{ $admin->admin_email }}</td>
                    </tr>
                    <tr>
                        <td class="label">Temp Password</td>
                        <td class="value">{{ $temporaryPassword }}</td>
                    </tr>
                </table>
            </div>

            <!-- Call to Action -->
            <div class="btn-container">
                <a href="{{ url('/admin/login') }}" class="btn">Sign In to Admin Portal &rarr;</a>
            </div>
            
            <!-- Security Notice -->
            <div class="security-notice">
                🔒 <strong>Important:</strong> For security purposes, please change your password immediately after your first login in <em>Settings &rarr; Security</em>.
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