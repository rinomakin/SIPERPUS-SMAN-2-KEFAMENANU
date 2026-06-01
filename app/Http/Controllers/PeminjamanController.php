<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\DetailPeminjaman;
use App\Models\Anggota;
use App\Models\Buku;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class PeminjamanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
        $this->middleware('permission:peminjaman.create')->only(['create', 'store']);
        $this->middleware('permission:peminjaman.edit')->only(['edit', 'update']);
        $this->middleware('permission:peminjaman.delete')->only(['destroy']);
    }

    public function index(Request $request)
    {
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            // Summary-only request for stat cards
            if ($request->filled('ajax_summary')) {
                $totalAktif = Peminjaman::where('status', '!=', 'dikembalikan')->count();
                $dipinjam = Peminjaman::where('status', 'dipinjam')->count();
                $terlambat = Peminjaman::where('status', 'terlambat')->count();
                $totalBuku = Peminjaman::where('status', '!=', 'dikembalikan')
                    ->sum('jumlah_buku');

                return response()->json([
                    'summary' => [
                        'total_aktif' => $totalAktif,
                        'dipinjam' => $dipinjam,
                        'terlambat' => $terlambat,
                        'total_buku' => (int) $totalBuku,
                    ]
                ]);
            }

            $query = Peminjaman::with(['anggota', 'user'])
                ->where('status', '!=', 'dikembalikan');

            // Apply filters from request
            if ($request->filled('filter_status')) {
                $query->where('status', $request->filter_status);
            }

            if ($request->filled('filter_tanggal_dari')) {
                $query->whereDate('tanggal_peminjaman', '>=', $request->filter_tanggal_dari);
            }

            if ($request->filled('filter_tanggal_sampai')) {
                $query->whereDate('tanggal_peminjaman', '<=', $request->filter_tanggal_sampai);
            }

            // Summary counts for stat cards
            $summaryData = [
                'total_aktif' => Peminjaman::where('status', '!=', 'dikembalikan')->count(),
                'dipinjam' => Peminjaman::where('status', 'dipinjam')->count(),
                'terlambat' => Peminjaman::where('status', 'terlambat')->count(),
                'total_buku' => (int) Peminjaman::where('status', '!=', 'dikembalikan')->sum('jumlah_buku'),
            ];

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('nomor_badge', function($row) {
                    return '<div class="flex items-center">
                                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center mr-2.5 flex-shrink-0">
                                    <i class="fas fa-hashtag text-blue-400 text-xs"></i>
                                </div>
                                <span class="font-semibold text-gray-800 text-xs">' . e($row->nomor_peminjaman) . '</span>
                            </div>';
                })
                ->addColumn('anggota_info', function($row) {
                    $nama = $row->anggota ? e($row->anggota->nama_lengkap) : 'N/A';
                    $nomor = $row->anggota ? e($row->anggota->nomor_anggota ?? '') : '';
                    return '<div>
                                <p class="font-semibold text-gray-900 text-sm leading-tight">' . $nama . '</p>
                                ' . ($nomor ? '<p class="text-xs text-gray-400 mt-0.5">' . $nomor . '</p>' : '') . '
                            </div>';
                })
                ->addColumn('jumlah_badge', function($row) {
                    $count = $row->jumlah_buku ?? 0;
                    return '<span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-violet-50 text-violet-700 font-bold text-sm">' . $count . '</span>';
                })
                ->addColumn('tanggal_pinjam_info', function($row) {
                    if (!$row->tanggal_peminjaman) return '<span class="text-gray-400 text-xs">-</span>';
                    $tgl = \Carbon\Carbon::parse($row->tanggal_peminjaman);
                    $html = '<div>
                                <p class="text-sm font-medium text-gray-800">' . $tgl->format('d M Y') . '</p>';
                    if ($row->jam_peminjaman) {
                        $html .= '<p class="text-xs text-gray-400 mt-0.5"><i class="far fa-clock mr-1"></i>' . \Carbon\Carbon::parse($row->jam_peminjaman)->format('H:i') . '</p>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('batas_kembali_info', function($row) {
                    if (!$row->tanggal_harus_kembali) return '<span class="text-gray-400 text-xs">-</span>';
                    $tgl = \Carbon\Carbon::parse($row->tanggal_harus_kembali);
                    $now = \Carbon\Carbon::now();
                    $isOverdue = $now->gt($tgl) && $row->status !== 'dikembalikan';
                    $daysLeft = $now->diffInDays($tgl, false);

                    $html = '<div>
                                <p class="text-sm font-medium ' . ($isOverdue ? 'text-rose-600' : 'text-gray-800') . '">' . $tgl->format('d M Y') . '</p>';
                    if ($isOverdue) {
                        $html .= '<p class="text-xs text-rose-500 mt-0.5 font-medium"><i class="fas fa-exclamation-circle mr-1"></i>Terlambat ' . abs($daysLeft) . ' hari</p>';
                    } elseif ($row->status === 'dipinjam') {
                        $html .= '<p class="text-xs text-gray-400 mt-0.5">' . $daysLeft . ' hari lagi</p>';
                    }
                    $html .= '</div>';
                    return $html;
                })
                ->addColumn('status_badge', function($row) {
                    $badges = [
                        'dipinjam' => '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200/60"><span class="w-1.5 h-1.5 rounded-full bg-amber-400 mr-1.5"></span>Dipinjam</span>',
                        'dikembalikan' => '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200/60"><span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1.5"></span>Dikembalikan</span>',
                        'terlambat' => '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200/60"><span class="w-1.5 h-1.5 rounded-full bg-rose-400 mr-1.5 animate-pulse"></span>Terlambat</span>',
                    ];
                    return $badges[$row->status] ?? '<span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-50 text-gray-600 border border-gray-200/60">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function($row) {
                    $actions = '<div class="flex items-center justify-center gap-1">';

                    if (auth()->user()->hasPermission('peminjaman.show') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('peminjaman.show', $row->id) . '" class="action-btn bg-blue-50 text-blue-600 hover:bg-blue-100" title="Detail"><i class="fas fa-eye"></i></a>';
                    }

                    if (auth()->user()->hasPermission('peminjaman.edit') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('peminjaman.edit', $row->id) . '" class="action-btn bg-amber-50 text-amber-600 hover:bg-amber-100" title="Edit"><i class="fas fa-pen"></i></a>';
                    }

                    if (auth()->user()->hasPermission('peminjaman.delete') || auth()->user()->isAdmin()) {
                        $actions .= '<button onclick="confirmDelete(' . $row->id . ')" class="action-btn bg-rose-50 text-rose-600 hover:bg-rose-100" title="Hapus"><i class="fas fa-trash-alt"></i></button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['nomor_badge', 'anggota_info', 'jumlah_badge', 'tanggal_pinjam_info', 'batas_kembali_info', 'status_badge', 'action'])
                ->with('summary', $summaryData)
                ->make(true);
        }

        return view('admin.peminjaman.index');
    }

    public function create()
    {
        $anggota = Anggota::where('status', 'aktif')->get();
        $buku = Buku::where('stok_tersedia', '>', 0)->get();
        return view('admin.peminjaman.create', compact('anggota', 'buku'));
    }

    public function store(Request $request)
    {
        // Validate all fields including books
        $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'buku_ids' => 'required|array|min:1',
            'buku_ids.*' => 'required|exists:buku,id',
            'jumlah_buku' => 'required|array',
            'jumlah_buku.*' => 'required|integer|min:1',
            'tanggal_peminjaman' => 'required|date',
            'jam_peminjaman' => 'nullable|date_format:H:i',
            'tanggal_harus_kembali' => 'required|date|after_or_equal:tanggal_peminjaman',
            'jam_kembali' => 'required|date_format:H:i',
            'tanggal_kembali_buku' => 'nullable|array',
            'tanggal_kembali_buku.*' => 'nullable|date|after_or_equal:tanggal_peminjaman',
            'jam_kembali_buku' => 'nullable|array',
            'jam_kembali_buku.*' => 'nullable|date_format:H:i',
            'catatan' => 'nullable|string',
        ], [
            'buku_ids.required' => 'Pilih minimal 1 buku untuk dipinjam.',
            'buku_ids.min' => 'Pilih minimal 1 buku untuk dipinjam.',
            'buku_ids.*.required' => 'ID buku tidak boleh kosong.',
            'buku_ids.*.exists' => 'Buku yang dipilih tidak valid.',
            'tanggal_harus_kembali.after_or_equal' => 'Tanggal kembali tidak boleh kurang dari tanggal pinjam.',
            'jam_kembali.required' => 'Jam kembali wajib diisi.',
            'tanggal_kembali_buku.*.after_or_equal' => 'Tanggal kembali buku tidak boleh kurang dari tanggal pinjam.',
        ]);

        // Custom validation for jam_kembali
        if ($request->jam_peminjaman && $request->jam_kembali) {
            if ($request->jam_peminjaman === $request->jam_kembali) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['jam_kembali' => 'Jam pengembalian tidak boleh sama dengan jam peminjaman']);
            }
        }

        // Cek apakah anggota memiliki buku yang terlambat dikembalikan
        $overdueCount = Peminjaman::where('anggota_id', $request->anggota_id)
            ->where('status', 'dipinjam')
            ->where('tanggal_harus_kembali', '<', Carbon::today())
            ->count();

        if ($overdueCount > 0) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Anggota ini tidak dapat meminjam buku baru karena masih memiliki ' . $overdueCount . ' peminjaman yang melewati batas waktu pengembalian. Harap kembalikan buku tersebut terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            // Calculate total books
            $totalBooks = array_sum($request->jumlah_buku);
            
            // Create peminjaman
            $peminjaman = Peminjaman::create([
                'nomor_peminjaman' => Peminjaman::generateNomorPeminjaman(),
                'anggota_id' => $request->anggota_id,
                'user_id' => auth()->id(),
                'tanggal_peminjaman' => $request->tanggal_peminjaman,
                'jam_peminjaman' => $request->jam_peminjaman ?? now()->format('H:i'),
                'tanggal_harus_kembali' => $request->tanggal_harus_kembali,
                'jam_kembali' => $request->jam_kembali,
                'status' => 'dipinjam',
                'catatan' => $request->catatan,
                'jumlah_buku' => $totalBooks,
            ]);

            // Create detail peminjaman for each book with quantity
            foreach ($request->buku_ids as $index => $buku_id) {
                if (empty($buku_id)) continue; // Skip empty values
                
                $buku = Buku::find($buku_id);
                if (!$buku) continue; // Skip if book not found
                
                $jumlah = $request->jumlah_buku[$buku_id] ?? 1;
                
                // Check if book is available
                if ($buku->stok_tersedia < $jumlah) {
                    throw new \Exception("Buku {$buku->judul_buku} hanya tersedia {$buku->stok_tersedia} eksemplar, diminta {$jumlah} eksemplar");
                }

                // Ambil tanggal & jam kembali per-buku, fallback ke global
                $tglKembaliBuku = $request->tanggal_kembali_buku[$buku_id] ?? $request->tanggal_harus_kembali;
                $jamKembaliBuku = $request->jam_kembali_buku[$buku_id]     ?? $request->jam_kembali;

                // Create detail peminjaman
                $peminjaman->detailPeminjaman()->create([
                    'buku_id' => $buku_id,
                    'jumlah' => $jumlah,
                    'tanggal_harus_kembali' => $tglKembaliBuku,
                    'jam_kembali' => $jamKembaliBuku,
                    'kondisi_kembali' => 'baik',
                    'catatan' => null,
                ]);

                // Update book stock
                $buku->decrement('stok_tersedia', $jumlah);
            }

            DB::commit();
            return redirect()->route('peminjaman.index')
                ->with('success', 'Peminjaman berhasil dicatat.');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $peminjaman = Peminjaman::with([
            'anggota',
            'user',
            'detailPeminjaman.buku',
            'detailPeminjaman.detailPengembalian'
        ])->findOrFail($id);

        // Filter hanya buku yang belum dikembalikan seluruhnya
        $peminjaman->detailPeminjaman = $peminjaman->detailPeminjaman->filter(function($detail) {
            $returned = $detail->detailPengembalian->sum('jumlah_dikembalikan');
            return $returned < ($detail->jumlah ?? 1);
        });

        return view('admin.peminjaman.show', compact('peminjaman'));
    }

    public function edit($id)
    {
        $peminjaman = Peminjaman::with(['detailPeminjaman.buku'])->findOrFail($id);
        $anggota = Anggota::where('status', 'aktif')->get();
        $buku = Buku::where('stok_tersedia', '>', 0)->get();
        return view('admin.peminjaman.edit', compact('peminjaman', 'anggota', 'buku'));
    }

    public function update(Request $request, $id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        
        $request->validate([
            'anggota_id' => 'required|exists:anggota,id',
            'tanggal_peminjaman' => 'required|date',
            'jam_peminjaman' => 'nullable|date_format:H:i',
            'tanggal_harus_kembali' => 'required|date|after_or_equal:tanggal_peminjaman',
            'jam_kembali' => 'required|date_format:H:i',
            'status' => 'required|in:dipinjam,dikembalikan,terlambat',
            'catatan' => 'nullable|string',
            'jumlah_buku' => 'nullable|array',
            'jumlah_buku.*' => 'nullable|integer|min:1',
        ], [
            'tanggal_harus_kembali.after_or_equal' => 'Tanggal kembali tidak boleh kurang dari tanggal pinjam.',
            'jam_kembali.required' => 'Jam kembali wajib diisi.',
        ]);

        // Jika status diubah menjadi dikembalikan, set jam_kembali otomatis
        if ($request->status === 'dikembalikan' && !$request->jam_kembali) {
            $request->merge(['jam_kembali' => now()->format('H:i')]);
        }

        DB::beginTransaction();
        try {
            // Update informasi peminjaman
            $peminjaman->update($request->except(['jumlah_buku']));

            // Handle perubahan buku yang dipinjam
            if ($request->has('jumlah_buku')) {
                $totalBooks = 0;
                
                // Update jumlah untuk buku yang ada
                foreach ($request->jumlah_buku as $bukuId => $jumlah) {
                    $detail = $peminjaman->detailPeminjaman()->where('buku_id', $bukuId)->first();
                    if ($detail) {
                        $oldJumlah = $detail->jumlah ?? 1;
                        $newJumlah = (int) $jumlah;
                        
                        // Update stok buku
                        $buku = $detail->buku;
                        $buku->increment('stok_tersedia', $oldJumlah); // Kembalikan stok lama
                        $buku->decrement('stok_tersedia', $newJumlah); // Kurangi stok baru
                        
                        // Update detail peminjaman
                        $detail->update(['jumlah' => $newJumlah]);
                        $totalBooks += $newJumlah;
                    }
                }
                
                // Update total jumlah buku
                $peminjaman->update(['jumlah_buku' => $totalBooks]);
            }

            DB::commit();
            return redirect()->route('peminjaman.show', $peminjaman->id)
                ->with('success', 'Data peminjaman berhasil diperbarui.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        
        DB::beginTransaction();
        try {
            // Return all books to stock with correct quantity
            foreach ($peminjaman->detailPeminjaman as $detail) {
                $detail->buku->increment('stok_tersedia', $detail->jumlah ?? 1);
            }
            
            $peminjaman->delete();
            DB::commit();
            
            return redirect()->route('peminjaman.index')
                ->with('success', 'Data peminjaman berhasil dihapus.');
                
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    // Method untuk menampilkan detail peminjaman dengan QR scanner
    public function detail($id)
    {
        $peminjaman = Peminjaman::with(['anggota', 'user', 'detailPeminjaman.buku'])->findOrFail($id);
        $buku = Buku::where('stok_tersedia', '>', 0)->get();
        return view('admin.peminjaman.detail', compact('peminjaman', 'buku'));
    }

    // AJAX method untuk menambah buku ke peminjaman
    public function addBook(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'buku_id' => 'required|exists:buku,id',
            'jumlah' => 'required|integer|min:1',
        ]);

        $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);
        $buku = Buku::findOrFail($request->buku_id);

        // Check if book is already in this loan
        $existingDetail = $peminjaman->detailPeminjaman()->where('buku_id', $request->buku_id)->first();
        if ($existingDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Buku ini sudah ada dalam peminjaman ini'
            ]);
        }

        // Check if book is available
        if ($buku->stok_tersedia < $request->jumlah) {
            return response()->json([
                'success' => false,
                'message' => "Buku hanya tersedia {$buku->stok_tersedia} eksemplar, diminta {$request->jumlah} eksemplar"
            ]);
        }

        DB::beginTransaction();
        try {
            $detail = $peminjaman->detailPeminjaman()->create([
                'buku_id' => $request->buku_id,
                'jumlah' => $request->jumlah,
                'kondisi_kembali' => 'baik',
                'catatan' => null,
            ]);

            $buku->decrement('stok_tersedia', $request->jumlah);

            // Update jumlah_buku di peminjaman
            $peminjaman->update([
                'jumlah_buku' => $peminjaman->detailPeminjaman()->sum('jumlah')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil ditambahkan',
                'detail' => $detail->load('buku')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // AJAX method untuk menghapus buku dari peminjaman
    public function removeBook(Request $request)
    {
        $request->validate([
            'detail_id' => 'required|exists:detail_peminjaman,id',
        ]);

        $detail = DetailPeminjaman::findOrFail($request->detail_id);
        $buku = $detail->buku;
        $jumlah = $detail->jumlah ?? 1;

        DB::beginTransaction();
        try {
            $buku->increment('stok_tersedia', $jumlah);
            $detail->delete();

            // Update jumlah_buku di peminjaman
            $peminjaman = $detail->peminjaman;
            $peminjaman->update([
                'jumlah_buku' => $peminjaman->detailPeminjaman()->sum('jumlah')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil dihapus dari peminjaman'
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // AJAX method untuk scan QR code
    public function scanQR(Request $request)
    {
        $request->validate([
            'peminjaman_id' => 'required|exists:peminjaman,id',
            'barcode' => 'required|string',
            'jumlah' => 'required|integer|min:1',
        ]);

        $peminjaman = Peminjaman::findOrFail($request->peminjaman_id);
        $buku = Buku::where('barcode', $request->barcode)->first();

        if (!$buku) {
            return response()->json([
                'success' => false,
                'message' => 'Buku tidak ditemukan'
            ]);
        }

        // Check if book is already in this loan
        $existingDetail = $peminjaman->detailPeminjaman()->where('buku_id', $buku->id)->first();
        if ($existingDetail) {
            return response()->json([
                'success' => false,
                'message' => 'Buku ini sudah ada dalam peminjaman ini'
            ]);
        }

        // Check if book is available
        if ($buku->stok_tersedia < $request->jumlah) {
            return response()->json([
                'success' => false,
                'message' => "Buku hanya tersedia {$buku->stok_tersedia} eksemplar, diminta {$request->jumlah} eksemplar"
            ]);
        }

        DB::beginTransaction();
        try {
            $detail = $peminjaman->detailPeminjaman()->create([
                'buku_id' => $buku->id,
                'jumlah' => $request->jumlah,
                'kondisi_kembali' => 'baik',
                'catatan' => null,
            ]);

            $buku->decrement('stok_tersedia', $request->jumlah);

            // Update jumlah_buku di peminjaman
            $peminjaman->update([
                'jumlah_buku' => $peminjaman->detailPeminjaman()->sum('jumlah')
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Buku berhasil ditambahkan',
                'detail' => $detail->load('buku')
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * API untuk scan barcode anggota
     */
    public function scanAnggota(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $anggota = Anggota::where('barcode_anggota', $request->barcode)
                          ->where('status', 'aktif')
                          ->with('kelas')
                          ->first();

        if (!$anggota) {
            // Get some example barcodes for better error message
            $exampleBarcodes = Anggota::where('status', 'aktif')
                                     ->whereNotNull('barcode_anggota')
                                     ->take(3)
                                     ->pluck('barcode_anggota')
                                     ->toArray();
            
            $message = 'Anggota dengan barcode "' . $request->barcode . '" tidak ditemukan atau tidak aktif.';
            if (!empty($exampleBarcodes)) {
                $message .= ' Contoh barcode yang valid: ' . implode(', ', $exampleBarcodes);
            }
            
            return response()->json([
                'success' => false,
                'message' => $message
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $anggota->id,
                'nama_lengkap' => $anggota->nama_lengkap,
                'nomor_anggota' => $anggota->nomor_anggota,
                'barcode_anggota' => $anggota->barcode_anggota,
                'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : 'N/A',
                'jenis_anggota' => $anggota->jenis_anggota
            ]
        ]);
    }

    /**
     * API untuk scan barcode buku
     */
    public function scanBuku(Request $request)
    {
        $request->validate([
            'barcode' => 'required|string'
        ]);

        $buku = Buku::where('barcode', $request->barcode)
                    ->where('stok_tersedia', '>', 0)
                    ->with('kategori', 'jenis')
                    ->first();

        if (!$buku) {
            // Get some example barcodes for better error message
            $exampleBarcodes = Buku::where('stok_tersedia', '>', 0)
                                   ->whereNotNull('barcode')
                                   ->take(3)
                                   ->pluck('barcode')
                                   ->toArray();
            
            $message = 'Buku dengan barcode "' . $request->barcode . '" tidak ditemukan atau stok habis.';
            if (!empty($exampleBarcodes)) {
                $message .= ' Contoh barcode yang valid: ' . implode(', ', $exampleBarcodes);
            }
            
            return response()->json([
                'success' => false,
                'message' => $message
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $buku->id,
                'judul_buku' => $buku->judul_buku,
                'barcode_buku' => $buku->barcode,
                'isbn' => $buku->isbn,
                'stok_tersedia' => $buku->stok_tersedia,
                'penulis' => $buku->penulis ?? 'N/A',
                'penerbit' => $buku->penerbit ?? 'N/A',
                'kategori' => $buku->kategori ? $buku->kategori->nama_kategori : 'N/A'
            ]
        ]);
    }

    /**
     * API untuk scan multiple barcode buku
     */
    public function scanMultipleBuku(Request $request)
    {
        $request->validate([
            'barcodes' => 'required|array',
            'barcodes.*' => 'string'
        ]);

        $bukuList = [];
        $errors = [];

        foreach ($request->barcodes as $barcode) {
            $buku = Buku::where('barcode', $barcode)
                        ->where('stok_tersedia', '>', 0)
                        ->with('kategori', 'jenis')
                        ->first();

            if ($buku) {
                $bukuList[] = [
                    'id' => $buku->id,
                    'judul_buku' => $buku->judul_buku,
                    'barcode_buku' => $buku->barcode,
                    'isbn' => $buku->isbn,
                    'stok_tersedia' => $buku->stok_tersedia,
                    'penulis' => $buku->penulis ?? 'N/A',
                    'penerbit' => $buku->penerbit ?? 'N/A',
                    'kategori' => $buku->kategori ? $buku->kategori->nama_kategori : 'N/A'
                ];
            } else {
                $errors[] = "Buku dengan barcode {$barcode} tidak ditemukan atau stok habis";
            }
        }

        return response()->json([
            'success' => count($bukuList) > 0,
            'data' => $bukuList,
            'errors' => $errors
        ]);
    }

    /**
     * API untuk search anggota
     */
    public function searchAnggota(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2'
            ]);

            $query = $request->get('query');
            
            $anggota = Anggota::where('status', 'aktif')
                              ->where(function($q) use ($query) {
                                  $q->where('nama_lengkap', 'LIKE', "%{$query}%")
                                    ->orWhere('nomor_anggota', 'LIKE', "%{$query}%")
                                    ->orWhere('barcode_anggota', 'LIKE', "%{$query}%");
                              })
                              ->with('kelas')
                              ->take(10)
                              ->get()
                              ->map(function($anggota) {
                                  return [
                                      'id' => $anggota->id,
                                      'nama_lengkap' => $anggota->nama_lengkap,
                                      'nomor_anggota' => $anggota->nomor_anggota,
                                      'barcode_anggota' => $anggota->barcode_anggota,
                                      'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : 'N/A',
                                      'jenis_anggota' => $anggota->jenis_anggota
                                  ];
                              });

            return response()->json([
                'success' => true,
                'data' => $anggota
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in searchAnggota: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari anggota: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk search buku
     */
    public function searchBuku(Request $request)
    {
        try {
            $request->validate([
                'query' => 'required|string|min:2'
            ]);

            $query = $request->get('query');
            
            $buku = Buku::where('stok_tersedia', '>', 0)
                        ->where(function($q) use ($query) {
                            $q->where('judul_buku', 'LIKE', "%{$query}%")
                              ->orWhere('pengarang', 'LIKE', "%{$query}%")
                              ->orWhere('isbn', 'LIKE', "%{$query}%")
                              ->orWhere('barcode', 'LIKE', "%{$query}%");
                        })
                        ->with('kategoriBuku', 'jenisBuku')
                        ->take(10)
                        ->get()
                        ->map(function($buku) {
                            return [
                                'id' => $buku->id,
                                'judul_buku' => $buku->judul_buku,
                                'penulis' => $buku->pengarang ?? 'N/A',
                                'penerbit' => $buku->penerbit ?? 'N/A',
                                'isbn' => $buku->isbn ?? 'N/A',
                                'barcode_buku' => $buku->barcode ?? 'N/A',
                                'stok_tersedia' => $buku->stok_tersedia,
                                'kategori' => $buku->kategoriBuku ? $buku->kategoriBuku->nama_kategori : 'N/A'
                            ];
                        });

            return response()->json([
                'success' => true,
                'data' => $buku
            ]);
        } catch (\Exception $e) {
            \Log::error('Error in searchBuku: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mencari buku: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk check apakah anggota memiliki peminjaman yang melewati jatuh tempo
     */
    public function checkOverdueLoan(Request $request)
    {
        try {
            $request->validate([
                'anggota_id' => 'required|exists:anggota,id'
            ]);

            $overdueLoans = Peminjaman::where('anggota_id', $request->anggota_id)
                ->where('status', 'dipinjam')
                ->where('tanggal_harus_kembali', '<', Carbon::today())
                ->with(['detailPeminjaman.buku', 'detailPeminjaman.detailPengembalian'])
                ->get();

            if ($overdueLoans->isEmpty()) {
                return response()->json([
                    'success' => true,
                    'has_overdue' => false,
                ]);
            }

            $books = [];
            foreach ($overdueLoans as $loan) {
                $daysLate = Carbon::today()->diffInDays(Carbon::parse($loan->tanggal_harus_kembali));
                foreach ($loan->detailPeminjaman as $detail) {
                    // Skip buku yang sudah dikembalikan
                    if ($detail->detailPengembalian->isNotEmpty()) continue;
                    $books[] = [
                        'nomor_peminjaman'      => $loan->nomor_peminjaman,
                        'judul_buku'            => $detail->buku ? $detail->buku->judul_buku : 'N/A',
                        'tanggal_harus_kembali' => Carbon::parse($loan->tanggal_harus_kembali)->format('d/m/Y'),
                        'hari_terlambat'        => $daysLate,
                    ];
                }
            }

            return response()->json([
                'success'     => true,
                'has_overdue' => true,
                'message'     => 'Anggota memiliki ' . $overdueLoans->count() . ' peminjaman yang melewati batas waktu pengembalian.',
                'data'        => $books,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * API untuk check apakah anggota masih memiliki pinjaman aktif untuk buku tertentu
     */
    public function checkActiveLoan(Request $request)
    {
        try {
            $request->validate([
                'anggota_id' => 'required|exists:anggota,id',
                'buku_id' => 'required|exists:buku,id'
            ]);

            $anggotaId = $request->get('anggota_id');
            $bukuId = $request->get('buku_id');

            // Check if anggota has active loan for this specific book (belum dikembalikan)
            $activeLoan = Peminjaman::where('anggota_id', $anggotaId)
                ->where('status', '!=', 'dikembalikan')
                ->whereHas('detailPeminjaman', function($query) use ($bukuId) {
                    $query->where('buku_id', $bukuId)
                          ->whereDoesntHave('detailPengembalian');
                })
                ->with(['detailPeminjaman' => function($query) use ($bukuId) {
                    $query->where('buku_id', $bukuId)
                          ->whereDoesntHave('detailPengembalian');
                }])
                ->first();

            if ($activeLoan) {
                // Get book details
                $buku = Buku::find($bukuId);
                $detailPeminjaman = $activeLoan->detailPeminjaman->first();
                
                return response()->json([
                    'success' => true,
                    'has_active_loan' => true,
                    'message' => "Anggota masih meminjam buku \"{$buku->judul_buku}\" dan belum mengembalikannya",
                    'data' => [
                        'peminjaman' => [
                            'nomor_peminjaman' => $activeLoan->nomor_peminjaman,
                            'tanggal_peminjaman' => $activeLoan->tanggal_peminjaman,
                            'tanggal_harus_kembali' => $activeLoan->tanggal_harus_kembali,
                            'status' => $activeLoan->status
                        ],
                        'buku' => [
                            'id' => $buku->id,
                            'judul_buku' => $buku->judul_buku,
                            'jumlah_dipinjam' => $detailPeminjaman->jumlah ?? 1
                        ]
                    ]
                ]);
            }

            return response()->json([
                'success' => true,
                'has_active_loan' => false,
                'message' => 'Tidak ada pinjaman aktif untuk buku ini'
            ]);

        } catch (\Exception $e) {
            \Log::error('Error in checkActiveLoan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memeriksa pinjaman aktif: ' . $e->getMessage()
            ], 500);
        }
    }


} 