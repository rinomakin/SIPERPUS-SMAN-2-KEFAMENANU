@extends('layouts.admin')

@section('title', 'Pengembalian Hari Ini')
@section('page-title', 'Pengembalian Hari Ini')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes pulse-ring {
        0%   { transform: scale(1); opacity: .8; }
        100% { transform: scale(1.9); opacity: 0; }
    }
    @keyframes pulse-dot {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%       { transform: scale(1.8); opacity: .4; }
    }
    @keyframes pulse-belum {
        0%, 100% { opacity: 1; }
        50%       { opacity: .7; }
    }
    .anim-up    { animation: fadeInUp .45s ease both; }
    .anim-up.d1 { animation-delay: .04s; }
    .anim-up.d2 { animation-delay: .09s; }
    .anim-up.d3 { animation-delay: .14s; }
    .anim-up.d4 { animation-delay: .19s; }
    .anim-up.d5 { animation-delay: .24s; }

    /* ── Stat Cards ───────────────────────────────── */
    .stat-card {
        background: white; border-radius: 18px; padding: 20px;
        border: 1px solid #f1f5f9; transition: all .3s ease;
        position: relative; overflow: hidden;
    }
    .stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: 18px 18px 0 0;
    }
    .stat-card::after {
        content: ''; position: absolute; bottom: -20px; right: -20px;
        width: 80px; height: 80px; border-radius: 50%;
        opacity: .04;
    }
    .stat-card:hover { transform: translateY(-4px); }
    .stat-card.emerald::before { background: linear-gradient(90deg,#10b981,#34d399); }
    .stat-card.emerald::after  { background: #10b981; }
    .stat-card.emerald:hover   { box-shadow: 0 12px 28px -8px rgba(16,185,129,.22); }
    .stat-card.rose::before    { background: linear-gradient(90deg,#f43f5e,#fb7185); }
    .stat-card.rose::after     { background: #f43f5e; }
    .stat-card.rose:hover      { box-shadow: 0 12px 28px -8px rgba(244,63,94,.22); }
    .stat-card.amber::before   { background: linear-gradient(90deg,#f59e0b,#fbbf24); }
    .stat-card.amber::after    { background: #f59e0b; }
    .stat-card.amber:hover     { box-shadow: 0 12px 28px -8px rgba(245,158,11,.22); }
    .stat-card.violet::before  { background: linear-gradient(90deg,#8b5cf6,#a78bfa); }
    .stat-card.violet::after   { background: #8b5cf6; }
    .stat-card.violet:hover    { box-shadow: 0 12px 28px -8px rgba(139,92,246,.22); }
    .stat-icon {
        width: 46px; height: 46px; border-radius: 13px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: white; flex-shrink: 0;
    }
    .stat-icon.emerald { background: linear-gradient(135deg,#10b981,#059669); box-shadow: 0 4px 12px rgba(16,185,129,.3); }
    .stat-icon.rose    { background: linear-gradient(135deg,#f43f5e,#e11d48); box-shadow: 0 4px 12px rgba(244,63,94,.3); }
    .stat-icon.amber   { background: linear-gradient(135deg,#f59e0b,#d97706); box-shadow: 0 4px 12px rgba(245,158,11,.3); }
    .stat-icon.violet  { background: linear-gradient(135deg,#8b5cf6,#7c3aed); box-shadow: 0 4px 12px rgba(139,92,246,.3); }

    /* ── Live indicator ───────────────────────────── */
    .live-dot {
        position: relative; display: inline-flex; align-items: center;
        justify-content: center; width: 10px; height: 10px;
    }
    .live-dot::before {
        content: ''; position: absolute; inset: 0; border-radius: 50%;
        background: #10b981; animation: pulse-ring 1.4s ease-out infinite;
    }
    .live-dot::after {
        content: ''; width: 8px; height: 8px; border-radius: 50%; background: #10b981;
    }

    /* ── Filter chips ─────────────────────────────── */
    .filter-chip {
        padding: 6px 18px; border-radius: 20px; font-size: 12px; font-weight: 600;
        cursor: pointer; transition: all .22s ease; border: 1.5px solid #e2e8f0;
        background: white; color: #64748b; white-space: nowrap;
    }
    .filter-chip:hover { border-color: #94a3b8; color: #334155; background: #f8fafc; }
    .filter-chip.active       { background: linear-gradient(135deg,#64748b,#475569); color: white; border-color: transparent; box-shadow: 0 3px 10px -2px rgba(71,85,105,.35); }
    .filter-chip.active-green { background: linear-gradient(135deg,#10b981,#059669); color: white; border-color: transparent; box-shadow: 0 3px 10px -2px rgba(16,185,129,.4); }
    .filter-chip.active-red   { background: linear-gradient(135deg,#f43f5e,#e11d48); color: white; border-color: transparent; box-shadow: 0 3px 10px -2px rgba(244,63,94,.4); }

    /* ── DataTables ───────────────────────────────── */
    #pengembalian-table_wrapper .dataTables_filter { display: none; }
    #pengembalian-table_wrapper .dataTables_length select {
        padding: 6px 28px 6px 12px; border-radius: 8px;
        border: 1px solid #e2e8f0; font-size: 13px; background: #f8fafc;
        outline: none; cursor: pointer;
    }
    #pengembalian-table_wrapper .dataTables_length,
    #pengembalian-table_wrapper .dataTables_info { font-size: 13px; color: #64748b; padding: 10px 0; }
    #pengembalian-table thead th {
        background: linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%);
        font-size: 10.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .06em; color: #64748b; padding: 13px 16px;
        border-bottom: 2px solid #e2e8f0; white-space: nowrap;
    }
    #pengembalian-table tbody td {
        padding: 13px 16px; font-size: 13px;
        vertical-align: middle; border-bottom: 1px solid #f1f5f9;
    }
    #pengembalian-table tbody tr { transition: background .12s ease; }
    #pengembalian-table tbody tr:hover { background: #f0fdf4 !important; }
    #pengembalian-table tbody tr:nth-child(even) { background: #fafafa; }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 6px 13px !important; margin: 0 2px !important;
        border-radius: 9px !important; border: 1px solid #e2e8f0 !important;
        font-size: 13px !important; transition: all .2s !important;
        color: #475569 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg,#10b981,#059669) !important;
        color: white !important; border-color: transparent !important;
        box-shadow: 0 2px 8px rgba(16,185,129,.35) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #1e293b !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        color: #cbd5e1 !important; cursor: default !important;
    }

    /* ── Action Buttons ───────────────────────────── */
    .action-btn {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 9px; font-size: 13px; transition: all .18s ease; color: white;
    }
    .action-btn:hover { transform: translateY(-2px); }
    .action-btn.view   { background: linear-gradient(135deg,#3b82f6,#2563eb); }
    .action-btn.view:hover  { box-shadow: 0 4px 12px rgba(59,130,246,.45); }
    .action-btn.edit   { background: linear-gradient(135deg,#f59e0b,#d97706); }
    .action-btn.edit:hover  { box-shadow: 0 4px 12px rgba(245,158,11,.45); }
    .action-btn.delete { background: linear-gradient(135deg,#f43f5e,#e11d48); }
    .action-btn.delete:hover { box-shadow: 0 4px 12px rgba(244,63,94,.45); }

    /* ── Badges ───────────────────────────────────── */
    .badge-status {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 4px 11px; border-radius: 20px; font-size: 11px;
        font-weight: 600; border: 1px solid; white-space: nowrap;
    }
    .badge-tepat    { background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
    .badge-terlambat { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .badge-dot.green { background: #10b981; }
    .badge-dot.red   { background: #ef4444; animation: pulse-dot 2s infinite; }

    .nomor-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: #ecfdf5; color: #059669;
        padding: 3px 9px; border-radius: 7px; font-size: 11px; font-weight: 600;
        border: 1px solid #a7f3d0; white-space: nowrap;
    }

    /* ── Denda ────────────────────────────────────── */
    .denda-card { display: inline-flex; flex-direction: column; align-items: flex-end; gap: 5px; padding: 7px 11px; border-radius: 11px; min-width: 110px; }
    .denda-card.has-denda { background: linear-gradient(135deg,#fef2f2,#fff1f2); border: 1px solid #fecdd3; }
    .denda-card.paid      { background: linear-gradient(135deg,#ecfdf5,#f0fdf4); border: 1px solid #bbf7d0; }
    .denda-amount { font-size: 13px; font-weight: 700; }
    .denda-amount.red   { color: #dc2626; }
    .denda-amount.green { color: #059669; }
    .denda-status-chip {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 2px 7px; border-radius: 20px; font-size: 10px;
        font-weight: 700; text-transform: uppercase;
    }
    .denda-status-chip.lunas { background: #d1fae5; color: #065f46; }
    .denda-status-chip.belum { background: #fee2e2; color: #991b1b; animation: pulse-belum 2.5s infinite; }
    .denda-badge.no-denda {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 12px; border-radius: 9px; font-size: 12px; font-weight: 600;
        background: linear-gradient(135deg,#f0fdf4,#ecfdf5);
        color: #059669; border: 1px solid #bbf7d0;
    }

    /* ── Modal ────────────────────────────────────── */
    .modal-backdrop { backdrop-filter: blur(4px); background: rgba(15,23,42,.42); }
    .modal-content  { animation: scaleIn .28s ease both; }

    /* ══════════════════════════════════════════════
       DARK MODE OVERRIDES — Pengembalian Index
    ══════════════════════════════════════════════ */
    html[data-theme="dark"] .stat-card {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    html[data-theme="dark"] .stat-card .text-gray-900 { color: #f1f5f9 !important; }
    html[data-theme="dark"] .stat-card .text-gray-500 { color: #64748b !important; }

    /* Date / Live indicator bar */
    html[data-theme="dark"] .flex.items-center.gap-2\.5.px-4.bg-white {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    html[data-theme="dark"] .bg-emerald-50.border.border-emerald-200 {
        background: rgba(16,185,129,0.1) !important;
        border-color: rgba(16,185,129,0.25) !important;
    }

    /* Quick filter chips */
    html[data-theme="dark"] .filter-chip {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    html[data-theme="dark"] .filter-chip:hover {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #cbd5e1 !important;
    }
    /* Filter bar background */
    html[data-theme="dark"] .bg-gray-50\/60 {
        background-color: rgba(15,23,42,0.6) !important;
    }

    /* Table */
    html[data-theme="dark"] #pengembalian-table thead th {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%) !important;
        color: #64748b !important;
        border-color: #334155 !important;
    }
    html[data-theme="dark"] #pengembalian-table tbody tr:nth-child(even) {
        background: rgba(15,23,42,0.4) !important;
    }
    html[data-theme="dark"] #pengembalian-table tbody tr:hover {
        background: rgba(99,102,241,0.07) !important;
    }
    html[data-theme="dark"] #pengembalian-table tbody td {
        border-color: #1e293b !important;
    }
    html[data-theme="dark"] #pengembalian-table_wrapper .dataTables_length select {
        background-color: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    html[data-theme="dark"] #pengembalian-table_wrapper .dataTables_length,
    html[data-theme="dark"] #pengembalian-table_wrapper .dataTables_info {
        color: #64748b !important;
    }
    html[data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button:not(.current) {
        background: transparent !important;
        border-color: #334155 !important;
        color: #64748b !important;
    }
    html[data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #1e293b !important;
        border-color: #475569 !important;
        color: #a5b4fc !important;
    }

    /* Badges & number pill */
    html[data-theme="dark"] .nomor-badge {
        background: rgba(16,185,129,0.15) !important;
        border-color: rgba(16,185,129,0.3) !important;
        color: #34d399 !important;
    }
    html[data-theme="dark"] .badge-tepat {
        background: rgba(16,185,129,0.15) !important;
        border-color: rgba(16,185,129,0.3) !important;
        color: #34d399 !important;
    }
    html[data-theme="dark"] .badge-terlambat {
        background: rgba(239,68,68,0.15) !important;
        border-color: rgba(239,68,68,0.3) !important;
        color: #f87171 !important;
    }

    /* Denda card */
    html[data-theme="dark"] .denda-card.has-denda {
        background: rgba(239,68,68,0.12) !important;
        border-color: rgba(239,68,68,0.25) !important;
    }
    html[data-theme="dark"] .denda-card.paid {
        background: rgba(16,185,129,0.12) !important;
        border-color: rgba(16,185,129,0.25) !important;
    }
    html[data-theme="dark"] .denda-status-chip.belum {
        background: rgba(239,68,68,0.2) !important;
        color: #fca5a5 !important;
    }
    html[data-theme="dark"] .denda-status-chip.lunas {
        background: rgba(16,185,129,0.2) !important;
        color: #6ee7b7 !important;
    }
    html[data-theme="dark"] .denda-badge.no-denda {
        background: rgba(16,185,129,0.12) !important;
        border-color: rgba(16,185,129,0.25) !important;
        color: #34d399 !important;
    }

    /* Filter modal */
    html[data-theme="dark"] .modal-content.bg-white,
    html[data-theme="dark"] .modal-content {
        background: #1e293b !important;
        border-color: #334155 !important;
    }
    html[data-theme="dark"] .modal-content .border-t { border-color: #334155 !important; }
    html[data-theme="dark"] .bg-emerald-50.rounded-xl.border.border-emerald-100 {
        background: rgba(16,185,129,0.08) !important;
        border-color: rgba(16,185,129,0.2) !important;
    }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="space-y-5">

    <!-- Date + Live Banner -->
    <div class="flex items-center justify-between anim-up d1">
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2.5 px-4 py-2.5 bg-white border border-gray-200 rounded-xl shadow-sm">
                <i class="fas fa-calendar-day text-emerald-500 text-sm"></i>
                <span class="text-sm font-semibold text-gray-700">{{ \Carbon\Carbon::today()->translatedFormat('l, d F Y') }}</span>
            </div>
            <div class="flex items-center gap-2 px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl">
                <span class="live-dot"></span>
                <span class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Live</span>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card emerald anim-up d1">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Pengembalian</p>
                    <p class="text-2xl font-bold text-gray-900 leading-none" id="stat-total">
                        <span class="inline-block w-5 h-5 border-2 border-emerald-400 border-t-transparent rounded-full animate-spin"></span>
                    </p>
                    <p class="text-[11px] text-emerald-600 font-medium mt-1.5">Hari ini</p>
                </div>
                <div class="stat-icon emerald"><i class="fas fa-undo-alt"></i></div>
            </div>
        </div>
        <div class="stat-card rose anim-up d2">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Terlambat</p>
                    <p class="text-2xl font-bold text-gray-900 leading-none" id="stat-terlambat">
                        <span class="inline-block w-5 h-5 border-2 border-rose-400 border-t-transparent rounded-full animate-spin"></span>
                    </p>
                    <p class="text-[11px] text-rose-500 font-medium mt-1.5">Hari ini</p>
                </div>
                <div class="stat-icon rose"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="stat-card amber anim-up d3">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Total Denda</p>
                    <p class="text-lg font-bold text-gray-900 leading-none" id="stat-denda">
                        <span class="inline-block w-5 h-5 border-2 border-amber-400 border-t-transparent rounded-full animate-spin"></span>
                    </p>
                    <p class="text-[11px] text-amber-600 font-medium mt-1.5">Hari ini</p>
                </div>
                <div class="stat-icon amber"><i class="fas fa-coins"></i></div>
            </div>
        </div>
        <div class="stat-card violet anim-up d4">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-1 uppercase tracking-wide">Tepat Waktu</p>
                    <p class="text-2xl font-bold text-gray-900 leading-none" id="stat-tepat">
                        <span class="inline-block w-5 h-5 border-2 border-violet-400 border-t-transparent rounded-full animate-spin"></span>
                    </p>
                    <p class="text-[11px] text-violet-500 font-medium mt-1.5">Hari ini</p>
                </div>
                <div class="stat-icon violet"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl shadow-md border border-gray-100 overflow-hidden anim-up d5">

        <!-- Gradient Header -->
        <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-5">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                        <i class="fas fa-undo-alt text-white"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white">Pengembalian Hari Ini</h3>
                        <p class="text-emerald-100 text-xs mt-0.5">Data real-time pengembalian buku</p>
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
                    <!-- Search -->
                    <div class="relative w-full sm:w-auto">
                        <input type="text" id="searchInput" placeholder="Cari nama, nomor pengembalian..."
                               class="w-full sm:w-72 px-4 py-2.5 pl-10 text-sm bg-white/15 backdrop-blur-sm text-white placeholder-emerald-200 rounded-xl border border-white/20 focus:bg-white/25 focus:ring-2 focus:ring-white/30 focus:outline-none transition-all">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-emerald-200 text-sm"></i>
                        </div>
                    </div>
                    <!-- Action Buttons -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="openFilterModal()"
                                class="flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white px-3.5 py-2.5 rounded-xl font-semibold text-xs border border-white/25 transition-all">
                            <i class="fas fa-sliders-h"></i><span>Filter</span>
                        </button>
                        @if(Auth::user()->hasPermission('riwayat-transaksi.view') || Auth::user()->isAdmin())
                        <a href="{{ route('riwayat-pengembalian.index') }}"
                           class="flex items-center gap-1.5 bg-white/15 hover:bg-white/25 text-white px-3.5 py-2.5 rounded-xl font-semibold text-xs border border-white/25 transition-all">
                            <i class="fas fa-history"></i><span>Riwayat</span>
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('pengembalian.create') || Auth::user()->isAdmin())
                        <a href="{{ route('pengembalian.create') }}"
                           class="flex items-center gap-1.5 bg-white hover:bg-emerald-50 text-emerald-700 px-3.5 py-2.5 rounded-xl font-semibold text-xs transition-all shadow-md">
                            <i class="fas fa-plus"></i><span>Tambah</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Filter Bar -->
        <div class="px-6 py-3 bg-gray-50/60 border-b border-gray-100 flex items-center gap-2 overflow-x-auto">
            <span class="text-[11px] text-gray-400 font-semibold uppercase tracking-wider mr-1 whitespace-nowrap">Filter:</span>
            <button class="filter-chip active" onclick="setQuickFilter('all', this)" data-filter="all">
                <i class="fas fa-layer-group mr-1 text-[10px]"></i>Semua
            </button>
            <button class="filter-chip" onclick="setQuickFilter('tepat_waktu', this)" data-filter="tepat_waktu">
                <i class="fas fa-check-circle mr-1 text-[10px]"></i>Tepat Waktu
            </button>
            <button class="filter-chip" onclick="setQuickFilter('terlambat', this)" data-filter="terlambat">
                <i class="fas fa-exclamation-circle mr-1 text-[10px]"></i>Terlambat
            </button>
        </div>

        <!-- Table -->
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="pengembalian-table" class="min-w-full" style="min-width:860px;">
                    <thead>
                        <tr>
                            <th class="text-center" style="width:48px;">No</th>
                            <!-- <th class="text-left">No. Pengembalian</th> -->
                            <th class="text-left">Anggota</th>
                            <th class="text-center">Buku</th>
                            <th class="text-left">Tanggal Kembali</th>
                            <th class="text-center">Status</th>
                            <th class="text-left">Denda</th>
                            <!-- <th class="text-left">Petugas</th> -->
                            @if(Auth::user()->hasPermission('pengembalian.show') || Auth::user()->isAdmin() || Auth::user()->hasPermission('pengembalian.edit') || Auth::user()->isAdmin() || Auth::user()->hasPermission('pengembalian.delete') || Auth::user()->isAdmin())
                            <th class="text-center" style="width:50px;">Aksi</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop absolute inset-0" onclick="closeFilterModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4 relative z-10">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-sliders-h text-white"></i>
                        </div>
                        <h3 class="text-base font-bold text-white">Filter Pengembalian</h3>
                    </div>
                    <button onclick="closeFilterModal()" class="w-8 h-8 bg-white/15 hover:bg-white/25 rounded-lg flex items-center justify-center text-white transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <form id="filterForm" class="p-6 space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-flag mr-1 text-emerald-500"></i>Status Pengembalian
                    </label>
                    <select id="filter_status" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                        <option value="">Semua Status</option>
                        <option value="tepat_waktu">Tepat Waktu</option>
                        <option value="terlambat">Terlambat</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">
                        <i class="fas fa-coins mr-1 text-amber-500"></i>Status Denda
                    </label>
                    <select id="filter_status_denda" class="w-full px-4 py-3 border border-gray-200 rounded-xl text-sm bg-gray-50 focus:ring-2 focus:ring-emerald-400 focus:border-transparent transition-all">
                        <option value="">Semua Status Denda</option>
                        <option value="tidak_ada">Tidak Ada Denda</option>
                        <option value="belum_dibayar">Belum Dibayar</option>
                        <option value="sudah_dibayar">Sudah Dibayar</option>
                    </select>
                </div>
                <div class="flex items-center gap-2 p-3 bg-emerald-50 rounded-xl border border-emerald-100">
                    <i class="fas fa-info-circle text-emerald-500 text-sm flex-shrink-0"></i>
                    <p class="text-xs text-emerald-700">Halaman ini hanya menampilkan data pengembalian <strong>hari ini</strong>. Lihat <strong>Riwayat</strong> untuk data lengkap.</p>
                </div>
                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" onclick="resetFilters()" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-all">
                        <i class="fas fa-undo mr-1.5"></i>Reset
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-sm font-semibold shadow-md transition-all">
                        <i class="fas fa-check mr-1.5"></i>Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
let pengembalianTable;
let currentQuickFilter = 'all';

updateSummaryCards(@json($summaryData));

$(document).ready(function () {
    const hasAction = {{ (Auth::user()->hasPermission('pengembalian.show') || Auth::user()->isAdmin() || Auth::user()->hasPermission('pengembalian.edit') || Auth::user()->isAdmin() || Auth::user()->hasPermission('pengembalian.delete') || Auth::user()->isAdmin()) ? 'true' : 'false' }};

    let columns = [
        { data: 'DT_RowIndex',  name: 'DT_RowIndex',  orderable: false, searchable: false, className: 'text-left' },
        // { data: 'nomor_badge',  name: 'nomor_pengembalian', orderable: false, searchable: false },
        { data: 'anggota_info', name: 'anggota_id',   orderable: false, searchable: false },
        { data: 'jumlah_badge', name: 'jumlah_badge', orderable: false, searchable: false, className: 'text-left ' },
        { data: 'tanggal_info', name: 'tanggal_pengembalian', orderable: false, searchable: false },
        { data: 'status_badge', name: 'status',       orderable: false, searchable: false, className: 'text-left' },
        { data: 'denda_info',   name: 'total_denda',  orderable: false, searchable: false, className: 'text-left' },
        // { data: 'petugas_info', name: 'user_id',      orderable: false, searchable: false },
    ];
    if (hasAction) {
        columns.push({ data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-left' });
    }

    pengembalianTable = $('#pengembalian-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url : '{{ route("pengembalian.data") }}',
            type: 'GET',
            data: function (d) {
                d.filter_status       = currentQuickFilter !== 'all' ? currentQuickFilter : ($('#filter_status').val() || '');
                d.filter_status_denda = $('#filter_status_denda').val();
                d.search_keyword      = $('#searchInput').val() || '';
            },
            dataSrc: function (json) {
                if (json && json.summary) updateSummaryCards(json.summary);
                return json.data || [];
            },
            error: function (xhr) {
                console.error('DataTables error', xhr.status, xhr.responseText?.substring(0, 300));
            }
        },
        columns: columns,
        language: {
            processing  : '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-[3px] border-emerald-200 border-t-emerald-500 mr-3"></div><span class="text-gray-500 text-sm">Memuat data...</span></div>',
            lengthMenu  : 'Tampilkan _MENU_ data',
            zeroRecords : '<div class="text-center py-14"><div class="mx-auto w-16 h-16 bg-emerald-50 rounded-2xl flex items-center justify-center mb-4"><i class="fas fa-undo-alt text-2xl text-emerald-300"></i></div><p class="text-sm font-semibold text-gray-700 mb-1">Belum ada pengembalian hari ini</p><p class="text-xs text-gray-400">Coba ubah filter atau cari data lain</p></div>',
            info        : 'Menampilkan _START_–_END_ dari _TOTAL_ data',
            infoEmpty   : 'Tidak ada data',
            infoFiltered: '(filter dari _MAX_ total)',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [],
        drawCallback: function () {
            $('#pengembalian-table tbody tr').each(function (i) {
                $(this).css({ animation: 'fadeInUp .3s ease both', animationDelay: (i * 0.025) + 's' });
            });
        }
    });

    let searchTimeout;
    $('#searchInput').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => pengembalianTable.draw(), 380);
    });
});

function updateSummaryCards(s) {
    animateCount('stat-total',    s.total    || 0);
    animateCount('stat-terlambat',s.terlambat|| 0);
    animateCount('stat-tepat',    s.tepat_waktu || 0);
    const el = document.getElementById('stat-denda');
    if (el) el.innerHTML = 'Rp&nbsp;' + new Intl.NumberFormat('id-ID').format(s.total_denda || 0);
}

function animateCount(id, target) {
    const el = document.getElementById(id);
    if (!el) return;
    const dur = 550, start = performance.now();
    (function tick(now) {
        const p = Math.min((now - start) / dur, 1);
        const e = 1 - Math.pow(1 - p, 3);
        el.textContent = Math.round(target * e).toLocaleString('id-ID');
        if (p < 1) requestAnimationFrame(tick);
    })(start);
}

function setQuickFilter(filter, btn) {
    currentQuickFilter = filter;
    document.querySelectorAll('.filter-chip').forEach(c => c.className = 'filter-chip');
    btn.classList.add(filter === 'all' ? 'active' : filter === 'tepat_waktu' ? 'active-green' : 'active-red');
    $('#filter_status').val('');
    pengembalianTable.draw();
}

function openFilterModal()  { document.getElementById('filterModal').classList.remove('hidden'); document.body.style.overflow = 'hidden'; }
function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); document.body.style.overflow = ''; }

function resetFilters() {
    $('#filter_status, #filter_status_denda').val('');
    currentQuickFilter = 'all';
    document.querySelectorAll('.filter-chip').forEach(c => c.className = 'filter-chip');
    document.querySelector('[data-filter="all"]').classList.add('active');
    pengembalianTable.draw();
    closeFilterModal();
}

document.getElementById('filterForm').addEventListener('submit', function (e) {
    e.preventDefault();
    currentQuickFilter = 'all';
    document.querySelectorAll('.filter-chip').forEach(c => c.className = 'filter-chip');
    document.querySelector('[data-filter="all"]').classList.add('active');
    pengembalianTable.draw();
    closeFilterModal();
});

function confirmDelete(id) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus Pengembalian?', text: 'Data tidak dapat dikembalikan.',
            icon: 'warning', showCancelButton: true,
            confirmButtonColor: '#ef4444', cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash mr-1"></i>Ya, Hapus',
            cancelButtonText: 'Batal', reverseButtons: true
        }).then(r => { if (r.isConfirmed) doDelete(id); });
    } else {
        if (confirm('Hapus data pengembalian ini?')) doDelete(id);
    }
}

function doDelete(id) {
    const f = document.createElement('form');
    f.method = 'POST'; f.action = `/admin/pengembalian/${id}`;
    f.innerHTML = `<input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
                   <input type="hidden" name="_method" value="DELETE">`;
    document.body.appendChild(f); f.submit();
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeFilterModal(); });
</script>
@endsection
