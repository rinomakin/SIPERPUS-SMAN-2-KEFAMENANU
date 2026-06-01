@extends('layouts.admin')

@section('title', 'Edit Pengembalian — ' . $pengembalian->nomor_pengembalian)

@section('content')
<div class="min-h-screen py-6">
    <div class="px-4 sm:px-6 lg:px-8 max-w-14xl mx-auto">
        <div class="flex items-center justify-between mb-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Edit Pengembalian</h2>
                <p class="text-xs text-gray-500 mt-1">{{ $pengembalian->nomor_pengembalian }}</p>
            </div>
            <a href="{{ route('pengembalian.show', $pengembalian->id) }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs font-semibold transition-all">
                <i class="fas fa-arrow-left mr-1"></i>Kembali
            </a>
        </div>

        @if ($errors->any())
        <div class="mb-5 p-4 bg-red-50 border border-red-200 rounded-xl">
            <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <form action="{{ route('pengembalian.update', $pengembalian->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                {{-- Left Column (2/3) — Detail Buku --}}
                <div class="lg:col-span-2 space-y-5">
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-blue-500 to-indigo-600 px-5 py-3.5">
                            <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                                <i class="fas fa-book-open"></i> Buku yang Dikembalikan
                            </h3>
                        </div>
                        <div class="p-5 space-y-4">
                            @foreach ($pengembalian->detailPengembalian as $detail)
                            <div class="border border-gray-200 rounded-xl overflow-hidden">
                                <div class="flex items-center gap-3 px-4 py-3 bg-gray-50 border-b border-gray-100">
                                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-book text-blue-500 text-xs"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs font-semibold text-gray-900 truncate">{{ $detail->buku->judul_buku ?? 'N/A' }}</p>
                                        <p class="text-[10px] text-gray-500">{{ $detail->buku->kode_buku ?? '' }}</p>
                                    </div>
                                </div>
                                <div class="px-4 py-3 space-y-3">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div>
                                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                                <i class="fas fa-eye text-purple-400 mr-1"></i>Kondisi
                                            </label>
                                            <select name="kondisi_kembali[{{ $detail->id }}]" required
                                                    class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none bg-white">
                                                <option value="baik" {{ $detail->kondisi_kembali === 'baik' ? 'selected' : '' }}>Baik</option>
                                                <option value="sedikit_rusak" {{ $detail->kondisi_kembali === 'sedikit_rusak' ? 'selected' : '' }}>Sedikit Rusak</option>
                                                <option value="rusak" {{ $detail->kondisi_kembali === 'rusak' ? 'selected' : '' }}>Rusak</option>
                                                <option value="hilang" {{ $detail->kondisi_kembali === 'hilang' ? 'selected' : '' }}>Hilang</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                                <i class="fas fa-sort-amount-up-alt text-blue-400 mr-1"></i>Jml Dikembalikan
                                            </label>
                                            <input type="number" name="jumlah_dikembalikan[{{ $detail->id }}]"
                                                   value="{{ $detail->jumlah_dikembalikan }}" min="1"
                                                   class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none bg-white">
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">
                                            <i class="fas fa-sticky-note text-yellow-400 mr-1"></i>Catatan Buku
                                        </label>
                                        <input type="text" name="catatan_buku[{{ $detail->id }}]"
                                               value="{{ $detail->catatan_buku }}"
                                               class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-yellow-400 outline-none bg-white"
                                               placeholder="Catatan untuk buku ini (opsional)">
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                {{-- Right Column (1/3) — Info & Denda --}}
                <div class="space-y-5">
                    {{-- Info Anggota --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-500 to-green-600 px-5 py-3.5">
                            <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                                <i class="fas fa-user-check"></i> Informasi
                            </h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Anggota</p>
                                <p class="text-xs font-bold text-gray-900">{{ $pengembalian->anggota->nama_lengkap ?? 'N/A' }}</p>
                                <p class="text-[10px] text-gray-500">{{ $pengembalian->anggota->nomor_anggota ?? '' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Peminjaman</p>
                                <p class="text-xs font-semibold text-gray-900">{{ $pengembalian->peminjaman->nomor_peminjaman ?? 'N/A' }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Petugas</p>
                                <p class="text-xs text-gray-700">{{ $pengembalian->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                    </div>

                    {{-- Tanggal & Jam --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-purple-500 to-violet-600 px-5 py-3.5">
                            <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                                <i class="fas fa-calendar-alt"></i> Waktu Pengembalian
                            </h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal</label>
                                <input type="date" name="tanggal_pengembalian"
                                       value="{{ $pengembalian->tanggal_pengembalian ? $pengembalian->tanggal_pengembalian->format('Y-m-d') : date('Y-m-d') }}"
                                       class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Jam</label>
                                <input type="time" name="jam_pengembalian"
                                       value="{{ $pengembalian->jam_pengembalian ? Carbon\Carbon::parse($pengembalian->jam_pengembalian)->format('H:i') : date('H:i') }}"
                                       class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-400 outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- Denda --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-amber-500 to-orange-600 px-5 py-3.5">
                            <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                                <i class="fas fa-coins"></i> Denda
                            </h3>
                        </div>
                        <div class="p-4 space-y-3">
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Status Pembayaran</label>
                                <select name="status_pembayaran_denda" id="status_pembayaran_denda"
                                        class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-400 outline-none">
                                    <option value="belum_dibayar" {{ $pengembalian->status_denda === 'belum_dibayar' ? 'selected' : '' }}>Belum Dibayar</option>
                                    <option value="sudah_dibayar" {{ $pengembalian->status_denda === 'sudah_dibayar' ? 'selected' : '' }}>Sudah Dibayar</option>
                                    <option value="tidak_ada" {{ $pengembalian->status_denda === 'tidak_ada' ? 'selected' : '' }}>Tidak Ada Denda</option>
                                </select>
                            </div>
                            <div id="tanggalPembayaranSection" class="{{ $pengembalian->status_denda === 'sudah_dibayar' ? '' : 'hidden' }}">
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Tanggal Bayar</label>
                                <input type="date" name="tanggal_pembayaran_denda"
                                       value="{{ $pengembalian->tanggal_pembayaran_denda ? $pengembalian->tanggal_pembayaran_denda->format('Y-m-d') : '' }}"
                                       class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-green-400 outline-none">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold text-gray-500 uppercase tracking-wider mb-1">Catatan Denda</label>
                                @php
                                    $dendaRecord = $pengembalian->denda->first();
                                @endphp
                                <input type="text" name="catatan_pembayaran_denda"
                                       value="{{ $dendaRecord->catatan ?? '' }}"
                                       placeholder="Keterangan denda"
                                       class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-amber-400 outline-none">
                            </div>
                        </div>
                    </div>

                    {{-- Catatan Pengembalian --}}
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                        <div class="bg-gradient-to-r from-gray-500 to-gray-600 px-5 py-3.5">
                            <h3 class="text-[13px] font-bold text-white flex items-center gap-2">
                                <i class="fas fa-sticky-note"></i> Catatan
                            </h3>
                        </div>
                        <div class="p-4">
                            <textarea name="catatan_pengembalian" rows="3"
                                      class="w-full text-xs px-3 py-2 border border-gray-200 rounded-lg focus:ring-2 focus:ring-gray-400 outline-none resize-none"
                                      placeholder="Catatan tambahan...">{{ $pengembalian->catatan }}</textarea>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <button type="submit"
                            class="w-full py-3 bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 text-white rounded-xl text-xs font-bold transition-all shadow-lg">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusSelect = document.getElementById('status_pembayaran_denda');
    const tanggalSection = document.getElementById('tanggalPembayaranSection');

    if (statusSelect) {
        statusSelect.addEventListener('change', function() {
            tanggalSection.classList.toggle('hidden', this.value !== 'sudah_dibayar');
        });
    }
});
</script>
@endsection