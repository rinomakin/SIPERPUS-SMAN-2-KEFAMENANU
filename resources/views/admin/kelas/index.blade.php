@extends('layouts.admin')

@section('title', 'Data Kelas')

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
#kelasTable_wrapper .dataTables_filter input {
    border:1px solid #e5e7eb; border-radius:8px; padding:6px 12px;
    font-size:13px; outline:none; transition:.2s;
}
#kelasTable_wrapper .dataTables_filter input:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
#kelasTable_wrapper .dataTables_length select {
    border:1px solid #e5e7eb; border-radius:8px; padding:5px 8px; font-size:13px;
}
#kelasTable_wrapper .dataTables_info { font-size:12px; color:#9ca3af; }
#kelasTable_wrapper .dataTables_paginate .paginate_button {
    border-radius:8px !important; font-size:13px !important; padding:4px 10px !important;
    transition:.15s !important;
}
#kelasTable_wrapper .dataTables_paginate .paginate_button.current,
#kelasTable_wrapper .dataTables_paginate .paginate_button.current:hover {
    background:#6366f1 !important; color:#fff !important; border-color:#6366f1 !important;
}
#kelasTable_wrapper .dataTables_paginate .paginate_button:hover {
    background:#f3f4f6 !important; color:#374151 !important; border-color:#e5e7eb !important;
}

