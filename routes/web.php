<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PetugasController;
use App\Http\Controllers\KepsekController;
use App\Http\Controllers\BukuTamuController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\BukuController;
use App\Http\Controllers\JurusanController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\KategoriBukuController;
use App\Http\Controllers\JenisBukuController;
use App\Http\Controllers\SumberBukuController;
use App\Http\Controllers\PeminjamanController;
use App\Http\Controllers\DendaController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\CetakController;
use App\Http\Controllers\PengembalianController;
use App\Http\Controllers\RiwayatPeminjamanController;
use App\Http\Controllers\RiwayatPengembalianController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PermissionController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to appropriate dashboard based on user role
Route::get('/', function () {
    if (!Auth::check()) {
        return redirect('/login');
    }
    
    $user = Auth::user();
    $kodePeran = $user->role ? $user->role->kode_peran : null;
    
    switch ($kodePeran) {
        case 'ADMIN':
            // Langsung tampilkan dashboard admin di root URL
            return app(\App\Http\Controllers\AdminController::class)->dashboard();
        case 'KEPALA_SEKOLAH':
            // Langsung tampilkan dashboard kepala sekolah di root URL
            return app(\App\Http\Controllers\KepsekController::class)->dashboard();
        case 'PETUGAS':
            return redirect('/petugas/dashboard');
        default:
            return redirect('/login');
    }
});

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard redirect untuk user yang sudah login
Route::get('/dashboard', function () {
    if (!Auth::check()) {
        return redirect('/login');
    }
    
    $user = Auth::user();
    $kodePeran = $user->role ? $user->role->kode_peran : null;
    
    switch ($kodePeran) {
        case 'ADMIN':
            return redirect('/'); // Redirect ke root untuk admin
        case 'KEPALA_SEKOLAH':
            return redirect('/'); // Redirect ke root untuk kepala sekolah
        case 'PETUGAS':
            return redirect('/petugas/dashboard');
        default:
            return redirect('/login');
    }
})->name('dashboard');

// Route alternatif untuk akses langsung admin dan kepala sekolah
Route::get('/admin-panel', [AdminController::class, 'dashboard'])
    ->middleware(['auth', 'role:ADMIN'])
    ->name('admin.dashboard.direct');
    
Route::get('/kepsek-panel', [KepsekController::class, 'dashboard'])
    ->middleware(['auth', 'role:KEPALA_SEKOLAH'])
    ->name('kepsek.dashboard.direct');




