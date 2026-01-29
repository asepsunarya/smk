<?php

namespace App\Http\Controllers\Api\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\TahunAjaran;
use App\Models\Nilai;
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
     * List siswa for cetak rapor (filter: kelas_id, semester).
     * Each row: no, nama, nisn, nis, can_cetak_rapor, rapor_id (optional).
     */
    public function index(Request $request)
    {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
            'semester' => 'required|in:1,2',
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

        $siswaList = Siswa::where('kelas_id', $request->kelas_id)
            ->where('status', 'aktif')
            ->orderBy('nama_lengkap')
            ->get();

        $result = [];
        $no = 1;
        foreach ($siswaList as $siswa) {
            $canCetakRapor = $this->canCetakRapor($siswa->id, $tahunAjaran->id, $request->semester);
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
     * Check if siswa can cetak rapor: all nilai (tahun_ajaran + semester) >= KKM.
     * If no nilai, cannot print.
     */
    protected function canCetakRapor(int $siswaId, int $tahunAjaranId, string $semester): bool
    {
        $nilaiList = Nilai::where('siswa_id', $siswaId)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->with('mataPelajaran')
            ->get();

        if ($nilaiList->isEmpty()) {
            return false;
        }

        foreach ($nilaiList as $nilai) {
            $kkm = $nilai->mataPelajaran->kkm ?? 75;
            $nilaiRapor = $nilai->nilai_rapor ?? 0;
            if ($nilaiRapor < $kkm) {
                return false;
            }
        }

        return true;
    }

    /**
     * Download rapor as PDF (format RAPOR Kurmer).
     * Rejects if any nilai < KKM.
     */
    public function download(Request $request, Siswa $siswa)
    {
        $request->validate([
            'semester' => 'required|in:1,2',
        ]);

        $user = Auth::user();
        $kelasIds = $user->kelasAsWali()->pluck('id')->toArray();

        if (!in_array((int) $siswa->kelas_id, $kelasIds)) {
            return response()->json(['message' => 'Anda bukan wali kelas untuk siswa ini'], 403);
        }

        $tahunAjaran = TahunAjaran::where('is_active', true)->first();
        if (!$tahunAjaran) {
            return response()->json(['message' => 'Tahun ajaran aktif tidak ditemukan'], 404);
        }

        if (!$this->canCetakRapor($siswa->id, $tahunAjaran->id, $request->semester)) {
            return response()->json([
                'message' => 'Cetak rapor tidak dapat dilakukan. Terdapat nilai di bawah KKM.',
            ], 403);
        }

        $data = $this->buildRaporData($siswa, $tahunAjaran->id, $request->semester);
        $pdf = Pdf::loadView('rapor.kurmer', $data);
        $pdf->setPaper('a4', 'portrait');

        $filename = "rapor-{$siswa->nis}-{$tahunAjaran->tahun}-s{$request->semester}.pdf";
        return $pdf->download($filename);
    }

    /**
     * Build rapor data for PDF view.
     */
    protected function buildRaporData(Siswa $siswa, int $tahunAjaranId, string $semester): array
    {
        $siswa->load(['kelas.jurusan', 'user']);

        $nilaiList = Nilai::where('siswa_id', $siswa->id)
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->where('semester', $semester)
            ->with('mataPelajaran')
            ->get();

        $nilaiByKelompok = $nilaiList->groupBy(function ($n) {
            $k = $n->mataPelajaran->kelompok ?? null;
            return in_array($k, ['umum', 'kejuruan', 'muatan_lokal']) ? $k : 'umum';
        });

        $kehadiran = $siswa->kehadiran()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->first();

        $catatan = $siswa->catatanAkademik()
            ->where('tahun_ajaran_id', $tahunAjaranId)
            ->first();

        $tahunAjaran = TahunAjaran::find($tahunAjaranId);

        return [
            'siswa' => $siswa,
            'nilai_by_kelompok' => $nilaiByKelompok,
            'kehadiran' => $kehadiran,
            'catatan_akademik' => $catatan,
            'tahun_ajaran' => $tahunAjaran,
            'semester' => $semester,
        ];
    }

    /**
     * Cetak transkrip - format menyusul. Placeholder.
     */
    public function transkrip(Request $request, Siswa $siswa)
    {
        $user = Auth::user();
        $kelasIds = $user->kelasAsWali()->pluck('id')->toArray();

        if (!in_array((int) $siswa->kelas_id, $kelasIds)) {
            return response()->json(['message' => 'Anda bukan wali kelas untuk siswa ini'], 403);
        }

        return response()->json([
            'message' => 'Format cetak transkrip menyusul.',
        ], 501);
    }
}
