@extends('layouts.admin')

@section('page-title', 'Tentang')

@section('content')
<div class="space-y-8 animate-stagger">
    <!-- Header Section -->
    <div class="modern-card-gradient relative overflow-hidden card-hover-float">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-400/20 to-purple-600/20"></div>
        <div class="relative px-6 py-12 text-center">
            <div class="max-w-4xl mx-auto">
                <div class="animate-fadeInDown">
                    <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">
                        Tentang Kami
                    </h1>
                    <p class="text-xl text-blue-100">
                        Mengenal lebih dekat perpustakaan {{ $pengaturan->nama_sekolah ?? 'sekolah kami' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- School Information -->
    @if($pengaturan)
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Logo dan Info Dasar -->
        <div class="modern-card">
            <div class="p-6 text-center">
                @if($pengaturan->logo)
                    <img src="{{ asset('storage/' . $pengaturan->logo) }}" alt="Logo Sekolah" class="h-32 w-auto mx-auto mb-6 animate-scaleIn">
                @endif
                
                <h2 class="text-2xl font-bold text-gray-900 mb-4">
                    {{ $pengaturan->nama_sekolah }}
                </h2>
                
                <div class="space-y-3 text-left">
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-map-marker-alt text-red-500 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Alamat</h3>
                            <p class="text-gray-600">{{ $pengaturan->alamat_sekolah ?? 'Alamat tidak tersedia' }}</p>
                        </div>
                    </div>
                    
                    @if($pengaturan->telepon_sekolah)
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-phone text-green-500 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Telepon</h3>
                            <p class="text-gray-600">{{ $pengaturan->telepon_sekolah }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($pengaturan->email_sekolah)
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-envelope text-blue-500 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Email</h3>
                            <p class="text-gray-600">{{ $pengaturan->email_sekolah }}</p>
                        </div>
                    </div>
                    @endif
                    
                    @if($pengaturan->website_sekolah)
                    <div class="flex items-start space-x-3">
                        <i class="fas fa-globe text-purple-500 mt-1"></i>
                        <div>
                            <h3 class="font-semibold text-gray-900">Website</h3>
                            <a href="{{ $pengaturan->website_sekolah }}" target="_blank" class="text-blue-600 hover:text-blue-700">
                                {{ $pengaturan->website_sekolah }}
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Visi Misi -->
        <div class="modern-card">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-eye text-blue-500 mr-3"></i>
                    Visi & Misi Perpustakaan
                </h2>
                
                <div class="space-y-6">
                    @if($pengaturan->visi_perpustakaan)
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-900 mb-2 flex items-center">
                            <i class="fas fa-bullseye mr-2"></i>
                            Visi
                        </h3>
                        <p class="text-blue-800">{{ $pengaturan->visi_perpustakaan }}</p>
                    </div>
                    @else
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-blue-900 mb-2 flex items-center">
                            <i class="fas fa-bullseye mr-2"></i>
                            Visi
                        </h3>
                        <p class="text-blue-800">Menjadi pusat informasi dan pembelajaran yang unggul dalam mendukung pendidikan berkualitas.</p>
                    </div>
                    @endif
                    
                    @if($pengaturan->misi_perpustakaan)
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-900 mb-2 flex items-center">
                            <i class="fas fa-tasks mr-2"></i>
                            Misi
                        </h3>
                        <p class="text-green-800">{{ $pengaturan->misi_perpustakaan }}</p>
                    </div>
                    @else
                    <div class="bg-green-50 p-4 rounded-lg">
                        <h3 class="font-semibold text-green-900 mb-2 flex items-center">
                            <i class="fas fa-tasks mr-2"></i>
                            Misi
                        </h3>
                        <ul class="text-green-800 space-y-1">
                            <li>• Menyediakan koleksi buku dan sumber informasi yang berkualitas</li>
                            <li>• Memberikan layanan perpustakaan yang optimal</li>
                            <li>• Menciptakan lingkungan belajar yang kondusif</li>
                            <li>• Mengembangkan minat baca dan literasi informasi</li>
                        </ul>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Fasilitas Perpustakaan -->
    <div class="modern-card">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-building text-green-500 mr-3"></i>
                Fasilitas Perpustakaan
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="bg-gradient-to-br from-blue-50 to-blue-100 p-6 rounded-lg card-hover-float">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-book text-2xl text-white"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Koleksi Buku</h3>
                        <p class="text-gray-600 text-sm">Ribuan koleksi buku dari berbagai kategori dan tingkat pendidikan</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-green-50 to-green-100 p-6 rounded-lg card-hover-float">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-wifi text-2xl text-white"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">WiFi Gratis</h3>
                        <p class="text-gray-600 text-sm">Akses internet gratis untuk mendukung pembelajaran digital</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-purple-50 to-purple-100 p-6 rounded-lg card-hover-float">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-desktop text-2xl text-white"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Komputer</h3>
                        <p class="text-gray-600 text-sm">Fasilitas komputer untuk akses katalog digital dan penelusuran online</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-red-50 to-red-100 p-6 rounded-lg card-hover-float">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chair text-2xl text-white"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Ruang Baca</h3>
                        <p class="text-gray-600 text-sm">Ruang baca yang nyaman dan kondusif untuk belajar</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 p-6 rounded-lg card-hover-float">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-users text-2xl text-white"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Ruang Diskusi</h3>
                        <p class="text-gray-600 text-sm">Area khusus untuk diskusi kelompok dan presentasi</p>
                    </div>
                </div>
                
                <div class="bg-gradient-to-br from-indigo-50 to-indigo-100 p-6 rounded-lg card-hover-float">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-indigo-500 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-print text-2xl text-white"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Fotokopi</h3>
                        <p class="text-gray-600 text-sm">Layanan fotokopi dan print untuk kebutuhan pembelajaran</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Layanan Perpustakaan -->
    <div class="modern-card">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-concierge-bell text-purple-500 mr-3"></i>
                Layanan Perpustakaan
            </h2>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-book-open text-blue-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Layanan Sirkulasi</h3>
                            <p class="text-gray-600 text-sm">Peminjaman dan pengembalian buku dengan sistem barcode</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-search text-green-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Layanan Referensi</h3>
                            <p class="text-gray-600 text-sm">Bantuan pencarian informasi dan bahan pustaka</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-purple-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Bimbingan Literasi</h3>
                            <p class="text-gray-600 text-sm">Program peningkatan kemampuan literasi informasi</p>
                        </div>
                    </div>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-id-card text-red-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Keanggotaan</h3>
                            <p class="text-gray-600 text-sm">Pendaftaran dan pengelolaan kartu anggota perpustakaan</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-calendar-alt text-yellow-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Reservasi Buku</h3>
                            <p class="text-gray-600 text-sm">Pemesanan buku yang sedang dipinjam anggota lain</p>
                        </div>
                    </div>
                    
                    <div class="flex items-start space-x-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 bg-indigo-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-chart-bar text-indigo-600"></i>
                            </div>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900">Layanan Statistik</h3>
                            <p class="text-gray-600 text-sm">Data statistik pengunjung dan penggunaan koleksi</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tim Perpustakaan -->
    <div class="modern-card">
        <div class="p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-users text-indigo-500 mr-3"></i>
                Tim Perpustakaan
            </h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <div class="text-center p-6 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg card-hover-float">
                    <div class="w-20 h-20 bg-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-tie text-2xl text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Kepala Perpustakaan</h3>
                    <p class="text-blue-600 text-sm font-medium">Pengelola Utama</p>
                    <p class="text-gray-600 text-xs mt-2">Bertanggung jawab atas seluruh operasional perpustakaan</p>
                </div>
                
                <div class="text-center p-6 bg-gradient-to-br from-green-50 to-green-100 rounded-lg card-hover-float">
                    <div class="w-20 h-20 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-graduate text-2xl text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Pustakawan</h3>
                    <p class="text-green-600 text-sm font-medium">Tenaga Ahli</p>
                    <p class="text-gray-600 text-xs mt-2">Mengelola koleksi dan memberikan layanan referensi</p>
                </div>
                
                <div class="text-center p-6 bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg card-hover-float">
                    <div class="w-20 h-20 bg-purple-500 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user-friends text-2xl text-white"></i>
                    </div>
                    <h3 class="font-semibold text-gray-900 mb-1">Staff Administrasi</h3>
                    <p class="text-purple-600 text-sm font-medium">Tenaga Pendukung</p>
                    <p class="text-gray-600 text-xs mt-2">Membantu operasional harian dan layanan sirkulasi</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Contact Information -->
    <div class="modern-card-gradient text-center">
        <div class="p-8">
            <h2 class="text-2xl font-bold text-white mb-4">
                Hubungi Kami
            </h2>
            <p class="text-blue-100 mb-6">
                Untuk informasi lebih lanjut atau pertanyaan seputar layanan perpustakaan
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('petugas.buku-tamu.index') }}" class="btn-modern btn-primary btn-hover-glow">
                    <i class="fas fa-clipboard-list"></i>
                    Kunjungi Perpustakaan
                </a>
                <a href="{{ route('petugas.beranda') }}" class="btn-modern btn-secondary">
                    <i class="fas fa-home"></i>
                    Kembali ke Beranda
                </a>
            </div>
        </div>
    </div>
</div>
@endsection