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
use Yajra\DataTables\Facades\DataTables;

class PengembalianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        // Hapus middleware permission untuk method pencarian - akan dicek di method masing-masing jika diperlukan
        $this->middleware('permission:pengembalian.create')->only(['create', 'store']);
        $this->middleware('permission:pengembalian.edit')->only(['edit', 'update']);
        $this->middleware('permission:pengembalian.delete')->only(['destroy']);
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
                             ->orWhere('nomor_anggota', 'like', "%{$search}%")
                             ->orWhere('barcode_anggota', 'like', "%{$search}%");
                      });
                });
            }

            $peminjaman = $query->paginate(10);
            return view('admin.pengembalian.index_active', compact('peminjaman'));
        }

        // Pass summary data directly to the view (no extra AJAX call needed)
        $summaryData = $this->getTodaySummaryData();
        return view('admin.pengembalian.index', compact('summaryData'));
    }

    /**
     * Dedicated DataTables JSON endpoint — always returns JSON, no AJAX detection needed.
     */
    public function getData(Request $request)
    {
        $query = Pengembalian::with([
            'anggota',
            'user',
            'detailPengembalian'
        ]);

        // Default: hanya tampilkan data pengembalian hari ini (range query for index)
        $query->where('tanggal_pengembalian', '>=', Carbon::today()->startOfDay())
              ->where('tanggal_pengembalian', '<=', Carbon::today()->endOfDay());

        // Apply filters from request
        if ($request->filled('filter_status')) {
            if ($request->filter_status === 'tepat_waktu') {
                $query->where('jumlah_hari_terlambat', '<=', 0);
            } elseif ($request->filter_status === 'terlambat') {
                $query->where('jumlah_hari_terlambat', '>', 0);
            }
        }

        if ($request->filled('filter_status_denda')) {
            $query->where('status_denda', $request->filter_status_denda);
        }

        // Pencarian custom (nomor pengembalian atau nama anggota)
        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where(function($q) use ($keyword) {
                $q->where('nomor_pengembalian', 'like', "%{$keyword}%")
                  ->orWhereHas('anggota', function($q2) use ($keyword) {
                      $q2->where('nama_lengkap', 'like', "%{$keyword}%")
                         ->orWhere('nomor_anggota', 'like', "%{$keyword}%");
                  });
            });
        }

        $summaryData = $this->getTodaySummaryData();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('nomor_badge', function($row) {
                return '<span class="nomor-badge"><i class="fas fa-hashtag" style="font-size:9px;opacity:.6"></i>' . e($row->nomor_pengembalian) . '</span>';
            })
            ->addColumn('anggota_info', function($row) {
                if ($row->anggota) {
                    $fotoHtml = '';
                    if ($row->anggota->foto) {
                        $fotoHtml = '<img src="' . asset('storage/anggota/' . $row->anggota->foto) . '" alt="" class="w-8 h-8 rounded-lg object-cover shadow-sm" onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\'">';
                    }
                    $fotoHtml .= '<div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-500 flex items-center justify-center text-white text-xs font-bold shadow-sm"' . ($row->anggota->foto ? ' style="display:none"' : '') . '>' . strtoupper(substr($row->anggota->nama_lengkap, 0, 1)) . '</div>';
                    return '<div class="flex items-center gap-2.5">'
                                . '<div class="flex-shrink-0">' . $fotoHtml . '</div>'
                                . '<div>'
                                    . '<div class="text-xs font-semibold text-gray-900">' . e($row->anggota->nama_lengkap) . '</div>
                                </div>
                            </div>';
                }
                return '<span class="text-gray-300 text-xs">N/A</span>';
            })
            ->addColumn('jumlah_badge', function($row) {
                $total = $row->detailPengembalian ? $row->detailPengembalian->sum('jumlah_dikembalikan') : 0;
                return '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#7c3aed;font-size:12px;font-weight:700;">' . $total . '</span>';
            })
            ->addColumn('tanggal_info', function($row) {
                $html = '<div class="text-xs font-medium text-gray-900">';
                if ($row->tanggal_pengembalian) {
                    $html .= '<i class="far fa-calendar-alt mr-1 text-gray-400"></i>' . $row->tanggal_pengembalian->format('d M Y');
                } else {
                    $html .= 'N/A';
                }
                $html .= '</div>';
                if ($row->jam_pengembalian) {
                    $jam = $row->jam_pengembalian instanceof \Carbon\Carbon
                        ? $row->jam_pengembalian->format('H:i')
                        : substr((string) $row->jam_pengembalian, 0, 5);
                    $html .= '<div class="text-[11px] text-gray-400 mt-0.5"><i class="far fa-clock mr-1"></i>' . e($jam) . '</div>';
                }
                return $html;
            })
            ->addColumn('status_badge', function($row) {
                if ($row->jumlah_hari_terlambat > 0) {
                    return '<span class="badge-status badge-terlambat"><span class="badge-dot red"></span>Terlambat ' . $row->jumlah_hari_terlambat . ' hari</span>';
                }
                return '<span class="badge-status badge-tepat"><span class="badge-dot green"></span>Tepat Waktu</span>';
            })
            ->addColumn('denda_info', function($row) {
                if ($row->total_denda > 0) {
                    $isPaid = $row->status_denda === 'sudah_dibayar';
                    $cardClass = $isPaid ? 'denda-card paid' : 'denda-card has-denda';
                    $amountClass = $isPaid ? 'denda-amount green' : 'denda-amount red';
                    $chipClass = $isPaid ? 'denda-status-chip lunas' : 'denda-status-chip belum';
                    $chipIcon = $isPaid ? 'fa-check-double' : 'fa-clock';
                    $chipText = $isPaid ? 'Lunas' : 'Belum Bayar';

                    return '<div class="' . $cardClass . '">'
                         . '<div class="' . $amountClass . '"><i class="fas fa-coins" style="font-size:11px;opacity:.7;margin-right:3px"></i>Rp ' . number_format($row->total_denda, 0, ',', '.') . '</div>'
                         . '<span class="' . $chipClass . '"><i class="fas ' . $chipIcon . '" style="font-size:8px"></i>' . $chipText . '</span>'
                         . '</div>';
                }
                return '<span class="denda-badge no-denda"><i class="fas fa-check-circle" style="font-size:11px"></i>Tidak ada denda</span>';
            })
            ->addColumn('action', function($row) {
                if (auth()->user()->hasPermission('pengembalian.show') || auth()->user()->isAdmin()) {
                    return '<div class="flex items-center justify-center gap-1.5"><a href="' . route('pengembalian.show', $row->id) . '" class="action-btn view" title="Detail"><i class="fas fa-eye"></i></a></div>';
                }
                return '';
            })
            ->addColumn('petugas_info', function($row) {
                $name = e($row->user->name ?? '-');
                return '<span class="text-xs font-medium text-gray-700">' . $name . '</span>';
            })
            ->rawColumns(['nomor_badge', 'anggota_info', 'jumlah_badge', 'tanggal_info', 'status_badge', 'denda_info', 'petugas_info', 'action'])
            ->with('summary', $summaryData)
            ->make(true);
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

    public function edit($id)
    {
        $pengembalian = Pengembalian::with([
            'anggota.kelas',
            'user',
            'detailPengembalian.buku.kategoriBuku',
            'detailPengembalian.detailPeminjaman',
            'peminjaman.detailPeminjaman.buku',
            'denda'
        ])->findOrFail($id);

        return view('admin.pengembalian.edit', compact('pengembalian'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'tanggal_pengembalian' => 'required|date',
            'jam_pengembalian' => 'nullable|date_format:H:i',
            'catatan_pengembalian' => 'nullable|string',
            'kondisi_kembali' => 'required|array',
            'kondisi_kembali.*' => 'required|in:baik,sedikit_rusak,rusak,hilang',
            'jumlah_dikembalikan' => 'nullable|array',
            'jumlah_dikembalikan.*' => 'nullable|integer|min:1',
            'catatan_buku' => 'nullable|array',
            'catatan_buku.*' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $pengembalian = Pengembalian::with([
                'detailPengembalian.buku',
                'detailPengembalian.detailPeminjaman',
                'peminjaman.detailPeminjaman'
            ])->findOrFail($id);

            $pengembalian->update([
                'tanggal_pengembalian' => $request->tanggal_pengembalian,
                'jam_pengembalian' => $request->jam_pengembalian ?? $pengembalian->jam_pengembalian,
                'catatan' => $request->catatan_pengembalian,
            ]);

            $totalDenda = 0;
            $hasLate = false;
            $maxDaysLate = 0;

            foreach ($pengembalian->detailPengembalian as $detail) {
                $kondisi = $request->kondisi_kembali[$detail->id] ?? 'baik';
                $jumlahDikembalikan = (int) ($request->jumlah_dikembalikan[$detail->id] ?? $detail->jumlah_dikembalikan);
                $jmlHilang = (int) ($request->jumlah_hilang[$detail->id] ?? $detail->jumlah_hilang ?? 0);
                $catatanBuku = $request->catatan_buku[$detail->id] ?? null;

                $dendaKondisi = match ($kondisi) {
                    'sedikit_rusak' => 5000,
                    'rusak' => 25000,
                    'hilang' => 100000,
                    default => 0,
                };

                // Recalculate late fee per-book
                $tanggalKembali = Carbon::parse($request->tanggal_pengembalian);
                $dueDateBook = $detail->detailPeminjaman?->tanggal_harus_kembali
                    ? Carbon::parse($detail->detailPeminjaman->tanggal_harus_kembali)
                    : Carbon::parse($pengembalian->peminjaman->tanggal_harus_kembali);

                $isLateBook = $tanggalKembali->gt($dueDateBook);
                $daysLateBook = $isLateBook ? (int) $tanggalKembali->diffInDays($dueDateBook) : 0;
                $dendaLate = $daysLateBook * 1000;

                $subTotal = $dendaKondisi + $dendaLate;
                $totalDenda += $subTotal;
                if ($daysLateBook > 0) $hasLate = true;
                if ($daysLateBook > $maxDaysLate) $maxDaysLate = $daysLateBook;

                $oldKondisi = $detail->kondisi_kembali;
                $oldJumlah = $detail->jumlah_dikembalikan;
                $oldJmlHilang = $detail->jumlah_hilang ?? 0;

                $detail->update([
                    'kondisi_kembali' => $kondisi,
                    'jumlah_dikembalikan' => $jumlahDikembalikan,
                    'jumlah_hilang' => $jmlHilang,
                    'denda_buku' => $subTotal,
                    'catatan_buku' => $catatanBuku ?? $this->getCatatanBuku($kondisi),
                ]);

                // Sync stock: revert old stock, apply new stock
                if ($detail->buku) {
                    $oldNonHilang = $oldJumlah - ($oldKondisi === 'hilang' ? $oldJmlHilang : 0);
                    $newNonHilang = $jumlahDikembalikan - ($kondisi === 'hilang' ? $jmlHilang : 0);
                    $adjustment = $newNonHilang - $oldNonHilang;
                    if ($adjustment !== 0) {
                        $detail->buku->increment('stok_tersedia', $adjustment);
                    }
                }
            }

            // Update pengembalian totals
            $statusDenda = $pengembalian->status_denda;
            if ($totalDenda <= 0) $statusDenda = 'tidak_ada';

            $tanggalPembayaran = $totalDenda > 0 ? now() : $pengembalian->tanggal_pembayaran_denda;

            $pengembalian->update([
                'jumlah_hari_terlambat' => $maxDaysLate,
                'total_denda' => $totalDenda,
                'status_denda' => $statusDenda,
                'tanggal_pembayaran_denda' => $tanggalPembayaran,
            ]);

            // Sync denda record
            $denda = Denda::where('pengembalian_id', $pengembalian->id)->first();
            if ($totalDenda > 0) {
                $catatanDenda = $maxDaysLate > 0
                    ? "Keterlambatan pengembalian {$maxDaysLate} hari"
                    : "Denda kerusakan/kehilangan buku";
                if ($denda) {
                    $denda->update([
                        'jumlah_hari_terlambat' => $maxDaysLate,
                        'jumlah_denda' => $totalDenda,
                        'status_pembayaran' => 'sudah_dibayar',
                        'tanggal_pembayaran' => now(),
                        'catatan' => $catatanDenda,
                    ]);
                } else {
                    Denda::create([
                        'peminjaman_id' => $pengembalian->peminjaman_id,
                        'pengembalian_id' => $pengembalian->id,
                        'anggota_id' => $pengembalian->anggota_id,
                        'jumlah_hari_terlambat' => $maxDaysLate,
                        'jumlah_denda' => $totalDenda,
                        'jumlah_denda_asal' => $totalDenda,
                        'status_pembayaran' => 'sudah_dibayar',
                        'tanggal_pembayaran' => now(),
                        'catatan' => $catatanDenda,
                    ]);
                }
            } elseif ($denda) {
                $denda->delete();
            }

            DB::commit();

            return redirect()->route('pengembalian.index')
                ->with('success', 'Pengembalian berhasil diperbarui.');

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('pengembalian.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
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
                      ->with(['detailPeminjaman.buku', 'detailPeminjaman.detailPengembalian']);
                }
            ])
            ->where('status', 'aktif')
            ->whereHas('peminjaman', function($q) {
                $q->where('status', 'dipinjam');
            });

            // Add search filter if query is provided
            if (strlen($query) >= 2) {
                $anggotaQuery->where(function($q) use ($query) {
                    $q->where('nama_lengkap', 'LIKE', "%{$query}%")
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
                    'nis' => $anggota->nomor_anggota ?? 'N/A',
                    'nomor_anggota' => $anggota->nomor_anggota,
                    'foto' => $anggota->foto,
                    'barcode_anggota' => $anggota->barcode_anggota,
                    'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : 'N/A',
                    'jurusan' => $anggota->jurusan ? $anggota->jurusan->nama_jurusan : 'N/A',
                    'jenis_anggota' => $anggota->jenis_anggota ?: 'Siswa',
                    'jumlah_peminjaman_aktif' => $peminjamanAktif->count(),
                    'memiliki_peminjaman_aktif' => $peminjamanAktif->count() > 0,
                    'detail_peminjaman' => $peminjamanAktif->map(function($peminjaman) {
                        $today = Carbon::now();
                        $tanggalKembali = Carbon::parse($peminjaman->tanggal_harus_kembali);
                        $isLate = $today->gt($tanggalKembali);
                        $daysLate = $isLate ? $today->diffInDays($tanggalKembali) : 0;

                        // Hanya buku yang belum dikembalikan seluruhnya
                        $detailBelumKembali = $peminjaman->detailPeminjaman->filter(function($d) {
                            $returned = $d->detailPengembalian->sum('jumlah_dikembalikan');
                            return $returned < ($d->jumlah ?? 1);
                        });

                        return [
                            'id' => $peminjaman->id,
                            'nomor_peminjaman' => $peminjaman->nomor_peminjaman,
                            'tanggal_peminjaman' => $peminjaman->tanggal_peminjaman,
                            'tanggal_harus_kembali' => $peminjaman->tanggal_harus_kembali,
                            'tanggal_harus_kembali_raw' => $peminjaman->tanggal_harus_kembali,
                            'is_late' => $isLate,
                            'days_late' => $daysLate,
                            'jumlah_buku' => $detailBelumKembali->sum(function($d) {
                                return ($d->jumlah ?? 1) - $d->detailPengembalian->sum('jumlah_dikembalikan');
                            }),
                            'buku' => $detailBelumKembali->map(function($detail) use ($peminjaman) {
                                $remainingJumlah = ($detail->jumlah ?? 1) - $detail->detailPengembalian->sum('jumlah_dikembalikan');
                                return [
                                    'id' => $detail->id,
                                    'judul' => $detail->buku ? $detail->buku->judul_buku : 'N/A',
                                    'pengarang' => $detail->buku ? $detail->buku->pengarang : 'N/A',
                                    'jumlah' => max($remainingJumlah, 1),
                                    'tanggal_harus_kembali' => $detail->tanggal_harus_kembali
                                        ? Carbon::parse($detail->tanggal_harus_kembali)->format('d/m/Y')
                                        : Carbon::parse($peminjaman->tanggal_harus_kembali)->format('d/m/Y'),
                                    'tanggal_harus_kembali_raw' => $detail->tanggal_harus_kembali
                                        ?? $peminjaman->tanggal_harus_kembali,
                                    'jam_kembali' => $detail->jam_kembali ?? $peminjaman->jam_kembali,
                                ];
                            })->values()
                        ];
                    })->filter(fn($p) => count($p['buku']) > 0)->values()
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

            $peminjaman = Peminjaman::with(['detailPeminjaman.buku.kategoriBuku', 'detailPeminjaman.detailPengembalian', 'anggota'])
                ->where('anggota_id', $anggotaId)
                ->where('status', 'dipinjam')
                ->get()
                ->map(function($item) {
                    $today = Carbon::now();
                    $tanggalKembali = Carbon::parse($item->tanggal_harus_kembali);
                    $isLate = $today->gt($tanggalKembali);
                    $daysLate = $isLate ? $today->diffInDays($tanggalKembali) : 0;

                    // Hanya tampilkan buku yang belum dikembalikan seluruhnya
                    $detailBelumKembali = $item->detailPeminjaman->filter(function($d) {
                        $returned = $d->detailPengembalian->sum('jumlah_dikembalikan');
                        return $returned < ($d->jumlah ?? 1);
                    });

                    return [
                        'id' => $item->id,
                        'nomor_peminjaman' => $item->nomor_peminjaman,
                        'tanggal_peminjaman' => $item->tanggal_peminjaman->format('d/m/Y'),
                        'tanggal_harus_kembali' => $item->tanggal_harus_kembali->format('d/m/Y'),
                        'tanggal_harus_kembali_raw' => $item->tanggal_harus_kembali,
                        'is_late' => $isLate,
                        'days_late' => $daysLate,
                        'catatan' => $item->catatan ?? '',
                        'jumlah_buku' => $detailBelumKembali->sum(function($d) {
                            return ($d->jumlah ?? 1) - $d->detailPengembalian->sum('jumlah_dikembalikan');
                        }),
                        'detail_peminjaman' => $detailBelumKembali->map(function($detail) use ($item) {
                            $remainingJumlah = ($detail->jumlah ?? 1) - $detail->detailPengembalian->sum('jumlah_dikembalikan');
                            return [
                                'id' => $detail->id,
                                'buku_id' => $detail->buku_id,
                                'judul_buku' => $detail->buku ? $detail->buku->judul_buku : 'N/A',
                                'jumlah' => max($remainingJumlah, 1),
                                'kondisi_kembali' => $detail->kondisi_kembali ?? 'baik',
                                'tanggal_harus_kembali' => $detail->tanggal_harus_kembali
                                    ? Carbon::parse($detail->tanggal_harus_kembali)->format('d/m/Y')
                                    : Carbon::parse($item->tanggal_harus_kembali)->format('d/m/Y'),
                                'tanggal_harus_kembali_raw' => $detail->tanggal_harus_kembali
                                    ?? $item->tanggal_harus_kembali,
                                'jam_kembali' => $detail->jam_kembali ?? $item->jam_kembali,
                            ];
                        })->values()
                    ];
                })
                ->filter(fn($p) => count($p['detail_peminjaman']) > 0); // sembunyikan peminjaman yang semua bukunya sudah kembali

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

            // Reload with detailPengembalian to filter returned books
            $peminjamanAktif->load('detailPeminjaman.detailPengembalian');

            // Format data peminjaman aktif
            $peminjamanFormatted = $peminjamanAktif->map(function($item) {
                $today = Carbon::now();
                $tanggalKembali = Carbon::parse($item->tanggal_harus_kembali);
                $isLate = $today->gt($tanggalKembali);
                $daysLate = $isLate ? $today->diffInDays($tanggalKembali) : 0;

                // Hanya tampilkan buku yang belum dikembalikan seluruhnya
                $detailBelumKembali = $item->detailPeminjaman->filter(function($d) {
                    $returned = $d->detailPengembalian->sum('jumlah_dikembalikan');
                    return $returned < ($d->jumlah ?? 1);
                });

                return [
                    'id' => $item->id,
                    'nomor_peminjaman' => $item->nomor_peminjaman,
                    'tanggal_peminjaman' => $item->tanggal_peminjaman->format('d/m/Y'),
                    'tanggal_harus_kembali' => $item->tanggal_harus_kembali->format('d/m/Y'),
                    'tanggal_harus_kembali_raw' => $item->tanggal_harus_kembali,
                    'is_late' => $isLate,
                    'days_late' => $daysLate,
                    'jumlah_buku' => $detailBelumKembali->sum(function($d) {
                        return ($d->jumlah ?? 1) - $d->detailPengembalian->sum('jumlah_dikembalikan');
                    }),
                    'catatan' => $item->catatan,
                    'detail_peminjaman' => $detailBelumKembali->map(function($detail) use ($item) {
                        $remainingJumlah = ($detail->jumlah ?? 1) - $detail->detailPengembalian->sum('jumlah_dikembalikan');
                        return [
                            'id' => $detail->id,
                            'buku_id' => $detail->buku_id,
                            'judul_buku' => $detail->buku->judul_buku ?? 'N/A',
                            'jumlah' => max($remainingJumlah, 1),
                            'kondisi_kembali' => $detail->kondisi_kembali,
                            'tanggal_harus_kembali' => $detail->tanggal_harus_kembali
                                ? Carbon::parse($detail->tanggal_harus_kembali)->format('d/m/Y')
                                : Carbon::parse($item->tanggal_harus_kembali)->format('d/m/Y'),
                            'tanggal_harus_kembali_raw' => $detail->tanggal_harus_kembali
                                ?? $item->tanggal_harus_kembali,
                            'jam_kembali' => $detail->jam_kembali ?? $item->jam_kembali,
                        ];
                    })->values()
                ];
            })->filter(fn($p) => count($p['detail_peminjaman']) > 0)->values();

            return response()->json([
                'success' => true,
                'data' => [
                    'anggota' => [
                        'id' => $anggota->id,
                        'nama_lengkap' => $anggota->nama_lengkap,
                        'nomor_anggota' => $anggota->nomor_anggota,
                        'foto' => $anggota->foto,
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
            'tanggal_kembali' => 'nullable|date',
            'jam_kembali' => 'nullable|date_format:H:i',
            'catatan_pengembalian' => 'nullable|string',
            'selected_detail_ids' => 'nullable|string',
            'jumlah_dikembalikan' => 'nullable|array',
            'jumlah_dikembalikan.*' => 'nullable|integer|min:1',
        ]);

        DB::beginTransaction();
        try {

            $peminjaman = Peminjaman::with('detailPeminjaman.buku')->findOrFail($request->peminjaman_id);

            // Check if already returned
            if ($peminjaman->status === 'dikembalikan') {
                throw new \Exception('Peminjaman ini sudah dikembalikan sebelumnya.');
            }

            // Determine which detail_peminjaman to process (partial or all)
            $selectedDetailIds = null;
            if ($request->filled('selected_detail_ids')) {
                $selectedDetailIds = json_decode($request->selected_detail_ids, true);
            }

            // Filter detail peminjaman based on selection
            $detailsToProcess = $peminjaman->detailPeminjaman;
            if ($selectedDetailIds && is_array($selectedDetailIds) && count($selectedDetailIds) > 0) {
                $detailsToProcess = $detailsToProcess->filter(function($detail) use ($selectedDetailIds) {
                    return in_array($detail->id, $selectedDetailIds);
                });
            }

            if ($detailsToProcess->isEmpty()) {
                throw new \Exception('Tidak ada buku yang dipilih untuk dikembalikan.');
            }

            // Determine if partial return based on quantity (jumlah), not just record count
            $totalBorrowedQty = $peminjaman->detailPeminjaman->sum('jumlah');
            $totalReturningQty = 0;
            foreach ($detailsToProcess as $detail) {
                $totalReturningQty += (int) ($request->jumlah_dikembalikan[$detail->id] ?? $detail->jumlah ?? 1);
            }
            $isPartialReturn = $totalReturningQty < $totalBorrowedQty;

            // Server-side guard against duplicate submission
            $existingReturnedQty = DetailPengembalian::whereIn('detail_peminjaman_id', $peminjaman->detailPeminjaman->pluck('id'))
                ->sum('jumlah_dikembalikan');
            if (($existingReturnedQty + $totalReturningQty) > $totalBorrowedQty) {
                throw new \Exception('Pengembalian sudah diproses sebelumnya (duplikat terdeteksi).');
            }

            $tanggalKembali = $request->tanggal_kembali ? Carbon::parse($request->tanggal_kembali) : Carbon::now();
            $jamKembali     = $request->jam_kembali ?? now()->format('H:i');
            $dendaPerHari   = 1000; // Rp 1.000/hari

            // Hitung denda per buku
            $totalDendaTerlambat = 0;
            $maxDaysLate         = 0;
            $perBookDenda        = []; // [detail_id => ['days_late'=>X, 'denda_late'=>Y, 'denda_kondisi'=>Z, 'total'=>W]]

            foreach ($detailsToProcess as $detail) {
                // Per-book due date — fallback ke peminjaman-level
                $dueDateBook = $detail->tanggal_harus_kembali
                    ? Carbon::parse($detail->tanggal_harus_kembali)
                    : Carbon::parse($peminjaman->tanggal_harus_kembali);

                $isLateBook   = $tanggalKembali->gt($dueDateBook);
                $daysLateBook = $isLateBook ? (int) $tanggalKembali->diffInDays($dueDateBook) : 0;
                $dendaLateBook = $daysLateBook * $dendaPerHari;

                $perBookDenda[$detail->id] = [
                    'days_late'     => $daysLateBook,
                    'denda_late'    => $dendaLateBook,
                    'denda_kondisi' => 0,
                    'total'         => $dendaLateBook,
                ];

                $totalDendaTerlambat += $dendaLateBook;
                if ($daysLateBook > $maxDaysLate) $maxDaysLate = $daysLateBook;
            }

            $finalTotalDenda = $totalDendaTerlambat;
            $isLate = $maxDaysLate > 0;

            // Read kondisi + jumlah_hilang from form
            $kondisiBuku = $request->input('kondisi_buku', []);
            $jumlahHilangInput = $request->input('jumlah_hilang', []);

            // Hitung denda kondisi (hilang)
            $totalDendaKondisi = 0;
            $jumlahBukuHilang = 0;
            foreach ($detailsToProcess as $detail) {
                $kondisi = $kondisiBuku[$detail->id] ?? 'baik';
                $jmlHilang = (int) ($jumlahHilangInput[$detail->id] ?? 0);
                if ($kondisi === 'hilang' && $jmlHilang > 0) {
                    $totalDendaKondisi += $jmlHilang * 100000;
                    $jumlahBukuHilang += $jmlHilang;
                }
            }
            $finalTotalDenda += $totalDendaKondisi;

            // Create pengembalian record
            $pengembalian = Pengembalian::create([
                'nomor_pengembalian'    => Pengembalian::generateNomorPengembalian(),
                'peminjaman_id'         => $peminjaman->id,
                'anggota_id'            => $peminjaman->anggota_id,
                'user_id'               => auth()->id(),
                'tanggal_pengembalian'  => $tanggalKembali,
                'jam_kembali'           => $jamKembali,
                'jumlah_hari_terlambat' => $maxDaysLate,
                'total_denda'           => $finalTotalDenda,
                'status_denda'          => $finalTotalDenda > 0 ? 'belum_dibayar' : 'tidak_ada',
                'catatan'               => $request->catatan_pengembalian . ($isPartialReturn ? ' [Pengembalian sebagian]' : '') . ($totalDendaKondisi > 0 ? " [{$jumlahBukuHilang} buku hilang]" : ''),
                'status'                => 'selesai'
            ]);

            // Create detail pengembalian per buku
            foreach ($detailsToProcess as $detail) {
                $bookDenda  = $perBookDenda[$detail->id] ?? ['total' => 0];

                $jumlahDikembalikan = (int) ($request->jumlah_dikembalikan[$detail->id] ?? $detail->jumlah ?? 1);
                $alreadyReturned = (int) $detail->detailPengembalian()->sum('jumlah_dikembalikan');
                $maxReturnable = ($detail->jumlah ?? 1) - $alreadyReturned;
                if ($jumlahDikembalikan > $maxReturnable) {
                    throw new \Exception("Jumlah dikembalikan untuk buku {$detail->buku->judul_buku} melebihi sisa pinjaman ({$maxReturnable}).");
                }

                $kondisi = $kondisiBuku[$detail->id] ?? 'baik';
                $jmlHilang = (int) ($jumlahHilangInput[$detail->id] ?? 0);
                $biayaHilang = ($kondisi === 'hilang') ? $jmlHilang * 100000 : 0;
                $catatanBuku = ($kondisi === 'hilang' && $jmlHilang > 0) ? "{$jmlHilang} buku hilang" : 'Buku dalam kondisi baik';

                DetailPengembalian::create([
                    'pengembalian_id'      => $pengembalian->id,
                    'buku_id'              => $detail->buku_id,
                    'detail_peminjaman_id' => $detail->id,
                    'kondisi_kembali'      => $kondisi,
                    'jumlah_dikembalikan'  => $jumlahDikembalikan,
                    'jumlah_hilang'        => $jmlHilang,
                    'denda_buku'           => $bookDenda['total'] + $biayaHilang,
                    'catatan_buku'         => $catatanBuku,
                ]);

                $detail->update(['kondisi_kembali' => $kondisi]);

                if ($kondisi !== 'hilang' || $jmlHilang < $jumlahDikembalikan) {
                    $stokKembali = $jumlahDikembalikan - ($kondisi === 'hilang' ? $jmlHilang : 0);
                    if ($stokKembali > 0) {
                        $detail->buku->increment('stok_tersedia', $stokKembali);
                    }
                }

                // Kembalikan stok untuk buku yang hilang
                if ($kondisi === 'hilang' && $jmlHilang > 0) {
                    $detail->buku->increment('stok_tersedia', $jmlHilang);
                }
            }

            // Update peminjaman status based on quantity returned
            $totalReturnedQty = DetailPengembalian::whereIn('detail_peminjaman_id', $peminjaman->detailPeminjaman->pluck('id'))
                ->sum('jumlah_dikembalikan');

            if ($totalReturnedQty >= $totalBorrowedQty) {
                // Semua buku sudah kembali
                $peminjaman->update([
                    'tanggal_kembali' => $tanggalKembali,
                    'jam_kembali' => $jamKembali,
                    'status' => 'dikembalikan',
                    'jumlah_buku' => 0,
                    'catatan' => $peminjaman->catatan . ($request->catatan_pengembalian ? "\n\nCatatan Pengembalian: " . $request->catatan_pengembalian : '')
                ]);
            } else {
                // Masih ada buku yang belum kembali
                $peminjaman->update([
                    'jumlah_buku' => $totalBorrowedQty - $totalReturnedQty,
                    'status' => 'dipinjam',
                    'catatan' => $peminjaman->catatan . ($request->catatan_pengembalian ? "\n\nCatatan Pengembalian Sebagian: " . $request->catatan_pengembalian : '')
                ]);
            }

            // Buat record denda dan sinkronkan status jika ada denda (terlambat)
            if ($finalTotalDenda > 0) {
                $catatanDenda = $maxDaysLate > 0
                    ? "Keterlambatan pengembalian {$maxDaysLate} hari"
                    : "Denda keterlambatan";

                Denda::create([
                    'peminjaman_id'        => $peminjaman->id,
                    'pengembalian_id'      => $pengembalian->id,
                    'anggota_id'           => $peminjaman->anggota_id,
                    'jumlah_hari_terlambat'=> $maxDaysLate,
                    'jumlah_denda'         => $finalTotalDenda,
                    'jumlah_denda_asal'    => $finalTotalDenda,
                    'status_pembayaran'    => 'sudah_dibayar',
                    'tanggal_pembayaran'   => now(),
                    'catatan'              => $catatanDenda,
                ]);

                // Sinkronkan status_denda di pengembalian
                $pengembalian->update([
                    'total_denda'             => $finalTotalDenda,
                    'status_denda'            => 'sudah_dibayar',
                    'tanggal_pembayaran_denda'=> now(),
                ]);
            }

            DB::commit();

            $message = 'Pengembalian berhasil diproses.';
            if ($isPartialReturn) {
                $remaining = $totalBorrowedQty - $totalReturningQty;
                $message = "Pengembalian sebagian berhasil ({$totalReturningQty} buku dikembalikan, {$remaining} buku masih dipinjam).";
            }
            if ($totalDendaKondisi > 0) {
                $message .= " {$jumlahBukuHilang} buku hilang, dikenakan denda Rp " . number_format($totalDendaKondisi, 0, ',', '.');
            }
            if ($isLate) {
                $message .= " Anggota terlambat {$maxDaysLate} hari dan dikenakan denda Rp " . number_format($finalTotalDenda, 0, ',', '.');
            }

            return redirect()->route('pengembalian.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('pengembalian.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get summary data for stat cards (all time)
     */
    private function getSummaryData()
    {
        return [
            'total' => Pengembalian::count(),
            'terlambat' => Pengembalian::where('jumlah_hari_terlambat', '>', 0)->count(),
            'tepat_waktu' => Pengembalian::where('jumlah_hari_terlambat', '<=', 0)->count(),
            'total_denda' => Pengembalian::sum('total_denda'),
        ];
    }

    /**
     * Get summary data for today only
     */
    private function getTodaySummaryData()
    {
        $start = Carbon::today()->startOfDay();
        $end   = Carbon::today()->endOfDay();
        $stats = Pengembalian::where('tanggal_pengembalian', '>=', $start)
            ->where('tanggal_pengembalian', '<=', $end)
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN jumlah_hari_terlambat > 0 THEN 1 ELSE 0 END) as terlambat')
            ->selectRaw('SUM(CASE WHEN jumlah_hari_terlambat <= 0 THEN 1 ELSE 0 END) as tepat_waktu')
            ->selectRaw('COALESCE(SUM(total_denda), 0) as total_denda')
            ->first();
        return [
            'total'       => (int) ($stats->total ?? 0),
            'terlambat'   => (int) ($stats->terlambat ?? 0),
            'tepat_waktu' => (int) ($stats->tepat_waktu ?? 0),
            'total_denda' => (int) ($stats->total_denda ?? 0),
        ];
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
            $pengembalian = Pengembalian::with('detailPengembalian.buku')->findOrFail($id);
            
            // Update status denda di pengembalian
            $pengembalian->status_denda = $request->status_pembayaran;
            $pengembalian->tanggal_pembayaran_denda = $request->tanggal_pembayaran;
            $pengembalian->save();

            // Update atau buat record denda
            $denda = Denda::where('pengembalian_id', $pengembalian->id)
                ->orWhere('peminjaman_id', $pengembalian->peminjaman_id)
                ->first();

            if ($denda) {
                // Update denda yang sudah ada
                $denda->status_pembayaran = $request->status_pembayaran;
                $denda->tanggal_pembayaran = $request->tanggal_pembayaran;
                // Pastikan pengembalian_id terisi
                if (!$denda->pengembalian_id) {
                    $denda->pengembalian_id = $pengembalian->id;
                }
                $denda->save();
            } else {
                // Buat denda baru jika belum ada
                Denda::create([
                    'peminjaman_id' => $pengembalian->peminjaman_id,
                    'pengembalian_id' => $pengembalian->id,
                    'anggota_id' => $pengembalian->anggota_id,
                    'jumlah_hari_terlambat' => $pengembalian->jumlah_hari_terlambat,
                    'jumlah_denda' => $pengembalian->total_denda,
                    'jumlah_denda_asal' => $pengembalian->total_denda,
                    'status_pembayaran' => $request->status_pembayaran,
                    'tanggal_pembayaran' => $request->tanggal_pembayaran,
                    'catatan' => 'Denda dari pengembalian terlambat'
                ]);
            }

            // Kembalikan stok buku yang hilang jika denda sudah dibayar
            if ($request->status_pembayaran === 'sudah_dibayar') {
                foreach ($pengembalian->detailPengembalian as $detail) {
                    if ($detail->kondisi_kembali === 'hilang' && $detail->jumlah_hilang > 0 && $detail->buku) {
                        $detail->buku->increment('stok_tersedia', $detail->jumlah_hilang);
                    }
                }
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
