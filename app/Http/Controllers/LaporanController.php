<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kelas;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\BukuTamu;
use App\Models\KategoriBuku;
use App\Models\JenisBuku;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnggotaExport;
use App\Exports\BukuExport;
use App\Exports\PeminjamanExport;
use App\Exports\PengembalianExport;
use App\Exports\DendaExport;
use App\Exports\KasExport;
use App\Exports\BukuTamuExport;
use App\Models\PengaturanWebsite;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
    }

    private function getPengaturan()
    {
        return PengaturanWebsite::first();
    }

    private function generatePdf($view, $data, $filename, $orientation = 'landscape')
    {
        $data['pengaturan'] = $this->getPengaturan();
        $pdf = Pdf::loadView($view, $data)
                  ->setPaper('a4', $orientation);
        return $pdf->download($filename);
    }

    public function index()
    {
        return view('admin.laporan.index');
    }

    public function anggota(Request $request)
    {
        $query = Anggota::with(['kelas.jurusan']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('created_at', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        if ($request->filled('jenis_anggota')) {
            $query->where('jenis_anggota', $request->jenis_anggota);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('kelas_id')) {
            $query->whereHas('kelas', function($q) use ($request) {
                $q->where('id', $request->kelas_id);
            });
        }

        // DataTables server-side
        if ($request->ajax() && $request->filled('draw')) {
            $columns = $request->columns ?? [];
            $orderColName = $columns[$request->order[0]['column']]['name'] ?? 'created_at';
            $orderDir = $request->order[0]['dir'] ?? 'desc';

            $orderMap = [
                'nama_lengkap' => 'nama_lengkap',
                'nomor_anggota' => 'nomor_anggota',
                'jenis_kelamin' => 'jenis_kelamin',
                'jenis_anggota' => 'jenis_anggota',
                'status' => 'status',
                'created_at' => 'created_at',
            ];
            $orderColumn = $orderMap[$orderColName] ?? 'created_at';

            $recordsTotal = $query->count();

            if ($search = $request->search['value']) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nomor_anggota', 'like', "%{$search}%")
                      ->orWhere('jenis_kelamin', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy($orderColumn, $orderDir)
                ->skip($request->start)
                ->take($request->length)
                ->get()
                ->map(function($item, $index) use ($request) {
                    $rowIndex = $request->start + $index + 1;
                    $jk = $item->jenis_kelamin == 'Laki-laki' ? 'L' : 'P';
                    $jkClass = $item->jenis_kelamin == 'Laki-laki' ? 'bg-sky-100 text-sky-700' : 'bg-pink-100 text-pink-700';
                    return [
                        'DT_RowIndex' => $rowIndex,
                        'nama_lengkap' => '<span class="font-medium text-gray-900">' . e($item->nama_lengkap) . '</span>',
                        'nomor_anggota' => '<span class="font-mono text-gray-600 text-xs">' . e($item->nomor_anggota) . '</span>',
                        'jenis_kelamin_label' => '<span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold ' . $jkClass . '">' . $jk . '</span>',
                        'kelas_jurusan' => $item->kelas
                            ? '<span class="font-medium text-xs text-gray-600">' . e($item->kelas->nama_kelas) . '</span>' . ($item->kelas->jurusan ? '<br><span class="text-gray-400 text-xs">' . e($item->kelas->jurusan->nama_jurusan) . '</span>' : '')
                            : '<span class="text-gray-400 text-xs">—</span>',
                        'jenis_anggota_label' => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold border bg-blue-50 text-blue-700 border-blue-200">' . ucfirst(e($item->jenis_anggota)) . '</span>',
                        'status_label' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border ' .
                            ($item->status == 'aktif' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($item->status == 'nonaktif' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200')) .
                            '"><span class="w-1.5 h-1.5 rounded-full ' .
                            ($item->status == 'aktif' ? 'bg-emerald-500' : ($item->status == 'nonaktif' ? 'bg-red-500' : 'bg-amber-500')) .
                            '"></span> ' . ucfirst(e($item->status)) . '</span>',
                        'tanggal_daftar' => $item->created_at
                            ? '<span class="text-gray-500 text-xs whitespace-nowrap">' . $item->created_at->format('d/m/Y') . '</span>'
                            : '<span class="text-xs text-gray-400">—</span>',
                    ];
                });

            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        if ($request->filled('export') && $request->export === 'excel') {
            $anggota = $query->orderBy('created_at', 'desc')->get();
            return Excel::download(new AnggotaExport($anggota), 'laporan-anggota-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            $anggota = $query->orderBy('created_at', 'desc')->get();
            return $this->generatePdf('admin.laporan.pdf.anggota', compact('anggota'), 'laporan-anggota-' . date('Y-m-d') . '.pdf');
        }

        $totalAnggota = $query->count();
        $siswa = (clone $query)->where('jenis_anggota', 'siswa')->count();
        $guru = (clone $query)->where('jenis_anggota', 'guru')->count();
        $staff = (clone $query)->where('jenis_anggota', 'staff')->count();
        $aktif = (clone $query)->where('status', 'aktif')->count();
        $nonaktif = (clone $query)->where('status', 'nonaktif')->count();
        $kelas = Kelas::with('jurusan')->get();
        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.anggota', compact('totalAnggota', 'siswa', 'guru', 'staff', 'aktif', 'nonaktif', 'kelas', 'pengaturan'));
    }

    public function buku(Request $request)
    {
        $query = Buku::with(['kategoriBuku', 'jenisBuku', 'rakBuku']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('created_at', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('jenis_buku_id')) {
            $query->where('jenis_buku_id', $request->jenis_buku_id);
        }

        if ($request->filled('status')) {
            if ($request->status == 'tersedia') {
                $query->where('stok_tersedia', '>', 0);
            } elseif ($request->status == 'dipinjam') {
                $query->where('stok_tersedia', 0);
            }
        }

        // DataTables server-side
        if ($request->ajax() && $request->filled('draw')) {
            $columns = $request->columns ?? [];
            $orderColName = $columns[$request->order[0]['column']]['name'] ?? 'created_at';
            $orderDir = $request->order[0]['dir'] ?? 'desc';

            $orderMap = [
                'judul_buku' => 'judul_buku',
                'isbn' => 'isbn',
                'pengarang' => 'pengarang',
                'kategori_id' => 'kategori_id',
                'jenis_buku_id' => 'jenis_buku_id',
                'jumlah_stok' => 'jumlah_stok',
                'stok_tersedia' => 'stok_tersedia',
                'created_at' => 'created_at',
            ];
            $orderColumn = $orderMap[$orderColName] ?? 'created_at';

            $recordsTotal = $query->count();

            if ($search = $request->search['value']) {
                $query->where(function($q) use ($search) {
                    $q->where('judul_buku', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%")
                      ->orWhere('pengarang', 'like', "%{$search}%")
                      ->orWhere('penerbit', 'like', "%{$search}%");
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy($orderColumn, $orderDir)
                ->skip($request->start)
                ->take($request->length)
                ->get()
                ->map(function($item, $index) use ($request) {
                    $rowIndex = $request->start + $index + 1;
                    $stokTotal = $item->jumlah_stok ?? 0;
                    $stokTersedia = $item->stok_tersedia ?? 0;
                    $stokDipinjam = max(0, $stokTotal - $stokTersedia);

                    $statusBadge = '';
                    if ($stokTersedia > 0) {
                        $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia</span>';
                    } elseif ($stokTotal > 0) {
                        $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200"><span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Dipinjam</span>';
                    } else {
                        $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Habis</span>';
                    }

                    $subInfo = e($item->penerbit ?? '');
                    if ($item->tahun_terbit) {
                        $subInfo .= $subInfo ? ' (' . e($item->tahun_terbit) . ')' : e($item->tahun_terbit);
                    }

                    return [
                        'DT_RowIndex' => $rowIndex,
                        'judul_buku' => '<div class="flex items-center gap-2"><div class="w-7 h-7 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 bg-blue-100 text-blue-700"><i class="fas fa-book text-[10px]"></i></div><div><div class="font-medium text-gray-900 leading-tight">' . e($item->judul_buku) . '</div>' . ($subInfo ? '<div class="text-xs text-gray-400">' . $subInfo . '</div>' : '') . '</div></div>',
                        'isbn' => '<span class="font-mono text-gray-600 text-xs">' . e($item->isbn ?? '—') . '</span>',
                        'pengarang' => '<span class="text-gray-600 text-xs">' . e($item->pengarang ?? '—') . '</span>',
                        'kategori_label' => '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">' . e($item->kategoriBuku->nama_kategori ?? '—') . '</span>',
                        'jenis_label' => '<span class="text-gray-600 text-xs">' . e($item->jenisBuku->nama_jenis ?? '—') . '</span>',
                        'jumlah_stok' => '<span class="font-bold text-gray-800">' . $stokTotal . '</span>' . ($stokDipinjam > 0 ? '<div class="text-xs text-amber-500">' . $stokDipinjam . ' dipinjam</div>' : ''),
                        'stok_tersedia' => '<span class="font-bold ' . ($stokTersedia > 0 ? 'text-emerald-600' : 'text-red-500') . '">' . $stokTersedia . '</span>',
                        'status_label' => $statusBadge,
                    ];
                });

            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        if ($request->filled('export') && $request->export === 'excel') {
            $buku = $query->orderBy('created_at', 'desc')->get();
            return Excel::download(new BukuExport($buku), 'laporan-buku-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            $buku = $query->orderBy('created_at', 'desc')->get();
            return $this->generatePdf('admin.laporan.pdf.buku', compact('buku'), 'laporan-buku-' . date('Y-m-d') . '.pdf');
        }

        $totalBuku = $query->count();
        $totalEksemplar = $query->sum('jumlah_stok');
        $tersedia = (clone $query)->where('stok_tersedia', '>', 0)->count();
        $habis = (clone $query)->where('stok_tersedia', 0)->count();
        $kategori = KategoriBuku::all();
        $jenis = JenisBuku::all();
        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.buku', compact('totalBuku', 'totalEksemplar', 'tersedia', 'habis', 'kategori', 'jenis', 'pengaturan'));
    }

    public function kas(Request $request)
    {
        $query = Denda::with(['peminjaman.anggota.kelas.jurusan', 'peminjaman.detailPeminjaman.buku.kategoriBuku'])
                     ->where('status_pembayaran', 'sudah_dibayar');

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_pembayaran', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        $kas = $query->orderBy('tanggal_pembayaran', 'desc')->get();

        if ($request->filled('export') && $request->export === 'excel') {
            return Excel::download(new KasExport($kas), 'laporan-kas-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->generatePdf('admin.laporan.pdf.kas', compact('kas'), 'laporan-kas-' . date('Y-m-d') . '.pdf');
        }

        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.kas', compact('kas', 'pengaturan'));
    }

    public function peminjaman(Request $request)
    {
        $query = Peminjaman::with(['anggota.kelas.jurusan', 'detailPeminjaman.buku.kategoriBuku', 'user']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_peminjaman', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // DataTables server-side
        if ($request->ajax() && $request->filled('draw')) {
            $columns = $request->columns ?? [];
            $orderColName = $columns[$request->order[0]['column']]['name'] ?? 'tanggal_peminjaman';
            $orderDir = $request->order[0]['dir'] ?? 'desc';

            $orderMap = [
                'nomor_peminjaman' => 'nomor_peminjaman',
                'anggota_id' => 'anggota_id',
                'tanggal_peminjaman' => 'tanggal_peminjaman',
                'tanggal_kembali' => 'tanggal_kembali',
                'jumlah_buku' => 'jumlah_buku',
                'status' => 'status',
                'user_id' => 'user_id',
            ];
            $orderColumn = $orderMap[$orderColName] ?? 'tanggal_peminjaman';

            $recordsTotal = $query->count();

            if ($search = $request->search['value']) {
                $query->where(function($q) use ($search) {
                    $q->where('nomor_peminjaman', 'like', "%{$search}%")
                      ->orWhereHas('anggota', function($q) use ($search) {
                          $q->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nomor_anggota', 'like', "%{$search}%");
                      })
                      ->orWhereHas('user', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy($orderColumn, $orderDir)
                ->skip($request->start)
                ->take($request->length)
                ->get()
                ->map(function($item, $index) use ($request) {
                    $rowIndex = $request->start + $index + 1;

                    $statusColor = [
                        'dipinjam' => 'bg-amber-50 text-amber-700 border-amber-200',
                        'dikembalikan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'terlambat' => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $sc = $statusColor[$item->status] ?? 'bg-gray-50 text-gray-700 border-gray-200';

                    return [
                        'DT_RowIndex' => $rowIndex,
                        'nomor_peminjaman' => '<span class="font-mono text-gray-600 text-xs">' . e($item->nomor_peminjaman) . '</span>',
                        'anggota_info' => '<div class="font-medium text-gray-900 text-xs">' . e($item->anggota->nama_lengkap ?? '—') . '</div><div class="text-xs text-gray-400">' . e($item->anggota->nomor_anggota ?? '') . ($item->anggota->kelas ? ' &middot; ' . e($item->anggota->kelas->nama_kelas) : '') . '</div>',
                        'tanggal_peminjaman' => '<span class="text-gray-600 text-xs whitespace-nowrap">' . \Carbon\Carbon::parse($item->tanggal_peminjaman)->format('d/m/Y') . '</span>',
                        'tanggal_kembali' => '<span class="text-gray-600 text-xs whitespace-nowrap">' . ($item->tanggal_kembali ? \Carbon\Carbon::parse($item->tanggal_kembali)->format('d/m/Y') : '<span class="text-gray-400">—</span>') . '</span>',
                        'jumlah_buku' => '<span class="font-semibold text-gray-800 text-xs">' . $item->detailPeminjaman->count() . '</span>',
                        'status_label' => '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold border ' . $sc . '"><span class="w-1.5 h-1.5 rounded-full ' . ($item->status == 'dipinjam' ? 'bg-amber-500' : ($item->status == 'dikembalikan' ? 'bg-emerald-500' : 'bg-red-500')) . '"></span> ' . ucfirst(e($item->status)) . '</span>',
                        'petugas' => '<span class="text-gray-600 text-xs">' . e($item->user->name ?? '—') . '</span>',
                    ];
                });

            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        if ($request->filled('export') && $request->export === 'excel') {
            $peminjaman = $query->orderBy('tanggal_peminjaman', 'desc')->get();
            return Excel::download(new PeminjamanExport($peminjaman), 'laporan-peminjaman-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            $peminjaman = $query->orderBy('tanggal_peminjaman', 'desc')->get();
            return $this->generatePdf('admin.laporan.pdf.peminjaman', compact('peminjaman'), 'laporan-peminjaman-' . date('Y-m-d') . '.pdf');
        }

        $totalPeminjaman = $query->count();
        $dipinjam = (clone $query)->where('status', 'dipinjam')->count();
        $dikembalikan = (clone $query)->where('status', 'dikembalikan')->count();
        $terlambat = (clone $query)->where('status', 'terlambat')->count();
        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.peminjaman', compact('totalPeminjaman', 'dipinjam', 'dikembalikan', 'terlambat', 'pengaturan'));
    }

    public function pengembalian(Request $request)
    {
        $query = Pengembalian::with(['anggota.kelas.jurusan', 'detailPengembalian.buku.kategoriBuku', 'user', 'peminjaman']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_pengembalian', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        // DataTables server-side
        if ($request->ajax() && $request->filled('draw')) {
            $columns = $request->columns ?? [];
            $orderColName = $columns[$request->order[0]['column']]['name'] ?? 'tanggal_pengembalian';
            $orderDir = $request->order[0]['dir'] ?? 'desc';

            $orderMap = [
                'nomor_pengembalian' => 'nomor_pengembalian',
                'anggota_id' => 'anggota_id',
                'tanggal_pengembalian' => 'tanggal_pengembalian',
                'jumlah_hari_terlambat' => 'jumlah_hari_terlambat',
                'total_denda' => 'total_denda',
            ];
            $orderColumn = $orderMap[$orderColName] ?? 'tanggal_pengembalian';

            $recordsTotal = $query->count();

            if ($search = $request->search['value']) {
                $query->where(function($q) use ($search) {
                    $q->where('nomor_pengembalian', 'like', "%{$search}%")
                      ->orWhereHas('anggota', function($q) use ($search) {
                          $q->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nomor_anggota', 'like', "%{$search}%");
                      });
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy($orderColumn, $orderDir)
                ->skip($request->start)
                ->take($request->length)
                ->get()
                ->map(function($item, $index) use ($request) {
                    $rowIndex = $request->start + $index + 1;
                    $jmlBuku = $item->detailPengembalian->sum('jumlah_dikembalikan');

                    $terlambat = $item->jumlah_hari_terlambat ?? 0;
                    if ($terlambat > 0) {
                        $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Terlambat ' . $terlambat . ' hari</span>';
                    } else {
                        $statusBadge = '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tepat Waktu</span>';
                    }

                    return [
                        'DT_RowIndex' => $rowIndex,
                        'nomor_pengembalian' => '<span class="font-mono text-gray-600 text-xs">' . e($item->nomor_pengembalian) . '</span>',
                        'anggota_info' => '<div class="font-medium text-gray-900 text-xs">' . e($item->anggota->nama_lengkap ?? '—') . '</div><div class="text-xs text-gray-400">' . e($item->anggota->nomor_anggota ?? '') . '</div>',
                        'tanggal_pengembalian' => '<span class="text-gray-600 text-xs whitespace-nowrap">' . \Carbon\Carbon::parse($item->tanggal_pengembalian)->format('d/m/Y') . '</span>',
                        'jumlah_buku' => '<span class="font-semibold text-gray-800 text-xs">' . $jmlBuku . '</span>',
                        'status_label' => $statusBadge,
                        'total_denda' => '<span class="font-semibold text-red-600 text-xs">Rp ' . number_format($item->total_denda ?? 0, 0, ',', '.') . '</span>',
                    ];
                });

            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        if ($request->filled('export') && $request->export === 'excel') {
            $pengembalian = $query->orderBy('tanggal_pengembalian', 'desc')->get();
            return Excel::download(new PengembalianExport($pengembalian), 'laporan-pengembalian-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            $pengembalian = $query->orderBy('tanggal_pengembalian', 'desc')->get();
            return $this->generatePdf('admin.laporan.pdf.pengembalian', compact('pengembalian'), 'laporan-pengembalian-' . date('Y-m-d') . '.pdf');
        }

        $totalPengembalian = $query->count();
        $tepatWaktu = (clone $query)->where(function($q) { $q->whereNull('jumlah_hari_terlambat')->orWhere('jumlah_hari_terlambat', 0); })->count();
        $terlambatKembali = (clone $query)->where('jumlah_hari_terlambat', '>', 0)->count();
        $totalDenda = $query->sum('total_denda');
        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.pengembalian', compact('totalPengembalian', 'tepatWaktu', 'terlambatKembali', 'totalDenda', 'pengaturan'));
    }

    public function denda(Request $request)
    {
        $query = Denda::with(['peminjaman.anggota.kelas.jurusan', 'peminjaman.detailPeminjaman.buku.kategoriBuku']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            if ($request->filled('status') && $request->status === 'sudah_dibayar') {
                $query->whereBetween('tanggal_pembayaran', [$request->tanggal_mulai, $request->tanggal_akhir]);
            } else {
                $query->whereBetween('created_at', [$request->tanggal_mulai, $request->tanggal_akhir]);
            }
        }

        if ($request->filled('status')) {
            $query->where('status_pembayaran', $request->status);
        }

        // DataTables server-side
        if ($request->ajax() && $request->filled('draw')) {
            $columns = $request->columns ?? [];
            $orderColName = $columns[$request->order[0]['column']]['name'] ?? 'created_at';
            $orderDir = $request->order[0]['dir'] ?? 'desc';

            $orderMap = [
                'anggota_id' => 'anggota_id',
                'peminjaman_id' => 'peminjaman_id',
                'jumlah_hari_terlambat' => 'jumlah_hari_terlambat',
                'jumlah_denda' => 'jumlah_denda',
                'status_pembayaran' => 'status_pembayaran',
                'tanggal_pembayaran' => 'tanggal_pembayaran',
                'created_at' => 'created_at',
            ];
            $orderColumn = $orderMap[$orderColName] ?? 'created_at';

            $recordsTotal = $query->count();

            if ($search = $request->search['value']) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('peminjaman.anggota', function($q) use ($search) {
                        $q->where('nama_lengkap', 'like', "%{$search}%")
                          ->orWhere('nomor_anggota', 'like', "%{$search}%");
                    })->orWhereHas('peminjaman', function($q) use ($search) {
                        $q->where('nomor_peminjaman', 'like', "%{$search}%");
                    });
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy($orderColumn, $orderDir)
                ->skip($request->start)
                ->take($request->length)
                ->get()
                ->map(function($item, $index) use ($request) {
                    $rowIndex = $request->start + $index + 1;

                    $statusBadge = $item->status_pembayaran == 'sudah_dibayar'
                        ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sudah Bayar</span>'
                        : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-red-50 text-red-700 border border-red-200"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Belum Bayar</span>';

                    $tglBayar = $item->tanggal_pembayaran
                        ? '<span class="text-gray-600 text-xs whitespace-nowrap">' . \Carbon\Carbon::parse($item->tanggal_pembayaran)->format('d/m/Y') . '</span>'
                        : '<span class="text-gray-400 text-xs">—</span>';

                    return [
                        'DT_RowIndex' => $rowIndex,
                        'anggota_info' => '<div class="font-medium text-gray-900 text-xs">' . e($item->peminjaman->anggota->nama_lengkap ?? '—') . '</div><div class="text-xs text-gray-400">' . e($item->peminjaman->anggota->nomor_anggota ?? '') . '</div>',
                        'nomor_peminjaman' => '<span class="font-mono text-gray-600 text-xs">' . e($item->peminjaman->nomor_peminjaman ?? '—') . '</span>',
                        'jumlah_hari_terlambat' => '<span class="font-semibold text-amber-600 text-xs">' . $item->jumlah_hari_terlambat . ' hari</span>',
                        'jumlah_denda' => '<span class="font-semibold text-red-600 text-xs">Rp ' . number_format($item->jumlah_denda ?? 0, 0, ',', '.') . '</span>',
                        'status_pembayaran' => $statusBadge,
                        'tanggal_pembayaran' => $tglBayar,
                    ];
                });

            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        if ($request->filled('export') && $request->export === 'excel') {
            $denda = $query->orderBy('created_at', 'desc')->get();
            return Excel::download(new DendaExport($denda), 'laporan-denda-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            $denda = $query->orderBy('created_at', 'desc')->get();
            return $this->generatePdf('admin.laporan.pdf.denda', compact('denda'), 'laporan-denda-' . date('Y-m-d') . '.pdf');
        }

        $totalDenda = $query->count();
        $totalNominal = $query->sum('jumlah_denda');
        $sudahDibayar = (clone $query)->where('status_pembayaran', 'sudah_dibayar')->count();
        $belumDibayar = (clone $query)->where('status_pembayaran', 'belum_dibayar')->count();
        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.denda', compact('totalDenda', 'totalNominal', 'sudahDibayar', 'belumDibayar', 'pengaturan'));
    }

    public function bukuTamu(Request $request)
    {
        $query = BukuTamu::with(['anggota.kelas.jurusan', 'petugas']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('waktu_datang', [$request->tanggal_mulai . ' 00:00:00', $request->tanggal_akhir . ' 23:59:59']);
        }

        if ($request->filled('tipe')) {
            if ($request->tipe === 'anggota') {
                $query->whereNotNull('anggota_id');
            } elseif ($request->tipe === 'umum') {
                $query->whereNull('anggota_id');
            }
        }

        if ($request->filled('status')) {
            if ($request->status === 'berkunjung') {
                $query->whereNull('waktu_pulang');
            } elseif ($request->status === 'pulang') {
                $query->whereNotNull('waktu_pulang');
            }
        }

        // DataTables server-side
        if ($request->ajax() && $request->filled('draw')) {
            $columns = $request->columns ?? [];
            $orderColName = $columns[$request->order[0]['column']]['name'] ?? 'waktu_datang';
            $orderDir = $request->order[0]['dir'] ?? 'desc';

            $orderMap = [
                'waktu_datang' => 'waktu_datang',
                'waktu_pulang' => 'waktu_pulang',
                'nama_tamu' => 'nama_tamu',
                'tipe' => 'anggota_id',
                'keperluan' => 'keperluan',
                'status_kunjungan' => 'waktu_pulang',
            ];
            $orderColumn = $orderMap[$orderColName] ?? 'waktu_datang';

            $recordsTotal = $query->count();

            if ($search = $request->search['value']) {
                $query->where(function($q) use ($search) {
                    $q->where('nama_tamu', 'like', "%{$search}%")
                      ->orWhere('keperluan', 'like', "%{$search}%")
                      ->orWhere('instansi', 'like', "%{$search}%")
                      ->orWhere('no_telepon', 'like', "%{$search}%")
                      ->orWhereHas('anggota', function($q) use ($search) {
                          $q->where('nama_lengkap', 'like', "%{$search}%")
                            ->orWhere('nomor_anggota', 'like', "%{$search}%");
                      });
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy($orderColumn, $orderDir)
                ->skip($request->start)
                ->take($request->length)
                ->get()
                ->map(function($item, $index) use ($request) {
                    $rowIndex = $request->start + $index + 1;

                    $tipeBadge = $item->anggota_id
                        ? '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">Anggota</span>'
                        : '<span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-200">Tamu Umum</span>';

                    $statusBadge = $item->waktu_pulang
                        ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-200"><span class="w-1.5 h-1.5 rounded-full bg-gray-500"></span> Sudah Pulang</span>'
                        : '<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Berkunjung</span>';

                    $namaInfo = $item->anggota_id
                        ? '<div class="font-medium text-gray-900 text-xs">' . e($item->anggota->nama_lengkap ?? $item->nama_tamu) . '</div><div class="text-xs text-gray-400">' . e($item->anggota->nomor_anggota ?? '') . '</div>'
                        : '<div class="font-medium text-gray-900 text-xs">' . e($item->nama_tamu) . '</div>' . ($item->no_telepon ? '<div class="text-xs text-gray-400">' . e($item->no_telepon) . '</div>' : '');

                    $kelasInfo = '';
                    if ($item->anggota_id && $item->anggota) {
                        $kelasInfo = e($item->anggota->kelas->nama_kelas ?? '');
                        if ($item->anggota->kelas && $item->anggota->kelas->jurusan) {
                            $kelasInfo .= ' - ' . e($item->anggota->kelas->jurusan->nama_jurusan);
                        }
                    } else {
                        $kelasInfo = e($item->instansi ?? '—');
                    }

                    return [
                        'DT_RowIndex' => $rowIndex,
                        'tanggal' => '<span class="text-gray-600 text-xs whitespace-nowrap">' . \Carbon\Carbon::parse($item->waktu_datang)->format('d/m/Y') . '</span>',
                        'waktu_datang' => '<span class="text-gray-600 text-xs whitespace-nowrap">' . \Carbon\Carbon::parse($item->waktu_datang)->format('H:i') . '</span>',
                        'waktu_pulang' => $item->waktu_pulang
                            ? '<span class="text-gray-600 text-xs whitespace-nowrap">' . \Carbon\Carbon::parse($item->waktu_pulang)->format('H:i') . '</span>'
                            : '<span class="text-gray-400 text-xs">—</span>',
                        'nama_info' => $namaInfo,
                        'tipe_label' => $tipeBadge,
                        'kelas_info' => '<span class="text-gray-600 text-xs">' . $kelasInfo . '</span>',
                        'keperluan' => '<span class="text-gray-600 text-xs">' . e($item->keperluan) . '</span>',
                        'status_label' => $statusBadge,
                    ];
                });

            return response()->json([
                'draw' => (int) $request->draw,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
            ]);
        }

        if ($request->filled('export') && $request->export === 'excel') {
            $bukuTamu = $query->orderBy('waktu_datang', 'desc')->get();
            return Excel::download(new BukuTamuExport($bukuTamu), 'laporan-buku-tamu-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            $bukuTamu = $query->orderBy('waktu_datang', 'desc')->get();
            return $this->generatePdf('admin.laporan.pdf.buku-tamu', compact('bukuTamu'), 'laporan-buku-tamu-' . date('Y-m-d') . '.pdf');
        }

        $totalKunjungan = $query->count();
        $anggota = (clone $query)->whereNotNull('anggota_id')->count();
        $umum = (clone $query)->whereNull('anggota_id')->count();
        $berkunjung = (clone $query)->whereNull('waktu_pulang')->count();
        $sudahPulang = (clone $query)->whereNotNull('waktu_pulang')->count();
        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.buku-tamu', compact('totalKunjungan', 'anggota', 'umum', 'berkunjung', 'sudahPulang', 'pengaturan'));
    }
}
