<?php

namespace App\Http\Controllers\Api\WaliKelas;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\WaliKelas;
use Illuminate\Support\Facades\Auth;

/**
 * KelasController for Wali Kelas
 * 
 * Handles Kelas data retrieval for wali kelas
 */
class KelasController extends Controller
{
    /**
     * Get all kelas that the authenticated user is wali kelas for.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user->guru) {
            return response()->json([
                'data' => [],
                'message' => 'Guru tidak ditemukan'
            ]);
        }

        // Get all active WaliKelas assignments for this guru
        $waliKelas = WaliKelas::where('guru_id', $user->guru->id)
            ->where('is_active', true)
            ->pluck('kelas_id')
            ->toArray();

        // Get kelas by the WaliKelas assignments
        $kelas = Kelas::whereIn('id', $waliKelas)
            ->with(['jurusan', 'waliKelas' => function ($query) use ($user) {
                $query->where('guru_id', $user->guru->id)->where('is_active', true);
            }])
            ->orderBy('nama_kelas')
            ->get();

        return response()->json([
            'data' => $kelas,
            'message' => 'Kelas berhasil diambil'
        ]);
    }
}
