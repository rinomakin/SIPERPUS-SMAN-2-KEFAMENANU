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
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Denda ini sudah dibayar sebelumnya.']);
            }
            return back()->with('error', 'Denda ini sudah dibayar sebelumnya.');
        }

        $denda->update([
            'status_pembayaran' => 'sudah_dibayar',
            'tanggal_pembayaran' => now(),
        ]);

        // Update status denda di pengembalian terkait
        $this->syncPengembalianStatus($denda, 'sudah_dibayar', now(), $denda->jumlah_denda);

        // Kembalikan stok buku yang hilang
        $this->restoreStockForLostBooks($denda);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Denda berhasil dibayar lunas.']);
        }

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
            'jumlah_hari_terlambat' => 'required|integer|min:0',
            'jumlah_denda' => 'required|numeric|min:0',
            'jumlah_bayar' => 'nullable|numeric|min:0|max:' . ($request->jumlah_denda ?? 0),
            'catatan' => 'nullable|string|max:500',
        ]);

        $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);

        $jumlahDenda = (int) $request->jumlah_denda;
        $jumlahBayar = (int) ($request->jumlah_bayar ?? 0);

        if ($jumlahBayar >= $jumlahDenda) {
            $sisaDenda = 0;
            $status = 'sudah_dibayar';
            $tglBayar = now();
        } elseif ($jumlahBayar > 0) {
            $sisaDenda = $jumlahDenda - $jumlahBayar;
            $status = 'sudah_dibayar';
            $tglBayar = now();
        } else {
            $sisaDenda = $jumlahDenda;
            $status = 'belum_dibayar';
            $tglBayar = null;
        }

        // Cek apakah sudah ada denda untuk peminjaman ini
        $existingDenda = Denda::where('peminjaman_id', $request->peminjaman_id)->first();

        if ($existingDenda) {
            $sisaDenda = max(0, $existingDenda->jumlah_denda - $jumlahBayar);
            $lunas = $sisaDenda == 0;

            if ($jumlahBayar > 0) {
                $asal = $existingDenda->jumlah_denda_asal ?? ($existingDenda->jumlah_denda + $jumlahBayar);
                $ratio = min(1, $jumlahBayar / max($asal, 1));
                $this->restoreStockForLostBooks($existingDenda, $ratio);
            }

            $existingDenda->update([
                'jumlah_denda' => $lunas ? $existingDenda->jumlah_denda : $sisaDenda,
                'status_pembayaran' => $lunas ? 'sudah_dibayar' : 'belum_dibayar',
                'tanggal_pembayaran' => $lunas ? now() : null,
                'catatan' => $request->catatan,
            ]);

            $this->syncPengembalianStatus(
                $existingDenda,
                $lunas ? 'sudah_dibayar' : 'belum_dibayar',
                $lunas ? now() : null,
                $lunas ? $existingDenda->jumlah_denda : $sisaDenda
            );

            return redirect()->route('admin.denda.index')
                ->with('success', 'Pembayaran denda berhasil dicatat. Sisa: Rp ' . number_format($sisaDenda, 0, ',', '.'));
        }

        $dendaBaru = Denda::create([
            'peminjaman_id' => $request->peminjaman_id,
            'anggota_id' => $peminjaman->anggota_id,
            'jumlah_hari_terlambat' => $request->jumlah_hari_terlambat,
            'jumlah_denda' => $status === 'sudah_dibayar' ? $jumlahDenda : $sisaDenda,
            'jumlah_denda_asal' => $jumlahDenda,
            'status_pembayaran' => $status,
            'tanggal_pembayaran' => $tglBayar,
            'catatan' => $request->catatan,
        ]);

        if ($jumlahBayar > 0) {
            $ratio = min(1, $jumlahBayar / max($jumlahDenda, 1));
            $this->restoreStockForLostBooks($dendaBaru, $ratio);
        }

        $this->syncPengembalianStatus($dendaBaru, $status, $tglBayar, $status === 'sudah_dibayar' ? $jumlahDenda : $sisaDenda);

        $msg = 'Data denda berhasil ditambahkan.';
        if ($jumlahBayar > 0) {
            $msg .= ' Dibayar: Rp ' . number_format($jumlahBayar, 0, ',', '.') . ', Sisa: Rp ' . number_format($sisaDenda, 0, ',', '.');
        }

        return redirect()->route('admin.denda.index')
            ->with('success', $msg);
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

        // Active overdue loans
        $peminjamanAktif = Peminjaman::with(['anggota.kelas', 'anggota.jurusan', 'detailPeminjaman.buku'])
            ->where('status', 'dipinjam')
            ->where('tanggal_harus_kembali', '<', now())
            ->get();

        // Returned loans without denda
        $peminjamanKembali = Peminjaman::with(['anggota.kelas', 'anggota.jurusan', 'detailPeminjaman.buku'])
            ->where('status', 'dikembalikan')
            ->where('tanggal_harus_kembali', '<', \DB::raw('tanggal_kembali'))
            ->whereDoesntHave('denda')
            ->get();

        $peminjamanTerlambat = $peminjamanAktif->merge($peminjamanKembali);
        $anggota = Anggota::where('status', 'aktif')->get();
        
        return view('admin.denda.edit', compact('denda', 'peminjamanTerlambat', 'anggota'));
    }

    public function update(Request $request, $id)
    {
        $denda = Denda::findOrFail($id);
        
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'jumlah_hari_terlambat' => 'required|integer|min:0',
            'jumlah_denda' => 'required|numeric|min:0',
            'jumlah_bayar' => 'nullable|numeric|min:0|max:' . ($request->jumlah_denda ?? 0),
            'catatan' => 'nullable|string|max:500',
            'status_pembayaran' => 'required|in:belum_dibayar,sudah_dibayar',
            'tanggal_pembayaran' => 'nullable|date|required_if:status_pembayaran,sudah_dibayar',
        ]);

        $jumlahDenda = (int) $request->jumlah_denda;
        $jumlahBayar = (int) ($request->jumlah_bayar ?? 0);

        if ($jumlahBayar >= $jumlahDenda) {
            $sisaDenda = 0;
            $status = 'sudah_dibayar';
            $tglBayar = $request->tanggal_pembayaran ?? now();
        } else {
            $sisaDenda = $jumlahDenda - $jumlahBayar;
            $status = $request->status_pembayaran;
            $tglBayar = $status === 'sudah_dibayar' ? $request->tanggal_pembayaran : null;
        }

        $denda->update([
            'peminjaman_id' => $request->peminjaman_id,
            'jumlah_hari_terlambat' => $request->jumlah_hari_terlambat,
            'jumlah_denda' => $status === 'sudah_dibayar' ? $jumlahDenda : $sisaDenda,
            'status_pembayaran' => $status,
            'tanggal_pembayaran' => $tglBayar,
            'catatan' => $request->catatan,
        ]);

        $this->syncPengembalianStatus($denda, $status, $tglBayar, $status === 'sudah_dibayar' ? $jumlahDenda : $sisaDenda);

        if ($status === 'sudah_dibayar') {
            $this->restoreStockForLostBooks($denda);
        }

        $msg = 'Data denda berhasil diperbarui.';
        if ($jumlahBayar > 0) {
            $msg .= ' Dibayar: Rp ' . number_format($jumlahBayar, 0, ',', '.') . ', Sisa: Rp ' . number_format($sisaDenda, 0, ',', '.');
        }

        return redirect()->route('admin.denda.index')
            ->with('success', $msg);
    }

    public function destroy($id)
    {
        $denda = Denda::findOrFail($id);
        $denda->delete();
        
        return redirect()->route('admin.denda.index')
            ->with('success', 'Data denda berhasil dihapus.');
    }

    /**
     * Cari anggota dengan peminjaman terlambat (untuk form tambah denda)
     */
    public function searchPeminjaman(Request $request)
    {
        $query = $request->query('query');

        if (!$query || strlen($query) < 1) {
            return response()->json(['data' => []]);
        }

        $anggotas = Anggota::where(function ($q) use ($query) {
            $q->where('nama_lengkap', 'like', '%' . $query . '%')
              ->orWhere('nomor_anggota', 'like', '%' . $query . '%')
              ->orWhere('barcode_anggota', 'like', '%' . $query . '%');
        })->where('status', 'aktif')->get();

        $result = [];
        foreach ($anggotas as $anggota) {
            $defaultFoto = $anggota->jenis_kelamin == 'Laki-laki'
                ? asset('images/template_foto_laki_laki.jpg')
                : asset('images/teplate_foto_perpempuan.jpg');

            $foto = $anggota->foto
                ? asset('storage/anggota/' . $anggota->foto)
                : $defaultFoto;

            $anggotaData = [
                'id' => $anggota->id,
                'nama_lengkap' => $anggota->nama_lengkap,
                'nomor_anggota' => $anggota->nomor_anggota,
                'foto' => $foto,
                'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : '-',
                'jenis_kelamin' => $anggota->jenis_kelamin,
            ];

            // 1. Returned loans that don't have a denda record yet
            $peminjamanKembaliTanpaDenda = Peminjaman::with(['detailPeminjaman.buku'])
                ->where('anggota_id', $anggota->id)
                ->where('status', 'dikembalikan')
                ->where('tanggal_harus_kembali', '<', \DB::raw('tanggal_kembali'))
                ->whereDoesntHave('denda')
                ->get();

            if ($peminjamanKembaliTanpaDenda->count() > 0) {
                $result[] = [
                    'anggota' => $anggotaData,
                    'type' => 'returned_no_denda',
                    'peminjaman' => $peminjamanKembaliTanpaDenda->map(function ($p) {
                        $tanggalHarusKembali = \Carbon\Carbon::parse($p->tanggal_harus_kembali);
                        $tanggalKembali = \Carbon\Carbon::parse($p->tanggal_kembali);
                        $hariTerlambat = $tanggalKembali->diffInDays($tanggalHarusKembali, false);
                        return [
                            'id' => $p->id,
                            'nomor_peminjaman' => $p->nomor_peminjaman,
                            'tanggal_pinjam' => $p->tanggal_pinjam,
                            'tanggal_harus_kembali' => $p->tanggal_harus_kembali,
                            'tanggal_kembali' => $p->tanggal_kembali,
                            'hari_terlambat' => max(0, (int) $hariTerlambat),
                            'denda' => max(0, (int) $hariTerlambat) * 1000,
                            'buku' => $p->detailPeminjaman->map(function ($dp) {
                                return [
                                    'judul' => $dp->buku->judul ?? '-',
                                    'kode_buku' => $dp->buku->kode_buku ?? '-',
                                ];
                            }),
                        ];
                    }),
                ];
            }

            // 2. Existing unpaid fines (belum_dibayar)
            $dendaBelumBayar = Denda::with(['peminjaman.detailPeminjaman.buku'])
                ->where('anggota_id', $anggota->id)
                ->where('status_pembayaran', 'belum_dibayar')
                ->get();

            if ($dendaBelumBayar->count() > 0) {
                $result[] = [
                    'anggota' => $anggotaData,
                    'type' => 'unpaid_denda',
                    'denda' => $dendaBelumBayar->map(function ($d) {
                        return [
                            'id' => $d->id,
                            'peminjaman_id' => $d->peminjaman_id,
                            'jumlah_hari_terlambat' => $d->jumlah_hari_terlambat,
                            'jumlah_denda' => $d->jumlah_denda,
                            'tanggal_pembayaran' => $d->tanggal_pembayaran,
                            'buku' => $d->peminjaman && $d->peminjaman->detailPeminjaman
                                ? $d->peminjaman->detailPeminjaman->map(function ($dp) {
                                    return [
                                        'judul' => $dp->buku->judul ?? '-',
                                        'kode_buku' => $dp->buku->kode_buku ?? '-',
                                    ];
                                })
                                : [],
                        ];
                    }),
                ];
            }
        }

        return response()->json(['data' => $result]);
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
        $this->syncPengembalianStatus($denda, $request->status_pembayaran, $tanggalBayar, $denda->jumlah_denda);

        if ($request->status_pembayaran === 'sudah_dibayar') {
            $this->restoreStockForLostBooks($denda);
        }

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
            $this->syncPengembalianStatus($denda, 'sudah_dibayar', now(), $denda->jumlah_denda);
            $this->restoreStockForLostBooks($denda);
        }

        return response()->json([
            'success' => true,
            'message' => $dendas->count() . ' denda berhasil dilunasi.',
            'count'   => $dendas->count(),
        ]);
    }

    /**
     * Pulihkan stok buku hilang untuk semua denda yang sudah dibayar lunas.
     * Digunakan untuk retroactive fix data lama.
     */
    public function pulihkanStokBukuHilang()
    {
        $totalBuku = 0;
        $totalDenda = 0;

        $dendas = Denda::where('status_pembayaran', 'sudah_dibayar')->get();

        foreach ($dendas as $denda) {
            $pengembalian = null;

            if ($denda->pengembalian_id) {
                $pengembalian = Pengembalian::with('detailPengembalian.buku')->find($denda->pengembalian_id);
            }

            if (!$pengembalian && $denda->peminjaman_id) {
                $pengembalian = Pengembalian::with('detailPengembalian.buku')
                    ->where('peminjaman_id', $denda->peminjaman_id)
                    ->first();
            }

            if ($pengembalian) {
                foreach ($pengembalian->detailPengembalian as $detail) {
                    if ($detail->kondisi_kembali === 'hilang' && $detail->jumlah_hilang > 0 && $detail->buku) {
                        $detail->buku->increment('stok_tersedia', $detail->jumlah_hilang);
                        $totalBuku += $detail->jumlah_hilang;
                        $totalDenda++;
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Stok berhasil dipulihkan: {$totalBuku} buku dari {$totalDenda} transaksi denda."
        ]);
    }

    /**
     * Restore stok buku yang hilang ketika denda dibayar.
     * $ratio: proporsi dari total denda asal yang dibayar (default 1.0 = lunas).
     * Stok dipulihkan secara proporsional dan dicatat di denda.stok_restored.
     */
    private function restoreStockForLostBooks(Denda $denda, float $ratio = 1.0)
    {
        $pengembalian = null;

        if ($denda->pengembalian_id) {
            $pengembalian = Pengembalian::with('detailPengembalian.buku')->find($denda->pengembalian_id);
        }

        if (!$pengembalian && $denda->peminjaman_id) {
            $pengembalian = Pengembalian::with('detailPengembalian.buku')
                ->where('peminjaman_id', $denda->peminjaman_id)
                ->first();
        }

        if (!$pengembalian) return;

        $totalHilang = 0;
        foreach ($pengembalian->detailPengembalian as $detail) {
            if ($detail->kondisi_kembali === 'hilang') {
                $totalHilang += $detail->jumlah_hilang;
            }
        }

        if ($totalHilang <= 0) return;

        $targetRestored = (int) ceil($totalHilang * max(0, min(1, $ratio)));
        $sudahRestored = (int) ($denda->stok_restored ?? 0);
        $restoreNow = max(0, $targetRestored - $sudahRestored);

        if ($restoreNow <= 0) return;

        $remaining = $restoreNow;
        foreach ($pengembalian->detailPengembalian as $detail) {
            if ($detail->kondisi_kembali === 'hilang' && $detail->jumlah_hilang > 0 && $detail->buku && $remaining > 0) {
                $proporsi = $detail->jumlah_hilang / $totalHilang;
                $qty = (int) floor($restoreNow * $proporsi);
                $qty = min($qty, $remaining, $detail->jumlah_hilang);
                if ($qty > 0) {
                    $detail->buku->increment('stok_tersedia', $qty);
                    $remaining -= $qty;
                }
            }
        }

        // Sisa karena pembulatan floor, distribusi 1 per detail
        if ($remaining > 0) {
            foreach ($pengembalian->detailPengembalian as $detail) {
                if ($detail->kondisi_kembali === 'hilang' && $detail->buku && $remaining > 0) {
                    $detail->buku->increment('stok_tersedia', 1);
                    $remaining--;
                }
            }
        }

        $denda->update(['stok_restored' => $targetRestored]);
    }

    /**
     * Sinkronisasi status denda ke pengembalian terkait.
     * Cari pengembalian via pengembalian_id, fallback ke peminjaman_id.
     */
    private function syncPengembalianStatus(Denda $denda, string $statusDenda, $tanggalPembayaran = null, $totalDenda = null)
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
            $updateData = [
                'status_denda' => $statusDenda,
                'tanggal_pembayaran_denda' => $tanggalPembayaran,
            ];
            if ($totalDenda !== null) {
                $updateData['total_denda'] = $totalDenda;
            }
            $pengembalian->update($updateData);
        }
    }
}