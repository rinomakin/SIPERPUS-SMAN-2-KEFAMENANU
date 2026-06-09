@extends('layouts.admin')

@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
    .glass-card {
        background: rgba(255,255,255,0.85);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,0.3);
    }
    .report-item {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .report-item::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.3s;
        border-radius: inherit;
    }
    .report-item:hover {
        transform: translateY(-6px);
        box-shadow: 0 20px 40px -12px rgba(0,0,0,0.15);
    }
    .report-item:hover::before {
        opacity: 1;
    }
    .report-item .report-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-size: 22px;
        transition: all 0.3s;
    }
    .report-item:hover .report-icon {
        transform: scale(1.1) rotate(-5deg);
    }
    .report-item .report-arrow {
        opacity: 0; transform: translateX(-8px);
        transition: all 0.3s;
    }
    .report-item:hover .report-arrow {
        opacity: 1; transform: translateX(0);
    }
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .animate-item {
        animation: fadeInUp 0.5s ease forwards;
        opacity: 0;
    }
</style>

<div class="space-y-6">
    {{-- Header --}}
    <div class="glass-card rounded-2xl shadow-lg p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 flex items-center gap-3">
                    <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-chart-bar text-white text-lg"></i>
                    </div>
                    Laporan Perpustakaan
                </h1>
                <p class="text-gray-500 mt-1 ml-13">Pilih jenis laporan, filter periode, lalu lihat atau unduh dalam format Excel/PDF</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        {{-- Laporan Anggota --}}
        @if(Auth::user()->hasPermission('laporan.anggota') || Auth::user()->isAdmin())
        <div class="report-item glass-card rounded-2xl p-6 cursor-pointer animate-item" style="animation-delay: 0.05s" onclick="window.location.href='/admin/laporan/anggota'">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="report-icon bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-lg shadow-indigo-200">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Laporan Anggota</h3>
                        <p class="text-sm text-gray-500 mt-1">Data anggota perpustakaan, status keanggotaan, dan informasi detail</p>
                    </div>
                </div>
                <div class="report-arrow text-gray-400">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>
        @endif

        {{-- Laporan Buku --}}
        @if(Auth::user()->hasPermission('laporan.buku') || Auth::user()->isAdmin())
        <div class="report-item glass-card rounded-2xl p-6 cursor-pointer animate-item" style="animation-delay: 0.1s" onclick="window.location.href='/admin/laporan/buku'">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="report-icon bg-gradient-to-br from-pink-500 to-rose-600 text-white shadow-lg shadow-pink-200">
                        <i class="fas fa-book"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Laporan Buku</h3>
                        <p class="text-sm text-gray-500 mt-1">Data koleksi buku, stok, kategori, dan status ketersediaan</p>
                    </div>
                </div>
                <div class="report-arrow text-gray-400">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>
        @endif

        {{-- Laporan Peminjaman --}}
        @if(Auth::user()->hasPermission('laporan.peminjaman') || Auth::user()->isAdmin())
        <div class="report-item glass-card rounded-2xl p-6 cursor-pointer animate-item" style="animation-delay: 0.15s" onclick="window.location.href='/admin/laporan/peminjaman'">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="report-icon bg-gradient-to-br from-sky-500 to-blue-600 text-white shadow-lg shadow-sky-200">
                        <i class="fas fa-book-reader"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Laporan Peminjaman</h3>
                        <p class="text-sm text-gray-500 mt-1">Data transaksi peminjaman buku dan status peminjaman</p>
                    </div>
                </div>
                <div class="report-arrow text-gray-400">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>
        @endif

        {{-- Laporan Pengembalian --}}
        @if(Auth::user()->hasPermission('laporan.pengembalian') || Auth::user()->isAdmin())
        <div class="report-item glass-card rounded-2xl p-6 cursor-pointer animate-item" style="animation-delay: 0.2s" onclick="window.location.href='/admin/laporan/pengembalian'">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="report-icon bg-gradient-to-br from-emerald-500 to-green-600 text-white shadow-lg shadow-emerald-200">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Laporan Pengembalian</h3>
                        <p class="text-sm text-gray-500 mt-1">Data transaksi pengembalian buku dan keterlambatan</p>
                    </div>
                </div>
                <div class="report-arrow text-gray-400">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>
        @endif

        {{-- Laporan Denda --}}
        @if(Auth::user()->hasPermission('laporan.denda') || Auth::user()->isAdmin())
        <div class="report-item glass-card rounded-2xl p-6 cursor-pointer animate-item" style="animation-delay: 0.25s" onclick="window.location.href='/admin/laporan/denda'">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="report-icon bg-gradient-to-br from-amber-500 to-orange-600 text-white shadow-lg shadow-amber-200">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Laporan Denda</h3>
                        <p class="text-sm text-gray-500 mt-1">Data denda keterlambatan dan status pembayaran</p>
                    </div>
                </div>
                <div class="report-arrow text-gray-400">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>
        @endif

        {{-- Laporan Buku Tamu --}}
        @if(Auth::user()->hasPermission('laporan.buku-tamu') || Auth::user()->isAdmin())
        <div class="report-item glass-card rounded-2xl p-6 cursor-pointer animate-item" style="animation-delay: 0.3s" onclick="window.location.href='/admin/laporan/buku-tamu'">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-4">
                    <div class="report-icon bg-gradient-to-br from-violet-500 to-purple-600 text-white shadow-lg shadow-violet-200">
                        <i class="fas fa-book-open"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-gray-900">Laporan Buku Tamu</h3>
                        <p class="text-sm text-gray-500 mt-1">Data kunjungan tamu perpustakaan</p>
                    </div>
                </div>
                <div class="report-arrow text-gray-400">
                    <i class="fas fa-arrow-right"></i>
                </div>
            </div>
        </div>
        @endif

        {{-- Laporan Kas --}}
        @if(Auth::user()->hasPermission('laporan.kas') || Auth::user()->isAdmin())
        @endif
    </div>
