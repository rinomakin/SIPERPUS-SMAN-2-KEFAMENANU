<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\BukuTamu;
use App\Models\KategoriBuku;
use App\Models\JenisBuku;
use App\Models\Kelas;
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

        $kelas = Kelas::with('jurusan')->get();

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

        $buku = $query->orderBy('created_at', 'desc')->get();
        $kategori = KategoriBuku::all();
        $jenis = JenisBuku::all();

        if ($request->filled('export') && $request->export === 'excel') {
            return Excel::download(new BukuExport($buku), 'laporan-buku-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->generatePdf('admin.laporan.pdf.buku', compact('buku'), 'laporan-buku-' . date('Y-m-d') . '.pdf');
        }

        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.buku', compact('buku', 'kategori', 'jenis', 'pengaturan'));
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

        $peminjaman = $query->orderBy('tanggal_peminjaman', 'desc')->get();

        if ($request->filled('export') && $request->export === 'excel') {
            return Excel::download(new PeminjamanExport($peminjaman), 'laporan-peminjaman-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->generatePdf('admin.laporan.pdf.peminjaman', compact('peminjaman'), 'laporan-peminjaman-' . date('Y-m-d') . '.pdf');
        }

        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.peminjaman', compact('peminjaman', 'pengaturan'));
    }

    public function pengembalian(Request $request)
    {
        $query = Pengembalian::with(['anggota.kelas.jurusan', 'detailPengembalian.buku.kategoriBuku', 'user', 'peminjaman']);

        if ($request->filled('tanggal_mulai') && $request->filled('tanggal_akhir')) {
            $query->whereBetween('tanggal_pengembalian', [$request->tanggal_mulai, $request->tanggal_akhir]);
        }

        $pengembalian = $query->orderBy('tanggal_pengembalian', 'desc')->get();

        if ($request->filled('export') && $request->export === 'excel') {
            return Excel::download(new PengembalianExport($pengembalian), 'laporan-pengembalian-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->generatePdf('admin.laporan.pdf.pengembalian', compact('pengembalian'), 'laporan-pengembalian-' . date('Y-m-d') . '.pdf');
        }

        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.pengembalian', compact('pengembalian', 'pengaturan'));
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

        $denda = $query->orderBy('created_at', 'desc')->get();

        if ($request->filled('export') && $request->export === 'excel') {
            return Excel::download(new DendaExport($denda), 'laporan-denda-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->generatePdf('admin.laporan.pdf.denda', compact('denda'), 'laporan-denda-' . date('Y-m-d') . '.pdf');
        }

        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.denda', compact('denda', 'pengaturan'));
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

        $bukuTamu = $query->orderBy('waktu_datang', 'desc')->get();

        if ($request->filled('export') && $request->export === 'excel') {
            return Excel::download(new BukuTamuExport($bukuTamu), 'laporan-buku-tamu-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->generatePdf('admin.laporan.pdf.buku-tamu', compact('bukuTamu'), 'laporan-buku-tamu-' . date('Y-m-d') . '.pdf');
        }

        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.buku-tamu', compact('bukuTamu', 'pengaturan'));
    }
}
