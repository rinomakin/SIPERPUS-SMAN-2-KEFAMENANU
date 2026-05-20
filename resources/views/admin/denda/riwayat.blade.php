@extends('layouts.admin')

@section('title', 'Riwayat Pembayaran Denda')

@push('styles')
<style>
/* ===== Dark Mode Overrides: Denda Riwayat ===== */
html[data-theme="dark"] .stat-card .stat-icon {
    filter: brightness(0.65) saturate(1.4);
}
html[data-theme="dark"] .modal-box {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #e2e8f0 !important;
}
html[data-theme="dark"] .filter-label { color: #94a3b8 !important; }
html[data-theme="dark"] .filter-input {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
html[data-theme="dark"] .filter-input:focus {
    border-color: #10b981 !important;
    background-color: #1e293b !important;
}
html[data-theme="dark"] .filter-select {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
/* DataTables ID-based overrides */
html[data-theme="dark"] #riwayat-table_wrapper .dataTables_length {
    border-color: #334155 !important;
    color: #94a3b8 !important;
}
html[data-theme="dark"] #riwayat-table_wrapper .dataTables_length select {
    background-color: #0f172a !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
html[data-theme="dark"] #riwayat-table_wrapper .dataTables_info { color: #64748b !important; }
html[data-theme="dark"] #riwayat-table_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
    background: #1e293b !important;
    color: #a5b4fc !important;
    border-color: #334155 !important;
}
html[data-theme="dark"] .riwayat-table thead th {
    background: #1e293b !important;
    border-color: #334155 !important;
}
html[data-theme="dark"] .riwayat-table tbody td { border-color: #1e293b !important; }
html[data-theme="dark"] .riwayat-table tbody tr:hover { background: rgba(99,102,241,0.06) !important; }
html[data-theme="dark"] .riwayat-table tbody tr.selected-row { background: rgba(59,130,246,0.12) !important; }
html[data-theme="dark"] .dt-bottom-bar { border-color: #334155 !important; }
html[data-theme="dark"] .glass-card > .border-b { border-color: #334155 !important; }
html[data-theme="dark"] .avatar-img { border-color: #334155 !important; }
/* Active filter bar */
html[data-theme="dark"] .bg-emerald-50\/60 { background-color: rgba(16,185,129,0.08) !important; }
html[data-theme="dark"] .border-emerald-100 { border-color: rgba(16,185,129,0.2) !important; }
</style>
@endpush

@section('content')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

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
    position: absolute; top: -20px; right: -20px;
    width: 90px; height: 90px; border-radius: 50%; opacity: 0.06;
}
.stat-card .stat-value { font-size: 22px; font-weight: 800; line-height: 1.2; }
.stat-card .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }

/* ===== Glass Card ===== */
.glass-card {
    background: rgba(255,255,255,0.97); backdrop-filter: blur(20px);
    border-radius: 16px; border: 1px solid rgba(255,255,255,0.8);
    box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden;
}

/* ===== Animations ===== */
@keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
.anim-up  { animation: fadeInUp 0.4s ease forwards; }
.anim-d1  { animation-delay: 0.05s; opacity: 0; }
.anim-d2  { animation-delay: 0.10s; opacity: 0; }
.anim-d3  { animation-delay: 0.15s; opacity: 0; }
.anim-d4  { animation-delay: 0.20s; opacity: 0; }

/* ===== Table ===== */
.riwayat-table { border-collapse: separate; border-spacing: 0; width: 100%; }
.riwayat-table thead th {
    background: #f8fafc; font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: #94a3b8; padding: 12px 16px; border-bottom: 2px solid #f1f5f9;
    white-space: nowrap;
}
.riwayat-table thead th.sorting:after,
.riwayat-table thead th.sorting_asc:after,
.riwayat-table thead th.sorting_desc:after { color: #94a3b8 !important; }
.riwayat-table tbody tr { transition: all 0.15s ease; }
.riwayat-table tbody tr:hover { background: #f0fdf4; }
.riwayat-table tbody tr.selected-row { background: #eff6ff !important; }
.riwayat-table tbody td { padding: 13px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }

/* ===== Checkbox ===== */
.row-checkbox, .select-all-cb {
    width: 16px; height: 16px; border-radius: 4px; cursor: pointer;
    accent-color: #10b981;
}

/* ===== Avatar ===== */
.avatar-circle {
    width: 38px; height: 38px; border-radius: 11px;
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; color: white; flex-shrink: 0;
}
.avatar-img {
    width: 38px; height: 38px; border-radius: 11px; object-fit: cover;
    flex-shrink: 0; border: 2px solid #f1f5f9;
}

/* ===== Badges ===== */
.badge-late {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626;
    border: 1px solid #fecaca;
}
.badge-paid {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #059669;
    border: 1px solid #a7f3d0;
}
.badge-amount { font-size: 13px; font-weight: 700; color: #059669; }

/* ===== Action Buttons ===== */
.action-btn {
    width: 32px; height: 32px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; transition: all 0.2s; border: 1px solid transparent;
}
.action-btn.view   { color: #3b82f6; background: #eff6ff; border-color: #dbeafe; }
.action-btn.view:hover { background: #3b82f6; color: white; }
.action-btn.delete { color: #ef4444; background: #fef2f2; border-color: #fecaca; }
.action-btn.delete:hover { background: #ef4444; color: white; }

/* ===== Filter Chip ===== */
.filter-chip {
    display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 600; transition: all 0.2s;
}

/* ===== DataTables Override ===== */
#riwayat-table_wrapper .dataTables_filter { display: none !important; }
#riwayat-table_wrapper { overflow: visible !important; }
#riwayat-table_wrapper .dataTables_length {
    padding: 14px 20px; font-size: 12px; color: #6b7280;
    display: flex; align-items: center; gap: 8px;
}
#riwayat-table_wrapper .dataTables_length label { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280; }
#riwayat-table_wrapper .dataTables_length select {
    padding: 5px 28px 5px 10px; border-radius: 8px; border: 1px solid #e5e7eb;
    background: #f9fafb; font-size: 12px; cursor: pointer; outline: none;
    -webkit-appearance: none; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 8px center;
    transition: all 0.2s;
}
#riwayat-table_wrapper .dataTables_length select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); }
#riwayat-table_wrapper .dataTables_info { padding: 14px 20px; font-size: 12px; color: #9ca3af; }
#riwayat-table_wrapper .dataTables_paginate { padding: 10px 20px; }
#riwayat-table_wrapper .dataTables_paginate .paginate_button {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 8px;
    border-radius: 8px; font-size: 12px; font-weight: 600;
    color: #6b7280 !important; border: 1px solid transparent !important;
    background: transparent !important; cursor: pointer; transition: all 0.2s; margin: 0 2px;
}
#riwayat-table_wrapper .dataTables_paginate .paginate_button:hover {
    background: #ecfdf5 !important; color: #059669 !important; border-color: #a7f3d0 !important;
}
#riwayat-table_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #10b981, #059669) !important;
    color: white !important; border-color: transparent !important;
    box-shadow: 0 2px 8px rgba(16,185,129,0.35) !important;
}
#riwayat-table_wrapper .dataTables_paginate .paginate_button.disabled,
#riwayat-table_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    color: #d1d5db !important; background: transparent !important; cursor: default;
}
.dt-bottom-bar {
    display: flex; align-items: center; justify-content: space-between;
    border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 8px;
}

/* ===== Bulk Action Bar ===== */
#bulkActionBar {
    position: fixed; bottom: 24px; left: 50%; transform: translateX(-50%) translateY(80px);
    z-index: 60; min-width: 340px; max-width: 90vw;
    background: #1e293b; border-radius: 16px; padding: 12px 18px;
    display: flex; align-items: center; gap: 12px;
    box-shadow: 0 20px 60px rgba(0,0,0,0.35), 0 4px 20px rgba(0,0,0,0.2);
    transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease;
    opacity: 0; pointer-events: none;
}
#bulkActionBar.visible {
    transform: translateX(-50%) translateY(0); opacity: 1; pointer-events: all;
}
#bulkActionBar .bulk-count {
    display: flex; align-items: center; gap: 8px; flex: 1;
}
#bulkActionBar .count-badge {
    min-width: 28px; height: 28px; border-radius: 8px;
    background: linear-gradient(135deg, #ef4444, #dc2626);
    display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 800; color: white; padding: 0 6px;
}
#bulkActionBar .bulk-label { font-size: 12px; color: #94a3b8; font-weight: 500; }
.bulk-btn-delete {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 700;
    background: linear-gradient(135deg, #ef4444, #dc2626); color: white;
    border: none; cursor: pointer; transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(239,68,68,0.4);
}
.bulk-btn-delete:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(239,68,68,0.5); }
.bulk-btn-cancel {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 8px 14px; border-radius: 10px; font-size: 12px; font-weight: 600;
    background: rgba(255,255,255,0.08); color: #94a3b8;
    border: 1px solid rgba(255,255,255,0.1); cursor: pointer; transition: all 0.2s;
}
.bulk-btn-cancel:hover { background: rgba(255,255,255,0.15); color: white; }

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
.filter-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); background: white; }
.filter-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 6px; display: block; }
.filter-select {
    width: 100%; padding: 9px 32px 9px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; outline: none; transition: all 0.2s; background: #fafafa;
    -webkit-appearance: none; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
}
.filter-select:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.1); background: white; }
</style>

