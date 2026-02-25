@extends('layouts.admin')

@section('title', 'Laporan Anggota')
@section('page-title', 'Laporan Anggota')

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
                    <h1 class="text-xl font-bold text-gray-900">Laporan Anggota</h1>
                    <p class="text-sm text-gray-500">Total: <span class="font-semibold text-indigo-600">{{ $anggota->count() }}</span> anggota</p>
                </div>
            </div>

            <form method="GET" class="flex flex-wrap items-center gap-2">
                <input type="date" name="tanggal_mulai" value="{{ request('tanggal_mulai') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white/70">
                <span class="text-gray-400 text-xs">s/d</span>
                <input type="date" name="tanggal_akhir" value="{{ request('tanggal_akhir') }}"
                       class="px-3 py-2 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-white/70">

                <select name="jenis_anggota" class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white/70">
                    <option value="">Semua Jenis</option>
                    <option value="siswa" {{ request('jenis_anggota') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                    <option value="guru" {{ request('jenis_anggota') == 'guru' ? 'selected' : '' }}>Guru</option>
                    <option value="staff" {{ request('jenis_anggota') == 'staff' ? 'selected' : '' }}>Staff</option>
                </select>

                <select name="status" class="px-3 py-2 text-sm border border-gray-200 rounded-xl bg-white/70">
                    <option value="">Semua Status</option>
                    <option value="aktif" {{ request('status') == 'aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="nonaktif" {{ request('status') == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                    <option value="ditangguhkan" {{ request('status') == 'ditangguhkan' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>

                <button type="submit" class="px-4 py-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>

                <a href="{{ route('admin.laporan.anggota', array_merge(request()->query(), ['export' => 'excel'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-emerald-500 to-green-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-excel mr-1"></i> Excel
                </a>

                <a href="{{ route('admin.laporan.anggota', array_merge(request()->query(), ['export' => 'pdf'])) }}"
                   class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-file-pdf mr-1"></i> PDF
                </a>
            </form>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        @php
            $totalAnggota = $anggota->count();
            $siswa = $anggota->where('jenis_anggota', 'siswa')->count();
            $guru = $anggota->where('jenis_anggota', 'guru')->count();
            $aktif = $anggota->where('status', 'aktif')->count();
        @endphp
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-indigo-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total</p>
                    <p class="text-lg font-bold text-gray-900">{{ $totalAnggota }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-graduate text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Siswa</p>
                    <p class="text-lg font-bold text-gray-900">{{ $siswa }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chalkboard-teacher text-green-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Guru</p>
                    <p class="text-lg font-bold text-gray-900">{{ $guru }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.2s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-check-circle text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Aktif</p>
                    <p class="text-lg font-bold text-gray-900">{{ $aktif }}</p>
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
                        <tr class="bg-gradient-to-r from-indigo-50 to-purple-50">
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">No</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">Nama Lengkap</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">NIS/NIK</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">No. Anggota</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">L/P</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">Kelas/Jurusan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">Jenis</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">Status</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-indigo-700 uppercase">Tgl Daftar</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($anggota as $index => $item)
                        <tr class="hover:bg-indigo-50/30 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $item->nama_lengkap }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $item->nis ?: $item->nik }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 font-mono">{{ $item->nomor_anggota }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->jenis_kelamin == 'Laki-laki' ? 'L' : 'P' }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                @if($item->kelas)
                                    {{ $item->kelas->nama_kelas }} - {{ $item->kelas->jurusan->nama_jurusan ?? '' }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium border
                                    {{ $item->jenis_anggota == 'siswa' ? 'bg-blue-50 text-blue-700 border-blue-200' :
                                       ($item->jenis_anggota == 'guru' ? 'bg-green-50 text-green-700 border-green-200' : 'bg-purple-50 text-purple-700 border-purple-200') }}">
                                    {{ ucfirst($item->jenis_anggota) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium border
                                    {{ $item->status == 'aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                       ($item->status == 'nonaktif' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $item->status == 'aktif' ? 'bg-emerald-500' : ($item->status == 'nonaktif' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">{{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}</td>
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
</div>
@endsection
