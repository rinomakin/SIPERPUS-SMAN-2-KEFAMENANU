@extends('layouts.admin')

@section('title', 'Data User')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100">
    <!-- Header Section -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-emerald-50">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Data User</h2>
                <p class="text-gray-600 text-sm mt-1">Kelola user dan akun sistem</p>
            </div>
            @if(Auth::user()->hasPermission('user.create') || Auth::user()->isAdmin())
            <a href="{{ route('user.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Tambah User
            </a>
            @endif
        </div>
    </div>

    <!-- Search and Filter Section -->
    <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" 
                           id="searchInput"
                           placeholder="Cari user..." 
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                </div>
            </div>
            <div class="flex gap-2">
                <select id="roleFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    <option value="">Semua Role</option>
                    @foreach(\App\Models\Role::aktif()->get() as $role)
                        <option value="{{ $role->nama_peran }}">{{ $role->nama_peran }}</option>
                    @endforeach
                </select>
                <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        User
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        No. Telepon
                    </th>
                    @if(Auth::user()->hasAnyPermission(['user.view', 'user.edit', 'user.delete']) || Auth::user()->isAdmin())
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                @forelse($users as $index => $user)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $index + 1 + ($users->currentPage() - 1) * $users->perPage() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center mr-3">
                                    <span class="text-white font-medium text-sm">
                                        {{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}
                                    </span>
                                </div>
                                <div>
                                    <div class="text-sm font-medium text-gray-900">{{ $user->nama_lengkap }}</div>
                                    <div class="text-xs text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->role)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                    @if($user->role->kode_peran === 'ADMIN') bg-red-100 text-red-800
                                    @elseif($user->role->kode_peran === 'KEPALA_SEKOLAH') bg-blue-100 text-blue-800
                                    @elseif($user->role->kode_peran === 'PETUGAS') bg-green-100 text-green-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    <i class="fas fa-user-shield mr-1"></i>
                                    {{ $user->role->nama_peran }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-question-circle mr-1"></i>
                                    Role tidak ditemukan
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->status === 'aktif')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1"></i>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1"></i>
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                @if($user->nomor_telepon)
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-phone mr-1 text-gray-400"></i>
                                        {{ $user->nomor_telepon }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        @if(Auth::user()->hasAnyPermission(['user.view', 'user.edit', 'user.delete']) || Auth::user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex items-center space-x-2">
                                @if(Auth::user()->hasPermission('user.view') || Auth::user()->isAdmin())
                                <a href="{{ route('user.show', $user->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-1 rounded hover:bg-blue-50 transition-colors" 
                                   title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @endif
                                @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                                <a href="{{ route('user.edit', $user->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 p-1 rounded hover:bg-yellow-50 transition-colors" 
                                   title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @endif
                                @if(Auth::user()->hasPermission('user.delete') || Auth::user()->isAdmin())
                                @if($user->id !== auth()->id())
                                    <button type="button" 
                                            onclick="confirmDeleteUser({{ $user->id }})"
                                            class="text-red-600 hover:text-red-900 p-1 rounded hover:bg-red-50 transition-colors" 
                                            title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                @endif
                                @endif
                                @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                                <button type="button" 
                                        onclick="confirmResetPassword({{ $user->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1 rounded hover:bg-purple-50 transition-colors" 
                                        title="Reset Password">
                                    <i class="fas fa-key"></i>
                                </button>
                                @endif
                                @if(!Auth::user()->hasAnyPermission(['user.view', 'user.edit', 'user.delete']) && !Auth::user()->isAdmin())
                                <span class="text-gray-400 text-sm">Tidak ada aksi tersedia</span>
                                @endif
                            </div>
                        </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ (Auth::user()->hasAnyPermission(['user.view', 'user.edit', 'user.delete']) || Auth::user()->isAdmin()) ? '7' : '6' }}" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-users text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500 text-lg font-medium">Tidak ada data user</p>
                                <p class="text-gray-400 text-sm mt-1">Mulai dengan menambahkan user pertama</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $users->links() }}
        </div>
    @endif
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('tableBody');
    const rows = tableBody.querySelectorAll('tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const roleTerm = roleFilter.value.toLowerCase();
        const statusTerm = statusFilter.value.toLowerCase();

        rows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length === 0) return; // Skip empty rows

            const userCell = cells[1]?.textContent.toLowerCase() || '';
            const emailCell = cells[2]?.textContent.toLowerCase() || '';
            const roleCell = cells[3]?.textContent.toLowerCase() || '';
            const statusCell = cells[4]?.textContent.toLowerCase() || '';
            const phoneCell = cells[5]?.textContent.toLowerCase() || '';

            const matchesSearch = userCell.includes(searchTerm) || 
                                emailCell.includes(searchTerm) || 
                                phoneCell.includes(searchTerm);
            const matchesRole = !roleTerm || roleCell.includes(roleTerm);
            const matchesStatus = !statusTerm || statusCell.includes(statusTerm);

            row.style.display = matchesSearch && matchesRole && matchesStatus ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterTable);
    roleFilter.addEventListener('change', filterTable);
    statusFilter.addEventListener('change', filterTable);
});

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
