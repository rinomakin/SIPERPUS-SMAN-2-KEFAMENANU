@extends('layouts.admin')

@section('title', 'Tambah Denda')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .toolbar-btn {
        display: inline-flex;
        align-items: center;
        padding: 8px 14px;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.2s;
        gap: 6px;
        white-space: nowrap;
    }
    .toolbar-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .search-result-card {
        transition: all 0.2s ease;
        cursor: pointer;
    }
    .search-result-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .search-result-card.active {
        border-color: #3b82f6;
        background: #eff6ff;
    }

    .scanner-modal-backdrop {
        position: fixed;
        inset: 0;
        z-index: 9999;
        background: rgba(0,0,0,0.85);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 1rem;
        backdrop-filter: blur(8px);
    }
    .scanner-modal-backdrop.hidden { display: none; }
    .scanner-pinjam-box {
        background: #1e293b;
        border-radius: 20px;
        max-width: 480px;
        width: 100%;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(0,0,0,0.5);
    }
    .scanner-pinjam-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 14px 18px;
        background: rgba(255,255,255,0.06);
        border-bottom: 1px solid rgba(255,255,255,0.08);
    }
    .scanner-pinjam-video {
        position: relative;
        width: 100%;
        aspect-ratio: 4/3;
        background: #0f172a;
        overflow: hidden;
    }
    .scanner-pinjam-footer {
        padding: 12px 18px;
        border-top: 1px solid rgba(255,255,255,0.08);
    }

    .animate-scan-line {
        animation: scan-line 2s ease-in-out infinite;
        position: absolute;
    }
    @keyframes scan-line {
        0% { top: 5%; opacity: 0; }
        10% { opacity: 1; }
        90% { opacity: 1; }
        100% { top: 95%; opacity: 0; }
    }

    @media (max-width: 768px) {
        .toolbar-btn span.btn-text { display: none; }
    }
</style>
@endpush

