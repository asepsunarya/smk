<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * MataPelajaranController
 * 
 * Handles CRUD operations for mata pelajaran (admin only)
 */
class MataPelajaranController extends Controller
{
    /**
     * Display a listing of mata pelajaran.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = MataPelajaran::with(['guru.user', 'kelas.jurusan'])
                              ->withCount(['nilai', 'capaianPembelajaran']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_mapel', 'like', "%{$search}%")
                  ->orWhere('nama_mapel', 'like', "%{$search}%")
                  ->orWhereHas('guru', function ($qg) use ($search) {
                      $qg->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nip', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kelas', function ($qk) use ($search) {
                      $qk->where('nama_kelas', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('guru_id')) {
            $query->where('guru_id', $request->guru_id);
        }

        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $mataPelajaran = $query->orderBy('kode_mapel')->paginate($request->get('per_page', 15));

        return response()->json($mataPelajaran);
    }

    /**
     * Store a newly created mata pelajaran.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_mapel' => ['required', 'string', 'max:20', 'unique:mata_pelajaran,kode_mapel'],
            'nama_mapel' => ['required', 'string', 'max:255'],
            'kkm' => ['required', 'integer', 'min:0', 'max:100'],
            'guru_id' => ['required', 'integer', 'exists:guru,id'],
            'kelas_id' => ['required', 'integer', 'exists:kelas,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $mataPelajaran = MataPelajaran::create([
                'kode_mapel' => $validated['kode_mapel'],
                'nama_mapel' => $validated['nama_mapel'],
                'kkm' => (int) $validated['kkm'],
                'guru_id' => (int) $validated['guru_id'],
                'kelas_id' => (int) $validated['kelas_id'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            DB::commit();

            // Reload with relationships
            $mataPelajaran->refresh();
            $mataPelajaran->load(['guru.user', 'kelas.jurusan']);
            $mataPelajaran->loadCount(['nilai', 'capaianPembelajaran']);

            return response()->json([
                'message' => 'Mata pelajaran berhasil ditambahkan',
                'data' => $mataPelajaran,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating mata pelajaran: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Gagal menambahkan mata pelajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified mata pelajaran.
     *
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(MataPelajaran $mataPelajaran)
    {
        $mataPelajaran->load(['guru.user', 'kelas.jurusan'])
                      ->loadCount(['nilai', 'capaianPembelajaran']);

        return response()->json($mataPelajaran);
    }

    /**
     * Update the specified mata pelajaran.
     *
     * @param  Request  $request
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, MataPelajaran $mataPelajaran)
    {
        $request->validate([
            'kode_mapel' => ['sometimes', 'string', 'max:20', Rule::unique('mata_pelajaran', 'kode_mapel')->ignore($mataPelajaran->id)],
            'nama_mapel' => ['sometimes', 'string', 'max:255'],
            'kkm' => ['sometimes', 'integer', 'min:0', 'max:100'],
            'guru_id' => ['sometimes', 'exists:guru,id'],
            'kelas_id' => ['sometimes', 'exists:kelas,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $mataPelajaran->update($request->only([
                'kode_mapel',
                'nama_mapel',
                'kkm',
                'guru_id',
                'kelas_id',
                'is_active',
            ]));

            DB::commit();

            return response()->json([
                'message' => 'Mata pelajaran berhasil diperbarui',
                'data' => $mataPelajaran->fresh()->load(['guru.user', 'kelas.jurusan'])->loadCount(['nilai', 'capaianPembelajaran']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui mata pelajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified mata pelajaran.
     *
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(MataPelajaran $mataPelajaran)
    {
        // Check if mata pelajaran has related data
        if ($mataPelajaran->nilai()->exists()) {
            return response()->json([
                'message' => 'Mata pelajaran tidak dapat dihapus karena masih memiliki data nilai.',
            ], 422);
        }

        if ($mataPelajaran->capaianPembelajaran()->exists()) {
            return response()->json([
                'message' => 'Mata pelajaran tidak dapat dihapus karena masih memiliki capaian pembelajaran.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $mataPelajaran->delete();

            DB::commit();

            return response()->json([
                'message' => 'Mata pelajaran berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus mata pelajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of mata pelajaran.
     *
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(MataPelajaran $mataPelajaran)
    {
        DB::beginTransaction();
        try {
            $mataPelajaran->update(['is_active' => !$mataPelajaran->is_active]);

            DB::commit();

            return response()->json([
                'message' => 'Status mata pelajaran berhasil diubah',
                'data' => $mataPelajaran->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengubah status mata pelajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

