<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        (function(){
            var t = localStorage.getItem('theme');
            if(t === 'dark') document.documentElement.setAttribute('data-theme','dark');
        })();
    </script>
    <title>{{ $pengaturan->nama_website ?? 'SIPERPUS' }} - @yield('title', 'Anggota')</title>

    @if($pengaturan && $pengaturan->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset($pengaturan->favicon) }}">
    @endif

    @vite(['resources/css/app.css', 'resources/css/dark-mode.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @stack('styles')

    <style>
        body { font-family: 'Inter', sans-serif; }

        .header-bar {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .profile-dropdown {
            transform: translateY(-8px) scale(0.97);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }
        .profile-dropdown.open {
            transform: translateY(0) scale(1);
            opacity: 1;
            visibility: visible;
            pointer-events: auto;
        }

        :root {
            --primary: #3b82f6;
            --primary-dark: #2563eb;
        }

        /* Dark mode overrides */
        html[data-theme="dark"] body { background: #0f172a; }
        html[data-theme="dark"] .header-bar {
            background: rgba(15,23,42,0.9) !important;
            border-color: rgba(148,163,184,0.15) !important;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Top Header -->
    <header class="header-bar border-b border-gray-200/80 sticky top-0 z-50" style="overflow:visible;">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-14">
                <!-- Left: Logo -->
                <div class="flex items-center gap-3">
                    <a href="{{ route('anggota.dashboard') }}" class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                            @if($pengaturan && $pengaturan->logo)
                                <img src="{{ asset($pengaturan->logo) }}" alt="Logo" class="h-full w-full object-contain p-1">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold">
                                    <i class="fas fa-book"></i>
                                </div>
                            @endif
                        </div>
                        <div class="hidden sm:block">
                            <h1 class="text-sm font-bold text-gray-800 leading-tight">{{ $pengaturan->nama_website ?? 'SIPERPUS' }}</h1>
                            <p class="text-[10px] text-gray-400 font-medium">Portal Anggota</p>
                        </div>
                    </a>
                </div>

                <!-- Right: Actions -->
                <div class="flex items-center gap-2">
                    <!-- Dark Mode -->
                    <button id="themeToggle"
                            class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-500 hover:bg-gray-100 transition-all"
                            title="Toggle Dark Mode">
                        <i class="fas fa-moon text-sm"></i>
                    </button>

                    <!-- Profile Dropdown -->
                    <div class="relative" id="profileMenuContainer" style="overflow:visible;">
                        <button onclick="toggleProfileDropdown(event)"
                                class="flex items-center gap-2.5 px-3 py-1.5 rounded-xl hover:bg-gray-100 transition-all duration-200 border border-transparent hover:border-gray-200"
                                id="profileBtn">
                            <div class="w-8 h-8 rounded-lg overflow-hidden flex-shrink-0" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                                @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                    <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Foto" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold">
                                        {{ strtoupper(substr(Auth::user()->nama_lengkap ?? 'A', 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="hidden sm:block text-left">
                                <div class="text-xs font-medium text-gray-800 leading-tight">{{ Auth::user()->nama_panggilan ?: Auth::user()->nama_lengkap }}</div>
                                <div class="text-[10px] text-gray-400 leading-tight">Anggota</div>
                            </div>
                            <i class="fas fa-chevron-down text-[10px] text-gray-400 hidden sm:block transition-transform duration-200" id="profileChevron"></i>
                        </button>

                        <!-- Dropdown -->
                        <div class="profile-dropdown absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 z-50" id="profileDropdown">
                            <div class="p-3 bg-gradient-to-r from-gray-50 to-gray-100/50">
                                <div class="flex items-center gap-2">
                                    <div class="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                                        @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                            <img src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold">
                                                {{ strtoupper(substr(Auth::user()->nama_lengkap ?? 'A', 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <div class="font-semibold text-xs text-gray-900 truncate">{{ Auth::user()->nama_lengkap }}</div>
                                        <div class="text-[10px] text-gray-500 truncate">{{ Auth::user()->email }}</div>
                                        <span class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                            Anggota
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="py-1">
                                <a href="{{ route('anggota.profil') }}"
                                   class="flex items-center gap-2 px-3 py-2 text-xs text-gray-700 hover:bg-gray-50 transition-colors">
                                    <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                        <i class="fas fa-user text-blue-500 text-[10px]"></i>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-800 text-xs">Profil Saya</div>
                                        <div class="text-[10px] text-gray-400">Kelola data pribadi</div>
                                    </div>
                                </a>
                            </div>
                            <div class="border-t border-gray-100 p-1.5">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit"
                                            class="flex w-full items-center gap-2 px-2.5 py-2 rounded-lg text-xs text-red-600 hover:bg-red-50 transition-colors">
                                        <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center">
                                            <i class="fas fa-sign-out-alt text-red-500 text-[10px]"></i>
                                        </div>
                                        <span class="font-medium">Keluar</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        @yield('content')
    </main>

    <script>
    function toggleProfileDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('profileDropdown');
        const chevron = document.getElementById('profileChevron');
        const isOpen = dropdown.classList.contains('open');
        dropdown.classList.toggle('open');
        if (chevron) chevron.style.transform = isOpen ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    document.addEventListener('click', function(e) {
        const container = document.getElementById('profileMenuContainer');
        if (container && !container.contains(e.target)) {
            const dropdown = document.getElementById('profileDropdown');
            const chevron = document.getElementById('profileChevron');
            if (dropdown) dropdown.classList.remove('open');
            if (chevron) chevron.style.transform = 'rotate(0deg)';
        }
    });

    // Theme Toggle
    (function() {
        var btn = document.getElementById('themeToggle');
        var html = document.documentElement;
        if (!btn) return;
        btn.addEventListener('click', function() {
            var current = html.getAttribute('data-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });
    })();
    </script>

    @stack('scripts')
</body>
</html>
