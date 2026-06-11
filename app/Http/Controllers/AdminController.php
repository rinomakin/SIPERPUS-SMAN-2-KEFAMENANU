<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\PengaturanWebsite;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Models\Denda;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Mengubah middleware untuk mengizinkan akses dari ADMIN, KEPALA_SEKOLAH, dan PETUGAS
        $this->middleware('role:ADMIN,KEPALA_SEKOLAH,PETUGAS');
    }

    public function dashboard()
    {
        // 1. Summary Statistics
        $totalAnggota = Anggota::count();
        $totalBuku = Buku::count();
        $peminjamanAktif = Peminjaman::where('status', 'dipinjam')->count();
        $totalDenda = Denda::sum('jumlah_denda');
        $bukuDipinjam = Peminjaman::where('status', 'dipinjam')
            ->join('detail_peminjaman', 'peminjaman.id', '=', 'detail_peminjaman.peminjaman_id')
            ->sum('detail_peminjaman.jumlah');
        $anggotaBaruBulanIni = Anggota::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // 2. Chart: Peminjaman & Pengembalian per Bulan (Last 6 Months)
        $months = collect([]);
        $peminjamanPerBulan = collect([]);
        $pengembalianPerBulan = collect([]);

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthName = $date->format('M Y');
            $months->push($monthName);

            $peminjamanCount = Peminjaman::whereMonth('tanggal_peminjaman', $date->month)
                ->whereYear('tanggal_peminjaman', $date->year)
                ->count();
            $peminjamanPerBulan->push($peminjamanCount);

            $pengembalianCount = \App\Models\Pengembalian::whereMonth('tanggal_pengembalian', $date->month)
                ->whereYear('tanggal_pengembalian', $date->year)
                ->count();
            $pengembalianPerBulan->push($pengembalianCount);
        }

        // 3. Chart: Kategori Buku Terpopuler (Top 5)
        $kategoriTerpopuler = Buku::join('kategori_buku', 'buku.kategori_id', '=', 'kategori_buku.id')
            ->select('kategori_buku.nama_kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori_buku.nama_kategori')
            ->orderByDesc('total')
            ->limit(5)
            ->get();
        
        $kategoriLabels = $kategoriTerpopuler->pluck('nama_kategori');
        $kategoriData = $kategoriTerpopuler->pluck('total');

        // 4. Recent Activities (Combined Peminjaman & Pengembalian)
        $recentPeminjaman = Peminjaman::with('anggota')
            ->select('id', 'anggota_id', 'created_at', 'status', DB::raw("'peminjaman' as type"))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
            
        $recentPengembalian = \App\Models\Pengembalian::with('anggota')
            ->select('id', 'anggota_id', 'created_at', DB::raw("'selesai' as status"), DB::raw("'pengembalian' as type"))
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentActivities = $recentPeminjaman->concat($recentPengembalian)
            ->sortByDesc('created_at')
            ->take(6);

        // 5. Buku Terpopuler (Sering Dipinjam) — hitung jumlah kali dipinjam, bukan jumlah buku
        $bukuTerpopuler = Buku::withCount(['detailPeminjaman as total_dipinjam'])
            ->orderByDesc('total_dipinjam')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalAnggota', 
            'totalBuku', 
            'peminjamanAktif', 
            'totalDenda',
            'bukuDipinjam',
            'anggotaBaruBulanIni',
            'months',
            'peminjamanPerBulan',
            'pengembalianPerBulan',
            'kategoriLabels',
            'kategoriData',
            'recentActivities',
            'bukuTerpopuler'
        ));
    }

    public function pengaturanWebsite()
    {
        $pengaturan = PengaturanWebsite::first();
        return view('admin.pengaturan-website', compact('pengaturan'));
    }

    public function updatePengaturanWebsite(Request $request)
    {
        $request->validate([
            'nama_website' => 'required|string|max:255',
            'deskripsi_website' => 'nullable|string',
            'alamat_sekolah' => 'nullable|string',
            'telepon_sekolah' => 'nullable|string',
            'email_sekolah' => 'nullable|email',
            'nama_kepala_sekolah' => 'nullable|string',
            'visi_sekolah' => 'nullable|string',
            'misi_sekolah' => 'nullable|string',
            'sejarah_sekolah' => 'nullable|string',
            'jam_operasional' => 'nullable|string',
            'kebijakan_perpustakaan' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'favicon' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,ico|max:1024',
        ]);

        $pengaturan = PengaturanWebsite::first();
        
        if ($request->hasFile('logo')) {
            $logo = $request->file('logo');
            $logoName = time() . '.' . $logo->getClientOriginalExtension();
            $logo->move(public_path('uploads/logo'), $logoName);
            $pengaturan->logo = 'uploads/logo/' . $logoName;
        }

        if ($request->hasFile('favicon')) {
            $favicon = $request->file('favicon');
            $faviconName = time() . '.' . $favicon->getClientOriginalExtension();
            $favicon->move(public_path('uploads/favicon'), $faviconName);
            $pengaturan->favicon = 'uploads/favicon/' . $faviconName;
        }

        $pengaturan->update($request->except(['logo', 'favicon']));

        return redirect()->back()->with('success', 'Pengaturan website berhasil diperbarui');
    }

    public function profil()
    {
        $user = auth()->user();
        
        // Validasi berdasarkan role user yang login
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Pastikan user hanya bisa melihat profilnya sendiri
        // Tidak perlu validasi tambahan karena menggunakan auth()->user()
        
        // Tentukan view berdasarkan role user
        if ($user->isPetugas()) {
            return view('petugas.profil', compact('user'));
        } elseif ($user->isKepalaSekolah()) {
            return view('kepsek.profil', compact('user'));
        } else {
            return view('admin.profil', compact('user'));
        }
    }

    public function updateProfil(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nama_panggilan' => 'nullable|string|max:100',
            'email' => 'required|email|max:255|unique:users,email,' . auth()->id(),
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();
        $data = $request->except('foto');

        // Handle foto upload
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($user->foto && file_exists(public_path('storage/' . $user->foto))) {
                unlink(public_path('storage/' . $user->foto));
            }

            // Upload foto baru
            $foto = $request->file('foto');
            $fotoName = 'profil_' . $user->id . '_' . time() . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('storage/foto-profil'), $fotoName);
            $data['foto'] = 'foto-profil/' . $fotoName;
        }

        $user->update($data);

        return redirect()->back()->with('success', 'Profil berhasil diperbarui');
    }

    public function hapusFoto(Request $request)
    {
        $user = auth()->user();
        
        if ($user->foto && file_exists(public_path('storage/' . $user->foto))) {
            unlink(public_path('storage/' . $user->foto));
        }
        
        $user->update(['foto' => null]);
        
        return redirect()->back()->with('success', 'Foto profil berhasil dihapus');
    }

    public function gantiPassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|min:8|confirmed',
        ], [
            'password_lama.required' => 'Password lama harus diisi',
            'password_baru.required' => 'Password baru harus diisi',
            'password_baru.min' => 'Password baru minimal 8 karakter',
            'password_baru.confirmed' => 'Konfirmasi password tidak cocok',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return redirect()->back()->with('error', 'Password lama tidak sesuai');
        }

        $user->update([
            'password' => Hash::make($request->password_baru)
        ]);

        return redirect()->back()->with('success', 'Password berhasil diubah');
    }
} 