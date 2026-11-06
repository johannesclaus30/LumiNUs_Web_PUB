<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/login_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">

<style>
    .back-to-home {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        font-weight: 500;
        color: #6B7280;
        text-decoration: none;
        padding: 0.5rem 0.75rem;
        margin-bottom: 1.5rem;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
        margin-left: -0.75rem;
    }
    .back-to-home:hover {
        color: #32418C;
        background: #F3F4F6;
        gap: 0.75rem;
    }
    .back-to-home svg {
        transition: transform 0.2s ease;
    }
    .back-to-home:hover svg {
        transform: translateX(-3px);
    }

        .eye-icon { 
        width: 22px;
        height: 22px;
        display: none; 
    }
    .show-eye-open .eye-icon.eye-open { display: block; }
    .show-eye-closed .eye-icon.eye-closed { display: block; }

    
</style>

</head>

<body>

<div class="login-wrapper">
    
    {{-- LEFT PANEL: BRANDING & IMAGE --}}
    <div class="brand-panel">
        <div class="brand-overlay"></div>
        <img src="/assets/NULipa_Building.jpg" alt="NU Lipa Building" class="brand-bg">
        
        <div class="brand-content">
            <img src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo" class="brand-logo">
            <h1 class="brand-title">Welcome Back</h1>
            <p class="brand-subtitle">NU Lipa Alumni Affairs Office</p>
        </div>
    </div>

    {{-- RIGHT PANEL: LOGIN FORM --}}
    <main class="form-panel">
        <div class="form-container">
  
            <a href="{{ url('/') }}" class="back-to-home">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <path d="M20 11H7.83l5.59-5.59L12 4l-8 8 8 8 1.41-1.41L7.83 13H20v-2z"/>
                </svg>
                Back to Home
            </a>
            
            <div class="form-header">
                <h2>Admin Sign In</h2>
                <p>Enter your credentials to access the dashboard</p>
            </div>

    {{-- Error Alert --}}
    @if ($errors->any())
        <div class="alert alert-error" id="errorAlert">
            <div class="alert-icon-wrapper">
                <svg class="alert-icon" viewBox="0 0 24 24">
                    <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
            </div>
            
            <div class="alert-content">
                <div class="alert-title">
                    <svg width="16" height="16" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                    Login Failed
                </div>
                <div class="alert-message">
                    @foreach ($errors->all() as $error)
                        {{ $error }}<br>
                    @endforeach
                </div>
            </div>
            
            <button type="button" class="alert-dismiss" aria-label="Dismiss error" data-action="dismiss-alert">
                <svg viewBox="0 0 24 24">
                    <path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        </div>
    @endif

            <form id="admin-login-form" method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf
                
                {{-- Email Field --}}
                <div class="input-group">
                    <label for="admin_email">Email Address</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>

                        <input type="email" id="admin_email" name="admin_email" value="" placeholder="name@example.com" required autocomplete="username">

                    </div>
                </div>

                {{-- Password Field --}}
                <div class="input-group">
                    <label for="password-field">Password</label>
                    <div class="input-wrapper">
                        <svg class="input-icon" viewBox="0 0 24 24"><path fill="currentColor" d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zm-6 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm3.1-9H8.9V6c0-1.71 1.39-3.1 3.1-3.1 1.71 0 3.1 1.39 3.1 3.1v2z"/></svg>
                        <input type="password" id="password-field" name="password" placeholder="Enter your password" required>
                        
                        <button type="button" class="toggle-password show-eye-open" aria-label="Show password">
                            <svg class="eye-icon eye-open" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>
                            <svg class="eye-icon eye-closed" width="22" height="22" viewBox="0 0 24 24"><path fill="currentColor" d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>
                        </button>
                    </div>
                </div>

                {{-- Forgot Password Link --}}
                <div class="forgot-password-wrapper">
                    <a href="{{ route('admin.forgot-password') }}" class="forgot-password-link">
                        Forgot Password?
                    </a>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn-submit">
                    Sign In
                    <svg class="btn-arrow" viewBox="0 0 24 24"><path fill="currentColor" d="M12 4l-1.41 1.41L16.17 11H4v2h12.17l-5.58 5.59L12 20l8-8z"/></svg>
                </button>
            </form>
            
            <div class="form-footer">
                <p>© {{ date('Y') }} LumiNUs - NU Lipa Alumni Affairs</p>
            </div>

        </div>
    </main>
</div>

<script src="/js/admin-login.js" defer></script>

</body>
</html>

{{-- This is admin_login.blade.php --}}