</div>

{{-- Report Modal --}}
<div id="reportModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden transition-opacity duration-300" style="opacity: 0;">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div id="modalContent" class="bg-white rounded-2xl shadow-2xl max-w-lg w-full transform transition-all duration-300 scale-95">
            {{-- Modal Header --}}
            <div id="modalHeader" class="bg-gradient-to-r from-indigo-500 to-purple-600 px-6 py-5 rounded-t-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center">
                            <i id="modalIcon" class="fas fa-chart-bar text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 id="modalTitle" class="text-lg font-bold text-white">Unduh Laporan</h3>
                            <p class="text-white/70 text-sm">Atur filter dan pilih aksi</p>
                        </div>
                    </div>
                    <button onclick="closeReportModal()" class="w-8 h-8 bg-white/20 hover:bg-white/30 rounded-lg flex items-center justify-center text-white transition-colors">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <div class="p-6">
                <form id="reportForm">
                    {{-- Date Range --}}
                    <div class="mb-5">
                        <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-3">
                            <i class="fas fa-calendar-alt text-indigo-500"></i>
                            Rentang Tanggal
                        </label>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <input type="date" id="modalStartDate"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50">
                                <label class="text-xs text-gray-400 mt-1 block">Tanggal Mulai</label>
                            </div>
                            <div>
                                <input type="date" id="modalEndDate"
                                       class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-gray-50">
                                <label class="text-xs text-gray-400 mt-1 block">Tanggal Akhir</label>
                            </div>
                        </div>
                    </div>

                    {{-- Additional Filters --}}
                    <div id="additionalFilters" class="space-y-4 mb-5"></div>

                    {{-- Action Buttons --}}
                    <div class="flex items-center gap-3 pt-4 border-t border-gray-100">
                        <button type="button" onclick="closeReportModal()"
                                class="flex-1 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="button" onclick="viewReport()"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white text-sm font-medium rounded-xl transition-all shadow-md">
                            <i class="fas fa-eye mr-2"></i>Lihat
                        </button>
                        <button type="button" onclick="downloadReport()"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white text-sm font-medium rounded-xl transition-all shadow-md">
                            <i class="fas fa-file-excel mr-2"></i>Excel
                        </button>
                        <button type="button" onclick="downloadPdf()"
                                class="flex-1 px-4 py-2.5 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white text-sm font-medium rounded-xl transition-all shadow-md">
                            <i class="fas fa-file-pdf mr-2"></i>PDF
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
let currentReportType = '';

document.addEventListener('DOMContentLoaded', function() {
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    document.getElementById('globalStartDate').value = formatDate(firstDay);
    document.getElementById('globalEndDate').value = formatDate(lastDay);
});

