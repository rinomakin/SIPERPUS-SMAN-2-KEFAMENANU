<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RakBuku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RakBukuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $rakBuku       = RakBuku::orderBy('nama_rak')->get();
        $totalRak      = $rakBuku->count();
        $rakAktif      = $rakBuku->where('status', 'Aktif')->count();
        $rakNonaktif   = $rakBuku->where('status', 'Nonaktif')->count();
        return view('admin.rak-buku.index', compact('rakBuku', 'totalRak', 'rakAktif', 'rakNonaktif'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.rak-buku.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_rak' => 'required|string|max:100',
            'kode_rak' => 'required|string|max:20|unique:rak_buku,kode_rak',
            'deskripsi' => 'nullable|string',
            'lokasi' => 'nullable|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'status' => 'required|in:Aktif,Nonaktif'
        ], [
            'nama_rak.required' => 'Nama rak harus diisi',
            'kode_rak.required' => 'Kode rak harus diisi',
            'kode_rak.unique' => 'Kode rak sudah digunakan',
            'kapasitas.required' => 'Kapasitas harus diisi',
            'kapasitas.min' => 'Kapasitas minimal 1',
            'status.required' => 'Status harus dipilih'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            RakBuku::create($request->all());
            
            return redirect()->route('rak-buku.index')
                ->with('success', 'Rak buku berhasil ditambahkan');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(RakBuku $rakBuku)
    {
        $rakBuku->load('buku');
        return view('admin.rak-buku.show', compact('rakBuku'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RakBuku $rakBuku)
    {
        return view('admin.rak-buku.edit', compact('rakBuku'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RakBuku $rakBuku)
    {
        $validator = Validator::make($request->all(), [
            'nama_rak' => 'required|string|max:100',
            'kode_rak' => 'required|string|max:20|unique:rak_buku,kode_rak,' . $rakBuku->id,
            'deskripsi' => 'nullable|string',
            'lokasi' => 'nullable|string|max:100',
            'kapasitas' => 'required|integer|min:1',
            'status' => 'required|in:Aktif,Nonaktif'
        ], [
            'nama_rak.required' => 'Nama rak harus diisi',
            'kode_rak.required' => 'Kode rak harus diisi',
            'kode_rak.unique' => 'Kode rak sudah digunakan',
            'kapasitas.required' => 'Kapasitas harus diisi',
            'kapasitas.min' => 'Kapasitas minimal 1',
            'status.required' => 'Status harus dipilih'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $rakBuku->update($request->all());
            
            return redirect()->route('rak-buku.index')
                ->with('success', 'Rak buku berhasil diperbarui');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RakBuku $rakBuku)
    {
        try {
            // Cek apakah ada buku di rak ini
            if ($rakBuku->buku()->count() > 0) {
                return redirect()->route('rak-buku.index')
                    ->with('error', 'Tidak dapat menghapus rak yang masih berisi buku');
            }

            $rakBuku->delete();
            
            return redirect()->route('rak-buku.index')
                ->with('success', 'Rak buku berhasil dihapus');
        } catch (\Exception $e) {
            return redirect()->route('rak-buku.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Get rak buku for dropdown
     */
    public function getRakBuku()
    {
        $rakBuku = RakBuku::aktif()->get();
        return response()->json($rakBuku);
    }
}
