<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\BukuTamu;
use App\Models\Jurusan;
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

        if ($request->filled('jurusan_id')) {
            $query->whereHas('kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        $anggota = $query->orderBy('created_at', 'desc')->get();
        $jurusan = Jurusan::all();
        $kelas = Kelas::with('jurusan')->get();

        if ($request->filled('export') && $request->export === 'excel') {
            return Excel::download(new AnggotaExport($anggota), 'laporan-anggota-' . date('Y-m-d') . '.xlsx');
        }

        if ($request->filled('export') && $request->export === 'pdf') {
            return $this->generatePdf('admin.laporan.pdf.anggota', compact('anggota'), 'laporan-anggota-' . date('Y-m-d') . '.pdf');
        }

        $pengaturan = $this->getPengaturan();
        return view('admin.laporan.anggota', compact('anggota', 'jurusan', 'kelas', 'pengaturan'));
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
