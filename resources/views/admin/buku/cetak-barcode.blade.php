<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Barcode - {{ $buku->judul_buku }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
            .barcode-container { 
                page-break-inside: avoid; 
                margin-bottom: 20px;
            }
        }
        
        .barcode-image {
            width: 100%;
            height: auto;
            max-width: 300px;
        }
        
        .barcode-text {
            font-family: 'Courier New', monospace;
        }
        
        .barcode-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            padding: 20px;
        }
        
        .barcode-item {
            break-inside: avoid;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="no-print fixed top-5 right-5 z-50 flex space-x-2">
        <button onclick="window.print()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg">
            <i class="fas fa-print mr-2"></i>Cetak Barcode
        </button>
        <button onclick="window.close()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg shadow-lg">
            <i class="fas fa-times mr-2"></i>Tutup
        </button>
    </div>
    
    <div class="barcode-grid">
        <div class="barcode-item">
            <div class="bg-white rounded-lg shadow-lg p-6 border">
                <div class="text-center mb-4">
                    <h2 class="text-lg font-bold text-gray-800 mb-2">Barcode Buku</h2>
                    <div class="text-sm text-gray-600">{{ $buku->judul_buku }}</div>
                </div>
                
                <div class="text-center mb-4">
                    <div class="inline-block p-4 bg-white border border-gray-300 rounded-lg">
                        @if($buku->barcode)
                        <img src="data:image/png;base64,{{ \App\Helpers\BarcodeHelper::generateBarcodeImage($buku->barcode, 'C128') }}"
                             alt="Barcode" class="barcode-image mb-2">
                        <div class="barcode-text text-sm text-gray-700">{{ $buku->barcode }}</div>
                        @else
                        <div class="text-sm text-red-500 py-4">Barcode belum digenerate</div>
                        @endif
                    </div>
                </div>
                
                <div class="text-center text-xs text-gray-500">
                    <div>Judul: {{ $buku->judul_buku }}</div>
                    <div>ISBN: {{ $buku->isbn ?? 'Tidak tersedia' }}</div>
                    <div>Kategori: {{ $buku->kategori->nama_kategori ?? 'Tidak diketahui' }}</div>
                    <div>Jenis: {{ $buku->jenis->nama_jenis ?? 'Tidak diketahui' }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
