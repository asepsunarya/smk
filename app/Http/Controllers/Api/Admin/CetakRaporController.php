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
        $query = Rapor::with([
            'siswa.user',
            'siswa.kelas.jurusan',
            'tahunAjaran',
            'approver'
        ]);

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

        if ($request->has('tahun_ajaran_id')) {
            $query->where('tahun_ajaran_id', $request->tahun_ajaran_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Only show approved or published rapor for printing
        if (!$request->has('status')) {
            $query->whereIn('status', ['approved', 'published']);
        }

        $rapor = $query->orderBy('created_at', 'desc')->paginate($request->get('per_page', 15));

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

        if ($request->has('tahun_ajaran_id')) {
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
     * Get detail rapor hasil P5 for a student.
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function detailHasilP5(Request $request, Siswa $siswa)
    {
        $siswa->load([
            'user',
            'kelas.jurusan'
        ]);

        $tahunAjaranId = $request->tahun_ajaran_id;

        // Get all P5 projects for this student
        $nilaiP5Query = $siswa->nilaiP5()->with([
            'p5.tahunAjaran',
            'p5.koordinator.user',
            'dimensi'
        ]);

        if ($tahunAjaranId) {
            $nilaiP5Query->whereHas('p5', function ($q) use ($tahunAjaranId) {
                $q->where('tahun_ajaran_id', $tahunAjaranId);
            });
        }

        $nilaiP5 = $nilaiP5Query->get();

        // Group by P5 project
        $p5Projects = $nilaiP5->groupBy('p5_id')->map(function ($nilai) {
            $p5 = $nilai->first()->p5;
            return [
                'id' => $p5->id,
                'tema' => $p5->tema,
                'deskripsi' => $p5->deskripsi,
                'koordinator' => $p5->koordinator ? [
                    'nama' => $p5->koordinator->nama_lengkap,
                    'user' => $p5->koordinator->user ? [
                        'name' => $p5->koordinator->user->name,
                        'email' => $p5->koordinator->user->email,
                    ] : null,
                ] : null,
                'tahun_ajaran' => $p5->tahunAjaran ? [
                    'tahun' => $p5->tahunAjaran->tahun,
                    'semester' => $p5->tahunAjaran->semester,
                    'label' => "{$p5->tahunAjaran->tahun} - Semester {$p5->tahunAjaran->semester}",
                ] : null,
                'dimensi' => $nilai->map(function ($n) {
                    return [
                        'id' => $n->dimensi->id,
                        'nama_dimensi' => $n->dimensi->nama_dimensi,
                        'nilai' => $n->nilai,
                        'nilai_description' => $n->nilai_description,
                        'catatan' => $n->catatan,
                    ];
                })->values(),
            ];
        })->values();

        return response()->json([
            'siswa' => $siswa,
            'p5_projects' => $p5Projects,
            'tahun_ajaran_id' => $tahunAjaranId,
        ]);
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
        // Load all necessary data
        $data = $this->detailHasilP5($request, $siswa)->getData(true);

        // For now, return JSON response
        // TODO: Implement PDF generation using DomPDF or similar
        return response()->json([
            'message' => 'PDF generation will be implemented',
            'data' => $data,
        ]);

        // Future implementation:
        // $pdf = PDF::loadView('rapor.hasil-p5', $data);
        // return $pdf->download("rapor-p5-{$siswa->nis}.pdf");
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
}

