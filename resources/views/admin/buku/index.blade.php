@extends('layouts.admin')

@section('title', 'Data Buku')
@section('page-title', 'Data Buku')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    /* Stat card animations */
    .stat-card {
        opacity: 0;
        transform: translateY(20px);
        animation: slideUp 0.5s ease forwards;
    }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.1s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.2s; }

    @keyframes slideUp {
        to { opacity: 1; transform: translateY(0); }
    }

    /* Glass card */
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }

    /* Custom DataTables styling */
    #buku-table_wrapper .dataTables_length,
    #buku-table_wrapper .dataTables_info,
    #buku-table_wrapper .dataTables_paginate {
        padding: 12px 16px;
        font-size: 0.875rem;
        color: #6b7280;
    }
    #buku-table_wrapper .dataTables_filter {
        display: none;
    }
    #buku-table_wrapper .dataTables_length select {
        padding: 6px 32px 6px 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background-color: #f9fafb;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    #buku-table_wrapper .dataTables_length select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        outline: none;
    }
    #buku-table thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }
    #buku-table tbody tr {
        transition: all 0.15s ease;
    }
    #buku-table tbody tr:hover {
        background-color: #f0f9ff !important;
    }
    #buku-table tbody tr.selected-row {
        background-color: #eff6ff !important;
        border-left: 3px solid #3b82f6;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 6px 12px !important;
        margin: 0 2px !important;
        border-radius: 8px !important;
        border: 1px solid #e5e7eb !important;
        font-size: 0.8rem !important;
        transition: all 0.2s !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #3b82f6, #2563eb) !important;
        color: white !important;
        border: none !important;
        box-shadow: 0 2px 8px rgba(59,130,246,0.3) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #f1f5f9 !important;
        border-color: #cbd5e1 !important;
        color: #1e293b !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.disabled {
        opacity: 0.4 !important;
        cursor: not-allowed !important;
    }

    /* Action button styles */
    .action-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        transition: all 0.2s;
        font-size: 0.8rem;
    }
    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.12);
    }
    .action-btn-view { background: #eff6ff; color: #3b82f6; }
    .action-btn-view:hover { background: #3b82f6; color: white; }
    .action-btn-edit { background: #fefce8; color: #ca8a04; }
    .action-btn-edit:hover { background: #ca8a04; color: white; }
    .action-btn-print { background: #f0fdf4; color: #16a34a; }
    .action-btn-print:hover { background: #16a34a; color: white; }
    .action-btn-delete { background: #fef2f2; color: #ef4444; }
    .action-btn-delete:hover { background: #ef4444; color: white; }

    /* Cover styles */
    .cover-container {
        width: 44px;
        height: 60px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    .cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .cover-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    /* Toolbar button */
    .toolbar-btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 14px;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.2s;
        gap: 6px;
        white-space: nowrap;
    }
    .toolbar-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    /* Bulk action bar */
    .bulk-bar {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        max-height: 0;
        opacity: 0;
        overflow: hidden;
    }
    .bulk-bar.active {
        max-height: 60px;
        opacity: 1;
    }

    /* Filter chip */
    .filter-chip {
        display: inline-flex;
        align-items: center;
        padding: 4px 12px;
        font-size: 0.7rem;
        font-weight: 500;
        border-radius: 9999px;
        background: #eff6ff;
        color: #3b82f6;
        gap: 4px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .filter-chip:hover {
        background: #dbeafe;
    }
    .filter-chip .remove {
        width: 14px;
        height: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(59,130,246,0.2);
        font-size: 0.6rem;
    }

    /* Mobile responsive */
    @media (max-width: 768px) {
        .toolbar-btn span.btn-text {
            display: none;
        }
        .toolbar-btn {
            padding: 8px 10px;
        }
        #searchInput {
            width: 100% !important;
        }
    }
</style>

<div class="space-y-5">
    <!-- Header Toolbar -->
    <div class="glass-card rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col gap-3">
            <!-- Top row: Actions & Search -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <!-- Left: Action buttons -->
                <div class="flex items-center flex-wrap gap-2">
                    @if(Auth::user()->hasPermission('buku.create') || Auth::user()->isAdmin())
                    <a href="{{ route('buku.create') }}" class="toolbar-btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md">
                        <i class="fas fa-plus"></i>
                        <span class="btn-text">Tambah Buku</span>
                    </a>
                    @endif

                    <!-- @if(Auth::user()->hasPermission('buku.export') || Auth::user()->isAdmin())
                    <a href="{{ route('buku.export', request()->query()) }}" class="toolbar-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-file-excel text-emerald-500"></i>
                        <span class="btn-text">Export</span>
                    </a>
                    @endif -->

                    @if(Auth::user()->hasPermission('buku.import') || Auth::user()->isAdmin())
                    <a href="{{ route('buku.download-template') }}" class="toolbar-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-download text-blue-500"></i>
                        <span class="btn-text">Template</span>
                    </a>
                    <button onclick="showImportModal()" class="toolbar-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-upload text-amber-500"></i>
                        <span class="btn-text">Import</span>
                    </button>
                    @endif
                </div>

                <!-- Right: Search & Filter -->
                <div class="flex items-center gap-2">
                    <div class="relative flex-1 sm:flex-none">
                        <input type="text" id="searchInput" placeholder="Cari judul, ISBN, penulis..."
                               class="w-full sm:w-72 px-4 py-2.5 pl-10 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400 text-sm"></i>
                        </div>
                    </div>
                    <button onclick="openFilterModal()" id="filterBtn"
                            class="toolbar-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 relative">
                        <i class="fas fa-sliders-h"></i>
                        <span class="btn-text">Filter</span>
                        <span id="filterBadge" class="hidden absolute -top-1.5 -right-1.5 w-5 h-5 bg-blue-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span>
                    </button>
                </div>
            </div>

            <!-- Active filters row -->
            <div id="activeFilters" class="hidden flex items-center flex-wrap gap-2">
                <span class="text-xs text-gray-500 font-medium">Filter aktif:</span>
                <div id="filterChips" class="flex flex-wrap gap-1.5"></div>
                <button onclick="resetFilters()" class="text-xs text-red-500 hover:text-red-700 font-medium ml-1">
                    <i class="fas fa-times-circle mr-1"></i>Hapus semua
                </button>
            </div>
        </div>
    </div>

    <!-- Bulk Action Bar -->
    @if(Auth::user()->hasPermission('buku.delete') || Auth::user()->isAdmin() || Auth::user()->hasPermission('buku.print-barcode') || Auth::user()->isAdmin())
    <div id="bulkActionBar" class="bulk-bar">
        <div class="glass-card rounded-xl border border-blue-200 bg-blue-50/50 px-4 py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-check-double text-blue-600 text-sm"></i>
                </div>
                <span id="selectedCount" class="text-sm font-medium text-blue-700">0 buku dipilih</span>
            </div>
            <div class="flex items-center gap-2">
                @if(Auth::user()->hasPermission('buku.print-barcode') || Auth::user()->isAdmin())
                <button onclick="printBarcodeSelected()" class="toolbar-btn bg-white border border-purple-200 text-purple-700 hover:bg-purple-50 text-xs">
                    <i class="fas fa-barcode"></i>
                    <span class="btn-text">Cetak Barcode</span>
                </button>
                @endif
                @if(Auth::user()->hasPermission('buku.delete') || Auth::user()->isAdmin())
                <button onclick="deleteSelected()" class="toolbar-btn bg-white border border-red-200 text-red-700 hover:bg-red-50 text-xs">
                    <i class="fas fa-trash-alt"></i>
                    <span class="btn-text">Hapus</span>
                </button>
                @endif
                <button onclick="clearSelection()" class="toolbar-btn bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-xs">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Table Card -->
    <div class="glass-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table id="buku-table" class="w-full" style="min-width: 900px;">
                <thead>
                    <tr>
                        @if(Auth::user()->hasPermission('buku.delete') || Auth::user()->isAdmin() || Auth::user()->hasPermission('buku.print-barcode') || Auth::user()->isAdmin())
                        <th class="px-4 py-3.5 text-left w-12">
                            <div class="flex items-center justify-center">
                                <input type="checkbox" id="selectAll" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 cursor-pointer transition-all duration-200">
                            </div>
                        </th>
                        @endif
                        <th class="px-4 py-3.5 text-left w-16">Cover</th>
                        <th class="px-4 py-3.5 text-left">Judul Buku</th>
                        <th class="px-4 py-3.5 text-left">Rak</th>
                        <th class="px-4 py-3.5 text-left">Kategori</th>
                        <th class="px-4 py-3.5 text-left">Jenis</th>
                        <th class="px-4 py-3.5 text-left">Stok</th>
                        <th class="px-4 py-3.5 text-left">Status</th>
                        @if(Auth::user()->hasPermission('buku.view') || Auth::user()->hasPermission('buku.edit') || Auth::user()->hasPermission('buku.delete') || Auth::user()->hasPermission('buku.print-barcode') || Auth::user()->isAdmin())
                        <th class="px-4 py-3.5 text-center w-40">Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Filter Modal -->
<div id="filterModal" class="fixed inset-0 z-50 hidden" style="background:rgba(15,23,42,0.5);backdrop-filter:blur(4px);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95 opacity-0" id="filterModalContent">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-filter text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Filter Buku</h3>
                            <p class="text-blue-100 text-xs">Saring data berdasarkan kriteria</p>
                        </div>
                    </div>
                    <button onclick="closeFilterModal()" class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="filterForm" class="p-6">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Kategori</label>
                        <select name="kategori_id" id="filter_kategori_id" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Kategori</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}">{{ $kategori->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Jenis Buku</label>
                        <select name="jenis_id" id="filter_jenis_id" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Jenis</option>
                            @foreach($jenis as $jenisItem)
                                <option value="{{ $jenisItem->id }}">{{ $jenisItem->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Status Ketersediaan</label>
                        <select name="status" id="filter_status" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Status</option>
                            <option value="tersedia">Tersedia</option>
                            <option value="habis">Habis</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" id="filter_tahun_terbit" placeholder="Contoh: 2023"
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 mt-6 pt-5 border-t border-gray-100">
                    <button type="button" onclick="resetFilters()" class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200">
                        <i class="fas fa-undo"></i>
                        <span>Reset</span>
                    </button>
                    <button type="submit" class="toolbar-btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md">
                        <i class="fas fa-check"></i>
                        <span>Terapkan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@if(Auth::user()->hasPermission('buku.import') || Auth::user()->isAdmin())
<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden" style="background:rgba(15,23,42,0.5);backdrop-filter:blur(4px);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300">
            <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-file-import text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-white">Import Data Buku</h3>
                            <p class="text-emerald-100 text-xs">Upload file Excel/CSV</p>
                        </div>
                    </div>
                    <button onclick="closeImportModal()" class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('buku.import') }}" enctype="multipart/form-data" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">
                        File Excel/CSV <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required
                               class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    </div>
                    <p class="mt-1.5 text-[11px] text-gray-400">Format: Excel (.xlsx, .xls) atau CSV. Maksimal 2MB</p>
                </div>

                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex gap-2">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 text-sm"></i>
                        <div class="text-xs text-blue-700 space-y-0.5">
                            <p class="font-medium">Catatan Penting:</p>
                            <p>- Download template terlebih dahulu</p>
                            <p>- Pastikan format data sesuai template</p>
                            <p>- Barcode akan digenerate otomatis jika tidak diisi</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-5 pt-4 border-t border-gray-100">
                    <button type="button" onclick="closeImportModal()" class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200">
                        Batal
                    </button>
                    <button type="submit" class="toolbar-btn bg-gradient-to-r from-emerald-500 to-green-600 text-white shadow-md">
                        <i class="fas fa-upload"></i>
                        <span>Import Data</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 flex items-center justify-center z-[60] hidden" style="background:rgba(15,23,42,0.6);backdrop-filter:blur(4px);">
    <div class="bg-white rounded-2xl p-6 flex flex-col items-center gap-3 shadow-2xl">
        <div class="w-12 h-12 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
        <span class="text-sm font-medium text-gray-700">Memproses...</span>
    </div>
</div>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
let bukuTable;
let selectedIds = [];

const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

$(document).ready(function() {
    const hasCheckboxColumn = {{ (Auth::user()->hasPermission('buku.delete') || Auth::user()->isAdmin() || Auth::user()->hasPermission('buku.print-barcode') || Auth::user()->isAdmin()) ? 'true' : 'false' }};
    const hasActionColumn = {{ (Auth::user()->hasPermission('buku.view') || Auth::user()->hasPermission('buku.edit') || Auth::user()->hasPermission('buku.delete') || Auth::user()->hasPermission('buku.print-barcode') || Auth::user()->isAdmin()) ? 'true' : 'false' }};

    let columns = [];

    if (hasCheckboxColumn) {
        columns.push({
            data: 'checkbox', name: 'checkbox', orderable: false, searchable: false,
            className: 'px-4 py-3 whitespace-nowrap text-center'
        });
    }

    columns = columns.concat([
        { data: 'cover', name: 'cover', orderable: false, searchable: false, className: 'px-4 py-3 whitespace-nowrap' },
        { data: 'judul_info', name: 'judul_buku', className: 'px-4 py-3' },
        { data: 'rak_info', name: 'rak', orderable: false, className: 'px-4 py-3 whitespace-nowrap' },
        { data: 'kategori_badge', name: 'kategori', orderable: false, className: 'px-4 py-3 whitespace-nowrap' },
        { data: 'jenis_badge', name: 'jenis', orderable: false, className: 'px-4 py-3 whitespace-nowrap' },
        { data: 'stok_info', name: 'stok_tersedia', className: 'px-4 py-3 whitespace-nowrap' },
        { data: 'status_badge', name: 'status', orderable: false, className: 'px-4 py-3 whitespace-nowrap' }
    ]);

    if (hasActionColumn) {
        columns.push({
            data: 'action', name: 'action', orderable: false, searchable: false,
            className: 'px-4 py-3 whitespace-nowrap text-center'
        });
    }

    bukuTable = $('#buku-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("buku.index") }}',
            data: function(d) {
                d.filter_kategori_id = $('#filter_kategori_id').val();
                d.filter_jenis_id = $('#filter_jenis_id').val();
                d.filter_status = $('#filter_status').val();
                d.filter_tahun_terbit = $('#filter_tahun_terbit').val();
            }
        },
        columns: columns,
        language: {
            processing: '<div class="flex items-center justify-center py-6"><div class="w-8 h-8 rounded-full border-3 border-blue-200 border-t-blue-600 animate-spin"></div><span class="ml-3 text-sm text-gray-600">Memuat data...</span></div>',
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: '<div class="text-center py-12"><div class="mx-auto w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-4"><i class="fas fa-book text-2xl text-gray-400"></i></div><h3 class="text-base font-semibold text-gray-800 mb-1">Tidak ada buku ditemukan</h3><p class="text-sm text-gray-500">Coba ubah kata kunci atau filter pencarian</p></div>',
            info: "Menampilkan _START_-_END_ dari _TOTAL_ buku",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(dari _MAX_ total)",
            search: "Cari:",
            paginate: {
                first: '<i class="fas fa-angle-double-left"></i>',
                last: '<i class="fas fa-angle-double-right"></i>',
                next: '<i class="fas fa-angle-right"></i>',
                previous: '<i class="fas fa-angle-left"></i>'
            }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[hasCheckboxColumn ? 2 : 1, 'asc']],
        drawCallback: function() {
            attachCheckboxListeners();
            updateSelectedCount();
        }
    });

    // Custom search with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const val = this.value;
        searchTimeout = setTimeout(() => {
            bukuTable.search(val).draw();
        }, 400);
    });

    // Select all
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        $('.book-checkbox').each(function() {
            $(this).prop('checked', isChecked);
            const row = $(this).closest('tr');
            if (isChecked) {
                row.addClass('selected-row');
                if (!selectedIds.includes($(this).val())) {
                    selectedIds.push($(this).val());
                }
            } else {
                row.removeClass('selected-row');
                selectedIds = [];
            }
        });
        updateSelectedCount();
    });
});

function attachCheckboxListeners() {
    $('.book-checkbox').off('change').on('change', function() {
        const id = $(this).val();
        const row = $(this).closest('tr');

        if ($(this).is(':checked')) {
            row.addClass('selected-row');
            if (!selectedIds.includes(id)) selectedIds.push(id);
        } else {
            row.removeClass('selected-row');
            selectedIds = selectedIds.filter(item => item !== id);
        }

        updateSelectedCount();
        updateSelectAllState();
    });
}

function updateSelectedCount() {
    const count = $('.book-checkbox:checked').length;
    const selectedCountEl = document.getElementById('selectedCount');
    const bulkBar = document.getElementById('bulkActionBar');

    if (selectedCountEl) {
        selectedCountEl.textContent = `${count} buku dipilih`;
    }

    if (bulkBar) {
        if (count > 0) {
            bulkBar.classList.add('active');
        } else {
            bulkBar.classList.remove('active');
        }
    }
}

function updateSelectAllState() {
    const checked = $('.book-checkbox:checked').length;
    const total = $('.book-checkbox').length;
    const selectAll = $('#selectAll');
    if (total > 0) {
        selectAll.prop('checked', checked === total);
        selectAll.prop('indeterminate', checked > 0 && checked < total);
    }
}

function clearSelection() {
    $('.book-checkbox').prop('checked', false);
    $('#selectAll').prop('checked', false).prop('indeterminate', false);
    $('tr').removeClass('selected-row');
    selectedIds = [];
    updateSelectedCount();
}

// Filter modal
function openFilterModal() {
    const modal = document.getElementById('filterModal');
    const content = document.getElementById('filterModalContent');
    modal.classList.remove('hidden');
    setTimeout(() => {
        content.style.transform = 'scale(1)';
        content.style.opacity = '1';
    }, 10);
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    const content = document.getElementById('filterModalContent');
    content.style.transform = 'scale(0.95)';
    content.style.opacity = '0';
    setTimeout(() => modal.classList.add('hidden'), 200);
}

function resetFilters() {
    $('#filter_kategori_id').val('');
    $('#filter_jenis_id').val('');
    $('#filter_status').val('');
    $('#filter_tahun_terbit').val('');
    bukuTable.draw();
    closeFilterModal();
    updateFilterChips();
}

function updateFilterChips() {
    const chipsContainer = document.getElementById('filterChips');
    const activeFilters = document.getElementById('activeFilters');
    const filterBadge = document.getElementById('filterBadge');
    chipsContainer.innerHTML = '';
    let count = 0;

    const selectFilters = [
        { id: 'filter_kategori_id', label: 'Kategori' },
        { id: 'filter_jenis_id', label: 'Jenis' },
        { id: 'filter_status', label: 'Status' }
    ];

    selectFilters.forEach(f => {
        const el = document.getElementById(f.id);
        if (el && el.value) {
            count++;
            const text = el.options[el.selectedIndex].text;
            const chip = document.createElement('span');
            chip.className = 'filter-chip';
            chip.innerHTML = `${f.label}: ${text} <span class="remove" onclick="removeFilter('${f.id}')">&times;</span>`;
            chipsContainer.appendChild(chip);
        }
    });

    // Tahun terbit (input field)
    const tahunEl = document.getElementById('filter_tahun_terbit');
    if (tahunEl && tahunEl.value) {
        count++;
        const chip = document.createElement('span');
        chip.className = 'filter-chip';
        chip.innerHTML = `Tahun: ${tahunEl.value} <span class="remove" onclick="removeFilter('filter_tahun_terbit')">&times;</span>`;
        chipsContainer.appendChild(chip);
    }

    if (count > 0) {
        activeFilters.classList.remove('hidden');
        filterBadge.classList.remove('hidden');
        filterBadge.textContent = count;
    } else {
        activeFilters.classList.add('hidden');
        filterBadge.classList.add('hidden');
    }
}

function removeFilter(id) {
    document.getElementById(id).value = '';
    bukuTable.draw();
    updateFilterChips();
}

// Filter form submit
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    bukuTable.draw();
    closeFilterModal();
    updateFilterChips();
});

