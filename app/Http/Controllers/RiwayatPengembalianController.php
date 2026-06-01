<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengembalian;
use App\Models\Anggota;
use App\Models\Buku;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class RiwayatPengembalianController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
        $this->middleware('role:ADMIN')->only(['destroy', 'bulkDestroy']);
    }

    public function index(Request $request)
    {
        $anggota = Anggota::where('status', 'aktif')->orderBy('nama_lengkap')->get();
        $buku    = Buku::orderBy('judul_buku')->get();

        $summary = [
            'total'      => Pengembalian::count(),
            'terlambat'  => Pengembalian::where('jumlah_hari_terlambat', '>', 0)->count(),
            'total_denda'=> Pengembalian::sum('total_denda'),
            'hari_ini'   => Pengembalian::whereDate('tanggal_pengembalian', today())->count(),
        ];

        $canDelete = auth()->user()->isAdmin();
        return view('admin.riwayat-pengembalian.index', compact('anggota', 'buku', 'summary', 'canDelete'));
    }

    public function getData(Request $request)
    {
        $isAdmin = auth()->user()->isAdmin();
        $query = Pengembalian::with(['anggota.kelas', 'user', 'detailPengembalian'])
            ->select('pengembalian.*');

        // Filter status keterlambatan
        if ($request->filled('filter_status')) {
            if ($request->filter_status === 'tepat_waktu') {
                $query->where('jumlah_hari_terlambat', '<=', 0);
            } elseif ($request->filter_status === 'terlambat') {
                $query->where('jumlah_hari_terlambat', '>', 0);
            }
        }

        // Filter status denda
        if ($request->filled('filter_status_denda')) {
            if ($request->filter_status_denda === 'tidak_ada') {
                $query->where('total_denda', 0);
            } else {
                $query->where('status_denda', $request->filter_status_denda);
            }
        }

        // Filter tanggal
        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_pengembalian', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_pengembalian', '<=', $request->tanggal_akhir);
        }

        // Filter anggota
        if ($request->filled('filter_anggota')) {
            $query->where('anggota_id', $request->filter_anggota);
        }

        // Pencarian keyword
        if ($request->filled('search_keyword')) {
            $keyword = $request->search_keyword;
            $query->where(function ($q) use ($keyword) {
                $q->where('nomor_pengembalian', 'like', "%{$keyword}%")
                  ->orWhereHas('anggota', function ($q2) use ($keyword) {
                      $q2->where('nama_lengkap', 'like', "%{$keyword}%")
                         ->orWhere('nomor_anggota', 'like', "%{$keyword}%");
                  });
            });
        }

        $summary = [
            'total'       => Pengembalian::count(),
            'terlambat'   => Pengembalian::where('jumlah_hari_terlambat', '>', 0)->count(),
            'total_denda' => Pengembalian::sum('total_denda'),
            'hari_ini'    => Pengembalian::whereDate('tanggal_pengembalian', today())->count(),
        ];

        return DataTables::of($query->orderBy('tanggal_pengembalian', 'desc'))
            ->addIndexColumn()
            ->addColumn('checkbox', function ($row) use ($isAdmin) {
                if (!$isAdmin) return '';
                return '<input type="checkbox" class="row-checkbox" value="' . $row->id . '">';
            })
            ->addColumn('nomor_badge', function ($row) {
                return '<span class="nomor-badge"><i class="fas fa-hashtag" style="font-size:9px;opacity:.6"></i>' . e($row->nomor_pengembalian) . '</span>';
            })
            ->addColumn('anggota_info', function ($row) {
                if (!$row->anggota) {
                    return '<span class="text-gray-300 text-xs">N/A</span>';
                }
                $gradients = ['#10b981,#059669', '#3b82f6,#2563eb', '#8b5cf6,#7c3aed', '#f59e0b,#d97706', '#ef4444,#dc2626'];
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
                $total = $row->detailPengembalian ? $row->detailPengembalian->sum('jumlah_dikembalikan') : 0;
                return '<span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:linear-gradient(135deg,#ede9fe,#ddd6fe);color:#7c3aed;font-size:12px;font-weight:700;">' . $total . '</span>';
            })
            ->addColumn('tanggal_info', function ($row) {
                $html = '<div class="text-xs font-medium text-gray-900">';
                $html .= $row->tanggal_pengembalian
                    ? '<i class="far fa-calendar-alt mr-1 text-gray-400"></i>' . $row->tanggal_pengembalian->format('d M Y')
                    : 'N/A';
                $html .= '</div>';
                if ($row->jam_pengembalian) {
                    $jam   = $row->jam_pengembalian instanceof \Carbon\Carbon
                        ? $row->jam_pengembalian->format('H:i')
                        : substr((string) $row->jam_pengembalian, 0, 5);
                    $html .= '<div class="text-[11px] text-gray-400 mt-0.5"><i class="far fa-clock mr-1"></i>' . e($jam) . '</div>';
                }
                return $html;
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->jumlah_hari_terlambat > 0) {
                    return '<span class="badge-status badge-terlambat"><span class="badge-dot red"></span>Terlambat ' . $row->jumlah_hari_terlambat . 'h</span>';
                }
                return '<span class="badge-status badge-tepat"><span class="badge-dot green"></span>Tepat Waktu</span>';
            })
            ->addColumn('denda_info', function ($row) {
                if ($row->total_denda > 0) {
                    $isPaid    = $row->status_denda === 'sudah_dibayar';
                    $cardClass = $isPaid ? 'denda-card paid' : 'denda-card has-denda';
                    $amtClass  = $isPaid ? 'denda-amount green' : 'denda-amount red';
                    $chipClass = $isPaid ? 'denda-status-chip lunas' : 'denda-status-chip belum';
                    $chipIcon  = $isPaid ? 'fa-check-double' : 'fa-clock';
                    $chipText  = $isPaid ? 'Lunas' : 'Belum Bayar';
                    return '<div class="' . $cardClass . '">'
                         . '<div class="' . $amtClass . '"><i class="fas fa-coins" style="font-size:11px;opacity:.7;margin-right:3px"></i>Rp ' . number_format($row->total_denda, 0, ',', '.') . '</div>'
                         . '<span class="' . $chipClass . '"><i class="fas ' . $chipIcon . '" style="font-size:8px"></i> ' . $chipText . '</span>'
                         . '</div>';
                }
                return '<span class="denda-badge no-denda"><i class="fas fa-check-circle" style="font-size:11px"></i> Tidak ada denda</span>';
            })
            ->addColumn('petugas', function ($row) {
                return '<span class="text-xs text-gray-700 font-medium">' . e($row->user->name ?? '-') . '</span>';
            })
            ->addColumn('action', function ($row) use ($isAdmin) {
                $actions  = '<div class="flex items-center justify-center gap-1.5">';
                $actions .= '<a href="' . route('pengembalian.show', $row->id) . '" class="action-btn view" title="Detail"><i class="fas fa-eye"></i></a>';
                if ($isAdmin) {
                    $actions .= '<button onclick="confirmDelete(' . $row->id . ', \'' . addslashes($row->nomor_pengembalian) . '\')" class="action-btn delete" title="Hapus"><i class="fas fa-trash"></i></button>';
                }
                $actions .= '</div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'nomor_badge', 'anggota_info', 'jumlah_badge', 'tanggal_info', 'status_badge', 'denda_info', 'petugas', 'action'])
            ->with('summary', $summary)
            ->make(true);
    }

    public function destroy($id)
    {
        $pengembalian = Pengembalian::findOrFail($id);
        DB::transaction(function () use ($pengembalian) {
            $pengembalian->detailPengembalian()->delete();
            $pengembalian->denda()->delete();
            $pengembalian->delete();
        });

        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Data berhasil dihapus.']);
        }
        return redirect()->back()->with('success', 'Data riwayat pengembalian berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate(['ids' => 'required|array', 'ids.*' => 'integer|exists:pengembalian,id']);

        DB::transaction(function () use ($request) {
            $records = Pengembalian::whereIn('id', $request->ids)->get();
            foreach ($records as $pengembalian) {
                $pengembalian->detailPengembalian()->delete();
                $pengembalian->denda()->delete();
                $pengembalian->delete();
            }
        });

        $count = count($request->ids);
        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => "{$count} data berhasil dihapus."]);
        }
        return redirect()->back()->with('success', "{$count} data riwayat pengembalian berhasil dihapus.");
    }

    public function export(Request $request)
    {
        $query = Pengembalian::with(['anggota.kelas', 'user', 'detailPengembalian.buku.kategoriBuku', 'peminjaman.detailPeminjaman.buku']);

        if ($request->filled('tanggal_mulai')) {
            $query->where('tanggal_pengembalian', '>=', $request->tanggal_mulai);
        }
        if ($request->filled('tanggal_akhir')) {
            $query->where('tanggal_pengembalian', '<=', $request->tanggal_akhir);
        }
        if ($request->filled('status_terlambat')) {
            if ($request->status_terlambat == 'terlambat') {
                $query->where('jumlah_hari_terlambat', '>', 0);
            } else {
                $query->where('jumlah_hari_terlambat', '=', 0);
            }
        }
        if ($request->filled('anggota_id')) {
            $query->where('anggota_id', $request->anggota_id);
        }
        if ($request->filled('buku_id')) {
            $query->whereHas('detailPengembalian', function ($q) use ($request) {
                $q->where('buku_id', $request->buku_id);
            });
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_pengembalian', 'like', "%{$search}%")
                  ->orWhereHas('anggota', function ($q2) use ($search) {
                      $q2->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nomor_anggota', 'like', "%{$search}%");
                  });
            });
        }

        $pengembalian = $query->orderBy('tanggal_pengembalian', 'desc')->get();

        $filename = 'riwayat_pengembalian_' . date('Y-m-d_H-i-s') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($pengembalian) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Nomor Pengembalian', 'Nama Anggota', 'Nomor Anggota',
                'Tanggal Pengembalian', 'Jam Pengembalian', 'Jumlah Buku',
                'Jumlah Hari Terlambat', 'Total Denda', 'Petugas', 'Catatan']);
            $no = 1;
            foreach ($pengembalian as $return) {
                fputcsv($file, [
                    $no++,
                    $return->nomor_pengembalian,
                    $return->anggota->nama_lengkap ?? '',
                    $return->anggota->nomor_anggota ?? '',
                    $return->tanggal_pengembalian ? $return->tanggal_pengembalian->format('d/m/Y') : '',
                    $return->jam_pengembalian
                        ? ($return->jam_pengembalian instanceof \Carbon\Carbon
                            ? $return->jam_pengembalian->format('H:i')
                            : substr((string) $return->jam_pengembalian, 0, 5))
                        : '',
                    $return->detailPengembalian ? $return->detailPengembalian->sum('jumlah_dikembalikan') : 0,
                    $return->jumlah_hari_terlambat,
                    $return->total_denda,
                    $return->user->name ?? '',
                    $return->catatan ?? '',
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
