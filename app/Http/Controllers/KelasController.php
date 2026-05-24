<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\Jurusan;

class KelasController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:kelas.create')->only(['create', 'store']);
        $this->middleware('permission:kelas.edit')->only(['edit', 'update']);
        $this->middleware('permission:kelas.delete')->only(['destroy']);
    }

    public function index()
    {
        $kelas       = Kelas::with(['jurusan', 'anggota'])->orderBy('created_at', 'desc')->get();
        $jurusan     = Jurusan::where('status', 'aktif')->get();
        $totalKelas  = $kelas->count();
        $kelasAktif  = $kelas->where('status', 'aktif')->count();
        $kelasNonaktif = $kelas->where('status', 'nonaktif')->count();
        return view('admin.kelas.index', compact('kelas', 'jurusan', 'totalKelas', 'kelasAktif', 'kelasNonaktif'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'kode_kelas' => 'required|string|max:20|unique:kelas,kode_kelas',
            'jurusan_id' => 'required|exists:jurusan,id',
            'tahun_ajaran' => 'required|regex:/^\d{4}\/\d{4}$/',
            'status' => 'required|in:0,1',
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2024/2025)',
            'kode_kelas.unique' => 'Kode kelas sudah digunakan',
        ]);

        Kelas::create($request->all());
        
        return redirect()->route('kelas.index')
            ->with('success', 'Data kelas berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);
        
        $request->validate([
            'nama_kelas' => 'required|string|max:255',
            'jurusan_id' => 'required|exists:jurusan,id',
            'tahun_ajaran' => 'required|regex:/^\d{4}\/\d{4}$/',
            'status' => 'required|in:0,1',
        ], [
            'tahun_ajaran.regex' => 'Format tahun ajaran harus YYYY/YYYY (contoh: 2024/2025)',
        ]);

        // Kode kelas tidak bisa diubah, jadi kita exclude dari update
        $kelas->update($request->except(['kode_kelas']));
        
        return redirect()->route('kelas.index')
            ->with('success', 'Data kelas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);
        
        // Cek apakah kelas masih memiliki anggota
        if ($kelas->anggota()->count() > 0) {
            return redirect()->route('kelas.index')
                ->with('error', 'Tidak dapat menghapus kelas yang masih memiliki anggota.');
        }
        
        $kelas->delete();
        
        return redirect()->route('kelas.index')
            ->with('success', 'Data kelas berhasil dihapus.');
    }

    public function generateKodeKelas(Request $request)
    {
        $jurusan = Jurusan::find($request->jurusan_id);
        $tahunAjaran = $request->tahun_ajaran;
        
        if (!$jurusan || !$tahunAjaran) {
            return response()->json(['kode' => '']);
        }
        
        // Ambil 2 digit terakhir dari tahun ajaran (misal: 2024/2025 -> 24)
        $tahunPendek = substr(explode('/', $tahunAjaran)[0], -2);
        
        // Hitung jumlah kelas yang sudah ada untuk jurusan dan tahun ajaran ini
        $jumlahKelas = Kelas::where('jurusan_id', $request->jurusan_id)
                           ->where('tahun_ajaran', $tahunAjaran)
                           ->count();
        
        // Generate nomor urut kelas (1, 2, 3, dst)
        $nomorUrut = $jumlahKelas + 1;
        
        // Format: [KodeJurusan][TahunPendek][NomorUrut]
        // Contoh: TKJ241 (TKJ tahun 2024 kelas 1)
        $kodeKelas = $jurusan->kode_jurusan . $tahunPendek . $nomorUrut;
        
        return response()->json(['kode' => $kodeKelas]);
    }
    
    // Method yang tidak digunakan lagi karena menggunakan modal
    public function create()
    {
        $jurusan = Jurusan::all();
        return view('admin.kelas.create', compact('jurusan'));
    }

    public function show($id)
    {
        $kelas = Kelas::with('jurusan')->findOrFail($id);
        return view('admin.kelas.show', compact('kelas'));
    }

    public function edit($id)
    {
        $kelas = Kelas::findOrFail($id);
        $jurusan = Jurusan::all();
        return view('admin.kelas.edit', compact('kelas', 'jurusan'));
    }
} 