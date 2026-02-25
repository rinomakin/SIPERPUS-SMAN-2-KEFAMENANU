<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::with('role')->orderBy('nama_lengkap')->paginate(10);
        return view('admin.user.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::aktif()->orderBy('nama_peran')->get();
        return view('admin.user.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'peran_id' => 'required|exists:peran,id',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'peran_id' => $request->peran_id,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'status' => $request->status
        ]);

        return redirect()->route('user.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        return view('admin.user.show', compact('user'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::aktif()->orderBy('nama_peran')->get();
        return view('admin.user.edit', compact('user', 'roles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8|confirmed',
            'peran_id' => 'required|exists:peran,id',
            'nomor_telepon' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'status' => 'required|in:aktif,nonaktif'
        ]);

        $data = [
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'peran_id' => $request->peran_id,
            'nomor_telepon' => $request->nomor_telepon,
            'alamat' => $request->alamat,
            'status' => $request->status
        ];

        // Update password jika diisi
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('user.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Cek apakah user yang akan dihapus adalah user yang sedang login
        if ($user->id === auth()->id()) {
            return redirect()->route('user.index')
                ->with('error', 'Tidak dapat menghapus akun yang sedang digunakan!');
        }

        $user->delete();

        return redirect()->route('user.index')
            ->with('success', 'User berhasil dihapus!');
    }

    /**
     * Reset password user
     */
    public function resetPassword(User $user)
    {
        $user->update([
            'password' => Hash::make('password123')
        ]);

        return redirect()->route('user.index')
            ->with('success', 'Password user berhasil direset!');
    }
}
