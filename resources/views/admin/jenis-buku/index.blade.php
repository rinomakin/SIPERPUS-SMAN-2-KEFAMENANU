@extends('layouts.admin')

@section('title', 'Data Jenis Buku')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
@endpush

@section('content')
<style>
/* ===== Animations ===== */
@keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
.anim-up { animation: fadeInUp 0.4s ease forwards; }
.anim-d1 { animation-delay:.05s; opacity:0; }
.anim-d2 { animation-delay:.10s; opacity:0; }
.anim-d3 { animation-delay:.15s; opacity:0; }
.anim-d4 { animation-delay:.20s; opacity:0; }
.anim-d5 { animation-delay:.25s; opacity:0; }

/* ===== Stat Cards ===== */
.stat-card {
    background:#fff; border-radius:16px; padding:20px; position:relative; overflow:hidden;
    border:1px solid rgba(0,0,0,.04);
    box-shadow:0 1px 3px rgba(0,0,0,.04),0 4px 12px rgba(0,0,0,.03);
    transition:all .3s ease;
}
.stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.08); }
.stat-icon { width:48px; height:48px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:18px; }
.stat-bg { position:absolute; top:-20px; right:-20px; width:90px; height:90px; border-radius:50%; opacity:.06; }
.stat-value { font-size:26px; font-weight:800; line-height:1.2; }
.stat-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:#9ca3af; }

/* ===== Glass Card ===== */
.glass-card {
    background:rgba(255,255,255,.97); backdrop-filter:blur(20px);
    border-radius:16px; border:1px solid rgba(255,255,255,.8);
    box-shadow:0 4px 24px rgba(0,0,0,.06); overflow:hidden;
}

/* ===== DataTable Overrides ===== */
#jenisTable_wrapper .dataTables_filter input {
    border:1px solid #e5e7eb; border-radius:8px; padding:6px 12px;
    font-size:13px; outline:none; transition:.2s;
}
#jenisTable_wrapper .dataTables_filter input:focus { border-color:#0ea5e9; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
#jenisTable_wrapper .dataTables_length select {
    border:1px solid #e5e7eb; border-radius:8px; padding:5px 8px; font-size:13px;
}
#jenisTable_wrapper .dataTables_info { font-size:12px; color:#9ca3af; }
#jenisTable_wrapper .dataTables_paginate .paginate_button {
    border-radius:8px !important; font-size:13px !important; padding:4px 10px !important; transition:.15s !important;
}
#jenisTable_wrapper .dataTables_paginate .paginate_button.current,
#jenisTable_wrapper .dataTables_paginate .paginate_button.current:hover {
    background:#0ea5e9 !important; color:#fff !important; border-color:#0ea5e9 !important;
}
#jenisTable_wrapper .dataTables_paginate .paginate_button:hover {
    background:#f3f4f6 !important; color:#374151 !important; border-color:#e5e7eb !important;
}

/* ===== Table ===== */
.jenis-table tbody tr { transition:background .15s; }
.jenis-table tbody tr:hover { background:#f8fafc; }
.jenis-table thead th {
    background:#f8fafc; font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:.8px; color:#94a3b8; padding:12px 16px; border-bottom:2px solid #f1f5f9; white-space:nowrap;
}
.jenis-table tbody td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f8fafc; }

