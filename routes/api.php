<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\Admin\SiswaController;
use App\Http\Controllers\Api\Admin\GuruController;
use App\Http\Controllers\Api\Admin\UserController;
use App\Http\Controllers\Api\Admin\KelasController;
use App\Http\Controllers\Api\Admin\JurusanController;
use App\Http\Controllers\Api\Admin\MataPelajaranController;
use App\Http\Controllers\Api\Admin\TahunAjaranController;
use App\Http\Controllers\Api\Admin\WaliKelasController;
use App\Http\Controllers\Api\Admin\EkstrakurikulerController;
use App\Http\Controllers\Api\Admin\PklController;
use App\Http\Controllers\Api\Guru\NilaiController as GuruNilaiController;
use App\Http\Controllers\Api\Guru\CapaianPembelajaranController;
use App\Http\Controllers\Api\Guru\TujuanPembelajaranController;
use App\Http\Controllers\Api\Guru\P5Controller as GuruP5Controller;
use App\Http\Controllers\Api\Guru\PklController as GuruPklController;
use App\Http\Controllers\Api\Guru\NilaiEkstrakurikulerController;
use App\Http\Controllers\Api\WaliKelas\NilaiKelasController;
use App\Http\Controllers\Api\WaliKelas\KehadiranController;
use App\Http\Controllers\Api\WaliKelas\CatatanAkademikController;
use App\Http\Controllers\Api\WaliKelas\RaporController as WaliRaporController;
use App\Http\Controllers\Api\WaliKelas\CetakRaporBelajarController;
use App\Http\Controllers\Api\WaliKelas\CetakProfilSiswaController;
use App\Http\Controllers\Api\WaliKelas\NilaiPklController;
use App\Http\Controllers\Api\KepalaSekolah\RaporApprovalController;
use App\Http\Controllers\Api\KepalaSekolah\RekapController;
use App\Http\Controllers\Api\Siswa\RaporSiswaController;
use App\Http\Controllers\Api\Siswa\NilaiSiswaController;
use App\Http\Controllers\Api\LookupController;
use App\Http\Controllers\Api\Admin\UkkController;
use App\Http\Controllers\Api\Admin\P5Controller as AdminP5Controller;
use App\Http\Controllers\Api\Admin\CetakRaporController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/update-password', [AuthController::class, 'updatePassword']);

    // Dashboard routes (role-based)
    Route::get('/dashboard/admin', [DashboardController::class, 'admin'])->middleware('role:admin');
    Route::get('/dashboard/guru', [DashboardController::class, 'guru'])->middleware('role:guru');
    Route::get('/dashboard/wali-kelas', [DashboardController::class, 'waliKelas'])->middleware('role:wali_kelas');
    Route::get('/dashboard/kepala-sekolah', [DashboardController::class, 'kepalaSekolah'])->middleware('role:kepala_sekolah');
    Route::get('/dashboard/siswa', [DashboardController::class, 'siswa'])->middleware('role:siswa');

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        // Master Data Management
        Route::get('siswa/available-siswa', [SiswaController::class, 'availableSiswa']);
        Route::apiResource('siswa', SiswaController::class);
        Route::post('siswa/{siswa}/move-class', [SiswaController::class, 'moveClass']);
        Route::post('siswa/{siswa}/reset-password', [SiswaController::class, 'resetPassword']);

        Route::get('guru/available-users', [GuruController::class, 'availableUsers']);
        Route::get('guru/available-guru', [GuruController::class, 'availableGuru']);
        Route::apiResource('guru', GuruController::class);
        Route::post('guru/{guru}/reset-password', [GuruController::class, 'resetPassword']);
        Route::post('guru/{guru}/toggle-status', [GuruController::class, 'toggleStatus']);

        Route::apiResource('user', UserController::class);
        Route::post('user/{user}/reset-password', [UserController::class, 'resetPassword']);
        Route::post('user/{user}/toggle-status', [UserController::class, 'toggleStatus']);

        Route::apiResource('kelas', KelasController::class);
        Route::post('kelas/{kelas}/assign-wali', [KelasController::class, 'assignWali']);
        Route::get('kelas/{kelas}/siswa', [KelasController::class, 'getSiswa']);

        Route::apiResource('jurusan', JurusanController::class);
        Route::apiResource('mata-pelajaran', MataPelajaranController::class);
        Route::post('mata-pelajaran/{mataPelajaran}/toggle-status', [MataPelajaranController::class, 'toggleStatus']);
        Route::apiResource('tahun-ajaran', TahunAjaranController::class);
        Route::post('tahun-ajaran/{tahunAjaran}/activate', [TahunAjaranController::class, 'activate']);

        Route::apiResource('wali-kelas', WaliKelasController::class);
        Route::get('wali-kelas/guru/{guru}/kelas', [WaliKelasController::class, 'getKelas']);
        Route::post('wali-kelas/assign', [WaliKelasController::class, 'assign']);
        Route::post('wali-kelas/remove', [WaliKelasController::class, 'remove']);
        Route::get('wali-kelas/find-id', [WaliKelasController::class, 'findId']);

        Route::apiResource('ekstrakurikuler', EkstrakurikulerController::class);
        Route::post('ekstrakurikuler/{ekstrakurikuler}/toggle-status', [EkstrakurikulerController::class, 'toggleStatus']);
        Route::post('ekstrakurikuler/{ekstrakurikuler}/assign-pembina', [EkstrakurikulerController::class, 'assignPembina']);

        Route::apiResource('pkl', PklController::class);
        Route::apiResource('ukk', UkkController::class);
        Route::get('ukk/jurusan/{jurusan}', [UkkController::class, 'byJurusan']);
        Route::apiResource('p5', AdminP5Controller::class);

        // Cetak Rapor
        Route::get('cetak-rapor/hasil-belajar', [CetakRaporController::class, 'hasilBelajar']);
        Route::get('cetak-rapor/hasil-belajar/{rapor}', [CetakRaporController::class, 'detailHasilBelajar']);
        Route::get('cetak-rapor/hasil-belajar/{rapor}/preview', [CetakRaporController::class, 'previewHasilBelajar']);
        Route::get('cetak-rapor/hasil-belajar/{rapor}/download', [CetakRaporController::class, 'downloadHasilBelajar']);
        
        Route::get('cetak-rapor/p5', [CetakRaporController::class, 'hasilP5']);
        Route::get('cetak-rapor/p5/{siswa}', [CetakRaporController::class, 'detailHasilP5']);
        Route::get('cetak-rapor/p5/{siswa}/preview', [CetakRaporController::class, 'previewHasilP5']);
        Route::get('cetak-rapor/p5/{siswa}/download', [CetakRaporController::class, 'downloadHasilP5']);
        
        Route::get('cetak-rapor/legger/{kelas}', [CetakRaporController::class, 'legger']);
        Route::get('cetak-rapor/legger/{kelas}/download', [CetakRaporController::class, 'downloadLegger']);
    });

    // Guru routes
    Route::middleware('role:guru')->prefix('guru')->group(function () {
        // Nilai Management
        Route::get('nilai/kelas/{kelas}/mapel/{mataPelajaran}', [GuruNilaiController::class, 'index']);
        Route::get('nilai/kelas/{kelas}/siswa', [GuruNilaiController::class, 'getSiswa']);
        Route::post('nilai/store', [GuruNilaiController::class, 'store']);
        Route::post('nilai/get-or-create-special-cp', [GuruNilaiController::class, 'getOrCreateSpecialCP']);
        Route::post('nilai/batch-update', [GuruNilaiController::class, 'batchUpdate']);
        Route::put('nilai/{nilai}', [GuruNilaiController::class, 'update']);
        Route::delete('nilai/{nilai}', [GuruNilaiController::class, 'destroy']);

        // Capaian & Tujuan Pembelajaran
        Route::get('capaian-pembelajaran/mapel/{mataPelajaran}', [CapaianPembelajaranController::class, 'byMapel']);
        Route::apiResource('capaian-pembelajaran', CapaianPembelajaranController::class);
        Route::apiResource('capaian-pembelajaran.tujuan-pembelajaran', TujuanPembelajaranController::class)->shallow();

        // P5 Management
        Route::apiResource('p5', GuruP5Controller::class);
        Route::post('p5/{p5}/nilai', [GuruP5Controller::class, 'inputNilai']);
        Route::get('p5/{p5}/nilai', [GuruP5Controller::class, 'getNilai']);

        // PKL Management
        Route::get('pkl/pembimbing', [GuruPklController::class, 'myStudents']);
        Route::put('pkl/{pkl}/nilai-sekolah', [GuruPklController::class, 'updateNilaiSekolah']);

        // Nilai Ekstrakurikuler
        Route::get('nilai-ekstrakurikuler/siswa', [NilaiEkstrakurikulerController::class, 'getSiswa']);
        Route::get('nilai-ekstrakurikuler/my-ekstrakurikuler', [NilaiEkstrakurikulerController::class, 'myEkstrakurikuler']);
        Route::post('nilai-ekstrakurikuler/batch-store', [NilaiEkstrakurikulerController::class, 'batchStore']);
    });

    // Wali Kelas routes
    Route::middleware('role:wali_kelas')->prefix('wali-kelas')->group(function () {
        // Capaian & Tujuan Pembelajaran
        Route::get('capaian-pembelajaran/mapel/{mataPelajaran}', [CapaianPembelajaranController::class, 'byMapel']);
        Route::apiResource('capaian-pembelajaran', CapaianPembelajaranController::class);
        
        // Nilai Management (same as guru)
        Route::get('nilai/kelas/{kelas}/mapel/{mataPelajaran}', [GuruNilaiController::class, 'index']);
        Route::get('nilai/kelas/{kelas}/siswa', [GuruNilaiController::class, 'getSiswa']);
        Route::post('nilai/store', [GuruNilaiController::class, 'store']);
        Route::post('nilai/get-or-create-special-cp', [GuruNilaiController::class, 'getOrCreateSpecialCP']);
        Route::post('nilai/batch-update', [GuruNilaiController::class, 'batchUpdate']);
        Route::put('nilai/{nilai}', [GuruNilaiController::class, 'update']);
        Route::delete('nilai/{nilai}', [GuruNilaiController::class, 'destroy']);
        
        // Nilai Kelas
        Route::get('nilai-kelas', [NilaiKelasController::class, 'index']);
        Route::get('nilai-kelas/rekap', [NilaiKelasController::class, 'rekap']);
        Route::get('nilai-kelas/siswa/{siswa}', [NilaiKelasController::class, 'bySiswa']);

        // Kehadiran
        Route::get('kehadiran', [KehadiranController::class, 'index']);
        Route::post('kehadiran/batch-update', [KehadiranController::class, 'batchUpdate']);
        Route::put('kehadiran/{kehadiran}', [KehadiranController::class, 'update']);

        // Ketidakhadiran
        Route::get('ketidakhadiran', [KehadiranController::class, 'index']);
        Route::post('ketidakhadiran/batch-update', [KehadiranController::class, 'batchUpdate']);
        Route::put('ketidakhadiran/{kehadiran}', [KehadiranController::class, 'update']);

        // Catatan Akademik
        Route::apiResource('catatan-akademik', CatatanAkademikController::class);
        Route::get('catatan-akademik/siswa/{siswa}', [CatatanAkademikController::class, 'bySiswa']);

        // Cek Penilaian
        Route::get('cek-penilaian/sts', [\App\Http\Controllers\Api\WaliKelas\CekPenilaianController::class, 'sts']);
        Route::get('cek-penilaian/sts/{mataPelajaran}', [\App\Http\Controllers\Api\WaliKelas\CekPenilaianController::class, 'stsDetail']);
        Route::get('cek-penilaian/sas', [\App\Http\Controllers\Api\WaliKelas\CekPenilaianController::class, 'sas']);
        Route::get('cek-penilaian/sas/{mataPelajaran}', [\App\Http\Controllers\Api\WaliKelas\CekPenilaianController::class, 'sasDetail']);
        Route::get('cek-penilaian/p5', [\App\Http\Controllers\Api\WaliKelas\CekPenilaianController::class, 'p5']);

        // Nilai Ekstrakurikuler
        Route::get('nilai-ekstrakurikuler/siswa', [NilaiEkstrakurikulerController::class, 'getSiswa']);
        Route::get('nilai-ekstrakurikuler/my-ekstrakurikuler', [NilaiEkstrakurikulerController::class, 'myEkstrakurikuler']);
        Route::post('nilai-ekstrakurikuler/batch-store', [NilaiEkstrakurikulerController::class, 'batchStore']);
        
        // Nilai PKL
        Route::get('nilai-pkl', [NilaiPklController::class, 'index']);
        Route::post('nilai-pkl', [NilaiPklController::class, 'store']);
        Route::get('nilai-pkl/pkl-by-jurusan', [NilaiPklController::class, 'getPklByJurusan']);

        // Rapor Management
        Route::get('rapor', [WaliRaporController::class, 'index']);
        Route::post('rapor/generate', [WaliRaporController::class, 'generate']);
        Route::get('rapor/{rapor}', [WaliRaporController::class, 'show']);
        Route::post('rapor/{rapor}/submit', [WaliRaporController::class, 'submit']);
        Route::get('rapor/{rapor}/preview', [WaliRaporController::class, 'preview']);
        Route::get('rapor/{rapor}/download', [WaliRaporController::class, 'download']);

        // Cetak Rapor Pembelajaran (Rapor Hasil Belajar - format Kurmer)
        Route::get('cetak-rapor/kelas', [CetakRaporBelajarController::class, 'kelas']);
        Route::get('cetak-rapor/belajar', [CetakRaporBelajarController::class, 'index']);
        Route::get('cetak-rapor/belajar/{siswa}/download', [CetakRaporBelajarController::class, 'download']);
        Route::get('cetak-rapor/belajar/{siswa}/transkrip', [CetakRaporBelajarController::class, 'transkrip']);

        // Cetak Profil Siswa (Biodata Rapor)
        Route::get('cetak-rapor/profil-siswa', [CetakProfilSiswaController::class, 'index']);
        Route::get('cetak-rapor/profil-siswa/{siswa}/download', [CetakProfilSiswaController::class, 'download']);
    });

    // Kepala Sekolah routes
    Route::middleware('role:kepala_sekolah')->prefix('kepala-sekolah')->group(function () {
        // Rapor Approval (Belajar)
        Route::get('rapor-approval', [RaporApprovalController::class, 'index']);
        Route::get('rapor-approval/pending', [RaporApprovalController::class, 'pending']);
        Route::get('rapor-approval/{rapor}', [RaporApprovalController::class, 'show']);
        Route::post('rapor-approval/{rapor}/approve', [RaporApprovalController::class, 'approve']);
        Route::post('rapor-approval/{rapor}/reject', [RaporApprovalController::class, 'reject']);
        Route::post('rapor-approval/bulk-approve', [RaporApprovalController::class, 'batchApprove']);
        
        // Rapor P5 Approval
        Route::get('rapor-approval-p5', [RaporApprovalController::class, 'indexP5']);
        Route::get('rapor-approval-p5/{siswa}', [RaporApprovalController::class, 'showP5']);
        Route::post('rapor-approval-p5/{siswa}/approve', [RaporApprovalController::class, 'approveP5']);
        Route::post('rapor-approval-p5/{siswa}/reject', [RaporApprovalController::class, 'rejectP5']);

        // Rekap & Reports
        Route::get('rekap/nilai', [RekapController::class, 'nilai']);
        Route::get('rekap/kehadiran', [RekapController::class, 'kehadiran']);
        Route::get('rekap/prestasi', [RekapController::class, 'prestasi']);
        Route::get('rekap/statistik', [RekapController::class, 'statistik']);
        Route::get('legger/kelas/{kelas}', [RekapController::class, 'leggerKelas']);
        Route::get('legger/download/kelas/{kelas}', [RekapController::class, 'downloadLegger']);
    });

    // Siswa routes
    Route::middleware('role:siswa')->prefix('siswa')->group(function () {
        // View Rapor
        Route::get('rapor', [RaporSiswaController::class, 'index']);
        Route::get('rapor/{rapor}', [RaporSiswaController::class, 'show']);
        Route::get('rapor/{rapor}/download', [RaporSiswaController::class, 'download']);

        // View Nilai
        Route::get('nilai', [NilaiSiswaController::class, 'index']);
        Route::get('nilai/tahun-ajaran/{tahunAjaran}', [NilaiSiswaController::class, 'byTahunAjaran']);
        Route::get('nilai/detail/{nilai}', [NilaiSiswaController::class, 'show']);

        // View Kehadiran
        Route::get('kehadiran', [NilaiSiswaController::class, 'kehadiran']);
    });

    // Shared routes (multiple roles)
    Route::middleware('role:admin,guru,wali_kelas')->group(function () {
        // Nilai Ekstrakurikuler
        Route::get('nilai-ekstrakurikuler/siswa/{siswa}', [NilaiEkstrakurikulerController::class, 'bySiswa']);
        Route::post('nilai-ekstrakurikuler', [NilaiEkstrakurikulerController::class, 'store']);
        Route::put('nilai-ekstrakurikuler/{nilaiEkstrakurikuler}', [NilaiEkstrakurikulerController::class, 'update']);
        Route::delete('nilai-ekstrakurikuler/{nilaiEkstrakurikuler}', [NilaiEkstrakurikulerController::class, 'destroy']);

        // UKK - TODO: Create UkkController
        // Route::apiResource('ukk', UkkController::class);
        // Route::get('ukk/jurusan/{jurusan}', [UkkController::class, 'byJurusan']);
    });

    // Common lookup endpoints
    Route::get('lookup/kelas', [LookupController::class, 'kelas']);
    Route::get('lookup/jurusan', [LookupController::class, 'jurusan']);
    Route::get('lookup/mata-pelajaran', [LookupController::class, 'mataPelajaran']);
    Route::get('lookup/kelas-by-mapel', [LookupController::class, 'kelasByMapel']);
    Route::get('lookup/guru', [LookupController::class, 'guru']);
    Route::get('lookup/tahun-ajaran', [LookupController::class, 'tahunAjaran']);
    Route::get('lookup/tahun-ajaran-aktif', [LookupController::class, 'tahunAjaranAktif']);
    Route::get('lookup/dimensi-p5', [LookupController::class, 'dimensiP5']);
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'message' => 'Endpoint tidak ditemukan',
    ], 404);
});