@extends('layouts.admin')

@section('title', 'Riwayat Kunjungan')
@section('page-title', 'Riwayat Kunjungan Buku Tamu')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .glass-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.3);
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade { animation: fadeIn 0.4s ease forwards; }

    /* DataTables overrides */
    #history-table_wrapper .dataTables_length,
    #history-table_wrapper .dataTables_info,
    #history-table_wrapper .dataTables_paginate {
        padding: 12px 20px;
    }
    #history-table_wrapper .dataTables_filter { display: none; }
    #history-table_wrapper .dataTables_length select {
        padding: 6px 28px 6px 12px;
        border-radius: 10px;
        border: 1px solid #e5e7eb;
        background: rgba(255,255,255,0.7);
        font-size: 0.875rem;
    }
    #history-table tbody tr:hover { background-color: rgba(139, 92, 246, 0.04) !important; }
    .dataTables_wrapper .dataTables_paginate .paginate_button {
        padding: 8px 14px !important;
        margin: 0 2px !important;
        border-radius: 10px !important;
        border: 1px solid #e5e7eb !important;
        font-size: 0.8rem !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed) !important;
        color: white !important;
        border: none !important;
        box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3) !important;
    }
    .dataTables_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #f5f3ff !important;
        border: 1px solid #c4b5fd !important;
        color: #6d28d9 !important;
    }
    .bulk-bar { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
    .bulk-bar.hidden { opacity: 0; transform: translateY(-10px); pointer-events: none; }
    .bulk-bar:not(.hidden) { opacity: 1; transform: translateY(0); }
    .cb-row { width: 18px; height: 18px; accent-color: #8b5cf6; cursor: pointer; }
</style>

<div class="space-y-5">
    {{-- Header & Toolbar --}}
    <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.buku-tamu.index') }}" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Riwayat Kunjungan</h1>
                    <p class="text-sm text-gray-500">Semua data kunjungan buku tamu</p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Search --}}
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Cari nama/instansi..."
                           class="w-56 px-4 py-2.5 pl-10 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white/70 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                </div>

                {{-- Filter --}}
                <button onclick="openFilterModal()"
                        class="px-4 py-2.5 bg-white border border-gray-200 hover:border-violet-300 hover:bg-violet-50 text-gray-700 text-sm font-medium rounded-xl transition-all">
                    <i class="fas fa-sliders-h mr-1.5"></i> Filter
                </button>

                {{-- Export --}}
                <button id="export-excel"
                        class="px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1.5"></i> Excel
                </button>
                <button id="export-pdf"
                        class="px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1.5"></i> PDF
                </button>
            </div>
        </div>
    </div>

    {{-- Bulk Delete Bar (admin & petugas only) --}}
    @if(!Auth::user()->isKepalaSekolah())
    <div id="bulkBar" class="bulk-bar hidden glass-card rounded-2xl shadow-lg p-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-double text-red-600"></i>
                </div>
                <div>
                    <span class="text-sm font-semibold text-gray-900"><span id="selectedCount">0</span> data dipilih</span>
                    <p class="text-xs text-gray-500">Pilih data yang ingin dihapus</p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <button onclick="clearSelection()" class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    <i class="fas fa-times mr-1.5"></i> Batal
                </button>
                <button onclick="bulkDelete()" class="px-4 py-2 text-sm font-medium text-white bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-trash mr-1.5"></i> Hapus Terpilih
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Data Table --}}
    <div class="glass-card rounded-2xl shadow-lg overflow-hidden animate-fade" style="animation-delay:0.1s">
        <div class="overflow-x-auto">
            <table id="history-table" class="w-full">
                <thead>
                    <tr class="bg-gradient-to-r from-violet-50 to-purple-50">
                        <th class="px-4 py-3 text-center" style="width:40px;">
                            @if(!Auth::user()->isKepalaSekolah())
                            <input type="checkbox" id="selectAll" class="cb-row" title="Pilih Semua">
                            @endif
                        </th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">No</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Tamu</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Tipe</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Kelas/Instansi</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Waktu Datang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Waktu Pulang</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Keperluan</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-semibold text-violet-700 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Filter Modal --}}