/* ===== Badges ===== */
.badge-aktif    { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-nonaktif { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.kode-badge {
    background:linear-gradient(135deg,#0ea5e9,#38bdf8);
    color:#fff; padding:3px 10px; border-radius:8px; font-size:11px; font-weight:700;
    letter-spacing:.5px; font-family:monospace;
}
.buku-badge {
    display:inline-flex; align-items:center; gap:4px;
    background:#f0f9ff; color:#0369a1; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600;
}

/* ===== Action Buttons ===== */
.btn-edit {
    display:inline-flex; align-items:center; gap:4px;
    background:#ede9fe; color:#6d28d9; border:none; border-radius:8px;
    padding:5px 10px; font-size:11px; font-weight:600; cursor:pointer; transition:.2s;
}
.btn-edit:hover { background:#6d28d9; color:#fff; }
.btn-delete {
    display:inline-flex; align-items:center; gap:4px;
    background:#fee2e2; color:#dc2626; border:none; border-radius:8px;
    padding:5px 10px; font-size:11px; font-weight:600; cursor:pointer; transition:.2s;
}
.btn-delete:hover { background:#dc2626; color:#fff; }

/* ===== Modal ===== */
.modal-overlay {
    position:fixed; inset:0; background:rgba(0,0,0,.45); backdrop-filter:blur(4px);
    display:flex; align-items:center; justify-content:center; z-index:9999;
    opacity:0; pointer-events:none; transition:opacity .2s ease;
}
.modal-overlay.active { opacity:1; pointer-events:all; }
.modal-box {
    background:#fff; border-radius:20px; padding:28px; width:100%; max-width:480px;
    box-shadow:0 20px 60px rgba(0,0,0,.2);
    transform:scale(.95) translateY(10px); transition:.2s ease;
    max-height:90vh; overflow-y:auto;
}
.modal-overlay.active .modal-box { transform:scale(1) translateY(0); }
.modal-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.modal-title { font-size:17px; font-weight:700; color:#1e293b; }
.modal-close {
    width:32px; height:32px; border-radius:50%; border:none; background:#f1f5f9;
    color:#64748b; cursor:pointer; display:flex; align-items:center; justify-content:center;
    font-size:13px; transition:.2s;
}
.modal-close:hover { background:#e2e8f0; color:#1e293b; }
.form-group { margin-bottom:16px; }
.form-label { display:block; font-size:12px; font-weight:600; color:#374151; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
.form-control {
    width:100%; padding:10px 14px; border:1.5px solid #e5e7eb; border-radius:10px;
    font-size:13px; outline:none; transition:.2s; background:#fff;
}
.form-control:focus { border-color:#0ea5e9; box-shadow:0 0 0 3px rgba(14,165,233,.1); }
.form-control[readonly] { background:#f8fafc; color:#94a3b8; cursor:not-allowed; }
.form-textarea { resize:vertical; min-height:80px; }
.kode-auto-input {
    background:linear-gradient(135deg,#f0f9ff,#e0f2fe);
    border-color:#7dd3fc; color:#0369a1; font-weight:700;
    font-family:monospace; font-size:14px; letter-spacing:.5px;
}
.kode-hint { font-size:11px; color:#0ea5e9; margin-top:5px; display:flex; align-items:center; gap:4px; }
.btn-primary {
    background:linear-gradient(135deg,#0ea5e9,#38bdf8); color:#fff;
    border:none; border-radius:10px; padding:10px 20px; font-size:13px;
    font-weight:600; cursor:pointer; transition:.2s; width:100%;
}
.btn-primary:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 4px 12px rgba(14,165,233,.4); }
.btn-secondary {
    background:#f1f5f9; color:#64748b; border:none; border-radius:10px;
    padding:10px 20px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; width:100%;
}
.btn-secondary:hover { background:#e2e8f0; }
</style>

<!-- ── Page Header ─────────────────────────────────── -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6 anim-up anim-d1">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Jenis Buku</h2>
        <p class="text-sm text-gray-400 mt-1">Kelola kategori dan jenis koleksi buku perpustakaan</p>
    </div>
    @if(Auth::user()->hasPermission('jenis-buku.create') || Auth::user()->isAdmin())
    <button onclick="openTambahModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-sky-500 to-cyan-400 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-plus"></i>
        Tambah Jenis Buku
    </button>
    @endif
</div>

<!-- ── Stat Cards ─────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="stat-card anim-up anim-d2">
        <div class="stat-bg" style="background:#0ea5e9;"></div>
        <div class="stat-icon mb-3" style="background:#e0f2fe; color:#0ea5e9;">
            <i class="fas fa-tags"></i>
        </div>
        <div class="stat-value" style="color:#0ea5e9;">{{ $totalJenis }}</div>
        <div class="stat-label">Total Jenis</div>
    </div>
    <div class="stat-card anim-up anim-d3">
        <div class="stat-bg" style="background:#22c55e;"></div>
        <div class="stat-icon mb-3" style="background:#dcfce7; color:#16a34a;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value" style="color:#16a34a;">{{ $jenisAktif }}</div>
        <div class="stat-label">Jenis Aktif</div>
    </div>
    <div class="stat-card anim-up anim-d4 col-span-2 lg:col-span-1">
        <div class="stat-bg" style="background:#ef4444;"></div>
        <div class="stat-icon mb-3" style="background:#fee2e2; color:#dc2626;">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-value" style="color:#dc2626;">{{ $jenisNonaktif }}</div>
        <div class="stat-label">Tidak Aktif</div>
    </div>
</div>

<!-- ── Data Table Card ─────────────────────────────── -->
<div class="glass-card p-6 anim-up anim-d5">
    <div class="overflow-x-auto">
        <table id="jenisTable" class="jenis-table w-full" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Jenis</th>
                    <th>Nama Jenis</th>
                    <th>Deskripsi</th>
                    <th>Jml Buku</th>
                    <th>Status</th>
                    <th class="no-sort">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jenis as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><span class="kode-badge">{{ $item->kode_jenis }}</span></td>
                    <td class="font-semibold text-gray-800">{{ $item->nama_jenis }}</td>
                    <td class="text-gray-500 max-w-xs" style="max-width:220px; white-space:normal;">
                        {{ $item->deskripsi ?: '-' }}
                    </td>
                    <td>
                        <span class="buku-badge">
                            <i class="fas fa-book text-xs"></i>
                            {{ $item->buku_count }}
                        </span>
                    </td>
                    <td>
                        @if($item->status)
                            <span class="badge-aktif">Aktif</span>
                        @else
                            <span class="badge-nonaktif">Tidak Aktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            @if(Auth::user()->hasPermission('jenis-buku.edit') || Auth::user()->isAdmin())
                            <button onclick="openEditModal({{ $item->id }}, '{{ addslashes($item->nama_jenis) }}', '{{ $item->kode_jenis }}', '{{ addslashes($item->deskripsi ?? '') }}', {{ $item->status ? 1 : 0 }})"
                                    class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            @endif
                            @if(Auth::user()->hasPermission('jenis-buku.delete') || Auth::user()->isAdmin())
                            <button onclick="deleteJenis({{ $item->id }}, '{{ addslashes($item->nama_jenis) }}', {{ $item->buku_count }})"
                                    class="btn-delete">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL TAMBAH JENIS BUKU
══════════════════════════════════════════════════════ -->
<div id="tambahModal" class="modal-overlay" onclick="handleOverlayClick(event,'tambahModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-plus-circle text-sky-500 mr-2"></i>Tambah Jenis Buku</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kode jenis digenerate otomatis dari nama</p>
            </div>
            <button class="modal-close" onclick="closeTambahModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="tambahForm" method="POST" action="{{ route('jenis-buku.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Nama Jenis <span class="text-red-500">*</span></label>
                <input type="text" name="nama_jenis" id="nama_jenis" required
                       class="form-control" placeholder="Contoh: Fiksi, Non Fiksi, Ilmu Pengetahuan"
                       oninput="autoGenerateKode(this.value)">
            </div>

            <div class="form-group">
                <label class="form-label">Kode Jenis <span class="text-red-500">*</span></label>
                <input type="text" name="kode_jenis" id="kode_jenis" required maxlength="10"
                       class="form-control kode-auto-input"
                       placeholder="Otomatis dari nama…">
                <p class="kode-hint"><i class="fas fa-magic"></i> Digenerate otomatis — bisa diubah manual (maks. 10 karakter)</p>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3" maxlength="500"
                          class="form-control form-textarea"
                          placeholder="Deskripsi singkat jenis buku (opsional)"></textarea>
                <p style="font-size:11px; color:#94a3b8; margin-top:4px;">Maksimal 500 karakter</p>
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status" required class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="button" onclick="closeTambahModal()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Simpan Jenis Buku
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL EDIT JENIS BUKU
══════════════════════════════════════════════════════ -->
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event,'editModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-edit text-purple-500 mr-2"></i>Edit Jenis Buku</h3>
            </div>
            <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Nama Jenis <span class="text-red-500">*</span></label>
                <input type="text" name="nama_jenis" id="edit_nama_jenis" required
                       class="form-control" placeholder="Nama jenis buku">
            </div>

            <div class="form-group">
                <label class="form-label">Kode Jenis <span class="text-red-500">*</span></label>
                <input type="text" name="kode_jenis" id="edit_kode_jenis" required maxlength="10"
                       class="form-control kode-auto-input" placeholder="Kode jenis">
                <p class="kode-hint"><i class="fas fa-pen"></i> Dapat diubah (maks. 10 karakter)</p>
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="3" maxlength="500"
                          class="form-control form-textarea"
                          placeholder="Deskripsi singkat jenis buku (opsional)"></textarea>
                <p style="font-size:11px; color:#94a3b8; margin-top:4px;">Maksimal 500 karakter</p>
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" id="edit_status" required class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="button" onclick="closeEditModal()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Form Delete Hidden -->
<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
// ── DataTable Init ───────────────────────────────────
$(document).ready(function () {
    $('#jenisTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            emptyTable: 'Tidak ada data jenis buku',
            zeroRecords: 'Data tidak ditemukan',
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        columnDefs: [
            { orderable: false, targets: 6 }, // Aksi
        ],
        order: [[0, 'asc']],
        responsive: true,
    });

    // ── SweetAlert2: Flash Messages ──────────────────
    @if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: '{{ session('success') }}',
        timer: 2800,
        timerProgressBar: true,
        showConfirmButton: false,
        toast: true,
        position: 'top-end',
        background: '#f0fdf4',
        iconColor: '#16a34a',
    });
    @endif

    @if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: '{{ session('error') }}',
        confirmButtonColor: '#0ea5e9',
        confirmButtonText: 'OK',
    });
    @endif

    @if($errors->any())
    Swal.fire({
        icon: 'warning',
        title: 'Periksa Data',
        html: `<ul style="text-align:left;padding-left:20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>`,
        confirmButtonColor: '#0ea5e9',
        confirmButtonText: 'OK',
    });
    @endif
});

// ── Auto Generate Kode dari Nama ─────────────────────
function autoGenerateKode(nama) {
    if (!nama.trim()) {
        document.getElementById('kode_jenis').value = '';
        return;
    }
    const words = nama.trim().split(/\s+/);
    let kode = '';
    if (words.length === 1) {
        // Ambil 3 huruf pertama
        kode = words[0].substring(0, 3).toUpperCase();
    } else {
        // Ambil huruf pertama tiap kata (maks 5 kata)
        kode = words.slice(0, 5).map(w => w[0].toUpperCase()).join('');
    }
    document.getElementById('kode_jenis').value = kode;
}

// ── Modal Open/Close ─────────────────────────────────
function openTambahModal() {
    document.getElementById('tambahModal').classList.add('active');
    setTimeout(() => document.getElementById('nama_jenis').focus(), 200);
}

function closeTambahModal() {
    document.getElementById('tambahModal').classList.remove('active');
    document.getElementById('tambahForm').reset();
    document.getElementById('kode_jenis').value = '';
}

function openEditModal(id, nama, kode, deskripsi, status) {
    document.getElementById('editForm').action = '{{ url('admin/jenis-buku') }}/' + id;
    document.getElementById('edit_nama_jenis').value  = nama;
    document.getElementById('edit_kode_jenis').value  = kode;
    document.getElementById('edit_deskripsi').value   = deskripsi;
    document.getElementById('edit_status').value      = status;
    document.getElementById('editModal').classList.add('active');
}

function closeEditModal() {
    document.getElementById('editModal').classList.remove('active');
}

function handleOverlayClick(event, modalId) {
    if (event.target === document.getElementById(modalId)) {
        if (modalId === 'tambahModal') closeTambahModal();
        else closeEditModal();
    }
}

// ── Delete dengan SweetAlert2 ────────────────────────
function deleteJenis(id, nama, jumlahBuku) {
    if (jumlahBuku > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Dapat Dihapus',
            html: `Jenis buku <strong>${nama}</strong> masih digunakan oleh <strong>${jumlahBuku} buku</strong>.<br>Hapus atau pindahkan buku tersebut terlebih dahulu.`,
            confirmButtonColor: '#0ea5e9',
            confirmButtonText: 'Mengerti',
        });
        return;
    }

    Swal.fire({
        title: 'Hapus Jenis Buku?',
        html: `Jenis buku <strong>${nama}</strong> akan dihapus permanen.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="fas fa-trash mr-1"></i> Ya, Hapus',
        cancelButtonText: 'Batal',
        reverseButtons: true,
    }).then(result => {
        if (result.isConfirmed) {
            const form = document.getElementById('deleteForm');
            form.action = '{{ url('admin/jenis-buku') }}/' + id;
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
