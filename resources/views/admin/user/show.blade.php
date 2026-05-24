@extends('layouts.admin')

@section('title', 'Detail User')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100">
    <!-- Header Section -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail User</h2>
                <p class="text-gray-600 text-sm mt-1">Informasi lengkap user: {{ $user->nama_lengkap }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                <a href="{{ route('user.edit', $user->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-edit mr-2"></i>
                    Edit User
                </a>
                @endif
                <a href="{{ route('user.index') }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- User Information -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user mr-2 text-green-600"></i>
                        Informasi User
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center">
                                    <span class="text-white font-medium text-sm">
                                        {{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}
                                    </span>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Nama Lengkap</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $user->nama_lengkap }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-envelope text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Email</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-shield text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Role</label>
                                <p class="mt-1">
                                    @if($user->role)
                                        <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-user-shield mr-1"></i>
                                            {{ $user->role->nama_peran }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 bg-gray-100 text-gray-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-question-circle mr-1"></i>
                                            Role tidak ditemukan
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-toggle-on text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Status</label>
                                <p class="mt-1">
                                    @if($user->status === 'aktif')
                                        <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-check-circle mr-1"></i>
                                            Aktif
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 rounded-full text-sm font-medium">
                                            <i class="fas fa-times-circle mr-1"></i>
                                            Nonaktif
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-phone text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Nomor Telepon</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $user->nomor_telepon ?? 'Tidak ada nomor telepon' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-map-marker-alt text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Alamat</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $user->alamat ?? 'Tidak ada alamat' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Tanggal Dibuat</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Terakhir Diupdate</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $user->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Account Information & Actions -->
            <div class="space-y-6">
                <!-- Account Information -->
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle mr-2 text-green-600"></i>
                        Informasi Akun
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-700">ID User:</span>
                            <span class="text-sm text-gray-900 font-mono">{{ $user->id }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Kode Role:</span>
                            <span class="text-sm text-gray-900 font-mono">{{ $user->peran }}</span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Email Verified:</span>
                            <span class="text-sm text-gray-900">
                                @if($user->email_verified_at)
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 bg-red-100 text-red-800 rounded-full text-xs">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Belum diverifikasi
                                    </span>
                                @endif
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 bg-white rounded-lg border border-gray-200">
                            <span class="text-sm font-medium text-gray-700">Remember Token:</span>
                            <span class="text-sm text-gray-900">
                                @if($user->remember_token)
                                    <span class="inline-flex items-center px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        Ada
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-800 rounded-full text-xs">
                                        <i class="fas fa-times-circle mr-1"></i>
                                        Tidak ada
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Special Actions -->
                @if((Auth::user()->hasAnyPermission(['user.edit', 'user.delete']) || Auth::user()->isAdmin()) && $user->id !== auth()->id())
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-tools mr-2 text-green-600"></i>
                            Aksi Khusus
                        </h3>
                        
                        <div class="space-y-3">
                            @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                            <button type="button" 
                                    onclick="confirmResetPassword({{ $user->id }})"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-key mr-2"></i>
                                Reset Password
                            </button>
                            @endif
                            
                            @if(Auth::user()->hasPermission('user.delete') || Auth::user()->isAdmin())
                            <button type="button" 
                                    onclick="confirmDeleteUser({{ $user->id }})"
                                    class="w-full inline-flex items-center justify-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                                <i class="fas fa-trash mr-2"></i>
                                Hapus User
                            </button>
                            @endif
                        </div>
                    </div>
                @elseif($user->id === auth()->id())
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-info-circle text-yellow-600 mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-yellow-800">Akun Aktif</h3>
                                <p class="text-sm text-yellow-700 mt-1">
                                    Ini adalah akun yang sedang Anda gunakan. Beberapa aksi tidak tersedia untuk keamanan.
                                </p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lock text-blue-600 mt-1"></i>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800">Akses Terbatas</h3>
                                <p class="text-sm text-blue-700 mt-1">
                                    Anda tidak memiliki hak akses untuk mengelola user ini. Silakan hubungi administrator untuk mendapatkan akses.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                    <a href="{{ route('user.edit', $user->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Edit User
                    </a>
                    @endif
                    <a href="{{ route('user.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
                
                @if((Auth::user()->hasAnyPermission(['user.edit', 'user.delete']) || Auth::user()->isAdmin()) && $user->id !== auth()->id())
                    <div class="flex flex-col sm:flex-row gap-2">
                        @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                        <button type="button" 
                                onclick="confirmResetPassword({{ $user->id }})"
                                class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-key mr-2"></i>
                            Reset Password
                        </button>
                        @endif
                        
                        @if(Auth::user()->hasPermission('user.delete') || Auth::user()->isAdmin())
                        <button type="button" 
                                onclick="confirmDeleteUser({{ $user->id }})"
                                class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus User
                        </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// SweetAlert2 Functions for User Management
function confirmDeleteUser(userId) {
    showConfirmDialog(
        'Apakah Anda yakin ingin menghapus user ini? Tindakan ini tidak dapat dibatalkan.',
        'Konfirmasi Hapus User',
        function() {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/user/${userId}`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            const methodField = document.createElement('input');
            methodField.type = 'hidden';
            methodField.name = '_method';
            methodField.value = 'DELETE';
            
            form.appendChild(csrfToken);
            form.appendChild(methodField);
            document.body.appendChild(form);
            form.submit();
        }
    );
}

function confirmResetPassword(userId) {
    showConfirmDialog(
        'Reset password user ini menjadi password123?',
        'Konfirmasi Reset Password',
        function() {
            // Create form and submit
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/user/${userId}/reset-password`;
            
            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            
            form.appendChild(csrfToken);
            document.body.appendChild(form);
            form.submit();
        }
    );
}
</script>
@endsection