@section('content')
<div class="max-w-6xl mx-auto space-y-5">
    {{-- Header --}}
    <div class="glass-card rounded-2xl shadow-sm border border-gray-100 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-500 to-rose-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-plus text-sm"></i>
                </div>
                <div>
                    <h1 class="text-sm font-bold text-gray-900">Tambah Denda</h1>
                    <p class="text-[11px] text-gray-400">Pilih peminjaman terlambat untuk dikenakan denda</p>
                </div>
            </div>
            <a href="{{ route('admin.denda.index') }}"
               class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200">
                <i class="fas fa-arrow-left text-[10px]"></i>
                <span class="btn-text">Kembali</span>
            </a>
        </div>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
        <div class="flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <div>
                <h4 class="font-medium text-xs">Terjadi kesalahan:</h4>
                <ul class="list-disc list-inside mt-1 text-xs">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    <form action="{{ route('admin.denda.store') }}" method="POST" data-spa-ignore onsubmit="return confirmSubmit(event)">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
            {{-- Left Panel: Search & Scan --}}
            <div class="lg:col-span-3 glass-card rounded-2xl shadow-sm border border-gray-100 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-search text-red-500 text-[10px]"></i>
                        Cari Peminjaman Terlambat
                    </h2>
                    <button type="button" id="scanBarcodeBtn"
                            class="toolbar-btn bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-100">
                        <i class="fas fa-barcode"></i>
                        <span class="btn-text">Scan Anggota</span>
                    </button>
                </div>

                {{-- Search Input --}}
                <div class="relative mb-4">
                    <input type="text" id="searchInput" autocomplete="off"
                           placeholder="Ketik nama / nomor anggota untuk mencari peminjaman terlambat..."
                           class="w-full pl-10 pr-4 py-3 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-400 focus:bg-white transition-all duration-200">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-xs"></i>
                    </div>
                </div>

                {{-- Search Results --}}
                <div id="searchResults" class="space-y-2 max-h-96 overflow-y-auto">
                    <div class="text-center py-8 text-gray-400">
                        <i class="fas fa-search text-2xl mb-2 block"></i>
                        <p class="text-xs">Ketik untuk mencari anggota dengan peminjaman terlambat</p>
                    </div>
                </div>

                {{-- Selected Peminjaman Info --}}
                <div id="selectedPeminjamanCard" class="hidden mt-4 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center">
                            <i class="fas fa-check text-emerald-600 text-xs"></i>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-emerald-800">Peminjaman Terpilih</p>
                            <p id="selectedInfoText" class="text-[10px] text-emerald-600">Belum ada data</p>
                        </div>
                    </div>
                    <input type="hidden" name="peminjaman_id" id="peminjaman_id" value="">
                </div>
            </div>

            {{-- Right Panel: Denda Details --}}
            <div class="lg:col-span-2 glass-card rounded-2xl shadow-sm border border-gray-100 p-5">
                <h2 class="text-sm font-bold text-gray-900 flex items-center gap-2 mb-4">
                    <i class="fas fa-money-bill-wave text-red-500 text-[10px]"></i>
                    Detail Denda
                </h2>

                <div class="space-y-4">
                    {{-- Hari Terlambat (disabled) --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">
                            <i class="fas fa-calendar-times mr-1"></i>
                            Jumlah Hari Terlambat
                        </label>
                        <input type="number" id="jumlah_hari_terlambat" name="jumlah_hari_terlambat"
                               value="{{ old('jumlah_hari_terlambat') }}"
                               readonly
                               placeholder="Otomatis terisi saat pilih peminjaman"
                               class="w-full px-4 py-3 text-xs bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-red-500/20 focus:border-red-400 transition-all">
                    </div>

                    {{-- Denda --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">
                            <i class="fas fa-money-bill-wave mr-1"></i>
                            Jumlah Denda (Rp)
                        </label>
                        <input type="text" id="jumlah_denda" name="jumlah_denda"
                               value="{{ old('jumlah_denda') ? number_format((int) (preg_match('/\.\d{1,2}$/', old('jumlah_denda')) ? round((float) old('jumlah_denda')) : str_replace('.', '', old('jumlah_denda'))), 0, ',', '.') : '' }}"
                               inputmode="numeric"
                               placeholder="Otomatis terisi, bisa diubah manual"
                               class="w-full px-4 py-3 text-xs bg-gray-50 border border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-red-500/20 focus:border-red-400 transition-all rupiah-input">
                    </div>

                    {{-- Jumlah Bayar --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">
                            <i class="fas fa-hand-holding-usd mr-1"></i>
                            Jumlah Dibayar (Rp)
                        </label>
                        <input type="text" id="jumlah_bayar" name="jumlah_bayar"
                               value="{{ old('jumlah_bayar') ? number_format((int) (preg_match('/\.\d{1,2}$/', old('jumlah_bayar')) ? round((float) old('jumlah_bayar')) : str_replace('.', '', old('jumlah_bayar'))), 0, ',', '.') : '0' }}"
                               inputmode="numeric"
                               placeholder="0 — bayar sebagian atau lunas"
                               class="w-full px-4 py-3 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-400 transition-all rupiah-input">
                        <p id="bayarWarning" class="text-[10px] text-red-500 mt-1 hidden">
                            <i class="fas fa-exclamation-circle mr-0.5"></i>Jumlah dibayar tidak boleh melebihi jumlah denda
                        </p>
                    </div>

                    {{-- Catatan --}}
                    <div>
                        <label class="block text-[10px] font-semibold text-gray-600 mb-1.5 uppercase tracking-wider">
                            <i class="fas fa-sticky-note mr-1"></i>
                            Catatan
                        </label>
                        <textarea id="catatan" name="catatan" rows="3"
                                  placeholder="Catatan tambahan (opsional)..."
                                  class="w-full px-4 py-3 text-xs bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-red-500/20 focus:border-red-400 transition-all">{{ old('catatan') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end gap-3 mt-5">
            <a href="{{ route('admin.denda.index') }}"
               class="toolbar-btn bg-gray-100 text-gray-600 hover:bg-gray-200">
                <i class="fas fa-times"></i>
                <span>Batal</span>
            </a>
            <button type="submit"
                    class="toolbar-btn bg-gradient-to-r from-red-500 to-rose-600 text-white shadow-md hover:shadow-lg">
                <i class="fas fa-save"></i>
                <span>Simpan Denda</span>
            </button>
        </div>
    </form>
</div>

{{-- Scanner Modal --}}
<div id="scannerModal" class="scanner-modal-backdrop hidden">
    <div class="scanner-pinjam-box">
        <div class="scanner-pinjam-header">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-camera text-white text-sm"></i>
                </div>
                <div>
                    <h3 class="text-sm font-semibold text-white">Scan Barcode Anggota</h3>
                    <p class="text-[10px] text-white/50">Arahkan kamera ke barcode anggota</p>
                </div>
            </div>
            <button type="button" id="closeScanner"
                    class="w-8 h-8 bg-white/10 hover:bg-red-500/80 rounded-full flex items-center justify-center text-white transition-colors flex-shrink-0">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        <div id="scannerContainer" class="scanner-pinjam-video">
            <div id="scannerPlaceholder" class="absolute inset-0 flex items-center justify-center z-10 px-4">
                <div class="text-center">
                    <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-camera text-2xl text-white/60"></i>
                    </div>
                    <p class="text-sm text-white/70 mb-1">Kamera akan aktif otomatis</p>
                    <p class="text-xs text-white/40">Pastikan izin kamera diaktifkan</p>
                </div>
            </div>
            <div id="scannerVideo" class="w-full h-full hidden">
                <div id="reader" class="w-full h-full"></div>
            </div>
            <div id="scannerLoading" class="absolute inset-0 bg-black/60 flex items-center justify-center hidden z-10">
                <div class="text-center">
                    <div class="w-10 h-10 border-4 border-white/20 border-t-white rounded-full animate-spin mx-auto mb-2"></div>
                    <p class="text-xs text-white/70">Menyiapkan kamera...</p>
                </div>
            </div>
            <div id="scanOverlay" class="absolute inset-0 z-10 pointer-events-none hidden">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative w-5/6 h-3/5">
                        <div class="absolute inset-0 border-2 border-emerald-400/60 rounded-xl"></div>
                        <div class="absolute left-2 right-2 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent animate-scan-line"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="scanner-pinjam-footer">
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gray-500" id="scannerStatusDot"></span>
                    <span class="text-[11px] text-white/70" id="scannerStatus">Siap untuk scan</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="button" id="toggleTorchBtn"
                            class="w-8 h-8 bg-white/10 hover:bg-amber-500/60 rounded-lg flex items-center justify-center text-white/80 transition-colors hidden" title="Lampu kilat">
                        <i class="fas fa-bolt text-xs"></i>
                    </button>
                    <button type="button" id="switchCameraBtn"
                            class="w-8 h-8 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-center text-white/80 transition-colors hidden" title="Ganti kamera">
                        <i class="fas fa-camera-retro text-xs"></i>
                    </button>
                </div>
            </div>
            <div class="flex gap-2">
                <button type="button" id="startScanBtn"
                        class="flex-1 py-2 bg-emerald-500/80 hover:bg-emerald-500 text-white text-xs font-semibold rounded-xl transition-colors hidden">
                    <i class="fas fa-play mr-1"></i> Mulai Scan
                </button>
                <button type="button" id="stopScanBtn"
                        class="flex-1 py-2 bg-red-500/80 hover:bg-red-500 text-white text-xs font-semibold rounded-xl transition-colors">
                    <i class="fas fa-stop mr-1"></i> Berhenti
                </button>
                <button type="button" id="manualInputBtn"
                        class="py-2 px-4 bg-white/10 hover:bg-white/20 text-white/80 text-xs font-semibold rounded-xl transition-colors">
                    <i class="fas fa-keyboard"></i>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// ─────────────────────────────────────────────────
// Scanner
// ─────────────────────────────────────────────────
var html5QrcodeScanner = null;
var nativeBarcodeDetector = null;
var nativeScanStream = null;
var nativeScanInterval = null;
var torchEnabled = false;
var lastScannedCode = '';
var lastScanTime = 0;
var scanCooldown = 1500;
var isProcessingBarcode = false;
var cameraDevices = [];
var currentCameraIndex = 0;
var hasNativeBarcodeAPI = ('BarcodeDetector' in window);

// ── Re-enumerate after permission granted ──
async function refreshCameraLabels() {
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
        const all  = await navigator.mediaDevices.enumerateDevices();
        let vids   = all.filter(d => d.kind === 'videoinput');
        if (!vids.length || vids[0].label === '') return;

        vids.sort((a, b) => {
            const rA = /back|rear|environment|belakang/i.test(a.label) ? 1 : 0;
            const rB = /back|rear|environment|belakang/i.test(b.label) ? 1 : 0;
            return rB - rA;
        });

        if (nativeScanStream) {
            const activeId = nativeScanStream.getVideoTracks()[0]?.getSettings()?.deviceId;
            const idx = vids.findIndex(d => d.deviceId === activeId);
            if (idx >= 0) currentCameraIndex = idx;
        }

        cameraDevices = vids;
        document.getElementById('switchCameraBtn').classList.toggle('hidden', vids.length < 2);
    } catch (e) {}
}

async function openScannerModal() {
    document.getElementById('scannerModal').classList.remove('hidden');
    lastScannedCode = ''; lastScanTime = 0; isProcessingBarcode = false;
    document.getElementById('scannerLoading').classList.add('hidden');
    document.getElementById('scannerVideo').classList.add('hidden');
    document.getElementById('scanOverlay').classList.add('hidden');
    document.getElementById('startScanBtn').classList.add('hidden');
    document.getElementById('stopScanBtn').classList.add('hidden');
    document.getElementById('toggleTorchBtn').classList.add('hidden');
    document.getElementById('switchCameraBtn').classList.add('hidden');
    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center"><div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full animate-spin mx-auto mb-3"></div><p class="text-sm text-white/70">Mendeteksi kamera tersedia...</p></div>`;
    updateScannerStatus('idle', 'Mendeteksi kamera...');
    await enumerateCameras();
    await startScanner();
}

async function enumerateCameras() {
    cameraDevices = []; currentCameraIndex = 0;
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
        const all = await navigator.mediaDevices.enumerateDevices();
        let vids = all.filter(d => d.kind === 'videoinput');
        if (!vids.length) return;
        vids.sort((a, b) => { const rA = /back|rear|environment|belakang/i.test(a.label) ? 1 : 0; const rB = /back|rear|environment|belakang/i.test(b.label) ? 1 : 0; return rB - rA; });
        cameraDevices = vids;
    } catch (e) {}
}

async function startScanner() {
    if (cameraDevices.length > 0 && cameraDevices[currentCameraIndex]?.deviceId) {
        await startScannerWithDeviceId(cameraDevices[currentCameraIndex].deviceId, cameraDevices[currentCameraIndex].label);
    } else {
        await startWithFacingModeFallback();
    }
}

async function startScannerWithDeviceId(deviceId, label) {
    updateScannerStatus('idle', label ? 'Menghubungkan: ' + label.substring(0, 30) + '...' : 'Menghubungkan kamera...');
    let r = await tryGetUserMedia({ deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia({ deviceId: { exact: deviceId } });
    if (r === 'fatal' || r === true) return;
    await startWithFacingModeFallback();
}

async function startWithFacingModeFallback() {
    let r = await tryGetUserMedia({ facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia({ facingMode: { ideal: 'user' }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia({ facingMode: { ideal: 'environment' } });
    if (r === 'fatal' || r === true) return;
    r = await tryGetUserMedia(true);
    if (r === 'fatal' || r === true) return;
    if (typeof Html5Qrcode !== 'undefined') { initHTML5Scanner(); } else { setupManualInput(); }
}

async function tryGetUserMedia(videoConstraints) {
    try {
        nativeScanStream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints, audio: false });
        await setupVideoFromStream(nativeScanStream);
        await refreshCameraLabels();
        return true;
    } catch (err) {
        if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
        if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') { setupPermissionDenied(); return 'fatal'; }
        return false;
    }
}

async function setupVideoFromStream(stream) {
    let videoEl = document.getElementById('nativeScanVideo');
    if (!videoEl) {
        videoEl = document.createElement('video');
        videoEl.id = 'nativeScanVideo';
        videoEl.setAttribute('playsinline', '');
        videoEl.setAttribute('autoplay', '');
        videoEl.setAttribute('muted', '');
        videoEl.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
        const readerEl = document.getElementById('reader');
        readerEl.innerHTML = '';
        readerEl.appendChild(videoEl);
    } else {
        if (videoEl.srcObject) { videoEl.srcObject.getTracks().forEach(t => t.stop()); }
    }
    videoEl.srcObject = stream;
    await new Promise((resolve) => {
        if (videoEl.readyState >= 2) { resolve(); return; }
        const onReady = () => { videoEl.removeEventListener('loadedmetadata', onReady); resolve(); };
        videoEl.addEventListener('loadedmetadata', onReady);
        videoEl.play().catch(() => {});
        setTimeout(resolve, 8000);
    });
    const track = stream.getVideoTracks()[0];
    if (track) {
        const caps = track.getCapabilities ? track.getCapabilities() : {};
        document.getElementById('toggleTorchBtn').classList.toggle('hidden', !caps.torch);
        try { if (caps.focusMode?.includes('continuous')) await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] }); } catch (e) {}
    }
    document.getElementById('scannerLoading').classList.add('hidden');
    document.getElementById('scannerPlaceholder').classList.add('hidden');
    document.getElementById('scannerVideo').classList.remove('hidden');
    document.getElementById('scanOverlay').classList.remove('hidden');
    document.getElementById('startScanBtn').classList.add('hidden');
    document.getElementById('stopScanBtn').classList.remove('hidden');
    const devLabel = cameraDevices[currentCameraIndex]?.label || (track?.getSettings()?.facingMode === 'user' ? 'Kamera Depan' : 'Kamera Belakang');
    const camInfo = cameraDevices.length > 1 ? ` (${currentCameraIndex + 1}/${cameraDevices.length})` : '';
    updateScannerStatus('active', devLabel.substring(0, 30) + camInfo);
    if (hasNativeBarcodeAPI) {
        nativeBarcodeDetector = new BarcodeDetector({ formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8', 'upc_a', 'upc_e', 'itf', 'codabar', 'qr_code', 'data_matrix', 'aztec', 'pdf417'] });
        startNativeScanLoop(videoEl);
    } else if (typeof Html5Qrcode !== 'undefined') {
        initHTML5Scanner();
    }
}

function startNativeScanLoop(videoEl) {
    if (nativeScanInterval) clearInterval(nativeScanInterval);
    nativeScanInterval = setInterval(async () => {
        if (!nativeBarcodeDetector || !videoEl || videoEl.readyState < 2) return;
        try {
            const barcodes = await nativeBarcodeDetector.detect(videoEl);
            if (barcodes.length > 0) {
                const code = barcodes[0].rawValue;
                const now = Date.now();
                if (code === lastScannedCode && (now - lastScanTime) < scanCooldown) return;
                lastScannedCode = code; lastScanTime = now;
                if (navigator.vibrate) navigator.vibrate([80]);
                flashScanSuccess();
                processScannedBarcode(code);
            }
        } catch (e) {}
    }, 50);
}

function flashScanSuccess() {
    const overlay = document.getElementById('scanOverlay');
    if (!overlay) return;
    const flash = document.createElement('div');
    flash.className = 'absolute inset-0 bg-emerald-500/20 z-20 pointer-events-none';
    flash.style.transition = 'opacity .3s ease';
    overlay.appendChild(flash);
    setTimeout(() => { flash.style.opacity = '0'; }, 100);
    setTimeout(() => { flash.remove(); }, 400);
}

function initHTML5Scanner() {
    const loading = document.getElementById('scannerLoading');
    const video = document.getElementById('scannerVideo');
    const placeholder = document.getElementById('scannerPlaceholder');
    const overlay = document.getElementById('scanOverlay');
    if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
    if (nativeScanInterval) { clearInterval(nativeScanInterval); nativeScanInterval = null; }
    if (html5QrcodeScanner) { try { html5QrcodeScanner.stop().catch(()=>{}); } catch(e){} html5QrcodeScanner = null; }
    loading.classList.remove('hidden');
    placeholder.classList.add('hidden');
    video.classList.remove('hidden');
    document.getElementById('reader').innerHTML = '';
    const config = {
        fps: 15,
        qrbox: (w, h) => ({ width: Math.floor(w * .82), height: Math.floor(h * .62) }),
        aspectRatio: window.innerWidth > window.innerHeight ? 16/9 : 4/3,
        formatsToSupport: [
            Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39,
            Html5QrcodeSupportedFormats.CODE_93, Html5QrcodeSupportedFormats.EAN_13,
            Html5QrcodeSupportedFormats.EAN_8, Html5QrcodeSupportedFormats.UPC_A,
            Html5QrcodeSupportedFormats.UPC_E, Html5QrcodeSupportedFormats.ITF,
            Html5QrcodeSupportedFormats.CODABAR, Html5QrcodeSupportedFormats.QR_CODE,
            Html5QrcodeSupportedFormats.DATA_MATRIX
        ],
        experimentalFeatures: { useBarCodeDetectorIfSupported: true },
        videoConstraints: { width: { ideal: 1280 }, height: { ideal: 720 } }
    };
    const onSuccess = (decodedText) => {
        const now = Date.now();
        if (decodedText === lastScannedCode && (now - lastScanTime) < scanCooldown) return;
        lastScannedCode = decodedText; lastScanTime = now;
        if (navigator.vibrate) navigator.vibrate([80]);
        flashScanSuccess();
        processScannedBarcode(decodedText);
    };
    const hasDev = cameraDevices.length > 0 && cameraDevices[currentCameraIndex]?.deviceId;
    const camConst = hasDev ? { deviceId: { exact: cameraDevices[currentCameraIndex].deviceId } } : { facingMode: { ideal: 'environment' } };
    html5QrcodeScanner = new Html5Qrcode('reader');
    html5QrcodeScanner.start(camConst, config, onSuccess, () => {})
    .then(() => {
        loading.classList.add('hidden'); overlay.classList.remove('hidden');
        updateScannerStatus('active', 'Scanner aktif');
        document.getElementById('startScanBtn').classList.add('hidden');
        document.getElementById('stopScanBtn').classList.remove('hidden');
        refreshCameraLabels();
    })
    .catch(async () => {
        try { await html5QrcodeScanner.stop().catch(()=>{}); } catch(e) {}
        html5QrcodeScanner = new Html5Qrcode('reader');
        html5QrcodeScanner.start({ facingMode: { ideal: 'user' } }, { ...config, videoConstraints: { width: { ideal: 640 }, height: { ideal: 480 } } }, onSuccess, () => {})
        .then(() => {
            loading.classList.add('hidden'); overlay.classList.remove('hidden');
            updateScannerStatus('active', 'Kamera depan aktif');
            document.getElementById('startScanBtn').classList.add('hidden');
            document.getElementById('stopScanBtn').classList.remove('hidden');
        })
        .catch(() => { loading.classList.add('hidden'); placeholder.classList.remove('hidden'); video.classList.add('hidden'); setupManualInput(); });
    });
}

function updateScannerStatus(state, text) {
    const dot = document.getElementById('scannerStatusDot');
    const status = document.getElementById('scannerStatus');
    if (status) status.textContent = text;
    if (dot) dot.className = state === 'active' ? 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse' : (state === 'error' ? 'w-2 h-2 rounded-full bg-red-500' : 'w-2 h-2 rounded-full bg-gray-500');
}

function stopAllScanners() {
    if (nativeScanInterval) { clearInterval(nativeScanInterval); nativeScanInterval = null; }
    if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
    nativeBarcodeDetector = null; torchEnabled = false;
    if (html5QrcodeScanner) { try { html5QrcodeScanner.stop().catch(()=>{}); } catch(e){} html5QrcodeScanner = null; }
    updateScannerStatus('idle', 'Scanner dihentikan');
    document.getElementById('startScanBtn').classList.remove('hidden');
    document.getElementById('stopScanBtn').classList.add('hidden');
    document.getElementById('toggleTorchBtn').classList.add('hidden');
}

function closeScanner() {
    stopAllScanners();
    isProcessingBarcode = false;
    document.getElementById('scannerModal').classList.add('hidden');
    document.getElementById('scanOverlay').classList.add('hidden');
    document.getElementById('switchCameraBtn').classList.add('hidden');
    updateScannerStatus('idle', 'Siap untuk scan');
    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center"><div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-3"><i class="fas fa-camera text-2xl text-white/60"></i></div><p class="text-sm text-white/70 mb-1">Kamera akan aktif otomatis</p><p class="text-xs text-white/40">Pastikan izin kamera diaktifkan</p></div>`;
    document.getElementById('scannerVideo').classList.add('hidden');
    document.getElementById('scannerLoading').classList.add('hidden');
    const nv = document.getElementById('nativeScanVideo');
    if (nv) { nv.srcObject = null; }
}

function setupPermissionDenied() {
    document.getElementById('scannerLoading').classList.add('hidden');
    document.getElementById('scannerVideo').classList.add('hidden');
    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center"><div class="w-14 h-14 bg-red-500/20 rounded-2xl flex items-center justify-center mx-auto mb-3"><i class="fas fa-ban text-2xl text-red-400"></i></div><p class="text-sm font-semibold text-white/80 mb-1">Akses Kamera Ditolak</p><p class="text-xs text-white/40 mb-4 max-w-xs mx-auto">Izinkan akses kamera di browser, lalu muat ulang halaman.</p><div class="flex flex-col gap-2 items-center"><button type="button" onclick="openScannerModal()" class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-xs transition-colors"><i class="fas fa-redo mr-1.5"></i>Coba Lagi</button><button type="button" onclick="showManualInputDialog()" class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white/80 rounded-xl font-semibold text-xs transition-colors"><i class="fas fa-keyboard mr-1.5"></i>Input Manual</button></div></div>`;
    updateScannerStatus('error', 'Izin kamera ditolak');
}

function setupManualInput() {
    document.getElementById('scannerVideo').classList.add('hidden');
    document.getElementById('scannerLoading').classList.add('hidden');
    const ph = document.getElementById('scannerPlaceholder');
    ph.classList.remove('hidden');
    ph.innerHTML = `<div class="text-center"><div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-3"><i class="fas fa-keyboard text-2xl text-white/60"></i></div><p class="text-sm text-white/70 mb-1">Kamera tidak tersedia</p><p class="text-xs text-white/40 mb-4">Gunakan input barcode manual</p><button type="button" onclick="showManualInputDialog()" class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold text-xs transition-colors"><i class="fas fa-keyboard mr-1.5"></i>Input Manual</button></div>`;
    updateScannerStatus('error', 'Kamera tidak tersedia');
    document.getElementById('startScanBtn').classList.remove('hidden');
    document.getElementById('stopScanBtn').classList.add('hidden');
}

function showManualInputDialog() {
    Swal.fire({
        title: 'Input Barcode Manual',
        input: 'text',
        inputPlaceholder: 'Masukkan kode barcode / nomor anggota...',
        inputAttributes: { autocomplete: 'off', autocorrect: 'off', spellcheck: 'false' },
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check mr-1"></i>Proses',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#ef4444',
        didOpen: () => { document.querySelector('.swal2-input')?.focus(); },
        inputValidator: v => { if (!v?.trim()) return 'Masukkan kode barcode!'; }
    }).then(r => {
        if (r.isConfirmed && r.value) {
            closeScanner();
            searchByBarcode(r.value.trim());
        }
    });
}

function processScannedBarcode(barcode) {
    if (isProcessingBarcode) return;
    isProcessingBarcode = true;
    updateScannerStatus('idle', 'Barcode terdeteksi...');
    closeScanner();
    searchByBarcode(barcode);
}

function searchByBarcode(barcode) {
    document.getElementById('searchInput').value = barcode;
    performSearch(barcode);
}

// ─────────────────────────────────────────────────
// Search
// ─────────────────────────────────────────────────
var searchTimeout;

document.getElementById('searchInput').addEventListener('input', function() {
    clearTimeout(searchTimeout);
    const val = this.value.trim();
    if (val.length < 1) {
        document.getElementById('searchResults').innerHTML = `<div class="text-center py-8 text-gray-400"><i class="fas fa-search text-2xl mb-2 block"></i><p class="text-xs">Ketik untuk mencari anggota dengan peminjaman terlambat</p></div>`;
        return;
    }
    searchTimeout = setTimeout(() => performSearch(val), 300);
});

function performSearch(query) {
    const resultsContainer = document.getElementById('searchResults');
    resultsContainer.innerHTML = `<div class="text-center py-4"><div class="w-6 h-6 border-2 border-red-200 border-t-red-500 rounded-full animate-spin mx-auto"></div><p class="text-xs text-gray-400 mt-2">Mencari...</p></div>`;

    fetch('/admin/denda/search-peminjaman?query=' + encodeURIComponent(query), {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.data || data.data.length === 0) {
            resultsContainer.innerHTML = `<div class="text-center py-8 text-gray-400"><i class="fas fa-exclamation-circle text-2xl mb-2 block"></i><p class="text-xs">Tidak ditemukan peminjaman terlambat</p></div>`;
            return;
        }
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

        resultsContainer.innerHTML = '';
        data.data.forEach(item => {
            const anggota = item.anggota;

            // ─── UNPAID DENDA ───
            if (item.type === 'unpaid_denda') {
                const section = document.createElement('div');
                section.className = 'p-3 border border-red-200 rounded-xl bg-red-50';
                section.innerHTML = `
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0 bg-gray-200">
                            <img src="${anggota.foto}" alt="" class="w-full h-full object-cover"
                                 onerror="this.style.display='none';this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center text-gray-400 text-xs\\'><i class=\\'fas fa-user\\'></i></div>'">
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-gray-900">${anggota.nama_lengkap}</span>
                                <span class="text-[10px] text-gray-400">#${anggota.nomor_anggota}</span>
                                <span class="text-[9px] font-medium px-1.5 py-0.5 rounded bg-red-100 text-red-700">Belum Dibayar</span>
                            </div>
                            <p class="text-[10px] text-gray-500">${anggota.kelas}</p>
                        </div>
                    </div>
                    <div class="space-y-1.5 search-result-denda-list">
                        ${item.denda.map(d => `
                            <div class="search-result-card search-result-denda flex items-center justify-between bg-white rounded-lg px-3 py-2 border border-red-100 cursor-pointer hover:border-red-300" data-peminjaman-id="${d.peminjaman_id}" data-hari-terlambat="${d.jumlah_hari_terlambat}" data-denda="${d.jumlah_denda}" data-anggota-nama="${anggota.nama_lengkap}">
                                <div>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-600">
                                        <span class="font-semibold text-red-600">Rp ${Number(d.jumlah_denda).toLocaleString('id-ID')}</span>
                                        <span>| Terlambat ${d.jumlah_hari_terlambat} hari</span>
                                    </div>
                                    ${d.buku && d.buku.length > 0 ? `
                                    <div class="flex flex-wrap gap-1 mt-0.5">
                                        ${d.buku.map(b => `<span class="text-[9px] bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">${b.judul}</span>`).join('')}
                                    </div>` : ''}
                                </div>
                                <i class="fas fa-chevron-right text-gray-300 text-[10px]"></i>
                            </div>
                        `).join('')}
                    </div>
                `;
                resultsContainer.appendChild(section);

                section.querySelectorAll('.search-result-denda').forEach(el => {
                    el.addEventListener('click', () => {
                        document.querySelectorAll('.search-result-card').forEach(c => c.classList.remove('active', 'border-red-400', 'bg-red-50'));
                        el.classList.add('active', 'border-red-400', 'bg-red-50');

                        const peminjamanId = el.dataset.peminjamanId;
                        const hariTerlambat = el.dataset.hariTerlambat;
                        const dendaVal = el.dataset.denda;
                        const anggotaNama = el.dataset.anggotaNama;

                        document.getElementById('peminjaman_id').value = peminjamanId;
                        document.getElementById('jumlah_hari_terlambat').value = hariTerlambat;
                        document.getElementById('jumlah_denda').value = formatRupiah(dendaVal);
                        if (window.syncBayarMax) window.syncBayarMax();

                        const infoCard = document.getElementById('selectedPeminjamanCard');
                        infoCard.classList.remove('hidden');
                        document.getElementById('selectedInfoText').textContent = `${anggotaNama} - Terlambat ${hariTerlambat} hari - Rp ${Number(dendaVal).toLocaleString('id-ID')}`;
                    });
                });
                return;
            }

            // ─── RETURNED NO DENDA ───
            item.peminjaman.forEach(pinjam => {
                const card = document.createElement('div');
                card.className = 'search-result-card p-3 border border-gray-200 rounded-xl hover:border-red-300';
                card.dataset.peminjamanId = pinjam.id;
                card.dataset.hariTerlambat = pinjam.hari_terlambat;
                card.dataset.denda = pinjam.denda;
                card.dataset.anggotaNama = anggota.nama_lengkap;
                card.dataset.tanggalHarusKembali = pinjam.tanggal_harus_kembali;

                if (currentPeminjamanData && pinjam.id === currentPeminjamanData.id) {
                    card.classList.add('active', 'border-red-400', 'bg-red-50');
                }

                card.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg overflow-hidden flex-shrink-0 bg-gray-200">
                            <img src="${anggota.foto}" alt="" class="w-full h-full object-cover"
                                 onerror="this.style.display='none';this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center text-gray-400 text-xs\\'><i class=\\'fas fa-user\\'></i></div>'">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-semibold text-gray-900 truncate">${anggota.nama_lengkap}</span>
                                <span class="text-[10px] text-gray-400">#${anggota.nomor_anggota}</span>
                                <span class="text-[9px] font-medium px-1.5 py-0.5 rounded bg-purple-100 text-purple-700">Sudah Dikembalikan</span>
                            </div>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-[10px] text-gray-500">${anggota.kelas}</span>
                                <span class="text-[10px] text-gray-300">|</span>
                                <span class="text-[10px] text-gray-500">Pinjam: ${pinjam.tanggal_pinjam}</span>
                                <span class="text-[10px] text-gray-300">|</span>
                                <span class="text-[10px] text-emerald-600">Dikembalikan: ${pinjam.tanggal_kembali}</span>
                            </div>
                            <div class="flex items-center gap-2 mt-1">
                                <span class="text-[10px] font-semibold text-red-600">Terlambat ${pinjam.hari_terlambat} hari</span>
                                <span class="text-[10px] font-semibold text-rose-600">Rp ${Number(pinjam.denda).toLocaleString('id-ID')}</span>
                            </div>
                            ${pinjam.buku && pinjam.buku.length > 0 ? `
                            <div class="flex flex-wrap gap-1 mt-1">
                                ${pinjam.buku.map(b => `<span class="text-[9px] bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded">${b.judul}</span>`).join('')}
                            </div>` : ''}
                        </div>
                    </div>
                `;

                card.addEventListener('click', () => selectPeminjaman(card, pinjam));
                resultsContainer.appendChild(card);
            });
        });
    })
    .catch(() => {
        resultsContainer.innerHTML = `<div class="text-center py-8 text-red-400"><i class="fas fa-exclamation-triangle text-2xl mb-2 block"></i><p class="text-xs">Gagal mencari data</p></div>`;
    });
}

function selectPeminjaman(card, pinjam) {
    document.querySelectorAll('.search-result-card').forEach(c => c.classList.remove('active', 'border-red-400', 'bg-red-50'));
    card.classList.add('active', 'border-red-400', 'bg-red-50');

    document.getElementById('peminjaman_id').value = pinjam.id;
    document.getElementById('jumlah_hari_terlambat').value = pinjam.hari_terlambat;
    document.getElementById('jumlah_denda').value = formatRupiah(pinjam.denda);
    if (window.syncBayarMax) window.syncBayarMax();

    const infoCard = document.getElementById('selectedPeminjamanCard');
    infoCard.classList.remove('hidden');
    document.getElementById('selectedInfoText').textContent = `${pinjam.nama_lengkap || card.dataset.anggotaNama} - Terlambat ${pinjam.hari_terlambat} hari - Rp ${Number(pinjam.denda).toLocaleString('id-ID')}`;
}

// ─────────────────────────────────────────────────
// Rupiah Formatting (global)
// ─────────────────────────────────────────────────
function stripCommas(val) {
    return (val || '').replace(/\./g, '');
}

function formatRupiah(val) {
    let str = String(val).trim();
    // Detect decimal format "100000.00" (trailing . + 1-2 digits)
    const lastDotIdx = str.lastIndexOf('.');
    if (lastDotIdx > 0) {
        const afterLastDot = str.slice(lastDotIdx + 1);
        if (/^\d{1,2}$/.test(afterLastDot)) {
            const num = Math.round(parseFloat(str));
            if (isNaN(num)) return '';
            return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
        }
    }
    // Handle formatted "100.000" or plain "100000" → strip dots, parseInt
    const num = parseInt(str.replace(/\./g, ''));
    if (isNaN(num)) return '';
    return num.toLocaleString('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
}

// ─────────────────────────────────────────────────
// Scanner Button Listeners
// ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('scanBarcodeBtn').addEventListener('click', openScannerModal);
    document.getElementById('closeScanner').addEventListener('click', closeScanner);
    document.getElementById('startScanBtn').addEventListener('click', () => startScanner());
    document.getElementById('stopScanBtn').addEventListener('click', stopAllScanners);
    document.getElementById('manualInputBtn').addEventListener('click', showManualInputDialog);
    document.getElementById('switchCameraBtn').addEventListener('click', async () => {
        if (cameraDevices.length < 2) return;
        currentCameraIndex = (currentCameraIndex + 1) % cameraDevices.length;
        stopAllScanners();
        await startScannerWithDeviceId(cameraDevices[currentCameraIndex].deviceId, cameraDevices[currentCameraIndex].label);
    });
    document.getElementById('toggleTorchBtn').addEventListener('click', async function() {
        if (!nativeScanStream) return;
        const track = nativeScanStream.getVideoTracks()[0];
        if (!track) return;
        try {
            torchEnabled = !torchEnabled;
            await track.applyConstraints({ advanced: [{ torch: torchEnabled }] });
            this.classList.toggle('bg-amber-500/60', torchEnabled);
            this.classList.toggle('bg-white/15', !torchEnabled);
        } catch (e) { torchEnabled = !torchEnabled; }
    });

    // Close scanner on backdrop click
    document.getElementById('scannerModal').addEventListener('click', function(e) {
        if (e.target === this) closeScanner();
    });

    document.querySelectorAll('.rupiah-input').forEach(function(input) {
        input.addEventListener('input', function() {
            const cursor = this.selectionStart;
            const raw = stripCommas(this.value);
            const digitsOnly = raw.replace(/\D/g, '');
            const formatted = formatRupiah(digitsOnly);
            if (formatted !== this.value) {
                this.value = formatted;
            }
        });

        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' || e.key === 'Delete') {
                const raw = stripCommas(this.value);
                const digitsOnly = raw.replace(/\D/g, '');
                if (digitsOnly.length <= 1) {
                    this.value = '';
                }
            }
        });
    });

    // ─────────────────────────────────────────────
    // Jumlah Bayar validation
    // ─────────────────────────────────────────────
    window.syncBayarMax = function() {
        const dendaInput = document.getElementById('jumlah_denda');
        const bayarInput = document.getElementById('jumlah_bayar');
        const bayarWarning = document.getElementById('bayarWarning');
        const dendaVal = parseInt(stripCommas(dendaInput.value)) || 0;
        const bayarVal = parseInt(stripCommas(bayarInput.value)) || 0;
        if (bayarVal > dendaVal) {
            bayarInput.value = formatRupiah(String(dendaVal));
        }
        if (bayarWarning) {
            bayarWarning.classList.toggle('hidden', bayarVal <= dendaVal);
        }
    };

    document.getElementById('jumlah_denda').addEventListener('input', window.syncBayarMax);
    document.getElementById('jumlah_bayar').addEventListener('input', window.syncBayarMax);
    window.syncBayarMax();
});
</script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function confirmSubmit(event) {
    event.preventDefault();

    const form = event.target;
    const jumlahDenda = stripCommas(document.getElementById('jumlah_denda')?.value || '0');
    const jumlahBayar = stripCommas(document.getElementById('jumlah_bayar')?.value || '0');

    Swal.fire({
        title: 'Konfirmasi Pembayaran Denda',
        html: `
            <div style="text-align: left; font-size: 13px;">
                <p style="margin-bottom: 8px;">Apakah anda yakin ingin menyimpan data denda ini?</p>
                <div style="background: #f8fafc; padding: 10px; border-radius: 8px; margin-top: 10px;">
                    <p style="margin: 4px 0;"><strong>Jumlah Denda:</strong> Rp ${parseInt(jumlahDenda).toLocaleString('id-ID')}</p>
                    <p style="margin: 4px 0;"><strong>Dibayar:</strong> Rp ${parseInt(jumlahBayar).toLocaleString('id-ID')}</p>
                </div>
            </div>
        `,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-check mr-1"></i>Ya, Simpan',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then(result => {
        if (result.isConfirmed) {
            const btn = form.querySelector('button[type="submit"]');
            if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Menyimpan...'; }

            document.querySelectorAll('.rupiah-input').forEach(function(el) {
                el.value = stripCommas(el.value);
            });
            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => {
                if (res.redirected) {
                    window.location.href = res.url;
                    return null;
                }
                return res.json();
            })
            .then(data => {
                if (data === null) return;
                if (data.success) {
                    Swal.fire({
                        title: 'Berhasil!',
                        text: data.message || 'Data denda berhasil disimpan.',
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false,
                    }).then(() => {
                        window.location.href = '{{ route("admin.denda.index") }}';
                    });
                } else {
                    Swal.fire('Error!', data.message || 'Terjadi kesalahan.', 'error');
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i><span> Simpan Denda</span>'; }
                }
            })
            .catch(err => {
                Swal.fire('Error!', 'Terjadi kesalahan jaringan. Coba lagi.', 'error');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-save"></i><span> Simpan Denda</span>'; }
            });
        }
    });

    return false;
}
</script>
@endpush
