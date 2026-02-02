<?php

namespace App\Http\Controllers\Api\Guru;

use App\Http\Controllers\Controller;
use App\Models\Ukk;
use App\Models\UkkEvent;
use App\Models\Jurusan;
use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Guru UkkController
 *
 * Nilai UKK hanya untuk guru yang menjadi kepala jurusan (jurusan.kepala_jurusan_id = guru.id).
 */
class UkkController extends Controller
{
    /**
     * Jurusan IDs where current user's guru is kepala jurusan.
     *
     * @return array<int>
     */
    private function allowedJurusanIds(): array
    {
        $user = Auth::user();
        if (!$user || !$user->guru) {
            return [];
        }
        return Jurusan::where('kepala_jurusan_id', $user->guru->id)->pluck('id')->all();
    }

    /**
     * Ensure current guru is kepala jurusan; abort 403 if not.
     */
    private function ensureKepalaJurusan(): void
    {
        $ids = $this->allowedJurusanIds();
        if (empty($ids)) {
            abort(403, 'Akses Nilai UKK hanya untuk Kepala Jurusan.');
        }
    }

    /**
     * Ensure given jurusan_id is allowed for current guru.
     */
    private function ensureJurusanAllowed(int $jurusanId): void
    {
        $ids = $this->allowedJurusanIds();
        if (!in_array((int) $jurusanId, $ids, true)) {
            abort(403, 'Jurusan tidak dalam wewenang Kepala Jurusan Anda.');
        }
    }

    /**
     * Ensure given UKK record belongs to an allowed jurusan.
     */
    private function ensureUkkAllowed(Ukk $ukk): void
    {
        $this->ensureJurusanAllowed($ukk->jurusan_id);
    }

    public function index(Request $request)
    {
        $this->ensureKepalaJurusan();
        $jurusanIds = $this->allowedJurusanIds();

        $query = Ukk::with(['siswa.user', 'jurusan', 'kelas', 'pengujiInternal.user', 'tahunAjaran'])
            ->whereIn('jurusan_id', $jurusanIds);

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

        if ($request->filled('jurusan_id')) {
            $this->ensureJurusanAllowed($request->jurusan_id);
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->filled('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->filled('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->filled('predikat')) {
            $query->where('predikat', $request->predikat);
        }

        $ukk = $query->orderBy('tanggal_ujian', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($ukk);
    }

    /**
     * Jurusan options for dropdown (only jurusan where current guru is kepala jurusan).
     */
    public function jurusanOptions()
    {
        $this->ensureKepalaJurusan();
        $jurusan = Jurusan::whereIn('id', $this->allowedJurusanIds())
            ->select('id', 'kode_jurusan', 'nama_jurusan')
            ->orderBy('kode_jurusan')
            ->get();

        return response()->json($jurusan);
    }

    /**
     * Kelas options for a jurusan (only if jurusan is allowed, tingkat 12).
     */
    public function getKelas(Request $request)
    {
        $this->ensureKepalaJurusan();
        $request->validate(['jurusan_id' => 'required|exists:jurusan,id']);
        $this->ensureJurusanAllowed($request->jurusan_id);

        $kelas = Kelas::where('jurusan_id', $request->jurusan_id)
            ->where('tingkat', '12')
            ->orderBy('nama_kelas')
            ->get(['id', 'nama_kelas', 'jurusan_id']);

        return response()->json($kelas);
    }

    /**
     * Siswa options for a kelas (only if kelas belongs to allowed jurusan).
     */
    public function getSiswa(Request $request)
    {
        $this->ensureKepalaJurusan();
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);

        $kelas = Kelas::findOrFail($request->kelas_id);
        $this->ensureJurusanAllowed($kelas->jurusan_id);

        $siswa = Siswa::where('kelas_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nis', 'kelas_id']);

        return response()->json($siswa);
    }