@php $canDelete = Auth::user()->isAdmin() || Auth::user()->isPetugas(); @endphp
<div class="max-w-7xl mx-auto pb-24">

    {{-- ===== Statistics Cards ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card anim-up anim-d1">
            <div class="stat-bg" style="background:#10b981;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);color:#10b981;">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Total Dibayar</p>
            <p class="stat-value text-emerald-600">Rp {{ number_format($totalDendaDibayar, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d2">
            <div class="stat-bg" style="background:#3b82f6;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#eff6ff,#dbeafe);color:#3b82f6;">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Jumlah Transaksi</p>
            <p class="stat-value text-blue-600">{{ number_format($jumlahTransaksi, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d3">
            <div class="stat-bg" style="background:#8b5cf6;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#f5f3ff,#ede9fe);color:#8b5cf6;">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Bulan Ini</p>
            <p class="stat-value text-violet-600">Rp {{ number_format($dendaBulanIni, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d4">
            <div class="stat-bg" style="background:#f59e0b;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);color:#f59e0b;">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Rata-rata Denda</p>
            <p class="stat-value text-amber-600">Rp {{ number_format($rataRataDenda, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ===== Main Card ===== --}}
    <div class="glass-card anim-up" style="animation-delay:0.25s;opacity:0;">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3 mb-3 sm:mb-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white">
                    <i class="fas fa-history text-xs"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Riwayat Pembayaran Denda</h2>
                    <p class="text-[11px] text-gray-400">{{ $riwayat->count() }} transaksi denda lunas tercatat</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Filter Button --}}
                @php
                    $activeFilterCount = collect([request('search'), request('tanggal_mulai'), request('tanggal_selesai'), request('kelas_id'), request('jurusan_id')])->filter()->count();
                @endphp
                <button type="button" id="openFilterModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all border
                               {{ $activeFilterCount > 0 ? 'bg-emerald-500 text-white border-emerald-500 shadow-sm' : 'bg-gray-50 hover:bg-emerald-50 text-gray-600 hover:text-emerald-600 border-gray-200 hover:border-emerald-200' }}">
                    <i class="fas fa-sliders-h"></i>
                    <span>Filter</span>
                    @if($activeFilterCount > 0)
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-white text-emerald-600 text-[10px] font-extrabold">{{ $activeFilterCount }}</span>
                    @endif
                </button>
                {{-- Kembali --}}
                <a href="{{ route('admin.denda.index') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-semibold transition-all border border-gray-200">
                    <i class="fas fa-arrow-left"></i>
                    <span class="hidden sm:inline">Kembali</span>
                </a>
            </div>
        </div>

        {{-- Active Filter Chips --}}
        @if($activeFilterCount > 0)
        <div class="px-5 py-2.5 bg-emerald-50/60 border-b border-emerald-100 flex items-center gap-2 flex-wrap">
            <span class="text-[10px] text-emerald-600 font-bold uppercase tracking-wider flex items-center gap-1">
                <i class="fas fa-filter"></i> Filter Aktif:
            </span>
            @if(request('search'))
                <span class="filter-chip bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <i class="fas fa-search text-[9px]"></i> "{{ request('search') }}"
                    <a href="{{ route('admin.denda.riwayat', array_merge(request()->except('search'))) }}" class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('tanggal_mulai'))
                <span class="filter-chip bg-blue-50 text-blue-700 border border-blue-100">
                    <i class="fas fa-calendar text-[9px]"></i> Dari {{ \Carbon\Carbon::parse(request('tanggal_mulai'))->format('d M Y') }}
                    <a href="{{ route('admin.denda.riwayat', array_merge(request()->except('tanggal_mulai'))) }}" class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('tanggal_selesai'))
                <span class="filter-chip bg-blue-50 text-blue-700 border border-blue-100">
                    <i class="fas fa-calendar text-[9px]"></i> Sampai {{ \Carbon\Carbon::parse(request('tanggal_selesai'))->format('d M Y') }}
                    <a href="{{ route('admin.denda.riwayat', array_merge(request()->except('tanggal_selesai'))) }}" class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('kelas_id'))
                @php $sk = $kelasList->firstWhere('id', request('kelas_id')); @endphp
                <span class="filter-chip bg-purple-50 text-purple-700 border border-purple-100">
                    <i class="fas fa-layer-group text-[9px]"></i> {{ $sk ? $sk->nama_kelas : '-' }}
                    <a href="{{ route('admin.denda.riwayat', array_merge(request()->except('kelas_id'))) }}" class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('jurusan_id'))
                @php $sj = $jurusanList->firstWhere('id', request('jurusan_id')); @endphp
                <span class="filter-chip bg-indigo-50 text-indigo-700 border border-indigo-100">
                    <i class="fas fa-graduation-cap text-[9px]"></i> {{ $sj ? $sj->nama_jurusan : '-' }}
                    <a href="{{ route('admin.denda.riwayat', array_merge(request()->except('jurusan_id'))) }}" class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            <a href="{{ route('admin.denda.riwayat') }}"
               class="ml-auto text-[11px] text-gray-400 hover:text-red-500 font-semibold transition-colors">
                <i class="fas fa-times mr-0.5"></i>Hapus semua
            </a>
        </div>
        @endif

        {{-- DataTables Table --}}
        <div>
            <table id="riwayat-table" class="riwayat-table">
                <thead>
                    <tr>
                        <th style="width:40px;text-align:center;">
                            @if($canDelete)
                            <input type="checkbox" id="selectAllCb" class="select-all-cb" title="Pilih semua">
                            @endif
                        </th>
                        <th class="text-left">Anggota</th>
                        <th class="text-left">Peminjaman</th>
                        <th class="text-center">Terlambat</th>
                        <th class="text-right">Jumlah Denda</th>
                        <th class="text-center">Tgl Pembayaran</th>
                        <th class="text-center" style="width:80px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayat as $item)
                    <tr data-id="{{ $item->id }}">
                        {{-- Checkbox --}}
                        <td class="text-center" style="vertical-align:middle;">
                            @if($canDelete)
                            <input type="checkbox" class="row-checkbox" value="{{ $item->id }}">
                            @endif
                        </td>

                        {{-- Anggota --}}
                        <td data-order="{{ $item->anggota->nama_lengkap ?? '' }}">
                            <div class="flex items-center gap-2.5">
                                @if($item->anggota && $item->anggota->foto)
                                    <img class="avatar-img"
                                         src="{{ asset('storage/anggota/' . $item->anggota->foto) }}"
                                         alt="{{ $item->anggota->nama_lengkap }}"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="avatar-circle" style="display:none;background:linear-gradient(135deg,{{ ['#10b981,#059669','#3b82f6,#2563eb','#8b5cf6,#6d28d9','#f97316,#ea580c','#ec4899,#db2777'][($item->anggota->id ?? 0) % 5] }});">
                                        {{ strtoupper(substr($item->anggota->nama_lengkap ?? 'N', 0, 1)) }}
                                    </div>
                                @elseif($item->anggota)
                                    <div class="avatar-circle" style="background:linear-gradient(135deg,{{ ['#10b981,#059669','#3b82f6,#2563eb','#8b5cf6,#6d28d9','#f97316,#ea580c','#ec4899,#db2777'][($item->anggota->id ?? 0) % 5] }});">
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

                        {{-- Tanggal Pembayaran --}}
                        <td class="text-center" data-order="{{ $item->tanggal_pembayaran ? $item->tanggal_pembayaran->format('Y-m-d') : '0000-00-00' }}">
                            @if($item->tanggal_pembayaran)
                                <span class="badge-paid">
                                    <i class="fas fa-check text-[9px]"></i>
                                    {{ $item->tanggal_pembayaran->format('d M Y') }}
                                </span>
                                <p class="text-[10px] text-gray-400 mt-0.5">{{ $item->tanggal_pembayaran->format('H:i') }}</p>
                            @else
                                <span class="text-xs text-gray-300">-</span>
                            @endif
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <a href="{{ route('admin.denda.show', $item->id) }}"
                               class="action-btn view" title="Detail">
                                <i class="fas fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>{{-- end glass-card --}}

</div>{{-- end max-w-7xl --}}


@if($canDelete)
{{-- ===== Bulk Delete Form (hidden) ===== --}}
<form id="bulkDeleteForm" action="{{ route('admin.denda.riwayat.bulk-destroy') }}" method="POST">
    @csrf
    <div id="bulkIdsContainer"></div>
</form>

{{-- ===== Bulk Action Bar ===== --}}
<div id="bulkActionBar">
    <div class="bulk-count">
        <span class="count-badge" id="selectedCount">0</span>
        <span class="bulk-label">data dipilih</span>
    </div>
    <button type="button" class="bulk-btn-cancel" id="cancelBulkBtn">
        <i class="fas fa-times text-xs"></i>Batal
    </button>
    <button type="button" class="bulk-btn-delete" id="bulkDeleteBtn">
        <i class="fas fa-trash-alt text-xs"></i>Hapus Terpilih
    </button>
</div>
@endif


{{-- ===== Filter Modal ===== --}}
<div id="filterModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header bg-gradient-to-r from-emerald-500 to-teal-600">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fas fa-sliders-h text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Filter & Pencarian</h3>
                    <p class="text-[10px] text-white/70">Saring riwayat pembayaran denda</p>
                </div>
            </div>
            <button type="button" class="modal-close" id="closeFilterModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form action="{{ route('admin.denda.riwayat') }}" method="GET" id="filterForm">
            <div class="p-5 space-y-4">

                {{-- Search --}}
                <div>
                    <label class="filter-label">Cari Anggota</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search"
                               value="{{ request('search') }}"
                               placeholder="Nama atau nomor anggota..."
                               class="filter-input pl-9">
                    </div>
                </div>

                {{-- Tanggal Range --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="filter-label">Dari Tanggal</label>
                        <input type="date" name="tanggal_mulai"
                               value="{{ request('tanggal_mulai') }}"
                               class="filter-input">
                    </div>
                    <div>
                        <label class="filter-label">Sampai Tanggal</label>
                        <input type="date" name="tanggal_selesai"
                               value="{{ request('tanggal_selesai') }}"
                               class="filter-input">
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

            </div>

            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex gap-2.5">
                <a href="{{ route('admin.denda.riwayat') }}"
                   class="flex-1 py-2.5 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl text-xs font-semibold text-center transition-all">
                    <i class="fas fa-redo mr-1"></i>Reset Filter
                </a>
                <button type="submit"
                        class="flex-1 py-2.5 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                    <i class="fas fa-search mr-1"></i>Terapkan Filter
                </button>
            </div>
        </form>
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
    var riwayatTable = $('#riwayat-table').DataTable({
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
                <div style="font-size:40px;color:#d1d5db;margin-bottom:12px;"><i class="fas fa-inbox"></i></div>
                <p style="font-size:13px;font-weight:700;color:#374151;">Belum ada riwayat pembayaran</p>
                <p style="font-size:11px;color:#9ca3af;margin-top:4px;">Riwayat akan muncul setelah ada denda yang dibayar</p>
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
        order: [[5, 'desc']],
        columns: [
            { orderable: false, searchable: false }, // checkbox
            { orderable: true  },                    // anggota
            { orderable: true  },                    // peminjaman
            { orderable: true  },                    // terlambat
            { orderable: true  },                    // jumlah denda
            { orderable: true  },                    // tgl pembayaran
            { orderable: false, searchable: false },  // aksi
        ],
    });

    @if($canDelete)
    // ========================
    // Select All & Row Checkboxes
    // ========================
    const selectAllCb   = document.getElementById('selectAllCb');
    const bulkBar       = document.getElementById('bulkActionBar');
    const selectedCount = document.getElementById('selectedCount');

    function getCheckedIds() {
        return Array.from(document.querySelectorAll('.row-checkbox:checked')).map(cb => cb.value);
    }

    function updateBulkBar() {
        const ids = getCheckedIds();
        selectedCount.textContent = ids.length;
        if (ids.length > 0) {
            bulkBar.classList.add('visible');
        } else {
            bulkBar.classList.remove('visible');
            selectAllCb.checked = false;
            selectAllCb.indeterminate = false;
        }
        const allVisible  = document.querySelectorAll('#riwayat-table tbody .row-checkbox');
        const allChecked  = Array.from(allVisible).every(cb => cb.checked);
        const someChecked = Array.from(allVisible).some(cb => cb.checked);
        selectAllCb.checked = allChecked && allVisible.length > 0;
        selectAllCb.indeterminate = someChecked && !allChecked;
    }

    selectAllCb.addEventListener('change', function () {
        riwayatTable.rows().every(function () {
            const row = this.node();
            const cb  = row.querySelector('.row-checkbox');
            if (cb) {
                cb.checked = selectAllCb.checked;
                row.classList.toggle('selected-row', selectAllCb.checked);
            }
        });
        updateBulkBar();
    });

    $(document).on('change', '.row-checkbox', function () {
        $(this).closest('tr').toggleClass('selected-row', this.checked);
        updateBulkBar();
    });

    riwayatTable.on('draw', function () {
        selectAllCb.checked = false;
        selectAllCb.indeterminate = false;
    });

    // ========================
    // Cancel Bulk Selection
    // ========================
    document.getElementById('cancelBulkBtn').addEventListener('click', function () {
        riwayatTable.rows().every(function () {
            const cb = this.node().querySelector('.row-checkbox');
            if (cb) { cb.checked = false; this.node().classList.remove('selected-row'); }
        });
        selectAllCb.checked = false;
        selectAllCb.indeterminate = false;
        bulkBar.classList.remove('visible');
    });

    // ========================
    // Bulk Delete
    // ========================
    document.getElementById('bulkDeleteBtn').addEventListener('click', function () {
        const ids = getCheckedIds();
        if (ids.length === 0) return;
        Swal.fire({
            title: 'Hapus Riwayat Denda?',
            html: `Anda akan menghapus <b>${ids.length} data</b> riwayat pembayaran denda.<br>
                   <small style="color:#9ca3af;">Tindakan ini tidak dapat dibatalkan.</small>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i>Ya, Hapus',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                const container = document.getElementById('bulkIdsContainer');
                container.innerHTML = '';
                ids.forEach(id => {
                    const input = document.createElement('input');
                    input.type  = 'hidden';
                    input.name  = 'ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    });
    @endif

    // ========================
    // Filter Modal
    // ========================
    const filterModal = document.getElementById('filterModal');
    const openFilter  = document.getElementById('openFilterModal');
    const closeFilter = document.getElementById('closeFilterModal');

    openFilter.addEventListener('click',  () => filterModal.classList.add('active'));
    closeFilter.addEventListener('click', () => filterModal.classList.remove('active'));
    filterModal.addEventListener('click', (e) => { if (e.target === filterModal) filterModal.classList.remove('active'); });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') filterModal.classList.remove('active');
    });

});
</script>
@endsection
