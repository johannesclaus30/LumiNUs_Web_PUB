<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Restricted | LumiNUs Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    <style>
        :root {
            --nu-blue: #32418C;
            --nu-gold: #FBD117;
            --danger: #EF4444;
            --gray-600: #4B5563;
            --gray-500: #6B7280;
            --white: #FFFFFF;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f0f2fb 0%, #e8ebf7 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .restricted-card {
            background: var(--white);
            border-radius: 1.5rem;
            box-shadow: 0 20px 40px rgba(50, 65, 140, 0.12);
            max-width: 520px;
            width: 100%;
            padding-top: 2rem;
            padding: 1.5rem 2rem;
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .restricted-icon {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #FEE2E2;
            color: #EF4444;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 2rem;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        .restricted-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1F2937;
            margin-bottom: 0.75rem;
        }
        .restricted-message {
            font-size: 0.9375rem;
            color: #6B7280;
            line-height: 1.7;
            margin-bottom: 2rem;
        }
        .restricted-message strong {
            color: #1F2937;
        }
        .restricted-contact {
            background: #F9FAFB;
            border: 1px solid #E5E7EB;
            border-radius: 1rem;
            padding: 1.25rem;
            margin-bottom: 2rem;
            text-align: left;
        }
        .restricted-contact h4 {
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .restricted-contact h4 i {
            color: var(--nu-blue);
        }
        .restricted-contact p {
            font-size: 0.8125rem;
            color: #6B7280;
            margin: 0.25rem 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-return {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: linear-gradient(135deg, var(--nu-blue), #253069);
            color: var(--white);
            text-decoration: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }
        .btn-return:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(50, 65, 140, 0.25);
        }
        .brand-footer {
            margin-top: 1rem;
            padding-top: 1.5rem;
            border-top: 1px solid #E5E7EB;
        }
        .brand-footer img {
            height: 80px;
        }
    </style>
</head>
<body>
    <div class="restricted-card">
        <div class="restricted-icon">
            <i class="fa-solid fa-lock"></i>
        </div>
        <h1 class="restricted-title">Account Restricted</h1>
        <p class="restricted-message">
            Your administrator account <strong>{{ session('restricted_email', '') }}</strong> 
            has been temporarily restricted. You are unable to access the admin dashboard 
            at this time.
        </p>
        <div class="restricted-contact">
            <h4><i class="fa-solid fa-circle-info"></i> What to do:</h4>
            <p>
                <i class="fa-solid fa-envelope"></i>
                Contact the <strong>NU Lipa Alumni Affairs Office Coordinator</strong>
            </p>
            <p>
                <i class="fa-solid fa-phone"></i>
                Reach out via official communication channels for assistance
            </p>
            <p style="margin-top: 0.75rem; font-size: 0.8rem; color: #9CA3AF;">
                <i class="fa-solid fa-clock"></i>
                The coordinator can review your account status and restore access if appropriate.
            </p>
        </div>
        <a href="{{ route('admin.login') }}" class="btn-return">
            <i class="fa-solid fa-arrow-left"></i> Return to Login
        </a>
        <div class="brand-footer">
            <img src="/assets/logos/LumiNUs_Logo_Landscape_Blue.png" alt="LumiNUs">
        </div>
    </div>
</body>
</html>