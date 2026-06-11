@extends('layouts.admin')

@section('title', 'Edit Anggota')
@section('page-title', 'Edit Anggota')

@push('styles')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .fade-in {
        opacity: 0;
        transform: translateY(16px);
        animation: slideUp 0.5s ease forwards;
    }
    .fade-in:nth-child(1) { animation-delay: 0.05s; }
    .fade-in:nth-child(2) { animation-delay: 0.1s; }
    .fade-in:nth-child(3) { animation-delay: 0.15s; }
    .fade-in:nth-child(4) { animation-delay: 0.2s; }
    .fade-in:nth-child(5) { animation-delay: 0.25s; }
    .fade-in:nth-child(6) { animation-delay: 0.3s; }
    @keyframes slideUp {
        to { opacity: 1; transform: translateY(0); }
    }

    .form-input {
        width: 100%;
        padding: 6px 10px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        font-size: 10px;
        color: #1e293b;
        background: #f8fafc;
        transition: all 0.2s ease;
    }
    .form-input:focus {
        outline: none;
        border-color: #3b82f6;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .form-input.is-invalid {
        border-color: #ef4444;
        background: #fef2f2;
    }
    .form-input.is-invalid:focus {
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .form-label {
        display: block;
        font-size: 10px;
        font-weight: 600;
        color: #475569;
        margin-bottom: 3px;
        letter-spacing: 0.01em;
    }
    .form-label .required {
        color: #ef4444;
        margin-left: 2px;
    }
    .form-hint {
        font-size: 10px;
        color: #94a3b8;
        margin-top: 2px;
    }
    .form-error {
        font-size: 10px;
        color: #ef4444;
        margin-top: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .section-title {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 10px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 12px;
        padding-bottom: 6px;
        border-bottom: 2px solid #f1f5f9;
    }
    .section-title .icon-box {
        width: 24px;
        height: 24px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        color: white;
        flex-shrink: 0;
    }

    .photo-upload-area {
        width: 100px;
        height: 100px;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
        overflow: hidden;
        position: relative;
    }
    .photo-upload-area:hover {
        border-color: #3b82f6;
        background: #eff6ff;
    }
    .photo-upload-area.has-photo {
        border-style: solid;
        border-color: #e2e8f0;
    }
    .photo-upload-area img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .photo-upload-area .upload-placeholder {
        text-align: center;
        color: #94a3b8;
    }
    .photo-upload-area .upload-placeholder i {
        font-size: 1.2rem;
        margin-bottom: 4px;
        display: block;
    }
    .photo-upload-area .upload-placeholder span {
        font-size: 10px;
        font-weight: 500;
    }
    .photo-upload-area .photo-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.2s;
    }
    .photo-upload-area:hover .photo-overlay {
        opacity: 1;
    }
    .photo-upload-area .photo-overlay i {
        color: white;
        font-size: 1rem;
    }

    .info-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 600;
    }

    .jenis-anggota-option {
        flex: 1;
        padding: 8px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
    }
    .jenis-anggota-option:hover {
        border-color: #93c5fd;
        background: #eff6ff;
    }
    .jenis-anggota-option.active {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .jenis-anggota-option .option-icon {
        font-size: 0.9rem;
        margin-bottom: 2px;
        display: block;
    }
    .jenis-anggota-option .option-label {
        font-size: 10px;
        font-weight: 600;
        color: #334155;
    }

    .gender-option {
        flex: 1;
        padding: 6px 12px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
    }
    .gender-option:hover {
        border-color: #93c5fd;
        background: #eff6ff;
    }
    .gender-option.active {
        border-color: #3b82f6;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    .gender-option .option-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
    }
    .gender-option .option-text {
        font-size: 10px;
        font-weight: 600;
        color: #334155;
    }

    .status-option {
        flex: 1;
        padding: 6px 10px;
        border: 1.5px solid #e2e8f0;
        border-radius: 8px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s ease;
        background: #f8fafc;
    }
    .status-option:hover {
        border-color: #93c5fd;
    }
    .status-option.active-aktif {
        border-color: #22c55e;
        background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
        box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.1);
    }
    .status-option.active-nonaktif {
        border-color: #ef4444;
        background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
    }
    .status-option.active-ditangguhkan {
        border-color: #f59e0b;
        background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.1);
    }
    .status-option .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 6px;
    }
    .status-option .status-label {
        font-size: 10px;
        font-weight: 600;
        color: #334155;
    }

    .camera-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.85);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 16px;
    }
    .camera-overlay.active {
        display: flex;
    }
    .camera-modal {
        background: #1e293b;
        border-radius: 16px;
        overflow: hidden;
        max-width: 480px;
        width: 100%;
    }
    .camera-modal video {
        width: 100%;
        display: block;
        aspect-ratio: 4/3;
        object-fit: cover;
        background: #000;
    }
    .camera-modal .camera-toolbar {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 24px;
        padding: 12px 16px;
    }
    .camera-modal .camera-toolbar button {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 16px;
        transition: transform 0.15s;
    }
    .camera-modal .camera-toolbar button:active {
        transform: scale(0.9);
    }
    .camera-modal .camera-toolbar .btn-capture {
        width: 56px;
        height: 56px;
        background: white;
        color: #1e293b;
        font-size: 20px;
        box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
    }
    .camera-modal .camera-toolbar .btn-close {
        background: rgba(255,255,255,0.15);
        color: white;
    }
    .camera-modal .camera-toolbar .btn-flip {
        background: rgba(255,255,255,0.15);
        color: white;
    }