    /**
     * Get siswa list for a kelas with UKK event and existing nilai (for table input).
     */
    public function byKelas(Request $request)
    {
        $this->ensureKepalaJurusan();
        $request->validate(['kelas_id' => 'required|exists:kelas,id']);
        $kelasId = (int) $request->kelas_id;

        $kelas = Kelas::with('jurusan')->findOrFail($kelasId);
        $this->ensureJurusanAllowed($kelas->jurusan_id);

        $event = UkkEvent::where('jurusan_id', $kelas->jurusan_id)
            ->where(function ($q) use ($kelasId) {
                $q->whereNull('kelas_id')->orWhere('kelas_id', $kelasId);
            })
            ->with(['pengujiInternal.user', 'tahunAjaran'])
            ->orderBy('tanggal_ujian', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$event) {
            return response()->json([
                'can_access' => true,
                'event' => null,
                'nama_du_di' => null,
                'penguji_internal' => null,
                'penguji_eksternal' => null,
                'kelas' => $kelas->only(['id', 'nama_kelas', 'jurusan_id']),
                'siswa' => [],
                'message' => 'Data UKK tidak ditemukan untuk kelas ini. Minta admin membuat Data UKK terlebih dahulu.',
            ]);
        }

        $siswaList = Siswa::where('kelas_id', $kelasId)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get(['id', 'nama_lengkap', 'nis', 'kelas_id']);

        $ukkBySiswa = Ukk::where('ukk_event_id', $event->id)
            ->whereIn('siswa_id', $siswaList->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        $siswa = $siswaList->map(function ($s) use ($ukkBySiswa) {
            $ukk = $ukkBySiswa->get($s->id);
            return [
                'id' => $s->id,
                'nama_lengkap' => $s->nama_lengkap,
                'nis' => $s->nis,
                'kelas_id' => $s->kelas_id,
                'ukk' => $ukk ? [
                    'id' => $ukk->id,
                    'nilai_teori' => $ukk->nilai_teori,
                    'nilai_praktek' => $ukk->nilai_praktek,
                    'nilai_akhir' => $ukk->nilai_akhir,
                    'predikat' => $ukk->predikat,
                ] : null,
            ];
        })->values()->all();

        return response()->json([
            'can_access' => true,
            'event' => $event->only(['id', 'nama_du_di', 'tanggal_ujian', 'penguji_internal_id', 'penguji_eksternal']),
            'nama_du_di' => $event->nama_du_di,
            'penguji_internal' => $event->pengujiInternal ? $event->pengujiInternal->nama_lengkap : null,
            'penguji_eksternal' => $event->penguji_eksternal,
            'kelas' => $kelas->only(['id', 'nama_kelas', 'jurusan_id']),
            'siswa' => $siswa,
        ]);
    }

    /**
     * Batch save UKK nilai for a kelas (table input).
     */
    public function simpanKelas(Request $request)
    {
        $this->ensureKepalaJurusan();
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'nilai' => 'required|array',
            'nilai.*.siswa_id' => 'required|exists:siswa,id',
            'nilai.*.nilai_teori' => 'nullable|integer|min:0|max:100',
            'nilai.*.nilai_praktek' => 'nullable|integer|min:0|max:100',
        ]);

        $kelas = Kelas::findOrFail($request->kelas_id);
        $this->ensureJurusanAllowed($kelas->jurusan_id);

        $event = UkkEvent::where('jurusan_id', $kelas->jurusan_id)
            ->where(function ($q) use ($request) {
                $q->whereNull('kelas_id')->orWhere('kelas_id', $request->kelas_id);
            })
            ->orderBy('tanggal_ujian', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$event) {
            return response()->json([
                'message' => 'Data UKK tidak ditemukan untuk kelas ini. Minta admin membuat Data UKK terlebih dahulu.',
            ], 422);
        }

        $updated = 0;
        $created = 0;
        foreach ($request->nilai as $row) {
            $siswaId = (int) $row['siswa_id'];
            $siswa = Siswa::find($siswaId);
            if (!$siswa || $siswa->kelas_id != $request->kelas_id) {
                continue;
            }
            $nilaiTeori = isset($row['nilai_teori']) && $row['nilai_teori'] !== '' ? (int) $row['nilai_teori'] : null;
            $nilaiPraktek = isset($row['nilai_praktek']) && $row['nilai_praktek'] !== '' ? (int) $row['nilai_praktek'] : null;

            $ukk = Ukk::where('ukk_event_id', $event->id)->where('siswa_id', $siswaId)->first();
            if ($ukk) {
                $ukk->update([
                    'nilai_teori' => $nilaiTeori,
                    'nilai_praktek' => $nilaiPraktek,
                ]);
                $updated++;
            } else {
                Ukk::create([
                    'ukk_event_id' => $event->id,
                    'siswa_id' => $siswaId,
                    'jurusan_id' => $event->jurusan_id,
                    'kelas_id' => $event->kelas_id ?? $siswa->kelas_id,
                    'tahun_ajaran_id' => $event->tahun_ajaran_id,
                    'nama_du_di' => $event->nama_du_di,
                    'tanggal_ujian' => $event->tanggal_ujian,
                    'penguji_internal_id' => $event->penguji_internal_id,
                    'penguji_eksternal' => $event->penguji_eksternal,
                    'nilai_teori' => $nilaiTeori,
                    'nilai_praktek' => $nilaiPraktek,
                ]);
                $created++;
            }
        }

