@extends('layouts.admin')

@section('title', 'Laporan Buku')
@section('page-title', 'Laporan Buku')

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
                    <h1 class="text-xl font-bold text-gray-900">Laporan Buku</h1>
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-pink-600">{{ $buku->count() }}</span> buku</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 bg-white/70">
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 bg-white/70">

                <select name="kategori_id" class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white/70">
                    <option value="">Semua Kategori</option>
                    @foreach($kategori as $k)
                        <option value="{{ $k->id }}" {{ request('kategori_id') == $k->id ? 'selected' : '' }}>{{ $k->nama_kategori }}</option>
                    @endforeach
                </select>

                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white/70">
                    <option value="">Semua Status</option>
                    <option value="tersedia" {{ request('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                    <option value="dipinjam" {{ request('status') == 'dipinjam' ? 'selected' : '' }}>Habis</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-pink-500 to-rose-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>

                <a href="{{ route('admin.laporan.buku', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.buku', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $totalBuku = $buku->count();
            $totalStok = $buku->sum('stok');
            $tersedia = $buku->where('stok', '>', 0)->count();
            $habis = $buku->where('stok', 0)->count();
        @endphp
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-pink-100 rounded-xl flex items-center justify-center"><i class="fas fa-book text-pink-600"></i></div>
                <div><p class="text-xs text-gray-500">Judul Buku</p><p class="text-lg font-bold text-gray-900">{{ $totalBuku }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center"><i class="fas fa-layer-group text-blue-600"></i></div>
                <div><p class="text-xs text-gray-500">Total Eksemplar</p><p class="text-lg font-bold text-gray-900">{{ $totalStok }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center"><i class="fas fa-check-circle text-emerald-600"></i></div>
                <div><p class="text-xs text-gray-500">Tersedia</p><p class="text-lg font-bold text-gray-900">{{ $tersedia }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.2s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center"><i class="fas fa-times-circle text-red-600"></i></div>
                <div><p class="text-xs text-gray-500">Habis</p><p class="text-lg font-bold text-gray-900">{{ $habis }}</p></div>
            </div>
        </div>
    </div>

    {{-- Report Content --}}
    <div>
        <div class="glass-card rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gradient-to-r from-pink-50 to-rose-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-pink-700 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-pink-700 uppercase">Judul Buku</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-pink-700 uppercase">ISBN</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-pink-700 uppercase">Pengarang</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-pink-700 uppercase">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-pink-700 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-pink-700 uppercase">Stok</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-pink-700 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($buku as $index => $item)
                        <tr class="hover:bg-pink-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm font-medium text-gray-900">{{ $item->judul_buku }}</div>
                                <div class="text-xs text-gray-500">{{ $item->penerbit }} ({{ $item->tahun_terbit }})</div>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $item->isbn }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->pengarang }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-pink-50 text-pink-700 border border-pink-200">
                                    {{ $item->kategoriBuku->nama_kategori ?? '-' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->jenisBuku->nama_jenis ?? '-' }}</td>
                            <td class="px-4 py-3 text-center text-sm font-semibold text-gray-900">{{ $item->stok }}</td>
                            <td class="px-4 py-3">
                                @if($item->stok > 0)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-red-50 text-red-700 border border-red-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis
                                    </span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
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
</div>
@endsection