/* ===== Table Row ===== */
.kelas-table tbody tr { transition:background .15s; }
.kelas-table tbody tr:hover { background:#f8fafc; }
.kelas-table thead th {
    background:#f8fafc; font-size:10px; font-weight:700; text-transform:uppercase;
    letter-spacing:.8px; color:#94a3b8; padding:12px 16px; border-bottom:2px solid #f1f5f9;
    white-space:nowrap;
}
.kelas-table tbody td { padding:12px 16px; font-size:13px; color:#374151; border-bottom:1px solid #f8fafc; }

/* ===== Badge Status ===== */
.badge-aktif    { background:#dcfce7; color:#16a34a; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-nonaktif { background:#fee2e2; color:#dc2626; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:700; }

/* ===== Kode Badge ===== */
.kode-badge {
    background:linear-gradient(135deg,#6366f1,#8b5cf6);
    color:#fff; padding:3px 10px; border-radius:8px; font-size:11px; font-weight:700;
    letter-spacing:.5px; font-family:monospace;
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
.form-control:focus { border-color:#6366f1; box-shadow:0 0 0 3px rgba(99,102,241,.1); }
.form-control[readonly] { background:#f8fafc; color:#94a3b8; cursor:not-allowed; }
.form-control.kode-input {
    background:linear-gradient(135deg,#f5f3ff,#ede9fe);
    border-color:#c4b5fd; color:#5b21b6; font-weight:700;
    font-family:monospace; font-size:14px; letter-spacing:.5px;
}
.kode-hint { font-size:11px; color:#8b5cf6; margin-top:5px; display:flex; align-items:center; gap:4px; }
.btn-primary {
    background:linear-gradient(135deg,#6366f1,#8b5cf6); color:#fff;
    border:none; border-radius:10px; padding:10px 20px; font-size:13px;
    font-weight:600; cursor:pointer; transition:.2s; width:100%;
}
.btn-primary:hover { opacity:.9; transform:translateY(-1px); box-shadow:0 4px 12px rgba(99,102,241,.4); }
.btn-secondary {
    background:#f1f5f9; color:#64748b; border:none; border-radius:10px;
    padding:10px 20px; font-size:13px; font-weight:600; cursor:pointer; transition:.2s; width:100%;
}
.btn-secondary:hover { background:#e2e8f0; }
.generating-indicator { display:inline-flex; align-items:center; gap:6px; font-size:11px; color:#8b5cf6; }
@keyframes spin { to { transform:rotate(360deg); } }
.spin-icon { animation:spin 1s linear infinite; }
</style>

<!-- ── Page Header ─────────────────────────────────── -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6 anim-up anim-d1">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Kelas</h2>
        <p class="text-sm text-gray-400 mt-1">Kelola data kelas dan rombongan belajar</p>
    </div>
    @if(Auth::user()->hasPermission('kelas.create') || Auth::user()->isAdmin())
    <button onclick="openTambahModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-plus"></i>
        Tambah Kelas
    </button>
    @endif
</div>

<!-- ── Stat Cards ─────────────────────────────────── -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="stat-card anim-up anim-d2">
        <div class="stat-bg" style="background:#6366f1;"></div>
        <div class="stat-icon mb-3" style="background:#ede9fe; color:#6366f1;">
            <i class="fas fa-layer-group"></i>
        </div>
        <div class="stat-value" style="color:#6366f1;">{{ $totalKelas }}</div>
        <div class="stat-label">Total Kelas</div>
    </div>
    <div class="stat-card anim-up anim-d3">
        <div class="stat-bg" style="background:#22c55e;"></div>
        <div class="stat-icon mb-3" style="background:#dcfce7; color:#16a34a;">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-value" style="color:#16a34a;">{{ $kelasAktif }}</div>
        <div class="stat-label">Kelas Aktif</div>
    </div>
    <div class="stat-card anim-up anim-d4 col-span-2 lg:col-span-1">
        <div class="stat-bg" style="background:#ef4444;"></div>
        <div class="stat-icon mb-3" style="background:#fee2e2; color:#dc2626;">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-value" style="color:#dc2626;">{{ $kelasNonaktif }}</div>
        <div class="stat-label">Kelas Nonaktif</div>
    </div>
</div>

<!-- ── Data Table Card ─────────────────────────────── -->
<div class="glass-card p-6 anim-up anim-d5">
    <div class="overflow-x-auto">
        <table id="kelasTable" class="kelas-table w-full" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode Kelas</th>
                    <th>Nama Kelas</th>
                    <th>Jurusan</th>
                    <th>Tahun Ajaran</th>
                    <th>Jml Anggota</th>
                    <th>Status</th>
                    <th class="no-sort">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kelas as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><span class="kode-badge">{{ $item->kode_kelas }}</span></td>
                    <td class="font-medium text-gray-800">{{ $item->nama_kelas }}</td>
                    <td>{{ $item->jurusan->nama_jurusan ?? '-' }}</td>
                    <td>{{ $item->tahun_ajaran }}</td>
                    <td>
                        <span class="inline-flex items-center gap-1 text-gray-700 font-semibold">
                            <i class="fas fa-users text-indigo-400 text-xs"></i>
                            {{ $item->anggota->count() }}
                        </span>
                    </td>
                    <td>
                        @php $aktif = ($item->status === 'aktif' || $item->status == 1); @endphp
                        <span class="{{ $aktif ? 'badge-aktif' : 'badge-nonaktif' }}">{{ $aktif ? 'Aktif' : 'Nonaktif' }}</span>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            @if(Auth::user()->hasPermission('kelas.edit') || Auth::user()->isAdmin())
                            <button onclick="openEditModal({{ $item->id }}, '{{ $item->kode_kelas }}', '{{ addslashes($item->nama_kelas) }}', {{ $item->jurusan_id }}, '{{ $item->tahun_ajaran }}', '{{ $item->status }}')"
                                    class="btn-edit">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                            @endif
                            @if(Auth::user()->hasPermission('kelas.delete') || Auth::user()->isAdmin())
                            <button onclick="deleteKelas({{ $item->id }}, '{{ addslashes($item->nama_kelas) }}')"
                                    class="btn-delete">
                                <i class="fas fa-trash"></i> Hapus
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center gap-3">
                            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center">
                                <i class="fas fa-school text-2xl text-indigo-300"></i>
                            </div>
                            <h4 class="text-sm font-semibold text-gray-700">Belum ada data kelas</h4>
                            <p class="text-xs text-gray-400">Klik tombol "Tambah Kelas" untuk menambahkan kelas baru</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL TAMBAH KELAS
══════════════════════════════════════════════════════ -->
<div id="tambahModal" class="modal-overlay" onclick="handleOverlayClick(event,'tambahModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-plus-circle text-indigo-500 mr-2"></i>Tambah Kelas</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kode kelas digenerate otomatis</p>
            </div>
            <button class="modal-close" onclick="closeTambahModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="tambahForm" method="POST" action="{{ route('kelas.store') }}">
            @csrf

            <div class="form-group">
                <label class="form-label">Jurusan <span class="text-red-500">*</span></label>
                <select name="jurusan_id" id="jurusan_id" required class="form-control" onchange="generateKodeKelas()">
                    <option value="">— Pilih Jurusan —</option>
                    @foreach($jurusan as $jur)
                        <option value="{{ $jur->id }}" data-kode="{{ $jur->kode_jurusan }}">{{ $jur->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tahun Ajaran <span class="text-red-500">*</span></label>
                <input type="text" name="tahun_ajaran" id="tahun_ajaran" required
                       class="form-control" placeholder="2025/2026"
                       oninput="formatTahunAjaran(this)" onblur="generateKodeKelas()">
            </div>

            <div class="form-group">
                <label class="form-label">Kode Kelas</label>
                <div style="position:relative;">
                    <input type="text" name="kode_kelas" id="kode_kelas" required readonly
                           class="form-control kode-input" placeholder="Otomatis digenerate…">
                    <span id="kodeGenerating" class="generating-indicator" style="display:none;position:absolute;right:12px;top:50%;transform:translateY(-50%);">
                        <i class="fas fa-circle-notch spin-icon"></i> Generating…
                    </span>
                </div>
                <p class="kode-hint"><i class="fas fa-magic"></i> Terisi otomatis berdasarkan jurusan &amp; tahun ajaran</p>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Kelas <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kelas" id="nama_kelas" required
                       class="form-control" placeholder="Contoh: X TKJ 1">
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
                    <i class="fas fa-save mr-2"></i>Simpan Kelas
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL EDIT KELAS
══════════════════════════════════════════════════════ -->
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event,'editModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-edit text-purple-500 mr-2"></i>Edit Kelas</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kode kelas tidak dapat diubah</p>
            </div>
            <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>

        <form id="editForm" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label class="form-label">Jurusan <span class="text-red-500">*</span></label>
                <select name="jurusan_id" id="edit_jurusan_id" required class="form-control">
                    <option value="">— Pilih Jurusan —</option>
                    @foreach($jurusan as $jur)
                        <option value="{{ $jur->id }}">{{ $jur->nama_jurusan }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Tahun Ajaran <span class="text-red-500">*</span></label>
                <input type="text" name="tahun_ajaran" id="edit_tahun_ajaran" required
                       class="form-control" placeholder="2025/2026">
            </div>

            <div class="form-group">
                <label class="form-label">Kode Kelas</label>
                <input type="text" id="edit_kode_kelas" readonly class="form-control kode-input">
                <p class="kode-hint"><i class="fas fa-lock"></i> Kode tidak dapat diubah setelah dibuat</p>
            </div>

            <div class="form-group">
                <label class="form-label">Nama Kelas <span class="text-red-500">*</span></label>
                <input type="text" name="nama_kelas" id="edit_nama_kelas" required
                       class="form-control" placeholder="Contoh: X TKJ 1">
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
    $('#kelasTable').DataTable({
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
            emptyTable: 'Tidak ada data kelas',
            zeroRecords: 'Data tidak ditemukan',
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50],
        columnDefs: [
            { orderable: false, targets: 7 },  // Aksi column
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
        confirmButtonColor: '#6366f1',
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
        confirmButtonColor: '#6366f1',
        confirmButtonText: 'OK',
    });
    @endif
});

// ── Modal Open/Close ─────────────────────────────────
function openTambahModal() {
    // Pre-fill tahun ajaran dengan tahun akademik berjalan
    const now     = new Date();
    const month   = now.getMonth() + 1; // 1-12
    const thisYear = now.getFullYear();
    const startYear = month >= 7 ? thisYear : thisYear - 1;
    const tahunAjaran = startYear + '/' + (startYear + 1);
    document.getElementById('tahun_ajaran').value = tahunAjaran;

    // Pilih jurusan pertama secara default
    const jurusanSelect = document.getElementById('jurusan_id');
    if (jurusanSelect.options.length > 1) {
        jurusanSelect.selectedIndex = 1;
    }

    document.getElementById('tambahModal').classList.add('active');

    // Auto-generate kode
    generateKodeKelas();
}

function closeTambahModal() {
    document.getElementById('tambahModal').classList.remove('active');
    document.getElementById('tambahForm').reset();
    document.getElementById('kode_kelas').value = '';
}

function openEditModal(id, kode, nama, jurusanId, tahunAjaran, status) {
    document.getElementById('editForm').action = '{{ url('admin/kelas') }}/' + id;
    document.getElementById('edit_kode_kelas').value   = kode;
    document.getElementById('edit_nama_kelas').value   = nama;
    document.getElementById('edit_jurusan_id').value   = jurusanId;
    document.getElementById('edit_tahun_ajaran').value = tahunAjaran;
    // Normalise status → "1" / "0"
    const statusVal = (status === 'aktif' || status == 1) ? '1' : '0';
    document.getElementById('edit_status').value = statusVal;
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

// ── Auto-Generate Kode Kelas ─────────────────────────
function generateKodeKelas() {
    const jurusanId   = document.getElementById('jurusan_id').value;
    const tahunAjaran = document.getElementById('tahun_ajaran').value;
    const kodeInput   = document.getElementById('kode_kelas');
    const genIndicator = document.getElementById('kodeGenerating');

    if (!jurusanId || !tahunAjaran) {
        kodeInput.value = '';
        return;
    }

    // Validasi format tahun ajaran
    if (!/^\d{4}\/\d{4}$/.test(tahunAjaran)) return;

    genIndicator.style.display = 'inline-flex';
    kodeInput.value = '';

    $.ajax({
        url: '{{ route("kelas.generate-kode") }}',
        method: 'POST',
        data: { _token: '{{ csrf_token() }}', jurusan_id: jurusanId, tahun_ajaran: tahunAjaran },
        success: function (res) {
            kodeInput.value = res.kode;
            genIndicator.style.display = 'none';
        },
        error: function () {
            genIndicator.style.display = 'none';
            Swal.fire({ icon:'error', title:'Error', text:'Gagal generate kode kelas.', timer:2000, showConfirmButton:false });
        }
    });
}

// ── Format Tahun Ajaran Input ────────────────────────
function formatTahunAjaran(el) {
    let v = el.value.replace(/[^0-9\/]/g, '');
    if (v.length === 4 && !v.includes('/')) v += '/';
    el.value = v;
}

// ── Delete dengan SweetAlert2 ────────────────────────
function deleteKelas(id, nama) {
    Swal.fire({
        title: 'Hapus Kelas?',
        html: `Kelas <strong>${nama}</strong> akan dihapus permanen.<br><small class="text-gray-500">Kelas yang masih memiliki anggota tidak dapat dihapus.</small>`,
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
            form.action = '{{ url('admin/kelas') }}/' + id;
            form.submit();
        }
    });
}
</script>
@endpush
@endsection
