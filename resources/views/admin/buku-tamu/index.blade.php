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
                <div class="relative">
                    <input type="text" id="searchInput" placeholder="Cari tamu..." value="{{ request('search') }}"
                           class="w-52 px-4 py-2.5 pl-10 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 focus:border-transparent bg-white/70 transition-all">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400 text-sm"></i>
                    </div>
                </div>

                {{-- Filter Status --}}
                <select id="filterStatus" onchange="applyFilter()"
                        class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-white/70 focus:ring-2 focus:ring-violet-500">
                    <option value="">Semua Status</option>
                    <option value="berkunjung" {{ request('status') == 'berkunjung' ? 'selected' : '' }}>Sedang Berkunjung</option>
                    <option value="pulang" {{ request('status') == 'pulang' ? 'selected' : '' }}>Sudah Pulang</option>
                </select>

                {{-- Filter Tipe --}}
                <select id="filterTipe" onchange="applyFilter()"
                        class="px-3 py-2.5 text-sm border border-gray-200 rounded-xl bg-white/70 focus:ring-2 focus:ring-violet-500">
                    <option value="">Semua Tipe</option>
                    <option value="anggota" {{ request('tipe_tamu') == 'anggota' ? 'selected' : '' }}>Anggota</option>
                    <option value="umum" {{ request('tipe_tamu') == 'umum' ? 'selected' : '' }}>Tamu Umum</option>
                </select>

                {{-- Tambah --}}
                <a href="{{ route('admin.buku-tamu.create') }}"
                   class="px-4 py-2.5 bg-gradient-to-r from-violet-500 to-purple-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-plus mr-1.5"></i> Tambah
                </a>

                {{-- Riwayat --}}
                <a href="{{ route('admin.buku-tamu.history') }}"
                   class="px-4 py-2.5 bg-white border border-gray-200 hover:border-violet-300 hover:bg-violet-50 text-gray-700 text-sm font-medium rounded-xl transition-all">
                    <i class="fas fa-history mr-1.5"></i> Riwayat
                </a>
            </div>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.05s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-violet-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-violet-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Hari Ini</p>
                    <p class="text-lg font-bold text-gray-900">{{ $totalTamuHariIni }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.1s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-emerald-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Sedang Berkunjung</p>
                    <p class="text-lg font-bold text-gray-900">{{ $sedangBerkunjung }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.15s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-amber-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-sign-out-alt text-amber-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Sudah Pulang</p>
                    <p class="text-lg font-bold text-gray-900">{{ $sudahPulang }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.2s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-id-card text-blue-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Anggota</p>
                    <p class="text-lg font-bold text-gray-900">{{ $tamuAnggota }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-2xl p-4 animate-fade" style="animation-delay:0.25s">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-friends text-purple-600"></i>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Tamu Umum</p>
                    <p class="text-lg font-bold text-gray-900">{{ $tamuUmum }}</p>
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
                <a href="{{ route('admin.buku-tamu.index') }}" class="text-xs text-violet-600 hover:text-violet-800 font-medium">
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
                <a href="{{ route('admin.buku-tamu.create') }}"
                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white text-sm font-medium rounded-xl shadow-md hover:shadow-lg transition-all">
                    <i class="fas fa-plus mr-1.5"></i> Tambah Tamu
                </a>
            </div>
        @else
            <div class="divide-y divide-gray-100">
                @foreach($kunjunganHariIni as $kunjungan)
                <div class="visitor-card px-5 py-4 hover:bg-violet-50/30 transition-colors">
                    <div class="flex items-center justify-between gap-4">
                        {{-- Left: Avatar + Info --}}
                        <div class="flex items-center gap-4 min-w-0 flex-1">
                            <div class="relative flex-shrink-0">
                                <img src="{{ $kunjungan->anggota && $kunjungan->anggota->foto ? asset('storage/anggota/' . $kunjungan->anggota->foto) : asset('images/default-avatar.png') }}"
                                     alt="Foto" class="w-12 h-12 rounded-xl object-cover border-2 border-gray-200"
                                     onerror="this.src='{{ asset('images/default-avatar.png') }}'">
                                @if(!$kunjungan->waktu_pulang)
                                    <span class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 rounded-full border-2 border-white"></span>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h4 class="text-sm font-semibold text-gray-900 truncate">{{ $kunjungan->nama_tamu ?? ($kunjungan->anggota ? $kunjungan->anggota->nama_lengkap : '-') }}</h4>
                                    @if($kunjungan->anggota_id)
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Anggota
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium bg-purple-50 text-purple-700 border border-purple-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Umum
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
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
                                @if($kunjungan->keperluan)
                                    <p class="text-xs text-violet-600 mt-0.5">
                                        <i class="fas fa-tag mr-1"></i>{{ $kunjungan->keperluan }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Center: Time Info --}}
                        <div class="hidden md:flex items-center gap-4 text-center flex-shrink-0">
                            <div>
                                <p class="text-xs text-gray-400">Datang</p>
                                <p class="text-sm font-semibold text-gray-800 font-mono">{{ $kunjungan->waktu_datang->format('H:i') }}</p>
                            </div>
                            <i class="fas fa-arrow-right text-gray-300 text-xs"></i>
                            <div>
                                <p class="text-xs text-gray-400">Pulang</p>
                                @if($kunjungan->waktu_pulang)
                                    <p class="text-sm font-semibold text-gray-800 font-mono">{{ $kunjungan->waktu_pulang->format('H:i') }}</p>
                                @else
                                    <p class="text-sm text-gray-400">--:--</p>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Status + Actions --}}
                        <div class="flex items-center gap-3 flex-shrink-0">
                            @if($kunjungan->waktu_pulang)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span> Pulang
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Berkunjung
                                </span>
                            @endif

                            <div class="flex items-center gap-1">
                                @if(!$kunjungan->waktu_pulang)
                                    <button onclick="recordExit({{ $kunjungan->id }})"
                                            class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Catat Pulang">
                                        <i class="fas fa-sign-out-alt text-xs"></i>
                                    </button>
                                @endif
                                <a href="{{ route('admin.buku-tamu.show', $kunjungan->id) }}"
                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                @if(!Auth::user()->isKepalaSekolah())
                                <a href="{{ route('admin.buku-tamu.edit', $kunjungan->id) }}"
                                   class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                @endif
                                @if(!Auth::user()->isKepalaSekolah())
                                <button onclick="hapusData({{ $kunjungan->id }})"
                                        class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
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
});

function applyFilter() {
    const params = new URLSearchParams();
    const search = document.getElementById('searchInput').value.trim();
    const status = document.getElementById('filterStatus').value;
    const tipe = document.getElementById('filterTipe').value;

    if (search) params.set('search', search);
    if (status) params.set('status', status);
    if (tipe) params.set('tipe_tamu', tipe);

    window.location.href = '{{ route("admin.buku-tamu.index") }}' + (params.toString() ? '?' + params.toString() : '');
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
        const response = await fetch('{{ route("admin.buku-tamu.record-exit") }}', {
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
