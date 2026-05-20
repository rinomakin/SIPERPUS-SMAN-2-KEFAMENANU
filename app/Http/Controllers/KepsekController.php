<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Denda;
use Carbon\Carbon;

class KepsekController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:KEPALA_SEKOLAH');
    }

    public function dashboard()
    {
        $now = now();

        // ── Stat Cards ──────────────────────────────────────────
        $totalAnggota       = Anggota::count();
        $totalBuku          = Buku::count();
        $totalPeminjaman    = Peminjaman::where('status', 'dipinjam')->count();
        $totalDendaNominal  = Denda::where('status_pembayaran', 'belum_dibayar')->sum('jumlah_denda');
        $jumlahDendaBelum   = Denda::where('status_pembayaran', 'belum_dibayar')->count();

        // ── Terlambat ────────────────────────────────────────────
        $terlambat = Peminjaman::where('status', 'dipinjam')
            ->where('tanggal_harus_kembali', '<', $now->toDateString())
            ->count();

        // ── Bulan ini ────────────────────────────────────────────
        $peminjamanBulanIni   = Peminjaman::whereMonth('created_at', $now->month)
                                           ->whereYear('created_at',  $now->year)->count();
        $pengembalianBulanIni = Peminjaman::whereNotNull('tanggal_kembali')
                                           ->whereMonth('tanggal_kembali', $now->month)
                                           ->whereYear('tanggal_kembali',  $now->year)->count();
        $dendaBulanIni        = Denda::whereMonth('created_at', $now->month)
                                      ->whereYear('created_at',  $now->year)->count();

        // ── Chart 7 hari terakhir ────────────────────────────────
        $chartLabels    = [];
        $chartPinjam    = [];
        $chartKembali   = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $chartLabels[]  = $day->format('d/m');
            $chartPinjam[]  = Peminjaman::whereDate('created_at', $day)->count();
            $chartKembali[] = Peminjaman::whereNotNull('tanggal_kembali')
                                         ->whereDate('tanggal_kembali', $day)->count();
        }

        // ── Anggota terlambat (top 5) ────────────────────────────
        $anggotaTerlambat = Peminjaman::with('anggota')
            ->where('status', 'dipinjam')
            ->where('tanggal_harus_kembali', '<', $now->toDateString())
            ->orderBy('tanggal_harus_kembali')
            ->take(5)
            ->get();

        return view('kepsek.dashboard', compact(
            'totalAnggota', 'totalBuku', 'totalPeminjaman',
            'totalDendaNominal', 'jumlahDendaBelum', 'terlambat',
            'peminjamanBulanIni', 'pengembalianBulanIni', 'dendaBulanIni',
            'chartLabels', 'chartPinjam', 'chartKembali',
            'anggotaTerlambat'
        ));
    }
} 