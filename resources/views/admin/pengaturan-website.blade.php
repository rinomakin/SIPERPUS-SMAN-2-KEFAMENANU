@extends('layouts.admin')

@section('title', 'Pengaturan Website')

@push('styles')
<style>
/* ── Override inline styles untuk dark mode ── */

/* Header back button & badge */
html[data-theme="dark"] .mb-8 a.rounded-xl[style],
html[data-theme="dark"] .mb-8 div.hidden.sm\:flex[style] {
    background: rgba(30,41,59,0.85) !important;
    border-color: rgba(148,163,184,0.2) !important;
}
html[data-theme="dark"] .mb-8 div.hidden.sm\:flex span { color: #94a3b8 !important; }

/* Card containers (glass white) */
html[data-theme="dark"] #formPengaturan > .mb-8.rounded-2xl,
html[data-theme="dark"] #formPengaturan > .grid > .rounded-2xl {
    background: rgba(22, 32, 51, 0.92) !important;
    border-color: rgba(99,102,241,0.18) !important;
    box-shadow: 0 8px 32px rgba(0,0,0,0.35) !important;
}

/* Card body inner padding area */
html[data-theme="dark"] #formPengaturan .p-6 { background: transparent !important; }

/* Logo & Favicon preview containers */
html[data-theme="dark"] #logoPreviewContainer,
html[data-theme="dark"] #faviconPreviewContainer {
    background: rgba(99, 60, 180, 0.12) !important;
    border-color: rgba(124,58,237,0.35) !important;
}

/* Upload file buttons (violet) */
html[data-theme="dark"] #formPengaturan button.text-violet-600 {
    background: rgba(124,58,237,0.2) !important;
    border-color: rgba(124,58,237,0.4) !important;
    color: #c4b5fd !important;
}
html[data-theme="dark"] #formPengaturan button.text-violet-600:hover {
    background: rgba(124,58,237,0.3) !important;
}

/* Form inputs & textareas (override violet/emerald inline bg) */
html[data-theme="dark"] #formPengaturan input[style],
html[data-theme="dark"] #formPengaturan textarea[style] {
    background: #1e293b !important;
    border-color: #334155 !important;
    color: #f1f5f9 !important;
}
html[data-theme="dark"] #formPengaturan input[style]::placeholder,
html[data-theme="dark"] #formPengaturan textarea[style]::placeholder {
    color: #475569 !important;
}
html[data-theme="dark"] #formPengaturan input[style]:focus,
html[data-theme="dark"] #formPengaturan textarea[style]:focus {
    border-color: #7c3aed !important;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.15) !important;
}

/* Kembali button */
html[data-theme="dark"] #formPengaturan button.text-gray-600 {
    background: rgba(30,41,59,0.85) !important;
    border-color: rgba(148,163,184,0.2) !important;
    color: #94a3b8 !important;
}
html[data-theme="dark"] #formPengaturan button.text-gray-600:hover {
    background: rgba(51,65,85,0.9) !important;
}

/* Label text */
html[data-theme="dark"] #formPengaturan label.text-sm.font-semibold { color: #cbd5e1 !important; }
html[data-theme="dark"] #formPengaturan p.text-xs.text-gray-400     { color: #475569 !important; }
html[data-theme="dark"] #formPengaturan p.text-xs.text-gray-500     { color: #475569 !important; }

