@extends('layouts.admin')

@section('title', 'Detail Rak Buku')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Rak Buku</h1>
                <p class="text-gray-600 mt-1">{{ $rakBuku->nama_rak }} ({{ $rakBuku->kode_rak }})</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('rak-buku.edit', $rakBuku->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('rak-buku.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Detail Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Nama Rak</label>
                        <p class="text-sm text-gray-900 font-medium">{{ $rakBuku->nama_rak }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Kode Rak</label>
                        <p class="text-sm text-gray-900 font-mono">{{ $rakBuku->kode_rak }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Lokasi</label>
                        <p class="text-sm text-gray-900">{{ $rakBuku->lokasi ?? 'Tidak ditentukan' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($rakBuku->status == 'Aktif') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                            <span class="w-2 h-2 rounded-full mr-1.5
                                @if($rakBuku->status == 'Aktif') bg-green-400 @else bg-red-400 @endif"></span>
                            {{ $rakBuku->status }}
                        </span>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Kapasitas</label>
                        <p class="text-sm text-gray-900">{{ $rakBuku->kapasitas }} buku</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Jumlah Buku</label>
                        <p class="text-sm text-gray-900">{{ $rakBuku->jumlah_buku }} buku</p>
                    </div>
                </div>
                
                @if($rakBuku->deskripsi)
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-500">Deskripsi</label>
                    <p class="text-sm text-gray-900 mt-1">{{ $rakBuku->deskripsi }}</p>
                </div>
                @endif
            </div>

            <!-- Capacity Info -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kapasitas</h2>
                <div class="space-y-4">
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Penggunaan Kapasitas</span>
                            <span class="text-sm font-medium text-gray-900">{{ $rakBuku->jumlah_buku }}/{{ $rakBuku->kapasitas }}</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            @php
                                $percentage = $rakBuku->kapasitas > 0 ? ($rakBuku->jumlah_buku / $rakBuku->kapasitas) * 100 : 0;
                            @endphp
                            <div class="h-2.5 rounded-full 
                                @if($percentage >= 90) bg-red-600 
                                @elseif($percentage >= 70) bg-yellow-500 
                                @else bg-green-600 @endif" 
                                 style="width: {{ $percentage }}%"></div>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-xs text-gray-500">0%</span>
                            <span class="text-xs text-gray-500">100%</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-green-50 rounded-lg">
                            <div class="text-2xl font-bold text-green-600">{{ $rakBuku->getSisaKapasitas() }}</div>
                            <div class="text-sm text-green-700">Sisa Kapasitas</div>
                        </div>
                        <div class="text-center p-4 bg-blue-50 rounded-lg">
                            <div class="text-2xl font-bold text-blue-600">{{ $rakBuku->jumlah_buku }}</div>
                            <div class="text-sm text-blue-700">Buku Tersedia</div>
                        </div>
                        <div class="text-center p-4 
                            @if($rakBuku->isFull()) bg-red-50 @else bg-gray-50 @endif rounded-lg">
                            <div class="text-2xl font-bold 
                                @if($rakBuku->isFull()) text-red-600 @else text-gray-600 @endif">
                                @if($rakBuku->isFull()) Penuh @else Tersedia @endif
                            </div>
                            <div class="text-sm 
                                @if($rakBuku->isFull()) text-red-700 @else text-gray-700 @endif">Status</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Stats -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Dibuat</span>
                        <span class="text-sm font-medium text-gray-900">{{ $rakBuku->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Terakhir Update</span>
                        <span class="text-sm font-medium text-gray-900">{{ $rakBuku->updated_at->format('d M Y') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-600">Persentase Terisi</span>
                        <span class="text-sm font-medium text-gray-900">
                            @php
                                $percentage = $rakBuku->kapasitas > 0 ? round(($rakBuku->jumlah_buku / $rakBuku->kapasitas) * 100, 1) : 0;
                            @endphp
                            {{ $percentage }}%
                        </span>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h2>
                <div class="space-y-3">
                    <a href="{{ route('rak-buku.edit', $rakBuku->id) }}" 
                       class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white font-medium rounded-lg transition-all duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Rak
                    </a>
                    <button onclick="deleteRak({{ $rakBuku->id }})" 
                            class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-all duration-200">
                        <i class="fas fa-trash mr-2"></i>
                        Hapus Rak
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Books in this rack -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Buku di Rak Ini</h2>
            <p class="text-sm text-gray-600 mt-1">Daftar buku yang tersimpan di rak {{ $rakBuku->nama_rak }}</p>
        </div>
        
        @if($rakBuku->buku->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cover</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penulis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Penerbit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($rakBuku->buku as $buku)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex-shrink-0 h-16 w-12">
                            @if($buku->gambar_sampul)
                                <img src="{{ asset('uploads/' . $buku->gambar_sampul) }}" alt="Cover {{ $buku->judul_buku }}" class="h-16 w-12 object-cover rounded-lg">
                                @else
                                    <div class="h-16 w-12 rounded-lg bg-gradient-to-br from-blue-50 to-indigo-100 flex items-center justify-center">
                                        <i class="fas fa-book text-blue-500"></i>
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $buku->judul_buku }}</div>
                            <div class="text-sm text-gray-500">{{ $buku->isbn ?? 'ISBN tidak tersedia' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $buku->penulis ?? 'Tidak diketahui' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $buku->penerbit ?? 'Tidak diketahui' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $buku->stok }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($buku->status == 'Tersedia') bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                {{ $buku->status }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-12 text-center">
            <div class="mx-auto w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-books text-3xl text-gray-400"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada buku di rak ini</h3>
            <p class="text-gray-600">Rak ini masih kosong dan siap untuk diisi dengan buku-buku.</p>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.deleteRak = function(id) {
        if (!confirm('Yakin ingin menghapus rak buku ini? Tindakan ini tidak dapat dibatalkan.')) {
            return;
        }

        fetch(`/admin/rak-buku/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showSuccessAlert('Rak buku berhasil dihapus');
                window.location.href = '{{ route("rak-buku.index") }}';
            } else {
                showErrorAlert('Gagal menghapus rak buku: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showErrorAlert('Terjadi kesalahan saat menghapus rak buku');
        });
    };
});
</script>
@endsection
