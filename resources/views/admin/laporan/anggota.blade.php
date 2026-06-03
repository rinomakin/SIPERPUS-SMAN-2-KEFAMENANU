@extends('layouts.admin')

@section('title', 'Laporan Anggota')
@section('page-title', 'Laporan Anggota')

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
        border-color: #6366f1;
    }
    #tabelAnggota_wrapper .dataTables_info,
    #tabelAnggota_wrapper .dataTables_length,
    #tabelAnggota_wrapper .dataTables_filter { font-size: 0.82rem; color: #6b7280; }
    #tabelAnggota_wrapper .dataTables_paginate .paginate_button {
        border-radius: .5rem !important;
        font-size: .8rem !important;
        padding: .3rem .65rem !important;
        margin: 0 2px !important;
        border: 1px solid transparent !important;
        transition: all .2s !important;
    }
    #tabelAnggota_wrapper .dataTables_paginate .paginate_button.current {
        background: linear-gradient(135deg,#6366f1,#8b5cf6) !important;
        color: #fff !important;
        border-color: transparent !important;
    }
    #tabelAnggota_wrapper .dataTables_paginate .paginate_button:hover:not(.current) {
        background: #eef2ff !important;
        color: #4f46e5 !important;
        border-color: #c7d2fe !important;
    }
    table.dataTable thead th {
        border-bottom: 2px solid #e0e7ff !important;
    }
    table.dataTable tbody tr:hover { background: #f5f3ff !important; }
    table.dataTable.no-footer { border-bottom: none !important; }

    /* ── Modal backdrop ── */
    #filterModal { transition: opacity .25s ease; }
    #filterModalBox { transition: transform .3s cubic-bezier(.34,1.56,.64,1), opacity .25s ease; }

    /* ── Active filter badge ── */
    .filter-badge {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 2px 8px; border-radius: 9999px; font-size: 0.72rem;
        font-weight: 600; background: #eef2ff; color: #4f46e5;
        border: 1px solid #c7d2fe;
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
                        <span class="font-semibold text-indigo-600">{{ $anggota->count() }}</span> anggota
                        @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','jurusan_id','kelas_id']))
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
                               {{ request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','jurusan_id','kelas_id'])
                                  ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-200'
                                  : 'bg-white text-gray-700 border-gray-200 hover:border-indigo-400 hover:text-indigo-600' }}">
                    <i class="fas fa-sliders-h"></i>
                    Filter
                    @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','jurusan_id','kelas_id']))
                    <span class="w-5 h-5 rounded-full bg-white/30 text-xs font-bold flex items-center justify-center">
                        {{ collect(['tanggal_mulai','jenis_anggota','status','jurusan_id','kelas_id'])->filter(fn($k)=>request()->filled($k))->count() }}
                    </span>
                    @endif
                </button>

                {{-- Reset Filter --}}
                @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','jurusan_id','kelas_id']))
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
        @if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','jurusan_id','kelas_id']))
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
            @if(request('jurusan_id'))
            <span class="filter-badge"><i class="fas fa-graduation-cap text-[10px]"></i> {{ $jurusan->firstWhere('id', request('jurusan_id'))?->nama_jurusan ?? 'Jurusan' }}</span>
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
    @php
        $totalAnggota = $anggota->count();
        $siswa        = $anggota->where('jenis_anggota', 'siswa')->count();
        $guru         = $anggota->where('jenis_anggota', 'guru')->count();
        $staff        = $anggota->where('jenis_anggota', 'staff')->count();
        $aktif        = $anggota->where('status', 'aktif')->count();
        $nonaktif     = $anggota->where('status', 'nonaktif')->count();
    @endphp
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-3">
        @foreach([
            ['icon'=>'fas fa-users',              'color'=>'indigo',  'label'=>'Total',      'val'=>$totalAnggota,  'delay'=>'0s'],
            ['icon'=>'fas fa-user-graduate',       'color'=>'blue',    'label'=>'Siswa',      'val'=>$siswa,         'delay'=>'.05s'],
            ['icon'=>'fas fa-chalkboard-teacher',  'color'=>'violet',  'label'=>'Guru',       'val'=>$guru,          'delay'=>'.1s'],
            ['icon'=>'fas fa-briefcase',           'color'=>'purple',  'label'=>'Staff',      'val'=>$staff,         'delay'=>'.15s'],
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
                <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-table text-indigo-600 text-sm"></i>
                </div>
                <span class="font-semibold text-gray-800 text-sm">Data Anggota</span>
            </div>
            <span class="text-xs text-gray-400">{{ now()->format('d M Y') }}</span>
        </div>

        <div class="p-4 overflow-x-auto">
            <table id="tabelAnggota" class="w-full no-footer" style="width:100%">
                <thead>
                    <tr class="bg-gradient-to-r from-indigo-50 to-purple-50">
                        <th class="px-3 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">No</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">Nama Lengkap</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">NIS / NIK</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">No. Anggota</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">L/P</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">Kelas / Jurusan</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">Jenis</th>
                        <th class="px-3 py-3 text-center text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">Status</th>
                        <th class="px-3 py-3 text-left text-xs font-semibold text-indigo-700 uppercase tracking-wide whitespace-nowrap">Tgl Daftar</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($anggota as $index => $item)
                    <tr class="hover:bg-indigo-50/40 transition-colors">
                        <td class="px-3 py-3 text-gray-500 text-center w-10">{{ $index + 1 }}</td>
                        <td class="px-3 py-3">
                            <div class="flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold shrink-0
                                    {{ $item->jenis_anggota == 'siswa' ? 'bg-blue-100 text-blue-700' :
                                       ($item->jenis_anggota == 'guru' ? 'bg-violet-100 text-violet-700' : 'bg-purple-100 text-purple-700') }}">
                                    {{ strtoupper(substr($item->nama_lengkap, 0, 1)) }}
                                </div>
                                <span class="font-medium text-gray-900">{{ $item->nama_lengkap }}</span>
                            </div>
                        </td>
                        <td class="px-3 py-3 font-mono text-gray-600 text-xs">{{ $item->nis ?: $item->nomor_anggota }}</td>
                        <td class="px-3 py-3 font-mono text-gray-600 text-xs">{{ $item->nomor_anggota }}</td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold
                                {{ $item->jenis_kelamin == 'Laki-laki' ? 'bg-sky-100 text-sky-700' : 'bg-pink-100 text-pink-700' }}">
                                {{ $item->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-gray-600 text-xs">
                            @if($item->kelas)
                                <span class="font-medium">{{ $item->kelas->nama_kelas }}</span>
                                @if($item->kelas->jurusan)
                                <br><span class="text-gray-400">{{ $item->kelas->jurusan->nama_jurusan }}</span>
                                @endif
                            @else
                                <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border
                                {{ $item->jenis_anggota == 'siswa'  ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                   ($item->jenis_anggota == 'guru'  ? 'bg-violet-50 text-violet-700 border-violet-200'
                                                                     : 'bg-purple-50 text-purple-700 border-purple-200') }}">
                                {{ ucfirst($item->jenis_anggota) }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-center">
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border
                                {{ $item->status == 'aktif'       ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                   ($item->status == 'nonaktif'   ? 'bg-red-50 text-red-700 border-red-200'
                                                                  : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                <span class="w-1.5 h-1.5 rounded-full
                                    {{ $item->status == 'aktif' ? 'bg-emerald-500' : ($item->status == 'nonaktif' ? 'bg-red-500' : 'bg-amber-500') }}">
                                </span>
                                {{ ucfirst($item->status) }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-gray-500 text-xs whitespace-nowrap">
                            {{ $item->created_at ? $item->created_at->format('d/m/Y') : '—' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-users text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="text-base font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                            <p class="text-sm text-gray-500">Tidak ada anggota yang sesuai dengan filter</p>
                        </td>
                    </tr>
                    @endforelse
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
        <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/20 rounded-lg flex items-center justify-center">
                    <i class="fas fa-sliders-h text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-base font-bold text-white">Filter Laporan</h3>
                    <p class="text-xs text-indigo-200">Sesuaikan data yang ditampilkan</p>
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
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt text-indigo-500 mr-1"></i> Rentang Tanggal Daftar
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Dari</label>
                            <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Sampai</label>
                            <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                                   class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition">
                        </div>
                    </div>
                </div>

                {{-- Jenis Anggota --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-id-badge text-indigo-500 mr-1"></i> Jenis Anggota
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['' => 'Semua', 'siswa' => 'Siswa', 'guru' => 'Guru', 'staff' => 'Staff'] as $val => $label)
                        <label class="jenis-option cursor-pointer">
                            <input type="radio" name="jenis_anggota" value="{{ $val }}"
                                   {{ request('jenis_anggota', '') === $val ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="text-center px-2 py-2 rounded-xl border text-xs font-medium transition-all
                                        peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600
                                        border-gray-200 text-gray-600 hover:border-indigo-400 hover:text-indigo-600">
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-toggle-on text-indigo-500 mr-1"></i> Status Anggota
                    </label>
                    <div class="grid grid-cols-4 gap-2">
                        @foreach(['' => 'Semua', 'aktif' => 'Aktif', 'nonaktif' => 'Nonaktif', 'ditangguhkan' => 'Tangguh'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="status" value="{{ $val }}"
                                   {{ request('status', '') === $val ? 'checked' : '' }}
                                   class="sr-only peer">
                            <div class="text-center px-2 py-2 rounded-xl border text-xs font-medium transition-all
                                        peer-checked:bg-indigo-600 peer-checked:text-white peer-checked:border-indigo-600
                                        border-gray-200 text-gray-600 hover:border-indigo-400 hover:text-indigo-600">
                                {{ $label }}
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                {{-- Jurusan --}}
                @if($jurusan->count())
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-graduation-cap text-indigo-500 mr-1"></i> Jurusan
                    </label>
                    <select name="jurusan_id"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition">
                        <option value="">Semua Jurusan</option>
                        @foreach($jurusan as $j)
                        <option value="{{ $j->id }}" {{ request('jurusan_id') == $j->id ? 'selected' : '' }}>
                            {{ $j->nama_jurusan }}
                        </option>
                        @endforeach
                    </select>
                </div>
                @endif

                {{-- Kelas --}}
                @if($kelas->count())
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-2">
                        <i class="fas fa-door-open text-indigo-500 mr-1"></i> Kelas
                    </label>
                    <select name="kelas_id"
                            class="w-full px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-400 outline-none transition">
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
                        class="inline-flex items-center gap-2 px-5 py-2 rounded-xl text-sm font-semibold text-white bg-gradient-to-r from-indigo-600 to-purple-600 shadow-md shadow-indigo-200 hover:shadow-lg hover:opacity-90 transition-all">
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
    $('#tabelAnggota').DataTable({
        responsive: true,
        language: {
            url: 'https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
        },
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        order: [],
        columnDefs: [
            { orderable: false, targets: [0] },
        ],
        dom: '<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4"lf>t<"flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mt-4"ip>',
    });
});

// ── Modal helpers ──
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

// Auto-open modal if filter was active but returned empty
@if(request()->hasAny(['tanggal_mulai','tanggal_akhir','jenis_anggota','status','jurusan_id','kelas_id']) && $anggota->isEmpty())
openFilterModal();
@endif
</script>
@endpush
