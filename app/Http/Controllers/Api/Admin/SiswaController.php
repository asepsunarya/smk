<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Siswa;
use App\Models\User;
use App\Models\Kelas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/**
 * SiswaController
 * 
 * Handles CRUD operations for siswa (admin only)
 */
class SiswaController extends Controller
{
    /**
     * Display a listing of siswa.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = Siswa::with(['user', 'kelas.jurusan']);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        if ($request->has('kelas_id')) {
            $query->where('kelas_id', $request->kelas_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $siswa = $query->orderBy('nama_lengkap')->paginate($request->get('per_page', 15));

        return response()->json($siswa);
    }

    /**
     * Store a newly created siswa.
     * Only siswa data, no user required.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nis' => ['required', 'string', 'unique:siswa', 'regex:/^[0-9]+$/'],
            'nisn' => ['required', 'string', 'unique:siswa', 'regex:/^[0-9]+$/'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'anak_ke' => ['required', 'string', 'max:10'],
            'sekolah_asal' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['nullable', 'string'],
            'nama_ayah' => ['required', 'string'],
            'nama_ibu' => ['required', 'string'],
            'pekerjaan_ayah' => ['required', 'string'],
            'pekerjaan_ibu' => ['required', 'string'],
            'no_hp_ortu' => ['nullable', 'string'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tanggal_masuk' => ['required', 'date'],
        ]);

        DB::beginTransaction();
        try {
            $siswa = Siswa::create([
                'user_id' => null,
                'nis' => $request->nis,
                'nisn' => $request->nisn,
                'nama_lengkap' => $request->nama_lengkap,
                'jenis_kelamin' => $request->jenis_kelamin,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'agama' => $request->agama,
                'anak_ke' => $request->anak_ke,
                'sekolah_asal' => $request->sekolah_asal,
                'alamat' => $request->alamat,
                'no_hp' => $request->no_hp,
                'nama_ayah' => $request->nama_ayah,
                'nama_ibu' => $request->nama_ibu,
                'pekerjaan_ayah' => $request->pekerjaan_ayah,
                'pekerjaan_ibu' => $request->pekerjaan_ibu,
                'no_hp_ortu' => $request->no_hp_ortu,
                'kelas_id' => $request->kelas_id,
                'tanggal_masuk' => $request->tanggal_masuk ?? now(),
                'status' => 'aktif',
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Siswa berhasil ditambahkan',
                'data' => $siswa->load('kelas.jurusan'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menambahkan siswa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified siswa.
     *
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Siswa $siswa)
    {
        $siswa->load([
            'user',
            'kelas.jurusan',
            'kelas.waliKelas',
            'nilai.mataPelajaran',
            'nilaiEkstrakurikuler.ekstrakurikuler',
            'kehadiran',
            'rapor',
        ]);

        return response()->json($siswa);
    }

    /**
     * Update the specified siswa.
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Siswa $siswa)
    {
        $request->validate([
            'nis' => ['required', 'string', Rule::unique('siswa')->ignore($siswa->id), 'regex:/^[0-9]+$/'],
            'nisn' => ['required', 'string', Rule::unique('siswa')->ignore($siswa->id), 'regex:/^[0-9]+$/'],
            'nama_lengkap' => ['required', 'string', 'max:255'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tempat_lahir' => ['required', 'string'],
            'tanggal_lahir' => ['required', 'date'],
            'agama' => ['required', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'anak_ke' => ['required', 'string', 'max:10'],
            'sekolah_asal' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_hp' => ['nullable', 'string'],
            'nama_ayah' => ['required', 'string'],
            'nama_ibu' => ['required', 'string'],
            'pekerjaan_ayah' => ['required', 'string'],
            'pekerjaan_ibu' => ['required', 'string'],
            'no_hp_ortu' => ['nullable', 'string'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'status' => ['required', 'in:aktif,lulus,pindah,keluar'],
        ]);

        DB::beginTransaction();
        try {
            // Update user's name and NIS if user exists
            if ($siswa->user) {
                $siswa->user->update([
                    'name' => $request->nama_lengkap,
                    'nis' => $request->nis,
                ]);
            }

            $siswa->update($request->except(['email', 'password']));

            DB::commit();

            return response()->json([
                'message' => 'Siswa berhasil diperbarui',
                'data' => $siswa->fresh()->load(['user', 'kelas.jurusan']),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal memperbarui siswa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified siswa.
     *
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Siswa $siswa)
    {
        DB::beginTransaction();
        try {
            $user = $siswa->user;
            $siswa->delete();
            $user->delete();

            DB::commit();

            return response()->json([
                'message' => 'Siswa berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus siswa',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Move siswa to another class.
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function moveClass(Request $request, Siswa $siswa)
    {
        $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
        ]);

        $kelas = Kelas::find($request->kelas_id);

        if ($kelas->is_full) {
            return response()->json([
                'message' => 'Kelas sudah penuh',
            ], 422);
        }

        $siswa->update(['kelas_id' => $request->kelas_id]);

        return response()->json([
            'message' => 'Siswa berhasil dipindahkan ke kelas ' . $kelas->nama_kelas,
            'data' => $siswa->fresh()->load('kelas'),
        ]);
    }

    /**
     * Reset password for siswa.
     *
     * @param  Request  $request
     * @param  Siswa  $siswa
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetPassword(Request $request, Siswa $siswa)
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $siswa->user->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'message' => 'Password berhasil direset',
        ]);
    }

    /**
     * Get siswa that don't have user yet.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function availableSiswa(Request $request)
    {
        $query = Siswa::where('status', 'aktif')
            ->with('kelas.jurusan')
            ->select('id', 'nama_lengkap', 'nis', 'kelas_id', 'user_id');
        
        // If editing, include siswa that's already linked to this user
        if ($request->has('user_id')) {
            $query->where(function($q) use ($request) {
                $q->whereNull('user_id')
                  ->orWhere('user_id', $request->user_id);
            });
        } else {
            $query->whereNull('user_id');
        }
        
        $siswa = $query->orderBy('nama_lengkap')->get();

        return response()->json($siswa);
    }
}