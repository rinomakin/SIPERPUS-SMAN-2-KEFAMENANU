@extends('layouts.admin')

@section('page-title', 'Profil')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Single Modern Card -->
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden">
            <!-- Header dengan Gradient -->
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 px-8 py-6">
                <div class="flex items-center">
                    <div class="w-16 h-16 bg-white bg-opacity-20 rounded-full flex items-center justify-center mr-4 overflow-hidden">
                        @if($user->foto && file_exists(public_path('storage/' . $user->foto)))
                            <img src="{{ asset('storage/' . $user->foto) }}" 
                                 alt="Foto Profil" 
                                 class="w-full h-full object-cover">
                        @else
                            <i class="fas fa-user text-2xl text-white"></i>
                        @endif
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-white">
                            {{ $user->nama_panggilan ? $user->nama_panggilan : $user->nama_lengkap }}
                        </h1>
                        <p class="text-blue-100 text-sm">Kelola informasi profil dan keamanan akun Anda</p>
                        <!-- Menampilkan role user -->
                        <div class="mt-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white bg-opacity-20 text-white">
                                <i class="fas fa-user-tag mr-1"></i>
                                {{ $user->role ? $user->role->nama_peran : 'Tidak ada role' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Content -->
            <div class="p-8">
                <!-- Tabs Navigation -->
                <div class="flex border-b border-gray-200 mb-8">
                    <button onclick="switchTab('profile')" id="profile-tab" 
                            class="flex items-center px-6 py-3 text-sm font-medium border-b-2 border-blue-600 text-blue-600 focus:outline-none">
                        <i class="fas fa-user mr-2"></i>
                        Informasi Profil
                    </button>
                    <button onclick="switchTab('security')" id="security-tab" 
                            class="flex items-center px-6 py-3 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i class="fas fa-shield-alt mr-2"></i>
                        Keamanan
                    </button>
                </div>

                <!-- Profile Tab Content -->
                <div id="profile-content">
                    <form action="{{ route('petugas.profil.update') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                        @csrf
                        
                        <!-- Foto Profil Section -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-camera text-blue-500 mr-2"></i>
                                Foto Profil
                            </h3>
                            
                            <div class="flex items-center space-x-6">
                                <!-- Current Photo Display -->
                                <div class="flex-shrink-0">
                                    @if($user->foto && file_exists(public_path('storage/' . $user->foto)))
                                        <img src="{{ asset('storage/' . $user->foto) }}" 
                                             alt="Foto Profil" 
                                             class="w-24 h-24 rounded-full object-cover border-4 border-white shadow-lg"
                                             id="current-photo">
                                    @else
                                        <div class="w-24 h-24 bg-gray-300 rounded-full flex items-center justify-center border-4 border-white shadow-lg" id="default-avatar">
                                            <i class="fas fa-user text-3xl text-gray-600"></i>
                                        </div>
                                    @endif
                                </div>
                                
                                <!-- Upload Controls -->
                                <div class="flex-1">
                                    <div class="space-y-3">
                                        <div>
                                            <label for="foto" class="block text-sm font-medium text-gray-700 mb-2">
                                                Upload Foto Baru
                                            </label>
                                            <input type="file" 
                                                   id="foto" 
                                                   name="foto" 
                                                   accept="image/*"
                                                   onchange="previewImage(this)"
                                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                                            @error('foto')
                                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                            <p class="text-xs text-gray-500 mt-1">Format: JPG, JPEG, PNG, GIF. Maksimal 2MB</p>
                                        </div>
                                        
                                        @if($user->foto)
                                        <div>
                                            <button type="button" 
                                                    onclick="hapusFoto()"
                                                    class="inline-flex items-center px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 text-sm font-medium rounded-lg transition-colors">
                                                <i class="fas fa-trash mr-2"></i>
                                                Hapus Foto
                                            </button>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Grid Layout untuk Form -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Nama Lengkap -->
                            <div class="space-y-2">
                                <label for="nama_lengkap" class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-id-card text-blue-500 mr-2"></i>
                                    Nama Lengkap
                                </label>
                                <input type="text" 
                                       id="nama_lengkap" 
                                       name="nama_lengkap" 
                                       value="{{ old('nama_lengkap', $user->nama_lengkap) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('nama_lengkap') border-red-500 @enderror"
                                       placeholder="Masukkan nama lengkap"
                                       required>
                                @error('nama_lengkap')
                                    <p class="text-red-500 text-sm mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Nama Panggilan -->
                            <div class="space-y-2">
                                <label for="nama_panggilan" class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-user text-blue-500 mr-2"></i>
                                    Nama Panggilan
                                </label>
                                <input type="text" 
                                       id="nama_panggilan" 
                                       name="nama_panggilan" 
                                       value="{{ old('nama_panggilan', $user->nama_panggilan) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('nama_panggilan') border-red-500 @enderror"
                                       placeholder="Masukkan nama panggilan">
                                @error('nama_panggilan')
                                    <p class="text-red-500 text-sm mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <label for="email" class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-envelope text-blue-500 mr-2"></i>
                                    Email
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="{{ old('email', $user->email) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('email') border-red-500 @enderror"
                                       placeholder="Masukkan email"
                                       required>
                                @error('email')
                                    <p class="text-red-500 text-sm mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Telepon -->
                            <div class="space-y-2">
                                <label for="nomor_telepon" class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-phone text-blue-500 mr-2"></i>
                                    Nomor Telepon
                                </label>
                                <input type="text" 
                                       id="nomor_telepon" 
                                       name="nomor_telepon" 
                                       value="{{ old('nomor_telepon', $user->nomor_telepon) }}"
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('nomor_telepon') border-red-500 @enderror"
                                       placeholder="Contoh: 081234567890">
                                @error('nomor_telepon')
                                    <p class="text-red-500 text-sm mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Role/Peran (Read Only) -->
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-user-tag text-purple-500 mr-2"></i>
                                    Peran/Role
                                </label>
                                <div class="px-4 py-3 bg-purple-50 border border-purple-200 rounded-lg">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-purple-100 text-purple-800">
                                        <i class="fas fa-shield-alt mr-1"></i>
                                        {{ $user->role ? $user->role->nama_peran : 'Tidak ada role' }}
                                    </span>
                                </div>
                                <p class="text-xs text-gray-500">Peran tidak dapat diubah</p>
                            </div>

                            <!-- Status -->
                            <div class="space-y-2">
                                <label class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-circle text-green-500 mr-2"></i>
                                    Status Akun
                                </label>
                                <div class="px-4 py-3 bg-green-50 border border-green-200 rounded-lg">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ ucfirst($user->status) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Alamat (Full Width) -->
                        <div class="space-y-2">
                            <label for="alamat" class="flex items-center text-sm font-semibold text-gray-700">
                                <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                                Alamat
                            </label>
                            <textarea id="alamat" 
                                      name="alamat" 
                                      rows="3"
                                      class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('alamat') border-red-500 @enderror"
                                      placeholder="Masukkan alamat lengkap">{{ old('alamat', $user->alamat) }}</textarea>
                            @error('alamat')
                                <p class="text-red-500 text-sm mt-1 flex items-center">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end pt-6 border-t border-gray-200">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors duration-200">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Security Tab Content -->
                <div id="security-content" class="hidden">
                    <form action="{{ route('petugas.profil.ganti-password') }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password Lama -->
                            <div class="space-y-2">
                                <label for="password_lama" class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-lock text-red-500 mr-2"></i>
                                    Password Lama
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           id="password_lama" 
                                           name="password_lama" 
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('password_lama') border-red-500 @enderror"
                                           placeholder="Masukkan password lama"
                                           required>
                                    <button type="button" onclick="togglePassword('password_lama')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye" id="password_lama_icon"></i>
                                    </button>
                                </div>
                                @error('password_lama')
                                    <p class="text-red-500 text-sm mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Placeholder untuk symmetry -->
                            <div></div>

                            <!-- Password Baru -->
                            <div class="space-y-2">
                                <label for="password_baru" class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-key text-green-500 mr-2"></i>
                                    Password Baru
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           id="password_baru" 
                                           name="password_baru" 
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 @error('password_baru') border-red-500 @enderror"
                                           placeholder="Masukkan password baru"
                                           required>
                                    <button type="button" onclick="togglePassword('password_baru')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye" id="password_baru_icon"></i>
                                    </button>
                                </div>
                                @error('password_baru')
                                    <p class="text-red-500 text-sm mt-1 flex items-center">
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                        {{ $message }}
                                    </p>
                                @enderror
                                <p class="text-sm text-gray-500">Password minimal 8 karakter</p>
                            </div>

                            <!-- Konfirmasi Password -->
                            <div class="space-y-2">
                                <label for="password_baru_confirmation" class="flex items-center text-sm font-semibold text-gray-700">
                                    <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                    Konfirmasi Password
                                </label>
                                <div class="relative">
                                    <input type="password" 
                                           id="password_baru_confirmation" 
                                           name="password_baru_confirmation" 
                                           class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200"
                                           placeholder="Konfirmasi password baru"
                                           required>
                                    <button type="button" onclick="togglePassword('password_baru_confirmation')" 
                                            class="absolute inset-y-0 right-0 pr-3 flex items-center text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-eye" id="password_baru_confirmation_icon"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Security Tips -->
                        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
                            <div class="flex items-start">
                                <i class="fas fa-shield-alt text-blue-600 text-xl mr-4 mt-1"></i>
                                <div>
                                    <h3 class="font-semibold text-blue-900 mb-2">Tips Keamanan Password</h3>
                                    <ul class="text-sm text-blue-800 space-y-1">
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Gunakan kombinasi huruf besar, huruf kecil, angka, dan simbol
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Hindari menggunakan informasi pribadi
                                        </li>
                                        <li class="flex items-center">
                                            <i class="fas fa-check text-green-500 mr-2"></i>
                                            Jangan gunakan password yang sama di tempat lain
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-end pt-6 border-t border-gray-200">
                            <button type="submit" class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200">
                                <i class="fas fa-key mr-2"></i>
                                Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript untuk tabs dan password toggle -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize tabs
    switchTab('profile');
});

