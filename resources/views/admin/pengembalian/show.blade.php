@extends('layouts.admin')

@section('title', 'Detail Pengembalian — ' . $pengembalian->nomor_pengembalian)

@push('styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-border {
        0%, 100% { box-shadow: 0 0 0 0 rgba(239,68,68,.15); }
        50%       { box-shadow: 0 0 0 6px rgba(239,68,68,.0); }
    }
    @keyframes pulse-dot {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%       { transform: scale(1.7); opacity: .4; }
    }
    .anim-up    { animation: fadeInUp .45s ease both; }
    .anim-up.d1 { animation-delay: .04s; }
    .anim-up.d2 { animation-delay: .09s; }
    .anim-up.d3 { animation-delay: .14s; }
    .anim-up.d4 { animation-delay: .19s; }
    .anim-up.d5 { animation-delay: .24s; }

    /* ── Info item ─────────────────────────────────── */
    .info-row {
        display: flex; flex-direction: column; gap: 3px;
        padding: 14px 0; border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child { border-bottom: none; padding-bottom: 0; }
    .info-label { font-size: 10.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
    .info-value { font-size: 14px; font-weight: 600; color: #1e293b; }

    /* ── Section card ──────────────────────────────── */
    .section-card {
        background: white; border-radius: 18px;
        border: 1px solid #f1f5f9; overflow: hidden;
        box-shadow: 0 1px 8px rgba(0,0,0,.04);
    }
    .section-header {
        padding: 16px 20px; display: flex; align-items: center; gap: 12px;
    }
    .section-header-icon {
        width: 38px; height: 38px; border-radius: 11px;
        display: flex; align-items: center; justify-content: center;
        font-size: 15px; color: white; flex-shrink: 0;
    }
    .section-body { padding: 20px; }

    /* ── Key metrics strip ─────────────────────────── */
    .metric-strip {
        display: grid; grid-template-columns: repeat(2, 1fr);
        gap: 1px; background: rgba(255,255,255,.15);
    }
    @media (min-width: 640px) { .metric-strip { grid-template-columns: repeat(4, 1fr); } }
    .metric-item {
        padding: 16px 20px; background: rgba(255,255,255,.08);
        display: flex; flex-direction: column; gap: 4px;
    }
    .metric-label { font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: .07em; color: rgba(255,255,255,.65); }
    .metric-value { font-size: 14px; font-weight: 700; color: white; }
    .metric-sub   { font-size: 11px; color: rgba(255,255,255,.7); }

    /* ── Status badge ──────────────────────────────── */
    .status-pill {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 5px 14px; border-radius: 20px; font-size: 12px; font-weight: 700;
    }
    .status-pill.selesai   { background: rgba(255,255,255,.2); color: white; border: 1px solid rgba(255,255,255,.3); }
    .status-pill.terlambat { background: rgba(239,68,68,.25); color: #fecaca; border: 1px solid rgba(239,68,68,.3); }
    .status-dot { width: 7px; height: 7px; border-radius: 50%; }
    .status-dot.green { background: #4ade80; animation: pulse-dot 2s infinite; }
    .status-dot.red   { background: #f87171; animation: pulse-dot 2s infinite; }

    /* ── Kondisi badge ─────────────────────────────── */
    .kondisi-baik    { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .kondisi-rusak   { background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
    .kondisi-sedang  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .kondisi-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 600; }

    /* ── Book table ────────────────────────────────── */
    .book-table thead th {
        background: linear-gradient(135deg,#f8fafc,#f1f5f9);
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #64748b; padding: 11px 14px;
        border-bottom: 2px solid #e2e8f0;
    }
    .book-table tbody td {
        padding: 13px 14px; font-size: 13px; vertical-align: middle;
        border-bottom: 1px solid #f8fafc;
    }
    .book-table tbody tr:last-child td { border-bottom: none; }
    .book-table tbody tr:hover { background: #f8fafc; }

    /* ── Denda amount ──────────────────────────────── */
    .denda-big {
        font-size: 32px; font-weight: 800; letter-spacing: -.03em; line-height: 1;
    }

    /* ── Payment status ────────────────────────────── */
    .payment-card {
        border-radius: 14px; padding: 14px 16px;
        display: flex; align-items: center; gap: 12px;
    }
    .payment-icon {
        width: 40px; height: 40px; border-radius: 11px; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; font-size: 15px; color: white;
    }

    /* ── Modal ─────────────────────────────────────── */
    .modal-backdrop { backdrop-filter: blur(4px); background: rgba(15,23,42,.42); }
    .modal-anim     { animation: fadeInUp .28s ease both; }

    /* Print */
    @media print {
        .no-print { display: none !important; }
        .section-card { box-shadow: none; border: 1px solid #e2e8f0; }
    }

    /* ══════════════════════════════════════════════
       DARK MODE OVERRIDES — Pengembalian Show
    ══════════════════════════════════════════════ */
    html[data-theme="dark"] .section-card {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    html[data-theme="dark"] .info-row { border-color: #334155 !important; }
    html[data-theme="dark"] .info-value { color: #f1f5f9 !important; }
    html[data-theme="dark"] .book-table thead th {
        background: linear-gradient(135deg, #1e293b, #0f172a) !important;
        color: #64748b !important;
        border-color: #334155 !important;
    }
    html[data-theme="dark"] .book-table tbody td { border-color: #1e293b !important; }
    html[data-theme="dark"] .book-table tbody tr:hover { background: rgba(99,102,241,0.07) !important; }
    html[data-theme="dark"] .kondisi-baik {
        background: rgba(16,185,129,0.15) !important;
        border-color: rgba(16,185,129,0.3) !important;
        color: #34d399 !important;
    }
    html[data-theme="dark"] .kondisi-rusak {
        background: rgba(239,68,68,0.15) !important;
        border-color: rgba(239,68,68,0.3) !important;
        color: #f87171 !important;
    }
    html[data-theme="dark"] .kondisi-sedang {
        background: rgba(245,158,11,0.15) !important;
        border-color: rgba(245,158,11,0.3) !important;
        color: #fbbf24 !important;
    }
    /* Back/print action buttons */
    html[data-theme="dark"] .bg-white.border.border-gray-200.text-gray-700 {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #cbd5e1 !important;
    }
    html[data-theme="dark"] .bg-white.border.border-gray-200.text-gray-700:hover {
        background: #334155 !important;
    }
    /* Payment card inside show */
    html[data-theme="dark"] .payment-card {
        background: #0f172a !important;
        border-color: #334155 !important;
    }
    /* Modal in show */
    html[data-theme="dark"] .modal-anim.bg-white,
    html[data-theme="dark"] .rounded-2xl.bg-white,
    html[data-theme="dark"] .rounded-xl.bg-white {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    html[data-theme="dark"] .border-t.border-gray-100 { border-color: #334155 !important; }
    html[data-theme="dark"] .bg-gray-50.rounded-xl { background: #0f172a !important; }
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- ─── Breadcrumb + Actions ──────────────────── --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 anim-up d1 no-print">
        <div class="flex items-center gap-2 text-sm text-gray-500">
            <a href="{{ route('pengembalian.index') }}" class="hover:text-emerald-600 transition-colors font-medium">Pengembalian</a>
            <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
            <span class="text-gray-800 font-semibold">{{ $pengembalian->nomor_pengembalian }}</span>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('pengembalian.index') }}"
               class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-all shadow-sm">
                <i class="fas fa-arrow-left text-xs"></i>Kembali
            </a>
            <button onclick="window.print()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-semibold hover:bg-gray-50 transition-all shadow-sm">
                <i class="fas fa-print text-xs text-blue-500"></i>Cetak
            </button>
            @if(Auth::user()->isAdmin() && $pengembalian->total_denda > 0)
            <button onclick="openStatusPembayaranModal()"
                    class="flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 text-white rounded-xl text-sm font-semibold hover:from-emerald-600 hover:to-teal-700 transition-all shadow-md">
                <i class="fas fa-money-bill-wave text-xs"></i>
                {{ $pengembalian->status_denda === 'belum_dibayar' ? 'Bayar Denda' : 'Update Denda' }}
            </button>
            @endif
        </div>
    </div>

    {{-- ─── Hero Header Card ──────────────────────── --}}
    @php
        $isLate       = $pengembalian->jumlah_hari_terlambat > 0;
        $dendaPaid    = $pengembalian->status_denda === 'sudah_dibayar';
        $hasDenda     = $pengembalian->total_denda > 0;
        $gradientFrom = $isLate ? 'from-red-500' : 'from-emerald-500';
        $gradientTo   = $isLate ? 'to-rose-600'  : 'to-teal-600';
    @endphp
    <div class="rounded-2xl overflow-hidden shadow-lg anim-up d1 bg-gradient-to-r {{ $gradientFrom }} {{ $gradientTo }}">
        <!-- Top row: title + status -->
        <div class="px-6 pt-6 pb-4 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-undo-alt text-white text-2xl"></i>
                </div>
                <div>
                    <p class="text-white/70 text-xs font-semibold uppercase tracking-widest mb-1">Pengembalian</p>
                    <h1 class="text-xl sm:text-2xl font-extrabold text-white tracking-tight">{{ $pengembalian->nomor_pengembalian }}</h1>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                @if($isLate)
                    <span class="status-pill terlambat">
                        <span class="status-dot red"></span>
                        Terlambat {{ $pengembalian->jumlah_hari_terlambat }} hari
                    </span>
                @else
                    <span class="status-pill selesai">
                        <span class="status-dot green"></span>
                        Tepat Waktu
                    </span>
                @endif
                @if($hasDenda)
                    @if($dendaPaid)
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-white/20 text-white border border-white/25">
                        <i class="fas fa-check-double text-emerald-300"></i> Denda Lunas
                    </span>
                    @else
                    <span class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-bold bg-red-400/30 text-red-100 border border-red-300/30">
                        <i class="fas fa-exclamation-circle"></i> Denda Belum Bayar
                    </span>
                    @endif
                @endif
            </div>
        </div>
        <!-- Metric strip -->
        <div class="metric-strip">
            <div class="metric-item">
                <span class="metric-label"><i class="far fa-calendar-alt mr-1"></i>Tanggal Kembali</span>
                <span class="metric-value">{{ $pengembalian->tanggal_pengembalian->format('d M Y') }}</span>
                <span class="metric-sub">{{ $pengembalian->jam_pengembalian ? (is_string($pengembalian->jam_pengembalian) ? substr($pengembalian->jam_pengembalian,0,5) : $pengembalian->jam_pengembalian->format('H:i')) . ' WIB' : '' }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label"><i class="fas fa-user mr-1"></i>Anggota</span>
                <span class="metric-value">{{ $pengembalian->anggota->nama_lengkap }}</span>
                <span class="metric-sub">{{ $pengembalian->anggota->nomor_anggota }}</span>
            </div>
            <div class="metric-item">
                <span class="metric-label"><i class="fas fa-books mr-1"></i>Jumlah Buku</span>
                <span class="metric-value">{{ $pengembalian->detailPengembalian->count() }} Buku</span>
                <span class="metric-sub">Dikembalikan</span>
            </div>
            <div class="metric-item">
                <span class="metric-label"><i class="fas fa-coins mr-1"></i>Total Denda</span>
                <span class="metric-value">Rp {{ number_format($pengembalian->total_denda, 0, ',', '.') }}</span>
                <span class="metric-sub">{{ $hasDenda ? ($dendaPaid ? 'Lunas' : 'Belum dibayar') : 'Tidak ada denda' }}</span>
            </div>
        </div>
    </div>

    {{-- ─── Main Grid ─────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

        {{-- Left Column (2/3) --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Informasi Pengembalian --}}
            <div class="section-card anim-up d2">
                <div class="section-header bg-gradient-to-r from-blue-500 to-indigo-600">
                    <div class="section-header-icon" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-undo-alt text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Informasi Pengembalian</h3>
                        <p class="text-blue-100 text-xs">Data transaksi pengembalian</p>
                    </div>
                </div>
                <div class="section-body">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8">
                        <div class="info-row">
                            <span class="info-label">Nomor Pengembalian</span>
                            <span class="info-value">
                                <span class="inline-flex items-center gap-1.5 bg-blue-50 text-blue-700 px-2.5 py-1 rounded-lg text-[11px] font-bold border border-blue-200">
                                    <i class="fas fa-hashtag" style="font-size:9px;opacity:.7"></i>{{ $pengembalian->nomor_pengembalian }}
                                </span>
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Tanggal Pengembalian</span>
                            <span class="info-value">
                                <i class="far fa-calendar-alt mr-1.5 text-gray-400"></i>{{ $pengembalian->tanggal_pengembalian->format('d F Y') }}
                            </span>
                            <span class="text-xs text-gray-400">
                                <i class="far fa-clock mr-1"></i>
                                {{ $pengembalian->jam_pengembalian
                                    ? (is_string($pengembalian->jam_pengembalian)
                                        ? substr($pengembalian->jam_pengembalian, 0, 5)
                                        : $pengembalian->jam_pengembalian->format('H:i'))
                                    : '-' }} WIB
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status Keterlambatan</span>
                            <span class="info-value">
                                @if($isLate)
                                    <span class="inline-flex items-center gap-1.5 bg-red-50 text-red-700 px-2.5 py-1 rounded-lg text-xs font-bold border border-red-200">
                                        <i class="fas fa-clock text-red-500"></i>Terlambat {{ $pengembalian->jumlah_hari_terlambat }} Hari
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 bg-emerald-50 text-emerald-700 px-2.5 py-1 rounded-lg text-xs font-bold border border-emerald-200">
                                        <i class="fas fa-check-circle text-emerald-500"></i>Tepat Waktu
                                    </span>
                                @endif
                            </span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Petugas</span>
                            <div class="flex items-center gap-2 mt-1">
                                <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-slate-400 to-slate-500 flex items-center justify-center text-white text-[10px] font-bold">
                                    {{ strtoupper(substr($pengembalian->user->name ?? 'P', 0, 1)) }}
                                </div>
                                <span class="info-value">{{ $pengembalian->user->name ?? '-' }}</span>
                            </div>
                        </div>
                        @if($pengembalian->status)
                        <div class="info-row sm:col-span-2">
                            <span class="info-label">Status</span>
                            <span class="info-value capitalize">{{ $pengembalian->status }}</span>
                        </div>
                        @endif
                        @if($pengembalian->catatan)
                        <div class="info-row sm:col-span-2">
                            <span class="info-label">Catatan</span>
                            <p class="text-sm text-gray-700 bg-gray-50 rounded-xl px-4 py-3 mt-1 border border-gray-100">{{ $pengembalian->catatan }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Informasi Anggota --}}
            <div class="section-card anim-up d3">
                <div class="section-header bg-gradient-to-r from-emerald-500 to-teal-600">
                    <div class="section-header-icon" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-user text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Informasi Anggota</h3>
                        <p class="text-emerald-100 text-xs">Data peminjam buku</p>
                    </div>
                </div>
                <div class="section-body">
                    <div class="flex items-center gap-4 mb-5 p-4 bg-gradient-to-r from-emerald-50 to-teal-50 rounded-2xl border border-emerald-100">
                        @php
                            $gradients = ['#10b981,#059669','#3b82f6,#2563eb','#8b5cf6,#7c3aed','#f59e0b,#d97706','#ef4444,#dc2626'];
                            $grad = $gradients[$pengembalian->anggota->id % 5];
                        @endphp
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-white text-xl font-bold shadow-md flex-shrink-0"
                             style="background:linear-gradient(135deg,{{ $grad }});">
                            {{ strtoupper(substr($pengembalian->anggota->nama_lengkap, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-base font-bold text-gray-900">{{ $pengembalian->anggota->nama_lengkap }}</p>
                            <p class="text-sm text-gray-500">{{ $pengembalian->anggota->nomor_anggota }}</p>
                            @if($pengembalian->anggota->kelas)
                            <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 bg-emerald-100 px-2.5 py-0.5 rounded-lg mt-1">
                                <i class="fas fa-graduation-cap text-[9px]"></i>{{ $pengembalian->anggota->kelas->nama_kelas }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-x-8">
                        <div class="info-row">
                            <span class="info-label">Nomor Anggota</span>
                            <span class="info-value">{{ $pengembalian->anggota->nomor_anggota }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Kelas</span>
                            <span class="info-value">{{ $pengembalian->anggota->kelas->nama_kelas ?? 'N/A' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Jenis Anggota</span>
                            <span class="info-value capitalize">{{ $pengembalian->anggota->jenis_anggota ?? '-' }}</span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Status</span>
                            <span class="info-value">
                                <span class="inline-flex items-center gap-1 text-xs font-semibold px-2.5 py-0.5 rounded-lg {{ $pengembalian->anggota->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ ucfirst($pengembalian->anggota->status ?? '-') }}
                                </span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Detail Buku Dikembalikan --}}
            <div class="section-card anim-up d4">
                <div class="section-header bg-gradient-to-r from-violet-500 to-purple-600">
                    <div class="section-header-icon" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-book text-white"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="text-sm font-bold text-white">Detail Buku Dikembalikan</h3>
                        <p class="text-violet-100 text-xs">{{ $pengembalian->detailPengembalian->count() }} buku dikembalikan</p>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="book-table w-full" style="min-width:520px;">
                        <thead>
                            <tr>
                                <th class="text-left">Judul Buku</th>
                                <th class="text-left">Kategori</th>
                                <th class="text-center" style="width:70px;">Jml</th>
                                <th class="text-center">Kondisi</th>
                                <th class="text-right">Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pengembalian->detailPengembalian as $i => $detail)
                            <tr>
                                <td>
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-100 to-purple-100 border border-violet-200 flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-book text-violet-500" style="font-size:11px;"></i>
                                        </div>
                                        <div>
                                            <p class="text-sm font-semibold text-gray-900 leading-tight">{{ $detail->buku->judul_buku }}</p>
                                            @if($detail->buku->pengarang)
                                            <p class="text-[11px] text-gray-400 mt-0.5">{{ $detail->buku->pengarang }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-xs text-gray-600 font-medium bg-gray-100 px-2.5 py-1 rounded-lg">
                                        {{ $detail->buku->kategoriBuku->nama_kategori ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-violet-50 border border-violet-200 text-violet-700 text-xs font-bold">
                                        {{ $detail->jumlah_dikembalikan }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @php
                                        $kondisiMap = [
                                            'baik'          => ['kondisi-baik',   'fa-check-circle', 'Baik'],
                                            'sedikit_rusak' => ['kondisi-sedang', 'fa-minus-circle', 'Sedikit Rusak'],
                                            'rusak'         => ['kondisi-rusak',  'fa-times-circle', 'Rusak'],
                                        ];
                                        $kondisi = $kondisiMap[$detail->kondisi_buku ?? 'baik'] ?? $kondisiMap['baik'];
                                    @endphp
                                    <span class="kondisi-badge {{ $kondisi[0] }}">
                                        <i class="fas {{ $kondisi[1] }}" style="font-size:9px;"></i>
                                        {{ $kondisi[2] }}
                                    </span>
                                </td>
                                <td class="text-right">
                                    @if(isset($detail->denda_buku) && $detail->denda_buku > 0)
                                        <span class="text-sm font-bold text-red-600">Rp {{ number_format($detail->denda_buku, 0, ',', '.') }}</span>
                                    @else
                                        <span class="text-xs text-gray-300 font-medium">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Right Column (1/3) --}}
        <div class="space-y-5">

            {{-- Denda Card --}}
            <div class="section-card anim-up d2">
                @if($dendaPaid)
                    <div class="section-header bg-gradient-to-r from-emerald-500 to-teal-600">
                @elseif($hasDenda)
                    <div class="section-header bg-gradient-to-r from-red-500 to-rose-600">
                @else
                    <div class="section-header bg-gradient-to-r from-gray-400 to-slate-500">
                @endif
                    <div class="section-header-icon" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-coins text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Informasi Denda</h3>
                        <p class="text-white/70 text-xs">
                            {{ $hasDenda ? ($dendaPaid ? 'Pembayaran selesai' : 'Menunggu pembayaran') : 'Tidak ada denda' }}
                        </p>
                    </div>
                </div>

                <div class="section-body space-y-4">
                    <!-- Total Denda Amount -->
                    <div class="text-center py-5 rounded-2xl
                        {{ $hasDenda
                            ? ($dendaPaid
                                ? 'bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100'
                                : 'bg-gradient-to-br from-red-50 to-rose-50 border border-red-100')
                            : 'bg-gradient-to-br from-gray-50 to-slate-50 border border-gray-100' }}">
                        <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-2">Total Denda</p>
                        <p class="denda-big {{ $hasDenda ? ($dendaPaid ? 'text-emerald-600' : 'text-red-600') : 'text-gray-300' }}">
                            Rp&nbsp;{{ number_format($pengembalian->total_denda, 0, ',', '.') }}
                        </p>
                    </div>

                    <!-- Status Pembayaran -->
                    @if($hasDenda && $dendaPaid)
                    <div class="payment-card bg-emerald-50 border border-emerald-200">
                        <div class="payment-icon bg-gradient-to-br from-emerald-500 to-teal-600 shadow-md shadow-emerald-200">
                            <i class="fas fa-check-double"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-emerald-700">Lunas</p>
                            <p class="text-xs text-emerald-500">Denda telah dibayar</p>
                        </div>
                    </div>
                    @elseif($hasDenda)
                    <div class="payment-card bg-red-50 border border-red-200" style="animation: pulse-border 2.5s infinite;">
                        <div class="payment-icon bg-gradient-to-br from-red-500 to-rose-600 shadow-md shadow-red-200">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-700">Belum Dibayar</p>
                            <p class="text-xs text-red-400">Menunggu pelunasan</p>
                        </div>
                    </div>
                    @else
                    <div class="payment-card bg-gray-50 border border-gray-200">
                        <div class="payment-icon bg-gradient-to-br from-gray-400 to-slate-500">
                            <i class="fas fa-check"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-600">Tidak Ada Denda</p>
                            <p class="text-xs text-gray-400">Pengembalian tepat waktu</p>
                        </div>
                    </div>
                    @endif

                    <!-- Keterlambatan -->
                    @if($isLate)
                    <div class="payment-card bg-amber-50 border border-amber-200">
                        <div class="payment-icon bg-gradient-to-br from-amber-400 to-orange-500 shadow-md shadow-amber-200">
                            <i class="fas fa-calendar-times"></i>
                        </div>
                        <div>
                            <p class="text-lg font-extrabold text-amber-700 leading-none">{{ $pengembalian->jumlah_hari_terlambat }} <span class="text-sm font-semibold">hari</span></p>
                            <p class="text-xs text-amber-500">Terlambat dari batas waktu</p>
                        </div>
                    </div>
                    @endif

                    <!-- Tanggal Pembayaran -->
                    @if($pengembalian->tanggal_pembayaran_denda)
                    <div class="payment-card bg-blue-50 border border-blue-200">
                        <div class="payment-icon bg-gradient-to-br from-blue-500 to-indigo-600 shadow-md shadow-blue-200">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-blue-700">{{ $pengembalian->tanggal_pembayaran_denda->format('d M Y') }}</p>
                            <p class="text-xs text-blue-400">Tanggal pelunasan</p>
                        </div>
                    </div>
                    @endif

                    <!-- Tombol Bayar -->
                    @if(Auth::user()->isAdmin() && $hasDenda)
                    <button onclick="openStatusPembayaranModal()"
                            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl text-sm font-bold transition-all
                            {{ $dendaPaid
                                ? 'bg-gradient-to-r from-gray-100 to-slate-100 text-gray-600 hover:from-gray-200 border border-gray-200'
                                : 'bg-gradient-to-r from-emerald-500 to-teal-600 text-white shadow-lg shadow-emerald-500/25 hover:from-emerald-600 hover:to-teal-700 hover:shadow-xl hover:-translate-y-0.5' }}">
                        <i class="fas {{ $dendaPaid ? 'fa-edit' : 'fa-money-bill-wave' }}"></i>
                        {{ $dendaPaid ? 'Ubah Status Denda' : 'Bayar Denda Sekarang' }}
                    </button>
                    @endif
                </div>
            </div>

            {{-- Informasi Peminjaman --}}
            <div class="section-card anim-up d3">
                <div class="section-header bg-gradient-to-r from-indigo-500 to-purple-600">
                    <div class="section-header-icon" style="background:rgba(255,255,255,.2);">
                        <i class="fas fa-book-open text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-white">Informasi Peminjaman</h3>
                        <p class="text-indigo-100 text-xs">Referensi transaksi pinjam</p>
                    </div>
                </div>
                <div class="section-body space-y-0">
                    <div class="info-row">
                        <span class="info-label">Nomor Peminjaman</span>
                        <span class="inline-flex items-center gap-1.5 bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-lg text-[11px] font-bold border border-indigo-200 w-fit mt-1">
                            <i class="fas fa-hashtag" style="font-size:9px;opacity:.7"></i>
                            {{ $pengembalian->peminjaman->nomor_peminjaman ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tanggal Pinjam</span>
                        <span class="info-value">
                            <i class="far fa-calendar-alt mr-1.5 text-gray-400"></i>
                            {{ $pengembalian->peminjaman->tanggal_peminjaman
                                ? $pengembalian->peminjaman->tanggal_peminjaman->format('d M Y')
                                : 'N/A' }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Batas Pengembalian</span>
                        @php
                            $batas    = $pengembalian->peminjaman->tanggal_harus_kembali ?? null;
                            $lewat    = $batas && $batas->isPast() && $isLate;
                        @endphp
                        <span class="info-value {{ $lewat ? 'text-red-600' : '' }}">
                            <i class="far fa-calendar-alt mr-1.5 text-gray-400"></i>
                            {{ $batas ? $batas->format('d M Y') : 'N/A' }}
                        </span>
                        @if($lewat)
                        <span class="text-[11px] text-red-400 font-medium">Terlewat {{ $pengembalian->jumlah_hari_terlambat }} hari</span>
                        @endif
                    </div>
                    @if($pengembalian->peminjaman)
                    <div class="info-row">
                        <span class="info-label">Status Peminjaman</span>
                        <span class="info-value capitalize">{{ $pengembalian->peminjaman->status ?? '-' }}</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>{{-- /right --}}
    </div>{{-- /grid --}}
</div>

{{-- ─── Modal Update Status Denda ────────────────── --}}
<div id="statusPembayaranModal" class="fixed inset-0 hidden z-50">
    <div class="modal-backdrop absolute inset-0" onclick="closeStatusPembayaranModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4 relative z-10">
        <div class="modal-anim bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-money-bill-wave text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Update Pembayaran Denda</h3>
                        <p class="text-emerald-100 text-xs">Total: Rp {{ number_format($pengembalian->total_denda, 0, ',', '.') }}</p>
                    </div>
                </div>
                <button onclick="closeStatusPembayaranModal()" class="w-8 h-8 bg-white/15 hover:bg-white/25 rounded-lg flex items-center justify-center text-white transition-all">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="statusPembayaranForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-flag mr-1 text-emerald-500"></i>Status Pembayaran
                    </label>
                    <select id="modal_status_pembayaran" name="status_pembayaran"
                            class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-emerald-400 focus:border-transparent text-sm font-medium transition-all">
                        <option value="belum_dibayar" {{ $pengembalian->status_denda === 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                        <option value="sudah_dibayar" {{ $pengembalian->status_denda === 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar (Lunas)</option>
                    </select>
                </div>
                <div id="tanggalPembayaranDiv" class="{{ $pengembalian->status_denda === 'sudah_dibayar' ? '' : 'hidden' }}">
                    <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-calendar-check mr-1 text-blue-500"></i>Tanggal Pembayaran
                    </label>
                    <input type="date" id="modal_tanggal_pembayaran" name="tanggal_pembayaran"
                           value="{{ $pengembalian->tanggal_pembayaran_denda ? $pengembalian->tanggal_pembayaran_denda->format('Y-m-d') : date('Y-m-d') }}"
                           class="w-full px-4 py-3 border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-emerald-400 focus:border-transparent text-sm transition-all">
                </div>
                <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    <button type="button" onclick="closeStatusPembayaranModal()"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-all">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-sm font-semibold shadow-md transition-all">
                        <i class="fas fa-check mr-1.5"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const selStatus = document.getElementById('modal_status_pembayaran');
    const divTanggal = document.getElementById('tanggalPembayaranDiv');
    if (selStatus) {
        selStatus.addEventListener('change', function () {
            divTanggal.classList.toggle('hidden', this.value !== 'sudah_dibayar');
        });
    }

    const form = document.getElementById('statusPembayaranForm');
    if (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch('{{ route("pengembalian.update-status-pembayaran-denda", $pengembalian->id) }}', {
                method : 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body   : new FormData(this),
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) { location.reload(); }
                else { alert('Gagal: ' + data.message); }
            })
            .catch(() => alert('Terjadi kesalahan.'));
        });
    }
});

function openStatusPembayaranModal()  { document.getElementById('statusPembayaranModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeStatusPembayaranModal() { document.getElementById('statusPembayaranModal').classList.add('hidden'); document.body.style.overflow = ''; }

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeStatusPembayaranModal(); });
</script>
@endsection
