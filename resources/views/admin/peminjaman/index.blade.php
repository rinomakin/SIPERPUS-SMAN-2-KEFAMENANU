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
        display: none;
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
</style>
@endpush

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="space-y-5">

    <!-- Page Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 anim-up">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Peminjaman Buku</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data peminjaman buku perpustakaan</p>
        </div>
        <div class="flex items-center gap-2.5 flex-wrap">
            @if(Auth::user()->hasPermission('riwayat-transaksi.view') || Auth::user()->isAdmin())
            <a href="{{ route('riwayat-peminjaman.index') }}"
               class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                <i class="fas fa-history mr-2 text-gray-400"></i>Riwayat
            </a>
            @endif

            @if(Auth::user()->hasPermission('peminjaman.create') || Auth::user()->isAdmin())
            <a href="{{ route('peminjaman.create') }}"
               class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-medium transition-all shadow-md shadow-blue-500/25">
                <i class="fas fa-plus mr-2"></i>Tambah Peminjaman
            </a>
            @endif
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
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
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden anim-up" style="animation-delay:.25s">
        <!-- Toolbar -->
        <div class="px-5 py-4 border-b border-gray-100">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <!-- Search -->
                <div class="relative max-w-sm w-full">
                    <input type="text" id="searchInput" placeholder="Cari nomor, anggota, buku..."
                           class="w-full pl-10 pr-4 py-2.5 text-sm bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 focus:bg-white transition-all outline-none">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                </div>

                <!-- Quick Filters + Filter Button -->
                <div class="flex items-center gap-2 flex-wrap">
                    <button onclick="setQuickFilter('')"
                            class="filter-chip px-3 py-1.5 text-xs font-medium rounded-lg border transition-all active bg-blue-50 border-blue-200 text-blue-700"
                            data-filter="" id="chip-all">
                        Semua
                    </button>
                    <button onclick="setQuickFilter('dipinjam')"
                            class="filter-chip px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 text-gray-600 bg-white hover:bg-amber-50 hover:border-amber-200 hover:text-amber-700 transition-all"
                            data-filter="dipinjam" id="chip-dipinjam">
                        <span class="status-dot bg-amber-400"></span>Dipinjam
                    </button>
                    <button onclick="setQuickFilter('terlambat')"
                            class="filter-chip px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 text-gray-600 bg-white hover:bg-rose-50 hover:border-rose-200 hover:text-rose-700 transition-all"
                            data-filter="terlambat" id="chip-terlambat">
                        <span class="status-dot bg-rose-400"></span>Terlambat
                    </button>

                    <div class="w-px h-6 bg-gray-200 mx-1 hidden sm:block"></div>

                    <button onclick="openFilterModal()"
                            class="inline-flex items-center px-3.5 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 transition-all">
                        <i class="fas fa-sliders-h mr-1.5"></i>Filter Lanjutan
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table id="peminjaman-table" class="w-full">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nomor Peminjaman</th>
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
        <div class="modal-panel relative bg-white rounded-2xl shadow-2xl max-w-md w-full overflow-hidden">
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
let currentQuickFilter = '';

$(document).ready(function() {
    // Initialize DataTable
    peminjamanTable = $('#peminjaman-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("peminjaman.index") }}',
            data: function(d) {
                d.filter_status = currentQuickFilter || $('#filter_status').val();
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
            { data: 'nomor_badge', name: 'nomor_peminjaman', className: 'px-4 py-3' },
            { data: 'anggota_info', name: 'anggota.nama_lengkap', className: 'px-4 py-3' },
            { data: 'jumlah_badge', name: 'jumlah_buku', className: 'px-4 py-3 text-center' },
            { data: 'tanggal_pinjam_info', name: 'tanggal_peminjaman', className: 'px-4 py-3' },
            { data: 'batas_kembali_info', name: 'tanggal_harus_kembali', className: 'px-4 py-3' },
            { data: 'status_badge', name: 'status', className: 'px-4 py-3' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-3 text-center' }
        ],
        language: {
            processing: '<div class="flex items-center justify-center py-6"><div class="animate-spin rounded-full h-5 w-5 border-2 border-blue-600 border-t-transparent"></div><span class="ml-3 text-sm text-gray-500">Memuat data...</span></div>',
            lengthMenu: "Tampilkan _MENU_ data",
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
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[4, 'desc']],
        drawCallback: function() {
            // Re-apply tooltips or animations after draw if needed
        }
    });

    // Debounced search
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const val = this.value;
        searchTimeout = setTimeout(() => peminjamanTable.search(val).draw(), 400);
    });

    // Load summary on init
    loadSummary();
});

// ===== Summary Cards =====
function loadSummary() {
    $.ajax({
        url: '{{ route("peminjaman.index") }}',
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

// ===== Quick Filters =====
function setQuickFilter(status) {
    currentQuickFilter = status;
    // Update chip styles
    document.querySelectorAll('.filter-chip').forEach(chip => {
        const f = chip.dataset.filter;
        chip.classList.remove('active', 'bg-blue-50', 'border-blue-200', 'text-blue-700', 'bg-amber-50', 'border-amber-200', 'text-amber-700', 'bg-rose-50', 'border-rose-200', 'text-rose-700');
        chip.classList.add('bg-white', 'border-gray-200', 'text-gray-600');
        if (f === status) {
            chip.classList.remove('bg-white', 'border-gray-200', 'text-gray-600');
            if (status === '') {
                chip.classList.add('active', 'bg-blue-50', 'border-blue-200', 'text-blue-700');
            } else if (status === 'dipinjam') {
                chip.classList.add('active', 'bg-amber-50', 'border-amber-200', 'text-amber-700');
            } else if (status === 'terlambat') {
                chip.classList.add('active', 'bg-rose-50', 'border-rose-200', 'text-rose-700');
            }
        }
    });
    // Sync dropdown
    $('#filter_status').val(status);
    peminjamanTable.draw();
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
    currentQuickFilter = '';
    setQuickFilter('');
    peminjamanTable.draw();
    closeFilterModal();
}

document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    currentQuickFilter = $('#filter_status').val();
    setQuickFilter(currentQuickFilter);
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
            form.action = '{{ route("peminjaman.index") }}/' + peminjamanId;
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
