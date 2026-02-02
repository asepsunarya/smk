<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\CapaianPembelajaran;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CapaianPembelajaranController for Guru
 * 
 * Handles CP (Capaian Pembelajaran) management for teachers
 */
class CapaianPembelajaranController extends Controller
{
    /**
     * Get CP by mata pelajaran.
     *
     * @param  Request  $request
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function byMapel(MataPelajaran $mataPelajaran)
    {
        $cp = CapaianPembelajaran::where('mata_pelajaran_id', $mataPelajaran->id) 
                                ->with('tujuanPembelajaran')
                                 ->orderBy('tingkat')
                                 ->orderBy('semester')
                                 ->orderBy('target')
                                 ->get();

        return response()->json([
            'mata_pelajaran' => $mataPelajaran,
            'capaian_pembelajaran' => $cp,
        ]);
    }

    /**
     * Display a listing of CP.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = CapaianPembelajaran::with(['mataPelajaran', 'tujuanPembelajaran']);

        if ($request->has('mata_pelajaran_id')) {
            $query->where('mata_pelajaran_id', $request->mata_pelajaran_id);
        }

        if ($request->has('fase')) {
            $query->where('fase', $request->fase);
        }

        if ($request->has('elemen')) {
            $query->where('elemen', $request->elemen);
        }

        $cp = $query->orderBy('kode_cp')->paginate($request->get('per_page', 15));

        return response()->json($cp);
    }

    /**
     * Store a newly created CP.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kode_cp' => 'required|string|max:50',
            'target' => 'required|string|in:tengah_semester,akhir_semester',
            'deskripsi' => 'required|string|max:200',
            'fase' => 'required|string|in:10,11,12',
            'semester' => 'nullable|string|in:1,2',
            'tingkat' => 'nullable|string',
            'elemen' => 'required|string|in:pemahaman,keterampilan,sikap',
            'is_active' => 'sometimes|boolean',
        ]);

        // Set tingkat from fase if not provided
        $tingkat = $request->tingkat ?? $request->fase;

        $cp = CapaianPembelajaran::create([
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'kode_cp' => $request->kode_cp,
            'target' => $request->target,
            'deskripsi' => $request->deskripsi,
            'fase' => $request->fase,
            'semester' => $request->semester,
            'tingkat' => $tingkat,
            'elemen' => $request->elemen,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Capaian Pembelajaran berhasil ditambahkan',
            'data' => $cp->load(['mataPelajaran', 'tujuanPembelajaran']),
        ], 201);
    }

    /**
     * Display the specified CP.
     *
     * @param  CapaianPembelajaran  $capaianPembelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(CapaianPembelajaran $capaianPembelajaran)
    {
        $capaianPembelajaran->load(['mataPelajaran', 'tujuanPembelajaran']);

        return response()->json($capaianPembelajaran);
    }

    /**
     * Update the specified CP.
     *
     * @param  Request  $request
     * @param  CapaianPembelajaran  $capaianPembelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, CapaianPembelajaran $capaianPembelajaran)
    {
        $request->validate([
            'kode_cp' => 'sometimes|required|string|max:50',
            'target' => 'required|string|in:tengah_semester,akhir_semester',
            'deskripsi' => 'sometimes|required|string|max:200',
            'fase' => 'sometimes|required|string|in:10,11,12',
            'semester' => 'nullable|string|in:1,2',
            'tingkat' => 'nullable|string',
            'elemen' => 'sometimes|required|string|in:pemahaman,keterampilan,sikap',
            'is_active' => 'sometimes|boolean',
        ]);

        $updateData = $request->only([
            'kode_cp',
            'target',
            'deskripsi',
            'fase',
            'semester',
            'elemen',
            'is_active',
        ]);

        // Set tingkat from fase if not provided
        if ($request->has('tingkat')) {
            $updateData['tingkat'] = $request->tingkat;
        } elseif ($request->has('fase')) {
            $updateData['tingkat'] = $request->fase;
        }

        $capaianPembelajaran->update($updateData);

        return response()->json([
            'message' => 'Capaian Pembelajaran berhasil diperbarui',
            'data' => $capaianPembelajaran->load(['mataPelajaran', 'tujuanPembelajaran']),
        ]);
    }

    /**
     * Remove the specified CP.
     *
     * @param  CapaianPembelajaran  $capaianPembelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(CapaianPembelajaran $capaianPembelajaran)
    {
        // Check if CP has tujuan pembelajaran
        if ($capaianPembelajaran->tujuanPembelajaran()->count() > 0) {
            return response()->json([
                'message' => 'Tidak dapat menghapus CP yang memiliki Tujuan Pembelajaran',
            ], 422);
        }

        $capaianPembelajaran->delete();

        return response()->json([
            'message' => 'Capaian Pembelajaran berhasil dihapus',
        ]);
    }
}