// Close modal on backdrop click
document.getElementById('filterModal').addEventListener('click', function(e) {
    if (e.target === this) closeFilterModal();
});

// Import modal
function showImportModal() {
    document.getElementById('importModal').classList.remove('hidden');
}

function closeImportModal() {
    document.getElementById('importModal').classList.add('hidden');
}

const importModal = document.getElementById('importModal');
if (importModal) {
    importModal.addEventListener('click', function(e) {
        if (e.target === this) closeImportModal();
    });
}

// Bulk operations
function deleteSelected() {
    const ids = Array.from(document.querySelectorAll('.book-checkbox:checked')).map(cb => cb.value);

    if (ids.length === 0) {
        Toast.fire({ icon: 'warning', title: 'Pilih buku yang akan dihapus' });
        return;
    }

    Swal.fire({
        title: 'Hapus Data Buku?',
        html: `<p class="text-gray-600">Anda akan menghapus <strong class="text-red-600">${ids.length}</strong> data buku. Tindakan ini tidak dapat dibatalkan.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash-alt mr-2"></i>Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            showLoadingOverlay();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch('{{ route("buku.destroy-multiple") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ buku_ids: ids })
            })
            .then(r => r.json())
            .then(data => {
                hideLoadingOverlay();
                if (data.success) {
                    Toast.fire({ icon: 'success', title: data.message });
                    selectedIds = [];
                    clearSelection();
                    bukuTable.draw();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                Swal.fire('Error', 'Terjadi kesalahan saat menghapus data', 'error');
            });
        }
    });
}

function printBarcodeSelected() {
    const ids = Array.from(document.querySelectorAll('.book-checkbox:checked')).map(cb => cb.value);

    if (ids.length === 0) {
        Toast.fire({ icon: 'warning', title: 'Pilih buku yang akan dicetak barcodenya' });
        return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("buku.print-multiple-barcode") }}';
    form.target = '_blank';

    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    form.appendChild(csrfInput);

    ids.forEach(id => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'buku_ids[]';
        input.value = id;
        form.appendChild(input);
    });

    document.body.appendChild(form);
    form.submit();
    document.body.removeChild(form);
}

function confirmDeleteBuku(id) {
    Swal.fire({
        title: 'Hapus Buku?',
        text: 'Data buku ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash-alt mr-2"></i>Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            showLoadingOverlay();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

            fetch('{{ route("buku.index") }}/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(r => r.json())
            .then(data => {
                hideLoadingOverlay();
                if (data.success) {
                    Toast.fire({ icon: 'success', title: data.message });
                    bukuTable.draw();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                Swal.fire('Error', 'Terjadi kesalahan saat menghapus data', 'error');
            });
        }
    });
}

function showLoadingOverlay() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoadingOverlay() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}
</script>
@endsection