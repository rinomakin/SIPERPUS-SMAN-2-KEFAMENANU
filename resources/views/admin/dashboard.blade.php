@extends('layouts.admin')

@section('title', 'Dashboard Admin')

@push('styles')
<style>
    /* Animations */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes countUp {
        from { opacity: 0; transform: scale(0.5); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes slideInRight {
        from { opacity: 0; transform: translateX(30px); }
        to { opacity: 1; transform: translateX(0); }
    }
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0px); }
        50% { transform: translateY(-8px); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .animate-slide-in-right {
        animation: slideInRight 0.5s ease-out forwards;
    }
    .animate-float {
        animation: float 3s ease-in-out infinite;
    }
    .animate-count-up {
        animation: countUp 0.5s ease-out forwards;
    }
    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }

    /* Stat card hover */
    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.15);
    }

    /* Activity item hover */
    .activity-item {
        transition: all 0.2s ease;
    }
    .activity-item:hover {
        transform: translateX(4px);
    }

    /* Quick action hover */
    .quick-action {
        transition: all 0.3s ease;
    }
    .quick-action:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
    }

    /* Rank badge */
    .rank-badge {
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        font-size: 12px;
        font-weight: 700;
    }

    /* Progress ring */
    .progress-ring {
        transition: stroke-dashoffset 1s ease-in-out;
    }

    /* Glassmorphism card */
    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }
</style>
@endpush