/* Info text di action bar */
html[data-theme="dark"] .mt-8 p.text-xs.text-gray-400 { color: #475569 !important; }

/* ── Read-only mode ── */
.form-readonly input:not([type="file"]),
.form-readonly textarea {
    background: #f8fafc !important;
    border-color: #e2e8f0 !important;
    color: #64748b !important;
    cursor: not-allowed;
    pointer-events: none;
}
.form-readonly input:not([type="file"])::placeholder,
.form-readonly textarea::placeholder { color: #94a3b8 !important; }
.form-readonly .upload-btn {
    opacity: 0.5;
    cursor: not-allowed !important;
    pointer-events: none;
}
.form-readonly .upload-overlay { display: none !important; }
html[data-theme="dark"] .form-readonly input:not([type="file"]),
html[data-theme="dark"] .form-readonly textarea {
    background: #1e293b !important;
    border-color: #1e293b !important;
    color: #64748b !important;
}
</style>
@endpush

@push('scripts')
<script>
// ─── Toggle Edit Mode ──────────────────────────────────────────────────
function toggleEdit() {
    const form = document.getElementById('formPengaturan');
    const isReadonly = form.classList.contains('form-readonly');

    form.classList.toggle('form-readonly');

    // Toggle disabled on all form controls
    form.querySelectorAll('input, textarea').forEach(el => {
        if (el.type !== 'hidden') {
            el.disabled = isReadonly ? false : true;
        }
    });
    // Toggle disabled on upload trigger buttons
    form.querySelectorAll('.upload-btn').forEach(el => {
        el.disabled = isReadonly ? false : true;
    });

    // Toggle button visibility
    document.getElementById('btnEdit').classList.toggle('hidden');
    document.getElementById('btnSimpan').classList.toggle('hidden');
}

document.addEventListener('DOMContentLoaded', function () {
    // Ensure initial state: form is readonly
    const form = document.getElementById('formPengaturan');
    form.classList.add('form-readonly');
});
</script>
@endpush

@section('content')
<div class="min-h-screen py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <a href="{{ route('admin.dashboard') }}"
                       class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-300 hover:scale-105"
                       style="background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                        <i class="fas fa-arrow-left text-violet-600"></i>
                    </a>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800">Pengaturan Website</h1>
                        <p class="text-sm text-gray-500 mt-0.5">Kelola informasi dan tampilan website perpustakaan</p>
                    </div>
                </div>
                <div class="hidden sm:flex items-center gap-2 px-4 py-2 rounded-xl text-sm"
                     style="background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.3);">
                    <i class="fas fa-cog text-violet-500"></i>
                    <span class="text-gray-600">Pengaturan</span>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.pengaturan.update') }}" method="POST" enctype="multipart/form-data" id="formPengaturan">
            @csrf

            {{-- Branding Section: Logo & Favicon --}}
            <div class="mb-8 rounded-2xl overflow-hidden"
                 style="background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 8px 32px rgba(124,58,237,0.08);">
                <div class="px-6 py-4" style="background: linear-gradient(135deg, #7c3aed, #a855f7);">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                            <i class="fas fa-palette text-white"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-semibold">Branding & Identitas</h3>
                            <p class="text-violet-200 text-xs">Logo dan favicon website perpustakaan</p>
                        </div>
                    </div>
                </div>

                <div class="p-6">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        {{-- Logo Upload --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-image text-violet-500 mr-1.5"></i>Logo Website
                            </label>
                            <div class="flex flex-col items-center gap-4">
                                {{-- Logo Preview --}}
                                <div class="relative group">
                                    <div id="logoPreviewContainer"
                                         class="w-40 h-40 rounded-2xl flex items-center justify-center overflow-hidden transition-all duration-300"
                                         style="background: rgba(124,58,237,0.05); border: 2px dashed rgba(124,58,237,0.2);">
                                        @if($pengaturan->logo)
                                            <img id="logoPreview" src="{{ asset($pengaturan->logo) }}" alt="Logo"
                                                 class="w-full h-full object-contain p-2"
                                                 onerror="this.style.display='none'; document.getElementById('logoPlaceholder').style.display='flex';">
                                            <div id="logoPlaceholder" class="hidden flex-col items-center gap-2 text-violet-300">
                                                <i class="fas fa-image text-3xl"></i>
                                                <span class="text-xs">Tidak ditemukan</span>
                                            </div>
                                        @else
                                            <img id="logoPreview" src="" alt="Logo" class="w-full h-full object-contain p-2 hidden">
                                            <div id="logoPlaceholder" class="flex flex-col items-center gap-2 text-violet-300">
                                                <i class="fas fa-image text-3xl"></i>
                                                <span class="text-xs">Belum ada logo</span>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Hover overlay --}}
                                    <label for="logo"
                                           class="absolute inset-0 rounded-2xl flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer">
                                        <div class="text-white text-center">
                                            <i class="fas fa-camera text-xl mb-1"></i>
                                            <p class="text-xs font-medium">Ganti Logo</p>
                                        </div>
                                    </label>
                                </div>
                                <input type="file" name="logo" id="logo" accept="image/*" class="hidden" onchange="previewImage(this, 'logoPreview', 'logoPlaceholder')" disabled>
                                <button type="button" onclick="document.getElementById('logo').click()"
                                        class="upload-btn px-4 py-2 rounded-xl text-sm font-medium text-violet-600 transition-all duration-200 hover:scale-105"
                                        style="background: rgba(124,58,237,0.08); border: 1px solid rgba(124,58,237,0.15);" disabled>
                                    <i class="fas fa-upload mr-1.5"></i>Pilih File Logo
                                </button>
                                <p class="text-xs text-gray-400">Format: JPG, PNG, GIF, SVG. Maks 2MB</p>
                            </div>
                        </div>

                        {{-- Favicon Upload --}}
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-3">
                                <i class="fas fa-star text-violet-500 mr-1.5"></i>Favicon
                            </label>
                            <div class="flex flex-col items-center gap-4">
                                {{-- Favicon Preview --}}
                                <div class="relative group">
                                    <div id="faviconPreviewContainer"
                                         class="w-40 h-40 rounded-2xl flex items-center justify-center overflow-hidden transition-all duration-300"
                                         style="background: rgba(124,58,237,0.05); border: 2px dashed rgba(124,58,237,0.2);">
                                        @if($pengaturan->favicon)
                                            <img id="faviconPreview" src="{{ asset($pengaturan->favicon) }}" alt="Favicon"
                                                 class="w-20 h-20 object-contain"
                                                 onerror="this.style.display='none'; document.getElementById('faviconPlaceholder').style.display='flex';">
                                            <div id="faviconPlaceholder" class="hidden flex-col items-center gap-2 text-violet-300">
                                                <i class="fas fa-star text-3xl"></i>
                                                <span class="text-xs">Tidak ditemukan</span>
                                            </div>
                                        @else
                                            <img id="faviconPreview" src="" alt="Favicon" class="w-20 h-20 object-contain hidden">
                                            <div id="faviconPlaceholder" class="flex flex-col items-center gap-2 text-violet-300">
                                                <i class="fas fa-star text-3xl"></i>
                                                <span class="text-xs">Belum ada favicon</span>
                                            </div>
                                        @endif
                                    </div>
                                    {{-- Hover overlay --}}
                                    <label for="favicon"
                                           class="absolute inset-0 rounded-2xl flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer">
                                        <div class="text-white text-center">
                                            <i class="fas fa-camera text-xl mb-1"></i>
                                            <p class="text-xs font-medium">Ganti Favicon</p>
                                        </div>
                                    </label>
                                </div>
                                <input type="file" name="favicon" id="favicon" accept="image/*,.ico" class="hidden" onchange="previewImage(this, 'faviconPreview', 'faviconPlaceholder')" disabled>
                                <button type="button" onclick="document.getElementById('favicon').click()"
                                        class="upload-btn px-4 py-2 rounded-xl text-sm font-medium text-violet-600 transition-all duration-200 hover:scale-105"
                                        style="background: rgba(124,58,237,0.08); border: 1px solid rgba(124,58,237,0.15);" disabled>
                                    <i class="fas fa-upload mr-1.5"></i>Pilih File Favicon
                                </button>
                                <p class="text-xs text-gray-400">Format: JPG, PNG, ICO, SVG. Maks 1MB</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                {{-- Informasi Website Card --}}
                <div class="rounded-2xl overflow-hidden"
                     style="background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 8px 32px rgba(124,58,237,0.08);">
                    <div class="px-6 py-4" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-globe text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">Informasi Website</h3>
                                <p class="text-blue-200 text-xs">Nama dan deskripsi website</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Nama Website --}}
                        <div>
                            <label for="nama_website" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-tag text-blue-500 mr-1.5"></i>Nama Website
                            </label>
                            <input type="text" name="nama_website" id="nama_website" value="{{ $pengaturan->nama_website }}"
                                   class="w-full px-4 py-3 rounded-xl text-sm transition-all duration-200 focus:ring-2 focus:ring-violet-400 focus:outline-none"
                                   style="background: rgba(124,58,237,0.03); border: 1px solid rgba(124,58,237,0.12);"
                                   placeholder="Masukkan nama website" disabled>
                        </div>

                        {{-- Deskripsi Website --}}
                        <div>
                            <label for="deskripsi_website" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-align-left text-blue-500 mr-1.5"></i>Deskripsi Website
                            </label>
                            <textarea name="deskripsi_website" id="deskripsi_website" rows="4"
                                      class="w-full px-4 py-3 rounded-xl text-sm transition-all duration-200 focus:ring-2 focus:ring-violet-400 focus:outline-none resize-none"
                                      style="background: rgba(124,58,237,0.03); border: 1px solid rgba(124,58,237,0.12);"
                                      placeholder="Masukkan deskripsi website" disabled>{{ $pengaturan->deskripsi_website }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Informasi Sekolah Card --}}
                <div class="rounded-2xl overflow-hidden"
                     style="background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.3); box-shadow: 0 8px 32px rgba(124,58,237,0.08);">
                    <div class="px-6 py-4" style="background: linear-gradient(135deg, #10b981, #059669);">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                                <i class="fas fa-school text-white"></i>
                            </div>
                            <div>
                                <h3 class="text-white font-semibold">Informasi Sekolah</h3>
                                <p class="text-emerald-200 text-xs">Data dan kontak sekolah</p>
                            </div>
                        </div>
                    </div>

                    <div class="p-6 space-y-5">
                        {{-- Alamat --}}
                        <div>
                            <label for="alamat_sekolah" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-map-marker-alt text-emerald-500 mr-1.5"></i>Alamat Sekolah
                            </label>
                            <textarea name="alamat_sekolah" id="alamat_sekolah" rows="2"
                                      class="w-full px-4 py-3 rounded-xl text-sm transition-all duration-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none resize-none"
                                      style="background: rgba(16,185,129,0.03); border: 1px solid rgba(16,185,129,0.15);"
                                      placeholder="Masukkan alamat sekolah" disabled>{{ $pengaturan->alamat_sekolah }}</textarea>
                        </div>

                        {{-- Telepon & Email --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="telepon_sekolah" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    <i class="fas fa-phone text-emerald-500 mr-1.5"></i>Telepon
                                </label>
                                <input type="text" name="telepon_sekolah" id="telepon_sekolah" value="{{ $pengaturan->telepon_sekolah }}"
                                       class="w-full px-4 py-3 rounded-xl text-sm transition-all duration-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                       style="background: rgba(16,185,129,0.03); border: 1px solid rgba(16,185,129,0.15);"
                                       placeholder="08xxx" disabled>
                            </div>
                            <div>
                                <label for="email_sekolah" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                    <i class="fas fa-envelope text-emerald-500 mr-1.5"></i>Email
                                </label>
                                <input type="email" name="email_sekolah" id="email_sekolah" value="{{ $pengaturan->email_sekolah }}"
                                       class="w-full px-4 py-3 rounded-xl text-sm transition-all duration-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                       style="background: rgba(16,185,129,0.03); border: 1px solid rgba(16,185,129,0.15);"
                                       placeholder="email@sekolah.sch.id" disabled>
                            </div>
                        </div>

                        {{-- Kepala Sekolah --}}
                        <div>
                            <label for="nama_kepala_sekolah" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-user-tie text-emerald-500 mr-1.5"></i>Kepala Sekolah
                            </label>
                            <input type="text" name="nama_kepala_sekolah" id="nama_kepala_sekolah" value="{{ $pengaturan->nama_kepala_sekolah }}"
                                   class="w-full px-4 py-3 rounded-xl text-sm transition-all duration-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                   style="background: rgba(16,185,129,0.03); border: 1px solid rgba(16,185,129,0.15);"
                                   placeholder="Nama kepala sekolah" disabled>
                        </div>

                        {{-- Jam Operasional --}}
                        <div>
                            <label for="jam_operasional" class="block text-sm font-semibold text-gray-700 mb-1.5">
                                <i class="fas fa-clock text-emerald-500 mr-1.5"></i>Jam Operasional
                            </label>
                            <input type="text" name="jam_operasional" id="jam_operasional" value="{{ $pengaturan->jam_operasional }}"
                                   class="w-full px-4 py-3 rounded-xl text-sm transition-all duration-200 focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                                   style="background: rgba(16,185,129,0.03); border: 1px solid rgba(16,185,129,0.15);"
                                   placeholder="Senin - Jumat, 08:00 - 15:00" disabled>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="mt-8 flex items-center justify-between">
                <p class="text-xs text-gray-400 hidden sm:block">
                    <i class="fas fa-info-circle mr-1"></i>Klik tombol Edit untuk mengubah pengaturan
                </p>
                <div class="flex items-center gap-3 ml-auto">
                    <button type="button" onclick="window.history.back()"
                            class="px-5 py-2.5 rounded-xl text-sm font-medium text-gray-600 transition-all duration-200 hover:scale-105"
                            style="background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); border: 1px solid rgba(0,0,0,0.08);">
                        <i class="fas fa-arrow-left mr-1.5"></i>Kembali
                    </button>
                    @if(Auth::user()->hasPermission('pengaturan.edit'))
                    <button type="button" id="btnEdit" onclick="toggleEdit()"
                            class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg flex items-center gap-2"
                            style="background: linear-gradient(135deg, #3b82f6, #6366f1); box-shadow: 0 4px 15px rgba(59,130,246,0.3);">
                        <i class="fas fa-pen"></i>
                        <span>Edit Pengaturan</span>
                    </button>
                    <button type="submit" id="btnSimpan"
                            class="px-6 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg flex items-center gap-2 hidden"
                            style="background: linear-gradient(135deg, #7c3aed, #a855f7); box-shadow: 0 4px 15px rgba(124,58,237,0.3);">
                        <i class="fas fa-save"></i>
                        <span>Simpan Pengaturan</span>
                    </button>
                    @endif
                </div>
            </div>
        </form>
    </div>
</div>

{{-- SweetAlert for success/error --}}
@if(session('success'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session("success") }}',
        showConfirmButton: false,
        timer: 2500,
        timerProgressBar: true,
        customClass: { popup: 'rounded-2xl' }
    });
});
</script>
@endif

