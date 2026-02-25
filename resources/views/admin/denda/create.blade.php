@extends('layouts.admin')

@section('title', 'Tambah Denda')

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <!-- <div class="bg-gradient-to-r from-red-600 to-pink-700 rounded-xl shadow-lg p-6 mb-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">➕ Tambah Denda</h1>
                <p class="text-red-100 mt-1">Tambah data denda keterlambatan pengembalian buku</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.denda.index') }}" 
                   class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div> -->

    <!-- Create Form -->
    <div class="bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">
            <i class="fas fa-plus mr-2 text-red-600"></i>
            Form Tambah Denda
        </h2>

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6">
            <div class="flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                <div>
                    <h4 class="font-medium">Terjadi kesalahan:</h4>
                    <ul class="list-disc list-inside mt-1 text-sm">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('admin.denda.store') }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Peminjaman Selection -->
                <div>
                    <label for="peminjaman_id" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-book mr-1"></i>
                        Pilih Peminjaman <span class="text-red-500">*</span>
                    </label>
                    <select id="peminjaman_id" name="peminjaman_id" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('peminjaman_id') border-red-500 @enderror">
                        <option value="">Pilih peminjaman terlambat...</option>
                        @foreach($peminjamanTerlambat as $peminjaman)
                            <option value="{{ $peminjaman->id }}" 
                                    {{ old('peminjaman_id') == $peminjaman->id ? 'selected' : '' }}
                                    data-anggota="{{ $peminjaman->anggota->nama_lengkap }}"
                                    data-tanggal-harus-kembali="{{ $peminjaman->tanggal_harus_kembali }}">
                                ID: {{ $peminjaman->id }} - {{ $peminjaman->anggota->nama_lengkap }}
                                ({{ $peminjaman->tanggal_harus_kembali }})
                            </option>
                        @endforeach
                    </select>
                    @error('peminjaman_id')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Hari Terlambat -->
                <div>
                    <label for="jumlah_hari_terlambat" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-times mr-1"></i>
                        Jumlah Hari Terlambat <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="jumlah_hari_terlambat" name="jumlah_hari_terlambat" 
                           value="{{ old('jumlah_hari_terlambat') }}"
                           min="1"
                           placeholder="Masukkan jumlah hari terlambat..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('jumlah_hari_terlambat') border-red-500 @enderror">
                    @error('jumlah_hari_terlambat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Denda -->
                <div>
                    <label for="jumlah_denda" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave mr-1"></i>
                        Jumlah Denda (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="jumlah_denda" name="jumlah_denda" 
                           value="{{ old('jumlah_denda') }}"
                           min="0"
                           step="100"
                           placeholder="Masukkan jumlah denda..."
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('jumlah_denda') border-red-500 @enderror">
                    @error('jumlah_denda')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Pembayaran -->
                <div>
                    <label for="status_pembayaran" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-credit-card mr-1"></i>
                        Status Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <select id="status_pembayaran" name="status_pembayaran" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('status_pembayaran') border-red-500 @enderror">
                        <option value="belum_dibayar" {{ old('status_pembayaran') == 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                        <option value="sudah_dibayar" {{ old('status_pembayaran') == 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
                    </select>
                    @error('status_pembayaran')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Pembayaran -->
                <div id="tanggal_pembayaran_div" class="hidden">
                    <label for="tanggal_pembayaran" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check mr-1"></i>
                        Tanggal Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <input type="date" id="tanggal_pembayaran" name="tanggal_pembayaran" 
                           value="{{ old('tanggal_pembayaran', date('Y-m-d')) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('tanggal_pembayaran') border-red-500 @enderror">
                    @error('tanggal_pembayaran')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Catatan -->
                <div class="lg:col-span-2">
                    <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-sticky-note mr-1"></i>
                        Catatan
                    </label>
                    <textarea id="catatan" name="catatan" rows="3" 
                              placeholder="Masukkan catatan tambahan (opsional)..."
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent @error('catatan') border-red-500 @enderror">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Selected Peminjaman Info -->
            <div id="selected-peminjaman-info" class="mt-6 p-4 bg-gray-50 rounded-lg hidden">
                <h3 class="font-medium text-gray-900 mb-2">Informasi Peminjaman Terpilih:</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-gray-700">Anggota:</span>
                        <span id="selected-anggota" class="text-gray-900"></span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Tanggal Harus Kembali:</span>
                        <span id="selected-tanggal" class="text-gray-900"></span>
                    </div>
                    <div>
                        <span class="font-medium text-gray-700">Status:</span>
                        <span class="text-red-600 font-medium">Terlambat</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('admin.denda.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
                <button type="submit" 
                        class="bg-red-500 hover:bg-red-600 text-white px-6 py-3 rounded-lg font-medium transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Denda
                </button>
            </div>
        </form>
    </div>

    <!-- Quick Actions -->
    <!-- <div class="bg-white rounded-xl shadow-lg p-6 mt-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">
            <i class="fas fa-lightning-bolt mr-2 text-blue-600"></i>
            Aksi Cepat
        </h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <a href="{{ route('admin.denda.index') }}" 
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors duration-200">
                <i class="fas fa-list text-blue-600 text-xl mr-3"></i>
                <div>
                    <h3 class="font-medium text-gray-900">Lihat Daftar Denda</h3>
                    <p class="text-sm text-gray-600">Lihat semua data denda</p>
                </div>
            </a>
            <a href="{{ route('admin.laporan.denda') }}" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors duration-200">
                <i class="fas fa-chart-bar text-green-600 text-xl mr-3"></i>
                <div>
                    <h3 class="font-medium text-gray-900">Laporan Denda</h3>
                    <p class="text-sm text-gray-600">Lihat laporan denda</p>
                </div>
            </a>
        </div>
    </div> -->
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const peminjamanSelect = document.getElementById('peminjaman_id');
    const statusPembayaranSelect = document.getElementById('status_pembayaran');
    const tanggalPembayaranDiv = document.getElementById('tanggal_pembayaran_div');
    const selectedPeminjamanInfo = document.getElementById('selected-peminjaman-info');
    const selectedAnggota = document.getElementById('selected-anggota');
    const selectedTanggal = document.getElementById('selected-tanggal');

    // Update selected peminjaman info
    function updateSelectedPeminjamanInfo() {
        const selectedOption = peminjamanSelect.options[peminjamanSelect.selectedIndex];
        
        if (peminjamanSelect.value) {
            selectedAnggota.textContent = selectedOption.dataset.anggota;
            selectedTanggal.textContent = selectedOption.dataset.tanggalHarusKembali;
            selectedPeminjamanInfo.classList.remove('hidden');
        } else {
            selectedPeminjamanInfo.classList.add('hidden');
        }
    }

    // Show/hide tanggal pembayaran based on status
    function updateTanggalPembayaran() {
        if (statusPembayaranSelect.value === 'sudah_dibayar') {
            tanggalPembayaranDiv.classList.remove('hidden');
        } else {
            tanggalPembayaranDiv.classList.add('hidden');
        }
    }

    // Event listeners
    peminjamanSelect.addEventListener('change', updateSelectedPeminjamanInfo);
    statusPembayaranSelect.addEventListener('change', updateTanggalPembayaran);
    
    // Initialize on page load
    updateSelectedPeminjamanInfo();
    updateTanggalPembayaran();
});
</script>
@endsection
