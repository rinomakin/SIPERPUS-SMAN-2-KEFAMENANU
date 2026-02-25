<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BukuTamu;
use App\Models\Anggota;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Barryvdh\DomPDF\Facade\Pdf;
use Yajra\DataTables\Facades\DataTables;

class BukuTamuController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,PETUGAS,KEPALA_SEKOLAH']);
    }

    public function index(Request $request)
    {
        // Only show today's visitors - resets every new day
        $query = BukuTamu::with(['anggota.kelas', 'anggota.jurusan'])
            ->whereDate('waktu_datang', today());

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_tamu', 'like', '%' . $search . '%')
                  ->orWhere('instansi', 'like', '%' . $search . '%')
                  ->orWhereHas('anggota', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', '%' . $search . '%')
                        ->orWhere('nomor_anggota', 'like', '%' . $search . '%');
                  });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'berkunjung') {
                $query->whereNull('waktu_pulang');
            } elseif ($request->status === 'pulang') {
                $query->whereNotNull('waktu_pulang');
            }
        }

        // Visitor type filter
        if ($request->filled('tipe_tamu')) {
            if ($request->tipe_tamu === 'anggota') {
                $query->whereNotNull('anggota_id');
            } elseif ($request->tipe_tamu === 'umum') {
                $query->whereNull('anggota_id');
            }
        }

        // Yang masih berkunjung (waktu_pulang NULL) tampil paling atas, lalu urutkan waktu datang terbaru
        $kunjunganHariIni = $query->orderByRaw('waktu_pulang IS NOT NULL ASC')
                                  ->orderBy('waktu_datang', 'desc')
                                  ->get();

        $totalTamuHariIni = BukuTamu::whereDate('waktu_datang', today())->count();
        $sedangBerkunjung = BukuTamu::whereDate('waktu_datang', today())->whereNull('waktu_pulang')->count();
        $sudahPulang = BukuTamu::whereDate('waktu_datang', today())->whereNotNull('waktu_pulang')->count();
        $tamuAnggota = BukuTamu::whereDate('waktu_datang', today())->whereNotNull('anggota_id')->count();
        $tamuUmum = BukuTamu::whereDate('waktu_datang', today())->whereNull('anggota_id')->count();

        return view('admin.buku-tamu.index', compact(
            'kunjunganHariIni', 'totalTamuHariIni', 'sedangBerkunjung', 'sudahPulang', 'tamuAnggota', 'tamuUmum'
        ));
    }

    public function create()
    {
        $anggota = Anggota::where('status', 'aktif')->get();
        return view('admin.buku-tamu.create', compact('anggota'));
    }


    public function store(Request $request)
    {
        $request->validate([
            'anggota_id' => 'nullable|exists:anggota,id',
            'nama_tamu' => 'required|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'keperluan' => 'required|string|max:255',
            'waktu_datang' => 'required|date',
            'no_telepon' => 'nullable|string|max:20',
            'status_kunjungan' => 'nullable|in:datang,pulang',
            'keterangan' => 'nullable|string|max:500',
        ]);

        if ($request->anggota_id) {
            $sudahBerkunjung = BukuTamu::where('anggota_id', $request->anggota_id)
                ->whereDate('waktu_datang', $request->waktu_datang)->exists();

            if ($sudahBerkunjung) {
                if ($request->ajax()) {
                    return response()->json([
                        'success' => false, 
                        'message' => 'Anggota sudah tercatat berkunjung pada tanggal yang sama.'
                    ]);
                }
                return back()->with('error', 'Anggota sudah tercatat berkunjung pada tanggal yang sama.')->withInput();
            }
            
            $anggota = Anggota::find($request->anggota_id);
            $namaTamu = $request->nama_tamu ?: $anggota->nama_lengkap;
        } else {
            $namaTamu = $request->nama_tamu;
        }

        BukuTamu::create([
            'anggota_id' => $request->anggota_id,
            'nama_tamu' => $namaTamu,
            'instansi' => $request->instansi,
            'keperluan' => $request->keperluan,
            'waktu_datang' => $request->waktu_datang,
            'no_telepon' => $request->no_telepon,
            'status_kunjungan' => $request->status_kunjungan ?: 'datang',
            'keterangan' => $request->keterangan,
            'petugas_id' => Auth::id(),
        ]);

        // Return JSON response for AJAX requests
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Kunjungan berhasil dicatat di buku tamu.'
            ]);
        }

        // Regular redirect for non-AJAX requests
        if (auth()->user()->hasRole('ADMIN')) {
            return redirect()->route('admin.buku-tamu.index')->with('success', 'Kunjungan berhasil dicatat di buku tamu.');
        } else {
            return redirect()->route('petugas.buku-tamu.index')->with('success', 'Kunjungan berhasil dicatat di buku tamu.');
        }
    }


    public function destroy($id)
    {
        $kunjungan = BukuTamu::findOrFail($id);
        $kunjungan->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'message' => 'Data kunjungan berhasil dihapus.']);
        }

        if (auth()->user()->hasRole('ADMIN')) {
            return redirect()->route('admin.buku-tamu.index')->with('success', 'Data kunjungan berhasil dihapus.');
        } else {
            return redirect()->route('petugas.buku-tamu.index')->with('success', 'Data kunjungan berhasil dihapus.');
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:buku_tamu,id',
        ]);

        $deleted = BukuTamu::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => $deleted . ' data kunjungan berhasil dihapus.',
        ]);
    }

    public function show($id)
    {
        $kunjungan = BukuTamu::with(['anggota.kelas', 'anggota.jurusan', 'petugas'])->findOrFail($id);
        return view('admin.buku-tamu.show', compact('kunjungan'));
    }

    public function edit($id)
    {
        $kunjungan = BukuTamu::with(['anggota.kelas', 'anggota.jurusan'])->findOrFail($id);
        $anggota = Anggota::where('status', 'aktif')->get();
        return view('admin.buku-tamu.edit', compact('kunjungan', 'anggota'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'anggota_id' => 'nullable|exists:anggota,id',
            'nama_tamu' => 'required_without:anggota_id|string|max:255',
            'instansi' => 'nullable|string|max:255',
            'keperluan' => 'required|string|max:255',
            'waktu_datang' => 'required|date',
            'no_telepon' => 'nullable|string|max:20',
            'keterangan' => 'nullable|string|max:500',
        ]);

        $kunjungan = BukuTamu::findOrFail($id);

        if ($request->anggota_id) {
            $existingVisit = BukuTamu::where('anggota_id', $request->anggota_id)
                ->whereDate('waktu_datang', $request->waktu_datang)
                ->where('id', '!=', $id)->exists();

            if ($existingVisit) {
                return back()->with('error', 'Anggota sudah memiliki catatan kunjungan pada tanggal yang sama.')->withInput();
            }
            
            $anggota = Anggota::find($request->anggota_id);
            $namaTamu = $request->nama_tamu ?: $anggota->nama_lengkap;
        } else {
            $namaTamu = $request->nama_tamu;
        }

        $kunjungan->update([
            'anggota_id' => $request->anggota_id,
            'nama_tamu' => $namaTamu,
            'instansi' => $request->instansi,
            'keperluan' => $request->keperluan,
            'waktu_datang' => $request->waktu_datang,
            'no_telepon' => $request->no_telepon,
            'keterangan' => $request->keterangan,
        ]);

        return redirect()->route('admin.buku-tamu.index')->with('success', 'Data kunjungan berhasil diperbarui.');
    }

    public function recordExit(Request $request)
    {
        $request->validate(['kunjungan_id' => 'required|exists:buku_tamu,id']);
        
        $kunjungan = BukuTamu::findOrFail($request->kunjungan_id);

        if ($kunjungan->waktu_pulang) {
            return response()->json(['success' => false, 'message' => 'Tamu sudah mencatat waktu pulang.']);
        }

        $kunjungan->update(['waktu_pulang' => now(), 'status_kunjungan' => 'pulang']);

        return response()->json([
            'success' => true,
            'message' => 'Waktu pulang berhasil dicatat.',
            'data' => ['waktu_pulang' => $kunjungan->waktu_pulang->format('H:i:s')]
        ]);
    }

    public function history(Request $request)
    {
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = BukuTamu::with(['anggota.kelas', 'anggota.jurusan']);
            
            // Apply filters from request
            if ($request->filled('filter_status')) {
                if ($request->filter_status === 'berkunjung') {
                    $query->whereNull('waktu_pulang');
                } elseif ($request->filter_status === 'pulang') {
                    $query->whereNotNull('waktu_pulang');
                }
            }
            
            if ($request->filled('filter_tipe')) {
                if ($request->filter_tipe === 'anggota') {
                    $query->whereNotNull('anggota_id');
                } elseif ($request->filter_tipe === 'umum') {
                    $query->whereNull('anggota_id');
                }
            }
            
            if ($request->filled('filter_tanggal_dari')) {
                $query->whereDate('waktu_datang', '>=', $request->filter_tanggal_dari);
            }
            
            if ($request->filled('filter_tanggal_sampai')) {
                $query->whereDate('waktu_datang', '<=', $request->filter_tanggal_sampai);
            }
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('tamu_info', function($row) {
                    $foto = $row->anggota && $row->anggota->foto 
                        ? asset('storage/' . $row->anggota->foto) 
                        : asset('images/default-avatar.png');
                    $nomor = $row->anggota ? $row->anggota->nomor_anggota : '-';
                    
                    return '<div class="flex items-center">
                                <img src="' . $foto . '" alt="Foto" class="h-10 w-10 rounded-full object-cover border-2 border-gray-200 mr-3" onerror="this.src=\'' . asset('images/default-avatar.png') . '\'">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">' . e($row->nama_tamu) . '</div>
                                    <div class="text-xs text-gray-500">' . e($nomor) . '</div>
                                </div>
                            </div>';
                })
                ->addColumn('tipe_badge', function($row) {
                    $isMember = !is_null($row->anggota_id);
                    if ($isMember) {
                        return '<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Anggota</span>';
                    }
                    return '<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Tamu Umum</span>';
                })
                ->addColumn('kelas_instansi', function($row) {
                    if ($row->anggota && $row->anggota->kelas) {
                        $html = '<div>' . e($row->anggota->kelas->nama_kelas) . '</div>';
                        if ($row->anggota->jurusan) {
                            $html .= '<div class="text-xs text-gray-500">' . e($row->anggota->jurusan->nama_jurusan) . '</div>';
                        }
                        return $html;
                    }
                    return e($row->instansi ?? '-');
                })
                ->addColumn('waktu_datang_info', function($row) {
                    return $row->waktu_datang ? $row->waktu_datang->format('d M Y H:i') : '-';
                })
                ->addColumn('waktu_pulang_info', function($row) {
                    if ($row->waktu_pulang) {
                        $html = $row->waktu_pulang->format('d M Y H:i');
                        $durasi = $row->waktu_datang->diffForHumans($row->waktu_pulang, true);
                        $html .= '<div class="text-xs text-gray-500">' . $durasi . '</div>';
                        return $html;
                    }
                    return '<span class="text-gray-400">-</span>';
                })
                ->addColumn('keperluan_info', function($row) {
                    return '<div class="max-w-xs truncate" title="' . e($row->keperluan) . '">' . e($row->keperluan) . '</div>';
                })
                ->addColumn('status_badge', function($row) {
                    if ($row->waktu_pulang) {
                        return '<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Sudah Pulang</span>';
                    }
                    return '<span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Sedang Berkunjung</span>';
                })
                ->addColumn('action', function($row) {
                    return '<div class="flex items-center gap-1">
                                <a href="' . route('admin.buku-tamu.show', $row->id) . '" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 transition-colors" title="Detail">
                                    <i class="fas fa-eye text-xs"></i>
                                </a>
                                <a href="' . route('admin.buku-tamu.edit', $row->id) . '" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-100 transition-colors" title="Edit">
                                    <i class="fas fa-edit text-xs"></i>
                                </a>
                                <button onclick="hapusData(' . $row->id . ')" class="w-8 h-8 inline-flex items-center justify-center rounded-lg bg-red-50 text-red-600 hover:bg-red-100 transition-colors" title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </div>';
                })
                ->rawColumns(['tamu_info', 'tipe_badge', 'kelas_instansi', 'waktu_pulang_info', 'keperluan_info', 'status_badge', 'action'])
                ->make(true);
        }

        return view('admin.buku-tamu.history');
    }

    public function historySearch(Request $request)
    {
        $query = BukuTamu::with(['anggota.kelas', 'anggota.jurusan', 'petugas']);

        // Date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('waktu_datang', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('waktu_datang', '<=', $request->end_date);
        }

        // Time range filter
        if ($request->filled('start_time')) {
            $query->whereTime('waktu_datang', '>=', $request->start_time);
        }
        if ($request->filled('end_time')) {
            $query->whereTime('waktu_datang', '<=', $request->end_time);
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'berkunjung') {
                $query->whereNull('waktu_pulang');
            } elseif ($request->status === 'pulang') {
                $query->whereNotNull('waktu_pulang');
            }
            // 'semua' = no filter
        }

        // Visitor type filter
        if ($request->filled('tipe_tamu')) {
            if ($request->tipe_tamu === 'anggota') {
                $query->whereNotNull('anggota_id');
            } elseif ($request->tipe_tamu === 'umum') {
                $query->whereNull('anggota_id');
            }
            // 'semua' = no filter
        }

        // Search filter (name, member number, institution)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_tamu', 'like', '%' . $search . '%')
                  ->orWhere('instansi', 'like', '%' . $search . '%')
                  ->orWhereHas('anggota', function($q) use ($search) {
                      $q->where('nama_lengkap', 'like', '%' . $search . '%')
                        ->orWhere('nomor_anggota', 'like', '%' . $search . '%');
                  });
            });
        }

        // Order by most recent first
        $query->orderBy('waktu_datang', 'desc');

        // Pagination
        $perPage = 15;
        $kunjungan = $query->paginate($perPage);

        // Format data for response
        $data = $kunjungan->map(function ($item) {
            $isMember = !is_null($item->anggota_id);
            
            return [
                'id' => $item->id,
                'nama_tamu' => $item->nama_tamu,
                'foto' => $isMember && $item->anggota && $item->anggota->foto 
                    ? asset('storage/' . $item->anggota->foto) 
                    : asset('images/default-avatar.png'),
                'tipe' => $isMember ? 'Anggota' : 'Tamu Umum',
                'nomor_anggota' => $isMember && $item->anggota ? $item->anggota->nomor_anggota : '-',
                'kelas' => $isMember && $item->anggota && $item->anggota->kelas 
                    ? $item->anggota->kelas->nama_kelas 
                    : ($item->instansi ?? '-'),
                'jurusan' => $isMember && $item->anggota && $item->anggota->jurusan 
                    ? $item->anggota->jurusan->nama_jurusan 
                    : '',
                'waktu_datang' => $item->waktu_datang->format('d M Y H:i'),
                'waktu_pulang' => $item->waktu_pulang ? $item->waktu_pulang->format('d M Y H:i') : null,
                'durasi' => $item->waktu_pulang 
                    ? $item->waktu_datang->diffForHumans($item->waktu_pulang, true) 
                    : null,
                'keperluan' => $item->keperluan,
                'status' => $item->waktu_pulang ? 'Sudah Pulang' : 'Sedang Berkunjung',
                'no_telepon' => $item->no_telepon ?? '-',
            ];
        });

        // Statistics
        $statistics = [
            'total' => BukuTamu::count(),
            'today' => BukuTamu::whereDate('waktu_datang', today())->count(),
            'week' => BukuTamu::whereBetween('waktu_datang', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month' => BukuTamu::whereMonth('waktu_datang', now()->month)->whereYear('waktu_datang', now()->year)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $kunjungan->currentPage(),
                'last_page' => $kunjungan->lastPage(),
                'per_page' => $kunjungan->perPage(),
                'total' => $kunjungan->total(),
            ],
            'statistics' => $statistics,
        ]);
    }


    public function searchMembers(Request $request)
    {
        $query = $request->get('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['success' => false, 'message' => 'Minimal 2 karakter untuk pencarian']);
        }

        // Get IDs of members who are currently visiting (haven't left yet today)
        $currentlyVisitingMemberIds = BukuTamu::whereDate('waktu_datang', today())
            ->whereNull('waktu_pulang')
            ->whereNotNull('anggota_id')
            ->pluck('anggota_id')
            ->toArray();

        $members = Anggota::where('status', 'aktif')
            ->where(function($q) use ($query) {
                $q->where('nama_lengkap', 'like', '%' . $query . '%')
                  ->orWhere('nomor_anggota', 'like', '%' . $query . '%')
                  ->orWhere('barcode_anggota', 'like', '%' . $query . '%');
            })
            // Exclude members who are currently visiting
            ->whereNotIn('id', $currentlyVisitingMemberIds)
            ->with(['kelas', 'jurusan'])->limit(10)->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'nama_lengkap' => $member->nama_lengkap,
                    'nomor_anggota' => $member->nomor_anggota,
                    'barcode_anggota' => $member->barcode_anggota,
                    'kelas' => $member->kelas ? $member->kelas->nama_kelas : '-',
                    'jurusan' => $member->jurusan ? $member->jurusan->nama_jurusan : '-',
                    'status' => $member->status,
                    'foto' => $member->foto ? asset('storage/' . $member->foto) : null
                ];
            });

        return response()->json(['success' => true, 'data' => $members]);
    }

    public function scanBarcode(Request $request)
    {
        $request->validate(['barcode' => 'required|string']);
        
        $member = Anggota::where('status', 'aktif')
            ->where(function($q) use ($request) {
                $q->where('barcode_anggota', $request->barcode)
                  ->orWhere('nomor_anggota', $request->barcode);
            })
            ->with(['kelas', 'jurusan'])->first();

        if (!$member) {
            return response()->json(['success' => false, 'message' => 'Anggota tidak ditemukan']);
        }

        // Check if member already visited today
        $existingVisit = BukuTamu::where('anggota_id', $member->id)
            ->whereDate('waktu_datang', today())->exists();

        if ($existingVisit) {
            return response()->json(['success' => false, 'message' => 'Anggota sudah tercatat berkunjung hari ini']);
        }

        $memberData = [
            'id' => $member->id,
            'nama_lengkap' => $member->nama_lengkap,
            'nomor_anggota' => $member->nomor_anggota,
            'barcode_anggota' => $member->barcode_anggota,
            'kelas' => $member->kelas ? $member->kelas->nama_kelas : '-',
            'jurusan' => $member->jurusan ? $member->jurusan->nama_jurusan : '-',
            'status' => $member->status,
            'foto' => $member->foto ? asset('storage/' . $member->foto) : null
        ];

        return response()->json(['success' => true, 'data' => $memberData, 'message' => 'Anggota ditemukan']);
    }

    public function searchHistory(Request $request)
    {
        $query = BukuTamu::with(['anggota.kelas', 'anggota.jurusan']);

        // Apply filters
        if ($request->startDate) {
            $query->whereDate('waktu_datang', '>=', $request->startDate);
        }

        if ($request->endDate) {
            $query->whereDate('waktu_datang', '<=', $request->endDate);
        }

        if ($request->member) {
            $query->whereHas('anggota', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->member . '%')
                  ->orWhere('nomor_anggota', 'like', '%' . $request->member . '%');
            });
        }

        $page = $request->get('page', 1);
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $total = $query->count();
        $data = $query->orderBy('waktu_datang', 'desc')
                     ->offset($offset)
                     ->limit($perPage)
                     ->get()
                     ->map(function($item) {
                         return [
                             'id' => $item->id,
                             'anggota' => [
                                 'nama_lengkap' => $item->anggota->nama_lengkap ?? $item->nama_tamu,
                                 'nomor_anggota' => $item->anggota->nomor_anggota ?? '-',
                                 'foto' => $item->anggota->foto ? asset('storage/' . $item->anggota->foto) : null,
                                 'kelas' => $item->anggota->kelas->nama_kelas ?? '-',
                             ],
                             'waktu_masuk' => $item->waktu_datang->format('d/m/Y H:i'),
                             'waktu_keluar' => $item->waktu_pulang ? $item->waktu_pulang->format('d/m/Y H:i') : null,
                             'durasi' => $item->waktu_pulang ? $item->waktu_datang->diff($item->waktu_pulang)->format('%H:%I:%S') : null,
                             'status' => $item->waktu_pulang ? 'Selesai' : 'Berkunjung',
                             'keterangan' => $item->keterangan,
                         ];
                     });

        $lastPage = ceil($total / $perPage);

        $statistics = [
            'total' => BukuTamu::count(),
            'today' => BukuTamu::whereDate('waktu_datang', today())->count(),
            'week' => BukuTamu::whereBetween('waktu_datang', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'month' => BukuTamu::whereMonth('waktu_datang', now()->month)->whereYear('waktu_datang', now()->year)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'last_page' => $lastPage,
                'total' => $total
            ],
            'statistics' => $statistics
        ]);
    }

    public function exportExcel(Request $request)
    {
        $query = BukuTamu::with(['anggota.kelas', 'anggota.jurusan']);

        // Apply same filters as search
        if ($request->startDate) {
            $query->whereDate('waktu_datang', '>=', $request->startDate);
        }
        if ($request->endDate) {
            $query->whereDate('waktu_datang', '<=', $request->endDate);
        }
        if ($request->member) {
            $query->whereHas('anggota', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->member . '%');
            });
        }

        $data = $query->orderBy('waktu_datang', 'desc')->get();

        return Excel::download(new class($data) implements FromCollection, WithHeadings, WithMapping {
            private $data;
            
            public function __construct($data) {
                $this->data = $data;
            }
            
            public function collection() {
                return $this->data;
            }
            
            public function headings(): array {
                return ['Tanggal', 'Nama', 'Nomor Anggota', 'Kelas', 'Keperluan', 'Waktu Datang', 'Waktu Pulang', 'Status'];
            }
            
            public function map($row): array {
                return [
                    $row->waktu_datang->format('d/m/Y'),
                    $row->anggota->nama_lengkap ?? $row->nama_tamu,
                    $row->anggota->nomor_anggota ?? '-',
                    $row->anggota->kelas->nama_kelas ?? '-',
                    $row->keperluan,
                    $row->waktu_datang->format('H:i'),
                    $row->waktu_pulang ? $row->waktu_pulang->format('H:i') : '-',
                    $row->waktu_pulang ? 'Selesai' : 'Berkunjung'
                ];
            }
        }, 'riwayat-buku-tamu-' . now()->format('Y-m-d') . '.xlsx');
    }

    public function exportPdf(Request $request)
    {
        $query = BukuTamu::with(['anggota.kelas', 'anggota.jurusan']);

        // Apply same filters as search
        if ($request->startDate) {
            $query->whereDate('waktu_datang', '>=', $request->startDate);
        }
        if ($request->endDate) {
            $query->whereDate('waktu_datang', '<=', $request->endDate);
        }
        if ($request->member) {
            $query->whereHas('anggota', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%' . $request->member . '%');
            });
        }

        $data = $query->orderBy('waktu_datang', 'desc')->get();
        
        $pdf = Pdf::loadView('admin.buku-tamu.pdf.history', compact('data'));
        
        return $pdf->download('riwayat-buku-tamu-' . now()->format('Y-m-d') . '.pdf');
    }
}