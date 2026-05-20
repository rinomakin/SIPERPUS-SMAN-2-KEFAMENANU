@extends('layouts.admin')

@section('title', 'Manajemen Denda')

@push('styles')
<style>
/* ===== Dark Mode Overrides: Denda Index ===== */

/* Stat Cards */
[data-theme="dark"] .stat-card {
    background: #1e293b !important;
    border-color: rgba(255,255,255,0.06) !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.35) !important;
}
[data-theme="dark"] .stat-card:hover {
    box-shadow: 0 8px 24px rgba(0,0,0,0.5) !important;
}
[data-theme="dark"] .stat-card .stat-label { color: #475569 !important; }
[data-theme="dark"] .stat-card .stat-value.text-gray-900 { color: #f1f5f9 !important; }
/* Stat icon containers — replace pastel with dark tinted backgrounds */
[data-theme="dark"] .stat-card .stat-icon {
    background: rgba(255,255,255,0.07) !important;
    filter: none !important;
}

/* Glass Card */
[data-theme="dark"] .glass-card {
    background: #1e293b !important;
    border-color: rgba(255,255,255,0.06) !important;
}
[data-theme="dark"] .glass-card .border-b { border-color: #334155 !important; }

/* Glass card header texts */
[data-theme="dark"] .glass-card h2.text-gray-900   { color: #f1f5f9 !important; }
[data-theme="dark"] .glass-card .text-gray-400     { color: #64748b !important; }

/* Filter button (inactive state) */
[data-theme="dark"] #openFilterModal.bg-gray-50 {
    background: #334155 !important;
    border-color: #475569 !important;
    color: #94a3b8 !important;
}
[data-theme="dark"] #openFilterModal.bg-gray-50:hover {
    background: rgba(239,68,68,0.15) !important;
    border-color: rgba(239,68,68,0.4) !important;
    color: #fca5a5 !important;
}
/* Scan button */
[data-theme="dark"] #scanBarcodeBtn {
    background: rgba(99,102,241,0.12) !important;
    border-color: rgba(99,102,241,0.25) !important;
    color: #a5b4fc !important;
}
[data-theme="dark"] #scanBarcodeBtn:hover {
    background: rgba(99,102,241,0.2) !important;
}

/* Active filter chips bar */
[data-theme="dark"] .glass-card .bg-amber-50\/60 { background-color: rgba(245,158,11,0.08) !important; }
[data-theme="dark"] .glass-card .border-amber-100 { border-color: rgba(245,158,11,0.2) !important; }
[data-theme="dark"] .glass-card .text-amber-600   { color: #fcd34d !important; }
[data-theme="dark"] .glass-card .bg-red-50        { background-color: rgba(239,68,68,0.10) !important; }
[data-theme="dark"] .glass-card .border-red-100   { border-color: rgba(239,68,68,0.2) !important; }
[data-theme="dark"] .glass-card .text-red-700     { color: #fca5a5 !important; }
[data-theme="dark"] .glass-card .bg-blue-50       { background-color: rgba(59,130,246,0.10) !important; }
[data-theme="dark"] .glass-card .border-blue-100  { border-color: rgba(59,130,246,0.2) !important; }
[data-theme="dark"] .glass-card .text-blue-700    { color: #93c5fd !important; }
[data-theme="dark"] .glass-card .bg-purple-50     { background-color: rgba(139,92,246,0.10) !important; }
[data-theme="dark"] .glass-card .border-purple-100{ border-color: rgba(139,92,246,0.2) !important; }
[data-theme="dark"] .glass-card .text-purple-700  { color: #c4b5fd !important; }
[data-theme="dark"] .glass-card .bg-green-50      { background-color: rgba(16,185,129,0.10) !important; }
[data-theme="dark"] .glass-card .border-green-100 { border-color: rgba(16,185,129,0.2) !important; }
[data-theme="dark"] .glass-card .text-green-700   { color: #6ee7b7 !important; }

/* DataTables length bar */
[data-theme="dark"] #denda-table_wrapper .dataTables_length {
    border-bottom-color: #334155 !important;
    color: #64748b !important;
    background: #1e293b !important;
}
[data-theme="dark"] #denda-table_wrapper .dataTables_length label { color: #64748b !important; }
[data-theme="dark"] #denda-table_wrapper .dataTables_length select {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
[data-theme="dark"] #denda-table_wrapper .dataTables_info { color: #64748b !important; }

/* Table header */
[data-theme="dark"] .denda-table thead th {
    background: #0f172a !important;
    color: #64748b !important;
    border-bottom-color: #334155 !important;
}

/* Table rows */
[data-theme="dark"] .denda-table tbody tr { background: #1e293b; }
[data-theme="dark"] .denda-table tbody td {
    border-bottom-color: #334155 !important;
    color: #e2e8f0;
}
[data-theme="dark"] .denda-table tbody tr:hover { background: #243047 !important; }

/* Table cell text colors */
[data-theme="dark"] .denda-table .text-gray-900 { color: #f1f5f9 !important; }
[data-theme="dark"] .denda-table .text-gray-400  { color: #64748b !important; }

/* Avatar border */
[data-theme="dark"] .avatar-img { border-color: #334155 !important; }

/* Action button */
[data-theme="dark"] .action-btn.view {
    background: rgba(59,130,246,0.12) !important;
    border-color: rgba(59,130,246,0.25) !important;
    color: #93c5fd !important;
}

/* Pagination */
[data-theme="dark"] #denda-table_wrapper .dataTables_paginate .paginate_button {
    color: #64748b !important;
    background: transparent !important;
}
[data-theme="dark"] #denda-table_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
    background: rgba(239,68,68,0.12) !important;
    color: #fca5a5 !important;
    border-color: rgba(239,68,68,0.3) !important;
}
[data-theme="dark"] #denda-table_wrapper .dataTables_paginate .paginate_button.disabled {
    color: #334155 !important;
}

/* Bottom bar */
[data-theme="dark"] .dt-bottom-bar { border-top-color: #334155 !important; }

/* Filter Modal */
[data-theme="dark"] .modal-box {
    background: #1e293b !important;
    border-color: #334155 !important;
}
[data-theme="dark"] .modal-box .px-5.py-4.bg-gray-50 {
    background: #0f172a !important;
}
[data-theme="dark"] .modal-box .text-gray-900 { color: #f1f5f9 !important; }
[data-theme="dark"] .modal-box .text-gray-700 { color: #e2e8f0 !important; }
[data-theme="dark"] .modal-box .text-gray-500 { color: #94a3b8 !important; }
[data-theme="dark"] .modal-box .border-b,
[data-theme="dark"] .modal-box .border-t     { border-color: #334155 !important; }
[data-theme="dark"] .filter-label   { color: #94a3b8 !important; }
[data-theme="dark"] .filter-input {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
[data-theme="dark"] .filter-input:focus {
    border-color: #ef4444 !important;
    background-color: #0f172a !important;
}
[data-theme="dark"] .filter-select {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
[data-theme="dark"] .modal-box button.bg-gray-100 {
    background: #334155 !important;
    color: #e2e8f0 !important;
}
[data-theme="dark"] .modal-box button.bg-gray-100:hover {
    background: #475569 !important;
}
</style>
@endpush

@section('content')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<!-- html5-qrcode -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
/* ===== Stat Cards ===== */
.stat-card {
    background: white; border-radius: 16px; padding: 20px; position: relative; overflow: hidden;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
.stat-card .stat-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.stat-card .stat-bg {
    position: absolute; top: -20px; right: -20px; width: 90px; height: 90px;
    border-radius: 50%; opacity: 0.06;
}
.stat-card .stat-value { font-size: 22px; font-weight: 800; line-height: 1.2; }
.stat-card .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }

/* ===== Glass Card ===== */
.glass-card {
    background: rgba(255,255,255,0.97); backdrop-filter: blur(20px);
    border-radius: 16px; border: 1px solid rgba(255,255,255,0.8);
    box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden;
}

/* ===== Table ===== */
.denda-table { border-collapse: separate; border-spacing: 0; width: 100%; }
.denda-table thead th {
    background: #f8fafc; font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: #94a3b8; padding: 12px 16px; border-bottom: 2px solid #f1f5f9;
    white-space: nowrap;
}
.denda-table thead th.sorting:after,
.denda-table thead th.sorting_asc:after,
.denda-table thead th.sorting_desc:after { color: #94a3b8 !important; }
.denda-table tbody tr { transition: all 0.15s ease; }
.denda-table tbody tr:hover { background: #fefce8; }
.denda-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }

/* ===== Avatar ===== */
.avatar-circle {
    width: 40px; height: 40px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: white; flex-shrink: 0;
    background: linear-gradient(135deg, #f97316, #ef4444);
}
.avatar-img {
    width: 40px; height: 40px; border-radius: 12px; object-fit: cover;
    flex-shrink: 0; border: 2px solid #f1f5f9;
}

/* ===== Badges ===== */
.badge-late {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626;
    border: 1px solid #fecaca;
}
.badge-amount { font-size: 13px; font-weight: 700; color: #dc2626; }

/* ===== Action Buttons ===== */
.action-btn {
    width: 32px; height: 32px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; transition: all 0.2s; border: 1px solid transparent;
}
.action-btn.view { color: #3b82f6; background: #eff6ff; border-color: #dbeafe; }
.action-btn.view:hover { background: #3b82f6; color: white; }
.pay-btn {
    display: inline-flex; align-items: center; gap: 4px; padding: 5px 14px;
    border-radius: 8px; font-size: 11px; font-weight: 700;
    background: linear-gradient(135deg, #10b981, #059669); color: white;
    border: none; cursor: pointer; transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(16,185,129,0.3);
}
.pay-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(16,185,129,0.4); }

/* ===== Filter Chip ===== */
.filter-chip {
    display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 600; transition: all 0.2s;
}

/* ===== Empty State ===== */
.empty-state { text-align: center; padding: 48px 24px; }
.empty-state .empty-icon {
    width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 16px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
}

/* ===== Animations ===== */
@keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
.anim-up { animation: fadeInUp 0.4s ease forwards; }
.anim-d1 { animation-delay: 0.05s; opacity: 0; }
.anim-d2 { animation-delay: 0.10s; opacity: 0; }
.anim-d3 { animation-delay: 0.15s; opacity: 0; }
.anim-d4 { animation-delay: 0.20s; opacity: 0; }

/* ===== DataTables Override ===== */
#denda-table_wrapper .dataTables_filter { display: none !important; }
#denda-table_wrapper { overflow: visible !important; }
#denda-table_wrapper .dataTables_scrollBody { overflow-x: auto; }

#denda-table_wrapper .dataTables_length {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 8px;
}
#denda-table_wrapper .dataTables_length label { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280; }
#denda-table_wrapper .dataTables_length select {
    padding: 5px 28px 5px 10px; border-radius: 8px; border: 1px solid #e5e7eb;
    background: #f9fafb; font-size: 12px; cursor: pointer; outline: none;
    -webkit-appearance: none; -moz-appearance: none; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 8px center;
    transition: all 0.2s;
}
#denda-table_wrapper .dataTables_length select:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }

#denda-table_wrapper .dataTables_info {
    padding: 14px 20px; font-size: 12px; color: #9ca3af;
}

#denda-table_wrapper .dataTables_paginate {
    padding: 10px 20px;
}
#denda-table_wrapper .dataTables_paginate .paginate_button {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 8px;
    border-radius: 8px; font-size: 12px; font-weight: 600;
    color: #6b7280 !important; border: 1px solid transparent !important;
    background: transparent !important; cursor: pointer; transition: all 0.2s; margin: 0 2px;
}
#denda-table_wrapper .dataTables_paginate .paginate_button:hover {
    background: #fef2f2 !important; color: #ef4444 !important; border-color: #fecaca !important;
}
#denda-table_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: white !important; border-color: transparent !important;
    box-shadow: 0 2px 8px rgba(239,68,68,0.35) !important;
}
#denda-table_wrapper .dataTables_paginate .paginate_button.disabled,
#denda-table_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    color: #d1d5db !important; background: transparent !important; cursor: default;
}
.dt-bottom-bar {
    display: flex; align-items: center; justify-content: space-between;
    border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 8px;
}

/* ===== Modal ===== */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px); z-index: 100; display: none;
    align-items: center; justify-content: center; padding: 16px;
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: white; border-radius: 20px; width: 100%; max-width: 440px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2); overflow: hidden;
    animation: fadeInUp 0.25s ease;
}
.modal-header {
    padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;
}
.modal-close {
    width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.2);
    border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: white; font-size: 14px; transition: background 0.2s;
}
.modal-close:hover { background: rgba(255,255,255,0.35); }

.filter-input {
    width: 100%; padding: 9px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; outline: none; transition: all 0.2s; background: #fafafa;
}
.filter-input:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); background: white; }
.filter-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 6px; display: block; }
.filter-select {
    width: 100%; padding: 9px 32px 9px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; outline: none; transition: all 0.2s; background: #fafafa;
    -webkit-appearance: none; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
}
.filter-select:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); background: white; }

/* ===== Dark Scanner Modal ===== */
@keyframes scan-line { 0%{top:5%;opacity:0;} 10%{opacity:1;} 90%{opacity:1;} 100%{top:95%;opacity:0;} }
@keyframes denda-spin { to { transform: rotate(360deg); } }
.animate-scan-line { animation: scan-line 2s ease-in-out infinite; position:absolute; }
.denda-spinner { animation: denda-spin 0.8s linear infinite; border-radius:50%; }
.scanner-modal-backdrop {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.85); backdrop-filter: blur(4px);
    display: flex; align-items: center; justify-content: center; padding: 16px;
}
.scanner-modal-backdrop.hidden { display: none; }
.scanner-pinjam-box {
    background: #111827; border-radius: 20px; width: 100%; max-width: 420px;
    overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    display: flex; flex-direction: column;
}
.scanner-pinjam-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; background: rgba(0,0,0,0.4);
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.scanner-pinjam-video {
    position: relative; width: 100%; aspect-ratio: 4/3; background: #000; overflow: hidden;
}
.scanner-pinjam-footer {
    padding: 14px 16px; background: rgba(0,0,0,0.4);
    border-top: 1px solid rgba(255,255,255,0.08);
}
</style>

<div class="max-w-7xl mx-auto">

    {{-- ===== Statistics Cards ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card anim-up anim-d1">
            <div class="stat-bg" style="background:#ef4444;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#ef4444;">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Total Denda</p>
            <p class="stat-value text-gray-900">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d2">
            <div class="stat-bg" style="background:#f59e0b;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);color:#f59e0b;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Belum Dibayar</p>
            <p class="stat-value text-amber-600">Rp {{ number_format($dendaBelumDibayar, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d3">
            <div class="stat-bg" style="background:#10b981;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);color:#10b981;">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Sudah Dibayar</p>
            <p class="stat-value text-emerald-600">Rp {{ number_format($dendaSudahDibayar, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d4">
            <div class="stat-bg" style="background:#6366f1;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#6366f1;">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Denda Hari Ini</p>
            <p class="stat-value text-indigo-600">Rp {{ number_format($totalDendaHariIni, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ===== Main Card ===== --}}
    <div class="glass-card anim-up" style="animation-delay:0.25s;opacity:0;">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3 mb-3 sm:mb-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white">
                    <i class="fas fa-list text-xs"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Daftar Denda Belum Dibayar</h2>
                    <p class="text-[11px] text-gray-400">{{ $jumlahBelumBayar }} anggota memiliki denda aktif</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Filter Button --}}
                @php
                    $activeFilterCount = collect([request('search'), request('kelas_id'), request('jurusan_id'), request('jenis_anggota')])->filter()->count();
                @endphp
                <button type="button" id="openFilterModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all border
                               {{ $activeFilterCount > 0 ? 'bg-red-500 text-white border-red-500 shadow-sm' : 'bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 border-gray-200 hover:border-red-200' }}">
                    <i class="fas fa-sliders-h"></i>
                    <span>Filter</span>
                    @if($activeFilterCount > 0)
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-white text-red-600 text-[10px] font-extrabold">{{ $activeFilterCount }}</span>
                    @endif
                </button>
                {{-- Scan (semua role) --}}
                <button type="button" id="scanBarcodeBtn"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition-all border border-indigo-100">
                    <i class="fas fa-barcode"></i>
                    <span class="hidden sm:inline">Scan</span>
                </button>
                {{-- Riwayat --}}
                <a href="{{ route('admin.denda.riwayat') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                    <i class="fas fa-history"></i>
                    <span class="hidden sm:inline">Riwayat</span>
                </a>
            </div>
        </div>

        {{-- Active Filter Chips --}}
        @if($activeFilterCount > 0)
        <div class="px-5 py-2.5 bg-amber-50/60 border-b border-amber-100 flex items-center gap-2 flex-wrap">
            <span class="text-[10px] text-amber-600 font-bold uppercase tracking-wider flex items-center gap-1">
                <i class="fas fa-filter"></i> Filter Aktif:
            </span>
            @if(request('search'))
                <span class="filter-chip bg-red-50 text-red-700 border border-red-100">
                    <i class="fas fa-search text-[9px]"></i> "{{ request('search') }}"
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('search'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('kelas_id'))
                @php $sk = $kelasList->firstWhere('id', request('kelas_id')); @endphp
                <span class="filter-chip bg-blue-50 text-blue-700 border border-blue-100">
                    <i class="fas fa-layer-group text-[9px]"></i> {{ $sk ? $sk->nama_kelas : '-' }}
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('kelas_id'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('jurusan_id'))
                @php $sj = $jurusanList->firstWhere('id', request('jurusan_id')); @endphp
                <span class="filter-chip bg-purple-50 text-purple-700 border border-purple-100">
                    <i class="fas fa-graduation-cap text-[9px]"></i> {{ $sj ? $sj->nama_jurusan : '-' }}
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('jurusan_id'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('jenis_anggota'))
                <span class="filter-chip bg-green-50 text-green-700 border border-green-100">
                    <i class="fas fa-user-tag text-[9px]"></i> {{ ucfirst(request('jenis_anggota')) }}
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('jenis_anggota'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            <a href="{{ route('admin.denda.index') }}"
               class="ml-auto text-[11px] text-gray-400 hover:text-red-500 font-semibold transition-colors">
                <i class="fas fa-times mr-0.5"></i>Hapus semua
            </a>
        </div>
        @endif

        {{-- DataTables Table --}}
        <div>
            <table id="denda-table" class="denda-table">
                <thead>
                    <tr>
                        <th class="text-left">Anggota</th>
                        <th class="text-left">Peminjaman</th>
                        <th class="text-center">Terlambat</th>
                        <th class="text-right">Jumlah Denda</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center" style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($denda as $item)
                    <tr>
                        {{-- Anggota --}}
                        <td data-order="{{ $item->anggota->nama_lengkap ?? '' }}">
                            <div class="flex items-center gap-3">
                                @if($item->anggota && $item->anggota->foto)
                                    <img class="avatar-img"
                                         src="{{ asset('storage/anggota/' . $item->anggota->foto) }}"
                                         alt="{{ $item->anggota->nama_lengkap }}"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="avatar-circle" style="display:none;background:linear-gradient(135deg,{{ ['#f97316,#ef4444','#8b5cf6,#6366f1','#10b981,#059669','#3b82f6,#2563eb','#ec4899,#db2777'][($item->anggota->id ?? 0) % 5] }});">
                                        {{ strtoupper(substr($item->anggota->nama_lengkap ?? 'N', 0, 1)) }}
                                    </div>
                                @elseif($item->anggota)
                                    <div class="avatar-circle" style="background:linear-gradient(135deg,{{ ['#f97316,#ef4444','#8b5cf6,#6366f1','#10b981,#059669','#3b82f6,#2563eb','#ec4899,#db2777'][($item->anggota->id ?? 0) % 5] }});">
                                        {{ strtoupper(substr($item->anggota->nama_lengkap ?? 'N', 0, 1)) }}
                                    </div>
                                @else
                                    <div class="avatar-circle" style="background:linear-gradient(135deg,#94a3b8,#64748b);">
                                        <i class="fas fa-user text-xs"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $item->anggota->nama_lengkap ?? 'N/A' }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $item->anggota->nomor_anggota ?? 'N/A' }}</p>
                                    @if($item->anggota && $item->anggota->kelas)
                                        <p class="text-[10px] text-gray-400">{{ $item->anggota->kelas->nama_kelas }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Peminjaman --}}
                        <td data-order="{{ $item->peminjaman ? ($item->peminjaman->tanggal_peminjaman ?? '') : '' }}">
                            @if($item->peminjaman)
                                <p class="text-xs font-semibold text-gray-800">{{ $item->peminjaman->nomor_peminjaman ?? ('ID: ' . $item->peminjaman_id) }}</p>
                                <p class="text-[10px] text-gray-400">
                                    <i class="far fa-calendar-alt mr-0.5"></i>{{ $item->peminjaman->tanggal_peminjaman ? $item->peminjaman->tanggal_peminjaman->format('d M Y') : '-' }}
                                </p>
                            @else
                                <span class="text-xs text-gray-300">N/A</span>
                            @endif
                        </td>

                        {{-- Hari Terlambat --}}
                        <td class="text-center" data-order="{{ $item->jumlah_hari_terlambat }}">
                            <span class="badge-late">
                                <i class="fas fa-clock text-[9px]"></i>
                                {{ $item->jumlah_hari_terlambat }} hari
                            </span>
                        </td>

                        {{-- Jumlah Denda --}}
                        <td class="text-right" data-order="{{ $item->jumlah_denda }}">
                            <span class="badge-amount">Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}</span>
                        </td>

                        {{-- Tanggal --}}
                        <td class="text-center" data-order="{{ $item->created_at->format('Y-m-d H:i:s') }}">
                            <p class="text-xs text-gray-600">{{ $item->created_at->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400">{{ $item->created_at->format('H:i') }}</p>
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.denda.show', $item->id) }}"
                                   class="action-btn view" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(Auth::user()->isAdmin() || Auth::user()->isPetugas())
                                <form action="{{ route('admin.denda.bayar-lunas', $item->id) }}"
                                      method="POST" class="inline bayar-form"
                                      data-nama="{{ $item->anggota ? $item->anggota->nama_lengkap : 'N/A' }}"
                                      data-denda="Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}">
                                    @csrf
                                    <button type="submit" class="pay-btn" title="Bayar Lunas">
                                        <i class="fas fa-check"></i>Bayar
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>{{-- end glass-card --}}

</div>{{-- end max-w-7xl --}}


{{-- ===== Filter Modal ===== --}}
<div id="filterModal" class="modal-overlay">
    <div class="modal-box">
        {{-- Modal Header --}}
        <div class="modal-header bg-gradient-to-r from-red-500 to-rose-600">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fas fa-sliders-h text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Filter & Pencarian</h3>
                    <p class="text-[10px] text-white/70">Saring data denda sesuai kebutuhan</p>
                </div>
            </div>
            <button type="button" class="modal-close" id="closeFilterModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <form action="{{ route('admin.denda.index') }}" method="GET" id="filterForm">
            <div class="p-5 space-y-4">

                {{-- Search --}}
                <div>
                    <label class="filter-label">Cari Anggota</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" id="filterSearch"
                               value="{{ request('search') }}"
                               placeholder="Nama, nomor anggota, atau barcode..."
                               class="filter-input pl-9">
                    </div>
                </div>

                {{-- Kelas --}}
                <div>
                    <label class="filter-label">Kelas</label>
                    <select name="kelas_id" class="filter-select">
                        <option value="">— Semua Kelas —</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jurusan --}}
                <div>
                    <label class="filter-label">Jurusan</label>
                    <select name="jurusan_id" class="filter-select">
                        <option value="">— Semua Jurusan —</option>
                        @foreach($jurusanList as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis Anggota --}}
                <div>
                    <label class="filter-label">Jenis Anggota</label>
                    <select name="jenis_anggota" class="filter-select">
                        <option value="">— Semua Jenis —</option>
                        <option value="siswa" {{ request('jenis_anggota') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="guru"  {{ request('jenis_anggota') == 'guru'  ? 'selected' : '' }}>Guru</option>
                        <option value="staff" {{ request('jenis_anggota') == 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex gap-2.5">
                <a href="{{ route('admin.denda.index') }}"
                   class="flex-1 py-2.5 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl text-xs font-semibold text-center transition-all">
                    <i class="fas fa-redo mr-1"></i>Reset Filter
                </a>
                <button type="submit"
                        class="flex-1 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                    <i class="fas fa-search mr-1"></i>Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===== Dark Scanner Modal ===== --}}
<div id="scannerModal" class="scanner-modal-backdrop hidden">
    <div class="scanner-pinjam-box">
        {{-- Header --}}
        <div class="scanner-pinjam-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-barcode text-white text-xs"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white leading-none">Scan Barcode Anggota</h3>
                    <p class="text-[10px] text-white/60 mt-0.5">Arahkan kamera ke barcode anggota</p>
                </div>
            </div>
            <button type="button" id="closeScanner"
                    class="w-8 h-8 bg-white/10 hover:bg-red-500/80 rounded-full flex items-center justify-center text-white transition-colors flex-shrink-0">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        {{-- Video area --}}
        <div id="scannerContainer" class="scanner-pinjam-video">
            <div id="scannerPlaceholder" class="absolute inset-0 flex items-center justify-center z-10 px-4">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-camera text-2xl text-white/60"></i>
                    </div>
                    <p class="text-sm text-white/70 mb-1">Kamera akan aktif otomatis</p>
                    <p class="text-xs text-white/40">Pastikan izin kamera diaktifkan</p>
                </div>
            </div>
            <div id="scannerVideo" class="w-full h-full hidden">
                <div id="reader" class="w-full h-full"></div>
            </div>
            <div id="scannerLoading" class="absolute inset-0 bg-black/60 flex items-center justify-center hidden z-10">
                <div class="text-center text-white">
                    <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full denda-spinner mx-auto mb-3"></div>
                    <p class="text-xs font-medium">Memulai kamera...</p>
                </div>
            </div>
            <div id="scanOverlay" class="absolute inset-0 z-10 pointer-events-none hidden">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative" style="width:78%;max-width:320px;aspect-ratio:4/3;">
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-emerald-400 rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-emerald-400 rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-emerald-400 rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-emerald-400 rounded-br-lg"></div>
                        <div class="absolute left-2 right-2 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent animate-scan-line"></div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Footer --}}
        <div class="scanner-pinjam-footer">
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gray-500" id="scannerStatusDot"></span>
                    <span class="text-[11px] text-white/70" id="scannerStatus">Siap untuk scan</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="toggleTorchBtn"
                            class="w-8 h-8 bg-white/10 hover:bg-amber-500/60 rounded-lg flex items-center justify-center text-white/80 transition-colors hidden">
                        <i class="fas fa-bolt text-xs"></i>
                    </button>
                    <button type="button" id="switchCameraBtn"
                            class="w-8 h-8 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-center text-white/80 transition-colors hidden">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </button>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button" id="cancelScan"
                        class="flex-1 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold text-xs transition-colors">
                    <i class="fas fa-arrow-left mr-1.5"></i>Kembali
                </button>
                <button type="button" id="startScanBtn"
                        class="flex-1 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-xs transition-colors hidden">
                    <i class="fas fa-play mr-1.5"></i>Mulai Scan
                </button>
                <button type="button" id="stopScanBtn"
                        class="flex-1 py-2 bg-red-500/80 hover:bg-red-600 text-white rounded-xl font-semibold text-xs transition-colors hidden">
                    <i class="fas fa-stop mr-1.5"></i>Stop
                </button>
                <button type="button"
                        class="py-2 px-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold text-xs transition-colors"
                        onclick="showManualInputDialog()">
                    <i class="fas fa-keyboard"></i>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ===== Result Modal ===== --}}
<div id="resultModal" class="modal-overlay">
    <div class="modal-box" style="max-width:420px;">
        <div class="modal-header bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center gap-2">
                <i class="fas fa-user-check text-white"></i>
                <h3 class="text-sm font-bold text-white">Hasil Scan Anggota</h3>
            </div>
            <button type="button" class="modal-close" id="closeResultModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-5">
            <div id="barcodeLoading" class="hidden text-center py-3">
                <div class="inline-flex items-center gap-2 text-indigo-600 text-xs">
                    <i class="fas fa-spinner fa-spin"></i><span>Mencari data anggota...</span>
                </div>
            </div>
            <div id="barcodeError" class="hidden bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl text-xs mb-3">
                <i class="fas fa-exclamation-circle mr-1"></i><span id="barcodeErrorText"></span>
            </div>
            <div id="barcodeResult" class="hidden">
                <div class="border border-indigo-100 rounded-xl overflow-hidden mb-3">
                    {{-- Anggota info --}}
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-4 py-3 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <img id="resultFoto" src="" class="w-11 h-11 rounded-xl object-cover hidden" alt=""
                                 onerror="this.classList.add('hidden');document.getElementById('resultFotoInitial').classList.remove('hidden');">
                            <span id="resultFotoInitial" class="text-white font-bold text-sm"></span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900" id="resultNama"></p>
                            <p class="text-[10px] text-gray-500" id="resultNomor"></p>
                            <p class="text-[10px] text-gray-400" id="resultKelas"></p>
                        </div>
                    </div>
                    {{-- Ada denda --}}
                    <div id="hasDendaSection" class="px-4 py-3 hidden">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] text-gray-500">Denda Belum Dibayar:</span>
                            <span class="text-xs font-bold text-red-600" id="resultJumlahDenda"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] text-gray-500">Total Nominal:</span>
                            <span class="text-base font-extrabold text-red-600" id="resultTotalDenda"></span>
                        </div>
                    </div>
                    {{-- Tidak ada denda --}}
                    <div id="noDendaMsg" class="px-4 py-3 hidden">
                        <div class="flex items-center gap-2 bg-emerald-50 border border-emerald-100 rounded-lg px-3 py-2.5">
                            <i class="fas fa-check-circle text-emerald-500 text-base flex-shrink-0"></i>
                            <span class="text-xs font-semibold text-emerald-700">Anggota ini tidak memiliki denda yang belum dibayar.</span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    @if(Auth::user()->isAdmin() || Auth::user()->isPetugas())
                    <button type="button" id="bayarLunasBtn"
                            class="flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm hidden">
                        <i class="fas fa-check-double mr-1"></i>Bayar Lunas Semua
                    </button>
                    @else
                    <div id="bayarLunasBtn" class="hidden"></div>
                    @endif
                    <button type="button" id="scanAgainBtn"
                            class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-all">
                        <i class="fas fa-redo mr-1"></i>Scan Lagi
                    </button>
                </div>
            </div>
            {{-- Manual input --}}
            <div class="relative my-4">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                <div class="relative flex justify-center"><span class="px-3 bg-white text-[10px] text-gray-400 font-semibold uppercase tracking-wider">atau ketik manual</span></div>
            </div>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-indigo-400 pointer-events-none">
                        <i class="fas fa-keyboard text-xs"></i>
                    </span>
                    <input type="text" id="barcodeInput" placeholder="Ketik barcode / nomor anggota..."
                           class="filter-input pl-9 font-mono">
                </div>
                <button type="button" id="manualSearchBtn"
                        class="px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-all">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </div>
</div>


{{-- jQuery + DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {

    // ========================
    // DataTables Init
    // ========================
    var dendaTable = $('#denda-table').DataTable({
        dom:
            '<"flex items-center justify-between px-5 py-3 border-b border-gray-50 bg-gray-50/30"<"text-xs text-gray-500"l>>' +
            'rt' +
            '<"dt-bottom-bar px-5 py-3"<"text-xs text-gray-400"i><"dt-pager"p>>',
        language: {
            lengthMenu: 'Tampilkan _MENU_ entri',
            info: 'Menampilkan _START_–_END_ dari <b>_TOTAL_</b> data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            emptyTable: `<div style="padding:40px 24px;text-align:center;">
                <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#ecfdf5,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <i class="fas fa-check-circle" style="font-size:28px;color:#10b981;"></i>
                </div>
                <p style="font-size:13px;font-weight:700;color:#374151;margin-bottom:4px;">Semua denda sudah dibayar!</p>
                <p style="font-size:11px;color:#9ca3af;">Tidak ada anggota dengan denda yang belum dibayar</p>
            </div>`,
            paginate: {
                first:    '<i class="fas fa-angle-double-left text-xs"></i>',
                last:     '<i class="fas fa-angle-double-right text-xs"></i>',
                next:     '<i class="fas fa-angle-right text-xs"></i>',
                previous: '<i class="fas fa-angle-left text-xs"></i>',
            },
            zeroRecords: `<div style="padding:40px 24px;text-align:center;">
                <div style="font-size:32px;color:#d1d5db;margin-bottom:12px;"><i class="fas fa-search"></i></div>
                <p style="font-size:13px;font-weight:700;color:#374151;">Tidak ada hasil ditemukan</p>
                <p style="font-size:11px;color:#9ca3af;">Coba ubah kata kunci atau filter pencarian</p>
            </div>`,
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']],
        order: [[4, 'desc']],
        columns: [
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: false, searchable: false },
        ],
        initComplete: function () {
            // Style the paginate wrapper
            $('.dt-pager').css({ display: 'flex', alignItems: 'center', gap: '2px' });
        }
    });

    // ========================
    // Filter Modal
    // ========================
    const filterModal  = document.getElementById('filterModal');
    const openFilter   = document.getElementById('openFilterModal');
    const closeFilter  = document.getElementById('closeFilterModal');

    openFilter.addEventListener('click', () => filterModal.classList.add('active'));
    closeFilter.addEventListener('click', () => filterModal.classList.remove('active'));
    filterModal.addEventListener('click', (e) => { if (e.target === filterModal) filterModal.classList.remove('active'); });

    // Auto-open if active filters (show user current state)
    @if($activeFilterCount > 0)
    // filters are active, don't auto-open — just show chips
    @endif

    // ========================
    // Bayar Lunas with SweetAlert
    // ========================
    document.querySelectorAll('.bayar-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const nama  = this.dataset.nama;
            const denda = this.dataset.denda;
            const formEl = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    html: `Tandai denda <b>${denda}</b> atas nama <b>${nama}</b> sebagai <span style="color:#10b981;font-weight:700;">LUNAS</span>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i>Ya, Bayar Lunas',
                    cancelButtonText: 'Batal',
                }).then(result => { if (result.isConfirmed) formEl.submit(); });
            } else {
                if (confirm(`Tandai denda ${denda} atas nama ${nama} sebagai LUNAS?`)) formEl.submit();
            }
        });
    });

    // ========================
    // Result Modal
    // ========================
    document.getElementById('scanBarcodeBtn').addEventListener('click', openScannerModal);
    document.getElementById('closeResultModal').addEventListener('click', closeResultModal);
    document.getElementById('resultModal').addEventListener('click', (e) => { if (e.target === document.getElementById('resultModal')) closeResultModal(); });

    document.getElementById('scanAgainBtn').addEventListener('click', () => {
        closeResultModal();
        openScannerModal();
    });

    document.getElementById('bayarLunasBtn').addEventListener('click', () => {
        const nama  = document.getElementById('resultNama').textContent;
        const total = 'Rp ' + new Intl.NumberFormat('id-ID').format(dendaFoundTotalDenda);
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Konfirmasi Pelunasan Denda',
                html: `Bayar lunas semua denda milik <b>${nama}</b>?<br>
                       Total: <span style="color:#dc2626;font-weight:800;">${total}</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-check-double" style="margin-right:4px"></i>Ya, Bayar Lunas',
                cancelButtonText: 'Batal',
            }).then(result => { if (result.isConfirmed) doBayarLunas(); });
        } else {
            if (confirm('Bayar lunas semua denda ' + nama + ' (' + total + ')?')) doBayarLunas();
        }
    });

    document.getElementById('manualSearchBtn').addEventListener('click', () => {
        const b = document.getElementById('barcodeInput').value.trim();
        if (b) searchByBarcode(b);
    });
    document.getElementById('barcodeInput').addEventListener('keypress', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); const b = e.target.value.trim(); if (b) searchByBarcode(b); }
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            filterModal.classList.remove('active');
            closeScanner();
            closeResultModal();
        }
    });

});

// ─────────────────────────────────────────────────
// Denda result modal state
// ─────────────────────────────────────────────────
let dendaFoundMemberId    = 0;
let dendaFoundTotalDenda  = 0;

function resetResultModal() {
    document.getElementById('barcodeInput').value = '';
    document.getElementById('barcodeLoading').classList.add('hidden');
    document.getElementById('barcodeError').classList.add('hidden');
    document.getElementById('barcodeResult').classList.add('hidden');
    document.getElementById('hasDendaSection').classList.add('hidden');
    document.getElementById('noDendaMsg').classList.add('hidden');
    document.getElementById('bayarLunasBtn').classList.add('hidden');
    dendaFoundMemberId   = 0;
    dendaFoundTotalDenda = 0;
}

function closeResultModal() {
    document.getElementById('resultModal').classList.remove('active');
    resetResultModal();
}

function openResultModal(barcode) {
    resetResultModal();
    document.getElementById('resultModal').classList.add('active');
    if (barcode) {
        document.getElementById('barcodeInput').value = barcode;
        searchByBarcode(barcode);
    }
}

function searchByBarcode(barcode) {
    document.getElementById('barcodeLoading').classList.remove('hidden');
    document.getElementById('barcodeError').classList.add('hidden');
    document.getElementById('barcodeResult').classList.add('hidden');
    fetch('{{ route("admin.denda.scan-barcode") }}?barcode=' + encodeURIComponent(barcode), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('barcodeLoading').classList.add('hidden');
        if (!data.success) {
            document.getElementById('barcodeError').classList.remove('hidden');
            document.getElementById('barcodeErrorText').textContent = data.message;
            return;
        }
        dendaFoundMemberId   = data.anggota.id;
        dendaFoundTotalDenda = data.total_denda;
        document.getElementById('barcodeResult').classList.remove('hidden');
        document.getElementById('resultNama').textContent  = data.anggota.nama_lengkap;
        document.getElementById('resultNomor').textContent = data.anggota.nomor_anggota + (data.anggota.barcode_anggota ? ' | ' + data.anggota.barcode_anggota : '');
        document.getElementById('resultKelas').textContent = data.anggota.kelas;
        const fotoEl = document.getElementById('resultFoto');
        const initEl = document.getElementById('resultFotoInitial');
        if (data.anggota.foto) {
            fotoEl.src = data.anggota.foto; fotoEl.classList.remove('hidden'); initEl.classList.add('hidden');
        } else {
            fotoEl.classList.add('hidden'); initEl.classList.remove('hidden');
            initEl.textContent = (data.anggota.nama_lengkap || 'N').charAt(0).toUpperCase();
        }
        if (data.jumlah_denda > 0) {
            document.getElementById('hasDendaSection').classList.remove('hidden');
            document.getElementById('noDendaMsg').classList.add('hidden');
            document.getElementById('resultJumlahDenda').textContent = data.jumlah_denda + ' denda belum dibayar';
            document.getElementById('resultTotalDenda').textContent  = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_denda);
            document.getElementById('bayarLunasBtn').classList.remove('hidden');
        } else {
            document.getElementById('hasDendaSection').classList.add('hidden');
            document.getElementById('noDendaMsg').classList.remove('hidden');
            document.getElementById('bayarLunasBtn').classList.add('hidden');
        }
    })
    .catch(() => {
        document.getElementById('barcodeLoading').classList.add('hidden');
        document.getElementById('barcodeError').classList.remove('hidden');
        document.getElementById('barcodeErrorText').textContent = 'Terjadi kesalahan saat mencari data.';
    });
}

function doBayarLunas() {
    const btn = document.getElementById('bayarLunasBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
    fetch('{{ route("admin.denda.bayar-lunas-anggota") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ anggota_id: dendaFoundMemberId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closeResultModal();
            if (typeof Swal !== 'undefined') {
                Swal.fire({ title: 'Berhasil!', text: data.message, icon: 'success', timer: 2200, showConfirmButton: false })
                    .then(() => window.location.reload());
            } else { alert(data.message); window.location.reload(); }
        } else {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-double mr-1"></i>Bayar Lunas Semua';
            if (typeof Swal !== 'undefined') { Swal.fire('Gagal', data.message, 'error'); } else { alert(data.message); }
        }
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-double mr-1"></i>Bayar Lunas Semua';
        if (typeof Swal !== 'undefined') { Swal.fire('Error', 'Terjadi kesalahan jaringan. Coba lagi.', 'error'); }
    });
}

// ─────────────────────────────────────────────────
// Scanner — Device-Aware (html5-qrcode + BarcodeDetector)
// ─────────────────────────────────────────────────
let html5QrcodeScanner    = null;
let nativeBarcodeDetector = null;
let nativeScanStream      = null;
let nativeScanInterval    = null;
let torchEnabled          = false;
let lastScannedCode       = '';
let lastScanTime          = 0;
const scanCooldown        = 1500;
let isProcessingBarcode   = false;
let cameraDevices         = [];
let currentCameraIndex    = 0;
const hasNativeBarcodeAPI = ('BarcodeDetector' in window);

async function openScannerModal() {
    document.getElementById('scannerModal').classList.remove('hidden');
    lastScannedCode = ''; lastScanTime = 0; isProcessingBarcode = false;

    document.getElementById('scannerLoading').classList.add('hidden');
    document.getElementById('scannerVideo').classList.add('hidden');
    document.getElementById('scanOverlay').classList.add('hidden');
    document.getElementById('startScanBtn').classList.add('hidden');
    document.getElementById('stopScanBtn').classList.add('hidden');
    document.getElementById('toggleTorchBtn').classList.add('hidden');
    document.getElementById('switchCameraBtn').classList.add('hidden');

    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center">
        <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full denda-spinner mx-auto mb-3"></div>
        <p class="text-sm text-white/70">Mendeteksi kamera tersedia...</p>
    </div>`;
    updateScannerStatus('idle', 'Mendeteksi kamera...');

    await enumerateCameras();
    await startScanner();
}

async function enumerateCameras() {
    cameraDevices = []; currentCameraIndex = 0;
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
        const all  = await navigator.mediaDevices.enumerateDevices();
        let vids   = all.filter(d => d.kind === 'videoinput');
        if (!vids.length) return;
        vids.sort((a, b) => {
            const rA = /back|rear|environment|belakang/i.test(a.label) ? 1 : 0;
            const rB = /back|rear|environment|belakang/i.test(b.label) ? 1 : 0;
            return rB - rA;
        });
        cameraDevices = vids;
    } catch (e) {}
}

async function refreshCameraLabels() {
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
        const all  = await navigator.mediaDevices.enumerateDevices();
        let vids   = all.filter(d => d.kind === 'videoinput');
        if (!vids.length || vids[0].label === '') return;
        vids.sort((a, b) => {
            const rA = /back|rear|environment|belakang/i.test(a.label) ? 1 : 0;
            const rB = /back|rear|environment|belakang/i.test(b.label) ? 1 : 0;
            return rB - rA;
        });
        if (nativeScanStream) {
            const activeId = nativeScanStream.getVideoTracks()[0]?.getSettings()?.deviceId;
            const idx = vids.findIndex(d => d.deviceId === activeId);
            if (idx >= 0) currentCameraIndex = idx;
        }
        cameraDevices = vids;
        document.getElementById('switchCameraBtn').classList.toggle('hidden', vids.length < 2);
    } catch (e) {}
}

