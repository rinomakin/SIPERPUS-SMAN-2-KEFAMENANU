@extends('layouts.admin')

@section('title', 'Tambah Peminjaman')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>

<style>
@keyframes fadeIn      { from { opacity:0; } to { opacity:1; } }
@keyframes slideInUp   { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
@keyframes slideInRight{ from { opacity:0; transform:translateX(12px); } to { opacity:1; transform:translateX(0); } }
@keyframes spin        { from { transform:rotate(0deg); } to { transform:rotate(360deg); } }
@keyframes scan-line   { 0%{top:5%;opacity:0;} 10%{opacity:1;} 90%{opacity:1;} 100%{top:95%;opacity:0;} }
@keyframes pulse-ring  { 0%{transform:scale(.8);opacity:1;} 100%{transform:scale(1.4);opacity:0;} }

.animate-fade-in     { animation: fadeIn .35s ease-out forwards; }
.animate-slide-up    { animation: slideInUp .4s ease-out forwards; }
.animate-slide-right { animation: slideInRight .35s ease-out forwards; }
.spinner             { animation: spin 1s linear infinite; }
.animate-scan-line   { animation: scan-line 2s ease-in-out infinite; position:absolute; }

/* Dropdown */
.dropdown-item { transition: all .15s ease; }
.dropdown-item:hover { background:#eff6ff; transform:translateX(2px); }
.dropdown-item.selected { background:#dbeafe; border-left:3px solid #3b82f6; }

/* Search input */
.search-input:focus { box-shadow: 0 0 0 3px rgba(59,130,246,.12); }

/* Book item */
.book-item { transition: all .2s ease; }
.book-item:hover { box-shadow: 0 4px 12px -4px rgba(0,0,0,.1); }

/* Scan button pulse effect */
.scan-btn-pulse { position:relative; overflow:hidden; }
.scan-btn-pulse::after {
    content:''; position:absolute; inset:0; border-radius:inherit;
    border:2px solid currentColor;
    animation:pulse-ring 2s ease-out infinite; pointer-events:none;
}

/* Section label badge */
.section-badge {
    display:inline-flex; align-items:center; justify-content:center;
    width:28px; height:28px; border-radius:8px;
    font-size:11px; flex-shrink:0;
}

/* Responsive: on mobile stack columns */
@media (max-width: 1023px) {
    .col-divider { border-top: 1px solid #f1f5f9; border-left: none !important; }
}

/* ── Scanner Modal Box ── */
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

<div class="py-4 sm:py-6">
<div class="px-0 sm:px-2 lg:px-4">

<form action="{{ route('peminjaman.store') }}" method="POST" onsubmit="return validateForm()" id="peminjamanForm">
@csrf

{{-- ══════════════════════════════════════════════════════
     MAIN CARD — satu halaman, tidak terpisah-pisah
══════════════════════════════════════════════════════ --}}
<div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden animate-slide-up">

    {{-- ── Card Header ── --}}
    <div class="bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 px-5 sm:px-6 py-4">
        <div class="flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm flex-shrink-0">
                    <i class="fas fa-book-reader text-white text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-white">Tambah Peminjaman Buku</h2>
                    <p class="text-[10px] sm:text-xs text-blue-100">Isi data anggota, pilih buku, dan atur jadwal</p>
                </div>
            </div>
            <div class="flex items-center gap-2 bg-white/10 backdrop-blur-sm rounded-lg px-3 py-1.5 flex-shrink-0">
                <i class="fas fa-clock text-white/80 text-xs"></i>
                <span id="realTimeClock" class="text-white text-xs font-mono tracking-tight">--:--:--</span>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 1: Cari Anggota | Cari Buku (SEJAJAR)
    ══════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-2">

        {{-- ── KIRI: Cari Anggota ── --}}
        <div class="p-5 sm:p-6 border-b lg:border-b-0 lg:border-r border-gray-100">

            {{-- Section label --}}
            <div class="flex items-center gap-2 mb-4">
                <span class="section-badge bg-blue-100"><i class="fas fa-user text-blue-600"></i></span>
                <div>
                    <h3 class="text-sm font-bold text-gray-800 leading-none">Pilih Anggota</h3>
                    <p class="text-[10px] text-gray-400 mt-0.5">Cari nama / nomor atau scan barcode</p>
                </div>
            </div>

            {{-- Search bar --}}
            <div class="flex gap-2.5">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" id="anggota_search"
                           placeholder="Nama atau nomor anggota..."
                           class="search-input w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all"
                           autocomplete="off">
                    <input type="hidden" name="anggota_id" id="anggota_id" required>

                    {{-- Dropdown --}}
                    <div id="anggotaDropdown"
                         class="absolute z-50 w-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl hidden max-h-56 overflow-y-auto">
                    </div>
                </div>
                <button type="button" id="scanAnggotaBtn"
                        class="scan-btn-pulse flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-qrcode text-sm"></i>
                    <span class="hidden sm:inline">Scan</span>
                </button>
            </div>

            {{-- Info Anggota Terpilih --}}
            <div id="anggotaInfo" class="mt-3 hidden animate-fade-in">
                <div class="relative bg-blue-50 rounded-xl p-3.5 border border-blue-100">
                    <div class="flex items-center gap-3">
                        <div id="anggotaAvatarWrap" class="w-11 h-11 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-sm flex-shrink-0 overflow-hidden">
                            <i id="anggotaAvatarIcon" class="fas fa-user text-white text-sm"></i>
                            <img id="anggotaAvatarImg" src="" alt="" class="w-full h-full object-cover hidden">
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 id="anggotaNama" class="font-bold text-sm text-gray-900 truncate"></h4>
                            <p id="anggotaNomor" class="text-xs text-gray-600 flex items-center mt-0.5">
                                <i class="fas fa-id-card mr-1.5 text-gray-400 flex-shrink-0"></i>
                                <span class="truncate"></span>
                            </p>
                            <p id="anggotaKelas" class="text-xs text-gray-500 flex items-center mt-0.5">
                                <i class="fas fa-graduation-cap mr-1.5 text-gray-400 flex-shrink-0"></i>
                                <span class="truncate"></span>
                            </p>
                        </div>
                        <button type="button" id="clearAnggota"
                                class="w-7 h-7 flex items-center justify-center bg-red-100 hover:bg-red-200 text-red-500 hover:text-red-700 rounded-lg transition-all flex-shrink-0">
                            <i class="fas fa-times text-xs"></i>
                        </button>
                    </div>
                    <div class="absolute -top-2 -right-2 w-5 h-5 bg-green-500 rounded-full flex items-center justify-center shadow">
                        <i class="fas fa-check text-white text-[9px]"></i>
                    </div>
                </div>
            </div>

            {{-- Banner Peringatan Keterlambatan --}}
            <div id="overdueBanner" class="mt-3 hidden animate-fade-in">
                <div class="bg-red-50 border border-red-300 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-ban text-red-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h5 class="font-bold text-sm text-red-800">Tidak Dapat Meminjam Buku Baru!</h5>
                            <p class="text-xs text-red-700 mt-0.5 mb-2">Anggota ini memiliki buku yang melewati batas waktu pengembalian. Kembalikan buku berikut terlebih dahulu:</p>
                            <ul id="overdueBookList" class="space-y-1.5"></ul>
                        </div>
                    </div>
                </div>
            </div>

            @error('anggota_id')
            <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                <i class="fas fa-exclamation-circle"></i>{{ $message }}
            </p>
            @enderror
        </div>

        {{-- ── KANAN: Pilih Buku ── --}}
        <div class="p-5 sm:p-6">

            {{-- Section label --}}
            <div class="flex items-center gap-2 mb-4">
                <span class="section-badge bg-emerald-100"><i class="fas fa-book text-emerald-600"></i></span>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-gray-800 leading-none">Pilih Buku</h3>
                    <p class="text-[10px] text-gray-400 mt-0.5">Cari judul / penulis atau scan barcode</p>
                </div>
                <span class="flex-shrink-0 inline-flex items-center gap-1 bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">
                    <i class="fas fa-layer-group text-[10px]"></i>
                    <span id="selectedCount">0</span> buku
                </span>
            </div>

            {{-- Search bar buku --}}
            <div class="flex gap-2.5 mb-4">
                <div class="flex-1 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                    <input type="text" id="buku_search"
                           placeholder="Judul, penulis, atau ISBN..."
                           class="search-input w-full pl-10 pr-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-gray-50/60 transition-all"
                           autocomplete="off">
                    <div id="bukuDropdown"
                         class="absolute z-50 w-full mt-1.5 bg-white border border-gray-200 rounded-xl shadow-xl hidden max-h-56 overflow-y-auto text-sm">
                    </div>
                </div>
                <button type="button" id="scanBukuBtn"
                        class="scan-btn-pulse flex-shrink-0 inline-flex items-center gap-1.5 px-4 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-sm transition-all shadow-sm">
                    <i class="fas fa-qrcode text-sm"></i>
                    <span class="hidden sm:inline">Scan</span>
                </button>
            </div>

            {{-- Daftar buku terpilih --}}
            <div class="flex items-center justify-between mb-2.5">
                <span class="text-[10px] font-semibold text-gray-400 uppercase tracking-wide">Daftar Buku Dipilih</span>
                <span class="text-[10px] text-gray-400">Total: <strong class="text-emerald-600" id="totalJumlah">0</strong> eksemplar</span>
            </div>

            <div id="selectedBooks">
                <div id="selectedBooksList" class="space-y-2 max-h-52 overflow-y-auto">
                </div>

                {{-- Empty state --}}
                <div id="emptyBooksState"
                     class="bg-gradient-to-br from-slate-50 to-emerald-50/60 rounded-xl border-2 border-dashed border-emerald-200 p-5 text-center">
                    <div class="w-12 h-12 bg-white rounded-xl shadow-sm flex items-center justify-center mx-auto mb-2.5">
                        <i class="fas fa-book-open text-xl text-emerald-300"></i>
                    </div>
                    <p class="text-xs font-semibold text-gray-600 mb-0.5">Belum ada buku dipilih</p>
                    <p class="text-[11px] text-gray-400">Cari atau scan barcode buku di atas</p>
                </div>
            </div>

            <div id="hiddenBookInputs"></div>

            {{-- Debug: manual input (hidden) --}}
            <div id="manualBookInput" style="display:none;" class="mt-3 p-3 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                <p class="text-xs font-medium text-gray-600 mb-2">Debug: Manual Book Input</p>
                <div class="flex gap-2">
                    <input type="number" id="manual_book_id" placeholder="Book ID"
                           class="px-2 py-1.5 border border-gray-300 rounded text-xs w-24">
                    <button type="button" onclick="addManualBook()"
                            class="px-3 py-1.5 bg-blue-500 text-white rounded text-xs">Add</button>
                    <button type="button" onclick="toggleManualInput()"
                            class="px-3 py-1.5 bg-gray-500 text-white rounded text-xs">Hide</button>
                </div>
            </div>

            @error('buku_ids')
            <p class="text-red-500 text-xs mt-2 flex items-center gap-1">
                <i class="fas fa-exclamation-circle"></i>{{ $message }}
            </p>
            @enderror
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         ROW 2: Jadwal Peminjaman (full width)
    ══════════════════════════════════════════════ --}}
    <div class="border-t border-gray-100 p-5 sm:p-6">

        {{-- Section label --}}
        <div class="flex items-center gap-2 mb-4">
            <span class="section-badge bg-violet-100"><i class="fas fa-calendar-alt text-violet-600"></i></span>
            <div>
                <h3 class="text-sm font-bold text-gray-800 leading-none">Jadwal Peminjaman</h3>
                <p class="text-[10px] text-gray-400 mt-0.5">Atur tanggal dan waktu pinjam / kembali</p>
            </div>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">

            {{-- Tanggal Pinjam --}}
            <div>
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    <i class="fas fa-calendar text-blue-400"></i> Tgl. Pinjam
                </label>
                <input type="date" name="tanggal_peminjaman" id="tanggal_peminjaman"
                       value="{{ old('tanggal_peminjaman', date('Y-m-d')) }}" required readonly
                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed">
                <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                    <i class="fas fa-lock"></i> Otomatis hari ini
                </p>
                @error('tanggal_peminjaman')
                <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jam Pinjam --}}
            <div>
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                    <i class="fas fa-clock text-blue-400"></i> Jam Pinjam
                </label>
                <input type="time" name="jam_peminjaman" id="jam_peminjaman"
                       value="{{ old('jam_peminjaman', date('H:i')) }}" required readonly
                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 text-gray-500 cursor-not-allowed">
                <p class="text-[10px] text-gray-400 mt-1 flex items-center gap-1">
                    <i class="fas fa-lock"></i> Otomatis saat ini
                </p>
                @error('jam_peminjaman')
                <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Tanggal Harus Kembali (Default) --}}
            <div id="defaultTglCol">
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    <i class="fas fa-calendar-check text-amber-500"></i> Tgl. Kembali Default <span class="text-red-500">*</span>
                </label>
                <input type="date" name="tanggal_harus_kembali" id="tanggal_harus_kembali"
                       value="{{ old('tanggal_harus_kembali', date('Y-m-d')) }}" required
                       onchange="applyDefaultDateToAll()"
                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all hover:border-gray-400">
                <p class="text-[10px] text-gray-400 mt-1 flex items-center justify-between">
                    <span>Diterapkan ke semua buku otomatis</span>
                    <button type="button" onclick="applyDefaultDateToAll()"
                            class="text-amber-600 hover:text-amber-700 font-semibold flex items-center gap-1">
                        <i class="fas fa-magic text-[9px]"></i>Terapkan ulang
                    </button>
                </p>
                @error('tanggal_harus_kembali')
                <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Jam Kembali (Default) --}}
            <div id="defaultJamCol">
                <label class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-600 uppercase tracking-wide mb-1.5">
                    <i class="fas fa-clock text-amber-500"></i> Jam Kembali Default <span class="text-red-500">*</span>
                </label>
                <input type="time" name="jam_kembali" id="jam_kembali"
                       value="{{ old('jam_kembali') }}" required
                       onchange="applyDefaultTimeToAll()"
                       class="w-full px-3 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all hover:border-gray-400">
                <p class="text-[10px] text-gray-400 mt-1 flex items-center justify-between">
                    <span>Diterapkan ke semua buku otomatis</span>
                    <button type="button" onclick="applyDefaultTimeToAll()"
                            class="text-amber-600 hover:text-amber-700 font-semibold flex items-center gap-1">
                        <i class="fas fa-magic text-[9px]"></i>Terapkan ulang
                    </button>
                </p>
                @error('jam_kembali')
                <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Catatan --}}
        <div class="mt-4">
            <label class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500 uppercase tracking-wide mb-1.5">
                <i class="fas fa-sticky-note text-gray-400"></i>
                Catatan <span class="text-gray-400 font-normal normal-case">(opsional)</span>
            </label>
            <textarea name="catatan" id="catatan" rows="2"
                      class="w-full px-3.5 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-violet-500 transition-all hover:border-gray-400 resize-none"
                      placeholder="Tambahkan catatan jika diperlukan...">{{ old('catatan') }}</textarea>
            @error('catatan')
            <p class="text-red-500 text-[11px] mt-1">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- ══════════════════════════════════════════════
         SUBMIT BAR
    ══════════════════════════════════════════════ --}}
    <div class="border-t border-gray-100 bg-gray-50/60 px-5 sm:px-6 py-4">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-4">

            {{-- Summary info --}}
            <div class="flex items-center gap-4 text-xs text-gray-500 w-full sm:w-auto flex-wrap">
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-300 transition-colors" id="summaryAnggotaDot"></span>
                    <span id="summaryAnggota">Anggota belum dipilih</span>
                </div>
                <div class="hidden sm:block w-px h-3.5 bg-gray-300"></div>
                <div class="flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-gray-300 transition-colors" id="summaryBukuDot"></span>
                    <span id="summaryBuku">0 buku dipilih</span>
                </div>
            </div>

            {{-- Action buttons --}}
            <div class="flex items-center gap-3 w-full sm:w-auto">
                <a href="{{ route('peminjaman.index') }}"
                   class="flex-1 sm:flex-none inline-flex items-center justify-center px-5 py-2.5 bg-white hover:bg-gray-100 text-gray-700 rounded-xl font-semibold text-sm transition-all border border-gray-300">
                    <i class="fas fa-arrow-left mr-2 text-xs"></i>Batal
                </a>
                <button type="submit" id="submitBtn" disabled
                        class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white rounded-xl font-semibold text-sm transition-all shadow-md hover:shadow-lg disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:shadow-md">
                    <i class="fas fa-paper-plane mr-2 text-xs"></i>Simpan Peminjaman
                </button>
            </div>
        </div>
    </div>

