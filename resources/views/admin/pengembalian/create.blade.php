@extends('layouts.admin')

@section('title', 'Proses Pengembalian')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Include HTML5-QRCode -->
<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
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
.book-check-card:hover { border-color: #93c5fd; background: #eff6ff; transform: translateY(-1px); }
.book-check-card.selected { border-color: #3b82f6; background: #eff6ff; box-shadow: 0 0 0 3px rgba(59,130,246,0.15); }
.book-check-card.already-returned { border-color: #d1d5db; background: #f9fafb; opacity: 0.6; cursor: not-allowed; }

/* Peminjaman card */
.peminjaman-card {
    border: 2px solid #e5e7eb; border-radius: 14px; padding: 16px;
    transition: all 0.3s ease; background: white;
}
.peminjaman-card.active { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,0.12); }
.peminjaman-card.late { border-left: 4px solid #ef4444; }

/* Modal scanner */
.scanner-fullscreen {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(0,0,0,0.75); backdrop-filter: blur(4px);
    display: none; align-items: center; justify-content: center; padding: 20px;
}
.scanner-fullscreen.active { display: flex; }
.scanner-modal-box {
    background: #111827; border-radius: 20px; width: 100%; max-width: 420px;
    overflow: hidden; box-shadow: 0 25px 60px rgba(0,0,0,0.6);
    display: flex; flex-direction: column;
}
.scanner-modal-header {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 16px; background: rgba(0,0,0,0.4);
    border-bottom: 1px solid rgba(255,255,255,0.08);
}
.scanner-video-wrap {
    position: relative; width: 100%; aspect-ratio: 4/3; background: #000; overflow: hidden;
}
.scanner-modal-footer {
    display: flex; justify-content: center; align-items: center;
    padding: 12px; background: rgba(0,0,0,0.4);
    border-top: 1px solid rgba(255,255,255,0.08); min-height: 52px;
}
.scanner-overlay {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    pointer-events: none;
}
.scan-region {
    width: 72%; max-width: 280px; aspect-ratio: 4/3;
    border: 2px solid rgba(59,130,246,0.85); border-radius: 12px;
    position: relative;
}
.scan-line {
    position: absolute; left: 5%; right: 5%; height: 2px;
    background: linear-gradient(90deg, transparent, #3b82f6, transparent);
    border-radius: 2px; animation: scanLine 2s ease-in-out infinite;
}
@keyframes scanLine { 0%,100% { top: 10%; } 50% { top: 85%; } }

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
.select-all-btn:hover { border-color: #3b82f6; color: #2563eb; background: #eff6ff; }
.select-all-btn.all-selected { border-color: #3b82f6; color: white; background: #3b82f6; }

/* ══════════════════════════════════════════════
   DARK MODE OVERRIDES — Pengembalian Create
══════════════════════════════════════════════ */
html[data-theme="dark"] .glass-card {
    background: rgba(30,41,59,0.95) !important;
    border-color: #334155 !important;
}
html[data-theme="dark"] .book-check-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}
html[data-theme="dark"] .book-check-card:hover {
    background: rgba(59,130,246,0.08) !important;
    border-color: rgba(59,130,246,0.4) !important;
}
html[data-theme="dark"] .book-check-card.selected {
    background: rgba(59,130,246,0.12) !important;
    border-color: rgba(59,130,246,0.5) !important;
}
html[data-theme="dark"] .book-check-card.already-returned {
    background: #0f172a !important;
    border-color: #1e293b !important;
}

html[data-theme="dark"] .peminjaman-card {
    background: #1e293b !important;
    border-color: #334155 !important;
}

html[data-theme="dark"] .select-all-btn {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #94a3b8 !important;
}
html[data-theme="dark"] .select-all-btn:hover {
    background: rgba(59,130,246,0.1) !important;
    border-color: rgba(59,130,246,0.4) !important;
    color: #60a5fa !important;
}

html[data-theme="dark"] #anggotaSearchResults {
    background: #1e293b !important;
    border-color: #334155 !important;
}
html[data-theme="dark"] #anggotaSearchResults > * {
    border-color: #334155 !important;
}
</style>

<div class="min-h-screen py-6">
    <div class="px-4 sm:px-6 lg:px-8 max-w-14xl mx-auto">

        <!-- Section 1: Pilih Anggota -->
        <div class="glass-card mb-5 fade-in-up delay-1" id="sectionAnggota">
            <div class="bg-blue-500 px-5 py-3.5">
                <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                    <i class="fas fa-user-check"></i> Pilih Anggota
                </h3>
            </div>
            <div class="p-5">
                <div class="flex flex-col sm:flex-row gap-3 mb-4">
                    <div class="flex-1 relative">
                        <input type="text" id="searchAnggotaInput"
                               placeholder="Ketik nama / nomor anggota untuk mencari..."
                               class="w-full px-4 py-3 text-xs border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition-all"
                               autocomplete="off">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3 hidden" id="searchSpinner">
                            <i class="fas fa-spinner fa-spin text-black"></i>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button type="button" id="refreshAnggotaBtn"
                                class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-semibold transition-all">
                            <i class="fas fa-sync mr-1"></i>Refresh
                        </button>
                        <button type="button" id="scanAnggotaBtn"
                                class="px-4 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-xs font-semibold transition-all">
                            <i class="fas fa-barcode mr-1"></i>Scan
                        </button>
                    </div>
                </div>

                <!-- Search results -->
                <div id="anggotaSearchResults" class="max-h-60 overflow-y-auto border border-gray-200 rounded-xl hidden"></div>

                <!-- Selected Anggota Info -->
                <div id="anggotaInfo" class="mt-4 p-4 bg-white rounded-xl border hidden">
                    <div class="flex items-center gap-4">
                        <div class="anggota-photo-wrapper w-14 h-14 rounded-full flex-shrink-0 shadow-lg relative">
                            <img id="anggotaFoto" src="" alt="" class="w-14 h-14 rounded-full object-cover hidden"
                                 onerror="this.classList.add('hidden');document.getElementById('anggotaIcon').classList.remove('hidden')">
                            <div id="anggotaIcon" class="w-14 h-14 bg-blue-500 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-white text-lg"></i>
                            </div>
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

        <!-- Section 2: Pilih Buku & Pengembalian -->
        <form id="pengembalianForm" action="{{ route('pengembalian.store') }}" method="POST" onsubmit="return validateAndSubmit(event)">
            @csrf
            <input type="hidden" name="peminjaman_id" id="selectedPeminjamanId">
            <input type="hidden" name="selected_detail_ids" id="selectedDetailIds">
            <input type="hidden" name="tanggal_kembali" id="tanggal_kembali">
            <input type="hidden" name="jam_kembali" id="jam_kembali">
            <input type="hidden" name="hari_terlambat" id="hari_terlambat" value="0">
            <input type="hidden" name="jumlah_denda" id="jumlah_denda" value="0">

            <div class="glass-card mb-5 fade-in-up delay-2 hidden" id="sectionPilihBuku">
                <div class="bg-blue-600 px-5 py-3.5">
                    <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                        <i class="fas fa-book-open"></i> Pilih Buku yang Dikembalikan
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

                    <!-- Daftar buku yang dikembalikan & denda -->
                    <div id="returnFormContent" class="hidden mt-6 pt-6 border-t border-gray-200 space-y-5">
                        <!-- Per-book denda section -->
                        <div>
                            <h5 class="text-xs font-bold text-gray-800 flex items-center gap-2">
                                <i class="fas fa-book text-purple-500"></i> Rincian Denda per Buku
                            </h5>
                            <div id="dendaList" class="space-y-3 mt-3">
                                <p class="text-xs text-gray-400 italic">Pilih buku terlebih dahulu</p>
                            </div>
                        </div>

                        <!-- Total denda & payment section -->
                        <div id="dendaFieldsSection" class="hidden">
                            <div class="flex items-center justify-between px-4 py-3 bg-red-50 border border-red-200 rounded-xl">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-coins text-red-400 text-sm"></i>
                                    <span class="text-xs font-bold text-red-800">Total Denda Keseluruhan</span>
                                </div>
                                <span id="totalDendaDisplay" class="text-sm font-extrabold text-red-600">Rp 0</span>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-4">
                                <div>
                                    <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                        <i class="fas fa-check-circle mr-1 text-blue-400"></i>Status Pembayaran
                                    </label>
                                    <select name="status_pembayaran_denda" id="status_pembayaran_denda"
                                            onchange="toggleTanggalPembayaran(this.value)"
                                            class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none">
                                        <option value="belum_dibayar">Belum Dibayar</option>
                                        <option value="sudah_dibayar">Sudah Dibayar</option>
                                    </select>
                                </div>
                            </div>
                            <div id="tanggalPembayaranSection" class="hidden mt-3 p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                            <i class="fas fa-calendar-check mr-1 text-blue-500"></i>Tanggal Pembayaran Denda <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" name="tanggal_pembayaran_denda" id="tanggal_pembayaran_denda"
                                               value="{{ date('Y-m-d') }}"
                                               class="w-full text-xs px-3 py-2.5 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none bg-white">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                            <i class="fas fa-sticky-note mr-1 text-blue-400"></i>Catatan Pembayaran (opsional)
                                        </label>
                                        <input type="text" name="catatan_pembayaran_denda" id="catatan_pembayaran_denda"
                                               placeholder="Keterangan pembayaran denda..."
                                               class="w-full text-xs px-3 py-2.5 border border-blue-300 rounded-lg focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none bg-white">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Catatan -->
                        <div>
                            <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-sticky-note mr-1 text-yellow-400"></i>Catatan Pengembalian
                            </label>
                            <textarea name="catatan_pengembalian" id="catatan_pengembalian" rows="2"
                                      class="w-full text-xs px-3 py-2.5 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none resize-none"
                                      placeholder="Catatan tambahan (opsional)..."></textarea>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end gap-3 pt-3 border-t border-gray-100">
                            
                            <button type="submit" id="submitBtn"
                                    class="px-6 py-3 w-full bg-blue-600 text-white rounded-xl text-xs font-bold transition-all shadow-lg shadow-blue-200">
                                <i class="fas fa-check-circle mr-1"></i>Proses Pengembalian
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Modal Barcode Scanner -->
<div class="scanner-fullscreen" id="scannerFullscreen">
    <div class="scanner-modal-box">
        <!-- Header -->
        <div class="scanner-modal-header">
            <div>
                <p class="text-white text-xs font-semibold"><i class="fas fa-barcode mr-1.5 text-black"></i>Scan Kartu Anggota</p>
                <p class="text-gray-400 text-[10px] mt-0.5" id="scannerStatusText">Menginisialisasi...</p>
            </div>
            <button type="button" id="closeScannerBtn"
                    class="w-9 h-9 bg-white/10 hover:bg-red-500/80 rounded-full flex items-center justify-center text-white transition-all flex-shrink-0">
                <i class="fas fa-times text-xs"></i>
            </button>
        </div>
        <!-- Video area -->
        <div class="scanner-video-wrap">
            <video id="scannerVideoEl" class="w-full h-full object-cover" playsinline autoplay muted></video>
            <div class="scanner-overlay">
                <div class="scan-region">
                    <div class="scan-line"></div>
                </div>
            </div>
            <!-- Fallback reader (hidden) -->
            <div id="fallbackReader" class="hidden" style="position:absolute;inset:0;"></div>
        </div>
        <!-- Footer -->
        <div class="scanner-modal-footer">
            <button type="button" id="torchBtn"
                    class="px-5 py-2 bg-white/10 hover:bg-white/20 rounded-xl text-white text-xs font-semibold hidden transition-all">
                <i class="fas fa-bolt mr-1"></i>Flash
            </button>
            <p class="text-gray-500 text-[10px]" id="scannerFooterHint">Arahkan kamera ke barcode kartu anggota</p>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// State
let selectedAnggota = null;
let allPeminjaman = []; // All active peminjaman for selected anggota
let selectedBooks = {}; // { detailId: { peminjamanId, judul, jumlah, kondisi } }
let activePeminjamanId = null; // currently active peminjaman in form
let isSubmitting = false; // prevent double submit

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
    autoCaptureDateTime();
    // Check native barcode support
    if ('BarcodeDetector' in window) {
        BarcodeDetector.getSupportedFormats().then(formats => {
            if (formats.includes('code_128') || formats.includes('ean_13')) {
                nativeBarcodeDetector = new BarcodeDetector({ formats: ['code_128','code_39','ean_13','ean_8','qr_code'] });
            }
        }).catch(() => {});
    }
});

function autoCaptureDateTime() {
    const now = new Date();
    const year  = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    const day   = String(now.getDate()).padStart(2, '0');
    document.getElementById('tanggal_kembali').value = `${year}-${month}-${day}`;
    document.getElementById('jam_kembali').value = now.toTimeString().slice(0, 5);
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
        const fotoHtml = a.foto
            ? `<img src="/storage/anggota/${a.foto}" alt="" class="w-9 h-9 rounded-full object-cover" onerror="this.style.display='none';this.nextElementSibling.style.display='flex'"><div class="w-9 h-9 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-bold" style="display:none">${(a.nama_lengkap || 'N').charAt(0).toUpperCase()}</div>`
            : `<div class="w-9 h-9 bg-blue-500 rounded-full flex items-center justify-center text-white text-xs font-bold">${(a.nama_lengkap || 'N').charAt(0).toUpperCase()}</div>`;
        html += `
        <div class="p-3 border-b border-gray-100 hover:bg-blue-50 cursor-pointer transition-all text-xs" onclick='pickAnggota(${JSON.stringify(a).replace(/'/g, "\\'")})'>
            <div class="flex items-center gap-3">
                ${fotoHtml}
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
    const fotoEl = document.getElementById('anggotaFoto');
    const iconEl = document.getElementById('anggotaIcon');
    if (anggota.foto) {
        fotoEl.src = '/storage/anggota/' + anggota.foto;
        fotoEl.classList.remove('hidden');
        iconEl.classList.add('hidden');
    } else {
        fotoEl.classList.add('hidden');
        iconEl.classList.remove('hidden');
    }

    // Load peminjaman aktif
    if (anggota.memiliki_peminjaman_aktif && anggota.detail_peminjaman && anggota.detail_peminjaman.length > 0) {
        // Format from search result
        const formatted = anggota.detail_peminjaman.map(p => ({
            id: p.id,
            nomor_peminjaman: p.nomor_peminjaman,
            tanggal_peminjaman: p.tanggal_peminjaman,
            tanggal_harus_kembali: p.tanggal_harus_kembali,
            tanggal_harus_kembali_raw: p.tanggal_harus_kembali_raw || p.tanggal_harus_kembali,
            is_late: p.is_late || false,
            days_late: p.days_late || 0,
            jumlah_buku: p.jumlah_buku || (p.buku ? p.buku.reduce((s,b) => s + (b.jumlah||1), 0) : 0),
            detail_peminjaman: p.buku ? p.buku.map(b => ({
                id: b.id,
                judul_buku: b.judul || b.judul_buku || 'N/A',
                jumlah: b.jumlah || 1,
                tanggal_harus_kembali: b.tanggal_harus_kembali || p.tanggal_harus_kembali,
                tanggal_harus_kembali_raw: b.tanggal_harus_kembali_raw || p.tanggal_harus_kembali_raw || p.tanggal_harus_kembali
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
    document.getElementById('returnFormContent').classList.add('hidden');
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
            : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold"><i class="fas fa-check-circle"></i>Tepat waktu</span>`;

        let booksHtml = '';
        if (p.detail_peminjaman && p.detail_peminjaman.length > 0) {
            p.detail_peminjaman.forEach(d => {
                const bookKey = `${p.id}_${d.id}`;
                booksHtml += `
                <div class="book-check-card" id="bookCard_${bookKey}" onclick="toggleBook('${bookKey}', ${p.id}, ${d.id}, '${(d.judul_buku||'').replace(/'/g,"\\'")}', ${d.jumlah||1}, '${d.tanggal_harus_kembali_raw||''}')">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-5 h-5 border-2 border-blue-300 rounded flex items-center justify-center transition-all" id="checkIcon_${bookKey}">
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-blue-900 truncate">${d.judul_buku || 'N/A'}</p>
                            <p class="text-[10px] text-blue-500">Jumlah: ${d.jumlah || 1} buku</p>
                        </div>
                        <div class="flex-shrink-0">
                            <i class="fas fa-book text-blue-300 text-lg"></i>
                        </div>
                    </div>
                </div>`;
            });
        }

        html += `
        <div class="peminjaman-card ${lateClass}" id="peminjamanCard_${p.id}">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <h4 class="text-xs font-bold text-blue-900 flex items-center gap-2">
                        <i class="fas fa-file-alt text-blue-500"></i>${p.nomor_peminjaman}
                    </h4>
                    <div class="flex flex-wrap items-center gap-2 mt-1 text-[10px] text-blue-500">
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
                <p class="text-[10px] text-blue-500 font-semibold uppercase tracking-wider">Daftar Buku</p>
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

function toggleBook(bookKey, peminjamanId, detailId, judul, jumlah, tglKembali) {
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
        selectedBooks[bookKey] = { peminjamanId, detailId, judul, jumlah, tanggal_harus_kembali: tglKembali || null };
        card.classList.add('selected');
        checkIcon.innerHTML = '<i class="fas fa-check text-white text-[9px]"></i>';
        checkIcon.style.borderColor = '#add8e6';
        checkIcon.style.background = '#add8e6';
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
            selectedBooks[key] = { peminjamanId, detailId: d.id, judul: d.judul_buku || 'N/A', jumlah: d.jumlah || 1, tanggal_harus_kembali: d.tanggal_harus_kembali_raw || null };
            const card = document.getElementById(`bookCard_${key}`);
            const check = document.getElementById(`checkIcon_${key}`);
            if (card) card.classList.add('selected');
            if (check) { check.innerHTML = '<i class="fas fa-check text-white text-[9px]"></i>'; check.style.borderColor = '#ADD8E6'; check.style.background = '#ADD8E6'; }
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

        // Show return content
        document.getElementById('returnFormContent').classList.remove('hidden');

        // Reset status pembayaran ke default saat peminjaman pertama dipilih
        const statusSelect = document.getElementById('status_pembayaran_denda');
        if (statusSelect) { statusSelect.value = 'belum_dibayar'; toggleTanggalPembayaran('belum_dibayar'); }

        renderDendaBuku(selected);
        recalculateDenda();
    } else {
        document.getElementById('returnFormContent').classList.add('hidden');
        activePeminjamanId = null;
    }
}

function renderDendaBuku(selected) {
    const list = document.getElementById('dendaList');
    if (selected.length === 0) {
        list.innerHTML = '<p class="text-xs text-blue-400 italic">Pilih buku terlebih dahulu</p>';
        return;
    }
    const _now1 = new Date();
    const _todayLocal = `${_now1.getFullYear()}-${String(_now1.getMonth()+1).padStart(2,'0')}-${String(_now1.getDate()).padStart(2,'0')}`;
    const tanggalKembali = document.getElementById('tanggal_kembali')?.value || _todayLocal;
    let html = '';
    selected.forEach(s => {
        const key = `${s.peminjamanId}_${s.detailId}`;

        // Hitung per-book denda keterlambatan
        let daysLate = 0;
        let dendaLate = 0;
        if (s.tanggal_harus_kembali) {
            const due  = new Date(s.tanggal_harus_kembali);
            const ret  = new Date(tanggalKembali);
            const diff = Math.floor((ret - due) / (1000 * 60 * 60 * 24));
            if (diff > 0) { daysLate = diff; dendaLate = diff * 1000; }
        }

        const subTotal = dendaLate;

        const lateChip = daysLate > 0
            ? `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 text-red-700 text-[9px] font-bold"><i class="fas fa-clock"></i>Terlambat ${daysLate} hari</span>`
            : `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-gray-100 text-gray-700 text-[9px] font-bold"><i class="fas fa-check"></i>Tepat Waktu</span>`;

        const dueDateDisplay = s.tanggal_harus_kembali
            ? new Date(s.tanggal_harus_kembali).toLocaleDateString('id-ID', {day:'2-digit',month:'short',year:'numeric'})
            : '-';

        const subTotalClass = subTotal > 0 ? 'text-red-600 font-extrabold' : 'text-black font-bold';
        const subTotalText  = subTotal > 0 ? `Rp ${subTotal.toLocaleString('id-ID')}` : 'Tidak ada denda';

        html += `
        <div class="bg-white border border-blue-200 rounded-xl overflow-hidden shadow-sm" id="dendaCard_${key}">
            <!-- Book header -->
            <div class="flex items-center gap-3 px-4 py-3 bg-blue-50 border-b border-blue-100">
                <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-book text-purple-500 text-xs"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-blue-900 truncate">${s.judul}</p>
                    <p class="text-[10px] text-blue-500">Qty: ${s.jumlah} &bull; Batas: ${dueDateDisplay}</p>
                </div>
                ${lateChip}
            </div>
            <!-- Denda detail rows -->
            <div class="px-4 py-3 space-y-2.5">
                <!-- Jumlah Dikembalikan -->
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2 text-[10px] text-blue-500 font-semibold uppercase tracking-wide">
                        <i class="fas fa-sort-amount-up-alt text-blue-400"></i>Jumlah Dikembalikan
                    </div>
                    <input type="number" name="jumlah_dikembalikan[${s.detailId}]"
                           value="${s.jumlah}" min="1" max="${s.jumlah}"
                           class="text-xs px-3 py-1.5 border border-blue-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none bg-white w-20 text-center"
                           onchange="updateBookJumlah('${key}', this.value)">
                </div>
                <!-- Denda keterlambatan -->
                <div class="flex items-center justify-between text-xs">
                    <span class="text-blue-500 flex items-center gap-1.5"><i class="fas fa-calendar-times text-red-400 text-[10px]"></i>Denda keterlambatan</span>
                    <span id="dendaLate_${key}" class="${daysLate > 0 ? 'text-red-600 font-semibold' : 'text-blue-400'}">
                        ${daysLate > 0 ? `${daysLate} hari &times; Rp 1.000 = Rp ${dendaLate.toLocaleString('id-ID')}` : 'Rp 0'}
                    </span>
                </div>
                <!-- Sub-total -->
                <div class="flex items-center justify-between pt-2 border-t border-blue-100 text-xs">
                    <span class="font-semibold text-blue-700">Sub-total buku ini</span>
                    <span id="subTotal_${key}" class="${subTotalClass}">${subTotalText}</span>
                </div>
            </div>
        </div>`;
    });
    list.innerHTML = html;
}

