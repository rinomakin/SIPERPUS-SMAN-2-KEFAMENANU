@extends('layouts.admin')

@section('title', 'Peminjaman Buku')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    /* ===== Animations ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .anim-up {
        animation: fadeInUp 0.5s ease-out forwards;
        opacity: 0;
    }
    .anim-up.d1 { animation-delay: .05s; }
    .anim-up.d2 { animation-delay: .10s; }
    .anim-up.d3 { animation-delay: .15s; }
    .anim-up.d4 { animation-delay: .20s; }

    /* ===== Stat card hover ===== */
    .stat-mini {
        transition: all .25s cubic-bezier(.4,0,.2,1);
    }
    .stat-mini:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px -6px rgba(0,0,0,.12);
    }

    /* ===== DataTables overrides ===== */
    #peminjaman-table_wrapper .dataTables_length,
    #peminjaman-table_wrapper .dataTables_info,
    #peminjaman-table_wrapper .dataTables_paginate {
        padding: 14px 0;
        font-size: 13px;
        color: #6b7280;
    }
    #peminjaman-table_wrapper .dataTables_filter {
        float: none;
        text-align: inherit;
    }
    #peminjaman-table_wrapper .dataTables_length select {
        padding: 6px 28px 6px 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background-color: #f9fafb;
        font-size: 13px;
        outline: none;
        transition: border-color .2s;
    }
    #peminjaman-table_wrapper .dataTables_length select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,.15);
    }

    /* Table rows */
    #peminjaman-table thead th {
        background: #f8fafc;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: .05em;
        text-transform: uppercase;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 14px 16px !important;
        white-space: nowrap;
    }
    #peminjaman-table tbody td {
        padding: 14px 16px !important;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9 !important;
        font-size: 13px;
    }
    #peminjaman-table tbody tr {
        transition: background .15s;
    }
    #peminjaman-table tbody tr:hover {
        background-color: #f0f7ff !important;
    }

    /* Pagination */
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 7px 13px !important;
        margin: 0 2px !important;
        border-radius: 8px !important;
        border: 1px solid #e5e7eb !important;
        font-size: 13px !important;
        transition: all .2s !important;
        color: #374151 !important;
        background: #fff !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: #fff !important;
        border: none !important;
        box-shadow: 0 2px 6px rgba(37,99,235,.3) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #f3f4f6 !important;
        border-color: #d1d5db !important;
        color: #1f2937 !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: .4 !important;
        cursor: default !important;
    }

    /* ── DT toolbar left/right layout ── */
    #peminjaman-table_wrapper { max-width: 100%; width: 100%; }
    .dt-toolbar .dt-length-wrap .dataTables_length { float: none; }
    .dt-toolbar .dt-length-wrap .dataTables_length label { display: flex; align-items: center; gap: 0.25rem; white-space: nowrap; margin: 0; }
    .dt-toolbar .dt-search-wrap .dataTables_filter { float: none; text-align: inherit; }
    .dt-toolbar .dt-search-wrap .dataTables_filter input {
        width: 200px; padding: 6px 12px; border-radius: 0.75rem;
        border: 1px solid #e5e7eb; font-size: 0.85rem; outline: none;
        background: #f9fafb; transition: all 0.2s;
    }
    .dt-toolbar .dt-search-wrap .dataTables_filter input:focus {
        border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.1); background: white;
    }
    .dt-toolbar .dt-actions { flex-shrink: 0; }
    .dt-toolbar .dt-buttons-wrap { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .dt-table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; }
    .dt-bottom .dt-info { white-space: nowrap; }
    .dt-bottom .dt-pager { white-space: nowrap; }
    @media (max-width: 480px) {
        #peminjaman-table_wrapper .dt-toolbar .dataTables_filter input { width: 100px; font-size: 0.7rem !important; padding: 0.2rem 0.4rem !important; }
        #peminjaman-table_wrapper .dt-toolbar .dataTables_length select { padding: 0.2rem 1.25rem 0.2rem 0.35rem !important; font-size: 0.7rem !important; }
        .dt-toolbar .dt-buttons-wrap a,
        .dt-toolbar .dt-buttons-wrap button { font-size: 0.7rem !important; padding: 0.25rem 0.4rem !important; }
        .dt-toolbar .dt-buttons-wrap { gap: 4px; }
        .dt-bottom { font-size: 0.7rem; }
    }
    @media (max-width: 370px) {
        #peminjaman-table_wrapper .dt-toolbar .dataTables_length select,
        #peminjaman-table_wrapper .dt-toolbar .dataTables_filter input,
        .dt-toolbar .dt-buttons-wrap a,
        .dt-toolbar .dt-buttons-wrap button {
            font-size: 7px !important;
            padding: 2px 3px !important;
        }
        #peminjaman-table_wrapper .dt-toolbar .dataTables_filter input { width: 60px !important; }
        #peminjaman-table_wrapper .dt-toolbar .dataTables_length select { padding: 0.15rem 0.8rem 0.15rem 0.2rem !important; }
        .dt-toolbar .dt-buttons-wrap a,
        .dt-toolbar .dt-buttons-wrap button { gap: 2px !important; }
        .dt-toolbar .dt-buttons-wrap { gap: 2px; }
        .dt-toolbar .dt-buttons-wrap .btn-text { display: none; }
        .dt-toolbar .dt-buttons-wrap a:last-child .btn-text { display: inline; }
        .dt-bottom { font-size: 0.5rem; }
    }

    /* ===== Action Buttons ===== */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        transition: all .2s;
        font-size: 12px;
    }
    .action-btn:hover {
        transform: scale(1.1);
    }

    /* ===== Filter Modal ===== */
    .modal-backdrop {
        transition: opacity .25s;
    }
    .modal-panel {
        transition: all .3s cubic-bezier(.4,0,.2,1);
        transform: scale(.95) translateY(10px);
        opacity: 0;
    }
    .modal-backdrop.active .modal-panel {
        transform: scale(1) translateY(0);
        opacity: 1;
    }

    /* Status dots */
    .status-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }

    /* Quick filter chip */
    .filter-chip {
        transition: all .2s;
    }
    .filter-chip:hover {
        transform: translateY(-1px);
    }
    .filter-chip.active {
        box-shadow: 0 2px 8px rgba(59,130,246,.25);
    }

    /* ─────────────── Dark Mode ─────────────── */

    /* Page header */
    [data-theme="dark"] #pinjam-page h2 { color: #f1f5f9; }
    [data-theme="dark"] #pinjam-page p.text-gray-500 { color: #94a3b8; }

    /* Riwayat button */
    [data-theme="dark"] #pinjam-page a.bg-white {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    [data-theme="dark"] #pinjam-page a.bg-white:hover {
        background: #334155 !important;
    }

    /* Stat cards */
    [data-theme="dark"] .stat-mini {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.06) !important;
    }
    [data-theme="dark"] .stat-mini:hover {
        box-shadow: 0 8px 20px -6px rgba(0,0,0,.4) !important;
    }
    [data-theme="dark"] .stat-mini .text-gray-500 { color: #64748b !important; }
    [data-theme="dark"] .stat-mini .text-gray-900 { color: #f1f5f9 !important; }
    [data-theme="dark"] .stat-mini .bg-blue-50   { background-color: rgba(59,130,246,0.15) !important; }
    [data-theme="dark"] .stat-mini .bg-amber-50  { background-color: rgba(245,158,11,0.15) !important; }
    [data-theme="dark"] .stat-mini .bg-rose-50   { background-color: rgba(239,68,68,0.15) !important; }
    [data-theme="dark"] .stat-mini .bg-violet-50 { background-color: rgba(139,92,246,0.15) !important; }

    /* Table card */
    [data-theme="dark"] #pinjam-table-card {
        background: #1e293b !important;
        border-color: rgba(255,255,255,0.06) !important;
    }
    [data-theme="dark"] #pinjam-table-card .border-b { border-bottom-color: #334155 !important; }

    /* Search input */
    [data-theme="dark"] #searchInput {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] #searchInput::placeholder { color: #475569; }
    [data-theme="dark"] #searchInput:focus {
        background: #0f172a !important;
        border-color: #3b82f6 !important;
    }

    /* Filter chips – default state */
    [data-theme="dark"] .filter-chip {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    /* Active states */
    [data-theme="dark"] .filter-chip.active.bg-blue-50,
    [data-theme="dark"] .filter-chip.active.text-blue-700 {
        background: rgba(59,130,246,0.15) !important;
        border-color: rgba(59,130,246,0.4) !important;
        color: #93c5fd !important;
    }
    [data-theme="dark"] .filter-chip.active.bg-amber-50,
    [data-theme="dark"] .filter-chip.active.text-amber-700 {
        background: rgba(245,158,11,0.15) !important;
        border-color: rgba(245,158,11,0.4) !important;
        color: #fcd34d !important;
    }
    [data-theme="dark"] .filter-chip.active.bg-rose-50,
    [data-theme="dark"] .filter-chip.active.text-rose-700 {
        background: rgba(239,68,68,0.15) !important;
        border-color: rgba(239,68,68,0.4) !important;
        color: #fca5a5 !important;
    }

    /* Filter Lanjutan button */
    [data-theme="dark"] button[onclick="openFilterModal()"] {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    [data-theme="dark"] button[onclick="openFilterModal()"]:hover {
        background: #334155 !important;
    }

    /* Separator line */
    [data-theme="dark"] .w-px.h-6.bg-gray-200 { background-color: #334155 !important; }

    /* DataTables info / length */
    [data-theme="dark"] #peminjaman-table_wrapper .dataTables_length,
    [data-theme="dark"] #peminjaman-table_wrapper .dataTables_info { color: #64748b; }
    [data-theme="dark"] #peminjaman-table_wrapper .dataTables_length select {
        background-color: #0f172a !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }

    /* Table header */
    [data-theme="dark"] #peminjaman-table thead th {
        background: #0f172a !important;
        color: #64748b !important;
        border-bottom-color: #334155 !important;
    }
    /* Table body rows */
    [data-theme="dark"] #peminjaman-table tbody tr.odd  { background-color: #1e293b !important; }
    [data-theme="dark"] #peminjaman-table tbody tr.even { background-color: #1a2744 !important; }
    [data-theme="dark"] #peminjaman-table tbody td {
        border-bottom-color: #334155 !important;
        color: #e2e8f0;
    }
    [data-theme="dark"] #peminjaman-table tbody tr:hover {
        background-color: #243047 !important;
    }

    /* Pagination buttons */
    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #94a3b8 !important;
    }
    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    [data-theme="dark"] .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #334155 !important;
        border-color: #475569 !important;
        color: #f1f5f9 !important;
    }

    /* Filter Modal */
    [data-theme="dark"] #pinjam-modal-panel {
        background: #1e293b !important;
    }
    [data-theme="dark"] #pinjam-modal-panel .border-b,
    [data-theme="dark"] #pinjam-modal-panel .border-t { border-color: #334155 !important; }
    [data-theme="dark"] #pinjam-modal-panel .text-gray-900 { color: #f1f5f9 !important; }
    [data-theme="dark"] #pinjam-modal-panel .text-gray-500 { color: #94a3b8 !important; }
    [data-theme="dark"] #pinjam-modal-panel .text-gray-700 { color: #e2e8f0 !important; }
    [data-theme="dark"] #pinjam-modal-panel .text-gray-400 { color: #64748b !important; }
    [data-theme="dark"] #pinjam-modal-panel select,
    [data-theme="dark"] #pinjam-modal-panel input[type="date"] {
        background: #0f172a !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    [data-theme="dark"] #pinjam-modal-panel .bg-gray-100 {
        background-color: #334155 !important;
        color: #e2e8f0 !important;
    }
    [data-theme="dark"] #pinjam-modal-panel button.hover\:bg-gray-100:hover,
    [data-theme="dark"] #pinjam-modal-panel button.hover\:bg-gray-200:hover {
        background-color: #475569 !important;
    }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div id="pinjam-page" class="space-y-5">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 anim-up">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Peminjaman Buku</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data peminjaman buku perpustakaan</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="hidden lg:grid lg:grid-cols-4 gap-4">
        <div class="stat-mini bg-white rounded-xl border border-gray-100 p-4 anim-up d1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Aktif</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="countAktif">-</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <i class="fas fa-book-open text-blue-500"></i>
                </div>
            </div>
        </div>
        <div class="stat-mini bg-white rounded-xl border border-gray-100 p-4 anim-up d2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Dipinjam</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="countDipinjam">-</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <i class="fas fa-hourglass-half text-amber-500"></i>
                </div>
            </div>
        </div>
        <div class="stat-mini bg-white rounded-xl border border-gray-100 p-4 anim-up d3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Terlambat</p>
                    <p class="text-xl font-bold text-rose-600 mt-1" id="countTerlambat">-</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-rose-50 flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-rose-500"></i>
                </div>
            </div>
        </div>
        <div class="stat-mini bg-white rounded-xl border border-gray-100 p-4 anim-up d4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Buku</p>
                    <p class="text-xl font-bold text-gray-900 mt-1" id="countBuku">-</p>
                </div>
                <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center">
                    <i class="fas fa-books text-violet-500"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div id="pinjam-table-card" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden anim-up" style="animation-delay:.25s">
        <!-- Table -->
        <div>
            <table id="peminjaman-table" class="w-full">
                <thead>
                    <tr>
                        <th>No</th>
                        <!-- <th>Nomor Peminjaman</th> -->
                        <th>Anggota</th>
                        <th>Jumlah</th>
                        <th>Tgl. Pinjam</th>
                        <th>Batas Kembali</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>

        <!-- Bottom padding -->
        <div class="px-5 pb-2"></div>
    </div>
