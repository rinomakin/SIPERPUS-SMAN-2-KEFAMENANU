@extends('layouts.admin')

@section('title', 'Riwayat Peminjaman')
@section('page-title', 'Riwayat Peminjaman')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(0.95); }
        to   { opacity: 1; transform: scale(1); }
    }
    @keyframes pulse-dot {
        0%, 100% { transform: scale(1); opacity: 1; }
        50%       { transform: scale(1.8); opacity: 0.4; }
    }
    .anim-up { animation: fadeInUp .5s ease both; }
    .anim-up.d1 { animation-delay: .05s; }
    .anim-up.d2 { animation-delay: .1s; }
    .anim-up.d3 { animation-delay: .15s; }
    .anim-up.d4 { animation-delay: .2s; }
    .anim-up.d5 { animation-delay: .25s; }

    /* Stat Cards */
    .stat-card {
        background: white; border-radius: 16px; padding: 20px;
        border: 1px solid #f1f5f9; transition: all .3s ease;
        position: relative; overflow: hidden;
    }
    .stat-card::before {
        content: ''; position: absolute; top: 0; left: 0; right: 0;
        height: 3px; border-radius: 16px 16px 0 0;
    }
    .stat-card:hover { transform: translateY(-4px); box-shadow: 0 12px 24px -8px rgba(0,0,0,.1); }
    .stat-card.blue::before   { background: linear-gradient(90deg, #3b82f6, #60a5fa); }
    .stat-card.amber::before  { background: linear-gradient(90deg, #f59e0b, #fbbf24); }
    .stat-card.rose::before   { background: linear-gradient(90deg, #f43f5e, #fb7185); }
    .stat-card.indigo::before { background: linear-gradient(90deg, #6366f1, #818cf8); }
    .stat-icon {
        width: 44px; height: 44px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px; color: white;
    }
    .stat-icon.blue   { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .stat-icon.amber  { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .stat-icon.rose   { background: linear-gradient(135deg, #f43f5e, #e11d48); }
    .stat-icon.indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }

    /* DataTables custom */
    #riwayat-table_wrapper .dataTables_filter { display: none; }
    #riwayat-table_wrapper .dataTables_length select {
        padding: 6px 28px 6px 12px; border-radius: 8px;
        border: 1px solid #e2e8f0; font-size: 13px; background-color: #f8fafc;
    }
    #riwayat-table thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        font-size: 11px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .05em; color: #64748b; padding: 14px 16px;
        border-bottom: 2px solid #e2e8f0;
    }
    #riwayat-table tbody td {
        padding: 14px 16px; font-size: 13px;
        vertical-align: middle; border-bottom: 1px solid #f1f5f9;
    }
    #riwayat-table tbody tr { transition: background .15s ease; }
    #riwayat-table tbody tr:hover { background: #eff6ff !important; }
    #riwayat-table tbody tr.selected-row { background: #dbeafe !important; }

    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 6px 12px !important; margin: 0 2px !important;
        border-radius: 8px !important; border: 1px solid #e2e8f0 !important;
        font-size: 13px !important; transition: all .2s ease !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: white !important; border-color: transparent !important;
        box-shadow: 0 2px 8px -2px rgba(59,130,246,.4) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #f1f5f9 !important; border-color: #cbd5e1 !important; color: #1e293b !important;
    }
    .dataTables_wrapper .dataTables_info { font-size: 13px; color: #64748b; padding-top: 12px; }
    .dataTables_wrapper .dataTables_length { padding-top: 12px; font-size: 13px; color: #64748b; }

    /* Action buttons */
    .action-btn {
        width: 32px; height: 32px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px; font-size: 13px; transition: all .2s ease; color: white;
    }
    .action-btn:hover { transform: translateY(-2px); }
    .action-btn.view   { background: linear-gradient(135deg, #3b82f6, #2563eb); }
    .action-btn.view:hover  { box-shadow: 0 4px 12px -2px rgba(59,130,246,.5); }
    .action-btn.delete { background: linear-gradient(135deg, #f43f5e, #e11d48); }
    .action-btn.delete:hover { box-shadow: 0 4px 12px -2px rgba(244,63,94,.5); }

    /* Badges */
    .badge-status {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 4px 12px; border-radius: 20px; font-size: 11px;
        font-weight: 600; border: 1px solid;
    }
    .badge-dipinjam    { background: #fffbeb; color: #d97706; border-color: #fde68a; }
    .badge-dikembalikan{ background: #ecfdf5; color: #059669; border-color: #a7f3d0; }
    .badge-terlambat   { background: #fef2f2; color: #dc2626; border-color: #fecaca; }
    .badge-other       { background: #f8fafc; color: #64748b; border-color: #e2e8f0; }
    .badge-dot { width: 6px; height: 6px; border-radius: 50%; display: inline-block; }
    .badge-dot.yellow { background: #f59e0b; }
    .badge-dot.green  { background: #10b981; }
    .badge-dot.red    { background: #ef4444; animation: pulse-dot 2s infinite; }
    .badge-dot.gray   { background: #94a3b8; }

    .nomor-badge {
        display: inline-flex; align-items: center; gap: 4px;
        background: #eff6ff; color: #2563eb; padding: 3px 10px;
        border-radius: 6px; font-size: 11px; font-weight: 600;
        border: 1px solid #bfdbfe;
    }

    /* Checkbox */
    .row-checkbox, #checkAll {
        width: 16px; height: 16px; accent-color: #3b82f6; cursor: pointer;
    }

    /* Bulk bar */
    #bulkBar {
        display: none; align-items: center; gap: 10px;
        padding: 10px 16px;
        background: linear-gradient(135deg, #fef2f2, #fff1f2);
        border: 1px solid #fecdd3; border-radius: 12px;
        animation: fadeInUp .25s ease both;
    }
    #bulkBar.show { display: flex; }

    /* Toolbar btn */
    .toolbar-btn {
        display: inline-flex; align-items: center;
        padding: 8px 14px; font-size: 0.75rem; font-weight: 500;
        border-radius: 10px; transition: all 0.2s; gap: 6px; white-space: nowrap;
    }
    .toolbar-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }

    /* Quick filter chips */
    .filter-chip {
        padding: 6px 16px; border-radius: 20px; font-size: 12px; font-weight: 600;
        cursor: pointer; transition: all .25s ease; border: 1.5px solid #e2e8f0;
        background: white; color: #64748b; white-space: nowrap;
    }
    .filter-chip:hover { border-color: #94a3b8; color: #334155; }
    .filter-chip.active-all      { background: linear-gradient(135deg, #3b82f6, #2563eb); color: white; border-color: transparent; box-shadow: 0 4px 12px -2px rgba(59,130,246,.4); }
    .filter-chip.active-dipinjam { background: linear-gradient(135deg, #f59e0b, #d97706); color: white; border-color: transparent; box-shadow: 0 4px 12px -2px rgba(245,158,11,.4); }
    .filter-chip.active-kembali  { background: linear-gradient(135deg, #10b981, #059669); color: white; border-color: transparent; box-shadow: 0 4px 12px -2px rgba(16,185,129,.4); }
    .filter-chip.active-terlambat{ background: linear-gradient(135deg, #f43f5e, #e11d48); color: white; border-color: transparent; box-shadow: 0 4px 12px -2px rgba(244,63,94,.4); }

    /* Modal */
    .modal-backdrop { backdrop-filter: blur(4px); background: rgba(15,23,42,.45); }
    .modal-content  { animation: scaleIn .3s ease both; }
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="space-y-5">

    {{-- Flash message --}}
    @if(session('success'))
    <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 font-medium anim-up d1" id="flashMsg">
        <i class="fas fa-check-circle text-blue-500"></i>
        {{ session('success') }}
        <button onclick="this.parentElement.remove()" class="ml-auto text-blue-400 hover:text-blue-600"><i class="fas fa-times"></i></button>
    </div>
    @endif

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="stat-card blue anim-up d1">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Total Peminjaman</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-total">{{ number_format($summary['total']) }}</p>
                </div>
                <div class="stat-icon blue"><i class="fas fa-book-open"></i></div>
            </div>
        </div>
        <div class="stat-card amber anim-up d2">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Sedang Dipinjam</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-dipinjam">{{ number_format($summary['dipinjam']) }}</p>
                </div>
                <div class="stat-icon amber"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="stat-card rose anim-up d3">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Terlambat</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-terlambat">{{ number_format($summary['terlambat']) }}</p>
                </div>
                <div class="stat-icon rose"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        <div class="stat-card indigo anim-up d4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-gray-500 mb-1">Hari Ini</p>
                    <p class="text-2xl font-bold text-gray-900" id="stat-hariini">{{ number_format($summary['hari_ini']) }}</p>
                </div>
                <div class="stat-icon indigo"><i class="fas fa-calendar-day"></i></div>
            </div>
        </div>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-100 overflow-hidden anim-up d5">

        <!-- Card Header -->
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h3 class="text-lg font-bold text-white flex items-center gap-2">
                        <i class="fas fa-history"></i> Riwayat Peminjaman
                    </h3>
                    <p class="text-blue-100 text-xs mt-1">Seluruh data riwayat peminjaman buku</p>
                </div>
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full lg:w-auto">
                    <!-- Search -->
                    <div class="relative w-full sm:w-auto">
                        <input type="text" id="searchInput" placeholder="Cari nama, nomor peminjaman..."
                               class="w-full sm:w-72 px-4 py-2.5 pl-10 text-sm bg-white/15 backdrop-blur-sm text-white placeholder-blue-200 rounded-xl border border-white/20 focus:bg-white/25 focus:ring-2 focus:ring-white/30 focus:outline-none transition-all duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-blue-200"></i>
                        </div>
                    </div>
                    <!-- Actions -->
                    <div class="flex flex-wrap gap-2">
                        <button onclick="openFilterModal()"
                                class="bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white px-4 py-2.5 rounded-xl font-semibold text-xs border border-white/20 transition-all duration-200">
                            <i class="fas fa-sliders-h mr-1.5"></i>Filter
                        </button>
                        <a href="{{ route('riwayat-peminjaman.export') }}"
                           class="bg-white/15 hover:bg-white/25 backdrop-blur-sm text-white px-4 py-2.5 rounded-xl font-semibold text-xs border border-white/20 transition-all duration-200">
                            <i class="fas fa-file-csv mr-1.5"></i>Export CSV
                        </a>
                        <a href="{{ route('peminjaman.index') }}"
                           class="bg-white hover:bg-blue-50 text-blue-700 px-4 py-2.5 rounded-xl font-semibold text-xs transition-all duration-200 shadow-lg shadow-white/20">
                            <i class="fas fa-arrow-left mr-1.5"></i>Kembali
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="px-6 py-3 border-b border-gray-100 bg-gray-50/50 flex items-center gap-2 overflow-x-auto">
            <span class="text-xs text-gray-400 font-medium mr-1 whitespace-nowrap">Filter Cepat:</span>
            <button class="filter-chip active-all" onclick="setQuickFilter('all', this)" data-filter="all">
                <i class="fas fa-layer-group mr-1"></i>Semua
            </button>
            <button class="filter-chip" onclick="setQuickFilter('dipinjam', this)" data-filter="dipinjam">
                <i class="fas fa-hourglass-half mr-1"></i>Dipinjam
            </button>
            <button class="filter-chip" onclick="setQuickFilter('dikembalikan', this)" data-filter="dikembalikan">
                <i class="fas fa-check-circle mr-1"></i>Dikembalikan
            </button>
            <button class="filter-chip" onclick="setQuickFilter('terlambat', this)" data-filter="terlambat">
                <i class="fas fa-exclamation-circle mr-1"></i>Terlambat
            </button>
        </div>

        @if($canDelete)
        <div id="bulkBar" class="mx-6 mt-4">
            <i class="fas fa-check-square text-rose-500"></i>
            <span class="text-sm font-semibold text-rose-700"><span id="selectedCount">0</span> data dipilih</span>
            <button onclick="confirmBulkDelete()"
                    class="toolbar-btn bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-md">
                <i class="fas fa-trash-alt"></i><span>Hapus Terpilih</span>
            </button>
            <button onclick="clearSelection()" class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200">
                <i class="fas fa-times"></i><span>Batal</span>
            </button>
        </div>
        @endif

        <!-- Table -->
        <div class="p-6">
            <div class="overflow-x-auto">
                <table id="riwayat-table" class="min-w-full" style="min-width:950px;">
                    <thead>
                        <tr>
                            <th class="text-center w-10">
                                @if($canDelete)<input type="checkbox" id="checkAll" title="Pilih semua di halaman ini">@endif
                            </th>
                            <th class="text-center w-12">No</th>
                            <!-- <th class="text-left">No. Peminjaman</th> -->
                            <th class="text-left">Anggota</th>
                            <th class="text-center">Buku</th>
                            <th class="text-left">Tgl Pinjam</th>
                            <th class="text-left">Batas Kembali</th>
                            <th class="text-center">Status</th>
                            <!-- <th class="text-left">Petugas</th> -->
                            <th class="text-center w-24">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ─── Modals ─────────────────────────────────────────────────────── --}}

<!-- Filter Modal -->
<div id="filterModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop absolute inset-0" onclick="closeFilterModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4 relative z-10">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-lg w-full overflow-hidden">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                            <i class="fas fa-sliders-h text-white"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Filter Riwayat</h3>
                    </div>
                    <button onclick="closeFilterModal()" class="w-8 h-8 bg-white/15 hover:bg-white/25 rounded-lg flex items-center justify-center text-white transition-all">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <form id="filterForm" class="p-6 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <!-- Status -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Status</label>
                        <select id="filter_status" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Status</option>
                            <option value="dipinjam">Dipinjam</option>
                            <option value="dikembalikan">Dikembalikan</option>
                            <option value="terlambat">Terlambat</option>
                        </select>
                    </div>
                    <!-- Anggota -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Anggota</label>
                        <select id="filter_anggota" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Anggota</option>
                            @foreach($anggota as $member)
                            <option value="{{ $member->id }}">{{ $member->nama_lengkap }} — {{ $member->nomor_anggota }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Buku -->
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Buku</label>
                        <select id="filter_buku" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Buku</option>
                            @foreach($buku as $book)
                            <option value="{{ $book->id }}">{{ $book->judul_buku }}</option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Tanggal Dari -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Tanggal Dari</label>
                        <input type="date" id="filter_tanggal_mulai"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                    <!-- Tanggal Sampai -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Tanggal Sampai</label>
                        <input type="date" id="filter_tanggal_akhir"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" onclick="resetFilters()" class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200">
                        <i class="fas fa-undo"></i><span>Reset</span>
                    </button>
                    <button type="submit" class="toolbar-btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md">
                        <i class="fas fa-check"></i><span>Terapkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Satu -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop absolute inset-0" onclick="closeDeleteModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4 relative z-10">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-sm w-full">
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <i class="fas fa-trash-alt text-2xl text-red-500"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Riwayat Peminjaman</h3>
                <p class="text-sm text-gray-500 mb-1">Anda akan menghapus data:</p>
                <p class="text-sm font-semibold text-gray-800 mb-3" id="deleteNomor"></p>
                <p class="text-xs text-red-500 font-medium mb-6">Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex items-center justify-center gap-3">
                    <button onclick="closeDeleteModal()" class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-5">
                        <i class="fas fa-times"></i><span>Batal</span>
                    </button>
                    <button onclick="submitDelete()" class="toolbar-btn bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-md px-5">
                        <i class="fas fa-trash-alt"></i><span>Ya, Hapus</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Banyak -->
<div id="bulkDeleteModal" class="fixed inset-0 z-50 hidden">
    <div class="modal-backdrop absolute inset-0" onclick="closeBulkDeleteModal()"></div>
    <div class="flex items-center justify-center min-h-screen p-4 relative z-10">
        <div class="modal-content bg-white rounded-2xl shadow-2xl max-w-sm w-full">
            <div class="p-6 text-center">
                <div class="mx-auto w-16 h-16 rounded-full bg-red-100 flex items-center justify-center mb-4">
                    <i class="fas fa-trash-alt text-2xl text-red-500"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Hapus Data Terpilih</h3>
                <p class="text-sm text-gray-500 mb-1">Anda akan menghapus</p>
                <p class="text-3xl font-bold text-red-600 my-2" id="bulkDeleteCount"></p>
                <p class="text-sm text-gray-500 mb-3">data riwayat peminjaman.</p>
                <p class="text-xs text-red-500 font-medium mb-6">Tindakan ini tidak dapat dibatalkan.</p>
                <div class="flex items-center justify-center gap-3">
                    <button onclick="closeBulkDeleteModal()" class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200 px-5">
                        <i class="fas fa-times"></i><span>Batal</span>
                    </button>
                    <button onclick="submitBulkDelete()" class="toolbar-btn bg-gradient-to-r from-rose-500 to-red-600 text-white shadow-md px-5">
                        <i class="fas fa-trash-alt"></i><span>Ya, Hapus Semua</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 flex items-center justify-center z-[60] hidden" style="background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl p-6 flex flex-col items-center gap-3 shadow-2xl">
        <div class="w-12 h-12 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
        <span class="text-sm font-medium text-gray-700">Memproses...</span>
    </div>
</div>

<!-- jQuery & DataTables -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
let riwayatTable;
let currentQuickFilter = 'all';
let pendingDeleteId    = null;
const CSRF_TOKEN  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
const DESTROY_URL = '/admin/riwayat-peminjaman/__ID__';
const BULK_URL    = '/admin/riwayat-peminjaman/bulk';

// ─── DataTables Init ───────────────────────────────────────────────────────
$(document).ready(function () {

    riwayatTable = $('#riwayat-table').DataTable({
        processing : true,
        serverSide : true,
        ajax: {
            url : '/admin/riwayat-peminjaman/data',
            type: 'GET',
            data: function (d) {
                d.filter_status    = currentQuickFilter !== 'all' ? currentQuickFilter : $('#filter_status').val();
                d.filter_anggota   = $('#filter_anggota').val();
                d.filter_buku      = $('#filter_buku').val();
                d.tanggal_mulai    = $('#filter_tanggal_mulai').val();
                d.tanggal_akhir    = $('#filter_tanggal_akhir').val();
                d.search_keyword   = $('#searchInput').val();
            },
            dataSrc: function (json) {
                if (json && json.summary) updateSummaryCards(json.summary);
                clearSelection();
                return json.data || [];
            }
        },
        columns: [
            { data: 'checkbox',      name: 'checkbox',           orderable: false, searchable: false, className: 'text-center', width: '40px' },
            { data: 'DT_RowIndex',   name: 'DT_RowIndex',        orderable: false, searchable: false, className: 'text-center', width: '45px' },
            // { data: 'nomor_badge',   name: 'nomor_peminjaman',   orderable: false, searchable: false },
            { data: 'anggota_info',  name: 'anggota_id',         orderable: false, searchable: false },
            { data: 'jumlah_badge',  name: 'jumlah_badge',       orderable: false, searchable: false, className: 'text-center' },
            { data: 'tanggal_info',  name: 'tanggal_peminjaman', orderable: false, searchable: false },
            { data: 'batas_kembali', name: 'batas_kembali',      orderable: false, searchable: false },
            { data: 'status_badge',  name: 'status',             orderable: false, searchable: false, className: 'text-center' },
            // { data: 'petugas',       name: 'petugas',            orderable: false, searchable: false },
            { data: 'action',        name: 'action',             orderable: false, searchable: false, className: 'text-center', width: '96px' },
        ],
        language: {
            processing  : '<div class="flex items-center justify-center py-8"><div class="animate-spin rounded-full h-8 w-8 border-[3px] border-blue-200 border-t-blue-500"></div><span class="ml-3 text-gray-500 text-sm">Memuat data...</span></div>',
            lengthMenu  : 'Tampilkan _MENU_ data',
            zeroRecords : '<div class="text-center py-12"><div class="mx-auto w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center mb-4"><i class="fas fa-history text-2xl text-blue-300"></i></div><h3 class="text-sm font-semibold text-gray-900 mb-1">Tidak ada riwayat</h3><p class="text-gray-400 text-xs">Belum ada data yang sesuai filter</p></div>',
            info        : 'Menampilkan _START_ - _END_ dari _TOTAL_ data',
            infoEmpty   : 'Tidak ada data',
            infoFiltered: '(filter dari _MAX_ total)',
            paginate: {
                first   : '<i class="fas fa-angle-double-left"></i>',
                last    : '<i class="fas fa-angle-double-right"></i>',
                next    : '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>',
            },
        },
        pageLength: 15,
        lengthMenu: [[10, 15, 25, 50, 100], [10, 15, 25, 50, 100]],
        order     : [],
        drawCallback: function () {
            bindCheckboxes();
            $('#riwayat-table tbody tr').each(function (i) {
                $(this).css({ animation: 'fadeInUp .3s ease both', 'animation-delay': (i * 0.025) + 's' });
            });
        }
    });

    // Search debounce
    let searchTimeout;
    $('#searchInput').on('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => riwayatTable.draw(), 400);
    });
});

// ─── Checkbox Logic ────────────────────────────────────────────────────────
function bindCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    if (checkAll) {
        checkAll.checked       = false;
        checkAll.indeterminate = false;
    }

    $(document).off('change', '.row-checkbox').on('change', '.row-checkbox', function () {
        updateBulkBar();
        const boxes   = document.querySelectorAll('.row-checkbox');
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checkAll) {
            checkAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
            checkAll.checked       = checked.length === boxes.length && boxes.length > 0;
        }
        $(this).closest('tr').toggleClass('selected-row', this.checked);
    });

    if (checkAll) {
        $(checkAll).off('change').on('change', function () {
            document.querySelectorAll('.row-checkbox').forEach(cb => {
                cb.checked = this.checked;
                $(cb).closest('tr').toggleClass('selected-row', this.checked);
            });
            updateBulkBar();
        });
    }
}

function updateBulkBar() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.textContent = checked.length;
    const bar = document.getElementById('bulkBar');
    if (bar) checked.length > 0 ? bar.classList.add('show') : bar.classList.remove('show');
}

function clearSelection() {
    document.querySelectorAll('.row-checkbox').forEach(cb => {
        cb.checked = false;
        $(cb).closest('tr').removeClass('selected-row');
    });
    const checkAll = document.getElementById('checkAll');
    if (checkAll) { checkAll.checked = false; checkAll.indeterminate = false; }
    const bulkBar = document.getElementById('bulkBar');
    if (bulkBar) bulkBar.classList.remove('show');
    const selectedCount = document.getElementById('selectedCount');
    if (selectedCount) selectedCount.textContent = '0';
}

// ─── Quick Filter ──────────────────────────────────────────────────────────
function setQuickFilter(filter, btn) {
    currentQuickFilter = filter;
    document.querySelectorAll('.filter-chip').forEach(c => { c.className = 'filter-chip'; });
    const classMap = { all: 'active-all', dipinjam: 'active-dipinjam', dikembalikan: 'active-kembali', terlambat: 'active-terlambat' };
    btn.classList.add(classMap[filter] || 'active-all');
    $('#filter_status').val('');
    riwayatTable.draw();
}

// ─── Filter Modal ──────────────────────────────────────────────────────────
function openFilterModal() {
    document.getElementById('filterModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeFilterModal() {
    document.getElementById('filterModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function resetFilters() {
    $('#filter_status, #filter_anggota, #filter_buku, #filter_tanggal_mulai, #filter_tanggal_akhir').val('');
    currentQuickFilter = 'all';
    document.querySelectorAll('.filter-chip').forEach(c => c.className = 'filter-chip');
    document.querySelector('[data-filter="all"]').classList.add('active-all');
    riwayatTable.draw();
    closeFilterModal();
}
document.getElementById('filterForm').addEventListener('submit', function (e) {
    e.preventDefault();
    currentQuickFilter = 'all';
    document.querySelectorAll('.filter-chip').forEach(c => c.className = 'filter-chip');
    document.querySelector('[data-filter="all"]').classList.add('active-all');
    riwayatTable.draw();
    closeFilterModal();
});

// ─── Summary Cards ─────────────────────────────────────────────────────────
function updateSummaryCards(s) {
    setElText('stat-total',    s.total     ? s.total.toLocaleString('id-ID')    : '0');
    setElText('stat-dipinjam', s.dipinjam  ? s.dipinjam.toLocaleString('id-ID') : '0');
    setElText('stat-terlambat',s.terlambat ? s.terlambat.toLocaleString('id-ID'): '0');
    setElText('stat-hariini',  s.hari_ini  ? s.hari_ini.toLocaleString('id-ID') : '0');
}
function setElText(id, val) {
    const el = document.getElementById(id);
    if (el) el.textContent = val;
}

// ─── Delete Single ─────────────────────────────────────────────────────────
function confirmDelete(id, nomor) {
    pendingDeleteId = id;
    document.getElementById('deleteNomor').textContent = nomor;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
    pendingDeleteId = null;
}
function submitDelete() {
    if (!pendingDeleteId) return;
    document.getElementById('loadingOverlay').classList.remove('hidden');

    fetch(DESTROY_URL.replace('__ID__', pendingDeleteId), {
        method : 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        closeDeleteModal();
        if (data.success) { showFlash(data.message); riwayatTable.draw(); }
    })
    .catch(() => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        alert('Terjadi kesalahan, coba lagi.');
    });
}

// ─── Delete Bulk ───────────────────────────────────────────────────────────
function confirmBulkDelete() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    if (!checked.length) return;
    document.getElementById('bulkDeleteCount').textContent = checked.length;
    document.getElementById('bulkDeleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeBulkDeleteModal() {
    document.getElementById('bulkDeleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function submitBulkDelete() {
    const checked = document.querySelectorAll('.row-checkbox:checked');
    const ids = Array.from(checked).map(cb => cb.value);
    if (!ids.length) return;

    document.getElementById('loadingOverlay').classList.remove('hidden');

    const formData = new FormData();
    formData.append('_method', 'DELETE');
    formData.append('_token', CSRF_TOKEN);
    ids.forEach(id => formData.append('ids[]', id));

    fetch(BULK_URL, { method: 'POST', body: formData, headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        closeBulkDeleteModal();
        if (data.success) { showFlash(data.message); riwayatTable.draw(); }
    })
    .catch(() => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        alert('Terjadi kesalahan, coba lagi.');
    });
}

// ─── Flash message ─────────────────────────────────────────────────────────
function showFlash(msg) {
    const existing = document.getElementById('flashMsg');
    if (existing) existing.remove();
    const div = document.createElement('div');
    div.id = 'flashMsg';
    div.className = 'flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-sm text-blue-800 font-medium';
    div.innerHTML = '<i class="fas fa-check-circle text-blue-500"></i>'
                  + msg
                  + '<button onclick="this.parentElement.remove()" class="ml-auto text-blue-400 hover:text-blue-600"><i class="fas fa-times"></i></button>';
    document.querySelector('.space-y-5').prepend(div);
    setTimeout(() => div.remove(), 5000);
}

// ─── Keyboard ──────────────────────────────────────────────────────────────
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeFilterModal(); closeDeleteModal(); closeBulkDeleteModal(); }
});
</script>
@endsection
