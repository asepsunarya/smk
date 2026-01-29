<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Jurusan;
use App\Models\User;
use App\Models\WaliKelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * KelasController
 * 
 * Handles CRUD operations for kelas (admin only)
 */
class KelasController extends Controller
{
    /**
     * Display a listing of kelas.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Kelas::with(['jurusan', 'waliKelas', 'waliKelasAktif.guru.user', 'siswa']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_kelas', 'like', "%{$search}%")
                  ->orWhereHas('jurusan', function ($qu) use ($search) {
                      $qu->where('nama_jurusan', 'like', "%{$search}%")
                        ->orWhere('kode_jurusan', 'like', "%{$search}%");
                  })
                  ->orWhereHas('waliKelasAktif.guru.user', function ($qu) use ($search) {
                      $qu->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('jurusan_id')) {
            $query->where('jurusan_id', $request->jurusan_id);
        }

        if ($request->has('tingkat')) {
            $query->where('tingkat', $request->tingkat);
        }

        if ($request->has('wali_kelas_id') && $request->wali_kelas_id) {
            $query->whereHas('waliKelasAktif.guru', function ($q) use ($request) {
                $q->where('user_id', $request->wali_kelas_id);
            });
        }

        $kelas = $query->orderBy('tingkat')
                      ->orderBy('nama_kelas')
                      ->paginate($request->get('per_page', 15));

        // Add computed attributes
        $kelas->getCollection()->transform(function ($item) {
            $item->active_siswa_count = $item->activeSiswa()->count();
            $item->is_full = $item->is_full;
            $item->available_capacity = $item->available_capacity;
            return $item;
        });

        return response()->json($kelas);
    }

    /**
     * Store a newly created kelas.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->merge(['wali_kelas_id' => $request->filled('wali_kelas_id') ? $request->wali_kelas_id : null]);

        $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'in:10,11,12'],
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:50'],
        ]);

        // Validate wali_kelas role if provided
        if ($request->wali_kelas_id) {
            $waliKelas = User::find($request->wali_kelas_id);
            if (!$waliKelas || !in_array($waliKelas->role, ['wali_kelas', 'guru', 'kepala_sekolah'])) {
                return response()->json([
                    'message' => 'User yang dipilih harus memiliki role wali kelas, guru, atau kepala sekolah',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $kelas = Kelas::create([
                'nama_kelas' => $request->nama_kelas,
                'tingkat' => $request->tingkat,
                'jurusan_id' => $request->jurusan_id,
                'kapasitas' => $request->kapasitas,
            ]);

            $this->syncWaliKelasForKelas($kelas, $request->wali_kelas_id);

            DB::commit();

            $kelas->load(['jurusan', 'waliKelasAktif.guru.user']);
            return response()->json([
                'message' => 'Kelas berhasil ditambahkan',
                'data' => $kelas,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified kelas.
     *
     * @param  Kelas  $kela  (route param {kela} from apiResource)
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Kelas $kela)
    {
        $kela->load([
            'jurusan',
            'waliKelasAktif.guru.user',
            'siswa.user',
        ]);

        // Add computed attributes
        $kela->active_siswa_count = $kela->activeSiswa()->count();
        $kela->is_full = $kela->is_full;
        $kela->available_capacity = $kela->available_capacity;

        return response()->json($kela);
    }

    /**
     * Update the specified kelas.
     *
     * @param  Request  $request
     * @param  Kelas  $kela  (route param {kela} from apiResource)
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Kelas $kela)
    {
        $kelas = $kela;

        $request->validate([
            'nama_kelas' => ['required', 'string', 'max:255'],
            'tingkat' => ['required', 'in:10,11,12'],
            'jurusan_id' => ['required', 'exists:jurusan,id'],
            'kapasitas' => ['required', 'integer', 'min:1', 'max:50'],
            'wali_kelas_id' => ['nullable', 'exists:users,id'],
        ]);

        // Validate kapasitas - cannot be less than current active students
        $currentActiveSiswa = $kelas->activeSiswa()->count();
        if ((int) $request->kapasitas < $currentActiveSiswa) {
            return response()->json([
                'message' => "Kapasitas tidak boleh kurang dari jumlah siswa aktif ({$currentActiveSiswa} siswa)",
            ], 422);
        }

        // Validasi role wali hanya jika wali_kelas_id dikirim (opsional)
        if ($request->has('wali_kelas_id') && $request->wali_kelas_id) {
            $waliKelas = User::find($request->wali_kelas_id);
            if (!$waliKelas || !in_array($waliKelas->role, ['wali_kelas', 'guru', 'kepala_sekolah'])) {
                return response()->json([
                    'message' => 'User yang dipilih harus memiliki role wali kelas, guru, atau kepala sekolah',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $kelas->nama_kelas = $request->nama_kelas;
            $kelas->tingkat = $request->tingkat;
            $kelas->jurusan_id = $request->jurusan_id;
            $kelas->kapasitas = $request->kapasitas;
            $kelas->save();

            // Sinkron wali kelas hanya jika request mengirim wali_kelas_id (opsional)
            if ($request->has('wali_kelas_id')) {
                $this->syncWaliKelasForKelas($kelas, $request->wali_kelas_id ?: null);
            }

            DB::commit();

            $kelas->unsetRelation('jurusan')->unsetRelation('waliKelasAktif');
            $kelas->load(['jurusan', 'waliKelasAktif.guru.user']);

            return response()->json([
                'message' => 'Kelas berhasil diperbarui',
                'data' => $kelas,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sync wali kelas for a kelas: set or clear active wali via wali_kelas table.
     * $waliKelasUserId = user id (nullable).
     */
    protected function syncWaliKelasForKelas(Kelas $kelas, $waliKelasUserId): void
    {
        WaliKelas::where('kelas_id', $kelas->id)->where('is_active', true)->update(['is_active' => false]);

        if (!$waliKelasUserId) {
            return;
        }

        $guru = \App\Models\Guru::where('user_id', $waliKelasUserId)->with('user')->first();
        if (!$guru || !$guru->user || !in_array($guru->user->role, ['wali_kelas', 'guru', 'kepala_sekolah'])) {
            return;
        }

        WaliKelas::create([
            'guru_id' => $guru->id,
            'kelas_id' => $kelas->id,
            'is_active' => true,
            'tanggal_mulai' => now(),
        ]);
    }

