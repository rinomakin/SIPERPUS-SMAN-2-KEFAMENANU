<!DOCTYPE html>
<html lang="id" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Anti-FOUC: Apply theme before render -->
    <script>
        (function(){
            var t = localStorage.getItem('theme');
            if(t === 'dark') document.documentElement.setAttribute('data-theme','dark');
        })();
    </script>
    <title>{{ $pengaturan->nama_website ?? 'SIPERPUS' }} - Admin</title>

    @if($pengaturan && $pengaturan->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset($pengaturan->favicon) }}">
    @endif

    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/css/dark-mode.css', 'resources/js/app.js', 'resources/js/spa.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <noscript id="spa-styles-start"></noscript>
    @stack('styles')
    <noscript id="spa-styles-end"></noscript>

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        html {
            scroll-behavior: smooth;
        }

        .spa-loader-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 99999;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, #6366f1, #a855f7, #3b82f6);
            background-size: 300% 100%;
            animation: spa-loader 1.2s ease-in-out infinite;
            box-shadow: 0 0 10px rgba(59,130,246,.5);
        }
        @keyframes spa-loader {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .spa-fade {
            animation: spa-fade-in 0.2s ease-out;
        }
        @keyframes spa-fade-in {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* ============ SIDEBAR ============ */
        .sidebar {
            width: 264px;
            min-width: 264px;
            transition: margin-left 0.3s cubic-bezier(0.4, 0, 0.2, 1),
                        transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            will-change: margin-left, transform;
        }

        @media (min-width: 1024px) {
            .sidebar {
                position: relative;
                transform: none;
                z-index: 30;
            }
            .app-wrapper.sidebar-collapsed .sidebar {
                margin-left: -264px;
            }
        }

        @media (max-width: 1023px) {
            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                bottom: 0;
                z-index: 50;
                transform: translateX(-100%);
            }
            .sidebar.mobile-open {
                transform: translateX(0);
            }
        }

        .sidebar-overlay {
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .sidebar-overlay.active {
            opacity: 1;
            visibility: visible;
        }

        /* ============ SIDEBAR NAV ============ */
        .nav-section-label {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.35);
            padding: 0 16px;
            margin-bottom: 4px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 10px;
            font-weight: 500;
            color: rgba(255,255,255,0.7);
            transition: all 0.2s ease;
            position: relative;
            gap: 10px;
        }
        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: #fff;
            transform: translateX(2px);
        }
        .nav-link.active {
            background: rgba(255,255,255,0.15);
            color: #fff;
            font-weight: 600;
        }
        .nav-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 20px;
            background: #fff;
            border-radius: 0 4px 4px 0;
        }
        .nav-link .nav-icon {
            width: 18px;
            text-align: center;
            font-size: 10px;
            opacity: 0.8;
            flex-shrink: 0;
        }
        .nav-link.active .nav-icon {
            opacity: 1;
        }

        /* ============ MASTER DROPDOWN ============ */
        .dropdown-menu {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: max-height 0.35s cubic-bezier(0.4, 0, 0.2, 1),
                        opacity 0.25s ease;
        }
        .dropdown-menu.open {
            max-height: 400px;
            opacity: 1;
        }
        .dropdown-toggle .chevron-icon {
            transition: transform 0.3s ease;
        }
        .dropdown-toggle.open .chevron-icon {
            transform: rotate(180deg);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 5px 12px 5px 40px;
            border-radius: 6px;
            font-size: 10px;
            font-weight: 400;
            color: rgba(255,255,255,0.55);
            transition: all 0.2s ease;
            gap: 8px;
            position: relative;
        }
        .dropdown-item::before {
            content: '';
            position: absolute;
            left: 24px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 4px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            transition: all 0.2s;
        }
        .dropdown-item:hover {
            color: #fff;
            background: rgba(255,255,255,0.08);
        }
        .dropdown-item:hover::before {
            background: rgba(255,255,255,0.6);
        }
        .dropdown-item.active {
            color: #fff;
            font-weight: 500;
        }
        .dropdown-item.active::before {
            background: #fff;
            box-shadow: 0 0 6px rgba(255,255,255,0.4);
        }

        /* ============ PROFILE DROPDOWN ============ */
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

        /* ============ SCROLLBAR ============ */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.15) transparent;
        }
        .custom-scrollbar::-webkit-scrollbar {
            width: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255,255,255,0.15);
            border-radius: 4px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255,255,255,0.3);
        }

        /* ============ TOGGLE BUTTON ============ */
        .sidebar-toggle-btn {
            transition: all 0.2s ease;
        }
        .sidebar-toggle-btn:hover {
            background-color: #f3f4f6;
        }
        .sidebar-toggle-btn .bar {
            display: block;
            width: 18px;
            height: 2px;
            background-color: #6b7280;
            border-radius: 1px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            transform-origin: center;
        }
        .sidebar-toggle-btn.is-active .bar:nth-child(1) {
            transform: translateY(6px) rotate(45deg);
        }
        .sidebar-toggle-btn.is-active .bar:nth-child(2) {
            opacity: 0;
            transform: scaleX(0);
        }
        .sidebar-toggle-btn.is-active .bar:nth-child(3) {
            transform: translateY(-6px) rotate(-45deg);
        }

        /* ============ HEADER ============ */
        .header-bar {
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden app-wrapper" id="appWrapper">
        <!-- Sidebar -->
        <aside class="sidebar flex flex-col" id="sidebar" style="background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-12 px-4 flex-shrink-0 border-b border-white/10">
                <a href="{{ url('/') }}" class="flex items-center gap-2 min-w-0">
                    <div class="w-7 h-7 rounded-lg bg-white/10 flex items-center justify-center flex-shrink-0 overflow-hidden">
                        <img src="{{ asset($pengaturan->logo ?? 'images/logo.png') }}" alt="Logo" class="h-5 w-5 object-contain">
                    </div>
                    <div class="min-w-0">
                        <h1 class="font-bold text-[10px] text-white truncate">{{ $pengaturan->nama_website ?? 'SIPERPUS' }}</h1>
                        <p class="text-[9px] text-white/40 font-medium">
                            @if(Auth::user()->isPetugas()) Petugas Panel
                            @elseif(Auth::user()->isKepalaSekolah()) Kepsek Panel
                            @else Admin Panel
                            @endif
                        </p>
                    </div>
                </a>
                <button class="lg:hidden w-6 h-6 rounded-lg bg-white/10 text-white/70 hover:text-white hover:bg-white/20 flex items-center justify-center transition-colors" onclick="closeSidebarMobile()">
                    <i class="fas fa-times text-[10px]"></i>
                </button>
            </div>

            <!-- Sidebar Navigation -->
            <nav class="flex-1 px-3 py-3 overflow-y-auto custom-scrollbar min-h-0">
                <div class="space-y-1">
                    <!-- Dashboard -->
                    @if(Auth::user()->hasPermission('dashboard.view') || Auth::user()->isAdmin() || Auth::user()->isKepalaSekolah() || Auth::user()->isPetugas())
                    <a href="{{ url('/') }}"
                       class="nav-link {{ (request()->is('/') && !Auth::user()->isPetugas()) || (Auth::user()->isPetugas() && request()->routeIs('petugas.dashboard')) ? 'active' : '' }}">
                        <i class="fas fa-th-large nav-icon"></i>
                        <span>Dashboard</span>
                    </a>
                    @endif
                </div>

                <!-- Master Data Section -->
                @php
                    $hasMasterPermission = Auth::user()->hasAnyPermission([
                        'role.view', 'permission.view',
                        'jurusan.view', 'kelas.view',
                        'jenis-buku.view', 'kategori-buku.view',
                        'rak-buku.view', 'sumber-buku.view'
                    ]) || Auth::user()->isAdmin();

                    $masterDataActive = request()->routeIs('role.*', 'permissions.*', 'jurusan.*', 'kelas.*', 'jenis-buku.*', 'kategori-buku.*', 'rak-buku.*', 'sumber-buku.*');
                @endphp

                @if($hasMasterPermission)
                <div class="mt-4">
                    <div class="nav-section-label">Master Data</div>
                    <div class="space-y-0.5">
                        <button onclick="toggleDropdown('masterDropdown', this)"
                                class="dropdown-toggle nav-link w-full justify-between {{ $masterDataActive ? 'active open' : '' }}">
                            <div class="flex items-center gap-3">
                                <i class="fas fa-database nav-icon"></i>
                                <span>Data Master</span>
                            </div>
                            <i class="fas fa-chevron-down chevron-icon text-[9px] opacity-50"></i>
                        </button>

                        <div id="masterDropdown" class="dropdown-menu {{ $masterDataActive ? 'open' : '' }} space-y-0.5 mt-1">
                            @if(Auth::user()->hasAnyPermission(['role.view']) || Auth::user()->isAdmin())
                            <!-- <a href="{{ route('role.index') }}"
                               class="dropdown-item {{ request()->routeIs('role.*') ? 'active' : '' }}">
                                Data Role
                            </a> -->
                            @endif

                            @if(Auth::user()->hasAnyPermission(['permission.view']) || Auth::user()->isAdmin())
                            <a href="{{ route('permissions.index') }}"
                               class="dropdown-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                                Hak Akses
                            </a>
                            @endif

                            @if(Auth::user()->hasAnyPermission(['jurusan.view']) || Auth::user()->isAdmin())
                            <a href="{{ route('jurusan.index') }}"
                               class="dropdown-item {{ request()->routeIs('jurusan.*') ? 'active' : '' }}">
                                Data Jurusan
                            </a>
                            @endif

                            @if(Auth::user()->hasAnyPermission(['kelas.view']) || Auth::user()->isAdmin())
                            <a href="{{ route('kelas.index') }}"
                               class="dropdown-item {{ request()->routeIs('kelas.*') ? 'active' : '' }}">
                                Data Kelas
                            </a>
                            @endif

                            @if(Auth::user()->hasAnyPermission(['jenis-buku.view']) || Auth::user()->isAdmin())
                            <a href="{{ route('jenis-buku.index') }}"
                               class="dropdown-item {{ request()->routeIs('jenis-buku.*') ? 'active' : '' }}">
                                Jenis Buku
                            </a>
                            @endif

                            @if(Auth::user()->hasAnyPermission(['kategori-buku.view']) || Auth::user()->isAdmin())
                            <a href="{{ route('kategori-buku.index') }}"
                               class="dropdown-item {{ request()->routeIs('kategori-buku.*') ? 'active' : '' }}">
                                Kategori Buku
                            </a>
                            @endif

                            @if(Auth::user()->hasAnyPermission(['rak-buku.view']) || Auth::user()->isAdmin())
                            <a href="{{ route('rak-buku.index') }}"
                               class="dropdown-item {{ request()->routeIs('rak-buku.*') ? 'active' : '' }}">
                                Rak Buku
                            </a>
                            @endif

                            @if(Auth::user()->hasAnyPermission(['sumber-buku.view']) || Auth::user()->isAdmin())
                            <a href="{{ route('sumber-buku.index') }}"
                               class="dropdown-item {{ request()->routeIs('sumber-buku.*') ? 'active' : '' }}">
                                Sumber Buku
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
                @endif

                <!-- Manajemen Data -->
                <div class="mt-4">
                    <div class="nav-section-label">Manajemen Data</div>
                    <div class="space-y-0.5">
                        @if(Auth::user()->hasAnyPermission(['user.view']) || Auth::user()->isAdmin())
                        <a href="{{ route('user.index') }}"
                           class="nav-link {{ request()->routeIs('user.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog nav-icon"></i>
                            <span>Data User</span>
                        </a>
                        @endif

                        @if(Auth::user()->hasAnyPermission(['anggota.view']) || Auth::user()->isAdmin())
                        <a href="{{ route('anggota.index') }}"
                           class="nav-link {{ request()->routeIs('anggota.*') ? 'active' : '' }}">
                            <i class="fas fa-users nav-icon"></i>
                            <span>Data Anggota</span>
                        </a>
                        @endif

                        @if(Auth::user()->hasAnyPermission(['buku.view']) || Auth::user()->isAdmin())
                        <a href="{{ route('buku.index') }}"
                           class="nav-link {{ request()->routeIs('buku.*') ? 'active' : '' }}">
                            <i class="fas fa-book nav-icon"></i>
                            <span>Data Buku</span>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Transaksi -->
                <div class="mt-4">
                    <div class="nav-section-label">Transaksi</div>
                    <div class="space-y-0.5">
                        @if(Auth::user()->hasAnyPermission(['peminjaman.view']) || Auth::user()->isAdmin())
                        <a href="{{ route('peminjaman.index') }}"
                           class="nav-link {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                            <i class="fas fa-hand-holding nav-icon"></i>
                            <span>Peminjaman</span>
                        </a>
                        @endif

                        @if(Auth::user()->hasAnyPermission(['pengembalian.view']) || Auth::user()->isAdmin())
                        <a href="{{ route('pengembalian.index') }}"
                           class="nav-link {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
                            <i class="fas fa-undo-alt nav-icon"></i>
                            <span>Pengembalian</span>
                        </a>
                        @endif

                        @if(Auth::user()->hasAnyPermission(['riwayat-transaksi.view']) || Auth::user()->isAdmin())
                        <!-- <a href="{{ route('riwayat-peminjaman.index') }}"
                           class="nav-link {{ request()->routeIs('riwayat-peminjaman.*') ? 'active' : '' }}">
                            <i class="fas fa-history nav-icon"></i>
                            <span>Riwayat Peminjaman</span>
                        </a> -->
                        @endif

                        @if(Auth::user()->hasAnyPermission(['denda.view']) || Auth::user()->isAdmin())
                        <a href="{{ route('admin.denda.index') }}"
                           class="nav-link {{ request()->routeIs('admin.denda.index', 'admin.denda.create', 'admin.denda.show', 'admin.denda.edit') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave nav-icon"></i>
                            <span>Denda</span>
                        </a>
                        <!-- <a href="{{ route('admin.denda.riwayat') }}"
                           class="nav-link {{ request()->routeIs('admin.denda.riwayat') ? 'active' : '' }}">
                            <i class="fas fa-history nav-icon"></i>
                            <span>Riwayat Denda</span>
                        </a> -->
                        @endif

                        @if(Auth::user()->hasAnyPermission(['buku-tamu.view']) || Auth::user()->isAdmin() || Auth::user()->isPetugas())
                        @php
                            $bukuTamuUrl    = Auth::user()->isPetugas() ? route('petugas.buku-tamu.index') : route('admin.buku-tamu.index');
                            $bukuTamuActive = Auth::user()->isPetugas() ? request()->routeIs('petugas.buku-tamu.*') : request()->routeIs('admin.buku-tamu.*');
                        @endphp
                        <a href="{{ $bukuTamuUrl }}"
                           class="nav-link {{ $bukuTamuActive ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list nav-icon"></i>
                            <span>Buku Tamu</span>
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Laporan -->
                @php
                    $hasLaporanPermission = Auth::user()->hasAnyPermission([
                        'laporan.view', 'laporan.anggota', 'laporan.buku', 'laporan.peminjaman',
                        'laporan.pengembalian', 'laporan.denda', 'laporan.kas', 'laporan.absensi'
                    ]) || Auth::user()->isAdmin() || Auth::user()->isKepalaSekolah();
                @endphp

                @if($hasLaporanPermission)
                <div class="mt-4">
                    <div class="nav-section-label">Laporan</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('laporan.index') }}"
                           class="nav-link {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar nav-icon"></i>
                            <span>Laporan</span>
                        </a>
                    </div>
                </div>
                @endif

                <!-- Pengaturan -->
                @if(Auth::user()->hasAnyPermission(['pengaturan.view']) || Auth::user()->isAdmin())
                <div class="mt-4">
                    <div class="nav-section-label">Pengaturan</div>
                    <div class="space-y-0.5">
                        <a href="{{ route('admin.pengaturan') }}"
                           class="nav-link {{ request()->routeIs('admin.pengaturan') ? 'active' : '' }}">
                            <i class="fas fa-cogs nav-icon"></i>
                            <span>Pengaturan Website</span>
                        </a>
                    </div>
                </div>
                @endif
            </nav>

            <!-- Sidebar Footer -->
            <div class="hidden lg:block border-t border-white/10 p-3 flex-shrink-0">
                <button onclick="toggleSidebarDesktop()"
                        class="w-full flex items-center justify-center gap-2 px-3 py-1.5 text-white/40 hover:text-white/70 hover:bg-white/5 rounded-lg transition-colors text-[10px] font-medium"
                        title="Sembunyikan sidebar">
                    <i class="fas fa-chevron-left text-[9px] transition-transform duration-300" id="collapseIcon"></i>
                    <span>Tutup Sidebar</span>
                </button>
            </div>
        </aside>

        <!-- Sidebar Overlay (mobile) -->
        <div class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden sidebar-overlay" id="sidebarOverlay" onclick="closeSidebarMobile()"></div>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden main-content">
            <!-- Top Header -->
            <header class="header-bar border-b border-gray-200/80 flex-shrink-0 z-20">
                <div class="max-w-full mx-auto px-3 sm:px-4 lg:px-6">
                    <div class="flex justify-between items-center h-12">
                        <!-- Left: Toggle + Title -->
                        <div class="flex items-center gap-2">
                            <button class="sidebar-toggle-btn p-1.5 rounded-lg flex flex-col justify-center items-center space-y-1"
                                    id="sidebarToggleBtn"
                                    onclick="toggleSidebar()"
                                    title="Toggle Sidebar">
                                <span class="bar"></span>
                                <span class="bar"></span>
                                <span class="bar"></span>
                            </button>

                            <div>
                                <h1 class="text-xs font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                            </div>
                        </div>

                        <!-- Right: Dark Mode + Profile -->
                        <div class="flex items-center gap-1.5">
                            <!-- Dark Mode Toggle -->
                            <button id="adminThemeToggle" class="dark-toggle-btn" title="Toggle Dark Mode (Ctrl+Shift+D)">
                                <i class="fas fa-moon icon-moon"></i>
                                <i class="fas fa-sun icon-sun"></i>
                            </button>
                            <!-- Profile -->
                            <div class="relative" id="profileMenuContainer">
                                <button onclick="toggleProfileDropdown(event)"
                                        class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-100 transition-all duration-200" id="profileBtn">
                                    <div class="w-7 h-7 rounded-lg overflow-hidden flex-shrink-0" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                                        @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                            <img src="{{ asset('storage/' . Auth::user()->foto) }}"
                                                 alt="Foto" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-white text-[10px] font-bold">
                                                {{ strtoupper(substr(Auth::user()->nama_lengkap ?? 'A', 0, 1)) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <div class="text-[10px] font-medium text-gray-800 leading-tight">{{ Auth::user()->nama_panggilan ?: Auth::user()->nama_lengkap }}</div>
                                        <div class="text-[10px] text-gray-400 leading-tight">{{ Auth::user()->role->nama_peran ?? 'Admin' }}</div>
                                    </div>
                                    <i class="fas fa-chevron-down text-[9px] text-gray-400 hidden sm:block transition-transform duration-200" id="profileChevron"></i>
                                </button>

                                <!-- Profile Dropdown -->
                                <div class="profile-dropdown absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-gray-100 z-50 overflow-hidden" id="profileDropdown">
                                    <!-- User Info -->
                                    <div class="p-3 bg-gradient-to-r from-gray-50 to-gray-100/50">
                                        <div class="flex items-center gap-2">
                                            <div class="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                                                @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                                    <img src="{{ asset('storage/' . Auth::user()->foto) }}"
                                                         alt="Foto" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center text-white text-xs font-bold">
                                                        {{ strtoupper(substr(Auth::user()->nama_lengkap ?? 'A', 0, 1)) }}
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="font-semibold text-[10px] text-gray-900 truncate">{{ Auth::user()->nama_lengkap }}</div>
                                                <div class="text-[10px] text-gray-500 truncate">{{ Auth::user()->email }}</div>
                                                <span class="inline-flex items-center gap-1 mt-0.5 px-1.5 py-0.5 rounded text-[9px] font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                                    {{ Auth::user()->role->nama_peran ?? 'Admin' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="py-1">
                                        <a href="{{ Auth::user()->isPetugas() ? route('petugas.profil') : route('admin.profil') }}"
                                           class="flex items-center gap-2 px-3 py-2 text-[10px] text-gray-700 hover:bg-gray-50 transition-colors">
                                            <div class="w-7 h-7 rounded-lg bg-blue-50 flex items-center justify-center">
                                                <i class="fas fa-user text-blue-500 text-[10px]"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-800 text-[10px]">Profil Saya</div>
                                                <div class="text-[9px] text-gray-400">Kelola informasi akun</div>
                                            </div>
                                        </a>

                                        @if(Auth::user()->isAdmin())
                                        <a href="{{ route('admin.pengaturan') }}"
                                           class="flex items-center gap-2 px-3 py-2 text-[10px] text-gray-700 hover:bg-gray-50 transition-colors">
                                            <div class="w-7 h-7 rounded-lg bg-gray-100 flex items-center justify-center">
                                                <i class="fas fa-cog text-gray-500 text-[10px]"></i>
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-800 text-[10px]">Pengaturan</div>
                                                <div class="text-[9px] text-gray-400">Konfigurasi website</div>
                                            </div>
                                        </a>
                                        @endif
                                    </div>

                                    <!-- Logout -->
                                    <div class="border-t border-gray-100 p-1.5">
                                        <form method="POST" action="{{ route('logout') }}" data-spa-ignore>
                                            @csrf
                                            <button type="submit"
                                                    class="flex w-full items-center gap-2 px-2.5 py-2 rounded-lg text-[10px] text-red-600 hover:bg-red-50 transition-colors">
                                                <div class="w-7 h-7 rounded-lg bg-red-50 flex items-center justify-center">
                                                    <i class="fas fa-sign-out-alt text-red-500 text-[10px]"></i>
                                                </div>
                                                <span class="font-medium text-[10px]">Keluar</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Breadcrumb Navigation -->
            <div class="bg-white/60 border-b border-gray-200/60 flex-shrink-0" style="backdrop-filter:blur(8px);">
                <div class="max-w-full mx-auto px-3 sm:px-4 lg:px-6">
                    <div class="py-1.5">
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1">
                                <li class="inline-flex items-center">
                                    <a href="{{ url('/') }}"
                                       class="inline-flex items-center text-[10px] font-medium text-gray-500 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-home mr-1 text-[9px]"></i>
                                        Dashboard
                                    </a>
                                </li>

                                @if(Request::segment(2) && Request::segment(2) != 'dashboard')
                                <li>
                                    <div class="flex items-center">
                                        <i class="fas fa-chevron-right text-gray-300 text-[7px] mx-1"></i>
                                        @php
                                            $segment2 = Request::segment(2);
                                            $breadcrumbName = match($segment2) {
                                                'anggota' => 'Data Anggota',
                                                'buku' => 'Data Buku',
                                                'peminjaman' => 'Peminjaman',
                                                'pengembalian' => 'Pengembalian',
                                                'role' => 'Role',
                                                'user' => 'User',
                                                'permissions' => 'Hak Akses',
                                                'jurusan' => 'Data Jurusan',
                                                'kelas' => 'Data Kelas',
                                                'jenis-buku' => 'Jenis Buku',
                                                'kategori-buku' => 'Kategori Buku',
                                                'rak-buku' => 'Rak Buku',
                                                'sumber-buku' => 'Sumber Buku',
                                                'laporan' => 'Laporan',
                                                'pengaturan' => 'Pengaturan Website',
                                                'profil' => 'Profil',
                                                default => ucfirst(str_replace('-', ' ', $segment2))
                                            };
                                        @endphp

                                        @if(Request::segment(3))
                                            @php $breadcrumbBase = Auth::user()->isPetugas() ? 'petugas' : 'admin'; @endphp
                                            <a href="{{ url($breadcrumbBase . '/' . $segment2) }}"
                                               class="text-[10px] font-medium text-gray-500 hover:text-blue-600 transition-colors">
                                                {{ $breadcrumbName }}
                                            </a>
                                        @else
                                            <span class="text-[10px] font-medium text-gray-400">{{ $breadcrumbName }}</span>
                                        @endif
                                    </div>
                                </li>
                                @endif

                                @if(Request::segment(3))
                                <li>
                                    <div class="flex items-center">
                                        <i class="fas fa-chevron-right text-gray-300 text-[7px] mx-1"></i>
                                        @php
                                            $segment3 = Request::segment(3);
                                            $actionName = match($segment3) {
                                                'create' => 'Tambah Data',
                                                'edit' => 'Edit Data',
                                                'show' => 'Detail Data',
                                                default => is_numeric($segment3)
                                                    ? (Request::segment(4) ? ucfirst(Request::segment(4)) : 'Detail')
                                                    : ucfirst(str_replace('-', ' ', $segment3))
                                            };
                                        @endphp
                                        <span class="text-[10px] font-medium text-blue-600">{{ $actionName }}</span>
                                    </div>
                                </li>
                                @endif
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>

            <!-- Page Content -->
            <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-50">
                <div class="container mx-auto px-3 sm:px-4 lg:px-6 py-4">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <script>
    (function() {
        const STORAGE_KEY = 'sidebar_collapsed';
        const wrapper = document.getElementById('appWrapper');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebarOverlay');
        const toggleBtn = document.getElementById('sidebarToggleBtn');
        const collapseIcon = document.getElementById('collapseIcon');

        function isDesktop() {
            return window.innerWidth >= 1024;
        }

        function isSidebarCollapsed() {
            return wrapper.classList.contains('sidebar-collapsed');
        }

        function saveSidebarState(collapsed) {
            try { localStorage.setItem(STORAGE_KEY, collapsed ? '1' : '0'); } catch(e) {}
        }

        function getSavedState() {
            try { return localStorage.getItem(STORAGE_KEY); } catch(e) { return null; }
        }

        function updateToggleBtn(collapsed) {
            if (collapsed) {
                toggleBtn.classList.remove('is-active');
            } else {
                toggleBtn.classList.add('is-active');
            }
        }

        function updateCollapseIcon(collapsed) {
            if (collapseIcon) {
                collapseIcon.style.transform = collapsed ? 'rotate(180deg)' : 'rotate(0deg)';
            }
        }

        // ---- Desktop toggle ----
        window.toggleSidebarDesktop = function() {
            if (!isDesktop()) return;
            const willCollapse = !isSidebarCollapsed();
            wrapper.classList.toggle('sidebar-collapsed', willCollapse);
            saveSidebarState(willCollapse);
            updateToggleBtn(willCollapse);
            updateCollapseIcon(willCollapse);
        };

        // ---- Mobile toggle ----
        function openSidebarMobile() {
            sidebar.classList.add('mobile-open');
            overlay.classList.add('active');
        }

        window.closeSidebarMobile = function() {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        };

        window.toggleSidebar = function() {
            if (isDesktop()) {
                window.toggleSidebarDesktop();
            } else {
                if (sidebar.classList.contains('mobile-open')) {
                    window.closeSidebarMobile();
                } else {
                    openSidebarMobile();
                }
            }
        };

        // ---- Dropdown Toggle (Master Data, etc.) ----
        window.toggleDropdown = function(dropdownId, toggleEl) {
            const dropdown = document.getElementById(dropdownId);
            if (!dropdown) return;

            const isOpen = dropdown.classList.contains('open');

            // Close all other dropdowns first
            document.querySelectorAll('.dropdown-menu.open').forEach(function(el) {
                if (el.id !== dropdownId) {
                    el.classList.remove('open');
                    // Also remove 'open' class from corresponding toggle
                    const otherToggle = el.previousElementSibling;
                    if (otherToggle && otherToggle.classList.contains('dropdown-toggle')) {
                        otherToggle.classList.remove('open');
                    }
                }
            });

            // Toggle this one
            if (isOpen) {
                dropdown.classList.remove('open');
                toggleEl.classList.remove('open');
            } else {
                dropdown.classList.add('open');
                toggleEl.classList.add('open');
            }
        };

        // ---- Profile Dropdown (click-based) ----
        window.toggleProfileDropdown = function(e) {
            e.stopPropagation();
            const dropdown = document.getElementById('profileDropdown');
            const chevron = document.getElementById('profileChevron');
            const isOpen = dropdown.classList.contains('open');

            if (isOpen) {
                dropdown.classList.remove('open');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            } else {
                dropdown.classList.add('open');
                if (chevron) chevron.style.transform = 'rotate(180deg)';
            }
        };

        // Close profile dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const container = document.getElementById('profileMenuContainer');
            const dropdown = document.getElementById('profileDropdown');
            const chevron = document.getElementById('profileChevron');

            if (container && dropdown && !container.contains(e.target)) {
                dropdown.classList.remove('open');
                if (chevron) chevron.style.transform = 'rotate(0deg)';
            }
        });

        // ---- Initialize ----
        function init() {
            if (isDesktop()) {
                const saved = getSavedState();
                const collapsed = saved === '1';
                wrapper.classList.toggle('sidebar-collapsed', collapsed);
                updateToggleBtn(collapsed);
                updateCollapseIcon(collapsed);
            } else {
                updateToggleBtn(true);
            }

            // Auto-open master dropdown if active
            document.querySelectorAll('.dropdown-menu').forEach(function(dropdown) {
                if (dropdown.classList.contains('open')) {
                    const toggle = dropdown.previousElementSibling;
                    if (toggle && toggle.classList.contains('dropdown-toggle')) {
                        toggle.classList.add('open');
                    }
                }
            });
        }

        // ---- Handle resize ----
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                if (isDesktop()) {
                    sidebar.classList.remove('mobile-open');
                    overlay.classList.remove('active');
                    const saved = getSavedState();
                    const collapsed = saved === '1';
                    wrapper.classList.toggle('sidebar-collapsed', collapsed);
                    updateToggleBtn(collapsed);
                    updateCollapseIcon(collapsed);
                } else {
                    wrapper.classList.remove('sidebar-collapsed');
                    updateToggleBtn(true);
                }
            }, 100);
        });

        // Escape key closes dropdowns and mobile sidebar
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                // Close profile dropdown
                const profileDropdown = document.getElementById('profileDropdown');
                const chevron = document.getElementById('profileChevron');
                if (profileDropdown) profileDropdown.classList.remove('open');
                if (chevron) chevron.style.transform = 'rotate(0deg)';

                // Close mobile sidebar
                if (!isDesktop()) {
                    window.closeSidebarMobile();
                }
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', init);
        } else {
            init();
        }
    })();
    </script>

    <script>
    (function() {
        var btn = document.getElementById('adminThemeToggle');
        var html = document.documentElement;
        if (!btn) return;
        btn.addEventListener('click', function() {
            var current = html.getAttribute('data-theme') || 'light';
            var next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        });
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey && e.shiftKey && e.key === 'D') {
                e.preventDefault();
                btn.click();
            }
        });
    })();
    </script>
    <div id="spa-scripts">
        @stack('scripts')
    </div>
</body>
</html>
