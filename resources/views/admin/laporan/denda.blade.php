@extends('layouts.admin')

@section('title', 'Laporan Denda')
@section('page-title', 'Laporan Denda')

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
                    <h1 class="text-xl font-bold text-gray-900">Laporan Denda</h1>
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-amber-600">{{ $denda->count() }}</span> denda</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 bg-white/70">
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 bg-white/70">

                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white/70">
                    <option value="">Semua Status</option>
                    <option value="belum_dibayar" {{ request('status') == 'belum_dibayar' ? 'selected' : '' }}>Belum Bayar</option>
                    <option value="sudah_dibayar" {{ request('status') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Bayar</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>

                <a href="{{ route('admin.laporan.denda', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.denda', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @php
            $totalDenda = $denda->sum('jumlah_denda');
            $dendaSudahBayar = $denda->where('status_pembayaran', 'sudah_dibayar')->sum('jumlah_denda');
            $dendaBelumBayar = $denda->where('status_pembayaran', 'belum_dibayar')->sum('jumlah_denda');
        @endphp
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><i class="fas fa-money-bill-wave text-amber-600"></i></div>
                <div><p class="text-xs text-gray-500">Total Denda</p><p class="text-lg font-bold text-gray-900">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
                <div><p class="text-xs text-gray-500">Sudah Bayar</p><p class="text-lg font-bold text-emerald-600">Rp {{ number_format($dendaSudahBayar, 0, ',', '.') }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center"><i class="fas fa-exclamation-circle text-red-600"></i></div>
                <div><p class="text-xs text-gray-500">Belum Bayar</p><p class="text-lg font-bold text-red-600">Rp {{ number_format($dendaBelumBayar, 0, ',', '.') }}</p></div>
            </div>
        </div>
    </div>

    {{-- Report Content --}}
    <div>
        <div class="glass-card rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gradient-to-r from-amber-50 to-orange-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-amber-700 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-amber-700 uppercase">Anggota</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-amber-700 uppercase">No. Peminjaman</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-amber-700 uppercase">Hari Terlambat</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-amber-700 uppercase">Total Denda</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-amber-700 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-amber-700 uppercase">Tgl Bayar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($denda as $index => $item)
                        <tr class="hover:bg-amber-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->peminjaman->anggota->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ $item->peminjaman->anggota->nomor_anggota }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-mono font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                    {{ $item->peminjaman->nomor_peminjaman }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-medium text-gray-900">{{ $item->jumlah_hari_terlambat }} hari</td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-red-600">Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}</td>
                            <td class="px-4 py-3">
                                @if($item->status_pembayaran == 'sudah_dibayar')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sudah Bayar
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Belum Bayar
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal_pembayaran ? $item->tanggal_pembayaran->format('d/m/Y') : '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-money-bill-wave text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                                <p class="text-sm text-gray-500">Tidak ada denda yang sesuai dengan filter</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($denda->count() > 0)
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="4" class="px-4 py-3 text-sm text-gray-700 text-right">Total:</td>
                            <td class="px-4 py-3 text-sm text-right text-red-600 font-bold">Rp {{ number_format($denda->sum('jumlah_denda'), 0, ',', '.') }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
