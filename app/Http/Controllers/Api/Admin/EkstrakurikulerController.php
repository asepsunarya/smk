<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ekstrakurikuler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * EkstrakurikulerController
 *
 * Handles CRUD operations for ekstrakurikuler (admin only)
 */
class EkstrakurikulerController extends Controller
{
    /**
     * Display a listing of ekstrakurikuler.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Ekstrakurikuler::with(['pembina.user']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%")
                  ->orWhereHas('pembina', function ($qp) use ($search) {
                      $qp->where('nama_lengkap', 'like', "%{$search}%")
                         ->orWhere('nuptk', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('pembina_id')) {
            $query->where('pembina_id', $request->pembina_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $ekstrakurikuler = $query->orderBy('nama')->paginate($request->get('per_page', 15));

        return response()->json($ekstrakurikuler);
    }

    /**
     * Store a newly created ekstrakurikuler.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'pembina_id' => ['nullable', 'integer', 'exists:guru,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $ekstrakurikuler = Ekstrakurikuler::create([
                'nama' => $validated['nama'],
                'deskripsi' => $validated['deskripsi'] ?? null,
                'pembina_id' => $validated['pembina_id'] ?? null,
                'is_active' => $validated['is_active'] ?? true,
            ]);

            DB::commit();

            // Reload with relationships
            $ekstrakurikuler->refresh();
            $ekstrakurikuler->load(['pembina.user']);

            return response()->json([
                'message' => 'Ekstrakurikuler berhasil ditambahkan',
                'data' => $ekstrakurikuler,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating ekstrakurikuler: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Gagal menambahkan ekstrakurikuler',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified ekstrakurikuler.
     *
     * @param  Ekstrakurikuler  $ekstrakurikuler
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Ekstrakurikuler $ekstrakurikuler)
    {
        $ekstrakurikuler->load(['pembina.user']);

        return response()->json($ekstrakurikuler);
    }

    /**
     * Update the specified ekstrakurikuler.
     *
     * @param  Request  $request
     * @param  Ekstrakurikuler  $ekstrakurikuler
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $validated = $request->validate([
            'nama' => ['sometimes', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'pembina_id' => ['nullable', 'integer', 'exists:guru,id'],
            'is_active' => ['sometimes', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $ekstrakurikuler->update($validated);

            DB::commit();

            // Reload with relationships
            $ekstrakurikuler->refresh();
            $ekstrakurikuler->load(['pembina.user']);

            return response()->json([
                'message' => 'Ekstrakurikuler berhasil diperbarui',
                'data' => $ekstrakurikuler,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error updating ekstrakurikuler: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Gagal memperbarui ekstrakurikuler',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified ekstrakurikuler.
     *
     * @param  Ekstrakurikuler  $ekstrakurikuler
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ekstrakurikuler $ekstrakurikuler)
    {
        // Check if ekstrakurikuler has related data
        if ($ekstrakurikuler->nilaiEkstrakurikuler()->exists()) {
            return response()->json([
                'message' => 'Ekstrakurikuler tidak dapat dihapus karena masih memiliki data nilai ekstrakurikuler.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $ekstrakurikuler->delete();

            DB::commit();

            return response()->json([
                'message' => 'Ekstrakurikuler berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus ekstrakurikuler',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle active status of ekstrakurikuler.
     *
     * @param  Ekstrakurikuler  $ekstrakurikuler
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Ekstrakurikuler $ekstrakurikuler)
    {
        DB::beginTransaction();
        try {
            $ekstrakurikuler->update(['is_active' => !$ekstrakurikuler->is_active]);

            DB::commit();

            return response()->json([
                'message' => 'Status ekstrakurikuler berhasil diubah',
                'data' => $ekstrakurikuler->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mengubah status ekstrakurikuler',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign pembina to ekstrakurikuler.
     *
     * @param  Request  $request
     * @param  Ekstrakurikuler  $ekstrakurikuler
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPembina(Request $request, Ekstrakurikuler $ekstrakurikuler)
    {
        $validated = $request->validate([
            'pembina_id' => ['required', 'integer', 'exists:guru,id'],
        ]);

        DB::beginTransaction();
        try {
            $ekstrakurikuler->update(['pembina_id' => $validated['pembina_id']]);

            DB::commit();

            $ekstrakurikuler->refresh();
            $ekstrakurikuler->load(['pembina.user']);

            return response()->json([
                'message' => 'Pembina berhasil ditetapkan',
                'data' => $ekstrakurikuler,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menetapkan pembina',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