function updateBookJumlah(key, value) {
    if (selectedBooks[key]) {
        selectedBooks[key].jumlahDikembalikan = parseInt(value) || 1;
    }
}

function recalculateDenda() {
    if (!activePeminjamanId) return;
    const peminjaman = allPeminjaman.find(p => p.id === activePeminjamanId);
    if (!peminjaman) return;

    const selected = Object.values(selectedBooks);
    const _now2 = new Date();
    const _todayLocal2 = `${_now2.getFullYear()}-${String(_now2.getMonth()+1).padStart(2,'0')}-${String(_now2.getDate()).padStart(2,'0')}`;
    const tanggalKembali = document.getElementById('tanggal_kembali')?.value || _todayLocal2;
    const retDate = new Date(tanggalKembali);

    let totalDenda = 0;
    let maxDaysLate = 0;

    selected.forEach(s => {
        const key = `${s.peminjamanId}_${s.detailId}`;

        // Per-book denda keterlambatan
        let daysLate = 0;
        let dendaLate = 0;
        const dueDateRaw = s.tanggal_harus_kembali || peminjaman.tanggal_harus_kembali_raw;
        if (dueDateRaw) {
            const due  = new Date(dueDateRaw);
            const diff = Math.floor((retDate - due) / (1000 * 60 * 60 * 24));
            if (diff > 0) { daysLate = diff; dendaLate = diff * 1000; }
        }
        if (daysLate > maxDaysLate) maxDaysLate = daysLate;

        const subTotal = dendaLate;
        totalDenda += subTotal;

        // Update per-book display if card exists
        const lateEl = document.getElementById(`dendaLate_${key}`);
        const subEl  = document.getElementById(`subTotal_${key}`);

        if (lateEl) {
            lateEl.className = daysLate > 0 ? 'text-red-600 font-semibold' : 'text-gray-400';
            lateEl.innerHTML = daysLate > 0
                ? `${daysLate} hari &times; Rp 1.000 = Rp ${dendaLate.toLocaleString('id-ID')}`
                : 'Rp 0';
        }
        if (subEl) {
            subEl.className = subTotal > 0 ? 'text-red-600 font-extrabold' : 'text-black font-bold';
            subEl.textContent = subTotal > 0 ? `Rp ${subTotal.toLocaleString('id-ID')}` : 'Tidak ada denda';
        }
    });

    // Re-render denda cards if not yet rendered (first call)
    if (selected.length > 0 && !document.getElementById(`dendaCard_${Object.keys(selectedBooks)[0]}`)) {
        renderDendaBuku(selected);
        return;
    }

    // Update total display & hidden fields
    const totalDisplayEl = document.getElementById('totalDendaDisplay');
    if (totalDisplayEl) totalDisplayEl.textContent = `Rp ${totalDenda.toLocaleString('id-ID')}`;

    document.getElementById('hari_terlambat').value = maxDaysLate;
    document.getElementById('jumlah_denda').value   = totalDenda;

    if (totalDenda > 0) {
        document.getElementById('dendaFieldsSection').classList.remove('hidden');
        const statusSelect = document.getElementById('status_pembayaran_denda');
        if (statusSelect && !statusSelect.dataset.userChanged) {
            statusSelect.value = 'belum_dibayar';
        }
    } else {
        document.getElementById('dendaFieldsSection').classList.add('hidden');
        document.getElementById('tanggalPembayaranSection').classList.add('hidden');
    }
}

