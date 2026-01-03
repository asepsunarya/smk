<?php

namespace App\Http\Controllers\Api\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Nilai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CekPenilaianController for Wali Kelas
 * 
 * Handles penilaian checking for homeroom teachers
 */
class CekPenilaianController extends Controller
{
    /**
     * Get mata pelajaran for STS (Sumatif Tengah Semester) checking.
     * Returns all mata pelajaran from classes managed by the wali-kelas.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sts(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'wali_kelas') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get active classes where user is wali kelas
        $kelas = $user->kelasAsWali();
        
        if ($kelas->isEmpty()) {
            return response()->json([
                'message' => 'Anda belum ditugaskan sebagai wali kelas',
                'kelas' => [],
                'mata_pelajaran' => []
            ], 200);
        }

        // Filter by kelas_id if provided
        $kelasIds = $kelas->pluck('id');
        if ($request->has('kelas_id') && $request->kelas_id) {
            $kelasIds = $kelasIds->intersect([$request->kelas_id]);
            if ($kelasIds->isEmpty()) {
                return response()->json([
                    'message' => 'Kelas tidak ditemukan atau tidak diwalikan oleh Anda',
                    'kelas' => $kelas,
                    'mata_pelajaran' => [],
                ], 200);
            }
        }

        // Get tahun ajaran aktif
        $tahunAjaran = \App\Models\TahunAjaran::where('is_active', true)->first();
        
        if (!$tahunAjaran) {
            return response()->json([
                'message' => 'Tahun ajaran aktif tidak ditemukan',
                'kelas' => $kelas,
                'mata_pelajaran' => [],
            ], 200);
        }

        // Get all mata pelajaran from filtered classes
        $mataPelajaranIds = collect();
        foreach ($kelas->whereIn('id', $kelasIds) as $k) {
            $mapelIds = $k->mataPelajaran()
                ->where('mata_pelajaran.is_active', true)
                ->pluck('mata_pelajaran.id');
            $mataPelajaranIds = $mataPelajaranIds->merge($mapelIds);
        }

        // Get unique mata pelajaran
        $uniqueMapelIds = $mataPelajaranIds->unique()->values();
        
        if ($uniqueMapelIds->isEmpty()) {
            return response()->json([
                'kelas' => $kelas,
                'mata_pelajaran' => [],
            ]);
        }

        $mataPelajaran = MataPelajaran::whereIn('id', $uniqueMapelIds)
            ->where('is_active', true)
            ->with(['guru.user'])
            ->orderBy('nama_mapel')
            ->get();

        // Get semester from request (default to 1 for STS)
        $semester = $request->has('semester') && $request->semester ? $request->semester : '1';

        // Get tingkat from selected classes
        $tingkatList = \App\Models\Kelas::whereIn('id', $kelasIds)
            ->distinct('tingkat')
            ->pluck('tingkat')
            ->filter()
            ->values();

        // Calculate progress for each mata pelajaran
        $mataPelajaran->each(function($mapel) use ($kelasIds, $tahunAjaran, $semester, $tingkatList) {
            // Get all active students from filtered classes
            $totalSiswa = \App\Models\Siswa::whereIn('kelas_id', $kelasIds)
                ->where('status', 'aktif')
                ->count();

            // Get all active CP for this mata pelajaran that match semester, tingkat, and target (tengah_semester for STS)
            $allCP = \App\Models\CapaianPembelajaran::where('mata_pelajaran_id', $mapel->id)
                ->where('is_active', true)
                ->where(function($query) use ($semester, $tingkatList) {
                    // Filter by semester: CP must have semester matching selected semester, or no semester (backward compatibility)
                    $query->where(function($q) use ($semester) {
                        $q->where('semester', $semester)
                          ->orWhereNull('semester');
                    });
                    
                    // Filter by tingkat: CP must have tingkat matching selected kelas tingkat, or no tingkat (backward compatibility)
                    if ($tingkatList->isNotEmpty()) {
                        $query->where(function($q) use ($tingkatList) {
                            $q->whereIn('tingkat', $tingkatList)
                              ->orWhereNull('tingkat');
                        });
                    }
                })
                ->where('target', 'tengah_semester') // Only CP with target 'tengah_semester' for STS
                ->get();

            // Get STS CP (kode_cp = 'STS' or target = 'tengah_semester')
            $stsCP = $allCP->where('kode_cp', 'STS')->first();
            if (!$stsCP) {
                $stsCP = $allCP->where('target', 'tengah_semester')->where('kode_cp', 'like', 'CP-%')->first();
            }

            // Get other CP (exclude STS/SAS kode_cp, but include all CP with tengah_semester target)
            $otherCP = $allCP->where('kode_cp', '!=', 'STS')
                            ->where('kode_cp', '!=', 'SAS')
                            ->where('target', 'tengah_semester');
            
            $progressData = [];
            $hasActiveCP = $otherCP->isNotEmpty();

            // Calculate progress for all active CP that have nilai in the selected semester (exclude STS and SAS)
            foreach ($otherCP as $cp) {
                $siswaSudahInput = Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester', $semester)
                    ->where('capaian_pembelajaran_id', $cp->id)
                    ->whereHas('siswa', function($q) use ($kelasIds) {
                        $q->whereIn('kelas_id', $kelasIds)->where('status', 'aktif');
                    })
                    ->distinct('siswa_id')
                    ->count('siswa_id');

                $progressData[] = [
                    'capaian_pembelajaran_id' => $cp->id,
                    'kode_cp' => $cp->kode_cp,
                    'sudah_input' => $siswaSudahInput,
                    'total' => $totalSiswa,
                    'lengkap' => $siswaSudahInput == $totalSiswa && $totalSiswa > 0,
                ];
            }

            // Always show STS if there are active CP (other than STS/SAS) that have nilai in the selected semester
            if ($stsCP || $hasActiveCP) {
                $stsSudahInput = 0;
                if ($stsCP) {
                    $stsSudahInput = Nilai::where('mata_pelajaran_id', $mapel->id)
                        ->where('tahun_ajaran_id', $tahunAjaran->id)
                        ->where('semester', $semester)
                        ->where('capaian_pembelajaran_id', $stsCP->id)
                        ->whereHas('siswa', function($q) use ($kelasIds) {
                            $q->whereIn('kelas_id', $kelasIds)->where('status', 'aktif');
                        })
                        ->distinct('siswa_id')
                        ->count('siswa_id');
                }

                $progressData[] = [
                    'capaian_pembelajaran_id' => $stsCP ? $stsCP->id : null,
                    'kode_cp' => 'STS',
                    'sudah_input' => $stsSudahInput,
                    'total' => $totalSiswa,
                    'lengkap' => $stsSudahInput == $totalSiswa && $totalSiswa > 0,
                ];
            }

            $mapel->progress = [
                'total_siswa' => $totalSiswa,
                'total_cp' => count($progressData),
                'progress_data' => $progressData,
            ];
        });

        return response()->json([
            'kelas' => $kelas,
            'mata_pelajaran' => $mataPelajaran,
        ]);
    }

    /**
     * Get mata pelajaran for SAS (Sumatif Akhir Semester) checking.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sas(Request $request)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'wali_kelas') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get active classes where user is wali kelas
        $kelas = $user->kelasAsWali();
        
        if ($kelas->isEmpty()) {
            return response()->json([
                'message' => 'Anda belum ditugaskan sebagai wali kelas',
                'kelas' => [],
                'mata_pelajaran' => []
            ], 200);
        }

        // Filter by kelas_id if provided
        $kelasIds = $kelas->pluck('id');
        if ($request->has('kelas_id') && $request->kelas_id) {
            $kelasIds = $kelasIds->intersect([$request->kelas_id]);
            if ($kelasIds->isEmpty()) {
                return response()->json([
                    'message' => 'Kelas tidak ditemukan atau tidak diwalikan oleh Anda',
                    'kelas' => $kelas,
                    'mata_pelajaran' => [],
                ], 200);
            }
        }

        // Get tahun ajaran aktif
        $tahunAjaran = \App\Models\TahunAjaran::where('is_active', true)->first();
        
        if (!$tahunAjaran) {
            return response()->json([
                'message' => 'Tahun ajaran aktif tidak ditemukan',
                'kelas' => $kelas,
                'mata_pelajaran' => [],
            ], 200);
        }

        // Get all mata pelajaran from filtered classes
        $mataPelajaranIds = collect();
        foreach ($kelas->whereIn('id', $kelasIds) as $k) {
            $mapelIds = $k->mataPelajaran()
                ->where('mata_pelajaran.is_active', true)
                ->pluck('mata_pelajaran.id');
            $mataPelajaranIds = $mataPelajaranIds->merge($mapelIds);
        }

        // Get unique mata pelajaran
        $uniqueMapelIds = $mataPelajaranIds->unique()->values();
        
        if ($uniqueMapelIds->isEmpty()) {
            return response()->json([
                'kelas' => $kelas,
                'mata_pelajaran' => [],
            ]);
        }

        $mataPelajaran = MataPelajaran::whereIn('id', $uniqueMapelIds)
            ->where('is_active', true)
            ->with(['guru.user'])
            ->orderBy('nama_mapel')
            ->get();

        // Get semester from request (default to 2 for SAS)
        $semester = $request->has('semester') && $request->semester ? $request->semester : '2';

        // Get tingkat from selected classes
        $tingkatList = \App\Models\Kelas::whereIn('id', $kelasIds)
            ->distinct('tingkat')
            ->pluck('tingkat')
            ->filter()
            ->values();

        // Calculate progress for each mata pelajaran
        $mataPelajaran->each(function($mapel) use ($kelasIds, $tahunAjaran, $semester, $tingkatList) {
            // Get all active students from filtered classes
            $totalSiswa = \App\Models\Siswa::whereIn('kelas_id', $kelasIds)
                ->where('status', 'aktif')
                ->count();

            // Get all active CP for this mata pelajaran that match semester, tingkat, and target (akhir_semester for SAS)
            $allCP = \App\Models\CapaianPembelajaran::where('mata_pelajaran_id', $mapel->id)
                ->where('is_active', true)
                ->where(function($query) use ($semester, $tingkatList) {
                    // Filter by semester: CP must have semester matching selected semester, or no semester (backward compatibility)
                    $query->where(function($q) use ($semester) {
                        $q->where('semester', $semester)
                          ->orWhereNull('semester');
                    });
                    
                    // Filter by tingkat: CP must have tingkat matching selected kelas tingkat, or no tingkat (backward compatibility)
                    if ($tingkatList->isNotEmpty()) {
                        $query->where(function($q) use ($tingkatList) {
                            $q->whereIn('tingkat', $tingkatList)
                              ->orWhereNull('tingkat');
                        });
                    }
                })
                ->where('target', 'akhir_semester') // Only CP with target 'akhir_semester' for SAS
                ->get();

            // Get SAS CP (kode_cp = 'SAS' or target = 'akhir_semester')
            $sasCP = $allCP->where('kode_cp', 'SAS')->first();
            if (!$sasCP) {
                $sasCP = $allCP->where('target', 'akhir_semester')->where('kode_cp', 'like', 'CP-%')->first();
            }

            // Get other CP (exclude STS/SAS kode_cp, but include all CP with akhir_semester target)
            $otherCP = $allCP->where('kode_cp', '!=', 'STS')
                            ->where('kode_cp', '!=', 'SAS')
                            ->where('target', 'akhir_semester');
            
            $progressData = [];
            $hasActiveCP = $otherCP->isNotEmpty();

            // Calculate progress for all active CP that have nilai in the selected semester (exclude STS and SAS)
            foreach ($otherCP as $cp) {
                $siswaSudahInput = Nilai::where('mata_pelajaran_id', $mapel->id)
                    ->where('tahun_ajaran_id', $tahunAjaran->id)
                    ->where('semester', $semester)
                    ->where('capaian_pembelajaran_id', $cp->id)
                    ->whereHas('siswa', function($q) use ($kelasIds) {
                        $q->whereIn('kelas_id', $kelasIds)->where('status', 'aktif');
                    })
                    ->distinct('siswa_id')
                    ->count('siswa_id');

                $progressData[] = [
                    'capaian_pembelajaran_id' => $cp->id,
                    'kode_cp' => $cp->kode_cp,
                    'sudah_input' => $siswaSudahInput,
                    'total' => $totalSiswa,
                    'lengkap' => $siswaSudahInput == $totalSiswa && $totalSiswa > 0,
                ];
            }

            // Always show SAS if there are active CP (other than STS/SAS) that have nilai in the selected semester
            if ($sasCP || $hasActiveCP) {
                $sasSudahInput = 0;
                if ($sasCP) {
                    $sasSudahInput = Nilai::where('mata_pelajaran_id', $mapel->id)
                        ->where('tahun_ajaran_id', $tahunAjaran->id)
                        ->where('semester', $semester)
                        ->where('capaian_pembelajaran_id', $sasCP->id)
                        ->whereHas('siswa', function($q) use ($kelasIds) {
                            $q->whereIn('kelas_id', $kelasIds)->where('status', 'aktif');
                        })
                        ->distinct('siswa_id')
                        ->count('siswa_id');
                }

                $progressData[] = [
                    'capaian_pembelajaran_id' => $sasCP ? $sasCP->id : null,
                    'kode_cp' => 'SAS',
                    'sudah_input' => $sasSudahInput,
                    'total' => $totalSiswa,
                    'lengkap' => $sasSudahInput == $totalSiswa && $totalSiswa > 0,
                ];
            }

            $mapel->progress = [
                'total_siswa' => $totalSiswa,
                'total_cp' => count($progressData),
                'progress_data' => $progressData,
            ];
        });

        return response()->json([
            'kelas' => $kelas,
            'mata_pelajaran' => $mataPelajaran,
        ]);
    }

    /**
     * Get detail STS for a specific mata pelajaran.
     * Returns list of students with their STS nilai status and grades.
     *
     * @param  Request  $request
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function stsDetail(Request $request, MataPelajaran $mataPelajaran)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'wali_kelas') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get active classes where user is wali kelas
        $kelas = $user->kelasAsWali();
        
        if ($kelas->isEmpty()) {
            return response()->json([
                'message' => 'Anda belum ditugaskan sebagai wali kelas',
            ], 404);
        }

        // Verify that mata pelajaran is taught in user's classes
        $kelasIds = $kelas->pluck('id');
        $isMapelInKelas = $mataPelajaran->kelas()
            ->whereIn('kelas.id', $kelasIds)
            ->exists();
        
        if (!$isMapelInKelas) {
            return response()->json([
                'message' => 'Mata pelajaran tidak ditemukan di kelas yang Anda walikan',
            ], 404);
        }

        // Get tahun ajaran aktif
        $tahunAjaran = \App\Models\TahunAjaran::where('is_active', true)->first();
        
        if (!$tahunAjaran) {
            return response()->json([
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        // Get all active students from user's classes
        $siswaIds = collect();
        foreach ($kelas as $k) {
            $siswaIds = $siswaIds->merge($k->siswa()->where('status', 'aktif')->pluck('id'));
        }

        // Get semester from request (default to 1 for STS)
        $semester = $request->has('semester') && $request->semester ? $request->semester : '1';

        // Get all siswa with their nilai for STS
        $siswa = \App\Models\Siswa::whereIn('id', $siswaIds->unique())
            ->with(['user', 'kelas.jurusan'])
            ->orderBy('nama_lengkap')
            ->get();

        // Get tingkat from selected classes
        $tingkatList = \App\Models\Kelas::whereIn('id', $kelasIds)
            ->distinct('tingkat')
            ->pluck('tingkat')
            ->filter()
            ->values();

        // Get all active CP for this mata pelajaran that match semester, tingkat, and target (tengah_semester for STS)
        $allCP = \App\Models\CapaianPembelajaran::where('mata_pelajaran_id', $mataPelajaran->id)
            ->where('is_active', true)
            ->where(function($query) use ($semester, $tingkatList) {
                // Filter by semester: CP must have semester matching selected semester, or no semester (backward compatibility)
                $query->where(function($q) use ($semester) {
                    $q->where('semester', $semester)
                      ->orWhereNull('semester');
                });
                
                // Filter by tingkat: CP must have tingkat matching selected kelas tingkat, or no tingkat (backward compatibility)
                if ($tingkatList->isNotEmpty()) {
                    $query->where(function($q) use ($tingkatList) {
                        $q->whereIn('tingkat', $tingkatList)
                          ->orWhereNull('tingkat');
                    });
                }
            })
            ->where('target', 'tengah_semester') // Only CP with target 'tengah_semester' for STS
            ->get();

        // Get STS CP (kode_cp = 'STS' or target = 'tengah_semester')
        $stsCP = $allCP->where('kode_cp', 'STS')->first();
        if (!$stsCP) {
            $stsCP = $allCP->where('target', 'tengah_semester')->where('kode_cp', 'like', 'CP-%')->first();
        }

        // Get other CP (exclude STS/SAS kode_cp, but include all CP with tengah_semester target)
        $otherCP = $allCP->where('kode_cp', '!=', 'STS')
                        ->where('kode_cp', '!=', 'SAS')
                        ->where('target', 'tengah_semester');

        // If there are active CP (other than STS/SAS) that have nilai in the selected semester but STS CP doesn't exist, add STS to the list
        if ($otherCP->isNotEmpty() && !$stsCP) {
            // Create a virtual STS CP entry for display purposes
            $virtualSTSCP = (object)[
                'id' => null,
                'kode_cp' => 'STS',
                'deskripsi' => 'Sumatif Tengah Semester',
                'is_active' => true,
            ];
            $allCP = $allCP->push($virtualSTSCP);
        }

        // Get all nilai for this mata pelajaran, based on semester, tahun ajaran aktif
        $nilai = Nilai::where('mata_pelajaran_id', $mataPelajaran->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds->unique())
            ->with(['capaianPembelajaran', 'siswa.user'])
            ->get()
            ->groupBy('siswa_id');

        // Prepare response data
        $siswaData = $siswa->map(function($s) use ($nilai, $mataPelajaran, $allCP) {
            $siswaNilai = $nilai->get($s->id, collect());
            
            // Create nilai map by CP kode
            $nilaiByCP = [];
            foreach ($allCP as $cp) {
                $nilaiForCP = $siswaNilai->firstWhere('capaian_pembelajaran_id', $cp->id);
                $nilaiByCP[$cp->kode_cp] = $nilaiForCP ? [
                    'id' => $nilaiForCP->id,
                    'nilai' => $nilaiForCP->nilai_akhir ?? $nilaiForCP->nilai_sumatif_1,
                    'deskripsi' => $nilaiForCP->deskripsi,
                ] : null;
            }
            
            return [
                'id' => $s->id,
                'nis' => $s->nis,
                'nama_lengkap' => $s->nama_lengkap,
                'kelas' => [
                    'id' => $s->kelas->id,
                    'nama_kelas' => $s->kelas->nama_kelas,
                    'jurusan' => $s->kelas->jurusan ? $s->kelas->jurusan->nama_jurusan : null,
                ],
                'sudah_mengerjakan' => collect($nilaiByCP)->filter()->isNotEmpty(),
                'nilai_by_cp' => $nilaiByCP,
            ];
        });

        // Get CP list for frontend (excluding SAS, STS at the end)
        $cpList = $allCP->map(function($cp) {
            return [
                'id' => $cp->id,
                'kode_cp' => $cp->kode_cp,
                'deskripsi' => $cp->deskripsi,
            ];
        })->sortBy(function($cp) {
            // Sort: CP first (alphabetically), then STS at the end
            if ($cp['kode_cp'] === 'STS') {
                return 'zzz'; // Put STS at the end
            }
            return $cp['kode_cp'];
        })->values();

        return response()->json([
            'mata_pelajaran' => [
                'id' => $mataPelajaran->id,
                'kode_mapel' => $mataPelajaran->kode_mapel,
                'nama_mapel' => $mataPelajaran->nama_mapel,
                'kkm' => $mataPelajaran->kkm,
            ],
            'tahun_ajaran' => [
                'id' => $tahunAjaran->id,
                'tahun' => $tahunAjaran->tahun,
                'semester' => $tahunAjaran->semester,
            ],
            'capaian_pembelajaran' => $cpList,
            'siswa' => $siswaData,
        ]);
    }

    /**
     * Get detail SAS for a specific mata pelajaran.
     * Returns list of students with their SAS nilai status and grades.
     *
     * @param  Request  $request
     * @param  MataPelajaran  $mataPelajaran
     * @return \Illuminate\Http\JsonResponse
     */
    public function sasDetail(Request $request, MataPelajaran $mataPelajaran)
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'wali_kelas') {
            return response()->json([
                'message' => 'Unauthorized',
            ], 403);
        }

        // Get active classes where user is wali kelas
        $kelas = $user->kelasAsWali();
        
        if ($kelas->isEmpty()) {
            return response()->json([
                'message' => 'Anda belum ditugaskan sebagai wali kelas',
            ], 404);
        }

        // Verify that mata pelajaran is taught in user's classes
        $kelasIds = $kelas->pluck('id');
        $isMapelInKelas = $mataPelajaran->kelas()
            ->whereIn('kelas.id', $kelasIds)
            ->exists();
        
        if (!$isMapelInKelas) {
            return response()->json([
                'message' => 'Mata pelajaran tidak ditemukan di kelas yang Anda walikan',
            ], 404);
        }

        // Get tahun ajaran aktif
        $tahunAjaran = \App\Models\TahunAjaran::where('is_active', true)->first();
        
        if (!$tahunAjaran) {
            return response()->json([
                'message' => 'Tahun ajaran aktif tidak ditemukan',
            ], 404);
        }

        // Get all active students from user's classes
        $siswaIds = collect();
        foreach ($kelas as $k) {
            $siswaIds = $siswaIds->merge($k->siswa()->where('status', 'aktif')->pluck('id'));
        }

        // Get semester from request (default to 2 for SAS)
        $semester = $request->has('semester') && $request->semester ? $request->semester : '2';

        // Get all siswa with their nilai for SAS
        $siswa = \App\Models\Siswa::whereIn('id', $siswaIds->unique())
            ->with(['user', 'kelas.jurusan'])
            ->orderBy('nama_lengkap')
            ->get();

        // Get tingkat from selected classes
        $tingkatList = \App\Models\Kelas::whereIn('id', $kelasIds)
            ->distinct('tingkat')
            ->pluck('tingkat')
            ->filter()
            ->values();

        // Get all active CP for this mata pelajaran that match semester, tingkat, and target (akhir_semester for SAS)
        $allCP = \App\Models\CapaianPembelajaran::where('mata_pelajaran_id', $mataPelajaran->id)
            ->where('is_active', true)
            ->where(function($query) use ($semester, $tingkatList) {
                // Filter by semester: CP must have semester matching selected semester, or no semester (backward compatibility)
                $query->where(function($q) use ($semester) {
                    $q->where('semester', $semester)
                      ->orWhereNull('semester');
                });
                
                // Filter by tingkat: CP must have tingkat matching selected kelas tingkat, or no tingkat (backward compatibility)
                if ($tingkatList->isNotEmpty()) {
                    $query->where(function($q) use ($tingkatList) {
                        $q->whereIn('tingkat', $tingkatList)
                          ->orWhereNull('tingkat');
                    });
                }
            })
            ->where('target', 'akhir_semester') // Only CP with target 'akhir_semester' for SAS
            ->get();

        // Get SAS CP (kode_cp = 'SAS' or target = 'akhir_semester')
        $sasCP = $allCP->where('kode_cp', 'SAS')->first();
        if (!$sasCP) {
            $sasCP = $allCP->where('target', 'akhir_semester')->where('kode_cp', 'like', 'CP-%')->first();
        }

        // Get other CP (exclude STS/SAS kode_cp, but include all CP with akhir_semester target)
        $otherCP = $allCP->where('kode_cp', '!=', 'STS')
                        ->where('kode_cp', '!=', 'SAS')
                        ->where('target', 'akhir_semester');

        // If there are active CP (other than STS/SAS) that have nilai in the selected semester but SAS CP doesn't exist, add SAS to the list
        if ($otherCP->isNotEmpty() && !$sasCP) {
            // Create a virtual SAS CP entry for display purposes
            $virtualSASCP = (object)[
                'id' => null,
                'kode_cp' => 'SAS',
                'deskripsi' => 'Sumatif Akhir Semester',
                'is_active' => true,
            ];
            $allCP = $allCP->push($virtualSASCP);
        }

        // Get all nilai for this mata pelajaran, based on semester, tahun ajaran aktif
        $nilai = Nilai::where('mata_pelajaran_id', $mataPelajaran->id)
            ->where('tahun_ajaran_id', $tahunAjaran->id)
            ->where('semester', $semester)
            ->whereIn('siswa_id', $siswaIds->unique())
            ->with(['capaianPembelajaran', 'siswa.user'])
            ->get()
            ->groupBy('siswa_id');

        // Prepare response data
        $siswaData = $siswa->map(function($s) use ($nilai, $mataPelajaran, $allCP) {
            $siswaNilai = $nilai->get($s->id, collect());
            
            // Create nilai map by CP kode
            $nilaiByCP = [];
            foreach ($allCP as $cp) {
                $nilaiForCP = $siswaNilai->firstWhere('capaian_pembelajaran_id', $cp->id);
                $nilaiByCP[$cp->kode_cp] = $nilaiForCP ? [
                    'id' => $nilaiForCP->id,
                    'nilai' => $nilaiForCP->nilai_akhir ?? $nilaiForCP->nilai_sumatif_1,
                    'deskripsi' => $nilaiForCP->deskripsi,
                ] : null;
            }
            
            return [
                'id' => $s->id,
                'nis' => $s->nis,
                'nama_lengkap' => $s->nama_lengkap,
                'kelas' => [
                    'id' => $s->kelas->id,
                    'nama_kelas' => $s->kelas->nama_kelas,
                    'jurusan' => $s->kelas->jurusan ? $s->kelas->jurusan->nama_jurusan : null,
                ],
                'sudah_mengerjakan' => collect($nilaiByCP)->filter()->isNotEmpty(),
                'nilai_by_cp' => $nilaiByCP,
            ];
        });

        // Get CP list for frontend (excluding STS, SAS at the end)
        $cpList = $allCP->map(function($cp) {
            return [
                'id' => $cp->id,
                'kode_cp' => $cp->kode_cp,
                'deskripsi' => $cp->deskripsi,
            ];
        })->sortBy(function($cp) {
            // Sort: CP first (alphabetically), then SAS at the end
            if ($cp['kode_cp'] === 'SAS') {
                return 'zzz'; // Put SAS at the end
            }
            return $cp['kode_cp'];
        })->values();

        return response()->json([
            'mata_pelajaran' => [
                'id' => $mataPelajaran->id,
                'kode_mapel' => $mataPelajaran->kode_mapel,
                'nama_mapel' => $mataPelajaran->nama_mapel,
                'kkm' => $mataPelajaran->kkm,
            ],
            'tahun_ajaran' => [
                'id' => $tahunAjaran->id,
                'tahun' => $tahunAjaran->tahun,
                'semester' => $tahunAjaran->semester,
            ],
            'capaian_pembelajaran' => $cpList,
            'siswa' => $siswaData,
        ]);
    }

    /**
     * Get mata pelajaran for P5 checking.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function p5(Request $request)
    {
        // Same as STS for now
        return $this->sts($request);
    }
}

