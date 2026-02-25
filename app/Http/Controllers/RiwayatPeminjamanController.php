<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Anggota;
use App\Models\Buku;
use Illuminate\Support\Facades\DB;

class RiwayatPeminjamanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
    }

    public function index(Request $request)
    {
        $query = Peminjaman::with(['anggota', 'user', 'detailPeminjaman.buku']);

        // Filter berdasarkan tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_peminjaman', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_peminjaman', '<=', $request->tanggal_akhir);
        }

        // Filter berdasarkan jam
        if ($request->filled('jam_mulai')) {
            $query->where('jam_peminjaman', '>=', $request->jam_mulai);
        }

        if ($request->filled('jam_akhir')) {
            $query->where('jam_peminjaman', '<=', $request->jam_akhir);
        }

        // Filter berdasarkan status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter berdasarkan anggota
        if ($request->filled('anggota_id')) {
            $query->where('anggota_id', $request->anggota_id);
        }

        // Filter berdasarkan buku
        if ($request->filled('buku_id')) {
            $query->whereHas('detailPeminjaman', function($q) use ($request) {
                $q->where('buku_id', $request->buku_id);
            });
        }

        // Pencarian berdasarkan nomor peminjaman atau nama anggota
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_peminjaman', 'like', "%{$search}%")
                  ->orWhereHas('anggota', function($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nomor_anggota', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $peminjaman = $query->paginate(15);

        // Data untuk filter
        $anggota = Anggota::where('status', 'aktif')->get();
        $buku = Buku::all();

        return view('admin.riwayat-peminjaman.index', compact('peminjaman', 'anggota', 'buku'));
    }

    public function export(Request $request)
    {
        $query = Peminjaman::with(['anggota', 'user', 'detailPeminjaman.buku']);

        // Apply same filters as index method
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_peminjaman', '>=', $request->tanggal_mulai);
        }

        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_peminjaman', '<=', $request->tanggal_akhir);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('anggota_id')) {
            $query->where('anggota_id', $request->anggota_id);
        }

        if ($request->filled('buku_id')) {
            $query->whereHas('detailPeminjaman', function($q) use ($request) {
                $q->where('buku_id', $request->buku_id);
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nomor_peminjaman', 'like', "%{$search}%")
                  ->orWhereHas('anggota', function($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nomor_anggota', 'like', "%{$search}%");
                  });
            });
        }

        $peminjaman = $query->get();

        // Generate CSV
        $filename = 'riwayat_peminjaman_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($peminjaman) {
            $file = fopen('php://output', 'w');
            
            // Header CSV
            fputcsv($file, [
                'No', 'Nomor Peminjaman', 'Nama Anggota', 'Nomor Anggota', 
                'Tanggal Pinjam', 'Jam Pinjam', 'Tanggal Harus Kembali',
                'Tanggal Kembali', 'Jam Kembali', 'Status', 'Petugas', 'Catatan'
            ]);

            $no = 1;
            foreach ($peminjaman as $loan) {
                fputcsv($file, [
                    $no++,
                    $loan->nomor_peminjaman,
                    $loan->anggota->nama_lengkap,
                    $loan->anggota->nomor_anggota,
                    $loan->tanggal_peminjaman ? $loan->tanggal_peminjaman->format('d/m/Y') : '',
                    $loan->jam_peminjaman ? $loan->jam_peminjaman->format('H:i') : '',
                    $loan->tanggal_harus_kembali ? $loan->tanggal_harus_kembali->format('d/m/Y') : '',
                    $loan->tanggal_kembali ? $loan->tanggal_kembali->format('d/m/Y') : '',
                    $loan->jam_kembali ? $loan->jam_kembali->format('H:i') : '',
                    $loan->status,
                    $loan->user->name ?? '',
                    $loan->catatan ?? ''
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
} 