</style>
@endpush

@section('content')
<div class="max-w-5xl mx-auto">

    {{-- Alert Sukses --}}
    @if(session('success'))
    <div class="mb-5 p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-1.5 fade-in">
        <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-check text-green-600 text-[10px]"></i>
        </div>
        <p class="text-[10px] text-green-800 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Alert Error --}}
    @if(session('error'))
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-1.5 fade-in">
        <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-exclamation-triangle text-red-600 text-[10px]"></i>
        </div>
        <p class="text-[10px] text-red-800 font-medium">{{ session('error') }}</p>
    </div>
    @endif

    {{-- Validation Errors --}}
    @if($errors->any())
    <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl fade-in">
        <div class="flex items-start gap-1.5">
            <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                <i class="fas fa-exclamation-triangle text-red-600 text-[10px]"></i>
            </div>
            <div>
                <h4 class="text-[10px] font-semibold text-red-800 mb-1">Terdapat kesalahan pada form</h4>
                <ul class="text-[10px] text-red-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                    <li class="flex items-center gap-1.5">
                        <i class="fas fa-circle text-red-400" style="font-size: 4px;"></i>
                        {{ $error }}
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Member Info Header --}}
    <div class="glass-card rounded-xl shadow-sm border border-gray-200 p-3 mb-5 fade-in">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1.5">
            <div class="flex items-center gap-1.5">
                <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-user-edit text-blue-600"></i>
                </div>
                <div>
                    <h3 class="text-[10px] font-bold text-gray-800">{{ $anggota->nama_lengkap }}</h3>
                    <p class="text-[10px] text-gray-500">No. Anggota: {{ $anggota->nomor_anggota }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <span class="info-badge" style="background: #f0f9ff; color: #0369a1; border: 1px solid #bae6fd;">
                    <i class="fas fa-barcode text-[10px]"></i> {{ $anggota->barcode_anggota }}
                </span>
                @if($anggota->status == 'aktif')
                    <span class="info-badge" style="background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;">
                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Aktif
                    </span>
                @elseif($anggota->status == 'nonaktif')
                    <span class="info-badge" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca;">
                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span> Nonaktif
                    </span>
                @else
                    <span class="info-badge" style="background: #fffbeb; color: #d97706; border: 1px solid #fde68a;">
                        <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 inline-block"></span> Ditangguhkan
                    </span>
                @endif
            </div>
        </div>
    </div>

    <form method="POST" action="{{ route('anggota.update', $anggota->id) }}" enctype="multipart/form-data" id="editForm">
        @csrf
        @method('PUT')

        {{-- Section 1: Identitas & Foto --}}
        <div class="glass-card rounded-xl shadow-sm border border-gray-200 p-3 mb-5 fade-in">
            <div class="section-title">
                <div class="icon-box" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <i class="fas fa-id-card"></i>
                </div>
                Identitas Anggota
            </div>

            <div class="flex flex-col md:flex-row gap-1.5">
                {{-- Photo Upload --}}
                <div class="flex flex-col items-center gap-2">
                    <div class="photo-upload-area has-photo" id="photoArea" onclick="document.getElementById('foto').click()" data-default-male="{{ asset('images/template_foto_laki_laki.jpg') }}" data-default-female="{{ asset('images/teplate_foto_perpempuan.jpg') }}">
                        @if($anggota->foto)
                            <img id="photoPreview" src="{{ asset('storage/anggota/' . $anggota->foto) }}" alt="Foto {{ $anggota->nama_lengkap }}">
                        @else
                            <img id="photoPreview" src="{{ $anggota->jenis_kelamin == 'Laki-laki' ? asset('images/template_foto_laki_laki.jpg') : asset('images/teplate_foto_perpempuan.jpg') }}" alt="Foto Default">
                        @endif
                            <div class="photo-overlay">
                                <i class="fas fa-camera"></i>
                            </div>
                    </div>
                    <input type="file" name="foto" id="foto" accept="image/jpeg,image/png,image/jpg" class="hidden" onchange="previewPhoto(this)">
                    <div class="flex items-center gap-1.5">
                        <button type="button" onclick="document.getElementById('foto').click()" class="px-2.5 py-1 text-[10px] font-medium bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 transition-colors">
                            <i class="fas fa-upload mr-1"></i>Upload
                        </button>
                        <button type="button" onclick="openCamera()" class="px-2.5 py-1 text-[10px] font-medium bg-purple-50 text-purple-700 rounded-lg hover:bg-purple-100 transition-colors">
                            <i class="fas fa-camera mr-1"></i>Kamera
                        </button>
                    </div>
                    <span class="text-[10px] text-gray-400">JPG/PNG, maks 2MB</span>
                    @error('foto')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Identity Fields --}}
                <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-2">
                    {{-- Barcode --}}
                    <div>
                        <label class="form-label">Barcode Anggota</label>
                        <div class="flex gap-2">
                            <input type="text" name="barcode_anggota" id="barcode_anggota"
                                   class="form-input flex-1 @error('barcode_anggota') is-invalid @enderror"
                                   value="{{ old('barcode_anggota') }}"
                                   placeholder="Kosongkan jika tidak diubah">
                            <button type="button" onclick="generateBarcode()"
                                    class="px-3.5 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors flex items-center gap-1.5 text-[10px] font-medium whitespace-nowrap">
                                <i class="fas fa-sync-alt text-[10px]"></i> Generate
                            </button>
                        </div>
                        <span class="form-hint">Saat ini: {{ $anggota->barcode_anggota }}. Kosongkan jika tidak ingin mengubah.</span>
                        @error('barcode_anggota')
                            <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Nama Lengkap --}}
                    <div>
                        <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="required">*</span></label>
                        <input type="text" name="nama_lengkap" id="nama_lengkap"
                               class="form-input @error('nama_lengkap') is-invalid @enderror"
                               value="{{ old('nama_lengkap', $anggota->nama_lengkap) }}"
                               placeholder="Masukkan nama lengkap" required>
                        @error('nama_lengkap')
                            <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Jenis Kelamin --}}
                    <div>
                        <label class="form-label">Jenis Kelamin <span class="required">*</span></label>
                        <input type="hidden" name="jenis_kelamin" id="jenis_kelamin" value="{{ old('jenis_kelamin', $anggota->jenis_kelamin) }}" required>
                        <div class="flex gap-1.5">
                            <div class="gender-option {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Laki-laki' ? 'active' : '' }}" onclick="selectGender('Laki-laki', this)">
                                <div class="option-icon" style="background: #eff6ff; color: #3b82f6;">
                                    <i class="fas fa-mars"></i>
                                </div>
                                <span class="option-text">Laki-laki</span>
                            </div>
                            <div class="gender-option {{ old('jenis_kelamin', $anggota->jenis_kelamin) == 'Perempuan' ? 'active' : '' }}" onclick="selectGender('Perempuan', this)">
                                <div class="option-icon" style="background: #fdf2f8; color: #ec4899;">
                                    <i class="fas fa-venus"></i>
                                </div>
                                <span class="option-text">Perempuan</span>
                            </div>
                        </div>
                        @error('jenis_kelamin')
                            <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>

                    {{-- Tanggal Lahir --}}
                    <div>
                        <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" id="tanggal_lahir"
                               class="form-input @error('tanggal_lahir') is-invalid @enderror"
                               value="{{ old('tanggal_lahir', $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('Y-m-d') : '') }}">
                        @error('tanggal_lahir')
                            <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Jenis Anggota --}}
        <div class="glass-card rounded-xl shadow-sm border border-gray-200 p-3 mb-5 fade-in">
            <div class="section-title">
                <div class="icon-box" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <i class="fas fa-users"></i>
                </div>
                Kategori Anggota
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
                {{-- Jenis Anggota --}}
                <div>
                    <label class="form-label">Jenis Anggota <span class="required">*</span></label>
                    <input type="hidden" name="jenis_anggota" id="jenis_anggota" value="{{ old('jenis_anggota', $anggota->jenis_anggota) }}" required>
                    <div class="flex gap-1.5">
                        <div class="jenis-anggota-option {{ old('jenis_anggota', $anggota->jenis_anggota) == 'siswa' ? 'active' : '' }}" onclick="selectJenisAnggota('siswa', this)">
                            <span class="option-icon"><i class="fas fa-user-graduate"></i></span>
                            <span class="option-label">Siswa</span>
                        </div>
                        <div class="jenis-anggota-option {{ old('jenis_anggota', $anggota->jenis_anggota) == 'guru' ? 'active' : '' }}" onclick="selectJenisAnggota('guru', this)">
                            <span class="option-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                            <span class="option-label">Guru</span>
                        </div>
                        <div class="jenis-anggota-option {{ old('jenis_anggota', $anggota->jenis_anggota) == 'staff' ? 'active' : '' }}" onclick="selectJenisAnggota('staff', this)">
                            <span class="option-icon"><i class="fas fa-user-tie"></i></span>
                            <span class="option-label">Staff</span>
                        </div>
                    </div>
                    @error('jenis_anggota')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 3: Kontak & Alamat --}}
        <div class="glass-card rounded-xl shadow-sm border border-gray-200 p-3 mb-5 fade-in">
            <div class="section-title">
                <div class="icon-box" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="fas fa-address-book"></i>
                </div>
                Kontak & Alamat
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2 mb-2">
                {{-- Nomor Telepon --}}
                <div>
                    <label for="nomor_telepon" class="form-label">Nomor Telepon <span class="required">*</span></label>
                    <div class="relative">
                        <!-- <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px]"><i class="fas fa-phone"></i></span> -->
                        <input type="text" name="nomor_telepon" id="nomor_telepon"
                               class="form-input pl-9 @error('nomor_telepon') is-invalid @enderror"
                               value="{{ old('nomor_telepon', $anggota->nomor_telepon) }}"
                               placeholder="08xxxxxxxxxx" required
                               oninput="this.value = this.value.replace(/[^0-9+\-\s]/g, '')">
                    </div>
                    @error('nomor_telepon')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="form-label">Email</label>
                    <div class="relative">
                        <!-- <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px]"><i class="fas fa-envelope"></i></span> -->
                        <input type="email" name="email" id="email"
                               class="form-input pl-9 @error('email') is-invalid @enderror"
                               value="{{ old('email', $anggota->email) }}"
                               placeholder="contoh@email.com">
                    </div>
                    @error('email')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>

            {{-- Alamat --}}
            <div>
                <label for="alamat" class="form-label">Alamat <span class="required">*</span></label>
                <textarea name="alamat" id="alamat" rows="3"
                          class="form-input @error('alamat') is-invalid @enderror"
                          placeholder="Masukkan alamat lengkap" required>{{ old('alamat', $anggota->alamat) }}</textarea>
                @error('alamat')
                    <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                @enderror
            </div>
        </div>

        {{-- Section 4: Informasi Sekolah --}}
        <div class="glass-card rounded-xl shadow-sm border border-gray-200 p-3 mb-5 fade-in">
            <div class="section-title">
                <div class="icon-box" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="fas fa-school"></i>
                </div>
                Informasi Sekolah
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                {{-- Kelas --}}
                <div>
                    <label for="kelas_id" class="form-label">Kelas</label>
                    <select name="kelas_id" id="kelas_id"
                            class="form-input @error('kelas_id') is-invalid @enderror">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($kelas as $k)
                            <option value="{{ $k->id }}" {{ old('kelas_id', $anggota->kelas_id) == $k->id ? 'selected' : '' }}>
                                {{ $k->nama_kelas }} - {{ $k->jurusan->nama_jurusan }}
                            </option>
                        @endforeach
                    </select>
                    <span class="form-hint">Wajib diisi jika jenis anggota adalah Siswa</span>
                    @error('kelas_id')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Jabatan --}}
                <div>
                    <label for="jabatan" class="form-label">Jabatan</label>
                    <input type="text" name="jabatan" id="jabatan"
                           class="form-input @error('jabatan') is-invalid @enderror"
                           value="{{ old('jabatan', $anggota->jabatan) }}"
                           placeholder="Masukkan jabatan (opsional)">
                    <span class="form-hint">Contoh: Wali Kelas, Kepala Lab, dsb.</span>
                    @error('jabatan')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Section 5: Status & Tanggal --}}
        <div class="glass-card rounded-xl shadow-sm border border-gray-200 p-3 mb-5 fade-in">
            <div class="section-title">
                <div class="icon-box" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                    <i class="fas fa-cog"></i>
                </div>
                Status Keanggotaan
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-1.5">
                {{-- Status --}}
                <div>
                    <label class="form-label">Status <span class="required">*</span></label>
                    <input type="hidden" name="status" id="status" value="{{ old('status', $anggota->status) }}" required>
                    <div class="flex gap-1.5">
                        <div class="status-option {{ old('status', $anggota->status) == 'aktif' ? 'active-aktif' : '' }}" onclick="selectStatus('aktif', this)">
                            <span class="status-dot" style="background: #22c55e;"></span>
                            <span class="status-label">Aktif</span>
                        </div>
                        <div class="status-option {{ old('status', $anggota->status) == 'nonaktif' ? 'active-nonaktif' : '' }}" onclick="selectStatus('nonaktif', this)">
                            <span class="status-dot" style="background: #ef4444;"></span>
                            <span class="status-label">Nonaktif</span>
                        </div>
                        <div class="status-option {{ old('status', $anggota->status) == 'ditangguhkan' ? 'active-ditangguhkan' : '' }}" onclick="selectStatus('ditangguhkan', this)">
                            <span class="status-dot" style="background: #f59e0b;"></span>
                            <span class="status-label">Ditangguhkan</span>
                        </div>
                    </div>
                    @error('status')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>

                {{-- Tanggal Bergabung --}}
                <div>
                    <label for="tanggal_bergabung" class="form-label">Tanggal Bergabung <span class="required">*</span></label>
                    <div class="relative">
                        <!-- <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-[10px]"><i class="fas fa-calendar-alt"></i></span> -->
                        <input type="date" name="tanggal_bergabung" id="tanggal_bergabung"
                               class="form-input pl-9 @error('tanggal_bergabung') is-invalid @enderror"
                               value="{{ old('tanggal_bergabung', $anggota->tanggal_bergabung->format('Y-m-d')) }}" required>
                    </div>
                    @error('tanggal_bergabung')
                        <span class="form-error"><i class="fas fa-exclamation-circle"></i> {{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center justify-between gap-1.5 mb-8">
            <a href="{{ route('anggota.index') }}"
               class="px-3 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-colors text-[10px] font-medium flex items-center gap-2">
                <i class="fas fa-arrow-left text-[10px]"></i> Kembali
            </a>
            <button type="submit" id="submitBtn"
                    class="px-8 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition-colors text-[10px] font-semibold flex items-center gap-2 shadow-sm shadow-blue-200">
                <i class="fas fa-save text-[10px]"></i> Simpan Perubahan
            </button>
        </div>
    </form>
</div>

{{-- Camera Modal --}}
<div class="camera-overlay" id="cameraOverlay">
    <div class="camera-modal">
        <video id="cameraVideo" autoplay playsinline></video>
        <div class="camera-toolbar">
            <button type="button" class="btn-flip" onclick="flipCamera()" title="Balik Kamera">
                <i class="fas fa-sync-alt"></i>
            </button>
            <button type="button" class="btn-capture" onclick="capturePhoto()" title="Ambil Foto">
                <i class="fas fa-circle"></i>
            </button>
            <button type="button" class="btn-close" onclick="closeCamera()" title="Tutup">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle kelas/jabatan visibility based on jenis anggota
    updateJenisAnggotaFields();

    // Handle form submission - preserve barcode if empty
    document.getElementById('editForm').addEventListener('submit', function() {
        const barcodeInput = document.getElementById('barcode_anggota');
        if (barcodeInput.value.trim() === '') {
            barcodeInput.value = '{{ $anggota->barcode_anggota }}';
        }
    });
});

