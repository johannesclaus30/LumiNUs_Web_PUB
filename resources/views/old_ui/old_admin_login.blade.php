<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | LumiNUs Admin</title>

    <link rel="stylesheet" href="/css/admin_login.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
</head>

<body>
    
<div class="layout-wrapper">

    <div class="building-wrapper">
        <img class="building-img" src="/assets/NULipa_Building.jpg" alt="NULipa Building">
    </div>

    <div class="login-container">
        <div class="login-container-content">

            <img class="luminus-logo" src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo">
            <p class="subtitle">Admin Account</p>

            <form id="admin-login-form" method="POST" action="{{ route('admin.login.attempt') }}">
                @csrf
                
                {{-- Error Alert (shown only when validation fails) --}}
                @if ($errors->any())
                    <div class="error-alert">
                        <svg class="error-icon" viewBox="0 0 24 24" width="20" height="20">
                            <path fill="currentColor" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                        </svg>
                        <span>
                            @foreach ($errors->all() as $error)
                                {{ $error }}
                            @endforeach
                        </span>
                    </div>
                @endif

                <label>Email</label>
                <input class="login-textfield" type="email" name="admin_email" value="{{ old('admin_email') }}" placeholder="Enter Your Admin Email Address" required>

                <label>Password</label>
                <div class="password-wrapper">
                    <input class="login-textfield password-input" type="password" name="password" id="password-field" placeholder="Enter Your Password" required>
                    <button type="button" class="toggle-password" aria-label="Show password">
                        <svg class="eye-icon" viewBox="0 0 24 24" width="22" height="22">
                            <path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                        </svg>
                    </button>
                </div>

                <button type="submit" style="display:none;" aria-hidden="true" tabindex="-1"></button>
                <a href="#" class="login-btn" onclick="event.preventDefault(); document.getElementById('admin-login-form').submit();">Sign In</a>
            </form>
            

        </div>
    </div>

</div>

{{-- Password Toggle JavaScript --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.querySelector('.toggle-password');
        const passwordInput = document.getElementById('password-field');
        
        if (toggleBtn && passwordInput) {
            toggleBtn.addEventListener('click', function() {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                
                // Toggle eye icon (open/closed)
                const eyeIcon = this.querySelector('.eye-icon');
                if (type === 'text') {
                    eyeIcon.innerHTML = '<path fill="currentColor" d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.43-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/>';
                    this.setAttribute('aria-label', 'Hide password');
                } else {
                    eyeIcon.innerHTML = '<path fill="currentColor" d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>';
                    this.setAttribute('aria-label', 'Show password');
                }
            });
        }
    });
</script>

</body>
</html>