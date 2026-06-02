@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100">
    <!-- Header Section -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Edit Role</h2>
                <p class="text-gray-600 text-sm mt-1">Edit informasi role: {{ $role->nama_peran }}</p>
            </div>
            <a href="{{ route('role.index') }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="p-6">
        <form action="{{ route('role.update', $role->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Nama Role -->
                <div class="lg:col-span-1">
                    <label for="nama_peran" class="block text-sm font-semibold text-gray-700 mb-2">
                        Nama Role <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user-shield text-gray-400"></i>
                        </div>
                        <input type="text" 
                               name="nama_peran" 
                               id="nama_peran" 
                               value="{{ old('nama_peran', $role->nama_peran) }}"
                               class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama_peran') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                               placeholder="Masukkan nama role"
                               required>
                    </div>
                    @error('nama_peran')
                        <p class="text-red-500 text-sm mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Kode Role -->
                <div class="lg:col-span-1">
                    <label for="kode_peran" class="block text-sm font-semibold text-gray-700 mb-2">
                        Kode Role <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-2">
                        <div class="relative flex-1">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-code text-gray-400"></i>
                            </div>
                            <input type="text" 
                                   name="kode_peran" 
                                   id="kode_peran" 
                                   value="{{ old('kode_peran', $role->kode_peran) }}"
                                   class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kode_peran') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                                   placeholder="Masukkan kode role"
                                   required>
                        </div>
                        <button type="button" 
                                id="generateKodeBtn"
                                class="inline-flex items-center px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                            <i class="fas fa-magic mr-2"></i>
                            Generate
                        </button>
                    </div>
                    @error('kode_peran')
                        <p class="text-red-500 text-sm mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="lg:col-span-2">
                    <label for="deskripsi" class="block text-sm font-semibold text-gray-700 mb-2">
                        Deskripsi
                    </label>
                    <div class="relative">
                        <div class="absolute top-3 left-3 flex items-center pointer-events-none">
                            <i class="fas fa-align-left text-gray-400"></i>
                        </div>
                        <textarea name="deskripsi" 
                                  id="deskripsi" 
                                  rows="4"
                                  class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('deskripsi') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                                  placeholder="Masukkan deskripsi role">{{ old('deskripsi', $role->deskripsi) }}</textarea>
                    </div>
                    @error('deskripsi')
                        <p class="text-red-500 text-sm mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Status -->
                <div class="lg:col-span-1">
                    <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-toggle-on text-gray-400"></i>
                        </div>
                        <select name="status" 
                                id="status" 
                                class="block w-full pl-10 pr-3 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                                required>
                            <option value="">Pilih status</option>
                            <option value="aktif" {{ old('status', $role->status) === 'aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status', $role->status) === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    @error('status')
                        <p class="text-red-500 text-sm mt-2 flex items-center">
                            <i class="fas fa-exclamation-circle mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('role.index') }}" 
                   class="inline-flex items-center justify-center px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center justify-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-save mr-2"></i>
                    Update Role
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const namaPeranInput = document.getElementById('nama_peran');
    const kodePeranInput = document.getElementById('kode_peran');
    const generateKodeBtn = document.getElementById('generateKodeBtn');

    // Generate kode otomatis saat nama peran berubah
    namaPeranInput.addEventListener('input', function() {
        if (this.value.trim()) {
            generateKode();
        }
    });

    // Generate kode saat tombol ditekan
    generateKodeBtn.addEventListener('click', function() {
        if (namaPeranInput.value.trim()) {
            generateKode();
        } else {
            // Show alert if nama peran is empty
            showWarningAlert('Silakan masukkan nama role terlebih dahulu!');
        }
    });

    function generateKode() {
        const namaPeran = namaPeranInput.value;
        
        // Show loading state
        generateKodeBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Generating...';
        generateKodeBtn.disabled = true;
        
        fetch('/admin/role/generate-kode', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                nama_peran: namaPeran
            })
        })
        .then(response => response.json())
        .then(data => {
            kodePeranInput.value = data.kode_peran;
            // Reset button state
            generateKodeBtn.innerHTML = '<i class="fas fa-magic mr-2"></i>Generate';
            generateKodeBtn.disabled = false;
        })
        .catch(error => {
            console.error('Error:', error);
            // Reset button state on error
            generateKodeBtn.innerHTML = '<i class="fas fa-magic mr-2"></i>Generate';
            generateKodeBtn.disabled = false;
        });
    }
});
</script>
@endpush
@endsection