</div>{{-- /main card --}}
</form>
</div>
</div>

{{-- ══════════════════════════════════════════════════════
     BARCODE SCANNER MODAL (modal box)
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
                    <h3 class="text-xs font-bold text-white leading-none" id="scannerTitle">Scan Barcode</h3>
                    <p class="text-[10px] text-white/60 mt-0.5" id="scannerDescription">Arahkan kamera ke barcode</p>
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
                        class="py-2 px-3 bg-white/10 hover:bg-white/20 text-white rounded-xl font-semibold text-xs transition-colors"
                        onclick="showManualInputDialog()">
                    <i class="fas fa-keyboard"></i>
                </button>
            </div>
        </div>

    </div>
</div>

<script>
// ─────────────────────────────────────────────────
// Utilities
// ─────────────────────────────────────────────────
function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                  document.querySelector('input[name="_token"]')?.value;

let selectedIndex  = -1;
let currentDropdown = null;

let html5QrcodeScanner = null;
let currentScanType    = null;
let nativeBarcodeDetector = null;
let nativeScanStream   = null;
let nativeScanInterval = null;
let nativeScanCanvas   = null;
let torchEnabled       = false;
let lastScannedCode    = '';
let lastScanTime       = 0;
const scanCooldown     = 1500;
let isProcessingBarcode = false;
let cameraDevices      = [];   // semua videoinput yang terdeteksi
let currentCameraIndex = 0;    // indeks kamera aktif

const hasNativeBarcodeAPI = ('BarcodeDetector' in window);

// ─────────────────────────────────────────────────
// Step indicator — no-op (removed from UI, kept for safety)
// ─────────────────────────────────────────────────
function updateStepIndicator() {
    // Step indicator removed from this layout — nothing to do
}

// ─────────────────────────────────────────────────
// Summary bar
// ─────────────────────────────────────────────────
function updateSummary() {
    const anggotaId   = document.getElementById('anggota_id').value;
    const anggotaNama = document.getElementById('anggotaNama').textContent;
    const count       = parseInt(document.getElementById('selectedCount').textContent);
    const dot1 = document.getElementById('summaryAnggotaDot');
    const dot2 = document.getElementById('summaryBukuDot');

    if (anggotaId) {
        document.getElementById('summaryAnggota').textContent = anggotaNama;
        dot1.className = 'w-2 h-2 rounded-full bg-emerald-500 transition-colors';
    } else {
        document.getElementById('summaryAnggota').textContent = 'Anggota belum dipilih';
        dot1.className = 'w-2 h-2 rounded-full bg-gray-300 transition-colors';
    }

    document.getElementById('summaryBuku').textContent = count + ' buku dipilih';
    dot2.className = count > 0
        ? 'w-2 h-2 rounded-full bg-emerald-500 transition-colors'
        : 'w-2 h-2 rounded-full bg-gray-300 transition-colors';
}

// ─────────────────────────────────────────────────
// Date / Time auto-update
// ─────────────────────────────────────────────────
function updateDateTime() {
    const now  = new Date();
    const date = now.toISOString().split('T')[0];
    const time = now.toTimeString().slice(0, 5);
    const full = now.toTimeString().slice(0, 8);

    const tgl  = document.getElementById('tanggal_peminjaman');
    const jam  = document.getElementById('jam_peminjaman');
    const clk  = document.getElementById('realTimeClock');

    if (tgl) tgl.value = date;
    if (jam) jam.value = time;
    if (clk) clk.textContent = full;
}

// ─────────────────────────────────────────────────
// Empty state
// ─────────────────────────────────────────────────
function updateEmptyState() {
    const count = parseInt(document.getElementById('selectedCount').textContent);
    const el    = document.getElementById('emptyBooksState');
    if (el) el.style.display = count > 0 ? 'none' : 'block';
}

// ─────────────────────────────────────────────────
// Submit button state
// ─────────────────────────────────────────────────
let anggotaHasOverdue = false;

function updateSubmitButton() {
    const btn   = document.getElementById('submitBtn');
    const count = parseInt(document.getElementById('selectedCount').textContent);
    const id    = document.getElementById('anggota_id').value;
    btn.disabled = !(count > 0 && id && !anggotaHasOverdue);
}

// ─────────────────────────────────────────────────
// Init
// ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    updateDateTime();
    setInterval(updateDateTime, 1000);

    const jamKembali    = document.getElementById('jam_kembali');
    const tglPinjam     = document.getElementById('tanggal_peminjaman');
    const tglKembali    = document.getElementById('tanggal_harus_kembali');

    if (jamKembali && !jamKembali.value) {
        const n = new Date(); n.setHours(n.getHours() + 1);
        jamKembali.value = n.toTimeString().slice(0, 5);
    }
    if (tglPinjam && tglKembali && !tglKembali.value) {
        tglKembali.value = tglPinjam.value;
    }

    if (jamKembali) {
        jamKembali.addEventListener('change', function () {
            if (!this.value) {
                Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Jam kembali wajib diisi!', confirmButtonColor: '#3b82f6' });
            }
        });
    }
    if (tglKembali) {
        tglKembali.addEventListener('change', validateTanggalKembali);
    }

    updateSubmitButton();
    updateSummary();
    updateEmptyState();
});

