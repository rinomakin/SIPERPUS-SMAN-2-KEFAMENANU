<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisBuku;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class JenisBukuController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:jenis-buku.create')->only(['create', 'store']);
        $this->middleware('permission:jenis-buku.edit')->only(['edit', 'update']);
        $this->middleware('permission:jenis-buku.delete')->only(['destroy', 'bulkDelete']);
    }

    public function index()
    {
        $jenis          = JenisBuku::withCount('buku')->orderBy('nama_jenis')->get();
        $totalJenis     = $jenis->count();
        $jenisAktif     = $jenis->where('status', 1)->count();
        $jenisNonaktif  = $jenis->where('status', 0)->count();

        return view('admin.jenis-buku.index', compact('jenis', 'totalJenis', 'jenisAktif', 'jenisNonaktif'));
    }

    public function create()
    {
        // Redirect to index page since we're using modal for create
        return redirect()->route('jenis-buku.index');
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nama_jenis' => 'required|string|max:255|unique:jenis_buku,nama_jenis',
                'kode_jenis' => 'required|string|max:10|unique:jenis_buku,kode_jenis',
                'deskripsi' => 'nullable|string|max:500',
                'status' => 'required|boolean',
            ], [
                'nama_jenis.required' => 'Nama jenis buku wajib diisi.',
                'nama_jenis.unique' => 'Nama jenis buku sudah ada.',
                'kode_jenis.required' => 'Kode jenis wajib diisi.',
                'kode_jenis.unique' => 'Kode jenis sudah ada.',
                'kode_jenis.max' => 'Kode jenis maksimal 10 karakter.',
                'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
            ]);

            if ($validator->fails()) {
                return redirect()->route('jenis-buku.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();
            
            JenisBuku::create($request->all());
            
            DB::commit();
            
            return redirect()->route('jenis-buku.index')
                ->with('success', 'Data jenis buku berhasil ditambahkan.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->route('jenis-buku.index')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('jenis-buku.index')
                ->with('error', 'Terjadi kesalahan saat menyimpan data jenis buku: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        try {
            $jenis = JenisBuku::with('buku')->findOrFail($id);
            return view('admin.jenis-buku.show', compact('jenis'));
        } catch (\Exception $e) {
            return redirect()->route('jenis-buku.index')
                ->with('error', 'Data jenis buku tidak ditemukan.');
        }
    }

    public function edit($id)
    {
        try {
            $jenis = JenisBuku::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $jenis
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Data tidak ditemukan'
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $jenis = JenisBuku::findOrFail($id);
            
            $validator = Validator::make($request->all(), [
                'nama_jenis' => 'required|string|max:255|unique:jenis_buku,nama_jenis,' . $id,
                'kode_jenis' => 'required|string|max:10|unique:jenis_buku,kode_jenis,' . $id,
                'deskripsi' => 'nullable|string|max:500',
                'status' => 'required|boolean',
            ], [
                'nama_jenis.required' => 'Nama jenis buku wajib diisi.',
                'nama_jenis.unique' => 'Nama jenis buku sudah ada.',
                'kode_jenis.required' => 'Kode jenis wajib diisi.',
                'kode_jenis.unique' => 'Kode jenis sudah ada.',
                'kode_jenis.max' => 'Kode jenis maksimal 10 karakter.',
                'deskripsi.max' => 'Deskripsi maksimal 500 karakter.',
            ]);

            if ($validator->fails()) {
                return redirect()->route('jenis-buku.index')
                    ->withErrors($validator)
                    ->withInput();
            }

            DB::beginTransaction();

            $jenis->update($request->all());
            
            DB::commit();
            
            return redirect()->route('jenis-buku.index')
                ->with('success', 'Data jenis buku berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->route('jenis-buku.index')
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('jenis-buku.index')
                ->with('error', 'Terjadi kesalahan saat memperbarui data jenis buku: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $jenis = JenisBuku::findOrFail($id);
            
            // Cek apakah jenis buku masih digunakan oleh buku
            if ($jenis->buku()->count() > 0) {
                return redirect()->route('jenis-buku.index')
                    ->with('error', 'Jenis buku tidak dapat dihapus karena masih digunakan oleh ' . $jenis->buku()->count() . ' buku.');
            }
            
            DB::beginTransaction();
            
            $jenis->delete();
            
            DB::commit();
            
            return redirect()->route('jenis-buku.index')
                ->with('success', 'Data jenis buku berhasil dihapus.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('jenis-buku.index')
                ->with('error', 'Terjadi kesalahan saat menghapus data jenis buku: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        try {
            $query = JenisBuku::query();

            // Pencarian
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('nama_jenis', 'like', "%{$search}%")
                      ->orWhere('kode_jenis', 'like', "%{$search}%")
                      ->orWhere('deskripsi', 'like', "%{$search}%");
                });
            }

            // Filter status
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $jenis = $query->get();

            $filename = 'jenis_buku_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($jenis) {
                $file = fopen('php://output', 'w');
                
                // Header CSV
                fputcsv($file, ['No', 'Nama Jenis', 'Kode Jenis', 'Deskripsi', 'Status', 'Tanggal Dibuat', 'Terakhir Diperbarui']);
                
                // Data
                foreach ($jenis as $index => $item) {
                    fputcsv($file, [
                        $index + 1,
                        $item->nama_jenis,
                        $item->kode_jenis,
                        $item->deskripsi ?: '-',
                        $item->status ? 'Aktif' : 'Tidak Aktif',
                        $item->created_at->format('d-m-Y H:i:s'),
                        $item->updated_at->format('d-m-Y H:i:s'),
                    ]);
                }
                
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            return redirect()->route('jenis-buku.index')
                ->with('error', 'Terjadi kesalahan saat mengexport data: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            
            if (empty($ids)) {
                return response()->json(['error' => 'Tidak ada data yang dipilih'], 400);
            }

            DB::beginTransaction();

            $jenisToDelete = JenisBuku::whereIn('id', $ids)->get();
            $deletedCount = 0;
            $errorMessages = [];

            foreach ($jenisToDelete as $jenis) {
                if ($jenis->buku()->count() > 0) {
                    $errorMessages[] = "Jenis buku '{$jenis->nama_jenis}' tidak dapat dihapus karena masih digunakan oleh {$jenis->buku()->count()} buku.";
                } else {
                    $jenis->delete();
                    $deletedCount++;
                }
            }

            DB::commit();

            $message = "Berhasil menghapus {$deletedCount} data jenis buku.";
            if (!empty($errorMessages)) {
                $message .= " " . implode(' ', $errorMessages);
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'deleted_count' => $deletedCount,
                'errors' => $errorMessages
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
}