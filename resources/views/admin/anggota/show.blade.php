@extends('layouts.admin')

@section('title', 'Detail Anggota - ' . $anggota->nama_lengkap)

@section('content')
<style>
    .glass-card {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
    }
    .stat-card {
        opacity: 0;
        transform: translateY(16px);
        animation: slideUp 0.5s ease forwards;
    }
    .stat-card:nth-child(1) { animation-delay: 0.05s; }
    .stat-card:nth-child(2) { animation-delay: 0.1s; }
    .stat-card:nth-child(3) { animation-delay: 0.15s; }
    .stat-card:nth-child(4) { animation-delay: 0.2s; }
    @keyframes slideUp {
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .fade-in {
        animation: fadeIn 0.4s ease forwards;
    }
    .info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-label {
        font-size: 0.8rem;
        font-weight: 500;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .info-value {
        font-size: 0.875rem;
        font-weight: 500;
        color: #1e293b;
        text-align: right;
    }
    .profile-avatar {
        width: 100px;
        height: 100px;
        border-radius: 20px;
        overflow: hidden;
        flex-shrink: 0;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        border: 3px solid white;
    }
    .profile-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .profile-avatar .avatar-initial {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 2.2rem;
    }
    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 10px;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .action-btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .barcode-box {
        background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
        border: 2px dashed #e2e8f0;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
    }
    @media (max-width: 768px) {
        .profile-avatar {
            width: 72px;
            height: 72px;
            border-radius: 16px;
        }
        .profile-avatar .avatar-initial {
            font-size: 1.6rem;
        }
        .action-btn span.btn-text {
            display: none;
        }
        .action-btn {
            padding: 8px 10px;
        }
    }
</style>

@php
    $gradients = ['#f97316,#ef4444','#8b5cf6,#6366f1','#10b981,#059669','#3b82f6,#2563eb','#ec4899,#db2777'];
    $gradient = $gradients[($anggota->id ?? 0) % 5];
    $initial = strtoupper(substr($anggota->nama_lengkap ?? 'N', 0, 1));

    $totalPeminjaman = $anggota->peminjaman->count();
    $sedangDipinjam = $anggota->peminjaman->where('status', 'dipinjam')->count();
    $dikembalikan = $anggota->peminjaman->where('status', 'dikembalikan')->count();
    $totalDenda = $anggota->denda->sum('jumlah_denda');
    $dendaBelumBayar = $anggota->denda->where('status_pembayaran', 'belum_dibayar')->sum('jumlah_denda');
@endphp

<div class="max-w-5xl mx-auto space-y-6">

    <!-- Profile Header Card -->
    <div class="glass-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in">
        <!-- Gradient Banner -->
        <div class="h-28 sm:h-32 relative" style="background: linear-gradient(135deg, {{ str_replace(',', ', ', $gradient) }});">
            <div class="absolute inset-0 opacity-20">
                <svg class="w-full h-full" viewBox="0 0 800 200" preserveAspectRatio="none">
                    <path d="M0,100 C200,160 400,40 600,120 C700,150 800,80 800,80 L800,200 L0,200 Z" fill="white" opacity="0.3"/>
                    <circle cx="700" cy="50" r="80" fill="white" opacity="0.1"/>
                    <circle cx="100" cy="30" r="60" fill="white" opacity="0.1"/>
                </svg>
            </div>
        </div>

        <!-- Profile Info -->
        <div class="px-3 pb-6 -mt-14 relative">
            <div class="flex flex-col sm:flex-row sm:items-end gap-2">
                <!-- Avatar -->
                <div class="profile-avatar">
                    @if($anggota->foto)
                        <img src="{{ asset('storage/anggota/' . $anggota->foto) }}"
                             alt="{{ $anggota->nama_lengkap }}"
                             onerror="this.style.display='none';this.nextElementSibling.style.display='flex';">
                        <div class="avatar-initial" style="display:none;background:linear-gradient(135deg,{{ $gradient }});">
                            {{ $initial }}
                        </div>
                    @else
                        <div class="avatar-initial" style="background:linear-gradient(135deg,{{ $gradient }});">
                            {{ $initial }}
                        </div>
                    @endif
                </div>

                <!-- Name & Meta -->
                <div class="flex-1 sm:pb-1">
                    <h1 class="text-[10px] font-bold text-gray-900">{{ $anggota->nama_lengkap }}</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-1.5">
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-medium border
                            {{ $anggota->jenis_kelamin == 'Laki-laki' ? 'bg-blue-50 text-blue-700 border-blue-100' : 'bg-pink-50 text-pink-700 border-pink-100' }}">
                            <i class="fas {{ $anggota->jenis_kelamin == 'Laki-laki' ? 'fa-mars' : 'fa-venus' }}"></i>
                            {{ $anggota->jenis_kelamin }}
                        </span>
                        @php
                            $jenisConfig = match($anggota->jenis_anggota) {
                                'siswa' => ['bg-blue-50 text-blue-700 border-blue-100', 'fa-user-graduate'],
                                'guru' => ['bg-emerald-50 text-emerald-700 border-emerald-100', 'fa-chalkboard-teacher'],
                                default => ['bg-purple-50 text-purple-700 border-purple-100', 'fa-user-tie']
                            };
                            $statusConfig = match($anggota->status) {
                                'aktif' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
                                'nonaktif' => ['bg-red-50 text-red-700 border-red-200', 'bg-red-500'],
                                default => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500']
                            };
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[10px] font-medium {{ $jenisConfig[0] }} border">
                            <i class="fas {{ $jenisConfig[1] }} text-[10px]"></i>{{ ucfirst($anggota->jenis_anggota) }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-medium {{ $statusConfig[0] }} border">
                            <span class="w-1.5 h-1.5 rounded-full {{ $statusConfig[1] }}"></span>{{ ucfirst($anggota->status) }}
                        </span>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex items-center gap-2 sm:pb-1">
                    @if(Auth::user()->hasPermission('anggota.edit') || Auth::user()->isAdmin())
                    <a href="{{ route('anggota.edit', $anggota->id) }}" class="action-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-edit text-blue-500"></i>
                        <span class="btn-text">Edit</span>
                    </a>
                    @endif
                    @if(Auth::user()->hasPermission('anggota.cetak-kartu') || Auth::user()->isAdmin())
                    <a href="{{ route('anggota.cetak-kartu', $anggota->id) }}" target="_blank" class="action-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-print text-emerald-500"></i>
                        <span class="btn-text">Cetak Kartu</span>
                    </a>
                    @endif
                    <a href="{{ route('anggota.index') }}" class="action-btn bg-white border border-gray-200 text-gray-700 hover:bg-gray-50">
                        <i class="fas fa-arrow-left text-gray-400"></i>
                        <span class="btn-text">Kembali</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-1.5 sm:gap-2">
        <div class="stat-card glass-card rounded-xl border border-gray-100 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 rounded-bl-[40px] opacity-10" style="background:linear-gradient(135deg, #3b82f6, #2563eb);"></div>
            <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center mb-2.5">
                <i class="fas fa-book text-blue-500 text-[10px]"></i>
            </div>
            <div class="text-[10px] font-bold text-gray-900">{{ $totalPeminjaman }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Total Peminjaman</div>
        </div>

        <div class="stat-card glass-card rounded-xl border border-gray-100 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 rounded-bl-[40px] opacity-10" style="background:linear-gradient(135deg, #f59e0b, #d97706);"></div>
            <div class="w-9 h-9 rounded-lg bg-amber-50 flex items-center justify-center mb-2.5">
                <i class="fas fa-hourglass-half text-amber-500 text-[10px]"></i>
            </div>
            <div class="text-[10px] font-bold text-gray-900">{{ $sedangDipinjam }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Sedang Dipinjam</div>
        </div>

        <div class="stat-card glass-card rounded-xl border border-gray-100 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 rounded-bl-[40px] opacity-10" style="background:linear-gradient(135deg, #10b981, #059669);"></div>
            <div class="w-9 h-9 rounded-lg bg-emerald-50 flex items-center justify-center mb-2.5">
                <i class="fas fa-check-circle text-emerald-500 text-[10px]"></i>
            </div>
            <div class="text-[10px] font-bold text-gray-900">{{ $dikembalikan }}</div>
            <div class="text-[10px] text-gray-500 mt-0.5">Dikembalikan</div>
        </div>

        <div class="stat-card glass-card rounded-xl border border-gray-100 p-4 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-16 h-16 rounded-bl-[40px] opacity-10" style="background:linear-gradient(135deg, #ef4444, #dc2626);"></div>
            <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center mb-2.5">
                <i class="fas fa-money-bill-wave text-red-500 text-[10px]"></i>
            </div>
            <div class="text-[10px] font-bold {{ $dendaBelumBayar > 0 ? 'text-red-600' : 'text-gray-900' }}">
                Rp {{ number_format($totalDenda, 0, ',', '.') }}
            </div>
            <div class="text-[10px] text-gray-500 mt-0.5">
                Total Denda
                @if($dendaBelumBayar > 0)
                    <span class="text-red-500 font-medium">(Rp {{ number_format($dendaBelumBayar, 0, ',', '.') }} belum bayar)</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Detail Cards Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-1.5">

        <!-- Informasi Pribadi -->
        <div class="glass-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in" style="animation-delay: 0.1s;">
            <div class="px-3 py-2 border-b border-gray-100 flex items-center gap-1.5">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                    <i class="fas fa-user text-white text-[10px]"></i>
                </div>
                <h3 class="text-[10px] font-semibold text-gray-800 uppercase tracking-wide">Informasi Pribadi</h3>
            </div>
            <div class="px-3 py-2">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-phone text-gray-400 text-[10px]"></i> Telepon</span>
                    <span class="info-value">{{ $anggota->nomor_telepon }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-envelope text-gray-400 text-[10px]"></i> Email</span>
                    <span class="info-value {{ $anggota->email ? '' : 'text-gray-400' }}">{{ $anggota->email ?: '-' }}</span>
                </div>
                @if($anggota->tanggal_lahir)
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-birthday-cake text-gray-400 text-[10px]"></i> Tanggal Lahir</span>
                    <span class="info-value">{{ $anggota->tanggal_lahir->format('d F Y') }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-map-marker-alt text-gray-400 text-[10px]"></i> Alamat</span>
                    <span class="info-value text-left max-w-[55%]">{{ $anggota->alamat }}</span>
                </div>
            </div>
        </div>

        <!-- Informasi Keanggotaan -->
        <div class="glass-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in" style="animation-delay: 0.15s;">
            <div class="px-3 py-2 border-b border-gray-100 flex items-center gap-1.5">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center">
                    <i class="fas fa-id-badge text-white text-[10px]"></i>
                </div>
                <h3 class="text-[10px] font-semibold text-gray-800 uppercase tracking-wide">Informasi Keanggotaan</h3>
            </div>
            <div class="px-3 py-2">
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-hashtag text-gray-400 text-[10px]"></i> Nomor Anggota</span>
                    <span class="info-value font-mono bg-gray-50 px-2 py-0.5 rounded-md text-[10px]">{{ $anggota->nomor_anggota }}</span>
                </div>
                @if($anggota->kelas)
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-school text-gray-400 text-[10px]"></i> Kelas</span>
                    <span class="info-value">{{ $anggota->kelas->nama_kelas }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-graduation-cap text-gray-400 text-[10px]"></i> Jurusan</span>
                    <span class="info-value">{{ $anggota->kelas->jurusan->nama_jurusan ?? '-' }}</span>
                </div>
                @endif
                @if($anggota->jabatan)
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-briefcase text-gray-400 text-[10px]"></i> Jabatan</span>
                    <span class="info-value">{{ $anggota->jabatan }}</span>
                </div>
                @endif
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-calendar-check text-gray-400 text-[10px]"></i> Bergabung</span>
                    <span class="info-value">{{ $anggota->tanggal_bergabung->format('d F Y') }}</span>
                </div>
                <div class="info-row">
                    <span class="info-label"><i class="fas fa-clock text-gray-400 text-[10px]"></i> Terakhir Diupdate</span>
                    <span class="info-value text-gray-400 text-[10px]">{{ $anggota->updated_at->format('d/m/Y H:i') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Barcode Card -->
    <div class="glass-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in" style="animation-delay: 0.2s;">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center gap-1.5">
            <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 flex items-center justify-center">
                <i class="fas fa-barcode text-white text-[10px]"></i>
            </div>
            <h3 class="text-[10px] font-semibold text-gray-800 uppercase tracking-wide">Barcode Anggota</h3>
        </div>
        <div class="p-3">
            <div class="barcode-box max-w-sm mx-auto">
                <img src="data:image/png;base64,{{ \App\Helpers\BarcodeHelper::generateBarcodeImage($anggota->barcode_anggota, 'C128') }}"
                     alt="Barcode" class="mx-auto mb-3" style="max-width: 240px; height: auto;">
                <div class="font-mono text-[10px] font-semibold text-gray-700 tracking-widest">{{ $anggota->barcode_anggota }}</div>
                <div class="text-[10px] text-gray-400 mt-1">Code 128</div>
            </div>
        </div>
    </div>

    <!-- Riwayat Peminjaman Terbaru -->
    @if($anggota->peminjaman->count() > 0)
    <div class="glass-card rounded-2xl shadow-sm border border-gray-100 overflow-hidden fade-in" style="animation-delay: 0.25s;">
        <div class="px-3 py-2 border-b border-gray-100 flex items-center justify-between">
            <div class="flex items-center gap-1.5">
                <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center">
                    <i class="fas fa-history text-white text-[10px]"></i>
                </div>
                <h3 class="text-[10px] font-semibold text-gray-800 uppercase tracking-wide">Riwayat Peminjaman Terbaru</h3>
            </div>
            <span class="text-[10px] text-gray-400">{{ $totalPeminjaman }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50/80">
                        <th class="px-3 py-1.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">ID</th>
                        <th class="px-3 py-1.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Tgl Pinjam</th>
                        <th class="px-3 py-1.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Tgl Kembali</th>
                        <th class="px-3 py-1.5 text-left text-[10px] font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach($anggota->peminjaman->sortByDesc('created_at')->take(5) as $pinjam)
                    <tr class="hover:bg-gray-50/50 transition-colors">
                        <td class="px-3 py-1.5 text-[10px] font-mono text-gray-600">#{{ $pinjam->id }}</td>
                        <td class="px-3 py-1.5 text-[10px] text-gray-600">{{ $pinjam->tanggal_peminjaman->format('d/m/Y') }}</td>
                        <td class="px-3 py-1.5 text-[10px] text-gray-600">{{ $pinjam->tanggal_harus_kembali->format('d/m/Y') }}</td>
                        <td class="px-3 py-1.5">
                            @php
                                $pinjamStatusConfig = match($pinjam->status) {
                                    'dipinjam' => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500'],
                                    'dikembalikan' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
                                    'terlambat' => ['bg-red-50 text-red-700 border-red-200', 'bg-red-500'],
                                    default => ['bg-gray-50 text-gray-700 border-gray-200', 'bg-gray-500']
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-[10px] font-medium {{ $pinjamStatusConfig[0] }} border">
                                <span class="w-1.5 h-1.5 rounded-full {{ $pinjamStatusConfig[1] }}"></span>
                                {{ ucfirst($pinjam->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @if($totalPeminjaman > 5)
        <div class="px-3 py-1.5 border-t border-gray-100 text-center">
            <span class="text-[10px] text-gray-400">Menampilkan 5 dari {{ $totalPeminjaman }} peminjaman</span>
        </div>
        @endif
    </div>
    @endif

</div>
@endsection
