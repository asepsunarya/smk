<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\P5;
use App\Models\NilaiP5;
use App\Models\TahunAjaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * P5Controller for Guru
 * 
 * Handles P5 project and nilai management for teachers
 */
class P5Controller extends Controller
{
    /**
     * Display a listing of P5 projects.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $guru = $user->guru;

        $query = P5::with(['koordinator.user', 'tahunAjaran', 'peserta.kelas', 'kelompok.guru', 'kelompok.siswa.kelas']);

        // Show P5 where guru is koordinator OR fasilitator di salah satu kelompok
        if ($guru) {
            $query->where(function ($q) use ($guru) {
                $q->where('koordinator_id', $guru->id)
                    ->orWhereHas('kelompok', fn ($k) => $k->where('guru_id', $guru->id));
            });
        }

        if ($request->has('tahun_ajaran_id') && $request->tahun_ajaran_id) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        $p5 = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        // Filter peserta hanya siswa di kelompok yang difasilitasi guru ini
        if ($guru) {
            try {
                $p5->getCollection()->transform(function ($item) use ($guru) {
                    if (!$item->relationLoaded('peserta') || !$item->relationLoaded('kelompok')) {
                        return $item;
                    }
                    $kelompokGuru = $item->kelompok->where('guru_id', $guru->id);
                    $siswaIdsDiKelompok = $kelompokGuru->flatMap(function ($k) {
                        return $k->relationLoaded('siswa') ? $k->siswa->pluck('id') : collect();
                    })->unique()->values()->all();
                    if (empty($siswaIdsDiKelompok)) {
                        $item->setRelation('peserta', $item->peserta->take(0));
                        return $item;
                    }
                    $filtered = $item->peserta->filter(fn ($s) => in_array((int) $s->id, $siswaIdsDiKelompok, true));
                    $item->setRelation('peserta', $filtered->values());
                    return $item;
                });
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json($p5);
    }

    /**
     * Store a newly created P5 project.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'tema' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        $user = Auth::user();
        $guru = $user->guru;

        if (!$guru) {
            return response()->json([
                'message' => 'Guru tidak ditemukan',
            ], 404);
        }

        $p5 = P5::create([
            'tema' => $request->tema,
            'deskripsi' => $request->deskripsi,
            'koordinator_id' => $guru->id,
            'tahun_ajaran_id' => $request->tahun_ajaran_id,
        ]);

        return response()->json([
            'message' => 'Projek P5 berhasil dibuat',
            'data' => $p5->load(['koordinator.user', 'tahunAjaran']),
        ], 201);
    }

    /**
     * Display the specified P5 project.
     *
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(P5 $p5)
    {
        $p5->load(['koordinator.user', 'tahunAjaran', 'nilaiP5.siswa.user', 'nilaiP5.dimensi']);

        return response()->json($p5);
    }

    /**
     * Update the specified P5 project.
     *
     * @param  Request  $request
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, P5 $p5)
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Verify that this P5 belongs to the current guru
        if ($p5->koordinator_id !== $guru->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk mengubah projek ini',
            ], 403);
        }

        $request->validate([
            'tema' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
        ]);

        $p5->update($request->only(['tema', 'deskripsi']));

        return response()->json([
            'message' => 'Projek P5 berhasil diperbarui',
            'data' => $p5->load(['koordinator.user', 'tahunAjaran']),
        ]);
    }

    /**
     * Remove the specified P5 project.
     *
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(P5 $p5)
    {
        $user = Auth::user();
        $guru = $user->guru;

        // Verify that this P5 belongs to the current guru
        if ($p5->koordinator_id !== $guru->id) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk menghapus projek ini',
            ], 403);
        }

        $p5->delete();

        return response()->json([
            'message' => 'Projek P5 berhasil dihapus',
        ]);
    }

    /**
     * Input nilai P5 for students.
     *
     * @param  Request  $request
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function inputNilai(Request $request, P5 $p5)
    {
        $request->validate([
            'nilai' => 'required|array',
            'nilai.*.siswa_id' => 'required|exists:siswa,id',
            'nilai.*.sub_elemen' => 'required|string|max:500',
            'nilai.*.nilai' => 'required|string|in:MB,SB,BSH,SAB',
            'catatan_proses' => 'nullable|array',
            'catatan_proses.*.siswa_id' => 'required|exists:siswa,id',
            'catatan_proses.*.catatan_proses' => 'nullable|string|max:5000',
        ]);

        $user = Auth::user();
        $guru = $user->guru;

        // Verify: guru harus koordinator P5 atau fasilitator di salah satu kelompok
        $isKoordinator = $p5->koordinator_id == $guru->id;
        $isFasilitator = $p5->kelompok()->where('guru_id', $guru->id)->exists();
        if (!$isKoordinator && !$isFasilitator) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses untuk menginput nilai projek ini',
            ], 403);
        }

        DB::beginTransaction();
        try {
            foreach ($request->nilai as $nilaiData) {
                NilaiP5::updateOrCreate(
                    [
                        'siswa_id' => $nilaiData['siswa_id'],
                        'p5_id' => $p5->id,
                        'sub_elemen' => $nilaiData['sub_elemen'],
                    ],
                    [
                        'nilai' => $nilaiData['nilai'],
                        'dimensi_id' => null,
                    ]
                );
            }

            if ($request->has('catatan_proses') && is_array($request->catatan_proses)) {
                foreach ($request->catatan_proses as $item) {
                    DB::table('p5_siswa')
                        ->where('p5_id', $p5->id)
                        ->where('siswa_id', $item['siswa_id'])
                        ->update(['catatan_proses' => $item['catatan_proses'] ?? null]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Nilai P5 berhasil diinput',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menginput nilai P5',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get nilai P5 for a project.
     *
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNilai(P5 $p5)
    {
        $p5->load(['koordinator.user', 'tahunAjaran', 'peserta.kelas']);
        $elemenSub = $p5->elemen_sub ?? [];
        if (!is_array($elemenSub) || count($elemenSub) === 0) {
            return response()->json([
                'p5' => $p5,
                'elemen_sub' => [],
                'nilai' => [],
            ]);
        }

        $subElemenList = array_map(fn ($es) => $es['sub_elemen'] ?? $es['sub_elemen'] ?? '', $elemenSub);
        $subElemenList = array_values(array_unique(array_filter($subElemenList)));

        $nilaiRows = $p5->nilaiP5()
            ->whereNotNull('sub_elemen')
            ->whereIn('sub_elemen', $subElemenList)
            ->get();

        $nilai = [];
        foreach ($nilaiRows as $row) {
            if (!isset($nilai[$row->siswa_id])) {
                $nilai[$row->siswa_id] = [];
            }
            $nilai[$row->siswa_id][$row->sub_elemen] = $row->nilai;
        }

        $catatanProses = \Illuminate\Support\Facades\DB::table('p5_siswa')
            ->where('p5_id', $p5->id)
            ->pluck('catatan_proses', 'siswa_id')
            ->toArray();

        // Filter peserta hanya siswa di kelompok yang difasilitasi guru ini
        $user = Auth::user();
        $guru = $user->guru;
        if ($guru && $p5->relationLoaded('peserta')) {
            try {
                $p5->load(['kelompok.siswa']);
                $kelompokGuru = $p5->kelompok->where('guru_id', $guru->id);
                $siswaIdsDiKelompok = $kelompokGuru->flatMap(fn ($k) => $k->siswa->pluck('id'))->unique()->values()->all();
                if (!empty($siswaIdsDiKelompok)) {
                    $filtered = $p5->peserta->filter(fn ($s) => in_array((int) $s->id, $siswaIdsDiKelompok, true));
                    $p5->setRelation('peserta', $filtered->values());
                } else {
                    $p5->setRelation('peserta', $p5->peserta->take(0));
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return response()->json([
            'p5' => $p5,
            'elemen_sub' => $elemenSub,
            'nilai' => $nilai,
            'catatan_proses' => $catatanProses,
        ]);
    }
}