// Admin Routes (Admin dan Kepala Sekolah bisa akses)
Route::middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/pengaturan-website', [AdminController::class, 'pengaturanWebsite'])->name('admin.pengaturan');
    Route::post('/pengaturan-website', [AdminController::class, 'updatePengaturanWebsite'])->name('admin.pengaturan.update');
    
    // Profil untuk semua role (Admin, Kepala Sekolah, Petugas)
    Route::get('/profil', [AdminController::class, 'profil'])->name('admin.profil');
    Route::post('/profil/update', [AdminController::class, 'updateProfil'])->name('admin.profil.update');
    Route::post('/profil/ganti-password', [AdminController::class, 'gantiPassword'])->name('admin.profil.ganti-password');
    Route::delete('/profil/hapus-foto', [AdminController::class, 'hapusFoto'])->name('admin.profil.hapus-foto');
    
    // API Routes untuk AJAX pencarian
    Route::get('/peminjaman/search-anggota', [PeminjamanController::class, 'searchAnggota'])->name('peminjaman.search-anggota');
    Route::get('/peminjaman/search-buku', [PeminjamanController::class, 'searchBuku'])->name('peminjaman.search-buku');
    

    
    // CRUD Anggota
    Route::get('/anggota/export', [AnggotaController::class, 'export'])->name('anggota.export');
    Route::get('/anggota/download-template', [AnggotaController::class, 'downloadTemplate'])->name('anggota.download-template');
    Route::post('/anggota/import', [AnggotaController::class, 'import'])->name('anggota.import');
    Route::post('/anggota/bulk-delete', [AnggotaController::class, 'bulkDelete'])->name('anggota.bulk-delete');
    Route::get('/anggota/cetak-kartu/{id}', [AnggotaController::class, 'cetakKartu'])->name('anggota.cetak-kartu');
    Route::get('/anggota/bulk-print-kartu', [AnggotaController::class, 'bulkPrintKartu'])->name('anggota.bulk-print-kartu');
    Route::post('/anggota/scan-barcode', [AnggotaController::class, 'scanBarcode'])->name('anggota.scan-barcode');
    Route::post('/anggota/generate-barcode', [AnggotaController::class, 'generateBarcode'])->name('anggota.generate-barcode');
    Route::post('/anggota/clean-duplicates', [AnggotaController::class, 'cleanDuplicateData'])->name('anggota.clean-duplicates');
    Route::post('/anggota/regenerate-duplicates', [AnggotaController::class, 'regenerateDuplicateCodes'])->name('anggota.regenerate-duplicates');
    Route::post('/anggota/clean-and-regenerate', [AnggotaController::class, 'cleanAndRegenerateDuplicates'])->name('anggota.clean-and-regenerate');
    Route::get('/anggota/check-duplicates', [AnggotaController::class, 'checkDuplicateData'])->name('anggota.check-duplicates');
    Route::get('/anggota/import-stats', [AnggotaController::class, 'getImportStats'])->name('anggota.import-stats');
    Route::resource('anggota', AnggotaController::class);
    
    // CRUD Buku - Routes khusus harus ditempatkan sebelum resource route
    Route::get('/buku/export', [BukuController::class, 'export'])->name('buku.export');
    Route::get('/buku/download-template', [BukuController::class, 'downloadTemplate'])->name('buku.download-template');
    Route::post('/buku/import', [BukuController::class, 'import'])->name('buku.import');
    Route::post('/buku/generate-barcode', [BukuController::class, 'generateBarcode'])->name('buku.generate-barcode');
    Route::post('/buku/generate-multiple-barcode', [BukuController::class, 'generateMultipleBarcode'])->name('buku.generate-multiple-barcode');
    Route::get('/buku/{id}/print-barcode', [BukuController::class, 'printBarcode'])->name('buku.print-barcode');
    Route::get('/buku/{id}/cetak-barcode', [BukuController::class, 'cetakBarcode'])->name('buku.cetak-barcode');
    Route::post('/buku/print-multiple-barcode', [BukuController::class, 'printMultipleBarcode'])->name('buku.print-multiple-barcode');
    Route::post('/buku/scan-barcode', [BukuController::class, 'scanBarcode'])->name('buku.scan-barcode');
    Route::delete('/buku/destroy-multiple', [BukuController::class, 'destroyMultiple'])->name('buku.destroy-multiple');
    
    // Resource route harus di bawah routes khusus
    Route::resource('buku', BukuController::class);
    
    // CRUD Jurusan
    Route::resource('jurusan', JurusanController::class);
    
    // CRUD Kelas
    Route::resource('kelas', KelasController::class);
    Route::post('/kelas/generate-kode', [KelasController::class, 'generateKodeKelas'])->name('kelas.generate-kode');
    
    // CRUD Kategori Buku
    Route::resource('kategori-buku', KategoriBukuController::class);
    Route::post('/kategori-buku/generate-kode', [KategoriBukuController::class, 'generateKode'])->name('kategori-buku.generate-kode');
    Route::delete('/kategori-buku/destroy-multiple', [KategoriBukuController::class, 'destroyMultiple'])->name('kategori-buku.destroy-multiple');
    
    // CRUD Jenis Buku
    Route::resource('jenis-buku', JenisBukuController::class);
    Route::get('/jenis-buku/export', [JenisBukuController::class, 'export'])->name('jenis-buku.export');
    Route::post('/jenis-buku/bulk-delete', [JenisBukuController::class, 'bulkDelete'])->name('jenis-buku.bulk-delete');
    
    // CRUD Sumber Buku
    Route::post('/sumber-buku/generate-kode', [SumberBukuController::class, 'generateKode'])->name('sumber-buku.generate-kode');
    Route::delete('/sumber-buku/destroy-multiple', [SumberBukuController::class, 'destroyMultiple'])->name('sumber-buku.destroy-multiple');
    Route::resource('sumber-buku', SumberBukuController::class);
    
    // CRUD Rak Buku
    Route::resource('rak-buku', \App\Http\Controllers\Admin\RakBukuController::class);
    Route::get('/rak-buku/get-rak', [\App\Http\Controllers\Admin\RakBukuController::class, 'getRakBuku'])->name('rak-buku.get-rak');
    
    // CRUD Role
    Route::resource('role', RoleController::class);
    
    // Permission Management
    Route::get('/permissions', [PermissionController::class, 'index'])->name('permissions.index');
    Route::get('/permissions/role/{roleId}', [PermissionController::class, 'getRolePermissions'])->name('permissions.role.get');
    Route::post('/permissions/role/{roleId}/update', [PermissionController::class, 'updateRolePermissions'])->name('permissions.role.update');
    Route::post('/permissions/role/{roleId}/reset', [PermissionController::class, 'resetRolePermissions'])->name('permissions.role.reset');
    Route::post('/permissions/copy', [PermissionController::class, 'copyPermissions'])->name('permissions.copy');
    Route::post('/permissions/bulk-assign', [PermissionController::class, 'bulkAssignPermissions'])->name('permissions.bulk-assign');
    Route::post('/role/generate-kode', [RoleController::class, 'generateKode'])->name('role.generate-kode');
    
    // CRUD User
    Route::resource('user', UserController::class);
    Route::post('/user/{user}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
    
    // CRUD Peminjaman
    Route::resource('peminjaman', PeminjamanController::class);
    Route::post('/peminjaman/scan-anggota', [PeminjamanController::class, 'scanAnggota'])->name('peminjaman.scan-anggota');
    Route::post('/peminjaman/scan-buku', [PeminjamanController::class, 'scanBuku'])->name('peminjaman.scan-buku');
    Route::post('/peminjaman/scan-multiple-buku', [PeminjamanController::class, 'scanMultipleBuku'])->name('peminjaman.scan-multiple-buku');
    
    // Route pencarian anggota untuk pengembalian - dengan detail peminjaman
    Route::get('/pengembalian/search-anggota', [PengembalianController::class, 'searchAnggota'])
        ->name('pengembalian.search-anggota');
    
    Route::get('/pengembalian/get-peminjaman-aktif', [PengembalianController::class, 'getPeminjamanAktif'])->name('pengembalian.get-peminjaman-aktif');
    Route::post('/pengembalian/scan-barcode', [PengembalianController::class, 'scanBarcode'])->name('pengembalian.scan-barcode');
    Route::post('/pengembalian/scan-barcode-anggota', [PengembalianController::class, 'scanBarcodeAnggota'])->name('pengembalian.scan-barcode-anggota');
    Route::post('/pengembalian/{id}/update-status-pembayaran-denda', [PengembalianController::class, 'updateStatusPembayaranDenda'])->name('pengembalian.update-status-pembayaran-denda');
    Route::get('/pengembalian/{id}/denda-info', [PengembalianController::class, 'getDendaInfo'])->name('pengembalian.denda-info');
    
    // Add a specific route for active borrowings
    Route::get('/pengembalian/aktif', function () {
        request()->merge(['view' => 'active']);
        return app(App\Http\Controllers\PengembalianController::class)->index(request());
    })->name('pengembalian.aktif');
    
    // CRUD Pengembalian - harus diletakkan setelah route custom
    Route::resource('pengembalian', PengembalianController::class);

    // Riwayat Pengembalian
    Route::get('/riwayat-pengembalian', [RiwayatPengembalianController::class, 'index'])->name('riwayat-pengembalian.index');
    Route::get('/riwayat-pengembalian/export', [RiwayatPengembalianController::class, 'export'])->name('riwayat-pengembalian.export');
    
    // CRUD Denda
    Route::resource('denda', DendaController::class)->names([
        'index' => 'admin.denda.index',
        'create' => 'admin.denda.create',
        'store' => 'admin.denda.store',
        'show' => 'admin.denda.show',
        'edit' => 'admin.denda.edit',
        'update' => 'admin.denda.update',
        'destroy' => 'admin.denda.destroy',
    ]);
    Route::post('/denda/hitung-denda', [DendaController::class, 'hitungDenda'])->name('admin.denda.hitung');
    Route::post('/denda/{id}/update-status', [DendaController::class, 'updateStatusPembayaran'])->name('admin.denda.update-status');
    Route::post('/denda/search', [DendaController::class, 'searchDenda'])->name('admin.denda.search');
    
    // CRUD Buku Tamu
    // Buku Tamu
    Route::resource('buku-tamu', BukuTamuController::class)->names([
        'index' => 'admin.buku-tamu.index',
        'create' => 'admin.buku-tamu.create',
        'store' => 'admin.buku-tamu.store',
        'show' => 'admin.buku-tamu.show',
        'edit' => 'admin.buku-tamu.edit',
        'update' => 'admin.buku-tamu.update',
        'destroy' => 'admin.buku-tamu.destroy',
    ]);
    Route::post('/buku-tamu/scan-barcode', [BukuTamuController::class, 'scanBarcode'])->name('admin.buku-tamu.scan-barcode');
    Route::get('/buku-tamu/search-members', [BukuTamuController::class, 'searchMembers'])->name('admin.buku-tamu.search-members');
    Route::post('/buku-tamu/store-ajax', [BukuTamuController::class, 'storeAjax'])->name('admin.buku-tamu.store-ajax');
    Route::post('/buku-tamu/record-exit', [BukuTamuController::class, 'recordExit'])->name('admin.buku-tamu.record-exit');
    Route::get('/buku-tamu/history', [BukuTamuController::class, 'history'])->name('admin.buku-tamu.history');
    Route::get('/buku-tamu/history/search', [BukuTamuController::class, 'searchHistory'])->name('admin.buku-tamu.history.search');
    Route::get('/buku-tamu/export-excel', [BukuTamuController::class, 'exportExcel'])->name('admin.buku-tamu.export-excel');
    Route::get('/buku-tamu/export-pdf', [BukuTamuController::class, 'exportPdf'])->name('admin.buku-tamu.export-pdf');
    Route::get('/buku-tamu/today', [BukuTamuController::class, 'todayVisitors'])->name('admin.buku-tamu.today');
    
    // Riwayat Peminjaman
    Route::get('/riwayat-peminjaman', [RiwayatPeminjamanController::class, 'index'])->name('riwayat-peminjaman.index');
    Route::get('/riwayat-peminjaman/export', [RiwayatPeminjamanController::class, 'export'])->name('riwayat-peminjaman.export');
    

    
    // Laporan
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('/laporan/anggota', [LaporanController::class, 'anggota'])->name('admin.laporan.anggota');
    Route::get('/laporan/buku', [LaporanController::class, 'buku'])->name('admin.laporan.buku');
    Route::get('/laporan/peminjaman', [LaporanController::class, 'peminjaman'])->name('admin.laporan.peminjaman');
    Route::get('/laporan/pengembalian', [LaporanController::class, 'pengembalian'])->name('admin.laporan.pengembalian');
    Route::get('/laporan/denda', [LaporanController::class, 'denda'])->name('admin.laporan.denda');
    Route::get('/laporan/absensi', [LaporanController::class, 'absensi'])->name('admin.laporan.absensi');
    Route::get('/laporan/kas', [LaporanController::class, 'kas'])->name('admin.laporan.kas');
    
    // Cetak
    Route::get('/cetak/label-buku/{id}', [CetakController::class, 'labelBuku'])->name('admin.cetak.label');
    Route::get('/cetak/kartu-anggota/{id}', [CetakController::class, 'kartuAnggota'])->name('admin.cetak.kartu');
});

