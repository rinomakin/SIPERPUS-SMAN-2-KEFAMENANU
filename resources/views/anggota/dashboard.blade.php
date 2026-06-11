@extends('layouts.anggota')

@section('title', 'Dashboard Anggota')

@push('styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes pulse-slow {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.7; }
    }
    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out forwards;
    }
    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }

    .stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px -8px rgba(0, 0, 0, 0.15);
    }

    .book-item {
        transition: all 0.2s ease;
    }
    .book-item:hover {
        transform: translateX(4px);
    }

    .glass-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    html[data-theme="dark"] .glass-card {
        background: rgba(22, 32, 51, 0.92) !important;
        border-color: rgba(99,102,241,0.18) !important;
    }
    html[data-theme="dark"] .stat-card {
        background: rgba(22, 32, 51, 0.92) !important;
        border-color: rgba(99,102,241,0.18) !important;
    }
    html[data-theme="dark"] .text-gray-800 { color: #f1f5f9 !important; }
    html[data-theme="dark"] .text-gray-700 { color: #e2e8f0 !important; }
    html[data-theme="dark"] .text-gray-600 { color: #cbd5e1 !important; }
    html[data-theme="dark"] .text-gray-500 { color: #94a3b8 !important; }
    html[data-theme="dark"] .text-gray-400 { color: #64748b !important; }
    html[data-theme="dark"] .bg-gray-50 { background: #1e293b !important; }
    html[data-theme="dark"] .border-gray-100 { border-color: rgba(148,163,184,0.1) !important; }
    html[data-theme="dark"] .border-gray-200 { border-color: rgba(148,163,184,0.15) !important; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- Welcome Hero --}}
    <div class="relative overflow-hidden rounded-2xl shadow-lg animate-fade-in-up">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-600 via-indigo-600 to-purple-700"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-white/5 rounded-full -translate-y-1/2 translate-x-1/3"></div>
        <div class="absolute bottom-0 left-0 w-64 h-64 bg-white/5 rounded-full translate-y-1/2 -translate-x-1/4"></div>

        <div class="relative z-10 px-6 py-8 md:px-8 md:py-10">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                    @php
                        $hour = now()->format('H');
                        if ($hour < 12) $greeting = 'Selamat Pagi';
                        elseif ($hour < 15) $greeting = 'Selamat Siang';
                        elseif ($hour < 18) $greeting = 'Selamat Sore';
                        else $greeting = 'Selamat Malam';
                    @endphp
                    <p class="text-blue-200 text-sm font-medium mb-1">{{ $greeting }},</p>
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">
                        {{ $anggota->nama_lengkap ?? Auth::user()->nama_lengkap }}
                    </h2>
                    <p class="text-blue-100 text-base">
                        Selamat datang di Portal Anggota {{ $pengaturan->nama_website ?? 'SIPERPUS' }}
                    </p>
                    <div class="flex items-center mt-3 space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">
                            <i class="fas fa-calendar-alt mr-1.5"></i>
                            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </span>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-white/15 text-white backdrop-blur-sm">
                            <i class="fas fa-clock mr-1.5"></i>
                            <span id="liveClock">{{ now()->format('H:i') }}</span>
                        </span>
                    </div>
                </div>
                <div class="mt-4 md:mt-0">
                    <span class="inline-flex items-center px-4 py-2 bg-white/20 text-white rounded-xl text-sm font-medium backdrop-blur-sm border border-white/20">
                        <i class="fas fa-id-card mr-2"></i>
                        {{ $anggota->nomor_anggota ?? '---' }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Profile Info Card --}}
    @if($anggota)
    <div class="glass-card rounded-2xl p-6 border border-gray-100 animate-fade-in-up stagger-1">
        <div class="flex items-start justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl overflow-hidden flex-shrink-0" style="background: linear-gradient(135deg, #3b82f6, #6366f1);">
                    @if($anggota->foto && file_exists(public_path('storage/' . $anggota->foto)))
                        <img src="{{ asset('storage/' . $anggota->foto) }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white text-2xl font-bold">
                            {{ strtoupper(substr($anggota->nama_lengkap ?? 'A', 0, 1)) }}
                        </div>
                    @endif
                </div>
                <div>
                    <h3 class="text-lg font-bold text-gray-800">{{ $anggota->nama_lengkap }}</h3>
                    <div class="flex flex-wrap items-center gap-2 mt-1">
                        <span class="text-xs text-gray-500"><i class="fas fa-id-badge text-blue-500 mr-1"></i>{{ $anggota->nomor_anggota }}</span>
                        @if($anggota->kelas)
                            <span class="text-xs text-gray-500"><i class="fas fa-graduation-cap text-blue-500 mr-1"></i>{{ $anggota->kelas->nama_kelas ?? $anggota->kelas->tingkat_kelas ?? '' }}</span>
                        @endif
                        @if($anggota->jenis_kelamin)
                            <span class="text-xs text-gray-500"><i class="fas fa-venus-mars text-blue-500 mr-1"></i>{{ $anggota->jenis_kelamin == 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $anggota->status == 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ ucfirst($anggota->status) }}
            </span>
        </div>
    </div>
    @endif

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-fade-in-up stagger-2">
        <div class="stat-card rounded-2xl p-5 border border-gray-100" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(59,130,246,0.1);">
                    <i class="fas fa-book-open text-blue-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $peminjamanAktif->count() }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Buku Sedang Dipinjam</p>
        </div>

        <div class="stat-card rounded-2xl p-5 border border-gray-100" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(16,185,129,0.1);">
                    <i class="fas fa-history text-emerald-600"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $totalPernahDipinjam }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Pernah Dipinjam</p>
        </div>

        <div class="stat-card rounded-2xl p-5 border border-gray-100" style="background: rgba(255,255,255,0.85); backdrop-filter: blur(10px);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(239,68,68,0.1);">
                    <i class="fas fa-money-bill-wave text-red-500"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Denda Belum Dibayar</p>
        </div>

        <div class="stat-card rounded-2xl p-5 border border-gray-100" style="background: rgba(245,158,11,0.1);">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background: rgba(245,158,11,0.1);">
                    <i class="fas fa-star text-amber-500"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-800">{{ $bukuPopuler->first()->judul_buku ?? '-' }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Buku Terpopuler</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-fade-in-up stagger-3">
        {{-- Currently Borrowed Books --}}
        <div class="lg:col-span-2 glass-card rounded-2xl border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(59,130,246,0.1);">
                        <i class="fas fa-hand-holding text-blue-600 text-xs"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 text-sm">Buku Sedang Dipinjam</h3>
                </div>
                @if($peminjamanAktif->count() > 0)
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                        {{ $peminjamanAktif->count() }} aktif
                    </span>
                @endif
            </div>
            <div class="p-6">
                @if($peminjamanAktif->count() > 0)
                    <div class="space-y-3">
                        @foreach($peminjamanAktif as $peminjaman)
                            @foreach($peminjaman->detailPeminjaman as $detail)
                            <div class="book-item flex items-center gap-4 p-3 rounded-xl bg-gray-50">
                                <div class="w-10 h-14 rounded-lg overflow-hidden flex-shrink-0 bg-white border border-gray-200">
                                    @if($detail->buku && $detail->buku->gambar_sampul)
                                        <img src="{{ asset($detail->buku->gambar_sampul) }}" alt="{{ $detail->buku->judul_buku }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                                            <i class="fas fa-book text-lg"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold text-gray-800 truncate">{{ $detail->buku->judul_buku ?? 'Buku tidak ditemukan' }}</p>
                                    <p class="text-xs text-gray-500">{{ $detail->buku->pengarang ?? '' }}</p>
                                    <div class="flex items-center gap-3 mt-1">
                                        <span class="text-[10px] text-gray-400">
                                            <i class="fas fa-calendar text-blue-400 mr-0.5"></i>
                                            Pinjam: {{ \Carbon\Carbon::parse($peminjaman->tanggal_peminjaman)->format('d/m/Y') }}
                                        </span>
                                        <span class="text-[10px] {{ now() > \Carbon\Carbon::parse($peminjaman->tanggal_harus_kembali) ? 'text-red-500 font-semibold animate-pulse' : 'text-gray-400' }}">
                                            <i class="fas fa-clock mr-0.5"></i>
                                            @php
                                                $tenggat = \Carbon\Carbon::parse($peminjaman->tanggal_harus_kembali);
                                            @endphp
                                            @if(now() > $tenggat)
                                                Terlambat {{ now()->diffInDays($tenggat) }} hari
                                            @else
                                                Kembali: {{ $tenggat->format('d/m/Y') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                <div class="flex-shrink-0">
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold
                                        {{ now() > \Carbon\Carbon::parse($peminjaman->tanggal_harus_kembali) ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                        {{ now() > \Carbon\Carbon::parse($peminjaman->tanggal_harus_kembali) ? 'Terlambat' : 'Tepat Waktu' }}
                                    </span>
                                </div>
                            </div>
                            @endforeach
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-10">
                        <div class="w-16 h-16 rounded-2xl mx-auto flex items-center justify-center mb-3" style="background: rgba(59,130,246,0.08);">
                            <i class="fas fa-book-open text-blue-300 text-2xl"></i>
                        </div>
                        <p class="text-gray-500 font-medium">Tidak ada buku yang sedang dipinjam</p>
                        <p class="text-xs text-gray-400 mt-1">Kunjungi perpustakaan untuk meminjam buku</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Right Sidebar: Popular Books + Denda --}}
        <div class="space-y-6">
            {{-- Popular Books --}}
            <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(245,158,11,0.1);">
                        <i class="fas fa-fire text-amber-500 text-xs"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 text-sm">Buku Terpopuler</h3>
                </div>
                <div class="p-4">
                    @if($bukuPopuler->count() > 0)
                        <div class="space-y-2">
                            @foreach($bukuPopuler as $index => $buku)
                            <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-gray-50 transition-colors">
                                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold
                                    {{ $index == 0 ? 'bg-amber-100 text-amber-700' : ($index == 1 ? 'bg-gray-100 text-gray-600' : ($index == 2 ? 'bg-orange-100 text-orange-700' : 'bg-gray-50 text-gray-400')) }}">
                                    {{ $index + 1 }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-semibold text-gray-800 truncate">{{ $buku->judul_buku }}</p>
                                    <p class="text-[10px] text-gray-400">{{ $buku->pengarang ?? '' }} · {{ $buku->total_dipinjam ?? 0 }}x dipinjam</p>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-xs text-gray-400 text-center py-4">Belum ada data buku populer</p>
                    @endif
                </div>
            </div>

            {{-- Denda Summary --}}
            <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(239,68,68,0.1);">
                        <i class="fas fa-exclamation-triangle text-red-500 text-xs"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800 text-sm">Denda</h3>
                </div>
                <div class="p-4">
                    @if($totalDenda > 0)
                        <div class="text-center">
                            <p class="text-2xl font-bold text-red-500">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 mt-1">Total denda yang perlu dibayar</p>
                            <div class="mt-3 px-3 py-2 rounded-xl bg-red-50 text-xs text-red-600">
                                <i class="fas fa-info-circle mr-1"></i>
                                Segera lunasi denda di perpustakaan
                            </div>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <div class="w-10 h-10 rounded-xl mx-auto flex items-center justify-center mb-2" style="background: rgba(16,185,129,0.1);">
                                <i class="fas fa-check-circle text-emerald-500"></i>
                            </div>
                            <p class="text-xs font-medium text-gray-600">Tidak ada denda</p>
                            <p class="text-[10px] text-gray-400 mt-0.5">Anda tertib dalam meminjam buku</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Peminjaman --}}
    @if($riwayatPeminjaman->count() > 0)
    <div class="glass-card rounded-2xl border border-gray-100 overflow-hidden animate-fade-in-up stagger-4">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center gap-2">
            <div class="w-8 h-8 rounded-lg flex items-center justify-center" style="background: rgba(16,185,129,0.1);">
                <i class="fas fa-clock text-emerald-600 text-xs"></i>
            </div>
            <h3 class="font-semibold text-gray-800 text-sm">Riwayat Peminjaman</h3>
        </div>
        <div class="p-6">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs text-gray-500 border-b border-gray-100">
                            <th class="text-left py-2 px-2 font-medium">Buku</th>
                            <th class="text-left py-2 px-2 font-medium">Tanggal Pinjam</th>
                            <th class="text-left py-2 px-2 font-medium">Tanggal Kembali</th>
                            <th class="text-left py-2 px-2 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($riwayatPeminjaman as $peminjaman)
                            @foreach($peminjaman->detailPeminjaman as $detail)
                            <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                                <td class="py-2.5 px-2">
                                    <p class="font-medium text-gray-800 text-xs">{{ $detail->buku->judul_buku ?? '-' }}</p>
                                </td>
                                <td class="py-2.5 px-2 text-xs text-gray-500">
                                    {{ \Carbon\Carbon::parse($peminjaman->tanggal_peminjaman)->format('d/m/Y') }}
                                </td>
                                <td class="py-2.5 px-2 text-xs text-gray-500">
                                    {{ $peminjaman->tanggal_kembali ? \Carbon\Carbon::parse($peminjaman->tanggal_kembali)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2.5 px-2">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold
                                        {{ $peminjaman->status == 'selesai' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($peminjaman->status) }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<script>
(function() {
    function updateClock() {
        var now = new Date();
        var hours = String(now.getHours()).padStart(2, '0');
        var minutes = String(now.getMinutes()).padStart(2, '0');
        var seconds = String(now.getSeconds()).padStart(2, '0');
        var el = document.getElementById('liveClock');
        if (el) el.textContent = hours + ':' + minutes + ':' + seconds;
    }
    updateClock();
    setInterval(updateClock, 1000);
})();
</script>
@endsection
