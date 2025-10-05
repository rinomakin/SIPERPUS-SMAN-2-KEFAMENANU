@extends('layouts.admin')

@section('title', 'Data Peminjaman Aktif')
@section('page-title', 'Data Peminjaman Aktif untuk Dikembalikan')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="min-h-screen bg-gradient-to-br py-8">
    <div class="px-4 sm:px-6 lg:px-8">
        <!-- Main Content -->
        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <!-- Header Section -->
            <div class="bg-gradient-to-r flex justify-between items-center from-blue-500 to-indigo-600 px-6 py-4">
                <div>
                    <h3 class="text-lg font-semibold text-white">Data Peminjaman Aktif</h3>
                    <p class="text-sm text-white/80">Buku yang sedang dipinjam dan belum dikembalikan</p>
                </div>
                <div class="flex items-center gap-3">
                    <!-- Search Input -->
                    <div class="relative">
                        <input type="text" id="searchInput" placeholder="Cari peminjaman aktif..." 
                               class="w-64 px-4 py-2 pl-10 text-sm border border-white/20 bg-white/10 text-white placeholder-white/70 rounded-lg focus:ring-2 focus:ring-white/50 focus:border-transparent transition-all duration-200">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-white/70"></i>
                        </div>
                    </div>
                    
                    <a href="{{ route('pengembalian.index') }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-semibold text-xs">
                        <i class="fas fa-list mr-2"></i>
                        Lihat Pengembalian Hari Ini
                    </a>
                    
                    @if(Auth::user()->hasPermission('pengembalian.create') || Auth::user()->isAdmin())
                    <a href="{{ route('pengembalian.create') }}" 
                       class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold text-xs">
                        <i class="fas fa-plus mr-2"></i>
                        Proses Pengembalian
                    </a>
                    @endif
                </div>
            </div>
            
            <div class="p-6">
                @if($peminjaman->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Peminjaman</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Anggota</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Buku</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tgl Harus Kembali</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($peminjaman as $item)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            {{ $item->nomor_peminjaman }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs font-medium text-gray-900">{{ $item->anggota->nama_lengkap }}</div>
                                        <div class="text-xs text-gray-500">{{ $item->anggota->nomor_anggota }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            {{ $item->detailPeminjaman->sum('jumlah') }} Buku
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-gray-900">{{ $item->tanggal_peminjaman->format('d/m/Y') }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-xs text-gray-900">{{ $item->tanggal_harus_kembali->format('d/m/Y') }}</div>
                                        @if($item->tanggal_harus_kembali < now()->format('Y-m-d'))
                                            <div class="text-xs text-red-600 font-medium">TERLAMBAT</div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($item->tanggal_harus_kembali < now()->format('Y-m-d'))
                                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Terlambat</span>
                                        @else
                                            <span class="bg-yellow-100 text-yellow-800 text-xs font-medium px-2.5 py-0.5 rounded">Dipinjam</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex space-x-2">
                                            <a href="{{ route('pengembalian.create') }}?peminjaman_id={{ $item->id }}" 
                                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs font-medium">
                                                <i class="fas fa-undo mr-1"></i>Kembalikan
                                            </a>
                                            <a href="{{ route('peminjaman.show', $item->id) }}" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                                <i class="fas fa-eye mr-1"></i>Detail
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    @if($peminjaman->hasPages())
                    <div class="mt-6 flex items-center justify-between">
                        <div class="text-sm text-gray-700">
                            Menampilkan {{ $peminjaman->firstItem() ?? 0 }} - {{ $peminjaman->lastItem() ?? 0 }} dari {{ $peminjaman->total() }} peminjaman aktif
                        </div>
                        <div class="flex items-center space-x-2">
                            {{ $peminjaman->appends(request()->query())->links() }}
                        </div>
                    </div>
                    @endif
                @else
                    <div class="text-center py-12">
                        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-book-open text-xl text-gray-400"></i>
                        </div>
                        @if(request('search'))
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Tidak ada peminjaman aktif ditemukan</h3>
                            <p class="text-gray-600 text-sm mb-4">Tidak ada peminjaman aktif yang sesuai dengan pencarian "{{ request('search') }}".</p>
                            <a href="{{ route('pengembalian.index', ['view' => 'active']) }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                                <i class="fas fa-arrow-left mr-1"></i>Kembali ke semua peminjaman aktif
                            </a>
                        @else
                            <h3 class="text-sm font-medium text-gray-900 mb-2">Tidak ada peminjaman aktif</h3>
                            <p class="text-gray-600 text-sm">Tidak ada buku yang sedang dipinjam saat ini.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const searchValue = this.value.trim();
            
            searchTimeout = setTimeout(() => {
                if (searchValue.length >= 2) {
                    // Redirect to search peminjaman with search term
                    window.location.href = '{{ route("pengembalian.index", ["view" => "active"]) }}' + '?search=' + encodeURIComponent(searchValue);
                } else if (searchValue.length === 0) {
                    // If empty, reload to show all active loans
                    window.location.href = '{{ route("pengembalian.index", ["view" => "active"]) }}';
                }
            }, 500); // 500ms debounce
        });
    }
});
</script>

@endsection