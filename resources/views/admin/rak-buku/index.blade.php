@extends('layouts.admin')

@section('title', 'Data Rak Buku')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.min.css">
@endpush

@section('content')
<style>
@keyframes fadeInUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:translateY(0)}}
.anim-up{animation:fadeInUp .4s ease forwards}
.anim-d1{animation-delay:.05s;opacity:0}.anim-d2{animation-delay:.10s;opacity:0}
.anim-d3{animation-delay:.15s;opacity:0}.anim-d4{animation-delay:.20s;opacity:0}
.anim-d5{animation-delay:.25s;opacity:0}

.stat-card{background:#fff;border-radius:16px;padding:20px;position:relative;overflow:hidden;border:1px solid rgba(0,0,0,.04);box-shadow:0 1px 3px rgba(0,0,0,.04),0 4px 12px rgba(0,0,0,.03);transition:all .3s ease}
.stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.08)}
.stat-icon{width:48px;height:48px;border-radius:14px;display:flex;align-items:center;justify-content:center;font-size:18px}
.stat-bg{position:absolute;top:-20px;right:-20px;width:90px;height:90px;border-radius:50%;opacity:.06}
.stat-value{font-size:26px;font-weight:800;line-height:1.2}
.stat-label{font-size:11px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:#9ca3af}

.glass-card{background:rgba(255,255,255,.97);backdrop-filter:blur(20px);border-radius:16px;border:1px solid rgba(255,255,255,.8);box-shadow:0 4px 24px rgba(0,0,0,.06);overflow:hidden}

#rakTable_wrapper .dataTables_filter input{border:1px solid #e5e7eb;border-radius:8px;padding:6px 12px;font-size:13px;outline:none;transition:.2s}
#rakTable_wrapper .dataTables_filter input:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.1)}
#rakTable_wrapper .dataTables_length select{border:1px solid #e5e7eb;border-radius:8px;padding:5px 8px;font-size:13px}
#rakTable_wrapper .dataTables_info{font-size:12px;color:#9ca3af}
#rakTable_wrapper .dataTables_paginate .paginate_button{border-radius:8px !important;font-size:13px !important;padding:4px 10px !important;transition:.15s !important}
#rakTable_wrapper .dataTables_paginate .paginate_button.current,
#rakTable_wrapper .dataTables_paginate .paginate_button.current:hover{background:#8b5cf6 !important;color:#fff !important;border-color:#8b5cf6 !important}
#rakTable_wrapper .dataTables_paginate .paginate_button:hover{background:#f3f4f6 !important;color:#374151 !important;border-color:#e5e7eb !important}

.rak-table tbody tr{transition:background .15s}
.rak-table tbody tr:hover{background:#f8fafc}
.rak-table thead th{background:#f8fafc;font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.8px;color:#94a3b8;padding:12px 16px;border-bottom:2px solid #f1f5f9;white-space:nowrap}
.rak-table tbody td{padding:12px 16px;font-size:13px;color:#374151;border-bottom:1px solid #f8fafc}

.kode-badge{background:linear-gradient(135deg,#8b5cf6,#a78bfa);color:#fff;padding:3px 10px;border-radius:8px;font-size:11px;font-weight:700;letter-spacing:.5px;font-family:monospace}
.badge-aktif{background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}
.badge-nonaktif{background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700}

.kapasitas-bar{width:100%;height:6px;background:#f1f5f9;border-radius:4px;overflow:hidden;margin-top:4px}
.kapasitas-fill{height:100%;border-radius:4px;transition:width .3s}

.btn-edit{display:inline-flex;align-items:center;gap:4px;background:#ede9fe;color:#6d28d9;border:none;border-radius:8px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;transition:.2s}
.btn-edit:hover{background:#6d28d9;color:#fff}
.btn-delete{display:inline-flex;align-items:center;gap:4px;background:#fee2e2;color:#dc2626;border:none;border-radius:8px;padding:5px 10px;font-size:11px;font-weight:600;cursor:pointer;transition:.2s}
.btn-delete:hover{background:#dc2626;color:#fff}
.btn-delete-disabled{display:inline-flex;align-items:center;gap:4px;background:#f1f5f9;color:#94a3b8;border:none;border-radius:8px;padding:5px 10px;font-size:11px;font-weight:600;cursor:not-allowed}

.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);backdrop-filter:blur(4px);display:flex;align-items:center;justify-content:center;z-index:9999;opacity:0;pointer-events:none;transition:opacity .2s ease}
.modal-overlay.active{opacity:1;pointer-events:all}
.modal-box{background:#fff;border-radius:20px;padding:28px;width:100%;max-width:520px;box-shadow:0 20px 60px rgba(0,0,0,.2);transform:scale(.95) translateY(10px);transition:.2s ease;max-height:92vh;overflow-y:auto}
.modal-overlay.active .modal-box{transform:scale(1) translateY(0)}
.modal-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:20px}
.modal-title{font-size:17px;font-weight:700;color:#1e293b}
.modal-close{width:32px;height:32px;border-radius:50%;border:none;background:#f1f5f9;color:#64748b;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:13px;transition:.2s}
.modal-close:hover{background:#e2e8f0;color:#1e293b}
.form-group{margin-bottom:16px}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-label{display:block;font-size:12px;font-weight:600;color:#374151;margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px}
.form-control{width:100%;padding:10px 14px;border:1.5px solid #e5e7eb;border-radius:10px;font-size:13px;outline:none;transition:.2s;background:#fff}
.form-control:focus{border-color:#8b5cf6;box-shadow:0 0 0 3px rgba(139,92,246,.1)}
.form-textarea{resize:vertical;min-height:70px}
.kode-auto-input{background:linear-gradient(135deg,#faf5ff,#ede9fe);border-color:#c4b5fd;color:#5b21b6;font-weight:700;font-family:monospace;font-size:14px;letter-spacing:.5px}
.kode-hint{font-size:11px;color:#8b5cf6;margin-top:5px;display:flex;align-items:center;gap:4px}
.btn-primary{background:linear-gradient(135deg,#8b5cf6,#a78bfa);color:#fff;border:none;border-radius:10px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;transition:.2s;width:100%}
.btn-primary:hover{opacity:.9;transform:translateY(-1px);box-shadow:0 4px 12px rgba(139,92,246,.4)}
.btn-secondary{background:#f1f5f9;color:#64748b;border:none;border-radius:10px;padding:10px 20px;font-size:13px;font-weight:600;cursor:pointer;transition:.2s;width:100%}
.btn-secondary:hover{background:#e2e8f0}
</style>

<!-- Header -->
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6 anim-up anim-d1">
    <div>
        <h2 class="text-2xl font-bold text-gray-800">Data Rak Buku</h2>
        <p class="text-sm text-gray-400 mt-1">Kelola rak dan lokasi penyimpanan buku perpustakaan</p>
    </div>
    @if(Auth::user()->hasPermission('rak-buku.create') || Auth::user()->isAdmin())
    <button onclick="openTambahModal()"
            class="flex items-center gap-2 bg-gradient-to-r from-violet-500 to-purple-600 text-white px-5 py-2.5 rounded-xl font-semibold text-sm shadow-md hover:shadow-lg hover:-translate-y-0.5 transition-all">
        <i class="fas fa-plus"></i> Tambah Rak Buku
    </button>
    @endif
</div>

<!-- Stat Cards -->
<div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
    <div class="stat-card anim-up anim-d2">
        <div class="stat-bg" style="background:#8b5cf6"></div>
        <div class="stat-icon mb-3" style="background:#ede9fe;color:#8b5cf6"><i class="fas fa-archive"></i></div>
        <div class="stat-value" style="color:#8b5cf6">{{ $totalRak }}</div>
        <div class="stat-label">Total Rak</div>
    </div>
    <div class="stat-card anim-up anim-d3">
        <div class="stat-bg" style="background:#22c55e"></div>
        <div class="stat-icon mb-3" style="background:#dcfce7;color:#16a34a"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value" style="color:#16a34a">{{ $rakAktif }}</div>
        <div class="stat-label">Rak Aktif</div>
    </div>
    <div class="stat-card anim-up anim-d4 col-span-2 lg:col-span-1">
        <div class="stat-bg" style="background:#ef4444"></div>
        <div class="stat-icon mb-3" style="background:#fee2e2;color:#dc2626"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value" style="color:#dc2626">{{ $rakNonaktif }}</div>
        <div class="stat-label">Nonaktif</div>
    </div>
</div>

<!-- DataTable -->
<div class="glass-card p-6 anim-up anim-d5">
    <div class="overflow-x-auto">
        <table id="rakTable" class="rak-table w-full" style="width:100%">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Nama Rak</th>
                    <th>Lokasi</th>
                    <th>Kapasitas</th>
                    <th>Terisi</th>
                    <th>Status</th>
                    <th class="no-sort">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rakBuku as $index => $rak)
                @php $pct = $rak->kapasitas > 0 ? min(($rak->jumlah_buku / $rak->kapasitas) * 100, 100) : 0; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td><span class="kode-badge">{{ $rak->kode_rak }}</span></td>
                    <td>
                        <div class="font-semibold text-gray-800">{{ $rak->nama_rak }}</div>
                        @if($rak->deskripsi)
                            <div class="text-xs text-gray-400 mt-0.5" style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $rak->deskripsi }}</div>
                        @endif
                    </td>
                    <td class="text-gray-500">{{ $rak->lokasi ?: '—' }}</td>
                    <td class="font-semibold text-gray-700">{{ $rak->kapasitas }}</td>
                    <td>
                        <div class="text-xs font-semibold {{ $rak->isFull() ? 'text-red-600' : 'text-gray-700' }}">
                            {{ $rak->jumlah_buku }}/{{ $rak->kapasitas }}
                            @if($rak->isFull()) <span class="text-red-500 ml-1">Penuh</span> @endif
                        </div>
                        <div class="kapasitas-bar">
                            <div class="kapasitas-fill" style="width:{{ $pct }}%;background:{{ $pct >= 90 ? '#ef4444' : ($pct >= 70 ? '#f59e0b' : '#8b5cf6') }}"></div>
                        </div>
                    </td>
                    <td>
                        @if($rak->status === 'Aktif')
                            <span class="badge-aktif">Aktif</span>
                        @else
                            <span class="badge-nonaktif">Nonaktif</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            @if(Auth::user()->hasPermission('rak-buku.edit') || Auth::user()->isAdmin())
                            <button onclick="openEditModal({{ $rak->id }}, '{{ addslashes($rak->nama_rak) }}', '{{ $rak->kode_rak }}', '{{ addslashes($rak->deskripsi ?? '') }}', '{{ addslashes($rak->lokasi ?? '') }}', {{ $rak->kapasitas }}, '{{ $rak->status }}')"
                                    class="btn-edit"><i class="fas fa-edit"></i> Edit</button>
                            @endif
                            @if(Auth::user()->hasPermission('rak-buku.delete') || Auth::user()->isAdmin())
                                @if($rak->jumlah_buku == 0)
                                <button onclick="deleteRak({{ $rak->id }}, '{{ addslashes($rak->nama_rak) }}')"
                                        class="btn-delete"><i class="fas fa-trash"></i> Hapus</button>
                                @else
                                <span class="btn-delete-disabled" title="Rak masih berisi {{ $rak->jumlah_buku }} buku"><i class="fas fa-trash"></i> Hapus</span>
                                @endif
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

<!-- Modal Tambah -->
<div id="tambahModal" class="modal-overlay" onclick="handleOverlayClick(event,'tambahModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-plus-circle text-violet-500 mr-2"></i>Tambah Rak Buku</h3>
                <p class="text-xs text-gray-400 mt-0.5">Kode digenerate otomatis dari nama rak</p>
            </div>
            <button class="modal-close" onclick="closeTambahModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="tambahForm" method="POST" action="{{ route('rak-buku.store') }}">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Rak <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_rak" id="nama_rak" required
                           class="form-control" placeholder="Contoh: Rak Fiksi A"
                           oninput="autoKodeRak(this.value)">
                </div>
                <div class="form-group">
                    <label class="form-label">Kode Rak <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_rak" id="kode_rak" required maxlength="20"
                           class="form-control kode-auto-input" placeholder="Auto dari nama">
                    <p class="kode-hint"><i class="fas fa-magic"></i> Bisa diubah manual</p>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" id="lokasi" maxlength="100"
                           class="form-control" placeholder="Contoh: Lantai 1, Ruang A">
                </div>
                <div class="form-group">
                    <label class="form-label">Kapasitas <span class="text-red-500">*</span></label>
                    <input type="number" name="kapasitas" id="kapasitas" required min="1"
                           class="form-control" placeholder="Maks buku" value="50">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="deskripsi" rows="2" class="form-control form-textarea"
                          placeholder="Deskripsi rak (opsional)"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" id="status" required class="form-control">
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-3 mt-4">
                <button type="button" onclick="closeTambahModal()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-2"></i>Simpan Rak</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div id="editModal" class="modal-overlay" onclick="handleOverlayClick(event,'editModal')">
    <div class="modal-box">
        <div class="modal-header">
            <div>
                <h3 class="modal-title"><i class="fas fa-edit text-purple-500 mr-2"></i>Edit Rak Buku</h3>
            </div>
            <button class="modal-close" onclick="closeEditModal()"><i class="fas fa-times"></i></button>
        </div>
        <form id="editForm" method="POST">
            @csrf @method('PUT')
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Nama Rak <span class="text-red-500">*</span></label>
                    <input type="text" name="nama_rak" id="edit_nama_rak" required class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Kode Rak <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_rak" id="edit_kode_rak" required maxlength="20"
                           class="form-control kode-auto-input">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="lokasi" id="edit_lokasi" maxlength="100" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Kapasitas <span class="text-red-500">*</span></label>
                    <input type="number" name="kapasitas" id="edit_kapasitas" required min="1" class="form-control">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="deskripsi" id="edit_deskripsi" rows="2" class="form-control form-textarea"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Status <span class="text-red-500">*</span></label>
                <select name="status" id="edit_status" required class="form-control">
                    <option value="Aktif">Aktif</option>
                    <option value="Nonaktif">Nonaktif</option>
                </select>
            </div>
            <div class="flex gap-3 mt-4">
                <button type="button" onclick="closeEditModal()" class="btn-secondary">Batal</button>
                <button type="submit" class="btn-primary"><i class="fas fa-save mr-2"></i>Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<form id="deleteForm" method="POST" style="display:none">@csrf @method('DELETE')</form>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script>
$(document).ready(function(){
    $('#rakTable').DataTable({
        language:{url:'//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',emptyTable:'Tidak ada data rak buku',zeroRecords:'Data tidak ditemukan'},
        pageLength:10,lengthMenu:[5,10,25,50],
        columnDefs:[{orderable:false,targets:7}],order:[[0,'asc']],responsive:true
    });

    @if(session('success'))
    Swal.fire({icon:'success',title:'Berhasil!',text:'{{ session('success') }}',timer:2800,timerProgressBar:true,showConfirmButton:false,toast:true,position:'top-end',background:'#f0fdf4',iconColor:'#16a34a'});
    @endif
    @if(session('error'))
    Swal.fire({icon:'error',title:'Gagal!',text:'{{ session('error') }}',confirmButtonColor:'#8b5cf6',confirmButtonText:'OK'});
    @endif
    @if($errors->any())
    Swal.fire({icon:'warning',title:'Periksa Data',html:`<ul style="text-align:left;padding-left:20px;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>`,confirmButtonColor:'#8b5cf6',confirmButtonText:'OK'});
    @endif
});

// Auto-generate kode rak dari nama
function autoKodeRak(nama) {
    if (!nama.trim()) { document.getElementById('kode_rak').value=''; return; }
    const words = nama.trim().split(/\s+/);
    let kode = '';
    if (words.length === 1) {
        kode = 'R' + words[0].substring(0,4).toUpperCase();
    } else {
        kode = 'R' + words.filter(w=>w.length>1).slice(0,4).map(w=>w[0].toUpperCase()).join('');
    }
    document.getElementById('kode_rak').value = kode.substring(0,20);
}

function openTambahModal(){
    document.getElementById('tambahModal').classList.add('active');
    setTimeout(()=>document.getElementById('nama_rak').focus(),200);
}
function closeTambahModal(){
    document.getElementById('tambahModal').classList.remove('active');
    document.getElementById('tambahForm').reset();
    document.getElementById('kode_rak').value='';
}
function openEditModal(id,nama,kode,deskripsi,lokasi,kapasitas,status){
    document.getElementById('editForm').action='{{ url('admin/rak-buku') }}/'+id;
    document.getElementById('edit_nama_rak').value=nama;
    document.getElementById('edit_kode_rak').value=kode;
    document.getElementById('edit_deskripsi').value=deskripsi;
    document.getElementById('edit_lokasi').value=lokasi;
    document.getElementById('edit_kapasitas').value=kapasitas;
    document.getElementById('edit_status').value=status;
    document.getElementById('editModal').classList.add('active');
}
function closeEditModal(){ document.getElementById('editModal').classList.remove('active'); }
function handleOverlayClick(e,id){ if(e.target===document.getElementById(id)){ id==='tambahModal'?closeTambahModal():closeEditModal(); } }

function deleteRak(id, nama) {
    Swal.fire({title:'Hapus Rak Buku?',html:`Rak <strong>${nama}</strong> akan dihapus permanen.`,icon:'warning',showCancelButton:true,confirmButtonColor:'#dc2626',cancelButtonColor:'#6b7280',confirmButtonText:'<i class="fas fa-trash mr-1"></i> Ya, Hapus',cancelButtonText:'Batal',reverseButtons:true})
    .then(r=>{ if(r.isConfirmed){ const f=document.getElementById('deleteForm'); f.action='{{ url('admin/rak-buku') }}/'+id; f.submit(); } });
}
</script>
@endpush
@endsection
