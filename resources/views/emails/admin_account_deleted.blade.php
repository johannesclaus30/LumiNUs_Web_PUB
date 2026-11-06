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

        /* Category Tag Line (Security Alert Style) */
        .category-tag {
            display: inline-block;
            background-color: #fef2f2;
            color: #dc2626;
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

        /* Removal Details Card */
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

        /* Red Notice / Action Box */
        .warning-box {
            font-size: 13px; 
            color: #991b1b; 
            background-color: #fef2f2; 
            padding: 16px 20px; 
            border-radius: 10px;
            border: 1px solid #fecaca;
            border-left: 5px solid #dc2626;
            margin-top: 24px;
        }

        .warning-box strong {
            color: #7f1d1d;
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
            <!-- Category Tag -->
            <div class="category-tag">Access Revoked</div>

            <!-- Highly Emphasized Title -->
            <h1 class="welcome-title">Admin Account Removed</h1>

            <p class="greeting">Hello {{ $adminName }},</p>
            
            <p>This is an automated notification to inform you that administrative privileges associated with your account have been permanently revoked.</p>
            
            <!-- Removal Details Card -->
            <div class="details-card">
                <table>
                    <tr>
                        <td class="label">Target Account</td>
                        <td class="value">{{ $adminEmail }}</td>
                    </tr>
                    <tr>
                        <td class="label">Action Executed By</td>
                        <td class="value">{{ $deletedBy }}</td>
                    </tr>
                    <tr>
                        <td class="label">Access Status</td>
                        <td class="value" style="color: #dc2626;">Disabled</td>
                    </tr>
                </table>
            </div>

            <p>You will no longer be able to sign in or perform management actions within the LumiNUs Admin Dashboard.</p>

            <!-- Warning Notice Box -->
            <div class="warning-box">
                ⚠️ <strong>Need Assistance?</strong> If you believe your account was removed in error or require administrative clarification, please contact the <strong>NU Lipa Alumni Affairs Office Coordinator</strong> immediately.
            </div>
            
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