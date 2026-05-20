<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Denda;
use App\Models\Peminjaman;
use App\Models\Anggota;
use App\Models\Pengembalian;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DendaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        // Read-only: Kepala Sekolah boleh lihat (jika punya permission denda.view)
        $this->middleware('role:ADMIN,PETUGAS,KEPALA_SEKOLAH')->only([
            'index', 'show', 'riwayat', 'scanBarcodeDenda', 'searchDenda',
        ]);
        // Write operations: hanya Admin dan Petugas
        $this->middleware('role:ADMIN,PETUGAS')->except([
            'index', 'show', 'riwayat', 'scanBarcodeDenda', 'searchDenda',
        ]);
    }

    public function index(Request $request)
    {
        $query = Denda::with(['peminjaman.detailPeminjaman.buku', 'anggota.kelas.jurusan'])
            ->where('status_pembayaran', 'belum_dibayar');

        // Filter pencarian nama/nomor anggota
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nomor_anggota', 'like', '%' . $search . '%')
                  ->orWhere('barcode_anggota', 'like', '%' . $search . '%');
            });
        }

        // Filter berdasarkan kelas
        if ($request->kelas_id) {
            $query->whereHas('anggota', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Filter berdasarkan jurusan (melalui kelas)
        if ($request->jurusan_id) {
            $query->whereHas('anggota.kelas', function ($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        // Filter berdasarkan jenis anggota
        if ($request->jenis_anggota) {
            $query->whereHas('anggota', function ($q) use ($request) {
                $q->where('jenis_anggota', $request->jenis_anggota);
            });
        }

        $denda = $query->orderBy('created_at', 'desc')->get();

        // Statistik denda
        $totalDenda = Denda::sum('jumlah_denda');
        $dendaBelumDibayar = Denda::where('status_pembayaran', 'belum_dibayar')->sum('jumlah_denda');
        $dendaSudahDibayar = Denda::where('status_pembayaran', 'sudah_dibayar')->sum('jumlah_denda');
        $totalDendaHariIni = Denda::whereDate('created_at', today())->sum('jumlah_denda');
        $jumlahBelumBayar = Denda::where('status_pembayaran', 'belum_dibayar')->count();

        // Data untuk filter dropdown
        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('admin.denda.index', compact(
            'denda', 'totalDenda', 'dendaBelumDibayar', 'dendaSudahDibayar',
            'totalDendaHariIni', 'jumlahBelumBayar', 'kelasList', 'jurusanList'
        ));
    }

    public function riwayat(Request $request)
    {
        $query = Denda::with(['peminjaman.detailPeminjaman.buku', 'anggota.kelas', 'anggota.jurusan'])
            ->where('status_pembayaran', 'sudah_dibayar');

        // Filter pencarian
        if ($request->search) {
            $search = $request->search;
            $query->whereHas('anggota', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', '%' . $search . '%')
                  ->orWhere('nomor_anggota', 'like', '%' . $search . '%');
            });
        }

        if ($request->tanggal_mulai) {
            $query->whereDate('tanggal_pembayaran', '>=', $request->tanggal_mulai);
        }

        if ($request->tanggal_selesai) {
            $query->whereDate('tanggal_pembayaran', '<=', $request->tanggal_selesai);
        }

        // Filter berdasarkan kelas
        if ($request->kelas_id) {
            $query->whereHas('anggota', function ($q) use ($request) {
                $q->where('kelas_id', $request->kelas_id);
            });
        }

        // Filter berdasarkan jurusan
        if ($request->jurusan_id) {
            $query->whereHas('anggota.kelas', function ($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        $riwayat = $query->orderBy('tanggal_pembayaran', 'desc')->get();

        // Statistik
        $totalDendaDibayar = Denda::where('status_pembayaran', 'sudah_dibayar')->sum('jumlah_denda');
        $jumlahTransaksi = Denda::where('status_pembayaran', 'sudah_dibayar')->count();
        $dendaBulanIni = Denda::where('status_pembayaran', 'sudah_dibayar')
            ->whereMonth('tanggal_pembayaran', now()->month)
            ->whereYear('tanggal_pembayaran', now()->year)
            ->sum('jumlah_denda');
        $rataRataDenda = $jumlahTransaksi > 0 ? round($totalDendaDibayar / $jumlahTransaksi) : 0;

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $jurusanList = Jurusan::orderBy('nama_jurusan')->get();

        return view('admin.denda.riwayat', compact(
            'riwayat', 'totalDendaDibayar', 'jumlahTransaksi', 'dendaBulanIni',
            'rataRataDenda', 'kelasList', 'jurusanList'
        ));
    }

    public function bulkDestroyRiwayat(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada data yang dipilih untuk dihapus.');
        }

        $deleted = Denda::whereIn('id', $ids)
            ->where('status_pembayaran', 'sudah_dibayar')
            ->delete();

        return back()->with('success', $deleted . ' riwayat denda berhasil dihapus.');
    }

    public function bayarLunas($id)
    {
        $denda = Denda::findOrFail($id);

        if ($denda->status_pembayaran === 'sudah_dibayar') {
            return back()->with('error', 'Denda ini sudah dibayar sebelumnya.');
        }

        $denda->update([
            'status_pembayaran' => 'sudah_dibayar',
            'tanggal_pembayaran' => now(),
        ]);

        // Update status denda di pengembalian terkait
        $this->syncPengembalianStatus($denda, 'sudah_dibayar', now());

        return redirect()->route('admin.denda.index')
            ->with('success', 'Denda berhasil dibayar lunas.');
    }

    /**
     * Cari denda berdasarkan scan barcode anggota
     */
    public function scanBarcodeDenda(Request $request)
    {
        $barcode = $request->barcode;

        if (!$barcode) {
            return response()->json(['success' => false, 'message' => 'Barcode tidak boleh kosong']);
        }

        $anggota = Anggota::where('barcode_anggota', $barcode)
            ->orWhere('nomor_anggota', $barcode)
            ->first();

        if (!$anggota) {
            return response()->json(['success' => false, 'message' => 'Anggota tidak ditemukan']);
        }

        $dendaBelumBayar = Denda::with(['peminjaman.detailPeminjaman.buku'])
            ->where('anggota_id', $anggota->id)
            ->where('status_pembayaran', 'belum_dibayar')
            ->get();

        return response()->json([
            'success' => true,
            'anggota' => [
                'id' => $anggota->id,
                'nama_lengkap' => $anggota->nama_lengkap,
                'nomor_anggota' => $anggota->nomor_anggota,
                'barcode_anggota' => $anggota->barcode_anggota,
                'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : '-',
                'foto' => $anggota->foto ? asset('storage/anggota/' . $anggota->foto) : null,
            ],
            'denda' => $dendaBelumBayar,
            'total_denda' => $dendaBelumBayar->sum('jumlah_denda'),
            'jumlah_denda' => $dendaBelumBayar->count(),
        ]);
    }

    public function create()
    {
        // Ambil peminjaman yang terlambat atau belum dikembalikan
        $peminjamanTerlambat = Peminjaman::with(['anggota.kelas', 'anggota.jurusan', 'detailPeminjaman.buku'])
            ->where('status', 'dipinjam')
            ->where('tanggal_harus_kembali', '<', now())
            ->get();

        $anggota = Anggota::where('status', 'aktif')->get();
        
        return view('admin.denda.create', compact('peminjamanTerlambat', 'anggota'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'jumlah_hari_terlambat' => 'required|integer|min:1',
            'jumlah_denda' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
            'status_pembayaran' => 'required|in:belum_dibayar,sudah_dibayar',
            'tanggal_pembayaran' => 'nullable|date|required_if:status_pembayaran,sudah_dibayar',
        ]);

        // Ambil data peminjaman
        $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);
        
        // Cek apakah sudah ada denda untuk peminjaman ini
        $existingDenda = Denda::where('peminjaman_id', $request->peminjaman_id)->first();
        
        if ($existingDenda) {
            return back()->with('error', 'Denda untuk peminjaman ini sudah ada.')
                ->withInput();
        }

        Denda::create([
            'peminjaman_id' => $request->peminjaman_id,
            'anggota_id' => $peminjaman->anggota_id,
            'jumlah_hari_terlambat' => $request->jumlah_hari_terlambat,
            'jumlah_denda' => $request->jumlah_denda,
            'status_pembayaran' => $request->status_pembayaran,
            'tanggal_pembayaran' => $request->status_pembayaran === 'sudah_dibayar' ? $request->tanggal_pembayaran : null,
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('admin.denda.index')
            ->with('success', 'Data denda berhasil ditambahkan.');
    }

    public function show($id)
    {
        $denda = Denda::with(['peminjaman.detailPeminjaman.buku', 'anggota.kelas', 'anggota.jurusan'])
            ->findOrFail($id);
            
        return view('admin.denda.show', compact('denda'));
    }

    public function edit($id)
    {
        $denda = Denda::with(['peminjaman.detailPeminjaman.buku', 'anggota.kelas', 'anggota.jurusan'])
            ->findOrFail($id);
            
        $peminjamanTerlambat = Peminjaman::with(['anggota.kelas', 'anggota.jurusan', 'detailPeminjaman.buku'])
            ->where('status', 'dipinjam')
            ->where('tanggal_harus_kembali', '<', now())
            ->get();

        $anggota = Anggota::where('status', 'aktif')->get();
        
        return view('admin.denda.edit', compact('denda', 'peminjamanTerlambat', 'anggota'));
    }

    public function update(Request $request, $id)
    {
        $denda = Denda::findOrFail($id);
        
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'jumlah_hari_terlambat' => 'required|integer|min:1',
            'jumlah_denda' => 'required|numeric|min:0',
            'catatan' => 'nullable|string|max:500',
            'status_pembayaran' => 'required|in:belum_dibayar,sudah_dibayar',
            'tanggal_pembayaran' => 'nullable|date|required_if:status_pembayaran,sudah_dibayar',
        ]);

        $denda->update([
            'peminjaman_id' => $request->peminjaman_id,
            'jumlah_hari_terlambat' => $request->jumlah_hari_terlambat,
            'jumlah_denda' => $request->jumlah_denda,
            'status_pembayaran' => $request->status_pembayaran,
            'tanggal_pembayaran' => $request->status_pembayaran === 'sudah_dibayar' ? $request->tanggal_pembayaran : null,
            'catatan' => $request->catatan,
        ]);

        // Update status denda di pengembalian terkait
        $tanggalBayar = $request->status_pembayaran === 'sudah_dibayar' ? $request->tanggal_pembayaran : null;
        $this->syncPengembalianStatus($denda, $request->status_pembayaran, $tanggalBayar);

        return redirect()->route('admin.denda.index')
            ->with('success', 'Data denda berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $denda = Denda::findOrFail($id);
        $denda->delete();
        
        return redirect()->route('admin.denda.index')
            ->with('success', 'Data denda berhasil dihapus.');
    }

    /**
     * Hitung denda otomatis berdasarkan peminjaman
     */
    public function hitungDenda(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
        ]);

        $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);
        
        // Hitung hari terlambat
        $tanggalHarusKembali = Carbon::parse($peminjaman->tanggal_harus_kembali);
        $tanggalSekarang = Carbon::now();
        
        $jumlahHariTerlambat = $tanggalSekarang->diffInDays($tanggalHarusKembali, false);
        
        if ($jumlahHariTerlambat <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Peminjaman belum terlambat'
            ]);
        }

        // Hitung denda (misalnya Rp 1000 per hari)
        $tarifDendaPerHari = 1000; // Bisa disesuaikan dengan kebijakan
        $jumlahDenda = $jumlahHariTerlambat * $tarifDendaPerHari;

        return response()->json([
            'success' => true,
            'data' => [
                'jumlah_hari_terlambat' => $jumlahHariTerlambat,
                'jumlah_denda' => $jumlahDenda,
                'tanggal_harus_kembali' => $peminjaman->tanggal_harus_kembali,
                'anggota' => [
                    'nama' => $peminjaman->anggota->nama_lengkap,
                    'nomor_anggota' => $peminjaman->anggota->nomor_anggota,
                    'kelas' => $peminjaman->anggota->kelas ? $peminjaman->anggota->kelas->nama_kelas : '-',
                ]
            ]
        ]);
    }

    /**
     * Update status pembayaran denda
     */
    public function updateStatusPembayaran(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:belum_dibayar,sudah_dibayar',
            'tanggal_pembayaran' => 'nullable|date|required_if:status_pembayaran,sudah_dibayar',
        ]);

        $denda = Denda::findOrFail($id);

        $denda->update([
            'status_pembayaran' => $request->status_pembayaran,
            'tanggal_pembayaran' => $request->status_pembayaran === 'sudah_dibayar' ? $request->tanggal_pembayaran : null,
        ]);

        // Update status denda di pengembalian terkait
        $tanggalBayar = $request->status_pembayaran === 'sudah_dibayar' ? ($request->tanggal_pembayaran ?? now()) : null;
        $this->syncPengembalianStatus($denda, $request->status_pembayaran, $tanggalBayar);

        return response()->json([
            'success' => true,
            'message' => 'Status pembayaran denda berhasil diperbarui'
        ]);
    }

    /**
     * Cari denda berdasarkan anggota
     */
    public function searchDenda(Request $request)
    {
        $query = Denda::with(['peminjaman.detailPeminjaman.buku', 'anggota.kelas', 'anggota.jurusan']);

        if ($request->anggota) {
            $query->whereHas('anggota', function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->anggota . '%')
                  ->orWhere('nomor_anggota', 'like', '%' . $request->anggota . '%');
            });
        }

        if ($request->status_pembayaran) {
            $query->where('status_pembayaran', $request->status_pembayaran);
        }

        if ($request->tanggal_mulai) {
            $query->whereDate('created_at', '>=', $request->tanggal_mulai);
        }

        if ($request->tanggal_selesai) {
            $query->whereDate('created_at', '<=', $request->tanggal_selesai);
        }

        $denda = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $denda->items(),
            'pagination' => [
                'current_page' => $denda->currentPage(),
                'last_page' => $denda->lastPage(),
                'per_page' => $denda->perPage(),
                'total' => $denda->total()
            ]
        ]);
    }

    /**
     * Bayar lunas semua denda belum bayar milik satu anggota (via AJAX dari scan barcode)
     */
    public function bayarLunasAnggota(Request $request)
    {
        $request->validate(['anggota_id' => 'required|exists:anggota,id']);

        $dendas = Denda::where('anggota_id', $request->anggota_id)
            ->where('status_pembayaran', 'belum_dibayar')
            ->get();

        if ($dendas->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Tidak ada denda yang perlu dibayar.']);
        }

        foreach ($dendas as $denda) {
            $denda->update([
                'status_pembayaran' => 'sudah_dibayar',
                'tanggal_pembayaran' => now(),
            ]);
            $this->syncPengembalianStatus($denda, 'sudah_dibayar', now());
        }

        return response()->json([
            'success' => true,
            'message' => $dendas->count() . ' denda berhasil dilunasi.',
            'count'   => $dendas->count(),
        ]);
    }

    /**
     * Sinkronisasi status denda ke pengembalian terkait.
     * Cari pengembalian via pengembalian_id, fallback ke peminjaman_id.
     */
    private function syncPengembalianStatus(Denda $denda, string $statusDenda, $tanggalPembayaran = null)
    {
        $pengembalian = null;

        // Cari via pengembalian_id langsung
        if ($denda->pengembalian_id) {
            $pengembalian = Pengembalian::find($denda->pengembalian_id);
        }

        // Fallback: cari via peminjaman_id
        if (!$pengembalian && $denda->peminjaman_id) {
            $pengembalian = Pengembalian::where('peminjaman_id', $denda->peminjaman_id)->first();

            // Simpan pengembalian_id ke denda agar next time langsung ketemu
            if ($pengembalian && !$denda->pengembalian_id) {
                $denda->update(['pengembalian_id' => $pengembalian->id]);
            }
        }

        if ($pengembalian) {
            $pengembalian->update([
                'status_denda' => $statusDenda,
                'tanggal_pembayaran_denda' => $tanggalPembayaran,
            ]);
        }
    }
}