<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kelas;
use App\Models\Jurusan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AnggotaExport;
use App\Exports\AnggotaTemplateExport;
use App\Imports\AnggotaImport;
use Yajra\DataTables\Facades\DataTables;

class AnggotaController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:ADMIN,KEPALA_SEKOLAH,PETUGAS']);
        $this->middleware('permission:anggota.create')->only(['create', 'store']);
        $this->middleware('permission:anggota.edit')->only(['edit', 'update']);
        $this->middleware('permission:anggota.delete')->only(['destroy', 'bulkDelete']);
    }

    public function index(Request $request)
    {
        // Handle DataTables AJAX request
        if ($request->ajax()) {
            $query = Anggota::with(['kelas.jurusan'])->orderBy('nama_lengkap', 'asc');
            
            // Apply filters from request
            if ($request->filled('filter_kelas_id')) {
                $query->where('kelas_id', $request->filter_kelas_id);
            }
            
            if ($request->filled('filter_jurusan_id')) {
                $query->whereHas('kelas', function($q) use ($request) {
                    $q->where('jurusan_id', $request->filter_jurusan_id);
                });
            }
            
            if ($request->filled('filter_jenis_anggota')) {
                $query->where('jenis_anggota', $request->filter_jenis_anggota);
            }
            
            if ($request->filled('filter_status')) {
                $query->where('status', $request->filter_status);
            }
            
            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('checkbox', function($row) {
                    return '<input type="checkbox" class="member-checkbox w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 transition-all duration-200" value="' . $row->id . '">';
                })
                ->addColumn('nama_info', function($row) {
                    $gradients = ['#f97316,#ef4444','#8b5cf6,#6366f1','#10b981,#059669','#3b82f6,#2563eb','#ec4899,#db2777'];
                    $gradient = $gradients[($row->id ?? 0) % 5];
                    $initial = strtoupper(substr($row->nama_lengkap ?? 'N', 0, 1));

                    if ($row->foto) {
                        $foto = '<div class="avatar-container">'
                            . '<img src="' . asset('storage/anggota/' . $row->foto) . '" alt="Foto" class="avatar-img"'
                            . ' onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'flex\';">'
                            . '<div class="avatar-initial" style="display:none;background:linear-gradient(135deg,' . $gradient . ');">' . $initial . '</div>'
                            . '</div>';
                    } else {
                        $foto = '<div class="avatar-container">'
                            . '<div class="avatar-initial" style="background:linear-gradient(135deg,' . $gradient . ');">' . $initial . '</div>'
                            . '</div>';
                    }

                    return '<div class="flex items-center gap-3">' . $foto . '
                        <div>
                            <div class="text-sm font-semibold text-gray-900">' . e($row->nama_lengkap) . '</div>
                            <div class="text-xs text-gray-400">' . ($row->email ?: '-') . '</div>
                        </div>
                    </div>';
                })
                ->addColumn('jenis_kelamin_badge', function($row) {
                    if ($row->jenis_kelamin == 'Laki-laki') {
                        return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                            <i class="fas fa-mars"></i>Laki-laki</span>';
                    }
                    return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium bg-pink-50 text-pink-700 border border-pink-100">
                        <i class="fas fa-venus"></i>' . ($row->jenis_kelamin ?: '-') . '</span>';
                })
                ->addColumn('kelas_info', function($row) {
                    if ($row->kelas) {
                        return '<div>
                            <div class="text-sm font-medium text-gray-800">' . e($row->kelas->nama_kelas) . '</div>
                            <div class="text-[11px] text-gray-400">' . e($row->kelas->jurusan->nama_jurusan ?? '-') . '</div>
                        </div>';
                    }
                    return '<span class="text-xs text-gray-400">-</span>';
                })
                ->addColumn('jenis_badge', function($row) {
                    $config = match($row->jenis_anggota) {
                        'siswa' => ['bg-blue-50 text-blue-700 border-blue-100', 'fa-user-graduate'],
                        'guru' => ['bg-emerald-50 text-emerald-700 border-emerald-100', 'fa-chalkboard-teacher'],
                        default => ['bg-purple-50 text-purple-700 border-purple-100', 'fa-user-tie']
                    };
                    return '<span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-medium ' . $config[0] . ' border">
                        <i class="fas ' . $config[1] . ' text-[10px]"></i>' . ucfirst($row->jenis_anggota) . '</span>';
                })
                ->addColumn('status_badge', function($row) {
                    $config = match($row->status) {
                        'aktif' => ['bg-emerald-50 text-emerald-700 border-emerald-200', 'bg-emerald-500'],
                        'nonaktif' => ['bg-red-50 text-red-700 border-red-200', 'bg-red-500'],
                        default => ['bg-amber-50 text-amber-700 border-amber-200', 'bg-amber-500']
                    };
                    return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium ' . $config[0] . ' border">
                        <span class="w-1.5 h-1.5 rounded-full ' . $config[1] . '"></span>' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function($row) {
                    $actions = '<div class="flex items-center justify-center gap-1.5">';

                    if (auth()->user()->hasPermission('anggota.view') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('anggota.show', $row->id) . '" class="action-btn action-btn-view" title="Lihat Detail"><i class="fas fa-eye"></i></a>';
                    }

                    if (auth()->user()->hasPermission('anggota.edit') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('anggota.edit', $row->id) . '" class="action-btn action-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>';
                    }

                    if (auth()->user()->hasPermission('anggota.cetak-kartu') || auth()->user()->isAdmin()) {
                        $actions .= '<a href="' . route('anggota.cetak-kartu', $row->id) . '" class="action-btn action-btn-print" title="Cetak Kartu" target="_blank"><i class="fas fa-print"></i></a>';
                    }

                    if (auth()->user()->hasPermission('anggota.delete') || auth()->user()->isAdmin()) {
                        $actions .= '<button onclick="confirmDeleteAnggota(' . $row->id . ')" class="action-btn action-btn-delete" title="Hapus"><i class="fas fa-trash-alt"></i></button>';
                    }

                    $actions .= '</div>';
                    return $actions;
                })
                ->rawColumns(['checkbox', 'nama_info', 'jenis_kelamin_badge', 'kelas_info', 'jenis_badge', 'status_badge', 'action'])
                ->make(true);
        }
        
        // Regular view request
        $kelas = Kelas::with('jurusan')->get();
        $jurusan = Jurusan::all();

        return view('admin.anggota.index', compact('kelas', 'jurusan'));
    }

    public function create()
    {
        $kelas = Kelas::with('jurusan')->get();
        return view('admin.anggota.create', compact('kelas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'kelas_id' => 'nullable|exists:kelas,id',
            'jabatan' => 'nullable|string|max:255',
            'jenis_anggota' => 'required|in:siswa,guru,staff',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:aktif,nonaktif,ditangguhkan',
            'tanggal_bergabung' => 'required|date',
            'barcode_anggota' => 'required|string|unique:anggota,barcode_anggota',
        ]);

        DB::beginTransaction();
        try {
            // Generate nomor anggota otomatis
            $nomorAnggota = Anggota::generateNomorAnggota();

            $data = $request->all();
            $data['nomor_anggota'] = $nomorAnggota;

            // Handle foto upload
            if ($request->hasFile('foto')) {
                $foto = $request->file('foto');
                $fotoName = time() . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
                $foto->move(public_path('storage/anggota'), $fotoName);
                $data['foto'] = $fotoName;
            }

            Anggota::create($data);
            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Data anggota berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $anggota = Anggota::with(['kelas.jurusan', 'peminjaman', 'denda'])->findOrFail($id);
        return view('admin.anggota.show', compact('anggota'));
    }

    public function edit($id)
    {
        $anggota = Anggota::findOrFail($id);
        $kelas = Kelas::with('jurusan')->get();
        return view('admin.anggota.edit', compact('anggota', 'kelas'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'alamat' => 'required|string',
            'nomor_telepon' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'kelas_id' => 'nullable|exists:kelas,id',
            'jabatan' => 'nullable|string|max:255',
            'jenis_anggota' => 'required|in:siswa,guru,staff',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status' => 'required|in:aktif,nonaktif,ditangguhkan',
            'tanggal_bergabung' => 'required|date',
        ]);

        DB::beginTransaction();
        try {
            $anggota = Anggota::findOrFail($id);
            $data = $request->all();

            // Handle barcode - if empty, keep current barcode
            if (empty($data['barcode_anggota'])) {
                unset($data['barcode_anggota']);
            }

            // Handle foto upload
            if ($request->hasFile('foto')) {
                // Delete old foto if exists
                if ($anggota->foto && file_exists(public_path('storage/anggota/' . $anggota->foto))) {
                    unlink(public_path('storage/anggota/' . $anggota->foto));
                }

                $foto = $request->file('foto');
                $fotoName = time() . '_' . Str::random(10) . '.' . $foto->getClientOriginalExtension();
                $foto->move(public_path('storage/anggota'), $fotoName);
                $data['foto'] = $fotoName;
            }

            $anggota->update($data);
            DB::commit();

            return redirect()->route('anggota.index')
                ->with('success', 'Data anggota berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $anggota = Anggota::findOrFail($id);
            
            // Delete foto if exists
            if ($anggota->foto && file_exists(public_path('storage/anggota/' . $anggota->foto))) {
                unlink(public_path('storage/anggota/' . $anggota->foto));
            }
            
            $anggota->delete();
            
            return redirect()->route('anggota.index')
                ->with('success', 'Data anggota berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:anggota,id'
        ]);

        DB::beginTransaction();
        try {
            $anggota = Anggota::whereIn('id', $request->ids)->get();
            
            foreach ($anggota as $item) {
                if ($item->foto && file_exists(public_path('storage/anggota/' . $item->foto))) {
                    unlink(public_path('storage/anggota/' . $item->foto));
                }
            }
            
            Anggota::whereIn('id', $request->ids)->delete();
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => count($request->ids) . ' data anggota berhasil dihapus.'
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    public function export(Request $request)
    {
        $filename = 'anggota_' . date('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new AnggotaExport($request), $filename);
    }

    public function downloadTemplate()
    {
        $filename = 'template_import_anggota.xlsx';
        
        return Excel::download(new AnggotaTemplateExport(), $filename);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            // Clean existing duplicates before import
            $cleanedCount = Anggota::cleanAndRegenerateDuplicates();
            
            $import = new AnggotaImport();
            Excel::import($import, $request->file('file'));

            $importedCount = $import->getImportedCount();
            $errors = $import->getErrors();
            
            if ($importedCount > 0) {
                $message = "✅ Berhasil mengimpor {$importedCount} data anggota.";
                
                if ($cleanedCount > 0) {
                    $message .= " 🔧 {$cleanedCount} data duplikasi telah dibersihkan.";
                }
                
                if (!empty($errors)) {
                    $uniqueErrors = array_unique($errors);
                    $errorCount = count($uniqueErrors);
                    
                    $displayErrors = array_slice($uniqueErrors, 0, 3);
                    foreach ($displayErrors as $error) {
                        $message .= "\n• " . $error;
                    }
                    
                    if ($errorCount > 3) {
                        $message .= "\n• Dan " . ($errorCount - 3) . " error lainnya.";
                    }
                }
                
                return redirect()->route('anggota.index')
                    ->with('success', $message);
            } else {
                $errorMessage = '❌ Tidak ada data yang berhasil diimpor.';
                if (!empty($errors)) {
                    $uniqueErrors = array_unique($errors);
                    $errorCount = count($uniqueErrors);
                    $errorMessage .= "\n\nDitemukan {$errorCount} jenis error:";
                    
                    // Tampilkan hanya 5 error pertama
                    $displayErrors = array_slice($uniqueErrors, 0, 5);
                    foreach ($displayErrors as $error) {
                        $errorMessage .= "\n• " . $error;
                    }
                    
                    if ($errorCount > 5) {
                        $errorMessage .= "\n• Dan " . ($errorCount - 5) . " error lainnya.";
                    }
                }
                
                return redirect()->back()
                    ->with('error', $errorMessage);
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', '❌ Terjadi kesalahan saat import: ' . $e->getMessage());
        }
    }

    public function cetakKartu($id)
    {
        $anggota = Anggota::with(['kelas.jurusan'])->findOrFail($id);
        return view('admin.anggota.cetak-kartu', compact('anggota'));
    }

    public function bulkPrintKartu(Request $request)
    {
        $ids = explode(',', $request->ids);
        $anggotaList = Anggota::whereIn('id', $ids)->get();
        
        return view('admin.anggota.bulk-print-kartu', compact('anggotaList'));
    }

    public function allIds(Request $request)
    {
        $query = Anggota::query();

        if ($request->filled('filter_kelas_id')) {
            $query->where('kelas_id', $request->filter_kelas_id);
        }
        if ($request->filled('filter_jurusan_id')) {
            $query->whereHas('kelas', function($q) use ($request) {
                $q->where('jurusan_id', $request->filter_jurusan_id);
            });
        }
        if ($request->filled('filter_jenis_anggota')) {
            $query->where('jenis_anggota', $request->filter_jenis_anggota);
        }
        if ($request->filled('filter_status')) {
            $query->where('status', $request->filter_status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nomor_anggota', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $ids = $query->pluck('id')->toArray();

        return response()->json(['ids' => $ids]);
    }

    public function scanBarcode(Request $request)
    {
        try {
            $request->validate([
                'barcode' => 'required|string'
            ]);

            $barcode = $request->barcode;
            $anggota = Anggota::where('barcode_anggota', $barcode)
                              ->orWhere('nomor_anggota', $barcode)
                              ->with('kelas')
                              ->first();

            if ($anggota) {
                if ($anggota->status !== 'aktif') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Status anggota tidak aktif'
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'data' => [
                        'id' => $anggota->id,
                        'nama_lengkap' => $anggota->nama_lengkap,
                        'nomor_anggota' => $anggota->nomor_anggota,
                        'barcode_anggota' => $anggota->barcode_anggota,
                        'kelas' => $anggota->kelas ? $anggota->kelas->nama_kelas : 'N/A',
                        'jenis_anggota' => $anggota->jenis_anggota,
                        'status' => $anggota->status
                    ]
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Anggota dengan barcode tersebut tidak ditemukan'
                ]);
            }
        } catch (\Exception $e) {
            \Log::error('Error in scanBarcode: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat scan barcode: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateBarcode(Request $request)
    {
        $request->validate([
            'code' => 'required|string'
        ]);

        try {
            $barcodeImage = \App\Helpers\BarcodeHelper::generateBarcodeImage($request->code, 'C128');
            return response()->json([
                'success' => true,
                'barcode' => $barcodeImage
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Gagal generate barcode'
            ]);
        }
    }

    // Method untuk membersihkan data duplikasi
    public function cleanDuplicateData()
    {
        try {
            $cleaned = Anggota::cleanDuplicateData();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil membersihkan {$cleaned} data duplikasi."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Method untuk regenerate kode yang duplikasi
    public function regenerateDuplicateCodes()
    {
        try {
            $regenerated = Anggota::regenerateDuplicateCodes();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil regenerate {$regenerated} kode duplikasi."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Method untuk membersihkan dan regenerate data duplikasi
    public function cleanAndRegenerateDuplicates()
    {
        try {
            $processed = Anggota::cleanAndRegenerateDuplicates();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil membersihkan dan regenerate {$processed} data duplikasi."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Method untuk mengecek data duplikasi
    public function checkDuplicateData()
    {
        try {
            $duplicateNomor = Anggota::select('nomor_anggota')
                ->groupBy('nomor_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            $duplicateBarcode = Anggota::select('barcode_anggota')
                ->groupBy('barcode_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'duplicate_nomor' => $duplicateNomor,
                    'duplicate_barcode' => $duplicateBarcode,
                    'total_duplicates' => $duplicateNomor + $duplicateBarcode
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }

    // Method untuk menampilkan statistik import
    public function getImportStats()
    {
        try {
            $totalAnggota = Anggota::count();
            $duplicateNomor = Anggota::select('nomor_anggota')
                ->groupBy('nomor_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            $duplicateBarcode = Anggota::select('barcode_anggota')
                ->groupBy('barcode_anggota')
                ->havingRaw('COUNT(*) > 1')
                ->count();
            return response()->json([
                'success' => true,
                'data' => [
                    'total_anggota' => $totalAnggota,
                    'duplicate_nomor' => $duplicateNomor,
                    'duplicate_barcode' => $duplicateBarcode,
                    'total_duplicates' => $duplicateNomor + $duplicateBarcode,
                    'clean_data' => $totalAnggota - ($duplicateNomor + $duplicateBarcode)
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Terjadi kesalahan: ' . $e->getMessage()
            ]);
        }
    }
} 