<div id="filterModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="glass-card rounded-2xl shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div class="bg-gradient-to-r from-violet-500 to-purple-600 px-6 py-4 rounded-t-2xl">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white"><i class="fas fa-sliders-h mr-2"></i>Filter Kunjungan</h3>
                <button onclick="closeFilterModal()" class="text-white/80 hover:text-white transition-colors">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
        </div>

        <form id="filterForm" class="p-6 space-y-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Dari</label>
                    <input type="date" id="filter_tanggal_dari"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white/70 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Sampai</label>
                    <input type="date" id="filter_tanggal_sampai"
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white/70 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <select id="filter_status"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white/70 text-sm">
                        <option value="">Semua Status</option>
                        <option value="berkunjung">Sedang Berkunjung</option>
                        <option value="pulang">Sudah Pulang</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Tipe Tamu</label>
                    <select id="filter_tipe"
                            class="w-full px-4 py-2.5 border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white/70 text-sm">
                        <option value="">Semua Tipe</option>
                        <option value="anggota">Anggota</option>
                        <option value="umum">Tamu Umum</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-200">
                <button type="button" onclick="resetFilters()"
                        class="px-4 py-2.5 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors">
                    <i class="fas fa-undo mr-1.5"></i> Reset
                </button>
                <button type="submit"
                        class="px-5 py-2.5 text-sm font-medium text-white bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-filter mr-1.5"></i> Terapkan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- DataTables CSS --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

{{-- jQuery & DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
let historyTable;
let selectedIds = new Set();
var isKepsek = {{ Auth::user()->isKepalaSekolah() ? 'true' : 'false' }};

$(document).ready(function() {
    historyTable = $('#history-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.buku-tamu.history") }}',
            data: function(d) {
                d.filter_status = $('#filter_status').val();
                d.filter_tipe = $('#filter_tipe').val();
                d.filter_tanggal_dari = $('#filter_tanggal_dari').val();
                d.filter_tanggal_sampai = $('#filter_tanggal_sampai').val();
            }
        },
        columns: [
            {
                data: null, orderable: false, searchable: false,
                className: 'px-4 py-3 text-center',
                visible: !isKepsek,
                render: function(data, type, row) {
                    if (isKepsek) return '';
                    const checked = selectedIds.has(row.id) ? 'checked' : '';
                    return '<input type="checkbox" class="cb-row row-check" data-id="' + row.id + '" ' + checked + '>';
                }
            },
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'px-4 py-3 text-sm text-gray-500' },
            { data: 'tamu_info', name: 'nama_tamu', className: 'px-4 py-3' },
            { data: 'tipe_badge', name: 'anggota_id', orderable: false, className: 'px-4 py-3' },
            { data: 'kelas_instansi', name: 'instansi', className: 'px-4 py-3 text-sm text-gray-700' },
            { data: 'waktu_datang_info', name: 'waktu_datang', className: 'px-4 py-3 text-sm text-gray-700' },
            { data: 'waktu_pulang_info', name: 'waktu_pulang', className: 'px-4 py-3 text-sm text-gray-700' },
            { data: 'keperluan_info', name: 'keperluan', className: 'px-4 py-3 text-sm text-gray-700' },
            { data: 'status_badge', name: 'status', orderable: false, className: 'px-4 py-3' },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'px-4 py-3 text-center' }
        ],
        language: {
            processing: '<div class="flex items-center justify-center py-4"><div class="animate-spin rounded-full h-6 w-6 border-b-2 border-violet-600"></div><span class="ml-3 text-gray-600 text-sm">Memproses...</span></div>',
            lengthMenu: "Tampilkan _MENU_ data",
            zeroRecords: '<div class="text-center py-12"><div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4"><i class="fas fa-book-open text-2xl text-gray-400"></i></div><h3 class="text-sm font-semibold text-gray-900 mb-1">Tidak ada data kunjungan</h3><p class="text-xs text-gray-500">Coba ubah filter pencarian Anda</p></div>',
            info: "Menampilkan _START_ - _END_ dari _TOTAL_ kunjungan",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(dari _MAX_ total)",
            paginate: { first: "Awal", last: "Akhir", next: '<i class="fas fa-chevron-right"></i>', previous: '<i class="fas fa-chevron-left"></i>' }
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[5, 'desc']],
        drawCallback: function() {
            updateSelectAllState();
        }
    });

    // Search with debounce
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        const val = this.value;
        searchTimeout = setTimeout(() => historyTable.search(val).draw(), 400);
    });

    // Select all checkbox
    $('#selectAll').on('change', function() {
        const checked = this.checked;
        $('#history-table tbody .row-check').each(function() {
            this.checked = checked;
            const id = parseInt($(this).data('id'));
            if (checked) {
                selectedIds.add(id);
            } else {
                selectedIds.delete(id);
            }
        });
        updateBulkBar();
    });

    // Row checkbox
    $('#history-table tbody').on('change', '.row-check', function() {
        const id = parseInt($(this).data('id'));
        if (this.checked) {
            selectedIds.add(id);
        } else {
            selectedIds.delete(id);
        }
        updateSelectAllState();
        updateBulkBar();
    });

    // Export Excel
    $('#export-excel').on('click', function() {
        const params = getFilterParams();
        window.open('{{ route("admin.buku-tamu.export-excel") }}?' + params.toString(), '_blank');
    });

    // Export PDF
    $('#export-pdf').on('click', function() {
        const params = getFilterParams();
        window.open('{{ route("admin.buku-tamu.export-pdf") }}?' + params.toString(), '_blank');
    });
});

