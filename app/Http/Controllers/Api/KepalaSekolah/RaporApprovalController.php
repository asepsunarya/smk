<?php

namespace App\Http\Controllers\Api\KepalaSekolah;

use App\Http\Controllers\Api\Concerns\CalculatesNilaiAkhirRapor;
use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\NilaiPkl;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\NilaiP5;
use App\Models\TahunAjaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * RaporApprovalController for Kepala Sekolah
 * 
 * Handles rapor approval for principal
 */
class RaporApprovalController extends Controller
{
    use CalculatesNilaiAkhirRapor;
    /**
     * Get rapor for approval with filters.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $tahunAjaranId = $request->filled('tahun_ajaran_id') ? (int) $request->tahun_ajaran_id : 0;
        $kelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : 0;
        $jenis = $request->filled('jenis') ? strtolower($request->jenis) : '';
        $status = $request->filled('status') ? strtolower((string) $request->status) : '';

        // Jika filter lengkap (tahun ajaran, kelas, periode, status): tampilkan daftar siswa dengan rapor (atau null)
        $statusValid = in_array($status, ['belum_disetujui', 'setujui'], true);
        if ($tahunAjaranId && $kelasId && in_array($jenis, ['sts', 'sas'], true) && $statusValid) {
            $tahunAjaran = \App\Models\TahunAjaran::find($tahunAjaranId);
            $kelas = \App\Models\Kelas::with('jurusan')->find($kelasId);
            if (! $tahunAjaran || ! $kelas) {
                return response()->json([
                    'data' => [],
                    'summary' => ['pending' => 0, 'approved' => 0, 'rejected' => 0, 'total' => 0],
                    'pagination' => ['current_page' => 1, 'last_page' => 1, 'per_page' => 15, 'total' => 0],
                ]);
            }
            $siswaList = Siswa::where('kelas_id', $kelasId)
                ->where('status', 'aktif')
                ->with(['user', 'kelas.jurusan'])
                ->orderBy('nama_lengkap')
                ->get();
            $raporBySiswa = Rapor::where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('kelas_id', $kelasId)
                ->with(['approver'])
                ->get()
                ->keyBy('siswa_id');
            $list = [];
            foreach ($siswaList as $siswa) {
                $rapor = $raporBySiswa->get($siswa->id);
                $periodeSudahDiisi = \App\Models\Nilai::isPeriodeFilledForSiswa($siswa->id, $tahunAjaran->id, $jenis, $tahunAjaran->semester ?? null);
                $row = [
                    'siswa' => $siswa,
                    'rapor' => $rapor ? $rapor->load(['tahunAjaran']) : null,
                    'tahun_ajaran' => $tahunAjaran,
                    'kelas' => $kelas,
                    'periode_sudah_diisi' => $periodeSudahDiisi,
                ];
                // Filter by status: setujui | belum_disetujui
                $raporStatus = $rapor?->status ?? null;
                $match = false;
                if ($status === 'setujui') {
                    $match = $rapor && in_array($raporStatus, ['approved', 'published'], true);
                } elseif ($status === 'belum_disetujui') {
                    $match = ! $rapor || in_array($raporStatus, ['draft', 'pending', 'rejected'], true);
                }
                if ($match) {
                    $list[] = $row;
                }
            }
            $summaryQuery = Rapor::where('tahun_ajaran_id', $tahunAjaran->id)->where('kelas_id', $kelasId);
            $summary = [
                'pending' => (clone $summaryQuery)->where(function ($q) {
                    $q->where('status', 'pending')
                        ->orWhere(function ($q2) {
                            $q2->where('status', 'draft')->whereNull('approved_at');
                        });
                })->count(),
                'approved' => (clone $summaryQuery)->whereIn('status', ['approved', 'published'])->count(),
                'rejected' => (clone $summaryQuery)->where('status', 'rejected')->count(),
                'total' => (clone $summaryQuery)->count(),
            ];
            return response()->json([
                'data' => $list,
                'summary' => $summary,
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => count($list),
                    'total' => count($list),
                ],
            ]);
        }

        $query = Rapor::with(['siswa.user', 'siswa.kelas.jurusan', 'kelas.jurusan', 'tahunAjaran', 'approver']);

        // Filter by status
        if ($request->has('status') && $request->status !== '') {
            $status = $request->status;
            if ($status === 'setujui') {
                $query->whereIn('status', ['approved', 'published']);
            } elseif ($status === 'belum') {
                $query->whereIn('status', ['draft', 'pending']);
            } elseif ($status === 'pending') {
                $query->where(function ($q) {
                    $q->where('status', 'pending')
                      ->orWhere(function ($q2) {
                          $q2->where('status', 'draft')->whereNull('approved_at');
                      });
                });
            } else {
                $query->where('status', $status);
            }
        }

        // Filter by tahun ajaran
        if ($request->has('tahun_ajaran_id') && $request->tahun_ajaran_id !== '') {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        // Filter by kelas
        if ($request->has('kelas_id') && $request->kelas_id !== '') {
            $query->where('kelas_id', $request->kelas_id);
        }

        // STS/SAS bukan mapping ke semester 1/2 — periode tengah/akhir bisa di tiap semester
        // Filter jenis tidak diterapkan ke query Rapor (Rapor per tahun_ajaran, jenis dipilih saat preview)

        $rapor = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        // Summary dengan filter yang sama agar angka sesuai daftar
        $summaryQuery = Rapor::query();
        if ($request->has('tahun_ajaran_id') && $request->tahun_ajaran_id !== '') {
            $summaryQuery->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }
        if ($request->has('kelas_id') && $request->kelas_id !== '') {
            $summaryQuery->where('kelas_id', $request->kelas_id);
        }

        $summary = [
            'pending' => (clone $summaryQuery)->where(function ($q) {
                $q->where('status', 'pending')
                  ->orWhere(function ($q2) {
                      $q2->where('status', 'draft')->whereNull('approved_at');
                  });
            })->count(),
            'approved' => (clone $summaryQuery)->whereIn('status', ['approved', 'published'])->count(),
            'rejected' => (clone $summaryQuery)->where('status', 'rejected')->count(),
            'total' => (clone $summaryQuery)->count(),
        ];

        return response()->json([
            'data' => $rapor->items(),
            'summary' => $summary,
            'pagination' => [
                'current_page' => $rapor->currentPage(),
                'last_page' => $rapor->lastPage(),
                'per_page' => $rapor->perPage(),
                'total' => $rapor->total(),
            ],
        ]);
    }

    /**
     * Get pending rapor for approval.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function pending(Request $request)
    {
        $query = Rapor::where('status', 'pending')
                     ->with(['siswa.user', 'siswa.kelas.jurusan', 'tahunAjaran']);

        if ($request->has('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        $rapor = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        return response()->json($rapor);
    }

    /**
     * Display the specified rapor.
     *
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Rapor $rapor)
    {
        $rapor->load([
            'siswa.user',
            'siswa.kelas.jurusan',
            'tahunAjaran',
            'approver',
        ]);

        // Load related data
        $rapor->nilai = $rapor->nilai;
        $rapor->kehadiran = $rapor->kehadiran;
        $rapor->catatan_akademik = $rapor->catatan_akademik;
        $rapor->nilai_ekstrakurikuler = $rapor->siswa->nilaiEkstrakurikuler()
                                                      ->where('tahun_ajaran_id', $rapor->tahun_ajaran_id)
                                                      ->with('ekstrakurikuler')
                                                      ->get();
        $rapor->nilai_p5 = $rapor->siswa->nilaiP5()
                                        ->with(['p5', 'dimensi'])
                                        ->get();

        return response()->json([
            'data' => $rapor,
        ]);
    }

    /**
     * Preview rapor belajar as PDF (sama dengan cetak rapor di Wali Kelas).
     * Mengembalikan PDF inline agar bisa dibuka di tab baru.
     */
    public function previewRaporBelajar(Request $request, Siswa $siswa)
    {
        $request->validate([
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
            'semester' => 'required|in:1,2',
            'jenis' => 'required|in:sts,sas',
        ]);

        $tahunAjaranId = (int) $request->tahun_ajaran_id;
        $semester = $request->semester;
        $jenis = strtolower($request->jenis);

        try {
            $data = $this->buildRaporDataForPdf($siswa, $tahunAjaranId, $semester, $jenis);
            $titimangsa = $request->filled('titimangsa') ? $request->titimangsa : now()->format('Y-m-d');
            $data['tanggal_rapor'] = \Carbon\Carbon::parse($titimangsa)->locale('id');
            $pdf = Pdf::loadView('rapor.kurmer', $data);
            $pdf->setPaper([0, 0, 595.28, 935.43], 'portrait'); // F4: 210mm x 330mm

            $tahunAjaran = TahunAjaran::find($tahunAjaranId);
            $filename = "rapor-{$siswa->nis}-{$tahunAjaran->tahun}-s{$semester}.pdf";

            return $pdf->stream($filename);
        } catch (\Throwable $e) {
            \Log::error('Preview rapor PDF error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'message' => 'Gagal menghasilkan PDF: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Build rapor data for PDF view (sama dengan CetakRaporBelajarController).
     * STS = tengah semester, SAS = akhir semester. Keduanya bisa di semester 1 atau 2.
     *
     * @param  string  $jenis  'sts' (tengah semester) atau 'sas' (akhir semester)
     */
    protected function buildRaporDataForPdf(Siswa $siswa, int $tahunAjaranId, string $semester, string $jenis = 'sts'): array
    {
        // #region agent log
        $logPath = storage_path('logs/rapor-debug.log');
        $log = function (array $data) use ($logPath) {
            @file_put_contents($logPath, json_encode(array_merge(['timestamp' => time(), 'sessionId' => 'debug-session', 'source' => 'KepalaSekolah'], $data)) . "\n", FILE_APPEND | LOCK_EX);
        };
        $log(['location' => 'RaporApprovalController::buildRaporDataForPdf:entry', 'message' => 'buildRaporDataForPdf entry', 'data' => ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranId, 'semester' => $semester, 'jenis' => $jenis], 'hypothesisId' => 'H5']);
        \Log::info('[rapor-debug] RaporApprovalController::buildRaporDataForPdf:entry', ['siswa_id' => $siswa->id, 'tahun_ajaran_id' => $tahunAjaranId, 'semester' => $semester, 'jenis' => $jenis]);
        // #endregion

        $siswa->load(['kelas.jurusan', 'user']);

        // STS = tengah semester (CP target tengah_semester), SAS = akhir semester (CP target akhir_semester)
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
        // Nilai: filter by semester DAN CP target (STS/SAS) — hanya ambil nilai untuk periode yang dipilih
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
        $log(['location' => 'RaporApprovalController::buildRaporDataForPdf:after_nilaiList', 'message' => 'nilaiList loaded', 'data' => ['nilai_count' => $nilaiList->count(), 'mapel_kelas_count' => $mapelKelas->count(), 'sample_nilai' => $sampleNilai], 'hypothesisId' => 'H1,H2,H5']);
        \Log::info('[rapor-debug] RaporApprovalController::after_nilaiList', ['nilai_count' => $nilaiList->count(), 'mapel_kelas_count' => $mapelKelas->count(), 'sample_nilai' => $sampleNilai]);
        // #endregion

        $nilaiByMapel = $nilaiList->groupBy('mata_pelajaran_id');

        $order = ['umum', 'kejuruan', 'muatan_lokal'];
        $nilaiByKelompok = [];
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
                    static $loggedOneMapelKepala = false;
                    if (! $loggedOneMapelKepala) {
                        $loggedOneMapelKepala = true;
                        $log(['location' => 'RaporApprovalController::buildRaporDataForPdf:per_mapel', 'message' => 'first mapel with nilai', 'data' => ['mapel_id' => $mapel->id, 'nama_mapel' => $mapel->nama_mapel, 'nilai_group_count' => $nilaiGroup->count(), 'nilai_rapor_display' => $nilaiRapor, 'deskripsi_parts_count' => $deskripsiParts->count(), 'deskripsi_preview' => mb_substr($deskripsi, 0, 80)], 'hypothesisId' => 'H1,H2,H3']);
                        \Log::info('[rapor-debug] RaporApprovalController::per_mapel', ['mapel_id' => $mapel->id, 'nama_mapel' => $mapel->nama_mapel, 'nilai_group_count' => $nilaiGroup->count(), 'nilai_rapor_display' => $nilaiRapor, 'deskripsi_parts_count' => $deskripsiParts->count(), 'deskripsi_preview' => mb_substr($deskripsi, 0, 80)]);
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
        $log(['location' => 'RaporApprovalController::buildRaporDataForPdf:final', 'message' => 'nilaiByKelompok built', 'data' => ['nilai_by_kelompok_summary' => $summary], 'hypothesisId' => 'H4']);
        \Log::info('[rapor-debug] RaporApprovalController::final', ['nilai_by_kelompok_summary' => $summary]);
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

        $fase = $this->deriveFaseForRapor($siswa->kelas);
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

    protected function deriveFaseForRapor(?\App\Models\Kelas $kelas): string
    {
        if (! $kelas) {
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
        if (! $wali || ! $wali->guru) {
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
        if (! $user || ! $user->guru) {
            return null;
        }
        $g = $user->guru;
        return (object) [
            'nama_lengkap' => $g->nama_lengkap ?? '-',
            'nuptk' => $g->nuptk ?? '-',
        ];
    }

    /**
     * Approve a rapor (by rapor id).
     */
    public function approve(Request $request, Rapor $rapor)
    {
        if (! in_array($rapor->status, ['pending', 'draft'], true)) {
            return response()->json([
                'message' => 'Rapor tidak dalam status pending atau draft',
            ], 422);
        }

        $user = Auth::user();
        $rapor->approve($user->id);

        return response()->json([
            'message' => 'Rapor berhasil disetujui',
            'data' => $rapor->load(['siswa.user', 'siswa.kelas.jurusan', 'tahunAjaran', 'approver']),
        ]);
    }

    /**
     * Approve rapor by siswa + tahun ajaran. Jika rapor belum ada, dibuat lalu disetujui.
     * Kepala sekolah bisa langsung approve jika nilai periode (STS/SAS) sudah diisi.
     */
    public function approveBySiswa(Request $request)
    {
        $request->validate([
            'siswa_id' => 'required|exists:siswa,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        $user = Auth::user();
        $siswa = Siswa::findOrFail($request->siswa_id);

        $rapor = Rapor::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->first();

        if (! $rapor) {
            $rapor = Rapor::create([
                'siswa_id' => $siswa->id,
                'tahun_ajaran_id' => $request->tahun_ajaran_id,
                'kelas_id' => $siswa->kelas_id,
                'tanggal_rapor' => now(),
                'status' => 'approved',
                'approved_by' => $user->id,
                'approved_at' => now(),
            ]);
        } else {
            if (! in_array($rapor->status, ['pending', 'draft'], true)) {
                return response()->json([
                    'message' => 'Rapor sudah disetujui atau dipublikasi',
                ], 422);
            }
            $rapor->approve($user->id);
        }

        return response()->json([
            'message' => 'Rapor berhasil disetujui',
            'data' => $rapor->load(['siswa.user', 'siswa.kelas.jurusan', 'tahunAjaran', 'approver']),
        ]);
    }

    /**
     * Reject a rapor.
     *
     * @param  Request  $request
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\JsonResponse
     */
    public function reject(Request $request, Rapor $rapor)
    {
        if ($rapor->status !== 'pending') {
            return response()->json([
                'message' => 'Rapor tidak dalam status pending',
            ], 422);
        }

        $request->validate([
            'reason' => 'nullable|string|max:500',
            'alasan' => 'nullable|string|max:500', // Support both
        ]);

        $rapor->update([
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json([
            'message' => 'Rapor ditolak dan dikembalikan ke draft',
            'data' => $rapor->load(['siswa.user', 'siswa.kelas.jurusan', 'tahunAjaran']),
        ]);
    }

    /**
     * Unapprove (Belum disetujui) - ubah rapor yang sudah disetujui kembali ke draft.
     *
     * @param  Request  $request
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\JsonResponse
     */
    public function unapprove(Request $request, Rapor $rapor)
    {
        if (! in_array($rapor->status, ['approved', 'published'], true)) {
            return response()->json([
                'message' => 'Rapor belum disetujui, tidak dapat diubah ke belum disetujui',
            ], 422);
        }

        $rapor->update([
            'status' => 'draft',
            'approved_by' => null,
            'approved_at' => null,
        ]);

        return response()->json([
            'message' => 'Rapor berhasil diubah ke belum disetujui',
            'data' => $rapor->load(['siswa.user', 'siswa.kelas.jurusan', 'tahunAjaran']),
        ]);
    }

    /**
     * Batch approve multiple rapor.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchApprove(Request $request)
    {
        $request->validate([
            'rapor_ids' => 'required|array',
            'rapor_ids.*' => 'exists:rapor,id',
        ]);

        $user = Auth::user();

        DB::beginTransaction();
        try {
            $rapor = Rapor::whereIn('id', $request->rapor_ids)
                         ->where('status', 'pending')
                         ->get();

            foreach ($rapor as $r) {
                $r->approve($user->id);
            }

            DB::commit();

            return response()->json([
                'message' => count($rapor) . ' rapor berhasil disetujui',
                'approved_count' => count($rapor),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menyetujui rapor',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

