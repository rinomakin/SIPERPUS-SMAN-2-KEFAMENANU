@extends('layouts.admin')

@section('title', 'Manajemen Denda')

@section('content')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">

<style>
/* ===== Stat Cards ===== */
.stat-card {
    background: white; border-radius: 16px; padding: 20px; position: relative; overflow: hidden;
    border: 1px solid rgba(0,0,0,0.04);
    box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 4px 12px rgba(0,0,0,0.03);
    transition: all 0.3s ease;
}
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(0,0,0,0.08); }
.stat-card .stat-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center; font-size: 18px;
}
.stat-card .stat-bg {
    position: absolute; top: -20px; right: -20px; width: 90px; height: 90px;
    border-radius: 50%; opacity: 0.06;
}
.stat-card .stat-value { font-size: 22px; font-weight: 800; line-height: 1.2; }
.stat-card .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; color: #9ca3af; }

/* ===== Glass Card ===== */
.glass-card {
    background: rgba(255,255,255,0.97); backdrop-filter: blur(20px);
    border-radius: 16px; border: 1px solid rgba(255,255,255,0.8);
    box-shadow: 0 4px 24px rgba(0,0,0,0.06); overflow: hidden;
}

/* ===== Table ===== */
.denda-table { border-collapse: separate; border-spacing: 0; width: 100%; }
.denda-table thead th {
    background: #f8fafc; font-size: 10px; font-weight: 700; text-transform: uppercase;
    letter-spacing: 0.8px; color: #94a3b8; padding: 12px 16px; border-bottom: 2px solid #f1f5f9;
    white-space: nowrap;
}
.denda-table thead th.sorting:after,
.denda-table thead th.sorting_asc:after,
.denda-table thead th.sorting_desc:after { color: #94a3b8 !important; }
.denda-table tbody tr { transition: all 0.15s ease; }
.denda-table tbody tr:hover { background: #fefce8; }
.denda-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; font-size: 13px; }

/* ===== Avatar ===== */
.avatar-circle {
    width: 40px; height: 40px; border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 14px; font-weight: 700; color: white; flex-shrink: 0;
    background: linear-gradient(135deg, #f97316, #ef4444);
}
.avatar-img {
    width: 40px; height: 40px; border-radius: 12px; object-fit: cover;
    flex-shrink: 0; border: 2px solid #f1f5f9;
}

/* ===== Badges ===== */
.badge-late {
    display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 700;
    background: linear-gradient(135deg, #fef2f2, #fee2e2); color: #dc2626;
    border: 1px solid #fecaca;
}
.badge-amount { font-size: 13px; font-weight: 700; color: #dc2626; }

/* ===== Action Buttons ===== */
.action-btn {
    width: 32px; height: 32px; border-radius: 8px;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 12px; transition: all 0.2s; border: 1px solid transparent;
}
.action-btn.view { color: #3b82f6; background: #eff6ff; border-color: #dbeafe; }
.action-btn.view:hover { background: #3b82f6; color: white; }
.pay-btn {
    display: inline-flex; align-items: center; gap: 4px; padding: 5px 14px;
    border-radius: 8px; font-size: 11px; font-weight: 700;
    background: linear-gradient(135deg, #10b981, #059669); color: white;
    border: none; cursor: pointer; transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(16,185,129,0.3);
}
.pay-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 8px rgba(16,185,129,0.4); }

/* ===== Filter Chip ===== */
.filter-chip {
    display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px;
    border-radius: 20px; font-size: 11px; font-weight: 600; transition: all 0.2s;
}

/* ===== Empty State ===== */
.empty-state { text-align: center; padding: 48px 24px; }
.empty-state .empty-icon {
    width: 72px; height: 72px; border-radius: 50%; margin: 0 auto 16px;
    display: flex; align-items: center; justify-content: center;
    background: linear-gradient(135deg, #ecfdf5, #d1fae5);
}

/* ===== Animations ===== */
@keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
.anim-up { animation: fadeInUp 0.4s ease forwards; }
.anim-d1 { animation-delay: 0.05s; opacity: 0; }
.anim-d2 { animation-delay: 0.10s; opacity: 0; }
.anim-d3 { animation-delay: 0.15s; opacity: 0; }
.anim-d4 { animation-delay: 0.20s; opacity: 0; }

/* ===== DataTables Override ===== */
#denda-table_wrapper .dataTables_filter { display: none !important; }
#denda-table_wrapper { overflow: visible !important; }
#denda-table_wrapper .dataTables_scrollBody { overflow-x: auto; }

#denda-table_wrapper .dataTables_length {
    padding: 14px 20px;
    border-bottom: 1px solid #f1f5f9;
    font-size: 12px;
    color: #6b7280;
    display: flex;
    align-items: center;
    gap: 8px;
}
#denda-table_wrapper .dataTables_length label { display: flex; align-items: center; gap: 8px; font-size: 12px; color: #6b7280; }
#denda-table_wrapper .dataTables_length select {
    padding: 5px 28px 5px 10px; border-radius: 8px; border: 1px solid #e5e7eb;
    background: #f9fafb; font-size: 12px; cursor: pointer; outline: none;
    -webkit-appearance: none; -moz-appearance: none; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 8px center;
    transition: all 0.2s;
}
#denda-table_wrapper .dataTables_length select:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); }

#denda-table_wrapper .dataTables_info {
    padding: 14px 20px; font-size: 12px; color: #9ca3af;
}

#denda-table_wrapper .dataTables_paginate {
    padding: 10px 20px;
}
#denda-table_wrapper .dataTables_paginate .paginate_button {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 32px; height: 32px; padding: 0 8px;
    border-radius: 8px; font-size: 12px; font-weight: 600;
    color: #6b7280 !important; border: 1px solid transparent !important;
    background: transparent !important; cursor: pointer; transition: all 0.2s; margin: 0 2px;
}
#denda-table_wrapper .dataTables_paginate .paginate_button:hover {
    background: #fef2f2 !important; color: #ef4444 !important; border-color: #fecaca !important;
}
#denda-table_wrapper .dataTables_paginate .paginate_button.current {
    background: linear-gradient(135deg, #ef4444, #dc2626) !important;
    color: white !important; border-color: transparent !important;
    box-shadow: 0 2px 8px rgba(239,68,68,0.35) !important;
}
#denda-table_wrapper .dataTables_paginate .paginate_button.disabled,
#denda-table_wrapper .dataTables_paginate .paginate_button.disabled:hover {
    color: #d1d5db !important; background: transparent !important; cursor: default;
}
.dt-bottom-bar {
    display: flex; align-items: center; justify-content: space-between;
    border-top: 1px solid #f1f5f9; flex-wrap: wrap; gap: 8px;
}