// Function untuk switch tabs
function switchTab(tabName) {
    // Hide all content
    document.getElementById('profile-content').classList.add('hidden');
    document.getElementById('security-content').classList.add('hidden');
    
    // Remove active class from all tabs
    document.getElementById('profile-tab').classList.remove('border-blue-600', 'text-blue-600');
    document.getElementById('profile-tab').classList.add('border-transparent', 'text-gray-500');
    document.getElementById('security-tab').classList.remove('border-blue-600', 'text-blue-600');
    document.getElementById('security-tab').classList.add('border-transparent', 'text-gray-500');
    
    // Show selected content and activate tab
    if (tabName === 'profile') {
        document.getElementById('profile-content').classList.remove('hidden');
        document.getElementById('profile-tab').classList.add('border-blue-600', 'text-blue-600');
        document.getElementById('profile-tab').classList.remove('border-transparent', 'text-gray-500');
    } else if (tabName === 'security') {
        document.getElementById('security-content').classList.remove('hidden');
        document.getElementById('security-tab').classList.add('border-blue-600', 'text-blue-600');
        document.getElementById('security-tab').classList.remove('border-transparent', 'text-gray-500');
    }
}

// Function untuk toggle password visibility
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Add smooth transitions
document.addEventListener('DOMContentLoaded', function() {
    const elements = document.querySelectorAll('.transition-all');
    elements.forEach(el => {
        el.style.transition = 'all 0.3s ease';
    });
});

// Function untuk preview image sebelum upload
function previewImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            const currentPhoto = document.getElementById('current-photo');
            const defaultAvatar = document.getElementById('default-avatar');
            
            if (currentPhoto) {
                currentPhoto.src = e.target.result;
            } else if (defaultAvatar) {
                // Replace default avatar with image
                defaultAvatar.innerHTML = `<img src="${e.target.result}" alt="Preview" class="w-24 h-24 rounded-full object-cover">`;
            }
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}

// Function untuk hapus foto
function hapusFoto() {
    showConfirmDialog(
        'Apakah Anda yakin ingin menghapus foto profil?',
        'Konfirmasi Hapus Foto',
        function() {
            // Create form untuk delete
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route("petugas.profil.hapus-foto") }}';
            
            // Add CSRF token
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = '{{ csrf_token() }}';
            form.appendChild(csrfToken);
            
            // Add method override for DELETE
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            form.appendChild(methodField);
            
            document.body.appendChild(form);
            form.submit();
        }
    );
}
</script>
@endsection