function formatDate(date) {
    return date.toISOString().split('T')[0];
}

const reportConfig = {
    anggota: {
        title: 'Laporan Anggota',
        icon: 'fa-users',
        gradient: 'from-indigo-500 to-purple-600',
        filters: `
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user-tag text-indigo-500"></i> Jenis Anggota
                </label>
                <select id="filterJenisAnggota" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                    <option value="">Semua Jenis</option>
                    <option value="siswa">Siswa</option>
                    <option value="guru">Guru</option>
                    <option value="staff">Staff</option>
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-toggle-on text-indigo-500"></i> Status
                </label>
                <select id="filterStatus" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-indigo-500 bg-gray-50">
                    <option value="">Semua Status</option>
                    <option value="aktif">Aktif</option>
                    <option value="nonaktif">Nonaktif</option>
                    <option value="ditangguhkan">Ditangguhkan</option>
                </select>
            </div>`
    },
    buku: {
        title: 'Laporan Buku',
        icon: 'fa-book',
        gradient: 'from-pink-500 to-rose-600',
        filters: `
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-box text-pink-500"></i> Status Buku
                </label>
                <select id="filterStatusBuku" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-pink-500 bg-gray-50">
                    <option value="">Semua Status</option>
                    <option value="tersedia">Tersedia</option>
                    <option value="dipinjam">Sedang Dipinjam</option>
                </select>
            </div>`
    },
    peminjaman: {
        title: 'Laporan Peminjaman',
        icon: 'fa-book-reader',
        gradient: 'from-sky-500 to-blue-600',
        filters: `
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-info-circle text-sky-500"></i> Status
                </label>
                <select id="filterStatusPeminjaman" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-sky-500 bg-gray-50">
                    <option value="">Semua Status</option>
                    <option value="dipinjam">Sedang Dipinjam</option>
                    <option value="dikembalikan">Sudah Dikembalikan</option>
                    <option value="terlambat">Terlambat</option>
                </select>
            </div>`
    },
    pengembalian: {
        title: 'Laporan Pengembalian',
        icon: 'fa-undo',
        gradient: 'from-emerald-500 to-green-600',
        filters: ''
    },
    denda: {
        title: 'Laporan Denda',
        icon: 'fa-money-bill-wave',
        gradient: 'from-amber-500 to-orange-600',
        filters: `
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-receipt text-amber-500"></i> Status Pembayaran
                </label>
                <select id="filterStatusDenda" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 bg-gray-50">
                    <option value="">Semua Status</option>
                    <option value="belum_dibayar">Belum Bayar</option>
                    <option value="sudah_dibayar">Sudah Bayar</option>
                </select>
            </div>`
    },
    'buku-tamu': {
        title: 'Laporan Buku Tamu',
        icon: 'fa-book-open',
        gradient: 'from-violet-500 to-purple-600',
        filters: `
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-user text-violet-500"></i> Tipe Pengunjung
                </label>
                <select id="filterTipePengunjung" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50">
                    <option value="">Semua Tipe</option>
                    <option value="anggota">Anggota</option>
                    <option value="umum">Tamu Umum</option>
                </select>
            </div>
            <div>
                <label class="flex items-center gap-2 text-sm font-semibold text-gray-700 mb-2">
                    <i class="fas fa-clock text-violet-500"></i> Status
                </label>
                <select id="filterStatusBukuTamu" class="w-full px-3 py-2.5 text-sm border border-gray-200 rounded-xl focus:ring-2 focus:ring-violet-500 bg-gray-50">
                    <option value="">Semua Status</option>
                    <option value="berkunjung">Sedang Berkunjung</option>
                    <option value="pulang">Sudah Pulang</option>
                </select>
            </div>`
    },
    kas: {
        title: 'Laporan Kas',
        icon: 'fa-wallet',
        gradient: 'from-teal-500 to-cyan-600',
        filters: ''
    }
};

