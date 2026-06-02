@extends('layouts.admin')

@section('title', 'Detail Buku')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Detail Buku</h1>
                    <p class="text-gray-600 mt-1">Informasi lengkap buku perpustakaan</p>
                </div>
                <div class="flex items-center gap-3">
                    @if(Auth::user()->hasPermission('buku.edit'))
                    <a href="{{ route('buku.edit', $buku->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Buku
                    </a>
                    @endif
                    <a href="{{ route('buku.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Book Information -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Informasi Dasar
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Judul Buku</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $buku->judul_buku }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">ISBN</label>
                            <p class="text-gray-900">{{ $buku->isbn ?? 'Tidak ada ISBN' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Tahun Terbit</label>
                            <p class="text-gray-900">{{ $buku->tahun_terbit ?? 'Tidak diketahui' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Jumlah Halaman</label>
                            <p class="text-gray-900">{{ $buku->jumlah_halaman ?? 'Tidak diketahui' }} halaman</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Lokasi Rak</label>
                            <p class="text-gray-900">{{ $buku->lokasi_rak ?? 'Belum ditentukan' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Status</label>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($buku->stok_tersedia > 0) bg-green-100 text-green-800 @else bg-red-100 text-red-800 @endif">
                                <span class="w-2 h-2 rounded-full mr-1.5
                                    @if($buku->stok_tersedia > 0) bg-green-400 @else bg-red-400 @endif"></span>
                                {{ $buku->stok_tersedia > 0 ? 'Tersedia' : 'Habis' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Author and Publisher -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-user-edit text-green-500 mr-2"></i>
                        Penulis & Penerbit
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Penulis</label>
                            <p class="text-gray-900">{{ $buku->penulis ?? 'Tidak diketahui' }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Penerbit</label>
                            <p class="text-gray-900">{{ $buku->penerbit ?? 'Tidak diketahui' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Category and Type -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-tags text-purple-500 mr-2"></i>
                        Kategori & Jenis
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Kategori</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                {{ $buku->kategori->nama_kategori ?? 'Tidak diketahui' }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Jenis Buku</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                {{ $buku->jenis->nama_jenis ?? 'Tidak diketahui' }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500 mb-1">Sumber</label>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-orange-100 text-orange-800">
                                {{ $buku->sumber->nama_sumber ?? 'Tidak diketahui' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Stock Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-boxes text-indigo-500 mr-2"></i>
                        Informasi Stok
                    </h3>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-3xl font-bold text-blue-600">{{ $buku->stok_tersedia }}</div>
                            <div class="text-sm text-gray-500">Stok Tersedia</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold text-gray-600">{{ $buku->jumlah_stok }}</div>
                            <div class="text-sm text-gray-500">Total Stok</div>
                        </div>
                        
                        <div class="text-center">
                            <div class="text-3xl font-bold text-red-600">{{ $buku->jumlah_stok - $buku->stok_tersedia }}</div>
                            <div class="text-sm text-gray-500">Sedang Dipinjam</div>
                        </div>
                    </div>
                    
                    <!-- Progress Bar -->
                    <div class="mt-4">
                        <div class="flex justify-between text-sm text-gray-600 mb-1">
                            <span>Persentase Ketersediaan</span>
                            <span>{{ $buku->jumlah_stok > 0 ? round(($buku->stok_tersedia / $buku->jumlah_stok) * 100, 1) : 0 }}%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full transition-all duration-300" 
                                 style="width: {{ $buku->jumlah_stok > 0 ? ($buku->stok_tersedia / $buku->jumlah_stok) * 100 : 0 }}%"></div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                @if($buku->deskripsi)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-align-left text-orange-500 mr-2"></i>
                        Deskripsi
                    </h3>
                    
                    <div class="prose max-w-none">
                        <p class="text-gray-700 leading-relaxed">{{ $buku->deskripsi }}</p>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Book Cover -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-book text-blue-500 mr-2"></i>
                        Cover Buku
                    </h3>
                    
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-lg p-8 text-center">
                        <i class="fas fa-book text-6xl text-blue-500 mb-4"></i>
                        <h4 class="text-lg font-semibold text-blue-800 mb-2">{{ $buku->kategori->nama_kategori ?? 'Kategori' }}</h4>
                        <p class="text-sm text-blue-600">{{ $buku->jenis->nama_jenis ?? 'Jenis' }}</p>
                    </div>
                </div>

                <!-- Barcode Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-barcode text-purple-500 mr-2"></i>
                        Barcode
                    </h3>
                    
                    @if($buku->barcode)
                        <div class="text-center">
                            <div id="barcode" class="mb-4"></div>
                            <p class="text-sm text-gray-600 font-mono">{{ $buku->barcode }}</p>
                            
                            @if(Auth::user()->hasPermission('buku.print-barcode'))
                            <div class="flex flex-col gap-2 mt-4">
                                <a href="{{ route('buku.print-barcode', $buku->id) }}" target="_blank"
                                   class="inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                    <i class="fas fa-print mr-2"></i>
                                    Cetak Barcode
                                </a>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="text-center">
                            <div class="bg-gray-100 rounded-lg p-6 mb-4">
                                <i class="fas fa-barcode text-4xl text-gray-400 mb-2"></i>
                                <p class="text-sm text-gray-600">Belum ada barcode</p>
                            </div>
                            
                            @if(Auth::user()->hasPermission('buku.generate-barcode'))
                            <button onclick="generateBarcode()" 
                                    class="inline-flex items-center justify-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                                <i class="fas fa-barcode mr-2"></i>
                                Generate Barcode
                            </button>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-bolt text-yellow-500 mr-2"></i>
                        Aksi Cepat
                    </h3>
                    
                    <div class="space-y-3">
                        @if(Auth::user()->hasPermission('buku.edit'))
                        <a href="{{ route('buku.edit', $buku->id) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Buku
                        </a>
                        @endif
                        
                        @if($buku->barcode && Auth::user()->hasPermission('buku.print-barcode'))
                        <a href="{{ route('buku.print-barcode', $buku->id) }}" target="_blank"
                           class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                            <i class="fas fa-print mr-2"></i>
                            Cetak Barcode
                        </a>
                        @endif
                        
                        @if(Auth::user()->hasPermission('buku.delete'))
                        <button onclick="deleteBuku()" 
                                class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Buku
                        </button>
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
                            <span class="text-sm text-gray-600">Ketersediaan</span>
                            <span class="text-sm font-medium text-gray-900">
                                {{ $buku->jumlah_stok > 0 ? round(($buku->stok_tersedia / $buku->jumlah_stok) * 100, 1) : 0 }}%
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Dipinjam</span>
                            <span class="text-sm font-medium text-gray-900">{{ $buku->jumlah_stok - $buku->stok_tersedia }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-600">Total</span>
                            <span class="text-sm font-medium text-gray-900">{{ $buku->jumlah_stok }}</span>
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

<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generate barcode if exists
    @if($buku->barcode)
        JsBarcode("#barcode", "{{ $buku->barcode }}", {
            format: "CODE128",
            width: 2,
            height: 50,
            displayValue: false
        });
    @endif

    function showLoading() {
        document.getElementById('loadingOverlay').classList.remove('hidden');
    }

    function hideLoading() {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }

    // Generate barcode function
    window.generateBarcode = function() {
        showConfirmDialog(
            'Apakah Anda yakin ingin generate barcode untuk buku ini?',
            'Konfirmasi Generate Barcode',
            function() {
                showLoading();
                fetch('/admin/buku/generate-barcode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        buku_id: {{ $buku->id }}
                    })
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showSuccessAlert('Barcode berhasil di-generate: ' + data.barcode);
                        location.reload();
                    } else {
                        showErrorAlert('Gagal generate barcode: ' + data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showErrorAlert('Terjadi kesalahan saat generate barcode');
                });
            }
        );
    };

    // Delete buku function
    window.deleteBuku = function() {
        showConfirmDialog(
            'Apakah Anda yakin ingin menghapus buku ini? Tindakan ini tidak dapat dibatalkan.',
            'Konfirmasi Hapus Buku',
            function() {
                showLoading();
                fetch('/admin/buku/' + {{ $buku->id }}, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        showSuccessAlert('Buku berhasil dihapus');
                        window.location.href = '/admin/buku';
                    } else {
                        showErrorAlert('Gagal menghapus buku: ' + data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    showErrorAlert('Terjadi kesalahan saat menghapus buku');
                });
            }
        );
    };
});
</script>
@endsection 