@extends('layouts.admin')

@section('title', 'Edit Kategori Buku')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="space-y-6">
        <!-- Header Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Edit Kategori Buku</h1>
                    <p class="text-gray-600 mt-1">Perbarui informasi kategori "{{ $kategoriBuku->nama_kategori }}"</p>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('kategori-buku.show', $kategoriBuku->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-eye mr-2"></i>
                        Lihat Detail
                    </a>
                    <a href="{{ route('kategori-buku.index') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <form method="POST" action="{{ route('kategori-buku.update', $kategoriBuku->id) }}" class="space-y-6">
                @csrf
                @method('PUT')
                
                <!-- Category Name -->
                <div>
                    <label for="nama_kategori" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Kategori <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama_kategori" name="nama_kategori" value="{{ old('nama_kategori', $kategoriBuku->nama_kategori) }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('nama_kategori') border-red-500 @enderror"
                           placeholder="Contoh: Fiksi, Non-Fiksi, Pendidikan, dll">
                    @error('nama_kategori')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Category Code -->
                <div>
                    <label for="kode_kategori" class="block text-sm font-medium text-gray-700 mb-2">
                        Kode Kategori
                        <span class="text-gray-500 text-sm">(Opsional)</span>
                    </label>
                    <div class="flex space-x-2">
                        <input type="text" id="kode_kategori" name="kode_kategori" value="{{ old('kode_kategori', $kategoriBuku->kode_kategori) }}" maxlength="10"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('kode_kategori') border-red-500 @enderror"
                               placeholder="Contoh: FIK, NFIK, PEND">
                        <button type="button" onclick="generateKode()" 
                                class="px-4 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold transition-all duration-200">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>
                    @error('kode_kategori')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Kode kategori untuk identifikasi singkat</p>
                </div>

                <!-- Description -->
                <div>
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('deskripsi') border-red-500 @enderror"
                              placeholder="Masukkan deskripsi singkat tentang kategori ini (opsional)">{{ old('deskripsi', $kategoriBuku->deskripsi) }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Current Usage Info -->
                @php
                    $bukuCount = $kategoriBuku->buku()->count();
                @endphp
                @if($bukuCount > 0)
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-blue-800">Kategori Sedang Digunakan</h4>
                            <p class="text-sm text-blue-700 mt-1">
                                Kategori ini sedang digunakan oleh {{ $bukuCount }} buku. 
                                Perubahan nama kategori akan mempengaruhi semua buku dalam kategori ini.
                            </p>
                        </div>
                    </div>
                </div>
                @else
                <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-check-circle text-green-500 mt-0.5 mr-3"></i>
                        <div>
                            <h4 class="text-sm font-medium text-green-800">Kategori Belum Digunakan</h4>
                            <p class="text-sm text-green-700 mt-1">
                                Kategori ini belum digunakan oleh buku manapun, sehingga dapat diubah dengan aman.
                            </p>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Action Buttons -->
                <div class="flex items-center justify-end gap-4 pt-6 border-t border-gray-200">
                    <a href="{{ route('kategori-buku.show', $kategoriBuku->id) }}" 
                       class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                        <i class="fas fa-times mr-2"></i>
                        Batal
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-yellow-600 to-yellow-700 hover:from-yellow-700 hover:to-yellow-800 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="fas fa-save mr-2"></i>
                        Update Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form validation
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-500');
                isValid = false;
            } else {
                field.classList.remove('border-red-500');
            }
        });

        if (!isValid) {
            e.preventDefault();
            showWarningAlert('Mohon lengkapi semua field yang wajib diisi');
        }
    });

    // Real-time validation
    const inputs = form.querySelectorAll('input, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('border-red-500');
            } else {
                this.classList.remove('border-red-500');
            }
        });

        input.addEventListener('input', function() {
            if (this.classList.contains('border-red-500') && this.value.trim()) {
                this.classList.remove('border-red-500');
            }
        });
    });
});

// Generate kode kategori
function generateKode() {
    const namaKategori = document.getElementById('nama_kategori').value;
    const kodeInput = document.getElementById('kode_kategori');
    
    if (!namaKategori.trim()) {
        showWarningAlert('Masukkan nama kategori terlebih dahulu');
        return;
    }
    
    // Show loading state
    const button = event.target;
    const originalHTML = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    button.disabled = true;
    
    fetch('/admin/kategori-buku/generate-kode', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            nama_kategori: namaKategori
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            kodeInput.value = data.kode_kategori;
            showSuccessAlert('Kode kategori berhasil digenerate: ' + data.kode_kategori);
        } else {
            showErrorAlert('Gagal generate kode kategori');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showErrorAlert('Terjadi kesalahan saat generate kode');
    })
    .finally(() => {
        button.innerHTML = originalHTML;
        button.disabled = false;
    });
}
</script>
@endsection 