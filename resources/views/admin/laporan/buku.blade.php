@extends('layouts.admin')

@section('title', 'Laporan Buku')
@section('page-title', 'Laporan Buku')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.dataTables.min.css">
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
    #tabelBuku_wrapper .dataTables_length select,
    #tabelBuku_wrapper .dataTables_filter input {
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        padding: 0.35rem 0.65rem;
        font-size: 0.85rem;
        outline: none;
        transition: box-shadow .2s;
    }
    #tabelBuku_wrapper .dataTables_filter input:focus {
        box-shadow: 0 0 0 3px rgba(236,72,153,.18);
        border-color: #ec4899;
    }
    #tabelBuku_wrapper .dataTables_info,
    #tabelBuku_wrapper .dataTables_length,
    #tabelBuku_wrapper .dataTables_filter { font-size: 0.82rem; color: #6b7280; }
    #tabelBuku_wrapper .dataTables_paginate .paginate_button {
        border-radius: .5rem !important;
        font-size: .8rem !important;
        padding: .3rem .65rem !important;
        margin: 0 2px !important;
        border: 1px solid transparent !important;
        transition: all .2s !important;
    }
    #tabelBuku_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg,#ec4899,#f43f5e) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    #tabelBuku_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #fdf2f8 !important;
        color: #be185d !important;
        border-color: #fbcfe8 !important;
    }
    table.dataTable thead th { border-bottom: 2px solid #fce7f3 !important; }
    table.dataTable tbody tr:hover { background: #fdf2f8 !important; }
    table.dataTable.no-footer { border-bottom: none !important; }

    /* ── Modal ── */
    #filterModal { transition: opacity .25s ease; }
    #filterModalBox { transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease; }

    .filter-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 9999px; font-size: 0.72rem;
        font-weight: 600; background: #fdf2f8; color: #be185d;
        border: 1px solid #fbcfe8;
    }
</style>
@endpush

@section('content')
@php
    $totalBuku    = $buku->count();
    $totalEks     = $buku->sum('jumlah_stok');
    $tersedia     = $buku->where('stok_tersedia', '>', 0)->count();
    $habis        = $buku->where('stok_tersedia', 0)->count();
    $dipinjam     = $buku->sum('jumlah_stok') - $buku->sum('stok_tersedia');
    $dipinjam     = max(0, $dipinjam);
@endphp

<div class="space-y-5">

    {{-- ══ HEADER BAR ══ --}}
    <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">

            <div class="flex items-center gap-3">
                <a href="{{ route('laporan.index') }}"
                   class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors shrink-0">
                    <i class="fas fa-arrow-left text-gray-600 text-sm"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 leading-tight">Laporan Buku</h1>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Total:
                        <span class="font-semibold text-pink-600">{{ $totalBuku }}</span> judul
                        @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','kategori_id','jenis_buku_id','status']))
                        <span class="filter-badge ml-2"><i class="fas fa-filter text-[10px]"></i> Difilter</span>
                        @endif
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                <button onclick="openFilterModal()"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition-all
                               {{ request()->hasAny(['tanggal_mulai','tanggal_akhir','kategori_id','jenis_buku_id','status'])
                                  ? 'bg-pink-600 text-white border-pink-600 shadow-md shadow-pink-200'
                                  : 'bg-white text-gray-700 border-gray-200 hover:border-pink-400 hover:text-pink-600' }}">
                    <i class="fas fa-sliders-h"></i>
                    Filter
                    @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','kategori_id','jenis_buku_id','status']))
                    <span class="w-5 h-5 rounded-full bg-white/30 text-xs font-bold flex items-center justify-center">
                        {{ collect(['tanggal_mulai','kategori_id','jenis_buku_id','status'])->filter(fn($k)=>request()->filled($k))->count() }}
                    </span>
                    @endif
                </button>

                @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','kategori_id','jenis_buku_id','status']))
                <a href="{{ route('admin.laporan.buku') }}"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-red-600 bg-red-50 border border-red-200 hover:bg-red-100 transition-all">
                    <i class="fas fa-times"></i> Reset
                </a>
                @endif

                <a href="{{ route('admin.laporan.buku', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-emerald-500 to-green-600 shadow-md hover:shadow-lg hover:opacity-90 transition-all">
                    <i class="fas fa-file-excel"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.buku', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-white bg-gradient-to-r from-red-500 to-rose-600 shadow-md hover:shadow-lg hover:opacity-90 transition-all">
                    <i class="fas fa-file-pdf"></i> PDF
                </a>
            </div>
        </div>

        {{-- Active filter pills --}}
        @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','kategori_id','jenis_buku_id','status']))
        <div class="flex flex-wrap gap-2 mt-4 pt-4 border-t border-gray-100">
            @if(request('tanggal_mulai') && request('tanggal_akhir'))
            <span class="filter-badge"><i class="fas fa-calendar-alt text-[10px]"></i> {{ request('tanggal_mulai') }} – {{ request('tanggal_akhir') }}</span>
            @endif
            @if(request('kategori_id'))
            <span class="filter-badge"><i class="fas fa-tag text-[10px]"></i> {{ $kategori->firstWhere('id', request('kategori_id'))?->nama_kategori ?? 'Kategori' }}</span>
            @endif
            @if(request('jenis_buku_id'))
            <span class="filter-badge"><i class="fas fa-bookmark text-[10px]"></i> {{ $jenis->firstWhere('id', request('jenis_buku_id'))?->nama_jenis ?? 'Jenis' }}</span>
            @endif
            @if(request('status'))
            <span class="filter-badge"><i class="fas fa-circle text-[10px]"></i> {{ request('status') === 'tersedia' ? 'Tersedia' : 'Habis/Dipinjam' }}</span>
            @endif
        </div>
        @endif
    </div>

    {{-- ══ STATS CARDS ══ --}}
    <div class="grid grid-cols-2 md:grid-cols-2 xl:grid-cols-4 gap-3">
        @foreach([
            ['icon'=>'fas fa-book',         'color'=>'pink',    'label'=>'Judul Buku',      'val'=>$totalBuku,  'delay'=>'0s'],
            ['icon'=>'fas fa-layer-group',   'color'=>'blue',    'label'=>'Total Eksemplar', 'val'=>$totalEks,   'delay'=>'.05s'],
            ['icon'=>'fas fa-check-circle',  'color'=>'emerald', 'label'=>'Tersedia',        'val'=>$tersedia,   'delay'=>'.1s'],
            ['icon'=>'fas fa-times-circle',  'color'=>'rose',    'label'=>'Stok Habis',      'val'=>$habis,      'delay'=>'.15s'],
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

    {{-- ══ DATA TABLE ══ --}}
    <div class="glass-card rounded-2xl shadow-lg overflow-hidden animate-fade" style="animation-delay:.2s">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-pink-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-table text-pink-600 text-sm"></i>
                </div>
                <span class="font-semibold text-gray-800 text-sm">Data Buku</span>
            </div>
            <span class="text-xs text-gray-400">{{ now()->format('d M Y') }}</span>
        </div>

        <div class="p-4 overflow-x-auto">
            <table id="tabelBuku" class="w-full no-footer" style="width:100%">
                <thead>
                    <tr class="bg-gradient-to-r from-pink-50 to-rose-50">
                        <th class="px-3 py-3 text-left text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">No</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">Judul Buku</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">ISBN</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">Pengarang</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">Kategori</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">Jenis</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">Total Eks.</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">Tersedia</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-pink-700 uppercase tracking-wide whitespace-nowrap">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($buku as $index => $item)
                    @php
                        $stokTotal    = $item->jumlah_stok ?? 0;
                        $stokTersedia = $item->stok_tersedia ?? 0;
                        $stokDipinjam = max(0, $stokTotal - $stokTersedia);
                    @endphp
                    <tr class="hover:bg-pink-50/40 transition-colors">
                        <td class="px-3 py-3 text-gray-500 text-center w-10">{{ $index + 1 }}</td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 bg-pink-100 text-pink-700">
                                    <i class="fas fa-book text-[10px]"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900 leading-tight">{{ $item->judul_buku }}</div>
                                    <div class="text-xs text-gray-400">{{ $item->penerbit }} @if($item->tahun_terbit)({{ $item->tahun_terbit }})@endif</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-3 font-mono text-gray-600 text-xs">{{ $item->isbn ?? '—' }}</td>
                        <td class="px-3 py-3 text-gray-600 text-xs">{{ $item->pengarang ?? '—' }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-pink-50 text-pink-700 border border-pink-200">
                                {{ $item->kategoriBuku->nama_kategori ?? '—' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-gray-600 text-xs">{{ $item->jenisBuku->nama_jenis ?? '—' }}</td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-bold text-gray-800">{{ $stokTotal }}</span>
                            @if($stokDipinjam > 0)
                            <div class="text-xs text-amber-500">{{ $stokDipinjam }} dipinjam</div>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="font-bold {{ $stokTersedia > 0 ? 'text-emerald-600' : 'text-red-500' }}">
                                {{ $stokTersedia }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            @if($stokTersedia > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                                </span>
                            @elseif($stokTotal > 0)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Dipinjam
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis
                                </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-book text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                            <p class="text-sm text-gray-500">Tidak ada buku yang sesuai dengan filter</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ══ FILTER MODAL ══ --}}
<div id="filterModal"
     class="fixed inset-0 z-50 flex items-center justify-center px-4 opacity-0 pointer-events-none"
     onclick="closeFilterModalOutside(event)">
    <div class="absolute inset-0 bg-gray-900/40 backdrop-blur-sm"></div>

    <div id="filterModalBox"
         class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl overflow-hidden scale-95 opacity-0">

        {{-- Header --}}
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-pink-600 to-rose-600">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sliders-h text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Filter Laporan Buku</h3>
                    <p class="text-xs text-pink-200">Sesuaikan data yang ditampilkan</p>
                </div>
            </div>
            <button onclick="closeFilterModal()"
                    class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center transition-colors">
                <i class="fas fa-times text-white text-sm"></i>
            </button>
        </div>

        {{-- Form --}}
        <form method="GET" action="{{ route('admin.laporan.buku') }}">
            <div class="px-6 py-5 space-y-4">

                {{-- Rentang Tanggal --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-pink-500 mr-1"></i> Rentang Tanggal Input
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Dari</label>
                            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-400 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 focus:border-pink-400 outline-none transition">
                        </div>
                    </div>
                </div>

                {{-- Kategori --}}
                @if($kategori->count())
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-tag text-pink-500 mr-1"></i> Kategori Buku
                    </label>
                    <select name="kategori_id"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-400 outline-none transition">
                        <option value="">Semua Kategori</option>
                        @foreach($kategori as $k)
                        <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>
                            {{ $k->nama_kategori }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Jenis Buku --}}
                @if($jenis->count())
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-bookmark text-pink-500 mr-1"></i> Jenis Buku
                    </label>
                    <select name="jenis_buku_id"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-pink-500 focus:border-pink-400 outline-none transition">
                        <option value="">Semua Jenis</option>
                        @foreach($jenis as $j)
                        <option value="{{ $j->id }}" {{ request('jenis_buku_id') == $j->id ? 'selected' : '' }}>
                            {{ $j->nama_jenis }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Status Stok --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-boxes text-pink-500 mr-1"></i> Status Stok
                    </label>
                    <div class="grid grid-cols-3 gap-2">
                        @foreach(['' => 'Semua', 'tersedia' => 'Tersedia', 'dipinjam' => 'Habis'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="{{ $val }}"
                                   {{ request('status', '') === $val ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="text-center px-2 py-2 rounded-xl border text-xs font-medium transition-all
                                        peer-checked:bg-pink-600 peer-checked:text-white peer-checked:border-pink-600
                                        border-gray-200 text-gray-600 hover:border-pink-400 hover:text-pink-600">
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between px-6 py-4 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('admin.laporan.buku') }}"
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium text-gray-600 bg-white border border-gray-200 hover:border-red-300 hover:text-red-600 transition-all">
                    <i class="fas fa-undo text-xs"></i> Reset Filter
                </a>
                <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-pink-600 to-rose-600 shadow-md shadow-pink-200 hover:shadow-lg hover:opacity-90 transition-all">
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
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script>
$(document).ready(function () {
    $('#tabelBuku').DataTable({
        responsive: true,
        language: { url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json' },
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [],
        columnDefs: [{ orderable: false, targets: [0] }],
        dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4"lf>t<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4"ip>',
    });
});

function openFilterModal() {
    const modal = document.getElementById('filterModal');
    const box   = document.getElementById('filterModalBox');
    modal.classList.remove('opacity-0', 'pointer-events-none');
    box.classList.remove('scale-95', 'opacity-0');
    document.body.style.overflow = 'hidden';
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

@if(request()->hasAny(['tanggal_mulai','tanggal_akhir','kategori_id','jenis_buku_id','status']) && $buku->isEmpty())
openFilterModal();
@endif
</script>
@endpush
