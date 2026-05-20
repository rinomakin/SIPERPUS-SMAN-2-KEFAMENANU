@extends('layouts.admin')

@section('title', 'Dashboard Petugas')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ── Animations ─────────────────────────────── */
    @keyframes fadeInUp { from { opacity:0; transform:translateY(18px); } to { opacity:1; transform:translateY(0); } }
    @keyframes scaleIn  { from { opacity:0; transform:scale(.94); }       to { opacity:1; transform:scale(1); } }
    @keyframes pulseDot { 0%,100%{transform:scale(1);opacity:1;} 50%{transform:scale(1.7);opacity:.4;} }

    .anim-up        { animation: fadeInUp .5s ease both; }
    .anim-up.d1     { animation-delay:.05s; }
    .anim-up.d2     { animation-delay:.10s; }
    .anim-up.d3     { animation-delay:.15s; }
    .anim-up.d4     { animation-delay:.20s; }
    .anim-up.d5     { animation-delay:.25s; }
    .anim-up.d6     { animation-delay:.30s; }
    .anim-scale     { animation: scaleIn .45s ease both; }

    /* ── Stat cards ─────────────────────────────── */
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 18px 16px;
        border: 1px solid #f1f5f9;
        position: relative;
        overflow: hidden;
        transition: all .3s ease;
    }
    .stat-card::before {
        content:'';
        position:absolute;
        top:0; left:0; right:0;
        height:3px;
        border-radius:16px 16px 0 0;
    }
    .stat-card:hover { transform:translateY(-4px); }

    .stat-card.emerald::before { background:linear-gradient(90deg,#10b981,#34d399); }
    .stat-card.emerald:hover   { box-shadow:0 12px 24px -8px rgba(16,185,129,.25); }
    .stat-card.amber::before   { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
    .stat-card.amber:hover     { box-shadow:0 12px 24px -8px rgba(245,158,11,.25); }
    .stat-card.rose::before    { background:linear-gradient(90deg,#f43f5e,#fb7185); }
    .stat-card.rose:hover      { box-shadow:0 12px 24px -8px rgba(244,63,94,.25); }
    .stat-card.violet::before  { background:linear-gradient(90deg,#8b5cf6,#a78bfa); }
    .stat-card.violet:hover    { box-shadow:0 12px 24px -8px rgba(139,92,246,.25); }
    .stat-card.blue::before    { background:linear-gradient(90deg,#3b82f6,#60a5fa); }
    .stat-card.blue:hover      { box-shadow:0 12px 24px -8px rgba(59,130,246,.25); }
    .stat-card.indigo::before  { background:linear-gradient(90deg,#6366f1,#818cf8); }
    .stat-card.indigo:hover    { box-shadow:0 12px 24px -8px rgba(99,102,241,.25); }

    .stat-icon {
        width:44px; height:44px;
        border-radius:12px;
        display:flex; align-items:center; justify-content:center;
        font-size:17px; color:white; flex-shrink:0;
    }
    .stat-icon.emerald { background:linear-gradient(135deg,#10b981,#059669); }
    .stat-icon.amber   { background:linear-gradient(135deg,#f59e0b,#d97706); }
    .stat-icon.rose    { background:linear-gradient(135deg,#f43f5e,#e11d48); }
    .stat-icon.violet  { background:linear-gradient(135deg,#8b5cf6,#7c3aed); }
    .stat-icon.blue    { background:linear-gradient(135deg,#3b82f6,#2563eb); }
    .stat-icon.indigo  { background:linear-gradient(135deg,#6366f1,#4f46e5); }

    /* ── Section cards ───────────────────────────── */
    .section-card {
        background:white;
        border-radius:16px;
        border:1px solid #f1f5f9;
        overflow:hidden;
        box-shadow:0 1px 3px rgba(0,0,0,.05);
    }
    .section-header {
        padding:14px 18px;
        border-bottom:1px solid #f1f5f9;
        display:flex;
        align-items:center;
        justify-content:space-between;
        gap:8px;
    }

    /* ── Quick action buttons ────────────────────── */
    .qa-btn {
        display:flex;
        align-items:center;
        gap:10px;
        width:100%;
        padding:12px 14px;
        border-radius:12px;
        font-size:13px;
        font-weight:600;
        transition:all .22s ease;
        text-decoration:none;
        border:1px solid transparent;
    }
    .qa-btn:hover { transform:translateX(4px); }
    .qa-btn .qa-icon {
        width:36px; height:36px;
        border-radius:10px;
        display:flex; align-items:center; justify-content:center;
        font-size:14px; flex-shrink:0;
    }
    .qa-btn .qa-arrow { margin-left:auto; font-size:10px; opacity:.45; transition:transform .2s, opacity .2s; }
    .qa-btn:hover .qa-arrow { transform:translateX(3px); opacity:.8; }

    .qa-btn.green  { background:#f0fdf4; color:#15803d; border-color:#d1fae5; }
    .qa-btn.green:hover  { background:#dcfce7; }
    .qa-btn.green .qa-icon  { background:#dcfce7; color:#16a34a; }

    .qa-btn.blue   { background:#eff6ff; color:#1d4ed8; border-color:#bfdbfe; }
    .qa-btn.blue:hover   { background:#dbeafe; }
    .qa-btn.blue .qa-icon   { background:#dbeafe; color:#2563eb; }

    .qa-btn.amber  { background:#fffbeb; color:#92400e; border-color:#fde68a; }
    .qa-btn.amber:hover  { background:#fef3c7; }
    .qa-btn.amber .qa-icon  { background:#fef3c7; color:#d97706; }

    .qa-btn.violet { background:#f5f3ff; color:#5b21b6; border-color:#ddd6fe; }
    .qa-btn.violet:hover { background:#ede9fe; }
    .qa-btn.violet .qa-icon { background:#ede9fe; color:#7c3aed; }

    /* ── Activity list ────────────────────────────── */
    .activity-item {
        display:flex; align-items:flex-start; gap:12px;
        padding:11px 18px;
        border-bottom:1px solid #f8fafc;
        transition:background .15s;
    }
    .activity-item:last-child { border-bottom:none; }
    .activity-item:hover { background:#f8fafc; }

    /* ── Visitor row ─────────────────────────────── */
    .visitor-row {
        display:flex; align-items:center; gap:10px;
        padding:10px 18px;
        border-bottom:1px solid #f8fafc;
        transition:background .15s;
    }
    .visitor-row:last-child { border-bottom:none; }
    .visitor-row:hover { background:#f0fdf4; }

    /* ── Badge ───────────────────────────────────── */
    .badge {
        display:inline-flex; align-items:center; gap:4px;
        padding:2px 8px;
        border-radius:20px; font-size:10px; font-weight:600; border:1px solid;
    }
    .badge-green  { background:#ecfdf5; color:#059669; border-color:#a7f3d0; }
    .badge-amber  { background:#fffbeb; color:#d97706; border-color:#fde68a; }
    .badge-blue   { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
    .badge-rose   { background:#fff1f2; color:#e11d48; border-color:#fecdd3; }
    .badge-gray   { background:#f8fafc; color:#64748b; border-color:#e2e8f0; }

    /* ── Pulse dot ───────────────────────────────── */
    .pulse-dot {
        width:8px; height:8px; border-radius:50%;
        background:#10b981; display:inline-block;
        animation:pulseDot 1.8s infinite;
    }

    /* ── Chart wrappers ──────────────────────────── */
    .chart-wrap { position:relative; width:100%; }

    /* ── Clock ───────────────────────────────────── */
    #live-clock { font-variant-numeric:tabular-nums; }

    /* ─────────────── Dark Mode ─────────────── */

    /* Stat cards */
    [data-theme="dark"] .stat-card {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.06) !important;
    }
    [data-theme="dark"] #petugas-dash .text-gray-900 { color: #f1f5f9 !important; }
    [data-theme="dark"] #petugas-dash .text-gray-800 { color: #f1f5f9 !important; }
    [data-theme="dark"] #petugas-dash .text-gray-700 { color: #e2e8f0 !important; }
    [data-theme="dark"] #petugas-dash .text-gray-600 { color: #94a3b8 !important; }
    [data-theme="dark"] #petugas-dash .text-gray-500 { color: #64748b !important; }
    [data-theme="dark"] #petugas-dash .text-gray-400 { color: #475569 !important; }
    /* Stat card mini-badges */
    [data-theme="dark"] .stat-card .bg-emerald-50 { background-color: rgba(16,185,129,0.13) !important; }
    [data-theme="dark"] .stat-card .bg-rose-50    { background-color: rgba(244,63,94,0.13)  !important; }
    [data-theme="dark"] .stat-card .bg-blue-50    { background-color: rgba(59,130,246,0.13)  !important; }
    [data-theme="dark"] .stat-card .bg-amber-50   { background-color: rgba(245,158,11,0.13)  !important; }
    [data-theme="dark"] .stat-card .bg-indigo-50  { background-color: rgba(99,102,241,0.13)  !important; }
    [data-theme="dark"] .stat-card .bg-gray-50    { background-color: rgba(255,255,255,0.05) !important; }

    /* Section cards */
    [data-theme="dark"] .section-card {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.06) !important;
        box-shadow: 0 1px 3px rgba(0,0,0,.3) !important;
    }
    [data-theme="dark"] .section-header { border-bottom-color: #334155 !important; }
    [data-theme="dark"] .section-header .bg-amber-100 {
        background-color: rgba(245,158,11,0.15) !important;
        color: #fcd34d !important;
    }
    [data-theme="dark"] .section-card .border-t { border-top-color: #334155 !important; }
    [data-theme="dark"] .section-card .bg-emerald-50 { background-color: rgba(16,185,129,0.10) !important; }
    [data-theme="dark"] .section-card .bg-violet-50  { background-color: rgba(139,92,246,0.12) !important; }
    [data-theme="dark"] .section-card .text-violet-600 { color: #c4b5fd !important; }

    /* Quick action buttons */
    [data-theme="dark"] .qa-btn.green {
        background: rgba(16,185,129,0.08) !important;
        border-color: rgba(16,185,129,0.22) !important;
        color: #6ee7b7 !important;
    }
    [data-theme="dark"] .qa-btn.green:hover  { background: rgba(16,185,129,0.15) !important; }
    [data-theme="dark"] .qa-btn.green .qa-icon  { background: rgba(16,185,129,0.18) !important; color: #34d399 !important; }
    [data-theme="dark"] .qa-btn.blue {
        background: rgba(59,130,246,0.08) !important;
        border-color: rgba(59,130,246,0.22) !important;
        color: #93c5fd !important;
    }
    [data-theme="dark"] .qa-btn.blue:hover   { background: rgba(59,130,246,0.15) !important; }
    [data-theme="dark"] .qa-btn.blue .qa-icon   { background: rgba(59,130,246,0.18) !important; color: #60a5fa !important; }
    [data-theme="dark"] .qa-btn.amber {
        background: rgba(245,158,11,0.08) !important;
        border-color: rgba(245,158,11,0.22) !important;
        color: #fcd34d !important;
    }
    [data-theme="dark"] .qa-btn.amber:hover  { background: rgba(245,158,11,0.15) !important; }
    [data-theme="dark"] .qa-btn.amber .qa-icon  { background: rgba(245,158,11,0.18) !important; color: #fbbf24 !important; }
    [data-theme="dark"] .qa-btn.violet {
        background: rgba(139,92,246,0.08) !important;
        border-color: rgba(139,92,246,0.22) !important;
        color: #c4b5fd !important;
    }
    [data-theme="dark"] .qa-btn.violet:hover { background: rgba(139,92,246,0.15) !important; }
    [data-theme="dark"] .qa-btn.violet .qa-icon { background: rgba(139,92,246,0.18) !important; color: #a78bfa !important; }

    /* Activity list */
    [data-theme="dark"] .activity-item { border-bottom-color: #334155 !important; }
    [data-theme="dark"] .activity-item:hover { background: #243047 !important; }
    [data-theme="dark"] .divide-y.divide-gray-50 > * + * { border-top-color: #334155 !important; }

    /* Visitor rows */
    [data-theme="dark"] .visitor-row { border-bottom-color: #334155 !important; }
    [data-theme="dark"] .visitor-row:hover { background: rgba(16,185,129,0.06) !important; }

    /* Badges */
    [data-theme="dark"] .badge-green { background: rgba(16,185,129,0.12) !important; color: #6ee7b7 !important; border-color: rgba(16,185,129,0.28) !important; }
    [data-theme="dark"] .badge-amber { background: rgba(245,158,11,0.12)  !important; color: #fcd34d   !important; border-color: rgba(245,158,11,0.28)  !important; }
    [data-theme="dark"] .badge-blue  { background: rgba(59,130,246,0.12)  !important; color: #93c5fd   !important; border-color: rgba(59,130,246,0.28)  !important; }
    [data-theme="dark"] .badge-rose  { background: rgba(244,63,94,0.12)   !important; color: #fda4af   !important; border-color: rgba(244,63,94,0.28)   !important; }
    [data-theme="dark"] .badge-gray  { background: rgba(255,255,255,0.05) !important; color: #94a3b8   !important; border-color: rgba(255,255,255,0.10) !important; }
</style>
@endpush

@section('content')
<div id="petugas-dash" class="py-6">
<div class="px-4 sm:px-6 lg:px-8 space-y-6">

{{-- ══════════════════════════════════════════════════════
     HERO HEADER
══════════════════════════════════════════════════════ --}}
<div class="relative overflow-hidden rounded-2xl anim-scale"
     style="background:linear-gradient(135deg,#10b981 0%,#059669 50%,#047857 100%)">

    {{-- decorative blobs --}}
    <div class="absolute inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-8 -right-8 w-56 h-56 bg-white/10 rounded-full"></div>
        <div class="absolute -bottom-12 -left-12 w-72 h-72 bg-white/5 rounded-full"></div>
        <div class="absolute top-1/2 right-1/3 w-28 h-28 bg-white/10 rounded-full"></div>
    </div>

    <div class="relative px-6 py-7">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">

            {{-- Greeting --}}
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <span class="pulse-dot"></span>
                    <span class="text-emerald-200 text-xs font-semibold uppercase tracking-wider">Petugas Perpustakaan</span>
                </div>
                <h1 class="text-2xl md:text-3xl font-bold text-white">
                    Selamat Datang, {{ Auth::user()->nama_panggilan ?: Str::before(Auth::user()->nama_lengkap, ' ') }}!
                </h1>
                <p class="text-emerald-100 text-sm mt-1">
                    <i class="fas fa-calendar-day mr-1"></i>
                    {{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}
                </p>
            </div>

            {{-- Live stats --}}
            <div class="flex items-center gap-5 flex-wrap">
                <div class="text-center">
                    <div id="live-clock" class="text-3xl font-bold text-white tracking-tight">--:--</div>
                    <div class="text-emerald-200 text-xs mt-0.5">Waktu Sekarang</div>
                </div>
                <div class="hidden sm:block w-px h-12 bg-white/20"></div>
                <div class="hidden sm:flex items-center gap-5">
                    <div class="text-center">
                        <div class="text-xl font-bold text-white">{{ $totalTamuHariIni }}</div>
                        <div class="text-emerald-200 text-xs">Tamu Hari Ini</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-amber-300">{{ $sedangBerkunjung }}</div>
                        <div class="text-emerald-200 text-xs">Masih di Sini</div>
                    </div>
                    <div class="text-center">
                        <div class="text-xl font-bold text-blue-200">{{ $peminjamanAktif }}</div>
                        <div class="text-emerald-200 text-xs">Buku Dipinjam</div>
                    </div>
                    @if($peminjamanTerlambat > 0)
                    <div class="text-center">
                        <div class="text-xl font-bold text-rose-300">{{ $peminjamanTerlambat }}</div>
                        <div class="text-emerald-200 text-xs">Terlambat</div>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     6 STAT CARDS
══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">

    {{-- 1. Tamu Hari Ini --}}
    <div class="stat-card emerald anim-up d1">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-500 mb-1">Tamu Hari Ini</p>
                <p class="text-2xl font-bold text-gray-900">{{ $totalTamuHariIni }}</p>
                <div class="mt-2">
                    @if($selisihKemarin > 0)
                        <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <i class="fas fa-arrow-up mr-0.5"></i>+{{ $selisihKemarin }}
                        </span>
                    @elseif($selisihKemarin < 0)
                        <span class="text-[10px] font-semibold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-full">
                            <i class="fas fa-arrow-down mr-0.5"></i>{{ $selisihKemarin }}
                        </span>
                    @else
                        <span class="text-[10px] text-gray-400 bg-gray-50 px-2 py-0.5 rounded-full">Sama</span>
                    @endif
                </div>
            </div>
            <div class="stat-icon emerald"><i class="fas fa-users"></i></div>
        </div>
    </div>

    {{-- 2. Masih Berkunjung --}}
    <div class="stat-card amber anim-up d2">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-500 mb-1">Masih di Sini</p>
                <p class="text-2xl font-bold text-gray-900">{{ $sedangBerkunjung }}</p>
                <div class="mt-2">
                    <span class="text-[10px] font-semibold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full inline-flex items-center gap-1">
                        <span class="pulse-dot" style="width:5px;height:5px;background:#f59e0b;"></span> Live
                    </span>
                </div>
            </div>
            <div class="stat-icon amber"><i class="fas fa-door-open"></i></div>
        </div>
    </div>

    {{-- 3. Sudah Pulang --}}
    <div class="stat-card rose anim-up d3">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-500 mb-1">Sudah Pulang</p>
                <p class="text-2xl font-bold text-gray-900">{{ $sudahPulang }}</p>
                <div class="mt-2">
                    @if($totalTamuHariIni > 0)
                        <span class="text-[10px] font-semibold text-rose-500 bg-rose-50 px-2 py-0.5 rounded-full">
                            {{ round(($sudahPulang / $totalTamuHariIni) * 100) }}%
                        </span>
                    @else
                        <span class="text-[10px] text-gray-400">—</span>
                    @endif
                </div>
            </div>
            <div class="stat-icon rose"><i class="fas fa-sign-out-alt"></i></div>
        </div>
    </div>

    {{-- 4. Peminjaman Aktif --}}
    <div class="stat-card blue anim-up d4">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-500 mb-1">Buku Dipinjam</p>
                <p class="text-2xl font-bold text-gray-900">{{ $peminjamanAktif }}</p>
                <div class="mt-2">
                    <span class="text-[10px] font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">
                        +{{ $peminjamanHariIni }} hari ini
                    </span>
                </div>
            </div>
            <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
        </div>
    </div>

    {{-- 5. Pengembalian Hari Ini --}}
    <div class="stat-card indigo anim-up d5">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-500 mb-1">Dikembalikan</p>
                <p class="text-2xl font-bold text-gray-900">{{ $pengembalianHariIni }}</p>
                <div class="mt-2">
                    <span class="text-[10px] font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-full">
                        Hari ini
                    </span>
                </div>
            </div>
            <div class="stat-icon indigo"><i class="fas fa-undo-alt"></i></div>
        </div>
    </div>

    {{-- 6. Terlambat --}}
    <div class="stat-card {{ $peminjamanTerlambat > 0 ? 'rose' : 'emerald' }} anim-up d6">
        <div class="flex items-start justify-between gap-2">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-500 mb-1">Terlambat</p>
                <p class="text-2xl font-bold {{ $peminjamanTerlambat > 0 ? 'text-rose-600' : 'text-gray-900' }}">{{ $peminjamanTerlambat }}</p>
                <div class="mt-2">
                    @if($peminjamanTerlambat > 0)
                        <span class="text-[10px] font-semibold text-rose-600 bg-rose-50 px-2 py-0.5 rounded-full">
                            <i class="fas fa-exclamation-triangle mr-0.5"></i>Perlu ditindak
                        </span>
                    @else
                        <span class="text-[10px] text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">
                            <i class="fas fa-check mr-0.5"></i>Aman
                        </span>
                    @endif
                </div>
            </div>
            <div class="stat-icon {{ $peminjamanTerlambat > 0 ? 'rose' : 'emerald' }}">
                <i class="fas {{ $peminjamanTerlambat > 0 ? 'fa-exclamation-circle' : 'fa-check-circle' }}"></i>
            </div>
        </div>
    </div>

</div>

{{-- ══════════════════════════════════════════════════════
     MAIN ROW: Quick Actions + Donut | 7-Day Trend + Activity
══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- ── LEFT: Quick Actions + Donut Chart ── --}}
    <div class="space-y-5">

        {{-- Quick Actions --}}
        <div class="section-card anim-up d1">
            <div class="section-header">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-bolt text-amber-400"></i> Aksi Cepat
                </h3>
            </div>
            <div class="p-4 space-y-2">
                <a href="{{ route('peminjaman.create') }}" class="qa-btn green">
                    <span class="qa-icon"><i class="fas fa-plus-circle"></i></span>
                    <span>Tambah Peminjaman</span>
                    <i class="fas fa-chevron-right qa-arrow"></i>
                </a>
                <a href="{{ route('pengembalian.create') }}" class="qa-btn blue">
                    <span class="qa-icon"><i class="fas fa-undo-alt"></i></span>
                    <span>Proses Pengembalian</span>
                    <i class="fas fa-chevron-right qa-arrow"></i>
                </a>
                <a href="{{ route('petugas.buku-tamu.create') }}" class="qa-btn amber">
                    <span class="qa-icon"><i class="fas fa-user-plus"></i></span>
                    <span>Tambah Tamu</span>
                    <i class="fas fa-chevron-right qa-arrow"></i>
                </a>
                <a href="{{ route('petugas.buku-tamu.index') }}" class="qa-btn violet">
                    <span class="qa-icon"><i class="fas fa-clipboard-list"></i></span>
                    <span>Daftar Buku Tamu</span>
                    <i class="fas fa-chevron-right qa-arrow"></i>
                </a>
            </div>
        </div>

        {{-- Donut Chart: Tipe Tamu --}}
        <div class="section-card anim-up d2">
            <div class="section-header">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-pie text-violet-500"></i> Tipe Tamu Hari Ini
                </h3>
                <span class="text-xs font-semibold text-gray-500 bg-gray-100 px-2.5 py-0.5 rounded-full">
                    {{ $totalTamuHariIni }} total
                </span>
            </div>
            <div class="p-5">
                <div class="chart-wrap" style="height:180px;">
                    <canvas id="donutTamuChart"></canvas>
                </div>
                <div class="flex justify-center gap-6 mt-4">
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span class="w-3 h-3 rounded-sm inline-block flex-shrink-0" style="background:#10b981;"></span>
                        Anggota <span class="font-bold text-gray-900 ml-1">{{ $tamuAnggota }}</span>
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-600">
                        <span class="w-3 h-3 rounded-sm inline-block flex-shrink-0" style="background:#8b5cf6;"></span>
                        Umum <span class="font-bold text-gray-900 ml-1">{{ $tamuUmum }}</span>
                    </div>
                </div>
                <div class="pt-3 mt-3 border-t border-gray-100 flex items-center justify-between text-xs text-gray-400">
                    <span><i class="fas fa-history mr-1"></i>Kemarin: <span class="font-semibold text-gray-600">{{ $totalKemarin }}</span> tamu</span>
                    <span class="font-semibold text-violet-600 bg-violet-50 px-2 py-0.5 rounded-full">
                        Bulan ini: {{ $totalBulanIni }}
                    </span>
                </div>
            </div>
        </div>

    </div>

    {{-- ── RIGHT 2-col: Trend Chart + Recent Activity ── --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- 7-Day Trend Line Chart --}}
        <div class="section-card anim-up d2">
            <div class="section-header">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-line text-blue-500"></i> Tren 7 Hari Terakhir
                </h3>
                <div class="hidden sm:flex items-center gap-3 text-xs text-gray-400">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-1 rounded bg-emerald-500 inline-block"></span>Tamu
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-1 rounded bg-blue-500 inline-block"></span>Pinjam
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-1 rounded bg-violet-500 inline-block"></span>Kembali
                    </span>
                </div>
            </div>
            <div class="px-5 py-4">
                <div class="chart-wrap" style="height:210px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>

        {{-- Recent Activities --}}
        <div class="section-card anim-up d3">
            <div class="section-header">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-stream text-emerald-500"></i> Aktivitas Tamu Hari Ini
                </h3>
                <a href="{{ route('petugas.buku-tamu.index') }}"
                   class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1 flex-shrink-0">
                    Lihat Semua <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            @if($recentActivities->isEmpty())
                <div class="flex flex-col items-center justify-center py-10">
                    <div class="w-14 h-14 bg-emerald-50 rounded-2xl flex items-center justify-center mb-3">
                        <i class="fas fa-clipboard-list text-emerald-300 text-xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700 mb-1">Belum ada kunjungan hari ini</p>
                    <p class="text-xs text-gray-400 mb-4">Tambahkan tamu pertama untuk memulai</p>
                    <a href="{{ route('petugas.buku-tamu.create') }}"
                       class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white text-xs font-semibold rounded-xl transition-colors">
                        <i class="fas fa-plus"></i> Tambah Tamu
                    </a>
                </div>
            @else
                <div class="divide-y divide-gray-50 max-h-60 overflow-y-auto">
                    @foreach($recentActivities as $item)
                    @php
                        $nama    = $item->nama_tamu ?: ($item->anggota ? $item->anggota->nama_lengkap : 'Tamu Umum');
                        $kelas   = $item->instansi ?: ($item->anggota && $item->anggota->kelas ? $item->anggota->kelas->nama_kelas : null);
                        $tujuan  = $item->keperluan ?: 'Kunjungan perpustakaan';
                        $sudahPulangItem = !is_null($item->waktu_pulang);
                        $inisial = strtoupper(mb_substr($nama, 0, 1));
                        $warna   = $item->anggota_id
                            ? 'background:linear-gradient(135deg,#10b981,#059669)'
                            : 'background:linear-gradient(135deg,#8b5cf6,#7c3aed)';
                    @endphp
                    <div class="activity-item">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                             style="{{ $warna }}">{{ $inisial }}</div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-xs font-semibold text-gray-900 truncate">{{ $nama }}</p>
                                @if($item->anggota_id)
                                    <span class="badge badge-green flex-shrink-0">Anggota</span>
                                @else
                                    <span class="badge badge-blue flex-shrink-0">Umum</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-gray-400 truncate mt-0.5">
                                @if($kelas)<span>{{ $kelas }}</span><span class="mx-1">·</span>@endif
                                {{ Str::limit($tujuan, 40) }}
                            </p>
                        </div>
                        <div class="text-right flex-shrink-0 space-y-1">
                            <p class="text-[11px] text-gray-400">
                                <i class="fas fa-sign-in-alt mr-0.5 text-emerald-400"></i>
                                {{ $item->waktu_datang ? $item->waktu_datang->format('H:i') : '--:--' }}
                            </p>
                            @if($sudahPulangItem)
                                <span class="badge badge-green"><i class="fas fa-check-circle"></i> Selesai</span>
                            @else
                                <span class="badge badge-amber">
                                    <span class="pulse-dot" style="width:5px;height:5px;background:#f59e0b;"></span> Ada
                                </span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     BOTTOM ROW: Tamu Saat Ini + Hourly Chart
══════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-5 gap-6 anim-up d4">

    {{-- Tamu Saat Ini --}}
    <div class="lg:col-span-2">
        <div class="section-card h-full">
            <div class="section-header">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <span class="pulse-dot"></span> Masih di Perpustakaan
                </h3>
                <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-0.5 rounded-full">
                    {{ $tamuSaatIni->count() }}
                </span>
            </div>

            @if($tamuSaatIni->isEmpty())
                <div class="p-8 text-center">
                    <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-door-closed text-emerald-300 text-lg"></i>
                    </div>
                    <p class="text-sm text-gray-500">Tidak ada pengunjung saat ini</p>
                </div>
            @else
                <div class="divide-y divide-gray-50 max-h-72 overflow-y-auto">
                    @foreach($tamuSaatIni as $tamu)
                    @php
                        $namaTamu  = $tamu->nama_tamu ?: ($tamu->anggota ? $tamu->anggota->nama_lengkap : 'Tamu Umum');
                        $kelasTamu = $tamu->instansi ?: ($tamu->anggota && $tamu->anggota->kelas ? $tamu->anggota->kelas->nama_kelas : null);
                        $inisialT  = strtoupper(mb_substr($namaTamu, 0, 1));
                    @endphp
                    <div class="visitor-row">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                             style="background:linear-gradient(135deg,#10b981,#059669)">
                            {{ $inisialT }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-900 truncate">{{ $namaTamu }}</p>
                            @if($kelasTamu)
                                <p class="text-[11px] text-gray-400 truncate">{{ $kelasTamu }}</p>
                            @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-[11px] text-gray-400">
                                {{ $tamu->waktu_datang ? $tamu->waktu_datang->format('H:i') : '--:--' }}
                            </p>
                            @if(!is_null($tamu->anggota_id))
                                <span class="badge badge-green" style="font-size:9px;padding:2px 6px;">Anggota</span>
                            @else
                                <span class="badge badge-gray" style="font-size:9px;padding:2px 6px;">Umum</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Hourly Bar Chart --}}
    <div class="lg:col-span-3">
        <div class="section-card h-full">
            <div class="section-header">
                <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-chart-bar text-blue-500"></i> Distribusi Kunjungan Per Jam
                </h3>
                <span class="text-xs text-gray-400">06:00 – 18:00</span>
            </div>
            <div class="px-5 py-4">
                <div class="chart-wrap" style="height:190px;">
                    <canvas id="hourlyChart"></canvas>
                </div>
                <div class="flex items-center gap-4 mt-3 pt-3 border-t border-gray-100 flex-wrap">
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="inline-block w-3 h-3 rounded-sm" style="background:#10b981;"></span> Kunjungan
                    </div>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <span class="inline-block w-3 h-3 rounded-sm" style="background:#f59e0b;"></span> Jam ini
                    </div>
                    @php $peakJam = array_search(max($jamData), $jamData); @endphp
                    @if(max($jamData) > 0)
                    <div class="ml-auto text-xs text-gray-400">
                        Jam tersibuk:
                        <span class="font-semibold text-gray-700">{{ str_pad($peakJam, 2, '0', STR_PAD_LEFT) }}:00</span>
                        ({{ max($jamData) }} tamu)
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Data dari PHP ─────────────────────────────────────────────────
const trendLabels      = {!! json_encode($last7Days) !!};
const tamuData         = {!! json_encode($tamuPerHari) !!};
const peminjamanData   = {!! json_encode($peminjamanPerHari) !!};
const pengembalianData = {!! json_encode($pengembalianPerHari) !!};

const jamHours  = {!! json_encode(array_keys($jamData)) !!};
const jamValues = {!! json_encode(array_values($jamData)) !!};
const jamLabels = jamHours.map(h => String(h).padStart(2, '0') + ':00');

const currentHour = {{ (int) now()->format('H') }};
const tamuAnggota = {{ $tamuAnggota }};
const tamuUmum    = {{ $tamuUmum }};

// Theme helpers
const isDark       = document.documentElement.getAttribute('data-theme') === 'dark';
const gridColor    = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.04)';
const tickColor    = isDark ? '#475569' : '#9ca3af';
const legendColor  = isDark ? '#94a3b8' : '#6b7280';
const donutBorder  = isDark ? '#1e293b' : '#fff';
const centerNumClr = isDark ? '#f1f5f9' : '#111827';
const centerSubClr = isDark ? '#64748b' : '#9ca3af';

// Default chart.js globals
Chart.defaults.font.family = "'Inter', sans-serif";

// ── 1. Donut Chart — Tipe Tamu ────────────────────────────────────
(function () {
    const ctx = document.getElementById('donutTamuChart').getContext('2d');
    const total = tamuAnggota + tamuUmum;

    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Anggota', 'Tamu Umum'],
            datasets: [{
                data: total > 0 ? [tamuAnggota, tamuUmum] : [1, 1],
                backgroundColor: total > 0 ? ['#10b981', '#8b5cf6'] : [isDark ? '#334155' : '#e2e8f0', isDark ? '#334155' : '#e2e8f0'],
                borderColor: [donutBorder, donutBorder],
                borderWidth: 4,
                hoverOffset: 8,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '72%',
            animation: { animateRotate: true, duration: 700 },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(17,24,39,.9)',
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        label(ctx) {
                            if (total === 0) return ' Belum ada data';
                            const pct = Math.round((ctx.parsed / total) * 100);
                            return ` ${ctx.label}: ${ctx.parsed} (${pct}%)`;
                        }
                    }
                }
            }
        },
        plugins: [{
            id: 'centerText',
            afterDraw(chart) {
                const { width, height, ctx } = chart;
                ctx.save();
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                const cx = width / 2, cy = height / 2;
                ctx.font = 'bold 24px Inter, sans-serif';
                ctx.fillStyle = centerNumClr;
                ctx.fillText(total, cx, cy - 9);
                ctx.font = '11px Inter, sans-serif';
                ctx.fillStyle = centerSubClr;
                ctx.fillText('Total', cx, cy + 13);
                ctx.restore();
            }
        }]
    });
})();

// ── 2. Line/Area Chart — 7-Day Trend ─────────────────────────────
(function () {
    const ctx = document.getElementById('trendChart').getContext('2d');

    const makeGrad = (ctx, r, g, b) => {
        const grad = ctx.createLinearGradient(0, 0, 0, 210);
        grad.addColorStop(0, `rgba(${r},${g},${b},0.18)`);
        grad.addColorStop(1, `rgba(${r},${g},${b},0.01)`);
        return grad;
    };

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [
                {
                    label: 'Kunjungan Tamu',
                    data: tamuData,
                    borderColor: '#10b981',
                    backgroundColor: makeGrad(ctx, 16, 185, 129),
                    borderWidth: 2.5,
                    pointBackgroundColor: '#10b981',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Peminjaman',
                    data: peminjamanData,
                    borderColor: '#3b82f6',
                    backgroundColor: makeGrad(ctx, 59, 130, 246),
                    borderWidth: 2.5,
                    pointBackgroundColor: '#3b82f6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                },
                {
                    label: 'Pengembalian',
                    data: pengembalianData,
                    borderColor: '#8b5cf6',
                    backgroundColor: makeGrad(ctx, 139, 92, 246),
                    borderWidth: 2.5,
                    pointBackgroundColor: '#8b5cf6',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    fill: true,
                    tension: 0.4,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: {
                    display: true,
                    position: 'bottom',
                    labels: {
                        boxWidth: 12, boxHeight: 3, padding: 16,
                        font: { size: 11 }, color: legendColor,
                        usePointStyle: true, pointStyle: 'circle',
                    }
                },
                tooltip: {
                    backgroundColor: 'rgba(17,24,39,.92)',
                    padding: 12, cornerRadius: 10,
                    titleFont: { size: 12, weight: '600' },
                    bodyFont: { size: 11 },
                    itemSort: (a, b) => b.raw - a.raw,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: tickColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { font: { size: 11 }, color: tickColor, stepSize: 1, precision: 0 }
                }
            }
        }
    });
})();

// ── 3. Bar Chart — Hourly Distribution ───────────────────────────
(function () {
    const ctx = document.getElementById('hourlyChart').getContext('2d');

    const bgColors = jamHours.map((h, i) => {
        if (h === currentHour)    return 'rgba(245,158,11,0.85)';
        if (jamValues[i] === 0)   return 'rgba(16,185,129,0.18)';
        return 'rgba(16,185,129,0.78)';
    });

    const borderColors = jamHours.map(h =>
        h === currentHour ? '#f59e0b' : '#10b981'
    );

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: jamLabels,
            datasets: [{
                label: 'Kunjungan',
                data: jamValues,
                backgroundColor: bgColors,
                borderColor: borderColors,
                borderWidth: 0,
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(17,24,39,.92)',
                    padding: 10, cornerRadius: 8,
                    callbacks: {
                        title: items => items[0].label + ' WIB',
                        label: item => ` ${item.raw} pengunjung`,
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 }, color: tickColor }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: gridColor, drawBorder: false },
                    ticks: { font: { size: 10 }, color: tickColor, stepSize: 1, precision: 0 }
                }
            }
        }
    });
})();

// ── Live Clock ────────────────────────────────────────────────────
function updateClock() {
    const now = new Date();
    const el = document.getElementById('live-clock');
    if (el) {
        el.textContent =
            String(now.getHours()).padStart(2, '0') + ':' +
            String(now.getMinutes()).padStart(2, '0');
    }
}
updateClock();
setInterval(updateClock, 30000);
</script>
@endpush
@endsection
