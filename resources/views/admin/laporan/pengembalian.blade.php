@extends('layouts.admin')

@section('title', 'Laporan Pengembalian')
@section('page-title', 'Laporan Pengembalian')

@section('content')
<style>
    .glass-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.3);
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade { animation: fadeIn 0.4s ease forwards; }
</style>

<div class="space-y-5">
    {{-- Header & Filter --}}
    <div class="glass-card rounded-2xl shadow-lg p-5">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('laporan.index') }}" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Laporan Pengembalian</h1>
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-emerald-600">{{ $pengembalian->count() }}</span> transaksi</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white/70">
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 bg-white/70">

                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>

                <a href="{{ route('admin.laporan.pengembalian', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.pengembalian', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $totalPengembalian = $pengembalian->count();
            $tepatWaktu = $pengembalian->where('jumlah_hari_terlambat', '<=', 0)->count();
            $terlambat = $pengembalian->where('jumlah_hari_terlambat', '>', 0)->count();
            $totalDenda = $pengembalian->sum('total_denda');
        @endphp
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-undo text-emerald-600"></i></div>
                <div><p class="text-xs text-gray-500">Total</p><p class="text-lg font-bold text-gray-900">{{ $totalPengembalian }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-green-600"></i></div>
                <div><p class="text-xs text-gray-500">Tepat Waktu</p><p class="text-lg font-bold text-gray-900">{{ $tepatWaktu }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center"><i class="fas fa-exclamation-circle text-red-600"></i></div>
                <div><p class="text-xs text-gray-500">Terlambat</p><p class="text-lg font-bold text-gray-900">{{ $terlambat }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.2s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><i class="fas fa-money-bill-wave text-amber-600"></i></div>
                <div><p class="text-xs text-gray-500">Total Denda</p><p class="text-lg font-bold text-gray-900">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p></div>
            </div>
        </div>
    </div>

    {{-- Report Content --}}
    <div>
        <div class="glass-card rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gradient-to-r from-emerald-50 to-green-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">No. Pengembalian</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Anggota</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Tgl Kembali</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-emerald-700 uppercase">Jml Buku</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Status</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-700 uppercase">Denda</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pengembalian as $index => $item)
                        <tr class="hover:bg-emerald-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-mono font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    {{ $item->nomor_pengembalian }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->anggota->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ $item->anggota->nomor_anggota }}</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal_pengembalian ? $item->tanggal_pengembalian->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ $item->detailPengembalian->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($item->jumlah_hari_terlambat > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Terlambat {{ $item->jumlah_hari_terlambat }} hari
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tepat Waktu
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-semibold {{ $item->total_denda > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                Rp {{ number_format($item->total_denda, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-undo text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                                <p class="text-sm text-gray-500">Tidak ada pengembalian yang sesuai dengan filter</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($pengembalian->count() > 0)
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="6" class="px-4 py-3 text-sm text-gray-700 text-right">Total Denda:</td>
                            <td class="px-4 py-3 text-sm text-right text-red-600 font-bold">Rp {{ number_format($pengembalian->sum('total_denda'), 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
