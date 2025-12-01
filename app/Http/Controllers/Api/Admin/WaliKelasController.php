<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\WaliKelas;
use App\Models\Guru;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * WaliKelasController
 * 
 * Handles CRUD operations for wali kelas assignments (admin only)
 */
class WaliKelasController extends Controller
{
    /**
     * Display a listing of wali kelas.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = WaliKelas::with(['guru.user', 'kelas.jurusan', 'kelas.siswa'])
                          ->where('is_active', true);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('guru', function ($qu) use ($search) {
                    $qu->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nuptk', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($qus) use ($search) {
                          $qus->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                      });
                })
                ->orWhereHas('kelas', function ($qu) use ($search) {
                    $qu->where('nama_kelas', 'like', "%{$search}%")
                      ->orWhereHas('jurusan', function ($qj) use ($search) {
                          $qj->where('nama_jurusan', 'like', "%{$search}%");
                      });
                });
            });
        }

        if ($request->has('kelas_id') && $request->kelas_id) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->has('guru_id') && $request->guru_id) {
            $query->where('guru_id', $request->guru_id);
        }

        if ($request->has('jurusan_id') && $request->jurusan_id) {
            $query->whereHas('kelas', function ($q) use ($request) {
                $q->where('jurusan_id', $request->jurusan_id);
            });
        }

        $waliKelas = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        // Transform data to group by guru
        $grouped = $waliKelas->getCollection()->groupBy('guru_id')->map(function ($items, $guruId) {
            $first = $items->first();
            $guru = $first->guru;
            $user = $guru->user;
            
            return [
                'id' => $user->id,
                'guru_id' => $guru->id,
                'name' => $user->name,
                'email' => $user->email,
                'guru' => $guru,
                'kelas_as_wali' => $items->map(function ($item) {
                    return $item->kelas;
                }),
                'total_kelas' => $items->count(),
                'total_siswa' => $items->sum(function ($item) {
                    return $item->kelas->siswa()->where('status', 'aktif')->count();
                }),
            ];
        })->values();

        return response()->json([
            'data' => $grouped,
            'current_page' => $waliKelas->currentPage(),
            'last_page' => $waliKelas->lastPage(),
            'per_page' => $waliKelas->perPage(),
            'total' => $grouped->count(),
        ]);
    }

    /**
     * Store a newly created wali kelas assignment.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => ['required', 'exists:guru,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
        ]);

        $guru = Guru::find($request->guru_id);
        $kelas = Kelas::find($request->kelas_id);

        // Validate user role
        if (!$guru->user || !in_array($guru->user->role, ['wali_kelas', 'guru', 'kepala_sekolah'])) {
            return response()->json([
                'message' => 'Guru yang dipilih harus memiliki role wali kelas, guru, atau kepala sekolah',
            ], 422);
        }

        // Check if kelas already has active wali kelas
        $existingWaliKelas = WaliKelas::where('kelas_id', $request->kelas_id)
                                     ->where('is_active', true)
                                     ->first();

        if ($existingWaliKelas) {
            return response()->json([
                'message' => 'Kelas sudah memiliki wali kelas aktif. Nonaktifkan wali kelas yang ada terlebih dahulu.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $waliKelas = WaliKelas::create([
                'guru_id' => $request->guru_id,
                'kelas_id' => $request->kelas_id,
                'tanggal_mulai' => $request->tanggal_mulai ?? now(),
                'tanggal_selesai' => $request->tanggal_selesai,
                'is_active' => true,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Wali kelas berhasil ditetapkan',
                'data' => $waliKelas->load(['guru.user', 'kelas.jurusan']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menetapkan wali kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified wali kelas.
     *
     * @param  WaliKelas  $waliKelas
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(WaliKelas $waliKelas)
    {
        $waliKelas->load([
            'guru.user',
            'kelas.jurusan',
            'kelas.siswa.user',
        ]);

        return response()->json($waliKelas);
    }

    /**
     * Update wali kelas assignment.
     *
     * @param  Request  $request
     * @param  WaliKelas  $waliKelas
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, WaliKelas $waliKelas)
    {
        $request->validate([
            'guru_id' => ['sometimes', 'exists:guru,id'],
            'kelas_id' => ['sometimes', 'exists:kelas,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after:tanggal_mulai'],
            'is_active' => ['sometimes', 'boolean'],
            'keterangan' => ['nullable', 'string'],
        ]);

        // If changing kelas_id, check if new kelas already has active wali kelas
        if ($request->has('kelas_id') && $request->kelas_id != $waliKelas->kelas_id) {
            $existingWaliKelas = WaliKelas::where('kelas_id', $request->kelas_id)
                                         ->where('is_active', true)
                                         ->where('id', '!=', $waliKelas->id)
                                         ->first();

            if ($existingWaliKelas) {
                return response()->json([
                    'message' => 'Kelas sudah memiliki wali kelas aktif.',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $waliKelas->update($request->only([
                'guru_id',
                'kelas_id',
                'tanggal_mulai',
                'tanggal_selesai',
                'is_active',
                'keterangan',
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Wali kelas berhasil diperbarui',
                'data' => $waliKelas->fresh()->load(['guru.user', 'kelas.jurusan']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui wali kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove wali kelas assignment.
     *
     * @param  WaliKelas  $waliKelas
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(WaliKelas $waliKelas)
    {
        DB::beginTransaction();
        try {
            $waliKelas->delete();

            DB::commit();

            return response()->json([
                'message' => 'Wali kelas berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus wali kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get kelas for a specific guru (wali kelas).
     *
     * @param  Guru  $guru
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKelas(Guru $guru)
    {
        $waliKelas = $guru->waliKelasAktif()
                          ->with(['kelas.jurusan', 'kelas.siswa.user'])
                          ->get();

        $kelas = $waliKelas->map(function ($wk) {
            $k = $wk->kelas;
            $k->active_siswa_count = $k->activeSiswa()->count();
            $k->is_full = $k->is_full;
            $k->available_capacity = $k->available_capacity;
            return $k;
        });

        return response()->json($kelas);
    }

    /**
     * Assign wali kelas to kelas (alternative endpoint).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function assign(Request $request)
    {
        $request->validate([
            'guru_id' => ['required', 'exists:guru,id'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tanggal_mulai' => ['nullable', 'date'],
            'tanggal_selesai' => ['nullable', 'date', 'after:tanggal_mulai'],
            'keterangan' => ['nullable', 'string'],
        ]);

        return $this->store($request);
    }

    /**
     * Remove wali kelas from kelas (alternative endpoint).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function remove(Request $request)
    {
        $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);

        $waliKelas = WaliKelas::where('kelas_id', $request->kelas_id)
                              ->where('is_active', true)
                              ->first();

        if (!$waliKelas) {
            return response()->json([
                'message' => 'Kelas tidak memiliki wali kelas aktif',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // Set to inactive instead of deleting
            $waliKelas->update(['is_active' => false]);

            DB::commit();

            return response()->json([
                'message' => 'Wali kelas berhasil dihapus dari kelas',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus wali kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