async function startScanner() {
    if (cameraDevices.length > 0 && cameraDevices[currentCameraIndex]?.deviceId) {
        const dev = cameraDevices[currentCameraIndex];
        await startScannerWithDeviceId(dev.deviceId, dev.label);
    } else {
        await startWithFacingModeFallback();
    }
}

async function startScannerWithDeviceId(deviceId, label) {
    updateScannerStatus('idle', label ? 'Menghubungkan: ' + label.substring(0, 30) + '...' : 'Menghubungkan kamera...');
    let r = await tryGetUserMedia({ deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia({ deviceId: { exact: deviceId } });
    if (r === 'fatal' || r === true) return;
    await startWithFacingModeFallback();
}

async function startWithFacingModeFallback() {
    let r = await tryGetUserMedia({ facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia({ facingMode: { ideal: 'user' }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia({ facingMode: { ideal: 'environment' } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia(true);
    if (r === 'fatal' || r === true) return;
    if (typeof Html5Qrcode !== 'undefined') { initHTML5Scanner(); } else { setupManualInput(); }
}

async function tryGetUserMedia(videoConstraints) {
    try {
        nativeScanStream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints, audio: false });
        await setupVideoFromStream(nativeScanStream);
        await refreshCameraLabels();
        return true;
    } catch (err) {
        if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') { setupPermissionDenied(); return 'fatal'; }
        return false;
    }
}

async function setupVideoFromStream(stream) {
    let videoEl = document.getElementById('nativeScanVideo');
    if (!videoEl) {
        videoEl = document.createElement('video');
        videoEl.id = 'nativeScanVideo';
        videoEl.setAttribute('playsinline', '');
        videoEl.setAttribute('autoplay',    '');
        videoEl.setAttribute('muted',       '');
        videoEl.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
        const readerEl = document.getElementById('reader');
        readerEl.innerHTML = '';
        readerEl.appendChild(videoEl);
    } else {
        if (videoEl.srcObject) { videoEl.srcObject.getTracks().forEach(t => t.stop()); }
    }
    videoEl.srcObject = stream;
    await new Promise((resolve) => {
        if (videoEl.readyState >= 2) { resolve(); return; }
        const onReady = () => { videoEl.removeEventListener('loadedmetadata', onReady); resolve(); };
        videoEl.addEventListener('loadedmetadata', onReady);
        videoEl.play().catch(() => {});
        setTimeout(resolve, 8000);
    });
    const track = stream.getVideoTracks()[0];
    if (track) {
        const caps = track.getCapabilities ? track.getCapabilities() : {};
        document.getElementById('toggleTorchBtn').classList.toggle('hidden', !caps.torch);
        try { if (caps.focusMode?.includes('continuous')) await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }); } catch (e) {}
    }
    document.getElementById('scannerLoading').classList.add('hidden');
    document.getElementById('scannerPlaceholder').classList.add('hidden');
    document.getElementById('scannerVideo').classList.remove('hidden');
    document.getElementById('scanOverlay').classList.remove('hidden');
    document.getElementById('startScanBtn').classList.add('hidden');
    document.getElementById('stopScanBtn').classList.remove('hidden');
    const devLabel = cameraDevices[currentCameraIndex]?.label || (track?.getSettings()?.facingMode === 'user' ? 'Kamera Depan' : 'Kamera Belakang');
    const camInfo  = cameraDevices.length > 1 ? ` (${currentCameraIndex + 1}/${cameraDevices.length})` : '';
    updateScannerStatus('active', devLabel.substring(0, 30) + camInfo);
    if (hasNativeBarcodeAPI) {
        nativeBarcodeDetector = new BarcodeDetector({
            formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'itf', 'codabar', 'qr_code', 'data_matrix', 'aztec', 'pdf417']
        });
        startNativeScanLoop(videoEl);
    } else if (typeof Html5Qrcode !== 'undefined') {
        initHTML5Scanner();
    }
}

function startNativeScanLoop(videoEl) {
    if (nativeScanInterval) clearInterval(nativeScanInterval);
    nativeScanInterval = setInterval(async () => {
        if (!nativeBarcodeDetector || !videoEl || videoEl.readyState < 2) return;
        try {
            const barcodes = await nativeBarcodeDetector.detect(videoEl);
            if (barcodes.length > 0) {
                const code = barcodes[0].rawValue;
                const now  = Date.now();
                if (code === lastScannedCode && (now - lastScanTime) < scanCooldown) return;
                lastScannedCode = code; lastScanTime = now;
                if (navigator.vibrate) navigator.vibrate([80]);
                flashScanSuccess();
                processScannedBarcode(code);
            }
        } catch (e) {}
    }, 50);
}

function flashScanSuccess() {
    const overlay = document.getElementById('scanOverlay');
    if (!overlay) return;
    const flash = document.createElement('div');
    flash.className = 'absolute inset-0 bg-emerald-500/20 z-20 pointer-events-none';
    flash.style.transition = 'opacity .3s ease';
    overlay.appendChild(flash);
    setTimeout(() => { flash.style.opacity = '0'; }, 100);
    setTimeout(() => { flash.remove(); }, 400);
}

function initHTML5Scanner() {
    const loading     = document.getElementById('scannerLoading');
    const video       = document.getElementById('scannerVideo');
    const placeholder = document.getElementById('scannerPlaceholder');
    const overlay     = document.getElementById('scanOverlay');
    if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
    if (nativeScanInterval) { clearInterval(nativeScanInterval); nativeScanInterval = null; }
    if (html5QrcodeScanner) { try { html5QrcodeScanner.stop().catch(()=>{}); } catch(e){} html5QrcodeScanner = null; }
    loading.classList.remove('hidden');
    placeholder.classList.add('hidden');
    video.classList.remove('hidden');
    document.getElementById('reader').innerHTML = '';
    const config = {
        fps: 15,
        qrbox: (w, h) => ({ width: Math.floor(w * .82), height: Math.floor(h * .62) }),
        aspectRatio: window.innerWidth > window.innerHeight ? 16/9 : 4/3,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.CODE_93,  Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8,    Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E,    Html5QrcodeSupportedFormats.ITF,
            Html5QrcodeSupportedFormats.CODABAR,  Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.DATA_MATRIX
        ],
        experimentalFeatures: { useBarCodeDetectorIfSupported: true },
        videoConstraints: { width: { ideal: 1280 }, height: { ideal: 720 } }
    };
    const onSuccess = (decodedText) => {
        const now = Date.now();
        if (decodedText === lastScannedCode && (now - lastScanTime) < scanCooldown) return;
        lastScannedCode = decodedText; lastScanTime = now;
        if (navigator.vibrate) navigator.vibrate([80]);
        flashScanSuccess();
        processScannedBarcode(decodedText);
    };
    const hasDev   = cameraDevices.length > 0 && cameraDevices[currentCameraIndex]?.deviceId;
    const camConst = hasDev ? { deviceId: { exact: cameraDevices[currentCameraIndex].deviceId } } : { facingMode: { ideal: 'environment' } };
    html5QrcodeScanner = new Html5Qrcode('reader');
    html5QrcodeScanner.start(camConst, config, onSuccess, () => {})
    .then(() => {
        loading.classList.add('hidden'); overlay.classList.remove('hidden');
        updateScannerStatus('active', 'Scanner aktif');
        document.getElementById('startScanBtn').classList.add('hidden');
        document.getElementById('stopScanBtn').classList.remove('hidden');
        refreshCameraLabels();
    })
    .catch(async () => {
        try { await html5QrcodeScanner.stop().catch(()=>{}); } catch(e) {}
        html5QrcodeScanner = new Html5Qrcode('reader');
        html5QrcodeScanner.start(
            { facingMode: { ideal: 'user' } },
            { ...config, videoConstraints: { width: { ideal: 640 }, height: { ideal: 480 } } },
            onSuccess, () => {}
        )
        .then(() => {
            loading.classList.add('hidden'); overlay.classList.remove('hidden');
            updateScannerStatus('active', 'Kamera depan aktif');
            document.getElementById('startScanBtn').classList.add('hidden');
            document.getElementById('stopScanBtn').classList.remove('hidden');
        })
        .catch(() => { loading.classList.add('hidden'); placeholder.classList.remove('hidden'); video.classList.add('hidden'); setupManualInput(); });
    });
}

function updateScannerStatus(state, text) {
    const dot    = document.getElementById('scannerStatusDot');
    const status = document.getElementById('scannerStatus');
    if (status) status.textContent = text;
    if (dot) dot.className = state === 'active'
        ? 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse'
        : (state === 'error' ? 'w-2 h-2 rounded-full bg-red-500' : 'w-2 h-2 rounded-full bg-gray-500');
}

function stopAllScanners() {
    if (nativeScanInterval)  { clearInterval(nativeScanInterval); nativeScanInterval = null; }
    if (nativeScanStream)    { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
    nativeBarcodeDetector = null; torchEnabled = false;
    if (html5QrcodeScanner)  { try { html5QrcodeScanner.stop().catch(()=>{}); } catch(e){} html5QrcodeScanner = null; }
    updateScannerStatus('idle', 'Scanner dihentikan');
    document.getElementById('startScanBtn').classList.remove('hidden');
    document.getElementById('stopScanBtn').classList.add('hidden');
    document.getElementById('toggleTorchBtn').classList.add('hidden');
}

function closeScanner() {
    stopAllScanners();
    isProcessingBarcode = false;
    document.getElementById('scannerModal').classList.add('hidden');
    document.getElementById('scanOverlay').classList.add('hidden');
    document.getElementById('switchCameraBtn').classList.add('hidden');
    updateScannerStatus('idle', 'Siap untuk scan');
    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center">
        <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-camera text-2xl text-white/60"></i>
        </div>
        <p class="text-sm text-white/70 mb-1">Kamera akan aktif otomatis</p>
        <p class="text-xs text-white/40">Pastikan izin kamera diaktifkan</p>
    </div>`;
    document.getElementById('scannerVideo').classList.add('hidden');
    document.getElementById('scannerLoading').classList.add('hidden');
    const nv = document.getElementById('nativeScanVideo');
    if (nv) { nv.srcObject = null; }
}

function setupPermissionDenied() {
    document.getElementById('scannerLoading').classList.add('hidden');
    document.getElementById('scannerVideo').classList.add('hidden');
    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center">
        <div class="w-14 h-14 bg-red-500/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-ban text-2xl text-red-400"></i>
        </div>
        <p class="text-sm font-semibold text-white/80 mb-1">Akses Kamera Ditolak</p>
        <p class="text-xs text-white/40 mb-4 max-w-xs mx-auto">
            Izinkan akses kamera di browser, lalu muat ulang halaman atau klik tombol di bawah.
        </p>
        <div class="flex flex-col gap-2 items-center">
            <button type="button" onclick="openScannerModal()"
                    class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-xs transition-colors">
                <i class="fas fa-redo mr-1.5"></i>Coba Lagi
            </button>
            <button type="button" onclick="showManualInputDialog()"
                    class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white/80 rounded-xl font-semibold text-xs transition-colors">
                <i class="fas fa-keyboard mr-1.5"></i>Input Manual
            </button>
        </div>
    </div>`;
    updateScannerStatus('error', 'Izin kamera ditolak');
}

function setupManualInput() {
    document.getElementById('scannerVideo').classList.add('hidden');
    document.getElementById('scannerLoading').classList.add('hidden');
    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center">
        <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-3">
            <i class="fas fa-keyboard text-2xl text-white/60"></i>
        </div>
        <p class="text-sm text-white/70 mb-1">Kamera tidak tersedia</p>
        <p class="text-xs text-white/40 mb-4">Gunakan input barcode manual</p>
        <button type="button" onclick="showManualInputDialog()"
                class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold text-xs transition-colors">
            <i class="fas fa-keyboard mr-1.5"></i>Input Manual
        </button>
    </div>`;
    updateScannerStatus('error', 'Kamera tidak tersedia');
    document.getElementById('startScanBtn').classList.remove('hidden');
    document.getElementById('stopScanBtn').classList.add('hidden');
}

function showManualInputDialog() {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        title: 'Input Barcode Manual',
        input: 'text',
        inputPlaceholder: 'Masukkan kode barcode / nomor anggota...',
        inputAttributes: { autocomplete: 'off', autocorrect: 'off', spellcheck: 'false' },
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check mr-1"></i>Proses',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3b82f6',
        didOpen: () => { document.querySelector('.swal2-input')?.focus(); },
        inputValidator: v => { if (!v?.trim()) return 'Masukkan kode barcode!'; }
    }).then(r => {
        if (r.isConfirmed && r.value) {
            closeScanner();
            openResultModal(r.value.trim());
        }
    });
}

function processScannedBarcode(barcode) {
    if (isProcessingBarcode) return;
    isProcessingBarcode = true;
    updateScannerStatus('idle', 'Barcode terdeteksi...');
    closeScanner();
    openResultModal(barcode);
}

// Scanner button listeners
document.addEventListener('DOMContentLoaded', function () {
    document.getElementById('closeScanner').addEventListener('click',  closeScanner);
    document.getElementById('cancelScan').addEventListener('click',    closeScanner);
    document.getElementById('startScanBtn').addEventListener('click',  () => startScanner());
    document.getElementById('stopScanBtn').addEventListener('click',   stopAllScanners);
    document.getElementById('switchCameraBtn').addEventListener('click', async () => {
        if (cameraDevices.length < 2) return;
        currentCameraIndex = (currentCameraIndex + 1) % cameraDevices.length;
        stopAllScanners();
        const dev = cameraDevices[currentCameraIndex];
        updateScannerStatus('idle', 'Beralih kamera...');
        await startScannerWithDeviceId(dev.deviceId, dev.label);
    });
    document.getElementById('toggleTorchBtn').addEventListener('click', async function () {
        if (!nativeScanStream) return;
        const track = nativeScanStream.getVideoTracks()[0];
        if (!track) return;
        try {
            torchEnabled = !torchEnabled;
            await track.applyConstraints({ advanced: [{ torch: torchEnabled }] });
            this.classList.toggle('bg-amber-500/60', torchEnabled);
            this.classList.toggle('bg-white/15', !torchEnabled);
        } catch (e) { torchEnabled = !torchEnabled; }
    });
});
</script>
@endsection
