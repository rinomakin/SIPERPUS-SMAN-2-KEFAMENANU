@extends('layouts.admin')

@section('title', 'Buku Tamu')
@section('page-title', 'Buku Tamu')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .glass-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.3);
    }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    .animate-fade { animation: fadeIn 0.4s ease forwards; }
    @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
    .animate-slide-up { animation: slideUp 0.3s ease-out forwards; }
    @media (max-width: 767px) {
        .modal-mobile { animation: slideUp 0.3s ease-out forwards; border-radius: 1rem 1rem 0 0; }
    }
    .visitor-card { transition: all 0.2s ease; }
    .visitor-card:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0,0,0,0.08); }
</style>

<div class="space-y-5">
    {{-- Header --}}
    <div class="glass-card rounded-2xl shadow-lg p-5 animate-fade">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buku Tamu Hari Ini</h1>
                <p class="text-sm text-gray-500">
                    <i class="fas fa-calendar-day mr-1"></i>
                    {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                {{-- Search --}}
                <div class="relative flex-1 min-w-[140px] max-w-full md:max-w-[200px]">
                    <input type="text" id="searchInput" placeholder="Cari tamu..." value="{{ request('search') }}"
                           class="w-full px-4 py-2.5 pl-10 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white/70 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Filter Button --}}
                    <button type="button" onclick="openFilterModal()"
                            class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-white/70 hover:bg-violet-50 hover:border-violet-300 transition-all flex items-center gap-2 @if(request()->hasAny(['status','tipe_tamu'])) text-violet-700 border-violet-300 bg-violet-50 @else text-gray-600 @endif">
                        <i class="fas fa-filter text-xs"></i>
                        <span class="hidden sm:inline">Filter</span>
                        @if(request()->hasAny(['status','tipe_tamu']))
                            <span class="w-2 h-2 rounded-full bg-violet-500"></span>
                        @endif
                    </button>

                    {{-- Tambah --}}
                    @if(Auth::user()->hasPermission('buku-tamu.create'))
                    <a href="{{ route('admin.buku-tamu.create') }}"
                       class="px-4 py-2.5 bg-gradient-to-r from-violet-500 to-purple-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all whitespace-nowrap">
                        <i class="fas fa-plus mr-1.5"></i> Tambah
                    </a>
                    @endif

                    {{-- Riwayat --}}
                    <a href="{{ route('admin.buku-tamu.history') }}"
                       class="px-4 py-2.5 bg-white border border-gray-200 hover:border-violet-300 hover:bg-violet-50 text-gray-700 text-sm font-medium rounded-xl transition-all whitespace-nowrap">
                        <i class="fas fa-history mr-1.5"></i> Riwayat
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 md:gap-4">
        <div class="glass-card rounded-2xl p-3 md:p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-violet-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-users text-violet-600 text-sm md:text-base"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] md:text-xs text-gray-500 truncate">Total Hari Ini</p>
                    <p class="text-base md:text-lg font-bold text-gray-900">{{ $totalTamuHariIni }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-3 md:p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-clock text-emerald-600 text-sm md:text-base"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] md:text-xs text-gray-500 truncate">Sedang Berkunjung</p>
                    <p class="text-base md:text-lg font-bold text-gray-900">{{ $sedangBerkunjung }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-3 md:p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-amber-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-sign-out-alt text-amber-600 text-sm md:text-base"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] md:text-xs text-gray-500 truncate">Sudah Pulang</p>
                    <p class="text-base md:text-lg font-bold text-gray-900">{{ $sudahPulang }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-3 md:p-4 animate-fade" style="animation-delay:0.2s">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-id-card text-blue-600 text-sm md:text-base"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] md:text-xs text-gray-500 truncate">Anggota</p>
                    <p class="text-base md:text-lg font-bold text-gray-900">{{ $tamuAnggota }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-3 md:p-4 animate-fade col-span-2 md:col-span-1" style="animation-delay:0.25s">
            <div class="flex items-center gap-2 md:gap-3">
                <div class="w-8 h-8 md:w-10 md:h-10 bg-purple-100 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-user-friends text-purple-600 text-sm md:text-base"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-[10px] md:text-xs text-gray-500 truncate">Tamu Umum</p>
                    <p class="text-base md:text-lg font-bold text-gray-900">{{ $tamuUmum }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Visitor List --}}
    <div class="glass-card rounded-2xl shadow-lg overflow-hidden animate-fade" style="animation-delay:0.15s">
        <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                <i class="fas fa-list text-violet-500"></i>
                Daftar Tamu
                <span class="px-2 py-0.5 bg-violet-100 text-violet-700 text-xs font-bold rounded-lg">{{ $kunjunganHariIni->count() }}</span>
            </h2>
            @if(request()->hasAny(['search', 'status', 'tipe_tamu']))
                <a href="/admin/buku-tamu" class="text-xs text-violet-600 hover:text-violet-800 font-medium">
                    <i class="fas fa-times mr-1"></i> Reset Filter
                </a>
            @endif
        </div>

        @if($kunjunganHariIni->count() === 0)
            <div class="text-center py-16 px-6">
                <div class="w-20 h-20 bg-gray-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-book-open text-3xl text-gray-400"></i>
                </div>
                <h3 class="text-base font-semibold text-gray-900 mb-1">Belum ada tamu hari ini</h3>
                <p class="text-sm text-gray-500 mb-4">Klik tombol "Tambah" untuk mencatat kunjungan baru</p>
                @if(Auth::user()->hasPermission('buku-tamu.create'))
                <a href="/admin/buku-tamu/create"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-plus mr-1.5"></i> Tambah Tamu
                </a>
                @endif
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($kunjunganHariIni as $kunjungan)
                <div class="visitor-card px-4 md:px-5 py-3 md:py-4 hover:bg-violet-50/30 transition-colors">
                    {{-- Main Row --}}
                    <div class="flex items-start justify-between gap-3">
                        {{-- Left: Avatar + Info --}}
                        <div class="flex items-start gap-3 min-w-0 flex-1">
                            <div class="relative flex-shrink-0 mt-0.5">
                                <img src="{{ $kunjungan->anggota && $kunjungan->anggota->foto ? asset('storage/anggota/' . $kunjungan->anggota->foto) : asset('images/default-avatar.png') }}"
                                     alt="Foto" class="w-10 h-10 md:w-12 md:h-12 rounded-xl object-cover border-2 border-gray-200"
                                     onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                @if(!$kunjungan->waktu_pulang)
                                    <span class="absolute -bottom-1 -right-1 w-3 h-3 md:w-4 md:h-4 bg-emerald-500 rounded-full border-2 border-white"></span>
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <div class="flex items-center gap-1.5 flex-wrap">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate max-w-[140px] md:max-w-none">{{ $kunjungan->nama_tamu ?? ($kunjungan->anggota ? $kunjungan->anggota->nama_lengkap : '-') }}</h4>
                                    @if($kunjungan->anggota_id)
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-lg text-[10px] md:text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200 whitespace-nowrap">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Anggota
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-lg text-[10px] md:text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200 whitespace-nowrap">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Umum
                                        </span>
                                    @endif
                                </div>
                                <p class="text-[11px] md:text-xs text-gray-500 mt-0.5 truncate">
                                    @if($kunjungan->anggota)
                                        {{ $kunjungan->anggota->nomor_anggota }}
                                        @if($kunjungan->anggota->kelas)
                                            &middot; {{ $kunjungan->anggota->kelas->nama_kelas }}
                                        @endif
                                    @else
                                        {{ $kunjungan->instansi ?: '-' }}
                                        @if($kunjungan->no_telepon)
                                            &middot; {{ $kunjungan->no_telepon }}
                                        @endif
                                    @endif
                                </p>
                                {{-- Time Info for Mobile --}}
                                <div class="flex md:hidden items-center gap-2 mt-1.5">
                                    <span class="text-[10px] text-gray-400 font-mono">
                                        <i class="fas fa-sign-in-alt mr-0.5"></i>{{ $kunjungan->waktu_datang->format('H:i') }}
                                    </span>
                                    @if($kunjungan->waktu_pulang)
                                        <span class="text-[10px] text-gray-400">|</span>
                                        <span class="text-[10px] text-gray-400 font-mono">
                                            <i class="fas fa-sign-out-alt mr-0.5"></i>{{ $kunjungan->waktu_pulang->format('H:i') }}
                                        </span>
                                    @endif
                                </div>
                                @if($kunjungan->keperluan)
                                    <p class="text-[11px] md:text-xs text-violet-600 mt-0.5 truncate">
                                        <i class="fas fa-tag mr-1"></i>{{ $kunjungan->keperluan }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Status + Actions --}}
                        <div class="flex flex-col items-end gap-2 flex-shrink-0">
                            @if($kunjungan->waktu_pulang)
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 md:px-2.5 md:py-1 rounded-lg text-[10px] md:text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Pulang
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 md:px-2.5 md:py-1 rounded-lg text-[10px] md:text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200 whitespace-nowrap">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Berkunjung
                                </span>
                            @endif

                            {{-- Time Info Desktop --}}
                            <div class="hidden md:flex items-center gap-2 text-center">
                                <div>
                                    <p class="text-[10px] text-gray-400">Datang</p>
                                    <p class="text-xs font-semibold text-gray-800 font-mono">{{ $kunjungan->waktu_datang->format('H:i') }}</p>
                                </div>
                                <i class="fas fa-arrow-right text-gray-300 text-[10px]"></i>
                                <div>
                                    <p class="text-[10px] text-gray-400">Pulang</p>
                                    @if($kunjungan->waktu_pulang)
                                        <p class="text-xs font-semibold text-gray-800 font-mono">{{ $kunjungan->waktu_pulang->format('H:i') }}</p>
                                    @else
                                        <p class="text-xs text-gray-400 font-mono">--:--</p>
                                    @endif
                                </div>
                            </div>

                            {{-- Actions --}}
                            <div class="flex items-center gap-1">
                                @if(!$kunjungan->waktu_pulang)
                                    <button onclick="recordExit({{ $kunjungan->id }})"
                                            class="w-7 h-7 md:w-8 md:h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Catat Pulang">
                                        <i class="fas fa-sign-out-alt text-[10px] md:text-xs"></i>
                                    </button>
                                @endif
                                <a href="{{ route('admin.buku-tamu.show', $kunjungan->id) }}"
                                   class="w-7 h-7 md:w-8 md:h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Detail">
                                    <i class="fas fa-eye text-[10px] md:text-xs"></i>
                                </a>
                                @if(Auth::user()->hasPermission('buku-tamu.edit'))
                                <a href="{{ route('admin.buku-tamu.edit', $kunjungan->id) }}"
                                   class="w-7 h-7 md:w-8 md:h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors" title="Edit">
                                    <i class="fas fa-edit text-[10px] md:text-xs"></i>
                                </a>
                                @endif
                                @if(Auth::user()->hasPermission('buku-tamu.delete'))
                                <button onclick="hapusData({{ $kunjungan->id }})"
                                        class="w-7 h-7 md:w-8 md:h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                                    <i class="fas fa-trash text-[10px] md:text-xs"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
{{-- Filter Modal --}}
<div id="filterModal" class="fixed inset-0 z-50 hidden" style="background:rgba(15,23,42,0.5);backdrop-filter:blur(4px);">
    <div class="flex items-end md:items-center justify-center min-h-screen p-0 md:p-4">
        <div class="bg-white rounded-t-2xl md:rounded-2xl shadow-2xl max-w-sm w-full transform transition-all duration-300 modal-mobile animate-fade md:animate-fade">
            <div class="bg-gradient-to-r from-violet-500 to-purple-600 px-4 md:px-5 py-3 md:py-4 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="fas fa-filter text-white text-sm"></i>
                        </div>
                        <h3 class="text-sm font-bold text-white">Filter Tamu</h3>
                    </div>
                    <button onclick="closeFilterModal()" class="w-8 h-8 rounded-lg bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            <div class="p-5 space-y-4">
                {{-- Status --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status Kunjungan</label>
                    <select id="filterStatus"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        <option value="">Semua Status</option>
                        <option value="berkunjung" {{ request('status') == 'berkunjung' ? 'selected' : '' }}>Sedang Berkunjung</option>
                        <option value="pulang" {{ request('status') == 'pulang' ? 'selected' : '' }}>Sudah Pulang</option>
                    </select>
                </div>
                {{-- Tipe --}}
                <div>
                    <label class="block text-xs font-semibold text-gray-600 mb-1.5">Tipe Tamu</label>
                    <select id="filterTipe"
                            class="w-full px-4 py-2.5 text-sm border border-gray-200 rounded-xl bg-gray-50 focus:ring-2 focus:ring-violet-500 focus:border-transparent">
                        <option value="">Semua Tipe</option>
                        <option value="anggota" {{ request('tipe_tamu') == 'anggota' ? 'selected' : '' }}>Anggota</option>
                        <option value="umum" {{ request('tipe_tamu') == 'umum' ? 'selected' : '' }}>Tamu Umum</option>
                    </select>
                </div>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end gap-2">
                @if(request()->hasAny(['search', 'status', 'tipe_tamu']))
                    <a href="/admin/buku-tamu"
                       class="px-4 py-2 text-sm font-medium text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-xl transition-all">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                @endif
                <button type="button" onclick="applyFilter()"
                        class="px-5 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white text-sm font-bold rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-check mr-1"></i> Terapkan
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Search with debounce
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    let searchTimeout;

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            const val = this.value;
            searchTimeout = setTimeout(() => applyFilter(), 500);
        });
    }

    // Enter key on search also triggers filter
    if (searchInput) {
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') applyFilter();
        });
    }
});