/* ===== Modal ===== */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(0,0,0,0.5);
    backdrop-filter: blur(4px); z-index: 100; display: none;
    align-items: center; justify-content: center; padding: 16px;
}
.modal-overlay.active { display: flex; }
.modal-box {
    background: white; border-radius: 20px; width: 100%; max-width: 440px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2); overflow: hidden;
    animation: fadeInUp 0.25s ease;
}
.modal-header {
    padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;
}
.modal-close {
    width: 30px; height: 30px; border-radius: 50%; background: rgba(255,255,255,0.2);
    border: none; cursor: pointer; display: flex; align-items: center; justify-content: center;
    color: white; font-size: 14px; transition: background 0.2s;
}
.modal-close:hover { background: rgba(255,255,255,0.35); }

.filter-input {
    width: 100%; padding: 9px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; outline: none; transition: all 0.2s; background: #fafafa;
}
.filter-input:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); background: white; }
.filter-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; margin-bottom: 6px; display: block; }
.filter-select {
    width: 100%; padding: 9px 32px 9px 14px; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 13px; outline: none; transition: all 0.2s; background: #fafafa;
    -webkit-appearance: none; appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%236b7280' stroke-width='2'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat; background-position: right 12px center;
}
.filter-select:focus { border-color: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,0.1); background: white; }
</style>

