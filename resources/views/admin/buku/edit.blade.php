@extends('layouts.admin')

@section('title', 'Edit Buku')

@section('content')
<style>
@keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
@keyframes slideDown { from { opacity:0; max-height:0; } to { opacity:1; max-height:500px; } }
.anim-up { animation: fadeInUp .45s ease-out forwards; opacity:0; }
.d1 { animation-delay:.05s; } .d2 { animation-delay:.1s; } .d3 { animation-delay:.15s; }
.d4 { animation-delay:.2s; } .d5 { animation-delay:.25s; } .d6 { animation-delay:.3s; }
.form-input { transition:all .2s ease; }
.form-input:focus { box-shadow:0 0 0 3px rgba(59,130,246,.12); }
.section-header { display:flex; align-items:center; gap:10px; }
.section-icon { width:32px; height:32px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; flex-shrink:0; }
</style>

<div class="max-w-4xl mx-auto py-4 sm:py-6 px-0 sm:px-4">
    <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden anim-up">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-yellow-500 via-yellow-600 to-orange-600 px-5 sm:px-6 py-4">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm flex-shrink-0">
                    <i class="fas fa-edit text-white text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm sm:text-base font-bold text-white">Edit Buku</h2>
                    <p class="text-[10px] sm:text-xs text-yellow-100">Perbarui informasi buku "{{ $buku->judul_buku }}"</p>
                </div>
            </div>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('buku.update', $buku->id) }}" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-6">
            @csrf
            @method('PUT')

            {{-- Informasi Dasar --}}
            <div class="anim-up d1">
                <div class="section-header mb-4">
                    <div class="section-icon bg-blue-100"><i class="fas fa-info-circle text-blue-600"></i></div>
                    <h3 class="text-sm font-bold text-gray-800">Informasi Dasar</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="judul_buku" class="block text-xs font-semibold text-gray-600 mb-1.5">Judul Buku <span class="text-red-500">*</span></label>
                        <input type="text" id="judul_buku" name="judul_buku" value="{{ old('judul_buku', $buku->judul_buku) }}" required
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('judul_buku') border-red-400 @enderror"
                               placeholder="Masukkan judul buku">
                        @error('judul_buku') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="isbn" class="block text-xs font-semibold text-gray-600 mb-1.5">ISBN</label>
                        <input type="text" id="isbn" name="isbn" value="{{ old('isbn', $buku->isbn) }}"
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('isbn') border-red-400 @enderror"
                               placeholder="Masukkan ISBN">
                        @error('isbn') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="tahun_terbit" class="block text-xs font-semibold text-gray-600 mb-1.5">Tahun Terbit</label>
                        <input type="number" id="tahun_terbit" name="tahun_terbit" value="{{ old('tahun_terbit', $buku->tahun_terbit) }}"
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('tahun_terbit') border-red-400 @enderror"
                               placeholder="Contoh: 2024">
                        @error('tahun_terbit') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jumlah_halaman" class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Halaman</label>
                        <input type="number" id="jumlah_halaman" name="jumlah_halaman" value="{{ old('jumlah_halaman', $buku->jumlah_halaman) }}"
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('jumlah_halaman') border-red-400 @enderror"
                               placeholder="Jumlah halaman">
                        @error('jumlah_halaman') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="bahasa" class="block text-xs font-semibold text-gray-600 mb-1.5">Bahasa</label>
                        <input type="text" id="bahasa" name="bahasa" value="{{ old('bahasa', $buku->bahasa ?? 'Indonesia') }}"
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('bahasa') border-red-400 @enderror"
                               placeholder="Contoh: Indonesia">
                        @error('bahasa') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jumlah_stok" class="block text-xs font-semibold text-gray-600 mb-1.5">Jumlah Stok <span class="text-red-500">*</span></label>
                        <input type="number" id="jumlah_stok" name="jumlah_stok" value="{{ old('jumlah_stok', $buku->jumlah_stok) }}" required min="1"
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('jumlah_stok') border-red-400 @enderror"
                               placeholder="Jumlah stok">
                        @error('jumlah_stok') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="rak_id" class="block text-xs font-semibold text-gray-600 mb-1.5">Rak Buku</label>
                        <select id="rak_id" name="rak_id"
                                class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('rak_id') border-red-400 @enderror">
                            <option value="">Pilih Rak Buku</option>
                            @foreach($rakBuku as $rak)
                                <option value="{{ $rak->id }}" {{ old('rak_id', $buku->rak_id) == $rak->id ? 'selected' : '' }}>{{ $rak->nama_rak }} - {{ $rak->lokasi ?? 'Lokasi tidak ditentukan' }}</option>
                            @endforeach
                        </select>
                        @error('rak_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <hr class="border-gray-200 anim-up d2">

            {{-- Penulis & Penerbit --}}
            <div class="anim-up d2">
                <div class="section-header mb-4">
                    <div class="section-icon bg-emerald-100"><i class="fas fa-user-edit text-emerald-600"></i></div>
                    <h3 class="text-sm font-bold text-gray-800">Penulis & Penerbit</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label for="penulis" class="block text-xs font-semibold text-gray-600 mb-1.5">Penulis <span class="text-red-500">*</span></label>
                        <input type="text" id="penulis" name="penulis" required value="{{ old('penulis', $buku->pengarang) }}"
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('penulis') border-red-400 @enderror"
                               placeholder="Nama penulis">
                        @error('penulis') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="penerbit" class="block text-xs font-semibold text-gray-600 mb-1.5">Penerbit <span class="text-red-500">*</span></label>
                        <input type="text" id="penerbit" name="penerbit" required value="{{ old('penerbit', $buku->penerbit) }}"
                               class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('penerbit') border-red-400 @enderror"
                               placeholder="Nama penerbit">
                        @error('penerbit') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <hr class="border-gray-200 anim-up d3">

            {{-- Kategori & Jenis --}}
            <div class="anim-up d3">
                <div class="section-header mb-4">
                    <div class="section-icon bg-purple-100"><i class="fas fa-tags text-purple-600"></i></div>
                    <h3 class="text-sm font-bold text-gray-800">Kategori & Jenis</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="kategori_id" class="block text-xs font-semibold text-gray-600 mb-1.5">Kategori <span class="text-red-500">*</span></label>
                        <select id="kategori_id" name="kategori_id" required
                                class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('kategori_id') border-red-400 @enderror">
                            <option value="">Pilih Kategori</option>
                            @foreach($kategoris as $kat)
                                <option value="{{ $kat->id }}" {{ old('kategori_id', $buku->kategori_id) == $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                        @error('kategori_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jenis_id" class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Buku <span class="text-red-500">*</span></label>
                        <select id="jenis_id" name="jenis_id" required
                                class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('jenis_id') border-red-400 @enderror">
                            <option value="">Pilih Jenis</option>
                            @foreach($jenis as $jen)
                                <option value="{{ $jen->id }}" {{ old('jenis_id', $buku->jenis_id) == $jen->id ? 'selected' : '' }}>{{ $jen->nama_jenis }}</option>
                            @endforeach
                        </select>
                        @error('jenis_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="sumber_id" class="block text-xs font-semibold text-gray-600 mb-1.5">Sumber Buku</label>
                        <select id="sumber_id" name="sumber_id"
                                class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('sumber_id') border-red-400 @enderror">
                            <option value="">Pilih Sumber</option>
                            @foreach($sumber as $sum)
                                <option value="{{ $sum->id }}" {{ old('sumber_id', $buku->sumber_id) == $sum->id ? 'selected' : '' }}>{{ $sum->nama_sumber }}</option>
                            @endforeach
                        </select>
                        @error('sumber_id') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <hr class="border-gray-200 anim-up d4">

            {{-- Gambar Sampul --}}
            <div class="anim-up d4">
                <div class="section-header mb-4">
                    <div class="section-icon bg-pink-100"><i class="fas fa-image text-pink-600"></i></div>
                    <h3 class="text-sm font-bold text-gray-800">Gambar Sampul</h3>
                </div>
                <div class="flex flex-col sm:flex-row items-start gap-5">
                    @if($buku->gambar_sampul)
                    <div class="flex-shrink-0">
                        <div class="relative group">
                            <img src="{{ asset('uploads/' . $buku->gambar_sampul) }}" alt="Cover"
                                 class="w-28 h-36 object-cover rounded-xl border border-gray-200 shadow-sm">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 rounded-xl transition-all"></div>
                        </div>
                        <p class="text-[10px] text-gray-400 mt-1.5 text-center truncate max-w-[112px]">{{ $buku->gambar_sampul }}</p>
                    </div>
                    @endif
                    <div class="flex-1 w-full">
                        <label for="gambar_sampul" class="block text-xs font-semibold text-gray-600 mb-1.5">Upload Sampul Baru</label>
                        <div class="relative">
                            <input type="file" id="gambar_sampul" name="gambar_sampul" accept="image/*"
                                   class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all file:mr-3 file:py-1.5 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-yellow-50 file:text-yellow-700 hover:file:bg-yellow-100 @error('gambar_sampul') border-red-400 @enderror">
                        </div>
                        <p class="text-xs text-gray-400 mt-1.5">Format: JPG, PNG, GIF. Maksimal 2MB. Kosongkan jika tidak ingin mengubah gambar.</p>
                        @error('gambar_sampul') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
            <hr class="border-gray-200 anim-up d5">

            {{-- Status & Deskripsi --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 anim-up d5">
                <div>
                    <div class="section-header mb-4">
                        <div class="section-icon bg-green-100"><i class="fas fa-toggle-on text-green-600"></i></div>
                        <h3 class="text-sm font-bold text-gray-800">Status</h3>
                    </div>
                    <div>
                        <select id="status" name="status" required
                                class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all @error('status') border-red-400 @enderror">
                            <option value="tersedia" {{ old('status', $buku->status) == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="tidak_tersedia" {{ old('status', $buku->status) == 'tidak_tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                        </select>
                        @error('status') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                    </div>
                </div>
                <div class="sm:col-span-2">
                    <div class="section-header mb-4">
                        <div class="section-icon bg-orange-100"><i class="fas fa-align-left text-orange-600"></i></div>
                        <h3 class="text-sm font-bold text-gray-800">Deskripsi</h3>
                    </div>
                    <textarea id="deskripsi" name="deskripsi" rows="3"
                              class="form-input w-full px-4 py-2.5 text-sm border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-gray-50/60 transition-all resize-none @error('deskripsi') border-red-400 @enderror"
                              placeholder="Deskripsi singkat tentang buku...">{{ old('deskripsi', $buku->deskripsi) }}</textarea>
                    @error('deskripsi') <p class="mt-1 text-xs text-red-500">{{ $message }}</p> @enderror
                </div>
            </div>
            <hr class="border-gray-200 anim-up d5">

            {{-- Barcode --}}
            <div class="anim-up d6">
                @if($buku->barcode)
                <div class="bg-blue-50/80 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-barcode text-blue-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-blue-800">Barcode Saat Ini</h4>
                            <p class="text-xs text-blue-700 mt-1 font-mono">{{ $buku->barcode }}</p>
                            <p class="text-[10px] text-blue-500 mt-1">Barcode tidak dapat diubah setelah dibuat.</p>
                        </div>
                        <a href="{{ route('buku.print-barcode', $buku->id) }}" target="_blank"
                           class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-all shadow-sm">
                            <i class="fas fa-print"></i> Cetak
                        </a>
                    </div>
                </div>
                @else
                <div class="bg-amber-50/80 border border-amber-200 rounded-xl p-4">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0 mt-0.5">
                            <i class="fas fa-exclamation-triangle text-amber-600 text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h4 class="text-xs font-bold text-amber-800">Barcode Belum Ada</h4>
                            <p class="text-xs text-amber-700 mt-1">Setelah disimpan, Anda dapat generate barcode melalui halaman detail buku.</p>
                        </div>
                        <button type="button" id="generateBarcodeBtn"
                                class="flex-shrink-0 inline-flex items-center gap-1.5 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg transition-all shadow-sm">
                            <i class="fas fa-magic"></i> Generate
                        </button>
                    </div>
                </div>
                @endif
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-gray-200 anim-up d6">
                <a href="{{ route('buku.index') }}"
                   class="w-full sm:w-auto inline-flex items-center justify-center px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-xl transition-all">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
                <button type="submit"
                        class="w-full sm:w-auto inline-flex items-center justify-center px-6 py-2.5 bg-gradient-to-r from-yellow-500 to-orange-600 hover:from-yellow-600 hover:to-orange-700 text-white text-sm font-bold rounded-xl transition-all shadow-md hover:shadow-lg">
                    <i class="fas fa-save mr-2"></i>Update Buku
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('border-red-400');
                isValid = false;
            } else {
                field.classList.remove('border-red-400');
            }
        });
        if (!isValid) {
            e.preventDefault();
            Swal.fire({ icon: 'warning', title: 'Lengkapi Data', text: 'Mohon lengkapi semua field yang wajib diisi', confirmButtonColor: '#eab308' });
        }
    });

    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach(input => {
        input.addEventListener('blur', function() {
            if (this.hasAttribute('required') && !this.value.trim()) {
                this.classList.add('border-red-400');
            } else {
                this.classList.remove('border-red-400');
            }
        });
        input.addEventListener('input', function() {
            if (this.classList.contains('border-red-400') && this.value.trim()) {
                this.classList.remove('border-red-400');
            }
        });
    });

    const generateBarcodeBtn = document.getElementById('generateBarcodeBtn');
    if (generateBarcodeBtn) {
        generateBarcodeBtn.addEventListener('click', function() {
            const bukuId = this.getAttribute('data-buku-id');
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>Generating...';
            this.disabled = true;
            fetch(`/admin/buku/generate-barcode`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ buku_id: bukuId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                } else {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.message, confirmButtonColor: '#eab308' });
                    this.innerHTML = '<i class="fas fa-magic mr-1"></i>Generate';
                    this.disabled = false;
                }
            })
            .catch(() => {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Terjadi kesalahan', confirmButtonColor: '#eab308' });
                this.innerHTML = '<i class="fas fa-magic mr-1"></i>Generate';
                this.disabled = false;
            });
        });
    }
});
</script>
@endsection
