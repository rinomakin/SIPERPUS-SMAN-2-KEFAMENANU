<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pengaturan->nama_website ?? 'SIPERPUS' }} - Admin</title>
    
    @if($pengaturan && $pengaturan->favicon)
        <link rel="icon" type="image/x-icon" href="{{ asset('storage/' . $pengaturan->favicon) }}">
    @endif
    
    <!-- Vite CSS & JS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        /* Sidebar Styles */
        .sidebar {
            transition: transform 0.3s ease;
        }
        
        @media (min-width: 1024px) {
            .sidebar {
                transform: translateX(0) !important;
            }
        }
        
        .sidebar-link.active {
            background: linear-gradient(135deg, #215361); 
            color: white;
        }
        
        .sidebar-link:hover {
            background: #215361 ;
            transform: translateX(2px);
            opacity: 1;
        }
        
        .profile-dropdown {
            transform: translateY(-10px);
            opacity: 0;
            visibility: hidden;
            transition: all 0.2s ease;
        }
        
        .profile-menu:hover .profile-dropdown {
            transform: translateY(0);
            opacity: 1;
            visibility: visible;
        }
        
        /* Master Data Dropdown */
        .master-dropdown {
            max-height: 0;
            opacity: 0;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        .master-dropdown.active {
            max-height: 250px;
            opacity: 1;
            overflow-y: auto;
        }
        
        .master-toggle .fa-chevron-down {
            transition: transform 0.3s ease;
        }
        
        .master-toggle.active .fa-chevron-down {
            transform: rotate(180deg);
        }
        
        /* Smooth Scrollbar */
        .custom-scrollbar {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 170, 0.5) transparent;
        }
        
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        
        .custom-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.5);
            border-radius: 3px;
            transition: background 0.3s ease;
        }
        
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.8);
        }
        
        /* Smooth scroll behavior */
        html {
            scroll-behavior: smooth;
        }
        
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl transform -translate-x-full transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 sidebar flex flex-col" id="sidebar">
            <!-- Sidebar Header -->
            <div class="flex items-center justify-between h-16 px-6 bg-gradient-to-r from-blue-600 to-indigo-700 flex-shrink-0">
                <div class="flex items-center space-x-3">
                    <img src="{{ asset($pengaturan->logo ?? 'images/logo.png') }}" alt="Logo" class="h-8 w-8 rounded-lg">
                    <div class="text-white">
                        <h1 class="font-bold text-sm">{{ $pengaturan->nama_website ?? 'SIPERPUS' }}</h1>
                        <p class="text-xs text-blue-100">Admin Panel</p>
                    </div>
                </div>
                <button class="lg:hidden text-white hover:text-gray-200" onclick="toggleSidebar()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <!-- Sidebar Navigation -->
            <nav class="flex-1 px-4 py-6 overflow-y-auto custom-scrollbar min-h-0">
                <div class="space-y-2">
                    <!-- Dashboard -->
                    @if(Auth::user()->hasPermission('dashboard.view') || Auth::user()->isAdmin() || Auth::user()->isKepalaSekolah())
                    <a href="{{ url('/') }}" 
                       class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ (request()->is('/') && (Auth::user()->isAdmin() || Auth::user()->isKepalaSekolah())) ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt mr-3 text-xs"></i>
                        <span class="text-sm font-medium">Dashboard</span>
                    </a>
                    @endif
                    
                      <!-- Master Data Section -->
                    @php
                        $hasMasterPermission = Auth::user()->hasAnyPermission([
                            'role.view', 'role.manage', 'permission.view', 'permission.manage', 
                            'jurusan.view', 'jurusan.manage', 'kelas.view', 'kelas.manage', 
                            'jenis-buku.view', 'jenis-buku.manage', 'kategori-buku.view', 'kategori-buku.manage', 
                            'rak-buku.view', 'rak-buku.manage', 'sumber-buku.view', 'sumber-buku.manage'
                        ]) || Auth::user()->isAdmin();
                        
                        $masterDataActive = request()->routeIs('role.*', 'user.*', 'permissions.*', 'jurusan.*', 'kelas.*', 'jenis-buku.*', 'kategori-buku.*', 'rak-buku.*', 'sumber-buku.*');
                    @endphp
                    
                    @if($hasMasterPermission)
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Master Data</h3>
                        
                        <!-- Master Data Dropdown Toggle -->
                        <button onclick="toggleMasterDropdown()" 
                                class="master-toggle w-full flex items-center justify-between px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 hover:bg-gray-100 {{ $masterDataActive ? 'active bg-blue-50 text-blue-700' : '' }}">
                            <div class="flex items-center">
                                <i class="fas fa-database mr-3 text-xs"></i>
                                <span class="text-sm font-medium">Data Master</span>
                            </div>
                            <i class="fas fa-chevron-down text-xs transition-transform duration-300"></i>
                        </button>
                        
                        <!-- Master Data Dropdown Menu -->
                        <div id="masterDropdown" class="master-dropdown ml-4 mt-2 space-y-1 custom-scrollbar {{ $masterDataActive ? 'active' : '' }}">
                            @if(Auth::user()->hasAnyPermission(['role.view', 'role.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('role.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('role.*') ? 'active' : '' }}">
                                <i class="fas fa-user-shield mr-3 text-xs"></i>
                                <span>Role</span>
                            </a>
                            @endif
                            
                            @if(Auth::user()->hasAnyPermission(['permission.view', 'permission.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('permissions.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                                <i class="fas fa-shield-alt mr-3 text-xs"></i>
                                <span>Hak Akses</span>
                            </a>
                            @endif
                            
                            
                            @if(Auth::user()->hasAnyPermission(['jurusan.view', 'jurusan.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('jurusan.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('jurusan.*') ? 'active' : '' }}">
                                <i class="fas fa-graduation-cap mr-3 text-xs"></i>
                                <span>Data Jurusan</span>
                            </a>
                            @endif
                            
                            @if(Auth::user()->hasAnyPermission(['kelas.view', 'kelas.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('kelas.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('kelas.*') ? 'active' : '' }}">
                                <i class="fas fa-chalkboard mr-3 text-xs"></i>
                                <span>Data Kelas</span>
                            </a>
                            @endif
                            
                            @if(Auth::user()->hasAnyPermission(['jenis-buku.view', 'jenis-buku.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('jenis-buku.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('jenis-buku.*') ? 'active' : '' }}">
                                <i class="fas fa-tags mr-3 text-xs"></i>
                                <span>Jenis Buku</span>
                            </a>
                            @endif
                            
                            @if(Auth::user()->hasAnyPermission(['kategori-buku.view', 'kategori-buku.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('kategori-buku.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('kategori-buku.*') ? 'active' : '' }}">
                                <i class="fas fa-folder mr-3 text-xs"></i>
                                <span>Kategori Buku</span>
                            </a>
                            @endif
                            
                            @if(Auth::user()->hasAnyPermission(['rak-buku.view', 'rak-buku.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('rak-buku.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('rak-buku.*') ? 'active' : '' }}">
                                <i class="fas fa-archive mr-3 text-xs"></i>
                                <span>Rak Buku</span>
                            </a>
                            @endif
                            
                            @if(Auth::user()->hasAnyPermission(['sumber-buku.view', 'sumber-buku.manage']) || Auth::user()->isAdmin())
                            <a href="{{ route('sumber-buku.index') }}" 
                               class="sidebar-link flex items-center px-4 py-2 text-gray-600 rounded-lg transition-all duration-200 text-sm {{ request()->routeIs('sumber-buku.*') ? 'active' : '' }}">
                                <i class="fas fa-building mr-3 text-xs"></i>
                                <span>Sumber Buku</span>
                            </a>
                            @endif
                        </div>
                    </div>
                    @endif
                    <!-- Data Management Section -->
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Manajemen Data</h3>
                        
                        @if(Auth::user()->hasAnyPermission(['user.view', 'user.manage']) || Auth::user()->isAdmin())
                        <a href="{{ route('user.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('user.*') ? 'active' : '' }}">
                            <i class="fas fa-user-cog mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Data User</span>
                        </a>
                        @endif
                        
                        @if(Auth::user()->hasAnyPermission(['anggota.view', 'anggota.manage']) || Auth::user()->isAdmin())
                        <a href="{{ route('anggota.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('anggota.*') ? 'active' : '' }}">
                            <i class="fas fa-users mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Data Anggota</span>
                        </a>
                        @endif
                        
                        @if(Auth::user()->hasAnyPermission(['buku.view', 'buku.manage']) || Auth::user()->isAdmin())
                        <a href="{{ route('buku.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('buku.*') ? 'active' : '' }}">
                            <i class="fas fa-book mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Data Buku</span>
                        </a>
                        @endif
                    </div>
                    
                    <!-- Transaction Section -->
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Transaksi</h3>
                        
                        @if(Auth::user()->hasAnyPermission(['peminjaman.view', 'peminjaman.manage']) || Auth::user()->isAdmin())
                        <a href="{{ route('peminjaman.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('peminjaman.*') ? 'active' : '' }}">
                            <i class="fas fa-hand-holding mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Peminjaman</span>
                        </a>
                        @endif
                        
                        @if(Auth::user()->hasAnyPermission(['pengembalian.view', 'pengembalian.manage']) || Auth::user()->isAdmin())
                        <a href="{{ route('pengembalian.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('pengembalian.*') ? 'active' : '' }}">
                            <i class="fas fa-undo-alt mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Pengembalian</span>
                        </a>
                        @endif
                        
                        @if(Auth::user()->hasAnyPermission(['denda.view', 'denda.manage']) || Auth::user()->isAdmin())
                        <a href="{{ route('admin.denda.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.denda.*') ? 'active' : '' }}">
                            <i class="fas fa-money-bill-wave mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Denda</span>
                        </a>
                        @endif
                        
                        @if(Auth::user()->hasAnyPermission(['buku-tamu.view', 'buku-tamu.manage']) || Auth::user()->isAdmin())
                        <a href="{{ route('admin.buku-tamu.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.buku-tamu.*') ? 'active' : '' }}">
                            <i class="fas fa-clipboard-list mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Buku Tamu</span>
                        </a>
                        @endif
                    </div>
                    
                  
                    
                    <!-- Reports Section -->
                    @php
                        $hasLaporanPermission = Auth::user()->hasAnyPermission([
                            'laporan.view', 'laporan.anggota', 'laporan.buku', 'laporan.peminjaman', 
                            'laporan.pengembalian', 'laporan.denda', 'laporan.kas', 'laporan.absensi'
                        ]) || Auth::user()->isAdmin() || Auth::user()->isKepalaSekolah();
                    @endphp
                    
                    @if($hasLaporanPermission)
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Laporan</h3>
                        
                        <a href="{{ route('laporan.index') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('laporan.*') ? 'active' : '' }}">
                            <i class="fas fa-chart-bar mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Laporan</span>
                        </a>
                    </div>
                    @endif
                    
                    <!-- Settings Section -->
                    @if(Auth::user()->hasAnyPermission(['pengaturan.view', 'pengaturan.manage']) || Auth::user()->isAdmin())
                    <div class="pt-4">
                        <h3 class="px-4 text-xs font-semibold text-gray-400 uppercase tracking-wider mb-2">Pengaturan</h3>
                        
                        <a href="{{ route('admin.pengaturan') }}" 
                           class="sidebar-link flex items-center px-4 py-2.5 text-gray-700 rounded-lg transition-all duration-200 {{ request()->routeIs('admin.pengaturan') ? 'active' : '' }}">
                            <i class="fas fa-cogs mr-3 text-xs"></i>
                            <span class="text-sm font-medium">Pengaturan Website</span>
                        </a>
                    </div>
                    @endif
                </div>
            </nav>
        </div>
        
        <!-- Sidebar Overlay for Mobile -->
        <div class="fixed inset-0 z-40 bg-black bg-opacity-50 lg:hidden hidden" id="sidebarOverlay" onclick="toggleSidebar()"></div>
        
        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden lg:ml-0">
            <!-- Top Header -->
            <header class="bg-white shadow-sm border-b border-gray-200">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-16">
                        <!-- Mobile menu button -->
                        <button class="lg:hidden text-gray-500 hover:text-gray-700" onclick="toggleSidebar()">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        
                        <!-- Page Title -->
                        <div class="flex-1 lg:flex-none">
                            <h1 class="text-xl font-semibold text-gray-800">
                                @yield('page-title', 'Dashboard')
                            </h1>
                        </div>
                        
                        <!-- Right Side - Profile Menu -->
                        <div class="flex items-center space-x-4">
                            <!-- Notifications -->
                            <div class="relative">
                                <button class="p-2 text-gray-400 hover:text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                                    <i class="fas fa-bell text-lg"></i>
                                    <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                                </button>
                            </div>
                            
                            <!-- Profile Dropdown -->
                            <div class="relative profile-menu">
                                <button class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                                    <div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center overflow-hidden">
                                        @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                            <img src="{{ asset('storage/' . Auth::user()->foto) }}" 
                                                 alt="Foto Profil"
                                                 class="w-full h-full object-cover">
                                        @else
                                            <i class="fas fa-user text-white text-sm"></i>
                                        @endif
                                    </div>
                                    <div class="hidden sm:block text-left">
                                        <div class="text-sm font-medium text-gray-700">{{ Auth::user()->nama_panggilan ?: Auth::user()->nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500">{{ Auth::user()->role->nama_peran ?? 'Admin' }}</div>
                                    </div>
                                    <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                                </button>
                                
                                <!-- Dropdown Menu -->
                                <div class="profile-dropdown absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-200 z-50">
                                    <div class="p-4 border-b border-gray-100">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-full flex items-center justify-center overflow-hidden">
                                                @if(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                                    <img src="{{ asset('storage/' . Auth::user()->foto) }}" 
                                                         alt="Foto Profil"
                                                         class="w-full h-full object-cover">
                                                @else
                                                    <i class="fas fa-user text-white"></i>
                                                @endif
                                            </div>
                                            <div>
                                                <div class="font-medium text-gray-900">{{ Auth::user()->nama_lengkap }}</div>
                                                <div class="text-sm text-gray-500">{{ Auth::user()->email }}</div>
                                                <div class="text-xs text-blue-600 font-medium">{{ Auth::user()->role->nama_peran ?? 'Admin' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="py-2">
                                        <a href="{{ route('admin.profil') }}"
                                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-user-circle mr-3 text-gray-400"></i>
                                            <span>Profil Saya</span>
                                        </a>
                                        
                                        @if(Auth::user()->isAdmin())
                                        <a href="{{ route('admin.pengaturan') }}"
                                           class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                            <i class="fas fa-cog mr-3 text-gray-400"></i>
                                            <span>Pengaturan</span>
                                        </a>
                                        @endif
                                        
                                        <hr class="my-2">
                                        
                                        <form method="POST" action="{{ route('logout') }}" class="block">
                                            @csrf
                                            <button type="submit" 
                                                    class="flex w-full items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                                <i class="fas fa-sign-out-alt mr-3"></i>
                                                <span>Logout</span>
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
            <div class="bg-white border-b border-gray-200">
                <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="py-3">
                        <nav class="flex" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-3">
                                <li class="inline-flex items-center">
                                    <a href="{{ url('/') }}" 
                                       class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                                        <i class="fas fa-home mr-2 text-xs"></i>
                                        Dashboard
                                    </a>
                                </li>
                                
                                @if(Request::segment(2) && Request::segment(2) != 'dashboard')
                                <li>
                                    <div class="flex items-center">
                                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                                        @php
                                            $segment2 = Request::segment(2);
                                            $breadcrumbName = '';
                                            
                                            switch($segment2) {
                                                case 'anggota':
                                                    $breadcrumbName = 'Data Anggota';
                                                    break;
                                                case 'buku':
                                                    $breadcrumbName = 'Data Buku';
                                                    break;
                                                case 'peminjaman':
                                                    $breadcrumbName = 'Peminjaman';
                                                    break;
                                                case 'pengembalian':
                                                    $breadcrumbName = 'Pengembalian';
                                                    break;
                                                case 'role':
                                                    $breadcrumbName = 'Role';
                                                    break;
                                                case 'user':
                                                    $breadcrumbName = 'User';
                                                    break;
                                                case 'permissions':
                                                    $breadcrumbName = 'Hak Akses';
                                                    break;
                                                case 'jurusan':
                                                    $breadcrumbName = 'Data Jurusan';
                                                    break;
                                                case 'kelas':
                                                    $breadcrumbName = 'Data Kelas';
                                                    break;
                                                case 'jenis-buku':
                                                    $breadcrumbName = 'Jenis Buku';
                                                    break;
                                                case 'kategori-buku':
                                                    $breadcrumbName = 'Kategori Buku';
                                                    break;
                                                case 'rak-buku':
                                                    $breadcrumbName = 'Rak Buku';
                                                    break;
                                                case 'sumber-buku':
                                                    $breadcrumbName = 'Sumber Buku';
                                                    break;
                                                case 'laporan':
                                                    $breadcrumbName = 'Laporan';
                                                    break;
                                                case 'pengaturan':
                                                    $breadcrumbName = 'Pengaturan Website';
                                                    break;
                                                case 'profil':
                                                    $breadcrumbName = 'Profil';
                                                    break;
                                                default:
                                                    $breadcrumbName = ucfirst(str_replace('-', ' ', $segment2));
                                            }
                                        @endphp
                                        
                                        @if(Request::segment(3))
                                            <a href="{{ url('admin/' . $segment2) }}" 
                                               class="text-sm font-medium text-gray-700 hover:text-blue-600 transition-colors">
                                                {{ $breadcrumbName }}
                                            </a>
                                        @else
                                            <span class="text-sm font-medium text-gray-500">{{ $breadcrumbName }}</span>
                                        @endif
                                    </div>
                                </li>
                                @endif
                                
                                @if(Request::segment(3))
                                <li>
                                    <div class="flex items-center">
                                        <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                                        @php
                                            $segment3 = Request::segment(3);
                                            $actionName = '';
                                            
                                            switch($segment3) {
                                                case 'create':
                                                    $actionName = 'Tambah Data';
                                                    break;
                                                case 'edit':
                                                    $actionName = 'Edit Data';
                                                    break;
                                                case 'show':
                                                    $actionName = 'Detail Data';
                                                    break;
                                                default:
                                                    if(is_numeric($segment3)) {
                                                        $actionName = Request::segment(4) ? ucfirst(Request::segment(4)) : 'Detail';
                                                    } else {
                                                        $actionName = ucfirst(str_replace('-', ' ', $segment3));
                                                    }
                                            }
                                        @endphp
                                        <span class="text-sm font-medium text-blue-600">{{ $actionName }}</span>
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
                <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-6">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }
        
        function toggleMasterDropdown() {
            const dropdown = document.getElementById('masterDropdown');
            const toggle = document.querySelector('.master-toggle');
            
            dropdown.classList.toggle('active');
            toggle.classList.toggle('active');
        }
        
        // Handle responsive sidebar
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth >= 1024) {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.add('hidden');
            } else {
                sidebar.classList.add('-translate-x-full');
            }
        });
        
        // Initialize sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            const masterDropdown = document.getElementById('masterDropdown');
            const masterToggle = document.querySelector('.master-toggle');
            
            // Auto-open master dropdown if any master data page is active
            if (masterDropdown && masterDropdown.classList.contains('active')) {
                masterToggle.classList.add('active');
            }
        });
    </script>
    
    @stack('scripts')
</body>
</html>