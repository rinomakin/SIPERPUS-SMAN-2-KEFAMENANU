@extends('layouts.admin')

@section('title', 'Detail Kategori Buku')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Kategori Buku</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap kategori buku</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('kategori-buku.edit', $kategoriBuku->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Kategori
                    </a>
                    <a href="{{ route('kategori-buku.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Category Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Informasi Kategori
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Nama Kategori</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $kategoriBuku->nama_kategori }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <span class="w-2 h-2 rounded-full mr-1.5 bg-green-400"></span>
                                Aktif
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Dibuat Pada</label>
                            <p class="text-gray-900">{{ $kategoriBuku->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Terakhir Diupdate</label>
                            <p class="text-gray-900">{{ $kategoriBuku->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($kategoriBuku->deskripsi)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-align-left text-orange-500 mr-2"></i>
                        Deskripsi
                    </h3>
                    
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed">{{ $kategoriBuku->deskripsi }}</p>
                    </div>
                </div>
                @endif

                <!-- Books in this Category -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-book text-green-500 mr-2"></i>
                        Buku dalam Kategori Ini
                    </h3>
                    
                    <div class="text-center py-8">
                        <div class="text-4xl font-bold text-blue-600 mb-2">{{ $bukuCount }}</div>
                        <div class="text-sm text-gray-500">Total Buku</div>
                        
                        @if($bukuCount > 0)
                        <div class="mt-4">
                            <a href="{{ route('buku.index', ['kategori_id' => $kategoriBuku->id]) }}" 
                               class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <i class="fas fa-eye mr-2"></i>
                                Lihat Semua Buku
                            </a>
                        </div>
                        @else
                        <div class="mt-4 text-sm text-gray-500">
                            Belum ada buku dalam kategori ini
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Category Icon -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-tags text-purple-500 mr-2"></i>
                        Kategori
                    </h3>
                    
                    <div class="bg-gradient-to-br from-purple-50 to-indigo-100 rounded-lg p-8 text-center">
                        <i class="fas fa-tags text-6xl text-purple-500 mb-4"></i>
                        <h4 class="text-lg font-semibold text-purple-800 mb-2">{{ $kategoriBuku->nama_kategori }}</h4>
                        <p class="text-sm text-purple-600">Kategori Buku</p>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                        Aksi Cepat
                    </h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('kategori-buku.edit', $kategoriBuku->id) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Kategori
                        </a>
                        
                        @if($bukuCount == 0)
                        <button onclick="deleteKategori()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Kategori
                        </button>
                        @else
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-exclamation-triangle text-yellow-500 mt-0.5 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-medium text-yellow-800">Tidak Dapat Dihapus</h4>
                                    <p class="text-sm text-yellow-700 mt-1">
                                        Kategori ini masih digunakan oleh {{ $bukuCount }} buku
                                    </p>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Statistics -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-bar text-green-500 mr-2"></i>
                        Statistik
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total Buku</span>
                            <span class="text-sm font-medium text-gray-900">{{ $bukuCount }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dibuat</span>
                            <span class="text-sm font-medium text-gray-900">{{ $kategoriBuku->created_at->format('d/m/Y') }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Diupdate</span>
                            <span class="text-sm font-medium text-gray-900">{{ $kategoriBuku->updated_at->format('d/m/Y') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-700">Memproses...</span>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    // Delete kategori function
    window.deleteKategori = function() {
        showConfirmDialog(
            'Yakin ingin menghapus kategori ini? Tindakan ini tidak dapat dibatalkan.',
            'Konfirmasi Hapus Kategori',
            function() {
                showLoading();
                fetch('/admin/kategori-buku/' + {{ $kategoriBuku->id }}, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showSuccessAlert('Kategori berhasil dihapus');
                        window.location.href = '/admin/kategori-buku';
                    } else {
                        showErrorAlert('Gagal menghapus kategori: ' + data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showErrorAlert('Terjadi kesalahan saat menghapus kategori');
                });
            }
        );
    };
});
</script>
@endsection 