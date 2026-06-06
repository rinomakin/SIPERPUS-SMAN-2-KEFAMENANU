<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Perpustakaan - {{ $anggota->nama_lengkap }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
        
        .barcode-image {
            width: 100%;
            height: auto;
            max-width: 180px;
        }
        
        .barcode-text {
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body class="bg-gray-100 p-5">
    <button onclick="window.print()" class="fixed top-5 right-5 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg shadow-lg no-print">
        <i class="fas fa-print mr-2"></i>Cetak Kartu
    </button>
    

    <div class="w-[700px] h-[250px] mx-auto bg-inherit bg-gradient-to-r bg-white  shadow-lg rounded-lg flex overflow-hidden border">
        <!-- Left Panel - Member Information -->
        <div class="w-[350px] h-[250px] p-4 border-r-2 border-gray-200">
            <div class="flex">
               <div class="w-1/2">
                <div class="flex items-center justify-center mb-2">
                    <img src="{{ asset($pengaturan->logo) }}" alt="Logo" class="h-10 w-auto">
                </div>
                    <div class="text-center">
                        <div class="text-[8px] font-bold text-gray-900 uppercase">KARTU PERPUSTAKAAN</div>
                        <!-- <div class="text-[8px] font-bold text-gray-900 uppercase">{{ $pengaturan->nama_website }}</div> -->
                        <div class="text-[8px] font-bold text-gray-900 uppercase">{{ $pengaturan->deskripsi_website }}</div>
                        <!-- tambahkan alamat dari pengaturan -->
                        <div class="text-[8px] text-gray-900 mb-5">{{ $pengaturan->alamat_website }}</div>
                      
                        <div class="text-[10px] font-bold mb-7 text-gray-900 uppercase border-b-2 border-gray-400 pb-1">
                                     {{ $anggota->nama_lengkap }}
                        </div>
                        <div class="inline-block p-2 mb-4 rounded-lg max-w-[150px]">
                    <img src="data:image/png;base64,{{ \App\Helpers\BarcodeHelper::generateBarcodeImage($anggota->barcode_anggota, 'C128') }}" 
                         alt="Barcode" class="barcode-image text-gray-900 mb-1">
                    <div class="barcode-text text-[10px] text-gray-900">{{ $anggota->barcode_anggota }}</div>
                </div>
                </div>
            </div>
            <div class="flex-grow text-right">
                <div class="text-[14px] font-bold  text-center  mb-2">
                    
                <div class=" font-bold bg-slate-500 text-center text-white  rounded-lg p-1 w-[100px] mx-auto">

                    {{ strtoupper($anggota->jenis_anggota) }}</div>
                </div>
                <div class="text-[10px] text-gray-900 font-bold text-center mb-2">{{ $anggota->nomor_anggota }}</div>
                <div class="text-[8px] text-gray-900 font-semibold text-center">Kartu Berlaku Hingga </div>
                <div class="text-[8px] text-gray-900 font-bold text-center mb-2"> Selama Menjadi Siswa</div>
                <div class="w-[90px] h-[110px] bg-gray-200 mb-2 border-gray-300 rounded flex items-center justify-center mx-auto">
                    @if($anggota->foto)
                        <img src="{{ asset('storage/anggota/' . $anggota->foto) }}" 
                             alt="Foto" class="w-full h-full object-cover rounded">
                    @else
                        <img src="{{ $anggota->jenis_kelamin == 'Laki-laki' ? asset('images/template_foto_laki_laki.jpg') : asset('images/teplate_foto_perpempuan.jpg') }}" alt="Foto Default" class="w-full h-full object-cover rounded">
                    @endif
                </div>
                
            </div>

        </div>
            
        </div>
        
        <!-- Right Panel - Library Rules -->
        <div class="w-[350px] h-[250px] p-4">
            <div class="text-center mb-4">
                <div class="text-sm font-bold text-black uppercase border-b-2 border-black-200 pb-2">
                    PERATURAN PERPUSTAKAAN
                </div>
            </div>
            
            <div class="text-xs text-gray-900 leading-tight mb-4">
                <ol class="list-decimal text-[8px] font-semibold text-gray-900 list-inside space-y-1">
                    <li>Kartu dibawa setiap berkunjung ke Perpustakaan.</li>
                    <li>Kartu tidak dapat dipinjamkan kepada orang lain.</li>
                    <li>Kartu berlaku selama menjadi anggota perpustakaan.</li>
                    <li>Bersedia mengembalikan buku yang dipinjam sebelum batas waktu.</li>
                    <li>Bersedia membayar denda jika terlambat mengembalikan buku.</li>
                    <li>Bersedia mengganti buku yang dihilangkan.</li>
                    <li>Kartu ini dapat dicabut apabila yang bersangkutan tidak memenuhi ketentuan diatas</li>
                </ol>
            </div>
            <div class="text-xs text-gray-900 text-right pr-5">
                <div class="text-[8px]">Kefamenanu, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
                <div class="font-bold text-[8px]">Kepala Perpustakaan</div>
                <div class="mt-3">
                    <span class="text-[8px]">{{ $pengaturan->nama_kepala_sekolah }}</span>
                </div>          
            </div>
        </div>
    </div>
</body>
</html> 