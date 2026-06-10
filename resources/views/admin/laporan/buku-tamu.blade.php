@extends('layouts.admin')

@section('title', 'Laporan Buku Tamu')
@section('page-title', 'Laporan Buku Tamu')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
    .glass-card {
        background: rgba(255,255,255,0.9);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.4);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(16px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .animate-fade { animation: fadeInUp 0.45s ease forwards; }

    #tabelBukuTamu_wrapper .dataTables_length select,
    #tabelBukuTamu_wrapper .dataTables_filter input {
        border-radius: 0.5rem;
        padding: 0.40rem 0.70rem;
        font-size: 0.85rem;
        outline: none;
        transition: box-shadow .2s;
    }
    #tabelBukuTamu_wrapper .dataTables_filter input:focus {
        box-shadow: 0 0 0 3px rgba(99,102,241,.2);
        border-color: #3b82f6;
    }
    #tabelBukuTamu_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #eff6ff !important;
        color: #2563eb !important;
        border-color: #bfdbfe !important;
    }
    table.dataTable thead th { border-bottom: 2px solid #dbeafe !important; }
    table.dataTable tbody tr:hover { background: #eff6ff !important; }

    .dt-toolbar { position: sticky; left: 0; z-index: 5; }
    .dt-bottom  { position: sticky; left: 0; z-index: 5; }
    .dt-table-scroll { -webkit-overflow-scrolling: touch; width: 100%; }
    #tabelBukuTamu_wrapper { max-width: 100%; width: 100%; }
    #tabelBukuTamu_wrapper table.dataTable { width: 100% !important; }
    .dt-length-wrap, .dt-search-wrap { flex-shrink: 0; }
    .dt-actions { gap: 6px; }
    .dt-buttons-wrap { display: flex; align-items: center; gap: 6px; white-space: nowrap; }
    .dt-search-wrap .dataTables_filter { float: none; text-align: inherit; }
    .dt-length-wrap .dataTables_length { float: none; }
    .dt-length-wrap .dataTables_length label { display: flex; align-items: center; gap: 0.25rem; white-space: nowrap; }
    .dt-search-wrap .dataTables_filter input { width: 160px; }
    @media (max-width: 480px) {
        #tabelBukuTamu_wrapper .dt-toolbar .dataTables_filter input { width: 90px; font-size: 0.7rem !important; padding: 0.2rem 0.4rem !important; }
        #tabelBukuTamu_wrapper .dt-toolbar .dataTables_length select { padding: 0.2rem 1.25rem 0.2rem 0.35rem !important; font-size: 0.7rem !important; }
        .dt-toolbar .dt-buttons-wrap a,
        .dt-toolbar .dt-buttons-wrap button { font-size: 0.7rem !important; padding: 0.25rem 0.4rem !important; }
        .dt-toolbar .dt-buttons-wrap { gap: 4px; }
        .dt-bottom { font-size: 0.7rem; }
    }
    @media (max-width: 370px) {
        #tabelBukuTamu_wrapper .dt-toolbar .dataTables_length select,
        #tabelBukuTamu_wrapper .dt-toolbar .dataTables_filter input,
        .dt-toolbar .dt-buttons-wrap a,
        .dt-toolbar .dt-buttons-wrap button {
            font-size: 7px !important;
            padding: 2px 3px !important;
        }
        .dt-toolbar .dt-buttons-wrap a,
        .dt-toolbar .dt-buttons-wrap button { gap: 2px !important; }
        .dt-toolbar .dt-buttons-wrap { gap: 2px; }
        #tabelBukuTamu_wrapper .dt-toolbar .dataTables_filter input { width: 60px !important; }
        #tabelBukuTamu_wrapper .dt-toolbar .dataTables_length select { padding: 0.15rem 0.8rem 0.15rem 0.2rem !important; }
        .dt-bottom { font-size: 0.5rem; }
    }
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- STATS CARDS --}}
    <div class="hidden md:grid grid-cols-2 md:grid-cols-5 gap-3">
        @foreach([
            ['icon'=>'fas fa-book',           'color'=>'blue',  'label'=>'Total Kunjungan','val'=>$totalKunjungan,'delay'=>'0s'],
            ['icon'=>'fas fa-user-graduate',   'color'=>'blue',  'label'=>'Anggota',        'val'=>$anggota,      'delay'=>'.05s'],
            ['icon'=>'fas fa-user',            'color'=>'gray',  'label'=>'Tamu Umum',      'val'=>$umum,         'delay'=>'.1s'],
            ['icon'=>'fas fa-sign-in-alt',     'color'=>'emerald','label'=>'Berkunjung',    'val'=>$berkunjung,   'delay'=>'.15s'],
            ['icon'=>'fas fa-sign-out-alt',    'color'=>'gray',  'label'=>'Sudah Pulang',   'val'=>$sudahPulang,  'delay'=>'.2s'],
        ] as $card)
        <div class="glass-card rounded-2xl p-4 animate-fade shadow-sm hover:shadow-md transition-shadow"
             style="animation-delay:{{ $card['delay'] }}">
            <div class="flex flex-col gap-2">
                <div class="w-9 h-9 rounded-xl flex items-center justify-center bg-{{ $card['color'] }}-100">
                    <i class="{{ $card['icon'] }} text-{{ $card['color'] }}-600 text-base"></i>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $card['val'] }}</p>
                    <p class="text-xs text-gray-500 mt-0.5">{{ $card['label'] }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Hidden action buttons --}}
    <div id="tableActions" class="hidden flex flex-wrap items-center gap-2">
        <button onclick="openFilterModal()"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all
                       {{ request()->hasAny(['tanggal_mulai','tanggal_akhir','tipe','status'])
                          ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200'
                          : 'bg-white text-gray-700 border-gray-200 hover:border-blue-400 hover:text-blue-600' }}">
            <i class="fas fa-sliders-h"></i>
            Filter
            @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','tipe','status']))
            <span class="w-5 h-5 rounded-full bg-white/30 text-xs font-bold flex items-center justify-center">
                {{ collect(['tanggal_mulai','tipe','status'])->filter(fn($k)=>request()->filled($k))->count() }}
            </span>
            @endif
        </button>

        @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','tipe','status']))
        <a href="{{ route('admin.laporan.buku-tamu') }}"
           class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition-all">
            <i class="fas fa-times"></i> Reset
        </a>
        @endif

        <a href="{{ request()->fullUrlWithQuery(['export' => 'excel']) }}"
           data-spa-ignore
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-green-600 shadow-md hover:shadow-lg hover:opacity-90 transition-all">
            <i class="fas fa-file-excel"></i> Excel
        </a>

        <a href="{{ request()->fullUrlWithQuery(['export' => 'pdf']) }}"
           data-spa-ignore
           class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-red-500 to-rose-600 shadow-md hover:shadow-lg hover:opacity-90 transition-all">
            <i class="fas fa-file-pdf"></i> PDF
        </a>
    </div>

    <div class="glass-card rounded-2xl shadow-lg overflow-hidden animate-fade" style="animation-delay:.3s">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-table text-blue-600 text-sm"></i>
                </div>
                <span class="font-semibold text-gray-800 text-sm">Data Buku Tamu</span>
            </div>
            <span class="text-xs text-gray-400">{{ now()->format('d M Y') }}</span>
        </div>

        <div class="p-4">
            <table id="tabelBukuTamu" class="w-full no-footer" style="width:100%">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-50 to-blue-100">
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">No</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Tanggal</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Jam Datang</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Jam Pulang</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Nama</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Tipe</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Kelas/Instansi</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Keperluan</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- FILTER MODAL --}}
<div id="filterModal"
     class="fixed inset-0 z-50 flex items-center justify-center px-4 opacity-0 pointer-events-none"
     onclick="closeFilterModalOutside(event)">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>

    <div id="filterModalBox"
         class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden scale-95 opacity-0">

        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sliders-h text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Filter Laporan Buku Tamu</h3>
                    <p class="text-xs text-blue-200">Sesuaikan data yang ditampilkan</p>
                </div>
            </div>
            <button onclick="closeFilterModal()"
                    class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors">
                <i class="fas fa-times text-white text-sm"></i>
            </button>
        </div>

        <form method="GET" action="{{ route('admin.laporan.buku-tamu') }}" id="filterForm" data-spa-ignore>
            <div class="px-6 py-5 space-y-4">

                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-2">
                        <i class="fas fa-calendar-alt text-blue-500 mr-1"></i> Rentang Tanggal Kunjungan
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-900 mb-1">Dari</label>
                            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-900 mb-1">Sampai</label>
                            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') ?: date('Y-m-d') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition">
                        </div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-2">
                        <i class="fas fa-id-badge text-blue-500 mr-1"></i> Tipe Pengunjung
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['' => 'Semua', 'anggota' => 'Anggota', 'umum' => 'Tamu Umum'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="tipe" value="{{ $val }}"
                                   {{ request('tipe', '') === $val ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="text-center px-2 py-2 rounded-xl border text-xs font-medium transition-all
                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                        border-gray-200 text-gray-900 hover:border-blue-400 hover:text-blue-600">
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-2">
                        <i class="fas fa-toggle-on text-blue-500 mr-1"></i> Status Kunjungan
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['' => 'Semua', 'berkunjung' => 'Berkunjung', 'pulang' => 'Sudah Pulang'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="{{ $val }}"
                                   {{ request('status', '') === $val ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="text-center px-2 py-2 rounded-xl border text-xs font-medium transition-all
                                        peer-checked:bg-blue-600 peer-checked:text-white peer-checked:border-blue-600
                                        border-gray-200 text-gray-900 hover:border-blue-400 hover:text-blue-600">
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('admin.laporan.buku-tamu') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:border-red-300 hover:text-red-600 transition-all">
                    <i class="fas fa-undo text-xs"></i> Reset Filter
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-blue-600 to-blue-700 shadow-md shadow-blue-200 hover:shadow-lg hover:opacity-90 transition-all">
                    <i class="fas fa-filter text-xs"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#tabelBukuTamu').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.laporan.buku-tamu") }}',
            type: 'GET',
            data: function(d) {
                var params = new URLSearchParams(window.location.search);
                d.tanggal_mulai = params.get('tanggal_mulai') || '';
                d.tanggal_akhir = params.get('tanggal_akhir') || '';
                d.tipe = params.get('tipe') || '';
                d.status = params.get('status') || '';
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'tanggal', name: 'waktu_datang' },
            { data: 'waktu_datang', name: 'waktu_datang' },
            { data: 'waktu_pulang', name: 'waktu_pulang' },
            { data: 'nama_info', name: 'nama_tamu' },
            { data: 'tipe_label', name: 'anggota_id' },
            { data: 'kelas_info', name: 'instansi' },
            { data: 'keperluan', name: 'keperluan' },
            { data: 'status_label', name: 'status_kunjungan' },
        ],
        language: {
            processing: '<div class="flex items-center justify-center py-3"><div class="w-5 h-5 rounded-full border-2 border-blue-200 border-t-blue-600 animate-spin"></div><span class="ml-2 text-xs text-gray-500">Memuat...</span></div>',
            paginate: {
                previous: '<i class="fas fa-angle-left"></i>',
                next: '<i class="fas fa-angle-right"></i>',
            },
            info: 'Menampilkan _START_–_END_ dari <b>_TOTAL_</b>',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_)',
            lengthMenu: '_MENU_',
            search: '',
            searchPlaceholder: 'Cari...',
            zeroRecords: 'Tidak ada data ditemukan',
            emptyTable: 'Tidak ada data',
        },
        scrollX: true,
        pagingType: 'simple_numbers',
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[1, 'desc']],
        dom: '<"flex flex-row items-center justify-between gap-2 mb-4 dt-toolbar"<"dt-length-wrap"l><"flex flex-row items-center gap-2 dt-actions"<"dt-search-wrap"f><"dt-buttons-wrap">>><"overflow-x-auto dt-table-scroll"t><"flex flex-row items-center justify-between gap-2 mt-4 dt-bottom"<"text-xs text-gray-400"i><"dt-pager"p>>',
        initComplete: function() {
            $('#tableActions').children().appendTo('.dt-buttons-wrap');
        },
        drawCallback: function () {
            var api = this.api();
            var info = api.page.info();
            var current = info.page + 1;
            var total = info.pages;
            var start = current;
            var end = Math.min(current + 1, total);
            $('#tabelBukuTamu_wrapper').find('.paginate_button')
                .not('.previous, .next')
                .each(function () {
                    var num = parseInt($(this).text());
                    $(this).toggle(num >= start && num <= end);
                });
        },
    });
});

function openFilterModal() {
    const modal = document.getElementById('filterModal');
    const box   = document.getElementById('filterModalBox');
    if (!modal || !box) return;
    modal.classList.remove('opacity-0', 'pointer-events-none');
    box.classList.remove('scale-95', 'opacity-0');
    document.body.style.overflow = 'hidden';
    try {
        var akhirInput = document.querySelector('[name="tanggal_akhir"]');
        if (akhirInput && !akhirInput.value) {
            var today = new Date();
            today.setHours(0, 0, 0, 0);
            var iso = today.toISOString().slice(0, 10);
            akhirInput.value = iso;
        }
    } catch(e) { console.warn(e); }
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    const box   = document.getElementById('filterModalBox');
    if (!modal || !box) return;
    box.classList.add('scale-95', 'opacity-0');
    modal.classList.add('opacity-0');
    setTimeout(function() {
        if (modal) modal.classList.add('pointer-events-none');
    }, 260);
    document.body.style.overflow = '';
}

function closeFilterModalOutside(e) {
    if (e.target === document.getElementById('filterModal')) closeFilterModal();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeFilterModal(); }
});
</script>
@endpush