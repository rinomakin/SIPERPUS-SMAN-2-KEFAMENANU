@extends('layouts.admin')

@section('title', 'Detail Kunjungan Tamu')

@section('content')
<div class="max-w-12xl mx-auto">
    <!-- Page Header -->
    <!-- <div class="bg-gradient-to-r from-blue-600 to-indigo-700 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">👤 Detail Kunjungan Tamu</h1>
                <p class="text-blue-100 mt-1">Informasi lengkap kunjungan tamu perpustakaan</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.buku-tamu.index') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
                <a href="{{ route('admin.buku-tamu.edit', $kunjungan->id) }}" 
                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
            </div>
        </div>
    </div> -->

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Member Information -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-user mr-2 text-blue-600"></i>
                Informasi Anggota
            </h2>
            
            <div class="space-y-4">
                <!-- Member Photo -->
                <div class="flex items-center space-x-4">
                    <img src="{{ $kunjungan->anggota->foto ? asset('storage/anggota/' . $kunjungan->anggota->foto) : 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 80 80"><rect width="80" height="80" fill="#e5e7eb"/><text x="40" y="48" text-anchor="middle" fill="#9ca3af" font-family="Arial" font-size="28">👤</text></svg>') }}" 
                         alt="Foto Anggota" class="w-20 h-20 rounded-full object-cover border-4 border-gray-200">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900">{{ $kunjungan->anggota->nama_lengkap }}</h3>
                        <p class="text-gray-600">{{ $kunjungan->anggota->nomor_anggota }}</p>
                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full font-medium">
                            {{ ucfirst($kunjungan->anggota->status) }}
                        </span>
                    </div>
                </div>

                <!-- Member Details -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                        <p class="text-gray-900">{{ $kunjungan->anggota->kelas ? $kunjungan->anggota->kelas->nama_kelas : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jurusan</label>
                        <p class="text-gray-900">{{ $kunjungan->anggota->jurusan ? $kunjungan->anggota->jurusan->nama_jurusan : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                        <p class="text-gray-900 font-mono">{{ $kunjungan->anggota->barcode_anggota ?: '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <p class="text-gray-900">{{ $kunjungan->anggota->email ?: '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Information -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">
                <i class="fas fa-clock mr-2 text-green-600"></i>
                Informasi Kunjungan
            </h2>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Waktu Datang</label>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $kunjungan->waktu_datang->format('d F Y H:i:s') }}
                    </p>
                    <p class="text-sm text-gray-500">{{ $kunjungan->waktu_datang->diffForHumans() }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                    <p class="text-gray-900">{{ $kunjungan->waktu_datang->format('l, d F Y') }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                    <p class="text-gray-900">{{ $kunjungan->keterangan ?: 'Tidak ada keterangan' }}</p>
                </div>

                @if($kunjungan->petugas)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Dicatat Oleh</label>
                    <p class="text-gray-900">{{ $kunjungan->petugas->name }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-info-circle mr-2 text-purple-600"></i>
            Informasi Tambahan
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center p-4 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">{{ $kunjungan->waktu_datang->format('H') }}</div>
                <div class="text-sm text-gray-600">Jam</div>
            </div>
            <div class="text-center p-4 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">{{ $kunjungan->waktu_datang->format('i') }}</div>
                <div class="text-sm text-gray-600">Menit</div>
            </div>
            <div class="text-center p-4 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">{{ $kunjungan->waktu_datang->format('s') }}</div>
                <div class="text-sm text-gray-600">Detik</div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <!-- <div class="flex justify-center space-x-4 mt-6">
        <a href="{{ route('admin.buku-tamu.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>
            Kembali ke Daftar
        </a>
        <a href="{{ route('admin.buku-tamu.edit', $kunjungan->id) }}" 
           class="bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
            <i class="fas fa-edit mr-2"></i>
            Edit Data
        </a>
        <form action="{{ route('admin.buku-tamu.destroy', $kunjungan->id) }}" 
              method="POST" 
              class="inline" 
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data kunjungan ini?')">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                <i class="fas fa-trash mr-2"></i>
                Hapus Data
            </button>
        </form>
    </div> -->
</div>
@endsection