@if(session('error'))
<script>
document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session("error") }}',
        confirmButtonColor: '#7c3aed',
        customClass: { popup: 'rounded-2xl' }
    });
});
</script>
@endif

@if($errors->any())
<script>
document.addEventListener('DOMContentLoaded', function() {
    let errorList = '';
    @foreach($errors->all() as $error)
        errorList += '{{ $error }}\n';
    @endforeach
    Swal.fire({
        icon: 'warning',
        title: 'Validasi Gagal',
        text: errorList.trim(),
        confirmButtonColor: '#7c3aed',
        customClass: { popup: 'rounded-2xl' }
    });
});
</script>
@endif

<script>
function previewImage(input, previewId, placeholderId) {
    const preview = document.getElementById(previewId);
    const placeholder = document.getElementById(placeholderId);

    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate size
        const maxSize = previewId === 'logoPreview' ? 2 * 1024 * 1024 : 1024 * 1024;
        if (file.size > maxSize) {
            Swal.fire({
                icon: 'warning',
                title: 'File Terlalu Besar',
                text: `Ukuran file maksimal ${previewId === 'logoPreview' ? '2MB' : '1MB'}`,
                confirmButtonColor: '#7c3aed',
                customClass: { popup: 'rounded-2xl' }
            });
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            preview.style.display = '';
            placeholder.style.display = 'none';
            placeholder.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
}

// Submit with loading state
document.getElementById('formPengaturan').addEventListener('submit', function() {
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Menyimpan...</span>';
});
</script>
@endsection
