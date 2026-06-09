@extends('layouts.admin')

@section('title', 'Laporan Anggota')
@section('page-title', 'Laporan Anggota')

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

    /* ── DataTables overrides ── */
    #tabelAnggota_wrapper .dataTables_length select,
    #tabelAnggota_wrapper .dataTables_filter input {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.35rem 0.65rem;
        font-size: 0.85rem;
        outline: none;
        transition: box-shadow .2s;
    }
    #tabelAnggota_wrapper .dataTables_filter input:focus {
        box-shadow: 0 0 0 3px rgba(99,102,241,.2);
        border-color: #3b82f6;
    }
    #tabelAnggota_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg,#3b82f6,#2563eb) !important;
    }
    #tabelAnggota_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #eff6ff !important;
        color: #2563eb !important;
        border-color: #bfdbfe !important;
    }
    table.dataTable thead th {
        border-bottom: 2px solid #dbeafe !important;
    }
    table.dataTable tbody tr:hover { background: #eff6ff !important; }
    .filter-badge {
        font-weight: 600; background: #eff6ff; color: #2563eb;
        border: 1px solid #bfdbfe;
    }
</style>
@endpush

@section('content')
<div class="space-y-5">

    {{-- ══════════════════════════════════════════
         HEADER BAR
    ══════════════════════════════════════════ --}}
    <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            {{-- Back + Title --}}
            <div class="flex items-center gap-3">
                <a href="{{ route('laporan.index') }}"
                   class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors shrink-0">
                    <i class="fas fa-arrow-left text-gray-600 text-sm"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 leading-tight">Laporan Anggota</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Total:
                        <span class="font-semibold text-blue-600">{{ $totalAnggota }}</span> anggota
                        @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','kelas_id']))
                        <span class="filter-badge ml-2"><i class="fas fa-filter text-[10px]"></i> Difilter</span>
                        @endif
                    </p>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex flex-wrap items-center gap-2">
                {{-- Filter Modal Trigger --}}
                <button onclick="openFilterModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all
                               {{ request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','kelas_id'])
                                  ? 'bg-blue-600 text-white border-blue-600 shadow-md shadow-blue-200'
                                  : 'bg-white text-gray-700 border-gray-200 hover:border-blue-400 hover:text-blue-600' }}">
                    <i class="fas fa-sliders-h"></i>
                    Filter
                    @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','kelas_id']))
                    <span class="w-5 h-5 rounded-full bg-white/30 text-xs font-bold flex items-center justify-center">
                        {{ collect(['tanggal_mulai','jenis_anggota','status','kelas_id'])->filter(fn($k)=>request()->filled($k))->count() }}
                    </span>
                    @endif
                </button>

                {{-- Reset Filter --}}
                @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','kelas_id']))
                <a href="{{ route('admin.laporan.anggota') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition-all">
                    <i class="fas fa-times"></i> Reset
                </a>
                @endif

                {{-- Export Excel --}}
                <a href="{{ route('admin.laporan.anggota', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-green-600 shadow-md hover:shadow-lg hover:opacity-90 transition-all">
                    <i class="fas fa-file-excel"></i> Excel
                </a>

                {{-- Export PDF --}}
                <a href="{{ route('admin.laporan.anggota', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-red-500 to-rose-600 shadow-md hover:shadow-lg hover:opacity-90 transition-all">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        {{-- Active filter pills --}}
        @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','kelas_id']))
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
            @if(request('tanggal_mulai') && request('tanggal_akhir'))
            <span class="filter-badge"><i class="fas fa-calendar-alt text-[10px]"></i> {{ request('tanggal_mulai') }} – {{ request('tanggal_akhir') }}</span>
            @endif
            @if(request('jenis_anggota'))
            <span class="filter-badge"><i class="fas fa-id-badge text-[10px]"></i> {{ ucfirst(request('jenis_anggota')) }}</span>
            @endif
            @if(request('status'))
            <span class="filter-badge"><i class="fas fa-circle text-[10px]"></i> {{ ucfirst(request('status')) }}</span>
            @endif
            @if(request('kelas_id'))
            <span class="filter-badge"><i class="fas fa-door-open text-[10px]"></i> {{ $kelas->firstWhere('id', request('kelas_id'))?->nama_kelas ?? 'Kelas' }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════
         STATS CARDS
    ══════════════════════════════════════════ --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        @foreach([
            ['icon'=>'fas fa-users',              'color'=>'blue',  'label'=>'Total',      'val'=>$totalAnggota,  'delay'=>'0s'],
            ['icon'=>'fas fa-user-graduate',       'color'=>'blue',    'label'=>'Siswa',      'val'=>$siswa,         'delay'=>'.05s'],
            ['icon'=>'fas fa-chalkboard-teacher',  'color'=>'blue',  'label'=>'Guru',       'val'=>$guru,          'delay'=>'.1s'],
            ['icon'=>'fas fa-briefcase',           'color'=>'blue',  'label'=>'Staff',      'val'=>$staff,         'delay'=>'.15s'],
            ['icon'=>'fas fa-check-circle',        'color'=>'emerald', 'label'=>'Aktif',      'val'=>$aktif,         'delay'=>'.2s'],
            ['icon'=>'fas fa-times-circle',        'color'=>'rose',    'label'=>'Nonaktif',   'val'=>$nonaktif,      'delay'=>'.25s'],
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

    {{-- ══════════════════════════════════════════
         DATA TABLE
    ══════════════════════════════════════════ --}}
    <div class="glass-card rounded-2xl shadow-lg overflow-hidden animate-fade" style="animation-delay:.3s">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-table text-blue-600 text-sm"></i>
                </div>
                <span class="font-semibold text-gray-800 text-sm">Data Anggota</span>
            </div>
            <span class="text-xs text-gray-400">{{ now()->format('d M Y') }}</span>
        </div>

        <div class="p-4 overflow-x-auto">
            <table id="tabelAnggota" class="w-full no-footer" style="width:100%">
                <thead>
                    <tr class="bg-gradient-to-r from-blue-50 to-blue-100">
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">No</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Nama Lengkap</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">No. Anggota</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">L/P</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Kelas / Jurusan</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Jenis</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-blue-700 uppercase tracking-wide whitespace-nowrap">Tgl Daftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     FILTER MODAL
══════════════════════════════════════════ --}}
<div id="filterModal"
     class="fixed inset-0 z-50 flex items-center justify-center px-4 opacity-0 pointer-events-none"
     onclick="closeFilterModalOutside(event)">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>

    {{-- Modal Box --}}
    <div id="filterModalBox"
         class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden scale-95 opacity-0">

        {{-- Modal Header --}}
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-700">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sliders-h text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Filter Laporan</h3>
                    <p class="text-xs text-blue-200">Sesuaikan data yang ditampilkan</p>
                </div>
            </div>
            <button onclick="closeFilterModal()"
                    class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors">
                <i class="fas fa-times text-white text-sm"></i>
            </button>
        </div>

        {{-- Modal Form --}}
        <form method="GET" action="{{ route('admin.laporan.anggota') }}" id="filterForm">
            <div class="px-6 py-5 space-y-4">

                {{-- Rentang Tanggal --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-2">
                        <i class="fas fa-calendar-alt text-blue-500 mr-1"></i> Rentang Tanggal Daftar
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

                {{-- Jenis Anggota --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-2">
                        <i class="fas fa-id-badge text-blue-500 mr-1"></i> Jenis Anggota
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['' => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru', 'staff' => 'Staff'] as $val => $label)
                        <label class="jenis-option cursor-pointer">
                            <input type="radio" name="jenis_anggota" value="{{ $val }}"
                                   {{ request('jenis_anggota', '') === $val ? 'checked' : '' }}
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

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-2">
                        <i class="fas fa-toggle-on text-blue-500 mr-1"></i> Status Anggota
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'ditangguhkan' => 'Tangguh'] as $val => $label)
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

                {{-- Kelas --}}
                @if($kelas->count())
                <div>
                    <label class="block text-xs font-semibold text-gray-900 mb-2">
                        <i class="fas fa-door-open text-blue-500 mr-1"></i> Kelas
                    </label>
                    <select name="kelas_id"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-blue-500 focus:border-blue-400 outline-none transition">
                        <option value="">Semua Kelas</option>
                        @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kelas }}{{ $k->jurusan ? ' – '.$k->jurusan->nama_jurusan : '' }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

            </div>

            {{-- Modal Footer --}}
            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('admin.laporan.anggota') }}"
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
    $('#tabelAnggota').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("admin.laporan.anggota") }}',
            type: 'GET',
            data: function(d) {
                var params = new URLSearchParams(window.location.search);
                d.tanggal_mulai = params.get('tanggal_mulai') || '';
                d.tanggal_akhir = params.get('tanggal_akhir') || '';
                d.jenis_anggota = params.get('jenis_anggota') || '';
                d.status = params.get('status') || '';
                d.kelas_id = params.get('kelas_id') || '';
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'nama_lengkap', name: 'nama_lengkap' },
            { data: 'nomor_anggota', name: 'nomor_anggota' },
            { data: 'jenis_kelamin_label', name: 'jenis_kelamin' },
            { data: 'kelas_jurusan', name: 'kelas.nama_kelas' },
            { data: 'jenis_anggota_label', name: 'jenis_anggota' },
            { data: 'status_label', name: 'status' },
            { data: 'tanggal_daftar', name: 'created_at' },
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
            search: 'Cari:',
            zeroRecords: 'Tidak ada data ditemukan',
            emptyTable: 'Tidak ada data',
        },
        pagingType: 'simple_numbers',
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [[1, 'asc']],
        dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4"lf>t<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4"<"text-xs text-gray-400"i><"dt-pager"p>>',
        drawCallback: function () {
            var api = this.api();
            var info = api.page.info();
            var current = info.page + 1;
            var total = info.pages;
            var start = current;
            var end = Math.min(current + 1, total);
            $('#tabelAnggota_wrapper').find('.paginate_button')
                .not('.previous, .next')
                .each(function () {
                    var num = parseInt($(this).text());
                    $(this).toggle(num >= start && num <= end);
                });
        },
    });
});