// ==================== FORM VALIDATION & SUBMIT ====================
function validateAndSubmit(event) {
    event.preventDefault();
    if (isSubmitting) return false;

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

    // Validate tanggal pembayaran denda jika status sudah_dibayar
    const statusDenda = document.getElementById('status_pembayaran_denda');
    const tanggalPembayaran = document.getElementById('tanggal_pembayaran_denda');
    if (statusDenda && statusDenda.value === 'sudah_dibayar' && tanggalPembayaran && !tanggalPembayaran.value) {
        showNotification('Tanggal pembayaran denda harus diisi jika status sudah dibayar!', 'error');
        tanggalPembayaran.focus();
        return false;
    }

    // Auto-capture current date/time before submit
    autoCaptureDateTime();

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
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="fas fa-check mr-1"></i>Ya, Proses',
            cancelButtonText: 'Batal',
        }).then(result => {
            if (result.isConfirmed) {
                isSubmitting = true;
                const btn = document.getElementById('submitBtn');
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
                document.getElementById('pengembalianForm').submit();
            }
        });
    } else {
        if (confirm('Proses pengembalian buku?')) {
            isSubmitting = true;
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Memproses...';
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
        // Auto-fill hari ini jika kosong
        if (!tanggalInput.value) {
            const _n = new Date();
            tanggalInput.value = `${_n.getFullYear()}-${String(_n.getMonth()+1).padStart(2,'0')}-${String(_n.getDate()).padStart(2,'0')}`;
        }
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
            document.getElementById('scannerFooterHint').classList.add('hidden');
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
            const fotoEl = document.getElementById('anggotaFoto');
            const iconEl = document.getElementById('anggotaIcon');
            if (a.foto) {
                fotoEl.src = '/storage/anggota/' + a.foto;
                fotoEl.classList.remove('hidden');
                iconEl.classList.add('hidden');
            } else {
                fotoEl.classList.add('hidden');
                iconEl.classList.remove('hidden');
            }

            // Load peminjaman
            if (data.data.peminjaman && data.data.peminjaman.length > 0) {
                loadPeminjamanData(data.data.peminjaman);
            } else {
                showNoPeminjaman();
            }
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
