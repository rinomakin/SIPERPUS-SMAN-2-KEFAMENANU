@extends('layouts.admin')

@section('title', 'Data Anggota')
@section('page-title', 'Data Anggota')

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
    #anggota-table_wrapper .dataTables_length,
    #anggota-table_wrapper .dataTables_info,
    #anggota-table_wrapper .dataTables_paginate {
        padding: 12px 16px;
        font-size: 0.875rem;
        color: #6b7280;
    }
    #anggota-table_wrapper .dataTables_toolbar {
        padding: 10px 16px;
        border-bottom: 1px solid #e5e7eb;
        background: #fafbfc;
    }
    #anggota-table_wrapper .dataTables_toolbar_right {
        margin-left: auto;
    }
    #anggota-table_wrapper .dataTables_filter label {
        position: relative;
        margin-bottom: 0;
    }
    #anggota-table_wrapper .dataTables_filter label::before {
        content: '\f002';
        font-family: 'Font Awesome 6 Free';
        font-weight: 900;
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
        font-size: 0.7rem;
        z-index: 1;
        pointer-events: none;
    }
    #anggota-table_wrapper .dataTables_filter input {
        width: 16rem;
        padding: 6px 12px 6px 32px;
        font-size: 0.7rem;
        background: #f9fafb;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        outline: none;
        transition: all 0.2s;
    }
    #anggota-table_wrapper .dataTables_filter input:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        background: white;
    }
    #anggota-table_wrapper .dataTables_length {
        display: flex;
        align-items: center;
    }
    #anggota-table_wrapper .dataTables_length label {
        display: flex;
        align-items: center;
        gap: 4px;
        font-size: 0.7rem;
        color: #6b7280;
        margin-bottom: 0;
    }
    #anggota-table_wrapper .dataTables_length select {
        padding: 6px 32px 6px 12px;
        border-radius: 8px;
        border: 1px solid #e5e7eb;
        background-color: #f9fafb;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }
    #anggota-table_wrapper .dataTables_length select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59,130,246,0.1);
        outline: none;
    }
    #anggota-table thead th {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        font-size: 10px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 2px solid #e2e8f0;
    }
    #anggota-table tbody tr {
        transition: all 0.15s ease;
        font-size: 10px;
    }
    #anggota-table tbody tr:hover {
        background-color: #f0f9ff !important;
    }
    #anggota-table tbody tr.selected-row {
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
        width: 20px;
        height: 20px;
        border-radius: 100%;
        transition: all 0.2s;
        font-size: 10px;
        gap: 2px;
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

    /* Avatar styles */
    .avatar-container {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        overflow: hidden;
        flex-shrink: 0;
    }
    .avatar-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.85rem;
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
        .toolbar-btn {
            padding: 4px 5px;
            font-size: 0.45rem;
        }
        .toolbar-btn i,
        .toolbar-btn span.btn-text {
            font-size: inherit;
        }
        #anggota-table_wrapper .dataTables_toolbar {
            flex-wrap: nowrap;
            gap: 3px;
            padding: 6px 8px;
        }
        #anggota-table_wrapper .dataTables_toolbar_left,
        #anggota-table_wrapper .dataTables_toolbar_right {
            gap: 3px;
        }
        #anggota-table_wrapper .dataTables_filter input {
            width: 90px;
            padding: 4px 6px 4px 22px;
            font-size: 0.6rem;
        }
        #anggota-table_wrapper .dataTables_filter label::before {
            left: 8px;
            font-size: 0.6rem;
        }
        #anggota-table_wrapper .dataTables_length label {
            font-size: 0.6rem;
            gap: 2px;
        }
        #anggota-table_wrapper .dataTables_length select {
            padding: 3px 18px 3px 5px;
            font-size: 0.6rem;
        }
    }
    /* Very small screens — icons only, tighter spacing */
    @media (max-width: 380px) {
        .toolbar-btn {
            padding: 3px 4px;
            font-size: 0.4rem;
        }
        .toolbar-btn span.btn-text {
            display: none;
        }
        #anggota-table_wrapper .dataTables_toolbar {
            gap: 2px;
            padding: 4px 6px;
        }
        #anggota-table_wrapper .dataTables_toolbar_left,
        #anggota-table_wrapper .dataTables_toolbar_right {
            gap: 2px;
        }
        #anggota-table_wrapper .dataTables_filter input {
            width: 60px;
            font-size: 0.5rem;
            padding: 3px 4px 3px 18px;
        }
        #anggota-table_wrapper .dataTables_filter label::before {
            left: 6px;
            font-size: 0.5rem;
        }
        #anggota-table_wrapper .dataTables_length select {
            font-size: 0.5rem;
            padding: 2px 14px 2px 4px;
        }
        #anggota-table_wrapper .dataTables_length label {
            font-size: 0.5rem;
            gap: 1px;
        }
    }