function validateTanggalKembali() {
    const tp = document.getElementById('tanggal_peminjaman').value;
    const tk = document.getElementById('tanggal_harus_kembali').value;
    if (tp && tk && new Date(tk) < new Date(tp)) {
        Swal.fire({ icon: 'error', title: 'Tanggal Tidak Valid', text: 'Tanggal kembali tidak boleh sebelum tanggal pinjam!', confirmButtonColor: '#3b82f6' });
        document.getElementById('tanggal_harus_kembali').value = tp;
        return false;
    }
    return true;
}

// ─────────────────────────────────────────────────
// Scanner — Device-Aware
// ─────────────────────────────────────────────────

// ── Button Listeners ──
document.getElementById('scanAnggotaBtn').addEventListener('click', () => {
    currentScanType = 'anggota';
    document.getElementById('scannerTitle').textContent       = 'Scan Barcode Anggota';
    document.getElementById('scannerDescription').textContent = 'Arahkan kamera ke barcode anggota';
    openScannerModal();
});

document.getElementById('scanBukuBtn').addEventListener('click', () => {
    currentScanType = 'buku';
    document.getElementById('scannerTitle').textContent       = 'Scan Barcode Buku';
    document.getElementById('scannerDescription').textContent = 'Arahkan kamera ke barcode buku';
    openScannerModal();
});

