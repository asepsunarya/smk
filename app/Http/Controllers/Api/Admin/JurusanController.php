<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * JurusanController
 * 
 * Handles CRUD operations for jurusan (admin only)
 */
class JurusanController extends Controller
{
    /**
     * Display a listing of jurusan.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Jurusan::withCount(['kelas', 'siswa']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('kode_jurusan', 'like', "%{$search}%")
                  ->orWhere('nama_jurusan', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $jurusan = $query->orderBy('kode_jurusan')->paginate($request->get('per_page', 15));

        // Add computed attributes
        $jurusan->getCollection()->transform(function ($item) {
            $item->active_siswa_count = $item->siswa()->where('status', 'aktif')->count();
            return $item;
        });

        return response()->json($jurusan);
    }

    /**
     * Store a newly created jurusan.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode_jurusan' => ['required', 'string', 'max:10', 'unique:jurusan'],
            'nama_jurusan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $jurusan = Jurusan::create([
                'kode_jurusan' => strtoupper($request->kode_jurusan),
                'nama_jurusan' => $request->nama_jurusan,
                'deskripsi' => $request->deskripsi,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Jurusan berhasil ditambahkan',
                'data' => $jurusan,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan jurusan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified jurusan.
     *
     * @param  Jurusan  $jurusan
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Jurusan $jurusan)
    {
        $jurusan->load(['kelas', 'siswa.user', 'ukk']);
        
        // Add computed attributes
        $jurusan->active_siswa_count = $jurusan->siswa()->where('status', 'aktif')->count();
        $jurusan->kelas_count = $jurusan->kelas()->count();

        return response()->json($jurusan);
    }

    /**
     * Update the specified jurusan.
     *
     * @param  Request  $request
     * @param  Jurusan  $jurusan
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Jurusan $jurusan)
    {
        $request->validate([
            'kode_jurusan' => ['required', 'string', 'max:10', Rule::unique('jurusan')->ignore($jurusan->id)],
            'nama_jurusan' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $jurusan->update([
                'kode_jurusan' => strtoupper($request->kode_jurusan),
                'nama_jurusan' => $request->nama_jurusan,
                'deskripsi' => $request->deskripsi,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Jurusan berhasil diperbarui',
                'data' => $jurusan->fresh(),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui jurusan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified jurusan.
     *
     * @param  Jurusan  $jurusan
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Jurusan $jurusan)
    {
        // Prevent deleting jurusan if it has classes
        if ($jurusan->kelas()->exists()) {
            return response()->json([
                'message' => 'Jurusan tidak dapat dihapus karena masih memiliki kelas. Hapus kelas terlebih dahulu.',
            ], 422);
        }

        // Prevent deleting jurusan if it has UKK
        if ($jurusan->ukk()->exists()) {
            return response()->json([
                'message' => 'Jurusan tidak dapat dihapus karena masih memiliki data UKK.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $jurusan->delete();

            DB::commit();

            return response()->json([
                'message' => 'Jurusan berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus jurusan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

