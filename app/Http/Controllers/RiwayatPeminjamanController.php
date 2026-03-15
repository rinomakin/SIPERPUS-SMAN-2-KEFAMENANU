<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Peminjaman;
use App\Models\Anggota;
use App\Models\Buku;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RiwayatPeminjamanController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
    }

    public function index(Request $request)
    {
        $anggota = Anggota::where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $buku    = Buku::orderBy('judul_buku')->get();

        $summary = [
            'total'      => Peminjaman::count(),
            'dipinjam'   => Peminjaman::where('status', 'dipinjam')->count(),
            'terlambat'  => Peminjaman::where('status', 'terlambat')->count(),
            'hari_ini'   => Peminjaman::whereDate('tanggal_peminjaman', today())->count(),
        ];

        return view('admin.riwayat-peminjaman.index', compact('anggota', 'buku', 'summary'));
    }

    public function getData(Request $request)
    {
        $query = Peminjaman::with(['anggota.kelas', 'user', 'detailPeminjaman'])
            ->select('peminjaman.*');

        // Filter status
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_peminjaman', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_peminjaman', '<=', $request->tanggal_akhir);
        }

        // Filter anggota
        if ($request->filled('filter_anggota')) {
            $query->where('anggota_id', $request->filter_anggota);
        }

        // Filter buku
        if ($request->filled('filter_buku')) {
            $query->whereHas('detailPeminjaman', function ($q) use ($request) {
                $q->where('buku_id', $request->filter_buku);
            });
        }

        // Pencarian keyword
        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_peminjaman', 'like', "%{$keyword}%")
                  ->orWhereHas('anggota', function ($q2) use ($keyword) {
                      $q2->where('nama_lengkap', 'like', "%{$keyword}%")
                         ->orWhere('nomor_anggota', 'like', "%{$keyword}%");
                  });
            });
        }

        $summary = [
            'total'     => Peminjaman::count(),
            'dipinjam'  => Peminjaman::where('status', 'dipinjam')->count(),
            'terlambat' => Peminjaman::where('status', 'terlambat')->count(),
            'hari_ini'  => Peminjaman::whereDate('tanggal_peminjaman', today())->count(),
        ];

        return DataTables::of($query->orderBy('tanggal_peminjaman', 'desc'))
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) {
                return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
            })
            ->addColumn('nomor_badge', function ($row) {
                return '<span class="nomor-badge"><i class="fas fa-hashtag" style="font-size:9px;opacity:.6"></i>' . e($row->nomor_peminjaman) . '</span>';
            })
            ->addColumn('anggota_info', function ($row) {
                if (!$row->anggota) {
                    return '<span class="text-gray-300 text-xs">N/A</span>';
                }
                $gradients = ['#3b82f6,#2563eb', '#10b981,#059669', '#8b5cf6,#7c3aed', '#f59e0b,#d97706', '#ef4444,#dc2626'];
                $gradient  = $gradients[$row->anggota->id % 5];
                $initial   = strtoupper(substr($row->anggota->nama_lengkap, 0, 1));
                $kelas     = $row->anggota->kelas ? ' - ' . e($row->anggota->kelas->nama_kelas) : '';
                return '<div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg flex items-center justify-center text-white text-xs font-bold shadow-sm flex-shrink-0" style="background:linear-gradient(135deg,' . $gradient . ');">' . $initial . '</div>
                            <div>
                                <div class="text-xs font-semibold text-gray-900">' . e($row->anggota->nama_lengkap) . '</div>
                                <div class="text-[11px] text-gray-400">' . e($row->anggota->nomor_anggota) . $kelas . '</div>
                            </div>
                        </div>';
            })
            ->addColumn('jumlah_badge', function ($row) {
                $count = $row->detailPeminjaman ? $row->detailPeminjaman->count() : 0;
                return '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#dbeafe,#bfdbfe);color:#2563eb;font-size:12px;font-weight:700;">' . $count . '</span>';
            })
            ->addColumn('tanggal_info', function ($row) {
                $html = '<div class="text-xs font-medium text-gray-900">';
                $html .= $row->tanggal_peminjaman
                    ? '<i class="far fa-calendar-alt mr-1 text-gray-400"></i>' . $row->tanggal_peminjaman->format('d M Y')
                    : 'N/A';
                $html .= '</div>';
                if ($row->jam_peminjaman) {
                    $jam   = $row->jam_peminjaman instanceof \Carbon\Carbon
                        ? $row->jam_peminjaman->format('H:i')
                        : substr((string) $row->jam_peminjaman, 0, 5);
                    $html .= '<div class="text-[11px] text-gray-400 mt-0.5"><i class="far fa-clock mr-1"></i>' . e($jam) . '</div>';
                }
                return $html;
            })
            ->addColumn('batas_kembali', function ($row) {
                if (!$row->tanggal_harus_kembali) return '<span class="text-gray-400 text-xs">-</span>';
                $isLate = $row->status === 'terlambat' || ($row->status === 'dipinjam' && $row->tanggal_harus_kembali->isPast());
                $color  = $isLate ? 'color:#dc2626;' : 'color:#374151;';
                return '<div class="text-xs font-medium" style="' . $color . '">'
                     . '<i class="far fa-calendar-times mr-1 text-gray-400"></i>'
                     . $row->tanggal_harus_kembali->format('d M Y')
                     . '</div>';
            })
            ->addColumn('status_badge', function ($row) {
                return match($row->status) {
                    'dipinjam'    => '<span class="badge-status badge-dipinjam"><span class="badge-dot yellow"></span>Dipinjam</span>',
                    'dikembalikan'=> '<span class="badge-status badge-dikembalikan"><span class="badge-dot green"></span>Dikembalikan</span>',
                    'terlambat'   => '<span class="badge-status badge-terlambat"><span class="badge-dot red"></span>Terlambat</span>',
                    default       => '<span class="badge-status badge-other"><span class="badge-dot gray"></span>' . ucfirst($row->status) . '</span>',
                };
            })
            ->addColumn('petugas', function ($row) {
                return '<span class="text-xs text-gray-700 font-medium">' . e($row->user->name ?? '-') . '</span>';
            })
            ->addColumn('action', function ($row) {
                $actions  = '<div class="flex items-center justify-center gap-1.5">';
                $actions .= '<a href="' . route('peminjaman.show', $row->id) . '" class="action-btn view" title="Detail"><i class="fas fa-eye"></i></a>';
                $actions .= '<button onclick="confirmDelete(' . $row->id . ', \'' . addslashes($row->nomor_peminjaman) . '\')" class="action-btn delete" title="Hapus"><i class="fas fa-trash"></i></button>';
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'nomor_badge', 'anggota_info', 'jumlah_badge', 'tanggal_info', 'batas_kembali', 'status_badge', 'petugas', 'action'])
            ->with('summary', $summary)
            ->make(true);
    }

    public function destroy($id)
    {
        $peminjaman = Peminjaman::findOrFail($id);
        DB::transaction(function () use ($peminjaman) {
            $peminjaman->detailPeminjaman()->delete();
            if ($peminjaman->denda) $peminjaman->denda()->delete();
            $peminjaman->delete();
        });

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        }
        return redirect()->back()->with('success', 'Data riwayat peminjaman berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:peminjaman,id']);

        DB::transaction(function () use ($request) {
            $records = Peminjaman::whereIn('id', $request->ids)->get();
            foreach ($records as $peminjaman) {
                $peminjaman->detailPeminjaman()->delete();
                if ($peminjaman->denda) $peminjaman->denda()->delete();
                $peminjaman->delete();
            }
        });

        $count = count($request->ids);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "{$count} data berhasil dihapus."]);
        }
        return redirect()->back()->with('success', "{$count} data riwayat peminjaman berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $query = Peminjaman::with(['anggota', 'user', 'detailPeminjaman.buku']);

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
            $query->whereHas('detailPeminjaman', function ($q) use ($request) {
                $q->where('buku_id', $request->buku_id);
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_peminjaman', 'like', "%{$search}%")
                  ->orWhereHas('anggota', function ($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nomor_anggota', 'like', "%{$search}%");
                  });
            });
        }

        $peminjaman = $query->orderBy('tanggal_peminjaman', 'desc')->get();

        $filename = 'riwayat_peminjaman_' . date('Y-m-d_H-i-s') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($peminjaman) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'No', 'Nomor Peminjaman', 'Nama Anggota', 'Nomor Anggota',
                'Tanggal Pinjam', 'Jam Pinjam', 'Batas Kembali',
                'Tanggal Kembali', 'Jam Kembali', 'Status', 'Jumlah Buku', 'Petugas', 'Catatan'
            ]);
            $no = 1;
            foreach ($peminjaman as $loan) {
                fputcsv($file, [
                    $no++,
                    $loan->nomor_peminjaman,
                    $loan->anggota->nama_lengkap ?? '',
                    $loan->anggota->nomor_anggota ?? '',
                    $loan->tanggal_peminjaman ? $loan->tanggal_peminjaman->format('d/m/Y') : '',
                    $loan->jam_peminjaman ? $loan->jam_peminjaman->format('H:i') : '',
                    $loan->tanggal_harus_kembali ? $loan->tanggal_harus_kembali->format('d/m/Y') : '',
                    $loan->tanggal_kembali ? $loan->tanggal_kembali->format('d/m/Y') : '',
                    $loan->jam_kembali ? $loan->jam_kembali->format('H:i') : '',
                    $loan->status,
                    $loan->detailPeminjaman ? $loan->detailPeminjaman->count() : 0,
                    $loan->user->name ?? '',
                    $loan->catatan ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