// Filter modal
function openFilterModal() {
    document.getElementById('filterModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeFilterModal() {
    document.getElementById('filterModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// Close modal on outside click
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('filterModal');
    if (modal) {
        modal.addEventListener('click', function(e) {
            if (e.target === this) closeFilterModal();
        });
    }
});

function applyFilter() {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value.trim();
    const status = document.getElementById('filterStatus').value;
    const tipe = document.getElementById('filterTipe').value;

    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (tipe) params.set('tipe_tamu', tipe);

    window.location.href = '/admin/buku-tamu' + (params.toString() ? '?' + params.toString() : '');
}

// Record exit
window.recordExit = async function(kunjunganId) {
    const result = await Swal.fire({
        title: 'Catat Waktu Pulang?',
        text: 'Tamu ini akan dicatat sudah pulang.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#8b5cf6',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-sign-out-alt mr-1"></i> Ya, Pulangkan',
        cancelButtonText: 'Batal',
        reverseButtons: true
    });

    if (!result.isConfirmed) return;

    Swal.fire({ title: 'Menyimpan...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

    try {
        const response = await fetch('/admin/buku-tamu/record-exit', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({ kunjungan_id: kunjunganId })
        });

        const data = await response.json();

        if (data.success) {
            await Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false });
            location.reload();
        } else {
            Swal.fire({ icon: 'error', title: 'Gagal!', text: data.message });
        }
    } catch (error) {
        Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan saat mencatat waktu pulang.' });
    }
};

// Delete single
function hapusData(id) {
    Swal.fire({
        title: 'Hapus Data?',
        text: 'Data kunjungan ini akan dihapus permanen.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/admin/buku-tamu/' + id, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Berhasil!', text: data.message, timer: 1500, showConfirmButton: false });
                    location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal!', text: 'Terjadi kesalahan.' });
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error!', text: 'Terjadi kesalahan saat menghapus data.' });
            });
        }
    });
}
</script>
@endpush