function openReportModal(reportType) {
    currentReportType = reportType;
    const config = reportConfig[reportType];
    const modal = document.getElementById('reportModal');
    const modalContent = document.getElementById('modalContent');
    const modalHeader = document.getElementById('modalHeader');

    document.getElementById('modalTitle').textContent = config.title;
    document.getElementById('modalIcon').className = `fas ${config.icon} text-white text-lg`;
    modalHeader.className = `bg-gradient-to-r ${config.gradient} px-6 py-5 rounded-t-2xl`;

    document.getElementById('modalStartDate').value = document.getElementById('globalStartDate').value;
    document.getElementById('modalEndDate').value = document.getElementById('globalEndDate').value;

    document.getElementById('additionalFilters').innerHTML = config.filters;

    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        modal.style.opacity = '1';
        modalContent.classList.remove('scale-95');
        modalContent.classList.add('scale-100');
    });
}

function closeReportModal() {
    const modal = document.getElementById('reportModal');
    const modalContent = document.getElementById('modalContent');
    modal.style.opacity = '0';
    modalContent.classList.remove('scale-100');
    modalContent.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}

function applyGlobalDateFilter() {
    const startDate = document.getElementById('globalStartDate').value;
    const endDate = document.getElementById('globalEndDate').value;

    if (!startDate || !endDate) {
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Silakan pilih rentang tanggal', confirmButtonColor: '#6366f1' });
        return;
    }
    if (startDate > endDate) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir', confirmButtonColor: '#6366f1' });
        return;
    }

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
    Toast.fire({ icon: 'success', title: 'Filter tanggal berhasil diterapkan' });
}

function viewReport() {
    const params = buildReportParams();
    if (!validateReportParams()) return;

    const url = `/admin/laporan/${currentReportType}?${params}`;
    window.open(url, '_blank');
    closeReportModal();
}

function downloadReport() {
    const params = buildReportParams();
    if (!validateReportParams()) return;

    const url = `/admin/laporan/${currentReportType}?${params}&export=excel`;
    window.location.href = url;

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    Toast.fire({ icon: 'info', title: 'Mengunduh laporan Excel...' });

    setTimeout(() => closeReportModal(), 1000);
}

function downloadPdf() {
    const params = buildReportParams();
    if (!validateReportParams()) return;

    const url = `/admin/laporan/${currentReportType}?${params}&export=pdf`;
    window.location.href = url;

    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
    Toast.fire({ icon: 'info', title: 'Mengunduh laporan PDF...' });

    setTimeout(() => closeReportModal(), 1000);
}

function buildReportParams() {
    const startDate = document.getElementById('modalStartDate').value;
    const endDate = document.getElementById('modalEndDate').value;

    let params = new URLSearchParams();
    if (startDate) params.append('tanggal_mulai', startDate);
    if (endDate) params.append('tanggal_akhir', endDate);

    switch(currentReportType) {
        case 'anggota':
            const jenisAnggota = document.getElementById('filterJenisAnggota')?.value;
            const status = document.getElementById('filterStatus')?.value;
            if (jenisAnggota) params.append('jenis_anggota', jenisAnggota);
            if (status) params.append('status', status);
            break;
        case 'buku':
            const statusBuku = document.getElementById('filterStatusBuku')?.value;
            if (statusBuku) params.append('status', statusBuku);
            break;
        case 'peminjaman':
            const statusPeminjaman = document.getElementById('filterStatusPeminjaman')?.value;
            if (statusPeminjaman) params.append('status', statusPeminjaman);
            break;
        case 'denda':
            const statusDenda = document.getElementById('filterStatusDenda')?.value;
            if (statusDenda) params.append('status', statusDenda);
            break;
        case 'buku-tamu':
            const tipePengunjung = document.getElementById('filterTipePengunjung')?.value;
            const statusBukuTamu = document.getElementById('filterStatusBukuTamu')?.value;
            if (tipePengunjung) params.append('tipe', tipePengunjung);
            if (statusBukuTamu) params.append('status', statusBukuTamu);
            break;
    }

    return params.toString();
}

function validateReportParams() {
    const startDate = document.getElementById('modalStartDate').value;
    const endDate = document.getElementById('modalEndDate').value;

    if (!startDate || !endDate) {
        Swal.fire({ icon: 'warning', title: 'Perhatian', text: 'Silakan pilih rentang tanggal', confirmButtonColor: '#6366f1' });
        return false;
    }
    if (startDate > endDate) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Tanggal mulai tidak boleh lebih besar dari tanggal akhir', confirmButtonColor: '#6366f1' });
        return false;
    }
    return true;
}

document.getElementById('reportModal').addEventListener('click', function(e) {
    if (e.target === this) closeReportModal();
});
</script>
@endsection
