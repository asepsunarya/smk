<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * GuruController
 * 
 * Handles CRUD operations for guru (admin only)
 */
class GuruController extends Controller
{
    /**
     * Normalize nuptk: simpan NULL ketika kosong atau hanya strip (tanpa angka).
     */
    private function normalizeNuptk(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }
        if (!preg_match('/\d/', $value)) {
            return null;
        }
        return $value;
    }
    /**
     * Display a listing of guru.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Guru::with(['user']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($qu) use ($search) {
                      $qu->where('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('bidang_studi')) {
            $query->where('bidang_studi', 'like', "%{$request->bidang_studi}%");
        }

        $guru = $query->orderBy('nama_lengkap')->paginate($request->get('per_page', 15));

        return response()->json($guru);
    }

    /**
     * Store a newly created guru.
     * Only guru data, no user required.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nuptk' => [
                'nullable',
                'string',
                'regex:/^[\d-]*$/',
                function ($attribute, $value, $fail) {
                    if ($value === null || trim($value) === '' || !preg_match('/\d/', $value)) {
                        return;
                    }
                    if (Guru::where('nuptk', $value)->exists()) {
                        $fail('NUPTK sudah digunakan.');
                    }
                },
            ],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['required', 'string'],
            'pendidikan_terakhir' => ['required', 'string'],
            'bidang_studi' => ['required', 'string'],
            'tanggal_masuk' => ['required', 'date'],
        ], [
            'nuptk.regex' => 'NUPTK hanya boleh berisi angka dan tanda strip (-).',
        ]);

        DB::beginTransaction();
        try {
            $nuptk = $this->normalizeNuptk($request->nuptk);
            $guru = Guru::create([
                'user_id' => null, // No user linked yet
                'nuptk' => $nuptk,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'bidang_studi' => $request->bidang_studi,
                'tanggal_masuk' => $request->tanggal_masuk,
                'status' => 'aktif',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Guru berhasil ditambahkan',
                'data' => $guru,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified guru.
     *
     * @param  Guru  $guru
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Guru $guru)
    {
        $guru->load([
            'user',
            'ekstrakurikuler',
            'pklAsPembimbing.siswa',
            'p5AsKoordinator',
        ]);

        if ($guru->user && $guru->waliKelasAktif()->exists()) {
            $guru->load(['waliKelasAktif.kelas.siswa']);
        }

        return response()->json($guru);
    }

    /**
     * Update the specified guru.
     * User data (name, email, role) is not updated here.
     *
     * @param  Request  $request
     * @param  Guru  $guru
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Guru $guru)
    {
        $request->validate([
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'nuptk' => [
                'nullable',
                'string',
                'regex:/^[\d-]*$/',
                function ($attribute, $value, $fail) use ($guru) {
                    if ($value === null || trim($value) === '' || !preg_match('/\d/', $value)) {
                        return;
                    }
                    if (Guru::where('nuptk', $value)->where('id', '!=', $guru->id)->exists()) {
                        $fail('NUPTK sudah digunakan.');
                    }
                },
            ],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['required', 'string'],
            'pendidikan_terakhir' => ['required', 'string'],
            'bidang_studi' => ['required', 'string'],
            'status' => ['required', 'in:aktif,non_aktif,pensiun'],
        ], [
            'nuptk.regex' => 'NUPTK hanya boleh berisi angka dan tanda strip (-).',
        ]);

        DB::beginTransaction();
        try {
            $nuptk = $this->normalizeNuptk($request->nuptk);
            // Update NUPTK in user if provided and different
            if ($guru->user && $guru->user->nuptk !== $nuptk) {
                $guru->user->update([
                    'nuptk' => $nuptk,
                ]);
            }

            // Update nama_lengkap in guru to match user name if user exists, otherwise use form value
            $updateData = [
                'nuptk' => $nuptk,
                'nama_lengkap' => $guru->user ? $guru->user->name : ($request->nama_lengkap ?? $guru->nama_lengkap),
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'pendidikan_terakhir' => $request->pendidikan_terakhir,
                'bidang_studi' => $request->bidang_studi,
                'status' => $request->status,
            ];
            
            $guru->update($updateData);

            DB::commit();

            return response()->json([
                'message' => 'Guru berhasil diperbarui',
                'data' => $guru->fresh()->load('user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified guru.
     *
     * @param  Guru  $guru
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Guru $guru)
    {

        DB::beginTransaction();
        try {
            $user = $guru->user;
            $guru->delete();
            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'Guru berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus guru',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /**
     * Reset password for guru.
     *
     * @param  Request  $request
     * @param  Guru  $guru
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, Guru $guru)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $guru->user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil direset',
        ]);
    }

    /**
     * Toggle guru active status.
     *
     * @param  Guru  $guru
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(Guru $guru)
    {
        $newStatus = $guru->status === 'aktif' ? 'non_aktif' : 'aktif';
        $guru->update(['status' => $newStatus]);
        $guru->user->update(['is_active' => $newStatus === 'aktif']);

        return response()->json([
            'message' => 'Status guru berhasil diubah',
            'data' => $guru->fresh(),
        ]);
    }

    /**
     * Get users that don't have guru profile yet.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableUsers(Request $request)
    {
        $users = User::whereIn('role', ['guru', 'kepala_sekolah'])
            ->whereDoesntHave('guru')
            ->where('is_active', true)
            ->select('id', 'name', 'email', 'role', 'nuptk')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * Get guru that don't have user yet.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableGuru(Request $request)
    {
        $query = Guru::where('status', 'aktif')
            ->select('id', 'nama_lengkap', 'nuptk', 'bidang_studi', 'user_id');
        
        // If editing, include guru that's already linked to this user
        if ($request->has('user_id')) {
            $query->where(function($q) use ($request) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $request->user_id);
            });
        } else {
            $query->whereNull('user_id');
        }
        
        $guru = $query->orderBy('nama_lengkap')->get();

        return response()->json($guru);
    }
}