@section('content')
<div class="space-y-6">

    <!-- Welcome Hero Section -->
    <div class="relative overflow-hidden rounded-2xl shadow-lg animate-fade-in-up">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700"></div>
        <!-- Decorative elements -->
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>
        <div class="absolute top-1/2 right-10 -translate-y-1/2 hidden lg:block animate-float">
            <div class="text-white/10">
                <i class="fas fa-book-reader" style="font-size: 8rem;"></i>
            </div>
        </div>

        <div class="relative z-10 px-6 py-8 md:px-8 md:py-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="text-blue-200 text-sm font-medium mb-1">
                        @php
                            $hour = now()->format('H');
                            if ($hour < 12) $greeting = 'Selamat Pagi';
                            elseif ($hour < 15) $greeting = 'Selamat Siang';
                            elseif ($hour < 18) $greeting = 'Selamat Sore';
                            else $greeting = 'Selamat Malam';
                        @endphp
                        {{ $greeting }},
                    </p>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                        {{ Auth::user()->nama_panggilan ?: Auth::user()->name }}!
                    </h2>
                    <p class="text-blue-100 text-base">
                        {{ $pengaturan->nama_website ?? 'Sistem Informasi Perpustakaan' }}
                    </p>
                    <div class="flex items-center mt-3 space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">
                            <i class="fas fa-calendar-alt mr-1.5"></i>
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">
                            <i class="fas fa-clock mr-1.5"></i>
                            <span id="liveClock">{{ now()->format('H:i') }}</span>
                        </span>
                    </div>
                </div>
                <div class="mt-4 md:mt-0 flex space-x-3">
                    <a href="{{ route('peminjaman.create') }}" class="inline-flex items-center px-4 py-2.5 bg-white/20 hover:bg-white/30 text-white rounded-xl text-sm font-medium transition-all backdrop-blur-sm border border-white/20">
                        <i class="fas fa-plus mr-2"></i> Peminjaman Baru
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Total Anggota -->
        <div class="stat-card bg-white rounded-2xl shadow-sm p-5 border border-gray-100 animate-fade-in-up stagger-1 opacity-0">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg shadow-blue-500/25">
                    <i class="fas fa-users text-white text-lg"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-50 text-green-700">
                    <i class="fas fa-arrow-up mr-1 text-[10px]"></i>{{ $anggotaBaruBulanIni }}
                </span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1" data-count="{{ $totalAnggota }}">{{ number_format($totalAnggota) }}</h3>
            <p class="text-sm text-gray-500">Total Anggota</p>
            <div class="mt-3 h-1.5 bg-blue-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-600 rounded-full" style="width: {{ min($totalAnggota, 100) }}%"></div>
            </div>
        </div>

        <!-- Total Buku -->
        <div class="stat-card bg-white rounded-2xl shadow-sm p-5 border border-gray-100 animate-fade-in-up stagger-2 opacity-0">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                    <i class="fas fa-book text-white text-lg"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700">
                    <i class="fas fa-layer-group mr-1 text-[10px]"></i>Koleksi
                </span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($totalBuku) }}</h3>
            <p class="text-sm text-gray-500">Total Judul Buku</p>
            <div class="mt-3 h-1.5 bg-emerald-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-emerald-500 to-emerald-600 rounded-full" style="width: {{ min($totalBuku, 100) }}%"></div>
            </div>
        </div>

        <!-- Peminjaman Aktif -->
        <div class="stat-card bg-white rounded-2xl shadow-sm p-5 border border-gray-100 animate-fade-in-up stagger-3 opacity-0">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/25">
                    <i class="fas fa-exchange-alt text-white text-lg"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700">
                    <i class="fas fa-book-open mr-1 text-[10px]"></i>{{ $bukuDipinjam }} buku
                </span>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-1">{{ number_format($peminjamanAktif) }}</h3>
            <p class="text-sm text-gray-500">Peminjaman Aktif</p>
            <div class="mt-3 h-1.5 bg-amber-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-amber-500 to-orange-500 rounded-full" style="width: {{ $totalAnggota > 0 ? min(($peminjamanAktif / max($totalAnggota, 1)) * 100, 100) : 0 }}%"></div>
            </div>
        </div>

        <!-- Total Denda -->
        <div class="stat-card bg-white rounded-2xl shadow-sm p-5 border border-gray-100 animate-fade-in-up stagger-4 opacity-0">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-rose-500 to-red-500 flex items-center justify-center shadow-lg shadow-rose-500/25">
                    <i class="fas fa-money-bill-wave text-white text-lg"></i>
                </div>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700">
                    <i class="fas fa-receipt mr-1 text-[10px]"></i>Denda
                </span>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Rp {{ number_format($totalDenda, 0, ',', '.') }}</h3>
            <p class="text-sm text-gray-500">Total Denda</p>
            <div class="mt-3 h-1.5 bg-rose-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-rose-500 to-red-500 rounded-full" style="width: {{ min($totalDenda / max(1, 1000000) * 100, 100) }}%"></div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 animate-fade-in-up" style="animation-delay: 0.3s; opacity: 0;">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Statistik Peminjaman & Pengembalian</h3>
                    <p class="text-sm text-gray-500 mt-0.5">6 bulan terakhir</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="text-xs text-gray-500">Pinjam</span>
                    </div>
                    <div class="flex items-center space-x-1.5">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="text-xs text-gray-500">Kembali</span>
                    </div>
                </div>
            </div>
            <div class="h-72 w-full">
                <canvas id="loanChart"></canvas>
            </div>
        </div>

        <!-- Doughnut Chart -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 animate-fade-in-up" style="animation-delay: 0.4s; opacity: 0;">
            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-900">Kategori Populer</h3>
                <p class="text-sm text-gray-500 mt-0.5">Distribusi koleksi buku</p>
            </div>
            <div class="h-72 w-full flex items-center justify-center">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Bottom Section: Activity + Popular Books + Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Activities -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 p-6 animate-fade-in-up" style="animation-delay: 0.5s; opacity: 0;">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Peminjaman dan pengembalian terbaru</p>
                </div>
                <a href="{{ route('riwayat-peminjaman.index') }}" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium transition-colors">
                    Lihat Semua <i class="fas fa-arrow-right ml-1.5 text-xs"></i>
                </a>
            </div>
            <div class="space-y-3">
                @forelse($recentActivities as $activity)
                    <div class="activity-item flex items-center p-3 rounded-xl border border-gray-100 hover:bg-gray-50/80 hover:border-gray-200 transition-all">
                        <div class="flex-shrink-0">
                            @if($activity->type == 'peminjaman')
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-sm">
                                    <i class="fas fa-arrow-right text-white text-sm"></i>
                                </div>
                            @else
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center shadow-sm">
                                    <i class="fas fa-check text-white text-sm"></i>
                                </div>
                            @endif
                        </div>
                        <div class="ml-3.5 flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <p class="text-sm font-semibold text-gray-900 truncate">{{ $activity->anggota->nama_lengkap }}</p>
                                <span class="text-xs text-gray-400 ml-2 flex-shrink-0">{{ $activity->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-0.5">
                                @if($activity->type == 'peminjaman')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700">
                                        <i class="fas fa-book-reader mr-1"></i> Peminjaman
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700">
                                        <i class="fas fa-undo mr-1"></i> Pengembalian
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-10">
                        <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3">
                            <i class="fas fa-inbox text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Belum ada aktivitas</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Right Column: Popular Books + Quick Actions -->
        <div class="space-y-6 animate-fade-in-up" style="animation-delay: 0.6s; opacity: 0;">
            <!-- Popular Books -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Buku Terpopuler</h3>
                    <p class="text-sm text-gray-500 mt-0.5">Paling sering dipinjam</p>
                </div>
                <div class="space-y-3">
                    @forelse($bukuTerpopuler as $index => $buku)
                        <div class="flex items-center p-2.5 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="flex-shrink-0 mr-3">
                                @if($index == 0)
                                    <div class="rank-badge bg-gradient-to-br from-yellow-400 to-amber-500 text-white shadow-sm shadow-amber-500/30">1</div>
                                @elseif($index == 1)
                                    <div class="rank-badge bg-gradient-to-br from-gray-300 to-gray-400 text-white shadow-sm">2</div>
                                @elseif($index == 2)
                                    <div class="rank-badge bg-gradient-to-br from-amber-600 to-amber-700 text-white shadow-sm">3</div>
                                @else
                                    <div class="rank-badge bg-gray-100 text-gray-500">{{ $index + 1 }}</div>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900 truncate">{{ $buku->judul_buku }}</p>
                                <p class="text-xs text-gray-400">{{ $buku->total_dipinjam ?? 0 }} kali dipinjam</p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-6">
                            <div class="w-12 h-12 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-2">
                                <i class="fas fa-book text-gray-400"></i>
                            </div>
                            <p class="text-gray-500 text-sm">Belum ada data</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Aksi Cepat</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('peminjaman.create') }}" class="quick-action flex flex-col items-center justify-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 text-blue-700 rounded-xl hover:from-blue-100 hover:to-indigo-100 transition-all border border-blue-100/50">
                        <div class="w-10 h-10 rounded-lg bg-blue-500 flex items-center justify-center mb-2 shadow-sm shadow-blue-500/25">
                            <i class="fas fa-plus text-white"></i>
                        </div>
                        <span class="text-xs font-semibold">Pinjam Buku</span>
                    </a>
                    <a href="{{ route('pengembalian.index') }}" class="quick-action flex flex-col items-center justify-center p-4 bg-gradient-to-br from-emerald-50 to-green-50 text-emerald-700 rounded-xl hover:from-emerald-100 hover:to-green-100 transition-all border border-emerald-100/50">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500 flex items-center justify-center mb-2 shadow-sm shadow-emerald-500/25">
                            <i class="fas fa-undo text-white"></i>
                        </div>
                        <span class="text-xs font-semibold">Kembalikan</span>
                    </a>
                    <a href="{{ route('anggota.create') }}" class="quick-action flex flex-col items-center justify-center p-4 bg-gradient-to-br from-violet-50 to-purple-50 text-violet-700 rounded-xl hover:from-violet-100 hover:to-purple-100 transition-all border border-violet-100/50">
                        <div class="w-10 h-10 rounded-lg bg-violet-500 flex items-center justify-center mb-2 shadow-sm shadow-violet-500/25">
                            <i class="fas fa-user-plus text-white"></i>
                        </div>
                        <span class="text-xs font-semibold">Anggota Baru</span>
                    </a>
                    <a href="{{ route('buku.create') }}" class="quick-action flex flex-col items-center justify-center p-4 bg-gradient-to-br from-orange-50 to-amber-50 text-orange-700 rounded-xl hover:from-orange-100 hover:to-amber-100 transition-all border border-orange-100/50">
                        <div class="w-10 h-10 rounded-lg bg-orange-500 flex items-center justify-center mb-2 shadow-sm shadow-orange-500/25">
                            <i class="fas fa-book-medical text-white"></i>
                        </div>
                        <span class="text-xs font-semibold">Tambah Buku</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // Live Clock
    function updateClock() {
        const now = new Date();
        const h = String(now.getHours()).padStart(2, '0');
        const m = String(now.getMinutes()).padStart(2, '0');
        const el = document.getElementById('liveClock');
        if (el) el.textContent = h + ':' + m;
    }
    setInterval(updateClock, 30000);

    // Chart defaults
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#6B7280';

    // 1. Loan vs Return Chart (Rounded Bar + Line combo)
    const loanCtx = document.getElementById('loanChart').getContext('2d');
    new Chart(loanCtx, {
        type: 'bar',
        data: {
            labels: {!! json_encode($months) !!},
            datasets: [
                {
                    label: 'Peminjaman',
                    data: {!! json_encode($peminjamanPerBulan) !!},
                    backgroundColor: (ctx) => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.85)');
                        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.35)');
                        return gradient;
                    },
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.7,
                },
                {
                    label: 'Pengembalian',
                    data: {!! json_encode($pengembalianPerBulan) !!},
                    backgroundColor: (ctx) => {
                        const gradient = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                        gradient.addColorStop(0, 'rgba(16, 185, 129, 0.85)');
                        gradient.addColorStop(1, 'rgba(16, 185, 129, 0.35)');
                        return gradient;
                    },
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 0,
                    borderRadius: 8,
                    borderSkipped: false,
                    barPercentage: 0.6,
                    categoryPercentage: 0.7,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleColor: '#F9FAFB',
                    bodyColor: '#D1D5DB',
                    borderColor: 'rgba(255,255,255,0.1)',
                    borderWidth: 1,
                    cornerRadius: 10,
                    padding: 12,
                    displayColors: true,
                    boxPadding: 4,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(243, 244, 246, 1)',
                        drawBorder: false,
                    },
                    ticks: {
                        padding: 10,
                        font: { size: 12 },
                    },
                    border: { display: false }
                },
                x: {
                    grid: { display: false },
                    ticks: {
                        padding: 8,
                        font: { size: 12 },
                    },
                    border: { display: false }
                }
            }
        }
    });

    // 2. Category Doughnut Chart
    const categoryCtx = document.getElementById('categoryChart').getContext('2d');
    const categoryLabels = {!! json_encode($kategoriLabels) !!};
    const categoryData = {!! json_encode($kategoriData) !!};

    new Chart(categoryCtx, {
        type: 'doughnut',
        data: {
            labels: categoryLabels,
            datasets: [{
                data: categoryData,
                backgroundColor: [
                    'rgba(59, 130, 246, 0.85)',
                    'rgba(16, 185, 129, 0.85)',
                    'rgba(245, 158, 11, 0.85)',
                    'rgba(239, 68, 68, 0.85)',
                    'rgba(139, 92, 246, 0.85)',
                ],
                borderWidth: 3,
                borderColor: '#ffffff',
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'circle',
                        padding: 16,
                        font: { size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleColor: '#F9FAFB',
                    bodyColor: '#D1D5DB',
                    cornerRadius: 10,
                    padding: 12,
                }
            },
            cutout: '72%',
        }
    });
});
</script>
@endsection
