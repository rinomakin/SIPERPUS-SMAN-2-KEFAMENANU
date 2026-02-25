@extends('layouts.admin')

@section('title', 'Laporan Absensi')
@section('page-title', 'Laporan Absensi')

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
                    <h1 class="text-xl font-bold text-gray-900">Laporan Absensi Pengunjung</h1>
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-violet-600">{{ $absensi->count() }}</span> kunjungan</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 bg-white/70">
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 bg-white/70">

                <select name="jenis" class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white/70">
                    <option value="">Semua Jenis</option>
                    <option value="anggota" {{ request('jenis') == 'anggota' ? 'selected' : '' }}>Anggota</option>
                    <option value="tamu" {{ request('jenis') == 'tamu' ? 'selected' : '' }}>Tamu</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>

                <a href="{{ route('admin.laporan.absensi', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.absensi', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        @php
            $totalKunjungan = $absensi->count();
            $kunjunganAnggota = $absensi->where('jenis_pengunjung', 'anggota')->count();
            $kunjunganTamu = $absensi->where('jenis_pengunjung', 'tamu')->count();
        @endphp
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center"><i class="fas fa-clipboard-check text-violet-600"></i></div>
                <div><p class="text-xs text-gray-500">Total Kunjungan</p><p class="text-lg font-bold text-gray-900">{{ $totalKunjungan }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center"><i class="fas fa-id-card text-blue-600"></i></div>
                <div><p class="text-xs text-gray-500">Anggota</p><p class="text-lg font-bold text-gray-900">{{ $kunjunganAnggota }}</p></div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center"><i class="fas fa-user-friends text-purple-600"></i></div>
                <div><p class="text-xs text-gray-500">Tamu</p><p class="text-lg font-bold text-gray-900">{{ $kunjunganTamu }}</p></div>
            </div>
        </div>
    </div>

    {{-- Report Content --}}
    <div>
        <div class="glass-card rounded-2xl shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead>
                        <tr class="bg-gradient-to-r from-violet-50 to-purple-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Tanggal</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Jam Masuk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Jam Keluar</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Nama</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Instansi/Kelas</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-violet-700 uppercase">Keperluan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($absensi as $index => $item)
                        <tr class="hover:bg-violet-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->tanggal ? $item->tanggal->format('d/m/Y') : '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $item->jam_masuk ?: '-' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $item->jam_keluar ?: '-' }}</td>
                            <td class="px-4 py-3">
                                @if($item->jenis_pengunjung === 'anggota' && $item->anggota)
                                    <div class="text-sm font-medium text-gray-900">{{ $item->anggota->nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->anggota->nomor_anggota }}</div>
                                @else
                                    <div class="text-sm font-medium text-gray-900">{{ $item->nama_tamu }}</div>
                                    <div class="text-xs text-gray-500">{{ $item->no_telepon }}</div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($item->jenis_pengunjung === 'anggota')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Anggota
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Tamu
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($item->jenis_pengunjung === 'anggota' && $item->anggota && $item->anggota->kelas)
                                    {{ $item->anggota->kelas->nama_kelas }} - {{ $item->anggota->kelas->jurusan->nama_jurusan ?? '' }}
                                @else
                                    {{ $item->instansi ?: '-' }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->keperluan ?: '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                                    <i class="fas fa-clipboard-check text-2xl text-gray-400"></i>
                                </div>
                                <h3 class="text-base font-semibold text-gray-900 mb-1">Tidak ada data</h3>
                                <p class="text-sm text-gray-500">Tidak ada kunjungan yang sesuai dengan filter</p>
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