    /**
     * Remove the specified kelas.
     *
     * @param  Kelas  $kela  (route param {kela} from apiResource)
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Kelas $kela)
    {
        $kelas = $kela;

        // Prevent deleting kelas if it has students
        if ($kelas->siswa()->exists()) {
            return response()->json([
                'message' => 'Kelas tidak dapat dihapus karena masih memiliki siswa. Pindahkan siswa terlebih dahulu.',
            ], 422);
        }


        DB::beginTransaction();
        try {
            $kelas->delete();

            DB::commit();

            return response()->json([
                'message' => 'Kelas berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Assign wali kelas to kelas.
     *
     * @param  Request  $request
     * @param  Kelas  $kelas
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignWali(Request $request, Kelas $kelas)
    {
        $request->validate([
            'guru_id' => ['required', 'exists:guru,id'],
        ]);

        $guru = \App\Models\Guru::find($request->guru_id);
        
        if (!$guru->user || !in_array($guru->user->role, ['wali_kelas', 'guru', 'kepala_sekolah'])) {
            return response()->json([
                'message' => 'Guru yang dipilih harus memiliki role wali kelas, guru, atau kepala sekolah',
            ], 422);
        }

        // Check if kelas already has active wali kelas
        $existingWaliKelas = WaliKelas::where('kelas_id', $kelas->id)
                                                  ->where('is_active', true)
                                                  ->first();

        if ($existingWaliKelas) {
            return response()->json([
                'message' => 'Kelas sudah memiliki wali kelas aktif. Nonaktifkan wali kelas yang ada terlebih dahulu.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $waliKelas = WaliKelas::create([
                'guru_id' => $request->guru_id,
                'kelas_id' => $kelas->id,
                'is_active' => true,
                'tanggal_mulai' => now(),
            ]);

            DB::commit();

            $kelas->load('waliKelasAktif.guru.user');
            return response()->json([
                'message' => 'Wali kelas berhasil ditetapkan',
                'data' => $kelas,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menetapkan wali kelas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get siswa in kelas.
     *
     * @param  Kelas  $kelas
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSiswa(Kelas $kelas, Request $request)
    {
        $query = $kelas->siswa()->with(['user']);

        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to active students
            $query->where('status', 'aktif');
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $siswa = $query->orderBy('nama_lengkap')->paginate($request->get('per_page', 15));

        return response()->json($siswa);
    }
}

