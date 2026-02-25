@extends('layouts.admin')

@section('title', 'Proses Pengembalian')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Include HTML5-QRCode -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
/* Step Indicator */
.step-indicator { display: flex; align-items: center; justify-content: center; gap: 0; }
.step-item { display: flex; align-items: center; gap: 8px; }
.step-circle {
    width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center;
    font-size: 13px; font-weight: 700; transition: all 0.3s ease;
    background: #e5e7eb; color: #9ca3af;
}
.step-circle.active { background: linear-gradient(135deg, #10b981, #059669); color: white; box-shadow: 0 4px 12px rgba(16,185,129,0.3); }
.step-circle.completed { background: linear-gradient(135deg, #10b981, #059669); color: white; }
.step-label { font-size: 12px; font-weight: 600; color: #9ca3af; transition: color 0.3s; }
.step-label.active { color: #065f46; }
.step-label.completed { color: #059669; }
.step-line { width: 60px; height: 3px; background: #e5e7eb; margin: 0 8px; border-radius: 2px; transition: background 0.3s; }
.step-line.active { background: linear-gradient(90deg, #10b981, #059669); }

/* Glass Card */
.glass-card {
    background: rgba(255,255,255,0.95); backdrop-filter: blur(20px);
    border-radius: 16px; border: 1px solid rgba(255,255,255,0.8);
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    overflow: hidden; transition: all 0.3s ease;
}
.glass-card:hover { box-shadow: 0 12px 40px rgba(0,0,0,0.12); }

/* Book checkbox cards */
.book-check-card {
    border: 2px solid #e5e7eb; border-radius: 12px; padding: 14px;
    transition: all 0.25s ease; cursor: pointer; background: white;
}
.book-check-card:hover { border-color: #a7f3d0; background: #f0fdf4; transform: translateY(-1px); }
.book-check-card.selected { border-color: #10b981; background: #ecfdf5; box-shadow: 0 0 0 3px rgba(16,185,129,0.15); }
.book-check-card.already-returned { border-color: #d1d5db; background: #f9fafb; opacity: 0.6; cursor: not-allowed; }

/* Peminjaman card */
.peminjaman-card {
    border: 2px solid #e5e7eb; border-radius: 14px; padding: 16px;
    transition: all 0.3s ease; background: white;
}
.peminjaman-card.active { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.12); }
.peminjaman-card.late { border-left: 4px solid #ef4444; }

/* Fullscreen scanner */
.scanner-fullscreen {
    position: fixed; inset: 0; z-index: 9999; background: #000;
    display: none; flex-direction: column;
}
.scanner-fullscreen.active { display: flex; }
.scanner-overlay {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    pointer-events: none;
}
.scan-region {
    width: 85%; max-width: 400px; aspect-ratio: 4/3;
    border: 3px solid rgba(16,185,129,0.7); border-radius: 16px;
    position: relative; box-shadow: 0 0 0 9999px rgba(0,0,0,0.5);
}
.scan-line {
    position: absolute; left: 5%; right: 5%; height: 3px;
    background: linear-gradient(90deg, transparent, #10b981, transparent);
    border-radius: 2px; animation: scanLine 2s ease-in-out infinite;
}
@keyframes scanLine { 0%,100% { top: 10%; } 50% { top: 85%; } }

/* Summary bar */
.summary-bar {
    position: sticky; bottom: 0; z-index: 40;
    background: linear-gradient(135deg, #065f46, #047857);
    border-radius: 16px 16px 0 0; padding: 16px 24px;
    box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
    display: none;
}
.summary-bar.visible { display: flex; }

/* Animations */
@keyframes fadeInUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
.fade-in-up { animation: fadeInUp 0.4s ease forwards; }
.delay-1 { animation-delay: 0.1s; opacity: 0; }
.delay-2 { animation-delay: 0.2s; opacity: 0; }
.delay-3 { animation-delay: 0.3s; opacity: 0; }

/* Select All toggle */
.select-all-btn {
    display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px;
    border-radius: 8px; font-size: 12px; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
    border: 1.5px solid #d1d5db; color: #6b7280; background: white;
}
.select-all-btn:hover { border-color: #10b981; color: #059669; background: #f0fdf4; }
.select-all-btn.all-selected { border-color: #10b981; color: white; background: #10b981; }
</style>

<div class="min-h-screen py-6">
    <div class="px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto">

        <!-- Step Indicator -->
        <div class="mb-6 fade-in-up">
            <div class="step-indicator">
                <div class="step-item">
                    <div class="step-circle active" id="step1Circle">1</div>
                    <span class="step-label active" id="step1Label">Anggota</span>
                </div>
                <div class="step-line" id="stepLine1"></div>
                <div class="step-item">
                    <div class="step-circle" id="step2Circle">2</div>
                    <span class="step-label" id="step2Label">Pilih Buku</span>
                </div>
                <div class="step-line" id="stepLine2"></div>
                <div class="step-item">
                    <div class="step-circle" id="step3Circle">3</div>
                    <span class="step-label" id="step3Label">Konfirmasi</span>
                </div>
            </div>
        </div>

        <!-- Section 1: Pilih Anggota -->
        <div class="glass-card mb-5 fade-in-up delay-1" id="sectionAnggota">
            <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-3.5">
                <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                    <i class="fas fa-user-check"></i> Langkah 1: Pilih Anggota
                </h3>
            </div>
            <div class="p-5">
                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="flex-1 relative">
                        <input type="text" id="searchAnggotaInput"
                               placeholder="Ketik nama / nomor anggota untuk mencari..."
                               class="w-full px-4 py-3 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none transition-all"
                               autocomplete="off">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 hidden" id="searchSpinner">
                            <i class="fas fa-spinner fa-spin text-emerald-500"></i>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="refreshAnggotaBtn"
                                class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-semibold transition-all">
                            <i class="fas fa-sync mr-1"></i>Refresh
                        </button>
                        <button type="button" id="scanAnggotaBtn"
                                class="px-4 py-3 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-xs font-semibold transition-all">
                            <i class="fas fa-barcode mr-1"></i>Scan
                        </button>
                    </div>
                </div>

                <!-- Search results -->
                <div id="anggotaSearchResults" class="max-h-60 overflow-y-auto border border-gray-200 rounded-xl hidden"></div>

                <!-- Selected Anggota Info -->
                <div id="anggotaInfo" class="mt-4 p-4 bg-gradient-to-r from-emerald-50 to-green-50 rounded-xl border border-emerald-200 hidden">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-user text-white text-lg"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 id="anggotaNama" class="text-sm font-bold text-gray-900 truncate"></h4>
                            <p id="anggotaNomor" class="text-xs text-gray-600"></p>
                            <p id="anggotaKelas" class="text-xs text-gray-500"></p>
                        </div>
                        <button type="button" id="clearAnggota"
                                class="p-2 text-red-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Pilih Buku untuk Dikembalikan -->
        <div class="glass-card mb-5 fade-in-up delay-2 hidden" id="sectionPilihBuku">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-3.5">
                <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                    <i class="fas fa-book-open"></i> Langkah 2: Pilih Buku yang Dikembalikan
                </h3>
            </div>
            <div class="p-5">
                <div id="peminjamanContainer" class="space-y-5">
                    <!-- Peminjaman cards will be rendered here -->
                </div>
                <div id="noPeminjamanMsg" class="text-center py-8 hidden">
                    <i class="fas fa-inbox text-4xl text-gray-300 mb-3"></i>
                    <p class="text-sm text-gray-500">Tidak ada peminjaman aktif untuk anggota ini</p>
                </div>
            </div>
        </div>

        <!-- Section 3: Form Pengembalian -->
        <form id="pengembalianForm" action="{{ route('pengembalian.store') }}" method="POST" class="hidden" onsubmit="return validateAndSubmit(event)">
            @csrf
            <input type="hidden" name="peminjaman_id" id="selectedPeminjamanId">
            <input type="hidden" name="selected_detail_ids" id="selectedDetailIds">

            <div class="glass-card mb-5 fade-in-up delay-3" id="sectionKonfirmasi">
                <div class="bg-gradient-to-r from-purple-500 to-violet-600 px-5 py-3.5">
                    <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                        <i class="fas fa-clipboard-check"></i> Langkah 3: Detail & Konfirmasi Pengembalian
                    </h3>
                </div>
                <div class="p-5">
                    <!-- Date/Time fields -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-5">
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-calendar mr-1 text-purple-400"></i>Tanggal Pengembalian
                            </label>
                            <input type="date" name="tanggal_kembali" id="tanggal_kembali"
                                   value="{{ date('Y-m-d') }}" required
                                   class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-clock mr-1 text-purple-400"></i>Jam Pengembalian
                            </label>
                            <input type="time" name="jam_kembali" id="jam_kembali"
                                   value="{{ date('H:i') }}"
                                   class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none">
                        </div>
                    </div>

                    <!-- Late info banner -->
                    <div id="lateInfoBanner" class="mb-5 p-4 bg-gradient-to-r from-red-50 to-orange-50 border-l-4 border-red-400 rounded-r-xl hidden">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-exclamation-triangle text-red-500"></i>
                            </div>
                            <div>
                                <h5 class="text-sm font-bold text-red-800 mb-1">Terlambat Mengembalikan</h5>
                                <p id="lateInfoText" class="text-xs text-red-700"></p>
                            </div>
                        </div>
                    </div>

                    <!-- Denda fields (shown when late) -->
                    <div id="dendaFieldsSection" class="hidden mb-5">
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    <i class="fas fa-calendar-times mr-1 text-red-400"></i>Hari Terlambat
                                </label>
                                <input type="number" name="hari_terlambat" id="hari_terlambat"
                                       value="0" min="0" readonly
                                       class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    <i class="fas fa-money-bill-wave mr-1 text-yellow-500"></i>Jumlah Denda (Rp)
                                </label>
                                <input type="number" name="jumlah_denda" id="jumlah_denda"
                                       value="0" min="0" step="1000" readonly
                                       class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg bg-gray-50 cursor-not-allowed">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                    <i class="fas fa-check-circle mr-1 text-green-400"></i>Status Pembayaran
                                </label>
                                <select name="status_pembayaran_denda" id="status_pembayaran_denda"
                                        onchange="toggleTanggalPembayaran(this.value)"
                                        class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-400 outline-none">
                                    <option value="belum_dibayar">Belum Dibayar</option>
                                    <option value="sudah_dibayar">Sudah Dibayar</option>
                                </select>
                            </div>
                        </div>

                        <!-- Tanggal pembayaran — muncul hanya saat "Sudah Dibayar" dipilih -->
                        <div id="tanggalPembayaranSection" class="hidden mt-3 p-3 bg-green-50 border border-green-200 rounded-lg">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        <i class="fas fa-calendar-check mr-1 text-green-500"></i>Tanggal Pembayaran Denda
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <input type="date" name="tanggal_pembayaran_denda" id="tanggal_pembayaran_denda"
                                           value="{{ date('Y-m-d') }}"
                                           class="w-full text-xs px-3 py-2.5 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-400 focus:border-green-400 outline-none bg-white">
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        <i class="fas fa-sticky-note mr-1 text-green-400"></i>Catatan Pembayaran (opsional)
                                    </label>
                                    <input type="text" name="catatan_pembayaran_denda" id="catatan_pembayaran_denda"
                                           placeholder="Keterangan pembayaran denda..."
                                           class="w-full text-xs px-3 py-2.5 border border-green-300 rounded-lg focus:ring-2 focus:ring-green-400 focus:border-green-400 outline-none bg-white">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selected books kondisi -->
                    <div class="mb-5">
                        <h5 class="text-xs font-bold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-book text-purple-500"></i> Kondisi Buku yang Dikembalikan
                        </h5>
                        <div id="kondisiBukuList" class="space-y-2.5">
                            <p class="text-xs text-gray-400 italic">Pilih buku terlebih dahulu di Langkah 2</p>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div class="mb-5">
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            <i class="fas fa-sticky-note mr-1 text-yellow-400"></i>Catatan Pengembalian
                        </label>
                        <textarea name="catatan_pengembalian" id="catatan_pengembalian" rows="2"
                                  class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none resize-none"
                                  placeholder="Catatan tambahan (opsional)..."></textarea>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" onclick="resetForm()"
                                class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-all">
                            <i class="fas fa-undo mr-1"></i>Reset
                        </button>
                        <button type="submit" id="submitBtn"
                                class="px-6 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-emerald-200">
                            <i class="fas fa-check-circle mr-1"></i>Proses Pengembalian
                        </button>
                    </div>
                </div>
            </div>
        </form>

        <!-- Summary Bar -->
        <div class="summary-bar" id="summaryBar">
            <div class="flex items-center justify-between w-full text-white">
                <div class="flex items-center gap-4">
                    <div>
                        <p class="text-[10px] text-emerald-200 uppercase tracking-wider">Buku Dipilih</p>
                        <p class="text-lg font-bold" id="summaryBookCount">0</p>
                    </div>
                    <div class="w-px h-8 bg-emerald-400/30"></div>
                    <div>
                        <p class="text-[10px] text-emerald-200 uppercase tracking-wider">Peminjaman</p>
                        <p class="text-sm font-semibold" id="summaryPeminjamanNo">-</p>
                    </div>
                </div>
                <button type="button" onclick="scrollToForm()"
                        class="px-4 py-2 bg-white/20 hover:bg-white/30 rounded-lg text-xs font-semibold transition-all backdrop-blur">
                    <i class="fas fa-arrow-down mr-1"></i>Ke Form
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Fullscreen Barcode Scanner -->
<div class="scanner-fullscreen" id="scannerFullscreen">
    <div class="relative flex-1">
        <video id="scannerVideoEl" class="w-full h-full object-cover" playsinline autoplay muted></video>
        <div class="scanner-overlay">
            <div class="scan-region">
                <div class="scan-line"></div>
            </div>
        </div>
        <!-- Top bar -->
        <div class="absolute top-0 left-0 right-0 p-4 flex items-center justify-between" style="pointer-events:auto;">
            <div class="bg-black/50 backdrop-blur rounded-xl px-4 py-2">
                <p class="text-white text-xs font-semibold">Scan Kartu Anggota</p>
                <p class="text-gray-300 text-[10px]" id="scannerStatusText">Menginisialisasi...</p>
            </div>
            <button type="button" id="closeScannerBtn"
                    class="w-10 h-10 bg-black/50 backdrop-blur rounded-full flex items-center justify-center text-white hover:bg-red-500/80 transition-all">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <!-- Torch button -->
        <div class="absolute bottom-8 left-0 right-0 flex justify-center gap-4" style="pointer-events:auto;">
            <button type="button" id="torchBtn"
                    class="px-5 py-2.5 bg-black/50 backdrop-blur rounded-xl text-white text-xs font-semibold hidden">
                <i class="fas fa-bolt mr-1"></i>Flash
            </button>
        </div>
    </div>
    <!-- Fallback reader (hidden) -->
    <div id="fallbackReader" class="hidden"></div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// State
let selectedAnggota = null;
let allPeminjaman = []; // All active peminjaman for selected anggota
let selectedBooks = {}; // { detailId: { peminjamanId, judul, jumlah, kondisi } }
let activePeminjamanId = null; // currently active peminjaman in form

// Scanner state
let nativeBarcodeDetector = null;
let nativeScanStream = null;
let nativeScanInterval = null;
let html5QrScanner = null;
let isProcessingBarcode = false;
let lastScannedBarcode = '';
let lastScanTime = 0;

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    setupEventListeners();
    startRealTimeUpdate();
    // Check native barcode support
    if ('BarcodeDetector' in window) {
        BarcodeDetector.getSupportedFormats().then(formats => {
            if (formats.includes('code_128') || formats.includes('ean_13')) {
                nativeBarcodeDetector = new BarcodeDetector({ formats: ['code_128','code_39','ean_13','ean_8','qr_code'] });
            }
        }).catch(() => {});
    }
});

function startRealTimeUpdate() {
    updateDateTime();
    setInterval(updateDateTime, 1000);
}

function updateDateTime() {
    const now = new Date();
    const d = document.getElementById('tanggal_kembali');
    const t = document.getElementById('jam_kembali');
    if (d) d.value = now.toISOString().split('T')[0];
    if (t) t.value = now.toTimeString().slice(0, 5);
}

// ==================== STEP INDICATOR ====================
function updateSteps(step) {
    for (let i = 1; i <= 3; i++) {
        const circle = document.getElementById(`step${i}Circle`);
        const label = document.getElementById(`step${i}Label`);
        circle.classList.remove('active', 'completed');
        label.classList.remove('active', 'completed');
        if (i < step) { circle.classList.add('completed'); label.classList.add('completed'); circle.innerHTML = '<i class="fas fa-check text-xs"></i>'; }
        else if (i === step) { circle.classList.add('active'); label.classList.add('active'); circle.textContent = i; }
        else { circle.textContent = i; }
    }
    if (document.getElementById('stepLine1')) document.getElementById('stepLine1').classList.toggle('active', step >= 2);
    if (document.getElementById('stepLine2')) document.getElementById('stepLine2').classList.toggle('active', step >= 3);
}

// ==================== EVENT LISTENERS ====================
function setupEventListeners() {
    document.getElementById('scanAnggotaBtn').addEventListener('click', openScanner);
    document.getElementById('closeScannerBtn').addEventListener('click', closeScanner);
    document.getElementById('refreshAnggotaBtn').addEventListener('click', loadAllAnggota);
    document.getElementById('clearAnggota').addEventListener('click', clearAnggota);

    // Search input with debounce
    let searchTimeout;
    document.getElementById('searchAnggotaInput').addEventListener('input', function() {
        const q = this.value.trim();
        clearTimeout(searchTimeout);
        if (q === '') { document.getElementById('anggotaSearchResults').classList.add('hidden'); return; }
        if (q.length >= 2) {
            searchTimeout = setTimeout(() => searchAnggota(q), 300);
        }
    });
}

// ==================== SEARCH / SELECT ANGGOTA ====================
function searchAnggota(query) {
    const results = document.getElementById('anggotaSearchResults');
    const spinner = document.getElementById('searchSpinner');
    spinner.classList.remove('hidden');
    results.classList.remove('hidden');
    results.innerHTML = '<div class="p-4 text-center text-gray-400 text-xs"><i class="fas fa-spinner fa-spin mr-2"></i>Mencari...</div>';

    fetch(`/admin/pengembalian/search-anggota?query=${encodeURIComponent(query)}`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        spinner.classList.add('hidden');
        if (data.success && data.data && data.data.length > 0) {
            renderAnggotaList(data.data);
        } else {
            results.innerHTML = '<div class="p-4 text-center text-gray-400 text-xs">Tidak ditemukan anggota dengan peminjaman aktif</div>';
        }
    })
    .catch(err => {
        spinner.classList.add('hidden');
        results.innerHTML = '<div class="p-4 text-center text-red-400 text-xs">Terjadi kesalahan</div>';
    });
}

function loadAllAnggota() {
    searchAnggota('');
}

function renderAnggotaList(list) {
    const results = document.getElementById('anggotaSearchResults');
    let html = '';
    list.forEach(a => {
        const safeJson = JSON.stringify(a).replace(/'/g, '&#39;').replace(/"/g, '&quot;');
        html += `
        <div class="p-3 border-b border-gray-100 hover:bg-emerald-50 cursor-pointer transition-all text-xs" onclick='pickAnggota(${JSON.stringify(a).replace(/'/g, "\\'")})'>
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-green-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                    ${(a.nama_lengkap || 'N').charAt(0).toUpperCase()}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-900 truncate">${a.nama_lengkap || 'N/A'}</p>
                    <p class="text-gray-500">${a.nomor_anggota || ''} &bull; ${a.kelas || ''} &bull; <span class="font-semibold text-blue-600">${a.jumlah_peminjaman_aktif} peminjaman</span></p>
                </div>
            </div>
        </div>`;
    });
    results.innerHTML = html;
}

function pickAnggota(anggota) {
    selectedAnggota = anggota;
    document.getElementById('anggotaNama').textContent = anggota.nama_lengkap;
    document.getElementById('anggotaNomor').textContent = anggota.nomor_anggota || '';
    document.getElementById('anggotaKelas').textContent = (anggota.kelas || '') + ' - ' + (anggota.jenis_anggota || 'Siswa');
    document.getElementById('anggotaInfo').classList.remove('hidden');
    document.getElementById('anggotaSearchResults').classList.add('hidden');
    document.getElementById('searchAnggotaInput').value = '';

    // Load peminjaman aktif
    if (anggota.memiliki_peminjaman_aktif && anggota.detail_peminjaman && anggota.detail_peminjaman.length > 0) {
        // Format from search result
        const formatted = anggota.detail_peminjaman.map(p => ({
            id: p.id,
            nomor_peminjaman: p.nomor_peminjaman,
            tanggal_peminjaman: p.tanggal_peminjaman,
            tanggal_harus_kembali: p.tanggal_harus_kembali,
            is_late: p.is_late || false,
            days_late: p.days_late || 0,
            jumlah_buku: p.jumlah_buku || (p.buku ? p.buku.reduce((s,b) => s + (b.jumlah||1), 0) : 0),
            detail_peminjaman: p.buku ? p.buku.map(b => ({
                id: b.id, judul_buku: b.judul || b.judul_buku || 'N/A', jumlah: b.jumlah || 1
            })) : (p.detail_peminjaman || [])
        }));
        loadPeminjamanData(formatted);
    } else {
        // Fetch from API
        fetch(`/admin/pengembalian/get-peminjaman-aktif?anggota_id=${anggota.id}`, {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
        })
        .then(r => r.json())
        .then(data => {
            if (data.success && data.data.length > 0) {
                loadPeminjamanData(data.data);
            } else {
                showNoPeminjaman();
            }
        })
        .catch(() => showNoPeminjaman());
    }

    updateSteps(2);
    showNotification(`Anggota dipilih: ${anggota.nama_lengkap}`, 'success');
}

function showNoPeminjaman() {
    document.getElementById('sectionPilihBuku').classList.remove('hidden');
    document.getElementById('peminjamanContainer').innerHTML = '';
    document.getElementById('noPeminjamanMsg').classList.remove('hidden');
    showNotification('Anggota ini tidak memiliki peminjaman aktif', 'info');
}

function clearAnggota() {
    selectedAnggota = null;
    allPeminjaman = [];
    selectedBooks = {};
    activePeminjamanId = null;
    document.getElementById('anggotaInfo').classList.add('hidden');
    document.getElementById('sectionPilihBuku').classList.add('hidden');
    document.getElementById('pengembalianForm').classList.add('hidden');
    document.getElementById('summaryBar').classList.remove('visible');
    updateSteps(1);
}

// ==================== PEMINJAMAN & BOOK SELECTION ====================
function loadPeminjamanData(peminjamanList) {
    allPeminjaman = peminjamanList;
    selectedBooks = {};
    activePeminjamanId = null;

    const container = document.getElementById('peminjamanContainer');
    document.getElementById('noPeminjamanMsg').classList.add('hidden');
    document.getElementById('sectionPilihBuku').classList.remove('hidden');

    let html = '';
    peminjamanList.forEach((p, idx) => {
        const lateClass = p.is_late ? 'late' : '';
        const lateBadge = p.is_late
            ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[10px] font-bold"><i class="fas fa-exclamation-triangle"></i>Terlambat ${p.days_late} hari</span>`
            : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-green-100 text-green-700 text-[10px] font-bold"><i class="fas fa-check-circle"></i>Tepat waktu</span>`;

        let booksHtml = '';
        if (p.detail_peminjaman && p.detail_peminjaman.length > 0) {
            p.detail_peminjaman.forEach(d => {
                const bookKey = `${p.id}_${d.id}`;
                booksHtml += `
                <div class="book-check-card" id="bookCard_${bookKey}" onclick="toggleBook('${bookKey}', ${p.id}, ${d.id}, '${(d.judul_buku||'').replace(/'/g,"\\'")}', ${d.jumlah||1})">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-5 h-5 border-2 border-gray-300 rounded flex items-center justify-center transition-all" id="checkIcon_${bookKey}">
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-gray-900 truncate">${d.judul_buku || 'N/A'}</p>
                            <p class="text-[10px] text-gray-500">Jumlah: ${d.jumlah || 1} buku</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-book text-gray-300 text-lg"></i>
                        </div>
                    </div>
                </div>`;
            });
        }

        html += `
        <div class="peminjaman-card ${lateClass}" id="peminjamanCard_${p.id}">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h4 class="text-xs font-bold text-gray-900 flex items-center gap-2">
                        <i class="fas fa-file-alt text-blue-500"></i>${p.nomor_peminjaman}
                    </h4>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-[10px] text-gray-500">
                        <span><i class="fas fa-calendar mr-1"></i>Pinjam: ${p.tanggal_peminjaman}</span>
                        <span>&bull;</span>
                        <span><i class="fas fa-calendar-check mr-1"></i>Batas: ${p.tanggal_harus_kembali}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    ${lateBadge}
                </div>
            </div>

            <div class="flex items-center justify-between mb-3">
                <p class="text-[10px] text-gray-500 font-semibold uppercase tracking-wider">Daftar Buku</p>
                <button type="button" class="select-all-btn" id="selectAllBtn_${p.id}" onclick="toggleSelectAll(${p.id}, event)">
                    <i class="fas fa-check-double text-[10px]"></i> Pilih Semua
                </button>
            </div>

            <div class="space-y-2" id="booksList_${p.id}">
                ${booksHtml}
            </div>
        </div>`;
    });

    container.innerHTML = html;

    if (peminjamanList.length === 1) {
        showNotification('1 peminjaman aktif ditemukan. Pilih buku yang ingin dikembalikan.', 'success');
    } else {
        showNotification(`${peminjamanList.length} peminjaman aktif ditemukan. Pilih buku yang ingin dikembalikan.`, 'success');
    }
}

function toggleBook(bookKey, peminjamanId, detailId, judul, jumlah) {
    const card = document.getElementById(`bookCard_${bookKey}`);
    const checkIcon = document.getElementById(`checkIcon_${bookKey}`);

    if (selectedBooks[bookKey]) {
        // Deselect
        delete selectedBooks[bookKey];
        card.classList.remove('selected');
        checkIcon.innerHTML = '';
        checkIcon.style.borderColor = '#d1d5db';
        checkIcon.style.background = 'transparent';
    } else {
        // Select
        selectedBooks[bookKey] = { peminjamanId, detailId, judul, jumlah, kondisi: 'baik' };
        card.classList.add('selected');
        checkIcon.innerHTML = '<i class="fas fa-check text-white text-[9px]"></i>';
        checkIcon.style.borderColor = '#10b981';
        checkIcon.style.background = '#10b981';
    }

    updateSelectAllState(peminjamanId);
    updateFormState();
}

function toggleSelectAll(peminjamanId, event) {
    event.stopPropagation();
    const peminjaman = allPeminjaman.find(p => p.id === peminjamanId);
    if (!peminjaman || !peminjaman.detail_peminjaman) return;

    // Check if all are currently selected
    const allSelected = peminjaman.detail_peminjaman.every(d => {
        return selectedBooks[`${peminjamanId}_${d.id}`];
    });

    if (allSelected) {
        // Deselect all
        peminjaman.detail_peminjaman.forEach(d => {
            const key = `${peminjamanId}_${d.id}`;
            delete selectedBooks[key];
            const card = document.getElementById(`bookCard_${key}`);
            const check = document.getElementById(`checkIcon_${key}`);
            if (card) card.classList.remove('selected');
            if (check) { check.innerHTML = ''; check.style.borderColor = '#d1d5db'; check.style.background = 'transparent'; }
        });
    } else {
        // Select all
        peminjaman.detail_peminjaman.forEach(d => {
            const key = `${peminjamanId}_${d.id}`;
            selectedBooks[key] = { peminjamanId, detailId: d.id, judul: d.judul_buku || 'N/A', jumlah: d.jumlah || 1, kondisi: 'baik' };
            const card = document.getElementById(`bookCard_${key}`);
            const check = document.getElementById(`checkIcon_${key}`);
            if (card) card.classList.add('selected');
            if (check) { check.innerHTML = '<i class="fas fa-check text-white text-[9px]"></i>'; check.style.borderColor = '#10b981'; check.style.background = '#10b981'; }
        });
    }

    updateSelectAllState(peminjamanId);
    updateFormState();
}

function updateSelectAllState(peminjamanId) {
    const peminjaman = allPeminjaman.find(p => p.id === peminjamanId);
    if (!peminjaman) return;
    const btn = document.getElementById(`selectAllBtn_${peminjamanId}`);
    if (!btn) return;
    const allSelected = peminjaman.detail_peminjaman.every(d => selectedBooks[`${peminjamanId}_${d.id}`]);
    btn.classList.toggle('all-selected', allSelected);
}

function updateFormState() {
    const selected = Object.values(selectedBooks);
    const count = selected.length;

    // Summary bar
    const bar = document.getElementById('summaryBar');
    if (count > 0) {
        bar.classList.add('visible');
        document.getElementById('summaryBookCount').textContent = count;
        // Get unique peminjaman IDs
        const pIds = [...new Set(selected.map(s => s.peminjamanId))];
        // We only support 1 peminjaman at a time for the store endpoint
        if (pIds.length === 1) {
            const p = allPeminjaman.find(pm => pm.id === pIds[0]);
            document.getElementById('summaryPeminjamanNo').textContent = p ? p.nomor_peminjaman : '-';
        } else {
            document.getElementById('summaryPeminjamanNo').textContent = `${pIds.length} peminjaman`;
        }
    } else {
        bar.classList.remove('visible');
    }

    // Show/hide form
    if (count > 0) {
        // Determine the peminjaman(s) involved
        const pIds = [...new Set(selected.map(s => s.peminjamanId))];

        if (pIds.length > 1) {
            showNotification('Saat ini hanya dapat memproses pengembalian dari 1 peminjaman per transaksi. Silakan pilih buku dari satu peminjaman saja.', 'warning');
            return;
        }

        activePeminjamanId = pIds[0];
        const peminjaman = allPeminjaman.find(p => p.id === activePeminjamanId);

        document.getElementById('selectedPeminjamanId').value = activePeminjamanId;
        document.getElementById('selectedDetailIds').value = JSON.stringify(selected.map(s => s.detailId));

        // Show form
        document.getElementById('pengembalianForm').classList.remove('hidden');
        updateSteps(3);

        // Late info
        if (peminjaman && peminjaman.is_late) {
            const denda = peminjaman.days_late * 1000;
            document.getElementById('lateInfoBanner').classList.remove('hidden');
            document.getElementById('lateInfoText').textContent = `Terlambat ${peminjaman.days_late} hari. Estimasi denda keterlambatan: Rp ${denda.toLocaleString('id-ID')}`;
            document.getElementById('dendaFieldsSection').classList.remove('hidden');
            document.getElementById('hari_terlambat').value = peminjaman.days_late;
            document.getElementById('jumlah_denda').value = denda;
            // Reset status pembayaran ke default
            const statusSelect = document.getElementById('status_pembayaran_denda');
            statusSelect.value = 'belum_dibayar';
            toggleTanggalPembayaran('belum_dibayar');
        } else {
            document.getElementById('lateInfoBanner').classList.add('hidden');
            document.getElementById('dendaFieldsSection').classList.add('hidden');
            document.getElementById('tanggalPembayaranSection').classList.add('hidden');
            document.getElementById('hari_terlambat').value = 0;
            document.getElementById('jumlah_denda').value = 0;
        }

        // Kondisi buku
        renderKondisiBuku(selected);
    } else {
        document.getElementById('pengembalianForm').classList.add('hidden');
        activePeminjamanId = null;
        updateSteps(2);
    }
}

function renderKondisiBuku(selected) {
    const list = document.getElementById('kondisiBukuList');
    let html = '';
    selected.forEach(s => {
        const key = `${s.peminjamanId}_${s.detailId}`;
        html += `
        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-xl border border-gray-100">
            <div class="flex items-center gap-3 flex-1 min-w-0">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-book text-purple-500 text-xs"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-900 truncate">${s.judul}</p>
                    <p class="text-[10px] text-gray-500">Qty: ${s.jumlah}</p>
                </div>
            </div>
            <select name="kondisi_kembali[${s.detailId}]" required onchange="updateBookKondisi('${key}', this.value)"
                    class="text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none bg-white">
                <option value="baik">Baik</option>
                <option value="sedikit_rusak">Sedikit Rusak</option>
                <option value="rusak">Rusak</option>
                <option value="hilang">Hilang</option>
            </select>
        </div>`;
    });
    list.innerHTML = html;
}

function updateBookKondisi(key, kondisi) {
    if (selectedBooks[key]) selectedBooks[key].kondisi = kondisi;
}

function scrollToForm() {
    document.getElementById('sectionKonfirmasi').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

// ==================== FORM VALIDATION & SUBMIT ====================
function validateAndSubmit(event) {
    event.preventDefault();

    const selected = Object.values(selectedBooks);
    if (selected.length === 0) {
        showNotification('Pilih minimal 1 buku untuk dikembalikan!', 'error');
        return false;
    }

    // Check all from same peminjaman
    const pIds = [...new Set(selected.map(s => s.peminjamanId))];
    if (pIds.length > 1) {
        showNotification('Hanya bisa memproses 1 peminjaman per transaksi!', 'error');
        return false;
    }

    // Validate kondisi
    const kondisiInputs = document.querySelectorAll('select[name^="kondisi_kembali"]');
    let allValid = true;
    kondisiInputs.forEach(i => { if (!i.value) allValid = false; });
    if (!allValid) {
        showNotification('Pilih kondisi untuk semua buku!', 'error');
        return false;
    }

    // Validate tanggal pembayaran denda jika status sudah_dibayar
    const statusDenda = document.getElementById('status_pembayaran_denda');
    const tanggalPembayaran = document.getElementById('tanggal_pembayaran_denda');
    if (statusDenda && statusDenda.value === 'sudah_dibayar' && tanggalPembayaran && !tanggalPembayaran.value) {
        showNotification('Tanggal pembayaran denda harus diisi jika status sudah dibayar!', 'error');
        tanggalPembayaran.focus();
        return false;
    }

    // Update hidden field
    document.getElementById('selectedDetailIds').value = JSON.stringify(selected.map(s => s.detailId));

    // Confirm via SweetAlert
    const peminjaman = allPeminjaman.find(p => p.id === pIds[0]);
    const totalBooksInPeminjaman = peminjaman ? peminjaman.detail_peminjaman.length : 0;
    const isPartial = selected.length < totalBooksInPeminjaman;

    let confirmMsg = `Mengembalikan <b>${selected.length} buku</b> dari peminjaman <b>${peminjaman ? peminjaman.nomor_peminjaman : ''}</b>.`;
    if (isPartial) {
        confirmMsg += `<br><br><span class="text-orange-600 text-xs"><i class="fas fa-info-circle mr-1"></i>Pengembalian sebagian: ${totalBooksInPeminjaman - selected.length} buku lainnya belum dikembalikan.</span>`;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Konfirmasi Pengembalian',
            html: confirmMsg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-check mr-1"></i>Ya, Proses',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                document.getElementById('pengembalianForm').submit();
            }
        });
    } else {
        if (confirm('Proses pengembalian buku?')) {
            document.getElementById('pengembalianForm').submit();
        }
    }
    return false;
}

// ==================== DENDA PAYMENT TOGGLE ====================
function toggleTanggalPembayaran(value) {
    const section = document.getElementById('tanggalPembayaranSection');
    const tanggalInput = document.getElementById('tanggal_pembayaran_denda');
    if (value === 'sudah_dibayar') {
        section.classList.remove('hidden');
        tanggalInput.setAttribute('required', 'required');
    } else {
        section.classList.add('hidden');
        tanggalInput.removeAttribute('required');
        tanggalInput.value = '';
    }
}

function resetForm() {
    clearAnggota();
}

// ==================== BARCODE SCANNER ====================
function openScanner() {
    const modal = document.getElementById('scannerFullscreen');
    modal.classList.add('active');
    isProcessingBarcode = false;
    lastScannedBarcode = '';

    if (nativeBarcodeDetector) {
        startNativeScanner();
    } else {
        startFallbackScanner();
    }
}

async function startNativeScanner() {
    const videoEl = document.getElementById('scannerVideoEl');
    const statusText = document.getElementById('scannerStatusText');
    statusText.textContent = 'Memulai kamera HD...';

    try {
        nativeScanStream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: { ideal: 'environment' },
                width: { ideal: 1920, min: 1280 },
                height: { ideal: 1080, min: 720 },
                frameRate: { ideal: 30, min: 15 },
                focusMode: { ideal: 'continuous' }
            }
        });
        videoEl.srcObject = nativeScanStream;
        await videoEl.play();

        // Try torch
        const track = nativeScanStream.getVideoTracks()[0];
        const caps = track.getCapabilities ? track.getCapabilities() : {};
        if (caps.torch) {
            document.getElementById('torchBtn').classList.remove('hidden');
            document.getElementById('torchBtn').onclick = async () => {
                const settings = track.getSettings();
                await track.applyConstraints({ advanced: [{ torch: !settings.torch }] });
            };
        }

        statusText.textContent = 'Scanner aktif - arahkan ke barcode';

        // Start scanning at 20fps
        nativeScanInterval = setInterval(async () => {
            if (isProcessingBarcode) return;
            try {
                const barcodes = await nativeBarcodeDetector.detect(videoEl);
                if (barcodes.length > 0) {
                    const code = barcodes[0].rawValue;
                    const now = Date.now();
                    if (code && (code !== lastScannedBarcode || now - lastScanTime > 3000)) {
                        lastScannedBarcode = code;
                        lastScanTime = now;
                        if (navigator.vibrate) navigator.vibrate(100);
                        processScannedBarcode(code);
                    }
                }
            } catch(e) {}
        }, 50);

    } catch(err) {
        console.error('Native scanner error:', err);
        statusText.textContent = 'Gagal akses kamera, coba fallback...';
        startFallbackScanner();
    }
}

function startFallbackScanner() {
    const statusText = document.getElementById('scannerStatusText');
    statusText.textContent = 'Menggunakan scanner alternatif...';
    // Hide main video, use fallback reader
    document.getElementById('scannerVideoEl').style.display = 'none';
    const fallback = document.getElementById('fallbackReader');
    fallback.classList.remove('hidden');
    fallback.style.cssText = 'position:absolute;inset:0;';

    try {
        html5QrScanner = new Html5Qrcode('fallbackReader');
        html5QrScanner.start(
            { facingMode: 'environment' },
            { fps: 30, qrbox: { width: 300, height: 200 }, aspectRatio: 1.333, formatsToSupport: [Html5QrcodeSupportedFormats.CODE_128, Html5QrcodeSupportedFormats.CODE_39, Html5QrcodeSupportedFormats.EAN_13, Html5QrcodeSupportedFormats.QR_CODE] },
            (text) => {
                if (isProcessingBarcode) return;
                const now = Date.now();
                if (text && (text !== lastScannedBarcode || now - lastScanTime > 3000)) {
                    lastScannedBarcode = text;
                    lastScanTime = now;
                    if (navigator.vibrate) navigator.vibrate(100);
                    processScannedBarcode(text);
                }
            },
            () => {}
        ).then(() => {
            statusText.textContent = 'Scanner aktif - arahkan ke barcode';
        }).catch(err => {
            statusText.textContent = 'Gagal memulai scanner';
        });
    } catch(e) {
        statusText.textContent = 'Scanner tidak tersedia';
    }
}

function processScannedBarcode(barcode) {
    if (isProcessingBarcode) return;
    isProcessingBarcode = true;
    document.getElementById('scannerStatusText').textContent = 'Memproses barcode...';

    fetch('/admin/pengembalian/scan-barcode-anggota', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ barcode })
    })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(data => {
        isProcessingBarcode = false;
        if (data.success) {
            closeScanner();
            // Set anggota from scan data
            const a = data.data.anggota;
            selectedAnggota = a;
            document.getElementById('anggotaNama').textContent = a.nama_lengkap;
            document.getElementById('anggotaNomor').textContent = a.nomor_anggota || '';

            const kelasName = (typeof a.kelas === 'object' && a.kelas !== null)
                ? (a.kelas.nama_kelas || 'N/A')
                : (a.kelas || 'N/A');
            document.getElementById('anggotaKelas').textContent = kelasName + ' - ' + (a.jenis_anggota || 'Siswa');
            document.getElementById('anggotaInfo').classList.remove('hidden');

            // Load peminjaman
            if (data.data.peminjaman && data.data.peminjaman.length > 0) {
                loadPeminjamanData(data.data.peminjaman);
            } else {
                showNoPeminjaman();
            }
            updateSteps(2);
            showNotification(`Anggota ditemukan: ${a.nama_lengkap}`, 'success');
        } else {
            showNotification(data.message || 'Anggota tidak ditemukan', 'error');
            document.getElementById('scannerStatusText').textContent = 'Scan gagal - coba lagi';
        }
    })
    .catch(err => {
        isProcessingBarcode = false;
        showNotification('Error: ' + err.message, 'error');
        document.getElementById('scannerStatusText').textContent = 'Error - coba lagi';
    });
}

function closeScanner() {
    isProcessingBarcode = false;
    lastScannedBarcode = '';

    // Stop native
    if (nativeScanInterval) { clearInterval(nativeScanInterval); nativeScanInterval = null; }
    if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }

    // Stop fallback
    if (html5QrScanner) { html5QrScanner.stop().catch(() => {}); html5QrScanner = null; }

    // Reset UI
    const videoEl = document.getElementById('scannerVideoEl');
    videoEl.srcObject = null;
    videoEl.style.display = '';
    document.getElementById('fallbackReader').classList.add('hidden');
    document.getElementById('torchBtn').classList.add('hidden');
    document.getElementById('scannerFullscreen').classList.remove('active');
}

// ==================== NOTIFICATION ====================
function showNotification(message, type = 'info') {
    if (typeof Swal !== 'undefined') {
        const iconMap = { success: 'success', error: 'error', warning: 'warning', info: 'info' };
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false,
            timer: 3000, timerProgressBar: true,
            didOpen: (t) => { t.addEventListener('mouseenter', Swal.stopTimer); t.addEventListener('mouseleave', Swal.resumeTimer); }
        });
        Toast.fire({ icon: iconMap[type] || 'info', title: message });
    } else {
        console.log(`[${type.toUpperCase()}] ${message}`);
    }
}
</script>
@endsection