// Generate Barcode
function generateBarcode() {
    const prefix = 'BC';
    const timestamp = Date.now().toString().slice(-8);
    const random = Math.floor(Math.random() * 100).toString().padStart(2, '0');
    const barcode = prefix + timestamp + random;
    document.getElementById('barcode_anggota').value = barcode;
    showNotification('Barcode baru di-generate: ' + barcode, 'success');
}

// Gender Selection
function selectGender(value, el) {
    document.getElementById('jenis_kelamin').value = value;
    document.querySelectorAll('.gender-option').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');

    const fotoInput = document.getElementById('foto');
    const hasExistingFoto = {{ $anggota->foto ? 'true' : 'false' }};
    if ((!fotoInput.files || !fotoInput.files[0]) && !hasExistingFoto) {
        const photoArea = document.getElementById('photoArea');
        const defaultSrc = value === 'Laki-laki' ? photoArea.getAttribute('data-default-male') : photoArea.getAttribute('data-default-female');
        const preview = document.getElementById('photoPreview');
        preview.src = defaultSrc;
        preview.style.display = 'block';
    }
}

// Jenis Anggota Selection
function selectJenisAnggota(value, el) {
    document.getElementById('jenis_anggota').value = value;
    document.querySelectorAll('.jenis-anggota-option').forEach(opt => opt.classList.remove('active'));
    el.classList.add('active');
    updateJenisAnggotaFields();
}