</style>

<div class="space-y-5">
    <!-- Bulk Action Bar -->
    @if(Auth::user()->hasPermission('anggota.delete') || Auth::user()->isAdmin() || Auth::user()->hasPermission('anggota.cetak-kartu') || Auth::user()->isAdmin())
    <div id="bulkActionBar" class="bulk-bar">
        <div class="glass-card rounded-xl border border-blue-200 bg-blue-50/50 px-4 py-2.5 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-check-double text-blue-600 text-[10px]"></i>
                </div>
                <span id="selectedCount" class="text-[10px] font-medium text-blue-700">0 anggota dipilih</span>
            </div>
            <div class="flex items-center gap-2">
                @if(Auth::user()->hasPermission('anggota.cetak-kartu') || Auth::user()->isAdmin())
                <button onclick="bulkPrintKartu()" class="toolbar-btn bg-white border border-purple-200 text-purple-700 hover:bg-purple-50 text-[10px]">
                    <i class="fas fa-print"></i>
                    <span class="btn-text">Cetak Kartu</span>
                </button>
                @endif
                @if(Auth::user()->hasPermission('anggota.delete') || Auth::user()->isAdmin())
                <button onclick="bulkDelete()" class="toolbar-btn bg-white border border-red-200 text-red-700 hover:bg-red-50 text-[10px]">
                    <i class="fas fa-trash-alt"></i>
                    <span class="btn-text">Hapus</span>
                </button>
                @endif
                <button onclick="clearSelection()" class="toolbar-btn bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 text-[10px]">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    </div>
    @endif

    <!-- Table Card -->
    <div class="glass-card rounded-2xl shadow-sm border border-gray-100 w-full transform transition-all duration-300">
        <div class="w-full">
            <table id="anggota-table" class="w-full" style="min-width: 850px;">
                <thead>
                    <tr>
                        @if(Auth::user()->hasPermission('anggota.delete') || Auth::user()->isAdmin() || Auth::user()->hasPermission('anggota.cetak-kartu') || Auth::user()->isAdmin())
                        <th class="px-2 py-2 text-left w-6">
                            <input type="checkbox" id="selectAll" class="member-checkbox-select-all">
                        </th>
                        <th class="px-2 py-2 text-left text-[9px]">No</th>
                        <th class="px-2 py-2 text-left text-[9px]">Anggota</th>
                        <th class="px-2 py-2 text-left text-[9px]">Gender</th>
                        <th class="px-2 py-2 text-left text-[9px] hidden">NIK</th>
                        <th class="px-2 py-2 text-left text-[9px] hidden">Kelas</th>
                        <th class="px-2 py-2 text-left text-[9px]">Jenis</th>
                        <th class="px-2 py-2 text-left text-[9px]">Status</th>

                        <th class="px-2 py-2 text-center text-[9px]">Aksi</th>
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
            <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-3 py-2 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <div class="w-[10px] h-[10px] rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-filter text-white text-[10px]"></i>
                        </div>
                        <div>
                            <h3 class="text-[10px] font-semibold text-white">Filter Anggota</h3>
                            <p class="text-blue-100 text-[10px]">Saring data berdasarkan kriteria</p>
                        </div>
                    </div>
                    <button onclick="closeFilterModal()" class="w-[30px] h-[30px] rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-[10px]"></i>
                    </button>
                </div>
            </div>

            <form id="filterForm" class="p-3" data-spa-ignore>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5  tracking-wider">Kelas</label>
                        <select name="kelas_id" id="filter_kelas_id" class="w-full px-3 py-2.5 text-[10px] border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Kelas</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5  tracking-wider">Jurusan</label>
                        <select name="jurusan_id" id="filter_jurusan_id" class="w-full px-3 py-2.5 text-[10px] border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Jurusan</option>
                            @foreach($jurusan as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jurusan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5  tracking-wider">Jenis Anggota</label>
                        <select name="jenis_anggota" id="filter_jenis_anggota" class="w-full px-3 py-2.5 text-[10px] border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Jenis</option>
                            <option value="siswa">Siswa</option>
                            <option value="guru">Guru</option>
                            <option value="staff">Staff</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5  tracking-wider">Status</label>
                        <select name="status" id="filter_status" class="w-full px-3 py-2.5 text-[10px] border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all">
                            <option value="">Semua Status</option>
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                            <option value="ditangguhkan">Ditangguhkan</option>
                        </select>
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

@if(Auth::user()->hasPermission('anggota.import') || Auth::user()->isAdmin())
<!-- Import Modal -->
<div id="importModal" class="fixed inset-0 z-50 hidden" style="background:rgba(15,23,42,0.5);backdrop-filter:blur(4px);">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300">
            <div class=" px-3 py-2 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-1.5">
                        <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-file-import text-black"></i>
                        </div>
                        <div>
                            <h3 class="text-[10px] font-semibold text-black">Import Data Anggota</h3>
                            <p class="text-black text-[10px]">Upload file Excel/CSV</p>
                        </div>
                    </div>
                    <button onclick="closeImportModal()" class="w-6 h-6 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-times text-black"></i>
                    </button>
                </div>
            </div>

            <form method="POST" action="{{ route('anggota.import') }}" enctype="multipart/form-data" class="p-3">
                @csrf
                <div class="mb-4">
                    <label class="block text-[10px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">
                        File Excel/CSV <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv" required
                               class="w-full px-3 py-2.5 text-[10px] border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 file:mr-1.5 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[10px] file:font-medium file:bg-emerald-50 file:text-emerald-700 hover:file:bg-emerald-100 cursor-pointer">
                    </div>
                    <p class="mt-1.5 text-[10px] text-gray-400">Format: Excel (.xlsx, .xls) atau CSV. Maksimal 2MB</p>
                </div>

                <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                    <div class="flex gap-2">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 text-[10px]"></i>
                        <div class="text-[10px] text-blue-700 space-y-0.5">
                            <p class="font-medium">Catatan Penting:</p>
                            <p>- Download template terlebih dahulu</p>
                            <p>- Pastikan format data sesuai template</p>
                            <p>- Nomor anggota & barcode akan digenerate otomatis</p>
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
    <div class="bg-white rounded-2xl p-3 flex flex-col items-center gap-1.5 shadow-2xl">
        <div class="w-6 h-6 rounded-full border-4 border-blue-200 border-t-blue-600 animate-spin"></div>
        <span class="text-[10px] font-medium text-gray-700">Memproses...</span>
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
@if(session('success'))
Swal.fire({icon:'success',title:'Berhasil!',text:{!! json_encode(session('success')) !!},timer:2800,timerProgressBar:true,showConfirmButton:false,toast:true,position:'top-end',background:'#f0fdf4',iconColor:'#16a34a'});
@endif
@if(session('error'))
Swal.fire({icon:'error',title:'Gagal!',text:{!! json_encode(session('error')) !!},confirmButtonColor:'#ef4444',confirmButtonText:'OK'});
@endif

let anggotaTable;
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
    const hasCheckboxColumn = {{ (Auth::user()->hasPermission('anggota.delete') || Auth::user()->isAdmin() || Auth::user()->hasPermission('anggota.cetak-kartu') || Auth::user()->isAdmin()) ? 'true' : 'false' }};
    const hasActionColumn = {{ (Auth::user()->hasPermission('anggota.view') || Auth::user()->hasPermission('anggota.edit') || Auth::user()->hasPermission('anggota.delete') || Auth::user()->hasPermission('anggota.cetak-kartu') || Auth::user()->isAdmin()) ? 'true' : 'false' }};

    let columns = [];

    if (hasCheckboxColumn) {
        columns.push({
            data: 'checkbox', name: 'checkbox', orderable: false, searchable: false,
            className: 'px-1 py-1.5 whitespace-nowrap text-center'
        });
    }

    columns = columns.concat([
        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-2 py-1.5 text-[9px] whitespace-nowrap text-gray-500 font-medium' },

        { data: 'nama_info', name: 'nama_lengkap', className: 'px-2 py-1.5 whitespace-nowrap text-[9px]' },

        { data: 'jenis_kelamin_badge', name: 'jenis_kelamin', className: 'px-2 py-1.5 whitespace-nowrap text-[9px] font-mono' },

        { data: 'kelas_info', name: 'kelas', orderable: false, visible: false, className: 'px-2 py-1.5 whitespace-nowrap text-[9px]' },

        { data: 'jenis_badge', name: 'jenis_anggota', className: 'px-2 py-1.5 whitespace-nowrap text-[9px]' },

        { data: 'status_badge', name: 'status', className: 'px-2 py-1.5 whitespace-nowrap text-[9px]' }
    ]);

    if (hasActionColumn) {
        columns.push({
            data: 'action', name: 'action', orderable: false, searchable: false,
            className: 'px-1 py-1.5 whitespace-nowrap text-center'
        });
    }

    anggotaTable = $('#anggota-table').DataTable({
        processing: true,
        serverSide: true,
        autoWidth: false,
        dom: '<"dataTables_toolbar flex items-center gap-2 flex-wrap"<"dataTables_toolbar_left flex items-center"l><"dataTables_toolbar_right flex items-center gap-2"f>>rt<"flex items-center justify-between"ip>',
        searchDelay: 400,
        ajax: {
            url: '/admin/anggota',
            data: function(d) {
                d.filter_kelas_id = $('#filter_kelas_id').val();
                d.filter_jurusan_id = $('#filter_jurusan_id').val();
                d.filter_jenis_anggota = $('#filter_jenis_anggota').val();
                d.filter_status = $('#filter_status').val();
            }
        },
        columns: columns,
        language: {
            processing: '<div class="flex items-center justify-center py-3"><div class="w-6 h-6 rounded-full border-3 border-blue-200 border-t-blue-600 animate-spin"></div><span class="ml-2 text-[10px] text-gray-600">Memuat data...</span></div>',
            lengthMenu: "_MENU_",
            zeroRecords: '<div class="text-center py-8"><div class="mx-auto w-14 h-14 bg-gradient-to-br from-gray-100 to-gray-200 rounded-2xl flex items-center justify-center mb-3"><i class="fas fa-users text-[10px] text-gray-400"></i></div><h3 class="text-[10px] font-semibold text-gray-800 mb-1">Tidak ada data ditemukan</h3><p class="text-[10px] text-gray-500">Coba ubah kata kunci atau filter pencarian</p></div>',
            info: "Menampilkan _START_-_END_ dari _TOTAL_ anggota",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(dari _MAX_ total)",
            search: "",
            searchPlaceholder: "Cari nama, NIK, email...",
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
        },
        initComplete: function() {
            var right = $('.dataTables_toolbar_right');

            right.append('<button onclick="openFilterModal()" id="filterBtn" class="toolbar-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 relative"><i class="fas fa-sliders-h text-[10px]"></i><span class="btn-text text-[10px]">Filter</span><span id="filterBadge" class="hidden absolute -top-1.5 -right-1.5 w-5 h-5 bg-blue-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">0</span></button>');

            @if(Auth::user()->hasPermission('anggota.import') || Auth::user()->isAdmin())
            right.append('<a href="{{ route('anggota.download-template') }}" class="toolbar-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50"><i class="fas fa-download text-blue-500 text-[10px]"></i><span class="btn-text text-[10px]">Template</span></a>');
            right.append('<button onclick="showImportModal()" class="toolbar-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50"><i class="fas fa-upload text-amber-500 text-[10px]"></i><span class="btn-text text-[10px]">Import</span></button>');
            @endif

            @if(Auth::user()->hasPermission('anggota.create') || Auth::user()->isAdmin())
            right.append('<a href="{{ route('anggota.create') }}" class="toolbar-btn bg-gradient-to-r from-blue-600 to-indigo-600 text-white shadow-md"><i class="fas fa-plus text-[10px]"></i><span class="btn-text text-[10px]">Tambah</span></a>');
            @endif

            /* Wrap table in scrollable div so toolbar stays fixed */
            $('#anggota-table').wrap('<div class="anggota-table-scroll" style="overflow-x:auto;-webkit-overflow-scrolling:touch;"></div>');
        }
    });

    // Select all
    $('#selectAll').on('change', function() {
        const isChecked = $(this).is(':checked');
        if (!isChecked) selectedIds = [];
        $('.member-checkbox').each(function() {
            $(this).prop('checked', isChecked).trigger('change');
        });
    });
});

