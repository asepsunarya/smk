<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * UserController
 * 
 * Handles CRUD operations for users (admin only)
 */
class UserController extends Controller
{
    /**
     * Display a listing of users.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = User::with(['guru', 'siswa.kelas.jurusan']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('nuptk', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhereHas('guru', function ($qg) use ($search) {
                      $qg->where('nuptk', 'like', "%{$search}%")
                         ->orWhere('nama_lengkap', 'like', "%{$search}%");
                  })
                  ->orWhereHas('siswa', function ($qs) use ($search) {
                      $qs->where('nis', 'like', "%{$search}%")
                         ->orWhere('nama_lengkap', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->has('role')) {
            $query->where('role', $request->role);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active === 'true' || $request->is_active === '1');
        }

        $users = $query->orderBy('name')->paginate($request->get('per_page', 15));

        return response()->json($users);
    }

    /**
     * Store a newly created user.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['nullable', 'string', 'max:255'], // Optional, will be filled from selected data
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:admin,guru,wali_kelas,kepala_sekolah,siswa'],
            'guru_id' => ['nullable', 'exists:guru,id', 'required_if:role,guru,wali_kelas,kepala_sekolah'],
            'siswa_id' => ['nullable', 'exists:siswa,id', 'required_if:role,siswa'],
            'nuptk' => ['nullable', 'string', 'unique:users'],
            'nis' => ['nullable', 'string', 'unique:users'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Validate role-specific fields
        if (in_array($request->role, ['guru', 'wali_kelas', 'kepala_sekolah'])) {
            // For these roles, guru_id is required
            if (!$request->guru_id) {
                return response()->json([
                    'message' => 'Guru harus dipilih untuk role ' . $request->role,
                ], 422);
            }

            // Get data from guru
            $guru = \App\Models\Guru::find($request->guru_id);
            if ($guru && $guru->user_id) {
                return response()->json([
                    'message' => 'Guru ini sudah memiliki user',
                ], 422);
            }
            
            // Auto-fill name and NUPTK from guru
            if ($guru) {
                $request->merge([
                    'name' => $guru->nama_lengkap,
                    'nuptk' => $guru->nuptk,
                ]);
            }
        } elseif ($request->role === 'siswa') {
            // For siswa role, siswa_id is required
            if (!$request->siswa_id) {
                return response()->json([
                    'message' => 'Siswa harus dipilih untuk role siswa',
                ], 422);
            }

            // Get data from siswa
            $siswa = \App\Models\Siswa::find($request->siswa_id);
            if ($siswa && $siswa->user_id) {
                return response()->json([
                    'message' => 'Siswa ini sudah memiliki user',
                ], 422);
            }
            
            // Auto-fill name and NIS from siswa
            if ($siswa) {
                $request->merge([
                    'name' => $siswa->nama_lengkap,
                    'nis' => $siswa->nis,
                ]);
            }
        } elseif ($request->role === 'admin') {
            // Admin requires manual name input
            if (!$request->name) {
                return response()->json([
                    'message' => 'Nama lengkap wajib diisi untuk role admin',
                ], 422);
            }
        }

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'nuptk' => $request->nuptk,
                'nis' => $request->nis,
                'is_active' => $request->is_active ?? true,
            ]);

            // Link guru to user if guru_id is provided
            if ($request->guru_id) {
                $guru = \App\Models\Guru::find($request->guru_id);
                if ($guru) {
                    $guru->update([
                        'user_id' => $user->id,
                        'nama_lengkap' => $request->name, // Update nama_lengkap to match user name
                    ]);
                }
            }

            // Link siswa to user if siswa_id is provided
            if ($request->siswa_id) {
                $siswa = \App\Models\Siswa::find($request->siswa_id);
                if ($siswa) {
                    $siswa->update([
                        'user_id' => $user->id,
                        'nama_lengkap' => $request->name, // Update nama_lengkap to match user name
                    ]);
                }
            }

            DB::commit();

            // Load relationships based on role
            if ($user->role === 'siswa') {
                $user->load(['siswa.kelas.jurusan']);
            } else {
                $user->load(['guru', 'siswa.kelas.jurusan']);
                
                // Load kelasAsWali separately if user has guru
                if ($user->guru) {
                    $user->setRelation('kelasAsWali', $user->kelasAsWali());
                } else {
                    $user->setRelation('kelasAsWali', collect());
                }
            }

            return response()->json([
                'message' => 'User berhasil ditambahkan',
                'data' => $user,
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(User $user)
    {
        $user->load([
            'guru',
            'siswa.kelas.jurusan',
            'siswa.kelas.waliKelas',
            'catatanAkademik',
            'approvedRapor',
        ]);
        
        // Load kelasAsWali separately if user has guru
        if ($user->guru) {
            $user->setRelation('kelasAsWali', $user->kelasAsWali());
        } else {
            $user->setRelation('kelasAsWali', collect());
        }

        return response()->json($user);
    }

    /**
     * Update the specified user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => ['required', 'in:admin,guru,wali_kelas,kepala_sekolah,siswa'],
            'nuptk' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'nis' => ['nullable', 'string', Rule::unique('users')->ignore($user->id)],
            'is_active' => ['nullable', 'boolean'],
        ]);

        // Validate role-specific fields
        if (in_array($request->role, ['guru', 'wali_kelas', 'kepala_sekolah']) && !$request->nuptk) {
            return response()->json([
                'message' => 'NUPTK wajib diisi untuk role guru, wali kelas, atau kepala sekolah',
            ], 422);
        }

        if ($request->role === 'siswa' && !$request->nis) {
            return response()->json([
                'message' => 'NIS wajib diisi untuk role siswa',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user->update([
                'name' => $request->name,
                'email' => $request->email,
                'role' => $request->role,
                'nuptk' => $request->nuptk,
                'nis' => $request->nis,
                'is_active' => $request->is_active ?? $user->is_active,
            ]);

            DB::commit();

            // Load relationships based on role
            $user = $user->fresh();
            if ($user->role === 'siswa') {
                $user->load(['siswa.kelas.jurusan']);
            } else {
                $user->load(['guru', 'siswa.kelas.jurusan']);
                
                // Load kelasAsWali separately if user has guru
                if ($user->guru) {
                    $user->setRelation('kelasAsWali', $user->kelasAsWali());
                } else {
                    $user->setRelation('kelasAsWali', collect());
                }
            }

            return response()->json([
                'message' => 'User berhasil diperbarui',
                'data' => $user,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified user.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        // Prevent deleting user if they have related data
        if ($user->guru()->exists()) {
            return response()->json([
                'message' => 'User tidak dapat dihapus karena masih memiliki data guru. Hapus data guru terlebih dahulu.',
            ], 422);
        }

        if ($user->siswa()->exists()) {
            return response()->json([
                'message' => 'User tidak dapat dihapus karena masih memiliki data siswa. Hapus data siswa terlebih dahulu.',
            ], 422);
        }

        if ($user->kelasAsWali()->exists()) {
            return response()->json([
                'message' => 'User tidak dapat dihapus karena masih menjadi wali kelas. Pindahkan kelas terlebih dahulu.',
            ], 422);
        }

        if ($user->catatanAkademik()->exists()) {
            return response()->json([
                'message' => 'User tidak dapat dihapus karena masih memiliki catatan akademik.',
            ], 422);
        }

        if ($user->approvedRapor()->exists()) {
            return response()->json([
                'message' => 'User tidak dapat dihapus karena masih memiliki rapor yang disetujui.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'User berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus user',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset password for user.
     *
     * @param  Request  $request
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, User $user)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil direset',
        ]);
    }

    /**
     * Toggle user active status.
     *
     * @param  User  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function toggleStatus(User $user)
    {
        $newStatus = !$user->is_active;
        $user->update(['is_active' => $newStatus]);

        return response()->json([
            'message' => 'Status user berhasil diubah',
            'data' => $user->fresh(),
        ]);
    }
}


