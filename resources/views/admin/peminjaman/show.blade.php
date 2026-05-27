@extends('layouts.admin')

@section('title', 'Detail Peminjaman')

@push('styles')
<style>
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(18px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes scaleIn {
        from { opacity: 0; transform: scale(.92); }
        to { opacity: 1; transform: scale(1); }
    }
    .anim-up { animation: fadeInUp .5s ease-out forwards; opacity: 0; }
    .anim-scale { animation: scaleIn .45s ease-out forwards; opacity: 0; }
    .d1 { animation-delay: .05s; }
    .d2 { animation-delay: .12s; }
    .d3 { animation-delay: .19s; }
    .d4 { animation-delay: .26s; }
    .d5 { animation-delay: .33s; }

    .info-card {
        transition: all .25s cubic-bezier(.4,0,.2,1);
    }
    .info-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px -8px rgba(0,0,0,.1);
    }

    .book-card {
        transition: all .25s cubic-bezier(.4,0,.2,1);
    }
    .book-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 28px -8px rgba(0,0,0,.12);
        border-color: #c7d2fe;
    }

    .timeline-dot {
        width: 10px;
        height: 10px;
        border-radius: 50%;
        border: 2px solid #fff;
        box-shadow: 0 0 0 2px;
        flex-shrink: 0;
    }

    .detail-label {
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: .05em;
        color: #94a3b8;
        margin-bottom: 4px;
    }
</style>
@endpush

@section('content')
@php
    $now = \Carbon\Carbon::now();
    $batasKembali = $peminjaman->tanggal_harus_kembali;
    $isOverdue = $now->gt($batasKembali) && $peminjaman->status !== 'dikembalikan';
    $daysOverdue = $isOverdue ? $now->diffInDays($batasKembali) : 0;
    $daysLeft = !$isOverdue && $peminjaman->status === 'dipinjam' ? $now->diffInDays($batasKembali, false) : 0;
    $totalHari = $peminjaman->tanggal_peminjaman->diffInDays($batasKembali);

    $statusConfig = [
        'dipinjam' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-700', 'border' => 'border-amber-200', 'dot' => 'bg-amber-400', 'icon' => 'fa-hourglass-half', 'label' => 'Dipinjam'],
        'dikembalikan' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'border' => 'border-emerald-200', 'dot' => 'bg-emerald-400', 'icon' => 'fa-check-circle', 'label' => 'Dikembalikan'],
        'terlambat' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-700', 'border' => 'border-rose-200', 'dot' => 'bg-rose-400', 'icon' => 'fa-exclamation-circle', 'label' => 'Terlambat'],
    ];
    $sc = $statusConfig[$peminjaman->status] ?? $statusConfig['dipinjam'];
@endphp