// Update field visibility based on jenis anggota
function updateJenisAnggotaFields() {
    const jenis = document.getElementById('jenis_anggota').value;
    const kelasField = document.getElementById('kelas_id').closest('div');
    const jabatanField = document.getElementById('jabatan').closest('div');

    if (jenis === 'siswa') {
        kelasField.style.opacity = '1';
        jabatanField.style.opacity = '0.5';
    } else if (jenis === 'guru' || jenis === 'staff') {
        kelasField.style.opacity = '0.5';
        jabatanField.style.opacity = '1';
    } else {
        kelasField.style.opacity = '1';
        jabatanField.style.opacity = '1';
    }
}

// Status Selection
function selectStatus(value, el) {
    document.getElementById('status').value = value;
    document.querySelectorAll('.status-option').forEach(opt => {
        opt.classList.remove('active-aktif', 'active-nonaktif', 'active-ditangguhkan');
    });
    el.classList.add('active-' + value);
}

// Photo Preview
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];

        // Validate size
        if (file.size > 2 * 1024 * 1024) {
            showNotification('Ukuran foto maksimal 2MB', 'error');
            input.value = '';
            return;
        }

        // Validate type
        if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
            showNotification('Format foto harus JPG atau PNG', 'error');
            input.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            const photoArea = document.getElementById('photoArea');
            let preview = document.getElementById('photoPreview');
            let overlay = photoArea.querySelector('.photo-overlay');
            let placeholder = document.getElementById('uploadPlaceholder');

            if (!preview) {
                preview = document.createElement('img');
                preview.id = 'photoPreview';
                photoArea.insertBefore(preview, photoArea.firstChild);
            }

            preview.src = e.target.result;
            preview.style.display = 'block';
            photoArea.classList.add('has-photo');

            if (placeholder) placeholder.style.display = 'none';

            if (!overlay) {
                overlay = document.createElement('div');
                overlay.className = 'photo-overlay';
                overlay.innerHTML = '<i class="fas fa-camera"></i>';
                photoArea.appendChild(overlay);
            }
            overlay.style.display = '';
        };
        reader.readAsDataURL(file);
    }
}

