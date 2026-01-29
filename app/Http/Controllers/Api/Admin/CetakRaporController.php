<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rapor;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\NilaiP5;
use App\Models\P5;
use App\Models\Nilai;
use App\Models\MataPelajaran;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * CetakRaporController for Admin
 * 
 * Handles report card printing for admin
 */
class CetakRaporController extends Controller
{
    /**
     * Get rapor hasil belajar list.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function hasilBelajar(Request $request)
    {
        // Get active tahun ajaran automatically
        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        
        if (!$tahunAjaran) {
            return response()->json([
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        $query = Rapor::with([
            'siswa.user',
            'siswa.kelas.jurusan',
            'tahunAjaran',
            'approver'
        ])->where('tahun_ajaran_id', $tahunAjaran->id);

        if ($request->has('search')) {
            $search = $request->search;
            $query->whereHas('siswa', function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        // Filter by semester - check if siswa has nilai with this semester
        if ($request->has('semester') && $request->semester) {
            $query->whereHas('siswa.nilai', function ($q) use ($tahunAjaran, $request) {
                $q->where('tahun_ajaran_id', $tahunAjaran->id)
                  ->where('semester', $request->semester);
            });
        }

        if ($request->has('status')) {
            // Map status: 'disetujui' -> 'approved', 'tidak disetujui' -> others
            if ($request->status === 'disetujui') {
                $query->where('status', 'approved');
            } elseif ($request->status === 'tidak disetujui') {
                $query->where('status', '!=', 'approved');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Only show approved or published rapor for printing if no status filter
        if (!$request->has('status')) {
            $query->whereIn('status', ['approved', 'published']);
        }

        $rapor = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

        // Add semester information to each rapor
        $rapor->getCollection()->transform(function ($item) use ($tahunAjaran, $request) {
            // If semester filter is provided, use it
            if ($request->has('semester') && $request->semester) {
                $item->semester = $request->semester;
            } else {
                // Get the most common semester from nilai for this siswa
                $semesterCounts = Nilai::where('siswa_id', $item->siswa_id)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->whereNotNull('semester')
                    ->selectRaw('semester, COUNT(*) as count')
                    ->groupBy('semester')
                    ->orderByDesc('count')
                    ->first();
                
                if ($semesterCounts) {
                    $item->semester = $semesterCounts->semester;
                } else {
                    $item->semester = null;
                }
            }
            
            return $item;
        });

        return response()->json($rapor);
    }

    /**
     * Get detail rapor hasil belajar for printing.
     *
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailHasilBelajar(Rapor $rapor)
    {
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
     * Download rapor as PDF.
     *
     * @param  Rapor  $rapor
     * @return \Illuminate\Http\Response
     */
    public function downloadHasilBelajar(Rapor $rapor)
    {
        // Verify rapor is approved or published
        if (!in_array($rapor->status, ['approved', 'published'])) {
            return response()->json([
                'message' => 'Rapor belum disetujui',
            ], 403);
        }

        // Load all necessary data
        $rapor->load([
            'siswa.user',
            'siswa.kelas.jurusan',
            'tahunAjaran',
            'approver'
        ]);

        // For now, return JSON response
        // TODO: Implement PDF generation using DomPDF or similar
        return response()->json([
            'message' => 'PDF generation will be implemented',
            'rapor' => $rapor,
        ]);

        // Future implementation:
        // $pdf = PDF::loadView('rapor.hasil-belajar', compact('rapor'));
        // return $pdf->download("rapor-{$rapor->siswa->nis}-{$rapor->tahunAjaran->tahun}.pdf");
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
     * Build payload detail rapor P5 (dari DB, dipakai untuk API dan PDF).
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return array{siswa: \App\Models\Siswa, p5_projects: array, tahun_ajaran_id: mixed}
     */
    protected function getDetailHasilP5Payload(Request $request, Siswa $siswa): array
    {
        $siswa->load(['user', 'kelas.jurusan']);
        $tahunAjaranId = $request->tahun_ajaran_id;

        $nilaiP5Query = $siswa->nilaiP5()->with(['p5.tahunAjaran', 'p5.koordinator.user']);
        if ($tahunAjaranId) {
            $nilaiP5Query->whereHas('p5', function ($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            });
        }
        $nilaiP5 = $nilaiP5Query->get();
        $p5Ids = $nilaiP5->pluck('p5_id')->unique()->values();

        $catatanByP5 = \Illuminate\Support\Facades\DB::table('p5_siswa')
            ->where('siswa_id', $siswa->id)
            ->whereIn('p5_id', $p5Ids)
            ->pluck('catatan_proses', 'p5_id')
            ->toArray();

        // Ambil P5 dari DB (fresh) supaya elemen_sub dari kolom JSON terbaca
        $p5List = P5::with(['koordinator', 'tahunAjaran', 'kelompok.guru', 'kelompok.siswa'])
            ->whereIn('id', $p5Ids)
            ->get()
            ->keyBy('id');

        $nilaiBySub = $nilaiP5->whereNotNull('sub_elemen')->keyBy(function ($n) {
            return $n->p5_id . '|' . $n->sub_elemen;
        });

        $deskripsiReferensi = $this->getP5SubElemenDeskripsi();

        $p5Projects = [];
        foreach ($p5Ids as $p5Id) {
            $p5 = $p5List->get($p5Id);
            if (!$p5) {
                continue;
            }
            $elemenSubRaw = $p5->elemen_sub;
            $elemenSub = is_array($elemenSubRaw) ? $elemenSubRaw : (is_string($elemenSubRaw) ? json_decode($elemenSubRaw, true) ?? [] : []);
            $subWithPredikat = [];
            foreach ($elemenSub as $es) {
                $es = is_array($es) ? $es : (array) $es;
                $sub = trim((string) ($es['sub_elemen'] ?? ''));
                if ($sub === '') {
                    continue;
                }
                $key = $p5->id . '|' . $sub;
                $nilaiRow = $nilaiBySub->get($key);
                // Prioritas: dari DB (elemen_sub.deskripsi_tujuan), lalu config, lalu nama sub_elemen (wajib ada teks)
                $deskripsiTujuan = isset($es['deskripsi_tujuan']) ? trim((string) $es['deskripsi_tujuan']) : '';
                if ($deskripsiTujuan === '') {
                    $deskripsiTujuan = $deskripsiReferensi[$sub] ?? $this->findDeskripsiByKey($deskripsiReferensi, $sub);
                }
                $deskripsiTujuan = ($deskripsiTujuan !== null && trim((string) $deskripsiTujuan) !== '')
                    ? trim((string) $deskripsiTujuan)
                    : $sub;
                $subWithPredikat[] = [
                    'elemen' => $es['elemen'] ?? '-',
                    'sub_elemen' => $sub,
                    'deskripsi_tujuan' => $deskripsiTujuan,
                    'predikat' => $nilaiRow ? $nilaiRow->nilai : null,
                    'predikat_label' => $nilaiRow ? $nilaiRow->nilai_description : '-',
                ];
            }
            $fasilitator = $p5->koordinator ? $p5->koordinator->nama_lengkap : null;
            foreach ($p5->kelompok ?? [] as $kel) {
                $siswaIds = $kel->siswa->pluck('id')->toArray();
                if (in_array($siswa->id, $siswaIds) && $kel->guru) {
                    $fasilitator = $kel->guru->nama_lengkap;
                    break;
                }
            }
            $p5Projects[] = [
                'id' => $p5->id,
                'tema' => $p5->tema,
                'judul' => $p5->judul,
                'deskripsi' => $p5->deskripsi,
                'dimensi' => $p5->dimensi ?? '-',
                'koordinator' => $p5->koordinator ? ['nama' => $p5->koordinator->nama_lengkap] : null,
                'fasilitator_nama' => $fasilitator,
                'tahun_ajaran' => $p5->tahunAjaran ? [
                    'tahun' => $p5->tahunAjaran->tahun,
                    'semester' => $p5->tahunAjaran->semester,
                    'label' => "{$p5->tahunAjaran->tahun} - Semester {$p5->tahunAjaran->semester}",
                ] : null,
                'elemen_sub' => $subWithPredikat,
                'catatan_proses' => $catatanByP5[$p5->id] ?? null,
            ];
        }

        return [
            'siswa' => $siswa,
            'p5_projects' => $p5Projects,
            'tahun_ajaran_id' => $tahunAjaranId,
        ];
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
     * Build data for rapor P5 PDF view.
     *
     * @param  array  $payload  From detailHasilP5 response
     * @param  Siswa  $siswa
     * @return array
     */
    protected function buildRaporP5PdfData(array $payload, Siswa $siswa): array
    {
        $bulanIndo = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        $now = now();
        $tanggalCetak = $now->format('d') . ' ' . $bulanIndo[(int) $now->format('n')] . ' ' . $now->format('Y');
        $waktuUnduh = $now->format('H.i') . ' ' . $now->format('d') . ' ' . $bulanIndo[(int) $now->format('n')] . ' ' . $now->format('Y');

        $tahunAjaranAktif = TahunAjaran::where('is_active', true)->first();
        $tahunPelajaran = $tahunAjaranAktif ? $tahunAjaranAktif->tahun : '';

        $tingkat = $siswa->kelas->tingkat ?? null;
        $fase = 'E';
        if ($tingkat !== null && $tingkat !== '') {
            $t = (string) $tingkat;
            if (in_array($t, ['11', '12', 'XI', 'xI', 'XII', 'xII'])) {
                $fase = 'F';
            } elseif (in_array($t, ['10', 'X', 'x'])) {
                $fase = 'E';
            }
        }

        return [
            'nama_sekolah' => config('app.school_name', 'SMKS Progresia Cianjur'),
            'tahun_pelajaran' => $tahunPelajaran,
            'fase' => $fase,
            'siswa' => $payload['siswa'] ?? $siswa,
            'tanggal_cetak' => $tanggalCetak,
            'waktu_unduh' => $waktuUnduh,
            'p5_projects' => $payload['p5_projects'] ?? [],
        ];
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
        $tahunAjaranId = $request->get('tahun_ajaran_id');
        
        if (!$tahunAjaranId) {
            // Get active tahun ajaran if not provided
            $tahunAjaran = TahunAjaran::where('is_active', true)->first();
            if (!$tahunAjaran) {
                return response()->json([
                    'message' => 'Tahun ajaran aktif tidak ditemukan',
                ], 404);
            }
            $tahunAjaranId = $tahunAjaran->id;
        }

        // Get all active students in the class
        $siswa = $kelas->siswa()->where('status', 'aktif')->orderBy('nama_lengkap')->get();

        // Get all mata pelajaran for this class
        $mataPelajaran = MataPelajaran::where('kelas_id', $kelas->id)
            ->where('is_active', true)
            ->orderBy('kelompok')
            ->orderBy('nama_mapel')
            ->get();

        // Get all nilai for students in this class and tahun ajaran
        $nilai = Nilai::whereIn('siswa_id', $siswa->pluck('id'))
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->with(['siswa.user', 'mataPelajaran'])
            ->get();

        // Build legger data
        $legger = [];
        foreach ($siswa as $s) {
            // Get nilai for this student, grouped by mata_pelajaran_id
            $siswaNilai = $nilai->where('siswa_id', $s->id)
                ->groupBy('mata_pelajaran_id')
                ->map(function ($nilaiGroup) {
                    return $nilaiGroup->first();
                });
            
            $nilaiMap = [];
            foreach ($siswaNilai as $mapelId => $nilaiItem) {
                $nilaiMap[$mapelId] = $nilaiItem;
            }
            
            $legger[] = [
                'siswa' => $s->load('user'),
                'nilai' => $nilaiMap,
            ];
        }

        return response()->json([
            'kelas' => $kelas->load('jurusan'),
            'tahun_ajaran' => TahunAjaran::find($tahunAjaranId),
            'mata_pelajaran' => $mataPelajaran,
            'legger' => $legger,
        ]);
    }

    /**
     * Download legger as PDF.
     *
     * @param  Request  $request
     * @param  Kelas  $kelas
     * @return \Illuminate\Http\Response
     */
    public function downloadLegger(Request $request, Kelas $kelas)
    {
        // Load all necessary data
        $data = $this->legger($request, $kelas)->getData(true);

        // For now, return JSON response
        // TODO: Implement PDF generation using DomPDF or similar
        return response()->json([
            'message' => 'PDF generation will be implemented',
            'data' => $data,
        ]);

        // Future implementation:
        // $pdf = PDF::loadView('rapor.legger', $data);
        // return $pdf->download("legger-{$kelas->nama_kelas}.pdf");
    }

    /**
     * Load referensi deskripsi sub-elemen P5 (dari file config, tidak pakai cache).
     *
     * @return array<string, string>
     */
    protected function getP5SubElemenDeskripsi(): array
    {
        $path = base_path('config/p5_sub_elemen_deskripsi.php');
        if (file_exists($path)) {
            $ref = require $path;
            if (is_array($ref) && !empty($ref)) {
                return $ref;
            }
        }
        $ref = config('p5_sub_elemen_deskripsi');
        return is_array($ref) ? $ref : [];
    }

    /**
     * Cari deskripsi dengan key yang mirip (normalisasi spasi, case-insensitive).
     *
     * @param  array<string, string>  $referensi
     * @param  string  $sub
     * @return string|null
     */
    protected function findDeskripsiByKey(array $referensi, string $sub): ?string
    {
        $normalize = function (string $s): string {
            $s = trim(preg_replace('/\s+/', ' ', $s));
            return mb_strtolower($s);
        };
        $keyNorm = $normalize($sub);
        foreach ($referensi as $k => $v) {
            if ($normalize($k) === $keyNorm) {
                return $v;
            }
        }
        return null;
    }
}

