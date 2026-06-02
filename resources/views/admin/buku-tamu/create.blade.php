@extends('layouts.admin')

@section('title', 'Tambah Tamu')
@section('page-title', 'Catat Kunjungan Buku Tamu')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    @keyframes fadeIn      { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    @keyframes slideDown   { from { opacity:0; max-height:0; } to { opacity:1; max-height:1000px; } }
    @keyframes fadeInUp    { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
    @keyframes spin        { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
    @keyframes scan-line   { 0%{top:5%;opacity:0;} 10%{opacity:1;} 90%{opacity:1;} 100%{top:95%;opacity:0;} }
    @keyframes pulse-ring  { 0%{transform:scale(.8);opacity:1;} 100%{transform:scale(1.4);opacity:0;} }

    .animate-fade     { animation: fadeIn .35s ease-out forwards; }
    .animate-fade-up  { animation: fadeInUp .4s ease-out forwards; }
    .spinner          { animation: spin 1s linear infinite; }
    .animate-scan-line{ animation: scan-line 2s ease-in-out infinite; position:absolute; }

    .glass-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.3);
    }
    .tab-btn { transition: all 0.25s ease; }
    .tab-btn.active {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
        box-shadow: 0 4px 15px rgba(139, 92, 246, 0.35);
    }
    .tab-btn:not(.active) { background: white; color: #6b7280; border: 1px solid #e5e7eb; }
    .tab-btn:not(.active):hover { border-color: #c4b5fd; color: #7c3aed; background: #f5f3ff; }
    .member-result-card { transition: all 0.2s ease; }
    .member-result-card:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(0,0,0,0.1); border-color: #8b5cf6; }
    .input-field {
        width: 100%; padding: 0.65rem 1rem; border: 1px solid #e5e7eb; border-radius: 0.75rem;
        font-size: 0.875rem; transition: all 0.2s;
        background: rgba(255,255,255,0.7);
    }
    .input-field:focus { outline: none; border-color: #8b5cf6; box-shadow: 0 0 0 3px rgba(139,92,246,0.15); }
    .input-field[readonly] { background: #f9fafb; color: #6b7280; }
    .live-clock { font-variant-numeric: tabular-nums; }

    /* Scan button pulse */
    .scan-btn-pulse { position:relative; overflow:hidden; }
    .scan-btn-pulse::after {
        content:''; position:absolute; inset:0; border-radius:inherit;
        border:2px solid currentColor;
        animation:pulse-ring 2s ease-out infinite; pointer-events:none;
    }

    /* Scanner modal dark theme */
    .scanner-modal-backdrop {
        position: fixed; inset: 0; z-index: 50;
        background: rgba(0,0,0,0.78); backdrop-filter: blur(4px);
        display: flex; align-items: center; justify-content: center; padding: 20px;
    }
    .scanner-modal-backdrop.hidden { display: none; }
    .scanner-pinjam-box {
        background: #111827; border-radius: 20px; width: 100%; max-width: 480px;
        overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.65);
        display: flex; flex-direction: column; max-height: 90vh;
    }
    .scanner-pinjam-header {
        display: flex; align-items: center; justify-content: space-between;
        padding: 13px 16px; background: rgba(0,0,0,0.45);
        border-bottom: 1px solid rgba(255,255,255,0.08); flex-shrink: 0;
    }
    .scanner-pinjam-video {
        position: relative; width: 100%; aspect-ratio: 4/3;
        background: #000; overflow: hidden; flex-shrink: 0;
    }
    .scanner-pinjam-footer {
        padding: 12px 14px; background: rgba(0,0,0,0.45);
        border-top: 1px solid rgba(255,255,255,0.08); flex-shrink: 0;
    }
</style>

<div class="space-y-5">
    {{-- Header --}}
    <!-- <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.buku-tamu.index') }}" class="w-10 h-10 bg-gray-100 hover:bg-gray-200 rounded-xl flex items-center justify-center transition-colors">
                    <i class="fas fa-arrow-left text-gray-600"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Catat Kunjungan</h1>
                    <p class="text-sm text-gray-500">
                        <i class="fas fa-clock mr-1"></i>
                        <span id="live-clock" class="live-clock">{{ now()->format('H:i') }}</span> WITA &middot; {{ now()->translatedFormat('d F Y') }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-xl">
                    <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                    <span class="text-xs font-medium text-emerald-700" id="status-text">Siap</span>
                </div>
            </div>
        </div>
    </div> -->

    {{-- Tab Selector: Anggota / Tamu Umum --}}
    <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade" style="animation-delay:0.05s">
        <p class="text-sm font-medium text-gray-600 mb-3">Pilih jenis tamu:</p>
        <div class="flex gap-3">
            <button type="button" id="tab-anggota" class="tab-btn active flex-1 py-3 px-4 rounded-xl text-sm font-semibold flex items-center justify-center gap-2" onclick="switchTab('anggota')">
                <i class="fas fa-id-card"></i> Anggota Perpustakaan
            </button>
            <button type="button" id="tab-umum" class="tab-btn flex-1 py-3 px-4 rounded-xl text-sm font-semibold flex items-center justify-center gap-2" onclick="switchTab('umum')">
                <i class="fas fa-user-friends"></i> Tamu Umum / Non-Anggota
            </button>
        </div>
    </div>

    {{-- Panel Anggota: Search & Scan --}}
    <div id="panel-anggota" class="space-y-5">
        <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade" style="animation-delay:0.1s">
            <h2 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <div class="w-8 h-8 bg-violet-100 rounded-lg flex items-center justify-center"><i class="fas fa-search text-violet-600 text-xs"></i></div>
                Cari atau Scan Anggota
            </h2>
            <div class="flex gap-2">
                <div class="relative flex-1">
                    <input type="text" id="search-member"
                           class="input-field pl-10"
                           placeholder="Ketik nama, nomor anggota, atau barcode...">
                    <!-- <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div> -->
                </div>
                <button type="button" id="search-btn"
                        class="px-4 py-2.5 bg-violet-100 hover:bg-violet-200 text-violet-700 rounded-xl font-medium transition-colors text-sm">
                    <i class="fas fa-search"></i>
                </button>
                <button type="button" id="scan-btn"
                        class="scan-btn-pulse px-4 py-2.5 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white rounded-xl font-medium shadow-md hover:shadow-lg transition-all text-sm flex items-center gap-1.5">
                    <i class="fas fa-barcode"></i> Scan
                </button>
            </div>
            <div id="search-loading" class="text-center mt-3 hidden">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-violet-50 rounded-xl">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-violet-600"></div>
                    <span class="text-sm text-violet-700">Mencari anggota...</span>
                </div>
            </div>
        </div>

        {{-- Search Results --}}
        <div id="search-results" class="glass-card rounded-2xl shadow-lg p-5" style="display: none;">
            <h3 class="text-sm font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i class="fas fa-users text-violet-500"></i> Hasil Pencarian
            </h3>
            <div id="members-list" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4"></div>
        </div>
    </div>

    {{-- Panel Tamu Umum (hidden by default) --}}
    <div id="panel-umum" class="hidden"></div>

    {{-- Form Section (shared for both) --}}
    <div id="member-form" style="display: none;" class="space-y-5">

        {{-- Member Data Card (only for anggota) --}}
        <div id="member-data-card" class="glass-card rounded-2xl shadow-lg p-5 animate-fade" style="display: none;">
            <div class="flex items-center gap-2 mb-4">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center"><i class="fas fa-id-badge text-blue-600 text-xs"></i></div>
                <h3 class="text-sm font-semibold text-gray-800">Data Anggota Terpilih</h3>
                <button type="button" onclick="resetAll()" class="ml-auto text-xs text-red-500 hover:text-red-700 font-medium">
                    <i class="fas fa-times mr-1"></i>Ganti Anggota
                </button>
            </div>
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
                <img id="member-photo" src="{{ asset('images/default-avatar.png') }}" alt="Foto"
                     class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-200 shadow-md flex-shrink-0"
                     onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                <div class="flex-1 grid grid-cols-1 sm:grid-cols-2 gap-3 w-full">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nama Lengkap</label>
                        <input type="text" id="member-name" class="input-field" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Anggota</label>
                        <input type="text" id="member-number" class="input-field" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Kelas</label>
                        <input type="text" id="member-class" class="input-field" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jurusan</label>
                        <input type="text" id="member-major" class="input-field" readonly>
                    </div>
                </div>
            </div>
        </div>

        {{-- Form Kunjungan --}}
        <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 bg-emerald-100 rounded-lg flex items-center justify-center"><i class="fas fa-pen text-emerald-600 text-xs"></i></div>
                <h3 class="text-sm font-semibold text-gray-800">Form Kunjungan</h3>
            </div>

            <form id="attendance-form">
                <input type="hidden" id="anggota-id" name="anggota_id">
                <input type="hidden" id="status-kunjungan" name="status_kunjungan" value="datang">

                {{-- Non-member fields --}}
                <div id="non-member-fields" style="display: none;">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label for="nama-tamu" class="block text-xs font-medium text-gray-600 mb-1.5">Nama Tamu <span class="text-red-500">*</span></label>
                            <input type="text" id="nama-tamu" name="nama_tamu" class="input-field" placeholder="Nama lengkap tamu...">
                        </div>
                        <div>
                            <label for="instansi" class="block text-xs font-medium text-gray-600 mb-1.5">Instansi / Asal Sekolah</label>
                            <input type="text" id="instansi" name="instansi" class="input-field" placeholder="Nama instansi atau sekolah...">
                        </div>
                        <div>
                            <label for="no-telepon" class="block text-xs font-medium text-gray-600 mb-1.5">Nomor Telepon</label>
                            <input type="tel" id="no-telepon" name="no_telepon" class="input-field" placeholder="08xxxxxxxxxx">
                        </div>
                    </div>
                    <hr class="border-gray-200 mb-5">
                </div>

                {{-- Shared fields --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                    <div>
                        <label for="keperluan" class="block text-xs font-medium text-gray-600 mb-1.5">Keperluan <span class="text-red-500">*</span></label>
                        <select id="keperluan" name="keperluan" class="input-field">
                            <option value="">-- Pilih keperluan --</option>
                            <option value="Membaca Buku">Membaca Buku</option>
                            <option value="Meminjam Buku">Meminjam Buku</option>
                            <option value="Mengembalikan Buku">Mengembalikan Buku</option>
                            <option value="Belajar/Kerja Kelompok">Belajar/Kerja Kelompok</option>
                            <option value="Konsultasi dengan Petugas">Konsultasi dengan Petugas</option>
                            <option value="Menggunakan Komputer/Internet">Menggunakan Komputer/Internet</option>
                            <option value="Mengikuti Kegiatan Perpustakaan">Mengikuti Kegiatan Perpustakaan</option>
                            <option value="Penelitian/Riset">Penelitian/Riset</option>
                            <option value="Kunjungan Akademik">Kunjungan Akademik</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label for="waktu-datang" class="block text-xs font-medium text-gray-600 mb-1.5">Waktu Datang (WITA)</label>
                        <div class="relative">
                            <input type="datetime-local" id="waktu-datang" name="waktu_datang" class="input-field pl-10" value="{{ now()->format('Y-m-d\TH:i') }}" readonly>
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                                <!-- <i class="fas fa-clock text-gray-400 text-sm"></i> -->
                            </div>
                        </div>
                        <p class="text-xs text-gray-400 mt-1">Otomatis real-time WITA</p>
                    </div>
                    <div class="sm:col-span-2">
                        <label for="keterangan" class="block text-xs font-medium text-gray-600 mb-1.5">Keterangan <span class="text-gray-400">(Opsional)</span></label>
                        <textarea id="keterangan" name="keterangan" rows="2" class="input-field" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="px-6 py-2.5 bg-gradient-to-r from-violet-500 to-purple-600 hover:from-violet-600 hover:to-purple-700 text-white text-sm font-semibold rounded-xl shadow-md hover:shadow-lg transition-all flex items-center gap-2">
                        <i class="fas fa-save"></i> Catat Kunjungan
                    </button>
                    <button type="button" id="reset-form"
                            class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors flex items-center gap-2">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════
     BARCODE SCANNER MODAL (dark theme, advanced)
══════════════════════════════════════════════════════ --}}
<div id="scannerModal" class="scanner-modal-backdrop hidden">
    <div class="scanner-pinjam-box">

        {{-- Header --}}
        <div class="scanner-pinjam-header">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-white/15 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-barcode text-white text-xs"></i>
                </div>
                <div>
                    <h3 class="text-xs font-bold text-white leading-none" id="scannerTitle">Scan Barcode Anggota</h3>
                    <p class="text-[10px] text-white/60 mt-0.5" id="scannerDescription">Arahkan kamera ke barcode anggota</p>
                </div>
            </div>
            <button type="button" id="closeScanner"
                    class="w-8 h-8 bg-white/10 hover:bg-red-500/80 rounded-full flex items-center justify-center text-white transition-colors flex-shrink-0">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>

        {{-- Video area --}}
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
                <div class="text-center text-white">
                    <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full spinner mx-auto mb-3"></div>
                    <p class="text-xs font-medium">Memulai kamera...</p>
                </div>
            </div>
            <div id="scanOverlay" class="absolute inset-0 z-10 pointer-events-none hidden">
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="relative" style="width:78%;max-width:320px;aspect-ratio:4/3;">
                        <div class="absolute top-0 left-0 w-6 h-6 border-t-4 border-l-4 border-emerald-400 rounded-tl-lg"></div>
                        <div class="absolute top-0 right-0 w-6 h-6 border-t-4 border-r-4 border-emerald-400 rounded-tr-lg"></div>
                        <div class="absolute bottom-0 left-0 w-6 h-6 border-b-4 border-l-4 border-emerald-400 rounded-bl-lg"></div>
                        <div class="absolute bottom-0 right-0 w-6 h-6 border-b-4 border-r-4 border-emerald-400 rounded-br-lg"></div>
                        <div class="absolute left-2 right-2 h-0.5 bg-gradient-to-r from-transparent via-emerald-400 to-transparent animate-scan-line"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="scanner-pinjam-footer">
            {{-- Status row --}}
            <div class="flex items-center justify-between mb-2.5">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-gray-500" id="scannerStatusDot"></span>
                    <span class="text-[11px] text-white/70" id="scannerStatus">Siap untuk scan</span>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" id="toggleTorchBtn"
                            class="w-8 h-8 bg-white/10 hover:bg-amber-500/60 rounded-lg flex items-center justify-center text-white/80 transition-colors hidden">
                        <i class="fas fa-bolt text-xs"></i>
                    </button>
                    <button type="button" id="switchCameraBtn"
                            class="w-8 h-8 bg-white/10 hover:bg-white/20 rounded-lg flex items-center justify-center text-white/80 transition-colors hidden">
                        <i class="fas fa-sync-alt text-xs"></i>
                    </button>
                </div>
            </div>
            {{-- Action buttons --}}
            <div class="flex gap-2">
                <button type="button" id="cancelScan"
                        class="flex-1 py-2 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold text-xs transition-colors">
                    <i class="fas fa-arrow-left mr-1.5"></i>Kembali
                </button>
                <button type="button" id="startScanBtn"
                        class="flex-1 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-xs transition-colors hidden">
                    <i class="fas fa-play mr-1.5"></i>Mulai Scan
                </button>
                <button type="button" id="stopScanBtn"
                        class="flex-1 py-2 bg-red-500/80 hover:bg-red-600 text-white rounded-xl font-semibold text-xs transition-colors hidden">
                    <i class="fas fa-stop mr-1.5"></i>Stop
                </button>
                <button type="button" id="manualInputBtn"
                        class="py-2 px-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold text-xs transition-colors">
                    <i class="fas fa-keyboard"></i>
                </button>
            </div>
        </div>

    </div>
</div>

{{-- Toast Messages --}}
<div id="message-container" class="fixed top-4 right-4 z-50 space-y-2"></div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
<script>
// Tab switching
let currentTab = 'anggota';
function switchTab(tab) {
    currentTab = tab;
    document.getElementById('tab-anggota').classList.toggle('active', tab === 'anggota');
    document.getElementById('tab-umum').classList.toggle('active', tab === 'umum');
    document.getElementById('panel-anggota').classList.toggle('hidden', tab !== 'anggota');

    if (tab === 'umum') {
        // Show form directly for guest
        jQuery('#member-form').show();
        jQuery('#member-data-card').hide();
        jQuery('#non-member-fields').show();
        jQuery('#anggota-id').val('');
        jQuery('#nama-tamu').val('').focus();
        jQuery('#search-results').hide();
    } else {
        // Hide form, wait for member selection
        jQuery('#member-form').hide();
        jQuery('#non-member-fields').hide();
        jQuery('#search-results').hide();
    }
}

function resetAll() {
    if (window.memberSearchScanner) {
        window.memberSearchScanner.resetForm();
    }
    switchTab(currentTab);
}

// Live clock
function updateClock() {
    const now = new Date();
    const h = String(now.getHours()).padStart(2, '0');
    const m = String(now.getMinutes()).padStart(2, '0');
    const s = String(now.getSeconds()).padStart(2, '0');
    const el = document.getElementById('live-clock');
    if (el) el.textContent = h + ':' + m + ':' + s;
}
setInterval(updateClock, 1000);
updateClock();

document.addEventListener('DOMContentLoaded', function () {
    if (typeof jQuery === 'undefined') { console.error('jQuery not loaded'); return; }

    jQuery.ajaxSetup({ headers: { 'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content') } });

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // ── Scanner State ──
    let html5QrcodeScanner = null;
    let nativeBarcodeDetector = null;
    let nativeScanStream = null;
    let nativeScanInterval = null;
    let torchEnabled = false;
    let lastScannedCode = '';
    let lastScanTime = 0;
    const scanCooldown = 1500;
    let isProcessingBarcode = false;
    let cameraDevices = [];
    let currentCameraIndex = 0;
    const hasNativeBarcodeAPI = ('BarcodeDetector' in window);

    // ── Scanner ─────────────────────────────────────
    function updateScannerStatus(state, text) {
        const dot = document.getElementById('scannerStatusDot');
        const status = document.getElementById('scannerStatus');
        if (status) status.textContent = text;
        if (dot) dot.className = state === 'active'
            ? 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse'
            : (state === 'error' ? 'w-2 h-2 rounded-full bg-red-500' : 'w-2 h-2 rounded-full bg-gray-500');
    }

    async function enumerateCameras() {
        cameraDevices = []; currentCameraIndex = 0;
        if (!navigator.mediaDevices?.enumerateDevices) return;
        try {
            const all = await navigator.mediaDevices.enumerateDevices();
            let vids = all.filter(d => d.kind === 'videoinput');
            if (!vids.length) return;
            vids.sort((a, b) => {
                const rA = /back|rear|environment|belakang/i.test(a.label) ? 1 : 0;
                const rB = /back|rear|environment|belakang/i.test(b.label) ? 1 : 0;
                return rB - rA;
            });
            cameraDevices = vids;
        } catch (e) {}
    }

    async function refreshCameraLabels() {
        if (!navigator.mediaDevices?.enumerateDevices) return;
        try {
            const all = await navigator.mediaDevices.enumerateDevices();
            let vids = all.filter(d => d.kind === 'videoinput');
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

    async function tryGetUserMedia(videoConstraints) {
        try {
            nativeScanStream = await navigator.mediaDevices.getUserMedia({ video: videoConstraints, audio: false });
            await setupVideoFromStream(nativeScanStream);
            await refreshCameraLabels();
            return true;
        } catch (err) {
            if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
            if (err.name === 'NotAllowedError' || err.name === 'PermissionDeniedError') {
                setupPermissionDenied(); return 'fatal';
            }
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
            if (videoEl.srcObject) {
                videoEl.srcObject.getTracks().forEach(t => t.stop());
            }
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
            try {
                if (caps.focusMode?.includes('continuous')) {
                    await track.applyConstraints({ advanced: [{ focusMode: 'continuous' }] });
                }
            } catch (e) {}
        }

        document.getElementById('scannerLoading').classList.add('hidden');
        document.getElementById('scannerPlaceholder').classList.add('hidden');
        document.getElementById('scannerVideo').classList.remove('hidden');
        document.getElementById('scanOverlay')?.classList.remove('hidden');
        document.getElementById('startScanBtn').classList.add('hidden');
        document.getElementById('stopScanBtn').classList.remove('hidden');

        const devLabel = cameraDevices[currentCameraIndex]?.label
            || (track?.getSettings()?.facingMode === 'user' ? 'Kamera Depan' : 'Kamera Belakang');
        const camInfo = cameraDevices.length > 1 ? ` (${currentCameraIndex + 1}/${cameraDevices.length})` : '';
        updateScannerStatus('active', devLabel.substring(0, 30) + camInfo);

        if (hasNativeBarcodeAPI) {
            nativeBarcodeDetector = new BarcodeDetector({
                formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8',
                          'upc_a', 'upc_e', 'itf', 'codabar', 'qr_code', 'data_matrix', 'aztec', 'pdf417']
            });
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
                Html5QrcodeSupportedFormats.CODE_93,  Html5QrcodeSupportedFormats.EAN_13,
                Html5QrcodeSupportedFormats.EAN_8,    Html5QrcodeSupportedFormats.UPC_A,
                Html5QrcodeSupportedFormats.UPC_E,    Html5QrcodeSupportedFormats.ITF,
                Html5QrcodeSupportedFormats.CODABAR,  Html5QrcodeSupportedFormats.QR_CODE,
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
        const camConst = hasDev
            ? { deviceId: { exact: cameraDevices[currentCameraIndex].deviceId } }
            : { facingMode: { ideal: 'environment' } };

        html5QrcodeScanner = new Html5Qrcode('reader');
        html5QrcodeScanner.start(camConst, config, onSuccess, () => {})
        .then(() => {
            loading.classList.add('hidden');
            overlay?.classList.remove('hidden');
            updateScannerStatus('active', 'Scanner aktif');
            document.getElementById('startScanBtn').classList.add('hidden');
            document.getElementById('stopScanBtn').classList.remove('hidden');
            refreshCameraLabels();
        })
        .catch(async () => {
            try { await html5QrcodeScanner.stop().catch(()=>{}); } catch(e) {}
            html5QrcodeScanner = new Html5Qrcode('reader');
            html5QrcodeScanner.start(
                { facingMode: { ideal: 'user' } },
                { ...config, videoConstraints: { width: { ideal: 640 }, height: { ideal: 480 } } },
                onSuccess, () => {}
            )
            .then(() => {
                loading.classList.add('hidden');
                overlay?.classList.remove('hidden');
                updateScannerStatus('active', 'Kamera depan aktif');
                document.getElementById('startScanBtn').classList.add('hidden');
                document.getElementById('stopScanBtn').classList.remove('hidden');
            })
            .catch(() => {
                loading.classList.add('hidden');
                placeholder.classList.remove('hidden');
                video.classList.add('hidden');
                setupManualInput();
            });
        });
    }

    async function startScanner() {
        if (cameraDevices.length > 0 && cameraDevices[currentCameraIndex]?.deviceId) {
            const dev = cameraDevices[currentCameraIndex];
            await startScannerWithDeviceId(dev.deviceId, dev.label);
        } else {
            await startWithFacingModeFallback();
        }
    }

    async function startScannerWithDeviceId(deviceId, label) {
        updateScannerStatus('idle',
            label ? 'Menghubungkan: ' + label.substring(0, 30) + '...' : 'Menghubungkan kamera...');

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

        if (typeof Html5Qrcode !== 'undefined') {
            initHTML5Scanner();
        } else {
            setupManualInput();
        }
    }

    function setupPermissionDenied() {
        document.getElementById('scannerLoading').classList.add('hidden');
        document.getElementById('scannerVideo').classList.add('hidden');
        const ph = document.getElementById('scannerPlaceholder');
        ph.classList.remove('hidden');
        ph.innerHTML = `<div class="text-center">
            <div class="w-14 h-14 bg-red-500/20 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-ban text-2xl text-red-400"></i>
            </div>
            <p class="text-sm font-semibold text-white/80 mb-1">Akses Kamera Ditolak</p>
            <p class="text-xs text-white/40 mb-4 max-w-xs mx-auto">
                Izinkan akses kamera di browser, lalu muat ulang halaman atau klik tombol di bawah.
            </p>
            <button type="button" onclick="openScannerModal()"
                    class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-xs transition-colors">
                <i class="fas fa-redo mr-1.5"></i>Coba Lagi
            </button>
            <button type="button" onclick="showManualInputDialog()"
                    class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white/80 rounded-xl font-semibold text-xs transition-colors">
                <i class="fas fa-keyboard mr-1.5"></i>Input Manual
            </button>
        </div>`;
        updateScannerStatus('error', 'Izin kamera ditolak');
    }

    function setupManualInput() {
        document.getElementById('scannerVideo').classList.add('hidden');
        document.getElementById('scannerLoading').classList.add('hidden');
        const ph = document.getElementById('scannerPlaceholder');
        ph.classList.remove('hidden');
        ph.innerHTML = `<div class="text-center">
            <div class="w-14 h-14 bg-white/10 rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-keyboard text-2xl text-white/60"></i>
            </div>
            <p class="text-sm text-white/70 mb-1">Kamera tidak tersedia</p>
            <p class="text-xs text-white/40 mb-4">Gunakan input barcode manual</p>
            <button type="button" onclick="showManualInputDialog()"
                    class="px-5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-semibold text-xs transition-colors">
                <i class="fas fa-keyboard mr-1.5"></i>Input Manual
            </button>
        </div>`;
        updateScannerStatus('error', 'Kamera tidak tersedia');
        document.getElementById('startScanBtn').classList.remove('hidden');
        document.getElementById('stopScanBtn').classList.add('hidden');
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

    async function openScannerModal() {
        document.getElementById('scannerModal').classList.remove('hidden');
        lastScannedCode = ''; lastScanTime = 0; isProcessingBarcode = false;

        document.getElementById('scannerLoading').classList.add('hidden');
        document.getElementById('scannerVideo').classList.add('hidden');
        document.getElementById('scanOverlay')?.classList.add('hidden');
        document.getElementById('startScanBtn').classList.add('hidden');
        document.getElementById('stopScanBtn').classList.add('hidden');
        document.getElementById('toggleTorchBtn').classList.add('hidden');
        document.getElementById('switchCameraBtn').classList.add('hidden');

        const ph = document.getElementById('scannerPlaceholder');
        ph.classList.remove('hidden');
        ph.innerHTML = `<div class="text-center">
            <div class="w-12 h-12 border-4 border-white/20 border-t-white rounded-full spinner mx-auto mb-3"></div>
            <p class="text-sm text-white/70">Mendeteksi kamera tersedia...</p>
        </div>`;

        updateScannerStatus('idle', 'Mendeteksi kamera...');

        await enumerateCameras();
        await startScanner();
    }

    function closeScanModal() {
        stopAllScanners();
        isProcessingBarcode = false;
        document.getElementById('scannerModal').classList.add('hidden');
        document.getElementById('scanOverlay')?.classList.add('hidden');
        document.getElementById('switchCameraBtn').classList.add('hidden');
        updateScannerStatus('idle', 'Siap untuk scan');

        const ph = document.getElementById('scannerPlaceholder');
        ph.classList.remove('hidden');
        ph.innerHTML = `<div class="text-center">
            <div class="w-16 h-16 bg-white/10 backdrop-blur-md rounded-2xl flex items-center justify-center mx-auto mb-3">
                <i class="fas fa-camera text-2xl text-white/60"></i>
            </div>
            <p class="text-sm text-white/70 mb-1">Kamera akan aktif otomatis</p>
            <p class="text-xs text-white/40">Pastikan izin kamera diaktifkan</p>
        </div>`;
        document.getElementById('scannerVideo').classList.add('hidden');
        document.getElementById('scannerLoading').classList.add('hidden');
        const nv = document.getElementById('nativeScanVideo');
        if (nv) { nv.srcObject = null; }
    }

    function processScannedBarcode(barcode) {
        if (isProcessingBarcode) return;
        isProcessingBarcode = true;
        document.getElementById('scannerStatus').textContent = 'Memproses...';

        fetch('/admin/buku-tamu/scan-barcode', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            body: JSON.stringify({ barcode })
        })
        .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
        .then(data => {
            if (data.success) {
                closeScanModal();
                window.memberSearchScanner.loadMemberData(data.data);
                showSwalNotification(data.message || 'Anggota ditemukan', 'success');
            } else {
                // Error: barcode tidak ditemukan
                isProcessingBarcode = false;
                document.getElementById('scannerStatus').textContent = 'Barcode tidak dikenal';
                updateScannerStatus('error', 'Barcode salah');
                showSwalNotification(data.message || 'Barcode tidak dikenali! Silahkan coba lagi.', 'error');
            }
        })
        .catch(err => {
            isProcessingBarcode = false;
            document.getElementById('scannerStatus').textContent = 'Error - coba lagi';
            updateScannerStatus('error', 'Gagal memproses');
            showSwalNotification('Barcode tidak dikenali! Silahkan coba lagi.', 'error');
        });
    }

    function showManualInputDialog() {
        Swal.fire({
            title: 'Input Barcode Manual',
            input: 'text',
            inputPlaceholder: 'Masukkan kode barcode...',
            inputAttributes: { autocomplete: 'off', autocorrect: 'off', spellcheck: 'false' },
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-check mr-1"></i>Proses',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#8b5cf6',
            didOpen: () => { document.querySelector('.swal2-input')?.focus(); },
            inputValidator: v => { if (!v?.trim()) return 'Masukkan kode barcode!'; }
        }).then(r => { if (r.isConfirmed && r.value) processScannedBarcode(r.value.trim()); });
    }

    function showSwalNotification(message, type = 'info') {
        const iconMap = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false,
            timer: 3000, timerProgressBar: true,
            didOpen: (t) => { t.addEventListener('mouseenter', Swal.stopTimer); t.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        Toast.fire({ icon: iconMap[type] || 'info', title: message });
    }

    // ── Event Listeners ────────────────────────────
    function setupEventListeners() {
        let searchTimeout;
        jQuery('#search-member').on('input', (e) => {
            clearTimeout(searchTimeout);
            const query = e.target.value.trim();
            if (query.length >= 2) {
                searchTimeout = setTimeout(() => window.memberSearchScanner.searchMembers(query), 500);
            } else if (query.length === 0) {
                jQuery('#search-results').hide();
            }
        });

        jQuery('#search-btn').on('click', () => {
            const q = jQuery('#search-member').val().trim();
            if (q) window.memberSearchScanner.searchMembers(q);
        });

        jQuery('#search-member').on('keypress', (e) => {
            if (e.which === 13) {
                const q = jQuery('#search-member').val().trim();
                if (q) window.memberSearchScanner.searchMembers(q);
            }
        });

        jQuery('#scan-btn').on('click', openScannerModal);
        jQuery('#attendance-form').on('submit', (e) => window.memberSearchScanner.submitAttendance(e));
        jQuery('#reset-form').on('click', () => { window.memberSearchScanner.resetForm(); switchTab(currentTab); });

        document.getElementById('closeScanner').addEventListener('click', closeScanModal);
        document.getElementById('cancelScan').addEventListener('click', closeScanModal);
        document.getElementById('startScanBtn').addEventListener('click', () => startScanner());
        document.getElementById('stopScanBtn').addEventListener('click', stopAllScanners);
        document.getElementById('scannerModal').addEventListener('click', (e) => {
            if (e.target === document.getElementById('scannerModal')) closeScanModal();
        });

        document.getElementById('switchCameraBtn').addEventListener('click', async () => {
            if (cameraDevices.length < 2) return;
            currentCameraIndex = (currentCameraIndex + 1) % cameraDevices.length;
            stopAllScanners();
            const dev = cameraDevices[currentCameraIndex];
            updateScannerStatus('idle', 'Beralih kamera...');
            await startScannerWithDeviceId(dev.deviceId, dev.label);
        });

        document.getElementById('toggleTorchBtn').addEventListener('click', async function () {
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

        document.getElementById('manualInputBtn').addEventListener('click', showManualInputDialog);
    }

    // ── MemberSearchScanner class ──────────────────
    class MemberSearchScanner {
        constructor() {}

        searchMembers(query) {
            if (query.length < 2) { this.showToast('Minimal 2 karakter', 'warning'); return; }
            jQuery('#search-loading').show();
            jQuery('#search-results').hide();

            jQuery.ajax({
                url: '/admin/buku-tamu/search-members',
                method: 'GET',
                data: { q: query },
                success: (res) => {
                    jQuery('#search-loading').hide();
                    if (res.success) this.displaySearchResults(res.data);
                    else this.showToast(res.message, 'error');
                },
                error: (xhr) => {
                    jQuery('#search-loading').hide();
                    this.showToast(xhr.responseJSON?.message || 'Gagal mencari anggota', 'error');
                }
            });
        }

        displaySearchResults(members) {
            const container = jQuery('#members-list');
            container.empty();

            if (members.length === 0) {
                container.html(`
                    <div class="col-span-full text-center py-8">
                        <div class="w-14 h-14 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-user-slash text-xl text-gray-400"></i>
                        </div>
                        <p class="text-sm font-medium text-gray-700">Anggota tidak ditemukan</p>
                        <p class="text-xs text-gray-500 mt-1">Coba kata kunci lain atau scan barcode</p>
                    </div>
                `);
            } else {
                container.append(`
                    <div class="col-span-full mb-1">
                        <p class="text-xs text-emerald-600 font-medium"><i class="fas fa-check-circle mr-1"></i> Ditemukan ${members.length} anggota</p>
                    </div>
                `);

                const defaultAvatar = '{{ asset("images/default-avatar.png") }}';
                members.forEach(member => {
                    const card = jQuery(`
                        <div class="member-result-card glass-card rounded-xl p-4 cursor-pointer border border-transparent">
                            <div class="flex items-center gap-3">
                                <img src="${member.foto || defaultAvatar}" alt="" class="w-12 h-12 rounded-xl object-cover border border-gray-200" onerror="this.src='${defaultAvatar}'">
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">${member.nama_lengkap}</h4>
                                    <p class="text-xs text-gray-500">${member.nomor_anggota} &middot; ${member.kelas}</p>
                                    <p class="text-xs text-gray-400">${member.jurusan}</p>
                                </div>
                                <button class="select-member px-3 py-1.5 bg-violet-100 hover:bg-violet-200 text-violet-700 text-xs font-semibold rounded-lg transition-colors flex-shrink-0">
                                    <i class="fas fa-check mr-1"></i>Pilih
                                </button>
                            </div>
                        </div>
                    `);
                    card.data('member', member);
                    container.append(card);
                });

                jQuery('.select-member').on('click', (e) => {
                    e.stopPropagation();
                    const card = jQuery(e.target).closest('.member-result-card');
                    const data = card.data('member');
                    this.loadMemberData(data);
                    jQuery('#search-results').hide();
                    this.showToast(`${data.nama_lengkap} dipilih`, 'success');
                });
            }
            jQuery('#search-results').show();
        }

        loadMemberData(m) {
            jQuery('#anggota-id').val(m.id);
            jQuery('#member-name').val(m.nama_lengkap);
            jQuery('#member-number').val(m.nomor_anggota);
            jQuery('#member-class').val(m.kelas);
            jQuery('#member-major').val(m.jurusan);
            jQuery('#nama-tamu').val(m.nama_lengkap);

            const defaultAvatar = '{{ asset("images/default-avatar.png") }}';
            jQuery('#member-photo').attr('src', m.foto || defaultAvatar)
                .on('error', function() { jQuery(this).attr('src', defaultAvatar); });

            jQuery('#non-member-fields').hide();
            jQuery('#member-data-card').show();
            jQuery('#member-form').show();
            jQuery('html, body').animate({ scrollTop: jQuery('#member-form').offset().top - 80 }, 400);
        }

        submitAttendance(e) {
            e.preventDefault();
            const nama = jQuery('#nama-tamu').val().trim();
            const keperluan = jQuery('#keperluan').val();
            const anggotaId = jQuery('#anggota-id').val();

            if (!anggotaId && !nama) { this.showToast('Nama tamu harus diisi', 'warning'); jQuery('#nama-tamu').focus(); return; }
            if (!keperluan) { this.showToast('Pilih keperluan kunjungan', 'warning'); jQuery('#keperluan').focus(); return; }

            const now = new Date();
            const waktu = now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0') + 'T' + String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
            jQuery('#waktu-datang').val(waktu);

            const formData = {
                anggota_id: anggotaId || null,
                nama_tamu: nama,
                instansi: jQuery('#instansi').val(),
                keperluan: keperluan,
                waktu_datang: jQuery('#waktu-datang').val(),
                no_telepon: jQuery('#no-telepon').val(),
                status_kunjungan: jQuery('#status-kunjungan').val(),
                keterangan: jQuery('#keterangan').val()
            };

            jQuery.ajax({
                url: '/admin/buku-tamu',
                method: 'POST',
                data: formData,
                success: (res) => {
                    if (res.success) {
                        Swal.fire({ icon: 'success', title: 'Berhasil!', text: res.message || 'Kunjungan berhasil dicatat', timer: 1500, showConfirmButton: false });
                        setTimeout(() => { window.location.href = '{{ route("admin.buku-tamu.index") }}'; }, 1600);
                    } else {
                        Swal.fire({ icon: 'error', title: 'Gagal!', text: res.message || 'Gagal mencatat kunjungan' });
                    }
                },
                error: (xhr) => {
                    let msg = 'Terjadi kesalahan';
                    if (xhr.status === 422 && xhr.responseJSON?.errors) {
                        msg = Object.values(xhr.responseJSON.errors)[0];
                        if (Array.isArray(msg)) msg = msg[0];
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: msg });
                }
            });
        }

        resetForm() {
            jQuery('#member-form').hide();
            jQuery('#member-data-card').hide();
            jQuery('#non-member-fields').hide();
            jQuery('#search-results').hide();
            jQuery('#search-member').val('');
            jQuery('#search-loading').hide();
            jQuery('#attendance-form')[0].reset();
            const now = new Date();
            jQuery('#waktu-datang').val(
                now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0')
                + 'T' + String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0')
            );
            jQuery('#keperluan').val('');
            jQuery('#anggota-id').val('');
        }

        showToast(message, type = 'info') {
            const colors = { success: 'bg-emerald-500', warning: 'bg-amber-500', error: 'bg-red-500', info: 'bg-blue-500' };
            const icons = { success: 'fa-check-circle', warning: 'fa-exclamation-triangle', error: 'fa-times-circle', info: 'fa-info-circle' };
            const el = document.createElement('div');
            el.className = `${colors[type] || colors.info} text-white px-4 py-3 rounded-xl shadow-lg flex items-center gap-3 text-sm animate-fade`;
            el.innerHTML = `<i class="fas ${icons[type] || icons.info}"></i><span class="flex-1">${message}</span><button onclick="this.parentElement.remove()" class="text-white/70 hover:text-white"><i class="fas fa-times"></i></button>`;
            document.getElementById('message-container').appendChild(el);
            setTimeout(() => { if (el.parentNode) el.remove(); }, 4000);
        }
    }

    window.memberSearchScanner = new MemberSearchScanner();
    setupEventListeners();

    function updateWaktu() {
        const now = new Date();
        document.getElementById('waktu-datang').value =
            now.getFullYear() + '-' + String(now.getMonth()+1).padStart(2,'0') + '-' + String(now.getDate()).padStart(2,'0')
            + 'T' + String(now.getHours()).padStart(2,'0') + ':' + String(now.getMinutes()).padStart(2,'0');
    }
    setInterval(updateWaktu, 30000);
});
</script>
@endpush
