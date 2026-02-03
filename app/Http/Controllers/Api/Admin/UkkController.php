<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ukk;
use App\Models\UkkEvent;
use App\Models\Jurusan;
use App\Models\Kelas;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * UkkController
 *
 * Handles Nilai UKK (scores) – event data is in UkkEventController.
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
     * Store Nilai UKK (link siswa to event and save scores).
     */
    public function store(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'required|exists:jurusan,id',
            'kelas_id' => 'required|exists:kelas,id',
            'siswa_id' => 'required|exists:siswa,id',
            'nilai_teori' => 'nullable|integer|min:0|max:100',
            'nilai_praktek' => 'nullable|integer|min:0|max:100',
        ]);

        $event = UkkEvent::where('jurusan_id', $request->jurusan_id)
            ->where('kelas_id', $request->kelas_id)
            ->with('tahunAjaran')
            ->orderBy('tanggal_ujian', 'desc')
            ->orderBy('id', 'desc')
            ->first();

        if (!$event) {
            return response()->json([
                'message' => 'Data UKK tidak ditemukan untuk jurusan dan kelas ini. Buat Data UKK terlebih dahulu.',
            ], 422);
        }

        $siswa = \App\Models\Siswa::findOrFail($request->siswa_id);
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
     * Update Nilai UKK (scores only).
     */
    public function update(Request $request, Ukk $ukk)
    {
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

    /**
     * List kelas (with tahun ajaran) that have UKK data for a jurusan.
     * Used for download nilai UKK page: filter jurusan -> list per kelas.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function kelasList(Request $request)
    {
        $jurusanId = $request->get('jurusan_id');
        if (! $jurusanId) {
            return response()->json(['data' => []]);
        }

        $pairs = Ukk::query()
            ->where('jurusan_id', $jurusanId)
            ->whereHas('kelas', function ($q) {
                $q->where('tingkat', 12)->orWhere('tingkat', '12');
            })
            ->select('kelas_id', 'tahun_ajaran_id')
            ->distinct()
            ->get();

        $rows = $pairs->map(function ($p) {
            $kelas = Kelas::with('jurusan')->find($p->kelas_id);
            if (! $kelas || ((string) $kelas->tingkat !== '12')) {
                return null;
            }
            $tahunAjaran = \App\Models\TahunAjaran::find($p->tahun_ajaran_id);
            $tahun = $tahunAjaran ? (int) $tahunAjaran->tahun : 0;
            $semester = $tahunAjaran ? (string) $tahunAjaran->semester : '';
            return [
                'kelas_id' => $p->kelas_id,
                'tahun_ajaran_id' => $p->tahun_ajaran_id,
                'kelas' => [
                    'id' => $kelas->id,
                    'nama_kelas' => $kelas->nama_kelas,
                    'jurusan' => $kelas->jurusan ? [
                        'id' => $kelas->jurusan->id,
                        'nama_jurusan' => $kelas->jurusan->nama_jurusan,
                    ] : null,
                ],
                'tahun_ajaran' => $tahunAjaran ? [
                    'id' => $tahunAjaran->id,
                    'tahun' => $tahunAjaran->tahun,
                    'semester' => $tahunAjaran->semester,
                    'label' => $tahun . '/' . ($tahun + 1) . ' - Semester ' . $semester,
                ] : null,
            ];
        })->filter()->values();

        return response()->json(['data' => $rows]);
    }

    /**
     * Export daftar nilai UKK untuk satu kelas + tahun ajaran ke .xlsx.
     * Format: DAFTAR NILAI UJI KOMPETENSI KEAHLIAN (UKK).
     *
     * @param  Request  $request
     * @return StreamedResponse
     */
    public function exportExcel(Request $request)
    {
        $request->validate([
            'jurusan_id' => 'required|exists:jurusan,id',
            'kelas_id' => 'required|exists:kelas,id',
            'tahun_ajaran_id' => 'required|exists:tahun_ajaran,id',
        ]);

        // Ambil semua nilai UKK lewat event yang sama (jurusan + kelas + tahun ajaran)
        // agar semua siswa yang diinput guru/wali kelas ikut tercetak, tidak hanya satu
        $eventIds = UkkEvent::where('jurusan_id', $request->jurusan_id)
            ->where(function ($q) use ($request) {
                $q->whereNull('kelas_id')->orWhere('kelas_id', $request->kelas_id);
            })
            ->where('tahun_ajaran_id', $request->tahun_ajaran_id)
            ->pluck('id');

        $ukkList = Ukk::with([
            'siswa.user',
            'kelas.jurusan',
            'tahunAjaran',
            'pengujiInternal.user',
        ])
            ->whereIn('ukk_event_id', $eventIds)
            ->orderBy('siswa_id')
            ->get();

        if ($ukkList->isEmpty()) {
            return response()->json(['message' => 'Tidak ada data nilai UKK untuk kelas dan tahun ajaran ini.'], 404);
        }

        // Pakai kelas dari request (yang dipilih user) agar nama kelas di Excel selalu tampil
        // meskipun ada beberapa event (mis. event tanpa kelas_id + event per kelas)
        $kelas = Kelas::with('jurusan')->find($request->kelas_id);
        $tahunAjaran = $ukkList->first()->tahunAjaran;
        $namaSekolah = config('app.school_name', 'SMK');
        $namaKelas = $kelas ? $kelas->nama_kelas : '';
        $namaJurusan = $kelas && $kelas->jurusan ? $kelas->jurusan->nama_jurusan : '';
        $tahunInt = $tahunAjaran ? (int) $tahunAjaran->tahun : 0;
        $tahunLabel = $tahunAjaran ? $tahunInt . '/' . ($tahunInt + 1) : '';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nilai UKK');

        $sheet->setCellValue('C1', strtoupper($namaSekolah));
        $sheet->mergeCells('C1:J1');
        $sheet->getStyle('C1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('C1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('D2', 'DAFTAR NILAI UJI KOMPETENSI KEAHLIAN (UKK)');
        $sheet->mergeCells('D2:J2');
        $sheet->getStyle('D2')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('D2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A3', 'Kelas');
        $sheet->setCellValue('B3', ': ' . $namaKelas . ($namaJurusan ? ' (' . $namaJurusan . ')' : ''));
        $sheet->getStyle('A3')->getFont()->setBold(true);

        $sheet->setCellValue('G3', 'Tahun Pelajaran');
        $sheet->setCellValue('H3', ': ' . $tahunLabel);
        $sheet->getStyle('G3')->getFont()->setBold(true);

        $headerRow = 5;
        $headers = ['No', 'NIS', 'Nama Siswa', 'Instansi', 'Penguji Internal', 'Penguji Eksternal', 'Nilai Teori', 'Nilai Praktek', 'Nilai Akhir', 'Predikat'];
        foreach ($headers as $col => $label) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col + 1);
            $sheet->setCellValue($colLetter . $headerRow, $label);
            $sheet->getStyle($colLetter . $headerRow)->getFont()->setBold(true);
            $sheet->getStyle($colLetter . $headerRow)->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('E2E8F0');
            $sheet->getStyle($colLetter . $headerRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $row = $headerRow + 1;
        foreach ($ukkList as $index => $ukk) {
            $siswa = $ukk->siswa;
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $siswa ? ($siswa->nis ?? '-') : '-');
            $sheet->setCellValue('C' . $row, $siswa ? ($siswa->nama_lengkap ?? '-') : '-');
            $sheet->setCellValue('D' . $row, $ukk->nama_du_di ?? '-');
            $sheet->setCellValue('E' . $row, $ukk->pengujiInternal ? $ukk->pengujiInternal->nama_lengkap : '-');
            $sheet->setCellValue('F' . $row, $ukk->penguji_eksternal ?? '-');
            $sheet->setCellValue('G' . $row, $ukk->nilai_teori !== null ? $ukk->nilai_teori : '-');
            $sheet->setCellValue('H' . $row, $ukk->nilai_praktek !== null ? $ukk->nilai_praktek : '-');
            $sheet->setCellValue('I' . $row, $ukk->nilai_akhir !== null ? $ukk->nilai_akhir : '-');
            $sheet->setCellValue('J' . $row, $ukk->predikat ?? '-');
            $row++;
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Daftar_Nilai_UKK_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $namaKelas) . '_' . $tahunLabel . '.xlsx';

        return new StreamedResponse(function () use ($spreadsheet) {
            $writer = new XlsxWriter($spreadsheet);
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
            'Cache-Control' => 'max-age=0',
        ]);
    }
}

