{{-- resources/views/welcome.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LumiNUs - NU Lipa Alumni Management System</title>

    <link rel="stylesheet" href="/css/landing_modern.css">
    <link rel="icon" type="image/png" href="/assets/logos/LumiNUs_Icon.png">
    
    <!-- Font Awesome for icons -->
    <link 
        rel="stylesheet" 
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
        integrity="sha512-Avb2QiuDEEvB4bZJYdft2mNjVShBftLdPG8FJ0V7irTLQ8Uo0qcPxh4Plq7G5tGm0rU+1SPhVotteLpBERwTkw=="
        crossorigin="anonymous"
        referrerpolicy="no-referrer"
    />

</head>
<body>

<!-- =========================================
     NAVIGATION
     ========================================= -->
<nav class="navbar">
    <div class="nav-container">
        <div class="nav-logo">
            <img src="/assets/logos/NULIPA_AAO_White.png" alt="LumiNUs Logo" class="nav-logo-img">
        </div>
        <div class="nav-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#download">Download</a>
            <a href="#contact">Contact</a>
            <a href="{{ route('admin.login') }}" class="btn-nav-login">
                <i class="fas fa-sign-in-alt"></i> Admin Login
            </a>
        </div>
        <button class="nav-toggle" aria-label="Toggle navigation menu">
            <i class="fas fa-bars"></i>
        </button>
    </div>
</nav>

<!-- =========================================
     HERO SECTION
     ========================================= -->
<section class="hero">
    <div class="hero-overlay"></div>
    <img src="/assets/NULipa_Building.jpg" alt="NU Lipa Building" class="hero-bg">
    
    <div class="hero-content">
        <img src="/assets/logos/LumiNUs_Logo_Landscape_White.png" alt="LumiNUs Logo" class="hero-logo">
        <h1 class="hero-title">Stay Connected,<br>Wherever You Are</h1>
        <p class="hero-subtitle">
            The official mobile app for <strong>NU Lipa Alumni</strong>. 
            Stay updated, network with fellow alumni, and unlock exclusive perks 
            — all from the palm of your hand.
        </p>
        <div class="hero-buttons">
            <a href="#download" class="btn-primary">
                <i class="fas fa-download"></i> Download App
            </a>
            <a href="#features" class="btn-secondary">
                <i class="fas fa-chevron-down"></i> Explore Features
            </a>
        </div>
        <div class="hero-stats">
            <div class="stat-item">
                <span class="stat-number" data-count="500">0</span>
                <span class="stat-label">Alumni</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number" data-count="20">0</span>
                <span class="stat-label">Events</span>
            </div>
            <div class="stat-divider"></div>
            <div class="stat-item">
                <span class="stat-number" data-count="4">0</span>
                <span class="stat-label">Years</span>
            </div>
        </div>
    </div>
</section>

<!-- =========================================
     FEATURES SECTION - MOBILE APP
     ========================================= -->
<section id="features" class="features">
    <div class="section-header">
        <span class="section-tag">Mobile App Features</span>
        <h2 class="section-title">Everything You Need in One App</h2>
        <p class="section-subtitle">Designed exclusively for NU Lipa alumni to stay connected and engaged</p>
    </div>

    <div class="features-grid">
        <div class="feature-card">
            <div class="feature-icon icon-blue">
                <i class="fas fa-newspaper"></i>
            </div>
            <h3>Announcements</h3>
            <p>Stay updated with the latest news, events, and important announcements from the Alumni Affairs Office.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-yellow">
                <i class="fas fa-calendar-alt"></i>
            </div>
            <h3>Events</h3>
            <p>Discover and register for alumni events, reunions, and university activities right from your phone.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-blue">
                <i class="fas fa-gifts"></i>
            </div>
            <h3>Perks & Discounts</h3>
            <p>Unlock exclusive perks, discounts, and benefits from partner establishments as a NU Lipa alumni.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-yellow">
                <i class="fas fa-comments"></i>
            </div>
            <h3>Messaging</h3>
            <p>Connect with fellow alumni and the Alumni Affairs Office through secure in-app messaging.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-blue">
                <i class="fas fa-users"></i>
            </div>
            <h3>Community Feed</h3>
            <p>Share updates, photos, and memories with the NU Lipa alumni community in a vibrant social feed.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-yellow">
                <i class="fas fa-user-friends"></i>
            </div>
            <h3>Alumni Networking</h3>
            <p>Build your professional network by connecting with alumni across different industries and batches.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-blue">
                <i class="fas fa-chalkboard-teacher"></i>
            </div>
            <h3>Mentorship Opportunities</h3>
            <p>Find mentors, offer guidance, and participate in programs that foster professional growth.</p>
        </div>

        <div class="feature-card">
            <div class="feature-icon icon-yellow">
                <i class="fas fa-clipboard-list"></i>
            </div>
            <h3>Alumni Tracer</h3>
            <p>Complete your alumni profile and help us track the success of NU Lipa graduates through our tracer system.</p>
            {{-- <span class="feature-badge">Most Important</span> --}}
        </div>
    </div>
</section>

<!-- =========================================
     DOWNLOAD SECTION - APP PROMO WITH SCREENSHOT
     ========================================= -->
<!-- =========================================
     DOWNLOAD SECTION - APP PROMO WITH SCREENSHOT
     ========================================= -->
<section id="download" class="download">
    <!-- Interactive Background -->
    <div class="download-bg">
        <div class="bg-orbs">
            <div class="orb orb-1"></div>
            <div class="orb orb-2"></div>
            <div class="orb orb-3"></div>
            <div class="orb orb-4"></div>
        </div>
        <div class="bg-particles" id="particles"></div>
    </div>
    
    <div class="download-container">
        <div class="download-content">
            <span class="section-tag">Download Now</span>
            <h2>Get the Mobile App!</h2>
            <p class="download-description">
                Available for Android devices. Stay connected with your alma mater wherever life takes you.
            </p>
            
            <div class="download-buttons">
                <a 
                    href="https://drive.google.com/drive/folders/1tAe8ypcPWOBCW-TvTcLh5ITVySbyivsQ?usp=sharing" 
                    class="download-btn"
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label="Download LumiNUs APK from Google Drive"
                >
                    <i class="fab fa-google-drive"></i>
                    <div class="download-btn-text">
                        <span>Download APK</span>
                        <strong>Google Drive</strong>
                    </div>
                </a>
            </div>
            
            <div class="download-note">
                <i class="fas fa-shield-alt"></i>
                <p>Scan the APK with your device's security software before installing. Enable "Install from Unknown Sources" in your Android settings.</p>
            </div>
        </div>
        
        <div class="download-mockup">
            <div class="mockup-phone">
                <!-- Phone Frame with Screenshot -->
                <div class="mockup-screen">
                    <img src="/assets/luminus_alumni.jpg" alt="LumiNUs App Screenshot" class="mockup-screenshot">
                </div>
                <!-- Phone Details -->
                <div class="mockup-notch"></div>
                <div class="mockup-buttons">
                    <div class="mockup-side-btn"></div>
                    <div class="mockup-side-btn volume-up"></div>
                    <div class="mockup-side-btn volume-down"></div>
                </div>
                <div class="mockup-home-indicator"></div>
            </div>
            
            <!-- Floating Elements around the phone -->
            <div class="floating-float float-1">
                <i class="fas fa-star"></i>
            </div>
            <div class="floating-float float-2">
                <i class="fas fa-heart"></i>
            </div>
            <div class="floating-float float-3">
                <i class="fas fa-bolt"></i>
            </div>
            <div class="floating-float float-4">
                <i class="fas fa-circle-check"></i>
            </div>
        </div>
    </div>
</section>

<!-- =========================================
     ABOUT / MISSION SECTION
     ========================================= -->
<section id="about" class="about">
    <div class="about-container">
        <div class="about-image">
            <img src="/assets/nulipa.jpg" alt="NU Lipa Campus">
            <div class="about-image-overlay">
                <span>NU Lipa</span>
            </div>
        </div>
        <div class="about-content">
            <span class="section-tag">About LumiNUs</span>
            <h2>Empowering Alumni Connections</h2>
            <p>
                <strong>LumiNUs</strong> is the official alumni mobile application of <strong>National University - Lipa</strong>, 
                developed to strengthen the bond between the university and its graduates.
            </p>
            <p>
                Managed by the <strong>NU Lipa Alumni Affairs Office (AAO)</strong>, this platform serves as a central hub 
                for alumni engagement, event coordination, and community building — accessible anytime, anywhere.
            </p>
            <div class="about-mission">
                <div class="mission-item">
                    <i class="fas fa-bullseye icon-yellow-text"></i>
                    <div>
                        <h4>Our University Mission</h4>
                        <p>Guided by the core values and characterized by our cultural heritage of Dynamic Filipinism, National University commits to delivering transformative, quality education that is accessible and affordable.</p>
                    </div>
                </div>
                <div class="mission-item">
                    <i class="fas fa-eye icon-yellow-text"></i>
                    <div>
                        <h4>Our University Vision</h4>
                        <p>We are National University, a dynamic Philippine institution leading in Asia, internationally recognized in teaching, research, and sustainable community development towards nation-building</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- =========================================
     CONTACT SECTION
     ========================================= -->
<section id="contact" class="contact">
    <div class="section-header">
        <span class="section-tag">Contact</span>
        <h2 class="section-title">Get in Touch</h2>
        <p class="section-subtitle">Reach out to the NU Lipa Alumni Affairs Office</p>
    </div>

    <div class="contact-grid">
        <div class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-map-marker-alt"></i>
            </div>
            <h4>Visit Us</h4>
            <p>SM City Lipa,<br>J.P. Laurel Highway,<br>Brgy. Marauouy, Lipa City, Batangas, PH</p>
        </div>

        <div class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-envelope"></i>
            </div>
            <h4>Email Us</h4>
            <p><a href="mailto:alumniaffairs@nu-lipa.edu.ph">ccmaranan@nu-lipa.edu.ph</a><br>NU Lipa Alumni Coordinator</p>
        </div>

        <div class="contact-card">
            <div class="contact-icon">
                <i class="fas fa-phone-alt"></i>
            </div>
            <h4>Call Us</h4>
            <p>0945-859-3348<br>NU Lipa Alumni Affairs Office</p>
        </div>
    </div>
</section>

<!-- =========================================
     FOOTER
     ========================================= -->
<footer class="footer">
    <div class="footer-container">
        <div class="footer-brand">
            <img src="/assets/logos/Footer_LumiNUsLipa.png" alt="LumiNUs Logo" class="footer-logo">
            <p>NU Lipa Alumni Management System</p>
        </div>
        <div class="footer-links">
            <a href="#features">Features</a>
            <a href="#about">About</a>
            <a href="#download">Download</a>
            <a href="#contact">Contact</a>
            <a href="{{ route('admin.login') }}">Admin Login</a>
        </div>
        <div class="footer-social">
            <a href="#" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
            <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
            <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; {{ date('Y') }} LumiNUs - National University Lipa. All rights reserved.</p>
    </div>
</footer>

<div id="luminus-scrollbar"></div>

<script src="/js/landing.js" defer></script>

</body>
</html>

{{-- This is welcome.blade.php --}}