</div>

<!-- ===== Filter Modal ===== -->
<div id="filterModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop flex items-center justify-center min-h-screen p-4" id="filterBackdrop">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm" onclick="closeFilterModal()"></div>
        <!-- Panel -->
        <div id="pinjam-modal-panel" class="modal-panel relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Filter Lanjutan</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Saring data peminjaman</p>
                </div>
                <button onclick="closeFilterModal()" class="w-8 h-8 flex items-center justify-center rounded-lg hover:bg-gray-100 text-gray-400 hover:text-gray-600 transition-colors">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form id="filterForm" class="p-6">
                <div class="space-y-5">
                    <!-- Status -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
                        <select id="filter_status" class="w-full px-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all outline-none">
                            <option value="">Semua Status</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="terlambat">Terlambat</option>
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Rentang Tanggal</label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Dari</label>
                                <input type="date" id="filter_tanggal_dari"
                                       class="w-full px-3 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Sampai</label>
                                <input type="date" id="filter_tanggal_sampai"
                                       class="w-full px-3 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all outline-none">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-2.5 mt-6 pt-5 border-t border-gray-100">
                    <button type="button" onclick="resetFilters()"
                            class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                        <i class="fas fa-undo mr-1.5"></i>Reset
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 rounded-xl transition-all shadow-md shadow-blue-500/25">
                        <i class="fas fa-check mr-1.5"></i>Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- jQuery + DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
