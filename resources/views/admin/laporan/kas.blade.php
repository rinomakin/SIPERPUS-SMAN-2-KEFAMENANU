@extends('layouts.admin')

@section('title', 'Laporan Kas')
@section('page-title', 'Laporan Kas')

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
                    <h1 class="text-xl font-bold text-gray-900">Laporan Kas Perpustakaan</h1>
                    <p class="text-sm text-gray-500">Total Pemasukan: <span class="font-semibold text-emerald-600">Rp {{ number_format($kas->sum('jumlah_denda'), 0, ',', '.') }}</span></p>
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

                <a href="{{ route('admin.laporan.kas', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.kas', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </form>
        </div>
    </div>

    {{-- Summary Card --}}
    <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade" style="animation-delay:0.05s">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-emerald-500 to-green-600 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-200">
                    <i class="fas fa-wallet text-white text-2xl"></i>
                </div>
                <div>
                    <p class="text-sm text-gray-500">Total Pemasukan Kas</p>
                    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($kas->sum('jumlah_denda'), 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">Dari {{ $kas->count() }} transaksi pembayaran denda</p>
                </div>
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
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Anggota</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Sumber</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Keterangan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-emerald-700 uppercase">Jumlah</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-emerald-700 uppercase">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($kas as $index => $item)
                        <tr class="hover:bg-emerald-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal_pembayaran ? $item->tanggal_pembayaran->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->peminjaman->anggota->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ $item->peminjaman->anggota->nomor_anggota }}</div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                    Denda Keterlambatan
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm text-gray-600">Pembayaran denda {{ $item->jumlah_hari_terlambat }} hari terlambat</div>
                                <div class="text-xs text-gray-400 font-mono">{{ $item->peminjaman->nomor_peminjaman }}</div>
                            </td>
                            <td class="px-4 py-3 text-right text-sm font-bold text-emerald-600">Rp {{ number_format($item->jumlah_denda, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">-</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-wallet text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">Tidak ada pemasukan</h3>
                                <p class="text-sm text-gray-500">Tidak ada transaksi kas yang sesuai dengan filter</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                    @if($kas->count() > 0)
                    <tfoot>
                        <tr class="bg-gray-50 font-semibold">
                            <td colspan="5" class="px-4 py-3 text-sm text-gray-700 text-right">Total Pemasukan:</td>
                            <td class="px-4 py-3 text-sm text-right text-emerald-600 font-bold">Rp {{ number_format($kas->sum('jumlah_denda'), 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
