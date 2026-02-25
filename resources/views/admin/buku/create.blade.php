@extends('layouts.admin')

@section('title', 'Tambah Buku Baru')
@section('page-title', 'Tambah Buku Baru')

@section('content')
<div class="max-w-7xl mx-auto">
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
        <!-- Error Display -->
        @if($errors->any())
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-red-500 mt-0.5 mr-3"></i>
                    <div>
                        <h4 class="text-sm font-medium text-red-800">Terjadi Kesalahan</h4>
                        <ul class="text-sm text-red-700 mt-1 list-disc list-inside">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('buku.store') }}" enctype="multipart/form-data">
            @csrf
            
            <!-- All Form Fields in One Section -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Judul Buku -->
                <div class="lg:col-span-2">
                    <label for="judul_buku" class="block text-sm font-medium text-gray-700 mb-2">
                        Judul Buku <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="judul_buku" name="judul_buku" value="{{ old('judul_buku') }}" required
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('judul_buku') border-red-500 @enderror"
                           placeholder="Masukkan judul buku">
                    @error('judul_buku')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Stok -->
                <div>
                    <label for="jumlah_stok" class="block text-sm font-medium text-gray-700 mb-2">
                        Jumlah Stok <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok', 1) }}" required min="1"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('jumlah_stok') border-red-500 @enderror"
                           placeholder="Jumlah stok">
                    @error('jumlah_stok')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Penulis -->
                <div>
                    <label for="penulis" class="block text-sm font-medium text-gray-700 mb-2">
                        Penulis <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="penulis" name="penulis" required value="{{ old('penulis') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('penulis') border-red-500 @enderror"
                           placeholder="Nama penulis">
                    @error('penulis')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Penerbit -->
                <div>
                    <label for="penerbit" class="block text-sm font-medium text-gray-700 mb-2">
                        Penerbit <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="penerbit" name="penerbit" required value="{{ old('penerbit') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('penerbit') border-red-500 @enderror"
                           placeholder="Nama penerbit">
                    @error('penerbit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tahun Terbit -->
                <div>
                    <label for="tahun_terbit" class="block text-sm font-medium text-gray-700 mb-2">Tahun Terbit</label>
                    <input type="number" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit', date('Y')) }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('tahun_terbit') border-red-500 @enderror"
                           placeholder="Tahun terbit">
                    @error('tahun_terbit')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Barcode -->
                <div class="lg:col-span-2">
                    <label for="barcode" class="block text-sm font-medium text-gray-700 mb-2">Barcode</label>
                    <div class="flex space-x-2">
                        <input type="text" id="barcode" name="barcode" value="{{ old('barcode') }}"
                               class="flex-1 px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('barcode') border-red-500 @enderror"
                               placeholder="Kosongkan untuk generate otomatis">
                        <button type="button" id="scanBarcodeBtn" 
                                class="px-3 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg" title="Scan Barcode">
                            <i class="fas fa-qrcode"></i>
                        </button>
                        <button type="button" id="generateBarcodeBtn" 
                                class="px-3 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg" title="Generate Barcode">
                            <i class="fas fa-magic"></i>
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk generate otomatis</p>
                    @error('barcode')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ISBN -->
                <div>
                    <label for="isbn" class="block text-sm font-medium text-gray-700 mb-2">ISBN</label>
                    <input type="text" id="isbn" name="isbn" value="{{ old('isbn') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('isbn') border-red-500 @enderror"
                           placeholder="ISBN">
                    @error('isbn')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Kategori -->
                <div>
                    <label for="kategori_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select id="kategori_id" name="kategori_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('kategori_id') border-red-500 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach($kategoris as $kat)
                            <option value="{{ $kat->id }}" {{ old('kategori_id') == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    @error('kategori_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis Buku -->
                <div>
                    <label for="jenis_id" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Buku <span class="text-red-500">*</span>
                    </label>
                    <select id="jenis_id" name="jenis_id" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('jenis_id') border-red-500 @enderror">
                        <option value="">Pilih Jenis</option>
                        @foreach($jenis as $jen)
                            <option value="{{ $jen->id }}" {{ old('jenis_id') == $jen->id ? 'selected' : '' }}>
                                {{ $jen->nama_jenis }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Sumber Buku -->
                <div>
                    <label for="sumber_id" class="block text-sm font-medium text-gray-700 mb-2">Sumber Buku</label>
                    <select id="sumber_id" name="sumber_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('sumber_id') border-red-500 @enderror">
                        <option value="">Pilih Sumber</option>
                        @foreach($sumber as $sum)
                            <option value="{{ $sum->id }}" {{ old('sumber_id') == $sum->id ? 'selected' : '' }}>
                                {{ $sum->nama_sumber }}
                            </option>
                        @endforeach
                    </select>
                    @error('sumber_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Halaman -->
                <div>
                    <label for="jumlah_halaman" class="block text-sm font-medium text-gray-700 mb-2">Jumlah Halaman</label>
                    <input type="number" id="jumlah_halaman" name="jumlah_halaman" value="{{ old('jumlah_halaman') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('jumlah_halaman') border-red-500 @enderror"
                           placeholder="Jumlah halaman">
                    @error('jumlah_halaman')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Bahasa -->
                <div>
                    <label for="bahasa" class="block text-sm font-medium text-gray-700 mb-2">Bahasa</label>
                    <input type="text" id="bahasa" name="bahasa" value="{{ old('bahasa', 'Indonesia') }}"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('bahasa') border-red-500 @enderror"
                           placeholder="Indonesia, Inggris, Arab">
                    @error('bahasa')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Rak Buku -->
                <div>
                    <label for="rak_id" class="block text-sm font-medium text-gray-700 mb-2">Rak Buku</label>
                    <select id="rak_id" name="rak_id"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('rak_id') border-red-500 @enderror">
                        <option value="">Pilih Rak Buku</option>
                        @foreach($rakBuku as $rak)
                            <option value="{{ $rak->id }}" {{ old('rak_id') == $rak->id ? 'selected' : '' }}>
                                {{ $rak->nama_rak }} - {{ $rak->lokasi ?? 'Lokasi tidak ditentukan' }}
                            </option>
                        @endforeach
                    </select>
                    @error('rak_id')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status -->
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                        Status <span class="text-red-500">*</span>
                    </label>
                    <select id="status" name="status" required
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('status') border-red-500 @enderror">
                        <option value="">Pilih Status</option>
                        <option value="tersedia" {{ old('status') == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="tidak_tersedia" {{ old('status') == 'tidak_tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                    </select>
                    @error('status')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Gambar Sampul -->
                <div>
                    <label for="gambar_sampul" class="block text-sm font-medium text-gray-700 mb-2">Gambar Sampul</label>
                    <input type="file" id="gambar_sampul" name="gambar_sampul" accept="image/*"
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('gambar_sampul') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">JPG, PNG, GIF. Max 2MB</p>
                    @error('gambar_sampul')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Deskripsi -->
                <div class="lg:col-span-3">
                    <label for="deskripsi" class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <textarea id="deskripsi" name="deskripsi" rows="4"
                              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('deskripsi') border-red-500 @enderror"
                              placeholder="Deskripsi singkat tentang buku">{{ old('deskripsi') }}</textarea>
                    @error('deskripsi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center justify-end gap-4 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('buku.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200 shadow-md hover:shadow-lg">
                    <i class="fas fa-times mr-2"></i>
                    Batal
                </a>
                <button type="submit" 
                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-medium rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                    <i class="fas fa-save mr-2"></i>
                    Simpan Buku
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Barcode Scanner Modal -->
<div id="scannerModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white">Scan Barcode Buku</h3>
                    <button type="button" id="closeScannerBtn" class="text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-6">
                <div class="mb-4">
                    <p class="text-gray-600 mb-4">Arahkan kamera ke barcode buku untuk scan</p>
                    <div id="scannerContainer" class="w-full h-80 bg-gray-100 rounded-lg flex items-center justify-center relative overflow-hidden">
                        <div id="scannerPlaceholder" class="text-center">
                            <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500">Kamera akan aktif saat modal dibuka</p>
                        </div>
                        <div id="scannerVideo" class="w-full h-full hidden">
                            <video id="scannerVideoElement" class="w-full h-full object-cover"></video>
                            <div id="scannerOverlay" class="absolute inset-0 flex items-center justify-center">
                                <div class="border-2 border-white border-dashed w-64 h-32 rounded-lg flex items-center justify-center">
                                    <div class="text-white text-center">
                                        <i class="fas fa-barcode text-2xl mb-2"></i>
                                        <p class="text-sm">Arahkan barcode ke dalam kotak</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div id="scannerLoading" class="absolute inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
                            <div class="text-center text-white">
                                <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                                <p>Memulai kamera...</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-600">
                        <span id="scannerStatus">Siap untuk scan</span>
                    </div>
                    <div class="flex space-x-3" id="scannerControls">
                        <button type="button" id="startScanBtn" 
                                class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold">
                            <i class="fas fa-play mr-2"></i>Mulai Scan
                        </button>
                        <button type="button" id="stopScanBtn" 
                                class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold hidden">
                            <i class="fas fa-stop mr-2"></i>Stop Scan
                        </button>
                        <button type="button" id="cancelScan" 
                                class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill current year for tahun_terbit if empty
    const tahunTerbitInput = document.getElementById('tahun_terbit');
    if (!tahunTerbitInput.value) {
        tahunTerbitInput.value = new Date().getFullYear();
    }

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
            alert('Mohon lengkapi semua field yang wajib diisi');
        }
    });

    // Real-time validation
    const inputs = form.querySelectorAll('input, select, textarea');
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

    // Barcode functionality
    const barcodeInput = document.getElementById('barcode');
    const scanBarcodeBtn = document.getElementById('scanBarcodeBtn');
    const generateBarcodeBtn = document.getElementById('generateBarcodeBtn');
    const scannerModal = document.getElementById('scannerModal');
    const closeScannerBtn = document.getElementById('closeScannerBtn');
    const startScanBtn = document.getElementById('startScanBtn');
    const stopScanBtn = document.getElementById('stopScanBtn');

    // Generate barcode button
    generateBarcodeBtn.addEventListener('click', function() {
        const prefix = 'BK';
        const timestamp = Date.now().toString().slice(-6);
        const randomNum = Math.floor(Math.random() * 1000).toString().padStart(3, '0');
        const generatedBarcode = prefix + timestamp + randomNum;
        
        barcodeInput.value = generatedBarcode;
        showNotification('Barcode berhasil di-generate: ' + generatedBarcode, 'success');
    });

    // Scanner functionality
    let quaggaInitialized = false;

    // Scan barcode button
    scanBarcodeBtn.addEventListener('click', function() {
        scannerModal.classList.remove('hidden');
        initializeScanner();
    });

    // Close scanner modal
    closeScannerBtn.addEventListener('click', function() {
        closeScanner();
    });

    // Start scanning
    startScanBtn.addEventListener('click', function() {
        startScanning();
    });

    // Stop scanning
    stopScanBtn.addEventListener('click', function() {
        stopScanning();
    });

    // Cancel scan
    document.getElementById('cancelScan').addEventListener('click', function() {
        closeScanner();
    });

    // Close modal when clicking outside
    scannerModal.addEventListener('click', function(e) {
        if (e.target === scannerModal) {
            closeScanner();
        }
    });

    // Load ZXing library for better barcode detection
    function loadZXingLibrary() {
        return new Promise((resolve, reject) => {
            if (window.ZXing) {
                resolve(window.ZXing);
                return;
            }
            
            const script = document.createElement('script');
            script.src = 'https://unpkg.com/@zxing/library@latest/umd/index.min.js';
            script.onload = () => {
                console.log('ZXing library loaded successfully');
                resolve(window.ZXing);
            };
            script.onerror = () => {
                console.error('Failed to load ZXing library');
                reject(new Error('Failed to load ZXing library'));
            };
            document.head.appendChild(script);
        });
    }

    // Modern barcode scanner using ZXing
    async function setupModernScanner() {
        const videoElement = document.getElementById('scannerVideoElement');
        const scannerLoading = document.getElementById('scannerLoading');
        const scannerVideo = document.getElementById('scannerVideo');
        const scannerPlaceholder = document.getElementById('scannerPlaceholder');
        
        try {
            const ZXing = await loadZXingLibrary();
            
            scannerLoading.classList.remove('hidden');
            scannerPlaceholder.classList.add('hidden');
            scannerVideo.classList.remove('hidden');
            
            const stream = await navigator.mediaDevices.getUserMedia({
                video: {
                    width: { ideal: 1280 },
                    height: { ideal: 720 },
                    facingMode: "environment"
                }
            });
            
            videoElement.srcObject = stream;
            await videoElement.play();
            
            scannerLoading.classList.add('hidden');
            scannerVideo.classList.remove('hidden');
            
            const codeReader = new ZXing.BrowserMultiFormatReader();
            
            await codeReader.decodeFromVideoDevice(null, videoElement, (result, error) => {
                if (result) {
                    const barcodeText = result.text.trim();
                    if (barcodeText && barcodeText.length > 0) {
                        stopModernScanner();
                        processScannedBarcode(barcodeText);
                    }
                }
            });
            
            window.currentCodeReader = codeReader;
            showNotification('Scanner siap. Arahkan kamera ke barcode.', 'success');
            
        } catch (error) {
            console.error('Modern scanner setup error:', error);
            scannerLoading.classList.add('hidden');
            scannerPlaceholder.classList.remove('hidden');
            scannerVideo.classList.add('hidden');
            
            if (error.name === 'NotAllowedError') {
                showNotification('Akses kamera ditolak. Silakan izinkan akses kamera.', 'error');
            } else if (error.name === 'NotFoundError') {
                showNotification('Tidak ada kamera yang ditemukan.', 'error');
            } else {
                showNotification('Gagal mengakses kamera: ' + error.message, 'error');
            }
        }
    }

    function stopModernScanner() {
        if (window.currentCodeReader) {
            try {
                window.currentCodeReader.reset();
            } catch (error) {
                console.error('Error stopping modern scanner:', error);
            }
        }
    }

    async function initializeScanner() {
        try {
            await setupModernScanner();
        } catch (error) {
            console.log('Scanner initialization failed:', error);
        }
    }

    function startScanning() {
        if (!quaggaInitialized) {
            showNotification('Scanner belum siap. Silakan tunggu.', 'warning');
            return;
        }
    }

    function stopScanning() {
        try {
            if (typeof Quagga !== 'undefined') {
                Quagga.stop();
            }
            document.getElementById('startScanBtn').classList.remove('hidden');
            document.getElementById('stopScanBtn').classList.add('hidden');
            document.getElementById('scannerStatus').textContent = 'Scanner dihentikan';
        } catch (error) {
            console.error('Error stopping scanner:', error);
        }
    }

    function closeScanner() {
        try {
            if (typeof Quagga !== 'undefined') {
                Quagga.stop();
            }
            
            if (window.currentCodeReader) {
                window.currentCodeReader.reset();
            }
            
            const videoElement = document.getElementById('scannerVideoElement');
            if (videoElement.srcObject) {
                const tracks = videoElement.srcObject.getTracks();
                tracks.forEach(track => track.stop());
                videoElement.srcObject = null;
            }
            
        } catch (error) {
            console.error('Error stopping scanner:', error);
        }
        
        scannerModal.classList.add('hidden');
        document.getElementById('startScanBtn').classList.remove('hidden');
        document.getElementById('stopScanBtn').classList.add('hidden');
        document.getElementById('scannerStatus').textContent = 'Siap untuk scan';
        
        const scannerLoading = document.getElementById('scannerLoading');
        const scannerPlaceholder = document.getElementById('scannerPlaceholder');
        const scannerVideo = document.getElementById('scannerVideo');
        
        scannerLoading.classList.add('hidden');
        scannerPlaceholder.classList.remove('hidden');
        scannerVideo.classList.add('hidden');
    }

    function processScannedBarcode(barcode) {
        const cleanBarcode = barcode.trim();
        document.getElementById('scannerStatus').textContent = 'Memproses barcode...';
        barcodeInput.value = cleanBarcode;
        closeScanner();
        showNotification('Barcode berhasil di-scan: ' + cleanBarcode, 'success');
    }

    function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 transform translate-x-full`;
        
        if (type === 'success') {
            notification.className += ' bg-green-500 text-white';
        } else if (type === 'error') {
            notification.className += ' bg-red-500 text-white';
        } else {
            notification.className += ' bg-blue-500 text-white';
        }
        
        notification.innerHTML = `
            <div class="flex items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} mr-2"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.classList.remove('translate-x-full');
        }, 100);
        
        setTimeout(() => {
            notification.classList.add('translate-x-full');
            setTimeout(() => {
                document.body.removeChild(notification);
            }, 300);
        }, 3000);
    }
});
</script>
@endsection