@extends('layouts.admin')

@section('title', 'Detail Role')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100">
    <!-- Header Section -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-indigo-50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Detail Role</h2>
                <p class="text-gray-600 text-sm mt-1">Informasi lengkap role: {{ $role->nama_peran }}</p>
            </div>
            <div class="flex flex-col sm:flex-row gap-2">
                @if(Auth::user()->hasPermission('role.edit') || Auth::user()->isAdmin())
                <a href="{{ route('role.edit', $role->id) }}" 
                   class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Role
                </a>
                @endif
                <a href="{{ route('role.index') }}" 
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
            <!-- Role Information -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-user-shield mr-2 text-blue-600"></i>
                        Informasi Role
                    </h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-user-shield text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Nama Role</label>
                                <p class="mt-1 text-lg font-medium text-gray-900">{{ $role->nama_peran }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-code text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Kode Role</label>
                                <p class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                                        <i class="fas fa-tag mr-1"></i>
                                        {{ $role->kode_peran }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-align-left text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Deskripsi</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $role->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-toggle-on text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Status</label>
                                <p class="mt-1">
                                    @if($role->status === 'aktif')
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
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-calendar text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Tanggal Dibuat</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $role->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        <div class="flex items-start">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-clock text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="ml-3 flex-1">
                                <label class="block text-sm font-semibold text-gray-700">Terakhir Diupdate</label>
                                <p class="mt-1 text-sm text-gray-900">{{ $role->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Users with this Role -->
            <div class="space-y-6">
                <div class="bg-gray-50 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-users mr-2 text-blue-600"></i>
                        User dengan Role Ini
                        <span class="ml-2 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                            {{ $role->users->count() }} user
                        </span>
                    </h3>
                    
                    @if($role->users->count() > 0)
                        <div class="space-y-3">
                            @foreach($role->users as $user)
                                <div class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200 hover:shadow-sm transition-shadow">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center mr-3">
                                            <span class="text-white font-medium text-sm">
                                                {{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900">{{ $user->nama_lengkap }}</p>
                                            <p class="text-sm text-gray-500">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <span class="px-2 py-1 rounded-full text-xs font-medium
                                        {{ $user->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->status }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                            <p class="text-gray-500 text-lg font-medium">Belum ada user</p>
                            <p class="text-gray-400 text-sm mt-1">Role ini belum digunakan oleh user manapun</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="mt-8 pt-6 border-t border-gray-200">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex flex-col sm:flex-row gap-3">
                    @if(Auth::user()->hasPermission('role.edit') || Auth::user()->isAdmin())
                    <a href="{{ route('role.edit', $role->id) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-lg transition-colors duration-200">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Role
                    </a>
                    @endif
                    <a href="{{ route('role.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                </div>
                
                @if(Auth::user()->hasPermission('role.delete') || Auth::user()->isAdmin())
                <form action="{{ route('role.destroy', $role->id) }}" 
                      method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus role ini?')"
                      class="flex-shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition-colors duration-200 {{ $role->users->count() > 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                            {{ $role->users->count() > 0 ? 'disabled' : '' }}>
                        <i class="fas fa-trash mr-2"></i>
                        Hapus Role
                    </button>
                    @if($role->users->count() > 0)
                        <p class="text-sm text-red-600 mt-2 flex items-center">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            Role tidak dapat dihapus karena masih digunakan oleh {{ $role->users->count() }} user
                        </p>
                    @endif
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
