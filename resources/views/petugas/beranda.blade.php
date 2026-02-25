@extends('layouts.admin')

@section('page-title', 'Beranda')

@section('content')
<div class="space-y-8 animate-stagger">
    <!-- Hero Section -->
    <div class="modern-card-gradient relative overflow-hidden card-hover-float">
        <div class="absolute inset-0 bg-gradient-to-br from-green-400/20 to-teal-600/20"></div>
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full animate-pulse"></div>
            <div class="absolute bottom-10 right-10 w-24 h-24 bg-white rounded-full animate-pulse" style="animation-delay: 0.5s;"></div>
        </div>
        <div class="relative px-6 py-12 text-center">
            <div class="max-w-4xl mx-auto">
                <div class="mb-6 animate-fadeInDown">
                    @if($pengaturan && $pengaturan->logo)
                        <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo" class="h-24 w-auto mx-auto mb-4">
                    @endif
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        {{ $pengaturan->nama_website ?? 'SIPERPUS' }}
                    </h1>
                    <p class="text-xl text-green-100 mb-2">
                        {{ $pengaturan->nama_sekolah ?? 'Sistem Informasi Perpustakaan' }}
                    </p>
                    <p class="text-lg text-green-200">
                        {{ $pengaturan->alamat_sekolah ?? 'Alamat Sekolah' }}
                    </p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8 animate-fadeInUp">
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-6 card-hover-float">
                        <div class="text-center">
                            <i class="fas fa-book text-4xl text-white mb-3 icon-bounce"></i>
                            <h3 class="text-lg font-semibold text-white mb-2">Koleksi Buku</h3>
                            <p class="text-green-100 text-sm">Akses ribuan koleksi buku digital dan fisik</p>
                        </div>
                    </div>
                    
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-6 card-hover-float">
                        <div class="text-center">
                            <i class="fas fa-users text-4xl text-white mb-3 icon-bounce"></i>
                            <h3 class="text-lg font-semibold text-white mb-2">Layanan Anggota</h3>
                            <p class="text-green-100 text-sm">Pendaftaran dan layanan untuk anggota perpustakaan</p>
                        </div>
                    </div>
                    
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-6 card-hover-float">
                        <div class="text-center">
                            <i class="fas fa-clock text-4xl text-white mb-3 icon-bounce"></i>
                            <h3 class="text-lg font-semibold text-white mb-2">Jam Operasional</h3>
                            <p class="text-green-100 text-sm">Senin - Jumat: 07:00 - 16:00</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Info Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Informasi Layanan -->
        <div class="modern-card">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-info-circle text-blue-500 mr-3 icon-pulse"></i>
                    Informasi Layanan
                </h2>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-book-open text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Peminjaman Buku</h3>
                            <p class="text-gray-600 text-sm">Siswa dapat meminjam maksimal 3 buku selama 7 hari</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-undo text-green-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Pengembalian</h3>
                            <p class="text-gray-600 text-sm">Pengembalian dapat dilakukan sebelum jam 15:00</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Denda Keterlambatan</h3>
                            <p class="text-gray-600 text-sm">Denda Rp. 1.000 per hari untuk setiap buku yang terlambat</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kontak dan Jam Operasional -->
        <div class="modern-card">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-phone text-green-500 mr-3 icon-pulse"></i>
                    Kontak & Jam Operasional
                </h2>
                
                <div class="space-y-6">
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Jam Operasional</h3>
                        <div class="space-y-2 text-sm text-gray-600">
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span>Senin - Jumat</span>
                                <span class="font-medium">07:00 - 16:00</span>
                            </div>
                            <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                                <span>Sabtu</span>
                                <span class="font-medium">08:00 - 12:00</span>
                            </div>
                            <div class="flex justify-between items-center p-2 bg-red-50 rounded text-red-600">
                                <span>Minggu & Libur</span>
                                <span class="font-medium">Tutup</span>
                            </div>
                        </div>
                    </div>
                    
                    @if($pengaturan)
                    <div>
                        <h3 class="font-semibold text-gray-900 mb-3">Informasi Kontak</h3>
                        <div class="space-y-3">
                            @if($pengaturan->telepon_sekolah)
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-phone text-gray-400"></i>
                                <span class="text-gray-600">{{ $pengaturan->telepon_sekolah }}</span>
                            </div>
                            @endif
                            
                            @if($pengaturan->email_sekolah)
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-envelope text-gray-400"></i>
                                <span class="text-gray-600">{{ $pengaturan->email_sekolah }}</span>
                            </div>
                            @endif
                            
                            @if($pengaturan->website_sekolah)
                            <div class="flex items-center space-x-3">
                                <i class="fas fa-globe text-gray-400"></i>
                                <a href="{{ $pengaturan->website_sekolah }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                                    {{ $pengaturan->website_sekolah }}
                                </a>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="modern-card">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 text-center">
                Fitur Unggulan Perpustakaan
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="text-center p-6 rounded-lg hover:bg-gray-50 transition-colors card-hover-float">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-qrcode text-2xl text-blue-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Barcode System</h3>
                    <p class="text-gray-600 text-sm">Sistem barcode untuk peminjaman dan pengembalian yang cepat</p>
                </div>
                
                <div class="text-center p-6 rounded-lg hover:bg-gray-50 transition-colors card-hover-float">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-search text-2xl text-green-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Pencarian Mudah</h3>
                    <p class="text-gray-600 text-sm">Cari buku berdasarkan judul, penulis, atau kategori dengan mudah</p>
                </div>
                
                <div class="text-center p-6 rounded-lg hover:bg-gray-50 transition-colors card-hover-float">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-mobile-alt text-2xl text-purple-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Mobile Friendly</h3>
                    <p class="text-gray-600 text-sm">Akses sistem perpustakaan dari smartphone atau tablet</p>
                </div>
                
                <div class="text-center p-6 rounded-lg hover:bg-gray-50 transition-colors card-hover-float">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-chart-bar text-2xl text-red-600"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-2">Laporan Real-time</h3>
                    <p class="text-gray-600 text-sm">Statistik dan laporan penggunaan perpustakaan secara real-time</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action -->
    <div class="modern-card-gradient text-center">
        <div class="p-8">
            <h2 class="text-2xl font-bold text-white mb-4">
                Butuh Bantuan?
            </h2>
            <p class="text-green-100 mb-6">
                Tim petugas perpustakaan siap membantu Anda dalam menggunakan fasilitas perpustakaan
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('petugas.buku-tamu.index') }}" class="btn-modern btn-primary btn-hover-glow">
                    <i class="fas fa-clipboard-list"></i>
                    Buku Tamu
                </a>
                <a href="{{ route('petugas.tentang') }}" class="btn-modern btn-secondary">
                    <i class="fas fa-info-circle"></i>
                    Tentang Kami
                </a>
            </div>
        </div>
    </div>
</div>
@endsection