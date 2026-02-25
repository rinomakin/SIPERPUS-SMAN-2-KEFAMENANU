<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BukuTamu;
use App\Models\Peminjaman;
use App\Models\Pengembalian;
use App\Models\PengaturanWebsite;
use Carbon\Carbon;

class PetugasController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:PETUGAS');
    }

    public function dashboard()
    {
        $today    = Carbon::today();
        $yesterday = Carbon::yesterday();

        // ── Statistik Buku Tamu ────────────────────────────────────────
        $totalTamuHariIni  = BukuTamu::whereDate('waktu_datang', $today)->count();
        $sedangBerkunjung  = BukuTamu::whereDate('waktu_datang', $today)->whereNull('waktu_pulang')->count();
        $sudahPulang       = BukuTamu::whereDate('waktu_datang', $today)->whereNotNull('waktu_pulang')->count();
        $totalBulanIni     = BukuTamu::whereMonth('waktu_datang', now()->month)
                                     ->whereYear('waktu_datang', now()->year)->count();
        $totalKemarin      = BukuTamu::whereDate('waktu_datang', $yesterday)->count();
        $selisihKemarin    = $totalTamuHariIni - $totalKemarin;
        $tamuAnggota       = BukuTamu::whereDate('waktu_datang', $today)->whereNotNull('anggota_id')->count();
        $tamuUmum          = BukuTamu::whereDate('waktu_datang', $today)->whereNull('anggota_id')->count();

        // ── Statistik Peminjaman & Pengembalian ────────────────────────
        $peminjamanHariIni   = Peminjaman::whereDate('tanggal_peminjaman', $today)->count();
        $peminjamanAktif     = Peminjaman::where('status', 'dipinjam')->count();
        $peminjamanTerlambat = Peminjaman::where('status', 'dipinjam')
                                ->where('tanggal_harus_kembali', '<', $today)->count();
        $pengembalianHariIni = Pengembalian::whereDate('tanggal_pengembalian', $today)->count();

        // ── Daftar Tamu Saat Ini ────────────────────────────────────────
        $tamuSaatIni = BukuTamu::whereDate('waktu_datang', $today)
            ->whereNull('waktu_pulang')
            ->with(['anggota.kelas'])
            ->orderBy('waktu_datang', 'asc')
            ->get();

        // ── Aktivitas Terbaru (10 terakhir) ────────────────────────────
        $recentActivities = BukuTamu::whereDate('waktu_datang', $today)
            ->with(['anggota.kelas'])
            ->orderBy('waktu_datang', 'desc')
            ->limit(10)
            ->get();

        // ── Distribusi Per Jam (06:00–18:00) ───────────────────────────
        $jamData = [];
        for ($i = 6; $i <= 18; $i++) {
            $jamData[$i] = BukuTamu::whereDate('waktu_datang', $today)
                ->whereRaw('HOUR(waktu_datang) = ?', [$i])
                ->count();
        }
        $maxJam = max($jamData) ?: 1;

        // ── Tren 7 Hari Terakhir ────────────────────────────────────────
        $last7Days           = [];
        $tamuPerHari         = [];
        $peminjamanPerHari   = [];
        $pengembalianPerHari = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $last7Days[]           = $date->translatedFormat('d M');
            $tamuPerHari[]         = BukuTamu::whereDate('waktu_datang', $date)->count();
            $peminjamanPerHari[]   = Peminjaman::whereDate('tanggal_peminjaman', $date)->count();
            $pengembalianPerHari[] = Pengembalian::whereDate('tanggal_pengembalian', $date)->count();
        }

        return view('petugas.dashboard', compact(
            'totalTamuHariIni', 'sedangBerkunjung', 'sudahPulang', 'totalBulanIni',
            'totalKemarin', 'selisihKemarin', 'tamuAnggota', 'tamuUmum',
            'tamuSaatIni', 'recentActivities', 'jamData', 'maxJam',
            'peminjamanHariIni', 'peminjamanAktif', 'peminjamanTerlambat', 'pengembalianHariIni',
            'last7Days', 'tamuPerHari', 'peminjamanPerHari', 'pengembalianPerHari'
        ));
    }

    public function beranda()
    {
        $pengaturan = PengaturanWebsite::first();
        return view('petugas.beranda', compact('pengaturan'));
    }

    public function tentang()
    {
        $pengaturan = PengaturanWebsite::first();
        return view('petugas.tentang', compact('pengaturan'));
    }
}
