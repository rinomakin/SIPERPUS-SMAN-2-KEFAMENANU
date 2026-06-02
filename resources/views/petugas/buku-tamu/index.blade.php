<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buku Tamu - SIPERPUS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- Include HTML5-QRCode -->
    <script src="https://unpkg.com/html5-qrcode@2.3.8/html5-qrcode.min.js"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-white rounded-lg flex items-center justify-center">
                        <i class="fas fa-book text-blue-600 text-xl"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold">SIPERPUS</h1>
                        <p class="text-blue-100 text-sm">Sistem Perpustakaan</p>
                    </div>
                </div>
                <nav class="flex items-center space-x-8">
                    <a href="{{ route('petugas.beranda') }}" class="text-white hover:text-blue-200 font-medium">Beranda</a>
                    <a href="{{ route('petugas.tentang') }}" class="text-white hover:text-blue-200 font-medium">Tentang</a>
                    <a href="{{ route('petugas.buku-tamu.index') }}" class="bg-blue-700 text-white px-4 py-2 rounded-md hover:bg-blue-800 font-medium">Buku Tamu</a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                <i class="fas fa-sign-out-alt mr-2"></i>Logout
                            </button>
                        </form>
                </nav>
            </div>
        </div>
    </header>

    <!-- Page Title -->
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <h1 class="text-4xl font-bold text-white">Buku Tamu</h1>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Member Search Form -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg flex items-center">
                    <i class="fas fa-search mr-2"></i>
                    <span class="font-medium">Pencarian Tamu</span>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <!-- Search Input with Scan Button -->
                        <div class="flex space-x-2">
                            <div class="flex-1 relative">
                                <input type="text" id="member-search" 
                                       placeholder="Cari tamu/anggota berdasarkan nama, nomor anggota, atau barcode..." 
                                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                                    <i class="fas fa-search text-gray-400"></i>
                                </div>
                            </div>
                            <button id="scan-barcode-btn" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center">
                                <i class="fas fa-qrcode mr-2"></i>
                                Scan
                            </button>
                        </div>

                        <!-- Search Results -->
                        <div id="search-results" class="hidden">
                            <div class="border border-gray-200 rounded-lg max-h-64 overflow-y-auto">
                                <div id="search-results-list" class="divide-y divide-gray-200">
                                    <!-- Search results will be populated here -->
                                </div>
                            </div>
                        </div>

                        <!-- Selected Member Info -->
                        <div id="selected-member" class="hidden p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <img id="selected-photo" src="" alt="Foto" class="w-12 h-12 rounded-full object-cover">
                                <div class="flex-1">
                                    <div id="selected-name" class="font-medium text-gray-900"></div>
                                    <div id="selected-info" class="text-sm text-gray-600"></div>
                                </div>
                                <button id="record-visit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    <i class="fas fa-check mr-2"></i>
                                    Catat Kunjungan
                                </button>
                            </div>
                        </div>

                        <!-- Manual Input -->
                        <div class="border-t pt-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Input Manual Barcode Anggota:</label>
                            <div class="flex space-x-2">
                                <input type="text" id="manual-barcode" placeholder="Masukkan barcode anggota..." 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <button id="process-manual" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors duration-200">
                                    <i class="fas fa-check"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Last Scan Result -->
                        <div id="scan-result" class="mt-4 p-3 rounded-lg hidden">
                            <div class="flex items-center space-x-3">
                                <img id="result-photo" src="" alt="Foto" class="w-12 h-12 rounded-full object-cover">
                                <div>
                                    <div id="result-name" class="font-medium text-gray-900"></div>
                                    <div id="result-class" class="text-sm text-gray-600"></div>
                                    <div id="result-time" class="text-xs text-gray-500"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Visitor Data -->
            <div class="bg-white rounded-lg shadow-md">
                <div class="bg-black text-white px-4 py-3 rounded-t-lg flex items-center">
                    <i class="fas fa-users mr-2"></i>
                    <span class="font-medium">Data Tamu Hari Ini</span>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Show</span>
                            <select class="border border-gray-300 rounded px-2 py-1 text-sm">
                                <option>10</option>
                                <option>25</option>
                                <option>50</option>
                            </select>
                            <span class="text-sm text-gray-600">entries</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-sm text-gray-600">Search:</span>
                            <input type="text" id="searchInput" class="border border-gray-300 rounded px-3 py-1 text-sm" placeholder="Cari...">
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Tamu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kelas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jam Kunjungan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($kunjunganHariIni as $index => $kunjungan)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="h-8 w-8 rounded-full bg-blue-100 flex items-center justify-center">
                                                <span class="text-sm font-medium text-blue-600">
                                                    {{ substr($kunjungan->nama_tamu ?? ($kunjungan->anggota ? $kunjungan->anggota->nama_lengkap : 'Tamu'), 0, 1) }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $kunjungan->nama_tamu ?? ($kunjungan->anggota ? $kunjungan->anggota->nama_lengkap : 'Tamu Umum') }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ $kunjungan->anggota ? $kunjungan->anggota->nomor_anggota : 'Tamu Umum' }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $kunjungan->instansi ?? ($kunjungan->anggota && $kunjungan->anggota->kelas ? $kunjungan->anggota->kelas->nama_kelas : '-') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $kunjungan->waktu_datang->format('H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <form action="{{ route('petugas.buku-tamu.destroy', $kunjungan->id) }}" method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Yakin ingin menghapus data kunjungan ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                                        <i class="fas fa-inbox text-4xl mb-4"></i>
                                        <p>No data available in table</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="flex justify-between items-center mt-4">
                        <div class="text-sm text-gray-700">
                            Showing {{ $absensiHariIni->count() }} to {{ $absensiHariIni->count() }} of {{ $absensiHariIni->count() }} entries
                        </div>
                        <div class="flex space-x-2">
                            <button class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="px-3 py-1 border border-gray-300 rounded text-sm disabled:opacity-50">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Manual Attendance -->
        <div class="mt-8 bg-white rounded-lg shadow-md">
            <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg flex items-center">
                <i class="fas fa-plus mr-2"></i>
                <span class="font-medium">+ Absen Secara Manual</span>
            </div>
            <div class="p-6">
                <form action="{{ route('petugas.absensi-pengunjung.store') }}" method="POST">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="anggota_id" class="block text-sm font-medium text-gray-700 mb-2">Pilih Anggota</label>
                            <select name="anggota_id" id="anggota_id" class="w-full border border-gray-300 rounded-lg px-3 py-2" required>
                                <option value="">Pilih anggota...</option>
                                @foreach(\App\Models\Anggota::where('status', 'aktif')->get() as $anggota)
                                <option value="{{ $anggota->id }}">
                                    {{ $anggota->nomor_anggota }} - {{ $anggota->nama_lengkap }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <input type="text" name="keterangan" id="keterangan" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="Keterangan...">
                        </div>
                    </div>
                    <div class="mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium">
                            <i class="fas fa-plus mr-2"></i>Catat Absensi
                        </button>
                    </div>
                    
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-8 mt-16">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <div class="flex items-center justify-center space-x-3 mb-4">
                <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                    <i class="fas fa-book text-white"></i>
                </div>
                <span class="text-xl font-bold">SIPERPUS</span>
            </div>
            <p class="text-gray-400">&copy; {{ date('Y') }} Sistem Perpustakaan SMAN 1 Kefamenanu.</p>
        </div>
    </footer>

    <!-- Scan Modal -->
    <div id="scanModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4 rounded-t-2xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold text-white">Scan Barcode Anggota</h3>
                        <button type="button" id="closeScanModal" class="text-white hover:text-gray-200">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    <div class="mb-4">
                        <p class="text-gray-600 mb-4">Arahkan kamera ke barcode anggota untuk scan</p>
                        <div id="scanContainer" class="w-full h-80 bg-gray-100 rounded-lg flex items-center justify-center relative overflow-hidden">
                            <div id="scanPlaceholder" class="text-center">
                                <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Kamera akan aktif saat modal dibuka</p>
                            </div>
                            <div id="scanVideo" class="w-full h-full hidden">
                                <div id="reader" class="w-full h-full"></div>
                            </div>
                            <div id="scanLoading" class="absolute inset-0 bg-gray-900 bg-opacity-75 flex items-center justify-center hidden">
                                <div class="text-center text-white">
                                    <i class="fas fa-spinner fa-spin text-3xl mb-2"></i>
                                    <p>Memulai kamera...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-600">
                            <span id="scanStatus">Siap untuk scan</span>
                        </div>
                        <div class="flex space-x-3" id="scanControls">
                            <button type="button" id="startScanBtn" 
                                    class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-lg font-semibold">
                                <i class="fas fa-play mr-2"></i>Mulai Scan
                            </button>
                            <button type="button" id="stopScanBtn" 
                                    class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg font-semibold hidden">
                                <i class="fas fa-stop mr-2"></i>Stop Scan
                            </button>
                            <button type="button" id="cancelScan" 
                                    class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-lg font-semibold">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Member Search and Barcode Scanner functionality
        class MemberSearchScanner {
            constructor() {
                this.isScanning = false;
                this.html5QrcodeScanner = null;
                this.lastScanTime = 0;
                this.scanCooldown = 3000; // 3 seconds cooldown between scans
                this.searchTimeout = null;
                this.selectedMember = null;
                this.init();
            }

            init() {
                this.bindEvents();
            }

            bindEvents() {
                // Search functionality
                document.getElementById('member-search').addEventListener('input', (e) => this.handleSearch(e.target.value));
                document.getElementById('member-search').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') this.handleSearch(e.target.value);
                });

                // Scan button
                document.getElementById('scan-barcode-btn').addEventListener('click', () => this.openScanModal());

                // Manual input
                document.getElementById('process-manual').addEventListener('click', () => this.processManualBarcode());
                document.getElementById('manual-barcode').addEventListener('keypress', (e) => {
                    if (e.key === 'Enter') this.processManualBarcode();
                });

                // Record attendance
                document.getElementById('record-attendance').addEventListener('click', () => this.recordAttendance());

                // Modal controls
                document.getElementById('closeScanModal').addEventListener('click', () => this.closeScanModal());
                document.getElementById('cancelScan').addEventListener('click', () => this.closeScanModal());
                document.getElementById('startScanBtn').addEventListener('click', () => this.startScanning());
                document.getElementById('stopScanBtn').addEventListener('click', () => this.stopScanning());

                // Close modal when clicking outside
                document.getElementById('scanModal').addEventListener('click', (e) => {
                    if (e.target === e.currentTarget) {
                        this.closeScanModal();
                    }
                });

                // Table search functionality
                document.getElementById('searchInput').addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    const tableRows = document.querySelectorAll('tbody tr');
                    
                    tableRows.forEach(row => {
                        const text = row.textContent.toLowerCase();
                        if (text.includes(searchTerm)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Search functionality
            handleSearch(query) {
                clearTimeout(this.searchTimeout);
                
                if (query.length < 2) {
                    this.hideSearchResults();
                    return;
                }

                this.searchTimeout = setTimeout(() => {
                    this.searchMembers(query);
                }, 300);
            }

            async searchMembers(query) {
                try {
                    const response = await fetch(`/petugas/buku-tamu/search-members?q=${encodeURIComponent(query)}`);
                    const result = await response.json();

                    if (result.success) {
                        this.displaySearchResults(result.data);
                    } else {
                        this.showMessage(result.message, 'error');
                    }
                } catch (error) {
                    console.error('Error searching members:', error);
                    this.showMessage('Terjadi kesalahan saat mencari anggota', 'error');
                }
            }

            displaySearchResults(members) {
                const resultsContainer = document.getElementById('search-results');
                const resultsList = document.getElementById('search-results-list');

                if (members.length === 0) {
                    resultsList.innerHTML = `
                        <div class="p-4 text-center text-gray-500">
                            <i class="fas fa-search text-2xl mb-2"></i>
                            <p>Tidak ada anggota yang ditemukan</p>
                        </div>
                    `;
                } else {
                    resultsList.innerHTML = members.map(member => `
                        <div class="p-3 hover:bg-gray-50 cursor-pointer transition-colors duration-200 member-result" 
                             data-member='${JSON.stringify(member)}'>
                            <div class="flex items-center space-x-3">
                                <img src="${member.foto || 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect width="40" height="40" fill="#e5e7eb"/><text x="20" y="24" text-anchor="middle" fill="#9ca3af" font-family="Arial" font-size="14">👤</text></svg>')}" 
                                     alt="Foto" class="w-10 h-10 rounded-full object-cover">
                                <div class="flex-1">
                                    <div class="font-medium text-gray-900">${member.nama_lengkap}</div>
                                    <div class="text-sm text-gray-600">
                                        ${member.nomor_anggota} | ${member.kelas || '-'}
                                    </div>
                                </div>
                                <div class="text-xs text-gray-500">
                                    ${member.status === 'aktif' ? '<span class="text-green-600">Aktif</span>' : '<span class="text-red-600">Tidak Aktif</span>'}
                                </div>
                            </div>
                        </div>
                    `).join('');

                    // Add click event listeners
                    resultsList.querySelectorAll('.member-result').forEach(element => {
                        element.addEventListener('click', () => {
                            const member = JSON.parse(element.dataset.member);
                            this.selectMember(member);
                        });
                    });
                }

                resultsContainer.classList.remove('hidden');
            }

            selectMember(member) {
                this.selectedMember = member;
                
                // Update selected member display
                document.getElementById('selected-photo').src = member.foto || 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect width="40" height="40" fill="#e5e7eb"/><text x="20" y="24" text-anchor="middle" fill="#9ca3af" font-family="Arial" font-size="14">👤</text></svg>');
                document.getElementById('selected-name').textContent = member.nama_lengkap;
                document.getElementById('selected-info').textContent = `${member.nomor_anggota} | ${member.kelas || '-'}`;
                
                // Show selected member section
                document.getElementById('selected-member').classList.remove('hidden');
                
                // Hide search results
                this.hideSearchResults();
                
                // Clear search input
                document.getElementById('member-search').value = '';
            }

            hideSearchResults() {
                document.getElementById('search-results').classList.add('hidden');
            }

            async recordAttendance() {
                if (!this.selectedMember) {
                    this.showMessage('Pilih anggota terlebih dahulu', 'warning');
                    return;
                }

                try {
                    const response = await fetch('/petugas/buku-tamu', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ 
                            anggota_id: this.selectedMember.id,
                            keterangan: 'Pencarian Manual'
                        })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showMessage(result.message, 'success');
                        this.clearSelectedMember();
                        // Reload page to show updated attendance list
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        this.showMessage(result.message, 'error');
                    }

                } catch (error) {
                    console.error('Error recording attendance:', error);
                    this.showMessage('Terjadi kesalahan saat mencatat absensi', 'error');
                }
            }

            clearSelectedMember() {
                this.selectedMember = null;
                document.getElementById('selected-member').classList.add('hidden');
            }

            // Setup CSRF token untuk AJAX requests
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

            // Scan functionality
            openScanModal() {
                document.getElementById('scanModal').classList.remove('hidden');
                this.initializeScanner();
            }

            closeScanModal() {
                this.stopScanning();
                document.getElementById('scanModal').classList.add('hidden');
                this.resetScanModal();
            }

            resetScanModal() {
                const scanContainer = document.getElementById('scanContainer');
                const scanPlaceholder = document.getElementById('scanPlaceholder');
                const scanVideo = document.getElementById('scanVideo');
                const scanLoading = document.getElementById('scanLoading');
                
                scanLoading.classList.add('hidden');
                scanPlaceholder.classList.remove('hidden');
                scanVideo.classList.add('hidden');
                
                scanPlaceholder.innerHTML = `
                    <i class="fas fa-camera text-4xl text-gray-400 mb-2"></i>
                    <p class="text-gray-500">Kamera akan aktif saat modal dibuka</p>
                `;
                
                document.getElementById('scanStatus').textContent = 'Siap untuk scan';
                document.getElementById('startScanBtn').classList.remove('hidden');
                document.getElementById('stopScanBtn').classList.add('hidden');
            }

                async initializeScanner() {
        console.log('🚀 Memulai inisialisasi scanner...');
        
        const scanContainer = document.getElementById('scanContainer');
        const scanLoading = document.getElementById('scanLoading');
        const scanVideo = document.getElementById('scanVideo');
        const scanPlaceholder = document.getElementById('scanPlaceholder');
        
        // Tampilkan loading
        scanLoading.classList.remove('hidden');
        scanPlaceholder.classList.add('hidden');
        scanVideo.classList.remove('hidden');
        
        try {
            // Periksa apakah browser mendukung getUserMedia
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                throw new Error('Browser tidak mendukung akses kamera');
            }
            
            // Minta izin akses kamera terlebih dahulu
            const stream = await navigator.mediaDevices.getUserMedia({ 
                video: { 
                    facingMode: 'environment',
                    width: { ideal: 1280 },
                    height: { ideal: 720 }
                } 
            });
            
            // Hentikan stream sementara
            stream.getTracks().forEach(track => track.stop());
            
            // Buat HTML5-QRCode scanner
            this.html5QrcodeScanner = new Html5Qrcode("reader");
            
            // Konfigurasi scanner
            const config = {
                fps: 10,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0,
                supportedScanTypes: [
                    Html5QrcodeScanType.SCAN_TYPE_CAMERA
                ]
            };
            
            // Mulai scanning
            await this.html5QrcodeScanner.start(
                { facingMode: "environment" },
                config,
                (decodedText, decodedResult) => this.onScanSuccess(decodedText, decodedResult),
                (error) => this.onScanFailure(error)
            );
            
            console.log('📹 Scanner berhasil dimulai');
            scanLoading.classList.add('hidden');
            scanVideo.classList.remove('hidden');
            document.getElementById('scanStatus').textContent = 'Scanner aktif - Arahkan ke barcode';
            document.getElementById('startScanBtn').classList.add('hidden');
            document.getElementById('stopScanBtn').classList.remove('hidden');
            this.showMessage('Scanner siap! Arahkan kamera ke barcode anggota.', 'success');
            
        } catch (error) {
            console.error('❌ Error inisialisasi scanner:', error);
            scanLoading.classList.add('hidden');
            scanPlaceholder.classList.remove('hidden');
            scanVideo.classList.add('hidden');
            
            let errorMessage = 'Gagal menginisialisasi scanner';
            
            if (error.name === 'NotAllowedError') {
                errorMessage = 'Akses kamera ditolak. Silakan klik ikon kamera di address bar dan izinkan akses kamera.';
            } else if (error.name === 'NotFoundError') {
                errorMessage = 'Tidak ada kamera yang ditemukan di perangkat ini.';
            } else if (error.name === 'NotSupportedError') {
                errorMessage = 'Browser tidak mendukung akses kamera. Gunakan browser modern seperti Chrome, Firefox, atau Safari.';
            } else if (error.message.includes('HTTPS')) {
                errorMessage = 'Akses kamera memerlukan koneksi HTTPS. Silakan gunakan server HTTPS.';
            } else {
                errorMessage = 'Gagal menginisialisasi scanner: ' + error.message;
            }
            
            this.showMessage(errorMessage, 'error');
            
            // Tampilkan opsi manual input
            scanPlaceholder.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-exclamation-triangle text-4xl text-yellow-400 mb-2"></i>
                    <p class="text-gray-500 mb-4">Kamera tidak tersedia</p>
                    <p class="text-sm text-gray-400 mb-4">${errorMessage}</p>
                    <button onclick="document.getElementById('manual-barcode').focus()" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-keyboard mr-2"></i>
                        Gunakan Input Manual
                    </button>
                </div>
            `;
        }
    }

                onScanSuccess(decodedText, decodedResult) {
        console.log('🎉 Barcode terdeteksi:', decodedText);
        
        // Hentikan scanner untuk mencegah scan berulang
        this.stopScanning();
        
        // Tampilkan pesan sukses
        this.showMessage(`Barcode terdeteksi: ${decodedText}`, 'success');
        
        // Proses barcode
        this.processBarcode(decodedText);
    }

    onScanFailure(error) {
        // Handle scan failure silently untuk menghindari spam log
        // Hanya log jika ada error yang signifikan
        if (error && error.name !== 'NotFoundException') {
            console.log('⚠️ Scan failure:', error);
        }
    }

                startScanning() {
        if (!this.html5QrcodeScanner) {
            this.showMessage('Scanner belum siap. Silakan tunggu.', 'warning');
            return;
        }
        
        try {
            document.getElementById('scanStatus').textContent = 'Scanning aktif - Arahkan ke barcode';
            this.showMessage('Scanner aktif! Arahkan kamera ke barcode anggota.', 'info');
        } catch (error) {
            console.error('Error starting scanner:', error);
            this.showMessage('Gagal memulai scanner. Silakan coba lagi.', 'error');
        }
    }

    stopScanning() {
        if (this.html5QrcodeScanner) {
            try {
                this.html5QrcodeScanner.stop();
                document.getElementById('scanStatus').textContent = 'Scanner dihentikan';
                this.showMessage('Scanner dihentikan.', 'info');
            } catch (error) {
                console.error('Error stopping scanner:', error);
            }
        }
    }

            async processBarcode(barcode) {
                if (!barcode) return;

                this.setStatus('processing');
                this.showMessage('Memproses barcode...', 'info');
                
                try {
                    const response = await fetch('/petugas/buku-tamu/scan-barcode', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ qr_code: barcode })
                    });

                    const result = await response.json();

                    if (result.success) {
                        this.showScanResult(result.data);
                        this.showMessage(result.message, 'success');
                        
                        // Tutup modal setelah 2 detik untuk memberikan waktu melihat hasil
                        setTimeout(() => {
                            this.closeScanModal();
                            // Reload page to show updated attendance list
                            setTimeout(() => {
                                window.location.reload();
                            }, 500);
                        }, 2000);
                    } else {
                        this.showMessage(result.message, 'error');
                        // Buka kembali scanner jika ada error
                        setTimeout(() => {
                            this.startScanning();
                        }, 1000);
                    }

                } catch (error) {
                    console.error('Error processing barcode:', error);
                    this.showMessage('Terjadi kesalahan saat memproses barcode. Silakan coba lagi.', 'error');
                    
                    // Buka kembali scanner jika ada error
                    setTimeout(() => {
                        this.startScanning();
                    }, 1000);
                }

                this.setStatus('ready');
            }

            processManualBarcode() {
                const barcode = document.getElementById('manual-barcode').value.trim();
                if (barcode) {
                    this.showMessage('Memproses barcode manual...', 'info');
                    this.processBarcode(barcode);
                    document.getElementById('manual-barcode').value = '';
                } else {
                    this.showMessage('Silakan masukkan barcode terlebih dahulu', 'warning');
                    document.getElementById('manual-barcode').focus();
                }
            }

            showScanResult(data) {
                const resultDiv = document.getElementById('scan-result');
                const photo = document.getElementById('result-photo');
                const name = document.getElementById('result-name');
                const classInfo = document.getElementById('result-class');
                const time = document.getElementById('result-time');

                photo.src = data.foto || 'data:image/svg+xml;base64,' + btoa('<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 40 40"><rect width="40" height="40" fill="#e5e7eb"/><text x="20" y="24" text-anchor="middle" fill="#9ca3af" font-family="Arial" font-size="14">👤</text></svg>');
                name.textContent = data.nama_lengkap;
                classInfo.textContent = `${data.kelas} | ${data.nomor_anggota}`;
                time.textContent = `Waktu masuk: ${data.waktu_masuk}`;

                resultDiv.classList.remove('hidden', 'bg-red-50', 'bg-green-50');
                resultDiv.classList.add('bg-green-50');
                
                // Auto hide after 5 seconds
                setTimeout(() => {
                    resultDiv.classList.add('hidden');
                }, 5000);
            }

            showMessage(message, type = 'info') {
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `fixed top-4 right-4 z-50 px-6 py-3 rounded-lg text-white font-medium shadow-lg transform transition-all duration-300 translate-x-full`;
                
                let icon;
                
                // Set background color and icon based on type
                switch(type) {
                    case 'success':
                        notification.classList.add('bg-green-500');
                        icon = 'fas fa-check-circle';
                        break;
                    case 'error':
                        notification.classList.add('bg-red-500');
                        icon = 'fas fa-times-circle';
                        break;
                    case 'warning':
                        notification.classList.add('bg-yellow-500');
                        icon = 'fas fa-exclamation-triangle';
                        break;
                    default:
                        notification.classList.add('bg-blue-500');
                        icon = 'fas fa-info-circle';
                }
                
                notification.innerHTML = `
                    <div class="flex items-center">
                        <i class="${icon} mr-2"></i>
                        <span>${message}</span>
                    </div>
                `;
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.classList.remove('translate-x-full');
                }, 100);
                
                // Remove after 5 seconds
                setTimeout(() => {
                    notification.classList.add('translate-x-full');
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.parentNode.removeChild(notification);
                        }
                    }, 300);
                }, 5000);
            }
        }

        // Initialize scanner when page loads
        document.addEventListener('DOMContentLoaded', function() {
            window.memberSearchScanner = new MemberSearchScanner();
        });

        // Cleanup when page is unloaded
        window.addEventListener('beforeunload', function() {
            if (window.memberSearchScanner) {
                window.memberSearchScanner.stopScanning();
            }
        });


    </script>
</body>
</html>