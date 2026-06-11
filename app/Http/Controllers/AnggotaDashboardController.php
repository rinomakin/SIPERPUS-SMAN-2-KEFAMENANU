<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use App\Models\Peminjaman;
use App\Models\Denda;
use App\Models\Buku;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AnggotaDashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:ANGGOTA');
    }

    public function dashboard()
    {
        $user = Auth::user();

        $anggota = Anggota::where('email', $user->email)->first();

        $data = ['anggota' => $anggota];

        if ($anggota) {
            $data += [
                'peminjamanAktif' => Peminjaman::where('anggota_id', $anggota->id)
                    ->where('status', 'dipinjam')
                    ->with(['detailPeminjaman.buku'])
                    ->orderBy('tanggal_peminjaman', 'desc')
                    ->get(),

                'riwayatPeminjaman' => Peminjaman::where('anggota_id', $anggota->id)
                    ->with(['detailPeminjaman.buku'])
                    ->whereIn('status', ['selesai', 'dikembalikan'])
                    ->orderBy('tanggal_peminjaman', 'desc')
                    ->limit(10)
                    ->get(),

                'totalDenda' => Denda::where('anggota_id', $anggota->id)
                    ->where('status_pembayaran', 'belum_dibayar')
                    ->sum('jumlah_denda'),

                'totalPernahDipinjam' => Peminjaman::where('anggota_id', $anggota->id)->count(),

                'bukuPopuler' => Buku::withCount(['detailPeminjaman as total_dipinjam' => function ($q) {
                    $q->select(DB::raw('COALESCE(sum(jumlah), 0)'));
                }])
                    ->orderByDesc('total_dipinjam')
                    ->limit(5)
                    ->get(),
            ];
        } else {
            $data += [
                'peminjamanAktif' => collect(),
                'riwayatPeminjaman' => collect(),
                'totalDenda' => 0,
                'totalPernahDipinjam' => 0,
                'bukuPopuler' => Buku::withCount(['detailPeminjaman as total_dipinjam' => function ($q) {
                    $q->select(DB::raw('COALESCE(sum(jumlah), 0)'));
                }])
                    ->orderByDesc('total_dipinjam')
                    ->limit(5)
                    ->get(),
            ];
        }

        return view('anggota.dashboard', $data);
    }

    public function profil()
    {
        $user = Auth::user();
        $anggota = Anggota::where('email', $user->email)->first();
        $kelas = Kelas::with('jurusan')->get();

        return view('anggota.profil', compact('anggota', 'kelas'));
    }

    public function updateProfil(Request $request)
    {
        $user = Auth::user();
        $anggota = Anggota::where('email', $user->email)->firstOrFail();

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'tanggal_lahir' => 'nullable|date',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['foto']);

            if ($request->hasFile('foto')) {
                if ($anggota->foto && file_exists(public_path('storage/anggota/' . $anggota->foto))) {
                    unlink(public_path('storage/anggota/' . $anggota->foto));
                }
                $foto = $request->file('foto');
                $fotoName = time() . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
                $foto->move(public_path('storage/anggota'), $fotoName);
                $data['foto'] = $fotoName;
            }

            $anggota->update($data);

            // Sync User data
            $user->update([
                'nama_lengkap' => $request->nama_lengkap,
                'email' => $request->email ?? $user->email,
                'nomor_telepon' => $request->nomor_telepon,
                'alamat' => $request->alamat,
            ]);

            DB::commit();

            return redirect()->route('anggota.profil')
                ->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Gagal memperbarui profil: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function gantiPassword(Request $request)
    {
        $request->validate([
            'password_lama' => 'required',
            'password_baru' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password_lama, $user->password)) {
            return redirect()->back()->with('error', 'Password lama tidak sesuai.');
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return redirect()->route('anggota.profil')
            ->with('success', 'Password berhasil diubah.');
    }
}