// Petugas Routes
Route::middleware(['auth', 'role:PETUGAS'])->prefix('petugas')->group(function () {
    Route::get('/dashboard', [PetugasController::class, 'dashboard'])->name('petugas.dashboard');
    Route::get('/beranda', [PetugasController::class, 'beranda'])->name('petugas.beranda');
    Route::get('/tentang', [PetugasController::class, 'tentang'])->name('petugas.tentang');
    
    // Profil Petugas (menggunakan AdminController yang sudah ada)
    Route::get('/profil', [AdminController::class, 'profil'])->name('petugas.profil');
    Route::post('/profil/update', [AdminController::class, 'updateProfil'])->name('petugas.profil.update');
    Route::post('/profil/ganti-password', [AdminController::class, 'gantiPassword'])->name('petugas.profil.ganti-password');
    Route::delete('/profil/hapus-foto', [AdminController::class, 'hapusFoto'])->name('petugas.profil.hapus-foto');
    
    Route::resource('buku-tamu', BukuTamuController::class)->names([
        'index' => 'petugas.buku-tamu.index',
        'create' => 'petugas.buku-tamu.create',
        'store' => 'petugas.buku-tamu.store',
        'show' => 'petugas.buku-tamu.show',
        'edit' => 'petugas.buku-tamu.edit',
        'update' => 'petugas.buku-tamu.update',
        'destroy' => 'petugas.buku-tamu.destroy',
    ]);
    Route::post('/buku-tamu/scan-barcode', [BukuTamuController::class, 'scanBarcode'])->name('petugas.buku-tamu.scan-barcode');
    Route::get('/buku-tamu/search-members', [BukuTamuController::class, 'searchMembers'])->name('petugas.buku-tamu.search-members');
});

