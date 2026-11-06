<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | LumiNUs Admin</title>
    
    <!-- Fonts & Icons -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />

    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            overflow-x: hidden;
        }

        .auth-container {
            width: 100%;
            max-width: 460px;
            margin: auto;
        }

        .auth-card {
            background: #FFFFFF;
            border-radius: 1.5rem;
            box-shadow: 
                0 4px 6px -1px rgba(0, 0, 0, 0.05),
                0 10px 15px -3px rgba(0, 0, 0, 0.05),
                0 20px 50px -12px rgba(0, 0, 0, 0.1);
            border: 1px solid #E5E7EB;
            overflow: hidden;
            transition: transform 0.3s ease;
        }

        .auth-header {
            background: linear-gradient(135deg, #1e2a5e 0%, #32418C 50%, #253069 100%);
            padding: 2.5rem 2rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .auth-header::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.05) 0%, transparent 60%);
            pointer-events: none;
        }

        .auth-header .icon-circle {
            width: 64px;
            height: 64px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.25rem;
            position: relative;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .auth-header .icon-circle i {
            font-size: 1.75rem;
            color: #FBD117;
            filter: drop-shadow(0 2px 4px rgba(0, 0, 0, 0.1));
        }

        .auth-header h1 {
            color: #FFFFFF;
            font-size: 1.5rem;
            font-weight: 600;
            margin: 0;
            position: relative;
        }

        .auth-header p {
            color: rgba(255, 255, 255, 0.75);
            font-size: 0.875rem;
            margin-top: 0.5rem;
            position: relative;
            font-weight: 400;
        }

        .auth-body {
            padding: 2.25rem 2rem 2rem;
        }

        .auth-body .subtitle {
            color: #6B7280;
            font-size: 0.9375rem;
            line-height: 1.6;
            margin-bottom: 1.75rem;
            text-align: center;
        }

        /* Alert Styles */
        .alert {
            padding: 0.875rem 1rem;
            border-radius: 0.75rem;
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            border: 1px solid;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-error {
            background: #FEF2F2;
            color: #991B1B;
            border-color: #FECACA;
        }

        .alert i {
            margin-top: 0.125rem;
            flex-shrink: 0;
        }

        .alert span {
            line-height: 1.5;
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 0.875rem 2.75rem 0.875rem 2.75rem;
            font-size: 0.9375rem;
            font-family: 'Poppins', sans-serif;
            color: #111827;
            background: #F9FAFB;
            border: 2px solid #E5E7EB;
            border-radius: 0.75rem;
            transition: all 0.2s ease;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        .form-control::placeholder {
            color: #9CA3AF;
        }

        .form-control:hover {
            border-color: #D1D5DB;
            background: #FFFFFF;
        }

        .form-control:focus {
            outline: none;
            border-color: #32418C;
            background: #FFFFFF;
            box-shadow: 0 0 0 3px rgba(50, 65, 140, 0.1);
        }

        .input-icon {
            position: absolute;
            left: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            color: #9CA3AF;
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .form-control:focus ~ .input-icon,
        .input-wrapper:focus-within .input-icon {
            color: #32418C;
        }

        .password-toggle {
            position: absolute;
            right: 0.875rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #9CA3AF;
            cursor: pointer;
            padding: 0.5rem;
            font-size: 1rem;
            transition: color 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .password-toggle:hover {
            color: #32418C;
        }

        .password-hint {
            font-size: 0.8125rem;
            color: #6B7280;
            margin-top: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.375rem;
        }

        .password-hint i {
            font-size: 0.75rem;
            color: #9CA3AF;
        }

        /* Button Styles */
        .btn-primary {
            width: 100%;
            padding: 0.9375rem;
            background: linear-gradient(135deg, #FBD117 0%, #F59E0B 100%);
            color: #1e2a5e;
            border: none;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 1rem;
            font-family: 'Poppins', sans-serif;
            cursor: pointer;
            transition: all 0.25s ease;
            box-shadow: 0 4px 14px rgba(251, 209, 23, 0.3);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(251, 209, 23, 0.4);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(251, 209, 23, 0.3);
        }

        .btn-primary:focus-visible {
            outline: 2px solid #32418C;
            outline-offset: 2px;
        }

        /* Back Link */
        .back-link {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 1.75rem;
            color: #6B7280;
            text-decoration: none;
            font-weight: 500;
            font-size: 0.9375rem;
            transition: all 0.2s ease;
            padding: 0.5rem;
        }

        .back-link:hover {
            color: #32418C;
            gap: 0.75rem;
        }

        .back-link i {
            transition: transform 0.2s ease;
        }

        .back-link:hover i {
            transform: translateX(-3px);
        }

        /* Responsive Adjustments */
        @media (max-width: 480px) {
            body {
                padding: 1rem;
            }

            .auth-header {
                padding: 2rem 1.5rem 1.5rem;
            }

            .auth-header .icon-circle {
                width: 56px;
                height: 56px;
            }

            .auth-header .icon-circle i {
                font-size: 1.5rem;
            }

            .auth-header h1 {
                font-size: 1.375rem;
            }

            .auth-body {
                padding: 1.5rem;
            }

            .auth-body .subtitle {
                font-size: 0.875rem;
            }

            .form-control {
                padding: 0.8125rem 2.5rem 0.8125rem 2.5rem;
                font-size: 0.875rem;
            }
        }

        @media (max-width: 360px) {
            body {
                padding: 0.75rem;
            }

            .auth-header {
                padding: 1.75rem 1.25rem 1.25rem;
            }

            .auth-body {
                padding: 1.25rem;
            }

            .btn-primary {
                padding: 0.8125rem;
                font-size: 0.9375rem;
            }
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <!-- Header -->
            <div class="auth-header">
                <div class="icon-circle">
                    <i class="fa-solid fa-lock"></i>
                </div>
                <h1>Reset Password</h1>
                <p>LumiNUs Admin Portal</p>
            </div>
            
            <!-- Body -->
            <div class="auth-body">
                @if(session('error'))
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-error">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                <p class="subtitle">
                    Create a strong new password for your admin account. Make sure it's something you'll remember!
                </p>

                <form method="POST" action="{{ route('admin.reset-password.process') }}" novalidate>
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="form-group">
                        <label class="form-label" for="password">New Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                name="password" 
                                id="password" 
                                class="form-control" 
                                placeholder="Min. 8 characters" 
                                required 
                                minlength="8" 
                                autofocus
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <p class="password-hint">
                            <i class="fa-solid fa-circle-info"></i>
                            Must be at least 8 characters long.
                        </p>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="input-wrapper">
                            <i class="fa-solid fa-lock input-icon"></i>
                            <input 
                                type="password" 
                                name="password_confirmation" 
                                id="password_confirmation" 
                                class="form-control" 
                                placeholder="Re-enter your password" 
                                required 
                                minlength="8"
                                autocomplete="new-password"
                            >
                            <button type="button" class="password-toggle" onclick="togglePassword(this)" tabindex="-1" aria-label="Toggle password visibility">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-shield-halved"></i> 
                        Reset Password
                    </button>
                </form>

                <a href="{{ route('admin.login') }}" class="back-link">
                    <i class="fa-solid fa-arrow-left"></i> 
                    Back to Sign In
                </a>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(button) {
            const input = button.parentElement.querySelector('input');
            const icon = button.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>