let peminjamanTable;

$(document).ready(function() {
    // Initialize DataTable
    peminjamanTable = $('#peminjaman-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '/admin/peminjaman',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tanggal_dari = $('#filter_tanggal_dari').val();
                d.filter_tanggal_sampai = $('#filter_tanggal_sampai').val();
            },
            dataSrc: function(json) {
                // Update summary cards from custom data
                if (json.summary) {
                    animateCount('countAktif', json.summary.total_aktif || 0);
                    animateCount('countDipinjam', json.summary.dipinjam || 0);
                    animateCount('countTerlambat', json.summary.terlambat || 0);
                    animateCount('countBuku', json.summary.total_buku || 0);
                }
                return json.data;
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-3 text-center text-gray-400 font-medium' },
            // { data: 'nomor_badge', name: 'nomor_peminjaman', className: 'px-4 py-3' },
            { data: 'anggota_info', name: 'anggota.nama_lengkap', className: 'px-4 py-3' },
            { data: 'jumlah_badge', name: 'jumlah_buku', className: 'px-4 py-3 text-center' },
            { data: 'tanggal_pinjam_info', name: 'tanggal_peminjaman', className: 'px-4 py-3' },
            { data: 'batas_kembali_info', name: 'tanggal_harus_kembali', className: 'px-4 py-3' },
            { data: 'status_badge', name: 'status', className: 'px-4 py-3' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-3 text-center' }
        ],
        language: {
            processing: '<div class="flex items-center justify-center py-6"><div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent"></div><span class="ml-3 text-sm text-gray-500">Memuat data...</span></div>',
            lengthMenu: '_MENU_',
            search: '',
            searchPlaceholder: 'Cari...',
            zeroRecords: '<div class="text-center py-12"><div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3"><i class="fas fa-inbox text-gray-400 text-xl"></i></div><p class="text-gray-500 text-sm font-medium">Tidak ada data peminjaman</p><p class="text-gray-400 text-xs mt-1">Coba ubah filter atau kata kunci pencarian</p></div>',
            info: "Menampilkan _START_-_END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(filter dari _MAX_ total)",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        scrollX: true,
        autoWidth: false,
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[4, 'desc']],
        dom: '<"flex flex-row items-center justify-between gap-2 mb-2 dt-toolbar"<"dt-length-wrap"l><"flex flex-row items-center gap-2 dt-actions"<"dt-search-wrap"f><"dt-buttons-wrap">>><"dt-table-scroll"t><"flex flex-row items-center justify-between gap-2 mt-2 dt-bottom"<"text-xs text-gray-400 dt-info"i><"dt-pager"p>>',
        initComplete: function() {
            var btnWrap = $('.dt-buttons-wrap');

            btnWrap.append('<button onclick="openFilterModal()" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all"><i class="fas fa-sliders-h"></i><span class="btn-text">Filter</span></button>');

            @if(Auth::user()->hasPermission('riwayat-transaksi.view') || Auth::user()->isAdmin())
            btnWrap.append('<a href="{{ route('riwayat-peminjaman.index') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all"><i class="fas fa-history text-gray-400"></i><span class="btn-text">Riwayat</span></a>');
            @endif

            @if(Auth::user()->hasPermission('peminjaman.create') || Auth::user()->isAdmin())
            btnWrap.append('<a href="{{ route('peminjaman.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-sm hover:from-blue-700 hover:to-indigo-700 transition-all"><i class="fas fa-plus"></i><span class="btn-text">Tambah</span></a>');
            @endif
        },
        drawCallback: function() {
            // Re-apply tooltips or animations after draw if needed
        }
    });

    // Load summary on init
    loadSummary();
});

