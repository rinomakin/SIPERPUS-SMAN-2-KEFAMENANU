@extends('layouts.anggota')

@section('title', 'Profil Saya')

@push('styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }

    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    .form-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 13px;
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

    html[data-theme="dark"] .glass-card {
        background: rgba(22, 32, 51, 0.92) !important;
        border-color: rgba(99,102,241,0.18) !important;
    }
    html[data-theme="dark"] .form-input {
        background: #1e293b !important;
        border-color: #334155 !important;
        color: #f1f5f9 !important;
    }
    html[data-theme="dark"] .form-input:focus {
        border-color: #7c3aed !important;
    }
</style>
@endpush

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    {{-- Header --}}
    <div class="flex items-center justify-between animate-fade-in-up">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Profil Saya</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data pribadi dan akun Anda</p>
        </div>
        <button type="submit" form="formProfil"
                class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-300 hover:scale-105 hover:shadow-lg flex items-center gap-2"
                style="background: linear-gradient(135deg, #3b82f6, #6366f1); box-shadow: 0 4px 15px rgba(59,130,246,0.3);">
            <i class="fas fa-save"></i>
            <span>Simpan</span>
        </button>
    </div>

    @if(session('success'))
    <div class="p-4 bg-green-50 border border-green-200 rounded-xl flex items-center gap-3 animate-fade-in-up">
        <i class="fas fa-check-circle text-green-500"></i>
        <p class="text-sm text-green-800 font-medium">{{ session('success') }}</p>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-red-50 border border-red-200 rounded-xl flex items-center gap-3 animate-fade-in-up">
        <i class="fas fa-exclamation-circle text-red-500"></i>
        <p class="text-sm text-red-800 font-medium">{{ session('error') }}</p>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-red-50 border border-red-200 rounded-xl animate-fade-in-up">
        <ul class="text-sm text-red-700 space-y-1">
            @foreach($errors->all() as $error)
            <li class="flex items-center gap-2"><i class="fas fa-circle text-red-400" style="font-size:5px;"></i>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form method="POST" action="{{ route('anggota.profil.update') }}" enctype="multipart/form-data" id="formProfil">
        @csrf

        {{-- Data Pribadi --}}
        <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden animate-fade-in-up stagger-1">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3"
                 style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                    <i class="fas fa-user text-white"></i>
                </div>
                <div>
                    <h3 class="text-white font-semibold">Data Pribadi</h3>
                    <p class="text-blue-200 text-xs">Informasi identitas anggota</p>
                </div>
            </div>

            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-8">
                    {{-- Foto --}}
                    <div class="flex flex-col items-center gap-3">
                        <div class="relative group">
                            <div class="w-32 h-32 rounded-2xl overflow-hidden"
                                 style="background: rgba(59,130,246,0.05); border: 2px dashed rgba(59,130,246,0.2);">
                                @if($anggota && $anggota->foto && file_exists(public_path('storage/anggota/' . $anggota->foto)))
                                    <img id="fotoPreview" src="{{ asset('storage/anggota/' . $anggota->foto) }}" alt="Foto" class="w-full h-full object-cover">
                                @elseif(Auth::user()->foto && file_exists(public_path('storage/' . Auth::user()->foto)))
                                    <img id="fotoPreview" src="{{ asset('storage/' . Auth::user()->foto) }}" alt="Foto" class="w-full h-full object-cover">
                                @else
                                    <div id="fotoPlaceholder" class="w-full h-full flex flex-col items-center justify-center text-blue-300">
                                        <i class="fas fa-user text-4xl"></i>
                                        <span class="text-xs mt-1">Foto</span>
                                    </div>
                                    <img id="fotoPreview" src="" alt="Preview" class="w-full h-full object-cover hidden">
                                @endif
                            </div>
                            <label for="foto" class="absolute inset-0 rounded-2xl flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-all duration-300 cursor-pointer">
                                <div class="text-white text-center">
                                    <i class="fas fa-camera text-xl"></i>
                                    <p class="text-xs font-medium">Ganti Foto</p>
                                </div>
                            </label>
                        </div>
                        <input type="file" name="foto" id="foto" accept="image/*" class="hidden" onchange="previewFoto(this)">
                        <p class="text-xs text-gray-400">JPG/PNG, maks 2MB</p>
                    </div>

                    {{-- Fields --}}
                    <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Lengkap</label>
                            <input type="text" name="nama_lengkap" value="{{ old('nama_lengkap', $anggota->nama_lengkap ?? Auth::user()->nama_lengkap) }}" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Jenis Kelamin</label>
                            <select name="jenis_kelamin" class="form-input">
                                <option value="Laki-laki" {{ (old('jenis_kelamin', $anggota->jenis_kelamin ?? '') == 'Laki-laki') ? 'selected' : '' }}>Laki-laki</option>
                                <option value="Perempuan" {{ (old('jenis_kelamin', $anggota->jenis_kelamin ?? '') == 'Perempuan') ? 'selected' : '' }}>Perempuan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $anggota && $anggota->tanggal_lahir ? $anggota->tanggal_lahir->format('Y-m-d') : '') }}" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Nomor Telepon</label>
                            <input type="text" name="nomor_telepon" value="{{ old('nomor_telepon', $anggota->nomor_telepon ?? '') }}" class="form-input" required>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Email</label>
                            <input type="email" name="email" value="{{ old('email', $anggota->email ?? Auth::user()->email) }}" class="form-input">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-1">Kelas</label>
                            <input type="text" value="{{ $anggota->kelas->nama_kelas ?? '-' }}" class="form-input" readonly style="cursor:not-allowed;color:#94a3b8;">
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Alamat</label>
                    <textarea name="alamat" rows="3" class="form-input" required>{{ old('alamat', $anggota->alamat ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </form>

    {{-- Ganti Password --}}
    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden animate-fade-in-up stagger-2">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3"
             style="background: linear-gradient(135deg, #8b5cf6, #a855f7);">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(255,255,255,0.2);">
                <i class="fas fa-lock text-white"></i>
            </div>
            <div>
                <h3 class="text-white font-semibold">Ganti Password</h3>
                <p class="text-purple-200 text-xs">Ubah password akun Anda</p>
            </div>
        </div>

        <div class="p-6">
            <form method="POST" action="{{ route('anggota.profil.ganti-password') }}" class="max-w-md space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password Lama</label>
                    <input type="password" name="password_lama" class="form-input" required placeholder="Masukkan password lama">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="password_baru" class="form-input" required placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="password_baru_confirmation" class="form-input" required placeholder="Ulangi password baru">
                </div>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-sm font-semibold text-white transition-all duration-300 hover:scale-105"
                        style="background: linear-gradient(135deg, #8b5cf6, #a855f7);">
                    <i class="fas fa-key mr-2"></i>Ubah Password
                </button>
            </form>
        </div>
    </div>

    {{-- Informasi Akun --}}
    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden animate-fade-in-up stagger-3">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-3">
            <div class="w-9 h-9 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1);">
                <i class="fas fa-info-circle text-emerald-600"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm">Informasi Akun</h3>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <span class="text-gray-500">Nomor Anggota</span>
                    <p class="font-semibold text-gray-800">{{ $anggota->nomor_anggota ?? '-' }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Jenis Anggota</span>
                    <p class="font-semibold text-gray-800">{{ ucfirst($anggota->jenis_anggota ?? '-') }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Status</span>
                    <p class="font-semibold {{ $anggota->status == 'aktif' ? 'text-green-600' : 'text-red-600' }}">{{ ucfirst($anggota->status ?? '-') }}</p>
                </div>
                <div>
                    <span class="text-gray-500">Tanggal Bergabung</span>
                    <p class="font-semibold text-gray-800">{{ $anggota->tanggal_bergabung ? \Carbon\Carbon::parse($anggota->tanggal_bergabung)->format('d/m/Y') : '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById('fotoPreview');
            const placeholder = document.getElementById('fotoPlaceholder');
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (placeholder) placeholder.style.display = 'none';
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endsection
