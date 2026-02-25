@extends('layouts.admin')

@section('title', 'Laporan Peminjaman')
@section('page-title', 'Laporan Peminjaman')

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
                    <h1 class="text-xl font-bold text-gray-900">Laporan Peminjaman</h1>
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-sky-600">{{ $peminjaman->count() }}</span> transaksi</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 bg-white/70">
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 bg-white/70">

                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white/70">
                    <option value="">Semua Status</option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Sedang Dipinjam</option>
                    <option value="dikembalikan" {{ request('status') == 'dikembalikan' ? 'selected' : '' }}>Sudah Dikembalikan</option>
                    <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-sky-500 to-blue-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>

                <a href="{{ route('admin.laporan.peminjaman', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.peminjaman', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $totalPeminjaman = $peminjaman->count();
            $sedangDipinjam = $peminjaman->where('status', 'dipinjam')->count();
            $sudahDikembalikan = $peminjaman->where('status', 'dikembalikan')->count();
            $terlambat = $peminjaman->where('status', 'terlambat')->count();
        @endphp
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-sky-100 rounded-xl flex items-center justify-center"><i class="fas fa-book-reader text-sky-600"></i></div>
                <div><p class="text-xs text-gray-500">Total</p><p class="text-lg font-bold text-gray-900">{{ $totalPeminjaman }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center"><i class="fas fa-clock text-amber-600"></i></div>
                <div><p class="text-xs text-gray-500">Dipinjam</p><p class="text-lg font-bold text-gray-900">{{ $sedangDipinjam }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
                <div><p class="text-xs text-gray-500">Dikembalikan</p><p class="text-lg font-bold text-gray-900">{{ $sudahDikembalikan }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.2s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center"><i class="fas fa-exclamation-triangle text-red-600"></i></div>
                <div><p class="text-xs text-gray-500">Terlambat</p><p class="text-lg font-bold text-gray-900">{{ $terlambat }}</p></div>
            </div>
        </div>
    </div>

    {{-- Report Content --}}
    <div>
        <div class="glass-card rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gradient-to-r from-sky-50 to-blue-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-sky-700 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-sky-700 uppercase">No. Peminjaman</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-sky-700 uppercase">Anggota</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-sky-700 uppercase">Tgl Pinjam</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-sky-700 uppercase">Tgl Kembali</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-sky-700 uppercase">Jml Buku</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-sky-700 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-sky-700 uppercase">Petugas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($peminjaman as $index => $item)
                        <tr class="hover:bg-sky-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-mono font-medium bg-sky-50 text-sky-700 border border-sky-200">
                                    {{ $item->nomor_peminjaman }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->anggota->nama_lengkap }}</div>
                                <div class="text-xs text-gray-500">{{ $item->anggota->nomor_anggota }}</div>
                                @if($item->anggota->kelas)
                                    <div class="text-xs text-gray-400">{{ $item->anggota->kelas->nama_kelas }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal_peminjaman ? $item->tanggal_peminjaman->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal_kembali ? $item->tanggal_kembali->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                    {{ $item->detailPeminjaman->count() }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if($item->status == 'dipinjam')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Dipinjam
                                    </span>
                                @elseif($item->status == 'dikembalikan')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Dikembalikan
                                    </span>
                                @elseif($item->status == 'terlambat')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Terlambat
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-gray-50 text-gray-700 border border-gray-200">
                                        {{ ucfirst($item->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->user->name ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-book-reader text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                                <p class="text-sm text-gray-500">Tidak ada peminjaman yang sesuai dengan filter</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>
@endsection
