<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alumni Tracer Survey Reminder</title>
    
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

        /* Action/Status Card */
        .status-card { 
            background: linear-gradient(135deg, #f8fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-left: 5px solid #32418c;
            padding: 20px; 
            border-radius: 10px; 
            margin: 28px 0; 
        }

        .status-card table {
            width: 100%;
            border-collapse: collapse;
        }

        .status-label {
            color: #64748b;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 4px;
        }

        .status-value {
            color: #32418c;
            font-size: 16px;
            font-weight: 700;
        }

        /* Modern Feature Grid / Purpose Highlight Section */
        .purpose-section {
            margin: 32px 0;
            padding-top: 24px;
            border-top: 1px solid #f1f5f9;
        }

        .purpose-heading {
            font-size: 14px;
            font-weight: 700;
            color: #32418c;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .purpose-item {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }

        .purpose-icon {
            display: table-cell;
            width: 36px;
            vertical-align: top;
            font-size: 18px;
        }

        .purpose-text {
            display: table-cell;
            vertical-align: top;
            font-size: 14px;
            color: #475569;
        }

        .purpose-text strong {
            color: #1e293b;
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

        .disclaimer-box {
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
            <!-- Category Tag -->
            <div class="category-tag">Action Required</div>

            <!-- Highly Emphasized Title -->
            <h1 class="welcome-title">Alumni Tracer Survey Reminder</h1>

            <p class="greeting">Hello {{ $alumniName ?? ($alumni->first_name ?? 'Alumni') }},</p>
            
            <p>We hope you are doing well! We noticed that your responses for the official tracer survey are still pending. Your input is vital in shaping the future of National University Lipa.</p>
            
            <!-- Pending Form Card -->
            <div class="status-card">
                <table>
                    <tr>
                        <td class="status-label">Pending Document</td>
                    </tr>
                    <tr>
                        <td class="status-value">{{ $formTitle ?? ($form->form_title ?? 'Tracer Form') }}</td>
                    </tr>
                </table>
            </div>

            <!-- Why This Matters Section -->
            <div class="purpose-section">
                <div class="purpose-heading">Why Your Participation Matters</div>
                
                <div class="purpose-item">
                    <div class="purpose-icon">🎓</div>
                    <div class="purpose-text">
                        <strong>Enhance Academic Programs:</strong> Your career achievements help us align institutional curricula with real-world industry demands.
                    </div>
                </div>

                <div class="purpose-item">
                    <div class="purpose-icon">🌟</div>
                    <div class="purpose-text">
                        <strong>Elevate NU's Reputation:</strong> Alumni success metrics directly influence university rankings, accreditation, and degree prestige.
                    </div>
                </div>

                <div class="purpose-item">
                    <div class="purpose-icon">⏱️</div>
                    <div class="purpose-text">
                        <strong>Quick & Confidential:</strong> Takes only <strong>10–15 minutes</strong> to complete and directly informs future alumni initiatives.
                    </div>
                </div>
            </div>

            <!-- Call to Action -->
            {{-- <div class="btn-container">
                <a href="{{ $tracerUrl ?? url('/alumni/tracer/' . ($form->id ?? '')) }}" class="btn">Complete Survey Now &rarr;</a>
            </div> --}}
            
            <!-- Conditional Disclaimer -->
            <div class="disclaimer-box">
                If you have already submitted this survey, please disregard this email. Thank you for keeping your profile updated!
            </div>
            
            <!-- Sign-off -->
            <p class="signoff">
                Warm regards,<br><br>
                <span style="color: #32418c; font-weight: 700; font-size: 15px;">
                    Lumi<span style="color: #fbd117">NU</span>s Team
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

{{-- this is the tracer_reminder.blade.php --}}