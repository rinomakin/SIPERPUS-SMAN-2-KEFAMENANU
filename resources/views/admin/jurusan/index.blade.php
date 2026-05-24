@extends('layouts.admin')

@section('title', 'Data Jurusan')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
<style>
@keyframes fadeInUp { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
.anim-up { animation: fadeInUp 0.4s ease forwards; }
.anim-d1 { animation-delay:.05s; opacity:0; }
.anim-d2 { animation-delay:.10s; opacity:0; }
.anim-d3 { animation-delay:.15s; opacity:0; }
.anim-d4 { animation-delay:.20s; opacity:0; }
.anim-d5 { animation-delay:.25s; opacity:0; }

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

.glass-card {
    background:rgba(255,255,255,.97); backdrop-filter:blur(20px);
    border-radius:16px; border:1px solid rgba(255,255,255,.8);
    box-shadow:0 4px 24px rgba(0,0,0,.06); overflow:hidden;
}

#jurusanTable_wrapper .dataTables_filter input {
    border:1px solid #e5e7eb; border-radius:8px; padding:6px 12px;
    font-size:13px; outline:none; transition:.2s;
}
#jurusanTable_wrapper .dataTables_filter input:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.1); }
#jurusanTable_wrapper .dataTables_length select {
    border:1px solid #e5e7eb; border-radius:8px; padding:5px 8px; font-size:13px;
}
#jurusanTable_wrapper .dataTables_info { font-size:12px; color:#9ca3af; }
#jurusanTable_wrapper .dataTables_paginate .paginate_button {
    border-radius:8px !important; font-size:13px !important; padding:4px 10px !important;
    transition:.15s !important;
}
#jurusanTable_wrapper .dataTables_paginate .paginate_button.current,
#jurusanTable_wrapper .dataTables_paginate .paginate_button.current:hover {
    background:#f59e0b !important; color:#fff !important; border-color:#f59e0b !important;
}
#jurusanTable_wrapper .dataTables_paginate .paginate_button:hover {
    background:#f3f4f6 !important; color:#374151 !important; border-color:#e5e7eb !important;
}

