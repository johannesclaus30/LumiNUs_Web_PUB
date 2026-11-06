// =========================================
// MAIN INITIALIZATION
// =========================================
document.addEventListener('DOMContentLoaded', function() {
    
    // =========================================
    // MOBILE NAVIGATION TOGGLE
    // =========================================
    const toggleBtn = document.querySelector('.nav-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (toggleBtn && navLinks) {
        toggleBtn.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            const icon = this.querySelector('i');
            if (icon) {
                icon.classList.toggle('fa-bars');
                icon.classList.toggle('fa-times');
            }
        });
    }

    // Close menu when clicking a link
    document.querySelectorAll('.nav-links a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            const icon = toggleBtn?.querySelector('i');
            if (icon) {
                icon.classList.add('fa-bars');
                icon.classList.remove('fa-times');
            }
        });
    });

    // =========================================
    // ANIMATED COUNTER
    // =========================================
    const animateCounter = (element) => {
        const target = parseInt(element.getAttribute('data-count'));
        const duration = 2000;
        const startTime = performance.now();
        
        const updateCounter = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const eased = 1 - Math.pow(1 - progress, 3);
            const current = Math.floor(eased * target);
            element.textContent = current + '+';
            
            if (progress < 1) {
                requestAnimationFrame(updateCounter);
            } else {
                element.textContent = target + '+';
            }
        };
        
        requestAnimationFrame(updateCounter);
    };

    // Intersection Observer for stats
    const stats = document.querySelectorAll('.stat-number');
    const statsObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateCounter(entry.target);
                statsObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    stats.forEach(stat => statsObserver.observe(stat));

    // =========================================
    // SMOOTH SCROLL FOR ANCHOR LINKS
    // =========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href === '#') return;
            
            e.preventDefault();
            const target = document.querySelector(href);
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // =========================================
    // NAVBAR SCROLL EFFECT
    // =========================================
    const navbar = document.querySelector('.navbar');
    
    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;
        
        if (currentScroll > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

// =========================================
    // AUTO-HIDING SCROLLBAR (CUSTOM)
    // =========================================
    function initScrollbar() {
        let scrollTimeout;
        const body = document.body;
        const scrollThumb = document.getElementById('luminus-scrollbar');
        
        // Add class and move thumb when scrolling
        window.addEventListener('scroll', () => {
            body.classList.add('scrolling');
            
            // Move the custom thumb
            if (scrollThumb) {
                // 1. Calculate how far down the page we are (0.0 to 1.0)
                const scrollPercent = window.scrollY / (document.documentElement.scrollHeight - window.innerHeight);
                
                // 2. Calculate how tall the thumb should be based on page length (minimum 40px)
                const thumbHeight = Math.max((window.innerHeight / document.documentElement.scrollHeight) * window.innerHeight, 40);
                
                // 3. Apply the height and move it down the Y axis
                scrollThumb.style.height = `${thumbHeight}px`;
                scrollThumb.style.transform = `translateY(${scrollPercent * (window.innerHeight - thumbHeight)}px)`;
            }
            
            // Clear the timeout
            clearTimeout(scrollTimeout);
            
            // Remove class after scrolling stops
            scrollTimeout = setTimeout(() => {
                body.classList.remove('scrolling');
            }, 1500);
        });
        
        // Show scrollbar when hovering near the right edge
        document.addEventListener('mousemove', (e) => {
            const windowWidth = window.innerWidth;
            const isNearEdge = e.clientX > windowWidth - 30;
            
            if (isNearEdge) {
                body.classList.add('scrolling');
                
                clearTimeout(scrollTimeout);
                
                scrollTimeout = setTimeout(() => {
                    body.classList.remove('scrolling');
                }, 2000);
            }
        });
    }

    initScrollbar();

    // =========================================
    // INTERACTIVE PARTICLES
    // =========================================
    function createParticles() {
        const container = document.getElementById('particles');
        if (!container) return;
        
        const particleCount = 50;
        
        for (let i = 0; i < particleCount; i++) {
            const particle = document.createElement('div');
            particle.classList.add('particle');
            
            const size = Math.random() * 4 + 2;
            particle.style.width = size + 'px';
            particle.style.height = size + 'px';
            particle.style.left = Math.random() * 100 + '%';
            particle.style.animationDuration = (Math.random() * 20 + 10) + 's';
            particle.style.animationDelay = (Math.random() * 20) + 's';
            particle.style.opacity = Math.random() * 0.5 + 0.1;
            
            container.appendChild(particle);
        }
    }

    createParticles();

    // =========================================
    // PARALLAX ON MOUSE MOVE
    // =========================================
    function initParallax() {
        const downloadSection = document.querySelector('.download');
        if (!downloadSection) return;
        
        let isTouching = false;
        
        document.addEventListener('mousemove', (e) => {
            if (isTouching) return;
            
            const rect = downloadSection.getBoundingClientRect();
            const x = (e.clientX - rect.left) / rect.width - 0.5;
            const y = (e.clientY - rect.top) / rect.height - 0.5;
            
            const orbs = document.querySelectorAll('.orb');
            orbs.forEach((orb, index) => {
                const speed = (index + 1) * 0.5;
                const moveX = x * 30 * speed;
                const moveY = y * 30 * speed;
                orb.style.transform = `translate(${moveX}px, ${moveY}px) scale(${1 + Math.abs(x) * 0.1})`;
            });
            
            const phone = document.querySelector('.mockup-phone');
            if (phone) {
                const rotateX = y * -5;
                const rotateY = x * 5;
                phone.style.transform = `perspective(800px) rotateX(${rotateX}deg) rotateY(${rotateY}deg)`;
            }
        });
        
        document.addEventListener('touchstart', () => {
            isTouching = true;
        });
        
        document.addEventListener('touchend', () => {
            isTouching = false;
        });
    }

    initParallax();

    // =========================================
    // SCROLL REVEAL ANIMATIONS
    // =========================================
    function initScrollReveal() {
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };
        
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);
        
        document.querySelectorAll('section').forEach(section => {
            section.style.opacity = '0';
            section.style.transform = 'translateY(40px)';
            section.style.transition = 'all 0.8s cubic-bezier(0.34, 1.56, 0.64, 1)';
            observer.observe(section);
        });
    }

    initScrollReveal();

    // =========================================
    // FEATURE CARDS 3D HOVER EFFECT
    // =========================================
    function initFeatureCards() {
        document.querySelectorAll('.feature-card').forEach(card => {
            card.addEventListener('mousemove', (e) => {
                const rect = card.getBoundingClientRect();
                const x = (e.clientX - rect.left) / rect.width - 0.5;
                const y = (e.clientY - rect.top) / rect.height - 0.5;
                
                card.style.transform = `perspective(600px) rotateX(${y * -8}deg) rotateY(${x * 8}deg) translateY(-8px)`;
                card.style.transition = 'none';
            });
            
            card.addEventListener('mouseleave', () => {
                card.style.transform = 'translateY(0)';
                card.style.transition = 'all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1)';
            });
        });
    }

    initFeatureCards();

});

// This is the public/js/landing.js file for the landing page of the Luminus website. It includes various interactive features such as mobile navigation toggle, animated counters, smooth scrolling for anchor links, navbar scroll effects, auto-hiding scrollbar, interactive particles, parallax effects on mouse move, scroll reveal animations, and 3D hover effects for feature cards.