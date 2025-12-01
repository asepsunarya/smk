<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Guru;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * GuruController
 * 
 * Handles CRUD operations for guru (admin only)
 */
class GuruController extends Controller
{
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
                  ->orWhere('nip', 'like', "%{$search}%")
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
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nip' => ['required', 'string', 'unique:guru'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:guru,wali_kelas,kepala_sekolah'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['required', 'string'],
            'pendidikan_terakhir' => ['required', 'string'],
            'bidang_studi' => ['required', 'string'],
            'tanggal_masuk' => ['required', 'date'],
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'nip' => $request->nip,
            ]);

            $guru = Guru::create([
                'user_id' => $user->id,
                'nip' => $request->nip,
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
                'data' => $guru->load('user'),
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

        if ($guru->user->role === 'wali_kelas') {
            $guru->load('user.kelasAsWali.siswa');
        }

        return response()->json($guru);
    }

    /**
     * Update the specified guru.
     *
     * @param  Request  $request
     * @param  Guru  $guru
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Guru $guru)
    {
        $request->validate([
            'nip' => ['required', 'string', Rule::unique('guru')->ignore($guru->id)],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', Rule::unique('users')->ignore($guru->user_id)],
            'role' => ['required', 'in:guru,wali_kelas,kepala_sekolah'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['required', 'string'],
            'pendidikan_terakhir' => ['required', 'string'],
            'bidang_studi' => ['required', 'string'],
            'status' => ['required', 'in:aktif,non_aktif,pensiun'],
        ]);

        DB::beginTransaction();
        try {
            $guru->user->update([
                'name' => $request->nama_lengkap,
                'email' => $request->email,
                'role' => $request->role,
                'nip' => $request->nip,
            ]);

            $guru->update($request->except(['email', 'password', 'role']));

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
}