.jurusan-table tbody tr { transition:background .15s; }
.jurusan-table tbody tr:hover { background:#fffbeb; }
.jurusan-table thead th {
    background:#fffbeb; font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:.8px; color:#b45309; padding:12px 16px; border-bottom:2px solid #fef3c7;
    white-space:nowrap;
}
.jurusan-table tbody td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #fffbeb; }

.badge-aktif    { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-nonaktif { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

.kode-badge {
    background:linear-gradient(135deg,#f59e0b,#d97706);
    color:#fff; padding:3px 10px; border-radius:8px; font-size:11px; font-weight:700;
    letter-spacing:.5px; font-family:monospace;
}

.btn-edit {
    display:inline-flex; align-items:center; gap:4px;
    background:#fef3c7; color:#d97706; border:none; border-radius:8px;
    padding:5px 10px; font-size:11px; font-weight:600; cursor:pointer; transition:.2s;
}
.btn-edit:hover { background:#d97706; color:#fff; }
.btn-delete {
    display:inline-flex; align-items:center; gap:4px;
    background:#fee2e2; color:#dc2626; border:none; border-radius:8px;
    padding:5px 10px; font-size:11px; font-weight:600; cursor:pointer; transition:.2s;
}
.btn-delete:hover { background:#dc2626; color:#fff; }

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
.form-control:focus { border-color:#f59e0b; box-shadow:0 0 0 3px rgba(245,158,11,.1); }
.btn-primary {
    background:linear-gradient(135deg,#f59e0b,#d97706); color:#fff;
    border:none; border-radius:10px; padding:10px 20px; font-size:13px;
    font-weight:600; cursor:pointer; transition:.2s; width:100%;
}
.btn-primary:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 4px 12px rgba(245,158,11,.4); }
.btn-secondary {
    background:#f1f5f9; color:#64748b; border:none; border-radius:10px;
    padding:10px 20px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; width:100%;
}
.btn-secondary:hover { background:#e2e8f0; }
</style>
@endpush

@section('content')
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6 anim-up anim-d1">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Jurusan</h2>
        <p class="text-sm text-gray-400 mt-1">Kelola data jurusan dan program keahlian</p>
    </div>
    @if(Auth::user()->hasPermission('jurusan.create') || Auth::user()->isAdmin())
    <button onclick="openTambahModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-plus"></i>
        Tambah Jurusan
    </button>
    @endif
</div>

<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="stat-card anim-up anim-d2">
        <div class="stat-bg" style="background:#f59e0b;"></div>
        <div class="stat-icon mb-3" style="background:#fef3c7; color:#f59e0b;">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-value" style="color:#f59e0b;">{{ $totalJurusan }}</div>
        <div class="stat-label">Total Jurusan</div>
    </div>
    <div class="stat-card anim-up anim-d3">
        <div class="stat-bg" style="background:#22c55e;"></div>
        <div class="stat-icon mb-3" style="background:#dcfce7; color:#16a34a;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value" style="color:#16a34a;">{{ $jurusanAktif }}</div>
        <div class="stat-label">Jurusan Aktif</div>
    </div>
    <div class="stat-card anim-up anim-d4 col-span-2 lg:col-span-1">
        <div class="stat-bg" style="background:#ef4444;"></div>
        <div class="stat-icon mb-3" style="background:#fee2e2; color:#dc2626;">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-value" style="color:#dc2626;">{{ $jurusanNonaktif }}</div>
        <div class="stat-label">Jurusan Nonaktif</div>
    </div>
</div>

<div class="glass-card p-6 anim-up anim-d5">
    <div class="overflow-x-auto">
        <table id="jurusanTable" class="jurusan-table w-full" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Jurusan</th>
                    <th>Nama Jurusan</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th class="no-sort">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jurusan as $index => $item)
                <tr>
                    <td>{{ $index + 1 + ($jurusan->currentPage() - 1) * $jurusan->perPage() }}</td>
                    <td><span class="kode-badge">{{ $item->kode_jurusan }}</span></td>
                    <td class="font-medium text-gray-800">{{ $item->nama_jurusan }}</td>
                    <td class="text-gray-500 max-w-[200px] truncate">{{ $item->deskripsi ?? '-' }}</td>
                    <td>
                        @if($item->status)
                            <span class="badge-aktif">Aktif</span>
                        @else
                            <span class="badge-nonaktif">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            @if(Auth::user()->hasPermission('jurusan.edit') || Auth::user()->isAdmin())
                            <button onclick="openEditModal({{ $item->id }}, '{{ $item->kode_jurusan }}', '{{ addslashes($item->nama_jurusan) }}', '{{ addslashes($item->deskripsi) }}', {{ $item->status }})"
                                    class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            @endif
                            @if(Auth::user()->hasPermission('jurusan.delete') || Auth::user()->isAdmin())
                            <button onclick="deleteJurusan({{ $item->id }}, '{{ addslashes($item->nama_jurusan) }}')"
                                    class="btn-delete">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 bg-amber-50 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-graduation-cap text-2xl text-amber-300"></i>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700">Belum ada data jurusan</h4>
                            <p class="text-xs text-gray-400">Klik tombol "Tambah Jurusan" untuk menambahkan jurusan baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="tambahModal" class="modal-overlay" onclick="handleOverlayClick(event,'tambahModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-plus-circle text-amber-500 mr-2"></i>Tambah Jurusan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Lengkapi data jurusan baru</p>
            </div>
            <button class="modal-close" onclick="closeTambahModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="tambahForm" method="POST" action="{{ route('jurusan.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Kode Jurusan <span class="text-red-500">*</span></label>
                <input type="text" name="kode_jurusan" id="kode_jurusan" required
                       class="form-control" placeholder="Contoh: TJKT">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Jurusan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_jurusan" id="nama_jurusan" required
                       class="form-control" placeholder="Contoh: Teknik Jaringan Komputer dan Telekomunikasi">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="3"
                          class="form-control" placeholder="Deskripsi jurusan (opsional)"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status" required class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="button" onclick="closeTambahModal()" class="btn-secondary">Batal</button>
                <button type="submit" id="btnSimpan" class="btn-primary">
                    <i class="fas fa-save mr-2"></i>Simpan Jurusan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event,'editModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-edit text-amber-500 mr-2"></i>Edit Jurusan</h3>
                <p class="text-xs text-gray-400 mt-0.5">Perbarui data jurusan</p>
            </div>
            <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Kode Jurusan <span class="text-red-500">*</span></label>
                <input type="text" name="kode_jurusan" id="edit_kode_jurusan" required
                       class="form-control" placeholder="Contoh: TJKT">
            </div>

            <div class="form-group">
                <label class="form-label">Nama Jurusan <span class="text-red-500">*</span></label>
                <input type="text" name="nama_jurusan" id="edit_nama_jurusan" required
                       class="form-control" placeholder="Contoh: Teknik Jaringan Komputer dan Telekomunikasi">
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="3"
                          class="form-control" placeholder="Deskripsi jurusan (opsional)"></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" id="edit_status" required class="form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
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

<form id="deleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function () {
    $('#jurusanTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            emptyTable: 'Tidak ada data jurusan',
            zeroRecords: 'Data tidak ditemukan',
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        columnDefs: [
            { orderable: false, targets: 5 },
        ],
        order: [[0, 'asc']],
        responsive: true,
    });

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
        confirmButtonColor: '#f59e0b',
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
        confirmButtonColor: '#f59e0b',
        confirmButtonText: 'OK',
    });
    @endif
});

function openTambahModal() {
    document.getElementById('tambahModal').classList.add('active');
}

function closeTambahModal() {
    document.getElementById('tambahModal').classList.remove('active');
    document.getElementById('tambahForm').reset();
}

function openEditModal(id, kode, nama, deskripsi, status) {
    document.getElementById('editForm').action = '{{ url('admin/jurusan') }}/' + id;
    document.getElementById('edit_kode_jurusan').value = kode;
    document.getElementById('edit_nama_jurusan').value = nama;
    document.getElementById('edit_deskripsi').value = deskripsi === 'null' || deskripsi === '' ? '' : deskripsi;
    document.getElementById('edit_status').value = status;
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

function deleteJurusan(id, nama) {
    Swal.fire({
        title: 'Hapus Jurusan?',
        html: `Jurusan <strong>${nama}</strong> akan dihapus permanen.`,
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
            form.action = '{{ url('admin/jurusan') }}/' + id;
            form.submit();
        }
    });
}
</script>
@endpush
@endsection