@extends('layouts.admin')

@section('title', 'Dashboard Kepala Sekolah')

@section('content')
<style>
/* ── Stat Cards ── */
.ks-stat {
    position: relative; overflow: hidden; border-radius: 18px; padding: 22px 24px;
    background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.03);
    transition: transform .25s ease, box-shadow .25s ease;
}
.ks-stat:hover { transform: translateY(-3px); box-shadow: 0 8px 28px rgba(0,0,0,0.10); }
.ks-stat-glow {
    position: absolute; width: 120px; height: 120px; border-radius: 50%;
    top: -30px; right: -30px; opacity: .12;
}
.ks-stat-icon {
    width: 46px; height: 46px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
    margin-bottom: 14px; flex-shrink: 0;
}
.ks-stat-value { font-size: 26px; font-weight: 800; line-height: 1.1; }
.ks-stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: #9ca3af; margin-top: 2px; }
.ks-stat-badge {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; margin-top: 8px;
}

/* ── Glass Card ── */
.ks-card {
    background: white; border-radius: 18px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.06), 0 0 0 1px rgba(0,0,0,0.03);
    overflow: hidden;
}

/* ── Section Header ── */
.ks-section-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 18px 22px; border-bottom: 1px solid #f1f5f9;
}
.ks-section-title { font-size: 14px; font-weight: 700; color: #1e293b; }

/* ── Quick Link ── */
.ks-qlink {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    gap: 8px; padding: 18px 12px; border-radius: 14px; text-decoration: none;
    transition: transform .2s ease, box-shadow .2s ease; text-align: center;
}
.ks-qlink:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(0,0,0,0.12); }
.ks-qlink-icon {
    width: 44px; height: 44px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.ks-qlink-label { font-size: 11px; font-weight: 600; }

/* ── Overdue row ── */
.ks-overdue-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 22px; border-bottom: 1px solid #f8fafc; transition: background .15s;
}
.ks-overdue-row:hover { background: #fef9ff; }
.ks-overdue-row:last-child { border-bottom: none; }

/* ── Animations ── */
@keyframes ks-fadein { from { opacity:0; transform: translateY(14px); } to { opacity:1; transform: translateY(0); } }
.ks-anim { animation: ks-fadein .45s ease forwards; opacity: 0; }
.ks-d1 { animation-delay: .05s; } .ks-d2 { animation-delay: .10s; }
.ks-d3 { animation-delay: .15s; } .ks-d4 { animation-delay: .20s; }
.ks-d5 { animation-delay: .25s; } .ks-d6 { animation-delay: .30s; }
.ks-d7 { animation-delay: .35s; } .ks-d8 { animation-delay: .40s; }

/* ─────────────── Dark Mode ─────────────── */
[data-theme="dark"] .ks-stat {
    background: #1e293b;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.06);
}
[data-theme="dark"] .ks-stat:hover { box-shadow: 0 8px 28px rgba(0,0,0,0.5); }
[data-theme="dark"] .ks-stat-value { color: #f1f5f9 !important; }
[data-theme="dark"] .ks-stat-label { color: #64748b; }
[data-theme="dark"] .ks-stat-icon  { background: rgba(255,255,255,0.07) !important; }
[data-theme="dark"] .ks-stat-badge { background: rgba(255,255,255,0.07) !important; }

[data-theme="dark"] .ks-card {
    background: #1e293b;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4), 0 0 0 1px rgba(255,255,255,0.06);
}
[data-theme="dark"] .ks-section-header { border-bottom-color: #334155; }
[data-theme="dark"] .ks-section-title  { color: #f1f5f9; }
[data-theme="dark"] .ks-section-header .w-7 { background: rgba(255,255,255,0.08) !important; }

[data-theme="dark"] .ks-qlink       { background: rgba(255,255,255,0.05) !important; }
[data-theme="dark"] .ks-qlink:hover { background: rgba(255,255,255,0.09) !important; box-shadow: 0 6px 18px rgba(0,0,0,0.35); }
[data-theme="dark"] .ks-qlink-label { color: #cbd5e1 !important; }

[data-theme="dark"] .ks-overdue-row            { border-bottom-color: #334155; }
[data-theme="dark"] .ks-overdue-row:hover      { background: #243047; }

/* Dashboard scoped text overrides */
[data-theme="dark"] .ks-dashboard .text-gray-900 { color: #f1f5f9; }
[data-theme="dark"] .ks-dashboard .text-gray-800 { color: #f1f5f9; }
[data-theme="dark"] .ks-dashboard .text-gray-700 { color: #e2e8f0; }
[data-theme="dark"] .ks-dashboard .text-gray-500 { color: #94a3b8; }
[data-theme="dark"] .ks-dashboard .text-gray-400 { color: #64748b; }

/* Chart area monthly summary boxes */
[data-theme="dark"] .ks-card .bg-indigo-50  { background-color: rgba(99,102,241,0.13)  !important; }
[data-theme="dark"] .ks-card .bg-emerald-50 { background-color: rgba(16,185,129,0.13)  !important; }
[data-theme="dark"] .ks-card .bg-amber-50   { background-color: rgba(245,158,11,0.13)  !important; }
[data-theme="dark"] .ks-card .text-indigo-700  { color: #a5b4fc !important; }
[data-theme="dark"] .ks-card .text-emerald-700 { color: #6ee7b7 !important; }
[data-theme="dark"] .ks-card .text-amber-700   { color: #fcd34d !important; }

/* System status strip */
[data-theme="dark"] #ks-system-status {
    background: rgba(16,185,129,0.08) !important;
    border-color: rgba(16,185,129,0.22) !important;
}
[data-theme="dark"] #ks-system-status .text-green-800 { color: #86efac !important; }
[data-theme="dark"] #ks-system-status .text-green-600 { color: #4ade80 !important; }
[data-theme="dark"] #ks-system-status .text-green-400 { color: #22c55e !important; }

/* Summary ring cards */
[data-theme="dark"] .ks-ring-card .text-gray-800 { color: #f1f5f9 !important; }
[data-theme="dark"] .ks-ring-card .text-gray-500 { color: #94a3b8 !important; }
[data-theme="dark"] .ks-ring-card .text-purple-700  { color: #c4b5fd !important; }
[data-theme="dark"] .ks-ring-card .text-purple-600  { color: #c4b5fd !important; }
[data-theme="dark"] .ks-ring-card .text-emerald-700 { color: #6ee7b7 !important; }
[data-theme="dark"] .ks-ring-card .text-emerald-600 { color: #6ee7b7 !important; }
[data-theme="dark"] .ks-ring-card .text-amber-700   { color: #fcd34d !important; }
[data-theme="dark"] .ks-ring-card .text-amber-600   { color: #fcd34d !important; }
[data-theme="dark"] .ks-ring-card .bg-purple-50  { background-color: rgba(139,92,246,0.13) !important; }
[data-theme="dark"] .ks-ring-card .bg-emerald-50 { background-color: rgba(16,185,129,0.13) !important; }
[data-theme="dark"] .ks-ring-card .bg-amber-50   { background-color: rgba(245,158,11,0.13) !important; }
[data-theme="dark"] .ks-ring-card svg circle[stroke="#e2e8f0"] { stroke: #334155; }

/* Overdue section */
[data-theme="dark"] .ks-section-title.text-red-700    { color: #fca5a5 !important; }
[data-theme="dark"] .ks-card .bg-red-50               { background-color: rgba(239,68,68,0.10) !important; }
[data-theme="dark"] .ks-card .border-red-100          { border-color:     rgba(239,68,68,0.22) !important; }
[data-theme="dark"] .ks-card .bg-red-100              { background-color: rgba(239,68,68,0.15) !important; }
</style>

<div class="max-w-7xl mx-auto ks-dashboard">

    {{-- ===== Header ===== --}}
    <div class="flex items-center justify-between mb-6 ks-anim ks-d1">
        <div>
            <h1 class="text-2xl font-extrabold text-gray-900 leading-tight">Dashboard Kepala Sekolah</h1>
            <p class="text-sm text-gray-500 mt-0.5">Sistem Informasi Perpustakaan — Ringkasan Hari Ini</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="text-right hidden sm:block">
                <div class="text-xs font-semibold text-gray-400 uppercase tracking-wide">{{ now()->isoFormat('dddd') }}</div>
                <div class="text-sm font-bold text-gray-700">{{ now()->isoFormat('D MMMM Y') }}</div>
            </div>
            <div class="w-10 h-10 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-xl flex items-center justify-center">
                <i class="fas fa-graduation-cap text-white text-sm"></i>
            </div>
        </div>
    </div>

    {{-- ===== Stat Cards ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Anggota --}}
        <div class="ks-stat ks-anim ks-d2">
            <div class="ks-stat-glow" style="background:#3b82f6;"></div>
            <div class="ks-stat-icon" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#3b82f6;">
                <i class="fas fa-users"></i>
            </div>
            <div class="ks-stat-value text-gray-900">{{ number_format($totalAnggota) }}</div>
            <div class="ks-stat-label">Total Anggota</div>
            <div class="ks-stat-badge" style="background:#eff6ff;color:#3b82f6;">
                <i class="fas fa-circle text-[6px]"></i> Terdaftar
            </div>
        </div>

        {{-- Buku --}}
        <div class="ks-stat ks-anim ks-d3">
            <div class="ks-stat-glow" style="background:#10b981;"></div>
            <div class="ks-stat-icon" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);color:#10b981;">
                <i class="fas fa-book"></i>
            </div>
            <div class="ks-stat-value text-gray-900">{{ number_format($totalBuku) }}</div>
            <div class="ks-stat-label">Total Buku</div>
            <div class="ks-stat-badge" style="background:#ecfdf5;color:#10b981;">
                <i class="fas fa-circle text-[6px]"></i> Koleksi
            </div>
        </div>

        {{-- Sedang Dipinjam --}}
        <div class="ks-stat ks-anim ks-d4">
            <div class="ks-stat-glow" style="background:#8b5cf6;"></div>
            <div class="ks-stat-icon" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);color:#8b5cf6;">
                <i class="fas fa-book-reader"></i>
            </div>
            <div class="ks-stat-value text-gray-900">{{ number_format($totalPeminjaman) }}</div>
            <div class="ks-stat-label">Sedang Dipinjam</div>
            @if($terlambat > 0)
            <div class="ks-stat-badge" style="background:#fef2f2;color:#ef4444;">
                <i class="fas fa-exclamation-circle text-[8px]"></i> {{ $terlambat }} terlambat
            </div>
            @else
            <div class="ks-stat-badge" style="background:#f0fdf4;color:#16a34a;">
                <i class="fas fa-check-circle text-[8px]"></i> Tepat waktu
            </div>
            @endif
        </div>

        {{-- Denda --}}
        <div class="ks-stat ks-anim ks-d5">
            <div class="ks-stat-glow" style="background:#f59e0b;"></div>
            <div class="ks-stat-icon" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);color:#f59e0b;">
                <i class="fas fa-coins"></i>
            </div>
            <div class="ks-stat-value" style="color:#d97706;">Rp {{ number_format($totalDendaNominal, 0, ',', '.') }}</div>
            <div class="ks-stat-label">Denda Belum Dibayar</div>
            <div class="ks-stat-badge" style="background:#fffbeb;color:#d97706;">
                <i class="fas fa-circle text-[6px]"></i> {{ $jumlahDendaBelum }} kasus
            </div>
        </div>

    </div>

    {{-- ===== Row 2: Chart + Quick Links ===== --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-4">

        {{-- Chart 7 hari --}}
        <div class="ks-card lg:col-span-2 ks-anim ks-d6">
            <div class="ks-section-header">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-area text-indigo-600 text-xs"></i>
                    </div>
                    <span class="ks-section-title">Aktivitas 7 Hari Terakhir</span>
                </div>
                <span class="text-[11px] text-gray-400 font-medium">{{ now()->subDays(6)->format('d M') }} – {{ now()->format('d M Y') }}</span>
            </div>
            <div class="p-4">
                <canvas id="activityChart" style="height:190px;"></canvas>
            </div>
            <div class="px-5 pb-4 grid grid-cols-3 gap-3">
                <div class="bg-indigo-50 rounded-xl p-3 text-center">
                    <div class="text-lg font-extrabold text-indigo-700">{{ $peminjamanBulanIni }}</div>
                    <div class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide mt-0.5">Peminjaman Bulan Ini</div>
                </div>
                <div class="bg-emerald-50 rounded-xl p-3 text-center">
                    <div class="text-lg font-extrabold text-emerald-700">{{ $pengembalianBulanIni }}</div>
                    <div class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide mt-0.5">Pengembalian Bulan Ini</div>
                </div>
                <div class="bg-amber-50 rounded-xl p-3 text-center">
                    <div class="text-lg font-extrabold text-amber-700">{{ $dendaBulanIni }}</div>
                    <div class="text-[10px] text-gray-500 font-semibold uppercase tracking-wide mt-0.5">Denda Bulan Ini</div>
                </div>
            </div>
        </div>

        {{-- Quick Links --}}
        <div class="ks-card ks-anim ks-d7">
            <div class="ks-section-header">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-th-large text-purple-600 text-xs"></i>
                    </div>
                    <span class="ks-section-title">Akses Cepat</span>
                </div>
            </div>
            <div class="p-4 grid grid-cols-2 gap-3">
                <a href="{{ route('laporan.index') }}" class="ks-qlink" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);">
                    <div class="ks-qlink-icon" style="background:linear-gradient(135deg,#3b82f6,#2563eb);">
                        <i class="fas fa-chart-bar text-white text-sm"></i>
                    </div>
                    <span class="ks-qlink-label text-blue-700">Laporan</span>
                </a>
                <a href="{{ route('kepsek.data-anggota') }}" class="ks-qlink" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);">
                    <div class="ks-qlink-icon" style="background:linear-gradient(135deg,#10b981,#059669);">
                        <i class="fas fa-users text-white text-sm"></i>
                    </div>
                    <span class="ks-qlink-label text-emerald-700">Data Anggota</span>
                </a>
                <a href="{{ route('kepsek.data-buku') }}" class="ks-qlink" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);">
                    <div class="ks-qlink-icon" style="background:linear-gradient(135deg,#8b5cf6,#7c3aed);">
                        <i class="fas fa-book text-white text-sm"></i>
                    </div>
                    <span class="ks-qlink-label text-purple-700">Data Buku</span>
                </a>
                <a href="{{ route('kepsek.riwayat-peminjaman') }}" class="ks-qlink" style="background:linear-gradient(135deg,#fff7ed,#ffedd5);">
                    <div class="ks-qlink-icon" style="background:linear-gradient(135deg,#f97316,#ea580c);">
                        <i class="fas fa-history text-white text-sm"></i>
                    </div>
                    <span class="ks-qlink-label text-orange-700">Riwayat Pinjam</span>
                </a>
                <a href="{{ route('kepsek.riwayat-pengembalian') }}" class="ks-qlink" style="background:linear-gradient(135deg,#fff1f2,#ffe4e6);">
                    <div class="ks-qlink-icon" style="background:linear-gradient(135deg,#ef4444,#dc2626);">
                        <i class="fas fa-undo-alt text-white text-sm"></i>
                    </div>
                    <span class="ks-qlink-label text-red-700">Riwayat Kembali</span>
                </a>
                @if(Auth::user()->hasAnyPermission(['denda.view']))
                <a href="{{ route('admin.denda.index') }}" class="ks-qlink" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);">
                    <div class="ks-qlink-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                        <i class="fas fa-coins text-white text-sm"></i>
                    </div>
                    <span class="ks-qlink-label text-amber-700">Data Denda</span>
                </a>
                @else
                <a href="{{ route('admin.buku-tamu.index') }}" class="ks-qlink" style="background:linear-gradient(135deg,#f0fdfa,#ccfbf1);">
                    <div class="ks-qlink-icon" style="background:linear-gradient(135deg,#14b8a6,#0d9488);">
                        <i class="fas fa-clipboard-list text-white text-sm"></i>
                    </div>
                    <span class="ks-qlink-label text-teal-700">Buku Tamu</span>
                </a>
                @endif
            </div>

            {{-- System status strip --}}
            <div id="ks-system-status" class="mx-4 mb-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-100 rounded-xl px-4 py-3 flex items-center gap-3">
                <div class="w-2.5 h-2.5 bg-green-500 rounded-full animate-pulse flex-shrink-0"></div>
                <div class="flex-1">
                    <div class="text-xs font-semibold text-green-800">Sistem Online</div>
                    <div class="text-[10px] text-green-600" id="liveTime">{{ now()->format('H:i') }} WITA</div>
                </div>
                <i class="fas fa-shield-alt text-green-400 text-sm"></i>
            </div>
        </div>

    </div>

    {{-- ===== Row 3: Overdue alert ===== --}}
    @if($terlambat > 0)
    <div class="ks-card ks-anim ks-d8 mb-4">
        <div class="ks-section-header">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600 text-xs"></i>
                </div>
                <span class="ks-section-title text-red-700">Anggota Terlambat Mengembalikan</span>
            </div>
            <span class="inline-flex items-center gap-1 text-[11px] font-bold text-red-700 bg-red-50 border border-red-200 px-2.5 py-1 rounded-full">
                <i class="fas fa-clock text-[9px]"></i>
                {{ $terlambat }} peminjaman
            </span>
        </div>
        @forelse($anggotaTerlambat as $p)
        <div class="ks-overdue-row">
            <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-red-400 to-rose-500 flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                {{ strtoupper(substr($p->anggota->nama_lengkap ?? 'N', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="text-sm font-semibold text-gray-900 truncate">{{ $p->anggota->nama_lengkap ?? '-' }}</div>
                <div class="text-[11px] text-gray-400">{{ $p->anggota->nomor_anggota ?? '' }}</div>
            </div>
            <div class="text-right flex-shrink-0">
                <div class="text-xs font-semibold text-red-600">
                    {{ \Carbon\Carbon::parse($p->tanggal_harus_kembali)->diffInDays(now()) }} hari terlambat
                </div>
                <div class="text-[10px] text-gray-400">
                    Batas: {{ \Carbon\Carbon::parse($p->tanggal_harus_kembali)->format('d M Y') }}
                </div>
            </div>
        </div>
        @empty
        @endforelse
        @if($terlambat > 5)
        <div class="px-5 py-3 bg-red-50 border-t border-red-100 text-center">
            <a href="{{ route('admin.pengembalian.index') }}" class="text-xs font-semibold text-red-600 hover:text-red-700">
                Lihat semua {{ $terlambat }} peminjaman terlambat <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        @endif
    </div>
    @endif

    {{-- ===== Summary Ring ===== --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 ks-anim ks-d8">
        {{-- Sirkulasi --}}
        <div class="ks-card ks-ring-card p-5 flex items-center gap-4">
            <div class="relative w-16 h-16 flex-shrink-0">
                <svg class="w-16 h-16 -rotate-90" viewBox="0 0 64 64">
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="6"/>
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#8b5cf6" stroke-width="6"
                        stroke-dasharray="{{ min(($totalPeminjaman / max($totalBuku, 1)) * 175.9, 175.9) }} 175.9"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-purple-700">
                    {{ number_format(($totalPeminjaman / max($totalBuku, 1)) * 100, 0) }}%
                </div>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-800">Sirkulasi Buku</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ number_format($totalPeminjaman) }} dari {{ number_format($totalBuku) }} buku dipinjam</div>
                <div class="text-[10px] text-purple-600 font-semibold mt-1.5 bg-purple-50 px-2 py-0.5 rounded-full inline-block">Tingkat Pemakaian</div>
            </div>
        </div>

        {{-- Ketersediaan --}}
        <div class="ks-card ks-ring-card p-5 flex items-center gap-4">
            <div class="relative w-16 h-16 flex-shrink-0">
                @php $tersedia = max($totalBuku - $totalPeminjaman, 0); $pct = ($tersedia / max($totalBuku, 1)) * 100; @endphp
                <svg class="w-16 h-16 -rotate-90" viewBox="0 0 64 64">
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="6"/>
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#10b981" stroke-width="6"
                        stroke-dasharray="{{ min($pct * 1.759, 175.9) }} 175.9"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-emerald-700">
                    {{ number_format($pct, 0) }}%
                </div>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-800">Buku Tersedia</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ number_format($tersedia) }} buku siap dipinjam</div>
                <div class="text-[10px] text-emerald-600 font-semibold mt-1.5 bg-emerald-50 px-2 py-0.5 rounded-full inline-block">Stok Tersedia</div>
            </div>
        </div>

        {{-- Penyelesaian Denda --}}
        <div class="ks-card ks-ring-card p-5 flex items-center gap-4">
            @php
                $totalDendaAll = \App\Models\Denda::count();
                $lunas = max($totalDendaAll - $jumlahDendaBelum, 0);
                $pctLunas = ($lunas / max($totalDendaAll, 1)) * 100;
            @endphp
            <div class="relative w-16 h-16 flex-shrink-0">
                <svg class="w-16 h-16 -rotate-90" viewBox="0 0 64 64">
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#e2e8f0" stroke-width="6"/>
                    <circle cx="32" cy="32" r="28" fill="none" stroke="#f59e0b" stroke-width="6"
                        stroke-dasharray="{{ min($pctLunas * 1.759, 175.9) }} 175.9"
                        stroke-linecap="round"/>
                </svg>
                <div class="absolute inset-0 flex items-center justify-center text-xs font-bold text-amber-700">
                    {{ number_format($pctLunas, 0) }}%
                </div>
            </div>
            <div>
                <div class="text-sm font-bold text-gray-800">Penyelesaian Denda</div>
                <div class="text-xs text-gray-500 mt-0.5">{{ number_format($lunas) }} dari {{ number_format($totalDendaAll) }} kasus lunas</div>
                <div class="text-[10px] text-amber-600 font-semibold mt-1.5 bg-amber-50 px-2 py-0.5 rounded-full inline-block">Tingkat Lunas</div>
            </div>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // ── Live clock ──
    function tick() {
        const el = document.getElementById('liveTime');
        if (!el) return;
        const now = new Date();
        const h = String(now.getHours()).padStart(2,'0');
        const m = String(now.getMinutes()).padStart(2,'0');
        el.textContent = h + ':' + m + ' WITA';
    }
    setInterval(tick, 1000);

    // ── Activity Chart ──
    const ctx = document.getElementById('activityChart')?.getContext('2d');
    if (!ctx) return;

    const isDark   = document.documentElement.getAttribute('data-theme') === 'dark';
    const gridClr  = isDark ? '#334155' : '#f1f5f9';
    const tickClr  = isDark ? '#94a3b8' : '#6b7280';
    const legendClr = isDark ? '#cbd5e1' : '#374151';

    const labels  = {!! json_encode($chartLabels) !!};
    const pinjam  = {!! json_encode($chartPinjam) !!};
    const kembali = {!! json_encode($chartKembali) !!};

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Peminjaman',
                    data: pinjam,
                    backgroundColor: 'rgba(99,102,241,.75)',
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 14,
                },
                {
                    label: 'Pengembalian',
                    data: kembali,
                    backgroundColor: 'rgba(16,185,129,.75)',
                    borderRadius: 6,
                    borderSkipped: false,
                    barThickness: 14,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { color: legendClr, font: { size: 11, weight: '600' }, boxWidth: 10, padding: 14 }
                },
                tooltip: {
                    backgroundColor: isDark ? '#0f172a' : '#1e293b',
                    titleColor: '#f1f5f9',
                    bodyColor: '#cbd5e1',
                    titleFont: { size: 11 }, bodyFont: { size: 11 },
                    padding: 10, cornerRadius: 8,
                }
            },
            scales: {
                x: { grid: { display: false }, ticks: { color: tickClr, font: { size: 10 } } },
                y: {
                    beginAtZero: true,
                    grid: { color: gridClr },
                    ticks: { color: tickClr, font: { size: 10 }, stepSize: 1 }
                }
            }
        }
    });
});
</script>
@endsection
