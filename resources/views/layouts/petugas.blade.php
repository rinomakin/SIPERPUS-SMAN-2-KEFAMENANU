<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pengaturan->nama_website ?? 'SIPERPUS' }} - Petugas</title>
    
    @if($pengaturan && $pengaturan->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $pengaturan->favicon) }}">
    @endif
    
    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/css/modern-components.css', 'resources/css/dark-mode.css', 'resources/css/animations.css', 'resources/js/app.js'])
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Modern Design System Variables */
        :root {
            --primary-gradient: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --secondary-gradient: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --card-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            --card-shadow-hover: 0 20px 40px -5px rgba(0, 0, 0, 0.15), 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            --glassmorphism: rgba(255, 255, 255, 0.25);
            --backdrop-blur: blur(10px);
        }

        /* Advanced Profile Dropdown */
        .profile-dropdown {
            transform: translateY(-10px) scale(0.95);
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: var(--backdrop-blur);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .profile-menu:hover .profile-dropdown {
            transform: translateY(0) scale(1);
            opacity: 1;
            visibility: visible;
        }

        /* Modern Navigation Effects */
        .nav-link {
            position: relative;
            overflow: hidden;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, var(--glassmorphism), transparent);
            transition: left 0.6s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        /* Glassmorphism Cards */
        .glass-card {
            background: var(--glassmorphism);
            backdrop-filter: var(--backdrop-blur);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--card-shadow);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            box-shadow: var(--card-shadow-hover);
            transform: translateY(-2px);
        }

        /* Modern Notification Badge */
        .notification-badge {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        /* Smooth Mobile Menu Animation */
        .mobile-menu {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease-out;
        }

        .mobile-menu.active {
            max-height: 400px;
        }

        /* Modern Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #10b981, #059669);
            border-radius: 3px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #059669, #047857);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
        }

        /* Page Transition */
        .page-transition {
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 via-blue-50 to-green-50">
    <!-- Modern Top Navigation Bar with Glassmorphism -->
    <nav class="glass-card sticky top-0 z-50 border-0 rounded-none" style="background: var(--primary-gradient); backdrop-filter: var(--backdrop-blur);">
        <div class="max-w-full mx-auto px-4">
            <div class="flex items-center justify-between h-16">
                <!-- Logo dan Brand -->
                <div class="flex items-center space-x-4">
                    <a href="{{ route('petugas.dashboard') }}" class="flex items-center space-x-3">
                        <img src="{{ asset($pengaturan->logo ?? 'images/logo.png') }}" alt="Logo" class="h-10 w-auto">
                        <div>
                            <h1 class="font-bold text-lg">{{ $pengaturan->nama_website ?? 'SIPERPUS' }}</h1>
                            <p class="text-xs text-green-100">Petugas Panel</p>
                        </div>
                    </a>
                </div>
                
                <!-- Enhanced Navigation Menu -->
                <div class="hidden md:flex items-center space-x-1">
                    <a href="{{ route('petugas.dashboard') }}" 
                       class="nav-link flex items-center gap-2 px-4 py-2.5 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('petugas.dashboard') ? 'bg-white bg-opacity-25 shadow-lg' : '' }}">
                        <i class="fas fa-tachometer-alt text-sm"></i>
                        <span class="font-medium">Dashboard</span>
                    </a>

                    <a href="{{ route('petugas.beranda') }}" 
                       class="nav-link flex items-center gap-2 px-4 py-2.5 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('petugas.beranda') ? 'bg-white bg-opacity-25 shadow-lg' : '' }}">
                        <i class="fas fa-home text-sm"></i>
                        <span class="font-medium">Beranda</span>
                    </a>

                    <a href="{{ route('petugas.tentang') }}" 
                       class="nav-link flex items-center gap-2 px-4 py-2.5 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('petugas.tentang') ? 'bg-white bg-opacity-25 shadow-lg' : '' }}">
                        <i class="fas fa-info-circle text-sm"></i>
                        <span class="font-medium">Tentang</span>
                    </a>

                    <a href="{{ route('petugas.buku-tamu.index') }}" 
                       class="nav-link flex items-center gap-2 px-4 py-2.5 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-105 {{ request()->routeIs('petugas.buku-tamu.*') ? 'bg-white bg-opacity-25 shadow-lg' : '' }}">
                        <i class="fas fa-clipboard-list text-sm"></i>
                        <span class="font-medium">Buku Tamu</span>
                    </a>
                </div>

                <!-- Enhanced Mobile menu button -->
                <div class="md:hidden">
                    <button id="mobile-menu-button" 
                            class="glass-card p-3 rounded-xl hover:bg-white hover:bg-opacity-30 transition-all duration-300 transform hover:scale-110">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                </div>

                <!-- Enhanced Right side - Profile Menu -->
                <div class="flex items-center space-x-3">
                    <!-- Dark Mode Toggle -->
                    <div class="relative">
                        <button id="themeToggle" class="theme-toggle" title="Toggle Dark Mode">
                        </button>
                    </div>

                    <!-- Modern Notifications -->
                    <div class="relative">
                        <button class="glass-card p-3 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:scale-110 group">
                            <i class="fas fa-bell text-lg group-hover:rotate-12 transition-transform duration-300"></i>
                            <span class="notification-badge absolute -top-1 -right-1 w-3 h-3 bg-red-500 rounded-full flex items-center justify-center">
                                <span class="w-2 h-2 bg-white rounded-full"></span>
                            </span>
                        </button>
                    </div>

                    <!-- Enhanced Profile Dropdown -->
                    <div class="relative profile-menu">
                        <button class="glass-card flex items-center space-x-3 hover:bg-white hover:bg-opacity-20 px-4 py-2.5 rounded-xl transition-all duration-300 transform hover:scale-105">
                            <div class="w-10 h-10 bg-white bg-opacity-30 rounded-full flex items-center justify-center overflow-hidden ring-2 ring-white ring-opacity-50">
                                @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                    <img src="{{ asset('storage/' . Auth::user()->foto) }}" 
                                         alt="Foto Profil"
                                         class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-user text-white text-sm"></i>
                                @endif
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-sm font-semibold text-white">{{ Auth::user()->nama_panggilan ?: Auth::user()->nama_lengkap }}</div>
                                <div class="text-xs text-green-100 font-medium">{{ Auth::user()->role->nama_peran ?? 'Petugas' }}</div>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-white transition-transform duration-300 group-hover:rotate-180"></i>
                        </button>
                        
                        <!-- Enhanced Profile Dropdown Menu -->
                        <div class="profile-dropdown absolute right-0 mt-3 w-72 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden">
                            <!-- Profile Header -->
                            <div class="p-6 bg-gradient-to-r from-green-500 to-teal-600 text-white">
                                <div class="flex items-center space-x-4">
                                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center overflow-hidden ring-4 ring-white ring-opacity-30">
                                        @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                            <img src="{{ asset('storage/' . Auth::user()->foto) }}" 
                                                 alt="Foto Profil"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-user text-white text-xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-semibold text-lg">{{ Auth::user()->nama_lengkap }}</div>
                                        <div class="text-sm text-green-100">{{ Auth::user()->email }}</div>
                                        <div class="text-xs bg-white bg-opacity-20 px-2 py-1 rounded-full mt-1 inline-block font-medium">{{ Auth::user()->role->nama_peran ?? 'Petugas' }}</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Menu Items -->
                            <div class="py-2">
                                <a href="{{ route('petugas.profil') }}"
                                   class="flex items-center px-6 py-4 text-gray-700 hover:bg-gradient-to-r hover:from-green-50 hover:to-teal-50 transition-all duration-300 group">
                                    <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-4 group-hover:bg-green-200 transition-colors">
                                        <i class="fas fa-user-circle text-green-600"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium">Profil Saya</div>
                                        <div class="text-xs text-gray-500">Kelola informasi akun</div>
                                    </div>
                                </a>
                                
                                <hr class="my-2 border-gray-100">
                                
                                <form method="POST" action="{{ route('logout') }}" class="block">
                                    @csrf
                                    <button type="submit" 
                                            class="flex w-full items-center px-6 py-4 text-red-600 hover:bg-red-50 transition-all duration-300 group">
                                        <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center mr-4 group-hover:bg-red-200 transition-colors">
                                            <i class="fas fa-sign-out-alt text-red-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium">Logout</div>
                                            <div class="text-xs text-red-400">Keluar dari sistem</div>
                                        </div>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Mobile Menu -->
        <div id="mobileMenu" class="mobile-menu md:hidden overflow-hidden" style="background: linear-gradient(135deg, #059669 0%, #047857 100%); border-top: 1px solid rgba(255, 255, 255, 0.2);">
            <div class="px-4 py-4 space-y-2">
                <a href="{{ route('petugas.dashboard') }}" 
                   class="nav-link flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('petugas.dashboard') ? 'bg-white bg-opacity-25' : '' }}">
                    <i class="fas fa-tachometer-alt w-5 text-center"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                <a href="{{ route('petugas.beranda') }}" 
                   class="nav-link flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('petugas.beranda') ? 'bg-white bg-opacity-25' : '' }}">
                    <i class="fas fa-home w-5 text-center"></i>
                    <span class="font-medium">Beranda</span>
                </a>
                <a href="{{ route('petugas.tentang') }}" 
                   class="nav-link flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('petugas.tentang') ? 'bg-white bg-opacity-25' : '' }}">
                    <i class="fas fa-info-circle w-5 text-center"></i>
                    <span class="font-medium">Tentang</span>
                </a>
                <a href="{{ route('petugas.buku-tamu.index') }}" 
                   class="nav-link flex items-center space-x-3 px-4 py-3 rounded-xl hover:bg-white hover:bg-opacity-20 transition-all duration-300 transform hover:translate-x-2 {{ request()->routeIs('petugas.buku-tamu.*') ? 'bg-white bg-opacity-25' : '' }}">
                    <i class="fas fa-clipboard-list w-5 text-center"></i>
                    <span class="font-medium">Buku Tamu</span>
                </a>
            </div>
        </div>
    </nav>

    <!-- Enhanced Main Content with Page Transition -->
    <main class="min-h-screen">
        <div class="page-transition container mx-auto px-4 sm:px-6 lg:px-8 py-8">
            @yield('content')
        </div>
    </main>

    <!-- Enhanced Mobile menu toggle script -->
    <script>
        // Dark Mode Toggle Functionality
        const themeToggle = document.getElementById('themeToggle');
        const htmlElement = document.documentElement;
        
        // Check for saved theme preference or default to 'light'
        const currentTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', currentTheme);
        
        themeToggle.addEventListener('click', function() {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
            
            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            
            // Add smooth transition effect
            document.body.style.transition = 'all 0.3s ease';
            setTimeout(() => {
                document.body.style.transition = '';
            }, 300);
        });

        // Enhanced mobile menu with smooth animations
        document.getElementById('mobile-menu-button').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobileMenu');
            const button = this;
            const icon = button.querySelector('i');
            
            mobileMenu.classList.toggle('active');
            
            // Animate hamburger to X
            if (mobileMenu.classList.contains('active')) {
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                button.style.transform = 'rotate(90deg)';
            } else {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                button.style.transform = 'rotate(0deg)';
            }
        });

        // Enhanced notification interactions
        document.addEventListener('DOMContentLoaded', function() {
            // Add ripple effect to clickable elements
            const clickableElements = document.querySelectorAll('.nav-link, .glass-card button, .btn-modern');
            
            clickableElements.forEach(element => {
                element.addEventListener('click', function(e) {
                    // Skip ripple for theme toggle
                    if (this.id === 'themeToggle') return;
                    
                    const ripple = document.createElement('div');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 255, 255, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        pointer-events: none;
                        z-index: 0;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
            
            // Add CSS for ripple animation
            const style = document.createElement('style');
            style.textContent = `
                @keyframes ripple {
                    to {
                        transform: scale(4);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
            
            // Keyboard shortcuts for accessibility
            document.addEventListener('keydown', function(e) {
                // Toggle dark mode with Ctrl + Shift + D
                if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                    e.preventDefault();
                    themeToggle.click();
                }
                
                // Focus management
                if (e.key === 'Tab') {
                    document.activeElement.classList.add('focus-visible');
                }
            });
            
            // Remove focus-visible class on mouse click
            document.addEventListener('mousedown', function() {
                document.querySelectorAll('.focus-visible').forEach(el => {
                    el.classList.remove('focus-visible');
                });
            });
            
            // Scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        entry.target.classList.add('animate-fadeInUp');
                    }
                });
            }, observerOptions);
            
            // Observe elements for scroll animations
            document.querySelectorAll('.modern-card, .stat-card, .animate-stagger > *').forEach(el => {
                el.classList.add('reveal-on-scroll');
                observer.observe(el);
            });
            
            // Enhanced button interactions
            document.querySelectorAll('.btn-modern').forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.classList.add('btn-hover-lift');
                });
                
                button.addEventListener('mouseleave', function() {
                    this.classList.remove('btn-hover-lift');
                });
                
                button.addEventListener('click', function() {
                    this.classList.add('animate-pulse');
                    setTimeout(() => {
                        this.classList.remove('animate-pulse');
                    }, 600);
                });
            });
            
            // Enhanced card interactions
            document.querySelectorAll('.modern-card, .stat-card').forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('card-hover-float');
                });
                
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('card-hover-float');
                });
            });
            
            // Icon animations on hover
            document.querySelectorAll('.fas, .far, .fab').forEach(icon => {
                const parent = icon.closest('button, a, .nav-link');
                if (parent) {
                    parent.addEventListener('mouseenter', function() {
                        icon.classList.add('icon-bounce');
                    });
                    
                    parent.addEventListener('mouseleave', function() {
                        icon.classList.remove('icon-bounce');
                    });
                }
            });
            
            // Enhanced notification bell animation
            const bellIcon = document.querySelector('.fa-bell');
            if (bellIcon) {
                bellIcon.closest('button').addEventListener('click', function() {
                    bellIcon.classList.add('notification-bell');
                    setTimeout(() => {
                        bellIcon.classList.remove('notification-bell');
                    }, 1000);
                });
            }
            
            // Auto-save scroll position
            window.addEventListener('beforeunload', function() {
                localStorage.setItem('scrollPosition', window.scrollY);
            });
            
            // Restore scroll position
            const savedScrollPosition = localStorage.getItem('scrollPosition');
            if (savedScrollPosition) {
                window.scrollTo(0, parseInt(savedScrollPosition));
                localStorage.removeItem('scrollPosition');
            }
            
            // Performance optimization: Debounced scroll handler
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                if (scrollTimeout) {
                    cancelAnimationFrame(scrollTimeout);
                }
                
                scrollTimeout = requestAnimationFrame(function() {
                    // Add scroll-based effects here if needed
                    const scrolled = window.scrollY;
                    const navbar = document.querySelector('nav');
                    
                    if (scrolled > 10) {
                        navbar.style.backdropFilter = 'blur(15px)';
                        navbar.style.backgroundColor = 'rgba(16, 185, 129, 0.95)';
                    } else {
                        navbar.style.backdropFilter = 'blur(10px)';
                        navbar.style.backgroundColor = 'rgba(16, 185, 129, 0.9)';
                    }
                });
            });
        });
    </script>
    
    @stack('scripts')
</body>
</html>
