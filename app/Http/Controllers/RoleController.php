<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role.view')->only(['index', 'show']);
        $this->middleware('permission:role.create')->only(['create', 'store', 'generateKode']);
        $this->middleware('permission:role.edit')->only(['edit', 'update']);
        $this->middleware('permission:role.delete')->only(['destroy']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::orderBy('nama_peran')->paginate(10);
        return view('admin.role.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.role.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_peran' => 'required|string|max:255',
            'kode_peran' => 'required|string|max:50|unique:peran,kode_peran',
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        Role::create($request->all());

        return redirect()->route('role.index')
            ->with('success', 'Role berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Role $role)
    {
        return view('admin.role.show', compact('role'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        return view('admin.role.edit', compact('role'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'nama_peran' => 'required|string|max:255',
            'kode_peran' => 'required|string|max:50|unique:peran,kode_peran,' . $role->id,
            'deskripsi' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        $role->update($request->all());

        return redirect()->route('role.index')
            ->with('success', 'Role berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        // Cek apakah role masih digunakan oleh user
        if ($role->users()->count() > 0) {
            return redirect()->route('role.index')
                ->with('error', 'Role tidak dapat dihapus karena masih digunakan oleh user!');
        }

        $role->delete();

        return redirect()->route('role.index')
            ->with('success', 'Role berhasil dihapus!');
    }

    /**
     * Generate kode peran otomatis
     */
    public function generateKode(Request $request)
    {
        $namaPeran = $request->nama_peran;
        $kodePeran = strtoupper(Str::slug($namaPeran, '_'));
        
        return response()->json(['kode_peran' => $kodePeran]);
    }
}