<div class="max-w-7xl mx-auto">

    {{-- ===== Statistics Cards ===== --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="stat-card anim-up anim-d1">
            <div class="stat-bg" style="background:#ef4444;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#fef2f2,#fee2e2);color:#ef4444;">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Total Denda</p>
            <p class="stat-value text-gray-900">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d2">
            <div class="stat-bg" style="background:#f59e0b;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#fffbeb,#fef3c7);color:#f59e0b;">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Belum Dibayar</p>
            <p class="stat-value text-amber-600">Rp {{ number_format($dendaBelumDibayar, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d3">
            <div class="stat-bg" style="background:#10b981;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#ecfdf5,#d1fae5);color:#10b981;">
                    <i class="fas fa-check-double"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Sudah Dibayar</p>
            <p class="stat-value text-emerald-600">Rp {{ number_format($dendaSudahDibayar, 0, ',', '.') }}</p>
        </div>
        <div class="stat-card anim-up anim-d4">
            <div class="stat-bg" style="background:#6366f1;"></div>
            <div class="flex items-center gap-3 mb-3">
                <div class="stat-icon" style="background:linear-gradient(135deg,#eef2ff,#e0e7ff);color:#6366f1;">
                    <i class="fas fa-calendar-day"></i>
                </div>
            </div>
            <p class="stat-label mb-1">Denda Hari Ini</p>
            <p class="stat-value text-indigo-600">Rp {{ number_format($totalDendaHariIni, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- ===== Main Card ===== --}}
    <div class="glass-card anim-up" style="animation-delay:0.25s;opacity:0;">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between px-5 py-4 border-b border-gray-100">
            <div class="flex items-center gap-3 mb-3 sm:mb-0">
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white">
                    <i class="fas fa-list text-xs"></i>
                </div>
                <div>
                    <h2 class="text-sm font-bold text-gray-900">Daftar Denda Belum Dibayar</h2>
                    <p class="text-[11px] text-gray-400">{{ $jumlahBelumBayar }} anggota memiliki denda aktif</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                {{-- Filter Button --}}
                @php
                    $activeFilterCount = collect([request('search'), request('kelas_id'), request('jurusan_id'), request('jenis_anggota')])->filter()->count();
                @endphp
                <button type="button" id="openFilterModal"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg text-xs font-semibold transition-all border
                               {{ $activeFilterCount > 0 ? 'bg-red-500 text-white border-red-500 shadow-sm' : 'bg-gray-50 hover:bg-red-50 text-gray-600 hover:text-red-600 border-gray-200 hover:border-red-200' }}">
                    <i class="fas fa-sliders-h"></i>
                    <span>Filter</span>
                    @if($activeFilterCount > 0)
                        <span class="inline-flex items-center justify-center w-4 h-4 rounded-full bg-white text-red-600 text-[10px] font-extrabold">{{ $activeFilterCount }}</span>
                    @endif
                </button>
                {{-- Scan --}}
                <button type="button" id="scanBarcodeBtn"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 rounded-lg text-xs font-semibold transition-all border border-indigo-100">
                    <i class="fas fa-barcode"></i>
                    <span class="hidden sm:inline">Scan</span>
                </button>
                {{-- Riwayat --}}
                <a href="{{ route('admin.denda.riwayat') }}"
                   class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-lg text-xs font-semibold transition-all shadow-sm">
                    <i class="fas fa-history"></i>
                    <span class="hidden sm:inline">Riwayat</span>
                </a>
            </div>
        </div>

        {{-- Active Filter Chips --}}
        @if($activeFilterCount > 0)
        <div class="px-5 py-2.5 bg-amber-50/60 border-b border-amber-100 flex items-center gap-2 flex-wrap">
            <span class="text-[10px] text-amber-600 font-bold uppercase tracking-wider flex items-center gap-1">
                <i class="fas fa-filter"></i> Filter Aktif:
            </span>
            @if(request('search'))
                <span class="filter-chip bg-red-50 text-red-700 border border-red-100">
                    <i class="fas fa-search text-[9px]"></i> "{{ request('search') }}"
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('search'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('kelas_id'))
                @php $sk = $kelasList->firstWhere('id', request('kelas_id')); @endphp
                <span class="filter-chip bg-blue-50 text-blue-700 border border-blue-100">
                    <i class="fas fa-layer-group text-[9px]"></i> {{ $sk ? $sk->nama_kelas : '-' }}
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('kelas_id'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('jurusan_id'))
                @php $sj = $jurusanList->firstWhere('id', request('jurusan_id')); @endphp
                <span class="filter-chip bg-purple-50 text-purple-700 border border-purple-100">
                    <i class="fas fa-graduation-cap text-[9px]"></i> {{ $sj ? $sj->nama_jurusan : '-' }}
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('jurusan_id'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            @if(request('jenis_anggota'))
                <span class="filter-chip bg-green-50 text-green-700 border border-green-100">
                    <i class="fas fa-user-tag text-[9px]"></i> {{ ucfirst(request('jenis_anggota')) }}
                    <a href="{{ route('admin.denda.index', array_merge(request()->except('jenis_anggota'), ['page' => null])) }}"
                       class="ml-1 opacity-60 hover:opacity-100">&times;</a>
                </span>
            @endif
            <a href="{{ route('admin.denda.index') }}"
               class="ml-auto text-[11px] text-gray-400 hover:text-red-500 font-semibold transition-colors">
                <i class="fas fa-times mr-0.5"></i>Hapus semua
            </a>
        </div>
        @endif

        {{-- DataTables Table --}}
        <div>
            <table id="denda-table" class="denda-table">
                <thead>
                    <tr>
                        <th class="text-left">Anggota</th>
                        <th class="text-left">Peminjaman</th>
                        <th class="text-center">Terlambat</th>
                        <th class="text-right">Jumlah Denda</th>
                        <th class="text-center">Tanggal</th>
                        <th class="text-center" style="width:110px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($denda as $item)
                    <tr>
                        {{-- Anggota --}}
                        <td data-order="{{ $item->anggota->nama_lengkap ?? '' }}">
                            <div class="flex items-center gap-3">
                                @if($item->anggota && $item->anggota->foto)
                                    <img class="avatar-img"
                                         src="{{ asset('storage/' . $item->anggota->foto) }}"
                                         alt="{{ $item->anggota->nama_lengkap }}"
                                         onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                                    <div class="avatar-circle" style="display:none;background:linear-gradient(135deg,{{ ['#f97316,#ef4444','#8b5cf6,#6366f1','#10b981,#059669','#3b82f6,#2563eb','#ec4899,#db2777'][($item->anggota->id ?? 0) % 5] }});">
                                        {{ strtoupper(substr($item->anggota->nama_lengkap ?? 'N', 0, 1)) }}
                                    </div>
                                @elseif($item->anggota)
                                    <div class="avatar-circle" style="background:linear-gradient(135deg,{{ ['#f97316,#ef4444','#8b5cf6,#6366f1','#10b981,#059669','#3b82f6,#2563eb','#ec4899,#db2777'][($item->anggota->id ?? 0) % 5] }});">
                                        {{ strtoupper(substr($item->anggota->nama_lengkap ?? 'N', 0, 1)) }}
                                    </div>
                                @else
                                    <div class="avatar-circle" style="background:linear-gradient(135deg,#94a3b8,#64748b);">
                                        <i class="fas fa-user text-xs"></i>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-xs font-bold text-gray-900 truncate">{{ $item->anggota->nama_lengkap ?? 'N/A' }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $item->anggota->nomor_anggota ?? 'N/A' }}</p>
                                    @if($item->anggota && $item->anggota->kelas)
                                        <p class="text-[10px] text-gray-400">{{ $item->anggota->kelas->nama_kelas }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Peminjaman --}}
                        <td data-order="{{ $item->peminjaman ? ($item->peminjaman->tanggal_peminjaman ?? '') : '' }}">
                            @if($item->peminjaman)
                                <p class="text-xs font-semibold text-gray-800">{{ $item->peminjaman->nomor_peminjaman ?? ('ID: ' . $item->peminjaman_id) }}</p>
                                <p class="text-[10px] text-gray-400">
                                    <i class="far fa-calendar-alt mr-0.5"></i>{{ $item->peminjaman->tanggal_peminjaman ? $item->peminjaman->tanggal_peminjaman->format('d M Y') : '-' }}
                                </p>
                            @else
                                <span class="text-xs text-gray-300">N/A</span>
                            @endif
                        </td>

                        {{-- Hari Terlambat --}}
                        <td class="text-center" data-order="{{ $item->jumlah_hari_terlambat }}">
                            <span class="badge-late">
                                <i class="fas fa-clock text-[9px]"></i>
                                {{ $item->jumlah_hari_terlambat }} hari
                            </span>
                        </td>

                        {{-- Jumlah Denda --}}
                        <td class="text-right" data-order="{{ $item->jumlah_denda }}">
                            <span class="badge-amount">Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}</span>
                        </td>

                        {{-- Tanggal --}}
                        <td class="text-center" data-order="{{ $item->created_at->format('Y-m-d H:i:s') }}">
                            <p class="text-xs text-gray-600">{{ $item->created_at->format('d M Y') }}</p>
                            <p class="text-[10px] text-gray-400">{{ $item->created_at->format('H:i') }}</p>
                        </td>

                        {{-- Aksi --}}
                        <td class="text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('admin.denda.show', $item->id) }}"
                                   class="action-btn view" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <form action="{{ route('admin.denda.bayar-lunas', $item->id) }}"
                                      method="POST" class="inline bayar-form"
                                      data-nama="{{ $item->anggota ? $item->anggota->nama_lengkap : 'N/A' }}"
                                      data-denda="Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}">
                                    @csrf
                                    <button type="submit" class="pay-btn" title="Bayar Lunas">
                                        <i class="fas fa-check"></i>Bayar
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

    </div>{{-- end glass-card --}}

</div>{{-- end max-w-7xl --}}


{{-- ===== Filter Modal ===== --}}
<div id="filterModal" class="modal-overlay">
    <div class="modal-box">
        {{-- Modal Header --}}
        <div class="modal-header bg-gradient-to-r from-red-500 to-rose-600">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                    <i class="fas fa-sliders-h text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-white">Filter & Pencarian</h3>
                    <p class="text-[10px] text-white/70">Saring data denda sesuai kebutuhan</p>
                </div>
            </div>
            <button type="button" class="modal-close" id="closeFilterModal">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Modal Body --}}
        <form action="{{ route('admin.denda.index') }}" method="GET" id="filterForm">
            <div class="p-5 space-y-4">

                {{-- Search --}}
                <div>
                    <label class="filter-label">Cari Anggota</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400 pointer-events-none">
                            <i class="fas fa-search text-xs"></i>
                        </span>
                        <input type="text" name="search" id="filterSearch"
                               value="{{ request('search') }}"
                               placeholder="Nama, nomor anggota, atau barcode..."
                               class="filter-input pl-9">
                    </div>
                </div>

                {{-- Kelas --}}
                <div>
                    <label class="filter-label">Kelas</label>
                    <select name="kelas_id" class="filter-select">
                        <option value="">— Semua Kelas —</option>
                        @foreach($kelasList as $kelas)
                            <option value="{{ $kelas->id }}" {{ request('kelas_id') == $kelas->id ? 'selected' : '' }}>
                                {{ $kelas->nama_kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jurusan --}}
                <div>
                    <label class="filter-label">Jurusan</label>
                    <select name="jurusan_id" class="filter-select">
                        <option value="">— Semua Jurusan —</option>
                        @foreach($jurusanList as $jurusan)
                            <option value="{{ $jurusan->id }}" {{ request('jurusan_id') == $jurusan->id ? 'selected' : '' }}>
                                {{ $jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Jenis Anggota --}}
                <div>
                    <label class="filter-label">Jenis Anggota</label>
                    <select name="jenis_anggota" class="filter-select">
                        <option value="">— Semua Jenis —</option>
                        <option value="siswa" {{ request('jenis_anggota') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="guru"  {{ request('jenis_anggota') == 'guru'  ? 'selected' : '' }}>Guru</option>
                        <option value="staff" {{ request('jenis_anggota') == 'staff' ? 'selected' : '' }}>Staff</option>
                    </select>
                </div>

            </div>

            {{-- Modal Footer --}}
            <div class="px-5 py-4 bg-gray-50 border-t border-gray-100 flex gap-2.5">
                <a href="{{ route('admin.denda.index') }}"
                   class="flex-1 py-2.5 bg-white hover:bg-gray-100 text-gray-600 border border-gray-200 rounded-xl text-xs font-semibold text-center transition-all">
                    <i class="fas fa-redo mr-1"></i>Reset Filter
                </a>
                <button type="submit"
                        class="flex-1 py-2.5 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white rounded-xl text-xs font-bold transition-all shadow-sm">
                    <i class="fas fa-search mr-1"></i>Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ===== Barcode Scanner Modal ===== --}}
<div id="barcodeModal" class="modal-overlay">
    <div class="modal-box" style="max-width:480px;">
        <div class="modal-header bg-gradient-to-r from-indigo-600 to-purple-600">
            <div class="flex items-center gap-2">
                <i class="fas fa-barcode text-white"></i>
                <h3 class="text-sm font-bold text-white">Scan Barcode Anggota</h3>
            </div>
            <button type="button" class="modal-close" id="closeBarcodeModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="p-5">
            <div id="scannerContainer" class="w-full h-52 bg-gray-100 rounded-xl flex items-center justify-center relative overflow-hidden mb-4">
                <div id="scannerPlaceholder" class="text-center">
                    <div class="w-14 h-14 rounded-full bg-gray-200 flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-camera text-xl text-gray-400"></i>
                    </div>
                    <p class="text-gray-400 text-xs">Klik "Mulai Scan" untuk mengaktifkan kamera</p>
                </div>
                <div id="scannerVideo" class="w-full h-full hidden">
                    <video id="scannerVideoElement" class="w-full h-full object-cover"></video>
                    <div id="scannerOverlay" class="absolute inset-0 flex items-center justify-center">
                        <div class="border-2 border-white/60 border-dashed w-56 h-20 rounded-lg flex items-center justify-center">
                            <p class="text-white text-[10px] font-semibold">Arahkan barcode ke sini</p>
                        </div>
                    </div>
                </div>
                <div id="scannerLoading" class="absolute inset-0 bg-gray-900/75 hidden flex items-center justify-center">
                    <div class="text-center text-white">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p class="text-xs">Memulai kamera...</p>
                    </div>
                </div>
            </div>
            <div class="flex justify-between items-center mb-4">
                <p class="text-[11px] text-gray-400" id="scannerStatus">Siap untuk scan</p>
                <div class="flex gap-2">
                    <button type="button" id="startScanBtn"
                            class="px-3 py-1.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-lg text-xs font-semibold transition-all">
                        <i class="fas fa-play mr-1"></i>Mulai Scan
                    </button>
                    <button type="button" id="stopScanBtn"
                            class="px-3 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-xs font-semibold hidden transition-all">
                        <i class="fas fa-stop mr-1"></i>Stop
                    </button>
                </div>
            </div>
            <div class="relative mb-4">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-100"></div></div>
                <div class="relative flex justify-center"><span class="px-3 bg-white text-[10px] text-gray-400 font-semibold uppercase tracking-wider">atau ketik manual</span></div>
            </div>
            <div class="flex gap-2 mb-3">
                <div class="relative flex-1">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-indigo-400 pointer-events-none">
                        <i class="fas fa-keyboard text-xs"></i>
                    </span>
                    <input type="text" id="barcodeInput" placeholder="Ketik barcode / nomor anggota..."
                           class="filter-input pl-9 font-mono">
                </div>
                <button type="button" id="manualSearchBtn"
                        class="px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-semibold transition-all">
                    <i class="fas fa-search"></i>
                </button>
            </div>
            <div id="barcodeLoading" class="hidden text-center py-3">
                <div class="inline-flex items-center gap-2 text-indigo-600 text-xs">
                    <i class="fas fa-spinner fa-spin"></i><span>Mencari data anggota...</span>
                </div>
            </div>
            <div id="barcodeError" class="hidden bg-red-50 border border-red-100 text-red-700 px-4 py-3 rounded-xl text-xs mb-3">
                <i class="fas fa-exclamation-circle mr-1"></i><span id="barcodeErrorText"></span>
            </div>
            <div id="barcodeResult" class="hidden">
                <div class="border border-indigo-100 rounded-xl overflow-hidden mb-3">
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-4 py-3 flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center overflow-hidden flex-shrink-0" id="resultAvatarContainer">
                            <img id="resultFoto" src="" class="w-11 h-11 rounded-xl object-cover hidden" alt=""
                                 onerror="this.classList.add('hidden');document.getElementById('resultFotoInitial').classList.remove('hidden');">
                            <span id="resultFotoInitial" class="text-white font-bold text-sm"></span>
                        </div>
                        <div>
                            <p class="text-xs font-bold text-gray-900" id="resultNama"></p>
                            <p class="text-[10px] text-gray-500" id="resultNomor"></p>
                            <p class="text-[10px] text-gray-400" id="resultKelas"></p>
                        </div>
                    </div>
                    <div class="px-4 py-3">
                        <div class="flex items-center justify-between mb-1.5">
                            <span class="text-[11px] text-gray-500">Jumlah Denda Belum Bayar:</span>
                            <span class="text-xs font-bold text-red-600" id="resultJumlahDenda"></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-[11px] text-gray-500">Total Nominal:</span>
                            <span class="text-base font-extrabold text-red-600" id="resultTotalDenda"></span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="button" id="searchByMemberBtn"
                            class="flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all">
                        <i class="fas fa-search mr-1"></i>Lihat Daftar Denda
                    </button>
                    <button type="button" id="scanAgainBtn"
                            class="flex-1 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-all">
                        <i class="fas fa-redo mr-1"></i>Scan Lagi
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- jQuery + DataTables --}}
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {

    // ========================
    // DataTables Init
    // ========================
    var dendaTable = $('#denda-table').DataTable({
        dom:
            '<"flex items-center justify-between px-5 py-3 border-b border-gray-50 bg-gray-50/30"<"text-xs text-gray-500"l>>' +
            'rt' +
            '<"dt-bottom-bar px-5 py-3"<"text-xs text-gray-400"i><"dt-pager"p>>',
        language: {
            lengthMenu: 'Tampilkan _MENU_ entri',
            info: 'Menampilkan _START_–_END_ dari <b>_TOTAL_</b> data',
            infoEmpty: 'Tidak ada data',
            infoFiltered: '(difilter dari _MAX_ total data)',
            emptyTable: `<div style="padding:40px 24px;text-align:center;">
                <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#ecfdf5,#d1fae5);display:flex;align-items:center;justify-content:center;margin:0 auto 12px;">
                    <i class="fas fa-check-circle" style="font-size:28px;color:#10b981;"></i>
                </div>
                <p style="font-size:13px;font-weight:700;color:#374151;margin-bottom:4px;">Semua denda sudah dibayar!</p>
                <p style="font-size:11px;color:#9ca3af;">Tidak ada anggota dengan denda yang belum dibayar</p>
            </div>`,
            paginate: {
                first:    '<i class="fas fa-angle-double-left text-xs"></i>',
                last:     '<i class="fas fa-angle-double-right text-xs"></i>',
                next:     '<i class="fas fa-angle-right text-xs"></i>',
                previous: '<i class="fas fa-angle-left text-xs"></i>',
            },
            zeroRecords: `<div style="padding:40px 24px;text-align:center;">
                <div style="font-size:32px;color:#d1d5db;margin-bottom:12px;"><i class="fas fa-search"></i></div>
                <p style="font-size:13px;font-weight:700;color:#374151;">Tidak ada hasil ditemukan</p>
                <p style="font-size:11px;color:#9ca3af;">Coba ubah kata kunci atau filter pencarian</p>
            </div>`,
        },
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']],
        order: [[4, 'desc']],
        columns: [
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: true },
            { orderable: false, searchable: false },
        ],
        initComplete: function () {
            // Style the paginate wrapper
            $('.dt-pager').css({ display: 'flex', alignItems: 'center', gap: '2px' });
        }
    });

    // ========================
    // Filter Modal
    // ========================
    const filterModal  = document.getElementById('filterModal');
    const openFilter   = document.getElementById('openFilterModal');
    const closeFilter  = document.getElementById('closeFilterModal');

    openFilter.addEventListener('click', () => filterModal.classList.add('active'));
    closeFilter.addEventListener('click', () => filterModal.classList.remove('active'));
    filterModal.addEventListener('click', (e) => { if (e.target === filterModal) filterModal.classList.remove('active'); });

    // Auto-open if active filters (show user current state)
    @if($activeFilterCount > 0)
    // filters are active, don't auto-open — just show chips
    @endif

    // ========================
    // Bayar Lunas with SweetAlert
    // ========================
    document.querySelectorAll('.bayar-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            const nama  = this.dataset.nama;
            const denda = this.dataset.denda;
            const formEl = this;
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Konfirmasi Pembayaran',
                    html: `Tandai denda <b>${denda}</b> atas nama <b>${nama}</b> sebagai <span style="color:#10b981;font-weight:700;">LUNAS</span>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#6b7280',
                    confirmButtonText: '<i class="fas fa-check mr-1"></i>Ya, Bayar Lunas',
                    cancelButtonText: 'Batal',
                }).then(result => { if (result.isConfirmed) formEl.submit(); });
            } else {
                if (confirm(`Tandai denda ${denda} atas nama ${nama} sebagai LUNAS?`)) formEl.submit();
            }
        });
    });

    // ========================
    // Barcode Scanner
    // ========================
    const barcodeModal     = document.getElementById('barcodeModal');
    const closeBarcodeBtn  = document.getElementById('closeBarcodeModal');
    const scanBarcodeBtn   = document.getElementById('scanBarcodeBtn');
    const barcodeInput     = document.getElementById('barcodeInput');
    const manualSearchBtn  = document.getElementById('manualSearchBtn');
    const barcodeLoading   = document.getElementById('barcodeLoading');
    const barcodeError     = document.getElementById('barcodeError');
    const barcodeErrorText = document.getElementById('barcodeErrorText');
    const barcodeResult    = document.getElementById('barcodeResult');
    const scanAgainBtn     = document.getElementById('scanAgainBtn');
    const searchByMemberBtn= document.getElementById('searchByMemberBtn');
    const startScanBtn     = document.getElementById('startScanBtn');
    const stopScanBtn      = document.getElementById('stopScanBtn');
    let foundMemberNomor   = '';

    function loadZXingLibrary() {
        return new Promise((resolve, reject) => {
            if (window.ZXing) { resolve(window.ZXing); return; }
            const s = document.createElement('script');
            s.src = 'https://unpkg.com/@zxing/library@latest/umd/index.min.js';
            s.onload = () => resolve(window.ZXing);
            s.onerror = () => reject(new Error('Failed to load ZXing'));
            document.head.appendChild(s);
        });
    }

    async function setupCameraScanner() {
        const videoEl      = document.getElementById('scannerVideoElement');
        const scanLoading  = document.getElementById('scannerLoading');
        const scanVideo    = document.getElementById('scannerVideo');
        const scanPlaceholder = document.getElementById('scannerPlaceholder');
        const scanStatus   = document.getElementById('scannerStatus');
        try {
            const ZXing = await loadZXingLibrary();
            scanLoading.classList.remove('hidden');
            scanPlaceholder.classList.add('hidden');
            scanVideo.classList.remove('hidden');
            scanStatus.textContent = 'Memulai kamera...';
            const stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
            videoEl.srcObject = stream;
            await videoEl.play();
            scanLoading.classList.add('hidden');
            startScanBtn.classList.add('hidden');
            stopScanBtn.classList.remove('hidden');
            scanStatus.textContent = 'Scanner aktif — arahkan barcode ke kamera';
            const reader = new ZXing.BrowserMultiFormatReader();
            await reader.decodeFromVideoDevice(null, videoEl, (result) => {
                if (result) { stopCameraScanner(); processScannedBarcode(result.text.trim()); }
            });
            window.currentCodeReader = reader;
        } catch (err) {
            scanLoading.classList.add('hidden');
            scanPlaceholder.classList.remove('hidden');
            scanVideo.classList.add('hidden');
            startScanBtn.classList.remove('hidden');
            stopScanBtn.classList.add('hidden');
            document.getElementById('scannerStatus').textContent =
                err.name === 'NotAllowedError' ? 'Akses kamera ditolak' : 'Gagal memulai kamera';
        }
    }

    function stopCameraScanner() {
        if (window.currentCodeReader) { try { window.currentCodeReader.reset(); } catch(e){} }
        const v = document.getElementById('scannerVideoElement');
        if (v && v.srcObject) { v.srcObject.getTracks().forEach(t => t.stop()); v.srcObject = null; }
        startScanBtn.classList.remove('hidden');
        stopScanBtn.classList.add('hidden');
        document.getElementById('scannerStatus').textContent = 'Scanner dihentikan';
    }

    function resetBarcodeModal() {
        barcodeInput.value = '';
        barcodeLoading.classList.add('hidden');
        barcodeError.classList.add('hidden');
        barcodeResult.classList.add('hidden');
        foundMemberNomor = '';
    }

    function closeBarcodeModal() {
        stopCameraScanner();
        barcodeModal.classList.remove('active');
        resetBarcodeModal();
        document.getElementById('scannerPlaceholder').classList.remove('hidden');
        document.getElementById('scannerVideo').classList.add('hidden');
        document.getElementById('scannerStatus').textContent = 'Siap untuk scan';
        startScanBtn.classList.remove('hidden');
        stopScanBtn.classList.add('hidden');
    }

    function processScannedBarcode(barcode) {
        document.getElementById('scannerStatus').textContent = 'Barcode: ' + barcode;
        barcodeInput.value = barcode;
        searchByBarcode(barcode);
    }

    function searchByBarcode(barcode) {
        barcodeLoading.classList.remove('hidden');
        barcodeError.classList.add('hidden');
        barcodeResult.classList.add('hidden');
        fetch('{{ route("admin.denda.scan-barcode") }}?barcode=' + encodeURIComponent(barcode), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(data => {
            barcodeLoading.classList.add('hidden');
            if (!data.success) {
                barcodeError.classList.remove('hidden');
                barcodeErrorText.textContent = data.message;
                return;
            }
            barcodeResult.classList.remove('hidden');
            foundMemberNomor = data.anggota.nomor_anggota;
            document.getElementById('resultNama').textContent = data.anggota.nama_lengkap;
            document.getElementById('resultNomor').textContent = data.anggota.nomor_anggota + ' | ' + data.anggota.barcode_anggota;
            document.getElementById('resultKelas').textContent = data.anggota.kelas;
            const fotoEl = document.getElementById('resultFoto');
            const initEl = document.getElementById('resultFotoInitial');
            if (data.anggota.foto) {
                fotoEl.src = data.anggota.foto; fotoEl.classList.remove('hidden'); initEl.classList.add('hidden');
            } else {
                fotoEl.classList.add('hidden'); initEl.classList.remove('hidden');
                initEl.textContent = (data.anggota.nama_lengkap || 'N').charAt(0).toUpperCase();
            }
            document.getElementById('resultJumlahDenda').textContent = data.jumlah_denda + ' denda';
            document.getElementById('resultTotalDenda').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total_denda);
            if (data.jumlah_denda === 0) {
                searchByMemberBtn.innerHTML = '<i class="fas fa-check-circle mr-1"></i>Tidak Ada Denda';
                searchByMemberBtn.disabled = true;
                searchByMemberBtn.className = 'flex-1 py-2.5 bg-gray-300 text-gray-500 cursor-not-allowed rounded-xl text-xs font-bold';
            } else {
                searchByMemberBtn.innerHTML = '<i class="fas fa-search mr-1"></i>Lihat Daftar Denda';
                searchByMemberBtn.disabled = false;
                searchByMemberBtn.className = 'flex-1 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold transition-all';
            }
        })
        .catch(() => {
            barcodeLoading.classList.add('hidden');
            barcodeError.classList.remove('hidden');
            barcodeErrorText.textContent = 'Terjadi kesalahan saat mencari data.';
        });
    }

    scanBarcodeBtn.addEventListener('click', () => { barcodeModal.classList.add('active'); resetBarcodeModal(); });
    closeBarcodeBtn.addEventListener('click', closeBarcodeModal);
    barcodeModal.addEventListener('click', (e) => { if (e.target === barcodeModal) closeBarcodeModal(); });
    startScanBtn.addEventListener('click', setupCameraScanner);
    stopScanBtn.addEventListener('click', stopCameraScanner);
    scanAgainBtn.addEventListener('click', () => {
        resetBarcodeModal();
        document.getElementById('scannerPlaceholder').classList.remove('hidden');
        document.getElementById('scannerVideo').classList.add('hidden');
        document.getElementById('scannerStatus').textContent = 'Siap untuk scan';
        startScanBtn.classList.remove('hidden');
        stopScanBtn.classList.add('hidden');
    });
    searchByMemberBtn.addEventListener('click', () => {
        if (foundMemberNomor) window.location.href = '{{ route("admin.denda.index") }}?search=' + encodeURIComponent(foundMemberNomor);
    });
    barcodeInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); const b = barcodeInput.value.trim(); if (b) searchByBarcode(b); }
    });
    manualSearchBtn.addEventListener('click', () => { const b = barcodeInput.value.trim(); if (b) searchByBarcode(b); });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            filterModal.classList.remove('active');
            if (!barcodeModal.classList.contains('active')) return;
            closeBarcodeModal();
        }
    });

});
</script>
@endsection
