<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * TahunAjaranController
 * 
 * Handles CRUD operations for tahun ajaran (admin only)
 */
class TahunAjaranController extends Controller
{
    /**
     * Display a listing of tahun ajaran.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = TahunAjaran::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tahun', 'like', "%{$search}%");
            });
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $tahunAjaran = $query->orderBy('tahun', 'desc')
            ->orderBy('semester', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json($tahunAjaran);
    }

    /**
     * Store a newly created tahun ajaran.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tahun' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'string', Rule::in(['1', '2'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            // If setting as active, deactivate all others
            if ($request->has('is_active') && $request->is_active) {
                TahunAjaran::where('is_active', true)->update(['is_active' => false]);
            }

            $tahunAjaran = TahunAjaran::create([
                'tahun' => $validated['tahun'],
                'semester' => $validated['semester'],
                'is_active' => $validated['is_active'] ?? false,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Tahun ajaran berhasil ditambahkan',
                'data' => $tahunAjaran,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan tahun ajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified tahun ajaran.
     *
     * @param  \App\Models\TahunAjaran  $tahunAjaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(TahunAjaran $tahunAjaran)
    {
        return response()->json($tahunAjaran);
    }

    /**
     * Update the specified tahun ajaran.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TahunAjaran  $tahunAjaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, TahunAjaran $tahunAjaran)
    {
        $validated = $request->validate([
            'tahun' => ['sometimes', 'string', 'max:255'],
            'semester' => ['sometimes', 'string', Rule::in(['1', '2'])],
            'is_active' => ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            // If setting as active, deactivate all others
            if ($request->has('is_active') && $request->is_active && !$tahunAjaran->is_active) {
                TahunAjaran::where('is_active', true)->update(['is_active' => false]);
            }

            $tahunAjaran->update($validated);

            DB::commit();

            return response()->json([
                'message' => 'Tahun ajaran berhasil diperbarui',
                'data' => $tahunAjaran,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui tahun ajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified tahun ajaran.
     *
     * @param  \App\Models\TahunAjaran  $tahunAjaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(TahunAjaran $tahunAjaran)
    {
        DB::beginTransaction();
        try {
            // Check if tahun ajaran is active
            if ($tahunAjaran->is_active) {
                return response()->json([
                    'message' => 'Tidak dapat menghapus tahun ajaran yang sedang aktif',
                ], 422);
            }

            // Check if tahun ajaran has related data
            $hasRelatedData = $tahunAjaran->nilai()->exists() ||
                             $tahunAjaran->nilaiEkstrakurikuler()->exists() ||
                             $tahunAjaran->pkl()->exists() ||
                             $tahunAjaran->p5()->exists() ||
                             $tahunAjaran->kehadiran()->exists() ||
                             $tahunAjaran->ukk()->exists() ||
                             $tahunAjaran->catatanAkademik()->exists() ||
                             $tahunAjaran->rapor()->exists();

            if ($hasRelatedData) {
                return response()->json([
                    'message' => 'Tidak dapat menghapus tahun ajaran yang sudah memiliki data terkait',
                ], 422);
            }

            $tahunAjaran->delete();

            DB::commit();

            return response()->json([
                'message' => 'Tahun ajaran berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus tahun ajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Activate the specified tahun ajaran.
     *
     * @param  \App\Models\TahunAjaran  $tahunAjaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function activate(TahunAjaran $tahunAjaran)
    {
        DB::beginTransaction();
        try {
            // Deactivate all other tahun ajaran
            TahunAjaran::where('is_active', true)->update(['is_active' => false]);

            // Activate this tahun ajaran
            $tahunAjaran->update(['is_active' => true]);

            DB::commit();

            return response()->json([
                'message' => 'Tahun ajaran berhasil diaktifkan',
                'data' => $tahunAjaran,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengaktifkan tahun ajaran',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

