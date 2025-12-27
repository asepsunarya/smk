<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * PklController
 *
 * Handles CRUD operations for PKL (admin only)
 */
class PklController extends Controller
{
    /**
     * Display a listing of PKL.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Pkl::with(['tahunAjaran', 'pembimbingSekolah']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('alamat_perusahaan', 'like', "%{$search}%")
                  ->orWhere('pembimbing_perusahaan', 'like', "%{$search}%")
                  ->orWhereHas('pembimbingSekolah', function ($qg) use ($search) {
                      $qg->where('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->has('status')) {
            $today = now();
            if ($request->status === 'belum_mulai') {
                $query->where('tanggal_mulai', '>', $today);
            } elseif ($request->status === 'sedang_berlangsung') {
                $query->where('tanggal_mulai', '<=', $today)
                      ->where('tanggal_selesai', '>=', $today);
            } elseif ($request->status === 'selesai') {
                $query->where('tanggal_selesai', '<', $today);
            }
        }

        $pkl = $query->orderBy('tanggal_mulai', 'desc')->paginate($request->get('per_page', 15));

        // Transform collection to add computed attributes
        $pkl->getCollection()->transform(function ($item) {
            $item->status = $item->status;
            return $item;
        });

        return response()->json($pkl);
    }

    /**
     * Store a newly created PKL.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_perusahaan' => ['required', 'string', 'max:255'],
            'alamat_perusahaan' => ['required', 'string'],
            'pembimbing_perusahaan' => ['required', 'string', 'max:255'],
            'pembimbing_sekolah_id' => ['required', 'integer', 'exists:guru,id'],
            'tanggal_mulai' => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after:tanggal_mulai'],
            'tahun_ajaran_id' => ['required', 'integer', 'exists:tahun_ajaran,id'],
        ]);

        DB::beginTransaction();
        try {
            $pkl = Pkl::create($validated);

            DB::commit();

            // Reload with relationships
            $pkl->refresh();
            $pkl->load(['tahunAjaran', 'pembimbingSekolah']);

            return response()->json([
                'message' => 'PKL berhasil ditambahkan',
                'data' => $pkl,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating PKL: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Gagal menambahkan PKL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified PKL.
     *
     * @param  Pkl  $pkl
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Pkl $pkl)
    {
        $pkl->load(['tahunAjaran', 'pembimbingSekolah']);

        return response()->json($pkl);
    }

    /**
     * Update the specified PKL.
     *
     * @param  Request  $request
     * @param  Pkl  $pkl
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Pkl $pkl)
    {
        $validated = $request->validate([
            'nama_perusahaan' => ['sometimes', 'string', 'max:255'],
            'alamat_perusahaan' => ['sometimes', 'string'],
            'pembimbing_perusahaan' => ['sometimes', 'string', 'max:255'],
            'pembimbing_sekolah_id' => ['sometimes', 'integer', 'exists:guru,id'],
            'tanggal_mulai' => ['sometimes', 'date'],
            'tanggal_selesai' => ['sometimes', 'date', 'after:tanggal_mulai'],
            'tahun_ajaran_id' => ['sometimes', 'integer', 'exists:tahun_ajaran,id'],
        ]);

        DB::beginTransaction();
        try {
            $pkl->update($validated);

            DB::commit();

            // Reload with relationships
            $pkl->refresh();
            $pkl->load(['tahunAjaran', 'pembimbingSekolah']);

            return response()->json([
                'message' => 'PKL berhasil diperbarui',
                'data' => $pkl,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating PKL: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Gagal memperbarui PKL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified PKL.
     *
     * @param  Pkl  $pkl
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Pkl $pkl)
    {
        DB::beginTransaction();
        try {
            $pkl->delete();

            DB::commit();

            return response()->json([
                'message' => 'PKL berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus PKL',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

