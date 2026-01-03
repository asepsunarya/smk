<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\CapaianPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * NilaiController for Guru
 * 
 * Handles nilai sumatif management for teachers
 */
class NilaiController extends Controller
{
    /**
     * Get nilai for a specific kelas and mata pelajaran.
     *
     * @param  Request  $request
     * @param  Kelas  $kelas
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, Kelas $kelas, MataPelajaran $mataPelajaran)
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        $tahunAjaranId = $request->get('tahun_ajaran_id') ?? TahunAjaran::where('is_active', true)->first()?->id;

        if (!$tahunAjaranId) {
            return response()->json([
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        $siswa = $kelas->siswa()->where('status', 'aktif')->get();

        $query = Nilai::where('mata_pelajaran_id', $mataPelajaran->id)
                     ->where('tahun_ajaran_id', $tahunAjaranId)
                     ->where('guru_id', $guru->id)
                     ->whereIn('siswa_id', $siswa->pluck('id'));

        // Filter by capaian_pembelajaran_id if provided
        if ($request->has('capaian_pembelajaran_id') && $request->capaian_pembelajaran_id) {
            $query->where('capaian_pembelajaran_id', $request->capaian_pembelajaran_id);
        }

        // Filter by semester if provided
        if ($request->has('semester') && $request->semester) {
            $query->where('semester', $request->semester);
        }

        $nilai = $query->with(['siswa.user', 'mataPelajaran', 'capaianPembelajaran'])
                     ->get();

        return response()->json([
            'kelas' => $kelas->load('jurusan'),
            'mata_pelajaran' => $mataPelajaran,
            'tahun_ajaran' => TahunAjaran::find($tahunAjaranId),
            'nilai' => $nilai->sortBy('siswa.nama_lengkap')->values(),
        ]);
    }

    /**
     * Batch update nilai.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchUpdate(Request $request)
    {
        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.id' => 'required|exists:nilai,id',
            'nilai.*.nilai_sumatif_1' => 'nullable|integer|min:0|max:100',
            'nilai.*.nilai_sumatif_2' => 'nullable|integer|min:0|max:100',
            'nilai.*.nilai_sumatif_3' => 'nullable|integer|min:0|max:100',
            'nilai.*.nilai_sumatif_4' => 'nullable|integer|min:0|max:100',
            'nilai.*.nilai_sumatif_5' => 'nullable|integer|min:0|max:100',
            'nilai.*.nilai_uts' => 'nullable|integer|min:0|max:100',
            'nilai.*.nilai_uas' => 'nullable|integer|min:0|max:100',
            'nilai.*.deskripsi' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $guru = $user->guru;

        DB::beginTransaction();
        try {
            foreach ($request->nilai as $nilaiData) {
                $nilai = Nilai::find($nilaiData['id']);

                // Verify that this nilai belongs to the current guru
                if ($nilai->guru_id !== $guru->id) {
                    continue;
                }

                $nilai->update([
                    'nilai_sumatif_1' => $nilaiData['nilai_sumatif_1'] ?? null,
                    'nilai_sumatif_2' => $nilaiData['nilai_sumatif_2'] ?? null,
                    'nilai_sumatif_3' => $nilaiData['nilai_sumatif_3'] ?? null,
                    'nilai_sumatif_4' => $nilaiData['nilai_sumatif_4'] ?? null,
                    'nilai_sumatif_5' => $nilaiData['nilai_sumatif_5'] ?? null,
                    'nilai_uts' => $nilaiData['nilai_uts'] ?? null,
                    'nilai_uas' => $nilaiData['nilai_uas'] ?? null,
                    'deskripsi' => $nilaiData['deskripsi'] ?? null,
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Nilai berhasil diperbarui',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui nilai',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a specific nilai.
     *
     * @param  Request  $request
     * @param  Nilai  $nilai
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Nilai $nilai)
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Verify that this nilai belongs to the current guru
        if ($nilai->guru_id !== $guru->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengubah nilai ini',
            ], 403);
        }

        $request->validate([
            'nilai_sumatif_1' => 'nullable|numeric|min:0|max:100',
            'nilai_akhir' => 'nullable|numeric|min:0|max:100',
            'deskripsi' => 'nullable|string|max:500',
        ]);

        $nilai->update($request->only([
            'nilai_sumatif_1',
            'nilai_akhir',
            'deskripsi',
        ]));

        return response()->json([
            'message' => 'Nilai berhasil diperbarui',
            'data' => $nilai->load(['siswa.user', 'mataPelajaran', 'tahunAjaran', 'capaianPembelajaran']),
        ]);
    }

    /**
     * Delete a specific nilai.
     *
     * @param  Nilai  $nilai
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Nilai $nilai)
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Verify that this nilai belongs to the current guru
        if ($nilai->guru_id !== $guru->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk menghapus nilai ini',
            ], 403);
        }

        $nilai->delete();

        return response()->json([
            'message' => 'Nilai berhasil dihapus',
        ]);
    }

    /**
     * Store new nilai for capaian pembelajaran.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.siswa_id' => 'required|exists:siswa,id',
            'nilai.*.mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'nilai.*.tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'nilai.*.guru_id' => 'required|exists:guru,id',
            'nilai.*.capaian_pembelajaran_id' => 'required|exists:capaian_pembelajaran,id',
            'nilai.*.semester' => 'required|string|in:1,2',
            'nilai.*.nilai' => 'nullable|numeric|min:0|max:100',
            'nilai.*.nilai_akhir' => 'required|numeric|min:0|max:100',
            'nilai.*.deskripsi' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        // Verify that all nilai belong to the current guru
        foreach ($request->nilai as $nilaiData) {
            if ($nilaiData['guru_id'] != $guru->id) {
                return response()->json([
                    'message' => 'Anda tidak memiliki akses untuk menyimpan nilai ini',
                ], 403);
            }
        }

        DB::beginTransaction();
        try {
            $createdNilai = [];
            
            foreach ($request->nilai as $nilaiData) {
                // Use updateOrCreate to handle existing records
                $nilai = Nilai::updateOrCreate(
                    [
                        'siswa_id' => $nilaiData['siswa_id'],
                        'mata_pelajaran_id' => $nilaiData['mata_pelajaran_id'],
                        'tahun_ajaran_id' => $nilaiData['tahun_ajaran_id'],
                        'capaian_pembelajaran_id' => $nilaiData['capaian_pembelajaran_id'],
                        'semester' => $nilaiData['semester'],
                    ],
                    [
                        'guru_id' => $nilaiData['guru_id'],
                        'nilai_sumatif_1' => $nilaiData['nilai'] ?? null,
                        'nilai_akhir' => $nilaiData['nilai_akhir'],
                        'deskripsi' => $nilaiData['deskripsi'] ?? null,
                    ]
                );

                $createdNilai[] = $nilai->load(['siswa.user', 'mataPelajaran', 'capaianPembelajaran']);
            }

            DB::commit();

            return response()->json([
                'message' => 'Nilai berhasil disimpan',
                'data' => $createdNilai,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan nilai',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get or create special CP for STS/SAS.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrCreateSpecialCP(Request $request)
    {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajaran,id',
            'kode_cp' => 'required|string|in:STS,SAS',
            'semester' => 'required|string|in:1,2',
        ]);

        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        // Verify mata pelajaran is taught by this guru
        $mataPelajaran = MataPelajaran::where('id', $request->mata_pelajaran_id)
            ->where('guru_id', $guru->id)
            ->first();

        if (!$mataPelajaran) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mata pelajaran ini',
            ], 403);
        }

        // For STS/SAS, we use fase '10' as default
        $fase = '10';

        // Check if CP already exists
        $cp = CapaianPembelajaran::where('mata_pelajaran_id', $request->mata_pelajaran_id)
            ->where('kode_cp', $request->kode_cp)
            ->first();

        if (!$cp) {
            // Create new CP for STS/SAS
            $cp = CapaianPembelajaran::create([
                'mata_pelajaran_id' => $request->mata_pelajaran_id,
                'kode_cp' => $request->kode_cp,
                'target' => $request->kode_cp === 'STS' ? 'tengah_semester' : 'akhir_semester',
                'deskripsi' => $request->kode_cp === 'STS' 
                    ? 'Nilai Sumatif Tengah Semester' 
                    : 'Nilai Sumatif Akhir Semester',
                'fase' => $fase,
                'semester' => $request->semester,
                'tingkat' => $fase, // Set tingkat same as fase
                'elemen' => 'pemahaman',
                'is_active' => true,
            ]);
        }

        return response()->json([
            'capaian_pembelajaran_id' => $cp->id,
            'capaian_pembelajaran' => $cp,
        ]);
    }

    /**
     * Get siswa for a specific kelas.
     *
     * @param  Kelas  $kelas
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSiswa(Kelas $kelas)
    {
        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        // Verify that the kelas is associated with mata pelajaran taught by this guru
        $mataPelajaran = MataPelajaran::where('guru_id', $guru->id)
            ->whereHas('kelas', function ($query) use ($kelas) {
                $query->where('kelas.id', $kelas->id);
            })
            ->first();

        if (!$mataPelajaran) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk kelas ini',
            ], 403);
        }

        $siswa = $kelas->siswa()
            ->where('status', 'aktif')
            ->with('user')
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nis' => $s->nis,
                    'nama_lengkap' => $s->nama_lengkap,
                    'user' => $s->user ? [
                        'id' => $s->user->id,
                        'email' => $s->user->email,
                    ] : null,
                ];
            });

        return response()->json([
            'kelas' => [
                'id' => $kelas->id,
                'nama_kelas' => $kelas->nama_kelas,
            ],
            'siswa' => $siswa,
        ]);
    }
}