function getFilterParams() {
    return new URLSearchParams({
        filter_status: $('#filter_status').val(),
        filter_tipe: $('#filter_tipe').val(),
        filter_tanggal_dari: $('#filter_tanggal_dari').val(),
        filter_tanggal_sampai: $('#filter_tanggal_sampai').val()
    });
}

function updateSelectAllState() {
    const allBoxes = $('#history-table tbody .row-check');
    const checkedBoxes = allBoxes.filter(':checked');
    $('#selectAll').prop('checked', allBoxes.length > 0 && allBoxes.length === checkedBoxes.length);
    $('#selectAll').prop('indeterminate', checkedBoxes.length > 0 && checkedBoxes.length < allBoxes.length);
}

function updateBulkBar() {
    const bar = document.getElementById('bulkBar');
    document.getElementById('selectedCount').textContent = selectedIds.size;
    if (selectedIds.size > 0) {
        bar.classList.remove('hidden');
    } else {
        bar.classList.add('hidden');
    }
}

function clearSelection() {
    selectedIds.clear();
    $('#history-table tbody .row-check').prop('checked', false);
    $('#selectAll').prop('checked', false).prop('indeterminate', false);
    updateBulkBar();
}

function hapusData(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: 'Data kunjungan ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/buku-tamu/' + id,
                type: 'DELETE',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                    selectedIds.delete(id);
                    updateBulkBar();
                    historyTable.draw(false);
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus data.' });
                }
            });
        }
    });
}

function bulkDelete() {
    if (selectedIds.size === 0) return;

    Swal.fire({
        title: 'Hapus ' + selectedIds.size + ' Data?',
        text: 'Semua data kunjungan yang dipilih akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus Semua',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '{{ route("admin.buku-tamu.bulk-delete") }}',
                type: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                data: { ids: Array.from(selectedIds) },
                success: function(res) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message, timer: 1500, showConfirmButton: false });
                    clearSelection();
                    historyTable.draw(false);
                },
                error: function() {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan saat menghapus data.' });
                }
            });
        }
    });
}

// Filter Modal
function openFilterModal() { document.getElementById('filterModal').classList.remove('hidden'); }
function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); }

function resetFilters() {
    $('#filter_status, #filter_tipe, #filter_tanggal_dari, #filter_tanggal_sampai').val('');
    historyTable.draw();
    closeFilterModal();
}

document.getElementById('filterForm').addEventListener('submit', function(e) {
    e.preventDefault();
    historyTable.draw();
    closeFilterModal();
});

document.getElementById('filterModal').addEventListener('click', function(e) {
    if (e.target === this) closeFilterModal();
});
</script>
@endsection
