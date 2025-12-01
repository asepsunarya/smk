<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\Guru;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\TahunAjaran;
use App\Models\Nilai;
use App\Models\Rapor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * DashboardController
 * 
 * Provides dashboard statistics for each user role
 */
class DashboardController extends Controller
{
    /**
     * Get admin dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function admin()
    {
        $stats = [
            'total_siswa' => Siswa::where('status', 'aktif')->count(),
            'total_guru' => Guru::where('status', 'aktif')->count(),
            'total_kelas' => Kelas::count(),
            'total_mapel' => MataPelajaran::where('is_active', true)->count(),
            'tahun_ajaran_aktif' => TahunAjaran::where('is_active', true)->first(),
            'recent_students' => Siswa::with('kelas')
                ->where('status', 'aktif')
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get(),
            'class_capacity' => Kelas::with('jurusan')
                ->withCount('siswa')
                ->get()
                ->map(function ($kelas) {
                    return [
                        'kelas' => $kelas->nama_kelas,
                        'jurusan' => $kelas->jurusan->nama_jurusan,
                        'siswa_count' => $kelas->siswa_count,
                        'kapasitas' => $kelas->kapasitas,
                        'percentage' => round(($kelas->siswa_count / $kelas->kapasitas) * 100, 2),
                    ];
                }),
        ];

        return response()->json($stats);
    }

    /**
     * Get guru dashboard statistics.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function guru(Request $request)
    {
        $guru = $request->user()->guru;
        $tahunAjaran = TahunAjaran::where('is_active', true)->first();

        $stats = [
            'total_kelas' => 0,
            'total_mapel' => 0,
            'total_siswa' => 0,
            'nilai_belum_input' => 0,
            'jadwal_hari_ini' => [],
            'recent_nilai' => Nilai::with(['siswa', 'mataPelajaran'])
                ->where('guru_id', $guru->id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->orderBy('updated_at', 'desc')
                ->take(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get wali kelas dashboard statistics.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function waliKelas(Request $request)
    {
        $user = $request->user();
        $kelas = $user->kelasAsWali()->first();
        $tahunAjaran = TahunAjaran::where('is_active', true)->first();

        if (!$kelas) {
            return response()->json(['message' => 'Anda belum ditugaskan sebagai wali kelas'], 404);
        }

        $stats = [
            'kelas_info' => [
                'nama_kelas' => $kelas->nama_kelas,
                'jurusan' => $kelas->jurusan->nama_jurusan,
                'total_siswa' => $kelas->siswa()->where('status', 'aktif')->count(),
                'kapasitas' => $kelas->kapasitas,
            ],
            'kehadiran_summary' => DB::table('kehadiran')
                ->join('siswa', 'kehadiran.siswa_id', '=', 'siswa.id')
                ->where('siswa.kelas_id', $kelas->id)
                ->where('kehadiran.tahun_ajaran_id', $tahunAjaran->id)
                ->select(
                    DB::raw('SUM(sakit) as total_sakit'),
                    DB::raw('SUM(izin) as total_izin'),
                    DB::raw('SUM(tanpa_keterangan) as total_alpha')
                )
                ->first(),
            'nilai_summary' => DB::table('nilai')
                ->join('siswa', 'nilai.siswa_id', '=', 'siswa.id')
                ->where('siswa.kelas_id', $kelas->id)
                ->where('nilai.tahun_ajaran_id', $tahunAjaran->id)
                ->whereNotNull('nilai.nilai_rapor')
                ->select(
                    'nilai.mata_pelajaran_id',
                    DB::raw('COUNT(CASE WHEN nilai_rapor >= mata_pelajaran.kkm THEN 1 END) as tuntas'),
                    DB::raw('COUNT(CASE WHEN nilai_rapor < mata_pelajaran.kkm THEN 1 END) as tidak_tuntas')
                )
                ->join('mata_pelajaran', 'nilai.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->groupBy('nilai.mata_pelajaran_id')
                ->get(),
            'rapor_status' => [
                'draft' => Rapor::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('status', 'draft')
                    ->count(),
                'approved' => Rapor::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('status', 'approved')
                    ->count(),
                'published' => Rapor::whereHas('siswa', fn($q) => $q->where('kelas_id', $kelas->id))
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('status', 'published')
                    ->count(),
            ],
            'siswa_list' => Siswa::with(['kehadiran' => fn($q) => $q->where('tahun_ajaran_id', $tahunAjaran->id)])
                ->where('kelas_id', $kelas->id)
                ->where('status', 'aktif')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get kepala sekolah dashboard statistics.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function kepalaSekolah()
    {
        $tahunAjaran = TahunAjaran::where('is_active', true)->first();

        $stats = [
            'overview' => [
                'total_siswa' => Siswa::where('status', 'aktif')->count(),
                'total_guru' => Guru::where('status', 'aktif')->count(),
                'total_kelas' => Kelas::count(),
                'total_jurusan' => DB::table('jurusan')->count(),
            ],
            'rapor_summary' => [
                'total' => Rapor::where('tahun_ajaran_id', $tahunAjaran->id)->count(),
                'draft' => Rapor::where('tahun_ajaran_id', $tahunAjaran->id)->where('status', 'draft')->count(),
                'waiting_approval' => Rapor::where('tahun_ajaran_id', $tahunAjaran->id)->where('status', 'draft')->count(),
                'approved' => Rapor::where('tahun_ajaran_id', $tahunAjaran->id)->where('status', 'approved')->count(),
                'published' => Rapor::where('tahun_ajaran_id', $tahunAjaran->id)->where('status', 'published')->count(),
            ],
            'academic_performance' => DB::table('nilai')
                ->join('mata_pelajaran', 'nilai.mata_pelajaran_id', '=', 'mata_pelajaran.id')
                ->where('nilai.tahun_ajaran_id', $tahunAjaran->id)
                ->whereNotNull('nilai.nilai_rapor')
                ->select(
                    'mata_pelajaran.nama_mapel',
                    DB::raw('AVG(nilai_rapor) as rata_rata'),
                    DB::raw('COUNT(CASE WHEN nilai_rapor >= mata_pelajaran.kkm THEN 1 END) as tuntas'),
                    DB::raw('COUNT(CASE WHEN nilai_rapor < mata_pelajaran.kkm THEN 1 END) as tidak_tuntas')
                )
                ->groupBy('mata_pelajaran.id', 'mata_pelajaran.nama_mapel')
                ->get(),
            'class_performance' => Kelas::with(['jurusan'])
                ->withCount(['siswa as siswa_aktif' => fn($q) => $q->where('status', 'aktif')])
                ->get()
                ->map(function ($kelas) use ($tahunAjaran) {
                    $avgNilai = DB::table('nilai')
                        ->join('siswa', 'nilai.siswa_id', '=', 'siswa.id')
                        ->where('siswa.kelas_id', $kelas->id)
                        ->where('nilai.tahun_ajaran_id', $tahunAjaran->id)
                        ->avg('nilai_rapor');

                    return [
                        'kelas' => $kelas->nama_kelas,
                        'jurusan' => $kelas->jurusan->nama_jurusan,
                        'total_siswa' => $kelas->siswa_aktif,
                        'rata_rata_nilai' => round($avgNilai, 2),
                    ];
                }),
            'recent_rapor_approvals' => Rapor::with(['siswa', 'kelas'])
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->where('status', 'draft')
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get siswa dashboard statistics.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function siswa(Request $request)
    {
        $siswa = $request->user()->siswa;
        $tahunAjaran = TahunAjaran::where('is_active', true)->first();

        $stats = [
            'profile' => [
                'nama' => $siswa->nama_lengkap,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'kelas' => $siswa->kelas->nama_kelas,
                'jurusan' => $siswa->kelas->jurusan->nama_jurusan,
            ],
            'nilai' => Nilai::with('mataPelajaran')
                ->where('siswa_id', $siswa->id)
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->get()
                ->map(function ($nilai) {
                    return [
                        'mata_pelajaran' => $nilai->mataPelajaran->nama_mapel,
                        'nilai_akhir' => $nilai->nilai_akhir,
                        'nilai_uts' => $nilai->nilai_uts,
                        'nilai_uas' => $nilai->nilai_uas,
                        'nilai_rapor' => $nilai->nilai_rapor,
                        'predikat' => $nilai->predikat,
                        'status' => $nilai->status,
                    ];
                }),
            'kehadiran' => $siswa->kehadiran()
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->first(),
            'ekstrakurikuler' => $siswa->nilaiEkstrakurikuler()
                ->with('ekstrakurikuler')
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->get(),
            'rapor' => $siswa->rapor()
                ->where('tahun_ajaran_id', $tahunAjaran->id)
                ->first(),
            'jadwal' => [],
        ];

        return response()->json($stats);
    }

    /**
     * Get current day name in Indonesian.
     *
     * @return string
     */
    private function getDayName()
    {
        $days = [
            'Sunday' => 'Minggu',
            'Monday' => 'Senin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis',
            'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];

        return $days[date('l')] ?? 'Senin';
    }
}