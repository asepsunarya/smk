<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ukk;
use App\Models\Jurusan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * UkkController
 * 
 * Handles CRUD operations for UKK (admin only)
 */
class UkkController extends Controller
{
    /**
     * Display a listing of UKK.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Ukk::with(['siswa.user', 'jurusan', 'kelas', 'pengujiInternal.user', 'tahunAjaran']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('siswa', function ($q) use ($search) {
                      $q->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%");
                  })
                  ->orWhereHas('kelas', function ($q) use ($search) {
                      $q->where('nama_kelas', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->has('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->has('predikat')) {
            $query->where('predikat', $request->predikat);
        }

        $ukk = $query->orderBy('tanggal_ujian', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($ukk);
    }

    /**
     * Store a newly created UKK.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'jurusan_id' => 'required|exists:jurusan,id',
            'kelas_id' => 'required|exists:kelas,id',
            'nama_du_di' => 'nullable|string|max:255',
            'tanggal_ujian' => 'required|date',
            'nilai_teori' => 'nullable|integer|min:0|max:100',
            'nilai_praktek' => 'nullable|integer|min:0|max:100',
            'penguji_internal_id' => 'required|exists:guru,id',
            'penguji_eksternal' => 'nullable|string|max:255',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        // Verify siswa belongs to the jurusan and kelas
        $siswa = \App\Models\Siswa::findOrFail($request->siswa_id);
        if ($siswa->kelas->jurusan_id != $request->jurusan_id) {
            return response()->json([
                'message' => 'Siswa tidak berada di jurusan yang dipilih',
            ], 422);
        }
        
        if ($siswa->kelas_id != $request->kelas_id) {
            return response()->json([
                'message' => 'Siswa tidak berada di kelas yang dipilih',
            ], 422);
        }

        $ukk = Ukk::create($request->all());

        // Reload with relationships
        $ukk->load(['siswa.user', 'jurusan', 'kelas', 'pengujiInternal.user', 'tahunAjaran']);

        return response()->json($ukk, 201);
    }

    /**
     * Display the specified UKK.
     *
     * @param  Ukk  $ukk
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Ukk $ukk)
    {
        $ukk->load(['siswa.user', 'jurusan', 'pengujiInternal.user', 'tahunAjaran']);

        return response()->json($ukk);
    }

    /**
     * Update the specified UKK.
     *
     * @param  Request  $request
     * @param  Ukk  $ukk
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Ukk $ukk)
    {
        $request->validate([
            'siswa_id' => 'sometimes|required|exists:siswa,id',
            'jurusan_id' => 'sometimes|required|exists:jurusan,id',
            'kelas_id' => 'sometimes|required|exists:kelas,id',
            'nama_du_di' => 'nullable|string|max:255',
            'tanggal_ujian' => 'sometimes|required|date',
            'nilai_teori' => 'nullable|integer|min:0|max:100',
            'nilai_praktek' => 'nullable|integer|min:0|max:100',
            'penguji_internal_id' => 'sometimes|required|exists:guru,id',
            'penguji_eksternal' => 'nullable|string|max:255',
            'tahun_ajaran_id' => 'sometimes|required|exists:tahun_ajaran,id',
        ]);

        // If siswa_id, jurusan_id, or kelas_id is being updated, verify siswa belongs to jurusan and kelas
        if ($request->has('siswa_id') || $request->has('jurusan_id') || $request->has('kelas_id')) {
            $siswaId = $request->siswa_id ?? $ukk->siswa_id;
            $jurusanId = $request->jurusan_id ?? $ukk->jurusan_id;
            $kelasId = $request->kelas_id ?? $ukk->kelas_id;
            
            $siswa = \App\Models\Siswa::findOrFail($siswaId);
            if ($siswa->kelas->jurusan_id != $jurusanId) {
                return response()->json([
                    'message' => 'Siswa tidak berada di jurusan yang dipilih',
                ], 422);
            }
            
            if ($siswa->kelas_id != $kelasId) {
                return response()->json([
                    'message' => 'Siswa tidak berada di kelas yang dipilih',
                ], 422);
            }
        }

        $ukk->update($request->all());

        // Reload with relationships
        $ukk->load(['siswa.user', 'jurusan', 'kelas', 'pengujiInternal.user', 'tahunAjaran']);

        return response()->json($ukk);
    }

    /**
     * Remove the specified UKK.
     *
     * @param  Ukk  $ukk
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Ukk $ukk)
    {
        $ukk->delete();

        return response()->json([
            'message' => 'UKK berhasil dihapus',
        ]);
    }

    /**
     * Get UKK by jurusan.
     *
     * @param  Jurusan  $jurusan
     * @return \Illuminate\Http\JsonResponse
     */
    public function byJurusan(Jurusan $jurusan)
    {
        $ukk = Ukk::with(['siswa.user', 'pengujiInternal.user', 'tahunAjaran'])
                  ->where('jurusan_id', $jurusan->id)
                  ->orderBy('tanggal_ujian', 'desc')
                  ->get();

        return response()->json($ukk);
    }
}

