<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\P5;
use App\Models\P5Kelompok;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * P5Controller for Admin
 * 
 * Handles P5 project management for admin (full access)
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
        $query = P5::with(['koordinator.user', 'tahunAjaran', 'peserta', 'kelompok.guru', 'kelompok.siswa.kelas']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('tema', 'like', "%{$search}%")
                  ->orWhere('judul', 'like', "%{$search}%")
                  ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        if ($request->has('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->has('koordinator_id')) {
            $query->where('koordinator_id', $request->koordinator_id);
        }

        $p5 = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

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
            'dimensi' => 'required|string|max:255',
            'tema' => 'required|string|max:255',
            'judul' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'elemen_sub' => 'required|array',
            'elemen_sub.*.elemen' => 'required|string|max:500',
            'elemen_sub.*.sub_elemen' => 'required|string|max:500',
            'elemen_sub.*.deskripsi_tujuan' => 'nullable|string|max:2000',
        ]);

        $elemenSub = array_map(function ($e) {
            $item = [
                'elemen' => $e['elemen'] ?? '',
                'sub_elemen' => $e['sub_elemen'] ?? '',
            ];
            $dt = isset($e['deskripsi_tujuan']) ? trim((string) $e['deskripsi_tujuan']) : '';
            if ($dt !== '') {
                $item['deskripsi_tujuan'] = $dt;
            }
            return $item;
        }, $request->elemen_sub);
        $first = $elemenSub[0] ?? null;
        $p5 = P5::create([
            'dimensi' => $request->dimensi,
            'tema' => $request->tema,
            'elemen' => $first['elemen'] ?? null,
            'sub_elemen' => $first['sub_elemen'] ?? null,
            'elemen_sub' => $elemenSub,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        $p5->load(['koordinator.user', 'tahunAjaran', 'peserta', 'kelompok.guru', 'kelompok.siswa.kelas']);

        return response()->json([
            'message' => 'Projek P5 berhasil dibuat',
            'data' => $p5,
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
        $p5->load(['koordinator.user', 'tahunAjaran', 'peserta.kelas', 'kelompok.guru', 'kelompok.siswa.kelas', 'nilaiP5.siswa.user', 'nilaiP5.dimensi']);

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
        $request->validate([
            'dimensi' => 'sometimes|required|string|max:255',
            'tema' => 'sometimes|required|string|max:255',
            'judul' => 'sometimes|required|string|max:255',
            'deskripsi' => 'sometimes|required|string',
            'elemen_sub' => 'sometimes|required|array',
            'elemen_sub.*.elemen' => 'required_with:elemen_sub|string|max:500',
            'elemen_sub.*.sub_elemen' => 'required_with:elemen_sub|string|max:500',
            'elemen_sub.*.deskripsi_tujuan' => 'nullable|string|max:2000',
        ]);

        $data = $request->only(['dimensi', 'tema', 'judul', 'deskripsi']);
        if ($request->has('elemen_sub')) {
            $elemenSub = array_map(function ($e) {
                $item = [
                    'elemen' => $e['elemen'] ?? '',
                    'sub_elemen' => $e['sub_elemen'] ?? '',
                ];
                $dt = isset($e['deskripsi_tujuan']) ? trim((string) $e['deskripsi_tujuan']) : '';
                if ($dt !== '') {
                    $item['deskripsi_tujuan'] = $dt;
                }
                return $item;
            }, $request->elemen_sub);
            $first = $elemenSub[0] ?? null;
            $data['elemen'] = $first['elemen'] ?? null;
            $data['sub_elemen'] = $first['sub_elemen'] ?? null;
            $data['elemen_sub'] = $elemenSub;
        }
        $p5->update($data);

        $p5->load(['koordinator.user', 'tahunAjaran', 'peserta', 'kelompok.guru', 'kelompok.siswa.kelas']);

        return response()->json([
            'message' => 'Projek P5 berhasil diperbarui',
            'data' => $p5,
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
        $p5->delete();

        return response()->json([
            'message' => 'Projek P5 berhasil dihapus',
        ]);
    }

    /**
     * Get guru that can be assigned as koordinator (not already koordinator of another P5).
     * When exclude_p5_id is set (editing), current P5's koordinator is allowed.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableGuru(Request $request)
    {
        $excludeP5Id = $request->query('exclude_p5_id');
        $usedGuruIds = P5::when($excludeP5Id, fn ($q) => $q->where('id', '!=', $excludeP5Id))
            ->pluck('koordinator_id')
            ->unique()
            ->filter()
            ->values()
            ->all();

        $guru = Guru::whereNotIn('id', $usedGuruIds)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);

        return response()->json($guru);
    }

    /**
     * Get siswa that can be assigned as peserta (not already peserta of another P5).
     * Returns siswa with label_display = "Nama - Kelas". When exclude_p5_id is set (editing), current P5's peserta are allowed.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableSiswa(Request $request)
    {
        $excludeP5Id = $request->query('exclude_p5_id');
        $usedSiswaIds = DB::table('p5_siswa')
            ->when($excludeP5Id, fn ($q) => $q->where('p5_id', '!=', $excludeP5Id))
            ->pluck('siswa_id')
            ->unique()
            ->values()
            ->all();

        $siswa = Siswa::with('kelas:id,nama_kelas')
            ->where('status', 'aktif')
            ->whereNotIn('id', $usedSiswaIds)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($s) {
                $s->label_display = $s->nama_lengkap . ' - ' . ($s->kelas?->nama_kelas ?? '-');
                return $s;
            });

        return response()->json($siswa);
    }

    /**
     * Get kelompok for a P5 (groups with fasilitator and siswa).
     *
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function getKelompok(P5 $p5)
    {
        $p5->load(['kelompok.guru', 'kelompok.siswa.kelas']);
        return response()->json([
            'p5' => $p5,
            'kelompok' => $p5->kelompok->map(function ($k) {
                $k->siswa->each(fn ($s) => ($s->label_display = $s->nama_lengkap . ' - ' . ($s->kelas?->nama_kelas ?? '-')));
                return $k;
            }),
        ]);
    }

    /**
     * Save/replace kelompok for a P5. Syncs p5_siswa (peserta) from all siswa in kelompok.
     *
     * @param  Request  $request
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveKelompok(Request $request, P5 $p5)
    {
        $request->validate([
            'kelompok' => 'required|array',
            'kelompok.*.guru_id' => 'required|exists:guru,id',
            'kelompok.*.siswa_ids' => 'required|array',
            'kelompok.*.siswa_ids.*' => 'exists:siswa,id',
        ]);

        DB::beginTransaction();
        try {
            $p5->kelompok()->delete();
            $allSiswaIds = [];
            foreach ($request->kelompok as $row) {
                $kelompok = P5Kelompok::create([
                    'p5_id' => $p5->id,
                    'guru_id' => $row['guru_id'],
                ]);
                $kelompok->siswa()->sync($row['siswa_ids']);
                $allSiswaIds = array_merge($allSiswaIds, $row['siswa_ids']);
            }
            $p5->peserta()->sync(array_unique($allSiswaIds));
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyimpan kelompok',
                'error' => $e->getMessage(),
            ], 500);
        }

        $p5->load(['kelompok.guru', 'kelompok.siswa.kelas', 'peserta']);
        return response()->json([
            'message' => 'Kelompok berhasil disimpan',
            'data' => $p5,
        ]);
    }

    /**
     * Get guru available as fasilitator for kelompok (not already in this P5's kelompok).
     *
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableFasilitatorForKelompok(P5 $p5)
    {
        $usedGuruIds = $p5->kelompok()->pluck('guru_id')->unique()->filter()->values()->all();
        $guru = Guru::whereNotIn('id', $usedGuruIds)
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap']);
        return response()->json($guru);
    }

    /**
     * Get siswa available for kelompok (not already in this P5's kelompok). Returns label_display.
     *
     * @param  P5  $p5
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableSiswaForKelompok(P5 $p5)
    {
        $usedSiswaIds = DB::table('p5_kelompok_siswa')
            ->join('p5_kelompok', 'p5_kelompok_siswa.p5_kelompok_id', '=', 'p5_kelompok.id')
            ->where('p5_kelompok.p5_id', $p5->id)
            ->pluck('p5_kelompok_siswa.siswa_id')
            ->unique()
            ->values()
            ->all();

        $siswa = Siswa::with('kelas:id,nama_kelas')
            ->where('status', 'aktif')
            ->whereNotIn('id', $usedSiswaIds)
            ->orderBy('nama_lengkap')
            ->get()
            ->map(function ($s) {
                $s->label_display = $s->nama_lengkap . ' - ' . ($s->kelas?->nama_kelas ?? '-');
                return $s;
            });

        return response()->json($siswa);
    }
}

