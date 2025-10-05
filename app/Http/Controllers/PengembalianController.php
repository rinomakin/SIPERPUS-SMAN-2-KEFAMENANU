<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\DetailPeminjaman;
use App\Models\Anggota;
use App\Models\Buku;
use App\Models\Denda;
use App\Models\Pengembalian;
use App\Models\DetailPengembalian;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PengembalianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Hapus middleware permission untuk method pencarian - akan dicek di method masing-masing jika diperlukan
    }

    public function index(Request $request)
    {
        // Check if user wants to see active loans to return or completed returns
        $viewType = $request->get('view', 'returns'); // 'returns' for completed returns, 'active' for active loans to return
        
        if ($viewType === 'active') {
            // Show active borrowings that haven't been returned yet
            $query = Peminjaman::with(['anggota', 'user', 'detailPeminjaman.buku'])
                ->where('status', 'dipinjam')
                ->orderBy('tanggal_harus_kembali', 'asc');
            
            // Add search functionality for active borrowings
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_peminjaman', 'like', "%{$search}%")
                      ->orWhereHas('anggota', function($q2) use ($search) {
                          $q2->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('nama', 'like', "%{$search}%")
                             ->orWhere('nomor_anggota', 'like', "%{$search}%")
                             ->orWhere('nis', 'like', "%{$search}%")
                             ->orWhere('barcode_anggota', 'like', "%{$search}%");
                      });
                });
            }
            
            $peminjaman = $query->paginate(10);
            return view('admin.pengembalian.index_active', compact('peminjaman'));
        } else {
            // Show completed returns for today (original functionality)
            $query = Pengembalian::with([
                'anggota.kelas', 
                'user', 
                'detailPengembalian.buku.kategoriBuku',
                'peminjaman.detailPeminjaman.buku'
            ])
            ->whereDate('tanggal_pengembalian', today())
            ->orderBy('created_at', 'desc');
            
            // Add search functionality for returns
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nomor_pengembalian', 'like', "%{$search}%")
                      ->orWhereHas('anggota', function($q2) use ($search) {
                          $q2->where('nama_lengkap', 'like', "%{$search}%")
                             ->orWhere('nama', 'like', "%{$search}%")
                             ->orWhere('nomor_anggota', 'like', "%{$search}%")
                             ->orWhere('nis', 'like', "%{$search}%")
                             ->orWhere('barcode_anggota', 'like', "%{$search}%");
                      })
                      ->orWhereHas('detailPengembalian.buku', function($q3) use ($search) {
                          $q3->where('judul_buku', 'like', "%{$search}%")
                             ->orWhere('isbn', 'like', "%{$search}%")
                             ->orWhere('barcode', 'like', "%{$search}%");
                      });
                });
            }
            
            $pengembalian = $query->paginate(10);
                
            return view('admin.pengembalian.index', compact('pengembalian'));
        }
    }

    public function create()
    {
        return view('admin.pengembalian.create');
    }

    public function show($id)
    {
        $pengembalian = Pengembalian::with([
            'anggota.kelas', 
            'user', 
            'detailPengembalian.buku.kategoriBuku',
            'peminjaman.detailPeminjaman.buku'
        ])->findOrFail($id);
        
        return view('admin.pengembalian.show', compact('pengembalian'));
    }

    /**
     * FIXED: Search anggota for pengembalian - method yang diperbaiki
     */
    public function searchAnggota(Request $request)
    {
        try {
            // Log untuk debugging
            Log::info('Search Anggota Request', [
                'query' => $request->get('query', ''),
                'user' => auth()->user() ? auth()->user()->name : 'Not logged in',
                'method' => $request->method(),
                'url' => $request->url()
            ]);

            $query = $request->get('query', '');

            // Build the base query for anggota with active borrowings
            $anggotaQuery = Anggota::with([
                'kelas', 
                'jurusan',
                'peminjaman' => function($q) {
                    $q->where('status', 'dipinjam')
                      ->with(['detailPeminjaman.buku']);
                }
            ])
            ->where('status', 'aktif')
            ->whereHas('peminjaman', function($q) {
                $q->where('status', 'dipinjam');
            });

            // Add search filter if query is provided
            if (strlen($query) >= 2) {
                $anggotaQuery->where(function($q) use ($query) {
                    $q->where('nama', 'LIKE', "%{$query}%")
                      ->orWhere('nama_lengkap', 'LIKE', "%{$query}%")
                      ->orWhere('nis', 'LIKE', "%{$query}%")
                      ->orWhere('nomor_anggota', 'LIKE', "%{$query}%")
                      ->orWhere('barcode_anggota', 'LIKE', "%{$query}%");
                });
            }

            // Get results
            $anggota = $anggotaQuery->limit(50)->get();

            $result = $anggota->map(function($anggota) {
                // Filter peminjaman aktif dengan detail buku
                $peminjamanAktif = $anggota->peminjaman->filter(function($p) {
                    return $p->status === 'dipinjam';
                });
                
                return [
                    'id' => $anggota->id,
                    'nama_lengkap' => $anggota->nama_lengkap ?: $anggota->nama,
                    'nis' => $anggota->nis,
                    'nomor_anggota' => $anggota->nomor_anggota ?: $anggota->nis,
                    'barcode_anggota' => $anggota->barcode_anggota,
                    'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : 'N/A',
                    'jurusan' => $anggota->jurusan ? $anggota->jurusan->nama_jurusan : 'N/A',
                    'jenis_anggota' => $anggota->jenis_anggota ?: 'Siswa',
                    'jumlah_peminjaman_aktif' => $peminjamanAktif->count(),
                    'memiliki_peminjaman_aktif' => $peminjamanAktif->count() > 0,
                    'detail_peminjaman' => $peminjamanAktif->map(function($peminjaman) {
                        return [
                            'id' => $peminjaman->id,
                            'nomor_peminjaman' => $peminjaman->nomor_peminjaman,
                            'tanggal_peminjaman' => $peminjaman->tanggal_peminjaman,
                            'tanggal_harus_kembali' => $peminjaman->tanggal_harus_kembali,
                            'jumlah_buku' => $peminjaman->detailPeminjaman->sum('jumlah'),
                            'buku' => $peminjaman->detailPeminjaman->map(function($detail) {
                                return [
                                    'id' => $detail->id,
                                    'judul' => $detail->buku ? $detail->buku->judul_buku : 'N/A',
                                    'pengarang' => $detail->buku ? $detail->buku->pengarang : 'N/A',
                                    'jumlah' => $detail->jumlah ?: 1
                                ];
                            })
                        ];
                    })
                ];
            });
            
            Log::info('Search Anggota Result', [
                'query' => $query,
                'count' => $result->count(),
                'total_peminjaman_aktif' => $result->sum('jumlah_peminjaman_aktif')
            ]);

            return response()->json([
                'success' => true,
                'data' => $result,
                'debug' => [
                    'query' => $query,
                    'count' => $result->count(),
                    'total_peminjaman_aktif' => $result->sum('jumlah_peminjaman_aktif'),
                    'timestamp' => now()
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error in search anggota:', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'query' => $request->get('query', '')
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'debug' => [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine(),
                    'query' => $request->get('query', '')
                ]
            ], 500);
        }
    }

    /**
     * Get active borrowings for an anggota
     */
    public function getPeminjamanAktif(Request $request)
    {
        $anggotaId = $request->get('anggota_id');
        
        if (!$anggotaId) {
            return response()->json([
                'success' => false,
                'message' => 'ID anggota tidak ditemukan'
            ]);
        }

        try {
            Log::info('Get Peminjaman Aktif', ['anggota_id' => $anggotaId]);

            $peminjaman = Peminjaman::with(['detailPeminjaman.buku.kategoriBuku', 'anggota'])
                ->where('anggota_id', $anggotaId)
                ->where('status', 'dipinjam')
                ->get()
                ->map(function($item) {
                    $today = Carbon::now();
                    $tanggalKembali = Carbon::parse($item->tanggal_harus_kembali);
                    $isLate = $today->gt($tanggalKembali);
                    $daysLate = $isLate ? $today->diffInDays($tanggalKembali) : 0;
                    
                    return [
                        'id' => $item->id,
                        'nomor_peminjaman' => $item->nomor_peminjaman,
                        'tanggal_peminjaman' => $item->tanggal_peminjaman->format('d/m/Y'),
                        'tanggal_harus_kembali' => $item->tanggal_harus_kembali->format('d/m/Y'),
                        'is_late' => $isLate,
                        'days_late' => $daysLate,
                        'catatan' => $item->catatan ?? '',
                        'jumlah_buku' => $item->detailPeminjaman->sum('jumlah'),
                        'detail_peminjaman' => $item->detailPeminjaman->map(function($detail) {
                            return [
                                'id' => $detail->id,
                                'buku_id' => $detail->buku_id,
                                'judul_buku' => $detail->buku ? $detail->buku->judul_buku : 'N/A',
                                'pengarang' => $detail->buku ? $detail->buku->pengarang : 'N/A',
                                'kategori' => $detail->buku && $detail->buku->kategoriBuku ? $detail->buku->kategoriBuku->nama_kategori : 'N/A',
                                'jumlah' => $detail->jumlah ?? 1,
                                'kondisi_kembali' => $detail->kondisi_kembali ?? 'baik'
                            ];
                        })
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $peminjaman,
                'message' => $peminjaman->count() > 0 ? 'Peminjaman aktif ditemukan' : 'Tidak ada peminjaman aktif'
            ]);
        } catch (\Exception $e) {
            Log::error('Error get peminjaman aktif:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Scan barcode anggota untuk pengembalian
     */
    public function scanBarcodeAnggota(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        try {
            Log::info('Scan Barcode Anggota', ['barcode' => $request->barcode]);

            $anggota = Anggota::where('barcode_anggota', $request->barcode)
                              ->where('status', 'aktif')
                              ->with(['kelas', 'jurusan', 'peminjaman' => function($q) {
                                  $q->where('status', 'dipinjam')
                                    ->with(['detailPeminjaman.buku.kategoriBuku']);
                              }])
                              ->first();

            if (!$anggota) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggota dengan barcode tersebut tidak ditemukan atau tidak aktif'
                ], 404);
            }

            // Filter hanya peminjaman aktif
            $peminjamanAktif = $anggota->peminjaman->filter(function($p) {
                return $p->status === 'dipinjam';
            });

            // Jika tidak ada peminjaman aktif
            if ($peminjamanAktif->count() == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggota ditemukan tetapi tidak sedang meminjam buku'
                ]);
            }

            // Format data peminjaman aktif
            $peminjamanFormatted = $peminjamanAktif->map(function($item) {
                $today = Carbon::now();
                $tanggalKembali = Carbon::parse($item->tanggal_harus_kembali);
                $isLate = $today->gt($tanggalKembali);
                $daysLate = $isLate ? $today->diffInDays($tanggalKembali) : 0;
                
                return [
                    'id' => $item->id,
                    'nomor_peminjaman' => $item->nomor_peminjaman,
                    'tanggal_peminjaman' => $item->tanggal_peminjaman->format('d/m/Y'),
                    'tanggal_harus_kembali' => $item->tanggal_harus_kembali->format('d/m/Y'),
                    'is_late' => $isLate,
                    'days_late' => $daysLate,
                    'jumlah_buku' => $item->detailPeminjaman->sum('jumlah'),
                    'catatan' => $item->catatan,
                    'detail_peminjaman' => $item->detailPeminjaman->map(function($detail) {
                        return [
                            'id' => $detail->id,
                            'buku_id' => $detail->buku_id,
                            'judul_buku' => $detail->buku->judul_buku ?? 'N/A',
                            'penulis' => $detail->buku->pengarang ?? 'N/A',
                            'kategori' => $detail->buku->kategoriBuku ? $detail->buku->kategoriBuku->nama_kategori : 'N/A',
                            'jumlah' => $detail->jumlah,
                            'kondisi_kembali' => $detail->kondisi_kembali
                        ];
                    })
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'anggota' => [
                        'id' => $anggota->id,
                        'nama_lengkap' => $anggota->nama_lengkap,
                        'nomor_anggota' => $anggota->nomor_anggota,
                        'barcode_anggota' => $anggota->barcode_anggota,
                        'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : 'N/A',
                        'jenis_anggota' => $anggota->jenis_anggota
                    ],
                    'peminjaman' => $peminjamanFormatted
                ],
                'message' => 'Peminjaman aktif ditemukan'
            ]);

        } catch (\Exception $e) {
            Log::error('Error scan barcode anggota:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process book return
     */
    public function store(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'tanggal_kembali' => 'required|date',
            'jam_kembali' => 'nullable|date_format:H:i',
            'catatan_pengembalian' => 'nullable|string',
            'kondisi_kembali' => 'required|array',
            'kondisi_kembali.*' => 'required|in:baik,sedikit_rusak,rusak,hilang',
            'status_pembayaran_denda' => 'nullable|in:belum_dibayar,sudah_dibayar',
            'tanggal_pembayaran_denda' => 'nullable|date',
            'catatan_pembayaran_denda' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Validasi tambahan untuk pembayaran denda
            if ($request->status_pembayaran_denda === 'sudah_dibayar' && !$request->tanggal_pembayaran_denda) {
                throw new \Exception('Tanggal pembayaran harus diisi jika status sudah dibayar.');
            }
            
            $peminjaman = Peminjaman::with('detailPeminjaman.buku')->findOrFail($request->peminjaman_id);
            
            // Check if already returned
            if ($peminjaman->status === 'dikembalikan') {
                throw new \Exception('Peminjaman ini sudah dikembalikan sebelumnya.');
            }

            // Calculate late fee if any
            $tanggalKembali = Carbon::parse($request->tanggal_kembali);
            $tanggalHarusKembali = Carbon::parse($peminjaman->tanggal_harus_kembali);
            $isLate = $tanggalKembali->gt($tanggalHarusKembali);
            $daysLate = $isLate ? $tanggalKembali->diffInDays($tanggalHarusKembali) : 0;
            
            // Calculate total denda
            $dendaPerHari = 1000; // Rp 1000 per hari
            $totalDenda = $daysLate * $dendaPerHari;
            
            // Create pengembalian record
            $pengembalian = Pengembalian::create([
                'nomor_pengembalian' => Pengembalian::generateNomorPengembalian(),
                'peminjaman_id' => $peminjaman->id,
                'anggota_id' => $peminjaman->anggota_id,
                'user_id' => auth()->id(),
                'tanggal_pengembalian' => $tanggalKembali,
                'jam_pengembalian' => $request->jam_kembali ?? now()->format('H:i'),
                'jumlah_hari_terlambat' => $daysLate,
                'total_denda' => $totalDenda,
                'status_denda' => $totalDenda > 0 ? 'belum_dibayar' : 'tidak_ada',
                'catatan' => $request->catatan_pengembalian,
                'status' => 'selesai'
            ]);

            // Create detail pengembalian and update book stock
            $totalDendaBuku = 0;
            foreach ($peminjaman->detailPeminjaman as $detail) {
                $kondisi = $request->kondisi_kembali[$detail->id] ?? 'baik';
                
                // Calculate denda buku berdasarkan kondisi
                $dendaBuku = 0;
                switch ($kondisi) {
                    case 'sedikit_rusak':
                        $dendaBuku = 5000;
                        break;
                    case 'rusak':
                        $dendaBuku = 25000;
                        break;
                    case 'hilang':
                        $dendaBuku = 100000;
                        break;
                }
                $totalDendaBuku += $dendaBuku;
                
                DetailPengembalian::create([
                    'pengembalian_id' => $pengembalian->id,
                    'buku_id' => $detail->buku_id,
                    'detail_peminjaman_id' => $detail->id,
                    'kondisi_kembali' => $kondisi,
                    'jumlah_dikembalikan' => $detail->jumlah ?? 1,
                    'denda_buku' => $dendaBuku,
                    'catatan_buku' => $this->getCatatanBuku($kondisi)
                ]);

                // Update detail peminjaman
                $detail->update([
                    'kondisi_kembali' => $kondisi
                ]);

                // Return book stock if condition is good or damaged (not lost)
                if ($kondisi !== 'hilang') {
                    $detail->buku->increment('stok_tersedia', $detail->jumlah ?? 1);
                }
            }

            // Update total denda jika ada denda buku
            if ($totalDendaBuku > 0) {
                $pengembalian->update([
                    'total_denda' => $totalDenda + $totalDendaBuku
                ]);
            }

            // Update peminjaman status
            $peminjaman->update([
                'tanggal_kembali' => $tanggalKembali,
                'jam_kembali' => $request->jam_kembali ?? now()->format('H:i'),
                'status' => 'dikembalikan',
                'catatan' => $peminjaman->catatan . ($request->catatan_pengembalian ? "\n\nCatatan Pengembalian: " . $request->catatan_pengembalian : '')
            ]);

            // Create denda record if late
            if ($isLate && $totalDenda > 0) {
                $denda = Denda::create([
                    'peminjaman_id' => $peminjaman->id,
                    'pengembalian_id' => $pengembalian->id,
                    'anggota_id' => $peminjaman->anggota_id,
                    'jumlah_hari_terlambat' => $daysLate,
                    'jumlah_denda' => $totalDenda + $totalDendaBuku,
                    'status_pembayaran' => $request->status_pembayaran_denda ?? 'belum_dibayar',
                    'tanggal_pembayaran' => $request->tanggal_pembayaran_denda,
                    'catatan' => $request->catatan_pembayaran_denda ?? "Keterlambatan pengembalian {$daysLate} hari"
                ]);
                
                // Update status denda di pengembalian berdasarkan input form
                $pengembalian->update([
                    'status_denda' => $request->status_pembayaran_denda ?? 'belum_dibayar',
                    'tanggal_pembayaran_denda' => $request->tanggal_pembayaran_denda
                ]);
            }

            DB::commit();
            
            $message = 'Pengembalian berhasil diproses.';
            if ($isLate) {
                $message .= " Anggota terlambat {$daysLate} hari dan dikenakan denda Rp " . number_format($totalDenda + $totalDendaBuku, 0, ',', '.');
            }
            
            return redirect()->route('pengembalian.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get catatan buku berdasarkan kondisi
     */
    private function getCatatanBuku(string $kondisi): ?string
    {
        return match($kondisi) {
            'baik' => 'Buku dalam kondisi baik',
            'sedikit_rusak' => 'Buku sedikit rusak pada bagian cover',
            'rusak' => 'Buku rusak pada beberapa halaman',
            'hilang' => 'Buku tidak ditemukan',
            default => null
        };
    }

    /**
     * Update status pembayaran denda
     */
    public function updateStatusPembayaranDenda(Request $request, $id)
    {
        $request->validate([
            'status_pembayaran' => 'required|in:belum_dibayar,sudah_dibayar',
            'tanggal_pembayaran' => 'nullable|date',
        ]);

        try {
            $pengembalian = Pengembalian::findOrFail($id);
            
            // Update status denda di pengembalian
            $pengembalian->status_denda = $request->status_pembayaran;
            $pengembalian->tanggal_pembayaran_denda = $request->tanggal_pembayaran;
            $pengembalian->save();

            // Update atau buat record denda
            $denda = Denda::where('peminjaman_id', $pengembalian->peminjaman_id)->first();
            
            if ($denda) {
                // Update denda yang sudah ada
                $denda->status_pembayaran = $request->status_pembayaran;
                $denda->tanggal_pembayaran = $request->tanggal_pembayaran;
                $denda->save();
            } else {
                // Buat denda baru jika belum ada
                Denda::create([
                    'peminjaman_id' => $pengembalian->peminjaman_id,
                    'anggota_id' => $pengembalian->anggota_id,
                    'jumlah_hari_terlambat' => $pengembalian->jumlah_hari_terlambat,
                    'jumlah_denda' => $pengembalian->total_denda,
                    'status_pembayaran' => $request->status_pembayaran,
                    'tanggal_pembayaran' => $request->tanggal_pembayaran,
                    'catatan' => 'Denda dari pengembalian terlambat'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Status pembayaran denda berhasil diperbarui'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Get denda info untuk pengembalian
     */
    public function getDendaInfo($id)
    {
        try {
            $pengembalian = Pengembalian::with(['denda'])->findOrFail($id);
            
            $dendaInfo = [
                'has_denda' => $pengembalian->total_denda > 0,
                'total_denda' => $pengembalian->total_denda,
                'jumlah_hari_terlambat' => $pengembalian->jumlah_hari_terlambat,
                'status_denda' => $pengembalian->status_denda,
                'tanggal_pembayaran_denda' => $pengembalian->tanggal_pembayaran_denda,
                'denda_records' => $pengembalian->denda
            ];

            return response()->json([
                'success' => true,
                'data' => $dendaInfo
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
}