document.getElementById('closeScanner').addEventListener('click', closeScanner);
document.getElementById('cancelScan').addEventListener('click',  closeScanner);
document.getElementById('startScanBtn').addEventListener('click', () => startScanner());
document.getElementById('stopScanBtn').addEventListener('click',  stopAllScanners);

// Switch to next available camera
document.getElementById('switchCameraBtn').addEventListener('click', async () => {
    if (cameraDevices.length < 2) return;
    currentCameraIndex = (currentCameraIndex + 1) % cameraDevices.length;
    stopAllScanners();
    const dev = cameraDevices[currentCameraIndex];
    updateScannerStatus('idle', 'Beralih kamera...');
    await startScannerWithDeviceId(dev.deviceId, dev.label);
});

// Torch / flashlight toggle
document.getElementById('toggleTorchBtn').addEventListener('click', async function () {
    if (!nativeScanStream) return;
    const track = nativeScanStream.getVideoTracks()[0];
    if (!track) return;
    try {
        torchEnabled = !torchEnabled;
        await track.applyConstraints({ advanced: [{ torch: torchEnabled }] });
        this.classList.toggle('bg-amber-500/60',  torchEnabled);
        this.classList.toggle('bg-white/15', !torchEnabled);
    } catch (e) { torchEnabled = !torchEnabled; }
});

// ── Open Modal ──
async function openScannerModal() {
    document.getElementById('scannerModal').classList.remove('hidden');
    lastScannedCode = ''; lastScanTime = 0; isProcessingBarcode = false;

    // Reset semua UI dulu
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

    // Enumerate cameras kemudian start
    await enumerateCameras();
    await startScanner();
}

// ── Enumerate available cameras (tanpa butuh permission dulu) ──
async function enumerateCameras() {
    cameraDevices = []; currentCameraIndex = 0;
    if (!navigator.mediaDevices?.enumerateDevices) return;
    try {
        const all = await navigator.mediaDevices.enumerateDevices();
        let vids  = all.filter(d => d.kind === 'videoinput');
        if (!vids.length) return;

        // Sort: rear/back cameras pertama
        vids.sort((a, b) => {
            const rA = /back|rear|environment|belakang/i.test(a.label) ? 1 : 0;
            const rB = /back|rear|environment|belakang/i.test(b.label) ? 1 : 0;
            return rB - rA;
        });
        cameraDevices = vids;
    } catch (e) {}
}

// ── Re-enumerate setelah permission diberikan untuk mendapatkan label ──
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

        // Sinkronkan index: cari deviceId yang sedang aktif di list baru
        if (nativeScanStream) {
            const activeId = nativeScanStream.getVideoTracks()[0]?.getSettings()?.deviceId;
            const idx = vids.findIndex(d => d.deviceId === activeId);
            if (idx >= 0) currentCameraIndex = idx;
        }

        cameraDevices = vids;
        document.getElementById('switchCameraBtn').classList.toggle('hidden', vids.length < 2);
    } catch (e) {}
}

// ── Main start logic ──
async function startScanner() {
    if (cameraDevices.length > 0 && cameraDevices[currentCameraIndex]?.deviceId) {
        const dev = cameraDevices[currentCameraIndex];
        await startScannerWithDeviceId(dev.deviceId, dev.label);
    } else {
        // Belum ada device list — langsung pakai strategi facingMode
        await startWithFacingModeFallback();
    }
}