function attachCheckboxListeners() {
    // Delegated listener — survives DataTables redraws
}

function updateSelectedCount() {
    const count = $('.member-checkbox:checked').length;
    const selectedCountEl = document.getElementById('selectedCount');
    const bulkBar = document.getElementById('bulkActionBar');

    if (selectedCountEl) {
        selectedCountEl.textContent = `${count} anggota dipilih`;
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
    const checked = $('.member-checkbox:checked').length;
    const total = $('.member-checkbox').length;
    const selectAll = $('#selectAll');
    if (total > 0) {
        selectAll.prop('checked', checked === total);
        selectAll.prop('indeterminate', checked > 0 && checked < total);
    }
}

function clearSelection() {
    selectedIds = [];
    $('.member-checkbox').each(function() {
        $(this).prop('checked', false).trigger('change');
    });
    $('#selectAll').prop('checked', false).prop('indeterminate', false);
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
    $('#filter_kelas_id').val('');
    $('#filter_jurusan_id').val('');
    $('#filter_jenis_anggota').val('');
    $('#filter_status').val('');
    anggotaTable.draw();
    closeFilterModal();
    updateFilterChips();
}

function updateFilterChips() {
    const filterBadge = document.getElementById('filterBadge');
    if (!filterBadge) return;
    let count = 0;

    const filters = [
        { id: 'filter_kelas_id', label: 'Kelas' },
        { id: 'filter_jurusan_id', label: 'Jurusan' },
        { id: 'filter_jenis_anggota', label: 'Jenis' },
        { id: 'filter_status', label: 'Status' }
    ];

    filters.forEach(f => {
        const el = document.getElementById(f.id);
        if (el && el.value) count++;
    });

    if (count > 0) {
        filterBadge.classList.remove('hidden');
        filterBadge.textContent = count;
    } else {
        filterBadge.classList.add('hidden');
    }
}

function removeFilter(id) {
    document.getElementById(id).value = '';
    anggotaTable.draw();
    updateFilterChips();
}

// Filter form submit
document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    anggotaTable.draw();
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
function bulkDelete() {
    const ids = Array.from(document.querySelectorAll('.member-checkbox:checked')).map(cb => cb.value);

    if (ids.length === 0) {
        Toast.fire({ icon: 'warning', title: 'Pilih data yang akan dihapus' });
        return;
    }

    Swal.fire({
        title: 'Hapus Data Anggota?',
        html: `<p class="text-gray-600">Anda akan menghapus <strong class="text-red-600">${ids.length}</strong> data anggota. Tindakan ini tidak dapat dibatalkan.</p>`,
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

            fetch('/admin/anggota/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(r => r.json())
            .then(data => {
                hideLoadingOverlay();
                if (data.success) {
                    Toast.fire({ icon: 'success', title: data.message });
                    selectedIds = [];
                    clearSelection();
                    anggotaTable.draw();
                } else {
                    Swal.fire('Error', data.error, 'error');
                }
            })
            .catch(error => {
                hideLoadingOverlay();
                Swal.fire('Error', 'Terjadi kesalahan saat menghapus data', 'error');
            });
        }
    });
}

function bulkPrintKartu() {
    const ids = Array.from(document.querySelectorAll('.member-checkbox:checked')).map(cb => cb.value);

    if (ids.length === 0) {
        Toast.fire({ icon: 'warning', title: 'Pilih data yang akan dicetak kartunya' });
        return;
    }

    const url = '/admin/anggota/bulk-print-kartu?ids=' + ids.join(',');
    window.open(url, '_blank');
}

function printAllKartu() {
    const checked = document.querySelectorAll('.member-checkbox:checked');
    if (checked.length > 0) {
        const ids = Array.from(checked).map(cb => cb.value);
        const url = '/admin/anggota/bulk-print-kartu?ids=' + ids.join(',');
        window.open(url, '_blank');
        return;
    }

    $.ajax({
        url: '/admin/anggota/all-ids',
        data: anggotaTable.ajax.params(),
        success: function(res) {
            if (res.ids && res.ids.length > 0) {
                const url = '/admin/anggota/bulk-print-kartu?ids=' + res.ids.join(',');
                window.open(url, '_blank');
            } else {
                Toast.fire({ icon: 'warning', title: 'Tidak ada anggota untuk dicetak' });
            }
        },
        error: function() {
            Toast.fire({ icon: 'error', title: 'Gagal mengambil data anggota' });
        }
    });
}

function confirmDeleteAnggota(id) {
    Swal.fire({
        title: 'Hapus Anggota?',
        text: 'Data anggota ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash-alt mr-2"></i>Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/anggota/' + id;

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfInput);
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function showLoadingOverlay() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoadingOverlay() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

$(document).on('change', '.member-checkbox', function() {
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
</script>
@endsection