// Camera
var cameraStream = null;
var facingMode = 'environment';

function openCamera() {
    document.getElementById('cameraOverlay').classList.add('active');
    startCamera();
}

function closeCamera() {
    stopCamera();
    document.getElementById('cameraOverlay').classList.remove('active');
}

function startCamera() {
    stopCamera();
    const constraints = {
        video: { facingMode, width: { ideal: 1280 }, height: { ideal: 960 } },
        audio: false
    };
    navigator.mediaDevices.getUserMedia(constraints)
        .then(stream => {
            cameraStream = stream;
            document.getElementById('cameraVideo').srcObject = stream;
        })
        .catch(() => {
            showNotification('Tidak dapat mengakses kamera', 'error');
            closeCamera();
        });
}

function stopCamera() {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
        cameraStream = null;
    }
}

function flipCamera() {
    facingMode = facingMode === 'environment' ? 'user' : 'environment';
    startCamera();
}

function capturePhoto() {
    const video = document.getElementById('cameraVideo');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0);

    canvas.toBlob(function(blob) {
        const file = new File([blob], 'camera_photo.jpg', { type: 'image/jpeg' });
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        const input = document.getElementById('foto');
        input.files = dataTransfer.files;
        previewPhoto(input);
        closeCamera();
    }, 'image/jpeg', 0.92);
}

// Toast Notification
function showNotification(message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500'
    };
    const icons = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        warning: 'fa-exclamation-triangle',
        info: 'fa-info-circle'
    };

    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 px-4 py-1.5 rounded-xl shadow-lg ${colors[type]} text-white text-[10px] font-medium flex items-center gap-2 transition-all duration-300 transform translate-x-full`;
    notification.innerHTML = `<i class="fas ${icons[type]}"></i><span>${message}</span>`;
    document.body.appendChild(notification);

    requestAnimationFrame(() => {
        notification.classList.remove('translate-x-full');
    });

    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}
</script>
@endpush
@endsection