        return response()->json([
            'message' => 'Nilai UKK berhasil disimpan.',
            'created' => $created,
            'updated' => $updated,
        ]);
    }

    public function store(Request $request)
    {
        $this->ensureKepalaJurusan();
        $request->validate([
            'jurusan_id' => 'required|exists:jurusan,id',
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswa,id',
            'nilai_teori' => 'nullable|integer|min:0|max:100',
            'nilai_praktek' => 'nullable|integer|min:0|max:100',
        ]);

        $this->ensureJurusanAllowed($request->jurusan_id);

        $event = UkkEvent::where('jurusan_id', $request->jurusan_id)
            ->where(function ($q) use ($request) {
                $q->whereNull('kelas_id')->orWhere('kelas_id', $request->kelas_id);
            })
            ->with('tahunAjaran')
            ->orderBy('tanggal_ujian', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$event) {
            return response()->json([
                'message' => 'Data UKK tidak ditemukan untuk jurusan dan kelas ini. Minta admin membuat Data UKK terlebih dahulu.',
            ], 422);
        }

        $siswa = Siswa::findOrFail($request->siswa_id);
        if ($siswa->kelas_id != $request->kelas_id || $siswa->kelas->jurusan_id != $request->jurusan_id) {
            return response()->json(['message' => 'Siswa tidak berada di jurusan/kelas yang dipilih.'], 422);
        }

        if (Ukk::where('ukk_event_id', $event->id)->where('siswa_id', $request->siswa_id)->exists()) {
            return response()->json(['message' => 'Nilai UKK untuk siswa ini sudah ada pada Data UKK yang sama.'], 422);
        }

        $ukk = Ukk::create([
            'ukk_event_id' => $event->id,
            'siswa_id' => $request->siswa_id,
            'jurusan_id' => $event->jurusan_id,
            'kelas_id' => $event->kelas_id,
            'tahun_ajaran_id' => $event->tahun_ajaran_id,
            'nama_du_di' => $event->nama_du_di,
            'tanggal_ujian' => $event->tanggal_ujian,
            'penguji_internal_id' => $event->penguji_internal_id,
            'penguji_eksternal' => $event->penguji_eksternal,
            'nilai_teori' => $request->nilai_teori ? (int) $request->nilai_teori : null,
            'nilai_praktek' => $request->nilai_praktek ? (int) $request->nilai_praktek : null,
        ]);

        $ukk->load(['siswa.user', 'jurusan', 'kelas', 'pengujiInternal.user', 'tahunAjaran']);
        return response()->json($ukk, 201);
    }

    public function show(Ukk $ukk)
    {
        $this->ensureKepalaJurusan();
        $this->ensureUkkAllowed($ukk);

        $ukk->load(['siswa.user', 'jurusan', 'pengujiInternal.user', 'tahunAjaran']);
        return response()->json($ukk);
    }

    public function update(Request $request, Ukk $ukk)
    {
        $this->ensureKepalaJurusan();
        $this->ensureUkkAllowed($ukk);

        $request->validate([
            'nilai_teori' => 'nullable|integer|min:0|max:100',
            'nilai_praktek' => 'nullable|integer|min:0|max:100',
        ]);

        $up = [];
        if ($request->has('nilai_teori')) {
            $up['nilai_teori'] = $request->nilai_teori === '' || $request->nilai_teori === null ? null : (int) $request->nilai_teori;
        }
        if ($request->has('nilai_praktek')) {
            $up['nilai_praktek'] = $request->nilai_praktek === '' || $request->nilai_praktek === null ? null : (int) $request->nilai_praktek;
        }
        $ukk->update($up);

        $ukk->load(['siswa.user', 'jurusan', 'kelas', 'pengujiInternal.user', 'tahunAjaran']);
        return response()->json($ukk);
    }

    public function destroy(Ukk $ukk)
    {
        $this->ensureKepalaJurusan();
        $this->ensureUkkAllowed($ukk);

        $ukk->delete();

        return response()->json([
            'message' => 'UKK berhasil dihapus',
        ]);
    }
}