// ── Start dengan deviceId tertentu, dengan fallback ──
async function startScannerWithDeviceId(deviceId, label) {
    updateScannerStatus('idle',
        label ? 'Menghubungkan: ' + label.substring(0, 30) + '...' : 'Menghubungkan kamera...');

    // Attempt 1: deviceId + resolusi ideal (tanpa min)
    let r = await tryGetUserMedia({ deviceId: { exact: deviceId }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;

    // Attempt 2: deviceId saja (tanpa constraint resolusi)
    r = await tryGetUserMedia({ deviceId: { exact: deviceId } });
    if (r === 'fatal' || r === true) return;

    // Fallback ke facingMode
    await startWithFacingModeFallback();
}

// ── Fallback bertahap menggunakan facingMode ──
async function startWithFacingModeFallback() {
    // 1. environment ideal + resolusi
    let r = await tryGetUserMedia({ facingMode: { ideal: 'environment' }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;

    // 2. user ideal + resolusi
    r = await tryGetUserMedia({ facingMode: { ideal: 'user' }, width: { ideal: 1280 }, height: { ideal: 720 } });
    if (r === 'fatal' || r === true) return;

    // 3. environment tanpa resolusi
    r = await tryGetUserMedia({ facingMode: { ideal: 'environment' } });
    if (r === 'fatal' || r === true) return;

    // 4. kamera apapun (constraint minimal)
    r = await tryGetUserMedia(true);
    if (r === 'fatal' || r === true) return;

    // Semua getUserMedia gagal — coba html5-qrcode
    if (typeof Html5Qrcode !== 'undefined') {
        initHTML5Scanner();
    } else {
        setupManualInput();
    }
}

// ── Coba getUserMedia, return true | false | 'fatal' ──
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
        return false; // NotFoundError, OverconstrainedError, dll → coba strategi berikutnya
    }
}

// ── Setup elemen video dan mulai deteksi ──
async function setupVideoFromStream(stream) {
    let videoEl = document.getElementById('nativeScanVideo');
    if (!videoEl) {
        videoEl = document.createElement('video');
        videoEl.id = 'nativeScanVideo';
        videoEl.setAttribute('playsinline', '');
        videoEl.setAttribute('autoplay',    '');
        videoEl.setAttribute('muted',       '');
        videoEl.style.cssText = 'width:100%;height:100%;object-fit:cover;display:block;';
        const readerEl = document.getElementById('reader');
        readerEl.innerHTML = '';
        readerEl.appendChild(videoEl);
    } else {
        // Hentikan stream lama sebelum assign baru
        if (videoEl.srcObject) {
            videoEl.srcObject.getTracks().forEach(t => t.stop());
        }
    }

    videoEl.srcObject = stream;

    // Tunggu video siap (loadedmetadata atau timeout 8 detik)
    await new Promise((resolve) => {
        if (videoEl.readyState >= 2) { resolve(); return; }
        const onReady = () => { videoEl.removeEventListener('loadedmetadata', onReady); resolve(); };
        videoEl.addEventListener('loadedmetadata', onReady);
        videoEl.play().catch(() => {});
        setTimeout(resolve, 8000);
    });

    // Kemampuan track (torch, focus)
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

    // Tampilkan video
    document.getElementById('scannerLoading').classList.add('hidden');
    document.getElementById('scannerPlaceholder').classList.add('hidden');
    document.getElementById('scannerVideo').classList.remove('hidden');
    document.getElementById('scanOverlay')?.classList.remove('hidden');
    document.getElementById('startScanBtn').classList.add('hidden');
    document.getElementById('stopScanBtn').classList.remove('hidden');

    // Label kamera di status bar
    const devLabel = cameraDevices[currentCameraIndex]?.label
        || (track?.getSettings()?.facingMode === 'user' ? 'Kamera Depan' : 'Kamera Belakang');
    const camInfo  = cameraDevices.length > 1 ? ` (${currentCameraIndex + 1}/${cameraDevices.length})` : '';
    updateScannerStatus('active', devLabel.substring(0, 30) + camInfo);

    // Mulai deteksi barcode
    if (hasNativeBarcodeAPI) {
        nativeBarcodeDetector = new BarcodeDetector({
            formats: ['code_128', 'code_39', 'code_93', 'ean_13', 'ean_8',
                      'upc_a', 'upc_e', 'itf', 'codabar', 'qr_code', 'data_matrix', 'aztec', 'pdf417']
        });
        startNativeScanLoop(videoEl);
    } else if (typeof Html5Qrcode !== 'undefined') {
        // BarcodeDetector tidak ada, gunakan html5-qrcode di atas stream yang sama
        initHTML5Scanner();
    }
}

// ── Native BarcodeDetector scan loop (interval 50ms) ──
function startNativeScanLoop(videoEl) {
    if (nativeScanInterval) clearInterval(nativeScanInterval);
    nativeScanInterval = setInterval(async () => {
        if (!nativeBarcodeDetector || !videoEl || videoEl.readyState < 2) return;
        try {
            const barcodes = await nativeBarcodeDetector.detect(videoEl);
            if (barcodes.length > 0) {
                const code = barcodes[0].rawValue;
                const now  = Date.now();
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

// ── html5-qrcode fallback (pakai deviceId jika tersedia) ──
function initHTML5Scanner() {
    const loading     = document.getElementById('scannerLoading');
    const video       = document.getElementById('scannerVideo');
    const placeholder = document.getElementById('scannerPlaceholder');
    const overlay     = document.getElementById('scanOverlay');

    // Hentikan stream getUserMedia yang ada
    if (nativeScanStream) { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
    if (nativeScanInterval) { clearInterval(nativeScanInterval); nativeScanInterval = null; }
    if (html5QrcodeScanner) { try { html5QrcodeScanner.stop().catch(()=>{}); } catch(e){} html5QrcodeScanner = null; }

    loading.classList.remove('hidden');
    placeholder.classList.add('hidden');
    video.classList.remove('hidden');

    // Bersihkan reader
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

    // Pilih constraint: pakai deviceId kalau sudah ter-enumerate, kalau belum pakai facingMode
    const hasDev    = cameraDevices.length > 0 && cameraDevices[currentCameraIndex]?.deviceId;
    const camConst  = hasDev
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
        // Refresh label & tombol switch setelah html5-qrcode berhasil start
        refreshCameraLabels();
    })
    .catch(async () => {
        // Coba fallback terakhir: facingMode user (kamera depan / webcam desktop)
        try {
            await html5QrcodeScanner.stop().catch(()=>{});
        } catch(e) {}
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

function updateScannerStatus(state, text) {
    const dot    = document.getElementById('scannerStatusDot');
    const status = document.getElementById('scannerStatus');
    if (status) status.textContent = text;
    if (dot) dot.className = state === 'active'
        ? 'w-2 h-2 rounded-full bg-emerald-500 animate-pulse'
        : (state === 'error' ? 'w-2 h-2 rounded-full bg-red-500' : 'w-2 h-2 rounded-full bg-gray-500');
}

function stopAllScanners() {
    if (nativeScanInterval)  { clearInterval(nativeScanInterval); nativeScanInterval = null; }
    if (nativeScanStream)    { nativeScanStream.getTracks().forEach(t => t.stop()); nativeScanStream = null; }
    nativeBarcodeDetector = null; torchEnabled = false;
    if (html5QrcodeScanner)  { try { html5QrcodeScanner.stop().catch(()=>{}); } catch(e){} html5QrcodeScanner = null; }
    updateScannerStatus('idle', 'Scanner dihentikan');
    document.getElementById('startScanBtn').classList.remove('hidden');
    document.getElementById('stopScanBtn').classList.add('hidden');
    document.getElementById('toggleTorchBtn').classList.add('hidden');
    // Jangan sembunyikan switchCameraBtn agar tetap bisa berganti kamera
}

function closeScanner() {
    stopAllScanners();
    isProcessingBarcode = false;
    document.getElementById('scannerModal').classList.add('hidden');
    document.getElementById('scanOverlay')?.classList.add('hidden');
    document.getElementById('switchCameraBtn').classList.add('hidden');
    updateScannerStatus('idle', 'Siap untuk scan');

    // Reset placeholder ke default
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

// ── Tampilkan pesan izin ditolak ──
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
        <div class="flex flex-col gap-2 items-center">
            <button type="button" onclick="openScannerModal()"
                    class="px-5 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl font-semibold text-xs transition-colors">
                <i class="fas fa-redo mr-1.5"></i>Coba Lagi
            </button>
            <button type="button" onclick="showManualInputDialog()"
                    class="px-5 py-2 bg-white/10 hover:bg-white/20 text-white/80 rounded-xl font-semibold text-xs transition-colors">
                <i class="fas fa-keyboard mr-1.5"></i>Input Manual
            </button>
        </div>
    </div>`;
    updateScannerStatus('error', 'Izin kamera ditolak');
}

// ── Tampilkan UI input manual (kamera tidak tersedia) ──
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

function showManualInputDialog() {
    Swal.fire({
        title: 'Input Barcode Manual',
        input: 'text',
        inputPlaceholder: 'Masukkan kode barcode...',
        inputAttributes: { autocomplete: 'off', autocorrect: 'off', spellcheck: 'false' },
        showCancelButton: true,
        confirmButtonText: '<i class="fas fa-check mr-1"></i>Proses',
        cancelButtonText: 'Batal',
        confirmButtonColor: '#3b82f6',
        didOpen: () => { document.querySelector('.swal2-input')?.focus(); },
        inputValidator: v => { if (!v?.trim()) return 'Masukkan kode barcode!'; }
    }).then(r => { if (r.isConfirmed && r.value) processScannedBarcode(r.value.trim()); });
}

// ─────────────────────────────────────────────────
// Process barcode
// ─────────────────────────────────────────────────
function processScannedBarcode(barcode) {
    if (!currentScanType || isProcessingBarcode) return;
    isProcessingBarcode = true;
    document.getElementById('scannerStatus').textContent = 'Memproses...';

    const route = currentScanType === 'anggota'
        ? '/admin/peminjaman/scan-anggota'
        : '/admin/peminjaman/scan-buku';

    fetch(route, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ barcode })
    })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(data => {
        if (data.success) {
            if (currentScanType === 'anggota') {
                const a = data.data;
                const kelasName = (typeof a.kelas === 'object' && a.kelas !== null) ? (a.kelas.nama_kelas || 'N/A') : (a.kelas || 'N/A');
                selectAnggota({ id: a.id, nama_lengkap: a.nama_lengkap, nomor_anggota: a.nomor_anggota, barcode_anggota: a.barcode_anggota, foto: a.foto, kelas: kelasName, jenis_anggota: a.jenis_anggota });
                closeScanner();
                showNotification(`Anggota ditemukan: ${a.nama_lengkap}`, 'success');
            } else {
                const b = data.data;
                selectBook({ id: b.id, judul_buku: b.judul_buku, penulis: b.penulis, isbn: b.isbn, stok_tersedia: b.stok_tersedia, kategori: b.kategori });
                closeScanner();
                showNotification(`Buku ditemukan: ${b.judul_buku}`, 'success');
            }
        } else {
            showNotification(data.message || 'Tidak ditemukan', 'error');
            document.getElementById('scannerStatus').textContent = 'Gagal - coba lagi';
            isProcessingBarcode = false;
        }
    })
    .catch(e => {
        showNotification('Kesalahan: ' + e.message, 'error');
        document.getElementById('scannerStatus').textContent = 'Error - coba lagi';
        isProcessingBarcode = false;
    });
}

// ─────────────────────────────────────────────────
// Search Anggota
// ─────────────────────────────────────────────────
const searchAnggota = debounce(function (query) {
    const dropdown = document.getElementById('anggotaDropdown');
    currentDropdown = dropdown; selectedIndex = -1;
    if (query.length < 2) { dropdown.classList.add('hidden'); return; }

    dropdown.innerHTML = '<div class="px-4 py-3 text-center text-gray-500 text-sm"><i class="fas fa-spinner spinner mr-2"></i>Mencari...</div>';
    dropdown.classList.remove('hidden');

    fetch(`/admin/peminjaman/search-anggota?query=${encodeURIComponent(query)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(data => {
        if (data.success && data.data.length > 0) {
            dropdown.innerHTML = '';
            data.data.forEach((anggota, idx) => {
                const item = document.createElement('div');
                item.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 transition-colors dropdown-item';
                item.setAttribute('data-index', idx);
                item.innerHTML = `
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex-shrink-0 overflow-hidden ${anggota.foto ? '' : 'bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center'}">
                            ${anggota.foto
                                ? `<img src="/storage/anggota/${anggota.foto}" alt="" class="w-full h-full object-cover">`
                                : `<i class="fas fa-user text-white text-xs"></i>`
                            }
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold text-sm text-gray-900 truncate">${anggota.nama_lengkap}</div>
                            <div class="text-xs text-gray-500">${anggota.nomor_anggota} &middot; ${anggota.kelas}</div>
                        </div>
                    </div>`;
                item.addEventListener('click', () => selectAnggota(anggota));
                item.addEventListener('mouseenter', () => { selectedIndex = idx; updateSelectedItem(); });
                dropdown.appendChild(item);
            });
        } else {
            dropdown.innerHTML = '<div class="px-4 py-4 text-center text-gray-400 text-sm"><i class="fas fa-user-slash mr-2"></i>Tidak ditemukan</div>';
        }
    })
    .catch(e => { dropdown.innerHTML = `<div class="px-4 py-3 text-center text-red-500 text-sm">${e.message}</div>`; });
}, 300);

function updateSelectedItem() {
    if (!currentDropdown) return;
    currentDropdown.querySelectorAll('[data-index]').forEach((item, idx) => {
        item.classList.toggle('selected', idx === selectedIndex);
    });
}

document.getElementById('anggota_search').addEventListener('input', function () { searchAnggota(this.value.trim()); });

function selectAnggota(anggota) {
    try {
        document.getElementById('anggota_id').value = anggota.id;
        document.getElementById('anggotaNama').textContent = anggota.nama_lengkap;

        // Set foto anggota
        const avatarIcon = document.getElementById('anggotaAvatarIcon');
        const avatarImg = document.getElementById('anggotaAvatarImg');
        const avatarWrap = document.getElementById('anggotaAvatarWrap');
        if (anggota.foto) {
            avatarImg.src = '/storage/anggota/' + anggota.foto;
            avatarImg.classList.remove('hidden');
            avatarIcon.classList.add('hidden');
        } else {
            avatarImg.classList.add('hidden');
            avatarIcon.classList.remove('hidden');
        }

        const nomorEl  = document.getElementById('anggotaNomor');
        const kelasEl  = document.getElementById('anggotaKelas');
        const nSpan    = nomorEl.querySelector('span');
        const kSpan    = kelasEl.querySelector('span');
        if (nSpan) nSpan.textContent = anggota.nomor_anggota || '';
        else nomorEl.textContent = anggota.nomor_anggota || '';
        if (kSpan) kSpan.textContent = anggota.kelas || '';
        else kelasEl.textContent = anggota.kelas || '';

        document.getElementById('anggotaInfo').classList.remove('hidden');
        document.getElementById('anggota_search').value = anggota.nama_lengkap;
        document.getElementById('anggotaDropdown').classList.add('hidden');
        selectedIndex = -1;

        // Cek apakah anggota memiliki peminjaman yang melewati jatuh tempo
        checkOverdueLoans(anggota.id);

        updateSummary();
        showNotification(`${anggota.nama_lengkap} dipilih!`, 'success');
    } catch (err) {
        showNotification('Gagal memproses data anggota: ' + err.message, 'error');
    }
}

function checkOverdueLoans(anggotaId) {
    fetch(`/admin/peminjaman/check-overdue-loan?anggota_id=${anggotaId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => r.json())
    .then(data => {
        const banner   = document.getElementById('overdueBanner');
        const list     = document.getElementById('overdueBookList');
        const bukuSearch = document.getElementById('buku_search');

        if (data.success && data.has_overdue) {
            anggotaHasOverdue = true;

            // Isi daftar buku terlambat
            list.innerHTML = '';
            (data.data || []).forEach(item => {
                const li = document.createElement('li');
                li.className = 'flex items-start gap-2 text-xs bg-white border border-red-200 rounded-lg px-3 py-2';
                li.innerHTML = `
                    <i class="fas fa-book text-red-400 mt-0.5 flex-shrink-0"></i>
                    <div class="min-w-0">
                        <span class="font-semibold text-gray-900 block truncate">${item.judul_buku}</span>
                        <span class="text-red-600">Jatuh tempo: ${item.tanggal_harus_kembali}
                            &bull; <span class="font-bold">${item.hari_terlambat} hari terlambat</span>
                        </span>
                        <span class="text-gray-400 block">${item.nomor_peminjaman}</span>
                    </div>`;
                list.appendChild(li);
            });

            banner.classList.remove('hidden');
            bukuSearch.disabled = true;
            bukuSearch.placeholder = 'Selesaikan keterlambatan terlebih dahulu...';
        } else {
            anggotaHasOverdue = false;
            banner.classList.add('hidden');
            list.innerHTML = '';
            bukuSearch.disabled = false;
            bukuSearch.placeholder = 'Ketik judul / ISBN / barcode buku...';
        }

        updateSubmitButton();
    })
    .catch(() => {
        // Jika gagal cek, biarkan form tetap bisa digunakan (fail open)
        anggotaHasOverdue = false;
        updateSubmitButton();
    });
}

document.getElementById('clearAnggota').addEventListener('click', function () {
    document.getElementById('anggota_id').value = '';
    document.getElementById('anggotaInfo').classList.add('hidden');
    document.getElementById('anggota_search').value = '';
    document.getElementById('anggotaDropdown').classList.add('hidden');
    selectedIndex = -1;

    // Reset foto avatar
    document.getElementById('anggotaAvatarImg').classList.add('hidden');
    document.getElementById('anggotaAvatarIcon').classList.remove('hidden');

    // Reset overdue state
    anggotaHasOverdue = false;
    document.getElementById('overdueBanner').classList.add('hidden');
    document.getElementById('overdueBookList').innerHTML = '';
    const bukuSearch = document.getElementById('buku_search');
    bukuSearch.disabled = false;
    bukuSearch.placeholder = 'Ketik judul / ISBN / barcode buku...';

    updateSubmitButton(); updateSummary();
});

// ─────────────────────────────────────────────────
// Search Buku
// ─────────────────────────────────────────────────
const searchBuku = debounce(function (query) {
    const dropdown = document.getElementById('bukuDropdown');
    currentDropdown = dropdown; selectedIndex = -1;
    if (query.length < 2) { dropdown.classList.add('hidden'); return; }

    dropdown.innerHTML = '<div class="px-4 py-3 text-center text-gray-500 text-sm"><i class="fas fa-spinner spinner mr-2"></i>Mencari...</div>';
    dropdown.classList.remove('hidden');

    fetch(`/admin/peminjaman/search-buku?query=${encodeURIComponent(query)}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken }
    })
    .then(r => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
    .then(data => {
        if (data.success && data.data.length > 0) {
            dropdown.innerHTML = '';
            data.data.forEach((book, idx) => {
                const sc   = book.stok_tersedia > 3 ? 'text-emerald-600 bg-emerald-50' : book.stok_tersedia > 0 ? 'text-amber-600 bg-amber-50' : 'text-red-600 bg-red-50';
                const item = document.createElement('div');
                item.className = 'px-4 py-3 hover:bg-blue-50 cursor-pointer border-b border-gray-100 transition-colors dropdown-item';
                item.setAttribute('data-index', idx);
                item.innerHTML = `
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-lg flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-book text-white text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <div class="font-semibold text-sm text-gray-900 truncate">${book.judul_buku}</div>
                                <div class="text-xs text-gray-500 truncate">${book.penulis || 'N/A'} &middot; ${book.kategori}</div>
                            </div>
                        </div>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full ${sc} flex-shrink-0">Stok: ${book.stok_tersedia}</span>
                    </div>`;
                item.addEventListener('click', () => selectBook(book));
                item.addEventListener('mouseenter', () => { selectedIndex = idx; updateSelectedItem(); });
                dropdown.appendChild(item);
            });
        } else {
            dropdown.innerHTML = '<div class="px-4 py-4 text-center text-gray-400 text-sm"><i class="fas fa-book-open mr-2"></i>Tidak ditemukan</div>';
        }
    })
    .catch(e => { dropdown.innerHTML = `<div class="px-4 py-3 text-center text-red-500 text-sm">${e.message}</div>`; });
}, 300);

document.getElementById('buku_search').addEventListener('input', function () { searchBuku(this.value.trim()); });

// ─────────────────────────────────────────────────
// Select / Add / Remove Book
// ─────────────────────────────────────────────────
function selectBook(book) {
    if (document.querySelector(`[data-book-id="${book.id}"]`)) { showNotification('Buku ini sudah dipilih!', 'warning'); return; }
    if (book.stok_tersedia <= 0) { showNotification('Buku tidak tersedia!', 'error'); return; }
    const anggotaId = document.getElementById('anggota_id').value;
    if (!anggotaId) { showNotification('Pilih anggota terlebih dahulu!', 'warning'); return; }

    fetch('/admin/peminjaman/check-active-loan', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
        body: JSON.stringify({ anggota_id: anggotaId, buku_id: book.id })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.has_active_loan) {
            showNotification(data.message, 'error');
            if (data.data?.peminjaman) {
                const p = data.data.peminjaman, b = data.data.buku;
                Swal.fire({
                    icon: 'error', title: 'Buku Masih Dipinjam!', width: '500px',
                    html: `<div class="text-left text-sm">
                        <p class="mb-3 text-gray-700">${data.message}</p>
                        <div class="bg-red-50 p-4 rounded-xl border border-red-200">
                            <table class="w-full text-sm">
                                <tr><td class="py-1 text-gray-500">No. Peminjaman</td><td class="py-1 font-semibold">${p.nomor_peminjaman}</td></tr>
                                <tr><td class="py-1 text-gray-500">Tgl. Pinjam</td><td class="py-1 font-semibold">${p.tanggal_peminjaman}</td></tr>
                                <tr><td class="py-1 text-gray-500">Harus Kembali</td><td class="py-1 font-semibold">${p.tanggal_harus_kembali}</td></tr>
                                <tr><td class="py-1 text-gray-500">Status</td><td class="py-1"><span class="px-2 py-0.5 bg-amber-100 text-amber-700 rounded text-xs font-medium">${p.status}</span></td></tr>
                            </table>
                        </div>
                        <p class="mt-3 text-xs text-gray-500"><i class="fas fa-info-circle mr-1"></i>Buku harus dikembalikan terlebih dahulu.</p>
                    </div>`,
                    confirmButtonText: 'Mengerti', confirmButtonColor: '#3b82f6'
                });
            }
            return;
        }
        addBookToList(book);
    })
    .catch(() => showNotification('Kesalahan saat memeriksa pinjaman aktif.', 'error'));
}

function addBookToList(book) {
    const list  = document.getElementById('selectedBooksList');
    const count = document.getElementById('selectedCount');

    const stokColor = book.stok_tersedia > 3
        ? 'bg-emerald-100 text-emerald-700'
        : 'bg-amber-100 text-amber-700';

    const defaultTgl = document.getElementById('tanggal_harus_kembali').value || '';
    const defaultJam = document.getElementById('jam_kembali').value || '';
    const minTgl     = document.getElementById('tanggal_peminjaman').value || '';

    const item = document.createElement('div');
    item.className = 'book-item flex flex-col gap-2 p-3 bg-white rounded-xl border border-emerald-100 shadow-sm animate-slide-right';
    item.setAttribute('data-book-id', book.id);

    item.innerHTML = `
        <div class="flex items-center gap-3">
            <div class="w-9 h-9 bg-gradient-to-br from-emerald-400 to-teal-500 rounded-lg flex items-center justify-center flex-shrink-0">
                <i class="fas fa-book text-white text-xs"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h5 class="font-semibold text-xs text-gray-900 truncate leading-tight">${book.judul_buku}</h5>
                <p class="text-[11px] text-gray-500 truncate mt-0.5">${book.penulis || 'N/A'}
                    <span class="ml-1 ${stokColor} px-1.5 py-0 rounded text-[10px] font-medium">Stok: ${book.stok_tersedia}</span>
                </p>
            </div>
            <div class="flex items-center gap-1.5 flex-shrink-0">
                <input type="number" name="jumlah_buku[${book.id}]" value="1" min="1" max="${book.stok_tersedia}"
                       class="w-12 px-1.5 py-1 text-xs text-center border border-gray-200 rounded-lg focus:ring-1 focus:ring-emerald-500 bg-white font-semibold"
                       onchange="updateTotalJumlah()">
                <button type="button"
                        class="w-7 h-7 flex items-center justify-center bg-red-50 hover:bg-red-100 text-red-400 hover:text-red-600 rounded-lg transition-all"
                        onclick="removeBook(${book.id})">
                    <i class="fas fa-trash-alt text-[10px]"></i>
                </button>
            </div>
        </div>
        <div class="book-return-date-row hidden flex items-center gap-2 pt-2 border-t border-amber-100/80">
            <i class="fas fa-calendar-check text-amber-400 text-[10px] flex-shrink-0"></i>
            <span class="text-[10px] text-gray-500 font-medium whitespace-nowrap">Batas kembali:</span>
            <input type="date" name="tanggal_kembali_buku[${book.id}]" value="${defaultTgl}" required
                   ${minTgl ? `min="${minTgl}"` : ''}
                   class="flex-1 min-w-0 px-2 py-1 text-[11px] border border-amber-200 rounded-lg focus:ring-1 focus:ring-amber-400 focus:border-amber-400 bg-amber-50/40 outline-none">
            <i class="fas fa-clock text-amber-400 text-[10px] flex-shrink-0"></i>
            <input type="time" name="jam_kembali_buku[${book.id}]" value="${defaultJam}" required
                   class="w-24 px-2 py-1 text-[11px] border border-amber-200 rounded-lg focus:ring-1 focus:ring-amber-400 focus:border-amber-400 bg-amber-50/40 outline-none">
        </div>`;

    list.appendChild(item);

    const hidden = document.createElement('input');
    hidden.type = 'hidden'; hidden.name = 'buku_ids[]'; hidden.value = book.id;
    hidden.className = 'book-input'; hidden.setAttribute('data-book-id', book.id);
    document.getElementById('hiddenBookInputs').appendChild(hidden);

    count.textContent = parseInt(count.textContent) + 1;
    document.getElementById('buku_search').value = '';
    document.getElementById('bukuDropdown').classList.add('hidden');
    selectedIndex = -1;

    updateSubmitButton(); updateTotalJumlah(); updateSummary(); updateEmptyState();
    updateReturnDateVisibility();
    showNotification('Buku berhasil ditambahkan!', 'success');
}

function removeBook(bookId) {
    const item = document.querySelector(`div[data-book-id="${bookId}"]`);
    if (!item) return;
    item.style.opacity = '0';
    item.style.transform = 'translateX(12px)';
    item.style.transition = 'all .25s ease';
    setTimeout(() => {
        item.remove();
        document.querySelector(`input[data-book-id="${bookId}"]`)?.remove();
        document.querySelector(`input[name="jumlah_buku[${bookId}]"]`)?.remove();
        document.getElementById('selectedCount').textContent =
            parseInt(document.getElementById('selectedCount').textContent) - 1;
        updateSubmitButton(); updateTotalJumlah(); updateSummary(); updateEmptyState();
        updateReturnDateVisibility();
    }, 250);
}

function updateTotalJumlah() {
    let total = 0;
    document.querySelectorAll('input[name^="jumlah_buku["]').forEach(i => { total += parseInt(i.value) || 0; });
    document.getElementById('totalJumlah').textContent = total;
}

function applyDefaultDateToAll() {
    const val    = document.getElementById('tanggal_harus_kembali').value;
    const minTgl = document.getElementById('tanggal_peminjaman').value || '';
    if (!val) return;
    document.querySelectorAll('input[name^="tanggal_kembali_buku["]').forEach(inp => {
        inp.value = val;
        if (minTgl) inp.min = minTgl;
    });
}

function applyDefaultTimeToAll() {
    const val = document.getElementById('jam_kembali').value;
    if (!val) return;
    document.querySelectorAll('input[name^="jam_kembali_buku["]').forEach(inp => { inp.value = val; });
}

function updateReturnDateVisibility() {
    const count   = parseInt(document.getElementById('selectedCount').textContent);
    const rows    = document.querySelectorAll('.book-return-date-row');
    const tglCol  = document.getElementById('defaultTglCol');
    const jamCol  = document.getElementById('defaultJamCol');

    if (count >= 2) {
        // Tampilkan baris tanggal kembali per-buku
        rows.forEach(r => r.classList.remove('hidden'));
        // Sembunyikan kolom default di jadwal
        tglCol?.classList.add('hidden');
        jamCol?.classList.add('hidden');
    } else {
        // Sembunyikan baris per-buku (tidak perlu untuk 1 buku)
        rows.forEach(r => r.classList.add('hidden'));
        // Tampilkan kembali kolom default
        tglCol?.classList.remove('hidden');
        jamCol?.classList.remove('hidden');
    }
}

// ─────────────────────────────────────────────────
// Notifications
// ─────────────────────────────────────────────────
function showNotification(message, type = 'info') {
    Swal.fire({
        icon: type,
        title: { success: 'Berhasil!', error: 'Error!', warning: 'Perhatian!', info: 'Info' }[type] || 'Info',
        text: message,
        timer: type === 'error' ? 4000 : 2500,
        showConfirmButton: type === 'error',
        toast: type !== 'error',
        position: type !== 'error' ? 'top-end' : 'center',
    });
}

// ─────────────────────────────────────────────────
// Close dropdowns on outside click
// ─────────────────────────────────────────────────
document.addEventListener('click', function (e) {
    const as = document.getElementById('anggota_search');
    const ad = document.getElementById('anggotaDropdown');
    const bs = document.getElementById('buku_search');
    const bd = document.getElementById('bukuDropdown');
    if (!as.contains(e.target) && !ad.contains(e.target)) { ad.classList.add('hidden'); selectedIndex = -1; }
    if (!bs.contains(e.target) && !bd.contains(e.target)) { bd.classList.add('hidden'); selectedIndex = -1; }
});

// ─────────────────────────────────────────────────
// Form validation
// ─────────────────────────────────────────────────
function validateForm() {
    const anggotaId = document.getElementById('anggota_id').value;
    const books     = document.querySelectorAll('#hiddenBookInputs input[name="buku_ids[]"]');
    const jamKembali = document.getElementById('jam_kembali').value;

    if (!anggotaId) {
        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Pilih anggota terlebih dahulu!', confirmButtonColor: '#3b82f6' });
        return false;
    }
    if (anggotaHasOverdue) {
        Swal.fire({
            icon: 'error',
            title: 'Tidak Dapat Meminjam!',
            html: 'Anggota ini masih memiliki buku yang <b>melewati batas waktu pengembalian</b>.<br><br>Kembalikan buku tersebut terlebih dahulu sebelum dapat meminjam buku baru.',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Mengerti'
        });
        return false;
    }
    if (books.length === 0) {
        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Pilih minimal 1 buku!', confirmButtonColor: '#3b82f6' });
        return false;
    }
    if ([...books].filter(i => i.value && !isNaN(i.value)).length === 0) {
        Swal.fire({ icon: 'error', title: 'Data Tidak Valid', text: 'Tidak ada buku valid!', confirmButtonColor: '#3b82f6' });
        return false;
    }
    if (!jamKembali) {
        Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Jam kembali default wajib diisi!', confirmButtonColor: '#3b82f6' });
        document.getElementById('jam_kembali').focus();
        return false;
    }

    // Validasi tanggal & jam kembali per buku (hanya jika 2+ buku dipilih)
    const bookCount = parseInt(document.getElementById('selectedCount').textContent);
    if (bookCount >= 2) {
        let allDatesOk = true;
        document.querySelectorAll('input[name^="tanggal_kembali_buku["]').forEach(inp => { if (!inp.value) allDatesOk = false; });
        document.querySelectorAll('input[name^="jam_kembali_buku["]').forEach(inp => { if (!inp.value) allDatesOk = false; });
        if (!allDatesOk) {
            Swal.fire({ icon: 'warning', title: 'Data Belum Lengkap', text: 'Isi tanggal dan jam kembali untuk setiap buku yang dipilih!', confirmButtonColor: '#3b82f6' });
            return false;
        }
    }

    return validateTanggalKembali();
}

// ─────────────────────────────────────────────────
// Debug helpers
// ─────────────────────────────────────────────────
function toggleManualInput() {
    const m = document.getElementById('manualBookInput');
    m.style.display = m.style.display === 'none' ? 'block' : 'none';
}

function addManualBook() {
    const id = document.getElementById('manual_book_id').value;
    if (!id || isNaN(id)) return;
    if (document.querySelector(`input[data-book-id="${id}"]`)) return;
    const c = document.getElementById('hiddenBookInputs');
    const bi = document.createElement('input'); bi.type = 'hidden'; bi.name = 'buku_ids[]'; bi.value = id; bi.setAttribute('data-book-id', id); c.appendChild(bi);
    const qi = document.createElement('input'); qi.type = 'hidden'; qi.name = `jumlah_buku[${id}]`; qi.value = '1'; c.appendChild(qi);
    document.getElementById('selectedCount').textContent = parseInt(document.getElementById('selectedCount').textContent) + 1;
    document.getElementById('manual_book_id').value = '';
    updateSubmitButton(); updateSummary(); updateEmptyState();
}

updateSubmitButton();
</script>
@endsection
