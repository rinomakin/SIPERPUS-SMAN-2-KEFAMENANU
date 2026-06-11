@extends('layouts.admin')

@section('title', 'Data User')

@section('content')
<div class="bg-white rounded-xl shadow-lg border border-gray-100">
    <!-- Header Section -->
    <div class="px-6 py-4 border-b ">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl font-bold text-gray-800">Data User</h2>
                <p class="text-gray-600 text-[11px] mt-1">Kelola user dan akun sistem</p>
            </div>
            @if($filter !== 'anggota' && (Auth::user()->hasPermission('user.create') || Auth::user()->isAdmin()))
            <a href="{{ route('user.create') }}" 
               class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors duration-200 shadow-sm hover:shadow-md">
                <i class="fas fa-plus mr-2"></i>
                Tambah
            </a>
            @endif
        </div>
    </div>

    <!-- Filter Tab: Staff | Anggota -->
    <div class="px-6 py-3 border-b border-gray-200 bg-gray-50">
        <div class="flex items-center gap-1 bg-white rounded-lg p-1 border border-gray-200 w-fit">
            <a href="{{ route('user.index', ['filter' => 'staff']) }}"
               class="px-4 py-1.5 text-xs font-medium rounded-md transition-colors {{ $filter === 'staff' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-100' }}">
                <i class="fas fa-user-tie mr-1"></i> Staff
            </a>
            <a href="{{ route('user.index', ['filter' => 'anggota']) }}"
               class="px-4 py-1.5 text-xs font-medium rounded-md transition-colors {{ $filter === 'anggota' ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-600 hover:text-gray-800 hover:bg-gray-100' }}">
                <i class="fas fa-user-graduate mr-1"></i> Anggota
            </a>
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
                           class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-[11px]">
                </div>
            </div>
            <div class="flex gap-2">
                <select id="roleFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-[11px]">
                    <option value="">Semua Role</option>
                    @foreach(\App\Models\Role::aktif()->get() as $role)
                        @if($filter === 'anggota' ? $role->kode_peran === 'ANGGOTA' : $role->kode_peran !== 'ANGGOTA')
                        <option value="{{ $role->nama_peran }}">{{ $role->nama_peran }}</option>
                        @endif
                    @endforeach
                </select>
                <select id="statusFilter" class="px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-[11px]">
                    <option value="" class="text-[11px]">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Table Info & Length -->
    <div class="px-6 py-3 border-b border-gray-200 bg-gray-50/50">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                @if($users->total() > 0)
                    Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} data
                @else
                    Tidak ada data
                @endif
            </div>
            <div class="flex items-center gap-2">
                <label class="text-xs text-gray-500">Tampilkan</label>
                <select id="lengthSelect" onchange="changeLength(this.value)" class="px-2 py-1.5 border border-gray-300 rounded-lg text-xs focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    <option value="10" {{ request('length', 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ request('length') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('length') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('length') == 100 ? 'selected' : '' }}>100</option>
                </select>
                <label class="text-xs text-gray-500">data</label>
            </div>
        </div>
    </div>

    <!-- Table Section -->
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-[9px] font-medium text-gray-500 uppercase tracking-wider">
                        No
                    </th>
                    <th class="px-6 py-3 text-left text-[9px] font-medium text-gray-500 uppercase tracking-wider">
                        User
                    </th>
                    <th class="px-6 py-3 text-left text-[9px] font-medium text-gray-500 uppercase tracking-wider">
                        Email
                    </th>
                    <th class="px-6 py-3 text-left text-[9px] font-medium text-gray-500 uppercase tracking-wider">
                        Role
                    </th>
                    <th class="px-6 py-3 text-left text-[9px] font-medium text-gray-500 uppercase tracking-wider">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-[9px] font-medium text-gray-500 uppercase tracking-wider">
                        No. Telepon
                    </th>
                    @if(Auth::user()->hasAnyPermission(['user.view', 'user.edit', 'user.delete']) || Auth::user()->isAdmin())
                    <th class="px-6 py-3 text-left text-[9px] font-medium text-gray-500 uppercase tracking-wider">
                        Aksi
                    </th>
                    @endif
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200" id="tableBody">
                @forelse($users as $index => $user)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-[10px] text-gray-900">
                            {{ $index + 1 + ($users->currentPage() - 1) * $users->perPage() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                @php
                                    $fotoUser = $user->foto && file_exists(public_path('storage/' . $user->foto)) ? asset('storage/' . $user->foto) : null;
                                    $anggota = $anggotaMap[$user->email] ?? null;
                                    $fotoAnggota = $anggota && $anggota->foto && file_exists(public_path('storage/anggota/' . $anggota->foto)) ? asset('storage/anggota/' . $anggota->foto) : null;
                                    $foto = $fotoUser ?: $fotoAnggota;
                                @endphp
                                <div class="w-9 h-9 rounded-full flex items-center justify-center mr-3 overflow-hidden flex-shrink-0
                                    {{ $foto ? '' : 'bg-gradient-to-r from-green-400 to-blue-500' }}">
                                    @if($foto)
                                        <img src="{{ $foto }}" alt="{{ $user->nama_lengkap }}" class="w-full h-full object-cover">
                                    @else
                                        <span class="text-white font-medium text-[10px]">
                                            {{ strtoupper(substr($user->nama_lengkap, 0, 2)) }}
                                        </span>
                                    @endif
                                </div>
                                <div>
                                    <div class="text-[10px] font-medium text-gray-900">{{ $user->nama_lengkap }}</div>
                                    <div class="text-[11px] text-gray-500">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-[10px] text-gray-900">{{ $user->email }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->role)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium 
                                    @if($user->role->kode_peran === 'ADMIN') bg-red-100 text-red-800
                                    @elseif($user->role->kode_peran === 'KEPALA_SEKOLAH') bg-blue-100 text-blue-800
                                    @elseif($user->role->kode_peran === 'PETUGAS') bg-green-100 text-green-800
                                    @else bg-purple-100 text-purple-800 @endif">
                                    <i class="fas fa-user-shield mr-1 text-[10px]"></i>
                                    {{ $user->role->nama_peran }}
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-gray-100 text-gray-800">
                                    <i class="fas fa-question-circle mr-1 text-[10px]"></i>
                                    Role tidak ditemukan
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->status === 'aktif')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-green-100 text-green-800">
                                    <i class="fas fa-check-circle mr-1 text-[10px]"></i>
                                    Aktif
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-times-circle mr-1 text-[10px]"></i>
                                    Nonaktif
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-xs text-gray-900">
                                @if($user->nomor_telepon)
                                    <span class="inline-flex items-center">
                                        <i class="fas fa-phone mr-1 text-gray-400 text-[10px]"></i>
                                        {{ $user->nomor_telepon }}
                                    </span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                        </td>
                        @if(Auth::user()->hasAnyPermission(['user.view', 'user.edit', 'user.delete']) || Auth::user()->isAdmin())
                        <td class="px-6 py-4 whitespace-nowrap text-xs font-medium">
                            <div class="flex items-center space-x-1">
                                @if(Auth::user()->hasPermission('user.view') || Auth::user()->isAdmin())
                                <a href="{{ route('user.show', $user->id) }}" 
                                   class="text-blue-600 hover:text-blue-900 p-1.5 rounded hover:bg-blue-50 transition-colors" 
                                   title="Detail">
                                    <i class="fas fa-eye text-[11px]"></i>
                                </a>
                                @endif
                                @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                                <a href="{{ route('user.edit', $user->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900 p-1.5 rounded hover:bg-yellow-50 transition-colors" 
                                   title="Edit">
                                    <i class="fas fa-edit text-[11px]"></i>
                                </a>
                                @endif
                                @if((Auth::user()->hasPermission('user.delete') || Auth::user()->isAdmin()) && $user->id !== auth()->id() && $user->role?->kode_peran !== 'ANGGOTA')
                                    <button type="button" 
                                            onclick="confirmDeleteUser({{ $user->id }})"
                                            class="text-red-600 hover:text-red-900 p-1.5 rounded hover:bg-red-50 transition-colors" 
                                            title="Hapus">
                                        <i class="fas fa-trash text-[11px]"></i>
                                    </button>
                                @endif
                                @if(Auth::user()->hasPermission('user.edit') || Auth::user()->isAdmin())
                                <button type="button" 
                                        onclick="confirmResetPassword({{ $user->id }})"
                                        class="text-purple-600 hover:text-purple-900 p-1.5 rounded hover:bg-purple-50 transition-colors" 
                                        title="Reset Password">
                                    <i class="fas fa-key text-[11px]"></i>
                                </button>
                                @endif
                                @if(!Auth::user()->hasAnyPermission(['user.view', 'user.edit', 'user.delete']) && !Auth::user()->isAdmin())
                                <span class="text-gray-400 text-xs">Tidak ada aksi tersedia</span>
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
    <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
        <div class="flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="text-xs text-gray-500">
                @if($users->total() > 0)
                    Menampilkan {{ $users->firstItem() }} sampai {{ $users->lastItem() }} dari {{ $users->total() }} data
                @endif
            </div>
            @if($users->hasPages())
                {{ $users->appends(['length' => request('length', 10), 'filter' => request('filter', 'staff')])->links() }}
            @endif
        </div>
    </div>
</div>

<script>
function changeLength(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('length', value);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}

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
