<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Api\Concerns\BuildsLeggerData;
use App\Http\Controllers\Api\Concerns\BuildsRaporP5Data;
use App\Http\Controllers\Api\Concerns\CalculatesNilaiAkhirRapor;
use App\Http\Controllers\Controller;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\NilaiP5;
use App\Models\P5;
use App\Models\Nilai;
use App\Models\MataPelajaran;
use App\Models\Kehadiran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * CetakRaporController for Admin
 * 
 * Handles report card printing for admin
 */
class CetakRaporController extends Controller
{
    use BuildsLeggerData;
    use BuildsRaporP5Data;
    use CalculatesNilaiAkhirRapor;

    /**
     * Get rapor hasil belajar list (daftar siswa per kelas, sama seperti Wali Kelas).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function hasilBelajar(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'semester' => 'required|in:1,2',
            'jenis' => 'required|in:sts,sas',
        ]);

        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        if (! $tahunAjaran) {
            return response()->json([
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        $requestJenis = strtolower($request->jenis);
        $requestSemester = $request->semester;

        $siswaQuery = Siswa::where('kelas_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap');

        if ($request->filled('search')) {
            $search = $request->search;
            $siswaQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        $siswaList = $siswaQuery->get();
        $raporBySiswa = Rapor::where('tahun_ajaran_id', $tahunAjaran->id)
            ->where('kelas_id', $request->kelas_id)
            ->whereIn('siswa_id', $siswaList->pluck('id'))
            ->whereIn('status', ['approved', 'published'])
            ->get()
            ->keyBy('siswa_id');

        $result = [];
        $no = 1;
        foreach ($siswaList as $siswa) {
            $periodeSudahDiisi = Nilai::isPeriodeFilledForSiswa($siswa->id, $tahunAjaran->id, $requestJenis, $requestSemester);
            $canCetakRapor = $periodeSudahDiisi
                && $this->canCetakRapor($siswa->id, $tahunAjaran->id, $requestSemester, $requestJenis)
                && $this->hasApprovedRapor($siswa->id, $tahunAjaran->id);
            $rapor = $raporBySiswa->get($siswa->id);
            $result[] = [
                'id' => $siswa->id,
                'no' => $no++,
                'nama_lengkap' => $siswa->nama_lengkap,
                'nisn' => $siswa->nisn ?? '-',
                'nis' => $siswa->nis ?? '-',
                'can_cetak_rapor' => $canCetakRapor,
                'rapor' => $rapor ? [
                    'id' => $rapor->id,
                    'tahun_ajaran_id' => $rapor->tahun_ajaran_id,
                    'semester' => $requestSemester,
                    'jenis' => $requestJenis,
                    'status' => $rapor->status,
                ] : null,
                'siswa' => [
                    'id' => $siswa->id,
                    'nama_lengkap' => $siswa->nama_lengkap,
                    'nisn' => $siswa->nisn ?? '-',
                    'nis' => $siswa->nis ?? '-',
                ],
                'tahun_ajaran' => [
                    'id' => $tahunAjaran->id,
                    'tahun' => $tahunAjaran->tahun,
                    'semester' => $tahunAjaran->semester,
                ],
            ];
        }

        return response()->json([
            'data' => $result,
            'tahun_ajaran' => $tahunAjaran,
        ]);
    }

    protected function canCetakRapor(int $siswaId, int $tahunAjaranId, string $semester, string $jenis = 'sas'): bool
    {
        $cpTarget = $jenis === 'sas' ? 'akhir_semester' : 'tengah_semester';
        $semesterVal = (string) $semester;
        $nilaiList = Nilai::where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereIn('semester', [$semesterVal, (int) $semester])
            ->whereHas('capaianPembelajaran', fn ($q) => $q->where('target', $cpTarget)->where('is_active', true))
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

    protected function hasApprovedRapor(int $siswaId, int $tahunAjaranId): bool
    {
        return Rapor::where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->whereIn('status', ['approved', 'published'])
            ->exists();
    }

    /**
     * Get detail rapor hasil belajar for printing.
     *
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailHasilBelajar(Rapor $rapor)
    {
        if (!in_array($rapor->status, ['approved', 'published'])) {
            return response()->json([
                'message' => 'Rapor belum disetujui kepala sekolah. Hanya rapor yang sudah disetujui yang dapat dilihat dan dicetak.',
            ], 403);
        }

        $rapor->load([
            'siswa.user',
            'siswa.kelas.jurusan',
            'tahunAjaran',
            'approver'
        ]);

        // Load nilai
        $rapor->nilai = $rapor->siswa->nilai()
            ->where('tahun_ajaran_id', $rapor->tahun_ajaran_id)
            ->with('mataPelajaran')
            ->get()
            ->groupBy('mataPelajaran.kelompok');

        // Load kehadiran
        $rapor->kehadiran = $rapor->siswa->kehadiran()
            ->where('tahun_ajaran_id', $rapor->tahun_ajaran_id)
            ->first();

        // Load catatan akademik
        $rapor->catatan_akademik = $rapor->siswa->catatanAkademik()
            ->where('tahun_ajaran_id', $rapor->tahun_ajaran_id)
            ->first();

        // Load nilai ekstrakurikuler
        $rapor->nilai_ekstrakurikuler = $rapor->siswa->nilaiEkstrakurikuler()
            ->where('tahun_ajaran_id', $rapor->tahun_ajaran_id)
            ->with('ekstrakurikuler')
            ->get();

        // Load nilai P5
        $rapor->nilai_p5 = $rapor->siswa->nilaiP5()
            ->with(['p5', 'dimensi'])
            ->get()
            ->groupBy('p5_id');

        return response()->json($rapor);
    }

    /**
     * Download rapor as PDF (sama dengan Kepala Sekolah / Wali Kelas - format kurmer).
     *
     * @param  Request  $request  Query: semester (1|2), jenis (sts|sas), default semester=1, jenis=sas
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\Response
     */
    public function downloadHasilBelajar(Request $request, Rapor $rapor)
    {
        if (!in_array($rapor->status, ['approved', 'published'])) {
            return response()->json(['message' => 'Rapor belum disetujui'], 403);
        }

        $rapor->load(['siswa.user', 'siswa.kelas.jurusan', 'tahunAjaran', 'approver']);
        $siswa = $rapor->siswa;
        $tahunAjaranId = (int) $rapor->tahun_ajaran_id;

        $semester = $request->filled('semester') ? (string) $request->semester : ($rapor->semester ?? '1');
        if (!in_array($semester, ['1', '2'], true)) {
            $semester = '1';
        }
        $jenis = $request->filled('jenis') ? strtolower($request->jenis) : 'sas';
        if (!in_array($jenis, ['sts', 'sas'], true)) {
            $jenis = 'sas';
        }

        try {
            $controller = app(\App\Http\Controllers\Api\WaliKelas\CetakRaporBelajarController::class);
            $data = $controller->buildRaporDataPublic($siswa, $tahunAjaranId, $semester, $jenis);
            $titimangsa = $request->filled('titimangsa') ? $request->titimangsa : now()->format('Y-m-d');
            $data['tanggal_rapor'] = \Carbon\Carbon::parse($titimangsa)->locale('id');
            $pdf = Pdf::loadView('rapor.kurmer', $data);
            $pdf->setPaper([0, 0, 595.28, 935.43], 'portrait'); // F4: 210mm x 330mm
            $filename = "rapor-{$siswa->nis}-{$rapor->tahunAjaran->tahun}-s{$semester}.pdf";
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('Admin cetak rapor PDF error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal menghasilkan PDF: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Preview rapor hasil belajar.
     *
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewHasilBelajar(Rapor $rapor)
    {
        return $this->detailHasilBelajar($rapor);
    }

    /**
     * Get rapor hasil P5 list.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function hasilP5(Request $request)
    {
        $query = Siswa::with([
            'user',
            'kelas.jurusan'
        ])->whereHas('nilaiP5'); // Only show students who have P5 scores

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->has('tahun_ajaran_id') && $request->tahun_ajaran_id) {
            $query->whereHas('nilaiP5', function ($q) use ($request) {
                $q->whereHas('p5', function ($qp) use ($request) {
                    $qp->where('tahun_ajaran_id', $request->tahun_ajaran_id);
                });
            });
        }

        $siswa = $query->orderBy('nama_lengkap')->paginate($request->get('per_page', 15));

        // Add P5 summary for each student
        $siswa->getCollection()->transform(function ($item) use ($request) {
            $tahunAjaranId = $request->tahun_ajaran_id;
            
            // Use relationship query, not collection method
            $nilaiP5Query = $item->nilaiP5()->with(['p5.tahunAjaran', 'dimensi']);
            
            if ($tahunAjaranId) {
                $nilaiP5Query->whereHas('p5', function ($q) use ($tahunAjaranId) {
                    $q->where('tahun_ajaran_id', $tahunAjaranId);
                });
            }
            
            $nilaiP5 = $nilaiP5Query->get();
            
            $item->p5_projects = $nilaiP5->groupBy('p5_id')->map(function ($nilai) {
                $p5 = $nilai->first()->p5;
                return [
                    'id' => $p5->id,
                    'tema' => $p5->tema,
                    'tahun_ajaran' => $p5->tahunAjaran ? 
                        "{$p5->tahunAjaran->tahun} - Semester {$p5->tahunAjaran->semester}" : null,
                    'dimensi_count' => $nilai->count(),
                ];
            })->values();
            
            $item->total_p5_projects = $item->p5_projects->count();
            
            return $item;
        });

        return response()->json($siswa);
    }

    /**
     * Get detail rapor hasil P5 for a student (per sub_elemen dengan predikat).
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailHasilP5(Request $request, Siswa $siswa)
    {
        $payload = $this->getDetailHasilP5Payload($request, $siswa);
        return response()->json($payload);
    }

    /**
     * Preview rapor hasil P5.
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function previewHasilP5(Request $request, Siswa $siswa)
    {
        return $this->detailHasilP5($request, $siswa);
    }

    /**
     * Download rapor P5 as PDF.
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function downloadHasilP5(Request $request, Siswa $siswa)
    {
        $siswa->load(['kelas.jurusan']);
        $payload = $this->detailHasilP5($request, $siswa)->getData(true);
        $data = $this->buildRaporP5PdfData($payload, $siswa);
        $data['siswa'] = $siswa;

        $pdf = Pdf::loadView('rapor.hasil-p5', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = 'rapor-p5-' . preg_replace('/[^a-zA-Z0-9\-_.]/', '-', $siswa->nis) . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Get legger (grade book) for a class.
     *
     * @param  Request  $request
     * @param  Kelas  $kelas
     * @return \Illuminate\Http\JsonResponse
     */
    public function legger(Request $request, Kelas $kelas)
    {
        $payload = $this->getLeggerPayload($request, $kelas);
        if (isset($payload['message'])) {
            return response()->json($payload, 404);
        }

        return response()->json($payload);
    }

    /**
     * Download legger as Excel (.xlsx).
     * Format: LEGER NILAI RAPOR SISWA TAHUN PELAJARAN ... GENAP/GANJIL, SEKOLAH, Kelas,
     * tabel No | NAMA SISWA | NISN | NIS | [Mapel sesuai kelas] | Sakit | Izin | Alpa.
     *
     * @param  Request  $request
     * @param  Kelas  $kelas
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function downloadLegger(Request $request, Kelas $kelas)
    {
        $payload = $this->getLeggerPayload($request, $kelas);
        if (isset($payload['message'])) {
            return response()->json($payload, 404);
        }

        return $this->downloadLeggerAsExcel($payload, $kelas);
    }

    /**
     * List siswa untuk cetak transkrip (filter: kelas_id, tahun_ajaran_id, semester, jenis).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function transkripList(Request $request)
    {
        $tahunAjaranId = $request->filled('tahun_ajaran_id') ? (int) $request->tahun_ajaran_id : null;
        $kelasId = $request->filled('kelas_id') ? (int) $request->kelas_id : null;

        if (! $tahunAjaranId) {
            $tahunAjaran = TahunAjaran::where('is_active', true)->first();
            $tahunAjaranId = $tahunAjaran ? $tahunAjaran->id : null;
        }

        $query = Siswa::with(['user', 'kelas.jurusan'])
            ->where('status', 'aktif');

        if ($kelasId) {
            $query->where('kelas_id', $kelasId);
        }

        if ($request->has('search') && trim((string) $request->search) !== '') {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%")
                    ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswa = $query->orderBy('nama_lengkap')->paginate($request->get('per_page', 15));

        return response()->json($siswa);
    }

    /**
     * Download transkrip hasil belajar (PDF) untuk satu siswa.
     * Query: tahun_ajaran_id, semester (1|2), jenis (sts|sas).
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\Response
     */
    public function transkripDownload(Request $request, Siswa $siswa)
    {
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

        $waliKelasController = app(\App\Http\Controllers\Api\WaliKelas\CetakRaporBelajarController::class);
        $data = $waliKelasController->buildRaporDataPublic($siswa, $tahunAjaranId, $semester, $jenis);

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
            \Log::error('Admin cetak transkrip PDF error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json(['message' => 'Gagal menghasilkan PDF: ' . $e->getMessage()], 500);
        }
    }
}

