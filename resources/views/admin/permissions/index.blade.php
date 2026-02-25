@extends('layouts.admin')

@section('page-title', 'Manajemen Hak Akses')

@section('content')

<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Manajemen Hak Akses</h1>
                <p class="text-gray-600 mt-1">Kelola hak akses untuk setiap role dengan mudah</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="showBulkAssignModal()"
                        class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-users-cog mr-2"></i>
                    Bulk Assign
                </button>
                <button onclick="showCopyPermissionsModal()"
                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-all duration-200">
                    <i class="fas fa-copy mr-2"></i>
                    Copy Permissions
                </button>
            </div>
        </div>
    </div>

    <!-- Roles Grid -->
    @php $totalPermissions = $groupedPermissions->flatten()->count(); @endphp
    <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
        @foreach($roles as $role)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-6">
                <!-- Role Header -->
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">{{ $role->nama_peran }}</h3>
                        <p class="text-sm text-gray-600">{{ $role->deskripsi ?? 'Tidak ada deskripsi' }}</p>
                    </div>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                        {{ $role->status === 'aktif' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        {{ ucfirst($role->status) }}
                    </span>
                </div>

                <!-- Permission Count -->
                <div class="mb-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-600">Total Hak Akses:</span>
                        <span class="font-medium text-blue-600">{{ $role->permissions->count() }} / {{ $totalPermissions }}</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                        <div class="bg-blue-600 h-2 rounded-full"
                             style="width: {{ $totalPermissions > 0 ? round($role->permissions->count() / $totalPermissions * 100) : 0 }}%"></div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <button onclick="showPermissionModal({{ $role->id }})"
                            class="flex-1 px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-all duration-200">
                        <i class="fas fa-edit mr-1"></i>
                        Edit Hak Akses
                    </button>
                    <button onclick="resetRolePermissions({{ $role->id }})"
                            class="px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-all duration-200"
                            title="Reset Semua Hak Akses">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>

                <!-- Quick Preview -->
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-xs text-gray-500 mb-2">Preview Hak Akses:</p>
                    <div class="flex flex-wrap gap-1">
                        @foreach($role->permissions->take(3) as $permission)
                        <span class="inline-flex items-center px-2 py-1 bg-blue-50 text-blue-700 text-xs rounded">
                            {{ $permission->name }}
                        </span>
                        @endforeach
                        @if($role->permissions->count() > 3)
                        <span class="inline-flex items-center px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded">
                            +{{ $role->permissions->count() - 3 }} lainnya
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- ==================== MODAL: Edit Hak Akses ==================== -->
<div id="permissionModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 id="modalTitle" class="text-lg font-semibold text-gray-900">Edit Hak Akses</h3>
                    <button type="button" onclick="hidePermissionModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="permissionForm" class="overflow-y-auto max-h-[calc(90vh-180px)]">
                @csrf
                <input type="hidden" id="roleId" name="role_id">

                <div class="p-6">
                    <!-- Select All Controls -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <div class="flex items-center justify-between">
                            <h4 class="font-medium text-gray-900">Kontrol Cepat</h4>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllPermissions()"
                                        class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm rounded transition-colors">
                                    <i class="fas fa-check-double mr-1"></i>Pilih Semua
                                </button>
                                <button type="button" onclick="deselectAllPermissions()"
                                        class="px-3 py-1 bg-gray-600 hover:bg-gray-700 text-white text-sm rounded transition-colors">
                                    <i class="fas fa-times mr-1"></i>Batal Pilih
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Permission Groups -->
                    @foreach($groupedPermissions as $groupName => $permissions)
                    <div class="mb-6">
                        <div class="flex items-center mb-3">
                            <input type="checkbox" id="group_{{ $loop->index }}"
                                   class="group-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500"
                                   onchange="toggleGroup('{{ $loop->index }}')">
                            <label for="group_{{ $loop->index }}" class="ml-2 text-sm font-semibold text-gray-900">
                                {{ $groupName }}
                            </label>
                            <span class="ml-2 text-xs text-gray-500">({{ $permissions->count() }} hak akses)</span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 ml-6" data-group="{{ $loop->index }}">
                            @foreach($permissions as $permission)
                            <label class="flex items-start space-x-3 p-3 border border-gray-200 rounded-lg hover:bg-blue-50 cursor-pointer transition-colors">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                       class="permission-checkbox group-{{ $loop->parent->index }} w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 mt-0.5"
                                       onchange="updateGroupCheckbox('{{ $loop->parent->index }}')">
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-medium text-gray-900">{{ $permission->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $permission->description }}</div>
                                    <div class="text-xs text-blue-600 mt-1">{{ $permission->slug }}</div>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>

                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button type="button" onclick="hidePermissionModal()"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200">
                        Batal
                    </button>
                    <button type="submit" id="submitBtn"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-all duration-200">
                        <i class="fas fa-save mr-2"></i>Simpan Hak Akses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: Copy Permissions ==================== -->
<div id="copyModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <div class="p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Copy Hak Akses</h3>
                    <button type="button" onclick="hideCopyModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <form id="copyForm" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Role</label>
                        <select name="from_role_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih role sumber</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->nama_peran }} ({{ $role->permissions->count() }} hak akses)</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Ke Role</label>
                        <select name="to_role_id" required
                                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih role tujuan</option>
                            @foreach($roles as $role)
                            <option value="{{ $role->id }}">{{ $role->nama_peran }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex space-x-3 pt-4">
                        <button type="button" onclick="hideCopyModal()"
                                class="flex-1 px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200">
                            Batal
                        </button>
                        <button type="submit"
                                class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-all duration-200">
                            <i class="fas fa-copy mr-2"></i>Copy
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- ==================== MODAL: Bulk Assign ==================== -->
<div id="bulkModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900">Bulk Assign Hak Akses</h3>
                    <button type="button" onclick="hideBulkModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <form id="bulkForm" class="overflow-y-auto max-h-[calc(90vh-140px)]">
                @csrf
                <div class="p-6 space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Role Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Role</label>
                            <div class="space-y-2 max-h-36 overflow-y-auto border border-gray-200 rounded-lg p-3 bg-gray-50">
                                @foreach($roles as $role)
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" name="role_ids[]" value="{{ $role->id }}"
                                           class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">{{ $role->nama_peran }}</span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Action -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Aksi</label>
                            <select name="action" required
                                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih aksi</option>
                                <option value="add">Tambah hak akses</option>
                                <option value="replace">Ganti semua hak akses</option>
                                <option value="remove">Hapus hak akses</option>
                            </select>
                            <p class="mt-2 text-xs text-gray-500">
                                <strong>Tambah:</strong> menambahkan ke yang sudah ada.<br>
                                <strong>Ganti:</strong> mengganti semua hak akses.<br>
                                <strong>Hapus:</strong> menghapus hak akses yang dipilih.
                            </p>
                        </div>
                    </div>

                    <!-- Permissions Selection -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-sm font-medium text-gray-700">Pilih Hak Akses</label>
                            <div class="flex gap-2">
                                <button type="button" onclick="selectAllBulkPermissions()"
                                        class="text-xs px-2 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded transition-colors">
                                    <i class="fas fa-check-double mr-1"></i>Pilih Semua
                                </button>
                                <button type="button" onclick="deselectAllBulkPermissions()"
                                        class="text-xs px-2 py-1 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors">
                                    <i class="fas fa-times mr-1"></i>Batal Pilih
                                </button>
                            </div>
                        </div>

                        <div class="border border-gray-200 rounded-lg overflow-y-auto max-h-64 bg-gray-50 p-3 space-y-3">
                            @foreach($groupedPermissions as $groupName => $permissions)
                            <div>
                                <div class="flex items-center mb-1">
                                    <input type="checkbox" id="bulk_group_{{ $loop->index }}"
                                           class="bulk-group-checkbox w-4 h-4 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500"
                                           onchange="toggleBulkGroup('{{ $loop->index }}')">
                                    <label for="bulk_group_{{ $loop->index }}" class="ml-2 text-xs font-semibold text-gray-800 cursor-pointer">
                                        {{ $groupName }}
                                        <span class="font-normal text-gray-500">({{ $permissions->count() }})</span>
                                    </label>
                                </div>
                                <div class="grid grid-cols-2 gap-x-4 gap-y-1 ml-6">
                                    @foreach($permissions as $permission)
                                    <label class="flex items-center cursor-pointer">
                                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                               class="bulk-permission-checkbox bulk-group-perm-{{ $loop->parent->index }} w-3.5 h-3.5 text-purple-600 bg-gray-100 border-gray-300 rounded focus:ring-purple-500"
                                               onchange="updateBulkGroupCheckbox('{{ $loop->parent->index }}')">
                                        <span class="ml-1.5 text-xs text-gray-700">{{ $permission->name }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-3 p-6 border-t border-gray-200 bg-gray-50">
                    <button type="button" onclick="hideBulkModal()"
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white font-medium rounded-lg transition-all duration-200">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white font-medium rounded-lg transition-all duration-200">
                        <i class="fas fa-users-cog mr-2"></i>Proses
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-[60] hidden">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
        <span class="text-gray-700">Memproses...</span>
    </div>
</div>

<script>
// ── Alert helpers (SweetAlert2) ──────────────────────────────────────
function showSuccessAlert(message) {
    Swal.fire({
        icon: 'success',
        title: 'Berhasil!',
        text: message,
        confirmButtonColor: '#2563EB'
    });
}

function showErrorAlert(message) {
    Swal.fire({
        icon: 'error',
        title: 'Gagal!',
        text: message,
        confirmButtonColor: '#DC2626'
    });
}

function showConfirmDialog(message, title, callback) {
    Swal.fire({
        title: title || 'Konfirmasi',
        text: message,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#DC2626',
        cancelButtonColor: '#6B7280',
        confirmButtonText: 'Ya, lanjutkan!',
        cancelButtonText: 'Batal'
    }).then(result => {
        if (result.isConfirmed) callback();
    });
}

// ── Loading overlay ──────────────────────────────────────────────────
function showLoading() { document.getElementById('loadingOverlay').classList.remove('hidden'); }
function hideLoading() { document.getElementById('loadingOverlay').classList.add('hidden'); }

// ── Modal visibility ─────────────────────────────────────────────────
function hidePermissionModal() { document.getElementById('permissionModal').classList.add('hidden'); }
function showCopyPermissionsModal() { document.getElementById('copyModal').classList.remove('hidden'); }
function hideCopyModal() { document.getElementById('copyModal').classList.add('hidden'); }
function showBulkAssignModal() { document.getElementById('bulkModal').classList.remove('hidden'); }
function hideBulkModal() { document.getElementById('bulkModal').classList.add('hidden'); }

// ── Edit permission modal ────────────────────────────────────────────
function showPermissionModal(roleId) {
    document.getElementById('roleId').value = roleId;
    showLoading();

    fetch(`/admin/permissions/role/${roleId}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        hideLoading();
        if (!data.success) { showErrorAlert(data.message || 'Gagal memuat data role'); return; }

        document.getElementById('modalTitle').textContent = `Edit Hak Akses — ${data.role.nama_peran}`;

        // Reset checkboxes
        document.querySelectorAll('.permission-checkbox').forEach(cb => cb.checked = false);
        document.querySelectorAll('.group-checkbox').forEach(cb => { cb.checked = false; cb.indeterminate = false; });

        // Mark role's current permissions
        data.permission_ids.forEach(id => {
            const cb = document.querySelector(`#permissionForm input[name="permissions[]"][value="${id}"]`);
            if (cb) cb.checked = true;
        });

        updateAllGroupCheckboxes();
        document.getElementById('permissionModal').classList.remove('hidden');
    })
    .catch(err => {
        hideLoading();
        console.error(err);
        showErrorAlert('Terjadi kesalahan saat memuat data');
    });
}

// ── Edit modal: permission group toggles ─────────────────────────────
function selectAllPermissions() {
    document.querySelectorAll('#permissionForm .permission-checkbox').forEach(cb => cb.checked = true);
    document.querySelectorAll('.group-checkbox').forEach(cb => { cb.checked = true; cb.indeterminate = false; });
}

function deselectAllPermissions() {
    document.querySelectorAll('#permissionForm .permission-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.group-checkbox').forEach(cb => { cb.checked = false; cb.indeterminate = false; });
}

function toggleGroup(groupIndex) {
    const checked = document.getElementById(`group_${groupIndex}`).checked;
    document.querySelectorAll(`.group-${groupIndex}`).forEach(cb => cb.checked = checked);
}

function updateGroupCheckbox(groupIndex) {
    const all     = document.querySelectorAll(`.group-${groupIndex}`);
    const checked = document.querySelectorAll(`.group-${groupIndex}:checked`);
    const cb      = document.getElementById(`group_${groupIndex}`);

    cb.indeterminate = checked.length > 0 && checked.length < all.length;
    cb.checked       = checked.length === all.length;
}

function updateAllGroupCheckboxes() {
    document.querySelectorAll('.group-checkbox').forEach((_, i) => updateGroupCheckbox(i));
}

// ── Bulk assign: permission group toggles ────────────────────────────
function selectAllBulkPermissions() {
    document.querySelectorAll('.bulk-permission-checkbox').forEach(cb => cb.checked = true);
    document.querySelectorAll('.bulk-group-checkbox').forEach(cb => { cb.checked = true; cb.indeterminate = false; });
}

function deselectAllBulkPermissions() {
    document.querySelectorAll('.bulk-permission-checkbox').forEach(cb => cb.checked = false);
    document.querySelectorAll('.bulk-group-checkbox').forEach(cb => { cb.checked = false; cb.indeterminate = false; });
}

function toggleBulkGroup(groupIndex) {
    const checked = document.getElementById(`bulk_group_${groupIndex}`).checked;
    document.querySelectorAll(`.bulk-group-perm-${groupIndex}`).forEach(cb => cb.checked = checked);
}

function updateBulkGroupCheckbox(groupIndex) {
    const all     = document.querySelectorAll(`.bulk-group-perm-${groupIndex}`);
    const checked = document.querySelectorAll(`.bulk-group-perm-${groupIndex}:checked`);
    const cb      = document.getElementById(`bulk_group_${groupIndex}`);

    cb.indeterminate = checked.length > 0 && checked.length < all.length;
    cb.checked       = checked.length === all.length;
}

// ── Form: Edit permissions ───────────────────────────────────────────
document.getElementById('permissionForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const roleId    = document.getElementById('roleId').value;
    const submitBtn = document.getElementById('submitBtn');
    const orig      = submitBtn.innerHTML;

    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled  = true;

    fetch(`/admin/permissions/role/${roleId}/update`, {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showSuccessAlert(data.message);
            hidePermissionModal();
            location.reload();
        } else {
            showErrorAlert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(err => { console.error(err); showErrorAlert('Terjadi kesalahan saat menyimpan'); })
    .finally(() => { submitBtn.innerHTML = orig; submitBtn.disabled = false; });
});

// ── Form: Copy permissions ───────────────────────────────────────────
document.getElementById('copyForm').addEventListener('submit', function(e) {
    e.preventDefault();

    fetch('/admin/permissions/copy', {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showSuccessAlert(data.message);
            hideCopyModal();
            location.reload();
        } else {
            showErrorAlert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(err => { console.error(err); showErrorAlert('Terjadi kesalahan saat copy permissions'); });
});

// ── Form: Bulk assign ────────────────────────────────────────────────
document.getElementById('bulkForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const roles = this.querySelectorAll('input[name="role_ids[]"]:checked');
    if (roles.length === 0) { showErrorAlert('Pilih minimal satu role.'); return; }

    const action = this.querySelector('select[name="action"]').value;
    if (!action) { showErrorAlert('Pilih aksi terlebih dahulu.'); return; }

    if (action !== 'replace') {
        const perms = this.querySelectorAll('input[name="permissions[]"]:checked');
        if (perms.length === 0) { showErrorAlert('Pilih minimal satu hak akses.'); return; }
    }

    fetch('/admin/permissions/bulk-assign', {
        method: 'POST',
        body: new FormData(this),
        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            showSuccessAlert(data.message);
            hideBulkModal();
            location.reload();
        } else {
            showErrorAlert(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(err => { console.error(err); showErrorAlert('Terjadi kesalahan saat bulk assign'); });
});

// ── Reset role permissions ───────────────────────────────────────────
function resetRolePermissions(roleId) {
    showConfirmDialog(
        'Semua hak akses untuk role ini akan dihapus. Lanjutkan?',
        'Reset Hak Akses',
        () => {
            showLoading();
            fetch(`/admin/permissions/role/${roleId}/reset`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                hideLoading();
                if (data.success) { showSuccessAlert(data.message); location.reload(); }
                else showErrorAlert(data.message || 'Terjadi kesalahan');
            })
            .catch(err => { hideLoading(); console.error(err); showErrorAlert('Terjadi kesalahan saat reset permissions'); });
        }
    );
}
</script>
@endsection