<div class="space-y-6">

    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 anim-up">
        <div class="flex items-center gap-3">
            <a href="{{ route('peminjaman.index') }}"
               class="w-10 h-10 rounded-xl bg-white border border-gray-200 flex items-center justify-center text-gray-500 hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                <i class="fas fa-arrow-left text-sm"></i>
            </a>
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Detail Peminjaman</h2>
                <p class="text-sm text-gray-500 mt-0.5">{{ $peminjaman->nomor_peminjaman }}</p>
            </div>
        </div>
        <div class="flex items-center gap-2.5">
            @if(Auth::user()->hasPermission('peminjaman.edit') || Auth::user()->isAdmin())
            <a href="{{ route('peminjaman.edit', $peminjaman->id) }}"
               class="inline-flex items-center px-4 py-2.5 bg-white border border-gray-200 text-gray-700 rounded-xl text-sm font-medium hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm">
                <i class="fas fa-pen mr-2 text-gray-400"></i>Edit
            </a>
            @endif
            <a href="{{ route('peminjaman.index') }}"
               class="inline-flex items-center px-4 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white rounded-xl text-sm font-medium transition-all shadow-md shadow-blue-500/25">
                <i class="fas fa-list mr-2"></i>Semua Peminjaman
            </a>
        </div>
    </div>

    <!-- Status Banner -->
    @if($isOverdue)
    <div class="anim-up d1 bg-gradient-to-r from-rose-500 to-red-600 rounded-2xl p-5 text-white shadow-lg shadow-rose-500/20">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Peminjaman Terlambat!</h3>
                    <p class="text-rose-100 text-sm">Terlambat {{ $daysOverdue }} hari dari batas pengembalian</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-black">{{ $daysOverdue }}</p>
                <p class="text-rose-200 text-xs uppercase tracking-wide font-medium">Hari Terlambat</p>
            </div>
        </div>
    </div>
    @elseif($peminjaman->status === 'dipinjam')
    <div class="anim-up d1 bg-gradient-to-r from-amber-400 to-orange-500 rounded-2xl p-5 text-white shadow-lg shadow-amber-500/20">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Sedang Dipinjam</h3>
                    <p class="text-amber-100 text-sm">Harus dikembalikan sebelum {{ $batasKembali->translatedFormat('d F Y') }}</p>
                </div>
            </div>
            <div class="text-right">
                <p class="text-3xl font-black">{{ max($daysLeft, 0) }}</p>
                <p class="text-amber-200 text-xs uppercase tracking-wide font-medium">Hari Tersisa</p>
            </div>
        </div>
    </div>
    @elseif($peminjaman->status === 'dikembalikan')
    <div class="anim-up d1 bg-gradient-to-r from-emerald-500 to-green-600 rounded-2xl p-5 text-white shadow-lg shadow-emerald-500/20">
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-xl bg-white/20 flex items-center justify-center backdrop-blur-sm">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-lg">Sudah Dikembalikan</h3>
                    <p class="text-emerald-100 text-sm">
                        Dikembalikan pada {{ $peminjaman->tanggal_kembali ? $peminjaman->tanggal_kembali->translatedFormat('d F Y') : '-' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <i class="fas fa-check text-emerald-200 text-2xl"></i>
            </div>
        </div>
    </div>
    @endif

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left Column: Loan Info + Timeline -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Loan Details Card -->
            <div class="info-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden anim-up d2">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-500 text-sm"></i>
                        </div>
                        <h3 class="font-bold text-gray-900">Informasi Peminjaman</h3>
                    </div>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-5">
                        <!-- Nomor -->
                        <div>
                            <p class="detail-label">Nomor Peminjaman</p>
                            <p class="text-sm font-bold text-gray-900">{{ $peminjaman->nomor_peminjaman }}</p>
                        </div>
                        <!-- Jumlah Buku -->
                        <div>
                            <p class="detail-label">Jumlah Buku</p>
                            <div class="flex items-center gap-1.5">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-lg bg-violet-50 text-violet-700 font-bold text-sm">{{ $peminjaman->jumlah_buku ?? $peminjaman->detailPeminjaman->count() }}</span>
                                <span class="text-xs text-gray-500">buku</span>
                            </div>
                        </div>
                        <!-- Status -->
                        <div>
                            <p class="detail-label">Status</p>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $sc['bg'] }} {{ $sc['text'] }} border {{ $sc['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ $sc['dot'] }} mr-1.5 {{ $peminjaman->status === 'terlambat' ? 'animate-pulse' : '' }}"></span>
                                {{ $sc['label'] }}
                            </span>
                        </div>
                        <!-- Petugas -->
                        <div>
                            <p class="detail-label">Petugas</p>
                            <div class="flex items-center gap-2">
                                <div class="w-6 h-6 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-user text-white" style="font-size:8px"></i>
                                </div>
                                <span class="text-sm font-medium text-gray-800">{{ $peminjaman->user->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Divider -->
                    <div class="border-t border-dashed border-gray-200 my-5"></div>

                    <!-- Timeline -->
                    <div class="relative">
                        <p class="detail-label mb-4">Timeline Peminjaman</p>
                        <div class="space-y-4">
                            <!-- Tanggal Pinjam -->
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="timeline-dot bg-blue-500" style="color: rgba(59,130,246,.3)"></div>
                                    <div class="w-px h-full bg-gray-200 mt-1 min-h-[20px]"></div>
                                </div>
                                <div class="pb-2">
                                    <p class="text-sm font-semibold text-gray-900">Dipinjam</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $peminjaman->tanggal_peminjaman->translatedFormat('l, d F Y') }}
                                        @if($peminjaman->jam_peminjaman)
                                            <span class="text-gray-400">pukul {{ $peminjaman->jam_peminjaman->format('H:i') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Batas Kembali -->
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="timeline-dot {{ $isOverdue ? 'bg-rose-500' : 'bg-amber-500' }}" style="color: {{ $isOverdue ? 'rgba(244,63,94,.3)' : 'rgba(245,158,11,.3)' }}"></div>
                                    @if($peminjaman->tanggal_kembali)
                                    <div class="w-px h-full bg-gray-200 mt-1 min-h-[20px]"></div>
                                    @endif
                                </div>
                                <div class="pb-2">
                                    <p class="text-sm font-semibold {{ $isOverdue ? 'text-rose-600' : 'text-gray-900' }}">
                                        Batas Pengembalian
                                        @if($isOverdue)
                                            <span class="text-xs font-medium text-rose-500 ml-1">(Terlewat!)</span>
                                        @endif
                                    </p>
                                    <p class="text-sm {{ $isOverdue ? 'text-rose-600' : 'text-gray-600' }}">
                                        {{ $batasKembali->translatedFormat('l, d F Y') }}
                                        @if($peminjaman->jam_kembali && !$peminjaman->tanggal_kembali)
                                            <span class="text-gray-400">pukul {{ $peminjaman->jam_kembali->format('H:i') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            <!-- Tanggal Dikembalikan -->
                            @if($peminjaman->tanggal_kembali)
                            <div class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="timeline-dot bg-emerald-500" style="color: rgba(16,185,129,.3)"></div>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-emerald-700">Dikembalikan</p>
                                    <p class="text-sm text-gray-600">
                                        {{ $peminjaman->tanggal_kembali->translatedFormat('l, d F Y') }}
                                        @if($peminjaman->jam_kembali)
                                            <span class="text-gray-400">pukul {{ $peminjaman->jam_kembali->format('H:i') }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Catatan -->
                    @if($peminjaman->catatan)
                    <div class="border-t border-dashed border-gray-200 mt-5 pt-5">
                        <p class="detail-label mb-2">Catatan</p>
                        <div class="bg-gray-50 rounded-xl px-4 py-3 border border-gray-100">
                            <p class="text-sm text-gray-700 leading-relaxed">{{ $peminjaman->catatan }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Books List -->
            <div class="info-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden anim-up d4">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center">
                                <i class="fas fa-book text-emerald-500 text-sm"></i>
                            </div>
                            <h3 class="font-bold text-gray-900">Buku yang Dipinjam</h3>
                        </div>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                            {{ $peminjaman->detailPeminjaman->count() }} Buku
                        </span>
                    </div>
                </div>
                <div class="p-5">
                    <div class="space-y-3">
                        @forelse($peminjaman->detailPeminjaman as $idx => $detail)
                        <div class="book-card flex items-start gap-4 p-4 rounded-xl border border-gray-100 bg-white anim-scale" style="animation-delay: {{ 0.35 + ($idx * 0.06) }}s">
                            <!-- Book Cover -->
                            <div class="w-14 h-[72px] rounded-lg bg-gradient-to-br from-indigo-100 to-blue-50 flex items-center justify-center flex-shrink-0 overflow-hidden border border-indigo-100/50">
                                @if($detail->buku && $detail->buku->gambar_sampul)
                                    <img src="{{ asset('uploads/' . $detail->buku->gambar_sampul) }}"
                                         alt="{{ $detail->buku->judul_buku }}"
                                         class="w-full h-full object-cover">
                                @else
                                    <i class="fas fa-book text-indigo-300 text-lg"></i>
                                @endif
                            </div>

                            <!-- Book Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <h5 class="font-semibold text-gray-900 text-sm leading-snug truncate">{{ $detail->buku->judul_buku ?? 'N/A' }}</h5>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ $detail->buku->pengarang ?? $detail->buku->penulis ?? 'Penulis tidak diketahui' }}</p>
                                    </div>
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-lg bg-blue-50 text-blue-700 text-xs font-bold flex-shrink-0 border border-blue-100/60">
                                        {{ $detail->jumlah ?? 1 }}x
                                    </span>
                                </div>

                                <div class="flex items-center flex-wrap gap-2 mt-2.5">
                                    @if($detail->buku && $detail->buku->isbn)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-gray-50 text-gray-500 text-xs border border-gray-100">
                                        <i class="fas fa-barcode mr-1 text-gray-400"></i>{{ $detail->buku->isbn }}
                                    </span>
                                    @endif

                                    @if($detail->buku && $detail->buku->kategoriBuku)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-xs border border-indigo-100/60">
                                        <i class="fas fa-folder mr-1"></i>{{ $detail->buku->kategoriBuku->nama_kategori }}
                                    </span>
                                    @elseif($detail->buku && $detail->buku->kategori)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-indigo-50 text-indigo-600 text-xs border border-indigo-100/60">
                                        <i class="fas fa-folder mr-1"></i>{{ $detail->buku->kategori->nama_kategori }}
                                    </span>
                                    @endif

                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs border
                                        {{ $detail->kondisi_kembali === 'baik' ? 'bg-emerald-50 text-emerald-600 border-emerald-100/60' : 'bg-amber-50 text-amber-600 border-amber-100/60' }}">
                                        <i class="fas {{ $detail->kondisi_kembali === 'baik' ? 'fa-check-circle' : 'fa-info-circle' }} mr-1"></i>{{ ucfirst($detail->kondisi_kembali ?? 'Baik') }}
                                    </span>
                                </div>

                                @if($detail->catatan)
                                <p class="text-xs text-gray-400 mt-2 italic leading-relaxed bg-gray-50 rounded-lg px-2.5 py-1.5">"{{ $detail->catatan }}"</p>
                                @endif
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto rounded-full bg-gray-100 flex items-center justify-center mb-3">
                                <i class="fas fa-book-open text-gray-400 text-xl"></i>
                            </div>
                            <p class="text-gray-500 text-sm font-medium">Tidak ada buku dipinjam</p>
                            <p class="text-gray-400 text-xs mt-1">Belum ada buku dalam peminjaman ini</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Member Info + Summary -->
        <div class="space-y-6">

            <!-- Member Card -->
            <div class="info-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden anim-up d3">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center">
                            <i class="fas fa-user text-violet-500 text-sm"></i>
                        </div>
                        <h3 class="font-bold text-gray-900">Data Anggota</h3>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Avatar + Name -->
                    <div class="flex flex-col items-center text-center mb-5">
                        <div class="w-20 h-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center overflow-hidden shadow-lg shadow-blue-500/20 mb-3">
                            @if($peminjaman->anggota && $peminjaman->anggota->foto)
                                <img src="{{ asset('storage/anggota/' . $peminjaman->anggota->foto) }}"
                                     alt="{{ $peminjaman->anggota->nama_lengkap }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.parentElement.innerHTML='<span class=\'text-white text-2xl font-bold\'>{{ $peminjaman->anggota ? strtoupper(substr($peminjaman->anggota->nama_lengkap, 0, 1)) : '?' }}</span>'">
                            @else
                                <span class="text-white text-2xl font-bold">
                                    {{ $peminjaman->anggota ? strtoupper(substr($peminjaman->anggota->nama_lengkap, 0, 1)) : '?' }}
                                </span>
                            @endif
                        </div>
                        <h4 class="font-bold text-gray-900 text-lg leading-tight">{{ $peminjaman->anggota->nama_lengkap ?? 'N/A' }}</h4>
                        <p class="text-xs text-gray-400 mt-0.5">{{ $peminjaman->anggota->nomor_anggota ?? '' }}</p>
                    </div>

                    <!-- Info List -->
                    <div class="space-y-3">
                        @if($peminjaman->anggota && $peminjaman->anggota->kelas)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-chalkboard text-blue-400 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">Kelas</p>
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $peminjaman->anggota->kelas->nama_kelas }}</p>
                            </div>
                        </div>
                        @endif

                        @if($peminjaman->anggota && $peminjaman->anggota->email)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-envelope text-emerald-400 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">Email</p>
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $peminjaman->anggota->email }}</p>
                            </div>
                        </div>
                        @endif

                        @if($peminjaman->anggota && $peminjaman->anggota->jenis_anggota)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-id-badge text-amber-400 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">Jenis Anggota</p>
                                <p class="text-sm font-medium text-gray-800 truncate">{{ ucfirst($peminjaman->anggota->jenis_anggota) }}</p>
                            </div>
                        </div>
                        @endif

                        @if($peminjaman->anggota && $peminjaman->anggota->nomor_telepon)
                        <div class="flex items-center gap-3 px-3 py-2.5 rounded-xl bg-gray-50">
                            <div class="w-8 h-8 rounded-lg bg-violet-50 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-phone text-violet-400 text-xs"></i>
                            </div>
                            <div class="min-w-0">
                                <p class="text-xs text-gray-400">Telepon</p>
                                <p class="text-sm font-medium text-gray-800 truncate">{{ $peminjaman->anggota->nomor_telepon }}</p>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Summary Card -->
            <div class="info-card bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden anim-up d5">
                <div class="px-6 py-4 border-b border-gray-100">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center">
                            <i class="fas fa-chart-pie text-amber-500 text-sm"></i>
                        </div>
                        <h3 class="font-bold text-gray-900">Ringkasan</h3>
                    </div>
                </div>
                <div class="p-5 space-y-3">
                    <!-- Durasi Pinjam -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-blue-50/50 border border-blue-100/50">
                        <span class="text-xs font-medium text-gray-500">Durasi Pinjam</span>
                        <span class="text-sm font-bold text-blue-700">{{ $totalHari }} Hari</span>
                    </div>
                    <!-- Total Eksemplar -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-violet-50/50 border border-violet-100/50">
                        <span class="text-xs font-medium text-gray-500">Total Eksemplar</span>
                        <span class="text-sm font-bold text-violet-700">{{ $peminjaman->detailPeminjaman->sum('jumlah') ?? $peminjaman->jumlah_buku }}</span>
                    </div>
                    <!-- Judul Buku -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-emerald-50/50 border border-emerald-100/50">
                        <span class="text-xs font-medium text-gray-500">Judul Buku</span>
                        <span class="text-sm font-bold text-emerald-700">{{ $peminjaman->detailPeminjaman->count() }} Judul</span>
                    </div>
                    @if($isOverdue)
                    <!-- Hari Terlambat -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-rose-50/50 border border-rose-100/50">
                        <span class="text-xs font-medium text-gray-500">Hari Terlambat</span>
                        <span class="text-sm font-bold text-rose-700">{{ $daysOverdue }} Hari</span>
                    </div>
                    @elseif($peminjaman->status === 'dipinjam')
                    <!-- Sisa Hari -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-amber-50/50 border border-amber-100/50">
                        <span class="text-xs font-medium text-gray-500">Sisa Hari</span>
                        <span class="text-sm font-bold text-amber-700">{{ max($daysLeft, 0) }} Hari</span>
                    </div>
                    @endif

                    @if($peminjaman->denda)
                    <!-- Denda -->
                    <div class="flex items-center justify-between px-3 py-2.5 rounded-xl bg-rose-50/50 border border-rose-200/60">
                        <span class="text-xs font-medium text-gray-500">Denda</span>
                        <span class="text-sm font-bold text-rose-700">Rp {{ number_format($peminjaman->denda->jumlah_denda, 0, ',', '.') }}</span>
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