// ── Modal helpers ──
function openFilterModal() {
    const modal = document.getElementById('filterModal');
    const box   = document.getElementById('filterModalBox');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    box.classList.remove('scale-95', 'opacity-0');
    document.body.style.overflow = 'hidden';

    var akhirInput = document.querySelector('[name="tanggal_akhir"]');
    if (akhirInput && !akhirInput.value) {
        var today = new Date();
        var yyyy = today.getFullYear();
        var mm = String(today.getMonth() + 1).padStart(2, '0');
        var dd = String(today.getDate()).padStart(2, '0');
        akhirInput.value = yyyy + '-' + mm + '-' + dd;
    }
}

function closeFilterModal() {
    const modal = document.getElementById('filterModal');
    const box   = document.getElementById('filterModalBox');
    box.classList.add('scale-95', 'opacity-0');
    modal.classList.add('opacity-0');
    setTimeout(() => modal.classList.add('pointer-events-none'), 260);
    document.body.style.overflow = '';
}

function closeFilterModalOutside(e) {
    if (e.target === document.getElementById('filterModal')) closeFilterModal();
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeFilterModal();
});

// Auto-open modal if filter was active but returned empty
@if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','kelas_id']) && $totalAnggota < 1)
openFilterModal();
@endif
</script>
@endpush