// Kepala Sekolah Routes
Route::middleware(['auth', 'role:KEPALA_SEKOLAH'])->prefix('kepsek')->group(function () {
    Route::get('/dashboard', [KepsekController::class, 'dashboard'])->name('kepsek.dashboard');
    // Laporan route removed - kepala sekolah now uses admin laporan
    
    // Profil Kepala Sekolah (menggunakan AdminController yang sudah ada)
    Route::get('/profil', [AdminController::class, 'profil'])->name('kepsek.profil');
    Route::post('/profil/update', [AdminController::class, 'updateProfil'])->name('kepsek.profil.update');
    Route::post('/profil/ganti-password', [AdminController::class, 'gantiPassword'])->name('kepsek.profil.ganti-password');
    Route::delete('/profil/hapus-foto', [AdminController::class, 'hapusFoto'])->name('kepsek.profil.hapus-foto');
    
    // Gunakan controller admin yang sudah ada
    Route::get('/riwayat-peminjaman', [RiwayatPeminjamanController::class, 'index'])->name('kepsek.riwayat-peminjaman');
    Route::get('/riwayat-pengembalian', [RiwayatPengembalianController::class, 'index'])->name('kepsek.riwayat-pengembalian');
    Route::get('/data-buku', [BukuController::class, 'index'])->name('kepsek.data-buku');
    Route::get('/data-anggota', [AnggotaController::class, 'index'])->name('kepsek.data-anggota');
    
    // Peminjaman aktif untuk kepala sekolah (moved to admin group)
    // Route::get('/peminjaman-aktif', [PeminjamanController::class, 'peminjamanAktif'])->name('kepsek.peminjaman-aktif');
    // Route::get('/peminjaman-aktif/export', [PeminjamanController::class, 'exportPeminjamanAktif'])->name('kepsek.peminjaman-aktif.export');
    
    // Export routes (gunakan yang sudah ada)
    Route::get('/export/riwayat-peminjaman', [RiwayatPeminjamanController::class, 'export'])->name('kepsek.export.riwayat-peminjaman');
    Route::get('/export/riwayat-pengembalian', [RiwayatPengembalianController::class, 'export'])->name('kepsek.export.riwayat-pengembalian');
    Route::get('/export/data-buku', [BukuController::class, 'export'])->name('kepsek.export.data-buku');
    Route::get('/export/data-anggota', [AnggotaController::class, 'export'])->name('kepsek.export.data-anggota');
});

