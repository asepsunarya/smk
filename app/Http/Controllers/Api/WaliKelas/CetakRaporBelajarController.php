<?php

namespace App\Http\Controllers\Api\WaliKelas;

use App\Http\Controllers\Api\Concerns\CalculatesNilaiAkhirRapor;
use App\Http\Controllers\Controller;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Nilai;
use App\Models\NilaiPkl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * CetakRaporBelajarController for Wali Kelas
 *
 * Handles "Rapor Pembelajaran" - list siswa, cetak rapor PDF (format Kurmer), cetak transkrip (menyusul).
 * Cetak rapor tidak bisa dicetak jika ada nilai di bawah KKM.
 */
class CetakRaporBelajarController extends Controller
{
    use CalculatesNilaiAkhirRapor;
    /**
     * Get kelas options (only where user is wali kelas).
     */
    public function kelas()
    {
        $user = Auth::user();
        $kelas = $user->kelasAsWali();

        $data = $kelas->map(function ($k) {
            return [
                'id' => $k->id,
                'nama_kelas' => $k->nama_kelas,
                'full_name' => $k->full_name ?? "{$k->nama_kelas} - " . ($k->jurusan->nama_jurusan ?? ''),
            ];
        });

        return response()->json($data);
    }

    /**
     * List siswa for cetak rapor (filter: kelas_id, semester, periode STS/SAS wajib).
     * Rapor hanya bisa dicetak jika nilai periode (STS atau SAS) untuk semester tersebut sudah diisi, semua nilai ≥ KKM, dan rapor sudah disetujui.
     * can_cetak_rapor per periode: jika STS sudah siap cetak sedangkan SAS belum, maka di filter STS tampil bisa cetak, di filter SAS tampil tidak bisa.
     */
    public function index(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'semester' => 'required|in:1,2',
            'jenis' => 'required|in:sts,sas',
        ]);

        $user = Auth::user();
        $kelasIds = $user->kelasAsWali()->pluck('id')->toArray();

        if (!in_array((int) $request->kelas_id, $kelasIds)) {
            return response()->json(['message' => 'Anda bukan wali kelas untuk kelas ini'], 403);
        }

        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        if (!$tahunAjaran) {
            return response()->json(['message' => 'Tahun ajaran aktif tidak ditemukan'], 404);
        }

        $requestJenis = strtolower($request->jenis);

        $siswaList = Siswa::where('kelas_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $result = [];
        $no = 1;
        $requestSemester = $request->semester;
        foreach ($siswaList as $siswa) {
            $periodeSudahDiisi = Nilai::isPeriodeFilledForSiswa($siswa->id, $tahunAjaran->id, $requestJenis, $requestSemester);
            $canCetakRapor = $periodeSudahDiisi
                && $this->canCetakRapor($siswa->id, $tahunAjaran->id, $request->semester, $requestJenis)
                && $this->hasApprovedRapor($siswa->id, $tahunAjaran->id);
            $result[] = [
                'no' => $no++,
                'id' => $siswa->id,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn ?? '-',
                'nis' => $siswa->nis ?? '-',
                'can_cetak_rapor' => $canCetakRapor,
            ];
        }

        return response()->json([
            'data' => $result,
            'tahun_ajaran' => $tahunAjaran,
        ]);
    }

    /**
     * Check if siswa can cetak rapor: nilai akhir rapor per mapel >= KKM.
     * Nilai akhir rapor = rata-rata dari (rata-rata CP-1, CP-2, dst) dan nilai STS/SAS.
     *
     * @param  string  $jenis  'sts' atau 'sas', default 'sas'
     */
    protected function canCetakRapor(int $siswaId, int $tahunAjaranId, string $semester, string $jenis = 'sas'): bool
    {
        $cpTarget = $jenis === 'sas' ? 'akhir_semester' : 'tengah_semester';
        $semesterVal = (string) $semester;
        $nilaiList = Nilai::where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereIn('semester', [$semesterVal, (int) $semester])
            ->whereHas('capaianPembelajaran', function ($q) use ($cpTarget) {
                $q->where('target', $cpTarget)->where('is_active', true);
            })
            ->with(['mataPelajaran', 'capaianPembelajaran'])
            ->get();

        if ($nilaiList->isEmpty()) {
            return false;
        }

        $nilaiByMapel = $nilaiList->groupBy('mata_pelajaran_id');
        $siswa = Siswa::with('kelas')->find($siswaId);
        if (! $siswa || ! $siswa->kelas) {
            return false;
        }

        $mapelKelas = $siswa->kelas->mataPelajaran()
            ->where('mata_pelajaran.is_active', true)
            ->get();

        foreach ($mapelKelas as $mapel) {
            $nilaiGroup = $nilaiByMapel->get($mapel->id, collect());
            $nilaiRapor = $nilaiGroup->isNotEmpty()
                ? $this->calculateNilaiAkhirRapor($nilaiGroup)
                : null;

            if ($nilaiRapor === '-' || $nilaiRapor === null) {
                return false;
            }
            $kkm = $mapel->kkm ?? 75;
            if ((float) $nilaiRapor < (float) $kkm) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if rapor for siswa in tahun ajaran has been approved by kepala sekolah.
     * One Rapor per (siswa_id, tahun_ajaran_id); approval applies to both semesters.
     */
    protected function hasApprovedRapor(int $siswaId, int $tahunAjaranId): bool
    {
        return Rapor::where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereIn('status', ['approved', 'published'])
            ->exists();
    }

    /**
     * Download rapor as PDF (format RAPOR Kurmer).
     * Rejects if periode nilai belum diisi, any nilai < KKM, or rapor not approved by kepala sekolah.
     */
    public function download(Request $request, Siswa $siswa)
    {
        // #region agent log
        \Log::info('[rapor-debug] CetakRaporBelajarController::download:entry', ['siswa_id' => $siswa->id, 'semester' => $request->semester ?? null]);
        @file_put_contents(storage_path('logs/rapor-debug.log'), json_encode(['timestamp' => time(), 'location' => 'CetakRaporBelajarController::download:entry', 'message' => 'download endpoint hit', 'data' => ['siswa_id' => $siswa->id, 'semester' => $request->semester ?? null], 'hypothesisId' => 'H5']) . "\n", FILE_APPEND | LOCK_EX);
        // #endregion

        $request->validate([
            'semester' => 'required|in:1,2',
            'jenis' => 'required|in:sts,sas',
        ]);

        $user = Auth::user();
        $kelasIds = $user->kelasAsWali()->pluck('id')->toArray();

        if (!in_array((int) $siswa->kelas_id, $kelasIds)) {
            \Log::info('[rapor-debug] download:403 wali_kelas');
            return response()->json(['message' => 'Anda bukan wali kelas untuk siswa ini'], 403);
        }

        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        if (!$tahunAjaran) {
            return response()->json(['message' => 'Tahun ajaran aktif tidak ditemukan'], 404);
        }

        $jenis = strtolower($request->jenis);
        if (!Nilai::isPeriodeFilledForSiswa($siswa->id, $tahunAjaran->id, $jenis, $request->semester)) {
            return response()->json([
                'message' => 'Nilai periode (STS/SAS) untuk semester ini belum diisi. Rapor belum dapat dicetak.',
            ], 403);
        }

        if (!$this->canCetakRapor($siswa->id, $tahunAjaran->id, $request->semester, $jenis)) {
            \Log::info('[rapor-debug] download:403 canCetakRapor');
            return response()->json([
                'message' => 'Cetak rapor tidak dapat dilakukan. Terdapat nilai di bawah KKM.',
            ], 403);
        }

        if (!$this->hasApprovedRapor($siswa->id, $tahunAjaran->id)) {
            \Log::info('[rapor-debug] download:403 hasApprovedRapor');
            return response()->json([
                'message' => 'Rapor belum disetujui kepala sekolah. Cetak rapor belum dapat dilakukan.',
            ], 403);
        }

        try {
            $data = $this->buildRaporData($siswa, $tahunAjaran->id, $request->semester, $jenis);
            $titimangsa = $request->filled('titimangsa') ? $request->titimangsa : now()->format('Y-m-d');
            $data['tanggal_rapor'] = \Carbon\Carbon::parse($titimangsa)->locale('id');
            $pdf = Pdf::loadView('rapor.kurmer', $data);
            $pdf->setPaper([0, 0, 595.28, 935.43], 'portrait'); // F4: 210mm x 330mm

            $filename = "rapor-{$siswa->nis}-{$tahunAjaran->tahun}-s{$request->semester}-{$jenis}.pdf";
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('Cetak rapor PDF error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal menghasilkan PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build rapor data for PDF view (format 2 halaman: nilai + ekstrakurikuler/kehadiran/catatan/TTD).
     * STS = tengah semester, SAS = akhir semester. Keduanya bisa di semester 1 atau 2.
     *
     * @param  string  $jenis  'sts' atau 'sas', default 'sas'
     */
    protected function buildRaporData(Siswa $siswa, int $tahunAjaranId, string $semester, string $jenis = 'sas'): array
    {
        // #region agent log
        $logPath = storage_path('logs/rapor-debug.log');
        $log = function (array $data) use ($logPath) {
            @file_put_contents($logPath, json_encode(array_merge(['timestamp' => time(), 'sessionId' => 'debug-session'], $data)) . "\n", FILE_APPEND | LOCK_EX);
        };
        $log(['location' => 'CetakRaporBelajarController::buildRaporData:entry', 'message' => 'buildRaporData entry', 'data' => ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranId, 'semester' => $semester, 'jenis' => $jenis], 'hypothesisId' => 'H5']);
        \Log::info('[rapor-debug] buildRaporData:entry', ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranId, 'semester' => $semester, 'jenis' => $jenis]);
        // #endregion

        $siswa->load(['kelas.jurusan', 'user']);

        $cpTarget = $jenis === 'sas' ? 'akhir_semester' : 'tengah_semester';

        // Daftar mapel: sama seperti API cek-penilaian/sts — dari kelas siswa -> mataPelajaran (kelas_mata_pelajaran)
        $mapelKelas = collect();
        if ($siswa->kelas_id && $siswa->kelas) {
            $mapelKelas = $siswa->kelas->mataPelajaran()
                ->where('mata_pelajaran.is_active', true)
                ->orderBy('mata_pelajaran.nama_mapel')
                ->get();
        }

        $semesterVal = (string) $semester;
        // Nilai: filter by semester DAN CP target (STS/SAS)
        $nilaiList = Nilai::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereIn('semester', [$semesterVal, (int) $semester])
            ->whereHas('capaianPembelajaran', function ($q) use ($cpTarget) {
                $q->where('target', $cpTarget)->where('is_active', true);
            })
            ->with(['mataPelajaran', 'capaianPembelajaran'])
            ->get();

        // #region agent log
        $sampleNilai = $nilaiList->take(3)->map(fn ($n) => [
            'id' => $n->id,
            'mata_pelajaran_id' => $n->mata_pelajaran_id,
            'capaian_pembelajaran_id' => $n->capaian_pembelajaran_id,
            'nilai_rapor' => $n->nilai_rapor,
            'has_cp_relation' => $n->relationLoaded('capaianPembelajaran'),
            'cp_deskripsi_preview' => $n->capaianPembelajaran ? (mb_substr($n->capaianPembelajaran->deskripsi ?? '', 0, 50) . '...') : null,
            'nilai_deskripsi_preview' => $n->deskripsi ? (mb_substr($n->deskripsi, 0, 30) . '...') : null,
        ])->values()->all();
        $log(['location' => 'CetakRaporBelajarController::buildRaporData:after_nilaiList', 'message' => 'nilaiList loaded', 'data' => ['nilai_count' => $nilaiList->count(), 'mapel_kelas_count' => $mapelKelas->count(), 'sample_nilai' => $sampleNilai], 'hypothesisId' => 'H1,H2,H5']);
        \Log::info('[rapor-debug] after_nilaiList', ['nilai_count' => $nilaiList->count(), 'mapel_kelas_count' => $mapelKelas->count(), 'sample_nilai' => $sampleNilai]);
        // #endregion

        $nilaiByMapel = $nilaiList->groupBy('mata_pelajaran_id');

        $order = ['umum', 'kejuruan', 'muatan_lokal'];
        $nilaiByKelompok = [];
        $loggedOneMapel = false;
        foreach ($order as $key) {
            $rows = [];
            foreach ($mapelKelas as $mapel) {
                $k = in_array($mapel->kelompok ?? '', ['umum', 'kejuruan', 'muatan_lokal']) ? $mapel->kelompok : 'umum';
                if ($k !== $key) {
                    continue;
                }
                $nilaiGroup = $nilaiByMapel->get($mapel->id, collect());
                if ($nilaiGroup->isNotEmpty()) {
                    // Nilai Akhir Rapor = rata-rata dari (rata-rata CP-1, CP-2, dst) dan nilai STS/SAS (sama dengan cek penilaian wali kelas)
                    $nilaiRapor = $this->calculateNilaiAkhirRapor($nilaiGroup);
                    // Capaian Kompetensi = deskripsi dari Nilai (kelola sumatif), fallback CP.deskripsi; urut kode_cp
                    // Jika ada 2 CP, gabungkan 2 deskripsi sekaligus
                    // Hilangkan "Nilai Sumatif Tengah Semester" / "Nilai Sumatif Akhir Semester" dari tampilan
                    $sortedByKodeCp = $nilaiGroup->sortBy(fn ($n) => optional($n->capaianPembelajaran)->kode_cp ?? 'Z');
                    $deskripsiParts = $sortedByKodeCp->map(function ($n) {
                        $d = $n->deskripsi;
                        if ($d !== null && trim((string) $d) !== '') {
                            return trim($d);
                        }
                        $d = optional($n->capaianPembelajaran)->deskripsi;
                        return $d !== null && trim((string) $d) !== '' ? trim($d) : null;
                    })->filter()->values()
                    ->map(fn ($s) => preg_replace('/\s*Nilai Sumatif (Tengah|Akhir) Semester\s*/ui', ' ', $s))
                    ->map(fn ($s) => trim(preg_replace('/\s+/', ' ', $s)))
                    ->filter(fn ($s) => $s !== '');
                    $deskripsi = $deskripsiParts->isNotEmpty() ? $deskripsiParts->implode(' ') : '-';

                    // #region agent log
                    if (! $loggedOneMapel) {
                        $loggedOneMapel = true;
                        $log(['location' => 'CetakRaporBelajarController::buildRaporData:per_mapel', 'message' => 'first mapel with nilai', 'data' => ['mapel_id' => $mapel->id, 'nama_mapel' => $mapel->nama_mapel, 'nilai_group_count' => $nilaiGroup->count(), 'nilai_rapor_display' => $nilaiRapor, 'deskripsi_parts_count' => $deskripsiParts->count(), 'deskripsi_preview' => mb_substr($deskripsi, 0, 80)], 'hypothesisId' => 'H1,H2,H3']);
                        \Log::info('[rapor-debug] per_mapel', ['mapel_id' => $mapel->id, 'nama_mapel' => $mapel->nama_mapel, 'nilai_group_count' => $nilaiGroup->count(), 'nilai_rapor_display' => $nilaiRapor, 'deskripsi_parts_count' => $deskripsiParts->count(), 'deskripsi_preview' => mb_substr($deskripsi, 0, 80)]);
                    }
                    // #endregion
                } else {
                    $nilaiRapor = '-';
                    $deskripsi = '-';
                }
                // Cast to string so PDF/Blade always get predictable types
                $rows[] = [
                    'nama_mapel' => (string) ($mapel->nama_mapel ?? '-'),
                    'nilai_rapor' => $nilaiRapor === '-' ? '-' : (string) round((float) $nilaiRapor, 2),
                    'deskripsi' => (string) ($deskripsi ?? '-'),
                ];
            }
            $nilaiByKelompok[$key] = $rows;
        }

        // #region agent log
        $summary = [];
        foreach ($nilaiByKelompok as $key => $rows) {
            $summary[$key] = ['row_count' => count($rows)];
            if (! empty($rows)) {
                $first = $rows[0];
                $summary[$key]['sample'] = ['nama_mapel' => $first['nama_mapel'] ?? null, 'nilai_rapor' => $first['nilai_rapor'] ?? null, 'deskripsi_len' => isset($first['deskripsi']) ? strlen($first['deskripsi']) : 0];
            }
        }
        $log(['location' => 'CetakRaporBelajarController::buildRaporData:final', 'message' => 'nilaiByKelompok built', 'data' => ['nilai_by_kelompok_summary' => $summary], 'hypothesisId' => 'H4']);
        \Log::info('[rapor-debug] buildRaporData:final', ['nilai_by_kelompok_summary' => $summary]);
        // #endregion

        $kehadiran = $siswa->kehadiran()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->first();

        $nilaiPkl = \App\Models\NilaiPkl::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semesterVal)
            ->first();

        $catatan = $siswa->catatanAkademik()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->first();

        $tahunAjaran = TahunAjaran::find($tahunAjaranId);

        $raporDisetujui = Rapor::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereIn('status', ['approved', 'published'])
            ->exists();

        $fase = $this->deriveFase($siswa->kelas);
        $kelasDisplay = $siswa->kelas ? $siswa->kelas->nama_kelas : '-';
        $periode = $jenis === 'sas' ? 'Akhir semester' : 'Tengah semester';
        $waliKelas = $this->getWaliKelasForKelas($siswa->kelas_id);
        $kepalaSekolah = $this->getKepalaSekolahGuru();
        $nilaiEkstrakurikuler = $siswa->nilaiEkstrakurikuler()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->with('ekstrakurikuler')
            ->get();

        return [
            'siswa' => $siswa,
            'nilai_by_kelompok' => $nilaiByKelompok,
            'nilai_belum_diisi' => $nilaiList->isEmpty(),
            'kehadiran' => $kehadiran,
            'nilai_pkl' => $nilaiPkl,
            'catatan_akademik' => $catatan,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
            'periode' => $periode,
            'kelas_display' => $kelasDisplay,
            'nama_sekolah' => config('app.school_name', 'SMKS Progresia Cianjur'),
            'alamat_sekolah' => config('app.school_address', ''),
            'fase' => $fase,
            'nilai_ekstrakurikuler' => $nilaiEkstrakurikuler,
            'wali_kelas' => $waliKelas,
            'kepala_sekolah' => $kepalaSekolah,
            'tanggal_rapor' => \Carbon\Carbon::now()->locale('id'),
            'rapor_disetujui' => $raporDisetujui,
            'ttd_ks_path' => public_path('images/ttd_ks.png'),
        ];
    }

    protected function deriveFase(?\App\Models\Kelas $kelas): string
    {
        if (!$kelas) {
            return 'E';
        }
        $tingkat = $kelas->tingkat ?? '';
        $t = (string) $tingkat;
        if (in_array($t, ['11', 'XI', 'xI', '12', 'XII', 'xII'], true)) {
            return 'F';
        }
        return 'E';
    }

    protected function getWaliKelasForKelas(int $kelasId): ?object
    {
        $wali = \App\Models\WaliKelas::where('kelas_id', $kelasId)
            ->where('is_active', true)
            ->with('guru')
            ->first();
        if (!$wali || !$wali->guru) {
            return null;
        }
        return (object) [
            'nama_lengkap' => $wali->guru->nama_lengkap ?? '-',
            'nuptk' => $wali->guru->nuptk ?? '-',
        ];
    }

    protected function getKepalaSekolahGuru(): ?object
    {
        $user = \App\Models\User::where('role', 'kepala_sekolah')->first();
        if (!$user || !$user->guru) {
            return null;
        }
        $g = $user->guru;
        return (object) [
            'nama_lengkap' => $g->nama_lengkap ?? '-',
            'nuptk' => $g->nuptk ?? '-',
        ];
    }

    /**
     * Build rapor data for PDF - public untuk dipakai Admin cetak rapor.
     *
     * @param  string  $jenis  'sts' atau 'sas', default 'sas'
     */
    public function buildRaporDataPublic(Siswa $siswa, int $tahunAjaranId, string $semester, string $jenis = 'sas'): array
    {
        return $this->buildRaporData($siswa, $tahunAjaranId, $semester, $jenis);
    }

    /**
     * Cetak transkrip hasil belajar (PDF) - format TRANSKRIP HASIL BELAJAR.
     */
    public function transkrip(Request $request, Siswa $siswa)
    {
        $user = Auth::user();
        $kelasIds = $user->kelasAsWali()->pluck('id')->toArray();

        if (! in_array((int) $siswa->kelas_id, $kelasIds)) {
            return response()->json(['message' => 'Anda bukan wali kelas untuk siswa ini'], 403);
        }

        $tahunAjaranId = $request->filled('tahun_ajaran_id') ? (int) $request->tahun_ajaran_id : null;
        if (! $tahunAjaranId) {
            $ta = TahunAjaran::where('is_active', true)->first();
            if (! $ta) {
                return response()->json(['message' => 'Tahun ajaran aktif tidak ditemukan'], 404);
            }
            $tahunAjaranId = $ta->id;
        }

        $semester = $request->filled('semester') ? (string) $request->semester : '1';
        if (! in_array($semester, ['1', '2'], true)) {
            $semester = '1';
        }

        $jenis = $request->filled('jenis') ? strtolower($request->jenis) : 'sas';
        if (! in_array($jenis, ['sts', 'sas'], true)) {
            $jenis = 'sas';
        }

        $data = $this->buildRaporData($siswa, $tahunAjaranId, $semester, $jenis);

        $tahunAjaran = TahunAjaran::find($tahunAjaranId);
        $tahunInt = $tahunAjaran ? (int) $tahunAjaran->tahun : 0;
        $data['semester_romawi'] = $semester === '2' ? 'II' : 'I';
        $data['tahun_pelajaran_label'] = $tahunInt ? $tahunInt . '/' . ($tahunInt + 1) : '-';
        $data['nama_sekolah'] = config('app.school_name', 'SMKS Progresia Cianjur');
        $data['alamat_sekolah'] = config('app.school_address', '');
        $titimangsa = $request->filled('titimangsa') ? $request->titimangsa : now()->format('Y-m-d');
        $data['tanggal_rapor'] = \Carbon\Carbon::parse($titimangsa)->locale('id');

        try {
            $pdf = Pdf::loadView('rapor.transkrip', $data);
            $pdf->setPaper('a4', 'portrait');
            $filename = 'transkrip-' . ($siswa->nis ?? $siswa->id) . '-s' . $semester . '.pdf';
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('Wali Kelas cetak transkrip PDF error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal menghasilkan PDF: ' . $e->getMessage()], 500);
        }
    }
}
