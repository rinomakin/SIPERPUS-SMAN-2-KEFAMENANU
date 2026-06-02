@extends('layouts.admin')

@section('title', 'Detail Peminjaman')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Detail Peminjaman</h1>
            <a href="{{ route('peminjaman.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-3 rounded-xl font-semibold">
                Kembali
            </a>
        </div>

        <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
            <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-6 py-4">
                <h3 class="text-lg font-semibold text-white">Informasi Peminjaman</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Nomor Peminjaman</h4>
                        <p class="text-lg font-bold text-blue-600">{{ $peminjaman->nomor_peminjaman }}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Anggota</h4>
                        <p class="font-semibold">{{ $peminjaman->anggota->nama_lengkap }}</p>
                        <p class="text-sm text-gray-500">{{ $peminjaman->anggota->nomor_anggota }}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Tanggal Pinjam</h4>
                        <p>{{ $peminjaman->tanggal_peminjaman->format('d M Y') }}</p>
                    </div>
                    <div>
                        <h4 class="font-semibold text-gray-700 mb-2">Batas Kembali</h4>
                        <p>{{ $peminjaman->tanggal_harus_kembali->format('d M Y') }}</p>
                    </div>
                </div>

                <div class="mt-6">
                    <h4 class="font-semibold text-gray-700 mb-4">Buku yang Dipinjam</h4>
                    <div class="space-y-4">
                        @forelse($peminjaman->detailPeminjaman as $detail)
                        <div class="border border-gray-200 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h5 class="font-semibold">{{ $detail->buku->judul_buku }}</h5>
                                    <p class="text-sm text-gray-500">{{ $detail->buku->penulis ?? 'N/A' }}</p>
                                </div>
                                <button onclick="removeBook({{ $detail->id }})" 
                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                    Hapus
                                </button>
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-500">Belum ada buku dipinjam</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

function removeBook(detailId) {
    showConfirmDialog(
        'Apakah Anda yakin ingin menghapus buku ini dari peminjaman?',
        'Konfirmasi Hapus Buku',
        function() {
            fetch('/admin/peminjaman/remove-book', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    detail_id: detailId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccessAlert(data.message);
                    location.reload();
                } else {
                    showErrorAlert(data.message);
                }
            })
            .catch(error => {
                showErrorAlert('Terjadi kesalahan: ' + error.message);
            });
        }
    );
}
</script>
@endsection 