<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Jurusan;

class JurusanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:jurusan.create')->only(['create', 'store']);
        $this->middleware('permission:jurusan.edit')->only(['edit', 'update']);
        $this->middleware('permission:jurusan.delete')->only(['destroy']);
    }

    public function index()
    {
        $jurusan = Jurusan::paginate(10);
        $totalJurusan = Jurusan::count();
        $jurusanAktif = Jurusan::where('status', 1)->count();
        $jurusanNonaktif = Jurusan::where('status', 0)->count();
        return view('admin.jurusan.index', compact('jurusan', 'totalJurusan', 'jurusanAktif', 'jurusanNonaktif'));
    }

    public function create()
    {
        return view('admin.jurusan.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_jurusan' => 'required|string|max:255|unique:jurusan,nama_jurusan',
            'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan',
            'deskripsi' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        Jurusan::create($request->all());
        
        return redirect()->route('jurusan.index')
            ->with('success', 'Data jurusan berhasil ditambahkan.');
    }

    public function show($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        return view('admin.jurusan.show', compact('jurusan'));
    }

    public function edit($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        return view('admin.jurusan.edit', compact('jurusan'));
    }

    public function update(Request $request, $id)
    {
        $jurusan = Jurusan::findOrFail($id);
        
        $request->validate([
            'nama_jurusan' => 'required|string|max:255|unique:jurusan,nama_jurusan,' . $id,
            'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan,' . $id,
            'deskripsi' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $jurusan->update($request->all());
        
        return redirect()->route('jurusan.index')
            ->with('success', 'Data jurusan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $jurusan = Jurusan::findOrFail($id);
        $jurusan->delete();
        
        return redirect()->route('jurusan.index')
            ->with('success', 'Data jurusan berhasil dihapus.');
    }
} 