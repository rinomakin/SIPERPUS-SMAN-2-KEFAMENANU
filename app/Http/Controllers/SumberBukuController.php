<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SumberBuku;

class SumberBukuController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:sumber-buku.create')->only(['create', 'store']);
        $this->middleware('permission:sumber-buku.edit')->only(['edit', 'update']);
        $this->middleware('permission:sumber-buku.delete')->only(['destroy', 'destroyMultiple']);
    }

    public function index()
    {
        $sumber         = SumberBuku::withCount('buku')->orderBy('nama_sumber')->get();
        $totalSumber    = $sumber->count();
        $sumberAktif    = $sumber->where('status', 'aktif')->count();
        $sumberNonaktif = $sumber->where('status', 'nonaktif')->count();
        return view('admin.sumber-buku.index', compact('sumber', 'totalSumber', 'sumberAktif', 'sumberNonaktif'));
    }

    public function create()
    {
        return view('admin.sumber-buku.create');
    }

    public function store(Request $request)
    {
        try {
            $request->validate([
                'nama_sumber' => 'required|string|max:255|unique:sumber_buku,nama_sumber',
                'kode_sumber' => 'nullable|string|max:10|unique:sumber_buku,kode_sumber',
                'deskripsi' => 'nullable|string',
                'status' => 'required|in:aktif,nonaktif',
            ]);

            $data = $request->all();
            
            // Generate kode sumber jika tidak diisi
            if (empty($data['kode_sumber'])) {
                $data['kode_sumber'] = $this->generateKodeSumber();
            }

            $sumber = SumberBuku::create($data);
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data sumber buku berhasil ditambahkan.',
                    'data' => $sumber
                ]);
            }
            
            return redirect()->route('sumber-buku.index')
                ->with('success', 'Data sumber buku berhasil ditambahkan.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $sumber = SumberBuku::with('buku')->findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $sumber
            ]);
        }
        
        return view('admin.sumber-buku.show', compact('sumber'));
    }

    public function edit($id)
    {
        $sumber = SumberBuku::findOrFail($id);
        
        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $sumber
            ]);
        }
        
        return view('admin.sumber-buku.edit', compact('sumber'));
    }

    public function update(Request $request, $id)
    {
        try {
            $sumber = SumberBuku::findOrFail($id);
            
            $request->validate([
                'nama_sumber' => 'required|string|max:255|unique:sumber_buku,nama_sumber,' . $id,
                'kode_sumber' => 'nullable|string|max:10|unique:sumber_buku,kode_sumber,' . $id,
                'deskripsi' => 'nullable|string',
                'status' => 'required|in:aktif,nonaktif',
            ]);

            $sumber->update($request->all());
            
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data sumber buku berhasil diperbarui.',
                    'data' => $sumber
                ]);
            }
            
            return redirect()->route('sumber-buku.index')
                ->with('success', 'Data sumber buku berhasil diperbarui.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal.',
                    'errors' => $e->validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $sumber = SumberBuku::findOrFail($id);
            
            // Check if sumber is being used by any books
            $isUsed = $sumber->buku()->exists();
            
            if ($isUsed) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Sumber buku tidak dapat dihapus karena masih digunakan oleh buku lain.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'Sumber buku tidak dapat dihapus karena masih digunakan oleh buku lain.');
            }
            
            $sumber->delete();
            
            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data sumber buku berhasil dihapus.'
                ]);
            }
            
            return redirect()->route('sumber-buku.index')
                ->with('success', 'Data sumber buku berhasil dihapus.');
                
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Generate kode sumber otomatis
     */
    public function generateKodeSumber()
    {
        $prefix = 'SB';
        $lastSumber = SumberBuku::whereNotNull('kode_sumber')
                                ->where('kode_sumber', 'LIKE', $prefix . '%')
                                ->orderBy('id', 'desc')
                                ->first();
        
        if ($lastSumber && $lastSumber->kode_sumber) {
            $lastNumber = intval(substr($lastSumber->kode_sumber, 2));
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Generate kode sumber via AJAX
     */
    public function generateKode()
    {
        try {
            $kode = $this->generateKodeSumber();
            
            return response()->json([
                'success' => true,
                'kode' => $kode
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal generate kode: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete multiple sumber buku
     */
    public function destroyMultiple(Request $request)
    {
        $request->validate([
            'sumber_ids' => 'required|array',
            'sumber_ids.*' => 'exists:sumber_buku,id'
        ]);

        try {
            $deletedCount = 0;
            $errors = [];
            
            foreach ($request->sumber_ids as $sumberId) {
                $sumber = SumberBuku::find($sumberId);
                
                if ($sumber) {
                    // Check if sumber is being used
                    $isUsed = $sumber->buku()->exists();
                    
                    if ($isUsed) {
                        $errors[] = "Sumber '{$sumber->nama_sumber}' masih digunakan oleh buku lain";
                    } else {
                        $sumber->delete();
                        $deletedCount++;
                    }
                }
            }

            $message = "Berhasil menghapus {$deletedCount} sumber buku";
            if (count($errors) > 0) {
                $message .= " (" . count($errors) . " sumber tidak dapat dihapus)";
            }

            return response()->json([
                'success' => true,
                'count' => $deletedCount,
                'message' => $message,
                'errors' => $errors
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
} 