// ===== Summary Cards =====
function loadSummary() {
    $.ajax({
        url: '/admin/peminjaman',
        data: { ajax_summary: 1 },
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(res) {
            if (res.summary) {
                animateCount('countAktif', res.summary.total_aktif || 0);
                animateCount('countDipinjam', res.summary.dipinjam || 0);
                animateCount('countTerlambat', res.summary.terlambat || 0);
                animateCount('countBuku', res.summary.total_buku || 0);
            }
        }
    });
}

function animateCount(elId, target) {
    const el = document.getElementById(elId);
    if (!el) return;
    const current = parseInt(el.textContent) || 0;
    if (current === target) { el.textContent = target; return; }
    const duration = 600;
    const start = performance.now();
    function step(now) {
        const progress = Math.min((now - start) / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        el.textContent = Math.round(current + (target - current) * eased);
        if (progress < 1) requestAnimationFrame(step);
    }
    requestAnimationFrame(step);
}

// ===== Filter Modal =====
function openFilterModal() {
    const modal = document.getElementById('filterModal');
    const backdrop = document.getElementById('filterBackdrop');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => backdrop.classList.add('active'));
}

function closeFilterModal() {
    const backdrop = document.getElementById('filterBackdrop');
    backdrop.classList.remove('active');
    setTimeout(() => document.getElementById('filterModal').classList.add('hidden'), 250);
}

function resetFilters() {
    $('#filter_status').val('');
    $('#filter_tanggal_dari').val('');
    $('#filter_tanggal_sampai').val('');
    peminjamanTable.draw();
    closeFilterModal();
}

document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    peminjamanTable.draw();
    closeFilterModal();
});

document.getElementById('filterModal').addEventListener('click', function(e) {
    if (e.target === this) closeFilterModal();
});

// ===== Delete =====
function confirmDelete(peminjamanId) {
    Swal.fire({
        title: 'Hapus Peminjaman?',
        text: 'Data peminjaman akan dihapus dan stok buku dikembalikan. Tindakan ini tidak dapat dibatalkan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl',
            cancelButton: 'rounded-xl'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/peminjaman/' + peminjamanId;
            const csrf = document.createElement('input');
            csrf.type = 'hidden'; csrf.name = '_token';
            csrf.value = document.querySelector('meta[name="csrf-token"]').content;
            const method = document.createElement('input');
            method.type = 'hidden'; method.name = '_method'; method.value = 'DELETE';
            form.appendChild(csrf);
            form.appendChild(method);
            document.body.appendChild(form);
            form.submit();
        }
    });
}
</script>
@endsection
