<?php

namespace App\Http\Controllers;

use App\Models\KategoriBuku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KategoriBukuController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kategori-buku.create')->only(['create', 'store']);
        $this->middleware('permission:kategori-buku.edit')->only(['edit', 'update']);
        $this->middleware('permission:kategori-buku.delete')->only(['destroy', 'destroyMultiple']);
    }
    public function index()
    {
        $kategoris       = KategoriBuku::withCount('buku')->orderBy('nama_kategori')->get();
        $totalKategori   = $kategoris->count();
        $totalBuku       = $kategoris->sum('buku_count');
        $kategoriAda     = $kategoris->where('buku_count', '>', 0)->count();
        return view('admin.kategori-buku.index', compact('kategoris', 'totalKategori', 'totalBuku', 'kategoriAda'));
    }

    public function create()
    {
        return view('admin.kategori-buku.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_buku,nama_kategori',
            'kode_kategori' => 'nullable|string|max:10|unique:kategori_buku,kode_kategori',
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi',
            'nama_kategori.unique' => 'Nama kategori sudah ada',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter',
            'kode_kategori.unique' => 'Kode kategori sudah ada',
            'kode_kategori.max' => 'Kode kategori maksimal 10 karakter',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter',
        ]);

        try {
            DB::beginTransaction();
            
            // Generate kode_kategori if not provided
            $kodeKategori = $request->kode_kategori;
            if (empty($kodeKategori)) {
                $kodeKategori = $this->generateKodeKategori($request->nama_kategori);
            }
            
            KategoriBuku::create([
                'nama_kategori' => $request->nama_kategori,
                'kode_kategori' => $kodeKategori,
                'deskripsi' => $request->deskripsi,
            ]);

            DB::commit();
            return redirect()->route('kategori-buku.index')->with('success', 'Kategori buku berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function show(KategoriBuku $kategoriBuku)
    {
        $bukuCount = $kategoriBuku->buku()->count();
        return view('admin.kategori-buku.show', compact('kategoriBuku', 'bukuCount'));
    }

    public function edit(KategoriBuku $kategoriBuku)
    {
        return view('admin.kategori-buku.edit', compact('kategoriBuku'));
    }

    public function update(Request $request, KategoriBuku $kategoriBuku)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255|unique:kategori_buku,nama_kategori,' . $kategoriBuku->id,
            'kode_kategori' => 'nullable|string|max:10|unique:kategori_buku,kode_kategori,' . $kategoriBuku->id,
            'deskripsi' => 'nullable|string|max:500',
        ], [
            'nama_kategori.required' => 'Nama kategori wajib diisi',
            'nama_kategori.unique' => 'Nama kategori sudah ada',
            'nama_kategori.max' => 'Nama kategori maksimal 255 karakter',
            'kode_kategori.unique' => 'Kode kategori sudah ada',
            'kode_kategori.max' => 'Kode kategori maksimal 10 karakter',
            'deskripsi.max' => 'Deskripsi maksimal 500 karakter',
        ]);

        try {
            DB::beginTransaction();
            
            // Generate kode_kategori if not provided and current is empty
            $kodeKategori = $request->kode_kategori;
            if (empty($kodeKategori) && empty($kategoriBuku->kode_kategori)) {
                $kodeKategori = $this->generateKodeKategori($request->nama_kategori);
            } elseif (empty($kodeKategori)) {
                $kodeKategori = $kategoriBuku->kode_kategori; // Keep existing code
            }
            
            $kategoriBuku->update([
                'nama_kategori' => $request->nama_kategori,
                'kode_kategori' => $kodeKategori,
                'deskripsi' => $request->deskripsi,
            ]);

            DB::commit();
            return redirect()->route('kategori-buku.index')->with('success', 'Kategori buku berhasil diperbarui');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy(KategoriBuku $kategoriBuku)
    {
        try {
            $bukuCount = $kategoriBuku->buku()->count();
            if ($bukuCount > 0) {
                return redirect()->route('kategori-buku.index')
                    ->with('error', "Kategori tidak dapat dihapus karena masih digunakan oleh {$bukuCount} buku.");
            }

            DB::beginTransaction();
            $kategoriBuku->delete();
            DB::commit();

            return redirect()->route('kategori-buku.index')
                ->with('success', 'Kategori buku berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('kategori-buku.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'kategori_ids' => 'required|array',
            'kategori_ids.*' => 'exists:kategori_buku,id'
        ]);

        try {
            DB::beginTransaction();
            
            $deletedCount = 0;
            $errorCount = 0;
            
            foreach ($request->kategori_ids as $kategoriId) {
                $kategori = KategoriBuku::find($kategoriId);
                
                if ($kategori) {
                    $bukuCount = $kategori->buku()->count();
                    if ($bukuCount > 0) {
                        $errorCount++;
                        continue;
                    }
                    
                    $kategori->delete();
                    $deletedCount++;
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deletedCount} kategori";
            if ($errorCount > 0) {
                $message .= " ({$errorCount} kategori tidak dapat dihapus karena masih digunakan)";
            }

            return response()->json([
                'success' => true,
                'count' => $deletedCount,
                'message' => $message
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
     * Generate unique kode_kategori based on nama_kategori
     */
    private function generateKodeKategori($namaKategori)
    {
        // Clean and transform the name
        $cleanName = strtoupper(trim($namaKategori));
        
        // Remove common words and get meaningful parts
        $commonWords = ['BUKU', 'KATEGORI', 'JENIS', 'DAN', 'ATAU', 'UNTUK', 'DARI', 'PADA', 'DENGAN'];
        $words = explode(' ', $cleanName);
        $meaningfulWords = array_filter($words, function($word) use ($commonWords) {
            return !in_array($word, $commonWords) && strlen($word) > 1;
        });
        
        // Generate code based on meaningful words
        if (count($meaningfulWords) >= 2) {
            // Take first 2-3 characters from first two words
            $code = substr($meaningfulWords[0], 0, 3) . substr($meaningfulWords[1], 0, 2);
        } elseif (count($meaningfulWords) == 1) {
            // Take first 5 characters from single word
            $code = substr($meaningfulWords[0], 0, 5);
        } else {
            // Fallback: take first 5 characters from original name
            $code = substr(str_replace(' ', '', $cleanName), 0, 5);
        }
        
        // Ensure minimum length
        if (strlen($code) < 3) {
            $code = strtoupper(substr(str_replace(' ', '', $namaKategori), 0, 5));
        }
        
        // Make sure it's unique
        $originalCode = $code;
        $counter = 1;
        
        while (KategoriBuku::where('kode_kategori', $code)->exists()) {
            if (strlen($originalCode) >= 8) {
                $code = substr($originalCode, 0, 7) . $counter;
            } else {
                $code = $originalCode . $counter;
            }
            $counter++;
            
            // Prevent infinite loop
            if ($counter > 99) {
                $code = 'KAT' . str_pad($counter, 3, '0', STR_PAD_LEFT);
                break;
            }
        }
        
        return substr($code, 0, 10); // Ensure max 10 characters
    }

    /**
     * Generate kode kategori via AJAX
     */
    public function generateKode(Request $request)
    {
        $request->validate([
            'nama_kategori' => 'required|string|max:255'
        ]);

        $kode = $this->generateKodeKategori($request->nama_kategori);
        
        return response()->json([
            'success' => true,
            'kode_kategori' => $kode
        ]);
    }
} 