<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Buku;
use App\Models\KategoriBuku;
use App\Models\JenisBuku;
use App\Models\SumberBuku;
use App\Models\RakBuku;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\BukuExport;
use App\Exports\BukuTemplateExport;
use App\Imports\BukuImport;
use Yajra\DataTables\Facades\DataTables;

class BukuController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
        $this->middleware('permission:buku.create')->only(['create', 'store']);
        $this->middleware('permission:buku.edit')->only(['edit', 'update']);
        $this->middleware('permission:buku.delete')->only(['destroy', 'destroyMultiple']);
    }

    public function index(Request $request)
    {
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = Buku::with(['kategori', 'jenis', 'rak']);
            
            // Apply filters from request
            if ($request->filled('filter_kategori_id')) {
                $query->where('kategori_id', $request->filter_kategori_id);
            }
            
            if ($request->filled('filter_jenis_id')) {
                $query->where('jenis_id', $request->filter_jenis_id);
            }
            
            if ($request->filled('filter_status')) {
                if ($request->filter_status === 'tersedia') {
                    $query->where('stok_tersedia', '>', 0);
                } elseif ($request->filter_status === 'habis') {
                    $query->where('stok_tersedia', '<=', 0);
                }
            }
            
            if ($request->filled('filter_tahun_terbit')) {
                $query->where('tahun_terbit', $request->filter_tahun_terbit);
            }
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function($row) {
                    return '<input type="checkbox" class="book-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 transition-all duration-200" value="' . $row->id . '">';
                })
                ->addColumn('cover', function($row) {
                    $gradients = ['#6366f1,#8b5cf6','#3b82f6,#2563eb','#10b981,#059669','#f59e0b,#d97706','#ef4444,#dc2626'];
                    $gradient = $gradients[($row->id ?? 0) % 5];
                    $initial = strtoupper(substr($row->judul_buku ?? 'B', 0, 1));
                    if ($row->cover_buku) {
                        return '<div class="cover-container">'
                            . '<img src="' . asset('storage/' . $row->cover_buku) . '" alt="Cover" class="cover-img"'
                            . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">'
                            . '<div class="cover-placeholder" style="display:none;background:linear-gradient(135deg,' . $gradient . ');color:white;font-weight:700;font-size:1.2rem;">' . $initial . '</div>'
                            . '</div>';
                    }
                    return '<div class="cover-container">'
                        . '<div class="cover-placeholder" style="background:linear-gradient(135deg,' . $gradient . ');color:white;font-weight:700;font-size:1.2rem;">' . $initial . '</div>'
                        . '</div>';
                })
                ->addColumn('judul_info', function($row) {
                    $isbn = $row->isbn ? '<span class="font-mono text-gray-500">' . e($row->isbn) . '</span>' : '<span class="italic text-gray-400">Tanpa ISBN</span>';
                    $penulis = $row->penulis ? '<span class="text-gray-500">' . e($row->penulis) . '</span>' : '';
                    return '<div class="min-w-[180px]">'
                        . '<div class="text-sm font-semibold text-gray-900 line-clamp-2">' . e($row->judul_buku) . '</div>'
                        . ($penulis ? '<div class="text-xs mt-0.5"><i class="fas fa-user-pen text-gray-400 mr-1" style="font-size:0.6rem;"></i>' . $penulis . '</div>' : '')
                        . '<div class="text-[11px] mt-0.5">' . $isbn . '</div>'
                        . '</div>';
                })
                ->addColumn('rak_info', function($row) {
                    if ($row->rak) {
                        return '<div class="flex items-center gap-2">'
                            . '<div class="w-7 h-7 rounded-lg bg-amber-50 flex items-center justify-center flex-shrink-0"><i class="fas fa-archive text-amber-500" style="font-size:0.7rem;"></i></div>'
                            . '<div>'
                            . '<div class="text-sm font-medium text-gray-900">' . e($row->rak->nama_rak) . '</div>'
                            . '<div class="text-[11px] text-gray-400">' . e($row->rak->kode_rak) . '</div>'
                            . '</div></div>';
                    }
                    return '<span class="text-xs text-gray-400 italic">Belum ada rak</span>';
                })
                ->addColumn('kategori_badge', function($row) {
                    $kategori = $row->kategori ? $row->kategori->nama_kategori : 'Tidak diketahui';
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-blue-50 text-blue-700 border border-blue-200">'
                        . '<i class="fas fa-tag mr-1.5" style="font-size:0.55rem;"></i>' . e($kategori) . '</span>';
                })
                ->addColumn('jenis_badge', function($row) {
                    $jenis = $row->jenis ? $row->jenis->nama_jenis : 'Tidak diketahui';
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-violet-50 text-violet-700 border border-violet-200">'
                        . '<i class="fas fa-bookmark mr-1.5" style="font-size:0.55rem;"></i>' . e($jenis) . '</span>';
                })
                ->addColumn('stok_info', function($row) {
                    $percentage = $row->jumlah_stok > 0 ? round(($row->stok_tersedia / $row->jumlah_stok) * 100) : 0;
                    $barColor = $percentage > 50 ? '#22c55e' : ($percentage > 20 ? '#f59e0b' : '#ef4444');
                    return '<div class="min-w-[80px]">'
                        . '<div class="text-sm font-semibold text-gray-900">' . $row->stok_tersedia . ' <span class="text-gray-400 font-normal">/ ' . $row->jumlah_stok . '</span></div>'
                        . '<div class="w-full bg-gray-100 rounded-full h-1.5 mt-1"><div class="h-1.5 rounded-full" style="width:' . $percentage . '%;background:' . $barColor . ';"></div></div>'
                        . '</div>';
                })
                ->addColumn('status_badge', function($row) {
                    if ($row->stok_tersedia > 0) {
                        return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">'
                            . '<span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-emerald-500"></span>Tersedia</span>';
                    }
                    return '<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-medium bg-red-50 text-red-700 border border-red-200">'
                        . '<span class="w-1.5 h-1.5 rounded-full mr-1.5 bg-red-500"></span>Habis</span>';
                })
                ->addColumn('action', function($row) {
                    $actions = '<div class="flex items-center justify-center gap-1">';

                    if (auth()->user()->hasPermission('buku.view') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('buku.show', $row->id) . '" class="action-btn action-btn-view" title="Lihat Detail"><i class="fas fa-eye"></i></a>';
                    }

                    if (auth()->user()->hasPermission('buku.edit') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('buku.edit', $row->id) . '" class="action-btn action-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    if (auth()->user()->hasPermission('buku.print-barcode') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('buku.cetak-barcode', $row->id) . '" class="action-btn action-btn-print" title="Cetak Barcode" target="_blank"><i class="fas fa-barcode"></i></a>';
                    }

                    if (auth()->user()->hasPermission('buku.delete') || auth()->user()->isAdmin()) {
                        $actions .= '<button onclick="confirmDeleteBuku(' . $row->id . ')" class="action-btn action-btn-delete" title="Hapus"><i class="fas fa-trash-alt"></i></button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['checkbox', 'cover', 'judul_info', 'rak_info', 'kategori_badge', 'jenis_badge', 'stok_info', 'status_badge', 'action'])
                ->make(true);
        }
        
        // Regular view request - Data untuk filter
        $kategoris = KategoriBuku::all();
        $jenis = JenisBuku::all();

        return view('admin.buku.index', compact('kategoris', 'jenis'));
    }

    public function create()
    {
        $kategoris = KategoriBuku::all();
        $jenis = JenisBuku::all();
        $sumber = SumberBuku::all();
        $rakBuku = RakBuku::aktif()->get();
        
        return view('admin.buku.create', compact('kategoris', 'jenis', 'sumber', 'rakBuku'));
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'judul_buku' => 'required|string|max:255',
                'penulis' => 'required|string|max:255',
                'penerbit' => 'required|string|max:255',
                'kategori_id' => 'required|exists:kategori_buku,id',
                'jenis_id' => 'required|exists:jenis_buku,id',
                'sumber_id' => 'required|exists:sumber_buku,id',
                'isbn' => 'nullable|string|max:20',
                'tahun_terbit' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
                'jumlah_halaman' => 'nullable|integer|min:1',
                'bahasa' => 'nullable|string|max:50',
                'jumlah_stok' => 'required|integer|min:1',
                'lokasi_rak' => 'nullable|string|max:255',
                'rak_id' => 'nullable|exists:rak_buku,id',
                'gambar_sampul' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
                'deskripsi' => 'nullable|string',
                'status' => 'required|in:tersedia,tidak_tersedia',
                'barcode' => 'nullable|string|unique:buku,barcode',
            ]);

            // Handle barcode generation or manual input
            $barcode = null;
            if ($request->filled('barcode')) {
                // Manual barcode input
                $barcode = $request->barcode;
                // Check if barcode already exists
                if (Buku::where('barcode', $barcode)->exists()) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['barcode' => 'Barcode sudah ada, silakan gunakan barcode lain']);
                }
            } else {
                // Auto generate barcode
                try {
                    $barcode = Buku::generateBarcode();
                } catch (\Exception $e) {
                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['barcode' => 'Gagal generate barcode: ' . $e->getMessage()]);
                }
            }
            
            $data = $request->all();
            $data['barcode'] = $barcode;
            $data['stok_tersedia'] = $request->jumlah_stok;

            // Handle file upload for gambar_sampul
            if ($request->hasFile('gambar_sampul')) {
                $file = $request->file('gambar_sampul');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads'), $filename);
                $data['gambar_sampul'] = $filename;
            }

            Buku::create($data);
            
            return redirect()->route('buku.index')
                ->with('success', 'Data buku berhasil ditambahkan.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['general' => 'Terjadi kesalahan: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate barcode untuk buku
     */
    public function generateBarcode(Request $request)
    {
        $request->validate([
            'buku_id' => 'required|exists:buku,id'
        ]);

        $buku = Buku::findOrFail($request->buku_id);
        
        // Generate barcode baru jika belum ada
        if (!$buku->barcode) {
            $buku->update(['barcode' => Buku::generateBarcode()]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Barcode berhasil digenerate',
            'barcode' => $buku->barcode
        ]);
    }

    /**
     * Generate barcode untuk multiple buku
     */
    public function generateMultipleBarcode(Request $request)
    {
        $request->validate([
            'buku_ids' => 'required|array',
            'buku_ids.*' => 'exists:buku,id'
        ]);

        $successCount = 0;
        $errors = [];

        foreach ($request->buku_ids as $bukuId) {
            $buku = Buku::find($bukuId);
            
            if ($buku && !$buku->barcode) {
                try {
                    $buku->update(['barcode' => Buku::generateBarcode()]);
                    $successCount++;
                } catch (\Exception $e) {
                    $errors[] = "Gagal generate barcode untuk buku: {$buku->judul_buku}";
                }
            } elseif ($buku && $buku->barcode) {
                $errors[] = "Buku {$buku->judul_buku} sudah memiliki barcode";
            }
        }

        return response()->json([
            'success' => $successCount > 0,
            'message' => "Berhasil generate barcode untuk {$successCount} buku",
            'success_count' => $successCount,
            'errors' => $errors
        ]);
    }

    /**
     * Scan barcode buku
     */
    public function scanBarcode(Request $request)
    {
        try {
            $request->validate([
                'barcode' => 'required|string'
            ]);

            $barcode = $request->barcode;
            $buku = Buku::with(['kategoriBuku', 'jenisBuku', 'sumberBuku'])
                        ->where('barcode', $barcode)
                        ->first();

            if ($buku) {
                if ($buku->stok_tersedia <= 0) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Buku tidak tersedia untuk dipinjam (stok habis)'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $buku->id,
                        'judul_buku' => $buku->judul_buku,
                        'penulis' => $buku->pengarang ?? 'N/A',
                        'penerbit' => $buku->penerbit ?? 'N/A',
                        'isbn' => $buku->isbn ?? 'N/A',
                        'barcode' => $buku->barcode ?? 'N/A',
                        'stok_tersedia' => $buku->stok_tersedia,
                        'kategori' => $buku->kategoriBuku ? $buku->kategoriBuku->nama_kategori : 'N/A',
                        'jenis' => $buku->jenisBuku ? $buku->jenisBuku->nama_jenis : 'N/A'
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku dengan barcode tersebut tidak ditemukan'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error in scanBarcode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat scan barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cetak barcode untuk buku tunggal
     */
    public function printBarcode($id)
    {
        $buku = Buku::with(['kategori'])->findOrFail($id);
        
        return view('admin.buku.print-barcode', compact('buku'));
    }

    /**
     * Cetak barcode untuk buku tunggal (versi cetak)
     */
    public function cetakBarcode($id)
    {
        $buku = Buku::with(['kategori'])->findOrFail($id);
        
        return view('admin.buku.cetak-barcode', compact('buku'));
    }

    /**
     * Cetak barcode untuk multiple buku
     */
    public function printMultipleBarcode(Request $request)
    {
        $request->validate([
            'buku_ids' => 'required|array',
            'buku_ids.*' => 'exists:buku,id'
        ]);

        $buku = Buku::with(['kategori', 'jenis', 'sumber'])
                    ->whereIn('id', $request->buku_ids)
                    ->get();

        return view('admin.buku.print-multiple-barcode', compact('buku'));
    }

    /**
     * Export data buku ke Excel
     */
    public function export(Request $request)
    {
        $filename = 'data_buku_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new BukuExport($request), $filename);
    }

    /**
     * Download template import buku
     */
    public function downloadTemplate()
    {
        $filename = 'template_import_buku.xlsx';
        
        return Excel::download(new BukuTemplateExport(), $filename);
    }

    /**
     * Import data buku dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new BukuImport();
            Excel::import($import, $request->file('file'));

            $importedCount = $import->getImportedCount();
            $errors = $import->getErrors();
            
            if ($importedCount > 0) {
                $message = "✅ Berhasil mengimpor {$importedCount} data buku.";
                
                if (!empty($errors)) {
                    $uniqueErrors = array_unique($errors);
                    $errorCount = count($uniqueErrors);
                    $message .= " ⚠️ Ditemukan {$errorCount} jenis error:";
                    
                    // Tampilkan hanya 3 error pertama
                    $displayErrors = array_slice($uniqueErrors, 0, 3);
                    foreach ($displayErrors as $error) {
                        $message .= "\n• " . $error;
                    }
                    
                    if ($errorCount > 3) {
                        $message .= "\n• Dan " . ($errorCount - 3) . " error lainnya.";
                    }
                }
                
                return redirect()->route('buku.index')
                    ->with('success', $message);
            } else {
                $errorMessage = '❌ Tidak ada data yang berhasil diimpor.';
                if (!empty($errors)) {
                    $uniqueErrors = array_unique($errors);
                    $errorCount = count($uniqueErrors);
                    $errorMessage .= "\n\nDitemukan {$errorCount} jenis error:";
                    
                    // Tampilkan hanya 5 error pertama
                    $displayErrors = array_slice($uniqueErrors, 0, 5);
                    foreach ($displayErrors as $error) {
                        $errorMessage .= "\n• " . $error;
                    }
                    
                    if ($errorCount > 5) {
                        $errorMessage .= "\n• Dan " . ($errorCount - 5) . " error lainnya.";
                    }
                }
                
                return redirect()->back()
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '❌ Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $buku = Buku::with(['kategori', 'jenis', 'sumber'])->findOrFail($id);
        return view('admin.buku.show', compact('buku'));
    }

    public function edit($id)
    {
        $buku = Buku::findOrFail($id);
        $kategoris = KategoriBuku::all();
        $jenis = JenisBuku::all();
        $sumber = SumberBuku::all();
        $rakBuku = RakBuku::aktif()->get();
        
        return view('admin.buku.edit', compact('buku', 'kategoris', 'jenis', 'sumber', 'rakBuku'));
    }

    public function update(Request $request, $id)
    {
        $buku = Buku::findOrFail($id);
        
        $request->validate([
            'judul_buku' => 'required|string|max:255',
            'penulis' => 'required|string|max:255',
            'penerbit' => 'required|string|max:255',
            'kategori_id' => 'required|exists:kategori_buku,id',
            'jenis_id' => 'required|exists:jenis_buku,id',
            'sumber_id' => 'required|exists:sumber_buku,id',
            'isbn' => 'nullable|string|max:20',
            'tahun_terbit' => 'nullable|integer|min:1900|max:' . (date('Y') + 1),
            'jumlah_halaman' => 'nullable|integer|min:1',
            'bahasa' => 'nullable|string|max:50',
            'jumlah_stok' => 'required|integer|min:1',
            'lokasi_rak' => 'nullable|string|max:255',
            'rak_id' => 'nullable|exists:rak_buku,id',
            'gambar_sampul' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:tersedia,tidak_tersedia',
        ]);

        $data = $request->all();
        $data['stok_tersedia'] = $request->jumlah_stok;
        // Barcode tidak boleh diubah saat edit
        unset($data['barcode']);

        // Handle file upload for gambar_sampul
        if ($request->hasFile('gambar_sampul')) {
            $file = $request->file('gambar_sampul');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $data['gambar_sampul'] = $filename;
        }

        $buku->update($data);
        
        return redirect()->route('buku.index')
            ->with('success', 'Data buku berhasil diperbarui.');
    }

    public function destroy($id)
    {
        try {
            $buku = Buku::findOrFail($id);
            
            // Check if book is being borrowed
            $isBeingBorrowed = \App\Models\DetailPeminjaman::whereHas('peminjaman', function($query) {
                $query->where('status', 'dipinjam');
            })->where('buku_id', $id)->exists();
            
            if ($isBeingBorrowed) {
                return response()->json([
                    'success' => false,
                    'message' => 'Buku tidak dapat dihapus karena sedang dipinjam'
                ]);
            }
            
            $buku->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Data buku berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'buku_ids' => 'required|array',
            'buku_ids.*' => 'exists:buku,id'
        ]);

        try {
            DB::beginTransaction();
            
            $deletedCount = 0;
            $errors = [];
            
            foreach ($request->buku_ids as $bukuId) {
                $buku = Buku::find($bukuId);
                
                if ($buku) {
                    try {
                        // Check if book is being borrowed
                        $isBeingBorrowed = \App\Models\DetailPeminjaman::whereHas('peminjaman', function($query) {
                            $query->where('status', 'dipinjam');
                        })->where('buku_id', $bukuId)->exists();
                        
                        if ($isBeingBorrowed) {
                            $errors[] = "Buku '{$buku->judul_buku}' sedang dipinjam dan tidak dapat dihapus";
                        } else {
                            $buku->delete();
                            $deletedCount++;
                        }
                    } catch (\Exception $e) {
                        $errors[] = "Gagal menghapus buku '{$buku->judul_buku}': " . $e->getMessage();
                    }
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deletedCount} buku";
            if (count($errors) > 0) {
                $message .= " (" . count($errors) . " buku tidak dapat dihapus)";
            }

            return response()->json([
                'success' => true,
                'count' => $deletedCount,
                'message' => $message,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
} 